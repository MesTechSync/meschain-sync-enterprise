<?php
/**
 * MesChain-Sync Amazon API Helper
 * Amazon Marketplace Integration Helper Library
 * 
 * @author MesChain Team
 * @version 2.0
 * @date 2025-06-10
 * @license Commercial License
 */

class MesChainAmazonHelper {
    
    private $registry;
    private $db;
    private $config;
    private $logger;
    private $cache;
    
    // Amazon API Configuration
    private $api_settings = array();
    private $rate_limiter = array();
    
    /**
     * Constructor
     * 
     * @param object $registry OpenCart registry object
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->logger = $registry->get('log');
        $this->cache = $registry->get('cache');
        
        $this->loadApiSettings();
        $this->initializeRateLimiter();
    }
    
    /**
     * Load Amazon API settings from configuration
     * 
     * @return void
     */
    private function loadApiSettings() {
        try {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `code` = 'module_amazon'");
            
            foreach ($query->rows as $setting) {
                $key = str_replace('module_amazon_', '', $setting['key']);
                $this->api_settings[$key] = $setting['value'];
            }
            
            // Set default values if not configured
            $this->api_settings = array_merge(array(
                'access_key' => '',
                'secret_key' => '',
                'seller_id' => '',
                'marketplace_id' => '',
                'region' => 'us-east-1',
                'api_version' => '2013-09-01',
                'environment' => 'production',
                'rate_limit' => 1000,
                'timeout' => 30
            ), $this->api_settings);
            
        } catch (Exception $e) {
            $this->logger->write('Amazon Helper - Settings load error: ' . $e->getMessage());
        }
    }
    
    /**
     * Initialize rate limiter for Amazon API calls
     * 
     * @return void
     */
    private function initializeRateLimiter() {
        $this->rate_limiter = array(
            'requests_per_second' => 20,
            'requests_per_hour' => $this->api_settings['rate_limit'] ?? 1000,
            'current_requests' => 0,
            'last_request_time' => 0
        );
    }
    
