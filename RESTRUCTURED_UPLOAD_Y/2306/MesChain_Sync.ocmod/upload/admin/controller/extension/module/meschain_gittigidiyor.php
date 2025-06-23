<?php
namespace Opencart\Admin\Controller\Extension\Module;

/**
 * GittiGidiyor Marketplace Integration Controller
 *
 * @package MesChain Sync Enterprise
 * @version 3.0.0
 * @author MesChain Development Team
 */
class MeschainGittigidiyor extends \Opencart\System\Engine\Controller {
    private $error = array();

    /**
     * Main dashboard view
     */
    public function index(): void {
        $this->load->language('extension/module/meschain/gittigidiyor');
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
            'href' => $this->url->link('extension/module/meschain_gittigidiyor', 'user_token=' . $this->session->data['user_token'])
        );

        $data['action'] = $this->url->link('extension/module/meschain_gittigidiyor/save', 'user_token=' . $this->session->data['user_token']);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        // Load saved settings
        $this->load->model('setting/setting');
        $setting_info = $this->model_setting_setting->getSetting('module_meschain_gittigidiyor');

        // API Configuration
        $data['api_key'] = $setting_info['module_meschain_gittigidiyor_api_key'] ?? '';
        $data['api_secret'] = $setting_info['module_meschain_gittigidiyor_api_secret'] ?? '';
        $data['username'] = $setting_info['module_meschain_gittigidiyor_username'] ?? '';
        $data['status'] = $setting_info['module_meschain_gittigidiyor_status'] ?? 0;

        // Get marketplace statistics
        $data['stats'] = $this->getMarketplaceStats();

        // API URLs for AJAX calls
        $data['sync_products_url'] = $this->url->link('extension/module/meschain_gittigidiyor/syncProducts', 'user_token=' . $this->session->data['user_token']);
        $data['sync_orders_url'] = $this->url->link('extension/module/meschain_gittigidiyor/syncOrders', 'user_token=' . $this->session->data['user_token']);
        $data['test_connection_url'] = $this->url->link('extension/module/meschain_gittigidiyor/testConnection', 'user_token=' . $this->session->data['user_token']);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/meschain/marketplace/gittigidiyor', $data));
    }

    /**
     * Save settings
     */
    public function save(): void {
        $this->load->language('extension/module/meschain/gittigidiyor');

        $json = array();

        if (!$this->user->hasPermission('modify', 'extension/module/meschain_gittigidiyor')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');

            $settings = array(
                'module_meschain_gittigidiyor_api_key' => $this->request->post['api_key'] ?? '',
                'module_meschain_gittigidiyor_api_secret' => $this->request->post['api_secret'] ?? '',
                'module_meschain_gittigidiyor_username' => $this->request->post['username'] ?? '',
                'module_meschain_gittigidiyor_status' => $this->request->post['status'] ?? 0
            );

            $this->model_setting_setting->editSetting('module_meschain_gittigidiyor', $settings);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Test API connection
     */
    public function testConnection(): void {
        $this->load->language('extension/module/meschain/gittigidiyor');

        $json = array();

        try {
            $this->load->library('meschain/api/gittigidiyor');

            $api = new \MesChain\Api\GittiGidiyor($this->getApiConfig());
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
     * Sync products
     */
    public function syncProducts(): void {
        $this->load->language('extension/module/meschain/gittigidiyor');
        $this->load->model('extension/module/meschain/gittigidiyor');

        $json = array();

        try {
            $result = $this->model_extension_module_meschain_gittigidiyor->syncProducts();

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
     * Sync orders
     */
    public function syncOrders(): void {
        $this->load->language('extension/module/meschain/gittigidiyor');
        $this->load->model('extension/module/meschain/gittigidiyor');

        $json = array();

        try {
            $result = $this->model_extension_module_meschain_gittigidiyor->syncOrders();

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
        $this->load->model('extension/module/meschain/gittigidiyor');

        return $this->model_extension_module_meschain_gittigidiyor->getStats();
    }

    /**
     * Get API configuration
     */
    private function getApiConfig(): array {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_meschain_gittigidiyor');

        return array(
            'api_key' => $settings['module_meschain_gittigidiyor_api_key'] ?? '',
            'api_secret' => $settings['module_meschain_gittigidiyor_api_secret'] ?? '',
            'username' => $settings['module_meschain_gittigidiyor_username'] ?? ''
        );
    }

    /**
     * Install module
     */
    public function install(): void {
        $this->load->model('extension/module/meschain/gittigidiyor');
        $this->model_extension_module_meschain_gittigidiyor->install();
    }

    /**
     * Uninstall module
     */
    public function uninstall(): void {
        $this->load->model('extension/module/meschain/gittigidiyor');
        $this->model_extension_module_meschain_gittigidiyor->uninstall();
    }
}
