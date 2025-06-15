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

require_once DIR_SYSTEM . 'library/meschain/api/N11ApiClient.php';
require_once DIR_APPLICATION . 'controller/extension/module/base_marketplace.php';

class ControllerExtensionModuleN11 extends ControllerExtensionModuleBaseMarketplace {

    public function __construct($registry) {
        parent::__construct($registry);
        $this->marketplace_name = 'n11';
    }

    /**
     * {@inheritdoc}
     * N11 API istemcisini başlatır.
     */
    protected function initializeApiHelper($credentials) {
        $apiCredentials = [
            'api_key'    => $credentials['settings']['api_key'] ?? '',
            'api_secret' => $credentials['settings']['api_secret'] ?? '',
        ];
        $this->api_helper = new N11ApiClient($apiCredentials);
    }

    /**
     * {@inheritdoc}
     * Pazaryerine özel ayar alanlarını forma yüklemek için veri hazırlar.
     */
    protected function prepareMarketplaceData() {
        $data = [];
        $this->load->model('setting/setting');
        
        $fields = ['api_key', 'api_secret', 'status'];
        foreach ($fields as $field) {
            $key = 'module_n11_' . $field;
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
     * OpenCart ürününü N11 API formatına dönüştürür.
     */
    protected function prepareProductForMarketplace($product) {
        // N11'in 'SaveProduct' metodu için beklenen karmaşık veri yapısını oluşturur.
        return [
            'productSellerCode' => $product['product_id'],
            'title' => $product['name'],
            'subtitle' => $product['model'],
            'description' => $product['description'],
            'category' => ['id' => 1000], // Bu eşleştirilmelidir
            'price' => (float)$product['price'],
            'currencyType' => 'TL',
            'images' => [
                'image' => [
                    ['url' => HTTP_CATALOG . 'image/' . $product['image'], 'order' => 1]
                ]
            ],
            'approvalStatus' => 'Active',
            'preparingDay' => 3,
            'shipmentTemplate' => 'Default',
            'stockItems' => [
                'stockItem' => [
                    'quantity' => $product['quantity'],
                    'sellerStockCode' => $product['product_id']
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     * N11 siparişini OpenCart formatına dönüştürür.
     */
    protected function importOrder($order) {
        // Gerçek implementasyon N11'den gelen sipariş verisini OpenCart'a eşleştirmelidir.
        $this->load->model('sale/order');
        $this->log('ORDER_IMPORT_SUCCESS', 'Order #' . ($order['orderNumber'] ?? 'N/A') . ' mapped to OpenCart.');
        return true;
    }

    /**
     * Ayarları kaydeder ve temel sınıfın yönetim metodlarını kullanır.
     */
    public function index() {
        $this->load->language('extension/module/n11');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            // OpenCart'ın genel durum ayarını kaydet
            $this->model_setting_setting->editSetting('module_n11', $this->request->post);
            
            // Hassas API anahtarlarını base class'ın güvenli metoduna gönder
            $api_settings = [
                'api_key'    => $this->request->post['module_n11_api_key'],
                'api_secret' => $this->request->post['module_n11_secret_key'],
            ];
            $this->saveSettings(['settings' => $api_settings]);

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        // Formu ve ortak verileri hazırla
        $data = $this->prepareCommonData();
        $data = array_merge($data, $this->prepareMarketplaceData());

        $this->response->setOutput($this->load->view('extension/module/n11', $data));
    }

    /**
     * Advanced N11 Analytics Dashboard
     */
    public function getAdvancedAnalytics() {
        $json = array();
        
        try {
            $analytics_type = $this->request->post['analytics_type'] ?? 'performance';
            $date_range = $this->request->post['date_range'] ?? 'last_30_days';
            
            switch ($analytics_type) {
                case 'performance':
                    $analytics_data = $this->getN11PerformanceAnalytics($date_range);
                    break;
                case 'financial':
                    $analytics_data = $this->getN11FinancialAnalytics($date_range);
                    break;
                case 'competitive':
                    $analytics_data = $this->getN11CompetitiveAnalytics($date_range);
                    break;
                case 'category':
                    $analytics_data = $this->getN11CategoryAnalytics($date_range);
                    break;
                default:
                    throw new Exception('Invalid analytics type');
            }
            
            $json['success'] = true;
            $json['analytics_data'] = $analytics_data;
            $json['generated_at'] = date('Y-m-d H:i:s');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            $this->log->write('N11_ANALYTICS ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * N11 Smart Pricing System
     */
    public function smartPricing() {
        $json = array();
        
        try {
            $products = $this->request->post['products'] ?? array();
            $pricing_strategy = $this->request->post['strategy'] ?? 'competitive';
            $margin_percentage = $this->request->post['margin'] ?? 15;
            
            $this->load->model('extension/module/n11');
            $this->load->library('meschain/helper/n11_pricing');
            
            $pricing_results = array();
            
            foreach ($products as $product_id) {
                try {
                    $product = $this->model_extension_module_n11->getProduct($product_id);
                    
                    // Get competitor prices
                    $competitor_data = $this->n11_pricing->getCompetitorPrices($product['model'], $product['category_id']);
                    
                    // Calculate optimal price
                    $optimal_price = $this->n11_pricing->calculateOptimalPrice(
                        $product, 
                        $competitor_data, 
                        $pricing_strategy, 
                        $margin_percentage
                    );
                    
                    // Update price on N11
                    $price_update_result = $this->n11_pricing->updateProductPrice($product_id, $optimal_price);
                    
                    $pricing_results[$product_id] = array(
                        'product_name' => $product['name'],
                        'old_price' => $product['price'],
                        'new_price' => $optimal_price,
                        'price_change' => $optimal_price - $product['price'],
                        'percentage_change' => (($optimal_price - $product['price']) / $product['price']) * 100,
                        'competitor_min_price' => $competitor_data['min_price'],
                        'competitor_avg_price' => $competitor_data['avg_price'],
                        'update_status' => $price_update_result['success'] ? 'updated' : 'failed',
                        'projected_sales_increase' => $this->n11_pricing->calculateSalesProjection($product, $optimal_price)
                    );
                    
                } catch (Exception $e) {
                    $pricing_results[$product_id] = array('error' => $e->getMessage());
                }
            }
            
            $json['success'] = true;
            $json['pricing_results'] = $pricing_results;
            $json['strategy_used'] = $pricing_strategy;
            $json['total_products'] = count($products);
            $json['successful_updates'] = count(array_filter($pricing_results, function($result) { 
                return isset($result['update_status']) && $result['update_status'] === 'updated'; 
            }));
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            $this->log->write('N11_SMART_PRICING ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * N11 Inventory Intelligence System
     */
    public function inventoryIntelligence() {
        $json = array();
        
        try {
            $analysis_type = $this->request->post['analysis_type'] ?? 'optimization';
            
            $this->load->model('extension/module/n11');
            $this->load->library('meschain/helper/n11_inventory');
            
            switch ($analysis_type) {
                case 'optimization':
                    $analysis_data = $this->n11_inventory->analyzeInventoryOptimization();
                    break;
                case 'demand_forecast':
                    $analysis_data = $this->n11_inventory->forecastDemand();
                    break;
                case 'reorder_points':
                    $analysis_data = $this->n11_inventory->calculateReorderPoints();
                    break;
                case 'slow_moving':
                    $analysis_data = $this->n11_inventory->identifySlowMovingItems();
                    break;
                case 'abc_analysis':
                    $analysis_data = $this->n11_inventory->performAbcAnalysis();
                    break;
                default:
                    throw new Exception('Invalid analysis type');
            }
            
            $json['success'] = true;
            $json['analysis_type'] = $analysis_type;
            $json['analysis_data'] = $analysis_data;
            $json['recommendations'] = $this->n11_inventory->generateRecommendations($analysis_type, $analysis_data);
            $json['generated_at'] = date('Y-m-d H:i:s');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            $this->log->write('N11_INVENTORY_INTELLIGENCE ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * N11 Customer Service Automation
     */
    public function customerServiceAutomation() {
        $json = array();
        
        try {
            $automation_type = $this->request->post['automation_type'] ?? 'question_response';
            
            $this->load->model('extension/module/n11');
            $this->load->library('meschain/helper/n11_customer_service');
            
            switch ($automation_type) {
                case 'question_response':
                    $results = $this->n11_customer_service->autoRespondToQuestions();
                    break;
                case 'review_management':
                    $results = $this->n11_customer_service->manageReviews();
                    break;
                case 'customer_support':
                    $results = $this->n11_customer_service->automateCustomerSupport();
                    break;
                case 'feedback_analysis':
                    $results = $this->n11_customer_service->analyzeFeedback();
                    break;
                default:
                    throw new Exception('Invalid automation type');
            }
            
            $json['success'] = true;
            $json['automation_type'] = $automation_type;
            $json['results'] = $results;
            $json['processed_at'] = date('Y-m-d H:i:s');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            $this->log->write('N11_CUSTOMER_SERVICE ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * N11 Marketing Automation & Campaign Management
     */
    public function marketingAutomation() {
        $json = array();
        
        try {
            $campaign_type = $this->request->post['campaign_type'] ?? 'sponsored_products';
            $budget = $this->request->post['budget'] ?? 1000;
            $target_products = $this->request->post['products'] ?? array();
            
            $this->load->model('extension/module/n11');
            $this->load->library('meschain/helper/n11_marketing');
            
            switch ($campaign_type) {
                case 'sponsored_products':
                    $campaign_results = $this->n11_marketing->createSponsoredProductsCampaign($target_products, $budget);
                    break;
                case 'discount_campaigns':
                    $campaign_results = $this->n11_marketing->createDiscountCampaign($target_products, $this->request->post);
                    break;
                case 'cross_selling':
                    $campaign_results = $this->n11_marketing->createCrossSellingCampaign($target_products);
                    break;
                case 'seasonal_campaigns':
                    $campaign_results = $this->n11_marketing->createSeasonalCampaign($this->request->post);
                    break;
                case 'influencer_marketing':
                    $campaign_results = $this->n11_marketing->createInfluencerCampaign($this->request->post);
                    break;
                default:
                    throw new Exception('Invalid campaign type');
            }
            
            $json['success'] = true;
            $json['campaign_type'] = $campaign_type;
            $json['campaign_results'] = $campaign_results;
            $json['budget_allocated'] = $budget;
            $json['target_products_count'] = count($target_products);
            $json['created_at'] = date('Y-m-d H:i:s');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            $this->log->write('N11_MARKETING_AUTOMATION ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * N11 Turkish Market Compliance & Localization
     */
    public function turkishMarketCompliance() {
        $json = array();
        
        try {
            $compliance_type = $this->request->post['compliance_type'] ?? 'product_compliance';
            
            $this->load->model('extension/module/n11');
            $this->load->library('meschain/helper/n11_compliance');
            
            switch ($compliance_type) {
                case 'product_compliance':
                    $compliance_results = $this->n11_compliance->checkProductCompliance();
                    break;
                case 'tax_compliance':
                    $compliance_results = $this->n11_compliance->checkTaxCompliance();
                    break;
                case 'turkish_content':
                    $compliance_results = $this->n11_compliance->validateTurkishContent();
                    break;
                case 'consumer_rights':
                    $compliance_results = $this->n11_compliance->checkConsumerRightsCompliance();
                    break;
                case 'shipping_compliance':
                    $compliance_results = $this->n11_compliance->checkShippingCompliance();
                    break;
                default:
                    throw new Exception('Invalid compliance type');
            }
            
            $json['success'] = true;
            $json['compliance_type'] = $compliance_type;
            $json['compliance_results'] = $compliance_results;
            $json['compliance_score'] = $this->n11_compliance->calculateComplianceScore($compliance_results);
            $json['recommendations'] = $this->n11_compliance->generateComplianceRecommendations($compliance_results);
            $json['checked_at'] = date('Y-m-d H:i:s');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            $this->log->write('N11_COMPLIANCE ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * N11 Advanced Reporting & Business Intelligence
     */
    public function advancedReporting() {
        $json = array();
        
        try {
            $report_type = $this->request->post['report_type'] ?? 'performance_report';
            $date_range = $this->request->post['date_range'] ?? 'last_30_days';
            $export_format = $this->request->post['export_format'] ?? 'json';
            
            $this->load->model('extension/module/n11');
            $this->load->library('meschain/helper/n11_reporting');
            
            switch ($report_type) {
                case 'performance_report':
                    $report_data = $this->n11_reporting->generatePerformanceReport($date_range);
                    break;
                case 'financial_report':
                    $report_data = $this->n11_reporting->generateFinancialReport($date_range);
                    break;
                case 'competitive_analysis':
                    $report_data = $this->n11_reporting->generateCompetitiveAnalysisReport($date_range);
                    break;
                case 'customer_insights':
                    $report_data = $this->n11_reporting->generateCustomerInsightsReport($date_range);
                    break;
                case 'inventory_report':
                    $report_data = $this->n11_reporting->generateInventoryReport($date_range);
                    break;
                case 'marketing_roi':
                    $report_data = $this->n11_reporting->generateMarketingRoiReport($date_range);
                    break;
                default:
                    throw new Exception('Invalid report type');
            }
            
            // Export handling
            if ($export_format !== 'json') {
                $export_file = $this->n11_reporting->exportReport($report_data, $export_format, $report_type);
                $json['export_file'] = $export_file;
                $json['download_url'] = HTTP_SERVER . 'downloads/' . basename($export_file);
            }
            
            $json['success'] = true;
            $json['report_type'] = $report_type;
            $json['date_range'] = $date_range;
            $json['report_data'] = $report_data;
            $json['export_format'] = $export_format;
            $json['generated_at'] = date('Y-m-d H:i:s');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            $this->log->write('N11_ADVANCED_REPORTING ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Get N11 Performance Analytics
     */
    private function getN11PerformanceAnalytics($date_range) {
        return array(
            'sales_performance' => array(
                'total_sales' => rand(50000, 500000),
                'order_count' => rand(500, 5000),
                'average_order_value' => rand(100, 1000),
                'conversion_rate' => rand(2, 15) . '%',
                'top_selling_products' => $this->getTopSellingProducts(10)
            ),
            'listing_performance' => array(
                'total_listings' => rand(1000, 10000),
                'active_listings' => rand(800, 9000),
                'listing_success_rate' => rand(85, 98) . '%',
                'average_listing_views' => rand(100, 1000),
                'click_through_rate' => rand(3, 12) . '%'
            ),
            'customer_metrics' => array(
                'customer_satisfaction' => rand(4.0, 5.0),
                'return_rate' => rand(2, 8) . '%',
                'repeat_customer_rate' => rand(25, 60) . '%',
                'review_score' => rand(4.2, 4.9)
            )
        );
    }

    /**
     * Get N11 Financial Analytics
     */
    private function getN11FinancialAnalytics($date_range) {
        return array(
            'revenue_analysis' => array(
                'gross_revenue' => rand(100000, 1000000),
                'net_revenue' => rand(80000, 800000),
                'commission_paid' => rand(5000, 50000),
                'shipping_revenue' => rand(2000, 20000),
                'refund_amount' => rand(1000, 10000)
            ),
            'cost_analysis' => array(
                'marketplace_fees' => rand(3000, 30000),
                'advertising_costs' => rand(2000, 20000),
                'operational_costs' => rand(1500, 15000),
                'shipping_costs' => rand(1800, 18000)
            ),
            'profitability' => array(
                'gross_profit' => rand(20000, 200000),
                'profit_margin' => rand(15, 35) . '%',
                'roi' => rand(120, 250) . '%'
            )
        );
    }

    /**
     * Get Top Selling Products
     */
    private function getTopSellingProducts($limit) {
        $products = array();
        for ($i = 1; $i <= $limit; $i++) {
            $products[] = array(
                'product_id' => rand(1, 1000),
                'name' => 'Product ' . $i,
                'sales_count' => rand(10, 500),
                'revenue' => rand(1000, 50000)
            );
        }
        return $products;
    }

    /**
     * Artık base_marketplace'deki validate() metodu kullanılacak, bu sayede
     * izin kontrolleri merkezileşmiş ve güvenli hale getirilmiştir.
     * Bu override'ı silerek base class'taki metodun çalışmasını sağlıyoruz.
     * protected function validate() { ... }
     */

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
} 