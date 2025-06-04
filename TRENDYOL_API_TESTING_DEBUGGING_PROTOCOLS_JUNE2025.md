# 🧪 TRENDYOL API TESTING & DEBUGGING PROTOCOLS
**MesChain-Sync OpenCart Extension - Practical Testing Guide**

## 🎯 **TESTING EXECUTION PLAN**

### **Phase 1: Pre-Production Testing (Week 1)**

#### **🔧 Environment Setup**
```bash
# 1. Verify API Credentials
php -r "
require_once 'system/library/meschain/helper/trendyol.php';
\$registry = new Registry();
\$helper = new MeschainTrendyolHelper(\$registry);
\$health = \$helper->healthCheck();
echo json_encode(\$health, JSON_PRETTY_PRINT);
"

# Expected Output:
# {
#     "status": "healthy",
#     "message": "API bağlantısı başarılı", 
#     "response_time": 0.234,
#     "supplier_id": "12345"
# }
```

#### **🚦 API Connectivity Tests**
```php
<?php
// tests/TrendyolConnectivityTest.php

class TrendyolConnectivityTest {
    private $helper;
    
    public function testBasicConnectivity() {
        $health = $this->helper->healthCheck();
        assert($health['status'] === 'healthy', 'API connectivity failed');
        assert($health['response_time'] < 2.0, 'Response time too slow');
        echo "✅ Basic connectivity test passed\n";
    }
    
    public function testRateLimiting() {
        $startTime = time();
        $requestCount = 0;
        
        // Test rate limiting (100 requests/minute)
        for ($i = 0; $i < 105; $i++) {
            try {
                $this->helper->healthCheck();
                $requestCount++;
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Rate limit') !== false) {
                    echo "✅ Rate limiting working correctly after {$requestCount} requests\n";
                    return;
                }
            }
        }
        
        echo "⚠️ Rate limiting may not be working properly\n";
    }
    
    public function testErrorHandling() {
        // Test with invalid credentials
        $oldCredentials = $this->helper->getApiCredentials();
        
        // Set invalid credentials temporarily
        $this->helper->setTestCredentials('invalid', 'invalid');
        
        $health = $this->helper->healthCheck();
        assert($health['status'] === 'error', 'Error handling failed');
        echo "✅ Error handling test passed\n";
        
        // Restore credentials
        $this->helper->restoreCredentials($oldCredentials);
    }
}
?>
```

### **Phase 2: Data Synchronization Testing (Week 2)**

#### **📦 Product Sync Testing**
```php
<?php
// tests/TrendyolProductSyncTest.php

class TrendyolProductSyncTest {
    
    public function testSingleProductSync() {
        // Create test product
        $testProduct = [
            'product_id' => 999999,
            'name' => 'Test Product - Trendyol Integration',
            'model' => 'TEST-TY-001',
            'price' => 99.99,
            'quantity' => 10,
            'status' => 1
        ];
        
        // Insert test product
        $this->db->query("INSERT INTO oc_product SET ...");
        
        // Test sync
        $result = $this->helper->syncProducts([999999]);
        
        assert($result['success'] === true, 'Product sync failed');
        assert($result['synced_count'] === 1, 'Sync count mismatch');
        assert($result['error_count'] === 0, 'Unexpected errors');
        
        // Verify in database
        $mapping = $this->db->query("SELECT * FROM oc_trendyol_products WHERE opencart_product_id = 999999");
        assert($mapping->num_rows === 1, 'Product mapping not created');
        
        echo "✅ Single product sync test passed\n";
        
        // Cleanup
        $this->cleanupTestProduct(999999);
    }
    
    public function testBulkProductSync() {
        // Create 100 test products
        $productIds = [];
        for ($i = 0; $i < 100; $i++) {
            $productId = 900000 + $i;
            $productIds[] = $productId;
            // Insert test products...
        }
        
        $startTime = microtime(true);
        $result = $this->helper->syncProducts($productIds);
        $endTime = microtime(true);
        
        $syncTime = $endTime - $startTime;
        $rate = count($productIds) / $syncTime;
        
        assert($result['success'] === true, 'Bulk sync failed');
        assert($rate > 10, 'Sync rate too slow (target: >10 products/second)');
        
        echo "✅ Bulk sync test passed: {$rate} products/second\n";
        
        // Cleanup
        foreach ($productIds as $productId) {
            $this->cleanupTestProduct($productId);
        }
    }
    
    public function testProductValidation() {
        // Test invalid product data
        $invalidProduct = [
            'product_id' => 999998,
            'name' => '', // Empty name should fail
            'price' => -10, // Negative price should fail
            'quantity' => 'invalid' // Invalid quantity
        ];
        
        $this->db->query("INSERT INTO oc_product SET ...");
        
        $result = $this->helper->syncProducts([999998]);
        
        assert($result['error_count'] > 0, 'Validation should have failed');
        echo "✅ Product validation test passed\n";
        
        $this->cleanupTestProduct(999998);
    }
}
?>
```

