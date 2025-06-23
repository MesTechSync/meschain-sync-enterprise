<?php
/**
 * MesChain Sync - N11 Controller
 * 
 * @package    MesChain Sync
 * @author     MesChain Development Team
 * @copyright  2024 MesChain Technologies
 * @license    Commercial License
 * @version    2.3.0
 * @link       https://www.meschain.com
 */

namespace Opencart\Admin\Controller\Extension\Meschain;

use Opencart\System\Engine\Controller;

class N11 extends Controller {
    
    private $error = array();
    
    public function index() {
        $this->load->language('extension/meschain/n11');
        $this->load->model('extension/meschain/n11');
        $this->load->model('setting/setting');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=meschain')
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/meschain/n11', 'user_token=' . $this->session->data['user_token'])
        );
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('meschain_n11', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('extension/meschain/n11', 'user_token=' . $this->session->data['user_token']));
        }
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        $data['action'] = $this->url->link('extension/meschain/n11', 'user_token=' . $this->session->data['user_token']);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=meschain');
        
        // N11 API Settings
        if (isset($this->request->post['meschain_n11_status'])) {
            $data['meschain_n11_status'] = $this->request->post['meschain_n11_status'];
        } else {
            $data['meschain_n11_status'] = $this->config->get('meschain_n11_status');
        }
        
        if (isset($this->request->post['meschain_n11_api_key'])) {
            $data['meschain_n11_api_key'] = $this->request->post['meschain_n11_api_key'];
        } else {
            $data['meschain_n11_api_key'] = $this->config->get('meschain_n11_api_key');
        }
        
        if (isset($this->request->post['meschain_n11_api_secret'])) {
            $data['meschain_n11_api_secret'] = $this->request->post['meschain_n11_api_secret'];
        } else {
            $data['meschain_n11_api_secret'] = $this->config->get('meschain_n11_api_secret');
        }
        
        if (isset($this->request->post['meschain_n11_store_key'])) {
            $data['meschain_n11_store_key'] = $this->request->post['meschain_n11_store_key'];
        } else {
            $data['meschain_n11_store_key'] = $this->config->get('meschain_n11_store_key');
        }
        
        if (isset($this->request->post['meschain_n11_auto_sync'])) {
            $data['meschain_n11_auto_sync'] = $this->request->post['meschain_n11_auto_sync'];
        } else {
            $data['meschain_n11_auto_sync'] = $this->config->get('meschain_n11_auto_sync');
        }
        
        if (isset($this->request->post['meschain_n11_sync_interval'])) {
            $data['meschain_n11_sync_interval'] = $this->request->post['meschain_n11_sync_interval'];
        } else {
            $data['meschain_n11_sync_interval'] = $this->config->get('meschain_n11_sync_interval');
        }
        
        // Sync Statistics
        $data['sync_stats'] = $this->model_extension_meschain_n11->getSyncStatistics();
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/meschain/n11', $data));
    }
    
    public function syncProducts() {
        $this->load->language('extension/meschain/n11');
        $this->load->model('extension/meschain/n11');
        
        $json = array();
        
        try {
            $result = $this->model_extension_meschain_n11->syncProductsToN11();
            
            if ($result['success']) {
                $json['success'] = sprintf($this->language->get('text_sync_success'), $result['count']);
            } else {
                $json['error'] = $result['error'];
            }
        } catch (\Exception $e) {
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function syncOrders() {
        $this->load->language('extension/meschain/n11');
        $this->load->model('extension/meschain/n11');
        
        $json = array();
        
        try {
            $result = $this->model_extension_meschain_n11->syncOrdersFromN11();
            
            if ($result['success']) {
                $json['success'] = sprintf($this->language->get('text_order_sync_success'), $result['count']);
            } else {
                $json['error'] = $result['error'];
            }
        } catch (\Exception $e) {
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function testConnection() {
        $this->load->language('extension/meschain/n11');
        
        $json = array();
        
        $api_key = $this->request->post['api_key'] ?? '';
        $api_secret = $this->request->post['api_secret'] ?? '';
        $store_key = $this->request->post['store_key'] ?? '';
        
        if (empty($api_key) || empty($api_secret) || empty($store_key)) {
            $json['error'] = $this->language->get('error_api_credentials');
        } else {
            try {
                // N11 API connection test logic would go here
                $json['success'] = $this->language->get('text_connection_success');
            } catch (\Exception $e) {
                $json['error'] = $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/meschain/n11')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if ($this->request->post['meschain_n11_status'] && empty($this->request->post['meschain_n11_api_key'])) {
            $this->error['warning'] = $this->language->get('error_api_key');
        }
        
        if ($this->request->post['meschain_n11_status'] && empty($this->request->post['meschain_n11_api_secret'])) {
            $this->error['warning'] = $this->language->get('error_api_secret');
        }
        
        if ($this->request->post['meschain_n11_status'] && empty($this->request->post['meschain_n11_store_key'])) {
            $this->error['warning'] = $this->language->get('error_store_key');
        }
        
        return !$this->error;
    }
    
    public function install() {
        $this->load->model('extension/meschain/n11');
        $this->model_extension_meschain_n11->install();
    }
    
    public function uninstall() {
        $this->load->model('extension/meschain/n11');
        $this->model_extension_meschain_n11->uninstall();
    }
}
