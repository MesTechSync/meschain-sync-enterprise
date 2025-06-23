<?php
/**
 * Base Marketplace Model
 * MesChain-Sync OpenCart Extension
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0
 * @author MesChain Development Team
 */

class ModelExtensionModuleBaseMarketplace extends Model {
    
    private $marketplace_table = 'meschain_marketplace_settings';
    private $order_table = 'meschain_marketplace_orders';
    private $product_table = 'meschain_marketplace_products';
    private $log_table = 'meschain_marketplace_logs';
    
    /**
     * Initialize base marketplace tables
     *
     * @return void
     */
    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->marketplace_table . "` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `marketplace` varchar(50) NOT NULL,
                `setting_key` varchar(100) NOT NULL,
                `setting_value` text,
                `status` tinyint(1) DEFAULT 1,
                `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `marketplace_setting` (`marketplace`, `setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->order_table . "` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `marketplace` varchar(50) NOT NULL,
                `marketplace_order_id` varchar(100) NOT NULL,
                `opencart_order_id` int(11) DEFAULT NULL,
                `status` varchar(50) DEFAULT 'pending',
                `total` decimal(15,4) DEFAULT 0.0000,
                `currency` varchar(3) DEFAULT 'TRY',
                `customer_data` json,
                `product_data` json,
                `sync_status` enum('pending', 'synced', 'failed') DEFAULT 'pending',
                `error_message` text,
                `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `marketplace_order` (`marketplace`, `marketplace_order_id`),
                KEY `opencart_order_id` (`opencart_order_id`),
                KEY `sync_status` (`sync_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->product_table . "` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `marketplace` varchar(50) NOT NULL,
                `opencart_product_id` int(11) NOT NULL,
                `marketplace_product_id` varchar(100),
                `sku` varchar(100),
                `barcode` varchar(50),
                `sync_status` enum('pending', 'synced', 'failed') DEFAULT 'pending',
                `last_sync` datetime,
                `error_message` text,
                `stock_quantity` int(11) DEFAULT 0,
                `price` decimal(15,4) DEFAULT 0.0000,
                `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `marketplace_product` (`marketplace`, `marketplace_product_id`),
                KEY `opencart_product` (`opencart_product_id`),
                KEY `sync_status` (`sync_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->log_table . "` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `marketplace` varchar(50) NOT NULL,
                `level` enum('info', 'warning', 'error', 'debug') DEFAULT 'info',
                `message` text NOT NULL,
                `context` json,
                `ip_address` varchar(45),
                `user_id` int(11),
                `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `marketplace_level` (`marketplace`, `level`),
                KEY `date_added` (`date_added`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }
    
    /**
     * Get marketplace setting
     *
     * @param string $marketplace Marketplace name
     * @param string $key Setting key
     * @return mixed Setting value or null
     */
    public function getMarketplaceSetting($marketplace, $key) {
        $query = $this->db->query("
            SELECT setting_value 
            FROM " . DB_PREFIX . $this->marketplace_table . " 
            WHERE marketplace = '" . $this->db->escape($marketplace) . "' 
            AND setting_key = '" . $this->db->escape($key) . "'
            AND status = 1
        ");
        
        if ($query->num_rows) {
            $value = $query->row['setting_value'];
            // Try to decode JSON, return as is if not JSON
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }
        
        return null;
    }
    
    /**
     * Set marketplace setting
     *
     * @param string $marketplace Marketplace name
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool
     */
    public function setMarketplaceSetting($marketplace, $key, $value) {
        try {
            $setting_value = is_array($value) || is_object($value) ? json_encode($value) : $value;
            
            $this->db->query("
                INSERT INTO " . DB_PREFIX . $this->marketplace_table . " 
                (marketplace, setting_key, setting_value) 
                VALUES (
                    '" . $this->db->escape($marketplace) . "', 
                    '" . $this->db->escape($key) . "', 
                    '" . $this->db->escape($setting_value) . "'
                )
                ON DUPLICATE KEY UPDATE 
                setting_value = VALUES(setting_value),
                date_modified = CURRENT_TIMESTAMP
            ");
            
            return true;
        } catch (Exception $e) {
            $this->log('error', 'Failed to set marketplace setting', [
                'marketplace' => $marketplace,
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Log marketplace activity
     *
     * @param string $level Log level (info, warning, error, debug)
     * @param string $message Log message
     * @param array $context Additional context data
     * @param string $marketplace Marketplace name
     * @return bool
     */
    public function log($level, $message, $context = [], $marketplace = 'system') {
        try {
            $ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
            $user_id = isset($this->session->data['user_id']) ? $this->session->data['user_id'] : null;
            
            $this->db->query("
                INSERT INTO " . DB_PREFIX . $this->log_table . " 
                (marketplace, level, message, context, ip_address, user_id) 
                VALUES (
                    '" . $this->db->escape($marketplace) . "',
                    '" . $this->db->escape($level) . "',
                    '" . $this->db->escape($message) . "',
                    '" . $this->db->escape(json_encode($context)) . "',
                    '" . $this->db->escape($ip_address) . "',
                    " . (int)$user_id . "
                )
            ");
            
            return true;
        } catch (Exception $e) {
            error_log("MesChain Log Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get marketplace orders
     *
     * @param array $filters Filter options
     * @return array Orders data
     */
    public function getMarketplaceOrders($filters = []) {
        $sql = "SELECT * FROM " . DB_PREFIX . $this->order_table;
        $where = [];
        
        if (!empty($filters['marketplace'])) {
            $where[] = "marketplace = '" . $this->db->escape($filters['marketplace']) . "'";
        }
        
        if (!empty($filters['status'])) {
            $where[] = "status = '" . $this->db->escape($filters['status']) . "'";
        }
        
        if (!empty($filters['sync_status'])) {
            $where[] = "sync_status = '" . $this->db->escape($filters['sync_status']) . "'";
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "date_added >= '" . $this->db->escape($filters['date_from']) . "'";
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "date_added <= '" . $this->db->escape($filters['date_to']) . "'";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY date_added DESC";
        
        if (isset($filters['limit']) && $filters['limit'] > 0) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            
            if (isset($filters['offset']) && $filters['offset'] > 0) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get marketplace products
     *
     * @param array $filters Filter options
     * @return array Products data
     */
    public function getMarketplaceProducts($filters = []) {
        $sql = "SELECT mp.*, p.name as product_name, p.model, p.sku as opencart_sku 
                FROM " . DB_PREFIX . $this->product_table . " mp
                LEFT JOIN " . DB_PREFIX . "product p ON mp.opencart_product_id = p.product_id";
        
        $where = [];
        
        if (!empty($filters['marketplace'])) {
            $where[] = "mp.marketplace = '" . $this->db->escape($filters['marketplace']) . "'";
        }
        
        if (!empty($filters['sync_status'])) {
            $where[] = "mp.sync_status = '" . $this->db->escape($filters['sync_status']) . "'";
        }
        
        if (!empty($filters['product_id'])) {
            $where[] = "mp.opencart_product_id = " . (int)$filters['product_id'];
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY mp.date_modified DESC";
        
        if (isset($filters['limit']) && $filters['limit'] > 0) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Update product sync status
     *
     * @param string $marketplace Marketplace name
     * @param int $product_id OpenCart product ID
     * @param string $status Sync status
     * @param string $error_message Error message if any
     * @return bool
     */
    public function updateProductSyncStatus($marketplace, $product_id, $status, $error_message = '') {
        try {
            $this->db->query("
                UPDATE " . DB_PREFIX . $this->product_table . " 
                SET sync_status = '" . $this->db->escape($status) . "',
                    last_sync = NOW(),
                    error_message = '" . $this->db->escape($error_message) . "'
                WHERE marketplace = '" . $this->db->escape($marketplace) . "'
                AND opencart_product_id = " . (int)$product_id
            );
            
            return true;
        } catch (Exception $e) {
            $this->log('error', 'Failed to update product sync status', [
                'marketplace' => $marketplace,
                'product_id' => $product_id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get system statistics
     *
     * @return array Statistics data
     */
    public function getSystemStats() {
        $stats = [
            'total_orders' => 0,
            'pending_orders' => 0,
            'synced_orders' => 0,
            'failed_orders' => 0,
            'total_products' => 0,
            'synced_products' => 0,
            'failed_products' => 0,
            'marketplaces' => []
        ];
        
        // Order statistics
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN sync_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN sync_status = 'synced' THEN 1 ELSE 0 END) as synced,
                SUM(CASE WHEN sync_status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM " . DB_PREFIX . $this->order_table
        );
        
        if ($query->num_rows) {
            $row = $query->row;
            $stats['total_orders'] = (int)$row['total'];
            $stats['pending_orders'] = (int)$row['pending'];
            $stats['synced_orders'] = (int)$row['synced'];
            $stats['failed_orders'] = (int)$row['failed'];
        }
        
        // Product statistics
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN sync_status = 'synced' THEN 1 ELSE 0 END) as synced,
                SUM(CASE WHEN sync_status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM " . DB_PREFIX . $this->product_table
        );
        
        if ($query->num_rows) {
            $row = $query->row;
            $stats['total_products'] = (int)$row['total'];
            $stats['synced_products'] = (int)$row['synced'];
            $stats['failed_products'] = (int)$row['failed'];
        }
        
        // Marketplace statistics
        $query = $this->db->query("
            SELECT marketplace, COUNT(*) as count
            FROM " . DB_PREFIX . $this->order_table
            GROUP BY marketplace
        ");
        
        foreach ($query->rows as $row) {
            $stats['marketplaces'][$row['marketplace']] = (int)$row['count'];
        }
        
        return $stats;
    }
    
    /**
     * Clean old logs
     *
     * @param int $days Days to keep logs
     * @return bool
     */
    public function cleanOldLogs($days = 30) {
        try {
            $this->db->query("
                DELETE FROM " . DB_PREFIX . $this->log_table . " 
                WHERE date_added < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            ");
            
            $this->log('info', 'Cleaned old logs', [
                'days_kept' => $days,
                'affected_rows' => $this->db->countAffected()
            ]);
            
            return true;
        } catch (Exception $e) {
            $this->log('error', 'Failed to clean old logs', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}