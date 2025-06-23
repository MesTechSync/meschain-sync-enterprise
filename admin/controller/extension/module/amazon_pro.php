<?php
/**
 * Amazon Pro Marketplace Controller
 * MesChain-Sync Enterprise - OpenCart 3.0.4.0 Compatible
 * Amazon SP-API Integration with FBA & Advertising
 * 
 * @author MUSTI TEAM - MesChain Development
 * @version 4.0.0
 * @since 2024
 * @copyright 2024 MesChain Technologies
 */

class ControllerExtensionModuleAmazonPro extends Controller {
    
    private $error = array();
    private $logger;
    
    public function __construct($registry) {
        parent::__construct($registry);
        $this->logger = new Log('amazon_pro.log');
    }
    
    /**
     * Amazon Pro Dashboard
     * 
     * @return void
     */
    public function index() {
        $this->load->language('extension/module/amazon_pro');
        
        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addScript('view/javascript/extension/module/amazon_pro.js');
        $this->document->addStyle('view/stylesheet/extension/module/amazon_pro.css');
        
        // Set breadcrumbs
        $data['breadcrumbs'] = $this->buildBreadcrumbs();
        
        // Handle form submissions
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            $this->saveSettings();
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module/amazon_pro', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Prepare form data
        $data = $this->prepareFormData();
        
        // Load models
        $this->load->model('extension/module/amazon_pro');
        
        // Get dashboard statistics
        $data['statistics'] = $this->model_extension_module_amazon_pro->getDashboardStats();
        
        // Get SP-API status
        $data['sp_api_status'] = $this->checkSpApiStatus();
        
        // Get marketplace performance
        $data['marketplace_performance'] = $this->getMarketplacePerformance();
        
        // Get FBA inventory summary
        $data['fba_inventory'] = $this->getFbaInventorySummary();
        
        // Template includes
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/amazon_pro', $data));
    }
    
