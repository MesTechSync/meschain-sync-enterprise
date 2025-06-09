<?php
/**
 * Pazarama API Client
 * MesChain-Sync v4.0 - Pazarama Marketplace API Integration
 * Complete Turkish E-commerce Platform API Client
 * 
 * @author MesChain Development Team
 * @version 4.0.0
 * @copyright 2024 MesChain Technologies
 */

class PazaramaApiClient {
    
    private $api_key;
    private $api_secret;
    private $merchant_id;
    private $base_url;
    private $test_mode;
    private $timeout;
    private $rate_limit;
    private $last_request_time;
    private $log;
    private $access_token;
    private $token_expires;
    
    // API Endpoints
    private $endpoints = [
        'auth' => '/api/v1/auth',
        'products' => '/api/v1/products',
        'orders' => '/api/v1/orders',
        'categories' => '/api/v1/categories',
        'brands' => '/api/v1/brands',
        'inventory' => '/api/v1/inventory',
        'pricing' => '/api/v1/pricing',
        'campaigns' => '/api/v1/campaigns',
        'analytics' => '/api/v1/analytics',
        'shipping' => '/api/v1/shipping',
        'customers' => '/api/v1/customers',
        'reviews' => '/api/v1/reviews',
        'webhooks' => '/api/v1/webhooks',
        'reports' => '/api/v1/reports'
    ];
    
    public function __construct($config = array()) {
        $this->api_key = $config['api_key'] ?? '';
        $this->api_secret = $config['api_secret'] ?? '';
        $this->merchant_id = $config['merchant_id'] ?? '';
        $this->test_mode = $config['test_mode'] ?? true;
        $this->timeout = $config['timeout'] ?? 30;
        $this->rate_limit = $config['rate_limit'] ?? 1; // requests per second
        
        $this->base_url = $this->test_mode 
            ? 'https://api-test.pazarama.com' 
            : 'https://api.pazarama.com';
            
        $this->last_request_time = 0;
        
        // Initialize logging
        if (isset($config['log'])) {
            $this->log = $config['log'];
        }
    }
    
