<?php
namespace Opencart\Admin\Controller\Extension\Module;

/**
 * eBay Marketplace Integration Controller
 *
 * @package MesChain Sync Enterprise
 * @version 3.0.0
 * @author MesChain Development Team
 */
class MeschainEbay extends \Opencart\System\Engine\Controller {
    private $error = array();

    /**
     * Main dashboard view
     */
    public function index(): void {
        $this->load->language('extension/module/meschain/ebay');
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
            'href' => $this->url->link('extension/module/meschain_ebay', 'user_token=' . $this->session->data['user_token'])
        );

        $data['action'] = $this->url->link('extension/module/meschain_ebay/save', 'user_token=' . $this->session->data['user_token']);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        // Load saved settings
        $this->load->model('setting/setting');
        $setting_info = $this->model_setting_setting->getSetting('module_meschain_ebay');

        // eBay API Configuration
        $data['app_id'] = $setting_info['module_meschain_ebay_app_id'] ?? '';
        $data['cert_id'] = $setting_info['module_meschain_ebay_cert_id'] ?? '';
        $data['dev_id'] = $setting_info['module_meschain_ebay_dev_id'] ?? '';
        $data['auth_token'] = $setting_info['module_meschain_ebay_auth_token'] ?? '';
        $data['site_id'] = $setting_info['module_meschain_ebay_site_id'] ?? '0';
        $data['status'] = $setting_info['module_meschain_ebay_status'] ?? 0;

        // eBay Sites
        $data['sites'] = array(
            '0' => 'United States',
            '3' => 'United Kingdom',
            '77' => 'Germany',
            '71' => 'France',
            '101' => 'Italy',
            '186' => 'Spain',
            '2' => 'Canada',
            '15' => 'Australia'
        );

        // Get marketplace statistics
        $data['stats'] = $this->getMarketplaceStats();

        // API URLs for AJAX calls
        $data['sync_products_url'] = $this->url->link('extension/module/meschain_ebay/syncProducts', 'user_token=' . $this->session->data['user_token']);
        $data['sync_orders_url'] = $this->url->link('extension/module/meschain_ebay/syncOrders', 'user_token=' . $this->session->data['user_token']);
        $data['test_connection_url'] = $this->url->link('extension/module/meschain_ebay/testConnection', 'user_token=' . $this->session->data['user_token']);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/meschain/marketplace/ebay', $data));
    }

    /**
     * Save settings
     */
    public function save(): void {
        $this->load->language('extension/module/meschain/ebay');

        $json = array();

        if (!$this->user->hasPermission('modify', 'extension/module/meschain_ebay')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');

            $settings = array(
                'module_meschain_ebay_app_id' => $this->request->post['app_id'] ?? '',
                'module_meschain_ebay_cert_id' => $this->request->post['cert_id'] ?? '',
                'module_meschain_ebay_dev_id' => $this->request->post['dev_id'] ?? '',
                'module_meschain_ebay_auth_token' => $this->request->post['auth_token'] ?? '',
                'module_meschain_ebay_site_id' => $this->request->post['site_id'] ?? '0',
                'module_meschain_ebay_status' => $this->request->post['status'] ?? 0
            );

            $this->model_setting_setting->editSetting('module_meschain_ebay', $settings);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Test eBay API connection
     */
    public function testConnection(): void {
        $this->load->language('extension/module/meschain/ebay');

        $json = array();

        try {
            $this->load->library('meschain/api/ebay');

            $api = new \MesChain\Api\Ebay($this->getApiConfig());
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
     * Sync products with eBay
     */
    public function syncProducts(): void {
        $this->load->language('extension/module/meschain/ebay');
        $this->load->model('extension/module/meschain/ebay');

        $json = array();

        try {
            $filter = array(
                'start' => $this->request->get['start'] ?? 0,
                'limit' => $this->request->get['limit'] ?? 50
            );

            $result = $this->model_extension_module_meschain_ebay->syncProducts($filter);

            if ($result['success']) {
                $json['success'] = sprintf($this->language->get('text_sync_success'), $result['synced_count']);
                $json['synced'] = $result['synced_count'];
                $json['failed'] = $result['failed_count'];
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
     * Sync orders from eBay
     */
    public function syncOrders(): void {
        $this->load->language('extension/module/meschain/ebay');
        $this->load->model('extension/module/meschain/ebay');

        $json = array();

        try {
            $result = $this->model_extension_module_meschain_ebay->syncOrders();

            if ($result['success']) {
                $json['success'] = sprintf($this->language->get('text_orders_synced'), $result['order_count']);
                $json['orders'] = $result['orders'] ?? [];
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
     * Get marketplace statistics
     */
    private function getMarketplaceStats(): array {
        $this->load->model('extension/module/meschain/ebay');

        return $this->model_extension_module_meschain_ebay->getStats();
    }

    /**
     * Get API configuration
     */
    private function getApiConfig(): array {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_meschain_ebay');

        return array(
            'app_id' => $settings['module_meschain_ebay_app_id'] ?? '',
            'cert_id' => $settings['module_meschain_ebay_cert_id'] ?? '',
            'dev_id' => $settings['module_meschain_ebay_dev_id'] ?? '',
            'auth_token' => $settings['module_meschain_ebay_auth_token'] ?? '',
            'site_id' => $settings['module_meschain_ebay_site_id'] ?? '0'
        );
    }

    /**
     * Install module
     */
    public function install(): void {
        $this->load->model('extension/module/meschain/ebay');
        $this->model_extension_module_meschain_ebay->install();
    }

    /**
     * Uninstall module
     */
    public function uninstall(): void {
        $this->load->model('extension/module/meschain/ebay');
        $this->model_extension_module_meschain_ebay->uninstall();
    }
}
