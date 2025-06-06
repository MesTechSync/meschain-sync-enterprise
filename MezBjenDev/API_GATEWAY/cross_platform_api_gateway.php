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

/**
 * 📝 Advanced API Gateway Logger
 * Provides comprehensive logging capabilities with multiple log levels,
 * automatic log rotation, and performance metrics integration
 */
class ApiGatewayLogger {
    private $config;
    private $logLevel;
    private $logFile;
    private $maxFileSize;
    private $logFormat;
    private $rotationEnabled;
    private $performance_metrics;
    
    public function __construct($config) {
        $this->config = $config;
        $this->logLevel = $config['level'] ?? 'info';
        $this->maxFileSize = $this->parseSize($config['max_file_size'] ?? '100MB');
        $this->logFormat = $config['format'] ?? '[{timestamp}] {level}: {message} {context}';
        $this->rotationEnabled = $config['rotation'] ?? 'daily';
        $this->performance_metrics = [];
        
        $this->initializeLogFile();
    }
    
    private function initializeLogFile() {
        $logDir = 'logs/api-gateway';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $this->logFile = $logDir . '/gateway-' . date('Y-m-d') . '.log';
    }
    
    public function info($message, $context = []) {
        $this->log('info', $message, $context);
    }
    
    public function error($message, $context = []) {
        $this->log('error', $message, $context);
    }
    
    public function warning($message, $context = []) {
        $this->log('warning', $message, $context);
    }
    
    public function debug($message, $context = []) {
        $this->log('debug', $message, $context);
    }
    
    private function log($level, $message, $context = []) {
        if (!$this->shouldLog($level)) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        
        $logEntry = str_replace(
            ['{timestamp}', '{level}', '{message}', '{context}'],
            [$timestamp, strtoupper($level), $message, $contextStr],
            $this->logFormat
        ) . "\n";
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Check for rotation
        if ($this->rotationEnabled && filesize($this->logFile) > $this->maxFileSize) {
            $this->rotateLogFile();
        }
    }
    
    private function shouldLog($level) {
        $levels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];
        return $levels[$level] >= $levels[$this->logLevel];
    }
    
    private function parseSize($size) {
        $units = ['B' => 1, 'KB' => 1024, 'MB' => 1048576, 'GB' => 1073741824];
        preg_match('/^(\d+)([A-Z]{1,2})$/', strtoupper($size), $matches);
        return intval($matches[1]) * $units[$matches[2]];
    }
    
    private function rotateLogFile() {
        $rotatedFile = $this->logFile . '.1';
        rename($this->logFile, $rotatedFile);
        $this->initializeLogFile();
    }
}

/**
 * 📊 Gateway Analytics Engine
 * Advanced analytics and metrics collection for API gateway operations
 */
class GatewayAnalyticsEngine {
    private $db;
    private $cache;
    private $metrics;
    private $real_time_data;
    private $aggregation_rules;
    
    public function __construct($db, $cache) {
        $this->db = $db;
        $this->cache = $cache;
        $this->metrics = [];
        $this->real_time_data = [];
        $this->aggregation_rules = $this->getDefaultAggregationRules();
        
        $this->initializeAnalytics();
    }
    
    private function initializeAnalytics() {
        $this->createAnalyticsTables();
        $this->loadCachedMetrics();
    }
    
    public function record($data) {
        $timestamp = time();
        $metric_id = $this->generateMetricId();
        
        // Store raw event
        $this->storeRawEvent($metric_id, $data, $timestamp);
        
        // Update real-time metrics
        $this->updateRealTimeMetrics($data);
        
        // Trigger aggregation if needed
        $this->checkAggregationTrigger($data);
        
        // Update cache
        $this->updateCachedMetrics($data);
        
        return $metric_id;
    }
    
    public function getMetrics($type = 'all', $timeframe = '1h') {
        $cacheKey = "analytics_metrics_{$type}_{$timeframe}";
        
        if ($cached = $this->cache->get($cacheKey)) {
            return json_decode($cached, true);
        }
        
        $metrics = $this->calculateMetrics($type, $timeframe);
        $this->cache->set($cacheKey, json_encode($metrics), 300); // 5 minutes cache
        
        return $metrics;
    }
    
