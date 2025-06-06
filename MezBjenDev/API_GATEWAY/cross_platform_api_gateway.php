<?php
/**
 * 🔗 MezBjen Phase 3: Cross-Platform API Gateway
 * Enterprise-Grade API Management & Orchestration System
 * 
 * @version 3.0.0
 * @date June 2025
 * @author MezBjen Development Team
 * 
 * Features:
 * - 🌐 Unified API Endpoint Management
 * - 🔐 Advanced Authentication & Authorization (OAuth 2.0, JWT, API Keys)
 * - 📊 Intelligent Rate Limiting & Throttling
 * - 🛡️ Security Scanning & DDoS Protection
 * - 📈 Real-time API Analytics & Monitoring
 * - 🔄 Request/Response Transformation
 * - 📱 Mobile API Optimization
 * - ⚡ Multi-tier Caching & Performance Optimization
 * - 🔗 Microservices Orchestration
 * - 📚 Developer Portal & API Documentation
 */

namespace MezBjen\ApiGateway;

class CrossPlatformApiGateway {
    
    private $config;
    private $db;
    private $cache;
    private $logger;
    private $security_manager;
    private $rate_limiter;
    private $analytics_engine;
    private $transformation_engine;
    private $load_balancer;
    private $developer_portal;
    
    // Gateway Configuration
    private $gateway_config = [
        'version' => '3.0.0',
        'environment' => 'production',
        'default_timeout' => 30,
        'max_request_size' => '10MB',
        'supported_versions' => ['v1', 'v2', 'v3'],
        'default_version' => 'v3'
    ];
    
    // Service Registry
    private $service_registry = [];
    
    // Health Check Configuration
    private $health_config = [
        'check_interval' => 30,
        'timeout' => 10,
        'failure_threshold' => 3,
        'recovery_threshold' => 2
    ];
    
    public function __construct($config, $db, $cache) {
        $this->config = $config;
        $this->db = $db;
        $this->cache = $cache;
        
        $this->initializeGateway();
        $this->registerCoreServices();
        $this->setupHealthMonitoring();
    }
    
    /**
     * 🚀 Initialize API Gateway Core Systems
     */
    private function initializeGateway() {
        // Initialize Logger
        $this->logger = new ApiGatewayLogger([
            'level' => 'debug',
            'max_file_size' => '100MB',
            'rotation' => 'daily',
            'retention' => 30
        ]);
        
        // Initialize Security Manager
        $this->security_manager = new GatewaySecurityManager($this->config);
        
        // Initialize Advanced Rate Limiter
        $this->rate_limiter = new GatewayRateLimiter($this->db, $this->cache);
        
        // Initialize Analytics Engine
        $this->analytics_engine = new GatewayAnalyticsEngine($this->db, $this->cache);
        
        // Initialize Transformation Engine
        $this->transformation_engine = new RequestTransformationEngine();
        
        // Initialize Load Balancer
        $this->load_balancer = new IntelligentLoadBalancer($this->config);
        
        // Initialize Developer Portal
        $this->developer_portal = new DeveloperPortal($this->db, $this->config);
        
        $this->logger->info('🚀 API Gateway initialized successfully');
    }
    
    /**
     * 🌐 Main Request Processing Pipeline
     */
    public function processRequest($method, $path, $headers, $body, $query_params = []) {
        $request_id = $this->generateRequestId();
        $start_time = microtime(true);
        
        try {
            // Pre-processing Pipeline
            $request_context = $this->createRequestContext($request_id, $method, $path, $headers, $body, $query_params);
            
            // Security Validation
            $this->validateRequestSecurity($request_context);
            
            // Authentication & Authorization
            $auth_result = $this->authenticateRequest($request_context);
            $request_context['auth'] = $auth_result;
            
            // Rate Limiting Check
            $this->enforceRateLimit($request_context);
            
            // Route Resolution
            $route_info = $this->resolveRoute($request_context);
            $request_context['route'] = $route_info;
            
            // Request Transformation
            $transformed_request = $this->transformRequest($request_context);
            
            // Load Balancing & Service Selection
            $target_service = $this->selectTargetService($route_info);
            
            // Circuit Breaker Check
            $this->checkCircuitBreaker($target_service);
            
            // Execute Request
            $response = $this->executeServiceRequest($target_service, $transformed_request);
            
            // Response Transformation
            $transformed_response = $this->transformResponse($response, $request_context);
            
            // Analytics & Monitoring
            $this->recordAnalytics($request_context, $response, $start_time);
            
            // Caching Strategy
            $this->applyCachingStrategy($request_context, $transformed_response);
            
            return $transformed_response;
            
        } catch (Exception $e) {
            return $this->handleError($e, $request_context ?? null, $start_time);
        }
    }
    
