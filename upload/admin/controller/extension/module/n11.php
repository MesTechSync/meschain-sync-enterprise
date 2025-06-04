<?php
/**
 * N11 Marketplace Controller
 * MesChain-Sync v3.0 - OpenCart 3.0.4.0 Integration
 * Turkish E-commerce Platform Integration with N11 Pro Features
 * 
 * @author MesChain Development Team
 * @version 3.0.0
 * @copyright 2024 MesChain Technologies
 */

class ControllerExtensionModuleN11 extends Controller {
    
    private $error = array();
    private $api_base_url = 'https://www.n11.com/ws/';
    private $api_version = '1.0';
    
    /**
     * N11 dashboard main page
     */
    public function index() {
        $this->load->language('extension/module/n11');
        
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
            'href' => $this->url->link('extension/module/n11', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Save settings if form submitted
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->load->model('setting/setting');
            
            $this->model_setting_setting->editSetting('module_n11', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }
        
        // Error handling
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->error['api_key'])) {
            $data['error_api_key'] = $this->error['api_key'];
        } else {
            $data['error_api_key'] = '';
        }
        
        if (isset($this->error['secret_key'])) {
            $data['error_secret_key'] = $this->error['secret_key'];
        } else {
            $data['error_secret_key'] = '';
        }
        
        if (isset($this->error['store_key'])) {
            $data['error_store_key'] = $this->error['store_key'];
        } else {
            $data['error_store_key'] = '';
        }
        
        // Form action URLs
        $data['action'] = $this->url->link('extension/module/n11', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        // Get current settings
        if (isset($this->request->post['module_n11_api_key'])) {
            $data['module_n11_api_key'] = $this->request->post['module_n11_api_key'];
        } else {
            $data['module_n11_api_key'] = $this->config->get('module_n11_api_key');
        }
        
        if (isset($this->request->post['module_n11_secret_key'])) {
            $data['module_n11_secret_key'] = $this->request->post['module_n11_secret_key'];
        } else {
            $data['module_n11_secret_key'] = $this->config->get('module_n11_secret_key');
        }
        
        if (isset($this->request->post['module_n11_store_key'])) {
            $data['module_n11_store_key'] = $this->request->post['module_n11_store_key'];
        } else {
            $data['module_n11_store_key'] = $this->config->get('module_n11_store_key');
        }
        
        if (isset($this->request->post['module_n11_status'])) {
            $data['module_n11_status'] = $this->request->post['module_n11_status'];
        } else {
            $data['module_n11_status'] = $this->config->get('module_n11_status');
        }
        
        if (isset($this->request->post['module_n11_pro_seller'])) {
            $data['module_n11_pro_seller'] = $this->request->post['module_n11_pro_seller'];
        } else {
            $data['module_n11_pro_seller'] = $this->config->get('module_n11_pro_seller');
        }
        
        if (isset($this->request->post['module_n11_auto_campaign'])) {
            $data['module_n11_auto_campaign'] = $this->request->post['module_n11_auto_campaign'];
        } else {
            $data['module_n11_auto_campaign'] = $this->config->get('module_n11_auto_campaign');
        }
        
        if (isset($this->request->post['module_n11_commission_rate'])) {
            $data['module_n11_commission_rate'] = $this->request->post['module_n11_commission_rate'];
        } else {
            $data['module_n11_commission_rate'] = $this->config->get('module_n11_commission_rate');
        }
        
        if (isset($this->request->post['module_n11_price_markup'])) {
            $data['module_n11_price_markup'] = $this->request->post['module_n11_price_markup'];
        } else {
            $data['module_n11_price_markup'] = $this->config->get('module_n11_price_markup');
        }
        
        if (isset($this->request->post['module_n11_auto_discount'])) {
            $data['module_n11_auto_discount'] = $this->request->post['module_n11_auto_discount'];
        } else {
            $data['module_n11_auto_discount'] = $this->config->get('module_n11_auto_discount');
        }
        
        if (isset($this->request->post['module_n11_cargo_company'])) {
            $data['module_n11_cargo_company'] = $this->request->post['module_n11_cargo_company'];
        } else {
            $data['module_n11_cargo_company'] = $this->config->get('module_n11_cargo_company');
        }
        
        // Load helper for API operations
        $this->load->library('meschain/helper/n11_helper');
        
        // Get API connection status
        $data['api_status'] = $this->getApiStatus();
        
        // Get N11 categories
        $data['n11_categories'] = $this->getN11Categories();
        
        // Get cargo companies
        $data['cargo_companies'] = $this->getCargoCompanies();
        
        // Get dashboard metrics
        $data['metrics'] = $this->getDashboardMetrics();
        
        // Get N11 Pro features
        $data['pro_features'] = $this->getProFeatures();
        
        // Template data
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/n11', $data));
    }
    
