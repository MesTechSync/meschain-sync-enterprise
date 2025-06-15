# 📦 MesChain-Sync: Ozon Pazaryeri Entegrasyonu

Bu modül, OpenCart e-ticaret platformu için Ozon pazaryeri entegrasyonu sağlar. Rusya ve BDT (Bağımsız Devletler Topluluğu) pazarında lider olan Ozon ile OpenCart ürünlerinizi, stoklarınızı ve siparişlerinizi senkronize etmenizi sağlar.

## 🎯 Özellikler

- Ürün senkronizasyonu (OpenCart → Ozon)
- Sipariş senkronizasyonu (Ozon → OpenCart)
- Stok ve fiyat yönetimi
- Kategori eşleştirme
- Otomatik senkronizasyon
- Detaylı raporlama ve istatistikler
- Kapsamlı log yönetimi

## 📋 Gereksinimler

- OpenCart 3.x
- PHP 7.3 veya üzeri
- cURL etkin
- Ozon Satıcı API erişimi (Client ID ve API Key)

## 🔧 Kurulum

1. MesChain-Sync modülünü OpenCart mağazanıza yükleyin
2. Ozon entegrasyonunu etkinleştirin
3. API bilgilerinizi girin (Client ID, API Key ve API Secret)
4. Kategori eşleştirmelerini yapın
5. Ürün senkronizasyonunu başlatın

## ⚙️ Ayarlar

### API Ayarları

- **API Anahtarı:** Ozon API anahtarınız
- **API Secret:** Ozon API secret anahtarınız
- **Client ID:** Ozon Satıcı ID'niz
- **API URL:** Ozon API URL'si (genellikle https://api-seller.ozon.ru)

### Genel Ayarlar

- **Durum:** Modülün genel durumu
- **Otomatik Senkronizasyon:** Otomatik senkronizasyon durumu
- **Senkronizasyon Sıklığı:** Otomatik senkronizasyon aralığı (dakika)
- **Ürün Senkronizasyon Yönü:** Tek yönlü veya çift yönlü
- **Sipariş Senkronizasyon Yönü:** Tek yönlü veya çift yönlü

## 🔄 Senkronizasyon

### Ürün Senkronizasyonu

Ürün senkronizasyonu, OpenCart ürünlerinizi Ozon'a aktarır. Bu işlem sırasında şu veriler senkronize edilir:

- Ürün adı ve açıklaması
- Fiyat ve stok bilgileri
- Ürün resimleri
- Ürün özellikleri
- Kategori bilgisi
- Ürün boyutları ve ağırlığı

### Sipariş Senkronizasyonu

Sipariş senkronizasyonu, Ozon'daki siparişleri OpenCart'a aktarır. Bu işlem sırasında şu veriler senkronize edilir:

- Sipariş bilgileri
- Müşteri bilgileri
- Sipariş ürünleri
- Sipariş durumu
- Ödeme ve kargo bilgileri

## 📊 Dashboard

Dashboard sayfasında aşağıdaki bilgileri görüntüleyebilirsiniz:

- API bağlantı durumu
- Toplam ürün sayısı
- Toplam sipariş sayısı
- Bekleyen sipariş sayısı
- Son siparişler
- Ürün ve sipariş istatistikleri grafikleri

## 📝 Loglar

Log sayfasında, tüm API istekleri, yanıtlar ve hata mesajları kaydedilir. Bu loglar, sorun giderme ve sistem durumunu izleme için kullanılır.

Log türleri:
- Bilgi (Info)
- Uyarı (Warning)
- Hata (Error)

## 🔗 API Referansları

- [Ozon API Dokümantasyonu](https://docs.ozon.ru/api/seller)

## ❓ Sorun Giderme

Yaygın sorunlar ve çözümleri:

1. **API Bağlantı Hatası**
   - API bilgilerinizi kontrol edin
   - API URL'nin doğru olduğundan emin olun
   - Ozon hesabınızın API erişimine sahip olduğunu doğrulayın

2. **Ürün Senkronizasyon Sorunları**
   - Ürün özelliklerinin doğru eşleştirildiğinden emin olun
   - Kategori eşleştirmelerini kontrol edin
   - Log dosyalarında hata mesajlarını inceleyin

3. **Sipariş Senkronizasyon Sorunları**
   - OpenCart sipariş durumu eşleştirmelerini kontrol edin
   - Ozon hesabınızdaki sipariş durumlarını doğrulayın
   - Log dosyalarında hata mesajlarını inceleyin

## 📞 Destek

Yardıma ihtiyacınız olursa:

- MesChain-Sync dokümantasyonunu inceleyin
- Log dosyalarını kontrol edin
- Destek ekibiyle iletişime geçin: support@meschain-sync.com 