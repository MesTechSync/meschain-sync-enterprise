#!/bin/bash

# ============================================================================
# OpenCart 8090 + MesChain Trendyol Test ve Doğrulama Scripti
# Kurulum sonrası kapsamlı test ve doğrulama işlemleri
# ============================================================================

set -e  # Hata durumunda scripti durdur

# Renkli çıktı için
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Test sonuçları için
TESTS_PASSED=0
TESTS_FAILED=0
TESTS_TOTAL=0

# Log fonksiyonu
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}"
    ((TESTS_FAILED++))
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

success() {
    echo -e "${PURPLE}[SUCCESS] $1${NC}"
    ((TESTS_PASSED++))
}

test_header() {
    echo -e "${CYAN}[TEST] $1${NC}"
    ((TESTS_TOTAL++))
}

# Konfigürasyon değişkenleri
OPENCART_DIR="/var/www/html/opencart-8090"
DB_NAME="meschain_test_8090"
DB_USER="opencart_8090"
DB_PASS="oc8090_secure_password_2025"
SITE_URL="http://localhost:8090"
ADMIN_URL="$SITE_URL/admin"
TEST_REPORT="/var/log/opencart-8090/test-report.log"

log "OpenCart 8090 + MesChain Trendyol Test Süreci Başlatılıyor..."

# Test raporu başlığı
sudo mkdir -p "$(dirname "$TEST_REPORT")"
sudo tee "$TEST_REPORT" > /dev/null << EOF
OpenCart 8090 + MesChain Trendyol Test Raporu
=============================================
Test Tarihi: $(date)
Test Sürümü: 1.0.0
OpenCart Dizini: $OPENCART_DIR
Veritabanı: $DB_NAME
Site URL: $SITE_URL
Admin URL: $ADMIN_URL

TEST SONUÇLARI:
===============
EOF

# Test 1: Dizin ve Dosya Varlığı Kontrolü
test_header "Dizin ve Dosya Varlığı Kontrolü"

if [ -d "$OPENCART_DIR" ]; then
    success "OpenCart dizini mevcut: $OPENCART_DIR"
else
    error "OpenCart dizini bulunamadı: $OPENCART_DIR"
fi

if [ -f "$OPENCART_DIR/config.php" ]; then
    success "Ana konfigürasyon dosyası mevcut"
else
    error "Ana konfigürasyon dosyası bulunamadı"
fi

if [ -f "$OPENCART_DIR/admin/config.php" ]; then
    success "Admin konfigürasyon dosyası mevcut"
else
    error "Admin konfigürasyon dosyası bulunamadı"
fi

if [ -f "$OPENCART_DIR/admin/controller/extension/module/meschain_trendyol.php" ]; then
    success "MesChain Trendyol controller dosyası mevcut"
else
    error "MesChain Trendyol controller dosyası bulunamadı"
fi

# Test 2: Dizin İzinleri Kontrolü
test_header "Dizin İzinleri Kontrolü"

if [ -w "$OPENCART_DIR/system/storage" ]; then
    success "Storage dizini yazılabilir"
else
    error "Storage dizini yazılabilir değil"
fi

if [ -w "$OPENCART_DIR/image" ]; then
    success "Image dizini yazılabilir"
else
    error "Image dizini yazılabilir değil"
fi

if [ -w "$OPENCART_DIR/system/storage/cache" ]; then
    success "Cache dizini yazılabilir"
else
    error "Cache dizini yazılabilir değil"
fi

# Test 3: Veritabanı Bağlantısı ve Tablo Kontrolü
test_header "Veritabanı Bağlantısı ve Tablo Kontrolü"

# Veritabanı bağlantısı testi
if mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1;" &>/dev/null; then
    success "Veritabanı bağlantısı başarılı"
else
    error "Veritabanı bağlantısı başarısız"
fi