    /**
     * List products on N11
     */
    public function listProducts() {
        $this->load->language('extension/module/n11');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/n11');
            $this->load->library('meschain/helper/n11_helper');
            
            // Get products to list
            $products = $this->model_extension_module_n11->getProductsForListing();
            
            $listed_count = 0;
            $errors = array();
            
            foreach ($products as $product) {
                try {
                    // Optimize for Turkish market
                    $result = $this->n11_helper->listProductWithTurkishOptimization($product);
                    if ($result['success']) {
                        $listed_count++;
                        // Update listing status
                        $this->model_extension_module_n11->updateProductListingStatus($product['product_id'], 'listed', $result['product_id']);
                        
                        // Auto-create campaigns for N11 Pro sellers
                        if ($this->config->get('module_n11_pro_seller') && $this->config->get('module_n11_auto_campaign')) {
                            $this->n11_helper->createAutoCampaign($result['product_id'], $product);
                        }
                    } else {
                        $errors[] = $product['name'] . ': ' . $result['error'];
                    }
                } catch (Exception $e) {
                    $errors[] = $product['name'] . ': ' . $e->getMessage();
                }
                
                // Rate limiting for N11 API
                usleep(250000); // 250ms delay
            }
            
            $json['success'] = true;
            $json['message'] = sprintf($this->language->get('text_listing_success'), $listed_count);
            
            if (!empty($errors)) {
                $json['warnings'] = $errors;
            }
            
            // Log the operation
            $this->log->write('N11: ' . $listed_count . ' products listed successfully');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            // Log the error
            $this->log->write('N11 ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Update N11 product listings
     */
    public function updateListings() {
        $this->load->language('extension/module/n11');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/n11');
            $this->load->library('meschain/helper/n11_helper');
            
            // Get listings with changes
            $listings = $this->model_extension_module_n11->getListingsForUpdate();
            
            $updated_count = 0;
            $errors = array();
            
            foreach ($listings as $listing) {
                try {
                    $result = $this->n11_helper->updateProductWithPsychologicalPricing($listing);
                    if ($result['success']) {
                        $updated_count++;
                        // Update last sync time
                        $this->model_extension_module_n11->updateListingSyncTime($listing['n11_product_id']);
                        
                        // Update auto discounts if enabled
                        if ($this->config->get('module_n11_auto_discount')) {
                            $this->n11_helper->updateAutoDiscounts($listing['n11_product_id'], $listing);
                        }
                    } else {
                        $errors[] = $listing['title'] . ': ' . $result['error'];
                    }
                } catch (Exception $e) {
                    $errors[] = $listing['title'] . ': ' . $e->getMessage();
                }
                
                // Rate limiting
                usleep(200000); // 200ms delay
            }
            
            $json['success'] = true;
            $json['message'] = sprintf($this->language->get('text_update_success'), $updated_count);
            
            if (!empty($errors)) {
                $json['warnings'] = $errors;
            }
            
            // Log the operation
            $this->log->write('N11: ' . $updated_count . ' listings updated');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            $this->log->write('N11 LISTING UPDATE ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Sync N11 orders and create in OpenCart
     */
    public function syncOrders() {
        $this->load->language('extension/module/n11');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/n11');
            $this->load->library('meschain/helper/n11_helper');
            
            // Get orders from N11
            $orders = $this->n11_helper->getOrdersWithCargoTracking();
            
            if ($orders['success']) {
                $synced_count = 0;
                $errors = array();
                
                foreach ($orders['orders'] as $n11_order) {
                    try {
                        $result = $this->n11_helper->createOpenCartOrderWithTurkishData($n11_order);
                        if ($result['success']) {
                            $synced_count++;
                            // Save order mapping
                            $this->model_extension_module_n11->saveOrderMapping($result['order_id'], $n11_order);
                            
                            // Auto-process cargo if available
                            if (!empty($n11_order['cargo_tracking_number'])) {
                                $this->n11_helper->updateCargoTracking($result['order_id'], $n11_order['cargo_tracking_number']);
                            }
                        } else {
                            $errors[] = $n11_order['order_number'] . ': ' . $result['error'];
                        }
                    } catch (Exception $e) {
                        $errors[] = $n11_order['order_number'] . ': ' . $e->getMessage();
                    }
                }
                
                $json['success'] = true;
                $json['message'] = sprintf($this->language->get('text_order_sync_success'), $synced_count);
                
                if (!empty($errors)) {
                    $json['warnings'] = $errors;
                }
                
                // Log the operation
                $this->log->write('N11: ' . $synced_count . ' orders synced');
            } else {
                throw new Exception($orders['error']);
            }
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            $this->log->write('N11 ORDER SYNC ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Manage N11 campaigns and discounts
     */
    public function manageCampaigns() {
        $this->load->language('extension/module/n11');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            $this->load->model('extension/module/n11');
            $this->load->library('meschain/helper/n11_helper');
            
            // Get eligible products for campaigns
            $products = $this->model_extension_module_n11->getEligibleProductsForCampaigns();
            
            $campaign_count = 0;
            $errors = array();
            
            foreach ($products as $product) {
                try {
                    // Create targeted campaigns based on performance
                    $result = $this->n11_helper->createPerformanceBasedCampaign($product);
                    if ($result['success']) {
                        $campaign_count++;
                        // Update campaign status
                        $this->model_extension_module_n11->updateCampaignStatus($product['n11_product_id'], 'active', $result['campaign_id']);
                    } else {
                        $errors[] = $product['title'] . ': ' . $result['error'];
                    }
                } catch (Exception $e) {
                    $errors[] = $product['title'] . ': ' . $e->getMessage();
                }
                
                // Rate limiting
                usleep(300000); // 300ms delay
            }
            
            $json['success'] = true;
            $json['message'] = sprintf($this->language->get('text_campaign_success'), $campaign_count);
            
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
     * Manage N11 Pro seller features
     */
    public function manageProFeatures() {
        $this->load->language('extension/module/n11');
        
        $json = array();
        
        try {
            if (!$this->checkApiCredentials()) {
                throw new Exception($this->language->get('error_api_credentials'));
            }
            
            if (!$this->config->get('module_n11_pro_seller')) {
                throw new Exception($this->language->get('error_pro_required'));
            }
            
            $this->load->model('extension/module/n11');
            $this->load->library('meschain/helper/n11_helper');
            
            // Get Pro seller data
            $pro_data = $this->n11_helper->getProSellerData();
            
            if ($pro_data['success']) {
                // Update seller performance metrics
                $this->model_extension_module_n11->updateSellerMetrics($pro_data['data']);
                
                // Optimize listings for Pro features
                $optimized_count = $this->n11_helper->optimizeForProSeller();
                
                $json['success'] = true;
                $json['message'] = sprintf($this->language->get('text_pro_optimization_success'), $optimized_count);
                $json['pro_score'] = $pro_data['data']['pro_score'];
                $json['commission_rate'] = $pro_data['data']['commission_rate'];
            } else {
                throw new Exception($pro_data['error']);
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
            $this->load->library('meschain/helper/n11_helper');
            $result = $this->n11_helper->testConnection();
            
            if ($result['success']) {
                return array(
                    'status' => 'connected',
                    'message' => $this->language->get('text_api_connected'),
                    'store_name' => $result['store_name'],
                    'pro_status' => $result['pro_status'],
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
     * Get N11 categories
     */
    private function getN11Categories() {
        return array(
            array('id' => 1000, 'name' => 'Elektronik', 'commission' => 8),
            array('id' => 1001, 'name' => 'Bilgisayar', 'commission' => 6),
            array('id' => 1002, 'name' => 'Cep Telefonu', 'commission' => 7),
            array('id' => 1003, 'name' => 'Moda - Giyim', 'commission' => 12),
            array('id' => 1004, 'name' => 'Ayakkabı & Çanta', 'commission' => 14),
            array('id' => 1005, 'name' => 'Ev & Yaşam', 'commission' => 10),
            array('id' => 1006, 'name' => 'Kozmetik', 'commission' => 15),
            array('id' => 1007, 'name' => 'Anne & Bebek', 'commission' => 9),
            array('id' => 1008, 'name' => 'Spor & Outdoor', 'commission' => 11),
            array('id' => 1009, 'name' => 'Kitap & Müzik', 'commission' => 5),
            array('id' => 1010, 'name' => 'Otomotiv', 'commission' => 8),
            array('id' => 1011, 'name' => 'Bahçe & Yapı Market', 'commission' => 7)
        );
    }
    
    /**
     * Get cargo companies for Turkey
     */
    private function getCargoCompanies() {
        return array(
            array('id' => 1, 'name' => 'Yurtiçi Kargo', 'code' => 'yurtici', 'tracking_url' => 'https://www.yurticikargo.com/tr/online-takip'),
            array('id' => 2, 'name' => 'MNG Kargo', 'code' => 'mng', 'tracking_url' => 'https://www.mngkargo.com.tr/Track/Shipment'),
            array('id' => 3, 'name' => 'Aras Kargo', 'code' => 'aras', 'tracking_url' => 'https://www.araskargo.com.tr/takip'),
            array('id' => 4, 'name' => 'PTT Kargo', 'code' => 'ptt', 'tracking_url' => 'https://gonderitakip.ptt.gov.tr'),
            array('id' => 5, 'name' => 'UPS Kargo', 'code' => 'ups', 'tracking_url' => 'https://www.ups.com/track'),
            array('id' => 6, 'name' => 'Sendeo', 'code' => 'sendeo', 'tracking_url' => 'https://www.sendeo.com/takip'),
            array('id' => 7, 'name' => 'HepsiJet', 'code' => 'hepsijet', 'tracking_url' => 'https://www.hepsijet.com/takip'),
            array('id' => 8, 'name' => 'Sürat Kargo', 'code' => 'surat', 'tracking_url' => 'https://www.suratkargo.com.tr/takip')
        );
    }
    
    /**
     * Get N11 Pro features
     */
    private function getProFeatures() {
        return array(
            array('feature' => 'commission_discount', 'name' => 'Komisyon İndirimi', 'benefit' => 'En fazla %30 komisyon indirimi'),
            array('feature' => 'priority_listing', 'name' => 'Öncelikli Listeleme', 'benefit' => 'Arama sonuçlarında üst sıralarda görünme'),
            array('feature' => 'advanced_analytics', 'name' => 'Gelişmiş Analitik', 'benefit' => 'Detaylı satış ve performans raporları'),
            array('feature' => 'campaign_tools', 'name' => 'Kampanya Araçları', 'benefit' => 'Özel indirim ve promosyon oluşturma'),
            array('feature' => 'bulk_operations', 'name' => 'Toplu İşlemler', 'benefit' => 'Çoklu ürün güncelleme ve yönetimi'),
            array('feature' => 'api_integration', 'name' => 'API Entegrasyonu', 'benefit' => 'Otomatik stok ve fiyat senkronizasyonu'),
            array('feature' => 'dedicated_support', 'name' => 'Özel Destek', 'benefit' => '7/24 öncelikli müşteri desteği'),
            array('feature' => 'early_payment', 'name' => 'Erken Ödeme', 'benefit' => 'Satış sonrası 1 günde ödeme alma')
        );
    }
    
    /**
     * Get dashboard metrics
     */
    private function getDashboardMetrics() {
        $this->load->model('extension/module/n11');
        
        try {
            return array(
                'total_listings' => $this->model_extension_module_n11->getTotalListings(),
                'active_listings' => $this->model_extension_module_n11->getActiveListings(),
                'monthly_sales' => $this->model_extension_module_n11->getMonthlySales(),
                'monthly_commission' => $this->model_extension_module_n11->getMonthlyCommission(),
                'average_rating' => $this->model_extension_module_n11->getAverageRating(),
                'total_orders' => $this->model_extension_module_n11->getTotalOrders(),
                'last_sync_time' => $this->model_extension_module_n11->getLastSyncTime(),
                'active_campaigns' => $this->model_extension_module_n11->getActiveCampaigns(),
                'pro_score' => $this->model_extension_module_n11->getProScore(),
                'commission_rate' => $this->model_extension_module_n11->getCurrentCommissionRate()
            );
        } catch (Exception $e) {
            $this->log->write('N11 METRICS ERROR: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Check API credentials
     */
    private function checkApiCredentials() {
        $api_key = $this->config->get('module_n11_api_key');
        $secret_key = $this->config->get('module_n11_secret_key');
        $store_key = $this->config->get('module_n11_store_key');
        
        return !empty($api_key) && !empty($secret_key) && !empty($store_key);
    }
    
    /**
     * Validate form data
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/n11')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (!$this->request->post['module_n11_api_key']) {
            $this->error['api_key'] = $this->language->get('error_api_key');
        }
        
        if (!$this->request->post['module_n11_secret_key']) {
            $this->error['secret_key'] = $this->language->get('error_secret_key');
        }
        
        if (!$this->request->post['module_n11_store_key']) {
            $this->error['store_key'] = $this->language->get('error_store_key');
        }
        
        return !$this->error;
    }
    
    /**
     * Install module
     */
    public function install() {
        $this->load->model('extension/module/n11');
        $this->model_extension_module_n11->install();
    }
    
    /**
     * Uninstall module
     */
    public function uninstall() {
        $this->load->model('extension/module/n11');
        $this->model_extension_module_n11->uninstall();
    }
} 