<?php
namespace Opencart\Admin\Controller\Extension\Module;

/**
 * N11 Marketplace Integration Controller
 *
 * @package MesChain Sync Enterprise
 * @version 3.0.0
 * @author MesChain Development Team
 */
class MeschainN11 extends \Opencart\System\Engine\Controller {
    private $error = array();

    /**
     * Main dashboard view
     */
    public function index(): void {
        $this->load->language('extension/module/meschain/n11');
        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/meschain_n11', 'user_token=' . $this->session->data['user_token'])
        );

        $data['action'] = $this->url->link('extension/module/meschain_n11/save', 'user_token=' . $this->session->data['user_token']);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        // Load saved settings
        $this->load->model('setting/setting');
        $setting_info = $this->model_setting_setting->getSetting('module_meschain_n11');

        // API Configuration
        $data['api_key'] = $setting_info['module_meschain_n11_api_key'] ?? '';
        $data['api_secret'] = $setting_info['module_meschain_n11_api_secret'] ?? '';
        $data['app_key'] = $setting_info['module_meschain_n11_app_key'] ?? '';
        $data['app_secret'] = $setting_info['module_meschain_n11_app_secret'] ?? '';
        $data['status'] = $setting_info['module_meschain_n11_status'] ?? 0;

        // Get marketplace statistics
        $data['stats'] = $this->getMarketplaceStats();

        // API URLs for AJAX calls
        $data['sync_products_url'] = $this->url->link('extension/module/meschain_n11/syncProducts', 'user_token=' . $this->session->data['user_token']);
        $data['sync_orders_url'] = $this->url->link('extension/module/meschain_n11/syncOrders', 'user_token=' . $this->session->data['user_token']);
        $data['sync_inventory_url'] = $this->url->link('extension/module/meschain_n11/syncInventory', 'user_token=' . $this->session->data['user_token']);
        $data['test_connection_url'] = $this->url->link('extension/module/meschain_n11/testConnection', 'user_token=' . $this->session->data['user_token']);
        $data['get_categories_url'] = $this->url->link('extension/module/meschain_n11/getCategories', 'user_token=' . $this->session->data['user_token']);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/meschain/marketplace/n11', $data));
    }

    /**
     * Save settings
     */
    public function save(): void {
        $this->load->language('extension/module/meschain/n11');

        $json = array();

        if (!$this->user->hasPermission('modify', 'extension/module/meschain_n11')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');

            $settings = array(
                'module_meschain_n11_api_key' => $this->request->post['api_key'] ?? '',
                'module_meschain_n11_api_secret' => $this->request->post['api_secret'] ?? '',
                'module_meschain_n11_app_key' => $this->request->post['app_key'] ?? '',
                'module_meschain_n11_app_secret' => $this->request->post['app_secret'] ?? '',
                'module_meschain_n11_status' => $this->request->post['status'] ?? 0
            );

            $this->model_setting_setting->editSetting('module_meschain_n11', $settings);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Test N11 API connection
     */
    public function testConnection(): void {
        $this->load->language('extension/module/meschain/n11');

        $json = array();

        try {
            $this->load->library('meschain/api/n11');

            $api = new \MesChain\Api\N11($this->getApiConfig());
            $result = $api->testConnection();

            if ($result['success']) {
                $json['success'] = $this->language->get('text_connection_success');
                $json['info'] = $result['info'] ?? [];
            } else {
                $json['error'] = $result['error'] ?? $this->language->get('error_connection');
            }
        } catch (\Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Sync products with N11
     */
    public function syncProducts(): void {
        $this->load->language('extension/module/meschain/n11');
        $this->load->model('extension/module/meschain/n11');

        $json = array();

        try {
            $filter = array(
                'start' => $this->request->get['start'] ?? 0,
                'limit' => $this->request->get['limit'] ?? 50
            );

            $result = $this->model_extension_module_meschain_n11->syncProducts($filter);

            if ($result['success']) {
                $json['success'] = sprintf($this->language->get('text_sync_success'), $result['synced_count']);
                $json['synced'] = $result['synced_count'];
                $json['failed'] = $result['failed_count'];
                $json['details'] = $result['details'] ?? [];
            } else {
                $json['error'] = $result['error'] ?? $this->language->get('error_sync_failed');
            }
        } catch (\Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Sync orders from N11
     */
    public function syncOrders(): void {
        $this->load->language('extension/module/meschain/n11');
        $this->load->model('extension/module/meschain/n11');

        $json = array();

        try {
            $filter = array(
                'status' => $this->request->get['status'] ?? 'New',
                'start_date' => $this->request->get['start_date'] ?? date('Y-m-d', strtotime('-7 days')),
                'end_date' => $this->request->get['end_date'] ?? date('Y-m-d')
            );

            $result = $this->model_extension_module_meschain_n11->syncOrders($filter);

            if ($result['success']) {
                $json['success'] = sprintf($this->language->get('text_orders_synced'), $result['order_count']);
                $json['orders'] = $result['orders'] ?? [];
                $json['total'] = $result['total'] ?? 0;
            } else {
                $json['error'] = $result['error'] ?? $this->language->get('error_order_sync');
            }
        } catch (\Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Sync inventory with N11
     */
    public function syncInventory(): void {
        $this->load->language('extension/module/meschain/n11');
        $this->load->model('extension/module/meschain/n11');

        $json = array();

        try {
            $result = $this->model_extension_module_meschain_n11->syncInventory();

            if ($result['success']) {
                $json['success'] = $this->language->get('text_inventory_synced');
                $json['updated'] = $result['updated_count'] ?? 0;
                $json['details'] = $result['details'] ?? [];
            } else {
                $json['error'] = $result['error'] ?? $this->language->get('error_inventory_sync');
            }
        } catch (\Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Get N11 categories for mapping
     */
    public function getCategories(): void {
        $this->load->language('extension/module/meschain/n11');

        $json = array();

        try {
            $this->load->library('meschain/api/n11');

            $api = new \MesChain\Api\N11($this->getApiConfig());
            $result = $api->getCategories();

            if ($result['success']) {
                $json['categories'] = $result['categories'] ?? [];
                $json['total'] = count($json['categories']);
            } else {
                $json['error'] = $result['error'] ?? $this->language->get('error_categories');
            }
        } catch (\Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Get marketplace statistics
     */
    private function getMarketplaceStats(): array {
        $this->load->model('extension/module/meschain/n11');

        return $this->model_extension_module_meschain_n11->getStats();
    }

    /**
     * Get API configuration
     */
    private function getApiConfig(): array {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_meschain_n11');

        return array(
            'api_key' => $settings['module_meschain_n11_api_key'] ?? '',
            'api_secret' => $settings['module_meschain_n11_api_secret'] ?? '',
            'app_key' => $settings['module_meschain_n11_app_key'] ?? '',
            'app_secret' => $settings['module_meschain_n11_app_secret'] ?? ''
        );
    }

    /**
     * Install module
     */
    public function install(): void {
        $this->load->model('extension/module/meschain/n11');
        $this->model_extension_module_meschain_n11->install();
    }

    /**
     * Uninstall module
     */
    public function uninstall(): void {
        $this->load->model('extension/module/meschain/n11');
        $this->model_extension_module_meschain_n11->uninstall();
    }
}
