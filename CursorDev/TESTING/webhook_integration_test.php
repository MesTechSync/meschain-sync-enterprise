<?php
/**
 * Trendyol Webhook Integration Test Suite
 * MesChain-Sync v3.0.1 - Live API Endpoint Testing
 * 
 * Purpose: Test webhook functionality with actual Trendyol API endpoints
 */

class TrendyolWebhookIntegrationTest {
    
    private $config;
    private $testResults = [];
    private $errors = [];
    private $webhookEndpoint;
    private $secretKey;
    
    public function __construct($config = []) {
        $this->config = array_merge([
            'base_url' => 'https://api.trendyol.com/sapigw/suppliers/',
            'webhook_url' => 'https://your-domain.com/index.php?route=extension/module/trendyol/webhook',
            'supplier_id' => '',
            'api_key' => '',
            'api_secret' => '',
            'test_mode' => true,
            'timeout' => 30,
            'retry_count' => 3
        ], $config);
        
        $this->webhookEndpoint = $this->config['webhook_url'];
        $this->secretKey = $this->generateWebhookSecret();
    }
    
    /**
     * Run comprehensive webhook integration tests
     */
    public function runWebhookIntegrationTests() {
        echo "<h1>🚀 Trendyol Webhook Integration Test Suite</h1>\n";
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px;'>\n";
        
        // Test 1: API Connection Test
        $this->testAPIConnection();
        
        // Test 2: Webhook Registration Test
        $this->testWebhookRegistration();
        
        // Test 3: Webhook Event Simulation
        $this->testWebhookEventSimulation();
        
        // Test 4: Webhook Security Validation
        $this->testWebhookSecurity();
        
        // Test 5: Webhook Response Time Test
        $this->testWebhookPerformance();
        
        // Test 6: Error Handling Test
        $this->testErrorHandling();
        
        // Test 7: Webhook Configuration Management
        $this->testWebhookConfiguration();
        
        // Display comprehensive results
        $this->displayIntegrationResults();
        
        echo "</div>\n";
        
        return $this->testResults;
    }
    
    /**
     * Test 1: API Connection Test
     */
    private function testAPIConnection() {
        echo "<h2>🔌 Test 1: Trendyol API Connection</h2>\n";
        
        try {
            $startTime = microtime(true);
            
            // Test basic API connectivity
            $url = $this->config['base_url'] . $this->config['supplier_id'] . '/products';
            
            $headers = [
                'User-Agent: MesChain-Sync-v3.0.1',
                'Authorization: Basic ' . base64_encode($this->config['api_key'] . ':' . $this->config['api_secret']),
                'Content-Type: application/json'
            ];
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->config['timeout'],
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_FOLLOWLOCATION => true
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $responseTime = microtime(true) - $startTime;
            
            if (curl_errno($ch)) {
                throw new Exception('cURL Error: ' . curl_error($ch));
            }
            
            curl_close($ch);
            
            if ($httpCode === 200) {
                echo "✅ API connection successful (Response time: " . number_format($responseTime * 1000, 2) . " ms)\n";
                echo "✅ HTTP Status: {$httpCode}\n";
                $this->testResults['api_connection'] = [
                    'status' => 'success',
                    'response_time' => $responseTime * 1000,
                    'http_code' => $httpCode
                ];
            } else if ($httpCode === 401) {
                echo "⚠️ API authentication failed (HTTP {$httpCode}) - Check credentials\n";
                $this->testResults['api_connection'] = [
                    'status' => 'auth_failed',
                    'http_code' => $httpCode
                ];
            } else {
                echo "❌ API connection failed (HTTP {$httpCode})\n";
                $this->errors[] = "API connection failed with HTTP {$httpCode}";
                $this->testResults['api_connection'] = [
                    'status' => 'failed',
                    'http_code' => $httpCode
                ];
            }
            
        } catch (Exception $e) {
            echo "💥 API connection error: " . $e->getMessage() . "\n";
            $this->errors[] = "API connection error: " . $e->getMessage();
            $this->testResults['api_connection'] = ['status' => 'error', 'message' => $e->getMessage()];
        }
        
        echo "\n";
    }
    
