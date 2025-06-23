<?php
/**
 * MesChain-Sync React Frontend Controller
 * OpenCart 3.0.4.0 Compatible
 * 
 * @author MesChain Development Team
 * @version 3.1.0
 * @date June 2025
 */

class ControllerExtensionModuleMeschainReact extends Controller {
    
    private $error = array();
    
    /**
     * Main React Dashboard Index
     */
    public function index() {
        $this->load->language('extension/module/meschain_react');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Check user permissions
        if (!$this->user->hasPermission('modify', 'extension/module/meschain_react')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Load React assets
        $this->loadReactAssets();
        
        // Prepare user data for React
        $user_data = $this->prepareUserData();
        
        // Set template data
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/meschain_react', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['user_token'] = $this->session->data['user_token'];
        $data['user_data'] = json_encode($user_data);
        $data['api_base'] = HTTPS_SERVER . 'index.php?route=extension/module/meschain_api/';
        
        // Header and footer
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_react', $data));
    }
    
    /**
     * Load React CSS and JS assets
     */
    private function loadReactAssets() {
        // React CSS
        $this->document->addStyle('view/javascript/meschain-react/static/css/main.css');
        
        // React JS
        $this->document->addScript('view/javascript/meschain-react/static/js/main.js');
        
        // Chart.js for dashboard
        $this->document->addScript('view/javascript/meschain-react/static/js/chart.min.js');
        
        // Tailwind CSS
        $this->document->addStyle('https://cdn.tailwindcss.com');
        
        // Custom MesChain styles
        $this->document->addStyle('view/stylesheet/meschain-react.css');
    }
    
    /**
     * Prepare user data for React frontend
     */
    private function prepareUserData() {
        $user_role = $this->getUserRole();
        $permissions = $this->getUserPermissions();
        
        return array(
            'id' => $this->user->getId(),
            'username' => $this->user->getUserName(),
            'email' => $this->user->getEmail(),
            'role' => $user_role,
            'permissions' => $permissions,
            'token' => $this->session->data['user_token'],
            'language' => $this->config->get('config_language'),
            'timezone' => $this->config->get('config_timezone') ?: 'Europe/Istanbul'
        );
    }
    
    /**
     * Determine user role based on user group
     */
    private function getUserRole() {
        $user_group_id = $this->user->getGroupId();
        
        // Map OpenCart user groups to MesChain roles
        switch ($user_group_id) {
            case 1: // Administrator
                return 'super_admin';
            case 2: // Manager
                return 'admin';
            case 3: // Sales
                return 'dropshipper';
            case 4: // Support
                return 'support';
            default:
                return 'admin';
        }
    }
    
    /**
     * Get user permissions based on role
     */
    private function getUserPermissions() {
        $role = $this->getUserRole();
        
        $permissions = array();
        
        switch ($role) {
            case 'super_admin':
                $permissions = array(
                    'all',
                    'user_manage',
                    'system_config',
                    'api_manage',
                    'marketplace_manage',
                    'product_manage',
                    'order_manage',
                    'report_view',
                    'dropshipping_manage'
                );
                break;
                
            case 'admin':
                $permissions = array(
                    'marketplace_manage',
                    'product_manage',
                    'order_manage',
                    'report_view',
                    'dropshipping_manage'
                );
                break;
                
            case 'dropshipper':
                $permissions = array(
                    'product_view',
                    'order_view',
                    'profit_view',
                    'dropshipping_view'
                );
                break;
                
            case 'support':
                $permissions = array(
                    'user_support',
                    'ticket_manage',
                    'report_view'
                );
                break;
                
            default:
                $permissions = array('basic_access');
        }
        
        return $permissions;
    }
    
    /**
     * API endpoint for React frontend
     */
    public function api() {
        $this->load->language('extension/module/meschain_react');
        
        // Set JSON response headers
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $this->response->setOutput('');
            return;
        }
        
        // Get request data
        $input = json_decode(file_get_contents('php://input'), true);
        $action = isset($this->request->get['action']) ? $this->request->get['action'] : '';
        
        try {
            switch ($action) {
                case 'dashboard_metrics':
                    $response = $this->getDashboardMetrics();
                    break;
                    
                case 'marketplace_data':
                    $response = $this->getMarketplaceData();
                    break;
                    
                case 'user_list':
                    $response = $this->getUserList();
                    break;
                    
                case 'health_check':
                    $response = array('success' => true, 'message' => 'API is working');
                    break;
                    
                default:
                    $response = array('success' => false, 'error' => 'Invalid action');
            }
        } catch (Exception $e) {
            $response = array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
        
        $this->response->setOutput(json_encode($response));
    }
    
    /**
     * Get dashboard metrics for React frontend
     */
    private function getDashboardMetrics() {
        $this->load->model('sale/order');
        $this->load->model('catalog/product');
        
        // Get basic metrics
        $total_orders = $this->model_sale_order->getTotalOrders();
        $total_products = $this->model_catalog_product->getTotalProducts();
        
        // Calculate sales data (mock for now)
        $sales_data = array(
            'total_sales' => 285000,
            'monthly_sales' => array(45000, 52000, 48000, 61000, 58000, 65000),
            'orders_today' => 12,
            'orders_pending' => 5
        );
        
        return array(
            'success' => true,
            'data' => array(
                'sales' => $sales_data,
                'products' => array(
                    'total' => $total_products,
                    'active' => $total_products - 10,
                    'out_of_stock' => 10
                ),
                'orders' => array(
                    'total' => $total_orders,
                    'pending' => $sales_data['orders_pending'],
                    'today' => $sales_data['orders_today']
                ),
                'marketplaces' => array(
                    'connected' => 4,
                    'active' => 3,
                    'syncing' => 1
                )
            )
        );
    }
    
    /**
     * Get marketplace data
     */
    private function getMarketplaceData() {
        // Mock marketplace data
        $marketplaces = array(
            array(
                'name' => 'Amazon',
                'status' => 'connected',
                'products' => 1250,
                'orders' => 45,
                'revenue' => 125000,
                'last_sync' => date('Y-m-d H:i:s')
            ),
            array(
                'name' => 'Trendyol',
                'status' => 'connected',
                'products' => 890,
                'orders' => 32,
                'revenue' => 85000,
                'last_sync' => date('Y-m-d H:i:s')
            ),
            array(
                'name' => 'N11',
                'status' => 'syncing',
                'products' => 650,
                'orders' => 18,
                'revenue' => 45000,
                'last_sync' => date('Y-m-d H:i:s', strtotime('-5 minutes'))
            ),
            array(
                'name' => 'eBay',
                'status' => 'error',
                'products' => 420,
                'orders' => 8,
                'revenue' => 30000,
                'last_sync' => date('Y-m-d H:i:s', strtotime('-2 hours'))
            )
        );
        
        return array(
            'success' => true,
            'data' => $marketplaces
        );
    }
    
    /**
     * Get user list for admin
     */
    private function getUserList() {
        if (!$this->user->hasPermission('access', 'user/user')) {
            return array('success' => false, 'error' => 'Permission denied');
        }
        
        $this->load->model('user/user');
        $users = $this->model_user_user->getUsers();
        
        $user_list = array();
        foreach ($users as $user) {
            $user_list[] = array(
                'id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'status' => $user['status'] ? 'active' : 'inactive',
                'date_added' => $user['date_added']
            );
        }
        
        return array(
            'success' => true,
            'data' => $user_list
        );
    }
    
    /**
     * Install method
     */
    public function install() {
        // Create necessary database tables if needed
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_react_settings` (
                `setting_id` int(11) NOT NULL AUTO_INCREMENT,
                `key` varchar(64) NOT NULL,
                `value` text NOT NULL,
                `serialized` tinyint(1) NOT NULL DEFAULT '0',
                PRIMARY KEY (`setting_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Set default settings
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('meschain_react', array(
            'meschain_react_status' => 1,
            'meschain_react_version' => '3.1.0'
        ));
    }
    
    /**
     * Uninstall method
     */
    public function uninstall() {
        // Remove settings
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('meschain_react');
        
        // Drop table if needed
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_react_settings`");
    }
}
?> 