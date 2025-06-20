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

        // Simple logging implementation
        $this->log = new class {
            public function write($message) {
                error_log('[MeschainTrendyol] ' . $message);
            }
        };

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
     */    private function makeApiRequest($endpoint, $method = 'GET', $data = null, $tenantId = null, $testMode = false) {
        $startTime = microtime(true);
        $credentials = $this->getApiCredentials($tenantId);

        if (!$credentials['api_key'] || !$credentials['api_secret']) {
            throw new Exception('Trendyol API kimlik bilgileri eksik');
        }

        // Test mode için minimal kontrol
        if ($testMode) {
            return ['success' => true, 'test_mode' => true];
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
                            CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
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
        try {
            $healthData = [
                'api_connection' => false,
                'webhook_system' => false,
                'database_connection' => false,
                'order_processing' => false,
                'product_sync' => false,
                'last_check' => date('Y-m-d H:i:s'),
                'issues' => []
            ];

            // 1. Database bağlantısı kontrol
            try {
                $this->db->query("SELECT 1");
                $healthData['database_connection'] = true;
            } catch (Exception $e) {
                $healthData['issues'][] = 'Database connection failed: ' . $e->getMessage();
            }

            // 2. API bağlantısı kontrol
            try {
                $apiStatus = $this->testApiConnection();
                $healthData['api_connection'] = $apiStatus['success'];
                if (!$apiStatus['success']) {
                    $healthData['issues'][] = 'API connection failed: ' . ($apiStatus['error'] ?? 'Unknown error');
                }
            } catch (Exception $e) {
                $healthData['issues'][] = 'API test failed: ' . $e->getMessage();
            }

            // 3. Webhook sistem kontrol
            try {
                $webhookUrl = $this->getWebhookUrl();
                $healthData['webhook_system'] = !empty($webhookUrl);
                if (empty($webhookUrl)) {
                    $healthData['issues'][] = 'Webhook URL not configured';
                }
            } catch (Exception $e) {
                $healthData['issues'][] = 'Webhook system error: ' . $e->getMessage();
            }

            // 4. Son sipariş işleme kontrol
            try {
                $lastOrderQuery = $this->db->query("
                    SELECT COUNT(*) as order_count
                    FROM " . DB_PREFIX . "meschain_order_mapping
                    WHERE marketplace = 'trendyol'
                    AND date_added > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ");
                $healthData['order_processing'] = $lastOrderQuery->row['order_count'] > 0;
            } catch (Exception $e) {
                $healthData['issues'][] = 'Order processing check failed: ' . $e->getMessage();
            }

            // 5. Ürün senkronizasyon kontrol
            try {
                $lastSyncQuery = $this->db->query("
                    SELECT value FROM " . DB_PREFIX . "setting
                    WHERE `key` = 'trendyol_last_product_sync'
                ");
                $lastSync = $lastSyncQuery->num_rows ? $lastSyncQuery->row['value'] : null;
                $healthData['product_sync'] = $lastSync && (time() - strtotime($lastSync)) < 86400; // 24 saat

                if (!$healthData['product_sync']) {
                    $healthData['issues'][] = 'Product sync not executed in last 24 hours';
                }
            } catch (Exception $e) {
                $healthData['issues'][] = 'Product sync check failed: ' . $e->getMessage();
            }

            // Overall health status
            $healthData['overall_status'] = empty($healthData['issues']) ? 'healthy' : 'warning';
            if (count($healthData['issues']) > 2) {
                $healthData['overall_status'] = 'critical';
            }

            // Health check sonucunu kaydet
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "meschain_health_checks SET
                marketplace = 'trendyol',
                status = '" . $this->db->escape($healthData['overall_status']) . "',
                details = '" . $this->db->escape(json_encode($healthData)) . "',
                date_added = NOW()
            ");

            $this->log->write('Trendyol health check completed: ' . $healthData['overall_status']);

            return $healthData;

        } catch (Exception $e) {
            $this->log->write('Health check failed: ' . $e->getMessage());
            return [
                'overall_status' => 'critical',
                'issues' => ['Health check error'],
                'last_check' => date('Y-m-d H:i:s')
            ];
        }
    }

    private function testApiConnection() {
        try {
            $testEndpoint = $this->baseUrl . '/suppliers/' . $this->supplierId;
            $result = $this->makeApiRequest('GET', $testEndpoint, [], null, true);
            return ['success' => $result !== false];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
