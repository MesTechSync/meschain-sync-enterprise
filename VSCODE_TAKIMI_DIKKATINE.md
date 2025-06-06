# Konu: MesChain-Sync Projesi - Mimari Yeniden Yapılandırma ve Yeni Geliştirme Standartları Bilgilendirmesi

**Kime:** VSCode Geliştirme Ekibi
**Kimden:** Gemini Geliştirme Ekibi
**Tarih:** 6 Haziran 2025

Merhaba Ekip,

Projemizin uzun vadeli sağlığını, güvenliğini ve sürdürülebilirliğini artırmak amacıyla kod tabanında kapsamlı bir yeniden yapılandırma ve standardizasyon çalışması tamamlanmıştır. Bu belgenin amacı, yapılan değişiklikleri şeffaf bir şekilde özetlemek ve tüm ekibin yeni standartlar üzerinde uyum içinde çalışmasını sağlamaktır.

---

## 1. Neden Bu Değişiklikler Yapıldı? (Analiz Bulguları)

Yaptığımız derinlemesine kod analizi sonucunda, projenin büyümesini ve kararlılığını tehdit eden bazı temel sorunlar tespit edildi:

-   **Mimari Tutarsızlık:** Her pazar yeri modülü (`Trendyol`, `N11` vb.) kendi özel mantığı ve API çağırma yöntemiyle çalışıyordu. Ortak bir temel yapı yoktu.
-   **Kritik Güvenlik Açıkları:** API bağlantılarında SSL sertifika doğrulaması devre dışı bırakılmıştı (`CURLOPT_SSL_VERIFYPEER = false`), bu da sistemi Ortadaki Adam (MITM) saldırılarına karşı savunmasız bırakıyordu. Ayrıca, bazı modüllerde yetkilendirme kontrolleri bypass edilmişti.
-   **Kod Tekrarı ve Bakım Zorluğu:** API çağrı mantığı, hem PHP kontrolcülerinde hem de `server.js` gibi farklı yerlerde tekrar ediyordu. Bu, bir değişiklik yapmayı çok zor ve riskli hale getiriyordu.
-   **İşlevsel Olmayan Modüller:** `Dropshipping` modülü gibi önemli özelliklerin altyapısı olmasına rağmen, API entegrasyonları tamamlanmadığı için işlevsel değildi.

Bu sorunları kökünden çözmek için aşağıdaki yeniden yapılandırma süreci uygulanmıştır.

---

## 2. Ne Değişti? (Yeni Mimari ve Standartlar)

### A. Merkezi API Katmanı: `upload/system/library/meschain/api/`
Artık tüm pazar yeri API etkileşimleri için **tek bir yetkili merkezimiz** var. Bu dizin altında, her pazar yeri için özel olarak oluşturulmuş `ApiClient` sınıfları bulunmaktadır:
-   `TrendyolApiClient.php`
-   `N11ApiClient.php`
-   `OzonApiClient.php`
-   `HepsiburadaApiClient.php`
-   `AmazonApiClient.php`

**Bu sınıfların avantajları:**
-   **Güvenli:** Tüm API çağrılarında SSL doğrulaması (`CURLOPT_SSL_VERIFYPEER = true`) zorunludur.
-   **Merkezi:** API ile ilgili tüm mantık (kimlik doğrulama, istek yapma) bu sınıfların içindedir.
-   **Tekrar Kullanılabilir:** Projenin herhangi bir yerinden, aynı standart yöntemle API'lere erişilebilir.

### B. Temel Pazar Yeri Sınıfı: `base_marketplace.php`
Tüm pazar yeri kontrolcüleri artık `upload/admin/controller/extension/module/base_marketplace.php` içinde bulunan `ControllerExtensionModuleBaseMarketplace` sınıfından kalıtım almaktadır.

**Bu standardizasyonun getirdikleri:**
-   **Ortak İşlevsellik:** Ayar kaydetme (`saveSettings`), API anahtarlarını güvenli getirme (`getApiCredentials`), loglama (`log`) ve yetkilendirme (`validate`) gibi tüm ortak işlemler bu temel sınıfta toplanmıştır.
-   **Tutarlı Yapı:** Tüm pazar yeri modülleri artık aynı temel metotları ve yapıyı paylaşmaktadır.

