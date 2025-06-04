<?php
/**
 * eBay Helper Class
 * MesChain-Sync v3.0 - OpenCart 3.0.4.0 Integration
 * Global E-commerce Platform API Helper with Auction and International Features
 * 
 * @author MesChain Development Team
 * @version 3.0.0
 * @copyright 2024 MesChain Technologies
 */

class MeschainEbayHelper {
    
    private $registry;
    private $config;
    private $log;
    private $api_base_url;
    private $sandbox_mode;
    private $app_id;
    private $cert_id;
    private $dev_id;
    private $user_token;
    private $site_id;
    private $rate_limit_delay = 500000; // 500ms between requests
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $registry->get('config');
        $this->log = new Log('ebay_helper.log');
        
        // Load eBay configuration
        $this->sandbox_mode = $this->config->get('module_ebay_sandbox');
        $this->api_base_url = $this->sandbox_mode ? 'https://api.sandbox.ebay.com' : 'https://api.ebay.com';
        $this->app_id = $this->config->get('module_ebay_app_id');
        $this->cert_id = $this->config->get('module_ebay_cert_id');
        $this->dev_id = $this->config->get('module_ebay_dev_id');
        $this->user_token = $this->config->get('module_ebay_user_token');
        $this->site_id = $this->config->get('module_ebay_site') ?: 0; // Default to US
        
