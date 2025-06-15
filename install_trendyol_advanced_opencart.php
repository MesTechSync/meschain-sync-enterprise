<?php
/**
 * OpenCart-Based Trendyol Advanced Installation
 * 
 * This script uses OpenCart's framework to properly install the Trendyol Advanced features
 * Run this from your OpenCart root directory
 */

// Check if we're in OpenCart root
if (!file_exists('config.php') || !file_exists('admin/config.php')) {
    die("Error: Please run this script from your OpenCart root directory (where config.php is located)\n");
}

// Include OpenCart configuration
require_once('config.php');
require_once('admin/config.php');

// Start OpenCart framework
require_once(DIR_SYSTEM . 'startup.php');

// Initialize registry
$registry = new Registry();

// Database
$registry->set('db', new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT));

// Load
$registry->set('load', new Loader($registry));

// Log
$registry->set('log', new Log('trendyol_advanced_install.log'));

// Cache
$registry->set('cache', new Cache('file'));

echo "🚀 Trendyol Advanced Features Installation via OpenCart Framework\n";
echo "================================================================\n\n";

try {
    // Load the model manually
    require_once(DIR_APPLICATION . 'model/extension/module/trendyol_advanced.php');
    
    $model = new ModelExtensionModuleTrendyolAdvanced($registry);
    
    echo "📊 Installing database tables...\n";
    $model->install();
    echo "✅ Database tables created successfully!\n\n";
    
    // Verify tables exist
    echo "🔍 Verifying installation...\n";
    $db = $registry->get('db');
    
    $tables = [
        'trendyol_ai_optimization',
        'trendyol_analytics',
        'trendyol_performance', 
        'trendyol_activities',
        'trendyol_alerts'
    ];
    
    foreach ($tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
        if ($result->num_rows > 0) {
            echo "✅ " . DB_PREFIX . $table . " created successfully\n";
        } else {
            echo "❌ " . DB_PREFIX . $table . " not found\n";
        }
    }
    
    echo "\n🎉 Installation completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Login to your OpenCart admin panel\n";
    echo "2. Navigate to Extensions > Modules\n";
    echo "3. Find 'Trendyol Advanced' and install it\n";
    echo "4. Configure your Trendyol API settings\n";
    echo "5. Enable advanced features\n\n";
    
} catch (Exception $e) {
    echo "❌ Installation failed: " . $e->getMessage() . "\n";
    echo "Check the log file: " . DIR_LOGS . "trendyol_advanced_install.log\n";
}
?>