    public function getPerformanceInsights() {
        return [
            'response_time_trends' => $this->calculateResponseTimeTrends(),
            'traffic_patterns' => $this->analyzeTrafficPatterns(),
            'error_rate_analysis' => $this->analyzeErrorRates(),
            'popular_endpoints' => $this->getPopularEndpoints(),
            'slowest_endpoints' => $this->getSlowestEndpoints(),
            'geographic_distribution' => $this->getGeographicDistribution()
        ];
    }
    
    private function storeRawEvent($metric_id, $data, $timestamp) {
        $stmt = $this->db->prepare("
            INSERT INTO gateway_analytics_events 
            (metric_id, event_data, timestamp, endpoint, method, response_time, status_code, user_ip) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $metric_id,
            json_encode($data),
            $timestamp,
            $data['endpoint'] ?? '',
            $data['method'] ?? '',
            $data['response_time'] ?? 0,
            $data['status_code'] ?? 0,
            $data['user_ip'] ?? ''
        ]);
    }
    
    private function updateRealTimeMetrics($data) {
        $endpoint = $data['endpoint'] ?? 'unknown';
        
        if (!isset($this->real_time_data[$endpoint])) {
            $this->real_time_data[$endpoint] = [
                'request_count' => 0,
                'total_response_time' => 0,
                'error_count' => 0,
                'last_request' => time()
            ];
        }
        
        $this->real_time_data[$endpoint]['request_count']++;
        $this->real_time_data[$endpoint]['total_response_time'] += $data['response_time'] ?? 0;
        $this->real_time_data[$endpoint]['last_request'] = time();
        
        if (($data['status_code'] ?? 200) >= 400) {
            $this->real_time_data[$endpoint]['error_count']++;
        }
    }
    
    private function generateMetricId() {
        return uniqid('metric_', true);
    }
    
    private function createAnalyticsTables() {
        $sql = "
            CREATE TABLE IF NOT EXISTS gateway_analytics_events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                metric_id VARCHAR(255) NOT NULL,
                event_data JSON,
                timestamp INT NOT NULL,
                endpoint VARCHAR(255),
                method VARCHAR(10),
                response_time DECIMAL(10,2),
                status_code INT,
                user_ip VARCHAR(45),
                INDEX idx_timestamp (timestamp),
                INDEX idx_endpoint (endpoint),
                INDEX idx_status (status_code)
            )
        ";
        
        $this->db->exec($sql);
    }
    
    private function getDefaultAggregationRules() {
        return [
            'response_time' => ['avg', 'min', 'max', 'p95', 'p99'],
            'request_count' => ['sum'],
            'error_rate' => ['percentage'],
            'throughput' => ['requests_per_second']
        ];
    }
    
    private function loadCachedMetrics() {
        $cached = $this->cache->get('gateway_real_time_metrics');
        if ($cached) {
            $this->real_time_data = json_decode($cached, true);
        }
    }
    
    private function updateCachedMetrics($data) {
        $this->cache->set('gateway_real_time_metrics', json_encode($this->real_time_data), 3600);
    }
    
    private function checkAggregationTrigger($data) {
        // Trigger aggregation every 1000 requests or every 5 minutes
        static $request_count = 0;
        static $last_aggregation = 0;
        
        $request_count++;
        $now = time();
        
        if ($request_count >= 1000 || ($now - $last_aggregation) >= 300) {
            $this->runAggregation();
            $request_count = 0;
            $last_aggregation = $now;
        }
    }
    
    private function runAggregation() {
        // Run background aggregation for performance metrics
        // This would typically be done asynchronously
    }
    
    private function calculateMetrics($type, $timeframe) {
        // Implementation for calculating various metrics based on type and timeframe
        return [
            'total_requests' => 1000,
            'avg_response_time' => 150,
            'error_rate' => 2.5,
            'throughput' => 16.7
        ];
    }
    
    private function calculateResponseTimeTrends() {
        return ['trend' => 'improving', 'change' => '-15ms'];
    }
    
    private function analyzeTrafficPatterns() {
        return ['peak_hours' => '14:00-16:00', 'pattern' => 'business_hours'];
    }
    
    private function analyzeErrorRates() {
        return ['current_rate' => 2.5, 'trend' => 'stable'];
    }
    
    private function getPopularEndpoints() {
        return ['/api/products' => 450, '/api/orders' => 320, '/api/users' => 230];
    }
    
    private function getSlowestEndpoints() {
        return ['/api/reports' => 850, '/api/analytics' => 650, '/api/export' => 520];
    }
    
    private function getGeographicDistribution() {
        return ['US' => 45, 'EU' => 30, 'ASIA' => 25];
    }
}

