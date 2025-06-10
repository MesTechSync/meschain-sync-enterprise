<?php
/**
 * MesChain Alert Manager
 * Gerçek zamanlı alert ve bildirim sistemi
 * @author MesChain-Sync Team
 * @version 1.0.0
 */

class MesChainAlertManager {
    private $db;
    private $logger;
    private $config;
    
    // Alert seviyeleri
    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';
    
    // Alert tipleri
    const TYPE_HEALTH_CHECK = 'health_check';
    const TYPE_ERROR_RATE = 'error_rate';
    const TYPE_RESPONSE_TIME = 'response_time';
    const TYPE_QUEUE_SIZE = 'queue_size';
    const TYPE_ORDER_VOLUME = 'order_volume';
    
    public function __construct($db, $logger, $config) {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = $config;
    }
    
    /**
     * Sistem durumunu kontrol et ve alert'leri tetikle
     */
    public function checkAndTriggerAlerts($system_health) {
        try {
            // Aktif alert kurallarını getir
            $alert_rules = $this->getActiveAlertRules();
            
            foreach ($alert_rules as $rule) {
                $should_trigger = false;
                $current_value = null;
                $message = '';
                
                switch ($rule['rule_type']) {
                    case self::TYPE_HEALTH_CHECK:
                        $should_trigger = $this->checkHealthAlert($rule, $system_health);
                        $current_value = $system_health['status'] === 'unhealthy' ? 1 : 0;
                        $message = "Sistem durumu: {$system_health['status']}";
                        break;
                        
                    case self::TYPE_ERROR_RATE:
                        $current_value = $system_health['metrics']['error_rate'];
                        $should_trigger = $this->compareValue($current_value, $rule['threshold_value'], $rule['threshold_operator']);
                        $message = "Hata oranı: {$current_value} (Eşik: {$rule['threshold_value']})";
                        break;
                        
                    case self::TYPE_RESPONSE_TIME:
                        $current_value = $this->getAverageResponseTime($system_health['services']);
                        $should_trigger = $this->compareValue($current_value, $rule['threshold_value'], $rule['threshold_operator']);
                        $message = "Ortalama yanıt süresi: {$current_value}ms (Eşik: {$rule['threshold_value']}ms)";
                        break;
                        
                    case self::TYPE_QUEUE_SIZE:
                        $current_value = $system_health['metrics']['queue_size'];
                        $should_trigger = $this->compareValue($current_value, $rule['threshold_value'], $rule['threshold_operator']);
                        $message = "Kuyruk boyutu: {$current_value} (Eşik: {$rule['threshold_value']})";
                        break;
                        
                    case self::TYPE_ORDER_VOLUME:
                        $current_value = $this->getRecentOrderCount($rule['marketplace']);
                        $should_trigger = $this->compareValue($current_value, $rule['threshold_value'], $rule['threshold_operator']);
                        $message = "Sipariş hacmi: {$current_value} (Eşik: {$rule['threshold_value']})";
                        break;
                }
                
                if ($should_trigger) {
                    $this->triggerAlert($rule, $message, $current_value);
                }
            }
            
        } catch (Exception $e) {
            $this->logger->error('Alert kontrol hatası: ' . $e->getMessage());
        }
    }
    
    /**
     * Alert tetikle
     */
    private function triggerAlert($rule, $message, $current_value) {
        try {
            // Cooldown kontrolü - aynı alert çok sık tetiklenmemesi için
            if ($this->isInCooldown($rule['id'])) {
                return;
            }
            
            // Severity belirleme
            $severity = $this->determineSeverity($rule, $current_value);
            
            // Alert geçmişine kaydet
            $alert_id = $this->saveAlertHistory($rule, $message, $severity, $current_value);
            
            // Bildirimleri gönder
            $this->sendNotifications($rule, $message, $severity, $alert_id);
            
            // Alert kuralını güncelle
            $this->updateAlertRule($rule['id']);
            
            $this->logger->info("Alert tetiklendi: {$rule['name']} - {$message}");
            
        } catch (Exception $e) {
            $this->logger->error('Alert tetikleme hatası: ' . $e->getMessage());
        }
    }
    
    /**
     * Bildirimleri gönder
     */
    private function sendNotifications($rule, $message, $severity, $alert_id) {
        $channels = json_decode($rule['alert_channels'], true);
        if (!$channels) return;
        
        $sent_channels = array();
        
        foreach ($channels as $channel) {
            try {
                switch ($channel) {
                    case 'email':
                        if ($this->sendEmailNotification($rule, $message, $severity)) {
                            $sent_channels[] = 'email';
                        }
                        break;
                        
                    case 'webhook':
                        if ($this->sendWebhookNotification($rule, $message, $severity)) {
                            $sent_channels[] = 'webhook';
                        }
                        break;
                }
            } catch (Exception $e) {
                $this->logger->error("Bildirim gönderme hatası ({$channel}): " . $e->getMessage());
            }
        }
        
        // Gönderilen kanalları güncelle
        if (!empty($sent_channels)) {
            $this->updateSentChannels($alert_id, $sent_channels);
        }
    }
    
