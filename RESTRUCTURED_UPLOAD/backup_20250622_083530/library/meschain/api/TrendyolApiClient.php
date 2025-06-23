<?php
/**
 * Trendyol API Client - Enterprise Level
 * MesChain-Sync Enterprise v4.5.0
 *
 * @author MesChain Development Team
 * @version 4.5.0 Enterprise
 * @copyright 2024 MesChain Technologies
 */

namespace MesChain\Api;

class TrendyolApiClient {

    private $api_key;
    private $api_secret;
    private $supplier_id;
    private $base_url;
    private $timeout;
    private $retry_count;
    private $webhook_secret;

    // Rate limiting
    private $rate_limit = [
        'requests_per_minute' => 60,
        'requests_per_hour' => 1000,
        'current_minute_count' => 0,
        'current_hour_count' => 0,
        'last_minute' => 0,
        'last_hour' => 0
    ];

    public function __construct($config = []) {
        $this->api_key = $config['api_key'] ?? '';
        $this->api_secret = $config['api_secret'] ?? '';
        $this->supplier_id = $config['supplier_id'] ?? '';
        $this->webhook_secret = $config['webhook_secret'] ?? '';

        $this->base_url = $config['test_mode'] ?? false ?
            'https://api.trendyol.com/sapigw' :
            'https://api.trendyol.com/sapigw';

        $this->timeout = $config['timeout'] ?? 30;
        $this->retry_count = $config['retry_count'] ?? 3;
    }

