<?php
/**
 * MesChain Analytics Dashboard Controller
 * Business Intelligence ve analitik raporlama sistemi
 * @author MesChain-Sync Team
 * @version 1.0.0
 */

class ControllerExtensionModuleMeschainAnalytics extends Controller {
    
    public function index() {
        $this->load->language('extension/module/meschain_analytics');
        
        $this->document->setTitle('MesChain Analytics Dashboard');
        
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(  
            'text' => 'Ana Sayfa',
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Analytics Dashboard',
            'href' => $this->url->link('extension/module/meschain_analytics', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $period = $this->request->get['period'] ?? 'monthly';
        $data['selected_period'] = $period;
        
        $analytics_data = $this->getAnalyticsData($period);
        $data = array_merge($data, $analytics_data);
        
        $data['user_token'] = $this->session->data['user_token'];
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_analytics', $data));
    }
    
    /**
     * AJAX - Analytics verisi getir
     */
    public function getAnalyticsData($period = null) {
        if (!$period) {
            $period = $this->request->post['period'] ?? 'monthly';
        }
        
        try {
            // Analytics engine'i yükle
            require_once(DIR_SYSTEM . 'library/meschain/logger/logger.php');
            require_once(DIR_SYSTEM . 'library/meschain/analytics/analytics_engine.php');
            
            $logger = new MesChainLogger('analytics');
            
            // Cache sistemi (basit dosya cache)
            $cache = new Cache('file');
            
            $analytics = new MesChainAnalyticsEngine($this->db, $logger, $this->config, $cache);
            $data = $analytics->getDashboardData($period);
            
            if ($this->request->server['REQUEST_METHOD'] === 'POST') {
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($data));
                return;
            }
            
            return $data;
            
        } catch (Exception $e) {
            $error_data = array('error' => 'Analytics verisi alınamadı: ' . $e->getMessage());
            
            if ($this->request->server['REQUEST_METHOD'] === 'POST') {
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($error_data));
                return;
            }
            
            return $error_data;
        }
    }
    
