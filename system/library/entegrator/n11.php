<?php
/**
 * N11 API Integration Class
 * 
 * Bu sınıf N11 API'si ile OpenCart arasında entegrasyon sağlar.
 * Ürün, sipariş, kategori ve stok yönetimi işlemleri gerçekleştirir.
 * 
 * @category   Integration
 * @package    MesChain-Sync
 * @subpackage N11
 * @version    1.0.0
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

namespace MesChain\Library\Entegrator;

class N11 {
    
    private $registry;
    private $config;
    private $log;
    private $app_key;
    private $app_secret;
    private $api_base_url;
    
    /**
     * API endpoints
     */
    const ENDPOINTS = [
        'auth' => '/ws/auth.wsdl',
        'product' => '/ws/ProductService.wsdl',
        'order' => '/ws/OrderService.wsdl',
        'category' => '/ws/CategoryService.wsdl',
        'shipment' => '/ws/ShipmentService.wsdl',
        'city' => '/ws/CityService.wsdl'
    ];
    
    /**
     * Constructor
     * 
     * @param object $registry OpenCart registry nesnesi
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $this->registry->get('config');
        $this->log = new \Log('n11.log');
        
        // API credentials
        $this->app_key = $this->config->get('module_n11_app_key');
        $this->app_secret = $this->config->get('module_n11_app_secret');
        
        // API base URL
        $this->api_base_url = 'https://api.n11.com';
        
        $this->log->write('N11 API initialized');
    }
    
    /**
     * Test API connection
     * 
     * @return array Connection test result
     */
    public function testConnection() {
        try {
            $auth_data = $this->authenticate();
            
            if ($auth_data && isset($auth_data['token'])) {
                $this->log->write('N11 API connection test successful');
                return [
                    'success' => true,
                    'message' => 'API bağlantısı başarılı'
                ];
            } else {
                $this->log->write('N11 API connection test failed - authentication failed');
                return [
                    'success' => false,
                    'error' => 'API kimlik doğrulama başarısız'
                ];
            }
            
        } catch (Exception $e) {
            $this->log->write('N11 API connection test error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Bağlantı hatası: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Authenticate with N11 API
     * 
     * @return array|null Authentication result
     */
    private function authenticate() {
        try {
            $client = new \SoapClient($this->api_base_url . self::ENDPOINTS['auth']);
            
            $params = [
                'auth' => [
                    'appKey' => $this->app_key,
                    'appSecret' => $this->app_secret
                ]
            ];
            
            $response = $client->Authenticate($params);
            
            if (isset($response->result->status) && $response->result->status == 'success') {
                return [
                    'token' => $response->result->token,
                    'expires' => time() + 3600 // 1 hour
                ];
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->log->write('Error authenticating with N11: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get products from N11
     * 
     * @param array $filters Filter parameters
     * @return array Products data
     */
    public function getProducts($filters = []) {
        try {
            $auth_data = $this->authenticate();
            if (!$auth_data) {
                throw new Exception('Authentication failed');
            }
            
            $client = new \SoapClient($this->api_base_url . self::ENDPOINTS['product']);
            
            $params = [
                'auth' => [
                    'appKey' => $this->app_key,
                    'appSecret' => $this->app_secret,
                    'token' => $auth_data['token']
                ],
                'pagingData' => [
                    'currentPage' => $filters['page'] ?? 0,
                    'pageSize' => $filters['limit'] ?? 100
                ],
                'productSellerCode' => $filters['seller_code'] ?? null
            ];
            
            $response = $client->GetProducts($params);
            
            if (isset($response->result->status) && $response->result->status == 'success') {
                $this->log->write('Successfully retrieved products from N11');
                
                return [
                    'success' => true,
                    'data' => $response->result
                ];
            } else {
                throw new Exception('API returned error: ' . ($response->result->errorMessage ?? 'Unknown error'));
            }
            
        } catch (Exception $e) {
            $this->log->write('Error getting products: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create or update product in N11
     * 
     * @param array $product_data Product information
     * @return array Operation result
     */
    public function syncProduct($product_data) {
        try {
            $auth_data = $this->authenticate();
            if (!$auth_data) {
                throw new Exception('Authentication failed');
            }
            
            $client = new \SoapClient($this->api_base_url . self::ENDPOINTS['product']);
            
            // Convert OpenCart product to N11 format
            $n11_product = $this->convertProductToN11Format($product_data);
            
            $params = [
                'auth' => [
                    'appKey' => $this->app_key,
                    'appSecret' => $this->app_secret,
                    'token' => $auth_data['token']
                ],
                'product' => $n11_product
            ];
            
            $response = $client->SaveProduct($params);
            
            if (isset($response->result->status) && $response->result->status == 'success') {
                $this->log->write('Product synced successfully: ' . $product_data['name']);
                
                return [
                    'success' => true,
                    'data' => $response->result
                ];
            } else {
                throw new Exception('API returned error: ' . ($response->result->errorMessage ?? 'Unknown error'));
            }
            
        } catch (Exception $e) {
            $this->log->write('Error syncing product: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get orders from N11
     * 
     * @param array $filters Filter parameters
     * @return array Orders data
     */
    public function getOrders($filters = []) {
        try {
            $auth_data = $this->authenticate();
            if (!$auth_data) {
                throw new Exception('Authentication failed');
            }
            
            $client = new \SoapClient($this->api_base_url . self::ENDPOINTS['order']);
            
            $params = [
                'auth' => [
                    'appKey' => $this->app_key,
                    'appSecret' => $this->app_secret,
                    'token' => $auth_data['token']
                ],
                'searchData' => [
                    'productId' => $filters['product_id'] ?? null,
                    'status' => $filters['status'] ?? null,
                    'buyerName' => $filters['buyer_name'] ?? null,
                    'orderNumber' => $filters['order_number'] ?? null,
                    'beginDate' => $filters['begin_date'] ?? date('d/m/Y', strtotime('-7 days')),
                    'endDate' => $filters['end_date'] ?? date('d/m/Y'),
                    'pagingData' => [
                        'currentPage' => $filters['page'] ?? 0,
                        'pageSize' => $filters['limit'] ?? 100
                    ]
                ]
            ];
            
            $response = $client->OrderList($params);
            
            if (isset($response->result->status) && $response->result->status == 'success') {
                $this->log->write('Successfully retrieved orders from N11');
                
                return [
                    'success' => true,
                    'data' => $response->result
                ];
            } else {
                throw new Exception('API returned error: ' . ($response->result->errorMessage ?? 'Unknown error'));
            }
            
        } catch (Exception $e) {
            $this->log->write('Error getting orders: ' . $e->getMessage());
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
     * @return array Operation result
     */
    public function updateOrderStatus($order_id, $status, $tracking_info = []) {
        try {
            $auth_data = $this->authenticate();
            if (!$auth_data) {
                throw new Exception('Authentication failed');
            }
            
            $client = new \SoapClient($this->api_base_url . self::ENDPOINTS['order']);
            
            $params = [
                'auth' => [
                    'appKey' => $this->app_key,
                    'appSecret' => $this->app_secret,
                    'token' => $auth_data['token']
                ],
                'orderItemList' => [
                    'orderItem' => [
                        'id' => $order_id,
                        'status' => $status
                    ]
                ]
            ];
            
            if (!empty($tracking_info)) {
                $params['orderItemList']['orderItem']['trackingNumber'] = $tracking_info['tracking_number'] ?? '';
                $params['orderItemList']['orderItem']['cargoCompany'] = $tracking_info['carrier'] ?? '';
            }
            
            $response = $client->ChangeOrderStatus($params);
            
            if (isset($response->result->status) && $response->result->status == 'success') {
                $this->log->write('Order status updated successfully: ' . $order_id);
                
                return [
                    'success' => true,
                    'data' => $response->result
                ];
            } else {
                throw new Exception('API returned error: ' . ($response->result->errorMessage ?? 'Unknown error'));
            }
            
        } catch (Exception $e) {
            $this->log->write('Error updating order status: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get categories from N11
     * 
     * @return array Categories data
     */
    public function getCategories() {
        try {
            $auth_data = $this->authenticate();
            if (!$auth_data) {
                throw new Exception('Authentication failed');
            }
            
            $client = new \SoapClient($this->api_base_url . self::ENDPOINTS['category']);
            
            $params = [
                'auth' => [
                    'appKey' => $this->app_key,
                    'appSecret' => $this->app_secret,
                    'token' => $auth_data['token']
                ]
            ];
            
            $response = $client->GetTopLevelCategories($params);
            
            if (isset($response->result->status) && $response->result->status == 'success') {
                $this->log->write('Successfully retrieved categories from N11');
                
                return [
                    'success' => true,
                    'data' => $response->result
                ];
            } else {
                throw new Exception('API returned error: ' . ($response->result->errorMessage ?? 'Unknown error'));
            }
            
        } catch (Exception $e) {
            $this->log->write('Error getting categories: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update product inventory
     * 
     * @param array $inventory_data Inventory information
     * @return array Operation result
     */
    public function updateInventory($inventory_data) {
        try {
            $auth_data = $this->authenticate();
            if (!$auth_data) {
                throw new Exception('Authentication failed');
            }
            
            $client = new \SoapClient($this->api_base_url . self::ENDPOINTS['product']);
            
            $results = [];
            
            foreach ($inventory_data as $item) {
                $params = [
                    'auth' => [
                        'appKey' => $this->app_key,
                        'appSecret' => $this->app_secret,
                        'token' => $auth_data['token']
                    ],
                    'productSellerCode' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'sellerStockCode' => $item['stock_code'] ?? $item['sku']
                ];
                
                $response = $client->UpdateProductBasic($params);
                
                $results[] = [
                    'sku' => $item['sku'],
                    'success' => isset($response->result->status) && $response->result->status == 'success',
                    'message' => $response->result->errorMessage ?? 'Success'
                ];
            }
            
            $this->log->write('Inventory updated for ' . count($inventory_data) . ' products');
            
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
     * Convert OpenCart product to N11 format
     * 
     * @param array $product OpenCart product data
     * @return array N11 formatted product
     */
    private function convertProductToN11Format($product) {
        $commission_rate = $this->config->get('module_n11_commission_rate') ?: 8;
        
        // Calculate price including commission
        $base_price = (float)$product['price'];
        $final_price = $base_price * (1 + $commission_rate / 100);
        
        return [
            'productSellerCode' => $product['sku'] ?: 'OC_' . $product['product_id'],
            'title' => $product['name'],
            'subtitle' => substr($product['name'], 0, 100),
            'description' => strip_tags($product['description']),
            'category' => [
                'id' => $this->config->get('module_n11_default_category') ?: 1000
            ],
            'price' => number_format($final_price, 2, '.', ''),
            'currencyType' => 1, // TL
            'images' => $this->prepareProductImages($product),
            'approvalStatus' => 1,
            'preparingDay' => $this->config->get('module_n11_preparing_days') ?: 1,
            'attributes' => $this->prepareProductAttributes($product),
            'stockItems' => [
                'stockItem' => [
                    'bundle' => false,
                    'mpn' => $product['model'] ?: $product['sku'],
                    'gtin' => $product['ean'] ?? '',
                    'oem' => $product['manufacturer_model'] ?? '',
                    'quantity' => (int)$product['quantity'],
                    'sellerStockCode' => $product['sku'] ?: 'OC_' . $product['product_id'],
                    'attributes' => [
                        'attribute' => []
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Prepare product images for N11
     * 
     * @param array $product Product data
     * @return array Images array
     */
    private function prepareProductImages($product) {
        $images = [
            'image' => []
        ];
        
        // Main image
        if (!empty($product['image'])) {
            $images['image'][] = [
                'url' => HTTP_CATALOG . 'image/' . $product['image'],
                'order' => 1
            ];
        }
        
        // Additional images
        if (!empty($product['additional_images'])) {
            $order = 2;
            foreach ($product['additional_images'] as $image) {
                if ($order <= 8) { // N11 allows max 8 images
                    $images['image'][] = [
                        'url' => HTTP_CATALOG . 'image/' . $image,
                        'order' => $order
                    ];
                    $order++;
                }
            }
        }
        
        return $images;
    }
    
    /**
     * Prepare product attributes for N11
     * 
     * @param array $product Product data
     * @return array Attributes array
     */
    private function prepareProductAttributes($product) {
        $attributes = [
            'attribute' => []
        ];
        
        if (!empty($product['attributes'])) {
            foreach ($product['attributes'] as $attribute) {
                $attributes['attribute'][] = [
                    'name' => $attribute['attribute'],
                    'value' => $attribute['text']
                ];
            }
        }
        
        // Add default attributes
        if (!empty($product['weight'])) {
            $attributes['attribute'][] = [
                'name' => 'Ağırlık',
                'value' => $product['weight'] . ' kg'
            ];
        }
        
        if (!empty($product['manufacturer'])) {
            $attributes['attribute'][] = [
                'name' => 'Marka',
                'value' => $product['manufacturer']
            ];
        }
        
        return $attributes;
    }
    
    /**
     * Convert N11 order to OpenCart format
     * 
     * @param array $n11_order N11 order data
     * @return array OpenCart formatted order
     */
    public function convertOrderToOpenCartFormat($n11_order) {
        $order_data = [
            'order_id' => $n11_order['orderNumber'],
            'invoice_no' => $n11_order['invoiceNumber'] ?? '',
            'store_id' => 0,
            'store_name' => $this->config->get('config_name'),
            'store_url' => HTTP_CATALOG,
            'customer_id' => 0,
            'customer_group_id' => 1,
            'firstname' => $n11_order['buyerName'] ?? 'N11',
            'lastname' => 'Customer',
            'email' => 'noreply@n11.com',
            'telephone' => $n11_order['phone'] ?? '',
            'fax' => '',
            'custom_field' => [],
            'payment_firstname' => $n11_order['billingAddress']['firstName'] ?? '',
            'payment_lastname' => $n11_order['billingAddress']['lastName'] ?? '',
            'payment_company' => '',
            'payment_address_1' => $n11_order['billingAddress']['address'] ?? '',
            'payment_address_2' => '',
            'payment_city' => $n11_order['billingAddress']['city'] ?? '',
            'payment_postcode' => $n11_order['billingAddress']['postalCode'] ?? '',
            'payment_country' => 'Türkiye',
            'payment_country_id' => 215,
            'payment_zone' => $n11_order['billingAddress']['district'] ?? '',
            'payment_zone_id' => 0,
            'payment_address_format' => '',
            'payment_custom_field' => [],
            'payment_method' => 'N11',
            'payment_code' => 'n11',
            'shipping_firstname' => $n11_order['shippingAddress']['firstName'] ?? '',
            'shipping_lastname' => $n11_order['shippingAddress']['lastName'] ?? '',
            'shipping_company' => '',
            'shipping_address_1' => $n11_order['shippingAddress']['address'] ?? '',
            'shipping_address_2' => '',
            'shipping_city' => $n11_order['shippingAddress']['city'] ?? '',
            'shipping_postcode' => $n11_order['shippingAddress']['postalCode'] ?? '',
            'shipping_country' => 'Türkiye',
            'shipping_country_id' => 215,
            'shipping_zone' => $n11_order['shippingAddress']['district'] ?? '',
            'shipping_zone_id' => 0,
            'shipping_address_format' => '',
            'shipping_custom_field' => [],
            'shipping_method' => 'N11 Kargo',
            'shipping_code' => 'n11_shipping',
            'comment' => 'N11 siparişi: ' . $n11_order['orderNumber'],
            'total' => (float)$n11_order['totalAmount'],
            'order_status_id' => $this->getOrderStatusId($n11_order['status']),
            'affiliate_id' => 0,
            'commission' => 0,
            'marketing_id' => 0,
            'tracking' => '',
            'language_id' => 2,
            'currency_id' => 1,
            'currency_code' => 'TRY',
            'currency_value' => 1.0,
            'ip' => '',
            'forwarded_ip' => '',
            'user_agent' => 'N11 API',
            'accept_language' => 'tr-TR',
            'date_added' => date('Y-m-d H:i:s', strtotime($n11_order['createDate'])),
            'date_modified' => date('Y-m-d H:i:s')
        ];
        
        // Order products
        $order_data['products'] = [];
        if (!empty($n11_order['orderItemList'])) {
            foreach ($n11_order['orderItemList'] as $item) {
                $order_data['products'][] = [
                    'product_id' => $this->findProductIdBySku($item['productSellerCode']),
                    'name' => $item['productName'],
                    'model' => $item['productSellerCode'],
                    'option' => [],
                    'quantity' => (int)$item['quantity'],
                    'price' => (float)$item['price'],
                    'total' => (float)$item['totalPrice'],
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
                'value' => (float)$n11_order['totalAmount'],
                'sort_order' => 1
            ],
            [
                'code' => 'total',
                'title' => 'Toplam',
                'value' => (float)$n11_order['totalAmount'],
                'sort_order' => 3
            ]
        ];
        
        return $order_data;
    }
    
    /**
     * Get OpenCart order status ID from N11 status
     * 
     * @param string $n11_status N11 order status
     * @return int OpenCart order status ID
     */
    private function getOrderStatusId($n11_status) {
        $status_mapping = [
            'Onaylandı' => 2,       // Processing
            'Kargoya Verildi' => 3, // Shipped
            'Teslim Edildi' => 5,   // Complete
            'İptal Edildi' => 7,    // Cancelled
            'İade Edildi' => 11     // Refunded
        ];
        
        return isset($status_mapping[$n11_status]) ? 
            $status_mapping[$n11_status] : 1; // Default to Pending
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
            $auth_data = $this->authenticate();
            
            if ($auth_data && isset($auth_data['token'])) {
                return [
                    'status' => 'connected',
                    'message' => 'API bağlantısı aktif',
                    'last_check' => date('Y-m-d H:i:s')
                ];
            } else {
                return [
                    'status' => 'disconnected',
                    'message' => 'API bağlantısı başarısız',
                    'last_check' => date('Y-m-d H:i:s')
                ];
            }
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'API hatası: ' . $e->getMessage(),
                'last_check' => date('Y-m-d H:i:s')
            ];
        }
    }
} 