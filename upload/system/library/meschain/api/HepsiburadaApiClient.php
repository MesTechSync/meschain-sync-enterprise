<?php

class HepsiburadaApiClient {
    private $username;
    private $password;
    private $merchantId;
    private $baseUrl = 'https://mpop.hepsiburada.com/api/';
    private $token;
    private $tokenExpiry;
    private $cache;

    /**
     * @param array $credentials API credentials (username, password, merchant_id)
     * @param object|null $cache OpenCart cache object
     */
    public function __construct($credentials = [], $cache = null) {
        $this->username = $credentials['username'] ?? '';
        $this->password = $credentials['password'] ?? '';
        $this->merchantId = $credentials['merchant_id'] ?? '';
        $this->cache = $cache;
    }

    /**
     * Authenticates with the API and retrieves a token.
     * @return bool
     * @throws Exception
     */
    private function authenticate() {
        if ($this->token && $this->tokenExpiry && $this->tokenExpiry > time()) {
            return true;
        }
        
        if (empty($this->username) || empty($this->password)) {
            throw new \Exception('Hepsiburada username and password are required for authentication.');
        }

        $authData = [
            'username' => $this->username,
            'password' => $this->password,
            'authentication_type' => 'MERCHANT' // or 'INTEGRATOR'
        ];

        $ch = curl_init($this->baseUrl . 'authenticate');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($authData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL Error during Hepsiburada authentication: ' . $error);
        }
        
        $decodedResponse = json_decode($response, true);
        curl_close($ch);

        if ($httpCode === 200 && !empty($decodedResponse['id_token'])) {
            $this->token = $decodedResponse['id_token'];
            $this->tokenExpiry = time() + 3600; // Assume 1 hour expiry
            return true;
        }

        throw new \Exception('Hepsiburada authentication failed. Response: ' . $response, $httpCode);
    }

    /**
     * Perform a request to the Hepsiburada API.
     *
     * @param string $endpoint The API endpoint (e.g., 'listings').
     * @param array $data The data to send with the request.
     * @param string $method The HTTP method (GET, POST, PUT, DELETE).
     * @return array The API response decoded as an associative array.
     * @throws Exception If the request fails or returns an error.
     */
    public function request($endpoint, $method = 'GET', $data = []) {
        $this->authenticate(); // Ensure we have a valid token

        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: Meschain-Sync/2.0'
        ];

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty($data)) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if (!empty($data)) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default: // GET
                if (!empty($data)) $url .= '?' . http_build_query($data);
                break;
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL Error to Hepsiburada: ' . $error);
        }

        curl_close($ch);
        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMessage = 'Hepsiburada API Error';
            if (!empty($decodedResponse['message'])) {
                $errorMessage .= ': ' . $decodedResponse['message'];
            }
            throw new \Exception($errorMessage, $httpCode);
        }

        return $decodedResponse;
    }

    /**
     * Tests the connection to the Hepsiburada API.
     */
    public function testConnection() {
        try {
            // A simple check is to authenticate
            return $this->authenticate();
        } catch (\Exception $e) {
            error_log('Hepsiburada API Connection Test Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get product details from Hepsiburada by merchant SKU.
     *
     * @param string $merchantSku The unique merchant SKU for the product.
     * @return array|null The product data if found, otherwise null.
     */
    public function getProductBySku($merchantSku)
    {
        $cacheKey = 'hepsiburada.product.' . md5($merchantSku);
        if ($this->cache) {
            $cachedData = $this->cache->get($cacheKey);
            if ($cachedData) {
                return $cachedData;
            }
        }

        try {
            $endpoint = 'listings/merchant-sku/' . urlencode($merchantSku);
            $response = $this->request($endpoint, 'GET');

            if (!empty($response['listings'])) {
                $productData = $response['listings'][0]; // API returns a list
                if ($this->cache) {
                    $this->cache->set($cacheKey, $productData, 300); // Cache for 5 minutes
                }
                return $productData;
            }

            return null;
        } catch (\Exception $e) {
            error_log('Hepsiburada API Get Product by SKU Failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Process an order on Hepsiburada (e.g., approve or ship).
     *
     * @param string $orderNumber The Hepsiburada order number.
     * @param array $lineItems The items to be processed.
     * @return array The API response.
     * @throws \Exception
     */
    public function processOrder($orderNumber, $lineItems)
    {
        try {
            // This is a conceptual example of packing a shipment.
            // The actual endpoint and payload might be different.
            $endpoint = 'shipments';
            $data = [
                'orderNumber' => $orderNumber,
                'items' => array_map(function($item) {
                    return [
                        'merchantSku' => $item['sku'],
                        'quantity' => $item['quantity']
                    ];
                }, $lineItems)
            ];

            $response = $this->request($endpoint, 'POST', $data);

            return $response;
        } catch (\Exception $e) {
            error_log('Hepsiburada API Process Order Failed: ' . $e->getMessage());
            throw $e;
        }
    }
} 