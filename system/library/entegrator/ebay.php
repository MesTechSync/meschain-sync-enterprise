<?php
/**
 * eBay REST API Integration Class
 * 
 * Bu sınıf eBay Developer API ile OpenCart arasında entegrasyon sağlar.
 * OAuth 2.0 authentication, ürün listeleme, sipariş yönetimi işlemleri gerçekleştirir.
 * 
 * @category   Integration
 * @package    MesChain-Sync
 * @subpackage eBay
 * @version    1.0.0
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

namespace MesChain\Library\Entegrator;

class Ebay {
    
    private $registry;
    private $config;
    private $log;
    private $client_id;
    private $client_secret;
    private $refresh_token;
    private $access_token;
    private $token_expires_at;
    private $api_base_url;
    private $marketplace;
    
    /**
     * eBay REST API endpoints
     */
    const ENDPOINTS = [
        'auth' => '/identity/v1/oauth2/token',
        'user_profile' => '/commerce/identity/v1/user',
        'inventory' => '/sell/inventory/v1',
        'fulfillment' => '/sell/fulfillment/v1',
        'account' => '/sell/account/v1',
        'analytics' => '/sell/analytics/v1',
        'marketing' => '/sell/marketing/v1',
        'metadata' => '/sell/metadata/v1',
        'recommendation' => '/sell/recommendation/v1',
        'compliance' => '/sell/compliance/v1'
    ];
    
    /**
     * eBay marketplace configurations
     */
    const MARKETPLACES = [
        'US' => [
            'id' => 'EBAY_US',
            'name' => 'United States',
            'currency' => 'USD',
            'language' => 'en-US'
        ],
        'UK' => [
            'id' => 'EBAY_GB',
            'name' => 'United Kingdom',
            'currency' => 'GBP',
            'language' => 'en-GB'
        ],
        'DE' => [
            'id' => 'EBAY_DE',
            'name' => 'Germany',
            'currency' => 'EUR',
            'language' => 'de-DE'
        ],
        'FR' => [
            'id' => 'EBAY_FR',
            'name' => 'France',
            'currency' => 'EUR',
            'language' => 'fr-FR'
        ],
        'IT' => [
            'id' => 'EBAY_IT',
            'name' => 'Italy',
            'currency' => 'EUR',
            'language' => 'it-IT'
        ],
        'ES' => [
            'id' => 'EBAY_ES',
            'name' => 'Spain',
            'currency' => 'EUR',
            'language' => 'es-ES'
        ],
        'CA' => [
            'id' => 'EBAY_CA',
            'name' => 'Canada',
            'currency' => 'CAD',
            'language' => 'en-CA'
        ],
        'AU' => [
            'id' => 'EBAY_AU',
            'name' => 'Australia',
            'currency' => 'AUD',
            'language' => 'en-AU'
        ]
    ];
    
    /**
     * Constructor
     * 
     * @param object $registry OpenCart registry nesnesi
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $this->registry->get('config');
        $this->log = new \Log('ebay.log');
        
        // API credentials
        $this->client_id = $this->config->get('module_ebay_client_id');
        $this->client_secret = $this->config->get('module_ebay_client_secret');
        $this->refresh_token = $this->config->get('module_ebay_refresh_token');
        
        // Environment setup
        $sandbox = $this->config->get('module_ebay_sandbox_mode');
        $this->api_base_url = $sandbox ? 
            'https://api.sandbox.ebay.com' : 
            'https://api.ebay.com';
            
        // Marketplace configuration
        $this->marketplace = $this->config->get('module_ebay_marketplace') ?: 'US';
        
        $this->log->write('eBay API initialized for marketplace: ' . $this->marketplace . ' (' . ($sandbox ? 'sandbox' : 'production') . ')');
    }
    
    /**
     * Test API connection
     * 
     * @return array Connection test result
     */
    public function testConnection() {
        try {
            $token = $this->getAccessToken();
            
            if ($token) {
                // Test with user profile endpoint
                $response = $this->makeRequest('GET', self::ENDPOINTS['user_profile']);
                
                $this->log->write('eBay API connection test successful');
                return [
                    'success' => true,
                    'message' => 'eBay API bağlantısı başarılı',
                    'user_data' => $response
                ];
            } else {
                $this->log->write('eBay API connection test failed - token retrieval failed');
                return [
                    'success' => false,
                    'error' => 'Access token alınamadı'
                ];
            }
            
        } catch (Exception $e) {
            $this->log->write('eBay API connection test error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Bağlantı hatası: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get access token using refresh token
     * 
     * @return string|null Access token
     */
    private function getAccessToken() {
        try {
            // Check if we have a valid cached token
            if ($this->access_token && $this->token_expires_at > time()) {
                return $this->access_token;
            }
            
            $credentials = base64_encode($this->client_id . ':' . $this->client_secret);
            
            $data = [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->refresh_token,
                'scope' => 'https://api.ebay.com/oauth/api_scope https://api.ebay.com/oauth/api_scope/sell.marketing.readonly https://api.ebay.com/oauth/api_scope/sell.marketing https://api.ebay.com/oauth/api_scope/sell.inventory.readonly https://api.ebay.com/oauth/api_scope/sell.inventory https://api.ebay.com/oauth/api_scope/sell.account.readonly https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly https://api.ebay.com/oauth/api_scope/sell.fulfillment https://api.ebay.com/oauth/api_scope/sell.analytics.readonly'
            ];
            
            $response = $this->makeAuthRequest($data, $credentials);
            
            if (isset($response['access_token'])) {
                $this->access_token = $response['access_token'];
                $this->token_expires_at = time() + ($response['expires_in'] ?? 7200);
                return $this->access_token;
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->log->write('Error getting access token: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get inventory items
     * 
     * @param array $filters Filter parameters
     * @return array Inventory data
     */
    public function getInventoryItems($filters = []) {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                throw new Exception('Access token not available');
            }
            
            $params = array_merge([
                'limit' => 100,
                'offset' => 0
            ], $filters);
            
            $url = self::ENDPOINTS['inventory'] . '/inventory_item?' . http_build_query($params);
            $response = $this->makeRequest('GET', $url);
            
            $this->log->write('Successfully retrieved ' . count($response['inventoryItems'] ?? []) . ' inventory items from eBay');
            
            return [
                'success' => true,
                'data' => $response
            ];
            
        } catch (Exception $e) {
            $this->log->write('Error getting inventory items: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create or update inventory item
     * 
     * @param array $product_data Product information
     * @return array Operation result
     */
    public function syncProduct($product_data) {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                throw new Exception('Access token not available');
            }
            
            // Convert OpenCart product to eBay format
            $ebay_product = $this->convertProductToEbayFormat($product_data);
            
            $sku = $product_data['sku'] ?: 'OC_' . $product_data['product_id'];
            $url = self::ENDPOINTS['inventory'] . '/inventory_item/' . urlencode($sku);
            
            $response = $this->makeRequest('PUT', $url, $ebay_product);
            
            // Create or update offer if inventory item was successful
            if ($response) {
                $offer_result = $this->createOrUpdateOffer($sku, $product_data);
            }
            
            $this->log->write('Product synced successfully: ' . $product_data['name']);
            
            return [
                'success' => true,
                'data' => $response,
                'offer_data' => $offer_result ?? null
            ];
            
        } catch (Exception $e) {
            $this->log->write('Error syncing product: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create or update offer for inventory item
     * 
     * @param string $sku SKU
     * @param array $product_data Product data
     * @return array Operation result
     */
    private function createOrUpdateOffer($sku, $product_data) {
        try {
            $marketplace_id = self::MARKETPLACES[$this->marketplace]['id'];
            $currency = self::MARKETPLACES[$this->marketplace]['currency'];
            
            $offer_data = [
                'sku' => $sku,
                'marketplaceId' => $marketplace_id,
                'categoryId' => $this->config->get('module_ebay_default_category') ?: '9355',
                'format' => 'FIXED_PRICE',
                'availableQuantity' => (int)$product_data['quantity'],
                'quantityLimitPerBuyer' => 1,
                'pricingSummary' => [
                    'price' => [
                        'value' => number_format((float)$product_data['price'], 2, '.', ''),
                        'currency' => $currency
                    ]
                ],
                'listingDescription' => strip_tags($product_data['description']),
                'listingPolicies' => [
                    'fulfillmentPolicyId' => $this->config->get('module_ebay_fulfillment_policy'),
                    'paymentPolicyId' => $this->config->get('module_ebay_payment_policy'),
                    'returnPolicyId' => $this->config->get('module_ebay_return_policy')
                ],
                'merchantLocationKey' => $this->config->get('module_ebay_location_key') ?: 'DEFAULT'
            ];
            
            $url = self::ENDPOINTS['inventory'] . '/offer';
            $response = $this->makeRequest('POST', $url, $offer_data);
            
            // Publish the offer if creation was successful
            if (isset($response['offerId'])) {
                $publish_url = self::ENDPOINTS['inventory'] . '/offer/' . $response['offerId'] . '/publish';
                $this->makeRequest('POST', $publish_url);
            }
            
            return $response;
            
        } catch (Exception $e) {
            $this->log->write('Error creating/updating offer: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get orders from eBay
     * 
     * @param array $filters Filter parameters
     * @return array Orders data
     */
    public function getOrders($filters = []) {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                throw new Exception('Access token not available');
            }
            
            $params = array_merge([
                'filter' => 'creationdate:[' . date('Y-m-d\TH:i:s.000\Z', strtotime('-7 days')) . '..' . date('Y-m-d\TH:i:s.000\Z') . ']',
                'limit' => 50,
                'offset' => 0
            ], $filters);
            
            $url = self::ENDPOINTS['fulfillment'] . '/order?' . http_build_query($params);
            $response = $this->makeRequest('GET', $url);
            
            $this->log->write('Successfully retrieved ' . count($response['orders'] ?? []) . ' orders from eBay');
            
            return [
                'success' => true,
                'data' => $response
            ];
            
        } catch (Exception $e) {
            $this->log->write('Error getting orders: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update order fulfillment
     * 
     * @param string $order_id Order ID
     * @param array $fulfillment_data Fulfillment information
     * @return array Operation result
     */
    public function fulfillOrder($order_id, $fulfillment_data) {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                throw new Exception('Access token not available');
            }
            
            $url = self::ENDPOINTS['fulfillment'] . '/order/' . $order_id . '/shipping_fulfillment';
            $response = $this->makeRequest('POST', $url, $fulfillment_data);
            
            $this->log->write('Order fulfilled successfully: ' . $order_id);
            
            return [
                'success' => true,
                'data' => $response
            ];
            
        } catch (Exception $e) {
            $this->log->write('Error fulfilling order: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get eBay categories
     * 
     * @param string $category_tree_id Category tree ID
     * @return array Categories data
     */
    public function getCategories($category_tree_id = '0') {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                throw new Exception('Access token not available');
            }
            
            $url = self::ENDPOINTS['metadata'] . '/category_tree/' . $category_tree_id;
            $response = $this->makeRequest('GET', $url);
            
            $this->log->write('Successfully retrieved categories from eBay');
            
            return [
                'success' => true,
                'data' => $response
            ];
            
        } catch (Exception $e) {
            $this->log->write('Error getting categories: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update inventory quantities
     * 
     * @param array $inventory_data Inventory information
     * @return array Operation result
     */
    public function updateInventory($inventory_data) {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                throw new Exception('Access token not available');
            }
            
            $results = [];
            
            foreach ($inventory_data as $item) {
                $sku = $item['sku'];
                $quantity = (int)$item['quantity'];
                
                $update_data = [
                    'requests' => [
                        [
                            'sku' => $sku,
                            'shipToLocationAvailability' => [
                                'quantity' => $quantity
                            ]
                        ]
                    ]
                ];
                
                try {
                    $url = self::ENDPOINTS['inventory'] . '/bulk_update_price_quantity';
                    $response = $this->makeRequest('POST', $url, $update_data);
                    
                    $results[] = [
                        'sku' => $sku,
                        'success' => true,
                        'message' => 'Inventory updated successfully'
                    ];
                } catch (Exception $e) {
                    $results[] = [
                        'sku' => $sku,
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                }
            }
            
            $this->log->write('Inventory update completed for ' . count($inventory_data) . ' products');
            
            return [
                'success' => true,
                'data' => $results
            ];
            
        } catch (Exception $e) {
            $this->log->write('Error updating inventory: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Convert OpenCart product to eBay format
     * 
     * @param array $product OpenCart product data
     * @return array eBay formatted product
     */
    private function convertProductToEbayFormat($product) {
        $marketplace_id = self::MARKETPLACES[$this->marketplace]['id'];
        
        $inventory_item = [
            'availability' => [
                'shipToLocationAvailability' => [
                    'quantity' => (int)$product['quantity']
                ]
            ],
            'condition' => 'NEW',
            'conditionDescription' => 'Brand new item',
            'packageWeightAndSize' => [
                'dimensions' => [
                    'height' => (float)($product['height'] ?: 0),
                    'length' => (float)($product['length'] ?: 0),
                    'width' => (float)($product['width'] ?: 0),
                    'unit' => 'CENTIMETER'
                ],
                'packageType' => 'LETTER',
                'weight' => [
                    'value' => (float)($product['weight'] ?: 0),
                    'unit' => 'KILOGRAM'
                ]
            ],
            'product' => [
                'title' => $product['name'],
                'description' => strip_tags($product['description']),
                'brand' => $product['manufacturer'] ?: 'Generic',
                'mpn' => $product['model'] ?: $product['sku'],
                'imageUrls' => $this->prepareProductImages($product)
            ]
        ];
        
        // Add product identifiers if available
        if (!empty($product['upc'])) {
            $inventory_item['product']['upc'] = [$product['upc']];
        }
        
        if (!empty($product['ean'])) {
            $inventory_item['product']['ean'] = [$product['ean']];
        }
        
        if (!empty($product['isbn'])) {
            $inventory_item['product']['isbn'] = [$product['isbn']];
        }
        
        return $inventory_item;
    }
    
    /**
     * Prepare product images for eBay
     * 
     * @param array $product Product data
     * @return array Images array
     */
    private function prepareProductImages($product) {
        $images = [];
        
        // Main image
        if (!empty($product['image'])) {
            $images[] = HTTP_CATALOG . 'image/' . $product['image'];
        }
        
        // Additional images (eBay allows up to 12 images)
        if (!empty($product['additional_images'])) {
            foreach (array_slice($product['additional_images'], 0, 11) as $image) {
                $images[] = HTTP_CATALOG . 'image/' . $image;
            }
        }
        
        return $images;
    }
    
    /**
     * Make authentication request
     * 
     * @param array $data Request data
     * @param string $credentials Base64 encoded credentials
     * @return array Response data
     * @throws Exception
     */
    private function makeAuthRequest($data, $credentials) {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->api_base_url . self::ENDPOINTS['auth'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . $credentials
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        $decoded_response = json_decode($response, true);
        
        if ($http_code >= 400) {
            $error_message = isset($decoded_response['error_description']) ? 
                $decoded_response['error_description'] : 
                'HTTP Error ' . $http_code;
            throw new Exception($error_message);
        }
        
        return $decoded_response;
    }
    
    /**
     * Make API request
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return array Response data
     * @throws Exception
     */
    private function makeRequest($method, $endpoint, $data = null) {
        $token = $this->getAccessToken();
        if (!$token) {
            throw new Exception('Access token not available');
        }
        
        $url = $this->api_base_url . $endpoint;
        
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
            'Content-Language: ' . self::MARKETPLACES[$this->marketplace]['language']
        ];
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        // eBay can return empty response for successful operations
        if (empty($response) && $http_code < 400) {
            return ['success' => true];
        }
        
        $decoded_response = json_decode($response, true);
        
        if ($http_code >= 400) {
            $error_message = isset($decoded_response['errors'][0]['message']) ? 
                $decoded_response['errors'][0]['message'] : 
                'HTTP Error ' . $http_code;
            throw new Exception($error_message);
        }
        
        $this->log->write('API Request: ' . $method . ' ' . $endpoint . ' - Response Code: ' . $http_code);
        
        return $decoded_response;
    }
    
    /**
     * Convert eBay order to OpenCart format
     * 
     * @param array $ebay_order eBay order data
     * @return array OpenCart formatted order
     */
    public function convertOrderToOpenCartFormat($ebay_order) {
        $order_data = [
            'order_id' => $ebay_order['orderId'],
            'invoice_no' => '',
            'store_id' => 0,
            'store_name' => $this->config->get('config_name'),
            'store_url' => HTTP_CATALOG,
            'customer_id' => 0,
            'customer_group_id' => 1,
            'firstname' => $ebay_order['buyer']['username'] ?? 'eBay',
            'lastname' => 'Customer',
            'email' => 'noreply@ebay.com',
            'telephone' => '',
            'fax' => '',
            'custom_field' => [],
            'payment_firstname' => $ebay_order['fulfillmentStartInstructions'][0]['shippingStep']['shipTo']['contactAddress']['recipientName'] ?? '',
            'payment_lastname' => '',
            'payment_company' => '',
            'payment_address_1' => $ebay_order['fulfillmentStartInstructions'][0]['shippingStep']['shipTo']['contactAddress']['addressLine1'] ?? '',
            'payment_address_2' => $ebay_order['fulfillmentStartInstructions'][0]['shippingStep']['shipTo']['contactAddress']['addressLine2'] ?? '',
            'payment_city' => $ebay_order['fulfillmentStartInstructions'][0]['shippingStep']['shipTo']['contactAddress']['city'] ?? '',
            'payment_postcode' => $ebay_order['fulfillmentStartInstructions'][0]['shippingStep']['shipTo']['contactAddress']['postalCode'] ?? '',
            'payment_country' => $ebay_order['fulfillmentStartInstructions'][0]['shippingStep']['shipTo']['contactAddress']['countryCode'] ?? '',
            'payment_country_id' => 0,
            'payment_zone' => $ebay_order['fulfillmentStartInstructions'][0]['shippingStep']['shipTo']['contactAddress']['stateOrProvince'] ?? '',
            'payment_zone_id' => 0,
            'payment_address_format' => '',
            'payment_custom_field' => [],
            'payment_method' => 'eBay',
            'payment_code' => 'ebay',
            'shipping_firstname' => $ebay_order['fulfillmentStartInstructions'][0]['shippingStep']['shipTo']['contactAddress']['recipientName'] ?? '',
            'shipping_lastname' => '',
            'shipping_company' => '',
            'shipping_address_1' => $ebay_order['fulfillmentStartInstructions'][0]['shippingStep']['shipTo']['contactAddress']['addressLine1'] ?? '',
            'shipping_address_2' => $ebay_order['fulfillmentStartInstructions'][0]['shippingStep']['shipTo']['contactAddress']['addressLine2'] ?? '',
            'shipping_city' => $ebay_order['fulfillmentStartInstructions'][0]['shippingStep']['shipTo']['contactAddress']['city'] ?? '',
            'shipping_postcode' => $ebay_order['fulfillmentStartInstructions'][0]['shippingStep']['shipTo']['contactAddress']['postalCode'] ?? '',
            'shipping_country' => $ebay_order['fulfillmentStartInstructions'][0]['shippingStep']['shipTo']['contactAddress']['countryCode'] ?? '',
            'shipping_country_id' => 0,
            'shipping_zone' => $ebay_order['fulfillmentStartInstructions'][0]['shippingStep']['shipTo']['contactAddress']['stateOrProvince'] ?? '',
            'shipping_zone_id' => 0,
            'shipping_address_format' => '',
            'shipping_custom_field' => [],
            'shipping_method' => 'eBay Shipping',
            'shipping_code' => 'ebay_shipping',
            'comment' => 'eBay Order: ' . $ebay_order['orderId'],
            'total' => (float)$ebay_order['pricingSummary']['total']['value'],
            'order_status_id' => $this->getOrderStatusId($ebay_order['orderFulfillmentStatus']),
            'affiliate_id' => 0,
            'commission' => 0,
            'marketing_id' => 0,
            'tracking' => '',
            'language_id' => 1,
            'currency_id' => 1,
            'currency_code' => $ebay_order['pricingSummary']['total']['currency'],
            'currency_value' => 1.0,
            'ip' => '',
            'forwarded_ip' => '',
            'user_agent' => 'eBay REST API',
            'accept_language' => 'en-US',
            'date_added' => date('Y-m-d H:i:s', strtotime($ebay_order['creationDate'])),
            'date_modified' => date('Y-m-d H:i:s')
        ];
        
        // Order products
        $order_data['products'] = [];
        if (!empty($ebay_order['lineItems'])) {
            foreach ($ebay_order['lineItems'] as $item) {
                $order_data['products'][] = [
                    'product_id' => $this->findProductIdBySku($item['sku']),
                    'name' => $item['title'],
                    'model' => $item['sku'],
                    'option' => [],
                    'quantity' => (int)$item['quantity'],
                    'price' => (float)$item['lineItemCost']['value'],
                    'total' => (float)$item['total']['value'],
                    'tax' => 0,
                    'reward' => 0
                ];
            }
        }
        
        // Order totals
        $order_data['totals'] = [
            [
                'code' => 'sub_total',
                'title' => 'Alt Toplam',
                'value' => (float)$ebay_order['pricingSummary']['subtotal']['value'],
                'sort_order' => 1
            ],
            [
                'code' => 'total',
                'title' => 'Toplam',
                'value' => (float)$ebay_order['pricingSummary']['total']['value'],
                'sort_order' => 3
            ]
        ];
        
        return $order_data;
    }
    
    /**
     * Get OpenCart order status ID from eBay status
     * 
     * @param string $ebay_status eBay order status
     * @return int OpenCart order status ID
     */
    private function getOrderStatusId($ebay_status) {
        $status_mapping = [
            'NOT_STARTED' => 1,     // Pending
            'IN_PROGRESS' => 2,     // Processing
            'FULFILLED' => 3,       // Shipped
            'COMPLETE' => 5         // Complete
        ];
        
        return isset($status_mapping[$ebay_status]) ? 
            $status_mapping[$ebay_status] : 1; // Default to Pending
    }
    
    /**
     * Find OpenCart product ID by SKU
     * 
     * @param string $sku Product SKU
     * @return int Product ID
     */
    private function findProductIdBySku($sku) {
        $db = $this->registry->get('db');
        
        $query = $db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE sku = '" . $db->escape($sku) . "'");
        
        return $query->num_rows ? (int)$query->row['product_id'] : 0;
    }
    
    /**
     * Get API status information
     * 
     * @return array API status data
     */
    public function getApiStatus() {
        try {
            $token = $this->getAccessToken();
            
            if ($token) {
                return [
                    'status' => 'connected',
                    'message' => 'eBay API bağlantısı aktif',
                    'last_check' => date('Y-m-d H:i:s')
                ];
            } else {
                return [
                    'status' => 'disconnected',
                    'message' => 'eBay API bağlantısı başarısız',
                    'last_check' => date('Y-m-d H:i:s')
                ];
            }
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'eBay API hatası: ' . $e->getMessage(),
                'last_check' => date('Y-m-d H:i:s')
            ];
        }
    }
} 