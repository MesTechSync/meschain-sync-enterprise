<?php
/**
 * Base Marketplace Controller
 * Tüm pazaryeri modülleri için ortak işlevleri sağlar
 */
abstract class ControllerExtensionModuleBaseMarketplace extends Controller {
    protected $error = array();
    protected $marketplace_name;
    protected $api_helper;
    protected $encryption;
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        parent::__construct($registry);
        
        // Şifreleme sınıfını başlat
        $this->loadEncryption();
    }
    
    /**
     * Şifreleme sınıfını yükle
     */
    protected function loadEncryption() {
        $encryption_file = DIR_SYSTEM . 'library/meschain/encryption.php';
        if (file_exists($encryption_file)) {
            require_once($encryption_file);
            $this->encryption = new MeschainEncryption();
        }
    }
    
    /**
     * Ana dashboard
     */
    public function index() {
        $this->load->language('extension/module/' . $this->marketplace_name);
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Güvenlik kontrolü
        if (!$this->validateAccess()) {
            $this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
            return;
        }
        
        // POST işlemi
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->saveSettings($this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->getCurrentUrl());
        }
        
        $data = $this->prepareCommonData();
        $data = array_merge($data, $this->prepareMarketplaceData());
        
        $this->renderView($this->marketplace_name, $data);
    }
    
    /**
     * Dashboard
     */
    public function dashboard() {
        if (!$this->checkApiCredentials()) {
            $this->session->data['error_warning'] = $this->language->get('error_api_connect');
            $this->response->redirect($this->getConfigUrl());
            return;
        }
        
        $data = $this->prepareCommonData();
        $data['statistics'] = $this->getStatistics();
        $data['recent_activities'] = $this->getRecentActivities();
        
        $this->renderView($this->marketplace_name . '_dashboard', $data);
    }
    
    /**
     * API bağlantı testi
     */
    public function test_connection() {
        $json = array();
        
        try {
            $credentials = $this->getApiCredentials();
            if (!$credentials) {
                throw new Exception($this->language->get('text_missing_keys'));
            }
            
            $this->initializeApiHelper($credentials);
            $result = $this->api_helper->testConnection();
            
            if ($result) {
                $json['success'] = true;
                $json['message'] = $this->language->get('text_test_success');
                $this->log('API_TEST', 'Bağlantı testi başarılı');
            } else {
                throw new Exception($this->language->get('text_test_failed'));
            }
        } catch (Exception $e) {
            $json['success'] = false;
            $json['message'] = $e->getMessage();
            $this->log('API_TEST_ERROR', $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Ürün senkronizasyonu
     */
    public function sync_products() {
        if (!$this->validateOperation()) {
            return;
        }
        
        try {
            $this->load->model('catalog/product');
            $products = $this->model_catalog_product->getProducts();
            
            $synced_count = 0;
            $failed_count = 0;
            
            foreach ($products as $product) {
                try {
                    $marketplace_product = $this->prepareProductForMarketplace($product);
                    $result = $this->api_helper->sendProduct($marketplace_product);
                    
                    if ($result) {
                        $synced_count++;
                        $this->saveProductMapping($product['product_id'], $result);
                    } else {
                        $failed_count++;
                    }
                } catch (Exception $e) {
                    $failed_count++;
                    $this->log('PRODUCT_SYNC_ERROR', 'Ürün #' . $product['product_id'] . ': ' . $e->getMessage());
                }
            }
            
            $message = sprintf($this->language->get('text_sync_complete'), $synced_count, $failed_count);
            $this->session->data['success'] = $message;
            $this->log('PRODUCT_SYNC', $message);
            
        } catch (Exception $e) {
            $this->session->data['error_warning'] = $e->getMessage();
            $this->log('PRODUCT_SYNC_ERROR', $e->getMessage());
        }
        
        $this->response->redirect($this->getDashboardUrl());
    }
    
    /**
     * Sipariş çekme
     */
    public function get_orders() {
        if (!$this->validateOperation()) {
            return;
        }
        
        try {
            $status = $this->request->get['status'] ?? 'New';
            $limit = $this->request->get['limit'] ?? 50;
            
            $orders = $this->api_helper->getOrders($status, $limit);
            $imported_count = 0;
            
            if ($orders) {
                foreach ($orders as $order) {
                    if ($this->importOrder($order)) {
                        $imported_count++;
                    }
                }
            }
            
            $message = sprintf($this->language->get('text_orders_imported'), $imported_count);
            $this->session->data['success'] = $message;
            $this->log('ORDER_IMPORT', $message);
            
        } catch (Exception $e) {
            $this->session->data['error_warning'] = $e->getMessage();
            $this->log('ORDER_IMPORT_ERROR', $e->getMessage());
        }
        
        $this->response->redirect($this->getDashboardUrl());
    }
    
    /**
     * Kurulum
     */
    public function install() {
        $this->load->model('extension/module/' . $this->marketplace_name);
        $model_name = 'model_extension_module_' . $this->marketplace_name;
        $this->$model_name->install();
        
        $this->log('INSTALL', $this->marketplace_name . ' modülü kuruldu');
    }
    
    /**
     * Kaldırma
     */
    public function uninstall() {
        $this->load->model('extension/module/' . $this->marketplace_name);
        $model_name = 'model_extension_module_' . $this->marketplace_name;
        $this->$model_name->uninstall();
        
        $this->log('UNINSTALL', $this->marketplace_name . ' modülü kaldırıldı');
    }
    
    // Abstract metodlar - Alt sınıflar tarafından uygulanmalı
    abstract protected function prepareMarketplaceData();
    abstract protected function prepareProductForMarketplace($product);
    abstract protected function importOrder($order);
    abstract protected function initializeApiHelper($credentials);
    
    // Yardımcı metodlar
    protected function prepareCommonData() {
        $data = array();
        
        // Dil değişkenleri
        $this->load->language('extension/module/' . $this->marketplace_name);
        $language_keys = array(
            'heading_title', 'text_edit', 'text_enabled', 'text_disabled',
            'button_save', 'button_cancel', 'button_test', 'button_sync_products',
            'button_get_orders', 'text_success'
        );
        
        foreach ($language_keys as $key) {
            $data[$key] = $this->language->get($key);
        }
        
        // URL'ler
        $data['action'] = $this->getCurrentUrl();
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        $data['dashboard_url'] = $this->getDashboardUrl();
        $data['test_connection_url'] = $this->url->link('extension/module/' . $this->marketplace_name . '/test_connection', 'user_token=' . $this->session->data['user_token'], true);
        
        // Hata ve başarı mesajları
        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
        $data['success'] = isset($this->session->data['success']) ? $this->session->data['success'] : '';
        unset($this->session->data['success']);
        
        // Breadcrumbs
        $data['breadcrumbs'] = $this->getBreadcrumbs();
        
        // Header, footer
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        return $data;
    }
    
    protected function getBreadcrumbs() {
        $breadcrumbs = array();
        
        $breadcrumbs[] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $breadcrumbs[] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );
        
        $breadcrumbs[] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->getCurrentUrl()
        );
        
        return $breadcrumbs;
    }
    
    protected function validateAccess() {
        return $this->user->hasPermission('access', 'extension/module/' . $this->marketplace_name);
    }
    
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/' . $this->marketplace_name)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        return !$this->error;
    }
    
    protected function validateOperation() {
        if (!$this->validate()) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->getDashboardUrl());
            return false;
        }
        
        if (!$this->checkApiCredentials()) {
            $this->session->data['error_warning'] = $this->language->get('error_api_connect');
            $this->response->redirect($this->getDashboardUrl());
            return false;
        }
        
        return true;
    }
    
    protected function checkApiCredentials() {
        $credentials = $this->getApiCredentials();
        return !empty($credentials);
    }
    
    /**
     * API anahtarlarını al
     */
    protected function getApiCredentials() {
        $user_id = $this->user->getId();
        $api_settings = $this->getUserApiSettings($user_id);
        
        // Şifreleme sınıfı varsa şifre çöz
        if ($this->encryption && !empty($api_settings)) {
            $api_settings = $this->encryption->decryptApiCredentials($api_settings);
        }
        
        return $api_settings;
    }
    
    /**
     * Kullanıcı API ayarlarını al
     */
    protected function getUserApiSettings($user_id) {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "user_api_settings 
            WHERE user_id = '" . (int)$user_id . "' 
            AND marketplace = '" . $this->db->escape($this->marketplace_name) . "'
        ");
        
        if ($query->num_rows) {
            return $query->row;
        }
        
        return array();
    }
    
    protected function getApiKeys() {
        // Varsayılan API anahtarları, alt sınıflar override edebilir
        return array('api_key', 'api_secret');
    }
    
    /**
     * Ayarları kaydet
     */
    protected function saveSettings($data) {
        $user_id = $this->user->getId();
        
        // API anahtarları ve hassas verileri şifrele
        if ($this->encryption) {
            $data = $this->encryption->encryptApiCredentials($data);
        }
        
        // Temizle ve filtreleme yap
        $filtered_data = array();
        foreach ($data as $key => $value) {
            // module_ önekini kaldır
            $cleaned_key = str_replace('module_' . $this->marketplace_name . '_', '', $key);
            $filtered_data[$cleaned_key] = $value;
        }
        
        // Önceki ayarları kontrol et
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "user_api_settings 
            WHERE user_id = '" . (int)$user_id . "' 
            AND marketplace = '" . $this->db->escape($this->marketplace_name) . "'
        ");
        
        if ($query->num_rows) {
            // Mevcut ayarları güncelle
            $this->db->query("
                UPDATE " . DB_PREFIX . "user_api_settings 
                SET settings = '" . $this->db->escape(json_encode($filtered_data)) . "',
                    date_modified = NOW()
                WHERE user_id = '" . (int)$user_id . "' 
                AND marketplace = '" . $this->db->escape($this->marketplace_name) . "'
            ");
        } else {
            // Yeni ayar ekle
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "user_api_settings 
                SET user_id = '" . (int)$user_id . "', 
                    marketplace = '" . $this->db->escape($this->marketplace_name) . "',
                    settings = '" . $this->db->escape(json_encode($filtered_data)) . "',
                    date_added = NOW(),
                    date_modified = NOW()
            ");
        }
        
        $this->log('SETTINGS_SAVE', 'API ayarları güncellendi');
    }
    
    protected function saveProductMapping($product_id, $marketplace_data) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . $this->marketplace_name . "_products 
            SET product_id = '" . (int)$product_id . "',
                marketplace_product_id = '" . $this->db->escape($marketplace_data['id']) . "',
                sync_status = 'synced',
                last_updated = NOW()
            ON DUPLICATE KEY UPDATE
                marketplace_product_id = '" . $this->db->escape($marketplace_data['id']) . "',
                sync_status = 'synced',
                last_updated = NOW()
        ");
    }
    
    protected function getStatistics() {
        // Varsayılan istatistikler
        return array(
            'total_products' => 0,
            'synced_products' => 0,
            'pending_orders' => 0,
            'completed_orders' => 0,
            'last_sync' => 'N/A'
        );
    }
    
    protected function getRecentActivities() {
        // Son aktiviteleri veritabanından çek
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_sync_log 
            WHERE marketplace = '" . $this->marketplace_name . "'
            ORDER BY date_added DESC 
            LIMIT 10
        ");
        
        return $query->rows;
    }
    
    protected function getCurrentUrl() {
        return $this->url->link('extension/module/' . $this->marketplace_name, 'user_token=' . $this->session->data['user_token'], true);
    }
    
    protected function getConfigUrl() {
        return $this->getCurrentUrl();
    }
    
    protected function getDashboardUrl() {
        return $this->url->link('extension/module/' . $this->marketplace_name . '/dashboard', 'user_token=' . $this->session->data['user_token'], true);
    }
    
    protected function renderView($template, $data) {
        $this->response->setOutput($this->load->view('extension/module/' . $template, $data));
    }
    
    protected function log($action, $message) {
        // Merkezi loglama
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_sync_log SET
                user_id = '" . (int)$this->user->getId() . "',
                marketplace = '" . $this->marketplace_name . "',
                action = '" . $this->db->escape($action) . "',
                status = 'info',
                message = '" . $this->db->escape($message) . "',
                date_added = NOW()
        ");
        
        // Dosya logu
        $log = new Log($this->marketplace_name . '.log');
        $log->write('[' . $this->user->getUserName() . '] [' . $action . '] ' . $message);
    }
} 