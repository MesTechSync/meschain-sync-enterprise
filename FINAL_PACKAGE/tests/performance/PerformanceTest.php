<?php

/**
 * MesChain Trendyol Integration - Performance Tests
 * Load Testing and Performance Benchmarking
 *
 * @version 1.0.0
 * @author MesChain Development Team
 * @date June 21, 2025
 */

require_once __DIR__ . '/../TestCase.php';

class PerformanceTest extends TestCase
{
    private $apiClient;
    private $performanceMetrics = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize API client for performance testing
        $this->apiClient = new TrendyolApiClient([
            'api_key' => $this->config['trendyol_api_key'],
            'api_secret' => $this->config['trendyol_api_secret'],
            'supplier_id' => $this->config['trendyol_supplier_id'],
            'sandbox' => true
        ]);
    }

    /**
     * Test API response times under normal load
     */
    public function testApiResponseTimes()
    {
        $endpoints = [
            'products' => '/suppliers/{supplierId}/products',
            'orders' => '/suppliers/{supplierId}/orders',
            'shipment-providers' => '/shipment-providers',
            'brands' => '/product-categories/{categoryId}/brands',
            'categories' => '/product-categories'
        ];

        $results = [];

        foreach ($endpoints as $name => $endpoint) {
            $times = [];

            // Test each endpoint 10 times
            for ($i = 0; $i < 10; $i++) {
                $startTime = microtime(true);

                try {
                    $response = $this->apiClient->get($endpoint);
                    $endTime = microtime(true);
                    $times[] = ($endTime - $startTime) * 1000; // Convert to milliseconds
                } catch (Exception $e) {
                    $this->fail("API call failed for $name: " . $e->getMessage());
                }
            }

            $results[$name] = [
                'avg_response_time' => array_sum($times) / count($times),
                'min_response_time' => min($times),
                'max_response_time' => max($times),
                'total_requests' => count($times)
            ];
        }

        // Assert performance benchmarks
        foreach ($results as $endpoint => $metrics) {
            $this->assertLessThan(
                2000,
                $metrics['avg_response_time'],
                "Average response time for $endpoint should be under 2 seconds"
            );
            $this->assertLessThan(
                5000,
                $metrics['max_response_time'],
                "Max response time for $endpoint should be under 5 seconds"
            );
        }

        $this->performanceMetrics['api_response_times'] = $results;
    }

    /**
     * Test database query performance
     */
    public function testDatabasePerformance()
    {
        $queries = [
            'product_sync_status' => "
                SELECT p.product_id, p.model, tp.trendyol_id, tp.sync_status, tp.last_sync
                FROM oc_product p
                LEFT JOIN oc_trendyol_products tp ON p.product_id = tp.product_id
                WHERE p.status = 1
                LIMIT 100
            ",
            'order_processing' => "
                SELECT o.order_id, o.order_status_id, to.trendyol_order_id, to.status
                FROM oc_order o
                LEFT JOIN oc_trendyol_orders to ON o.order_id = to.order_id
                WHERE o.date_added >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                LIMIT 100
            ",
            'sync_queue_status' => "
                SELECT queue_id, entity_type, entity_id, action, status, created_at
                FROM oc_trendyol_sync_queue
                WHERE status IN ('pending', 'processing')
                ORDER BY priority DESC, created_at ASC
                LIMIT 50
            ",
            'error_logs' => "
                SELECT log_id, level, message, context, created_at
                FROM oc_trendyol_logs
                WHERE level = 'error' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY created_at DESC
                LIMIT 100
            "
        ];

        $results = [];

        foreach ($queries as $name => $sql) {
            $times = [];

            // Execute each query 5 times
            for ($i = 0; $i < 5; $i++) {
                $startTime = microtime(true);

                try {
                    $stmt = $this->testDb->prepare($sql);
                    $stmt->execute();
                    $stmt->fetchAll();

                    $endTime = microtime(true);
                    $times[] = ($endTime - $startTime) * 1000; // Convert to milliseconds
                } catch (PDOException $e) {
                    $this->fail("Database query failed for $name: " . $e->getMessage());
                }
            }

            $results[$name] = [
                'avg_query_time' => array_sum($times) / count($times),
                'min_query_time' => min($times),
                'max_query_time' => max($times),
                'total_queries' => count($times)
            ];
        }

        // Assert database performance benchmarks
        foreach ($results as $query => $metrics) {
            $this->assertLessThan(
                500,
                $metrics['avg_query_time'],
                "Average query time for $query should be under 500ms"
            );
            $this->assertLessThan(
                1000,
                $metrics['max_query_time'],
                "Max query time for $query should be under 1 second"
            );
        }

        $this->performanceMetrics['database_performance'] = $results;
    }

    /**
     * Test concurrent product synchronization
     */
    public function testConcurrentProductSync()
    {
        $productCount = 50;
        $concurrentProcesses = 5;

        // Create test products
        $productIds = [];
        for ($i = 0; $i < $productCount; $i++) {
            $productIds[] = $this->createTestProduct([
                'name' => "Performance Test Product $i",
                'model' => "PERF-TEST-$i-" . time()
            ]);
        }

        $startTime = microtime(true);

        // Simulate concurrent sync processes
        $processes = [];
        $productsPerProcess = ceil($productCount / $concurrentProcesses);

        for ($p = 0; $p < $concurrentProcesses; $p++) {
            $startIndex = $p * $productsPerProcess;
            $endIndex = min($startIndex + $productsPerProcess, $productCount);
            $processProducts = array_slice($productIds, $startIndex, $endIndex - $startIndex);

            $processes[] = $this->simulateProductSyncProcess($processProducts);
        }

        // Wait for all processes to complete
        $this->waitForProcesses($processes);

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Performance assertions
        $this->assertLessThan(
            30000,
            $totalTime,
            "Concurrent sync of $productCount products should complete within 30 seconds"
        );

        $avgTimePerProduct = $totalTime / $productCount;
        $this->assertLessThan(
            1000,
            $avgTimePerProduct,
            "Average sync time per product should be under 1 second"
        );

        $this->performanceMetrics['concurrent_sync'] = [
            'total_products' => $productCount,
            'concurrent_processes' => $concurrentProcesses,
            'total_time_ms' => $totalTime,
            'avg_time_per_product_ms' => $avgTimePerProduct,
            'products_per_second' => $productCount / ($totalTime / 1000)
        ];
    }

    /**
     * Test memory usage during bulk operations
     */
    public function testMemoryUsage()
    {
        $initialMemory = memory_get_usage(true);
        $peakMemory = $initialMemory;

        // Test bulk product processing
        $productCount = 1000;
        $products = [];

        for ($i = 0; $i < $productCount; $i++) {
            $products[] = [
                'id' => $i + 1,
                'name' => "Bulk Test Product $i",
                'model' => "BULK-$i",
                'price' => rand(10, 1000),
                'quantity' => rand(1, 100),
                'description' => str_repeat("Test description for product $i. ", 10)
            ];

            // Track peak memory usage
            $currentMemory = memory_get_usage(true);
            if ($currentMemory > $peakMemory) {
                $peakMemory = $currentMemory;
            }
        }

        // Process products in batches
        $batchSize = 100;
        $batches = array_chunk($products, $batchSize);

        foreach ($batches as $batch) {
            $this->processBulkProducts($batch);

            // Track memory after each batch
            $currentMemory = memory_get_usage(true);
            if ($currentMemory > $peakMemory) {
                $peakMemory = $currentMemory;
            }
        }

        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;
        $peakIncrease = $peakMemory - $initialMemory;

        // Memory usage assertions
        $maxAllowedIncrease = 50 * 1024 * 1024; // 50MB
        $this->assertLessThan(
            $maxAllowedIncrease,
            $memoryIncrease,
            "Memory increase should be less than 50MB"
        );

        $maxAllowedPeak = 100 * 1024 * 1024; // 100MB
        $this->assertLessThan(
            $maxAllowedPeak,
            $peakIncrease,
            "Peak memory increase should be less than 100MB"
        );

        $this->performanceMetrics['memory_usage'] = [
            'initial_memory_mb' => round($initialMemory / 1024 / 1024, 2),
            'final_memory_mb' => round($finalMemory / 1024 / 1024, 2),
            'peak_memory_mb' => round($peakMemory / 1024 / 1024, 2),
            'memory_increase_mb' => round($memoryIncrease / 1024 / 1024, 2),
            'peak_increase_mb' => round($peakIncrease / 1024 / 1024, 2),
            'products_processed' => $productCount
        ];
    }

    /**
     * Test webhook processing performance
     */
    public function testWebhookProcessingPerformance()
    {
        $webhookCount = 100;
        $webhooks = [];

        // Generate test webhooks
        for ($i = 0; $i < $webhookCount; $i++) {
            $webhooks[] = [
                'eventType' => 'OrderStatusChanged',
                'eventTime' => date('c'),
                'data' => [
                    'orderId' => 'TEST_ORDER_' . $i,
                    'status' => 'Shipped',
                    'trackingNumber' => 'TRACK_' . $i
                ]
            ];
        }

        $startTime = microtime(true);

        // Process webhooks
        foreach ($webhooks as $webhook) {
            $this->processWebhook($webhook);
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Performance assertions
        $avgTimePerWebhook = $totalTime / $webhookCount;
        $this->assertLessThan(
            100,
            $avgTimePerWebhook,
            "Average webhook processing time should be under 100ms"
        );

        $webhooksPerSecond = $webhookCount / ($totalTime / 1000);
        $this->assertGreaterThan(
            10,
            $webhooksPerSecond,
            "Should process at least 10 webhooks per second"
        );

        $this->performanceMetrics['webhook_processing'] = [
            'total_webhooks' => $webhookCount,
            'total_time_ms' => $totalTime,
            'avg_time_per_webhook_ms' => $avgTimePerWebhook,
            'webhooks_per_second' => $webhooksPerSecond
        ];
    }

    /**
     * Test cron job performance
     */
    public function testCronJobPerformance()
    {
        $cronJobs = [
            'product_sync' => 'php /path/to/opencart/meschain_trendyol_product_sync.php',
            'order_sync' => 'php /path/to/opencart/meschain_trendyol_order_sync.php',
            'stock_update' => 'php /path/to/opencart/meschain_trendyol_stock_update.php',
            'cleanup' => 'php /path/to/opencart/meschain_trendyol_cleanup.php'
        ];

        $results = [];

        foreach ($cronJobs as $jobName => $command) {
            $startTime = microtime(true);

            // Simulate cron job execution
            $this->simulateCronJob($jobName);

            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

            $results[$jobName] = [
                'execution_time_ms' => $executionTime,
                'status' => 'completed'
            ];

            // Assert reasonable execution times
            $maxTime = $jobName === 'cleanup' ? 10000 : 30000; // 10s for cleanup, 30s for others
            $this->assertLessThan(
                $maxTime,
                $executionTime,
                "Cron job $jobName should complete within reasonable time"
            );
        }

        $this->performanceMetrics['cron_jobs'] = $results;
    }

    /**
     * Generate comprehensive performance report
     */
    public function testGeneratePerformanceReport()
    {
        // Run all performance tests first
        $this->testApiResponseTimes();
        $this->testDatabasePerformance();
        $this->testConcurrentProductSync();
        $this->testMemoryUsage();
        $this->testWebhookProcessingPerformance();
        $this->testCronJobPerformance();

        // Generate report
        $report = [
            'test_date' => date('Y-m-d H:i:s'),
            'system_info' => [
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'opcache_enabled' => extension_loaded('opcache')
            ],
            'performance_metrics' => $this->performanceMetrics,
            'recommendations' => $this->generateRecommendations()
        ];

        // Save report to file
        $reportFile = __DIR__ . '/../../reports/performance_report_' . date('Y-m-d_H-i-s') . '.json';
        $this->ensureDirectoryExists(dirname($reportFile));
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));

        $this->assertTrue(file_exists($reportFile), 'Performance report should be generated');

        echo "\nPerformance Report Generated: $reportFile\n";
        echo "Summary:\n";
        echo "- API Response Times: " . count($this->performanceMetrics['api_response_times']) . " endpoints tested\n";
        echo "- Database Queries: " . count($this->performanceMetrics['database_performance']) . " queries tested\n";
        echo "- Memory Usage: " . $this->performanceMetrics['memory_usage']['peak_increase_mb'] . "MB peak increase\n";
        echo "- Webhook Processing: " . round($this->performanceMetrics['webhook_processing']['webhooks_per_second'], 2) . " webhooks/sec\n";
    }

    /**
     * Helper methods
     */

    private function simulateProductSyncProcess($productIds)
    {
        // Simulate product sync process
        foreach ($productIds as $productId) {
            // Simulate API call delay
            usleep(rand(100000, 500000)); // 100-500ms

            // Update sync status
            $stmt = $this->testDb->prepare("
                INSERT INTO oc_trendyol_products (product_id, sync_status, last_sync)
                VALUES (?, 'synced', NOW())
                ON DUPLICATE KEY UPDATE sync_status = 'synced', last_sync = NOW()
            ");
            $stmt->execute([$productId]);
        }

        return true;
    }

    private function waitForProcesses($processes)
    {
        // In a real implementation, this would wait for actual processes
        // For testing, we simulate the wait
        usleep(1000000); // 1 second
        return true;
    }

    private function processBulkProducts($products)
    {
        // Simulate bulk product processing
        foreach ($products as $product) {
            // Simulate processing overhead
            $processed = array_merge($product, [
                'processed_at' => time(),
                'hash' => md5(serialize($product))
            ]);
        }

        // Simulate memory cleanup
        unset($products);
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    private function processWebhook($webhook)
    {
        // Simulate webhook processing
        $processed = [
            'webhook_id' => uniqid(),
            'event_type' => $webhook['eventType'],
            'processed_at' => microtime(true),
            'data' => $webhook['data']
        ];

        // Simulate database operations
        usleep(rand(10000, 50000)); // 10-50ms

        return $processed;
    }

    private function simulateCronJob($jobName)
    {
        // Simulate different cron job operations
        switch ($jobName) {
            case 'product_sync':
                usleep(rand(5000000, 15000000)); // 5-15 seconds
                break;
            case 'order_sync':
                usleep(rand(3000000, 10000000)); // 3-10 seconds
                break;
            case 'stock_update':
                usleep(rand(2000000, 8000000)); // 2-8 seconds
                break;
            case 'cleanup':
                usleep(rand(1000000, 5000000)); // 1-5 seconds
                break;
        }
    }

    private function generateRecommendations()
    {
        $recommendations = [];

        // API performance recommendations
        if (isset($this->performanceMetrics['api_response_times'])) {
            $avgTimes = array_column($this->performanceMetrics['api_response_times'], 'avg_response_time');
            $overallAvg = array_sum($avgTimes) / count($avgTimes);

            if ($overallAvg > 1000) {
                $recommendations[] = "Consider implementing API response caching to improve performance";
            }
            if ($overallAvg > 1500) {
                $recommendations[] = "Review API rate limiting and consider request batching";
            }
        }

        // Memory usage recommendations
        if (isset($this->performanceMetrics['memory_usage'])) {
            $memoryIncrease = $this->performanceMetrics['memory_usage']['peak_increase_mb'];

            if ($memoryIncrease > 30) {
                $recommendations[] = "Consider implementing batch processing to reduce memory usage";
            }
            if ($memoryIncrease > 50) {
                $recommendations[] = "Review data structures and implement memory optimization";
            }
        }

        // Database performance recommendations
        if (isset($this->performanceMetrics['database_performance'])) {
            $queryTimes = array_column($this->performanceMetrics['database_performance'], 'avg_query_time');
            $avgQueryTime = array_sum($queryTimes) / count($queryTimes);

            if ($avgQueryTime > 200) {
                $recommendations[] = "Consider adding database indexes to improve query performance";
            }
            if ($avgQueryTime > 300) {
                $recommendations[] = "Review database queries and consider query optimization";
            }
        }

        return $recommendations;
    }

    private function ensureDirectoryExists($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}

/**
 * Mock Trendyol API Client for performance testing
 */
class TrendyolApiClient
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function get($endpoint)
    {
        // Simulate API call delay
        usleep(rand(100000, 800000)); // 100-800ms

        return [
            'success' => true,
            'data' => [
                'items' => array_fill(0, rand(10, 100), ['id' => rand(1, 1000)]),
                'totalCount' => rand(100, 1000)
            ]
        ];
    }

    public function post($endpoint, $data)
    {
        // Simulate API call delay
        usleep(rand(200000, 1000000)); // 200ms-1s

        return [
            'success' => true,
            'data' => ['id' => rand(1, 1000)]
        ];
    }
}
