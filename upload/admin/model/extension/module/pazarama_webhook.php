<?php
/**
 * Pazarama Webhook Model
 * MesChain-Sync v3.0 - Pazarama Marketplace Integration
 * 
 * Database operations for webhook management
 * Features: Webhook CRUD, Event logging, Notification system
 */

class ModelExtensionModulePazaramaWebhook extends Model {
    
    /**
     * Install webhook tables
     */
    public function install() {
        // Webhooks table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pazarama_webhooks` (
                `webhook_id` int(11) NOT NULL AUTO_INCREMENT,
                `event_type` varchar(100) NOT NULL,
                `url` varchar(500) NOT NULL,
                `secret` varchar(255) DEFAULT NULL,
                `status` tinyint(1) NOT NULL DEFAULT '1',
                `last_triggered` datetime DEFAULT NULL,
                `success_count` int(11) NOT NULL DEFAULT '0',
                `error_count` int(11) NOT NULL DEFAULT '0',
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`webhook_id`),
                KEY `event_type` (`event_type`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Webhook events log table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pazarama_webhook_events` (
                `event_id` int(11) NOT NULL AUTO_INCREMENT,
                `webhook_id` int(11) DEFAULT NULL,
                `event_type` varchar(100) NOT NULL,
                `payload` text NOT NULL,
                `response_code` int(11) DEFAULT NULL,
                `response_body` text DEFAULT NULL,
                `execution_time` decimal(10,4) DEFAULT NULL,
                `status` enum('success','error','pending') NOT NULL DEFAULT 'pending',
                `error_message` text DEFAULT NULL,
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`event_id`),
                KEY `webhook_id` (`webhook_id`),
                KEY `event_type` (`event_type`),
                KEY `status` (`status`),
                KEY `date_added` (`date_added`),
                FOREIGN KEY (`webhook_id`) REFERENCES `" . DB_PREFIX . "pazarama_webhooks` (`webhook_id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Webhook notifications table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pazarama_webhook_notifications` (
                `notification_id` int(11) NOT NULL AUTO_INCREMENT,
                `type` varchar(50) NOT NULL,
                `message` text NOT NULL,
                `status` enum('info','success','warning','error') NOT NULL DEFAULT 'info',
                `read_status` tinyint(1) NOT NULL DEFAULT '0',
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`notification_id`),
                KEY `type` (`type`),
                KEY `status` (`status`),
                KEY `read_status` (`read_status`),
                KEY `date_added` (`date_added`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    /**
     * Get webhooks with filtering and pagination
     */
    public function getWebhooks($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "pazarama_webhooks` WHERE 1=1";
        
        if (!empty($data['filter_event_type'])) {
            $sql .= " AND `event_type` = '" . $this->db->escape($data['filter_event_type']) . "'";
        }
        
        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $sql .= " AND `status` = '" . (int)$data['filter_status'] . "'";
        }
        
        $sort_data = array(
            'event_type',
            'url',
            'status',
            'success_count',
            'error_count',
            'date_added'
        );
        
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
     * Get single webhook by ID
     */
    public function getWebhook($webhook_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "pazarama_webhooks` WHERE `webhook_id` = '" . (int)$webhook_id . "'");
        
