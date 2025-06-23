<?php

namespace MesChain\Library;

/**
 * Trendyol API Integration Class
 * Handles all Trendyol marketplace API operations
 */
class TrendyolApi
{
    private $seller_id;
    private $api_key;
    private $api_secret;
    private $token;
    private $store_id;
    private $integration_code;
    private $base_url = 'https://api.trendyol.com';
    private $sandbox_url = 'https://stageapi.trendyol.com';
    private $is_sandbox = false;
    private $request_timestamps = [];
    private $max_requests_per_window = 50;
    private $time_window = 10; // seconds

    public function __construct($config = [])
    {
        $this->seller_id = $config['seller_id'] ?? '';
        $this->api_key = $config['api_key'] ?? '';
        $this->api_secret = $config['api_secret'] ?? '';
        $this->token = $config['token'] ?? '';
        $this->store_id = $config['store_id'] ?? '';
        $this->integration_code = $config['integration_code'] ?? '';
        $this->is_sandbox = $config['sandbox'] ?? false;
    }

    /**
     * Test API connection
     */
    public function testConnection()
    {
        try {
            $response = $this->makeRequest('GET', '/sapigw/suppliers/' . $this->seller_id);
            return [
                'success' => true,
                'message' => 'Connection successful',
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get product list from Trendyol
     */
    public function getProducts($page = 0, $size = 50)
    {
        try {
            $endpoint = '/sapigw/suppliers/' . $this->seller_id . '/products';
            $params = [
                'page' => $page,
                'size' => $size
            ];

            $response = $this->makeRequest('GET', $endpoint, $params);
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Create/Update product on Trendyol
     */
    public function createProduct($product_data)
    {
        try {
            $endpoint = '/sapigw/suppliers/' . $this->seller_id . '/v2/products';

            $trendyol_product = $this->formatProductForTrendyol($product_data);

            $response = $this->makeRequest('POST', $endpoint, [], $trendyol_product);
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update product stock
     */
    public function updateStock($barcode, $quantity)
    {
        try {
            $endpoint = '/sapigw/suppliers/' . $this->seller_id . '/products/price-and-inventory';

            $stock_data = [
                'items' => [
                    [
                        'barcode' => $barcode,
                        'quantity' => (int)$quantity,
                        'salePrice' => null, // Keep existing price
                        'listPrice' => null  // Keep existing price
                    ]
                ]
            ];

            $response = $this->makeRequest('POST', $endpoint, [], $stock_data);
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get orders from Trendyol
     */
    public function getOrders($start_date = null, $end_date = null, $page = 0, $size = 200)
    {
        try {
            $endpoint = '/sapigw/suppliers/' . $this->seller_id . '/orders';

            $params = [
                'page' => $page,
                'size' => $size
            ];

            if ($start_date) {
                $params['startDate'] = $start_date;
            }
            if ($end_date) {
                $params['endDate'] = $end_date;
            }

            $response = $this->makeRequest('GET', $endpoint, $params);
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update order status
     */
    public function updateOrderStatus($order_number, $status)
    {
        try {
            $endpoint = '/sapigw/suppliers/' . $this->seller_id . '/orders/' . $order_number . '/status';

            $status_data = [
                'status' => $status
            ];

            $response = $this->makeRequest('PUT', $endpoint, [], $status_data);
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get categories from Trendyol
     */
    public function getCategories()
    {
        try {
            $endpoint = '/sapigw/product-categories';

            $response = $this->makeRequest('GET', $endpoint);
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get brands from Trendyol
     */
    public function getBrands()
    {
        try {
            $endpoint = '/sapigw/brands';

            $response = $this->makeRequest('GET', $endpoint);
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Format OpenCart product data for Trendyol API
     */
    private function formatProductForTrendyol($product_data)
    {
        return [
            'barcode' => $product_data['sku'] ?? $product_data['model'],
            'title' => $product_data['name'],
            'productMainId' => $product_data['product_id'],
            'brandId' => $product_data['brand_id'] ?? 1,
            'categoryId' => $product_data['trendyol_category_id'] ?? 1,
            'quantity' => (int)$product_data['quantity'],
            'stockCode' => $product_data['sku'] ?? $product_data['model'],
            'dimensionalWeight' => (float)($product_data['weight'] ?? 0),
            'description' => strip_tags($product_data['description'] ?? ''),
            'currencyType' => 'TRY',
            'listPrice' => (float)$product_data['price'],
            'salePrice' => (float)$product_data['special_price'] ?? (float)$product_data['price'],
            'vatRate' => (int)($product_data['tax_rate'] ?? 18),
            'cargoCompanyId' => 10, // Default cargo company
            'images' => $this->formatImages($product_data['images'] ?? []),
            'attributes' => $this->formatAttributes($product_data['attributes'] ?? [])
        ];
    }

    /**
     * Format product images for Trendyol
     */
    private function formatImages($images)
    {
        $formatted_images = [];
        foreach ($images as $index => $image) {
            $formatted_images[] = [
                'url' => $image['url'],
                'order' => $index + 1
            ];
        }
        return $formatted_images;
    }

    /**
     * Format product attributes for Trendyol
     */
    private function formatAttributes($attributes)
    {
        $formatted_attributes = [];
        foreach ($attributes as $attribute) {
            $formatted_attributes[] = [
                'attributeId' => $attribute['trendyol_attribute_id'],
                'attributeValueId' => $attribute['trendyol_value_id'],
                'customAttributeValue' => $attribute['value']
            ];
        }
        return $formatted_attributes;
    }

    /**
     * Check and enforce rate limiting
     */
    private function checkRateLimit()
    {
        $current_time = time();

        // Remove timestamps older than the time window
        $this->request_timestamps = array_filter($this->request_timestamps, function ($timestamp) use ($current_time) {
            return ($current_time - $timestamp) < $this->time_window;
        });

        // Check if we're at the limit
        if (count($this->request_timestamps) >= $this->max_requests_per_window) {
            $oldest_request = min($this->request_timestamps);
            $wait_time = $this->time_window - ($current_time - $oldest_request);

            if ($wait_time > 0) {
                // Wait to respect rate limit
                sleep($wait_time + 1);
            }
        }

        // Add current request timestamp
        $this->request_timestamps[] = $current_time;
    }

    /**
     * Make HTTP request to Trendyol API
     */
    private function makeRequest($method, $endpoint, $params = [], $data = null)
    {
        // Enforce rate limiting
        $this->checkRateLimit();

        $base_url = $this->is_sandbox ? $this->sandbox_url : $this->base_url;
        $url = $base_url . $endpoint;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        // Create proper User-Agent according to Trendyol documentation
        // Format: "SellerID - IntegrationCompanyName"
        $user_agent = $this->seller_id . ' - MesChainSync';

        $headers = [
            'Authorization: Basic ' . $this->token,
            'Content-Type: application/json',
            'User-Agent: ' . $user_agent
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception('CURL Error: ' . $error);
        }

        $decoded_response = json_decode($response, true);

        // Handle specific Trendyol error responses according to documentation
        if ($http_code >= 400) {
            $error_message = 'HTTP ' . $http_code;

            // Handle specific Trendyol error codes
            if ($http_code == 401) {
                $error_message = 'Authentication failed (401): Please check your API credentials';
                if (isset($decoded_response['exception']) && $decoded_response['exception'] == 'ClientApiAuthenticationException') {
                    $error_message .= ' - ClientApiAuthenticationException';
                }
            } elseif ($http_code == 403) {
                $error_message = 'Access forbidden (403): User-Agent header missing or invalid';
            } elseif ($http_code == 429) {
                $error_message = 'Rate limit exceeded (429): Too many requests. Please wait before retrying.';
                if (isset($decoded_response['message']) && $decoded_response['message'] == 'too.many.requests') {
                    $error_message .= ' Maximum 50 requests per 10 seconds allowed.';
                }
            } elseif (isset($decoded_response['message'])) {
                $error_message .= ': ' . $decoded_response['message'];
            }

            throw new \Exception($error_message);
        }

        return $decoded_response;
    }

    /**
     * Log API activity
     */
    private function log($message, $data = null)
    {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message,
            'data' => $data
        ];

        // Log to file or database
        error_log('Trendyol API: ' . json_encode($log_entry));
    }
}
