<?php
namespace Opencart\Admin\Model\Extension\Module;

use Exception;

/**
 * MesChain Sync Enterprise - Main Model
 * OpenCart 4.0.2.3 Compatible
 *
 * @author Cursor Development Team
 * @version 1.0.0
 */
class MeschainSync extends \Opencart\System\Engine\Model {

    /**
     * Install method - creates database tables
     */
    public function install(): void {
        // Ayarlar tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_settings` (
            `setting_id` int(11) NOT NULL AUTO_INCREMENT,
            `marketplace` varchar(50) NOT NULL,
            `setting_group` varchar(100) NOT NULL,
            `setting_key` varchar(100) NOT NULL,
            `setting_value` longtext,
            `encrypted` tinyint(1) DEFAULT '0',
            `status` tinyint(1) DEFAULT '1',
            `date_added` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`setting_id`),
            KEY `marketplace` (`marketplace`),
            KEY `setting_group_key` (`setting_group`, `setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Ürün senkronizasyon tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_product_sync` (
            `sync_id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `marketplace` varchar(50) NOT NULL,
            `marketplace_product_id` varchar(255),
            `sync_status` enum('pending','syncing','success','error') DEFAULT 'pending',
            `last_sync` datetime DEFAULT NULL,
            `sync_data` longtext,
            `error_message` text,
            `retry_count` int(11) DEFAULT '0',
            PRIMARY KEY (`sync_id`),
            UNIQUE KEY `product_marketplace` (`product_id`, `marketplace`),
            KEY `sync_status` (`sync_status`),
            KEY `last_sync` (`last_sync`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Sipariş entegrasyon tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_order_integration` (
            `integration_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `marketplace` varchar(50) NOT NULL,
            `marketplace_order_id` varchar(255),
            `integration_status` enum('pending','integrated','shipped','delivered','cancelled','returned') DEFAULT 'pending',
            `tracking_number` varchar(255),
            `carrier_code` varchar(50),
            `marketplace_data` longtext,
            `date_integrated` datetime DEFAULT NULL,
            `date_shipped` datetime DEFAULT NULL,
            `date_delivered` datetime DEFAULT NULL,
            PRIMARY KEY (`integration_id`),
            UNIQUE KEY `order_marketplace` (`order_id`, `marketplace`),
            KEY `marketplace` (`marketplace`),
            KEY `integration_status` (`integration_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Log tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_logs` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `log_level` enum('debug','info','warning','error','critical') DEFAULT 'info',
            `log_type` varchar(50) NOT NULL,
            `log_message` text NOT NULL,
            `log_data` longtext,
            `marketplace` varchar(50) DEFAULT NULL,
            `user_id` int(11) DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `date_added` datetime NOT NULL,
            PRIMARY KEY (`log_id`),
            KEY `log_level` (`log_level`),
            KEY `log_type` (`log_type`),
            KEY `marketplace` (`marketplace`),
            KEY `date_added` (`date_added`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Metrik tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_metrics` (
            `metric_id` int(11) NOT NULL AUTO_INCREMENT,
            `metric_type` varchar(50) NOT NULL,
            `metric_key` varchar(100) NOT NULL,
            `metric_value` decimal(15,4) NOT NULL,
            `metric_data` longtext,
            `marketplace` varchar(50) DEFAULT NULL,
            `date_added` datetime NOT NULL,
            PRIMARY KEY (`metric_id`),
            KEY `metric_type` (`metric_type`),
            KEY `metric_key` (`metric_key`),
            KEY `marketplace` (`marketplace`),
            KEY `date_added` (`date_added`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Kategori eşleştirme tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_category_mapping` (
            `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
            `category_id` int(11) NOT NULL,
            `marketplace` varchar(50) NOT NULL,
            `marketplace_category_id` varchar(255) NOT NULL,
            `marketplace_category_name` varchar(500) NOT NULL,
            `confidence_score` decimal(5,4) DEFAULT '0.0000',
            `auto_mapped` tinyint(1) DEFAULT '0',
            `status` tinyint(1) DEFAULT '1',
            `date_added` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`mapping_id`),
            UNIQUE KEY `category_marketplace` (`category_id`, `marketplace`, `marketplace_category_id`),
            KEY `marketplace` (`marketplace`),
            KEY `confidence_score` (`confidence_score`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // API çağrı önbelleği tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_api_cache` (
            `cache_id` int(11) NOT NULL AUTO_INCREMENT,
            `cache_key` varchar(255) NOT NULL,
            `marketplace` varchar(50) NOT NULL,
            `cache_data` longtext NOT NULL,
            `expire_time` datetime NOT NULL,
            `date_added` datetime NOT NULL,
            PRIMARY KEY (`cache_id`),
            UNIQUE KEY `cache_key_marketplace` (`cache_key`, `marketplace`),
            KEY `expire_time` (`expire_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    /**
     * Uninstall method - removes database tables
     */
    public function uninstall(): void {
        $tables = [
            'meschain_settings',
            'meschain_product_sync',
            'meschain_order_integration',
            'meschain_logs',
            'meschain_metrics',
            'meschain_category_mapping',
            'meschain_api_cache'
        ];

        foreach ($tables as $table) {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . $table . "`");
        }
    }

    /**
     * Search products
     */
    public function searchProducts(string $query, int $limit = 50): array {
        $sql = "SELECT p.product_id, pd.name, p.model, p.sku, p.quantity, p.price, p.status,
                       pi.image, m.name as manufacturer
                FROM " . DB_PREFIX . "product p
                LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
                LEFT JOIN " . DB_PREFIX . "product_image pi ON (p.product_id = pi.product_id AND pi.sort_order = 0)
                LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id)
                WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($query)) {
            $sql .= " AND (pd.name LIKE '%" . $this->db->escape($query) . "%'
                          OR p.model LIKE '%" . $this->db->escape($query) . "%'
                          OR p.sku LIKE '%" . $this->db->escape($query) . "%')";
        }

        $sql .= " ORDER BY pd.name ASC LIMIT " . (int)$limit;

        $query = $this->db->query($sql);

        $products = [];
        foreach ($query->rows as $row) {
            $products[] = [
                'product_id' => $row['product_id'],
                'name' => $row['name'],
                'model' => $row['model'],
                'sku' => $row['sku'],
                'quantity' => $row['quantity'],
                'price' => $this->currency->format($row['price'], $this->config->get('config_currency')),
                'status' => $row['status'],
                'image' => $row['image'],
                'manufacturer' => $row['manufacturer']
            ];
        }

        return $products;
    }

    /**
     * Get product by barcode
     */
    public function getProductByBarcode(string $barcode): ?array {
        $query = $this->db->query("SELECT p.*, pd.name, pd.description
                                  FROM " . DB_PREFIX . "product p
                                  LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
                                  WHERE (p.ean = '" . $this->db->escape($barcode) . "'
                                         OR p.upc = '" . $this->db->escape($barcode) . "'
                                         OR p.isbn = '" . $this->db->escape($barcode) . "'
                                         OR p.mpn = '" . $this->db->escape($barcode) . "')
                                  AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                                  LIMIT 1");

        return $query->row ?: null;
    }

    /**
     * Sync marketplace
     */
    public function syncMarketplace(string $marketplace): array {
        try {
            // Get marketplace info
            $query = $this->db->query("
                SELECT * FROM `" . DB_PREFIX . "meschain_marketplaces`
                WHERE `code` = '" . $this->db->escape($marketplace) . "' AND `status` = 1
            ");

            if (!$query->num_rows) {
                throw new Exception('Marketplace not found or inactive: ' . $marketplace);
            }

            $marketplaceData = $query->row;

            // Load marketplace-specific library
            $this->load->library('meschain/api/' . $marketplace);
            $apiClass = 'MesChain\\Api\\' . ucfirst($marketplace);

            if (!class_exists($apiClass)) {
                throw new Exception('API class not found: ' . $apiClass);
            }

            $api = new $apiClass($marketplaceData);

            // Perform sync
            $result = $api->syncProducts();

            // Update last sync time
            $this->db->query("
                UPDATE `" . DB_PREFIX . "meschain_marketplaces`
                SET `last_sync` = NOW()
                WHERE `marketplace_id` = " . (int)$marketplaceData['marketplace_id']
            );

            // Log success
            $this->logActivity('info', 'sync', 'Marketplace sync completed: ' . $marketplace, $result);

            return [
                'success' => true,
                'message' => 'Sync completed successfully',
                'data' => $result
            ];

        } catch (Exception $e) {
            // Log error
            $this->logActivity('error', 'sync', 'Marketplace sync failed: ' . $marketplace, ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test marketplace connection
     */
    public function testMarketplaceConnection(string $marketplace): array {
        try {
            $startTime = microtime(true);
            
            // Load marketplace-specific model
            switch ($marketplace) {
                case 'trendyol':
                    $this->load->model('extension/module/meschain/trendyol');
                    $api = $this->model_extension_module_meschain_trendyol->getApiClient();
                    
                    // Test Trendyol API connection
                    $response = $api->testConnection();
                    break;
                    
                case 'amazon':
                    $this->load->model('extension/module/meschain/amazon');
                    $api = $this->model_extension_module_meschain_amazon->getApiClient();
                    $response = $api->testConnection();
                    break;
                    
                case 'hepsiburada':
                    $this->load->model('extension/module/meschain/hepsiburada');
                    $api = $this->model_extension_module_meschain_hepsiburada->getApiClient();
                    $response = $api->testConnection();
                    break;
                    
                case 'n11':
                    $this->load->model('extension/module/meschain/n11');
                    $api = $this->model_extension_module_meschain_n11->getApiClient();
                    $response = $api->testConnection();
                    break;
                    
                default:
                    throw new Exception('Unsupported marketplace: ' . $marketplace);
            }
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            // Log successful connection test
            $this->logActivity('info', 'connection_test',
                'Connection test successful for ' . $marketplace,
                ['response_time' => $responseTime, 'response' => $response]
            );
            
            return [
                'status' => 'success',
                'message' => 'Connection successful',
                'response_time' => $responseTime . 'ms',
                'api_version' => $response['api_version'] ?? null,
                'marketplace_status' => $response['status'] ?? 'active'
            ];
            
        } catch (Exception $e) {
            // Log failed connection test
            $this->logActivity('error', 'connection_test',
                'Connection test failed for ' . $marketplace,
                ['error' => $e->getMessage(), 'code' => $e->getCode()]
            );
            
            throw new Exception('Connection test failed: ' . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Get system status
     */
    public function getSystemStatus(): array {
        $status = [
            'overall' => 'healthy',
            'marketplaces' => [],
            'stats' => [],
            'issues' => []
        ];

        // Check marketplaces
        $query = $this->db->query("
            SELECT
                code,
                name,
                status,
                last_sync,
                TIMESTAMPDIFF(MINUTE, last_sync, NOW()) as minutes_since_sync
            FROM `" . DB_PREFIX . "meschain_marketplaces`
        ");

        foreach ($query->rows as $marketplace) {
            $marketplaceStatus = [
                'code' => $marketplace['code'],
                'name' => $marketplace['name'],
                'active' => (bool)$marketplace['status'],
                'last_sync' => $marketplace['last_sync'],
                'status' => 'healthy'
            ];

            if (!$marketplace['status']) {
                $marketplaceStatus['status'] = 'inactive';
            } elseif ($marketplace['last_sync'] && $marketplace['minutes_since_sync'] > 60) {
                $marketplaceStatus['status'] = 'warning';
                $status['issues'][] = $marketplace['name'] . ' has not synced for over 1 hour';
            } elseif (!$marketplace['last_sync']) {
                $marketplaceStatus['status'] = 'warning';
                $status['issues'][] = $marketplace['name'] . ' has never been synced';
            }

            $status['marketplaces'][] = $marketplaceStatus;
        }

        // Get stats
        $stats = $this->db->query("
            SELECT
                (SELECT COUNT(*) FROM `" . DB_PREFIX . "meschain_products` WHERE sync_status = 'synced') as synced_products,
                (SELECT COUNT(*) FROM `" . DB_PREFIX . "meschain_products` WHERE sync_status = 'failed') as failed_products,
                (SELECT COUNT(*) FROM `" . DB_PREFIX . "meschain_orders` WHERE sync_status = 'synced') as synced_orders,
                (SELECT COUNT(*) FROM `" . DB_PREFIX . "meschain_orders` WHERE sync_status = 'failed') as failed_orders,
                (SELECT COUNT(*) FROM `" . DB_PREFIX . "meschain_logs` WHERE log_type = 'error' AND created_date > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as errors_24h
        ");

        $status['stats'] = $stats->row;

        // Determine overall status
        if (count($status['issues']) > 0) {
            $status['overall'] = 'warning';
        }

        if ($status['stats']['errors_24h'] > 50) {
            $status['overall'] = 'critical';
            $status['issues'][] = 'High error rate in last 24 hours: ' . $status['stats']['errors_24h'] . ' errors';
        }

        return $status;
    }

    /**
     * Sync all marketplaces (cron job)
     */
    public function syncAllMarketplaces(): void {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_marketplaces`
            WHERE `status` = 1
        ");

        foreach ($query->rows as $marketplace) {
            $this->syncMarketplace($marketplace['code']);
        }
    }

    /**
     * Collect metrics (cron job)
     */
    public function collectMetrics(): void {
        // Collect and store performance metrics
        $metrics = [
            'timestamp' => time(),
            'total_products' => $this->getTotalProducts(),
            'synced_products' => $this->getSyncedProducts(),
            'total_orders' => $this->getTotalOrders(),
            'synced_orders' => $this->getSyncedOrders(),
            'api_calls' => $this->getApiCallsToday(),
            'error_rate' => $this->getErrorRate()
        ];

        // Store metrics (could be extended to use a time-series database)
        $this->logActivity('info', 'metrics', 'System metrics collected', $metrics);
    }

    /**
     * Clean up old data (cron job)
     */
    public function cleanupOldData(): void {
        // Clean old logs (older than 30 days)
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "meschain_logs`
            WHERE `created_date` < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");

        // Clean failed sync records (older than 7 days)
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "meschain_products`
            WHERE `sync_status` = 'failed'
            AND `modified_date` < DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");

        $this->logActivity('info', 'cleanup', 'Old data cleanup completed');
    }

    /**
     * Get order sync status
     */
    public function getOrderSyncStatus(int $orderId): array {
        $query = $this->db->query("
            SELECT
                mo.marketplace_order_id,
                mo.marketplace_status,
                mo.sync_status,
                mo.last_sync_date,
                m.name as marketplace_name,
                m.code as marketplace_code
            FROM `" . DB_PREFIX . "meschain_orders` mo
            LEFT JOIN `" . DB_PREFIX . "meschain_marketplaces` m ON mo.marketplace_id = m.marketplace_id
            WHERE mo.order_id = " . (int)$orderId
        );

        return $query->rows;
    }

    /**
     * Log activity
     */
    private function logActivity(string $type, string $category, string $message, array $context = []): void {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_logs`
            (`log_type`, `log_category`, `message`, `context_data`, `user_id`, `ip_address`, `user_agent`)
            VALUES (
                '" . $this->db->escape($type) . "',
                '" . $this->db->escape($category) . "',
                '" . $this->db->escape($message) . "',
                '" . $this->db->escape(json_encode($context)) . "',
                " . (isset($this->user) && $this->user->getId() ? (int)$this->user->getId() : 'NULL') . ",
                '" . $this->db->escape($this->request->server['REMOTE_ADDR'] ?? '') . "',
                '" . $this->db->escape($this->request->server['HTTP_USER_AGENT'] ?? '') . "'
            )
        ");
    }

    // Helper methods for metrics
    private function getTotalProducts(): int {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "product`");
        return (int)$query->row['total'];
    }

    private function getSyncedProducts(): int {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_products` WHERE sync_status = 'synced'");
        return (int)$query->row['total'];
    }

    private function getTotalOrders(): int {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "order`");
        return (int)$query->row['total'];
    }

    private function getSyncedOrders(): int {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_orders` WHERE sync_status = 'synced'");
        return (int)$query->row['total'];
    }

    private function getApiCallsToday(): int {
        $query = $this->db->query("
            SELECT COUNT(*) as total
            FROM `" . DB_PREFIX . "meschain_logs`
            WHERE log_category = 'api'
            AND DATE(created_date) = CURDATE()
        ");
        return (int)$query->row['total'];
    }

    private function getErrorRate(): float {
        $query = $this->db->query("
            SELECT
                COUNT(CASE WHEN log_type = 'error' THEN 1 END) as errors,
                COUNT(*) as total
            FROM `" . DB_PREFIX . "meschain_logs`
            WHERE created_date > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");

        $row = $query->row;
        return $row['total'] > 0 ? ($row['errors'] / $row['total']) * 100 : 0;
    }
}
