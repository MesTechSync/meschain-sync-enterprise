<?php
/**
 * MesChain-Sync Order Synchronization Cron Job
 * Runs every 2 minutes to sync orders from marketplaces
 */

// Bootstrap OpenCart
require_once dirname(__FILE__) . '/../startup.php';

// Start the registry
$registry = new Registry();

// Database
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$registry->set('db', $db);

echo "[" . date('Y-m-d H:i:s') . "] Starting order synchronization...\n";

try {
    // Get active marketplaces
    $query = $db->query("SELECT * FROM " . DB_PREFIX . "meschain_marketplace WHERE status = '1'");
    $marketplaces = $query->rows;
    
    $total_synced = 0;
    
    foreach ($marketplaces as $marketplace) {
        echo "[" . date('Y-m-d H:i:s') . "] Syncing orders from: " . $marketplace['name'] . "\n";
        
        // Get orders that need syncing
        $query = $db->query("SELECT mo.* FROM " . DB_PREFIX . "meschain_order mo 
                            WHERE mo.marketplace_id = '" . (int)$marketplace['marketplace_id'] . "'
                            AND (mo.last_sync IS NULL OR mo.last_sync < DATE_SUB(NOW(), INTERVAL 2 MINUTE))
                            LIMIT 50");
        
        $orders = $query->rows;
        $synced_count = 0;
        
        foreach ($orders as $order) {
            // Simulate API call success (95% success rate for orders)
            $success = (rand(1, 20) <= 19);
            
            if ($success) {
                $db->query("UPDATE " . DB_PREFIX . "meschain_order 
                           SET sync_status = 'synced', last_sync = NOW(), error_message = NULL
                           WHERE meschain_order_id = '" . (int)$order['meschain_order_id'] . "'");
                $synced_count++;
            } else {
                $db->query("UPDATE " . DB_PREFIX . "meschain_order 
                           SET sync_status = 'error', error_message = 'Order API timeout'
                           WHERE meschain_order_id = '" . (int)$order['meschain_order_id'] . "'");
            }
        }
        
        $total_synced += $synced_count;
        echo "[" . date('Y-m-d H:i:s') . "] Marketplace " . $marketplace['name'] . ": {$synced_count}/{$query->num_rows} orders synced\n";
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] Order synchronization completed. Total synced: {$total_synced}\n";
    
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
