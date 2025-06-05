<?php
/**
 * Base API Class
 * MesChain-Sync OpenCart Extension
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0
 * @author MesChain Development Team
 */

abstract class BaseApi {
    
    protected $config;
    protected $logger;
    protected $cache_enabled;
    protected $cache_ttl;
    protected $rate_limit;
    protected $last_request_time;
    
    /**
     * Constructor
     *
     * @param array $config API configuration
     * @param object $logger Logger instance
     */
    public function __construct($config = [], $logger = null) {
        $this->config = $config;
        $this->logger = $logger;
        $this->cache_enabled = isset($config['cache_enabled']) ? $config['cache_enabled'] : true;
        $this->cache_ttl = isset($config['cache_ttl']) ? $config['cache_ttl'] : 3600; // 1 hour
        $this->rate_limit = isset($config['rate_limit']) ? $config['rate_limit'] : 1000; // 1 second
        $this->last_request_time = 0;
    }
    
    /**
     * Make HTTP request with rate limiting and caching
     *
     * @param string $url Request URL
     * @param string $method HTTP method
     * @param array $data Request data
     * @param array $headers Additional headers
     * @param bool $use_cache Whether to use cache
     * @return array Response data
     */
    protected function makeRequest($url, $method = 'GET', $data = [], $headers = [], $use_cache = true) {
        // Rate limiting
        $this->enforceRateLimit();
        
        // Check cache first
        if ($use_cache && $this->cache_enabled && $method === 'GET') {
            $cached_response = $this->getCachedResponse($url, $data);
            if ($cached_response !== null) {
                return $cached_response;
            }
        }
        
        // Prepare request
        $ch = curl_init();
        $default_headers = $this->getDefaultHeaders();
        $all_headers = array_merge($default_headers, $headers);
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => $all_headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => $this->getUserAgent(),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ]);
        
        // Set method-specific options
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->prepareRequestData($data));
                }
                break;
                
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->prepareRequestData($data));
                }
                break;
                
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->prepareRequestData($data));
                }
                break;
                
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
                
            case 'GET':
            default:
                if (!empty($data)) {
                    $url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($data);
                    curl_setopt($ch, CURLOPT_URL, $url);
                }
                break;
        }
        
        // Execute request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            $this->logError('cURL Error: ' . $error, ['url' => $url, 'method' => $method]);
            throw new Exception('Request failed: ' . $error);
        }
        
        // Parse response
        $parsed_response = $this->parseResponse($response, $http_code);
        
        // Log API call
        if ($this->logger) {
            $this->logger->logApiCall(
                $this->getMarketplaceName(),
                $url,
                $method,
                $data,
                $parsed_response,
                $http_code
            );
        }
        
        // Cache successful GET responses
        if ($use_cache && $this->cache_enabled && $method === 'GET' && $http_code === 200) {
            $this->cacheResponse($url, $data, $parsed_response);
        }
        
        return [
            'success' => $http_code >= 200 && $http_code < 300,
            'http_code' => $http_code,
            'data' => $parsed_response,
            'raw_response' => $response,
            'info' => $info
        ];
    }
    
    /**
     * Enforce rate limiting
     *
     * @return void
     */
    protected function enforceRateLimit() {
        $current_time = microtime(true) * 1000; // milliseconds
        $time_since_last = $current_time - $this->last_request_time;
        
        if ($time_since_last < $this->rate_limit) {
            $sleep_time = ($this->rate_limit - $time_since_last) * 1000; // microseconds
            usleep($sleep_time);
        }
        
        $this->last_request_time = microtime(true) * 1000;
    }
    
    /**
     * Get cached response
     *
     * @param string $url Request URL
     * @param array $data Request data
     * @return mixed Cached response or null
     */
    protected function getCachedResponse($url, $data = []) {
        $cache_key = $this->generateCacheKey($url, $data);
        $cache_file = $this->getCacheFilePath($cache_key);
        
        if (file_exists($cache_file)) {
            $cache_data = json_decode(file_get_contents($cache_file), true);
            
            if ($cache_data && $cache_data['expires'] > time()) {
                return $cache_data['data'];
            } else {
                // Remove expired cache
                unlink($cache_file);
            }
        }
        
        return null;
    }
    
    /**
     * Cache response
     *
     * @param string $url Request URL
     * @param array $data Request data
     * @param mixed $response Response data
     * @return bool Success status
     */
    protected function cacheResponse($url, $data, $response) {
        try {
            $cache_key = $this->generateCacheKey($url, $data);
            $cache_file = $this->getCacheFilePath($cache_key);
            $cache_dir = dirname($cache_file);
            
            if (!is_dir($cache_dir)) {
                mkdir($cache_dir, 0755, true);
            }
            
            $cache_data = [
                'data' => $response,
                'expires' => time() + $this->cache_ttl,
                'created' => time()
            ];
            
            return file_put_contents($cache_file, json_encode($cache_data)) !== false;
        } catch (Exception $e) {
            $this->logError('Cache write failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate cache key
     *
     * @param string $url Request URL
     * @param array $data Request data
     * @return string Cache key
     */
    protected function generateCacheKey($url, $data = []) {
        return md5($url . serialize($data));
    }
    
    /**
     * Get cache file path
     *
     * @param string $cache_key Cache key
     * @return string File path
     */
    protected function getCacheFilePath($cache_key) {
        $cache_dir = DIR_CACHE . 'meschain/' . $this->getMarketplaceName() . '/';
        return $cache_dir . substr($cache_key, 0, 2) . '/' . $cache_key . '.json';
    }
    
    /**
     * Parse API response
     *
     * @param string $response Raw response
     * @param int $http_code HTTP status code
     * @return mixed Parsed response
     */
    protected function parseResponse($response, $http_code) {
        // Try to decode JSON
        $decoded = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
        
        // Try to parse XML
        if (strpos($response, '<?xml') === 0) {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($response);
            
            if ($xml !== false) {
                return json_decode(json_encode($xml), true);
            }
        }
        
        // Return raw response if parsing fails
        return $response;
    }
    
    /**
     * Prepare request data
     *
     * @param mixed $data Request data
     * @return string Prepared data
     */
    protected function prepareRequestData($data) {
        if (is_array($data) || is_object($data)) {
            return json_encode($data);
        }
        
        return (string)$data;
    }
    
    /**
     * Get default headers
     *
     * @return array Default headers
     */
    protected function getDefaultHeaders() {
        return [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: ' . $this->getUserAgent()
        ];
    }
    
    /**
     * Get user agent string
     *
     * @return string User agent
     */
    protected function getUserAgent() {
        return 'MesChain-Sync/3.0.4.0 (' . $this->getMarketplaceName() . ' API Client)';
    }
    
    /**
     * Log error message
     *
     * @param string $message Error message
     * @param array $context Additional context
     * @return void
     */
    protected function logError($message, $context = []) {
        if ($this->logger) {
            $this->logger->log($this->getMarketplaceName(), 'error', $message, $context);
        } else {
            error_log("MesChain API Error [{$this->getMarketplaceName()}]: " . $message);
        }
    }
    
    /**
     * Log info message
     *
     * @param string $message Info message
     * @param array $context Additional context
     * @return void
     */
    protected function logInfo($message, $context = []) {
        if ($this->logger) {
            $this->logger->log($this->getMarketplaceName(), 'info', $message, $context);
        }
    }
    
    /**
     * Log warning message
     *
     * @param string $message Warning message
     * @param array $context Additional context
     * @return void
     */
    protected function logWarning($message, $context = []) {
        if ($this->logger) {
            $this->logger->log($this->getMarketplaceName(), 'warning', $message, $context);
        }
    }
    
    /**
     * Validate required configuration
     *
     * @param array $required_keys Required configuration keys
     * @return bool Validation result
     * @throws Exception If validation fails
     */
    protected function validateConfig($required_keys) {
        $missing_keys = [];
        
        foreach ($required_keys as $key) {
            if (!isset($this->config[$key]) || empty($this->config[$key])) {
                $missing_keys[] = $key;
            }
        }
        
        if (!empty($missing_keys)) {
            throw new Exception('Missing required configuration: ' . implode(', ', $missing_keys));
        }
        
        return true;
    }
    
    /**
     * Clean cache
     *
     * @param int $max_age Maximum age in seconds (optional)
     * @return bool Success status
     */
    public function cleanCache($max_age = null) {
        try {
            $cache_dir = DIR_CACHE . 'meschain/' . $this->getMarketplaceName() . '/';
            
            if (!is_dir($cache_dir)) {
                return true;
            }
            
            $cutoff_time = $max_age ? time() - $max_age : 0;
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($cache_dir, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'json') {
                    if ($max_age === null || $file->getMTime() < $cutoff_time) {
                        unlink($file->getPathname());
                    }
                }
            }
            
            return true;
        } catch (Exception $e) {
            $this->logError('Cache cleanup failed: ' . $e->getMessage());
            return false;
        }
    }
    
    // Abstract methods that must be implemented by child classes
    
    /**
     * Get marketplace name
     *
     * @return string Marketplace name
     */
    abstract protected function getMarketplaceName();
    
    /**
     * Authenticate with the marketplace API
     *
     * @return array Authentication result
     */
    abstract public function authenticate();
    
    /**
     * Get products from marketplace
     *
     * @param array $filters Filter options
     * @return array Products data
     */
    abstract public function getProducts($filters = []);
    
    /**
     * Create or update product
     *
     * @param array $product_data Product data
     * @return array Operation result
     */
    abstract public function createOrUpdateProduct($product_data);
    
    /**
     * Get orders from marketplace
     *
     * @param array $filters Filter options
     * @return array Orders data
     */
    abstract public function getOrders($filters = []);
    
    /**
     * Update order status
     *
     * @param string $order_id Order ID
     * @param string $status New status
     * @param array $additional_data Additional data
     * @return array Operation result
     */
    abstract public function updateOrderStatus($order_id, $status, $additional_data = []);
} 