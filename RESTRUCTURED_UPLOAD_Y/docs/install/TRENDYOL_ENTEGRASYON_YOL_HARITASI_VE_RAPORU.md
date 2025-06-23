# Trendyol Modülü - Master Entegrasyon Kılavuzu (Blueprint)

**Tarih:** 19 Haziran 2025
**Versiyon:** 3.0 (Master)
**Amaç:** Mevcut ve çalışan Trendyol modül bileşenlerini, herhangi bir OpenCart 4.x sistemine **kod yazmadan**, sadece dosya aktarımı ve yapılandırma ile eksiksiz bir şekilde entegre etmek. Bu döküman, tam bir "lift and shift" (al ve taşı) operasyonu için gereken tüm bilgileri içerir.

---

## 1. Entegrasyon Mimarisi ve Felsefesi

Bu entegrasyon, iki temel senaryoyu destekleyecek şekilde tasarlanmıştır:

1.  **Bağımsız Eklenti:** Sadece Trendyol entegrasyonuna ihtiyaç duyan bir OpenCart sistemi için tek başına kurulabilir.
2.  **MesChain SYNC Ekosistemi:** `MesChain-Sync Enterprise` ana sistemiyle tam uyumlu çalışarak, merkezi bir panelden diğer pazaryerleri ile birlikte yönetilebilir.

Bu kılavuz, her iki senaryo için de geçerli olan adımları detaylandırmaktadır.

---

## 2. Bileşen Envanteri: Tüm Trendyol Dosyaları ve Görevleri

Başarılı bir entegrasyon için aşağıdaki dosyaların tam ve doğru bir şekilde hedef sisteme aktarılması gerekmektedir.

### **Kategori 1: OpenCart Backend Dosyaları (PHP)**
*Bu dosyalar `upload/` dizini altındadır ve doğrudan hedef OpenCart kök dizinine kopyalanmalıdır.*

| Dosya Yolu | Görevi |
| :--- | :--- |
| `admin/controller/extension/module/trendyol.php` | Ana modül yönetim paneli. Ayarlar, manuel senkronizasyon ve log görüntüleme mantığını içerir. |
| `admin/controller/extension/module/trendyol_login.php`| Güvenli (2FA destekli) modül giriş ekranı denetleyicisi. |
| `admin/model/extension/module/trendyol.php` | Tüm veritabanı işlemleri: Ürün, sipariş, kategori eşleştirme, webhook ve ayarları yönetir. `install()`/`uninstall()` fonksiyonlarını içerir. |
| `admin/language/tr-tr/extension/module/trendyol.php` | Yönetim paneli için Türkçe dil metinleri. |
| `admin/language/en-gb/extension/module/trendyol.php` | Yönetim paneli için İngilizce dil metinleri. |
| `admin/view/template/extension/module/trendyol_*.twig` | Yönetim paneli arayüzünü oluşturan tüm `twig` şablonları. |
| `catalog/controller/extension/module/trendyol_api.php`| Dışarıya açık API endpoint'leri (Örn: harici bir servisin stok sorması için). |
| `catalog/model/extension/module/trendyol_webhook.php`| Trendyol'dan gelen webhook bildirimlerini işleyen model. |

### **Kategori 2: MesChain Çekirdek Kütüphaneleri (PHP)**
*Bu dosyalar, sistemin kalbidir ve `upload/system/library/meschain/` altında bulunur.*

| Dosya Yolu | Görevi |
| :--- | :--- |
| `api/TrendyolApiClient.php` | Trendyol API'si ile iletişim kuran ana istemci. Tüm GET/POST/PUT isteklerini yönetir. |
| `webhook/TrendyolWebhookHandler.php` | Gelen webhook bildirimlerinin imzasını doğrulayan ve ilgili aksiyonları tetikleyen sınıf. |
| `helper/trendyol.php` | Veri formatlama, rate-limiting (API istek limiti yönetimi), hata loglama gibi kritik yardımcı fonksiyonları barındırır. |

### **Kategori 3: Süper Admin Arayüzü (Opsiyonel - İleri Düzey)**
*Bu dosyalar, standart OpenCart arayüzü yerine daha gelişmiş bir yönetim paneli sunmak için kullanılabilir.*

