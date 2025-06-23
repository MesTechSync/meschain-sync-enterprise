# Trendyol API Yapılandırma Kılavuzu

**Versiyon:** 4.5  
**Tarih:** 20 Haziran 2025  
**Uyumluluk:** Trendyol API v1.0, OpenCart 4.0.2.3+

---

## 📋 API Entegrasyonu Genel Bakış

Trendyol modülü, Trendyol Seller API'si ile tam entegrasyon sağlayarak aşağıdaki işlemleri gerçekleştirir:

- **Ürün Yönetimi:** Ürün ekleme, güncelleme, silme
- **Stok ve Fiyat Senkronizasyonu:** Real-time stok ve fiyat güncellemeleri
- **Sipariş Yönetimi:** Sipariş alma, durum güncelleme, kargo entegrasyonu
- **Kategori Eşleştirme:** OpenCart kategorilerini Trendyol kategorileriyle eşleştirme
- **Webhook İşlemleri:** Real-time bildirimler ve otomatik işlemler

---

## 🔑 API Kimlik Bilgileri Alma

### 1. Trendyol Satıcı Paneli'nden API Anahtarları

1. **Trendyol Satıcı Paneli**'ne giriş yapın: https://partner.trendyol.com/
2. **Entegrasyonlar** > **API Entegrasyonu** menüsüne gidin
3. **API Anahtarı Oluştur** butonuna tıklayın
4. Aşağıdaki bilgileri not alın:
   - **API Key:** Genel erişim anahtarı
   - **API Secret:** Gizli anahtar (güvenli saklayın)
   - **Supplier ID:** Satıcı kimlik numarası

### 2. Test Ortamı (Sandbox) Erişimi

```bash
# Test ortamı için
API Base URL: https://api.trendyol.com/sapigw (Test)
Production URL: https://api.trendyol.com/sapigw (Canlı)

# Test API anahtarları Trendyol'dan talep edilmelidir
Test API Key: [Test anahtarınız]
Test API Secret: [Test gizli anahtarınız]
Test Supplier ID: [Test satıcı ID'niz]
```

---

## ⚙️ OpenCart'ta API Yapılandırması

### 1. Admin Panel Yapılandırması

1. **Admin Panel** > **Extensions** > **Extensions** > **MesChain SYNC** > **Trendyol**
2. **Edit** butonuna tıklayın
3. Aşağıdaki alanları doldurun:

```
┌─────────────────────────────────────────────────────────────┐
│ Trendyol API Ayarları                                       │
├─────────────────────────────────────────────────────────────┤
│ API Key:        [Trendyol'dan aldığınız API anahtarı]       │
│ API Secret:     [Trendyol'dan aldığınız gizli anahtar]      │
│ Supplier ID:    [Satıcı kimlik numaranız]                  │
│ Test Modu:      [✓] Aktif (geliştirme için)                │
│ API Timeout:    30 (saniye)                                │
│ Retry Count:    3 (hata durumunda tekrar deneme)           │
└─────────────────────────────────────────────────────────────┘
```

### 2. Webhook URL Yapılandırması

```
Webhook URL: https://your-domain.com/index.php?route=extension/module/trendyol_webhook
Webhook Secret: [Güvenli bir secret key oluşturun]

Webhook Events:
☑ orderCreated       - Yeni sipariş bildirimi
☑ orderStatusChanged - Sipariş durumu değişikliği
☑ orderCancelled     - Sipariş iptali
☑ stockUpdated       - Stok güncellemesi
☑ priceUpdated       - Fiyat güncellemesi
```

---

## 🔧 API Client Yapılandırması

### 1. TrendyolApiClient Sınıfı