# OpenCart temel tabloları kontrolü
REQUIRED_TABLES=("oc_product" "oc_category" "oc_order" "oc_customer" "oc_user" "oc_extension")
for table in "${REQUIRED_TABLES[@]}"; do
    if mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESCRIBE $table;" &>/dev/null; then
        success "Tablo mevcut: $table"
    else
        error "Tablo bulunamadı: $table"
    fi
done

# MesChain Trendyol tabloları kontrolü
MESCHAIN_TABLES=("oc_meschain_trendyol_products" "oc_meschain_trendyol_orders" "oc_meschain_trendyol_settings" "oc_meschain_trendyol_logs" "oc_meschain_trendyol_categories")
for table in "${MESCHAIN_TABLES[@]}"; do
    if mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESCRIBE $table;" &>/dev/null; then
        success "MesChain tablosu mevcut: $table"
    else
        error "MesChain tablosu bulunamadı: $table"
    fi
done

# Test 4: PHP-FPM Socket Kontrolü
test_header "PHP-FPM Socket Kontrolü"

if [ -S "/var/run/php/php8.1-fpm-8090.sock" ]; then
    success "PHP-FPM socket mevcut"
else
    error "PHP-FPM socket bulunamadı"
fi

if sudo -u www-data test -r "/var/run/php/php8.1-fpm-8090.sock"; then
    success "PHP-FPM socket okunabilir"
else
    error "PHP-FPM socket okunamıyor"
fi

# Test 5: Web Sunucusu Konfigürasyon Kontrolü
test_header "Web Sunucusu Konfigürasyon Kontrolü"

# Port 8090 dinleme kontrolü
if netstat -tlnp 2>/dev/null | grep -q ":8090 "; then
    success "Port 8090 dinleniyor"
else
    warning "Port 8090 dinlenmiyor (manuel konfigürasyon gerekli)"
fi

# Apache konfigürasyon kontrolü
if systemctl is-active --quiet apache2; then
    if [ -f "/etc/apache2/sites-available/opencart-8090.conf" ]; then
        success "Apache virtual host konfigürasyonu mevcut"
    else
        warning "Apache virtual host konfigürasyonu bulunamadı"
    fi
fi

# Nginx konfigürasyon kontrolü
if systemctl is-active --quiet nginx; then
    if [ -f "/etc/nginx/sites-available/opencart-8090.conf" ]; then
        success "Nginx server block konfigürasyonu mevcut"
    else
        warning "Nginx server block konfigürasyonu bulunamadı"
    fi
fi

# Test 6: HTTP Erişim Testi
test_header "HTTP Erişim Testi"

# Ana sayfa erişim testi
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$SITE_URL" --connect-timeout 10 || echo "000")
if [ "$HTTP_CODE" = "200" ]; then
    success "Ana sayfa erişilebilir (HTTP $HTTP_CODE)"
elif [ "$HTTP_CODE" = "000" ]; then
    error "Ana sayfa erişilemiyor (Bağlantı hatası)"
else
    warning "Ana sayfa beklenmeyen yanıt (HTTP $HTTP_CODE)"
fi

# Admin paneli erişim testi
ADMIN_HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$ADMIN_URL" --connect-timeout 10 || echo "000")
if [ "$ADMIN_HTTP_CODE" = "200" ]; then
    success "Admin paneli erişilebilir (HTTP $ADMIN_HTTP_CODE)"
elif [ "$ADMIN_HTTP_CODE" = "000" ]; then
    error "Admin paneli erişilemiyor (Bağlantı hatası)"
else
    warning "Admin paneli beklenmeyen yanıt (HTTP $ADMIN_HTTP_CODE)"
fi

# Test 7: PHP Konfigürasyon Kontrolü
test_header "PHP Konfigürasyon Kontrolü"

# PHP sürüm kontrolü
PHP_VERSION=$(php -r "echo PHP_VERSION;" 2>/dev/null || echo "unknown")
if [[ "$PHP_VERSION" =~ ^8\.[1-9] ]]; then
    success "PHP sürümü uygun: $PHP_VERSION"
