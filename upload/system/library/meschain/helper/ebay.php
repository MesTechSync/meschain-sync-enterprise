<?php
/**
 * MesChain-Sync eBay API Helper
 * eBay Marketplace Integration Helper Library
 * 
 * @author MesChain Team
 * @version 2.0
 * @date 2025-06-10
 * @license Commercial License
 */

class MesChainEbayHelper {
    
    private $registry;
    private $db;
    private $config;
    private $logger;
    private $cache;
    
    // eBay API Configuration
    private $api_settings = array();
    private $rate_limiter = array();
    
    // eBay API Endpoints
    private $api_endpoints = array(
        'sandbox' => 'https://api.sandbox.ebay.com',
        'production' => 'https://api.ebay.com'
    );
    
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
     * Load eBay API settings from configuration
     * 
     * @return void
     */
    private function loadApiSettings() {
        try {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `code` = 'module_ebay'");
            
            foreach ($query->rows as $setting) {
                $key = str_replace('module_ebay_', '', $setting['key']);
                $this->api_settings[$key] = $setting['value'];
            }
            
            // Set default values if not configured
            $this->api_settings = array_merge(array(
                'app_id' => '',
                'dev_id' => '',
                'cert_id' => '',
                'user_token' => '',
                'environment' => 'sandbox',
                'site_id' => 0, // 0 = US, 3 = UK, 71 = France, 77 = Germany
                'version' => '1193',
                'timeout' => 30,
                'rate_limit' => 5000
            ), $this->api_settings);
            
        } catch (Exception $e) {
            $this->logger->write('eBay Helper - Settings load error: ' . $e->getMessage());
        }
    }
    
    /**
     * Initialize rate limiter for eBay API calls
     * 
     * @return void
     */
    private function initializeRateLimiter() {
        $this->rate_limiter = array(
            'requests_per_second' => 10,
            'requests_per_hour' => $this->api_settings['rate_limit'] ?? 5000,
            'current_requests' => 0,
            'last_request_time' => 0
        );
    }
    