    /**
     * Email bildirimi gönder
     */
    private function sendEmailNotification($rule, $message, $severity) {
        // Email ayarlarını config'den al
        $admin_email = $this->config->get('config_email');
        $store_name = $this->config->get('config_name');
        
        if (empty($admin_email)) return false;
        
        $subject = "[{$store_name}] MesChain Alert: {$rule['name']}";
        
        $body = "
<html>
<body>
    <h2>MesChain Alert Bildirimi</h2>
    <p><strong>Alert:</strong> {$rule['name']}</p>
    <p><strong>Seviye:</strong> " . strtoupper($severity) . "</p>
    <p><strong>Mesaj:</strong> {$message}</p>
    <p><strong>Zaman:</strong> " . date('Y-m-d H:i:s') . "</p>
    <p><strong>Açıklama:</strong> {$rule['description']}</p>
    
    <hr>
    <p><small>Bu otomatik bir bildirimdir. MesChain Monitoring System tarafından gönderilmiştir.</small></p>
</body>
</html>";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: {$store_name} <{$admin_email}>" . "\r\n";
        
        return mail($admin_email, $subject, $body, $headers);
    }
    
    /**
     * Webhook bildirimi gönder
     */
    private function sendWebhookNotification($rule, $message, $severity) {
        $webhook_url = $this->config->get('meschain_webhook_url');
        
        if (empty($webhook_url)) return false;
        
        $payload = array(
            'alert_name' => $rule['name'],
            'alert_type' => $rule['rule_type'],
            'severity' => $severity,
            'message' => $message,
            'timestamp' => date('c'),
            'marketplace' => $rule['marketplace']
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $webhook_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $http_code >= 200 && $http_code < 300;
    }
    
    /**
     * Aktif alert kurallarını getir
     */
    private function getActiveAlertRules() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_alert_rules` WHERE is_active = 1");
        return $query->rows;
    }
    
    /**
     * Sağlık alert'ini kontrol et
     */
    private function checkHealthAlert($rule, $system_health) {
        return $system_health['status'] === 'unhealthy' && $rule['threshold_operator'] === 'equal' && $rule['threshold_value'] == 1;
    }
    
    /**
     * Değer karşılaştırması yap
     */
    private function compareValue($current, $threshold, $operator) {
        switch ($operator) {
            case 'greater':
                return $current > $threshold;
            case 'less':
                return $current < $threshold;
            case 'equal':
                return $current == $threshold;
            case 'not_equal':
                return $current != $threshold;
            default:
                return false;
        }
    }
    
    /**
     * Ortalama yanıt süresini hesapla
     */
    private function getAverageResponseTime($services) {
        $total = 0;
        $count = 0;
        
        foreach ($services as $service) {
            if (isset($service['response_time'])) {
                $time = (float) str_replace('ms', '', $service['response_time']);
                $total += $time;
                $count++;
            }
        }
        
        return $count > 0 ? round($total / $count, 2) : 0;
    }
    
    /**
     * Son sipariş sayısını getir
     */
    private function getRecentOrderCount($marketplace = null) {
        $sql = "SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_orders` WHERE date_added > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        if ($marketplace) {
            $sql .= " AND marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        $result = $this->db->query($sql);
        return $result->row['count'];
    }
    
    /**
     * Cooldown kontrolü
     */
    private function isInCooldown($rule_id) {
        $result = $this->db->query("SELECT last_triggered FROM `" . DB_PREFIX . "meschain_alert_rules` WHERE id = " . (int)$rule_id);
        
        if ($result->num_rows == 0) return false;
        
        $last_triggered = $result->row['last_triggered'];
        if (!$last_triggered) return false;
        
        // Son 5 dakika içinde tetiklendiyse cooldown'da
        $cooldown_time = strtotime($last_triggered) + (5 * 60);
        return time() < $cooldown_time;
    }
    
    /**
     * Severity belirle
     */
    private function determineSeverity($rule, $current_value) {
        $threshold = $rule['threshold_value'];
        
        switch ($rule['rule_type']) {
            case self::TYPE_ERROR_RATE:
                if ($current_value > $threshold * 3) return self::SEVERITY_CRITICAL;
                if ($current_value > $threshold * 2) return self::SEVERITY_HIGH;
                if ($current_value > $threshold) return self::SEVERITY_MEDIUM;
                return self::SEVERITY_LOW;
                
            case self::TYPE_RESPONSE_TIME:
                if ($current_value > $threshold * 2) return self::SEVERITY_HIGH;
                if ($current_value > $threshold) return self::SEVERITY_MEDIUM;
                return self::SEVERITY_LOW;
                
            case self::TYPE_HEALTH_CHECK:
                return self::SEVERITY_CRITICAL;
                
            default:
                return self::SEVERITY_MEDIUM;
        }
    }
    
    /**
     * Alert geçmişine kaydet
     */
    private function saveAlertHistory($rule, $message, $severity, $current_value) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_alert_history` SET 
            alert_rule_id = " . (int)$rule['id'] . ",
            alert_type = '" . $this->db->escape($rule['rule_type']) . "',
            message = '" . $this->db->escape($message) . "',
            severity = '" . $this->db->escape($severity) . "',
            triggered_value = " . (float)$current_value . ",
            threshold_value = " . (float)$rule['threshold_value'] . ",
            date_added = NOW()");
            
        return $this->db->getLastId();
    }
    
    /**
     * Alert kuralını güncelle
     */
    private function updateAlertRule($rule_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_alert_rules` SET 
            last_triggered = NOW(),
            trigger_count = trigger_count + 1
            WHERE id = " . (int)$rule_id);
    }
    
    /**
     * Gönderilen kanalları güncelle
     */
    private function updateSentChannels($alert_id, $channels) {
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_alert_history` SET 
            channels_sent = '" . $this->db->escape(json_encode($channels)) . "'
            WHERE id = " . (int)$alert_id);
    }
}

?> 