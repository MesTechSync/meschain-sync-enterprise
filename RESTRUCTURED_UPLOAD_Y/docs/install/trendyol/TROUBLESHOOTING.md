# Trendyol Modülü - Sorun Giderme Kılavuzu

**Versiyon:** 4.5  
**Tarih:** 20 Haziran 2025  
**Kapsam:** Yaygın problemler ve çözümler

---

## 🔧 Genel Sorun Giderme Adımları

### 1. Sistem Durumu Kontrolü

```bash
# PHP ve uzantı kontrolü
php -v
php -m | grep -E "(curl|json|openssl|mysqli)"

# OpenCart izinleri kontrolü
ls -la /path/to/opencart/
find /path/to/opencart/ -name "*.php" -exec ls -la {} \;

# Log dosyaları kontrolü
tail -f storage/logs/error.log
tail -f storage/logs/trendyol.log
```

### 2. Trendyol Modül Durumu

```php
// admin/controller/extension/module/trendyol_diagnostic.php
public function systemCheck() {
    $checks = [
        'php_version' => $this->checkPhpVersion(),
        'required_extensions' => $this->checkRequiredExtensions(),
        'file_permissions' => $this->checkFilePermissions(),
        'database_tables' => $this->checkDatabaseTables(),
        'api_connectivity' => $this->checkApiConnectivity(),
        'webhook_endpoint' => $this->checkWebhookEndpoint(),
        'ssl_certificate' => $this->checkSslCertificate()
    ];
    
    return $checks;
}
```

---

## ❌ Yaygın Kurulum Hataları

### 1. OCMOD Kurulum Hataları

#### **Hata:** "File not found: upload/admin/controller/extension/module/trendyol.php"

**Çözüm:**
```bash
# Dosya izinlerini kontrol edin
chmod -R 755 upload/
chown -R www-data:www-data upload/

# Dosyaların doğru konuma kopyalandığını doğrulayın
find . -name "trendyol.php" -type f

# OCMOD cache'ini temizleyin
rm -rf system/storage/modification/*
```

#### **Hata:** "Database error: Table 'oc_trendyol_settings' doesn't exist"

**Çözüm:**
```sql
-- Eksik tabloları manuel olarak oluşturun
CREATE TABLE IF NOT EXISTS `oc_trendyol_settings` (
    `setting_id` int(11) NOT NULL AUTO_INCREMENT,
    `store_id` int(11) NOT NULL DEFAULT '0',
    `key` varchar(64) NOT NULL,
    `value` text NOT NULL,
    `serialized` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`setting_id`)
);

-- Modülü tekrar kurun
-- Admin Panel > Extensions > Extensions > MesChain SYNC > Trendyol > Install
```

### 2. Bağımlılık Hataları

#### **Hata:** "Class 'Firebase\JWT\JWT' not found"

**Çözüm:**
```bash
# Composer ile JWT kütüphanesini kurun
composer require firebase/jwt
composer require guzzlehttp/guzzle

# vendor/autoload.php dosyasını dahil edin
echo "require_once 'vendor/autoload.php';" >> config.php
```

#### **Hata:** "Call to undefined function openssl_encrypt()"

**Çözüm:**
```bash
# OpenSSL uzantısını kurun
sudo apt-get install php-openssl  # Ubuntu/Debian
sudo yum install php-openssl      # CentOS/RHEL

# PHP'yi yeniden başlatın
sudo service apache2 restart
sudo service php-fpm restart
```

---

## 🔌 API Bağlantı Sorunları

### 1. Kimlik Doğrulama Hataları

#### **Hata:** "401 Unauthorized - Invalid API credentials"

**Kontrol Listesi:**
```bash
# API bilgilerini doğrulayın
curl -X GET "https://api.trendyol.com/sapigw/suppliers/SUPPLIER_ID/products?page=0&size=1" \
     -H "Authorization: Basic $(echo -n 'API_KEY:API_SECRET' | base64)"

# Yanıt: 200 OK beklenir
```

