# Orijinal Yazılım (MesChain) Tam Dosya ve Algoritma Haritası

## Kök Dizin
- `config.php` : Ana yapılandırma dosyası
- `README.md`, `README.txt` : Genel açıklama ve kullanım
- `LICENSE` : Lisans
- `CHANGELOG.md`, `VERSIYON.md`, `FUTURE_PLAN.md`, `SECURITY.md` : Sürüm, yol haritası, güvenlik ve değişiklikler
- `menu.json`, `announcements.json` : Menü ve duyuru verileri

## Ana Klasörler

### 1. `templates/`
- `default_theme/` : Panel ve modüller için tema şablonları

### 2. `platforms/`
- `trendyol/` : Trendyol entegrasyon kodları (config, controller, model, view, test)
- `amazon/`, `ebay/`, `hepsiburada/`, `n11/`, `ozon/` : Diğer pazar yeri entegrasyonları

### 3. `modules/`
- `trendyol/` : Trendyol modül ayarları, log, fonksiyonlar
  - `settings.php`, `actions.php`, `log.php`, `index.php`, `settings.json`, `settings_MesChain.json`
- `users.json`, `roles.json` : Kullanıcı ve rol yönetimi
- `invoicing/`, `payments/`, `personnel/`, `themes/` : Diğer modül fonksiyonları

### 4. `log/`
- Her modül ve fonksiyon için ayrı log dosyaları (ör: `trendyol_modul.log`, `rol_gorunum.log`, `panel_bildirim.log`)

### 5. `lang/`
- `tr.php`, `en.php` : Çoklu dil desteği

### 6. `install/`
- `install.php`, `uninstall.php`, `update.php` : Kurulum ve güncelleme scriptleri

### 7. `drivers/`
- `db_mysql.php`, `db_sqlite.php`, `db_oracle.php`, `db_blockchain.php` : Veritabanı sürücüleri

### 8. `docs/`
- `api-integration.md` : API entegrasyon dokümantasyonu

### 9. `data/`
- `products/`, `orders/`, `calendar/` : Ürün, sipariş ve takvim/görev verileri (JSON)

### 10. `cron/`
- `sync_trendyol.php` : Zamanlanmış görevler (ör: sipariş senkronizasyonu)

### 11. `api_gateway/`
- `index.php` : API yönlendirme ve gateway

### 12. `admin_panel/`
- `views/`, `themes/`, `includes/`, `controllers/`, `assets/` : Panel arayüzü, tema, yardımcılar, controller ve statik dosyalar
  - `dashboard.php`, `login.php`, `user_manager.php`, `role_manager.php`, `module_manager.php`, `announcement_manager.php`, `backup_manager.php`, `theme_select.php`, `module_upload.php`, `lang_select.php`, `logout.php`
  - `includes/PHPMailer/` : Mail gönderimi
  - `assets/js/main.js`, `assets/css/main.css` : Panelin JS ve CSS dosyaları

### 13. `core/`
- (Boş veya çekirdek fonksiyonlar için ayrılmış)

---

## Algoritmik Akış ve Modüller Arası İlişki
- **Kullanıcı/rol yönetimi**: `modules/users.json` ve `roles.json` ile, panelde ve modüllerde yetki kontrolü yapılır.
- **Panel arayüzü**: `admin_panel/` altındaki controller ve view dosyaları ile yönetilir.
- **Her platformun (trendyol, amazon, n11, vb.) kendi modül ve fonksiyonları** vardır.
- **Loglama**: Her önemli işlem, hata ve API çağrısı ilgili log dosyasına atomik olarak kaydedilir.
- **Takvim/görev sistemi**: `data/calendar/` altında kullanıcıya özel görevler tutulur.
- **Tema ve dil desteği**: `templates/`, `admin_panel/themes/`, `lang/` ile sağlanır.
- **Kurulum/güncelleme**: `install/` scriptleriyle yapılır.
- **API ve cron işlemleri**: `api_gateway/` ve `cron/` ile dış sistemlerle entegrasyon ve zamanlanmış görevler yönetilir.

---

Her dosya ve klasör, atomik ve sürdürülebilir bir yapıdadır. Modüller arası ilişki, kullanıcı/rol ve yetki yönetimiyle entegre çalışır. Tüm loglar, ayarlar ve veriler ayrı dosyalarda ve standart formatta tutulur. 