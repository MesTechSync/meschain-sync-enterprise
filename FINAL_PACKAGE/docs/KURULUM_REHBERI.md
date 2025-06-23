# MesChain Trendyol Entegrasyonu - Kurulum Rehberi

## İçindekiler

1. [Ön Hazırlık](#ön-hazırlık)
2. [Sistem Gereksinimleri](#sistem-gereksinimleri)
3. [Trendyol Hesap Ayarları](#trendyol-hesap-ayarları)
4. [OpenCart Hazırlığı](#opencart-hazırlığı)
5. [Entegrasyon Kurulumu](#entegrasyon-kurulumu)
6. [Yapılandırma](#yapılandırma)
7. [Test ve Doğrulama](#test-ve-doğrulama)
8. [Cron Job Ayarları](#cron-job-ayarları)
9. [İzleme Sistemi Kurulumu](#izleme-sistemi-kurulumu)
10. [Sorun Giderme](#sorun-giderme)

## Ön Hazırlık

### Kurulum Öncesi Kontrol Listesi

- [ ] PHP 7.4 veya üzeri kurulu
- [ ] OpenCart 4.x kurulu ve çalışır durumda
- [ ] MySQL/MariaDB veritabanı erişimi
- [ ] SSL sertifikası aktif
- [ ] Trendyol satıcı hesabı onaylanmış
- [ ] API erişim bilgileri alınmış
- [ ] Sunucu yönetici erişimi mevcut

### Gerekli Bilgiler

Kurulum öncesinde aşağıdaki bilgileri hazır bulundurun:

```
Trendyol Bilgileri:
- Supplier ID: _______________
- API Key: __________________
- API Secret: _______________

OpenCart Bilgileri:
- Site URL: _________________
- Admin Klasörü: ____________
- Veritabanı Adı: ___________
- Veritabanı Kullanıcısı: ___
- Veritabanı Şifresi: _______

Sunucu Bilgileri:
- Sunucu IP: ________________
- SSH Kullanıcısı: __________
- Web Sunucusu: Apache/Nginx
```

## Sistem Gereksinimleri

### Minimum Sistem Gereksinimleri

| Bileşen | Minimum Sürüm | Önerilen Sürüm |
|---------|----------------|-----------------|
| PHP | 7.4 | 8.1+ |
| OpenCart | 4.0 | 4.0.2+ |
| MySQL | 5.7 | 8.0+ |
| Apache | 2.4 | 2.4.50+ |
| Nginx | 1.18 | 1.22+ |
| RAM | 2GB | 4GB+ |
| Disk | 1GB | 5GB+ |

### PHP Eklentileri

Aşağıdaki PHP eklentilerinin kurulu olduğundan emin olun:

```bash
# Gerekli eklentileri kontrol edin
php -m | grep -E "(curl|json|mbstring|pdo|openssl|zip|xml)"

# Eksik eklentileri kurun (Ubuntu/Debian)
sudo apt-get install php-curl php-json php-mbstring php-mysql php-zip php-xml

# Eksik eklentileri kurun (CentOS/RHEL)
sudo yum install php-curl php-json php-mbstring php-mysql php-zip php-xml
```

### Composer Kurulumu

```bash
# Composer'ı indirin ve kurun
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Kurulumu doğrulayın
composer --version
```

## Trendyol Hesap Ayarları

### 1. Satıcı Hesabı Oluşturma

1. [Trendyol Satıcı Paneli](https://partner.trendyol.com)'ne gidin
2. "Satıcı Ol" butonuna tıklayın
3. Gerekli bilgileri doldurun ve başvurunuzu tamamlayın
4. Onay sürecini bekleyin (genellikle 3-5 iş günü)

### 2. API Erişim Bilgilerini Alma

1. Satıcı paneline giriş yapın
2. **Entegrasyonlar** > **API Yönetimi** bölümüne gidin
3. **Yeni API Anahtarı Oluştur** butonuna tıklayın
4. Aşağıdaki bilgileri kaydedin:
   - Supplier ID
   - API Key
   - API Secret

### 3. Kategori Onayları

1. **Ürünler** > **Kategori Yönetimi** bölümüne gidin
2. Satmak istediğiniz kategoriler için onay alın
3. Kategori eşleştirmelerini not alın

### 4. Kargo Anlaşmaları

1. **Kargo** > **Kargo Firmaları** bölümüne gidin
2. Anlaşmalı kargo firmalarını seçin
3. Kargo ayarlarını yapılandırın

## OpenCart Hazırlığı

### 1. OpenCart Güncelleme

```bash
# Mevcut sürümü kontrol edin
grep "VERSION" system/startup.php

# Yedek alın
cp -r /path/to/opencart /path/to/opencart_backup_$(date +%Y%m%d)

# OpenCart 4.x'e güncelleyin (gerekirse)
# Resmi güncelleme talimatlarını takip edin
```

### 2. Gerekli Eklentiler

OpenCart admin panelinden aşağıdaki eklentileri kurun:

- **cURL Support**: API iletişimi için
- **JSON Support**: Veri formatı için
- **Image Processing**: Görsel işleme için

### 3. Veritabanı Optimizasyonu

```sql
-- Performans için indeksler oluşturun
ALTER TABLE oc_product ADD INDEX idx_model (model);
ALTER TABLE oc_product ADD INDEX idx_sku (sku);
ALTER TABLE oc_order ADD INDEX idx_date_added (date_added);

-- Tablo boyutlarını kontrol edin
SELECT
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'your_opencart_db'
ORDER BY (data_length + index_length) DESC;
```

### 4. Dosya İzinleri

```bash
# OpenCart klasöründe doğru izinleri ayarlayın
find /path/to/opencart -type f -exec chmod 644 {} \;
find /path/to/opencart -type d -exec chmod 755 {} \;

# Yazılabilir klasörler için özel izinler
chmod -R 777 image/
chmod -R 777 system/storage/
chmod -R 777 system/storage/logs/
chmod -R 777 system/storage/download/
chmod -R 777 system/storage/upload/
chmod -R 777 system/storage/modification/
```

## Entegrasyon Kurulumu

### Yöntem 1: Otomatik Kurulum (Önerilen)

```bash
# 1. Paketi indirin
wget https://releases.meschain.com/trendyol-integration-v1.0.0.zip

# 2. OpenCart dizinine çıkarın
cd /path/to/opencart
unzip trendyol-integration-v1.0.0.zip

# 3. Kurulum scriptini çalıştırın
chmod +x setup.sh
./setup.sh --production

# 4. Bağımlılıkları kurun
composer install --no-dev --optimize-autoloader
```

### Yöntem 2: Manuel Kurulum

```bash
# 1. Dosyaları manuel olarak kopyalayın
cp -r trendyol-integration/admin/* admin/
cp -r trendyol-integration/catalog/* catalog/
cp -r trendyol-integration/system/* system/

# 2. Veritabanı tablolarını oluşturun
mysql -u username -p database_name < install/trendyol_tables.sql

# 3. OCMOD paketini yükleyin
# Admin paneli > Extensions > Installer > Upload: trendyol.ocmod.zip
```

### 3. OCMOD Kurulumu

1. OpenCart admin paneline giriş yapın
2. **Extensions** > **Installer** bölümüne gidin
3. `trendyol-integration.ocmod.zip` dosyasını yükleyin
4. **Extensions** > **Extensions** > **Modules** bölümüne gidin
5. **Trendyol Integration** modülünü bulun ve **Install** butonuna tıklayın

## Yapılandırma

### 1. Temel Yapılandırma

```bash
# Ortam dosyasını oluşturun
cp .env.example .env

# Yapılandırma dosyasını düzenleyin
nano .env
```

### 2. Ortam Değişkenleri

```env
# Trendyol API Yapılandırması
TRENDYOL_API_URL=https://api.trendyol.com
TRENDYOL_SUPPLIER_ID=your_supplier_id
TRENDYOL_API_KEY=your_api_key
TRENDYOL_API_SECRET=your_api_secret

# Veritabanı Yapılandırması
DB_HOST=localhost
DB_NAME=opencart_production
DB_USER=opencart_user
DB_PASS=secure_password
DB_PREFIX=oc_

# OpenCart Yapılandırması
OPENCART_URL=https://your-store.com
OPENCART_ADMIN_PATH=admin

# Senkronizasyon Ayarları
SYNC_BATCH_SIZE=100
SYNC_INTERVAL=300
AUTO_SYNC_ENABLED=true

# Performans Ayarları
API_TIMEOUT=30
MAX_RETRIES=3
RATE_LIMIT=1000

# Güvenlik Ayarları
ENABLE_API_RATE_LIMITING=true
ENABLE_REQUEST_LOGGING=true
LOG_LEVEL=info

# İzleme Ayarları
MONITORING_ENABLED=true
ALERT_EMAIL=admin@your-store.com
SLACK_WEBHOOK_URL=

# Üretim Ayarları
DEBUG_MODE=false
CACHE_ENABLED=true
QUEUE_ENABLED=true
```

### 3. OpenCart Admin Panel Ayarları

1. **Extensions** > **Extensions** > **Modules** > **Trendyol Integration** > **Edit**
2. Aşağıdaki ayarları yapın:

#### API Ayarları
- **Status**: Enabled
- **API URL**: https://api.trendyol.com
- **Supplier ID**: [Trendyol'dan aldığınız ID]
- **API Key**: [Trendyol'dan aldığınız Key]
- **API Secret**: [Trendyol'dan aldığınız Secret]

#### Senkronizasyon Ayarları
- **Auto Sync**: Yes
- **Sync Interval**: 5 minutes
- **Batch Size**: 100
- **Max Retries**: 3

#### Ürün Ayarları
- **Default Category**: [Varsayılan kategori seçin]
- **Stock Status**: In Stock
- **Default Manufacturer**: [Varsayılan marka seçin]
- **Image Quality**: 90%

#### Sipariş Ayarları
- **Order Status Mapping**: [Durum eşleştirmelerini yapın]
- **Auto Accept Orders**: Yes
- **Default Shipping Method**: [Varsayılan kargo yöntemi]

### 4. Kategori Eşleştirme

```bash
# Kategori eşleştirme aracını çalıştırın
php system/cli/category_mapping.php

# Manuel eşleştirme için admin panelini kullanın
# Extensions > Trendyol Integration > Category Mapping
```

## Test ve Doğrulama

### 1. Bağlantı Testi

```bash
# API bağlantısını test edin
php system/cli/test_connection.php

# Beklenen çıktı:
# ✓ Trendyol API bağlantısı başarılı
# ✓ Supplier ID doğrulandı
# ✓ API yetkilendirmesi başarılı
```

### 2. Ürün Senkronizasyon Testi

```bash
# Test ürünü oluşturun
php system/cli/create_test_product.php

# Ürünü Trendyol'a göndermeyi test edin
php system/cli/sync_products.php --test --limit=1

# Sonuçları kontrol edin
tail -f system/storage/logs/trendyol.log
```

### 3. Sipariş Alma Testi

```bash
# Test siparişi oluşturun (Trendyol test ortamında)
# Siparişi OpenCart'a çekmeyi test edin
php system/cli/sync_orders.php --test

# Sonuçları kontrol edin
mysql -u username -p -e "SELECT * FROM oc_order WHERE comment LIKE '%Trendyol%' ORDER BY date_added DESC LIMIT 5;"
```

### 4. Kapsamlı Test Paketi

```bash
# Tüm testleri çalıştırın
./tests/run_tests.php

# Performans testini çalıştırın
./tests/performance/benchmark.sh

# Güvenlik testini çalıştırın
./tests/security/security_scan.sh
```

## Cron Job Ayarları

### 1. Gerekli Cron Job'ları

```bash
# Crontab'ı düzenleyin
crontab -e

# Aşağıdaki satırları ekleyin:

# Ürün senkronizasyonu (her 30 dakikada)
*/30 * * * * /usr/bin/php /path/to/opencart/system/cli/sync_products.php >> /path/to/opencart/system/storage/logs/cron.log 2>&1

# Sipariş senkronizasyonu (her 5 dakikada)
*/5 * * * * /usr/bin/php /path/to/opencart/system/cli/sync_orders.php >> /path/to/opencart/system/storage/logs/cron.log 2>&1

# Stok senkronizasyonu (her 15 dakikada)
*/15 * * * * /usr/bin/php /path/to/opencart/system/cli/sync_stock.php >> /path/to/opencart/system/storage/logs/cron.log 2>&1

# Fiyat senkronizasyonu (her saat)
0 * * * * /usr/bin/php /path/to/opencart/system/cli/sync_prices.php >> /path/to/opencart/system/storage/logs/cron.log 2>&1

# Sistem sağlık kontrolü (her dakika)
* * * * * /path/to/opencart/deployment/health_check.sh >> /path/to/opencart/system/storage/logs/health.log 2>&1

# Günlük raporlar (her gün saat 06:00)
0 6 * * * /usr/bin/php /path/to/opencart/system/cli/generate_reports.php >> /path/to/opencart/system/storage/logs/reports.log 2>&1

# Log temizleme (her hafta Pazar 02:00)
0 2 * * 0 /usr/bin/find /path/to/opencart/system/storage/logs -name "*.log" -mtime +30 -delete
```

### 2. Cron Job İzleme

```bash
# Cron job'ların çalışıp çalışmadığını kontrol edin
tail -f /path/to/opencart/system/storage/logs/cron.log

# Cron job durumunu kontrol eden script
php system/cli/check_cron_status.php
```

## İzleme Sistemi Kurulumu

### 1. İzleme Sistemi Kurulumu

```bash
# İzleme sistemini kurun
./monitoring/setup_monitoring.sh --production

# Gerekli bağımlılıkları kurun
sudo apt-get install nodejs npm
npm install -g pm2

# Dashboard'u başlatın
pm2 start monitoring/dashboard.js --name "trendyol-dashboard"
pm2 startup
pm2 save
```

### 2. Alerting Yapılandırması

```bash
# Alert yapılandırmasını düzenleyin
nano monitoring/config/alerts.json
```

```json
{
  "email": {
    "enabled": true,
    "smtp_host": "smtp.gmail.com",
    "smtp_port": 587,
    "username": "your-email@gmail.com",
    "password": "your-app-password",
    "recipients": ["admin@your-store.com"]
  },
  "slack": {
    "enabled": true,
    "webhook_url": "https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK"
  },
  "thresholds": {
    "api_response_time": 1000,
    "error_rate": 5,
    "sync_failure_rate": 10,
    "disk_usage": 80,
    "memory_usage": 85
  }
}
```

### 3. Dashboard Erişimi

Dashboard'a aşağıdaki URL'den erişebilirsiniz:
- **URL**: `https://your-store.com/monitoring/dashboard`
- **Kullanıcı Adı**: admin
- **Şifre**: [Kurulum sırasında belirlenen şifre]

## Sorun Giderme

### Yaygın Sorunlar ve Çözümleri

#### 1. API Bağlantı Hatası

**Hata**: `Connection refused` veya `Timeout`

**Çözüm**:
```bash
# SSL sertifikasını kontrol edin
curl -I https://api.trendyol.com

# Firewall ayarlarını kontrol edin
sudo ufw status

# DNS çözümlemesini test edin
nslookup api.trendyol.com
```

#### 2. Yetkilendirme Hatası

**Hata**: `401 Unauthorized` veya `403 Forbidden`

**Çözüm**:
```bash
# API bilgilerini doğrulayın
php system/cli/validate_credentials.php

# Trendyol panelinden API durumunu kontrol edin
# API anahtarının aktif olduğundan emin olun
```

#### 3. Ürün Senkronizasyon Hatası

**Hata**: Ürünler Trendyol'a gönderilmiyor

**Çözüm**:
```bash
# Ürün verilerini kontrol edin
php system/cli/validate_products.php

# Kategori eşleştirmelerini kontrol edin
php system/cli/check_category_mapping.php

# Log dosyalarını inceleyin
tail -f system/storage/logs/trendyol.log
```

#### 4. Performans Sorunları

**Hata**: Yavaş senkronizasyon

**Çözüm**:
```bash
# Veritabanı performansını kontrol edin
php system/cli/db_performance_check.php

# Batch boyutunu azaltın
# .env dosyasında SYNC_BATCH_SIZE=50 yapın

# İndeksleri kontrol edin
mysql -u username -p -e "SHOW INDEX FROM oc_product;"
```

### Log Dosyaları

Sorun giderme için aşağıdaki log dosyalarını kontrol edin:

```bash
# Ana uygulama logları
tail -f system/storage/logs/trendyol.log

# Hata logları
tail -f system/storage/logs/error.log

# API logları
tail -f system/storage/logs/api.log

# Senkronizasyon logları
tail -f system/storage/logs/sync.log

# Cron job logları
tail -f system/storage/logs/cron.log
```

### Destek İletişimi

Sorun çözemediğiniz durumda:

1. **Log dosyalarını** toplayın
2. **Hata mesajlarını** kaydedin
3. **Sistem bilgilerini** hazırlayın
4. **Destek ekibiyle** iletişime geçin:
   - E-posta: support@meschain.com
   - Telefon: +90 XXX XXX XXXX
   - Ticket: https://support.meschain.com

---

**Kurulum Tamamlandı!** 🎉

Entegrasyon başarıyla kuruldu ve yapılandırıldı. Artık OpenCart mağazanız Trendyol ile senkronize olmaya hazır.

**Sonraki Adımlar**:
1. İlk ürün senkronizasyonunu başlatın
2. Test siparişi verin ve kontrol edin
3. İzleme dashboard'unu düzenli olarak kontrol edin
4. Performans optimizasyonlarını uygulayın
