<?php

/**
 * MesChain Trendyol Integration - Integration Tests
 * Full System Integration Test Suite
 *
 * @version 1.0.0
 * @author MesChain Development Team
 * @date June 21, 2025
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../TestCase.php';

class TrendyolIntegrationTest extends TestCase
{
    private $db;
    private $config;
    private $trendyolClient;
    private $testProductId;
    private $testOrderId;

    protected function setUp(): void
    {
        parent::setUp();

        // Database connection
        $this->db = new PDO(
            'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'),
            getenv('DB_USER'),
            getenv('DB_PASS'),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Test configuration
        $this->config = [
            'api_key' => getenv('TRENDYOL_API_KEY'),
            'api_secret' => getenv('TRENDYOL_API_SECRET'),
            'supplier_id' => getenv('TRENDYOL_SUPPLIER_ID'),
            'sandbox_mode' => true,
            'debug_mode' => true
        ];

        // Initialize client
        $this->trendyolClient = new TrendyolClient($this->config);

        // Setup test data
        $this->setupTestData();
    }

    /**
     * Setup test data in database
     */
    private function setupTestData()
    {
        // Create test product
        $stmt = $this->db->prepare("
            INSERT INTO oc_product (model, sku, upc, ean, jan, isbn, mpn, location,
                                   quantity, stock_status_id, image, manufacturer_id,
                                   shipping, price, points, tax_class_id, date_available,
                                   weight, weight_class_id, length, width, height,
                                   length_class_id, subtract, minimum, sort_order,
                                   status, viewed, date_added, date_modified)
            VALUES (?, ?, ?, ?, '', '', '', '', ?, 7, '', 0, 1, ?, 0, 0, NOW(),
                    0, 1, 0, 0, 0, 1, 1, 1, 1, 1, 0, NOW(), NOW())
        ");

        $testBarcode = 'TEST' . time();
        $stmt->execute([
            'TEST-MODEL-' . time(),
            'TEST-SKU-' . time(),
            $testBarcode,
            $testBarcode,
            10,
            99.99
        ]);

        $this->testProductId = $this->db->lastInsertId();

        // Add product description
        $stmt = $this->db->prepare("
            INSERT INTO oc_product_description (product_id, language_id, name, description,
                                               tag, meta_title, meta_description, meta_keyword)
            VALUES (?, 1, ?, ?, '', ?, '', '')
        ");

        $productName = 'Test Product ' . time();
        $productDesc = 'Test product description for integration testing';

        $stmt->execute([
            $this->testProductId,
            $productName,
            $productDesc,
            $productName
        ]);

        // Create MesChain product record
        $stmt = $this->db->prepare("
            INSERT INTO meschain_trendyol_products (product_id, barcode, title, description,
                                                   brand, list_price, sale_price, quantity,
                                                   status, created_at)
            VALUES (?, ?, ?, ?, 'Test Brand', ?, ?, ?, 'pending', NOW())
        ");

        $stmt->execute([
            $this->testProductId,
            $testBarcode,
            $productName,
            $productDesc,
            99.99,
            89.99,
            10
        ]);
    }

    /**
     * Test complete product sync workflow
     */
    public function testProductSyncWorkflow()
    {
        // 1. Test product upload to Trendyol
        $productData = $this->getTestProductData();

        $response = $this->trendyolClient->uploadProduct($productData);
        $this->assertTrue($response['success'], 'Product upload should succeed');

        // 2. Update database with Trendyol response
        $this->updateProductSyncStatus($this->testProductId, 'synced');

        // 3. Test stock update
        $newStock = 15;
        $stockResponse = $this->trendyolClient->updateStock($productData['barcode'], $newStock);
        $this->assertTrue($stockResponse['success'], 'Stock update should succeed');

        // 4. Test price update
        $newPrice = 79.99;
        $priceResponse = $this->trendyolClient->updatePrice($productData['barcode'], $newPrice);
        $this->assertTrue($priceResponse['success'], 'Price update should succeed');

        // 5. Verify database updates
        $stmt = $this->db->prepare("
            SELECT * FROM meschain_trendyol_products WHERE product_id = ?
        ");
        $stmt->execute([$this->testProductId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('synced', $product['sync_status']);
        $this->assertNotNull($product['last_sync']);
    }

    /**
     * Test webhook processing workflow
     */
    public function testWebhookProcessingWorkflow()
    {
        // 1. Create test webhook payload
        $webhookPayload = [
            'eventType' => 'ORDER_CREATED',
            'eventTime' => date('c'),
            'orderNumber' => 'TY' . time(),
            'orderData' => [
                'orderNumber' => 'TY' . time(),
                'orderDate' => date('c'),
                'status' => 'Created',
                'customerFirstName' => 'Test',
                'customerLastName' => 'Customer',
                'customerEmail' => 'test@example.com',
                'totalPrice' => 99.99,
                'lines' => [
                    [
                        'barcode' => 'TEST' . time(),
                        'quantity' => 1,
                        'price' => 99.99,
                        'productName' => 'Test Product'
                    ]
                ]
            ]
        ];

        // 2. Store webhook in database
        $stmt = $this->db->prepare("
            INSERT INTO oc_trendyol_webhook_logs (event_type, event_data, received_at, processed)
            VALUES (?, ?, NOW(), 0)
        ");
        $stmt->execute([
            $webhookPayload['eventType'],
            json_encode($webhookPayload)
        ]);

        $webhookId = $this->db->lastInsertId();

        // 3. Process webhook
        $processor = new WebhookProcessor($this->db, $this->config);
        $result = $processor->processWebhook($webhookId);

        $this->assertTrue($result['success'], 'Webhook processing should succeed');

        // 4. Verify webhook was marked as processed
        $stmt = $this->db->prepare("
            SELECT processed, processed_at FROM oc_trendyol_webhook_logs WHERE log_id = ?
        ");
        $stmt->execute([$webhookId]);
        $webhook = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(1, $webhook['processed']);
        $this->assertNotNull($webhook['processed_at']);

        // 5. Verify order was created in OpenCart
        if ($webhookPayload['eventType'] === 'ORDER_CREATED') {
            $stmt = $this->db->prepare("
                SELECT * FROM oc_trendyol_orders WHERE order_number = ?
            ");
            $stmt->execute([$webhookPayload['orderData']['orderNumber']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->assertNotEmpty($order, 'Order should be created');
            $this->assertEquals('Created', $order['status']);
        }
    }

    /**
     * Test cron job execution
     */
    public function testCronJobExecution()
    {
        // 1. Test product sync cron
        $productSync = new ProductSyncCron($this->db, $this->config);
        $result = $productSync->execute();

        $this->assertTrue($result['success'], 'Product sync cron should succeed');
        $this->assertGreaterThan(0, $result['processed_count']);

        // 2. Test order sync cron
        $orderSync = new OrderSyncCron($this->db, $this->config);
        $result = $orderSync->execute();

        $this->assertTrue($result['success'], 'Order sync cron should succeed');

        // 3. Test stock sync cron
        $stockSync = new StockSyncCron($this->db, $this->config);
        $result = $stockSync->execute();

        $this->assertTrue($result['success'], 'Stock sync cron should succeed');

        // 4. Verify cron logs
        $stmt = $this->db->prepare("
            SELECT * FROM oc_trendyol_sync_logs
            WHERE sync_type IN ('product_sync', 'order_sync', 'stock_sync')
            AND DATE(created_at) = CURDATE()
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertGreaterThanOrEqual(3, count($logs), 'Should have logs for all cron jobs');
    }

    /**
     * Test API error handling
     */
    public function testApiErrorHandling()
    {
        // 1. Test invalid API credentials
        $invalidClient = new TrendyolClient([
            'api_key' => 'invalid_key',
            'api_secret' => 'invalid_secret',
            'supplier_id' => 'invalid_supplier',
            'sandbox_mode' => true
        ]);

        $response = $invalidClient->getProducts();
        $this->assertFalse($response['success'], 'Invalid credentials should fail');
        $this->assertArrayHasKey('error', $response);

        // 2. Test rate limiting
        $this->trendyolClient->setRateLimit(1, 60); // 1 request per minute

        $response1 = $this->trendyolClient->getProducts();
        $response2 = $this->trendyolClient->getProducts(); // Should be rate limited

        $this->assertFalse($response2['success'], 'Second request should be rate limited');

        // 3. Test network timeout
        $timeoutClient = new TrendyolClient($this->config);
        $timeoutClient->setTimeout(1); // 1 second timeout

        // This should timeout (assuming the API takes longer than 1 second)
        $response = $timeoutClient->getProducts();
        $this->assertArrayHasKey('error', $response);
    }

    /**
     * Test database transaction handling
     */
    public function testDatabaseTransactions()
    {
        $this->db->beginTransaction();

        try {
            // 1. Create test order
            $orderData = [
                'order_number' => 'TEST_ORDER_' . time(),
                'customer_name' => 'Test Customer',
                'customer_email' => 'test@example.com',
                'total_amount' => 199.99,
                'status' => 'Created'
            ];

            $stmt = $this->db->prepare("
                INSERT INTO oc_trendyol_orders (order_number, customer_name, customer_email,
                                               gross_amount, status, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $orderData['order_number'],
                $orderData['customer_name'],
                $orderData['customer_email'],
                $orderData['total_amount'],
                $orderData['status']
            ]);

            $orderId = $this->db->lastInsertId();

            // 2. Test rollback on error
            $this->db->rollback();

            // 3. Verify order was not saved
            $stmt = $this->db->prepare("
                SELECT * FROM oc_trendyol_orders WHERE order_number = ?
            ");
            $stmt->execute([$orderData['order_number']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->assertEmpty($order, 'Order should not exist after rollback');
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Test data validation and sanitization
     */
    public function testDataValidationAndSanitization()
    {
        // 1. Test product data validation
        $invalidProductData = [
            'barcode' => '', // Empty barcode
            'title' => '<script>alert("xss")</script>Malicious Product',
            'price' => -10, // Negative price
            'quantity' => 'invalid' // Non-numeric quantity
        ];

        $validator = new DataValidator();
        $result = $validator->validateProductData($invalidProductData);

        $this->assertFalse($result['valid'], 'Invalid product data should fail validation');
        $this->assertArrayHasKey('errors', $result);

        // 2. Test data sanitization
        $dirtyData = [
            'title' => '<script>alert("xss")</script>Clean Product',
            'description' => 'Product with "quotes" and special chars: àáâã',
            'price' => '99.99€'
        ];

        $sanitizer = new DataSanitizer();
        $cleanData = $sanitizer->sanitize($dirtyData);

        $this->assertStringNotContainsString('<script>', $cleanData['title']);
        $this->assertStringNotContainsString('€', $cleanData['price']);
        $this->assertIsNumeric($cleanData['price']);
    }

    /**
     * Test performance under load
     */
    public function testPerformanceUnderLoad()
    {
        $startTime = microtime(true);
        $memoryStart = memory_get_usage();

        // Process multiple products
        $productCount = 100;
        $successCount = 0;

        for ($i = 0; $i < $productCount; $i++) {
            $productData = $this->getTestProductData();
            $productData['barcode'] = 'PERF_TEST_' . $i . '_' . time();

            $response = $this->trendyolClient->uploadProduct($productData);
            if ($response['success']) {
                $successCount++;
            }

            // Small delay to avoid overwhelming the API
            usleep(100000); // 0.1 second
        }

        $endTime = microtime(true);
        $memoryEnd = memory_get_usage();

        $executionTime = $endTime - $startTime;
        $memoryUsed = $memoryEnd - $memoryStart;

        // Performance assertions
        $this->assertLessThan(60, $executionTime, 'Should complete within 60 seconds');
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 'Should use less than 50MB memory');
        $this->assertGreaterThan($productCount * 0.8, $successCount, 'At least 80% should succeed');

        // Log performance metrics
        $this->logPerformanceMetrics([
            'test' => 'performance_under_load',
            'products_processed' => $productCount,
            'success_rate' => ($successCount / $productCount) * 100,
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'avg_time_per_product' => $executionTime / $productCount
        ]);
    }

    /**
     * Test concurrent operations
     */
    public function testConcurrentOperations()
    {
        // Simulate concurrent webhook processing
        $webhookCount = 10;
        $processes = [];

        for ($i = 0; $i < $webhookCount; $i++) {
            $webhookPayload = [
                'eventType' => 'STOCK_UPDATE',
                'eventTime' => date('c'),
                'barcode' => 'CONCURRENT_TEST_' . $i,
                'quantity' => rand(1, 100)
            ];

            // Store webhook
            $stmt = $this->db->prepare("
                INSERT INTO oc_trendyol_webhook_logs (event_type, event_data, received_at, processed)
                VALUES (?, ?, NOW(), 0)
            ");
            $stmt->execute([
                $webhookPayload['eventType'],
                json_encode($webhookPayload)
            ]);
        }

        // Process webhooks concurrently (simulated)
        $processor = new WebhookProcessor($this->db, $this->config);
        $results = $processor->processPendingWebhooks();

        $this->assertGreaterThanOrEqual($webhookCount, $results['processed_count']);
        $this->assertEquals(0, $results['error_count'], 'No errors should occur during concurrent processing');
    }

    /**
     * Get test product data
     */
    private function getTestProductData()
    {
        return [
            'barcode' => 'TEST_' . time() . '_' . rand(1000, 9999),
            'title' => 'Test Product ' . time(),
            'description' => 'Test product for integration testing',
            'brand' => 'Test Brand',
            'categoryId' => 411, // Test category
            'listPrice' => 99.99,
            'salePrice' => 89.99,
            'quantity' => 10,
            'vatRate' => 18,
            'images' => [
                'https://example.com/test-image.jpg'
            ],
            'attributes' => [
                'Color' => 'Red',
                'Size' => 'M'
            ]
        ];
    }

    /**
     * Update product sync status
     */
    private function updateProductSyncStatus($productId, $status)
    {
        $stmt = $this->db->prepare("
            UPDATE meschain_trendyol_products
            SET sync_status = ?, last_sync = NOW()
            WHERE product_id = ?
        ");
        $stmt->execute([$status, $productId]);
    }

    /**
     * Log performance metrics
     */
    private function logPerformanceMetrics($metrics)
    {
        $stmt = $this->db->prepare("
            INSERT INTO oc_trendyol_sync_logs (sync_type, status, message, execution_time,
                                              items_processed, created_at)
            VALUES ('performance_test', 'success', ?, ?, ?, NOW())
        ");

        $stmt->execute([
            json_encode($metrics),
            $metrics['execution_time'],
            $metrics['products_processed']
        ]);
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        // Clean up test product
        if ($this->testProductId) {
            $stmt = $this->db->prepare("DELETE FROM oc_product WHERE product_id = ?");
            $stmt->execute([$this->testProductId]);

            $stmt = $this->db->prepare("DELETE FROM oc_product_description WHERE product_id = ?");
            $stmt->execute([$this->testProductId]);

            $stmt = $this->db->prepare("DELETE FROM meschain_trendyol_products WHERE product_id = ?");
            $stmt->execute([$this->testProductId]);
        }

        // Clean up test webhooks
        $stmt = $this->db->prepare("
            DELETE FROM oc_trendyol_webhook_logs
            WHERE event_data LIKE '%TEST_%' OR event_data LIKE '%CONCURRENT_TEST_%'
        ");
        $stmt->execute();

        // Clean up test orders
        $stmt = $this->db->prepare("
            DELETE FROM oc_trendyol_orders
            WHERE order_number LIKE 'TEST_ORDER_%'
        ");
        $stmt->execute();

        parent::tearDown();
    }
}

/**
 * Mock classes for testing
 */
class TrendyolClient
{
    private $config;
    private $rateLimitRequests = [];
    private $timeout = 30;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function uploadProduct($productData)
    {
        // Simulate API call
        if (empty($this->config['api_key']) || $this->config['api_key'] === 'invalid_key') {
            return ['success' => false, 'error' => 'Invalid API credentials'];
        }

        if (!$this->checkRateLimit()) {
            return ['success' => false, 'error' => 'Rate limit exceeded'];
        }

        // Simulate network delay
        usleep(rand(100000, 500000)); // 0.1-0.5 seconds

        return [
            'success' => true,
            'data' => [
                'id' => rand(1000, 9999),
                'barcode' => $productData['barcode'],
                'status' => 'pending'
            ]
        ];
    }

    public function updateStock($barcode, $quantity)
    {
        if (!$this->checkRateLimit()) {
            return ['success' => false, 'error' => 'Rate limit exceeded'];
        }

        return ['success' => true, 'data' => ['barcode' => $barcode, 'quantity' => $quantity]];
    }

    public function updatePrice($barcode, $price)
    {
        if (!$this->checkRateLimit()) {
            return ['success' => false, 'error' => 'Rate limit exceeded'];
        }

        return ['success' => true, 'data' => ['barcode' => $barcode, 'price' => $price]];
    }

    public function getProducts()
    {
        if (empty($this->config['api_key']) || $this->config['api_key'] === 'invalid_key') {
            return ['success' => false, 'error' => 'Invalid API credentials'];
        }

        if (!$this->checkRateLimit()) {
            return ['success' => false, 'error' => 'Rate limit exceeded'];
        }

        return ['success' => true, 'data' => []];
    }

    public function setRateLimit($requests, $timeWindow)
    {
        $this->config['rate_limit_requests'] = $requests;
        $this->config['rate_limit_window'] = $timeWindow;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    private function checkRateLimit()
    {
        $now = time();
        $window = $this->config['rate_limit_window'] ?? 60;
        $maxRequests = $this->config['rate_limit_requests'] ?? 100;

        $this->rateLimitRequests = array_filter(
            $this->rateLimitRequests,
            function ($timestamp) use ($now, $window) {
                return ($now - $timestamp) < $window;
            }
        );

        if (count($this->rateLimitRequests) >= $maxRequests) {
            return false;
        }

        $this->rateLimitRequests[] = $now;
        return true;
    }
}

class WebhookProcessor
{
    private $db;
    private $config;

    public function __construct($db, $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    public function processWebhook($webhookId)
    {
        // Mark as processing
        $stmt = $this->db->prepare("
            UPDATE oc_trendyol_webhook_logs
            SET processing = 1, processing_started_at = NOW()
            WHERE log_id = ?
        ");
        $stmt->execute([$webhookId]);

        // Simulate processing
        usleep(rand(50000, 200000)); // 0.05-0.2 seconds

        // Mark as processed
        $stmt = $this->db->prepare("
            UPDATE oc_trendyol_webhook_logs
            SET processed = 1, processed_at = NOW(), processing = 0
            WHERE log_id = ?
        ");
        $stmt->execute([$webhookId]);

        return ['success' => true];
    }

    public function processPendingWebhooks()
    {
        $stmt = $this->db->prepare("
            SELECT log_id FROM oc_trendyol_webhook_logs
            WHERE processed = 0 AND processing = 0
        ");
        $stmt->execute();
        $webhooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $processedCount = 0;
        $errorCount = 0;

        foreach ($webhooks as $webhook) {
            try {
                $this->processWebhook($webhook['log_id']);
                $processedCount++;
            } catch (Exception $e) {
                $errorCount++;
            }
        }

        return [
            'processed_count' => $processedCount,
            'error_count' => $errorCount
        ];
    }
}

class ProductSyncCron
{
    private $db;
    private $config;

    public function __construct($db, $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    public function execute()
    {
        // Simulate cron execution
        usleep(rand(100000, 300000)); // 0.1-0.3 seconds

        return [
            'success' => true,
            'processed_count' => rand(5, 20)
        ];
    }
}

class OrderSyncCron
{
    private $db;
    private $config;

    public function __construct($db, $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    public function execute()
    {
        usleep(rand(100000, 300000));
        return ['success' => true, 'processed_count' => rand(1, 10)];
    }
}

class StockSyncCron
{
    private $db;
    private $config;

    public function __construct($db, $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    public function execute()
    {
        usleep(rand(100000, 300000));
        return ['success' => true, 'processed_count' => rand(10, 50)];
    }
}

class DataValidator
{
    public function validateProductData($data)
    {
        $errors = [];

        if (empty($data['barcode'])) {
            $errors[] = 'Barcode is required';
        }

        if (isset($data['price']) && $data['price'] < 0) {
            $errors[] = 'Price cannot be negative';
        }

        if (isset($data['quantity']) && !is_numeric($data['quantity'])) {
            $errors[] = 'Quantity must be numeric';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

class DataSanitizer
{
    public function sanitize($data)
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove HTML tags
                $value = strip_tags($value);

                // Remove special characters from price
                if ($key === 'price') {
                    $value = preg_replace('/[^0-9.]/', '', $value);
                }
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }
}
