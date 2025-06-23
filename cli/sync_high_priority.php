<?php
/**
 * MesChain High Priority Sync CLI Script
 * Yüksek öncelikli senkronizasyon için cron job script'i
 * 
 * Kullanım: php sync_high_priority.php
 * Cron: 0,5,10,15,20,25,30,35,40,45,50,55 * * * * php /path/to/upload/cli/sync_high_priority.php
 */

// OpenCart bootstrap
define('VERSION', '3.0.4.0');
define('DIR_APPLICATION', dirname(__FILE__) . '/../admin/');
define('DIR_SYSTEM', dirname(__FILE__) . '/../system/');
define('DIR_IMAGE', dirname(__FILE__) . '/../image/');
define('DIR_STORAGE', dirname(__FILE__) . '/../storage/');
define('DIR_CATALOG', dirname(__FILE__) . '/../catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// Config dosyasını yükle
if (file_exists(DIR_CONFIG . 'config.php')) {
    require_once(DIR_CONFIG . 'config.php');
}

// Database config
if (defined('DB_HOSTNAME') && defined('DB_USERNAME') && defined('DB_PASSWORD') && defined('DB_DATABASE')) {
    define('DB_PREFIX', DB_PREFIX);
} else {
    die("Database configuration not found!\n");
}

// Registry ve temel sınıfları yükle
require_once(DIR_SYSTEM . 'library/registry.php');
require_once(DIR_SYSTEM . 'library/db.php');
require_once(DIR_SYSTEM . 'library/config.php');
require_once(DIR_SYSTEM . 'library/log.php');

// MesChain Cron Scheduler'ı yükle
require_once(DIR_SYSTEM . 'library/meschain/helper/cron_scheduler.php');

try {
    // Registry oluştur
    $registry = new Registry();
    
    // Database bağlantısı
    $db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
    $registry->set('db', $db);
    
    // Config
    $config = new Config();
    $registry->set('config', $config);
    
    // Log
    $log = new Log('meschain_high_priority.log');
    $registry->set('log', $log);
    
    // Cron Scheduler'ı başlat
    $cronScheduler = new CronScheduler($registry);
    
    echo "[" . date('Y-m-d H:i:s') . "] Starting High Priority Sync...\n";
    $log->write('MesChain High Priority Sync CLI Started');
    
    // Yüksek öncelikli senkronizasyonu çalıştır
    $results = $cronScheduler->runHighPrioritySync();
    
    // Sonuçları göster
    echo "[" . date('Y-m-d H:i:s') . "] High Priority Sync Results:\n";
    foreach ($results as $marketplace => $result) {
        if (isset($result['error'])) {
            echo "  $marketplace: ERROR - " . $result['error'] . "\n";
        } else {
            echo "  $marketplace: SUCCESS\n";
            echo "    - Orders Updated: " . ($result['orders_updated'] ?? 0) . "\n";
            echo "    - Critical Stock Alerts: " . ($result['critical_stock_alerts'] ?? 0) . "\n";
            echo "    - Payment Updates: " . ($result['payment_status_updates'] ?? 0) . "\n";
            echo "    - API Calls: " . ($result['api_calls'] ?? 0) . "\n";
        }
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] High Priority Sync Completed Successfully!\n";
    $log->write('MesChain High Priority Sync CLI Completed Successfully');
    
} catch (Exception $e) {
    $errorMsg = "High Priority Sync Error: " . $e->getMessage();
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: $errorMsg\n";
    
    if (isset($log)) {
        $log->write($errorMsg);
    }
    
    exit(1);
}

exit(0);
?> 