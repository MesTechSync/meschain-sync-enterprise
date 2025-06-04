<?php
/**
 * user_settings.php
 *
 * Amaç: MesChain Sync modülü için kullanıcı ayarları controller dosyası.
 *
 * Loglama: İşlemler user_settings.log dosyasına kaydedilir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 */

class ControllerExtensionModuleUserSettings extends Controller {
    private $error = array();
    
    /**
     * Ana sayfa
     */
    public function index() {
        $this->load->language('extension/module/user_settings');
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/module/user_settings');
        
        // CSS dosyasını yükle
        $this->document->addStyle('view/template/extension/module/meschain_theme.css');
        
        // Tema değiştirme
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['theme'])) {
            $theme = $this->request->post['theme'];
            $this->model_extension_module_user_settings->setUserTheme($this->user->getId(), $theme);
            $this->session->data['success'] = $this->language->get('text_success_theme');
            $this->response->redirect($this->url->link('extension/module/user_settings', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Dil değişkenlerini yükle
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_theme'] = $this->language->get('text_theme');
        $data['text_permissions'] = $this->language->get('text_permissions');
        $data['text_save'] = $this->language->get('text_save');
        $data['text_cancel'] = $this->language->get('text_cancel');
        
        // Hata mesajlarını yükle
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
        
        // Breadcrumb
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
            'href' => $this->url->link('extension/module/user_settings', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Formun gönderileceği URL
        $data['action'] = $this->url->link('extension/module/user_settings', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);
        
        // Tema bilgilerini yükle
        $themes = $this->model_extension_module_user_settings->getThemes();
        $current_theme = $this->model_extension_module_user_settings->getUserTheme($this->user->getId());
        
        $data['themes'] = $themes;
        $data['current_theme'] = $current_theme;
        
        // Kullanıcı bilgilerini yükle
        $data['user_id'] = $this->user->getId();
        $data['username'] = $this->user->getUserName();
        
        // Kullanıcı ayarlarını yükle
        $data['settings'] = $this->model_extension_module_user_settings->getSettings($this->user->getId());
        
        // Kullanıcı izinlerini yükle
        $data['permissions'] = $this->model_extension_module_user_settings->getPermissions($this->user->getId());
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/user_settings', $data));
    }
    
    /**
     * Tema değiştirme AJAX
     */
    public function changeTheme() {
        $this->load->language('extension/module/user_settings');
        
        $json = array();
        
        if (!$this->user->hasPermission('modify', 'extension/module/user_settings')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $this->load->model('extension/module/user_settings');
            
            if (isset($this->request->post['theme'])) {
                $theme = $this->request->post['theme'];
                $this->model_extension_module_user_settings->setUserTheme($this->user->getId(), $theme);
                $json['success'] = $this->language->get('text_success_theme');
            } else {
                $json['error'] = $this->language->get('error_theme');
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Ayar kaydetme AJAX
     */
    public function saveSetting() {
        $this->load->language('extension/module/user_settings');
        
        $json = array();
        
        if (!$this->user->hasPermission('modify', 'extension/module/user_settings')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $this->load->model('extension/module/user_settings');
            
            if (isset($this->request->post['key']) && isset($this->request->post['value'])) {
                $key = $this->request->post['key'];
                $value = $this->request->post['value'];
                $this->model_extension_module_user_settings->setSetting($this->user->getId(), $key, $value);
                $json['success'] = $this->language->get('text_success_setting');
            } else {
                $json['error'] = $this->language->get('error_setting');
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Kullanıcı profili
     */
    public function profile() {
        $this->load->language('extension/module/user_settings');
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/module/user_settings');
        
        // CSS dosyasını yükle
        $this->document->addStyle('view/template/extension/module/meschain_theme.css');
        
        // Profil güncelleme
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateProfile()) {
            $this->load->model('user/user');
            
            // Şifre değişikliği
            if (!empty($this->request->post['password'])) {
                $this->model_user_user->editPassword($this->user->getId(), $this->request->post['password']);
            }
            
            // Kullanıcı bilgilerini güncelle
            $user_info = array(
                'username' => $this->request->post['username'],
                'firstname' => $this->request->post['firstname'],
                'lastname' => $this->request->post['lastname'],
                'email' => $this->request->post['email']
            );
            
            $this->model_user_user->editUser($this->user->getId(), $user_info);
            
            $this->session->data['success'] = $this->language->get('text_success_profile');
            $this->response->redirect($this->url->link('extension/module/user_settings/profile', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Dil değişkenlerini yükle
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_profile'] = $this->language->get('text_profile');
        $data['text_username'] = $this->language->get('text_username');
        $data['text_firstname'] = $this->language->get('text_firstname');
        $data['text_lastname'] = $this->language->get('text_lastname');
        $data['text_email'] = $this->language->get('text_email');
        $data['text_password'] = $this->language->get('text_password');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['text_save'] = $this->language->get('text_save');
        $data['text_cancel'] = $this->language->get('text_cancel');
        
        // Hata mesajlarını yükle
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->error['username'])) {
            $data['error_username'] = $this->error['username'];
        } else {
            $data['error_username'] = '';
        }
        
        if (isset($this->error['firstname'])) {
            $data['error_firstname'] = $this->error['firstname'];
        } else {
            $data['error_firstname'] = '';
        }
        
        if (isset($this->error['lastname'])) {
            $data['error_lastname'] = $this->error['lastname'];
        } else {
            $data['error_lastname'] = '';
        }
        
        if (isset($this->error['email'])) {
            $data['error_email'] = $this->error['email'];
        } else {
            $data['error_email'] = '';
        }
        
        if (isset($this->error['password'])) {
            $data['error_password'] = $this->error['password'];
        } else {
            $data['error_password'] = '';
        }
        
        if (isset($this->error['confirm'])) {
            $data['error_confirm'] = $this->error['confirm'];
        } else {
            $data['error_confirm'] = '';
        }
        
        // Başarı mesajı
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Breadcrumb
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/user_settings', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_profile'),
            'href' => $this->url->link('extension/module/user_settings/profile', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Formun gönderileceği URL
        $data['action'] = $this->url->link('extension/module/user_settings/profile', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/module/user_settings', 'user_token=' . $this->session->data['user_token'], true);
        
        // Kullanıcı bilgilerini yükle
        $this->load->model('user/user');
        $user_info = $this->model_user_user->getUser($this->user->getId());
        
        if ($user_info) {
            $data['username'] = $user_info['username'];
            $data['firstname'] = $user_info['firstname'];
            $data['lastname'] = $user_info['lastname'];
            $data['email'] = $user_info['email'];
        } else {
            $data['username'] = '';
            $data['firstname'] = '';
            $data['lastname'] = '';
            $data['email'] = '';
        }
        
        $data['password'] = '';
        $data['confirm'] = '';
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/user_profile', $data));
    }
    
    /**
     * Profil doğrulama
     */
    protected function validateProfile() {
        if (!$this->user->hasPermission('modify', 'extension/module/user_settings')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if ((utf8_strlen($this->request->post['username']) < 3) || (utf8_strlen($this->request->post['username']) > 20)) {
            $this->error['username'] = $this->language->get('error_username');
        }
        
        $user_info = $this->model_user_user->getUserByUsername($this->request->post['username']);
        
        if ($user_info && ($user_info['user_id'] != $this->user->getId())) {
            $this->error['username'] = $this->language->get('error_exists_username');
        }
        
        if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
            $this->error['firstname'] = $this->language->get('error_firstname');
        }
        
        if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
            $this->error['lastname'] = $this->language->get('error_lastname');
        }
        
        if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['email'] = $this->language->get('error_email');
        }
        
        $user_info = $this->model_user_user->getUserByEmail($this->request->post['email']);
        
        if ($user_info && ($user_info['user_id'] != $this->user->getId())) {
            $this->error['email'] = $this->language->get('error_exists_email');
        }
        
        if ($this->request->post['password']) {
            if ((utf8_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) < 4) || (utf8_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) > 40)) {
                $this->error['password'] = $this->language->get('error_password');
            }
            
            if ($this->request->post['password'] != $this->request->post['confirm']) {
                $this->error['confirm'] = $this->language->get('error_confirm');
            }
        }
        
        return !$this->error;
    }
    
    /**
     * Kurulum
     */
    public function install() {
        $this->load->model('extension/module/user_settings');
        $this->model_extension_module_user_settings->install();
        
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_user_settings', array('module_user_settings_status' => 1));
        
        // Yetki ekle
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/user_settings');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/user_settings');
        
        $this->writeLog('SYSTEM', 'INSTALL', 'Kullanıcı ayarları modülü kuruldu');
    }
    
    /**
     * Kaldırma
     */
    public function uninstall() {
        $this->load->model('extension/module/user_settings');
        $this->model_extension_module_user_settings->uninstall();
        
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_user_settings');
        
        $this->writeLog('SYSTEM', 'UNINSTALL', 'Kullanıcı ayarları modülü kaldırıldı');
    }
    
    /**
     * Log kaydı
     */
    private function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'user_settings.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 