```php
<?php
// system/library/meschain/api/TrendyolApiClient.php

class TrendyolApiClient {
    
    private $api_key;
    private $api_secret;
    private $supplier_id;
    private $base_url;
    private $timeout;
    private $retry_count;
    
    public function __construct($config) {
        $this->api_key = $config['api_key'];
        $this->api_secret = $config['api_secret'];
        $this->supplier_id = $config['supplier_id'];
        $this->base_url = $config['test_mode'] ? 
            'https://api.trendyol.com/sapigw' : 
            'https://api.trendyol.com/sapigw';
        $this->timeout = $config['timeout'] ?? 30;
        $this->retry_count = $config['retry_count'] ?? 3;
    }
    
    /**
     * Trendyol API'sine HTTP isteği gönderir
     */
    public function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->base_url . $endpoint;
        $headers = $this->buildHeaders();
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        // Retry mekanizması
        $attempt = 0;
        do {
            $attempt++;
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            if ($response !== false && $http_code < 500) {
                break; // Başarılı veya client error (4xx)
            }
            
            if ($attempt < $this->retry_count) {
                sleep(pow(2, $attempt)); // Exponential backoff
            }
        } while ($attempt < $this->retry_count);
        
        curl_close($ch);
        
        if ($response === false) {
            throw new Exception("cURL Error: " . $error);
        }
        
        return $this->parseResponse($response, $http_code);
    }
    
    /**
     * HTTP headers oluşturur
     */
    private function buildHeaders() {
        $auth = base64_encode($this->api_key . ':' . $this->api_secret);
        
        return [
            'Authorization: Basic ' . $auth,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: MesChain-Trendyol-Module/4.5',
        ];
    }
    
    /**
     * API yanıtını parse eder
     */
    private function parseResponse($response, $http_code) {
        $decoded = json_decode($response, true);
        
        if ($http_code >= 400) {
            $error_message = $decoded['message'] ?? 'API Error';
            throw new TrendyolApiException($error_message, $http_code);
        }
        
        return $decoded;
    }
}
```

### 2. API Endpoint Metodları

```php
/**
 * Ürün işlemleri
 */
public function getProducts($page = 0, $size = 50) {
    return $this->makeRequest("/suppliers/{$this->supplier_id}/products?page={$page}&size={$size}");
}

public function createProduct($product_data) {
    return $this->makeRequest("/suppliers/{$this->supplier_id}/v2/products", 'POST', $product_data);
}

public function updateProduct($barcode, $product_data) {
    return $this->makeRequest("/suppliers/{$this->supplier_id}/products/price-and-inventory", 'POST', $product_data);
}

public function deleteProduct($barcode) {
    return $this->makeRequest("/suppliers/{$this->supplier_id}/products/{$barcode}", 'DELETE');
}

/**
 * Sipariş işlemleri
 */
public function getOrders($start_date, $end_date, $page = 0, $size = 200) {
    $params = http_build_query([
        'startDate' => $start_date,
        'endDate' => $end_date,
        'page' => $page,
        'size' => $size
    ]);
    
    return $this->makeRequest("/suppliers/{$this->supplier_id}/orders?{$params}");
}

public function updateOrderStatus($order_id, $status_data) {
    return $this->makeRequest("/suppliers/{$this->supplier_id}/orders/{$order_id}/status", 'PUT', $status_data);
}

/**
 * Kategori işlemleri
 */
public function getCategories() {
    return $this->makeRequest("/product-categories");
}

public function getCategoryAttributes($category_id) {
    return $this->makeRequest("/product-categories/{$category_id}/attributes");
}

/**
 * Stok ve fiyat güncellemeleri
 */
public function updatePriceAndStock($items) {
    return $this->makeRequest("/suppliers/{$this->supplier_id}/products/price-and-inventory", 'POST', [
        'items' => $items
    ]);
}

/**
 * Webhook işlemleri
 */
public function registerWebhook($webhook_url, $events) {
    return $this->makeRequest("/suppliers/{$this->supplier_id}/webhooks", 'POST', [
        'url' => $webhook_url,
        'events' => $events
    ]);
}
```

---

## 🔒 API Güvenliği

