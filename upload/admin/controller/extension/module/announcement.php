<?php
/**
 * announcement.php
 *
 * Amaç: MesChain Sync modülü için duyuru yönetim sistemi controller dosyası.
 *
 * Loglama: Duyuru işlemleri announcement.log dosyasına kaydedilir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 */

class ControllerExtensionModuleAnnouncement extends Controller {
    private $error = array();
    
    /**
     * Ana sayfa
     */
    public function index() {
        $this->load->language('extension/module/announcement');
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/module/announcement');
        
        // CSS dosyasını yükle
        $this->document->addStyle('view/template/extension/module/meschain_theme.css');
        
        // Duyuru ekleme/düzenleme
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (isset($this->request->post['announcement_id'])) {
                $this->model_extension_module_announcement->updateAnnouncement($this->request->post);
                $this->session->data['success'] = $this->language->get('text_success_update');
            } else {
                $this->model_extension_module_announcement->addAnnouncement($this->request->post);
                $this->session->data['success'] = $this->language->get('text_success_add');
            }
            $this->response->redirect($this->url->link('extension/module/announcement', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Duyuru silme
        if (isset($this->request->get['delete']) && $this->validate()) {
            $this->model_extension_module_announcement->deleteAnnouncement($this->request->get['delete']);
            $this->session->data['success'] = $this->language->get('text_success_delete');
            $this->response->redirect($this->url->link('extension/module/announcement', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Dil değişkenlerini yükle
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_add'] = $this->language->get('text_add');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');
        
        $data['column_title'] = $this->language->get('column_title');
        $data['column_content'] = $this->language->get('column_content');
        $data['column_roles'] = $this->language->get('column_roles');
        $data['column_date'] = $this->language->get('column_date');
        $data['column_expire'] = $this->language->get('column_expire');
        $data['column_active'] = $this->language->get('column_active');
        $data['column_action'] = $this->language->get('column_action');
        
        $data['entry_title'] = $this->language->get('entry_title');
        $data['entry_content'] = $this->language->get('entry_content');
        $data['entry_roles'] = $this->language->get('entry_roles');
        $data['entry_date'] = $this->language->get('entry_date');
        $data['entry_expire'] = $this->language->get('entry_expire');
        $data['entry_active'] = $this->language->get('entry_active');
        $data['entry_template'] = $this->language->get('entry_template');
        
        $data['button_add'] = $this->language->get('button_add');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        
        // Hata mesajlarını yükle
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->error['title'])) {
            $data['error_title'] = $this->error['title'];
        } else {
            $data['error_title'] = '';
        }
        
        if (isset($this->error['content'])) {
            $data['error_content'] = $this->error['content'];
        } else {
            $data['error_content'] = '';
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
            'href' => $this->url->link('extension/module/announcement', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Linkler
        $data['add'] = $this->url->link('extension/module/announcement/add', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        // Duyuruları listele
        $data['announcements'] = array();
        
        $announcements = $this->model_extension_module_announcement->getAnnouncements();
        
        foreach ($announcements as $announcement) {
            $data['announcements'][] = array(
                'announcement_id' => $announcement['announcement_id'],
                'title'           => $announcement['title'],
                'content'         => mb_substr(strip_tags($announcement['content']), 0, 100) . '...',
                'roles'           => implode(', ', json_decode($announcement['roles'], true)),
                'date'            => date($this->language->get('date_format_short'), strtotime($announcement['date'])),
                'expire'          => $announcement['expire'] ? date($this->language->get('date_format_short'), strtotime($announcement['expire'])) : $this->language->get('text_no_expire'),
                'active'          => $announcement['active'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
                'edit'            => $this->url->link('extension/module/announcement/edit', 'user_token=' . $this->session->data['user_token'] . '&announcement_id=' . $announcement['announcement_id'], true),
                'delete'          => $this->url->link('extension/module/announcement', 'user_token=' . $this->session->data['user_token'] . '&delete=' . $announcement['announcement_id'], true)
            );
        }
        
        // Şablon değişkenleri
        $data['user_token'] = $this->session->data['user_token'];
        
        // Kullanıcı rolleri
        $this->load->model('user/user_group');
        $user_groups = $this->model_user_user_group->getUserGroups();
        
        $data['user_roles'] = array();
        foreach ($user_groups as $user_group) {
            $data['user_roles'][] = array(
                'user_group_id' => $user_group['user_group_id'],
                'name'          => $user_group['name']
            );
        }
        
        // Duyuru şablonları
        $data['templates'] = array(
            'klasik' => $this->language->get('text_template_classic'),
            'uyari'  => $this->language->get('text_template_warning'),
            'basari' => $this->language->get('text_template_success'),
            'bilgi'  => $this->language->get('text_template_info'),
            'ozel'   => $this->language->get('text_template_special')
        );
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/announcement_list', $data));
    }
    
    /**
     * Duyuru ekleme sayfası
     */
    public function add() {
        $this->load->language('extension/module/announcement');
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->document->addStyle('view/template/extension/module/meschain_theme.css');
        
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_form'] = $this->language->get('text_add');
        
        $data['entry_title'] = $this->language->get('entry_title');
        $data['entry_content'] = $this->language->get('entry_content');
        $data['entry_roles'] = $this->language->get('entry_roles');
        $data['entry_date'] = $this->language->get('entry_date');
        $data['entry_expire'] = $this->language->get('entry_expire');
        $data['entry_active'] = $this->language->get('entry_active');
        $data['entry_template'] = $this->language->get('entry_template');
        
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        
        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->error['title'])) {
            $data['error_title'] = $this->error['title'];
        } else {
            $data['error_title'] = '';
        }
        
        if (isset($this->error['content'])) {
            $data['error_content'] = $this->error['content'];
        } else {
            $data['error_content'] = '';
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
            'href' => $this->url->link('extension/module/announcement', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_add'),
            'href' => $this->url->link('extension/module/announcement/add', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Formun gönderileceği URL
        $data['action'] = $this->url->link('extension/module/announcement', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/module/announcement', 'user_token=' . $this->session->data['user_token'], true);
        
        // Form değerleri
        $data['announcement'] = array(
            'title'    => '',
            'content'  => '',
            'roles'    => array(),
            'date'     => date('Y-m-d'),
            'expire'   => '',
            'active'   => 1,
            'template' => 'klasik'
        );
        
        // Kullanıcı rolleri
        $this->load->model('user/user_group');
        $user_groups = $this->model_user_user_group->getUserGroups();
        
        $data['user_roles'] = array();
        foreach ($user_groups as $user_group) {
            $data['user_roles'][] = array(
                'user_group_id' => $user_group['user_group_id'],
                'name'          => $user_group['name']
            );
        }
        
        // Duyuru şablonları
        $data['templates'] = array(
            'klasik' => $this->language->get('text_template_classic'),
            'uyari'  => $this->language->get('text_template_warning'),
            'basari' => $this->language->get('text_template_success'),
            'bilgi'  => $this->language->get('text_template_info'),
            'ozel'   => $this->language->get('text_template_special')
        );
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/announcement_form', $data));
    }
    
    /**
     * Duyuru düzenleme sayfası
     */
    public function edit() {
        $this->load->language('extension/module/announcement');
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/module/announcement');
        
        $this->document->addStyle('view/template/extension/module/meschain_theme.css');
        
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_form'] = $this->language->get('text_edit');
        
        $data['entry_title'] = $this->language->get('entry_title');
        $data['entry_content'] = $this->language->get('entry_content');
        $data['entry_roles'] = $this->language->get('entry_roles');
        $data['entry_date'] = $this->language->get('entry_date');
        $data['entry_expire'] = $this->language->get('entry_expire');
        $data['entry_active'] = $this->language->get('entry_active');
        $data['entry_template'] = $this->language->get('entry_template');
        
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        
        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->error['title'])) {
            $data['error_title'] = $this->error['title'];
        } else {
            $data['error_title'] = '';
        }
        
        if (isset($this->error['content'])) {
            $data['error_content'] = $this->error['content'];
        } else {
            $data['error_content'] = '';
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
            'href' => $this->url->link('extension/module/announcement', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Duyuru ID
        if (isset($this->request->get['announcement_id'])) {
            $announcement_id = $this->request->get['announcement_id'];
        } else {
            $announcement_id = 0;
        }
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_edit'),
            'href' => $this->url->link('extension/module/announcement/edit', 'user_token=' . $this->session->data['user_token'] . '&announcement_id=' . $announcement_id, true)
        );
        
        // Formun gönderileceği URL
        $data['action'] = $this->url->link('extension/module/announcement', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/module/announcement', 'user_token=' . $this->session->data['user_token'], true);
        
        // Duyuru bilgilerini yükle
        $announcement_info = $this->model_extension_module_announcement->getAnnouncement($announcement_id);
        
        if ($announcement_info) {
            $data['announcement'] = array(
                'announcement_id' => $announcement_info['announcement_id'],
                'title'           => $announcement_info['title'],
                'content'         => $announcement_info['content'],
                'roles'           => json_decode($announcement_info['roles'], true),
                'date'            => $announcement_info['date'],
                'expire'          => $announcement_info['expire'],
                'active'          => $announcement_info['active'],
                'template'        => $announcement_info['template'] ? $announcement_info['template'] : 'klasik'
            );
        } else {
            $this->response->redirect($this->url->link('extension/module/announcement', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Kullanıcı rolleri
        $this->load->model('user/user_group');
        $user_groups = $this->model_user_user_group->getUserGroups();
        
        $data['user_roles'] = array();
        foreach ($user_groups as $user_group) {
            $data['user_roles'][] = array(
                'user_group_id' => $user_group['user_group_id'],
                'name'          => $user_group['name']
            );
        }
        
        // Duyuru şablonları
        $data['templates'] = array(
            'klasik' => $this->language->get('text_template_classic'),
            'uyari'  => $this->language->get('text_template_warning'),
            'basari' => $this->language->get('text_template_success'),
            'bilgi'  => $this->language->get('text_template_info'),
            'ozel'   => $this->language->get('text_template_special')
        );
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/announcement_form', $data));
    }
    
    /**
     * Form doğrulama
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/announcement')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if ((utf8_strlen($this->request->post['title']) < 3) || (utf8_strlen($this->request->post['title']) > 128)) {
            $this->error['title'] = $this->language->get('error_title');
        }
        
        if (utf8_strlen($this->request->post['content']) < 10) {
            $this->error['content'] = $this->language->get('error_content');
        }
        
        return !$this->error;
    }
    
    /**
     * Kurulum
     */
    public function install() {
        $this->load->model('extension/module/announcement');
        $this->model_extension_module_announcement->install();
        
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_announcement', array('module_announcement_status' => 1));
        
        // Yetki ekle
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/announcement');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/announcement');
        
        $this->writeLog('SYSTEM', 'INSTALL', 'Duyuru modülü kuruldu');
    }
    
    /**
     * Kaldırma
     */
    public function uninstall() {
        $this->load->model('extension/module/announcement');
        $this->model_extension_module_announcement->uninstall();
        
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_announcement');
        
        $this->writeLog('SYSTEM', 'UNINSTALL', 'Duyuru modülü kaldırıldı');
    }
    
    /**
     * Log kaydı
     */
    private function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'announcement.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 