<?php
/**
 * Trendyol API Client - Advanced Integration
 * MesChain-Sync Enterprise v4.5.0
 *
 * @author MesChain Development Team
 * @version 4.5.0 Enterprise
 * @copyright 2024 MesChain Technologies
 */

namespace MesChain\Api;

use Exception;

class TrendyolApiClient {

    private $api_key;
    private $api_secret;
    private $supplier_id;
    private $base_url = 'https://api.trendyol.com/sapigw/suppliers';
    private $timeout = 30;
    private $test_mode = false;
    private $rate_limit_remaining = 1000;
    private $rate_limit_reset = 0;
    private $last_request_time = 0;
    private $registry;

    // Advanced features
    private $webhook_secret;
    private $auto_retry = true;
    private $max_retries = 3;
    private $cache_enabled = true;
    private $cache_ttl = 300; // 5 minutes
    private $log_enabled = true;

    public function __construct($config, $registry = null) {
        $this->api_key = $config['api_key'] ?? '';
        $this->api_secret = $config['api_secret'] ?? '';
        $this->supplier_id = $config['supplier_id'] ?? '';
        $this->test_mode = $config['test_mode'] ?? false;
        $this->webhook_secret = $config['webhook_secret'] ?? '';
        $this->registry = $registry;

        if ($this->test_mode) {
            $this->base_url = 'https://api.trendyol.com/sapigw/suppliers'; // Sandbox URL
        }

        $this->log('system', 'API_CLIENT_INIT', 'Trendyol API Client initialized');
    }

    /**
     * Get Products with Advanced Filtering
     */
    public function getProducts($params = []) {
        $endpoint = '/' . $this->supplier_id . '/products';

        $default_params = [
            'page' => 0,
            'size' => 100,
            'approved' => true,
            'archived' => false
        ];

        $params = array_merge($default_params, $params);

        $response = $this->makeRequest('GET', $endpoint, $params);

        if ($response && isset($response['content'])) {
            // Process products for better structure
            $products = [];
            foreach ($response['content'] as $product) {
                $products[] = $this->formatTrendyolProduct($product);
            }
            $response['content'] = $products;
        }

        return $response;
    }

    /**
     * Create Product with AI-Enhanced Description
     */
    public function createProduct($product_data) {
        $endpoint = '/' . $this->supplier_id . '/products';

        // Validate required fields
        $required_fields = ['title', 'barcode', 'categoryId', 'brandId', 'listPrice', 'salePrice'];
        foreach ($required_fields as $field) {
            if (empty($product_data[$field])) {
                throw new Exception("Required field missing: {$field}");
            }
        }

        // AI-enhance description if enabled
        if (!empty($product_data['ai_enhance_description']) && !empty($product_data['description'])) {
            $product_data['description'] = $this->enhanceDescriptionWithAI($product_data['description']);
        }

        // Format product for Trendyol
        $formatted_product = $this->formatProductForTrendyol($product_data);

        return $this->makeRequest('POST', $endpoint, [], $formatted_product);
    }

    /**
     * Update Product Stock and Price in Bulk
     */
    public function updateStockAndPrice($items) {
        $endpoint = '/' . $this->supplier_id . '/products/price-and-inventory';

        $data = [
            'items' => []
        ];

        foreach ($items as $item) {
            $data['items'][] = [
                'barcode' => $item['barcode'],
                'quantity' => (int)$item['quantity'],
                'listPrice' => (float)$item['listPrice'],
                'salePrice' => (float)$item['salePrice']
            ];
        }

        return $this->makeRequest('POST', $endpoint, [], $data);
    }

