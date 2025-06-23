<?php
namespace Opencart\Admin\Model\Extension\Module;

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
        $this->log('info', 'marketplace_sync', "Starting sync for marketplace: {$marketplace}");

        $result = [
            'success' => true,
            'marketplace' => $marketplace,
            'products_synced' => 0,
            'orders_synced' => 0,
            'errors' => []
        ];

        try {
            // Load marketplace specific sync class
            $syncClass = '\\MesChain\\Sync\\' . ucfirst($marketplace) . 'Sync';

            if (class_exists($syncClass)) {
                $sync = new $syncClass($this->registry);

                // Sync products
                $productResult = $sync->syncProducts();
                $result['products_synced'] = $productResult['count'] ?? 0;

                // Sync orders
                $orderResult = $sync->syncOrders();
                $result['orders_synced'] = $orderResult['count'] ?? 0;

            } else {
                throw new \Exception("Sync class not found for marketplace: {$marketplace}");
            }

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
            $this->log('error', 'marketplace_sync', $e->getMessage(), ['marketplace' => $marketplace]);
        }

        return $result;
    }

    /**
     * Get system status
     */
    public function getSystemStatus(): array {
        $status = [
            'system' => 'operational',
            'timestamp' => date('c'),
            'modules' => $this->getModuleStatuses(),
            'marketplaces' => $this->getMarketplaceStatuses(),
            'metrics' => $this->getSystemMetrics(),
            'health' => $this->getHealthChecks()
        ];

        return $status;
    }

    /**
     * Sync all marketplaces (for cron)
     */
    public function syncAllMarketplaces(): void {
        $marketplaces = ['trendyol', 'n11', 'hepsiburada', 'amazon', 'ebay', 'gittigidiyor', 'pazarama', 'pttavm'];

        foreach ($marketplaces as $marketplace) {
            $this->syncMarketplace($marketplace);
        }
    }

    /**
     * Collect metrics (for cron)
     */
    public function collectMetrics(): void {
        // Product metrics
        $productCount = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "product WHERE status = '1'")->row['total'];
        $this->addMetric('product', 'active_count', $productCount);

        // Order metrics
        $orderCount = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "order WHERE date_added >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->row['total'];
        $this->addMetric('order', 'daily_count', $orderCount);

        // Sync metrics
        $syncSuccess = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_product_sync WHERE sync_status = 'success' AND last_sync >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->row['total'];
        $this->addMetric('sync', 'daily_success', $syncSuccess);

        // API cache metrics
        $cacheSize = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_api_cache WHERE expire_time > NOW()")->row['total'];
        $this->addMetric('cache', 'active_entries', $cacheSize);
    }

    /**
     * Cleanup old data (for cron)
     */
    public function cleanupOldData(): void {
        // Clean old logs (keep 30 days)
        $this->db->query("DELETE FROM " . DB_PREFIX . "meschain_logs WHERE date_added < DATE_SUB(NOW(), INTERVAL 30 DAY)");

        // Clean old metrics (keep 90 days)
        $this->db->query("DELETE FROM " . DB_PREFIX . "meschain_metrics WHERE date_added < DATE_SUB(NOW(), INTERVAL 90 DAY)");

        // Clean expired cache
        $this->db->query("DELETE FROM " . DB_PREFIX . "meschain_api_cache WHERE expire_time < NOW()");

        // Clean old sync errors (keep 7 days)
        $this->db->query("UPDATE " . DB_PREFIX . "meschain_product_sync SET error_message = NULL WHERE sync_status = 'error' AND last_sync < DATE_SUB(NOW(), INTERVAL 7 DAY)");
    }

    /**
     * Private helper methods
     */

    private function getModuleStatuses(): array {
        return [
            'meschain_sync' => 'active',
            'api_gateway' => 'active',
            'category_matcher' => 'active',
            'pricing_engine' => 'active',
            'analytics' => 'active'
        ];
    }

    private function getMarketplaceStatuses(): array {
        $statuses = [];
        $marketplaces = ['trendyol', 'n11', 'hepsiburada', 'amazon', 'ebay', 'gittigidiyor', 'pazarama', 'pttavm'];

        foreach ($marketplaces as $marketplace) {
            $lastSync = $this->db->query("SELECT MAX(last_sync) as last_sync FROM " . DB_PREFIX . "meschain_product_sync WHERE marketplace = '" . $this->db->escape($marketplace) . "'")->row['last_sync'];

            $statuses[$marketplace] = [
                'status' => 'ready',
                'last_sync' => $lastSync,
                'enabled' => $this->getMarketplaceSetting($marketplace, 'enabled') == '1'
            ];
        }

        return $statuses;
    }

    private function getSystemMetrics(): array {
        $metrics = [];

        // Get latest metrics
        $query = $this->db->query("SELECT metric_key, metric_value FROM " . DB_PREFIX . "meschain_metrics WHERE metric_type = 'system' AND date_added >= DATE_SUB(NOW(), INTERVAL 1 HOUR) ORDER BY date_added DESC");

        foreach ($query->rows as $row) {
            $metrics[$row['metric_key']] = (float)$row['metric_value'];
        }

        return $metrics;
    }

    private function getHealthChecks(): array {
        $checks = [];

        // Database check
        $checks['database'] = $this->checkDatabase() ? 'healthy' : 'unhealthy';

        // API check
        $checks['api'] = $this->checkApiConnectivity() ? 'healthy' : 'unhealthy';

        // Cache check
        $checks['cache'] = $this->checkCache() ? 'healthy' : 'unhealthy';

        return $checks;
    }

    private function checkDatabase(): bool {
        try {
            $this->db->query("SELECT 1");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkApiConnectivity(): bool {
        // Simple connectivity check
        $ch = curl_init('https://api.github.com');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode == 200;
    }

    private function checkCache(): bool {
        try {
            // Try to write and read from cache table
            $testKey = 'health_check_' . time();
            $this->db->query("INSERT INTO " . DB_PREFIX . "meschain_api_cache SET cache_key = '" . $testKey . "', marketplace = 'system', cache_data = 'test', expire_time = DATE_ADD(NOW(), INTERVAL 1 MINUTE), date_added = NOW()");
            $this->db->query("DELETE FROM " . DB_PREFIX . "meschain_api_cache WHERE cache_key = '" . $testKey . "'");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getMarketplaceSetting(string $marketplace, string $key): ?string {
        $query = $this->db->query("SELECT setting_value FROM " . DB_PREFIX . "meschain_settings WHERE marketplace = '" . $this->db->escape($marketplace) . "' AND setting_key = '" . $this->db->escape($key) . "' AND status = '1' LIMIT 1");

        return $query->row ? $query->row['setting_value'] : null;
    }

    private function addMetric(string $type, string $key, $value, ?string $marketplace = null): void {
        $this->db->query("INSERT INTO " . DB_PREFIX . "meschain_metrics SET
                         metric_type = '" . $this->db->escape($type) . "',
                         metric_key = '" . $this->db->escape($key) . "',
                         metric_value = '" . (float)$value . "',
                         marketplace = " . ($marketplace ? "'" . $this->db->escape($marketplace) . "'" : "NULL") . ",
                         date_added = NOW()");
    }

    private function log(string $level, string $type, string $message, array $data = []): void {
        $this->db->query("INSERT INTO " . DB_PREFIX . "meschain_logs SET
                         log_level = '" . $this->db->escape($level) . "',
                         log_type = '" . $this->db->escape($type) . "',
                         log_message = '" . $this->db->escape($message) . "',
                         log_data = '" . $this->db->escape(json_encode($data)) . "',
                         user_id = '" . (int)($this->user->getId() ?? 0) . "',
                         ip_address = '" . $this->db->escape($this->request->server['REMOTE_ADDR'] ?? '') . "',
                         date_added = NOW()");
    }
}
