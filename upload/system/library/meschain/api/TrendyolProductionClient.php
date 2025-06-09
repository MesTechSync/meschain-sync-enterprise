<?php
/**
 * 🔥 TRENDYOL PRODUCTION API CLIENT
 * Live Production Environment with Real API Credentials
 * 
 * @version 3.0-PRODUCTION
 * @date June 9, 2025
 * @author MesChain Development Team - LIVE DEPLOYMENT
 */

class TrendyolProductionClient {
    
    // 🚀 LIVE PRODUCTION CREDENTIALS
    private $apiKey = 'f4KhSfv7ihjXcJFlJeim';
    private $apiSecret = 'GLs2YLpJwPJtEX6dSPbi';
    private $supplierId = '1076956';
    private $baseUrl = 'https://api.trendyol.com/sapigw';
    private $environment = 'PRODUCTION';
    private $cache;
    private $logger;

    /**
     * Production Constructor - Auto-configured with live credentials
     */
    public function __construct($cache = null, $logger = null) {
        $this->cache = $cache;
        $this->logger = $logger;
        
        // Log production initialization
        $this->log('PRODUCTION_INIT', 'Trendyol Production Client initialized', [
            'supplier_id' => $this->supplierId,
            'environment' => $this->environment,
            'api_url' => $this->baseUrl
        ]);
    }

    /**
     * 🔥 PRODUCTION API REQUEST METHOD
     */
    public function request($endpoint, $method = 'GET', $data = []) {
        $start_time = microtime(true);
        
        try {
            $url = $this->baseUrl . '/suppliers/' . $this->supplierId . $endpoint;
            
            $headers = [
                'Authorization: Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret),
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: MesChain-Sync-v3.0-Production',
                'X-Supplied-By: ' . $this->supplierId,
                'X-Environment: PRODUCTION'
            ];

            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3
            ]);

            switch (strtoupper($method)) {
                case 'POST':
                    curl_setopt($ch, CURLOPT_POST, true);
                    if (!empty($data)) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    }
                    break;
                    
