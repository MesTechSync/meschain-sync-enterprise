<?php
/**
 * API Helper Class
 * 
 * Bu sınıf tüm marketplace API entegrasyonları için ortak işlevler sağlar.
 * Rate limiting, retry logic, data validation ve utility functions içerir.
 * 
 * @category   Helper
 * @package    MesChain-Sync
 * @subpackage Helper
 * @version    1.0.0
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

namespace MesChain\Library\Entegrator\Helper;

class ApiHelper {
    
    private $registry;
    private $config;
    private $log;
    private $cache;
    
    /**
     * Rate limiting storage
     */
    private static $rate_limits = [];
    
    /**
     * Constructor
     * 
     * @param object $registry OpenCart registry nesnesi
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $this->registry->get('config');
        $this->log = new \Log('api_helper.log');
        $this->cache = $this->registry->get('cache');
    }
    
    /**
     * Execute API request with retry logic
     * 
     * @param callable $callback API request callback
     * @param array $options Request options
     * @return array Response data
     */
    public function executeWithRetry($callback, $options = []) {
        $default_options = [
            'max_retries' => 3,
            'retry_delay' => 1,
            'exponential_backoff' => true,
            'retry_on_status' => [429, 500, 502, 503, 504]
        ];
        
        $options = array_merge($default_options, $options);
        $attempt = 0;
        
        while ($attempt <= $options['max_retries']) {
            try {
                $response = call_user_func($callback);
                
                // Log successful request
                $this->log->write("API request successful on attempt " . ($attempt + 1));
                
                return [
                    'success' => true,
                    'data' => $response,
                    'attempts' => $attempt + 1
                ];
                
            } catch (Exception $e) {
                $attempt++;
                
                // Check if we should retry based on error
                if ($this->shouldRetry($e, $options, $attempt)) {
                    $delay = $this->calculateRetryDelay($attempt, $options);
                    
                    $this->log->write("API request failed (attempt $attempt), retrying in {$delay}s: " . $e->getMessage());
                    
                    sleep($delay);
                    continue;
                }
                
                // No more retries or non-retryable error
                $this->log->write("API request failed after $attempt attempts: " . $e->getMessage());
                
                return [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'attempts' => $attempt
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => 'Max retries exceeded',
            'attempts' => $attempt
        ];
    }
    
    /**
     * Check rate limits before making request
     * 
     * @param string $api_key API identifier
     * @param int $requests_per_minute Requests per minute limit
     * @return bool Can make request
     */
    public function checkRateLimit($api_key, $requests_per_minute = 60) {
        $cache_key = 'rate_limit_' . md5($api_key);
        $current_time = time();
        $window_start = $current_time - 60; // 1-minute window
        
        // Get current request count from cache
        $requests = $this->cache->get($cache_key) ?: [];
        
        // Filter out old requests (outside the window)
        $requests = array_filter($requests, function($timestamp) use ($window_start) {
            return $timestamp > $window_start;
        });
        
        // Check if we can make another request
        if (count($requests) >= $requests_per_minute) {
            $this->log->write("Rate limit exceeded for API: $api_key");
            return false;
        }
        
        // Add current request timestamp
        $requests[] = $current_time;
        
        // Store back in cache
        $this->cache->set($cache_key, $requests, 120); // 2 minutes TTL
        
        return true;
    }
    
    /**
     * Validate product data for API sync
     * 
     * @param array $product Product data
     * @param array $required_fields Required field names
     * @return array Validation result
     */
    public function validateProductData($product, $required_fields = []) {
        $errors = [];
        $warnings = [];
        
        // Default required fields
        $default_required = ['name', 'price', 'sku'];
        $required_fields = array_merge($default_required, $required_fields);
        
        // Check required fields
        foreach ($required_fields as $field) {
            if (empty($product[$field])) {
                $errors[] = "Missing required field: $field";
            }
        }
        
        // Validate price
        if (!empty($product['price'])) {
            if (!is_numeric($product['price']) || (float)$product['price'] <= 0) {
                $errors[] = "Invalid price: must be a positive number";
            }
        }
        
        // Validate quantity
        if (isset($product['quantity'])) {
            if (!is_numeric($product['quantity']) || (int)$product['quantity'] < 0) {
                $errors[] = "Invalid quantity: must be a non-negative number";
            }
        }
        
        // Validate SKU format
        if (!empty($product['sku'])) {
            if (strlen($product['sku']) > 50) {
                $warnings[] = "SKU is too long (max 50 characters)";
            }
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $product['sku'])) {
                $warnings[] = "SKU contains invalid characters (only alphanumeric, dash, underscore allowed)";
            }
        }
        
        // Validate name length
        if (!empty($product['name'])) {
            if (strlen($product['name']) > 255) {
                $warnings[] = "Product name is too long (max 255 characters)";
            }
        }
        
        // Validate description
        if (!empty($product['description'])) {
            if (strlen(strip_tags($product['description'])) > 5000) {
                $warnings[] = "Product description is too long (max 5000 characters)";
            }
        }
        
        // Validate dimensions
        $dimension_fields = ['length', 'width', 'height', 'weight'];
        foreach ($dimension_fields as $field) {
            if (!empty($product[$field])) {
                if (!is_numeric($product[$field]) || (float)$product[$field] < 0) {
                    $warnings[] = "Invalid $field: must be a non-negative number";
                }
            }
        }
        
        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Sanitize data for API transmission
     * 
     * @param array $data Data to sanitize
     * @return array Sanitized data
     */
    public function sanitizeApiData($data) {
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                $sanitized[$key] = $this->sanitizeApiData($value);
            }
            return $sanitized;
        }
        
        if (is_string($data)) {
            // Remove null bytes
            $data = str_replace("\0", '', $data);
            
            // Trim whitespace
            $data = trim($data);
            
            // Remove control characters except newlines and tabs
            $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $data);
            
            return $data;
        }
        
        return $data;
    }
    
    /**
     * Generate consistent API signature
     * 
     * @param string $method HTTP method
     * @param string $url Request URL
     * @param array $params Request parameters
     * @param string $secret API secret
     * @return string Signature
     */
    public function generateSignature($method, $url, $params, $secret) {
        // Sort parameters by key
        ksort($params);
        
        // Create query string
        $query_string = http_build_query($params);
        
        // Create signature base string
        $base_string = strtoupper($method) . '&' . rawurlencode($url) . '&' . rawurlencode($query_string);
        
        // Generate signature
        return base64_encode(hash_hmac('sha256', $base_string, $secret, true));
    }
    
    /**
     * Convert OpenCart product to standardized format
     * 
     * @param array $product OpenCart product data
     * @return array Standardized product data
     */
    public function standardizeProduct($product) {
        return [
            'id' => $product['product_id'] ?? null,
            'sku' => $product['sku'] ?? ('OC_' . ($product['product_id'] ?? uniqid())),
            'name' => $product['name'] ?? '',
            'description' => strip_tags($product['description'] ?? ''),
            'short_description' => $this->truncateText($product['description'] ?? '', 255),
            'price' => (float)($product['price'] ?? 0),
            'compare_price' => (float)($product['compare_price'] ?? 0),
            'cost_price' => (float)($product['cost_price'] ?? 0),
            'quantity' => (int)($product['quantity'] ?? 0),
            'weight' => (float)($product['weight'] ?? 0),
            'length' => (float)($product['length'] ?? 0),
            'width' => (float)($product['width'] ?? 0),
            'height' => (float)($product['height'] ?? 0),
            'status' => (bool)($product['status'] ?? true),
            'model' => $product['model'] ?? '',
            'upc' => $product['upc'] ?? '',
            'ean' => $product['ean'] ?? '',
            'isbn' => $product['isbn'] ?? '',
            'mpn' => $product['mpn'] ?? '',
            'manufacturer' => $product['manufacturer'] ?? '',
            'brand' => $product['manufacturer'] ?? '',
            'category_id' => $product['category_id'] ?? null,
            'category_name' => $product['category_name'] ?? '',
            'main_image' => $product['image'] ?? '',
            'additional_images' => $product['additional_images'] ?? [],
            'attributes' => $product['attributes'] ?? [],
            'options' => $product['options'] ?? [],
            'tags' => $product['tags'] ?? [],
            'meta_title' => $product['meta_title'] ?? '',
            'meta_description' => $product['meta_description'] ?? '',
            'meta_keywords' => $product['meta_keywords'] ?? '',
            'created_at' => $product['date_added'] ?? date('Y-m-d H:i:s'),
            'updated_at' => $product['date_modified'] ?? date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Parse and validate marketplace response
     * 
     * @param string $response Raw response
     * @param string $format Expected format (json, xml)
     * @return array Parsed response
     */
    public function parseResponse($response, $format = 'json') {
        try {
            switch (strtolower($format)) {
                case 'json':
                    $decoded = json_decode($response, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception('Invalid JSON response: ' . json_last_error_msg());
                    }
                    return $decoded;
                    
                case 'xml':
                    libxml_use_internal_errors(true);
                    $decoded = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
                    if ($decoded === false) {
                        $errors = libxml_get_errors();
                        $error_message = 'Invalid XML response';
                        if (!empty($errors)) {
                            $error_message .= ': ' . $errors[0]->message;
                        }
                        throw new Exception($error_message);
                    }
                    return json_decode(json_encode($decoded), true);
                    
                default:
                    return $response;
            }
        } catch (Exception $e) {
            $this->log->write('Response parsing error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Calculate retry delay with exponential backoff
     * 
     * @param int $attempt Current attempt number
     * @param array $options Retry options
     * @return int Delay in seconds
     */
    private function calculateRetryDelay($attempt, $options) {
        $base_delay = $options['retry_delay'];
        
        if ($options['exponential_backoff']) {
            return min($base_delay * pow(2, $attempt - 1), 60); // Max 60 seconds
        }
        
        return $base_delay;
    }
    
    /**
     * Check if error should trigger retry
     * 
     * @param Exception $e Exception thrown
     * @param array $options Retry options
     * @param int $attempt Current attempt number
     * @return bool Should retry
     */
    private function shouldRetry($e, $options, $attempt) {
        if ($attempt > $options['max_retries']) {
            return false;
        }
        
        // Check if error message contains HTTP status code
        if (preg_match('/HTTP Error (\d+)/', $e->getMessage(), $matches)) {
            $status_code = (int)$matches[1];
            return in_array($status_code, $options['retry_on_status']);
        }
        
        // Check for common retryable errors
        $retryable_messages = [
            'connection timeout',
            'connection refused',
            'temporary failure',
            'service unavailable',
            'rate limit',
            'too many requests'
        ];
        
        $error_message = strtolower($e->getMessage());
        foreach ($retryable_messages as $message) {
            if (strpos($error_message, $message) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Truncate text to specified length
     * 
     * @param string $text Text to truncate
     * @param int $length Maximum length
     * @param string $suffix Suffix to append
     * @return string Truncated text
     */
    private function truncateText($text, $length, $suffix = '...') {
        $text = strip_tags($text);
        
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length - strlen($suffix)) . $suffix;
    }
    
    /**
     * Get marketplace-specific currency formatting
     * 
     * @param float $amount Amount to format
     * @param string $currency Currency code
     * @return string Formatted amount
     */
    public function formatCurrency($amount, $currency = 'USD') {
        $currency_configs = [
            'USD' => ['decimals' => 2, 'decimal_point' => '.', 'thousands_sep' => ''],
            'EUR' => ['decimals' => 2, 'decimal_point' => '.', 'thousands_sep' => ''],
            'GBP' => ['decimals' => 2, 'decimal_point' => '.', 'thousands_sep' => ''],
            'TRY' => ['decimals' => 2, 'decimal_point' => '.', 'thousands_sep' => ''],
            'CAD' => ['decimals' => 2, 'decimal_point' => '.', 'thousands_sep' => ''],
            'AUD' => ['decimals' => 2, 'decimal_point' => '.', 'thousands_sep' => ''],
            'JPY' => ['decimals' => 0, 'decimal_point' => '', 'thousands_sep' => '']
        ];
        
        $config = $currency_configs[$currency] ?? $currency_configs['USD'];
        
        return number_format(
            (float)$amount,
            $config['decimals'],
            $config['decimal_point'],
            $config['thousands_sep']
        );
    }
    
    /**
     * Generate unique batch ID for bulk operations
     * 
     * @param string $prefix Prefix for batch ID
     * @return string Unique batch ID
     */
    public function generateBatchId($prefix = 'BATCH') {
        return $prefix . '_' . date('YmdHis') . '_' . uniqid();
    }
    
    /**
     * Log API performance metrics
     * 
     * @param string $api_name API name
     * @param float $response_time Response time in seconds
     * @param int $response_size Response size in bytes
     * @param bool $success Request success status
     */
    public function logPerformanceMetrics($api_name, $response_time, $response_size, $success) {
        $metrics = [
            'api_name' => $api_name,
            'response_time' => round($response_time, 3),
            'response_size' => $response_size,
            'success' => $success,
            'timestamp' => date('Y-m-d H:i:s'),
            'memory_usage' => memory_get_peak_usage(true)
        ];
        
        $this->log->write('API Performance: ' . json_encode($metrics));
        
        // Store metrics in cache for reporting
        $cache_key = 'api_metrics_' . date('Y-m-d');
        $daily_metrics = $this->cache->get($cache_key) ?: [];
        $daily_metrics[] = $metrics;
        $this->cache->set($cache_key, $daily_metrics, 86400); // 24 hours TTL
    }
} 