| Dosya Yolu | Görevi |
| :--- | :--- |
| `trendyol-admin.html` | Vue.js veya React ile geliştirilmiş, tüm özellikleri tek bir ekrandan yöneten gelişmiş süper admin paneli arayüzü. |
| `CursorDev/MARKETPLACE_UIS/trendyol_integration_v4_enhanced.js` | `trendyol-admin.html` arayüzünün tüm dinamik işlevselliğini sağlayan JavaScript dosyası. |

### **Kategori 4: Kurulum ve Yapılandırma Dosyaları**
*Bu dosyalar, entegrasyonun OpenCart sistemine kendisini tanıtmasını sağlar.*

| Dosya Yolu | Görevi |
| :--- | :--- |
| `install.xml` | **(OCMOD)** OpenCart modifikasyon dosyası. Sol ana menüye ve "Eklentiler > MesChain SYNC" altına linkleri ekler, sistem genelinde gerekli değişiklikleri yapar. |

---

## 3. Adım Adım Master Kurulum Protokolü

### **Adım 1: Dosya Aktarımı (Transplantasyon)**
1.  Kaynak projedeki `upload/` klasörünün **içeriğini** kopyalayın.
2.  Hedef OpenCart sisteminin **kök dizinine** yapıştırın. Bu işlem, `admin`, `catalog`, `system` klasörlerini ve diğer dosyaları mevcut olanlarla birleştirecektir.
3.  Kaynak projedeki `install.xml` dosyasını hedef sisteme kopyalayın (Genellikle bir `ocmod.zip` paketinin içinde yer alır).

### **Adım 2: OCMOD Kurulumu ve Aktivasyonu**
1.  OpenCart Admin Paneline giriş yapın.
2.  **Extensions > Installer**'a gidin.
3.  Hazırlanan `*.ocmod.zip` paketini yükleyin.
4.  **ÇOK ÖNEMLİ:** Yükleme sonrası **Extensions > Modifications**'a gidin.
5.  Sağ üstteki mavi **Refresh** butonuna tıklayarak modifikasyonları sisteme uygulayın.

### **Adım 3: Veritabanı ve Modül Kurulumu**
1.  **Extensions > Extensions**'a gidin.
2.  Açılır menüden **"MesChain SYNC"** eklenti tipini seçin.
3.  Listede **"Trendyol Entegrasyonu"**'nu bulun ve sağındaki yeşil **Install** (`+`) butonuna tıklayın. Bu, veritabanı tablolarını oluşturacaktır.
4.  Kurulum tamamlandığında, modülü yapılandırmak için mavi **Edit** (kalem) butonuna tıklayın.

### **Adım 4: Yapılandırma ve Test**
1.  **İzinler:** `System > Users > User Groups`'tan `Administrator` grubunu düzenleyerek `extension/module/trendyol` için hem erişim hem de düzenleme yetkilerini verin.
2.  **API Bilgileri:** Modülün ayarlar sayfasında, Trendyol'dan aldığınız **API Key, Secret ve Supplier ID**'yi girip kaydedin.
3.  **Bağlantı Testi:** "Bağlantıyı Test Et" butonu ile bilgilerin doğruluğunu anında kontrol edin.
4.  **Aktivasyon:** Modül genel durumunu **"Enabled"** yapıp tekrar kaydedin.

---

## 4. Doğrulama ve Son Kontroller (Post-Flight Check)
*   **Menü Kontrolü:** Sol ana menüde "MesChain SYNC" ve altında "Trendyol" linkinin göründüğünü doğrulayın.
*   **Eklenti Listesi Kontrolü:** `Extensions > Extensions > MesChain SYNC` altında modülün "Enabled" olarak listelendiğini doğrulayın.
*   **Log Kontrolü:** Test senkronizasyonu sonrası `storage/logs/meschain/` veya `storage/logs/trendyol.log` dosyalarında başarılı işlem kayıtlarını arayın.
*   **Önbellek Temizliği:** OpenCart ana panelindeki dişli ikonuna tıklayarak **Twig ve SASS** önbelleğini temizleyin.

Bu kılavuzu eksiksiz takip ederek, mevcut, kanıtlanmış ve tam fonksiyonel Trendyol entegrasyonunu yeni bir OpenCart 4 sistemine sorunsuz bir şekilde kurabilirsiniz.