### 1. Authentication Headers

```php
// Basic Authentication kullanımı
$auth_header = 'Authorization: Basic ' . base64_encode($api_key . ':' . $api_secret);

// Request signature doğrulaması (webhook için)
function verifyWebhookSignature($payload, $signature, $secret) {
    $expected_signature = hash_hmac('sha256', $payload, $secret);
    return hash_equals($signature, $expected_signature);
}
```

### 2. Rate Limiting

```php
class TrendyolRateLimiter {
    
    private $redis;
    private $limits = [
        'default' => ['requests' => 1000, 'window' => 3600], // 1000/saat
        'products' => ['requests' => 500, 'window' => 3600],  // 500/saat
        'orders' => ['requests' => 2000, 'window' => 3600],   // 2000/saat
    ];
    
    public function checkLimit($endpoint, $supplier_id) {
        $key = "trendyol_rate_limit:{$supplier_id}:{$endpoint}";
        $limit_config = $this->limits[$endpoint] ?? $this->limits['default'];
        
        $current = $this->redis->get($key) ?: 0;
        
        if ($current >= $limit_config['requests']) {
            throw new RateLimitExceededException("Rate limit exceeded for {$endpoint}");
        }
        
        $this->redis->incr($key);
        $this->redis->expire($key, $limit_config['window']);
        
        return true;
    }
}
```

### 3. API Key Güvenliği

```php
// Azure Key Vault entegrasyonu
class TrendyolConfigManager {
    
    private $keyVaultClient;
    
    public function getApiCredentials() {
        return [
            'api_key' => $this->keyVaultClient->getSecret('TrendyolApiKey'),
            'api_secret' => $this->keyVaultClient->getSecret('TrendyolApiSecret'),
            'supplier_id' => $this->keyVaultClient->getSecret('TrendyolSupplierId'),
        ];
    }
    
    public function rotateApiKeys() {
        // API anahtarlarını yenileme işlemi
        $new_keys = $this->trendyolApi->generateNewKeys();
        
        $this->keyVaultClient->setSecret('TrendyolApiKey', $new_keys['api_key']);
        $this->keyVaultClient->setSecret('TrendyolApiSecret', $new_keys['api_secret']);
        
        // Eski anahtarları devre dışı bırak
        $this->trendyolApi->deactivateOldKeys();
    }
}
```

---

## 📊 API Monitoring ve Logging

### 1. Request/Response Logging

```php
class TrendyolApiLogger {
    
    public function logRequest($endpoint, $method, $data, $response, $duration, $status_code) {
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoint' => $endpoint,
            'method' => $method,
            'request_size' => strlen(json_encode($data)),
            'response_size' => strlen(json_encode($response)),
            'duration_ms' => $duration,
            'status_code' => $status_code,
            'success' => $status_code < 400,
        ];
        
        // Log dosyasına yaz
        file_put_contents(
            'storage/logs/trendyol_api.log',
            json_encode($log_data) . "\n",
            FILE_APPEND | LOCK_EX
        );
        
        // Azure Monitor'a gönder
        $this->azureMonitor->trackRequest($log_data);
    }
    
    public function logError($endpoint, $error_message, $context = []) {
        $error_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoint' => $endpoint,
            'error' => $error_message,
            'context' => $context,
        ];
        
        file_put_contents(
            'storage/logs/trendyol_errors.log',
            json_encode($error_data) . "\n",
            FILE_APPEND | LOCK_EX
        );
        
        // Kritik hatalar için alert gönder
        if ($this->isCriticalError($error_message)) {
            $this->sendAlert($error_data);
        }
    }
}
```

### 2. Performance Monitoring