    /**
     * Get Orders with Advanced Filtering
     */
    public function getOrders($params = []) {
        $endpoint = '/' . $this->supplier_id . '/orders';

        $default_params = [
            'page' => 0,
            'size' => 50,
            'startDate' => strtotime('-7 days') * 1000, // Trendyol uses milliseconds
            'endDate' => time() * 1000,
            'status' => 'All'
        ];

        $params = array_merge($default_params, $params);

        $response = $this->makeRequest('GET', $endpoint, $params);

        if ($response && isset($response['content'])) {
            // Process orders for better structure
            $orders = [];
            foreach ($response['content'] as $order) {
                $orders[] = $this->formatTrendyolOrder($order);
            }
            $response['content'] = $orders;
        }

        return $response;
    }

    /**
     * Update Order Status
     */
    public function updateOrderStatus($order_id, $status, $tracking_info = []) {
        $endpoint = '/' . $this->supplier_id . '/orders/' . $order_id . '/status';

        $data = [
            'status' => $status
        ];

        if (!empty($tracking_info)) {
            $data['trackingNumber'] = $tracking_info['tracking_number'] ?? '';
            $data['cargoProvider'] = $tracking_info['cargo_provider'] ?? '';
        }

        return $this->makeRequest('PUT', $endpoint, [], $data);
    }

    /**
     * Get Categories with Caching
     */
    public function getCategories($parent_id = null) {
        $cache_key = 'trendyol_categories_' . ($parent_id ?? 'root');

        if ($this->cache_enabled) {
            $cached = $this->getFromCache($cache_key);
            if ($cached !== false) {
                return $cached;
            }
        }

        $endpoint = '/product-categories';
        $params = [];

        if ($parent_id !== null) {
            $params['parentId'] = $parent_id;
        }

        $response = $this->makeRequest('GET', $endpoint, $params);

        if ($this->cache_enabled && $response) {
            $this->saveToCache($cache_key, $response, $this->cache_ttl);
        }

        return $response;
    }

    /**
     * Get Brands with Search
     */
    public function getBrands($search = '', $page = 0, $size = 100) {
        $cache_key = 'trendyol_brands_' . md5($search . $page . $size);

        if ($this->cache_enabled && empty($search)) {
            $cached = $this->getFromCache($cache_key);
            if ($cached !== false) {
                return $cached;
            }
        }

        $endpoint = '/brands';
        $params = [
            'page' => $page,
            'size' => $size
        ];

        if (!empty($search)) {
            $params['name'] = $search;
        }

        $response = $this->makeRequest('GET', $endpoint, $params);

        if ($this->cache_enabled && $response && empty($search)) {
            $this->saveToCache($cache_key, $response, $this->cache_ttl);
        }

        return $response;
    }

