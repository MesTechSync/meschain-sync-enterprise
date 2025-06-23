#!/bin/bash

# ============================================================================
# MesChain Trendyol Hızlı Test Kurulumu (OCMOD'suz)
# RESTRUCTURED_UPLOAD/upload klasörünü kullanarak hızlı kurulum
# ============================================================================

set -e

# Renkli çıktı
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() { echo -e "${GREEN}[$(date +'%H:%M:%S')] $1${NC}"; }
error() { echo -e "${RED}[ERROR] $1${NC}"; exit 1; }
warning() { echo -e "${YELLOW}[WARNING] $1${NC}"; }

# Konfigürasyon
OPENCART_DIR="/var/www/html/opencart-8090"
SOURCE_DIR="../RESTRUCTURED_UPLOAD/upload"
DB_NAME="meschain_test_8090"
DB_USER="opencart_8090"
DB_PASS="oc8090_secure_password_2025"

log "MesChain Trendyol Hızlı Test Kurulumu Başlatılıyor..."

# Kontroller
[ ! -d "$OPENCART_DIR" ] && error "OpenCart dizini bulunamadı: $OPENCART_DIR"
[ ! -d "$SOURCE_DIR" ] && error "Kaynak dizin bulunamadı: $SOURCE_DIR"

# 1. Dosyaları kopyala
log "MesChain dosyaları kopyalanıyor..."
sudo cp -r "$SOURCE_DIR"/* "$OPENCART_DIR/"
sudo chown -R www-data:www-data "$OPENCART_DIR"

# 2. Veritabanı tablolarını oluştur
log "Veritabanı tabloları oluşturuluyor..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'EOF'
CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `trendyol_product_id` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `sync_status` enum('pending','synced','error') DEFAULT 'pending',
  `last_sync` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `trendyol_order_id` varchar(255) NOT NULL,
  `sync_status` enum('pending','synced','error') DEFAULT 'pending',
  `last_sync` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `oc_meschain_trendyol_settings` VALUES
(NULL, 'api_key', '', NOW()),
(NULL, 'api_secret', '', NOW()),
(NULL, 'supplier_id', '', NOW()),
(NULL, 'test_mode', '1', NOW()),
(NULL, 'debug_mode', '1', NOW());
EOF

# 3. Extension kaydını ekle
log "Extension kaydı ekleniyor..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
INSERT IGNORE INTO oc_extension (type, code) VALUES ('module', 'meschain_trendyol');
"

# 4. Cache temizle
log "Cache temizleniyor..."
sudo rm -rf "$OPENCART_DIR/system/storage/cache/*"

# 5. Test URL'leri
log "============================================================================"
log "HIZLI TEST KURULUMU TAMAMLANDI!"
log "============================================================================"
echo
echo "Test URL'leri:"
echo "• Ana Sayfa: http://localhost:8090"
echo "• Admin Panel: http://localhost:8090/admin"
echo "• MesChain Trendyol: Extensions > Extensions > Modules"
echo
echo "Test Adımları:"
echo "1. Admin paneline giriş yapın"
echo "2. Extensions > Extensions > Modules menüsüne gidin"
echo "3. MesChain Trendyol modülünü bulun"
echo "4. Install butonuna tıklayın"
echo "5. Edit butonuna tıklayın"
echo "6. API ayarlarını yapın"
echo
warning "Bu hızlı kurulum sadece test içindir!"
warning "Production için tam kurulum yapın!"

log "Hızlı test kurulumu tamamlandı!"