                case 'PUT':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                    if (!empty($data)) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    }
                    break;
                    
                case 'DELETE':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    break;
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $response_time = round((microtime(true) - $start_time) * 1000, 2);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new Exception('Production API cURL Error: ' . $error);
            }

            curl_close($ch);

            // Log production request
            $this->log('API_REQUEST', 'Production API request completed', [
                'endpoint' => $endpoint,
                'method' => $method,
                'http_code' => $httpCode,
                'response_time' => $response_time . 'ms',
                'environment' => 'PRODUCTION'
            ]);

            $decodedResponse = json_decode($response, true);

            if ($httpCode >= 400) {
                $errorMessage = 'Trendyol Production API Error (HTTP ' . $httpCode . ')';
                if (isset($decodedResponse['errors']) && is_array($decodedResponse['errors'])) {
                    $errorMessage .= ': ' . implode(', ', array_column($decodedResponse['errors'], 'message'));
                }
                
                $this->log('API_ERROR', $errorMessage, [
                    'endpoint' => $endpoint,
                    'http_code' => $httpCode,
                    'response' => $response
                ]);
                
                throw new Exception($errorMessage, $httpCode);
            }

            return $decodedResponse;
            
        } catch (Exception $e) {
            $this->log('API_EXCEPTION', 'Production API exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'environment' => 'PRODUCTION'
            ]);
            throw $e;
        }
    }

    /**
     * 🔍 PRODUCTION CONNECTION TEST
     */
    public function testProductionConnection() {
        try {
            $this->log('CONNECTION_TEST', 'Starting production connection test');
            
            // Test with brands endpoint (lightweight)
            $response = $this->request('/brands?page=0&size=1');
            
            $result = [
                'status' => 'SUCCESS',
                'message' => 'Production API connection established',
                'supplier_id' => $this->supplierId,
                'environment' => $this->environment,
                'api_url' => $this->baseUrl,
                'timestamp' => date('Y-m-d H:i:s'),
                'response_data' => $response
            ];
            
            $this->log('CONNECTION_SUCCESS', 'Production connection test successful', $result);
            return $result;
            
        } catch (Exception $e) {
            $result = [
                'status' => 'FAILED',
                'message' => 'Production API connection failed: ' . $e->getMessage(),
                'error_code' => $e->getCode(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            $this->log('CONNECTION_FAILED', 'Production connection test failed', $result);
            return $result;
        }
    }

    /**
     * 📦 GET PRODUCTS - Production
     */
    public function getProducts($page = 0, $size = 50) {
        return $this->request("/products?page={$page}&size={$size}");
    }

    /**
     * 📋 GET ORDERS - Production
     */
    public function getOrders($startDate = null, $endDate = null, $page = 0, $size = 50) {
        $params = "page={$page}&size={$size}";
        
        if ($startDate) {
            $params .= "&startDate=" . urlencode($startDate);
        }
        if ($endDate) {
            $params .= "&endDate=" . urlencode($endDate);
        }
        
        return $this->request("/orders?{$params}");
    }

    /**
     * 🏷️ GET CATEGORIES - Production
     */
    public function getCategories() {
        $cacheKey = 'trendyol_prod_categories';
        
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }
        
        $categories = $this->request('/product-categories');
        
        if ($this->cache) {
            $this->cache->set($cacheKey, $categories, 3600); // Cache for 1 hour
        }
        
        return $categories;
    }

    /**
     * 📊 GET SUPPLIER INFO - Production
     */
    public function getSupplierInfo() {
        return $this->request('/suppliers/' . $this->supplierId);
    }

    /**
     * 🔧 UPDATE PRODUCT PRICE AND INVENTORY
     */
    public function updatePriceAndInventory($items) {
        return $this->request('/products/price-and-inventory', 'POST', ['items' => $items]);
    }

    /**
     * 📦 CREATE PRODUCT - Production
     */
    public function createProduct($productData) {
        return $this->request('/products', 'POST', $productData);
    }

    /**
     * 🚚 GET SHIPMENT PROVIDERS
     */
    public function getShipmentProviders() {
        return $this->request('/shipment-providers');
    }

    /**
     * 💰 GET FINANCE INFO
     */
    public function getFinanceInfo() {
        return $this->request('/finance/iban');
    }

    /**
     * 🎯 WEBHOOK VALIDATION - Production
     */
    public function validateProductionWebhook($signature, $payload) {
        if (empty($signature) || empty($this->apiSecret)) {
            return false;
        }
        
        $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $this->apiSecret, true));
        $isValid = hash_equals($expectedSignature, $signature);
        
        $this->log('WEBHOOK_VALIDATION', 'Webhook validation result', [
            'is_valid' => $isValid,
            'environment' => 'PRODUCTION'
        ]);
        
        return $isValid;
    }

    /**
     * 📝 PRODUCTION LOGGING
     */
    private function log($type, $message, $data = []) {
        if ($this->logger) {
            $this->logger->write("TRENDYOL_PROD_{$type}: {$message} " . json_encode($data));
        }
        
        // Also log to file
        $log_dir = dirname(__FILE__) . '/../../../../logs/trendyol_production/';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'environment' => 'PRODUCTION',
            'supplier_id' => $this->supplierId
        ];
        
        file_put_contents(
            $log_dir . date('Y-m-d') . '_production.log', 
            json_encode($log_entry) . "\n", 
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * 📊 GET PRODUCTION STATUS
     */
    public function getProductionStatus() {
        return [
            'supplier_id' => $this->supplierId,
            'environment' => $this->environment,
            'api_url' => $this->baseUrl,
            'status' => 'PRODUCTION_ACTIVE',
            'last_check' => date('Y-m-d H:i:s'),
            'version' => '3.0-PRODUCTION'
        ];
    }
}

/**
 * 🚀 PRODUCTION HELPER FUNCTIONS
 */

// Get production client instance
function getTrendyolProductionClient($cache = null, $logger = null) {
    return new TrendyolProductionClient($cache, $logger);
}

// Test production connection
function testTrendyolProduction() {
    $client = new TrendyolProductionClient();
    return $client->testProductionConnection();
}

// Get production status
function getTrendyolProductionStatus() {
    $client = new TrendyolProductionClient();
    return $client->getProductionStatus();
} 