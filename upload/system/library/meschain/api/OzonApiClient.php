<?php

class OzonApiClient {
    private $clientId;
    private $apiKey;
    private $baseUrl = 'https://api-seller.ozon.ru/';
    private $cache;

    /**
     * @param array $credentials API credentials (client_id, api_key)
     * @param object|null $cache OpenCart cache object
     */
    public function __construct($credentials = [], $cache = null) {
        $this->clientId = $credentials['client_id'] ?? '';
        $this->apiKey = $credentials['api_key'] ?? '';
        $this->cache = $cache;
    }

    /**
     * Perform a request to the Ozon API.
     *
     * @param string $endpoint The API endpoint (e.g., 'v2/product/list').
     * @param array $data The data to send with the request.
     * @param string $method The HTTP method (POST, GET).
     * @return array The API response decoded as an associative array.
     * @throws Exception If the request fails or returns an error.
     */
    public function request($endpoint, $data = [], $method = 'POST') {
        if (empty($this->clientId) || empty($this->apiKey)) {
            throw new \Exception('Ozon Client-Id and Api-Key are required.');
        }

        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Client-Id: ' . $this->clientId,
            'Api-Key: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: Meschain-Sync/2.0'
        ];

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        // Enforce SSL certificate verification for security
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } else { // GET
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL Error to Ozon: ' . $error);
        }

        curl_close($ch);
        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMessage = 'Ozon API Error';
            if (!empty($decodedResponse['error']['message'])) {
                $errorMessage .= ': ' . $decodedResponse['error']['message'];
            }
            throw new \Exception($errorMessage, $httpCode);
        }

        return $decodedResponse;
    }

    /**
     * Tests the connection to the Ozon API.
     * A simple, low-cost request to check credentials.
     *
     * @return bool True if connection is successful, false otherwise.
     */
    public function testConnection() {
        try {
            // Get a list of warehouses, a common and simple check.
            $this->request('v1/warehouse/list');
            return true;
        } catch (\Exception $e) {
            error_log('Ozon API Connection Test Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get product details from Ozon by offer ID (product_id).
     *
     * @param string $offerId The offer ID (usually OpenCart product_id).
     * @return array|null The product data if found, otherwise null.
     */
    public function getProductByOfferId($offerId)
    {
        $cacheKey = 'ozon.product.' . md5($offerId);
        if ($this->cache) {
            $cachedData = $this->cache->get($cacheKey);
            if ($cachedData) {
                return $cachedData;
            }
        }

        try {
            $data = [
                'offer_id' => (string)$offerId,
            ];
            $response = $this->request('v2/product/info', $data, 'POST');

            if (!empty($response['result'])) {
                $productData = $response['result'];
                if ($this->cache) {
                    $this->cache->set($cacheKey, $productData, 300); // Cache for 5 minutes
                }
                return $productData;
            }

            return null;
        } catch (\Exception $e) {
            error_log('Ozon API Get Product by Offer ID Failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new FBS (Fulfillment by Seller) posting on Ozon.
     *
     * @param array $postingData Data for creating the posting.
     * @return array The API response.
     * @throws \Exception
     */
    public function createFbsPosting($postingData)
    {
        try {
            // Ozon API requires a specific structure for creating a posting
            $endpoint = 'v3/posting/fbs/ship'; // Example endpoint, might vary
            $response = $this->request($endpoint, 'POST', $postingData);

            return $response;
        } catch (\Exception $e) {
            error_log('Ozon API Create FBS Posting Failed: ' . $e->getMessage());
            throw $e;
        }
    }
} 