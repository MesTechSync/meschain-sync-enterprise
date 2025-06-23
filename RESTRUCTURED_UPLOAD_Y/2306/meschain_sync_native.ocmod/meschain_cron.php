<?php
/**
 * MesChain Sync Cron Entry Point
 * 
 * This file should be called by system cron or scheduled tasks
 * Example: php /path/to/opencart/meschain_cron.php
 * 
 * @package    MesChain Sync
 * @version    2.0.0
 * @author     MesChain Development Team
 * @copyright  2024 MesChain
 * @license    MIT License
 */

// Bootstrap OpenCart
require_once('config.php');
require_once(DIR_SYSTEM . 'startup.php');

// Create registry
$registry = new \Opencart\System\Engine\Registry();

// Database
$db = new \Opencart\System\Library\DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$registry->set('db', $db);

// Config
$config = new \Opencart\System\Engine\Config();
$config->addPath(DIR_CONFIG);
$config->load('default');
$config->load('catalog');
$registry->set('config', $config);

// Log
$log = new \Opencart\System\Library\Log($config->get('config_error_filename'));
$registry->set('log', $log);

// Load system classes
$registry->set('load', new \Opencart\System\Engine\Loader($registry));
$registry->set('cache', new \Opencart\System\Library\Cache($config->get('config_cache_engine'), $config->get('config_cache_expire')));

// Check if MesChain sync is enabled
$query = $db->query("SELECT setting_value FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `code` = 'meschain' AND `key` = 'meschain_status'");

if (!$query->num_rows || !$query->row['setting_value']) {
    $log->write('MesChain Cron: Extension is disabled. Exiting...');
    exit;
}

try {
    // Load MesChain sync manager
    require_once(DIR_SYSTEM . 'library/meschain/cron/syncmanager.php');
    
    $sync_manager = new \MesChain\Cron\SyncManager($registry);
    $sync_manager->execute();
    
} catch (\Exception $e) {
    $log->write('MesChain Cron Error: ' . $e->getMessage());
    exit(1);
}

exit(0);
