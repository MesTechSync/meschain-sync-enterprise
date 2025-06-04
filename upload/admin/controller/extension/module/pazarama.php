<?php
/**
 * MesChain-Sync Pazarama Marketplace Controller
 * 
 * @package     MesChain-Sync
 * @subpackage  Pazarama Controller
 * @category    Marketplace Integration
 * @author      MesChain Development Team
 * @copyright   2024 MesChain-Sync
 * @license     Commercial License
 * @version     1.0.0
 * @since       1.0.0
 */

class ControllerExtensionModulePazarama extends Controller {
    
    /**
     * Error array for validation
     */
    private $error = array();
    
    /**
     * API Helper instance
     */
    private $api_helper = null;
    
    /**
     * Main settings page
     * 
     * @return void
     */
    public function index() {
        try {
            $this->load->language('extension/module/pazarama');
            
            // Permission check
            if (!$this->user->hasPermission('modify', 'extension/module/pazarama')) {
                $this->error['warning'] = $this->language->get('error_permission');
            }
            
            $this->document->setTitle($this->language->get('heading_title'));
            $this->load->model('setting/setting');
            $this->load->model('extension/module/pazarama');

            // Handle form submission
            if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
                $this->model_setting_setting->editSetting('module_pazarama', $this->request->post);
                
                // Initialize tables if status is enabled
                if (isset($this->request->post['module_pazarama_status']) && $this->request->post['module_pazarama_status']) {
                    $this->model_extension_module_pazarama->install();
                }
                
                $this->model_extension_module_pazarama->log('info', 'settings_update', 'Pazarama settings updated successfully');
                $this->session->data['success'] = $this->language->get('text_success');
                $this->response->redirect($this->url->link('extension/module/pazarama', 'user_token=' . $this->session->data['user_token'], true));
            }

            // Prepare template data
            $data = $this->prepareTemplateData();
            
            // Test API connection status
            if ($data['module_pazarama_status'] && $data['module_pazarama_api_key'] && $data['module_pazarama_secret_key']) {
                $data['connection_status'] = $this->testApiConnection();
            } else {
                $data['connection_status'] = [
                    'success' => false,
                    'message' => 'API credentials not configured'
                ];
            }
            
            // Get statistics
            $data['statistics'] = $this->model_extension_module_pazarama->getStatistics();
            
            // Load view
            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');
            
            $this->response->setOutput($this->load->view('extension/module/pazarama', $data));
            
        } catch (Exception $e) {
            $this->handleException($e, 'index');
        }
    }

    /**
     * Dashboard page with statistics and management
     * 
     * @return void
     */
    public function dashboard() {
        try {
            $this->load->language('extension/module/pazarama');
            
            // Permission check
            if (!$this->user->hasPermission('access', 'extension/module/pazarama')) {
                $this->session->data['error'] = $this->language->get('error_permission');
                $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
            }
            
            $this->document->setTitle($this->language->get('heading_title') . ' - Dashboard');
            $this->load->model('extension/module/pazarama');
            
            // Prepare dashboard data
            $data = $this->prepareDashboardData();
            
            // Get comprehensive statistics
            $data['statistics'] = $this->model_extension_module_pazarama->getStatistics();
            
            // Get recent activities (logs)
            $data['recent_activities'] = $this->model_extension_module_pazarama->getLogs(['limit' => 10]);
            
            // Get products list (limited)
            $data['recent_products'] = $this->model_extension_module_pazarama->getProducts(['limit' => 5]);
            
            // Check API status
            $data['api_status'] = $this->checkApiStatus();
            
            // Load view
            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');
            
            $this->response->setOutput($this->load->view('extension/module/pazarama_dashboard', $data));
            
        } catch (Exception $e) {
            $this->handleException($e, 'dashboard');
        }
    }
    
    /**
     * Test API connection
     * 
     * @return void
     */
    public function test_connection() {
        $json = array();
        
        try {
            $this->load->model('extension/module/pazarama');
            
            // Get API credentials
            $api_key = $this->config->get('module_pazarama_api_key');
            $secret_key = $this->config->get('module_pazarama_secret_key');
            $debug = $this->config->get('module_pazarama_debug');
            
            if (empty($api_key) || empty($secret_key)) {
                throw new Exception('API credentials not configured');
            }
            
            // Initialize API Helper
            require_once(DIR_SYSTEM . 'library/meschain/helper/pazarama_api.php');
            $this->api_helper = new PazaramaApiHelper($api_key, $secret_key, $debug);
            
            // Validate credentials format
            $validation = $this->api_helper->validateCredentials();
            if (!$validation['valid']) {
                throw new Exception('Invalid credentials: ' . implode(', ', $validation['errors']));
            }
            
            // Test connection
            $result = $this->api_helper->testConnection();
            
            if ($result['success']) {
                $json['success'] = $result['message'];
                $this->model_extension_module_pazarama->log('success', 'connection_test', 'API connection test successful');
            } else {
                $json['error'] = $result['error'];
                $this->model_extension_module_pazarama->log('error', 'connection_test', 'API connection test failed: ' . $result['error']);
            }
            
        } catch (Exception $e) {
            $json['error'] = 'Connection test failed: ' . $e->getMessage();
            $this->model_extension_module_pazarama->log('error', 'connection_test', $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Synchronize products with Pazarama
     * 
     * @return void
     */
    public function sync_products() {
        $json = array();
        
        try {
            $this->load->model('extension/module/pazarama');
            $this->load->model('catalog/product');
            
            // Initialize API Helper
            $this->initializeApiHelper();
            
            // Get products to sync
            $filter_data = array(
                'filter_status' => 1,
                'start' => 0,
                'limit' => 50 // Process in batches
            );
            
            $products = $this->model_catalog_product->getProducts($filter_data);
            $synced_count = 0;
            $error_count = 0;
            
            foreach ($products as $product) {
                try {
                    // Check if product already exists in Pazarama
                    $pazarama_product = $this->model_extension_module_pazarama->getProduct($product['product_id']);
                    
                    // Prepare product data
                    $product_data = $this->prepareProductData($product);
                    
                    if ($pazarama_product) {
                        // Update existing product
                        $result = $this->api_helper->updateProduct($pazarama_product['pazarama_id'], $product_data);
                        if ($result['success']) {
                            $this->model_extension_module_pazarama->updateProduct([
                                'product_id' => $product['product_id'],
                                'price' => $product['price'],
                                'stock_quantity' => $product['quantity']
                            ]);
                            $synced_count++;
                        } else {
                            $error_count++;
                        }
                    } else {
                        // Upload new product
                        $result = $this->api_helper->uploadProduct($product_data);
                        if ($result['success']) {
                            $this->model_extension_module_pazarama->addProduct([
                                'product_id' => $product['product_id'],
                                'pazarama_id' => $result['pazarama_id'],
                                'price' => $product['price'],
                                'stock_quantity' => $product['quantity']
                            ]);
                            $synced_count++;
                        } else {
                            $error_count++;
                        }
                    }
                    
                } catch (Exception $e) {
                    $error_count++;
                    $this->model_extension_module_pazarama->log('error', 'product_sync', 'Product sync failed for ID ' . $product['product_id'] . ': ' . $e->getMessage());
                }
            }
            
            $json['success'] = sprintf('Product sync completed. Synced: %d, Errors: %d', $synced_count, $error_count);
            $this->model_extension_module_pazarama->log('info', 'product_sync', "Product sync completed. Synced: {$synced_count}, Errors: {$error_count}");
            
        } catch (Exception $e) {
            $json['error'] = 'Product sync failed: ' . $e->getMessage();
            $this->model_extension_module_pazarama->log('error', 'product_sync', $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get orders from Pazarama
     * 
     * @return void
     */
    public function get_orders() {
        $json = array();
        
        try {
            $this->load->model('extension/module/pazarama');
            $this->load->model('sale/order');
            
            // Initialize API Helper
            $this->initializeApiHelper();
            
            // Get orders from last 7 days
            $filters = array(
                'start_date' => date('Y-m-d', strtotime('-7 days')),
                'end_date' => date('Y-m-d')
            );
            
            $result = $this->api_helper->getOrders($filters);
            
            if ($result['success']) {
                $imported_count = 0;
                $error_count = 0;
                
                foreach ($result['orders'] as $order) {
                    try {
                        // Check if order already exists
                        $existing_order = $this->model_extension_module_pazarama->getOrderByPazaramaNumber($order['order_number']);
                        
                        if (!$existing_order) {
                            // Import new order
                            $order_data = $this->prepareOrderData($order);
                            
                            if ($this->model_extension_module_pazarama->addOrder($order_data)) {
                                $imported_count++;
                            } else {
                                $error_count++;
                            }
                        }
                        
                    } catch (Exception $e) {
                        $error_count++;
                        $this->model_extension_module_pazarama->log('error', 'order_import', 'Order import failed for ' . $order['order_number'] . ': ' . $e->getMessage());
                    }
                }
                
                $json['success'] = sprintf('Order import completed. Imported: %d, Errors: %d', $imported_count, $error_count);
                $this->model_extension_module_pazarama->log('info', 'order_import', "Order import completed. Imported: {$imported_count}, Errors: {$error_count}");
            } else {
                $json['error'] = $result['error'];
                $this->model_extension_module_pazarama->log('error', 'order_import', $result['error']);
            }
            
        } catch (Exception $e) {
            $json['error'] = 'Order import failed: ' . $e->getMessage();
            $this->model_extension_module_pazarama->log('error', 'order_import', $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Update stock quantities on Pazarama
     * 
     * @return void
     */
    public function update_stock() {
        $json = array();
        
        try {
            $this->load->model('extension/module/pazarama');
            $this->load->model('catalog/product');
            
            // Initialize API Helper
            $this->initializeApiHelper();
            
            // Get Pazarama products
            $pazarama_products = $this->model_extension_module_pazarama->getProducts(['limit' => 100]);
            $updated_count = 0;
            $error_count = 0;
            
            foreach ($pazarama_products as $pazarama_product) {
                try {
                    // Get current OpenCart stock
                    $opencart_product = $this->model_catalog_product->getProduct($pazarama_product['product_id']);
                    
                    if ($opencart_product && $opencart_product['quantity'] != $pazarama_product['stock_quantity']) {
                        // Update stock on Pazarama
                        $result = $this->api_helper->updateStock($pazarama_product['pazarama_id'], $opencart_product['quantity']);
                        
                        if ($result['success']) {
                            // Update local record
                            $this->model_extension_module_pazarama->updateProduct([
                                'product_id' => $pazarama_product['product_id'],
                                'stock_quantity' => $opencart_product['quantity']
                            ]);
                            $updated_count++;
                        } else {
                            $error_count++;
                        }
                    }
                    
                } catch (Exception $e) {
                    $error_count++;
                    $this->model_extension_module_pazarama->log('error', 'stock_update', 'Stock update failed for product ID ' . $pazarama_product['product_id'] . ': ' . $e->getMessage());
                }
            }
            
            $json['success'] = sprintf('Stock update completed. Updated: %d, Errors: %d', $updated_count, $error_count);
            $this->model_extension_module_pazarama->log('info', 'stock_update', "Stock update completed. Updated: {$updated_count}, Errors: {$error_count}");
            
        } catch (Exception $e) {
            $json['error'] = 'Stock update failed: ' . $e->getMessage();
            $this->model_extension_module_pazarama->log('error', 'stock_update', $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Installation hook
     * 
     * @return void
     */
    public function install() {
        try {
            // Set permissions
            $this->load->model('user/user_group');
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/pazarama');
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/pazarama');
            
            // Initialize database tables
            $this->load->model('extension/module/pazarama');
            $this->model_extension_module_pazarama->install();
            
        } catch (Exception $e) {
            $this->handleException($e, 'install');
        }
    }

    /**
     * Uninstallation hook
     * 
     * @return void
     */
    public function uninstall() {
        try {
            $this->load->model('extension/module/pazarama');
            $this->model_extension_module_pazarama->log('info', 'uninstall', 'Pazarama module uninstalled');
            
            // Note: We don't drop tables to preserve data
            // $this->model_extension_module_pazarama->uninstall();
            
        } catch (Exception $e) {
            $this->handleException($e, 'uninstall');
        }
    }

    /**
     * Form validation
     * 
     * @return bool
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/pazarama')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            // API Key validation
            if (empty($this->request->post['module_pazarama_api_key'])) {
                $this->error['api_key'] = $this->language->get('error_api_key');
            } elseif (strlen($this->request->post['module_pazarama_api_key']) < 20) {
                $this->error['api_key'] = $this->language->get('error_api_key_length');
            }
            
            // Secret Key validation
            if (empty($this->request->post['module_pazarama_secret_key'])) {
                $this->error['secret_key'] = $this->language->get('error_secret_key');
            } elseif (strlen($this->request->post['module_pazarama_secret_key']) < 32) {
                $this->error['secret_key'] = $this->language->get('error_secret_key_length');
            }
        }
        
        return !$this->error;
    }

    /**
     * Initialize API Helper
     * 
     * @return void
     * @throws Exception
     */
    private function initializeApiHelper() {
        if ($this->api_helper === null) {
            $api_key = $this->config->get('module_pazarama_api_key');
            $secret_key = $this->config->get('module_pazarama_secret_key');
            $debug = $this->config->get('module_pazarama_debug');
            
            if (empty($api_key) || empty($secret_key)) {
                throw new Exception('API credentials not configured');
            }
            
            require_once(DIR_SYSTEM . 'library/meschain/helper/pazarama_api.php');
            $this->api_helper = new PazaramaApiHelper($api_key, $secret_key, $debug);
        }
    }
    
    /**
     * Test API connection status
     * 
     * @return array
     */
    private function testApiConnection() {
        try {
            $this->initializeApiHelper();
            return $this->api_helper->testConnection();
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check API status for dashboard
     * 
     * @return array
     */
    private function checkApiStatus() {
        $status = [
            'connected' => false,
            'rate_limit' => null,
            'last_check' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->initializeApiHelper();
            
            // Test connection
            $connection_test = $this->api_helper->testConnection();
            $status['connected'] = $connection_test['success'];
            
            // Get rate limit info
            $rate_limit = $this->api_helper->getRateLimitStatus();
            if ($rate_limit['success']) {
                $status['rate_limit'] = $rate_limit;
            }
            
        } catch (Exception $e) {
            $status['error'] = $e->getMessage();
        }
        
        return $status;
    }
    
    /**
     * Prepare template data for main settings page
     * 
     * @return array
     */
    private function prepareTemplateData() {
        $data = array();
        
        // Language strings
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_api_settings'] = $this->language->get('text_api_settings');
        $data['entry_api_key'] = $this->language->get('entry_api_key');
        $data['entry_secret_key'] = $this->language->get('entry_secret_key');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_debug'] = $this->language->get('entry_debug');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_test_connection'] = $this->language->get('button_test_connection');
        $data['button_dashboard'] = $this->language->get('button_dashboard');
        
        // URLs
        $data['action'] = $this->url->link('extension/module/pazarama', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        $data['dashboard_url'] = $this->url->link('extension/module/pazarama/dashboard', 'user_token=' . $this->session->data['user_token'], true);
        $data['test_connection_url'] = $this->url->link('extension/module/pazarama/test_connection', 'user_token=' . $this->session->data['user_token'], true);
        
        // Form values
        $data['module_pazarama_api_key'] = $this->request->post['module_pazarama_api_key'] ?? $this->config->get('module_pazarama_api_key') ?? '';
        $data['module_pazarama_secret_key'] = $this->request->post['module_pazarama_secret_key'] ?? $this->config->get('module_pazarama_secret_key') ?? '';
        $data['module_pazarama_status'] = $this->request->post['module_pazarama_status'] ?? $this->config->get('module_pazarama_status') ?? '';
        $data['module_pazarama_debug'] = $this->request->post['module_pazarama_debug'] ?? $this->config->get('module_pazarama_debug') ?? '';
        
        // Error handling
        $data['error_warning'] = $this->error['warning'] ?? '';
        $data['error_api_key'] = $this->error['api_key'] ?? '';
        $data['error_secret_key'] = $this->error['secret_key'] ?? '';
        
        // Success message
        $data['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);
        
        // Breadcrumbs
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
            'href' => $this->url->link('extension/module/pazarama', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Additional data
        $data['has_permission'] = $this->user->hasPermission('modify', 'extension/module/pazarama');
        
        return $data;
    }
    
    /**
     * Prepare dashboard data
     * 
     * @return array
     */
    private function prepareDashboardData() {
        $data = array();
        
        // Language strings
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_dashboard'] = $this->language->get('text_dashboard');
        
        // URLs
        $data['test_connection'] = $this->url->link('extension/module/pazarama/test_connection', 'user_token=' . $this->session->data['user_token'], true);
        $data['sync_products'] = $this->url->link('extension/module/pazarama/sync_products', 'user_token=' . $this->session->data['user_token'], true);
        $data['get_orders'] = $this->url->link('extension/module/pazarama/get_orders', 'user_token=' . $this->session->data['user_token'], true);
        $data['update_stock'] = $this->url->link('extension/module/pazarama/update_stock', 'user_token=' . $this->session->data['user_token'], true);
        $data['settings'] = $this->url->link('extension/module/pazarama', 'user_token=' . $this->session->data['user_token'], true);
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/pazarama/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        return $data;
    }
    
    /**
     * Prepare product data for API
     * 
     * @param array $product OpenCart product data
     * @return array Formatted product data
     */
    private function prepareProductData($product) {
        // Get product images
        $this->load->model('catalog/product');
        $images = array();
        
        if ($product['image']) {
            $images[] = HTTP_CATALOG . 'image/' . $product['image'];
        }
        
        $product_images = $this->model_catalog_product->getProductImages($product['product_id']);
        foreach ($product_images as $image) {
            $images[] = HTTP_CATALOG . 'image/' . $image['image'];
        }
        
        // Get product description
        $product_info = $this->model_catalog_product->getProduct($product['product_id']);
        
        return array(
            'name' => $product['name'],
            'description' => $product_info['description'] ?? '',
            'price' => $product['price'],
            'quantity' => $product['quantity'],
            'sku' => $product['sku'] ?: $product['model'],
            'weight' => $product['weight'],
            'length' => $product['length'],
            'width' => $product['width'],
            'height' => $product['height'],
            'images' => $images,
            'manufacturer' => $product['manufacturer'] ?? ''
        );
    }
    
    /**
     * Prepare order data for import
     * 
     * @param array $order Pazarama order data
     * @return array Formatted order data
     */
    private function prepareOrderData($order) {
        return array(
            'pazarama_order_number' => $order['order_number'],
            'pazarama_status' => $order['status'],
            'customer_name' => $order['customer']['name'] ?? '',
            'customer_email' => $order['customer']['email'] ?? '',
            'customer_phone' => $order['customer']['phone'] ?? '',
            'total_amount' => $order['total_amount'],
            'currency' => $order['currency'] ?? 'TRY',
            'order_date' => $order['created_at'],
            'sync_status' => 'pending'
        );
    }
    
    /**
     * Handle exceptions with logging
     * 
     * @param Exception $e
     * @param string $action
     * @return void
     */
    private function handleException($e, $action) {
        $error_message = "Exception in {$action}: " . $e->getMessage();
        
        // Log error
        if (class_exists('ModelExtensionModulePazarama')) {
            $this->load->model('extension/module/pazarama');
            $this->model_extension_module_pazarama->log('error', $action, $error_message);
        } else {
            error_log($error_message);
        }
        
        // Set error for display
        $this->session->data['error'] = 'An error occurred. Please check the logs for details.';
        
        // Redirect to safe page
        $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
    }
    
    /**
     * API endpoint for webhook status
     */
    public function getWebhookStatus() {
        $json = array();
        
        try {
            $this->load->model('extension/module/pazarama_webhook');
            
            $status = $this->model_extension_module_pazarama_webhook->getWebhookConfiguration();
            
            $json['success'] = true;
            $json['status'] = $status;
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * API endpoint to toggle webhook
     */
    public function toggleWebhook() {
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $this->load->model('extension/module/pazarama_webhook');
                
                $input = json_decode(file_get_contents('php://input'), true);
                $event_type = $input['event_type'] ?? '';
                $enabled = $input['enabled'] ?? false;
                
                if (empty($event_type)) {
                    throw new Exception('Event type is required');
                }
                
                // Update all webhooks of this type
                $this->model_extension_module_pazarama_webhook->saveWebhookConfiguration([
                    $event_type => $enabled
                ]);
                
                $json['success'] = true;
                $json['message'] = $event_type . ' webhook ' . ($enabled ? 'enabled' : 'disabled');
                
            } catch (Exception $e) {
                $json['error'] = $e->getMessage();
            }
        } else {
            $json['error'] = 'Method not allowed';
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * API endpoint to test webhooks
     */
    public function testWebhook() {
        $json = array();
        
        try {
            $this->load->model('extension/module/pazarama_webhook');
            
            $webhooks = $this->model_extension_module_pazarama_webhook->getWebhooks(['filter_status' => 1]);
            $results = array();
            
            foreach ($webhooks as $webhook) {
                $test_data = [
                    'event_type' => $webhook['event_type'],
                    'test' => true,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'data' => [
                        'test_message' => 'This is a test webhook from Pazarama integration'
                    ]
                ];
                
                $start_time = microtime(true);
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $webhook['url']);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'User-Agent: Pazarama-Webhook-Test'
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $execution_time = microtime(true) - $start_time;
                
                curl_close($ch);
                
                $success = ($http_code >= 200 && $http_code < 300);
                
                $results[] = [
                    'webhook_id' => $webhook['webhook_id'],
                    'event_type' => $webhook['event_type'],
                    'url' => $webhook['url'],
                    'success' => $success,
                    'http_code' => $http_code,
                    'response' => $response,
                    'execution_time' => round($execution_time, 4)
                ];
                
                // Log the test result
                $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                    'WEBHOOK_TEST',
                    'Webhook test: ' . $webhook['event_type'],
                    $success ? 'success' : 'error',
                    $webhook['webhook_id'],
                    json_encode($test_data),
                    $http_code,
                    $response,
                    $execution_time
                );
                
                // Update webhook stats
                $this->model_extension_module_pazarama_webhook->updateWebhookStats($webhook['webhook_id'], $success, $execution_time);
            }
            
            $json['success'] = true;
            $json['results'] = $results;
            $json['message'] = 'Webhook tests completed';
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * API endpoint for dashboard data including webhook status
     */
    public function getDashboardData() {
        $json = array();
        
        try {
            $this->load->model('extension/module/pazarama');
            $this->load->model('extension/module/pazarama_webhook');
            
            // Get basic statistics
            $stats = $this->model_extension_module_pazarama->getStatistics();
            
            // Get webhook statistics
            $webhook_stats = $this->model_extension_module_pazarama_webhook->getWebhookStatistics();
            
            // Check API connection status
            $connection_status = $this->checkApiStatus();
            
            $json['success'] = true;
            $json['data'] = array_merge($stats, [
                'connectionStatus' => $connection_status['success'] ? 'connected' : 'disconnected',
                'webhookStats' => $webhook_stats
            ]);
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
?>