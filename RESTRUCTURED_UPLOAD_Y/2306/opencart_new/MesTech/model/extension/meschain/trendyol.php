<?php
namespace Opencart\Admin\Model\Extension\MesChain;

/**
 * MesChain Sync Trendyol Model
 * 
 * @package    MesChain Sync
 * @version    2.0.0
 * @author     MesChain Development Team
 * @copyright  2024 MesChain
 * @license    MIT License
 */

class Trendyol extends \Opencart\System\Engine\Model {
    
    /**
     * Create Trendyol tables
     */
    public function createTables(): void {
        // Trendyol products mapping table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_trendyol_products` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `trendyol_product_id` varchar(255) NOT NULL,
            `trendyol_barcode` varchar(255) DEFAULT NULL,
            `trendyol_category_id` int(11) DEFAULT NULL,
            `trendyol_brand_id` int(11) DEFAULT NULL,
            `sync_status` enum('pending','synced','error') DEFAULT 'pending',
            `last_sync` datetime DEFAULT NULL,
            `sync_error` text DEFAULT NULL,
            `trendyol_data` longtext DEFAULT NULL,
            `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_modified` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `product_id` (`product_id`),
            INDEX `trendyol_product_id` (`trendyol_product_id`),
            INDEX `sync_status` (`sync_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Trendyol orders mapping table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_trendyol_orders` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `trendyol_order_id` varchar(255) NOT NULL,
            `trendyol_package_id` varchar(255) DEFAULT NULL,
            `sync_status` enum('pending','synced','error') DEFAULT 'pending',
            `last_sync` datetime DEFAULT NULL,
            `sync_error` text DEFAULT NULL,
            `trendyol_data` longtext DEFAULT NULL,
            `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_modified` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `order_id` (`order_id`),
            INDEX `trendyol_order_id` (`trendyol_order_id`),
            INDEX `sync_status` (`sync_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Trendyol categories mapping table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_trendyol_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `category_id` int(11) NOT NULL,
            `trendyol_category_id` int(11) NOT NULL,
            `trendyol_category_name` varchar(255) NOT NULL,
            `trendyol_parent_id` int(11) DEFAULT NULL,
            `commission_rate` decimal(5,2) DEFAULT NULL,
            `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_modified` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `category_id` (`category_id`),
            INDEX `trendyol_category_id` (`trendyol_category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Trendyol sync logs table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_trendyol_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `log_level` enum('debug','info','warning','error','critical') NOT NULL DEFAULT 'info',
            `log_type` varchar(50) NOT NULL,
            `message` text NOT NULL,
            `context` longtext DEFAULT NULL,
            `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `log_level` (`log_level`),
            INDEX `log_type` (`log_type`),
            INDEX `date_added` (`date_added`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }
    
    /**
     * Drop Trendyol tables
     */
    public function dropTables(): void {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_trendyol_products`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_trendyol_orders`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_trendyol_categories`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_trendyol_logs`");
    }
    
    /**
     * Get Trendyol product mapping
     */
    public function getProductMapping(int $product_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_trendyol_products` 
            WHERE product_id = '" . (int)$product_id . "'");
        
        return $query->row;
    }
    
    /**
     * Add or update product mapping
     */
    public function saveProductMapping(int $product_id, array $data): void {
        $existing = $this->getProductMapping($product_id);
        
        if ($existing) {
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_trendyol_products` SET 
                `trendyol_product_id` = '" . $this->db->escape($data['trendyol_product_id']) . "',
                `trendyol_barcode` = '" . $this->db->escape($data['trendyol_barcode'] ?? '') . "',
                `trendyol_category_id` = '" . (int)($data['trendyol_category_id'] ?? 0) . "',
                `trendyol_brand_id` = '" . (int)($data['trendyol_brand_id'] ?? 0) . "',
                `sync_status` = '" . $this->db->escape($data['sync_status'] ?? 'pending') . "',
                `last_sync` = " . ($data['last_sync'] ? "'" . $this->db->escape($data['last_sync']) . "'" : 'NULL') . ",
                `sync_error` = '" . $this->db->escape($data['sync_error'] ?? '') . "',
                `trendyol_data` = '" . $this->db->escape(json_encode($data['trendyol_data'] ?? [])) . "'
                WHERE product_id = '" . (int)$product_id . "'");
        } else {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_trendyol_products` SET 
                `product_id` = '" . (int)$product_id . "',
                `trendyol_product_id` = '" . $this->db->escape($data['trendyol_product_id']) . "',
                `trendyol_barcode` = '" . $this->db->escape($data['trendyol_barcode'] ?? '') . "',
                `trendyol_category_id` = '" . (int)($data['trendyol_category_id'] ?? 0) . "',
                `trendyol_brand_id` = '" . (int)($data['trendyol_brand_id'] ?? 0) . "',
                `sync_status` = '" . $this->db->escape($data['sync_status'] ?? 'pending') . "',
                `last_sync` = " . ($data['last_sync'] ? "'" . $this->db->escape($data['last_sync']) . "'" : 'NULL') . ",
                `sync_error` = '" . $this->db->escape($data['sync_error'] ?? '') . "',
                `trendyol_data` = '" . $this->db->escape(json_encode($data['trendyol_data'] ?? [])) . "'");
        }
    }
    
    /**
     * Get products for sync
     */
    public function getProductsForSync(int $limit = 50): array {
        $query = $this->db->query("SELECT p.*, tp.* FROM `" . DB_PREFIX . "product` p 
            LEFT JOIN `" . DB_PREFIX . "meschain_trendyol_products` tp ON (p.product_id = tp.product_id)
            WHERE p.status = '1' 
            AND (tp.sync_status IS NULL OR tp.sync_status = 'pending' OR tp.sync_status = 'error')
            ORDER BY p.date_modified DESC
            LIMIT " . (int)$limit);
        
        return $query->rows;
    }
    
    /**
     * Get sync statistics
     */
    public function getSyncStats(): array {
        $stats = [
            'total_products' => 0,
            'synced_products' => 0,
            'pending_products' => 0,
            'error_products' => 0,
            'total_orders' => 0,
            'synced_orders' => 0,
            'pending_orders' => 0,
            'error_orders' => 0
        ];
        
        // Product stats
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "product` WHERE status = '1'");
        $stats['total_products'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_trendyol_products` WHERE sync_status = 'synced'");
        $stats['synced_products'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_trendyol_products` WHERE sync_status = 'pending'");
        $stats['pending_products'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_trendyol_products` WHERE sync_status = 'error'");
        $stats['error_products'] = $query->row['total'];
        
        // Order stats
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_trendyol_orders`");
        $stats['total_orders'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_trendyol_orders` WHERE sync_status = 'synced'");
        $stats['synced_orders'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_trendyol_orders` WHERE sync_status = 'pending'");
        $stats['pending_orders'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_trendyol_orders` WHERE sync_status = 'error'");
        $stats['error_orders'] = $query->row['total'];
        
        return $stats;
    }
    
    /**
     * Get recent logs
     */
    public function getRecentLogs(int $limit = 10): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_trendyol_logs` 
            ORDER BY date_added DESC 
            LIMIT " . (int)$limit);
        
        return $query->rows;
    }
    
    /**
     * Add log entry
     */
    public function addLog(string $level, string $type, string $message, array $context = []): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_trendyol_logs` SET 
            `log_level` = '" . $this->db->escape($level) . "',
            `log_type` = '" . $this->db->escape($type) . "',
            `message` = '" . $this->db->escape($message) . "',
            `context` = '" . $this->db->escape(json_encode($context)) . "'");
    }
    
    /**
     * Clear logs
     */
    public function clearLogs(): void {
        $this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "meschain_trendyol_logs`");
    }
    
    /**
     * Test API connection
     */
    public function testConnection(string $api_key, string $api_secret, string $supplier_id): array {
        try {
            $this->load->library('meschain/api/trendyol');
            $api = new \MesChain\Api\Trendyol([
                'api_key' => $api_key,
                'api_secret' => $api_secret,
                'supplier_id' => $supplier_id,
                'test_mode' => true
            ]);
            
            $result = $api->testConnection();
            
            return [
                'success' => true,
                'message' => 'API connection successful!',
                'data' => $result
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'API connection failed: ' . $e->getMessage()
            ];
        }
    }
}
