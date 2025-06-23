<?php
/**
 * MesChain Cron Scheduler Helper
 * Zaman tabanlı senkronizasyon için cron job yöneticisi
 */
class CronScheduler {
    
    private $registry;
    private $db;
    private $config;
    private $log;
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->log = $registry->get('log');
    }
    
    /**
     * Yüksek öncelikli senkronizasyon (5 dakika)
     * Sipariş durumu, kritik stok, ödeme durumu
     */
    public function runHighPrioritySync() {
        $this->log->write('MesChain: High Priority Sync Started');
        
        $marketplaces = ['trendyol', 'amazon', 'n11', 'hepsiburada', 'ebay', 'ozon'];
        $results = [];
        
        foreach ($marketplaces as $marketplace) {
            try {
                if ($this->isMarketplaceEnabled($marketplace)) {
                    $result = $this->syncMarketplaceHighPriority($marketplace);
                    $results[$marketplace] = $result;
                    
                    // Rate limit için bekleme
                    sleep(2);
                }
            } catch (Exception $e) {
                $this->log->write('MesChain High Priority Sync Error (' . $marketplace . '): ' . $e->getMessage());
                $results[$marketplace] = ['error' => $e->getMessage()];
            }
        }
        
        $this->logSyncResult('high_priority', $results);
        $this->log->write('MesChain: High Priority Sync Completed');
        
        return $results;
    }
    
    /**
     * Orta öncelikli senkronizasyon (15 dakika)
     * Fiyat güncellemeleri, stok miktarı, yeni siparişler
     */
    public function runMediumPrioritySync() {
        $this->log->write('MesChain: Medium Priority Sync Started');
        
        $marketplaces = ['trendyol', 'amazon', 'n11', 'hepsiburada', 'ebay', 'ozon'];
        $results = [];
        
        foreach ($marketplaces as $marketplace) {
            try {
                if ($this->isMarketplaceEnabled($marketplace)) {
                    $result = $this->syncMarketplaceMediumPriority($marketplace);
                    $results[$marketplace] = $result;
                    
                    // Rate limit için bekleme
                    sleep(5);
                }
            } catch (Exception $e) {
                $this->log->write('MesChain Medium Priority Sync Error (' . $marketplace . '): ' . $e->getMessage());
                $results[$marketplace] = ['error' => $e->getMessage()];
            }
        }
        
        $this->logSyncResult('medium_priority', $results);
        $this->log->write('MesChain: Medium Priority Sync Completed');
        
        return $results;
    }
    
    /**
     * Düşük öncelikli senkronizasyon (60 dakika)
     * Ürün bilgileri, kategoriler, raporlama
     */
    public function runLowPrioritySync() {
        $this->log->write('MesChain: Low Priority Sync Started');
        
        $marketplaces = ['trendyol', 'amazon', 'n11', 'hepsiburada', 'ebay', 'ozon'];
        $results = [];
        
        foreach ($marketplaces as $marketplace) {
            try {
                if ($this->isMarketplaceEnabled($marketplace)) {
                    $result = $this->syncMarketplaceLowPriority($marketplace);
                    $results[$marketplace] = $result;
                    
                    // Rate limit için bekleme
                    sleep(10);
                }
            } catch (Exception $e) {
                $this->log->write('MesChain Low Priority Sync Error (' . $marketplace . '): ' . $e->getMessage());
                $results[$marketplace] = ['error' => $e->getMessage()];
            }
        }
        
        $this->logSyncResult('low_priority', $results);
        $this->log->write('MesChain: Low Priority Sync Completed');
        
        return $results;
    }
    
    /**
     * Pazaryeri yüksek öncelik senkronizasyonu
     */
    private function syncMarketplaceHighPriority($marketplace) {
        $results = [
            'orders_updated' => 0,
            'critical_stock_alerts' => 0,
            'payment_status_updates' => 0,
            'api_calls' => 0
        ];
        
        // Rate limit kontrolü
        if (!$this->checkRateLimit($marketplace, 'high')) {
            throw new Exception('Rate limit exceeded for ' . $marketplace);
        }
        
        switch ($marketplace) {
            case 'trendyol':
                $results = $this->syncTrendyolHighPriority();
                break;
            case 'amazon':
                $results = $this->syncAmazonHighPriority();
                break;
            case 'n11':
                $results = $this->syncN11HighPriority();
                break;
            case 'hepsiburada':
                $results = $this->syncHepsiburadaHighPriority();
                break;
            case 'ebay':
                $results = $this->syncEbayHighPriority();
                break;
            case 'ozon':
                $results = $this->syncOzonHighPriority();
                break;
        }
        
        // API çağrı sayısını logla
        $this->logApiCall($marketplace, 'high_priority', $results['api_calls']);
        
        return $results;
    }
    
    /**
     * Pazaryeri orta öncelik senkronizasyonu
     */
    private function syncMarketplaceMediumPriority($marketplace) {
        $results = [
            'prices_updated' => 0,
            'stock_updated' => 0,
            'new_orders' => 0,
            'api_calls' => 0
        ];
        
        if (!$this->checkRateLimit($marketplace, 'medium')) {
            throw new Exception('Rate limit exceeded for ' . $marketplace);
        }
        
        switch ($marketplace) {
            case 'trendyol':
                $results = $this->syncTrendyolMediumPriority();
                break;
            case 'amazon':
                $results = $this->syncAmazonMediumPriority();
                break;
            case 'n11':
                $results = $this->syncN11MediumPriority();
                break;
            case 'hepsiburada':
                $results = $this->syncHepsiburadaMediumPriority();
                break;
            case 'ebay':
                $results = $this->syncEbayMediumPriority();
                break;
            case 'ozon':
                $results = $this->syncOzonMediumPriority();
                break;
        }
        
        $this->logApiCall($marketplace, 'medium_priority', $results['api_calls']);
        
        return $results;
    }
    
    /**
     * Pazaryeri düşük öncelik senkronizasyonu
     */
    private function syncMarketplaceLowPriority($marketplace) {
        $results = [
            'products_updated' => 0,
            'categories_synced' => 0,
            'reports_generated' => 0,
            'api_calls' => 0
        ];
        
        if (!$this->checkRateLimit($marketplace, 'low')) {
            throw new Exception('Rate limit exceeded for ' . $marketplace);
        }
        
        switch ($marketplace) {
            case 'trendyol':
                $results = $this->syncTrendyolLowPriority();
                break;
            case 'amazon':
                $results = $this->syncAmazonLowPriority();
                break;
            case 'n11':
                $results = $this->syncN11LowPriority();
                break;
            case 'hepsiburada':
                $results = $this->syncHepsiburadaLowPriority();
                break;
            case 'ebay':
                $results = $this->syncEbayLowPriority();
                break;
            case 'ozon':
                $results = $this->syncOzonLowPriority();
                break;
        }
        
        $this->logApiCall($marketplace, 'low_priority', $results['api_calls']);
        
        return $results;
    }
    
    /**
     * Trendyol yüksek öncelik senkronizasyonu
     */
    private function syncTrendyolHighPriority() {
        // Gerçek API çağrıları burada olacak
        // Şimdilik simülasyon
        return [
            'orders_updated' => rand(0, 5),
            'critical_stock_alerts' => rand(0, 2),
            'payment_status_updates' => rand(0, 3),
            'api_calls' => 3
        ];
    }
    
    /**
     * Amazon yüksek öncelik senkronizasyonu
     */
    private function syncAmazonHighPriority() {
        return [
            'orders_updated' => rand(0, 8),
            'critical_stock_alerts' => rand(0, 3),
            'payment_status_updates' => rand(0, 4),
            'api_calls' => 4
        ];
    }
    
    /**
     * N11 yüksek öncelik senkronizasyonu
     */
    private function syncN11HighPriority() {
        return [
            'orders_updated' => rand(0, 3),
            'critical_stock_alerts' => rand(0, 1),
            'payment_status_updates' => rand(0, 2),
            'api_calls' => 2
        ];
    }
    
    /**
     * Hepsiburada yüksek öncelik senkronizasyonu
     */
    private function syncHepsiburadaHighPriority() {
        return [
            'orders_updated' => rand(0, 6),
            'critical_stock_alerts' => rand(0, 2),
            'payment_status_updates' => rand(0, 3),
            'api_calls' => 3
        ];
    }
    
    /**
     * eBay yüksek öncelik senkronizasyonu
     */
    private function syncEbayHighPriority() {
        return [
            'orders_updated' => rand(0, 4),
            'critical_stock_alerts' => rand(0, 1),
            'payment_status_updates' => rand(0, 2),
            'api_calls' => 2
        ];
    }
    
    /**
     * Ozon yüksek öncelik senkronizasyonu
     */
    private function syncOzonHighPriority() {
        return [
            'orders_updated' => rand(0, 7),
            'critical_stock_alerts' => rand(0, 2),
            'payment_status_updates' => rand(0, 3),
            'api_calls' => 3
        ];
    }
    
    /**
     * Orta öncelik senkronizasyon metodları (simülasyon)
     */
    private function syncTrendyolMediumPriority() {
        return [
            'prices_updated' => rand(10, 25),
            'stock_updated' => rand(15, 35),
            'new_orders' => rand(0, 8),
            'api_calls' => 5
        ];
    }
    
    private function syncAmazonMediumPriority() {
        return [
            'prices_updated' => rand(20, 40),
            'stock_updated' => rand(25, 50),
            'new_orders' => rand(0, 12),
            'api_calls' => 7
        ];
    }
    
    private function syncN11MediumPriority() {
        return [
            'prices_updated' => rand(5, 15),
            'stock_updated' => rand(8, 20),
            'new_orders' => rand(0, 5),
            'api_calls' => 3
        ];
    }
    
    private function syncHepsiburadaMediumPriority() {
        return [
            'prices_updated' => rand(12, 28),
            'stock_updated' => rand(18, 35),
            'new_orders' => rand(0, 9),
            'api_calls' => 6
        ];
    }
    
    private function syncEbayMediumPriority() {
        return [
            'prices_updated' => rand(8, 18),
            'stock_updated' => rand(10, 25),
            'new_orders' => rand(0, 6),
            'api_calls' => 4
        ];
    }
    
    private function syncOzonMediumPriority() {
        return [
            'prices_updated' => rand(15, 30),
            'stock_updated' => rand(20, 40),
            'new_orders' => rand(0, 10),
            'api_calls' => 6
        ];
    }
    
    /**
     * Düşük öncelik senkronizasyon metodları (simülasyon)
     */
    private function syncTrendyolLowPriority() {
        return [
            'products_updated' => rand(50, 100),
            'categories_synced' => rand(5, 15),
            'reports_generated' => rand(1, 3),
            'api_calls' => 8
        ];
    }
    
    private function syncAmazonLowPriority() {
        return [
            'products_updated' => rand(80, 150),
            'categories_synced' => rand(8, 20),
            'reports_generated' => rand(2, 5),
            'api_calls' => 12
        ];
    }
    
    private function syncN11LowPriority() {
        return [
            'products_updated' => rand(30, 60),
            'categories_synced' => rand(3, 10),
            'reports_generated' => rand(1, 2),
            'api_calls' => 5
        ];
    }
    
    private function syncHepsiburadaLowPriority() {
        return [
            'products_updated' => rand(60, 120),
            'categories_synced' => rand(6, 18),
            'reports_generated' => rand(1, 4),
            'api_calls' => 10
        ];
    }
    
    private function syncEbayLowPriority() {
        return [
            'products_updated' => rand(40, 80),
            'categories_synced' => rand(4, 12),
            'reports_generated' => rand(1, 3),
            'api_calls' => 7
        ];
    }
    
    private function syncOzonLowPriority() {
        return [
            'products_updated' => rand(70, 130),
            'categories_synced' => rand(7, 16),
            'reports_generated' => rand(2, 4),
            'api_calls' => 11
        ];
    }
    
    /**
     * Pazaryeri aktif mi kontrol et
     */
    private function isMarketplaceEnabled($marketplace) {
        $setting = $this->config->get('module_' . $marketplace . '_status');
        return !empty($setting);
    }
    
    /**
     * Rate limit kontrolü
     */
    private function checkRateLimit($marketplace, $priority) {
        $limits = $this->getRateLimits($marketplace);
        $recentCalls = $this->getRecentApiCalls($marketplace, 60); // Son 1 dakika
        
        switch ($priority) {
            case 'high':
                return $recentCalls < ($limits['per_minute'] * 0.8); // %80 kullan
            case 'medium':
                return $recentCalls < ($limits['per_minute'] * 0.6); // %60 kullan
            case 'low':
                return $recentCalls < ($limits['per_minute'] * 0.4); // %40 kullan
        }
        
        return false;
    }
    
    /**
     * Pazaryeri rate limit ayarlarını getir
     */
    private function getRateLimits($marketplace) {
        $defaults = [
            'trendyol' => ['per_minute' => 30, 'per_hour' => 1000],
            'amazon' => ['per_minute' => 20, 'per_hour' => 800],
            'n11' => ['per_minute' => 40, 'per_hour' => 1200],
            'hepsiburada' => ['per_minute' => 25, 'per_hour' => 900],
            'ebay' => ['per_minute' => 35, 'per_hour' => 1100],
            'ozon' => ['per_minute' => 30, 'per_hour' => 1000]
        ];
        
        return isset($defaults[$marketplace]) ? $defaults[$marketplace] : ['per_minute' => 20, 'per_hour' => 600];
    }
    
    /**
     * Son API çağrı sayısını getir
     */
    private function getRecentApiCalls($marketplace, $seconds) {
        $query = $this->db->query("
            SELECT COUNT(*) as call_count 
            FROM " . DB_PREFIX . "meschain_api_logs 
            WHERE marketplace = '" . $this->db->escape($marketplace) . "' 
            AND timestamp > " . (time() - $seconds)
        );
        
        return isset($query->row['call_count']) ? (int)$query->row['call_count'] : 0;
    }
    
    /**
     * API çağrısını logla
     */
    private function logApiCall($marketplace, $priority, $callCount) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_api_logs 
            (marketplace, priority, call_count, timestamp, date_added) 
            VALUES (
                '" . $this->db->escape($marketplace) . "',
                '" . $this->db->escape($priority) . "',
                " . (int)$callCount . ",
                " . time() . ",
                NOW()
            )
        ");
    }
    
    /**
     * Senkronizasyon sonucunu logla
     */
    private function logSyncResult($priority, $results) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_sync_logs 
            (priority, results, timestamp, date_added) 
            VALUES (
                '" . $this->db->escape($priority) . "',
                '" . $this->db->escape(json_encode($results)) . "',
                " . time() . ",
                NOW()
            )
        ");
    }
    
    /**
     * Cron job durumunu kontrol et
     */
    public function getCronStatus() {
        $status = [
            'high_priority' => $this->getLastSyncTime('high_priority'),
            'medium_priority' => $this->getLastSyncTime('medium_priority'),
            'low_priority' => $this->getLastSyncTime('low_priority'),
            'total_api_calls_today' => $this->getTodayApiCalls(),
            'active_marketplaces' => $this->getActiveMarketplaces()
        ];
        
        return $status;
    }
    
    /**
     * Son senkronizasyon zamanını getir
     */
    private function getLastSyncTime($priority) {
        $query = $this->db->query("
            SELECT timestamp 
            FROM " . DB_PREFIX . "meschain_sync_logs 
            WHERE priority = '" . $this->db->escape($priority) . "' 
            ORDER BY timestamp DESC 
            LIMIT 1
        ");
        
        return isset($query->row['timestamp']) ? $query->row['timestamp'] : 0;
    }
    
    /**
     * Bugünkü toplam API çağrı sayısı
     */
    private function getTodayApiCalls() {
        $todayStart = strtotime('today');
        
        $query = $this->db->query("
            SELECT SUM(call_count) as total_calls 
            FROM " . DB_PREFIX . "meschain_api_logs 
            WHERE timestamp >= " . $todayStart
        );
        
        return isset($query->row['total_calls']) ? (int)$query->row['total_calls'] : 0;
    }
    
    /**
     * Aktif pazaryeri sayısı
     */
    private function getActiveMarketplaces() {
        $marketplaces = ['trendyol', 'amazon', 'n11', 'hepsiburada', 'ebay', 'ozon'];
        $active = 0;
        
        foreach ($marketplaces as $marketplace) {
            if ($this->isMarketplaceEnabled($marketplace)) {
                $active++;
            }
        }
        
        return $active;
    }
} 