<?php
/**
 * MesChain Low Priority Sync CLI Script
 * Düşük öncelikli senkronizasyon için cron job script'i
 * 
 * Kullanım: php sync_low_priority.php
 * Cron: 0 * * * * php /path/to/upload/cli/sync_low_priority.php
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
    $log = new Log('meschain_low_priority.log');
    $registry->set('log', $log);
    
    // Cron Scheduler'ı başlat
    $cronScheduler = new CronScheduler($registry);
    
    echo "[" . date('Y-m-d H:i:s') . "] Starting Low Priority Sync...\n";
    $log->write('MesChain Low Priority Sync CLI Started');
    
    // Düşük öncelikli senkronizasyonu çalıştır
    $results = $cronScheduler->runLowPrioritySync();
    
    // Sonuçları göster
    echo "[" . date('Y-m-d H:i:s') . "] Low Priority Sync Results:\n";
    foreach ($results as $marketplace => $result) {
        if (isset($result['error'])) {
            echo "  $marketplace: ERROR - " . $result['error'] . "\n";
        } else {
            echo "  $marketplace: SUCCESS\n";
            echo "    - Products Updated: " . (isset($result['products_updated']) ? $result['products_updated'] : 0) . "\n";
            echo "    - Categories Synced: " . (isset($result['categories_synced']) ? $result['categories_synced'] : 0) . "\n";
            echo "    - Reports Generated: " . (isset($result['reports_generated']) ? $result['reports_generated'] : 0) . "\n";
            echo "    - API Calls: " . (isset($result['api_calls']) ? $result['api_calls'] : 0) . "\n";
        }
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] Low Priority Sync Completed Successfully!\n";
    $log->write('MesChain Low Priority Sync CLI Completed Successfully');
    
} catch (Exception $e) {
    $errorMsg = "Low Priority Sync Error: " . $e->getMessage();
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: $errorMsg\n";
    
    if (isset($log)) {
        $log->write($errorMsg);
    }
    
    exit(1);
}

exit(0);
?> 