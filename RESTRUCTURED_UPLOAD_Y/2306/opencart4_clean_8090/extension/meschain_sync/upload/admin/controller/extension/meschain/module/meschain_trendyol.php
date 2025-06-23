<?php
namespace Opencart\Admin\Controller\Extension\Meschain\Module;

/**
 * MesChain Trendyol Main Module Controller
 * 
 * @author MesChain Development Team
 * @version 1.0.0
 */
class MeschainTrendyol extends \Opencart\System\Engine\Controller {
    
    private $error = array();
    
    public function index() {
        $this->load->language('extension/meschain/module/meschain_trendyol');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/meschain/module/meschain_trendyol');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_extension_meschain_module_meschain_trendyol->editSetting('meschain_trendyol', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }
        
        $this->getForm();
    }
    
    protected function getForm() {
        $data['heading_title'] = $this->language->get('heading_title');
        
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        
        $data['entry_status'] = $this->language->get('entry_status');
        
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
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
            'href' => $this->url->link('extension/meschain/module/meschain_trendyol', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['action'] = $this->url->link('extension/meschain/module/meschain_trendyol', 'user_token=' . $this->session->data['user_token'], true);
        
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        if (isset($this->request->post['module_meschain_trendyol_status'])) {
            $data['module_meschain_trendyol_status'] = $this->request->post['module_meschain_trendyol_status'];
        } else {
            $data['module_meschain_trendyol_status'] = $this->config->get('module_meschain_trendyol_status');
        }
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/meschain/module/meschain_trendyol', $data));
    }
    
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/meschain/module/meschain_trendyol')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        return !$this->error;
    }
    
    public function install() {
        $this->load->model('extension/meschain/module/meschain_trendyol');
        $this->model_extension_meschain_module_meschain_trendyol->install();
    }
    
    public function uninstall() {
        $this->load->model('extension/meschain/module/meschain_trendyol');
        $this->model_extension_meschain_module_meschain_trendyol->uninstall();
    }
}
?>