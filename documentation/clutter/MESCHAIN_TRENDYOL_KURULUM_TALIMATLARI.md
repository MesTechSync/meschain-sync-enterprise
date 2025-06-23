# MesChain Trendyol OpenCart Kurulum Talimatları

## 📦 Kurulum Paketi İçeriği

Bu paket aşağıdaki dosyaları içermektedir:

### 1. OCMOD Dosyası
- `MESCHAIN_TRENDYOL_FINAL.ocmod.xml` - Ana OCMOD kurulum dosyası

### 2. Yazılım Dosyaları (RESTRUCTURED_UPLOAD klasöründen)
- `admin/` - Admin panel dosyaları
- `catalog/` - Katalog dosyaları
- `system/` - Sistem kütüphaneleri
- `upload/` - Yüklenecek dosyalar

## 🚀 Kurulum Adımları

### Adım 1: Dosyaları Hazırlama

1. **RESTRUCTURED_UPLOAD klasöründeki tüm dosyaları OpenCart ana dizinine kopyalayın:**
```bash
# OpenCart ana dizininize gidin
cd /var/www/html/opencart/

# RESTRUCTURED_UPLOAD içeriğini kopyalayın
cp -r /path/to/RESTRUCTURED_UPLOAD/* ./
```

2. **Dosya izinlerini ayarlayın:**
```bash
# Dosya sahipliğini ayarlayın
sudo chown -R www-data:www-data /var/www/html/opencart/

# Dizin izinlerini ayarlayın
sudo find /var/www/html/opencart/ -type d -exec chmod 755 {} \;

# Dosya izinlerini ayarlayın
sudo find /var/www/html/opencart/ -type f -exec chmod 644 {} \;

# Özel izinler
sudo chmod 777 /var/www/html/opencart/system/storage/
sudo chmod 777 /var/www/html/opencart/system/storage/cache/
sudo chmod 777 /var/www/html/opencart/system/storage/logs/
sudo chmod 777 /var/www/html/opencart/image/
sudo chmod 777 /var/www/html/opencart/image/cache/
```

### Adım 2: Veritabanı Kurulumu

1. **MySQL/MariaDB'ye bağlanın:**
```bash
mysql -u root -p
```

