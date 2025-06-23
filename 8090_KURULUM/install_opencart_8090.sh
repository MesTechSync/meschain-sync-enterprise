#!/bin/bash

# ============================================================================
# OpenCart 8090 Port Temiz Kurulum Scripti
# MesChain Sync Enterprise - 8090 Port Özel Kurulum
# ============================================================================

set -e  # Hata durumunda scripti durdur

# Renkli çıktı için
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Log fonksiyonu
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}"
    exit 1
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

# Konfigürasyon değişkenleri
OPENCART_VERSION="4.0.2.3"
OPENCART_URL="https://github.com/opencart/opencart/releases/download/${OPENCART_VERSION}/opencart-${OPENCART_VERSION}.zip"
INSTALL_DIR="/var/www/html/opencart-8090"
DB_NAME="meschain_test_8090"
DB_USER="opencart_8090"
DB_PASS="oc8090_secure_password_2025"
ADMIN_USER="admin"
ADMIN_PASS="admin123!@#"
ADMIN_EMAIL="admin@meschain.local"
SITE_URL="http://localhost:8090"

log "8090 Port OpenCart Temiz Kurulum Başlatılıyor..."

# Root kontrolü
if [[ $EUID -eq 0 ]]; then
   error "Bu script root kullanıcısı ile çalıştırılmamalıdır!"
fi

# Sistem gereksinimlerini kontrol et
log "Sistem gereksinimleri kontrol ediliyor..."

# PHP kontrolü
if ! command -v php &> /dev/null; then
    error "PHP kurulu değil! Lütfen PHP 8.1+ kurun."
fi

PHP_VERSION=$(php -r "echo PHP_VERSION;" | cut -d. -f1,2)
if (( $(echo "$PHP_VERSION < 8.1" | bc -l) )); then
    error "PHP 8.1 veya üzeri gerekli! Mevcut sürüm: $PHP_VERSION"
fi

# MySQL kontrolü
if ! command -v mysql &> /dev/null; then
    error "MySQL kurulu değil! Lütfen MySQL 8.0+ kurun."
fi

# Web sunucusu kontrolü
if ! systemctl is-active --quiet apache2 && ! systemctl is-active --quiet nginx; then
    error "Apache2 veya Nginx çalışmıyor! Lütfen web sunucusunu başlatın."
fi

log "Sistem gereksinimleri karşılanıyor ✓"

# Gerekli dizinleri oluştur
log "Dizin yapısı oluşturuluyor..."

sudo mkdir -p "$INSTALL_DIR"
sudo mkdir -p "/var/log/opencart-8090"
sudo mkdir -p "/var/lib/php/sessions/opencart-8090"
sudo mkdir -p "/tmp/opencart-8090-install"

# İzinleri ayarla
sudo chown -R www-data:www-data "$INSTALL_DIR"
sudo chown -R www-data:www-data "/var/lib/php/sessions/opencart-8090"
sudo chmod -R 755 "$INSTALL_DIR"

log "Dizin yapısı oluşturuldu ✓"

# OpenCart'ı indir
log "OpenCart $OPENCART_VERSION indiriliyor..."

cd /tmp/opencart-8090-install
if [ ! -f "opencart-${OPENCART_VERSION}.zip" ]; then
    wget -q --show-progress "$OPENCART_URL" || error "OpenCart indirilemedi!"
fi

# Arşivi çıkart
log "OpenCart arşivi çıkartılıyor..."
unzip -q "opencart-${OPENCART_VERSION}.zip" || error "Arşiv çıkartılamadı!"

