<?php
namespace Opencart\Admin\Controller\Extension\Module;

/**
 * Trendyol Marketplace Controller - A+++++ Level
 * Turkey's largest marketplace integration
 *
 * @author Cursor Development Team
 * @version 1.0.0
 */
class MeschainTrendyol extends \Opencart\System\Engine\Controller {
    private $error = array();

    public function index(): void {
        $this->load->language('extension/meschain/trendyol');
        $this->document->setTitle($this->language->get('heading_title'));

        // Load models
        $this->load->model('extension/module/meschain_sync');
        $this->load->model('extension/meschain/trendyol');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_extension_meschain_trendyol->saveSettings($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/module/meschain_trendyol', 'user_token=' . $this->session->data['user_token'], true));
        }

        $data = $this->getFormData();

        // Load common controllers
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/meschain/trendyol', $data));
    }

    /**
     * Product synchronization
     */
    public function syncProducts(): void {
        $this->load->language('extension/meschain/trendyol');
        $this->load->model('extension/meschain/trendyol');

        $json = array();

        if (!$this->user->hasPermission('modify', 'extension/module/meschain_trendyol')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            try {
                $result = $this->model_extension_meschain_trendyol->syncProducts();

                if ($result['success']) {
                    $json['success'] = sprintf(
                        $this->language->get('text_sync_success'),
                        $result['synced_count'],
                        $result['total_count']
                    );
                    $json['data'] = $result;
                } else {
                    $json['error'] = $result['error'] ?? $this->language->get('error_sync_failed');
                }
            } catch (\Exception $e) {
                $json['error'] = $e->getMessage();
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Order management
     */
    public function getOrders(): void {
        $this->load->language('extension/meschain/trendyol');
        $this->load->model('extension/meschain/trendyol');

        $json = array();

        if (!$this->user->hasPermission('access', 'extension/module/meschain_trendyol')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            try {
                $filter_data = array(
                    'status' => $this->request->get['status'] ?? 'all',
                    'start' => ($this->request->get['page'] ?? 1 - 1) * 20,
                    'limit' => 20
                );

                $orders = $this->model_extension_meschain_trendyol->getOrders($filter_data);

                $json['success'] = true;
                $json['orders'] = $orders['data'];
                $json['total'] = $orders['total'];

            } catch (\Exception $e) {
                $json['error'] = $e->getMessage();
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Update inventory
     */
    public function updateInventory(): void {
        $this->load->language('extension/meschain/trendyol');
        $this->load->model('extension/meschain/trendyol');

        $json = array();

        if (!$this->user->hasPermission('modify', 'extension/module/meschain_trendyol')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            try {
                $product_id = $this->request->post['product_id'] ?? 0;
                $quantity = $this->request->post['quantity'] ?? 0;

                $result = $this->model_extension_meschain_trendyol->updateInventory($product_id, $quantity);

                if ($result['success']) {
                    $json['success'] = $this->language->get('text_inventory_updated');
                } else {
                    $json['error'] = $result['error'];
                }

            } catch (\Exception $e) {
                $json['error'] = $e->getMessage();
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Get analytics data
     */
    public function getAnalytics(): void {
        $this->load->language('extension/meschain/trendyol');
        $this->load->model('extension/meschain/trendyol');

        $json = array();

        if (!$this->user->hasPermission('access', 'extension/module/meschain_trendyol')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            try {
                $period = $this->request->get['period'] ?? '7days';
                $analytics = $this->model_extension_meschain_trendyol->getAnalytics($period);

                $json['success'] = true;
                $json['analytics'] = $analytics;

            } catch (\Exception $e) {
                $json['error'] = $e->getMessage();
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Campaign management
     */
    public function getCampaigns(): void {
        $this->load->language('extension/meschain/trendyol');
        $this->load->model('extension/meschain/trendyol');

        $json = array();

        try {
            $campaigns = $this->model_extension_meschain_trendyol->getCampaigns();

            $json['success'] = true;
            $json['campaigns'] = $campaigns;

        } catch (\Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Get form data
     */
    private function getFormData(): array {
        // Language
        $data['heading_title'] = $this->language->get('heading_title');

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
            'href' => $this->url->link('extension/module/meschain_trendyol', 'user_token=' . $this->session->data['user_token'], true)
        );

        // Actions
        $data['action'] = $this->url->link('extension/module/meschain_trendyol', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        // Get saved settings
        $settings = $this->model_extension_meschain_trendyol->getSettings();

        // Form fields
        $data['api_key'] = $settings['api_key'] ?? '';
        $data['api_secret'] = $settings['api_secret'] ?? '';
        $data['supplier_id'] = $settings['supplier_id'] ?? '';
        $data['status'] = $settings['status'] ?? 0;
        $data['auto_sync'] = $settings['auto_sync'] ?? 0;
        $data['sync_interval'] = $settings['sync_interval'] ?? 60;
        $data['price_margin'] = $settings['price_margin'] ?? 0;
        $data['stock_buffer'] = $settings['stock_buffer'] ?? 0;

        // API URLs
        $data['sync_products_url'] = $this->url->link('extension/module/meschain_trendyol/syncProducts', 'user_token=' . $this->session->data['user_token'], true);
        $data['get_orders_url'] = $this->url->link('extension/module/meschain_trendyol/getOrders', 'user_token=' . $this->session->data['user_token'], true);
        $data['update_inventory_url'] = $this->url->link('extension/module/meschain_trendyol/updateInventory', 'user_token=' . $this->session->data['user_token'], true);
        $data['get_analytics_url'] = $this->url->link('extension/module/meschain_trendyol/getAnalytics', 'user_token=' . $this->session->data['user_token'], true);
        $data['get_campaigns_url'] = $this->url->link('extension/module/meschain_trendyol/getCampaigns', 'user_token=' . $this->session->data['user_token'], true);

        // Token
        $data['user_token'] = $this->session->data['user_token'];

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

        return $data;
    }

    /**
     * Validate form
     */
    protected function validate(): bool {
        if (!$this->user->hasPermission('modify', 'extension/module/meschain_trendyol')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['api_key'])) {
            $this->error['warning'] = $this->language->get('error_api_key');
        }

        if (empty($this->request->post['api_secret'])) {
            $this->error['warning'] = $this->language->get('error_api_secret');
        }

        if (empty($this->request->post['supplier_id'])) {
            $this->error['warning'] = $this->language->get('error_supplier_id');
        }

        return !$this->error;
    }
}