        return $query->row;
    }
    
    /**
     * Add new webhook
     */
    public function addWebhook($data) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "pazarama_webhooks` SET
            `event_type` = '" . $this->db->escape($data['event_type']) . "',
            `url` = '" . $this->db->escape($data['url']) . "',
            `secret` = '" . $this->db->escape($data['secret'] ?? '') . "',
            `status` = '" . (int)($data['status'] ?? 1) . "'
        ");
        
        $webhook_id = $this->db->getLastId();
        
        // Log webhook creation
        $this->logWebhookEvent('WEBHOOK_CREATED', 'Webhook created: ' . $data['event_type'] . ' -> ' . $data['url'], 'success');
        
        return $webhook_id;
    }
    
    /**
     * Update webhook
     */
    public function updateWebhook($webhook_id, $data) {
        $sql = "UPDATE `" . DB_PREFIX . "pazarama_webhooks` SET ";
        $updates = array();
        
        if (isset($data['event_type'])) {
            $updates[] = "`event_type` = '" . $this->db->escape($data['event_type']) . "'";
        }
        
        if (isset($data['url'])) {
            $updates[] = "`url` = '" . $this->db->escape($data['url']) . "'";
        }
        
        if (isset($data['secret'])) {
            $updates[] = "`secret` = '" . $this->db->escape($data['secret']) . "'";
        }
        
        if (isset($data['status'])) {
            $updates[] = "`status` = '" . (int)$data['status'] . "'";
        }
        
        if (!empty($updates)) {
            $sql .= implode(', ', $updates);
            $sql .= " WHERE `webhook_id` = '" . (int)$webhook_id . "'";
            
            $this->db->query($sql);
            
            // Log webhook update
            $this->logWebhookEvent('WEBHOOK_UPDATED', 'Webhook updated: ID ' . $webhook_id, 'success');
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Update webhook status
     */
    public function updateWebhookStatus($webhook_id, $status) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "pazarama_webhooks` 
            SET `status` = '" . (int)$status . "' 
            WHERE `webhook_id` = '" . (int)$webhook_id . "'
        ");
        
        $this->logWebhookEvent('WEBHOOK_STATUS_CHANGED', 'Webhook status changed: ID ' . $webhook_id . ' -> ' . ($status ? 'enabled' : 'disabled'), 'info');
    }
    
    /**
     * Delete webhook
     */
    public function deleteWebhook($webhook_id) {
        // Delete related events first
        $this->db->query("DELETE FROM `" . DB_PREFIX . "pazarama_webhook_events` WHERE `webhook_id` = '" . (int)$webhook_id . "'");
        
        // Delete webhook
        $this->db->query("DELETE FROM `" . DB_PREFIX . "pazarama_webhooks` WHERE `webhook_id` = '" . (int)$webhook_id . "'");
        
        $this->logWebhookEvent('WEBHOOK_DELETED', 'Webhook deleted: ID ' . $webhook_id, 'warning');
    }
    
    /**
     * Get total webhooks count
     */
    public function getTotalWebhooks($data = array()) {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "pazarama_webhooks` WHERE 1=1";
        
        if (!empty($data['filter_event_type'])) {
            $sql .= " AND `event_type` = '" . $this->db->escape($data['filter_event_type']) . "'";
        }
        
        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $sql .= " AND `status` = '" . (int)$data['filter_status'] . "'";
        }
        
        $query = $this->db->query($sql);
        
        return $query->row['total'];
    }
    
    /**
     * Log webhook event
     */
    public function logWebhookEvent($event_type, $message, $status = 'info', $webhook_id = null, $payload = null, $response_code = null, $response_body = null, $execution_time = null) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "pazarama_webhook_events` SET
            `webhook_id` = " . ($webhook_id ? "'" . (int)$webhook_id . "'" : "NULL") . ",
            `event_type` = '" . $this->db->escape($event_type) . "',
            `payload` = '" . $this->db->escape($payload ?? $message) . "',
            `response_code` = " . ($response_code ? "'" . (int)$response_code . "'" : "NULL") . ",
            `response_body` = '" . $this->db->escape($response_body ?? '') . "',
            `execution_time` = " . ($execution_time ? "'" . (float)$execution_time . "'" : "NULL") . ",
            `status` = '" . $this->db->escape($status) . "',
            `error_message` = '" . $this->db->escape($status === 'error' ? $message : '') . "'
        ");
    }
    
    /**
     * Get webhook events/logs
     */
    public function getWebhookLogs($data = array()) {
        $sql = "SELECT we.*, w.event_type as webhook_event_type, w.url as webhook_url 
                FROM `" . DB_PREFIX . "pazarama_webhook_events` we 
                LEFT JOIN `" . DB_PREFIX . "pazarama_webhooks` w ON (we.webhook_id = w.webhook_id)
                WHERE 1=1";
        
        if (!empty($data['filter_event_type'])) {
            $sql .= " AND we.`event_type` = '" . $this->db->escape($data['filter_event_type']) . "'";
        }
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND we.`status` = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_webhook_id'])) {
            $sql .= " AND we.`webhook_id` = '" . (int)$data['filter_webhook_id'] . "'";
        }
        
        $sort_data = array(
            'event_type',
            'status',
            'response_code',
            'execution_time',
            'date_added'
        );
        
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY we.`" . $data['sort'] . "`";
        } else {
            $sql .= " ORDER BY we.`date_added`";
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
                $data['limit'] = 50;
            }
            
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Clear webhook logs
     */
    public function clearWebhookLogs($days = 30) {
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "pazarama_webhook_events` 
            WHERE `date_added` < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
        ");
        
        return $this->db->countAffected();
    }
    
    /**
     * Add notification
     */
    public function addNotification($data) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "pazarama_webhook_notifications` SET
            `type` = '" . $this->db->escape($data['type']) . "',
            `message` = '" . $this->db->escape($data['message']) . "',
            `status` = '" . $this->db->escape($data['status'] ?? 'info') . "'
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Get notifications
     */
    public function getNotifications($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "pazarama_webhook_notifications` WHERE 1=1";
        
        if (isset($data['filter_read_status']) && $data['filter_read_status'] !== '') {
            $sql .= " AND `read_status` = '" . (int)$data['filter_read_status'] . "'";
        }
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND `status` = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        $sql .= " ORDER BY `date_added` DESC";
        
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
     * Mark notification as read
     */
    public function markNotificationAsRead($notification_id) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "pazarama_webhook_notifications` 
            SET `read_status` = '1' 
            WHERE `notification_id` = '" . (int)$notification_id . "'
        ");
    }
    
    /**
     * Delete notification
     */
    public function deleteNotification($notification_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "pazarama_webhook_notifications` WHERE `notification_id` = '" . (int)$notification_id . "'");
    }
    
    /**
     * Get webhook configuration for event types
     */
    public function getWebhookConfiguration() {
        $query = $this->db->query("
            SELECT `event_type`, `status`, COUNT(*) as webhook_count 
            FROM `" . DB_PREFIX . "pazarama_webhooks` 
            GROUP BY `event_type`, `status`
        ");
        
        $config = array();
        foreach ($query->rows as $row) {
            $config[$row['event_type']] = array(
                'enabled' => (bool)$row['status'],
                'webhook_count' => $row['webhook_count']
            );
        }
        
        return $config;
    }
    
    /**
     * Save webhook configuration
     */
    public function saveWebhookConfiguration($events) {
        foreach ($events as $event_type => $enabled) {
            // Update all webhooks of this type
            $this->db->query("
                UPDATE `" . DB_PREFIX . "pazarama_webhooks` 
                SET `status` = '" . ($enabled ? 1 : 0) . "' 
                WHERE `event_type` = '" . $this->db->escape($event_type) . "'
            ");
        }
        
        $this->logWebhookEvent('CONFIGURATION_UPDATED', 'Webhook configuration updated', 'success');
        
        return true;
    }
    
    /**
     * Update webhook statistics after execution
     */
    public function updateWebhookStats($webhook_id, $success = true, $execution_time = null) {
        if ($success) {
            $this->db->query("
                UPDATE `" . DB_PREFIX . "pazarama_webhooks` 
                SET `success_count` = `success_count` + 1,
                    `last_triggered` = NOW()
                WHERE `webhook_id` = '" . (int)$webhook_id . "'
            ");
        } else {
            $this->db->query("
                UPDATE `" . DB_PREFIX . "pazarama_webhooks` 
                SET `error_count` = `error_count` + 1,
                    `last_triggered` = NOW()
                WHERE `webhook_id` = '" . (int)$webhook_id . "'
            ");
        }
    }
    
    /**
     * Get webhook statistics
     */
    public function getWebhookStatistics() {
        $stats = array();
        
        // Total webhooks
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "pazarama_webhooks`");
        $stats['total_webhooks'] = $query->row['total'];
        
        // Active webhooks
        $query = $this->db->query("SELECT COUNT(*) as active FROM `" . DB_PREFIX . "pazarama_webhooks` WHERE `status` = 1");
        $stats['active_webhooks'] = $query->row['active'];
        
        // Today's events
        $query = $this->db->query("SELECT COUNT(*) as today FROM `" . DB_PREFIX . "pazarama_webhook_events` WHERE DATE(`date_added`) = CURDATE()");
        $stats['events_today'] = $query->row['today'];
        
        // Success rate
        $query = $this->db->query("
            SELECT 
                SUM(CASE WHEN `status` = 'success' THEN 1 ELSE 0 END) as success_count,
                COUNT(*) as total_count
            FROM `" . DB_PREFIX . "pazarama_webhook_events` 
            WHERE `date_added` >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        
        if ($query->row['total_count'] > 0) {
            $stats['success_rate'] = round(($query->row['success_count'] / $query->row['total_count']) * 100, 2);
        } else {
            $stats['success_rate'] = 0;
        }
        
        return $stats;
    }
}
