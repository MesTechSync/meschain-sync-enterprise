<?php
/**
 * MesChain-Sync API Error Handler
 * Comprehensive error handling and response standardization system
 * 
 * @version 1.0.0
 * @date June 2, 2025
 * @author MesChain Development Team
 */

class MeschainApiErrorHandler {
    
    private $log_file;
    private $debug_mode;
    private $error_codes;
    private $response_templates;
    
    public function __construct($config = []) {
        $this->log_file = isset($config['log_file']) ? $config['log_file'] : DIR_LOGS . 'meschain_api_errors.log';
        $this->debug_mode = isset($config['debug_mode']) ? $config['debug_mode'] : false;
        
        $this->initializeErrorCodes();
        $this->initializeResponseTemplates();
    }
    
    /**
     * Initialize standardized error codes
     */
    private function initializeErrorCodes() {
        $this->error_codes = [
            // Authentication Errors (1000-1099)
            'AUTH_TOKEN_MISSING' => 1001,
            'AUTH_TOKEN_INVALID' => 1002,
            'AUTH_TOKEN_EXPIRED' => 1003,
            'AUTH_PERMISSION_DENIED' => 1004,
            'AUTH_USER_NOT_FOUND' => 1005,
            'AUTH_SESSION_EXPIRED' => 1006,
            
            // Validation Errors (1100-1199)
            'VALIDATION_REQUIRED_FIELD' => 1101,
            'VALIDATION_INVALID_FORMAT' => 1102,
            'VALIDATION_OUT_OF_RANGE' => 1103,
            'VALIDATION_TYPE_MISMATCH' => 1104,
            'VALIDATION_FILE_SIZE' => 1105,
            'VALIDATION_FILE_TYPE' => 1106,
            
            // Database Errors (1200-1299)
            'DB_CONNECTION_FAILED' => 1201,
            'DB_QUERY_FAILED' => 1202,
            'DB_RECORD_NOT_FOUND' => 1203,
            'DB_DUPLICATE_ENTRY' => 1204,
            'DB_FOREIGN_KEY_CONSTRAINT' => 1205,
            'DB_TRANSACTION_FAILED' => 1206,
            
            // Marketplace API Errors (1300-1399)
            'MARKETPLACE_API_UNAVAILABLE' => 1301,
            'MARKETPLACE_AUTH_FAILED' => 1302,
            'MARKETPLACE_RATE_LIMIT' => 1303,
            'MARKETPLACE_INVALID_REQUEST' => 1304,
            'MARKETPLACE_DATA_FORMAT' => 1305,
            'MARKETPLACE_TIMEOUT' => 1306,
            
            // Rate Limiting Errors (1400-1499)
            'RATE_LIMIT_EXCEEDED' => 1401,
            'RATE_LIMIT_DAILY_QUOTA' => 1402,
            'RATE_LIMIT_BURST_EXCEEDED' => 1403,
            'RATE_LIMIT_IP_BLOCKED' => 1404,
            'RATE_LIMIT_USER_SUSPENDED' => 1405,
            
            // System Errors (1500-1599)
            'SYSTEM_MAINTENANCE' => 1501,
            'SYSTEM_OVERLOAD' => 1502,
            'SYSTEM_FILE_NOT_FOUND' => 1503,
            'SYSTEM_PERMISSION_DENIED' => 1504,
            'SYSTEM_DISK_FULL' => 1505,
            'SYSTEM_MEMORY_EXHAUSTED' => 1506,
            
            // Business Logic Errors (1600-1699)
            'BUSINESS_OPERATION_NOT_ALLOWED' => 1601,
            'BUSINESS_INSUFFICIENT_FUNDS' => 1602,
            'BUSINESS_INVENTORY_UNAVAILABLE' => 1603,
            'BUSINESS_ORDER_CANCELLED' => 1604,
            'BUSINESS_PRODUCT_DISCONTINUED' => 1605,
            
            // Integration Errors (1700-1799)
            'INTEGRATION_SERVICE_UNAVAILABLE' => 1701,
            'INTEGRATION_CONFIG_MISSING' => 1702,
            'INTEGRATION_VERSION_MISMATCH' => 1703,
            'INTEGRATION_WEBHOOK_FAILED' => 1704,
            'INTEGRATION_SYNC_FAILED' => 1705,
            
            // Unknown/Generic Errors (9000-9099)
            'UNKNOWN_ERROR' => 9001,
            'INTERNAL_SERVER_ERROR' => 9002,
            'UNEXPECTED_EXCEPTION' => 9003
        ];
    }
    
    /**
     * Initialize response templates
     */
    private function initializeResponseTemplates() {
        $this->response_templates = [
            'success' => [
                'status' => 'success',
                'code' => 200,
                'message' => 'Operation completed successfully',
                'data' => null,
                'meta' => []
            ],
            'error' => [
                'status' => 'error',
                'code' => null,
                'message' => null,
                'error_code' => null,
                'details' => null,
                'meta' => [
                    'timestamp' => null,
                    'request_id' => null,
                    'debug_info' => null
                ]
            ],
            'validation_error' => [
                'status' => 'error',
                'code' => 400,
                'message' => 'Validation failed',
                'error_code' => 'VALIDATION_FAILED',
                'validation_errors' => [],
                'meta' => [
                    'timestamp' => null,
                    'request_id' => null
                ]
            ]
        ];
    }
    
