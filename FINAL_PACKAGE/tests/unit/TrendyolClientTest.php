<?php

/**
 * MesChain Trendyol Integration - Unit Tests
 * Trendyol API Client Test Suite
 *
 * @version 1.0.0
 * @author MesChain Development Team
 * @date June 21, 2025
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../TestCase.php';

use PHPUnit\Framework\TestCase;

class TrendyolClientTest extends TestCase
{
    private $trendyolClient;
    private $mockConfig;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock configuration
        $this->mockConfig = [
            'api_key' => 'test_api_key',
            'api_secret' => 'test_api_secret',
            'supplier_id' => 'test_supplier_id',
            'sandbox_mode' => true,
            'debug_mode' => true
        ];

        // Initialize Trendyol client with mock config
        $this->trendyolClient = new TrendyolClient($this->mockConfig);
    }

    /**
     * Test API client initialization
     */
    public function testClientInitialization()
    {
        $this->assertInstanceOf(TrendyolClient::class, $this->trendyolClient);
        $this->assertEquals('test_api_key', $this->trendyolClient->getApiKey());
        $this->assertEquals('test_supplier_id', $this->trendyolClient->getSupplierId());
        $this->assertTrue($this->trendyolClient->isSandboxMode());
    }

    /**
     * Test API URL generation
     */
    public function testApiUrlGeneration()
    {
        // Test sandbox URL
        $sandboxUrl = $this->trendyolClient->getApiUrl('/products');
        $this->assertStringContains('stageapi.trendyol.com', $sandboxUrl);

        // Test production URL
        $this->trendyolClient->setSandboxMode(false);
        $productionUrl = $this->trendyolClient->getApiUrl('/products');
        $this->assertStringContains('api.trendyol.com', $productionUrl);
    }

    /**
     * Test authentication header generation
     */
    public function testAuthenticationHeaders()
    {
        $headers = $this->trendyolClient->getAuthHeaders();

        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('User-Agent', $headers);

        // Test Basic Auth format
        $expectedAuth = base64_encode('test_api_key:test_api_secret');
        $this->assertEquals("Basic $expectedAuth", $headers['Authorization']);
    }

    /**
     * Test request signature generation
     */
    public function testRequestSignature()
    {
        $data = ['test' => 'data'];
        $timestamp = time();

        $signature = $this->trendyolClient->generateSignature($data, $timestamp);

        $this->assertIsString($signature);
        $this->assertNotEmpty($signature);

        // Test signature consistency
        $signature2 = $this->trendyolClient->generateSignature($data, $timestamp);
        $this->assertEquals($signature, $signature2);
    }

    /**
     * Test rate limiting
     */
    public function testRateLimiting()
    {
        $this->trendyolClient->setRateLimit(2, 60); // 2 requests per minute

        // First request should pass
        $this->assertTrue($this->trendyolClient->checkRateLimit());

        // Second request should pass
        $this->assertTrue($this->trendyolClient->checkRateLimit());

        // Third request should be rate limited
        $this->assertFalse($this->trendyolClient->checkRateLimit());
    }

    /**
     * Test error handling
     */
    public function testErrorHandling()
    {
        // Test invalid API key
        $invalidClient = new TrendyolClient([
            'api_key' => '',
            'api_secret' => 'test_secret',
            'supplier_id' => 'test_supplier'
        ]);

        $this->expectException(InvalidArgumentException::class);
        $invalidClient->validateConfig();
    }

    /**
     * Test product data validation
     */
    public function testProductDataValidation()
    {
        $validProduct = [
            'barcode' => '1234567890123',
            'title' => 'Test Product',
            'brand' => 'Test Brand',
            'categoryId' => 123,
            'listPrice' => 100.00,
            'salePrice' => 90.00,
            'quantity' => 10
        ];

        $this->assertTrue($this->trendyolClient->validateProductData($validProduct));

        // Test invalid product data
        $invalidProduct = [
            'barcode' => '123', // Too short
            'title' => '', // Empty title
            'listPrice' => -10 // Negative price
        ];

        $this->assertFalse($this->trendyolClient->validateProductData($invalidProduct));
    }

    /**
     * Test order data validation
     */
    public function testOrderDataValidation()
    {
        $validOrder = [
            'orderNumber' => 'TY123456789',
            'orderDate' => '2025-06-21T10:00:00Z',
            'status' => 'Created',
            'customerFirstName' => 'John',
            'customerLastName' => 'Doe',
            'totalPrice' => 150.00,
            'lines' => [
                [
                    'barcode' => '1234567890123',
                    'quantity' => 2,
                    'price' => 75.00
                ]
            ]
        ];

        $this->assertTrue($this->trendyolClient->validateOrderData($validOrder));

        // Test invalid order data
        $invalidOrder = [
            'orderNumber' => '', // Empty order number
            'totalPrice' => 'invalid', // Invalid price format
            'lines' => [] // Empty lines
        ];

        $this->assertFalse($this->trendyolClient->validateOrderData($invalidOrder));
    }

    /**
     * Test webhook signature validation
     */
    public function testWebhookSignatureValidation()
    {
        $payload = json_encode(['test' => 'webhook_data']);
        $secret = 'webhook_secret';
        $timestamp = time();

        // Generate valid signature
        $validSignature = hash_hmac('sha256', $timestamp . $payload, $secret);

        $this->assertTrue(
            $this->trendyolClient->validateWebhookSignature(
                $payload,
                $validSignature,
                $timestamp,
                $secret
            )
        );

        // Test invalid signature
        $invalidSignature = 'invalid_signature';

        $this->assertFalse(
            $this->trendyolClient->validateWebhookSignature(
                $payload,
                $invalidSignature,
                $timestamp,
                $secret
            )
        );
    }

    /**
     * Test API response parsing
     */
    public function testApiResponseParsing()
    {
        // Test successful response
        $successResponse = [
            'statusCode' => 200,
            'body' => json_encode([
                'success' => true,
                'data' => ['id' => 123, 'name' => 'Test']
            ])
        ];

        $parsed = $this->trendyolClient->parseApiResponse($successResponse);
        $this->assertTrue($parsed['success']);
        $this->assertEquals(123, $parsed['data']['id']);

        // Test error response
        $errorResponse = [
            'statusCode' => 400,
            'body' => json_encode([
                'success' => false,
                'errors' => ['Invalid data']
            ])
        ];

        $parsed = $this->trendyolClient->parseApiResponse($errorResponse);
        $this->assertFalse($parsed['success']);
        $this->assertArrayHasKey('errors', $parsed);
    }

    /**
     * Test retry mechanism
     */
    public function testRetryMechanism()
    {
        $this->trendyolClient->setMaxRetries(3);
        $this->trendyolClient->setRetryDelay(1); // 1 second delay

        $retryCount = 0;
        $callback = function () use (&$retryCount) {
            $retryCount++;
            if ($retryCount < 3) {
                throw new Exception('Temporary error');
            }
            return 'success';
        };

        $result = $this->trendyolClient->executeWithRetry($callback);

        $this->assertEquals('success', $result);
        $this->assertEquals(3, $retryCount);
    }

    /**
     * Test data sanitization
     */
    public function testDataSanitization()
    {
        $dirtyData = [
            'title' => '<script>alert("xss")</script>Test Product',
            'description' => 'Product with "quotes" and special chars: àáâã',
            'price' => '99.99€',
            'quantity' => '10 pieces'
        ];

        $cleanData = $this->trendyolClient->sanitizeData($dirtyData);

        $this->assertStringNotContainsString('<script>', $cleanData['title']);
        $this->assertStringNotContainsString('€', $cleanData['price']);
        $this->assertIsNumeric($cleanData['quantity']);
    }

    /**
     * Test logging functionality
     */
    public function testLogging()
    {
        $this->trendyolClient->enableLogging(true);

        // Test info log
        $this->trendyolClient->log('info', 'Test info message', ['data' => 'test']);

        // Test error log
        $this->trendyolClient->log('error', 'Test error message', ['error' => 'test_error']);

        $logs = $this->trendyolClient->getLogs();

        $this->assertCount(2, $logs);
        $this->assertEquals('info', $logs[0]['level']);
        $this->assertEquals('error', $logs[1]['level']);
    }

    /**
     * Test configuration validation
     */
    public function testConfigurationValidation()
    {
        // Test valid configuration
        $validConfig = [
            'api_key' => 'valid_key',
            'api_secret' => 'valid_secret',
            'supplier_id' => 'valid_supplier',
            'sandbox_mode' => true
        ];

        $this->assertTrue($this->trendyolClient->validateConfiguration($validConfig));

        // Test invalid configuration
        $invalidConfigs = [
            [], // Empty config
            ['api_key' => ''], // Empty API key
            ['api_key' => 'key'], // Missing secret
            ['api_key' => 'key', 'api_secret' => 'secret'] // Missing supplier ID
        ];

        foreach ($invalidConfigs as $config) {
            $this->assertFalse($this->trendyolClient->validateConfiguration($config));
        }
    }

    /**
     * Test cache functionality
     */
    public function testCacheFunctionality()
    {
        $this->trendyolClient->enableCache(true);

        $cacheKey = 'test_cache_key';
        $cacheData = ['test' => 'data'];
        $ttl = 3600; // 1 hour

        // Test cache set
        $this->assertTrue($this->trendyolClient->setCache($cacheKey, $cacheData, $ttl));

        // Test cache get
        $cachedData = $this->trendyolClient->getCache($cacheKey);
        $this->assertEquals($cacheData, $cachedData);

        // Test cache delete
        $this->assertTrue($this->trendyolClient->deleteCache($cacheKey));
        $this->assertNull($this->trendyolClient->getCache($cacheKey));
    }

    protected function tearDown(): void
    {
        $this->trendyolClient = null;
        parent::tearDown();
    }
}

