<?php
/**
 * MesChain Push Notification Service
 * FCM and APNS integration for mobile push notifications
 * 
 * @category   MesChain
 * @package    Mobile Push Notifications
 * @author     MesChain Development Team
 * @copyright  2025 MesChain
 * @license    https://meschain.com/license
 * @version    1.0.0
 */

class MesChainPushNotificationService {
    
    private $registry;
    private $config;
    private $cache;
    private $log;
    private $fcm_server_key;
    private $apns_certificate;
    private $notification_templates = [];
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $registry->get('config');
        $this->cache = $registry->get('cache');
        $this->log = new Log('meschain_push_notifications.log');
        
        // Load configuration
        $this->fcm_server_key = $this->config->get('meschain_fcm_server_key');
        $this->apns_certificate = $this->config->get('meschain_apns_certificate_path');
        
        $this->initializeTemplates();
    }
    
    /**
     * Send push notification
     */
    public function sendNotification($user_id, $title, $message, $data = [], $template = null) {
        try {
            // Get user devices
            $devices = $this->getUserDevices($user_id);
            
            if (empty($devices)) {
                $this->log->write("No devices found for user: {$user_id}");
                return ['success' => true, 'sent' => 0, 'message' => 'No devices registered'];
            }
            
            // Apply template if specified
            if ($template && isset($this->notification_templates[$template])) {
                $template_data = $this->notification_templates[$template];
                $title = $this->processTemplate($template_data['title'], $data);
                $message = $this->processTemplate($template_data['message'], $data);
                $data = array_merge($template_data['data'] ?? [], $data);
            }
            
            $results = [];
            $total_sent = 0;
            
            foreach ($devices as $device) {
                $result = $this->sendToDevice($device, $title, $message, $data);
                $results[] = $result;
                
                if ($result['success']) {
                    $total_sent++;
                }
            }
            
            // Log notification
            $this->logNotification($user_id, $title, $message, $total_sent, count($devices));
            
            return [
                'success' => true,
                'sent' => $total_sent,
                'total_devices' => count($devices),
                'results' => $results
            ];
            
        } catch (Exception $e) {
            $this->log->write('Push notification error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send notification to specific device
     */
    public function sendToDevice($device, $title, $message, $data = []) {
        switch ($device['platform']) {
            case 'android':
                return $this->sendFCMNotification($device['device_token'], $title, $message, $data);
            
            case 'ios':
                return $this->sendAPNSNotification($device['device_token'], $title, $message, $data);
            
            default:
                return [
                    'success' => false,
                    'error' => 'Unsupported platform: ' . $device['platform']
                ];
        }
    }
    
    /**
     * Send FCM notification (Android)
     */
    private function sendFCMNotification($device_token, $title, $message, $data = []) {
        if (empty($this->fcm_server_key)) {
            return [
                'success' => false,
                'error' => 'FCM server key not configured'
            ];
        }
        
        $fcm_url = 'https://fcm.googleapis.com/fcm/send';
        
        $notification_data = [
            'to' => $device_token,
            'notification' => [
                'title' => $title,
                'body' => $message,
                'icon' => 'ic_notification',
                'sound' => 'default',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ],
            'data' => array_merge([
                'type' => 'general',
                'timestamp' => time()
            ], $data),
            'priority' => 'high',
            'time_to_live' => 86400 // 24 hours
        ];
        
        $headers = [
            'Authorization: key=' . $this->fcm_server_key,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcm_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification_data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            return [
                'success' => false,
                'error' => 'FCM request failed'
            ];
        }
        
        $response_data = json_decode($response, true);
        
        if ($http_code === 200 && isset($response_data['success']) && $response_data['success'] > 0) {
            return [
                'success' => true,
                'platform' => 'android',
                'message_id' => $response_data['results'][0]['message_id'] ?? null
            ];
        } else {
            return [
                'success' => false,
                'error' => $response_data['results'][0]['error'] ?? 'FCM send failed',
                'response' => $response_data
            ];
        }
    }
    
    /**
     * Send APNS notification (iOS)
     */
    private function sendAPNSNotification($device_token, $title, $message, $data = []) {
        if (empty($this->apns_certificate) || !file_exists($this->apns_certificate)) {
            return [
                'success' => false,
                'error' => 'APNS certificate not found'
            ];
        }
        
        // APNS HTTP/2 endpoint
        $apns_url = 'https://api.push.apple.com/3/device/' . $device_token;
        
        $payload = [
            'aps' => [
                'alert' => [
                    'title' => $title,
                    'body' => $message
                ],
                'sound' => 'default',
                'badge' => 1,
                'content-available' => 1
            ]
        ];
        
        // Add custom data
        foreach ($data as $key => $value) {
            $payload[$key] = $value;
        }
        
        $headers = [
            'Content-Type: application/json',
            'apns-topic: com.meschain.app', // Your app bundle ID
            'apns-priority: 10',
            'apns-expiration: ' . (time() + 86400) // 24 hours
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apns_url);
        curl_setopt($ch, CURLOPT_PORT, 443);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->apns_certificate);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            return [
                'success' => false,
                'error' => 'APNS request failed'
            ];
        }
        
        if ($http_code === 200) {
            return [
                'success' => true,
                'platform' => 'ios'
            ];
        } else {
            $error_data = json_decode($response, true);
            return [
                'success' => false,
                'error' => $error_data['reason'] ?? 'APNS send failed',
                'response' => $error_data
            ];
        }
    }
    
    /**
     * Send bulk notifications
     */
    public function sendBulkNotification($user_ids, $title, $message, $data = [], $template = null) {
        $results = [];
        $total_sent = 0;
        $total_devices = 0;
        
        foreach ($user_ids as $user_id) {
            $result = $this->sendNotification($user_id, $title, $message, $data, $template);
            $results[$user_id] = $result;
            
            if ($result['success']) {
                $total_sent += $result['sent'];
                $total_devices += $result['total_devices'] ?? 0;
            }
        }
        
        return [
            'success' => true,
            'total_users' => count($user_ids),
            'total_sent' => $total_sent,
            'total_devices' => $total_devices,
            'results' => $results
        ];
    }
    
    /**
     * Send notification by template
     */
    public function sendTemplateNotification($user_id, $template_name, $variables = []) {
        if (!isset($this->notification_templates[$template_name])) {
            return [
                'success' => false,
                'error' => 'Template not found: ' . $template_name
            ];
        }
        
        return $this->sendNotification($user_id, '', '', $variables, $template_name);
    }
    
    /**
     * Register device
     */
    public function registerDevice($user_id, $device_token, $platform, $app_version = null, $device_info = []) {
        try {
            $db = $this->registry->get('db');
            
            // Check if device already exists
            $existing = $db->query("
                SELECT device_id FROM " . DB_PREFIX . "meschain_user_devices 
                WHERE user_id = '" . (int)$user_id . "' 
                AND device_token = '" . $db->escape($device_token) . "'
            ");
            
            if ($existing->num_rows > 0) {
                // Update existing device
                $db->query("
                    UPDATE " . DB_PREFIX . "meschain_user_devices 
                    SET 
                        platform = '" . $db->escape($platform) . "',
                        app_version = '" . $db->escape($app_version) . "',
                        device_info = '" . $db->escape(json_encode($device_info)) . "',
                        status = 1,
                        updated_at = NOW()
                    WHERE device_id = '" . (int)$existing->row['device_id'] . "'
                ");
                
                return [
                    'success' => true,
                    'device_id' => $existing->row['device_id'],
                    'action' => 'updated'
                ];
            } else {
                // Insert new device
                $db->query("
                    INSERT INTO " . DB_PREFIX . "meschain_user_devices 
                    (user_id, device_token, platform, app_version, device_info, status, created_at) 
                    VALUES (
                        '" . (int)$user_id . "',
                        '" . $db->escape($device_token) . "',
                        '" . $db->escape($platform) . "',
                        '" . $db->escape($app_version) . "',
                        '" . $db->escape(json_encode($device_info)) . "',
                        1,
                        NOW()
                    )
                ");
                
                return [
                    'success' => true,
                    'device_id' => $db->getLastId(),
                    'action' => 'created'
                ];
            }
            
        } catch (Exception $e) {
            $this->log->write('Device registration error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Unregister device
     */
    public function unregisterDevice($user_id, $device_token) {
        try {
            $db = $this->registry->get('db');
            
            $db->query("
                UPDATE " . DB_PREFIX . "meschain_user_devices 
                SET status = 0, updated_at = NOW()
                WHERE user_id = '" . (int)$user_id . "' 
                AND device_token = '" . $db->escape($device_token) . "'
            ");
            
            return [
                'success' => true,
                'message' => 'Device unregistered successfully'
            ];
            
        } catch (Exception $e) {
            $this->log->write('Device unregistration error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get user devices
     */
    private function getUserDevices($user_id) {
        $db = $this->registry->get('db');
        
        $query = $db->query("
            SELECT device_id, device_token, platform, app_version, device_info
            FROM " . DB_PREFIX . "meschain_user_devices 
            WHERE user_id = '" . (int)$user_id . "' 
            AND status = 1
        ");
        
        return $query->rows;
    }
    
    /**
     * Initialize notification templates
     */
    private function initializeTemplates() {
        $this->notification_templates = [
            'order_received' => [
                'title' => 'New Order Received',
                'message' => 'Order #{order_id} received from {marketplace}',
                'data' => [
                    'type' => 'order',
                    'action' => 'view_order'
                ]
            ],
            
            'order_shipped' => [
                'title' => 'Order Shipped',
                'message' => 'Order #{order_id} has been shipped',
                'data' => [
                    'type' => 'order',
                    'action' => 'track_order'
                ]
            ],
            
            'sync_completed' => [
                'title' => 'Sync Completed',
                'message' => '{marketplace} sync completed successfully',
                'data' => [
                    'type' => 'sync',
                    'action' => 'view_sync_report'
                ]
            ],
            
            'sync_failed' => [
                'title' => 'Sync Failed',
                'message' => '{marketplace} sync failed. Please check the logs.',
                'data' => [
                    'type' => 'error',
                    'action' => 'view_errors'
                ]
            ],
            
            'low_inventory' => [
                'title' => 'Low Inventory Alert',
                'message' => 'Product {product_name} is running low ({quantity} remaining)',
                'data' => [
                    'type' => 'inventory',
                    'action' => 'view_product'
                ]
            ],
            
            'price_change' => [
                'title' => 'Price Change Detected',
                'message' => 'Price changed for {product_name} on {marketplace}',
                'data' => [
                    'type' => 'price',
                    'action' => 'view_product'
                ]
            ],
            
            'system_alert' => [
                'title' => 'System Alert',
                'message' => '{message}',
                'data' => [
                    'type' => 'system',
                    'action' => 'view_dashboard'
                ]
            ]
        ];
    }
    
    /**
     * Process notification template
     */
    private function processTemplate($template, $variables) {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }
    
    /**
     * Log notification
     */
    private function logNotification($user_id, $title, $message, $sent_count, $total_devices) {
        $db = $this->registry->get('db');
        
        $db->query("
            INSERT INTO " . DB_PREFIX . "meschain_push_notifications_log 
            (user_id, title, message, sent_count, total_devices, created_at) 
            VALUES (
                '" . (int)$user_id . "',
                '" . $db->escape($title) . "',
                '" . $db->escape($message) . "',
                '" . (int)$sent_count . "',
                '" . (int)$total_devices . "',
                NOW()
            )
        ");
    }
    
    /**
     * Get notification statistics
     */
    public function getStatistics() {
        $db = $this->registry->get('db');
        
        $stats = [];
        
        // Total notifications sent
        $stats['total_sent'] = $db->query("
            SELECT COALESCE(SUM(sent_count), 0) as total
            FROM " . DB_PREFIX . "meschain_push_notifications_log
        ")->row['total'];
        
        // Today's notifications
        $stats['today_sent'] = $db->query("
            SELECT COALESCE(SUM(sent_count), 0) as total
            FROM " . DB_PREFIX . "meschain_push_notifications_log 
            WHERE DATE(created_at) = CURDATE()
        ")->row['total'];
        
        // Active devices
        $stats['active_devices'] = $db->query("
            SELECT COUNT(*) as count
            FROM " . DB_PREFIX . "meschain_user_devices 
            WHERE status = 1
        ")->row['count'];
        
        // Platform breakdown
        $platform_stats = $db->query("
            SELECT platform, COUNT(*) as count
            FROM " . DB_PREFIX . "meschain_user_devices 
            WHERE status = 1 
            GROUP BY platform
        ")->rows;
        
        $stats['platforms'] = [];
        foreach ($platform_stats as $platform) {
            $stats['platforms'][$platform['platform']] = $platform['count'];
        }
        
        return $stats;
    }
    
    /**
     * Test notification
     */
    public function testNotification($user_id) {
        return $this->sendNotification(
            $user_id,
            'Test Notification',
            'This is a test notification from MesChain-Sync',
            ['test' => true, 'timestamp' => time()]
        );
    }
} 