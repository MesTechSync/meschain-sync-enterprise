<?php
/**
 * Ozon API Helper Class
 * MesChain-Sync v3.0 - Russian E-commerce Platform Integration
 * Advanced FBO Support, Russian Market Optimization
 * 
 * @author MesChain Development Team
 * @version 3.0.0
 * @copyright 2024 MesChain Technologies
 */

class OzonHelper {
    
    private $config;
    private $log;
    private $api_base_url = 'https://api-seller.ozon.ru';
    private $api_version = 'v3';
    private $client_id;
    private $api_key;
    private $rate_limit_delay = 200000; // 200ms between requests
    private $max_retries = 3;
    
    public function __construct($config, $log) {
        $this->config = $config;
        $this->log = $log;
        $this->client_id = $config->get('module_ozon_client_id');
        $this->api_key = $config->get('module_ozon_api_key');
    }
    
    /**
     * Test API connection
     */
    public function testConnection() {
        $start_time = microtime(true);
        
        try {
            $response = $this->makeApiRequest('GET', '/v1/seller/info');
            $execution_time = microtime(true) - $start_time;
            
            if ($response['success']) {
                return array(
                    'success' => true,
                    'seller_id' => $response['data']['id'],
                    'seller_name' => $response['data']['name'],
                    'response_time' => round($execution_time * 1000, 2) . 'ms'
                );
            } else {
                return array(
                    'success' => false,
                    'error' => $response['error']
                );
            }
        } catch (Exception $e) {
            $this->log->write('OZON API CONNECTION ERROR: ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => 'Connection failed: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Sync product to Ozon
     */
    public function syncProduct($product) {
        try {
            // Prepare product data for Ozon format
            $ozon_product = $this->prepareProductData($product);
            
            // Check if product already exists
            $existing = $this->findProductBySku($product['sku']);
            
            if ($existing['success'] && !empty($existing['data'])) {
                // Update existing product
                return $this->updateProduct($existing['data'][0]['id'], $ozon_product);
            } else {
                // Create new product
                return $this->createProduct($ozon_product);
            }
            
        } catch (Exception $e) {
            $this->log->write('OZON SYNC PRODUCT ERROR: ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Update product price on Ozon
     */
    public function updateProductPrice($product) {
        try {
            $price_data = array(
                'prices' => array(
                    array(
                        'offer_id' => $product['sku'],
                        'price' => $this->formatRussianPrice($product['price']),
                        'old_price' => $this->calculateOldPrice($product['price']),
                        'premium_price' => $this->calculatePremiumPrice($product['price']),
                        'currency_code' => 'RUB'
                    )
                )
            );
            
            $response = $this->makeApiRequest('POST', '/v1/product/import/prices', $price_data);
            
            if ($response['success']) {
                return array(
                    'success' => true,
                    'task_id' => $response['data']['task_id']
                );
            } else {
                return array(
                    'success' => false,
                    'error' => $response['error']
                );
            }
            
        } catch (Exception $e) {
            $this->log->write('OZON UPDATE PRICE ERROR: ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Upload product to FBO warehouse
     */
    public function uploadToFbo($product, $warehouse_id) {
        try {
            $fbo_data = array(
                'stocks' => array(
                    array(
                        'offer_id' => $product['sku'],
                        'stock' => $this->calculateFboStock($product['quantity']),
                        'warehouse_id' => $warehouse_id
                    )
                )
            );
            
            $response = $this->makeApiRequest('POST', '/v1/product/import/stocks', $fbo_data);
            
            if ($response['success']) {
                return array(
                    'success' => true,
                    'task_id' => $response['data']['task_id']
                );
            } else {
                return array(
                    'success' => false,
                    'error' => $response['error']
                );
            }
            
        } catch (Exception $e) {
            $this->log->write('OZON FBO UPLOAD ERROR: ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Get orders from Ozon
     */
    public function getOrders($limit = 100, $status = null) {
        try {
            $params = array(
                'dir' => 'desc',
                'filter' => array(
                    'since' => date('Y-m-d\TH:i:s.v\Z', strtotime('-30 days')),
                    'to' => date('Y-m-d\TH:i:s.v\Z')
                ),
                'limit' => $limit,
                'offset' => 0
            );
            
            if ($status) {
                $params['filter']['status'] = $status;
            }
            
            $response = $this->makeApiRequest('POST', '/v3/order/list', $params);
            
            if ($response['success']) {
                return array(
                    'success' => true,
                    'data' => $response['data']['result']
                );
            } else {
                return array(
                    'success' => false,
                    'error' => $response['error']
                );
            }
            
        } catch (Exception $e) {
            $this->log->write('OZON GET ORDERS ERROR: ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Get FBO warehouses
     */
    public function getWarehouses() {
        try {
            $response = $this->makeApiRequest('POST', '/v1/warehouse/list');
            
            if ($response['success']) {
                return array(
                    'success' => true,
                    'data' => $response['data']['result']
                );
            } else {
                return array(
                    'success' => false,
                    'error' => $response['error']
                );
            }
            
        } catch (Exception $e) {
            $this->log->write('OZON GET WAREHOUSES ERROR: ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Find product by SKU
     */
    private function findProductBySku($sku) {
        try {
            $params = array(
                'filter' => array(
                    'offer_id' => array($sku)
                ),
                'limit' => 1
            );
            
            $response = $this->makeApiRequest('POST', '/v2/product/list', $params);
            
            return $response;
            
        } catch (Exception $e) {
            $this->log->write('OZON FIND PRODUCT ERROR: ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Create new product on Ozon
     */
    private function createProduct($product_data) {
        try {
            $response = $this->makeApiRequest('POST', '/v2/product/import', array('items' => array($product_data)));
            
            if ($response['success']) {
                return array(
                    'success' => true,
                    'task_id' => $response['data']['task_id']
                );
            } else {
                return array(
                    'success' => false,
                    'error' => $response['error']
                );
            }
            
        } catch (Exception $e) {
            $this->log->write('OZON CREATE PRODUCT ERROR: ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Update existing product on Ozon
     */
    private function updateProduct($product_id, $product_data) {
        try {
            $product_data['id'] = $product_id;
            
            $response = $this->makeApiRequest('POST', '/v2/product/import', array('items' => array($product_data)));
            
            if ($response['success']) {
                return array(
                    'success' => true,
                    'task_id' => $response['data']['task_id']
                );
            } else {
                return array(
                    'success' => false,
                    'error' => $response['error']
                );
            }
            
        } catch (Exception $e) {
            $this->log->write('OZON UPDATE PRODUCT ERROR: ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Prepare product data for Ozon format
     */
    private function prepareProductData($product) {
        return array(
            'name' => $this->sanitizeProductName($product['name']),
            'offer_id' => $product['sku'],
            'currency_code' => 'RUB',
            'old_price' => $this->calculateOldPrice($product['price']),
            'premium_price' => $this->calculatePremiumPrice($product['price']),
            'price' => $this->formatRussianPrice($product['price']),
            'vat' => '0.20', // Standard Russian VAT rate
            'category_id' => $this->detectOzonCategory($product),
            'images' => $this->prepareProductImages($product),
            'attributes' => $this->prepareProductAttributes($product),
            'complex_attributes' => array(),
            'depth' => $this->extractDimension($product, 'depth'),
            'dimension_unit' => 'mm',
            'height' => $this->extractDimension($product, 'height'),
            'weight' => $this->extractWeight($product),
            'weight_unit' => 'g',
            'width' => $this->extractDimension($product, 'width')
        );
    }
    
    /**
     * Make API request to Ozon
     */
    private function makeApiRequest($method, $endpoint, $data = null) {
        if (empty($this->client_id) || empty($this->api_key)) {
            throw new Exception('Ozon API credentials not configured');
        }
        
        $url = $this->api_base_url . $endpoint;
        
        $headers = array(
            'Client-Id: ' . $this->client_id,
            'Api-Key: ' . $this->api_key,
            'Content-Type: application/json',
            'User-Agent: MesChain-Sync/3.0 (OpenCart Integration)'
        );
        
        $ch = curl_init();
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_USERAGENT => 'MesChain-Sync/3.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 0
        ));
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Rate limiting
        usleep($this->rate_limit_delay);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        $decoded_response = json_decode($response, true);
        
        if ($http_code >= 200 && $http_code < 300) {
            return array(
                'success' => true,
                'data' => $decoded_response,
                'http_code' => $http_code
            );
        } else {
            $error_message = 'HTTP ' . $http_code;
            if (isset($decoded_response['message'])) {
                $error_message .= ': ' . $decoded_response['message'];
            }
            
            return array(
                'success' => false,
                'error' => $error_message,
                'http_code' => $http_code,
                'response' => $decoded_response
            );
        }
    }
    
    /**
     * Russian market optimization functions
     */
    private function formatRussianPrice($price) {
        // Convert price to RUB and format according to Russian standards
        $rub_price = $price; // Assuming price is already in RUB
        
        // Russian pricing psychology - ending with 9
        if ($rub_price > 100) {
            $rounded = floor($rub_price / 10) * 10 - 1;
            return max($rounded, $rub_price * 0.95); // Never go below 95% of original
        }
        
        return $rub_price;
    }
    
    private function calculateOldPrice($price) {
        // Calculate psychological old price for Russian market
        return $price * 1.25; // 25% higher for discount effect
    }
    
    private function calculatePremiumPrice($price) {
        // Premium pricing for Ozon Plus subscribers
        return $price * 0.95; // 5% discount for premium users
    }
    
    private function calculateFboStock($quantity) {
        // FBO stock calculation with Russian fulfillment optimization
        if ($quantity <= 5) {
            return max(1, $quantity); // Always show at least 1 for small quantities
        } elseif ($quantity <= 20) {
            return $quantity; // Show actual stock for medium quantities
        } else {
            return min(50, $quantity); // Cap at 50 for large quantities
        }
    }
    
    private function sanitizeProductName($name) {
        // Ozon product name requirements for Russian market
        $name = trim($name);
        $name = preg_replace('/[^\p{L}\p{N}\s\-_().,]/u', '', $name); // Allow Cyrillic
        $name = preg_replace('/\s+/', ' ', $name); // Normalize spaces
        
        return mb_substr($name, 0, 500, 'UTF-8'); // Ozon limit
    }
    
    private function detectOzonCategory($product) {
        // Auto-detect Ozon category based on product data
        // Default to Electronics for now - should be enhanced with ML
        return 17032819; // Electronics category ID
    }
    
    private function prepareProductImages($product) {
        // Prepare images for Ozon (max 15 images)
        $images = array();
        
        // Add main image
        if (isset($product['image']) && !empty($product['image'])) {
            $images[] = array(
                'file_name' => basename($product['image']),
                'default' => true
            );
        }
        
        return $images;
    }
    
    private function prepareProductAttributes($product) {
        // Basic attributes for Russian market
        return array(
            array(
                'complex_id' => 0,
                'id' => 85, // Brand attribute
                'values' => array(
                    array('value' => isset($product['manufacturer']) ? $product['manufacturer'] : 'Generic')
                )
            ),
            array(
                'complex_id' => 0,
                'id' => 9048, // Color attribute
                'values' => array(
                    array('value' => 'Разноцветный') // Multicolor
                )
            )
        );
    }
    
    private function extractDimension($product, $type) {
        // Extract dimensions from product data
        // Default values in mm for Russian market
        $defaults = array(
            'depth' => 100,
            'height' => 50,
            'width' => 100
        );
        
        return $defaults[$type];
    }
    
    private function extractWeight($product) {
        // Extract weight in grams
        return 500; // Default 500g
    }
    
    /**
     * Get Russian business hours factor
     */
    private function getRussianBusinessHoursFactor() {
        $moscow_time = new DateTime('now', new DateTimeZone('Europe/Moscow'));
        $hour = (int)$moscow_time->format('H');
        $day = (int)$moscow_time->format('N'); // 1 = Monday, 7 = Sunday
        
        if ($hour >= 9 && $hour <= 18 && $day <= 5) {
            return 1.0; // Business hours
        } elseif ($hour >= 19 && $hour <= 23) {
            return 1.5; // Evening peak
        } else {
            return 0.8; // Off hours
        }
    }
    
    /**
     * Retry mechanism for failed requests
     */
    private function retryApiRequest($method, $endpoint, $data = null, $attempt = 1) {
        try {
            return $this->makeApiRequest($method, $endpoint, $data);
        } catch (Exception $e) {
            if ($attempt < $this->max_retries) {
                // Exponential backoff
                sleep(pow(2, $attempt));
                return $this->retryApiRequest($method, $endpoint, $data, $attempt + 1);
            } else {
                throw $e;
            }
        }
    }
}
?> 