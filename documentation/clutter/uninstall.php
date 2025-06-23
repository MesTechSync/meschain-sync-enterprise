<?php
/**
 * MesTech Sync - Kaldırma Dosyası
 * 
 * Bu dosya, MesTech Sync modülünün kaldırılmasını gerçekleştirir.
 * Veritabanı tablolarını ve kayıtları temizler.
 */

class ModelExtensionMestechMestechSyncUninstall extends Model {
    /**
     * Kaldırma işlemini gerçekleştirir
     */
    public function uninstall() {
        // Eklentiyi kaldır
        $this->unregisterExtension();
        
        // İzinleri temizle
        $this->clearPermissions();
        
        // Log dosyasına kaydet
        $this->logUninstall();
        
        return true;
    }
    
    /**
     * Eklentiyi kaldır
     */
    private function unregisterExtension() {
        // Eklentiyi extension tablosundan kaldır
        $this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'mestech' AND `code` = 'mestech_sync'");
        
        // Ayarları temizle
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'mestech_mestech_sync'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'mestech_mestech_sync_trendyol'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'mestech_mestech_sync_amazon'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'mestech_mestech_sync_n11'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'mestech_mestech_sync_ebay'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'mestech_mestech_sync_hepsiburada'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'mestech_mestech_sync_ozon'");
        
        // extension_install tablosundan kaldır
        $this->db->query("DELETE FROM `" . DB_PREFIX . "extension_install` WHERE `filename` = 'mestech_sync.ocmod.zip'");
        
        // extension_path tablosundan kaldır
        $this->db->query("DELETE FROM `" . DB_PREFIX . "extension_path` WHERE `path` LIKE 'admin/controller/extension/mestech%'");
    }
    
    /**
     * İzinleri temizle
     */
    private function clearPermissions() {
        // Tüm kullanıcı gruplarından izinleri kaldır
        $this->load->model('user/user_group');
        
        // MesTech Sync ana modülü
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/mestech/mestech_sync');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/mestech/mestech_sync');
        
        // Pazaryerleri
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/mestech/trendyol');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/mestech/trendyol');
        
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/mestech/n11');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/mestech/n11');
        
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/mestech/amazon');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/mestech/amazon');
        
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/mestech/ebay');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/mestech/ebay');
        
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/mestech/hepsiburada');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/mestech/hepsiburada');
        
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/mestech/ozon');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/mestech/ozon');
        
        // Yardımcı modüller
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/mestech/announcement');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/mestech/announcement');
        
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/mestech/help');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/mestech/help');
        
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/mestech/log_viewer');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/mestech/log_viewer');
    }
    
    /**
     * Log dosyasına kaldırma kaydı ekle
     */
    private function logUninstall() {
        $log_file = DIR_LOGS . 'mestech_sync.log';
        
        if (file_exists($log_file)) {
            $handle = fopen($log_file, 'a');
            fwrite($handle, '[' . date('Y-m-d H:i:s') . '] [SYSTEM] [UNINSTALL] MesTech Sync modülü kaldırıldı.' . PHP_EOL);
            fclose($handle);
        }
    }
}

// Kaldırma işlemini başlat
$registry = new Registry();
$loader = new Loader($registry);
$registry->set('load', $loader);
$registry->set('db', new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT));
$registry->set('user', new User($registry));

$model = new ModelExtensionMestechMestechSyncUninstall($registry);
$model->uninstall(); 