    /**
     * Authenticate with Pazarama API
     */
    public function authenticate() {
        try {
            $auth_data = array(
                'api_key' => $this->api_key,
                'merchant_id' => $this->merchant_id,
                'timestamp' => time()
            );
            
            // Generate signature
            $auth_data['signature'] = $this->generateSignature($auth_data);
            
            $response = $this->makeRequest('POST', $this->endpoints['auth'], $auth_data);
            
            if ($response['success'] && isset($response['data']['access_token'])) {
                $this->access_token = $response['data']['access_token'];
                $this->token_expires = time() + ($response['data']['expires_in'] ?? 3600);
                return true;
            }
            
            throw new Exception('Authentication failed: ' . ($response['message'] ?? 'Unknown error'));
            
        } catch (Exception $e) {
            $this->logError('AUTHENTICATION_ERROR', $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Product Management Methods
     */
    
    /**
     * Get product list
     */
    public function getProducts($filters = array(), $page = 1, $limit = 50) {
        $this->ensureAuthenticated();
        
        $params = array_merge($filters, array(
            'page' => $page,
            'limit' => min($limit, 100),
            'merchant_id' => $this->merchant_id
        ));
        
        return $this->makeRequest('GET', $this->endpoints['products'], $params);
    }
    
    /**
     * Get single product
     */
    public function getProduct($product_id) {
        $this->ensureAuthenticated();
        
        return $this->makeRequest('GET', $this->endpoints['products'] . '/' . $product_id);
    }
    
    /**
     * Create product
     */
    public function createProduct($product_data) {
        $this->ensureAuthenticated();
        
        // Validate required fields
        $required_fields = ['title', 'brand_id', 'category_id', 'list_price', 'sale_price', 'stock_quantity'];
        foreach ($required_fields as $field) {
            if (!isset($product_data[$field]) || empty($product_data[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }
        
        // Add merchant ID
        $product_data['merchant_id'] = $this->merchant_id;
        
        // Process images
        if (isset($product_data['images'])) {
            $product_data['images'] = $this->processProductImages($product_data['images']);
        }
        
        // Validate and format product data
        $product_data = $this->formatProductData($product_data);
        
        return $this->makeRequest('POST', $this->endpoints['products'], $product_data);
    }
    
    /**
     * Update product
     */
    public function updateProduct($product_id, $product_data) {
        $this->ensureAuthenticated();
        
        $product_data['merchant_id'] = $this->merchant_id;
        $product_data = $this->formatProductData($product_data);
        
        return $this->makeRequest('PUT', $this->endpoints['products'] . '/' . $product_id, $product_data);
    }
    
    /**
     * Delete product
     */
    public function deleteProduct($product_id) {
        $this->ensureAuthenticated();
        
        return $this->makeRequest('DELETE', $this->endpoints['products'] . '/' . $product_id);
    }
    
    /**
     * Update product stock
     */
    public function updateStock($product_id, $stock_quantity, $stock_code = null) {
        $this->ensureAuthenticated();
        
        $data = array(
            'product_id' => $product_id,
            'stock_quantity' => $stock_quantity,
            'merchant_id' => $this->merchant_id
        );
        
        if ($stock_code) {
            $data['stock_code'] = $stock_code;
        }
        
        return $this->makeRequest('PUT', $this->endpoints['inventory'] . '/stock', $data);
    }
    
    /**
     * Update product price
     */
    public function updatePrice($product_id, $list_price, $sale_price = null) {
        $this->ensureAuthenticated();
        
        $data = array(
            'product_id' => $product_id,
            'list_price' => $list_price,
            'merchant_id' => $this->merchant_id
        );
        
        if ($sale_price !== null) {
            $data['sale_price'] = $sale_price;
        }
        
        return $this->makeRequest('PUT', $this->endpoints['pricing'] . '/update', $data);
    }
    
    /**
     * Order Management Methods
     */
    
    /**
     * Get orders
     */
    public function getOrders($filters = array(), $page = 1, $limit = 50) {
        $this->ensureAuthenticated();
        
        $params = array_merge($filters, array(
            'page' => $page,
            'limit' => min($limit, 100),
            'merchant_id' => $this->merchant_id
        ));
        
        return $this->makeRequest('GET', $this->endpoints['orders'], $params);
    }
    
    /**
     * Get single order
     */
    public function getOrder($order_id) {
        $this->ensureAuthenticated();
        
        return $this->makeRequest('GET', $this->endpoints['orders'] . '/' . $order_id);
    }
    
    /**
     * Update order status
     */
    public function updateOrderStatus($order_id, $status, $tracking_info = array()) {
        $this->ensureAuthenticated();
        
        $data = array(
            'order_id' => $order_id,
            'status' => $status,
            'merchant_id' => $this->merchant_id
        );
        
        if (!empty($tracking_info)) {
            $data['tracking_info'] = $tracking_info;
        }
        
        return $this->makeRequest('PUT', $this->endpoints['orders'] . '/' . $order_id . '/status', $data);
    }
    
    /**
     * Ship order
     */
    public function shipOrder($order_id, $shipping_data) {
        $this->ensureAuthenticated();
        
        $required_fields = ['cargo_company', 'tracking_number'];
        foreach ($required_fields as $field) {
            if (!isset($shipping_data[$field]) || empty($shipping_data[$field])) {
                throw new Exception("Required shipping field missing: {$field}");
            }
        }
        
        $data = array_merge($shipping_data, array(
            'order_id' => $order_id,
            'merchant_id' => $this->merchant_id,
            'ship_date' => date('Y-m-d H:i:s')
        ));
        
        return $this->makeRequest('POST', $this->endpoints['shipping'] . '/ship', $data);
    }
    
    /**
     * Cancel order
     */
    public function cancelOrder($order_id, $reason = '') {
        $this->ensureAuthenticated();
        
        $data = array(
            'order_id' => $order_id,
            'reason' => $reason,
            'merchant_id' => $this->merchant_id,
            'cancelled_at' => date('Y-m-d H:i:s')
        );
        
        return $this->makeRequest('POST', $this->endpoints['orders'] . '/' . $order_id . '/cancel', $data);
    }
    
    /**
     * Category and Brand Methods
     */
    
    /**
     * Get categories
     */
    public function getCategories($parent_id = null) {
        $this->ensureAuthenticated();
        
        $params = array();
        if ($parent_id !== null) {
            $params['parent_id'] = $parent_id;
        }
        
        return $this->makeRequest('GET', $this->endpoints['categories'], $params);
    }
    
    /**
     * Get category details
     */
    public function getCategory($category_id) {
        $this->ensureAuthenticated();
        
        return $this->makeRequest('GET', $this->endpoints['categories'] . '/' . $category_id);
    }
    
    /**
     * Get brands
     */
    public function getBrands($filters = array()) {
        $this->ensureAuthenticated();
        
        return $this->makeRequest('GET', $this->endpoints['brands'], $filters);
    }
    
    /**
     * Get brand details
     */
    public function getBrand($brand_id) {
        $this->ensureAuthenticated();
        
        return $this->makeRequest('GET', $this->endpoints['brands'] . '/' . $brand_id);
    }
    
    /**
     * Campaign Management Methods
     */
    
    /**
     * Get campaigns
     */
    public function getCampaigns($filters = array()) {
        $this->ensureAuthenticated();
        
        $params = array_merge($filters, array(
            'merchant_id' => $this->merchant_id
        ));
        
        return $this->makeRequest('GET', $this->endpoints['campaigns'], $params);
    }
    
    /**
     * Create campaign
     */
    public function createCampaign($campaign_data) {
        $this->ensureAuthenticated();
        
        $campaign_data['merchant_id'] = $this->merchant_id;
        $campaign_data = $this->formatCampaignData($campaign_data);
        
        return $this->makeRequest('POST', $this->endpoints['campaigns'], $campaign_data);
    }
    
    /**
     * Update campaign
     */
    public function updateCampaign($campaign_id, $campaign_data) {
        $this->ensureAuthenticated();
        
        $campaign_data['merchant_id'] = $this->merchant_id;
        $campaign_data = $this->formatCampaignData($campaign_data);
        
        return $this->makeRequest('PUT', $this->endpoints['campaigns'] . '/' . $campaign_id, $campaign_data);
    }
    
    /**
     * Analytics and Reporting Methods
     */
    
    /**
     * Get analytics data
     */
    public function getAnalytics($metrics = array(), $date_range = array()) {
        $this->ensureAuthenticated();
        
        $params = array(
            'merchant_id' => $this->merchant_id,
            'metrics' => is_array($metrics) ? implode(',', $metrics) : $metrics,
            'start_date' => $date_range['start_date'] ?? date('Y-m-d', strtotime('-30 days')),
            'end_date' => $date_range['end_date'] ?? date('Y-m-d')
        );
        
        return $this->makeRequest('GET', $this->endpoints['analytics'], $params);
    }
    
    /**
     * Get performance report
     */
    public function getPerformanceReport($report_type = 'sales', $date_range = array()) {
        $this->ensureAuthenticated();
        
        $params = array(
            'merchant_id' => $this->merchant_id,
            'report_type' => $report_type,
            'start_date' => $date_range['start_date'] ?? date('Y-m-d', strtotime('-30 days')),
            'end_date' => $date_range['end_date'] ?? date('Y-m-d')
        );
        
        return $this->makeRequest('GET', $this->endpoints['reports'] . '/performance', $params);
    }
    
    /**
     * Customer and Review Methods
     */
    
    /**
     * Get customer reviews
     */
    public function getReviews($product_id = null, $filters = array()) {
        $this->ensureAuthenticated();
        
        $params = array_merge($filters, array(
            'merchant_id' => $this->merchant_id
        ));
        
        if ($product_id) {
            $params['product_id'] = $product_id;
        }
        
        return $this->makeRequest('GET', $this->endpoints['reviews'], $params);
    }
    
    /**
     * Respond to review
     */
    public function respondToReview($review_id, $response_text) {
        $this->ensureAuthenticated();
        
        $data = array(
            'review_id' => $review_id,
            'response' => $response_text,
            'merchant_id' => $this->merchant_id,
            'response_date' => date('Y-m-d H:i:s')
        );
        
        return $this->makeRequest('POST', $this->endpoints['reviews'] . '/' . $review_id . '/respond', $data);
    }
    
    /**
     * Webhook Management
     */
    
    /**
     * Register webhook
     */
    public function registerWebhook($webhook_url, $events = array()) {
        $this->ensureAuthenticated();
        
        $data = array(
            'webhook_url' => $webhook_url,
            'events' => $events,
            'merchant_id' => $this->merchant_id,
            'secret' => $this->generateWebhookSecret()
        );
        
        return $this->makeRequest('POST', $this->endpoints['webhooks'], $data);
    }
    
    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($payload, $signature, $secret) {
        $expected_signature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected_signature, $signature);
    }
    
    /**
     * Private Helper Methods
     */
    
    /**
     * Make HTTP request to Pazarama API
     */
    private function makeRequest($method, $endpoint, $data = array()) {
        // Rate limiting
        $this->enforceRateLimit();
        
        $url = $this->base_url . $endpoint;
        
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->getHeaders(),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ));
        
        if (in_array($method, ['POST', 'PUT', 'PATCH']) && !empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'GET' && !empty($data)) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            throw new Exception('cURL Error: ' . $curl_error);
        }
        
        $decoded_response = json_decode($response, true);
        
        if ($http_code >= 400) {
            $error_message = $decoded_response['message'] ?? 'HTTP Error ' . $http_code;
            throw new Exception($error_message, $http_code);
        }
        
        return $decoded_response;
    }
    
    /**
     * Get HTTP headers for API requests
     */
    private function getHeaders() {
        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: MesChain-Sync/4.0'
        );
        
        if (isset($this->access_token)) {
            $headers[] = 'Authorization: Bearer ' . $this->access_token;
        }
        
        return $headers;
    }
    
    /**
     * Ensure user is authenticated
     */
    private function ensureAuthenticated() {
        if (!isset($this->access_token) || time() >= $this->token_expires) {
            $this->authenticate();
        }
    }
    
    /**
     * Enforce rate limiting
     */
    private function enforceRateLimit() {
        $time_since_last_request = microtime(true) - $this->last_request_time;
        $min_interval = 1.0 / $this->rate_limit;
        
        if ($time_since_last_request < $min_interval) {
            usleep(($min_interval - $time_since_last_request) * 1000000);
        }
        
        $this->last_request_time = microtime(true);
    }
    
    /**
     * Generate API signature
     */
    private function generateSignature($data) {
        ksort($data);
        $string_to_sign = http_build_query($data);
        return hash_hmac('sha256', $string_to_sign, $this->api_secret);
    }
    
    /**
     * Format product data for API
     */
    private function formatProductData($product_data) {
        // Format prices
        if (isset($product_data['list_price'])) {
            $product_data['list_price'] = number_format($product_data['list_price'], 2, '.', '');
        }
        
        if (isset($product_data['sale_price'])) {
            $product_data['sale_price'] = number_format($product_data['sale_price'], 2, '.', '');
        }
        
        // Format stock quantity
        if (isset($product_data['stock_quantity'])) {
            $product_data['stock_quantity'] = (int)$product_data['stock_quantity'];
        }
        
        // Format attributes
        if (isset($product_data['attributes']) && is_array($product_data['attributes'])) {
            $product_data['attributes'] = $this->formatProductAttributes($product_data['attributes']);
        }
        
        return $product_data;
    }
    
    /**
     * Format campaign data for API
     */
    private function formatCampaignData($campaign_data) {
        // Format dates
        if (isset($campaign_data['start_date'])) {
            $campaign_data['start_date'] = date('Y-m-d H:i:s', strtotime($campaign_data['start_date']));
        }
        
        if (isset($campaign_data['end_date'])) {
            $campaign_data['end_date'] = date('Y-m-d H:i:s', strtotime($campaign_data['end_date']));
        }
        
        // Format budget
        if (isset($campaign_data['budget'])) {
            $campaign_data['budget'] = number_format($campaign_data['budget'], 2, '.', '');
        }
        
        return $campaign_data;
    }
    
    /**
     * Process product images
     */
    private function processProductImages($images) {
        $processed_images = array();
        
        foreach ($images as $image) {
            if (is_string($image)) {
                // URL
                $processed_images[] = array(
                    'url' => $image,
                    'is_main' => count($processed_images) === 0
                );
            } elseif (is_array($image)) {
                // Detailed image data
                $processed_images[] = $image;
            }
        }
        
        return $processed_images;
    }
    
    /**
     * Format product attributes
     */
    private function formatProductAttributes($attributes) {
        $formatted_attributes = array();
        
        foreach ($attributes as $key => $value) {
            $formatted_attributes[] = array(
                'attribute_name' => $key,
                'attribute_value' => $value
            );
        }
        
        return $formatted_attributes;
    }
    
    /**
     * Generate webhook secret
     */
    private function generateWebhookSecret() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Log error messages
     */
    private function logError($type, $message) {
        if ($this->log) {
            $this->log->write("PAZARAMA_API_{$type}: {$message}");
        }
    }
    
    /**
     * Get API status
     */
    public function getApiStatus() {
        try {
            $response = $this->makeRequest('GET', '/api/v1/status');
            return array(
                'status' => 'online',
                'response_time' => $response['response_time'] ?? null,
                'server_time' => $response['server_time'] ?? date('Y-m-d H:i:s')
            );
        } catch (Exception $e) {
            return array(
                'status' => 'offline',
                'error' => $e->getMessage()
            );
        }
    }
} 