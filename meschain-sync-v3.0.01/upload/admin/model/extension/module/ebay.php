<?php
/**
 * eBay Model
 * 
 * eBay marketplace integration model
 * 
 * @category   Model
 * @package    MesChain-Sync
 * @version    2.5.0
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

class ModelExtensionModuleEbay extends Model {
    
    /**
     * Install module tables
     */
    public function install() {
        // Create eBay products table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ebay_products` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `ebay_item_id` varchar(100) NOT NULL,
                `ebay_sku` varchar(100) DEFAULT NULL,
                `listing_type` varchar(50) DEFAULT 'FixedPriceItem',
                `status` enum('active','inactive','pending','error') DEFAULT 'pending',
                `quantity` int(11) DEFAULT '0',
                `price` decimal(15,4) DEFAULT '0.0000',
                `last_sync` datetime DEFAULT NULL,
                `sync_status` enum('pending','success','error') DEFAULT 'pending',
                `error_message` text,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `product_id` (`product_id`),
                KEY `ebay_item_id` (`ebay_item_id`),
                KEY `sync_status` (`sync_status`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Create eBay orders table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ebay_orders` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `ebay_order_id` varchar(100) NOT NULL,
                `opencart_order_id` int(11) DEFAULT NULL,
                `buyer_user_id` varchar(100) NOT NULL,
                `buyer_email` varchar(255) NOT NULL,
                `order_status` varchar(50) NOT NULL,
                `total_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
                `currency_id` varchar(10) NOT NULL DEFAULT 'USD',
                `shipping_amount` decimal(15,4) DEFAULT '0.0000',
                `tax_amount` decimal(15,4) DEFAULT '0.0000',
                `payment_method` varchar(100) DEFAULT NULL,
                `shipping_address` text,
                `billing_address` text,
                `order_data` text,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `ebay_order_id` (`ebay_order_id`),
                KEY `opencart_order_id` (`opencart_order_id`),
                KEY `order_status` (`order_status`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Create eBay categories table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ebay_categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `ebay_category_id` varchar(50) NOT NULL,
                `category_name` varchar(255) NOT NULL,
                `parent_id` varchar(50) DEFAULT NULL,
                `category_level` int(2) DEFAULT '1',
                `leaf_category` tinyint(1) DEFAULT '0',
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `ebay_category_id` (`ebay_category_id`),
                KEY `parent_id` (`parent_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        $this->writeLog('INSTALL', 'eBay model tables created successfully');
    }
    
    /**
     * Uninstall module
     */
    public function uninstall() {
        // Don't drop tables to preserve data
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE code LIKE 'module_ebay%'");
        $this->writeLog('UNINSTALL', 'eBay module settings removed');
    }
    
    /**
     * Get eBay products
     */
    public function getProducts($filter = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "ebay_products WHERE 1=1";
        
        if (isset($filter['status'])) {
            $sql .= " AND status = '" . $this->db->escape($filter['status']) . "'";
        }
        
        if (isset($filter['sync_status'])) {
            $sql .= " AND sync_status = '" . $this->db->escape($filter['sync_status']) . "'";
        }
        
        $sql .= " ORDER BY updated_at DESC";
        
        if (isset($filter['start']) && isset($filter['limit'])) {
            $sql .= " LIMIT " . (int)$filter['start'] . ", " . (int)$filter['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Add eBay product
     */
    public function addProduct($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "ebay_products SET
            product_id = '" . (int)$data['product_id'] . "',
            ebay_item_id = '" . $this->db->escape($data['ebay_item_id']) . "',
            ebay_sku = '" . $this->db->escape($data['ebay_sku'] ?? '') . "',
            listing_type = '" . $this->db->escape($data['listing_type'] ?? 'FixedPriceItem') . "',
            status = '" . $this->db->escape($data['status'] ?? 'pending') . "',
            quantity = '" . (int)($data['quantity'] ?? 0) . "',
            price = '" . (float)($data['price'] ?? 0) . "',
            sync_status = '" . $this->db->escape($data['sync_status'] ?? 'pending') . "',
            created_at = NOW(),
            updated_at = NOW()");
        
        $product_id = $this->db->getLastId();
        $this->writeLog('ADD_PRODUCT', 'eBay product added: ' . $data['ebay_item_id']);
        return $product_id;
    }
    
    /**
     * Update eBay product
     */
    public function updateProduct($id, $data) {
        $sql = "UPDATE " . DB_PREFIX . "ebay_products SET ";
        $updates = array();
        
        foreach ($data as $key => $value) {
            if (in_array($key, array('ebay_item_id', 'ebay_sku', 'listing_type', 'status', 'sync_status', 'error_message'))) {
                $updates[] = $key . " = '" . $this->db->escape($value) . "'";
            } elseif (in_array($key, array('quantity', 'product_id'))) {
                $updates[] = $key . " = '" . (int)$value . "'";
            } elseif (in_array($key, array('price'))) {
                $updates[] = $key . " = '" . (float)$value . "'";
            }
        }
        
        if (!empty($updates)) {
            $updates[] = "updated_at = NOW()";
            $sql .= implode(', ', $updates) . " WHERE id = '" . (int)$id . "'";
            $this->db->query($sql);
            
            $this->writeLog('UPDATE_PRODUCT', 'eBay product updated: ' . $id);
            return $this->db->countAffected() > 0;
        }
        
        return false;
    }
    
    /**
     * Get eBay orders
     */
    public function getOrders($filter = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "ebay_orders WHERE 1=1";
        
        if (isset($filter['order_status'])) {
            $sql .= " AND order_status = '" . $this->db->escape($filter['order_status']) . "'";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if (isset($filter['start']) && isset($filter['limit'])) {
            $sql .= " LIMIT " . (int)$filter['start'] . ", " . (int)$filter['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Dashboard statistics
     */
    public function getDashboardStats() {
        $stats = array();
        
        // Total products
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "ebay_products WHERE status IN ('active', 'pending')");
        $stats['total_products'] = $query->row['total'];
        
        // Total orders
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "ebay_orders");
        $stats['total_orders'] = $query->row['total'];
        
        // Sync count (this month)
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "ebay_products WHERE DATE(last_sync) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
        $stats['total_sync'] = $query->row['total'];
        
        // Last sync
        $query = $this->db->query("SELECT MAX(last_sync) as last_sync FROM " . DB_PREFIX . "ebay_products");
        $stats['last_sync'] = $query->row['last_sync'] ? date('d.m.Y H:i', strtotime($query->row['last_sync'])) : 'Hiçbir zaman';
        
        // API status check
        $api_status = $this->checkApiConnection();
        $stats['status'] = $api_status ? 'connected' : 'error';
        
        // Recent activity
        $query = $this->db->query("SELECT created_at FROM " . DB_PREFIX . "ebay_orders ORDER BY created_at DESC LIMIT 1");
        if ($query->num_rows) {
            $stats['recent_activity'] = 'Son sipariş: ' . date('d.m.Y H:i', strtotime($query->row['created_at']));
        } else {
            $stats['recent_activity'] = 'Henüz sipariş yok';
        }
        
        return $stats;
    }
    
    /**
     * Check API connection
     */
    private function checkApiConnection() {
        try {
            $app_id = $this->config->get('module_ebay_app_id');
            $dev_id = $this->config->get('module_ebay_dev_id');
            $cert_id = $this->config->get('module_ebay_cert_id');
            $user_token = $this->config->get('module_ebay_user_token');
            
            if (empty($app_id) || empty($dev_id) || empty($cert_id) || empty($user_token)) {
                return false;
            }
            
            // Simple API test call could be implemented here
            return true; // For now return true, real API test would be implemented with eBay helper
            
        } catch (Exception $e) {
            $this->writeLog('API_CHECK', 'eBay API connection check failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Write log
     */
    private function writeLog($action, $message) {
        $log_file = DIR_LOGS . 'ebay_model.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [MODEL] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 