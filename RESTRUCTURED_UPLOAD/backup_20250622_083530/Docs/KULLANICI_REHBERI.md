# MesChain Trendyol Entegrasyonu - Kullanıcı Rehberi

## İçindekiler

1. [Giriş](#giriş)
2. [İlk Kurulum Sonrası](#ilk-kurulum-sonrası)
3. [Ürün Yönetimi](#ürün-yönetimi)
4. [Sipariş Yönetimi](#sipariş-yönetimi)
5. [Stok Yönetimi](#stok-yönetimi)
6. [Fiyat Yönetimi](#fiyat-yönetimi)
7. [Kategori Yönetimi](#kategori-yönetimi)
8. [Raporlama](#raporlama)
9. [İzleme ve Kontrol](#izleme-ve-kontrol)
10. [Sorun Giderme](#sorun-giderme)
11. [En İyi Uygulamalar](#en-iyi-uygulamalar)

## Giriş

Bu rehber, MesChain Trendyol Entegrasyonu'nun günlük kullanımı için hazırlanmıştır. Entegrasyonun tüm özelliklerini etkin bir şekilde kullanmanız için adım adım talimatlar içerir.

### Temel Kavramlar

- **Senkronizasyon**: OpenCart ve Trendyol arasında veri alışverişi
- **Batch İşlem**: Toplu veri işleme
- **API Limiti**: Trendyol'un belirlediği istek sınırları
- **Mapping**: Kategori ve özellik eşleştirmesi

## İlk Kurulum Sonrası

### 1. Dashboard'a Erişim

OpenCart admin paneline giriş yapın:

1. **Extensions** > **Extensions** > **Modules** bölümüne gidin
2. **Trendyol Integration** modülünü bulun
3. **Edit** butonuna tıklayın

### 2. İlk Yapılandırma Kontrolü

**Status Kontrolü**:
```
✅ Module Status: Enabled
✅ API Connection: Active
✅ Sync Status: Running
✅ Last Sync: [Tarih ve saat]
```

### 3. Test Bağlantısı

**Test Connection** butonuna tıklayarak bağlantıyı doğrulayın:
- ✅ **Başarılı**: "Connection successful" mesajı
- ❌ **Başarısız**: Hata mesajını kontrol edin ve API bilgilerini doğrulayın

## Ürün Yönetimi

### 1. Ürün Ekleme

#### OpenCart'tan Trendyol'a Ürün Gönderme

1. **Catalog** > **Products** bölümüne gidin
2. **Add New** butonuna tıklayın
3. Ürün bilgilerini doldurun:

**Gerekli Alanlar**:
```
Product Name: [Ürün adı - Türkçe]
Description: [Detaylı açıklama]
Meta Title: [SEO başlığı]
Model: [Ürün kodu/SKU]
Price: [Satış fiyatı]
Quantity: [Stok miktarı]
Status: Enabled
```

**Trendyol Özel Alanları**:
```
Trendyol Category: [Kategori seçin]
Brand: [Marka seçin]
Barcode: [Barkod numarası]
Product Code: [Ürün kodu]
```

4. **Trendyol** sekmesine gidin
5. **Send to Trendyol** kutusunu işaretleyin
6. **Save** butonuna tıklayın

#### Toplu Ürün Gönderimi

```bash
# Admin panelinden
Trendyol Integration > Bulk Operations > Send Products

# CLI'dan
php admin/cli/sync_products.php --action=send --limit=100
```

### 2. Ürün Güncelleme

#### Tekil Ürün Güncelleme

1. **Catalog** > **Products** bölümüne gidin
2. Güncellemek istediğiniz ürünü bulun
3. **Edit** butonuna tıklayın
4. Gerekli değişiklikleri yapın
5. **Trendyol** sekmesinde **Update on Trendyol** kutusunu işaretleyin
6. **Save** butonuna tıklayın

#### Toplu Ürün Güncelleme

```bash
# Fiyat güncelleme
php admin/cli/sync_prices.php

# Stok güncelleme
php admin/cli/sync_stock.php

# Tüm ürün bilgileri güncelleme
php admin/cli/sync_products.php --action=update
```

### 3. Ürün Durumu Takibi

**Ürün Durumları**:
- 🟢 **Active**: Trendyol'da aktif
- 🟡 **Pending**: Onay bekliyor
- 🔴 **Rejected**: Reddedildi
- ⚫ **Inactive**: Pasif

**Durum Kontrolü**:
```bash
# Ürün durumlarını kontrol edin
Trendyol Integration > Product Status > View All

# CLI'dan kontrol
php admin/cli/check_product_status.php
```

## Sipariş Yönetimi

### 1. Sipariş Alma

#### Otomatik Sipariş Alma

Sistem varsayılan olarak her 5 dakikada bir siparişleri kontrol eder:

```bash
# Cron job kontrolü
crontab -l | grep sync_orders

# Manuel sipariş alma
php admin/cli/sync_orders.php
```

#### Manuel Sipariş Alma

1. **Trendyol Integration** > **Orders** bölümüne gidin
2. **Fetch New Orders** butonuna tıklayın
3. Yeni siparişler otomatik olarak sisteme aktarılır

### 2. Sipariş İşleme

#### Sipariş Durumları

| Trendyol Durumu | OpenCart Durumu | Açıklama |
|-----------------|-----------------|----------|
| Created | Pending | Yeni sipariş |
| Processing | Processing | İşleniyor |
| Shipped | Shipped | Kargoya verildi |
| Delivered | Complete | Teslim edildi |
| Cancelled | Cancelled | İptal edildi |

#### Sipariş Onaylama

1. **Sales** > **Orders** bölümüne gidin
2. Trendyol siparişini bulun (Order ID'de "TY-" ön eki)
3. **View** butonuna tıklayın
4. **Order Status** kısmından durumu güncelleyin:
   - **Processing**: Siparişi onaylamak için
   - **Shipped**: Kargoya vermek için

### 3. Kargo İşlemleri

#### Kargo Bilgisi Gönderme

1. Sipariş detaylarına gidin
2. **Shipping** sekmesine tıklayın
3. Kargo bilgilerini doldurun:
```
Tracking Number: [Kargo takip numarası]
Shipping Company: [Kargo firması]
Shipping Date: [Kargo tarihi]
```
4. **Update Trendyol** butonuna tıklayın

#### Toplu Kargo Güncelleme

```bash
# CSV dosyasından kargo bilgilerini yükleyin
php admin/cli/bulk_shipping_update.php --file=shipping_data.csv

# Kargo durumlarını senkronize edin
php admin/cli/sync_shipping_status.php
```

## Stok Yönetimi

### 1. Stok Senkronizasyonu

#### Otomatik Stok Senkronizasyonu

Sistem her 15 dakikada bir stokları senkronize eder:

```bash
# Stok senkronizasyon durumu
Trendyol Integration > Stock Management > Sync Status

# Manuel stok senkronizasyonu
php admin/cli/sync_stock.php
```

#### Stok Uyarıları

**Düşük Stok Uyarısı**:
- Stok 10'un altına düştüğünde otomatik uyarı
- E-posta ve dashboard bildirimi

**Stok Tükendi Uyarısı**:
- Stok 0 olduğunda ürün otomatik pasifleşir
- Trendyol'a "out of stock" durumu gönderilir

### 2. Stok Ayarları

#### Güvenlik Stoğu

```bash
# Güvenlik stoğu ayarlayın
Trendyol Integration > Stock Settings > Safety Stock = 5

# Ürün bazında güvenlik stoğu
Product Edit > Trendyol Tab > Safety Stock = [Miktar]
```

#### Stok Rezervasyonu

```bash
# Sipariş alındığında otomatik rezervasyon
Trendyol Integration > Order Settings > Auto Reserve Stock = Yes

# Rezervasyon süresi
Stock Reservation Timeout = 24 hours
```

## Fiyat Yönetimi

### 1. Fiyat Senkronizasyonu

#### Otomatik Fiyat Güncelleme

```bash
# Saatlik fiyat senkronizasyonu
Trendyol Integration > Price Settings > Auto Sync = Enabled

# Manuel fiyat güncelleme
php admin/cli/sync_prices.php
```

#### Fiyat Kuralları

**Kar Marjı Ayarı**:
```bash
# Genel kar marjı
Trendyol Integration > Price Settings > Profit Margin = 15%

# Kategori bazında kar marjı
Category Management > [Kategori] > Trendyol Margin = 20%
```

**Kampanya Fiyatları**:
```bash
# İndirimli fiyat gönderme
Product Edit > Trendyol Tab >
- List Price: 100 TL
- Sale Price: 85 TL
- Campaign Start: [Başlangıç tarihi]
- Campaign End: [Bitiş tarihi]
```

### 2. Fiyat İzleme

#### Rakip Fiyat Analizi

```bash
# Rakip fiyatlarını kontrol edin
Trendyol Integration > Price Analysis > Competitor Prices

# Fiyat önerisi alın
Price Suggestions > Auto Calculate Optimal Price
```

#### Fiyat Geçmişi

```bash
# Fiyat değişiklik geçmişi
Product Edit > Trendyol Tab > Price History

# Fiyat performans raporu
Reports > Trendyol > Price Performance
```

## Kategori Yönetimi

### 1. Kategori Eşleştirme

#### Otomatik Eşleştirme

```bash
# Otomatik kategori eşleştirme
Trendyol Integration > Category Mapping > Auto Map Categories

# Eşleştirme doğruluğu: %85-95
```

#### Manuel Eşleştirme

1. **Trendyol Integration** > **Category Mapping** bölümüne gidin
2. Eşleşmeyen kategorileri bulun
3. **Map Category** butonuna tıklayın
4. Uygun Trendyol kategorisini seçin
5. **Save Mapping** butonuna tıklayın

### 2. Kategori Özellikleri

#### Zorunlu Özellikler

Her kategori için zorunlu özellikleri tanımlayın:

```bash
Category: Elektronik > Telefon
Required Attributes:
- Marka: [Zorunlu]
- Model: [Zorunlu]
- Renk: [Zorunlu]
- Hafıza: [Zorunlu]
- İşletim Sistemi: [Zorunlu]
```

#### Özellik Eşleştirme

```bash
# OpenCart özelliklerini Trendyol özellikleriyle eşleştirin
Trendyol Integration > Attribute Mapping
- OpenCart: "Color" → Trendyol: "Renk"
- OpenCart: "Size" → Trendyol: "Beden"
- OpenCart: "Brand" → Trendyol: "Marka"
```

## Raporlama

### 1. Satış Raporları

#### Günlük Satış Raporu

```bash
# Dashboard'dan erişim
Trendyol Integration > Reports > Daily Sales

# Rapor içeriği:
- Toplam sipariş sayısı
- Toplam satış tutarı
- En çok satan ürünler
- Kategori bazında satışlar
```

#### Aylık Performans Raporu

```bash
# Aylık rapor oluşturma
Reports > Trendyol > Monthly Performance

# Rapor detayları:
- Aylık satış trendi
- Kar marjı analizi
- İade oranları
- Müşteri memnuniyeti
```

### 2. Stok Raporları

#### Stok Durum Raporu

```bash
# Mevcut stok durumu
Reports > Trendyol > Stock Status

# Rapor içeriği:
- Toplam ürün sayısı
- Stokta olan ürünler
- Stoku tükenen ürünler
- Düşük stoklu ürünler
```

#### Stok Hareket Raporu

```bash
# Stok giriş-çıkış raporu
Reports > Trendyol > Stock Movement

# Detaylar:
- Günlük stok hareketleri
- En çok satılan ürünler
- Yavaş hareket eden ürünler
```

### 3. Özel Raporlar

#### Performans Analizi

```bash
# Ürün performans analizi
php admin/cli/generate_performance_report.php

# Kategori performans analizi
php admin/cli/generate_category_report.php

# Kar marjı analizi
php admin/cli/generate_profit_report.php
```

## İzleme ve Kontrol

### 1. Dashboard İzleme

#### Ana Dashboard

**Trendyol Integration Dashboard** üzerinden:

```bash
# Gerçek zamanlı veriler
- Aktif ürün sayısı: 1,250
- Günlük sipariş: 45
- Stok uyarıları: 12
- API durumu: ✅ Aktif
```

#### Sistem Sağlığı

```bash
# Sistem durumu kontrolleri
- API Response Time: 250ms
- Sync Success Rate: 98.5%
- Error Rate: 1.2%
- Uptime: 99.8%
```

### 2. Uyarı Sistemi

#### E-posta Uyarıları

```bash
# Uyarı türleri:
- API bağlantı hatası
- Senkronizasyon başarısızlığı
- Düşük stok uyarısı
- Sipariş alma hatası
- Sistem performans uyarısı
```

#### Slack Entegrasyonu

```bash
# Slack kanalına otomatik bildirimler
- Yeni sipariş bildirimi
- Hata uyarıları
- Günlük özet raporları
- Sistem durumu güncellemeleri
```

### 3. Log İzleme

#### Log Dosyaları

```bash
# Ana log dosyaları
tail -f system/storage/logs/trendyol.log      # Ana işlemler
tail -f system/storage/logs/api.log           # API istekleri
tail -f system/storage/logs/sync.log          # Senkronizasyon
tail -f system/storage/logs/error.log         # Hatalar
```

#### Log Analizi

```bash
# Hata analizi
php admin/cli/analyze_logs.php --type=error --days=7

# Performans analizi
php admin/cli/analyze_logs.php --type=performance --days=30
```

## Sorun Giderme

### 1. Yaygın Sorunlar

#### Ürün Gönderilmiyor

**Kontrol Listesi**:
- [ ] Ürün durumu "Enabled" mi?
- [ ] Trendyol kategorisi seçildi mi?
- [ ] Zorunlu alanlar dolduruldu mu?
- [ ] Ürün görseli var mı?
- [ ] Fiyat bilgisi girildi mi?

**Çözüm Adımları**:
```bash
# 1. Ürün validasyonu
php admin/cli/validate_product.php --product_id=123

# 2. Kategori kontrolü
php admin/cli/check_category_mapping.php

# 3. Manuel gönderim
php admin/cli/sync_products.php --product_id=123 --force
```

#### Sipariş Alınmıyor

**Kontrol Listesi**:
- [ ] API bağlantısı aktif mi?
- [ ] Cron job çalışıyor mu?
- [ ] Sipariş durumu ayarları doğru mu?

**Çözüm Adımları**:
```bash
# 1. API testi
php admin/cli/test_api_connection.php

# 2. Cron job kontrolü
php admin/cli/check_cron_status.php

# 3. Manuel sipariş alma
php admin/cli/sync_orders.php --debug
```

#### Stok Senkronizasyon Sorunu

**Kontrol Listesi**:
- [ ] Stok miktarı pozitif mi?
- [ ] Ürün aktif mi?
- [ ] Stok senkronizasyon ayarları doğru mu?

**Çözüm Adımları**:
```bash
# 1. Stok kontrolü
php admin/cli/check_stock_levels.php

# 2. Senkronizasyon testi
php admin/cli/test_stock_sync.php --product_id=123

# 3. Toplu stok güncelleme
php admin/cli/sync_stock.php --force
```

### 2. Hata Kodları

#### API Hata Kodları

| Kod | Açıklama | Çözüm |
|-----|----------|-------|
| 400 | Bad Request | İstek formatını kontrol edin |
| 401 | Unauthorized | API bilgilerini doğrulayın |
| 403 | Forbidden | Yetki kontrolü yapın |
| 404 | Not Found | URL'yi kontrol edin |
| 429 | Rate Limit | İstek sıklığını azaltın |
| 500 | Server Error | Trendyol desteğine başvurun |

#### Sistem Hata Kodları

| Kod | Açıklama | Çözüm |
|-----|----------|-------|
| TY001 | Kategori eşleştirme hatası | Kategori mapping kontrolü |
| TY002 | Ürün validasyon hatası | Ürün bilgilerini kontrol edin |
| TY003 | Stok senkronizasyon hatası | Stok ayarlarını kontrol edin |
| TY004 | Sipariş işleme hatası | Sipariş durumunu kontrol edin |
| TY005 | Fiyat güncelleme hatası | Fiyat formatını kontrol edin |

### 3. Destek Alma

#### Kendi Kendine Çözüm

```bash
# 1. Sistem durumu kontrolü
php admin/cli/system_health_check.php

# 2. Otomatik onarım
php admin/cli/auto_repair.php

# 3. Yapılandırma sıfırlama
php admin/cli/reset_configuration.php --confirm
```

#### Teknik Destek

**Destek Talep Etmeden Önce**:
1. Hata loglarını toplayın
2. Sistem bilgilerini hazırlayın
3. Sorunu yeniden oluşturmaya çalışın
4. Ekran görüntüleri alın

**İletişim Bilgileri**:
- **E-posta**: support@meschain.com
- **Telefon**: +90 XXX XXX XXXX
- **Ticket Sistemi**: https://support.meschain.com
- **Canlı Destek**: Pazartesi-Cuma 09:00-18:00

## En İyi Uygulamalar

### 1. Ürün Yönetimi

#### Ürün Bilgileri

```bash
# Kaliteli ürün açıklaması yazın
- Minimum 200 karakter
- Ürün özelliklerini detaylandırın
- SEO dostu başlık kullanın
- Anahtar kelimeleri dahil edin
```

#### Ürün Görselleri

```bash
# Görsel standartları
- Minimum çözünürlük: 1000x1000px
- Format: JPG, PNG
- Maksimum boyut: 2MB
- Beyaz arka plan tercih edin
- Ürünü net gösterin
```

### 2. Fiyatlandırma Stratejisi

#### Rekabetçi Fiyatlama

```bash
# Fiyat araştırması yapın
- Rakip fiyatlarını takip edin
- Pazar ortalamasını hesaplayın
- Kar marjınızı koruyun
- Kampanya fiyatlarını planlayın
```

#### Dinamik Fiyatlama

```bash
# Otomatik fiyat ayarlama
- Stok durumuna göre fiyat
- Talep yoğunluğuna göre fiyat
- Sezonsal fiyat ayarlamaları
- Rakip fiyat takibi
```

### 3. Stok Yönetimi

#### Stok Optimizasyonu

```bash
# Stok seviyelerini optimize edin
- Satış hızına göre stok planlayın
- Güvenlik stoğu belirleyin
- Yavaş hareket eden ürünleri tespit edin
- Stok devir hızını artırın
```

#### Tedarik Zinciri

```bash
# Tedarikçi yönetimi
- Güvenilir tedarikçilerle çalışın
- Alternatif tedarikçiler bulun
- Teslimat sürelerini takip edin
- Kalite kontrolü yapın
```

### 4. Müşteri Hizmetleri

#### Sipariş İşleme

```bash
# Hızlı sipariş işleme
- Siparişleri 24 saat içinde onaylayın
- Kargo bilgilerini hemen güncelleyin
- Müşteriyle proaktif iletişim kurun
- Sorunları hızla çözün
```

#### İade Yönetimi

```bash
# İade sürecini optimize edin
- İade politikanızı net belirtin
- İade sürecini basitleştirin
- İade nedenlerini analiz edin
- Müşteri memnuniyetini artırın
```

### 5. Performans İzleme

#### KPI Takibi

```bash
# Önemli metrikleri takip edin
- Satış büyüme oranı
- Kar marjı
- İade oranı
- Müşteri memnuniyeti
- Stok devir hızı
```

#### Sürekli İyileştirme

```bash
# Düzenli optimizasyon
- Aylık performans analizi
- Rakip analizi
- Müşteri geri bildirim değerlendirmesi
- Sistem güncellemeleri
- Eğitim ve gelişim
```

---

## Özet Kontrol Listesi

### ✅ Günlük Kontroller

- [ ] Dashboard durumu kontrol edildi
- [ ] Yeni siparişler kontrol edildi
- [ ] Stok seviyeleri kontrol edildi
- [ ] Hata logları kontrol edildi
- [ ] API durumu kontrol edildi

### ✅ Haftalık Kontroller

- [ ] Satış performansı analiz edildi
- [ ] Fiyat rekabeti kontrol edildi
- [ ] Stok devir analizi yapıldı
- [ ] Müşteri geri bildirimleri değerlendirildi
- [ ] Sistem performansı kontrol edildi

### ✅ Aylık Kontroller

- [ ] Kapsamlı performans raporu oluşturuldu
- [ ] Kategori performansı analiz edildi
- [ ] Kar marjı analizi yapıldı
- [ ] Sistem optimizasyonu gerçekleştirildi
- [ ] Eğitim ihtiyaçları değerlendirildi

---

**Başarılı Trendyol Entegrasyonu!** 🎯

Bu rehberi takip ederek Trendyol entegrasyonunuzu en verimli şekilde kullanabilir ve satışlarınızı artırabilirsiniz.

**MesChain Trendyol Entegrasyonu v1.0.0**
**Son Güncelleme**: 21 Haziran 2025
**Durum**: Aktif ve Destekleniyor ✅
