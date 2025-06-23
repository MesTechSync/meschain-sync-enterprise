<?php
/**
 * MesTech Sync - Kurulum Dosyası
 * 
 * Bu dosya, MesTech Sync modülünün kurulumunu gerçekleştirir.
 * Veritabanı tablolarını oluşturur ve gerekli izinleri tanımlar.
 */

class ModelExtensionMestechMestechSyncInstall extends Model {
    /**
     * Kurulum işlemini gerçekleştirir
     */
    public function install() {
        // Veritabanı tablolarını oluştur
        $this->createTables();
        
        // Eklentiyi kaydet
        $this->registerExtension();
        
        // İzinleri ayarla
        $this->setPermissions();
        
        // Mestech klasörünü eklenti tipi olarak ekle
        $this->addExtensionType();
        
        // Log dosyasını oluştur
        $this->createLogFile();
        
        return true;
    }
    
    /**
     * Veritabanı tablolarını oluşturur
     */
    private function createTables() {
        // MesTech Sync ayarları tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mestech_sync_setting` (
                `setting_id` INT(11) NOT NULL AUTO_INCREMENT,
                `store_id` INT(11) NOT NULL DEFAULT '0',
                `code` VARCHAR(128) NOT NULL,
                `key` VARCHAR(128) NOT NULL,
                `value` TEXT NOT NULL,
                `serialized` TINYINT(1) NOT NULL,
                PRIMARY KEY (`setting_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // MesTech Sync log tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mestech_sync_log` (
                `log_id` INT(11) NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) NOT NULL DEFAULT '0',
                `log_date` DATETIME NOT NULL,
                `log_type` VARCHAR(32) NOT NULL,
                `log_action` VARCHAR(64) NOT NULL,
                `log_message` TEXT NOT NULL,
                `log_data` TEXT,
                PRIMARY KEY (`log_id`),
                KEY `log_date` (`log_date`),
                KEY `log_type` (`log_type`),
                KEY `log_action` (`log_action`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // MesTech Sync kullanıcı ayarları tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mestech_sync_user_setting` (
                `user_setting_id` INT(11) NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) NOT NULL,
                `key` VARCHAR(128) NOT NULL,
                `value` TEXT NOT NULL,
                `serialized` TINYINT(1) NOT NULL,
                PRIMARY KEY (`user_setting_id`),
                KEY `user_id` (`user_id`),
                KEY `key` (`key`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
    }
    
    /**
     * Eklentiyi kaydet
     */
    private function registerExtension() {
        // Eklentiyi extension tablosuna ekle
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "extension` 
            (`type`, `code`) VALUES 
            ('mestech', 'mestech_sync')
        ");
    }
    
    /**
     * İzinleri ayarla
     */
    private function setPermissions() {
        // Tüm kullanıcı gruplarına izin ver
        $this->load->model('user/user_group');
        
        // MesTech Sync ana modülü
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mestech/mestech_sync');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mestech/mestech_sync');
        
        // Pazaryerleri
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mestech/trendyol');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mestech/trendyol');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mestech/n11');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mestech/n11');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mestech/amazon');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mestech/amazon');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mestech/ebay');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mestech/ebay');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mestech/hepsiburada');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mestech/hepsiburada');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mestech/ozon');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mestech/ozon');
        
        // Yardımcı modüller
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mestech/announcement');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mestech/announcement');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mestech/help');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mestech/help');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/mestech/log_viewer');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/mestech/log_viewer');
    }
    
    /**
     * Mestech klasörünü eklenti tipi olarak ekle
     */
    private function addExtensionType() {
        // extension_install tablosuna mestech tipini ekle
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "extension_install` 
            (`extension_install_id`, `extension_download_id`, `filename`, `date_added`) VALUES 
            (NULL, '0', 'mestech_sync.ocmod.zip', NOW())
        ");
        
        // extension_path tablosuna mestech tipini ekle
        $extension_install_id = $this->db->getLastId();
        
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "extension_path` 
            (`extension_path_id`, `extension_install_id`, `path`) VALUES 
            (NULL, '" . (int)$extension_install_id . "', 'admin/controller/extension/mestech')
        ");
    }
    
    /**
     * Log dosyasını oluştur
     */
    private function createLogFile() {
        $log_file = DIR_LOGS . 'mestech_sync.log';
        
        if (!file_exists($log_file)) {
            $handle = fopen($log_file, 'w');
            fwrite($handle, '[' . date('Y-m-d H:i:s') . '] [SYSTEM] [INSTALL] MesTech Sync modülü kuruldu.' . PHP_EOL);
            fclose($handle);
        }
    }
}

// Kurulumu başlat
$registry = new Registry();
$loader = new Loader($registry);
$registry->set('load', $loader);
$registry->set('db', new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT));
$registry->set('user', new User($registry));

$model = new ModelExtensionMestechMestechSyncInstall($registry);
$model->install(); 