#### **📋 Order Sync Testing**
```php
<?php
// tests/TrendyolOrderSyncTest.php

class TrendyolOrderSyncTest {
    
    public function testOrderRetrieval() {
        // Test recent orders (last 7 days)
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $result = $this->helper->syncOrders(null, $startDate, $endDate);
        
        assert($result['success'] === true, 'Order sync failed');
        assert(is_numeric($result['synced_count']), 'Invalid sync count');
        
        echo "✅ Order retrieval test passed: {$result['synced_count']} orders\n";
    }
    
    public function testOrderMapping() {
        // Create mock Trendyol order
        $mockOrder = [
            'orderNumber' => 'TY-TEST-' . time(),
            'grossAmount' => 149.99,
            'status' => 'Created',
            'orderDate' => date('Y-m-d H:i:s'),
            'lines' => [[
                'barcode' => 'TEST-BARCODE-001',
                'quantity' => 2,
                'unitPrice' => 74.99
            ]]
        ];
        
        // Process order
        $this->helper->processTrendyolOrder($mockOrder);
        
        // Verify in database
        $order = $this->db->query("SELECT * FROM oc_trendyol_orders WHERE order_number = '{$mockOrder['orderNumber']}'");
        assert($order->num_rows === 1, 'Order not saved to database');
        
        echo "✅ Order mapping test passed\n";
        
        // Cleanup
        $this->db->query("DELETE FROM oc_trendyol_orders WHERE order_number = '{$mockOrder['orderNumber']}'");
    }
}
?>
```

### **Phase 3: Webhook Testing (Week 3)**

#### **🔔 Webhook Processing Tests**
```php
<?php
// tests/TrendyolWebhookTest.php

class TrendyolWebhookTest {
    
    public function testOrderWebhook() {
        $webhookData = [
            'orderNumber' => 'TY-WEBHOOK-' . time(),
            'status' => 'Created',
            'eventType' => 'ORDER_CREATED',
            'timestamp' => date('c')
        ];
        
        $result = $this->helper->processWebhook('ORDER_CREATED', $webhookData);
        assert($result === true, 'Webhook processing failed');
        
        // Verify webhook was logged
        $webhook = $this->db->query("SELECT * FROM oc_trendyol_webhooks WHERE event_type = 'ORDER_CREATED' ORDER BY received_at DESC LIMIT 1");
        assert($webhook->num_rows === 1, 'Webhook not logged');
        assert($webhook->row['processed'] === '1', 'Webhook not marked as processed');
        
        echo "✅ Order webhook test passed\n";
    }
    
    public function testProductWebhook() {
        $webhookData = [
            'barcode' => 'TEST-WEBHOOK-' . time(),
            'status' => 'Approved',
            'contentId' => '12345',
            'eventType' => 'PRODUCT_APPROVED'
        ];
        
        $result = $this->helper->processWebhook('PRODUCT_APPROVED', $webhookData);
        assert($result === true, 'Product webhook processing failed');
        
        echo "✅ Product webhook test passed\n";
    }
    
    public function testWebhookResilience() {
        // Test malformed webhook data
        $malformedData = [
            'invalid' => 'data',
            'missing' => 'required_fields'
        ];
        
        try {
            $this->helper->processWebhook('INVALID_EVENT', $malformedData);
            assert(false, 'Should have thrown exception');
        } catch (Exception $e) {
            echo "✅ Webhook resilience test passed: " . $e->getMessage() . "\n";
        }
    }
}
?>
```

---

## 🐛 **DEBUGGING PROTOCOLS**

### **🔍 Debug Mode Activation**
```php
// Enable debug mode in helper
class MeschainTrendyolHelper {
    private $debugMode = true; // Set to true for debugging
    
    private function debugLog($message, $data = null) {
        if ($this->debugMode) {
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[{$timestamp}] [DEBUG] {$message}";
            
            if ($data) {
                $logMessage .= "\nData: " . json_encode($data, JSON_PRETTY_PRINT);
            }
            
            file_put_contents(DIR_LOGS . 'trendyol_debug.log', $logMessage . "\n", FILE_APPEND);
        }
    }
}
```

