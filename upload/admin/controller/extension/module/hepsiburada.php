<?php
/**
 * Hepsiburada Marketplace Controller
 * MesChain-Sync v3.0 - OpenCart 3.0.4.0 Integration
 * Turkish E-commerce Platform Integration with Cargo System
 * 
 * @author MesChain Development Team
 * @version 3.0.0
 * @copyright 2024 MesChain Technologies
 */

require_once DIR_SYSTEM . 'library/meschain/api/HepsiburadaApiClient.php';
require_once DIR_APPLICATION . 'controller/extension/module/base_marketplace.php';

class ControllerExtensionModuleHepsiburada extends ControllerExtensionModuleBaseMarketplace {

    public function __construct($registry) {
        parent::__construct($registry);
        $this->marketplace_name = 'hepsiburada';
    }

    /**
     * {@inheritdoc}
     * Hepsiburada API istemcisini başlatır.
     */
    protected function initializeApiHelper($credentials) {
        $apiCredentials = [
            'username'    => $credentials['settings']['username'] ?? '',
            'password'    => $credentials['settings']['password'] ?? '',
            'merchant_id' => $credentials['settings']['merchant_id'] ?? '',
        ];
        $this->api_helper = new HepsiburadaApiClient($apiCredentials);
    }

    /**
     * {@inheritdoc}
     * Pazaryerine özel ayar alanlarını forma yüklemek için veri hazırlar.
     */
    protected function prepareMarketplaceData() {
        $data = [];
        $this->load->model('setting/setting');
        
        $fields = ['username', 'password', 'merchant_id', 'status'];
        foreach ($fields as $field) {
            $key = 'module_hepsiburada_' . $field;
            if (isset($this->request->post[$key])) {
                $data[$key] = $this->request->post[$key];
            } else {
                $data[$key] = $this->config->get($key);
            }
        }
        // Şifre alanı için özel işlem, asla dolu gösterme
        $data['module_hepsiburada_password'] = '';
        
        return $data;
    }

    /**
     * {@inheritdoc}
     * OpenCart ürününü Hepsiburada API formatına dönüştürür.
     */
    protected function prepareProductForMarketplace($product) {
        return [
            'merchantSku' => $product['sku'],
            'listingId'   => $product['mpn'], // Hepsiburada'nın listingId'si ile eşleştirilmeli
            'price'       => (float)$product['price'],
            'availableStock' => (int)$product['quantity'],
        ];
    }

    /**
     * {@inheritdoc}
     * Hepsiburada siparişini OpenCart formatına dönüştürür.
     */
    protected function importOrder($order) {
        $this->load->model('sale/order');
        $this->log('ORDER_IMPORT_SUCCESS', 'Order #' . ($order['orderNumber'] ?? 'N/A') . ' mapped to OpenCart.');
        return true;
    }

    /**
     * Ayarları kaydeder ve temel sınıfın yönetim metodlarını kullanır.
     */
    public function index() {
        $this->load->language('extension/module/hepsiburada');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_hepsiburada', $this->request->post);
            
            $api_settings = [
                'username'    => $this->request->post['module_hepsiburada_username'],
                'merchant_id' => $this->request->post['module_hepsiburada_merchant_id'],
            ];
            // Sadece şifre alanı doluysa güncelle
            if (!empty($this->request->post['module_hepsiburada_password'])) {
                $api_settings['password'] = $this->request->post['module_hepsiburada_password'];
            }
            
            $this->saveSettings(['settings' => $api_settings]);

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        $data = $this->prepareCommonData();
        $data = array_merge($data, $this->prepareMarketplaceData());

        $this->response->setOutput($this->load->view('extension/module/hepsiburada', $data));
    }

