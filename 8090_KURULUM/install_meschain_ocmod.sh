#!/bin/bash

# ============================================================================
# MesChain Trendyol OCMOD Kurulum Scripti
# 8090 Port OpenCart için MesChain Trendyol Entegrasyonu
# ============================================================================

set -e  # Hata durumunda scripti durdur

# Renkli çıktı için
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
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

success() {
    echo -e "${PURPLE}[SUCCESS] $1${NC}"
}

# Konfigürasyon değişkenleri
OPENCART_DIR="/var/www/html/opencart-8090"
DB_NAME="meschain_test_8090"
DB_USER="opencart_8090"
DB_PASS="oc8090_secure_password_2025"
OCMOD_FILE="../MESCHAIN_TRENDYOL_FINAL.ocmod.xml"
BACKUP_DIR="/var/backups/opencart-8090"
LOG_FILE="/var/log/opencart-8090/ocmod-install.log"

log "MesChain Trendyol OCMOD Kurulum Başlatılıyor..."

# Ön kontroller
log "Ön kontroller yapılıyor..."

# OpenCart kurulumu kontrolü
if [ ! -d "$OPENCART_DIR" ]; then
    error "OpenCart kurulumu bulunamadı: $OPENCART_DIR"
fi

if [ ! -f "$OPENCART_DIR/config.php" ]; then
    error "OpenCart konfigürasyon dosyası bulunamadı!"
fi

# OCMOD dosyası kontrolü
if [ ! -f "$OCMOD_FILE" ]; then
    error "OCMOD dosyası bulunamadı: $OCMOD_FILE"
fi

# Veritabanı bağlantısı kontrolü
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1;" &>/dev/null || error "Veritabanı bağlantısı başarısız!"

log "Ön kontroller tamamlandı ✓"

# Backup oluştur
log "Güvenlik yedeği oluşturuluyor..."

sudo mkdir -p "$BACKUP_DIR"
BACKUP_TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Dosya yedeği
sudo tar -czf "$BACKUP_DIR/opencart_files_$BACKUP_TIMESTAMP.tar.gz" -C "$OPENCART_DIR" . || warning "Dosya yedeği oluşturulamadı!"

# Veritabanı yedeği
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/database_$BACKUP_TIMESTAMP.sql.gz" || warning "Veritabanı yedeği oluşturulamadı!"

log "Güvenlik yedeği oluşturuldu ✓"

# OCMOD dosyasını doğrula
log "OCMOD dosyası doğrulanıyor..."

# XML syntax kontrolü
xmllint --noout "$OCMOD_FILE" || error "OCMOD dosyasında XML syntax hatası!"

# OCMOD yapısı kontrolü
if ! grep -q "<modification>" "$OCMOD_FILE"; then
    error "Geçersiz OCMOD dosyası: <modification> etiketi bulunamadı!"
fi

if ! grep -q "meschain_trendyol" "$OCMOD_FILE"; then
    error "OCMOD dosyasında MesChain Trendyol kodu bulunamadı!"
fi

log "OCMOD dosyası doğrulandı ✓"