    /**
     * Get Seller Statistics and Analytics
     */
    public function getSellerStatistics($date_range = 'last_30_days') {
        $endpoint = '/' . $this->supplier_id . '/statistics';

        $params = $this->prepareDateRange($date_range);

        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Webhook Management
     */
    public function setupWebhook($webhook_url, $events = []) {
        $endpoint = '/' . $this->supplier_id . '/webhooks';

        $default_events = [
            'ORDER_CREATED',
            'ORDER_CANCELLED',
            'PRODUCT_APPROVED',
            'PRODUCT_REJECTED',
            'INVENTORY_UPDATED'
        ];

        $events = !empty($events) ? $events : $default_events;

        $data = [
            'url' => $webhook_url,
            'events' => $events,
            'secret' => $this->webhook_secret
        ];

        return $this->makeRequest('POST', $endpoint, [], $data);
    }

    /**
     * Validate Webhook Signature
     */
    public function validateWebhookSignature($payload, $signature) {
        if (empty($this->webhook_secret)) {
            return false;
        }

        $expected_signature = hash_hmac('sha256', $payload, $this->webhook_secret);

        return hash_equals($expected_signature, $signature);
    }

    /**
     * Advanced Campaign Management
     */
    public function getCampaigns($status = 'ACTIVE') {
        $endpoint = '/' . $this->supplier_id . '/campaigns';

        $params = [
            'status' => $status,
            'page' => 0,
            'size' => 100
        ];

        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Create Campaign
     */
    public function createCampaign($campaign_data) {
        $endpoint = '/' . $this->supplier_id . '/campaigns';

        return $this->makeRequest('POST', $endpoint, [], $campaign_data);
    }

    /**
     * Get Complaints and Reviews
     */
    public function getComplaints($status = 'OPEN') {
        $endpoint = '/' . $this->supplier_id . '/questions';

        $params = [
            'status' => $status,
            'page' => 0,
            'size' => 50
        ];

        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Reply to Complaint/Question
     */
    public function replyToComplaint($question_id, $answer) {
        $endpoint = '/' . $this->supplier_id . '/questions/' . $question_id . '/answers';

        $data = [
            'text' => $answer
        ];

        return $this->makeRequest('POST', $endpoint, [], $data);
    }

    /**
     * AI-Enhanced Product Description Generator
     */
    private function enhanceDescriptionWithAI($description) {
        // Basic AI enhancement - can be extended with actual AI service
        $enhanced = $description;

        // Add Turkish marketplace specific keywords
        $keywords = [
            'kaliteli', 'hızlı kargo', 'güvenilir', 'orijinal',
            'garanti', 'türkiye\'de', 'en iyi fiyat'
        ];

        // Simple keyword injection (basic example)
        if (strlen($enhanced) < 200) {
            $enhanced .= ' ' . implode(', ', array_slice($keywords, 0, 3));
        }

        return $enhanced;
    }

    /**
     * Format Product for Trendyol API
     */
    private function formatProductForTrendyol($product) {
        return [
            'barcode' => $product['barcode'],
            'title' => $product['title'],
            'productMainId' => $product['productMainId'] ?? '',
            'brandId' => (int)$product['brandId'],
            'categoryId' => (int)$product['categoryId'],
            'quantity' => (int)$product['quantity'],
            'stockCode' => $product['stockCode'] ?? $product['barcode'],
            'dimensionalWeight' => (float)($product['dimensionalWeight'] ?? 1),
            'description' => $product['description'],
            'currencyType' => 'TRY',
            'listPrice' => (float)$product['listPrice'],
            'salePrice' => (float)$product['salePrice'],
            'vatRate' => (int)($product['vatRate'] ?? 18),
            'cargoCompanyId' => (int)($product['cargoCompanyId'] ?? 1),
            'images' => $product['images'] ?? [],
            'attributes' => $product['attributes'] ?? []
        ];
    }

    /**
     * Format Trendyol Product Response
     */
    private function formatTrendyolProduct($product) {
        return [
            'id' => $product['id'] ?? '',
            'barcode' => $product['barcode'] ?? '',
            'title' => $product['title'] ?? '',
            'description' => $product['description'] ?? '',
            'brand' => $product['brand'] ?? '',
            'categoryId' => $product['categoryId'] ?? 0,
            'categoryName' => $product['categoryName'] ?? '',
            'listPrice' => (float)($product['listPrice'] ?? 0),
            'salePrice' => (float)($product['salePrice'] ?? 0),
            'stockQuantity' => (int)($product['stockQuantity'] ?? 0),
            'images' => $product['images'] ?? [],
            'status' => $product['approved'] ? 'active' : 'passive',
            'approvalStatus' => $product['onSale'] ? 'approved' : 'waiting',
            'lastUpdated' => $product['lastPriceChangeDate'] ?? date('c'),
            'salesCount' => (int)($product['pimSku'] ?? 0),
            'viewCount' => 0 // Not provided by Trendyol API
        ];
    }

    /**
     * Format Trendyol Order Response
     */
    private function formatTrendyolOrder($order) {
        return [
            'orderNumber' => $order['orderNumber'] ?? '',
            'grossAmount' => (float)($order['grossAmount'] ?? 0),
            'totalDiscount' => (float)($order['totalDiscount'] ?? 0),
            'totalTyDiscount' => (float)($order['totalTyDiscount'] ?? 0),
            'taxNumber' => $order['taxNumber'] ?? '',
            'invoiceAddress' => $order['invoiceAddress'] ?? [],
            'shippingAddress' => $order['shippingAddress'] ?? [],
            'orderDate' => $order['orderDate'] ?? 0,
            'tcIdentityNumber' => $order['tcIdentityNumber'] ?? '',
            'currencyCode' => $order['currencyCode'] ?? 'TRY',
            'packageHistories' => $order['packageHistories'] ?? [],
            'shipmentAddress' => $order['shipmentAddress'] ?? [],
            'status' => $order['status'] ?? '',
            'deliveryType' => $order['deliveryType'] ?? '',
            'timeSlotId' => $order['timeSlotId'] ?? null,
            'estimatedDeliveryStartDate' => $order['estimatedDeliveryStartDate'] ?? 0,
            'estimatedDeliveryEndDate' => $order['estimatedDeliveryEndDate'] ?? 0,
            'totalPrice' => (float)($order['totalPrice'] ?? 0),
            'lines' => $order['lines'] ?? []
        ];
    }

    /**
     * Prepare Date Range Parameters
     */
    private function prepareDateRange($range) {
        $end_time = time() * 1000; // Trendyol uses milliseconds

        switch ($range) {
            case 'today':
                $start_time = strtotime('today') * 1000;
                break;
            case 'yesterday':
                $start_time = strtotime('yesterday') * 1000;
                $end_time = strtotime('today') * 1000;
                break;
            case 'last_7_days':
                $start_time = strtotime('-7 days') * 1000;
                break;
            case 'last_30_days':
            default:
                $start_time = strtotime('-30 days') * 1000;
                break;
            case 'last_90_days':
                $start_time = strtotime('-90 days') * 1000;
                break;
        }

        return [
            'startDate' => $start_time,
            'endDate' => $end_time
        ];
    }

    /**
     * Main HTTP Request Method with Rate Limiting and Retry Logic
     */
    private function makeRequest($method, $endpoint, $params = [], $data = []) {
        // Rate limiting
        $this->enforceRateLimit();

        $url = $this->base_url . $endpoint;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $headers = [
            'Authorization: Basic ' . base64_encode($this->api_key . ':' . $this->api_secret),
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: MesChain-Sync/4.5.0'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CUSTOMREQUEST => $method
        ]);

        if ($method === 'POST' || $method === 'PUT') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $attempts = 0;
        $max_attempts = $this->auto_retry ? $this->max_retries : 1;

        do {
            $attempts++;
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);

            if ($curl_error) {
                $this->log('error', 'CURL_ERROR', "cURL Error: {$curl_error}");
                if ($attempts < $max_attempts) {
                    sleep(pow(2, $attempts)); // Exponential backoff
                    continue;
                }
                curl_close($ch);
                throw new Exception("cURL Error: {$curl_error}");
            }

            // Update rate limit info from headers
            $this->updateRateLimitInfo($ch);

            if ($http_code >= 200 && $http_code < 300) {
                curl_close($ch);
                $decoded = json_decode($response, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON response: ' . json_last_error_msg());
                }

                $this->log('success', 'API_CALL', "Successful {$method} to {$endpoint}");
                return $decoded;
            }

            // Handle specific HTTP errors
            if ($http_code === 429) {
                $this->log('warning', 'RATE_LIMIT', 'Rate limit exceeded, retrying...');
                if ($attempts < $max_attempts) {
                    sleep(60); // Wait 1 minute for rate limit reset
                    continue;
                }
            }

            if ($http_code >= 500 && $attempts < $max_attempts) {
                $this->log('warning', 'SERVER_ERROR', "Server error {$http_code}, retrying...");
                sleep(pow(2, $attempts)); // Exponential backoff
                continue;
            }

            curl_close($ch);

            $error_response = json_decode($response, true);
            $error_message = $error_response['message'] ?? "HTTP Error {$http_code}";

            $this->log('error', 'API_ERROR', "API Error: {$http_code} - {$error_message}");
            throw new Exception("API Error: {$http_code} - {$error_message}");

        } while ($attempts < $max_attempts);

        curl_close($ch);
        throw new Exception('Max retry attempts exceeded');
    }

    /**
     * Enforce Rate Limiting
     */
    private function enforceRateLimit() {
        $current_time = time();

        // If rate limit is exceeded, wait until reset
        if ($this->rate_limit_remaining <= 0 && $current_time < $this->rate_limit_reset) {
            $wait_time = $this->rate_limit_reset - $current_time;
            $this->log('info', 'RATE_LIMIT_WAIT', "Waiting {$wait_time} seconds for rate limit reset");
            sleep($wait_time);
        }

        // Minimum delay between requests (100ms)
        $time_since_last = $current_time - $this->last_request_time;
        if ($time_since_last < 0.1) {
            usleep((0.1 - $time_since_last) * 1000000);
        }

        $this->last_request_time = time();
    }

    /**
     * Update Rate Limit Information from Response Headers
     */
    private function updateRateLimitInfo($ch) {
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $response = curl_multi_getcontent($ch);
        $headers = substr($response, 0, $header_size);

        if (preg_match('/X-RateLimit-Remaining:\s*(\d+)/i', $headers, $matches)) {
            $this->rate_limit_remaining = (int)$matches[1];
        }

        if (preg_match('/X-RateLimit-Reset:\s*(\d+)/i', $headers, $matches)) {
            $this->rate_limit_reset = (int)$matches[1];
        }
    }

    /**
     * Cache Management
     */
    private function getFromCache($key) {
        if (!$this->cache_enabled) {
            return false;
        }

        $cache_file = sys_get_temp_dir() . '/trendyol_cache_' . md5($key);

        if (!file_exists($cache_file)) {
            return false;
        }

        $cache_data = json_decode(file_get_contents($cache_file), true);

        if (!$cache_data || $cache_data['expires'] < time()) {
            unlink($cache_file);
            return false;
        }

        return $cache_data['data'];
    }

    /**
     * Save to Cache
     */
    private function saveToCache($key, $data, $ttl) {
        if (!$this->cache_enabled) {
            return;
        }

        $cache_file = sys_get_temp_dir() . '/trendyol_cache_' . md5($key);

        $cache_data = [
            'data' => $data,
            'expires' => time() + $ttl
        ];

        file_put_contents($cache_file, json_encode($cache_data));
    }

    /**
     * Logging Method
     */
    private function log($level, $action, $message) {
        if (!$this->log_enabled) {
            return;
        }

        $log_message = sprintf(
            "[%s] [%s] [%s] %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $action,
            $message
        );

        $log_file = defined('DIR_LOGS') ? DIR_LOGS . 'trendyol_api.log' : sys_get_temp_dir() . '/trendyol_api.log';

        file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
    }

    /**
     * Test API Connection
     */
    public function testConnection() {
        try {
            $response = $this->getProducts(['page' => 0, 'size' => 1]);

            if ($response !== false) {
                $this->log('success', 'CONNECTION_TEST', 'API connection test successful');
                return [
                    'success' => true,
                    'message' => 'Trendyol API bağlantısı başarılı',
                    'data' => [
                        'supplier_id' => $this->supplier_id,
                        'rate_limit_remaining' => $this->rate_limit_remaining,
                        'test_mode' => $this->test_mode
                    ]
                ];
            }

            throw new Exception('API response was false');

        } catch (Exception $e) {
            $this->log('error', 'CONNECTION_TEST', 'API connection test failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Trendyol API bağlantısı başarısız: ' . $e->getMessage()
            ];
        }
    }
}
