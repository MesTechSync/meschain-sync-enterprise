<?php
/**
 * MeschainEbayHelper - Modern eBay API Entegrasyonu
 * 
 * Event-driven architecture, health monitoring ve webhook desteği ile
 * gelişmiş eBay marketplace entegrasyonu
 * 
 * @author MesChain Development Team
 * @version 2.0.0
 * @since 2024-01-21
 */

class MeschainEbayHelper {
    
    private $registry;
    private $db;
    private $log;
    private $configHelper;
    private $eventHelper;
    private $monitoringHelper;
    
    // API URLs
    private $apiUrl = 'https://api.ebay.com';
    private $sandboxUrl = 'https://api.sandbox.ebay.com';
    
    // API endpoints
    private $endpoints = [
        'oauth' => '/identity/v1/oauth2/token',
        'inventory' => '/sell/inventory/v1',
        'account' => '/sell/account/v1',
        'fulfillment' => '/sell/fulfillment/v1',
        'finances' => '/sell/finances/v1',
        'analytics' => '/sell/analytics/v1',
        'browse' => '/buy/browse/v1',
        'taxonomy' => '/commerce/taxonomy/v1',
        'catalog' => '/commerce/catalog/v1'
    ];
    
    // Global Site IDs
    private $globalSiteIds = [
        'US' => 0,    // United States
        'UK' => 3,    // United Kingdom
        'DE' => 77,   // Germany
        'FR' => 71,   // France
        'IT' => 101,  // Italy
        'ES' => 186,  // Spain
        'AU' => 15,   // Australia
        'CA' => 2,    // Canada
        'IN' => 203,  // India
        'JP' => 0     // Japan (not available yet)
    ];
    
    // Rate limiting
    private $rateLimits = [
        'default' => ['requests' => 5000, 'period' => 3600], // 5000 req/hour
        'inventory' => ['requests' => 2000, 'period' => 3600], // 2000 req/hour
        'orders' => ['requests' => 5000, 'period' => 3600]     // 5000 req/hour
    ];
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->log = new Log('meschain_ebay.log');
        
        // Helper'ları yükle
        require_once(DIR_SYSTEM . 'library/meschain/helper/config.php');
        require_once(DIR_SYSTEM . 'library/meschain/helper/event.php');
        require_once(DIR_SYSTEM . 'library/meschain/helper/monitoring.php');
        
        $this->configHelper = new MeschainConfigHelper($registry);
        $this->eventHelper = new MeschainEventHelper($registry);
        $this->monitoringHelper = new MeschainMonitoringHelper($registry);
        
