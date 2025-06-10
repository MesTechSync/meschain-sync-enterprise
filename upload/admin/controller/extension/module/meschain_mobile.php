<?php
/**
 * MesChain Mobile Management Controller
 * 
 * @category   MesChain
 * @package    Mobile Management
 * @author     MesChain Development Team
 * @copyright  2025 MesChain
 * @license    https://meschain.com/license
 * @version    1.0.0
 */

class ControllerExtensionModuleMeschainMobile extends Controller {
    
    private $error = [];
    
    public function index() {
        $this->load->language('extension/module/meschain_mobile');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Load mobile libraries
        $this->load->library('meschain/mobile/mobile_api_gateway');
        $this->load->library('meschain/mobile/push_notification_service');
        
        $mobile_api = new MesChainMobileApiGateway($this->registry);
        $push_service = new MesChainPushNotificationService($this->registry);
        
        $data['breadcrumbs'] = [];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/meschain_mobile', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        // Get mobile API statistics
        $data['api_stats'] = $mobile_api->getStatistics();
        
        // Get push notification statistics
        $data['push_stats'] = $push_service->getStatistics();
        
        // Get registered devices
        $data['devices'] = $this->getRegisteredDevices();
        
        // Get recent API calls
        $data['recent_api_calls'] = $this->getRecentApiCalls();
        
        // Get recent notifications
        $data['recent_notifications'] = $this->getRecentNotifications();
        
        // Action URLs
        $data['action_send_test_notification'] = $this->url->link('extension/module/meschain_mobile/sendTestNotification', 'user_token=' . $this->session->data['user_token'], true);
        $data['action_send_bulk_notification'] = $this->url->link('extension/module/meschain_mobile/sendBulkNotification', 'user_token=' . $this->session->data['user_token'], true);
        $data['action_update_fcm_config'] = $this->url->link('extension/module/meschain_mobile/updateFcmConfig', 'user_token=' . $this->session->data['user_token'], true);
        $data['action_test_api_endpoint'] = $this->url->link('extension/module/meschain_mobile/testApiEndpoint', 'user_token=' . $this->session->data['user_token'], true);
        $data['action_refresh_stats'] = $this->url->link('extension/module/meschain_mobile/refreshStats', 'user_token=' . $this->session->data['user_token'], true);
        
        // Configuration
        $data['fcm_server_key'] = $this->config->get('meschain_fcm_server_key') ? '****' . substr($this->config->get('meschain_fcm_server_key'), -8) : 'Not configured';
        $data['apns_certificate'] = $this->config->get('meschain_apns_certificate_path') ? 'Configured' : 'Not configured';
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_mobile', $data));
    }
    