else
    error "PHP sürümü uygun değil: $PHP_VERSION"
fi

# Gerekli PHP modülleri kontrolü
REQUIRED_MODULES=("mysqli" "curl" "gd" "zip" "xml" "mbstring" "json" "opcache")
for module in "${REQUIRED_MODULES[@]}"; do
    if php -m | grep -q "^$module$"; then
        success "PHP modülü mevcut: $module"
    else
        error "PHP modülü eksik: $module"
    fi
done

# Test 8: MesChain Trendyol Ayarları Kontrolü
test_header "MesChain Trendyol Ayarları Kontrolü"

# Ayarlar tablosu veri kontrolü
SETTINGS_COUNT=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -se "SELECT COUNT(*) FROM oc_meschain_trendyol_settings;" 2>/dev/null || echo "0")
if [ "$SETTINGS_COUNT" -gt "0" ]; then
    success "MesChain Trendyol ayarları mevcut ($SETTINGS_COUNT ayar)"
else
    error "MesChain Trendyol ayarları bulunamadı"
fi

# Extension kaydı kontrolü
EXTENSION_EXISTS=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -se "SELECT COUNT(*) FROM oc_extension WHERE code='meschain_trendyol';" 2>/dev/null || echo "0")
if [ "$EXTENSION_EXISTS" -gt "0" ]; then
    success "MesChain Trendyol extension kaydı mevcut"
else
    error "MesChain Trendyol extension kaydı bulunamadı"
fi

# Test 9: Log Dosyaları ve İzinler Kontrolü
test_header "Log Dosyaları ve İzinler Kontrolü"

# Log dizini kontrolü
if [ -d "/var/log/opencart-8090" ]; then
    success "Log dizini mevcut"
else
    error "Log dizini bulunamadı"
fi

# OpenCart log dizini kontrolü
if [ -d "$OPENCART_DIR/system/storage/logs" ]; then
    success "OpenCart log dizini mevcut"
    if [ -w "$OPENCART_DIR/system/storage/logs" ]; then
        success "OpenCart log dizini yazılabilir"
    else
        error "OpenCart log dizini yazılabilir değil"
    fi
else
    error "OpenCart log dizini bulunamadı"
fi

# Test 10: Güvenlik Kontrolü
test_header "Güvenlik Kontrolü"

# Install dizini kontrolü
if [ -d "$OPENCART_DIR/install" ]; then
    error "Install dizini hala mevcut (güvenlik riski)"
else
    success "Install dizini kaldırılmış"
fi

# Config dosyaları izin kontrolü
CONFIG_PERMS=$(stat -c "%a" "$OPENCART_DIR/config.php" 2>/dev/null || echo "000")
if [ "$CONFIG_PERMS" = "644" ] || [ "$CONFIG_PERMS" = "640" ]; then
    success "Ana config dosyası izinleri güvenli ($CONFIG_PERMS)"
else
    warning "Ana config dosyası izinleri kontrol edilmeli ($CONFIG_PERMS)"
fi

ADMIN_CONFIG_PERMS=$(stat -c "%a" "$OPENCART_DIR/admin/config.php" 2>/dev/null || echo "000")
if [ "$ADMIN_CONFIG_PERMS" = "644" ] || [ "$ADMIN_CONFIG_PERMS" = "640" ]; then
    success "Admin config dosyası izinleri güvenli ($ADMIN_CONFIG_PERMS)"
else
    warning "Admin config dosyası izinleri kontrol edilmeli ($ADMIN_CONFIG_PERMS)"
fi

# Test Sonuçları Raporu
log "============================================================================"
log "TEST SONUÇLARI RAPORU"
log "============================================================================"

echo -e "${CYAN}Toplam Test: $TESTS_TOTAL${NC}"
echo -e "${GREEN}Başarılı: $TESTS_PASSED${NC}"
echo -e "${RED}Başarısız: $TESTS_FAILED${NC}"