2. **Gerekli tabloları oluşturun:**
```sql
USE your_opencart_database;

-- MesChain Trendyol tabloları
CREATE TABLE IF NOT EXISTS `meschain_trendyol_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `trendyol_product_id` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `price` decimal(15,4) DEFAULT 0.0000,
  `sale_price` decimal(15,4) DEFAULT 0.0000,
  `status` tinyint(1) DEFAULT 1,
  `sync_status` enum('pending','synced','error') DEFAULT 'pending',
  `last_sync` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `trendyol_product_id` (`trendyol_product_id`),
  KEY `sync_status` (`sync_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `meschain_trendyol_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `trendyol_order_id` varchar(255) NOT NULL,
  `shipment_package_id` varchar(255) DEFAULT NULL,
  `order_status` varchar(50) DEFAULT NULL,
  `cargo_tracking_number` varchar(255) DEFAULT NULL,
  `sync_status` enum('pending','synced','error') DEFAULT 'pending',
  `last_sync` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `trendyol_order_id` (`trendyol_order_id`),
  KEY `sync_status` (`sync_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `meschain_trendyol_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan ayarları ekle
INSERT INTO `meschain_trendyol_settings` (`setting_key`, `setting_value`) VALUES
('api_key', ''),
('api_secret', ''),
('supplier_id', ''),
('environment', 'sandbox'),
('auto_sync', '0'),
('sync_interval', '300'),
('debug_mode', '1'),
('last_sync', ''),
('integration_status', 'installed');
```

### Adım 3: OCMOD Kurulumu

1. **OpenCart Admin Paneline giriş yapın**

2. **Extensions > Installer menüsüne gidin**

3. **MESCHAIN_TRENDYOL_FINAL.ocmod.xml dosyasını yükleyin:**
   - "Upload" butonuna tıklayın
   - `MESCHAIN_TRENDYOL_FINAL.ocmod.xml` dosyasını seçin
   - "Continue" butonuna tıklayın

4. **OCMOD'u etkinleştirin:**
   - Extensions > Modifications menüsüne gidin
   - "MesChain Trendyol Integration" modifikasyonunu bulun
   - "Enable" butonuna tıklayın
   - "Refresh" butonuna tıklayın

5. **Cache'i temizleyin:**
   - Dashboard > Developer Settings
   - "Theme" ve "SASS" cache'lerini temizleyin

### Adım 4: Modül Aktivasyonu

1. **Extensions > Extensions menüsüne gidin**

2. **Extension Type olarak "Modules" seçin**

3. **"MesChain Trendyol" modülünü bulun ve "Install" butonuna tıklayın**

4. **"Edit" butonuna tıklayarak konfigürasyonu açın**

## ⚙️ Konfigürasyon

### Temel Ayarlar

1. **API Bilgileri:**
   - **API Key**: Trendyol'dan aldığınız API anahtarı
   - **API Secret**: Trendyol'dan aldığınız gizli anahtar
   - **Supplier ID**: Trendyol tedarikçi ID'niz

2. **Ortam Ayarları:**
   - **Environment**: `sandbox` (test) veya `production` (canlı)
   - **Debug Mode**: Geliştirme aşamasında `enabled`

3. **Senkronizasyon Ayarları:**
   - **Auto Sync**: Otomatik senkronizasyon
   - **Sync Interval**: Senkronizasyon aralığı (saniye)

### Cron Job Kurulumu

```bash
# Crontab'ı düzenleyin
crontab -e

# Aşağıdaki satırları ekleyin
# Her 5 dakikada bir ürün senkronizasyonu
*/5 * * * * /usr/bin/php /var/www/html/opencart/admin/cli/meschain_trendyol_sync.php

# Her 10 dakikada bir sipariş senkronizasyonu
*/10 * * * * /usr/bin/php /var/www/html/opencart/admin/cli/meschain_trendyol_orders.php

# Günlük log temizliği
0 2 * * * /usr/bin/php /var/www/html/opencart/admin/cli/meschain_trendyol_cleanup.php
```

## 🧪 Test Etme

### 1. Kurulum Testi
```bash
# OpenCart ana dizininizde
php admin/cli/meschain_trendyol_test.php
```

### 2. API Bağlantı Testi
1. Admin panelde MesChain Trendyol modülüne gidin
2. "Test Connection" butonuna tıklayın
3. Başarılı bağlantı mesajını kontrol edin

### 3. Ürün Senkronizasyon Testi
1. Catalog > Products menüsünde bir ürün düzenleyin
2. "Trendyol" sekmesine gidin
3. Trendyol ayarlarını yapın
4. Ürünü kaydedin
5. MesChain Trendyol panelinde senkronizasyon durumunu kontrol edin

## 🔧 Sorun Giderme

### Yaygın Sorunlar

#### 1. OCMOD Yükleme Hatası
**Sorun**: "XML parsing error" veya "File already exists"
**Çözüm**:
```bash
# Eski modifikasyonları temizle
rm -rf /var/www/html/opencart/system/storage/modification/*

# Cache'i temizle
rm -rf /var/www/html/opencart/system/storage/cache/*

# OCMOD'u tekrar yükle
```

#### 2. Dosya İzin Sorunları
**Sorun**: "Permission denied" hataları
**Çözüm**:
```bash
# İzinleri düzelt
sudo chown -R www-data:www-data /var/www/html/opencart/
sudo chmod -R 755 /var/www/html/opencart/
sudo chmod -R 777 /var/www/html/opencart/system/storage/
```

#### 3. API Bağlantı Sorunları
**Sorun**: Trendyol API'ye bağlanılamıyor
**Çözüm**:
- API bilgilerini kontrol edin
- Firewall ayarlarını kontrol edin
- SSL sertifikalarını kontrol edin

### Log Dosyaları
```bash
# Hata logları
tail -f /var/www/html/opencart/system/storage/logs/error.log

# MesChain Trendyol logları
tail -f /var/www/html/opencart/system/storage/logs/meschain_trendyol.log
```

## 📞 Destek

### Teknik Destek
- **E-posta**: support@meschain.com
- **Dokümantasyon**: https://docs.meschain.com
- **GitHub**: https://github.com/meschain/trendyol-opencart

### Kurulum Sonrası Kontrol Listesi
- [ ] OCMOD başarıyla yüklendi
- [ ] Modül etkinleştirildi
- [ ] API bağlantısı test edildi
- [ ] Veritabanı tabloları oluşturuldu
- [ ] Cron job'lar kuruldu
- [ ] İlk ürün senkronizasyonu yapıldı
- [ ] Log dosyaları kontrol edildi

---

**Not**: Kurulum sırasında herhangi bir sorunla karşılaştığınızda, lütfen log dosyalarını kontrol edin ve gerekirse teknik destek ekibimizle iletişime geçin.