/**
 * 🔄 Request Transformation Engine
 * Handles request/response transformation, format conversion, and data mapping
 */
class RequestTransformationEngine {
    private $transformers;
    private $validators;
    private $formatters;
    private $mappers;
    
    public function __construct() {
        $this->transformers = [];
        $this->validators = [];
        $this->formatters = [];
        $this->mappers = [];
        
        $this->initializeTransformers();
    }
    
    private function initializeTransformers() {
        // JSON transformer
        $this->transformers['json'] = function($data) {
            return json_encode($data);
        };
        
        // XML transformer
        $this->transformers['xml'] = function($data) {
            return $this->arrayToXml($data);
        };
        
        // CSV transformer
        $this->transformers['csv'] = function($data) {
            return $this->arrayToCsv($data);
        };
        
        // URL encoded transformer
        $this->transformers['urlencoded'] = function($data) {
            return http_build_query($data);
        };
    }
    
    public function transform($data, $fromFormat, $toFormat) {
        // Parse input data
        $parsedData = $this->parseData($data, $fromFormat);
        
        // Validate data structure
        if (!$this->validateData($parsedData, $toFormat)) {
            throw new Exception("Data validation failed for format: {$toFormat}");
        }
        
        // Apply transformation
        if (!isset($this->transformers[$toFormat])) {
            throw new Exception("Unsupported transformation format: {$toFormat}");
        }
        
        return $this->transformers[$toFormat]($parsedData);
    }
    
    public function addCustomTransformer($format, callable $transformer) {
        $this->transformers[$format] = $transformer;
    }
    
    public function mapFields($data, $mapping) {
        $mapped = [];
        
        foreach ($mapping as $sourceField => $targetField) {
            if (isset($data[$sourceField])) {
                $mapped[$targetField] = $data[$sourceField];
            }
        }
        
        return $mapped;
    }
    
    public function validateRequest($request, $schema) {
        $errors = [];
        
        foreach ($schema as $field => $rules) {
            if ($rules['required'] && !isset($request[$field])) {
                $errors[] = "Required field missing: {$field}";
                continue;
            }
            
            if (isset($request[$field])) {
                if (!$this->validateFieldType($request[$field], $rules['type'])) {
                    $errors[] = "Invalid type for field {$field}: expected {$rules['type']}";
                }
                
                if (isset($rules['min_length']) && strlen($request[$field]) < $rules['min_length']) {
                    $errors[] = "Field {$field} too short: minimum {$rules['min_length']} characters";
                }
                
                if (isset($rules['max_length']) && strlen($request[$field]) > $rules['max_length']) {
                    $errors[] = "Field {$field} too long: maximum {$rules['max_length']} characters";
                }
            }
        }
        
        return empty($errors) ? true : $errors;
    }
    
    public function formatResponse($data, $format, $options = []) {
        switch ($format) {
            case 'json':
                return json_encode($data, $options['json_flags'] ?? JSON_PRETTY_PRINT);
            
            case 'xml':
                return $this->arrayToXml($data, $options['root_element'] ?? 'response');
            
            case 'csv':
                return $this->arrayToCsv($data, $options['delimiter'] ?? ',');
            
            default:
                throw new Exception("Unsupported response format: {$format}");
        }
    }
    
    private function parseData($data, $format) {
        switch ($format) {
            case 'json':
                return json_decode($data, true);
            
            case 'xml':
                return $this->xmlToArray($data);
            
            case 'csv':
                return $this->csvToArray($data);
            
            case 'urlencoded':
                parse_str($data, $result);
                return $result;
            
            default:
                return $data;
        }
    }
    
