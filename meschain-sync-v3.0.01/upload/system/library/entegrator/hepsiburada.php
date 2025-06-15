<?php
/**
 * Hepsiburada API Integration Class
 * 
 * Bu sınıf Hepsiburada API'si ile OpenCart arasında entegrasyon sağlar.
 * Ürün, sipariş, kategori ve stok yönetimi işlemleri gerçekleştirir.
 * 
 * @category   Integration
 * @package    MesChain-Sync
 * @subpackage Hepsiburada
 * @version    1.0.0
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

namespace MesChain\Library\Entegrator;

class Hepsiburada {
    
    private $registry;
    private $config;
    private $log;
    private $username;
    private $password;
    private $merchant_id;
    private $api_base_url;
    private $test_mode = false;
    
    /**
     * API endpoints
     */
    const ENDPOINTS = [
        'token' => '/oauth/token',
        'products' => '/product/api/products',
        'product_detail' => '/product/api/products/%s',
        'orders' => '/order/api/orders',
        'order_detail' => '/order/api/orders/%s',
        'categories' => '/product/api/categories',
        'inventory' => '/product/api/inventory',
        'shipment' => '/order/api/shipments'
    ];
    
    /**
     * Constructor
     * 
     * @param object $registry OpenCart registry nesnesi
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $this->registry->get('config');
        $this->log = new \Log('hepsiburada.log');
        
        // API credentials
        $this->username = $this->config->get('module_hepsiburada_username');
        $this->password = $this->config->get('module_hepsiburada_password');
        $this->merchant_id = $this->config->get('module_hepsiburada_merchant_id');
        $this->test_mode = $this->config->get('module_hepsiburada_test_mode');
        
        // API base URL
        $this->api_base_url = $this->test_mode ? 
            'https://sandbox-api.hepsiburada.com' : 
            'https://api.hepsiburada.com';
            
        $this->log->write('Hepsiburada API initialized with ' . ($this->test_mode ? 'sandbox' : 'production') . ' mode');
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
                $this->log->write('Hepsiburada API connection test successful');
                return [
                    'success' => true,
                    'message' => 'API bağlantısı başarılı'
                ];
            } else {
                $this->log->write('Hepsiburada API connection test failed - invalid token');
                return [
                    'success' => false,
                    'error' => 'API token alınamadı'
                ];
            }
            
        } catch (Exception $e) {
            $this->log->write('Hepsiburada API connection test error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Bağlantı hatası: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get access token for API requests
     * 
     * @return string|null Access token
     */
    private function getAccessToken() {
        try {
            $data = [
                'grant_type' => 'password',
                'username' => $this->username,
                'password' => $this->password
            ];
            
            $response = $this->makeRequest('POST', self::ENDPOINTS['token'], $data, false);
            
            if (isset($response['access_token'])) {
                return $response['access_token'];
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->log->write('Error getting access token: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get products from Hepsiburada
     * 
     * @param array $filters Filter parameters
     * @return array Products data
     */
    public function getProducts($filters = []) {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                throw new Exception('Access token not available');
            }
            
            $params = array_merge([
                'limit' => 100,
                'offset' => 0
            ], $filters);
            
            $url = self::ENDPOINTS['products'] . '?' . http_build_query($params);
            $response = $this->makeRequest('GET', $url, null, true, $token);
            
            $this->log->write('Successfully retrieved ' . count($response['listings'] ?? []) . ' products from Hepsiburada');
            
            return [
                'success' => true,
                'data' => $response
            ];
            
        } catch (Exception $e) {
            $this->log->write('Error getting products: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create or update product in Hepsiburada
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
            
            // Convert OpenCart product to Hepsiburada format
            $hepsiburada_product = $this->convertProductToHepsiburadaFormat($product_data);
            
            $response = $this->makeRequest('POST', self::ENDPOINTS['products'], $hepsiburada_product, true, $token);
            
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
     * Get orders from Hepsiburada
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
                'beginDate' => date('Y-m-d', strtotime('-7 days')),
                'endDate' => date('Y-m-d'),
                'status' => 'All'
            ], $filters);
            
            $url = self::ENDPOINTS['orders'] . '?' . http_build_query($params);
            $response = $this->makeRequest('GET', $url, null, true, $token);
            
            $this->log->write('Successfully retrieved ' . count($response['orders'] ?? []) . ' orders from Hepsiburada');
            
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
     * Update order status
     * 
     * @param string $order_id Order ID
     * @param string $status New status
     * @param array $tracking_info Tracking information
     * @return array Operation result
     */
    public function updateOrderStatus($order_id, $status, $tracking_info = []) {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                throw new Exception('Access token not available');
            }
            
            $data = [
                'status' => $status
            ];
            
            if (!empty($tracking_info)) {
                $data['tracking'] = $tracking_info;
            }
            
            $url = sprintf(self::ENDPOINTS['order_detail'], $order_id);
            $response = $this->makeRequest('PUT', $url, $data, true, $token);
            
            $this->log->write('Order status updated successfully: ' . $order_id);
            
            return [
                'success' => true,
                'data' => $response
            ];
            
        } catch (Exception $e) {
            $this->log->write('Error updating order status: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get categories from Hepsiburada
     * 
     * @return array Categories data
     */
    public function getCategories() {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                throw new Exception('Access token not available');
            }
            
            $response = $this->makeRequest('GET', self::ENDPOINTS['categories'], null, true, $token);
            
            $this->log->write('Successfully retrieved categories from Hepsiburada');
            
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
     * Update product inventory
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
            
            $response = $this->makeRequest('PUT', self::ENDPOINTS['inventory'], $inventory_data, true, $token);
            
            $this->log->write('Inventory updated successfully for ' . count($inventory_data) . ' products');
            
            return [
                'success' => true,
                'data' => $response
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
     * Convert OpenCart product to Hepsiburada format
     * 
     * @param array $product OpenCart product data
     * @return array Hepsiburada formatted product
     */
    private function convertProductToHepsiburadaFormat($product) {
        $vat_rate = $this->config->get('module_hepsiburada_vat_rate') ?: 18;
        $commission_rate = $this->config->get('module_hepsiburada_commission_rate') ?: 15;
        
        // Calculate price including VAT and commission
        $base_price = (float)$product['price'];
        $price_with_vat = $base_price * (1 + $vat_rate / 100);
        $final_price = $price_with_vat * (1 + $commission_rate / 100);
        
        return [
            'HepsiburadaSku' => $product['sku'] ?: 'OC_' . $product['product_id'],
            'Title' => $product['name'],
            'ProductDescription' => strip_tags($product['description']),
            'BrandName' => $product['manufacturer'] ?: 'Default Brand',
            'CategoryName' => $product['category_name'] ?: 'Genel',
            'Price' => number_format($final_price, 2, '.', ''),
            'AvailableStock' => (int)$product['quantity'],
            'Images' => $this->prepareProductImages($product),
            'Attributes' => $this->prepareProductAttributes($product),
            'Dimensions' => [
                'Weight' => (float)($product['weight'] ?: 0),
                'Height' => (float)($product['height'] ?: 0),
                'Width' => (float)($product['width'] ?: 0),
                'Length' => (float)($product['length'] ?: 0)
            ],
            'ShippingTemplate' => $this->config->get('module_hepsiburada_shipping_company') ?: 'aras'
        ];
    }
    
    /**
     * Prepare product images for Hepsiburada
     * 
     * @param array $product Product data
     * @return array Images array
     */
    private function prepareProductImages($product) {
        $images = [];
        
        // Main image
        if (!empty($product['image'])) {
            $images[] = [
                'Url' => HTTP_CATALOG . 'image/' . $product['image'],
                'IsPrimary' => true
            ];
        }
        
        // Additional images
        if (!empty($product['additional_images'])) {
            foreach ($product['additional_images'] as $image) {
                $images[] = [
                    'Url' => HTTP_CATALOG . 'image/' . $image,
                    'IsPrimary' => false
                ];
            }
        }
        
        return $images;
    }
    
    /**
     * Prepare product attributes for Hepsiburada
     * 
     * @param array $product Product data
     * @return array Attributes array
     */
    private function prepareProductAttributes($product) {
        $attributes = [];
        
        if (!empty($product['attributes'])) {
            foreach ($product['attributes'] as $attribute) {
                $attributes[] = [
                    'Name' => $attribute['attribute'],
                    'Value' => $attribute['text']
                ];
            }
        }
        
        return $attributes;
    }
    
    /**
     * Make API request
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param bool $auth Require authentication
     * @param string $token Access token
     * @return array Response data
     * @throws Exception
     */
    private function makeRequest($method, $endpoint, $data = null, $auth = true, $token = null) {
        $url = $this->api_base_url . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        if ($auth && $token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        
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
            $error_message = isset($decoded_response['message']) ? 
                $decoded_response['message'] : 
                'HTTP Error ' . $http_code;
            throw new Exception($error_message);
        }
        
        $this->log->write('API Request: ' . $method . ' ' . $endpoint . ' - Response Code: ' . $http_code);
        
        return $decoded_response;
    }
    
    /**
     * Convert Hepsiburada order to OpenCart format
     * 
     * @param array $hepsiburada_order Hepsiburada order data
     * @return array OpenCart formatted order
     */
    public function convertOrderToOpenCartFormat($hepsiburada_order) {
        $order_data = [
            'order_id' => $hepsiburada_order['orderNumber'],
            'invoice_no' => $hepsiburada_order['invoiceNumber'] ?? '',
            'store_id' => 0,
            'store_name' => $this->config->get('config_name'),
            'store_url' => HTTP_CATALOG,
            'customer_id' => 0,
            'customer_group_id' => 1,
            'firstname' => $hepsiburada_order['customerId'] ?? 'Hepsiburada',
            'lastname' => 'Customer',
            'email' => 'noreply@hepsiburada.com',
            'telephone' => '',
            'fax' => '',
            'custom_field' => [],
            'payment_firstname' => $hepsiburada_order['billingAddress']['firstName'] ?? '',
            'payment_lastname' => $hepsiburada_order['billingAddress']['lastName'] ?? '',
            'payment_company' => '',
            'payment_address_1' => $hepsiburada_order['billingAddress']['address'] ?? '',
            'payment_address_2' => '',
            'payment_city' => $hepsiburada_order['billingAddress']['city'] ?? '',
            'payment_postcode' => $hepsiburada_order['billingAddress']['zipCode'] ?? '',
            'payment_country' => 'Türkiye',
            'payment_country_id' => 215,
            'payment_zone' => $hepsiburada_order['billingAddress']['province'] ?? '',
            'payment_zone_id' => 0,
            'payment_address_format' => '',
            'payment_custom_field' => [],
            'payment_method' => 'Hepsiburada',
            'payment_code' => 'hepsiburada',
            'shipping_firstname' => $hepsiburada_order['shippingAddress']['firstName'] ?? '',
            'shipping_lastname' => $hepsiburada_order['shippingAddress']['lastName'] ?? '',
            'shipping_company' => '',
            'shipping_address_1' => $hepsiburada_order['shippingAddress']['address'] ?? '',
            'shipping_address_2' => '',
            'shipping_city' => $hepsiburada_order['shippingAddress']['city'] ?? '',
            'shipping_postcode' => $hepsiburada_order['shippingAddress']['zipCode'] ?? '',
            'shipping_country' => 'Türkiye',
            'shipping_country_id' => 215,
            'shipping_zone' => $hepsiburada_order['shippingAddress']['province'] ?? '',
            'shipping_zone_id' => 0,
            'shipping_address_format' => '',
            'shipping_custom_field' => [],
            'shipping_method' => 'Hepsiburada Kargo',
            'shipping_code' => 'hepsiburada_shipping',
            'comment' => 'Hepsiburada siparişi: ' . $hepsiburada_order['orderNumber'],
            'total' => (float)$hepsiburada_order['totalAmount'],
            'order_status_id' => $this->getOrderStatusId($hepsiburada_order['status']),
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
            'user_agent' => 'Hepsiburada API',
            'accept_language' => 'tr-TR',
            'date_added' => date('Y-m-d H:i:s', strtotime($hepsiburada_order['orderDate'])),
            'date_modified' => date('Y-m-d H:i:s')
        ];
        
        // Order products
        $order_data['products'] = [];
        if (!empty($hepsiburada_order['items'])) {
            foreach ($hepsiburada_order['items'] as $item) {
                $order_data['products'][] = [
                    'product_id' => $this->findProductIdBySku($item['sku']),
                    'name' => $item['productName'],
                    'model' => $item['sku'],
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
                'value' => (float)$hepsiburada_order['totalAmount'],
                'sort_order' => 1
            ],
            [
                'code' => 'total',
                'title' => 'Toplam',
                'value' => (float)$hepsiburada_order['totalAmount'],
                'sort_order' => 3
            ]
        ];
        
        return $order_data;
    }
    
    /**
     * Get OpenCart order status ID from Hepsiburada status
     * 
     * @param string $hepsiburada_status Hepsiburada order status
     * @return int OpenCart order status ID
     */
    private function getOrderStatusId($hepsiburada_status) {
        $status_mapping = [
            'Confirmed' => 2,  // Processing
            'Shipped' => 3,    // Shipped
            'Delivered' => 5,  // Complete
            'Cancelled' => 7,  // Cancelled
            'Returned' => 11   // Refunded
        ];
        
        return isset($status_mapping[$hepsiburada_status]) ? 
            $status_mapping[$hepsiburada_status] : 1; // Default to Pending
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