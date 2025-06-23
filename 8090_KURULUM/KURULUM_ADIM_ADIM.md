# 8090 Port OpenCart + MesChain Trendyol OCMOD Kurulum Rehberi

## 📋 İçindekiler

1. [Kurulum Öncesi Hazırlık](#kurulum-öncesi-hazırlık)
2. [Sistem Gereksinimleri](#sistem-gereksinimleri)
3. [Adım Adım Kurulum](#adım-adım-kurulum)
4. [Test ve Doğrulama](#test-ve-doğrulama)
5. [Troubleshooting](#troubleshooting)
6. [Güvenlik Ayarları](#güvenlik-ayarları)
7. [Performans Optimizasyonu](#performans-optimizasyonu)

---

## 🚀 Kurulum Öncesi Hazırlık

### Gerekli Bilgiler
- **Port**: 8090
- **Veritabanı**: `meschain_test_8090`
- **Kullanıcı**: `opencart_8090`
- **Şifre**: `oc8090_secure_password_2025`
- **Site URL**: `http://localhost:8090`
- **Admin URL**: `http://localhost:8090/admin`

### Kurulum Dosyaları
```
8090_KURULUM/
├── install_opencart_8090.sh      # OpenCart kurulum scripti
├── install_meschain_ocmod.sh     # MesChain OCMOD kurulum scripti
├── test_installation.sh          # Test ve doğrulama scripti
├── database_setup.sql            # Veritabanı kurulum scripti
└── KURULUM_ADIM_ADIM.md          # Bu rehber
```

---

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

### Port Gereksinimleri
- **8090**: OpenCart ana port
- **3306**: MySQL veritabanı portu
- **22**: SSH erişimi (opsiyonel)

---

## 📝 Adım Adım Kurulum

### ADIM 1: Sistem Hazırlığı

#### 1.1 Gerekli Paketlerin Kurulumu
```bash
# Ubuntu/Debian için
sudo apt update
sudo apt install -y apache2 nginx php8.1-fpm mysql-server
sudo apt install -y php8.1-mysql php8.1-curl php8.1-gd php8.1-zip
sudo apt install -y php8.1-xml php8.1-mbstring php8.1-json php8.1-opcache
sudo apt install -y curl wget unzip xmllint bc

# CentOS/RHEL için
sudo dnf install -y httpd nginx php-fpm mysql-server
sudo dnf install -y php-mysql php-curl php-gd php-zip
sudo dnf install -y php-xml php-mbstring php-json php-opcache
sudo dnf install -y curl wget unzip libxml2 bc
```

#### 1.2 Servisleri Başlatma
```bash
# MySQL
sudo systemctl start mysql
sudo systemctl enable mysql

# PHP-FPM
sudo systemctl start php8.1-fpm
sudo systemctl enable php8.1-fpm

# Apache (veya Nginx)
sudo systemctl start apache2
sudo systemctl enable apache2
```

#### 1.3 MySQL Güvenlik Ayarları
```bash
sudo mysql_secure_installation
```

### ADIM 2: Veritabanı Kurulumu

#### 2.1 Manuel Veritabanı Kurulumu
```bash
# MySQL'e root olarak giriş
mysql -u root -p

# Veritabanı ve kullanıcı oluştur
CREATE DATABASE meschain_test_8090 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'opencart_8090'@'localhost' IDENTIFIED BY 'oc8090_secure_password_2025';
GRANT ALL PRIVILEGES ON meschain_test_8090.* TO 'opencart_8090'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 2.2 Otomatik Veritabanı Kurulumu
```bash
# SQL scriptini çalıştır
mysql -u root -p < database_setup.sql
```

#### 2.3 Veritabanı Bağlantısını Test Et
```bash
mysql -u opencart_8090 -p meschain_test_8090 -e "SHOW TABLES;"
```

### ADIM 3: OpenCart Kurulumu

#### 3.1 Kurulum Scriptini Çalıştır
```bash
# Script izinlerini ayarla
chmod +x install_opencart_8090.sh

# Kurulumu başlat
./install_opencart_8090.sh
```

#### 3.2 Manuel Kurulum (Alternatif)
```bash
# OpenCart'ı indir
cd /tmp
wget https://github.com/opencart/opencart/releases/download/4.0.2.3/opencart-4.0.2.3.zip
unzip opencart-4.0.2.3.zip

# Dosyaları kopyala
sudo mkdir -p /var/www/html/opencart-8090
sudo cp -r upload/* /var/www/html/opencart-8090/
sudo chown -R www-data:www-data /var/www/html/opencart-8090
sudo chmod -R 755 /var/www/html/opencart-8090

# Konfigürasyon dosyalarını hazırla
cd /var/www/html/opencart-8090
sudo cp config-dist.php config.php
sudo cp admin/config-dist.php admin/config.php
sudo chmod 666 config.php admin/config.php
```

#### 3.3 CLI Kurulumu
```bash
cd /var/www/html/opencart-8090
sudo -u www-data php install/cli_install.php install \
    --db_hostname localhost \
    --db_username opencart_8090 \
    --db_password oc8090_secure_password_2025 \
    --db_database meschain_test_8090 \
    --db_driver mysqli \
    --db_port 3306 \
    --username admin \
    --password admin123!@# \
    --email admin@meschain.local \
    --http_server http://localhost:8090/
```

### ADIM 4: Web Sunucusu Konfigürasyonu

#### 4.1 Apache Virtual Host
```bash
# Virtual host dosyası oluştur
sudo tee /etc/apache2/sites-available/opencart-8090.conf > /dev/null << 'EOF'
<VirtualHost *:8090>
    ServerName localhost
    DocumentRoot /var/www/html/opencart-8090

    <Directory /var/www/html/opencart-8090>
        AllowOverride All
        Require all granted
    </Directory>

    # PHP-FPM
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.1-fpm-8090.sock|fcgi://localhost"
    </FilesMatch>

    # Logging
    ErrorLog /var/log/apache2/opencart-8090-error.log
    CustomLog /var/log/apache2/opencart-8090-access.log combined
</VirtualHost>
EOF

# Port dinleme ayarı
echo "Listen 8090" | sudo tee -a /etc/apache2/ports.conf

# Siteyi etkinleştir
sudo a2ensite opencart-8090.conf
sudo a2enmod rewrite proxy_fcgi
sudo systemctl restart apache2
```

#### 4.2 Nginx Server Block (Alternatif)
```bash
# Server block dosyası oluştur
sudo tee /etc/nginx/sites-available/opencart-8090.conf > /dev/null << 'EOF'
server {
    listen 8090;
    server_name localhost;
    root /var/www/html/opencart-8090;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm-8090.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Logging
    error_log /var/log/nginx/opencart-8090-error.log;
    access_log /var/log/nginx/opencart-8090-access.log;
}
EOF

# Siteyi etkinleştir
sudo ln -sf /etc/nginx/sites-available/opencart-8090.conf /etc/nginx/sites-enabled/
sudo systemctl restart nginx
```

### ADIM 5: MesChain Trendyol OCMOD Kurulumu

#### 5.1 OCMOD Kurulum Scriptini Çalıştır
```bash
# Script izinlerini ayarla
chmod +x install_meschain_ocmod.sh

# OCMOD kurulumunu başlat
./install_meschain_ocmod.sh
```

#### 5.2 Manuel OCMOD Kurulumu (Alternatif)
```bash
# Veritabanı tablolarını oluştur
mysql -u opencart_8090 -p meschain_test_8090 < database_setup.sql

# Admin controller dosyasını oluştur
sudo mkdir -p /var/www/html/opencart-8090/admin/controller/extension/module
# (Dosya içeriği install_meschain_ocmod.sh scriptinde mevcuttur)

# Extension kaydını ekle
mysql -u opencart_8090 -p meschain_test_8090 -e "
INSERT IGNORE INTO oc_extension (type, code) VALUES ('module', 'meschain_trendyol');
"
```

### ADIM 6: Güvenlik Ayarları

#### 6.1 Install Klasörünü Sil
```bash
sudo rm -rf /var/www/html/opencart-8090/install
```

#### 6.2 Dosya İzinlerini Güvenli Hale Getir
```bash
cd /var/www/html/opencart-8090
sudo chmod 644 config.php admin/config.php
sudo chmod -R 644 system/storage/logs/
sudo chmod -R 755 system/storage/cache/
```

#### 6.3 Firewall Ayarları
```bash
# UFW kullanıyorsanız
sudo ufw allow 8090/tcp
sudo ufw reload

# iptables kullanıyorsanız
sudo iptables -A INPUT -p tcp --dport 8090 -j ACCEPT
sudo iptables-save > /etc/iptables/rules.v4
```

---

## 🧪 Test ve Doğrulama

### ADIM 7: Otomatik Test

#### 7.1 Test Scriptini Çalıştır
```bash
# Test scriptini çalıştır
chmod +x test_installation.sh
./test_installation.sh
```

#### 7.2 Test Sonuçlarını İncele
```bash
# Test raporunu görüntüle
cat /var/log/opencart-8090/test-report.log
```

### ADIM 8: Manuel Test

#### 8.1 Web Erişim Testi
```bash
# Ana sayfa testi
curl -I http://localhost:8090

# Admin paneli testi
curl -I http://localhost:8090/admin
```

#### 8.2 Veritabanı Testi
```bash
# Tablo kontrolü
mysql -u opencart_8090 -p meschain_test_8090 -e "SHOW TABLES LIKE '%meschain%';"

# Ayar kontrolü
mysql -u opencart_8090 -p meschain_test_8090 -e "SELECT COUNT(*) FROM oc_meschain_trendyol_settings;"
```

#### 8.3 PHP-FPM Testi
```bash
# Socket kontrolü
ls -la /var/run/php/php8.1-fpm-8090.sock

# Process kontrolü
ps aux | grep php8.1-fpm-8090
```

---

## 🔧 Troubleshooting

### Yaygın Sorunlar ve Çözümleri

#### Sorun 1: Port 8090 Erişilemiyor
```bash
# Port dinleme kontrolü
sudo netstat -tlnp | grep :8090

# Firewall kontrolü
sudo ufw status
sudo iptables -L | grep 8090

# Çözüm
sudo ufw allow 8090
sudo systemctl restart apache2  # veya nginx
```

#### Sorun 2: PHP-FPM Socket Hatası
```bash
# Socket varlığı kontrolü
ls -la /var/run/php/php8.1-fpm-8090.sock

# PHP-FPM durumu
sudo systemctl status php8.1-fpm

# Çözüm
sudo systemctl restart php8.1-fpm
sudo chown www-data:www-data /var/run/php/php8.1-fpm-8090.sock
```

#### Sorun 3: Veritabanı Bağlantı Hatası
```bash
# Bağlantı testi
mysql -u opencart_8090 -p meschain_test_8090 -e "SELECT 1;"

# Kullanıcı izinleri kontrolü
mysql -u root -p -e "SHOW GRANTS FOR 'opencart_8090'@'localhost';"

# Çözüm
mysql -u root -p -e "
GRANT ALL PRIVILEGES ON meschain_test_8090.* TO 'opencart_8090'@'localhost';
FLUSH PRIVILEGES;
"
```

#### Sorun 4: Dosya İzin Hataları
```bash
# İzin kontrolü
ls -la /var/www/html/opencart-8090/

# Çözüm
sudo chown -R www-data:www-data /var/www/html/opencart-8090
sudo chmod -R 755 /var/www/html/opencart-8090
sudo chmod -R 777 /var/www/html/opencart-8090/system/storage
```

#### Sorun 5: OCMOD Yükleme Hatası
```bash
# Extension tablosu kontrolü
mysql -u opencart_8090 -p meschain_test_8090 -e "SELECT * FROM oc_extension WHERE code='meschain_trendyol';"

# Çözüm
mysql -u opencart_8090 -p meschain_test_8090 -e "
INSERT IGNORE INTO oc_extension (type, code) VALUES ('module', 'meschain_trendyol');
"

# Cache temizleme
sudo rm -rf /var/www/html/opencart-8090/system/storage/cache/*
sudo rm -rf /var/www/html/opencart-8090/system/storage/modification/*
```

---

## 🔒 Güvenlik Ayarları

### Temel Güvenlik Önlemleri

#### 1. Admin Şifresini Değiştir
```bash
# Admin paneline giriş yap: http://localhost:8090/admin
# System > Users > Users menüsünden şifreyi değiştir
```

#### 2. Admin Klasör Adını Değiştir
```bash
cd /var/www/html/opencart-8090
sudo mv admin admin_$(date +%s)
# Konfigürasyon dosyalarını güncelle
```

#### 3. SSL Sertifikası Kur
```bash
# Self-signed sertifika oluştur
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/opencart-8090.key \
    -out /etc/ssl/certs/opencart-8090.crt

# Apache SSL konfigürasyonu
sudo a2enmod ssl
# Virtual host'u SSL için güncelle
```

#### 4. Fail2Ban Kur
```bash
sudo apt install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

---

## ⚡ Performans Optimizasyonu

### PHP-FPM Optimizasyonu
```bash
# /etc/php/8.1/fpm/pool.d/opencart-8090.conf
sudo tee -a /etc/php/8.1/fpm/pool.d/opencart-8090.conf << 'EOF'
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 10
pm.max_spare_servers = 30
pm.max_requests = 500
EOF
```

### MySQL Optimizasyonu
```bash
# /etc/mysql/mysql.conf.d/mysqld.cnf
sudo tee -a /etc/mysql/mysql.conf.d/mysqld.cnf << 'EOF'
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
query_cache_size = 64M
query_cache_type = 1
EOF
```

### OPcache Optimizasyonu
```bash
# /etc/php/8.1/fpm/conf.d/10-opcache.ini
sudo tee /etc/php/8.1/fpm/conf.d/10-opcache.ini << 'EOF'
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.save_comments=1
EOF
```

---

## 📊 Kurulum Sonrası Kontrol Listesi

### ✅ Temel Kontroller
- [ ] OpenCart ana sayfası erişilebilir (http://localhost:8090)
- [ ] Admin paneli erişilebilir (http://localhost:8090/admin)
- [ ] Veritabanı bağlantısı çalışıyor
- [ ] PHP-FPM socket aktif
- [ ] MesChain Trendyol tabloları oluşturuldu

### ✅ Güvenlik Kontrolleri
- [ ] Install klasörü silindi
- [ ] Admin şifresi değiştirildi
- [ ] Dosya izinleri güvenli
- [ ] Firewall kuralları aktif
- [ ] SSL sertifikası kuruldu (opsiyonel)

### ✅ Performans Kontrolleri
- [ ] PHP-FPM pool optimizasyonu yapıldı
- [ ] MySQL ayarları optimize edildi
- [ ] OPcache etkinleştirildi
- [ ] Log rotasyonu ayarlandı

### ✅ MesChain Trendyol Kontrolleri
- [ ] Extension tablosuna kayıt eklendi
- [ ] Ayarlar tablosu dolduruldu
- [ ] Admin menüsünde MesChain Trendyol görünüyor
- [ ] API ayarları sayfası erişilebilir

---

## 📞 Destek ve İletişim

### Teknik Destek
- **E-posta**: support@meschain.com
- **Dokümantasyon**: https://docs.meschain.com
- **GitHub**: https://github.com/meschain/opencart-trendyol

### Log Dosyaları
- **Kurulum Logları**: `/var/log/opencart-8090/`
- **Apache Logları**: `/var/log/apache2/opencart-8090-*.log`
- **Nginx Logları**: `/var/log/nginx/opencart-8090-*.log`
- **PHP Logları**: `/var/log/php/opencart-8090-error.log`
- **OpenCart Logları**: `/var/www/html/opencart-8090/system/storage/logs/`

### Yedekleme
```bash
# Dosya yedeği
sudo tar -czf /var/backups/opencart-8090-$(date +%Y%m%d).tar.gz /var/www/html/opencart-8090

# Veritabanı yedeği
mysqldump -u opencart_8090 -p meschain_test_8090 | gzip > /var/backups/meschain_test_8090-$(date +%Y%m%d).sql.gz
```

---

## 🎉 Kurulum Tamamlandı!

8090 portunda temiz OpenCart kurulumu ve MesChain Trendyol OCMOD entegrasyonu başarıyla tamamlandı.

**Sonraki adımlar:**
1. Admin paneline giriş yapın
2. MesChain Trendyol modülünü etkinleştirin
3. Trendyol API bilgilerini girin
4. Test ürünleri ile senkronizasyon testleri yapın
5. Production ortamına geçiş için hazırlıkları tamamlayın

**Önemli:** Production ortamına geçmeden önce tüm testleri tamamlayın ve güvenlik ayarlarını gözden geçirin!
