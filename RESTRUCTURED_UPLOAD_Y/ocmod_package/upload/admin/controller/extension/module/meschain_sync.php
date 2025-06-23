<?php
namespace Opencart\Admin\Controller\Extension\Module;

class MeschainSync extends \Opencart\System\Engine\Controller {
    private $error = array();

    public function index(): void {
        $this->load->language('extension/module/meschain_sync');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_meschain_sync', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
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
            'href' => $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['save'] = $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        // Module Settings
        if (isset($this->request->post['module_meschain_sync_status'])) {
            $data['module_meschain_sync_status'] = $this->request->post['module_meschain_sync_status'];
        } else {
            $data['module_meschain_sync_status'] = $this->config->get('module_meschain_sync_status');
        }
        
        if (isset($this->request->post['module_meschain_sync_api_key'])) {
            $data['module_meschain_sync_api_key'] = $this->request->post['module_meschain_sync_api_key'];
        } else {
            $data['module_meschain_sync_api_key'] = $this->config->get('module_meschain_sync_api_key');
        }
        
        if (isset($this->request->post['module_meschain_sync_api_secret'])) {
            $data['module_meschain_sync_api_secret'] = $this->request->post['module_meschain_sync_api_secret'];
        } else {
            $data['module_meschain_sync_api_secret'] = $this->config->get('module_meschain_sync_api_secret');
        }
        
        // Error handling
        if (isset($this->error['api_key'])) {
            $data['error_api_key'] = $this->error['api_key'];
        } else {
            $data['error_api_key'] = '';
        }
        
        if (isset($this->error['api_secret'])) {
            $data['error_api_secret'] = $this->error['api_secret'];
        } else {
            $data['error_api_secret'] = '';
        }
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_sync', $data));
    }
    
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/meschain_sync')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        return !$this->error;
    }
    
    public function install(): void {
        // Gerekli tabloları oluştur
        $this->load->model('extension/module/meschain_sync');
        $this->model_extension_module_meschain_sync->install();
        
        // Olayları kaydet
        $this->load->model('setting/event');
        
        $this->model_setting_event->addEvent('meschain_sync', 'admin/view/common/column_left/before', 'extension/module/meschain_sync|addMenuItems');
        $this->model_setting_event->addEvent('meschain_sync_order', 'catalog/model/checkout/order/addHistory/after', 'extension/module/meschain_sync|syncOrder');
    }
    
    public function uninstall(): void {
        // Olayları kaldır
        $this->load->model('setting/event');
        
        $this->model_setting_event->deleteEventByCode('meschain_sync');
        $this->model_setting_event->deleteEventByCode('meschain_sync_order');
        
        // Tabloları kaldır
        $this->load->model('extension/module/meschain_sync');
        $this->model_extension_module_meschain_sync->uninstall();
    }
    
    // Event Handler Methods
    public function addMenuItems(&$route, &$data, &$output): void {
        // Add MesChain menu items to admin sidebar
        $this->load->language('extension/module/meschain_sync');
        
        $meschain_menu = '';
        $meschain_menu .= '<li class="nav-item dropdown">';
        $meschain_menu .= '<a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">';
        $meschain_menu .= '<i class="fas fa-sync"></i> MesChain-Sync';
        $meschain_menu .= '</a>';
        $meschain_menu .= '<ul class="dropdown-menu">';
        $meschain_menu .= '<li><a href="' . $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true) . '" class="dropdown-item">';
        $meschain_menu .= '<i class="fas fa-tachometer-alt"></i> Dashboard</a></li>';
        $meschain_menu .= '<li><a href="' . $this->url->link('extension/module/meschain_sync/marketplaces', 'user_token=' . $this->session->data['user_token'], true) . '" class="dropdown-item">';
        $meschain_menu .= '<i class="fas fa-store"></i> Marketplaces</a></li>';
        $meschain_menu .= '</ul>';
        $meschain_menu .= '</li>';
        
        // Insert menu before marketplace menu
        $output = str_replace('<li><a href="' . $this->url->link('marketplace/marketplace', 'user_token=' . $this->session->data['user_token'], true) . '">', $meschain_menu . '<li><a href="' . $this->url->link('marketplace/marketplace', 'user_token=' . $this->session->data['user_token'], true) . '">', $output);
    }
    
    public function syncOrder(&$route, &$data, &$output): void {
        // Sync order to marketplaces when order status changes
        if (isset($data[0]) && isset($data[1])) {
            $order_id = $data[0];
            $order_status_id = $data[1];
            
            $this->load->model('extension/module/meschain_sync');
            
            // Check if this order needs to be synced to marketplaces
            $marketplace_orders = $this->model_extension_module_meschain_sync->getMarketplaceOrdersByOrderId($order_id);
            
            foreach ($marketplace_orders as $marketplace_order) {
                // Sync order status to respective marketplace
                $this->syncOrderToMarketplace($marketplace_order, $order_status_id);
            }
        }
    }
    
    private function syncOrderToMarketplace($marketplace_order, $order_status_id): void {
        // Implementation for syncing order to specific marketplace
        // This would contain marketplace-specific API calls
        
        $this->load->model('extension/module/meschain_sync');
        
        // Log the sync attempt
        $this->model_extension_module_meschain_sync->addLog('order_sync', $marketplace_order['meschain_order_id'], 'Order status synced to ' . $marketplace_order['marketplace_name']);
    }
}
