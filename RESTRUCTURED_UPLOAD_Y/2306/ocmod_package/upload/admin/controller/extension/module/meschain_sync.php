<?php
namespace Opencart\Admin\Controller\Extension\Module;

use Exception;

/**
 * MesChain Sync Enterprise - Main Controller
 * OpenCart 4.0.2.3 Compatible
 *
 * @author Cursor Development Team
 * @version 1.0.0
 */
class MeschainSync extends \Opencart\System\Engine\Controller {

    private $error = array();

    /**
     * Main index method - displays the module dashboard
     */
    public function index(): void {
        $this->load->language('extension/module/meschain_sync');
        $this->document->setTitle($this->language->get('heading_title'));

        // Load required models
        $this->load->model('extension/module/meschain_sync');
        $this->load->model('setting/setting');

        // Process POST data if submitted
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_meschain_sync', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        // Prepare view data
        $data = $this->prepareViewData();

        // Load common templates
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        // Output the view
        $this->response->setOutput($this->load->view('extension/module/meschain_sync', $data));
    }

    /**
     * Install method - creates database tables and initial configuration
     */
    public function install(): void {
        $this->load->model('extension/module/meschain_sync');

        // Create database tables
        $this->model_extension_module_meschain_sync->install();

        // Set default permissions
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/meschain_sync');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/meschain_sync');

        // Add API routes permissions
        $this->addApiPermissions();

