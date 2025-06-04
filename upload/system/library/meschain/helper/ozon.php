<?php
/**
 * MeschainOzonHelper - Modern Ozon API Entegrasyonu
 * 
 * Event-driven architecture, health monitoring ve webhook desteği ile
 * gelişmiş Ozon marketplace entegrasyonu (Rusya pazaryeri)
 * 
 * @author MesChain Development Team
 * @version 2.0.0
 * @since 2024-01-21
 */

class MeschainOzonHelper {
    
    private $registry;
    private $db;
    private $log;
    private $configHelper;
    private $eventHelper;
    private $monitoringHelper;
    
    // API URLs
    private $apiUrl = 'https://api-seller.ozon.ru';
    private $sandboxUrl = 'https://api-seller.ozon.ru'; // Ozon aynı URL kullanır
    
    // API endpoints
    private $endpoints = [
        'categories' => '/v2/category/tree',
        'attributes' => '/v3/category/attribute',
        'products' => '/v2/product/info',
        'product_create' => '/v2/product/import',
        'product_update' => '/v1/product/update',
        'orders' => '/v3/posting/fbs/list',
        'order_details' => '/v2/posting/fbs/get',
        'shipments' => '/v2/posting/fbs/ship',
        'analytics' => '/v1/analytics/data',
        'finance' => '/v3/finance/transaction/list',
        'warehouse' => '/v1/warehouse/list',
        'stocks' => '/v3/product/info/stocks'
    ];
    
    // Rate limiting
    private $rateLimits = [
        'default' => ['requests' => 1000, 'period' => 60], // 1000 req/min
        'products' => ['requests' => 500, 'period' => 60],  // 500 req/min
        'orders' => ['requests' => 1000, 'period' => 60]    // 1000 req/min
    ];
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->log = new Log('meschain_ozon.log');
        
        // Helper'ları yükle
        require_once(DIR_SYSTEM . 'library/meschain/helper/config.php');
        require_once(DIR_SYSTEM . 'library/meschain/helper/event.php');
        require_once(DIR_SYSTEM . 'library/meschain/helper/monitoring.php');
        
        $this->configHelper = new MeschainConfigHelper($registry);
        $this->eventHelper = new MeschainEventHelper($registry);
        $this->monitoringHelper = new MeschainMonitoringHelper($registry);
        
