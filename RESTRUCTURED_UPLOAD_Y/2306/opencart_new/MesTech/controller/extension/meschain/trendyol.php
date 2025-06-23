<?php
/**
 * MesChain Trendyol Integration
 * Native OpenCart 4.x Controller
 * Path: admin/controller/extension/meschain/trendyol.php
 */

namespace Opencart\Admin\Controller\Extension\Meschain;

class Trendyol extends \Opencart\System\Engine\Controller {
    
    /**
     * Main Trendyol integration view
     */
    public function index() {
        $this->load->language('extension/meschain/trendyol');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_meschain'),
            'href' => $this->url->link('extension/meschain/dashboard', 'user_token=' . $this->session->data['user_token'])
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/meschain/trendyol', 'user_token=' . $this->session->data['user_token'])
        );
        
        // Handle form submission
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            $this->load->model('setting/setting');
            
            $this->model_setting_setting->editSetting('meschain_trendyol', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('extension/meschain/trendyol', 'user_token=' . $this->session->data['user_token']));
        }
        
        // Load current settings
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('meschain_trendyol');
        
        // API Configuration
        $data['meschain_trendyol_status'] = $settings['meschain_trendyol_status'] ?? 0;
        $data['meschain_trendyol_api_key'] = $settings['meschain_trendyol_api_key'] ?? '';
        $data['meschain_trendyol_api_secret'] = $settings['meschain_trendyol_api_secret'] ?? '';
        $data['meschain_trendyol_supplier_id'] = $settings['meschain_trendyol_supplier_id'] ?? '';
        $data['meschain_trendyol_environment'] = $settings['meschain_trendyol_environment'] ?? 'sandbox';
        
        // Sync Settings
        $data['meschain_trendyol_auto_sync'] = $settings['meschain_trendyol_auto_sync'] ?? 0;
        $data['meschain_trendyol_sync_interval'] = $settings['meschain_trendyol_sync_interval'] ?? 300;
        $data['meschain_trendyol_batch_size'] = $settings['meschain_trendyol_batch_size'] ?? 50;
        
        // Product Settings
        $data['meschain_trendyol_default_category'] = $settings['meschain_trendyol_default_category'] ?? '';
        $data['meschain_trendyol_price_margin'] = $settings['meschain_trendyol_price_margin'] ?? 0;
        $data['meschain_trendyol_stock_buffer'] = $settings['meschain_trendyol_stock_buffer'] ?? 0;
        
        // Load Trendyol data
        $this->load->model('extension/meschain/trendyol');
        
        $data['connection_status'] = $this->model_extension_meschain_trendyol->testConnection();
        $data['trendyol_categories'] = $this->model_extension_meschain_trendyol->getCategories();
        $data['sync_stats'] = $this->model_extension_meschain_trendyol->getSyncStats();
        $data['recent_orders'] = $this->model_extension_meschain_trendyol->getRecentOrders();
        
        // URLs
        $data['action'] = $this->url->link('extension/meschain/trendyol', 'user_token=' . $this->session->data['user_token']);
        $data['cancel'] = $this->url->link('extension/meschain/dashboard', 'user_token=' . $this->session->data['user_token']);
        $data['test_connection'] = $this->url->link('extension/meschain/trendyol/testConnection', 'user_token=' . $this->session->data['user_token']);
        $data['sync_products'] = $this->url->link('extension/meschain/trendyol/syncProducts', 'user_token=' . $this->session->data['user_token']);
        $data['sync_orders'] = $this->url->link('extension/meschain/trendyol/syncOrders', 'user_token=' . $this->session->data['user_token']);
        
        // Messages
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
        
        // Template data
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['user_token'] = $this->session->data['user_token'];
        
        $this->response->setOutput($this->load->view('extension/meschain/trendyol', $data));
    }
    
    /**
     * Test Trendyol API connection
     */
    public function testConnection() {
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->language('extension/meschain/trendyol');
            $this->load->model('extension/meschain/trendyol');
            
            try {
                $api_key = $this->request->post['api_key'] ?? '';
                $api_secret = $this->request->post['api_secret'] ?? '';
                $supplier_id = $this->request->post['supplier_id'] ?? '';
                $environment = $this->request->post['environment'] ?? 'sandbox';
                
                $result = $this->model_extension_meschain_trendyol->testApiConnection($api_key, $api_secret, $supplier_id, $environment);
                
                if ($result['success']) {
                    $json['success'] = $this->language->get('text_connection_success');
                    $json['data'] = $result['data'];
                } else {
                    $json['error'] = $result['message'];
                }
                
            } catch (Exception $e) {
                $json['error'] = $this->language->get('error_connection_failed') . ': ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Sync products to Trendyol
     */
    public function syncProducts() {
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->language('extension/meschain/trendyol');
            $this->load->model('extension/meschain/trendyol');
            
            try {
                $product_ids = $this->request->post['product_ids'] ?? [];
                $sync_mode = $this->request->post['sync_mode'] ?? 'create'; // create, update, delete
                
                if (empty($product_ids)) {
                    $json['error'] = $this->language->get('error_no_products');
                } else {
                    $result = $this->model_extension_meschain_trendyol->syncProducts($product_ids, $sync_mode);
                    
                    if ($result['success']) {
                        $json['success'] = sprintf($this->language->get('text_products_synced'), $result['synced_count']);
                        $json['data'] = $result['data'];
                    } else {
                        $json['error'] = $result['message'];
                    }
                }
                
            } catch (Exception $e) {
                $json['error'] = $this->language->get('error_sync_failed') . ': ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Sync orders from Trendyol
     */
    public function syncOrders() {
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->language('extension/meschain/trendyol');
            $this->load->model('extension/meschain/trendyol');
            
            try {
                $date_from = $this->request->post['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
                $date_to = $this->request->post['date_to'] ?? date('Y-m-d');
                
                $result = $this->model_extension_meschain_trendyol->syncOrders($date_from, $date_to);
                
                if ($result['success']) {
                    $json['success'] = sprintf($this->language->get('text_orders_synced'), $result['synced_count']);
                    $json['data'] = $result['data'];
                } else {
                    $json['error'] = $result['message'];
                }
                
            } catch (Exception $e) {
                $json['error'] = $this->language->get('error_sync_failed') . ': ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get Trendyol categories
     */
    public function getCategories() {
        $json = array();
        
        $this->load->model('extension/meschain/trendyol');
        
        try {
            $categories = $this->model_extension_meschain_trendyol->getTrendyolCategories();
            
            $json['success'] = true;
            $json['categories'] = $categories;
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Webhook endpoint for Trendyol
     */
    public function webhook() {
        $this->load->model('extension/meschain/trendyol');
        
        $input = file_get_contents('php://input');
        $webhook_data = json_decode($input, true);
        
        if ($webhook_data) {
            try {
                $result = $this->model_extension_meschain_trendyol->processWebhook($webhook_data);
                
                http_response_code($result['success'] ? 200 : 400);
                echo json_encode($result);
                
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
        }
    }
    
    /**
     * Validate form data
     */
    protected function validate() {
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
    
    /**
     * Product mapping interface
     */
    public function productMapping() {
        $this->load->language('extension/meschain/trendyol');
        
        $this->document->setTitle($this->language->get('heading_product_mapping'));
        
        $this->load->model('extension/meschain/trendyol');
        $this->load->model('catalog/product');
        
        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
        $limit = 20;
        
        $data['products'] = $this->model_extension_meschain_trendyol->getProductMappings(($page - 1) * $limit, $limit);
        $data['total'] = $this->model_extension_meschain_trendyol->getTotalProductMappings();
        
        // Pagination
        $pagination = new \Opencart\System\Library\Pagination();
        $pagination->total = $data['total'];
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('extension/meschain/trendyol/productMapping', 'user_token=' . $this->session->data['user_token'] . '&page={page}');
        
        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($data['total']) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($data['total'] - $limit)) ? $data['total'] : ((($page - 1) * $limit) + $limit), $data['total'], ceil($data['total'] / $limit));
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/meschain/trendyol_product_mapping', $data));
    }
}
?>
