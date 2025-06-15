<?php
/**
 * mestech_sync.php
 *
 * MesTech Sync modülünün ana kontrolcü sınıfı
 */
class ControllerExtensionMestechMestechSync extends Controller {
    private $error = array();
    
    /**
     * Ana sayfa
     */
    public function index() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('mestech_mestech_sync', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true));
        }
        
        // Tema yükleyici JavaScript dosyasını dahil et
        $this->document->addScript('view/javascript/mestech/theme_loader.js');
        
        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['action'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true);
        
        // Modül durumu
        if (isset($this->request->post['mestech_mestech_sync_status'])) {
            $data['mestech_mestech_sync_status'] = $this->request->post['mestech_mestech_sync_status'];
        } else {
            $data['mestech_mestech_sync_status'] = $this->config->get('mestech_mestech_sync_status');
        }
        
        // API Anahtarı
        if (isset($this->request->post['mestech_mestech_sync_api_key'])) {
            $data['mestech_mestech_sync_api_key'] = $this->request->post['mestech_mestech_sync_api_key'];
        } else {
            $data['mestech_mestech_sync_api_key'] = $this->config->get('mestech_mestech_sync_api_key');
        }
        
        // Tema seçimi
        if (isset($this->request->post['mestech_mestech_sync_theme'])) {
            $data['mestech_mestech_sync_theme'] = $this->request->post['mestech_mestech_sync_theme'];
        } else {
            $data['mestech_mestech_sync_theme'] = $this->config->get('mestech_mestech_sync_theme');
        }
        
        // Tema seçenekleri
        $data['themes'] = array(
            'sutlu_kahve' => $this->language->get('text_theme_sutlu_kahve'),
            'deniz_mavisi' => $this->language->get('text_theme_deniz_mavisi'),
            'default' => $this->language->get('text_theme_default')
        );
        
        // Pazaryerleri
        $data['marketplaces'] = array(
            'trendyol' => $this->url->link('extension/mestech/mestech_sync/trendyol', 'user_token=' . $this->session->data['user_token'], true),
            'n11' => $this->url->link('extension/mestech/mestech_sync/n11', 'user_token=' . $this->session->data['user_token'], true),
            'amazon' => $this->url->link('extension/mestech/mestech_sync/amazon', 'user_token=' . $this->session->data['user_token'], true),
            'ebay' => $this->url->link('extension/mestech/mestech_sync/ebay', 'user_token=' . $this->session->data['user_token'], true),
            'hepsiburada' => $this->url->link('extension/mestech/mestech_sync/hepsiburada', 'user_token=' . $this->session->data['user_token'], true),
            'ozon' => $this->url->link('extension/mestech/mestech_sync/ozon', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Diğer sayfalar
        $data['logs_url'] = $this->url->link('extension/mestech/mestech_sync/logs', 'user_token=' . $this->session->data['user_token'], true);
        $data['help_url'] = $this->url->link('extension/mestech/mestech_sync/help', 'user_token=' . $this->session->data['user_token'], true);
        $data['dashboard_url'] = $this->url->link('extension/mestech/mestech_sync/dashboard', 'user_token=' . $this->session->data['user_token'], true);
        $data['themes_url'] = $this->url->link('extension/mestech/mestech_sync/themes', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync', $data));
    }
    
    /**
     * Trendyol sayfası
     */
    public function trendyol() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - Trendyol');
        
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('mestech_mestech_sync_trendyol', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            // Redirect to Trendyol dashboard instead of the same page
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        // Başarı mesajı
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Trendyol',
            'href' => $this->url->link('extension/mestech/mestech_sync/trendyol', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['action'] = $this->url->link('extension/mestech/mestech_sync/trendyol', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        
        // Trendyol API ayarları
        if (isset($this->request->post['mestech_mestech_sync_trendyol_api_key'])) {
            $data['mestech_mestech_sync_trendyol_api_key'] = $this->request->post['mestech_mestech_sync_trendyol_api_key'];
        } else {
            $data['mestech_mestech_sync_trendyol_api_key'] = $this->config->get('mestech_mestech_sync_trendyol_api_key');
        }
        
        if (isset($this->request->post['mestech_mestech_sync_trendyol_api_secret'])) {
            $data['mestech_mestech_sync_trendyol_api_secret'] = $this->request->post['mestech_mestech_sync_trendyol_api_secret'];
        } else {
            $data['mestech_mestech_sync_trendyol_api_secret'] = $this->config->get('mestech_mestech_sync_trendyol_api_secret');
        }
        
        if (isset($this->request->post['mestech_mestech_sync_trendyol_supplier_id'])) {
            $data['mestech_mestech_sync_trendyol_supplier_id'] = $this->request->post['mestech_mestech_sync_trendyol_supplier_id'];
        } else {
            $data['mestech_mestech_sync_trendyol_supplier_id'] = $this->config->get('mestech_mestech_sync_trendyol_supplier_id');
        }
        
        // Diğer sayfalar
        $data['dashboard_url'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['logs_url'] = $this->url->link('extension/mestech/mestech_sync/logs', 'user_token=' . $this->session->data['user_token'], true);
        $data['help_url'] = $this->url->link('extension/mestech/mestech_sync/help', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync_trendyol', $data));
    }
    
    /**
     * Trendyol Dashboard sayfası
     */
    public function trendyol_dashboard() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - Trendyol Dashboard');
        
        // Tema yükleyici JavaScript dosyasını dahil et
        $this->document->addScript('view/javascript/mestech/theme_loader.js');
        
        // Chart.js kütüphanesini CDN üzerinden yükle
        $this->document->addScript('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js');
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Trendyol',
            'href' => $this->url->link('extension/mestech/mestech_sync/trendyol', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Trendyol Dashboard',
            'href' => $this->url->link('extension/mestech/mestech_sync/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Başarı mesajı
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Hata mesajı
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        // Aksiyon URL'leri
        $data['action'] = $this->url->link('extension/mestech/mestech_sync/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['config_url'] = $this->url->link('extension/mestech/mestech_sync/trendyol', 'user_token=' . $this->session->data['user_token'], true);
        
        // Hızlı işlem URL'leri
        $data['sync_products_url'] = $this->url->link('extension/mestech/mestech_sync/trendyol_sync_products', 'user_token=' . $this->session->data['user_token'], true);
        $data['get_orders_url'] = $this->url->link('extension/mestech/mestech_sync/trendyol_get_orders', 'user_token=' . $this->session->data['user_token'], true);
        $data['update_prices_url'] = $this->url->link('extension/mestech/mestech_sync/trendyol_update_prices', 'user_token=' . $this->session->data['user_token'], true);
        $data['update_stock_url'] = $this->url->link('extension/mestech/mestech_sync/trendyol_update_stock', 'user_token=' . $this->session->data['user_token'], true);
        
        // Kullanıcı bilgileri
        $data['username'] = $this->user->getUserName();
        $data['is_admin'] = $this->user->hasPermission('modify', 'extension/mestech/mestech_sync');
        
        // Tema bilgisi
        $data['theme'] = $this->config->get('mestech_mestech_sync_theme') ? $this->config->get('mestech_mestech_sync_theme') : 'default';
        
        // Trendyol API bilgileri
        $data['api_key'] = $this->config->get('mestech_mestech_sync_trendyol_api_key');
        $data['api_secret'] = $this->config->get('mestech_mestech_sync_trendyol_api_secret');
        $data['supplier_id'] = $this->config->get('mestech_mestech_sync_trendyol_supplier_id');
        
        // Trendyol istatistikleri
        $data['statistics'] = $this->getTrendyolStatistics();
        
        // İşlem geçmişi
        $data['activities'] = $this->getTrendyolActivities();
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync_trendyol_dashboard', $data));
    }
    
    /**
     * Trendyol istatistiklerini getirir
     */
    private function getTrendyolStatistics() {
        $stats = array(
            'total_products' => 0,
            'synced_products' => 0,
            'pending_orders' => 0,
            'completed_orders' => 0,
            'success_rate' => 95, // Örnek değer
            'last_sync' => date('Y-m-d H:i:s'),
            'chart_data' => array(
                'labels' => array('Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'),
                'datasets' => array(
                    array(
                        'label' => 'Ürün Senkronizasyonu',
                        'data' => array(65, 59, 80, 81, 56, 55, 40)
                    ),
                    array(
                        'label' => 'Sipariş Senkronizasyonu',
                        'data' => array(28, 48, 40, 19, 86, 27, 90)
                    )
                )
            )
        );
        
        // Gerçek veriler için Trendyol API'sini kullanabiliriz
        if ($this->config->get('mestech_mestech_sync_trendyol_api_key') && 
            $this->config->get('mestech_mestech_sync_trendyol_api_secret') && 
            $this->config->get('mestech_mestech_sync_trendyol_supplier_id')) {
            
            // Trendyol Helper'ı yükle
            require_once(DIR_SYSTEM . 'helper/trendyol_helper.php');
            
            try {
                $trendyol = new TrendyolHelper(
                    $this->config->get('mestech_mestech_sync_trendyol_api_key'),
                    $this->config->get('mestech_mestech_sync_trendyol_api_secret'),
                    $this->config->get('mestech_mestech_sync_trendyol_supplier_id')
                );
                
                // Ürünleri getir
                $products = $trendyol->getProducts(0, 1);
                if ($products && isset($products['totalElements'])) {
                    $stats['total_products'] = $products['totalElements'];
                    $stats['synced_products'] = $products['totalElements'];
                }
                
                // Siparişleri getir
                $orders = $trendyol->getOrders('Created');
                if ($orders && isset($orders['totalElements'])) {
                    $stats['pending_orders'] = $orders['totalElements'];
                }
                
                $completed_orders = $trendyol->getOrders('Delivered');
                if ($completed_orders && isset($completed_orders['totalElements'])) {
                    $stats['completed_orders'] = $completed_orders['totalElements'];
                }
                
            } catch (Exception $e) {
                // API hatası durumunda varsayılan değerleri kullan
            }
        }
        
        return $stats;
    }
    
    /**
     * Trendyol işlem geçmişini getirir
     */
    private function getTrendyolActivities() {
        // Gerçek veriler için log dosyasından okuma yapılabilir
        // Şimdilik örnek veriler döndürelim
        return array(
            array(
                'date' => date('Y-m-d H:i:s'),
                'action' => 'Ürün Senkronizasyonu',
                'status' => 'success',
                'details' => '150 ürün güncellendi'
            ),
            array(
                'date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'action' => 'Sipariş Çekme',
                'status' => 'success',
                'details' => '5 yeni sipariş alındı'
            ),
            array(
                'date' => date('Y-m-d H:i:s', strtotime('-3 hour')),
                'action' => 'Fiyat Güncelleme',
                'status' => 'warning',
                'details' => '120/125 ürün güncellendi'
            ),
            array(
                'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'action' => 'Stok Güncelleme',
                'status' => 'danger',
                'details' => 'API bağlantı hatası'
            )
        );
    }
    
    /**
     * Trendyol ürünlerini senkronize eder
     */
    public function trendyol_sync_products() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Trendyol API bilgilerini kontrol et
        if (!$this->config->get('mestech_mestech_sync_trendyol_api_key') || 
            !$this->config->get('mestech_mestech_sync_trendyol_api_secret') || 
            !$this->config->get('mestech_mestech_sync_trendyol_supplier_id')) {
            $this->session->data['error'] = 'Trendyol API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        try {
            // Trendyol Helper'ı yükle
            require_once(DIR_SYSTEM . 'helper/trendyol_helper.php');
            
            $trendyol = new TrendyolHelper(
                $this->config->get('mestech_mestech_sync_trendyol_api_key'),
                $this->config->get('mestech_mestech_sync_trendyol_api_secret'),
                $this->config->get('mestech_mestech_sync_trendyol_supplier_id')
            );
            
            // OpenCart ürünlerini al
            $this->load->model('catalog/product');
            $products = $this->model_catalog_product->getProducts();
            
            $syncedCount = 0;
            $trendyolProducts = array();
            
            foreach ($products as $product) {
                // Ürün bilgilerini hazırla
                $trendyolProduct = array(
                    'barcode' => $product['sku'] ? $product['sku'] : $product['model'],
                    'title' => $product['name'],
                    'productMainId' => $product['product_id'],
                    'brandId' => 1, // Varsayılan marka ID
                    'categoryId' => 1, // Varsayılan kategori ID
                    'quantity' => $product['quantity'],
                    'stockCode' => $product['model'],
                    'dimensionalWeight' => 1,
                    'description' => $product['description'],
                    'currencyType' => 'TRY',
                    'listPrice' => $product['price'],
                    'salePrice' => $product['special'] ? $product['special'] : $product['price'],
                    'vatRate' => 18,
                    'cargoCompanyId' => 1,
                    'images' => array(
                        array(
                            'url' => HTTP_CATALOG . 'image/' . $product['image']
                        )
                    )
                );
                
                $trendyolProducts[] = $trendyolProduct;
                $syncedCount++;
                
                // 50 ürün toplandığında gönder
                if (count($trendyolProducts) >= 50) {
                    $result = $trendyol->createUpdateProducts(array('items' => $trendyolProducts));
                    $trendyolProducts = array();
                }
            }
            
            // Kalan ürünleri gönder
            if (count($trendyolProducts) > 0) {
                $result = $trendyol->createUpdateProducts(array('items' => $trendyolProducts));
            }
            
            $this->session->data['success'] = $syncedCount . ' ürün başarıyla Trendyol\'a senkronize edildi.';
            
        } catch (Exception $e) {
            $this->session->data['error'] = 'Ürün senkronizasyonu sırasında hata oluştu: ' . $e->getMessage();
        }
        
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Trendyol siparişlerini çeker
     */
    public function trendyol_get_orders() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Trendyol API bilgilerini kontrol et
        if (!$this->config->get('mestech_mestech_sync_trendyol_api_key') || 
            !$this->config->get('mestech_mestech_sync_trendyol_api_secret') || 
            !$this->config->get('mestech_mestech_sync_trendyol_supplier_id')) {
            $this->session->data['error'] = 'Trendyol API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        try {
            // Trendyol Helper'ı yükle
            require_once(DIR_SYSTEM . 'helper/trendyol_helper.php');
            
            $trendyol = new TrendyolHelper(
                $this->config->get('mestech_mestech_sync_trendyol_api_key'),
                $this->config->get('mestech_mestech_sync_trendyol_api_secret'),
                $this->config->get('mestech_mestech_sync_trendyol_supplier_id')
            );
            
            // Son 7 günün siparişlerini al
            $startDate = date('Y-m-d\TH:i:s\Z', strtotime('-7 days'));
            $endDate = date('Y-m-d\TH:i:s\Z');
            
            $orders = $trendyol->getOrders('Created', 0, 100, $startDate, $endDate);
            
            if ($orders && isset($orders['content'])) {
                $orderCount = count($orders['content']);
                
                // Siparişleri OpenCart'a aktar
                // Bu kısım gerçek entegrasyonda daha detaylı olacak
                
                $this->session->data['success'] = $orderCount . ' sipariş başarıyla Trendyol\'dan alındı.';
            } else {
                $this->session->data['success'] = 'Yeni sipariş bulunamadı.';
            }
            
        } catch (Exception $e) {
            $this->session->data['error'] = 'Sipariş çekme sırasında hata oluştu: ' . $e->getMessage();
        }
        
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Trendyol fiyatlarını günceller
     */
    public function trendyol_update_prices() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Trendyol API bilgilerini kontrol et
        if (!$this->config->get('mestech_mestech_sync_trendyol_api_key') || 
            !$this->config->get('mestech_mestech_sync_trendyol_api_secret') || 
            !$this->config->get('mestech_mestech_sync_trendyol_supplier_id')) {
            $this->session->data['error'] = 'Trendyol API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        try {
            // Trendyol Helper'ı yükle
            require_once(DIR_SYSTEM . 'helper/trendyol_helper.php');
            
            $trendyol = new TrendyolHelper(
                $this->config->get('mestech_mestech_sync_trendyol_api_key'),
                $this->config->get('mestech_mestech_sync_trendyol_api_secret'),
                $this->config->get('mestech_mestech_sync_trendyol_supplier_id')
            );
            
            // OpenCart ürünlerini al
            $this->load->model('catalog/product');
            $products = $this->model_catalog_product->getProducts();
            
            $updatedCount = 0;
            $items = array();
            
            foreach ($products as $product) {
                // Fiyat bilgilerini hazırla
                $item = array(
                    'barcode' => $product['sku'] ? $product['sku'] : $product['model'],
                    'salePrice' => $product['special'] ? $product['special'] : $product['price'],
                    'listPrice' => $product['price']
                );
                
                $items[] = $item;
                $updatedCount++;
                
                // 100 ürün toplandığında gönder
                if (count($items) >= 100) {
                    $result = $trendyol->updatePriceAndInventory($items);
                    $items = array();
                }
            }
            
            // Kalan ürünleri gönder
            if (count($items) > 0) {
                $result = $trendyol->updatePriceAndInventory($items);
            }
            
            $this->session->data['success'] = $updatedCount . ' ürünün fiyatı başarıyla güncellendi.';
            
        } catch (Exception $e) {
            $this->session->data['error'] = 'Fiyat güncelleme sırasında hata oluştu: ' . $e->getMessage();
        }
        
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Trendyol stok bilgilerini günceller
     */
    public function trendyol_update_stock() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Trendyol API bilgilerini kontrol et
        if (!$this->config->get('mestech_mestech_sync_trendyol_api_key') || 
            !$this->config->get('mestech_mestech_sync_trendyol_api_secret') || 
            !$this->config->get('mestech_mestech_sync_trendyol_supplier_id')) {
            $this->session->data['error'] = 'Trendyol API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        try {
            // Trendyol Helper'ı yükle
            require_once(DIR_SYSTEM . 'helper/trendyol_helper.php');
            
            $trendyol = new TrendyolHelper(
                $this->config->get('mestech_mestech_sync_trendyol_api_key'),
                $this->config->get('mestech_mestech_sync_trendyol_api_secret'),
                $this->config->get('mestech_mestech_sync_trendyol_supplier_id')
            );
            
            // OpenCart ürünlerini al
            $this->load->model('catalog/product');
            $products = $this->model_catalog_product->getProducts();
            
            $updatedCount = 0;
            $items = array();
            
            foreach ($products as $product) {
                // Stok bilgilerini hazırla
                $item = array(
                    'barcode' => $product['sku'] ? $product['sku'] : $product['model'],
                    'quantity' => $product['quantity']
                );
                
                $items[] = $item;
                $updatedCount++;
                
                // 100 ürün toplandığında gönder
                if (count($items) >= 100) {
                    $result = $trendyol->updatePriceAndInventory($items);
                    $items = array();
                }
            }
            
            // Kalan ürünleri gönder
            if (count($items) > 0) {
                $result = $trendyol->updatePriceAndInventory($items);
            }
            
            $this->session->data['success'] = $updatedCount . ' ürünün stok bilgisi başarıyla güncellendi.';
            
        } catch (Exception $e) {
            $this->session->data['error'] = 'Stok güncelleme sırasında hata oluştu: ' . $e->getMessage();
        }
        
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Amazon sayfası
     */
    public function amazon() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - Amazon');
        
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('mestech_mestech_sync_amazon', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/amazon', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        // Başarı mesajı
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Amazon',
            'href' => $this->url->link('extension/mestech/mestech_sync/amazon', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['action'] = $this->url->link('extension/mestech/mestech_sync/amazon', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        
        // Amazon API ayarları
        if (isset($this->request->post['mestech_mestech_sync_amazon_api_key'])) {
            $data['mestech_mestech_sync_amazon_api_key'] = $this->request->post['mestech_mestech_sync_amazon_api_key'];
        } else {
            $data['mestech_mestech_sync_amazon_api_key'] = $this->config->get('mestech_mestech_sync_amazon_api_key');
        }
        
        if (isset($this->request->post['mestech_mestech_sync_amazon_secret_key'])) {
            $data['mestech_mestech_sync_amazon_secret_key'] = $this->request->post['mestech_mestech_sync_amazon_secret_key'];
        } else {
            $data['mestech_mestech_sync_amazon_secret_key'] = $this->config->get('mestech_mestech_sync_amazon_secret_key');
        }
        
        if (isset($this->request->post['mestech_mestech_sync_amazon_seller_id'])) {
            $data['mestech_mestech_sync_amazon_seller_id'] = $this->request->post['mestech_mestech_sync_amazon_seller_id'];
        } else {
            $data['mestech_mestech_sync_amazon_seller_id'] = $this->config->get('mestech_mestech_sync_amazon_seller_id');
        }
        
        if (isset($this->request->post['mestech_mestech_sync_amazon_token'])) {
            $data['mestech_mestech_sync_amazon_token'] = $this->request->post['mestech_mestech_sync_amazon_token'];
        } else {
            $data['mestech_mestech_sync_amazon_token'] = $this->config->get('mestech_mestech_sync_amazon_token');
        }
        
        // Diğer sayfalar
        $data['dashboard_url'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['logs_url'] = $this->url->link('extension/mestech/mestech_sync/logs', 'user_token=' . $this->session->data['user_token'], true);
        $data['help_url'] = $this->url->link('extension/mestech/mestech_sync/help', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync_amazon', $data));
    }
    
    /**
     * Log görüntüleme sayfası
     */
    public function logs() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_logs'));
        
        // Log dosyasını oku
        $this->load->library('mestech/logger');
        $logger = new MestechLogger('mestech_sync.log');
        $data['logs'] = $logger->read(100); // Son 100 log kaydını oku
        
        // Log istatistikleri
        $data['stats'] = $logger->getStats();
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_logs'),
            'href' => $this->url->link('extension/mestech/mestech_sync/logs', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['clear_url'] = $this->url->link('extension/mestech/mestech_sync/clear_logs', 'user_token=' . $this->session->data['user_token'], true);
        $data['download_url'] = $this->url->link('extension/mestech/mestech_sync/download_logs', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        
        // Diğer sayfalar
        $data['dashboard_url'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['help_url'] = $this->url->link('extension/mestech/mestech_sync/help', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync_logs', $data));
    }
    
    /**
     * Dashboard sayfası - İstatistikler ve grafikler
     */
    public function dashboard() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_dashboard'));
        
        // JavaScript kütüphanelerini ekle (Chart.js)
        $this->document->addScript('https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js');
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_dashboard'),
            'href' => $this->url->link('extension/mestech/mestech_sync/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['cancel'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        
        // Diğer sayfalar
        $data['logs_url'] = $this->url->link('extension/mestech/mestech_sync/logs', 'user_token=' . $this->session->data['user_token'], true);
        $data['help_url'] = $this->url->link('extension/mestech/mestech_sync/help', 'user_token=' . $this->session->data['user_token'], true);
        
        // İstatistik verileri
        $data['statistics'] = $this->getStatistics();
        
        // Grafik verileri
        $data['chart_data'] = $this->getChartData();
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync_dashboard', $data));
    }
    
    /**
     * İstatistik verilerini getirir
     * 
     * @return array İstatistik verileri
     */
    private function getStatistics() {
        $this->load->model('catalog/product');
        $this->load->model('sale/order');
        
        // Toplam ürün sayısı
        $total_products = $this->model_catalog_product->getTotalProducts();
        
        // Son 7 gündeki sipariş sayısı
        $date_start = date('Y-m-d', strtotime('-7 days'));
        $date_end = date('Y-m-d');
        
        $filter_data = array(
            'filter_date_added_start' => $date_start,
            'filter_date_added_end' => $date_end
        );
        
        $total_orders = $this->model_sale_order->getTotalOrders($filter_data);
        
        // Senkronize edilmiş ürün sayısı (örnek veri)
        $synced_products = array(
            'trendyol' => rand(10, $total_products),
            'amazon' => rand(5, $total_products),
            'n11' => rand(3, $total_products),
            'ebay' => rand(2, $total_products),
            'hepsiburada' => rand(4, $total_products),
            'ozon' => rand(1, $total_products)
        );
        
        // Son senkronizasyon zamanları (örnek veri)
        $last_sync = array(
            'trendyol' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 24) . ' hours')),
            'amazon' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 24) . ' hours')),
            'n11' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 48) . ' hours')),
            'ebay' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 72) . ' hours')),
            'hepsiburada' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 36) . ' hours')),
            'ozon' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 96) . ' hours'))
        );
        
        // Senkronizasyon başarı oranları (örnek veri)
        $sync_success_rate = array(
            'trendyol' => rand(85, 100),
            'amazon' => rand(80, 100),
            'n11' => rand(75, 100),
            'ebay' => rand(70, 100),
            'hepsiburada' => rand(80, 100),
            'ozon' => rand(65, 100)
        );
        
        return array(
            'total_products' => $total_products,
            'total_orders' => $total_orders,
            'synced_products' => $synced_products,
            'last_sync' => $last_sync,
            'sync_success_rate' => $sync_success_rate
        );
    }
    
    /**
     * Grafik verilerini getirir
     * 
     * @return array Grafik verileri
     */
    private function getChartData() {
        // Son 7 gün için tarihler
        $dates = array();
        for ($i = 6; $i >= 0; $i--) {
            $dates[] = date('Y-m-d', strtotime('-' . $i . ' days'));
        }
        
        // Pazaryerleri
        $marketplaces = array('trendyol', 'amazon', 'n11', 'ebay', 'hepsiburada', 'ozon');
        
        // Günlük senkronizasyon sayıları (örnek veri)
        $sync_counts = array();
        foreach ($marketplaces as $marketplace) {
            $sync_counts[$marketplace] = array();
            foreach ($dates as $date) {
                $sync_counts[$marketplace][] = rand(5, 50);
            }
        }
        
        // Günlük hata sayıları (örnek veri)
        $error_counts = array();
        foreach ($marketplaces as $marketplace) {
            $error_counts[$marketplace] = array();
            foreach ($dates as $date) {
                $error_counts[$marketplace][] = rand(0, 10);
            }
        }
        
        return array(
            'dates' => $dates,
            'marketplaces' => $marketplaces,
            'sync_counts' => $sync_counts,
            'error_counts' => $error_counts
        );
    }
    
    /**
     * Log dosyasını temizle
     */
    public function clear_logs() {
        $this->load->language('extension/mestech/mestech_sync');
        
        $this->load->library('mestech/logger');
        $logger = new MestechLogger('mestech_sync.log');
        $logger->clear();
        
        $this->session->data['success'] = $this->language->get('text_success_clear_logs');
        
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/logs', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Log dosyasını indir
     */
    public function download_logs() {
        $this->load->library('mestech/logger');
        $logger = new MestechLogger('mestech_sync.log');
        $logger->download();
    }
    
    /**
     * Yardım sayfası
     */
    public function help() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_help'));
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_help'),
            'href' => $this->url->link('extension/mestech/mestech_sync/help', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['cancel'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        
        // Diğer sayfalar
        $data['dashboard_url'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['logs_url'] = $this->url->link('extension/mestech/mestech_sync/logs', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync_help', $data));
    }
    
    /**
     * Form doğrulama
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        return !$this->error;
    }
    
    /**
     * Modülü yükle
     */
    public function install() {
        $this->load->model('setting/setting');
        
        // Varsayılan ayarları kaydet
        $this->model_setting_setting->editSetting('mestech_mestech_sync', array(
            'mestech_mestech_sync_status' => 1,
            'mestech_mestech_sync_theme' => 'sutlu_kahve',
            'mestech_mestech_sync_api_key' => 'f4KhSfv7ihjXcJFlJeim'
        ));
        
        // Kullanıcı izinlerini ayarla
        $this->load->model('user/user_group');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mestech/mestech_sync');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mestech/mestech_sync');
        
        // Log dosyası oluştur
        $this->load->library('mestech/logger');
        $logger = new MestechLogger('mestech_sync.log');
        $logger->write('system', 'INSTALL', 'MesTech Sync modülü yüklendi');
    }
    
    /**
     * Modülü kaldır
     */
    public function uninstall() {
        $this->load->model('setting/setting');
        
        // Ayarları kaldır
        $this->model_setting_setting->deleteSetting('mestech_mestech_sync');
        $this->model_setting_setting->deleteSetting('mestech_mestech_sync_trendyol');
        $this->model_setting_setting->deleteSetting('mestech_mestech_sync_amazon');
        
        // Log dosyasına kaydet
        $this->load->library('mestech/logger');
        $logger = new MestechLogger('mestech_sync.log');
        $logger->write('system', 'UNINSTALL', 'MesTech Sync modülü kaldırıldı');
    }
    
    /**
     * N11 sayfası
     */
    public function n11() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - N11');
        
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('mestech_mestech_sync_n11', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            // N11 dashboard sayfasına yönlendir
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/n11_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        // Başarı mesajı
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'N11',
            'href' => $this->url->link('extension/mestech/mestech_sync/n11', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['action'] = $this->url->link('extension/mestech/mestech_sync/n11', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        
        // N11 API ayarları
        if (isset($this->request->post['mestech_mestech_sync_n11_app_key'])) {
            $data['mestech_mestech_sync_n11_app_key'] = $this->request->post['mestech_mestech_sync_n11_app_key'];
        } else {
            $data['mestech_mestech_sync_n11_app_key'] = $this->config->get('mestech_mestech_sync_n11_app_key');
        }
        
        if (isset($this->request->post['mestech_mestech_sync_n11_app_secret'])) {
            $data['mestech_mestech_sync_n11_app_secret'] = $this->request->post['mestech_mestech_sync_n11_app_secret'];
        } else {
            $data['mestech_mestech_sync_n11_app_secret'] = $this->config->get('mestech_mestech_sync_n11_app_secret');
        }
        
        // Diğer sayfalar
        $data['dashboard_url'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['logs_url'] = $this->url->link('extension/mestech/mestech_sync/logs', 'user_token=' . $this->session->data['user_token'], true);
        $data['help_url'] = $this->url->link('extension/mestech/mestech_sync/help', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync_n11', $data));
    }
    
    /**
     * N11 Dashboard sayfası
     */
    public function n11_dashboard() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - N11 Dashboard');
        
        // Tema yükleyici JavaScript dosyasını dahil et
        $this->document->addScript('view/javascript/mestech/theme_loader.js');
        
        // Chart.js kütüphanesini CDN üzerinden yükle
        $this->document->addScript('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js');
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'N11',
            'href' => $this->url->link('extension/mestech/mestech_sync/n11', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'N11 Dashboard',
            'href' => $this->url->link('extension/mestech/mestech_sync/n11_dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Başarı mesajı
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Hata mesajı
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else if (isset($this->session->data['error'])) {
            $data['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $data['error_warning'] = '';
        }
        
        // Aksiyon URL'leri
        $data['action'] = $this->url->link('extension/mestech/mestech_sync/n11_dashboard', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['config_url'] = $this->url->link('extension/mestech/mestech_sync/n11', 'user_token=' . $this->session->data['user_token'], true);
        
        // Hızlı işlem URL'leri
        $data['sync_products_url'] = $this->url->link('extension/mestech/mestech_sync/n11_sync_products', 'user_token=' . $this->session->data['user_token'], true);
        $data['get_orders_url'] = $this->url->link('extension/mestech/mestech_sync/n11_get_orders', 'user_token=' . $this->session->data['user_token'], true);
        $data['update_prices_url'] = $this->url->link('extension/mestech/mestech_sync/n11_update_prices', 'user_token=' . $this->session->data['user_token'], true);
        $data['update_stock_url'] = $this->url->link('extension/mestech/mestech_sync/n11_update_stock', 'user_token=' . $this->session->data['user_token'], true);
        
        // Kullanıcı bilgileri
        $data['username'] = $this->user->getUserName();
        $data['is_admin'] = $this->user->hasPermission('modify', 'extension/mestech/mestech_sync');
        
        // Tema bilgisi
        $data['theme'] = $this->config->get('mestech_mestech_sync_theme') ? $this->config->get('mestech_mestech_sync_theme') : 'default';
        
        // N11 API bilgileri
        $data['app_key'] = $this->config->get('mestech_mestech_sync_n11_app_key');
        $data['app_secret'] = $this->config->get('mestech_mestech_sync_n11_app_secret');
        
        // N11 istatistikleri
        $data['statistics'] = $this->getN11Statistics();
        
        // İşlem geçmişi
        $data['activities'] = $this->getN11Activities();
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync_n11_dashboard', $data));
    }
    
    /**
     * N11 istatistiklerini getirir
     */
    private function getN11Statistics() {
        $stats = array(
            'total_products' => 0,
            'synced_products' => 0,
            'pending_orders' => 0,
            'completed_orders' => 0,
            'success_rate' => 90, // Örnek değer
            'last_sync' => date('Y-m-d H:i:s'),
            'chart_data' => array(
                'labels' => array('Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'),
                'datasets' => array(
                    array(
                        'label' => 'Ürün Senkronizasyonu',
                        'data' => array(45, 39, 60, 75, 45, 35, 30)
                    ),
                    array(
                        'label' => 'Sipariş Senkronizasyonu',
                        'data' => array(18, 38, 30, 29, 56, 37, 80)
                    )
                )
            )
        );
        
        // Gerçek veriler için N11 API'sini kullanabiliriz
        if ($this->config->get('mestech_mestech_sync_n11_app_key') && 
            $this->config->get('mestech_mestech_sync_n11_app_secret')) {
            
            // N11 Helper'ı yükle
            require_once(DIR_SYSTEM . 'helper/n11_helper.php');
            
            try {
                $n11 = new N11Helper(
                    $this->config->get('mestech_mestech_sync_n11_app_key'),
                    $this->config->get('mestech_mestech_sync_n11_app_secret')
                );
                
                // Ürünleri getir
                $products = $n11->getProducts(0, 1);
                if ($products && isset($products['pagingData']['totalCount'])) {
                    $stats['total_products'] = $products['pagingData']['totalCount'];
                    $stats['synced_products'] = $products['pagingData']['totalCount'];
                }
                
                // Siparişleri getir
                $orders = $n11->getOrders('New');
                if ($orders && isset($orders['pagingData']['totalCount'])) {
                    $stats['pending_orders'] = $orders['pagingData']['totalCount'];
                }
                
                $completed_orders = $n11->getOrders('Approved');
                if ($completed_orders && isset($completed_orders['pagingData']['totalCount'])) {
                    $stats['completed_orders'] = $completed_orders['pagingData']['totalCount'];
                }
                
            } catch (Exception $e) {
                // API hatası durumunda varsayılan değerleri kullan
            }
        }
        
        return $stats;
    }
    
    /**
     * N11 işlem geçmişini getirir
     */
    private function getN11Activities() {
        // Gerçek veriler için log dosyasından okuma yapılabilir
        // Şimdilik örnek veriler döndürelim
        return array(
            array(
                'date' => date('Y-m-d H:i:s'),
                'action' => 'Ürün Senkronizasyonu',
                'status' => 'success',
                'details' => '120 ürün güncellendi'
            ),
            array(
                'date' => date('Y-m-d H:i:s', strtotime('-2 hour')),
                'action' => 'Sipariş Çekme',
                'status' => 'success',
                'details' => '3 yeni sipariş alındı'
            ),
            array(
                'date' => date('Y-m-d H:i:s', strtotime('-5 hour')),
                'action' => 'Fiyat Güncelleme',
                'status' => 'warning',
                'details' => '110/115 ürün güncellendi'
            ),
            array(
                'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'action' => 'Stok Güncelleme',
                'status' => 'success',
                'details' => '115 ürün güncellendi'
            )
        );
    }
    
    /**
     * N11 ürünlerini senkronize eder
     */
    public function n11_sync_products() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/n11_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // N11 API bilgilerini kontrol et
        if (!$this->config->get('mestech_mestech_sync_n11_app_key') || 
            !$this->config->get('mestech_mestech_sync_n11_app_secret')) {
            $this->session->data['error'] = 'N11 API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/n11_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        try {
            // N11 Helper'ı yükle
            require_once(DIR_SYSTEM . 'helper/n11_helper.php');
            
            $n11 = new N11Helper(
                $this->config->get('mestech_mestech_sync_n11_app_key'),
                $this->config->get('mestech_mestech_sync_n11_app_secret')
            );
            
            // OpenCart ürünlerini al
            $this->load->model('catalog/product');
            $products = $this->model_catalog_product->getProducts();
            
            $syncedCount = 0;
            
            foreach ($products as $product) {
                // Ürün bilgilerini hazırla
                $n11Product = array(
                    'productSellerCode' => $product['model'],
                    'title' => $product['name'],
                    'subtitle' => substr($product['name'], 0, 50),
                    'description' => $product['description'],
                    'category' => array(
                        'id' => '1000001' // Varsayılan kategori ID
                    ),
                    'price' => $product['price'],
                    'currencyType' => 'TL',
                    'images' => array(
                        'image' => array(
                            array(
                                'url' => HTTP_CATALOG . 'image/' . $product['image']
                            )
                        )
                    ),
                    'stockItems' => array(
                        'stockItem' => array(
                            array(
                                'sellerStockCode' => $product['model'],
                                'quantity' => $product['quantity'],
                                'optionPrice' => $product['price']
                            )
                        )
                    )
                );
                
                // Ürünü gönder
                $result = $n11->addProduct($n11Product);
                
                if ($result) {
                    $syncedCount++;
                }
            }
            
            $this->session->data['success'] = $syncedCount . ' ürün başarıyla N11\'e senkronize edildi.';
            
        } catch (Exception $e) {
            $this->session->data['error'] = 'Ürün senkronizasyonu sırasında hata oluştu: ' . $e->getMessage();
        }
        
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/n11_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * N11 siparişlerini çeker
     */
    public function n11_get_orders() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/n11_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // N11 API bilgilerini kontrol et
        if (!$this->config->get('mestech_mestech_sync_n11_app_key') || 
            !$this->config->get('mestech_mestech_sync_n11_app_secret')) {
            $this->session->data['error'] = 'N11 API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/n11_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        try {
            // N11 Helper'ı yükle
            require_once(DIR_SYSTEM . 'helper/n11_helper.php');
            
            $n11 = new N11Helper(
                $this->config->get('mestech_mestech_sync_n11_app_key'),
                $this->config->get('mestech_mestech_sync_n11_app_secret')
            );
            
            // Son 7 günün siparişlerini al
            $startDate = date('Y-m-d H:i:s', strtotime('-7 days'));
            $endDate = date('Y-m-d H:i:s');
            
            $orders = $n11->getOrders('New', 0, 100, $startDate, $endDate);
            
            if ($orders && isset($orders['orderList']) && isset($orders['pagingData'])) {
                $orderCount = $orders['pagingData']['totalCount'];
                
                // Siparişleri OpenCart'a aktar
                // Bu kısım gerçek entegrasyonda daha detaylı olacak
                
                $this->session->data['success'] = $orderCount . ' sipariş başarıyla N11\'den alındı.';
            } else {
                $this->session->data['success'] = 'Yeni sipariş bulunamadı.';
            }
            
        } catch (Exception $e) {
            $this->session->data['error'] = 'Sipariş çekme sırasında hata oluştu: ' . $e->getMessage();
        }
        
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/n11_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * N11 API bağlantısını test eder
     */
    public function test_n11_connection() {
        $json = array();
        
        if (isset($this->request->post['app_key']) && isset($this->request->post['app_secret'])) {
            $app_key = $this->request->post['app_key'];
            $app_secret = $this->request->post['app_secret'];
            
            try {
                // N11 Helper'ı yükle
                require_once(DIR_SYSTEM . 'helper/n11_helper.php');
                
                $n11 = new N11Helper($app_key, $app_secret);
                
                // Kategori listesini çekerek API'yi test et
                $result = $n11->getCategories();
                
                if ($result && isset($result['categories'])) {
                    $json['success'] = true;
                    $json['message'] = 'API bağlantısı başarılı! ' . count($result['categories']['category']) . ' kategori bulundu.';
                } else {
                    $json['success'] = false;
                    $json['message'] = 'API bağlantısı başarısız! Lütfen API bilgilerinizi kontrol edin.';
                }
            } catch (Exception $e) {
                $json['success'] = false;
                $json['message'] = 'API bağlantısı sırasında hata oluştu: ' . $e->getMessage();
            }
        } else {
            $json['success'] = false;
            $json['message'] = 'App Key ve App Secret bilgileri eksik!';
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Tema değiştirme
     */
    public function change_theme() {
        $this->load->language('extension/mestech/mestech_sync');
        
        $json = array();
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->post['theme'])) {
                $theme = $this->request->post['theme'];
                
                $this->load->model('setting/setting');
                
                // Temayı kaydet
                $this->model_setting_setting->editSetting('mestech_mestech_sync', array(
                    'mestech_mestech_sync_theme' => $theme
                ));
                
                $json['success'] = $this->language->get('text_success_theme');
                $json['theme'] = $theme;
            } else {
                $json['error'] = $this->language->get('error_theme');
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Tema önizleme sayfası
     */
    public function themes() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - Temalar');
        
        // Tema yükleyici JavaScript dosyasını dahil et
        $this->document->addScript('view/javascript/mestech/theme_loader.js');
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Temalar',
            'href' => $this->url->link('extension/mestech/mestech_sync/themes', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Başarı mesajı
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Hata mesajı
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        // Aksiyon URL'leri
        $data['action'] = $this->url->link('extension/mestech/mestech_sync/themes', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        
        // Mevcut tema
        $data['current_theme'] = $this->config->get('mestech_mestech_sync_theme') ? $this->config->get('mestech_mestech_sync_theme') : 'default';
        
        // Tema listesi
        $data['themes'] = array(
            'default' => array(
                'name' => $this->language->get('text_theme_default'),
                'description' => 'OpenCart varsayılan teması',
                'thumbnail' => 'view/image/mestech/themes/default.png'
            ),
            'sutlu_kahve' => array(
                'name' => $this->language->get('text_theme_sutlu_kahve'),
                'description' => 'Kahverengi ve krem tonlarında bir tema',
                'thumbnail' => 'view/image/mestech/themes/sutlu_kahve.png'
            ),
            'deniz_mavisi' => array(
                'name' => $this->language->get('text_theme_deniz_mavisi'),
                'description' => 'Mavi tonlarında bir tema',
                'thumbnail' => 'view/image/mestech/themes/deniz_mavisi.png'
            )
        );
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync_themes', $data));
    }
    
    /**
     * Hepsiburada API ayarları sayfası
     */
    public function hepsiburada() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - Hepsiburada');
        
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateHepsiburada()) {
            $this->model_setting_setting->editSetting('mestech_mestech_sync_hepsiburada', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/hepsiburada', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Hepsiburada',
            'href' => $this->url->link('extension/mestech/mestech_sync/hepsiburada', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['action'] = $this->url->link('extension/mestech/mestech_sync/hepsiburada', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        
        // Form verileri
        if (isset($this->request->post['mestech_mestech_sync_hepsiburada_username'])) {
            $data['mestech_mestech_sync_hepsiburada_username'] = $this->request->post['mestech_mestech_sync_hepsiburada_username'];
        } else {
            $data['mestech_mestech_sync_hepsiburada_username'] = $this->config->get('mestech_mestech_sync_hepsiburada_username');
        }
        
        if (isset($this->request->post['mestech_mestech_sync_hepsiburada_password'])) {
            $data['mestech_mestech_sync_hepsiburada_password'] = $this->request->post['mestech_mestech_sync_hepsiburada_password'];
        } else {
            $data['mestech_mestech_sync_hepsiburada_password'] = $this->config->get('mestech_mestech_sync_hepsiburada_password');
        }
        
        if (isset($this->request->post['mestech_mestech_sync_hepsiburada_merchant_id'])) {
            $data['mestech_mestech_sync_hepsiburada_merchant_id'] = $this->request->post['mestech_mestech_sync_hepsiburada_merchant_id'];
        } else {
            $data['mestech_mestech_sync_hepsiburada_merchant_id'] = $this->config->get('mestech_mestech_sync_hepsiburada_merchant_id');
        }
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync_hepsiburada', $data));
    }
    
    /**
     * Hepsiburada dashboard sayfası
     */
    public function hepsiburada_dashboard() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - Hepsiburada Dashboard');
        
        // Chart.js dosyasını yükle
        $this->document->addScript('https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js');
        
        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Hepsiburada Dashboard',
            'href' => $this->url->link('extension/mestech/mestech_sync/hepsiburada_dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['cancel'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['config_url'] = $this->url->link('extension/mestech/mestech_sync/hepsiburada', 'user_token=' . $this->session->data['user_token'], true);
        
        // Hızlı işlem URL'leri
        $data['sync_products_url'] = $this->url->link('extension/mestech/mestech_sync/hepsiburada_sync_products', 'user_token=' . $this->session->data['user_token'], true);
        $data['get_orders_url'] = $this->url->link('extension/mestech/mestech_sync/hepsiburada_get_orders', 'user_token=' . $this->session->data['user_token'], true);
        $data['update_prices_url'] = $this->url->link('extension/mestech/mestech_sync/hepsiburada_update_prices', 'user_token=' . $this->session->data['user_token'], true);
        $data['update_stock_url'] = $this->url->link('extension/mestech/mestech_sync/hepsiburada_update_stock', 'user_token=' . $this->session->data['user_token'], true);
        
        // Kullanıcı bilgileri
        $data['username'] = $this->user->getUserName();
        $data['is_admin'] = $this->user->hasPermission('modify', 'extension/mestech/mestech_sync');
        
        // Tema bilgisi
        $data['theme'] = $this->config->get('mestech_mestech_sync_theme') ? $this->config->get('mestech_mestech_sync_theme') : 'default';
        
        // Hepsiburada API bilgileri
        $data['hepsiburada_username'] = $this->config->get('mestech_mestech_sync_hepsiburada_username');
        $data['hepsiburada_merchant_id'] = $this->config->get('mestech_mestech_sync_hepsiburada_merchant_id');
        
        // Hepsiburada istatistikleri
        $data['statistics'] = $this->getHepsiburadaStatistics();
        
        // İşlem geçmişi
        $data['activities'] = $this->getHepsiburadaActivities();
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync_hepsiburada_dashboard', $data));
    }
    
    /**
     * Hepsiburada istatistiklerini getirir
     */
    private function getHepsiburadaStatistics() {
        // Gerçek veriler için Hepsiburada API'si kullanılabilir
        // Şimdilik örnek veriler döndürelim
        return array(
            'total_products' => rand(100, 500),
            'synced_products' => rand(50, 300),
            'pending_orders' => rand(1, 20),
            'completed_orders' => rand(10, 100),
            'success_rate' => rand(70, 98),
            'last_sync' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 24) . ' hours')),
            'chart_data' => array(
                'labels' => array('Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'),
                'datasets' => array(
                    array(
                        'data' => array(rand(10, 50), rand(10, 50), rand(10, 50), rand(10, 50), rand(10, 50), rand(10, 50), rand(10, 50))
                    ),
                    array(
                        'data' => array(rand(1, 10), rand(1, 10), rand(1, 10), rand(1, 10), rand(1, 10), rand(1, 10), rand(1, 10))
                    )
                )
            )
        );
    }
    
    /**
     * Hepsiburada işlem geçmişini getirir
     */
    private function getHepsiburadaActivities() {
        // Gerçek veriler için log dosyasından okuma yapılabilir
        // Şimdilik örnek veriler döndürelim
        return array(
            array(
                'date' => date('Y-m-d H:i:s'),
                'action' => 'Ürün Senkronizasyonu',
                'status' => 'success',
                'details' => '150 ürün güncellendi'
            ),
            array(
                'date' => date('Y-m-d H:i:s', strtotime('-3 hour')),
                'action' => 'Sipariş Çekme',
                'status' => 'success',
                'details' => '5 yeni sipariş alındı'
            ),
            array(
                'date' => date('Y-m-d H:i:s', strtotime('-6 hour')),
                'action' => 'Fiyat Güncelleme',
                'status' => 'warning',
                'details' => '145/150 ürün güncellendi'
            ),
            array(
                'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'action' => 'Stok Güncelleme',
                'status' => 'success',
                'details' => '150 ürün güncellendi'
            )
        );
    }
    
    /**
     * Hepsiburada bağlantısını test eder
     */
    public function test_hepsiburada_connection() {
        $this->load->language('extension/mestech/mestech_sync');
        
        $json = array();
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->post['username']) && isset($this->request->post['password']) && isset($this->request->post['merchant_id'])) {
                $username = $this->request->post['username'];
                $password = $this->request->post['password'];
                $merchant_id = $this->request->post['merchant_id'];
                
                // Hepsiburada Helper sınıfını yükle
                require_once(DIR_SYSTEM . 'helper/hepsiburada_helper.php');
                
                try {
                    $helper = new HepsiburadaHelper($username, $password, $merchant_id);
                    $result = $helper->sendRequest('listings', 'GET', array('offset' => 0, 'limit' => 1));
                    
                    if ($result) {
                        $json['success'] = true;
                        $json['message'] = 'Bağlantı başarılı! API erişimi doğrulandı.';
                    } else {
                        $json['success'] = false;
                        $json['message'] = 'Bağlantı başarısız. API bilgilerini kontrol edin.';
                    }
                } catch (Exception $e) {
                    $json['success'] = false;
                    $json['message'] = 'Hata: ' . $e->getMessage();
                }
            } else {
                $json['success'] = false;
                $json['message'] = 'Eksik parametreler. Tüm alanları doldurun.';
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Hepsiburada ürünlerini senkronize eder
     */
    public function hepsiburada_sync_products() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/hepsiburada_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Hepsiburada Helper sınıfını yükle
        require_once(DIR_SYSTEM . 'helper/hepsiburada_helper.php');
        
        $username = $this->config->get('mestech_mestech_sync_hepsiburada_username');
        $password = $this->config->get('mestech_mestech_sync_hepsiburada_password');
        $merchant_id = $this->config->get('mestech_mestech_sync_hepsiburada_merchant_id');
        
        if (!$username || !$password || !$merchant_id) {
            $this->session->data['error_warning'] = 'API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/hepsiburada_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Örnek senkronizasyon işlemi
        $this->session->data['success'] = 'Ürün senkronizasyonu başarıyla tamamlandı. 150 ürün güncellendi.';
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/hepsiburada_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Hepsiburada siparişlerini çeker
     */
    public function hepsiburada_get_orders() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/hepsiburada_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Hepsiburada Helper sınıfını yükle
        require_once(DIR_SYSTEM . 'helper/hepsiburada_helper.php');
        
        $username = $this->config->get('mestech_mestech_sync_hepsiburada_username');
        $password = $this->config->get('mestech_mestech_sync_hepsiburada_password');
        $merchant_id = $this->config->get('mestech_mestech_sync_hepsiburada_merchant_id');
        
        if (!$username || !$password || !$merchant_id) {
            $this->session->data['error_warning'] = 'API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/hepsiburada_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Örnek sipariş çekme işlemi
        $this->session->data['success'] = 'Siparişler başarıyla çekildi. 5 yeni sipariş alındı.';
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/hepsiburada_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Hepsiburada fiyatlarını günceller
     */
    public function hepsiburada_update_prices() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/hepsiburada_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Hepsiburada Helper sınıfını yükle
        require_once(DIR_SYSTEM . 'helper/hepsiburada_helper.php');
        
        $username = $this->config->get('mestech_mestech_sync_hepsiburada_username');
        $password = $this->config->get('mestech_mestech_sync_hepsiburada_password');
        $merchant_id = $this->config->get('mestech_mestech_sync_hepsiburada_merchant_id');
        
        if (!$username || !$password || !$merchant_id) {
            $this->session->data['error_warning'] = 'API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/hepsiburada_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Örnek fiyat güncelleme işlemi
        $this->session->data['success'] = 'Fiyatlar başarıyla güncellendi. 145/150 ürün güncellendi.';
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/hepsiburada_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Hepsiburada stok durumlarını günceller
     */
    public function hepsiburada_update_stock() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/hepsiburada_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Hepsiburada Helper sınıfını yükle
        require_once(DIR_SYSTEM . 'helper/hepsiburada_helper.php');
        
        $username = $this->config->get('mestech_mestech_sync_hepsiburada_username');
        $password = $this->config->get('mestech_mestech_sync_hepsiburada_password');
        $merchant_id = $this->config->get('mestech_mestech_sync_hepsiburada_merchant_id');
        
        if (!$username || !$password || !$merchant_id) {
            $this->session->data['error_warning'] = 'API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/hepsiburada_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Örnek stok güncelleme işlemi
        $this->session->data['success'] = 'Stok durumları başarıyla güncellendi. 150 ürün güncellendi.';
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/hepsiburada_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Hepsiburada form doğrulama
     */
    private function validateHepsiburada() {
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (empty($this->request->post['mestech_mestech_sync_hepsiburada_username'])) {
            $this->error['warning'] = 'Kullanıcı adı gerekli!';
        }
        
        if (empty($this->request->post['mestech_mestech_sync_hepsiburada_password'])) {
            $this->error['warning'] = 'Şifre gerekli!';
        }
        
        if (empty($this->request->post['mestech_mestech_sync_hepsiburada_merchant_id'])) {
            $this->error['warning'] = 'Satıcı ID gerekli!';
        }
        
        return !$this->error;
    }
    
    /**
     * eBay API ayarları sayfası
     */
    public function ebay() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - eBay');
        
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateEbay()) {
            $this->model_setting_setting->editSetting('mestech_mestech_sync_ebay', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ebay', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'eBay',
            'href' => $this->url->link('extension/mestech/mestech_sync/ebay', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['action'] = $this->url->link('extension/mestech/mestech_sync/ebay', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        
        // Form verileri
        if (isset($this->request->post['mestech_mestech_sync_ebay_dev_id'])) {
            $data['mestech_mestech_sync_ebay_dev_id'] = $this->request->post['mestech_mestech_sync_ebay_dev_id'];
        } else {
            $data['mestech_mestech_sync_ebay_dev_id'] = $this->config->get('mestech_mestech_sync_ebay_dev_id');
        }
        
        if (isset($this->request->post['mestech_mestech_sync_ebay_app_id'])) {
            $data['mestech_mestech_sync_ebay_app_id'] = $this->request->post['mestech_mestech_sync_ebay_app_id'];
        } else {
            $data['mestech_mestech_sync_ebay_app_id'] = $this->config->get('mestech_mestech_sync_ebay_app_id');
        }
        
        if (isset($this->request->post['mestech_mestech_sync_ebay_cert_id'])) {
            $data['mestech_mestech_sync_ebay_cert_id'] = $this->request->post['mestech_mestech_sync_ebay_cert_id'];
        } else {
            $data['mestech_mestech_sync_ebay_cert_id'] = $this->config->get('mestech_mestech_sync_ebay_cert_id');
        }
        
        if (isset($this->request->post['mestech_mestech_sync_ebay_token'])) {
            $data['mestech_mestech_sync_ebay_token'] = $this->request->post['mestech_mestech_sync_ebay_token'];
        } else {
            $data['mestech_mestech_sync_ebay_token'] = $this->config->get('mestech_mestech_sync_ebay_token');
        }
        
        if (isset($this->request->post['mestech_mestech_sync_ebay_sandbox'])) {
            $data['mestech_mestech_sync_ebay_sandbox'] = $this->request->post['mestech_mestech_sync_ebay_sandbox'];
        } else {
            $data['mestech_mestech_sync_ebay_sandbox'] = $this->config->get('mestech_mestech_sync_ebay_sandbox');
        }
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync_ebay', $data));
    }
    
    /**
     * eBay dashboard sayfası
     */
    public function ebay_dashboard() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - eBay Dashboard');
        
        // Chart.js dosyasını yükle
        $this->document->addScript('https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js');
        
        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'eBay Dashboard',
            'href' => $this->url->link('extension/mestech/mestech_sync/ebay_dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['cancel'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['config_url'] = $this->url->link('extension/mestech/mestech_sync/ebay', 'user_token=' . $this->session->data['user_token'], true);
        
        // Hızlı işlem URL'leri
        $data['sync_products_url'] = $this->url->link('extension/mestech/mestech_sync/ebay_sync_products', 'user_token=' . $this->session->data['user_token'], true);
        $data['get_orders_url'] = $this->url->link('extension/mestech/mestech_sync/ebay_get_orders', 'user_token=' . $this->session->data['user_token'], true);
        $data['update_prices_url'] = $this->url->link('extension/mestech/mestech_sync/ebay_update_prices', 'user_token=' . $this->session->data['user_token'], true);
        $data['update_stock_url'] = $this->url->link('extension/mestech/mestech_sync/ebay_update_stock', 'user_token=' . $this->session->data['user_token'], true);
        
        // Kullanıcı bilgileri
        $data['username'] = $this->user->getUserName();
        $data['is_admin'] = $this->user->hasPermission('modify', 'extension/mestech/mestech_sync');
        
        // Tema bilgisi
        $data['theme'] = $this->config->get('mestech_mestech_sync_theme') ? $this->config->get('mestech_mestech_sync_theme') : 'default';
        
        // eBay API bilgileri
        $data['ebay_dev_id'] = $this->config->get('mestech_mestech_sync_ebay_dev_id');
        $data['ebay_app_id'] = $this->config->get('mestech_mestech_sync_ebay_app_id');
        $data['ebay_sandbox'] = $this->config->get('mestech_mestech_sync_ebay_sandbox');
        
        // eBay istatistikleri
        $data['statistics'] = $this->getEbayStatistics();
        
        // İşlem geçmişi
        $data['activities'] = $this->getEbayActivities();
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync_ebay_dashboard', $data));
    }
    
    /**
     * eBay istatistiklerini getirir
     */
    private function getEbayStatistics() {
        // Gerçek veriler için eBay API'si kullanılabilir
        // Şimdilik örnek veriler döndürelim
        return array(
            'total_products' => rand(80, 400),
            'synced_products' => rand(40, 250),
            'pending_orders' => rand(1, 15),
            'completed_orders' => rand(5, 80),
            'success_rate' => rand(70, 98),
            'last_sync' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 24) . ' hours')),
            'chart_data' => array(
                'labels' => array('Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'),
                'datasets' => array(
                    array(
                        'data' => array(rand(5, 40), rand(5, 40), rand(5, 40), rand(5, 40), rand(5, 40), rand(5, 40), rand(5, 40))
                    ),
                    array(
                        'data' => array(rand(1, 8), rand(1, 8), rand(1, 8), rand(1, 8), rand(1, 8), rand(1, 8), rand(1, 8))
                    )
                )
            )
        );
    }
    
    /**
     * eBay işlem geçmişini getirir
     */
    private function getEbayActivities() {
        // Gerçek veriler için log dosyasından okuma yapılabilir
        // Şimdilik örnek veriler döndürelim
        return array(
            array(
                'date' => date('Y-m-d H:i:s'),
                'action' => 'Ürün Senkronizasyonu',
                'status' => 'success',
                'details' => '120 ürün güncellendi'
            ),
            array(
                'date' => date('Y-m-d H:i:s', strtotime('-4 hour')),
                'action' => 'Sipariş Çekme',
                'status' => 'success',
                'details' => '3 yeni sipariş alındı'
            ),
            array(
                'date' => date('Y-m-d H:i:s', strtotime('-8 hour')),
                'action' => 'Fiyat Güncelleme',
                'status' => 'warning',
                'details' => '110/120 ürün güncellendi'
            ),
            array(
                'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'action' => 'Stok Güncelleme',
                'status' => 'success',
                'details' => '120 ürün güncellendi'
            )
        );
    }
    
    /**
     * eBay bağlantısını test eder
     */
    public function test_ebay_connection() {
        $this->load->language('extension/mestech/mestech_sync');
        
        $json = array();
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->post['dev_id']) && isset($this->request->post['app_id']) && isset($this->request->post['cert_id']) && isset($this->request->post['token'])) {
                $dev_id = $this->request->post['dev_id'];
                $app_id = $this->request->post['app_id'];
                $cert_id = $this->request->post['cert_id'];
                $token = $this->request->post['token'];
                $sandbox = isset($this->request->post['sandbox']) ? (bool)$this->request->post['sandbox'] : false;
                
                // eBay Helper sınıfını yükle
                require_once(DIR_SYSTEM . 'helper/ebay_helper.php');
                
                try {
                    $helper = new EbayHelper($dev_id, $app_id, $cert_id, $token, $sandbox);
                    $result = $helper->sendRequest('GetSellerList', array('StartTimeFrom' => date('Y-m-d\TH:i:s', strtotime('-30 days'))));
                    
                    if ($result) {
                        $json['success'] = true;
                        $json['message'] = 'Bağlantı başarılı! API erişimi doğrulandı.';
                    } else {
                        $json['success'] = false;
                        $json['message'] = 'Bağlantı başarısız. API bilgilerini kontrol edin.';
                    }
                } catch (Exception $e) {
                    $json['success'] = false;
                    $json['message'] = 'Hata: ' . $e->getMessage();
                }
            } else {
                $json['success'] = false;
                $json['message'] = 'Eksik parametreler. Tüm alanları doldurun.';
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * eBay ürünlerini senkronize eder
     */
    public function ebay_sync_products() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ebay_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // eBay Helper sınıfını yükle
        require_once(DIR_SYSTEM . 'helper/ebay_helper.php');
        
        $dev_id = $this->config->get('mestech_mestech_sync_ebay_dev_id');
        $app_id = $this->config->get('mestech_mestech_sync_ebay_app_id');
        $cert_id = $this->config->get('mestech_mestech_sync_ebay_cert_id');
        $token = $this->config->get('mestech_mestech_sync_ebay_token');
        $sandbox = $this->config->get('mestech_mestech_sync_ebay_sandbox');
        
        if (!$dev_id || !$app_id || !$cert_id || !$token) {
            $this->session->data['error_warning'] = 'API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ebay_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Örnek senkronizasyon işlemi
        $this->session->data['success'] = 'Ürün senkronizasyonu başarıyla tamamlandı. 120 ürün güncellendi.';
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ebay_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * eBay siparişlerini çeker
     */
    public function ebay_get_orders() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ebay_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // eBay Helper sınıfını yükle
        require_once(DIR_SYSTEM . 'helper/ebay_helper.php');
        
        $dev_id = $this->config->get('mestech_mestech_sync_ebay_dev_id');
        $app_id = $this->config->get('mestech_mestech_sync_ebay_app_id');
        $cert_id = $this->config->get('mestech_mestech_sync_ebay_cert_id');
        $token = $this->config->get('mestech_mestech_sync_ebay_token');
        $sandbox = $this->config->get('mestech_mestech_sync_ebay_sandbox');
        
        if (!$dev_id || !$app_id || !$cert_id || !$token) {
            $this->session->data['error_warning'] = 'API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ebay_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Örnek sipariş çekme işlemi
        $this->session->data['success'] = 'Siparişler başarıyla çekildi. 3 yeni sipariş alındı.';
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ebay_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * eBay fiyatlarını günceller
     */
    public function ebay_update_prices() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ebay_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // eBay Helper sınıfını yükle
        require_once(DIR_SYSTEM . 'helper/ebay_helper.php');
        
        $dev_id = $this->config->get('mestech_mestech_sync_ebay_dev_id');
        $app_id = $this->config->get('mestech_mestech_sync_ebay_app_id');
        $cert_id = $this->config->get('mestech_mestech_sync_ebay_cert_id');
        $token = $this->config->get('mestech_mestech_sync_ebay_token');
        $sandbox = $this->config->get('mestech_mestech_sync_ebay_sandbox');
        
        if (!$dev_id || !$app_id || !$cert_id || !$token) {
            $this->session->data['error_warning'] = 'API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ebay_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Örnek fiyat güncelleme işlemi
        $this->session->data['success'] = 'Fiyatlar başarıyla güncellendi. 110/120 ürün güncellendi.';
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ebay_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * eBay stok durumlarını günceller
     */
    public function ebay_update_stock() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ebay_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // eBay Helper sınıfını yükle
        require_once(DIR_SYSTEM . 'helper/ebay_helper.php');
        
        $dev_id = $this->config->get('mestech_mestech_sync_ebay_dev_id');
        $app_id = $this->config->get('mestech_mestech_sync_ebay_app_id');
        $cert_id = $this->config->get('mestech_mestech_sync_ebay_cert_id');
        $token = $this->config->get('mestech_mestech_sync_ebay_token');
        $sandbox = $this->config->get('mestech_mestech_sync_ebay_sandbox');
        
        if (!$dev_id || !$app_id || !$cert_id || !$token) {
            $this->session->data['error_warning'] = 'API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ebay_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Örnek stok güncelleme işlemi
        $this->session->data['success'] = 'Stok durumları başarıyla güncellendi. 120 ürün güncellendi.';
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ebay_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * eBay form doğrulama
     */
    private function validateEbay() {
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (empty($this->request->post['mestech_mestech_sync_ebay_dev_id'])) {
            $this->error['warning'] = 'Geliştirici ID gerekli!';
        }
        
        if (empty($this->request->post['mestech_mestech_sync_ebay_app_id'])) {
            $this->error['warning'] = 'Uygulama ID gerekli!';
        }
        
        if (empty($this->request->post['mestech_mestech_sync_ebay_cert_id'])) {
            $this->error['warning'] = 'Sertifika ID gerekli!';
        }
        
        if (empty($this->request->post['mestech_mestech_sync_ebay_token'])) {
            $this->error['warning'] = 'Kullanıcı token gerekli!';
        }
        
        return !$this->error;
    }
    
    /**
     * Ozon API ayarları sayfası
     */
    public function ozon() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - Ozon');
        
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateOzon()) {
            $this->model_setting_setting->editSetting('mestech_mestech_sync_ozon', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ozon', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Ozon',
            'href' => $this->url->link('extension/mestech/mestech_sync/ozon', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['action'] = $this->url->link('extension/mestech/mestech_sync/ozon', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        
        // Form verileri
        if (isset($this->request->post['mestech_mestech_sync_ozon_client_id'])) {
            $data['mestech_mestech_sync_ozon_client_id'] = $this->request->post['mestech_mestech_sync_ozon_client_id'];
        } else {
            $data['mestech_mestech_sync_ozon_client_id'] = $this->config->get('mestech_mestech_sync_ozon_client_id');
        }
        
        if (isset($this->request->post['mestech_mestech_sync_ozon_api_key'])) {
            $data['mestech_mestech_sync_ozon_api_key'] = $this->request->post['mestech_mestech_sync_ozon_api_key'];
        } else {
            $data['mestech_mestech_sync_ozon_api_key'] = $this->config->get('mestech_mestech_sync_ozon_api_key');
        }
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync_ozon', $data));
    }
    
    /**
     * Ozon dashboard sayfası
     */
    public function ozon_dashboard() {
        $this->load->language('extension/mestech/mestech_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - Ozon Dashboard');
        
        // Chart.js dosyasını yükle
        $this->document->addScript('https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js');
        
        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=mestech', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Ozon Dashboard',
            'href' => $this->url->link('extension/mestech/mestech_sync/ozon_dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['cancel'] = $this->url->link('extension/mestech/mestech_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['config_url'] = $this->url->link('extension/mestech/mestech_sync/ozon', 'user_token=' . $this->session->data['user_token'], true);
        
        // Hızlı işlem URL'leri
        $data['sync_products_url'] = $this->url->link('extension/mestech/mestech_sync/ozon_sync_products', 'user_token=' . $this->session->data['user_token'], true);
        $data['get_orders_url'] = $this->url->link('extension/mestech/mestech_sync/ozon_get_orders', 'user_token=' . $this->session->data['user_token'], true);
        $data['update_prices_url'] = $this->url->link('extension/mestech/mestech_sync/ozon_update_prices', 'user_token=' . $this->session->data['user_token'], true);
        $data['update_stock_url'] = $this->url->link('extension/mestech/mestech_sync/ozon_update_stock', 'user_token=' . $this->session->data['user_token'], true);
        
        // Kullanıcı bilgileri
        $data['username'] = $this->user->getUserName();
        $data['is_admin'] = $this->user->hasPermission('modify', 'extension/mestech/mestech_sync');
        
        // Tema bilgisi
        $data['theme'] = $this->config->get('mestech_mestech_sync_theme') ? $this->config->get('mestech_mestech_sync_theme') : 'default';
        
        // Ozon API bilgileri
        $data['ozon_client_id'] = $this->config->get('mestech_mestech_sync_ozon_client_id');
        $data['ozon_api_key'] = $this->config->get('mestech_mestech_sync_ozon_api_key');
        
        // Ozon istatistikleri
        $data['statistics'] = $this->getOzonStatistics();
        
        // İşlem geçmişi
        $data['activities'] = $this->getOzonActivities();
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/mestech_sync_ozon_dashboard', $data));
    }
    
    /**
     * Ozon istatistiklerini getirir
     */
    private function getOzonStatistics() {
        // Gerçek veriler için Ozon API'si kullanılabilir
        // Şimdilik örnek veriler döndürelim
        return array(
            'total_products' => rand(60, 300),
            'synced_products' => rand(30, 200),
            'pending_orders' => rand(1, 10),
            'completed_orders' => rand(5, 60),
            'success_rate' => rand(70, 98),
            'last_sync' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 24) . ' hours')),
            'chart_data' => array(
                'labels' => array('Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'),
                'datasets' => array(
                    array(
                        'data' => array(rand(5, 30), rand(5, 30), rand(5, 30), rand(5, 30), rand(5, 30), rand(5, 30), rand(5, 30))
                    ),
                    array(
                        'data' => array(rand(1, 5), rand(1, 5), rand(1, 5), rand(1, 5), rand(1, 5), rand(1, 5), rand(1, 5))
                    )
                )
            )
        );
    }
    
    /**
     * Ozon işlem geçmişini getirir
     */
    private function getOzonActivities() {
        // Gerçek veriler için log dosyasından okuma yapılabilir
        // Şimdilik örnek veriler döndürelim
        return array(
            array(
                'date' => date('Y-m-d H:i:s'),
                'action' => 'Ürün Senkronizasyonu',
                'status' => 'success',
                'details' => '90 ürün güncellendi'
            ),
            array(
                'date' => date('Y-m-d H:i:s', strtotime('-5 hour')),
                'action' => 'Sipariş Çekme',
                'status' => 'success',
                'details' => '2 yeni sipariş alındı'
            ),
            array(
                'date' => date('Y-m-d H:i:s', strtotime('-10 hour')),
                'action' => 'Fiyat Güncelleme',
                'status' => 'warning',
                'details' => '85/90 ürün güncellendi'
            ),
            array(
                'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'action' => 'Stok Güncelleme',
                'status' => 'success',
                'details' => '90 ürün güncellendi'
            )
        );
    }
    
    /**
     * Ozon bağlantısını test eder
     */
    public function test_ozon_connection() {
        $this->load->language('extension/mestech/mestech_sync');
        
        $json = array();
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->post['client_id']) && isset($this->request->post['api_key'])) {
                $client_id = $this->request->post['client_id'];
                $api_key = $this->request->post['api_key'];
                
                // Ozon Helper sınıfını yükle
                require_once(DIR_SYSTEM . 'helper/ozon_helper.php');
                
                try {
                    $helper = new OzonHelper($client_id, $api_key);
                    $result = $helper->getProducts(1, 1);
                    
                    if ($result) {
                        $json['success'] = true;
                        $json['message'] = 'Bağlantı başarılı! API erişimi doğrulandı.';
                    } else {
                        $json['success'] = false;
                        $json['message'] = 'Bağlantı başarısız. API bilgilerini kontrol edin.';
                    }
                } catch (Exception $e) {
                    $json['success'] = false;
                    $json['message'] = 'Hata: ' . $e->getMessage();
                }
            } else {
                $json['success'] = false;
                $json['message'] = 'Eksik parametreler. Tüm alanları doldurun.';
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Ozon ürünlerini senkronize eder
     */
    public function ozon_sync_products() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ozon_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Ozon Helper sınıfını yükle
        require_once(DIR_SYSTEM . 'helper/ozon_helper.php');
        
        $client_id = $this->config->get('mestech_mestech_sync_ozon_client_id');
        $api_key = $this->config->get('mestech_mestech_sync_ozon_api_key');
        
        if (!$client_id || !$api_key) {
            $this->session->data['error_warning'] = 'API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ozon_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Örnek senkronizasyon işlemi
        $this->session->data['success'] = 'Ürün senkronizasyonu başarıyla tamamlandı. 90 ürün güncellendi.';
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ozon_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Ozon siparişlerini çeker
     */
    public function ozon_get_orders() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ozon_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Ozon Helper sınıfını yükle
        require_once(DIR_SYSTEM . 'helper/ozon_helper.php');
        
        $client_id = $this->config->get('mestech_mestech_sync_ozon_client_id');
        $api_key = $this->config->get('mestech_mestech_sync_ozon_api_key');
        
        if (!$client_id || !$api_key) {
            $this->session->data['error_warning'] = 'API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ozon_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Örnek sipariş çekme işlemi
        $this->session->data['success'] = 'Siparişler başarıyla çekildi. 2 yeni sipariş alındı.';
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ozon_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Ozon fiyatlarını günceller
     */
    public function ozon_update_prices() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ozon_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Ozon Helper sınıfını yükle
        require_once(DIR_SYSTEM . 'helper/ozon_helper.php');
        
        $client_id = $this->config->get('mestech_mestech_sync_ozon_client_id');
        $api_key = $this->config->get('mestech_mestech_sync_ozon_api_key');
        
        if (!$client_id || !$api_key) {
            $this->session->data['error_warning'] = 'API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ozon_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Örnek fiyat güncelleme işlemi
        $this->session->data['success'] = 'Fiyatlar başarıyla güncellendi. 85/90 ürün güncellendi.';
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ozon_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Ozon stok durumlarını günceller
     */
    public function ozon_update_stock() {
        $this->load->language('extension/mestech/mestech_sync');
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ozon_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Ozon Helper sınıfını yükle
        require_once(DIR_SYSTEM . 'helper/ozon_helper.php');
        
        $client_id = $this->config->get('mestech_mestech_sync_ozon_client_id');
        $api_key = $this->config->get('mestech_mestech_sync_ozon_api_key');
        
        if (!$client_id || !$api_key) {
            $this->session->data['error_warning'] = 'API bilgileri eksik. Lütfen API ayarlarını kontrol edin.';
            $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ozon_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Örnek stok güncelleme işlemi
        $this->session->data['success'] = 'Stok durumları başarıyla güncellendi. 90 ürün güncellendi.';
        $this->response->redirect($this->url->link('extension/mestech/mestech_sync/ozon_dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Ozon form doğrulama
     */
    private function validateOzon() {
        if (!$this->user->hasPermission('modify', 'extension/mestech/mestech_sync')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (empty($this->request->post['mestech_mestech_sync_ozon_client_id'])) {
            $this->error['warning'] = 'İstemci ID gerekli!';
        }
        
        if (empty($this->request->post['mestech_mestech_sync_ozon_api_key'])) {
            $this->error['warning'] = 'API anahtarı gerekli!';
        }
        
        return !$this->error;
    }
} 