        $this->createEbayTables();
        $this->loadDefaultConfigs();
    }
    
    /**
     * eBay tablolarını oluştur
     */
    private function createEbayTables() {
        // eBay products mapping
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ebay_products` (
            `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_product_id` int(11) NOT NULL,
            `ebay_item_id` varchar(100),
            `sku` varchar(100) NOT NULL,
            `offer_id` varchar(100),
            `listing_id` varchar(100),
            `epid` varchar(100),
            `gtin` varchar(100),
            `brand` varchar(100),
            `mpn` varchar(100),
            `condition` varchar(50) DEFAULT 'NEW',
            `listing_status` varchar(50) DEFAULT 'DRAFT',
            `marketplace_id` varchar(50) NOT NULL,
            `format` enum('FIXED_PRICE','AUCTION') DEFAULT 'FIXED_PRICE',
            `duration` varchar(50) DEFAULT 'GTC',
            `category_id` varchar(100),
            `price` decimal(15,4),
            `quantity` int(11) DEFAULT 0,
            `last_sync` datetime DEFAULT NULL,
            `sync_status` enum('synced','pending','error') DEFAULT 'pending',
            `error_message` text,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`mapping_id`),
            UNIQUE KEY `sku_marketplace_unique` (`sku`, `marketplace_id`),
            KEY `opencart_product_id` (`opencart_product_id`),
            KEY `ebay_item_id` (`ebay_item_id`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // eBay orders
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ebay_orders` (
            `ebay_order_id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_order_id` int(11) DEFAULT NULL,
            `order_id` varchar(100) NOT NULL,
            `legacy_order_id` varchar(100),
            `order_fulfillment_status` varchar(50) NOT NULL,
            `order_payment_status` varchar(50) NOT NULL,
            `sales_record_reference` varchar(100),
            `total_fee_basis_amount` decimal(15,4) DEFAULT 0,
            `total_marketplace_fee` decimal(15,4) DEFAULT 0,
            `buyer` json,
            `pricing_summary` json,
            `cancel_status` json,
            `payment_summary` json,
            `fulfillment_start_instructions` json,
            `line_items` json,
            `creation_date` datetime NOT NULL,
            `last_modified_date` datetime,
            `order_fulfillment_instructions` json,
            `sync_status` enum('pending','synced','error') DEFAULT 'pending',
            `error_message` text,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`ebay_order_id`),
            UNIQUE KEY `order_id_unique` (`order_id`),
            KEY `opencart_order_id` (`opencart_order_id`),
            KEY `order_fulfillment_status` (`order_fulfillment_status`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // eBay API logs
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ebay_api_logs` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `endpoint` varchar(255) NOT NULL,
            `method` varchar(10) NOT NULL,
            `marketplace_id` varchar(50),
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
        
        // eBay categories cache
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ebay_categories` (
            `category_id` varchar(100) NOT NULL,
            `marketplace_id` varchar(50) NOT NULL,
            `parent_id` varchar(100) DEFAULT NULL,
            `category_name` varchar(255) NOT NULL,
            `category_level` int(11) DEFAULT 0,
            `leaf_category` tinyint(1) DEFAULT 0,
            `auto_pay_enabled` tinyint(1) DEFAULT 0,
            `b2b_vat_enabled` tinyint(1) DEFAULT 0,
            `catalog_enabled` tinyint(1) DEFAULT 0,
            `last_updated` datetime NOT NULL,
            PRIMARY KEY (`category_id`, `marketplace_id`),
            KEY `parent_id` (`parent_id`),
            KEY `marketplace_id` (`marketplace_id`),
            KEY `leaf_category` (`leaf_category`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        $this->log->write('eBay tabloları oluşturuldu/kontrol edildi');
    }
    
    /**
     * Varsayılan konfigürasyonları yükle
     */
    private function loadDefaultConfigs() {
        $defaults = [
            'ebay.api_url' => $this->apiUrl,
            'ebay.sandbox_url' => $this->sandboxUrl,
            'ebay.default_marketplace' => 'US',
            'ebay.timeout' => 30,
            'ebay.retry_attempts' => 3,
            'ebay.rate_limit_enabled' => true,
            'ebay.auto_sync_products' => true,
            'ebay.auto_sync_orders' => true,
            'ebay.auto_sync_interval' => 900, // 15 dakika
            'ebay.default_condition' => 'NEW',
            'ebay.default_format' => 'FIXED_PRICE',
            'ebay.default_duration' => 'GTC',
            'ebay.sandbox_mode' => false,
            'ebay.auto_relist' => false
        ];
        
        foreach ($defaults as $key => $value) {
            $existing = $this->configHelper->get($key);
            if ($existing === null) {
                $this->configHelper->set($key, $value, [
                    'type' => 'marketplace',
                    'description' => 'eBay marketplace configuration'
                ]);
            }
        }
    }
    
    /**
     * API kimlik bilgilerini al
     */
    private function getApiCredentials($tenantId = null) {
        $config = $this->configHelper->getMarketplaceConfig('ebay', $tenantId);
        
        return [
            'client_id' => $config['ebay.client_id'] ?? null,
            'client_secret' => $config['ebay.client_secret'] ?? null,
            'refresh_token' => $config['ebay.refresh_token'] ?? null,
            'redirect_uri' => $config['ebay.redirect_uri'] ?? null,
            'marketplace_id' => $config['ebay.marketplace_id'] ?? 'EBAY_US',
            'sandbox_mode' => $config['ebay.sandbox_mode'] ?? false
        ];
    }
    
    /**
     * Access token al
     */
    private function getAccessToken($tenantId = null) {
        $credentials = $this->getApiCredentials($tenantId);
        
        if (!$credentials['client_id'] || !$credentials['client_secret']) {
            throw new Exception('eBay API kimlik bilgileri eksik');
        }
        
        // Cache'den kontrol et
        $cacheKey = "ebay_access_token_{$tenantId}";
        $cache = $this->registry->get('cache');
        
        if ($cache) {
            $cachedToken = $cache->get($cacheKey);
            if ($cachedToken) {
                return $cachedToken;
            }
        }
        
        // OAuth token endpoint
        $baseUrl = $credentials['sandbox_mode'] ? $this->sandboxUrl : $this->apiUrl;
        $tokenUrl = $baseUrl . $this->endpoints['oauth'];
        
        $headers = [
            'Authorization: Basic ' . base64_encode($credentials['client_id'] . ':' . $credentials['client_secret']),
            'Content-Type: application/x-www-form-urlencoded'
        ];
        
        $postData = 'grant_type=refresh_token&refresh_token=' . urlencode($credentials['refresh_token']);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $tokenUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => $headers,
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
     * API isteği yap
     */
    private function makeApiRequest($endpoint, $method = 'GET', $data = null, $tenantId = null, $apiType = 'sell') {
        $startTime = microtime(true);
        $credentials = $this->getApiCredentials($tenantId);
        
        // Access token al
        $accessToken = $this->getAccessToken($tenantId);
        
        // Rate limiting kontrolü
        $this->checkRateLimit($endpoint);
        
        // Base URL belirle
        $baseUrl = $credentials['sandbox_mode'] ? $this->sandboxUrl : $this->apiUrl;
        
        // Full endpoint URL oluştur
        if (strpos($endpoint, 'http') === 0) {
            $url = $endpoint;
        } else {
            $url = $baseUrl . $endpoint;
        }
        
        // Headers
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'User-Agent: MesChain-Sync/2.0',
            'Accept: application/json'
        ];
        
        // Marketplace ID gerekiyorsa ekle
        if (in_array($apiType, ['sell', 'buy'])) {
            $headers[] = 'X-EBAY-C-MARKETPLACE-ID: ' . $credentials['marketplace_id'];
        }
        
        // cURL isteği
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->configHelper->get('ebay.timeout', 30),
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
        $this->monitoringHelper->recordMetric('ebay_api_response_time', $responseTime, 'seconds', [
            'endpoint' => $endpoint,
            'method' => $method,
            'marketplace_id' => $credentials['marketplace_id']
        ]);
        
        // API log kaydet
        $this->logApiRequest($endpoint, $method, $data, $response, $httpStatus, $responseTime, $error, $tenantId, $credentials['marketplace_id']);
        
        if ($error) {
            throw new Exception("cURL hatası: {$error}");
        }
        
        if ($httpStatus >= 400) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['errors'][0]['message'] ?? "HTTP {$httpStatus} hatası";
            throw new Exception("eBay API hatası: {$errorMessage}");
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
        if (!$this->configHelper->get('ebay.rate_limit_enabled', true)) {
            return;
        }
        
        $endpointType = $this->getEndpointType($endpoint);
        $limits = $this->rateLimits[$endpointType] ?? $this->rateLimits['default'];
        
        $cacheKey = "ebay_rate_limit_{$endpointType}";
        $cache = $this->registry->get('cache');
        
        if ($cache) {
            $currentCount = $cache->get($cacheKey) ?? 0;
            
            if ($currentCount >= $limits['requests']) {
                $this->eventHelper->trigger('api.rate_limit_exceeded', [
                    'marketplace' => 'ebay',
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
        
        $cacheKey = "ebay_rate_limit_{$endpointType}";
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
        if (strpos($endpoint, '/inventory/') !== false) {
            return 'inventory';
        } elseif (strpos($endpoint, '/fulfillment/') !== false) {
            return 'orders';
        }
        return 'default';
    }
    
    /**
     * API log kaydet
     */
    private function logApiRequest($endpoint, $method, $requestData, $responseData, $httpStatus, $responseTime, $error, $tenantId, $marketplaceId) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "ebay_api_logs` SET
            endpoint = '" . $this->db->escape($endpoint) . "',
            method = '" . $this->db->escape($method) . "',
            marketplace_id = '" . $this->db->escape($marketplaceId) . "',
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
            
            if (!$credentials['client_id'] || !$credentials['client_secret'] || !$credentials['refresh_token']) {
                return [
                    'status' => 'error',
                    'message' => 'API kimlik bilgileri eksik',
                    'response_time' => microtime(true) - $startTime
                ];
            }
            
            // Basit API testi - inventory location al
            $response = $this->makeApiRequest($this->endpoints['inventory'] . '/location', 'GET', null, null, 'sell');
            
            if (isset($response['locations'])) {
                return [
                    'status' => 'healthy',
                    'message' => 'API bağlantısı başarılı',
                    'response_time' => microtime(true) - $startTime,
                    'location_count' => count($response['locations'])
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
    public function syncCategories($tenantId = null, $marketplaceId = null) {
        $this->log->write("eBay kategori senkronizasyonu başlatıldı");
        
        try {
            $credentials = $this->getApiCredentials($tenantId);
            $targetMarketplace = $marketplaceId ?: $credentials['marketplace_id'];
            
            $response = $this->makeApiRequest($this->endpoints['taxonomy'] . '/category_tree', 'GET', [
                'marketplace_id' => $targetMarketplace
            ], $tenantId, 'taxonomy');
            
            if (isset($response['categoryTrees']) && is_array($response['categoryTrees'])) {
                foreach ($response['categoryTrees'] as $tree) {
                    if (isset($tree['rootCategoryNode'])) {
                        $this->processCategoryTree($tree['rootCategoryNode'], null, $targetMarketplace);
                    }
                }
                
                $this->log->write("eBay kategori senkronizasyonu tamamlandı");
                
                return [
                    'success' => true,
                    'message' => 'Kategoriler başarıyla senkronize edildi'
                ];
            } else {
                throw new Exception('Kategori verisi alınamadı');
            }
            
        } catch (Exception $e) {
            $this->log->write("eBay kategori senkronizasyonu hatası: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Kategori ağacını işle
     */
    private function processCategoryTree($node, $parentId = null, $marketplaceId = 'EBAY_US', $level = 0) {
        // Kategoriyi kaydet
        $this->db->query("INSERT INTO `" . DB_PREFIX . "ebay_categories` SET
            category_id = '" . $this->db->escape($node['category']['categoryId']) . "',
            marketplace_id = '" . $this->db->escape($marketplaceId) . "',
            parent_id = " . ($parentId ? "'" . $this->db->escape($parentId) . "'" : "NULL") . ",
            category_name = '" . $this->db->escape($node['category']['categoryName']) . "',
            category_level = " . (int)$level . ",
            leaf_category = " . (int)($node['leafCategoryTreeNode'] ?? 0) . ",
            auto_pay_enabled = " . (int)($node['category']['autoPayEnabled'] ?? 0) . ",
            b2b_vat_enabled = " . (int)($node['category']['b2bVATEnabled'] ?? 0) . ",
            catalog_enabled = " . (int)($node['category']['catalogEnabled'] ?? 0) . ",
            last_updated = NOW()
            ON DUPLICATE KEY UPDATE
            category_name = '" . $this->db->escape($node['category']['categoryName']) . "',
            category_level = " . (int)$level . ",
            leaf_category = " . (int)($node['leafCategoryTreeNode'] ?? 0) . ",
            auto_pay_enabled = " . (int)($node['category']['autoPayEnabled'] ?? 0) . ",
            b2b_vat_enabled = " . (int)($node['category']['b2bVATEnabled'] ?? 0) . ",
            catalog_enabled = " . (int)($node['category']['catalogEnabled'] ?? 0) . ",
            last_updated = NOW()
        ");
        
        // Alt kategorileri işle
        if (isset($node['childCategoryTreeNodes']) && !empty($node['childCategoryTreeNodes'])) {
            foreach ($node['childCategoryTreeNodes'] as $child) {
                $this->processCategoryTree($child, $node['category']['categoryId'], $marketplaceId, $level + 1);
            }
        }
    }
    
    /**
     * Ürün senkronizasyonu
     */
    public function syncProducts($productIds = [], $tenantId = null) {
        $this->log->write("eBay ürün senkronizasyonu başlatıldı");
        
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
                        'marketplace' => 'ebay',
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
        $existing = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ebay_products` 
            WHERE opencart_product_id = " . (int)$product['product_id'] . "
            AND tenant_id = " . ($tenantId ? (int)$tenantId : "NULL"));
        
        // eBay ürün verisi hazırla
        $ebayProduct = $this->prepareProductData($product);
        
        if ($existing->num_rows) {
            // Güncelle - Inventory Item API kullan
            $mapping = $existing->row;
            $response = $this->makeApiRequest(
                $this->endpoints['inventory'] . '/inventory_item/' . $mapping['sku'],
                'PUT',
                $ebayProduct,
                $tenantId,
                'sell'
            );
            
        } else {
            // Yeni ürün ekle - Inventory Item API kullan
            $sku = $this->generateSku($product);
            
            $response = $this->makeApiRequest(
                $this->endpoints['inventory'] . '/inventory_item/' . $sku,
                'PUT',
                $ebayProduct,
                $tenantId,
                'sell'
            );
            
            // Mapping kaydet
            if ($response === null || !isset($response['errors'])) { // 204 No Content success response
                $this->db->query("INSERT INTO `" . DB_PREFIX . "ebay_products` SET
                    opencart_product_id = " . (int)$product['product_id'] . ",
                    sku = '" . $this->db->escape($sku) . "',
                    marketplace_id = '" . $this->db->escape($this->getApiCredentials($tenantId)['marketplace_id']) . "',
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
     * Ürün verisini eBay formatına çevir
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
        $aspects = $this->prepareAspects($product['product_id']);
        
        return [
            'availability' => [
                'shipToLocationAvailability' => [
                    'quantity' => max(0, (int)$product['quantity'])
                ]
            ],
            'condition' => $this->configHelper->get('ebay.default_condition', 'NEW'),
            'product' => [
                'title' => $this->sanitizeTitle($product['name']),
                'description' => $this->sanitizeDescription($product['description']),
                'imageUrls' => $images,
                'aspects' => $aspects
            ],
            'locale' => 'en_US',
            'packageWeightAndSize' => [
                'dimensions' => [
                    'height' => 10,
                    'length' => 10,
                    'width' => 10,
                    'unit' => 'INCH'
                ],
                'weight' => [
                    'value' => 1.0,
                    'unit' => 'POUND'
                ]
            ]
        ];
    }
    
    /**
     * Sipariş senkronizasyonu
     */
    public function syncOrders($tenantId = null, $creationDateFrom = null, $creationDateTo = null) {
        $this->log->write("eBay sipariş senkronizasyonu başlatıldı");
        
        try {
            $filter = [];
            
            if ($creationDateFrom) {
                $filter['creationdate'] = '[' . $creationDateFrom . '..' . ($creationDateTo ?: date('c')) . ']';
            }
            
            $response = $this->makeApiRequest($this->endpoints['fulfillment'] . '/order', 'GET', $filter, $tenantId, 'sell');
            
            $syncedCount = 0;
            $errorCount = 0;
            
            if (isset($response['orders']) && is_array($response['orders'])) {
                foreach ($response['orders'] as $ebayOrder) {
                    try {
                        $this->processEbayOrder($ebayOrder, $tenantId);
                        $syncedCount++;
                        
                        // Event tetikle
                        $this->eventHelper->trigger('order.synced', [
                            'marketplace' => 'ebay',
                            'order_id' => $ebayOrder['orderId'],
                            'amount' => $ebayOrder['pricingSummary']['total']['value'] ?? 0
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
     * eBay siparişini işle
     */
    private function processEbayOrder($ebayOrder, $tenantId = null) {
        // Mevcut sipariş kontrolü
        $existing = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ebay_orders` 
            WHERE order_id = '" . $this->db->escape($ebayOrder['orderId']) . "'");
        
        if ($existing->num_rows) {
            // Güncelle
            $this->updateEbayOrder($existing->row, $ebayOrder);
        } else {
            // Yeni sipariş oluştur
            $this->createEbayOrder($ebayOrder, $tenantId);
        }
    }
    
    /**
     * Helper metodlar
     */
    private function generateSku($product) {
        return 'EB-' . str_pad($product['product_id'], 8, '0', STR_PAD_LEFT);
    }
    
    private function sanitizeTitle($title) {
        return substr(strip_tags($title), 0, 80);
    }
    
    private function sanitizeDescription($description) {
        return substr(strip_tags($description), 0, 4000);
    }
    
    private function calculateFinalPrice($price) {
        // eBay fee dahil değil, listing'de belirtilir
        return round($price, 2);
    }
    
    private function mapCategory($categoryId) {
        // Kategori mapping logic burada
        return '177'; // Computers/Tablets & Networking
    }
    
    private function prepareImages($productId) {
        // Ürün resimlerini hazırla
        return []; // Placeholder
    }
    
    private function prepareAspects($productId) {
        // Ürün özelliklerini hazırla
        return []; // Placeholder
    }
    
    private function createEbayOrder($ebayOrder, $tenantId = null) {
        // Yeni eBay siparişi oluştur
        // Implementation burada
    }
    
    private function updateEbayOrder($existing, $ebayOrder) {
        // Mevcut siparişi güncelle
        // Implementation burada
    }
}
?> 