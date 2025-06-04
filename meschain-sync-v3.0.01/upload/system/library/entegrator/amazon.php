<?php
/**
 * Amazon SP-API Integration Class
 * 
 * Bu sınıf Amazon Selling Partner API ile OpenCart arasında entegrasyon sağlar.
 * Ürün, sipariş, katalog ve fulfillment yönetimi işlemleri gerçekleştirir.
 * 
 * @category   Integration
 * @package    MesChain-Sync
 * @subpackage Amazon
 * @version    1.0.0
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

namespace MesChain\Library\Entegrator;

class Amazon {
    
    private $registry;
    private $config;
    private $log;
    private $refresh_token;
    private $client_id;
    private $client_secret;
    private $seller_id;
    private $marketplace_id;
    private $api_base_url;
    private $access_token;
    private $token_expires_at;
    
    /**
     * Amazon SP-API endpoints
     */
    const ENDPOINTS = [
        'auth' => '/auth/o2/token',
        'catalog' => '/catalog/2022-04-01/items',
        'catalog_categories' => '/catalog/2022-04-01/categories',
        'inventory' => '/fba/inventory/v1/summaries',
        'listings' => '/listings/2021-08-01/items',
        'orders' => '/orders/v0/orders',
        'shipments' => '/fba/outbound/2020-07-01/fulfillmentOrders',
        'reports' => '/reports/2021-06-30/reports',
        'notifications' => '/notifications/v1/subscriptions',
        'product_fees' => '/productFees/v0/feesEstimate',
        'product_pricing' => '/products/pricing/v0/price'
    ];
    
    /**
     * Marketplace configurations
     */
    const MARKETPLACES = [
        'us' => [
            'id' => 'ATVPDKIKX0DER',
            'endpoint' => 'https://sellingpartnerapi-na.amazon.com',
            'region' => 'us-east-1'
        ],
        'ca' => [
            'id' => 'A2EUQ1WTGCTBG2',
            'endpoint' => 'https://sellingpartnerapi-na.amazon.com',
            'region' => 'us-east-1'
        ],
        'mx' => [
            'id' => 'A1AM78C64UM0Y8',
            'endpoint' => 'https://sellingpartnerapi-na.amazon.com',
            'region' => 'us-east-1'
        ],
        'br' => [
            'id' => 'A2Q3Y263D00KWC',
            'endpoint' => 'https://sellingpartnerapi-na.amazon.com',
            'region' => 'us-east-1'
        ],
        'uk' => [
            'id' => 'A1F83G8C2ARO7P',
            'endpoint' => 'https://sellingpartnerapi-eu.amazon.com',
            'region' => 'eu-west-1'
        ],
        'de' => [
            'id' => 'A1PA6795UKMFR9',
            'endpoint' => 'https://sellingpartnerapi-eu.amazon.com',
            'region' => 'eu-west-1'
        ],
        'fr' => [
            'id' => 'A13V1IB3VIYZZH',
            'endpoint' => 'https://sellingpartnerapi-eu.amazon.com',
            'region' => 'eu-west-1'
        ],
        'it' => [
            'id' => 'APJ6JRA9NG5V4',
            'endpoint' => 'https://sellingpartnerapi-eu.amazon.com',
            'region' => 'eu-west-1'
        ],
        'es' => [
            'id' => 'A1RKKUPIHCS9HS',
            'endpoint' => 'https://sellingpartnerapi-eu.amazon.com',
            'region' => 'eu-west-1'
        ],
        'jp' => [
            'id' => 'A1VC38T7YXB528',
            'endpoint' => 'https://sellingpartnerapi-fe.amazon.com',
            'region' => 'us-west-2'
        ],
        'au' => [
            'id' => 'A39IBJ37TRP1C6',
            'endpoint' => 'https://sellingpartnerapi-fe.amazon.com',
            'region' => 'us-west-2'
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
        $this->log = new \Log('amazon.log');
        
        // API credentials
        $this->refresh_token = $this->config->get('module_amazon_refresh_token');
        $this->client_id = $this->config->get('module_amazon_client_id');
        $this->client_secret = $this->config->get('module_amazon_client_secret');
        $this->seller_id = $this->config->get('module_amazon_seller_id');
        
        // Marketplace configuration
        $marketplace = $this->config->get('module_amazon_marketplace') ?: 'us';
        $this->marketplace_id = self::MARKETPLACES[$marketplace]['id'];
        $this->api_base_url = self::MARKETPLACES[$marketplace]['endpoint'];
        
        $this->log->write('Amazon SP-API initialized for marketplace: ' . $marketplace);
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
                // Test with a simple API call
                $response = $this->makeRequest('GET', self::ENDPOINTS['orders'] . '?MarketplaceIds=' . $this->marketplace_id . '&CreatedAfter=' . date('c', strtotime('-7 days')));
                
                $this->log->write('Amazon SP-API connection test successful');
                return [
                    'success' => true,
                    'message' => 'SP-API bağlantısı başarılı'
                ];
            } else {
                $this->log->write('Amazon SP-API connection test failed - token retrieval failed');
                return [
                    'success' => false,
                    'error' => 'Access token alınamadı'
                ];
            }
            
        } catch (Exception $e) {
            $this->log->write('Amazon SP-API connection test error: ' . $e->getMessage());
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
            
            $data = [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->refresh_token,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret
            ];
            
            $response = $this->makeAuthRequest($data);
            
            if (isset($response['access_token'])) {
                $this->access_token = $response['access_token'];
                $this->token_expires_at = time() + ($response['expires_in'] ?? 3600);
                return $this->access_token;
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->log->write('Error getting access token: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get orders from Amazon
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
                'MarketplaceIds' => $this->marketplace_id,
                'CreatedAfter' => date('c', strtotime('-7 days')),
                'OrderStatuses' => 'Unshipped,PartiallyShipped,Shipped'
            ], $filters);
            
            $url = self::ENDPOINTS['orders'] . '?' . http_build_query($params);
            $response = $this->makeRequest('GET', $url);
            
            $this->log->write('Successfully retrieved ' . count($response['payload']['Orders'] ?? []) . ' orders from Amazon');
            
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
     * Get product catalog from Amazon
     * 
     * @param array $filters Filter parameters
     * @return array Catalog data
     */
    public function getCatalogItems($filters = []) {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                throw new Exception('Access token not available');
            }
            
            $params = array_merge([
                'marketplaceIds' => $this->marketplace_id,
                'includedData' => 'attributes,images,productTypes,relationships,salesRanks'
            ], $filters);
            
            $url = self::ENDPOINTS['catalog'] . '?' . http_build_query($params);
            $response = $this->makeRequest('GET', $url);
            
            $this->log->write('Successfully retrieved catalog items from Amazon');
            
            return [
                'success' => true,
                'data' => $response
            ];
            
        } catch (Exception $e) {
            $this->log->write('Error getting catalog items: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create or update product listing
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
            
            // Convert OpenCart product to Amazon format
            $amazon_product = $this->convertProductToAmazonFormat($product_data);
            
            $sku = $product_data['sku'] ?: 'OC_' . $product_data['product_id'];
            $url = self::ENDPOINTS['listings'] . '/' . $this->seller_id . '/' . urlencode($sku) . '?marketplaceIds=' . $this->marketplace_id;
            
            $response = $this->makeRequest('PUT', $url, $amazon_product);
            
            $this->log->write('Product synced successfully: ' . $product_data['name']);
            
            return [
                'success' => true,
                'data' => $response
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
                
                $listing_data = [
                    'productType' => 'PRODUCT',
                    'patches' => [
                        [
                            'op' => 'replace',
                            'path' => '/attributes/fulfillment_availability',
                            'value' => [
                                [
                                    'fulfillment_channel_code' => 'AMAZON_NA',
                                    'quantity' => $quantity
                                ]
                            ]
                        ]
                    ]
                ];
                
                $url = self::ENDPOINTS['listings'] . '/' . $this->seller_id . '/' . urlencode($sku) . '?marketplaceIds=' . $this->marketplace_id;
                
                try {
                    $response = $this->makeRequest('PATCH', $url, $listing_data);
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
     * Get FBA inventory summaries
     * 
     * @return array Inventory data
     */
    public function getFBAInventory() {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                throw new Exception('Access token not available');
            }
            
            $params = [
                'granularityType' => 'Marketplace',
                'granularityId' => $this->marketplace_id,
                'marketplaceIds' => $this->marketplace_id
            ];
            
            $url = self::ENDPOINTS['inventory'] . '?' . http_build_query($params);
            $response = $this->makeRequest('GET', $url);
            
            $this->log->write('Successfully retrieved FBA inventory summaries');
            
            return [
                'success' => true,
                'data' => $response
            ];
            
        } catch (Exception $e) {
            $this->log->write('Error getting FBA inventory: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Convert OpenCart product to Amazon format
     * 
     * @param array $product OpenCart product data
     * @return array Amazon formatted product
     */
    private function convertProductToAmazonFormat($product) {
        $fulfillment_method = $this->config->get('module_amazon_fulfillment_method') ?: 'FBM';
        
        $listing_data = [
            'productType' => $this->config->get('module_amazon_product_type') ?: 'PRODUCT',
            'attributes' => [
                'condition_type' => [
                    [
                        'value' => 'new_new',
                        'marketplace_id' => $this->marketplace_id
                    ]
                ],
                'item_name' => [
                    [
                        'value' => $product['name'],
                        'marketplace_id' => $this->marketplace_id
                    ]
                ],
                'description' => [
                    [
                        'value' => strip_tags($product['description']),
                        'marketplace_id' => $this->marketplace_id
                    ]
                ],
                'brand' => [
                    [
                        'value' => $product['manufacturer'] ?: 'Generic',
                        'marketplace_id' => $this->marketplace_id
                    ]
                ],
                'list_price' => [
                    [
                        'value' => [
                            'Amount' => number_format((float)$product['price'], 2, '.', ''),
                            'CurrencyCode' => $this->getCurrencyByMarketplace()
                        ],
                        'marketplace_id' => $this->marketplace_id
                    ]
                ]
            ]
        ];
        
        // Add images
        if (!empty($product['image'])) {
            $listing_data['attributes']['main_product_image_locator'] = [
                [
                    'value' => [
                        'media_location' => HTTP_CATALOG . 'image/' . $product['image']
                    ],
                    'marketplace_id' => $this->marketplace_id
                ]
            ];
        }
        
        // Add additional images
        if (!empty($product['additional_images'])) {
            $other_images = [];
            foreach (array_slice($product['additional_images'], 0, 8) as $image) {
                $other_images[] = [
                    'media_location' => HTTP_CATALOG . 'image/' . $image
                ];
            }
            
            if (!empty($other_images)) {
                $listing_data['attributes']['other_product_image_locator_1'] = [
                    [
                        'value' => $other_images,
                        'marketplace_id' => $this->marketplace_id
                    ]
                ];
            }
        }
        
        // Add fulfillment settings
        if ($fulfillment_method === 'FBA') {
            $listing_data['attributes']['fulfillment_channel_code'] = [
                [
                    'value' => 'AMAZON_NA',
                    'marketplace_id' => $this->marketplace_id
                ]
            ];
        } else {
            $listing_data['attributes']['fulfillment_availability'] = [
                [
                    'value' => [
                        [
                            'fulfillment_channel_code' => 'DEFAULT',
                            'quantity' => (int)$product['quantity']
                        ]
                    ],
                    'marketplace_id' => $this->marketplace_id
                ]
            ];
        }
        
        // Add product dimensions and weight
        if (!empty($product['weight'])) {
            $listing_data['attributes']['item_weight'] = [
                [
                    'value' => [
                        'value' => (float)$product['weight'],
                        'unit' => 'kilograms'
                    ],
                    'marketplace_id' => $this->marketplace_id
                ]
            ];
        }
        
        if (!empty($product['length']) || !empty($product['width']) || !empty($product['height'])) {
            $listing_data['attributes']['item_dimensions'] = [
                [
                    'value' => [
                        'length' => ['value' => (float)($product['length'] ?: 0), 'unit' => 'centimeters'],
                        'width' => ['value' => (float)($product['width'] ?: 0), 'unit' => 'centimeters'],
                        'height' => ['value' => (float)($product['height'] ?: 0), 'unit' => 'centimeters']
                    ],
                    'marketplace_id' => $this->marketplace_id
                ]
            ];
        }
        
        return $listing_data;
    }
    
    /**
     * Get currency code by marketplace
     * 
     * @return string Currency code
     */
    private function getCurrencyByMarketplace() {
        $marketplace = $this->config->get('module_amazon_marketplace') ?: 'us';
        
        $currencies = [
            'us' => 'USD',
            'ca' => 'CAD',
            'mx' => 'MXN',
            'br' => 'BRL',
            'uk' => 'GBP',
            'de' => 'EUR',
            'fr' => 'EUR',
            'it' => 'EUR',
            'es' => 'EUR',
            'jp' => 'JPY',
            'au' => 'AUD'
        ];
        
        return $currencies[$marketplace] ?? 'USD';
    }
    
    /**
     * Make authentication request
     * 
     * @param array $data Request data
     * @return array Response data
     * @throws Exception
     */
    private function makeAuthRequest($data) {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.amazon.com' . self::ENDPOINTS['auth'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
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
            'x-amz-access-token: ' . $token
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
     * Convert Amazon order to OpenCart format
     * 
     * @param array $amazon_order Amazon order data
     * @return array OpenCart formatted order
     */
    public function convertOrderToOpenCartFormat($amazon_order) {
        $order_data = [
            'order_id' => $amazon_order['AmazonOrderId'],
            'invoice_no' => '',
            'store_id' => 0,
            'store_name' => $this->config->get('config_name'),
            'store_url' => HTTP_CATALOG,
            'customer_id' => 0,
            'customer_group_id' => 1,
            'firstname' => 'Amazon',
            'lastname' => 'Customer',
            'email' => $amazon_order['BuyerEmail'] ?? 'noreply@amazon.com',
            'telephone' => '',
            'fax' => '',
            'custom_field' => [],
            'payment_firstname' => '',
            'payment_lastname' => '',
            'payment_company' => '',
            'payment_address_1' => '',
            'payment_address_2' => '',
            'payment_city' => '',
            'payment_postcode' => '',
            'payment_country' => $amazon_order['DefaultShipFromLocationAddress']['CountryCode'] ?? '',
            'payment_country_id' => 0,
            'payment_zone' => $amazon_order['DefaultShipFromLocationAddress']['StateOrRegion'] ?? '',
            'payment_zone_id' => 0,
            'payment_address_format' => '',
            'payment_custom_field' => [],
            'payment_method' => 'Amazon',
            'payment_code' => 'amazon',
            'shipping_firstname' => $amazon_order['ShippingAddress']['Name'] ?? '',
            'shipping_lastname' => '',
            'shipping_company' => '',
            'shipping_address_1' => $amazon_order['ShippingAddress']['AddressLine1'] ?? '',
            'shipping_address_2' => $amazon_order['ShippingAddress']['AddressLine2'] ?? '',
            'shipping_city' => $amazon_order['ShippingAddress']['City'] ?? '',
            'shipping_postcode' => $amazon_order['ShippingAddress']['PostalCode'] ?? '',
            'shipping_country' => $amazon_order['ShippingAddress']['CountryCode'] ?? '',
            'shipping_country_id' => 0,
            'shipping_zone' => $amazon_order['ShippingAddress']['StateOrRegion'] ?? '',
            'shipping_zone_id' => 0,
            'shipping_address_format' => '',
            'shipping_custom_field' => [],
            'shipping_method' => 'Amazon Shipping',
            'shipping_code' => 'amazon_shipping',
            'comment' => 'Amazon Order: ' . $amazon_order['AmazonOrderId'],
            'total' => (float)$amazon_order['OrderTotal']['Amount'],
            'order_status_id' => $this->getOrderStatusId($amazon_order['OrderStatus']),
            'affiliate_id' => 0,
            'commission' => 0,
            'marketing_id' => 0,
            'tracking' => '',
            'language_id' => 1,
            'currency_id' => 1,
            'currency_code' => $amazon_order['OrderTotal']['CurrencyCode'],
            'currency_value' => 1.0,
            'ip' => '',
            'forwarded_ip' => '',
            'user_agent' => 'Amazon SP-API',
            'accept_language' => 'en-US',
            'date_added' => date('Y-m-d H:i:s', strtotime($amazon_order['PurchaseDate'])),
            'date_modified' => date('Y-m-d H:i:s')
        ];
        
        return $order_data;
    }
    
    /**
     * Get OpenCart order status ID from Amazon status
     * 
     * @param string $amazon_status Amazon order status
     * @return int OpenCart order status ID
     */
    private function getOrderStatusId($amazon_status) {
        $status_mapping = [
            'Pending' => 1,        // Pending
            'Unshipped' => 2,      // Processing
            'PartiallyShipped' => 3, // Shipped
            'Shipped' => 3,        // Shipped
            'Canceled' => 7,       // Cancelled
            'Unfulfillable' => 10  // Failed
        ];
        
        return isset($status_mapping[$amazon_status]) ? 
            $status_mapping[$amazon_status] : 1; // Default to Pending
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
                    'message' => 'SP-API bağlantısı aktif',
                    'last_check' => date('Y-m-d H:i:s')
                ];
            } else {
                return [
                    'status' => 'disconnected',
                    'message' => 'SP-API bağlantısı başarısız',
                    'last_check' => date('Y-m-d H:i:s')
                ];
            }
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'SP-API hatası: ' . $e->getMessage(),
                'last_check' => date('Y-m-d H:i:s')
            ];
        }
    }
} 