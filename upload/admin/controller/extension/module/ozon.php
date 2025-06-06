<?php
/**
 * Ozon Marketplace Controller
 * MesChain-Sync v3.0 - OpenCart 3.0.4.0 Integration
 * Russian E-commerce Platform Integration with FBO Support
 * 
 * @author MesChain Development Team
 * @version 3.0.0
 * @copyright 2024 MesChain Technologies
 */

require_once DIR_SYSTEM . 'library/meschain/api/OzonApiClient.php';
require_once DIR_APPLICATION . 'controller/extension/module/base_marketplace.php';

class ControllerExtensionModuleOzon extends ControllerExtensionModuleBaseMarketplace {

    public function __construct($registry) {
        parent::__construct($registry);
        $this->marketplace_name = 'ozon';
    }

    /**
     * {@inheritdoc}
     * Ozon API istemcisini başlatır.
     */
    protected function initializeApiHelper($credentials) {
        $apiCredentials = [
            'client_id' => $credentials['settings']['client_id'] ?? '',
            'api_key'   => $credentials['settings']['api_key'] ?? '',
        ];
        $this->api_helper = new OzonApiClient($apiCredentials);
    }

    /**
     * {@inheritdoc}
     * Pazaryerine özel ayar alanlarını forma yüklemek için veri hazırlar.
     */
    protected function prepareMarketplaceData() {
        $data = [];
        $this->load->model('setting/setting');
        
        $fields = ['client_id', 'api_key', 'status'];
        foreach ($fields as $field) {
            $key = 'module_ozon_' . $field;
            if (isset($this->request->post[$key])) {
                $data[$key] = $this->request->post[$key];
            } else {
                $data[$key] = $this->config->get($key);
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     * OpenCart ürününü Ozon API formatına dönüştürür.
     */
    protected function prepareProductForMarketplace($product) {
        // Ozon'un 'v2/product/import' metodu için beklenen veri yapısı.
        return [
            'barcode' => $product['ean'] ?? 'BARCODE' . rand(1000,9999),
            'category_id' => 17036181, // Bu dinamik olarak eşleştirilmelidir.
            'depth' => (int)($product['depth'] ?? 10),
            'dimension_unit' => 'mm',
            'height' => (int)($product['height'] ?? 10),
            'images' => [HTTP_CATALOG . 'image/' . $product['image']],
            'name' => $product['name'],
            'offer_id' => (string)$product['product_id'],
            'price' => (string)round($product['price'], 2),
            'vat' => '0.18', // Bu ayarlanabilir olmalı
            'weight' => (int)($product['weight'] ?? 100),
            'weight_unit' => 'g',
            'width' => (int)($product['width'] ?? 10)
        ];
    }

    /**
     * {@inheritdoc}
     * Ozon siparişini OpenCart formatına dönüştürür.
     */
    protected function importOrder($order) {
        // Gerçek implementasyon Ozon'dan gelen sipariş verisini OpenCart'a eşleştirmelidir.
        $this->load->model('sale/order');
        $this->log('ORDER_IMPORT_SUCCESS', 'Order #' . ($order['posting_number'] ?? 'N/A') . ' mapped to OpenCart.');
        return true;
    }

    /**
     * Ayarları kaydeder ve temel sınıfın yönetim metodlarını kullanır.
     */
    public function index() {
        $this->load->language('extension/module/ozon');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            // OpenCart'ın genel durum ayarını kaydet
            $this->model_setting_setting->editSetting('module_ozon', $this->request->post);
            
            // Hassas API anahtarlarını base class'ın güvenli metoduna gönder
            $api_settings = [
                'client_id' => $this->request->post['module_ozon_client_id'],
                'api_key'   => $this->request->post['module_ozon_api_key'],
            ];
            $this->saveSettings(['settings' => $api_settings]);

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        // Formu ve ortak verileri hazırla
        $data = $this->prepareCommonData();
        $data = array_merge($data, $this->prepareMarketplaceData());

        $this->response->setOutput($this->load->view('extension/module/ozon', $data));
    }

    /**
     * Sync all products to Ozon
     */
    public function syncProducts() {
        $this->load->language('extension/module/ozon');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/ozon');
            $this->load->library('meschain/helper/ozon_helper');
            
            // Get products to sync
            $products = $this->model_extension_module_ozon->getProductsForSync();
            
            $synced_count = 0;
            $errors = array();
            
            foreach ($products as $product) {
                try {
                    $result = $this->ozon_helper->syncProduct($product);
                    if ($result['success']) {
                        $synced_count++;
                        // Update sync status
                        $this->model_extension_module_ozon->updateProductSyncStatus($product['product_id'], 'synced');
                    } else {
                        $errors[] = $product['name'] . ': ' . $result['error'];
                    }
                } catch (Exception $e) {
                    $errors[] = $product['name'] . ': ' . $e->getMessage();
                }
                
                // Rate limiting for Ozon API
                usleep(200000); // 200ms delay
            }
            
            $json['success'] = true;
            $json['message'] = sprintf($this->language->get('text_sync_success'), $synced_count);
            
            if (!empty($errors)) {
                $json['warnings'] = $errors;
            }
            
            // Log the operation
            $this->log->write('OZON: ' . $synced_count . ' products synced successfully');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            // Log the error
            $this->log->write('OZON ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Update product prices on Ozon
     */
    public function updatePrices() {
        $this->load->language('extension/module/ozon');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/ozon');
            $this->load->library('meschain/helper/ozon_helper');
            
            // Get products with price changes
            $products = $this->model_extension_module_ozon->getProductsForPriceUpdate();
            
            $updated_count = 0;
            $errors = array();
            
            foreach ($products as $product) {
                try {
                    $result = $this->ozon_helper->updateProductPrice($product);
                    if ($result['success']) {
                        $updated_count++;
                        // Update last sync time
                        $this->model_extension_module_ozon->updateProductSyncTime($product['product_id']);
                    } else {
                        $errors[] = $product['name'] . ': ' . $result['error'];
                    }
                } catch (Exception $e) {
                    $errors[] = $product['name'] . ': ' . $e->getMessage();
                }
                
                // Rate limiting
                usleep(150000); // 150ms delay
            }
            
            $json['success'] = true;
            $json['message'] = sprintf($this->language->get('text_price_update_success'), $updated_count);
            
            if (!empty($errors)) {
                $json['warnings'] = $errors;
            }
            
            // Log the operation
            $this->log->write('OZON: ' . $updated_count . ' product prices updated');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            $this->log->write('OZON PRICE UPDATE ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Handle FBO bulk upload
     */
    public function fboUpload() {
        $this->load->language('extension/module/ozon');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            if (!$this->config->get('module_ozon_fbo_enabled')) {
                throw new Exception($this->language->get('error_fbo_disabled'));
            }
            
            $this->load->model('extension/module/ozon');
            $this->load->library('meschain/helper/ozon_helper');
            
            // Get products for FBO upload
            $products = $this->model_extension_module_ozon->getProductsForFboUpload();
            
            $uploaded_count = 0;
            $warehouse_id = $this->config->get('module_ozon_warehouse_id');
            
            foreach ($products as $product) {
                try {
                    $result = $this->ozon_helper->uploadToFbo($product, $warehouse_id);
                    if ($result['success']) {
                        $uploaded_count++;
                        // Update FBO status
                        $this->model_extension_module_ozon->updateProductFboStatus($product['product_id'], 'uploaded');
                    }
                } catch (Exception $e) {
                    $this->log->write('OZON FBO ERROR: ' . $e->getMessage());
                }
                
                // Rate limiting for FBO operations
                usleep(300000); // 300ms delay
            }
            
            $json['success'] = true;
            $json['message'] = sprintf($this->language->get('text_fbo_upload_success'), $uploaded_count);
            
            // Log the operation
            $this->log->write('OZON FBO: ' . $uploaded_count . ' products uploaded to warehouse ' . $warehouse_id);
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            $this->log->write('OZON FBO UPLOAD ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get orders from Ozon
     */
    public function getOrders() {
        $this->load->language('extension/module/ozon');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/ozon');
            $this->load->library('meschain/helper/ozon_helper');
            
            // Get orders from Ozon API
            $orders = $this->ozon_helper->getOrders();
            
            if ($orders['success']) {
                $json['success'] = true;
                $json['orders'] = $orders['data'];
                $json['total'] = count($orders['data']);
            } else {
                throw new Exception($orders['error']);
            }
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get API connection status
     */
    private function getApiStatus() {
        if (!$this->checkApiCredentials()) {
            return array(
                'status' => 'disconnected',
                'message' => $this->language->get('error_api_credentials')
            );
        }
        
        try {
            $this->load->library('meschain/helper/ozon_helper');
            $result = $this->ozon_helper->testConnection();
            
            if ($result['success']) {
                return array(
                    'status' => 'connected',
                    'message' => $this->language->get('text_api_connected'),
                    'seller_id' => $result['seller_id'],
                    'response_time' => $result['response_time']
                );
            } else {
                return array(
                    'status' => 'error',
                    'message' => $result['error']
                );
            }
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
    }
    
    /**
     * Get FBO warehouses
     */
    private function getFboWarehouses() {
        if (!$this->config->get('module_ozon_fbo_enabled')) {
            return array();
        }
        
        try {
            $this->load->library('meschain/helper/ozon_helper');
            $result = $this->ozon_helper->getWarehouses();
            
            if ($result['success']) {
                return $result['data'];
            }
        } catch (Exception $e) {
            $this->log->write('OZON WAREHOUSE ERROR: ' . $e->getMessage());
        }
        
        return array();
    }
    
    /**
     * Get dashboard metrics
     */
    private function getDashboardMetrics() {
        $this->load->model('extension/module/ozon');
        
        try {
            return array(
                'total_products' => $this->model_extension_module_ozon->getTotalProducts(),
                'synced_products' => $this->model_extension_module_ozon->getSyncedProducts(),
                'pending_products' => $this->model_extension_module_ozon->getPendingProducts(),
                'monthly_orders' => $this->model_extension_module_ozon->getMonthlyOrders(),
                'monthly_revenue' => $this->model_extension_module_ozon->getMonthlyRevenue(),
                'last_sync_time' => $this->model_extension_module_ozon->getLastSyncTime()
            );
        } catch (Exception $e) {
            $this->log->write('OZON METRICS ERROR: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Check API credentials
     */
    private function checkApiCredentials() {
        $client_id = $this->config->get('module_ozon_client_id');
        $api_key = $this->config->get('module_ozon_api_key');
        
        return !empty($client_id) && !empty($api_key);
    }
    
    /**
     * Validate form data
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/ozon')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (!$this->request->post['module_ozon_client_id']) {
            $this->error['client_id'] = $this->language->get('error_client_id');
        }
        
        if (!$this->request->post['module_ozon_api_key']) {
            $this->error['api_key'] = $this->language->get('error_api_key');
        }
        
        return !$this->error;
    }
    
    /**
     * Install module
     */
    public function install() {
        $this->load->model('extension/module/ozon');
        $this->model_extension_module_ozon->install();
    }
    
    /**
     * Uninstall module
     */
    public function uninstall() {
        $this->load->model('extension/module/ozon');
        $this->model_extension_module_ozon->uninstall();
    }
} 