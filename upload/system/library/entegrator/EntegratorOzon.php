<?php
/**
 * MesChain-Sync - Ozon API Entegratörü
 * 
 * Bu sınıf, Ozon pazaryeri API'si ile entegrasyon sağlar.
 * Ürün yönetimi, sipariş yönetimi, kategori yönetimi ve diğer API işlemlerini gerçekleştirir.
 * 
 * @author      MesTech
 * @copyright   Copyright (c) 2023, MesTech
 * @license     MIT License
 * @version     1.0.0
 */
class EntegratorOzon {
    
    private $api_url;
    private $client_id;
    private $api_key;
    private $timeout;
    private $last_error;
    private $debug_mode;
    
    /**
     * Constructor
     */
    public function __construct($config = array()) {
        $this->api_url = isset($config['api_url']) ? $config['api_url'] : 'https://api-seller.ozon.ru';
        $this->client_id = isset($config['client_id']) ? $config['client_id'] : '';
        $this->api_key = isset($config['api_key']) ? $config['api_key'] : '';
        $this->timeout = isset($config['timeout']) ? $config['timeout'] : 30;
        $this->debug_mode = isset($config['debug']) ? $config['debug'] : false;
        $this->last_error = '';
    }
    
    /**
     * API bağlantı testi
     */
    public function testConnection() {
        try {
            $response = $this->makeRequest('GET', '/v1/seller/info');
            
            if ($response && isset($response['result'])) {
                return array(
                    'success' => true,
                    'message' => 'API bağlantısı başarılı',
                    'data' => $response['result']
                );
            } else {
                return array(
                    'success' => false,
                    'message' => 'API bağlantısı başarısız: ' . $this->getLastError()
                );
            }
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'API bağlantı hatası: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Ürünleri getir
     */
    public function getProducts($filter = array()) {
        $params = array(
            'filter' => array(
                'offer_id' => isset($filter['offer_id']) ? $filter['offer_id'] : array(),
                'product_id' => isset($filter['product_id']) ? $filter['product_id'] : array(),
                'visibility' => isset($filter['visibility']) ? $filter['visibility'] : 'ALL'
            ),
            'last_id' => isset($filter['last_id']) ? $filter['last_id'] : '',
            'limit' => isset($filter['limit']) ? $filter['limit'] : 100
        );
        
        $response = $this->makeRequest('POST', '/v2/product/list', $params);
        
        if ($response && isset($response['result']['items'])) {
            return $response['result']['items'];
        }
        
        return array();
    }
    
    /**
     * Ürün detaylarını getir
     */
    public function getProductInfo($product_ids) {
        if (!is_array($product_ids)) {
            $product_ids = array($product_ids);
        }
        
        $params = array(
            'product_id' => $product_ids,
            'sku' => array(),
            'offer_id' => array()
        );
        
        $response = $this->makeRequest('POST', '/v2/product/info', $params);
        
        if ($response && isset($response['result']['items'])) {
            return $response['result']['items'];
        }
        
        return array();
    }
    
    /**
     * Ürün stok bilgilerini getir
     */
    public function getProductStocks($filter = array()) {
        $params = array(
            'filter' => array(
                'offer_id' => isset($filter['offer_id']) ? $filter['offer_id'] : array(),
                'product_id' => isset($filter['product_id']) ? $filter['product_id'] : array(),
                'warehouse_type' => isset($filter['warehouse_type']) ? $filter['warehouse_type'] : 'ALL'
            ),
            'last_id' => isset($filter['last_id']) ? $filter['last_id'] : '',
            'limit' => isset($filter['limit']) ? $filter['limit'] : 100
        );
        
        $response = $this->makeRequest('POST', '/v3/product/info/stocks', $params);
        
        if ($response && isset($response['result']['items'])) {
            return $response['result']['items'];
        }
        
        return array();
    }
    
    /**
     * Ürün fiyatlarını getir
     */
    public function getProductPrices($filter = array()) {
        $params = array(
            'filter' => array(
                'offer_id' => isset($filter['offer_id']) ? $filter['offer_id'] : array(),
                'product_id' => isset($filter['product_id']) ? $filter['product_id'] : array()
            ),
            'last_id' => isset($filter['last_id']) ? $filter['last_id'] : '',
            'limit' => isset($filter['limit']) ? $filter['limit'] : 100
        );
        
        $response = $this->makeRequest('POST', '/v4/product/info/prices', $params);
        
        if ($response && isset($response['result']['items'])) {
            return $response['result']['items'];
        }
        
        return array();
    }
    
    /**
     * Ürün oluştur/güncelle
     */
    public function createOrUpdateProduct($product_data) {
        $params = array(
            'items' => array($product_data)
        );
        
        $response = $this->makeRequest('POST', '/v2/product/import', $params);
        
        if ($response && isset($response['result'])) {
            return $response['result'];
        }
        
        return false;
    }
    
    /**
     * Ürün stok güncelle
     */
    public function updateProductStock($stocks) {
        if (!is_array($stocks)) {
            $stocks = array($stocks);
        }
        
        $params = array(
            'stocks' => $stocks
        );
        
        $response = $this->makeRequest('POST', '/v1/product/import/stocks', $params);
        
        if ($response && isset($response['result'])) {
            return $response['result'];
        }
        
        return false;
    }
    
    /**
     * Ürün fiyat güncelle
     */
    public function updateProductPrice($prices) {
        if (!is_array($prices)) {
            $prices = array($prices);
        }
        
        $params = array(
            'prices' => $prices
        );
        
        $response = $this->makeRequest('POST', '/v1/product/import/prices', $params);
        
        if ($response && isset($response['result'])) {
            return $response['result'];
        }
        
        return false;
    }
    
    /**
     * Siparişleri getir
     */
    public function getOrders($filter = array()) {
        $params = array(
            'dir' => isset($filter['dir']) ? $filter['dir'] : 'ASC',
            'filter' => array(
                'since' => isset($filter['since']) ? $filter['since'] : date('Y-m-d\TH:i:s\Z', strtotime('-30 days')),
                'to' => isset($filter['to']) ? $filter['to'] : date('Y-m-d\TH:i:s\Z'),
                'status' => isset($filter['status']) ? $filter['status'] : ''
            ),
            'limit' => isset($filter['limit']) ? $filter['limit'] : 100,
            'offset' => isset($filter['offset']) ? $filter['offset'] : 0
        );
        
        $response = $this->makeRequest('POST', '/v3/posting/fbs/list', $params);
        
        if ($response && isset($response['result']['postings'])) {
            return $response['result']['postings'];
        }
        
        return array();
    }
    
    /**
     * Sipariş detaylarını getir
     */
    public function getOrderInfo($posting_number) {
        $params = array(
            'posting_number' => $posting_number,
            'translit' => true,
            'with' => array(
                'analytics_data' => true,
                'financial_data' => true
            )
        );
        
        $response = $this->makeRequest('POST', '/v3/posting/fbs/get', $params);
        
        if ($response && isset($response['result'])) {
            return $response['result'];
        }
        
        return false;
    }
    
    /**
     * Sipariş durumunu güncelle
     */
    public function updateOrderStatus($posting_number, $status, $tracking_number = '') {
        $params = array(
            'posting_number' => $posting_number,
            'tracking_number' => $tracking_number,
            'notify_customer' => true
        );
        
        $endpoint = '';
        switch ($status) {
            case 'ship':
                $endpoint = '/v2/posting/fbs/ship';
                break;
            case 'cancel':
                $endpoint = '/v2/posting/fbs/cancel';
                $params['cancel_reason_id'] = 352; // Seller cancelled
                break;
            default:
                throw new Exception('Geçersiz sipariş durumu: ' . $status);
        }
        
        $response = $this->makeRequest('POST', $endpoint, $params);
        
        if ($response && isset($response['result'])) {
            return $response['result'];
        }
        
        return false;
    }
    
    /**
     * Kategorileri getir
     */
    public function getCategories($filter = array()) {
        $params = array(
            'attribute_type' => isset($filter['attribute_type']) ? $filter['attribute_type'] : 'ALL',
            'language' => isset($filter['language']) ? $filter['language'] : 'DEFAULT'
        );
        
        $response = $this->makeRequest('POST', '/v1/category/tree', $params);
        
        if ($response && isset($response['result'])) {
            return $response['result'];
        }
        
        return array();
    }
    
    /**
     * Kategori özelliklerini getir
     */
    public function getCategoryAttributes($category_id, $language = 'DEFAULT') {
        $params = array(
            'category_id' => array($category_id),
            'type' => 'ALL',
            'language' => $language
        );
        
        $response = $this->makeRequest('POST', '/v1/category/attribute', $params);
        
        if ($response && isset($response['result'])) {
            return $response['result'];
        }
        
        return array();
    }
    
    /**
     * Satıcı bilgilerini getir
     */
    public function getSellerInfo() {
        $response = $this->makeRequest('GET', '/v1/seller/info');
        
        if ($response && isset($response['result'])) {
            return $response['result'];
        }
        
        return false;
    }
    
    /**
     * Warehouse bilgilerini getir
     */
    public function getWarehouses() {
        $response = $this->makeRequest('POST', '/v1/warehouse/list');
        
        if ($response && isset($response['result'])) {
            return $response['result'];
        }
        
        return array();
    }
    
    /**
     * Kargo şirketlerini getir
     */
    public function getDeliveryMethods() {
        $response = $this->makeRequest('POST', '/v1/delivery-method/list');
        
        if ($response && isset($response['result'])) {
            return $response['result'];
        }
        
        return array();
    }
    
    /**
     * Raporları getir
     */
    public function getReports($filter = array()) {
        $params = array(
            'filter' => array(
                'processed_at_from' => isset($filter['from']) ? $filter['from'] : date('Y-m-d\TH:i:s\Z', strtotime('-30 days')),
                'processed_at_to' => isset($filter['to']) ? $filter['to'] : date('Y-m-d\TH:i:s\Z')
            ),
            'limit' => isset($filter['limit']) ? $filter['limit'] : 100,
            'offset' => isset($filter['offset']) ? $filter['offset'] : 0
        );
        
        $response = $this->makeRequest('POST', '/v1/report/list', $params);
        
        if ($response && isset($response['result'])) {
            return $response['result'];
        }
        
        return array();
    }
    
    /**
     * HTTP isteği gönder
     */
    private function makeRequest($method, $endpoint, $data = null) {
        $url = $this->api_url . $endpoint;
        
        $headers = array(
            'Client-Id: ' . $this->client_id,
            'Api-Key: ' . $this->api_key,
            'Content-Type: application/json'
        );
        
        $ch = curl_init();
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'MesChain-Sync/1.0'
        ));
        
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
            case 'GET':
            default:
                if ($data) {
                    $url .= '?' . http_build_query($data);
                    curl_setopt($ch, CURLOPT_URL, $url);
                }
                break;
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            $this->last_error = 'cURL Error: ' . $error;
            throw new Exception($this->last_error);
        }
        
