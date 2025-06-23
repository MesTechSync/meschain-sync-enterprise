<?php
/**
 * MesChain Real-Time Dashboard Controller
 * Gerçek zamanlı sistem durumu dashboard'u
 * @author MesChain-Sync Team
 * @version 1.0.0
 */

class ControllerExtensionModuleMeschainRealTimeDashboard extends Controller {
    private $error = array();
    
    /**
     * Ana dashboard sayfası
     */
    public function index() {
        $this->load->language('extension/module/meschain_sync');
        
        $this->document->setTitle('MesChain - Real-Time Dashboard');
        
        // Production monitor'ü yükle
        require_once(DIR_SYSTEM . 'library/meschain/monitoring/production_monitor.php');
        
        $monitor = new MesChainProductionMonitor(
            $this->db, 
            new Log('meschain.log'),
            $this->config,
            new Cache('file')
        );
        
        // Sistem durumunu al
        $data['system_health'] = $monitor->checkSystemHealth();
        
        // Son 24 saatlik istatistikler
        $data['daily_stats'] = $this->getDailyStats();
        
        // Aktif marketplace'ler
        $data['marketplaces'] = $this->getMarketplaceStats();
        
        // Son webhook bildirimleri
        $data['recent_webhooks'] = $this->getRecentWebhooks();
        
        // Son hata logları
        $data['recent_errors'] = $this->getRecentErrors();
        
        // Navigation
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'MesChain Real-Time Dashboard',
            'href' => $this->url->link('extension/module/meschain_real_time_dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Action URLs
        $data['ajax_health_check'] = $this->url->link('extension/module/meschain_real_time_dashboard/getSystemHealth', 'user_token=' . $this->session->data['user_token'], true);
        $data['ajax_marketplace_stats'] = $this->url->link('extension/module/meschain_real_time_dashboard/getMarketplaceStats', 'user_token=' . $this->session->data['user_token'], true);
        
        // Header ve footer
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_real_time_dashboard', $data));
    }
    
    /**
     * AJAX: Sistem durumu getir
     */
    public function getSystemHealth() {
        // Production monitor'ü yükle
        require_once(DIR_SYSTEM . 'library/meschain/monitoring/production_monitor.php');
        
        $monitor = new MesChainProductionMonitor(
            $this->db, 
            $this->log,
            $this->config,
            $this->cache
        );
        
        $health = $monitor->checkSystemHealth();
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($health));
    }
    
