<?php

/**
 * MesChain Trendyol Controller - OpenCart 4.x
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @since OpenCart 4.0.2.3
 */

namespace Opencart\Admin\Controller\Extension\Meschain;

class Trendyol extends \Opencart\System\Engine\Controller
{

    private $error = [];

    public function index(): void
    {
        $this->load->language('extension/meschain/trendyol');

        $this->document->setTitle($this->language->get('heading_title'));

        // Load models
        $this->load->model('extension/meschain/trendyol');
        $this->load->model('setting/setting');

        // Handle form submission
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('meschain_trendyol', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/meschain/trendyol', 'user_token=' . $this->session->data['user_token']));
        }

        // Get current settings
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('meschain_trendyol');

        // Prepare data for view
        $data = $this->getCommonData();
        $data = array_merge($data, $this->getFormData($settings));

        // Get API status
        $data['api_status'] = $this->getApiStatus();

        // Get statistics
        $data['statistics'] = $this->getStatistics();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/meschain/trendyol', $data));
    }

    public function dashboard(): void
    {
        $this->load->language('extension/meschain/trendyol');

        $this->document->setTitle($this->language->get('heading_title_dashboard'));

        $this->load->model('extension/meschain/trendyol');

        $data = $this->getCommonData();

        // Get dashboard data
        $data['orders_today'] = $this->model_extension_meschain_trendyol->getOrdersCount(['date_from' => date('Y-m-d')]);
        $data['orders_week'] = $this->model_extension_meschain_trendyol->getOrdersCount(['date_from' => date('Y-m-d', strtotime('-7 days'))]);
        $data['orders_month'] = $this->model_extension_meschain_trendyol->getOrdersCount(['date_from' => date('Y-m-d', strtotime('-30 days'))]);

        $data['products_active'] = $this->model_extension_meschain_trendyol->getProductsCount(['status' => 'active']);
        $data['products_pending'] = $this->model_extension_meschain_trendyol->getProductsCount(['status' => 'pending']);
        $data['products_rejected'] = $this->model_extension_meschain_trendyol->getProductsCount(['status' => 'rejected']);

        // Get recent orders
        $data['recent_orders'] = $this->model_extension_meschain_trendyol->getRecentOrders(10);

        // Get recent API logs
        $data['recent_logs'] = $this->model_extension_meschain_trendyol->getRecentApiLogs(10);

        // Get webhook status
        $data['webhook_status'] = $this->model_extension_meschain_trendyol->getWebhookStatus();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/meschain/trendyol_dashboard', $data));
    }

