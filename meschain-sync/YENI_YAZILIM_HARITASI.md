# Yeni Yazılım (OpenCart Trendyol Connector) Tam Dosya ve Algoritma Haritası

## Kök Dizin (upload/)
- `install.xml` : OpenCart OCMOD kurulum dosyası
- `docs/` : Proje dokümantasyonu, API örnekleri, hata haritaları
  - `plan.txt` : Proje açıklama ve yol haritası
  - `api_examples.json` : Trendyol API için örnek JSON payload'lar
  - `error_map.txt` : Hata kodları ve açıklamaları

## Ana Klasörler

### 1. `controller/`
- `extension/module/trendyol.php` : Admin panelde Trendyol modülünü ve panelini yönetir
- `extension/module/trendyol_login.php` : Özel login ekranı ve oturum yönetimi
- `extension/module/trendyol_helper.php` : API yardımcı fonksiyonlar, loglama
- `extension/module/trendyol_tasks.php` : Takvim/görev yönetimi (isteğe bağlı ayrı controller)

### 2. `model/`
- `extension/module/trendyol_model.php` : Ürün gönderme, sipariş çekme, veri işleme ve API bağlantıları

### 3. `language/`
- `tr-tr/extension/module/trendyol.php` : Panel ve modül için Türkçe dil dosyası
- `en-gb/extension/module/trendyol.php` : Panel ve modül için İngilizce dil dosyası (isteğe bağlı)

### 4. `view/`
- `template/extension/module/trendyol_dashboard.twig` : Admin paneli ana arayüzü (dashboard)
- `template/extension/module/trendyol_login.twig` : Özel login ekranı
- `template/extension/module/trendyol_tasks.twig` : Takvim/görev yönetimi arayüzü (isteğe bağlı)

### 5. `system/library/trendyol/`
- `TrendyolClient.php` : Ana API istemcisi (token, istek, hata yönetimi)
- `TrendyolProduct.php` : Ürün işlemleri (ekle, güncelle, sil)
- `TrendyolOrder.php` : Sipariş işlemleri (çek, güncelle, durum değiştir)
- `TrendyolLogger.php` : Atomik loglama ve hata yönetimi

### 6. `logs/`
- `trendyol_controller.log` : Panel ve ayar işlemleri logu
- `trendyol_helper.log` : API ve yardımcı işlemler logu
- `trendyol_tasks.log` : Takvim/görev işlemleri logu
- `error.log`, `api.log` : Diğer hata ve API logları

### 7. `cron/`
- `update_products.php` : Ürünleri XML'den/DB'den Trendyol'a güncelleyen cron scripti
- `fetch_orders.php` : Siparişleri Trendyol'dan çeken cron scripti

### 8. `config/`
- `config_trendyol.php` : Trendyol API anahtarları, endpoint ve yapılandırma

### 9. `install/`
- `install.php`, `uninstall.php`, `update.php` : Kurulum ve güncelleme scriptleri (isteğe bağlı)

---

## Algoritmik Akış ve Modüller Arası İlişki
- **Kullanıcı/rol yönetimi**: OpenCart'ın kendi kullanıcı sistemi + modül içi rol mapping
- **Panel arayüzü**: `view/template/extension/module/` altındaki Twig dosyaları ile yönetilir
- **API işlemleri**: `system/library/trendyol/` altındaki sınıflar ile Trendyol API'ye bağlanılır
- **Loglama**: Her önemli işlem, hata ve API çağrısı ilgili log dosyasına atomik olarak kaydedilir
- **Takvim/görev sistemi**: Kullanıcıya özel görevler dosyada veya DB'de tutulur, panelde ekle/düzenle/sil yapılabilir
- **Kurulum/güncelleme**: `install/` ve `install.xml` ile yapılır
- **Dil desteği**: `language/` klasörü ile çoklu dil desteği sağlanır
- **Cron ve otomasyon**: `cron/` klasöründeki scriptlerle zamanlanmış görevler yönetilir
- **Dokümantasyon**: `docs/` klasöründe proje planı, API örnekleri ve hata haritaları bulunur

---

Her dosya ve klasör, atomik, sürdürülebilir ve modern OpenCart modül standartlarına uygundur. Tüm loglar, ayarlar ve veriler ayrı dosyalarda ve standart formatta tutulur. Panel ve API işlemleri, rol bazlı yetki ve güvenlik kontrolleriyle entegre çalışır. 