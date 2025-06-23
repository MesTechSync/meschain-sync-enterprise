<?php

class AmazonApiClient {
    private $clientId;
    private $clientSecret;
    private $refreshToken;
    private $region;
    private $accessToken;
    private $tokenExpiry;
    private $cache;

    private $endpoints = [
        'na' => 'https://sellingpartnerapi-na.amazon.com',
        'eu' => 'https://sellingpartnerapi-eu.amazon.com',
        'fe' => 'https://sellingpartnerapi-fe.amazon.com'
    ];
    private $sandboxEndpoints = [
        'na' => 'https://sandbox.sellingpartnerapi-na.amazon.com',
        'eu' => 'https://sandbox.sellingpartnerapi-eu.amazon.com',
        'fe' => 'https://sandbox.sellingpartnerapi-fe.amazon.com'
    ];
    private $isSandbox = false;

    public function __construct($credentials = [], $cache = null) {
        $this->clientId = $credentials['client_id'] ?? '';
        $this->clientSecret = $credentials['client_secret'] ?? '';
        $this->refreshToken = $credentials['refresh_token'] ?? '';
        $this->region = $credentials['region'] ?? 'eu';
        $this->isSandbox = $credentials['is_sandbox'] ?? false;
        $this->cache = $cache;
    }

    private function getAccessToken() {
        if ($this->accessToken && $this->tokenExpiry > time()) {
            return $this->accessToken;
        }

        if (empty($this->clientId) || empty($this->clientSecret) || empty($this->refreshToken)) {
            throw new \Exception('Amazon LWA credentials (ClientId, ClientSecret, RefreshToken) are required.');
        }
        
        // Use OpenCart cache if available for the token
        $cacheKey = 'amazon.accesstoken.' . md5($this->refreshToken);
        if ($this->cache) {
            $cachedToken = $this->cache->get($cacheKey);
            if ($cachedToken) {
                $this->accessToken = $cachedToken;
                return $this->accessToken;
            }
        }

        $url = 'https://api.amazon.com/auth/o2/token';
        $body = [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->refreshToken,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL Error during Amazon LWA authentication: ' . $error);
        }
        curl_close($ch);
        
        $data = json_decode($response, true);

        if ($httpCode !== 200 || empty($data['access_token'])) {
            throw new \Exception('Failed to get Amazon Access Token. Response: ' . $response, $httpCode);
        }

        $this->accessToken = $data['access_token'];
        $this->tokenExpiry = time() + (int)$data['expires_in'] - 60; // 60 seconds buffer
        
        if ($this->cache) {
            $this->cache->set($cacheKey, $this->accessToken, (int)$data['expires_in'] - 60);
        }

        return $this->accessToken;
    }

    public function request($path, $method = 'GET', $data = [], $queryParams = [], $marketplaceId = null) {
        $accessToken = $this->getAccessToken();
        
        $baseUrl = $this->isSandbox ? $this->sandboxEndpoints[$this->region] : $this->endpoints[$this->region];
        $url = $baseUrl . $path;

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        
        $headers = [
            'x-amz-access-token: ' . $accessToken,
            'Content-Type: application/json',
            'User-Agent: Meschain-Sync/2.0'
        ];
        if ($marketplaceId) {
            $headers[] = 'x-amz-marketplace-id: ' . $marketplaceId;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if (in_array($method, ['POST', 'PUT', 'PATCH']) && !empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL Error to Amazon SP-API: ' . $error);
        }
        curl_close($ch);

        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMessage = 'Amazon SP-API Error';
            if (!empty($decodedResponse['errors'][0]['message'])) {
                $errorMessage .= ': ' . $decodedResponse['errors'][0]['message'];
            }
            throw new \Exception($errorMessage, $httpCode);
        }

        return $decodedResponse;
    }

    public function testConnection() {
        try {
            $this->request('/sellers/v1/marketplaceParticipations');
            return true;
        } catch (\Exception $e) {
            error_log('Amazon SP-API Connection Test Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get product catalog details from Amazon by SKU.
     *
     * @param string $sku The SKU for the product.
     * @param string $marketplaceId The marketplace identifier.
     * @return array|null The product data if found, otherwise null.
     */
    public function getProductBySku($sku, $marketplaceId)
    {
        $cacheKey = 'amazon.product.' . md5($sku . '.' . $marketplaceId);
        if ($this->cache) {
            $cachedData = $this->cache->get($cacheKey);
            if ($cachedData) {
                return $cachedData;
            }
        }

        try {
            $queryParams = [
                'marketplaceIds' => $marketplaceId,
                'identifiers' => $sku,
                'identifierType' => 'SKU'
            ];
            
            // This endpoint gets catalog information.
            // For stock (inventory), a different endpoint ('/fba/inventory/v1/inventories') is needed.
            // For price, another endpoint is needed.
            // This is a starting point.
            $response = $this->request('/catalog/v2020-12-01/items', 'GET', [], $queryParams);

            if (!empty($response['items'])) {
                $productData = $response['items'][0];
                if ($this->cache) {
                    $this->cache->set($cacheKey, $productData, 300); // Cache for 5 minutes
                }
                return $productData;
            }

            return null;
        } catch (\Exception $e) {
            error_log('Amazon SP-API Get Product by SKU Failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Submit an Order Acknowledgement feed to Amazon.
     * This is how sellers typically confirm they have received and are processing an order.
     *
     * @param string $amazonOrderId The Amazon Order ID.
     * @param string $merchantOrderId Your internal order ID.
     * @param string $marketplaceId The marketplace identifier.
     * @return array The API response for the feed submission.
     * @throws \Exception
     */
    public function submitOrderAcknowledgement($amazonOrderId, $merchantOrderId, $marketplaceId)
    {
        try {
            $feedContent = [
                "AmazonOrderID" => $amazonOrderId,
                "MerchantOrderID" => $merchantOrderId,
                "StatusCode" => "Success",
                "Item" => [
                    // This should be populated with actual order items
                    // For acknowledgment, sending at least one item can be a best practice.
                ]
            ];

            $feedDocument = [
                "header" => [
                    "sellerId" => "YOUR_SELLER_ID", // This needs to be configured
                    "version" => "2.0",
                    "issueDate" => gmdate("Y-m-d\TH:i:s\Z")
                ],
                "messageType" => "OrderAcknowledgement",
                "message" => $feedContent
            ];
            
            // Step 1: Create a feed document
            $docResponse = $this->request('/feeds/2021-06-30/documents', 'POST', ['contentType' => 'application/json; charset=UTF-8']);
            $feedDocumentId = $docResponse['feedDocumentId'];
            $uploadUrl = $docResponse['url'];

            // Step 2: Upload the feed content to the provided URL
            $this->uploadFeedDocument($uploadUrl, json_encode($feedDocument));

            // Step 3: Create the feed
            $feedData = [
                'feedType' => 'POST_ORDER_ACKNOWLEDGEMENT_DATA',
                'marketplaceIds' => [$marketplaceId],
                'inputFeedDocumentId' => $feedDocumentId
            ];
            $response = $this->request('/feeds/2021-06-30/feeds', 'POST', $feedData);

            return $response;

        } catch (\Exception $e) {
            error_log('Amazon SP-API Submit Order Acknowledgement Failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Helper to upload the feed document content.
     */
    private function uploadFeedDocument($url, $content) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=UTF-8']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch) || $httpCode >= 300) {
             curl_close($ch);
             throw new \Exception("Failed to upload feed document. HTTP Status: " . $httpCode);
        }
        curl_close($ch);
    }
} 