    /**
     * Send test notification
     */
    public function sendTestNotification() {
        $this->load->language('extension/module/meschain_mobile');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $user_id = $this->request->post['user_id'] ?? '';
            $title = $this->request->post['title'] ?? 'Test Notification';
            $message = $this->request->post['message'] ?? 'This is a test notification from MesChain Mobile API';
            
            if (empty($user_id)) {
                $json['error'] = 'User ID is required';
            } else {
                $this->load->library('meschain/mobile/push_notification_service');
                $push_service = new MesChainPushNotificationService($this->registry);
                
                $result = $push_service->sendNotification($user_id, $title, $message, ['test' => true]);
                
                if ($result['success']) {
                    $json['success'] = 'Test notification sent successfully';
                    $json['sent_count'] = $result['sent'];
                    $json['total_devices'] = $result['total_devices'] ?? 0;
                } else {
                    $json['error'] = $result['error'];
                }
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Send bulk notification
     */
    public function sendBulkNotification() {
        $this->load->language('extension/module/meschain_mobile');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $title = $this->request->post['title'] ?? '';
            $message = $this->request->post['message'] ?? '';
            $target_type = $this->request->post['target_type'] ?? 'all'; // all, active_users, specific_users
            $user_ids = $this->request->post['user_ids'] ?? [];
            
            if (empty($title) || empty($message)) {
                $json['error'] = 'Title and message are required';
            } else {
                // Get target user IDs based on type
                switch ($target_type) {
                    case 'all':
                        $user_ids = $this->getAllUserIds();
                        break;
                    case 'active_users':
                        $user_ids = $this->getActiveUserIds();
                        break;
                    case 'specific_users':
                        if (is_string($user_ids)) {
                            $user_ids = array_map('trim', explode(',', $user_ids));
                        }
                        break;
                }
                
                if (empty($user_ids)) {
                    $json['error'] = 'No target users found';
                } else {
                    $this->load->library('meschain/mobile/push_notification_service');
                    $push_service = new MesChainPushNotificationService($this->registry);
                    
                    $result = $push_service->sendBulkNotification($user_ids, $title, $message);
                    
                    if ($result['success']) {
                        $json['success'] = 'Bulk notification sent successfully';
                        $json['total_users'] = $result['total_users'];
                        $json['total_sent'] = $result['total_sent'];
                        $json['total_devices'] = $result['total_devices'];
                    } else {
                        $json['error'] = 'Failed to send bulk notification';
                    }
                }
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Update FCM configuration
     */
    public function updateFcmConfig() {
        $this->load->language('extension/module/meschain_mobile');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $fcm_server_key = $this->request->post['fcm_server_key'] ?? '';
            
            if (empty($fcm_server_key)) {
                $json['error'] = 'FCM Server Key is required';
            } else {
                // Save FCM configuration
                $this->load->model('setting/setting');
                
                $setting_data = [
                    'meschain_fcm_server_key' => $fcm_server_key
                ];
                
                $this->model_setting_setting->editSetting('meschain_mobile', $setting_data);
                
                $json['success'] = 'FCM configuration updated successfully';
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Test API endpoint
     */
    public function testApiEndpoint() {
        $this->load->language('extension/module/meschain_mobile');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $endpoint = $this->request->post['endpoint'] ?? '';
            $method = $this->request->post['method'] ?? 'GET';
            $auth_token = $this->request->post['auth_token'] ?? '';
            
            if (empty($endpoint)) {
                $json['error'] = 'Endpoint is required';
            } elseif (empty($auth_token)) {
                $json['error'] = 'Auth token is required';
            } else {
                $this->load->library('meschain/mobile/mobile_api_gateway');
                $mobile_api = new MesChainMobileApiGateway($this->registry);
                
                $headers = [
                    'Authorization' => 'Bearer ' . $auth_token,
                    'API-Version' => 'v1'
                ];
                
                $result = $mobile_api->processRequest($endpoint, $method, [], $headers);
                
                $json['success'] = 'API test completed';
                $json['result'] = $result;
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Refresh statistics
     */
    public function refreshStats() {
        $this->load->library('meschain/mobile/mobile_api_gateway');
        $this->load->library('meschain/mobile/push_notification_service');
        
        $mobile_api = new MesChainMobileApiGateway($this->registry);
        $push_service = new MesChainPushNotificationService($this->registry);
        
        $json = [
            'success' => true,
            'api_stats' => $mobile_api->getStatistics(),
            'push_stats' => $push_service->getStatistics(),
            'devices' => $this->getRegisteredDevices(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get registered devices
     */
    private function getRegisteredDevices() {
        $query = $this->db->query("
            SELECT 
                d.device_id,
                d.user_id,
                d.platform,
                d.app_version,
                d.device_info,
                d.created_at,
                d.updated_at,
                u.username
            FROM " . DB_PREFIX . "meschain_user_devices d
            LEFT JOIN " . DB_PREFIX . "user u ON d.user_id = u.user_id
            WHERE d.status = 1
            ORDER BY d.updated_at DESC
            LIMIT 50
        ");
        
        $devices = [];
        foreach ($query->rows as $row) {
            $device_info = json_decode($row['device_info'], true) ?: [];
            $devices[] = [
                'device_id' => $row['device_id'],
                'user_id' => $row['user_id'],
                'username' => $row['username'] ?? 'Unknown',
                'platform' => $row['platform'],
                'app_version' => $row['app_version'],
                'device_model' => $device_info['model'] ?? 'Unknown',
                'os_version' => $device_info['os_version'] ?? 'Unknown',
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            ];
        }
        
        return $devices;
    }
    
    /**
     * Get recent API calls
     */
    private function getRecentApiCalls() {
        $query = $this->db->query("
            SELECT 
                endpoint,
                method,
                client_id,
                success,
                response_time,
                ip_address,
                created_at
            FROM " . DB_PREFIX . "meschain_mobile_api_logs
            ORDER BY created_at DESC
            LIMIT 20
        ");
        
        return $query->rows;
    }
    
    /**
     * Get recent notifications
     */
    private function getRecentNotifications() {
        $query = $this->db->query("
            SELECT 
                n.user_id,
                n.title,
                n.message,
                n.sent_count,
                n.total_devices,
                n.created_at,
                u.username
            FROM " . DB_PREFIX . "meschain_push_notifications_log n
            LEFT JOIN " . DB_PREFIX . "user u ON n.user_id = u.user_id
            ORDER BY n.created_at DESC
            LIMIT 20
        ");
        
        return $query->rows;
    }
    
    /**
     * Get all user IDs with devices
     */
    private function getAllUserIds() {
        $query = $this->db->query("
            SELECT DISTINCT user_id 
            FROM " . DB_PREFIX . "meschain_user_devices 
            WHERE status = 1
        ");
        
        return array_column($query->rows, 'user_id');
    }
    
    /**
     * Get active user IDs (logged in within last 30 days)
     */
    private function getActiveUserIds() {
        $query = $this->db->query("
            SELECT DISTINCT d.user_id 
            FROM " . DB_PREFIX . "meschain_user_devices d
            INNER JOIN " . DB_PREFIX . "user u ON d.user_id = u.user_id
            WHERE d.status = 1 
            AND u.date_added >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        return array_column($query->rows, 'user_id');
    }
    
    /**
     * Install mobile system
     */
    public function install() {
        $this->load->library('meschain/helper/mobile_installer');
        $installer = new MesChainMobileInstaller($this->db);
        
        $json = [];
        
        if ($installer->install()) {
            $json['success'] = 'Mobile system installed successfully';
        } else {
            $json['error'] = 'Failed to install mobile system';
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get mobile dashboard data
     */
    public function getDashboardData() {
        $this->load->library('meschain/mobile/mobile_api_gateway');
        $this->load->library('meschain/mobile/push_notification_service');
        
        $mobile_api = new MesChainMobileApiGateway($this->registry);
        $push_service = new MesChainPushNotificationService($this->registry);
        
        $json = [
            'api_stats' => $mobile_api->getStatistics(),
            'push_stats' => $push_service->getStatistics(),
            'devices_count' => $this->getDevicesCount(),
            'recent_activities' => $this->getRecentMobileActivities(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get devices count by platform
     */
    private function getDevicesCount() {
        $query = $this->db->query("
            SELECT 
                platform,
                COUNT(*) as count
            FROM " . DB_PREFIX . "meschain_user_devices 
            WHERE status = 1 
            GROUP BY platform
        ");
        
        $counts = [];
        foreach ($query->rows as $row) {
            $counts[$row['platform']] = $row['count'];
        }
        
        return $counts;
    }
    
    /**
     * Get recent mobile activities
     */
    private function getRecentMobileActivities() {
        $activities = [];
        
        // Recent API calls
        $api_calls = $this->db->query("
            SELECT 'api_call' as type, endpoint as details, created_at
            FROM " . DB_PREFIX . "meschain_mobile_api_logs
            ORDER BY created_at DESC
            LIMIT 5
        ")->rows;
        
        // Recent notifications
        $notifications = $this->db->query("
            SELECT 'notification' as type, title as details, created_at
            FROM " . DB_PREFIX . "meschain_push_notifications_log
            ORDER BY created_at DESC
            LIMIT 5
        ")->rows;
        
        // Recent device registrations
        $devices = $this->db->query("
            SELECT 'device_registration' as type, platform as details, created_at
            FROM " . DB_PREFIX . "meschain_user_devices
            WHERE status = 1
            ORDER BY created_at DESC
            LIMIT 5
        ")->rows;
        
        $activities = array_merge($api_calls, $notifications, $devices);
        
        // Sort by created_at
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($activities, 0, 10);
    }
} 