# Upload klasörünü kopyala
log "OpenCart dosyaları kopyalanıyor..."
sudo cp -r upload/* "$INSTALL_DIR/" || error "Dosyalar kopyalanamadı!"

# Gerekli dosyaları yeniden adlandır
cd "$INSTALL_DIR"
sudo cp config-dist.php config.php
sudo cp admin/config-dist.php admin/config.php

# İzinleri düzelt
sudo chown -R www-data:www-data "$INSTALL_DIR"
sudo chmod -R 755 "$INSTALL_DIR"
sudo chmod -R 777 "$INSTALL_DIR/system/storage"
sudo chmod -R 777 "$INSTALL_DIR/image"
sudo chmod 666 "$INSTALL_DIR/config.php"
sudo chmod 666 "$INSTALL_DIR/admin/config.php"

log "OpenCart dosyaları hazırlandı ✓"

# Veritabanını oluştur
log "Veritabanı oluşturuluyor..."

# MySQL root şifresi iste
read -s -p "MySQL root şifresini girin: " MYSQL_ROOT_PASS
echo

# Veritabanı ve kullanıcı oluştur
mysql -u root -p"$MYSQL_ROOT_PASS" << EOF
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

if [ $? -eq 0 ]; then
    log "Veritabanı oluşturuldu ✓"
else
    error "Veritabanı oluşturulamadı!"
fi

# OpenCart CLI kurulumu
log "OpenCart CLI kurulumu başlatılıyor..."

cd "$INSTALL_DIR"

# CLI kurulum komutu
sudo -u www-data php install/cli_install.php install \
    --db_hostname localhost \
    --db_username "$DB_USER" \
    --db_password "$DB_PASS" \
    --db_database "$DB_NAME" \
    --db_driver mysqli \
    --db_port 3306 \
    --username "$ADMIN_USER" \
    --password "$ADMIN_PASS" \
    --email "$ADMIN_EMAIL" \
    --http_server "$SITE_URL/"

if [ $? -eq 0 ]; then
    log "OpenCart CLI kurulumu tamamlandı ✓"
else
    error "OpenCart CLI kurulumu başarısız!"
fi

# Install klasörünü sil
log "Güvenlik için install klasörü siliniyor..."
sudo rm -rf "$INSTALL_DIR/install"

# Konfigürasyon dosyalarını güncelle
log "Konfigürasyon dosyaları güncelleniyor..."

# Ana config.php
sudo tee "$INSTALL_DIR/config.php" > /dev/null << EOF
<?php
// HTTP
define('HTTP_SERVER', '$SITE_URL/');

// HTTPS
define('HTTPS_SERVER', '$SITE_URL/');

// DIR
define('DIR_APPLICATION', '$INSTALL_DIR/catalog/');
define('DIR_SYSTEM', '$INSTALL_DIR/system/');
define('DIR_IMAGE', '$INSTALL_DIR/image/');
define('DIR_STORAGE', '$INSTALL_DIR/system/storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', '$DB_USER');
define('DB_PASSWORD', '$DB_PASS');
define('DB_DATABASE', '$DB_NAME');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

// MesChain Özel Ayarlar
define('MESCHAIN_PORT', '8090');
define('MESCHAIN_ENV', 'testing');
define('MESCHAIN_DEBUG', true);
EOF

# Admin config.php
sudo tee "$INSTALL_DIR/admin/config.php" > /dev/null << EOF
<?php
// HTTP
define('HTTP_SERVER', '$SITE_URL/admin/');
define('HTTP_CATALOG', '$SITE_URL/');

// HTTPS
define('HTTPS_SERVER', '$SITE_URL/admin/');
define('HTTPS_CATALOG', '$SITE_URL/');

// DIR
define('DIR_APPLICATION', '$INSTALL_DIR/admin/');
define('DIR_SYSTEM', '$INSTALL_DIR/system/');
define('DIR_IMAGE', '$INSTALL_DIR/image/');
define('DIR_STORAGE', '$INSTALL_DIR/system/storage/');
define('DIR_CATALOG', '$INSTALL_DIR/catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', '$DB_USER');
define('DB_PASSWORD', '$DB_PASS');
define('DB_DATABASE', '$DB_NAME');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

// MesChain Özel Ayarlar
define('MESCHAIN_PORT', '8090');
define('MESCHAIN_ENV', 'testing');
define('MESCHAIN_DEBUG', true);
EOF

# İzinleri düzelt
sudo chown www-data:www-data "$INSTALL_DIR/config.php"
sudo chown www-data:www-data "$INSTALL_DIR/admin/config.php"
sudo chmod 644 "$INSTALL_DIR/config.php"
sudo chmod 644 "$INSTALL_DIR/admin/config.php"

log "Konfigürasyon dosyaları güncellendi ✓"

# PHP-FPM pool konfigürasyonu
log "PHP-FPM pool konfigürasyonu oluşturuluyor..."

sudo tee "/etc/php/8.1/fpm/pool.d/opencart-8090.conf" > /dev/null << EOF
[opencart-8090]
user = www-data
group = www-data
listen = /var/run/php/php8.1-fpm-8090.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

php_admin_value[session.save_path] = /var/lib/php/sessions/opencart-8090
php_admin_value[error_log] = /var/log/opencart-8090/php-error.log
php_admin_flag[log_errors] = on
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
php_admin_value[upload_max_filesize] = 32M
php_admin_value[post_max_size] = 32M

env[MESCHAIN_PORT] = 8090
env[MESCHAIN_ENV] = testing
EOF

# PHP-FPM'i yeniden başlat
sudo systemctl restart php8.1-fpm

log "PHP-FPM pool konfigürasyonu tamamlandı ✓"

# Apache/Nginx konfigürasyonu kontrol et
if systemctl is-active --quiet apache2; then
    log "Apache konfigürasyonu kontrol ediliyor..."

    if [ ! -f "/etc/apache2/sites-available/opencart-8090.conf" ]; then
        warning "Apache virtual host bulunamadı. Manuel olarak oluşturmanız gerekiyor."
        info "Örnek konfigürasyon: config/apache/opencart-8090.conf"
    fi

elif systemctl is-active --quiet nginx; then
    log "Nginx konfigürasyonu kontrol ediliyor..."

    if [ ! -f "/etc/nginx/sites-available/opencart-8090.conf" ]; then
        warning "Nginx server block bulunamadı. Manuel olarak oluşturmanız gerekiyor."
        info "Örnek konfigürasyon: config/nginx/opencart-8090.conf"
    fi
fi

# Temizlik
log "Geçici dosyalar temizleniyor..."
rm -rf /tmp/opencart-8090-install

# Kurulum tamamlandı
log "============================================================================"
log "8090 PORT OPENCART KURULUMU BAŞARIYLA TAMAMLANDI!"
log "============================================================================"
echo
info "Erişim Bilgileri:"
echo "  • Site URL: $SITE_URL"
echo "  • Admin URL: $SITE_URL/admin"
echo "  • Admin Kullanıcı: $ADMIN_USER"
echo "  • Admin Şifre: $ADMIN_PASS"
echo "  • Veritabanı: $DB_NAME"
echo
info "Sonraki Adımlar:"
echo "  1. Web sunucusu konfigürasyonunu kontrol edin"
echo "  2. MesChain Trendyol OCMOD'u kurun: ./install_meschain_ocmod.sh"
echo "  3. Kurulumu test edin: ./test_installation.sh"
echo
warning "Güvenlik Uyarısı:"
echo "  • Admin şifresini değiştirin!"
echo "  • Firewall ayarlarını kontrol edin!"
echo "  • SSL sertifikası kurun!"
echo
log "Kurulum raporu: /var/log/opencart-8090/install.log dosyasına kaydedildi."

# Kurulum raporunu oluştur
sudo mkdir -p /var/log/opencart-8090
sudo tee "/var/log/opencart-8090/install.log" > /dev/null << EOF
OpenCart 8090 Port Kurulum Raporu
=================================
Kurulum Tarihi: $(date)
OpenCart Sürümü: $OPENCART_VERSION
Kurulum Dizini: $INSTALL_DIR
Veritabanı: $DB_NAME
Site URL: $SITE_URL
Admin URL: $SITE_URL/admin
Admin Kullanıcı: $ADMIN_USER
PHP-FPM Pool: /var/run/php/php8.1-fpm-8090.sock
Log Dizini: /var/log/opencart-8090/
Session Dizini: /var/lib/php/sessions/opencart-8090/

Kurulum Durumu: BAŞARILI
MesChain Entegrasyon: HAZIR
Sonraki Adım: OCMOD Kurulumu
EOF

log "Kurulum scripti tamamlandı!"
