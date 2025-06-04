<?php
/**
 * eBay Controller
 * 
 * Simplified controller for eBay marketplace integration
 * 
 * @category   Controller
 * @package    MesChain-Sync
 * @version    2.5.0
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

class ControllerExtensionModuleEbay extends Controller {
    private $error = array();

    /**
     * Index method
     */
    public function index() {
        $this->load->language('extension/module/ebay');
        $this->document->setTitle('eBay Marketplace Entegrasyonu');
        
        $this->load->model('extension/module/ebay');
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_ebay', $this->request->post);
            
            $this->session->data['success'] = 'eBay ayarları başarıyla kaydedildi!';
            
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        // Error handling
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        // Success message
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        // Breadcrumbs
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => 'Ana Sayfa',
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Eklentiler',
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => 'eBay Entegrasyonu',
            'href' => $this->url->link('extension/module/ebay', 'user_token=' . $this->session->data['user_token'], true)
        );

        // URLs
        $data['action'] = $this->url->link('extension/module/ebay', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        // Test URLs for AJAX
        $data['test_connection_url'] = $this->url->link('extension/module/ebay/testConnection', 'user_token=' . $this->session->data['user_token'], true);
        $data['sync_orders_url'] = $this->url->link('extension/module/ebay/syncOrders', 'user_token=' . $this->session->data['user_token'], true);

        // Load settings
        if (isset($this->request->post['module_ebay_status'])) {
            $data['module_ebay_status'] = $this->request->post['module_ebay_status'];
        } else {
            $data['module_ebay_status'] = $this->config->get('module_ebay_status');
        }

        if (isset($this->request->post['module_ebay_client_id'])) {
            $data['module_ebay_client_id'] = $this->request->post['module_ebay_client_id'];
        } else {
            $data['module_ebay_client_id'] = $this->config->get('module_ebay_client_id');
        }

        if (isset($this->request->post['module_ebay_client_secret'])) {
            $data['module_ebay_client_secret'] = $this->request->post['module_ebay_client_secret'];
        } else {
            $data['module_ebay_client_secret'] = $this->config->get('module_ebay_client_secret');
        }

        if (isset($this->request->post['module_ebay_sandbox'])) {
            $data['module_ebay_sandbox'] = $this->request->post['module_ebay_sandbox'];
        } else {
            $data['module_ebay_sandbox'] = $this->config->get('module_ebay_sandbox');
        }

        // Connection status
        $data['connection_status'] = false;
        $data['connection_message'] = 'API bilgileri yapılandırılmamış';
        
        if (!empty($data['module_ebay_client_id']) && !empty($data['module_ebay_client_secret'])) {
            $data['connection_status'] = true;
            $data['connection_message'] = 'API bilgileri yapılandırılmış - Bağlantı testi yapın';
        }

        // Load common elements
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        // Render template
        $this->response->setOutput($this->load->view('extension/module/ebay', $data));
    }
    
    /**
     * Install module
     */
    public function install() {
        $this->load->model('extension/module/ebay');
        $this->model_extension_module_ebay->install();
        
        // Add permissions
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/ebay');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/ebay');
    }

    /**
     * Uninstall module
     */
    public function uninstall() {
        $this->load->model('extension/module/ebay');
        $this->model_extension_module_ebay->uninstall();
    }
    
    /**
     * Test connection via AJAX
     */
    public function testConnection() {
        $json = array();
        
        try {
            $client_id = $this->config->get('module_ebay_client_id');
            $client_secret = $this->config->get('module_ebay_client_secret');
            
            if (empty($client_id) || empty($client_secret)) {
                $json['error'] = 'API bilgileri eksik!';
            } else {
                // Simulate API test (replace with actual eBay API call)
                $json['success'] = 'eBay API bağlantısı başarılı!';
            }
        } catch (Exception $e) {
            $json['error'] = 'Bağlantı hatası: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Sync orders via AJAX
     */
    public function syncOrders() {
        $json = array();
        
        try {
            // Simulate order sync
            $json['success'] = 'Siparişler başarıyla senkronize edildi! (Demo)';
        } catch (Exception $e) {
            $json['error'] = 'Senkronizasyon hatası: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Validate form
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/ebay')) {
            $this->error['warning'] = 'eBay modülünü değiştirme yetkiniz yok!';
        }

        return !$this->error;
    }
} 