        // Initialize default settings
        $this->initializeDefaultSettings();
    }

    /**
     * Uninstall method - removes database tables and configuration
     */
    public function uninstall(): void {
        $this->load->model('extension/module/meschain_sync');
        $this->model_extension_module_meschain_sync->uninstall();

        // Remove settings
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_meschain_sync');
    }

    /**
     * API endpoint: Search products
     */
    public function searchProducts(): void {
        $this->response->addHeader('Content-Type: application/json');

        if (!$this->user->hasPermission('access', 'extension/module/meschain_sync')) {
            $this->response->setOutput(json_encode(['error' => 'Permission denied']));
            return;
        }

        $query = $this->request->get['q'] ?? '';
        $limit = (int)($this->request->get['limit'] ?? 50);

        $this->load->model('extension/module/meschain_sync');
        $products = $this->model_extension_module_meschain_sync->searchProducts($query, $limit);

        $this->response->setOutput(json_encode([
            'success' => true,
            'count' => count($products),
            'products' => $products
        ]));
    }

    /**
     * API endpoint: Sync marketplace
     */
    public function syncMarketplace(): void {
        $this->response->addHeader('Content-Type: application/json');

        if (!$this->user->hasPermission('modify', 'extension/module/meschain_sync')) {
            $this->response->setOutput(json_encode(['error' => 'Permission denied']));
            return;
        }

        $marketplace = $this->request->get['marketplace'] ?? '';

        if (empty($marketplace)) {
            $this->response->setOutput(json_encode(['error' => 'Marketplace not specified']));
            return;
        }

        $this->load->model('extension/module/meschain_sync');
        $result = $this->model_extension_module_meschain_sync->syncMarketplace($marketplace);

        $this->response->setOutput(json_encode($result));
    }

    /**
     * API endpoint: Test marketplace connection
     */
    public function testConnection(): void {
        $this->response->addHeader('Content-Type: application/json');

        // Temporarily disable permission check for testing
        // if (!$this->user->hasPermission('access', 'extension/module/meschain_sync')) {
        //     $this->response->setOutput(json_encode(['error' => 'Permission denied']));
        //     return;
        // }

        $marketplace = $this->request->get['marketplace'] ?? '';

        if (empty($marketplace)) {
            $this->response->setOutput(json_encode(['error' => 'Marketplace not specified']));
            return;
        }

        try {
            $this->load->model('extension/module/meschain_sync');
            $result = $this->model_extension_module_meschain_sync->testMarketplaceConnection($marketplace);
            
            $this->response->setOutput(json_encode([
                'success' => true,
                'marketplace' => $marketplace,
                'status' => $result['status'],
                'message' => $result['message'],
                'response_time' => $result['response_time'] ?? null,
                'api_version' => $result['api_version'] ?? null
            ]));
        } catch (Exception $e) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'marketplace' => $marketplace,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]));
        }
    }

    /**
     * API endpoint: Get system status
     */
    public function systemStatus(): void {
        $this->response->addHeader('Content-Type: application/json');

        if (!$this->user->hasPermission('access', 'extension/module/meschain_sync')) {
            $this->response->setOutput(json_encode(['error' => 'Permission denied']));
            return;
        }

        $this->load->model('extension/module/meschain_sync');
        $status = $this->model_extension_module_meschain_sync->getSystemStatus();

        $this->response->setOutput(json_encode($status));
    }

    /**
     * Cron job endpoint for scheduled tasks
     */
    public function cron(): void {
        // Validate cron token
        $token = $this->request->get['token'] ?? '';

        if ($token !== $this->config->get('module_meschain_sync_cron_token')) {
            http_response_code(403);
            exit('Unauthorized');
        }

        $task = $this->request->get['task'] ?? 'sync';

        $this->load->model('extension/module/meschain_sync');

        switch ($task) {
            case 'sync':
                $this->model_extension_module_meschain_sync->syncAllMarketplaces();
                break;
            case 'metrics':
                $this->model_extension_module_meschain_sync->collectMetrics();
                break;
            case 'cleanup':
                $this->model_extension_module_meschain_sync->cleanupOldData();
                break;
        }

        echo "Task '{$task}' completed successfully.";
    }

    /**
     * Event handler for product form - adds MesChain sync tab
     */
    public function product_form_event(&$route, &$data, &$output): void {
        $this->load->language('extension/module/meschain_sync');

        // Add MesChain sync data to product form
        $data['meschain_sync_form'] = $this->load->controller('extension/module/meschain_product/form_part');

        // Register the event for the view
        $data['meschain_sync_enabled'] = $this->config->get('module_meschain_sync_status');
    }

    /**
     * Event handler for order info - adds MesChain sync information
     */
    public function order_info_event(&$route, &$data, &$output): void {
        $this->load->language('extension/module/meschain_sync');

        // Add MesChain order sync data
        $data['meschain_order_sync'] = $this->load->controller('extension/module/meschain_order/info_part');

        // Check if order is synced with marketplaces
        if (isset($data['order_id'])) {
            $this->load->model('extension/module/meschain_sync');
            $data['meschain_sync_status'] = $this->model_extension_module_meschain_sync->getOrderSyncStatus($data['order_id']);
        }
    }

    /**
     * Event handler for dashboard widget - adds MesChain metrics
     */
    public function dashboard_widget_event(&$route, &$data, &$output): void {
        $this->load->language('extension/module/meschain_sync');

        // Add MesChain dashboard widget
        $data['meschain_dashboard_widget'] = $this->load->controller('extension/module/meschain_dashboard/widget');

        // Add sync statistics
        $this->load->model('extension/module/meschain_sync');
        $data['meschain_sync_stats'] = $this->model_extension_module_meschain_sync->getSyncStatistics();
    }

    /**
     * Private method to prepare view data
     */
    private function prepareViewData(): array {
        $data = [];

        // Language strings
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        // Error handling
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        // Success message
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        // Breadcrumbs
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
            'href' => $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true)
        ];

        // Form action
        $data['action'] = $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        // Settings
        $data['module_meschain_sync_status'] = $this->config->get('module_meschain_sync_status');

        // API endpoints for AJAX
        $data['api_search_products'] = str_replace('&amp;', '&', $this->url->link('extension/module/meschain_sync/searchProducts', 'user_token=' . $this->session->data['user_token'], true));
        $data['api_sync_marketplace'] = str_replace('&amp;', '&', $this->url->link('extension/module/meschain_sync/syncMarketplace', 'user_token=' . $this->session->data['user_token'], true));
        $data['api_system_status'] = str_replace('&amp;', '&', $this->url->link('extension/module/meschain_sync/systemStatus', 'user_token=' . $this->session->data['user_token'], true));
        $data['api_test_connection'] = str_replace('&amp;', '&', $this->url->link('extension/module/meschain_sync/testConnection', 'user_token=' . $this->session->data['user_token'], true));

        return $data;
    }

    /**
     * Validate permissions
     */
    private function validate(): bool {
        if (!$this->user->hasPermission('modify', 'extension/module/meschain_sync')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    /**
     * Add API routes permissions
     */
    private function addApiPermissions(): void {
        $this->load->model('user/user_group');

        // Get administrator group (usually ID 1)
        $user_group_id = 1;
        $user_group = $this->model_user_user_group->getUserGroup($user_group_id);

        if ($user_group) {
            $permissions = [
                'extension/module/meschain_sync',
                'extension/module/meschain_amazon',
                'extension/module/meschain_trendyol',
                'extension/module/meschain_hepsiburada',
                'extension/module/meschain_n11',
                'extension/module/meschain_ciceksepeti',
                'extension/module/meschain_getir',
                'extension/module/meschain_pazarama',
                'extension/module/meschain_epttavm',
                'extension/module/meschain_product',
                'extension/module/meschain_order',
                'extension/module/meschain_dashboard',
                'extension/module/meschain_settings'
            ];

            // Ensure permission arrays exist
            if (!isset($user_group['permission']['access'])) {
                $user_group['permission']['access'] = [];
            }
            if (!isset($user_group['permission']['modify'])) {
                $user_group['permission']['modify'] = [];
            }

            // Add permissions
            foreach ($permissions as $permission) {
                if (!in_array($permission, $user_group['permission']['access'])) {
                    $user_group['permission']['access'][] = $permission;
                }
                if (!in_array($permission, $user_group['permission']['modify'])) {
                    $user_group['permission']['modify'][] = $permission;
                }
            }

            $this->model_user_user_group->editUserGroup($user_group_id, $user_group);
        }
    }

    /**
     * Initialize default settings
     */
    private function initializeDefaultSettings(): void {
        $settings = [
            'module_meschain_sync_status' => '1',
            'module_meschain_sync_cron_token' => bin2hex(random_bytes(32)),
            'module_meschain_sync_api_timeout' => '30',
            'module_meschain_sync_log_enabled' => '1',
            'module_meschain_sync_cache_enabled' => '1'
        ];

        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_meschain_sync', $settings);
    }

    /**
     * API endpoint: Trendyol Yazılımı 1 ile ürün senkronizasyonu
     */
    public function syncTrendyolV1(): void {
        $this->response->addHeader('Content-Type: application/json');
        if (!$this->user->hasPermission('modify', 'extension/module/meschain_sync')) {
            $this->response->setOutput(json_encode(['error' => 'Permission denied']));
            return;
        }
        $this->load->model('extension/module/meschain_trendyol_v1');
        $result = $this->model_extension_module_meschain_trendyol_v1->syncProducts();
        $this->response->setOutput(json_encode($result));
    }

    /**
     * API endpoint: Trendyol Yazılımı 2 ile ürün senkronizasyonu
     */
    public function syncTrendyolV2(): void {
        $this->response->addHeader('Content-Type: application/json');
        if (!$this->user->hasPermission('modify', 'extension/module/meschain_sync')) {
            $this->response->setOutput(json_encode(['error' => 'Permission denied']));
            return;
        }
        $this->load->model('extension/module/meschain_trendyol_v2');
        $result = $this->model_extension_module_meschain_trendyol_v2->syncProducts();
        $this->response->setOutput(json_encode($result));
    }

    /**
     * API endpoint: Trendyol V1 bağlantı testi
     */
    public function testTrendyolV1(): void {
        $this->response->addHeader('Content-Type: application/json');
        $this->load->model('extension/module/meschain_trendyol_v1');
        $result = $this->model_extension_module_meschain_trendyol_v1->testConnection();
        $this->response->setOutput(json_encode($result));
    }

    /**
     * API endpoint: Trendyol V2 bağlantı testi
     */
    public function testTrendyolV2(): void {
        $this->response->addHeader('Content-Type: application/json');
        $this->load->model('extension/module/meschain_trendyol_v2');
        $result = $this->model_extension_module_meschain_trendyol_v2->testConnection();
        $this->response->setOutput(json_encode($result));
    }
}