/**
 * Mock TrendyolClient class for testing
 */
class TrendyolClient
{
    private $config;
    private $rateLimitRequests = [];
    private $logs = [];
    private $cache = [];

    public function __construct($config)
    {
        $this->config = $config;
        $this->validateConfig();
    }

    public function getApiKey()
    {
        return $this->config['api_key'] ?? '';
    }

    public function getSupplierId()
    {
        return $this->config['supplier_id'] ?? '';
    }

    public function isSandboxMode()
    {
        return $this->config['sandbox_mode'] ?? false;
    }

    public function setSandboxMode($sandbox)
    {
        $this->config['sandbox_mode'] = $sandbox;
    }

    public function getApiUrl($endpoint)
    {
        $baseUrl = $this->isSandboxMode()
            ? 'https://stageapi.trendyol.com'
            : 'https://api.trendyol.com';

        return $baseUrl . $endpoint;
    }

    public function getAuthHeaders()
    {
        $auth = base64_encode($this->getApiKey() . ':' . ($this->config['api_secret'] ?? ''));

        return [
            'Authorization' => "Basic $auth",
            'Content-Type' => 'application/json',
            'User-Agent' => 'MesChain-Trendyol/1.0.0'
        ];
    }

    public function generateSignature($data, $timestamp)
    {
        $payload = json_encode($data) . $timestamp;
        return hash_hmac('sha256', $payload, $this->config['api_secret'] ?? '');
    }

