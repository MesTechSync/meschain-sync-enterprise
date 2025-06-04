<?php
/**
 * sync_orders.php
 *
 * Amaç: Tüm pazaryerlerinden siparişleri çekip OpenCart'a aktaran cron dosyası.
 *
 * Kullanım: php sync_orders.php [marketplace] [limit]
 * Örnek: php sync_orders.php trendyol 100
 * Tüm pazaryerleri için: php sync_orders.php all
 *
 * Loglama: Tüm işlemler ve hatalar cron_sync_orders.log dosyasına kaydedilir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [MARKETPLACE] [İŞLEM] [AÇIKLAMA]
 */

// OpenCart bootstrap
define('DIR_APPLICATION', str_replace('\\', '/', realpath(dirname(__FILE__) . '/../../../..')) . '/catalog/');
define('DIR_SYSTEM', str_replace('\\', '/', realpath(dirname(__FILE__) . '/../../..')) . '/');
define('DIR_DATABASE', DIR_SYSTEM . 'database/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_IMAGE', str_replace('\\', '/', realpath(dirname(__FILE__) . '/../../../..')) . '/image/');
define('DIR_CACHE', DIR_SYSTEM . 'storage/cache/');
define('DIR_DOWNLOAD', DIR_SYSTEM . 'storage/download/');
define('DIR_LOGS', DIR_SYSTEM . 'storage/logs/');
define('DIR_MODIFICATION', DIR_SYSTEM . 'storage/modification/');
define('DIR_UPLOAD', DIR_SYSTEM . 'storage/upload/');

// Autoloader
require_once(DIR_SYSTEM . 'startup.php');

// Registry
$registry = new Registry();

// Config
$config = new Config();
$registry->set('config', $config);

// Database
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$registry->set('db', $db);

// Marketplace listesi
$marketplaces = ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon'];

// Komut satırı parametreleri
$marketplace = isset($argv[1]) ? strtolower($argv[1]) : 'all';
$limit = isset($argv[2]) ? (int)$argv[2] : 50;

// Log dosyası
$log_file = DIR_LOGS . 'cron_sync_orders.log';

/**
 * Log kaydı ekler
 */
function writeLog($marketplace, $action, $message) {
    global $log_file;
    $date = date('Y-m-d H:i:s');
    $log = "[$date] [$marketplace] [$action] $message\n";
    file_put_contents($log_file, $log, FILE_APPEND);
}

/**
 * Pazaryerinden siparişleri çeker
 */
