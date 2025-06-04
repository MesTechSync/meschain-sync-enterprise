<?php
/**
 * Cron Manager System
 * Developer 1: Centralized cron job management for multi-user system
 */
class CronManager {
    private $db;
    private $config;
    private $logger;
    
    public function __construct($db, $config = null) {
        $this->db = $db;
        $this->config = $config;
        $this->logger = new Log('cron_manager.log');
    }
    
    /**
     * Ana cron job runner
     */
    public function runAllJobs() {
        $this->log('CRON_START', 'Tüm cron job\'lar başlatılıyor');
        
        try {
            // Aktif kullanıcıları al
            $users = $this->getActiveUsers();
            
            foreach ($users as $user) {
                $this->runUserJobs($user);
            }
            
            // Sistem temizlik işlemleri
            $this->runSystemCleanup();
            
            $this->log('CRON_COMPLETE', 'Tüm cron job\'lar tamamlandı');
            
        } catch (Exception $e) {
            $this->log('CRON_ERROR', 'Cron job hatası: ' . $e->getMessage());
        }
    }
    
    /**
     * Kullanıcı bazlı job'ları çalıştır
     */
    public function runUserJobs($user) {
        $user_id = $user['user_id'];
        $this->log('USER_CRON_START', "User $user_id için job'lar başlatılıyor");
        
        try {
            // N11 senkronizasyonu
            if ($this->hasMarketplaceAccess($user_id, 'n11')) {
                $this->runN11Sync($user_id);
            }
            
            // Trendyol senkronizasyonu
            if ($this->hasMarketplaceAccess($user_id, 'trendyol')) {
                $this->runTrendyolSync($user_id);
            }
            
            // Amazon senkronizasyonu (Developer 2)
            if ($this->hasMarketplaceAccess($user_id, 'amazon')) {
                $this->runAmazonSync($user_id);
            }
            
            // Dropshipping senkronizasyonu
            if ($this->hasDropshippingAccess($user_id)) {
                $this->runDropshippingSync($user_id);
            }
            
            $this->log('USER_CRON_COMPLETE', "User $user_id için job'lar tamamlandı");
            
        } catch (Exception $e) {
            $this->log('USER_CRON_ERROR', "User $user_id cron hatası: " . $e->getMessage());
        }
    }
    
    /**
     * N11 senkronizasyonu
     */
    private function runN11Sync($user_id) {
        $this->log('N11_SYNC_START', "User $user_id N11 senkronizasyonu başlatılıyor");
        
        try {
            // API ayarları kontrolü
            if (!$this->hasValidApiSettings($user_id, 'n11')) {
                $this->log('N11_SYNC_SKIP', "User $user_id N11 API ayarları eksik");
                return;
            }
            
            // N11 controller'ı çağır
            $url = $this->getBaseUrl() . 'index.php?route=extension/module/n11_enhanced/auto_sync&user_id=' . $user_id;
            $result = $this->executeCurlRequest($url);
            
            if ($result['success']) {
                $this->log('N11_SYNC_SUCCESS', "User $user_id N11 senkronizasyonu başarılı");
            } else {
                $this->log('N11_SYNC_ERROR', "User $user_id N11 senkronizasyon hatası: " . $result['error']);
            }
            
        } catch (Exception $e) {
            $this->log('N11_SYNC_ERROR', "User $user_id N11 senkronizasyon exception: " . $e->getMessage());
        }
    }
    
    /**
     * Trendyol senkronizasyonu
     */
    private function runTrendyolSync($user_id) {
        $this->log('TRENDYOL_SYNC_START', "User $user_id Trendyol senkronizasyonu başlatılıyor");
        
        try {
            if (!$this->hasValidApiSettings($user_id, 'trendyol')) {
                $this->log('TRENDYOL_SYNC_SKIP', "User $user_id Trendyol API ayarları eksik");
                return;
            }
            
            // Trendyol controller'ı çağır
            $url = $this->getBaseUrl() . 'index.php?route=extension/module/trendyol_enhanced/auto_sync&user_id=' . $user_id;
            $result = $this->executeCurlRequest($url);
            
            if ($result['success']) {
                $this->log('TRENDYOL_SYNC_SUCCESS', "User $user_id Trendyol senkronizasyonu başarılı");
            } else {
                $this->log('TRENDYOL_SYNC_ERROR', "User $user_id Trendyol senkronizasyon hatası: " . $result['error']);
            }
            
        } catch (Exception $e) {
            $this->log('TRENDYOL_SYNC_ERROR', "User $user_id Trendyol senkronizasyon exception: " . $e->getMessage());
        }
    }
    
