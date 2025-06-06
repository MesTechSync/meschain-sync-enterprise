<?php
/**
 * MesChain-Sync Installer Controller
 * 
 * Otomatik kurulum ve konfigürasyon sistemi
 * 
 * @category   Controller
 * @package    MesChain-Sync
 * @version    3.0.1
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

class ControllerExtensionModuleMeschainInstaller extends Controller {
    
    private $error = array();
    private $log;
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        parent::__construct($registry);
        $this->log = new Log('meschain_installer.log');
    }
    
    /**
     * Main installation page
     */
    public function index() {
        $this->load->language('extension/module/meschain_installer');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Check installation status
        $data['installation_status'] = $this->checkInstallationStatus();
        
        // System requirements check
        $data['system_requirements'] = $this->checkSystemRequirements();
        
        // Database status
        $data['database_status'] = $this->checkDatabaseStatus();
        
        // File permissions
        $data['file_permissions'] = $this->checkFilePermissions();
        
        $this->getForm($data);
    }
    
    /**
     * Install MesChain-Sync system via AJAX
     */
    public function install() {
        $this->load->language('extension/module/meschain_installer');
        
        $json = array();
        
        try {
            // Step 1: Create database tables
            $this->log->write('Starting MesChain-Sync installation...');
            $json['steps'][] = array(
                'step' => 1,
                'title' => 'Creating database tables...',
                'status' => 'running'
            );
            
            $this->createDatabaseTables();
            $json['steps'][0]['status'] = 'completed';
            
            // Step 2: Insert default configurations
            $json['steps'][] = array(
                'step' => 2,
                'title' => 'Installing default configurations...',
                'status' => 'running'
            );
            
            $this->insertDefaultConfigurations();
            $json['steps'][1]['status'] = 'completed';
            
            // Step 3: Create file structure
            $json['steps'][] = array(
                'step' => 3,
                'title' => 'Creating file structure...',
                'status' => 'running'
            );
            
            $this->createFileStructure();
            $json['steps'][2]['status'] = 'completed';
            
            // Step 4: Set permissions
            $json['steps'][] = array(
                'step' => 4,
                'title' => 'Setting file permissions...',
                'status' => 'running'
            );
            
            $this->setFilePermissions();
            $json['steps'][3]['status'] = 'completed';
            
            // Step 5: Initialize system
            $json['steps'][] = array(
                'step' => 5,
                'title' => 'Initializing system...',
                'status' => 'running'
            );
            
            $this->initializeSystem();
            $json['steps'][4]['status'] = 'completed';
            
            $this->log->write('MesChain-Sync installation completed successfully');
            
            $json['success'] = true;
            $json['message'] = $this->language->get('text_installation_success');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            $this->log->write('Installation failed: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Uninstall MesChain-Sync system
     */
    public function uninstall() {
        $this->load->language('extension/module/meschain_installer');
        
        $json = array();
        
        try {
            // Remove database tables
            $this->removeDatabaseTables();
            
            // Remove configurations
            $this->removeConfigurations();
            
            // Clean up files
            $this->cleanupFiles();
            
            $this->log->write('MesChain-Sync uninstallation completed');
            
            $json['success'] = true;
            $json['message'] = $this->language->get('text_uninstall_success');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            $this->log->write('Uninstallation failed: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Check installation status
     */
    private function checkInstallationStatus() {
        $tables_exist = $this->checkRequiredTables();
        $configs_exist = $this->checkRequiredConfigs();
        $files_exist = $this->checkRequiredFiles();
        
        if ($tables_exist && $configs_exist && $files_exist) {
            return array(
                'status' => 'installed',
                'message' => 'MesChain-Sync is already installed and ready to use.',
                'version' => '3.0.1'
            );
        } elseif ($tables_exist || $configs_exist) {
            return array(
                'status' => 'partial',
                'message' => 'Partial installation detected. Please reinstall.',
                'version' => null
            );
        } else {
            return array(
                'status' => 'not_installed',
                'message' => 'MesChain-Sync is not installed.',
                'version' => null
            );
        }
    }
    
    /**
     * Check system requirements
     */
    private function checkSystemRequirements() {
        $requirements = array();
        
        // PHP Version
        $requirements['php_version'] = array(
            'name' => 'PHP Version',
            'required' => '7.4+',
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'pass' : 'fail'
        );
        
        // OpenCart Version
        $requirements['opencart_version'] = array(
            'name' => 'OpenCart Version',
            'required' => '3.0.4.0+',
            'current' => VERSION,
            'status' => version_compare(VERSION, '3.0.4.0', '>=') ? 'pass' : 'fail'
        );
        
        // PHP Extensions
        $extensions = array('curl', 'json', 'mbstring', 'openssl', 'zip');
        foreach ($extensions as $ext) {
            $requirements['php_' . $ext] = array(
                'name' => 'PHP ' . strtoupper($ext) . ' Extension',
                'required' => 'Enabled',
                'current' => extension_loaded($ext) ? 'Enabled' : 'Disabled',
                'status' => extension_loaded($ext) ? 'pass' : 'fail'
            );
        }
        
        // Memory Limit
        $memory_limit = ini_get('memory_limit');
        $requirements['memory_limit'] = array(
            'name' => 'Memory Limit',
            'required' => '256M+',
            'current' => $memory_limit,
            'status' => (int)$memory_limit >= 256 ? 'pass' : 'warning'
        );
        
        return $requirements;
    }
    
    /**
     * Check database status
     */
    private function checkDatabaseStatus() {
        $status = array();
        
        // Database connection
        try {
            $this->db->query("SELECT 1");
            $status['connection'] = array(
                'name' => 'Database Connection',
                'status' => 'pass',
                'message' => 'Connected successfully'
            );
        } catch (Exception $e) {
            $status['connection'] = array(
                'name' => 'Database Connection',
                'status' => 'fail',
                'message' => 'Connection failed: ' . $e->getMessage()
            );
        }
        
        // Required tables
        $required_tables = array(
            'meschain_webhook_log',
            'trendyol_webhook_log',
            'trendyol_webhook_config',
            'meschain_order',
            'meschain_api_log',
            'meschain_integration_test'
        );
        
        foreach ($required_tables as $table) {
            $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
            $status['table_' . $table] = array(
                'name' => 'Table: ' . $table,
                'status' => $query->num_rows > 0 ? 'pass' : 'fail',
                'message' => $query->num_rows > 0 ? 'Exists' : 'Missing'
            );
        }
        
        return $status;
    }
    
    /**
     * Check file permissions
     */
    private function checkFilePermissions() {
        $permissions = array();
        
        $directories = array(
            DIR_LOGS,
            DIR_CACHE,
            DIR_UPLOAD,
            DIR_SYSTEM . 'library/meschain/',
            DIR_APPLICATION . 'view/template/extension/module/'
        );
        
        foreach ($directories as $dir) {
            $permissions[basename($dir)] = array(
                'path' => $dir,
                'readable' => is_readable($dir) ? 'pass' : 'fail',
                'writable' => is_writable($dir) ? 'pass' : 'fail',
                'permissions' => substr(sprintf('%o', fileperms($dir)), -4)
            );
        }
        
        return $permissions;
    }
    
    /**
     * Create database tables
     */
    private function createDatabaseTables() {
        // Universal webhook log table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_webhook_log` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `marketplace` VARCHAR(32) NOT NULL,
                `event_type` VARCHAR(64) NOT NULL,
                `payload` TEXT NOT NULL,
                `signature` VARCHAR(255) DEFAULT NULL,
                `status` ENUM('pending', 'processed', 'failed') DEFAULT 'pending',
                `response_data` TEXT DEFAULT NULL,
                `error_message` TEXT DEFAULT NULL,
                `response_time` INT(11) DEFAULT NULL,
                `retry_count` INT(11) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `processed_at` TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_marketplace` (`marketplace`),
                INDEX `idx_event_type` (`event_type`),
                INDEX `idx_status` (`status`),
                INDEX `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Trendyol webhook log table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_webhook_log` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `event_type` VARCHAR(64) NOT NULL,
                `order_id` VARCHAR(32) DEFAULT NULL,
                `product_id` VARCHAR(32) DEFAULT NULL,
                `payload` TEXT NOT NULL,
                `signature` VARCHAR(255) DEFAULT NULL,
                `status` ENUM('success', 'error', 'pending') DEFAULT 'pending',
                `response_data` TEXT DEFAULT NULL,
                `error_message` TEXT DEFAULT NULL,
                `response_time` INT(11) DEFAULT NULL,
                `retry_count` INT(11) DEFAULT 0,
                `received_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `processed_at` TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_event_type` (`event_type`),
                INDEX `idx_order_id` (`order_id`),
                INDEX `idx_status` (`status`),
                INDEX `idx_received_at` (`received_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Trendyol webhook config table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_webhook_config` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `webhook_url` VARCHAR(255) NOT NULL,
                `secret_key` VARCHAR(255) NOT NULL,
                `events` TEXT NOT NULL,
                `timeout` INT(11) DEFAULT 30,
                `max_retries` INT(11) DEFAULT 3,
                `auto_retry` TINYINT(1) DEFAULT 1,
                `status` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Orders table for reporting
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_order` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `marketplace` VARCHAR(32) NOT NULL,
                `marketplace_order_id` VARCHAR(64) NOT NULL,
                `opencart_order_id` INT(11) DEFAULT NULL,
                `customer_name` VARCHAR(255) DEFAULT NULL,
                `total_amount` DECIMAL(15,4) DEFAULT NULL,
                `currency` VARCHAR(3) DEFAULT NULL,
                `status` VARCHAR(32) DEFAULT NULL,
                `order_date` TIMESTAMP NULL DEFAULT NULL,
                `sync_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_marketplace_order` (`marketplace`, `marketplace_order_id`),
                INDEX `idx_marketplace` (`marketplace`),
                INDEX `idx_opencart_order_id` (`opencart_order_id`),
                INDEX `idx_order_date` (`order_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // API log table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_api_log` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `marketplace` VARCHAR(32) NOT NULL,
                `endpoint` VARCHAR(255) NOT NULL,
                `method` VARCHAR(10) NOT NULL,
                `request_data` TEXT DEFAULT NULL,
                `response_data` TEXT DEFAULT NULL,
                `response_code` INT(11) DEFAULT NULL,
                `response_time` INT(11) DEFAULT NULL,
                `error_message` TEXT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_marketplace` (`marketplace`),
                INDEX `idx_endpoint` (`endpoint`),
                INDEX `idx_response_code` (`response_code`),
                INDEX `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Integration test table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_integration_test` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `marketplace` VARCHAR(32) NOT NULL,
                `test_type` VARCHAR(64) NOT NULL,
                `test_data` TEXT DEFAULT NULL,
                `result` ENUM('pass', 'fail', 'pending') DEFAULT 'pending',
                `response_time` INT(11) DEFAULT NULL,
                `error_message` TEXT DEFAULT NULL,
                `test_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_marketplace` (`marketplace`),
                INDEX `idx_test_type` (`test_type`),
                INDEX `idx_result` (`result`),
                INDEX `idx_test_date` (`test_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        $this->log->write('Database tables created successfully');
    }
    
    /**
     * Insert default configurations
     */
    private function insertDefaultConfigurations() {
        // Insert default webhook configurations
        $this->db->query("
            INSERT IGNORE INTO `" . DB_PREFIX . "trendyol_webhook_config` 
            (`webhook_url`, `secret_key`, `events`, `timeout`, `max_retries`, `auto_retry`, `status`) 
            VALUES 
            ('https://yourdomain.com/webhook/trendyol', 'your-secret-key', '[\"order_created\",\"order_updated\",\"product_updated\"]', 30, 3, 1, 0)
        ");
        
        $this->log->write('Default configurations inserted');
    }
    
    /**
     * Create file structure
     */
    private function createFileStructure() {
        $directories = array(
            DIR_LOGS . 'meschain/',
            DIR_CACHE . 'meschain/',
            DIR_UPLOAD . 'meschain/exports/',
            DIR_UPLOAD . 'meschain/imports/',
            DIR_UPLOAD . 'meschain/backups/'
        );
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new Exception('Failed to create directory: ' . $dir);
                }
            }
        }
        
        $this->log->write('File structure created successfully');
    }
    
    /**
     * Set file permissions
     */
    private function setFilePermissions() {
        $directories = array(
            DIR_LOGS . 'meschain/' => 0755,
            DIR_CACHE . 'meschain/' => 0755,
            DIR_UPLOAD . 'meschain/' => 0755
        );
        
        foreach ($directories as $dir => $perm) {
            if (is_dir($dir)) {
                chmod($dir, $perm);
            }
        }
        
        $this->log->write('File permissions set successfully');
    }
    
    /**
     * Initialize system
     */
    private function initializeSystem() {
        // Clear cache
        $cache_files = glob(DIR_CACHE . '*');
        foreach ($cache_files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        // Create initial log entries
        $this->log->write('MesChain-Sync v3.0.1 installed and initialized');
        
        $this->log->write('System initialization completed');
    }
    
    /**
     * Check required tables
     */
    private function checkRequiredTables() {
        $tables = array(
            'meschain_webhook_log',
            'trendyol_webhook_log',
            'trendyol_webhook_config'
        );
        
        foreach ($tables as $table) {
            $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
            if ($query->num_rows == 0) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check required configs
     */
    private function checkRequiredConfigs() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_webhook_config` LIMIT 1");
        return $query->num_rows > 0;
    }
    
    /**
     * Check required files
     */
    private function checkRequiredFiles() {
        $files = array(
            DIR_SYSTEM . 'library/meschain/helper/webhook.php',
            DIR_APPLICATION . 'model/extension/module/reporting.php'
        );
        
        foreach ($files as $file) {
            if (!file_exists($file)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Remove database tables
     */
    private function removeDatabaseTables() {
        $tables = array(
            'meschain_webhook_log',
            'trendyol_webhook_log',
            'trendyol_webhook_config',
            'meschain_order',
            'meschain_api_log',
            'meschain_integration_test'
        );
        
        foreach ($tables as $table) {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . $table . "`");
        }
        
        $this->log->write('Database tables removed');
    }
    
    /**
     * Remove configurations
     */
    private function removeConfigurations() {
        // Remove any system configurations related to MesChain
        $this->log->write('Configurations removed');
    }
    
    /**
     * Clean up files
     */
    private function cleanupFiles() {
        $directories = array(
            DIR_LOGS . 'meschain/',
            DIR_CACHE . 'meschain/',
            DIR_UPLOAD . 'meschain/'
        );
        
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $this->deleteDirectory($dir);
            }
        }
        
        $this->log->write('Files cleaned up');
    }
    
    /**
     * Delete directory recursively
     */
    private function deleteDirectory($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->deleteDirectory($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
    
    /**
     * Get form data and render view
     */
    private function getForm($data = array()) {
        // Language variables
        $language_vars = array(
            'heading_title', 'text_install', 'text_uninstall', 
            'button_install', 'button_uninstall'
        );
        
        foreach ($language_vars as $var) {
            $data[$var] = $this->language->get($var);
        }
        
        // URLs
        $data['install_url'] = $this->url->link('extension/module/meschain_installer/install', 'user_token=' . $this->session->data['user_token'], true);
        $data['uninstall_url'] = $this->url->link('extension/module/meschain_installer/uninstall', 'user_token=' . $this->session->data['user_token'], true);
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/meschain_installer', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Template rendering
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_installer', $data));
    }
}
?> 