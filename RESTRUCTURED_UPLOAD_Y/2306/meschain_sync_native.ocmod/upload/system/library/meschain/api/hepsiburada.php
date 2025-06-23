<?php
/**
 * MesChain Sync - Hepsiburada API Library
 * 
 * @package    MesChain Sync
 * @author     MesChain Development Team
 * @copyright  2024 MesChain Technologies
 * @license    Commercial License
 * @version    2.3.0
 * @link       https://www.meschain.com
 */

namespace Meschain\Api;

class Hepsiburada {
    
    private $username;
    private $password;
    private $merchant_id;
    private $base_url = 'https://mpop-sit.hepsiburada.com/';
    private $version = 'v1';
    private $token = null;
    private $token_expires = null;
    
    public function __construct($username, $password, $merchant_id) {
        $this->username = $username;
        $this->password = $password;
        $this->merchant_id = $merchant_id;
    }
    
    /**
     * Authenticate and get access token
     */
    private function authenticate() {
        if ($this->token && $this->token_expires && time() < $this->token_expires) {
            return $this->token;
        }
        
        $url = $this->base_url . 'user/merchant/authenticate';
        
        $data = array(
            'username' => $this->username,
            'password' => $this->password
        );
        
        $response = $this->makeHttpRequest('POST', $url, $data);
        
        if (isset($response['access_token'])) {
            $this->token = $response['access_token'];
            $this->token_expires = time() + ($response['expires_in'] ?? 3600) - 300; // 5 dakika erken expire
            return $this->token;
        }
        
        throw new \Exception('Hepsiburada authentication failed');
    }
    