    /**
     * 🔐 Advanced Authentication & Authorization
     */
    private function authenticateRequest($request_context) {
        $auth_header = $request_context['headers']['authorization'] ?? null;
        $api_key = $request_context['headers']['x-api-key'] ?? $request_context['query']['api_key'] ?? null;
        
        // Multiple Authentication Methods
        if ($auth_header && strpos($auth_header, 'Bearer ') === 0) {
            return $this->authenticateJWT(substr($auth_header, 7), $request_context);
        } elseif ($api_key) {
            return $this->authenticateApiKey($api_key, $request_context);
        } elseif ($auth_header && strpos($auth_header, 'OAuth ') === 0) {
            return $this->authenticateOAuth($auth_header, $request_context);
        } else {
            // Check for public endpoints
            if ($this->isPublicEndpoint($request_context['path'])) {
                return ['type' => 'public', 'user_id' => 'anonymous'];
            }
            throw new UnauthorizedException('Authentication required');
        }
    }
    
    /**
     * 🛡️ JWT Token Authentication
     */
    private function authenticateJWT($token, $request_context) {
        try {
            $decoded = $this->security_manager->validateJWT($token);
            
            // Token validation
            if ($decoded['exp'] < time()) {
                throw new UnauthorizedException('Token expired');
            }
            
            // User validation
            $user = $this->validateUser($decoded['user_id']);
            if (!$user) {
                throw new UnauthorizedException('Invalid user');
            }
            
            // Scope validation
            $this->validateTokenScope($decoded, $request_context);
            
            return [
                'type' => 'jwt',
                'user_id' => $decoded['user_id'],
                'scopes' => $decoded['scopes'] ?? [],
                'token_id' => $decoded['jti'] ?? null,
                'user' => $user
            ];
            
        } catch (Exception $e) {
            throw new UnauthorizedException('Invalid JWT token: ' . $e->getMessage());
        }
    }
    
    /**
     * 🔑 API Key Authentication
     */
    private function authenticateApiKey($api_key, $request_context) {
        $key_info = $this->validateApiKey($api_key);
        
        if (!$key_info) {
            throw new UnauthorizedException('Invalid API key');
        }
        
        // Check key status
        if ($key_info['status'] !== 'active') {
            throw new UnauthorizedException('API key is not active');
        }
        
        // Check expiration
        if ($key_info['expires_at'] && strtotime($key_info['expires_at']) < time()) {
            throw new UnauthorizedException('API key expired');
        }
        
        // Check IP restrictions
        if ($key_info['allowed_ips']) {
            $client_ip = $this->getClientIP();
            $allowed_ips = json_decode($key_info['allowed_ips'], true);
            if (!in_array($client_ip, $allowed_ips)) {
                throw new UnauthorizedException('IP not allowed for this API key');
            }
        }
        
        return [
            'type' => 'api_key',
            'user_id' => $key_info['user_id'],
            'key_id' => $key_info['id'],
            'permissions' => json_decode($key_info['permissions'], true),
            'rate_limit_tier' => $key_info['rate_limit_tier']
        ];
    }
    
    /**
     * 📊 Intelligent Rate Limiting System
     */
    private function enforceRateLimit($request_context) {
        $auth = $request_context['auth'];
        $endpoint = $request_context['route']['endpoint'] ?? 'unknown';
        
        // Determine rate limit tier
        $tier = $this->determineRateLimitTier($auth, $endpoint);
        
        // Get rate limit configuration
        $limits = $this->getRateLimitConfig($tier, $endpoint);
        
        // Check various rate limit dimensions
        $checks = [
            'global' => $this->checkGlobalRateLimit(),
            'user' => $this->checkUserRateLimit($auth['user_id'], $limits),
            'ip' => $this->checkIPRateLimit($this->getClientIP(), $limits),
            'endpoint' => $this->checkEndpointRateLimit($endpoint, $auth['user_id'], $limits),
            'tier' => $this->checkTierRateLimit($tier, $auth['user_id'], $limits)
        ];
        
        foreach ($checks as $check_type => $result) {
            if (!$result['allowed']) {
                $this->recordRateLimitViolation($request_context, $check_type, $result);
                throw new RateLimitException(
                    $result['message'],
                    $result['retry_after'],
                    $result['limit'],
                    $result['remaining']
                );
            }
        }
        
        // Update rate limit counters
        $this->updateRateLimitCounters($request_context, $limits);
    }
    
