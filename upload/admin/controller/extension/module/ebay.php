<?php
/**
 * eBay Marketplace Controller
 * MesChain-Sync v3.0 - OpenCart 3.0.4.0 Integration
 * Global E-commerce Platform Integration with Auction System
 * 
 * @author MesChain Development Team
 * @version 3.0.0
 * @copyright 2024 MesChain Technologies
 */

class ControllerExtensionModuleEbay extends Controller {
    
    private $error = array();
    private $api_base_url = 'https://api.ebay.com';
    private $sandbox_url = 'https://api.sandbox.ebay.com';
    private $api_version = 'v1';
    
    /**
     * eBay dashboard main page
     */
    public function index() {
        $this->load->language('extension/module/ebay');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Breadcrumb navigation
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/ebay', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Save settings if form submitted
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->load->model('setting/setting');
            
            $this->model_setting_setting->editSetting('module_ebay', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }
        
        // Error handling
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->error['app_id'])) {
            $data['error_app_id'] = $this->error['app_id'];
        } else {
            $data['error_app_id'] = '';
        }
        
        if (isset($this->error['cert_id'])) {
            $data['error_cert_id'] = $this->error['cert_id'];
        } else {
            $data['error_cert_id'] = '';
        }
        
        if (isset($this->error['dev_id'])) {
            $data['error_dev_id'] = $this->error['dev_id'];
        } else {
            $data['error_dev_id'] = '';
        }
        
        if (isset($this->error['user_token'])) {
            $data['error_user_token'] = $this->error['user_token'];
        } else {
            $data['error_user_token'] = '';
        }
        
        // Form action URLs
        $data['action'] = $this->url->link('extension/module/ebay', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        // Get current settings
        if (isset($this->request->post['module_ebay_app_id'])) {
            $data['module_ebay_app_id'] = $this->request->post['module_ebay_app_id'];
        } else {
            $data['module_ebay_app_id'] = $this->config->get('module_ebay_app_id');
        }
        
        if (isset($this->request->post['module_ebay_cert_id'])) {
            $data['module_ebay_cert_id'] = $this->request->post['module_ebay_cert_id'];
        } else {
            $data['module_ebay_cert_id'] = $this->config->get('module_ebay_cert_id');
        }
        
        if (isset($this->request->post['module_ebay_dev_id'])) {
            $data['module_ebay_dev_id'] = $this->request->post['module_ebay_dev_id'];
        } else {
            $data['module_ebay_dev_id'] = $this->config->get('module_ebay_dev_id');
        }
        
        if (isset($this->request->post['module_ebay_user_token'])) {
            $data['module_ebay_user_token'] = $this->request->post['module_ebay_user_token'];
        } else {
            $data['module_ebay_user_token'] = $this->config->get('module_ebay_user_token');
        }
        
        if (isset($this->request->post['module_ebay_status'])) {
            $data['module_ebay_status'] = $this->request->post['module_ebay_status'];
        } else {
            $data['module_ebay_status'] = $this->config->get('module_ebay_status');
        }
        
        if (isset($this->request->post['module_ebay_sandbox'])) {
            $data['module_ebay_sandbox'] = $this->request->post['module_ebay_sandbox'];
        } else {
            $data['module_ebay_sandbox'] = $this->config->get('module_ebay_sandbox');
        }
        
        if (isset($this->request->post['module_ebay_site'])) {
            $data['module_ebay_site'] = $this->request->post['module_ebay_site'];
        } else {
            $data['module_ebay_site'] = $this->config->get('module_ebay_site');
        }
        
        if (isset($this->request->post['module_ebay_listing_type'])) {
            $data['module_ebay_listing_type'] = $this->request->post['module_ebay_listing_type'];
        } else {
            $data['module_ebay_listing_type'] = $this->config->get('module_ebay_listing_type');
        }
        
        if (isset($this->request->post['module_ebay_auto_paypal'])) {
            $data['module_ebay_auto_paypal'] = $this->request->post['module_ebay_auto_paypal'];
        } else {
            $data['module_ebay_auto_paypal'] = $this->config->get('module_ebay_auto_paypal');
        }
        
        if (isset($this->request->post['module_ebay_global_shipping'])) {
            $data['module_ebay_global_shipping'] = $this->request->post['module_ebay_global_shipping'];
        } else {
            $data['module_ebay_global_shipping'] = $this->config->get('module_ebay_global_shipping');
        }
        
        // Load helper for API operations
        $this->load->library('meschain/helper/ebay_helper');
        
        // Get API connection status
        $data['api_status'] = $this->getApiStatus();
        
        // Get eBay sites
        $data['ebay_sites'] = $this->getEbaySites();
        
        // Get listing types
        $data['listing_types'] = $this->getListingTypes();
        
        // Get dashboard metrics
        $data['metrics'] = $this->getDashboardMetrics();
        
        // Get payment methods
        $data['payment_methods'] = $this->getPaymentMethods();
        
        // Get shipping services
        $data['shipping_services'] = $this->getShippingServices();
        
        // Template data
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/ebay', $data));
    }
    
    /**
     * List products as Buy It Now on eBay
     */
    public function listProducts() {
        $this->load->language('extension/module/ebay');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/ebay');
            $this->load->library('meschain/helper/ebay_helper');
            
            // Get products to list
            $products = $this->model_extension_module_ebay->getProductsForListing();
            
            $listed_count = 0;
            $errors = array();
            
            foreach ($products as $product) {
                try {
                    $result = $this->ebay_helper->listProduct($product);
                    if ($result['success']) {
                        $listed_count++;
                        // Update listing status
                        $this->model_extension_module_ebay->updateProductListingStatus($product['product_id'], 'listed', $result['item_id']);
                    } else {
                        $errors[] = $product['name'] . ': ' . $result['error'];
                    }
                } catch (Exception $e) {
                    $errors[] = $product['name'] . ': ' . $e->getMessage();
                }
                
                // Rate limiting for eBay API
                usleep(500000); // 500ms delay
            }
            
            $json['success'] = true;
            $json['message'] = sprintf($this->language->get('text_listing_success'), $listed_count);
            
            if (!empty($errors)) {
                $json['warnings'] = $errors;
            }
            
            // Log the operation
            $this->log->write('EBAY: ' . $listed_count . ' products listed successfully');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            // Log the error
            $this->log->write('EBAY ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Update eBay listing prices and quantities
     */
    public function updateListings() {
        $this->load->language('extension/module/ebay');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/ebay');
            $this->load->library('meschain/helper/ebay_helper');
            
            // Get listings with changes
            $listings = $this->model_extension_module_ebay->getListingsForUpdate();
            
            $updated_count = 0;
            $errors = array();
            
            foreach ($listings as $listing) {
                try {
                    $result = $this->ebay_helper->updateListing($listing);
                    if ($result['success']) {
                        $updated_count++;
                        // Update last sync time
                        $this->model_extension_module_ebay->updateListingSyncTime($listing['item_id']);
                    } else {
                        $errors[] = $listing['title'] . ': ' . $result['error'];
                    }
                } catch (Exception $e) {
                    $errors[] = $listing['title'] . ': ' . $e->getMessage();
                }
                
                // Rate limiting
                usleep(400000); // 400ms delay
            }
            
            $json['success'] = true;
            $json['message'] = sprintf($this->language->get('text_update_success'), $updated_count);
            
            if (!empty($errors)) {
                $json['warnings'] = $errors;
            }
            
            // Log the operation
            $this->log->write('EBAY: ' . $updated_count . ' listings updated');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            $this->log->write('EBAY LISTING UPDATE ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Sync eBay orders and create in OpenCart
     */
    public function syncOrders() {
        $this->load->language('extension/module/ebay');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/ebay');
            $this->load->library('meschain/helper/ebay_helper');
            
            // Get orders from eBay
            $orders = $this->ebay_helper->getOrders();
            
            if ($orders['success']) {
                $synced_count = 0;
                $errors = array();
                
                foreach ($orders['orders'] as $ebay_order) {
                    try {
                        $result = $this->ebay_helper->createOpenCartOrder($ebay_order);
                        if ($result['success']) {
                            $synced_count++;
                            // Save order mapping
                            $this->model_extension_module_ebay->saveOrderMapping($result['order_id'], $ebay_order);
                        } else {
                            $errors[] = $ebay_order['order_id'] . ': ' . $result['error'];
                        }
                    } catch (Exception $e) {
                        $errors[] = $ebay_order['order_id'] . ': ' . $e->getMessage();
                    }
                }
                
                $json['success'] = true;
                $json['message'] = sprintf($this->language->get('text_order_sync_success'), $synced_count);
                
                if (!empty($errors)) {
                    $json['warnings'] = $errors;
                }
                
                // Log the operation
                $this->log->write('EBAY: ' . $synced_count . ' orders synced');
            } else {
                throw new Exception($orders['error']);
            }
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            $this->log->write('EBAY ORDER SYNC ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Manage promoted listings and advertising
     */
    public function managePromotedListings() {
        $this->load->language('extension/module/ebay');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/ebay');
            $this->load->library('meschain/helper/ebay_helper');
            
            // Get eligible listings for promotion
            $listings = $this->model_extension_module_ebay->getEligibleListingsForPromotion();
            
            $promoted_count = 0;
            $errors = array();
            
            foreach ($listings as $listing) {
                try {
                    $result = $this->ebay_helper->promoteListingBasedOnPerformance($listing);
                    if ($result['success']) {
                        $promoted_count++;
                        // Update promotion status
                        $this->model_extension_module_ebay->updatePromotionStatus($listing['item_id'], 'promoted');
                    } else {
                        $errors[] = $listing['title'] . ': ' . $result['error'];
                    }
                } catch (Exception $e) {
                    $errors[] = $listing['title'] . ': ' . $e->getMessage();
                }
                
                // Rate limiting
                usleep(600000); // 600ms delay
            }
            
            $json['success'] = true;
            $json['message'] = sprintf($this->language->get('text_promotion_success'), $promoted_count);
            
            if (!empty($errors)) {
                $json['warnings'] = $errors;
            }
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Manage feedback and seller performance
     */
    public function manageFeedback() {
        $this->load->language('extension/module/ebay');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/ebay');
            $this->load->library('meschain/helper/ebay_helper');
            
            // Get feedback data
            $feedback = $this->ebay_helper->getFeedbackScore();
            
            if ($feedback['success']) {
                // Update seller metrics
                $this->model_extension_module_ebay->updateSellerMetrics($feedback['data']);
                
                // Get orders eligible for feedback
                $orders = $this->model_extension_module_ebay->getOrdersForFeedback();
                
                $feedback_count = 0;
                foreach ($orders as $order) {
                    $result = $this->ebay_helper->leaveFeedback($order);
                    if ($result['success']) {
                        $feedback_count++;
                    }
                }
                
                $json['success'] = true;
                $json['message'] = sprintf($this->language->get('text_feedback_success'), $feedback_count);
                $json['feedback_score'] = $feedback['data']['score'];
                $json['feedback_percentage'] = $feedback['data']['percentage'];
            } else {
                throw new Exception($feedback['error']);
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
            $this->load->library('meschain/helper/ebay_helper');
            $result = $this->ebay_helper->testConnection();
            
            if ($result['success']) {
                return array(
                    'status' => 'connected',
                    'message' => $this->language->get('text_api_connected'),
                    'user_id' => $result['user_id'],
                    'site' => $result['site'],
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
     * Get eBay sites (global marketplaces)
     */
    private function getEbaySites() {
        return array(
            array('id' => 0, 'name' => 'eBay US (ebay.com)', 'currency' => 'USD', 'country' => 'US'),
            array('id' => 3, 'name' => 'eBay UK (ebay.co.uk)', 'currency' => 'GBP', 'country' => 'UK'),
            array('id' => 77, 'name' => 'eBay Germany (ebay.de)', 'currency' => 'EUR', 'country' => 'DE'),
            array('id' => 71, 'name' => 'eBay France (ebay.fr)', 'currency' => 'EUR', 'country' => 'FR'),
            array('id' => 101, 'name' => 'eBay Italy (ebay.it)', 'currency' => 'EUR', 'country' => 'IT'),
            array('id' => 186, 'name' => 'eBay Spain (ebay.es)', 'currency' => 'EUR', 'country' => 'ES'),
            array('id' => 15, 'name' => 'eBay Australia (ebay.com.au)', 'currency' => 'AUD', 'country' => 'AU'),
            array('id' => 2, 'name' => 'eBay Canada (ebay.ca)', 'currency' => 'CAD', 'country' => 'CA'),
            array('id' => 203, 'name' => 'eBay India (ebay.in)', 'currency' => 'INR', 'country' => 'IN'),
            array('id' => 207, 'name' => 'eBay Turkey (gittigidiyor.com)', 'currency' => 'TRY', 'country' => 'TR')
        );
    }
    
    /**
     * Get listing types
     */
    private function getListingTypes() {
        return array(
            array('type' => 'FixedPriceItem', 'name' => 'Buy It Now'),
            array('type' => 'Chinese', 'name' => 'Auction'),
            array('type' => 'StoreFixedPrice', 'name' => 'Store Inventory'),
            array('type' => 'LeadGeneration', 'name' => 'Classified Ad')
        );
    }
    
    /**
     * Get payment methods
     */
    private function getPaymentMethods() {
        return array(
            array('method' => 'PayPal', 'name' => 'PayPal'),
            array('method' => 'CreditCard', 'name' => 'Credit/Debit Card'),
            array('method' => 'BankTransfer', 'name' => 'Bank Transfer'),
            array('method' => 'Escrow', 'name' => 'Escrow'),
            array('method' => 'CashOnPickup', 'name' => 'Cash on Pickup')
        );
    }
    
    /**
     * Get shipping services
     */
    private function getShippingServices() {
        return array(
            array('service' => 'UPSGround', 'name' => 'UPS Ground', 'international' => false),
            array('service' => 'USPSPriority', 'name' => 'USPS Priority Mail', 'international' => false),
            array('service' => 'FedExHomeDelivery', 'name' => 'FedEx Home Delivery', 'international' => false),
            array('service' => 'USPSPriorityMailInternational', 'name' => 'USPS Priority Mail International', 'international' => true),
            array('service' => 'UPSWorldwideExpedited', 'name' => 'UPS Worldwide Expedited', 'international' => true),
            array('service' => 'FedExInternationalEconomy', 'name' => 'FedEx International Economy', 'international' => true),
            array('service' => 'GlobalShippingProgram', 'name' => 'eBay Global Shipping Program', 'international' => true),
            array('service' => 'StandardInternational', 'name' => 'Standard International Shipping', 'international' => true)
        );
    }
    
    /**
     * Get dashboard metrics
     */
    private function getDashboardMetrics() {
        $this->load->model('extension/module/ebay');
        
        try {
            return array(
                'total_listings' => $this->model_extension_module_ebay->getTotalListings(),
                'active_listings' => $this->model_extension_module_ebay->getActiveListings(),
                'watching_count' => $this->model_extension_module_ebay->getWatchingCount(),
                'monthly_sales' => $this->model_extension_module_ebay->getMonthlySales(),
                'monthly_fees' => $this->model_extension_module_ebay->getMonthlyFees(),
                'feedback_score' => $this->model_extension_module_ebay->getFeedbackScore(),
                'last_sync_time' => $this->model_extension_module_ebay->getLastSyncTime(),
                'promoted_listings' => $this->model_extension_module_ebay->getPromotedListings(),
                'international_sales' => $this->model_extension_module_ebay->getInternationalSales(),
                'defect_rate' => $this->model_extension_module_ebay->getDefectRate()
            );
        } catch (Exception $e) {
            $this->log->write('EBAY METRICS ERROR: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Check API credentials
     */
    private function checkApiCredentials() {
        $app_id = $this->config->get('module_ebay_app_id');
        $cert_id = $this->config->get('module_ebay_cert_id');
        $dev_id = $this->config->get('module_ebay_dev_id');
        $user_token = $this->config->get('module_ebay_user_token');
        
        return !empty($app_id) && !empty($cert_id) && !empty($dev_id) && !empty($user_token);
    }
    
    /**
     * Validate form data
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/ebay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (!$this->request->post['module_ebay_app_id']) {
            $this->error['app_id'] = $this->language->get('error_app_id');
        }
        
        if (!$this->request->post['module_ebay_cert_id']) {
            $this->error['cert_id'] = $this->language->get('error_cert_id');
        }
        
        if (!$this->request->post['module_ebay_dev_id']) {
            $this->error['dev_id'] = $this->language->get('error_dev_id');
        }
        
        if (!$this->request->post['module_ebay_user_token']) {
            $this->error['user_token'] = $this->language->get('error_user_token');
        }
        
        return !$this->error;
    }
    
    /**
     * Install module
     */
    public function install() {
        $this->load->model('extension/module/ebay');
        $this->model_extension_module_ebay->install();
    }
    
    /**
     * Uninstall module
     */
    public function uninstall() {
        $this->load->model('extension/module/ebay');
        $this->model_extension_module_ebay->uninstall();
    }
} 