<?php
/**
 * MesChain Sync Enterprise - Native OpenCart 4.x Installer
 * Version: 2.0.0
 * Compatibility: OpenCart 4.0.2.3+
 * 
 * This installer uses native OpenCart 4.x systems without OCMOD dependency
 */

// Check OpenCart version compatibility
if (!defined('VERSION') || version_compare(VERSION, '4.0.0.0', '<')) {
    throw new Exception('OpenCart 4.0.0.0+ required for MesChain Sync Enterprise');
}

class MeschainInstaller {
    private $registry;
    private $db;
    private $config;
    private $cache;
    private $log;
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->cache = $registry->get('cache');
        $this->log = $registry->get('log');
    }
    
    /**
     * Main installation process
     */
    public function install() {
        try {
            $this->log->write('MesChain Installation Started');
            
            // Pre-installation checks
            $this->preInstallationChecks();
            
            // Database installation
            $this->installDatabase();
            
            // Event registration (native OpenCart 4.x)
            $this->registerEvents();
            
            // Permission setup
            $this->setupPermissions();
            
            // Configuration defaults
            $this->setupDefaults();
            
            // Menu integration (native approach)
            $this->setupMenuIntegration();
            
            // Clear caches
            $this->clearCaches();
            
            // Post-installation verification
            $this->verifyInstallation();
            
            $this->log->write('MesChain Installation Completed Successfully');
            
            return [
                'success' => true, 
                'message' => 'MesChain Sync Enterprise installed successfully',
                'version' => '2.0.0'
            ];
            
        } catch (Exception $e) {
            $this->log->write('MesChain Installation Error: ' . $e->getMessage());
            
            // Rollback on error
            $this->rollback();
            
            return [
                'success' => false, 
                'message' => 'Installation failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Pre-installation system checks
     */
    private function preInstallationChecks() {
        // Check PHP version
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            throw new Exception('PHP 8.0+ required. Current version: ' . PHP_VERSION);
        }
        
        // Check MySQL version
        $mysql_version = $this->db->query("SELECT VERSION() as version")->row['version'];
        if (version_compare($mysql_version, '8.0.0', '<')) {
            throw new Exception('MySQL 8.0+ required. Current version: ' . $mysql_version);
        }
        
        // Check required PHP extensions
        $required_extensions = ['json', 'curl', 'openssl', 'mbstring'];
        foreach ($required_extensions as $extension) {
            if (!extension_loaded($extension)) {
                throw new Exception('Required PHP extension missing: ' . $extension);
            }
        }
        
        // Check directory permissions
        $write_dirs = [
            DIR_LOGS,
            DIR_CACHE,
            DIR_STORAGE
        ];
        
        foreach ($write_dirs as $dir) {
            if (!is_writable($dir)) {
                throw new Exception('Directory not writable: ' . $dir);
            }
        }
    }
    
    /**
     * Database schema installation with MySQL 8.0 features
     */
    private function installDatabase() {
        $sql_statements = [
            // Extension registry
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_registry` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `extension_code` varchar(64) NOT NULL,
                `version` varchar(16) NOT NULL,
                `status` tinyint(1) NOT NULL DEFAULT 1,
                `installed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_extension_code` (`extension_code`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Marketplace configurations with JSON
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_marketplaces` (
                `marketplace_id` int(11) NOT NULL AUTO_INCREMENT,
                `store_id` int(11) NOT NULL DEFAULT 0,
                `code` varchar(32) NOT NULL,
                `name` varchar(64) NOT NULL,
                `api_url` varchar(255) NOT NULL,
                `api_credentials` json,
                `settings` json,
                `status` tinyint(1) NOT NULL DEFAULT 1,
                `last_sync` timestamp NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`marketplace_id`),
                UNIQUE KEY `idx_store_code` (`store_id`, `code`),
                KEY `idx_status` (`status`),
                KEY `idx_last_sync` (`last_sync`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Product mappings
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_product_mappings` (
                `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `marketplace_id` int(11) NOT NULL,
                `external_id` varchar(255) NOT NULL,
                `external_sku` varchar(255),
                `mapping_data` json,
                `sync_status` enum('pending', 'synced', 'error', 'disabled') NOT NULL DEFAULT 'pending',
                `last_sync` timestamp NULL,
                `error_message` text,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`mapping_id`),
                UNIQUE KEY `idx_product_marketplace` (`product_id`, `marketplace_id`),
                UNIQUE KEY `idx_external_marketplace` (`external_id`, `marketplace_id`),
                KEY `idx_sync_status` (`sync_status`),
                KEY `idx_last_sync` (`last_sync`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Order mappings
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_order_mappings` (
                `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
                `order_id` int(11) NOT NULL,
                `marketplace_id` int(11) NOT NULL,
                `external_order_id` varchar(255) NOT NULL,
                `external_order_number` varchar(255),
                `order_data` json,
                `sync_status` enum('pending', 'synced', 'error') NOT NULL DEFAULT 'pending',
                `last_sync` timestamp NULL,
                `error_message` text,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`mapping_id`),
                UNIQUE KEY `idx_order_marketplace` (`order_id`, `marketplace_id`),
                UNIQUE KEY `idx_external_marketplace` (`external_order_id`, `marketplace_id`),
                KEY `idx_sync_status` (`sync_status`),
                KEY `idx_last_sync` (`last_sync`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Sync logs with partitioning
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_sync_logs` (
                `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
                `marketplace_id` int(11),
                `entity_type` enum('product', 'order', 'inventory', 'category') NOT NULL,
                `entity_id` int(11),
                `action` enum('create', 'update', 'delete', 'sync') NOT NULL,
                `status` enum('success', 'error', 'warning') NOT NULL,
                `message` text,
                `request_data` json,
                `response_data` json,
                `execution_time` decimal(8,3),
                `memory_usage` int(11),
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`log_id`),
                KEY `idx_marketplace_entity` (`marketplace_id`, `entity_type`),
                KEY `idx_status_created` (`status`, `created_at`),
                KEY `idx_entity_id` (`entity_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];
        
        foreach ($sql_statements as $sql) {
            $this->db->query($sql);
        }
        
        // Insert default data
        $this->insertDefaultData();
    }
    
    /**
     * Insert default marketplace configurations
     */
    private function insertDefaultData() {
        // Register extension
        $this->db->query("
            INSERT IGNORE INTO `" . DB_PREFIX . "meschain_registry` 
            (`extension_code`, `version`, `status`) 
            VALUES ('meschain_sync_enterprise', '2.0.0', 1)
        ");
        
        // Default marketplaces
        $marketplaces = [
            [
                'code' => 'trendyol',
                'name' => 'Trendyol',
                'api_url' => 'https://api.trendyol.com',
                'settings' => json_encode([
                    'api_version' => 'v2',
                    'sandbox_url' => 'https://sandbox-api.trendyol.com',
                    'rate_limit' => 100,
                    'timeout' => 30
                ])
            ],
            [
                'code' => 'amazon',
                'name' => 'Amazon TR',
                'api_url' => 'https://mws.amazonservices.com',
                'settings' => json_encode([
                    'api_version' => 'v1',
                    'regions' => ['TR', 'EU'],
                    'rate_limit' => 50,
                    'timeout' => 45
                ])
            ]
        ];
        
        foreach ($marketplaces as $marketplace) {
            $this->db->query("
                INSERT IGNORE INTO `" . DB_PREFIX . "meschain_marketplaces` 
                (`code`, `name`, `api_url`, `settings`, `status`) 
                VALUES (
                    '" . $this->db->escape($marketplace['code']) . "',
                    '" . $this->db->escape($marketplace['name']) . "',
                    '" . $this->db->escape($marketplace['api_url']) . "',
                    '" . $this->db->escape($marketplace['settings']) . "',
                    0
                )
            ");
        }
    }
    
    /**
     * Register native OpenCart 4.x events
     */
    private function registerEvents() {
        $events = [
            [
                'code' => 'meschain_product_sync',
                'trigger' => 'admin/model/catalog/product/editProduct/after',
                'action' => 'extension/meschain/event/product|sync'
            ],
            [
                'code' => 'meschain_order_sync',
                'trigger' => 'admin/model/sale/order/editOrder/after',
                'action' => 'extension/meschain/event/order|sync'
            ],
            [
                'code' => 'meschain_product_delete',
                'trigger' => 'admin/model/catalog/product/deleteProduct/before',
                'action' => 'extension/meschain/event/product|delete'
            ]
        ];
        
        foreach ($events as $event) {
            $this->db->query("
                INSERT IGNORE INTO `" . DB_PREFIX . "event` 
                SET `code` = '" . $this->db->escape($event['code']) . "',
                    `trigger` = '" . $this->db->escape($event['trigger']) . "',
                    `action` = '" . $this->db->escape($event['action']) . "',
                    `status` = 1,
                    `sort_order` = 1
            ");
        }
    }
    
    /**
     * Setup permissions for user groups
     */
    private function setupPermissions() {
        $permissions = [
            'extension/meschain/dashboard',
            'extension/meschain/trendyol',
            'extension/meschain/products',
            'extension/meschain/orders',
            'extension/meschain/settings',
            'extension/meschain/analytics',
            'extension/meschain/logs'
        ];
        
        // Get administrator user group
        $user_groups = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user_group` WHERE `name` = 'Administrator'")->rows;
        
        foreach ($user_groups as $user_group) {
            $existing_permissions = json_decode($user_group['permission'], true) ?: ['access' => [], 'modify' => []];
            
            foreach ($permissions as $permission) {
                if (!in_array($permission, $existing_permissions['access'])) {
                    $existing_permissions['access'][] = $permission;
                }
                if (!in_array($permission, $existing_permissions['modify'])) {
                    $existing_permissions['modify'][] = $permission;
                }
            }
            
            $this->db->query("
                UPDATE `" . DB_PREFIX . "user_group` 
                SET `permission` = '" . $this->db->escape(json_encode($existing_permissions)) . "'
                WHERE `user_group_id` = '" . (int)$user_group['user_group_id'] . "'
            ");
        }
    }
    
    /**
     * Setup default configuration
     */
    private function setupDefaults() {
        $defaults = [
            'meschain_status' => '1',
            'meschain_version' => '2.0.0',
            'meschain_api_timeout' => '30',
            'meschain_sync_interval' => '300',
            'meschain_debug_mode' => '0',
            'meschain_log_level' => 'info',
            'meschain_batch_size' => '50',
            'meschain_memory_limit' => '256M',
            'meschain_max_execution_time' => '300'
        ];
        
        foreach ($defaults as $key => $value) {
            $this->db->query("
                INSERT IGNORE INTO `" . DB_PREFIX . "setting` 
                SET `store_id` = 0,
                    `code` = 'meschain',
                    `key` = '" . $this->db->escape($key) . "',
                    `value` = '" . $this->db->escape($value) . "',
                    `serialized` = 0
            ");
        }
    }
    
    /**
     * Setup native menu integration
     */
    private function setupMenuIntegration() {
        // Enable extension status for menu display
        $this->db->query("
            INSERT IGNORE INTO `" . DB_PREFIX . "setting` 
            SET `store_id` = 0,
                `code` = 'module_meschain',
                `key` = 'module_meschain_status',
                `value` = '1',
                `serialized` = 0
        ");
        
        // Create extension entry point
        $this->db->query("
            INSERT IGNORE INTO `" . DB_PREFIX . "extension` 
            SET `type` = 'module',
                `code` = 'meschain'
        ");
    }
    
    /**
     * Clear OpenCart caches
     */
    private function clearCaches() {
        $cache_types = [
            'cache.currency.*',
            'cache.language.*',
            'cache.product.*',
            'cache.category.*',
            'cache.manufacturer.*',
            'cache.setting.*'
        ];
        
        foreach ($cache_types as $cache_type) {
            $this->cache->delete($cache_type);
        }
    }
    
    /**
     * Verify successful installation
     */
    private function verifyInstallation() {
        // Check if tables exist
        $required_tables = [
            DB_PREFIX . 'meschain_registry',
            DB_PREFIX . 'meschain_marketplaces',
            DB_PREFIX . 'meschain_product_mappings',
            DB_PREFIX . 'meschain_order_mappings',
            DB_PREFIX . 'meschain_sync_logs'
        ];
        
        foreach ($required_tables as $table) {
            $result = $this->db->query("SHOW TABLES LIKE '" . $table . "'");
            if (!$result->num_rows) {
                throw new Exception('Required table not created: ' . $table);
            }
        }
        
        // Check if events are registered
        $event_count = $this->db->query("
            SELECT COUNT(*) as count 
            FROM `" . DB_PREFIX . "event` 
            WHERE `code` LIKE 'meschain_%'
        ")->row['count'];
        
        if ($event_count < 3) {
            throw new Exception('Events not properly registered');
        }
        
        // Check if settings are saved
        $setting_count = $this->db->query("
            SELECT COUNT(*) as count 
            FROM `" . DB_PREFIX . "setting` 
            WHERE `code` = 'meschain'
        ")->row['count'];
        
        if ($setting_count < 5) {
            throw new Exception('Settings not properly saved');
        }
    }
    
    /**
     * Rollback installation on error
     */
    private function rollback() {
        try {
            // Remove events
            $this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` LIKE 'meschain_%'");
            
            // Remove settings
            $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'meschain'");
            
            // Remove extension entry
            $this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `code` = 'meschain'");
            
            // Drop tables
            $tables = [
                DB_PREFIX . 'meschain_sync_logs',
                DB_PREFIX . 'meschain_order_mappings',
                DB_PREFIX . 'meschain_product_mappings',
                DB_PREFIX . 'meschain_marketplaces',
                DB_PREFIX . 'meschain_registry'
            ];
            
            foreach ($tables as $table) {
                $this->db->query("DROP TABLE IF EXISTS `" . $table . "`");
            }
            
        } catch (Exception $e) {
            $this->log->write('MesChain Rollback Error: ' . $e->getMessage());
        }
    }
}

// Main installation execution
try {
    $installer = new MeschainInstaller($registry);
    $result = $installer->install();
    
    if ($result['success']) {
        echo json_encode($result);
    } else {
        http_response_code(500);
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Installation failed: ' . $e->getMessage()
    ]);
}
?>