    /**
     * 🔄 Request/Response Transformation Engine
     */
    private function transformRequest($request_context) {
        $route = $request_context['route'];
        $transformations = $route['transformations'] ?? [];
        
        $transformed = [
            'method' => $request_context['method'],
            'path' => $this->transformPath($request_context['path'], $transformations),
            'headers' => $this->transformHeaders($request_context['headers'], $transformations),
            'body' => $this->transformBody($request_context['body'], $transformations),
            'query' => $this->transformQuery($request_context['query'], $transformations)
        ];
        
        // Apply middleware transformations
        foreach ($route['middleware'] ?? [] as $middleware) {
            $transformed = $this->applyMiddleware($middleware, $transformed, $request_context);
        }
        
        return $transformed;
    }
    
    /**
     * ⚖️ Intelligent Load Balancing
     */
    private function selectTargetService($route_info) {
        $service_name = $route_info['service'];
        $available_instances = $this->getHealthyServiceInstances($service_name);
        
        if (empty($available_instances)) {
            throw new ServiceUnavailableException("No healthy instances available for service: {$service_name}");
        }
        
        // Load balancing strategies
        $strategy = $route_info['load_balancing'] ?? 'weighted_round_robin';
        
        switch ($strategy) {
            case 'round_robin':
                return $this->selectRoundRobin($available_instances);
            case 'weighted_round_robin':
                return $this->selectWeightedRoundRobin($available_instances);
            case 'least_connections':
                return $this->selectLeastConnections($available_instances);
            case 'least_response_time':
                return $this->selectLeastResponseTime($available_instances);
            case 'ip_hash':
                return $this->selectIPHash($available_instances);
            default:
                return $this->selectWeightedRoundRobin($available_instances);
        }
    }
    
    /**
     * 🔌 Circuit Breaker Pattern
     */
    private function checkCircuitBreaker($target_service) {
        $service_key = $target_service['id'];
        $circuit_state = $this->cache->get("circuit_breaker:{$service_key}");
        
        if (!$circuit_state) {
            $circuit_state = [
                'state' => 'closed',
                'failure_count' => 0,
                'last_failure' => null,
                'next_attempt' => null
            ];
        }
        
        switch ($circuit_state['state']) {
            case 'open':
                if (time() >= $circuit_state['next_attempt']) {
                    // Transition to half-open
                    $circuit_state['state'] = 'half-open';
                    $this->cache->set("circuit_breaker:{$service_key}", $circuit_state, 300);
                } else {
                    throw new ServiceUnavailableException("Circuit breaker is open for service: {$service_key}");
                }
                break;
                
            case 'half-open':
                // Allow one request through
                break;
                
            case 'closed':
            default:
                // Normal operation
                break;
        }
    }
    
    /**
     * 🎯 Service Request Execution
     */
    private function executeServiceRequest($target_service, $transformed_request) {
        $start_time = microtime(true);
        
        try {
            // Build target URL
            $target_url = $this->buildTargetURL($target_service, $transformed_request);
            
            // Prepare request options
            $options = $this->prepareRequestOptions($transformed_request, $target_service);
            
            // Execute HTTP request
            $response = $this->executeHTTPRequest($target_url, $options);
            
            // Update circuit breaker on success
            $this->updateCircuitBreakerSuccess($target_service['id']);
            
            // Update service metrics
            $this->updateServiceMetrics($target_service, microtime(true) - $start_time, true);
            
            return $response;
            
        } catch (Exception $e) {
            // Update circuit breaker on failure
            $this->updateCircuitBreakerFailure($target_service['id']);
            
            // Update service metrics
            $this->updateServiceMetrics($target_service, microtime(true) - $start_time, false);
            
            throw $e;
        }
    }
    
