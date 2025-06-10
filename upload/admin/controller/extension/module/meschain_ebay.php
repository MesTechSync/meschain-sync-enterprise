<?php
/**
 * MesChain eBay Controller
 * @author MesChain-Sync Team
 * @version 1.0.0
 */

require_once(DIR_SYSTEM . 'library/meschain/api/ebay_api.php');

class ControllerExtensionModuleMeschainEbay extends Controller {
    
    public function index() {
        $this->document->setTitle('MesChain eBay Yönetimi');
        
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => 'Ana Sayfa',
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => 'eBay Yönetimi',
            'href' => $this->url->link('extension/module/meschain_ebay', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // eBay API bağlantısını test et
        $api_status = $this->testEbayConnection();
        $data['api_status'] = $api_status;
        
        // İstatistikler
        $data['stats'] = $this->getEbayStats();
        
        // Son aktiviteler
        $data['recent_activities'] = $this->getRecentActivities();
        
        $data['user_token'] = $this->session->data['user_token'];
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_ebay', $data));
    }
    
    /**
     * eBay API bağlantı testi
     */
    public function testConnection() {
        $json = array();
        
        try {
            $config = $this->getEbayConfig();
            $ebay_api = new EbayAPI($config);
            
            $result = $ebay_api->testConnection();
            
            if ($result['success']) {
                $json['success'] = 'eBay API bağlantısı başarılı!';
                $json['timestamp'] = $result['timestamp'];
            } else {
                $json['error'] = 'eBay API bağlantı hatası: ' . $result['error'];
            }
            
        } catch (Exception $e) {
            $json['error'] = 'Hata: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Ürün listeleme
     */
    public function listProduct() {
        $json = array();
        
        try {
            if (!isset($this->request->post['product_id'])) {
                throw new Exception('Ürün ID gerekli');
            }
            
            $product_id = (int)$this->request->post['product_id'];
            
            // Ürün bilgilerini al
            $this->load->model('catalog/product');
            $product = $this->model_catalog_product->getProduct($product_id);
            
            if (!$product) {
                throw new Exception('Ürün bulunamadı');
            }
            
            // eBay'e listele
            $config = $this->getEbayConfig();
            $ebay_api = new EbayAPI($config);
            
            $item_data = array(
                'title' => $product['name'],
                'description' => strip_tags($product['description']),
                'category_id' => 9355, // Default kategori
                'price' => $product['price'],
                'quantity' => $product['quantity'] > 0 ? $product['quantity'] : 1
            );
            
            $result = $ebay_api->addFixedPriceItem($item_data);
            
            if ($result['success']) {
                // Veritabanına kaydet
                $this->db->query("
                    INSERT INTO `" . DB_PREFIX . "meschain_products` 
                    SET product_id = '" . (int)$product_id . "',
                        marketplace = 'ebay',
                        marketplace_product_id = '" . $this->db->escape($result['item_id']) . "',
                        title = '" . $this->db->escape($product['name']) . "',
                        price = '" . (float)$product['price'] . "',
                        quantity = '" . (int)$product['quantity'] . "',
                        status = 'active',
                        date_added = NOW()
                ");
                
                $json['success'] = 'Ürün eBay\'e başarıyla listelendi!';
                $json['item_id'] = $result['item_id'];
            } else {
                $json['error'] = 'eBay listeleme hatası: ' . $result['error'];
            }
            
        } catch (Exception $e) {
            $json['error'] = 'Hata: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Siparişleri senkronize et
     */
    public function syncOrders() {
        $json = array();
        
        try {
            $config = $this->getEbayConfig();
            $ebay_api = new EbayAPI($config);
            
            // Son 7 günün siparişlerini al
            $date_from = date('Y-m-d H:i:s', strtotime('-7 days'));
            $result = $ebay_api->getOrders($date_from);
            
            if ($result['success']) {
                $order_count = 0;
                
                foreach ($result['orders'] as $order) {
                    if ($this->processEbayOrder($order)) {
                        $order_count++;
                    }
                }
                
                $json['success'] = $order_count . ' sipariş senkronize edildi';
            } else {
                $json['error'] = 'Sipariş alma hatası: ' . $result['error'];
            }
            
        } catch (Exception $e) {
            $json['error'] = 'Hata: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * eBay yapılandırması
     */
    private function getEbayConfig() {
        return array(
            'app_id' => $this->config->get('meschain_ebay_app_id'),
            'dev_id' => $this->config->get('meschain_ebay_dev_id'),
            'cert_id' => $this->config->get('meschain_ebay_cert_id'),
            'user_token' => $this->config->get('meschain_ebay_user_token'),
            'sandbox' => $this->config->get('meschain_ebay_sandbox')
        );
    }
    
    /**
     * eBay API bağlantı durumu
     */
    private function testEbayConnection() {
        try {
            $config = $this->getEbayConfig();
            
            if (empty($config['app_id']) || empty($config['user_token'])) {
                return array(
                    'status' => 'error',
                    'message' => 'eBay API ayarları yapılandırılmamış'
                );
            }
            
            $ebay_api = new EbayAPI($config);
            $result = $ebay_api->testConnection();
            
            if ($result['success']) {
                return array(
                    'status' => 'success',
                    'message' => 'eBay API bağlantısı başarılı',
                    'timestamp' => $result['timestamp']
                );
            } else {
                return array(
                    'status' => 'error',
                    'message' => 'eBay API bağlantı hatası: ' . $result['error']
                );
            }
            
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => 'Bağlantı hatası: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * eBay istatistikleri
     */
    private function getEbayStats() {
        // Aktif ürün sayısı
        $active_products = $this->db->query("
            SELECT COUNT(*) as count 
            FROM `" . DB_PREFIX . "meschain_products` 
            WHERE marketplace = 'ebay' AND status = 'active'
        ");
        
        // Bugünkü siparişler
        $today_orders = $this->db->query("
            SELECT COUNT(*) as count 
            FROM `" . DB_PREFIX . "meschain_orders` 
            WHERE marketplace = 'ebay' AND DATE(date_added) = DATE(NOW())
        ");
        
        // Bu ayki gelir
        $month_revenue = $this->db->query("
            SELECT SUM(total_amount) as total 
            FROM `" . DB_PREFIX . "meschain_orders` 
            WHERE marketplace = 'ebay' AND YEAR(date_added) = YEAR(NOW()) AND MONTH(date_added) = MONTH(NOW())
        ");
        
        // Toplam sipariş
        $total_orders = $this->db->query("
            SELECT COUNT(*) as count 
            FROM `" . DB_PREFIX . "meschain_orders` 
            WHERE marketplace = 'ebay'
        ");
        
        return array(
            'active_products' => (int)$active_products->row['count'],
            'today_orders' => (int)$today_orders->row['count'],
            'month_revenue' => (float)$month_revenue->row['total'],
            'total_orders' => (int)$total_orders->row['count']
        );
    }
    
    /**
     * Son aktiviteler
     */
    private function getRecentActivities() {
        $activities = $this->db->query("
            SELECT 'order' as type, order_id as id, 'Yeni sipariş alındı' as message, date_added
            FROM `" . DB_PREFIX . "meschain_orders`
            WHERE marketplace = 'ebay' AND date_added >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            
            UNION ALL
            
            SELECT 'product' as type, product_id as id, 'Ürün listelendi' as message, date_added
            FROM `" . DB_PREFIX . "meschain_products`
            WHERE marketplace = 'ebay' AND date_added >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            
            ORDER BY date_added DESC
            LIMIT 10
        ");
        
        return $activities->rows;
    }
    
    /**
     * eBay siparişini işle
     */
    private function processEbayOrder($order) {
        try {
            // Sipariş zaten var mı kontrol et
            $existing = $this->db->query("
                SELECT order_id 
                FROM `" . DB_PREFIX . "meschain_orders` 
                WHERE marketplace = 'ebay' AND marketplace_order_id = '" . $this->db->escape($order['OrderID']) . "'
            ");
            
            if ($existing->num_rows > 0) {
                return false; // Zaten mevcut
            }
            
            // Yeni sipariş ekle
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "meschain_orders`
                SET marketplace = 'ebay',
                    marketplace_order_id = '" . $this->db->escape($order['OrderID']) . "',
                    customer_name = '" . $this->db->escape($order['ShippingAddress']['Name']) . "',
                    customer_email = '" . $this->db->escape($order['TransactionArray']['Transaction'][0]['Buyer']['Email']) . "',
                    total_amount = '" . (float)$order['Total']['_'] . "',
                    currency = '" . $this->db->escape($order['Total']['currencyID']) . "',
                    order_status = 'pending',
                    order_data = '" . $this->db->escape(json_encode($order)) . "',
                    date_added = '" . date('Y-m-d H:i:s', strtotime($order['CreatedTime'])) . "'
            ");
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
}

?> 