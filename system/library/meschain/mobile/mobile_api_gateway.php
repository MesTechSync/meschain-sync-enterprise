<?php
/**
 * MesChain Mobile API Gateway
 * Optimized API gateway for mobile applications
 * 
 * @category   MesChain
 * @package    Mobile API
 * @author     MesChain Development Team
 * @copyright  2025 MesChain
 * @license    https://meschain.com/license
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

class MesChainMobileApiGateway {
    
    private $registry;
    private $config;
    private $cache;
    private $log;
    private $oauth_server;
    private $api_version = 'v1';
    private $allowed_endpoints = [];
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $registry->get('config');
        $this->cache = $registry->get('cache');
        $this->log = new Log('meschain_mobile_api.log');
        
        // Load OAuth server for authentication
        $this->load->library('meschain/security/oauth_server');
        $this->oauth_server = new MesChainOAuthServer($registry);
        
        $this->initializeEndpoints();
    }
    
    /**
     * Process mobile API request
     */
    public function processRequest($endpoint, $method = 'GET', $data = [], $headers = []) {
        try {
            // Start request timing
            $start_time = microtime(true);
            
            // Validate API version
            if (!$this->validateApiVersion($headers)) {
                return $this->errorResponse('Unsupported API version', 400);
            }
            
            // Authenticate request
            $auth_result = $this->authenticateRequest($headers);
            if (!$auth_result['success']) {
                return $this->errorResponse($auth_result['error'], 401);
            }
            
            // Validate endpoint
            if (!$this->validateEndpoint($endpoint, $method)) {
                return $this->errorResponse('Endpoint not found', 404);
            }
            
            // Rate limiting check
            $rate_limit_result = $this->checkRateLimit($auth_result['client_id'], $endpoint);
            if (!$rate_limit_result['allowed']) {
                return $this->errorResponse('Rate limit exceeded', 429, [
                    'retry_after' => $rate_limit_result['retry_after']
                ]);
            }
            
            // Process the request
            $response = $this->routeRequest($endpoint, $method, $data, $auth_result);
            
            // Calculate response time
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            
            // Add metadata to response
            $response['meta'] = [
                'api_version' => $this->api_version,
                'response_time' => $response_time . 'ms',
                'timestamp' => date('c'),
                'request_id' => $this->generateRequestId()
            ];
            
            // Log successful request
            $this->logApiRequest($endpoint, $method, $auth_result['client_id'], true, $response_time);
            
            return $this->successResponse($response);
            
        } catch (Exception $e) {
            $this->log->write('Mobile API error: ' . $e->getMessage());
            return $this->errorResponse('Internal server error', 500);
        }
    }
    
    /**
     * Route request to appropriate handler
     */
    private function routeRequest($endpoint, $method, $data, $auth_result) {
        $endpoint_parts = explode('/', trim($endpoint, '/'));
        $resource = $endpoint_parts[0] ?? '';
        $action = $endpoint_parts[1] ?? '';
        $id = $endpoint_parts[2] ?? null;
        
        switch ($resource) {
            case 'dashboard':
                return $this->handleDashboardRequest($action, $method, $data, $auth_result);
            
            case 'marketplaces':
                return $this->handleMarketplaceRequest($action, $id, $method, $data, $auth_result);
            
            case 'orders':
                return $this->handleOrdersRequest($action, $id, $method, $data, $auth_result);
            
            case 'products':
                return $this->handleProductsRequest($action, $id, $method, $data, $auth_result);
            
            case 'analytics':
                return $this->handleAnalyticsRequest($action, $method, $data, $auth_result);
            
            case 'notifications':
                return $this->handleNotificationsRequest($action, $id, $method, $data, $auth_result);
            
            case 'settings':
                return $this->handleSettingsRequest($action, $method, $data, $auth_result);
            
            default:
                throw new Exception('Unknown resource: ' . $resource);
        }
    }
    
    /**
     * Handle dashboard requests
     */
    private function handleDashboardRequest($action, $method, $data, $auth_result) {
        switch ($action) {
            case 'overview':
                return $this->getDashboardOverview();
            
            case 'stats':
                return $this->getDashboardStats($data['period'] ?? '24h');
            
            case 'recent-activities':
                return $this->getRecentActivities($data['limit'] ?? 10);
            
            case 'alerts':
                return $this->getActiveAlerts();
            
            default:
                throw new Exception('Unknown dashboard action: ' . $action);
        }
    }
    
    /**
     * Handle marketplace requests
     */
    private function handleMarketplaceRequest($action, $id, $method, $data, $auth_result) {
        switch ($action) {
            case 'list':
                return $this->getMarketplaceList();
            
            case 'status':
                return $this->getMarketplaceStatus($id);
            
            case 'sync':
                if ($method === 'POST') {
                    return $this->triggerMarketplaceSync($id, $data);
                }
                break;
            
            case 'products':
                return $this->getMarketplaceProducts($id, $data);
            
            case 'orders':
                return $this->getMarketplaceOrders($id, $data);
            
            default:
                throw new Exception('Unknown marketplace action: ' . $action);
        }
    }
    
    /**
     * Handle orders requests
     */
    private function handleOrdersRequest($action, $id, $method, $data, $auth_result) {
        switch ($action) {
            case 'list':
                return $this->getOrdersList($data);
            
            case 'details':
                return $this->getOrderDetails($id);
            
            case 'update':
                if ($method === 'POST' || $method === 'PUT') {
                    return $this->updateOrder($id, $data);
                }
                break;
            
            case 'track':
                return $this->getOrderTracking($id);
            
            default:
                throw new Exception('Unknown orders action: ' . $action);
        }
    }
    
    /**
     * Handle products requests
     */
    private function handleProductsRequest($action, $id, $method, $data, $auth_result) {
        switch ($action) {
            case 'list':
                return $this->getProductsList($data);
            
            case 'details':
                return $this->getProductDetails($id);
            
            case 'update':
                if ($method === 'POST' || $method === 'PUT') {
                    return $this->updateProduct($id, $data);
                }
                break;
            
            case 'inventory':
                return $this->getProductInventory($id);
            
            case 'sync':
                if ($method === 'POST') {
                    return $this->syncProduct($id, $data);
                }
                break;
            
            default:
                throw new Exception('Unknown products action: ' . $action);
        }
    }
    
    /**
     * Handle analytics requests
     */
    private function handleAnalyticsRequest($action, $method, $data, $auth_result) {
        switch ($action) {
            case 'summary':
                return $this->getAnalyticsSummary($data['period'] ?? '7d');
            
            case 'sales':
                return $this->getSalesAnalytics($data);
            
            case 'performance':
                return $this->getPerformanceMetrics($data);
            
            case 'trends':
                return $this->getTrendAnalysis($data);
            
            default:
                throw new Exception('Unknown analytics action: ' . $action);
        }
    }
    
    /**
     * Handle notifications requests
     */
    private function handleNotificationsRequest($action, $id, $method, $data, $auth_result) {
        switch ($action) {
            case 'list':
                return $this->getNotificationsList($data);
            
            case 'mark-read':
                if ($method === 'POST') {
                    return $this->markNotificationRead($id);
                }
                break;
            
            case 'mark-all-read':
                if ($method === 'POST') {
                    return $this->markAllNotificationsRead($auth_result['user_id']);
                }
                break;
            
            case 'settings':
                if ($method === 'GET') {
                    return $this->getNotificationSettings($auth_result['user_id']);
                } elseif ($method === 'POST') {
                    return $this->updateNotificationSettings($auth_result['user_id'], $data);
                }
                break;
            
            default:
                throw new Exception('Unknown notifications action: ' . $action);
        }
    }
    
    /**
     * Handle settings requests
     */
    private function handleSettingsRequest($action, $method, $data, $auth_result) {
        switch ($action) {
            case 'profile':
                if ($method === 'GET') {
                    return $this->getUserProfile($auth_result['user_id']);
                } elseif ($method === 'POST') {
                    return $this->updateUserProfile($auth_result['user_id'], $data);
                }
                break;
            
            case 'preferences':
                if ($method === 'GET') {
                    return $this->getUserPreferences($auth_result['user_id']);
                } elseif ($method === 'POST') {
                    return $this->updateUserPreferences($auth_result['user_id'], $data);
                }
                break;
            
            case 'device':
                if ($method === 'POST') {
                    return $this->registerDevice($auth_result['user_id'], $data);
                } elseif ($method === 'DELETE') {
                    return $this->unregisterDevice($auth_result['user_id'], $data['device_token']);
                }
                break;
            
            default:
                throw new Exception('Unknown settings action: ' . $action);
        }
    }
    
    /**
     * Get dashboard overview
     */
    private function getDashboardOverview() {
        $db = $this->registry->get('db');
        
        // Get key metrics
        $today_orders = $db->query("
            SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as revenue
            FROM " . DB_PREFIX . "meschain_orders 
            WHERE DATE(created_at) = CURDATE()
        ")->row;
        
        $active_products = $db->query("
            SELECT COUNT(*) as count 
            FROM " . DB_PREFIX . "meschain_products 
            WHERE status = 1
        ")->row['count'];
        
        $sync_status = $db->query("
            SELECT marketplace, status, last_sync
            FROM " . DB_PREFIX . "meschain_sync_status 
            ORDER BY last_sync DESC
        ")->rows;
        
        return [
            'metrics' => [
                'today_orders' => (int)$today_orders['count'],
                'today_revenue' => number_format($today_orders['revenue'], 2),
                'active_products' => (int)$active_products,
                'sync_status' => $sync_status
            ]
        ];
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats($period = '24h') {
        $db = $this->registry->get('db');
        
        // Calculate time range
        $time_condition = $this->getTimeCondition($period);
        
        $stats = [
            'orders' => $db->query("
                SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total
                FROM " . DB_PREFIX . "meschain_orders 
                WHERE {$time_condition}
            ")->row,
            
            'products_synced' => $db->query("
                SELECT COUNT(*) as count
                FROM " . DB_PREFIX . "meschain_sync_logs 
                WHERE sync_type = 'product' AND {$time_condition}
            ")->row['count'],
            
            'api_calls' => $db->query("
                SELECT COUNT(*) as count
                FROM " . DB_PREFIX . "meschain_api_logs 
                WHERE {$time_condition}
            ")->row['count']
        ];
        
        return ['stats' => $stats, 'period' => $period];
    }
    
    /**
     * Authenticate mobile API request
     */
    private function authenticateRequest($headers) {
        $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (empty($auth_header)) {
            return ['success' => false, 'error' => 'Authorization header missing'];
        }
        
        if (!preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            return ['success' => false, 'error' => 'Invalid authorization format'];
        }
        
        $token = $matches[1];
        $validation_result = $this->oauth_server->validateToken($token);
        
        if (!$validation_result['success']) {
            return ['success' => false, 'error' => $validation_result['error']];
        }
        
        return [
            'success' => true,
            'client_id' => $validation_result['client_id'],
            'user_id' => $validation_result['user_id'],
            'scope' => $validation_result['scope']
        ];
    }
    
    /**
     * Check rate limit for mobile API
     */
    private function checkRateLimit($client_id, $endpoint) {
        $cache_key = 'mobile_rate_limit_' . $client_id . '_' . md5($endpoint);
        $current_count = (int)$this->cache->get($cache_key);
        
        // Mobile API limits: 1000 requests per hour per client
        $limit = 1000;
        $window = 3600; // 1 hour
        
        if ($current_count >= $limit) {
            $ttl = $this->cache->getTtl($cache_key);
            return [
                'allowed' => false,
                'retry_after' => $ttl > 0 ? $ttl : $window
            ];
        }
        
        // Increment counter
        $this->cache->set($cache_key, $current_count + 1, $window);
        
        return ['allowed' => true];
    }
    
    /**
     * Validate API version
     */
    private function validateApiVersion($headers) {
        $version = $headers['API-Version'] ?? $headers['api-version'] ?? $this->api_version;
        return in_array($version, ['v1']); // Support only v1 for now
    }
    
    /**
     * Validate endpoint
     */
    private function validateEndpoint($endpoint, $method) {
        $endpoint_key = $method . ':' . $endpoint;
        
        if (empty($this->allowed_endpoints)) {
            return true; // Allow all if not configured
        }
        
        return in_array($endpoint_key, $this->allowed_endpoints) || 
               in_array('*:' . $endpoint, $this->allowed_endpoints);
    }
    
    /**
     * Initialize allowed endpoints
     */
    private function initializeEndpoints() {
        $this->allowed_endpoints = [
            'GET:dashboard/overview',
            'GET:dashboard/stats',
            'GET:dashboard/recent-activities',
            'GET:dashboard/alerts',
            'GET:marketplaces/list',
            'GET:marketplaces/status',
            'POST:marketplaces/sync',
            'GET:marketplaces/products',
            'GET:marketplaces/orders',
            'GET:orders/list',
            'GET:orders/details',
            'POST:orders/update',
            'GET:orders/track',
            'GET:products/list',
            'GET:products/details',
            'POST:products/update',
            'GET:products/inventory',
            'POST:products/sync',
            'GET:analytics/summary',
            'GET:analytics/sales',
            'GET:analytics/performance',
            'GET:analytics/trends',
            'GET:notifications/list',
            'POST:notifications/mark-read',
            'POST:notifications/mark-all-read',
            'GET:notifications/settings',
            'POST:notifications/settings',
            'GET:settings/profile',
            'POST:settings/profile',
            'GET:settings/preferences',
            'POST:settings/preferences',
            'POST:settings/device',
            'DELETE:settings/device'
        ];
    }
    
    /**
     * Generate unique request ID
     */
    private function generateRequestId() {
        return 'mob_' . uniqid() . '_' . bin2hex(random_bytes(4));
    }
    
    /**
     * Log API request
     */
    private function logApiRequest($endpoint, $method, $client_id, $success, $response_time) {
        $db = $this->registry->get('db');
        
        $db->query("
            INSERT INTO " . DB_PREFIX . "meschain_mobile_api_logs 
            (endpoint, method, client_id, success, response_time, ip_address, user_agent, created_at) 
            VALUES (
                '" . $db->escape($endpoint) . "',
                '" . $db->escape($method) . "',
                '" . $db->escape($client_id) . "',
                " . ($success ? 1 : 0) . ",
                " . (float)$response_time . ",
                '" . $db->escape($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "',
                '" . $db->escape($_SERVER['HTTP_USER_AGENT'] ?? 'mobile-app') . "',
                NOW()
            )
        ");
    }
    
    /**
     * Get time condition for SQL queries
     */
    private function getTimeCondition($period) {
        switch ($period) {
            case '1h':
                return "created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            case '24h':
                return "created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            case '7d':
                return "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case '30d':
                return "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            default:
                return "created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        }
    }
    
    /**
     * Success response format
     */
    private function successResponse($data) {
        return [
            'success' => true,
            'data' => $data
        ];
    }
    
    /**
     * Error response format
     */
    private function errorResponse($message, $code = 400, $extra = []) {
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code
            ]
        ];
        
        if (!empty($extra)) {
            $response['error'] = array_merge($response['error'], $extra);
        }
        
        return $response;
    }
    
    /**
     * Get mobile API statistics
     */
    public function getStatistics() {
        $db = $this->registry->get('db');
        
        $stats = [];
        
        // API call counts
        $stats['total_calls'] = $db->query("
            SELECT COUNT(*) as count 
            FROM " . DB_PREFIX . "meschain_mobile_api_logs
        ")->row['count'];
        
        $stats['today_calls'] = $db->query("
            SELECT COUNT(*) as count 
            FROM " . DB_PREFIX . "meschain_mobile_api_logs 
            WHERE DATE(created_at) = CURDATE()
        ")->row['count'];
        
        // Success rate
        $stats['success_rate'] = $db->query("
            SELECT 
                ROUND(AVG(success) * 100, 2) as rate
            FROM " . DB_PREFIX . "meschain_mobile_api_logs 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ")->row['rate'];
        
        // Average response time
        $stats['avg_response_time'] = $db->query("
            SELECT 
                ROUND(AVG(response_time), 2) as time
            FROM " . DB_PREFIX . "meschain_mobile_api_logs 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ")->row['time'];
        
        return $stats;
    }
} 