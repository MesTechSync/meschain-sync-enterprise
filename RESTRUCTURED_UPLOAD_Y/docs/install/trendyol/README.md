# Trendyol Modülü - Kurulum ve Yapılandırma Kılavuzu

**Versiyon:** 4.5  
**Tarih:** 20 Haziran 2025  
**Uyumluluk:** OpenCart 4.0.2.3+, PHP 8.0+, MesChain-Sync Enterprise

---

## 📋 İçindekiler

1. [Sistem Gereksinimleri](#sistem-gereksinimleri)
2. [Kurulum Yöntemleri](#kurulum-yöntemleri)
3. [Azure Bulut Entegrasyonu](#azure-bulut-entegrasyonu)
4. [Güvenlik Yapılandırması](#güvenlik-yapılandırması)
5. [API Yapılandırması](#api-yapılandırması)
6. [Test ve Doğrulama](#test-ve-doğrulama)
7. [Sorun Giderme](#sorun-giderme)
8. [Performans Optimizasyonu](#performans-optimizasyonu)

---

## 🔧 Sistem Gereksinimleri

### Minimum Gereksinimler
- **OpenCart:** 4.0.2.3 veya üzeri
- **PHP:** 8.0+ (8.1+ önerilir)
- **MySQL:** 5.7+ veya MariaDB 10.3+
- **Web Sunucu:** Apache 2.4+ veya Nginx 1.18+
- **SSL Sertifikası:** HTTPS desteği (Trendyol API gereksinimi)
- **Memory Limit:** Minimum 256MB (512MB önerilir)

### Azure Bulut Gereksinimleri
- **Azure App Service:** Basic B1 veya üzeri
- **Azure Storage Account:** Standart LRS
- **Azure Key Vault:** API anahtarları için
- **Azure Monitor:** Loglama ve izleme
- **Azure CDN:** Statik dosyalar için (opsiyonel)

### Trendyol API Gereksinimleri
- **Trendyol Satıcı Hesabı:** Aktif hesap
- **API Anahtarları:** API Key, Secret ve Supplier ID
- **Test Ortamı:** Sandbox erişimi (geliştirme için)

---

## 🚀 Kurulum Yöntemleri

### Yöntem 1: Bağımsız Kurulum (Sadece Trendyol)

```bash
# 1. Dosyaları indirin
git clone https://github.com/meschain/trendyol-opencart-module.git
cd trendyol-opencart-module

# 2. OpenCart dizinine kopyalayın
cp -r upload/* /path/to/opencart/

# 3. OCMOD paketini kurun
# Admin Panel > Extensions > Installer > trendyol-module.ocmod.zip
```

### Yöntem 2: MesChain-Sync ile Entegre Kurulum

```bash
# 1. MesChain-Sync kurulu olmalı
# 2. Trendyol modülünü etkinleştirin
# Admin Panel > Extensions > Extensions > MesChain SYNC > Trendyol
```

### Yöntem 3: Azure Otomatik Deployment

```bash
# 1. Azure CLI ile giriş yapın
az login

# 2. Kaynak grubunu oluşturun
az group create --name "meschain-trendyol-rg" --location "West Europe"

# 3. App Service planını oluşturun
az appservice plan create --name "meschain-plan" --resource-group "meschain-trendyol-rg" --sku B1

# 4. Web uygulamasını dağıtın
az webapp create --name "your-opencart-app" --resource-group "meschain-trendyol-rg" --plan "meschain-plan"

# 5. Trendyol modülünü dağıtın
az webapp deployment source config-zip --resource-group "meschain-trendyol-rg" --name "your-opencart-app" --src trendyol-module.zip
```

---

## ☁️ Azure Bulut Entegrasyonu

### Azure Key Vault Yapılandırması

```bash
# 1. Key Vault oluşturun
az keyvault create --name "trendyol-keyvault" --resource-group "meschain-trendyol-rg" --location "West Europe"

# 2. API anahtarlarını ekleyin
az keyvault secret set --vault-name "trendyol-keyvault" --name "TrendyolApiKey" --value "your-api-key"
az keyvault secret set --vault-name "trendyol-keyvault" --name "TrendyolApiSecret" --value "your-api-secret"
az keyvault secret set --vault-name "trendyol-keyvault" --name "TrendyolSupplierId" --value "your-supplier-id"
```

### Azure Storage Yapılandırması

```bash
# 1. Storage Account oluşturun
az storage account create --name "trendyolstorage" --resource-group "meschain-trendyol-rg" --location "West Europe" --sku Standard_LRS

# 2. Container oluşturun
az storage container create --name "product-images" --account-name "trendyolstorage" --public-access blob
az storage container create --name "logs" --account-name "trendyolstorage" --public-access off
```

---

## 🔐 Güvenlik Yapılandırması

### JWT Token Ayarları

```php
// config/trendyol_config.php
return [
    'jwt' => [
        'secret' => env('JWT_SECRET', 'your-jwt-secret-key'),
        'algorithm' => 'HS256',
        'expiration' => 3600, // 1 saat
    ],
    'azure_ad' => [
        'tenant_id' => env('AZURE_TENANT_ID'),
        'client_id' => env('AZURE_CLIENT_ID'),
        'client_secret' => env('AZURE_CLIENT_SECRET'),
    ]
];
```

### SSL/TLS Yapılandırması

```nginx
# nginx.conf
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    
    # Trendyol webhook endpoint'leri için özel yapılandırma
    location /trendyol/webhook {
        proxy_pass http://localhost:8080;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

---

## 🔌 API Yapılandırması

### Trendyol API Ayarları

1. **Admin Panel** > **Extensions** > **Extensions** > **MesChain SYNC** > **Trendyol**
2. Aşağıdaki bilgileri girin:

```
API Key: [Trendyol'dan aldığınız API anahtarı]
API Secret: [Trendyol'dan aldığınız gizli anahtar]
Supplier ID: [Satıcı ID'niz]
Test Modu: [Geliştirme için aktif edin]
Webhook URL: https://your-domain.com/index.php?route=extension/module/trendyol_webhook
```

### Webhook Yapılandırması

```php
// Trendyol webhook ayarları
$webhook_config = [
    'events' => [
        'orderCreated',
        'orderStatusChanged', 
        'orderCancelled',
        'stockUpdated',
        'priceUpdated'
    ],
    'url' => 'https://your-domain.com/index.php?route=extension/module/trendyol_webhook',
    'secret' => 'your-webhook-secret'
];
```

---

## 🧪 Test ve Doğrulama

### Bağlantı Testi

```bash
# 1. API bağlantısını test edin
curl -X GET "https://api.trendyol.com/sapigw/suppliers/{supplierId}/products" \
     -H "Authorization: Basic $(echo -n 'api_key:api_secret' | base64)"

# 2. Webhook endpoint'ini test edin
curl -X POST "https://your-domain.com/index.php?route=extension/module/trendyol_webhook" \
     -H "Content-Type: application/json" \
     -d '{"test": true}'
```

### Fonksiyonel Testler

1. **Ürün Senkronizasyonu**
   - OpenCart'tan Trendyol'a ürün gönderimi
   - Trendyol'dan OpenCart'a ürün çekimi
   - Stok ve fiyat senkronizasyonu

2. **Sipariş Yönetimi**
   - Trendyol siparişlerinin OpenCart'a aktarımı
   - Sipariş durumu güncellemeleri
   - Kargo takip bilgileri

3. **Kategori Eşleştirme**
   - OpenCart kategorilerinin Trendyol kategorileriyle eşleştirilmesi
   - Otomatik kategori önerisi

---

## 🔧 Sorun Giderme

### Sık Karşılaşılan Problemler

#### 1. API Bağlantı Hatası
```
Hata: SSL certificate problem: unable to get local issuer certificate
Çözüm: cURL CA sertifikalarını güncelleyin veya SSL doğrulamayı devre dışı bırakın (sadece test için)
```

#### 2. Webhook Çalışmıyor
```
Kontrol Listesi:
- Webhook URL'si doğru mu?
- SSL sertifikası geçerli mi?
- Firewall webhook trafiğini engelliyor mu?
- Webhook secret doğru mu?
```

#### 3. Ürün Senkronizasyon Hatası
```
Kontrol Listesi:
- Ürün verisi eksik mi? (başlık, açıklama, fiyat, stok)
- Kategori eşleştirmesi yapıldı mı?
- Ürün görselleri yüklü mu?
- Barcode/SKU benzersiz mi?
```

### Log Dosyaları

```bash
# Trendyol modül logları
tail -f storage/logs/trendyol.log

# Webhook logları  
tail -f storage/logs/trendyol_webhook.log

# API istekleri
tail -f storage/logs/trendyol_api.log
```

---

## ⚡ Performans Optimizasyonu

### Cache Yapılandırması

```php
// config/cache.php
return [
    'trendyol' => [
        'product_cache_ttl' => 3600, // 1 saat
        'category_cache_ttl' => 86400, // 24 saat
        'api_response_cache_ttl' => 300, // 5 dakika
    ]
];
```

### Queue Yapılandırması

```php
// config/queue.php
return [
    'trendyol_sync' => [
        'driver' => 'database',
        'table' => 'trendyol_jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'batch_size' => 50,
    ]
];
```

### Azure CDN Entegrasyonu

```bash
# CDN profili oluşturun
az cdn profile create --name "trendyol-cdn" --resource-group "meschain-trendyol-rg" --sku Standard_Microsoft

# CDN endpoint oluşturun
az cdn endpoint create --name "trendyol-static" --profile-name "trendyol-cdn" --resource-group "meschain-trendyol-rg" --origin "your-storage-account.blob.core.windows.net"
```

---

## 📞 Destek ve Kaynaklar

- **Teknik Destek:** support@meschain.com
- **Dokümantasyon:** https://docs.meschain.com/trendyol
- **GitHub:** https://github.com/meschain/trendyol-opencart-module
- **Trendyol API Dokümantasyonu:** https://developers.trendyol.com

---

## 📝 Sürüm Notları

### v4.5 (20 Haziran 2025)
- Azure bulut entegrasyonu eklendi
- JWT güvenlik katmanı eklendi
- Real-time senkronizasyon iyileştirildi
- Performance monitoring eklendi

### v4.4 (15 Haziran 2025)
- MesChain-Sync Enterprise entegrasyonu
- Çoklu dil desteği eklendi
- Webhook güvenliği artırıldı

### v4.3 (10 Haziran 2025)
- İlk kararlı sürüm
- Temel Trendyol API entegrasyonu
- OCMOD paket desteği
