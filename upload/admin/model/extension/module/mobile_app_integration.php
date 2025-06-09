<?php
/**
 * Mobile App Integration Model
 * MesChain-Sync v4.0 - Mobile App Integration Model
 * iOS, Android, React Native, Flutter, PWA Support
 * 
 * @author MesChain Development Team
 * @version 4.0.0
 * @copyright 2024 MesChain Technologies
 */

class ModelExtensionModuleMobileAppIntegration extends Model {

    /**
     * Install mobile app integration tables
     */
    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mobile_apps` (
                `app_id` int(11) NOT NULL AUTO_INCREMENT,
                `app_name` varchar(255) NOT NULL,
                `app_type` enum('ios','android','react_native','flutter','pwa','xamarin','ionic') NOT NULL,
                `app_version` varchar(50) DEFAULT NULL,
                `bundle_id` varchar(255) DEFAULT NULL,
                `package_name` varchar(255) DEFAULT NULL,
                `app_store_id` varchar(255) DEFAULT NULL,
                `play_store_id` varchar(255) DEFAULT NULL,
                `api_key` varchar(255) NOT NULL,
                `api_secret` varchar(255) NOT NULL,
                `webhook_url` varchar(500) DEFAULT NULL,
                `push_notification_key` text,
                `fcm_server_key` text,
                `apns_certificate` text,
                `status` enum('active','inactive','testing','maintenance') DEFAULT 'inactive',
                `permissions` json DEFAULT NULL,
                `configuration` json DEFAULT NULL,
                `last_active` datetime DEFAULT NULL,
                `total_users` int(11) DEFAULT 0,
                `active_users` int(11) DEFAULT 0,
                `total_downloads` int(11) DEFAULT 0,
                `rating` decimal(3,2) DEFAULT NULL,
                `reviews_count` int(11) DEFAULT 0,
                `crash_reports` int(11) DEFAULT 0,
                `performance_score` decimal(5,2) DEFAULT NULL,
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`app_id`),
                UNIQUE KEY `unique_api_key` (`api_key`),
                KEY `idx_app_type` (`app_type`),
                KEY `idx_status` (`status`),
                KEY `idx_last_active` (`last_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mobile_users` (
                `user_id` int(11) NOT NULL AUTO_INCREMENT,
                `app_id` int(11) NOT NULL,
                `device_id` varchar(255) NOT NULL,
                `user_uuid` varchar(255) DEFAULT NULL,
                `customer_id` int(11) DEFAULT NULL,
                `device_type` varchar(100) DEFAULT NULL,
                `device_model` varchar(255) DEFAULT NULL,
                `os_version` varchar(100) DEFAULT NULL,
                `app_version` varchar(50) DEFAULT NULL,
                `push_token` varchar(500) DEFAULT NULL,
                `push_enabled` tinyint(1) DEFAULT 1,
                `location_enabled` tinyint(1) DEFAULT 0,
                `latitude` decimal(10,8) DEFAULT NULL,
                `longitude` decimal(11,8) DEFAULT NULL,
                `language` varchar(10) DEFAULT 'tr',
                `timezone` varchar(100) DEFAULT 'Europe/Istanbul',
                `first_login` datetime DEFAULT NULL,
                `last_login` datetime DEFAULT NULL,
                `login_count` int(11) DEFAULT 0,
                `session_duration` int(11) DEFAULT 0,
                `total_orders` int(11) DEFAULT 0,
                `total_spent` decimal(15,4) DEFAULT 0,
                `loyalty_points` int(11) DEFAULT 0,
                `preferences` json DEFAULT NULL,
                `behavior_data` json DEFAULT NULL,
                `status` enum('active','inactive','blocked','deleted') DEFAULT 'active',
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`user_id`),
                UNIQUE KEY `unique_device_app` (`app_id`, `device_id`),
                KEY `idx_app_id` (`app_id`),
                KEY `idx_customer_id` (`customer_id`),
                KEY `idx_device_type` (`device_type`),
                KEY `idx_last_login` (`last_login`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mobile_push_notifications` (
                `notification_id` int(11) NOT NULL AUTO_INCREMENT,
                `app_id` int(11) NOT NULL,
                `campaign_name` varchar(255) DEFAULT NULL,
                `title` varchar(255) NOT NULL,
                `message` text NOT NULL,
                `image_url` varchar(500) DEFAULT NULL,
                `action_url` varchar(500) DEFAULT NULL,
                `action_type` enum('open_app','open_url','open_product','open_category','custom') DEFAULT 'open_app',
                `target_audience` enum('all','segment','individual','location','behavior') DEFAULT 'all',
                `target_criteria` json DEFAULT NULL,
                `send_date` datetime DEFAULT NULL,
                `scheduled` tinyint(1) DEFAULT 0,
                `sent` tinyint(1) DEFAULT 0,
                `status` enum('draft','scheduled','sending','sent','cancelled','failed') DEFAULT 'draft',
                `total_recipients` int(11) DEFAULT 0,
                `sent_count` int(11) DEFAULT 0,
                `delivered_count` int(11) DEFAULT 0,
                `opened_count` int(11) DEFAULT 0,
                `clicked_count` int(11) DEFAULT 0,
                `conversion_count` int(11) DEFAULT 0,
                `open_rate` decimal(5,4) DEFAULT NULL,
                `click_rate` decimal(5,4) DEFAULT NULL,
                `conversion_rate` decimal(5,4) DEFAULT NULL,
                `error_count` int(11) DEFAULT 0,
                `error_details` text,
                `a_b_test` tinyint(1) DEFAULT 0,
                `test_group` varchar(100) DEFAULT NULL,
                `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
                `created_by` int(11) DEFAULT NULL,
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`notification_id`),
                KEY `idx_app_id` (`app_id`),
                KEY `idx_status` (`status`),
                KEY `idx_send_date` (`send_date`),
                KEY `idx_target_audience` (`target_audience`),
                KEY `idx_created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mobile_analytics` (
                `analytics_id` int(11) NOT NULL AUTO_INCREMENT,
                `app_id` int(11) NOT NULL,
                `date_recorded` date NOT NULL,
                `hour_recorded` int(11) DEFAULT NULL,
                `metric_type` varchar(100) NOT NULL,
                `metric_name` varchar(255) NOT NULL,
                `metric_value` decimal(15,4) DEFAULT NULL,
                `metric_count` int(11) DEFAULT NULL,
                `device_type` varchar(100) DEFAULT NULL,
                `os_version` varchar(100) DEFAULT NULL,
                `app_version` varchar(50) DEFAULT NULL,
                `country` varchar(100) DEFAULT NULL,
                `city` varchar(100) DEFAULT NULL,
                `user_segment` varchar(100) DEFAULT NULL,
                `session_data` json DEFAULT NULL,
                `event_data` json DEFAULT NULL,
                `conversion_data` json DEFAULT NULL,
                `performance_data` json DEFAULT NULL,
                `crash_data` json DEFAULT NULL,
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`analytics_id`),
                KEY `idx_app_id` (`app_id`),
                KEY `idx_date_recorded` (`date_recorded`),
                KEY `idx_metric_type` (`metric_type`),
                KEY `idx_device_type` (`device_type`),
                KEY `idx_country` (`country`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * Get all mobile apps
     */
    public function getApps($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mobile_apps`";
        
        $conditions = array();
        
        if (!empty($data['filter_type'])) {
            $conditions[] = "app_type = '" . $this->db->escape($data['filter_type']) . "'";
        }
        
        if (!empty($data['filter_status'])) {
            $conditions[] = "status = '" . $this->db->escape($data['filter_status']) . "'";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY date_added DESC";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
     * Get mobile app by ID
     */
    public function getApp($app_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "mobile_apps` 
            WHERE app_id = '" . (int)$app_id . "'
        ");
        
        return $query->row;
    }

    /**
     * Get mobile app by API key
     */
    public function getAppByApiKey($api_key) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "mobile_apps` 
            WHERE api_key = '" . $this->db->escape($api_key) . "'
        ");
        
        return $query->row;
    }

    /**
     * Save mobile app
     */
    public function saveApp($app_id, $data) {
        if ($app_id) {
            $sql = "UPDATE `" . DB_PREFIX . "mobile_apps` SET ";
            $update_data = array();
            
            foreach ($data as $key => $value) {
                if ($key !== 'date_added' && $key !== 'app_id') {
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    }
                    $update_data[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
                }
            }
            
            $sql .= implode(", ", $update_data);
            $sql .= " WHERE app_id = '" . (int)$app_id . "'";
            
            $this->db->query($sql);
        } else {
            $data['date_added'] = date('Y-m-d H:i:s');
            $data['api_key'] = $this->generateApiKey();
            $data['api_secret'] = $this->generateApiSecret();
            
            $sql = "INSERT INTO `" . DB_PREFIX . "mobile_apps` SET ";
            $insert_data = array();
            
            foreach ($data as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                $insert_data[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
            }
            
            $sql .= implode(", ", $insert_data);
            
            $this->db->query($sql);
            
            return $this->db->getLastId();
        }
    }

    /**
     * Generate API key
     */
    private function generateApiKey() {
        return 'meschain_' . bin2hex(random_bytes(16));
    }

    /**
     * Generate API secret
     */
    private function generateApiSecret() {
        return bin2hex(random_bytes(32));
    }

    /**
     * Get mobile users
     */
    public function getUsers($app_id = null, $data = array()) {
        $sql = "SELECT mu.*, ma.app_name, ma.app_type 
                FROM `" . DB_PREFIX . "mobile_users` mu
                LEFT JOIN `" . DB_PREFIX . "mobile_apps` ma ON (mu.app_id = ma.app_id)";
        
        $conditions = array();
        
        if ($app_id) {
            $conditions[] = "mu.app_id = '" . (int)$app_id . "'";
        }
        
        if (!empty($data['filter_status'])) {
            $conditions[] = "mu.status = '" . $this->db->escape($data['filter_status']) . "'";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY mu.last_login DESC";

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
     * Get push notifications
     */
    public function getNotifications($app_id = null, $data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mobile_push_notifications`";
        
        $conditions = array();
        
        if ($app_id) {
            $conditions[] = "app_id = '" . (int)$app_id . "'";
        }
        
        if (!empty($data['filter_status'])) {
            $conditions[] = "status = '" . $this->db->escape($data['filter_status']) . "'";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY date_added DESC";

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
     * Send push notification
     */
    public function sendNotification($notification_id) {
        $notification = $this->getNotification($notification_id);
        
        if (!$notification) {
            return array('success' => false, 'message' => 'Notification not found');
        }

        // Get target users based on criteria
        $users = $this->getTargetUsers($notification);
        
        if (empty($users)) {
            return array('success' => false, 'message' => 'No target users found');
        }

        // Update notification status
        $this->db->query("
            UPDATE `" . DB_PREFIX . "mobile_push_notifications` 
            SET status = 'sending', total_recipients = '" . count($users) . "' 
            WHERE notification_id = '" . (int)$notification_id . "'
        ");

        $sent_count = 0;
        $error_count = 0;
        $errors = array();

        foreach ($users as $user) {
            try {
                $result = $this->sendPushToDevice($user, $notification);
                if ($result['success']) {
                    $sent_count++;
                } else {
                    $error_count++;
                    $errors[] = $result['error'];
                }
            } catch (Exception $e) {
                $error_count++;
                $errors[] = $e->getMessage();
            }
        }

        // Update final status
        $status = ($sent_count > 0) ? 'sent' : 'failed';
        $this->db->query("
            UPDATE `" . DB_PREFIX . "mobile_push_notifications` 
            SET status = '" . $status . "', 
                sent_count = '" . $sent_count . "',
                error_count = '" . $error_count . "',
                error_details = '" . $this->db->escape(json_encode($errors)) . "'
            WHERE notification_id = '" . (int)$notification_id . "'
        ");

        return array(
            'success' => true,
            'sent_count' => $sent_count,
            'error_count' => $error_count,
            'errors' => $errors
        );
    }

    /**
     * Get notification by ID
     */
    public function getNotification($notification_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "mobile_push_notifications` 
            WHERE notification_id = '" . (int)$notification_id . "'
        ");
        
        return $query->row;
    }

    /**
     * Get target users for notification
     */
    private function getTargetUsers($notification) {
        $sql = "SELECT mu.*, ma.app_type 
                FROM `" . DB_PREFIX . "mobile_users` mu
                LEFT JOIN `" . DB_PREFIX . "mobile_apps` ma ON (mu.app_id = ma.app_id)
                WHERE mu.app_id = '" . (int)$notification['app_id'] . "' 
                AND mu.push_enabled = 1 
                AND mu.push_token IS NOT NULL 
                AND mu.status = 'active'";

        // Apply targeting criteria
        if ($notification['target_audience'] !== 'all') {
            $criteria = json_decode($notification['target_criteria'], true);
            
            switch ($notification['target_audience']) {
                case 'segment':
                    if (!empty($criteria['segment'])) {
                        $sql .= " AND JSON_EXTRACT(mu.behavior_data, '$.segment') = '" . $this->db->escape($criteria['segment']) . "'";
                    }
                    break;
                case 'location':
                    if (!empty($criteria['country'])) {
                        $sql .= " AND JSON_EXTRACT(mu.preferences, '$.country') = '" . $this->db->escape($criteria['country']) . "'";
                    }
                    break;
                case 'behavior':
                    if (!empty($criteria['min_orders'])) {
                        $sql .= " AND mu.total_orders >= '" . (int)$criteria['min_orders'] . "'";
                    }
                    break;
            }
        }

        $query = $this->db->query($sql);
        
        return $query->rows;
    }

    /**
     * Send push notification to device
     */
    private function sendPushToDevice($user, $notification) {
        // Implementation would depend on the push service (FCM, APNS, etc.)
        // This is a simplified version
        
        try {
            $app = $this->getApp($user['app_id']);
            
            $payload = array(
                'to' => $user['push_token'],
                'notification' => array(
                    'title' => $notification['title'],
                    'body' => $notification['message'],
                    'image' => $notification['image_url']
                ),
                'data' => array(
                    'action_type' => $notification['action_type'],
                    'action_url' => $notification['action_url']
                )
            );

            // Send via FCM (Firebase Cloud Messaging)
            if ($user['app_type'] === 'android' || $user['app_type'] === 'react_native') {
                return $this->sendFCM($app['fcm_server_key'], $payload);
            }
            
            // Send via APNS (Apple Push Notification Service)
            if ($user['app_type'] === 'ios') {
                return $this->sendAPNS($app['apns_certificate'], $payload);
            }

            return array('success' => false, 'error' => 'Unsupported platform');
            
        } catch (Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }
    }

    /**
     * Send FCM notification
     */
    private function sendFCM($server_key, $payload) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        
        $headers = array(
            'Authorization: key=' . $server_key,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            return array('success' => true);
        } else {
            return array('success' => false, 'error' => 'FCM error: ' . $response);
        }
    }

    /**
     * Send APNS notification
     */
    private function sendAPNS($certificate, $payload) {
        // APNS implementation would go here
        // This is a placeholder
        return array('success' => true);
    }

    /**
     * Get analytics data
     */
    public function getAnalytics($app_id = null, $date_range = 'last_30_days') {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mobile_analytics`";
        
        $conditions = array();
        
        if ($app_id) {
            $conditions[] = "app_id = '" . (int)$app_id . "'";
        }
        
        // Date range filtering
        switch ($date_range) {
            case 'today':
                $conditions[] = "date_recorded = CURDATE()";
                break;
            case 'yesterday':
                $conditions[] = "date_recorded = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                break;
            case 'last_7_days':
                $conditions[] = "date_recorded >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                break;
            case 'last_30_days':
                $conditions[] = "date_recorded >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                break;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY date_recorded DESC";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
     * Save analytics data
     */
    public function saveAnalytics($data) {
        $data['date_added'] = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO `" . DB_PREFIX . "mobile_analytics` SET ";
        $insert_data = array();
        
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            $insert_data[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
        }
        
        $sql .= implode(", ", $insert_data);
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats($app_id = null) {
        $stats = array();
        
        $app_filter = $app_id ? " WHERE app_id = '" . (int)$app_id . "'" : "";
        
        // App statistics
        if (!$app_id) {
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "mobile_apps`");
            $stats['total_apps'] = $query->row['total'];
            
            $query = $this->db->query("SELECT COUNT(*) as active FROM `" . DB_PREFIX . "mobile_apps` WHERE status = 'active'");
            $stats['active_apps'] = $query->row['active'];
        }
        
        // User statistics
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "mobile_users`" . $app_filter);
        $stats['total_users'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as active FROM `" . DB_PREFIX . "mobile_users` WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)" . ($app_id ? " AND app_id = '" . (int)$app_id . "'" : ""));
        $stats['monthly_active_users'] = $query->row['active'];
        
        // Notification statistics
        $query = $this->db->query("SELECT COUNT(*) as sent FROM `" . DB_PREFIX . "mobile_push_notifications` WHERE status = 'sent' AND date_added >= DATE_SUB(NOW(), INTERVAL 30 DAY)" . ($app_id ? " AND app_id = '" . (int)$app_id . "'" : ""));
        $stats['monthly_notifications'] = $query->row['sent'];
        
        $query = $this->db->query("SELECT AVG(open_rate) as avg_open_rate FROM `" . DB_PREFIX . "mobile_push_notifications` WHERE status = 'sent' AND open_rate > 0" . ($app_id ? " AND app_id = '" . (int)$app_id . "'" : ""));
        $stats['avg_open_rate'] = round($query->row['avg_open_rate'] ?? 0, 2);
        
        return $stats;
    }
} 