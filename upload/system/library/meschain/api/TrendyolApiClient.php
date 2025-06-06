<?php
class TrendyolApiClient {
    private $apiKey;
    private $apiSecret;
    private $supplierId;
    private $baseUrl = 'https://api.trendyol.com/sapigw';
    private $testMode = false;
    private $cache;

    /**
     * @param array $credentials API credentials (apiKey, apiSecret, supplierId)
     * @param object|null $cache OpenCart cache object
     */
    public function __construct($credentials = [], $cache = null) {
        $this->apiKey = $credentials['api_key'] ?? '';
        $this->apiSecret = $credentials['api_secret'] ?? '';
        $this->supplierId = $credentials['supplier_id'] ?? '';
        $this->cache = $cache;
        
        if (!empty($credentials['test_mode'])) {
            $this->baseUrl = 'https://api-sandbox.trendyol.com/sapigw';
            $this->testMode = true;
        }
    }

    /**
     * Perform a request to the Trendyol API.
     *
     * @param string $endpoint The API endpoint to call.
     * @param string $method The HTTP method (GET, POST, PUT, DELETE).
     * @param array $data The data to send with POST/PUT requests.
     * @return array The API response decoded as an associative array.
     * @throws Exception If the request fails or returns an error.
     */
    public function request($endpoint, $method = 'GET', $data = []) {
        if (empty($this->apiKey) || empty($this->apiSecret)) {
            throw new \Exception('API key and secret are required.');
        }

        $url = $this->baseUrl . '/suppliers/' . $this->supplierId . $endpoint;
        
        $headers = [
            'Authorization: Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret),
            'Content-Type: application/json',
            'User-Agent: Meschain-Sync/2.0'
        ];

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        // Enforce SSL certificate verification for security
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL Error: ' . $error);
        }

        curl_close($ch);

        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMessage = 'Trendyol API Error';
            if (isset($decodedResponse['errors']) && is_array($decodedResponse['errors'])) {
                $errorMessage .= ': ' . implode(', ', array_column($decodedResponse['errors'], 'message'));
            } elseif ($response) {
                $errorMessage .= ' - ' . $response;
            }
            throw new \Exception($errorMessage, $httpCode);
        }

        return $decodedResponse;
    }

    /**
     * Tests the connection to the Trendyol API.
     *
     * @return bool True if connection is successful, false otherwise.
     */
    public function testConnection() {
        try {
            // A simple endpoint to check credentials
            $this->request('/brands?page=0&size=1');
            return true;
        } catch (\Exception $e) {
            // Log the actual error for debugging
            error_log('Trendyol API Connection Test Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get product details from Trendyol by stock code (product_id).
     *
     * @param string $stockCode The stock code (usually OpenCart product_id) to search for.
     * @return array|null The product data if found, otherwise null.
     */
    public function getProductByStockCode($stockCode)
    {
        $cacheKey = 'trendyol.product.' . md5($stockCode);
        
        if ($this->cache) {
            $cachedData = $this->cache->get($cacheKey);
            if ($cachedData) {
                return $cachedData;
            }
        }

        try {
            $endpoint = '/products?stockCode=' . urlencode($stockCode);
            $response = $this->request($endpoint);

            if (!empty($response['content'])) {
                // Assuming the first item is the one we want
                $productData = $response['content'][0];
                if ($this->cache) {
                    $this->cache->set($cacheKey, $productData, 300); // Cache for 5 minutes
                }
                return $productData;
            }

            return null;
        } catch (\Exception $e) {
            error_log('Trendyol API Get Product by Stock Code Failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new shipment package (order) on Trendyol.
     *
     * @param array $orderData Data for creating the order, including address and lines.
     * @return array The API response.
     * @throws \Exception
     */
    public function createShipmentPackage($orderData)
    {
        try {
            $endpoint = '/orders'; // This is a common endpoint, might need adjustment
            $response = $this->request($endpoint, 'POST', $orderData);

            return $response;
        } catch (\Exception $e) {
            error_log('Trendyol API Create Shipment Package Failed: ' . $e->getMessage());
            // Re-throw the exception to be handled by the caller
            throw $e;
        }
    }
} 