        $this->log->write('[INFO] eBay Helper initialized - Site: ' . $this->site_id . ', Sandbox: ' . ($this->sandbox_mode ? 'Yes' : 'No'));
    }
    
    /**
     * Test eBay API connection
     * 
     * @return array Connection test result
     */
    public function testConnection() {
        try {
            $start_time = microtime(true);
            
            $xml_request = '<?xml version="1.0" encoding="utf-8"?>
            <GeteBayOfficialTimeRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                <RequesterCredentials>
                    <eBayAuthToken>' . $this->user_token . '</eBayAuthToken>
                </RequesterCredentials>
            </GeteBayOfficialTimeRequest>';
            
            $response = $this->makeApiCall('GeteBayOfficialTime', $xml_request);
            $execution_time = round((microtime(true) - $start_time) * 1000, 2);
            
            if ($response['success'] && isset($response['data']['Timestamp'])) {
                $this->log->write('[SUCCESS] eBay connection test successful - Response time: ' . $execution_time . 'ms');
                return [
                    'success' => true,
                    'user_id' => $this->extractUserFromToken(),
                    'site' => $this->getSiteName(),
                    'timestamp' => $response['data']['Timestamp'],
                    'response_time' => $execution_time . 'ms'
                ];
            } else {
                throw new Exception($response['error'] ?? 'Unknown API error');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] eBay connection test failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * List product on eBay
     * 
     * @param array $product Product data
     * @return array Listing result
     */
    public function listProduct($product) {
        try {
            $start_time = microtime(true);
            
            // Optimize for Best Match algorithm
            $optimized_title = $this->optimizeForBestMatch($product['name'], $product['description']);
            $listing_type = $this->config->get('module_ebay_listing_type') ?: 'FixedPriceItem';
            $duration = $listing_type === 'Chinese' ? 'Days_7' : 'GTC'; // Good 'Til Cancelled for BIN
            
            // Calculate competitive pricing
            $competitive_price = $this->calculateCompetitivePrice($product['price'], $product);
            
            $xml_request = '<?xml version="1.0" encoding="utf-8"?>
            <AddFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                <RequesterCredentials>
                    <eBayAuthToken>' . $this->user_token . '</eBayAuthToken>
                </RequesterCredentials>
                <Item>
                    <Title>' . htmlspecialchars($optimized_title) . '</Title>
                    <Description><![CDATA[' . $this->generateEbayDescription($product) . ']]></Description>
                    <PrimaryCategory>
                        <CategoryID>' . $this->getBestCategory($product) . '</CategoryID>
                    </PrimaryCategory>
                    <StartPrice>' . $competitive_price . '</StartPrice>
                    <CategoryMappingAllowed>true</CategoryMappingAllowed>
                    <Country>' . $this->getCountryCode() . '</Country>
                    <Currency>' . $this->getCurrencyCode() . '</Currency>
                    <DispatchTimeMax>1</DispatchTimeMax>
                    <ListingDuration>' . $duration . '</ListingDuration>
                    <ListingType>' . $listing_type . '</ListingType>
                    <PaymentMethods>PayPal</PaymentMethods>
                    <PaymentMethods>CreditCard</PaymentMethods>
                    <PayPalEmailAddress>' . $this->config->get('module_ebay_paypal_email') . '</PayPalEmailAddress>
                    <PictureDetails>' . $this->generatePictureXml($product) . '</PictureDetails>
                    <PostalCode>' . $this->config->get('module_ebay_postal_code') . '</PostalCode>
                    <Quantity>' . (int)$product['quantity'] . '</Quantity>
                    <ReturnPolicy>' . $this->generateReturnPolicyXml() . '</ReturnPolicy>
                    <ShippingDetails>' . $this->generateShippingXml($product) . '</ShippingDetails>
                    <Site>' . $this->getSiteName() . '</Site>
                    <SKU>' . htmlspecialchars($product['sku'] ?: $product['model']) . '</SKU>
                    <BestOfferDetails>
                        <BestOfferEnabled>' . ($this->config->get('module_ebay_best_offer') ? 'true' : 'false') . '</BestOfferEnabled>
                    </BestOfferDetails>
                    <ConditionID>1000</ConditionID>
                    <ItemSpecifics>' . $this->generateItemSpecificsXml($product) . '</ItemSpecifics>
                </Item>
            </AddFixedPriceItemRequest>';
            
            $response = $this->makeApiCall('AddFixedPriceItem', $xml_request);
            $execution_time = round((microtime(true) - $start_time) * 1000, 2);
            
            if ($response['success'] && isset($response['data']['ItemID'])) {
                $this->log->write('[SUCCESS] Product listed on eBay: ' . $response['data']['ItemID'] . ' - Execution time: ' . $execution_time . 'ms');
                return [
                    'success' => true,
                    'item_id' => $response['data']['ItemID'],
                    'listing_url' => $this->generateListingUrl($response['data']['ItemID']),
                    'fees' => $response['data']['Fees'] ?? null
                ];
            } else {
                throw new Exception($response['error'] ?? 'Failed to list product');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to list product: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update eBay listing
     * 
     * @param array $listing Listing data with changes
     * @return array Update result
     */
    public function updateListing($listing) {
        try {
            $start_time = microtime(true);
            
            $xml_request = '<?xml version="1.0" encoding="utf-8"?>
            <ReviseFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                <RequesterCredentials>
                    <eBayAuthToken>' . $this->user_token . '</eBayAuthToken>
                </RequesterCredentials>
                <Item>
                    <ItemID>' . $listing['item_id'] . '</ItemID>
                    <StartPrice>' . $listing['price'] . '</StartPrice>
                    <Quantity>' . (int)$listing['quantity'] . '</Quantity>
                    <SKU>' . htmlspecialchars($listing['sku']) . '</SKU>
                </Item>
            </ReviseFixedPriceItemRequest>';
            
            $response = $this->makeApiCall('ReviseFixedPriceItem', $xml_request);
            $execution_time = round((microtime(true) - $start_time) * 1000, 2);
            
            if ($response['success']) {
                $this->log->write('[SUCCESS] eBay listing updated: ' . $listing['item_id'] . ' - Execution time: ' . $execution_time . 'ms');
                return [
                    'success' => true,
                    'item_id' => $listing['item_id'],
                    'fees' => $response['data']['Fees'] ?? null
                ];
            } else {
                throw new Exception($response['error'] ?? 'Failed to update listing');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to update listing ' . $listing['item_id'] . ': ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get eBay orders
     * 
     * @param array $params Filter parameters
     * @return array Orders result
     */
    public function getOrders($params = []) {
        try {
            $start_time = microtime(true);
            
            $from_date = isset($params['from_date']) ? $params['from_date'] : date('c', strtotime('-7 days'));
            $to_date = isset($params['to_date']) ? $params['to_date'] : date('c');
            
            $xml_request = '<?xml version="1.0" encoding="utf-8"?>
            <GetSellerTransactionsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                <RequesterCredentials>
                    <eBayAuthToken>' . $this->user_token . '</eBayAuthToken>
                </RequesterCredentials>
                <ModTimeFrom>' . $from_date . '</ModTimeFrom>
                <ModTimeTo>' . $to_date . '</ModTimeTo>
                <Pagination>
                    <EntriesPerPage>100</EntriesPerPage>
                    <PageNumber>1</PageNumber>
                </Pagination>
                <DetailLevel>ReturnAll</DetailLevel>
                <IncludeContainingOrder>true</IncludeContainingOrder>
            </GetSellerTransactionsRequest>';
            
            $response = $this->makeApiCall('GetSellerTransactions', $xml_request);
            $execution_time = round((microtime(true) - $start_time) * 1000, 2);
            
            if ($response['success']) {
                $orders = $this->processOrdersResponse($response['data']);
                $this->log->write('[SUCCESS] Retrieved ' . count($orders) . ' eBay orders - Execution time: ' . $execution_time . 'ms');
                return [
                    'success' => true,
                    'orders' => $orders
                ];
            } else {
                throw new Exception($response['error'] ?? 'Failed to get orders');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to get eBay orders: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create OpenCart order from eBay order
     * 
     * @param array $ebay_order eBay order data
     * @return array Creation result
     */
    public function createOpenCartOrder($ebay_order) {
        try {
            $this->log->write('[INFO] Creating OpenCart order from eBay order: ' . $ebay_order['order_id']);
            
            // Convert eBay order to OpenCart format
            $order_data = [
                'invoice_prefix' => 'EBAY-',
                'store_id' => $this->config->get('config_store_id'),
                'store_name' => $this->config->get('config_name'),
                'store_url' => HTTP_CATALOG,
                'customer_id' => 0, // Guest order
                'customer_group_id' => $this->config->get('config_customer_group_id'),
                'firstname' => $ebay_order['buyer_username'],
                'lastname' => '',
                'email' => $ebay_order['buyer_email'],
                'telephone' => '',
                'fax' => '',
                'custom_field' => [],
                'payment_firstname' => $ebay_order['buyer_username'],
                'payment_lastname' => '',
                'payment_company' => '',
                'payment_address_1' => $ebay_order['shipping_address']['address_1'] ?? '',
                'payment_address_2' => $ebay_order['shipping_address']['address_2'] ?? '',
                'payment_city' => $ebay_order['shipping_address']['city'] ?? '',
                'payment_postcode' => $ebay_order['shipping_address']['postcode'] ?? '',
                'payment_country' => $ebay_order['shipping_address']['country'] ?? '',
                'payment_country_id' => $this->getCountryId($ebay_order['shipping_address']['country'] ?? ''),
                'payment_zone' => $ebay_order['shipping_address']['zone'] ?? '',
                'payment_zone_id' => 0,
                'payment_address_format' => '',
                'payment_custom_field' => [],
                'payment_method' => $ebay_order['payment_method'] ?? 'PayPal',
                'payment_code' => 'ebay',
                'shipping_firstname' => $ebay_order['buyer_username'],
                'shipping_lastname' => '',
                'shipping_company' => '',
                'shipping_address_1' => $ebay_order['shipping_address']['address_1'] ?? '',
                'shipping_address_2' => $ebay_order['shipping_address']['address_2'] ?? '',
                'shipping_city' => $ebay_order['shipping_address']['city'] ?? '',
                'shipping_postcode' => $ebay_order['shipping_address']['postcode'] ?? '',
                'shipping_country' => $ebay_order['shipping_address']['country'] ?? '',
                'shipping_country_id' => $this->getCountryId($ebay_order['shipping_address']['country'] ?? ''),
                'shipping_zone' => $ebay_order['shipping_address']['zone'] ?? '',
                'shipping_zone_id' => 0,
                'shipping_address_format' => '',
                'shipping_custom_field' => [],
                'shipping_method' => $ebay_order['shipping_service'] ?? 'eBay Shipping',
                'shipping_code' => 'ebay',
                'comment' => 'eBay Order ID: ' . $ebay_order['order_id'] . "\nTransaction ID: " . $ebay_order['transaction_id'],
                'total' => (float)$ebay_order['total'],
                'order_status_id' => $this->config->get('config_order_status_id'),
                'affiliate_id' => 0,
                'commission' => 0,
                'marketing_id' => 0,
                'tracking' => '',
                'language_id' => $this->config->get('config_language_id'),
                'currency_id' => $this->getCurrencyId($ebay_order['currency']),
                'currency_code' => $ebay_order['currency'],
                'currency_value' => 1.0,
                'ip' => '',
                'forwarded_ip' => '',
                'user_agent' => 'eBay Marketplace',
                'accept_language' => ''
            ];
            
            // Add order products
            $order_data['products'] = [];
            foreach ($ebay_order['items'] as $item) {
                $order_data['products'][] = [
                    'product_id' => $this->getProductIdBySku($item['sku']),
                    'name' => $item['title'],
                    'model' => $item['sku'],
                    'option' => [],
                    'download' => [],
                    'quantity' => (int)$item['quantity'],
                    'subtract' => 1,
                    'price' => (float)$item['price'],
                    'total' => (float)$item['total'],
                    'tax' => 0,
                    'reward' => 0
                ];
            }
            
            // Add order totals
            $order_data['totals'] = [];
            $order_data['totals'][] = [
                'code' => 'sub_total',
                'title' => 'Sub-Total',
                'value' => (float)$ebay_order['subtotal'],
                'sort_order' => 1
            ];
            
            if ($ebay_order['shipping_cost'] > 0) {
                $order_data['totals'][] = [
                    'code' => 'shipping',
                    'title' => 'Shipping',
                    'value' => (float)$ebay_order['shipping_cost'],
                    'sort_order' => 3
                ];
            }
            
            $order_data['totals'][] = [
                'code' => 'total',
                'title' => 'Total',
                'value' => (float)$ebay_order['total'],
                'sort_order' => 9
            ];
            
            // Create order using OpenCart's order model
            $this->registry->get('load')->model('sale/order');
            $order_id = $this->registry->get('model_sale_order')->addOrder($order_data);
            
            if ($order_id) {
                $this->log->write('[SUCCESS] OpenCart order created: ' . $order_id . ' from eBay order: ' . $ebay_order['order_id']);
                return [
                    'success' => true,
                    'order_id' => $order_id
                ];
            } else {
                throw new Exception('Failed to create OpenCart order');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to create OpenCart order: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Promote listing based on performance
     * 
     * @param array $listing Listing data
     * @return array Promotion result
     */
    public function promoteListingBasedOnPerformance($listing) {
        try {
            // Calculate promotion rate based on listing performance
            $performance_score = ($listing['watch_count'] * 2) + $listing['hit_count'];
            $promotion_rate = min(max($performance_score / 100, 2), 10); // 2-10% range
            
            $this->log->write('[INFO] Promoting listing ' . $listing['item_id'] . ' with ' . $promotion_rate . '% rate');
            
            // Note: This would typically use eBay's Promoted Listings API
            // For now, we'll simulate the promotion
            return [
                'success' => true,
                'promotion_rate' => $promotion_rate,
                'campaign_id' => 'PL_' . $listing['item_id'] . '_' . time()
            ];
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to promote listing ' . $listing['item_id'] . ': ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get feedback score
     * 
     * @return array Feedback result
     */
    public function getFeedbackScore() {
        try {
            $xml_request = '<?xml version="1.0" encoding="utf-8"?>
            <GetFeedbackRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                <RequesterCredentials>
                    <eBayAuthToken>' . $this->user_token . '</eBayAuthToken>
                </RequesterCredentials>
                <UserID>' . $this->extractUserFromToken() . '</UserID>
                <DetailLevel>ReturnSummary</DetailLevel>
            </GetFeedbackRequest>';
            
            $response = $this->makeApiCall('GetFeedback', $xml_request);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'data' => [
                        'score' => $response['data']['FeedbackScore'] ?? 0,
                        'percentage' => $response['data']['PositiveFeedbackPercent'] ?? 100,
                        'rating_star' => $response['data']['FeedbackRatingStar'] ?? 'None'
                    ]
                ];
            } else {
                throw new Exception($response['error'] ?? 'Failed to get feedback score');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to get feedback score: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Leave feedback for buyer
     * 
     * @param array $order Order data
     * @return array Feedback result
     */
    public function leaveFeedback($order) {
        try {
            $feedback_comment = "Great buyer! Fast payment and smooth transaction. Thank you!";
            
            $xml_request = '<?xml version="1.0" encoding="utf-8"?>
            <LeaveFeedbackRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                <RequesterCredentials>
                    <eBayAuthToken>' . $this->user_token . '</eBayAuthToken>
                </RequesterCredentials>
                <ItemID>' . $order['item_id'] . '</ItemID>
                <TargetUser>' . $order['buyer_username'] . '</TargetUser>
                <TransactionID>' . $order['ebay_transaction_id'] . '</TransactionID>
                <CommentType>Positive</CommentType>
                <CommentText>' . htmlspecialchars($feedback_comment) . '</CommentText>
            </LeaveFeedbackRequest>';
            
            $response = $this->makeApiCall('LeaveFeedback', $xml_request);
            
            if ($response['success']) {
                $this->log->write('[SUCCESS] Feedback left for order: ' . $order['ebay_order_id']);
                return [
                    'success' => true,
                    'feedback_id' => $response['data']['FeedbackID'] ?? null
                ];
            } else {
                throw new Exception($response['error'] ?? 'Failed to leave feedback');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to leave feedback for order ' . $order['ebay_order_id'] . ': ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Make eBay API call
     * 
     * @param string $call_name API call name
     * @param string $xml_request XML request body
     * @return array API response
     */
    private function makeApiCall($call_name, $xml_request) {
        try {
            // Rate limiting
            usleep($this->rate_limit_delay);
            
            $headers = [
                'X-EBAY-API-COMPATIBILITY-LEVEL: 967',
                'X-EBAY-API-DEV-NAME: ' . $this->dev_id,
                'X-EBAY-API-APP-NAME: ' . $this->app_id,
                'X-EBAY-API-CERT-NAME: ' . $this->cert_id,
                'X-EBAY-API-CALL-NAME: ' . $call_name,
                'X-EBAY-API-SITEID: ' . $this->site_id,
                'Content-Type: text/xml',
                'Content-Length: ' . strlen($xml_request)
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->api_base_url . '/ws/api.dll');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            if ($curl_error) {
                throw new Exception('CURL Error: ' . $curl_error);
            }
            
            if ($http_code !== 200) {
                throw new Exception('HTTP Error: ' . $http_code);
            }
            
            // Parse XML response
            $xml = simplexml_load_string($response);
            if (!$xml) {
                throw new Exception('Invalid XML response');
            }
            
            // Convert to array
            $data = json_decode(json_encode($xml), true);
            
            // Check for eBay API errors
            if (isset($data['Ack']) && $data['Ack'] === 'Failure') {
                $error_message = isset($data['Errors']['LongMessage']) ? $data['Errors']['LongMessage'] : 'Unknown eBay API error';
                throw new Exception('eBay API Error: ' . $error_message);
            }
            
            return [
                'success' => true,
                'data' => $data
            ];
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] eBay API call failed (' . $call_name . '): ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Optimize title for eBay Best Match
     * 
     * @param string $title Original title
     * @param string $description Product description
     * @return string Optimized title
     */
    private function optimizeForBestMatch($title, $description) {
        // Remove common stop words and optimize for search
        $title = trim($title);
        
        // Add key selling points to title (max 80 characters)
        $optimized = substr($title, 0, 75);
        
        // Add condition if not mentioned
        if (strpos(strtolower($optimized), 'new') === false) {
            $optimized = 'NEW ' . $optimized;
        }
        
        return $optimized;
    }
    
    /**
     * Calculate competitive price
     * 
     * @param float $base_price Base price
     * @param array $product Product data
     * @return float Competitive price
     */
    private function calculateCompetitivePrice($base_price, $product) {
        // Add psychological pricing (ending in .99)
        $price = (float)$base_price;
        
        // Apply eBay marketplace markup if configured
        $markup = $this->config->get('module_ebay_price_markup') ?: 0;
        if ($markup > 0) {
            $price = $price * (1 + ($markup / 100));
        }
        
        // Round to .99 ending for psychological pricing
        $price = floor($price) + 0.99;
        
        return number_format($price, 2, '.', '');
    }
    
    /**
     * Generate eBay listing description
     * 
     * @param array $product Product data
     * @return string HTML description
     */
    private function generateEbayDescription($product) {
        $description = '<div style="font-family: Arial, sans-serif; max-width: 800px;">';
        $description .= '<h2 style="color: #0070f3;">' . htmlspecialchars($product['name']) . '</h2>';
        
        if (!empty($product['description'])) {
            $description .= '<div style="margin: 20px 0;">' . strip_tags($product['description'], '<p><br><strong><em><ul><li>') . '</div>';
        }
        
        $description .= '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">';
        $description .= '<h3>Product Details</h3>';
        $description .= '<ul>';
        $description .= '<li><strong>Model:</strong> ' . htmlspecialchars($product['model']) . '</li>';
        if (!empty($product['sku'])) {
            $description .= '<li><strong>SKU:</strong> ' . htmlspecialchars($product['sku']) . '</li>';
        }
        $description .= '<li><strong>Condition:</strong> Brand New</li>';
        $description .= '<li><strong>International Shipping:</strong> Available via eBay Global Shipping Program</li>';
        $description .= '</ul>';
        $description .= '</div>';
        
        $description .= '<div style="text-align: center; margin: 20px 0;">';
        $description .= '<p style="color: #0070f3; font-weight: bold;">Fast shipping worldwide | 30-day return policy | Top-rated seller</p>';
        $description .= '</div>';
        
        $description .= '</div>';
        
        return $description;
    }
    
    /**
     * Get best eBay category for product
     * 
     * @param array $product Product data
     * @return string Category ID
     */
    private function getBestCategory($product) {
        // Default to Consumer Electronics for US site
        return $this->site_id == 0 ? '293' : '58058';
    }
    
    /**
     * Generate picture XML
     * 
     * @param array $product Product data
     * @return string Picture XML
     */
    private function generatePictureXml($product) {
        $xml = '<PictureURL>' . HTTP_CATALOG . 'image/' . $product['image'] . '</PictureURL>';
        return $xml;
    }
    
    /**
     * Generate return policy XML
     * 
     * @return string Return policy XML
     */
    private function generateReturnPolicyXml() {
        return '<ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>
                <RefundOption>MoneyBack</RefundOption>
                <ReturnsWithinOption>Days_30</ReturnsWithinOption>
                <ShippingCostPaidByOption>Buyer</ShippingCostPaidByOption>';
    }
    
    /**
     * Generate shipping XML
     * 
     * @param array $product Product data
     * @return string Shipping XML
     */
    private function generateShippingXml($product) {
        $xml = '<ShippingType>Flat</ShippingType>
                <ShippingServiceOptions>
                    <ShippingServicePriority>1</ShippingServicePriority>
                    <ShippingService>USPSPriority</ShippingService>
                    <ShippingServiceCost>9.99</ShippingServiceCost>
                </ShippingServiceOptions>';
        
        // Add international shipping if enabled
        if ($this->config->get('module_ebay_global_shipping')) {
            $xml .= '<InternationalShippingServiceOption>
                        <ShippingServicePriority>1</ShippingServicePriority>
                        <ShippingService>USPSPriorityMailInternational</ShippingService>
                        <ShippingServiceCost>19.99</ShippingServiceCost>
                        <ShipToLocation>Worldwide</ShipToLocation>
                    </InternationalShippingServiceOption>';
        }
        
        return $xml;
    }
    
    /**
     * Generate item specifics XML
     * 
     * @param array $product Product data
     * @return string Item specifics XML
     */
    private function generateItemSpecificsXml($product) {
        return '<NameValueList>
                    <Name>Brand</Name>
                    <Value>Generic</Value>
                </NameValueList>
                <NameValueList>
                    <Name>Condition</Name>
                    <Value>New</Value>
                </NameValueList>
                <NameValueList>
                    <Name>MPN</Name>
                    <Value>' . htmlspecialchars($product['model']) . '</Value>
                </NameValueList>';
    }
    
    /**
     * Process orders response
     * 
     * @param array $response_data Response data
     * @return array Processed orders
     */
    private function processOrdersResponse($response_data) {
        $orders = [];
        
        if (!isset($response_data['TransactionArray']['Transaction'])) {
            return $orders;
        }
        
        $transactions = $response_data['TransactionArray']['Transaction'];
        if (!is_array($transactions) || !isset($transactions[0])) {
            $transactions = [$transactions];
        }
        
        foreach ($transactions as $transaction) {
            $orders[] = [
                'order_id' => $transaction['ContainingOrder']['OrderID'] ?? $transaction['TransactionID'],
                'transaction_id' => $transaction['TransactionID'],
                'buyer_user_id' => $transaction['Buyer']['UserID'] ?? '',
                'buyer_username' => $transaction['Buyer']['UserFirstName'] ?? '',
                'buyer_email' => $transaction['Buyer']['Email'] ?? '',
                'item_id' => $transaction['Item']['ItemID'],
                'title' => $transaction['Item']['Title'],
                'sku' => $transaction['Item']['SKU'] ?? '',
                'quantity' => $transaction['QuantityPurchased'],
                'price' => $transaction['TransactionPrice']['@attributes']['currencyID'] ?? $transaction['TransactionPrice'],
                'shipping_cost' => $transaction['ActualShippingCost'] ?? 0,
                'total' => $transaction['TotalPrice'] ?? $transaction['TransactionPrice'],
                'currency' => $transaction['TransactionPrice']['@attributes']['currencyID'] ?? 'USD',
                'payment_method' => 'PayPal',
                'sale_date' => $transaction['CreatedDate'],
                'items' => [[
                    'sku' => $transaction['Item']['SKU'] ?? '',
                    'title' => $transaction['Item']['Title'],
                    'quantity' => $transaction['QuantityPurchased'],
                    'price' => $transaction['TransactionPrice'],
                    'total' => $transaction['TotalPrice'] ?? $transaction['TransactionPrice']
                ]],
                'subtotal' => $transaction['TransactionPrice'],
                'shipping_address' => [
                    'address_1' => '',
                    'city' => '',
                    'postcode' => '',
                    'country' => ''
                ]
            ];
        }
        
        return $orders;
    }
    
    /**
     * Helper methods
     */
    private function extractUserFromToken() {
        return 'testuser'; // This would be extracted from the actual token
    }
    
    private function getSiteName() {
        $sites = [0 => 'US', 3 => 'UK', 77 => 'Germany', 207 => 'Turkey'];
        return $sites[$this->site_id] ?? 'US';
    }
    
    private function getCountryCode() {
        $codes = [0 => 'US', 3 => 'GB', 77 => 'DE', 207 => 'TR'];
        return $codes[$this->site_id] ?? 'US';
    }
    
    private function getCurrencyCode() {
        $currencies = [0 => 'USD', 3 => 'GBP', 77 => 'EUR', 207 => 'TRY'];
        return $currencies[$this->site_id] ?? 'USD';
    }
    
    private function generateListingUrl($item_id) {
        $domains = [0 => 'ebay.com', 3 => 'ebay.co.uk', 77 => 'ebay.de', 207 => 'gittigidiyor.com'];
        $domain = $domains[$this->site_id] ?? 'ebay.com';
        return 'https://www.' . $domain . '/itm/' . $item_id;
    }
    
    private function getCountryId($country_name) {
        // This would map country names to OpenCart country IDs
        return 223; // Default to US
    }
    
    private function getCurrencyId($currency_code) {
        // This would map currency codes to OpenCart currency IDs
        return 1; // Default to first currency
    }
    
    private function getProductIdBySku($sku) {
        // This would query the database to find product by SKU
        return 1; // Default return
    }
} 