# MesChain Trendyol Entegrasyonu - Kurulum Rehberi

## OpenCart 4.x Uyumlu Sürüm

### Sistem Gereksinimleri

- **OpenCart:** 4.0.2.3 veya üzeri
- **PHP:** 7.4 veya üzeri (8.0+ önerilir)
- **MySQL:** 5.7 veya üzeri / MariaDB 10.3 veya üzeri
- **PHP Eklentileri:**
  - cURL (API çağrıları için)
  - GD (barkod üretimi için)
  - JSON (veri işleme için)
  - OpenSSL (güvenli bağlantılar için)
- **Sunucu Gereksinimleri:**
  - SSL sertifikası (webhook'lar için)
  - Cron job desteği (otomatik senkronizasyon için)
  - En az 256MB RAM
  - En az 100MB disk alanı

### Kurulum Adımları

#### 1. Dosyaları Yükleme

```bash
# RESTRUCTURED_UPLOAD klasöründeki tüm dosyaları OpenCart root dizinine kopyalayın
cp -r RESTRUCTURED_UPLOAD/upload/* /path/to/opencart/
cp -r RESTRUCTURED_UPLOAD/install/* /path/to/opencart/install/
```

#### 2. Veritabanı Kurulumu

```sql
-- MySQL/MariaDB'ye bağlanın ve install klasöründeki SQL dosyasını çalıştırın
mysql -u username -p database_name < install/meschain_trendyol_install.sql
```

#### 3. Dosya İzinleri

```bash
# Gerekli klasörlere yazma izni verin
chmod 755 system/library/meschain/
chmod 755 system/library/meschain/api/
chmod 755 system/library/meschain/barcode/
chmod 755 admin/controller/extension/meschain/
chmod 755 admin/model/extension/meschain/
chmod 755 admin/view/template/extension/meschain/
chmod 755 catalog/controller/extension/meschain/
```

#### 4. Admin Paneli Kurulumu

1. OpenCart admin paneline giriş yapın
2. **Extensions > Extensions** menüsüne gidin
3. **Choose the extension type** dropdown'dan **Modules** seçin
4. **MesChain Trendyol Integration** modülünü bulun ve **Install** butonuna tıklayın
5. Kurulum tamamlandıktan sonra **Edit** butonuna tıklayın

#### 5. Trendyol API Ayarları

1. **API Settings** sekmesine gidin
2. Trendyol Seller Center'dan aldığınız bilgileri girin:
   - **API Key:** Trendyol API anahtarınız
   - **API Secret:** Trendyol API gizli anahtarınız
   - **Supplier ID:** Tedarikçi ID numaranız
3. **Test Mode** seçeneğini geliştirme ortamında aktif edin
4. **Test Connection** butonuna tıklayarak bağlantıyı test edin

#### 6. Webhook Kurulumu

1. **Webhook Settings** sekmesine gidin
2. Webhook URL'yi kopyalayın: `https://yourdomain.com/index.php?route=extension/meschain/webhook/trendyol`
3. Trendyol Seller Center'da webhook ayarlarına gidin
4. Kopyaladığınız URL'yi webhook URL olarak ayarlayın
5. Webhook secret anahtarını oluşturun ve her iki yerde de aynı değeri kullanın

#### 7. Senkronizasyon Ayarları

1. **Synchronization** sekmesine gidin
2. **Auto Synchronization** seçeneğini aktif edin
3. İlk senkronizasyon için **Sync Products** ve **Sync Orders** butonlarını kullanın

### Cron Job Kurulumu (Opsiyonel)

Otomatik senkronizasyon için cron job'ları kurun:

```bash
# Her 15 dakikada bir ürün senkronizasyonu
*/15 * * * * /usr/bin/php /path/to/opencart/system/library/meschain/cron/sync_products.php

# Her 5 dakikada bir sipariş senkronizasyonu
*/5 * * * * /usr/bin/php /path/to/opencart/system/library/meschain/cron/sync_orders.php

# Günlük log temizliği
0 2 * * * /usr/bin/php /path/to/opencart/system/library/meschain/cron/cleanup_logs.php
```

### E-Fatura Entegrasyonu (Opsiyonel)

E-fatura özelliğini kullanmak için:

1. **General Settings** sekmesinde **E-Invoice Integration** seçeneğini aktif edin
2. GIB (Gelir İdaresi Başkanlığı) e-Arşiv sistemi kullanıcı bilgilerinizi girin
3. Test modunda deneme yapın
4. Canlı ortama geçmeden önce tüm ayarları kontrol edin

### Barkod Üretimi

Barkod üretimi için:

1. **Product Management** sayfasına gidin
2. Ürün listesinde **Generate Barcode** seçeneğini kullanın
3. EAN-13, EAN-8, Code 128 formatları desteklenir
4. Üretilen barkodlar otomatik olarak ürün bilgilerine eklenir

### Sorun Giderme

#### Yaygın Sorunlar ve Çözümleri

**1. API Bağlantı Hatası**
```
Hata: "API connection failed"
Çözüm:
- API anahtarlarını kontrol edin
- Sunucu IP adresinin Trendyol'da tanımlı olduğundan emin olun
- SSL sertifikasının geçerli olduğunu kontrol edin
```

**2. Webhook Çalışmıyor**
```
Hata: Webhook'lar alınmıyor
Çözüm:
- Webhook URL'nin doğru olduğunu kontrol edin
- SSL sertifikasının geçerli olduğunu kontrol edin
- Webhook secret anahtarının her iki yerde de aynı olduğunu kontrol edin
```

**3. Ürün Senkronizasyon Hatası**
```
Hata: "Product sync failed"
Çözüm:
- Ürün bilgilerinin eksiksiz olduğunu kontrol edin
- Kategori eşleştirmelerini kontrol edin
- API rate limit'ine takılmadığınızdan emin olun
```

**4. Barkod Üretim Hatası**
```
Hata: "Barcode generation failed"
Çözüm:
- GD extension'ının yüklü olduğunu kontrol edin
- Dosya yazma izinlerini kontrol edin
- Barkod formatının doğru olduğunu kontrol edin
```

### Performans Optimizasyonu

#### Veritabanı Optimizasyonu

```sql
-- İndeksleri optimize edin
OPTIMIZE TABLE meschain_trendyol_products;
OPTIMIZE TABLE meschain_trendyol_orders;
OPTIMIZE TABLE meschain_trendyol_api_logs;

-- Eski logları temizleyin (30 günden eski)
CALL CleanupOldLogs(30);
```

#### Sunucu Optimizasyonu

```php
// php.ini ayarları
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 10M
post_max_size = 10M
```

### Güvenlik Ayarları

#### Webhook Güvenliği

```php
// Webhook IP whitelist (opsiyonel)
$allowed_ips = [
    '185.125.190.0/24',  // Trendyol IP aralığı
    '185.125.191.0/24'   // Trendyol IP aralığı
];
```

#### API Güvenliği

- API anahtarlarını güvenli bir yerde saklayın
- HTTPS kullanın
- Rate limiting uygulayın
- Log dosyalarını düzenli olarak kontrol edin

### Monitoring ve Loglama

#### Log Dosyaları

```bash
# API logları
tail -f system/storage/logs/meschain_trendyol_api.log

# Webhook logları
tail -f system/storage/logs/meschain_trendyol_webhook.log

# Hata logları
tail -f system/storage/logs/meschain_trendyol_error.log
```

#### Dashboard Metrikleri

Admin panelinde **Trendyol Dashboard** sayfasından:
- Günlük/haftalık/aylık sipariş sayıları
- Aktif/bekleyen/reddedilen ürün sayıları
- API durumu ve son senkronizasyon zamanı
- Son webhook'lar ve API çağrıları

### Yedekleme ve Geri Yükleme

#### Veritabanı Yedeği

```bash
# Trendyol tablolarını yedekle
mysqldump -u username -p database_name \
  meschain_trendyol_products \
  meschain_trendyol_orders \
  meschain_trendyol_api_logs \
  meschain_trendyol_webhooks \
  meschain_trendyol_categories \
  meschain_trendyol_brands \
  meschain_trendyol_settings \
  > trendyol_backup_$(date +%Y%m%d).sql
```

#### Dosya Yedeği

```bash
# Konfigürasyon ve özel dosyaları yedekle
tar -czf trendyol_files_backup_$(date +%Y%m%d).tar.gz \
  system/library/meschain/ \
  admin/controller/extension/meschain/ \
  admin/model/extension/meschain/ \
  admin/view/template/extension/meschain/ \
  catalog/controller/extension/meschain/
```

### Destek ve Güncellemeler

#### Teknik Destek

- **E-posta:** support@meschain.com
- **Dokümantasyon:** https://docs.meschain.com/trendyol
- **GitHub:** https://github.com/meschain/opencart-trendyol

#### Güncelleme Kontrolü

```bash
# Güncel sürümü kontrol et
curl -s https://api.meschain.com/extensions/trendyol/version
```

#### Güncelleme Adımları

1. Mevcut dosyaları yedekleyin
2. Veritabanını yedekleyin
3. Yeni dosyaları yükleyin
4. Veritabanı güncellemelerini çalıştırın
5. Cache'i temizleyin
6. Ayarları kontrol edin

### Lisans ve Kullanım Koşulları

Bu yazılım MesChain tarafından geliştirilmiştir ve aşağıdaki koşullar altında kullanılabilir:

- Ticari kullanım için lisans gereklidir
- Kaynak kodu değiştirilebilir ancak telif hakkı korunmalıdır
- Yeniden dağıtım için izin gereklidir
- Destek sadece lisanslı kullanıcılara sağlanır

### Sürüm Geçmişi

- **v1.0.0** - İlk OpenCart 4.x uyumlu sürüm
  - Trendyol API v2 desteği
  - Modern TWIG template'leri
  - E-fatura entegrasyonu
  - Barkod üretim sistemi
  - Çoklu dil desteği (TR/EN)
  - Responsive admin arayüzü

---

**Not:** Bu dokümantasyon sürekli güncellenmektedir. En güncel sürüm için lütfen resmi web sitemizi ziyaret edin.
