# MesChain-Sync: API Dokümantasyonu

Bu belge, MesChain-Sync Trendyol entegrasyon modülünün API yapısını ve kullanımını açıklar.

## Trendyol API Entegrasyonu

### API Bilgileri

MesChain-Sync, Trendyol API v2'yi kullanır. Entegrasyon için aşağıdaki bilgiler gereklidir:

- **API Anahtarı**: Trendyol satıcı panelinden alınan API anahtarı
- **API Gizli Anahtarı**: Trendyol satıcı panelinden alınan API gizli anahtarı
- **Tedarikçi ID**: Trendyol satıcı hesabınıza ait tedarikçi ID

### Kimlik Doğrulama

Tüm API istekleri, temel kimlik doğrulama (Basic Authentication) kullanılarak yapılır:

```php
$auth = base64_encode($apiKey . ':' . $apiSecret);
$headers = [
    'Authorization: Basic ' . $auth,
    'Content-Type: application/json',
    'User-Agent: MesChain-Sync/1.0.0'
];
```

### Endpoint URL'leri

Trendyol API temel URL'i: `https://api.trendyol.com/sapigw`

| İşlev | Endpoint | HTTP Metodu |
|-------|----------|-------------|
| Ürün Listeleme | `/suppliers/{supplierId}/products` | GET |
| Ürün Oluşturma | `/suppliers/{supplierId}/v2/products` | POST |
| Ürün Güncelleme | `/suppliers/{supplierId}/v2/products` | PUT |
| Sipariş Listeleme | `/suppliers/{supplierId}/orders` | GET |
| Sipariş Detayı | `/suppliers/{supplierId}/orders?orderNumber={orderNumber}` | GET |
| Stok Güncelleme | `/suppliers/{supplierId}/products/price-and-inventory` | POST |
| Fiyat Güncelleme | `/suppliers/{supplierId}/products/price-and-inventory` | POST |
| Kategoriler | `/product-categories` | GET |
| Markalar | `/brands` | GET |

## Helper Sınıfı Kullanımı

MesChain-Sync, Trendyol API ile etkileşim için `TrendyolHelper` sınıfını kullanır:

```php
// Helper sınıfını başlat
$helper = new TrendyolHelper($apiKey, $apiSecret, $supplierId);

// Bağlantıyı test et
$isConnected = $helper->testConnection();

// Ürün gönder
$result = $helper->sendProduct($product);

// Siparişleri al
$orders = $helper->getOrders($startDate, $endDate, $status);

// Stok güncelle
$result = $helper->updateStock($productId, $quantity);

// Fiyat güncelle
$result = $helper->updatePrice($productId, $price);
```

## Veri Yapıları

### Ürün Veri Yapısı

```json
{
  "items": [
    {
      "barcode": "URUN123456",
      "title": "Ürün Başlığı",
      "productMainId": "ANAID123456",
      "brandId": 1234,
      "categoryId": 5678,
      "quantity": 100,
      "stockCode": "STOK123456",
      "dimensionalWeight": 2,
      "description": "Ürün açıklaması",
      "currencyType": "TRY",
      "listPrice": 159.99,
      "salePrice": 129.99,
      "vatRate": 18,
      "cargoCompanyId": 10,
      "images": [
        {
          "url": "https://example.com/images/urun1.jpg"
        }
      ],
      "attributes": [
        {
          "attributeId": 338,
          "attributeValueId": 6980
        }
      ]
    }
  ]
}
```

### Sipariş Veri Yapısı

```json
{
  "orders": [
    {
      "orderNumber": "123456789",
      "orderDate": "2023-09-15T10:30:45",
      "status": "Created",
      "shipmentAddress": {
        "firstName": "Ali",
        "lastName": "Yılmaz",
        "address1": "Örnek Mahallesi, Örnek Sokak No:1",
        "address2": "Daire 5",
        "city": "İstanbul",
        "district": "Kadıköy",
        "postalCode": "34000",
        "countryCode": "TR",
        "phone": "5321234567"
      },
      "invoiceAddress": {
        "firstName": "Ali",
        "lastName": "Yılmaz",
        "address1": "Örnek Mahallesi, Örnek Sokak No:1",
        "address2": "Daire 5",
        "city": "İstanbul",
        "district": "Kadıköy",
        "postalCode": "34000",
        "countryCode": "TR",
        "phone": "5321234567"
      },
      "totalPrice": 129.99,
      "totalDiscount": 30.00,
      "lines": [
        {
          "lineId": "1",
          "quantity": 1,
          "productId": "URUN123456",
          "productName": "Ürün Başlığı",
          "price": 129.99,
          "vatBaseAmount": 110.16,
          "barcode": "URUN123456"
        }
      ]
    }
  ]
}
```

