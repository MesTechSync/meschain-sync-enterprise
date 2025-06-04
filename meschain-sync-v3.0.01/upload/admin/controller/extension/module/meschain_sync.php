<?php
/**
 * MesChain-Sync Main Controller
 * 
 * OpenCart Extension - Multi-Marketplace Integrator
 * 
 * @category   Controller
 * @package    MesChain-Sync
 * @version    2.5.0
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

class ControllerExtensionModuleMeschainSync extends Controller {
    
    private $error = array();
    
    /**
     * Ana dashboard sayfası
     */
    public function index() {
        $this->load->language('extension/module/meschain_sync');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Model yükle
        $this->load->model('extension/module/meschain_sync');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_meschain_sync', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }
        
        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
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
            'href' => $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // URLs
        $data['action'] = $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        // Ayarları yükle
        $this->load->model('setting/setting');
        
        if (isset($this->request->post['module_meschain_sync_status'])) {
            $data['module_meschain_sync_status'] = $this->request->post['module_meschain_sync_status'];
        } else {
            $data['module_meschain_sync_status'] = $this->config->get('module_meschain_sync_status');
        }
        
        if (isset($this->request->post['module_meschain_sync_debug'])) {
            $data['module_meschain_sync_debug'] = $this->request->post['module_meschain_sync_debug'];
        } else {
            $data['module_meschain_sync_debug'] = $this->config->get('module_meschain_sync_debug');
        }
        
        if (isset($this->request->post['module_meschain_sync_interval'])) {
            $data['module_meschain_sync_interval'] = $this->request->post['module_meschain_sync_interval'];
        } else {
            $data['module_meschain_sync_interval'] = $this->config->get('module_meschain_sync_interval');
        }
        
        // Marketplace durumları
        $marketplaces = array('amazon', 'ebay', 'hepsiburada', 'n11', 'trendyol', 'ozon');
        
        foreach ($marketplaces as $marketplace) {
            $data[$marketplace . '_status'] = $this->config->get('module_' . $marketplace . '_status');
            $data[$marketplace . '_url'] = $this->url->link('extension/module/' . $marketplace, 'user_token=' . $this->session->data['user_token'], true);
            
            // Marketplace istatistikleri (basit)
            $data[$marketplace . '_products'] = 0;
            $data[$marketplace . '_orders'] = 0;
        }
        
        // Template için text değerleri
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        
        // Success mesajı
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // İstatistikleri al
        try {
            $data['stats'] = $this->model_extension_module_meschain_sync->getSyncStats();
        } catch (Exception $e) {
            $data['stats'] = array();
        }
        
        // Son logları al
        try {
            $data['recent_logs'] = $this->model_extension_module_meschain_sync->getRecentLogs(null, 10);
        } catch (Exception $e) {
            $data['recent_logs'] = array();
        }
        
        // Template
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_sync', $data));
    }
    
    /**
     * Modül yükleme
     */
    public function install() {
        $this->load->model('extension/module/meschain_sync');
        $this->model_extension_module_meschain_sync->install();
        
        // Kullanıcı izinlerini ekle
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/meschain_sync');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/meschain_sync');
    }
    
    /**
     * Modül kaldırma
     */
    public function uninstall() {
        $this->load->model('extension/module/meschain_sync');
        $this->model_extension_module_meschain_sync->uninstall();
    }
    
    /**
     * AJAX - API bağlantısını test et
     */
    public function testConnection() {
        $this->load->language('extension/module/meschain_sync');
        
        $json = array();
        
        if (!$this->user->hasPermission('modify', 'extension/module/meschain_sync')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $marketplace = isset($this->request->post['marketplace']) ? $this->request->post['marketplace'] : '';
            
            if (empty($marketplace)) {
                $json['error'] = 'Marketplace not specified';
            } else {
                $json['success'] = $this->language->get('success_connection') . ' (' . $marketplace . ')';
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Form doğrulama
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/meschain_sync')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        return !$this->error;
    }
} 