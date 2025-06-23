<?php
/**
 * MesChain Sync - N11 API Library
 * 
 * @package    MesChain Sync
 * @author     MesChain Development Team
 * @copyright  2024 MesChain Technologies
 * @license    Commercial License
 * @version    2.3.0
 * @link       https://www.meschain.com
 */

namespace Meschain\Api;

class N11 {
    
    private $api_key;
    private $api_secret;
    private $store_key;
    private $base_url = 'https://api.n11.com/ws/';
    private $version = '1.0';
    
    public function __construct($api_key, $api_secret, $store_key) {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->store_key = $store_key;
    }
    
    /**
     * Test API connection
     */
    public function testConnection() {
        try {
            $result = $this->makeRequest('CategoryService.wsdl', 'GetTopLevelCategories', array());
            return isset($result['result']['status']) && $result['result']['status'] == 'success';
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get product categories
     */
    public function getCategories() {
        $params = array();
        return $this->makeRequest('CategoryService.wsdl', 'GetTopLevelCategories', $params);
    }
    
    /**
     * Get sub categories
     */
    public function getSubCategories($category_id) {
        $params = array(
            'categoryId' => $category_id
        );
        return $this->makeRequest('CategoryService.wsdl', 'GetSubCategories', $params);
    }
    
    /**
     * Create product
     */
    public function createProduct($product_data) {
        $params = array(
            'product' => array(
                'productSellerCode' => $product_data['sku'],
                'title' => $product_data['title'],
                'subtitle' => $product_data['subtitle'] ?? '',
                'description' => $product_data['description'],
                'category' => array(
                    'id' => $product_data['category_id']
                ),
                'price' => $product_data['price'],
                'currencyType' => 'TL',
                'images' => array(
                    'image' => $product_data['images']
                ),
                'approvalStatus' => 1,
                'preparingDay' => $product_data['preparing_day'] ?? 1,
                'attributes' => $product_data['attributes'] ?? array()
            )
        );
        
        return $this->makeRequest('ProductService.wsdl', 'SaveProduct', $params);
    }
    
    /**
     * Update product
     */
    public function updateProduct($product_id, $product_data) {
        $params = array(
            'product' => array(
                'id' => $product_id,
                'productSellerCode' => $product_data['sku'],
                'title' => $product_data['title'],
                'subtitle' => $product_data['subtitle'] ?? '',
                'description' => $product_data['description'],
                'price' => $product_data['price'],
                'currencyType' => 'TL',
                'images' => array(
                    'image' => $product_data['images']
                ),
                'attributes' => $product_data['attributes'] ?? array()
            )
        );
        
        return $this->makeRequest('ProductService.wsdl', 'UpdateProduct', $params);
    }
    
    /**
     * Get product details
     */
    public function getProduct($product_id) {
        $params = array(
            'productId' => $product_id
        );
        
        return $this->makeRequest('ProductService.wsdl', 'GetProduct', $params);
    }
    
    /**
     * Update product stock
     */
    public function updateStock($product_id, $quantity) {
        $params = array(
            'productStockSellerCode' => $product_id,
            'quantity' => $quantity
        );
        
        return $this->makeRequest('ProductStockService.wsdl', 'UpdateStockByProductSellerCode', $params);
    }
    
    /**
     * Update product price
     */
    public function updatePrice($product_id, $price) {
        $params = array(
            'productSellerCode' => $product_id,
            'price' => $price
        );
        
        return $this->makeRequest('ProductService.wsdl', 'UpdateProductPrice', $params);
    }
    
    /**
     * Get orders
     */
    public function getOrders($start_date = null, $end_date = null, $status = null) {
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
        
        return $this->makeRequest('OrderService.wsdl', 'OrderList', $params);
    }
    
    /**
     * Get order details
     */
    public function getOrderDetails($order_id) {
        $params = array(
            'id' => $order_id
        );
        
        return $this->makeRequest('OrderService.wsdl', 'OrderDetail', $params);
    }
    
    /**
     * Update order status
     */
    public function updateOrderStatus($order_id, $status) {
        $params = array(
            'orderItemList' => array(
                'orderItem' => array(
                    'id' => $order_id,
                    'status' => $status
                )
            )
        );
        
        return $this->makeRequest('OrderService.wsdl', 'ChangeOrderStatus', $params);
    }
    
    /**
     * Make API request to N11
     */
    private function makeRequest($service, $method, $params) {
        $url = $this->base_url . $service;
        
        // Add authentication parameters
        $auth_params = array(
            'auth' => array(
                'appKey' => $this->api_key,
                'appSecret' => $this->api_secret
            )
        );
        
        $params = array_merge($auth_params, $params);
        
        $soap_client = new \SoapClient($url, array(
            'trace' => true,
            'exception' => true,
            'cache_wsdl' => WSDL_CACHE_NONE
        ));
        
        try {
            $result = $soap_client->__soapCall($method, array($params));
            return $this->parseResponse($result);
        } catch (\SoapFault $e) {
            throw new \Exception('N11 API Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Parse API response
     */
    private function parseResponse($response) {
        if (is_object($response)) {
            $response = json_decode(json_encode($response), true);
        }
        
        return $response;
    }
    
    /**
     * Get error message from response
     */
    public function getErrorMessage($response) {
        if (isset($response['result']['errorMessage'])) {
            return $response['result']['errorMessage'];
        }
        
        if (isset($response['result']['status']) && $response['result']['status'] == 'failure') {
            return 'N11 API request failed';
        }
        
        return 'Unknown error occurred';
    }
    
    /**
     * Check if response is successful
     */
    public function isSuccess($response) {
        return isset($response['result']['status']) && $response['result']['status'] == 'success';
    }
    
    /**
     * Get products by seller code
     */
    public function getProductBySellerCode($seller_code) {
        $params = array(
            'productSellerCode' => $seller_code
        );
        
        return $this->makeRequest('ProductService.wsdl', 'GetProductBySellerCode', $params);
    }
    
    /**
     * Delete product
     */
    public function deleteProduct($product_id) {
        $params = array(
            'productId' => $product_id
        );
        
        return $this->makeRequest('ProductService.wsdl', 'DeleteProduct', $params);
    }
    
    /**
     * Get product list
     */
    public function getProductList($page = 0, $size = 100) {
        $params = array(
            'pagingData' => array(
                'currentPage' => $page,
                'pageSize' => $size
            )
        );
        
        return $this->makeRequest('ProductService.wsdl', 'GetProductList', $params);
    }
}
