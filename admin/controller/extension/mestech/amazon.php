<?php
/**
 * Amazon Controller Class
 * 
 * Amazon SP-API entegrasyonu için admin panel controller sınıfı
 * Dashboard, ayarlar, ürün ve sipariş yönetimi işlemleri
 * 
 * @category   Controller
 * @package    MesChain-Sync
 * @subpackage Amazon
 * @version    3.0.4.0
 * @author     MezBjen Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

class ControllerExtensionMestechAmazon extends Controller {
    
    private $error = [];
    private $log;
    
    public function __construct($registry) {
        parent::__construct($registry);
        
        // Log dosyası oluştur
        $this->log = new Log('amazon.log');
        
        // Model yükle
        $this->load->model('extension/mestech/amazon');
    }
    
    /**
     * Ana dashboard sayfası
     */
    public function index() {
        $this->load->language('extension/module/amazon');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Breadcrumb
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/amazon', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        // İstatistikleri getir
        $data['statistics'] = $this->model_extension_mestech_amazon->getStatistics();
        
        // URL'ler
        $data['action'] = $this->url->link('extension/mestech/amazon/save', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        $data['test_connection'] = $this->url->link('extension/mestech/amazon/testConnection', 'user_token=' . $this->session->data['user_token'], true);
        $data['sync_products'] = $this->url->link('extension/mestech/amazon/syncProducts', 'user_token=' . $this->session->data['user_token'], true);
        $data['sync_orders'] = $this->url->link('extension/mestech/amazon/syncOrders', 'user_token=' . $this->session->data['user_token'], true);
        $data['products_url'] = $this->url->link('extension/mestech/amazon/products', 'user_token=' . $this->session->data['user_token'], true);
        $data['orders_url'] = $this->url->link('extension/mestech/amazon/orders', 'user_token=' . $this->session->data['user_token'], true);
        $data['logs_url'] = $this->url->link('extension/mestech/amazon/logs', 'user_token=' . $this->session->data['user_token'], true);
        
        // Ayarları getir
        $data['amazon_client_id'] = $this->config->get('amazon_client_id');
        $data['amazon_client_secret'] = $this->config->get('amazon_client_secret');
        $data['amazon_refresh_token'] = $this->config->get('amazon_refresh_token');
        $data['amazon_seller_id'] = $this->config->get('amazon_seller_id');
        $data['amazon_marketplace_id'] = $this->config->get('amazon_marketplace_id');
        $data['amazon_sandbox_mode'] = $this->config->get('amazon_sandbox_mode');
        $data['amazon_auto_sync'] = $this->config->get('amazon_auto_sync');
        $data['amazon_sync_interval'] = $this->config->get('amazon_sync_interval');
        $data['amazon_fulfillment_channel'] = $this->config->get('amazon_fulfillment_channel');
        $data['amazon_status'] = $this->config->get('amazon_status');
        
        // Marketplace seçenekleri
        $data['marketplaces'] = [
            'ATVPDKIKX0DER' => $this->language->get('marketplace_us'),
            'A2EUQ1WTGCTBG2' => $this->language->get('marketplace_ca'),
            'A1AM78C64UM0Y8' => $this->language->get('marketplace_mx'),
            'A2Q3Y263D00KWC' => $this->language->get('marketplace_br'),
            'A1F83G8C2ARO7P' => $this->language->get('marketplace_uk'),
            'A1PA6795UKMFR9' => $this->language->get('marketplace_de'),
            'A13V1IB3VIYZZH' => $this->language->get('marketplace_fr'),
            'APJ6JRA9NG5V4' => $this->language->get('marketplace_it'),
            'A1RKKUPIHCS9HS' => $this->language->get('marketplace_es'),
            'A1805IZSGTT6HS' => $this->language->get('marketplace_nl'),
            'A2NODRKZP88ZB9' => $this->language->get('marketplace_se'),
            'A1C3SOZRARQ6R3' => $this->language->get('marketplace_pl'),
            'A33AVAJ2PDY3EV' => $this->language->get('marketplace_tr'),
            'A1VC38T7YXB528' => $this->language->get('marketplace_jp'),
            'A19VAU5U5O7RUS' => $this->language->get('marketplace_sg'),
            'A39IBJ37TRP1C6' => $this->language->get('marketplace_au'),
            'A21TJRUUN4KGV' => $this->language->get('marketplace_in'),
            'A2VIGQ35RCS4UG' => $this->language->get('marketplace_ae')
        ];
        
        // Fulfillment seçenekleri
        $data['fulfillment_channels'] = [
            'FBM' => $this->language->get('fulfillment_fbm'),
            'FBA' => $this->language->get('fulfillment_fba')
        ];
        
        // Header ve footer
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/amazon/settings', $data));
    }
    
    /**
     * Ayarları kaydet
     */
    public function save() {
        $this->load->language('extension/module/amazon');
        
        $json = [];
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/amazon')) {
            $json['error'] = $this->language->get('error_permission');
        }
        
        if (!$json) {
            $this->load->model('setting/setting');
            
            $setting_data = [
                'amazon_client_id' => $this->request->post['amazon_client_id'] ?? '',
                'amazon_client_secret' => $this->request->post['amazon_client_secret'] ?? '',
                'amazon_refresh_token' => $this->request->post['amazon_refresh_token'] ?? '',
                'amazon_seller_id' => $this->request->post['amazon_seller_id'] ?? '',
                'amazon_marketplace_id' => $this->request->post['amazon_marketplace_id'] ?? 'ATVPDKIKX0DER',
                'amazon_sandbox_mode' => isset($this->request->post['amazon_sandbox_mode']) ? 1 : 0,
                'amazon_auto_sync' => isset($this->request->post['amazon_auto_sync']) ? 1 : 0,
                'amazon_sync_interval' => (int)($this->request->post['amazon_sync_interval'] ?? 60),
                'amazon_fulfillment_channel' => $this->request->post['amazon_fulfillment_channel'] ?? 'FBM',
                'amazon_status' => isset($this->request->post['amazon_status']) ? 1 : 0
            ];
            
            $this->model_setting_setting->editSetting('amazon', $setting_data);
            
            $this->model_extension_mestech_amazon->addLog('settings', 'info', 'Amazon ayarları güncellendi', $setting_data);
            
            $json['success'] = $this->language->get('text_success');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * API bağlantı testi
     */
    public function testConnection() {
        $this->load->language('extension/module/amazon');
        
        $json = [];
        
        if (!$this->user->hasPermission('access', 'extension/mestech/amazon')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $result = $this->model_extension_mestech_amazon->testConnection();
            
            if ($result['success']) {
                $json['success'] = $result['message'];
            } else {
                $json['error'] = $result['message'];
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Ürün senkronizasyonu
     */
    public function syncProducts() {
        $this->load->language('extension/module/amazon');
        
        $json = [];
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/amazon')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $limit = (int)($this->request->get['limit'] ?? 50);
            $result = $this->model_extension_mestech_amazon->syncProducts($limit);
            
            if (isset($result['error'])) {
                $json['error'] = $result['error'];
            } else {
                $json['success'] = sprintf($this->language->get('success_products_sync'), $result['success'], $result['total']);
                $json['data'] = $result;
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Sipariş senkronizasyonu
     */
    public function syncOrders() {
        $this->load->language('extension/module/amazon');
        
        $json = [];
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/amazon')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $days = (int)($this->request->get['days'] ?? 7);
            $result = $this->model_extension_mestech_amazon->syncOrders($days);
            
            if (isset($result['error'])) {
                $json['error'] = $result['error'];
            } else {
                $json['success'] = sprintf($this->language->get('success_orders_sync'), $result['success'], $result['total']);
                $json['data'] = $result;
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Ürün yönetimi sayfası
     */
    public function products() {
        $this->load->language('extension/module/amazon');
        
        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_products'));
        
        // Breadcrumb
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/amazon', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_products'),
            'href' => $this->url->link('extension/mestech/amazon/products', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        // Filtreleme
        $filter_data = [
            'filter_sku' => $this->request->get['filter_sku'] ?? '',
            'filter_asin' => $this->request->get['filter_asin'] ?? '',
            'filter_status' => $this->request->get['filter_status'] ?? '',
            'sort' => $this->request->get['sort'] ?? 'ap.updated_at',
            'order' => $this->request->get['order'] ?? 'DESC',
            'start' => ($this->request->get['page'] ?? 1 - 1) * 20,
            'limit' => 20
        ];
        
        // Ürünleri getir
        $data['products'] = $this->model_extension_mestech_amazon->getProducts($filter_data);
        
        // Pagination
        $product_total = $this->model_extension_mestech_amazon->getTotalProducts($filter_data);
        
        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $this->request->get['page'] ?? 1;
        $pagination->limit = 20;
        $pagination->url = $this->url->link('extension/mestech/amazon/products', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);
        
        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($this->request->get['page'] ?? 1 - 1) * 20) + 1 : 0, ((($this->request->get['page'] ?? 1 - 1) * 20) > ($product_total - 20)) ? $product_total : ((($this->request->get['page'] ?? 1 - 1) * 20) + 20), $product_total, ceil($product_total / 20));
        
        // URL'ler
        $data['sync_url'] = $this->url->link('extension/mestech/amazon/syncProducts', 'user_token=' . $this->session->data['user_token'], true);
        $data['back_url'] = $this->url->link('extension/mestech/amazon', 'user_token=' . $this->session->data['user_token'], true);
        
        // Filtre değerleri
        $data['filter_sku'] = $filter_data['filter_sku'];
        $data['filter_asin'] = $filter_data['filter_asin'];
        $data['filter_status'] = $filter_data['filter_status'];
        
        // Header ve footer
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/amazon/products', $data));
    }
    
    /**
     * Sipariş yönetimi sayfası
     */
    public function orders() {
        $this->load->language('extension/module/amazon');
        
        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_orders'));
        
        // Breadcrumb
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/amazon', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_orders'),
            'href' => $this->url->link('extension/mestech/amazon/orders', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        // Filtreleme
        $filter_data = [
            'filter_order_number' => $this->request->get['filter_order_number'] ?? '',
            'filter_status' => $this->request->get['filter_status'] ?? '',
            'filter_date_start' => $this->request->get['filter_date_start'] ?? '',
            'filter_date_end' => $this->request->get['filter_date_end'] ?? '',
            'start' => ($this->request->get['page'] ?? 1 - 1) * 20,
            'limit' => 20
        ];
        
        // Siparişleri getir
        $data['orders'] = $this->model_extension_mestech_amazon->getOrders($filter_data);
        
        // Pagination
        $order_total = $this->model_extension_mestech_amazon->getTotalOrders($filter_data);
        
        $pagination = new Pagination();
        $pagination->total = $order_total;
        $pagination->page = $this->request->get['page'] ?? 1;
        $pagination->limit = 20;
        $pagination->url = $this->url->link('extension/mestech/amazon/orders', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);
        
        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($this->request->get['page'] ?? 1 - 1) * 20) + 1 : 0, ((($this->request->get['page'] ?? 1 - 1) * 20) > ($order_total - 20)) ? $order_total : ((($this->request->get['page'] ?? 1 - 1) * 20) + 20), $order_total, ceil($order_total / 20));
        
        // URL'ler
        $data['sync_url'] = $this->url->link('extension/mestech/amazon/syncOrders', 'user_token=' . $this->session->data['user_token'], true);
        $data['back_url'] = $this->url->link('extension/mestech/amazon', 'user_token=' . $this->session->data['user_token'], true);
        
        // Filtre değerleri
        $data['filter_order_number'] = $filter_data['filter_order_number'];
        $data['filter_status'] = $filter_data['filter_status'];
        $data['filter_date_start'] = $filter_data['filter_date_start'];
        $data['filter_date_end'] = $filter_data['filter_date_end'];
        
        // Header ve footer
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/amazon/orders', $data));
    }
    
    /**
     * Log yönetimi sayfası
     */
    public function logs() {
        $this->load->language('extension/module/amazon');
        
        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_logs'));
        
        // Breadcrumb
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/amazon', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_logs'),
            'href' => $this->url->link('extension/mestech/amazon/logs', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        // Filtreleme
        $filter_data = [
            'filter_operation' => $this->request->get['filter_operation'] ?? '',
            'filter_level' => $this->request->get['filter_level'] ?? '',
            'filter_date_start' => $this->request->get['filter_date_start'] ?? '',
            'filter_date_end' => $this->request->get['filter_date_end'] ?? '',
            'start' => ($this->request->get['page'] ?? 1 - 1) * 20,
            'limit' => 20
        ];
        
        // Logları getir
        $data['logs'] = $this->model_extension_mestech_amazon->getLogs($filter_data);
        
        // Pagination
        $log_total = $this->model_extension_mestech_amazon->getTotalLogs($filter_data);
        
        $pagination = new Pagination();
        $pagination->total = $log_total;
        $pagination->page = $this->request->get['page'] ?? 1;
        $pagination->limit = 20;
        $pagination->url = $this->url->link('extension/mestech/amazon/logs', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);
        
        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($log_total) ? (($this->request->get['page'] ?? 1 - 1) * 20) + 1 : 0, ((($this->request->get['page'] ?? 1 - 1) * 20) > ($log_total - 20)) ? $log_total : ((($this->request->get['page'] ?? 1 - 1) * 20) + 20), $log_total, ceil($log_total / 20));
        
        // URL'ler
        $data['clear_url'] = $this->url->link('extension/mestech/amazon/clearLogs', 'user_token=' . $this->session->data['user_token'], true);
        $data['back_url'] = $this->url->link('extension/mestech/amazon', 'user_token=' . $this->session->data['user_token'], true);
        
        // Filtre değerleri
        $data['filter_operation'] = $filter_data['filter_operation'];
        $data['filter_level'] = $filter_data['filter_level'];
        $data['filter_date_start'] = $filter_data['filter_date_start'];
        $data['filter_date_end'] = $filter_data['filter_date_end'];
        
        // Header ve footer
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/amazon/logs', $data));
    }
    
    /**
     * Logları temizle
     */
    public function clearLogs() {
        $this->load->language('extension/module/amazon');
        
        $json = [];
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/amazon')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $this->db->query("DELETE FROM " . DB_PREFIX . "mestech_amazon_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            
            $json['success'] = $this->language->get('text_logs_cleared');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Modül kurulum
     */
    public function install() {
        $this->load->model('extension/mestech/amazon');
        $this->load->model('user/user_group');
        
        // Veritabanı tablolarını oluştur
        $this->model_extension_mestech_amazon->install();
        
        // Kullanıcı izinlerini ekle
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mestech/amazon');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mestech/amazon');
        
        $this->log->write('Amazon modülü kuruldu');
    }
    
    /**
     * Modül kaldırma
     */
    public function uninstall() {
        $this->load->model('extension/mestech/amazon');
        
        // Veritabanı tablolarını sil
        $this->model_extension_mestech_amazon->uninstall();
        
        $this->log->write('Amazon modülü kaldırıldı');
    }
    
    /**
     * Hata doğrulama
     */
    private function validate() {
        if (!$this->user->hasPermission('modify', 'extension/mestech/amazon')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (empty($this->request->post['amazon_client_id'])) {
            $this->error['client_id'] = $this->language->get('error_client_id');
        }
        
        if (empty($this->request->post['amazon_client_secret'])) {
            $this->error['client_secret'] = $this->language->get('error_client_secret');
        }
        
        if (empty($this->request->post['amazon_refresh_token'])) {
            $this->error['refresh_token'] = $this->language->get('error_refresh_token');
        }
        
        if (empty($this->request->post['amazon_seller_id'])) {
            $this->error['seller_id'] = $this->language->get('error_seller_id');
        }
        
        return !$this->error;
    }
}