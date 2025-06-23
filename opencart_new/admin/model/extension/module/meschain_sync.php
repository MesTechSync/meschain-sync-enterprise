<?php
class ModelExtensionModuleMeschainSync extends Model {
    
    public function install() {
        // Create module settings table if not exists
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_sync_settings` (
                `setting_id` int(11) NOT NULL AUTO_INCREMENT,
                `marketplace` varchar(50) NOT NULL,
                `setting_key` varchar(100) NOT NULL,
                `setting_value` text,
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`setting_id`),
                UNIQUE KEY `marketplace_key` (`marketplace`, `setting_key`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Create sync logs table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_sync_logs` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `marketplace` varchar(50) NOT NULL,
                `action` varchar(100) NOT NULL,
                `message` text,
                `status` enum('success','error','warning') DEFAULT 'success',
                `date_added` datetime NOT NULL,
                PRIMARY KEY (`log_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
    }
    
    public function uninstall() {
        // Keep tables for data preservation
        // Users can manually drop tables if needed
    }
    
    public function getSettings($marketplace = null) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_sync_settings`";
        
        if ($marketplace) {
            $sql .= " WHERE `marketplace` = '" . $this->db->escape($marketplace) . "'";
        }
        
        $query = $this->db->query($sql);
        
        $settings = array();
        foreach ($query->rows as $row) {
            $settings[$row['marketplace']][$row['setting_key']] = $row['setting_value'];
        }
        
        return $marketplace ? (isset($settings[$marketplace]) ? $settings[$marketplace] : array()) : $settings;
    }
    
    public function saveSetting($marketplace, $key, $value) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_sync_settings` 
            (`marketplace`, `setting_key`, `setting_value`, `date_added`, `date_modified`) 
            VALUES (
                '" . $this->db->escape($marketplace) . "',
                '" . $this->db->escape($key) . "',
                '" . $this->db->escape($value) . "',
                NOW(),
                NOW()
            ) ON DUPLICATE KEY UPDATE 
            `setting_value` = '" . $this->db->escape($value) . "',
            `date_modified` = NOW()
        ");
    }
    
    public function saveSettings($marketplace, $settings) {
        foreach ($settings as $key => $value) {
            $this->saveSetting($marketplace, $key, $value);
        }
    }
    
    public function addLog($marketplace, $action, $message, $status = 'success') {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_sync_logs` 
            (`marketplace`, `action`, `message`, `status`, `date_added`) 
            VALUES (
                '" . $this->db->escape($marketplace) . "',
                '" . $this->db->escape($action) . "',
                '" . $this->db->escape($message) . "',
                '" . $this->db->escape($status) . "',
                NOW()
            )
        ");
    }
    
    public function getLogs($marketplace = null, $limit = 100) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_sync_logs`";
        
        if ($marketplace) {
            $sql .= " WHERE `marketplace` = '" . $this->db->escape($marketplace) . "'";
        }
        
        $sql .= " ORDER BY `date_added` DESC LIMIT " . (int)$limit;
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    public function clearLogs($marketplace = null) {
        $sql = "DELETE FROM `" . DB_PREFIX . "meschain_sync_logs`";
        
        if ($marketplace) {
            $sql .= " WHERE `marketplace` = '" . $this->db->escape($marketplace) . "'";
        }
        
        $this->db->query($sql);
    }
    
    public function testConnection($marketplace, $settings) {
        switch ($marketplace) {
            case 'trendyol':
                return $this->testTrendyolConnection($settings);
            default:
                return array('success' => false, 'message' => 'Desteklenmeyen marketplace: ' . $marketplace);
        }
    }
    
    private function testTrendyolConnection($settings) {
        if (empty($settings['seller_id']) || empty($settings['api_key']) || empty($settings['api_secret'])) {
            return array('success' => false, 'message' => 'Seller ID, API Key ve API Secret alanları zorunludur.');
        }
        
        // Use our standalone API server
        $url = 'http://localhost:8091/?action=test';
        $data = array(
            'seller_id' => $settings['seller_id'],
            'api_key' => $settings['api_key'],
            'api_secret' => $settings['api_secret']
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return array('success' => false, 'message' => 'CURL Hatası: ' . $error);
        }
        
        if ($httpCode !== 200) {
            return array('success' => false, 'message' => 'HTTP Hatası: ' . $httpCode);
        }
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array('success' => false, 'message' => 'JSON Parse Hatası: ' . json_last_error_msg());
        }
        
        return $result;
    }
}