    /**
     * Handle and format exceptions
     */
    public function handleException($exception, $context = []) {
        $error_details = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $this->debug_mode ? $exception->getTraceAsString() : null,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'request_id' => $this->generateRequestId()
        ];
        
        // Log the error
        $this->logError($error_details);
        
        // Determine error type and response
        $response = $this->categorizeError($exception, $error_details);
        
        return $response;
    }
    
    /**
     * Categorize errors and create appropriate responses
     */
    private function categorizeError($exception, $error_details) {
        $error_type = $this->determineErrorType($exception);
        
        $response = $this->response_templates['error'];
        $response['meta']['timestamp'] = date('Y-m-d H:i:s');
        $response['meta']['request_id'] = $error_details['request_id'];
        
        switch ($error_type) {
            case 'database':
                $response['code'] = 500;
                $response['error_code'] = $this->error_codes['DB_QUERY_FAILED'];
                $response['message'] = 'Database operation failed';
                break;
                
            case 'authentication':
                $response['code'] = 401;
                $response['error_code'] = $this->error_codes['AUTH_TOKEN_INVALID'];
                $response['message'] = 'Authentication failed';
                break;
                
            case 'validation':
                $response['code'] = 400;
                $response['error_code'] = $this->error_codes['VALIDATION_INVALID_FORMAT'];
                $response['message'] = 'Input validation failed';
                break;
                
            case 'rate_limit':
                $response['code'] = 429;
                $response['error_code'] = $this->error_codes['RATE_LIMIT_EXCEEDED'];
                $response['message'] = 'Rate limit exceeded';
                break;
                
            case 'marketplace':
                $response['code'] = 503;
                $response['error_code'] = $this->error_codes['MARKETPLACE_API_UNAVAILABLE'];
                $response['message'] = 'Marketplace API unavailable';
                break;
                
            default:
                $response['code'] = 500;
                $response['error_code'] = $this->error_codes['INTERNAL_SERVER_ERROR'];
                $response['message'] = 'Internal server error';
                break;
        }
        
        if ($this->debug_mode) {
            $response['meta']['debug_info'] = $error_details;
        }
        
        return $response;
    }
    
    /**
     * Determine error type from exception
     */
    private function determineErrorType($exception) {
        $message = strtolower($exception->getMessage());
        $class = strtolower(get_class($exception));
        
        if (strpos($message, 'database') !== false || strpos($message, 'sql') !== false) {
            return 'database';
        }
        
        if (strpos($message, 'auth') !== false || strpos($message, 'token') !== false) {
            return 'authentication';
        }
        
        if (strpos($message, 'validation') !== false || strpos($message, 'invalid') !== false) {
            return 'validation';
        }
        
        if (strpos($message, 'rate limit') !== false || strpos($message, 'quota') !== false) {
            return 'rate_limit';
        }
        
        if (strpos($message, 'marketplace') !== false || strpos($message, 'api') !== false) {
            return 'marketplace';
        }
        
        return 'generic';
    }
    
    /**
     * Create validation error response
     */
    public function createValidationError($validation_errors, $context = []) {
        $response = $this->response_templates['validation_error'];
        $response['validation_errors'] = $validation_errors;
        $response['meta']['timestamp'] = date('Y-m-d H:i:s');
        $response['meta']['request_id'] = $this->generateRequestId();
        
        // Log validation error
        $this->logError([
            'type' => 'validation_error',
            'errors' => $validation_errors,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'request_id' => $response['meta']['request_id']
        ]);
        
        return $response;
    }
    
    /**
     * Create success response
     */
    public function createSuccessResponse($data = null, $message = null, $meta = []) {
        $response = $this->response_templates['success'];
        $response['data'] = $data;
        $response['meta']['timestamp'] = date('Y-m-d H:i:s');
        $response['meta']['request_id'] = $this->generateRequestId();
        
        if ($message !== null) {
            $response['message'] = $message;
        }
        
        if (!empty($meta)) {
            $response['meta'] = array_merge($response['meta'], $meta);
        }
        
        return $response;
    }
    
    /**
     * Create custom error response
     */
    public function createCustomError($error_code_key, $message, $http_code = 500, $details = null) {
        $response = $this->response_templates['error'];
        $response['code'] = $http_code;
        $response['error_code'] = isset($this->error_codes[$error_code_key]) ? 
                                 $this->error_codes[$error_code_key] : 
                                 $this->error_codes['UNKNOWN_ERROR'];
        $response['message'] = $message;
        $response['details'] = $details;
        $response['meta']['timestamp'] = date('Y-m-d H:i:s');
        $response['meta']['request_id'] = $this->generateRequestId();
        
        // Log custom error
        $this->logError([
            'type' => 'custom_error',
            'error_code' => $error_code_key,
            'message' => $message,
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s'),
            'request_id' => $response['meta']['request_id']
        ]);
        
        return $response;
    }
    
    /**
     * Log errors to file
     */
    private function logError($error_details) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => 'ERROR',
            'request_id' => isset($error_details['request_id']) ? $error_details['request_id'] : $this->generateRequestId(),
            'error' => $error_details,
            'server' => [
                'REQUEST_URI' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
                'REQUEST_METHOD' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '',
                'HTTP_USER_AGENT' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
                'REMOTE_ADDR' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''
            ]
        ];
        
        $formatted_log = json_encode($log_entry, JSON_PRETTY_PRINT) . "\n";
        
        // Write to log file
        error_log($formatted_log, 3, $this->log_file);
        
        // Also log to PHP error log for critical errors
        if (isset($error_details['type']) && $error_details['type'] === 'database') {
            error_log('MesChain API Database Error: ' . $error_details['message']);
        }
    }
    
    /**
     * Generate unique request ID
     */
    private function generateRequestId() {
        return 'req_' . uniqid() . '_' . substr(md5(microtime()), 0, 8);
    }
    
    /**
     * Send HTTP response with proper headers
     */
    public function sendResponse($response, $controller = null) {
        if ($controller && method_exists($controller, 'response')) {
            $controller->response->addHeader('Content-Type: application/json');
            $controller->response->addHeader('Access-Control-Allow-Origin: *');
            $controller->response->addHeader('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            $controller->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            
            // Set HTTP status code
            if (isset($response['code'])) {
                $controller->response->addHeader('HTTP/1.1 ' . $response['code']);
            }
            
            $controller->response->setOutput(json_encode($response, JSON_PRETTY_PRINT));
        } else {
            // Fallback for direct usage
            header('Content-Type: application/json');
            if (isset($response['code'])) {
                http_response_code($response['code']);
            }
            echo json_encode($response, JSON_PRETTY_PRINT);
        }
    }
    
    /**
     * Validate API request data
     */
    public function validateRequest($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule_set) {
            $value = isset($data[$field]) ? $data[$field] : null;
            
            foreach ($rule_set as $rule) {
                $validation_result = $this->applyValidationRule($field, $value, $rule);
                if ($validation_result !== true) {
                    $errors[] = $validation_result;
                }
            }
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Apply individual validation rule
     */
    private function applyValidationRule($field, $value, $rule) {
        switch ($rule['type']) {
            case 'required':
                if (empty($value) && $value !== '0' && $value !== 0) {
                    return [
                        'field' => $field,
                        'rule' => 'required',
                        'message' => $field . ' is required'
                    ];
                }
                break;
                
            case 'string':
                if (!is_string($value)) {
                    return [
                        'field' => $field,
                        'rule' => 'string',
                        'message' => $field . ' must be a string'
                    ];
                }
                break;
                
            case 'integer':
                if (!is_int($value) && !ctype_digit($value)) {
                    return [
                        'field' => $field,
                        'rule' => 'integer',
                        'message' => $field . ' must be an integer'
                    ];
                }
                break;
                
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return [
                        'field' => $field,
                        'rule' => 'email',
                        'message' => $field . ' must be a valid email'
                    ];
                }
                break;
                
            case 'min_length':
                if (strlen($value) < $rule['value']) {
                    return [
                        'field' => $field,
                        'rule' => 'min_length',
                        'message' => $field . ' must be at least ' . $rule['value'] . ' characters'
                    ];
                }
                break;
                
            case 'max_length':
                if (strlen($value) > $rule['value']) {
                    return [
                        'field' => $field,
                        'rule' => 'max_length',
                        'message' => $field . ' must not exceed ' . $rule['value'] . ' characters'
                    ];
                }
                break;
        }
        
        return true;
    }
    
    /**
     * Get error statistics
     */
    public function getErrorStats($period_hours = 24) {
        $stats = [
            'total_errors' => 0,
            'error_types' => [],
            'response_codes' => [],
            'top_errors' => [],
            'period' => $period_hours . ' hours'
        ];
        
        // Read log file and analyze errors
        if (file_exists($this->log_file)) {
            $log_content = file_get_contents($this->log_file);
            $log_lines = explode("\n", $log_content);
            
            $cutoff_time = time() - ($period_hours * 3600);
            
            foreach ($log_lines as $line) {
                if (empty(trim($line))) continue;
                
                $log_entry = json_decode($line, true);
                if (!$log_entry) continue;
                
                $log_time = strtotime($log_entry['timestamp']);
                if ($log_time < $cutoff_time) continue;
                
                $stats['total_errors']++;
                
                // Count error types
                $error_type = isset($log_entry['error']['type']) ? $log_entry['error']['type'] : 'unknown';
                $stats['error_types'][$error_type] = isset($stats['error_types'][$error_type]) ? 
                                                    $stats['error_types'][$error_type] + 1 : 1;
                
                // Count response codes (if available)
                if (isset($log_entry['code'])) {
                    $stats['response_codes'][$log_entry['code']] = isset($stats['response_codes'][$log_entry['code']]) ? 
                                                                  $stats['response_codes'][$log_entry['code']] + 1 : 1;
                }
            }
        }
        
        return $stats;
    }
}
