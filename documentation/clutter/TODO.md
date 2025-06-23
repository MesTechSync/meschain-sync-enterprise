# MesChain-Sync: Yapılacaklar Listesi

Bu dosya, MesChain-Sync çoklu pazaryeri entegrasyon modülü için yapılacak geliştirmeleri ve iyileştirmeleri içerir.

**Son Güncelleme:** 2024-01-21

## 🚨 ACİL - Kod Temizliği ve Reorganizasyon

### Dosya Temizliği (1-2 gün)
- [ ] `trendyol_enhanced.php` silinecek (trendyol.php kullanılacak)
- [ ] `n11_enhanced.php` ve `n11_optimized.php` silinecek (n11.php kullanılacak)
- [ ] `dropshipping_manager.php` silinecek (dropshipping.php kullanılacak)
- [ ] Tüm `.tpl` dosyaları silinecek (OpenCart 3.x `.twig` kullanır)
- [ ] Modül bazlı `CHANGELOG_*.md`, `VERSIYON_*.md`, `LOG_README_*.md` dosyaları silinecek
- [ ] `db_oracle.php`, `db_blockchain.php`, `db_sqlite.php` silinecek
- [ ] Dummy/boş dosyalar temizlenecek (ebay.php 547B, dashboard.php 525B vb.)
- [ ] Controller dizinindeki `.twig` dosyaları view dizinine taşınacak

### Dizin Yapısı Düzeltmeleri (3-5 gün)
- [ ] `logs/` dizini oluşturulacak
- [ ] Helper dosyaları `system/library/meschain/helper/` dizinine taşınacak
- [ ] CSS dosyaları `admin/view/stylesheet/` dizinine taşınacak
- [ ] Dokümantasyon dosyaları tek bir `docs/` dizininde toplanacak
- [ ] Tekrar eden dokümantasyon dosyaları birleştirilecek

## ✅ Tamamlananlar

### Ozon Modülü (TAMAMLANDI)
- [x] Controller (ControllerExtensionModuleOzon)
- [x] Model (ModelExtensionModuleOzon)
- [x] View dosyaları (dashboard, settings, products, orders, logs)
- [x] API entegrasyon sınıfı (EntegratorOzon)
- [x] Türkçe dil dosyası

### Dokümantasyon
- [x] PROJECT_OVERVIEW.md
- [x] STRUCTURE.md
- [x] TECH_STACK.md
- [x] MODULE_GUIDE.md
- [x] AI_PROMPT_GUIDE.md

## 📋 Öncelikli Görevler

### Trendyol İyileştirmeleri
- [ ] Login sonrası yönlendirme sorunu çözülecek
- [ ] TrendyolHelper.php içeriği doldurulacak
- [ ] API error handling geliştirilecek
- [ ] Toplu ürün yükleme optimize edilecek

### Model Katmanı
- [ ] N11 model dosyası (`model/extension/module/n11.php`) oluşturulacak
- [ ] Hepsiburada model dosyası oluşturulacak
- [ ] Amazon model dosyası oluşturulacak
- [ ] eBay model dosyası oluşturulacak

### Dil Desteği
- [ ] Tüm modüller için `en-gb` dil dosyaları eklenecek
- [ ] Trendyol dil dosyası oluşturulacak
- [ ] Amazon dil dosyası oluşturulacak
- [ ] eBay dil dosyası oluşturulacak
- [ ] Hepsiburada dil dosyası oluşturulacak

## 🔧 Orta Vadeli Görevler

### Modül Tamamlama
- [ ] eBay modülü sıfırdan yazılacak
- [ ] Amazon entegrasyonu tamamlanacak
- [ ] N11 backend implementasyonu tamamlanacak
- [ ] Hepsiburada backend implementasyonu tamamlanacak

### Sistem İyileştirmeleri
- [ ] Merkezi log sistemi kurulacak
- [ ] API rate limiting implementasyonu
- [ ] Webhook desteği eklenecek
- [ ] Cron job yönetimi geliştirilecek

### Arayüz İyileştirmeleri
- [ ] Responsive tasarım iyileştirmeleri
- [ ] Dark mode desteği
- [ ] Gerçek zamanlı bildirimler
- [ ] İlerleme çubukları ve loading animasyonları

## 🚀 Uzun Vadeli Görevler

### Gelişmiş Özellikler
- [ ] Dropshipping entegrasyonu tamamlanacak
- [ ] Multi-tenant mimari implementasyonu
- [ ] Yapay zeka destekli ürün eşleştirme
- [ ] Otomatik fiyatlandırma algoritması
- [ ] Satış tahminleme ve trend analizi

### Yeni Pazaryeri Entegrasyonları
- [ ] Çiçeksepeti entegrasyonu
- [ ] Morhipo entegrasyonu
- [ ] Gittigidiyor entegrasyonu
- [ ] AliExpress entegrasyonu

### Altyapı Geliştirmeleri
- [ ] Unit test coverage %60'a çıkarılacak
- [ ] Integration testleri eklenecek
- [ ] CI/CD pipeline kurulacak
- [ ] Docker desteği eklenecek

## 🐛 Bilinen Hatalar

### Kritik
- [ ] Trendyol login sonrası OpenCart dashboard'a yönlendiriyor
- [ ] Büyük veri setlerinde bellek tüketimi sorunu
- [ ] API bağlantı kesilmelerinde recovery mekanizması yok

### Orta
- [ ] Türkçe karakter sorunları bazı modüllerde devam ediyor
- [ ] Sipariş dönüştürme sırasında adres bilgileri eksik kalabiliyor
- [ ] Stok senkronizasyonunda gecikme yaşanıyor

### Düşük
- [ ] Dashboard grafiklerinde mobilde görüntüleme sorunu
- [ ] Bazı tooltip'ler doğru görünmüyor
- [ ] Pagination bazı listelerde çalışmıyor

---

**Not:** Bu liste canlı bir dokümandır ve proje ilerledikçe sürekli güncellenmektedir. Öncelikler, kullanıcı geri bildirimleri ve iş gereksinimleri doğrultusunda değişebilir. 