    /**
     * Test API connection
     */
    public function testConnection() {
        try {
            $this->authenticate();
            $categories = $this->getCategories();
            return !empty($categories);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get product categories
     */
    public function getCategories() {
        $token = $this->authenticate();
        $url = $this->base_url . 'product/api/' . $this->version . '/categories';
        
        return $this->makeAuthenticatedRequest('GET', $url, null, $token);
    }
    
    /**
     * Get category attributes
     */
    public function getCategoryAttributes($category_id) {
        $token = $this->authenticate();
        $url = $this->base_url . 'product/api/' . $this->version . '/categories/' . $category_id . '/attributes';
        
        return $this->makeAuthenticatedRequest('GET', $url, null, $token);
    }
    
    /**
     * Create product
     */
    public function createProduct($product_data) {
        $token = $this->authenticate();
        $url = $this->base_url . 'product/api/' . $this->version . '/products';
        
        $data = array(
            'merchantId' => $this->merchant_id,
            'productName' => $product_data['name'],
            'categoryId' => $product_data['category_id'],
            'merchantSku' => $product_data['sku'],
            'description' => $product_data['description'],
            'attributes' => $product_data['attributes'] ?? array(),
            'variants' => array(
                array(
                    'merchantSku' => $product_data['sku'],
                    'attributes' => $product_data['variant_attributes'] ?? array(),
                    'price' => $product_data['price'],
                    'availableStock' => $product_data['stock'] ?? 0,
                    'images' => $product_data['images'] ?? array()
                )
            )
        );
        
        return $this->makeAuthenticatedRequest('POST', $url, $data, $token);
    }
    
    /**
     * Update product
     */
    public function updateProduct($product_id, $product_data) {
        $token = $this->authenticate();
        $url = $this->base_url . 'product/api/' . $this->version . '/products/' . $product_id;
        
        $data = array(
            'productName' => $product_data['name'],
            'description' => $product_data['description'],
            'attributes' => $product_data['attributes'] ?? array()
        );
        
        return $this->makeAuthenticatedRequest('PUT', $url, $data, $token);
    }
    
    /**
     * Get product details
     */
    public function getProduct($product_id) {
        $token = $this->authenticate();
        $url = $this->base_url . 'product/api/' . $this->version . '/products/' . $product_id;
        
        return $this->makeAuthenticatedRequest('GET', $url, null, $token);
    }
    
    /**
     * Update product stock
     */
    public function updateStock($sku, $quantity) {
        $token = $this->authenticate();
        $url = $this->base_url . 'product/api/' . $this->version . '/variants/stocks';
        
        $data = array(
            'items' => array(
                array(
                    'merchantSku' => $sku,
                    'availableStock' => $quantity
                )
            )
        );
        
        return $this->makeAuthenticatedRequest('PUT', $url, $data, $token);
    }
    
    /**
     * Update product price
     */
    public function updatePrice($sku, $price) {
        $token = $this->authenticate();
        $url = $this->base_url . 'product/api/' . $this->version . '/variants/prices';
        
        $data = array(
            'items' => array(
                array(
                    'merchantSku' => $sku,
                    'price' => $price
                )
            )
        );
        
        return $this->makeAuthenticatedRequest('PUT', $url, $data, $token);
    }
    
    /**
     * Get orders
     */
    public function getOrders($start_date = null, $end_date = null, $status = null) {
        $token = $this->authenticate();
        $url = $this->base_url . 'order/api/' . $this->version . '/orders';
        
        $params = array();
        
        if ($start_date) {
            $params['startDate'] = $start_date;
        }
        
        if ($end_date) {
            $params['endDate'] = $end_date;
        }
        
        if ($status) {
            $params['status'] = $status;
        }
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $this->makeAuthenticatedRequest('GET', $url, null, $token);
    }
    
    /**
     * Get order details
     */
    public function getOrderDetails($order_id) {
        $token = $this->authenticate();
        $url = $this->base_url . 'order/api/' . $this->version . '/orders/' . $order_id;
        
        return $this->makeAuthenticatedRequest('GET', $url, null, $token);
    }
    
    /**
     * Update order status
     */
    public function updateOrderStatus($package_id, $status) {
        $token = $this->authenticate();
        $url = $this->base_url . 'order/api/' . $this->version . '/packages/' . $package_id . '/status';
        
        $data = array(
            'status' => $status
        );
        
        return $this->makeAuthenticatedRequest('PUT', $url, $data, $token);
    }
    
    /**
     * Ship order
     */
    public function shipOrder($package_id, $tracking_number, $carrier_id) {
        $token = $this->authenticate();
        $url = $this->base_url . 'order/api/' . $this->version . '/packages/' . $package_id . '/ship';
        
        $data = array(
            'trackingNumber' => $tracking_number,
            'carrierId' => $carrier_id
        );
        
        return $this->makeAuthenticatedRequest('POST', $url, $data, $token);
    }
    
    /**
     * Get product list
     */
    public function getProductList($page = 0, $size = 100) {
        $token = $this->authenticate();
        $url = $this->base_url . 'product/api/' . $this->version . '/products?merchantId=' . $this->merchant_id . '&offset=' . $page . '&limit=' . $size;
        
        return $this->makeAuthenticatedRequest('GET', $url, null, $token);
    }
    
    /**
     * Delete product
     */
    public function deleteProduct($product_id) {
        $token = $this->authenticate();
        $url = $this->base_url . 'product/api/' . $this->version . '/products/' . $product_id;
        
        return $this->makeAuthenticatedRequest('DELETE', $url, null, $token);
    }
    
    /**
     * Make authenticated HTTP request
     */
    private function makeAuthenticatedRequest($method, $url, $data = null, $token = null) {
        $headers = array();
        
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        
        return $this->makeHttpRequest($method, $url, $data, $headers);
    }
    
    /**
     * Make HTTP request
     */
    private function makeHttpRequest($method, $url, $data = null, $headers = array()) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MesChain-Sync/2.3.0');
        
        $default_headers = array(
            'Content-Type: application/json',
            'Accept: application/json'
        );
        
        $headers = array_merge($default_headers, $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('HTTP Request Error: ' . $error);
        }
        
        if ($http_code >= 400) {
            throw new \Exception('HTTP Error ' . $http_code . ': ' . $response);
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
        }
        
        return $decoded;
    }
    
    /**
     * Check if response is successful
     */
    public function isSuccess($response) {
        return !isset($response['error']) && !isset($response['message']);
    }
    
    /**
     * Get error message from response
     */
    public function getErrorMessage($response) {
        if (isset($response['message'])) {
            return $response['message'];
        }
        
        if (isset($response['error'])) {
            return $response['error'];
        }
        
        return 'Unknown error occurred';
    }
}
