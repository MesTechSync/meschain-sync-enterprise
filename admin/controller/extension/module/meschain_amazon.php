<?php
namespace Opencart\Admin\Controller\Extension\Module;

/**
 * Amazon SP-API Integration Controller
 *
 * @package MesChain Sync Enterprise
 * @version 3.0.0
 * @author MesChain Development Team
 */
class MeschainAmazon extends \Opencart\System\Engine\Controller {
    private $error = array();

    /**
     * Main dashboard view
     */
    public function index(): void {
        $this->load->language('extension/module/meschain/amazon');
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
            'href' => $this->url->link('extension/module/meschain_amazon', 'user_token=' . $this->session->data['user_token'])
        );

        $data['action'] = $this->url->link('extension/module/meschain_amazon/save', 'user_token=' . $this->session->data['user_token']);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        // Load saved settings
        $this->load->model('setting/setting');
        $setting_info = $this->model_setting_setting->getSetting('module_meschain_amazon');

        // SP-API Configuration
        $data['selling_partner_id'] = $setting_info['module_meschain_amazon_selling_partner_id'] ?? '';
        $data['refresh_token'] = $setting_info['module_meschain_amazon_refresh_token'] ?? '';
        $data['client_id'] = $setting_info['module_meschain_amazon_client_id'] ?? '';
        $data['client_secret'] = $setting_info['module_meschain_amazon_client_secret'] ?? '';
        $data['aws_access_key'] = $setting_info['module_meschain_amazon_aws_access_key'] ?? '';
        $data['aws_secret_key'] = $setting_info['module_meschain_amazon_aws_secret_key'] ?? '';
        $data['marketplace_id'] = $setting_info['module_meschain_amazon_marketplace_id'] ?? '';
        $data['region'] = $setting_info['module_meschain_amazon_region'] ?? 'eu-west-1';
        $data['status'] = $setting_info['module_meschain_amazon_status'] ?? 0;

        // Multi-region support
        $data['regions'] = array(
            'us-east-1' => 'North America (US)',
            'eu-west-1' => 'Europe (UK/DE/FR/IT/ES)',
            'us-west-2' => 'North America (CA/MX)',
            'eu-central-1' => 'Europe (PL/SE/NL)',
            'ap-northeast-1' => 'Far East (JP)',
            'ap-southeast-1' => 'Asia Pacific (SG/AU)',
            'ap-south-1' => 'India'
        );

        // Get marketplace statistics
        $data['stats'] = $this->getMarketplaceStats();

