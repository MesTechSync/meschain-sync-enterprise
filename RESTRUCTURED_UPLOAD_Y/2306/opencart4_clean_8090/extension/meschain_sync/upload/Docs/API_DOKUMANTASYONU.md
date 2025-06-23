# MesChain Trendyol Entegrasyonu - API Dokümantasyonu

## İçindekiler

1. [API Genel Bakış](#api-genel-bakış)
2. [Kimlik Doğrulama](#kimlik-doğrulama)
3. [Ürün API'leri](#ürün-apileri)
4. [Sipariş API'leri](#sipariş-apileri)
5. [Stok API'leri](#stok-apileri)
6. [Kategori API'leri](#kategori-apileri)
7. [Webhook API'leri](#webhook-apileri)
8. [Hata Kodları](#hata-kodları)
9. [Rate Limiting](#rate-limiting)
10. [SDK Kullanımı](#sdk-kullanımı)

## API Genel Bakış

MesChain Trendyol Entegrasyonu, OpenCart ve Trendyol arasında veri alışverişi için kapsamlı bir API seti sunar.

### Base URL
```
Production: https://api.trendyol.com/sapigw/suppliers/{supplierId}
Sandbox: https://stageapi.trendyol.com/sapigw/suppliers/{supplierId}
```

### API Versiyonu
```
Version: v1
Content-Type: application/json
Accept: application/json
```

### Desteklenen HTTP Metodları
- `GET`: Veri okuma işlemleri
- `POST`: Yeni veri oluşturma
- `PUT`: Veri güncelleme
- `DELETE`: Veri silme

## Kimlik Doğrulama

### Basic Authentication

Tüm API istekleri Basic Authentication kullanır:

```bash
# Kimlik bilgileri
Username: {API_KEY}
Password: {API_SECRET}

# Header formatı
Authorization: Basic {base64(API_KEY:API_SECRET)}
```

### PHP Örneği

```php
<?php
class TrendyolAuth {
    private $apiKey;
    private $apiSecret;
    private $supplierId;

    public function __construct($apiKey, $apiSecret, $supplierId) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->supplierId = $supplierId;
    }

    public function getAuthHeader() {
        $credentials = base64_encode($this->apiKey . ':' . $this->apiSecret);
        return 'Authorization: Basic ' . $credentials;
    }

    public function getBaseUrl() {
        return "https://api.trendyol.com/sapigw/suppliers/{$this->supplierId}";
    }
}

// Kullanım
$auth = new TrendyolAuth('your_api_key', 'your_api_secret', 'your_supplier_id');
$headers = [
    $auth->getAuthHeader(),
    'Content-Type: application/json',
    'Accept: application/json'
];
?>
```

### cURL Örneği

```bash
curl -X GET \
  'https://api.trendyol.com/sapigw/suppliers/123456/products' \
  -H 'Authorization: Basic {base64_encoded_credentials}' \
  -H 'Content-Type: application/json'
```

## Ürün API'leri

### 1. Ürün Listesi Alma

**Endpoint**: `GET /products`

**Parametreler**:
```json
{
  "page": 1,
  "size": 50,
  "approved": true,
  "barcode": "1234567890123",
  "startDate": "2025-01-01",
  "endDate": "2025-12-31"
}
```

**Yanıt**:
```json
{
  "content": [
    {
      "id": 12345,
      "productCode": "PROD001",
      "title": "Örnek Ürün",
      "description": "Ürün açıklaması",
      "brand": "Marka Adı",
      "barcode": "1234567890123",
      "categoryName": "Elektronik > Telefon",
      "quantity": 100,
      "listPrice": 1000.00,
      "salePrice": 850.00,
      "currencyType": "TRY",
      "images": [
        {
          "url": "https://cdn.trendyol.com/image1.jpg"
        }
      ],
      "approved": true,
      "hasActiveCampaign": false,
      "createdDate": 1640995200000,
      "lastModifiedDate": 1640995200000
    }
  ],
  "page": 1,
  "size": 50,
  "totalElements": 1250,
  "totalPages": 25
}
```

### 2. Ürün Ekleme

**Endpoint**: `POST /products`

**İstek Gövdesi**:
```json
{
  "items": [
    {
      "barcode": "1234567890123",
      "title": "Yeni Ürün Başlığı",
      "description": "Detaylı ürün açıklaması",
      "brand": "Marka Adı",
      "categoryName": "Elektronik > Telefon",
      "listPrice": 1000.00,
      "salePrice": 850.00,
      "currencyType": "TRY",
      "quantity": 50,
      "stockCode": "STOCK001",
      "dimensionalWeight": 1.5,
      "images": [
        {
          "url": "https://example.com/image1.jpg"
        },
        {
          "url": "https://example.com/image2.jpg"
        }
      ],
      "attributes": [
        {
          "attributeName": "Renk",
          "attributeValue": "Siyah"
        },
        {
          "attributeName": "Hafıza",
          "attributeValue": "128GB"
        }
      ]
    }
  ]
}
```

**Yanıt**:
```json
{
  "batchRequestId": "batch-123456",
  "items": [
    {
      "barcode": "1234567890123",
      "status": "SUCCESS",
      "failureReasons": []
    }
  ]
}
```

### 3. Ürün Güncelleme

**Endpoint**: `POST /products/price-and-inventory`

**İstek Gövdesi**:
```json
{
  "items": [
    {
      "barcode": "1234567890123",
      "quantity": 75,
      "listPrice": 1100.00,
      "salePrice": 950.00
    }
  ]
}
```

### 4. Ürün Silme

**Endpoint**: `DELETE /products`

**İstek Gövdesi**:
```json
{
  "items": [
    {
      "barcode": "1234567890123"
    }
  ]
}
```

## Sipariş API'leri

### 1. Sipariş Listesi Alma

**Endpoint**: `GET /orders`

**Parametreler**:
```json
{
  "page": 0,
  "size": 200,
  "status": "Created",
  "orderByField": "PackageLastModifiedDate",
  "orderByDirection": "DESC",
  "startDate": 1640995200000,
  "endDate": 1641081600000
}
```

**Yanıt**:
```json
{
  "content": [
    {
      "shipmentAddress": {
        "id": 12345,
        "firstName": "Ahmet",
        "lastName": "Yılmaz",
        "address1": "Örnek Mahalle, Örnek Sokak No:1",
        "city": "İstanbul",
        "district": "Kadıköy",
        "postalCode": "34710",
        "countryCode": "TR",
        "neighborhoodName": "Örnek Mahalle",
        "phone": "05551234567"
      },
      "orderNumber": "TY-123456789",
      "grossAmount": 850.00,
      "totalDiscount": 150.00,
      "totalTyDiscount": 50.00,
      "taxNumber": null,
      "invoiceAddress": {
        "id": 12346,
        "firstName": "Ahmet",
        "lastName": "Yılmaz",
        "address1": "Fatura Adresi",
        "city": "İstanbul",
        "district": "Kadıköy"
      },
      "customerFirstName": "Ahmet",
      "customerEmail": "ahmet@example.com",
      "customerId": 987654,
      "orderDate": 1640995200000,
      "tcIdentityNumber": "12345678901",
      "currencyCode": "TRY",
      "packageHistories": [
        {
          "createdDate": 1640995200000,
          "status": "Created"
        }
      ],
      "shipmentPackageStatus": "Created",
      "status": "Created",
      "deliveryType": "Fast",
      "timeSlotId": null,
      "estimatedDeliveryStartDate": 1641081600000,
      "estimatedDeliveryEndDate": 1641168000000,
      "totalPrice": 850.00,
      "deliveryAddressType": "Home",
      "agreedDeliveryDate": null,
      "fastDelivery": true,
      "originShipmentDate": 1641081600000,
      "lastModifiedDate": 1640995200000,
      "commercial": false,
      "fastDeliveryType": "SameDay",
      "deliveredByService": false,
      "agreedDeliveryDateExtendible": false,
      "extendedAgreedDeliveryDate": null,
      "agreedDeliveryExtensionEndDate": null,
      "agreedDeliveryExtensionStartDate": null,
      "warehouseId": 123,
      "micro": false,
      "lines": [
        {
          "quantity": 1,
          "salesCampaignId": 456789,
          "productSize": "Standart",
          "merchantSku": "PROD001",
          "productName": "Örnek Ürün",
          "productCode": 789012,
          "merchantId": 123456,
          "amount": 850.00,
          "discount": 150.00,
          "tyDiscount": 50.00,
          "discountDetails": [
            {
              "lineItemPrice": 1000.00,
              "lineItemDiscount": 150.00,
              "lineItemTyDiscount": 50.00
            }
          ],
          "currencyCode": "TRY",
          "productColor": "Siyah",
          "id": 345678,
          "sku": "SKU001",
          "vatBaseAmount": 708.33,
          "barcode": "1234567890123",
          "orderLineItemStatusName": "Created",
          "price": 850.00
        }
      ]
    }
  ],
  "page": 0,
  "size": 200,
  "totalElements": 45,
  "totalPages": 1
}
```

### 2. Sipariş Onaylama

**Endpoint**: `PUT /orders/{orderNumber}/status`

**İstek Gövdesi**:
```json
{
  "status": "Processing"
}
```

### 3. Kargo Bilgisi Gönderme

**Endpoint**: `POST /orders/{orderNumber}/shipment`

**İstek Gövdesi**:
```json
{
  "trackingNumber": "1234567890",
  "trackingUrl": "https://kargo.com/track/1234567890",
  "shippingCompany": "Aras Kargo"
}
```

## Stok API'leri

### 1. Stok Güncelleme

**Endpoint**: `POST /products/price-and-inventory`

**İstek Gövdesi**:
```json
{
  "items": [
    {
      "barcode": "1234567890123",
      "quantity": 100,
      "listPrice": 1000.00,
      "salePrice": 850.00
    }
  ]
}
```

### 2. Toplu Stok Güncelleme

**Endpoint**: `POST /products/batch-requests/price-and-inventory`

**İstek Gövdesi**:
```json
{
  "items": [
    {
      "barcode": "1234567890123",
      "quantity": 50
    },
    {
      "barcode": "1234567890124",
      "quantity": 75
    }
  ]
}
```

## Kategori API'leri

### 1. Kategori Listesi

**Endpoint**: `GET /product-categories`

**Yanıt**:
```json
{
  "categories": [
    {
      "id": 1001,
      "name": "Elektronik",
      "parentId": null,
      "subCategories": [
        {
          "id": 1002,
          "name": "Telefon",
          "parentId": 1001,
          "subCategories": []
        }
      ]
    }
  ]
}
```

### 2. Kategori Özellikleri

**Endpoint**: `GET /product-categories/{categoryId}/attributes`

**Yanıt**:
```json
{
  "categoryAttributes": [
    {
      "attribute": {
        "id": 338,
        "name": "Renk"
      },
      "attributeValues": [
        {
          "id": 6980,
          "name": "Siyah"
        },
        {
          "id": 6981,
          "name": "Beyaz"
        }
      ],
      "required": true,
      "allowCustom": false,
      "slicer": true,
      "varianter": true
    }
  ]
}
```

## Webhook API'leri

### 1. Webhook Kaydetme

**Endpoint**: `POST /webhooks`

**İstek Gövdesi**:
```json
{
  "url": "https://your-store.com/webhook/trendyol",
  "events": [
    "ORDER_CREATED",
    "ORDER_CANCELLED",
    "PRODUCT_APPROVED",
    "PRODUCT_REJECTED"
  ]
}
```

### 2. Webhook Olayları

#### Sipariş Oluşturuldu
```json
{
  "eventType": "ORDER_CREATED",
  "timestamp": 1640995200000,
  "data": {
    "orderNumber": "TY-123456789",
    "customerId": 987654,
    "totalAmount": 850.00
  }
}
```

#### Ürün Onaylandı
```json
{
  "eventType": "PRODUCT_APPROVED",
  "timestamp": 1640995200000,
  "data": {
    "barcode": "1234567890123",
    "productCode": "PROD001",
    "approvalDate": 1640995200000
  }
}
```

## Hata Kodları

### HTTP Durum Kodları

| Kod | Açıklama | Çözüm |
|-----|----------|-------|
| 200 | OK | İstek başarılı |
| 201 | Created | Kaynak oluşturuldu |
| 400 | Bad Request | İstek formatı hatalı |
| 401 | Unauthorized | Kimlik doğrulama hatası |
| 403 | Forbidden | Yetki yetersiz |
| 404 | Not Found | Kaynak bulunamadı |
| 429 | Too Many Requests | Rate limit aşıldı |
| 500 | Internal Server Error | Sunucu hatası |

### Trendyol Özel Hata Kodları

```json
{
  "errors": [
    {
      "code": "INVALID_BARCODE",
      "message": "Geçersiz barkod formatı",
      "field": "barcode"
    },
    {
      "code": "CATEGORY_NOT_FOUND",
      "message": "Kategori bulunamadı",
      "field": "categoryName"
    },
    {
      "code": "PRICE_TOO_LOW",
      "message": "Fiyat çok düşük",
      "field": "salePrice"
    }
  ]
}
```

### Yaygın Hatalar ve Çözümleri

#### 1. Kimlik Doğrulama Hatası
```json
{
  "error": "Unauthorized",
  "message": "Invalid credentials"
}
```
**Çözüm**: API Key ve Secret bilgilerini kontrol edin.

#### 2. Rate Limit Hatası
```json
{
  "error": "Too Many Requests",
  "message": "Rate limit exceeded",
  "retryAfter": 60
}
```
**Çözüm**: İstek sıklığını azaltın, `retryAfter` süresini bekleyin.

#### 3. Validasyon Hatası
```json
{
  "errors": [
    {
      "code": "REQUIRED_FIELD",
      "message": "Bu alan zorunludur",
      "field": "title"
    }
  ]
}
```
**Çözüm**: Zorunlu alanları doldurun.

## Rate Limiting

### Limit Bilgileri

| Endpoint | Limit | Süre |
|----------|-------|------|
| GET /products | 1000 | 1 saat |
| POST /products | 100 | 1 saat |
| GET /orders | 2000 | 1 saat |
| POST /orders/status | 500 | 1 saat |

### Rate Limit Headers

```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640995200
```

### Rate Limit Yönetimi

```php
<?php
class RateLimitManager {
    private $limits = [];

    public function checkLimit($endpoint) {
        $key = md5($endpoint);
        $now = time();

        if (!isset($this->limits[$key])) {
            $this->limits[$key] = [
                'count' => 0,
                'reset' => $now + 3600
            ];
        }

        if ($now > $this->limits[$key]['reset']) {
            $this->limits[$key] = [
                'count' => 0,
                'reset' => $now + 3600
            ];
        }

        $this->limits[$key]['count']++;

        return $this->limits[$key]['count'] <= 1000;
    }

    public function waitIfNeeded($endpoint) {
        if (!$this->checkLimit($endpoint)) {
            $waitTime = $this->limits[md5($endpoint)]['reset'] - time();
            sleep($waitTime);
        }
    }
}
?>
```

## SDK Kullanımı

### PHP SDK

#### Kurulum
```bash
composer require meschain/trendyol-sdk
```

#### Temel Kullanım
```php
<?php
require_once 'vendor/autoload.php';

use MesChain\TrendyolSDK\TrendyolClient;

// Client oluşturma
$client = new TrendyolClient([
    'api_key' => 'your_api_key',
    'api_secret' => 'your_api_secret',
    'supplier_id' => 'your_supplier_id',
    'base_url' => 'https://api.trendyol.com'
]);

// Ürün listesi alma
$products = $client->products()->list([
    'page' => 1,
    'size' => 50
]);

// Ürün ekleme
$result = $client->products()->create([
    'barcode' => '1234567890123',
    'title' => 'Yeni Ürün',
    'description' => 'Ürün açıklaması',
    'brand' => 'Marka',
    'categoryName' => 'Elektronik > Telefon',
    'listPrice' => 1000.00,
    'salePrice' => 850.00,
    'quantity' => 50
]);

// Sipariş listesi alma
$orders = $client->orders()->list([
    'status' => 'Created',
    'startDate' => strtotime('-7 days') * 1000,
    'endDate' => time() * 1000
]);

// Sipariş onaylama
$client->orders()->updateStatus('TY-123456789', 'Processing');
?>
```

#### Gelişmiş Kullanım
```php
<?php
// Hata yönetimi
try {
    $products = $client->products()->list();
} catch (TrendyolApiException $e) {
    echo "API Hatası: " . $e->getMessage();
    echo "Hata Kodu: " . $e->getCode();
} catch (TrendyolRateLimitException $e) {
    echo "Rate limit aşıldı. Bekleme süresi: " . $e->getRetryAfter();
}

// Batch işlemler
$batchProducts = [
    [
        'barcode' => '1234567890123',
        'title' => 'Ürün 1',
        // ... diğer alanlar
    ],
    [
        'barcode' => '1234567890124',
        'title' => 'Ürün 2',
        // ... diğer alanlar
    ]
];

$result = $client->products()->batchCreate($batchProducts);

// Webhook yönetimi
$client->webhooks()->register([
    'url' => 'https://your-store.com/webhook',
    'events' => ['ORDER_CREATED', 'PRODUCT_APPROVED']
]);
?>
```

### JavaScript SDK

#### Kurulum
```bash
npm install @meschain/trendyol-sdk
```

#### Kullanım
```javascript
const TrendyolClient = require('@meschain/trendyol-sdk');

const client = new TrendyolClient({
    apiKey: 'your_api_key',
    apiSecret: 'your_api_secret',
    supplierId: 'your_supplier_id',
    baseUrl: 'https://api.trendyol.com'
});

// Async/await kullanımı
async function getProducts() {
    try {
        const products = await client.products.list({
            page: 1,
            size: 50
        });
        console.log(products);
    } catch (error) {
        console.error('Hata:', error.message);
    }
}

// Promise kullanımı
client.orders.list({ status: 'Created' })
    .then(orders => {
        console.log('Siparişler:', orders);
    })
    .catch(error => {
        console.error('Hata:', error);
    });
```

---

## API Test Araçları

### Postman Collection

Postman collection dosyasını indirin:
```bash
curl -O https://api.meschain.com/trendyol/postman-collection.json
```

### Test Scriptleri

```bash
# API bağlantı testi
php tests/api_connection_test.php

# Ürün API testi
php tests/product_api_test.php

# Sipariş API testi
php tests/order_api_test.php

# Performans testi
php tests/api_performance_test.php
```

---

**API Dokümantasyonu Tamamlandı!** 📚

Bu dokümantasyon, Trendyol API'lerini etkin bir şekilde kullanmanız için gerekli tüm bilgileri içerir.

**MesChain Trendyol Entegrasyonu v1.0.0**
**Son Güncelleme**: 21 Haziran 2025
**Durum**: Aktif ve Destekleniyor ✅
