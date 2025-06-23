<?php
/**
 * MesChain Mobile System Database Installer
 * 
 * @category   MesChain
 * @package    Mobile Helper
 * @author     MesChain Development Team
 * @copyright  2025 MesChain
 * @license    https://meschain.com/license
 * @version    1.0.0
 */

class MesChainMobileInstaller {
    
    private $db;
    private $log;
    
    public function __construct($db) {
        $this->db = $db;
        $this->log = new Log('meschain_mobile_installer.log');
    }
    
    /**
     * Install all mobile-related database tables
     */
    public function install() {
        try {
            $this->createUserDevicesTable();
            $this->createMobileApiLogsTable();
            $this->createPushNotificationsLogTable();
            $this->createNotificationTemplatesTable();
            $this->createDeviceNotificationSettingsTable();
            
            $this->log->write('All mobile tables created successfully');
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Mobile table creation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create user devices table
     */
    private function createUserDevicesTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_user_devices` (
                `device_id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `device_token` varchar(255) NOT NULL,
                `platform` enum('android','ios','web') NOT NULL,
                `app_version` varchar(50),
                `device_info` text,
                `status` tinyint(1) DEFAULT 1,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`device_id`),
                UNIQUE KEY `unique_user_device` (`user_id`, `device_token`),
                KEY `user_id` (`user_id`),
                KEY `platform` (`platform`),
                KEY `status` (`status`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create mobile API logs table
     */
    private function createMobileApiLogsTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_mobile_api_logs` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `endpoint` varchar(255) NOT NULL,
                `method` varchar(10) NOT NULL,
                `client_id` varchar(128),
                `user_id` int(11),
                `success` tinyint(1) DEFAULT 0,
                `response_time` decimal(8,2),
                `response_code` int(3),
                `request_data` text,
                `response_data` text,
                `ip_address` varchar(45),
                `user_agent` text,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`log_id`),
                KEY `endpoint` (`endpoint`),
                KEY `method` (`method`),
                KEY `client_id` (`client_id`),
                KEY `user_id` (`user_id`),
                KEY `success` (`success`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create push notifications log table
     */
    private function createPushNotificationsLogTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_push_notifications_log` (
                `notification_id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11),
                `device_id` int(11),
                `title` varchar(255) NOT NULL,
                `message` text NOT NULL,
                `data` text,
                `template_used` varchar(100),
                `platform` enum('android','ios','web'),
                `sent_count` int(11) DEFAULT 0,
                `total_devices` int(11) DEFAULT 0,
                `delivery_status` enum('pending','sent','delivered','failed') DEFAULT 'pending',
                `error_message` text,
                `fcm_message_id` varchar(255),
                `apns_message_id` varchar(255),
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `sent_at` timestamp NULL,
                `delivered_at` timestamp NULL,
                PRIMARY KEY (`notification_id`),
                KEY `user_id` (`user_id`),
                KEY `device_id` (`device_id`),
                KEY `platform` (`platform`),
                KEY `delivery_status` (`delivery_status`),
                KEY `template_used` (`template_used`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create notification templates table
     */
    private function createNotificationTemplatesTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_notification_templates` (
                `template_id` int(11) NOT NULL AUTO_INCREMENT,
                `template_name` varchar(100) NOT NULL,
                `title_template` varchar(255) NOT NULL,
                `message_template` text NOT NULL,
                `data_template` text,
                `category` varchar(50),
                `icon` varchar(100),
                `sound` varchar(100) DEFAULT 'default',
                `priority` enum('low','normal','high') DEFAULT 'normal',
                `status` tinyint(1) DEFAULT 1,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`template_id`),
                UNIQUE KEY `template_name` (`template_name`),
                KEY `category` (`category`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Insert default templates
        $this->insertDefaultTemplates();
    }
    
    /**
     * Create device notification settings table
     */
    private function createDeviceNotificationSettingsTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_device_notification_settings` (
                `setting_id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `device_id` int(11),
                `notification_type` varchar(50) NOT NULL,
                `enabled` tinyint(1) DEFAULT 1,
                `sound_enabled` tinyint(1) DEFAULT 1,
                `vibration_enabled` tinyint(1) DEFAULT 1,
                `led_enabled` tinyint(1) DEFAULT 1,
                `quiet_hours_start` time,
                `quiet_hours_end` time,
                `timezone` varchar(50) DEFAULT 'UTC',
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`setting_id`),
                UNIQUE KEY `unique_user_device_type` (`user_id`, `device_id`, `notification_type`),
                KEY `user_id` (`user_id`),
                KEY `device_id` (`device_id`),
                KEY `notification_type` (`notification_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Insert default notification templates
     */
    private function insertDefaultTemplates() {
        $templates = [
            [
                'template_name' => 'order_received',
                'title_template' => 'New Order Received',
                'message_template' => 'Order #{order_id} received from {marketplace} - Total: {total}',
                'data_template' => '{"type":"order","action":"view_order","order_id":"{order_id}"}',
                'category' => 'orders',
                'icon' => 'ic_shopping_cart',
                'priority' => 'high'
            ],
            [
                'template_name' => 'order_shipped',
                'title_template' => 'Order Shipped',
                'message_template' => 'Order #{order_id} has been shipped and is on its way',
                'data_template' => '{"type":"order","action":"track_order","order_id":"{order_id}"}',
                'category' => 'orders',
                'icon' => 'ic_local_shipping',
                'priority' => 'normal'
            ],
            [
                'template_name' => 'sync_completed',
                'title_template' => 'Sync Completed',
                'message_template' => '{marketplace} sync completed successfully - {products_synced} products updated',
                'data_template' => '{"type":"sync","action":"view_sync_report","marketplace":"{marketplace}"}',
                'category' => 'sync',
                'icon' => 'ic_sync',
                'priority' => 'normal'
            ],
            [
                'template_name' => 'sync_failed',
                'title_template' => 'Sync Failed',
                'message_template' => '{marketplace} sync failed: {error_message}',
                'data_template' => '{"type":"error","action":"view_errors","marketplace":"{marketplace}"}',
                'category' => 'errors',
                'icon' => 'ic_error',
                'priority' => 'high'
            ],
            [
                'template_name' => 'low_inventory',
                'title_template' => 'Low Inventory Alert',
                'message_template' => 'Product {product_name} is running low - Only {quantity} remaining',
                'data_template' => '{"type":"inventory","action":"view_product","product_id":"{product_id}"}',
                'category' => 'inventory',
                'icon' => 'ic_warning',
                'priority' => 'normal'
            ],
            [
                'template_name' => 'price_change',
                'title_template' => 'Price Change Detected',
                'message_template' => 'Price changed for {product_name} on {marketplace} - New price: {new_price}',
                'data_template' => '{"type":"price","action":"view_product","product_id":"{product_id}"}',
                'category' => 'pricing',
                'icon' => 'ic_trending_up',
                'priority' => 'normal'
            ],
            [
                'template_name' => 'system_alert',
                'title_template' => 'System Alert',
                'message_template' => '{message}',
                'data_template' => '{"type":"system","action":"view_dashboard"}',
                'category' => 'system',
                'icon' => 'ic_notification_important',
                'priority' => 'high'
            ],
            [
                'template_name' => 'daily_report',
                'title_template' => 'Daily Report',
                'message_template' => 'Today: {orders_count} orders, {revenue} revenue, {products_synced} products synced',
                'data_template' => '{"type":"report","action":"view_dashboard"}',
                'category' => 'reports',
                'icon' => 'ic_assessment',
                'priority' => 'low'
            ],
            [
                'template_name' => 'new_marketplace',
                'title_template' => 'New Marketplace Connected',
                'message_template' => '{marketplace} has been successfully connected and configured',
                'data_template' => '{"type":"marketplace","action":"view_marketplace","marketplace":"{marketplace}"}',
                'category' => 'marketplace',
                'icon' => 'ic_store',
                'priority' => 'normal'
            ],
            [
                'template_name' => 'api_limit_warning',
                'title_template' => 'API Limit Warning',
                'message_template' => 'You have reached {percentage}% of your {marketplace} API limit',
                'data_template' => '{"type":"api","action":"view_api_usage","marketplace":"{marketplace}"}',
                'category' => 'api',
                'icon' => 'ic_speed',
                'priority' => 'high'
            ]
        ];
        
        foreach ($templates as $template) {
            $this->db->query("
                INSERT IGNORE INTO " . DB_PREFIX . "meschain_notification_templates
                (template_name, title_template, message_template, data_template, category, icon, priority)
                VALUES (
                    '" . $this->db->escape($template['template_name']) . "',
                    '" . $this->db->escape($template['title_template']) . "',
                    '" . $this->db->escape($template['message_template']) . "',
                    '" . $this->db->escape($template['data_template']) . "',
                    '" . $this->db->escape($template['category']) . "',
                    '" . $this->db->escape($template['icon']) . "',
                    '" . $this->db->escape($template['priority']) . "'
                )
            ");
        }
    }
    
    /**
     * Uninstall all mobile tables
     */
    public function uninstall() {
        try {
            $tables = [
                'meschain_user_devices',
                'meschain_mobile_api_logs',
                'meschain_push_notifications_log',
                'meschain_notification_templates',
                'meschain_device_notification_settings'
            ];
            
            foreach ($tables as $table) {
                $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . $table . "`");
            }
            
            $this->log->write('All mobile tables removed successfully');
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Mobile table removal failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if mobile tables exist
     */
    public function checkInstallation() {
        $tables = [
            'meschain_user_devices',
            'meschain_mobile_api_logs',
            'meschain_push_notifications_log',
            'meschain_notification_templates'
        ];
        
        $missing_tables = [];
        
        foreach ($tables as $table) {
            $result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
            if ($result->num_rows == 0) {
                $missing_tables[] = $table;
            }
        }
        
        return [
            'installed' => empty($missing_tables),
            'missing_tables' => $missing_tables
        ];
    }
    
    /**
     * Update mobile database schema
     */
    public function updateSchema() {
        try {
            // Add new columns if they don't exist
            $this->addColumnIfNotExists('meschain_user_devices', 'last_active', 'timestamp NULL');
            $this->addColumnIfNotExists('meschain_push_notifications_log', 'retry_count', 'int(11) DEFAULT 0');
            $this->addColumnIfNotExists('meschain_mobile_api_logs', 'request_size', 'int(11) DEFAULT 0');
            $this->addColumnIfNotExists('meschain_mobile_api_logs', 'response_size', 'int(11) DEFAULT 0');
            
            $this->log->write('Mobile schema updated successfully');
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Mobile schema update failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add column if it doesn't exist
     */
    private function addColumnIfNotExists($table, $column, $definition) {
        $result = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . $table . "` LIKE '" . $column . "'");
        
        if ($result->num_rows == 0) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . $table . "` ADD COLUMN `" . $column . "` " . $definition);
        }
    }
    
    /**
     * Get mobile system statistics
     */
    public function getSystemStats() {
        $stats = [];
        
        // Check if tables exist
        $installation_status = $this->checkInstallation();
        $stats['installation_status'] = $installation_status;
        
        if ($installation_status['installed']) {
            // Get table sizes
            $stats['table_sizes'] = [];
            
            $tables = [
                'meschain_user_devices',
                'meschain_mobile_api_logs', 
                'meschain_push_notifications_log',
                'meschain_notification_templates'
            ];
            
            foreach ($tables as $table) {
                $result = $this->db->query("
                    SELECT 
                        COUNT(*) as row_count,
                        ROUND(((data_length + index_length) / 1024 / 1024), 2) as size_mb
                    FROM information_schema.tables 
                    WHERE table_schema = DATABASE() 
                    AND table_name = '" . DB_PREFIX . $table . "'
                ");
                
                if ($result->num_rows > 0) {
                    $stats['table_sizes'][$table] = $result->row;
                }
            }
        }
        
        return $stats;
    }
    
    /**
     * Clean old mobile data
     */
    public function cleanOldData($days = 30) {
        try {
            $cleaned = 0;
            
            // Clean old API logs
            $result = $this->db->query("
                DELETE FROM " . DB_PREFIX . "meschain_mobile_api_logs 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            ");
            $cleaned += $this->db->countAffected();
            
            // Clean old notification logs
            $result = $this->db->query("
                DELETE FROM " . DB_PREFIX . "meschain_push_notifications_log 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
                AND delivery_status IN ('delivered', 'failed')
            ");
            $cleaned += $this->db->countAffected();
            
            // Clean inactive devices (not updated for 90+ days)
            $result = $this->db->query("
                UPDATE " . DB_PREFIX . "meschain_user_devices 
                SET status = 0 
                WHERE updated_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
                AND status = 1
            ");
            $cleaned += $this->db->countAffected();
            
            $this->log->write("Mobile data cleanup completed. {$cleaned} records affected.");
            
            return [
                'success' => true,
                'cleaned_records' => $cleaned
            ];
            
        } catch (Exception $e) {
            $this->log->write('Mobile data cleanup failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} 