    /**
     * 📈 Real-time Analytics & Monitoring
     */
    private function recordAnalytics($request_context, $response, $start_time) {
        $duration = (microtime(true) - $start_time) * 1000; // Convert to milliseconds
        
        $analytics_data = [
            'request_id' => $request_context['request_id'],
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request_context['method'],
            'path' => $request_context['path'],
            'endpoint' => $request_context['route']['endpoint'] ?? 'unknown',
            'service' => $request_context['route']['service'] ?? 'unknown',
            'user_id' => $request_context['auth']['user_id'] ?? 'anonymous',
            'ip_address' => $this->getClientIP(),
            'user_agent' => $request_context['headers']['user-agent'] ?? '',
            'status_code' => $response['status_code'],
            'response_time' => $duration,
            'request_size' => strlen(json_encode($request_context['body'])),
            'response_size' => strlen(json_encode($response['body'])),
            'cache_hit' => $response['cache_hit'] ?? false,
            'api_version' => $request_context['version'] ?? 'v3'
        ];
        
        // Store analytics data
        $this->analytics_engine->record($analytics_data);
        
        // Update real-time metrics
        $this->updateRealTimeMetrics($analytics_data);
        
        // Check for anomalies
        $this->checkForAnomalies($analytics_data);
    }
    
    /**
     * 💾 Multi-tier Caching Strategy
     */
    private function applyCachingStrategy($request_context, $response) {
        // Skip caching for non-GET requests or error responses
        if ($request_context['method'] !== 'GET' || $response['status_code'] >= 400) {
            return;
        }
        
        $cache_config = $request_context['route']['cache'] ?? [];
        if (!$cache_config['enabled'] ?? false) {
            return;
        }
        
        $cache_key = $this->generateCacheKey($request_context);
        $ttl = $cache_config['ttl'] ?? 300; // Default 5 minutes
        
        // Apply cache tags for invalidation
        $tags = $this->generateCacheTags($request_context);
        
        // Store in multiple cache layers
        $this->storeInCacheLayer('memory', $cache_key, $response, min($ttl, 60), $tags);
        $this->storeInCacheLayer('redis', $cache_key, $response, $ttl, $tags);
        
        if ($ttl > 3600) { // For long-term caching
            $this->storeInCacheLayer('file', $cache_key, $response, $ttl, $tags);
        }
    }
    
