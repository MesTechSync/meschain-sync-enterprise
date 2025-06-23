# 🚀 MesChain-Sync Trendyol Integration - Kurulum Kılavuzu

**Versiyon:** 4.5.0 Enterprise
**Tarih:** 19 Haziran 2025
**Uyumluluk:** OpenCart 4.x, PHP 7.4+

## 📋 İçindekiler

1. [Genel Bakış](#genel-bakış)
2. [Sistem Gereksinimleri](#sistem-gereksinimleri)
3. [Kurulum Yöntemleri](#kurulum-yöntemleri)
4. [Manuel Kurulum](#manuel-kurulum)
5. [Otomatik Kurulum](#otomatik-kurulum)
6. [Yapılandırma](#yapılandırma)
7. [Test ve Doğrulama](#test-ve-doğrulama)
8. [Sorun Giderme](#sorun-giderme)
9. [Gelişmiş Özellikler](#gelişmiş-özellikler)

## 🎯 Genel Bakış

MesChain-Sync Trendyol Integration, OpenCart mağazanızı Trendyol marketplace ile tam entegre eden gelişmiş bir eklentidir. Bu entegrasyon aşağıdaki özellikleri sağlar:

### 🌟 Ana Özellikler
- ✅ **Gerçek Zamanlı Sipariş Senkronizasyonu**
- ✅ **Otomatik Ürün Eşitleme**
- ✅ **Webhook Desteği** (9 farklı event türü)
- ✅ **AI Destekli Optimizasyon**
- ✅ **Gelişmiş Analytics Dashboard**
- ✅ **Bulk İşlemler** (Toplu fiyat, stok güncellemeleri)
- ✅ **Multi-tenant Destek**
- ✅ **RBAC (Role-Based Access Control)**
- ✅ **Kapsamlı Loglama**
- ✅ **Hata Yönetimi ve Recovery**

### 🏗️ Mimari
```
OpenCart Store
    ↕️
MesChain-Sync Core
    ↕️
Trendyol Integration Layer
    ↕️
Trendyol API (v2.0)
```

## 💻 Sistem Gereksinimleri

### Minimum Gereksinimler
- **PHP:** 7.4+ (8.1+ önerilir)
- **MySQL:** 5.7+ / MariaDB 10.3+
- **OpenCart:** 4.0.2.0+
- **Memory Limit:** 256MB (512MB önerilir)
- **Disk Alanı:** 50MB

### PHP Eklentileri
```bash
# Gerekli eklentiler
php-mysqli
php-curl
php-json
php-mbstring
php-zip
php-xml
php-gd

# Opsiyonel (performans için)
php-opcache
php-redis
php-memcached
```

### Sunucu Gereksinimleri
- **SSL Sertifikası** (Webhook'lar için zorunlu)
- **Cron Jobs** desteği
- **Outbound HTTPS** bağlantıları
- **JSON** desteği

## 🔧 Kurulum Yöntemleri

### Yöntem 1: Otomatik Kurulum (Önerilir)
```bash
# Terminal'den çalıştırın
php meschain_trendyol_installer.php

# Veya web tarayıcısından
https://yourstore.com/meschain_trendyol_installer.php
```

### Yöntem 2: Manuel Kurulum
Aşağıdaki adımları takip edin.

## 📦 Manuel Kurulum

### Adım 1: Dosyaları Kopyalayın

```bash
# Ana dizin yapısı
your-opencart/
├── admin/
│   ├── controller/extension/module/
│   │   ├── trendyol.php
│   │   └── trendyol_advanced.php
│   ├── model/extension/module/
│   │   ├── trendyol.php
│   │   └── trendyol_advanced.php
│   ├── view/template/extension/module/
│   │   ├── trendyol.twig
│   │   └── trendyol_advanced.twig
│   ├── view/javascript/meschain/
│   │   └── trendyol_advanced.js
│   └── language/
│       ├── tr-tr/extension/module/
│       │   ├── trendyol.php
│       │   └── trendyol_advanced.php
│       └── en-gb/extension/module/
│           ├── trendyol.php
│           └── trendyol_advanced.php
├── catalog/
│   └── controller/extension/module/
│       └── trendyol_webhook.php
└── system/
    └── library/meschain/
        ├── api/
        │   └── TrendyolApiClient.php
        ├── webhook/
        │   └── TrendyolWebhookHandler.php
        └── helper/
            └── trendyol.php
```

### Adım 2: Veritabanı Tablolarını Oluşturun

```sql
-- Ürün eşleştirme tablosu
CREATE TABLE `oc_trendyol_products` (
    `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
    `opencart_product_id` int(11) NOT NULL,
    `trendyol_product_id` varchar(100) DEFAULT NULL,
    `barcode` varchar(100) NOT NULL,
    `content_id` varchar(100) DEFAULT NULL,
    `approved` tinyint(1) DEFAULT 0,
    `status` enum('pending','approved','rejected','passive') DEFAULT 'pending',
    `category_id` int(11) DEFAULT NULL,
    `brand_id` int(11) DEFAULT NULL,
    `last_sync` datetime DEFAULT NULL,
    `sync_status` enum('synced','pending','error') DEFAULT 'pending',
    `error_message` text,
    `rejection_reason` text,
    `list_price` decimal(15,4) DEFAULT NULL,
    `sale_price` decimal(15,4) DEFAULT NULL,
    `quantity` int(11) DEFAULT 0,
    `tenant_id` int(11) DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`mapping_id`),
    UNIQUE KEY `barcode_unique` (`barcode`),
    KEY `opencart_product_id` (`opencart_product_id`),
    KEY `trendyol_product_id` (`trendyol_product_id`),
    KEY `status` (`status`),
    KEY `sync_status` (`sync_status`),
    KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sipariş tablosu
CREATE TABLE `oc_trendyol_orders` (
    `trendyol_order_id` int(11) NOT NULL AUTO_INCREMENT,
    `opencart_order_id` int(11) DEFAULT NULL,
    `shipment_package_id` varchar(100) DEFAULT NULL,
    `order_number` varchar(100) NOT NULL,
    `gross_amount` decimal(15,4) NOT NULL DEFAULT 0,
    `total_discount` decimal(15,4) DEFAULT 0,
    `total_tax` decimal(15,4) DEFAULT 0,
    `delivery_type` varchar(50) DEFAULT NULL,
    `time_slot_id` varchar(50) DEFAULT NULL,
    `estimated_delivery` datetime DEFAULT NULL,
    `status` varchar(50) NOT NULL,
    `order_date` datetime NOT NULL,
    `cargo_tracking_number` varchar(100) DEFAULT NULL,
    `cargo_provider_name` varchar(100) DEFAULT NULL,
    `lines` json DEFAULT NULL,
    `customer_info` json DEFAULT NULL,
    `invoice_address` json DEFAULT NULL,
    `delivery_address` json DEFAULT NULL,
    `sync_status` enum('pending','synced','error') DEFAULT 'pending',
    `error_message` text,
    `tenant_id` int(11) DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`trendyol_order_id`),
    UNIQUE KEY `order_number_unique` (`order_number`),
    KEY `opencart_order_id` (`opencart_order_id`),
    KEY `status` (`status`),
    KEY `order_date` (`order_date`),
    KEY `sync_status` (`sync_status`),
    KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Webhook logları
CREATE TABLE `oc_trendyol_webhook_logs` (
    `log_id` int(11) NOT NULL AUTO_INCREMENT,
    `event_type` varchar(100) NOT NULL,
    `event_data` json NOT NULL,
    `signature` varchar(255) DEFAULT NULL,
    `processed` tinyint(1) DEFAULT 0,
    `processed_at` datetime DEFAULT NULL,
    `error_message` text,
    `response_sent` text,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` varchar(255) DEFAULT NULL,
    `received_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`),
    KEY `event_type` (`event_type`),
    KEY `processed` (`processed`),
    KEY `received_at` (`received_at`),
    KEY `event_processed` (`event_type`, `processed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Webhook konfigürasyonu
CREATE TABLE `oc_trendyol_webhook_config` (
    `config_id` int(11) NOT NULL AUTO_INCREMENT,
    `event_type` varchar(100) NOT NULL,
    `enabled` tinyint(1) DEFAULT 1,
    `auto_process` tinyint(1) DEFAULT 1,
    `retry_count` int(11) DEFAULT 3,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`config_id`),
    UNIQUE KEY `event_type_unique` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics tablosu
CREATE TABLE `oc_trendyol_analytics` (
    `analytics_id` int(11) NOT NULL AUTO_INCREMENT,
    `metric_type` varchar(100) NOT NULL,
    `metric_value` decimal(15,4) NOT NULL,
    `metric_data` json DEFAULT NULL,
    `date_recorded` date NOT NULL,
    `hour_recorded` tinyint(2) DEFAULT NULL,
    `tenant_id` int(11) DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`analytics_id`),
    KEY `metric_type` (`metric_type`),
    KEY `date_recorded` (`date_recorded`),
    KEY `metric_date` (`metric_type`, `date_recorded`),
    KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Adım 3: OCMOD Kurulumu

1. **Admin Panel** → **Extensions** → **Installer**
2. `install.xml` dosyasını yükleyin
3. **Extensions** → **Modifications** → **Refresh** butonuna tıklayın

### Adım 4: Modül Aktivasyonu

1. **Extensions** → **Extensions**
2. Dropdown'dan **"MesChain SYNC"** seçin
3. **Trendyol** modülünü bulun
4. **Install** (+) butonuna tıklayın
5. **Edit** (kalem) butonuna tıklayarak yapılandırın

## ⚙️ Yapılandırma

### API Ayarları

```php
// Temel API Bilgileri
API Key: [Trendyol'dan aldığınız API anahtarı]
API Secret: [Trendyol'dan aldığınız gizli anahtar]
Supplier ID: [Satıcı ID'niz]
Test Mode: [Geliştirme için aktif edin]
```

### Webhook Yapılandırması

```bash
# Webhook URL'nizi Trendyol'a kaydedin
https://yourstore.com/index.php?route=extension/module/trendyol_webhook

# Desteklenen Event'ler
- ORDER_CREATED
- ORDER_CANCELLED
- ORDER_STATUS_CHANGED
- PRODUCT_APPROVED
- PRODUCT_REJECTED
- INVENTORY_UPDATED
- PRICE_UPDATED
- SHIPMENT_CREATED
- RETURN_INITIATED
```

### Cron Jobs

```bash
# Otomatik senkronizasyon için cron job'ları ekleyin
# Her 5 dakikada ürün senkronizasyonu
*/5 * * * * php /path/to/opencart/cli/trendyol_sync_products.php

# Her 10 dakikada sipariş senkronizasyonu
*/10 * * * * php /path/to/opencart/cli/trendyol_sync_orders.php

# Günlük analytics
0 1 * * * php /path/to/opencart/cli/trendyol_analytics.php
```

## 🧪 Test ve Doğrulama

### 1. Bağlantı Testi
```bash
# Admin panelden
Trendyol → Ayarlar → "Bağlantıyı Test Et"

# Webhook test endpoint'i
https://yourstore.com/index.php?route=extension/module/trendyol_webhook/test
```

### 2. Webhook Testi
```bash
curl -X POST https://yourstore.com/index.php?route=extension/module/trendyol_webhook \
  -H "Content-Type: application/json" \
  -H "X-Trendyol-Signature: test_signature" \
  -d '{
    "eventType": "ORDER_CREATED",
    "orderNumber": "TEST-123",
    "orderDate": 1640995200000,
    "grossAmount": 99.99
  }'
```

### 3. Sistem Durumu
```bash
# Health check endpoint'i
https://yourstore.com/index.php?route=extension/module/trendyol_webhook/health

# Webhook durumu
https://yourstore.com/index.php?route=extension/module/trendyol_webhook/status
```

## 🐛 Sorun Giderme

### Yaygın Problemler

#### 1. Webhook İmza Doğrulama Hatası
```bash
# Log dosyasını kontrol edin
tail -f storage/logs/trendyol_webhook.log

# Çözüm: API Secret'in doğru girildiğinden emin olun
```

#### 2. Veritabanı Bağlantı Hatası
```bash
# MySQL bağlantısını test edin
mysql -h localhost -u username -p database_name

# Çözüm: Veritabanı izinlerini kontrol edin
```

#### 3. Dosya İzin Hatası
```bash
# Dosya izinlerini düzeltin
chmod 644 admin/controller/extension/module/trendyol.php
chmod 755 system/library/meschain/
```

#### 4. Memory Limit Hatası
```php
// php.ini'de artırın
memory_limit = 512M

// Veya .htaccess ile
php_value memory_limit 512M
```

### Debug Modu

```php
// config.php'ye ekleyin
define('TRENDYOL_DEBUG', true);
define('TRENDYOL_LOG_LEVEL', 'DEBUG');
```

### Log Dosyaları

```bash
# Ana log dosyaları
storage/logs/trendyol_api.log
storage/logs/trendyol_webhook.log
storage/logs/trendyol_sync.log
storage/logs/error.log
```

## 🚀 Gelişmiş Özellikler

### 1. AI Destekli Optimizasyon
- **Dinamik Fiyatlandırma**
- **Talep Tahmini**
- **Müşteri Segmentasyonu**
- **Kampanya Optimizasyonu**

### 2. Bulk İşlemler
```php
// Toplu fiyat güncellemesi
$products = [1, 2, 3, 4, 5]; // Product ID'leri
$result = $trendyolAdvanced->bulkPriceUpdate($products, [
    'increase_percentage' => 10,
    'min_price' => 50,
    'max_price' => 1000
]);

// Toplu stok güncellemesi
$result = $trendyolAdvanced->bulkStockUpdate($products, [
    'quantity' => 100,
    'operation' => 'set' // set, increase, decrease
]);
```

### 3. Real-time Analytics
```javascript
// WebSocket bağlantısı
const ws = new WebSocket('wss://yourstore.com/trendyol/websocket');

ws.onmessage = function(event) {
    const data = JSON.parse(event.data);
    updateDashboard(data);
};
```

### 4. Multi-tenant Destek
```php
// Tenant bazlı ayarlar
$settings = [
    'tenant_id' => 1,
    'api_key' => 'tenant1_api_key',
    'api_secret' => 'tenant1_api_secret'
];
```

## 📊 Performans Optimizasyonu

### 1. Veritabanı İndeksleri
```sql
-- Performans için ek indeksler
CREATE INDEX idx_trendyol_products_sync ON oc_trendyol_products(sync_status, last_sync);
CREATE INDEX idx_trendyol_orders_status ON oc_trendyol_orders(status, order_date);
CREATE INDEX idx_webhook_logs_event_date ON oc_trendyol_webhook_logs(event_type, received_at);
```

### 2. Cache Yapılandırması
```php
// Redis cache
$cache = new Redis();
$cache->connect('127.0.0.1', 6379);
$cache->setex('trendyol_products_' . $page, 300, json_encode($products));
```

### 3. Rate Limiting
```php
// API çağrılarını sınırla
$rateLimiter = new RateLimiter([
    'products' => ['requests' => 50, 'period' => 60],
    'orders' => ['requests' => 200, 'period' => 60]
]);
```

## 🔒 Güvenlik

### 1. API Güvenliği
- **HTTPS** zorunlu
- **API Key** şifreleme
- **Request imzalama**
- **Rate limiting**

### 2. Webhook Güvenliği
- **Signature doğrulama**
- **IP whitelist**
- **Request logging**
- **Replay attack koruması**

### 3. Veri Güvenliği
- **Hassas verilerin şifrelenmesi**
- **SQL injection koruması**
- **XSS koruması**
- **CSRF token'ları**

## 📚 API Referansı

### TrendyolApiClient Metodları

```php
// Ürün işlemleri
$client->getProducts($page, $size);
$client->createProduct($productData);
$client->updateProduct($barcode, $productData);
$client->deleteProduct($barcode);

// Sipariş işlemleri
$client->getOrders($startDate, $endDate);
$client->getOrder($orderNumber);
$client->updateOrderStatus($orderNumber, $status);

// Stok işlemleri
$client->updateStock($barcode, $quantity);
$client->bulkUpdateStock($stockData);

// Fiyat işlemleri
$client->updatePrice($barcode, $listPrice, $salePrice);
$client->bulkUpdatePrices($priceData);
```

### Webhook Event Yapısı

```json
{
  "eventType": "ORDER_CREATED",
  "orderNumber": "TY-123456789",
  "orderDate": 1640995200000,
  "grossAmount": 150.75,
  "totalDiscount": 15.00,
  "customerFirstName": "Ahmet",
  "customerLastName": "Yılmaz",
  "customerEmail": "ahmet@example.com",
  "status": "Created",
  "lines": [
    {
      "productName": "Örnek Ürün",
      "barcode": "1234567890123",
      "quantity": 2,
      "price": 75.00,
      "totalPrice": 150.00
    }
  ]
}
```

## 🆘 Destek

### Teknik Destek
- **Email:** support@meschain.com
- **Telefon:** +90 212 XXX XX XX
- **Ticket Sistemi:** https://support.meschain.com

### Dokümantasyon
- **API Docs:** https://docs.meschain.com/trendyol
- **Video Tutorials:** https://youtube.com/meschain
- **Community Forum:** https://community.meschain.com

### Güncellemeler
- **GitHub:** https://github.com/meschain/trendyol-integration
- **Changelog:** CHANGELOG.md
- **Release Notes:** RELEASES.md

---

**© 2024 MesChain Technologies. Tüm hakları saklıdır.**