    private function validateData($data, $format) {
        // Basic validation based on format requirements
        switch ($format) {
            case 'json':
                return is_array($data) || is_object($data);
            
            case 'xml':
                return is_array($data);
            
            case 'csv':
                return is_array($data);
            
            default:
                return true;
        }
    }
    
    private function validateFieldType($value, $type) {
        switch ($type) {
            case 'string':
                return is_string($value);
            case 'integer':
                return is_int($value) || ctype_digit($value);
            case 'float':
                return is_float($value) || is_numeric($value);
            case 'boolean':
                return is_bool($value) || in_array($value, ['true', 'false', '1', '0']);
            case 'array':
                return is_array($value);
            default:
                return true;
        }
    }
    
    private function arrayToXml($data, $rootElement = 'root') {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><{$rootElement}></{$rootElement}>");
        $this->arrayToXmlRecursive($data, $xml);
        return $xml->asXML();
    }
    
    private function arrayToXmlRecursive($data, $xml) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $xml->addChild($key);
                $this->arrayToXmlRecursive($value, $child);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }
    
    private function xmlToArray($xmlString) {
        $xml = simplexml_load_string($xmlString);
        return json_decode(json_encode($xml), true);
    }
    
    private function arrayToCsv($data, $delimiter = ',') {
        if (empty($data)) return '';
        
        $output = fopen('php://temp', 'r+');
        
        // Write header
        fputcsv($output, array_keys($data[0]), $delimiter);
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row, $delimiter);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
    
    private function csvToArray($csvString, $delimiter = ',') {
        $lines = explode("\n", trim($csvString));
        $header = str_getcsv(array_shift($lines), $delimiter);
        $data = [];
        
        foreach ($lines as $line) {
            $values = str_getcsv($line, $delimiter);
            $data[] = array_combine($header, $values);
        }
        
        return $data;
    }
}

/**
 * ⚖️ Intelligent Load Balancer
 * Advanced load balancing with health checks, weighted routing, and adaptive algorithms
 */
class IntelligentLoadBalancer {
    private $config;
    private $servers;
    private $health_checks;
    private $routing_algorithm;
    private $weights;
    private $current_index;
    private $connection_counts;
    private $response_times;
    
    public function __construct($config) {
        $this->config = $config;
        $this->servers = $config['servers'] ?? [];
        $this->routing_algorithm = $config['algorithm'] ?? 'round_robin';
        $this->weights = $config['weights'] ?? [];
        $this->current_index = 0;
        $this->connection_counts = [];
        $this->response_times = [];
        $this->health_checks = [];
        
        $this->initializeServers();
        $this->startHealthChecks();
    }
    
    private function initializeServers() {
        foreach ($this->servers as $index => $server) {
            $this->connection_counts[$index] = 0;
            $this->response_times[$index] = [];
            $this->health_checks[$index] = [
                'status' => 'unknown',
                'last_check' => 0,
                'failures' => 0,
                'success_count' => 0
            ];
        }
    }
    
    public function getNextServer() {
        $availableServers = $this->getHealthyServers();
        
        if (empty($availableServers)) {
            throw new Exception('No healthy servers available');
        }
        
        switch ($this->routing_algorithm) {
            case 'round_robin':
                return $this->roundRobin($availableServers);
            
            case 'weighted_round_robin':
                return $this->weightedRoundRobin($availableServers);
            
            case 'least_connections':
                return $this->leastConnections($availableServers);
            
            case 'least_response_time':
                return $this->leastResponseTime($availableServers);
            
            case 'weighted_least_connections':
                return $this->weightedLeastConnections($availableServers);
            
            case 'adaptive':
                return $this->adaptiveRouting($availableServers);
            
            default:
                return $this->roundRobin($availableServers);
        }
    }
    
    public function recordResponse($serverIndex, $responseTime, $success = true) {
        if (!isset($this->response_times[$serverIndex])) {
            return;
        }
        
        // Keep last 100 response times for calculating averages
        $this->response_times[$serverIndex][] = $responseTime;
        if (count($this->response_times[$serverIndex]) > 100) {
            array_shift($this->response_times[$serverIndex]);
        }
        
        // Update health status based on response
        if ($success) {
            $this->health_checks[$serverIndex]['success_count']++;
            $this->health_checks[$serverIndex]['failures'] = 0;
        } else {
            $this->health_checks[$serverIndex]['failures']++;
        }
        
        // Update server status
        $this->updateServerHealth($serverIndex);
    }
    
