<?php

/**
 * MesChain Trendyol Integration - Base Test Case
 * Common testing functionality and utilities
 *
 * @version 1.0.0
 * @author MesChain Development Team
 * @date June 21, 2025
 */

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected $config;
    protected $testDb;

    protected function setUp(): void
    {
        parent::setUp();

        // Load test configuration
        $this->config = $this->loadTestConfig();

        // Setup test database if needed
        $this->setupTestDatabase();
    }

    protected function tearDown(): void
    {
        // Cleanup test database
        $this->cleanupTestDatabase();

        parent::tearDown();
    }

    /**
     * Load test configuration
     */
    protected function loadTestConfig()
    {
        $configFile = __DIR__ . '/config/test_config.php';

        if (file_exists($configFile)) {
            return include $configFile;
        }

        // Default test configuration
        return [
            'db_host' => getenv('DB_HOST') ?: 'localhost',
            'db_name' => getenv('DB_NAME') ?: 'test_opencart',
            'db_user' => getenv('DB_USER') ?: 'root',
            'db_pass' => getenv('DB_PASS') ?: '',
            'trendyol_api_key' => getenv('TRENDYOL_API_KEY') ?: 'test_key',
            'trendyol_api_secret' => getenv('TRENDYOL_API_SECRET') ?: 'test_secret',
            'trendyol_supplier_id' => getenv('TRENDYOL_SUPPLIER_ID') ?: '12345',
            'base_url' => getenv('BASE_URL') ?: 'http://localhost',
            'admin_user' => getenv('ADMIN_USER') ?: 'admin',
            'admin_pass' => getenv('ADMIN_PASS') ?: 'admin'
        ];
    }

    /**
     * Setup test database
     */
    protected function setupTestDatabase()
    {
        try {
            $this->testDb = new PDO(
                "mysql:host={$this->config['db_host']};dbname={$this->config['db_name']}",
                $this->config['db_user'],
                $this->config['db_pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Cleanup test database
     */
    protected function cleanupTestDatabase()
    {
        if ($this->testDb) {
            // Clean up test data
            $tables = [
                'oc_trendyol_products',
                'oc_trendyol_orders',
                'oc_trendyol_logs',
                'oc_trendyol_sync_queue'
            ];

            foreach ($tables as $table) {
                try {
                    $stmt = $this->testDb->prepare("DELETE FROM $table WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
                    $stmt->execute();
                } catch (PDOException $e) {
                    // Table might not exist, ignore
                }
            }
        }
    }

    /**
     * Create test product data
     */
    protected function createTestProduct($data = [])
    {
        $defaultData = [
            'name' => 'Test Product ' . time(),
            'model' => 'TEST-' . time(),
            'price' => 99.99,
            'quantity' => 10,
            'status' => 1,
            'date_added' => date('Y-m-d H:i:s'),
            'date_modified' => date('Y-m-d H:i:s')
        ];

        $productData = array_merge($defaultData, $data);

        $stmt = $this->testDb->prepare("
            INSERT INTO oc_product (model, quantity, price, status, date_added, date_modified)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $productData['model'],
            $productData['quantity'],
            $productData['price'],
            $productData['status'],
            $productData['date_added'],
            $productData['date_modified']
        ]);

        $productId = $this->testDb->lastInsertId();

        // Add product description
        $stmt = $this->testDb->prepare("
            INSERT INTO oc_product_description (product_id, language_id, name, description, meta_title)
            VALUES (?, 1, ?, ?, ?)
        ");

        $stmt->execute([
            $productId,
            $productData['name'],
            'Test product description',
            $productData['name']
        ]);

        return $productId;
    }

    /**
     * Create test order data
     */
    protected function createTestOrder($data = [])
    {
        $defaultData = [
            'order_number' => 'TEST_ORDER_' . time(),
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'gross_amount' => 199.99,
            'status' => 'Created',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $orderData = array_merge($defaultData, $data);

        $stmt = $this->testDb->prepare("
            INSERT INTO oc_trendyol_orders (order_number, customer_name, customer_email,
                                           gross_amount, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $orderData['order_number'],
            $orderData['customer_name'],
            $orderData['customer_email'],
            $orderData['gross_amount'],
            $orderData['status'],
            $orderData['created_at']
        ]);

        return $orderData['order_number'];
    }

    /**
     * Assert API response structure
     */
    protected function assertApiResponse($response, $expectedKeys = [])
    {
        $this->assertIsArray($response, 'Response should be an array');

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $response, "Response should contain key: $key");
        }
    }

    /**
     * Assert database record exists
     */
    protected function assertDatabaseHas($table, $conditions)
    {
        $whereClause = [];
        $values = [];

        foreach ($conditions as $column => $value) {
            $whereClause[] = "$column = ?";
            $values[] = $value;
        }

        $sql = "SELECT COUNT(*) as count FROM $table WHERE " . implode(' AND ', $whereClause);
        $stmt = $this->testDb->prepare($sql);
        $stmt->execute($values);

        $result = $stmt->fetch();
        $this->assertGreaterThan(0, $result['count'], "Record not found in $table");
    }

    /**
     * Assert database record does not exist
     */
    protected function assertDatabaseMissing($table, $conditions)
    {
        $whereClause = [];
        $values = [];

        foreach ($conditions as $column => $value) {
            $whereClause[] = "$column = ?";
            $values[] = $value;
        }

        $sql = "SELECT COUNT(*) as count FROM $table WHERE " . implode(' AND ', $whereClause);
        $stmt = $this->testDb->prepare($sql);
        $stmt->execute($values);

        $result = $stmt->fetch();
        $this->assertEquals(0, $result['count'], "Unexpected record found in $table");
    }

    /**
     * Mock HTTP client for API testing
     */
    protected function mockHttpClient($responses = [])
    {
        return new MockHttpClient($responses);
    }

    /**
     * Generate test API response
     */
    protected function generateApiResponse($data = [], $success = true)
    {
        return [
            'success' => $success,
            'data' => $data,
            'message' => $success ? 'Operation successful' : 'Operation failed',
            'timestamp' => time()
        ];
    }

    /**
     * Wait for condition with timeout
     */
    protected function waitForCondition(callable $condition, $timeout = 10, $interval = 0.5)
    {
        $start = microtime(true);

        while (microtime(true) - $start < $timeout) {
            if ($condition()) {
                return true;
            }
            usleep($interval * 1000000);
        }

        return false;
    }
}

/**
 * Mock HTTP Client for testing
 */
class MockHttpClient
{
    private $responses;
    private $requestCount = 0;

    public function __construct($responses = [])
    {
        $this->responses = $responses;
    }

    public function get($url, $headers = [])
    {
        return $this->getResponse();
    }

    public function post($url, $data = [], $headers = [])
    {
        return $this->getResponse();
    }

    public function put($url, $data = [], $headers = [])
    {
        return $this->getResponse();
    }

    public function delete($url, $headers = [])
    {
        return $this->getResponse();
    }

    private function getResponse()
    {
        if (isset($this->responses[$this->requestCount])) {
            $response = $this->responses[$this->requestCount];
        } else {
            $response = ['success' => true, 'data' => []];
        }

        $this->requestCount++;
        return $response;
    }

    public function getRequestCount()
    {
        return $this->requestCount;
    }
}