### C. Modüllerin Yeniden Yapılandırılması
`Trendyol`, `N11`, `Ozon`, `Hepsiburada` ve `Amazon` modüllerinin kontrolcüleri **tamamen yeniden yazılmıştır.** Artık bu kontrolcüler:
1.  `base_marketplace.php`'den `extends` eder.
2.  Tüm API işlemleri için ilgili `ApiClient` sınıfını kullanır.
3.  İçerisinde hiçbir cURL kodu, güvensiz ayar yönetimi veya bypass edilmiş yetki kontrolü barındırmaz.

### D. Gereksiz Dosyaların Temizlenmesi
-   `server.js` ve `config.json` dosyaları **kaldırılmıştır.** Bu dosyaların yönettiği API proxy işlevselliği, artık doğrudan PHP tarafında, güvenli API istemcileri aracılığıyla yapılmaktadır.
-   Tüm eski `..._helper.php` dosyaları (`n11_helper.php`, `ozon_helper.php` vb.) kaldırılarak yerlerine yeni `ApiClient` sınıfları getirilmiştir.

---

## 3. Bundan Sonra Nasıl Çalışmalıyız? (Geliştirme Kuralları)

Projenin bütünlüğünü korumak için, bundan sonraki tüm geliştirmelerde aşağıdaki kurallara uyulması büyük önem taşımaktadır:

-   **Kural 1: Yeni Bir Pazar Yeri Eklerken:**
    1.  `upload/system/library/meschain/api/` dizini içine yeni `YeniPazarYeriApiClient.php` dosyasını oluşturun.
    2.  `upload/admin/controller/extension/module/` dizini içine `yenipazaryeri.php` dosyasını oluşturun ve bu sınıfın `ControllerExtensionModuleBaseMarketplace`'den kalıtım aldığından emin olun.
    3.  Tüm API işlemlerini yeni oluşturduğunuz `ApiClient` üzerinden yapın.

-   **Kural 2: Mevcut Bir Modüle Özellik Eklerken:**
    -   Eğer eklenecek özellik yeni bir API çağrısı gerektiriyorsa, bu çağrıyı ilgili `ApiClient` sınıfına yeni bir metot olarak ekleyin.
    -   Kontrolcüye sadece arayüz mantığını ve `ApiClient`'ı çağıran kodu ekleyin.

-   **Kural 3: ASLA Doğrudan cURL veya `file_get_contents` Kullanmayın!**
    -   API çağrıları için **kesinlikle** doğrudan cURL fonksiyonları kullanılmamalıdır. Tüm istekler, standartlaştırılmış `ApiClient` sınıfları üzerinden yapılmalıdır. Bu, güvenlik ve bakım tutarlılığı için en önemli kuraldır.

-   **Kural 4: Test Yazın!**
    -   Projenin `upload/` dizini altına bir `PHPUnit` test altyapısı kurulmuştur. Testler `upload/tests/` dizininde yer almaktadır.
    -   Eklenen her yeni API metodu veya kritik iş mantığı için birim testi (`unit test`) yazılması, projenin kalitesini korumamız için gereklidir.

---

## 4. Güncellenen İşlevsellik: Dropshipping Modülü

Bu yeniden yapılandırma sürecinin bir parçası olarak, daha önce işlevsel olmayan **Dropshipping modülü artık tamamen çalışır durumdadır.**
-   Stok senkronizasyonu artık gerçek API verileriyle çalışmaktadır.
-   Sipariş oluşturma mantığı, tüm standartlaştırılmış pazar yerleri için sipariş iletebilmektedir.
-   Tedarikçileri ve onlara özel API ayarlarını yönetmek için `Dropshipping Yönetimi` sayfasında yeni ve tam işlevsel (Ekle/Düzenle/Sil) bir arayüz bulunmaktadır.

Bu değişikliklerin, projemizi daha sağlam, güvenli ve üzerinde çalışması daha keyifli bir hale getireceğine inanıyoruz. Herhangi bir sorunuz veya belirsizlik olursa lütfen bizimle iletişime geçmekten çekinmeyin.

İyi çalışmalar dileriz. 