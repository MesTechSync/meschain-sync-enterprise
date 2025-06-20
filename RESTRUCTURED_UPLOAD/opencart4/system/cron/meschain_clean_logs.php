<?php
/**
 * MesChain-Sync Log Cleanup Cron Job
 * Runs daily to clean old logs
 */

// Bootstrap OpenCart
require_once dirname(__FILE__) . '/../startup.php';

// Start the registry
$registry = new Registry();

// Database
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$registry->set('db', $db);

echo "[" . date('Y-m-d H:i:s') . "] Starting log cleanup...\n";

try {
    // Clean logs older than 30 days
    $query = $db->query("DELETE FROM " . DB_PREFIX . "meschain_log 
                        WHERE date_added < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    
    $deleted_count = $db->countAffected();
    
    echo "[" . date('Y-m-d H:i:s') . "] Log cleanup completed. Deleted {$deleted_count} old log entries\n";
    
    // Clean log files older than 7 days
    $log_dir = dirname(__FILE__) . '/../storage/logs';
    $log_files = glob($log_dir . '/meschain_*.log');
    
    foreach ($log_files as $log_file) {
        if (filemtime($log_file) < strtotime('-7 days')) {
            unlink($log_file);
            echo "[" . date('Y-m-d H:i:s') . "] Deleted old log file: " . basename($log_file) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
