<?php
/**
 * MesChain-Sync Enterprise - Trendyol Integration
 * Database Schema Installation Script
 * 
 * This script creates all necessary database tables for the Trendyol product import system.
 * Compatible with OpenCart 4.0.2.3 and MySQL 5.7+
 * 
 * @package    MesChain\Sync\Install
 * @author     MesChain Development Team
 * @version    1.0.0
 * @since      2025-01-21
 */

namespace MesChain\Sync\Install;

class DatabaseSchema {
    
    private $db;
    private $db_prefix;
    
    public function __construct($db, $db_prefix = '') {
        $this->db = $db;
        $this->db_prefix = $db_prefix;
    }
    
    /**
     * Install all required database tables
     * 
     * @return array Installation results
     */
    public function install() {
        $results = [];
        
        try {
            // Create tables in dependency order
            $results['import_sessions'] = $this->createImportSessionsTable();
            $results['import_products'] = $this->createImportProductsTable();
            $results['import_logs'] = $this->createImportLogsTable();
            $results['category_mapping'] = $this->createCategoryMappingTable();
            $results['brand_mapping'] = $this->createBrandMappingTable();
            $results['import_statistics'] = $this->createImportStatisticsTable();
            
            // Create indexes for performance
            $results['indexes'] = $this->createIndexes();
            
            // Insert default data
            $results['default_data'] = $this->insertDefaultData();
            
            return [
                'success' => true,
                'message' => 'Database schema installed successfully',
                'details' => $results
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Database installation failed: ' . $e->getMessage(),
                'details' => $results
            ];
        }
    }
    
    /**
     * Uninstall database tables
     * 
     * @return array Uninstallation results
     */
    public function uninstall() {
        $results = [];
        
        try {
            $tables = [
                'trendyol_import_statistics',
                'trendyol_import_logs',
                'trendyol_import_products',
                'trendyol_import_sessions',
                'trendyol_category_mapping',
                'trendyol_brand_mapping'
            ];
            
            foreach ($tables as $table) {
                $sql = "DROP TABLE IF EXISTS `" . $this->db_prefix . $table . "`";
                $this->db->query($sql);
                $results[$table] = 'Dropped successfully';
            }
            
            return [
                'success' => true,
                'message' => 'Database schema uninstalled successfully',
                'details' => $results
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Database uninstallation failed: ' . $e->getMessage(),
                'details' => $results
            ];
        }
    }
    
