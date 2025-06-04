<?php
/**
 * MesChain API Integration Service
 * Coordinates between all API components and provides unified interface
 * 
 * @version 1.0.0
 * @date June 2, 2025
 * @author MesChain Development Team
 */

class MeschainApiIntegrationService {
    
    private $error_handler;
    private $database_manager;
    private $response_formatter;
    private $rate_limiter;
    private $config;
    private $logger;
    
    public function __construct($db, $config = []) {
        $this->config = array_merge([
            'enable_logging' => true,
            'enable_caching' => true,
            'enable_rate_limiting' => true,
            'enable_metrics' => true,
            'cache_ttl' => 300, // 5 minutes
            'max_requests_per_minute' => 100,
            'debug_mode' => false
        ], $config);
        
        $this->loadComponents($db);
        $this->initializeLogger();
    }
    
    /**
     * Load all MesChain API components
     */
    private function loadComponents($db) {
        // Load error handler
        if (file_exists(DIR_SYSTEM . 'library/meschain/api_error_handler.php')) {
            require_once(DIR_SYSTEM . 'library/meschain/api_error_handler.php');
            $this->error_handler = new MeschainApiErrorHandler();
        }
        
        // Load database manager
        if (file_exists(DIR_SYSTEM . 'library/meschain/database_manager.php')) {
            require_once(DIR_SYSTEM . 'library/meschain/database_manager.php');
            $this->database_manager = new MeschainDatabaseManager($db);
        }
        
        // Load response formatter
        if (file_exists(DIR_SYSTEM . 'library/meschain/api_response_formatter.php')) {
            require_once(DIR_SYSTEM . 'library/meschain/api_response_formatter.php');
            $this->response_formatter = new MeschainApiResponseFormatter();
        }
        
        // Load rate limiter
        if (file_exists(DIR_SYSTEM . 'library/meschain/advanced_rate_limiter.php')) {
            require_once(DIR_SYSTEM . 'library/meschain/advanced_rate_limiter.php');
            $this->rate_limiter = new MeschainAdvancedRateLimiter($db, [
                'requests_per_minute' => $this->config['max_requests_per_minute']
            ]);
        }
    }
    
    /**
     * Initialize logger
     */
    private function initializeLogger() {
        $this->logger = new class {
            public function log($level, $message, $context = []) {
                if (!defined('DIR_LOGS')) return;
                
                $log_entry = [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'level' => $level,
                    'message' => $message,
                    'context' => $context,
                    'memory_usage' => memory_get_usage(true),
                    'peak_memory' => memory_get_peak_usage(true)
                ];
                
                $log_file = DIR_LOGS . 'meschain_api_' . date('Y-m-d') . '.log';
                file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
            }
        };
    }
    