    /**
     * Amazon senkronizasyonu (Developer 2 tarafından implement edilecek)
     */
    private function runAmazonSync($user_id) {
        $this->log('AMAZON_SYNC_START', "User $user_id Amazon senkronizasyonu başlatılıyor");
        
        try {
            if (!$this->hasValidApiSettings($user_id, 'amazon')) {
                $this->log('AMAZON_SYNC_SKIP', "User $user_id Amazon API ayarları eksik");
                return;
            }
            
            // Amazon controller'ı çağır (Developer 2 tarafından oluşturulacak)
            $url = $this->getBaseUrl() . 'index.php?route=extension/module/amazon/auto_sync&user_id=' . $user_id;
            $result = $this->executeCurlRequest($url);
            
            if ($result['success']) {
                $this->log('AMAZON_SYNC_SUCCESS', "User $user_id Amazon senkronizasyonu başarılı");
            } else {
                $this->log('AMAZON_SYNC_ERROR', "User $user_id Amazon senkronizasyon hatası: " . $result['error']);
            }
            
        } catch (Exception $e) {
            $this->log('AMAZON_SYNC_ERROR', "User $user_id Amazon senkronizasyon exception: " . $e->getMessage());
        }
    }
    
    /**
     * Dropshipping senkronizasyonu
     */
    private function runDropshippingSync($user_id) {
        $this->log('DROPSHIPPING_SYNC_START', "User $user_id Dropshipping senkronizasyonu başlatılıyor");
        
        try {
            // Dropshipping controller'ı çağır
            $url = $this->getBaseUrl() . 'index.php?route=extension/module/dropshipping_manager/auto_sync&user_id=' . $user_id;
            $result = $this->executeCurlRequest($url);
            
            if ($result['success']) {
                $this->log('DROPSHIPPING_SYNC_SUCCESS', "User $user_id Dropshipping senkronizasyonu başarılı");
            } else {
                $this->log('DROPSHIPPING_SYNC_ERROR', "User $user_id Dropshipping senkronizasyon hatası: " . $result['error']);
            }
            
        } catch (Exception $e) {
            $this->log('DROPSHIPPING_SYNC_ERROR', "User $user_id Dropshipping senkronizasyon exception: " . $e->getMessage());
        }
    }
    
    /**
     * Sistem temizlik işlemleri
     */
    private function runSystemCleanup() {
        $this->log('CLEANUP_START', 'Sistem temizlik işlemleri başlatılıyor');
        
        try {
            // Eski logları temizle (30 günden eski)
            $this->cleanOldLogs();
            
            // Eski session'ları temizle
            $this->cleanOldSessions();
            
            // Cache temizleme (gerekirse)
            $this->cleanOldCache();
            
            // Başarısız queue kayıtlarını temizle
            $this->cleanFailedQueue();
            
            $this->log('CLEANUP_COMPLETE', 'Sistem temizlik işlemleri tamamlandı');
            
        } catch (Exception $e) {
            $this->log('CLEANUP_ERROR', 'Sistem temizlik hatası: ' . $e->getMessage());
        }
    }
    