    /**
     * AJAX: Marketplace istatistikleri getir
     */
    public function getMarketplaceStatsAjax() {
        $stats = $this->getMarketplaceStats();
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($stats));
    }
    
    /**
     * Son 24 saatlik istatistikler
     */
    private function getDailyStats() {
        $stats = array();
        
        try {
            // Toplam sipariş sayısı
            $query = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_orders` WHERE date_added > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $stats['total_orders'] = $query->row['count'];
            
            // Toplam ürün senkronizasyonu
            $query = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_product_sync` WHERE date_updated > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $stats['synced_products'] = $query->row['count'];
            
            // Toplam webhook çağrısı
            $query = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_webhook_logs` WHERE date_added > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $stats['webhook_calls'] = $query->row['count'];
            
            // Toplam hata sayısı
            $query = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_logs` WHERE level = 'error' AND date_added > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $stats['error_count'] = $query->row['count'];
            
        } catch (Exception $e) {
            $stats = array(
                'total_orders' => 0,
                'synced_products' => 0,
                'webhook_calls' => 0,
                'error_count' => 0
            );
        }
        
        return $stats;
    }
    
    /**
     * Marketplace istatistikleri
     */
    private function getMarketplaceStats() {
        $marketplaces = array(
            'trendyol' => array('name' => 'Trendyol', 'orders' => 0, 'products' => 0, 'status' => 'active'),
            'n11' => array('name' => 'N11', 'orders' => 0, 'products' => 0, 'status' => 'active'),
            'ozon' => array('name' => 'Ozon', 'orders' => 0, 'products' => 0, 'status' => 'active'),
            'amazon' => array('name' => 'Amazon', 'orders' => 0, 'products' => 0, 'status' => 'active'),
            'hepsiburada' => array('name' => 'Hepsiburada', 'orders' => 0, 'products' => 0, 'status' => 'active'),
            'ebay' => array('name' => 'eBay', 'orders' => 0, 'products' => 0, 'status' => 'inactive')
        );
        
        try {
            foreach ($marketplaces as $marketplace => &$data) {
                // Son 24 saatteki siparişler
                $query = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_orders` WHERE marketplace = '" . $this->db->escape($marketplace) . "' AND date_added > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
                $data['orders'] = $query->row['count'];
                
                // Aktif ürün sayısı
                $query = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_products` WHERE marketplace = '" . $this->db->escape($marketplace) . "' AND status = '1'");
                $data['products'] = $query->row['count'];
            }
        } catch (Exception $e) {
            // Hata durumunda varsayılan değerler kalsın
        }
        
        return $marketplaces;
    }
    
    /**
     * Son webhook bildirimleri
     */
    private function getRecentWebhooks() {
        try {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_webhook_logs` ORDER BY date_added DESC LIMIT 10");
            return $query->rows;
        } catch (Exception $e) {
            return array();
        }
    }
    
    /**
     * Son hata logları
     */
    private function getRecentErrors() {
        try {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_logs` WHERE level = 'error' ORDER BY date_added DESC LIMIT 10");
            return $query->rows;
        } catch (Exception $e) {
            return array();
        }
    }
    
    /**
     * Webhook testleri
     */
    public function testWebhooks() {
        $results = array();
        
        // Trendyol webhook testi
        $results['trendyol'] = $this->testTrendyolWebhook();
        
        // N11 webhook testi
        $results['n11'] = $this->testN11Webhook();
        
        // Ozon webhook testi
        $results['ozon'] = $this->testOzonWebhook();
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($results));
    }
    
    /**
     * Trendyol webhook testi
     */
    private function testTrendyolWebhook() {
        try {
            $test_data = array(
                'eventType' => 'ORDER_CREATED',
                'timestamp' => time(),
                'data' => array(
                    'orderId' => 'TEST_' . uniqid(),
                    'status' => 'Created'
                )
            );
            
            $webhook_url = HTTPS_CATALOG . 'index.php?route=extension/module/trendyol_webhook/receive';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $webhook_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return array(
                'status' => ($http_code >= 200 && $http_code < 300) ? 'success' : 'failed',
                'http_code' => $http_code,
                'response' => $response
            );
            
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
    }
    
    /**
     * N11 webhook testi
     */
    private function testN11Webhook() {
        try {
            $test_data = array(
                'eventType' => 'ORDER_UPDATE',
                'timestamp' => time(),
                'data' => array(
                    'orderId' => 'TEST_' . uniqid(),
                    'status' => 'Processing'
                )
            );
            
            $webhook_url = HTTPS_CATALOG . 'index.php?route=extension/module/n11_webhook/receive';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $webhook_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return array(
                'status' => ($http_code >= 200 && $http_code < 300) ? 'success' : 'failed',
                'http_code' => $http_code,
                'response' => $response
            );
            
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
    }
    
    /**
     * Ozon webhook testi
     */
    private function testOzonWebhook() {
        try {
            $test_data = array(
                'eventType' => 'STOCK_UPDATE',
                'timestamp' => time(),
                'data' => array(
                    'productId' => 'TEST_' . uniqid(),
                    'stock' => 10
                )
            );
            
            $webhook_url = HTTPS_CATALOG . 'index.php?route=extension/module/ozon_webhook/receive';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $webhook_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return array(
                'status' => ($http_code >= 200 && $http_code < 300) ? 'success' : 'failed',
                'http_code' => $http_code,
                'response' => $response
            );
            
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
    }
}

?> 