    /**
     * Process API request with full integration
     */
    public function processRequest($endpoint, $method, $data = [], $headers = [], $user_ip = 'unknown') {
        $start_time = microtime(true);
        $request_id = $this->generateRequestId();
        
        try {
            // Log request start
            if ($this->config['enable_logging']) {
                $this->logger->log('info', 'API request started', [
                    'request_id' => $request_id,
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'user_ip' => $user_ip
                ]);
            }
            
            // Check rate limiting
            if ($this->config['enable_rate_limiting'] && $this->rate_limiter) {
                $rate_limit_result = $this->rate_limiter->checkLimit($user_ip, $endpoint);
                if (!$rate_limit_result['allowed']) {
                    return $this->response_formatter->formatRateLimitResponse(
                        $rate_limit_result['reset_time'],
                        $rate_limit_result['limit']
                    );
                }
            }
            
            // Validate request data
            $validation_rules = $this->getValidationRules($endpoint);
            if (!empty($validation_rules)) {
                $validation_result = $this->error_handler->validateRequest($validation_rules, $data);
                if (!$validation_result['valid']) {
                    return $this->response_formatter->formatValidationErrorResponse(
                        $validation_result['errors']
                    );
                }
            }
            
            // Check cache if enabled
            $cache_key = null;
            if ($this->config['enable_caching'] && $method === 'GET') {
                $cache_key = $this->generateCacheKey($endpoint, $data);
                $cached_response = $this->getCachedResponse($cache_key);
                if ($cached_response) {
                    $this->logger->log('info', 'Cache hit', ['cache_key' => $cache_key]);
                    return $cached_response;
                }
            }
            
            // Process the actual request
            $response_data = $this->executeRequest($endpoint, $method, $data, $headers);
            
            // Format response
            $response = $this->response_formatter->formatSuccessResponse(
                $response_data,
                'Request processed successfully'
            );
            
            // Cache response if applicable
            if ($cache_key && $this->config['enable_caching']) {
                $this->setCachedResponse($cache_key, $response);
            }
            
            // Log success
            $processing_time = (microtime(true) - $start_time) * 1000;
            if ($this->config['enable_logging']) {
                $this->logger->log('info', 'API request completed', [
                    'request_id' => $request_id,
                    'processing_time' => $processing_time,
                    'endpoint' => $endpoint
                ]);
            }
            
            // Store metrics
            if ($this->config['enable_metrics'] && $this->database_manager) {
                $this->database_manager->logApiRequest($endpoint, $data, $user_ip, [
                    'processing_time' => $processing_time,
                    'method' => $method,
                    'status' => 'success'
                ]);
            }
            
            return $response;
            
        } catch (Exception $e) {
            // Handle error
            $error_response = $this->error_handler->handleException($e, [
                'request_id' => $request_id,
                'endpoint' => $endpoint,
                'method' => $method,
                'user_ip' => $user_ip,
                'request_data' => $data
            ]);
            
            // Log error
            $processing_time = (microtime(true) - $start_time) * 1000;
            if ($this->config['enable_logging']) {
                $this->logger->log('error', 'API request failed', [
                    'request_id' => $request_id,
                    'processing_time' => $processing_time,
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Store error metrics
            if ($this->config['enable_metrics'] && $this->database_manager) {
                $this->database_manager->logApiRequest($endpoint, $data, $user_ip, [
                    'processing_time' => $processing_time,
                    'method' => $method,
                    'status' => 'error',
                    'error_message' => $e->getMessage()
                ]);
            }
            
            return $error_response;
        }
    }
    
    /**
     * Get validation rules for endpoint
     */
    private function getValidationRules($endpoint) {
        $rules = [
            'dashboard_metrics' => [
                'period' => ['type' => 'string', 'required' => false],
                'format' => ['type' => 'string', 'required' => false],
                'marketplace' => ['type' => 'string', 'required' => false]
            ],
            'amazon_metrics' => [
                'period' => ['type' => 'string', 'required' => false],
                'format' => ['type' => 'string', 'required' => false],
                'include_fees' => ['type' => 'string', 'required' => false],
                'region' => ['type' => 'string', 'required' => false]
            ],
            'ebay_metrics' => [
                'period' => ['type' => 'string', 'required' => false],
                'format' => ['type' => 'string', 'required' => false],
                'site_id' => ['type' => 'string', 'required' => false]
            ],
            'trendyol_metrics' => [
                'period' => ['type' => 'string', 'required' => false],
                'format' => ['type' => 'string', 'required' => false],
                'include_campaigns' => ['type' => 'string', 'required' => false]
            ],
            'product_sync' => [
                'marketplace' => ['type' => 'string', 'required' => true],
                'product_ids' => ['type' => 'array', 'required' => false],
                'force_update' => ['type' => 'string', 'required' => false]
            ],
            'order_sync' => [
                'marketplace' => ['type' => 'string', 'required' => true],
                'date_from' => ['type' => 'string', 'required' => false],
                'date_to' => ['type' => 'string', 'required' => false]
            ]
        ];
        
        return $rules[$endpoint] ?? [];
    }
    
    /**
     * Execute the actual request
     */
    private function executeRequest($endpoint, $method, $data, $headers) {
        // This would route to appropriate controller methods
        // For now, return mock data based on endpoint
        
        switch ($endpoint) {
            case 'dashboard_metrics':
                return $this->getDashboardMetrics($data);
            case 'amazon_metrics':
                return $this->getAmazonMetrics($data);
            case 'ebay_metrics':
                return $this->getEbayMetrics($data);
            case 'trendyol_metrics':
                return $this->getTrendyolMetrics($data);
            case 'product_sync':
                return $this->syncProducts($data);
            case 'order_sync':
                return $this->syncOrders($data);
            default:
                throw new Exception('Unknown endpoint: ' . $endpoint);
        }
    }
    
    /**
     * Get dashboard metrics
     */
    private function getDashboardMetrics($params) {
        return [
            'total_orders' => rand(100, 1000),
            'total_revenue' => rand(10000, 100000),
            'active_products' => rand(50, 500),
            'conversion_rate' => rand(2, 8) . '%',
            'average_order_value' => rand(50, 200),
            'marketplace_status' => [
                'amazon' => 'connected',
                'ebay' => 'connected',
                'trendyol' => 'connected',
                'n11' => 'connected',
                'hepsiburada' => 'connected',
                'ozon' => 'connected'
            ]
        ];
    }
    
    /**
     * Get Amazon-specific metrics
     */
    private function getAmazonMetrics($params) {
        return [
            'marketplace' => 'amazon',
            'orders_count' => rand(50, 200),
            'revenue' => rand(5000, 25000),
            'active_listings' => rand(25, 150),
            'seller_rating' => rand(95, 100) / 10,
            'account_health' => 'good',
            'fba_inventory' => rand(100, 1000),
            'advertising_spend' => rand(500, 2000),
            'advertising_sales' => rand(2000, 8000)
        ];
    }
    
    /**
     * Get eBay-specific metrics
     */
    private function getEbayMetrics($params) {
        return [
            'marketplace' => 'ebay',
            'active_listings' => rand(30, 180),
            'total_sales' => rand(3000, 15000),
            'watchers' => rand(50, 300),
            'seller_score' => rand(98, 100) . '%',
            'defect_rate' => rand(0, 2) . '%',
            'shipping_performance' => rand(95, 100) . '%'
        ];
    }
    
    /**
     * Get Trendyol-specific metrics
     */
    private function getTrendyolMetrics($params) {
        return [
            'marketplace' => 'trendyol',
            'active_products' => rand(40, 200),
            'monthly_sales' => rand(4000, 20000),
            'seller_score' => rand(4.2, 4.9),
            'return_rate' => rand(2, 8) . '%',
            'commission_rate' => rand(8, 15) . '%',
            'campaigns_active' => rand(2, 10)
        ];
    }
    
    /**
     * Sync products
     */
    private function syncProducts($params) {
        $marketplace = $params['marketplace'] ?? 'all';
        return [
            'sync_id' => $this->generateRequestId(),
            'marketplace' => $marketplace,
            'products_processed' => rand(10, 100),
            'products_updated' => rand(5, 50),
            'errors' => rand(0, 5),
            'status' => 'completed'
        ];
    }
    
    /**
     * Sync orders
     */
    private function syncOrders($params) {
        $marketplace = $params['marketplace'] ?? 'all';
        return [
            'sync_id' => $this->generateRequestId(),
            'marketplace' => $marketplace,
            'orders_processed' => rand(5, 50),
            'orders_updated' => rand(2, 25),
            'errors' => rand(0, 2),
            'status' => 'completed'
        ];
    }
    
    /**
     * Generate cache key
     */
    private function generateCacheKey($endpoint, $data) {
        return 'meschain_api_' . md5($endpoint . serialize($data));
    }
    
    /**
     * Get cached response
     */
    private function getCachedResponse($cache_key) {
        if (!$this->database_manager) return null;
        
        try {
            return $this->database_manager->getCacheValue($cache_key);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Set cached response
     */
    private function setCachedResponse($cache_key, $response) {
        if (!$this->database_manager) return;
        
        try {
            $this->database_manager->setCacheValue(
                $cache_key, 
                $response, 
                time() + $this->config['cache_ttl']
            );
        } catch (Exception $e) {
            // Silent fail on cache set
        }
    }
    
    /**
     * Generate unique request ID
     */
    private function generateRequestId() {
        return 'req_' . uniqid() . '_' . rand(1000, 9999);
    }
    
    /**
     * Get API health status
     */
    public function getHealthStatus() {
        $status = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'components' => [
                'error_handler' => $this->error_handler ? 'operational' : 'unavailable',
                'database_manager' => $this->database_manager ? 'operational' : 'unavailable',
                'response_formatter' => $this->response_formatter ? 'operational' : 'unavailable',
                'rate_limiter' => $this->rate_limiter ? 'operational' : 'unavailable'
            ],
            'config' => [
                'logging_enabled' => $this->config['enable_logging'],
                'caching_enabled' => $this->config['enable_caching'],
                'rate_limiting_enabled' => $this->config['enable_rate_limiting'],
                'metrics_enabled' => $this->config['enable_metrics']
            ]
        ];
        
        // Check if any critical components are down
        $critical_components = ['database_manager', 'response_formatter'];
        foreach ($critical_components as $component) {
            if ($status['components'][$component] === 'unavailable') {
                $status['status'] = 'degraded';
                break;
            }
        }
        
        return $status;
    }
    
    /**
     * Get API statistics
     */
    public function getApiStatistics($period_days = 7) {
        if (!$this->database_manager) {
            return ['error' => 'Database manager not available'];
        }
        
        try {
            return [
                'total_requests' => $this->database_manager->getApiRequestCount($period_days),
                'error_rate' => $this->database_manager->getApiErrorRate($period_days),
                'average_response_time' => $this->database_manager->getAverageResponseTime($period_days),
                'top_endpoints' => $this->database_manager->getTopEndpoints($period_days),
                'rate_limit_violations' => $this->database_manager->getRateLimitViolations($period_days)
            ];
        } catch (Exception $e) {
            return ['error' => 'Failed to retrieve statistics: ' . $e->getMessage()];
        }
    }
}
