<?php
/**
 * MesChain-Sync Enterprise - Trendyol Integration
 * Complete Installation Script
 * 
 * This script performs the complete installation of the Trendyol integration system.
 * - Installs database schema
 * - Integrates admin menu
 * - Validates installation
 * - Creates configuration files
 * 
 * Usage:
 * - Web: http://yoursite.com/path/to/install_complete.php
 * - CLI: php install_complete.php
 * 
 * @package    MesChain\Sync\Install
 * @author     MesChain Development Team
 * @version    1.0.0
 * @since      2025-01-21
 */

// Prevent direct access in web mode
if (!defined('CLI_MODE')) {
    define('CLI_MODE', php_sapi_name() === 'cli');
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define paths
define('OPENCART_ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/');
define('INSTALL_LOG', '/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/storagenew/logs/meschain_install_complete.log');

class MesChainCompleteInstaller {
    
    private $log_messages = [];
    private $opencart_root;
    private $config;
    private $db_config;
    
    public function __construct() {
        $this->opencart_root = OPENCART_ROOT;
        $this->loadConfiguration();
    }
    
    /**
     * Load OpenCart configuration
     */
    private function loadConfiguration() {
        $config_file = $this->opencart_root . 'config.php';
        
        if (!file_exists($config_file)) {
            $this->error('OpenCart config.php not found at: ' . $config_file);
        }
        
        // Load OpenCart configuration
        require_once $config_file;
        
        // Extract configuration
        $this->config = [
            'hostname' => defined('DB_HOSTNAME') ? DB_HOSTNAME : 'localhost',
            'username' => defined('DB_USERNAME') ? DB_USERNAME : '',
            'password' => defined('DB_PASSWORD') ? DB_PASSWORD : '',
            'database' => defined('DB_DATABASE') ? DB_DATABASE : '',
            'port' => defined('DB_PORT') ? DB_PORT : '3306',
            'prefix' => defined('DB_PREFIX') ? DB_PREFIX : ''
        ];
        
        $this->log('✅ Configuration loaded successfully');
        $this->log('   Database: ' . $this->config['database']);
        $this->log('   Prefix: ' . ($this->config['prefix'] ?: '(none)'));
    }
    
    /**
     * Perform complete installation
     */
    public function install() {
        try {
            $this->log('🚀 Starting MesChain-Sync Trendyol Integration Complete Installation...');
            $this->log('   Version: 1.0.0');
            $this->log('   Date: ' . date('Y-m-d H:i:s'));
            
            // Step 1: Check prerequisites
            $this->log('\n📋 Step 1: Checking Prerequisites...');
            $this->checkPrerequisites();
            
            // Step 2: Install database schema
            $this->log('\n🗄️ Step 2: Installing Database Schema...');
            $this->installDatabase();
            
            // Step 3: Integrate admin menu
            $this->log('\n📱 Step 3: Integrating Admin Menu...');
            $this->integrateMenu();
            
            // Step 4: Validate installation
            $this->log('\n✅ Step 4: Validating Installation...');
            $this->validateInstallation();
            
            // Step 5: Create configuration
            $this->log('\n⚙️ Step 5: Creating Configuration...');
            $this->createConfiguration();
            
            // Step 6: Final checks
            $this->log('\n🔍 Step 6: Final System Checks...');
            $this->performFinalChecks();
            
            $this->success('\n🎉 MesChain-Sync Trendyol Integration installed successfully!');
            $this->log('   You can now access the integration from:');
            $this->log('   Admin Panel > Extensions > MesChain-Sync > Trendyol Integration');
            
            return true;
            
        } catch (\Exception $e) {
            $this->error('Installation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check system prerequisites
     */
    private function checkPrerequisites() {
        $checks = [
            'PHP Version' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'OpenCart Directory' => is_dir($this->opencart_root . 'system'),
            'Database Config' => file_exists($this->opencart_root . 'config.php'),
            'Admin Config' => file_exists($this->opencart_root . 'admin/config.php'),
            'PDO MySQL' => extension_loaded('pdo_mysql'),
            'cURL Extension' => extension_loaded('curl'),
            'JSON Extension' => extension_loaded('json'),
            'GD Extension' => extension_loaded('gd'),
            'Write Permissions' => is_writable($this->opencart_root . 'system/logs')
        ];
        
        $failed_checks = [];
        
        foreach ($checks as $check_name => $result) {
            if ($result) {
                $this->log("   ✅ $check_name: OK");
            } else {
                $this->log("   ❌ $check_name: FAILED");
                $failed_checks[] = $check_name;
            }
        }
        
        if (!empty($failed_checks)) {
            throw new \Exception('Prerequisites failed: ' . implode(', ', $failed_checks));
        }
        
        $this->log('   All prerequisites met!');
    }
    
    /**
     * Install database schema
     */
    private function installDatabase() {
        require_once dirname(__FILE__) . '/install_database.php';
        
        $installer = new MesChainInstaller();
        $result = $installer->install();
        
        if (!$result) {
            throw new \Exception('Database installation failed');
        }
        
        $this->log('   ✅ Database schema installed successfully');
    }
    
    /**
     * Integrate admin menu
     */
    private function integrateMenu() {
        require_once dirname(__FILE__) . '/menu_integration.php';
        
        // Connect to database for menu integration
        try {
            $pdo = new \PDO(
                "mysql:host={$this->config['hostname']};port={$this->config['port']};dbname={$this->config['database']};charset=utf8mb4",
                $this->config['username'],
                $this->config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            $db = new \MesChain\Sync\Install\SimpleDBWrapper($pdo);
            
            $menu = new \MesChain\Sync\Install\MenuIntegration();
            $menu->db = $db;
            
            $result = $menu->install();
            
            if (!$result['success']) {
                throw new \Exception('Menu integration failed: ' . $result['message']);
            }
            
            $this->log('   ✅ Admin menu integrated successfully');
            
        } catch (\Exception $e) {
            throw new \Exception('Menu integration failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate installation
     */
    private function validateInstallation() {
        // Validate database installation
        $this->log('   Validating database tables...');
        
        require_once dirname(__FILE__) . '/database_schema.php';
        require_once dirname(__FILE__) . '/install_database.php';
        
        $installer = new MesChainInstaller();
        $schema = new \MesChain\Sync\Install\DatabaseSchema(new DatabaseWrapper(
            new \PDO(
                "mysql:host={$this->config['hostname']};port={$this->config['port']};dbname={$this->config['database']};charset=utf8mb4",
                $this->config['username'],
                $this->config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            )
        ), $this->config['prefix']);
        
        $check_result = $schema->checkInstallation();
        
        $all_tables_exist = true;
        foreach ($check_result as $table => $info) {
            if ($info['exists']) {
                $this->log("     ✅ $table: {$info['columns']} columns");
            } else {
                $this->log("     ❌ $table: MISSING");
                $all_tables_exist = false;
            }
        }
        
        if (!$all_tables_exist) {
            throw new \Exception('Database validation failed - some tables are missing');
        }
        
        // Validate file structure
        $this->log('   Validating file structure...');
        
        $required_files = [
            'system/library/meschain/api/TrendyolApiClient.php',
            'system/library/meschain/importer/TrendyolProductImporter.php',
            'admin/controller/extension/meschain/trendyol_importer.php',
            'admin/model/extension/meschain/trendyol_importer.php',
            'admin/language/en-gb/extension/meschain/trendyol_importer.php',
            'admin/view/template/extension/meschain/trendyol_importer_dashboard.twig',
            'admin/view/template/extension/meschain/trendyol_importer_wizard.twig',
            'admin/view/template/extension/meschain/trendyol_importer_progress.twig'
        ];
        
        $missing_files = [];
        foreach ($required_files as $file) {
            $full_path = $this->opencart_root . $file;
            if (file_exists($full_path)) {
                $this->log("     ✅ $file");
            } else {
                $this->log("     ❌ $file: MISSING");
                $missing_files[] = $file;
            }
        }
        
        if (!empty($missing_files)) {
            throw new \Exception('File validation failed - missing files: ' . implode(', ', $missing_files));
        }
        
        $this->log('   ✅ All validation checks passed');
    }
    
    /**
     * Create configuration files
     */
    private function createConfiguration() {
        // Create MesChain configuration directory
        $config_dir = $this->opencart_root . 'system/config/meschain/';
        if (!is_dir($config_dir)) {
            mkdir($config_dir, 0755, true);
            $this->log('   Created configuration directory');
        }
        
        // Create default configuration file
        $config_data = [
            'version' => '1.0.0',
            'installed_at' => date('Y-m-d H:i:s'),
            'database_version' => '1.0.0',
            'api_settings' => [
                'default_batch_size' => 100,
                'max_batch_size' => 500,
                'request_timeout' => 30,
                'max_retries' => 3,
                'rate_limit_delay' => 1000
            ],
            'import_settings' => [
                'default_import_images' => true,
                'default_create_categories' => true,
                'default_import_attributes' => true,
                'default_status' => 1,
                'memory_limit_mb' => 512
            ],
            'logging' => [
                'enabled' => true,
                'level' => 'info',
                'max_file_size_mb' => 10,
                'max_files' => 5
            ]
        ];
        
        $config_file = $config_dir . 'trendyol_config.php';
        $config_content = "<?php\n// MesChain-Sync Trendyol Integration Configuration\n// Generated on " . date('Y-m-d H:i:s') . "\n\nreturn " . var_export($config_data, true) . ";\n";
        
        file_put_contents($config_file, $config_content);
        $this->log('   ✅ Configuration file created: ' . $config_file);
        
        // Create API credentials template
        $credentials_template = $config_dir . 'trendyol_credentials.example.php';
        $credentials_content = "<?php\n// MesChain-Sync Trendyol API Credentials Template\n// Copy this file to trendyol_credentials.php and add your credentials\n\nreturn [\n    'api_key' => 'YOUR_TRENDYOL_API_KEY',\n    'api_secret' => 'YOUR_TRENDYOL_API_SECRET',\n    'supplier_id' => 'YOUR_SUPPLIER_ID', // e.g., 1076956\n    'environment' => 'production', // or 'sandbox'\n];\n";
        
        file_put_contents($credentials_template, $credentials_content);
        $this->log('   ✅ Credentials template created: ' . $credentials_template);
    }
    
    /**
     * Perform final system checks
     */
    private function performFinalChecks() {
        // Check memory limit
        $memory_limit = ini_get('memory_limit');
        $memory_limit_bytes = $this->parseBytes($memory_limit);
        $recommended_bytes = 512 * 1024 * 1024; // 512MB
        
        if ($memory_limit_bytes < $recommended_bytes) {
            $this->log('   ⚠️ Memory limit (' . $memory_limit . ') is below recommended 512MB');
        } else {
            $this->log('   ✅ Memory limit: ' . $memory_limit);
        }
        
        // Check max execution time
        $max_exec_time = ini_get('max_execution_time');
        if ($max_exec_time > 0 && $max_exec_time < 300) {
            $this->log('   ⚠️ Max execution time (' . $max_exec_time . 's) may be too low for large imports');
        } else {
            $this->log('   ✅ Max execution time: ' . ($max_exec_time == 0 ? 'unlimited' : $max_exec_time . 's'));
        }
        
        // Test API client instantiation
        try {
            if (file_exists($this->opencart_root . 'system/library/meschain/api/TrendyolApiClient.php')) {
                $this->log('   ✅ API client file exists');
            } else {
                $this->log('   ⚠️ API client file missing');
            }
        } catch (\Exception $e) {
            $this->log('   ⚠️ API client test failed: ' . $e->getMessage());
        }
        
        // Test database connection
        try {
            $pdo = new \PDO(
                "mysql:host={$this->config['hostname']};port={$this->config['port']};dbname={$this->config['database']};charset=utf8mb4",
                $this->config['username'],
                $this->config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            $stmt = $pdo->query("SELECT COUNT(*) FROM {$this->config['prefix']}trendyol_import_sessions");
            $this->log('   ✅ Database connection and tables accessible');
            
        } catch (\Exception $e) {
            $this->log('   ❌ Database connection test failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Convert PHP memory limit to bytes
     */
    private function parseBytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (int)$val;
        
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        
        return $val;
    }
    
    /**
     * Uninstall complete system
     */
    public function uninstall() {
        try {
            $this->log('🗑️ Starting MesChain-Sync Trendyol Integration Uninstallation...');
            
            // Uninstall database
            $this->log('\n🗄️ Removing Database Schema...');
            require_once dirname(__FILE__) . '/install_database.php';
            $installer = new MesChainInstaller();
            $installer->uninstall();
            
            // Remove menu items
            $this->log('\n📱 Removing Menu Items...');
            require_once dirname(__FILE__) . '/menu_integration.php';
            
            $pdo = new \PDO(
                "mysql:host={$this->config['hostname']};port={$this->config['port']};dbname={$this->config['database']};charset=utf8mb4",
                $this->config['username'],
                $this->config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            $db = new \MesChain\Sync\Install\SimpleDBWrapper($pdo);
            $menu = new \MesChain\Sync\Install\MenuIntegration();
            $menu->db = $db;
            $menu->uninstall();
            
            // Remove configuration files
            $this->log('\n⚙️ Removing Configuration Files...');
            $config_dir = $this->opencart_root . 'system/config/meschain/';
            if (is_dir($config_dir)) {
                $this->removeDirectory($config_dir);
                $this->log('   Configuration directory removed');
            }
            
            $this->success('\n✅ MesChain-Sync Trendyol Integration uninstalled successfully!');
            
            return true;
            
        } catch (\Exception $e) {
            $this->error('Uninstallation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove directory recursively
     */
    private function removeDirectory($dir) {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    $this->removeDirectory($path);
                } else {
                    unlink($path);
                }
            }
            rmdir($dir);
        }
    }
    
    /**
     * Check installation status
     */
    public function status() {
        $this->log('📊 MesChain-Sync Trendyol Integration Status Check...\n');
        
        try {
            // Check database
            require_once dirname(__FILE__) . '/database_schema.php';
            require_once dirname(__FILE__) . '/install_database.php';
            
            $installer = new MesChainInstaller();
            $schema = new \MesChain\Sync\Install\DatabaseSchema(new DatabaseWrapper(
                new \PDO(
                    "mysql:host={$this->config['hostname']};port={$this->config['port']};dbname={$this->config['database']};charset=utf8mb4",
                    $this->config['username'],
                    $this->config['password'],
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                )
            ), $this->config['prefix']);
            
            $check_result = $schema->checkInstallation();
            
            $installed_tables = 0;
            $total_tables = count($check_result);
            
            $this->log('🗄️ Database Status:');
            foreach ($check_result as $table => $info) {
                if ($info['exists']) {
                    $this->log("   ✅ $table: {$info['columns']} columns");
                    $installed_tables++;
                } else {
                    $this->log("   ❌ $table: MISSING");
                }
            }
            
            // Check files
            $this->log('\n📁 File Status:');
            $required_files = [
                'system/library/meschain/api/TrendyolApiClient.php',
                'admin/controller/extension/meschain/trendyol_importer.php',
                'admin/view/template/extension/meschain/trendyol_importer_dashboard.twig'
            ];
            
            $existing_files = 0;
            foreach ($required_files as $file) {
                if (file_exists($this->opencart_root . $file)) {
                    $this->log("   ✅ $file");
                    $existing_files++;
                } else {
                    $this->log("   ❌ $file: MISSING");
                }
            }
            
            // Overall status
            $this->log('\n📈 Overall Status:');
            $this->log("   Database: $installed_tables/$total_tables tables");
            $this->log("   Files: $existing_files/" . count($required_files) . " files");
            
            if ($installed_tables === $total_tables && $existing_files === count($required_files)) {
                $this->success('   Status: FULLY INSTALLED ✅');
            } elseif ($installed_tables > 0 || $existing_files > 0) {
                $this->log('   Status: PARTIALLY INSTALLED ⚠️');
            } else {
                $this->log('   Status: NOT INSTALLED ❌');
            }
            
        } catch (\Exception $e) {
            $this->error('Status check failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Log message
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $message";
        
        $this->log_messages[] = $log_entry;
        
        // Write to log file
        file_put_contents(INSTALL_LOG, $log_entry . PHP_EOL, FILE_APPEND | LOCK_EX);
        
        // Output to console/browser
        $this->output($message);
    }
    
    /**
     * Log success message
     */
    private function success($message) {
        $this->log("$message");
    }
    
    /**
     * Log error and exit
     */
    private function error($message) {
        $this->log("❌ ERROR: $message");
        
        if (!CLI_MODE) {
            $this->output('<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;"><strong>Error:</strong> ' . htmlspecialchars($message) . '</div>');
        }
        
        exit(1);
    }
    
    /**
     * Output message
     */
    private function output($message) {
        if (CLI_MODE) {
            echo $message . PHP_EOL;
        } else {
            echo '<div style="font-family: monospace; margin: 5px 0; padding: 5px; background: #f8f9fa; border-left: 3px solid #007bff;">' . htmlspecialchars($message) . '</div>';
            flush();
        }
    }
    
    /**
     * Get log messages
     */
    public function getLogMessages() {
        return $this->log_messages;
    }
}

// Main execution
if (!CLI_MODE) {
    // Web interface
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>MesChain-Sync Complete Installer</title>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
            .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { text-align: center; margin-bottom: 30px; }
            .button { display: inline-block; padding: 12px 24px; margin: 10px; text-decoration: none; border-radius: 5px; font-weight: bold; }
            .btn-primary { background: #007bff; color: white; }
            .btn-success { background: #28a745; color: white; }
            .btn-warning { background: #ffc107; color: black; }
            .btn-danger { background: #dc3545; color: white; }
            .output { margin-top: 20px; max-height: 600px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 5px; }
            .status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }
            .status-card { background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🚀 MesChain-Sync Complete Installer</h1>
                <p>Trendyol Integration for OpenCart 4.0.2.3</p>
                <p><strong>Enterprise Edition v1.0.0</strong></p>
            </div>
            
            <?php if (!isset($_GET['action'])): ?>
                <div style="text-align: center;">
                    <a href="?action=install" class="button btn-primary">🚀 Complete Installation</a>
                    <a href="?action=status" class="button btn-success">📊 Check Status</a>
                    <a href="?action=uninstall" class="button btn-danger">🗑️ Uninstall</a>
                </div>
                
                <div class="status-grid">
                    <div class="status-card">
                        <h4>📦 What Will Be Installed</h4>
                        <ul>
                            <li>Database Schema (6 tables)</li>
                            <li>Admin Menu Integration</li>
                            <li>API Client & Import Engine</li>
                            <li>Admin Dashboard & Wizard</li>
                            <li>Configuration Files</li>
                        </ul>
                    </div>
                    <div class="status-card">
                        <h4>⚙️ System Requirements</h4>
                        <ul>
                            <li>PHP 7.4+ (Current: <?= PHP_VERSION ?>)</li>
                            <li>OpenCart 4.0.2.3</li>
                            <li>MySQL 5.7+</li>
                            <li>cURL Extension</li>
                            <li>512MB Memory Limit</li>
                        </ul>
                    </div>
                    <div class="status-card">
                        <h4>🎯 After Installation</h4>
                        <ul>
                            <li>Configure Trendyol API credentials</li>
                            <li>Access via Admin > Extensions > MesChain-Sync</li>
                            <li>Start importing products</li>
                            <li>Monitor import progress</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="output">
                <?php
                if (isset($_GET['action'])) {
                    $installer = new MesChainCompleteInstaller();
                    
                    switch ($_GET['action']) {
                        case 'install':
                            echo '<h2>🚀 Complete Installation</h2>';
                            $installer->install();
                            echo '<div style="margin-top: 20px; text-align: center;"><a href="?" class="button btn-primary">← Back to Menu</a></div>';
                            break;
                            
                        case 'uninstall':
                            echo '<h2>🗑️ Uninstalling System</h2>';
                            $installer->uninstall();
                            echo '<div style="margin-top: 20px; text-align: center;"><a href="?" class="button btn-primary">← Back to Menu</a></div>';
                            break;
                            
                        case 'status':
                            echo '<h2>📊 Installation Status</h2>';
                            $installer->status();
                            echo '<div style="margin-top: 20px; text-align: center;"><a href="?" class="button btn-primary">← Back to Menu</a></div>';
                            break;
                            
                        default:
                            echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;">Invalid action specified.</div>';
                    }
                }
                ?>
            </div>
        </div>
    </body>
    </html>
    <?php
} else {
    // CLI interface
    $installer = new MesChainCompleteInstaller();
    
    $action = isset($argv[1]) ? $argv[1] : 'help';
    
    switch ($action) {
        case 'install':
            $installer->install();
            break;
            
        case 'uninstall':
            $installer->uninstall();
            break;
            
        case 'status':
            $installer->status();
            break;
            
        case 'help':
        default:
            echo "MesChain-Sync Trendyol Integration Complete Installer\n";
            echo "=====================================================\n\n";
            echo "Usage: php install_complete.php [action]\n\n";
            echo "Actions:\n";
            echo "  install    - Perform complete installation\n";
            echo "  uninstall  - Remove complete installation\n";
            echo "  status     - Check installation status\n";
            echo "  help       - Show this help message\n\n";
            echo "Enterprise Edition v1.0.0\n";
            echo "For support, visit: https://meschain.com/support\n";
            break;
    }
}
?>