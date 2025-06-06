<?php

class N11ApiClient {
    private $apiKey;
    private $apiSecret;
    private $baseUrl = 'https://api.n11.com/ws/v1.1/'; // Using v1.1 for better support
    private $cache;

    /**
     * @param array $credentials API credentials (apiKey, apiSecret)
     * @param object|null $cache OpenCart cache object
     */
    public function __construct($credentials = [], $cache = null) {
        $this->apiKey = $credentials['api_key'] ?? '';
        $this->apiSecret = $credentials['api_secret'] ?? '';
        $this->cache = $cache;
    }

    /**
     * Perform a request to the N11 API.
     *
     * @param string $service The service name (e.g., 'ProductService', 'CityService').
     * @param string $method The method name (e.g., 'GetCities', 'SaveProduct').
     * @param array $params The parameters for the API call.
     * @return array The API response decoded as an associative array.
     * @throws Exception If the request fails or returns an error.
     */
    public function request($service, $method, $params = []) {
        if (empty($this->apiKey) || empty($this.apiSecret)) {
            throw new \Exception('N11 API key and secret are required.');
        }

        $url = $this->baseUrl . $service . '.json';
        
        // N11 requires authentication details within the request body
        $auth = [
            'appKey' => $this->apiKey,
            'appSecret' => $this->apiSecret
        ];
        
        $body = [
            'auth' => $auth,
        ];
        // Merge actual method parameters
        $body = array_merge($body, $params);

        $headers = [
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json',
            'User-Agent: Meschain-Sync/2.0'
        ];

        $ch = curl_init();
        
        // The service and method are part of the URL path in some N11 versions,
        // but here we assume a more RESTful approach where it might be part of the endpoint path.
        // The provided helper had a different URL for each service, so we will adapt.
        // N11's actual SOAP/REST API is complex. This is a simplified client.
        // For real use, a proper N11 SDK or more detailed implementation is needed.
        // The URL will be constructed like: https://api.n11.com/ws/v1.1/{service}.json/{method} - this is a guess.
        // A more realistic URL for many services is just the service name.
        $url = $this->baseUrl . str_replace('Service', '', $service) . '/' . $method; // e.g. /product/saveProduct
        
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $service . '.json'); // Reverting to a simpler URL structure
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // Enforce SSL certificate verification for security
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL Error to N11: ' . $error);
        }

        curl_close($ch);
        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 400 || (isset($decodedResponse['result']['status']) && $decodedResponse['result']['status'] !== 'success')) {
            $errorMessage = 'N11 API Error';
            if (isset($decodedResponse['result']['errorMessage'])) {
                $errorMessage .= ': ' . $decodedResponse['result']['errorMessage'];
            } elseif (isset($decodedResponse['fault']['faultstring'])) {
                $errorMessage .= ': ' . $decodedResponse['fault']['faultstring'];
            }
            throw new \Exception($errorMessage, $httpCode);
        }

        return $decodedResponse;
    }
    
    /**
     * Tests the connection to the N11 API.
     *
     * @return bool True if connection is successful, false otherwise.
     */
    public function testConnection() {
        try {
            $this->request('CityService', 'GetCities');
            return true;
        } catch (\Exception $e) {
            error_log('N11 API Connection Test Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get product details from N11 by seller code.
     *
     * @param string $sellerCode The unique seller code for the product.
     * @return array|null The product data if found, otherwise null.
     */
    public function getProductBySellerCode($sellerCode)
    {
        $cacheKey = 'n11.product.' . md5($sellerCode);

        if ($this->cache) {
            $cachedData = $this->cache->get($cacheKey);
            if ($cachedData) {
                return $cachedData;
            }
        }
        
        try {
            $params = [
                'productSellerCode' => $sellerCode
            ];
            // N11 API uses service and method names
            $response = $this->request('ProductService', 'GetProductByProductSellerCode', $params);

            if (!empty($response['product'])) {
                $productData = $response['product'];
                if ($this->cache) {
                    $this->cache->set($cacheKey, $productData, 300); // Cache for 5 minutes
                }
                return $productData;
            }

            return null;
        } catch (\Exception $e) {
            error_log('N11 API Get Product by Seller Code Failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create an order on N11.
     * Note: N11 API for direct order creation by seller is not standard.
     * This is a conceptual method. Usually, orders are accepted, not created.
     * This method might map to an "accept order" or "prepare for shipment" call.
     *
     * @param array $orderData
     * @return array
     * @throws \Exception
     */
    public function createOrder($orderData)
    {
        try {
            // This is a placeholder for what would be a complex process.
            // For example, marking items in an existing order as ready to ship.
            $params = [
                'orderId' => $orderData['id'], // Assuming we get an N11 order ID to accept
                'numberOfPackages' => 1
            ];
            $response = $this->request('ShipmentService', 'createOrUpdateShipmentTemplate', $params);

            return $response;
        } catch (\Exception $e) {
            error_log('N11 API Create Order Failed: ' . $e->getMessage());
            throw $e;
        }
    }

    // We can move helper functions like `optimizeForTurkishMarket` here as public methods if needed.
} 