        if ($this->debug_mode) {
            error_log('Ozon API Request: ' . $method . ' ' . $url);
            error_log('Ozon API Request Data: ' . json_encode($data));
            error_log('Ozon API Response Code: ' . $http_code);
            error_log('Ozon API Response: ' . $response);
        }
        
        $decoded_response = json_decode($response, true);
        
        if ($http_code >= 400) {
            $error_message = 'HTTP Error ' . $http_code;
            
            if ($decoded_response && isset($decoded_response['message'])) {
                $error_message .= ': ' . $decoded_response['message'];
            } elseif ($decoded_response && isset($decoded_response['error'])) {
                $error_message .= ': ' . $decoded_response['error'];
            }
            
            $this->last_error = $error_message;
            throw new Exception($this->last_error);
        }
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->last_error = 'JSON Decode Error: ' . json_last_error_msg();
            throw new Exception($this->last_error);
        }
        
        return $decoded_response;
    }
    
    /**
     * Son hatayı getir
     */
    public function getLastError() {
        return $this->last_error;
    }
    
    /**
     * Debug modunu ayarla
     */
    public function setDebugMode($debug) {
        $this->debug_mode = (bool)$debug;
    }
    
    /**
     * Timeout süresini ayarla
     */
    public function setTimeout($timeout) {
        $this->timeout = (int)$timeout;
    }
    
    /**
     * API URL'ini ayarla
     */
    public function setApiUrl($url) {
        $this->api_url = rtrim($url, '/');
    }
    
    /**
     * Client ID'yi ayarla
     */
    public function setClientId($client_id) {
        $this->client_id = $client_id;
    }
    
    /**
     * API Key'i ayarla
     */
    public function setApiKey($api_key) {
        $this->api_key = $api_key;
    }
    
    /**
     * Rate limiting için bekleme
     */
    private function rateLimitWait() {
        // Ozon API rate limit: 1000 requests per minute
        // Güvenli olmak için 100ms bekle
        usleep(100000); // 100ms
    }
    
    /**
     * Batch işlemler için yardımcı fonksiyon
     */
    public function processBatch($items, $callback, $batch_size = 100) {
        $results = array();
        $batches = array_chunk($items, $batch_size);
        
        foreach ($batches as $batch) {
            try {
                $result = call_user_func($callback, $batch);
                $results[] = $result;
                
                // Rate limiting
                $this->rateLimitWait();
                
            } catch (Exception $e) {
                $results[] = array(
                    'error' => $e->getMessage(),
                    'batch' => $batch
                );
            }
        }
        
        return $results;
    }
    
    /**
     * Webhook doğrulama
     */
    public function validateWebhook($payload, $signature, $secret) {
        $calculated_signature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($signature, $calculated_signature);
    }
    
    /**
     * Webhook işleme
     */
    public function processWebhook($payload) {
        $data = json_decode($payload, true);
        
        if (!$data || !isset($data['message_type'])) {
            throw new Exception('Geçersiz webhook payload');
        }
        
        switch ($data['message_type']) {
            case 'TYPE_NEW_POSTING':
                return $this->processNewOrderWebhook($data);
            case 'TYPE_POSTING_STATUS_CHANGED':
                return $this->processOrderStatusWebhook($data);
            case 'TYPE_PRODUCT_PRICE_CHANGED':
                return $this->processProductPriceWebhook($data);
            case 'TYPE_PRODUCT_STOCKS_CHANGED':
                return $this->processProductStockWebhook($data);
            default:
                throw new Exception('Bilinmeyen webhook tipi: ' . $data['message_type']);
        }
    }
    
    /**
     * Yeni sipariş webhook işleme
     */
    private function processNewOrderWebhook($data) {
        if (!isset($data['posting_number'])) {
            throw new Exception('Posting number bulunamadı');
        }
        
        return array(
            'type' => 'new_order',
            'posting_number' => $data['posting_number'],
            'data' => $data
        );
    }
    
    /**
     * Sipariş durumu webhook işleme
     */
    private function processOrderStatusWebhook($data) {
        if (!isset($data['posting_number']) || !isset($data['status'])) {
            throw new Exception('Posting number veya status bulunamadı');
        }
        
        return array(
            'type' => 'order_status_changed',
            'posting_number' => $data['posting_number'],
            'status' => $data['status'],
            'data' => $data
        );
    }
    
    /**
     * Ürün fiyat webhook işleme
     */
    private function processProductPriceWebhook($data) {
        if (!isset($data['product_id'])) {
            throw new Exception('Product ID bulunamadı');
        }
        
        return array(
            'type' => 'product_price_changed',
            'product_id' => $data['product_id'],
            'data' => $data
        );
    }
    
    /**
     * Ürün stok webhook işleme
     */
    private function processProductStockWebhook($data) {
        if (!isset($data['product_id'])) {
            throw new Exception('Product ID bulunamadı');
        }
        
        return array(
            'type' => 'product_stock_changed',
            'product_id' => $data['product_id'],
            'data' => $data
        );
    }
}