<?php
/**
 * MesChain-Sync Çiçek Sepeti Marketplace Controller
 * 
 * @package     MesChain-Sync
 * @subpackage  Çiçek Sepeti Controller
 * @category    Marketplace Integration
 * @author      MesChain Development Team
 * @copyright   2024 MesChain-Sync
 * @license     Commercial License
 * @version     1.0.0
 * @since       1.0.0
 */

class ControllerExtensionModuleCiceksepeti extends Controller {
    
    /**
     * Error array for validation
     */
    private $error = array();
    
    /**
     * API Helper instance
     */
    private $api_helper = null;
    
    /**
     * Flower types mapping
     */
    private $flower_types = [
        'flower' => 'Çiçekler',
        'plant' => 'Bitkiler',
        'accessory' => 'Aksesuarlar',
        'special_occasion' => 'Özel Günler'
    ];
    
    /**
     * Main settings page
     * 
     * @return void
     */
    public function index() {
        try {
            $this->load->language('extension/module/ciceksepeti');
            
            // Permission check
            if (!$this->user->hasPermission('modify', 'extension/module/ciceksepeti')) {
                $this->error['warning'] = $this->language->get('error_permission');
            }
            
            $this->document->setTitle($this->language->get('heading_title'));
            $this->load->model('setting/setting');
            $this->load->model('extension/module/ciceksepeti');

            // Handle form submission
            if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
                $this->model_setting_setting->editSetting('module_ciceksepeti', $this->request->post);
                
                // Initialize tables if status is enabled
                if (isset($this->request->post['module_ciceksepeti_status']) && $this->request->post['module_ciceksepeti_status']) {
                    $this->model_extension_module_ciceksepeti->install();
                }
                
                $this->model_extension_module_ciceksepeti->log('info', 'settings_update', 'Çiçek Sepeti settings updated successfully');
                $this->session->data['success'] = $this->language->get('text_success');
                $this->response->redirect($this->url->link('extension/module/ciceksepeti', 'user_token=' . $this->session->data['user_token'], true));
            }

            // Prepare template data
            $data = $this->prepareTemplateData();
            
            // Test API connection status
            if ($data['module_ciceksepeti_status'] && $data['module_ciceksepeti_api_key'] && $data['module_ciceksepeti_supplier_id']) {
                $data['connection_status'] = $this->testApiConnection();
            } else {
                $data['connection_status'] = [
                    'success' => false,
                    'message' => 'API credentials not configured'
                ];
            }
            
            // Get statistics
            $data['statistics'] = $this->model_extension_module_ciceksepeti->getStatistics();
            
            // Get flower type statistics
            $data['flower_type_stats'] = $this->model_extension_module_ciceksepeti->getFlowerTypeStatistics();
            
            // Load view
            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');
            
            $this->response->setOutput($this->load->view('extension/module/ciceksepeti', $data));
            
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
            $this->load->language('extension/module/ciceksepeti');
            
            // Permission check
            if (!$this->user->hasPermission('access', 'extension/module/ciceksepeti')) {
                $this->session->data['error'] = $this->language->get('error_permission');
                $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
            }
            
            $this->document->setTitle($this->language->get('heading_title') . ' - Dashboard');
            $this->load->model('extension/module/ciceksepeti');
            
            // Prepare dashboard data
            $data = $this->prepareDashboardData();
            
            // Get comprehensive statistics
            $data['statistics'] = $this->model_extension_module_ciceksepeti->getStatistics();
            
            // Get recent activities (logs)
            $data['recent_activities'] = $this->model_extension_module_ciceksepeti->getLogs(['limit' => 10]);
            
            // Get products by flower type
            foreach ($this->flower_types as $type => $name) {
                $data['flower_products'][$type] = $this->model_extension_module_ciceksepeti->getProductsByFlowerType($type, ['limit' => 3]);
            }
            
            // Check API status
            $data['api_status'] = $this->checkApiStatus();
            
            // Get seasonal information
            $data['seasonal_info'] = $this->getSeasonalInfo();
            
            // Load view
            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');
            
            $this->response->setOutput($this->load->view('extension/module/ciceksepeti_dashboard', $data));
            
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
            $this->load->model('extension/module/ciceksepeti');
            
            // Get API credentials
            $api_key = $this->config->get('module_ciceksepeti_api_key');
            $supplier_id = $this->config->get('module_ciceksepeti_supplier_id');
            $debug = $this->config->get('module_ciceksepeti_debug');
            
            if (empty($api_key) || empty($supplier_id)) {
                throw new Exception('API credentials not configured');
            }
            
            // Initialize API Helper
            require_once(DIR_SYSTEM . 'library/meschain/helper/ciceksepeti_api.php');
            $this->api_helper = new CiceksepetiApiHelper($api_key, $supplier_id, $debug);
            
            // Validate credentials format
            $validation = $this->api_helper->validateCredentials();
            if (!$validation['valid']) {
                throw new Exception('Invalid credentials: ' . implode(', ', $validation['errors']));
            }
            
            // Test connection
            $result = $this->api_helper->testConnection();
            
            if ($result['success']) {
                $json['success'] = $result['message'];
                $this->model_extension_module_ciceksepeti->log('success', 'connection_test', 'API connection test successful');
            } else {
                $json['error'] = $result['error'];
                $this->model_extension_module_ciceksepeti->log('error', 'connection_test', 'API connection test failed: ' . $result['error']);
            }
            
        } catch (Exception $e) {
            $json['error'] = 'Connection test failed: ' . $e->getMessage();
            $this->model_extension_module_ciceksepeti->log('error', 'connection_test', $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Synchronize products with Çiçek Sepeti
     * 
     * @return void
     */
    public function sync_products() {
        $json = array();
        
        try {
            $this->load->model('extension/module/ciceksepeti');
            $this->load->model('catalog/product');
            
            // Initialize API Helper
            $this->initializeApiHelper();
            
            // Get flower type from request or default to 'flower'
            $flower_type = $this->request->get['flower_type'] ?? 'flower';
            
            // Get products to sync (filter by categories related to flowers/plants)
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
                    // Check if product already exists in Çiçek Sepeti
                    $ciceksepeti_product = $this->model_extension_module_ciceksepeti->getProduct($product['product_id']);
                    
                    // Prepare product data with flower-specific fields
                    $product_data = $this->prepareProductData($product, $flower_type);
                    
                    if ($ciceksepeti_product) {
                        // Update existing product
                        $result = $this->api_helper->updateProduct($ciceksepeti_product['ciceksepeti_id'], $product_data);
                        if ($result['success']) {
                            $this->model_extension_module_ciceksepeti->updateProduct([
                                'product_id' => $product['product_id'],
                                'price' => $product['price'],
                                'stock_quantity' => $product['quantity'],
                                'flower_type' => $flower_type
                            ]);
                            $synced_count++;
                        } else {
                            $error_count++;
                        }
                    } else {
                        // Upload new product
                        $result = $this->api_helper->uploadProduct($product_data);
                        if ($result['success']) {
                            $this->model_extension_module_ciceksepeti->addProduct([
                                'product_id' => $product['product_id'],
                                'ciceksepeti_id' => $result['ciceksepeti_id'],
                                'price' => $product['price'],
                                'stock_quantity' => $product['quantity'],
                                'flower_type' => $flower_type
                            ]);
                            $synced_count++;
                        } else {
                            $error_count++;
                        }
                    }
                    
                } catch (Exception $e) {
                    $error_count++;
                    $this->model_extension_module_ciceksepeti->log('error', 'product_sync', 'Product sync failed for ID ' . $product['product_id'] . ': ' . $e->getMessage());
                }
            }
            
            $json['success'] = sprintf('Product sync completed for %s. Synced: %d, Errors: %d', $this->flower_types[$flower_type], $synced_count, $error_count);
            $this->model_extension_module_ciceksepeti->log('info', 'product_sync', "Product sync completed for {$flower_type}. Synced: {$synced_count}, Errors: {$error_count}");
            
        } catch (Exception $e) {
            $json['error'] = 'Product sync failed: ' . $e->getMessage();
            $this->model_extension_module_ciceksepeti->log('error', 'product_sync', $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get orders from Çiçek Sepeti
     * 
     * @return void
     */
    public function get_orders() {
        $json = array();
        
        try {
            $this->load->model('extension/module/ciceksepeti');
            $this->load->model('sale/order');
            
            // Initialize API Helper
            $this->initializeApiHelper();
            
            // Get orders from last 7 days with delivery date filter
            $filters = array(
                'start_date' => date('Y-m-d', strtotime('-7 days')),
                'end_date' => date('Y-m-d H:i:s'),
                'delivery_date' => date('Y-m-d', strtotime('+1 day')) // Include orders for tomorrow
            );
            
            $result = $this->api_helper->getOrders($filters);
            
            if ($result['success']) {
                $imported_count = 0;
                $error_count = 0;
                
                foreach ($result['orders'] as $order) {
                    try {
                        // Check if order already exists
                        $existing_order = $this->model_extension_module_ciceksepeti->getOrderByCiceksepetiNumber($order['order_number']);
                        
                        if (!$existing_order) {
                            // Import new order with flower-specific data
                            $order_data = $this->prepareOrderData($order);
                            
                            if ($this->model_extension_module_ciceksepeti->addOrder($order_data)) {
                                $imported_count++;
                            } else {
                                $error_count++;
                            }
                        }
                        
                    } catch (Exception $e) {
                        $error_count++;
                        $this->model_extension_module_ciceksepeti->log('error', 'order_import', 'Order import failed for ' . $order['order_number'] . ': ' . $e->getMessage());
                    }
                }
                
                $json['success'] = sprintf('Order import completed. Imported: %d, Errors: %d', $imported_count, $error_count);
                $this->model_extension_module_ciceksepeti->log('info', 'order_import', "Order import completed. Imported: {$imported_count}, Errors: {$error_count}");
            } else {
                $json['error'] = $result['error'];
                $this->model_extension_module_ciceksepeti->log('error', 'order_import', $result['error']);
            }
            
        } catch (Exception $e) {
            $json['error'] = 'Order import failed: ' . $e->getMessage();
            $this->model_extension_module_ciceksepeti->log('error', 'order_import', $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Update stock quantities on Çiçek Sepeti
     * 
     * @return void
     */
    public function update_stock() {
        $json = array();
        
        try {
            $this->load->model('extension/module/ciceksepeti');
            $this->load->model('catalog/product');
            
            // Initialize API Helper
            $this->initializeApiHelper();
            
            // Get Çiçek Sepeti products
            $ciceksepeti_products = $this->model_extension_module_ciceksepeti->getProducts(['limit' => 100]);
            $updated_count = 0;
            $error_count = 0;
            
            foreach ($ciceksepeti_products as $ciceksepeti_product) {
                try {
                    // Get current OpenCart stock
                    $opencart_product = $this->model_catalog_product->getProduct($ciceksepeti_product['product_id']);
                    
                    if ($opencart_product && $opencart_product['quantity'] != $ciceksepeti_product['stock_quantity']) {
                        // Update stock on Çiçek Sepeti
                        $result = $this->api_helper->updateStock($ciceksepeti_product['ciceksepeti_id'], $opencart_product['quantity']);
                        
                        if ($result['success']) {
                            // Update local record
                            $this->model_extension_module_ciceksepeti->updateProduct([
                                'product_id' => $ciceksepeti_product['product_id'],
                                'stock_quantity' => $opencart_product['quantity']
                            ]);
                            $updated_count++;
                        } else {
                            $error_count++;
                        }
                    }
                    
                } catch (Exception $e) {
                    $error_count++;
                    $this->model_extension_module_ciceksepeti->log('error', 'stock_update', 'Stock update failed for product ID ' . $ciceksepeti_product['product_id'] . ': ' . $e->getMessage());
                }
            }
            
            $json['success'] = sprintf('Stock update completed. Updated: %d, Errors: %d', $updated_count, $error_count);
            $this->model_extension_module_ciceksepeti->log('info', 'stock_update', "Stock update completed. Updated: {$updated_count}, Errors: {$error_count}");
            
        } catch (Exception $e) {
            $json['error'] = 'Stock update failed: ' . $e->getMessage();
            $this->model_extension_module_ciceksepeti->log('error', 'stock_update', $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get delivery schedules for specific date
     * 
     * @return void
     */
    public function get_delivery_schedules() {
        $json = array();
        
        try {
            $this->load->model('extension/module/ciceksepeti');
            
            // Initialize API Helper
            $this->initializeApiHelper();
            
            $date = $this->request->get['date'] ?? date('Y-m-d', strtotime('+1 day'));
            $city = $this->request->get['city'] ?? '';
            
            $filters = array(
                'date' => $date,
                'city' => $city
            );
            
            $result = $this->api_helper->getDeliverySchedules($filters);
            
            if ($result['success']) {
                $json['success'] = true;
                $json['schedules'] = $result['schedules'];
                $this->model_extension_module_ciceksepeti->log('info', 'delivery_schedules', "Delivery schedules retrieved for {$date}");
            } else {
                $json['error'] = $result['error'];
                $this->model_extension_module_ciceksepeti->log('error', 'delivery_schedules', $result['error']);
            }
            
        } catch (Exception $e) {
            $json['error'] = 'Failed to get delivery schedules: ' . $e->getMessage();
            $this->model_extension_module_ciceksepeti->log('error', 'delivery_schedules', $e->getMessage());
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
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/ciceksepeti');
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/ciceksepeti');
            
            // Initialize database tables
            $this->load->model('extension/module/ciceksepeti');
            $this->model_extension_module_ciceksepeti->install();
            
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
            $this->load->model('extension/module/ciceksepeti');
            $this->model_extension_module_ciceksepeti->log('info', 'uninstall', 'Çiçek Sepeti module uninstalled');
            
            // Note: We don't drop tables to preserve data
            // $this->model_extension_module_ciceksepeti->uninstall();
            
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
        if (!$this->user->hasPermission('modify', 'extension/module/ciceksepeti')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            // API Key validation
            if (empty($this->request->post['module_ciceksepeti_api_key'])) {
                $this->error['api_key'] = $this->language->get('error_api_key');
            } elseif (strlen($this->request->post['module_ciceksepeti_api_key']) < 20) {
                $this->error['api_key'] = $this->language->get('error_api_key_length');
            }
            
            // Supplier ID validation
            if (empty($this->request->post['module_ciceksepeti_supplier_id'])) {
                $this->error['supplier_id'] = $this->language->get('error_supplier_id');
            } elseif (!is_numeric($this->request->post['module_ciceksepeti_supplier_id'])) {
                $this->error['supplier_id'] = $this->language->get('error_supplier_id_numeric');
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
            $api_key = $this->config->get('module_ciceksepeti_api_key');
            $supplier_id = $this->config->get('module_ciceksepeti_supplier_id');
            $debug = $this->config->get('module_ciceksepeti_debug');
            
            if (empty($api_key) || empty($supplier_id)) {
                throw new Exception('API credentials not configured');
            }
            
            require_once(DIR_SYSTEM . 'library/meschain/helper/ciceksepeti_api.php');
            $this->api_helper = new CiceksepetiApiHelper($api_key, $supplier_id, $debug);
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
            'seasonal_info' => null,
            'last_check' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->initializeApiHelper();
            
            // Test connection
            $connection_test = $this->api_helper->testConnection();
            $status['connected'] = $connection_test['success'];
            
            // Get seasonal information
            $seasonal_info = $this->api_helper->getSeasonalInfo('flower');
            if ($seasonal_info['success']) {
                $status['seasonal_info'] = $seasonal_info['seasonal_info'];
            }
            
        } catch (Exception $e) {
            $status['error'] = $e->getMessage();
        }
        
        return $status;
    }
    
    /**
     * Get seasonal information
     * 
     * @return array
     */
    private function getSeasonalInfo() {
        try {
            $this->initializeApiHelper();
            
            $seasonal_data = [];
            foreach ($this->flower_types as $type => $name) {
                $info = $this->api_helper->getSeasonalInfo($type);
                if ($info['success']) {
                    $seasonal_data[$type] = $info['seasonal_info'];
                }
            }
            
            return $seasonal_data;
        } catch (Exception $e) {
            return [];
        }
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
        $data['entry_supplier_id'] = $this->language->get('entry_supplier_id');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_debug'] = $this->language->get('entry_debug');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_test_connection'] = $this->language->get('button_test_connection');
        $data['button_dashboard'] = $this->language->get('button_dashboard');
        
        // URLs
        $data['action'] = $this->url->link('extension/module/ciceksepeti', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        $data['dashboard_url'] = $this->url->link('extension/module/ciceksepeti/dashboard', 'user_token=' . $this->session->data['user_token'], true);
        $data['test_connection_url'] = $this->url->link('extension/module/ciceksepeti/test_connection', 'user_token=' . $this->session->data['user_token'], true);
        
        // Form values
        $data['module_ciceksepeti_api_key'] = $this->request->post['module_ciceksepeti_api_key'] ?? $this->config->get('module_ciceksepeti_api_key') ?? '';
        $data['module_ciceksepeti_supplier_id'] = $this->request->post['module_ciceksepeti_supplier_id'] ?? $this->config->get('module_ciceksepeti_supplier_id') ?? '';
        $data['module_ciceksepeti_status'] = $this->request->post['module_ciceksepeti_status'] ?? $this->config->get('module_ciceksepeti_status') ?? '';
        $data['module_ciceksepeti_debug'] = $this->request->post['module_ciceksepeti_debug'] ?? $this->config->get('module_ciceksepeti_debug') ?? '';
        
        // Error handling
        $data['error_warning'] = $this->error['warning'] ?? '';
        $data['error_api_key'] = $this->error['api_key'] ?? '';
        $data['error_supplier_id'] = $this->error['supplier_id'] ?? '';
        
        // Success message
        $data['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);
        
        // Flower types
        $data['flower_types'] = $this->flower_types;
        
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
            'href' => $this->url->link('extension/module/ciceksepeti', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Additional data
        $data['has_permission'] = $this->user->hasPermission('modify', 'extension/module/ciceksepeti');
        
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
        $data['test_connection'] = $this->url->link('extension/module/ciceksepeti/test_connection', 'user_token=' . $this->session->data['user_token'], true);
        $data['sync_products'] = $this->url->link('extension/module/ciceksepeti/sync_products', 'user_token=' . $this->session->data['user_token'], true);
        $data['get_orders'] = $this->url->link('extension/module/ciceksepeti/get_orders', 'user_token=' . $this->session->data['user_token'], true);
        $data['update_stock'] = $this->url->link('extension/module/ciceksepeti/update_stock', 'user_token=' . $this->session->data['user_token'], true);
        $data['get_delivery_schedules'] = $this->url->link('extension/module/ciceksepeti/get_delivery_schedules', 'user_token=' . $this->session->data['user_token'], true);
        $data['settings'] = $this->url->link('extension/module/ciceksepeti', 'user_token=' . $this->session->data['user_token'], true);
        
        // Flower types
        $data['flower_types'] = $this->flower_types;
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/ciceksepeti/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        return $data;
    }
    
    /**
     * Prepare product data for API with flower-specific fields
     * 
     * @param array $product OpenCart product data
     * @param string $flower_type Flower type
     * @return array Formatted product data
     */
    private function prepareProductData($product, $flower_type = 'flower') {
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
        
        // Base product data
        $data = array(
            'name' => $product['name'],
            'description' => $product_info['description'] ?? '',
            'price' => $product['price'],
            'quantity' => $product['quantity'],
            'sku' => $product['sku'] ?: $product['model'],
            'flower_type' => $flower_type,
            'weight' => $product['weight'],
            'length' => $product['length'],
            'width' => $product['width'],
            'height' => $product['height'],
            'images' => $images,
            'manufacturer' => $product['manufacturer'] ?? ''
        );
        
        // Add flower-specific fields based on type
        if ($flower_type === 'flower') {
            $data['flower_color'] = $this->extractFlowerColor($product['name']);
            $data['flower_count'] = $this->extractFlowerCount($product['name']);
            $data['arrangement_type'] = 'bouquet';
            $data['care_instructions'] = 'Serin yerde muhafaza edin. Düzenli su değiştirin.';
            $data['occasion_tags'] = ['sevgililer_gunu', 'dogum_gunu', 'ozel_gun'];
        } elseif ($flower_type === 'plant') {
            $data['plant_type'] = 'indoor';
            $data['pot_included'] = true;
            $data['light_requirement'] = 'medium';
            $data['water_requirement'] = 'medium';
            $data['care_instructions'] = 'Haftada 2-3 kez sulayın. Dolaylı güneş ışığı tercih eder.';
        }
        
        return $data;
    }
    
    /**
     * Prepare order data for import with flower-specific fields
     * 
     * @param array $order Çiçek Sepeti order data
     * @return array Formatted order data
     */
    private function prepareOrderData($order) {
        return array(
            'ciceksepeti_order_number' => $order['order_number'],
            'ciceksepeti_status' => $order['status'],
            'customer_name' => $order['customer']['name'] ?? '',
            'customer_email' => $order['customer']['email'] ?? '',
            'customer_phone' => $order['customer']['phone'] ?? '',
            'delivery_address' => $order['delivery']['address'] ?? '',
            'delivery_date' => $order['delivery']['date'] ?? null,
            'delivery_time' => $order['delivery']['time'] ?? '',
            'gift_message' => $order['gift_message'] ?? '',
            'total_amount' => $order['total_amount'],
            'currency' => $order['currency'] ?? 'TRY',
            'order_date' => $order['created_at'],
            'sync_status' => 'pending'
        );
    }
    
    /**
     * Extract flower color from product name
     * 
     * @param string $name Product name
     * @return string Color
     */
    private function extractFlowerColor($name) {
        $colors = ['kırmızı', 'beyaz', 'pembe', 'sarı', 'mor', 'turuncu'];
        $name_lower = mb_strtolower($name, 'UTF-8');
        
        foreach ($colors as $color) {
            if (strpos($name_lower, $color) !== false) {
                return $color;
            }
        }
        
        return 'karışık';
    }
    
    /**
     * Extract flower count from product name
     * 
     * @param string $name Product name
     * @return int Count
     */
    private function extractFlowerCount($name) {
        if (preg_match('/(\d+)\s*(adet|dal|gül)/i', $name, $matches)) {
            return (int)$matches[1];
        }
        
        return 12; // Default count
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
        if (class_exists('ModelExtensionModuleCiceksepeti')) {
            $this->load->model('extension/module/ciceksepeti');
            $this->model_extension_module_ciceksepeti->log('error', $action, $error_message);
        } else {
            error_log($error_message);
        }
        
        // Set error for display
        $this->session->data['error'] = 'An error occurred. Please check the logs for details.';
        
        // Redirect to safe page
        $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
    }
}
?> 