<?php
/**
 * MesChain API Gateway
 * Enterprise-level API management system
 * 
 * @category   MesChain
 * @package    API Gateway
 * @author     MesChain Development Team
 * @copyright  2025 MesChain
 * @license    https://meschain.com/license
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

class MesChainApiGateway {
    
    private $registry;
    private $config;
    private $cache;
    private $log;
    private $rate_limits = [];
    private $circuit_breakers = [];
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $registry->get('config');
        $this->cache = $registry->get('cache');
        $this->log = new Log('meschain_api_gateway.log');
        
        // Default rate limits
        $this->initializeRateLimits();
        $this->initializeCircuitBreakers();
    }
    
    /**
     * Initialize rate limiting rules
     */
    private function initializeRateLimits() {
        $this->rate_limits = [
            'default' => [
                'requests_per_minute' => 100,
                'requests_per_hour' => 1000,
                'requests_per_day' => 10000
            ],
            'premium' => [
                'requests_per_minute' => 500,
                'requests_per_hour' => 5000,
                'requests_per_day' => 50000
            ],
            'enterprise' => [
                'requests_per_minute' => 1000,
                'requests_per_hour' => 10000,
                'requests_per_day' => 100000
            ]
        ];
    }
    
    /**
     * Initialize circuit breakers
     */
    private function initializeCircuitBreakers() {
        $this->circuit_breakers = [
            'trendyol' => ['failure_threshold' => 5, 'timeout' => 60],
            'n11' => ['failure_threshold' => 5, 'timeout' => 60],
            'amazon' => ['failure_threshold' => 3, 'timeout' => 120],
            'ebay' => ['failure_threshold' => 5, 'timeout' => 60],
            'hepsiburada' => ['failure_threshold' => 5, 'timeout' => 60],
            'ozon' => ['failure_threshold' => 5, 'timeout' => 60]
        ];
    }
    
    /**
     * Process API request through gateway
     */
    public function processRequest($api_key, $endpoint, $method, $data = [], $headers = []) {
        try {
            // Start request processing
            $request_id = $this->generateRequestId();
            $start_time = microtime(true);
            
            $this->logRequest($request_id, $api_key, $endpoint, $method, $data);
            
            // 1. API Key validation
            $client_info = $this->validateApiKey($api_key);
            if (!$client_info) {
                throw new Exception('Invalid API key', 401);
            }
            
            // 2. Rate limiting check
            if (!$this->checkRateLimit($api_key, $client_info['plan'])) {
                throw new Exception('Rate limit exceeded', 429);
            }
            
            // 3. Circuit breaker check
            $service = $this->extractServiceFromEndpoint($endpoint);
            if (!$this->checkCircuitBreaker($service)) {
                throw new Exception('Service temporarily unavailable', 503);
            }
            
            // 4. Load balancing & routing
            $target_endpoint = $this->routeRequest($endpoint, $service);
            
            // 5. Execute request
            $response = $this->executeRequest($target_endpoint, $method, $data, $headers);
            
            // 6. Process response
            $processed_response = $this->processResponse($response, $client_info);
            
            // 7. Update metrics
            $this->updateMetrics($request_id, $service, $start_time, true);
            
            $this->logResponse($request_id, $processed_response);
            
            return [
                'success' => true,
                'data' => $processed_response,
                'request_id' => $request_id,
                'processing_time' => round((microtime(true) - $start_time) * 1000, 2)
            ];
            
        } catch (Exception $e) {
            $this->updateMetrics($request_id, $service ?? 'unknown', $start_time, false);
            $this->logError($request_id, $e);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'request_id' => $request_id
            ];
        }
    }
    
    /**
     * Validate API key
     */
    private function validateApiKey($api_key) {
        $cache_key = 'api_key_' . md5($api_key);
        $client_info = $this->cache->get($cache_key);
        
        if (!$client_info) {
            // Check database
            $db = $this->registry->get('db');
            $query = $db->query("
                SELECT ak.*, c.company_name, c.plan_type 
                FROM " . DB_PREFIX . "meschain_api_keys ak
                LEFT JOIN " . DB_PREFIX . "meschain_clients c ON ak.client_id = c.client_id
                WHERE ak.api_key = '" . $db->escape($api_key) . "' 
                AND ak.status = 1 
                AND ak.expires_at > NOW()
            ");
            
            if ($query->num_rows) {
                $client_info = $query->row;
                $this->cache->set($cache_key, $client_info, 3600); // Cache for 1 hour
            }
        }
        
        return $client_info;
    }
    
    /**
     * Check rate limiting
     */
    private function checkRateLimit($api_key, $plan = 'default') {
        $limits = $this->rate_limits[$plan] ?? $this->rate_limits['default'];
        $current_time = time();
        
        // Check minute limit
        $minute_key = 'rate_limit_minute_' . md5($api_key) . '_' . floor($current_time / 60);
        $minute_count = (int)$this->cache->get($minute_key);
        
        if ($minute_count >= $limits['requests_per_minute']) {
            return false;
        }
        
        // Check hour limit
        $hour_key = 'rate_limit_hour_' . md5($api_key) . '_' . floor($current_time / 3600);
        $hour_count = (int)$this->cache->get($hour_key);
        
        if ($hour_count >= $limits['requests_per_hour']) {
            return false;
        }
        
        // Check day limit
        $day_key = 'rate_limit_day_' . md5($api_key) . '_' . floor($current_time / 86400);
        $day_count = (int)$this->cache->get($day_key);
        
        if ($day_count >= $limits['requests_per_day']) {
            return false;
        }
        
        // Increment counters
        $this->cache->set($minute_key, $minute_count + 1, 60);
        $this->cache->set($hour_key, $hour_count + 1, 3600);
        $this->cache->set($day_key, $day_count + 1, 86400);
        
        return true;
    }
    
    /**
     * Check circuit breaker status
     */
    private function checkCircuitBreaker($service) {
        if (!isset($this->circuit_breakers[$service])) {
            return true;
        }
        
        $breaker_key = 'circuit_breaker_' . $service;
        $breaker_data = $this->cache->get($breaker_key);
        
        if (!$breaker_data) {
            $breaker_data = [
                'state' => 'closed',
                'failure_count' => 0,
                'last_failure_time' => 0
            ];
        }
        
        $config = $this->circuit_breakers[$service];
        
        // Check if circuit is open
        if ($breaker_data['state'] === 'open') {
            if (time() - $breaker_data['last_failure_time'] > $config['timeout']) {
                // Try to close circuit
                $breaker_data['state'] = 'half-open';
                $this->cache->set($breaker_key, $breaker_data, 3600);
            } else {
                return false; // Circuit still open
            }
        }
        
        return true;
    }
    
    /**
     * Update circuit breaker on failure
     */
    private function updateCircuitBreakerOnFailure($service) {
        if (!isset($this->circuit_breakers[$service])) {
            return;
        }
        
        $breaker_key = 'circuit_breaker_' . $service;
        $breaker_data = $this->cache->get($breaker_key) ?: [
            'state' => 'closed',
            'failure_count' => 0,
            'last_failure_time' => 0
        ];
        
        $breaker_data['failure_count']++;
        $breaker_data['last_failure_time'] = time();
        
        if ($breaker_data['failure_count'] >= $this->circuit_breakers[$service]['failure_threshold']) {
            $breaker_data['state'] = 'open';
        }
        
        $this->cache->set($breaker_key, $breaker_data, 3600);
    }
    
    /**
     * Route request to appropriate service
     */
    private function routeRequest($endpoint, $service) {
        $service_endpoints = [
            'trendyol' => 'https://api.trendyol.com',
            'n11' => 'https://api.n11.com',
            'amazon' => 'https://mws.amazonservices.com',
            'ebay' => 'https://api.ebay.com',
            'hepsiburada' => 'https://listing-external.hepsiburada.com',
            'ozon' => 'https://api-seller.ozon.ru'
        ];
        
        $base_url = $service_endpoints[$service] ?? '';
        return $base_url . $endpoint;
    }
    
    /**
     * Execute HTTP request
     */
    private function executeRequest($url, $method, $data = [], $headers = []) {
        $ch = curl_init();
        
        $default_headers = [
            'Content-Type: application/json',
            'User-Agent: MesChain-API-Gateway/1.0'
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => array_merge($default_headers, $headers),
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('HTTP request failed: ' . $error);
        }
        
        return [
            'http_code' => $http_code,
            'response' => $response
        ];
    }
    
    /**
     * Process API response
     */
    private function processResponse($response, $client_info) {
        // Add client-specific processing here
        $processed = json_decode($response['response'], true);
        
        // Add metadata
        $processed['_meta'] = [
            'client_id' => $client_info['client_id'],
            'plan' => $client_info['plan_type'],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        return $processed;
    }
    
    /**
     * Extract service name from endpoint
     */
    private function extractServiceFromEndpoint($endpoint) {
        $patterns = [
            'trendyol' => '/trendyol/',
            'n11' => '/n11/',
            'amazon' => '/amazon/',
            'ebay' => '/ebay/',
            'hepsiburada' => '/hepsiburada/',
            'ozon' => '/ozon/'
        ];
        
        foreach ($patterns as $service => $pattern) {
            if (preg_match($pattern, $endpoint)) {
                return $service;
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Generate unique request ID
     */
    private function generateRequestId() {
        return 'req_' . uniqid() . '_' . mt_rand(1000, 9999);
    }
    
    /**
     * Update metrics
     */
    private function updateMetrics($request_id, $service, $start_time, $success) {
        $processing_time = microtime(true) - $start_time;
        
        $db = $this->registry->get('db');
        $db->query("
            INSERT INTO " . DB_PREFIX . "meschain_api_metrics 
            (request_id, service, processing_time, success, created_at) 
            VALUES (
                '" . $db->escape($request_id) . "',
                '" . $db->escape($service) . "',
                '" . (float)$processing_time . "',
                '" . (int)$success . "',
                NOW()
            )
        ");
        
        // Update circuit breaker on failure
        if (!$success) {
            $this->updateCircuitBreakerOnFailure($service);
        }
    }
    
    /**
     * Log request
     */
    private function logRequest($request_id, $api_key, $endpoint, $method, $data) {
        $this->log->write('REQUEST ' . $request_id . ': ' . $method . ' ' . $endpoint . ' (API Key: ' . substr($api_key, 0, 8) . '...)');
    }
    
    /**
     * Log response
     */
    private function logResponse($request_id, $response) {
        $this->log->write('RESPONSE ' . $request_id . ': Success');
    }
    
    /**
     * Log error
     */
    private function logError($request_id, $exception) {
        $this->log->write('ERROR ' . $request_id . ': ' . $exception->getMessage());
    }
    
    /**
     * Get API Gateway statistics
     */
    public function getStatistics() {
        $db = $this->registry->get('db');
        
        // Get request statistics
        $stats_query = $db->query("
            SELECT 
                service,
                COUNT(*) as total_requests,
                SUM(success) as successful_requests,
                AVG(processing_time) as avg_processing_time,
                DATE(created_at) as date
            FROM " . DB_PREFIX . "meschain_api_metrics 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY service, DATE(created_at)
            ORDER BY date DESC, service
        ");
        
        // Get rate limit statistics
        $rate_limit_query = $db->query("
            SELECT 
                api_key,
                COUNT(*) as requests_today
            FROM " . DB_PREFIX . "meschain_api_requests 
            WHERE DATE(created_at) = CURDATE()
            GROUP BY api_key
            ORDER BY requests_today DESC
            LIMIT 10
        ");
        
        return [
            'request_stats' => $stats_query->rows,
            'rate_limit_stats' => $rate_limit_query->rows,
            'circuit_breaker_status' => $this->getCircuitBreakerStatus()
        ];
    }
    
    /**
     * Get circuit breaker status
     */
    private function getCircuitBreakerStatus() {
        $status = [];
        
        foreach ($this->circuit_breakers as $service => $config) {
            $breaker_key = 'circuit_breaker_' . $service;
            $breaker_data = $this->cache->get($breaker_key) ?: [
                'state' => 'closed',
                'failure_count' => 0,
                'last_failure_time' => 0
            ];
            
            $status[$service] = $breaker_data;
        }
        
        return $status;
    }
    
    /**
     * Reset circuit breaker
     */
    public function resetCircuitBreaker($service) {
        $breaker_key = 'circuit_breaker_' . $service;
        $this->cache->delete($breaker_key);
        
        $this->log->write('Circuit breaker reset for service: ' . $service);
        
        return true;
    }
} 