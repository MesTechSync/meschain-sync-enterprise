<?php
/**
 * announcement.php
 *
 * Amaç: MesChain Sync modülü için duyuru yönetim sistemi model dosyası.
 *
 * Loglama: Veritabanı işlemleri announcement_model.log dosyasına kaydedilir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 */

class ModelExtensionModuleAnnouncement extends Model {
    /**
     * Modül kurulumu
     */
    public function install() {
        $this->writeLog('SYSTEM', 'INSTALL_START', 'Duyuru modülü kurulumu başladı');
        
        // Duyuru tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_announcement` (
            `announcement_id` INT(11) NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(128) NOT NULL,
            `content` TEXT NOT NULL,
            `roles` TEXT NOT NULL,
            `date` DATE NOT NULL,
            `expire` DATE NULL DEFAULT NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `template` VARCHAR(32) NOT NULL DEFAULT 'klasik',
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`announcement_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Duyuru görüntüleme tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_announcement_viewed` (
            `viewed_id` INT(11) NOT NULL AUTO_INCREMENT,
            `announcement_id` INT(11) NOT NULL,
            `user_id` INT(11) NOT NULL,
            `date_viewed` DATETIME NOT NULL,
            PRIMARY KEY (`viewed_id`),
            UNIQUE KEY `announcement_user` (`announcement_id`, `user_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Duyuru ekleri tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_announcement_attachment` (
            `attachment_id` INT(11) NOT NULL AUTO_INCREMENT,
            `announcement_id` INT(11) NOT NULL,
            `filename` VARCHAR(255) NOT NULL,
            `date_added` DATETIME NOT NULL,
            PRIMARY KEY (`attachment_id`),
            KEY `announcement_id` (`announcement_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Ekler için klasör oluştur
        $attachment_dir = DIR_IMAGE . 'catalog/announcements';
        if (!is_dir($attachment_dir)) {
            mkdir($attachment_dir, 0777, true);
        }
        
        $this->writeLog('SYSTEM', 'INSTALL_COMPLETE', 'Duyuru modülü kurulumu tamamlandı');
    }
    
    /**
     * Modülü kaldır
     */
    public function uninstall() {
        $this->writeLog('SYSTEM', 'UNINSTALL_START', 'Duyuru modülü kaldırılıyor');
        
        // Tabloları silme (isteğe bağlı)
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_announcement`");
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_announcement_viewed`");
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_announcement_attachment`");
        
        $this->writeLog('SYSTEM', 'UNINSTALL_COMPLETE', 'Duyuru modülü kaldırıldı');
    }
    
    /**
     * Duyuru ekle
     */
    public function addAnnouncement($data) {
        $this->writeLog('USER', 'ADD_ANNOUNCEMENT', 'Duyuru ekleniyor: ' . $data['title']);
        
        $roles = isset($data['roles']) ? json_encode($data['roles']) : json_encode([]);
        
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_announcement` SET
            `title` = '" . $this->db->escape($data['title']) . "',
            `content` = '" . $this->db->escape($data['content']) . "',
            `roles` = '" . $this->db->escape($roles) . "',
            `date` = '" . $this->db->escape($data['date']) . "',
            `expire` = " . ($data['expire'] ? "'" . $this->db->escape($data['expire']) . "'" : "NULL") . ",
            `active` = '" . (int)$data['active'] . "',
            `template` = '" . $this->db->escape($data['template']) . "',
            `date_added` = NOW(),
            `date_modified` = NOW()");
            
        $announcement_id = $this->db->getLastId();
        
        // Ekler
        if (isset($data['attachments']) && is_array($data['attachments'])) {
            foreach ($data['attachments'] as $attachment) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_announcement_attachment` SET
                    `announcement_id` = '" . (int)$announcement_id . "',
                    `filename` = '" . $this->db->escape($attachment) . "',
                    `date_added` = NOW()");
            }
        }
        
        $this->writeLog('USER', 'ADD_ANNOUNCEMENT_COMPLETE', 'Duyuru eklendi: ID=' . $announcement_id);
        
        return $announcement_id;
    }
    
    /**
     * Duyuru güncelle
     */
    public function updateAnnouncement($data) {
        $this->writeLog('USER', 'UPDATE_ANNOUNCEMENT', 'Duyuru güncelleniyor: ID=' . $data['announcement_id']);
        
        $roles = isset($data['roles']) ? json_encode($data['roles']) : json_encode([]);
        
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_announcement` SET
            `title` = '" . $this->db->escape($data['title']) . "',
            `content` = '" . $this->db->escape($data['content']) . "',
            `roles` = '" . $this->db->escape($roles) . "',
            `date` = '" . $this->db->escape($data['date']) . "',
            `expire` = " . ($data['expire'] ? "'" . $this->db->escape($data['expire']) . "'" : "NULL") . ",
            `active` = '" . (int)$data['active'] . "',
            `template` = '" . $this->db->escape($data['template']) . "',
            `date_modified` = NOW()
            WHERE `announcement_id` = '" . (int)$data['announcement_id'] . "'");
            
        // Ekleri güncelle
        if (isset($data['attachments'])) {
            // Mevcut ekleri sil
            $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_announcement_attachment` WHERE `announcement_id` = '" . (int)$data['announcement_id'] . "'");
            
            // Yeni ekleri ekle
            foreach ($data['attachments'] as $attachment) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_announcement_attachment` SET
                    `announcement_id` = '" . (int)$data['announcement_id'] . "',
                    `filename` = '" . $this->db->escape($attachment) . "',
                    `date_added` = NOW()");
            }
        }
        
        $this->writeLog('USER', 'UPDATE_ANNOUNCEMENT_COMPLETE', 'Duyuru güncellendi: ID=' . $data['announcement_id']);
    }
    
    /**
     * Duyuru sil
     */
    public function deleteAnnouncement($announcement_id) {
        $this->writeLog('USER', 'DELETE_ANNOUNCEMENT', 'Duyuru siliniyor: ID=' . $announcement_id);
        
        // Ekleri sil
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_announcement_attachment` WHERE `announcement_id` = '" . (int)$announcement_id . "'");
        foreach ($query->rows as $attachment) {
            $file = DIR_IMAGE . 'catalog/announcements/' . $attachment['filename'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        // Veritabanı kayıtlarını sil
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_announcement_attachment` WHERE `announcement_id` = '" . (int)$announcement_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_announcement_viewed` WHERE `announcement_id` = '" . (int)$announcement_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_announcement` WHERE `announcement_id` = '" . (int)$announcement_id . "'");
        
        $this->writeLog('USER', 'DELETE_ANNOUNCEMENT_COMPLETE', 'Duyuru silindi: ID=' . $announcement_id);
    }
    
    /**
     * Duyuru getir
     */
    public function getAnnouncement($announcement_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_announcement` WHERE `announcement_id` = '" . (int)$announcement_id . "'");
        
        $announcement = $query->row;
        
        if ($announcement) {
            // Ekleri yükle
            $attachments_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_announcement_attachment` WHERE `announcement_id` = '" . (int)$announcement_id . "'");
            $attachments = array();
            
            foreach ($attachments_query->rows as $attachment) {
                $attachments[] = $attachment['filename'];
            }
            
            $announcement['attachments'] = $attachments;
        }
        
        return $announcement;
    }
    
    /**
     * Tüm duyuruları getir
     */
    public function getAnnouncements() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_announcement` ORDER BY `date` DESC");
        return $query->rows;
    }
    
    /**
     * Kullanıcı için aktif duyuruları getir
     */
    public function getActiveAnnouncementsForUser($user_id, $user_group_id) {
        $now = date('Y-m-d');
        
        $query = $this->db->query("SELECT a.* FROM `" . DB_PREFIX . "meschain_announcement` a 
            LEFT JOIN `" . DB_PREFIX . "meschain_announcement_viewed` v ON (a.announcement_id = v.announcement_id AND v.user_id = '" . (int)$user_id . "')
            WHERE a.active = 1 
            AND a.date <= '" . $this->db->escape($now) . "' 
            AND (a.expire IS NULL OR a.expire >= '" . $this->db->escape($now) . "')
            AND v.viewed_id IS NULL
            AND JSON_CONTAINS(a.roles, '\"" . (int)$user_group_id . "\"')
            ORDER BY a.date DESC
            LIMIT 1");
            
        $announcement = $query->row;
        
        if ($announcement) {
            // Ekleri yükle
            $attachments_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_announcement_attachment` WHERE `announcement_id` = '" . (int)$announcement['announcement_id'] . "'");
            $attachments = array();
            
            foreach ($attachments_query->rows as $attachment) {
                $attachments[] = $attachment['filename'];
            }
            
            $announcement['attachments'] = $attachments;
        }
        
        return $announcement;
    }
    
    /**
     * Duyuruyu görüntülendi olarak işaretle
     */
    public function markAnnouncementAsViewed($announcement_id, $user_id) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_announcement_viewed` SET
            `announcement_id` = '" . (int)$announcement_id . "',
            `user_id` = '" . (int)$user_id . "',
            `date_viewed` = NOW()
            ON DUPLICATE KEY UPDATE `date_viewed` = NOW()");
            
        $this->writeLog('USER', 'MARK_VIEWED', 'Duyuru görüntülendi: ID=' . $announcement_id . ', User=' . $user_id);
    }
    
    /**
     * Ek dosyası yükle
     */
    public function uploadAttachment($file) {
        if (!empty($file['tmp_name']) && is_file($file['tmp_name'])) {
            // Güvenli dosya adı oluştur
            $filename = md5(mt_rand()) . '_' . token(8) . '_' . basename($file['name']);
            $target = DIR_IMAGE . 'catalog/announcements/' . $filename;
            
            // Dosyayı taşı
            if (move_uploaded_file($file['tmp_name'], $target)) {
                $this->writeLog('USER', 'UPLOAD_ATTACHMENT', 'Ek dosyası yüklendi: ' . $filename);
                return $filename;
            }
        }
        
        return false;
    }
    
    /**
     * Log kaydı
     */
    private function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'announcement_model.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 