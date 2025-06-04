# MesChain-Sync: Değişiklik Kaydı

Tüm önemli değişiklikler bu dosyada belgelenecektir.

## [1.0.0] - 2023-09-01

### Eklenen Özellikler
- Temel Trendyol API entegrasyonu
- Ürün, stok ve fiyat senkronizasyonu 
- Sipariş yönetimi ve OpenCart'a dönüştürme
- Kapsamlı raporlama sistemi
- Ürün eşleştirme arayüzü
- Türkçe ve İngilizce dil desteği

### Teknik Detaylar
- OpenCart 3.0+ uyumluluğu
- MVC mimarisi ile modüler tasarım
- Trendyol API v2 entegrasyonu
- Güvenli API istek/yanıt işleme
- Log sistemi ve hata ayıklama araçları

## [0.9.0] - 2023-08-15

### Eklenen Özellikler
- Raporlama sistemi eklendi
  - Satış raporları
  - Ürün performans raporları
  - Platform karşılaştırma raporları
- Grafik ve görselleştirme araçları
- CSV ve Excel dışa aktarma
- Türkçe dil desteği geliştirildi

### Düzeltilen Hatalar
- Ürün fiyatlarında ondalık sayı hatası
- Raporlar sayfasında veri eksikliği sorunu
- Zaman dilimi farklılıklarından kaynaklanan tarih hataları

## [0.8.0] - 2023-07-20

### Eklenen Özellikler
- Ürün eşleştirme sistemi tamamlandı
  - Kategori eşleştirme
  - Marka eşleştirme
  - Toplu eşleştirme aracı
- Ürün eşleştirme arayüzü iyileştirildi
- Eşleştirme kayıtları için loglar eklendi

### Düzeltilen Hatalar
- Ürün senkronizasyonundaki API hataları
- Stok güncelleme sırasında yaşanan bellek sorunları
- Kategori hiyerarşisi problemleri

## [0.7.0] - 2023-06-30

### Eklenen Özellikler
- Stok ve fiyat yönetimi iyileştirildi
  - Gerçek zamanlı stok güncelleme
  - Otomatik fiyat senkronizasyonu
  - Fiyat kuralları desteği
- Stok ve fiyat güncellemeleri için JSON API eklendi
- Stok/fiyat güncelleme logları eklendi

### Düzeltilen Hatalar
- Çoklu stok güncellemelerinde yaşanan çakışmalar
- Fiyat güncellemelerinde yaşanan gecikme sorunları

## [0.6.0] - 2023-06-15

### Eklenen Özellikler
- Sipariş yönetimi geliştirildi
  - Sipariş çekme
  - Sipariş detaylarını görüntüleme
  - OpenCart'a dönüştürme
  - Sipariş durumu güncelleme
- Sipariş filtreleme ve arama özellikleri
- Sipariş geçmişi ve logs

### Düzeltilen Hatalar
- Sipariş dönüştürme sırasında adres bilgilerinin kaybolması
- Sipariş notlarının doğru şekilde aktarılmaması
- Bazı durumlarda siparişlerin çekilememesi sorunu

## [0.5.0] - 2023-05-01

### Eklenen Özellikler
- Trendyol API entegrasyonu tamamlandı
- Temel ürün senkronizasyonu 
- Dashboard ve kontrol paneli
- Temel API ayarları
- API bağlantı testi

### Düzeltilen Hatalar
- API kimlik doğrulama sorunları
- Ürün bilgilerinin eksik aktarılması

## [0.1.0] - 2023-03-15

### İlk Sürüm
- Proje başlatıldı
- Temel dizin yapısı oluşturuldu
- API ve veritabanı tasarımı yapıldı

## [1.1.0] - 2024-01-21

### 🎉 Yenilikler
- **Ozon Marketplace Entegrasyonu** - Rusya'nın önde gelen e-ticaret platformu Ozon için tam entegrasyon
  - Dashboard görünümü ile anlık istatistikler
  - Ürün yönetimi (listeleme, güncelleme, stok/fiyat senkronizasyonu)
  - Sipariş yönetimi ve otomatik aktarım
  - API entegrasyon sınıfı (`EntegratorOzon`)
  - Türkçe dil desteği
  - Detaylı log kayıtları

### 📝 Dokümantasyon
- Kapsamlı analiz ve temizlik raporu oluşturuldu (`MesChain_Sync_Full_Analysis_Report.md`)
- `meschain_sync_full_audit.md` güncellendi - gerçek proje durumu yansıtıldı
- `TODO.md` güncellendi - yeni öncelikler ve temizlik görevleri eklendi

### 🐛 Tespit Edilen Sorunlar
- Tekrar eden dosyalar tespit edildi (trendyol/n11 için birden fazla controller)
- `.tpl` dosyaları hala mevcut (OpenCart 3.x `.twig` kullanıyor)
- Helper dosyaları yanlış dizinde
- Eksik dizinler: `logs/`, `admin_panel/`
- Dokümantasyon dosyaları birden fazla yerde tekrar ediyor

### ⚠️ Bilinen Sorunlar
- Trendyol login sonrası yönlendirme sorunu devam ediyor
- N11, Amazon, Hepsiburada modülleri için model katmanı eksik
- eBay modülü sadece dummy dosya içeriyor

## [1.0.5] - 2024-01-20

### Geliştirmeler
- Güvenlik iyileştirmeleri ve hata düzeltmeleri

### Dokümantasyon
- README dosyası güncellendi
- API dokümantasyonu iyileştirildi

## [1.0.4] - 2024-01-15

### Yenilikler
- N11 kategori yönetimi eklendi
- Dropshipping modülü temeli oluşturuldu

### Düzeltmeler
- Türkçe karakter sorunları giderildi
- Bellek kullanımı optimize edildi

## [1.0.3] - 2024-01-10

### Yenilikler
- Çoklu dil desteği altyapısı
- Kullanıcı yönetimi modülü

### Geliştirmeler
- Dashboard performans iyileştirmeleri
- API hata yönetimi geliştirildi

## [1.0.2] - 2024-01-05

### Yenilikler
- Amazon entegrasyonu başlangıç modülü
- Hepsiburada entegrasyonu başlangıç modülü

### Düzeltmeler
- Stok senkronizasyon hataları giderildi
- Sipariş aktarım sorunları çözüldü

## [1.0.1] - 2023-12-28

### Yenilikler
- N11 entegrasyonu eklendi
- Yardım paneli modülü
- Duyuru sistemi

### Düzeltmeler
- Trendyol API bağlantı sorunları giderildi
- Ürün eşleştirme hataları düzeltildi

## [1.0.0] - 2023-12-15

### İlk Sürüm
- Trendyol entegrasyonu
- Temel ürün yönetimi
- Sipariş yönetimi
- Dashboard ve raporlama
- OpenCart 3.x uyumluluğu

---

Not: Tarihler yaklaşık olup, gerçek geliştirme tarihlerini yansıtmayabilir. 