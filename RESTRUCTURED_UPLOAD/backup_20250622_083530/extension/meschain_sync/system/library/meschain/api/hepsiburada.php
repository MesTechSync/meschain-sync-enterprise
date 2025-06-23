<?php
namespace MesChain\Api;

/**
 * Hepsiburada API Client
 *
 * @author Cursor Development Team
 * @version 1.0.0
 */
class Hepsiburada {
    private $api_key;
    private $api_secret;
    private $merchant_id;
    private $base_url = 'https://mpop-sit.hepsiburada.com';
    private $timeout = 30;

    public function __construct($config) {
        $this->api_key = $config['api_key'];
        $this->api_secret = $config['api_secret'];
        $this->merchant_id = $config['merchant_id'] ?? '';
    }

    /**
     * Get products from marketplace
     */
    public function getProducts($limit = 100, $offset = 0): array {
        $endpoint = '/product/api/products/search';

        $params = array(
            'merchantId' => $this->merchant_id,
            'limit' => $limit,
            'offset' => $offset
        );

        $response = $this->makeRequest('GET', $endpoint, $params);

        return $response['products'] ?? array();
    }

    /**
     * Get product by ID
     */
    public function getProduct($product_id): array {
        $endpoint = '/product/api/products/' . $product_id;

        return $this->makeRequest('GET', $endpoint);
    }

    /**
     * Update product stock
     */
    public function updateStock($product_id, $quantity): bool {
        $endpoint = '/product/api/products/inventory';

        $data = array(
            'merchantSku' => $product_id,
            'quantity' => $quantity
        );

        $response = $this->makeRequest('POST', $endpoint, $data);

        return isset($response['success']) && $response['success'];
    }

    /**
     * Update product price
     */
    public function updatePrice($product_id, $price, $sale_price = null): bool {
        $endpoint = '/product/api/products/price';

        $data = array(
            'merchantSku' => $product_id,
            'price' => $price
        );

        if ($sale_price !== null) {
            $data['salePrice'] = $sale_price;
        }

        $response = $this->makeRequest('POST', $endpoint, $data);

        return isset($response['success']) && $response['success'];
    }

    /**
     * Get orders
     */
    public function getOrders($status = null, $limit = 100): array {
        $endpoint = '/order/api/orders';

        $params = array(
            'merchantId' => $this->merchant_id,
            'limit' => $limit
        );

        if ($status) {
            $params['status'] = $status;
        }

        $response = $this->makeRequest('GET', $endpoint, $params);

        return $response['orders'] ?? array();
    }

    /**
     * Get order details
     */
    public function getOrder($order_id): array {
        $endpoint = '/order/api/orders/' . $order_id;

        return $this->makeRequest('GET', $endpoint);
    }

    /**
     * Update order status
     */
    public function updateOrderStatus($order_id, $status): bool {
        $endpoint = '/order/api/orders/' . $order_id . '/status';

        $data = array(
            'status' => $status
        );

        $response = $this->makeRequest('PUT', $endpoint, $data);

        return isset($response['success']) && $response['success'];
    }

    /**
     * Ship order
     */
    public function shipOrder($order_id, $tracking_number, $carrier): bool {
        $endpoint = '/order/api/orders/' . $order_id . '/ship';

        $data = array(
            'trackingNumber' => $tracking_number,
            'shippingCompany' => $carrier
        );

        $response = $this->makeRequest('POST', $endpoint, $data);

        return isset($response['success']) && $response['success'];
    }

    /**
     * Get categories
     */
    public function getCategories(): array {
        $endpoint = '/product/api/categories';

        $response = $this->makeRequest('GET', $endpoint);

        return $response['categories'] ?? array();
    }

    /**
     * Make API request
     */
    private function makeRequest($method, $endpoint, $data = array()) {
        $url = $this->base_url . $endpoint;

        // Generate authentication header
        $timestamp = time();
        $signature = $this->generateSignature($method, $endpoint, $timestamp);

        $headers = array(
            'Authorization: Basic ' . base64_encode($this->api_key . ':' . $this->api_secret),
            'Content-Type: application/json',
            'Accept: application/json',
            'X-API-KEY: ' . $this->api_key,
            'X-Signature: ' . $signature,
            'X-Timestamp: ' . $timestamp
        );

        $ch = curl_init();

        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
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
            throw new \Exception('CURL Error: ' . $error);
        }

        if ($http_code >= 400) {
            throw new \Exception('API Error: HTTP ' . $http_code . ' - ' . $response);
        }

        return json_decode($response, true) ?: array();
    }

    /**
     * Generate signature for authentication
     */
    private function generateSignature($method, $endpoint, $timestamp): string {
        $string_to_sign = $method . "\n" . $endpoint . "\n" . $timestamp;
        return hash_hmac('sha256', $string_to_sign, $this->api_secret);
    }
}