    /**
     * 🚨 Error Handling & Recovery
     */
    private function handleError($exception, $request_context, $start_time) {
        $error_id = uniqid('err_');
        $duration = $request_context ? (microtime(true) - $start_time) * 1000 : 0;
        
        // Log error details
        $this->logger->error("API Gateway Error [{$error_id}]", [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'request_context' => $request_context,
            'duration' => $duration
        ]);
        
        // Record error analytics
        if ($request_context) {
            $this->recordErrorAnalytics($request_context, $exception, $duration);
        }
        
        // Determine error response
        $status_code = $this->getHTTPStatusCode($exception);
        $error_response = $this->formatErrorResponse($exception, $error_id, $request_context);
        
        return [
            'status_code' => $status_code,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Error-ID' => $error_id,
                'X-Gateway-Version' => $this->gateway_config['version']
            ],
            'body' => $error_response
        ];
    }
    
    /**
     * 🏥 Service Health Monitoring
     */
    private function setupHealthMonitoring() {
        // Register health check endpoints
        $this->registerHealthChecks();
        
        // Start background health monitoring
        $this->startHealthMonitoring();
    }
    
    private function performHealthCheck($service) {
        try {
            $start_time = microtime(true);
            
            // Execute health check request
            $response = $this->executeHTTPRequest($service['health_check_url'], [
                'method' => 'GET',
                'timeout' => $this->health_config['timeout'],
                'headers' => ['User-Agent' => 'MezBjen-Gateway-HealthCheck/3.0']
            ]);
            
            $response_time = (microtime(true) - $start_time) * 1000;
            
            $is_healthy = $response['status_code'] >= 200 && $response['status_code'] < 300;
            
            // Update service health status
            $this->updateServiceHealth($service['id'], $is_healthy, $response_time);
            
            return $is_healthy;
            
        } catch (Exception $e) {
            $this->updateServiceHealth($service['id'], false, null, $e->getMessage());
            return false;
        }
    }
    
    /**
     * 📚 Developer Portal Integration
     */
    public function getDeveloperPortalData() {
        return [
            'api_documentation' => $this->generateAPIDocumentation(),
            'interactive_explorer' => $this->getInteractiveExplorer(),
            'sdk_downloads' => $this->getSDKDownloads(),
            'rate_limits' => $this->getRateLimitDocumentation(),
            'authentication_guide' => $this->getAuthenticationGuide(),
            'code_examples' => $this->getCodeExamples(),
            'change_log' => $this->getChangeLog(),
            'status_page' => $this->getStatusPage()
        ];
    }
    
    /**
     * 🔧 Gateway Administration
     */
    public function getGatewayMetrics() {
        return [
            'performance' => $this->getPerformanceMetrics(),
            'security' => $this->getSecurityMetrics(),
            'availability' => $this->getAvailabilityMetrics(),
            'usage' => $this->getUsageMetrics(),
            'services' => $this->getServiceMetrics(),
            'errors' => $this->getErrorMetrics()
        ];
    }
    
    public function updateGatewayConfiguration($new_config) {
        $this->validateGatewayConfig($new_config);
        
        // Backup current configuration
        $this->backupConfiguration();
        
        // Apply new configuration
        $this->applyConfiguration($new_config);
        
        // Validate configuration
        $this->validateConfigurationIntegrity();
        
        $this->logger->info('Gateway configuration updated successfully');
        
        return ['status' => 'success', 'message' => 'Configuration updated'];
    }
    
    /**
     * 🔄 Service Registry Management
     */
    public function registerService($service_config) {
        $service_id = $service_config['id'];
        
        // Validate service configuration
        $this->validateServiceConfig($service_config);
        
        // Register service
        $this->service_registry[$service_id] = $service_config;
        
        // Store in database
        $this->storeServiceRegistration($service_config);
        
        // Initialize health monitoring
        $this->initializeServiceHealthMonitoring($service_id);
        
        $this->logger->info("Service registered: {$service_id}");
        
        return ['status' => 'success', 'service_id' => $service_id];
    }
    
    public function deregisterService($service_id) {
        if (isset($this->service_registry[$service_id])) {
            unset($this->service_registry[$service_id]);
            $this->removeServiceRegistration($service_id);
            $this->stopServiceHealthMonitoring($service_id);
            
            $this->logger->info("Service deregistered: {$service_id}");
            
            return ['status' => 'success'];
        }
        
        throw new NotFoundException("Service not found: {$service_id}");
    }
    
    /**
     * 🌐 WebSocket Support for Real-time APIs
     */
    public function handleWebSocketConnection($connection, $request) {
        // Authenticate WebSocket connection
        $auth = $this->authenticateWebSocketConnection($connection, $request);
        
        // Register connection
        $connection_id = $this->registerWebSocketConnection($connection, $auth);
        
        // Set up event handlers
        $this->setupWebSocketEventHandlers($connection, $connection_id);
        
        return $connection_id;
    }
    
    /**
     * 📊 Real-time Dashboard Data
     */
    public function getRealtimeDashboardData() {
        return [
            'gateway_status' => $this->getGatewayStatus(),
            'active_connections' => $this->getActiveConnections(),
            'request_metrics' => $this->getRequestMetrics(),
            'service_health' => $this->getServiceHealthSummary(),
            'rate_limit_status' => $this->getRateLimitStatus(),
            'error_rates' => $this->getErrorRates(),
            'performance_metrics' => $this->getPerformanceMetrics(),
            'security_alerts' => $this->getSecurityAlerts()
        ];
    }
    
    // Utility Methods
    private function generateRequestId() {
        return 'req_' . uniqid() . '_' . bin2hex(random_bytes(8));
    }
    
    private function createRequestContext($request_id, $method, $path, $headers, $body, $query) {
        return [
            'request_id' => $request_id,
            'timestamp' => microtime(true),
            'method' => strtoupper($method),
            'path' => $path,
            'headers' => array_change_key_case($headers, CASE_LOWER),
            'body' => $body,
            'query' => $query,
            'version' => $this->extractAPIVersion($path),
            'client_ip' => $this->getClientIP()
        ];
    }
    
    private function getClientIP() {
        $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }
        
        return '127.0.0.1';
    }
    
    private function extractAPIVersion($path) {
        if (preg_match('/\/v(\d+)\//', $path, $matches)) {
            return 'v' . $matches[1];
        }
        return $this->gateway_config['default_version'];
    }
}

/**
 * 🔐 Gateway Security Manager
 */
class GatewaySecurityManager {
    private $config;
    private $jwt_secret;
    
    public function __construct($config) {
        $this->config = $config;
        $this->jwt_secret = $config['jwt_secret'] ?? 'default_secret_change_me';
    }
    
    public function validateJWT($token) {
        // JWT validation implementation
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new InvalidArgumentException('Invalid JWT format');
        }
        
        $header = json_decode(base64_decode($parts[0]), true);
        $payload = json_decode(base64_decode($parts[1]), true);
        $signature = $parts[2];
        
        // Verify signature
        $expected_signature = base64_encode(hash_hmac('sha256', $parts[0] . '.' . $parts[1], $this->jwt_secret, true));
        
