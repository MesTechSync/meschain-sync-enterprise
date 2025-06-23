<?php
/**
 * MesChain-Sync API Response Formatter
 * Standardized response formatting for all API endpoints
 * 
 * @version 1.0.0
 * @date June 2, 2025
 * @author MesChain Development Team
 */

class MeschainApiResponseFormatter {
    
    private $response_version = '1.0';
    private $default_headers = [];
    private $performance_tracker;
    
    public function __construct($config = []) {
        $this->performance_tracker = new ApiPerformanceTracker();
        $this->initializeDefaultHeaders();
    }
    
    /**
     * Initialize default headers for all responses
     */
    private function initializeDefaultHeaders() {
        $this->default_headers = [
            'Content-Type' => 'application/json; charset=utf-8',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-API-Version, X-Rate-Limit-Remaining',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-API-Version' => $this->response_version,
            'X-Powered-By' => 'MesChain-Sync API'
        ];
    }
    
    /**
     * Format success response
     */
    public function success($data = null, $message = 'Operation completed successfully', $meta = []) {
        $response = [
            'status' => 'success',
            'code' => 200,
            'message' => $message,
            'data' => $data,
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        return $this->prepareResponse($response, 200);
    }
    
    /**
     * Format error response
     */
    public function error($message, $code = 500, $error_code = null, $details = null, $meta = []) {
        $response = [
            'status' => 'error',
            'code' => $code,
            'message' => $message,
            'error' => [
                'code' => $error_code,
                'details' => $details,
                'type' => $this->getErrorType($code)
            ],
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        return $this->prepareResponse($response, $code);
    }
    
    /**
     * Format validation error response
     */
    public function validationError($validation_errors, $message = 'Validation failed', $meta = []) {
        $response = [
            'status' => 'error',
            'code' => 422,
            'message' => $message,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'type' => 'validation',
                'validation_errors' => $this->formatValidationErrors($validation_errors)
            ],
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        return $this->prepareResponse($response, 422);
    }
    
    /**
     * Format paginated response
     */
    public function paginated($data, $pagination, $message = 'Data retrieved successfully', $meta = []) {
        $response = [
            'status' => 'success',
            'code' => 200,
            'message' => $message,
            'data' => $data,
            'pagination' => $this->formatPagination($pagination),
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        return $this->prepareResponse($response, 200);
    }
    
    /**
     * Format rate limit response
     */
    public function rateLimitExceeded($retry_after = null, $meta = []) {
        $response = [
            'status' => 'error',
            'code' => 429,
            'message' => 'Rate limit exceeded',
            'error' => [
                'code' => 'RATE_LIMIT_EXCEEDED',
                'type' => 'rate_limit',
                'retry_after' => $retry_after
            ],
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        $headers = [];
        if ($retry_after) {
            $headers['Retry-After'] = $retry_after;
        }
        
        return $this->prepareResponse($response, 429, $headers);
    }
    
    /**
     * Format authentication error response
     */
    public function authenticationError($message = 'Authentication required', $meta = []) {
        $response = [
            'status' => 'error',
            'code' => 401,
            'message' => $message,
            'error' => [
                'code' => 'AUTHENTICATION_REQUIRED',
                'type' => 'authentication'
            ],
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        return $this->prepareResponse($response, 401);
    }
    
    /**
     * Format authorization error response
     */
    public function authorizationError($message = 'Insufficient permissions', $meta = []) {
        $response = [
            'status' => 'error',
            'code' => 403,
            'message' => $message,
            'error' => [
                'code' => 'INSUFFICIENT_PERMISSIONS',
                'type' => 'authorization'
            ],
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        return $this->prepareResponse($response, 403);
    }
    
    /**
     * Format not found response
     */
    public function notFound($resource = 'Resource', $meta = []) {
        $response = [
            'status' => 'error',
            'code' => 404,
            'message' => $resource . ' not found',
            'error' => [
                'code' => 'RESOURCE_NOT_FOUND',
                'type' => 'not_found'
            ],
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        return $this->prepareResponse($response, 404);
    }
    
    /**
     * Format maintenance mode response
     */
    public function maintenanceMode($message = 'Service temporarily unavailable', $meta = []) {
        $response = [
            'status' => 'error',
            'code' => 503,
            'message' => $message,
            'error' => [
                'code' => 'SERVICE_UNAVAILABLE',
                'type' => 'maintenance'
            ],
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        return $this->prepareResponse($response, 503);
    }
    
    /**
     * Format marketplace API specific response
     */
    public function marketplaceResponse($marketplace, $operation, $data = null, $status = 'success', $meta = []) {
        $response = [
            'status' => $status,
            'code' => $status === 'success' ? 200 : 500,
            'message' => ucfirst($operation) . ' operation ' . ($status === 'success' ? 'completed' : 'failed') . ' for ' . ucfirst($marketplace),
            'marketplace' => $marketplace,
            'operation' => $operation,
            'data' => $data,
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        return $this->prepareResponse($response, $response['code']);
    }
    
    /**
     * Format dashboard data response
     */
    public function dashboardResponse($dashboard_data, $meta = []) {
        $response = [
            'status' => 'success',
            'code' => 200,
            'message' => 'Dashboard data retrieved successfully',
            'data' => [
                'metrics' => $dashboard_data['metrics'] ?? [],
                'charts' => $dashboard_data['charts'] ?? [],
                'widgets' => $dashboard_data['widgets'] ?? [],
                'real_time' => $dashboard_data['real_time'] ?? []
            ],
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        return $this->prepareResponse($response, 200);
    }
    
    /**
     * Format Chart.js compatible response
     */
    public function chartjsResponse($chart_data, $chart_type = 'line', $meta = []) {
        $response = [
            'status' => 'success',
            'code' => 200,
            'message' => 'Chart data retrieved successfully',
            'data' => [
                'type' => $chart_type,
                'data' => $chart_data,
                'options' => $this->getDefaultChartOptions($chart_type)
            ],
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        return $this->prepareResponse($response, 200);
    }
    
    /**
     * Format real-time update response
     */
    public function realTimeUpdate($event_type, $data, $meta = []) {
        $response = [
            'status' => 'success',
            'code' => 200,
            'message' => 'Real-time update',
            'event' => [
                'type' => $event_type,
                'timestamp' => date('Y-m-d H:i:s'),
                'data' => $data
            ],
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        return $this->prepareResponse($response, 200);
    }
    
    /**
     * Format webhook response
     */
    public function webhookResponse($webhook_data, $status = 'received', $meta = []) {
        $response = [
            'status' => 'success',
            'code' => 200,
            'message' => 'Webhook ' . $status . ' successfully',
            'webhook' => [
                'id' => $webhook_data['id'] ?? null,
                'type' => $webhook_data['type'] ?? 'unknown',
                'status' => $status,
                'processed_at' => date('Y-m-d H:i:s')
            ],
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        return $this->prepareResponse($response, 200);
    }
    
    /**
     * Format API documentation response
     */
    public function apiDocumentation($endpoints, $meta = []) {
        $response = [
            'status' => 'success',
            'code' => 200,
            'message' => 'API documentation',
            'api' => [
                'version' => $this->response_version,
                'endpoints' => $endpoints,
                'authentication' => 'Bearer token required',
                'rate_limits' => [
                    'global' => '1000 requests per hour',
                    'per_endpoint' => '100 requests per minute'
                ]
            ],
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        return $this->prepareResponse($response, 200);
    }
    
    /**
     * Format health check response
     */
    public function healthCheck($health_data, $meta = []) {
        $overall_status = $health_data['overall_status'] ?? 'healthy';
        $code = $overall_status === 'healthy' ? 200 : 503;
        
        $response = [
            'status' => $overall_status === 'healthy' ? 'success' : 'error',
            'code' => $code,
            'message' => 'System health check',
            'health' => $health_data,
            'meta' => $this->formatMeta($meta),
            'version' => $this->response_version
        ];
        
        return $this->prepareResponse($response, $code);
    }
    
    /**
     * Format meta information
     */
    private function formatMeta($meta = []) {
        $default_meta = [
            'timestamp' => date('Y-m-d H:i:s'),
            'timezone' => date_default_timezone_get(),
            'request_id' => $this->generateRequestId(),
            'processing_time' => $this->performance_tracker->getProcessingTime(),
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'server_time' => microtime(true)
        ];
        
        return array_merge($default_meta, $meta);
    }
    
    /**
     * Format pagination information
     */
    private function formatPagination($pagination) {
        return [
            'current_page' => (int)($pagination['page'] ?? 1),
            'per_page' => (int)($pagination['limit'] ?? 20),
            'total_items' => (int)($pagination['total'] ?? 0),
            'total_pages' => (int)ceil(($pagination['total'] ?? 0) / ($pagination['limit'] ?? 20)),
            'has_previous' => ($pagination['page'] ?? 1) > 1,
            'has_next' => ($pagination['page'] ?? 1) < ceil(($pagination['total'] ?? 0) / ($pagination['limit'] ?? 20)),
            'links' => [
                'first' => 1,
                'previous' => max(1, ($pagination['page'] ?? 1) - 1),
                'next' => min(ceil(($pagination['total'] ?? 0) / ($pagination['limit'] ?? 20)), ($pagination['page'] ?? 1) + 1),
                'last' => ceil(($pagination['total'] ?? 0) / ($pagination['limit'] ?? 20))
            ]
        ];
    }
    
    /**
     * Format validation errors
     */
    private function formatValidationErrors($errors) {
        $formatted_errors = [];
        
        foreach ($errors as $error) {
            if (is_array($error)) {
                $formatted_errors[] = [
                    'field' => $error['field'] ?? 'unknown',
                    'rule' => $error['rule'] ?? 'validation',
                    'message' => $error['message'] ?? 'Validation failed'
                ];
            } else {
                $formatted_errors[] = [
                    'field' => 'general',
                    'rule' => 'validation',
                    'message' => (string)$error
                ];
            }
        }
        
        return $formatted_errors;
    }
    
    /**
     * Get error type from HTTP code
     */
    private function getErrorType($code) {
        $error_types = [
            400 => 'bad_request',
            401 => 'authentication',
            403 => 'authorization',
            404 => 'not_found',
            422 => 'validation',
            429 => 'rate_limit',
            500 => 'internal_server',
            502 => 'bad_gateway',
            503 => 'service_unavailable',
            504 => 'gateway_timeout'
        ];
        
        return $error_types[$code] ?? 'unknown';
    }
    
    /**
     * Get default Chart.js options
     */
    private function getDefaultChartOptions($chart_type) {
        $base_options = [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'animation' => [
                'duration' => 1000
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top'
                ],
                'tooltip' => [
                    'enabled' => true,
                    'mode' => 'index',
                    'intersect' => false
                ]
            ]
        ];
        
        switch ($chart_type) {
            case 'line':
                $base_options['scales'] = [
                    'x' => ['display' => true],
                    'y' => ['display' => true, 'beginAtZero' => true]
                ];
                break;
                
            case 'bar':
                $base_options['scales'] = [
                    'x' => ['display' => true],
                    'y' => ['display' => true, 'beginAtZero' => true]
                ];
                break;
                
            case 'pie':
            case 'doughnut':
                unset($base_options['scales']);
                break;
        }
        
        return $base_options;
    }
    
    /**
     * Prepare response with headers
     */
    private function prepareResponse($response, $http_code, $additional_headers = []) {
        $headers = array_merge($this->default_headers, $additional_headers);
        
        // Add rate limiting headers if available
        if (class_exists('MeschainAdvancedRateLimiter')) {
            $rate_limit_info = $this->getRateLimitHeaders();
            $headers = array_merge($headers, $rate_limit_info);
        }
        
        // Add performance headers
        $headers['X-Response-Time'] = $this->performance_tracker->getProcessingTime() . 'ms';
        $headers['X-Memory-Usage'] = $this->formatBytes(memory_get_usage(true));
        
        return [
            'response' => $response,
            'http_code' => $http_code,
            'headers' => $headers
        ];
    }
    
    /**
     * Get rate limit headers
     */
    private function getRateLimitHeaders() {
        // This would integrate with the rate limiter
        return [
            'X-RateLimit-Limit' => '1000',
            'X-RateLimit-Remaining' => '999',
            'X-RateLimit-Reset' => time() + 3600
        ];
    }
    
    /**
     * Generate unique request ID
     */
    private function generateRequestId() {
        return 'req_' . uniqid() . '_' . substr(md5(microtime(true)), 0, 8);
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Send response with proper headers
     */
    public function send($formatted_response, $controller = null) {
        $response = $formatted_response['response'];
        $http_code = $formatted_response['http_code'];
        $headers = $formatted_response['headers'];
        
        if ($controller && method_exists($controller, 'response')) {
            // OpenCart controller response
            foreach ($headers as $name => $value) {
                $controller->response->addHeader($name . ': ' . $value);
            }
            
            // Set HTTP status code
            $controller->response->addHeader('HTTP/1.1 ' . $http_code);
            $controller->response->setOutput(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            // Direct PHP response
            foreach ($headers as $name => $value) {
                header($name . ': ' . $value);
            }
            
            http_response_code($http_code);
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Convert response to JSON string
     */
    public function toJson($formatted_response) {
        return json_encode($formatted_response['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Convert response to array
     */
    public function toArray($formatted_response) {
        return $formatted_response['response'];
    }
}

/**
 * API Performance Tracker
 */
class ApiPerformanceTracker {
    private $start_time;
    private $start_memory;
    
    public function __construct() {
        $this->start_time = microtime(true);
        $this->start_memory = memory_get_usage(true);
    }
    
    public function getProcessingTime() {
        return round((microtime(true) - $this->start_time) * 1000, 2);
    }
    
    public function getMemoryUsage() {
        return memory_get_usage(true) - $this->start_memory;
    }
}
