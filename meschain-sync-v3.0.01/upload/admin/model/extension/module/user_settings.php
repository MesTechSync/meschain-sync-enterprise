<?php
/**
 * user_settings.php
 *
 * Amaç: MesChain Sync modülü için kullanıcı ayarları model dosyası.
 *
 * Loglama: Veritabanı işlemleri user_settings_model.log dosyasına kaydedilir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 */

class ModelExtensionModuleUserSettings extends Model {
    /**
     * Modül kurulumu
     */
    public function install() {
        $this->writeLog('SYSTEM', 'INSTALL_START', 'Kullanıcı ayarları modülü kurulumu başladı');
        
        // Kullanıcı ayarları tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_user_setting` (
            `setting_id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `key` VARCHAR(64) NOT NULL,
            `value` TEXT NOT NULL,
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`setting_id`),
            UNIQUE KEY `user_key` (`user_id`, `key`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Kullanıcı izinleri tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_user_permission` (
            `permission_id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `permission_key` VARCHAR(64) NOT NULL,
            `permission_value` TINYINT(1) NOT NULL DEFAULT 0,
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`permission_id`),
            UNIQUE KEY `user_permission` (`user_id`, `permission_key`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Tema seçenekleri tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_theme` (
            `theme_id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(64) NOT NULL,
            `description` VARCHAR(255) NOT NULL,
            `directory` VARCHAR(64) NOT NULL,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            `date_added` DATETIME NOT NULL,
            PRIMARY KEY (`theme_id`),
            UNIQUE KEY `directory` (`directory`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Varsayılan tema ekle
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_theme` SET 
            `name` = 'Sütlü Kahve & Deniz Mavisi',
            `description` = 'MesChain Sync varsayılan tema',
            `directory` = 'default',
            `status` = 1,
            `date_added` = NOW()");
        
        $this->writeLog('SYSTEM', 'INSTALL_COMPLETE', 'Kullanıcı ayarları modülü kurulumu tamamlandı');
    }
    
    /**
     * Modülü kaldır
     */
    public function uninstall() {
        $this->writeLog('SYSTEM', 'UNINSTALL_START', 'Kullanıcı ayarları modülü kaldırılıyor');
        
        // Tabloları silme (isteğe bağlı)
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_user_setting`");
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_user_permission`");
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_theme`");
        
        $this->writeLog('SYSTEM', 'UNINSTALL_COMPLETE', 'Kullanıcı ayarları modülü kaldırıldı');
    }
    
    /**
     * Kullanıcı ayarını kaydet
     */
    public function setSetting($user_id, $key, $value) {
        $this->writeLog('USER', 'SET_SETTING', "Kullanıcı ayarı kaydediliyor: User=$user_id, Key=$key");
        
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_user_setting` SET
            `user_id` = '" . (int)$user_id . "',
            `key` = '" . $this->db->escape($key) . "',
            `value` = '" . $this->db->escape($value) . "',
            `date_added` = NOW(),
            `date_modified` = NOW()
            ON DUPLICATE KEY UPDATE
            `value` = '" . $this->db->escape($value) . "',
            `date_modified` = NOW()");
    }
    
    /**
     * Kullanıcı ayarını getir
     */
    public function getSetting($user_id, $key, $default = null) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_user_setting` 
            WHERE `user_id` = '" . (int)$user_id . "' AND `key` = '" . $this->db->escape($key) . "'");
        
        if ($query->num_rows) {
            return $query->row['value'];
        }
        
        return $default;
    }
    
    /**
     * Kullanıcı ayarlarını getir
     */
    public function getSettings($user_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_user_setting` 
            WHERE `user_id` = '" . (int)$user_id . "'");
        
        $settings = array();
        foreach ($query->rows as $row) {
            $settings[$row['key']] = $row['value'];
        }
        
        return $settings;
    }
    
    /**
     * Kullanıcı ayarını sil
     */
    public function deleteSetting($user_id, $key) {
        $this->writeLog('USER', 'DELETE_SETTING', "Kullanıcı ayarı siliniyor: User=$user_id, Key=$key");
        
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_user_setting` 
            WHERE `user_id` = '" . (int)$user_id . "' AND `key` = '" . $this->db->escape($key) . "'");
    }
    
    /**
     * Kullanıcı izinlerini kaydet
     */
    public function setPermission($user_id, $permission_key, $permission_value) {
        $this->writeLog('USER', 'SET_PERMISSION', "Kullanıcı izni kaydediliyor: User=$user_id, Key=$permission_key, Value=$permission_value");
        
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_user_permission` SET
            `user_id` = '" . (int)$user_id . "',
            `permission_key` = '" . $this->db->escape($permission_key) . "',
            `permission_value` = '" . (int)$permission_value . "',
            `date_added` = NOW(),
            `date_modified` = NOW()
            ON DUPLICATE KEY UPDATE
            `permission_value` = '" . (int)$permission_value . "',
            `date_modified` = NOW()");
    }
    
    /**
     * Kullanıcı izinlerini getir
     */
    public function getPermissions($user_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_user_permission` 
            WHERE `user_id` = '" . (int)$user_id . "'");
        
        $permissions = array();
        foreach ($query->rows as $row) {
            $permissions[$row['permission_key']] = (bool)$row['permission_value'];
        }
        
        return $permissions;
    }
    
    /**
     * Kullanıcı izni kontrol et
     */
    public function hasPermission($user_id, $permission_key) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_user_permission` 
            WHERE `user_id` = '" . (int)$user_id . "' AND `permission_key` = '" . $this->db->escape($permission_key) . "'");
        
        if ($query->num_rows) {
            return (bool)$query->row['permission_value'];
        }
        
        return false;
    }
    
    /**
     * Temaları getir
     */
    public function getThemes() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_theme` WHERE `status` = 1 ORDER BY `name`");
        return $query->rows;
    }
    
    /**
     * Tema ekle
     */
    public function addTheme($data) {
        $this->writeLog('USER', 'ADD_THEME', "Tema ekleniyor: " . $data['name']);
        
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_theme` SET
            `name` = '" . $this->db->escape($data['name']) . "',
            `description` = '" . $this->db->escape($data['description']) . "',
            `directory` = '" . $this->db->escape($data['directory']) . "',
            `status` = '" . (int)$data['status'] . "',
            `date_added` = NOW()");
            
        return $this->db->getLastId();
    }
    
    /**
     * Tema sil
     */
    public function deleteTheme($theme_id) {
        $this->writeLog('USER', 'DELETE_THEME', "Tema siliniyor: ID=$theme_id");
        
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_theme` WHERE `theme_id` = '" . (int)$theme_id . "'");
    }
    
    /**
     * Kullanıcı temasını ayarla
     */
    public function setUserTheme($user_id, $theme_directory) {
        $this->setSetting($user_id, 'theme', $theme_directory);
        $this->writeLog('USER', 'SET_THEME', "Kullanıcı teması ayarlandı: User=$user_id, Theme=$theme_directory");
    }
    
    /**
     * Kullanıcı temasını getir
     */
    public function getUserTheme($user_id) {
        return $this->getSetting($user_id, 'theme', 'default');
    }
    
    /**
     * Log kaydı
     */
    private function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'user_settings_model.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 