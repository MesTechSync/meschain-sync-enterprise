<?php
/**
 * MesChain-Sync Model
 * 
 * Ana model dosyası - tüm marketplace entegrasyonları için ortak model işlevleri
 * 
 * @category   Model
 * @package    MesChain-Sync
 * @version    2.5.0
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

class ModelExtensionModuleMeschainSync extends Model {
    
    /**
     * Install module and create necessary tables
     */
    public function install() {
        // Create category mapping table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_category_mapping` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `opencart_category_id` int(11) NOT NULL,
                `marketplace` varchar(50) NOT NULL,
                `marketplace_category_id` varchar(100) NOT NULL,
                `marketplace_category_name` varchar(255) NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_mapping` (`opencart_category_id`, `marketplace`),
                KEY `idx_marketplace` (`marketplace`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Create sync log table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_sync_log` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `marketplace` varchar(50) NOT NULL,
                `operation` varchar(100) NOT NULL,
                `product_id` int(11) DEFAULT NULL,
                `product_sku` varchar(100) DEFAULT NULL,
                `status` enum('success','error','warning') NOT NULL,
                `message` text,
                `response_data` text,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_marketplace` (`marketplace`),
                KEY `idx_product` (`product_id`),
                KEY `idx_status` (`status`),
                KEY `idx_created` (`created_at`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Create product mapping table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_product_mapping` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `marketplace` varchar(50) NOT NULL,
                `marketplace_product_id` varchar(100) NOT NULL,
                `marketplace_sku` varchar(100) DEFAULT NULL,
                `last_sync` datetime DEFAULT NULL,
                `sync_status` enum('pending','success','error') DEFAULT 'pending',
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_product_mapping` (`product_id`, `marketplace`),
                KEY `idx_marketplace` (`marketplace`),
                KEY `idx_sync_status` (`sync_status`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Create order mapping table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_order_mapping` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `opencart_order_id` int(11) NOT NULL,
                `marketplace` varchar(50) NOT NULL,
                `marketplace_order_id` varchar(100) NOT NULL,
                `last_sync` datetime DEFAULT NULL,
                `sync_status` enum('pending','success','error') DEFAULT 'pending',
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_order_mapping` (`opencart_order_id`, `marketplace`),
                KEY `idx_marketplace` (`marketplace`),
                KEY `idx_marketplace_order` (`marketplace_order_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Create settings table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `marketplace` varchar(50) NOT NULL,
                `setting_key` varchar(100) NOT NULL,
                `setting_value` text,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_setting` (`marketplace`, `setting_key`),
                KEY `idx_marketplace` (`marketplace`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Insert default settings
        $this->insertDefaultSettings();
    }
    
    /**
     * Uninstall module and remove tables
     */
    public function uninstall() {
        // Note: Tables are kept for data preservation
        // Only module settings are removed
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE code LIKE 'module_meschain_%'");
    }
    
    /**
     * Insert default settings for all marketplaces
     */
    private function insertDefaultSettings() {
        $marketplaces = ['amazon', 'ebay', 'hepsiburada', 'n11', 'ozon', 'trendyol'];
        
        foreach ($marketplaces as $marketplace) {
            $this->db->query("
                INSERT IGNORE INTO " . DB_PREFIX . "meschain_settings 
                (marketplace, setting_key, setting_value, created_at, updated_at)
                VALUES 
                ('" . $marketplace . "', 'auto_sync_enabled', '0', NOW(), NOW()),
                ('" . $marketplace . "', 'sync_interval', '60', NOW(), NOW()),
                ('" . $marketplace . "', 'default_category', '', NOW(), NOW()),
                ('" . $marketplace . "', 'default_shipping_time', '3', NOW(), NOW()),
                ('" . $marketplace . "', 'price_increase_rate', '0', NOW(), NOW())
            ");
        }
    }
    
    /**
     * Get sync statistics
     */
    public function getSyncStats() {
        $stats = [];
        
        // Total products synced
        $query = $this->db->query("
            SELECT marketplace, COUNT(*) as total_products
            FROM " . DB_PREFIX . "meschain_product_mapping 
            GROUP BY marketplace
        ");
        
        foreach ($query->rows as $row) {
            $stats[$row['marketplace']]['total_products'] = $row['total_products'];
        }
        
        // Success rate
        $query = $this->db->query("
            SELECT marketplace, sync_status, COUNT(*) as count
            FROM " . DB_PREFIX . "meschain_product_mapping 
            GROUP BY marketplace, sync_status
        ");
        
        foreach ($query->rows as $row) {
            $stats[$row['marketplace']][$row['sync_status']] = $row['count'];
        }
        
        // Recent sync activities
        $query = $this->db->query("
            SELECT marketplace, COUNT(*) as recent_syncs
            FROM " . DB_PREFIX . "meschain_sync_log 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY marketplace
        ");
        
        foreach ($query->rows as $row) {
            $stats[$row['marketplace']]['recent_syncs'] = $row['recent_syncs'];
        }
        
        return $stats;
    }
    
    /**
     * Log sync operation
     */
    public function logSync($marketplace, $operation, $product_id, $product_sku, $status, $message, $response_data = null) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_sync_log 
            (marketplace, operation, product_id, product_sku, status, message, response_data, created_at)
            VALUES (
                '" . $this->db->escape($marketplace) . "',
                '" . $this->db->escape($operation) . "',
                " . ($product_id ? "'" . (int)$product_id . "'" : "NULL") . ",
                " . ($product_sku ? "'" . $this->db->escape($product_sku) . "'" : "NULL") . ",
                '" . $this->db->escape($status) . "',
                '" . $this->db->escape($message) . "',
                " . ($response_data ? "'" . $this->db->escape(json_encode($response_data)) . "'" : "NULL") . ",
                NOW()
            )
        ");
    }
    
    /**
     * Get products for sync
     */
    public function getProductsForSync($marketplace, $limit = 100) {
        $query = $this->db->query("
            SELECT DISTINCT p.product_id, p.sku, p.model, pd.name, p.price, p.quantity, p.status
            FROM " . DB_PREFIX . "product p
            LEFT JOIN " . DB_PREFIX . "product_description pd ON p.product_id = pd.product_id
            LEFT JOIN " . DB_PREFIX . "meschain_product_mapping mpm ON p.product_id = mpm.product_id AND mpm.marketplace = '" . $this->db->escape($marketplace) . "'
            WHERE p.status = 1 
            AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            AND (mpm.last_sync IS NULL OR mpm.last_sync < DATE_SUB(NOW(), INTERVAL 1 HOUR))
            ORDER BY p.date_modified DESC
            LIMIT " . (int)$limit . "
        ");
        
        return $query->rows;
    }
    
    /**
     * Update product mapping
     */
    public function updateProductMapping($product_id, $marketplace, $marketplace_product_id, $marketplace_sku, $sync_status) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_product_mapping 
            (product_id, marketplace, marketplace_product_id, marketplace_sku, last_sync, sync_status, created_at, updated_at)
            VALUES (
                '" . (int)$product_id . "',
                '" . $this->db->escape($marketplace) . "',
                '" . $this->db->escape($marketplace_product_id) . "',
                '" . $this->db->escape($marketplace_sku) . "',
                NOW(),
                '" . $this->db->escape($sync_status) . "',
                NOW(),
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                marketplace_product_id = VALUES(marketplace_product_id),
                marketplace_sku = VALUES(marketplace_sku),
                last_sync = VALUES(last_sync),
                sync_status = VALUES(sync_status),
                updated_at = VALUES(updated_at)
        ");
    }
    
    /**
     * Get category mappings
     */
    public function getCategoryMappings($marketplace) {
        $query = $this->db->query("
            SELECT mcm.*, cd.name as opencart_category_name
            FROM " . DB_PREFIX . "meschain_category_mapping mcm
            LEFT JOIN " . DB_PREFIX . "category_description cd ON mcm.opencart_category_id = cd.category_id
            WHERE mcm.marketplace = '" . $this->db->escape($marketplace) . "'
            AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ORDER BY cd.name
        ");
        
        return $query->rows;
    }
    
    /**
     * Get recent sync logs
     */
    public function getRecentLogs($marketplace = null, $limit = 100) {
        $where = "";
        if ($marketplace) {
            $where = "WHERE marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        $query = $this->db->query("
            SELECT * 
            FROM " . DB_PREFIX . "meschain_sync_log 
            $where
            ORDER BY created_at DESC 
            LIMIT " . (int)$limit . "
        ");
        
        return $query->rows;
    }
    
    /**
     * Clean old logs (older than 30 days)
     */
    public function cleanOldLogs() {
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "meschain_sync_log 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        return $this->db->countAffected();
    }
    
    /**
     * Get marketplace settings
     */
    public function getMarketplaceSettings($marketplace) {
        $query = $this->db->query("
            SELECT setting_key, setting_value
            FROM " . DB_PREFIX . "meschain_settings 
            WHERE marketplace = '" . $this->db->escape($marketplace) . "'
        ");
        
        $settings = [];
        foreach ($query->rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }
    
    /**
     * Save marketplace settings
     */
    public function saveMarketplaceSettings($marketplace, $settings) {
        foreach ($settings as $key => $value) {
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "meschain_settings 
                (marketplace, setting_key, setting_value, created_at, updated_at)
                VALUES (
                    '" . $this->db->escape($marketplace) . "',
                    '" . $this->db->escape($key) . "',
                    '" . $this->db->escape($value) . "',
                    NOW(),
                    NOW()
                )
                ON DUPLICATE KEY UPDATE
                    setting_value = VALUES(setting_value),
                    updated_at = VALUES(updated_at)
            ");
        }
    }
    
    /**
     * Dashboard widget için istatistikleri getir
     * 
     * @return array Dashboard istatistikleri
     */
    public function getDashboardStats() {
        $stats = array();
        
        // Toplam entegre ürün sayısı (tüm marketplace'ler)
        $query = $this->db->query("SELECT COUNT(DISTINCT product_id) as total FROM " . DB_PREFIX . "meschain_product_mapping");
        $stats['total_products'] = $query->num_rows ? $query->row['total'] : 0;
        
        // Aktif marketplace sayısı
        $active_marketplaces = 0;
        $marketplaces = array('amazon', 'ebay', 'hepsiburada', 'n11', 'trendyol', 'ozon');
        foreach ($marketplaces as $marketplace) {
            if ($this->config->get('module_' . $marketplace . '_status')) {
                $active_marketplaces++;
            }
        }
        $stats['total_orders'] = $active_marketplaces;
        
        // Bu ay toplam senkronizasyon sayısı
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_sync_log WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
        $stats['total_sync'] = $query->num_rows ? $query->row['total'] : 0;
        
        // Son senkronizasyon tarihi
        $query = $this->db->query("SELECT MAX(created_at) as last_sync FROM " . DB_PREFIX . "meschain_sync_log");
        $stats['last_sync'] = ($query->num_rows && $query->row['last_sync']) ? 
            date('d.m.Y H:i', strtotime($query->row['last_sync'])) : 'Hiçbir zaman';
        
        // Sistem durumu (en az bir marketplace aktif mi)
        $stats['status'] = ($active_marketplaces > 0) ? 'connected' : 'error';
        
        // Son aktivite
        $query = $this->db->query("SELECT marketplace, operation FROM " . DB_PREFIX . "meschain_sync_log ORDER BY created_at DESC LIMIT 1");
        if ($query->num_rows) {
            $stats['recent_activity'] = 'Son işlem: ' . ucfirst($query->row['marketplace']) . ' - ' . $query->row['operation'];
        } else {
            $stats['recent_activity'] = 'Henüz aktivite yok';
        }
        
        return $stats;
    }
} 