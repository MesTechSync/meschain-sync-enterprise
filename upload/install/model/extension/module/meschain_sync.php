<?php
/**
 * meschain_sync.php
 *
 * MesChain Sync modülünün kurulum işlemlerini yöneten model sınıfı
 */
class ModelExtensionModuleMeschainSync extends Model {
    /**
     * Modülü kur
     */
    public function install() {
        // Veritabanı tablolarını oluştur
        $this->installDatabase();
        
        // Modül izinlerini ayarla
        $this->installPermissions();
        
        // Modül ayarlarını kaydet
        $this->installSettings();
        
        // Log dosyasına kaydet
        $this->log('MesChain Sync modülü kuruldu');
    }
    
    /**
     * Modülü kaldır
     */
    public function uninstall() {
        // Veritabanı tablolarını kaldır
        $this->uninstallDatabase();
        
        // Modül ayarlarını kaldır
        $this->uninstallSettings();
        
        // Log dosyasına kaydet
        $this->log('MesChain Sync modülü kaldırıldı');
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
     * Veritabanı tablolarını kaldır
     */
    private function uninstallDatabase() {
        $sql_file = DIR_APPLICATION . 'sql/uninstall.sql';
        
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
    
    /**
     * Modül ayarlarını kaydet
     */
    private function installSettings() {
        $settings = array(
            'module_meschain_sync_status' => 1,
            'module_meschain_sync_theme' => 'sutlu_kahve',
            'module_meschain_sync_log' => 1
        );
        
        foreach ($settings as $key => $value) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'module_meschain_sync', `key` = '" . $key . "', `value` = '" . $this->db->escape($value) . "'");
        }
    }
    
    /**
     * Modül ayarlarını kaldır
     */
    private function uninstallSettings() {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'module_meschain_sync'");
    }
    
    /**
     * Log kaydı
     */
    private function log($message) {
        $log_file = DIR_LOGS . 'meschain_install.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 