        // API URLs for AJAX calls
        $data['sync_products_url'] = $this->url->link('extension/module/meschain_amazon/syncProducts', 'user_token=' . $this->session->data['user_token']);
        $data['sync_orders_url'] = $this->url->link('extension/module/meschain_amazon/syncOrders', 'user_token=' . $this->session->data['user_token']);
        $data['sync_inventory_url'] = $this->url->link('extension/module/meschain_amazon/syncInventory', 'user_token=' . $this->session->data['user_token']);
        $data['test_connection_url'] = $this->url->link('extension/module/meschain_amazon/testConnection', 'user_token=' . $this->session->data['user_token']);
        $data['get_categories_url'] = $this->url->link('extension/module/meschain_amazon/getCategories', 'user_token=' . $this->session->data['user_token']);
        $data['fba_inventory_url'] = $this->url->link('extension/module/meschain_amazon/getFBAInventory', 'user_token=' . $this->session->data['user_token']);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/meschain/marketplace/amazon', $data));
    }

    /**
     * Save settings
     */
    public function save(): void {
        $this->load->language('extension/module/meschain/amazon');

        $json = array();

        if (!$this->user->hasPermission('modify', 'extension/module/meschain_amazon')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');

            $settings = array(
                'module_meschain_amazon_selling_partner_id' => $this->request->post['selling_partner_id'] ?? '',
                'module_meschain_amazon_refresh_token' => $this->request->post['refresh_token'] ?? '',
                'module_meschain_amazon_client_id' => $this->request->post['client_id'] ?? '',
                'module_meschain_amazon_client_secret' => $this->request->post['client_secret'] ?? '',
                'module_meschain_amazon_aws_access_key' => $this->request->post['aws_access_key'] ?? '',
                'module_meschain_amazon_aws_secret_key' => $this->request->post['aws_secret_key'] ?? '',
                'module_meschain_amazon_marketplace_id' => $this->request->post['marketplace_id'] ?? '',
                'module_meschain_amazon_region' => $this->request->post['region'] ?? 'eu-west-1',
                'module_meschain_amazon_status' => $this->request->post['status'] ?? 0
            );

            $this->model_setting_setting->editSetting('module_meschain_amazon', $settings);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Test Amazon SP-API connection
     */
    public function testConnection(): void {
        $this->load->language('extension/module/meschain/amazon');

        $json = array();

        try {
            $this->load->library('meschain/api/amazon');

            $api = new \MesChain\Api\Amazon($this->getApiConfig());
            $result = $api->testConnection();

            if ($result['success']) {
                $json['success'] = $this->language->get('text_connection_success');
                $json['info'] = $result['info'] ?? [];
                $json['marketplace'] = $result['marketplace'] ?? '';
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
     * Sync products with Amazon
     */
    public function syncProducts(): void {
        $this->load->language('extension/module/meschain/amazon');
        $this->load->model('extension/module/meschain/amazon');

        $json = array();

        try {
            $filter = array(
                'start' => $this->request->get['start'] ?? 0,
                'limit' => $this->request->get['limit'] ?? 50,
                'marketplace_id' => $this->request->get['marketplace_id'] ?? ''
            );

            $result = $this->model_extension_module_meschain_amazon->syncProducts($filter);

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
     * Sync orders from Amazon
     */
    public function syncOrders(): void {
        $this->load->language('extension/module/meschain/amazon');
        $this->load->model('extension/module/meschain/amazon');

        $json = array();

        try {
            $filter = array(
                'created_after' => $this->request->get['created_after'] ?? date('c', strtotime('-7 days')),
                'created_before' => $this->request->get['created_before'] ?? date('c'),
                'order_statuses' => $this->request->get['order_statuses'] ?? ['Unshipped', 'PartiallyShipped']
            );

            $result = $this->model_extension_module_meschain_amazon->syncOrders($filter);

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
     * Sync inventory with Amazon
     */
    public function syncInventory(): void {
        $this->load->language('extension/module/meschain/amazon');
        $this->load->model('extension/module/meschain/amazon');

        $json = array();

        try {
            $result = $this->model_extension_module_meschain_amazon->syncInventory();

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
     * Get FBA inventory
     */
    public function getFBAInventory(): void {
        $this->load->language('extension/module/meschain/amazon');
        $this->load->model('extension/module/meschain/amazon');

        $json = array();

        try {
            $result = $this->model_extension_module_meschain_amazon->getFBAInventory();

            if ($result['success']) {
                $json['inventory'] = $result['inventory'] ?? [];
                $json['summary'] = $result['summary'] ?? [];
                $json['total'] = count($json['inventory']);
            } else {
                $json['error'] = $result['error'] ?? $this->language->get('error_fba_inventory');
            }
        } catch (\Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Get Amazon categories for mapping
     */
    public function getCategories(): void {
        $this->load->language('extension/module/meschain/amazon');

        $json = array();

        try {
            $this->load->library('meschain/api/amazon');

            $api = new \MesChain\Api\Amazon($this->getApiConfig());
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
        $this->load->model('extension/module/meschain/amazon');

        return $this->model_extension_module_meschain_amazon->getStats();
    }

    /**
     * Get API configuration
     */
    private function getApiConfig(): array {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_meschain_amazon');

        return array(
            'selling_partner_id' => $settings['module_meschain_amazon_selling_partner_id'] ?? '',
            'refresh_token' => $settings['module_meschain_amazon_refresh_token'] ?? '',
            'client_id' => $settings['module_meschain_amazon_client_id'] ?? '',
            'client_secret' => $settings['module_meschain_amazon_client_secret'] ?? '',
            'aws_access_key' => $settings['module_meschain_amazon_aws_access_key'] ?? '',
            'aws_secret_key' => $settings['module_meschain_amazon_aws_secret_key'] ?? '',
            'marketplace_id' => $settings['module_meschain_amazon_marketplace_id'] ?? '',
            'region' => $settings['module_meschain_amazon_region'] ?? 'eu-west-1'
        );
    }

    /**
     * Install module
     */
    public function install(): void {
        $this->load->model('extension/module/meschain/amazon');
        $this->model_extension_module_meschain_amazon->install();
    }

    /**
     * Uninstall module
     */
    public function uninstall(): void {
        $this->load->model('extension/module/meschain/amazon');
        $this->model_extension_module_meschain_amazon->uninstall();
    }
}
