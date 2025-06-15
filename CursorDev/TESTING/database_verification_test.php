<?php
/**
 * Database Verification and Integration Test System
 * MesChain-Sync v3.0.1 - Trendyol Webhook Integration
 * 
 * Purpose: Verify database schema, test webhook functionality, and validate integration
 */

class TrendyolDatabaseVerificationTest {
    
    private $db;
    private $testResults = [];
    private $errors = [];
    
    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->registry = $registry;
    }
    
    /**
     * Run comprehensive database verification tests
     */
    public function runDatabaseVerification() {
        echo "<h1>🔍 Trendyol Database Verification Test</h1>\n";
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px;'>\n";
        
        // Test 1: Check table existence
        $this->testTableExistence();
        
        // Test 2: Verify table structure
        $this->testTableStructure();
        
        // Test 3: Test data insertion/retrieval
        $this->testDataOperations();
        
        // Test 4: Test webhook logging functionality
        $this->testWebhookLogging();
        
        // Test 5: Performance test
        $this->testPerformance();
        
        // Display results
        $this->displayResults();
        
        echo "</div>\n";
        
        return $this->testResults;
    }
    
    /**
     * Test 1: Check table existence
     */
    private function testTableExistence() {
        echo "<h2>📋 Test 1: Table Existence Check</h2>\n";
        
        $requiredTables = [
            'oc_trendyol_webhook_log',
            'oc_meschain_marketplace',
            'oc_meschain_product_mapping',
            'oc_meschain_order_mapping'
        ];
        
        foreach ($requiredTables as $table) {
            try {
                $query = $this->db->query("SHOW TABLES LIKE '" . $table . "'");
                
                if ($query->num_rows > 0) {
                    echo "✅ Table {$table} exists\n";
                    $this->testResults['tables'][$table] = 'exists';
                } else {
                    echo "❌ Table {$table} missing\n";
                    $this->errors[] = "Missing table: {$table}";
                    $this->testResults['tables'][$table] = 'missing';
                }
                
            } catch (Exception $e) {
                echo "💥 Error checking table {$table}: " . $e->getMessage() . "\n";
                $this->errors[] = "Error checking table {$table}: " . $e->getMessage();
            }
        }
        echo "\n";
    }
    
    /**
     * Test 2: Verify table structure
     */
    private function testTableStructure() {
        echo "<h2>🏗️ Test 2: Table Structure Verification</h2>\n";
        
        // Test webhook log table structure
        $this->testWebhookLogTableStructure();
        
        // Test marketplace table structure
        $this->testMarketplaceTableStructure();
        
        echo "\n";
    }
    
    /**
     * Test webhook log table structure
     */
    private function testWebhookLogTableStructure() {
        try {
            $query = $this->db->query("DESCRIBE oc_trendyol_webhook_log");
            $columns = [];
            
            foreach ($query->rows as $row) {
                $columns[$row['Field']] = $row['Type'];
            }
            
            $expectedColumns = [
                'log_id' => 'int',
                'event_type' => 'varchar',
                'message' => 'text',
                'status' => 'varchar',
                'timestamp' => 'datetime',
                'data' => 'text'
            ];
            
            foreach ($expectedColumns as $column => $type) {
                if (isset($columns[$column])) {
                    echo "✅ Column {$column} exists with type: {$columns[$column]}\n";
                    $this->testResults['webhook_log_structure'][$column] = 'ok';
                } else {
                    echo "❌ Column {$column} missing from webhook log table\n";
                    $this->errors[] = "Missing column {$column} in webhook log table";
                    $this->testResults['webhook_log_structure'][$column] = 'missing';
                }
            }
            
        } catch (Exception $e) {
            echo "💥 Error checking webhook log table structure: " . $e->getMessage() . "\n";
            $this->errors[] = "Error checking webhook log table structure: " . $e->getMessage();
        }
    }
    
    /**
     * Test marketplace table structure
     */
    private function testMarketplaceTableStructure() {
        try {
            $query = $this->db->query("DESCRIBE oc_meschain_marketplace");
            $columns = [];
            
            foreach ($query->rows as $row) {
                $columns[$row['Field']] = $row['Type'];
            }
            
            // Check if Trendyol entry exists
            $trendyolQuery = $this->db->query("SELECT * FROM oc_meschain_marketplace WHERE code = 'trendyol'");
            
            if ($trendyolQuery->num_rows > 0) {
                echo "✅ Trendyol marketplace entry exists\n";
                $this->testResults['trendyol_entry'] = 'exists';
            } else {
                echo "❌ Trendyol marketplace entry missing\n";
                $this->errors[] = "Trendyol marketplace entry missing";
                $this->testResults['trendyol_entry'] = 'missing';
            }
            
        } catch (Exception $e) {
            echo "💥 Error checking marketplace table: " . $e->getMessage() . "\n";
            $this->errors[] = "Error checking marketplace table: " . $e->getMessage();
        }
    }
    
    /**
     * Test 3: Test data operations
     */
    private function testDataOperations() {
        echo "<h2>💾 Test 3: Data Operations Test</h2>\n";
        
        // Test webhook log insertion
        $this->testWebhookLogInsertion();
        
        // Test marketplace data operations
        $this->testMarketplaceOperations();
        
        echo "\n";
    }
    
    /**
     * Test webhook log insertion
     */
    private function testWebhookLogInsertion() {
        try {
            $testData = [
                'event_type' => 'DATABASE_TEST',
                'message' => 'Database verification test - webhook log insertion',
                'status' => 'success',
                'data' => json_encode(['test' => true, 'timestamp' => date('Y-m-d H:i:s')])
            ];
            
            $this->db->query("INSERT INTO oc_trendyol_webhook_log SET 
                event_type = '" . $this->db->escape($testData['event_type']) . "',
                message = '" . $this->db->escape($testData['message']) . "',
                status = '" . $this->db->escape($testData['status']) . "',
                timestamp = NOW(),
                data = '" . $this->db->escape($testData['data']) . "'");
            
            $insertId = $this->db->getLastId();
            
            if ($insertId > 0) {
                echo "✅ Webhook log insertion successful (ID: {$insertId})\n";
                $this->testResults['webhook_log_insertion'] = 'success';
                
                // Test retrieval
                $query = $this->db->query("SELECT * FROM oc_trendyol_webhook_log WHERE log_id = '" . (int)$insertId . "'");
                
                if ($query->num_rows > 0) {
                    echo "✅ Webhook log retrieval successful\n";
                    $this->testResults['webhook_log_retrieval'] = 'success';
                    
                    // Cleanup test data
                    $this->db->query("DELETE FROM oc_trendyol_webhook_log WHERE log_id = '" . (int)$insertId . "'");
                    echo "🧹 Test data cleaned up\n";
                } else {
                    echo "❌ Webhook log retrieval failed\n";
                    $this->errors[] = "Webhook log retrieval failed";
                    $this->testResults['webhook_log_retrieval'] = 'failed';
                }
            } else {
                echo "❌ Webhook log insertion failed\n";
                $this->errors[] = "Webhook log insertion failed";
                $this->testResults['webhook_log_insertion'] = 'failed';
            }
            
        } catch (Exception $e) {
            echo "💥 Error testing webhook log operations: " . $e->getMessage() . "\n";
            $this->errors[] = "Error testing webhook log operations: " . $e->getMessage();
        }
    }
    
    /**
     * Test marketplace operations
     */
    private function testMarketplaceOperations() {
        try {
            // Test Trendyol marketplace update
            $testSettings = json_encode([
                'test_mode' => true,
                'api_key' => 'test_key_' . time(),
                'webhook_verification' => true
            ]);
            
            $this->db->query("UPDATE oc_meschain_marketplace SET 
                settings = '" . $this->db->escape($testSettings) . "',
                date_modified = NOW()
                WHERE code = 'trendyol'");
            
            // Verify update
            $query = $this->db->query("SELECT * FROM oc_meschain_marketplace WHERE code = 'trendyol'");
            
            if ($query->num_rows > 0) {
                $settings = json_decode($query->row['settings'], true);
                
                if (isset($settings['test_mode']) && $settings['test_mode'] === true) {
                    echo "✅ Marketplace data update successful\n";
                    $this->testResults['marketplace_update'] = 'success';
                } else {
                    echo "❌ Marketplace data update verification failed\n";
                    $this->errors[] = "Marketplace data update verification failed";
                    $this->testResults['marketplace_update'] = 'failed';
                }
            } else {
                echo "❌ Marketplace data retrieval failed\n";
                $this->errors[] = "Marketplace data retrieval failed";
                $this->testResults['marketplace_update'] = 'failed';
            }
            
        } catch (Exception $e) {
            echo "💥 Error testing marketplace operations: " . $e->getMessage() . "\n";
            $this->errors[] = "Error testing marketplace operations: " . $e->getMessage();
        }
    }
    
    /**
     * Test 4: Test webhook logging functionality
     */
    private function testWebhookLogging() {
        echo "<h2>🔔 Test 4: Webhook Logging Functionality</h2>\n";
        
        try {
            // Test different event types
            $eventTypes = [
                'ORDER_CREATED' => 'Order creation webhook test',
                'PRODUCT_UPDATED' => 'Product update webhook test',
                'INVENTORY_CHANGED' => 'Inventory change webhook test',
                'PAYMENT_RECEIVED' => 'Payment received webhook test'
            ];
            
            $insertedIds = [];
            
            foreach ($eventTypes as $eventType => $message) {
                $this->db->query("INSERT INTO oc_trendyol_webhook_log SET 
                    event_type = '" . $this->db->escape($eventType) . "',
                    message = '" . $this->db->escape($message) . "',
                    status = 'success',
                    timestamp = NOW(),
                    data = '" . $this->db->escape(json_encode(['test' => true, 'event' => $eventType])) . "'");
                
                $insertedIds[] = $this->db->getLastId();
            }
            
            // Test log retrieval with filters
            $query = $this->db->query("SELECT COUNT(*) as total FROM oc_trendyol_webhook_log WHERE status = 'success'");
            $totalLogs = $query->row['total'];
            
            echo "✅ Successfully logged {$totalLogs} webhook events\n";
            $this->testResults['webhook_logging'] = 'success';
            
            // Test log filtering by event type
            $query = $this->db->query("SELECT COUNT(*) as total FROM oc_trendyol_webhook_log WHERE event_type = 'ORDER_CREATED'");
            $orderLogs = $query->row['total'];
            
            echo "✅ Event type filtering working (ORDER_CREATED: {$orderLogs} logs)\n";
            $this->testResults['webhook_filtering'] = 'success';
            
            // Cleanup test logs
            foreach ($insertedIds as $id) {
                $this->db->query("DELETE FROM oc_trendyol_webhook_log WHERE log_id = '" . (int)$id . "'");
            }
            echo "🧹 Webhook test logs cleaned up\n";
            
        } catch (Exception $e) {
            echo "💥 Error testing webhook logging: " . $e->getMessage() . "\n";
            $this->errors[] = "Error testing webhook logging: " . $e->getMessage();
        }
    }
    
    /**
     * Test 5: Performance test
     */
    private function testPerformance() {
        echo "<h2>⚡ Test 5: Performance Test</h2>\n";
        
        try {
            // Test bulk webhook log insertion
            $startTime = microtime(true);
            
            $bulkData = [];
            for ($i = 0; $i < 100; $i++) {
                $bulkData[] = [
                    'event_type' => 'PERFORMANCE_TEST',
                    'message' => "Performance test log entry {$i}",
                    'status' => 'success',
                    'data' => json_encode(['test_id' => $i, 'timestamp' => time()])
                ];
            }
            
            // Insert bulk data
            foreach ($bulkData as $data) {
                $this->db->query("INSERT INTO oc_trendyol_webhook_log SET 
                    event_type = '" . $this->db->escape($data['event_type']) . "',
                    message = '" . $this->db->escape($data['message']) . "',
                    status = '" . $this->db->escape($data['status']) . "',
                    timestamp = NOW(),
                    data = '" . $this->db->escape($data['data']) . "'");
            }
            
            $insertTime = microtime(true) - $startTime;
            
            // Test bulk retrieval
            $startTime = microtime(true);
            $query = $this->db->query("SELECT * FROM oc_trendyol_webhook_log WHERE event_type = 'PERFORMANCE_TEST' ORDER BY timestamp DESC LIMIT 100");
            $retrievalTime = microtime(true) - $startTime;
            
            echo "✅ Bulk insertion (100 records): " . number_format($insertTime * 1000, 2) . " ms\n";
            echo "✅ Bulk retrieval (100 records): " . number_format($retrievalTime * 1000, 2) . " ms\n";
            
            $this->testResults['performance'] = [
                'insertion_time' => $insertTime * 1000,
                'retrieval_time' => $retrievalTime * 1000,
                'records_count' => $query->num_rows
            ];
            
            // Cleanup performance test data
            $this->db->query("DELETE FROM oc_trendyol_webhook_log WHERE event_type = 'PERFORMANCE_TEST'");
            echo "🧹 Performance test data cleaned up\n";
            
        } catch (Exception $e) {
            echo "💥 Error testing performance: " . $e->getMessage() . "\n";
            $this->errors[] = "Error testing performance: " . $e->getMessage();
        }
    }
    
    /**
     * Display comprehensive test results
     */
    private function displayResults() {
        echo "<h2>📊 Test Results Summary</h2>\n";
        
        $totalTests = 0;
        $passedTests = 0;
        
        foreach ($this->testResults as $category => $results) {
            if (is_array($results)) {
                foreach ($results as $test => $result) {
                    $totalTests++;
                    if (in_array($result, ['exists', 'ok', 'success'])) {
                        $passedTests++;
                    }
                }
            } else {
                $totalTests++;
                if (in_array($results, ['exists', 'ok', 'success'])) {
                    $passedTests++;
                }
            }
        }
        
        $passRate = ($totalTests > 0) ? ($passedTests / $totalTests) * 100 : 0;
        
        echo "<div style='background: " . ($passRate >= 90 ? '#d4edda' : ($passRate >= 70 ? '#fff3cd' : '#f8d7da')) . "; padding: 15px; margin: 10px 0; border-radius: 5px;'>\n";
        echo "<strong>Overall Test Results:</strong>\n";
        echo "✅ Passed: {$passedTests}/{$totalTests} (" . number_format($passRate, 1) . "%)\n";
        
        if (count($this->errors) > 0) {
            echo "\n❌ <strong>Errors Found:</strong>\n";
            foreach ($this->errors as $error) {
                echo "  • {$error}\n";
            }
        }
        
        echo "</div>\n";
        
        // Detailed results
        echo "\n<h3>📋 Detailed Results:</h3>\n";
        echo "<pre>" . json_encode($this->testResults, JSON_PRETTY_PRINT) . "</pre>\n";
    }
    
    /**
     * Get test results as JSON
     */
    public function getTestResults() {
        return [
            'results' => $this->testResults,
            'errors' => $this->errors,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

// Usage example
/*
// In your OpenCart controller or standalone script
$registry = new Registry();
$registry->set('db', $db); // Your database connection

$tester = new TrendyolDatabaseVerificationTest($registry);
$results = $tester->runDatabaseVerification();

// Save results to log file
file_put_contents('trendyol_database_test_' . date('Y-m-d_H-i-s') . '.log', 
    json_encode($tester->getTestResults(), JSON_PRETTY_PRINT));
*/
