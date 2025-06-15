<?php
/**
 * MeschainTrendyolHelper - Modern Trendyol API Entegrasyonu
 * 
 * Event-driven architecture, health monitoring ve webhook desteği ile
 * gelişmiş Trendyol marketplace entegrasyonu
 * 
 * @author MesChain Development Team
 * @version 2.0.0
 * @since 2024-01-21
 */

class MeschainTrendyolHelper {
    
    private $registry;
    private $db;
    private $log;
    private $configHelper;
    private $eventHelper;
    private $monitoringHelper;
    
    // API URLs
    private $apiUrl = 'https://api.trendyol.com/sapigw';
    private $sandboxUrl = 'https://stageapi.trendyol.com/sapigw';
    
    // API endpoints
    private $endpoints = [
        'suppliers' => '/suppliers/{supplierId}',
        'brands' => '/brands',
        'categories' => '/product-categories',
        'products' => '/suppliers/{supplierId}/products',
        'product_batch' => '/suppliers/{supplierId}/products/batch-requests',
        'orders' => '/suppliers/{supplierId}/orders',
        'shipments' => '/suppliers/{supplierId}/shipments',
        'claims' => '/suppliers/{supplierId}/claims',
        'settlements' => '/suppliers/{supplierId}/settlements',
        'commission' => '/suppliers/{supplierId}/commission-invoice',
        'questions' => '/suppliers/{supplierId}/questions',
        'reviews' => '/suppliers/{supplierId}/reviews'
    ];
    
    // Rate limiting
    private $rateLimits = [
        'default' => ['requests' => 100, 'period' => 60], // 100 req/min
        'products' => ['requests' => 50, 'period' => 60],  // 50 req/min
        'orders' => ['requests' => 200, 'period' => 60]    // 200 req/min
    ];
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->log = new Log('meschain_trendyol.log');
        
        // Helper'ları yükle
        require_once(DIR_SYSTEM . 'library/meschain/helper/config.php');
        require_once(DIR_SYSTEM . 'library/meschain/helper/event.php');
        require_once(DIR_SYSTEM . 'library/meschain/helper/monitoring.php');
        
        $this->configHelper = new MeschainConfigHelper($registry);
        $this->eventHelper = new MeschainEventHelper($registry);
        $this->monitoringHelper = new MeschainMonitoringHelper($registry);
        