    /**
     * FBA Inventory Management
     * 
     * @return void
     */
    public function fbaManagement() {
        $this->load->language('extension/module/amazon_pro');
        $this->load->model('extension/module/amazon_pro');
        
        $json = array();
        
        try {
            if (!$this->validate()) {
                throw new Exception($this->language->get('error_permission'));
            }
            
            $action = $this->request->post['action'] ?? '';
            
            switch ($action) {
                case 'sync_fba_inventory':
                    $json['result'] = $this->syncFbaInventory();
                    break;
                    
                case 'create_shipment':
                    $shipment_data = $this->request->post['shipment_data'] ?? array();
                    $json['result'] = $this->createFbaShipment($shipment_data);
                    break;
                    
                case 'get_shipment_status':
                    $shipment_id = $this->request->post['shipment_id'] ?? '';
                    $json['status'] = $this->getFbaShipmentStatus($shipment_id);
                    break;
                    
                case 'get_fba_fees':
                    $sku_list = $this->request->post['sku_list'] ?? array();
                    $json['fees'] = $this->getFbaFees($sku_list);
                    break;
                    
                case 'manage_removal_order':
                    $removal_data = $this->request->post['removal_data'] ?? array();
                    $json['result'] = $this->createRemovalOrder($removal_data);
                    break;
                    
                default:
                    throw new Exception($this->language->get('error_invalid_action'));
            }
            
            $json['success'] = true;
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
            $this->logger->write('FBA management error: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Amazon Advertising Management
     * 
     * @return void
     */
    public function advertisingManagement() {
        $this->load->language('extension/module/amazon_pro');
        $this->load->model('extension/module/amazon_pro');
        
        $json = array();
        
        try {
            $action = $this->request->post['action'] ?? '';
            
            switch ($action) {
                case 'create_campaign':
                    $campaign_data = $this->request->post['campaign_data'] ?? array();
                    $json['campaign'] = $this->createSponsoredProductCampaign($campaign_data);
                    break;
                    
                case 'get_campaign_performance':
                    $campaign_id = $this->request->post['campaign_id'] ?? '';
                    $date_range = $this->request->post['date_range'] ?? array();
                    $json['performance'] = $this->getCampaignPerformance($campaign_id, $date_range);
                    break;
                    
                case 'optimize_bids':
                    $campaign_id = $this->request->post['campaign_id'] ?? '';
                    $strategy = $this->request->post['strategy'] ?? 'auto';
                    $json['result'] = $this->optimizeCampaignBids($campaign_id, $strategy);
                    break;
                    
                case 'keyword_research':
                    $asin_list = $this->request->post['asin_list'] ?? array();
                    $json['keywords'] = $this->performKeywordResearch($asin_list);
                    break;
                    
                case 'negative_keyword_management':
                    $campaign_id = $this->request->post['campaign_id'] ?? '';
                    $negative_keywords = $this->request->post['negative_keywords'] ?? array();
                    $json['result'] = $this->manageNegativeKeywords($campaign_id, $negative_keywords);
                    break;
                    
                default:
                    throw new Exception($this->language->get('error_invalid_action'));
            }
            
            $json['success'] = true;
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Advanced Product Management
     * 
     * @return void
     */
    public function productManagement() {
        $this->load->language('extension/module/amazon_pro');
        $this->load->model('extension/module/amazon_pro');
        
        $json = array();
        
        try {
            $action = $this->request->post['action'] ?? '';
            
            switch ($action) {
                case 'bulk_list_products':
                    $product_ids = $this->request->post['product_ids'] ?? array();
                    $listing_settings = $this->request->post['listing_settings'] ?? array();
                    $json['result'] = $this->bulkListProducts($product_ids, $listing_settings);
                    break;
                    
                case 'update_product_data':
                    $updates = $this->request->post['updates'] ?? array();
                    $json['result'] = $this->updateProductData($updates);
                    break;
                    
                case 'manage_variations':
                    $parent_sku = $this->request->post['parent_sku'] ?? '';
                    $variations = $this->request->post['variations'] ?? array();
                    $json['result'] = $this->manageProductVariations($parent_sku, $variations);
                    break;
                    
                case 'check_listing_status':
                    $sku_list = $this->request->post['sku_list'] ?? array();
                    $json['status'] = $this->checkListingStatus($sku_list);
                    break;
                    
                case 'optimize_listing':
                    $sku = $this->request->post['sku'] ?? '';
                    $optimization_type = $this->request->post['optimization_type'] ?? 'seo';
                    $json['result'] = $this->optimizeListing($sku, $optimization_type);
                    break;
                    
                default:
                    throw new Exception($this->language->get('error_invalid_action'));
            }
            
            $json['success'] = true;
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Order Management & Fulfillment
     * 
     * @return void
     */
    public function orderManagement() {
        $this->load->language('extension/module/amazon_pro');
        $this->load->model('extension/module/amazon_pro');
        
        $json = array();
        
        try {
            $action = $this->request->post['action'] ?? '';
            
            switch ($action) {
                case 'sync_orders':
                    $date_from = $this->request->post['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
                    $date_to = $this->request->post['date_to'] ?? date('Y-m-d');
                    $json['result'] = $this->syncAmazonOrders($date_from, $date_to);
                    break;
                    
                case 'confirm_shipment':
                    $order_items = $this->request->post['order_items'] ?? array();
                    $carrier_data = $this->request->post['carrier_data'] ?? array();
                    $json['result'] = $this->confirmShipment($order_items, $carrier_data);
                    break;
                    
                case 'handle_returns':
                    $return_id = $this->request->post['return_id'] ?? '';
                    $action_type = $this->request->post['action_type'] ?? '';
                    $json['result'] = $this->handleReturn($return_id, $action_type);
                    break;
                    
                case 'get_buy_box_eligibility':
                    $asin_list = $this->request->post['asin_list'] ?? array();
                    $json['eligibility'] = $this->getBuyBoxEligibility($asin_list);
                    break;
                    
                case 'manage_prime_eligibility':
                    $sku_list = $this->request->post['sku_list'] ?? array();
                    $json['prime_status'] = $this->checkPrimeEligibility($sku_list);
                    break;
                    
                default:
                    throw new Exception($this->language->get('error_invalid_action'));
            }
            
            $json['success'] = true;
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Advanced Analytics & Reporting
     * 
     * @return void
     */
    public function analytics() {
        $this->load->language('extension/module/amazon_pro');
        $this->load->model('extension/module/amazon_pro');
        
        $json = array();
        
        try {
            $report_type = $this->request->post['report_type'] ?? 'sales';
            $date_range = $this->request->post['date_range'] ?? array();
            $marketplace_ids = $this->request->post['marketplace_ids'] ?? array();
            
            switch ($report_type) {
                case 'sales_performance':
                    $json['report'] = $this->getSalesPerformanceReport($date_range, $marketplace_ids);
                    break;
                    
                case 'inventory_health':
                    $json['report'] = $this->getInventoryHealthReport($marketplace_ids);
                    break;
                    
                case 'advertising_performance':
                    $json['report'] = $this->getAdvertisingPerformanceReport($date_range);
                    break;
                    
                case 'buy_box_percentage':
                    $json['report'] = $this->getBuyBoxPercentageReport($date_range);
                    break;
                    
                case 'fee_preview':
                    $sku_list = $this->request->post['sku_list'] ?? array();
                    $json['report'] = $this->getFeePreviewReport($sku_list);
                    break;
                    
                case 'customer_metrics':
                    $json['report'] = $this->getCustomerMetricsReport($date_range);
                    break;
                    
                default:
                    throw new Exception($this->language->get('error_invalid_report_type'));
            }
            
            $json['success'] = true;
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Sync FBA inventory with Amazon
     * 
     * @return array
     */
    private function syncFbaInventory() {
        $this->load->model('extension/module/amazon_pro');
        $this->load->library('meschain/helper/amazon_sp_api_helper');
        
        try {
            // Get FBA inventory summaries
            $inventory_data = $this->amazon_sp_api_helper->getFbaInventorySummaries();
            
            $sync_results = array();
            foreach ($inventory_data as $inventory_item) {
                // Update local inventory records
                $result = $this->model_extension_module_amazon_pro->updateFbaInventory(
                    $inventory_item['seller_sku'],
                    $inventory_item
                );
                
                $sync_results[] = array(
                    'sku' => $inventory_item['seller_sku'],
                    'fulfillable_quantity' => $inventory_item['fulfillable_quantity'],
                    'inbound_working_quantity' => $inventory_item['inbound_working_quantity'],
                    'sync_status' => $result ? 'success' : 'failed'
                );
            }
            
            return array(
                'synced_count' => count($sync_results),
                'sync_details' => $sync_results,
                'last_sync' => date('Y-m-d H:i:s')
            );
            
        } catch (Exception $e) {
            $this->logger->write('FBA inventory sync error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create sponsored product campaign
     * 
     * @param array $campaign_data
     * @return array
     */
    private function createSponsoredProductCampaign($campaign_data) {
        $this->load->library('meschain/helper/amazon_advertising_helper');
        
        try {
            // Validate campaign data
            $this->validateCampaignData($campaign_data);
            
            // Create campaign via Amazon Advertising API
            $campaign_response = $this->amazon_advertising_helper->createSponsoredProductCampaign(array(
                'name' => $campaign_data['name'],
                'targetingType' => $campaign_data['targeting_type'] ?? 'MANUAL',
                'state' => $campaign_data['state'] ?? 'ENABLED',
                'dailyBudget' => $campaign_data['daily_budget'],
                'startDate' => $campaign_data['start_date'],
                'endDate' => $campaign_data['end_date'] ?? null
            ));
            
            if ($campaign_response['success']) {
                // Store campaign locally
                $this->model_extension_module_amazon_pro->saveCampaign(array(
                    'amazon_campaign_id' => $campaign_response['campaign_id'],
                    'name' => $campaign_data['name'],
                    'type' => 'SPONSORED_PRODUCTS',
                    'status' => 'ACTIVE',
                    'daily_budget' => $campaign_data['daily_budget'],
                    'created_at' => date('Y-m-d H:i:s')
                ));
                
                return $campaign_response;
            } else {
                throw new Exception('Campaign creation failed: ' . $campaign_response['error']);
            }
            
        } catch (Exception $e) {
            $this->logger->write('Campaign creation error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get marketplace performance metrics
     * 
     * @return array
     */
    private function getMarketplacePerformance() {
        $this->load->model('extension/module/amazon_pro');
        
        $marketplaces = $this->config->get('module_amazon_pro_marketplaces') ?: array();
        $performance = array();
        
        foreach ($marketplaces as $marketplace_id) {
            $performance[$marketplace_id] = array(
                'marketplace_name' => $this->getMarketplaceName($marketplace_id),
                'total_sales' => $this->model_extension_module_amazon_pro->getTotalSales($marketplace_id),
                'order_count' => $this->model_extension_module_amazon_pro->getOrderCount($marketplace_id),
                'average_order_value' => $this->model_extension_module_amazon_pro->getAverageOrderValue($marketplace_id),
                'buy_box_percentage' => $this->model_extension_module_amazon_pro->getBuyBoxPercentage($marketplace_id)
            );
        }
        
        return $performance;
    }
    
    /**
     * Check SP-API status
     * 
     * @return array
     */
    private function checkSpApiStatus() {
        $this->load->library('meschain/helper/amazon_sp_api_helper');
        
        try {
            $response = $this->amazon_sp_api_helper->testConnection();
            return array(
                'status' => 'connected',
                'last_check' => date('Y-m-d H:i:s'),
                'seller_id' => $response['seller_id'] ?? 'Unknown'
            );
        } catch (Exception $e) {
            return array(
                'status' => 'disconnected',
                'error' => $e->getMessage(),
                'last_check' => date('Y-m-d H:i:s')
            );
        }
    }
    
    /**
     * Get FBA inventory summary
     * 
     * @return array
     */
    private function getFbaInventorySummary() {
        $this->load->model('extension/module/amazon_pro');
        
        return array(
            'total_fulfillable' => $this->model_extension_module_amazon_pro->getTotalFulfillableInventory(),
            'total_inbound' => $this->model_extension_module_amazon_pro->getTotalInboundInventory(),
            'total_stranded' => $this->model_extension_module_amazon_pro->getTotalStrandedInventory(),
            'storage_utilization' => $this->model_extension_module_amazon_pro->getStorageUtilization()
        );
    }
    
    /**
     * Validate form data
     * 
     * @return bool
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/amazon_pro')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        return !$this->error;
    }
    
    /**
     * Build breadcrumb navigation
     * 
     * @return array
     */
    private function buildBreadcrumbs() {
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
            'href' => $this->url->link('extension/module/amazon_pro', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        return $breadcrumbs;
    }
    
    /**
     * Prepare form data for template
     * 
     * @return array
     */
    private function prepareFormData() {
        $data = array();
        
        // URLs
        $data['action'] = $this->url->link('extension/module/amazon_pro', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        // User token
        $data['user_token'] = $this->session->data['user_token'];
        
        // Configuration values
        $config_fields = array(
            'module_amazon_pro_status',
            'module_amazon_pro_sp_api_key',
            'module_amazon_pro_sp_api_secret',
            'module_amazon_pro_refresh_token',
            'module_amazon_pro_seller_id',
            'module_amazon_pro_marketplaces',
            'module_amazon_pro_fba_enabled',
            'module_amazon_pro_advertising_enabled',
            'module_amazon_pro_auto_pricing',
            'module_amazon_pro_sync_interval'
        );
        
        foreach ($config_fields as $field) {
            if (isset($this->request->post[$field])) {
                $data[$field] = $this->request->post[$field];
            } else {
                $data[$field] = $this->config->get($field);
            }
        }
        
        return $data;
    }
    
    /**
     * Save module settings
     * 
     * @return void
     */
    private function saveSettings() {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_amazon_pro', $this->request->post);
        
        $this->logger->write('Amazon Pro settings saved');
    }
    
    /**
     * Install module
     * 
     * @return void
     */
    public function install() {
        $this->load->model('extension/module/amazon_pro');
        $this->model_extension_module_amazon_pro->install();
    }
    
    /**
     * Uninstall module
     * 
     * @return void
     */
    public function uninstall() {
        $this->load->model('extension/module/amazon_pro');
        $this->model_extension_module_amazon_pro->uninstall();
    }
}
?>