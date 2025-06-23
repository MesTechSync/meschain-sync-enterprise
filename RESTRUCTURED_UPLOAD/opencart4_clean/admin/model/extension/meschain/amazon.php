<?php
namespace Opencart\Admin\Model\Extension\MesChain;

/**
 * MesChain Sync Amazon Model
 * 
 * @package    MesChain Sync
 * @version    2.0.0
 * @author     MesChain Development Team
 * @copyright  2024 MesChain
 * @license    MIT License
 */

class Amazon extends \Opencart\System\Engine\Model {
    
    /**
     * Create Amazon tables
     */
    public function createTables(): void {
        // Amazon products mapping table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_amazon_products` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `amazon_sku` varchar(255) NOT NULL,
            `amazon_asin` varchar(255) DEFAULT NULL,
            `amazon_product_id` varchar(255) DEFAULT NULL,
            `amazon_product_id_type` varchar(50) DEFAULT 'SKU',
            `sync_status` enum('pending','synced','error') DEFAULT 'pending',
            `last_sync` datetime DEFAULT NULL,
            `sync_error` text DEFAULT NULL,
            `amazon_data` longtext DEFAULT NULL,
            `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_modified` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `product_id` (`product_id`),
            INDEX `amazon_sku` (`amazon_sku`),
            INDEX `amazon_asin` (`amazon_asin`),
            INDEX `sync_status` (`sync_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Amazon orders mapping table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_amazon_orders` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `amazon_order_id` varchar(255) NOT NULL,
            `amazon_order_reference_id` varchar(255) DEFAULT NULL,
            `sync_status` enum('pending','synced','error') DEFAULT 'pending',
            `last_sync` datetime DEFAULT NULL,
            `sync_error` text DEFAULT NULL,
            `amazon_data` longtext DEFAULT NULL,
            `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_modified` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `order_id` (`order_id`),
            INDEX `amazon_order_id` (`amazon_order_id`),
            INDEX `sync_status` (`sync_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Amazon sync logs table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_amazon_logs` (
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
     * Drop Amazon tables
     */
    public function dropTables(): void {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_amazon_products`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_amazon_orders`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_amazon_logs`");
    }
    
    /**
     * Test API connection
     */
    public function testConnection(string $access_key, string $secret_key, string $seller_id, string $marketplace_id, string $region): array {
        try {
            $this->load->library('meschain/api/amazon');
            $api = new \MesChain\Api\Amazon([
                'access_key' => $access_key,
                'secret_key' => $secret_key,
                'seller_id' => $seller_id,
                'marketplace_id' => $marketplace_id,
                'region' => $region,
                'test_mode' => true
            ]);
            
            $result = $api->testConnection();
            
            return [
                'success' => true,
                'message' => 'Amazon SP-API connection successful!',
                'data' => $result
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Amazon SP-API connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Clear logs
     */
    public function clearLogs(): void {
        $this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "meschain_amazon_logs`");
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
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_amazon_products` WHERE sync_status = 'synced'");
        $stats['synced_products'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_amazon_products` WHERE sync_status = 'pending'");
        $stats['pending_products'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_amazon_products` WHERE sync_status = 'error'");
        $stats['error_products'] = $query->row['total'];
        
        // Order stats
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_amazon_orders`");
        $stats['total_orders'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_amazon_orders` WHERE sync_status = 'synced'");
        $stats['synced_orders'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_amazon_orders` WHERE sync_status = 'pending'");
        $stats['pending_orders'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_amazon_orders` WHERE sync_status = 'error'");
        $stats['error_orders'] = $query->row['total'];
        
        return $stats;
    }
}