    /**
     * Make authenticated API request to eBay Trading API
     * 
     * @param string $call_name eBay API call name
     * @param array $params Request parameters
     * @return array|false
     */
    public function makeApiRequest($call_name, $params = array()) {
        try {
            // Rate limiting check
            $this->enforceRateLimit();
            
            // Prepare request
            $xml_request = $this->buildXmlRequest($call_name, $params);
            $headers = $this->buildHeaders($call_name);
            
            // Make HTTP request
            $response = $this->executeHttpRequest($xml_request, $headers);
            
            // Parse and validate response
            $parsed_response = $this->parseApiResponse($response);
            
            // Log successful request
            $this->logger->write('eBay API Request: ' . $call_name . ' - Success');
            
            return $parsed_response;
            
        } catch (Exception $e) {
            $this->logger->write('eBay API Request Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get item information from eBay
     * 
     * @param string $item_id eBay item ID
     * @return array|false
     */
    public function getItem($item_id) {
        try {
            $params = array(
                'ItemID' => $item_id,
                'DetailLevel' => 'ReturnAll'
            );
            
            $response = $this->makeApiRequest('GetItem', $params);
            
            if ($response && isset($response['Item'])) {
                return $this->formatItemData($response['Item']);
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logger->write('eBay Get Item Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add or verify item on eBay
     * 
     * @param array $product_data OpenCart product data
     * @param string $listing_type FixedPriceItem or Auction
     * @return array|false
     */
    public function addOrVerifyItem($product_data, $listing_type = 'FixedPriceItem') {
        try {
            // Validate required product data
            if (!$this->validateProductData($product_data)) {
                throw new Exception('Invalid product data provided');
            }
            
            // Format product for eBay
            $ebay_item = $this->formatProductForEbay($product_data, $listing_type);
            
            // Check if item exists
            $existing_item = $this->findExistingItem($product_data);
            
            if ($existing_item) {
                return $this->reviseItem($existing_item['item_id'], $ebay_item);
            } else {
                return $this->addFixedPriceItem($ebay_item);
            }
            
        } catch (Exception $e) {
            $this->logger->write('eBay Add/Verify Item Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update item inventory on eBay
     * 
     * @param string $item_id eBay item ID
     * @param int $quantity New quantity
     * @return bool
     */
    public function updateInventory($item_id, $quantity) {
        try {
            $params = array(
                'Item' => array(
                    'ItemID' => $item_id,
                    'Quantity' => $quantity
                )
            );
            
            $response = $this->makeApiRequest('ReviseInventoryStatus', $params);
            
            return $response && isset($response['Ack']) && $response['Ack'] === 'Success';
            
        } catch (Exception $e) {
            $this->logger->write('eBay Update Inventory Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get orders from eBay
     * 
     * @param string $start_date Start date for order retrieval
     * @param string $end_date End date for order retrieval
     * @return array|false
     */
    public function getOrders($start_date = null, $end_date = null) {
        try {
            $start_date = $start_date ?: date('Y-m-d\TH:i:s.000\Z', strtotime('-7 days'));
            $end_date = $end_date ?: date('Y-m-d\TH:i:s.000\Z');
            
            $params = array(
                'CreateTimeFrom' => $start_date,
                'CreateTimeTo' => $end_date,
                'OrderRole' => 'Seller',
                'OrderStatus' => 'All',
                'Pagination' => array(
                    'EntriesPerPage' => 200,
                    'PageNumber' => 1
                )
            );
            
            $response = $this->makeApiRequest('GetOrders', $params);
            
            if ($response && isset($response['OrderArray']['Order'])) {
                return $this->formatOrdersData($response['OrderArray']['Order']);
            }
            
            return array();
            
        } catch (Exception $e) {
            $this->logger->write('eBay Get Orders Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update order shipping status
     * 
     * @param string $order_id eBay order ID
     * @param array $shipping_data Shipping information
     * @return bool
     */
    public function updateOrderShipping($order_id, $shipping_data) {
        try {
            $params = array(
                'OrderID' => $order_id,
                'Shipped' => true,
                'Shipment' => array(
                    'ShipmentTrackingNumber' => $shipping_data['tracking_number'] ?? '',
                    'ShippingCarrierUsed' => $shipping_data['carrier'] ?? 'Other'
                )
            );
            
            $response = $this->makeApiRequest('CompleteSale', $params);
            
            return $response && isset($response['Ack']) && $response['Ack'] === 'Success';
            
        } catch (Exception $e) {
            $this->logger->write('eBay Update Shipping Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get eBay categories
     * 
     * @param string $category_site_id Site ID for categories
     * @return array|false
     */
    public function getCategories($category_site_id = null) {
        try {
            $site_id = $category_site_id ?: $this->api_settings['site_id'];
            
            $params = array(
                'CategorySiteID' => $site_id,
                'LevelLimit' => 3,
                'ViewAllNodes' => true
            );
            
            $response = $this->makeApiRequest('GetCategories', $params);
            
            if ($response && isset($response['CategoryArray']['Category'])) {
                return $this->formatCategoriesData($response['CategoryArray']['Category']);
            }
            
            return array();
            
        } catch (Exception $e) {
            $this->logger->write('eBay Get Categories Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Build XML request for eBay API
     * 
     * @param string $call_name API call name
     * @param array $params Request parameters
     * @return string
     */
    private function buildXmlRequest($call_name, $params) {
        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<' . $call_name . 'Request xmlns="urn:ebay:apis:eBLBaseComponents">';
        $xml .= '<RequesterCredentials>';
        $xml .= '<eBayAuthToken>' . $this->api_settings['user_token'] . '</eBayAuthToken>';
        $xml .= '</RequesterCredentials>';
        
        // Add parameters
        $xml .= $this->arrayToXml($params);
        
        $xml .= '</' . $call_name . 'Request>';
        
        return $xml;
    }
    
    /**
     * Build headers for eBay API request
     * 
     * @param string $call_name API call name
     * @return array
     */
    private function buildHeaders($call_name) {
        return array(
            'X-EBAY-API-COMPATIBILITY-LEVEL: ' . $this->api_settings['version'],
            'X-EBAY-API-DEV-NAME: ' . $this->api_settings['dev_id'],
            'X-EBAY-API-APP-NAME: ' . $this->api_settings['app_id'],
            'X-EBAY-API-CERT-NAME: ' . $this->api_settings['cert_id'],
            'X-EBAY-API-CALL-NAME: ' . $call_name,
            'X-EBAY-API-SITEID: ' . $this->api_settings['site_id'],
            'Content-Type: text/xml; charset=utf-8'
        );
    }
    
    /**
     * Execute HTTP request with proper error handling
     * 
     * @param string $xml_request XML request body
     * @param array $headers Request headers
     * @return string
     */
    private function executeHttpRequest($xml_request, $headers) {
        $url = $this->api_endpoints[$this->api_settings['environment']] . '/ws/api.dll';
        
        $ch = curl_init();
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->api_settings['timeout'],
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $xml_request,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true
        ));
        
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
     * Parse eBay API response
     * 
     * @param string $response Raw API response
     * @return array
     */
    private function parseApiResponse($response) {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response);
        
        if ($xml === false) {
            $errors = libxml_get_errors();
            throw new Exception('XML Parse Error: ' . print_r($errors, true));
        }
        
        return json_decode(json_encode($xml), true);
    }
    
    /**
     * Convert array to XML string
     * 
     * @param array $array Array to convert
     * @param string $parent_key Parent key name
     * @return string
     */
    private function arrayToXml($array, $parent_key = '') {
        $xml = '';
        
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $xml .= '<' . $key . '>' . $this->arrayToXml($value, $key) . '</' . $key . '>';
            } else {
                $xml .= '<' . $key . '>' . htmlspecialchars($value) . '</' . $key . '>';
            }
        }
        
        return $xml;
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
     * Validate product data before sending to eBay
     * 
     * @param array $product_data Product data
     * @return bool
     */
    private function validateProductData($product_data) {
        $required_fields = array('name', 'description', 'price', 'quantity', 'sku', 'category_id');
        
        foreach ($required_fields as $field) {
            if (empty($product_data[$field])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Format product data for eBay API
     * 
     * @param array $product_data OpenCart product data
     * @param string $listing_type Listing type
     * @return array
     */
    private function formatProductForEbay($product_data, $listing_type) {
        return array(
            'Title' => substr($product_data['name'], 0, 80), // eBay title limit
            'Description' => $this->formatDescription($product_data['description']),
            'PrimaryCategory' => array(
                'CategoryID' => $this->mapToEbayCategory($product_data['category_id'])
            ),
            'StartPrice' => $product_data['price'],
            'Quantity' => $product_data['quantity'],
            'SKU' => $product_data['sku'],
            'Country' => 'US',
            'Currency' => 'USD',
            'DispatchTimeMax' => 3,
            'ListingDuration' => 'GTC', // Good Till Cancelled
            'ListingType' => $listing_type,
            'PaymentMethods' => array('PayPal', 'VisaMC'),
            'PictureDetails' => $this->formatPictureDetails($product_data),
            'ReturnPolicy' => $this->getDefaultReturnPolicy(),
            'ShippingDetails' => $this->getDefaultShippingDetails()
        );
    }
    
    /**
     * Format orders data from eBay
     * 
     * @param array $ebay_orders Raw eBay orders data
     * @return array
     */
    private function formatOrdersData($ebay_orders) {
        $formatted_orders = array();
        
        // Ensure we have an array of orders
        if (!isset($ebay_orders[0])) {
            $ebay_orders = array($ebay_orders);
        }
        
        foreach ($ebay_orders as $order) {
            $formatted_orders[] = array(
                'ebay_order_id' => $order['OrderID'],
                'created_date' => $order['CreatedTime'],
                'order_status' => $order['OrderStatus'],
                'total_amount' => $order['Total']['_'],
                'currency' => $order['Total']['@attributes']['currencyID'],
                'buyer_id' => $order['BuyerUserID'],
                'buyer_email' => $order['TransactionArray']['Transaction']['Buyer']['Email'] ?? '',
                'shipping_address' => $this->formatShippingAddress($order['ShippingAddress'] ?? array())
            );
        }
        
        return $formatted_orders;
    }
    
    /**
     * Format shipping address from eBay order
     * 
     * @param array $address eBay address data
     * @return array
     */
    private function formatShippingAddress($address) {
        return array(
            'name' => $address['Name'] ?? '',
            'street1' => $address['Street1'] ?? '',
            'street2' => $address['Street2'] ?? '',
            'city' => $address['CityName'] ?? '',
            'state' => $address['StateOrProvince'] ?? '',
            'postal_code' => $address['PostalCode'] ?? '',
            'country' => $address['CountryName'] ?? ''
        );
    }
    
    /**
     * Format categories data from eBay
     * 
     * @param array $categories eBay categories data
     * @return array
     */
    private function formatCategoriesData($categories) {
        $formatted_categories = array();
        
        if (!isset($categories[0])) {
            $categories = array($categories);
        }
        
        foreach ($categories as $category) {
            $formatted_categories[] = array(
                'category_id' => $category['CategoryID'],
                'category_name' => $category['CategoryName'],
                'category_level' => $category['CategoryLevel'],
                'parent_id' => $category['CategoryParentID'] ?? 0,
                'leaf_category' => isset($category['LeafCategory']) ? $category['LeafCategory'] === 'true' : false
            );
        }
        
        return $formatted_categories;
    }
    
    /**
     * Additional helper methods for eBay-specific functionality
     */
    
    private function formatDescription($description) {
        return strip_tags($description, '<p><br><strong><b><i><em><ul><ol><li>');
    }
    
    private function mapToEbayCategory($opencart_category_id) {
        // This would map OpenCart categories to eBay categories
        // For now, return a default category
        return '888'; // Other category
    }
    
    private function formatPictureDetails($product_data) {
        if (!empty($product_data['image'])) {
            return array(
                'PictureURL' => array($product_data['image'])
            );
        }
        return array();
    }
    
    private function getDefaultReturnPolicy() {
        return array(
            'ReturnsAcceptedOption' => 'ReturnsAccepted',
            'RefundOption' => 'MoneyBack',
            'ReturnsWithinOption' => 'Days_30'
        );
    }
    
    private function getDefaultShippingDetails() {
        return array(
            'ShippingType' => 'Flat',
            'ShippingServiceOptions' => array(
                'ShippingServicePriority' => 1,
                'ShippingService' => 'USPSMedia',
                'ShippingServiceCost' => '2.50'
            )
        );
    }
    
    private function formatItemData($item) {
        return array(
            'item_id' => $item['ItemID'],
            'title' => $item['Title'],
            'current_price' => $item['SellingStatus']['CurrentPrice']['_'] ?? 0,
            'currency' => $item['SellingStatus']['CurrentPrice']['@attributes']['currencyID'] ?? 'USD',
            'quantity' => $item['Quantity'] ?? 0,
            'sku' => $item['SKU'] ?? '',
            'listing_type' => $item['ListingType'] ?? '',
            'time_left' => $item['SellingStatus']['TimeLeft'] ?? ''
        );
    }
    
    private function findExistingItem($product_data) {
        // Search for existing item by SKU or other identifier
        return false; // Placeholder
    }
    
    private function reviseItem($item_id, $item_data) {
        $params = array('Item' => array_merge(array('ItemID' => $item_id), $item_data));
        return $this->makeApiRequest('ReviseFixedPriceItem', $params);
    }
    
    private function addFixedPriceItem($item_data) {
        $params = array('Item' => $item_data);
        return $this->makeApiRequest('AddFixedPriceItem', $params);
    }
}
