<?php
/**
 * MeschainN11Helper - Modern N11 API Entegrasyonu
 * 
 * Event-driven architecture, health monitoring ve webhook desteği ile
 * gelişmiş N11 marketplace entegrasyonu
 * 
 * @author MesChain Development Team
 * @version 2.0.0
 * @since 2024-01-21
 */

class MeschainN11Helper {
    
    private $registry;
    private $db;
    private $log;
    private $configHelper;
    private $eventHelper;
    private $monitoringHelper;
    
    // API URLs
    private $apiUrl = 'https://api.n11.com/ws';
    private $sandboxUrl = 'https://prepapi.n11.com/ws';
    
    // API endpoints
    private $endpoints = [
        'city' => '/CityService.wsdl',
        'category' => '/CategoryService.wsdl',
        'product' => '/ProductService.wsdl',
        'order' => '/OrderService.wsdl',
        'shipment' => '/ShipmentService.wsdl',
        'settlement' => '/SettlementService.wsdl',
        'question' => '/ProductQuestionService.wsdl',
        'auth' => '/AuthenticationService.wsdl'
    ];
    
    // Rate limiting
    private $rateLimits = [
        'default' => ['requests' => 1000, 'period' => 3600], // 1000 req/hour
        'products' => ['requests' => 500, 'period' => 3600],  // 500 req/hour
        'orders' => ['requests' => 1000, 'period' => 3600]    // 1000 req/hour
    ];
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->log = new Log('meschain_n11.log');
        
        // Helper'ları yükle
        require_once(DIR_SYSTEM . 'library/meschain/helper/config.php');
        require_once(DIR_SYSTEM . 'library/meschain/helper/event.php');
        require_once(DIR_SYSTEM . 'library/meschain/helper/monitoring.php');
        
        $this->configHelper = new MeschainConfigHelper($registry);
        $this->eventHelper = new MeschainEventHelper($registry);
        $this->monitoringHelper = new MeschainMonitoringHelper($registry);
        
