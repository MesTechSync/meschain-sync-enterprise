# MesChain SYNC Trendyol Entegrasyonu – VSCode Ekibi Görev Listesi

**Tarih:** 20 Haziran 2025
**Hazırlayan:** GitHub Copilot (VSCode Ekibi için)

---

## 🎯 Amaç

RESTRUCTURED_UPLOAD klasöründeki Trendyol modülünün, eksiksiz ve canlıda çalışacak şekilde OpenCart 4.x sistemine entegre edilmesi için VSCode ekibinin adım adım uygulayacağı görevlerin detaylı ve teknik olarak eksiksiz şekilde listelenmesi.

---

## 📝 VSCode Ekibi İçin Detaylı Görev Listesi

| No | Görev Başlığı | Açıklama | Sorumlu | Durum | Tamamlanma Tarihi |
|----|---------------|----------|---------|-------|-------------------|
| 1  | Klasör ve Dosya Temizliği | RESTRUCTURED_UPLOAD altında gereksiz, eski veya çakışan dosyaların temizlenmesi. Sadece güncel ve gerekli dosyaların bırakılması. | VSCode Ekibi | ⬜ |  |
| 2  | Extension Type ve Menü | Trendyol modülünün OpenCart'ta "MesChain SYNC" başlığı altında görünecek şekilde özel extension type ile kaydedilmesi. `oc_extension_path` ve `oc_extension` tabloları güncellenecek. | VSCode Ekibi | ⬜ |  |
| 3  | Dil Dosyaları | Gerekli tüm dil dosyalarının (en-gb, tr-tr) oluşturulması ve güncellenmesi. Panelde doğru başlık ve çoklu dil desteği sağlanacak. | VSCode Ekibi | ⬜ |  |
| 4  | Kurulum Scripti | `install_meschain_core.php` betiğinin, OpenCart extension install/uninstall fonksiyonlarını tetikleyecek ve tüm veritabanı işlemlerini otomatik yapacak şekilde güncellenmesi. | VSCode Ekibi | ⬜ |  |
| 5  | Veritabanı Otomasyonu | Tüm veritabanı tabloları, ayarları ve default config'in otomatik kurulması. (oc_meschain_marketplaces, oc_meschain_products, oc_meschain_orders, oc_meschain_logs, oc_cron) | VSCode Ekibi | ⬜ |  |
| 6  | Composer ve Bağımlılıklar | composer.json ve vendor/ klasörünün güncellenmesi, eksik bağımlılıkların yüklenmesi (JWT, Guzzle, vs.), autoload işlemlerinin kontrolü. | VSCode Ekibi | ⬜ |  |
| 7  | Azure Entegrasyon Testi | Azure Key Vault, Storage, Monitor ve AD entegrasyonlarının canlı ortamda test edilmesi ve config dosyalarının güncellenmesi. | VSCode Ekibi | ⬜ |  |
| 8  | Güvenlik Katmanları | JWT, rate limiting, webhook signature, data encryption gibi güvenlik katmanlarının kodda ve canlıda test edilmesi. | VSCode Ekibi | ⬜ |  |
| 9  | Admin Panel Doğrulama | Trendyol modülünün admin panelde doğru başlık altında, eksiksiz ve hatasız göründüğünün doğrulanması. | VSCode Ekibi | ⬜ |  |
| 10 | Fonksiyon Testleri | Tüm fonksiyonların (API, webhook, kategori, ürün, sipariş, log) çalıştığının test edilmesi. | VSCode Ekibi | ⬜ |  |
| 11 | Otomatik Test Scripti | test_trendyol_integration.php ile tüm fonksiyonların otomatik test edilmesi ve sonuçların raporlanması. | VSCode Ekibi | ⬜ |  |
| 12 | Son Kullanıcı Dokümantasyonu | Son kullanıcı dokümantasyonunun ve troubleshooting rehberinin güncellenmesi, eksiksiz ve anlaşılır şekilde teslim edilmesi. | VSCode Ekibi | ⬜ |  |

---

## 📋 Açıklamalar

- Tüm görevler VSCode ekibi tarafından eksiksiz ve sıralı şekilde uygulanacaktır.
- Her adım tamamlandıkça, durum ve tarih alanı güncellenmelidir.
- Teknik detaylar ve çözüm planı için `MESCHAIN_SYNC_TRENDYOL_KURULUM_ANALIZ_RAPORU.md` dosyasına başvurulabilir.
- Amaç: RESTRUCTURED_UPLOAD içindeki Trendyol sistemi, OpenCart 4’te eksiksiz ve canlıda sorunsuz çalışacak şekilde teslim edilmelidir.

---

**Not:** Bu tablo, VSCode ekibinin Trendyol entegrasyonunu eksiksiz ve canlıya hazır şekilde tamamlaması için hazırlanmıştır. Her adım tamamlandıkça işaretlenmelidir.
