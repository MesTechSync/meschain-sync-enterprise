<?php
/**
 * MesChain Production Monitor
 * Gerçek zamanlı production monitoring sistemi
 * @author MesChain-Sync Team
 * @version 1.0.0
 */

class MesChainProductionMonitor {
    private $db;
    private $logger;
    private $config;
    private $cache;
    
    public function __construct($db, $logger, $config, $cache) {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = $config;
        $this->cache = $cache;
    }
    
    /**
     * Sistem durumunu kontrol et
     */
    public function checkSystemHealth() {
        $health = array(
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'services' => array(),
            'metrics' => array()
        );
        
        // Database bağlantısı kontrolü
        $health['services']['database'] = $this->checkDatabaseHealth();
        
        // Marketplace API'leri kontrolü
        $health['services']['trendyol'] = $this->checkTrendyolAPI();
        $health['services']['n11'] = $this->checkN11API();
        $health['services']['ozon'] = $this->checkOzonAPI();
        $health['services']['amazon'] = $this->checkAmazonAPI();
        $health['services']['hepsiburada'] = $this->checkHepsiburadaAPI();
        
        // Sistem metrikleri
        $health['metrics']['memory_usage'] = $this->getMemoryUsage();
        $health['metrics']['disk_usage'] = $this->getDiskUsage();
        $health['metrics']['queue_size'] = $this->getQueueSize();
        $health['metrics']['error_rate'] = $this->getErrorRate();
        
        // Genel durum değerlendirmesi
        $health['status'] = $this->evaluateOverallHealth($health);
        
        // Cache'e kaydet
        $this->cache->set('meschain_system_health', $health, 300); // 5 dakika
        
        // Alert kontrolü yap
        $this->triggerAlertCheck($health);
        
        return $health;
    }
    
    /**
     * Database sağlık kontrolü
     */
    private function checkDatabaseHealth() {
        try {
            $start_time = microtime(true);
            $result = $this->db->query("SELECT 1");
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            
            return array(
                'status' => 'healthy',
                'response_time' => $response_time . 'ms',
                'last_check' => date('Y-m-d H:i:s')
            );
        } catch (Exception $e) {
            $this->logger->error('Database health check failed: ' . $e->getMessage());
            return array(
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'last_check' => date('Y-m-d H:i:s')
            );
        }
    }
    
    /**
     * Trendyol API kontrolü
     */
    private function checkTrendyolAPI() {
        try {
            $start_time = microtime(true);
            
            // API endpoint'e test isteği
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.trendyol.com/sapigw/suppliers/check-supplier-request');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            
            return array(
                'status' => ($http_code >= 200 && $http_code < 300) ? 'healthy' : 'degraded',
                'http_code' => $http_code,
                'response_time' => $response_time . 'ms',
                'last_check' => date('Y-m-d H:i:s')
            );
        } catch (Exception $e) {
            return array(
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'last_check' => date('Y-m-d H:i:s')
            );
        }
    }
    
    /**
     * N11 API kontrolü
     */
    private function checkN11API() {
        try {
            $start_time = microtime(true);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.n11.com/ws/1.0/CategoryService.wsdl');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            
            return array(
                'status' => ($http_code >= 200 && $http_code < 300) ? 'healthy' : 'degraded',
                'http_code' => $http_code,
                'response_time' => $response_time . 'ms',
                'last_check' => date('Y-m-d H:i:s')
            );
        } catch (Exception $e) {
            return array(
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'last_check' => date('Y-m-d H:i:s')
            );
        }
    }
    
    /**
     * Ozon API kontrolü
     */
    private function checkOzonAPI() {
        try {
            $start_time = microtime(true);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api-seller.ozon.ru/v1/ping');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            
            return array(
                'status' => ($http_code >= 200 && $http_code < 300) ? 'healthy' : 'degraded',
                'http_code' => $http_code,
                'response_time' => $response_time . 'ms',
                'last_check' => date('Y-m-d H:i:s')
            );
        } catch (Exception $e) {
            return array(
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'last_check' => date('Y-m-d H:i:s')
            );
        }
    }
    
    /**
     * Amazon API kontrolü
     */
    private function checkAmazonAPI() {
        try {
            $start_time = microtime(true);
            
            // Amazon SP-API endpoint test
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://sellingpartnerapi-eu.amazon.com/');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            
            return array(
                'status' => ($http_code >= 200 && $http_code < 500) ? 'healthy' : 'degraded',
                'http_code' => $http_code,
                'response_time' => $response_time . 'ms',
                'last_check' => date('Y-m-d H:i:s')
            );
        } catch (Exception $e) {
            return array(
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'last_check' => date('Y-m-d H:i:s')
            );
        }
    }
    
    /**
     * Hepsiburada API kontrolü
     */
    private function checkHepsiburadaAPI() {
        try {
            $start_time = microtime(true);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://mpop.hepsiburada.com/api/ping');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            
            return array(
                'status' => ($http_code >= 200 && $http_code < 300) ? 'healthy' : 'degraded',
                'http_code' => $http_code,
                'response_time' => $response_time . 'ms',
                'last_check' => date('Y-m-d H:i:s')
            );
        } catch (Exception $e) {
            return array(
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'last_check' => date('Y-m-d H:i:s')
            );
        }
    }
    
    /**
     * Bellek kullanımı
     */
    private function getMemoryUsage() {
        $bytes = memory_get_usage(true);
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } else {
            $bytes = $bytes . ' Bytes';
        }
        
        return $bytes;
    }
    
    /**
     * Disk kullanımı
     */
    private function getDiskUsage() {
        $bytes = disk_free_space('/');
        $total = disk_total_space('/');
        $used = $total - $bytes;
        $percent = round(($used / $total) * 100, 2);
        
        return array(
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($bytes),
            'total' => $this->formatBytes($total),
            'percent' => $percent . '%'
        );
    }
    
    /**
     * Kuyruk boyutu
     */
    private function getQueueSize() {
        try {
            $result = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_queue` WHERE status = 'pending'");
            return $result->row['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Hata oranı (son 1 saat)
     */
    private function getErrorRate() {
        try {
            $result = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_logs` WHERE level = 'error' AND date_added > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            return $result->row['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Genel sağlık durumu değerlendirmesi
     */
    private function evaluateOverallHealth($health) {
        $unhealthy_services = 0;
        $total_services = count($health['services']);
        
        foreach ($health['services'] as $service) {
            if ($service['status'] === 'unhealthy') {
                $unhealthy_services++;
            }
        }
        
        if ($unhealthy_services === 0) {
            return 'healthy';
        } elseif ($unhealthy_services < $total_services / 2) {
            return 'degraded';
        } else {
            return 'unhealthy';
        }
    }
    
    /**
     * Alert kontrolü tetikle
     */
    private function triggerAlertCheck($health) {
        try {
            require_once(DIR_SYSTEM . 'library/meschain/alert/alert_manager.php');
            $alert_manager = new MesChainAlertManager($this->db, $this->logger, $this->config);
            $alert_manager->checkAndTriggerAlerts($health);
        } catch (Exception $e) {
            $this->logger->error('Alert kontrol hatası: ' . $e->getMessage());
        }
    }
    
    /**
     * Byte formatını düzenle
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

?> 