    /**
     * Marketplace detay analizi
     */
    public function marketplaceDetail() {
        $json = array();
        
        try {
            $marketplace = $this->request->post['marketplace'] ?? '';
            $period = $this->request->post['period'] ?? 'monthly';
            
            if (empty($marketplace)) {
                $json['error'] = 'Marketplace seçimi gerekli';
            } else {
                $json = $this->getMarketplaceDetailData($marketplace, $period);
            }
            
        } catch (Exception $e) {
            $json['error'] = 'Marketplace detayı alınamadı: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Ürün performans analizi
     */
    public function productPerformance() {
        $json = array();
        
        try {
            $product_id = (int)($this->request->post['product_id'] ?? 0);
            $period = $this->request->post['period'] ?? 'monthly';
            
            if ($product_id > 0) {
                $json = $this->getProductPerformanceData($product_id, $period);
            } else {
                $json['error'] = 'Geçersiz ürün ID';
            }
            
        } catch (Exception $e) {
            $json['error'] = 'Ürün performansı alınamadı: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Excel raporu oluştur
     */
    public function exportExcel() {
        try {
            $period = $this->request->get['period'] ?? 'monthly';
            
            // Analytics verisi al
            $analytics_data = $this->getAnalyticsData($period);
            
            // Excel dosyası oluştur
            $filename = $this->generateExcelReport($analytics_data, $period);
            
            if ($filename) {
                // Dosyayı indir
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="meschain_analytics_' . $period . '_' . date('Y-m-d') . '.xlsx"');
                header('Content-Length: ' . filesize($filename));
                readfile($filename);
                
                // Geçici dosyayı sil
                unlink($filename);
                exit;
            } else {
                echo 'Excel raporu oluşturulamadı';
            }
            
        } catch (Exception $e) {
            echo 'Hata: ' . $e->getMessage();
        }
    }
    
    /**
     * Cache temizle
     */
    public function clearCache() {
        $json = array();
        
        try {
            require_once(DIR_SYSTEM . 'library/meschain/logger/logger.php');
            require_once(DIR_SYSTEM . 'library/meschain/analytics/analytics_engine.php');
            
            $logger = new MesChainLogger('analytics');
            $cache = new Cache('file');
            
            $analytics = new MesChainAnalyticsEngine($this->db, $logger, $this->config, $cache);
            $analytics->clearCache();
            
            $json['success'] = 'Analytics cache temizlendi';
            
        } catch (Exception $e) {
            $json['error'] = 'Cache temizleme hatası: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Marketplace detay verisi
     */
    private function getMarketplaceDetailData($marketplace, $period) {
        $date_condition = $this->getDateCondition($period);
        
        // Marketplace sipariş detayları
        $orders = $this->db->query("
            SELECT DATE(date_added) as order_date,
                   COUNT(*) as order_count,
                   SUM(total_amount) as revenue,
                   AVG(total_amount) as avg_order_value
            FROM `" . DB_PREFIX . "meschain_orders`
            WHERE marketplace = '" . $this->db->escape($marketplace) . "'
            AND {$date_condition}
            GROUP BY DATE(date_added)
            ORDER BY order_date ASC
        ");
        
        // En çok satan ürünler
        $top_products = $this->db->query("
            SELECT p.name,
                   COUNT(o.id) as order_count,
                   SUM(o.total_amount) as revenue
            FROM `" . DB_PREFIX . "meschain_orders` o
            LEFT JOIN `" . DB_PREFIX . "meschain_products` mp ON o.product_id = mp.marketplace_product_id
            LEFT JOIN `" . DB_PREFIX . "product_description` p ON mp.opencart_product_id = p.product_id
            WHERE o.marketplace = '" . $this->db->escape($marketplace) . "'
            AND {$date_condition} AND p.language_id = 2
            GROUP BY p.product_id
            ORDER BY revenue DESC
            LIMIT 10
        ");
        
        return array(
            'marketplace' => $marketplace,
            'period' => $period,
            'orders_chart' => $orders->rows,
            'top_products' => $top_products->rows
        );
    }
    
    /**
     * Ürün performans verisi
     */
    private function getProductPerformanceData($product_id, $period) {
        $date_condition = $this->getDateCondition($period);
        
        // Ürün bilgisi
        $product_info = $this->db->query("
            SELECT p.name, p.model
            FROM `" . DB_PREFIX . "product_description` p
            WHERE p.product_id = " . (int)$product_id . " AND p.language_id = 2
        ");
        
        // Marketplace performansı
        $marketplace_performance = $this->db->query("
            SELECT o.marketplace,
                   COUNT(o.id) as order_count,
                   SUM(o.total_amount) as revenue,
                   AVG(o.total_amount) as avg_price
            FROM `" . DB_PREFIX . "meschain_orders` o
            LEFT JOIN `" . DB_PREFIX . "meschain_products` mp ON o.product_id = mp.marketplace_product_id
            WHERE mp.opencart_product_id = " . (int)$product_id . "
            AND {$date_condition}
            GROUP BY o.marketplace
            ORDER BY revenue DESC
        ");
        
        return array(
            'product_info' => $product_info->row,
            'marketplace_performance' => $marketplace_performance->rows
        );
    }
    
    /**
     * Excel raporu oluştur
     */
    private function generateExcelReport($data, $period) {
        // Basit CSV formatında rapor oluştur (Excel yerine)
        $filename = DIR_DOWNLOAD . 'meschain_analytics_' . $period . '_' . date('Y-m-d') . '.csv';
        
        $file = fopen($filename, 'w');
        
        // Header
        fputcsv($file, array('MesChain Analytics Raporu - ' . ucfirst($period)));
        fputcsv($file, array('Oluşturulma Tarihi: ' . date('d.m.Y H:i')));
        fputcsv($file, array(''));
        
        // Özet bilgiler
        if (isset($data['summary'])) {
            fputcsv($file, array('ÖZET BİLGİLER'));
            fputcsv($file, array('Toplam Sipariş', $data['summary']['total_orders']));
            fputcsv($file, array('Toplam Gelir', number_format($data['summary']['total_revenue'], 2)));
            fputcsv($file, array('Toplam Ürün', $data['summary']['total_products']));
            fputcsv($file, array(''));
        }
        
        // Marketplace karşılaştırması
        if (isset($data['marketplace_comparison'])) {
            fputcsv($file, array('MARKETPLACE KARŞILAŞTIRMASI'));
            fputcsv($file, array('Marketplace', 'Sipariş Sayısı', 'Gelir', 'Ortalama Sipariş Değeri'));
            
            foreach ($data['marketplace_comparison'] as $marketplace) {
                fputcsv($file, array(
                    $marketplace['marketplace'],
                    $marketplace['order_count'],
                    number_format($marketplace['revenue'], 2),
                    number_format($marketplace['avg_order_value'], 2)
                ));
            }
            fputcsv($file, array(''));
        }
        
        fclose($file);
        
        return $filename;
    }
    
    /**
     * Tarih koşulu oluştur
     */
    private function getDateCondition($period) {
        switch ($period) {
            case 'daily':
                return "date_added >= DATE(NOW())";
            case 'weekly':
                return "date_added >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'monthly':
                return "date_added >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case 'yearly':
                return "date_added >= DATE_SUB(NOW(), INTERVAL 365 DAY)";
            default:
                return "date_added >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        }
    }
}

?> 