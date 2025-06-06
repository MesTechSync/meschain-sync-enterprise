<?php
/**
 * Hepsiburada API Helper Class
 * MesChain-Sync Enterprise - Advanced Turkish E-commerce Integration
 * 
 * @author MUSTI TEAM - MesChain Development
 * @version 4.0.0
 * @since 2024
 * @copyright 2024 MesChain Technologies
 */

class HepsiburadaApiHelper {
    
    private $config;
    private $logger;
    private $api_base_url = 'https://mpop.hepsiburada.com/api/v1';
    private $merchant_id;
    private $api_key;
    private $username;
    private $password;
    private $access_token;
    
    /**
     * Constructor
     * 
     * @param object $config OpenCart config object
     * @param object $logger Logger instance
     */
    public function __construct($config, $logger = null) {
        $this->config = $config;
        $this->logger = $logger ?: new Log('hepsiburada_api.log');
        
        $this->merchant_id = $this->config->get('module_hepsiburada_advanced_merchant_id');
        $this->username = $this->config->get('module_hepsiburada_advanced_username');
        $this->password = $this->config->get('module_hepsiburada_advanced_password');
        $this->api_key = $this->config->get('module_hepsiburada_advanced_api_key');
    }
    
    /**
     * Test API connection
     * 
     * @return array
     * @throws Exception
     */
    public function testConnection() {
        $start_time = microtime(true);
        
        try {
            $response = $this->makeRequest('GET', '/merchant/info');
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            
            if ($response['success']) {
                return array(
                    'success' => true,
                    'response_time' => $response_time,
                    'merchant_info' => $response['data']
                );
            } else {
                throw new Exception('API bağlantı hatası: ' . $response['error']);
            }
            
        } catch (Exception $e) {
            $this->logger->write('API connection test failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * List product on Hepsiburada with Turkish optimization
     * 
     * @param array $product_data
     * @return array
     */
    public function listProduct($product_data) {
        try {
            // Prepare Turkish-optimized product data
            $listing_data = array(
                'merchantSku' => $this->generateMerchantSku($product_data['product_id']),
                'productName' => $this->optimizeTurkishTitle($product_data['title']),
                'productDescription' => $this->formatTurkishDescription($product_data['description']),
                'categoryId' => $product_data['category_id'],
                'price' => (float)$product_data['price'],
                'currency' => 'TRY',
                'stockQuantity' => (int)$product_data['quantity'],
                'images' => $this->formatProductImages($product_data['images']),
                'attributes' => $this->formatProductAttributes($product_data['attributes']),
                'shippingTemplate' => $this->getOptimalShippingTemplate($product_data),
                'warranty' => $this->determineWarrantyPeriod($product_data),
                'brand' => $product_data['manufacturer'] ?? 'Genel',
                'model' => $product_data['model'] ?? '',
                'keywords' => $this->generateTurkishKeywords($product_data)
            );
            
            // Add Turkish-specific optimizations
            if ($product_data['optimization_level'] === 'premium') {
                $listing_data = $this->applyPremiumOptimizations($listing_data, $product_data);
            }
            
            $response = $this->makeRequest('POST', '/products', $listing_data);
            
            if ($response['success']) {
                $this->logger->write('Product listed successfully: ' . $product_data['product_id']);
                return array(
                    'success' => true,
                    'listing_id' => $response['data']['productId'],
                    'merchant_sku' => $listing_data['merchantSku'],
                    'optimization_score' => $product_data['optimization_score']
                );
            } else {
                throw new Exception('Ürün listeleme hatası: ' . $response['error']);
            }
            
        } catch (Exception $e) {
            $this->logger->write('Product listing error for ID ' . $product_data['product_id'] . ': ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Update product price with market analysis
     * 
     * @param string $merchant_sku
     * @param float $new_price
     * @param array $price_strategy
     * @return array
     */
    public function updateProductPrice($merchant_sku, $new_price, $price_strategy = array()) {
        try {
            // Analyze competitor prices if strategy requires it
            if (isset($price_strategy['competitive_analysis']) && $price_strategy['competitive_analysis']) {
                $competitor_analysis = $this->analyzeCompetitorPrices($merchant_sku);
                $new_price = $this->optimizePriceBasedOnCompetition($new_price, $competitor_analysis, $price_strategy);
            }
            
            $update_data = array(
                'price' => (float)$new_price,
                'currency' => 'TRY'
            );
            
            // Add promotional pricing if specified
            if (isset($price_strategy['promotional']) && $price_strategy['promotional']) {
                $update_data['promotionalPrice'] = $this->calculatePromotionalPrice($new_price, $price_strategy);
                $update_data['promotionStartDate'] = date('Y-m-d H:i:s');
                $update_data['promotionEndDate'] = date('Y-m-d H:i:s', strtotime('+' . ($price_strategy['promotion_days'] ?? 7) . ' days'));
            }
            
            $response = $this->makeRequest('PUT', '/products/' . $merchant_sku . '/price', $update_data);
            
            if ($response['success']) {
                return array(
                    'success' => true,
                    'updated_price' => $new_price,
                    'competitive_position' => $competitor_analysis['position'] ?? 'unknown'
                );
            } else {
                throw new Exception('Fiyat güncelleme hatası: ' . $response['error']);
            }
            
        } catch (Exception $e) {
            $this->logger->write('Price update error for SKU ' . $merchant_sku . ': ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Analyze optimal cargo for order
     * 
     * @param array $order_data
     * @return array
     */
    public function analyzeOptimalCargo($order_data) {
        try {
            $analysis_data = array(
                'orderWeight' => $this->calculateOrderWeight($order_data),
                'orderDimensions' => $this->calculateOrderDimensions($order_data),
                'deliveryAddress' => array(
                    'cityCode' => $this->getCityCode($order_data['shipping_city']),
                    'districtCode' => $this->getDistrictCode($order_data['shipping_zone']),
                    'postalCode' => $order_data['shipping_postcode']
                ),
                'urgency' => $this->determineDeliveryUrgency($order_data),
                'value' => (float)$order_data['total']
            );
            
            $response = $this->makeRequest('POST', '/cargo/analyze', $analysis_data);
            
            if ($response['success']) {
                return array(
                    'success' => true,
                    'recommendations' => $response['data']['recommendations'],
                    'cost_analysis' => $response['data']['costs'],
                    'delivery_estimates' => $response['data']['delivery_times']
                );
            } else {
                // Fallback to local analysis if API fails
                return $this->performLocalCargoAnalysis($analysis_data);
            }
            
        } catch (Exception $e) {
            $this->logger->write('Cargo analysis error: ' . $e->getMessage());
            return $this->performLocalCargoAnalysis($analysis_data ?? array());
        }
    }
    
    /**
     * Get Hepsiburada orders with advanced filtering
     * 
     * @param array $filters
     * @return array
     */
    public function getOrders($filters = array()) {
        try {
            $query_params = array(
                'startDate' => $filters['start_date'] ?? date('Y-m-d', strtotime('-7 days')),
                'endDate' => $filters['end_date'] ?? date('Y-m-d'),
                'status' => $filters['status'] ?? 'all',
                'limit' => $filters['limit'] ?? 100,
                'offset' => $filters['offset'] ?? 0
            );
            
            $response = $this->makeRequest('GET', '/orders?' . http_build_query($query_params));
            
            if ($response['success']) {
                return array(
                    'success' => true,
                    'orders' => $response['data']['orders'],
                    'total_count' => $response['data']['totalCount'],
                    'pagination' => array(
                        'current_page' => ($query_params['offset'] / $query_params['limit']) + 1,
                        'total_pages' => ceil($response['data']['totalCount'] / $query_params['limit'])
                    )
                );
            } else {
                throw new Exception('Sipariş listesi alınamadı: ' . $response['error']);
            }
            
        } catch (Exception $e) {
            $this->logger->write('Get orders error: ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Update order status with cargo information
     * 
     * @param string $order_id
     * @param string $status
     * @param array $cargo_info
     * @return array
     */
    public function updateOrderStatus($order_id, $status, $cargo_info = array()) {
        try {
            $update_data = array(
                'status' => $status
            );
            
            if (!empty($cargo_info)) {
                $update_data['cargoCompany'] = $cargo_info['provider'];
                $update_data['trackingNumber'] = $cargo_info['tracking_number'];
                $update_data['shippingDate'] = date('Y-m-d H:i:s');
            }
            
            $response = $this->makeRequest('PUT', '/orders/' . $order_id . '/status', $update_data);
            
            if ($response['success']) {
                return array(
                    'success' => true,
                    'updated_status' => $status,
                    'tracking_info' => $cargo_info
                );
            } else {
                throw new Exception('Sipariş durumu güncellenemedi: ' . $response['error']);
            }
            
        } catch (Exception $e) {
            $this->logger->write('Order status update error for ' . $order_id . ': ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Create promotional campaign
     * 
     * @param array $campaign_data
     * @return array
     */
    public function createPromotionalCampaign($campaign_data) {
        try {
            $promotion_data = array(
                'campaignName' => $campaign_data['name'],
                'campaignType' => $campaign_data['type'], // flash_sale, discount, seasonal
                'startDate' => $campaign_data['start_date'],
                'endDate' => $campaign_data['end_date'],
                'products' => $campaign_data['products'],
                'discountType' => $campaign_data['discount_type'], // percentage, fixed_amount
                'discountValue' => (float)$campaign_data['discount_value'],
                'targetAudience' => $campaign_data['target_audience'] ?? 'all',
                'budget' => (float)($campaign_data['budget'] ?? 0)
            );
            
            $response = $this->makeRequest('POST', '/campaigns', $promotion_data);
            
            if ($response['success']) {
                return array(
                    'success' => true,
                    'campaign_id' => $response['data']['campaignId'],
                    'estimated_reach' => $response['data']['estimatedReach'],
                    'expected_revenue' => $response['data']['expectedRevenue']
                );
            } else {
                throw new Exception('Kampanya oluşturulamadı: ' . $response['error']);
            }
            
        } catch (Exception $e) {
            $this->logger->write('Campaign creation error: ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Get performance analytics
     * 
     * @param array $params
     * @return array
     */
    public function getPerformanceAnalytics($params = array()) {
        try {
            $analytics_params = array(
                'startDate' => $params['start_date'] ?? date('Y-m-d', strtotime('-30 days')),
                'endDate' => $params['end_date'] ?? date('Y-m-d'),
                'metrics' => implode(',', $params['metrics'] ?? array('sales', 'views', 'conversion')),
                'groupBy' => $params['group_by'] ?? 'day'
            );
            
            $response = $this->makeRequest('GET', '/analytics?' . http_build_query($analytics_params));
            
            if ($response['success']) {
                return array(
                    'success' => true,
                    'analytics' => $response['data']['analytics'],
                    'summary' => $response['data']['summary'],
                    'trends' => $response['data']['trends']
                );
            } else {
                throw new Exception('Analitik veriler alınamadı: ' . $response['error']);
            }
            
        } catch (Exception $e) {
            $this->logger->write('Analytics fetch error: ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Make HTTP request to Hepsiburada API
     * 
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @return array
     */
    private function makeRequest($method, $endpoint, $data = null) {
        $url = $this->api_base_url . $endpoint;
        
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->getAccessToken(),
            'X-Merchant-ID: ' . $this->merchant_id
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        if ($method === 'POST' || $method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            throw new Exception('CURL Error: ' . $curl_error);
        }
        
        $decoded_response = json_decode($response, true);
        
        if ($http_code >= 200 && $http_code < 300) {
            return array(
                'success' => true,
                'data' => $decoded_response
            );
        } else {
            return array(
                'success' => false,
                'error' => $decoded_response['message'] ?? 'HTTP Error: ' . $http_code,
                'http_code' => $http_code
            );
        }
    }
    
    /**
     * Get or refresh access token
     * 
     * @return string
     */
    private function getAccessToken() {
        // Check if we have a valid cached token
        if ($this->access_token && !$this->isTokenExpired()) {
            return $this->access_token;
        }
        
        // Get new token
        $auth_data = array(
            'username' => $this->username,
            'password' => $this->password,
            'grant_type' => 'password'
        );
        
        $response = $this->makeAuthRequest('/auth/token', $auth_data);
        
        if ($response['success']) {
            $this->access_token = $response['data']['access_token'];
            // Cache token expiry time
            $this->cacheTokenExpiry($response['data']['expires_in']);
            return $this->access_token;
        } else {
            throw new Exception('Token alınamadı: ' . $response['error']);
        }
    }
    
    /**
     * Optimize Turkish product title for SEO
     * 
     * @param string $title
     * @return string
     */
    private function optimizeTurkishTitle($title) {
        // Remove extra spaces and clean title
        $title = trim(preg_replace('/\s+/', ' ', $title));
        
        // Turkish-specific optimizations
        $title = $this->addTurkishKeywords($title);
        $title = $this->correctTurkishCharacters($title);
        
        // Length optimization for Hepsiburada (max 100 characters)
        if (mb_strlen($title, 'UTF-8') > 100) {
            $title = mb_substr($title, 0, 97, 'UTF-8') . '...';
        }
        
        return $title;
    }
    
    /**
     * Local cargo analysis fallback
     * 
     * @param array $analysis_data
     * @return array
     */
    private function performLocalCargoAnalysis($analysis_data) {
        // Local fallback logic for cargo selection
        $weight = $analysis_data['orderWeight'] ?? 1;
        $city_code = $analysis_data['deliveryAddress']['cityCode'] ?? '34'; // Default Istanbul
        
        $cargo_options = array(
            array(
                'provider' => 'Yurtiçi Kargo',
                'cost' => $weight < 5 ? 12.50 : 15.00,
                'delivery_time' => $city_code == '34' ? '1-2 gün' : '2-3 gün',
                'reason' => 'Güvenilir ve hızlı teslimat'
            ),
            array(
                'provider' => 'Aras Kargo',
                'cost' => $weight < 5 ? 11.00 : 14.00,
                'delivery_time' => $city_code == '34' ? '1-2 gün' : '2-4 gün',
                'reason' => 'Ekonomik seçenek'
            )
        );
        
        return array(
            'success' => true,
            'recommendations' => $cargo_options,
            'selected' => $cargo_options[0] // Default to first option
        );
    }
    
    /**
     * Generate merchant SKU
     * 
     * @param int $product_id
     * @return string
     */
    private function generateMerchantSku($product_id) {
        return 'MESH-' . $this->merchant_id . '-' . str_pad($product_id, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Calculate order weight
     * 
     * @param array $order_data
     * @return float
     */
    private function calculateOrderWeight($order_data) {
        $total_weight = 0;
        foreach ($order_data['products'] as $product) {
            $total_weight += (float)$product['weight'] * (int)$product['quantity'];
        }
        return $total_weight > 0 ? $total_weight : 1.0; // Minimum 1kg
    }
}
?>