        $this->createN11Tables();
        $this->loadDefaultConfigs();
    }
    
    /**
     * N11 tablolarını oluştur
     */
    private function createN11Tables() {
        // N11 products mapping
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_products` (
            `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_product_id` int(11) NOT NULL,
            `n11_product_id` varchar(100),
            `product_selling_id` varchar(100),
            `sku` varchar(100) NOT NULL,
            `approved` tinyint(1) DEFAULT 0,
            `approval_status` varchar(50) DEFAULT 'WAITING_APPROVAL',
            `category_id` int(11),
            `last_sync` datetime DEFAULT NULL,
            `sync_status` enum('synced','pending','error') DEFAULT 'pending',
            `error_message` text,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`mapping_id`),
            UNIQUE KEY `sku_unique` (`sku`),
            KEY `opencart_product_id` (`opencart_product_id`),
            KEY `n11_product_id` (`n11_product_id`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // N11 orders
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_orders` (
            `n11_order_id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_order_id` int(11) DEFAULT NULL,
            `order_number` varchar(100) NOT NULL,
            `order_status` varchar(50) NOT NULL,
            `total_amount` decimal(15,4) NOT NULL,
            `order_date` datetime NOT NULL,
            `citizen_ship_number` varchar(50),
            `payment_type` varchar(50),
            `installment` varchar(50),
            `order_items` json,
            `buyer_info` json,
            `billing_address` json,
            `shipping_address` json,
            `sync_status` enum('pending','synced','error') DEFAULT 'pending',
            `error_message` text,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`n11_order_id`),
            UNIQUE KEY `order_number_unique` (`order_number`),
            KEY `opencart_order_id` (`opencart_order_id`),
            KEY `order_status` (`order_status`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // N11 API logs
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_api_logs` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `service` varchar(100) NOT NULL,
            `method` varchar(100) NOT NULL,
            `request_data` json,
            `response_data` json,
            `response_time` decimal(10,4),
            `success` tinyint(1) DEFAULT 0,
            `error_code` varchar(50),
            `error_message` text,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`log_id`),
            KEY `service` (`service`),
            KEY `success` (`success`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // N11 categories cache
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_categories` (
            `category_id` int(11) NOT NULL,
            `parent_id` int(11) DEFAULT NULL,
            `name` varchar(255) NOT NULL,
            `full_name` varchar(500),
            `status` tinyint(1) DEFAULT 1,
            `last_updated` datetime NOT NULL,
            PRIMARY KEY (`category_id`),
            KEY `parent_id` (`parent_id`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        $this->log->write('N11 tabloları oluşturuldu/kontrol edildi');
    }
    
    /**
     * Varsayılan konfigürasyonları yükle
     */
    private function loadDefaultConfigs() {
        $defaults = [
            'n11.api_url' => $this->apiUrl,
            'n11.sandbox_url' => $this->sandboxUrl,
            'n11.timeout' => 60,
            'n11.retry_attempts' => 3,
            'n11.rate_limit_enabled' => true,
            'n11.auto_sync_products' => true,
            'n11.auto_sync_orders' => true,
            'n11.auto_sync_interval' => 600, // 10 dakika
            'n11.commission_rate' => 12.0,
            'n11.vat_rate' => 18.0,
            'n11.default_shipping_days' => 5,
            'n11.sandbox_mode' => false,
            'n11.soap_cache' => true
        ];
        
        foreach ($defaults as $key => $value) {
            $existing = $this->configHelper->get($key);
            if ($existing === null) {
                $this->configHelper->set($key, $value, [
                    'type' => 'marketplace',
                    'description' => 'N11 marketplace configuration'
                ]);
            }
        }
    }
    
    /**
     * API kimlik bilgilerini al
     */
    private function getApiCredentials($tenantId = null) {
        $config = $this->configHelper->getMarketplaceConfig('n11', $tenantId);
        
        return [
            'app_key' => $config['n11.app_key'] ?? null,
            'app_secret' => $config['n11.app_secret'] ?? null,
            'sandbox_mode' => $config['n11.sandbox_mode'] ?? false
        ];
    }
    
    /**
     * SOAP Client oluştur
     */
    private function createSoapClient($service, $tenantId = null) {
        $credentials = $this->getApiCredentials($tenantId);
        
        if (!$credentials['app_key'] || !$credentials['app_secret']) {
            throw new Exception('N11 API kimlik bilgileri eksik');
        }
        
        $baseUrl = $credentials['sandbox_mode'] ? $this->sandboxUrl : $this->apiUrl;
        $wsdlUrl = $baseUrl . $this->endpoints[$service];
        
        $options = [
            'soap_version' => SOAP_1_1,
            'exceptions' => true,
            'trace' => 1,
            'cache_wsdl' => $this->configHelper->get('n11.soap_cache', true) ? WSDL_CACHE_BOTH : WSDL_CACHE_NONE,
            'connection_timeout' => $this->configHelper->get('n11.timeout', 60),
            'user_agent' => 'MesChain-Sync/2.0'
        ];
        
        try {
            return new SoapClient($wsdlUrl, $options);
        } catch (SoapFault $e) {
            throw new Exception("SOAP Client oluşturulamadı: " . $e->getMessage());
        }
    }
    
    /**
     * API isteği yap
     */
    private function makeApiRequest($service, $method, $params = [], $tenantId = null) {
        $startTime = microtime(true);
        $credentials = $this->getApiCredentials($tenantId);
        
        // Rate limiting kontrolü
        $this->checkRateLimit($service);
        
        try {
            $client = $this->createSoapClient($service, $tenantId);
            
            // Authentication bilgilerini ekle
            $authParams = [
                'appKey' => $credentials['app_key'],
                'appSecret' => $credentials['app_secret']
            ];
            
            // Parametreleri birleştir
            $requestParams = array_merge(['auth' => $authParams], $params);
            
            // SOAP isteği yap
            $response = $client->__soapCall($method, [$requestParams]);
            
            $responseTime = microtime(true) - $startTime;
            
            // Performans metriği kaydet
            $this->monitoringHelper->recordMetric('n11_api_response_time', $responseTime, 'seconds', [
                'service' => $service,
                'method' => $method
            ]);
            
            // API log kaydet
            $this->logApiRequest($service, $method, $requestParams, $response, $responseTime, null, $tenantId);
            
            // Rate limit bilgisini güncelle
            $this->updateRateLimit($service);
            
            return $response;
            
        } catch (SoapFault $e) {
            $responseTime = microtime(true) - $startTime;
            
            // Hata logla
            $this->logApiRequest($service, $method, $requestParams ?? [], null, $responseTime, $e->getMessage(), $tenantId);
            
            throw new Exception("N11 API hatası: " . $e->getMessage());
        }
    }
    
    /**
     * Rate limit kontrolü
     */
    private function checkRateLimit($service) {
        if (!$this->configHelper->get('n11.rate_limit_enabled', true)) {
            return;
        }
        
        $serviceType = $this->getServiceType($service);
        $limits = $this->rateLimits[$serviceType] ?? $this->rateLimits['default'];
        
        $cacheKey = "n11_rate_limit_{$serviceType}";
        $cache = $this->registry->get('cache');
        
        if ($cache) {
            $currentCount = $cache->get($cacheKey) ?? 0;
            
            if ($currentCount >= $limits['requests']) {
                $this->eventHelper->trigger('api.rate_limit_exceeded', [
                    'marketplace' => 'n11',
                    'service' => $service,
                    'limit' => $limits['requests']
                ]);
                
                throw new Exception('Rate limit aşıldı. Lütfen bekleyin.');
            }
        }
    }
    
    /**
     * Rate limit sayacını güncelle
     */
    private function updateRateLimit($service) {
        $serviceType = $this->getServiceType($service);
        $limits = $this->rateLimits[$serviceType] ?? $this->rateLimits['default'];
        
        $cacheKey = "n11_rate_limit_{$serviceType}";
        $cache = $this->registry->get('cache');
        
        if ($cache) {
            $currentCount = $cache->get($cacheKey) ?? 0;
            $cache->set($cacheKey, $currentCount + 1, $limits['period']);
        }
    }
    
    /**
     * Service tipini belirle
     */
    private function getServiceType($service) {
        if (in_array($service, ['product'])) {
            return 'products';
        } elseif (in_array($service, ['order'])) {
            return 'orders';
        }
        return 'default';
    }
    
    /**
     * API log kaydet
     */
    private function logApiRequest($service, $method, $requestData, $responseData, $responseTime, $error, $tenantId) {
        // Response'dan error code çıkar
        $errorCode = null;
        if ($responseData && isset($responseData->result->errorCode)) {
            $errorCode = $responseData->result->errorCode;
        }
        
        $this->db->query("INSERT INTO `" . DB_PREFIX . "n11_api_logs` SET
            service = '" . $this->db->escape($service) . "',
            method = '" . $this->db->escape($method) . "',
            request_data = '" . $this->db->escape(json_encode($requestData)) . "',
            response_data = '" . $this->db->escape(substr(json_encode($responseData), 0, 65535)) . "',
            response_time = " . (float)$responseTime . ",
            success = " . ($error ? 0 : 1) . ",
            error_code = " . ($errorCode ? "'" . $this->db->escape($errorCode) . "'" : "NULL") . ",
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
            
            if (!$credentials['app_key'] || !$credentials['app_secret']) {
                return [
                    'status' => 'error',
                    'message' => 'API kimlik bilgileri eksik',
                    'response_time' => microtime(true) - $startTime
                ];
            }
            
            // Basit API testi - kategori listesi al
            $response = $this->makeApiRequest('category', 'GetTopLevelCategories');
            
            if (isset($response->result->status) && $response->result->status === 'success') {
                return [
                    'status' => 'healthy',
                    'message' => 'API bağlantısı başarılı',
                    'response_time' => microtime(true) - $startTime,
                    'category_count' => count($response->categoryList->category ?? [])
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => $response->result->errorMessage ?? 'Bilinmeyen hata',
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
     * Kategorileri senkronize et
     */
    public function syncCategories($tenantId = null) {
        $this->log->write("N11 kategori senkronizasyonu başlatıldı");
        
        try {
            $response = $this->makeApiRequest('category', 'GetTopLevelCategories', [], $tenantId);
            
            if (isset($response->result->status) && $response->result->status === 'success') {
                $this->processCategoriesRecursive($response->categoryList->category ?? [], null);
                
                $this->log->write("N11 kategori senkronizasyonu tamamlandı");
                
                return [
                    'success' => true,
                    'message' => 'Kategoriler başarıyla senkronize edildi'
                ];
            } else {
                throw new Exception($response->result->errorMessage ?? 'Kategori çekme hatası');
            }
            
        } catch (Exception $e) {
            $this->log->write("N11 kategori senkronizasyonu hatası: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Kategorileri recursive işle
     */
    private function processCategoriesRecursive($categories, $parentId = null) {
        foreach ($categories as $category) {
            // Kategoriyi kaydet
            $this->db->query("INSERT INTO `" . DB_PREFIX . "n11_categories` SET
                category_id = " . (int)$category->id . ",
                parent_id = " . ($parentId ? (int)$parentId : "NULL") . ",
                name = '" . $this->db->escape($category->name) . "',
                full_name = '" . $this->db->escape($category->name) . "',
                last_updated = NOW()
                ON DUPLICATE KEY UPDATE
                name = '" . $this->db->escape($category->name) . "',
                full_name = '" . $this->db->escape($category->name) . "',
                last_updated = NOW()
            ");
            
            // Alt kategorileri al ve işle
            if (isset($category->subCategoryList) && !empty($category->subCategoryList)) {
                try {
                    $subResponse = $this->makeApiRequest('category', 'GetSubCategories', [
                        'categoryId' => $category->id
                    ]);
                    
                    if (isset($subResponse->result->status) && $subResponse->result->status === 'success') {
                        $this->processCategoriesRecursive($subResponse->categoryList->category ?? [], $category->id);
                    }
                } catch (Exception $e) {
                    $this->log->write("Alt kategori çekme hatası: " . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * Ürün senkronizasyonu
     */
    public function syncProducts($productIds = [], $tenantId = null) {
        $this->log->write("N11 ürün senkronizasyonu başlatıldı");
        
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
                        'marketplace' => 'n11',
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
     * Tek ürün senkronizasyonu
     */
    private function syncSingleProduct($product, $tenantId = null) {
        // Mevcut mapping kontrol et
        $existing = $this->db->query("SELECT * FROM `" . DB_PREFIX . "n11_products` 
            WHERE opencart_product_id = " . (int)$product['product_id'] . "
            AND tenant_id = " . ($tenantId ? (int)$tenantId : "NULL"));
        
        // N11 ürün verisi hazırla
        $n11Product = $this->prepareProductData($product);
        
        if ($existing->num_rows) {
            // Güncelle
            $mapping = $existing->row;
            $response = $this->makeApiRequest('product', 'UpdateProductBasic', [
                'productSellingRequest' => $n11Product
            ], $tenantId);
            
        } else {
            // Yeni ürün ekle
            $response = $this->makeApiRequest('product', 'SaveProduct', [
                'productRequest' => $n11Product
            ], $tenantId);
            
            // Mapping kaydet
            if (isset($response->result->status) && $response->result->status === 'success') {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "n11_products` SET
                    opencart_product_id = " . (int)$product['product_id'] . ",
                    sku = '" . $this->db->escape($n11Product['productSellings']['productSelling']['sellerCode']) . "',
                    last_sync = NOW(),
                    sync_status = 'synced',
                    tenant_id = " . ($tenantId ? (int)$tenantId : "NULL") . ",
                    created_at = NOW(),
                    updated_at = NOW()
                ");
            }
        }
        
        return $response;
    }
    
    /**
     * Ürün verisini N11 formatına çevir
     */
    private function prepareProductData($product) {
        // SKU oluştur
        $sku = $this->generateSku($product);
        
        // Kategori mapping
        $categoryId = $this->mapCategory($product['category_id']);
        
        // Fiyat hesapla
        $finalPrice = $this->calculateFinalPrice($product['price']);
        
        // Resimler hazırla
        $images = $this->prepareImages($product['product_id']);
        
        // Özellikler hazırla
        $attributes = $this->prepareAttributes($product['product_id']);
        
        return [
            'product' => [
                'title' => $this->sanitizeTitle($product['name']),
                'subtitle' => '',
                'description' => $this->sanitizeDescription($product['description']),
                'category' => ['id' => $categoryId],
                'price' => $finalPrice,
                'currencyType' => 'TL',
                'images' => $images,
                'approvalStatus' => 'WAITING_APPROVAL',
                'preparingDay' => $this->configHelper->get('n11.default_shipping_days', 5),
                'attributes' => $attributes
            ],
            'productSellings' => [
                'productSelling' => [
                    'sellerCode' => $sku,
                    'attributes' => [],
                    'quantity' => max(0, (int)$product['quantity']),
                    'sellingPrice' => $finalPrice,
                    'currencyType' => 'TL',
                    'shippingOptions' => [
                        'shippingOption' => [
                            'shippingMethod' => 'normal'
                        ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Sipariş senkronizasyonu
     */
    public function syncOrders($tenantId = null, $period = 'LAST_MONTH') {
        $this->log->write("N11 sipariş senkronizasyonu başlatıldı");
        
        try {
            $response = $this->makeApiRequest('order', 'OrderList', [
                'searchData' => [
                    'period' => $period,
                    'sortForUpdateDate' => true
                ],
                'pagingData' => [
                    'currentPage' => 0,
                    'pageSize' => 100
                ]
            ], $tenantId);
            
            $syncedCount = 0;
            $errorCount = 0;
            
            if (isset($response->result->status) && $response->result->status === 'success') {
                foreach ($response->orderList->order ?? [] as $n11Order) {
                    try {
                        $this->processN11Order($n11Order, $tenantId);
                        $syncedCount++;
                        
                        // Event tetikle
                        $this->eventHelper->trigger('order.synced', [
                            'marketplace' => 'n11',
                            'order_number' => $n11Order->orderNumber,
                            'amount' => $n11Order->totalAmount
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
     * N11 siparişini işle
     */
    private function processN11Order($n11Order, $tenantId = null) {
        // Mevcut sipariş kontrolü
        $existing = $this->db->query("SELECT * FROM `" . DB_PREFIX . "n11_orders` 
            WHERE order_number = '" . $this->db->escape($n11Order->orderNumber) . "'");
        
        if ($existing->num_rows) {
            // Güncelle
            $this->updateN11Order($existing->row, $n11Order);
        } else {
            // Yeni sipariş oluştur
            $this->createN11Order($n11Order, $tenantId);
        }
    }
    
    /**
     * Helper metodlar
     */
    private function generateSku($product) {
        return 'N11-' . str_pad($product['product_id'], 8, '0', STR_PAD_LEFT);
    }
    
    private function sanitizeTitle($title) {
        return substr(strip_tags($title), 0, 200);
    }
    
    private function sanitizeDescription($description) {
        return substr(strip_tags($description), 0, 5000);
    }
    
    private function calculateFinalPrice($price) {
        $commission = $this->configHelper->get('n11.commission_rate', 12.0);
        return round($price * (1 + $commission / 100), 2);
    }
    
    private function mapCategory($categoryId) {
        // Kategori mapping logic burada
        return 1000; // Placeholder
    }
    
    private function prepareImages($productId) {
        // Ürün resimlerini hazırla
        return []; // Placeholder
    }
    
    private function prepareAttributes($productId) {
        // Ürün özelliklerini hazırla
        return []; // Placeholder
    }
    
    private function createN11Order($n11Order, $tenantId = null) {
        // Yeni N11 siparişi oluştur
        // Implementation burada
    }
    
    private function updateN11Order($existing, $n11Order) {
        // Mevcut siparişi güncelle
        // Implementation burada
    }
}
?> 