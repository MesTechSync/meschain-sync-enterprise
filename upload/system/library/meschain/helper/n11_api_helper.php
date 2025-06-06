<?php
/**
 * N11 API Helper Class
 * MesChain-Sync Enterprise - OpenCart 3.0.4.0 Compatible
 * N11 Marketplace API operations and Turkish market optimization
 * 
 * @author MUSTI TEAM - MesChain Development
 * @version 4.0.0
 * @since 2024
 * @copyright 2024 MesChain Technologies
 */

class N11ApiHelper {
    
    private $config;
    private $logger;
    private $api_base_url = 'https://www.n11.com/ws/';
    private $api_version = '1.0';
    private $timeout = 30;
    
    private $api_key;
    private $secret_key;
    private $store_key;
    
    /**
     * Constructor
     * 
     * @param object $config OpenCart config object
     */
    public function __construct($config) {
        $this->config = $config;
        $this->logger = new Log('n11_api_helper.log');
        
        // Load API credentials from config
        $this->api_key = $this->config->get('module_n11_api_key');
        $this->secret_key = $this->config->get('module_n11_secret_key');
        $this->store_key = $this->config->get('module_n11_store_key');
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
            $url = $this->api_base_url . 'CityService.do';
            $response = $this->makeRequest('GET', $url, array());
            
            $end_time = microtime(true);
            $response_time = round(($end_time - $start_time) * 1000, 2);
            
            if ($response && isset($response['result']['status']) && $response['result']['status'] == 'success') {
                return array(
                    'success' => true,
                    'response_time' => $response_time,
                    'message' => 'N11 API connection successful'
                );
            } else {
                throw new Exception('N11 API connection failed');
            }
            
        } catch (Exception $e) {
            $this->logger->write('N11 API connection test failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * List product on N11 with Turkish market optimization
     * 
     * @param array $product_data
     * @return array
     * @throws Exception
     */
    public function listProductWithTurkishOptimization($product_data) {
        try {
            // Optimize product data for Turkish market
            $optimized_data = $this->optimizeForTurkishMarket($product_data);
            
            // Prepare product listing request
            $listing_data = array(
                'stockItems' => array(
                    'stockItem' => array(
                        'productSellerCode' => $optimized_data['sku'],
                        'title' => $optimized_data['title'],
                        'subtitle' => $optimized_data['subtitle'],
                        'description' => $optimized_data['description'],
                        'category' => array(
                            'id' => $optimized_data['category_id']
                        ),
                        'price' => $optimized_data['price'],
                        'currencyType' => 1, // TL
                        'images' => $optimized_data['images'],
                        'saleStartDate' => $optimized_data['sale_start_date'],
                        'saleEndDate' => $optimized_data['sale_end_date'],
                        'productionDate' => $optimized_data['production_date'],
                        'expirationDate' => $optimized_data['expiration_date'],
                        'productCondition' => $optimized_data['condition'],
                        'preparingDay' => $optimized_data['preparing_day'],
                        'shipmentTemplate' => $optimized_data['shipment_template'],
                        'stockItems' => array(
                            'stockItem' => array(
                                'bundle' => false,
                                'attributes' => $optimized_data['attributes'],
                                'quantity' => $optimized_data['quantity'],
                                'sellerStockCode' => $optimized_data['seller_stock_code'],
                                'optionPrice' => 0,
                                'groupAttribute' => '',
                                'tax' => $optimized_data['tax_rate']
                            )
                        )
                    )
                )
            );
            
            // Send listing request
            $url = $this->api_base_url . 'ProductStockService.do';
            $response = $this->makeRequest('POST', $url, $listing_data);
            
            if ($response && isset($response['result']['status']) && $response['result']['status'] == 'success') {
                return array(
                    'success' => true,
                    'product_id' => $response['result']['stockItem']['id'],
                    'message' => 'Product listed successfully on N11'
                );
            } else {
                $error_message = isset($response['result']['errorMessage']) ? 
                    $response['result']['errorMessage'] : 'Unknown error occurred';
                throw new Exception($error_message);
            }
            
        } catch (Exception $e) {
            $this->logger->write('Product listing error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Search products on N11
     * 
     * @param string $keyword
     * @param int $category_id
     * @param array $options
     * @return array
     */
    public function searchProducts($keyword, $category_id = null, $options = array()) {
        try {
            $search_params = array(
                'keyword' => $keyword,
                'pagingData' => array(
                    'currentPage' => isset($options['page']) ? $options['page'] : 1,
                    'pageSize' => isset($options['limit']) ? $options['limit'] : 20
                )
            );
            
            if ($category_id) {
                $search_params['categoryId'] = $category_id;
            }
            
            if (isset($options['sort'])) {
                $search_params['sortForCategory'] = $options['sort'];
            }
            
            $url = $this->api_base_url . 'SearchService.do';
            $response = $this->makeRequest('POST', $url, $search_params);
            
            if ($response && isset($response['result']['products'])) {
                return $this->formatSearchResults($response['result']['products']);
            }
            
            return array();
            
        } catch (Exception $e) {
            $this->logger->write('Product search error: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Update product price
     * 
     * @param string $n11_product_id
     * @param float $new_price
     * @return bool
     * @throws Exception
     */
    public function updateProductPrice($n11_product_id, $new_price) {
        try {
            $price_data = array(
                'stockItems' => array(
                    'stockItem' => array(
                        'id' => $n11_product_id,
                        'price' => $new_price
                    )
                )
            );
            
            $url = $this->api_base_url . 'ProductStockService.do';
            $response = $this->makeRequest('PUT', $url, $price_data);
            
            if ($response && isset($response['result']['status']) && $response['result']['status'] == 'success') {
                return true;
            } else {
                throw new Exception('Price update failed: ' . 
                    (isset($response['result']['errorMessage']) ? $response['result']['errorMessage'] : 'Unknown error'));
            }
            
        } catch (Exception $e) {
            $this->logger->write('Price update error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update product stock
     * 
     * @param string $n11_product_id
     * @param int $quantity
     * @return bool
     * @throws Exception
     */
    public function updateProductStock($n11_product_id, $quantity) {
        try {
            $stock_data = array(
                'stockItems' => array(
                    'stockItem' => array(
                        'id' => $n11_product_id,
                        'quantity' => $quantity
                    )
                )
            );
            
            $url = $this->api_base_url . 'ProductStockService.do';
            $response = $this->makeRequest('PUT', $url, $stock_data);
            
            if ($response && isset($response['result']['status']) && $response['result']['status'] == 'success') {
                return true;
            } else {
                throw new Exception('Stock update failed: ' . 
                    (isset($response['result']['errorMessage']) ? $response['result']['errorMessage'] : 'Unknown error'));
            }
            
        } catch (Exception $e) {
            $this->logger->write('Stock update error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get orders from N11
     * 
     * @param string $status
     * @param string $date_from
     * @param string $date_to
     * @return array
     */
    public function getOrders($status = null, $date_from = null, $date_to = null) {
        try {
            $order_params = array(
                'pagingData' => array(
                    'currentPage' => 1,
                    'pageSize' => 100
                )
            );
            
            if ($status) {
                $order_params['status'] = $status;
            }
            
            if ($date_from) {
                $order_params['startDate'] = $date_from;
            }
            
            if ($date_to) {
                $order_params['endDate'] = $date_to;
            }
            
            $url = $this->api_base_url . 'OrderService.do';
            $response = $this->makeRequest('POST', $url, $order_params);
            
            if ($response && isset($response['result']['orderList'])) {
                return $this->formatOrderResults($response['result']['orderList']);
            }
            
            return array();
            
        } catch (Exception $e) {
            $this->logger->write('Order retrieval error: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Get N11 categories
     * 
     * @param int $parent_id
     * @return array
     */
    public function getCategories($parent_id = 0) {
        try {
            $category_params = array();
            
            if ($parent_id > 0) {
                $category_params['parentId'] = $parent_id;
            }
            
            $url = $this->api_base_url . 'CategoryService.do';
            $response = $this->makeRequest('POST', $url, $category_params);
            
            if ($response && isset($response['result']['categoryList'])) {
                return $response['result']['categoryList'];
            }
            
            return array();
            
        } catch (Exception $e) {
            $this->logger->write('Category retrieval error: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Optimize product data for Turkish market
     * 
     * @param array $product_data
     * @return array
     */
    private function optimizeForTurkishMarket($product_data) {
        $optimized = array();
        
        // Title optimization for Turkish SEO
        $optimized['title'] = $this->optimizeTurkishTitle($product_data['name']);
        $optimized['subtitle'] = $this->generateSubtitle($product_data);
        
        // Description optimization
        $optimized['description'] = $this->optimizeTurkishDescription($product_data['description']);
        
        // SKU and codes
        $optimized['sku'] = $product_data['sku'];
        $optimized['seller_stock_code'] = $product_data['model'];
        
        // Pricing
        $optimized['price'] = $this->calculateCompetitivePrice($product_data['price']);
        
        // Category mapping
        $optimized['category_id'] = $this->mapToN11Category($product_data['category_id']);
        
        // Images
        $optimized['images'] = $this->formatImages($product_data['images']);
        
        // Dates
        $optimized['sale_start_date'] = date('d/m/Y');
        $optimized['sale_end_date'] = date('d/m/Y', strtotime('+1 year'));
        $optimized['production_date'] = date('d/m/Y');
        $optimized['expiration_date'] = date('d/m/Y', strtotime('+2 years'));
        
        // Product specifics
        $optimized['condition'] = 1; // New
        $optimized['preparing_day'] = $this->config->get('module_n11_preparing_day') ?: 1;
        $optimized['shipment_template'] = $this->config->get('module_n11_shipment_template') ?: 1;
        $optimized['quantity'] = $product_data['quantity'];
        $optimized['tax_rate'] = $this->config->get('module_n11_tax_rate') ?: 18;
        
        // Attributes
        $optimized['attributes'] = $this->formatAttributes($product_data['attributes']);
        
        return $optimized;
    }
    
    /**
     * Optimize title for Turkish SEO
     * 
     * @param string $title
     * @return string
     */
    private function optimizeTurkishTitle($title) {
        // Turkish character normalization
        $title = $this->normalizeTurkishChars($title);
        
        // Add common Turkish e-commerce keywords
        $keywords = array('Kaliteli', 'Uygun Fiyat', 'Hızlı Kargo', 'Garantili');
        
        // Limit to N11 title character limit
        if (strlen($title) > 60) {
            $title = substr($title, 0, 57) . '...';
        }
        
        return $title;
    }
    
    /**
     * Optimize description for Turkish market
     * 
     * @param string $description
     * @return string
     */
    private function optimizeTurkishDescription($description) {
        // Clean HTML
        $description = strip_tags($description);
        
        // Turkish character normalization
        $description = $this->normalizeTurkishChars($description);
        
        // Add Turkish market appeal
        $turkish_phrases = array(
            'Türkiye\'de üretilmiştir.',
            'Kalite garantisi ile sunulmaktadır.',
            'Hızlı ve güvenli kargo ile teslimat yapılır.',
            'Müşteri memnuniyeti önceliğimizdir.'
        );
        
        $description .= "\n\n" . implode("\n", $turkish_phrases);
        
        return $description;
    }
    
    /**
     * Normalize Turkish characters
     * 
     * @param string $text
     * @return string
     */
    private function normalizeTurkishChars($text) {
        $turkish_chars = array(
            'ç' => 'c', 'Ç' => 'C',
            'ğ' => 'g', 'Ğ' => 'G',
            'ı' => 'i', 'I' => 'I',
            'ö' => 'o', 'Ö' => 'O',
            'ş' => 's', 'Ş' => 'S',
            'ü' => 'u', 'Ü' => 'U'
        );
        
        return strtr($text, $turkish_chars);
    }
    
    /**
     * Calculate competitive price
     * 
     * @param float $original_price
     * @return float
     */
    private function calculateCompetitivePrice($original_price) {
        $markup_percentage = $this->config->get('module_n11_price_markup') ?: 0;
        return $original_price * (1 + ($markup_percentage / 100));
    }
    
    /**
     * Make HTTP request to N11 API
     * 
     * @param string $method
     * @param string $url
     * @param array $data
     * @return array|null
     * @throws Exception
     */
    private function makeRequest($method, $url, $data = array()) {
        if (!$this->api_key || !$this->secret_key || !$this->store_key) {
            throw new Exception('N11 API credentials not configured');
        }
        
        // Add authentication
        $data['auth'] = array(
            'appKey' => $this->api_key,
            'appSecret' => $this->secret_key
        );
        
        $ch = curl_init();
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ));
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception('CURL Error: ' . $error);
        }
        
        if ($http_code >= 400) {
            throw new Exception('HTTP Error ' . $http_code . ': ' . $response);
        }
        
        $decoded_response = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . json_last_error_msg());
        }
        
        return $decoded_response;
    }
    
    /**
     * Format search results
     * 
     * @param array $products
     * @return array
     */
    private function formatSearchResults($products) {
        $formatted = array();
        
        foreach ($products as $product) {
            $formatted[] = array(
                'product_id' => $product['id'],
                'seller_id' => $product['seller']['id'],
                'seller_name' => $product['seller']['sellerName'],
                'name' => $product['title'],
                'price' => $product['price'],
                'rating' => $product['rate'],
                'url' => $product['productUrl']
            );
        }
        
        return $formatted;
    }
    
    /**
     * Format order results
     * 
     * @param array $orders
     * @return array
     */
    private function formatOrderResults($orders) {
        $formatted = array();
        
        foreach ($orders as $order) {
            $formatted[] = array(
                'order_id' => $order['id'],
                'order_number' => $order['orderNumber'],
                'status' => $order['status'],
                'total_amount' => $order['totalAmount'],
                'order_date' => $order['orderDate'],
                'customer_info' => $order['buyer'],
                'items' => $order['orderItemList']
            );
        }
        
        return $formatted;
    }
    
    /**
     * Map OpenCart category to N11 category
     * 
     * @param int $opencart_category_id
     * @return int
     */
    private function mapToN11Category($opencart_category_id) {
        // This would contain actual category mapping logic
        // For now, returning a default electronics category
        return 1000;
    }
    
    /**
     * Format product images for N11
     * 
     * @param array $images
     * @return array
     */
    private function formatImages($images) {
        $formatted_images = array();
        
        foreach ($images as $index => $image) {
            $formatted_images[] = array(
                'url' => $image,
                'order' => $index + 1
            );
        }
        
        return $formatted_images;
    }
    
    /**
     * Format product attributes for N11
     * 
     * @param array $attributes
     * @return array
     */
    private function formatAttributes($attributes) {
        $formatted_attributes = array();
        
        foreach ($attributes as $attribute) {
            $formatted_attributes[] = array(
                'name' => $attribute['name'],
                'value' => $attribute['value']
            );
        }
        
        return $formatted_attributes;
    }
    
    /**
     * Generate optimized subtitle
     * 
     * @param array $product_data
     * @return string
     */
    private function generateSubtitle($product_data) {
        $subtitle_parts = array();
        
        if (isset($product_data['brand'])) {
            $subtitle_parts[] = $product_data['brand'];
        }
        
        if (isset($product_data['model'])) {
            $subtitle_parts[] = $product_data['model'];
        }
        
        $subtitle_parts[] = 'Kaliteli';
        $subtitle_parts[] = 'Uygun Fiyat';
        
        return implode(' ', $subtitle_parts);
    }
}
?>