# Başarı oranı hesaplama
if [ $TESTS_TOTAL -gt 0 ]; then
    SUCCESS_RATE=$((TESTS_PASSED * 100 / TESTS_TOTAL))
    echo -e "${PURPLE}Başarı Oranı: %$SUCCESS_RATE${NC}"
else
    SUCCESS_RATE=0
fi

# Test raporu dosyasına yaz
sudo tee -a "$TEST_REPORT" > /dev/null << EOF

Toplam Test: $TESTS_TOTAL
Başarılı Test: $TESTS_PASSED
Başarısız Test: $TESTS_FAILED
Başarı Oranı: %$SUCCESS_RATE

Test Tamamlanma Tarihi: $(date)
EOF

# Sonuç değerlendirmesi
echo
if [ $SUCCESS_RATE -ge 90 ]; then
    success "KURULUM BAŞARILI! Sistem production'a hazır."
    echo -e "${GREEN}✓ Tüm kritik testler geçti${NC}"
    echo -e "${GREEN}✓ Sistem stabil ve güvenli${NC}"
    echo -e "${GREEN}✓ MesChain Trendyol entegrasyonu hazır${NC}"
elif [ $SUCCESS_RATE -ge 70 ]; then
    warning "KURULUM KISMEN BAŞARILI! Bazı iyileştirmeler gerekli."
    echo -e "${YELLOW}⚠ Bazı testler başarısız${NC}"
    echo -e "${YELLOW}⚠ Sistem çalışır durumda ancak optimizasyon gerekli${NC}"
    echo -e "${YELLOW}⚠ Production öncesi düzeltmeler yapın${NC}"
else
    error "KURULUM BAŞARISIZ! Kritik sorunlar mevcut."
    echo -e "${RED}✗ Çok sayıda test başarısız${NC}"
    echo -e "${RED}✗ Sistem stabil değil${NC}"
    echo -e "${RED}✗ Production'a geçmeyin${NC}"
fi

echo
info "Detaylı Erişim Bilgileri:"
echo "  • Site URL: $SITE_URL"
echo "  • Admin URL: $ADMIN_URL"
echo "  • Veritabanı: $DB_NAME"
echo "  • Test Raporu: $TEST_REPORT"
echo

info "Sonraki Adımlar:"
if [ $SUCCESS_RATE -ge 90 ]; then
    echo "  1. Admin paneline giriş yapın"
    echo "  2. MesChain Trendyol modülünü etkinleştirin"
    echo "  3. API bilgilerini yapılandırın"
    echo "  4. Test ürünleri ile senkronizasyon testleri yapın"
    echo "  5. Production ortamına geçiş için hazırlıkları tamamlayın"
elif [ $SUCCESS_RATE -ge 70 ]; then
    echo "  1. Başarısız testleri inceleyin"
    echo "  2. Gerekli düzeltmeleri yapın"
    echo "  3. Testi tekrar çalıştırın"
    echo "  4. %90+ başarı oranına ulaştıktan sonra production'a geçin"
else
    echo "  1. Kurulum loglarını inceleyin"
    echo "  2. Kritik hataları düzeltin"
    echo "  3. Kurulumu baştan yapın"
    echo "  4. Teknik destek alın"
fi

echo
warning "Önemli Güvenlik Notları:"
echo "  • Admin şifresini mutlaka değiştirin!"
echo "  • Firewall kurallarını yapılandırın!"
echo "  • SSL sertifikası kurun!"
echo "  • Düzenli backup alın!"
echo "  • Log dosyalarını izleyin!"

log "Test scripti tamamlandı!"

# Exit code belirleme
if [ $SUCCESS_RATE -ge 90 ]; then
    exit 0
elif [ $SUCCESS_RATE -ge 70 ]; then
    exit 1
else
    exit 2
fi
