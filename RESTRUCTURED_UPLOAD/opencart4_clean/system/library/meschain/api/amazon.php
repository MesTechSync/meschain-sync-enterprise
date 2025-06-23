<?php
namespace MesChain\Api;

/**
 * Amazon SP-API Integration Class
 * 
 * @package    MesChain Sync
 * @version    2.0.0
 * @author     MesChain Development Team
 * @copyright  2024 MesChain
 * @license    MIT License
 */

class Amazon {
    
    private $config;
    private $access_key;
    private $secret_key;
    private $seller_id;
    private $marketplace_id;
    private $region;
    private $test_mode;
    private $api_base_url;
    
    public function __construct(array $config) {
        $this->config = $config;
        $this->access_key = $config['access_key'] ?? '';
        $this->secret_key = $config['secret_key'] ?? '';
        $this->seller_id = $config['seller_id'] ?? '';
        $this->marketplace_id = $config['marketplace_id'] ?? '';
        $this->region = $config['region'] ?? 'eu-west-1';
        $this->test_mode = $config['test_mode'] ?? false;
        
        $this->setApiBaseUrl();
    }
    
    /**
     * Set API base URL based on region
     */
    private function setApiBaseUrl(): void {
        $region_mapping = [
            'us-east-1' => 'https://sellingpartnerapi-na.amazon.com',
            'us-west-2' => 'https://sellingpartnerapi-na.amazon.com',
            'eu-west-1' => 'https://sellingpartnerapi-eu.amazon.com',
            'eu-central-1' => 'https://sellingpartnerapi-eu.amazon.com',
            'ap-northeast-1' => 'https://sellingpartnerapi-fe.amazon.com',
            'ap-southeast-1' => 'https://sellingpartnerapi-fe.amazon.com'
        ];
        
        $this->api_base_url = $region_mapping[$this->region] ?? $region_mapping['eu-west-1'];
    }
    