    public function setRateLimit($requests, $timeWindow)
    {
        $this->config['rate_limit_requests'] = $requests;
        $this->config['rate_limit_window'] = $timeWindow;
    }

    public function checkRateLimit()
    {
        $now = time();
        $window = $this->config['rate_limit_window'] ?? 60;
        $maxRequests = $this->config['rate_limit_requests'] ?? 100;

        // Clean old requests
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

    public function validateConfig()
    {
        if (empty($this->config['api_key'])) {
            throw new InvalidArgumentException('API key is required');
        }

        if (empty($this->config['api_secret'])) {
            throw new InvalidArgumentException('API secret is required');
        }

        if (empty($this->config['supplier_id'])) {
            throw new InvalidArgumentException('Supplier ID is required');
        }
    }

    public function validateProductData($product)
    {
        // Validate barcode
        if (empty($product['barcode']) || strlen($product['barcode']) < 8) {
            return false;
        }

        // Validate title
        if (empty($product['title'])) {
            return false;
        }

        // Validate price
        if (isset($product['listPrice']) && $product['listPrice'] < 0) {
            return false;
        }

        return true;
    }

    public function validateOrderData($order)
    {
        // Validate order number
        if (empty($order['orderNumber'])) {
            return false;
        }

        // Validate total price
        if (isset($order['totalPrice']) && !is_numeric($order['totalPrice'])) {
            return false;
        }

        // Validate lines
        if (empty($order['lines'])) {
            return false;
        }

        return true;
    }

    public function validateWebhookSignature($payload, $signature, $timestamp, $secret)
    {
        $expectedSignature = hash_hmac('sha256', $timestamp . $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    public function parseApiResponse($response)
    {
        $body = json_decode($response['body'], true);

        if ($response['statusCode'] >= 200 && $response['statusCode'] < 300) {
            return $body;
        }

        return [
            'success' => false,
            'errors' => $body['errors'] ?? ['Unknown error'],
            'statusCode' => $response['statusCode']
        ];
    }

    public function setMaxRetries($retries)
    {
        $this->config['max_retries'] = $retries;
    }

    public function setRetryDelay($delay)
    {
        $this->config['retry_delay'] = $delay;
    }

    public function executeWithRetry($callback)
    {
        $maxRetries = $this->config['max_retries'] ?? 3;
        $delay = $this->config['retry_delay'] ?? 1;

        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                return $callback();
            } catch (Exception $e) {
                if ($i === $maxRetries - 1) {
                    throw $e;
                }
                sleep($delay);
            }
        }
    }

    public function sanitizeData($data)
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove HTML tags
                $value = strip_tags($value);

                // Remove special characters from numeric fields
                if (in_array($key, ['price', 'quantity'])) {
                    $value = preg_replace('/[^0-9.]/', '', $value);
                }
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    public function enableLogging($enabled)
    {
        $this->config['logging_enabled'] = $enabled;
    }

    public function log($level, $message, $context = [])
    {
        if ($this->config['logging_enabled'] ?? false) {
            $this->logs[] = [
                'level' => $level,
                'message' => $message,
                'context' => $context,
                'timestamp' => time()
            ];
        }
    }

    public function getLogs()
    {
        return $this->logs;
    }

    public function validateConfiguration($config)
    {
        $required = ['api_key', 'api_secret', 'supplier_id'];

        foreach ($required as $field) {
            if (empty($config[$field])) {
                return false;
            }
        }

        return true;
    }

    public function enableCache($enabled)
    {
        $this->config['cache_enabled'] = $enabled;
    }

    public function setCache($key, $data, $ttl)
    {
        if ($this->config['cache_enabled'] ?? false) {
            $this->cache[$key] = [
                'data' => $data,
                'expires' => time() + $ttl
            ];
            return true;
        }
        return false;
    }

    public function getCache($key)
    {
        if (!isset($this->cache[$key])) {
            return null;
        }

        $item = $this->cache[$key];

        if (time() > $item['expires']) {
            unset($this->cache[$key]);
            return null;
        }

        return $item['data'];
    }

    public function deleteCache($key)
    {
        if (isset($this->cache[$key])) {
            unset($this->cache[$key]);
            return true;
        }
        return false;
    }
}