```php
class TrendyolPerformanceMonitor {
    
    public function trackApiPerformance() {
        $metrics = [
            'total_requests' => $this->getTotalRequests(),
            'success_rate' => $this->getSuccessRate(),
            'average_response_time' => $this->getAverageResponseTime(),
            'error_rate' => $this->getErrorRate(),
            'rate_limit_hits' => $this->getRateLimitHits(),
        ];
        
        foreach ($metrics as $metric => $value) {
            $this->azureMonitor->trackMetric("trendyol_api_{$metric}", $value);
        }
    }
    
    public function generatePerformanceReport() {
        $start_time = strtotime('-24 hours');
        $end_time = time();
        
        $report = [
            'period' => '24 hours',
            'total_api_calls' => $this->getApiCallCount($start_time, $end_time),
            'success_percentage' => $this->getSuccessPercentage($start_time, $end_time),
            'average_response_time' => $this->getAverageResponseTime($start_time, $end_time),
            'slowest_endpoints' => $this->getSlowestEndpoints($start_time, $end_time),
            'most_common_errors' => $this->getMostCommonErrors($start_time, $end_time),
        ];
        
        return $report;
    }
}
```

---

## 🧪 API Test Araçları

### 1. API Connection Test

```php
public function testApiConnection() {
    try {
        // Basit bir API çağrısı yaparak bağlantıyı test et
        $response = $this->trendyolApi->makeRequest("/suppliers/{$this->supplier_id}/products?page=0&size=1");
        
        return [
            'success' => true,
            'message' => 'API bağlantısı başarılı',
            'response_time' => $response['response_time'] ?? null,
            'supplier_id' => $this->supplier_id
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'API bağlantısı başarısız: ' . $e->getMessage(),
            'error_code' => $e->getCode()
        ];
    }
}
```

### 2. Webhook Test

```php
public function testWebhook() {
    $test_payload = [
        'eventType' => 'test',
        'timestamp' => time(),
        'data' => ['test' => true]
    ];
    
    $webhook_url = $this->config->get('trendyol_webhook_url');
    $webhook_secret = $this->config->get('trendyol_webhook_secret');
    
    // Test webhook gönder
    $signature = hash_hmac('sha256', json_encode($test_payload), $webhook_secret);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $webhook_url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($test_payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Trendyol-Signature: ' . $signature
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'success' => $http_code === 200,
        'http_code' => $http_code,
        'response' => $response
    ];
}
```

---

## 📝 API Kullanım Örnekleri

### 1. Ürün Ekleme

```php
$product_data = [
    'barcode' => 'ABC123456789',
    'title' => 'Örnek Ürün Başlığı',
    'description' => 'Detaylı ürün açıklaması...',
    'brand' => 'Marka Adı',
    'categoryId' => 1234,
    'quantity' => 100,
    'listPrice' => 99.99,
    'salePrice' => 79.99,
    'images' => [
        ['url' => 'https://example.com/image1.jpg'],
        ['url' => 'https://example.com/image2.jpg']
    ],
    'attributes' => [
        ['attributeId' => 1, 'attributeValueId' => 10],
        ['attributeId' => 2, 'customAttributeValue' => 'Özel Değer']
    ]
];

try {
    $result = $trendyolApi->createProduct($product_data);
    echo "Ürün başarıyla eklendi. Batch Request ID: " . $result['batchRequestId'];
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
```

### 2. Sipariş Alma

```php
$start_date = date('Y-m-d', strtotime('-7 days'));
$end_date = date('Y-m-d');

try {
    $orders = $trendyolApi->getOrders($start_date, $end_date);
    
    foreach ($orders['content'] as $order) {
        echo "Sipariş No: " . $order['orderNumber'] . "\n";
        echo "Müşteri: " . $order['customerFirstName'] . " " . $order['customerLastName'] . "\n";
        echo "Toplam: " . $order['totalPrice'] . " TL\n";
        echo "---\n";
    }
} catch (Exception $e) {
    echo "Sipariş listesi alınamadı: " . $e->getMessage();
}
```

Bu API yapılandırma kılavuzu, Trendyol modülünün API entegrasyonu için gerekli tüm teknik detayları kapsamaktadır.