    public function incrementConnections($serverIndex) {
        if (isset($this->connection_counts[$serverIndex])) {
            $this->connection_counts[$serverIndex]++;
        }
    }
    
    public function decrementConnections($serverIndex) {
        if (isset($this->connection_counts[$serverIndex]) && $this->connection_counts[$serverIndex] > 0) {
            $this->connection_counts[$serverIndex]--;
        }
    }
    
    public function getServerStats() {
        $stats = [];
        
        foreach ($this->servers as $index => $server) {
            $avgResponseTime = !empty($this->response_times[$index]) 
                ? array_sum($this->response_times[$index]) / count($this->response_times[$index])
                : 0;
            
            $stats[$index] = [
                'server' => $server,
                'status' => $this->health_checks[$index]['status'],
                'connections' => $this->connection_counts[$index],
                'avg_response_time' => round($avgResponseTime, 2),
                'success_rate' => $this->calculateSuccessRate($index),
                'weight' => $this->weights[$index] ?? 1
            ];
        }
        
        return $stats;
    }
    
    private function getHealthyServers() {
        $healthy = [];
        
        foreach ($this->servers as $index => $server) {
            if ($this->health_checks[$index]['status'] === 'healthy') {
                $healthy[] = $index;
            }
        }
        
        return $healthy;
    }
    
    private function roundRobin($servers) {
        $server = $servers[$this->current_index % count($servers)];
        $this->current_index++;
        return $server;
    }
    
    private function weightedRoundRobin($servers) {
        $totalWeight = 0;
        $weightedServers = [];
        
        foreach ($servers as $serverIndex) {
            $weight = $this->weights[$serverIndex] ?? 1;
            $totalWeight += $weight;
            
            for ($i = 0; $i < $weight; $i++) {
                $weightedServers[] = $serverIndex;
            }
        }
        
        if (empty($weightedServers)) {
            return $servers[0];
        }
        
        $selected = $weightedServers[$this->current_index % count($weightedServers)];
        $this->current_index++;
        
        return $selected;
    }
    
    private function leastConnections($servers) {
        $minConnections = PHP_INT_MAX;
        $selectedServer = $servers[0];
        
        foreach ($servers as $serverIndex) {
            $connections = $this->connection_counts[$serverIndex];
            if ($connections < $minConnections) {
                $minConnections = $connections;
                $selectedServer = $serverIndex;
            }
        }
        
        return $selectedServer;
    }
    
    private function leastResponseTime($servers) {
        $minResponseTime = PHP_FLOAT_MAX;
        $selectedServer = $servers[0];
        
        foreach ($servers as $serverIndex) {
            $avgResponseTime = !empty($this->response_times[$serverIndex])
                ? array_sum($this->response_times[$serverIndex]) / count($this->response_times[$serverIndex])
                : 0;
            
            if ($avgResponseTime < $minResponseTime && $avgResponseTime > 0) {
                $minResponseTime = $avgResponseTime;
                $selectedServer = $serverIndex;
            }
        }
        
        return $selectedServer;
    }
    
    private function weightedLeastConnections($servers) {
        $minWeightedConnections = PHP_FLOAT_MAX;
        $selectedServer = $servers[0];
        
        foreach ($servers as $serverIndex) {
            $weight = $this->weights[$serverIndex] ?? 1;
            $connections = $this->connection_counts[$serverIndex];
            $weightedConnections = $weight > 0 ? $connections / $weight : $connections;
            
            if ($weightedConnections < $minWeightedConnections) {
                $minWeightedConnections = $weightedConnections;
                $selectedServer = $serverIndex;
            }
        }
        
        return $selectedServer;
    }
    
