<?php
/**
 * trendyol_webhook.php (Model)
 *
 * Amaç: Trendyol webhook bildirimlerini kaydetme ve yönetme işlemleri için model.
 * Bildirimler veritabanında saklanır ve admin panelinde görüntülenir.
 */
class ModelExtensionModuleTrendyolWebhook extends Model {
    
    /**
     * Webhook tablosunu oluştur
     * 
     * @return void
     */
    public function install() {
        // Bildirimler tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_notification` (
                `notification_id` INT(11) NOT NULL AUTO_INCREMENT,
                `type` VARCHAR(50) NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `message` TEXT NOT NULL,
                `data` TEXT,
                `status` TINYINT(1) NOT NULL DEFAULT '0',
                `date_added` DATETIME NOT NULL,
                PRIMARY KEY (`notification_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Webhook ayarları tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_webhook` (
                `webhook_id` INT(11) NOT NULL AUTO_INCREMENT,
                `event_type` VARCHAR(50) NOT NULL,
                `url` VARCHAR(255) NOT NULL,
                `status` TINYINT(1) NOT NULL DEFAULT '1',
                `date_added` DATETIME NOT NULL,
                PRIMARY KEY (`webhook_id`),
                UNIQUE KEY `event_type` (`event_type`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
    }
    
    /**
     * Webhook tablolarını kaldır
     * 
     * @return void
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_notification`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_webhook`");
    }
    
    /**
     * Yeni bildirim ekle
     * 
     * @param array $data Bildirim verileri
     * @return int Bildirim ID
     */
    public function addNotification($data) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "trendyol_notification` 
            SET `type` = '" . $this->db->escape($data['type']) . "', 
                `title` = '" . $this->db->escape($data['title']) . "', 
                `message` = '" . $this->db->escape($data['message']) . "', 
                `data` = '" . $this->db->escape($data['data'] ?? '') . "', 
                `status` = '" . (int)($data['status'] ?? 0) . "', 
                `date_added` = '" . $this->db->escape($data['date_added'] ?? date('Y-m-d H:i:s')) . "'
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Bildirimleri listele
     * 
     * @param array $data Filtre verileri
     * @return array Bildirim listesi
     */
    public function getNotifications($data = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "trendyol_notification`";
        
        $where = [];
        
        if (isset($data['filter_type'])) {
            $where[] = "`type` = '" . $this->db->escape($data['filter_type']) . "'";
        }
        
        if (isset($data['filter_status'])) {
            $where[] = "`status` = '" . (int)$data['filter_status'] . "'";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $sort_data = [
            'notification_id',
            'type',
            'title',
            'status',
            'date_added'
        ];
        
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY `" . $data['sort'] . "`";
        } else {
            $sql .= " ORDER BY `date_added`";
        }
        
        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }
        
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }
            
            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Toplam bildirim sayısını al
     * 
     * @param array $data Filtre verileri
     * @return int Toplam bildirim sayısı
     */
    public function getTotalNotifications($data = []) {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "trendyol_notification`";
        
        $where = [];
        
        if (isset($data['filter_type'])) {
            $where[] = "`type` = '" . $this->db->escape($data['filter_type']) . "'";
        }
        
        if (isset($data['filter_status'])) {
            $where[] = "`status` = '" . (int)$data['filter_status'] . "'";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $query = $this->db->query($sql);
        
        return $query->row['total'];
    }
    
    /**
     * Bildirim detaylarını al
     * 
     * @param int $notification_id Bildirim ID
     * @return array Bildirim detayları
     */
    public function getNotification($notification_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_notification` 
            WHERE `notification_id` = '" . (int)$notification_id . "'
        ");
        
        return $query->row;
    }
    
    /**
     * Bildirim durumunu güncelle
     * 
     * @param int $notification_id Bildirim ID
     * @param int $status Durum (0: Okunmadı, 1: Okundu)
     * @return void
     */
    public function updateNotificationStatus($notification_id, $status) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "trendyol_notification` 
            SET `status` = '" . (int)$status . "' 
            WHERE `notification_id` = '" . (int)$notification_id . "'
        ");
    }
    
    /**
     * Bildirim sil
     * 
     * @param int $notification_id Bildirim ID
     * @return void
     */
    public function deleteNotification($notification_id) {
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "trendyol_notification` 
            WHERE `notification_id` = '" . (int)$notification_id . "'
        ");
    }
    
    /**
     * Webhook ekle
     * 
     * @param array $data Webhook verileri
     * @return int Webhook ID
     */
    public function addWebhook($data) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "trendyol_webhook` 
            SET `event_type` = '" . $this->db->escape($data['event_type']) . "', 
                `url` = '" . $this->db->escape($data['url']) . "', 
                `status` = '" . (int)($data['status'] ?? 1) . "', 
                `date_added` = '" . $this->db->escape($data['date_added'] ?? date('Y-m-d H:i:s')) . "'
            ON DUPLICATE KEY UPDATE 
                `url` = '" . $this->db->escape($data['url']) . "', 
                `status` = '" . (int)($data['status'] ?? 1) . "'
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Webhook listele
     * 
     * @return array Webhook listesi
     */
    public function getWebhooks() {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_webhook` 
            ORDER BY `event_type` ASC
        ");
        
        return $query->rows;
    }
    
    /**
     * Webhook detaylarını al
     * 
     * @param int $webhook_id Webhook ID
     * @return array Webhook detayları
     */
    public function getWebhook($webhook_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_webhook` 
            WHERE `webhook_id` = '" . (int)$webhook_id . "'
        ");
        
        return $query->row;
    }
    
    /**
     * Webhook durumunu güncelle
     * 
     * @param int $webhook_id Webhook ID
     * @param int $status Durum (0: Pasif, 1: Aktif)
     * @return void
     */
    public function updateWebhookStatus($webhook_id, $status) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "trendyol_webhook` 
            SET `status` = '" . (int)$status . "' 
            WHERE `webhook_id` = '" . (int)$webhook_id . "'
        ");
    }
    
    /**
     * Webhook sil
     * 
     * @param int $webhook_id Webhook ID
     * @return void
     */
    public function deleteWebhook($webhook_id) {
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "trendyol_webhook` 
            WHERE `webhook_id` = '" . (int)$webhook_id . "'
        ");
    }
} 