<?php
/**
 * Trendyol Webhooks Model
 * MesChain-Sync Trendyol webhook yönetimi için model dosyası
 */
class ModelExtensionModuleTrendyolWebhooks extends Model {
    
    /**
     * Webhook loglarını getir
     * @param array $data Filtreleme verileri
     * @return array Webhook logları
     */
    public function getWebhookLogs($data = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "trendyol_webhook_log` WHERE 1=1";
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_event_type'])) {
            $sql .= " AND event_type = '" . $this->db->escape($data['filter_event_type']) . "'";
        }
        
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(date_created) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(date_created) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        $sort_data = [
            'date_created',
            'status',
            'event_type',
            'order_number'
        ];
        
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY date_created";
        }
        
        if (isset($data['order']) && ($data['order'] == 'ASC')) {
            $sql .= " ASC";
        } else {
            $sql .= " DESC";
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
     * Webhook log kaydı ekle
     * @param array $data Webhook verisi
     * @return int Log ID
     */
    public function addWebhookLog($data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_webhook_log` SET 
            event_type = '" . $this->db->escape($data['event_type']) . "',
            order_number = '" . $this->db->escape($data['order_number'] ?? '') . "',
            status = '" . $this->db->escape($data['status'] ?? 'pending') . "',
            request_payload = '" . $this->db->escape(json_encode($data['payload'] ?? [])) . "',
            response_payload = '" . $this->db->escape(json_encode($data['response'] ?? [])) . "',
            error_message = '" . $this->db->escape($data['error'] ?? '') . "',
            processing_time = '" . (float)($data['processing_time'] ?? 0) . "',
            date_created = NOW(),
            date_modified = NOW()");
        
        return $this->db->getLastId();
    }
    
    /**
     * Webhook log güncelle
     * @param int $log_id Log ID
     * @param array $data Güncellenecek veriler
     */
    public function updateWebhookLog($log_id, $data) {
        $sql = "UPDATE `" . DB_PREFIX . "trendyol_webhook_log` SET ";
        $updates = [];
        
        if (isset($data['status'])) {
            $updates[] = "status = '" . $this->db->escape($data['status']) . "'";
        }
        
        if (isset($data['response'])) {
            $updates[] = "response_payload = '" . $this->db->escape(json_encode($data['response'])) . "'";
        }
        
        if (isset($data['error'])) {
            $updates[] = "error_message = '" . $this->db->escape($data['error']) . "'";
        }
        
        if (isset($data['processing_time'])) {
            $updates[] = "processing_time = '" . (float)$data['processing_time'] . "'";
        }
        
        $updates[] = "date_modified = NOW()";
        
        $sql .= implode(', ', $updates);
        $sql .= " WHERE webhook_log_id = '" . (int)$log_id . "'";
        
        $this->db->query($sql);
    }
    
    /**
     * Webhook istatistiklerini getir
     * @param array $data Filtre parametreleri
     * @return array İstatistikler
     */
    public function getWebhookStats($data = []) {
        $stats = [];
        
        // Toplam webhook sayısı
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "trendyol_webhook_log`");
        $stats['total'] = $query->row['total'];
        
        // Başarılı webhook sayısı
        $query = $this->db->query("SELECT COUNT(*) as success FROM `" . DB_PREFIX . "trendyol_webhook_log` WHERE status = 'success'");
        $stats['success'] = $query->row['success'];
        
        // Başarısız webhook sayısı
        $query = $this->db->query("SELECT COUNT(*) as failed FROM `" . DB_PREFIX . "trendyol_webhook_log` WHERE status = 'failed'");
        $stats['failed'] = $query->row['failed'];
        
        // Bekleyen webhook sayısı
        $query = $this->db->query("SELECT COUNT(*) as pending FROM `" . DB_PREFIX . "trendyol_webhook_log` WHERE status = 'pending'");
        $stats['pending'] = $query->row['pending'];
        
        // Ortalama işlem süresi
        $query = $this->db->query("SELECT AVG(processing_time) as avg_time FROM `" . DB_PREFIX . "trendyol_webhook_log` WHERE status = 'success'");
        $stats['avg_processing_time'] = $query->row['avg_time'] ?? 0;
        
        // Event tipine göre dağılım
        $query = $this->db->query("SELECT event_type, COUNT(*) as count FROM `" . DB_PREFIX . "trendyol_webhook_log` GROUP BY event_type");
        $stats['by_event_type'] = $query->rows;
        
        // Günlük istatistikler (Son 30 gün)
        $query = $this->db->query("SELECT DATE(date_created) as date, COUNT(*) as count, 
                                   SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count
                                   FROM `" . DB_PREFIX . "trendyol_webhook_log` 
                                   WHERE date_created >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                   GROUP BY DATE(date_created) 
                                   ORDER BY date DESC");
        $stats['daily_stats'] = $query->rows;
        
        return $stats;
    }
    
    /**
     * Webhook konfigürasyon ayarlarını getir
     * @return array Konfigürasyon ayarları
     */
    public function getWebhookConfig() {
        $config = [];
        
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_webhook_config`");
        
        foreach ($query->rows as $row) {
            $config[$row['config_key']] = $row['config_value'];
        }
        
        return $config;
    }
    
    /**
     * Webhook konfigürasyon ayarını güncelle veya ekle
     * @param string $key Konfigürasyon anahtarı
     * @param string $value Konfigürasyon değeri
     */
    public function setWebhookConfig($key, $value) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_webhook_config` WHERE config_key = '" . $this->db->escape($key) . "'");
        
        if ($query->num_rows) {
            $this->db->query("UPDATE `" . DB_PREFIX . "trendyol_webhook_config` SET 
                config_value = '" . $this->db->escape($value) . "',
                date_modified = NOW()
                WHERE config_key = '" . $this->db->escape($key) . "'");
        } else {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_webhook_config` SET 
                config_key = '" . $this->db->escape($key) . "',
                config_value = '" . $this->db->escape($value) . "',
                date_created = NOW(),
                date_modified = NOW()");
        }
    }
    
    /**
     * Webhook tabloları oluştur (Install için)
     */
    public function createWebhookTables() {
        // Webhook log tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_webhook_log` (
            `webhook_log_id` int(11) NOT NULL AUTO_INCREMENT,
            `event_type` varchar(50) NOT NULL,
            `order_number` varchar(50) DEFAULT NULL,
            `status` enum('pending','success','failed') DEFAULT 'pending',
            `request_payload` longtext,
            `response_payload` longtext,
            `error_message` text,
            `processing_time` decimal(10,4) DEFAULT 0.0000,
            `date_created` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`webhook_log_id`),
            KEY `idx_status` (`status`),
            KEY `idx_event_type` (`event_type`),
            KEY `idx_date_created` (`date_created`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Webhook konfigürasyon tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_webhook_config` (
            `config_id` int(11) NOT NULL AUTO_INCREMENT,
            `config_key` varchar(100) NOT NULL,
            `config_value` text,
            `date_created` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`config_id`),
            UNIQUE KEY `config_key` (`config_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }
    
    /**
     * Webhook tabloları sil (Uninstall için)
     */
    public function dropWebhookTables() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_webhook_log`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_webhook_config`");
    }
} 