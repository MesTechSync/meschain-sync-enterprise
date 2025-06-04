<?php
/**
 * meschain_sync.php
 *
 * MesChain Sync modülünün ana kontrolcü sınıfı
 */
class ControllerExtensionModuleMeschainSync extends Controller {
    private $error = array();
    
    /**
     * Ana sayfa
     */
    public function index() {
        $this->load->language('extension/module/meschain_sync');
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
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
        
        // Aksiyon URL'leri
        $data['action'] = $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        // Modül durumu
        if (isset($this->request->post['module_meschain_sync_status'])) {
            $data['module_meschain_sync_status'] = $this->request->post['module_meschain_sync_status'];
        } else {
            $data['module_meschain_sync_status'] = $this->config->get('module_meschain_sync_status');
        }
        
        // API Anahtarı
        if (isset($this->request->post['module_meschain_sync_api_key'])) {
            $data['module_meschain_sync_api_key'] = $this->request->post['module_meschain_sync_api_key'];
        } else {
            $data['module_meschain_sync_api_key'] = $this->config->get('module_meschain_sync_api_key');
        }
        
        // Tema seçimi
        if (isset($this->request->post['module_meschain_sync_theme'])) {
            $data['module_meschain_sync_theme'] = $this->request->post['module_meschain_sync_theme'];
        } else {
            $data['module_meschain_sync_theme'] = $this->config->get('module_meschain_sync_theme');
        }
        
        // Tema seçenekleri
        $data['themes'] = array(
            'sutlu_kahve' => $this->language->get('text_theme_sutlu_kahve'),
            'deniz_mavisi' => $this->language->get('text_theme_deniz_mavisi'),
            'default' => $this->language->get('text_theme_default')
        );
        
        // Pazaryerleri
        $data['marketplaces'] = array(
            'trendyol' => $this->url->link('extension/module/meschain_sync/trendyol', 'user_token=' . $this->session->data['user_token'], true),
            'n11' => $this->url->link('extension/module/meschain_sync/n11', 'user_token=' . $this->session->data['user_token'], true),
            'amazon' => $this->url->link('extension/module/meschain_sync/amazon', 'user_token=' . $this->session->data['user_token'], true),
            'ebay' => $this->url->link('extension/module/meschain_sync/ebay', 'user_token=' . $this->session->data['user_token'], true),
            'hepsiburada' => $this->url->link('extension/module/meschain_sync/hepsiburada', 'user_token=' . $this->session->data['user_token'], true),
            'ozon' => $this->url->link('extension/module/meschain_sync/ozon', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Diğer sayfalar
        $data['logs_url'] = $this->url->link('extension/module/meschain_sync/logs', 'user_token=' . $this->session->data['user_token'], true);
        $data['help_url'] = $this->url->link('extension/module/meschain_sync/help', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_sync', $data));
    }
    
    /**
     * Trendyol sayfası
     */
    public function trendyol() {
        $this->load->language('extension/module/meschain_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - Trendyol');
        
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_meschain_sync_trendyol', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('extension/module/meschain_sync/trendyol', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        // Başarı mesajı
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
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
        
        $data['breadcrumbs'][] = array(
            'text' => 'Trendyol',
            'href' => $this->url->link('extension/module/meschain_sync/trendyol', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['action'] = $this->url->link('extension/module/meschain_sync/trendyol', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);
        
        // Trendyol API ayarları
        if (isset($this->request->post['module_meschain_sync_trendyol_api_key'])) {
            $data['module_meschain_sync_trendyol_api_key'] = $this->request->post['module_meschain_sync_trendyol_api_key'];
        } else {
            $data['module_meschain_sync_trendyol_api_key'] = $this->config->get('module_meschain_sync_trendyol_api_key');
        }
        
        if (isset($this->request->post['module_meschain_sync_trendyol_api_secret'])) {
            $data['module_meschain_sync_trendyol_api_secret'] = $this->request->post['module_meschain_sync_trendyol_api_secret'];
        } else {
            $data['module_meschain_sync_trendyol_api_secret'] = $this->config->get('module_meschain_sync_trendyol_api_secret');
        }
        
        if (isset($this->request->post['module_meschain_sync_trendyol_supplier_id'])) {
            $data['module_meschain_sync_trendyol_supplier_id'] = $this->request->post['module_meschain_sync_trendyol_supplier_id'];
        } else {
            $data['module_meschain_sync_trendyol_supplier_id'] = $this->config->get('module_meschain_sync_trendyol_supplier_id');
        }
        
        // Diğer sayfalar
        $data['dashboard_url'] = $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['logs_url'] = $this->url->link('extension/module/meschain_sync/logs', 'user_token=' . $this->session->data['user_token'], true);
        $data['help_url'] = $this->url->link('extension/module/meschain_sync/help', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_sync_trendyol', $data));
    }
    
    /**
     * Amazon sayfası
     */
    public function amazon() {
        $this->load->language('extension/module/meschain_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - Amazon');
        
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_meschain_sync_amazon', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('extension/module/meschain_sync/amazon', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        // Başarı mesajı
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
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
        
        $data['breadcrumbs'][] = array(
            'text' => 'Amazon',
            'href' => $this->url->link('extension/module/meschain_sync/amazon', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['action'] = $this->url->link('extension/module/meschain_sync/amazon', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);
        
        // Amazon API ayarları
        if (isset($this->request->post['module_meschain_sync_amazon_api_key'])) {
            $data['module_meschain_sync_amazon_api_key'] = $this->request->post['module_meschain_sync_amazon_api_key'];
        } else {
            $data['module_meschain_sync_amazon_api_key'] = $this->config->get('module_meschain_sync_amazon_api_key');
        }
        
        if (isset($this->request->post['module_meschain_sync_amazon_secret_key'])) {
            $data['module_meschain_sync_amazon_secret_key'] = $this->request->post['module_meschain_sync_amazon_secret_key'];
        } else {
            $data['module_meschain_sync_amazon_secret_key'] = $this->config->get('module_meschain_sync_amazon_secret_key');
        }
        
        if (isset($this->request->post['module_meschain_sync_amazon_seller_id'])) {
            $data['module_meschain_sync_amazon_seller_id'] = $this->request->post['module_meschain_sync_amazon_seller_id'];
        } else {
            $data['module_meschain_sync_amazon_seller_id'] = $this->config->get('module_meschain_sync_amazon_seller_id');
        }
        
        // Diğer sayfalar
        $data['dashboard_url'] = $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['logs_url'] = $this->url->link('extension/module/meschain_sync/logs', 'user_token=' . $this->session->data['user_token'], true);
        $data['help_url'] = $this->url->link('extension/module/meschain_sync/help', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_sync_amazon', $data));
    }
    
    /**
     * Log görüntüleme sayfası
     */
    public function logs() {
        $this->load->language('extension/module/meschain_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_logs'));
        
        // Log dosyasını oku
        $this->load->library('meschain/logger');
        $logger = new MeschainLogger('meschain_sync.log');
        $data['logs'] = $logger->read(100); // Son 100 log kaydını oku
        
        // Log istatistikleri
        $data['stats'] = $logger->getStats();
        
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
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_logs'),
            'href' => $this->url->link('extension/module/meschain_sync/logs', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['clear_url'] = $this->url->link('extension/module/meschain_sync/clear_logs', 'user_token=' . $this->session->data['user_token'], true);
        $data['download_url'] = $this->url->link('extension/module/meschain_sync/download_logs', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);
        
        // Diğer sayfalar
        $data['dashboard_url'] = $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['help_url'] = $this->url->link('extension/module/meschain_sync/help', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_sync_logs', $data));
    }
    
    /**
     * Log dosyasını temizle
     */
    public function clear_logs() {
        $this->load->language('extension/module/meschain_sync');
        
        $this->load->library('meschain/logger');
        $logger = new MeschainLogger('meschain_sync.log');
        $logger->clear();
        
        $this->session->data['success'] = $this->language->get('text_success_clear_logs');
        
        $this->response->redirect($this->url->link('extension/module/meschain_sync/logs', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Log dosyasını indir
     */
    public function download_logs() {
        $this->load->library('meschain/logger');
        $logger = new MeschainLogger('meschain_sync.log');
        $logger->download();
    }
    
    /**
     * Yardım sayfası
     */
    public function help() {
        $this->load->language('extension/module/meschain_sync');
        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_help'));
        
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
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_help'),
            'href' => $this->url->link('extension/module/meschain_sync/help', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Aksiyon URL'leri
        $data['cancel'] = $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);
        
        // Diğer sayfalar
        $data['dashboard_url'] = $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['logs_url'] = $this->url->link('extension/module/meschain_sync/logs', 'user_token=' . $this->session->data['user_token'], true);
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_sync_help', $data));
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
    
    /**
     * Modülü yükle
     */
    public function install() {
        $this->load->model('setting/setting');
        
        // Varsayılan ayarları kaydet
        $this->model_setting_setting->editSetting('module_meschain_sync', array(
            'module_meschain_sync_status' => 1,
            'module_meschain_sync_theme' => 'sutlu_kahve',
            'module_meschain_sync_api_key' => 'f4KhSfv7ihjXcJFlJeim'
        ));
        
        // Kullanıcı izinlerini ayarla
        $this->load->model('user/user_group');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/meschain_sync');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/meschain_sync');
        
        // Log dosyası oluştur
        $this->load->library('meschain/logger');
        $logger = new MeschainLogger('meschain_sync.log');
        $logger->write('system', 'INSTALL', 'MesChain Sync modülü yüklendi');
    }
    
    /**
     * Modülü kaldır
     */
    public function uninstall() {
        $this->load->model('setting/setting');
        
        // Ayarları kaldır
        $this->model_setting_setting->deleteSetting('module_meschain_sync');
        $this->model_setting_setting->deleteSetting('module_meschain_sync_trendyol');
        $this->model_setting_setting->deleteSetting('module_meschain_sync_amazon');
        
        // Log dosyasına kaydet
        $this->load->library('meschain/logger');
        $logger = new MeschainLogger('meschain_sync.log');
        $logger->write('system', 'UNINSTALL', 'MesChain Sync modülü kaldırıldı');
    }
} 