        $this->createOzonTables();
        $this->loadDefaultConfigs();
    }
    
    /**
     * Ozon tablolarını oluştur
     */
    private function createOzonTables() {
        // Ozon products mapping
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_products` (
            `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_product_id` int(11) NOT NULL,
            `ozon_product_id` bigint(20),
            `offer_id` varchar(100) NOT NULL,
            `fbo_sku` varchar(100),
            `fbs_sku` varchar(100),
            `barcode` varchar(100),
            `approved` tinyint(1) DEFAULT 0,
            `status` varchar(50) DEFAULT 'moderating',
            `state` varchar(50) DEFAULT 'processing',
            `category_id` int(11),
            `type_id` int(11),
            `last_sync` datetime DEFAULT NULL,
            `sync_status` enum('synced','pending','error') DEFAULT 'pending',
            `error_message` text,
            `errors` json,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`mapping_id`),
            UNIQUE KEY `offer_id_unique` (`offer_id`),
            KEY `opencart_product_id` (`opencart_product_id`),
            KEY `ozon_product_id` (`ozon_product_id`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Ozon orders
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_orders` (
            `ozon_order_id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_order_id` int(11) DEFAULT NULL,
            `posting_number` varchar(100) NOT NULL,
            `order_id` bigint(20) NOT NULL,
            `order_number` varchar(100) NOT NULL,
            `status` varchar(50) NOT NULL,
            `substatus` varchar(50),
            `delivery_method_id` bigint(20),
            `delivery_method_name` varchar(255),
            `warehouse_id` bigint(20),
            `warehouse_name` varchar(255),
            `tracking_number` varchar(100),
            `tpl_integration_type` varchar(50),
            `in_process_at` datetime,
            `shipment_date` datetime,
            `delivering_date` datetime,
            `cancellation_date` datetime,
            `cancellation_reason` varchar(255),
            `created_at_ozon` datetime NOT NULL,
            `updated_at_ozon` datetime,
            `products` json,
            `analytics_data` json,
            `financial_data` json,
            `customer` json,
            `addressee` json,
            `barcodes` json,
            `requirements` json,
            `sync_status` enum('pending','synced','error') DEFAULT 'pending',
            `error_message` text,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`ozon_order_id`),
            UNIQUE KEY `posting_number_unique` (`posting_number`),
            KEY `opencart_order_id` (`opencart_order_id`),
            KEY `order_id` (`order_id`),
            KEY `status` (`status`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Ozon API logs
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_api_logs` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `endpoint` varchar(255) NOT NULL,
            `method` varchar(10) NOT NULL,
            `request_data` json,
            `response_data` json,
            `http_status` int(11),
            `response_time` decimal(10,4),
            `success` tinyint(1) DEFAULT 0,
            `error_message` text,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`log_id`),
            KEY `endpoint` (`endpoint`),
            KEY `success` (`success`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Ozon categories cache
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_categories` (
            `category_id` int(11) NOT NULL,
            `parent_id` int(11) DEFAULT NULL,
            `title` varchar(255) NOT NULL,
            `type_id` int(11),
            `type_name` varchar(255),
            `disabled` tinyint(1) DEFAULT 0,
            `last_updated` datetime NOT NULL,
            PRIMARY KEY (`category_id`),
            KEY `parent_id` (`parent_id`),
            KEY `type_id` (`type_id`),
            KEY `disabled` (`disabled`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Ozon product types
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_product_types` (
            `type_id` int(11) NOT NULL,
            `title` varchar(255) NOT NULL,
            `category_id` int(11) NOT NULL,
            `last_updated` datetime NOT NULL,
            PRIMARY KEY (`type_id`),
            KEY `category_id` (`category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        $this->log->write('Ozon tabloları oluşturuldu/kontrol edildi');
    }
    
    /**
     * Varsayılan konfigürasyonları yükle
     */
    private function loadDefaultConfigs() {
        $defaults = [
            'ozon.api_url' => $this->apiUrl,
            'ozon.timeout' => 30,
            'ozon.retry_attempts' => 3,
            'ozon.rate_limit_enabled' => true,
            'ozon.auto_sync_products' => true,
            'ozon.auto_sync_orders' => true,
            'ozon.auto_sync_interval' => 600, // 10 dakika
            'ozon.commission_rate' => 5.0, // Düşük komisyon
            'ozon.vat_rate' => 20.0, // Rusya VAT
            'ozon.default_shipping_days' => 7,
            'ozon.warehouse_id' => null,
            'ozon.currency' => 'RUB',
            'ozon.auto_accept_orders' => false
        ];
        
        foreach ($defaults as $key => $value) {
            $existing = $this->configHelper->get($key);
            if ($existing === null) {
                $this->configHelper->set($key, $value, [
                    'type' => 'marketplace',
                    'description' => 'Ozon marketplace configuration'
                ]);
            }
        }
    }
    
    /**
     * API kimlik bilgilerini al
     */
    private function getApiCredentials($tenantId = null) {
        $config = $this->configHelper->getMarketplaceConfig('ozon', $tenantId);
        
        return [
            'client_id' => $config['ozon.client_id'] ?? null,
            'api_key' => $config['ozon.api_key'] ?? null,
            'warehouse_id' => $config['ozon.warehouse_id'] ?? null
        ];
    }
    
    /**
     * API isteği yap
     */
    private function makeApiRequest($endpoint, $method = 'POST', $data = null, $tenantId = null) {
        $startTime = microtime(true);
        $credentials = $this->getApiCredentials($tenantId);
        
        if (!$credentials['client_id'] || !$credentials['api_key']) {
            throw new Exception('Ozon API kimlik bilgileri eksik');
        }
        
        // Rate limiting kontrolü
        $this->checkRateLimit($endpoint);
        
        $url = $this->apiUrl . $endpoint;
        
        // Headers
        $headers = [
            'Client-Id: ' . $credentials['client_id'],
            'Api-Key: ' . $credentials['api_key'],
            'Content-Type: application/json',
            'User-Agent: MesChain-Sync/2.0'
        ];
        
        // cURL isteği
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->configHelper->get('ozon.timeout', 30),
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
            case 'GET':
                if ($data) {
                    $url .= '?' . http_build_query($data);
                    curl_setopt($ch, CURLOPT_URL, $url);
                }
                break;
        }
        
        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseTime = microtime(true) - $startTime;
        $error = curl_error($ch);
        curl_close($ch);
        
        // Performans metriği kaydet
        $this->monitoringHelper->recordMetric('ozon_api_response_time', $responseTime, 'seconds', [
            'endpoint' => $endpoint,
            'method' => $method
        ]);
        
        // API log kaydet
        $this->logApiRequest($endpoint, $method, $data, $response, $httpStatus, $responseTime, $error, $tenantId);
        
        if ($error) {
            throw new Exception("cURL hatası: {$error}");
        }
        
        if ($httpStatus >= 400) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['message'] ?? "HTTP {$httpStatus} hatası";
            throw new Exception("Ozon API hatası: {$errorMessage}");
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
        if (!$this->configHelper->get('ozon.rate_limit_enabled', true)) {
            return;
        }
        
        $endpointType = $this->getEndpointType($endpoint);
        $limits = $this->rateLimits[$endpointType] ?? $this->rateLimits['default'];
        
        $cacheKey = "ozon_rate_limit_{$endpointType}";
        $cache = $this->registry->get('cache');
        
        if ($cache) {
            $currentCount = $cache->get($cacheKey) ?? 0;
            
            if ($currentCount >= $limits['requests']) {
                $this->eventHelper->trigger('api.rate_limit_exceeded', [
                    'marketplace' => 'ozon',
                    'endpoint' => $endpoint,
                    'limit' => $limits['requests']
                ]);
                
                throw new Exception('Rate limit aşıldı. Lütfen bekleyin.');
            }
        }
    }
    
    /**
     * Rate limit sayacını güncelle
     */
    private function updateRateLimit($endpoint) {
        $endpointType = $this->getEndpointType($endpoint);
        $limits = $this->rateLimits[$endpointType] ?? $this->rateLimits['default'];
        
        $cacheKey = "ozon_rate_limit_{$endpointType}";
        $cache = $this->registry->get('cache');
        
        if ($cache) {
            $currentCount = $cache->get($cacheKey) ?? 0;
            $cache->set($cacheKey, $currentCount + 1, $limits['period']);
        }
    }
    
    /**
     * Endpoint tipini belirle
     */
    private function getEndpointType($endpoint) {
        if (strpos($endpoint, '/product') !== false) {
            return 'products';
        } elseif (strpos($endpoint, '/posting') !== false) {
            return 'orders';
        }
        return 'default';
    }
    
    /**
     * API log kaydet
     */
    private function logApiRequest($endpoint, $method, $requestData, $responseData, $httpStatus, $responseTime, $error, $tenantId) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "ozon_api_logs` SET
            endpoint = '" . $this->db->escape($endpoint) . "',
            method = '" . $this->db->escape($method) . "',
            request_data = '" . $this->db->escape(json_encode($requestData)) . "',
            response_data = '" . $this->db->escape(substr($responseData, 0, 65535)) . "',
            http_status = " . (int)$httpStatus . ",
            response_time = " . (float)$responseTime . ",
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
            
            if (!$credentials['client_id'] || !$credentials['api_key']) {
                return [
                    'status' => 'error',
                    'message' => 'API kimlik bilgileri eksik',
                    'response_time' => microtime(true) - $startTime
                ];
            }
            
            // Basit API testi - kategori listesi al
            $response = $this->makeApiRequest($this->endpoints['categories'], 'POST', [
                'language' => 'DEFAULT'
            ]);
            
            if (isset($response['result'])) {
                return [
                    'status' => 'healthy',
                    'message' => 'API bağlantısı başarılı',
                    'response_time' => microtime(true) - $startTime,
                    'category_count' => count($response['result'])
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
     * Kategorileri senkronize et
     */
    public function syncCategories($tenantId = null) {
        $this->log->write("Ozon kategori senkronizasyonu başlatıldı");
        
        try {
            $response = $this->makeApiRequest($this->endpoints['categories'], 'POST', [
                'language' => 'DEFAULT'
            ], $tenantId);
            
            if (isset($response['result']) && is_array($response['result'])) {
                $this->processCategoriesRecursive($response['result'], null);
                
                $this->log->write("Ozon kategori senkronizasyonu tamamlandı");
                
                return [
                    'success' => true,
                    'message' => 'Kategoriler başarıyla senkronize edildi'
                ];
            } else {
                throw new Exception('Kategori verisi alınamadı');
            }
            
        } catch (Exception $e) {
            $this->log->write("Ozon kategori senkronizasyonu hatası: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Kategorileri recursive işle
     */
    private function processCategoriesRecursive($categories, $parentId = null) {
        foreach ($categories as $category) {
            // Kategoriyi kaydet
            $this->db->query("INSERT INTO `" . DB_PREFIX . "ozon_categories` SET
                category_id = " . (int)$category['category_id'] . ",
                parent_id = " . ($parentId ? (int)$parentId : "NULL") . ",
                title = '" . $this->db->escape($category['title']) . "',
                type_id = " . (int)($category['type_id'] ?? 0) . ",
                type_name = '" . $this->db->escape($category['type_name'] ?? '') . "',
                disabled = " . (int)($category['disabled'] ?? 0) . ",
                last_updated = NOW()
                ON DUPLICATE KEY UPDATE
                title = '" . $this->db->escape($category['title']) . "',
                type_id = " . (int)($category['type_id'] ?? 0) . ",
                type_name = '" . $this->db->escape($category['type_name'] ?? '') . "',
                disabled = " . (int)($category['disabled'] ?? 0) . ",
                last_updated = NOW()
            ");
            
            // Alt kategorileri işle
            if (isset($category['children']) && !empty($category['children'])) {
                $this->processCategoriesRecursive($category['children'], $category['category_id']);
            }
        }
    }
    
    /**
     * Ürün senkronizasyonu
     */
    public function syncProducts($productIds = [], $tenantId = null) {
        $this->log->write("Ozon ürün senkronizasyonu başlatıldı");
        
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
            
            // Batch olarak ürünleri işle (Ozon batch import destekler)
            $batchSize = 100;
            $products = array_chunk($query->rows, $batchSize);
            
            foreach ($products as $batch) {
                try {
                    $this->syncProductBatch($batch, $tenantId);
                    $syncedCount += count($batch);
                    
                } catch (Exception $e) {
                    $errorCount += count($batch);
                    $errors[] = "Batch hatası: " . $e->getMessage();
                    $this->log->write("Ürün batch sync hatası: " . $e->getMessage());
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
     * Ürün batch senkronizasyonu
     */
    private function syncProductBatch($products, $tenantId = null) {
        $ozonItems = [];
        
        foreach ($products as $product) {
            $ozonProduct = $this->prepareProductData($product);
            $ozonItems[] = $ozonProduct;
        }
        
        // Batch import API çağrısı
        $response = $this->makeApiRequest($this->endpoints['product_create'], 'POST', [
            'items' => $ozonItems
        ], $tenantId);
        
        // Sonuçları işle
        if (isset($response['result']['task_id'])) {
            $this->log->write("Ozon batch import başlatıldı: Task ID " . $response['result']['task_id']);
            
            // Her ürün için mapping kaydet
            foreach ($products as $index => $product) {
                $offerId = $this->generateOfferId($product);
                
                $this->db->query("INSERT INTO `" . DB_PREFIX . "ozon_products` SET
                    opencart_product_id = " . (int)$product['product_id'] . ",
                    offer_id = '" . $this->db->escape($offerId) . "',
                    sync_status = 'pending',
                    tenant_id = " . ($tenantId ? (int)$tenantId : "NULL") . ",
                    created_at = NOW(),
                    updated_at = NOW()
                    ON DUPLICATE KEY UPDATE
                    sync_status = 'pending',
                    updated_at = NOW()
                ");
            }
        }
        
        return $response;
    }
    
    /**
     * Ürün verisini Ozon formatına çevir
     */
    private function prepareProductData($product) {
        // Offer ID oluştur
        $offerId = $this->generateOfferId($product);
        
        // Barcode oluştur
        $barcode = $this->generateBarcode($product);
        
        // Kategori ve tip mapping
        $categoryId = $this->mapCategory($product['category_id']);
        $typeId = $this->getProductType($categoryId);
        
        // Fiyat hesapla (RUB)
        $finalPrice = $this->calculateFinalPrice($product['price']);
        
        // Resimler hazırla
        $images = $this->prepareImages($product['product_id']);
        
        // Özellikler hazırla
        $attributes = $this->prepareAttributes($product['product_id'], $typeId);
        
        return [
            'barcode' => $barcode,
            'category_id' => $categoryId,
            'name' => $this->sanitizeTitle($product['name']),
            'offer_id' => $offerId,
            'price' => $finalPrice,
            'old_price' => $finalPrice,
            'premium_price' => $finalPrice,
            'vat' => '0.2', // 20% VAT
            'height' => 10, // cm
            'depth' => 10,  // cm
            'width' => 10,  // cm
            'dimension_unit' => 'cm',
            'weight' => 500, // gramlar
            'weight_unit' => 'g',
            'images' => $images,
            'image_group_id' => '',
            'primary_image' => $images[0] ?? '',
            'description_category_id' => $categoryId,
            'type_id' => $typeId,
            'attributes' => $attributes
        ];
    }
    
    /**
     * Sipariş senkronizasyonu
     */
    public function syncOrders($tenantId = null, $since = null, $to = null) {
        $this->log->write("Ozon sipariş senkronizasyonu başlatıldı");
        
        try {
            $filter = [
                'limit' => 1000,
                'offset' => 0,
                'dir' => 'ASC',
                'filter' => [
                    'since' => $since ?: date('Y-m-d\TH:i:s.000Z', strtotime('-7 days')),
                    'to' => $to ?: date('Y-m-d\TH:i:s.000Z')
                ]
            ];
            
            $response = $this->makeApiRequest($this->endpoints['orders'], 'POST', $filter, $tenantId);
            
            $syncedCount = 0;
            $errorCount = 0;
            
            if (isset($response['result']['postings']) && is_array($response['result']['postings'])) {
                foreach ($response['result']['postings'] as $ozonOrder) {
                    try {
                        $this->processOzonOrder($ozonOrder, $tenantId);
                        $syncedCount++;
                        
                        // Event tetikle
                        $this->eventHelper->trigger('order.synced', [
                            'marketplace' => 'ozon',
                            'posting_number' => $ozonOrder['posting_number'],
                            'order_id' => $ozonOrder['order_id']
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
     * Ozon siparişini işle
     */
    private function processOzonOrder($ozonOrder, $tenantId = null) {
        // Mevcut sipariş kontrolü
        $existing = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ozon_orders` 
            WHERE posting_number = '" . $this->db->escape($ozonOrder['posting_number']) . "'");
        
        if ($existing->num_rows) {
            // Güncelle
            $this->updateOzonOrder($existing->row, $ozonOrder);
        } else {
            // Yeni sipariş oluştur
            $this->createOzonOrder($ozonOrder, $tenantId);
        }
    }
    
    /**
     * Helper metodlar
     */
    private function generateOfferId($product) {
        return 'OZ-' . str_pad($product['product_id'], 8, '0', STR_PAD_LEFT);
    }
    
    private function generateBarcode($product) {
        return '2000000' . str_pad($product['product_id'], 6, '0', STR_PAD_LEFT);
    }
    
    private function sanitizeTitle($title) {
        return substr(strip_tags($title), 0, 500);
    }
    
    private function calculateFinalPrice($price) {
        // USD'den RUB'a çevrim (örnek rate: 75)
        $exchangeRate = $this->configHelper->get('ozon.exchange_rate', 75);
        $commission = $this->configHelper->get('ozon.commission_rate', 5.0);
        
        $rubPrice = $price * $exchangeRate;
        return round($rubPrice * (1 + $commission / 100), 2);
    }
    
    private function mapCategory($categoryId) {
        // Kategori mapping logic burada
        return 17027016; // Elektronik kategorisi (örnek)
    }
    
    private function getProductType($categoryId) {
        // Ürün tipi mapping
        return 970629; // Elektronik ürün tipi (örnek)
    }
    
    private function prepareImages($productId) {
        // Ürün resimlerini hazırla
        return []; // Placeholder
    }
    
    private function prepareAttributes($productId, $typeId) {
        // Ürün özelliklerini hazırla
        return []; // Placeholder
    }
    
    private function createOzonOrder($ozonOrder, $tenantId = null) {
        // Yeni Ozon siparişi oluştur
        $this->db->query("INSERT INTO `" . DB_PREFIX . "ozon_orders` SET
            posting_number = '" . $this->db->escape($ozonOrder['posting_number']) . "',
            order_id = " . (int)$ozonOrder['order_id'] . ",
            order_number = '" . $this->db->escape($ozonOrder['order_number']) . "',
            status = '" . $this->db->escape($ozonOrder['status']) . "',
            substatus = '" . $this->db->escape($ozonOrder['substatus'] ?? '') . "',
            delivery_method_id = " . (int)($ozonOrder['delivery_method']['id'] ?? 0) . ",
            delivery_method_name = '" . $this->db->escape($ozonOrder['delivery_method']['name'] ?? '') . "',
            warehouse_id = " . (int)($ozonOrder['delivery_method']['warehouse_id'] ?? 0) . ",
            warehouse_name = '" . $this->db->escape($ozonOrder['delivery_method']['warehouse'] ?? '') . "',
            tracking_number = '" . $this->db->escape($ozonOrder['tracking_number'] ?? '') . "',
            tpl_integration_type = '" . $this->db->escape($ozonOrder['tpl_integration_type'] ?? '') . "',
            in_process_at = " . ($ozonOrder['in_process_at'] ? "'" . date('Y-m-d H:i:s', strtotime($ozonOrder['in_process_at'])) . "'" : "NULL") . ",
            shipment_date = " . ($ozonOrder['shipment_date'] ? "'" . date('Y-m-d H:i:s', strtotime($ozonOrder['shipment_date'])) . "'" : "NULL") . ",
            delivering_date = " . ($ozonOrder['delivering_date'] ? "'" . date('Y-m-d H:i:s', strtotime($ozonOrder['delivering_date'])) . "'" : "NULL") . ",
            created_at_ozon = '" . date('Y-m-d H:i:s', strtotime($ozonOrder['created_at'])) . "',
            updated_at_ozon = " . ($ozonOrder['updated_at'] ? "'" . date('Y-m-d H:i:s', strtotime($ozonOrder['updated_at'])) . "'" : "NULL") . ",
            products = '" . $this->db->escape(json_encode($ozonOrder['products'] ?? [])) . "',
            analytics_data = '" . $this->db->escape(json_encode($ozonOrder['analytics_data'] ?? [])) . "',
            financial_data = '" . $this->db->escape(json_encode($ozonOrder['financial_data'] ?? [])) . "',
            customer = '" . $this->db->escape(json_encode($ozonOrder['customer'] ?? [])) . "',
            addressee = '" . $this->db->escape(json_encode($ozonOrder['addressee'] ?? [])) . "',
            barcodes = '" . $this->db->escape(json_encode($ozonOrder['barcodes'] ?? [])) . "',
            requirements = '" . $this->db->escape(json_encode($ozonOrder['requirements'] ?? [])) . "',
            sync_status = 'synced',
            tenant_id = " . ($tenantId ? (int)$tenantId : "NULL") . ",
            created_at = NOW(),
            updated_at = NOW()
        ");
    }
    
    private function updateOzonOrder($existing, $ozonOrder) {
        // Mevcut siparişi güncelle
        $this->db->query("UPDATE `" . DB_PREFIX . "ozon_orders` SET
            status = '" . $this->db->escape($ozonOrder['status']) . "',
            substatus = '" . $this->db->escape($ozonOrder['substatus'] ?? '') . "',
            tracking_number = '" . $this->db->escape($ozonOrder['tracking_number'] ?? '') . "',
            shipment_date = " . ($ozonOrder['shipment_date'] ? "'" . date('Y-m-d H:i:s', strtotime($ozonOrder['shipment_date'])) . "'" : "NULL") . ",
            delivering_date = " . ($ozonOrder['delivering_date'] ? "'" . date('Y-m-d H:i:s', strtotime($ozonOrder['delivering_date'])) . "'" : "NULL") . ",
            updated_at_ozon = " . ($ozonOrder['updated_at'] ? "'" . date('Y-m-d H:i:s', strtotime($ozonOrder['updated_at'])) . "'" : "NOW()") . ",
            updated_at = NOW()
            WHERE ozon_order_id = " . (int)$existing['ozon_order_id']);
    }
}
?> 