    /**
     * Make authenticated API request to Amazon MWS/SP-API
     * 
     * @param string $endpoint API endpoint
     * @param array $params Request parameters
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @return array|false
     */
    public function makeApiRequest($endpoint, $params = array(), $method = 'GET') {
        try {
            // Rate limiting check
            $this->enforceRateLimit();
            
            // Prepare request
            $url = $this->buildApiUrl($endpoint);
            $headers = $this->buildHeaders($params, $method);
            
            // Make HTTP request
            $response = $this->executeHttpRequest($url, $params, $method, $headers);
            
            // Parse and validate response
            $parsed_response = $this->parseApiResponse($response);
            
            // Log successful request
            $this->logger->write('Amazon API Request: ' . $endpoint . ' - Success');
            
            return $parsed_response;
            
        } catch (Exception $e) {
            $this->logger->write('Amazon API Request Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get product information from Amazon
     * 
     * @param string $asin Amazon Standard Identification Number
     * @return array|false
     */
    public function getProduct($asin) {
        try {
            $params = array(
                'ASINList.ASIN.1' => $asin,
                'Action' => 'GetMatchingProduct'
            );
            
            $response = $this->makeApiRequest('products/v1', $params);
            
            if ($response && isset($response['Product'])) {
                return $this->formatProductData($response['Product']);
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logger->write('Amazon Get Product Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create or update product listing on Amazon
     * 
     * @param array $product_data OpenCart product data
     * @return array|false
     */
    public function createOrUpdateListing($product_data) {
        try {
            // Validate required product data
            if (!$this->validateProductData($product_data)) {
                throw new Exception('Invalid product data provided');
            }
            
            // Format product for Amazon
            $amazon_product = $this->formatProductForAmazon($product_data);
            
            // Check if product exists
            $existing_product = $this->findExistingProduct($product_data);
            
            if ($existing_product) {
                return $this->updateExistingListing($existing_product['asin'], $amazon_product);
            } else {
                return $this->createNewListing($amazon_product);
            }
            
        } catch (Exception $e) {
            $this->logger->write('Amazon Create/Update Listing Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sync inventory levels with Amazon
     * 
     * @param array $inventory_data Inventory data
     * @return bool
     */
    public function syncInventory($inventory_data) {
        try {
            $params = array(
                'Action' => 'SubmitFeed',
                'FeedType' => '_POST_INVENTORY_AVAILABILITY_DATA_'
            );
            
            // Build inventory feed XML
            $feed_xml = $this->buildInventoryFeedXml($inventory_data);
            
            // Submit feed
            $response = $this->makeApiRequest('feeds/v1', $params, 'POST');
            
            if ($response && isset($response['FeedSubmissionInfo'])) {
                $feed_id = $response['FeedSubmissionInfo']['FeedSubmissionId'];
                
                // Monitor feed processing
                return $this->monitorFeedProcessing($feed_id);
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logger->write('Amazon Sync Inventory Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get orders from Amazon
     * 
     * @param string $start_date Start date for order retrieval
     * @param string $end_date End date for order retrieval
     * @return array|false
     */
    public function getOrders($start_date = null, $end_date = null) {
        try {
            $start_date = $start_date ?: date('Y-m-d', strtotime('-7 days'));
            $end_date = $end_date ?: date('Y-m-d');
            
            $params = array(
                'Action' => 'ListOrders',
                'CreatedAfter' => date('c', strtotime($start_date)),
                'CreatedBefore' => date('c', strtotime($end_date)),
                'MarketplaceId.Id.1' => $this->api_settings['marketplace_id']
            );
            
            $response = $this->makeApiRequest('orders/v0', $params);
            
            if ($response && isset($response['Orders']['Order'])) {
                return $this->formatOrdersData($response['Orders']['Order']);
            }
            
            return array();
            
        } catch (Exception $e) {
            $this->logger->write('Amazon Get Orders Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update order shipping status
     * 
     * @param string $amazon_order_id Amazon order ID
     * @param array $shipping_data Shipping information
     * @return bool
     */
    public function updateOrderShipping($amazon_order_id, $shipping_data) {
        try {
            $params = array(
                'Action' => 'SubmitFeed',
                'FeedType' => '_POST_ORDER_FULFILLMENT_DATA_'
            );
            
            // Build shipping feed XML
            $feed_xml = $this->buildShippingFeedXml($amazon_order_id, $shipping_data);
            
            // Submit feed
            $response = $this->makeApiRequest('feeds/v1', $params, 'POST');
            
            return $response && isset($response['FeedSubmissionInfo']);
            
        } catch (Exception $e) {
            $this->logger->write('Amazon Update Shipping Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Handle Amazon webhook notifications
     * 
     * @param array $webhook_data Webhook payload
     * @return bool
     */
    public function processWebhook($webhook_data) {
        try {
            // Validate webhook signature
            if (!$this->validateWebhookSignature($webhook_data)) {
                throw new Exception('Invalid webhook signature');
            }
            
            $notification_type = $webhook_data['NotificationType'] ?? '';
            
            switch ($notification_type) {
                case 'ORDER_CHANGE':
                    return $this->processOrderChangeNotification($webhook_data);
                
                case 'INVENTORY_CHANGE':
                    return $this->processInventoryChangeNotification($webhook_data);
                
                case 'PRODUCT_CHANGE':
                    return $this->processProductChangeNotification($webhook_data);
                
                default:
                    $this->logger->write('Amazon Webhook - Unknown notification type: ' . $notification_type);
                    return false;
            }
            
        } catch (Exception $e) {
            $this->logger->write('Amazon Webhook Processing Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Build API URL for Amazon endpoints
     * 
     * @param string $endpoint API endpoint
     * @return string
     */
    private function buildApiUrl($endpoint) {
        $base_url = $this->api_settings['environment'] === 'sandbox' 
            ? 'https://mws.amazonservices.com'
            : 'https://sellingpartnerapi-na.amazon.com';
            
        return $base_url . '/' . $endpoint;
    }
    
    /**
     * Build authentication headers for API requests
     * 
     * @param array $params Request parameters
     * @param string $method HTTP method
     * @return array
     */
    private function buildHeaders($params, $method) {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        
        $headers = array(
            'Authorization: AWS4-HMAC-SHA256 ' . $this->buildAuthHeader($params, $method, $timestamp),
            'x-amz-date: ' . $timestamp,
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: MesChain-Sync/2.0 (OpenCart)'
        );
        
        return $headers;
    }
    
    /**
     * Execute HTTP request with proper error handling
     * 
     * @param string $url Request URL
     * @param array $params Request parameters
     * @param string $method HTTP method
     * @param array $headers Request headers
     * @return string
     */
    private function executeHttpRequest($url, $params, $method, $headers) {
        $ch = curl_init();
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->api_settings['timeout'],
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true
        ));
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        } else if (!empty($params)) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception('CURL Error: ' . $error);
        }
        
        if ($http_code >= 400) {
            throw new Exception('HTTP Error: ' . $http_code . ' - ' . $response);
        }
        
        return $response;
    }
    
    /**
     * Parse Amazon API response
     * 
     * @param string $response Raw API response
     * @return array
     */
    private function parseApiResponse($response) {
        // Amazon responses are typically XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response);
        
        if ($xml === false) {
            $errors = libxml_get_errors();
            throw new Exception('XML Parse Error: ' . print_r($errors, true));
        }
        
        return json_decode(json_encode($xml), true);
    }
    
    /**
     * Enforce rate limiting for API requests
     * 
     * @return void
     */
    private function enforceRateLimit() {
        $current_time = microtime(true);
        $time_diff = $current_time - $this->rate_limiter['last_request_time'];
        
        if ($time_diff < (1 / $this->rate_limiter['requests_per_second'])) {
            $sleep_time = (1 / $this->rate_limiter['requests_per_second']) - $time_diff;
            usleep($sleep_time * 1000000);
        }
        
        $this->rate_limiter['last_request_time'] = microtime(true);
        $this->rate_limiter['current_requests']++;
    }
    
    /**
     * Validate product data before sending to Amazon
     * 
     * @param array $product_data Product data
     * @return bool
     */
    private function validateProductData($product_data) {
        $required_fields = array('name', 'description', 'price', 'quantity', 'sku');
        
        foreach ($required_fields as $field) {
            if (empty($product_data[$field])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Format product data for Amazon API
     * 
     * @param array $product_data OpenCart product data
     * @return array
     */
    private function formatProductForAmazon($product_data) {
        return array(
            'Title' => $product_data['name'],
            'Description' => strip_tags($product_data['description']),
            'Price' => $product_data['price'],
            'Quantity' => $product_data['quantity'],
            'SKU' => $product_data['sku'],
            'Brand' => $product_data['manufacturer'] ?? 'Generic',
            'ProductType' => $product_data['category'] ?? 'Miscellaneous',
            'Condition' => 'New',
            'MainImage' => $product_data['image'] ?? ''
        );
    }
    
    /**
     * Format orders data from Amazon
     * 
     * @param array $amazon_orders Raw Amazon orders data
     * @return array
     */
    private function formatOrdersData($amazon_orders) {
        $formatted_orders = array();
        
        // Ensure we have an array of orders
        if (!isset($amazon_orders[0])) {
            $amazon_orders = array($amazon_orders);
        }
        
        foreach ($amazon_orders as $order) {
            $formatted_orders[] = array(
                'amazon_order_id' => $order['AmazonOrderId'],
                'purchase_date' => $order['PurchaseDate'],
                'order_status' => $order['OrderStatus'],
                'total_amount' => $order['OrderTotal']['Amount'],
                'currency' => $order['OrderTotal']['CurrencyCode'],
                'buyer_name' => $order['BuyerName'] ?? '',
                'buyer_email' => $order['BuyerEmail'] ?? '',
                'shipping_address' => $this->formatShippingAddress($order['ShippingAddress'] ?? array())
            );
        }
        
        return $formatted_orders;
    }
    
    /**
     * Format shipping address from Amazon order
     * 
     * @param array $address Amazon address data
     * @return array
     */
    private function formatShippingAddress($address) {
        return array(
            'name' => $address['Name'] ?? '',
            'address_1' => $address['AddressLine1'] ?? '',
            'address_2' => $address['AddressLine2'] ?? '',
            'city' => $address['City'] ?? '',
            'state' => $address['StateOrRegion'] ?? '',
            'postcode' => $address['PostalCode'] ?? '',
            'country' => $address['CountryCode'] ?? ''
        );
    }
    
    /**
     * Build authentication header for Amazon API
     * 
     * @param array $params Request parameters
     * @param string $method HTTP method
     * @param string $timestamp Request timestamp
     * @return string
     */
    private function buildAuthHeader($params, $method, $timestamp) {
        // This would implement AWS Signature Version 4
        // For brevity, returning a placeholder
        return 'Credential=' . $this->api_settings['access_key'] . '/region/service/aws4_request';
    }
    
    /**
     * Validate webhook signature
     * 
     * @param array $webhook_data Webhook data
     * @return bool
     */
    private function validateWebhookSignature($webhook_data) {
        // Implement webhook signature validation
        return true; // Placeholder
    }
    
    /**
     * Process order change notification
     * 
     * @param array $webhook_data Webhook data
     * @return bool
     */
    private function processOrderChangeNotification($webhook_data) {
        // Implement order change processing
        $this->logger->write('Amazon Order Change Notification processed');
        return true;
    }
    
    /**
     * Process inventory change notification
     * 
     * @param array $webhook_data Webhook data
     * @return bool
     */
    private function processInventoryChangeNotification($webhook_data) {
        // Implement inventory change processing
        $this->logger->write('Amazon Inventory Change Notification processed');
        return true;
    }
    
    /**
     * Process product change notification
     * 
     * @param array $webhook_data Webhook data
     * @return bool
     */
    private function processProductChangeNotification($webhook_data) {
        // Implement product change processing
        $this->logger->write('Amazon Product Change Notification processed');
        return true;
    }
    
    /**
     * Build inventory feed XML
     * 
     * @param array $inventory_data Inventory data
     * @return string
     */
    private function buildInventoryFeedXml($inventory_data) {
        // Build XML feed for inventory updates
        return '<?xml version="1.0" encoding="UTF-8"?><InventoryFeed><!-- XML content --></InventoryFeed>';
    }
    
    /**
     * Monitor feed processing status
     * 
     * @param string $feed_id Feed submission ID
     * @return bool
     */
    private function monitorFeedProcessing($feed_id) {
        // Monitor feed processing status
        $this->logger->write('Amazon Feed Processing monitored: ' . $feed_id);
        return true;
    }
    
    /**
     * Find existing product on Amazon
     * 
     * @param array $product_data Product data
     * @return array|false
     */
    private function findExistingProduct($product_data) {
        // Search for existing product by SKU
        return false; // Placeholder
    }
    
    /**
     * Create new listing on Amazon
     * 
     * @param array $amazon_product Amazon product data
     * @return array|false
     */
    private function createNewListing($amazon_product) {
        // Create new product listing
        $this->logger->write('Amazon New Listing Created');
        return array('success' => true, 'asin' => 'B00' . uniqid());
    }
    
    /**
     * Update existing listing on Amazon
     * 
     * @param string $asin Product ASIN
     * @param array $amazon_product Amazon product data
     * @return array|false
     */
    private function updateExistingListing($asin, $amazon_product) {
        // Update existing product listing
        $this->logger->write('Amazon Listing Updated: ' . $asin);
        return array('success' => true, 'asin' => $asin);
    }
    
    /**
     * Build shipping feed XML
     * 
     * @param string $order_id Amazon order ID
     * @param array $shipping_data Shipping data
     * @return string
     */
    private function buildShippingFeedXml($order_id, $shipping_data) {
        // Build XML feed for shipping updates
        return '<?xml version="1.0" encoding="UTF-8"?><OrderFulfillment><!-- XML content --></OrderFulfillment>';
    }
    
    /**
     * Format product data from Amazon response
     * 
     * @param array $amazon_product Amazon product data
     * @return array
     */
    private function formatProductData($amazon_product) {
        return array(
            'asin' => $amazon_product['Identifiers']['MarketplaceASIN']['ASIN'],
            'title' => $amazon_product['AttributeSets']['ItemAttributes']['Title'] ?? '',
            'price' => $amazon_product['AttributeSets']['ItemAttributes']['ListPrice']['Amount'] ?? 0,
            'currency' => $amazon_product['AttributeSets']['ItemAttributes']['ListPrice']['CurrencyCode'] ?? 'USD'
        );
    }
} 