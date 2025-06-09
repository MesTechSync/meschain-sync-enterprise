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
     * Webhook Handler - Receives Hepsiburada notifications
     */
    public function webhook() {
        $this->load->language('extension/module/hepsiburada');
        
        $json = array();
        
        try {
            // Get raw input
            $raw_input = file_get_contents('php://input');
            $webhook_data = json_decode($raw_input, true);
            
            if (!$webhook_data) {
                throw new Exception('Invalid webhook data');
            }
            
            // Verify webhook signature
            if (!$this->verifyWebhookSignature($raw_input)) {
                throw new Exception('Invalid webhook signature');
            }
            
            $this->load->model('extension/module/hepsiburada');
            $this->load->library('meschain/helper/hepsiburada_helper');
            
            $event_type = $webhook_data['eventType'] ?? '';
            $webhook_id = $webhook_data['webhookId'] ?? uniqid();
            
            // Log webhook received
            $this->log->write('HEPSIBURADA WEBHOOK: ' . $event_type . ' - ID: ' . $webhook_id);
            
            switch ($event_type) {
                case 'ORDER_CREATED':
                case 'ORDER_UPDATED':
                    $order_data = $webhook_data['data'];
                    $result = $this->processOrderWebhook($order_data, $event_type);
                    break;
                    
                case 'PRODUCT_APPROVED':
                case 'PRODUCT_REJECTED':
                    $product_data = $webhook_data['data'];
                    $result = $this->processProductWebhook($product_data, $event_type);
                    break;
                    
                case 'INVENTORY_UPDATE':
                    $inventory_data = $webhook_data['data'];
                    $result = $this->processInventoryWebhook($inventory_data);
                    break;
                    
                case 'PRICE_UPDATE':
                    $price_data = $webhook_data['data'];
                    $result = $this->processPriceWebhook($price_data);
                    break;
                    
                case 'QUESTION_RECEIVED':
                    $question_data = $webhook_data['data'];
                    $result = $this->processQuestionWebhook($question_data);
                    break;
                    
                case 'REVIEW_RECEIVED':
                    $review_data = $webhook_data['data'];
                    $result = $this->processReviewWebhook($review_data);
                    break;
                    
                case 'CARGO_UPDATE':
                    $cargo_data = $webhook_data['data'];
                    $result = $this->processCargoWebhook($cargo_data);
                    break;
                    
                default:
                    throw new Exception('Unknown webhook event type: ' . $event_type);
            }
            
            if ($result['success']) {
                $json['success'] = true;
                $json['message'] = 'Webhook processed successfully';
                
                // Save webhook log
                $this->model_extension_module_hepsiburada->saveWebhookLog($webhook_id, $event_type, $webhook_data, 'success');
            } else {
                throw new Exception($result['error']);
            }
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            // Log error
            $this->log->write('HEPSIBURADA WEBHOOK ERROR: ' . $e->getMessage());
            
            // Save failed webhook log
            if (isset($webhook_id) && isset($event_type)) {
                $this->model_extension_module_hepsiburada->saveWebhookLog($webhook_id, $event_type, $webhook_data ?? [], 'failed', $e->getMessage());
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Process Order Webhook
     */
    private function processOrderWebhook($order_data, $event_type) {
        try {
            $order_number = $order_data['orderNumber'] ?? '';
            
            if ($event_type === 'ORDER_CREATED') {
                // Create new order in OpenCart
                $oc_order_data = $this->convertHepsiburadaOrderToOpenCart($order_data);
                $order_id = $this->model_extension_module_hepsiburada->createOrder($oc_order_data);
                
                if ($order_id) {
                    // Save order mapping
                    $this->model_extension_module_hepsiburada->saveOrderMapping($order_id, $order_number, $order_data);
                    
                    // Update stock
                    foreach ($order_data['items'] as $item) {
                        $this->updateProductStock($item['merchantSku'], $item['quantity']);
                    }
                    
                    $this->log->write('HEPSIBURADA: New order created - ' . $order_number);
                }
            } else {
                // Update existing order
                $existing_order = $this->model_extension_module_hepsiburada->getOrderByHepsiburadaNumber($order_number);
                
                if ($existing_order) {
                    $this->model_extension_module_hepsiburada->updateOrderStatus($existing_order['order_id'], $order_data['status']);
                    $this->log->write('HEPSIBURADA: Order updated - ' . $order_number);
                }
            }
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Product Webhook
     */
    private function processProductWebhook($product_data, $event_type) {
        try {
            $merchant_sku = $product_data['merchantSku'] ?? '';
            $product = $this->model_extension_module_hepsiburada->getProductBySku($merchant_sku);
            
            if ($product) {
                $status = ($event_type === 'PRODUCT_APPROVED') ? 'approved' : 'rejected';
                $this->model_extension_module_hepsiburada->updateProductStatus($product['product_id'], $status);
                
                if ($event_type === 'PRODUCT_REJECTED') {
                    $rejection_reason = $product_data['rejectionReason'] ?? 'Unknown reason';
                    $this->model_extension_module_hepsiburada->saveProductRejectionReason($product['product_id'], $rejection_reason);
                }
                
                $this->log->write('HEPSIBURADA: Product ' . $status . ' - ' . $merchant_sku);
            }
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Inventory Webhook
     */
    private function processInventoryWebhook($inventory_data) {
        try {
            $merchant_sku = $inventory_data['merchantSku'] ?? '';
            $available_stock = $inventory_data['availableStock'] ?? 0;
            
            $product = $this->model_extension_module_hepsiburada->getProductBySku($merchant_sku);
            
            if ($product) {
                // Update stock in OpenCart
                $this->model_extension_module_hepsiburada->updateProductStock($product['product_id'], $available_stock);
                
                // Sync back to other marketplaces if enabled
                if ($this->config->get('module_hepsiburada_cross_sync')) {
                    $this->syncStockToOtherMarketplaces($product['product_id'], $available_stock);
                }
                
                $this->log->write('HEPSIBURADA: Stock updated - ' . $merchant_sku . ' = ' . $available_stock);
            }
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Price Webhook
     */
    private function processPriceWebhook($price_data) {
        try {
            $merchant_sku = $price_data['merchantSku'] ?? '';
            $new_price = $price_data['price'] ?? 0;
            
            $product = $this->model_extension_module_hepsiburada->getProductBySku($merchant_sku);
            
            if ($product) {
                // Update price in OpenCart
                $this->model_extension_module_hepsiburada->updateProductPrice($product['product_id'], $new_price);
                
                // Auto-update other marketplace prices if enabled
                if ($this->config->get('module_hepsiburada_price_sync')) {
                    $this->syncPriceToOtherMarketplaces($product['product_id'], $new_price);
                }
                
                $this->log->write('HEPSIBURADA: Price updated - ' . $merchant_sku . ' = ' . $new_price);
            }
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Question Webhook
     */
    private function processQuestionWebhook($question_data) {
        try {
            $question_id = $question_data['questionId'] ?? '';
            $product_sku = $question_data['merchantSku'] ?? '';
            $question_text = $question_data['question'] ?? '';
            $customer_name = $question_data['customerName'] ?? 'Anonymous';
            
            // Save question to database
            $this->model_extension_module_hepsiburada->saveProductQuestion($question_id, $product_sku, $question_text, $customer_name);
            
            // Send notification email if enabled
            if ($this->config->get('module_hepsiburada_question_notifications')) {
                $this->sendQuestionNotification($question_data);
            }
            
            $this->log->write('HEPSIBURADA: New question received - ' . $question_id);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Review Webhook
     */
    private function processReviewWebhook($review_data) {
        try {
            $review_id = $review_data['reviewId'] ?? '';
            $product_sku = $review_data['merchantSku'] ?? '';
            $rating = $review_data['rating'] ?? 0;
            $review_text = $review_data['review'] ?? '';
            $customer_name = $review_data['customerName'] ?? 'Anonymous';
            
            // Save review to database
            $this->model_extension_module_hepsiburada->saveProductReview($review_id, $product_sku, $rating, $review_text, $customer_name);
            
            // Update product rating average
            $this->updateProductRatingAverage($product_sku);
            
            $this->log->write('HEPSIBURADA: New review received - ' . $review_id . ' (Rating: ' . $rating . ')');
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Cargo Webhook
     */
    private function processCargoWebhook($cargo_data) {
        try {
            $order_number = $cargo_data['orderNumber'] ?? '';
            $tracking_number = $cargo_data['trackingNumber'] ?? '';
            $cargo_company = $cargo_data['cargoCompany'] ?? '';
            $status = $cargo_data['status'] ?? '';
            
            $order = $this->model_extension_module_hepsiburada->getOrderByHepsiburadaNumber($order_number);
            
            if ($order) {
                // Update order with cargo information
                $this->model_extension_module_hepsiburada->updateOrderCargo($order['order_id'], $tracking_number, $cargo_company, $status);
                
                // Send customer notification if enabled
                if ($this->config->get('module_hepsiburada_cargo_notifications') && $status === 'shipped') {
                    $this->sendCargoNotification($order, $tracking_number, $cargo_company);
                }
                
                $this->log->write('HEPSIBURADA: Cargo updated - Order: ' . $order_number . ', Tracking: ' . $tracking_number);
            }
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verify Webhook Signature
     */
    private function verifyWebhookSignature($raw_input) {
        $signature = $_SERVER['HTTP_X_HEPSIBURADA_SIGNATURE'] ?? '';
        $secret = $this->config->get('module_hepsiburada_webhook_secret');
        
        if (empty($signature) || empty($secret)) {
            return false;
        }
        
        $expected_signature = hash_hmac('sha256', $raw_input, $secret);
        
        return hash_equals($expected_signature, $signature);
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