### **📊 Performance Monitoring**
```php
<?php
// monitoring/TrendyolPerformanceMonitor.php

class TrendyolPerformanceMonitor {
    
    public function generateDailyReport() {
        $today = date('Y-m-d');
        
        // API call statistics
        $apiStats = $this->db->query("
            SELECT 
                endpoint,
                COUNT(*) as total_calls,
                AVG(response_time) as avg_response_time,
                MAX(response_time) as max_response_time,
                COUNT(CASE WHEN success = 1 THEN 1 END) as successful_calls,
                COUNT(CASE WHEN success = 0 THEN 1 END) as failed_calls
            FROM oc_trendyol_api_logs 
            WHERE DATE(created_at) = '{$today}'
            GROUP BY endpoint
            ORDER BY total_calls DESC
        ");
        
        echo "📈 Trendyol API Daily Report - {$today}\n";
        echo "=" . str_repeat("=", 50) . "\n";
        
        foreach ($apiStats->rows as $stat) {
            $successRate = round(($stat['successful_calls'] / $stat['total_calls']) * 100, 2);
            
            echo "Endpoint: {$stat['endpoint']}\n";
            echo "  Total Calls: {$stat['total_calls']}\n";
            echo "  Success Rate: {$successRate}%\n";
            echo "  Avg Response Time: " . round($stat['avg_response_time'], 3) . "s\n";
            echo "  Max Response Time: " . round($stat['max_response_time'], 3) . "s\n";
            echo "\n";
            
            // Alert if performance is degraded
            if ($stat['avg_response_time'] > 2.0) {
                echo "⚠️  WARNING: Slow response time detected!\n";
            }
            
            if ($successRate < 95) {
                echo "🚨 ALERT: Low success rate detected!\n";
            }
        }
        
        // Sync statistics
        $syncStats = $this->db->query("
            SELECT 
                sync_status,
                COUNT(*) as count
            FROM oc_trendyol_products 
            WHERE DATE(updated_at) = '{$today}'
            GROUP BY sync_status
        ");
        
        echo "📦 Product Sync Report - {$today}\n";
        echo "=" . str_repeat("=", 30) . "\n";
        
        foreach ($syncStats->rows as $stat) {
            echo "{$stat['sync_status']}: {$stat['count']} products\n";
        }
    }
    
    public function checkRateLimitStatus() {
        $cache = $this->registry->get('cache');
        
        $endpoints = ['products', 'orders', 'default'];
        
        echo "🚦 Rate Limit Status\n";
        echo "=" . str_repeat("=", 20) . "\n";
        
        foreach ($endpoints as $endpoint) {
            $cacheKey = "trendyol_rate_limit_{$endpoint}";
            $currentCount = $cache->get($cacheKey) ?? 0;
            $limit = $this->rateLimits[$endpoint]['requests'] ?? 100;
            
            $percentage = round(($currentCount / $limit) * 100, 1);
            $status = $percentage > 80 ? '🔴' : ($percentage > 60 ? '🟡' : '🟢');
            
            echo "{$status} {$endpoint}: {$currentCount}/{$limit} ({$percentage}%)\n";
        }
    }
}
?>
```

### **🛠️ Troubleshooting Tools**
```bash
#!/bin/bash
# scripts/trendyol_troubleshoot.sh

echo "🔍 Trendyol Integration Troubleshooting"
echo "======================================"

# Check API connectivity
echo "1. Testing API connectivity..."
php -r "
require_once 'system/library/meschain/helper/trendyol.php';
\$registry = new Registry();
\$helper = new MeschainTrendyolHelper(\$registry);
\$health = \$helper->healthCheck();
if (\$health['status'] === 'healthy') {
    echo '✅ API connectivity OK\n';
} else {
    echo '❌ API connectivity FAILED: ' . \$health['message'] . '\n';
}
"

# Check database tables
echo "2. Checking database tables..."
mysql -u$DB_USER -p$DB_PASS $DB_NAME -e "
SELECT 
    'trendyol_products' as table_name,
    COUNT(*) as total_records,
    COUNT(CASE WHEN sync_status = 'synced' THEN 1 END) as synced,
    COUNT(CASE WHEN sync_status = 'error' THEN 1 END) as errors
FROM oc_trendyol_products
UNION ALL
SELECT 
    'trendyol_orders' as table_name,
    COUNT(*) as total_records,
    COUNT(CASE WHEN sync_status = 'synced' THEN 1 END) as synced,
    COUNT(CASE WHEN sync_status = 'error' THEN 1 END) as errors
FROM oc_trendyol_orders;
"

# Check recent API logs
echo "3. Checking recent API activity..."
mysql -u$DB_USER -p$DB_PASS $DB_NAME -e "
SELECT 
    created_at,
    endpoint,
    method,
    http_status,
    response_time,
    CASE WHEN success = 1 THEN '✅' ELSE '❌' END as status
FROM oc_trendyol_api_logs 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY created_at DESC 
LIMIT 10;
"

# Check error patterns
echo "4. Analyzing error patterns..."
mysql -u$DB_USER -p$DB_PASS $DB_NAME -e "
SELECT 
    endpoint,
    COUNT(*) as error_count,
    error_message
FROM oc_trendyol_api_logs 
WHERE success = 0 
AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY endpoint, error_message
ORDER BY error_count DESC;
"

echo "Troubleshooting complete!"
```

