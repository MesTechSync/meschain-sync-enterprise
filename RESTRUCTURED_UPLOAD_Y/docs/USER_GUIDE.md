# MESCHAIN-SYNC ENTERPRISE KULLANICI KILAVUZU

**Versiyon:** 3.0.0
**Son Güncelleme:** 18 Haziran 2025
**Platform:** OpenCart 4.0.2.3

## İçindekiler

1. [Başlarken](#başlarken)
2. [Kontrol Paneli](#kontrol-paneli)
3. [Marketplace Yönetimi](#marketplace-yönetimi)
4. [Ürün Senkronizasyonu](#ürün-senkronizasyonu)
5. [Sipariş Yönetimi](#sipariş-yönetimi)
6. [Stok Takibi](#stok-takibi)
7. [Fiyat Yönetimi](#fiyat-yönetimi)
8. [Raporlama ve Analitik](#raporlama-ve-analitik)
9. [Ayarlar](#ayarlar)
10. [Sık Sorulan Sorular](#sık-sorulan-sorular)

---

## Başlarken

### Hoş Geldiniz!

MesChain-Sync Enterprise, OpenCart mağazanızı Türkiye'nin ve dünyanın önde gelen e-ticaret pazaryerleri ile entegre eden güçlü bir çözümdür. Bu kılavuz, sistemi en verimli şekilde kullanmanız için hazırlanmıştır.

### İlk Giriş

1. OpenCart admin panelinize giriş yapın
2. Sol menüde **MesChain Sync** menüsünü bulun
3. **Dashboard** seçeneğine tıklayın

![Dashboard Görünümü](images/dashboard.png)

### Hızlı Başlangıç Kontrol Listesi

- [ ] Marketplace API bilgilerinizi girin
- [ ] İlk ürün senkronizasyonunu başlatın
- [ ] Otomatik senkronizasyon ayarlarını yapın
- [ ] Stok takibi kurallarını belirleyin
- [ ] Fiyatlandırma stratejinizi seçin

---

## Kontrol Paneli

### Genel Bakış

Kontrol paneli, tüm marketplace operasyonlarınızın merkezi yönetim noktasıdır.

#### Ana Metrikler

1. **Toplam Ürün Sayısı**: Sistemdeki tüm ürünler
2. **Senkronize Ürünler**: Marketplace'lere gönderilen ürünler
3. **Bekleyen Siparişler**: İşlem bekleyen siparişler
4. **Günlük Satış**: Bugünkü toplam satış tutarı

#### Hızlı Eylemler

- **Tüm Ürünleri Senkronize Et**: Tek tıkla toplu senkronizasyon
- **Siparişleri İşle**: Bekleyen siparişleri toplu işleme
- **Stok Güncelle**: Anlık stok senkronizasyonu
- **Rapor Oluştur**: Hızlı rapor alma

### Gerçek Zamanlı İzleme

Dashboard'da şu bilgileri gerçek zamanlı olarak görebilirsiniz:

- Marketplace bağlantı durumları
- Son senkronizasyon zamanları
- API kullanım limitleri
- Sistem performans metrikleri

---

## Marketplace Yönetimi

### Desteklenen Marketplace'ler

1. **Trendyol**
   - Türkiye'nin en büyük e-ticaret platformu
   - Gelişmiş kampanya yönetimi
   - Hızlı kargo entegrasyonu

2. **Hepsiburada**
   - Geniş müşteri kitlesi
   - Premium satıcı programı
   - Detaylı raporlama

3. **Amazon**
   - Global erişim
   - FBA (Fulfillment by Amazon) desteği
   - Çoklu ülke desteği

4. **N11**
   - Güçlü kategori yapısı
   - Mağaza özelleştirme
   - Kampanya yönetimi

5. **eBay**
   - Uluslararası satış
   - Açık artırma desteği
   - Global ödeme sistemleri

### Marketplace Ekleme

1. **MesChain Sync → Marketplace Yönetimi**'ne gidin
2. **Yeni Marketplace Ekle** butonuna tıklayın
3. Marketplace'i seçin ve API bilgilerini girin:
   - API Anahtarı
   - Gizli Anahtar
   - Satıcı ID (varsa)
4. **Test Et** ile bağlantıyı kontrol edin
5. **Kaydet** ile marketplace'i aktif edin

### API Bilgileri Nereden Alınır?

#### Trendyol
1. Trendyol Satıcı Paneli'ne giriş yapın
2. Entegrasyonlar → API Entegrasyonu
3. API anahtarlarınızı kopyalayın

#### Hepsiburada
1. Hepsiburada Satıcı Paneli'ne giriş yapın
2. Ayarlar → API Yönetimi
3. Yeni API anahtarı oluşturun

---

## Ürün Senkronizasyonu

### Manuel Senkronizasyon

#### Tekil Ürün Senkronizasyonu
1. **Katalog → Ürünler**'e gidin
2. Ürünü düzenleyin
3. **MesChain Sync** sekmesini açın
4. Göndermek istediğiniz marketplace'leri seçin
5. **Senkronize Et** butonuna tıklayın

#### Toplu Ürün Senkronizasyonu
1. **MesChain Sync → Ürün Yönetimi**'ne gidin
2. Filtreleme seçeneklerini kullanın:
   - Kategori
   - Marka
   - Stok durumu
   - Fiyat aralığı
3. Ürünleri seçin
4. **Toplu İşlemler → Senkronize Et**

### Otomatik Senkronizasyon

#### Senkronizasyon Kuralları
1. **MesChain Sync → Ayarlar → Senkronizasyon**
2. Otomatik senkronizasyon kuralları:
   - **Yeni Ürünler**: Otomatik gönder
   - **Stok Değişimi**: Anında güncelle
   - **Fiyat Değişimi**: 5 dakika içinde güncelle
   - **Ürün Bilgileri**: Günlük güncelle

#### Kategori Eşleştirme

MesChain-Sync'in AI destekli kategori eşleştirme özelliği:

1. **Otomatik Eşleştirme** (%90+ doğruluk)
2. **Manuel Düzeltme** imkanı
3. **Öğrenen Sistem**: Her düzeltme sistemi geliştirir

### Senkronizasyon Durumları

- **Bekliyor**: Senkronizasyon kuyruğunda
- **İşleniyor**: Şu anda senkronize ediliyor
- **Başarılı**: Marketplace'e gönderildi
- **Hata**: Senkronizasyon başarısız (detaylar için tıklayın)

---

## Sipariş Yönetimi

### Sipariş Akışı

1. **Yeni Sipariş Alımı**
   - Marketplace'den otomatik çekilir
   - OpenCart siparişi oluşturulur
   - Bildirim gönderilir

2. **Sipariş İşleme**
   - Stok kontrolü yapılır
   - Ödeme doğrulanır
   - Kargo için hazırlanır

3. **Kargo Süreci**
   - Kargo barkodu oluşturulur
   - Marketplace'e bildirilir
   - Müşteriye takip bilgisi gönderilir

### Sipariş Yönetim Ekranı

**MesChain Sync → Sipariş Yönetimi**

#### Filtreler
- Marketplace
- Sipariş durumu
- Tarih aralığı
- Müşteri bilgisi
- Ürün

#### Toplu İşlemler
- Kargo barkodu oluştur
- Fatura kes
- Sipariş durumu güncelle
- İptal/İade işlemleri

### Otomatik Sipariş İşleme

1. **Ayarlar → Sipariş Otomasyonu**
2. Kurallar belirleyin:
   - Otomatik onay kriterleri
   - Stok rezervasyon kuralları
   - Kargo şirketi seçimi
   - Fatura oluşturma

---

## Stok Takibi

### Stok Senkronizasyonu

#### Gerçek Zamanlı Stok Güncelleme
- Her satışta otomatik güncelleme
- Çoklu depo desteği
- Marketplace bazlı stok yönetimi

#### Stok Rezervasyon Sistemi
1. **Güvenlik Stoku**: Her marketplace için ayrı
2. **Öncelik Sırası**: Hangi marketplace öncelikli
3. **Stok Dağıtımı**: Otomatik veya manuel

### Stok Uyarıları

- **Kritik Stok**: Belirlenen seviyenin altına düştüğünde
- **Stoksuz Ürün**: Otomatik marketplace'den çekme
- **Fazla Stok**: Kampanya önerileri

### Stok Raporları

1. **Stok Durum Raporu**
2. **Stok Hareket Raporu**
3. **Marketplace Bazlı Stok Dağılımı**
4. **Stok Değeri Raporu**

---

## Fiyat Yönetimi

### Dinamik Fiyatlandırma

#### AI Destekli Fiyat Optimizasyonu

1. **Rekabet Analizi**
   - Rakip fiyatları takibi
   - Otomatik fiyat ayarlama
   - Kar marjı koruması

2. **Talep Bazlı Fiyatlandırma**
   - Sezonsal değişimler
   - Stok durumuna göre
   - Satış hızına göre

3. **Kampanya Yönetimi**
   - Marketplace kampanyalarına katılım
   - Özel indirimler
   - Paket fiyatlandırma

### Fiyat Kuralları

**MesChain Sync → Fiyat Yönetimi → Kurallar**

Örnek Kurallar:
- "Trendyol fiyatı = Mağaza fiyatı + %10"
- "Stok < 10 ise fiyatı %5 artır"
- "Hafta sonu %15 indirim"

### Fiyat Geçmişi

Her ürün için:
- Fiyat değişim grafiği
- Marketplace bazlı fiyat karşılaştırması
- Kar marjı analizi

---

## Raporlama ve Analitik

### Dashboard Raporları

#### Satış Performansı
- Günlük/Haftalık/Aylık satışlar
- Marketplace bazlı dağılım
- En çok satan ürünler
- Kategori performansı

#### Finansal Raporlar
- Gelir analizi
- Komisyon hesaplaması
- Kar-zarar durumu
- KDV raporu

#### Operasyonel Raporlar
- Senkronizasyon başarı oranı
- API kullanım istatistikleri
- Hata logları
- Performans metrikleri

### Özel Raporlar

1. **Rapor Oluşturucu**'yu kullanarak:
   - Metrikleri seçin
   - Filtreleri belirleyin
   - Görselleştirme tipini seçin
   - Raporu kaydedin/dışa aktarın

2. **Zamanlanmış Raporlar**:
   - Günlük özet e-postası
   - Haftalık performans raporu
   - Aylık finansal özet

### Veri Dışa Aktarma

Desteklenen formatlar:
- Excel (XLSX)
- CSV
- PDF
- JSON

---

## Ayarlar

### Genel Ayarlar

#### Sistem Ayarları
- Dil seçimi
- Zaman dilimi
- Para birimi
- Vergi ayarları

#### Bildirim Ayarları
- E-posta bildirimleri
- SMS bildirimleri (opsiyonel)
- Dashboard bildirimleri
- Webhook entegrasyonları

### Marketplace Özel Ayarları

Her marketplace için:
- Komisyon oranları
- Kargo şirketleri
- İade politikaları
- Özel alanlar

### Güvenlik Ayarları

- API erişim kontrolü
- IP kısıtlamaları
- İki faktörlü doğrulama
- Oturum yönetimi

### Yedekleme ve Geri Yükleme

1. **Otomatik Yedekleme**
   - Günlük yapılandırma yedekleri
   - Haftalık tam yedekleme

2. **Manuel Yedekleme**
   - Ayarları dışa aktar
   - Veritabanı yedeği al

---

## Sık Sorulan Sorular

### Genel Sorular

**S: Kaç marketplace'e aynı anda ürün gönderebilirim?**
C: Sınırsız sayıda marketplace'e aynı anda ürün gönderebilirsiniz.

**S: Ürün bilgileri otomatik güncellenir mi?**
C: Evet, belirlediğiniz kurallara göre otomatik güncelleme yapılır.

**S: Farklı marketplace'lerde farklı fiyat belirleyebilir miyim?**
C: Evet, her marketplace için ayrı fiyatlandırma kuralları tanımlayabilirsiniz.

### Teknik Sorular

**S: API limitleri aşılırsa ne olur?**
C: Sistem otomatik olarak bekler ve limit yenilendikten sonra devam eder.

**S: Senkronizasyon hataları nasıl çözülür?**
C: Hata detaylarına tıklayarak çözüm önerilerini görebilirsiniz.

**S: Sistem performansı nasıl iyileştirilir?**
C: Ayarlar → Performans bölümünden önbellek ve toplu işlem ayarlarını optimize edin.

### Sorun Giderme

**Marketplace bağlantı hatası:**
1. API bilgilerini kontrol edin
2. IP adresinizin marketplace tarafından onaylandığından emin olun
3. SSL sertifikanızın geçerli olduğunu kontrol edin

**Ürünler senkronize olmuyor:**
1. Ürün bilgilerinin eksiksiz olduğunu kontrol edin
2. Kategori eşleştirmelerini gözden geçirin
3. Marketplace özel gereksinimlerini kontrol edin

**Siparişler gelmiyor:**
1. Webhook URL'lerinin doğru tanımlandığını kontrol edin
2. Cron job'ların çalıştığından emin olun
3. API erişim izinlerini kontrol edin

---

## Destek

### İletişim Kanalları

- **E-posta**: support@meschain.com
- **Telefon**: +90 212 123 45 67
- **WhatsApp**: +90 555 123 45 67
- **Canlı Destek**: Panel içinden 7/24

### Kaynaklar

- [Video Eğitimler](https://meschain.com/egitim)
- [API Dokümantasyonu](https://docs.meschain.com)
- [Topluluk Forumu](https://forum.meschain.com)
- [Blog](https://blog.meschain.com)

### Güncelleme Bildirimleri

Yeni özellikler ve güncellemelerden haberdar olmak için:
- E-posta listesine kayıt olun
- Sosyal medya hesaplarımızı takip edin
- Panel içi bildirimlerini aktif tutun

---

**MesChain-Sync Enterprise** - E-ticaret entegrasyonunda lider çözüm

*Bu kılavuz düzenli olarak güncellenmektedir. Son versiyon için [docs.meschain.com](https://docs.meschain.com) adresini ziyaret edin.*