**Çözüm:**
1. Trendyol Satıcı Paneli'nden API bilgilerini tekrar kontrol edin
2. API Key ve Secret'in doğru kopyalandığından emin olun
3. Supplier ID'nin doğru olduğunu kontrol edin
4. Test modu aktifse production API bilgilerini kullanmayın

#### **Hata:** "403 Forbidden - Insufficient permissions"

**Çözüm:**
1. Trendyol hesabınızın API erişim iznine sahip olduğunu doğrulayın
2. API anahtarının geçerli olduğunu kontrol edin
3. Trendyol destek ekibiyle iletişime geçin

### 2. SSL/TLS Sorunları

#### **Hata:** "SSL certificate problem: unable to get local issuer certificate"

**Çözüm:**
```php
// Geçici çözüm (sadece test için)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Kalıcı çözüm: CA sertifikalarını güncelleyin
// Ubuntu/Debian:
sudo apt-get update && sudo apt-get install ca-certificates

// CentOS/RHEL:
sudo yum update ca-certificates
```

### 3. Timeout Sorunları

#### **Hata:** "cURL timeout: Operation timed out after 30 seconds"

**Çözüm:**
```php
// Timeout süresini artırın
$config['api_timeout'] = 60; // 60 saniye

// Retry mekanizmasını aktif edin
$config['retry_attempts'] = 3;
$config['retry_delay'] = 2; // saniye
```

---

## 🔄 Senkronizasyon Sorunları

### 1. Ürün Senkronizasyon Hataları

#### **Hata:** "Product validation failed: Title is required"

**Çözüm:**
```sql
-- Eksik ürün bilgilerini kontrol edin
SELECT p.product_id, pd.name, pd.description, p.price, p.quantity
FROM oc_product p
LEFT JOIN oc_product_description pd ON p.product_id = pd.product_id
WHERE pd.name IS NULL OR pd.name = '' OR p.price = 0;

-- Eksik bilgileri tamamlayın
UPDATE oc_product_description 
SET name = 'Varsayılan Ürün Adı', description = 'Varsayılan açıklama'
WHERE name IS NULL OR name = '';
```

#### **Hata:** "Category mapping not found for category ID: 123"

**Çözüm:**
```php
// Kategori eşleştirmesini manuel olarak ekleyin
public function addCategoryMapping($opencart_category_id, $trendyol_category_id) {
    $this->db->query("INSERT INTO " . DB_PREFIX . "trendyol_category_mapping 
                      (opencart_category_id, trendyol_category_id, trendyol_category_name) 
                      VALUES (" . (int)$opencart_category_id . ", " . (int)$trendyol_category_id . ", 
                      '" . $this->db->escape($trendyol_category_name) . "')");
}
```

### 2. Stok Senkronizasyon Sorunları

#### **Hata:** "Stock update failed: Invalid barcode format"

**Çözüm:**
```sql
-- Geçersiz barkodları kontrol edin
SELECT product_id, sku, barcode 
FROM oc_product 
WHERE barcode IS NULL OR barcode = '' OR LENGTH(barcode) < 8;

-- Otomatik barcode oluşturun
UPDATE oc_product 
SET barcode = CONCAT('AUTO', LPAD(product_id, 8, '0'))
WHERE barcode IS NULL OR barcode = '';
```

---

## 📡 Webhook Sorunları

### 1. Webhook Alınamıyor

#### **Kontrol Listesi:**
```bash
# Webhook URL'sine erişim testi
curl -X POST "https://your-domain.com/index.php?route=extension/module/trendyol_webhook" \
     -H "Content-Type: application/json" \
     -d '{"test": true}'

# Firewall kurallarını kontrol edin
sudo ufw status
sudo iptables -L

# SSL sertifikasını kontrol edin
openssl s_client -connect your-domain.com:443
```

**Çözüm:**
1. Webhook URL'sine dışarıdan erişilebildiğini doğrulayın
2. SSL sertifikasının geçerli olduğunu kontrol edin
3. Firewall kurallarını gözden geçirin
4. Load balancer/proxy ayarlarını kontrol edin

### 2. Webhook Signature Doğrulaması Başarısız