## Hata Kodları ve Mesajlar

| Kod | Mesaj | Açıklama |
|-----|-------|----------|
| 400 | Bad Request | İstek formatı veya parametreleri hatalı |
| 401 | Unauthorized | API kimlik bilgileri geçersiz |
| 403 | Forbidden | İstek yapılan kaynağa erişim izni yok |
| 404 | Not Found | İstenen kaynak bulunamadı |
| 429 | Too Many Requests | API istek limiti aşıldı |
| 500 | Internal Server Error | Trendyol sunucusunda hata |

## Örnek Kodlar

### Ürün Gönderme

```php
// Ürün verisi
$product = array(
    'items' => array(
        array(
            'barcode' => 'URUN123456',
            'title' => 'Ürün Başlığı',
            'productMainId' => 'ANAID123456',
            'brandId' => 1234,
            'categoryId' => 5678,
            'quantity' => 100,
            'stockCode' => 'STOK123456',
            'dimensionalWeight' => 2,
            'description' => 'Ürün açıklaması',
            'currencyType' => 'TRY',
            'listPrice' => 159.99,
            'salePrice' => 129.99,
            'vatRate' => 18,
            'cargoCompanyId' => 10,
            'images' => array(
                array('url' => 'https://example.com/images/urun1.jpg')
            ),
            'attributes' => array(
                array('attributeId' => 338, 'attributeValueId' => 6980)
            )
        )
    )
);

// Ürünü gönder
$result = $helper->sendProduct($product);

if ($result['success']) {
    echo "Ürün başarıyla gönderildi. Batch ID: " . $result['batchId'];
} else {
    echo "Hata: " . $result['message'];
}
```

### Siparişleri Alma

```php
// Son 7 gündeki siparişleri al
$startDate = date('Y-m-d\TH:i:s', strtotime('-7 days'));
$endDate = date('Y-m-d\TH:i:s');
$status = "Created"; // "Created", "Picking", "Invoiced", "Shipped", "Delivered", "Cancelled"

// Siparişleri al
$orders = $helper->getOrders($startDate, $endDate, $status);

if (isset($orders['orders']) && !empty($orders['orders'])) {
    foreach ($orders['orders'] as $order) {
        echo "Sipariş No: " . $order['orderNumber'] . "<br>";
        echo "Tarih: " . $order['orderDate'] . "<br>";
        echo "Durum: " . $order['status'] . "<br>";
        echo "Toplam Tutar: " . $order['totalPrice'] . "<br>";
        echo "--------------------------<br>";
    }
} else {
    echo "Belirtilen kriterlere uygun sipariş bulunamadı.";
}
```

## Webhook Kullanımı

MesChain-Sync henüz webhook desteklememektedir. Bu özellik, gelecek sürümlerde eklenecektir.

## API İstek Limitleri

Trendyol API'si, aşağıdaki istek limitlerini uygular:

- Ürün Listeleme: 100 istek/dakika
- Sipariş Listeleme: 30 istek/dakika
- Stok/Fiyat Güncelleme: 40 istek/dakika

## Sürüm Geçmişi

| Sürüm | Tarih | Değişiklikler |
|-------|-------|---------------|
| 1.0.0 | 2023-09-01 | İlk API sürümü |
| 0.9.0 | 2023-08-15 | Beta sürümü |

## Güvenlik Notları

- API anahtarlarınızı güvenli bir şekilde saklayın
- Tüm API isteklerini HTTPS üzerinden yapın
- API anahtarlarınızı düzenli olarak yenileyin
- İstek limitlerini aşmamaya dikkat edin

## İletişim

API entegrasyonu hakkında sorularınız için:
- E-posta: api@meschain-sync.com
- Destek sayfası: https://meschain-sync.com/api-support 