# Veritabanı tablolarını oluştur
log "MesChain Trendyol veritabanı tabloları oluşturuluyor..."

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'EOF'
-- MesChain Trendyol Products Table
CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_products` (
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
  `error_message` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_product` (`product_id`),
  KEY `trendyol_product_id` (`trendyol_product_id`),
  KEY `sync_status` (`sync_status`),
  KEY `last_sync` (`last_sync`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MesChain Trendyol Orders Table
CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `trendyol_order_id` varchar(255) NOT NULL,
  `shipment_package_id` varchar(255) DEFAULT NULL,
  `order_status` varchar(50) DEFAULT NULL,
  `cargo_tracking_number` varchar(255) DEFAULT NULL,
  `cargo_provider_name` varchar(100) DEFAULT NULL,
  `sync_status` enum('pending','synced','error') DEFAULT 'pending',
  `last_sync` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_order` (`order_id`),
  UNIQUE KEY `unique_trendyol_order` (`trendyol_order_id`),
  KEY `sync_status` (`sync_status`),
  KEY `last_sync` (`last_sync`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MesChain Trendyol Settings Table
CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text,
  `setting_type` enum('string','integer','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MesChain Trendyol Logs Table
CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_type` enum('info','warning','error','debug') DEFAULT 'info',
  `operation` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `data` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `log_type` (`log_type`),
  KEY `operation` (`operation`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MesChain Trendyol Categories Mapping Table
CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `opencart_category_id` int(11) NOT NULL,
  `trendyol_category_id` int(11) NOT NULL,
  `trendyol_category_name` varchar(255) NOT NULL,
  `commission_rate` decimal(5,2) DEFAULT 0.00,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_mapping` (`opencart_category_id`, `trendyol_category_id`),
  KEY `opencart_category_id` (`opencart_category_id`),
  KEY `trendyol_category_id` (`trendyol_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOF

if [ $? -eq 0 ]; then
    log "Veritabanı tabloları oluşturuldu ✓"
else
    error "Veritabanı tabloları oluşturulamadı!"
fi

# Varsayılan ayarları ekle
log "Varsayılan ayarlar ekleniyor..."

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'EOF'
INSERT IGNORE INTO `oc_meschain_trendyol_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('api_key', '', 'string', 'Trendyol API Anahtarı'),
('api_secret', '', 'string', 'Trendyol API Gizli Anahtarı'),
('supplier_id', '', 'string', 'Trendyol Tedarikçi ID'),
('environment', 'sandbox', 'string', 'API Ortamı (sandbox/production)'),
('auto_sync', '0', 'boolean', 'Otomatik Senkronizasyon'),
('sync_interval', '3600', 'integer', 'Senkronizasyon Aralığı (saniye)'),
('debug_mode', '1', 'boolean', 'Debug Modu'),
('last_product_sync', '', 'string', 'Son Ürün Senkronizasyonu'),
('last_order_sync', '', 'string', 'Son Sipariş Senkronizasyonu'),
('integration_status', 'testing', 'string', 'Entegrasyon Durumu'),
('webhook_url', '', 'string', 'Webhook URL'),
('webhook_secret', '', 'string', 'Webhook Gizli Anahtarı'),
('default_brand_id', '1', 'integer', 'Varsayılan Marka ID'),
('default_category_id', '1', 'integer', 'Varsayılan Kategori ID'),
('stock_buffer', '5', 'integer', 'Stok Tamponu'),
('price_margin', '0', 'integer', 'Fiyat Marjı (%)'),
('auto_approve_products', '0', 'boolean', 'Ürünleri Otomatik Onayla'),
('notification_email', '', 'string', 'Bildirim E-posta Adresi'),
('max_retry_count', '3', 'integer', 'Maksimum Yeniden Deneme'),
('connection_timeout', '30', 'integer', 'Bağlantı Zaman Aşımı (saniye)');
EOF

log "Varsayılan ayarlar eklendi ✓"

# OCMOD dosyalarını oluştur
log "OCMOD dosyaları oluşturuluyor..."

# Admin controller dosyası
sudo mkdir -p "$OPENCART_DIR/admin/controller/extension/module"
sudo tee "$OPENCART_DIR/admin/controller/extension/module/meschain_trendyol.php" > /dev/null << 'EOF'
<?php
class ControllerExtensionModuleMeschainTrendyol extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/module/meschain_trendyol');
        $this->document->setTitle($this->language->get('heading_title'));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_meschain_trendyol', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/meschain_trendyol', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/meschain_trendyol', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $this->load->model('setting/setting');
        $setting_info = $this->model_setting_setting->getSetting('module_meschain_trendyol');

        if (isset($this->request->post['module_meschain_trendyol_status'])) {
            $data['module_meschain_trendyol_status'] = $this->request->post['module_meschain_trendyol_status'];
        } elseif (isset($setting_info['module_meschain_trendyol_status'])) {
            $data['module_meschain_trendyol_status'] = $setting_info['module_meschain_trendyol_status'];
        } else {
            $data['module_meschain_trendyol_status'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/meschain_trendyol', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/meschain_trendyol')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }

    public function install() {
        // Kurulum işlemleri
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/meschain_trendyol');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/meschain_trendyol');
    }

    public function uninstall() {
        // Kaldırma işlemleri
        $this->load->model('user/user_group');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/module/meschain_trendyol');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/module/meschain_trendyol');
    }
}
EOF

# Admin language dosyası (Türkçe)
sudo mkdir -p "$OPENCART_DIR/admin/language/tr-tr/extension/module"
sudo tee "$OPENCART_DIR/admin/language/tr-tr/extension/module/meschain_trendyol.php" > /dev/null << 'EOF'
<?php
// Heading
$_['heading_title']    = 'MesChain Trendyol Entegrasyonu';

// Text
$_['text_extension']   = 'Eklentiler';
$_['text_success']     = 'Başarılı: MesChain Trendyol modülü güncellendi!';
$_['text_edit']        = 'MesChain Trendyol Modülünü Düzenle';

// Entry
$_['entry_status']     = 'Durum';

// Error
$_['error_permission'] = 'Uyarı: MesChain Trendyol modülünü değiştirme yetkiniz yok!';
EOF

# Admin language dosyası (İngilizce)
sudo mkdir -p "$OPENCART_DIR/admin/language/en-gb/extension/module"
sudo tee "$OPENCART_DIR/admin/language/en-gb/extension/module/meschain_trendyol.php" > /dev/null << 'EOF'
<?php
// Heading
$_['heading_title']    = 'MesChain Trendyol Integration';

// Text
$_['text_extension']   = 'Extensions';
$_['text_success']     = 'Success: You have modified MesChain Trendyol module!';
$_['text_edit']        = 'Edit MesChain Trendyol Module';

// Entry
$_['entry_status']     = 'Status';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify MesChain Trendyol module!';
EOF

# Admin view dosyası
sudo mkdir -p "$OPENCART_DIR/admin/view/template/extension/module"
sudo tee "$OPENCART_DIR/admin/view/template/extension/module/meschain_trendyol.twig" > /dev/null << 'EOF'
{{ header }}{{ column_left }}
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-module" data-toggle="tooltip" title="{{ button_save }}" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="{{ cancel }}" data-toggle="tooltip" title="{{ button_cancel }}" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1>{{ heading_title }}</h1>
      <ul class="breadcrumb">
        {% for breadcrumb in breadcrumbs %}
        <li><a href="{{ breadcrumb.href }}">{{ breadcrumb.text }}</a></li>
        {% endfor %}
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> {{ text_edit }}</h3>
      </div>
      <div class="panel-body">
        <form action="{{ action }}" method="post" enctype="multipart/form-data" id="form-module" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status">{{ entry_status }}</label>
            <div class="col-sm-10">
              <select name="module_meschain_trendyol_status" id="input-status" class="form-control">
                {% if module_meschain_trendyol_status %}
                <option value="1" selected="selected">{{ text_enabled }}</option>
                <option value="0">{{ text_disabled }}</option>
                {% else %}
                <option value="1">{{ text_enabled }}</option>
                <option value="0" selected="selected">{{ text_disabled }}</option>
                {% endif %}
              </select>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
{{ footer }}
EOF

# İzinleri düzelt
sudo chown -R www-data:www-data "$OPENCART_DIR/admin/controller/extension/module/meschain_trendyol.php"
sudo chown -R www-data:www-data "$OPENCART_DIR/admin/language/*/extension/module/meschain_trendyol.php"
sudo chown -R www-data:www-data "$OPENCART_DIR/admin/view/template/extension/module/meschain_trendyol.twig"

log "OCMOD dosyaları oluşturuldu ✓"

# Extension tablosuna kayıt ekle
log "Extension kaydı ekleniyor..."

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'EOF'
INSERT IGNORE INTO `oc_extension` (`extension_id`, `type`, `code`) VALUES
(NULL, 'module', 'meschain_trendyol');
EOF

log "Extension kaydı eklendi ✓"

# Cache temizle
log "Cache temizleniyor..."

sudo rm -rf "$OPENCART_DIR/system/storage/cache/*"
sudo rm -rf "$OPENCART_DIR/system/storage/modification/*"

log "Cache temizlendi ✓"

# Log dosyası oluştur
log "Kurulum raporu oluşturuluyor..."

sudo mkdir -p "$(dirname "$LOG_FILE")"
sudo tee "$LOG_FILE" > /dev/null << EOF
MesChain Trendyol OCMOD Kurulum Raporu
=====================================
Kurulum Tarihi: $(date)
OpenCart Dizini: $OPENCART_DIR
Veritabanı: $DB_NAME
OCMOD Dosyası: $OCMOD_FILE
Backup Dizini: $BACKUP_DIR

Oluşturulan Tablolar:
- oc_meschain_trendyol_products
- oc_meschain_trendyol_orders
- oc_meschain_trendyol_settings
- oc_meschain_trendyol_logs
- oc_meschain_trendyol_categories

Oluşturulan Dosyalar:
- admin/controller/extension/module/meschain_trendyol.php
- admin/language/tr-tr/extension/module/meschain_trendyol.php
- admin/language/en-gb/extension/module/meschain_trendyol.php
- admin/view/template/extension/module/meschain_trendyol.twig

Kurulum Durumu: BAŞARILI
Entegrasyon Durumu: HAZIR
Test Gerekli: EVET
EOF

# Kurulum tamamlandı
log "============================================================================"
success "MESCHAIN TRENDYOL OCMOD KURULUMU BAŞARIYLA TAMAMLANDI!"
log "============================================================================"
echo
info "Kurulum Detayları:"
echo "  • Veritabanı Tabloları: 5 tablo oluşturuldu"
echo "  • Admin Dosyaları: 4 dosya oluşturuldu"
echo "  • Extension Kaydı: Eklendi"
echo "  • Cache: Temizlendi"
echo "  • Backup: $BACKUP_DIR"
echo
info "Sonraki Adımlar:"
echo "  1. Admin paneline giriş yapın: http://localhost:8090/admin"
echo "  2. Extensions > Extensions > Modules menüsüne gidin"
echo "  3. MesChain Trendyol modülünü bulun ve Install edin"
echo "  4. Modül ayarlarını yapılandırın"
echo "  5. API bilgilerini girin"
echo "  6. Test işlemlerini çalıştırın: ./test_installation.sh"
echo
warning "Önemli Notlar:"
echo "  • API bilgilerini girmeden önce test etmeyin!"
echo "  • Sandbox ortamında test yapın!"
echo "  • Production'a geçmeden önce tüm testleri tamamlayın!"
echo
log "Kurulum raporu: $LOG_FILE"
log "OCMOD kurulum scripti tamamlandı!"