    /**
     * Make HTTP request to Trendyol API
     */
    public function makeRequest($endpoint, $method = 'GET', $data = null, $headers = []) {
        // Check rate limiting
        $this->checkRateLimit();

        $url = $this->base_url . $endpoint;
        $default_headers = $this->buildHeaders();
        $headers = array_merge($default_headers, $headers);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'MesChain-Trendyol-Client/4.5.0'
        ]);

        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        // Retry mechanism with exponential backoff
        $attempt = 0;
        do {
            $attempt++;
            $start_time = microtime(true);
            $response = curl_exec($ch);
            $duration = (microtime(true) - $start_time) * 1000; // milliseconds
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            // Log API call
            $this->logApiCall($endpoint, $method, $http_code, $duration, $attempt);

            if ($response !== false && $http_code < 500) {
                break; // Success or client error (4xx)
            }

            if ($attempt < $this->retry_count) {
                sleep(pow(2, $attempt)); // Exponential backoff: 2, 4, 8 seconds
            }
        } while ($attempt < $this->retry_count);

        curl_close($ch);

        if ($response === false) {
            throw new \Exception("cURL Error: " . $error);
        }

        return $this->parseResponse($response, $http_code);
    }

    /**
     * Product Management Methods
     */
    public function getProducts($page = 0, $size = 50) {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/products?page={$page}&size={$size}");
    }

    public function createProduct($product_data) {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/v2/products", 'POST', $product_data);
    }

    public function updateProduct($barcode, $product_data) {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/products/price-and-inventory", 'POST', $product_data);
    }

    public function deleteProduct($barcode) {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/products/{$barcode}", 'DELETE');
    }

    public function getProductByBarcode($barcode) {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/products?barcode={$barcode}");
    }

    /**
     * Order Management Methods
     */
    public function getOrders($start_date, $end_date, $page = 0, $size = 200) {
        $params = http_build_query([
            'startDate' => $start_date,
            'endDate' => $end_date,
            'page' => $page,
            'size' => $size
        ]);

        return $this->makeRequest("/suppliers/{$this->supplier_id}/orders?{$params}");
    }

    public function getOrderByNumber($order_number) {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/orders/{$order_number}");
    }

    public function updateOrderStatus($order_id, $status_data) {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/orders/{$order_id}/status", 'PUT', $status_data);
    }

    public function shipOrder($order_id, $shipment_data) {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/orders/{$order_id}/shipment", 'POST', $shipment_data);
    }

    /**
     * Category Management Methods
     */
    public function getCategories() {
        return $this->makeRequest("/product-categories");
    }

    public function getCategoryAttributes($category_id) {
        return $this->makeRequest("/product-categories/{$category_id}/attributes");
    }

    public function getCategoryTree() {
        return $this->makeRequest("/product-categories/tree");
    }

    /**
     * Inventory and Pricing Methods
     */
    public function updatePriceAndStock($items) {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/products/price-and-inventory", 'POST', [
            'items' => $items
        ]);
    }

    public function bulkUpdateInventory($items) {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/products/stock", 'POST', [
            'items' => $items
        ]);
    }

    public function bulkUpdatePrices($items) {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/products/price", 'POST', [
            'items' => $items
        ]);
    }

    /**
     * Webhook Management Methods
     */
    public function registerWebhook($webhook_url, $events) {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/webhooks", 'POST', [
            'url' => $webhook_url,
            'events' => $events
        ]);
    }

    public function getWebhooks() {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/webhooks");
    }

    public function deleteWebhook($webhook_id) {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/webhooks/{$webhook_id}", 'DELETE');
    }

    public function validateWebhookSignature($payload, $signature) {
        if (empty($this->webhook_secret)) {
            return false;
        }

        $expected_signature = hash_hmac('sha256', $payload, $this->webhook_secret);
        return hash_equals($signature, $expected_signature);
    }

    /**
     * Analytics and Reporting Methods
     */
    public function getSupplierStatistics() {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/statistics");
    }

    public function getProductPerformance($start_date, $end_date) {
        $params = http_build_query([
            'startDate' => $start_date,
            'endDate' => $end_date
        ]);

        return $this->makeRequest("/suppliers/{$this->supplier_id}/products/performance?{$params}");
    }

    public function getSalesReport($start_date, $end_date) {
        $params = http_build_query([
            'startDate' => $start_date,
            'endDate' => $end_date
        ]);

        return $this->makeRequest("/suppliers/{$this->supplier_id}/reports/sales?{$params}");
    }

    /**
     * Brand and Commission Methods
     */
    public function getBrands() {
        return $this->makeRequest("/brands");
    }

    public function getCommissionRates() {
        return $this->makeRequest("/suppliers/{$this->supplier_id}/commission-rates");
    }

    /**
     * Test Connection
     */
    public function testConnection() {
        try {
            $response = $this->makeRequest("/suppliers/{$this->supplier_id}/products?page=0&size=1");
            return [
                'success' => true,
                'message' => 'API connection successful',
                'supplier_id' => $this->supplier_id,
                'response_time' => $response['response_time'] ?? null
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'API connection failed: ' . $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }

    /**
     * Private Helper Methods
     */
    private function buildHeaders() {
        $auth = base64_encode($this->api_key . ':' . $this->api_secret);

        return [
            'Authorization: Basic ' . $auth,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: MesChain-Trendyol-Module/4.5.0',
        ];
    }

    private function parseResponse($response, $http_code) {
        $decoded = json_decode($response, true);

        if ($http_code >= 400) {
            $error_message = $decoded['message'] ?? $decoded['error'] ?? 'API Error';
            throw new \Exception($error_message, $http_code);
        }

        return $decoded;
    }

    private function checkRateLimit() {
        $current_time = time();
        $current_minute = floor($current_time / 60);
        $current_hour = floor($current_time / 3600);

        // Reset minute counter
        if ($current_minute != $this->rate_limit['last_minute']) {
            $this->rate_limit['current_minute_count'] = 0;
            $this->rate_limit['last_minute'] = $current_minute;
        }

        // Reset hour counter
        if ($current_hour != $this->rate_limit['last_hour']) {
            $this->rate_limit['current_hour_count'] = 0;
            $this->rate_limit['last_hour'] = $current_hour;
        }

        // Check limits
        if ($this->rate_limit['current_minute_count'] >= $this->rate_limit['requests_per_minute']) {
            throw new \Exception('Rate limit exceeded: Too many requests per minute');
        }

        if ($this->rate_limit['current_hour_count'] >= $this->rate_limit['requests_per_hour']) {
            throw new \Exception('Rate limit exceeded: Too many requests per hour');
        }

        // Increment counters
        $this->rate_limit['current_minute_count']++;
        $this->rate_limit['current_hour_count']++;
    }

    private function logApiCall($endpoint, $method, $http_code, $duration, $attempt) {
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoint' => $endpoint,
            'method' => $method,
            'http_code' => $http_code,
            'duration_ms' => round($duration, 2),
            'attempt' => $attempt,
            'success' => $http_code < 400
        ];

        // Log to file (you can customize this based on your logging system)
        error_log('[TRENDYOL_API] ' . json_encode($log_data));
    }

    /**
     * Batch Operations
     */
    public function batchOperation($operation, $items, $batch_size = 50) {
        $batches = array_chunk($items, $batch_size);
        $results = [];

        foreach ($batches as $batch_index => $batch) {
            try {
                switch ($operation) {
                    case 'update_stock':
                        $result = $this->bulkUpdateInventory($batch);
                        break;
                    case 'update_price':
                        $result = $this->bulkUpdatePrices($batch);
                        break;
                    case 'update_price_and_stock':
                        $result = $this->updatePriceAndStock($batch);
                        break;
                    default:
                        throw new \Exception("Unsupported batch operation: {$operation}");
                }

                $results[] = [
                    'batch_index' => $batch_index,
                    'success' => true,
                    'result' => $result,
                    'items_count' => count($batch)
                ];

                // Small delay between batches to respect rate limits
                if ($batch_index < count($batches) - 1) {
                    sleep(1);
                }

            } catch (\Exception $e) {
                $results[] = [
                    'batch_index' => $batch_index,
                    'success' => false,
                    'error' => $e->getMessage(),
                    'items_count' => count($batch)
                ];
            }
        }

        return $results;
    }
}
