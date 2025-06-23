# OpenCart Multi-Port Kurulum Rehberi

Bu rehber, OpenCart sistemlerini 8080 ve 8090 portlarında çalıştırmak için gerekli tüm konfigürasyonları içermektedir.

## 📋 İçindekiler

1. [Sistem Gereksinimleri](#sistem-gereksinimleri)
2. [Hızlı Kurulum](#hızlı-kurulum)
3. [Detaylı Kurulum](#detaylı-kurulum)
4. [Konfigürasyon Dosyaları](#konfigürasyon-dosyaları)
5. [Test ve Doğrulama](#test-ve-doğrulama)
6. [Trendyol Entegrasyonu](#trendyol-entegrasyonu)
7. [Sorun Giderme](#sorun-giderme)

## 🔧 Sistem Gereksinimleri

### Minimum Gereksinimler
- **İşletim Sistemi**: Ubuntu 20.04+ / CentOS 8+ / Debian 11+
- **Web Sunucusu**: Apache 2.4+ veya Nginx 1.18+
- **PHP**: 8.1 veya üzeri
- **Veritabanı**: MySQL 8.0+ veya MariaDB 10.6+
- **RAM**: Minimum 4GB (8GB önerilen)
- **Disk Alanı**: Minimum 10GB boş alan

### Gerekli PHP Modülleri
```bash
php8.1-fpm php8.1-mysql php8.1-curl php8.1-gd php8.1-zip
php8.1-xml php8.1-mbstring php8.1-json php8.1-opcache
```

## ⚡ Hızlı Kurulum

### 1. Otomatik Kurulum Scripti
```bash
# Kurulum scriptini çalıştırın
sudo chmod +x config/scripts/start-servers.sh
sudo ./config/scripts/start-servers.sh
```

### 2. Test Bağlantıları
```bash
# Bağlantı testlerini çalıştırın
sudo chmod +x config/scripts/test-connections.sh
./config/scripts/test-connections.sh
```

### 3. Erişim URL'leri
- **8080 Port (Entegre Sistem)**: http://localhost:8080
- **8090 Port (Temiz Sistem)**: http://localhost:8090
- **Admin Panel 8080**: http://localhost:8080/admin
- **Admin Panel 8090**: http://localhost:8090/admin

## 🔨 Detaylı Kurulum

### Adım 1: Sistem Hazırlığı

#### Gerekli Paketlerin Kurulumu
```bash
# Ubuntu/Debian için
sudo apt update
sudo apt install -y apache2 nginx php8.1-fpm mysql-server
sudo apt install -y php8.1-mysql php8.1-curl php8.1-gd php8.1-zip
sudo apt install -y php8.1-xml php8.1-mbstring php8.1-json php8.1-opcache

# CentOS/RHEL için
sudo dnf install -y httpd nginx php-fpm mysql-server
sudo dnf install -y php-mysql php-curl php-gd php-zip
sudo dnf install -y php-xml php-mbstring php-json php-opcache
```

#### Dizin Yapısının Oluşturulması
```bash
# Web dizinleri
sudo mkdir -p /var/www/html/opencart-8080
sudo mkdir -p /var/www/html/opencart-8090

# Log dizinleri
sudo mkdir -p /var/log/php
sudo mkdir -p /var/lib/php/sessions/opencart-8080
sudo mkdir -p /var/lib/php/sessions/opencart-8090

# İzinlerin ayarlanması
sudo chown -R www-data:www-data /var/www/html/opencart-*
sudo chown -R www-data:www-data /var/lib/php/sessions/opencart-*
```

### Adım 2: Veritabanı Kurulumu

#### MySQL/MariaDB Konfigürasyonu
```bash
# MySQL servisini başlatın
sudo systemctl start mysql
sudo systemctl enable mysql

# Güvenlik ayarlarını yapın
sudo mysql_secure_installation

# Veritabanlarını oluşturun
sudo mysql < config/database/mysql-setup.sql
```

#### Veritabanı Kullanıcıları
- **8080 Port**: `opencart_8080` / `oc8080_secure_password_2025`
- **8090 Port**: `opencart_8090` / `oc8090_secure_password_2025`

### Adım 3: PHP-FPM Konfigürasyonu

#### Pool Dosyalarının Kopyalanması
```bash
# PHP-FPM pool konfigürasyonları
sudo cp config/php/php-fpm-8080.conf /etc/php/8.1/fpm/pool.d/
sudo cp config/php/php-fpm-8090.conf /etc/php/8.1/fpm/pool.d/

# PHP-FPM'i yeniden başlatın
sudo systemctl restart php8.1-fpm
sudo systemctl enable php8.1-fpm
```

#### Pool Ayarları
- **8080 Pool**: `/var/run/php/php8.1-fpm-8080.sock`
- **8090 Pool**: `/var/run/php/php8.1-fpm-8090.sock`

### Adım 4: Web Sunucusu Konfigürasyonu

#### Apache Konfigürasyonu
```bash
# Gerekli modülleri etkinleştirin
sudo a2enmod rewrite headers ssl proxy_fcgi

# Virtual host dosyalarını kopyalayın
sudo cp config/apache/opencart-8080.conf /etc/apache2/sites-available/
sudo cp config/apache/opencart-8090.conf /etc/apache2/sites-available/

# Siteleri etkinleştirin
sudo a2ensite opencart-8080.conf
sudo a2ensite opencart-8090.conf

# Apache'yi yeniden başlatın
sudo systemctl restart apache2
sudo systemctl enable apache2
```

#### Nginx Konfigürasyonu (Alternatif)
```bash
# Konfigürasyon dosyalarını kopyalayın
sudo cp config/nginx/opencart-8080.conf /etc/nginx/sites-available/
sudo cp config/nginx/opencart-8090.conf /etc/nginx/sites-available/

# Siteleri etkinleştirin
sudo ln -sf /etc/nginx/sites-available/opencart-8080.conf /etc/nginx/sites-enabled/
sudo ln -sf /etc/nginx/sites-available/opencart-8090.conf /etc/nginx/sites-enabled/

# Nginx'i yeniden başlatın
sudo systemctl restart nginx
sudo systemctl enable nginx
```

### Adım 5: SSL Sertifikası Kurulumu (Opsiyonel)

```bash
# SSL sertifikalarını oluşturun
sudo chmod +x config/ssl/generate-certificates.sh
sudo ./config/ssl/generate-certificates.sh

# Web sunucusu konfigürasyonunda SSL'i etkinleştirin
# (Konfigürasyon dosyalarındaki SSL satırlarının yorumunu kaldırın)
```

## 📁 Konfigürasyon Dosyaları

### Dosya Yapısı
```
config/
├── apache/
│   ├── opencart-8080.conf      # Apache virtual host (8080)
│   └── opencart-8090.conf      # Apache virtual host (8090)
├── nginx/
│   ├── opencart-8080.conf      # Nginx server block (8080)
│   └── opencart-8090.conf      # Nginx server block (8090)
├── php/
│   ├── php-fpm-8080.conf       # PHP-FPM pool (8080)
│   └── php-fpm-8090.conf       # PHP-FPM pool (8090)
├── database/
│   └── mysql-setup.sql         # Veritabanı kurulum scripti
├── ssl/
│   └── generate-certificates.sh # SSL sertifika oluşturucu
└── scripts/
    ├── start-servers.sh        # Sunucu başlatma scripti
    └── test-connections.sh     # Bağlantı test scripti
```

### Önemli Konfigürasyon Parametreleri

#### Port 8080 (Entegre Sistem)
- **Ortam**: `production`
- **Veritabanı Prefix**: `oc8080_`
- **Trendyol Entegrasyonu**: `enabled`
- **Debug Modu**: `disabled`

#### Port 8090 (Temiz Sistem)
- **Ortam**: `sandbox`
- **Veritabanı Prefix**: `oc8090_`
- **Trendyol Entegrasyonu**: `testing`
- **Debug Modu**: `enabled`

## 🧪 Test ve Doğrulama

### Otomatik Test Scripti
```bash
# Kapsamlı test suite'ini çalıştırın
./config/scripts/test-connections.sh
```

### Manuel Test Adımları

#### 1. Port Bağlantı Testleri
```bash
# Port 8080 testi
curl -I http://localhost:8080

# Port 8090 testi
curl -I http://localhost:8090
```

#### 2. PHP-FPM Socket Testleri
```bash
# Socket dosyalarının varlığını kontrol edin
ls -la /var/run/php/php8.1-fpm-*.sock
```

#### 3. Veritabanı Bağlantı Testleri
```bash
# MySQL bağlantısını test edin
mysql -u opencart_8080 -p -e "SHOW DATABASES;"
mysql -u opencart_8090 -p -e "SHOW DATABASES;"
```

#### 4. Log Dosyası Kontrolü
```bash
# Hata loglarını kontrol edin
sudo tail -f /var/log/apache2/opencart-8080-error.log
sudo tail -f /var/log/apache2/opencart-8090-error.log
sudo tail -f /var/log/php/opencart-8080-error.log
sudo tail -f /var/log/php/opencart-8090-error.log
```

## 🛒 Trendyol Entegrasyonu

### OCMOD Kurulumu

#### 1. OCMOD Dosyasının Hazırlanması
- Dosya: `meschain_trendyol.ocmod.xml`
- Konum: OpenCart admin paneli > Extensions > Installer

#### 2. Kurulum Adımları
1. Admin paneline giriş yapın
2. Extensions > Installer menüsüne gidin
3. `meschain_trendyol.ocmod.xml` dosyasını yükleyin
4. Extensions > Modifications menüsünde etkinleştirin
5. Cache'i temizleyin

#### 3. Konfigürasyon
```php
// Port 8080 (Production)
$config['trendyol_api_key'] = 'YOUR_PRODUCTION_API_KEY';
$config['trendyol_api_secret'] = 'YOUR_PRODUCTION_SECRET';
$config['trendyol_environment'] = 'production';

// Port 8090 (Testing)
$config['trendyol_api_key'] = 'YOUR_SANDBOX_API_KEY';
$config['trendyol_api_secret'] = 'YOUR_SANDBOX_SECRET';
$config['trendyol_environment'] = 'sandbox';
```

### API Test Senaryoları

#### 1. Ürün Senkronizasyonu Testi
```bash
# 8080 port (production)
curl -X POST http://localhost:8080/index.php?route=extension/module/meschain_trendyol/sync_products

# 8090 port (testing)
curl -X POST http://localhost:8090/index.php?route=extension/module/meschain_trendyol/sync_products
```

#### 2. Sipariş Senkronizasyonu Testi
```bash
# 8080 port (production)
curl -X POST http://localhost:8080/index.php?route=extension/module/meschain_trendyol/sync_orders

# 8090 port (testing)
curl -X POST http://localhost:8090/index.php?route=extension/module/meschain_trendyol/sync_orders
```

## 🔧 Sorun Giderme

### Yaygın Sorunlar ve Çözümleri

#### 1. Port Erişim Sorunları
```bash
# Portların kullanımda olup olmadığını kontrol edin
sudo netstat -tlnp | grep :8080
sudo netstat -tlnp | grep :8090

# Firewall ayarlarını kontrol edin
sudo ufw status
sudo ufw allow 8080
sudo ufw allow 8090
```

#### 2. PHP-FPM Socket Sorunları
```bash
# PHP-FPM servis durumunu kontrol edin
sudo systemctl status php8.1-fpm

# Socket izinlerini kontrol edin
ls -la /var/run/php/php8.1-fpm-*.sock

# PHP-FPM'i yeniden başlatın
sudo systemctl restart php8.1-fpm
```

#### 3. Veritabanı Bağlantı Sorunları
```bash
# MySQL servis durumunu kontrol edin
sudo systemctl status mysql

# Kullanıcı izinlerini kontrol edin
mysql -u root -p -e "SELECT User, Host FROM mysql.user WHERE User LIKE 'opencart_%';"

# Bağlantıyı test edin
mysql -u opencart_8080 -p opencart_8080 -e "SHOW TABLES;"
```

#### 4. Web Sunucusu Sorunları
```bash
# Apache durumunu kontrol edin
sudo systemctl status apache2
sudo apache2ctl configtest

# Nginx durumunu kontrol edin
sudo systemctl status nginx
sudo nginx -t

# Hata loglarını inceleyin
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/log/nginx/error.log
```

#### 5. OCMOD Yükleme Sorunları
- **Kırmızı Uyarı**: OCMOD dosyasında syntax hatası var
- **Çözüm**: XML dosyasının geçerli olduğundan emin olun
- **Kontrol**: `meschain_trendyol.ocmod.xml` dosyasını XML validator ile kontrol edin

### Log Dosyası Konumları
```bash
# Web sunucusu logları
/var/log/apache2/opencart-8080-error.log
/var/log/apache2/opencart-8090-error.log
/var/log/nginx/opencart-8080-error.log
/var/log/nginx/opencart-8090-error.log

# PHP logları
/var/log/php/opencart-8080-error.log
/var/log/php/opencart-8090-error.log

# Sistem logları
/var/log/opencart-multiport/
```

### Performans Optimizasyonu

#### 1. PHP-FPM Ayarları
```ini
# Yüksek trafik için
pm.max_children = 100
pm.start_servers = 10
pm.min_spare_servers = 10
pm.max_spare_servers = 50
```

#### 2. MySQL Optimizasyonu
```sql
-- InnoDB ayarları
SET GLOBAL innodb_buffer_pool_size = 1073741824; -- 1GB
SET GLOBAL innodb_log_file_size = 268435456;     -- 256MB
```

#### 3. Cache Ayarları
```bash
# OPcache etkinleştirme
echo "opcache.enable=1" >> /etc/php/8.1/fpm/php.ini
echo "opcache.memory_consumption=256" >> /etc/php/8.1/fpm/php.ini
```

## 📞 Destek ve İletişim

### Teknik Destek
- **E-posta**: support@meschain.com
- **Dokümantasyon**: https://docs.meschain.com
- **GitHub**: https://github.com/meschain/opencart-multiport

### Güncellemeler
- Düzenli olarak güncellemeleri kontrol edin
- Güvenlik yamalarını hemen uygulayın
- Backup'larınızı düzenli olarak alın

---

**Not**: Bu rehber sürekli güncellenmektedir. En son sürüm için dokümantasyon sitesini ziyaret edin.