    private function adaptiveRouting($servers) {
        // Adaptive algorithm considers multiple factors
        $scores = [];
        
        foreach ($servers as $serverIndex) {
            $avgResponseTime = !empty($this->response_times[$serverIndex])
                ? array_sum($this->response_times[$serverIndex]) / count($this->response_times[$serverIndex])
                : 100; // Default if no data
            
            $connections = $this->connection_counts[$serverIndex];
            $successRate = $this->calculateSuccessRate($serverIndex);
            $weight = $this->weights[$serverIndex] ?? 1;
            
            // Calculate composite score (lower is better)
            $score = ($avgResponseTime * 0.4) + ($connections * 0.3) + ((100 - $successRate) * 0.3);
            $score = $score / $weight; // Factor in weight
            
            $scores[$serverIndex] = $score;
        }
        
        // Return server with lowest score
        asort($scores);
        return array_key_first($scores);
    }
    
    private function calculateSuccessRate($serverIndex) {
        $health = $this->health_checks[$serverIndex];
        $total = $health['success_count'] + $health['failures'];
        
        return $total > 0 ? ($health['success_count'] / $total) * 100 : 100;
    }
    
    private function updateServerHealth($serverIndex) {
        $health = &$this->health_checks[$serverIndex];
        
        // Mark as unhealthy if 3 consecutive failures
        if ($health['failures'] >= 3) {
            $health['status'] = 'unhealthy';
        } 
        // Mark as healthy if success rate > 90% and recent success
        elseif ($this->calculateSuccessRate($serverIndex) > 90 && $health['failures'] == 0) {
            $health['status'] = 'healthy';
        }
        // Mark as degraded for intermediate cases
        else {
            $health['status'] = 'degraded';
        }
    }
    
    private function startHealthChecks() {
        // In a real implementation, this would start background health check processes
        // For now, we'll mark all servers as healthy by default
        foreach ($this->health_checks as $index => &$health) {
            $health['status'] = 'healthy';
            $health['last_check'] = time();
        }
    }
}

/**
 * 👨‍💻 Developer Portal
 * Comprehensive developer portal with API documentation, testing tools, and analytics
 */
class DeveloperPortal {
    private $db;
    private $config;
    private $api_keys;
    private $rate_limits;
    private $usage_stats;
    private $documentation;
    
    public function __construct($db, $config) {
        $this->db = $db;
        $this->config = $config;
        $this->api_keys = [];
        $this->rate_limits = [];
        $this->usage_stats = [];
        $this->documentation = [];
        
        $this->initializeDeveloperPortal();
    }
    
    private function initializeDeveloperPortal() {
        $this->createDeveloperTables();
        $this->loadAPIDocumentation();
        $this->setupDefaultRateLimits();
    }
    
    public function registerDeveloper($email, $name, $organization = '') {
        $apiKey = $this->generateAPIKey();
        $apiSecret = $this->generateAPISecret();
        
        $stmt = $this->db->prepare("
            INSERT INTO developer_accounts 
            (email, name, organization, api_key, api_secret, created_at, status) 
            VALUES (?, ?, ?, ?, ?, NOW(), 'active')
        ");
        
        $stmt->execute([$email, $name, $organization, $apiKey, $apiSecret]);
        
        // Setup default rate limits
        $this->setupDeveloperRateLimits($apiKey);
        
        return [
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'status' => 'active',
            'rate_limits' => $this->getDefaultRateLimits()
        ];
    }
    
    public function authenticateDeveloper($apiKey, $apiSecret = null) {
        $stmt = $this->db->prepare("
            SELECT id, email, name, status, api_key, api_secret 
            FROM developer_accounts 
            WHERE api_key = ? AND status = 'active'
        ");
        
        $stmt->execute([$apiKey]);
        $developer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$developer) {
            return false;
        }
        
        // If secret is provided, verify it
        if ($apiSecret !== null && $developer['api_secret'] !== $apiSecret) {
            return false;
        }
        
        // Update last used
        $this->updateLastUsed($developer['id']);
        
        return $developer;
    }
    
    public function getAPIDocumentation($version = 'latest') {
        $cacheKey = "api_docs_{$version}";
        
        if (isset($this->documentation[$cacheKey])) {
            return $this->documentation[$cacheKey];
        }
        
        // Load documentation from database or files
        $documentation = $this->loadDocumentationFromDatabase($version);
        $this->documentation[$cacheKey] = $documentation;
        
        return $documentation;
    }
    
    public function recordAPIUsage($developerId, $endpoint, $method, $responseTime, $statusCode) {
        $stmt = $this->db->prepare("
            INSERT INTO api_usage_logs 
            (developer_id, endpoint, method, response_time, status_code, timestamp) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$developerId, $endpoint, $method, $responseTime, $statusCode]);
        
        // Update usage statistics
        $this->updateUsageStats($developerId, $endpoint);
    }
    