#### **Hata:** "Invalid webhook signature"

**Debug Kodu:**
```php
public function debugWebhookSignature($payload, $received_signature, $secret) {
    $calculated_signature = hash_hmac('sha256', $payload, $secret);
    
    error_log("Received signature: " . $received_signature);
    error_log("Calculated signature: " . $calculated_signature);
    error_log("Payload: " . $payload);
    error_log("Secret length: " . strlen($secret));
    
    return hash_equals($received_signature, $calculated_signature);
}
```

**Çözüm:**
1. Webhook secret'in doğru yapılandırıldığını kontrol edin
2. Payload'ın değiştirilmediğinden emin olun
3. Header'daki signature formatını doğrulayın

---

## 💾 Veritabanı Sorunları

### 1. Connection Errors

#### **Hata:** "Database connection failed"

**Kontrol:**
```php
// Veritabanı bağlantı bilgilerini test edin
try {
    $pdo = new PDO(
        "mysql:host={$db_hostname};dbname={$db_database}",
        $db_username,
        $db_password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Database connection successful";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
```

### 2. Table Structure Issues

#### **Hata:** "Column 'xyz' doesn't exist in table"

**Çözüm:**
```sql
-- Tablo yapısını kontrol edin
DESCRIBE oc_trendyol_settings;

-- Eksik sütunu ekleyin
ALTER TABLE oc_trendyol_settings 
ADD COLUMN missing_column VARCHAR(255) DEFAULT NULL;

-- Modül versiyonunu güncelleyin
UPDATE oc_extension 
SET version = '4.5.0' 
WHERE code = 'trendyol';
```

---

## 🔒 Güvenlik Sorunları

### 1. JWT Token Sorunları

#### **Hata:** "Invalid JWT token"

**Debug:**
```php
public function debugJwtToken($token) {
    try {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new Exception('Invalid JWT format');
        }
        
        $header = json_decode(base64_decode($parts[0]), true);
        $payload = json_decode(base64_decode($parts[1]), true);
        
        error_log("JWT Header: " . print_r($header, true));
        error_log("JWT Payload: " . print_r($payload, true));
        error_log("JWT Expiry: " . date('Y-m-d H:i:s', $payload['exp']));
        
    } catch (Exception $e) {
        error_log("JWT Debug Error: " . $e->getMessage());
    }
}
```

### 2. Rate Limiting Issues

#### **Hata:** "Rate limit exceeded"

**Çözüm:**
```bash
# Redis'te rate limit anahtarlarını kontrol edin
redis-cli KEYS "rate_limit:*"

# Belirli bir IP'nin rate limit durumunu sıfırlayın
redis-cli DEL "rate_limit:api_general:ip:1.2.3.4"

# Rate limit ayarlarını geçici olarak artırın
# config/trendyol.php'de limit değerlerini artırın
```

---

## 📊 Performance Sorunları

### 1. Yavaş API Yanıtları

#### **Monitoring:**
```php
public function measureApiPerformance($endpoint, $callable) {
    $start_time = microtime(true);
    
    try {
        $result = $callable();
        $end_time = microtime(true);
        $duration = ($end_time - $start_time) * 1000; // milliseconds
        
        // Yavaş API çağrılarını logla
        if ($duration > 5000) { // 5 saniyeden uzun
            error_log("Slow API call detected: {$endpoint} took {$duration}ms");
        }
        
        return $result;
    } catch (Exception $e) {
        $end_time = microtime(true);
        $duration = ($end_time - $start_time) * 1000;
        
        error_log("API call failed: {$endpoint} after {$duration}ms - " . $e->getMessage());
        throw $e;
    }
}
```

### 2. Memory Issues

#### **Hata:** "Fatal error: Allowed memory size exhausted"