    /**
     * Test API connection
     */
    public function testConnection(): array {
        try {
            // Test with seller info endpoint
            $response = $this->makeRequest('GET', '/sellers/v1/marketplaceParticipations');
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'message' => 'Amazon SP-API connection successful',
                    'data' => $response['data']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response['error'] ?? 'Connection test failed'
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create product listing
     */
    public function createProduct(array $product_data): array {
        try {
            $listing_data = $this->formatProductForAmazon($product_data);
            
            $response = $this->makeRequest('PUT', '/listings/2021-08-01/items/' . $this->seller_id . '/' . $listing_data['sku'], [
                'productType' => $listing_data['productType'],
                'requirements' => 'LISTING',
                'attributes' => $listing_data['attributes']
            ]);
            
            return $response;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Product creation failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update product listing
     */
    public function updateProduct(string $sku, array $product_data): array {
        try {
            $listing_data = $this->formatProductForAmazon($product_data);
            
            $response = $this->makeRequest('PATCH', '/listings/2021-08-01/items/' . $this->seller_id . '/' . $sku, [
                'productType' => $listing_data['productType'],
                'patches' => [
                    [
                        'op' => 'replace',
                        'path' => '/attributes',
                        'value' => $listing_data['attributes']
                    ]
                ]
            ]);
            
            return $response;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Product update failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get orders
     */
    public function getOrders(array $params = []): array {
        try {
            $query_params = [
                'MarketplaceIds' => $this->marketplace_id,
                'CreatedAfter' => $params['created_after'] ?? date('c', strtotime('-24 hours')),
                'OrderStatuses' => $params['order_statuses'] ?? 'Unshipped,PartiallyShipped,Shipped'
            ];
            
            if (isset($params['created_before'])) {
                $query_params['CreatedBefore'] = $params['created_before'];
            }
            
            $endpoint = '/orders/v0/orders?' . http_build_query($query_params);
            $response = $this->makeRequest('GET', $endpoint);
            
            return $response;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Orders retrieval failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update inventory
     */
    public function updateInventory(string $sku, int $quantity): array {
        try {
            $response = $this->makeRequest('PUT', '/fba/inventory/v1/summaries', [
                'granularityType' => 'Marketplace',
                'granularityId' => $this->marketplace_id,
                'marketplaceIds' => [$this->marketplace_id],
                'details' => true,
                'skus' => [$sku]
            ]);
            
            // Amazon inventory update is complex and requires different endpoints
            // This is a simplified version - full implementation would require
            // integration with Amazon's inventory management system
            
            return $response;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Inventory update failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Format product data for Amazon
     */
    private function formatProductForAmazon(array $product_data): array {
        return [
            'sku' => $product_data['sku'],
            'productType' => $product_data['product_type'] ?? 'PRODUCT',
            'attributes' => [
                'item_name' => [
                    [
                        'value' => $product_data['title'],
                        'language_tag' => 'en_US'
                    ]
                ],
                'brand' => [
                    [
                        'value' => $product_data['brand_name'] ?? 'Generic'
                    ]
                ],
                'manufacturer' => [
                    [
                        'value' => $product_data['manufacturer'] ?? $product_data['brand_name'] ?? 'Generic'
                    ]
                ],
                'product_description' => [
                    [
                        'value' => $product_data['description'],
                        'language_tag' => 'en_US'
                    ]
                ],
                'list_price' => [
                    [
                        'value' => $product_data['listing-price'],
                        'currency' => 'EUR'
                    ]
                ],
                'main_product_image_locator' => [
                    [
                        'media_location' => $product_data['images'][0] ?? ''
                    ]
                ],
                'other_product_image_locator_1' => isset($product_data['images'][1]) ? [
                    [
                        'media_location' => $product_data['images'][1]
                    ]
                ] : null,
                'fulfillment_availability' => [
                    [
                        'fulfillment_channel_code' => 'DEFAULT',
                        'quantity' => $product_data['quantity'] ?? 0
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Make HTTP request to Amazon SP-API
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): array {
        try {
            $url = $this->api_base_url . $endpoint;
            
            // Generate AWS Signature Version 4
            $headers = $this->generateAwsHeaders($method, $endpoint, $data);
            
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'MesChain-Sync/2.0 (Language=PHP)'
            ]);
            
            if ($method !== 'GET' && !empty($data)) {
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
            
            if ($http_code >= 200 && $http_code < 300) {
                return [
                    'success' => true,
                    'data' => $decoded_response,
                    'http_code' => $http_code
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $decoded_response['errors'][0]['message'] ?? 'HTTP Error: ' . $http_code,
                    'http_code' => $http_code,
                    'response' => $decoded_response
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate AWS Signature Version 4 headers
     */
    private function generateAwsHeaders(string $method, string $endpoint, array $data = []): array {
        $timestamp = gmdate('Ymd\THis\Z');
        $date = gmdate('Ymd');
        
        $payload = $method !== 'GET' && !empty($data) ? json_encode($data) : '';
        $payload_hash = hash('sha256', $payload);
        
        $headers = [
            'host' => parse_url($this->api_base_url, PHP_URL_HOST),
            'x-amz-access-token' => $this->getAccessToken(),
            'x-amz-date' => $timestamp,
            'x-amz-content-sha256' => $payload_hash
        ];
        
        if (!empty($payload)) {
            $headers['content-type'] = 'application/json';
        }
        
        // Create canonical request
        $canonical_headers = '';
        $signed_headers = '';
        ksort($headers);
        
        foreach ($headers as $key => $value) {
            $canonical_headers .= strtolower($key) . ':' . trim($value) . "\n";
            $signed_headers .= strtolower($key) . ';';
        }
        
        $signed_headers = rtrim($signed_headers, ';');
        
        $canonical_request = $method . "\n" .
                           $endpoint . "\n" .
                           '' . "\n" .
                           $canonical_headers . "\n" .
                           $signed_headers . "\n" .
                           $payload_hash;
        
        // Create string to sign
        $algorithm = 'AWS4-HMAC-SHA256';
        $credential_scope = $date . '/' . $this->region . '/execute-api/aws4_request';
        $string_to_sign = $algorithm . "\n" .
                         $timestamp . "\n" .
                         $credential_scope . "\n" .
                         hash('sha256', $canonical_request);
        
        // Calculate signature
        $signing_key = $this->getSignatureKey($this->secret_key, $date, $this->region, 'execute-api');
        $signature = hash_hmac('sha256', $string_to_sign, $signing_key);
        
        // Authorization header
        $authorization = $algorithm . ' ' .
                        'Credential=' . $this->access_key . '/' . $credential_scope . ', ' .
                        'SignedHeaders=' . $signed_headers . ', ' .
                        'Signature=' . $signature;
        
        // Return formatted headers
        $formatted_headers = [
            'Authorization: ' . $authorization,
            'x-amz-access-token: ' . $headers['x-amz-access-token'],
            'x-amz-date: ' . $timestamp,
            'x-amz-content-sha256: ' . $payload_hash
        ];
        
        if (!empty($payload)) {
            $formatted_headers[] = 'Content-Type: application/json';
        }
        
        return $formatted_headers;
    }
    
    /**
     * Get signing key for AWS Signature Version 4
     */
    private function getSignatureKey(string $key, string $date, string $region, string $service): string {
        $kDate = hash_hmac('sha256', $date, 'AWS4' . $key, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        
        return $kSigning;
    }
    
    /**
     * Get access token (simplified - would need proper LWA token refresh)
     */
    private function getAccessToken(): string {
        // This is a placeholder - actual implementation would require
        // Login with Amazon (LWA) token refresh flow
        return 'your-lwa-access-token';
    }
}
