<?php
/**
 * MesChain Super Admin Control Panel
 * Tüm sistemlerin merkezi yönetim paneli
 * @author MesChain-Sync Team
 * @version 1.0.0
 */

class ControllerExtensionModuleMeschainSuperAdmin extends Controller {
    
    public function index() {
        $this->document->setTitle('MesChain Super Admin Panel');
        
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => 'Ana Sayfa',
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Super Admin Panel',
            'href' => $this->url->link('extension/module/meschain_super_admin', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Sistem durumunu getir
        $data['system_status'] = $this->getSystemStatus();
        
        // Marketplace durumlarını getir
        $data['marketplace_status'] = $this->getMarketplaceStatus();
        
        // Son aktiviteleri getir
        $data['recent_activities'] = $this->getRecentActivities();
        
        // Quick stats
        $data['quick_stats'] = $this->getQuickStats();
        
        $data['user_token'] = $this->session->data['user_token'];
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_super_admin', $data));
    }
    
    /**
     * Sistem durumu kontrolü
     */
    public function getSystemStatus() {
        try {
            // Database bağlantısı kontrolü
            $db_status = $this->checkDatabaseStatus();
            
            // Cache durumu
            $cache_status = $this->checkCacheStatus();
            
            // Queue durumu
            $queue_status = $this->checkQueueStatus();
            
            // Cron durumu
            $cron_status = $this->checkCronStatus();
            
            // API durumu
            $api_status = $this->checkAPIStatus();
            
            // Log dosyaları
            $log_status = $this->checkLogStatus();
            
            return array(
                'database' => $db_status,
                'cache' => $cache_status,
                'queue' => $queue_status,
                'cron' => $cron_status,
                'api' => $api_status,
                'logs' => $log_status,
                'overall_status' => $this->calculateOverallStatus(array($db_status, $cache_status, $queue_status, $cron_status, $api_status))
            );
            
        } catch (Exception $e) {
            return array('error' => 'Sistem durumu kontrol edilemedi: ' . $e->getMessage());
        }
    }
    
    /**
     * Database durumu
     */
    private function checkDatabaseStatus() {
        try {
            // Tablolar var mı kontrol et
            $tables = array(
                'meschain_orders',
                'meschain_products', 
                'meschain_queue',
                'meschain_cron_jobs',
                'meschain_logs',
                'meschain_system_health_logs'
            );
            
            $existing_tables = array();
            $missing_tables = array();
            
            foreach ($tables as $table) {
                $result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
                if ($result->num_rows > 0) {
                    $existing_tables[] = $table;
                } else {
                    $missing_tables[] = $table;
                }
            }
            
            // Son 24 saatteki kayıt sayısı
            $recent_records = $this->db->query("
                SELECT COUNT(*) as count 
                FROM `" . DB_PREFIX . "meschain_logs` 
                WHERE date_added >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            
            $status = empty($missing_tables) ? 'healthy' : 'warning';
            
            return array(
                'status' => $status,
                'existing_tables' => count($existing_tables),
                'missing_tables' => $missing_tables,
                'recent_records' => (int)$recent_records->row['count'],
                'response_time' => $this->measureDbResponseTime()
            );
            
        } catch (Exception $e) {
            return array('status' => 'error', 'message' => $e->getMessage());
        }
    }
    
    /**
     * Cache durumu
     */
    private function checkCacheStatus() {
        try {
            $cache = new Cache('file');
            
            // Test cache yazma/okuma
            $test_key = 'meschain_cache_test_' . time();
            $test_value = 'test_data';
            
            $cache->set($test_key, $test_value, 60);
            $retrieved = $cache->get($test_key);
            $cache->delete($test_key);
            
            $is_working = ($retrieved === $test_value);
            
            // Cache klasörü boyutu
            $cache_size = $this->getCacheDirectorySize();
            
            return array(
                'status' => $is_working ? 'healthy' : 'error',
                'is_working' => $is_working,
                'size_mb' => round($cache_size / 1024 / 1024, 2)
            );
            
        } catch (Exception $e) {
            return array('status' => 'error', 'message' => $e->getMessage());
        }
    }
    
    /**
     * Queue durumu
     */
    private function checkQueueStatus() {
        try {
            $pending = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_queue` WHERE status = 'pending'");
            $processing = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_queue` WHERE status = 'processing'");
            $failed = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_queue` WHERE status = 'failed'");
            $completed_today = $this->db->query("
                SELECT COUNT(*) as count 
                FROM `" . DB_PREFIX . "meschain_queue` 
                WHERE status = 'completed' AND DATE(completed_at) = DATE(NOW())
            ");
            
            $pending_count = (int)$pending->row['count'];
            $failed_count = (int)$failed->row['count'];
            
            $status = 'healthy';
            if ($pending_count > 100) $status = 'warning';
            if ($failed_count > 10) $status = 'error';
            
            return array(
                'status' => $status,
                'pending' => $pending_count,
                'processing' => (int)$processing->row['count'],
                'failed' => $failed_count,
                'completed_today' => (int)$completed_today->row['count']
            );
            
        } catch (Exception $e) {
            return array('status' => 'error', 'message' => $e->getMessage());
        }
    }
    
    /**
     * Cron durumu
     */
    private function checkCronStatus() {
        try {
            $active_jobs = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_cron_jobs` WHERE is_active = 1");
            $recent_runs = $this->db->query("
                SELECT COUNT(*) as count 
                FROM `" . DB_PREFIX . "meschain_cron_jobs` 
                WHERE last_run >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $failed_jobs = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_cron_jobs` WHERE status = 'failed'");
            
            $failed_count = (int)$failed_jobs->row['count'];
            $recent_count = (int)$recent_runs->row['count'];
            
            $status = 'healthy';
            if ($failed_count > 2) $status = 'warning';
            if ($recent_count === 0) $status = 'warning';
            
            return array(
                'status' => $status,
                'active_jobs' => (int)$active_jobs->row['count'],
                'recent_runs' => $recent_count,
                'failed_jobs' => $failed_count
            );
            
        } catch (Exception $e) {
            return array('status' => 'error', 'message' => $e->getMessage());
        }
    }
    
    /**
     * API durumu
     */
    private function checkAPIStatus() {
        try {
            $success_rate = $this->db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN response_code = 200 THEN 1 ELSE 0 END) as successful
                FROM `" . DB_PREFIX . "meschain_api_health_logs`
                WHERE date_added >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            
            $total = (int)$success_rate->row['total'];
            $successful = (int)$success_rate->row['successful'];
            
            $success_percentage = $total > 0 ? ($successful / $total) * 100 : 0;
            
            $status = 'healthy';
            if ($success_percentage < 95) $status = 'warning';
            if ($success_percentage < 80) $status = 'error';
            
            return array(
                'status' => $status,
                'success_rate' => round($success_percentage, 2),
                'total_calls' => $total,
                'successful_calls' => $successful
            );
            
        } catch (Exception $e) {
            return array('status' => 'error', 'message' => $e->getMessage());
        }
    }
    
    /**
     * Log durumu
     */
    private function checkLogStatus() {
        try {
            $log_files = array(
                'error.log' => DIR_LOGS . 'error.log',
                'meschain.log' => DIR_LOGS . 'meschain.log'
            );
            
            $log_info = array();
            $total_size = 0;
            
            foreach ($log_files as $name => $path) {
                if (file_exists($path)) {
                    $size = filesize($path);
                    $total_size += $size;
                    $log_info[$name] = array(
                        'exists' => true,
                        'size_mb' => round($size / 1024 / 1024, 2),
                        'modified' => date('Y-m-d H:i:s', filemtime($path))
                    );
                } else {
                    $log_info[$name] = array('exists' => false);
                }
            }
            
            // Son 1 saatteki error log sayısı
            $recent_errors = $this->db->query("
                SELECT COUNT(*) as count 
                FROM `" . DB_PREFIX . "meschain_logs` 
                WHERE log_level = 'error' AND date_added >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            
            $error_count = (int)$recent_errors->row['count'];
            $status = $error_count > 5 ? 'warning' : 'healthy';
            
            return array(
                'status' => $status,
                'files' => $log_info,
                'total_size_mb' => round($total_size / 1024 / 1024, 2),
                'recent_errors' => $error_count
            );
            
        } catch (Exception $e) {
            return array('status' => 'error', 'message' => $e->getMessage());
        }
    }
    
    /**
     * Marketplace durumları
     */
    public function getMarketplaceStatus() {
        $marketplaces = array('trendyol', 'n11', 'ozon', 'amazon', 'hepsiburada', 'ebay');
        $status = array();
        
        foreach ($marketplaces as $marketplace) {
            // Aktif ürün sayısı
            $products = $this->db->query("
                SELECT COUNT(*) as count 
                FROM `" . DB_PREFIX . "meschain_products` 
                WHERE marketplace = '" . $this->db->escape($marketplace) . "' AND status = 'active'
            ");
            
            // Bugünkü siparişler
            $orders = $this->db->query("
                SELECT COUNT(*) as count 
                FROM `" . DB_PREFIX . "meschain_orders` 
                WHERE marketplace = '" . $this->db->escape($marketplace) . "' AND DATE(date_added) = DATE(NOW())
            ");
            
            // Son API çağrısı
            $last_api_call = $this->db->query("
                SELECT date_added 
                FROM `" . DB_PREFIX . "meschain_api_health_logs` 
                WHERE marketplace = '" . $this->db->escape($marketplace) . "' 
                ORDER BY date_added DESC 
                LIMIT 1
            ");
            
            $product_count = (int)$products->row['count'];
            $order_count = (int)$orders->row['count'];
            
            // Durum belirleme
            $marketplace_status = 'inactive';
            if ($product_count > 0) $marketplace_status = 'active';
            if ($order_count > 0) $marketplace_status = 'profitable';
            
            $status[$marketplace] = array(
                'status' => $marketplace_status,
                'products' => $product_count,
                'orders_today' => $order_count,
                'last_api_call' => $last_api_call->num_rows > 0 ? $last_api_call->row['date_added'] : null
            );
        }
        
        return $status;
    }
    
    /**
     * Son aktiviteler
     */
    public function getRecentActivities() {
        try {
            $activities = $this->db->query("
                SELECT 'order' as type, marketplace, 'Yeni sipariş' as message, date_added
                FROM `" . DB_PREFIX . "meschain_orders`
                WHERE date_added >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                
                UNION ALL
                
                SELECT 'log' as type, component as marketplace, message, date_added
                FROM `" . DB_PREFIX . "meschain_logs`
                WHERE log_level = 'error' AND date_added >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                
                ORDER BY date_added DESC
                LIMIT 20
            ");
            
            return $activities->rows;
            
        } catch (Exception $e) {
            return array();
        }
    }
    
    /**
     * Hızlı istatistikler
     */
    public function getQuickStats() {
        try {
            // Bugünkü siparişler
            $today_orders = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_orders` WHERE DATE(date_added) = DATE(NOW())");
            
            // Bu haftaki gelir
            $week_revenue = $this->db->query("SELECT SUM(total_amount) as total FROM `" . DB_PREFIX . "meschain_orders` WHERE date_added >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            
            // Toplam aktif ürün
            $active_products = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_products` WHERE status = 'active'");
            
            // Başarılı API çağrı oranı
            $api_success = $this->db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN response_code = 200 THEN 1 ELSE 0 END) as successful
                FROM `" . DB_PREFIX . "meschain_api_health_logs`
                WHERE date_added >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            
            $api_total = (int)$api_success->row['total'];
            $api_successful = (int)$api_success->row['successful'];
            $api_rate = $api_total > 0 ? round(($api_successful / $api_total) * 100, 1) : 0;
            
            return array(
                'today_orders' => (int)$today_orders->row['count'],
                'week_revenue' => (float)$week_revenue->row['total'],
                'active_products' => (int)$active_products->row['count'],
                'api_success_rate' => $api_rate
            );
            
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }
    
    /**
     * Sistem yeniden başlatma
     */
    public function restartSystem() {
        $json = array();
        
        try {
            // Cache temizle
            $cache = new Cache('file');
            $cache->delete('meschain_*');
            
            // Queue'yu yeniden başlat
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_queue` SET status = 'pending' WHERE status = 'processing'");
            
            // Cron job'ları aktif et
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_cron_jobs` SET status = 'pending' WHERE status = 'running'");
            
            $json['success'] = 'Sistem başarıyla yeniden başlatıldı';
            
        } catch (Exception $e) {
            $json['error'] = 'Sistem yeniden başlatılamadı: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Emergency stop
     */
    public function emergencyStop() {
        $json = array();
        
        try {
            // Tüm cron job'ları durdur
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_cron_jobs` SET is_active = 0");
            
            // Queue'daki işleri durdur
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_queue` SET status = 'failed' WHERE status = 'processing'");
            
            $json['success'] = 'Acil durdurma başarılı - Tüm sistemler durduruldu';
            
        } catch (Exception $e) {
            $json['error'] = 'Acil durdurma başarısız: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Yardımcı fonksiyonlar
     */
    private function calculateOverallStatus($statuses) {
        $error_count = 0;
        $warning_count = 0;
        
        foreach ($statuses as $status) {
            if ($status['status'] === 'error') $error_count++;
            if ($status['status'] === 'warning') $warning_count++;
        }
        
        if ($error_count > 0) return 'error';
        if ($warning_count > 0) return 'warning';
        return 'healthy';
    }
    
    private function measureDbResponseTime() {
        $start = microtime(true);
        $this->db->query("SELECT 1");
        $end = microtime(true);
        return round(($end - $start) * 1000, 2); // ms
    }
    
    private function getCacheDirectorySize() {
        $size = 0;
        $cache_dir = DIR_CACHE;
        
        if (is_dir($cache_dir)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cache_dir));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        
        return $size;
    }
}

?> 