        if (!hash_equals($expected_signature, $signature)) {
            throw new UnauthorizedException('Invalid JWT signature');
        }
        
        return $payload;
    }
    
    public function validateRequest($request_context) {
        // Request validation implementation
        $this->validateContentType($request_context);
        $this->validateRequestSize($request_context);
        $this->checkForMaliciousContent($request_context);
    }
    
    private function validateContentType($request_context) {
        $content_type = $request_context['headers']['content-type'] ?? '';
        $allowed_types = ['application/json', 'application/x-www-form-urlencoded', 'multipart/form-data'];
        
        if ($request_context['method'] !== 'GET' && !empty($request_context['body'])) {
            $is_valid = false;
            foreach ($allowed_types as $type) {
                if (strpos($content_type, $type) !== false) {
                    $is_valid = true;
                    break;
                }
            }
            
            if (!$is_valid) {
                throw new BadRequestException('Unsupported content type');
            }
        }
    }
    
    private function validateRequestSize($request_context) {
        $max_size = $this->parseSize($this->config['max_request_size'] ?? '10MB');
        $request_size = strlen(json_encode($request_context['body']));
        
        if ($request_size > $max_size) {
            throw new PayloadTooLargeException('Request size exceeds limit');
        }
    }
    
    private function parseSize($size_string) {
        $units = ['B' => 1, 'KB' => 1024, 'MB' => 1024*1024, 'GB' => 1024*1024*1024];
        
        if (preg_match('/^(\d+)([A-Z]{1,2})$/', strtoupper($size_string), $matches)) {
            return $matches[1] * $units[$matches[2]];
        }
        
        return (int)$size_string;
    }
}

/**
 * 📊 Gateway Rate Limiter
 */
class GatewayRateLimiter {
    private $db;
    private $cache;
    
    public function __construct($db, $cache) {
        $this->db = $db;
        $this->cache = $cache;
    }
    
    public function checkUserRateLimit($user_id, $limits) {
        $key = "rate_limit:user:{$user_id}";
        $window = $limits['user']['window'] ?? 3600;
        $limit = $limits['user']['requests'] ?? 1000;
        
        $current = $this->getSlidingWindowCount($key, $window);
        
        if ($current >= $limit) {
            return [
                'allowed' => false,
                'message' => 'User rate limit exceeded',
                'retry_after' => $this->calculateRetryAfter($key, $window),
                'limit' => $limit,
                'remaining' => 0
            ];
        }
        
        return [
            'allowed' => true,
            'limit' => $limit,
            'remaining' => $limit - $current
        ];
    }
    
    private function getSlidingWindowCount($key, $window) {
        $now = time();
        $window_start = $now - $window;
        
        // Get timestamps from cache
        $timestamps = $this->cache->get($key) ?? [];
        
        // Filter valid timestamps
        $valid_timestamps = array_filter($timestamps, function($ts) use ($window_start) {
            return $ts > $window_start;
        });
        
        return count($valid_timestamps);
    }
    
    private function calculateRetryAfter($key, $window) {
        $timestamps = $this->cache->get($key) ?? [];
        if (empty($timestamps)) {
            return 1;
        }
        
        $oldest_in_window = min($timestamps);
        return max(1, $oldest_in_window + $window - time());
    }
}

// Custom Exception Classes
class UnauthorizedException extends Exception {}
class RateLimitException extends Exception {
    private $retry_after;
    private $limit;
    private $remaining;
    
    public function __construct($message, $retry_after, $limit, $remaining) {
        parent::__construct($message);
        $this->retry_after = $retry_after;
        $this->limit = $limit;
        $this->remaining = $remaining;
    }
    
    public function getRetryAfter() { return $this->retry_after; }
    public function getLimit() { return $this->limit; }
    public function getRemaining() { return $this->remaining; }
}
class ServiceUnavailableException extends Exception {}
class BadRequestException extends Exception {}
class PayloadTooLargeException extends Exception {}
class NotFoundException extends Exception {}

// Additional stub classes for completeness
class ApiGatewayLogger {
    public function __construct($config) {}
    public function info($message, $context = []) {}
    public function error($message, $context = []) {}
}

class GatewayAnalyticsEngine {
    public function __construct($db, $cache) {}
    public function record($data) {}
}

class RequestTransformationEngine {
    public function __construct() {}
}

class IntelligentLoadBalancer {
    public function __construct($config) {}
}

class DeveloperPortal {
    public function __construct($db, $config) {}
}

return CrossPlatformApiGateway::class;
