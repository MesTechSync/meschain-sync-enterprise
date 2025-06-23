<?php
namespace Opencart\Admin\Controller\Extension\MesChain;

/**
 * MesChain Sync Hepsiburada Controller
 * 
 * @package    MesChain Sync
 * @version    2.0.0
 * @author     MesChain Development Team
 * @copyright  2024 MesChain
 * @license    MIT License
 */

class Hepsiburada extends \Opencart\System\Engine\Controller {
    
    private $error = [];
    
    public function index(): void {
        $this->load->language('extension/meschain/hepsiburada');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('meschain_hepsiburada', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module'));
        }
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        $data['breadcrumbs'] = [];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/meschain/hepsiburada', 'user_token=' . $this->session->data['user_token'])
        ];
        
        $data['action'] = $this->url->link('extension/meschain/hepsiburada', 'user_token=' . $this->session->data['user_token']);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');
        
        // Hepsiburada configuration fields
        $data['meschain_hepsiburada_status'] = $this->config->get('meschain_hepsiburada_status') ?? 0;
        $data['meschain_hepsiburada_debug'] = $this->config->get('meschain_hepsiburada_debug') ?? 0;
        $data['meschain_hepsiburada_api_key'] = $this->config->get('meschain_hepsiburada_api_key') ?? '';
        $data['meschain_hepsiburada_api_secret'] = $this->config->get('meschain_hepsiburada_api_secret') ?? '';
        $data['meschain_hepsiburada_merchant_id'] = $this->config->get('meschain_hepsiburada_merchant_id') ?? '';
        $data['meschain_hepsiburada_username'] = $this->config->get('meschain_hepsiburada_username') ?? '';
        $data['meschain_hepsiburada_password'] = $this->config->get('meschain_hepsiburada_password') ?? '';
        $data['meschain_hepsiburada_auto_sync'] = $this->config->get('meschain_hepsiburada_auto_sync') ?? 0;
        $data['meschain_hepsiburada_sync_interval'] = $this->config->get('meschain_hepsiburada_sync_interval') ?? 3600;
        $data['meschain_hepsiburada_order_status'] = $this->config->get('meschain_hepsiburada_order_status') ?? 1;
        $data['meschain_hepsiburada_log_level'] = $this->config->get('meschain_hepsiburada_log_level') ?? 'info';
        
        // Sync intervals
        $data['sync_intervals'] = [
            ['value' => 900, 'text' => $this->language->get('text_sync_interval_15min')],
            ['value' => 1800, 'text' => $this->language->get('text_sync_interval_30min')],
            ['value' => 3600, 'text' => $this->language->get('text_sync_interval_1hour')],
            ['value' => 7200, 'text' => $this->language->get('text_sync_interval_2hour')],
            ['value' => 21600, 'text' => $this->language->get('text_sync_interval_6hour')],
            ['value' => 43200, 'text' => $this->language->get('text_sync_interval_12hour')],
            ['value' => 86400, 'text' => $this->language->get('text_sync_interval_24hour')]
        ];
        
        // Log levels
        $data['log_levels'] = [
            ['value' => 'debug', 'text' => $this->language->get('text_log_level_debug')],
            ['value' => 'info', 'text' => $this->language->get('text_log_level_info')],
            ['value' => 'warning', 'text' => $this->language->get('text_log_level_warning')],
            ['value' => 'error', 'text' => $this->language->get('text_log_level_error')],
            ['value' => 'critical', 'text' => $this->language->get('text_log_level_critical')]
        ];
        
        // Order statuses
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        
        $data['user_token'] = $this->session->data['user_token'];
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/meschain/hepsiburada', $data));
    }
    
    /**
     * Test Hepsiburada API connection
     */
    public function testConnection(): void {
        $this->load->language('extension/meschain/hepsiburada');
        
        $json = [];
        
        if (!$this->user->hasPermission('modify', 'extension/meschain/hepsiburada')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            try {
                $api_key = $this->request->post['api_key'] ?? '';
                $api_secret = $this->request->post['api_secret'] ?? '';
                $merchant_id = $this->request->post['merchant_id'] ?? '';
                $username = $this->request->post['username'] ?? '';
                $password = $this->request->post['password'] ?? '';
                
                if (empty($api_key) || empty($api_secret) || empty($merchant_id)) {
                    $json['error'] = $this->language->get('error_api_credentials');
                } else {
                    $this->load->model('extension/meschain/hepsiburada');
                    $result = $this->model_extension_meschain_hepsiburada->testConnection($api_key, $api_secret, $merchant_id, $username, $password);
                    
                    if ($result['success']) {
                        $json['success'] = $this->language->get('success_connection');
                    } else {
                        $json['error'] = $result['message'];
                    }
                }
                
            } catch (\Exception $e) {
                $json['error'] = $this->language->get('error_connection') . ': ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Clear Hepsiburada logs
     */
    public function clearLogs(): void {
        $this->load->language('extension/meschain/hepsiburada');
        
        $json = [];
        
        if (!$this->user->hasPermission('modify', 'extension/meschain/hepsiburada')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            try {
                $this->load->model('extension/meschain/hepsiburada');
                $this->model_extension_meschain_hepsiburada->clearLogs();
                
                $json['success'] = $this->language->get('success_logs_cleared');
                
            } catch (\Exception $e) {
                $json['error'] = $this->language->get('error_connection') . ': ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    protected function validate(): bool {
        if (!$this->user->hasPermission('modify', 'extension/meschain/hepsiburada')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        return !$this->error;
    }
}
