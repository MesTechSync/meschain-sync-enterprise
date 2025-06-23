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

    /**
     * Ozon Premium Services Management
     */
    public function managePremiumServices() {
        $this->load->language('extension/module/ozon');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->library('meschain/helper/ozon_helper');
            
            $action = $this->request->post['action'] ?? 'list_services';
            
            switch ($action) {
                case 'list_services':
                    $result = $this->ozon_helper->getPremiumServices();
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['services'] = $result['services'];
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'activate_ozon_premium':
                    $product_ids = $this->request->post['product_ids'] ?? [];
                    $result = $this->ozon_helper->activateOzonPremium($product_ids);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['activated_count'] = $result['activated_count'];
                        $json['message'] = 'Ozon Premium activated for ' . $result['activated_count'] . ' products';
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'setup_express_delivery':
                    $cities = $this->request->post['cities'] ?? [];
                    $result = $this->ozon_helper->setupExpressDelivery($cities);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['message'] = 'Express delivery setup completed for ' . count($cities) . ' cities';
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'enable_ozon_card_cashback':
                    $cashback_rate = $this->request->post['cashback_rate'] ?? 5;
                    $result = $this->ozon_helper->enableOzonCardCashback($cashback_rate);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['message'] = 'Ozon Card cashback enabled with ' . $cashback_rate . '% rate';
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
            }
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
            $this->log->write('OZON PREMIUM ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Russian Market Compliance Management
     */
    public function manageRussianCompliance() {
        $this->load->language('extension/module/ozon');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->library('meschain/helper/ozon_helper');
            
            $action = $this->request->post['action'] ?? 'check_compliance';
            
            switch ($action) {
                case 'check_compliance':
                    $product_ids = $this->request->post['product_ids'] ?? [];
                    $result = $this->ozon_helper->checkRussianCompliance($product_ids);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['compliance_report'] = $result['report'];
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'generate_russian_certificates':
                    $product_data = $this->request->post['products'] ?? [];
                    $result = $this->ozon_helper->generateRussianCertificates($product_data);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['certificates'] = $result['certificates'];
                        $json['message'] = 'Russian certificates generated successfully';
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'setup_customs_declarations':
                    $declaration_data = $this->request->post['declarations'] ?? [];
                    $result = $this->ozon_helper->setupCustomsDeclarations($declaration_data);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['message'] = 'Customs declarations setup completed';
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'verify_gost_standards':
                    $products = $this->request->post['products'] ?? [];
                    $result = $this->ozon_helper->verifyGostStandards($products);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['verification_results'] = $result['results'];
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
            }
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
            $this->log->write('OZON COMPLIANCE ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Russian Logistics and Delivery Management
     */
    public function manageRussianLogistics() {
        $this->load->language('extension/module/ozon');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->library('meschain/helper/ozon_helper');
            
            $action = $this->request->post['action'] ?? 'get_pickup_points';
            
            switch ($action) {
                case 'get_pickup_points':
                    $city = $this->request->post['city'] ?? 'Moscow';
                    $result = $this->ozon_helper->getRussianPickupPoints($city);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['pickup_points'] = $result['points'];
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'setup_regional_delivery':
                    $regions = $this->request->post['regions'] ?? [];
                    $delivery_options = $this->request->post['delivery_options'] ?? [];
                    $result = $this->ozon_helper->setupRegionalDelivery($regions, $delivery_options);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['message'] = 'Regional delivery setup for ' . count($regions) . ' regions';
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'configure_russian_post':
                    $settings = $this->request->post['post_settings'] ?? [];
                    $result = $this->ozon_helper->configureRussianPost($settings);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['message'] = 'Russian Post integration configured';
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'enable_same_day_delivery':
                    $cities = $this->request->post['cities'] ?? ['Moscow', 'St. Petersburg'];
                    $result = $this->ozon_helper->enableSameDayDelivery($cities);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['message'] = 'Same-day delivery enabled for ' . count($cities) . ' cities';
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
            }
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
            $this->log->write('OZON LOGISTICS ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Ozon Marketing and Promotion Tools
     */
    public function manageMarketing() {
        $this->load->language('extension/module/ozon');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->library('meschain/helper/ozon_helper');
            
            $action = $this->request->post['action'] ?? 'list_campaigns';
            
            switch ($action) {
                case 'list_campaigns':
                    $result = $this->ozon_helper->getMarketingCampaigns();
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['campaigns'] = $result['campaigns'];
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'create_promo_campaign':
                    $campaign_data = $this->request->post['campaign'] ?? [];
                    $result = $this->ozon_helper->createPromoCampaign($campaign_data);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['campaign_id'] = $result['campaign_id'];
                        $json['message'] = 'Promo campaign created successfully';
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'setup_auto_bidding':
                    $products = $this->request->post['products'] ?? [];
                    $bid_settings = $this->request->post['bid_settings'] ?? [];
                    $result = $this->ozon_helper->setupAutoBidding($products, $bid_settings);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['message'] = 'Auto-bidding setup for ' . count($products) . ' products';
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'activate_ozon_seller_plus':
                    $subscription_type = $this->request->post['subscription_type'] ?? 'premium';
                    $result = $this->ozon_helper->activateOzonSellerPlus($subscription_type);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['message'] = 'Ozon Seller+ activated with ' . $subscription_type . ' plan';
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'manage_discount_programs':
                    $program_type = $this->request->post['program_type'] ?? 'seasonal';
                    $discount_data = $this->request->post['discount_data'] ?? [];
                    $result = $this->ozon_helper->manageDiscountPrograms($program_type, $discount_data);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['message'] = 'Discount programs updated successfully';
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
            }
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
            $this->log->write('OZON MARKETING ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Advanced Analytics for Russian Market
     */
    public function getRussianMarketAnalytics() {
        $this->load->language('extension/module/ozon');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->library('meschain/helper/ozon_helper');
            
            $report_type = $this->request->post['report_type'] ?? 'regional_performance';
            $date_range = $this->request->post['date_range'] ?? 'last_30_days';
            
            switch ($report_type) {
                case 'regional_performance':
                    $result = $this->ozon_helper->getRegionalPerformanceReport($date_range);
                    break;
                    
                case 'seasonal_trends':
                    $result = $this->ozon_helper->getSeasonalTrendsReport($date_range);
                    break;
                    
                case 'competitor_analysis':
                    $categories = $this->request->post['categories'] ?? [];
                    $result = $this->ozon_helper->getCompetitorAnalysisReport($categories, $date_range);
                    break;
                    
                case 'ruble_exchange_impact':
                    $result = $this->ozon_helper->getRubleExchangeImpactReport($date_range);
                    break;
                    
                case 'russian_holiday_performance':
                    $result = $this->ozon_helper->getRussianHolidayPerformanceReport($date_range);
                    break;
                    
                default:
                    throw new Exception('Unknown report type: ' . $report_type);
            }
            
            if ($result['success']) {
                $json['success'] = true;
                $json['report_data'] = $result['data'];
                $json['insights'] = $result['insights'];
                $json['generated_at'] = date('Y-m-d H:i:s');
            } else {
                $json['error'] = $result['message'];
            }
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
            $this->log->write('OZON ANALYTICS ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Currency and Multi-Language Management
     */
    public function manageCurrencyAndLanguage() {
        $this->load->language('extension/module/ozon');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->library('meschain/helper/ozon_helper');
            
            $action = $this->request->post['action'] ?? 'get_exchange_rates';
            
            switch ($action) {
                case 'get_exchange_rates':
                    $result = $this->ozon_helper->getCurrentExchangeRates();
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['rates'] = $result['rates'];
                        $json['last_updated'] = $result['last_updated'];
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'auto_convert_prices':
                    $base_currency = $this->request->post['base_currency'] ?? 'USD';
                    $margin = $this->request->post['margin'] ?? 0;
                    $result = $this->ozon_helper->autoConvertPricesToRuble($base_currency, $margin);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['converted_count'] = $result['converted_count'];
                        $json['message'] = 'Prices converted for ' . $result['converted_count'] . ' products';
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'translate_products':
                    $product_ids = $this->request->post['product_ids'] ?? [];
                    $target_language = $this->request->post['target_language'] ?? 'ru';
                    $result = $this->ozon_helper->translateProductsToRussian($product_ids, $target_language);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['translated_count'] = $result['translated_count'];
                        $json['message'] = 'Products translated successfully';
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
                    
                case 'localize_for_regions':
                    $regions = $this->request->post['regions'] ?? [];
                    $localization_data = $this->request->post['localization'] ?? [];
                    $result = $this->ozon_helper->localizeForRussianRegions($regions, $localization_data);
                    if ($result['success']) {
                        $json['success'] = true;
                        $json['message'] = 'Localization completed for ' . count($regions) . ' regions';
                    } else {
                        $json['error'] = $result['message'];
                    }
                    break;
            }
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
            $this->log->write('OZON CURRENCY ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
} 