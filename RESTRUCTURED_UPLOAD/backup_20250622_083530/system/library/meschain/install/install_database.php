<?php
/**
 * MesChain-Sync Enterprise - Trendyol Integration
 * Database Installation Script
 * 
 * This script installs the database schema for the Trendyol product import system.
 * Can be executed via web browser or command line.
 * 
 * Usage:
 * - Web: http://yoursite.com/path/to/install_database.php
 * - CLI: php install_database.php
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
define('INSTALL_LOG', '/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/storagenew/logs/meschain_install.log');

class MesChainInstaller {
    
    private $db;
    private $config;
    private $db_prefix;
    private $log_messages = [];
    
    public function __construct() {
        $this->loadOpenCartConfig();
        $this->connectDatabase();
    }
    
    /**
     * Load OpenCart configuration
     */
    private function loadOpenCartConfig() {
        $config_file = OPENCART_ROOT . 'config.php';
        
        if (!file_exists($config_file)) {
            $this->error('OpenCart config.php not found at: ' . $config_file);
        }
        
        // Load OpenCart configuration
        require_once $config_file;
        
        // Extract database configuration
        $this->config = [
            'hostname' => defined('DB_HOSTNAME') ? DB_HOSTNAME : 'localhost',
            'username' => defined('DB_USERNAME') ? DB_USERNAME : '',
            'password' => defined('DB_PASSWORD') ? DB_PASSWORD : '',
            'database' => defined('DB_DATABASE') ? DB_DATABASE : '',
            'port' => defined('DB_PORT') ? DB_PORT : '3306'
        ];
        
        $this->db_prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        
        $this->log('Configuration loaded successfully');
        $this->log('Database: ' . $this->config['database']);
        $this->log('Prefix: ' . ($this->db_prefix ?: '(none)'));
    }
    
    /**
     * Connect to database
     */
    private function connectDatabase() {
        try {
            $dsn = "mysql:host={$this->config['hostname']};port={$this->config['port']};dbname={$this->config['database']};charset=utf8mb4";
            
            $this->db = new PDO($dsn, $this->config['username'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            
            $this->log('Database connection established');
            
        } catch (PDOException $e) {
            $this->error('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Install database schema
     */
    public function install() {
        try {
            $this->log('Starting MesChain-Sync Trendyol Integration installation...');
            
            // Load database schema class
            require_once dirname(__FILE__) . '/database_schema.php';
            
            // Create database wrapper for compatibility
            $db_wrapper = new DatabaseWrapper($this->db);
            
            // Initialize schema installer
            $schema = new \MesChain\Sync\Install\DatabaseSchema($db_wrapper, $this->db_prefix);
            
            // Check if already installed
            $check_result = $schema->checkInstallation();
            $already_installed = true;
            
            foreach ($check_result as $table => $info) {
                if (!$info['exists']) {
                    $already_installed = false;
                    break;
                }
            }
            
            if ($already_installed) {
                $this->log('MesChain-Sync tables already exist. Checking for updates...');
                
                // Check for any missing tables or updates needed
                $missing_tables = [];
                foreach ($check_result as $table => $info) {
                    if ($info['status'] === 'MISSING') {
                        $missing_tables[] = $table;
                    }
                }
                
                if (empty($missing_tables)) {
                    $this->success('Installation already complete. All tables are present.');
                    return true;
                } else {
                    $this->log('Some tables are missing, proceeding with installation...');
                }
            }
            
            // Perform installation
            $this->log('Installing database schema...');
            $result = $schema->install();
            
            if ($result['success']) {
                $this->log('Database schema installed successfully');
                
                // Log installation details
                foreach ($result['details'] as $component => $status) {
                    if (is_array($status)) {
                        foreach ($status as $item => $item_status) {
                            $this->log("  $component.$item: $item_status");
                        }
                    } else {
                        $this->log("  $component: $status");
                    }
                }
                
                // Verify installation
                $this->log('Verifying installation...');
                $verification = $schema->checkInstallation();
                
                $all_good = true;
                foreach ($verification as $table => $info) {
                    if ($info['exists']) {
                        $this->log("  ✓ $table: {$info['columns']} columns");
                    } else {
                        $this->log("  ✗ $table: MISSING");
                        $all_good = false;
                    }
                }
                
                if ($all_good) {
                    $this->success('MesChain-Sync Trendyol Integration installed successfully!');
                    $this->log('Schema version: ' . $schema->getSchemaVersion());
                    return true;
                } else {
                    $this->error('Installation verification failed');
                }
                
            } else {
                $this->error('Installation failed: ' . $result['message']);
            }
            
        } catch (\Exception $e) {
            $this->error('Installation error: ' . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Uninstall database schema
     */
    public function uninstall() {
        try {
            $this->log('Starting MesChain-Sync Trendyol Integration uninstallation...');
            
            // Confirm uninstallation
            if (!CLI_MODE) {
                $confirm = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';
                if (!$confirm) {
                    $this->output('
                    <div style="background: #fff3cd; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                        <h3 style="color: #856404;">⚠️ Uninstall Confirmation Required</h3>
                        <p>This will permanently delete all MesChain-Sync data including:</p>
                        <ul>
                            <li>All import sessions and history</li>
                            <li>Product import logs</li>
                            <li>Category and brand mappings</li>
                            <li>Statistical data</li>
                        </ul>
                        <p><strong>This action cannot be undone!</strong></p>
                        <a href="?action=uninstall&confirm=yes" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Yes, Uninstall</a>
                        <a href="?" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;">Cancel</a>
                    </div>');
                    return false;
                }
            }
            
            // Load database schema class
            require_once dirname(__FILE__) . '/database_schema.php';
            
            // Create database wrapper for compatibility
            $db_wrapper = new DatabaseWrapper($this->db);
            
            // Initialize schema installer
            $schema = new \MesChain\Sync\Install\DatabaseSchema($db_wrapper, $this->db_prefix);
            
            // Perform uninstallation
            $result = $schema->uninstall();
            
            if ($result['success']) {
                $this->success('MesChain-Sync Trendyol Integration uninstalled successfully!');
                
                // Log uninstallation details
                foreach ($result['details'] as $table => $status) {
                    $this->log("  $table: $status");
                }
                
                return true;
            } else {
                $this->error('Uninstallation failed: ' . $result['message']);
            }
            
        } catch (\Exception $e) {
            $this->error('Uninstallation error: ' . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Check installation status
     */
    public function status() {
        try {
            // Load database schema class
            require_once dirname(__FILE__) . '/database_schema.php';
            
            // Create database wrapper for compatibility
            $db_wrapper = new DatabaseWrapper($this->db);
            
            // Initialize schema installer
            $schema = new \MesChain\Sync\Install\DatabaseSchema($db_wrapper, $this->db_prefix);
            
            $this->log('Checking MesChain-Sync installation status...');
            
            $check_result = $schema->checkInstallation();
            
            $installed_count = 0;
            $total_count = count($check_result);
            
            foreach ($check_result as $table => $info) {
                if ($info['exists']) {
                    $this->log("  ✓ $table: {$info['columns']} columns - {$info['status']}");
                    $installed_count++;
                } else {
                    $this->log("  ✗ $table: {$info['status']}");
                }
            }
            
            if ($installed_count === $total_count) {
                $this->success("Installation Status: COMPLETE ($installed_count/$total_count tables)");
                $this->log('Schema version: ' . $schema->getSchemaVersion());
            } elseif ($installed_count > 0) {
                $this->log("Installation Status: PARTIAL ($installed_count/$total_count tables)");
            } else {
                $this->log("Installation Status: NOT INSTALLED");
            }
            
            return $check_result;
            
        } catch (\Exception $e) {
            $this->error('Status check error: ' . $e->getMessage());
        }
        
        return false;
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
        $this->log("✅ SUCCESS: $message");
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

/**
 * Database wrapper for compatibility with DatabaseSchema class
 */
class DatabaseWrapper {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function query($sql) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        // Return a result-like object
        return new DatabaseResult($stmt);
    }
}

/**
 * Database result wrapper
 */
class DatabaseResult {
    private $stmt;
    
    public function __construct($stmt) {
        $this->stmt = $stmt;
    }
    
    public function __get($name) {
        if ($name === 'num_rows') {
            return $this->stmt->rowCount();
        }
        return null;
    }
}

// Main execution
if (!CLI_MODE) {
    // Web interface
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>MesChain-Sync Database Installer</title>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { text-align: center; margin-bottom: 30px; }
            .button { display: inline-block; padding: 12px 24px; margin: 10px; text-decoration: none; border-radius: 5px; font-weight: bold; }
            .btn-primary { background: #007bff; color: white; }
            .btn-success { background: #28a745; color: white; }
            .btn-warning { background: #ffc107; color: black; }
            .btn-danger { background: #dc3545; color: white; }
            .output { margin-top: 20px; }
            pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🔧 MesChain-Sync Database Installer</h1>
                <p>Trendyol Integration for OpenCart 4.0.2.3</p>
            </div>
            
            <?php if (!isset($_GET['action'])): ?>
                <div style="text-align: center;">
                    <a href="?action=install" class="button btn-primary">📦 Install Database</a>
                    <a href="?action=status" class="button btn-success">📊 Check Status</a>
                    <a href="?action=uninstall" class="button btn-danger">🗑️ Uninstall</a>
                </div>
                
                <div style="margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 5px;">
                    <h3>About This Installer</h3>
                    <p>This installer will create the following database tables:</p>
                    <ul>
                        <li><code>trendyol_import_sessions</code> - Import session tracking</li>
                        <li><code>trendyol_import_products</code> - Product import details</li>
                        <li><code>trendyol_import_logs</code> - Import process logs</li>
                        <li><code>trendyol_category_mapping</code> - Category mappings</li>
                        <li><code>trendyol_brand_mapping</code> - Brand mappings</li>
                        <li><code>trendyol_import_statistics</code> - Performance statistics</li>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="output">
                <?php
                if (isset($_GET['action'])) {
                    $installer = new MesChainInstaller();
                    
                    switch ($_GET['action']) {
                        case 'install':
                            echo '<h2>Installing Database Schema...</h2>';
                            $installer->install();
                            break;
                            
                        case 'uninstall':
                            echo '<h2>Uninstalling Database Schema...</h2>';
                            $installer->uninstall();
                            break;
                            
                        case 'status':
                            echo '<h2>Installation Status</h2>';
                            $installer->status();
                            break;
                            
                        default:
                            echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;">Invalid action specified.</div>';
                    }
                    
                    echo '<div style="margin-top: 20px; text-align: center;"><a href="?" class="button btn-primary">← Back to Menu</a></div>';
                }
                ?>
            </div>
        </div>
    </body>
    </html>
    <?php
} else {
    // CLI interface
    $installer = new MesChainInstaller();
    
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
            echo "MesChain-Sync Trendyol Integration Database Installer\n";
            echo "Usage: php install_database.php [action]\n\n";
            echo "Actions:\n";
            echo "  install    - Install database schema\n";
            echo "  uninstall  - Remove database schema\n";
            echo "  status     - Check installation status\n";
            echo "  help       - Show this help message\n";
            break;
    }
}
?>