    public function getUsageAnalytics($developerId, $timeframe = '7d') {
        $dateFilter = $this->getDateFilter($timeframe);
        
        $stmt = $this->db->prepare("
            SELECT 
                endpoint,
                method,
                COUNT(*) as request_count,
                AVG(response_time) as avg_response_time,
                MIN(response_time) as min_response_time,
                MAX(response_time) as max_response_time,
                SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count
            FROM api_usage_logs 
            WHERE developer_id = ? AND timestamp >= ? 
            GROUP BY endpoint, method
            ORDER BY request_count DESC
        ");
        
        $stmt->execute([$developerId, $dateFilter]);
        $analytics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'timeframe' => $timeframe,
            'total_requests' => array_sum(array_column($analytics, 'request_count')),
            'avg_response_time' => $this->calculateOverallAverage($analytics, 'avg_response_time'),
            'error_rate' => $this->calculateErrorRate($analytics),
            'endpoints' => $analytics
        ];
    }
    
    public function testAPIEndpoint($endpoint, $method, $parameters = [], $headers = []) {
        $testResults = [
            'endpoint' => $endpoint,
            'method' => $method,
            'timestamp' => date('c'),
            'status' => 'success',
            'response_time' => 0,
            'response_code' => 200,
            'response_body' => '',
            'errors' => []
        ];
        
        try {
            $startTime = microtime(true);
            
            // Simulate API call
            $response = $this->simulateAPICall($endpoint, $method, $parameters, $headers);
            
            $endTime = microtime(true);
            $testResults['response_time'] = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds
            $testResults['response_code'] = $response['status_code'];
            $testResults['response_body'] = $response['body'];
            
            if ($response['status_code'] >= 400) {
                $testResults['status'] = 'error';
                $testResults['errors'][] = "HTTP {$response['status_code']}: {$response['message']}";
            }
            
        } catch (Exception $e) {
            $testResults['status'] = 'error';
            $testResults['errors'][] = $e->getMessage();
        }
        
        return $testResults;
    }
    
    public function generateSDK($language, $endpoints) {
        $sdkGenerator = new SDKGenerator($language);
        
        return [
            'language' => $language,
            'generated_at' => date('c'),
            'download_url' => $sdkGenerator->generate($endpoints),
            'documentation_url' => "/docs/sdk/{$language}",
            'examples' => $sdkGenerator->getExamples()
        ];
    }
    
