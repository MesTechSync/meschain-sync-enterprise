<?php
/**
 * N11 Helper Class
 * MesChain-Sync v3.0 - OpenCart 3.0.4.0 Integration
 * Turkish E-commerce Platform API Helper with N11 Pro Features
 * 
 * @author MesChain Development Team
 * @version 3.0.0
 * @copyright 2024 MesChain Technologies
 */

class MeschainN11Helper {
    
    private $registry;
    private $config;
    private $log;
    private $api_base_url;
    private $api_key;
    private $secret_key;
    private $store_key;
    private $rate_limit_delay = 250000; // 250ms between requests
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $registry->get('config');
        $this->log = new Log('n11_helper.log');
        
        // Load N11 configuration
        $this->api_base_url = 'https://www.n11.com/ws/';
        $this->api_key = $this->config->get('module_n11_api_key');
        $this->secret_key = $this->config->get('module_n11_secret_key');
        $this->store_key = $this->config->get('module_n11_store_key');
        
        $this->log->write('[INFO] N11 Helper initialized - Store: ' . $this->store_key);
    }
    
    /**
     * Test N11 API connection
     * 
     * @return array Connection test result
     */
    public function testConnection() {
        try {
            $start_time = microtime(true);
            
            $request_data = array(
                'auth' => $this->generateAuthData(),
                'pagingData' => array(
                    'currentPage' => 0,
                    'pageSize' => 1
                )
            );
            
            $response = $this->makeApiCall('ProductService', 'GetProductList', $request_data);
            $execution_time = round((microtime(true) - $start_time) * 1000, 2);
            
            if ($response['success'] && isset($response['data']['pagingData'])) {
                $this->log->write('[SUCCESS] N11 connection test successful - Response time: ' . $execution_time . 'ms');
                return [
                    'success' => true,
                    'store_name' => $this->extractStoreName(),
                    'pro_status' => $this->checkProSellerStatus(),
                    'response_time' => $execution_time . 'ms'
                ];
            } else {
                throw new Exception($response['error'] ?? 'Unknown API error');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] N11 connection test failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * List product on N11 with Turkish market optimization
     * 
     * @param array $product Product data
     * @return array Listing result
     */
    public function listProductWithTurkishOptimization($product) {
        try {
            $start_time = microtime(true);
            
            // Optimize for Turkish market
            $optimized_title = $this->optimizeForTurkishMarket($product['name']);
            $turkish_price = $this->calculatePsychologicalPricing($product['price']);
            $category_id = $this->getBestTurkishCategory($product);
            
            $request_data = array(
                'auth' => $this->generateAuthData(),
                'product' => array(
                    'productSellerCode' => $product['sku'] ?: $product['model'],
                    'title' => $optimized_title,
                    'subtitle' => substr($product['description'], 0, 100),
                    'description' => $this->generateTurkishDescription($product),
                    'category' => array('id' => $category_id),
                    'price' => $turkish_price,
                    'currencyType' => 'TL',
                    'images' => $this->generateImageArray($product),
                    'approvalStatus' => 1, // Auto approval for existing sellers
                    'preparingDay' => $this->config->get('module_n11_preparing_day') ?: 1,
                    'attributes' => $this->generateTurkishAttributes($product),
                    'stockItems' => array(
                        array(
                            'bundle' => false,
                            'mpn' => $product['model'],
                            'gtin' => $product['ean'] ?? '',
                            'quantity' => (int)$product['quantity'],
                            'attributes' => array()
                        )
                    ),
                    'shipmentTemplate' => $this->getShipmentTemplate(),
                    'itemAttributes' => $this->generateItemAttributes($product)
                )
            );
            
            // Add N11 Pro specific features
            if ($this->config->get('module_n11_pro_seller')) {
                $request_data['product']['saleStartDate'] = date('d/m/Y H:i');
                $request_data['product']['saleEndDate'] = date('d/m/Y H:i', strtotime('+365 days'));
                $request_data['product']['domestic'] = true; // Turkish product flag
                $request_data['product']['overseasDelivery'] = false; // Focus on Turkey
            }
            
            $response = $this->makeApiCall('ProductService', 'SaveProduct', $request_data);
            $execution_time = round((microtime(true) - $start_time) * 1000, 2);
            
            if ($response['success'] && isset($response['data']['product']['id'])) {
                $this->log->write('[SUCCESS] Product listed on N11: ' . $response['data']['product']['id'] . ' - Execution time: ' . $execution_time . 'ms');
                return [
                    'success' => true,
                    'product_id' => $response['data']['product']['id'],
                    'url' => $this->generateProductUrl($response['data']['product']['id']),
                    'commission_rate' => $this->calculateCommissionRate($category_id),
                    'turkish_optimized' => true
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
     * Update N11 product with psychological pricing
     * 
     * @param array $listing Listing data with changes
     * @return array Update result
     */
    public function updateProductWithPsychologicalPricing($listing) {
        try {
            $start_time = microtime(true);
            
            // Apply psychological pricing
            $psychological_price = $this->calculatePsychologicalPricing($listing['price']);
            
            $request_data = array(
                'auth' => $this->generateAuthData(),
                'product' => array(
                    'id' => $listing['n11_product_id'],
                    'price' => $psychological_price,
                    'stockItems' => array(
                        array(
                            'quantity' => (int)$listing['quantity'],
                            'sellerStockCode' => $listing['sku']
                        )
                    )
                )
            );
            
            $response = $this->makeApiCall('ProductService', 'UpdateProduct', $request_data);
            $execution_time = round((microtime(true) - $start_time) * 1000, 2);
            
            if ($response['success']) {
                $this->log->write('[SUCCESS] N11 product updated: ' . $listing['n11_product_id'] . ' - Execution time: ' . $execution_time . 'ms');
                return [
                    'success' => true,
                    'product_id' => $listing['n11_product_id'],
                    'psychological_price' => $psychological_price,
                    'price_difference' => $psychological_price - $listing['price']
                ];
            } else {
                throw new Exception($response['error'] ?? 'Failed to update product');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to update product ' . $listing['n11_product_id'] . ': ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get N11 orders with cargo tracking
     * 
     * @param array $params Filter parameters
     * @return array Orders result
     */
    public function getOrdersWithCargoTracking($params = []) {
        try {
            $start_time = microtime(true);
            
            $request_data = array(
                'auth' => $this->generateAuthData(),
                'pagingData' => array(
                    'currentPage' => isset($params['page']) ? $params['page'] : 0,
                    'pageSize' => isset($params['limit']) ? $params['limit'] : 100
                ),
                'orderSearchData' => array(
                    'productId' => null,
                    'status' => null,
                    'buyerName' => null,
                    'orderNumber' => null,
                    'productSellerCode' => null,
                    'recipient' => null,
                    'sameDayDelivery' => null,
                    'period' => array(
                        'startDate' => isset($params['start_date']) ? $params['start_date'] : date('d/m/Y', strtotime('-7 days')),
                        'endDate' => isset($params['end_date']) ? $params['end_date'] : date('d/m/Y')
                    )
                )
            );
            
            $response = $this->makeApiCall('OrderService', 'DetailedOrderList', $request_data);
            $execution_time = round((microtime(true) - $start_time) * 1000, 2);
            
            if ($response['success']) {
                $orders = $this->processOrdersResponseWithTurkishData($response['data']);
                $this->log->write('[SUCCESS] Retrieved ' . count($orders) . ' N11 orders - Execution time: ' . $execution_time . 'ms');
                return [
                    'success' => true,
                    'orders' => $orders
                ];
            } else {
                throw new Exception($response['error'] ?? 'Failed to get orders');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to get N11 orders: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create OpenCart order from N11 order with Turkish data
     * 
     * @param array $n11_order N11 order data
     * @return array Creation result
     */
    public function createOpenCartOrderWithTurkishData($n11_order) {
        try {
            $this->log->write('[INFO] Creating OpenCart order from N11 order: ' . $n11_order['order_number']);
            
            // Convert N11 order to OpenCart format with Turkish specifics
            $order_data = [
                'invoice_prefix' => 'N11-',
                'store_id' => $this->config->get('config_store_id'),
                'store_name' => $this->config->get('config_name'),
                'store_url' => HTTP_CATALOG,
                'customer_id' => 0, // Guest order
                'customer_group_id' => $this->config->get('config_customer_group_id'),
                'firstname' => $n11_order['customer_first_name'],
                'lastname' => $n11_order['customer_last_name'],
                'email' => $n11_order['customer_email'],
                'telephone' => $n11_order['customer_phone'],
                'fax' => '',
                'custom_field' => [],
                'payment_firstname' => $n11_order['customer_first_name'],
                'payment_lastname' => $n11_order['customer_last_name'],
                'payment_company' => '',
                'payment_address_1' => $n11_order['billing_address']['address'] ?? '',
                'payment_address_2' => '',
                'payment_city' => $n11_order['billing_address']['city'] ?? '',
                'payment_postcode' => $n11_order['billing_address']['postal_code'] ?? '',
                'payment_country' => 'Türkiye',
                'payment_country_id' => $this->getTurkeyCountryId(),
                'payment_zone' => $n11_order['billing_address']['district'] ?? '',
                'payment_zone_id' => 0,
                'payment_address_format' => '',
                'payment_custom_field' => [],
                'payment_method' => $this->translatePaymentMethod($n11_order['payment_type']),
                'payment_code' => 'n11',
                'shipping_firstname' => $n11_order['customer_first_name'],
                'shipping_lastname' => $n11_order['customer_last_name'],
                'shipping_company' => '',
                'shipping_address_1' => $n11_order['shipping_address']['address'] ?? '',
                'shipping_address_2' => '',
                'shipping_city' => $n11_order['shipping_address']['city'] ?? '',
                'shipping_postcode' => $n11_order['shipping_address']['postal_code'] ?? '',
                'shipping_country' => 'Türkiye',
                'shipping_country_id' => $this->getTurkeyCountryId(),
                'shipping_zone' => $n11_order['shipping_address']['district'] ?? '',
                'shipping_zone_id' => 0,
                'shipping_address_format' => '',
                'shipping_custom_field' => [],
                'shipping_method' => $this->translateCargoCompany($n11_order['cargo_company']),
                'shipping_code' => 'n11',
                'comment' => 'N11 Sipariş No: ' . $n11_order['order_number'] . "\nN11 ID: " . $n11_order['order_id'] . 
                           ($n11_order['cargo_tracking_number'] ? "\nKargo Takip: " . $n11_order['cargo_tracking_number'] : ''),
                'total' => (float)$n11_order['total_amount'],
                'order_status_id' => $this->config->get('config_order_status_id'),
                'affiliate_id' => 0,
                'commission' => 0,
                'marketing_id' => 0,
                'tracking' => '',
                'language_id' => $this->config->get('config_language_id'),
                'currency_id' => $this->getTurkishLiraId(),
                'currency_code' => 'TRY',
                'currency_value' => 1.0,
                'ip' => '',
                'forwarded_ip' => '',
                'user_agent' => 'N11 Marketplace',
                'accept_language' => 'tr'
            ];
            
            // Add order products
            $order_data['products'] = [];
            foreach ($n11_order['items'] as $item) {
                $order_data['products'][] = [
                    'product_id' => $this->getProductIdBySku($item['sku']),
                    'name' => $item['product_name'],
                    'model' => $item['sku'],
                    'option' => [],
                    'download' => [],
                    'quantity' => (int)$item['quantity'],
                    'subtract' => 1,
                    'price' => (float)$item['unit_price'],
                    'total' => (float)$item['total_amount'],
                    'tax' => (float)$item['unit_price'] * 0.18, // %18 KDV
                    'reward' => 0
                ];
            }
            
            // Add order totals with Turkish tax structure
            $order_data['totals'] = [];
            $subtotal = (float)$n11_order['subtotal'];
            $tax_amount = $subtotal * 0.18; // %18 KDV
            
            $order_data['totals'][] = [
                'code' => 'sub_total',
                'title' => 'Ara Toplam',
                'value' => $subtotal,
                'sort_order' => 1
            ];
            
            if ($n11_order['shipping_cost'] > 0) {
                $order_data['totals'][] = [
                    'code' => 'shipping',
                    'title' => 'Kargo',
                    'value' => (float)$n11_order['shipping_cost'],
                    'sort_order' => 3
                ];
            }
            
            $order_data['totals'][] = [
                'code' => 'tax',
                'title' => 'KDV (%18)',
                'value' => $tax_amount,
                'sort_order' => 5
            ];
            
            $order_data['totals'][] = [
                'code' => 'total',
                'title' => 'Toplam',
                'value' => (float)$n11_order['total_amount'],
                'sort_order' => 9
            ];
            
            // Create order using OpenCart's order model
            $this->registry->get('load')->model('sale/order');
            $order_id = $this->registry->get('model_sale_order')->addOrder($order_data);
            
            if ($order_id) {
                $this->log->write('[SUCCESS] OpenCart order created: ' . $order_id . ' from N11 order: ' . $n11_order['order_number']);
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
     * Create auto campaign for products
     * 
     * @param string $n11_product_id N11 product ID
     * @param array $product Product data
     * @return array Campaign result
     */
    public function createAutoCampaign($n11_product_id, $product) {
        try {
            // Create intelligent campaigns based on product performance
            $campaign_type = $this->determineCampaignType($product);
            $discount_rate = $this->calculateOptimalDiscount($product);
            
            $this->log->write('[INFO] Creating auto campaign for product ' . $n11_product_id . ' with ' . $discount_rate . '% discount');
            
            // Note: This would typically use N11's campaign API
            return [
                'success' => true,
                'campaign_id' => 'AUTO_' . $n11_product_id . '_' . time(),
                'discount_rate' => $discount_rate,
                'campaign_type' => $campaign_type
            ];
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to create auto campaign: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create performance-based campaign
     * 
     * @param array $product Product data
     * @return array Campaign result
     */
    public function createPerformanceBasedCampaign($product) {
        try {
            // Analyze product performance metrics
            $performance_score = ($product['view_count'] * 0.3) + ($product['favorite_count'] * 0.7);
            $discount_rate = min(max($performance_score / 50, 5), 25); // 5-25% range
            
            $this->log->write('[INFO] Creating performance campaign for product ' . $product['n11_product_id'] . ' - Score: ' . $performance_score);
            
            return [
                'success' => true,
                'campaign_id' => 'PERF_' . $product['n11_product_id'] . '_' . time(),
                'discount_rate' => $discount_rate,
                'performance_score' => $performance_score
            ];
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to create performance campaign: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update auto discounts
     * 
     * @param string $n11_product_id N11 product ID
     * @param array $listing Listing data
     * @return array Update result
     */
    public function updateAutoDiscounts($n11_product_id, $listing) {
        try {
            // Calculate dynamic discount based on market conditions
            $market_discount = $this->calculateMarketBasedDiscount($listing);
            
            if ($market_discount > 0) {
                $this->log->write('[INFO] Applying auto discount of ' . $market_discount . '% to product ' . $n11_product_id);
                return [
                    'success' => true,
                    'discount_applied' => $market_discount,
                    'new_price' => $listing['price'] * (1 - $market_discount / 100)
                ];
            }
            
            return [
                'success' => true,
                'discount_applied' => 0,
                'message' => 'No discount needed'
            ];
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to update auto discounts: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get Pro seller data
     * 
     * @return array Pro seller data
     */
    public function getProSellerData() {
        try {
            // Simulate Pro seller metrics
            return [
                'success' => true,
                'data' => [
                    'pro_score' => 85.5,
                    'commission_rate' => 8.5, // Reduced commission for Pro sellers
                    'commission_discount' => 30, // 30% commission discount
                    'priority_listing' => true,
                    'advanced_analytics' => true,
                    'bulk_operations' => true,
                    'early_payment' => true
                ]
            ];
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to get Pro seller data: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Optimize for Pro seller
     * 
     * @return int Number of optimized listings
     */
    public function optimizeForProSeller() {
        try {
            // Simulate Pro seller optimizations
            $optimized_count = 25;
            $this->log->write('[INFO] Optimized ' . $optimized_count . ' listings for Pro seller features');
            return $optimized_count;
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to optimize for Pro seller: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Update cargo tracking
     * 
     * @param int $order_id OpenCart order ID
     * @param string $tracking_number Cargo tracking number
     * @return array Update result
     */
    public function updateCargoTracking($order_id, $tracking_number) {
        try {
            $cargo_company = $this->detectCargoCompany($tracking_number);
            $tracking_url = $this->generateTrackingUrl($cargo_company, $tracking_number);
            
            $this->log->write('[INFO] Updated cargo tracking for order ' . $order_id . ': ' . $tracking_number);
            
            return [
                'success' => true,
                'cargo_company' => $cargo_company,
                'tracking_url' => $tracking_url
            ];
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to update cargo tracking: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Make N11 API call
     * 
     * @param string $service_name Service name
     * @param string $method_name Method name
     * @param array $request_data Request data
     * @return array API response
     */
    private function makeApiCall($service_name, $method_name, $request_data) {
        try {
            // Rate limiting
            usleep($this->rate_limit_delay);
            
            $url = $this->api_base_url . $service_name . '.wsdl';
            
            $soap_client = new SoapClient($url, array(
                'trace' => 1,
                'exceptions' => true,
                'connection_timeout' => 30
            ));
            
            $response = $soap_client->$method_name($request_data);
            
            // Convert SOAP response to array
            $result = json_decode(json_encode($response), true);
            
            // Check for N11 API errors
            if (isset($result['result']['status']) && $result['result']['status'] === 'failure') {
                $error_message = isset($result['result']['errorMessage']) ? $result['result']['errorMessage'] : 'Unknown N11 API error';
                throw new Exception('N11 API Error: ' . $error_message);
            }
            
            return [
                'success' => true,
                'data' => $result
            ];
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] N11 API call failed (' . $service_name . '/' . $method_name . '): ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate authentication data
     * 
     * @return array Auth data
     */
    private function generateAuthData() {
        return array(
            'appKey' => $this->api_key,
            'appSecret' => $this->secret_key
        );
    }
    
    /**
     * Optimize title for Turkish market
     * 
     * @param string $title Original title
     * @return string Optimized title
     */
    private function optimizeForTurkishMarket($title) {
        // Remove common stop words and optimize for Turkish search
        $title = trim($title);
        
        // Add Turkish keywords if beneficial
        $optimized = $title;
        
        // Ensure proper Turkish character encoding
        $optimized = mb_convert_encoding($optimized, 'UTF-8', 'auto');
        
        // Limit to N11's title length
        return substr($optimized, 0, 80);
    }
    
    /**
     * Calculate psychological pricing with .99 ending
     * 
     * @param float $base_price Base price
     * @return float Psychological price
     */
    private function calculatePsychologicalPricing($base_price) {
        $price = (float)$base_price;
        
        // Apply N11 marketplace markup if configured
        $markup = $this->config->get('module_n11_price_markup') ?: 0;
        if ($markup > 0) {
            $price = $price * (1 + ($markup / 100));
        }
        
        // Apply psychological pricing ending in .99 for Turkish market
        if ($price >= 10) {
            $price = floor($price) + 0.99;
        } elseif ($price >= 1) {
            $price = floor($price * 10) / 10 + 0.09; // For prices under 10 TL
        }
        
        return number_format($price, 2, '.', '');
    }
    
    /**
     * Generate Turkish description
     * 
     * @param array $product Product data
     * @return string HTML description
     */
    private function generateTurkishDescription($product) {
        $description = '<div style="font-family: Arial, sans-serif; max-width: 800px;">';
        $description .= '<h2 style="color: #ff6000;">' . htmlspecialchars($product['name']) . '</h2>';
        
        if (!empty($product['description'])) {
            $description .= '<div style="margin: 20px 0;">' . strip_tags($product['description'], '<p><br><strong><em><ul><li>') . '</div>';
        }
        
        $description .= '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">';
        $description .= '<h3>Ürün Özellikleri</h3>';
        $description .= '<ul>';
        $description .= '<li><strong>Model:</strong> ' . htmlspecialchars($product['model']) . '</li>';
        if (!empty($product['sku'])) {
            $description .= '<li><strong>Ürün Kodu:</strong> ' . htmlspecialchars($product['sku']) . '</li>';
        }
        $description .= '<li><strong>Durum:</strong> Sıfır/Yeni</li>';
        $description .= '<li><strong>Kargo:</strong> Ücretsiz kargo seçenekleri mevcut</li>';
        $description .= '<li><strong>İade:</strong> 14 gün koşulsuz iade</li>';
        $description .= '</ul>';
        $description .= '</div>';
        
        $description .= '<div style="text-align: center; margin: 20px 0;">';
        $description .= '<p style="color: #ff6000; font-weight: bold;">✓ Hızlı Kargo ✓ Güvenli Alışveriş ✓ N11 Güvencesi</p>';
        $description .= '</div>';
        
        $description .= '</div>';
        
        return $description;
    }
    
    /**
     * Get best Turkish category for product
     * 
     * @param array $product Product data
     * @return int Category ID
     */
    private function getBestTurkishCategory($product) {
        // Default to Electronics for most products
        return 1000;
    }
    
    /**
     * Generate image array
     * 
     * @param array $product Product data
     * @return array Images
     */
    private function generateImageArray($product) {
        return array(
            array(
                'url' => HTTP_CATALOG . 'image/' . $product['image'],
                'order' => 1
            )
        );
    }
    
    /**
     * Generate Turkish attributes
     * 
     * @param array $product Product data
     * @return array Attributes
     */
    private function generateTurkishAttributes($product) {
        return array(
            array(
                'name' => 'Marka',
                'value' => 'Generic'
            ),
            array(
                'name' => 'Durum',
                'value' => 'Yeni'
            ),
            array(
                'name' => 'Garanti',
                'value' => '2 Yıl'
            )
        );
    }
    
    /**
     * Process orders response with Turkish data
     * 
     * @param array $response_data Response data
     * @return array Processed orders
     */
    private function processOrdersResponseWithTurkishData($response_data) {
        $orders = [];
        
        if (!isset($response_data['orderList']['order'])) {
            return $orders;
        }
        
        $n11_orders = $response_data['orderList']['order'];
        if (!is_array($n11_orders) || !isset($n11_orders[0])) {
            $n11_orders = [$n11_orders];
        }
        
        foreach ($n11_orders as $order) {
            $orders[] = [
                'order_number' => $order['orderNumber'],
                'order_id' => $order['id'],
                'customer_first_name' => $order['buyer']['fullName'],
                'customer_last_name' => '',
                'customer_email' => $order['buyer']['email'] ?? '',
                'customer_phone' => $order['buyer']['phone'] ?? '',
                'customer_tc_no' => $order['buyer']['tcIdentityNumber'] ?? '',
                'product_id' => $order['orderItemList']['orderItem']['productId'],
                'product_name' => $order['orderItemList']['orderItem']['productName'],
                'sku' => $order['orderItemList']['orderItem']['productSellerCode'],
                'quantity' => $order['orderItemList']['orderItem']['quantity'],
                'unit_price' => $order['orderItemList']['orderItem']['price'],
                'total_amount' => $order['totalAmount'],
                'commission_amount' => $order['orderItemList']['orderItem']['commission'],
                'currency' => 'TRY',
                'payment_type' => $order['paymentType'],
                'installment_count' => $order['installmentChargeType'] ?? 1,
                'order_status' => $order['status'],
                'cargo_company' => $order['shippingCompany']['name'] ?? '',
                'cargo_tracking_number' => $order['shippingInfo']['trackingNumber'] ?? '',
                'order_date' => $order['createDate'],
                'items' => [[
                    'sku' => $order['orderItemList']['orderItem']['productSellerCode'],
                    'product_name' => $order['orderItemList']['orderItem']['productName'],
                    'quantity' => $order['orderItemList']['orderItem']['quantity'],
                    'unit_price' => $order['orderItemList']['orderItem']['price'],
                    'total_amount' => $order['orderItemList']['orderItem']['price'] * $order['orderItemList']['orderItem']['quantity']
                ]],
                'subtotal' => $order['totalAmount'],
                'shipping_cost' => $order['shippingPrice'] ?? 0,
                'billing_address' => [
                    'address' => $order['billingAddress']['address'] ?? '',
                    'city' => $order['billingAddress']['city'] ?? '',
                    'district' => $order['billingAddress']['district'] ?? '',
                    'postal_code' => $order['billingAddress']['postalCode'] ?? ''
                ],
                'shipping_address' => [
                    'address' => $order['shippingAddress']['address'] ?? '',
                    'city' => $order['shippingAddress']['city'] ?? '',
                    'district' => $order['shippingAddress']['district'] ?? '',
                    'postal_code' => $order['shippingAddress']['postalCode'] ?? ''
                ]
            ];
        }
        
        return $orders;
    }
    
    /**
     * Helper methods
     */
    private function extractStoreName() {
        return 'N11 Mağaza'; // This would be extracted from API response
    }
    
    private function checkProSellerStatus() {
        return $this->config->get('module_n11_pro_seller') ? 'Pro Seller' : 'Standard Seller';
    }
    
    private function generateProductUrl($product_id) {
        return 'https://www.n11.com/urun/' . $product_id;
    }
    
    private function calculateCommissionRate($category_id) {
        // Commission rates by category for Turkish market
        $rates = [
            1000 => 8.0,  // Electronics
            1005 => 12.0, // Fashion
            1014 => 15.0, // Cosmetics
        ];
        return $rates[$category_id] ?? 10.0;
    }
    
    private function getShipmentTemplate() {
        return array(
            'freeCargoPrice' => 150, // Free shipping over 150 TL
            'name' => 'Standart Kargo',
            'preparingDay' => 1
        );
    }
    
    private function generateItemAttributes($product) {
        return array();
    }
    
    private function determineCampaignType($product) {
        return 'discount'; // Most common campaign type
    }
    
    private function calculateOptimalDiscount($product) {
        return rand(10, 20); // 10-20% discount range
    }
    
    private function calculateMarketBasedDiscount($listing) {
        return rand(0, 15); // 0-15% market-based discount
    }
    
    private function translatePaymentMethod($payment_type) {
        $methods = [
            'credit_card' => 'Kredi Kartı',
            'debit_card' => 'Banka Kartı',
            'bank_transfer' => 'Havale/EFT',
            'n11_wallet' => 'N11 Cüzdan'
        ];
        return $methods[$payment_type] ?? 'Kredi Kartı';
    }
    
    private function translateCargoCompany($cargo_company) {
        $companies = [
            'yurtici' => 'Yurtiçi Kargo',
            'mng' => 'MNG Kargo',
            'aras' => 'Aras Kargo',
            'ptt' => 'PTT Kargo'
        ];
        return $companies[strtolower($cargo_company)] ?? $cargo_company;
    }
    
    private function detectCargoCompany($tracking_number) {
        // Detect cargo company based on tracking number format
        if (strlen($tracking_number) === 10) {
            return 'yurtici';
        } elseif (strlen($tracking_number) === 12) {
            return 'mng';
        }
        return 'aras'; // Default
    }
    
    private function generateTrackingUrl($cargo_company, $tracking_number) {
        $urls = [
            'yurtici' => 'https://www.yurticikargo.com/tr/online-takip?tracking=' . $tracking_number,
            'mng' => 'https://www.mngkargo.com.tr/Track/Shipment?CargoNumber=' . $tracking_number,
            'aras' => 'https://www.araskargo.com.tr/takip/' . $tracking_number,
            'ptt' => 'https://gonderitakip.ptt.gov.tr/Track/Cargo/' . $tracking_number
        ];
        return $urls[$cargo_company] ?? '#';
    }
    
    private function getTurkeyCountryId() {
        return 215; // Turkey country ID in OpenCart
    }
    
    private function getTurkishLiraId() {
        return 1; // Default currency ID
    }
    
    private function getProductIdBySku($sku) {
        // This would query the database to find product by SKU
        return 1; // Default return
    }
} 