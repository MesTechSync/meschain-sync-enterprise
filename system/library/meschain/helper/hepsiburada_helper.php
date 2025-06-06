<?php
/**
 * Hepsiburada Helper Class
 * MesChain-Sync OpenCart Extension
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0
 * @author MesChain Development Team
 */

class HepsiburadaHelper {
    
    private $api_url = 'https://mpop-sit.hepsiburada.com/api';
    private $username;
    private $password;
    private $merchant_id;
    private $access_token;
    
    /**
     * Constructor
     *
     * @param array $config Configuration array
     */
    public function __construct($config = []) {
        $this->username = isset($config['username']) ? $config['username'] : '';
        $this->password = isset($config['password']) ? $config['password'] : '';
        $this->merchant_id = isset($config['merchant_id']) ? $config['merchant_id'] : '';
        
        // Set production URL if not in test mode
        if (isset($config['production']) && $config['production']) {
            $this->api_url = 'https://mpop.hepsiburada.com/api';
        }
    }
    
    /**
     * Authenticate and get access token
     *
     * @return array Authentication result
     */
    public function authenticate() {
        try {
            $data = [
                'username' => $this->username,
                'password' => $this->password
            ];
            
            $response = $this->makeApiRequest('authenticate', 'POST', $data, false);
            
            if ($response['http_code'] === 200 && isset($response['data']['access_token'])) {
                $this->access_token = $response['data']['access_token'];
                return [
                    'success' => true,
                    'token' => $this->access_token
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Authentication failed',
                'details' => $response['data']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Make API request to Hepsiburada
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $data Request data
     * @param bool $require_auth Whether authentication is required
     * @return array API response
     */
    public function makeApiRequest($endpoint, $method = 'GET', $data = [], $require_auth = true) {
        $url = $this->api_url . '/' . ltrim($endpoint, '/');
        
        $headers = [
            'Content-Type: application/json',
            'User-Agent: MesChain-Sync/3.0.4.0'
        ];
        
        if ($require_auth && $this->access_token) {
            $headers[] = 'Authorization: Bearer ' . $this->access_token;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        $decoded_response = json_decode($response, true);
        
        return [
            'http_code' => $http_code,
            'data' => $decoded_response,
            'raw_response' => $response
        ];
    }
    
    /**
     * Get categories
     *
     * @return array Categories list
     */
    public function getCategories() {
        try {
            $response = $this->makeApiRequest('categories');
            
            if ($response['http_code'] === 200) {
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'error' => 'API Error: HTTP ' . $response['http_code'],
                'details' => $response['data']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create or update product
     *
     * @param array $product_data Product data
     * @return array API response
     */
    public function createOrUpdateProduct($product_data) {
        try {
            $formatted_data = $this->formatProductData($product_data);
            $response = $this->makeApiRequest('products', 'POST', $formatted_data);
            
            if ($response['http_code'] === 200 || $response['http_code'] === 201) {
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'error' => 'API Error: HTTP ' . $response['http_code'],
                'details' => $response['data']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get orders
     *
     * @param array $filters Filter options
     * @return array Orders data
     */
    public function getOrders($filters = []) {
        try {
            $query_params = [];
            
            if (isset($filters['status'])) {
                $query_params['status'] = $filters['status'];
            }
            
            if (isset($filters['start_date'])) {
                $query_params['startDate'] = $filters['start_date'];
            }
            
            if (isset($filters['end_date'])) {
                $query_params['endDate'] = $filters['end_date'];
            }
            
            $endpoint = 'orders';
            if (!empty($query_params)) {
                $endpoint .= '?' . http_build_query($query_params);
            }
            
            $response = $this->makeApiRequest($endpoint);
            
            if ($response['http_code'] === 200) {
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'error' => 'API Error: HTTP ' . $response['http_code'],
                'details' => $response['data']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Format product data for Hepsiburada API
     *
     * @param array $product_data Raw product data
     * @return array Formatted product data
     */
    protected function formatProductData($product_data) {
        $formatted = [
            'merchantId' => $this->merchant_id,
            'sku' => $product_data['sku'],
            'title' => $product_data['title'],
            'description' => $product_data['description'],
            'brand' => $product_data['brand'],
            'categoryId' => $product_data['category_id'],
            'price' => (float)$product_data['price'],
            'stock' => (int)$product_data['stock'],
            'barcode' => $product_data['barcode'],
            'images' => []
        ];
        
        // Add images
        if (isset($product_data['images']) && is_array($product_data['images'])) {
            foreach ($product_data['images'] as $image_url) {
                $formatted['images'][] = ['url' => $image_url];
            }
        }
        
        return $formatted;
    }
    
    /**
     * Validate product data
     *
     * @param array $product_data Product data to validate
     * @return array Validation result
     */
    public function validateProductData($product_data) {
        $errors = [];
        
        if (empty($product_data['sku'])) {
            $errors[] = 'SKU is required';
        }
        
        if (empty($product_data['title'])) {
            $errors[] = 'Title is required';
        }
        
        if (empty($product_data['description'])) {
            $errors[] = 'Description is required';
        }
        
        if (empty($product_data['brand'])) {
            $errors[] = 'Brand is required';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Parse webhook data
     *
     * @param string $webhook_data Raw webhook data
     * @return array Parsed webhook data
     */
    public function parseWebhookData($webhook_data) {
        try {
            $data = json_decode($webhook_data, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'error' => 'Invalid JSON data'
                ];
            }
            
            return [
                'success' => true,
                'event' => isset($data['eventType']) ? $data['eventType'] : null,
                'data' => $data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}<?php
/**
 * Hepsiburada Helper Class
 * MesChain-Sync OpenCart Extension
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0
 * @author MesChain Development Team
 */

class HepsiburadaHelper {
    
    private $api_url = 'https://mpop-sit.hepsiburada.com/api';
    private $username;
    private $password;
    private $merchant_id;
    private $access_token;
    
    /**
     * Constructor
     *
     * @param array $config Configuration array
     */
    public function __construct($config = []) {
        $this->username = isset($config['username']) ? $config['username'] : '';
        $this->password = isset($config['password']) ? $config['password'] : '';
        $this->merchant_id = isset($config['merchant_id']) ? $config['merchant_id'] : '';
        
        // Set production URL if not in test mode
        if (isset($config['production']) && $config['production']) {
            $this->api_url = 'https://mpop.hepsiburada.com/api';
        }
    }
    
    /**
     * Authenticate and get access token
     *
     * @return array Authentication result
     */
    public function authenticate() {
        try {
            $data = [
                'username' => $this->username,
                'password' => $this->password
            ];
            
            $response = $this->makeApiRequest('authenticate', 'POST', $data, false);
            
            if ($response['http_code'] === 200 && isset($response['data']['access_token'])) {
                $this->access_token = $response['data']['access_token'];
                return [
                    'success' => true,
                    'token' => $this->access_token
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Authentication failed',
                'details' => $response['data']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Make API request to Hepsiburada
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $data Request data
     * @param bool $require_auth Whether authentication is required
     * @return array API response
     */
    public function makeApiRequest($endpoint, $method = 'GET', $data = [], $require_auth = true) {
        $url = $this->api_url . '/' . ltrim($endpoint, '/');
        
        $headers = [
            'Content-Type: application/json',
            'User-Agent: MesChain-Sync/3.0.4.0'
        ];
        
        if ($require_auth && $this->access_token) {
            $headers[] = 'Authorization: Bearer ' . $this->access_token;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        $decoded_response = json_decode($response, true);
        
        return [
            'http_code' => $http_code,
            'data' => $decoded_response,
            'raw_response' => $response
        ];
    }
    
    /**
     * Get categories
     *
     * @return array Categories list
     */
    public function getCategories() {
        try {
            $response = $this->makeApiRequest('categories');
            
            if ($response['http_code'] === 200) {
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'error' => 'API Error: HTTP ' . $response['http_code'],
                'details' => $response['data']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get category attributes
     *
     * @param int $category_id Category ID
     * @return array Category attributes
     */
    public function getCategoryAttributes($category_id) {
        try {
            $response = $this->makeApiRequest('categories/' . $category_id . '/attributes');
            
            if ($response['http_code'] === 200) {
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'error' => 'API Error: HTTP ' . $response['http_code'],
                'details' => $response['data']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create or update product
     *
     * @param array $product_data Product data
     * @return array API response
     */
    public function createOrUpdateProduct($product_data) {
        try {
            $formatted_data = $this->formatProductData($product_data);
            $response = $this->makeApiRequest('products', 'POST', $formatted_data);
            
            if ($response['http_code'] === 200 || $response['http_code'] === 201) {
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'error' => 'API Error: HTTP ' . $response['http_code'],
                'details' => $response['data']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update product stock and price
     *
     * @param array $updates Stock and price updates
     * @return array API response
     */
    public function updateStockAndPrice($updates) {
        try {
            $response = $this->makeApiRequest('products/stock-price', 'PUT', $updates);
            
            if ($response['http_code'] === 200) {
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'error' => 'API Error: HTTP ' . $response['http_code'],
                'details' => $response['data']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get orders
     *
     * @param array $filters Filter options
     * @return array Orders data
     */
    public function getOrders($filters = []) {
        try {
            $query_params = [];
            
            if (isset($filters['status'])) {
                $query_params['status'] = $filters['status'];
            }
            
            if (isset($filters['start_date'])) {
                $query_params['startDate'] = $filters['start_date'];
            }
            
            if (isset($filters['end_date'])) {
                $query_params['endDate'] = $filters['end_date'];
            }
            
            if (isset($filters['page'])) {
                $query_params['page'] = $filters['page'];
            }
            
            if (isset($filters['size'])) {
                $query_params['size'] = $filters['size'];
            }
            
            $endpoint = 'orders';
            if (!empty($query_params)) {
                $endpoint .= '?' . http_build_query($query_params);
            }
            
            $response = $this->makeApiRequest($endpoint);
            
            if ($response['http_code'] === 200) {
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'error' => 'API Error: HTTP ' . $response['http_code'],
                'details' => $response['data']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update order status
     *
     * @param string $order_id Order ID
     * @param string $status New status
     * @param array $tracking_info Tracking information
     * @return array API response
     */
    public function updateOrderStatus($order_id, $status, $tracking_info = []) {
        try {
            $data = [
                'status' => $status
            ];
            
            if (!empty($tracking_info)) {
                $data['trackingInfo'] = $tracking_info;
            }
            
            $response = $this->makeApiRequest('orders/' . $order_id . '/status', 'PUT', $data);
            
            if ($response['http_code'] === 200) {
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'error' => 'API Error: HTTP ' . $response['http_code'],
                'details' => $response['data']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get product performance data
     *
     * @param array $filters Filter options
     * @return array Performance data
     */
    public function getProductPerformance($filters = []) {
        try {
            $query_params = [];
            
            if (isset($filters['sku'])) {
                $query_params['sku'] = $filters['sku'];
            }
            
            if (isset($filters['start_date'])) {
                $query_params['startDate'] = $filters['start_date'];
            }
            
            if (isset($filters['end_date'])) {
                $query_params['endDate'] = $filters['end_date'];
            }
            
            $endpoint = 'products/performance';
            if (!empty($query_params)) {
                $endpoint .= '?' . http_build_query($query_params);
            }
            
            $response = $this->makeApiRequest($endpoint);
            
            if ($response['http_code'] === 200) {
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'error' => 'API Error: HTTP ' . $response['http_code'],
                'details' => $response['data']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Format product data for Hepsiburada API
     *
     * @param array $product_data Raw product data
     * @return array Formatted product data
     */
    protected function formatProductData($product_data) {
        $formatted = [
            'merchantId' => $this->merchant_id,
            'sku' => $product_data['sku'],
            'title' => $product_data['title'],
            'description' => $product_data['description'],
            'brand' => $product_data['brand'],
            'categoryId' => $product_data['category_id'],
            'price' => (float)$product_data['price'],
            'stock' => (int)$product_data['stock'],
            'barcode' => $product_data['barcode'],
            'images' => []
        ];
        
        // Add images
        if (isset($product_data['images']) && is_array($product_data['images'])) {
            foreach ($product_data['images'] as $image_url) {
                $formatted['images'][] = ['url' => $image_url];
            }
        }
        
        // Add attributes
        if (isset($product_data['attributes']) && is_array($product_data['attributes'])) {
            $formatted['attributes'] = [];
            foreach ($product_data['attributes'] as $attribute) {
                $formatted['attributes'][] = [
                    'attributeId' => $attribute['id'],
                    'value' => $attribute['value']
                ];
            }
        }
        
        // Add variant information if exists
        if (isset($product_data['variants']) && is_array($product_data['variants'])) {
            $formatted['variants'] = $product_data['variants'];
        }
        
        return $formatted;
    }
    
    /**
     * Validate product data
     *
     * @param array $product_data Product data to validate
     * @return array Validation result
     */
    public function validateProductData($product_data) {
        $errors = [];
        
        if (empty($product_data['sku'])) {
            $errors[] = 'SKU is required';
        }
        
        if (empty($product_data['title'])) {
            $errors[] = 'Title is required';
        }
        
        if (empty($product_data['description'])) {
            $errors[] = 'Description is required';
        }
        
        if (empty($product_data['brand'])) {
            $errors[] = 'Brand is required';
        }
        
        if (empty($product_data['category_id'])) {
            $errors[] = 'Category ID is required';
        }
        
        if (empty($product_data['price']) || $product_data['price'] <= 0) {
            $errors[] = 'Valid price is required';
        }
        
        if (!isset($product_data['stock']) || $product_data['stock'] < 0) {
            $errors[] = 'Valid stock quantity is required';
        }
        
        if (empty($product_data['barcode'])) {
            $errors[] = 'Barcode is required';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Get available order statuses
     *
     * @return array Order statuses
     */
    public function getOrderStatuses() {
        return [
            'Received' => 'Alındı',
            'Processing' => 'İşleniyor',
            'Shipped' => 'Kargoya Verildi',
            'Delivered' => 'Teslim Edildi',
            'Cancelled' => 'İptal Edildi',
            'Returned' => 'İade Edildi'
        ];
    }
    
    /**
     * Get webhook events
     *
     * @return array Available webhook events
     */
    public function getWebhookEvents() {
        return [
            'OrderCreated',
            'OrderCancelled',
            'OrderStatusChanged',
            'ProductStockChanged',
            'ProductPriceChanged',
            'ReturnRequested'
        ];
    }
    
    /**
     * Parse webhook data
     *
     * @param string $webhook_data Raw webhook data
     * @return array Parsed webhook data
     */
    public function parseWebhookData($webhook_data) {
        try {
            $data = json_decode($webhook_data, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'error' => 'Invalid JSON data'
                ];
            }
            
            return [
                'success' => true,
                'event' => isset($data['eventType']) ? $data['eventType'] : null,
                'data' => $data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get commission rates
     *
     * @param int $category_id Category ID
     * @return array Commission information
     */
    public function getCommissionRates($category_id = null) {
        try {
            $endpoint = 'commission-rates';
            if ($category_id) {
                $endpoint .= '?categoryId=' . $category_id;
            }
            
            $response = $this->makeApiRequest($endpoint);
            
            if ($response['http_code'] === 200) {
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            }
            
            return [
                'success' => false,
                'error' => 'API Error: HTTP ' . $response['http_code'],
                'details' => $response['data']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} 