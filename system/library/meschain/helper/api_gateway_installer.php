<?php
/**
 * MesChain API Gateway Database Installer
 * 
 * @category   MesChain
 * @package    API Gateway Helper
 * @author     MesChain Development Team
 * @copyright  2025 MesChain
 * @license    https://meschain.com/license
 * @version    1.0.0
 */

class MesChainApiGatewayInstaller {
    
    private $db;
    private $log;
    
    public function __construct($db) {
        $this->db = $db;
        $this->log = new Log('meschain_api_gateway_installer.log');
    }
    
    /**
     * Install API Gateway database tables
     */
    public function install() {
        try {
            $this->createClientsTable();
            $this->createApiKeysTable();
            $this->createApiMetricsTable();
            $this->createApiRequestsTable();
            $this->createRateLimitTable();
            
            $this->log->write('API Gateway database tables installed successfully');
            return true;
            
        } catch (Exception $e) {
            $this->log->write('API Gateway installation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create clients table
     */
    private function createClientsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_clients` (
            `client_id` int(11) NOT NULL AUTO_INCREMENT,
            `company_name` varchar(255) NOT NULL,
            `contact_email` varchar(255) DEFAULT NULL,
            `plan_type` enum('default','premium','enterprise') DEFAULT 'default',
            `monthly_quota` int(11) DEFAULT 10000,
            `current_usage` int(11) DEFAULT 0,
            `status` tinyint(1) DEFAULT 1,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`client_id`),
            KEY `idx_plan_type` (`plan_type`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->query($sql);
        $this->log->write('Created meschain_clients table');
    }
    
    /**
     * Create API keys table
     */
    private function createApiKeysTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_api_keys` (
            `api_key_id` int(11) NOT NULL AUTO_INCREMENT,
            `client_id` int(11) NOT NULL,
            `api_key` varchar(64) NOT NULL,
            `api_secret` varchar(128) DEFAULT NULL,
            `permissions` text DEFAULT NULL,
            `rate_limit_override` json DEFAULT NULL,
            `last_used_at` timestamp NULL DEFAULT NULL,
            `usage_count` bigint(20) DEFAULT 0,
            `expires_at` timestamp NULL DEFAULT NULL,
            `status` tinyint(1) DEFAULT 1,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`api_key_id`),
            UNIQUE KEY `unique_api_key` (`api_key`),
            KEY `idx_client_id` (`client_id`),
            KEY `idx_status` (`status`),
            KEY `idx_expires_at` (`expires_at`),
            FOREIGN KEY (`client_id`) REFERENCES `" . DB_PREFIX . "meschain_clients` (`client_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->query($sql);
        $this->log->write('Created meschain_api_keys table');
    }
    
    /**
     * Create API metrics table
     */
    private function createApiMetricsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_api_metrics` (
            `metric_id` bigint(20) NOT NULL AUTO_INCREMENT,
            `request_id` varchar(64) NOT NULL,
            `client_id` int(11) DEFAULT NULL,
            `api_key_id` int(11) DEFAULT NULL,
            `service` varchar(50) NOT NULL,
            `endpoint` varchar(255) NOT NULL,
            `method` varchar(10) NOT NULL,
            `http_status` int(11) DEFAULT NULL,
            `processing_time` decimal(10,6) NOT NULL,
            `request_size` int(11) DEFAULT NULL,
            `response_size` int(11) DEFAULT NULL,
            `success` tinyint(1) NOT NULL DEFAULT 0,
            `error_message` text DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`metric_id`),
            UNIQUE KEY `unique_request_id` (`request_id`),
            KEY `idx_client_id` (`client_id`),
            KEY `idx_service` (`service`),
            KEY `idx_success` (`success`),
            KEY `idx_created_at` (`created_at`),
            KEY `idx_processing_time` (`processing_time`),
            FOREIGN KEY (`client_id`) REFERENCES `" . DB_PREFIX . "meschain_clients` (`client_id`) ON DELETE SET NULL,
            FOREIGN KEY (`api_key_id`) REFERENCES `" . DB_PREFIX . "meschain_api_keys` (`api_key_id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->query($sql);
        $this->log->write('Created meschain_api_metrics table');
    }
    
    /**
     * Create API requests table
     */
    private function createApiRequestsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_api_requests` (
            `request_log_id` bigint(20) NOT NULL AUTO_INCREMENT,
            `request_id` varchar(64) NOT NULL,
            `api_key` varchar(64) NOT NULL,
            `endpoint` varchar(255) NOT NULL,
            `method` varchar(10) NOT NULL,
            `request_headers` json DEFAULT NULL,
            `request_body` longtext DEFAULT NULL,
            `response_headers` json DEFAULT NULL,
            `response_body` longtext DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`request_log_id`),
            KEY `idx_request_id` (`request_id`),
            KEY `idx_api_key` (`api_key`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->query($sql);
        $this->log->write('Created meschain_api_requests table');
    }
    
    /**
     * Create rate limit table
     */
    private function createRateLimitTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_rate_limits` (
            `rate_limit_id` bigint(20) NOT NULL AUTO_INCREMENT,
            `identifier` varchar(128) NOT NULL,
            `limit_type` enum('minute','hour','day') NOT NULL,
            `request_count` int(11) NOT NULL DEFAULT 0,
            `window_start` timestamp NOT NULL,
            `expires_at` timestamp NOT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`rate_limit_id`),
            UNIQUE KEY `unique_identifier_type_window` (`identifier`, `limit_type`, `window_start`),
            KEY `idx_expires_at` (`expires_at`),
            KEY `idx_identifier` (`identifier`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->query($sql);
        $this->log->write('Created meschain_rate_limits table');
    }
    
    /**
     * Insert sample data
     */
    public function insertSampleData() {
        try {
            // Insert sample client
            $this->db->query("
                INSERT IGNORE INTO `" . DB_PREFIX . "meschain_clients` 
                (`client_id`, `company_name`, `contact_email`, `plan_type`, `monthly_quota`, `status`) 
                VALUES 
                (1, 'MesChain Demo', 'demo@meschain.com', 'enterprise', 100000, 1),
                (2, 'Test Client', 'test@example.com', 'premium', 50000, 1),
                (3, 'Basic Client', 'basic@example.com', 'default', 10000, 1)
            ");
            
            // Insert sample API keys
            $this->db->query("
                INSERT IGNORE INTO `" . DB_PREFIX . "meschain_api_keys` 
                (`api_key_id`, `client_id`, `api_key`, `permissions`, `expires_at`, `status`) 
                VALUES 
                (1, 1, 'mk_demo_enterprise_key_123456789', 'all', DATE_ADD(NOW(), INTERVAL 1 YEAR), 1),
                (2, 2, 'mk_premium_client_key_987654321', 'marketplace,analytics', DATE_ADD(NOW(), INTERVAL 1 YEAR), 1),
                (3, 3, 'mk_basic_client_key_456789123', 'marketplace', DATE_ADD(NOW(), INTERVAL 1 YEAR), 1)
            ");
            
            $this->log->write('Sample data inserted successfully');
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Failed to insert sample data: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Uninstall API Gateway tables
     */
    public function uninstall() {
        try {
            $tables = [
                'meschain_rate_limits',
                'meschain_api_requests', 
                'meschain_api_metrics',
                'meschain_api_keys',
                'meschain_clients'
            ];
            
            foreach ($tables as $table) {
                $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . $table . "`");
                $this->log->write('Dropped table: ' . $table);
            }
            
            $this->log->write('API Gateway tables uninstalled successfully');
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Uninstall failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get installation status
     */
    public function getInstallationStatus() {
        $tables = [
            'meschain_clients',
            'meschain_api_keys', 
            'meschain_api_metrics',
            'meschain_api_requests',
            'meschain_rate_limits'
        ];
        
        $status = [];
        
        foreach ($tables as $table) {
            $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
            $status[$table] = $query->num_rows > 0;
        }
        
        return $status;
    }
    
    /**
     * Update database schema
     */
    public function updateSchema() {
        try {
            // Add any schema updates here for future versions
            $this->log->write('Schema updated successfully');
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Schema update failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Optimize tables
     */
    public function optimizeTables() {
        try {
            $tables = [
                'meschain_clients',
                'meschain_api_keys',
                'meschain_api_metrics', 
                'meschain_api_requests',
                'meschain_rate_limits'
            ];
            
            foreach ($tables as $table) {
                $this->db->query("OPTIMIZE TABLE `" . DB_PREFIX . $table . "`");
            }
            
            $this->log->write('Tables optimized successfully');
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Table optimization failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean old data
     */
    public function cleanOldData($days = 30) {
        try {
            // Clean old metrics (older than specified days)
            $this->db->query("
                DELETE FROM `" . DB_PREFIX . "meschain_api_metrics` 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            ");
            
            // Clean old rate limit entries
            $this->db->query("
                DELETE FROM `" . DB_PREFIX . "meschain_rate_limits` 
                WHERE expires_at < NOW()
            ");
            
            // Clean old request logs (older than 7 days)
            $this->db->query("
                DELETE FROM `" . DB_PREFIX . "meschain_api_requests` 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            
            $this->log->write('Old data cleaned successfully');
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Data cleanup failed: ' . $e->getMessage());
            return false;
        }
    }
} 