<?php
/**
 * meschain_sync.php
 *
 * MesChain Sync modülünün kurulumunu yöneten kontrolcü sınıfı
 */
class ControllerExtensionModuleMeschainSync extends Controller {
    /**
     * Kurulum adımlarını görüntüle
     */
    public function index() {
        $this->load->language('extension/module/meschain_sync');
        $this->document->setTitle($this->language->get('heading_title'));
        
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_step_1'] = $this->language->get('text_step_1');
        $data['text_step_2'] = $this->language->get('text_step_2');
        $data['text_step_3'] = $this->language->get('text_step_3');
        $data['text_step_4'] = $this->language->get('text_step_4');
        $data['text_install'] = $this->language->get('text_install');
        $data['text_upgrade'] = $this->language->get('text_upgrade');
        $data['text_success'] = $this->language->get('text_success');
        
        $data['button_continue'] = $this->language->get('button_continue');
        $data['button_back'] = $this->language->get('button_back');
        
        $data['action'] = $this->url->link('extension/module/meschain_sync/install', '', true);
        $data['back'] = $this->url->link('install/step_4', '', true);
        
        $this->response->setOutput($this->load->view('extension/module/meschain_sync', $data));
    }
    
    /**
     * Kurulum işlemini gerçekleştir
     */
    public function install() {
        $this->load->language('extension/module/meschain_sync');
        
        // Veritabanı tablolarını oluştur
        $this->installDatabase();
        
        // Modül dosyalarını kopyala
        $this->installFiles();
        
        // Modül izinlerini ayarla
        $this->installPermissions();
        
        // Başarılı mesajı göster
        $this->session->data['success'] = $this->language->get('text_success_install');
        
        // Yönetici paneline yönlendir
        $this->response->redirect($this->url->link('install/step_4', '', true));
    }
    
    /**
     * Veritabanı tablolarını oluştur
     */
    private function installDatabase() {
        $sql_file = DIR_APPLICATION . 'sql/install.sql';
        
        if (file_exists($sql_file)) {
            $lines = file($sql_file);
            
            if ($lines) {
                $sql = '';
                
                foreach ($lines as $line) {
                    if ($line && (substr($line, 0, 2) != '--') && (substr($line, 0, 1) != '#')) {
                        $sql .= $line;
                        
                        if (preg_match('/;\s*$/', $line)) {
                            $this->db->query($sql);
                            $sql = '';
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Modül dosyalarını kopyala
     */
    private function installFiles() {
        // Admin dosyalarını kopyala
        $this->copyFiles(DIR_APPLICATION . '../admin/', DIR_OPENCART . 'admin/');
        
        // Catalog dosyalarını kopyala
        $this->copyFiles(DIR_APPLICATION . '../catalog/', DIR_OPENCART . 'catalog/');
        
        // System dosyalarını kopyala
        $this->copyFiles(DIR_APPLICATION . '../system/', DIR_OPENCART . 'system/');
    }
    
    /**
     * Dosyaları kopyala
     */
    private function copyFiles($source, $destination) {
        if (is_dir($source)) {
            $directory = dir($source);
            
            if (!is_dir($destination)) {
                mkdir($destination, 0777, true);
            }
            
            while (false !== ($file = $directory->read())) {
                if (($file != '.') && ($file != '..')) {
                    $source_file = $source . $file;
                    $destination_file = $destination . $file;
                    
                    if (is_dir($source_file)) {
                        $this->copyFiles($source_file . '/', $destination_file . '/');
                    } else {
                        copy($source_file, $destination_file);
                    }
                }
            }
            
            $directory->close();
        } else {
            copy($source, $destination);
        }
    }
    
    /**
     * Modül izinlerini ayarla
     */
    private function installPermissions() {
        $this->load->model('user/user_group');
        
        // Yönetici izinleri
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/trendyol');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/trendyol');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/n11');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/n11');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/amazon');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/amazon');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/ebay');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/ebay');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/hepsiburada');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/hepsiburada');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/ozon');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/ozon');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/announcement');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/announcement');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/help');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/help');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/log_viewer');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/log_viewer');
    }
} 