    /**
     * Aktif kullanıcıları al
     */
    private function getActiveUsers() {
        $query = $this->db->query("
            SELECT u.user_id, u.username, ums.role, ums.marketplace_access, ums.dropshipping_enabled
            FROM " . DB_PREFIX . "user u
            INNER JOIN " . DB_PREFIX . "user_meschain_settings ums ON (u.user_id = ums.user_id)
            WHERE u.status = '1' AND ums.status = '1'
        ");
        
        return $query->rows;
    }
    
    /**
     * Pazaryeri erişimi kontrolü
     */
    private function hasMarketplaceAccess($user_id, $marketplace) {
        $query = $this->db->query("
            SELECT marketplace_access FROM " . DB_PREFIX . "user_meschain_settings 
            WHERE user_id = '" . (int)$user_id . "'
        ");
        
        if ($query->num_rows) {
            $access = json_decode($query->row['marketplace_access'], true);
            return in_array($marketplace, $access);
        }
        
        return false;
    }
    
    /**
     * Dropshipping erişimi kontrolü
     */
    private function hasDropshippingAccess($user_id) {
        $query = $this->db->query("
            SELECT dropshipping_enabled FROM " . DB_PREFIX . "user_meschain_settings 
            WHERE user_id = '" . (int)$user_id . "'
        ");
        
        return $query->num_rows && $query->row['dropshipping_enabled'];
    }
    
    /**
     * API ayarları kontrolü
     */
    private function hasValidApiSettings($user_id, $marketplace) {
        $query = $this->db->query("
            SELECT api_data FROM " . DB_PREFIX . "user_api_settings 
            WHERE user_id = '" . (int)$user_id . "' 
            AND marketplace = '" . $this->db->escape($marketplace) . "'
            AND status = '1'
        ");
        
        if ($query->num_rows) {
            $api_data = json_decode($query->row['api_data'], true);
            
            // Her pazaryeri için gerekli alanları kontrol et
            switch ($marketplace) {
                case 'n11':
                    return !empty($api_data['app_key']) && !empty($api_data['app_secret']);
                case 'trendyol':
                    return !empty($api_data['api_key']) && !empty($api_data['api_secret']);
                case 'amazon':
                    return !empty($api_data['access_key']) && !empty($api_data['secret_key']);
                default:
                    return !empty($api_data);
            }
        }
        
        return false;
    }
    
    /**
     * CURL request execute
     */
    private function executeCurlRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 dakika timeout
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($response === false || $http_code !== 200) {
            return array(
                'success' => false,
                'error' => $error ?: 'HTTP Error: ' . $http_code
            );
        }
        
        return array(
            'success' => true,
            'response' => $response
        );
    }
    
    /**
     * Base URL al
     */
    private function getBaseUrl() {
        if ($this->config) {
            return $this->config->get('config_url');
        }
        
        // Fallback
        return 'http://' . $_SERVER['HTTP_HOST'] . '/admin/';
    }
    
    /**
     * Eski logları temizle
     */
    private function cleanOldLogs() {
        // 30 günden eski logları sil
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "user_activity_log 
            WHERE created_date < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        // Sync logları temizle
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "meschain_sync_log 
            WHERE date_added < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
    }
    
    /**
     * Eski session'ları temizle
     */
    private function cleanOldSessions() {
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "session 
            WHERE date_added < DATE_SUB(NOW(), INTERVAL 1 DAY)
        ");
    }
    
    /**
     * Eski cache'leri temizle
     */
    private function cleanOldCache() {
        // OpenCart cache dosyalarını temizle
        $cache_dir = DIR_CACHE;
        $files = glob($cache_dir . 'cache.*');
        
        foreach ($files as $file) {
            if (filemtime($file) < (time() - 86400)) { // 24 saat
                unlink($file);
            }
        }
    }
    
    /**
     * Başarısız queue kayıtlarını temizle
     */
    private function cleanFailedQueue() {
        // 7 günden eski başarısız queue kayıtlarını sil
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "product_sync_queue 
            WHERE status = 'failed' 
            AND created_date < DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        
        // Tamamlanan kayıtları temizle (3 günden eski)
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "product_sync_queue 
            WHERE status = 'completed' 
            AND completed_date < DATE_SUB(NOW(), INTERVAL 3 DAY)
        ");
    }
    
    /**
     * Log kaydet
     */
    private function log($action, $message) {
        $this->logger->write('[' . $action . '] ' . $message);
        
        // Veritabanına da kaydet
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_sync_log SET
                user_id = '0',
                marketplace = 'system',
                action = '" . $this->db->escape($action) . "',
                status = 'info',
                message = '" . $this->db->escape($message) . "',
                date_added = NOW()
        ");
    }
}

// Cron job entry point
if (php_sapi_name() === 'cli') {
    // CLI'den çalıştırılıyor
    require_once(dirname(__FILE__) . '/../../config.php');
    require_once(DIR_SYSTEM . 'startup.php');
    
    // Registry oluştur
    $registry = new Registry();
    
    // Database
    $db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
    $registry->set('db', $db);
    
    // Config
    $config = new Config();
    $registry->set('config', $config);
    
    // Cron manager başlat
    $cron = new CronManager($db, $config);
    
    // Komut satırı argümanları
    $action = isset($argv[1]) ? $argv[1] : 'all';
    $user_id = isset($argv[2]) ? (int)$argv[2] : 0;
    
    switch ($action) {
        case 'all':
            $cron->runAllJobs();
            break;
        case 'user':
            if ($user_id) {
                $user = array('user_id' => $user_id);
                $cron->runUserJobs($user);
            } else {
                echo "User ID gerekli: php cron_manager.php user [user_id]\n";
            }
            break;
        case 'cleanup':
            $cron->runSystemCleanup();
            break;
        default:
            echo "Kullanım:\n";
            echo "  php cron_manager.php all          # Tüm job'ları çalıştır\n";
            echo "  php cron_manager.php user [id]    # Belirli kullanıcı job'ları\n";
            echo "  php cron_manager.php cleanup      # Sistem temizliği\n";
    }
}
?> 