**Çözüm:**
```php
// Memory kullanımını monitör edin
function checkMemoryUsage($label) {
    $memory_usage = memory_get_usage(true) / 1024 / 1024; // MB
    $peak_memory = memory_get_peak_usage(true) / 1024 / 1024; // MB
    
    error_log("{$label} - Memory: {$memory_usage}MB, Peak: {$peak_memory}MB");
    
    // Memory limit'e yaklaşıyorsak uyarı ver
    $memory_limit = ini_get('memory_limit');
    $limit_mb = (int)$memory_limit; // 'M' harfini varsayarak
    
    if ($memory_usage > ($limit_mb * 0.8)) {
        error_log("WARNING: Memory usage is high ({$memory_usage}MB of {$limit_mb}MB)");
    }
}

// Batch işlemlerinde bellek kullanımını optimize edin
public function syncProductsBatch($products, $batch_size = 50) {
    $batches = array_chunk($products, $batch_size);
    
    foreach ($batches as $batch) {
        $this->processBatch($batch);
        
        // Her batch sonrası garbage collection çalıştır
        gc_collect_cycles();
        
        // Memory kullanımını kontrol et
        $this->checkMemoryUsage("After batch");
    }
}
```

---

## 🛠️ Debug ve Log Analizi

### 1. Log Seviyesi Ayarlama

```php
// config/logging.php
return [
    'level' => 'debug', // debug, info, warning, error
    'max_file_size' => '10MB',
    'max_files' => 5,
    
    'channels' => [
        'trendyol_api' => 'storage/logs/trendyol_api.log',
        'trendyol_webhook' => 'storage/logs/trendyol_webhook.log',
        'trendyol_sync' => 'storage/logs/trendyol_sync.log',
        'trendyol_error' => 'storage/logs/trendyol_error.log'
    ]
];
```

### 2. Log Analiz Araçları

```bash
# En sık karşılaşılan hataları bulun
grep "ERROR" storage/logs/trendyol*.log | cut -d':' -f3- | sort | uniq -c | sort -nr

# API yanıt sürelerini analiz edin
grep "API_DURATION" storage/logs/trendyol_api.log | awk '{print $NF}' | sort -n

# Webhook durumunu kontrol edin
tail -f storage/logs/trendyol_webhook.log | grep -E "(SUCCESS|ERROR|SIGNATURE)"

# Rate limit ihlallerini kontrol edin
grep "rate_limit" storage/logs/trendyol*.log | wc -l
```

---

## 📞 Destek ve Raporlama

### 1. Sistem Raporu Oluşturma

```php
public function generateSystemReport() {
    $report = [
        'system_info' => [
            'php_version' => phpversion(),
            'opencart_version' => VERSION,
            'trendyol_module_version' => $this->getModuleVersion(),
            'server_info' => $_SERVER['SERVER_SOFTWARE'],
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time')
        ],
        
        'module_status' => [
            'installation_status' => $this->checkInstallationStatus(),
            'configuration_status' => $this->checkConfigurationStatus(),
            'api_connectivity' => $this->testApiConnectivity(),
            'webhook_status' => $this->checkWebhookStatus()
        ],
        
        'recent_errors' => $this->getRecentErrors(24), // Son 24 saat
        'performance_metrics' => $this->getPerformanceMetrics(),
        
        'recommendations' => $this->generateRecommendations()
    ];
    
    return $report;
}
```

### 2. Bug Raporu Şablonu

```
**Trendyol Modülü Bug Raporu**

**Ortam Bilgileri:**
- OpenCart Versiyonu: 
- PHP Versiyonu: 
- Trendyol Modül Versiyonu: 
- İşletim Sistemi: 

**Problem Açıklaması:**
[Problemi detaylı olarak açıklayın]

**Hatayı Yeniden Oluşturma Adımları:**
1. 
2. 
3. 

**Beklenen Davranış:**
[Ne olmasını bekliyordunuz?]

**Gerçek Davranış:**
[Ne oldu?]

**Log Çıktıları:**
```
[Hata loglarını buraya yapıştırın]
```

**Ek Bilgiler:**
[Ekran görüntüleri, ek loglar vb.]
```

Bu sorun giderme kılavuzu, Trendyol modülü ile karşılaşılabilecek yaygın sorunları ve çözümlerini kapsamlı olarak ele almaktadır.