        $this->createTrendyolTables();
        $this->loadDefaultConfigs();
    }
    
    /**
     * Trendyol tablolarını oluştur
     */
    private function createTrendyolTables() {
        // Trendyol products mapping
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_products` (
            `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_product_id` int(11) NOT NULL,
            `trendyol_product_id` varchar(100),
            `barcode` varchar(100) NOT NULL,
            `content_id` varchar(100),
            `approved` tinyint(1) DEFAULT 0,
            `status` enum('pending','approved','rejected','passive') DEFAULT 'pending',
            `category_id` int(11),
            `brand_id` int(11),
            `last_sync` datetime DEFAULT NULL,
            `sync_status` enum('synced','pending','error') DEFAULT 'pending',
            `error_message` text,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`mapping_id`),
            UNIQUE KEY `barcode_unique` (`barcode`),
            KEY `opencart_product_id` (`opencart_product_id`),
            KEY `trendyol_product_id` (`trendyol_product_id`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Trendyol orders
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_orders` (
            `trendyol_order_id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_order_id` int(11) DEFAULT NULL,
            `shipment_package_id` varchar(100) NOT NULL,
            `order_number` varchar(100) NOT NULL,
            `gross_amount` decimal(15,4) NOT NULL,
            `total_discount` decimal(15,4) DEFAULT 0,
            `total_tax` decimal(15,4) DEFAULT 0,
            `delivery_type` varchar(50),
            `time_slot_id` varchar(50),
            `estimated_delivery` datetime DEFAULT NULL,
            `status` varchar(50) NOT NULL,
            `order_date` datetime NOT NULL,
            `cargo_tracking_number` varchar(100),
            `cargo_provider_name` varchar(100),
            `lines` json,
            `customer_info` json,
            `invoice_address` json,
            `delivery_address` json,
            `sync_status` enum('pending','synced','error') DEFAULT 'pending',
            `error_message` text,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`trendyol_order_id`),
            UNIQUE KEY `order_number_unique` (`order_number`),
            KEY `opencart_order_id` (`opencart_order_id`),
            KEY `status` (`status`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Trendyol webhooks
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_webhooks` (
            `webhook_id` int(11) NOT NULL AUTO_INCREMENT,
            `event_type` varchar(100) NOT NULL,
            `event_data` json NOT NULL,
            `processed` tinyint(1) DEFAULT 0,
            `processed_at` datetime DEFAULT NULL,
            `error_message` text,
            `received_at` datetime NOT NULL,
            PRIMARY KEY (`webhook_id`),
            KEY `event_type` (`event_type`),
            KEY `processed` (`processed`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Trendyol API logs
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_api_logs` (
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
        
        $this->log->write('Trendyol tabloları oluşturuldu/kontrol edildi');
    }
    
    /**
     * Varsayılan konfigürasyonları yükle
     */
    private function loadDefaultConfigs() {
        $defaults = [
            'trendyol.api_url' => $this->apiUrl,
            'trendyol.sandbox_url' => $this->sandboxUrl,
            'trendyol.timeout' => 30,
            'trendyol.retry_attempts' => 3,
            'trendyol.rate_limit_enabled' => true,
            'trendyol.webhook_enabled' => true,
            'trendyol.auto_sync_products' => true,
            'trendyol.auto_sync_orders' => true,
            'trendyol.auto_sync_interval' => 300, // 5 dakika
            'trendyol.commission_rate' => 15.0,
            'trendyol.vat_rate' => 18.0,
            'trendyol.default_shipping_days' => 3,
            'trendyol.sandbox_mode' => false
        ];
        
        foreach ($defaults as $key => $value) {
            $existing = $this->configHelper->get($key);
            if ($existing === null) {
                $this->configHelper->set($key, $value, [
                    'type' => 'marketplace',
                    'description' => 'Trendyol marketplace configuration'
                ]);
            }
        }
    }
    
    /**
     * API kimlik bilgilerini al
     */
    private function getApiCredentials($tenantId = null) {
        $config = $this->configHelper->getMarketplaceConfig('trendyol', $tenantId);
        
        return [
            'api_key' => $config['trendyol.api_key'] ?? null,
            'api_secret' => $config['trendyol.api_secret'] ?? null,
            'supplier_id' => $config['trendyol.supplier_id'] ?? null,
            'sandbox_mode' => $config['trendyol.sandbox_mode'] ?? false
        ];
    }
    
    /**
     * API isteği yap
     */
    private function makeApiRequest($endpoint, $method = 'GET', $data = null, $tenantId = null) {
        $startTime = microtime(true);
        $credentials = $this->getApiCredentials($tenantId);
        
        if (!$credentials['api_key'] || !$credentials['api_secret']) {
            throw new Exception('Trendyol API kimlik bilgileri eksik');
        }
        
        // Rate limiting kontrolü
        $this->checkRateLimit($endpoint);
        
        // URL oluştur
        $baseUrl = $credentials['sandbox_mode'] ? $this->sandboxUrl : $this->apiUrl;
        $url = $baseUrl . str_replace('{supplierId}', $credentials['supplier_id'], $endpoint);
        
        // Headers
        $headers = [
            'Authorization: Basic ' . base64_encode($credentials['api_key'] . ':' . $credentials['api_secret']),
            'Content-Type: application/json',
            'User-Agent: MesChain-Sync/2.0'
        ];
        
        // cURL isteği
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->configHelper->get('trendyol.timeout', 30),
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
        
        // Performans metriği kaydet
        $this->monitoringHelper->recordMetric('trendyol_api_response_time', $responseTime, 'seconds', [
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
            throw new Exception("API hatası: {$errorMessage}");
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
        if (!$this->configHelper->get('trendyol.rate_limit_enabled', true)) {
            return;
        }
        
        $endpointType = $this->getEndpointType($endpoint);
        $limits = $this->rateLimits[$endpointType] ?? $this->rateLimits['default'];
        
        $cacheKey = "trendyol_rate_limit_{$endpointType}";
        $cache = $this->registry->get('cache');
        
        if ($cache) {
            $currentCount = $cache->get($cacheKey) ?? 0;
            
            if ($currentCount >= $limits['requests']) {
                $this->eventHelper->trigger('api.rate_limit_exceeded', [
                    'marketplace' => 'trendyol',
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
        
        $cacheKey = "trendyol_rate_limit_{$endpointType}";
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
        if (strpos($endpoint, '/products') !== false) {
            return 'products';
        } elseif (strpos($endpoint, '/orders') !== false) {
            return 'orders';
        }
        return 'default';
    }
    
    /**
     * API log kaydet
     */
    private function logApiRequest($endpoint, $method, $requestData, $responseData, $httpStatus, $responseTime, $error, $tenantId) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_api_logs` SET
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
            
            if (!$credentials['api_key'] || !$credentials['api_secret']) {
                return [
                    'status' => 'error',
                    'message' => 'API kimlik bilgileri eksik',
                    'response_time' => microtime(true) - $startTime
                ];
            }
            
            // Basit API testi - supplier bilgilerini çek
            $response = $this->makeApiRequest($this->endpoints['suppliers']);
            
            return [
                'status' => 'healthy',
                'message' => 'API bağlantısı başarılı',
                'response_time' => microtime(true) - $startTime,
                'supplier_id' => $response['id'] ?? null
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'response_time' => microtime(true) - $startTime
            ];
        }
    }
    
    /**
     * Ürün senkronizasyonu
     */
    public function syncProducts($productIds = [], $tenantId = null) {
        $this->log->write("Trendyol ürün senkronizasyonu başlatıldı");
        
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
                        'marketplace' => 'trendyol',
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
        $existing = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_products` 
            WHERE opencart_product_id = " . (int)$product['product_id'] . "
            AND tenant_id = " . ($tenantId ? (int)$tenantId : "NULL"));
        
        // Trendyol ürün verisi hazırla
        $trendyolProduct = $this->prepareProductData($product);
        
        if ($existing->num_rows) {
            // Güncelle
            $mapping = $existing->row;
            $response = $this->makeApiRequest(
                $this->endpoints['products'] . '/' . $mapping['trendyol_product_id'],
                'PUT',
                $trendyolProduct,
                $tenantId
            );
            
            // Mapping güncelle
            $this->db->query("UPDATE `" . DB_PREFIX . "trendyol_products` SET
                last_sync = NOW(),
                sync_status = 'synced',
                error_message = NULL,
                updated_at = NOW()
                WHERE mapping_id = " . (int)$mapping['mapping_id']);
                
        } else {
            // Yeni ürün ekle
            $response = $this->makeApiRequest(
                $this->endpoints['products'],
                'POST',
                $trendyolProduct,
                $tenantId
            );
            
            // Mapping kaydet
            $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_products` SET
                opencart_product_id = " . (int)$product['product_id'] . ",
                barcode = '" . $this->db->escape($trendyolProduct['barcode']) . "',
                last_sync = NOW(),
                sync_status = 'synced',
                tenant_id = " . ($tenantId ? (int)$tenantId : "NULL") . ",
                created_at = NOW(),
                updated_at = NOW()
            ");
        }
        
        return $response;
    }
    
    /**
     * Ürün verisini Trendyol formatına çevir
     */
    private function prepareProductData($product) {
        // Barcode oluştur
        $barcode = $this->generateBarcode($product);
        
        // Kategori mapping
        $categoryId = $this->mapCategory($product['category_id']);
        
        // Marka mapping
        $brandId = $this->mapBrand($product['manufacturer_id']);
        
        // Fiyat hesapla (komisyon vs dahil)
        $finalPrice = $this->calculateFinalPrice($product['price']);
        
        // Resimler hazırla
        $images = $this->prepareImages($product['product_id']);
        
        // Özellikler hazırla
        $attributes = $this->prepareAttributes($product['product_id']);
        
        return [
            'barcode' => $barcode,
            'title' => $this->sanitizeTitle($product['name']),
            'productMainId' => $product['model'] ?: $product['sku'],
            'brandId' => $brandId,
            'categoryId' => $categoryId,
            'quantity' => max(0, (int)$product['quantity']),
            'listPrice' => $finalPrice,
            'salePrice' => $finalPrice,
            'vatRate' => $this->configHelper->get('trendyol.vat_rate', 18),
            'dimensionalWeight' => $this->calculateDimensionalWeight($product),
            'description' => $this->sanitizeDescription($product['description']),
            'images' => $images,
            'attributes' => $attributes,
            'shipmentAddressId' => $this->getShipmentAddressId(),
            'returningAddressId' => $this->getReturningAddressId()
        ];
    }
    
    /**
     * Sipariş senkronizasyonu
     */
    public function syncOrders($tenantId = null, $startDate = null, $endDate = null) {
        $this->log->write("Trendyol sipariş senkronizasyonu başlatıldı");
        
        try {
            $params = [];
            
            if ($startDate) {
                $params['startDate'] = $startDate;
            }
            
            if ($endDate) {
                $params['endDate'] = $endDate;
            }
            
            // API'den siparişleri çek
            $response = $this->makeApiRequest(
                $this->endpoints['orders'] . '?' . http_build_query($params),
                'GET',
                null,
                $tenantId
            );
            
            $syncedCount = 0;
            $errorCount = 0;
            
            if (isset($response['content']) && is_array($response['content'])) {
                foreach ($response['content'] as $trendyolOrder) {
                    try {
                        $this->processTrendyolOrder($trendyolOrder, $tenantId);
                        $syncedCount++;
                        
                        // Event tetikle
                        $this->eventHelper->trigger('order.synced', [
                            'marketplace' => 'trendyol',
                            'order_number' => $trendyolOrder['orderNumber'],
                            'amount' => $trendyolOrder['grossAmount']
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
     * Trendyol siparişini işle
     */
    private function processTrendyolOrder($trendyolOrder, $tenantId = null) {
        // Mevcut sipariş kontrolü
        $existing = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_orders` 
            WHERE order_number = '" . $this->db->escape($trendyolOrder['orderNumber']) . "'");
        
        if ($existing->num_rows) {
            // Güncelle
            $this->updateTrendyolOrder($existing->row, $trendyolOrder);
        } else {
            // Yeni sipariş oluştur
            $this->createTrendyolOrder($trendyolOrder, $tenantId);
        }
    }
    
    /**
     * Webhook işle
     */
    public function processWebhook($eventType, $eventData) {
        $this->log->write("Trendyol webhook alındı: {$eventType}");
        
        // Webhook kaydet
        $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_webhooks` SET
            event_type = '" . $this->db->escape($eventType) . "',
            event_data = '" . $this->db->escape(json_encode($eventData)) . "',
            received_at = NOW()
        ");
        
        $webhookId = $this->db->getLastId();
        
        try {
            switch ($eventType) {
                case 'ORDER_CREATED':
                case 'ORDER_UPDATED':
                    $this->handleOrderWebhook($eventData);
                    break;
                    
                case 'PRODUCT_APPROVED':
                case 'PRODUCT_REJECTED':
                    $this->handleProductWebhook($eventData);
                    break;
                    
                case 'QUESTION_CREATED':
                    $this->handleQuestionWebhook($eventData);
                    break;
                    
                default:
                    $this->log->write("Bilinmeyen webhook tipi: {$eventType}");
            }
            
            // Webhook işlendi olarak işaretle
            $this->db->query("UPDATE `" . DB_PREFIX . "trendyol_webhooks` SET
                processed = 1,
                processed_at = NOW()
                WHERE webhook_id = " . (int)$webhookId);
            
            // Event tetikle
            $this->eventHelper->trigger('webhook.processed', [
                'marketplace' => 'trendyol',
                'event_type' => $eventType,
                'webhook_id' => $webhookId
            ], ['type' => 'async']);
            
        } catch (Exception $e) {
            // Webhook hata kaydet
            $this->db->query("UPDATE `" . DB_PREFIX . "trendyol_webhooks` SET
                error_message = '" . $this->db->escape($e->getMessage()) . "'
                WHERE webhook_id = " . (int)$webhookId);
            
            $this->log->write("Webhook işleme hatası: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Helper metodlar
     */
    private function generateBarcode($product) {
        return 'TY' . str_pad($product['product_id'], 10, '0', STR_PAD_LEFT) . rand(100, 999);
    }
    
    private function sanitizeTitle($title) {
        return substr(strip_tags($title), 0, 100);
    }
    
    private function sanitizeDescription($description) {
        return substr(strip_tags($description), 0, 5000);
    }
    
    private function calculateFinalPrice($price) {
        $commission = $this->configHelper->get('trendyol.commission_rate', 15.0);
        return round($price * (1 + $commission / 100), 2);
    }
    
    private function mapCategory($categoryId) {
        // Kategori mapping logic burada
        return 1; // Placeholder
    }
    
    private function mapBrand($manufacturerId) {
        // Marka mapping logic burada
        return 1; // Placeholder
    }
    
    private function prepareImages($productId) {
        // Ürün resimlerini hazırla
        return []; // Placeholder
    }
    
    private function prepareAttributes($productId) {
        // Ürün özelliklerini hazırla
        return []; // Placeholder
    }
    
    private function calculateDimensionalWeight($product) {
        // Boyutsal ağırlık hesapla
        return 1.0; // Placeholder
    }
    
    private function getShipmentAddressId() {
        return $this->configHelper->get('trendyol.shipment_address_id', 1);
    }
    
    private function getReturningAddressId() {
        return $this->configHelper->get('trendyol.returning_address_id', 1);
    }
    
    private function createTrendyolOrder($trendyolOrder, $tenantId = null) {
        // Yeni Trendyol siparişi oluştur
        // Implementation burada
    }
    
    private function updateTrendyolOrder($existing, $trendyolOrder) {
        // Mevcut siparişi güncelle
        // Implementation burada
    }
    
    private function handleOrderWebhook($eventData) {
        // Sipariş webhook'unu işle
        // Implementation burada
    }
    
    private function handleProductWebhook($eventData) {
        // Ürün webhook'unu işle
        // Implementation burada
    }
    
    private function handleQuestionWebhook($eventData) {
        // Soru webhook'unu işle
        // Implementation burada
    }
}
?> 