---

## 📊 **AUTOMATED TEST SUITE**

### **🚀 Continuous Integration Setup**
```yaml
# .github/workflows/trendyol-tests.yml
name: Trendyol Integration Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: opencart_test
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: curl, json, mysqli
        
    - name: Install dependencies
      run: composer install --no-progress --no-suggest --prefer-dist --optimize-autoloader
      
    - name: Setup database
      run: |
        mysql -h 127.0.0.1 -u root -proot opencart_test < tests/sql/schema.sql
        
    - name: Run Trendyol Tests
      env:
        TRENDYOL_API_KEY: ${{ secrets.TRENDYOL_API_KEY }}
        TRENDYOL_API_SECRET: ${{ secrets.TRENDYOL_API_SECRET }}
        TRENDYOL_SUPPLIER_ID: ${{ secrets.TRENDYOL_SUPPLIER_ID }}
      run: |
        php tests/TrendyolConnectivityTest.php
        php tests/TrendyolProductSyncTest.php
        php tests/TrendyolOrderSyncTest.php
        php tests/TrendyolWebhookTest.php
        
    - name: Generate Test Report
      run: php tests/generateReport.php
```

### **📋 Test Results Dashboard**
```html
<!DOCTYPE html>
<html>
<head>
    <title>Trendyol Integration Test Results</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard">
        <h1>🧪 Trendyol Integration Test Dashboard</h1>
        
        <div class="metrics">
            <div class="metric">
                <h3>API Connectivity</h3>
                <div id="connectivity-status">🟢 Healthy</div>
                <small>Response Time: 234ms</small>
            </div>
            
            <div class="metric">
                <h3>Product Sync</h3>
                <div id="sync-status">✅ 98.5% Success</div>
                <small>Last Sync: 2 mins ago</small>
            </div>
            
            <div class="metric">
                <h3>Webhook Processing</h3>
                <div id="webhook-status">⚡ Real-time</div>
                <small>Queue: 0 pending</small>
            </div>
        </div>
        
        <canvas id="performanceChart" width="400" height="200"></canvas>
    </div>
    
    <script>
        // Real-time performance monitoring chart
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [], // Time labels
                datasets: [{
                    label: 'API Response Time (ms)',
                    data: [],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 2000 // 2 seconds max
                    }
                }
            }
        });
        
        // Update chart every 30 seconds
        setInterval(() => {
            fetch('/admin/extension/module/trendyol/api/health')
                .then(response => response.json())
                .then(data => {
                    const now = new Date().toLocaleTimeString();
                    chart.data.labels.push(now);
                    chart.data.datasets[0].data.push(data.response_time * 1000);
                    
                    // Keep only last 20 data points
                    if (chart.data.labels.length > 20) {
                        chart.data.labels.shift();
                        chart.data.datasets[0].data.shift();
                    }
                    
                    chart.update();
                });
        }, 30000);
    </script>
</body>
</html>
```

---

## 🎯 **TESTING CHECKLIST**

### **✅ Pre-Deployment Checklist**
- [ ] API connectivity test passed
- [ ] Rate limiting functioning correctly
- [ ] Error handling working as expected
- [ ] Product sync test (single & bulk) passed
- [ ] Order sync test passed
- [ ] Webhook processing test passed
- [ ] Database integrity verified
- [ ] Performance metrics within acceptable range
- [ ] Security validation completed
- [ ] Multi-tenant isolation verified

### **📋 Post-Deployment Monitoring**
- [ ] Daily performance reports generated
- [ ] Error rate monitoring active
- [ ] Rate limit status tracking
- [ ] Webhook queue monitoring
- [ ] Database optimization running
- [ ] User activity logging functional

---

**📅 Document Created**: June 2025  
**👨‍💻 Testing Team**: VSCode Backend Development  
**🔄 Update Frequency**: Weekly during integration phase  
**📧 Support Contact**: VSCode Backend Team
