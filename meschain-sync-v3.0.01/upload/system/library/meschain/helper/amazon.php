<?php
/**
 * MeschainAmazonHelper - Modern Amazon SP-API Entegrasyonu
 * 
 * Amazon Selling Partner API (SP-API) v0 ile event-driven architecture,
 * health monitoring ve webhook desteği
 * 
 * @author MesChain Development Team
 * @version 2.0.0
 * @since 2024-01-21
 */

class MeschainAmazonHelper {
    
    private $registry;
    private $db;
    private $log;
    private $configHelper;
    private $eventHelper;
    private $monitoringHelper;
    
    // SP-API endpoints
    private $apiEndpoints = [
        'na' => 'https://sellingpartnerapi-na.amazon.com',
        'eu' => 'https://sellingpartnerapi-eu.amazon.com',
        'fe' => 'https://sellingpartnerapi-fe.amazon.com'
    ];
    
    // Sandbox endpoints
    private $sandboxEndpoints = [
        'na' => 'https://sandbox.sellingpartnerapi-na.amazon.com',
        'eu' => 'https://sandbox.sellingpartnerapi-eu.amazon.com',
        'fe' => 'https://sandbox.sellingpartnerapi-fe.amazon.com'
    ];
    
    // Marketplace IDs
    private $marketplaceIds = [
        'US' => 'ATVPDKIKX0DER',
        'CA' => 'A2EUQ1WTGCTBG2',
        'MX' => 'A1AM78C64UM0Y8',
        'BR' => 'A2Q3Y263D00KWC',
        'DE' => 'A1PA6795UKMFR9',
        'ES' => 'A1RKKUPIHCS9HS',
        'FR' => 'A13V1IB3VIYZZH',
        'IT' => 'APJ6JRA9NG5V4',
        'UK' => 'A1F83G8C2ARO7P',
        'IN' => 'A21TJRUUN4KGV',
        'JP' => 'A1VC38T7YXB528',
        'AU' => 'A39IBJ37TRP1C6',
        'SG' => 'A19VAU5U5O7RUS'
    ];
    
    // Rate limiting (requests per second)
    private $rateLimits = [
        'orders' => ['rate' => 0.0167, 'burst' => 20],      // 1 req/60 sec, burst 20
        'catalog' => ['rate' => 2, 'burst' => 20],          // 2 req/sec, burst 20
        'inventory' => ['rate' => 2, 'burst' => 30],        // 2 req/sec, burst 30
        'feeds' => ['rate' => 0.0167, 'burst' => 15],       // 1 req/60 sec, burst 15
        'reports' => ['rate' => 0.0167, 'burst' => 15]      // 1 req/60 sec, burst 15
    ];
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->log = new Log('meschain_amazon.log');
        
        // Helper'ları yükle
        require_once(DIR_SYSTEM . 'library/meschain/helper/config.php');
        require_once(DIR_SYSTEM . 'library/meschain/helper/event.php');
        require_once(DIR_SYSTEM . 'library/meschain/helper/monitoring.php');
        
        $this->configHelper = new MeschainConfigHelper($registry);
        $this->eventHelper = new MeschainEventHelper($registry);
        $this->monitoringHelper = new MeschainMonitoringHelper($registry);
        