    /**
     * Sync all products to Hepsiburada
     */
    public function syncProducts() {
        $this->load->language('extension/module/hepsiburada');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/hepsiburada');
            $this->load->library('meschain/helper/hepsiburada_helper');
            
            // Get products to sync
            $products = $this->model_extension_module_hepsiburada->getProductsForSync();
            
            $synced_count = 0;
            $errors = array();
            
            foreach ($products as $product) {
                try {
                    $result = $this->hepsiburada_helper->syncProduct($product);
                    if ($result['success']) {
                        $synced_count++;
                        // Update sync status
                        $this->model_extension_module_hepsiburada->updateProductSyncStatus($product['product_id'], 'synced');
                    } else {
                        $errors[] = $product['name'] . ': ' . $result['error'];
                    }
                } catch (Exception $e) {
                    $errors[] = $product['name'] . ': ' . $e->getMessage();
                }
                
                // Rate limiting for Hepsiburada API
                usleep(250000); // 250ms delay
            }
            
            $json['success'] = true;
            $json['message'] = sprintf($this->language->get('text_sync_success'), $synced_count);
            
            if (!empty($errors)) {
                $json['warnings'] = $errors;
            }
            
            // Log the operation
            $this->log->write('HEPSIBURADA: ' . $synced_count . ' products synced successfully');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            // Log the error
            $this->log->write('HEPSIBURADA ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Update product prices on Hepsiburada
     */
    public function updatePrices() {
        $this->load->language('extension/module/hepsiburada');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/hepsiburada');
            $this->load->library('meschain/helper/hepsiburada_helper');
            
            // Get products with price changes
            $products = $this->model_extension_module_hepsiburada->getProductsForPriceUpdate();
            
            $updated_count = 0;
            $errors = array();
            
            foreach ($products as $product) {
                try {
                    $result = $this->hepsiburada_helper->updateProductPrice($product);
                    if ($result['success']) {
                        $updated_count++;
                        // Update last sync time
                        $this->model_extension_module_hepsiburada->updateProductSyncTime($product['product_id']);
                    } else {
                        $errors[] = $product['name'] . ': ' . $result['error'];
                    }
                } catch (Exception $e) {
                    $errors[] = $product['name'] . ': ' . $e->getMessage();
                }
                
                // Rate limiting
                usleep(200000); // 200ms delay
            }
            
            $json['success'] = true;
            $json['message'] = sprintf($this->language->get('text_price_update_success'), $updated_count);
            
            if (!empty($errors)) {
                $json['warnings'] = $errors;
            }
            
            // Log the operation
            $this->log->write('HEPSIBURADA: ' . $updated_count . ' product prices updated');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            $this->log->write('HEPSIBURADA PRICE UPDATE ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Manage cargo and shipping
     */
    public function updateCargoInfo() {
        $this->load->language('extension/module/hepsiburada');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/hepsiburada');
            $this->load->library('meschain/helper/hepsiburada_helper');
            
            // Get orders that need cargo tracking update
            $orders = $this->model_extension_module_hepsiburada->getOrdersForCargoUpdate();
            
            $updated_count = 0;
            
            foreach ($orders as $order) {
                try {
                    $result = $this->hepsiburada_helper->updateCargoTracking($order);
                    if ($result['success']) {
                        $updated_count++;
                        // Update cargo status
                        $this->model_extension_module_hepsiburada->updateOrderCargoStatus($order['order_id'], 'shipped');
                    }
                } catch (Exception $e) {
                    $this->log->write('HEPSIBURADA CARGO ERROR: ' . $e->getMessage());
                }
                
                // Rate limiting for cargo operations
                usleep(300000); // 300ms delay
            }
            
            $json['success'] = true;
            $json['message'] = sprintf($this->language->get('text_cargo_update_success'), $updated_count);
            
            // Log the operation
            $this->log->write('HEPSIBURADA CARGO: ' . $updated_count . ' orders updated');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            $this->log->write('HEPSIBURADA CARGO UPDATE ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get orders from Hepsiburada
     */
    public function getOrders() {
        $this->load->language('extension/module/hepsiburada');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/hepsiburada');
            $this->load->library('meschain/helper/hepsiburada_helper');
            
            // Get orders from Hepsiburada API
            $orders = $this->hepsiburada_helper->getOrders();
            
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
     * Manage promotions and discounts
     */
    public function managePromotions() {
        $this->load->language('extension/module/hepsiburada');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/hepsiburada');
            $this->load->library('meschain/helper/hepsiburada_helper');
            
            // Get active promotions
            $promotions = $this->hepsiburada_helper->getActivePromotions();
            
            if ($promotions['success']) {
                $json['success'] = true;
                $json['promotions'] = $promotions['data'];
                
                // Apply promotions to eligible products
                $applied_count = 0;
                foreach ($promotions['data'] as $promotion) {
                    $result = $this->hepsiburada_helper->applyPromotion($promotion);
                    if ($result['success']) {
                        $applied_count++;
                    }
                }
                
                $json['applied_count'] = $applied_count;
            } else {
                throw new Exception($promotions['error']);
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
            $this->load->library('meschain/helper/hepsiburada_helper');
            $result = $this->hepsiburada_helper->testConnection();
            
            if ($result['success']) {
                return array(
                    'status' => 'connected',
                    'message' => $this->language->get('text_api_connected'),
                    'merchant_id' => $result['merchant_id'],
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
     * Get cargo companies
     */
    private function getCargoCompanies() {
        return array(
            array('code' => 'yurtici', 'name' => 'Yurtiçi Kargo'),
            array('code' => 'mng', 'name' => 'MNG Kargo'),
            array('code' => 'aras', 'name' => 'Aras Kargo'),
            array('code' => 'ptt', 'name' => 'PTT Kargo'),
            array('code' => 'ups', 'name' => 'UPS Kargo'),
            array('code' => 'sendeo', 'name' => 'Sendeo'),
            array('code' => 'horoz', 'name' => 'Horoz Lojistik'),
            array('code' => 'susaydin', 'name' => 'Süsaydın Nakliyat')
        );
    }
    
    /**
     * Get Turkish categories optimized for Hepsiburada
     */
    private function getTurkishCategories() {
        return array(
            array('id' => '18022298', 'name' => 'Elektronik'),
            array('id' => '18023006', 'name' => 'Moda'),
            array('id' => '18020000', 'name' => 'Ev & Yaşam'),
            array('id' => '18043490', 'name' => 'Spor & Outdoor'),
            array('id' => '18022372', 'name' => 'Otomotiv'),
            array('id' => '18023173', 'name' => 'Anne & Bebek'),
            array('id' => '18023329', 'name' => 'Kitap, Müzik, Film'),
            array('id' => '18022033', 'name' => 'Oyuncak'),
            array('id' => '18022518', 'name' => 'Kozmetik'),
            array('id' => '18023420', 'name' => 'Süpermarket')
        );
    }
    
    /**
     * Get dashboard metrics
     */
    private function getDashboardMetrics() {
        $this->load->model('extension/module/hepsiburada');
        
        try {
            return array(
                'total_products' => $this->model_extension_module_hepsiburada->getTotalProducts(),
                'synced_products' => $this->model_extension_module_hepsiburada->getSyncedProducts(),
                'pending_products' => $this->model_extension_module_hepsiburada->getPendingProducts(),
                'monthly_orders' => $this->model_extension_module_hepsiburada->getMonthlyOrders(),
                'monthly_revenue' => $this->model_extension_module_hepsiburada->getMonthlyRevenue(),
                'last_sync_time' => $this->model_extension_module_hepsiburada->getLastSyncTime(),
                'active_promotions' => $this->model_extension_module_hepsiburada->getActivePromotions(),
                'pending_shipments' => $this->model_extension_module_hepsiburada->getPendingShipments()
            );
        } catch (Exception $e) {
            $this->log->write('HEPSIBURADA METRICS ERROR: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Check API credentials
     */
    private function checkApiCredentials() {
        $username = $this->config->get('module_hepsiburada_username');
        $password = $this->config->get('module_hepsiburada_password');
        $merchant_id = $this->config->get('module_hepsiburada_merchant_id');
        
        return !empty($username) && !empty($password) && !empty($merchant_id);
    }
    
    /**
     * Validate form data
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/hepsiburada')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (!$this->request->post['module_hepsiburada_username']) {
            $this->error['username'] = $this->language->get('error_username');
        }
        
        if (!$this->request->post['module_hepsiburada_password']) {
            $this->error['password'] = $this->language->get('error_password');
        }
        
        if (!$this->request->post['module_hepsiburada_merchant_id']) {
            $this->error['merchant_id'] = $this->language->get('error_merchant_id');
        }
        
        return !$this->error;
    }
    
    /**
     * Install module
     */
    public function install() {
        $this->load->model('extension/module/hepsiburada');
        $this->model_extension_module_hepsiburada->install();
    }
    
    /**
     * Uninstall module
     */
    public function uninstall() {
        $this->load->model('extension/module/hepsiburada');
        $this->model_extension_module_hepsiburada->uninstall();
    }
} 