    public function products(): void
    {
        $this->load->language('extension/meschain/trendyol');

        $this->document->setTitle($this->language->get('heading_title_products'));

        $this->load->model('extension/meschain/trendyol');

        $data = $this->getCommonData();

        // Handle bulk actions
        if (isset($this->request->post['selected']) && isset($this->request->post['action'])) {
            $this->processBulkAction($this->request->post['action'], $this->request->post['selected']);
        }

        // Get filter parameters
        $filter_data = [
            'filter_name' => $this->request->get['filter_name'] ?? '',
            'filter_status' => $this->request->get['filter_status'] ?? '',
            'filter_barcode' => $this->request->get['filter_barcode'] ?? '',
            'start' => ($this->request->get['page'] ?? 1 - 1) * 20,
            'limit' => 20
        ];

        $data['products'] = $this->model_extension_meschain_trendyol->getProducts($filter_data);
        $data['total'] = $this->model_extension_meschain_trendyol->getTotalProducts($filter_data);

        // Pagination
        $pagination = new \Opencart\System\Library\Pagination();
        $pagination->total = $data['total'];
        $pagination->page = $this->request->get['page'] ?? 1;
        $pagination->limit = 20;
        $pagination->url = $this->url->link('extension/meschain/trendyol/products', 'user_token=' . $this->session->data['user_token'] . '&page={page}');

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($data['total']) ? (($this->request->get['page'] ?? 1 - 1) * 20) + 1 : 0, ((($this->request->get['page'] ?? 1 - 1) * 20) > ($data['total'] - 20)) ? $data['total'] : ((($this->request->get['page'] ?? 1 - 1) * 20) + 20), $data['total'], ceil($data['total'] / 20));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/meschain/trendyol_products', $data));
    }

    public function orders(): void
    {
        $this->load->language('extension/meschain/trendyol');

        $this->document->setTitle($this->language->get('heading_title_orders'));

        $this->load->model('extension/meschain/trendyol');

        $data = $this->getCommonData();

        // Get filter parameters
        $filter_data = [
            'filter_order_number' => $this->request->get['filter_order_number'] ?? '',
            'filter_status' => $this->request->get['filter_status'] ?? '',
            'filter_date_from' => $this->request->get['filter_date_from'] ?? '',
            'filter_date_to' => $this->request->get['filter_date_to'] ?? '',
            'start' => ($this->request->get['page'] ?? 1 - 1) * 20,
            'limit' => 20
        ];

        $data['orders'] = $this->model_extension_meschain_trendyol->getOrders($filter_data);
        $data['total'] = $this->model_extension_meschain_trendyol->getTotalOrders($filter_data);

        // Pagination
        $pagination = new \Opencart\System\Library\Pagination();
        $pagination->total = $data['total'];
        $pagination->page = $this->request->get['page'] ?? 1;
        $pagination->limit = 20;
        $pagination->url = $this->url->link('extension/meschain/trendyol/orders', 'user_token=' . $this->session->data['user_token'] . '&page={page}');

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($data['total']) ? (($this->request->get['page'] ?? 1 - 1) * 20) + 1 : 0, ((($this->request->get['page'] ?? 1 - 1) * 20) > ($data['total'] - 20)) ? $data['total'] : ((($this->request->get['page'] ?? 1 - 1) * 20) + 20), $data['total'], ceil($data['total'] / 20));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/meschain/trendyol_orders', $data));
    }

    public function testConnection(): void
    {
        $this->load->language('extension/meschain/trendyol');
        $this->load->model('extension/meschain/trendyol');

        $json = [];

        try {
            $result = $this->model_extension_meschain_trendyol->testApiConnection();

            if ($result['success']) {
                $json['success'] = $this->language->get('text_connection_success');
                $json['data'] = $result['data'];
            } else {
                $json['error'] = $result['error'];
            }
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function syncProducts(): void
    {
        $this->load->language('extension/meschain/trendyol');
        $this->load->model('extension/meschain/trendyol');

        $json = [];

        try {
            $result = $this->model_extension_meschain_trendyol->syncProducts();

            if ($result['success']) {
                $json['success'] = sprintf($this->language->get('text_sync_success'), $result['synced_count']);
            } else {
                $json['error'] = $result['error'];
            }
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function syncOrders(): void
    {
        $this->load->language('extension/meschain/trendyol');
        $this->load->model('extension/meschain/trendyol');

        $json = [];

        try {
            $result = $this->model_extension_meschain_trendyol->syncOrders();

            if ($result['success']) {
                $json['success'] = sprintf($this->language->get('text_sync_orders_success'), $result['synced_count']);
            } else {
                $json['error'] = $result['error'];
            }
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function getCommonData(): array
    {
        return [
            'breadcrumbs' => [
                [
                    'text' => $this->language->get('text_home'),
                    'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
                ],
                [
                    'text' => $this->language->get('text_extension'),
                    'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
                ],
                [
                    'text' => $this->language->get('heading_title'),
                    'href' => $this->url->link('extension/meschain/trendyol', 'user_token=' . $this->session->data['user_token'])
                ]
            ],
            'action' => $this->url->link('extension/meschain/trendyol', 'user_token=' . $this->session->data['user_token']),
            'cancel' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module'),
            'user_token' => $this->session->data['user_token'],
            'error_warning' => $this->error['warning'] ?? '',
        ];
    }

    private function getFormData(array $settings): array
    {
        return [
            'meschain_trendyol_status' => $settings['meschain_trendyol_status'] ?? false,
            'meschain_trendyol_api_key' => $settings['meschain_trendyol_api_key'] ?? '',
            'meschain_trendyol_api_secret' => $settings['meschain_trendyol_api_secret'] ?? '',
            'meschain_trendyol_supplier_id' => $settings['meschain_trendyol_supplier_id'] ?? '',
            'meschain_trendyol_test_mode' => $settings['meschain_trendyol_test_mode'] ?? true,
            'meschain_trendyol_auto_sync' => $settings['meschain_trendyol_auto_sync'] ?? false,
            'meschain_trendyol_webhook_secret' => $settings['meschain_trendyol_webhook_secret'] ?? '',
            'meschain_trendyol_debug' => $settings['meschain_trendyol_debug'] ?? false,
        ];
    }

    private function getApiStatus(): array
    {
        $this->load->model('extension/meschain/trendyol');

        try {
            $result = $this->model_extension_meschain_trendyol->testApiConnection();
            return [
                'connected' => $result['success'],
                'message' => $result['success'] ? 'API bağlantısı başarılı' : $result['error'],
                'last_check' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'connected' => false,
                'message' => $e->getMessage(),
                'last_check' => date('Y-m-d H:i:s')
            ];
        }
    }

    private function getStatistics(): array
    {
        $this->load->model('extension/meschain/trendyol');

        return [
            'total_products' => $this->model_extension_meschain_trendyol->getTotalProducts(),
            'active_products' => $this->model_extension_meschain_trendyol->getProductsCount(['status' => 'active']),
            'total_orders' => $this->model_extension_meschain_trendyol->getTotalOrders(),
            'orders_today' => $this->model_extension_meschain_trendyol->getOrdersCount(['date_from' => date('Y-m-d')]),
            'last_sync' => $this->model_extension_meschain_trendyol->getLastSyncTime()
        ];
    }

    private function processBulkAction(string $action, array $selected): void
    {
        $this->load->model('extension/meschain/trendyol');

        switch ($action) {
            case 'sync':
                foreach ($selected as $product_id) {
                    $this->model_extension_meschain_trendyol->syncProduct($product_id);
                }
                $this->session->data['success'] = $this->language->get('text_bulk_sync_success');
                break;

            case 'delete':
                foreach ($selected as $product_id) {
                    $this->model_extension_meschain_trendyol->deleteProduct($product_id);
                }
                $this->session->data['success'] = $this->language->get('text_bulk_delete_success');
                break;
        }
    }

    private function validate(): bool
    {
        if (!$this->user->hasPermission('modify', 'extension/meschain/trendyol')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['meschain_trendyol_api_key'])) {
            $this->error['api_key'] = $this->language->get('error_api_key');
        }

        if (empty($this->request->post['meschain_trendyol_api_secret'])) {
            $this->error['api_secret'] = $this->language->get('error_api_secret');
        }

        if (empty($this->request->post['meschain_trendyol_supplier_id'])) {
            $this->error['supplier_id'] = $this->language->get('error_supplier_id');
        }

        return !$this->error;
    }
}