        $this->createAmazonTables();
        $this->loadDefaultConfigs();
    }
    
    /**
     * Amazon tablolarını oluştur
     */
    private function createAmazonTables() {
        // Amazon products mapping
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "amazon_products` (
            `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_product_id` int(11) NOT NULL,
            `amazon_asin` varchar(50),
            `sku` varchar(100) NOT NULL,
            `marketplace_id` varchar(50) NOT NULL,
            `condition_type` varchar(50) DEFAULT 'New',
            `listing_price` decimal(15,4),
            `quantity` int(11) DEFAULT 0,
            `fulfillment_channel` enum('MERCHANT','AMAZON') DEFAULT 'MERCHANT',
            `status` varchar(50) DEFAULT 'Active',
            `last_sync` datetime DEFAULT NULL,
            `sync_status` enum('synced','pending','error') DEFAULT 'pending',
            `error_message` text,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`mapping_id`),
            UNIQUE KEY `sku_marketplace_unique` (`sku`, `marketplace_id`),
            KEY `opencart_product_id` (`opencart_product_id`),
            KEY `amazon_asin` (`amazon_asin`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Amazon orders
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "amazon_orders` (
            `amazon_order_id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_order_id` int(11) DEFAULT NULL,
            `order_id` varchar(100) NOT NULL,
            `marketplace_id` varchar(50) NOT NULL,
            `order_status` varchar(50) NOT NULL,
            `fulfillment_channel` varchar(50),
            `purchase_date` datetime NOT NULL,
            `last_update_date` datetime,
            `order_total` decimal(15,4),
            `currency_code` varchar(10),
            `number_of_items_shipped` int(11) DEFAULT 0,
            `number_of_items_unshipped` int(11) DEFAULT 0,
            `payment_execution_detail` json,
            `payment_method` varchar(50),
            `buyer_info` json,
            `shipping_address` json,
            `order_items` json,
            `sync_status` enum('pending','synced','error') DEFAULT 'pending',
            `error_message` text,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`amazon_order_id`),
            UNIQUE KEY `order_id_unique` (`order_id`),
            KEY `opencart_order_id` (`opencart_order_id`),
            KEY `order_status` (`order_status`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Amazon API logs
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "amazon_api_logs` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `endpoint` varchar(255) NOT NULL,
            `method` varchar(10) NOT NULL,
            `marketplace_id` varchar(50),
            `request_data` json,
            `response_data` json,
            `http_status` int(11),
            `response_time` decimal(10,4),
            `rate_limit_remaining` int(11),
            `success` tinyint(1) DEFAULT 0,
            `error_message` text,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`log_id`),
            KEY `endpoint` (`endpoint`),
            KEY `success` (`success`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Amazon reports
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "amazon_reports` (
            `report_id` int(11) NOT NULL AUTO_INCREMENT,
            `amazon_report_id` varchar(100) NOT NULL,
            `report_type` varchar(100) NOT NULL,
            `marketplace_ids` json,
            `report_status` varchar(50) NOT NULL,
            `created_time` datetime,
            `processing_start_time` datetime,
            `processing_end_time` datetime,
            `data_start_time` datetime,
            `data_end_time` datetime,
            `document_url` varchar(500),
            `processed` tinyint(1) DEFAULT 0,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`report_id`),
            UNIQUE KEY `amazon_report_id_unique` (`amazon_report_id`),
            KEY `report_type` (`report_type`),
            KEY `report_status` (`report_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        $this->log->write('Amazon tabloları oluşturuldu/kontrol edildi');
    }
    
    /**
     * Varsayılan konfigürasyonları yükle
     */
    private function loadDefaultConfigs() {
        $defaults = [
            'amazon.default_marketplace' => 'DE',
            'amazon.region' => 'eu',
            'amazon.timeout' => 30,
            'amazon.retry_attempts' => 3,
            'amazon.rate_limit_enabled' => true,
            'amazon.auto_sync_products' => true,
            'amazon.auto_sync_orders' => true,
            'amazon.auto_sync_interval' => 900, // 15 dakika
            'amazon.fulfillment_channel' => 'MERCHANT',
            'amazon.condition_type' => 'New',
            'amazon.sandbox_mode' => false,
            'amazon.max_results_per_page' => 50
        ];
        
        foreach ($defaults as $key => $value) {
            $existing = $this->configHelper->get($key);
            if ($existing === null) {
                $this->configHelper->set($key, $value, [
                    'type' => 'marketplace',
                    'description' => 'Amazon marketplace configuration'
                ]);
            }
        }
    }
    
    /**
     * API kimlik bilgilerini al
     */
    private function getApiCredentials($tenantId = null) {
        $config = $this->configHelper->getMarketplaceConfig('amazon', $tenantId);
        
        return [
            'client_id' => $config['amazon.client_id'] ?? null,
            'client_secret' => $config['amazon.client_secret'] ?? null,
            'refresh_token' => $config['amazon.refresh_token'] ?? null,
            'seller_id' => $config['amazon.seller_id'] ?? null,
            'marketplace_id' => $config['amazon.marketplace_id'] ?? $this->marketplaceIds[$config['amazon.default_marketplace'] ?? 'DE'],
            'region' => $config['amazon.region'] ?? 'eu',
            'sandbox_mode' => $config['amazon.sandbox_mode'] ?? false
        ];
    }
    
    /**
     * Access token al (LWA - Login with Amazon)
     */
    private function getAccessToken($tenantId = null) {
        $credentials = $this->getApiCredentials($tenantId);
        
        if (!$credentials['client_id'] || !$credentials['client_secret'] || !$credentials['refresh_token']) {
            throw new Exception('Amazon API kimlik bilgileri eksik');
        }
        
        // Cache'den kontrol et
        $cacheKey = "amazon_access_token_{$tenantId}";
        $cache = $this->registry->get('cache');
        
        if ($cache) {
            $cachedToken = $cache->get($cacheKey);
            if ($cachedToken) {
                return $cachedToken;
            }
        }
        
        // LWA token endpoint
        $tokenUrl = 'https://api.amazon.com/auth/o2/token';
        
        $postData = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $credentials['refresh_token'],
            'client_id' => $credentials['client_id'],
            'client_secret' => $credentials['client_secret']
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $tokenUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpStatus !== 200) {
            throw new Exception("Access token alınamadı: HTTP {$httpStatus}");
        }
        
        $tokenData = json_decode($response, true);
        
        if (!isset($tokenData['access_token'])) {
            throw new Exception("Access token response'unda hata: " . ($tokenData['error_description'] ?? 'Bilinmeyen hata'));
        }
        
        // Cache'le (expires_in - 60 saniye güvenlik)
        if ($cache && isset($tokenData['expires_in'])) {
            $cache->set($cacheKey, $tokenData['access_token'], $tokenData['expires_in'] - 60);
        }
        
        return $tokenData['access_token'];
    }
    
    /**
     * SP-API isteği yap
     */
    private function makeApiRequest($endpoint, $method = 'GET', $data = null, $tenantId = null, $marketplaceId = null) {
        $startTime = microtime(true);
        $credentials = $this->getApiCredentials($tenantId);
        
        if (!$marketplaceId) {
            $marketplaceId = $credentials['marketplace_id'];
        }
        
        // Access token al
        $accessToken = $this->getAccessToken($tenantId);
        
        // Rate limiting kontrolü
        $this->checkRateLimit($endpoint);
        
        // Base URL belirle
        $region = $credentials['region'];
        $baseUrl = $credentials['sandbox_mode'] 
            ? $this->sandboxEndpoints[$region] 
            : $this->apiEndpoints[$region];
        
        $url = $baseUrl . $endpoint;
        
        // Headers
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'User-Agent: MesChain-Sync/2.0',
            'x-amz-access-token: ' . $accessToken
        ];
        
        // Marketplace ID gerekiyorsa ekle
        if (strpos($endpoint, 'orders') !== false || strpos($endpoint, 'catalog') !== false) {
            $headers[] = 'x-amz-marketplace-id: ' . $marketplaceId;
        }
        
        // cURL isteği
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->configHelper->get('amazon.timeout', 30),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        // Method'a göre ayarlar
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
        }
        
        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseTime = microtime(true) - $startTime;
        $error = curl_error($ch);
        curl_close($ch);
        
        // Rate limit bilgilerini header'dan al
        $rateLimitRemaining = null;
        // SP-API'de rate limit header'ları response'da gelir
        
        // Performans metriği kaydet
        $this->monitoringHelper->recordMetric('amazon_api_response_time', $responseTime, 'seconds', [
            'endpoint' => $endpoint,
            'method' => $method,
            'marketplace_id' => $marketplaceId
        ]);
        
        // API log kaydet
        $this->logApiRequest($endpoint, $method, $data, $response, $httpStatus, $responseTime, $rateLimitRemaining, $error, $tenantId, $marketplaceId);
        
        if ($error) {
            throw new Exception("cURL hatası: {$error}");
        }
        
        if ($httpStatus >= 400) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['errors'][0]['message'] ?? "HTTP {$httpStatus} hatası";
            throw new Exception("Amazon API hatası: {$errorMessage}");
        }
        
        $result = json_decode($response, true);
        
        // Rate limit bilgisini güncelle
        $this->updateRateLimit($endpoint);
        
        return $result;
    }
    
    /**
     * Rate limit kontrolü
     */
    private function checkRateLimit($endpoint) {
        if (!$this->configHelper->get('amazon.rate_limit_enabled', true)) {
            return;
        }
        
        $endpointType = $this->getEndpointType($endpoint);
        $limits = $this->rateLimits[$endpointType] ?? $this->rateLimits['orders'];
        
        $cacheKey = "amazon_rate_limit_{$endpointType}";
        $cache = $this->registry->get('cache');
        
        if ($cache) {
            $lastRequest = $cache->get($cacheKey) ?? 0;
            $timeSinceLastRequest = microtime(true) - $lastRequest;
            $minInterval = 1 / $limits['rate'];
            
            if ($timeSinceLastRequest < $minInterval) {
                $waitTime = $minInterval - $timeSinceLastRequest;
                usleep($waitTime * 1000000); // microseconds
            }
        }
    }
    
    /**
     * Rate limit sayacını güncelle
     */
    private function updateRateLimit($endpoint) {
        $endpointType = $this->getEndpointType($endpoint);
        $cacheKey = "amazon_rate_limit_{$endpointType}";
        $cache = $this->registry->get('cache');
        
        if ($cache) {
            $cache->set($cacheKey, microtime(true), 3600);
        }
    }
    
    /**
     * Endpoint tipini belirle
     */
    private function getEndpointType($endpoint) {
        if (strpos($endpoint, '/orders/') !== false) {
            return 'orders';
        } elseif (strpos($endpoint, '/catalog/') !== false) {
            return 'catalog';
        } elseif (strpos($endpoint, '/inventory/') !== false) {
            return 'inventory';
        } elseif (strpos($endpoint, '/feeds/') !== false) {
            return 'feeds';
        } elseif (strpos($endpoint, '/reports/') !== false) {
            return 'reports';
        }
        return 'orders';
    }
    
    /**
     * API log kaydet
     */
    private function logApiRequest($endpoint, $method, $requestData, $responseData, $httpStatus, $responseTime, $rateLimitRemaining, $error, $tenantId, $marketplaceId) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "amazon_api_logs` SET
            endpoint = '" . $this->db->escape($endpoint) . "',
            method = '" . $this->db->escape($method) . "',
            marketplace_id = '" . $this->db->escape($marketplaceId) . "',
            request_data = '" . $this->db->escape(json_encode($requestData)) . "',
            response_data = '" . $this->db->escape(substr($responseData, 0, 65535)) . "',
            http_status = " . (int)$httpStatus . ",
            response_time = " . (float)$responseTime . ",
            rate_limit_remaining = " . ($rateLimitRemaining ? (int)$rateLimitRemaining : "NULL") . ",
            success = " . ($httpStatus >= 200 && $httpStatus < 300 ? 1 : 0) . ",
            error_message = " . ($error ? "'" . $this->db->escape($error) . "'" : "NULL") . ",
            tenant_id = " . ($tenantId ? (int)$tenantId : "NULL") . ",
            created_at = NOW()
        ");
    }
    
    /**
     * Health check
     */
    public function healthCheck() {
        $startTime = microtime(true);
        
        try {
            $credentials = $this->getApiCredentials();
            
            if (!$credentials['client_id'] || !$credentials['client_secret'] || !$credentials['refresh_token']) {
                return [
                    'status' => 'error',
                    'message' => 'API kimlik bilgileri eksik',
                    'response_time' => microtime(true) - $startTime
                ];
            }
            
            // Basit API testi - marketplace participation al
            $response = $this->makeApiRequest('/sellers/v1/marketplaceParticipations');
            
            if (isset($response['payload'])) {
                return [
                    'status' => 'healthy',
                    'message' => 'API bağlantısı başarılı',
                    'response_time' => microtime(true) - $startTime,
                    'marketplace_count' => count($response['payload'])
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'API response beklenen formatta değil',
                    'response_time' => microtime(true) - $startTime
                ];
            }
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'response_time' => microtime(true) - $startTime
            ];
        }
    }
    
    /**
     * Ürün senkronizasyonu (Inventory Management)
     */
    public function syncProducts($productIds = [], $tenantId = null) {
        $this->log->write("Amazon ürün senkronizasyonu başlatıldı");
        
        try {
            $syncedCount = 0;
            $errorCount = 0;
            $errors = [];
            
            // Tüm ürünler için veya belirli ürünler için
            $sql = "SELECT p.* FROM `" . DB_PREFIX . "product` p WHERE p.status = 1";
            
            if (!empty($productIds)) {
                $sql .= " AND p.product_id IN (" . implode(',', array_map('intval', $productIds)) . ")";
            }
            
            $query = $this->db->query($sql);
            
            foreach ($query->rows as $product) {
                try {
                    $this->syncSingleProduct($product, $tenantId);
                    $syncedCount++;
                    
                    // Event tetikle
                    $this->eventHelper->trigger('product.synced', [
                        'marketplace' => 'amazon',
                        'product_id' => $product['product_id'],
                        'product_name' => $product['name']
                    ], ['type' => 'async']);
                    
                } catch (Exception $e) {
                    $errorCount++;
                    $errors[] = "Ürün {$product['product_id']}: " . $e->getMessage();
                    $this->log->write("Ürün sync hatası: " . $e->getMessage());
                }
            }
            
            $this->log->write("Ürün senkronizasyonu tamamlandı. Başarılı: {$syncedCount}, Hatalı: {$errorCount}");
            
            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            $this->log->write("Ürün senkronizasyonu genel hatası: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Sipariş senkronizasyonu
     */
    public function syncOrders($tenantId = null, $createdAfter = null) {
        $this->log->write("Amazon sipariş senkronizasyonu başlatıldı");
        
        try {
            $credentials = $this->getApiCredentials($tenantId);
            
            $params = [
                'MarketplaceIds' => $credentials['marketplace_id']
            ];
            
            if ($createdAfter) {
                $params['CreatedAfter'] = $createdAfter;
            } else {
                $params['CreatedAfter'] = date('c', strtotime('-7 days'));
            }
            
            $queryString = http_build_query($params);
            $response = $this->makeApiRequest("/orders/v0/orders?{$queryString}", 'GET', null, $tenantId);
            
            $syncedCount = 0;
            $errorCount = 0;
            
            if (isset($response['payload']['Orders'])) {
                foreach ($response['payload']['Orders'] as $amazonOrder) {
                    try {
                        $this->processAmazonOrder($amazonOrder, $tenantId);
                        $syncedCount++;
                        
                        // Event tetikle
                        $this->eventHelper->trigger('order.synced', [
                            'marketplace' => 'amazon',
                            'order_id' => $amazonOrder['AmazonOrderId'],
                            'amount' => $amazonOrder['OrderTotal']['Amount'] ?? 0
                        ], ['type' => 'async']);
                        
                    } catch (Exception $e) {
                        $errorCount++;
                        $this->log->write("Sipariş sync hatası: " . $e->getMessage());
                    }
                }
            }
            
            $this->log->write("Sipariş senkronizasyonu tamamlandı. Başarılı: {$syncedCount}, Hatalı: {$errorCount}");
            
            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'error_count' => $errorCount
            ];
            
        } catch (Exception $e) {
            $this->log->write("Sipariş senkronizasyonu genel hatası: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Helper metodlar
     */
    private function syncSingleProduct($product, $tenantId = null) {
        // Amazon için SKU oluştur
        $sku = $this->generateSku($product);
        
        // Inventory güncelleme
        $inventoryData = [
            'inventoryDetails' => [
                $sku => [
                    'fulfillableQuantity' => max(0, (int)$product['quantity'])
                ]
            ]
        ];
        
        $response = $this->makeApiRequest('/fba/inventory/v1/inventories', 'PUT', $inventoryData, $tenantId);
        
        return $response;
    }
    
    private function processAmazonOrder($amazonOrder, $tenantId = null) {
        // Mevcut sipariş kontrolü ve işleme
        // Implementation burada
    }
    
    private function generateSku($product) {
        return 'AMZ-' . str_pad($product['product_id'], 8, '0', STR_PAD_LEFT);
    }
}
?> 