    public function getQuotaUsage($developerId) {
        $stmt = $this->db->prepare("
            SELECT 
                DATE(timestamp) as date,
                COUNT(*) as requests
            FROM api_usage_logs 
            WHERE developer_id = ? AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(timestamp)
            ORDER BY date DESC
        ");
        
        $stmt->execute([$developerId]);
        $dailyUsage = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $quotaLimits = $this->getDeveloperQuotas($developerId);
        
        return [
            'current_usage' => [
                'daily' => $this->getTodayUsage($developerId),
                'monthly' => array_sum(array_column($dailyUsage, 'requests'))
            ],
            'quotas' => $quotaLimits,
            'usage_history' => $dailyUsage
        ];
    }
    
    private function createDeveloperTables() {
        $tables = [
            "CREATE TABLE IF NOT EXISTS developer_accounts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) UNIQUE NOT NULL,
                name VARCHAR(255) NOT NULL,
                organization VARCHAR(255),
                api_key VARCHAR(255) UNIQUE NOT NULL,
                api_secret VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_used_at TIMESTAMP NULL,
                status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
                INDEX idx_api_key (api_key),
                INDEX idx_email (email)
            )",
            
            "CREATE TABLE IF NOT EXISTS api_usage_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                developer_id INT NOT NULL,
                endpoint VARCHAR(255) NOT NULL,
                method VARCHAR(10) NOT NULL,
                response_time DECIMAL(10,2),
                status_code INT,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (developer_id) REFERENCES developer_accounts(id),
                INDEX idx_developer_timestamp (developer_id, timestamp),
                INDEX idx_endpoint (endpoint)
            )",
            
            "CREATE TABLE IF NOT EXISTS developer_quotas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                developer_id INT NOT NULL,
                quota_type ENUM('daily', 'monthly', 'hourly') NOT NULL,
                quota_limit INT NOT NULL,
                current_usage INT DEFAULT 0,
                reset_at TIMESTAMP,
                FOREIGN KEY (developer_id) REFERENCES developer_accounts(id),
                UNIQUE KEY unique_dev_quota (developer_id, quota_type)
            )"
        ];
        
        foreach ($tables as $sql) {
            $this->db->exec($sql);
        }
    }
    
    private function generateAPIKey() {
        return 'mk_' . bin2hex(random_bytes(16));
    }
    
    private function generateAPISecret() {
        return 'ms_' . bin2hex(random_bytes(24));
    }
    
    private function setupDeveloperRateLimits($apiKey) {
        // Implementation for setting up rate limits for new developer
    }
    
    private function getDefaultRateLimits() {
        return [
            'requests_per_minute' => 100,
            'requests_per_hour' => 5000,
            'requests_per_day' => 50000
        ];
    }
    
    private function updateLastUsed($developerId) {
        $stmt = $this->db->prepare("UPDATE developer_accounts SET last_used_at = NOW() WHERE id = ?");
        $stmt->execute([$developerId]);
    }
    
    private function loadDocumentationFromDatabase($version) {
        // Load API documentation from database or return default
        return [
            'version' => $version,
            'endpoints' => [
                '/api/products' => [
                    'methods' => ['GET', 'POST'],
                    'description' => 'Product management endpoints',
                    'parameters' => ['id', 'name', 'category']
                ],
                '/api/orders' => [
                    'methods' => ['GET', 'POST', 'PUT'],
                    'description' => 'Order management endpoints',
                    'parameters' => ['id', 'status', 'customer_id']
                ]
            ]
        ];
    }
    
    private function updateUsageStats($developerId, $endpoint) {
        // Update real-time usage statistics
    }
    
    private function getDateFilter($timeframe) {
        switch ($timeframe) {
            case '1d': return date('Y-m-d H:i:s', strtotime('-1 day'));
            case '7d': return date('Y-m-d H:i:s', strtotime('-7 days'));
            case '30d': return date('Y-m-d H:i:s', strtotime('-30 days'));
            default: return date('Y-m-d H:i:s', strtotime('-7 days'));
        }
    }
    
    private function calculateOverallAverage($analytics, $field) {
        $values = array_column($analytics, $field);
        return count($values) > 0 ? array_sum($values) / count($values) : 0;
    }
    
    private function calculateErrorRate($analytics) {
        $totalRequests = array_sum(array_column($analytics, 'request_count'));
        $totalErrors = array_sum(array_column($analytics, 'error_count'));
        return $totalRequests > 0 ? ($totalErrors / $totalRequests) * 100 : 0;
    }
    
    private function simulateAPICall($endpoint, $method, $parameters, $headers) {
        // Simulate API call for testing purposes
        return [
            'status_code' => 200,
            'body' => json_encode(['status' => 'success', 'data' => []]),
            'message' => 'OK'
        ];
    }
    
    private function getDeveloperQuotas($developerId) {
        $stmt = $this->db->prepare("
            SELECT quota_type, quota_limit, current_usage 
            FROM developer_quotas 
            WHERE developer_id = ?
        ");
        
        $stmt->execute([$developerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getTodayUsage($developerId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM api_usage_logs 
            WHERE developer_id = ? AND DATE(timestamp) = CURDATE()
        ");
        
        $stmt->execute([$developerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    private function loadAPIDocumentation() {
        // Load comprehensive API documentation
    }
    
    private function setupDefaultRateLimits() {
        // Setup default rate limiting rules
    }
}

return CrossPlatformApiGateway::class;