function syncOrders($marketplace, $limit) {
    global $db;
    
    writeLog($marketplace, 'SYNC_STARTED', "Sipariş senkronizasyonu başladı. Limit: $limit");
    
    try {
        // Pazaryeri API ayarlarını yükle
        $settings = loadApiSettings($marketplace);
        if (!$settings) {
            writeLog($marketplace, 'ERROR', 'API ayarları bulunamadı');
            return false;
        }
        
        // API sınıfını yükle ve siparişleri çek
        $className = ucfirst($marketplace) . 'Helper';
        $helperFile = DIR_SYSTEM . "library/entegrator/api/{$marketplace}_api.php";
        
        if (!file_exists($helperFile)) {
            writeLog($marketplace, 'ERROR', "API dosyası bulunamadı: $helperFile");
            return false;
        }
        
        require_once($helperFile);
        
        if (!class_exists($className)) {
            writeLog($marketplace, 'ERROR', "API sınıfı bulunamadı: $className");
            return false;
        }
        
        // Son 1 günlük siparişleri çek
        $startDate = date('Y-m-d H:i:s', strtotime('-1 day'));
        $orders = $className::getOrders($settings, ['startDate' => $startDate, 'size' => $limit]);
        
        if (!$orders || !isset($orders['content']) || empty($orders['content'])) {
            writeLog($marketplace, 'INFO', 'Yeni sipariş bulunamadı');
            return true;
        }
        
        $orderCount = count($orders['content']);
        writeLog($marketplace, 'INFO', "$orderCount adet sipariş bulundu");
        
        // Siparişleri veritabanına kaydet
        foreach ($orders['content'] as $order) {
            $marketplaceOrderId = $order['orderNumber'] ?? $order['id'] ?? '';
            
            // Sipariş daha önce kaydedilmiş mi kontrol et
            $existingOrder = $db->query("SELECT * FROM " . DB_PREFIX . "meschain_orders 
                WHERE marketplace = '" . $db->escape($marketplace) . "' 
                AND marketplace_order_id = '" . $db->escape($marketplaceOrderId) . "'");
                
            if ($existingOrder->num_rows > 0) {
                // Sipariş durumunu güncelle
                $db->query("UPDATE " . DB_PREFIX . "meschain_orders SET 
                    order_status = '" . $db->escape($order['status']) . "',
                    date_modified = NOW()
                    WHERE marketplace = '" . $db->escape($marketplace) . "' 
                    AND marketplace_order_id = '" . $db->escape($marketplaceOrderId) . "'");
                    
                writeLog($marketplace, 'UPDATE', "Sipariş güncellendi: $marketplaceOrderId");
            } else {
                // Yeni sipariş ekle
                $shippingAddress = json_encode($order['shipmentAddress'] ?? []);
                $totalAmount = $order['totalPrice'] ?? $order['amount'] ?? 0;
                
                $db->query("INSERT INTO " . DB_PREFIX . "meschain_orders SET
                    marketplace = '" . $db->escape($marketplace) . "',
                    marketplace_order_id = '" . $db->escape($marketplaceOrderId) . "',
                    order_status = '" . $db->escape($order['status']) . "',
                    shipping_address = '" . $db->escape($shippingAddress) . "',
                    total_amount = '" . (float)$totalAmount . "',
                    date_added = NOW(),
                    date_modified = NOW()");
                    
                writeLog($marketplace, 'INSERT', "Yeni sipariş eklendi: $marketplaceOrderId");
            }
        }
        
        writeLog($marketplace, 'SYNC_COMPLETED', "Sipariş senkronizasyonu tamamlandı");
        return true;
    } catch (Exception $e) {
        writeLog($marketplace, 'ERROR', 'Hata: ' . $e->getMessage());
        return false;
    }
}

/**
 * Pazaryeri API ayarlarını yükler
 */
function loadApiSettings($marketplace) {
    global $db;
    
    // Varsayılan admin kullanıcısının ayarlarını al
    $query = $db->query("SELECT * FROM " . DB_PREFIX . "meschain_user_settings 
        WHERE marketplace = '" . $db->escape($marketplace) . "' 
        LIMIT 1");
        
    if ($query->num_rows) {
        return [
            'api_key' => $query->row['api_key'],
            'api_secret' => $query->row['api_secret'],
            'supplier_id' => $query->row['supplier_id'],
            'endpoint' => getEndpoint($marketplace)
        ];
    }
    
    // Veritabanında ayar yoksa config dosyasından oku
    $configFile = DIR_SYSTEM . "library/entegrator/config_{$marketplace}.php";
    if (file_exists($configFile)) {
        $config = require($configFile);
        $config['endpoint'] = getEndpoint($marketplace);
        return $config;
    }
    
    return false;
}

/**
 * Pazaryeri API endpoint'ini döndürür
 */
function getEndpoint($marketplace) {
    $endpoints = [
        'trendyol' => 'https://api.trendyol.com/sapigw',
        'n11' => 'https://api.n11.com/ws',
        'amazon' => 'https://mws-eu.amazonservices.com',
        'ebay' => 'https://api.ebay.com/ws/api.dll',
        'hepsiburada' => 'https://marketplace-api.hepsiburada.com',
        'ozon' => 'https://api-seller.ozon.ru'
    ];
    
    return $endpoints[$marketplace] ?? '';
}

// Ana işlem
writeLog('SYSTEM', 'CRON_STARTED', "Cron işlemi başladı. Marketplace: $marketplace, Limit: $limit");

if ($marketplace == 'all') {
    // Tüm pazaryerleri için senkronizasyon yap
    foreach ($marketplaces as $mp) {
        syncOrders($mp, $limit);
    }
} else if (in_array($marketplace, $marketplaces)) {
    // Belirli pazaryeri için senkronizasyon yap
    syncOrders($marketplace, $limit);
} else {
    writeLog('SYSTEM', 'ERROR', "Geçersiz pazaryeri: $marketplace");
}

writeLog('SYSTEM', 'CRON_FINISHED', 'Cron işlemi tamamlandı'); 