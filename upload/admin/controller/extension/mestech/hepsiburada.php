<?php
/**
 * MesChain-Sync Hepsiburada Controller
 * Hepsiburada Marketplace Integration Controller
 * 
 * @package MesChain
 * @subpackage Hepsiburada
 * @author MezBjen Team - DevOps & Backend Enhancement Specialist
 * @version 3.0.4.0
 * @since June 7, 2025
 */

class ControllerExtensionMestechHepsiburada extends Controller {
    
    private $error = [];
    
    /**
     * Main index page
     */
    public function index() {
        $this->load->language('extension/mestech/hepsiburada');
        $this->load->model('extension/mestech/hepsiburada');
        $this->load->model('setting/setting');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Handle form submission
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('mestech_hepsiburada', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/mestech/hepsiburada', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Set up breadcrumbs
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mestech/hepsiburada', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        // Get language strings
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        
        // Form action URLs
        $data['action'] = $this->url->link('extension/mestech/hepsiburada', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        // API URLs
        $data['test_connection_url'] = $this->url->link('extension/mestech/hepsiburada/testConnection', 'user_token=' . $this->session->data['user_token'], true);
        $data['sync_products_url'] = $this->url->link('extension/mestech/hepsiburada/syncProducts', 'user_token=' . $this->session->data['user_token'], true);
        $data['sync_orders_url'] = $this->url->link('extension/mestech/hepsiburada/syncOrders', 'user_token=' . $this->session->data['user_token'], true);
        $data['dashboard_data_url'] = $this->url->link('extension/mestech/hepsiburada/getDashboardData', 'user_token=' . $this->session->data['user_token'], true);
        
        // Get current settings
        $data['mestech_hepsiburada_status'] = $this->config->get('mestech_hepsiburada_status');
        $data['mestech_hepsiburada_api_key'] = $this->config->get('mestech_hepsiburada_api_key');
        $data['mestech_hepsiburada_api_secret'] = $this->config->get('mestech_hepsiburada_api_secret');
        $data['mestech_hepsiburada_merchant_id'] = $this->config->get('mestech_hepsiburada_merchant_id');
        $data['mestech_hepsiburada_environment'] = $this->config->get('mestech_hepsiburada_environment') ?: 'sandbox';
        $data['mestech_hepsiburada_auto_sync'] = $this->config->get('mestech_hepsiburada_auto_sync');
        $data['mestech_hepsiburada_sync_interval'] = $this->config->get('mestech_hepsiburada_sync_interval') ?: '60';
        $data['mestech_hepsiburada_debug_mode'] = $this->config->get('mestech_hepsiburada_debug_mode');
        $data['mestech_hepsiburada_webhook_url'] = $this->config->get('mestech_hepsiburada_webhook_url');
        $data['mestech_hepsiburada_fast_delivery'] = $this->config->get('mestech_hepsiburada_fast_delivery');
        $data['mestech_hepsiburada_same_day_delivery'] = $this->config->get('mestech_hepsiburada_same_day_delivery');
        
        // Error handling
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Get dashboard statistics
        $data['stats'] = $this->model_extension_mestech_hepsiburada->getDashboardStats();
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/hepsiburada/settings', $data));
    }
    
    /**
     * Products management page
     */
    public function products() {
        $this->load->language('extension/mestech/hepsiburada');
        $this->load->model('extension/mestech/hepsiburada');
        
        $this->document->setTitle($this->language->get('heading_title') . ' - Products');
        
        // Get filter parameters
        $filter_name = $this->request->get['filter_name'] ?? '';
        $filter_status = $this->request->get['filter_status'] ?? '';
        $filter_sync_status = $this->request->get['filter_sync_status'] ?? '';
        $page = $this->request->get['page'] ?? 1;
        $limit = 20;
        $start = ($page - 1) * $limit;
        
        $filter_data = [
            'filter_name' => $filter_name,
            'filter_status' => $filter_status,
            'filter_sync_status' => $filter_sync_status,
            'start' => $start,
            'limit' => $limit
        ];
        
        // Get products
        $products = $this->model_extension_mestech_hepsiburada->getProducts($filter_data);
        $total_products = $this->model_extension_mestech_hepsiburada->getTotalProducts($filter_data);
        
        // Prepare data
        $data['products'] = [];
        foreach ($products as $product) {
            $data['products'][] = [
                'hepsiburada_product_id' => $product['hepsiburada_product_id'],
                'product_id' => $product['product_id'],
                'title' => $product['title'],
                'merchant_sku' => $product['merchant_sku'],
                'price' => number_format($product['price'], 2),
                'stock_quantity' => $product['stock_quantity'],
                'status' => $product['status'],
                'sync_status' => $product['sync_status'],
                'last_sync_date' => $product['last_sync_date'] ? date('d/m/Y H:i', strtotime($product['last_sync_date'])) : '-',
                'edit' => $this->url->link('extension/mestech/hepsiburada/editProduct', 'user_token=' . $this->session->data['user_token'] . '&hepsiburada_product_id=' . $product['hepsiburada_product_id'], true),
                'delete' => $this->url->link('extension/mestech/hepsiburada/deleteProduct', 'user_token=' . $this->session->data['user_token'] . '&hepsiburada_product_id=' . $product['hepsiburada_product_id'], true)
            ];
        }
        
        // Pagination
        $pagination = new Pagination();
        $pagination->total = $total_products;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('extension/mestech/hepsiburada/products', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);
        $data['pagination'] = $pagination->render();
        
        // Set template data
        $data['heading_title'] = $this->language->get('heading_title') . ' - Products';
        $data['filter_name'] = $filter_name;
        $data['filter_status'] = $filter_status;
        $data['filter_sync_status'] = $filter_sync_status;
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/hepsiburada/products', $data));
    }
    
    /**
     * Orders management page
     */
    public function orders() {
        $this->load->language('extension/mestech/hepsiburada');
        $this->load->model('extension/mestech/hepsiburada');
        
        $this->document->setTitle($this->language->get('heading_title') . ' - Orders');
        
        // Get filter parameters
        $filter_order_number = $this->request->get['filter_order_number'] ?? '';
        $filter_status = $this->request->get['filter_status'] ?? '';
        $filter_payment_status = $this->request->get['filter_payment_status'] ?? '';
        $page = $this->request->get['page'] ?? 1;
        $limit = 20;
        $start = ($page - 1) * $limit;
        
        $filter_data = [
            'filter_order_number' => $filter_order_number,
            'filter_status' => $filter_status,
            'filter_payment_status' => $filter_payment_status,
            'start' => $start,
            'limit' => $limit
        ];
        
        // Get orders
        $orders = $this->model_extension_mestech_hepsiburada->getOrders($filter_data);
        $total_orders = $this->model_extension_mestech_hepsiburada->getTotalOrders($filter_data);
        
        // Prepare data
        $data['orders'] = [];
        foreach ($orders as $order) {
            $data['orders'][] = [
                'hepsiburada_order_id' => $order['hepsiburada_order_id'],
                'hepsiburada_order_number' => $order['hepsiburada_order_number'],
                'customer_name' => $order['customer_name'],
                'total_amount' => number_format($order['total_amount'], 2) . ' ' . $order['currency'],
                'order_status' => $order['order_status'],
                'payment_status' => $order['payment_status'],
                'order_date' => date('d/m/Y H:i', strtotime($order['order_date'])),
                'view' => $this->url->link('extension/mestech/hepsiburada/viewOrder', 'user_token=' . $this->session->data['user_token'] . '&hepsiburada_order_id=' . $order['hepsiburada_order_id'], true)
            ];
        }
        
        // Pagination
        $pagination = new Pagination();
        $pagination->total = $total_orders;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('extension/mestech/hepsiburada/orders', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);
        $data['pagination'] = $pagination->render();
        
        // Set template data
        $data['heading_title'] = $this->language->get('heading_title') . ' - Orders';
        $data['filter_order_number'] = $filter_order_number;
        $data['filter_status'] = $filter_status;
        $data['filter_payment_status'] = $filter_payment_status;
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/hepsiburada/orders', $data));
    }
    
    /**
     * Logs page
     */
    public function logs() {
        $this->load->language('extension/mestech/hepsiburada');
        $this->load->model('extension/mestech/hepsiburada');
        
        $this->document->setTitle($this->language->get('heading_title') . ' - Logs');
        
        // Get filter parameters
        $filter_operation = $this->request->get['filter_operation'] ?? '';
        $filter_level = $this->request->get['filter_level'] ?? '';
        $page = $this->request->get['page'] ?? 1;
        $limit = 50;
        $start = ($page - 1) * $limit;
        
        $filter_data = [
            'filter_operation' => $filter_operation,
            'filter_level' => $filter_level,
            'start' => $start,
            'limit' => $limit
        ];
        
        // Get logs
        $logs = $this->model_extension_mestech_hepsiburada->getLogs($filter_data);
        
        // Prepare data
        $data['logs'] = [];
        foreach ($logs as $log) {
            $data['logs'][] = [
                'log_id' => $log['log_id'],
                'operation_type' => $log['operation_type'],
                'level' => $log['level'],
                'message' => $log['message'],
                'execution_time' => $log['execution_time'] . 's',
                'created_date' => date('d/m/Y H:i:s', strtotime($log['created_date'])),
                'level_class' => $this->getLogLevelClass($log['level'])
            ];
        }
        
        // Set template data
        $data['heading_title'] = $this->language->get('heading_title') . ' - Logs';
        $data['filter_operation'] = $filter_operation;
        $data['filter_level'] = $filter_level;
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mestech/hepsiburada/logs', $data));
    }
    
    /**
     * Test API connection (AJAX)
     */
    public function testConnection() {
        $this->load->language('extension/mestech/hepsiburada');
        $this->load->library('entegrator/EntegratorHepsiburada');
        
        $json = [];
        
        try {
            $api_key = $this->config->get('mestech_hepsiburada_api_key');
            $api_secret = $this->config->get('mestech_hepsiburada_api_secret');
            $merchant_id = $this->config->get('mestech_hepsiburada_merchant_id');
            $environment = $this->config->get('mestech_hepsiburada_environment') ?: 'sandbox';
            
            if (empty($api_key) || empty($api_secret) || empty($merchant_id)) {
                throw new Exception('API credentials are missing');
            }
            
            $integrator = new EntegratorHepsiburada([
                'api_key' => $api_key,
                'api_secret' => $api_secret,
                'merchant_id' => $merchant_id,
                'environment' => $environment
            ]);
            
            $result = $integrator->testConnection();
            
            if ($result['success']) {
                $json['success'] = true;
                $json['message'] = 'Connection successful';
                $json['data'] = $result['data'];
            } else {
                $json['success'] = false;
                $json['error'] = $result['error'];
            }
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Sync products (AJAX)
     */
    public function syncProducts() {
        $this->load->language('extension/mestech/hepsiburada');
        $this->load->model('extension/mestech/hepsiburada');
        $this->load->library('entegrator/EntegratorHepsiburada');
        
        $json = [];
        
        try {
            $integrator = $this->getIntegrator();
            $result = $integrator->syncProducts();
            
            if ($result['success']) {
                $json['success'] = true;
                $json['message'] = 'Products synced successfully';
                $json['synced_count'] = $result['synced_count'];
            } else {
                $json['success'] = false;
                $json['error'] = $result['error'];
            }
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Sync orders (AJAX)
     */
    public function syncOrders() {
        $this->load->language('extension/mestech/hepsiburada');
        $this->load->model('extension/mestech/hepsiburada');
        $this->load->library('entegrator/EntegratorHepsiburada');
        
        $json = [];
        
        try {
            $integrator = $this->getIntegrator();
            $result = $integrator->syncOrders();
            
            if ($result['success']) {
                $json['success'] = true;
                $json['message'] = 'Orders synced successfully';
                $json['synced_count'] = $result['synced_count'];
            } else {
                $json['success'] = false;
                $json['error'] = $result['error'];
            }
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get dashboard data (AJAX)
     */
    public function getDashboardData() {
        $this->load->model('extension/mestech/hepsiburada');
        
        $json = [];
        
        try {
            $stats = $this->model_extension_mestech_hepsiburada->getDashboardStats();
            
            $json['success'] = true;
            $json['data'] = [
                'total_products' => $stats['products']['total_products'] ?? 0,
                'active_products' => $stats['products']['active_products'] ?? 0,
                'synced_products' => $stats['products']['synced_products'] ?? 0,
                'total_orders' => $stats['orders']['total_orders'] ?? 0,
                'new_orders' => $stats['orders']['new_orders'] ?? 0,
                'total_revenue' => number_format($stats['orders']['total_revenue'] ?? 0, 2),
                'average_order_value' => number_format($stats['orders']['average_order_value'] ?? 0, 2),
                'recent_activity' => $stats['recent_activity'] ?? 0
            ];
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Install module
     */
    public function install() {
        $this->load->model('extension/mestech/hepsiburada');
        $this->model_extension_mestech_hepsiburada->install();
    }
    
    /**
     * Uninstall module
     */
    public function uninstall() {
        $this->load->model('extension/mestech/hepsiburada');
        $this->model_extension_mestech_hepsiburada->uninstall();
    }
    
    /**
     * Validate form data
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/mestech/hepsiburada')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (empty($this->request->post['mestech_hepsiburada_api_key'])) {
            $this->error['api_key'] = $this->language->get('error_api_key');
        }
        
        if (empty($this->request->post['mestech_hepsiburada_api_secret'])) {
            $this->error['api_secret'] = $this->language->get('error_api_secret');
        }
        
        if (empty($this->request->post['mestech_hepsiburada_merchant_id'])) {
            $this->error['merchant_id'] = $this->language->get('error_merchant_id');
        }
        
        return !$this->error;
    }
    
    /**
     * Get integrator instance
     */
    private function getIntegrator() {
        $api_key = $this->config->get('mestech_hepsiburada_api_key');
        $api_secret = $this->config->get('mestech_hepsiburada_api_secret');
        $merchant_id = $this->config->get('mestech_hepsiburada_merchant_id');
        $environment = $this->config->get('mestech_hepsiburada_environment') ?: 'sandbox';
        
        if (empty($api_key) || empty($api_secret) || empty($merchant_id)) {
            throw new Exception('API credentials are missing');
        }
        
        return new EntegratorHepsiburada([
            'api_key' => $api_key,
            'api_secret' => $api_secret,
            'merchant_id' => $merchant_id,
            'environment' => $environment
        ]);
    }
    
    /**
     * Get log level CSS class
     */
    private function getLogLevelClass($level) {
        $classes = [
            'error' => 'danger',
            'warning' => 'warning',
            'info' => 'info',
            'debug' => 'default',
            'success' => 'success'
        ];
        
        return $classes[$level] ?? 'default';
    }
}