    /**
     * Test 2: Webhook Registration Test
     */
    private function testWebhookRegistration() {
        echo "<h2>📝 Test 2: Webhook Registration</h2>\n";
        
        try {
            // Test webhook URL accessibility
            $this->testWebhookURLAccessibility();
            
            // Test webhook registration with Trendyol
            $this->testTrendyolWebhookRegistration();
            
        } catch (Exception $e) {
            echo "💥 Webhook registration error: " . $e->getMessage() . "\n";
            $this->errors[] = "Webhook registration error: " . $e->getMessage();
        }
        
        echo "\n";
    }
    
    /**
     * Test webhook URL accessibility
     */
    private function testWebhookURLAccessibility() {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->webhookEndpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode(['test' => true]),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'User-Agent: Trendyol-Webhook-Test'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 || $httpCode === 405) { // 405 is acceptable (method not allowed for test)
                echo "✅ Webhook URL is accessible (HTTP {$httpCode})\n";
                $this->testResults['webhook_accessibility'] = ['status' => 'success', 'http_code' => $httpCode];
            } else {
                echo "❌ Webhook URL not accessible (HTTP {$httpCode})\n";
                $this->errors[] = "Webhook URL not accessible (HTTP {$httpCode})";
                $this->testResults['webhook_accessibility'] = ['status' => 'failed', 'http_code' => $httpCode];
            }
            
        } catch (Exception $e) {
            echo "💥 Webhook URL test error: " . $e->getMessage() . "\n";
            $this->errors[] = "Webhook URL test error: " . $e->getMessage();
        }
    }
    
    /**
     * Test Trendyol webhook registration
     */
    private function testTrendyolWebhookRegistration() {
        // Note: This would require actual Trendyol API credentials and webhook management endpoint
        echo "ℹ️ Webhook registration with Trendyol requires live API credentials\n";
        echo "ℹ️ Manual registration required at: https://partner.trendyol.com/\n";
        
        $this->testResults['trendyol_registration'] = [
            'status' => 'manual_required',
            'webhook_url' => $this->webhookEndpoint,
            'secret_key' => $this->secretKey
        ];
    }
    
    /**
     * Test 3: Webhook Event Simulation
     */
    private function testWebhookEventSimulation() {
        echo "<h2>🎭 Test 3: Webhook Event Simulation</h2>\n";
        
        $eventTypes = [
            'ORDER_CREATED' => $this->generateOrderCreatedEvent(),
            'ORDER_UPDATED' => $this->generateOrderUpdatedEvent(),
            'PRODUCT_UPDATED' => $this->generateProductUpdatedEvent(),
            'INVENTORY_UPDATED' => $this->generateInventoryUpdatedEvent()
        ];
        
        foreach ($eventTypes as $eventType => $eventData) {
            $this->simulateWebhookEvent($eventType, $eventData);
        }
        
        echo "\n";
    }
    
    /**
     * Simulate webhook event
     */
    private function simulateWebhookEvent($eventType, $eventData) {
        try {
            echo "🔄 Simulating {$eventType} event...\n";
            
            $payload = json_encode($eventData);
            $signature = $this->generateWebhookSignature($payload);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->webhookEndpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Trendyol-Signature: ' . $signature,
                    'X-Trendyol-Event-Type: ' . $eventType,
                    'User-Agent: Trendyol-Webhook/1.0'
                ]
            ]);
            
            $startTime = microtime(true);
            $response = curl_exec($ch);
            $responseTime = microtime(true) - $startTime;
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            curl_close($ch);
            
            if ($httpCode === 200) {
                echo "✅ {$eventType} event processed successfully (" . number_format($responseTime * 1000, 2) . " ms)\n";
                $this->testResults['webhook_events'][$eventType] = [
                    'status' => 'success',
                    'response_time' => $responseTime * 1000,
                    'http_code' => $httpCode
                ];
            } else {
                echo "❌ {$eventType} event failed (HTTP {$httpCode})\n";
                $this->errors[] = "{$eventType} event failed (HTTP {$httpCode})";
                $this->testResults['webhook_events'][$eventType] = [
                    'status' => 'failed',
                    'http_code' => $httpCode
                ];
            }
            
        } catch (Exception $e) {
            echo "💥 {$eventType} simulation error: " . $e->getMessage() . "\n";
            $this->errors[] = "{$eventType} simulation error: " . $e->getMessage();
        }
    }
    
    /**
     * Test 4: Webhook Security Validation
     */
    private function testWebhookSecurity() {
        echo "<h2>🔒 Test 4: Webhook Security Validation</h2>\n";
        
        // Test 1: Valid signature
        $this->testValidSignature();
        
        // Test 2: Invalid signature
        $this->testInvalidSignature();
        
        // Test 3: Missing signature
        $this->testMissingSignature();
        
        // Test 4: Timestamp validation
        $this->testTimestampValidation();
        
        echo "\n";
    }
    
    /**
     * Test valid signature
     */
    private function testValidSignature() {
        try {
            $payload = json_encode(['test' => 'valid_signature', 'timestamp' => time()]);
            $signature = $this->generateWebhookSignature($payload);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->webhookEndpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Trendyol-Signature: ' . $signature,
                    'X-Trendyol-Event-Type: SECURITY_TEST'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                echo "✅ Valid signature accepted\n";
                $this->testResults['security']['valid_signature'] = 'success';
            } else {
                echo "❌ Valid signature rejected (HTTP {$httpCode})\n";
                $this->testResults['security']['valid_signature'] = 'failed';
            }
            
        } catch (Exception $e) {
            echo "💥 Valid signature test error: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Test invalid signature
     */
    private function testInvalidSignature() {
        try {
            $payload = json_encode(['test' => 'invalid_signature', 'timestamp' => time()]);
            $invalidSignature = 'invalid_signature_123';
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->webhookEndpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Trendyol-Signature: ' . $invalidSignature,
                    'X-Trendyol-Event-Type: SECURITY_TEST'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 401 || $httpCode === 403) {
                echo "✅ Invalid signature properly rejected (HTTP {$httpCode})\n";
                $this->testResults['security']['invalid_signature'] = 'success';
            } else {
                echo "❌ Invalid signature not properly rejected (HTTP {$httpCode})\n";
                $this->testResults['security']['invalid_signature'] = 'failed';
            }
            
        } catch (Exception $e) {
            echo "💥 Invalid signature test error: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Test missing signature
     */
    private function testMissingSignature() {
        try {
            $payload = json_encode(['test' => 'missing_signature', 'timestamp' => time()]);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->webhookEndpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Trendyol-Event-Type: SECURITY_TEST'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 401 || $httpCode === 403) {
                echo "✅ Missing signature properly rejected (HTTP {$httpCode})\n";
                $this->testResults['security']['missing_signature'] = 'success';
            } else {
                echo "❌ Missing signature not properly rejected (HTTP {$httpCode})\n";
                $this->testResults['security']['missing_signature'] = 'failed';
            }
            
        } catch (Exception $e) {
            echo "💥 Missing signature test error: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Test timestamp validation
     */
    private function testTimestampValidation() {
        try {
            // Test with old timestamp (should be rejected)
            $oldTimestamp = time() - 3600; // 1 hour ago
            $payload = json_encode(['test' => 'old_timestamp', 'timestamp' => $oldTimestamp]);
            $signature = $this->generateWebhookSignature($payload);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->webhookEndpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Trendyol-Signature: ' . $signature,
                    'X-Trendyol-Event-Type: SECURITY_TEST',
                    'X-Trendyol-Timestamp: ' . $oldTimestamp
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 401 || $httpCode === 403) {
                echo "✅ Old timestamp properly rejected (HTTP {$httpCode})\n";
                $this->testResults['security']['timestamp_validation'] = 'success';
            } else {
                echo "❌ Old timestamp not properly rejected (HTTP {$httpCode})\n";
                $this->testResults['security']['timestamp_validation'] = 'failed';
            }
            
        } catch (Exception $e) {
            echo "💥 Timestamp validation test error: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Test 5: Webhook Performance
     */
    private function testWebhookPerformance() {
        echo "<h2>⚡ Test 5: Webhook Performance</h2>\n";
        
        $performanceResults = [];
        
        // Test response times for different payload sizes
        $payloadSizes = [
            'small' => $this->generateSmallPayload(),
            'medium' => $this->generateMediumPayload(),
            'large' => $this->generateLargePayload()
        ];
        
        foreach ($payloadSizes as $size => $payload) {
            $this->testPayloadPerformance($size, $payload, $performanceResults);
        }
        
        // Test concurrent webhook processing
        $this->testConcurrentWebhooks($performanceResults);
        
        $this->testResults['performance'] = $performanceResults;
        
        echo "\n";
    }
    
    /**
     * Test payload performance
     */
    private function testPayloadPerformance($size, $payload, &$results) {
        try {
            $payloadJson = json_encode($payload);
            $signature = $this->generateWebhookSignature($payloadJson);
            
            $times = [];
            
            // Test 5 times for average
            for ($i = 0; $i < 5; $i++) {
                $startTime = microtime(true);
                
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $this->webhookEndpoint,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $payloadJson,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'X-Trendyol-Signature: ' . $signature,
                        'X-Trendyol-Event-Type: PERFORMANCE_TEST'
                    ]
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                $responseTime = microtime(true) - $startTime;
                $times[] = $responseTime * 1000; // Convert to milliseconds
            }
            
            $avgTime = array_sum($times) / count($times);
            $minTime = min($times);
            $maxTime = max($times);
            
            echo "✅ {$size} payload performance:\n";
            echo "   Average: " . number_format($avgTime, 2) . " ms\n";
            echo "   Min: " . number_format($minTime, 2) . " ms\n";
            echo "   Max: " . number_format($maxTime, 2) . " ms\n";
            echo "   Payload size: " . number_format(strlen($payloadJson) / 1024, 2) . " KB\n";
            
            $results[$size] = [
                'average_time' => $avgTime,
                'min_time' => $minTime,
                'max_time' => $maxTime,
                'payload_size_kb' => strlen($payloadJson) / 1024
            ];
            
        } catch (Exception $e) {
            echo "💥 {$size} payload performance test error: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Test concurrent webhook processing
     */
    private function testConcurrentWebhooks(&$results) {
        echo "🔄 Testing concurrent webhook processing...\n";
        
        // Note: This is a simplified version. Full implementation would use multi-threading
        $concurrentResults = [];
        $payload = json_encode($this->generateOrderCreatedEvent());
        $signature = $this->generateWebhookSignature($payload);
        
        $startTime = microtime(true);
        
        // Simulate 5 concurrent requests (sequential for simplicity)
        for ($i = 0; $i < 5; $i++) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->webhookEndpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Trendyol-Signature: ' . $signature,
                    'X-Trendyol-Event-Type: CONCURRENT_TEST'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $concurrentResults[] = $httpCode;
        }
        
        $totalTime = microtime(true) - $startTime;
        $successCount = count(array_filter($concurrentResults, function($code) { return $code === 200; }));
        
        echo "✅ Concurrent test results:\n";
        echo "   Total time: " . number_format($totalTime * 1000, 2) . " ms\n";
        echo "   Success rate: {$successCount}/5\n";
        
        $results['concurrent'] = [
            'total_time' => $totalTime * 1000,
            'success_count' => $successCount,
            'total_requests' => 5
        ];
    }
    
    /**
     * Generate webhook signature for security testing
     */
    private function generateWebhookSignature($payload) {
        return hash_hmac('sha256', $payload, $this->secretKey);
    }
    
    /**
     * Generate webhook secret key
     */
    private function generateWebhookSecret() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Generate test event data
     */
    private function generateOrderCreatedEvent() {
        return [
            'eventType' => 'ORDER_CREATED',
            'timestamp' => time(),
            'data' => [
                'orderNumber' => 'TY-TEST-' . time(),
                'orderDate' => date('c'),
                'status' => 'Created',
                'customerFirstName' => 'Test',
                'customerLastName' => 'Customer',
                'customerEmail' => 'test@example.com',
                'totalPrice' => 99.99,
                'currency' => 'TRY',
                'items' => [
                    [
                        'productId' => 'TEST-001',
                        'productName' => 'Test Product',
                        'quantity' => 1,
                        'price' => 99.99
                    ]
                ]
            ]
        ];
    }
    
    private function generateOrderUpdatedEvent() {
        return [
            'eventType' => 'ORDER_UPDATED',
            'timestamp' => time(),
            'data' => [
                'orderNumber' => 'TY-TEST-' . time(),
                'status' => 'Shipped',
                'trackingNumber' => 'TRACK-' . time()
            ]
        ];
    }
    
    private function generateProductUpdatedEvent() {
        return [
            'eventType' => 'PRODUCT_UPDATED',
            'timestamp' => time(),
            'data' => [
                'productId' => 'TEST-001',
                'barcode' => '1234567890123',
                'price' => 109.99,
                'stock' => 50
            ]
        ];
    }
    
    private function generateInventoryUpdatedEvent() {
        return [
            'eventType' => 'INVENTORY_UPDATED',
            'timestamp' => time(),
            'data' => [
                'productId' => 'TEST-001',
                'quantity' => 45
            ]
        ];
    }
    
    private function generateSmallPayload() {
        return ['test' => 'small', 'timestamp' => time()];
    }
    
    private function generateMediumPayload() {
        return [
            'test' => 'medium',
            'timestamp' => time(),
            'data' => array_fill(0, 100, 'test_data_' . uniqid())
        ];
    }
    
    private function generateLargePayload() {
        return [
            'test' => 'large',
            'timestamp' => time(),
            'data' => array_fill(0, 1000, 'large_test_data_' . uniqid())
        ];
    }
    
    /**
     * Test 6: Error Handling
     */
    private function testErrorHandling() {
        echo "<h2>🚨 Test 6: Error Handling</h2>\n";
        
        // Test malformed JSON
        $this->testMalformedJSON();
        
        // Test missing required fields
        $this->testMissingRequiredFields();
        
        // Test invalid event types
        $this->testInvalidEventTypes();
        
        echo "\n";
    }
    
    private function testMalformedJSON() {
        try {
            $malformedPayload = '{"test": "malformed", "timestamp":}'; // Invalid JSON
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->webhookEndpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $malformedPayload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Trendyol-Event-Type: MALFORMED_TEST'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 400) {
                echo "✅ Malformed JSON properly rejected (HTTP {$httpCode})\n";
                $this->testResults['error_handling']['malformed_json'] = 'success';
            } else {
                echo "❌ Malformed JSON not properly handled (HTTP {$httpCode})\n";
                $this->testResults['error_handling']['malformed_json'] = 'failed';
            }
            
        } catch (Exception $e) {
            echo "💥 Malformed JSON test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testMissingRequiredFields() {
        try {
            $incompletePayload = json_encode(['timestamp' => time()]); // Missing required fields
            $signature = $this->generateWebhookSignature($incompletePayload);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->webhookEndpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $incompletePayload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Trendyol-Signature: ' . $signature,
                    'X-Trendyol-Event-Type: INCOMPLETE_TEST'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 400) {
                echo "✅ Missing required fields properly rejected (HTTP {$httpCode})\n";
                $this->testResults['error_handling']['missing_fields'] = 'success';
            } else {
                echo "❌ Missing required fields not properly handled (HTTP {$httpCode})\n";
                $this->testResults['error_handling']['missing_fields'] = 'failed';
            }
            
        } catch (Exception $e) {
            echo "💥 Missing fields test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testInvalidEventTypes() {
        try {
            $payload = json_encode(['test' => 'invalid_event', 'timestamp' => time()]);
            $signature = $this->generateWebhookSignature($payload);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->webhookEndpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Trendyol-Signature: ' . $signature,
                    'X-Trendyol-Event-Type: INVALID_EVENT_TYPE'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 400 || $httpCode === 422) {
                echo "✅ Invalid event type properly rejected (HTTP {$httpCode})\n";
                $this->testResults['error_handling']['invalid_event_type'] = 'success';
            } else {
                echo "❌ Invalid event type not properly handled (HTTP {$httpCode})\n";
                $this->testResults['error_handling']['invalid_event_type'] = 'failed';
            }
            
        } catch (Exception $e) {
            echo "💥 Invalid event type test error: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Test 7: Webhook Configuration Management
     */
    private function testWebhookConfiguration() {
        echo "<h2>⚙️ Test 7: Webhook Configuration Management</h2>\n";
        
        // Test configuration endpoints
        $this->testGetWebhookConfiguration();
        $this->testUpdateWebhookConfiguration();
        
        echo "\n";
    }
    
    private function testGetWebhookConfiguration() {
        try {
            $configUrl = str_replace('/webhook', '/api', $this->webhookEndpoint) . '&action=getWebhookConfiguration';
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $configUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                echo "✅ Get webhook configuration successful (HTTP {$httpCode})\n";
                $this->testResults['configuration']['get_config'] = 'success';
            } else {
                echo "❌ Get webhook configuration failed (HTTP {$httpCode})\n";
                $this->testResults['configuration']['get_config'] = 'failed';
            }
            
        } catch (Exception $e) {
            echo "💥 Get configuration test error: " . $e->getMessage() . "\n";
        }
    }
    
    private function testUpdateWebhookConfiguration() {
        try {
            $configUrl = str_replace('/webhook', '/api', $this->webhookEndpoint) . '&action=saveWebhookConfiguration';
            $configData = json_encode([
                'events' => [
                    'ORDER_CREATED' => true,
                    'ORDER_UPDATED' => true,
                    'PRODUCT_UPDATED' => false,
                    'INVENTORY_UPDATED' => true
                ]
            ]);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $configUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $configData,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                echo "✅ Update webhook configuration successful (HTTP {$httpCode})\n";
                $this->testResults['configuration']['update_config'] = 'success';
            } else {
                echo "❌ Update webhook configuration failed (HTTP {$httpCode})\n";
                $this->testResults['configuration']['update_config'] = 'failed';
            }
            
        } catch (Exception $e) {
            echo "💥 Update configuration test error: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Display comprehensive integration test results
     */
    private function displayIntegrationResults() {
        echo "<h2>📊 Integration Test Results Summary</h2>\n";
        
        $totalTests = 0;
        $passedTests = 0;
        
        $this->countTestResults($this->testResults, $totalTests, $passedTests);
        
        $passRate = ($totalTests > 0) ? ($passedTests / $totalTests) * 100 : 0;
        
        echo "<div style='background: " . ($passRate >= 90 ? '#d4edda' : ($passRate >= 70 ? '#fff3cd' : '#f8d7da')) . "; padding: 15px; margin: 10px 0; border-radius: 5px;'>\n";
        echo "<strong>Overall Integration Test Results:</strong>\n";
        echo "✅ Passed: {$passedTests}/{$totalTests} (" . number_format($passRate, 1) . "%)\n";
        
        if (count($this->errors) > 0) {
            echo "\n❌ <strong>Issues Found:</strong>\n";
            foreach ($this->errors as $error) {
                echo "  • {$error}\n";
            }
        }
        
        echo "\n📋 <strong>Test Categories:</strong>\n";
        
        if (isset($this->testResults['api_connection'])) {
            $status = $this->testResults['api_connection']['status'];
            echo "  🔌 API Connection: " . ($status === 'success' ? '✅' : '❌') . " {$status}\n";
        }
        
        if (isset($this->testResults['webhook_accessibility'])) {
            $status = $this->testResults['webhook_accessibility']['status'];
            echo "  📝 Webhook URL: " . ($status === 'success' ? '✅' : '❌') . " {$status}\n";
        }
        
        if (isset($this->testResults['webhook_events'])) {
            $events = $this->testResults['webhook_events'];
            $successCount = count(array_filter($events, function($e) { return $e['status'] === 'success'; }));
            echo "  🎭 Event Processing: ✅ {$successCount}/" . count($events) . " events\n";
        }
        
        if (isset($this->testResults['security'])) {
            $security = $this->testResults['security'];
            $securityCount = count(array_filter($security, function($s) { return $s === 'success'; }));
            echo "  🔒 Security Tests: ✅ {$securityCount}/" . count($security) . " checks\n";
        }
        
        if (isset($this->testResults['performance'])) {
            echo "  ⚡ Performance: ✅ Response times measured\n";
        }
        
        echo "</div>\n";
        
        // Detailed results
        echo "\n<h3>📋 Detailed Test Results:</h3>\n";
        echo "<pre>" . json_encode($this->testResults, JSON_PRETTY_PRINT) . "</pre>\n";
        
        // Recommendations
        $this->generateRecommendations();
    }
    
    /**
     * Count test results recursively
     */
    private function countTestResults($results, &$totalTests, &$passedTests) {
        foreach ($results as $key => $value) {
            if (is_array($value)) {
                if (isset($value['status'])) {
                    $totalTests++;
                    if (in_array($value['status'], ['success'])) {
                        $passedTests++;
                    }
                } else {
                    $this->countTestResults($value, $totalTests, $passedTests);
                }
            } else {
                $totalTests++;
                if (in_array($value, ['success'])) {
                    $passedTests++;
                }
            }
        }
    }
    
    /**
     * Generate recommendations based on test results
     */
    private function generateRecommendations() {
        echo "<h3>💡 Recommendations:</h3>\n";
        
        if (isset($this->testResults['api_connection']['status']) && $this->testResults['api_connection']['status'] !== 'success') {
            echo "🔧 Fix API connection issues before proceeding with webhook testing\n";
        }
        
        if (isset($this->testResults['webhook_accessibility']['status']) && $this->testResults['webhook_accessibility']['status'] !== 'success') {
            echo "🔧 Ensure webhook URL is publicly accessible and SSL is properly configured\n";
        }
        
        if (count($this->errors) > 0) {
            echo "🔧 Address the following errors:\n";
            foreach (array_slice($this->errors, 0, 5) as $error) {
                echo "  • {$error}\n";
            }
        }
        
        if (isset($this->testResults['performance']['small']['average_time']) && $this->testResults['performance']['small']['average_time'] > 1000) {
            echo "⚡ Consider optimizing webhook response time (current: " . number_format($this->testResults['performance']['small']['average_time'], 2) . " ms)\n";
        }
        
        echo "📚 For production deployment:\n";
        echo "  • Register webhook URL with Trendyol Partner Center\n";
        echo "  • Configure SSL certificate for webhook endpoint\n";
        echo "  • Set up monitoring and alerting for webhook failures\n";
        echo "  • Implement rate limiting and retry logic\n";
        echo "  • Test with production data before going live\n";
    }
    
    /**
     * Get test results as array
     */
    public function getTestResults() {
        return [
            'results' => $this->testResults,
            'errors' => $this->errors,
            'config' => $this->config,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

// Usage example
/*
$config = [
    'webhook_url' => 'https://yourdomain.com/index.php?route=extension/module/trendyol/webhook',
    'supplier_id' => 'your_supplier_id',
    'api_key' => 'your_api_key',
    'api_secret' => 'your_api_secret',
    'test_mode' => true
];

$tester = new TrendyolWebhookIntegrationTest($config);
$results = $tester->runWebhookIntegrationTests();

// Save results
file_put_contents('trendyol_integration_test_' . date('Y-m-d_H-i-s') . '.log', 
    json_encode($tester->getTestResults(), JSON_PRETTY_PRINT));
*/
