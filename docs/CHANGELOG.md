# 📋 MesChain-Sync: Sürüm Geçmişi

Bu dosya, MesChain-Sync projesinde yapılan tüm önemli değişiklikleri kaydeder.

Format, [Keep a Changelog](https://keepachangelog.com/tr/1.0.0/) esaslarına dayanmaktadır.

## [1.0.0] - Henüz Yayınlanmadı (Geliştirme Aşamasında)

### Eklenenler

- Temel sistem altyapısı
- Ana modül çerçevesi
- Trendyol entegrasyonu
- N11 entegrasyonu
- Hepsiburada entegrasyonu
- Amazon entegrasyonu
- eBay entegrasyonu
- Ozon entegrasyonu (kısmen)
- Merkezi loglama sistemi
- Temel dokümantasyon
- Kapsamlı hata yakalama

### Değiştirilenler

- Tüm API sınıfları modüler yapıya dönüştürüldü
- Kategori eşleştirme sistemi iyileştirildi
- Ürün senkronizasyon algoritması optimize edildi

### Düzeltilenler

- Ürün varyasyon işlemlerindeki sorunlar giderildi
- Sipariş entegrasyonundaki gecikme sorunları çözüldü
- Çoklu dil desteğinde karakter kodlama sorunları düzeltildi

### Güvenlik

- API anahtarları şifreleme ile saklanıyor
- Token tabanlı API istekleri uygulandı
- IP bazlı erişim kontrolü eklendi

## [Unreleased] - 2023-11-20

### Eklenenler

- N11 entegrasyonu geliştirilmeye başlandı
- N11 modülü için kapsamlı dil dosyaları eklendi (İngilizce ve Türkçe)
- N11 dashboard sayfası oluşturuldu
- API test fonksiyonları ve bağlantı kontrolü eklendi
- N11 için ürün senkronizasyon fonksiyonları eklendi
- Sipariş çekme, stok ve fiyat güncelleme fonksiyonları eklendi
- İstatistik ve aktivite izleme sistemi eklendi
- Grafiksel göstergeler ve veri görselleştirme eklendi

### Değiştirilenler

- N11 modülü arayüzü tamamen yenilendi
- API ayarları sayfası daha kullanıcı dostu hale getirildi
- Meschain_sync_todo_plan.md güncellendi

### Düzeltilenler

- N11 controller'daki yönlendirme hataları giderildi
- API bağlantı ve format sorunları giderildi

## [Unreleased] - 2023-11-19

### Eklenenler

- Kapsamlı Yardım modülü geliştirildi
- Yardım içeriği için kategori ve konu yapısı oluşturuldu
- Arama ve iletişim formları eklendi
- Kurulum, API ayarları ve sorun giderme rehberleri eklendi
- PROJECT_OVERVIEW.md dosyası oluşturuldu
- STRUCTURE.md dosyası oluşturuldu

### Değiştirilenler

- Yardım modülü arayüzü tamamen yenilendi
- Yardım içeriği interaktif ve kategorili hale getirildi
- Meschain_sync_todo_plan.md güncellendi

### Düzeltilenler

- Yardım modülü eksik içerik sorunu giderildi
- Meschain_sync_todo_plan.md'deki tamamlanan görevler işaretlendi

## [Unreleased] - 2023-11-18

### Eklenenler

- Amazon Helper sınıfına gerçek API entegrasyonu
- Amazon için dashboard görünümü
- Amazon Selling Partner API bağlantısı
- Sipariş, ürün, stok, fiyat senkronizasyon fonksiyonları
- API test bağlantı fonksiyonu

### Değiştirilenler

- Amazon modülünde API Key, Secret, Token, Seller ID, Marketplace ID ve Region alanları eklendi
- Controller ve view dosyaları arasındaki bağlantı iyileştirildi

### Düzeltilenler

- Amazon controller'da yönlendirme hataları giderildi
- API bağlantı ve format sorunları giderildi

## [Unreleased] - 2023-11-17

### Eklenenler

- Kapsamlı dokümantasyon dosyaları (docs/ dizininde)
- Ozon entegrasyonu için controller ve API sınıfları
- Trendyol Helper sınıfına gerçek API entegrasyonu
- Stok ve fiyat güncelleme fonksiyonları
- Sipariş durumu güncelleme fonksiyonları

### Değiştirilenler

- Trendyol paneli yönlendirme sorunları düzeltildi
- API fonksiyonları gerçek endpoint'lere bağlandı

### Düzeltilenler

- Trendyol modülünde yönlendirme hataları giderildi
- API bağlantı ve veri format sorunları giderildi

## [0.9.5] - 2023-10-15

### Eklenenler

- Trendyol tam entegrasyonu
- N11 tam entegrasyonu
- Hepsiburada kısmi entegrasyonu
- Temel dashboard
- İstatistik ve raporlama
- Sipariş yönetimi arayüzü

### Değiştirilenler

- OpenCart admin panel entegrasyonu iyileştirildi
- Veritabanı yapısı optimize edildi

### Düzeltilenler

- Kurulum sürecindeki sorunlar giderildi
- Birden fazla mağaza desteği için düzeltmeler yapıldı

## [0.9.0] - 2023-08-20

### Eklenenler

- Proje çerçevesi oluşturuldu
- Temel API bağlantı altyapısı
- Trendyol entegrasyonu (kısmen)
- Admin panel arayüzü
- Temel yapılandırma seçenekleri

### Değiştirilenler

- OpenCart OCMOD uyumluluğu iyileştirildi

### Düzeltilenler

- OpenCart 3.x uyumluluk sorunları giderildi 