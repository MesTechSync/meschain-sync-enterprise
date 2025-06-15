<?php
/**
 * MesChain-Sync - Ozon Pazaryeri Entegrasyonu
 * 
 * Bu controller, Ozon pazaryeri entegrasyonu için gerekli fonksiyonları içerir.
 * Dashboard, ürün yönetimi, sipariş yönetimi ve API ayarları gibi temel işlevleri sağlar.
 * 
 * @author      MesTech
 * @copyright   Copyright (c) 2023, MesTech
 * @license     MIT License
 * @version     1.0.0
 */
class ControllerExtensionMestechOzon extends Controller {
    private $error = array();
    
    /**
     * Ana sayfa (Dashboard)
     */
    public function index() {
        $this->load->language('extension/mestech/ozon/ozon');
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/mestech/ozon');
        
        // API bağlantı durumunu kontrol et
        $data['api_status'] = $this->model_extension_mestech_ozon->checkApiConnection();
        
        // İstatistikleri yükle
        $data['total_products'] = $this->model_extension_mestech_ozon->getTotalProducts();
        $data['total_orders'] = $this->model_extension_mestech_ozon->getTotalOrders();
        $data['pending_orders'] = $this->model_extension_mestech_ozon->getPendingOrders();
        
        // Son siparişleri yükle
        $data['latest_orders'] = $this->model_extension_mestech_ozon->getLatestOrders(5);
        
        // Breadcrumbs
        $data['breadcrumbs'] = $this->getBreadcrumbs();
        
        // Butonlar ve linkler
        $data['sync_products_url'] = $this->url->link('extension/mestech/ozon/syncProducts', 'user_token=' . $this->session->data['user_token'], true);
        $data['sync_orders_url'] = $this->url->link('extension/mestech/ozon/syncOrders', 'user_token=' . $this->session->data['user_token'], true);
        $data['settings_url'] = $this->url->link('extension/mestech/ozon/settings', 'user_token=' . $this->session->data['user_token'], true);
        $data['products_url'] = $this->url->link('extension/mestech/ozon/products', 'user_token=' . $this->session->data['user_token'], true);
        $data['orders_url'] = $this->url->link('extension/mestech/ozon/orders', 'user_token=' . $this->session->data['user_token'], true);
        $data['logs_url'] = $this->url->link('extension/mestech/ozon/logs', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablon verilerini hazırla
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        // Şablonu yükle
        $this->response->setOutput($this->load->view('extension/mestech/ozon/dashboard', $data));
    }
    
    /**
     * API ve genel ayarlar
     */
    public function settings() {
        $this->load->language('extension/mestech/ozon/ozon');
        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_settings'));
        
        $this->load->model('extension/mestech/ozon');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateSettings()) {
            $this->model_extension_mestech_ozon->saveSettings($this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success_settings');
            
            $this->response->redirect($this->url->link('extension/mestech/ozon/settings', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Hata mesajlarını hazırla
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        // Form alanları için hata mesajlarını hazırla
        foreach (['api_key', 'api_secret', 'client_id'] as $field) {
            if (isset($this->error[$field])) {
                $data['error_' . $field] = $this->error[$field];
            } else {
                $data['error_' . $field] = '';
            }
        }
        
        // Ayarları yükle
        $settings = $this->model_extension_mestech_ozon->getSettings();
        
        // Form değerlerini hazırla
        $fields = [
            'module_mestech_ozon_status',
            'module_mestech_ozon_api_key',
            'module_mestech_ozon_api_secret',
            'module_mestech_ozon_client_id',
            'module_mestech_ozon_api_url',
            'module_mestech_ozon_auto_sync'
        ];
        
        foreach ($fields as $field) {
            if (isset($this->request->post[$field])) {
                $data[$field] = $this->request->post[$field];
            } elseif (isset($settings[$field])) {
                $data[$field] = $settings[$field];
            } else {
                $data[$field] = '';
            }
        }
        
        // Başarı mesajını hazırla
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Breadcrumbs
        $data['breadcrumbs'] = $this->getBreadcrumbs();
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_settings'),
            'href' => $this->url->link('extension/mestech/ozon/settings', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        // Form action
        $data['action'] = $this->url->link('extension/mestech/ozon/settings', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/mestech/ozon', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablon verilerini hazırla
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        // Şablonu yükle
        $this->response->setOutput($this->load->view('extension/mestech/ozon/settings', $data));
    }
    
    /**
     * Ürün yönetimi
     */
    public function products() {
        $this->load->language('extension/mestech/ozon/ozon');
        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_products'));
        
        $this->load->model('extension/mestech/ozon');
        
        // Filtreleme ve sayfalama parametreleri
        $filter_data = [
            'filter_name'       => isset($this->request->get['filter_name']) ? $this->request->get['filter_name'] : '',
            'filter_model'      => isset($this->request->get['filter_model']) ? $this->request->get['filter_model'] : '',
            'filter_status'     => isset($this->request->get['filter_status']) ? $this->request->get['filter_status'] : '',
            'sort'              => isset($this->request->get['sort']) ? $this->request->get['sort'] : 'p.date_added',
            'order'             => isset($this->request->get['order']) ? $this->request->get['order'] : 'DESC',
            'page'              => isset($this->request->get['page']) ? $this->request->get['page'] : 1,
            'limit'             => 10
        ];
        
        // Ürünleri yükle
        $data['products'] = $this->model_extension_mestech_ozon->getProducts($filter_data);
        $total_products = $this->model_extension_mestech_ozon->getTotalProducts($filter_data);
        
        // Sayfalama
        $pagination = new Pagination();
        $pagination->total = $total_products;
        $pagination->page = $filter_data['page'];
        $pagination->limit = $filter_data['limit'];
        $pagination->url = $this->url->link('extension/mestech/ozon/products', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);
        
        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($total_products) ? (($filter_data['page'] - 1) * $filter_data['limit']) + 1 : 0, ((($filter_data['page'] - 1) * $filter_data['limit']) > ($total_products - $filter_data['limit'])) ? $total_products : ((($filter_data['page'] - 1) * $filter_data['limit']) + $filter_data['limit']), $total_products, ceil($total_products / $filter_data['limit']));
        
        // Başarı mesajını hazırla
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Filtreleme ve sıralama URL'leri
        $url = '';
        
        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }
        
        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
        }
        
        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }
        
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }
        
        // Sıralama URL'leri
        $data['sort_name'] = $this->url->link('extension/mestech/ozon/products', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name' . $url, true);
        $data['sort_model'] = $this->url->link('extension/mestech/ozon/products', 'user_token=' . $this->session->data['user_token'] . '&sort=p.model' . $url, true);
        $data['sort_status'] = $this->url->link('extension/mestech/ozon/products', 'user_token=' . $this->session->data['user_token'] . '&sort=p.status' . $url, true);
        $data['sort_date_added'] = $this->url->link('extension/mestech/ozon/products', 'user_token=' . $this->session->data['user_token'] . '&sort=p.date_added' . $url, true);
        
        // Filtre değerlerini hazırla
        $data['filter_name'] = $filter_data['filter_name'];
        $data['filter_model'] = $filter_data['filter_model'];
        $data['filter_status'] = $filter_data['filter_status'];
        
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        
        // Breadcrumbs
        $data['breadcrumbs'] = $this->getBreadcrumbs();
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_products'),
            'href' => $this->url->link('extension/mestech/ozon/products', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        // Butonlar ve linkler
        $data['sync_products_url'] = $this->url->link('extension/mestech/ozon/syncProducts', 'user_token=' . $this->session->data['user_token'], true);
        $data['add_product_url'] = $this->url->link('extension/mestech/ozon/addProduct', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablon verilerini hazırla
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        // Şablonu yükle
        $this->response->setOutput($this->load->view('extension/mestech/ozon/products', $data));
    }
    
    /**
     * Sipariş yönetimi
     */
    public function orders() {
        $this->load->language('extension/mestech/ozon/ozon');
        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_orders'));
        
        $this->load->model('extension/mestech/ozon');
        
        // Filtreleme ve sayfalama parametreleri
        $filter_data = [
            'filter_order_id'       => isset($this->request->get['filter_order_id']) ? $this->request->get['filter_order_id'] : '',
            'filter_customer'       => isset($this->request->get['filter_customer']) ? $this->request->get['filter_customer'] : '',
            'filter_order_status'   => isset($this->request->get['filter_order_status']) ? $this->request->get['filter_order_status'] : '',
            'filter_date_added'     => isset($this->request->get['filter_date_added']) ? $this->request->get['filter_date_added'] : '',
            'sort'                  => isset($this->request->get['sort']) ? $this->request->get['sort'] : 'o.date_added',
            'order'                 => isset($this->request->get['order']) ? $this->request->get['order'] : 'DESC',
            'page'                  => isset($this->request->get['page']) ? $this->request->get['page'] : 1,
            'limit'                 => 10
        ];
        
        // Siparişleri yükle
        $data['orders'] = $this->model_extension_mestech_ozon->getOrders($filter_data);
        $total_orders = $this->model_extension_mestech_ozon->getTotalOrders($filter_data);
        
        // Sayfalama
        $pagination = new Pagination();
        $pagination->total = $total_orders;
        $pagination->page = $filter_data['page'];
        $pagination->limit = $filter_data['limit'];
        $pagination->url = $this->url->link('extension/mestech/ozon/orders', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);
        
        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($total_orders) ? (($filter_data['page'] - 1) * $filter_data['limit']) + 1 : 0, ((($filter_data['page'] - 1) * $filter_data['limit']) > ($total_orders - $filter_data['limit'])) ? $total_orders : ((($filter_data['page'] - 1) * $filter_data['limit']) + $filter_data['limit']), $total_orders, ceil($total_orders / $filter_data['limit']));
        
        // Başarı mesajını hazırla
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Filtreleme ve sıralama URL'leri
        $url = '';
        
        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }
        
        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }
        
        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }
        
        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }
        
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }
        
        // Sıralama URL'leri
        $data['sort_order'] = $this->url->link('extension/mestech/ozon/orders', 'user_token=' . $this->session->data['user_token'] . '&sort=o.order_id' . $url, true);
        $data['sort_customer'] = $this->url->link('extension/mestech/ozon/orders', 'user_token=' . $this->session->data['user_token'] . '&sort=customer' . $url, true);
        $data['sort_status'] = $this->url->link('extension/mestech/ozon/orders', 'user_token=' . $this->session->data['user_token'] . '&sort=order_status' . $url, true);
        $data['sort_date_added'] = $this->url->link('extension/mestech/ozon/orders', 'user_token=' . $this->session->data['user_token'] . '&sort=o.date_added' . $url, true);
        
        // Filtre değerlerini hazırla
        $data['filter_order_id'] = $filter_data['filter_order_id'];
        $data['filter_customer'] = $filter_data['filter_customer'];
        $data['filter_order_status'] = $filter_data['filter_order_status'];
        $data['filter_date_added'] = $filter_data['filter_date_added'];
        
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        
        // Sipariş durumlarını yükle
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        
        // Breadcrumbs
        $data['breadcrumbs'] = $this->getBreadcrumbs();
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_orders'),
            'href' => $this->url->link('extension/mestech/ozon/orders', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        // Butonlar ve linkler
        $data['sync_orders_url'] = $this->url->link('extension/mestech/ozon/syncOrders', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablon verilerini hazırla
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        // Şablonu yükle
        $this->response->setOutput($this->load->view('extension/mestech/ozon/orders', $data));
    }
    
    /**
     * Ürün senkronizasyonu
     */
    public function syncProducts() {
        $this->load->language('extension/mestech/ozon/ozon');
        
        $json = [];
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/ozon')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $this->load->model('extension/mestech/ozon');
            
            try {
                $result = $this->model_extension_mestech_ozon->syncProducts();
                
                if ($result['success']) {
                    $json['success'] = sprintf($this->language->get('text_success_sync_products'), $result['total']);
                } else {
                    $json['error'] = $result['message'];
                }
            } catch (Exception $e) {
                $json['error'] = $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Sipariş senkronizasyonu
     */
    public function syncOrders() {
        $this->load->language('extension/mestech/ozon/ozon');
        
        $json = [];
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/ozon')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $this->load->model('extension/mestech/ozon');
            
            try {
                $result = $this->model_extension_mestech_ozon->syncOrders();
                
                if ($result['success']) {
                    $json['success'] = sprintf($this->language->get('text_success_sync_orders'), $result['total']);
                } else {
                    $json['error'] = $result['message'];
                }
            } catch (Exception $e) {
                $json['error'] = $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Log görüntüleme
     */
    public function logs() {
        $this->load->language('extension/mestech/ozon/ozon');
        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_logs'));
        
        $this->load->model('extension/mestech/ozon');
        
        // Log türü
        $log_type = isset($this->request->get['type']) ? $this->request->get['type'] : 'all';
        
        // Logları yükle
        $data['logs'] = $this->model_extension_mestech_ozon->getLogs($log_type);
        
        // Log türlerini hazırla
        $data['log_types'] = [
            'all' => $this->language->get('text_log_all'),
            'info' => $this->language->get('text_log_info'),
            'warning' => $this->language->get('text_log_warning'),
            'error' => $this->language->get('text_log_error')
        ];
        
        $data['log_type'] = $log_type;
        
        // Breadcrumbs
        $data['breadcrumbs'] = $this->getBreadcrumbs();
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_logs'),
            'href' => $this->url->link('extension/mestech/ozon/logs', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        // Butonlar ve linkler
        $data['clear_logs_url'] = $this->url->link('extension/mestech/ozon/clearLogs', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablon verilerini hazırla
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        // Şablonu yükle
        $this->response->setOutput($this->load->view('extension/mestech/ozon/logs', $data));
    }
    
    /**
     * Log temizleme
     */
    public function clearLogs() {
        $this->load->language('extension/mestech/ozon/ozon');
        
        $json = [];
        
        if (!$this->user->hasPermission('modify', 'extension/mestech/ozon')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $this->load->model('extension/mestech/ozon');
            
            $this->model_extension_mestech_ozon->clearLogs();
            
            $json['success'] = $this->language->get('text_success_clear_logs');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Ayarları doğrulama
     */
    private function validateSettings() {
        if (!$this->user->hasPermission('modify', 'extension/mestech/ozon')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (empty($this->request->post['module_mestech_ozon_api_key'])) {
            $this->error['api_key'] = $this->language->get('error_api_key');
        }
        
        if (empty($this->request->post['module_mestech_ozon_api_secret'])) {
            $this->error['api_secret'] = $this->language->get('error_api_secret');
        }
        
        if (empty($this->request->post['module_mestech_ozon_client_id'])) {
            $this->error['client_id'] = $this->language->get('error_client_id');
        }
        
        return !$this->error;
    }
    
    /**
     * Breadcrumbs hazırlama
     */
    private function getBreadcrumbs() {
        $breadcrumbs = [];
        
        $breadcrumbs[] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        $breadcrumbs[] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        ];
        
        $breadcrumbs[] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/ozon', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        return $breadcrumbs;
    }
} 