    /**
     * Create trendyol_import_sessions table
     */
    private function createImportSessionsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $this->db_prefix . "trendyol_import_sessions` (
            `session_id` int(11) NOT NULL AUTO_INCREMENT,
            `session_name` varchar(255) NOT NULL,
            `import_type` enum('all','category','brand','custom') NOT NULL DEFAULT 'all',
            `filter_data` text,
            `batch_size` int(11) NOT NULL DEFAULT 100,
            `total_products` int(11) NOT NULL DEFAULT 0,
            `processed_products` int(11) NOT NULL DEFAULT 0,
            `successful_imports` int(11) NOT NULL DEFAULT 0,
            `failed_imports` int(11) NOT NULL DEFAULT 0,
            `skipped_imports` int(11) NOT NULL DEFAULT 0,
            `status` enum('pending','running','paused','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
            `progress_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
            `current_batch` int(11) NOT NULL DEFAULT 0,
            `total_batches` int(11) NOT NULL DEFAULT 0,
            `start_time` datetime DEFAULT NULL,
            `end_time` datetime DEFAULT NULL,
            `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `settings` text,
            `error_message` text,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`session_id`),
            INDEX `idx_status` (`status`),
            INDEX `idx_created_at` (`created_at`),
            INDEX `idx_import_type` (`import_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Trendyol import sessions tracking';";
        
        $this->db->query($sql);
        return 'Import sessions table created successfully';
    }
    
    /**
     * Create trendyol_import_products table
     */
    private function createImportProductsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $this->db_prefix . "trendyol_import_products` (
            `import_product_id` int(11) NOT NULL AUTO_INCREMENT,
            `session_id` int(11) NOT NULL,
            `trendyol_product_id` varchar(100) NOT NULL,
            `opencart_product_id` int(11) DEFAULT NULL,
            `barcode` varchar(100) DEFAULT NULL,
            `title` varchar(500) NOT NULL,
            `brand` varchar(255) DEFAULT NULL,
            `category_name` varchar(255) DEFAULT NULL,
            `trendyol_category_id` int(11) DEFAULT NULL,
            `opencart_category_id` int(11) DEFAULT NULL,
            `sale_price` decimal(15,4) DEFAULT NULL,
            `list_price` decimal(15,4) DEFAULT NULL,
            `quantity` int(11) DEFAULT NULL,
            `images` text,
            `attributes` text,
            `description` text,
            `status` enum('pending','processing','completed','failed','skipped') NOT NULL DEFAULT 'pending',
            `import_status` enum('new','updated','skipped','error') DEFAULT NULL,
            `error_message` text,
            `trendyol_data` longtext,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`import_product_id`),
            INDEX `idx_session_id` (`session_id`),
            INDEX `idx_trendyol_product_id` (`trendyol_product_id`),
            INDEX `idx_opencart_product_id` (`opencart_product_id`),
            INDEX `idx_barcode` (`barcode`),
            INDEX `idx_status` (`status`),
            INDEX `idx_import_status` (`import_status`),
            CONSTRAINT `fk_import_products_session` FOREIGN KEY (`session_id`) REFERENCES `" . $this->db_prefix . "trendyol_import_sessions` (`session_id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Imported products tracking';";
        
        $this->db->query($sql);
        return 'Import products table created successfully';
    }
    
    /**
     * Create trendyol_import_logs table
     */
    private function createImportLogsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $this->db_prefix . "trendyol_import_logs` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `session_id` int(11) DEFAULT NULL,
            `import_product_id` int(11) DEFAULT NULL,
            `level` enum('debug','info','warning','error','critical') NOT NULL DEFAULT 'info',
            `message` text NOT NULL,
            `context` text,
            `file` varchar(255) DEFAULT NULL,
            `line` int(11) DEFAULT NULL,
            `memory_usage` int(11) DEFAULT NULL,
            `execution_time` decimal(10,6) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`log_id`),
            INDEX `idx_session_id` (`session_id`),
            INDEX `idx_import_product_id` (`import_product_id`),
            INDEX `idx_level` (`level`),
            INDEX `idx_created_at` (`created_at`),
            CONSTRAINT `fk_import_logs_session` FOREIGN KEY (`session_id`) REFERENCES `" . $this->db_prefix . "trendyol_import_sessions` (`session_id`) ON DELETE SET NULL ON UPDATE CASCADE,
            CONSTRAINT `fk_import_logs_product` FOREIGN KEY (`import_product_id`) REFERENCES `" . $this->db_prefix . "trendyol_import_products` (`import_product_id`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Import process logs';";
        
        $this->db->query($sql);
        return 'Import logs table created successfully';
    }
    
    /**
     * Create trendyol_category_mapping table
     */
    private function createCategoryMappingTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $this->db_prefix . "trendyol_category_mapping` (
            `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
            `trendyol_category_id` int(11) NOT NULL,
            `trendyol_category_name` varchar(255) NOT NULL,
            `opencart_category_id` int(11) NOT NULL,
            `opencart_category_name` varchar(255) NOT NULL,
            `auto_mapped` tinyint(1) NOT NULL DEFAULT 0,
            `confidence_score` decimal(3,2) DEFAULT NULL,
            `mapping_rules` text,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`mapping_id`),
            UNIQUE KEY `unique_trendyol_category` (`trendyol_category_id`),
            INDEX `idx_opencart_category_id` (`opencart_category_id`),
            INDEX `idx_auto_mapped` (`auto_mapped`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Category mapping between Trendyol and OpenCart';";
        
        $this->db->query($sql);
        return 'Category mapping table created successfully';
    }
    
    /**
     * Create trendyol_brand_mapping table
     */
    private function createBrandMappingTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $this->db_prefix . "trendyol_brand_mapping` (
            `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
            `trendyol_brand_id` int(11) NOT NULL,
            `trendyol_brand_name` varchar(255) NOT NULL,
            `opencart_manufacturer_id` int(11) NOT NULL,
            `opencart_manufacturer_name` varchar(255) NOT NULL,
            `auto_mapped` tinyint(1) NOT NULL DEFAULT 0,
            `confidence_score` decimal(3,2) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`mapping_id`),
            UNIQUE KEY `unique_trendyol_brand` (`trendyol_brand_id`),
            INDEX `idx_opencart_manufacturer_id` (`opencart_manufacturer_id`),
            INDEX `idx_auto_mapped` (`auto_mapped`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Brand mapping between Trendyol and OpenCart';";
        
        $this->db->query($sql);
        return 'Brand mapping table created successfully';
    }
    
    /**
     * Create trendyol_import_statistics table
     */
    private function createImportStatisticsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $this->db_prefix . "trendyol_import_statistics` (
            `stat_id` int(11) NOT NULL AUTO_INCREMENT,
            `session_id` int(11) NOT NULL,
            `metric_name` varchar(100) NOT NULL,
            `metric_value` decimal(15,6) NOT NULL,
            `metric_type` enum('counter','gauge','timer','percentage') NOT NULL DEFAULT 'counter',
            `metric_unit` varchar(50) DEFAULT NULL,
            `recorded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`stat_id`),
            INDEX `idx_session_id` (`session_id`),
            INDEX `idx_metric_name` (`metric_name`),
            INDEX `idx_recorded_at` (`recorded_at`),
            CONSTRAINT `fk_import_statistics_session` FOREIGN KEY (`session_id`) REFERENCES `" . $this->db_prefix . "trendyol_import_sessions` (`session_id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Import performance statistics';";
        
        $this->db->query($sql);
        return 'Import statistics table created successfully';
    }
    
    /**
     * Create additional indexes for performance optimization
     */
    private function createIndexes() {
        $indexes = [
            // Composite indexes for common queries
            "CREATE INDEX `idx_session_status_created` ON `" . $this->db_prefix . "trendyol_import_sessions` (`status`, `created_at`)",
            "CREATE INDEX `idx_products_session_status` ON `" . $this->db_prefix . "trendyol_import_products` (`session_id`, `status`)",
            "CREATE INDEX `idx_logs_session_level_created` ON `" . $this->db_prefix . "trendyol_import_logs` (`session_id`, `level`, `created_at`)",
            
            // Full-text search indexes
            "CREATE FULLTEXT INDEX `idx_products_search` ON `" . $this->db_prefix . "trendyol_import_products` (`title`, `brand`, `category_name`)",
            "CREATE FULLTEXT INDEX `idx_logs_search` ON `" . $this->db_prefix . "trendyol_import_logs` (`message`)"
        ];
        
        $results = [];
        foreach ($indexes as $index_sql) {
            try {
                $this->db->query($index_sql);
                $results[] = 'Index created successfully';
            } catch (\Exception $e) {
                // Some indexes might already exist, ignore duplicate key errors
                if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                    $results[] = 'Index creation failed: ' . $e->getMessage();
                } else {
                    $results[] = 'Index already exists';
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Insert default data
     */
    private function insertDefaultData() {
        $results = [];
        
        try {
            // Insert default category mappings (common categories)
            $default_mappings = [
                ['trendyol_id' => 1, 'trendyol_name' => 'Elektronik', 'opencart_id' => 1, 'opencart_name' => 'Components', 'auto_mapped' => 1, 'confidence' => 0.95],
                ['trendyol_id' => 2, 'trendyol_name' => 'Moda', 'opencart_id' => 2, 'opencart_name' => 'Tablets', 'auto_mapped' => 1, 'confidence' => 0.90],
                ['trendyol_id' => 3, 'trendyol_name' => 'Ev & Yaşam', 'opencart_id' => 3, 'opencart_name' => 'Software', 'auto_mapped' => 1, 'confidence' => 0.85]
            ];
            
            foreach ($default_mappings as $mapping) {
                $sql = "INSERT IGNORE INTO `" . $this->db_prefix . "trendyol_category_mapping` 
                        (`trendyol_category_id`, `trendyol_category_name`, `opencart_category_id`, `opencart_category_name`, `auto_mapped`, `confidence_score`) 
                        VALUES ({$mapping['trendyol_id']}, '{$mapping['trendyol_name']}', {$mapping['opencart_id']}, '{$mapping['opencart_name']}', {$mapping['auto_mapped']}, {$mapping['confidence']})";
                $this->db->query($sql);
            }
            
            $results['category_mappings'] = 'Default category mappings inserted';
            
            return $results;
            
        } catch (\Exception $e) {
            $results['error'] = 'Default data insertion failed: ' . $e->getMessage();
            return $results;
        }
    }
    
    /**
     * Check if tables exist and are properly configured
     */
    public function checkInstallation() {
        $required_tables = [
            'trendyol_import_sessions',
            'trendyol_import_products', 
            'trendyol_import_logs',
            'trendyol_category_mapping',
            'trendyol_brand_mapping',
            'trendyol_import_statistics'
        ];
        
        $results = [];
        
        foreach ($required_tables as $table) {
            $sql = "SHOW TABLES LIKE '" . $this->db_prefix . $table . "'";
            $result = $this->db->query($sql);
            
            if ($result->num_rows > 0) {
                // Check table structure
                $sql = "DESCRIBE `" . $this->db_prefix . $table . "`";
                $structure = $this->db->query($sql);
                $results[$table] = [
                    'exists' => true,
                    'columns' => $structure->num_rows,
                    'status' => 'OK'
                ];
            } else {
                $results[$table] = [
                    'exists' => false,
                    'status' => 'MISSING'
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Get database schema version
     */
    public function getSchemaVersion() {
        return '1.0.0';
    }
    
    /**
     * Upgrade database schema if needed
     */
    public function upgrade($from_version, $to_version) {
        // Future upgrade logic will be implemented here
        return [
            'success' => true,
            'message' => 'No upgrades needed for version ' . $to_version
        ];
    }
}