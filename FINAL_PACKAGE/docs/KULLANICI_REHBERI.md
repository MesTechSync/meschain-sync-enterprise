# MesChain Trendyol Entegrasyonu - Kullanıcı Rehberi

## İçindekiler

1. [Giriş](#giriş)
2. [İlk Kurulum Sonrası Ayarlar](#ilk-kurulum-sonrası-ayarlar)
3. [Ürün Yönetimi](#ürün-yönetimi)
4. [Sipariş Yönetimi](#sipariş-yönetimi)
5. [Stok Yönetimi](#stok-yönetimi)
6. [Fiyat Yönetimi](#fiyat-yönetimi)
7. [Kategori Yönetimi](#kategori-yönetimi)
8. [Raporlar ve Analitik](#raporlar-ve-analitik)
9. [İzleme ve Uyarılar](#izleme-ve-uyarılar)
10. [Sorun Giderme](#sorun-giderme)
11. [İpuçları ve En İyi Uygulamalar](#ipuçları-ve-en-iyi-uygulamalar)

KULLANICI_REHBERI
MesChain Trendyol Entegrasyonu, OpenCart mağazanızı Trendyol marketplace'i ile otomatik olarak senkronize eden güçlü bir araçtır. Bu rehber, entegrasyonu günlük kullanımınızda nasıl etkili bir şekilde kullanacağınızı gösterir.

### Temel Özellikler

- ✅ **Otomatik Ürün Senkronizasyonu**: OpenCart'taki ürünleriniz otomatik olarak Trendyol'a aktarılır
- ✅ **Gerçek Zamanlı Sipariş Yönetimi**: Trendyol siparişleri anında OpenCart'a gelir
- ✅ **Stok Senkronizasyonu**: Stok seviyeleri her iki platformda da güncel tutulur
- ✅ **Fiyat Yönetimi**: Dinamik fiyatlandırma ve promosyon yönetimi
- ✅ **Kapsamlı Raporlama**: Detaylı satış ve performans raporları

## İlk Kurulum Sonrası Ayarlar

### 1. Admin Paneline Erişim

1. OpenCart admin paneline giriş yapın
2. **Extensions** > **Extensions** menüsüne gidin
3. Filter'da **Modules** seçin
4. **Trendyol Integration** modülünü bulun
5. **Edit** butonuna tıklayın

### 2. Temel Ayarların Kontrolü

#### API Bağlantı Ayarları
```
✓ Status: Enabled
✓ API URL: https://api.trendyol.com
✓ Supplier ID: [Trendyol'dan aldığınız ID]
✓ API Key: [Trendyol API anahtarınız]
✓ API Secret: [Trendyol API secret'ınız]
```

#### Senkronizasyon Ayarları
```
✓ Auto Sync: Enabled
✓ Sync Interval: 5 minutes (önerilen)
✓ Batch Size: 100 (başlangıç için)
✓ Max Retries: 3
```

### 3. İlk Senkronizasyon

Kurulum sonrası ilk senkronizasyonu başlatmak için:

1. **Trendyol Integration** > **Sync Management** bölümüne gidin
2. **Initial Sync** butonuna tıklayın
3. Senkronizasyon türlerini seçin:
   - ☑️ Products (Ürünler)
   - ☑️ Categories (Kategoriler)
   - ☑️ Stock (Stok)
   - ☑️ Prices (Fiyatlar)
4. **Start Sync** butonuna tıklayın

⚠️ **Önemli**: İlk senkronizasyon ürün sayınıza bağlı olarak 30 dakika ile 2 saat arasında sürebilir.

## Ürün Yönetimi

### Yeni Ürün Ekleme

#### OpenCart'ta Ürün Oluşturma

1. **Catalog** > **Products** > **Add New** menüsüne gidin
2. Ürün bilgilerini doldurun:

**Genel Bilgiler**:
```
Product Name: [Ürün adı - Türkçe]
Description: [Detaylı ürün açıklaması]
Meta Tag Title: [SEO başlığı]
Meta Tag Description: [SEO açıklaması]
Meta Tag Keywords: [Anahtar kelimeler]
Product Tags: [Ürün etiketleri]
```

**Veri Sekmesi**:
```
Model: [Ürün modeli/SKU]
SKU: [Stok kodu]
UPC: [Barkod]
EAN: [Avrupa barkodu]
JAN: [Japon barkodu]
ISBN: [Kitap kodu]
MPN: [Üretici parça numarası]
Location: [Depo konumu]
```

**Bağlantılar Sekmesi**:
```
Manufacturer: [Marka seçin]
Categories: [Kategorileri seçin]
Filters: [Filtreleri seçin]
Stores: [Mağazaları seçin]
```

**Özellikler Sekmesi**:
```
Attributes: [Ürün özelliklerini ekleyin]
Options: [Seçenekleri ekleyin (renk, beden vb.)]
```

**Görseller Sekmesi**:
```
Main Image: [Ana ürün görseli - min 1000x1000px]
Additional Images: [Ek görseller - max 8 adet]
```

#### Trendyol'a Özel Ayarlar

**Trendyol Sekmesi** (entegrasyon sonrası eklenir):
```
Trendyol Category: [Trendyol kategori eşleştirmesi]
Brand: [Marka bilgisi]
Barcode: [Barkod bilgisi]
Stock Code: [Trendyol stok kodu]
Cargo Company: [Kargo firması]
Delivery Time: [Teslimat süresi]
```

### Ürün Senkronizasyonu

#### Otomatik Senkronizasyon

Ürünler varsayılan olarak otomatik senkronize edilir:
- **Yeni ürünler**: 5 dakika içinde Trendyol'a gönderilir
- **Güncellenen ürünler**: Değişiklik sonrası 5 dakika içinde güncellenir
- **Silinen ürünler**: Trendyol'da pasif duruma getirilir

#### Manuel Senkronizasyon

Belirli ürünleri manuel olarak senkronize etmek için:

1. **Catalog** > **Products** listesine gidin
2. Senkronize etmek istediğiniz ürünleri seçin
3. **Action** dropdown'dan **Sync to Trendyol** seçin
4. **Execute** butonuna tıklayın

#### Toplu Ürün İşlemleri

**Toplu Ürün Yükleme**:
```bash
# CSV dosyası ile toplu ürün yükleme
php system/cli/import_products.php --file=products.csv --source=trendyol

# Excel dosyası ile toplu ürün yükleme
php system/cli/import_products.php --file=products.xlsx --source=trendyol
```

**Toplu Ürün Güncelleme**:
```bash
# Tüm ürünleri güncelle
php system/cli/sync_products.php --all

# Belirli kategorideki ürünleri güncelle
php system/cli/sync_products.php --category=123

# Belirli markadaki ürünleri güncelle
php system/cli/sync_products.php --manufacturer=456
```

### Ürün Durumu Takibi

#### Senkronizasyon Durumları

Ürünlerin Trendyol'daki durumunu takip etmek için:

1. **Trendyol Integration** > **Product Status** bölümüne gidin
2. Ürün durumlarını görüntüleyin:

| Durum | Açıklama | Eylem |
|-------|----------|-------|
| 🟢 **Synced** | Başarıyla senkronize edildi | - |
| 🟡 **Pending** | Senkronizasyon bekliyor | Bekleyin |
| 🔴 **Failed** | Senkronizasyon başarısız | Hatayı kontrol edin |
| ⚪ **Not Synced** | Henüz senkronize edilmedi | Manuel sync yapın |

#### Hata Durumları

Yaygın ürün senkronizasyon hataları:

**Kategori Hatası**:
```
Hata: "Category not mapped"
Çözüm: Trendyol Integration > Category Mapping bölümünden kategori eşleştirmesi yapın
```

**Görsel Hatası**:
```
Hata: "Image size too small"
Çözüm: Ürün görsellerini minimum 1000x1000px boyutunda yükleyin
```

**Marka Hatası**:
```
Hata: "Brand not found"
Çözüm: Ürün markasını Trendyol onaylı markalar listesinden seçin
```

## Sipariş Yönetimi

### Sipariş Alma Süreci

#### Otomatik Sipariş Alma

Trendyol siparişleri otomatik olarak OpenCart'a aktarılır:

1. **Müşteri Trendyol'da sipariş verir**
2. **5 dakika içinde sipariş OpenCart'a gelir**
3. **Otomatik stok düşürme yapılır**
4. **E-posta bildirimi gönderilir**

#### Sipariş Detayları

Gelen siparişlerde aşağıdaki bilgiler bulunur:

**Müşteri Bilgileri**:
```
Ad Soyad: [Trendyol müşteri adı]
E-posta: [Trendyol proxy e-posta]
Telefon: [Trendyol proxy telefon]
```

**Teslimat Bilgileri**:
```
Teslimat Adresi: [Tam teslimat adresi]
Kargo Firması: [Seçilen kargo firması]
Teslimat Notu: [Müşteri notu]
```

**Sipariş Bilgileri**:
```
Sipariş No: [Trendyol sipariş numarası]
Sipariş Tarihi: [Sipariş tarihi]
Ödeme Yöntemi: [Trendyol ödeme yöntemi]
Toplam Tutar: [Sipariş tutarı]
```

### Sipariş İşleme

#### Sipariş Onaylama

1. **Sales** > **Orders** menüsüne gidin
2. Trendyol siparişini seçin (başlıkta "Trendyol" etiketi görünür)
3. **View** butonuna tıklayın
4. **Order Status** bölümünden durumu güncelleyin:
   - **Pending** → **Processing** (Hazırlanıyor)
   - **Processing** → **Shipped** (Kargoya verildi)
   - **Shipped** → **Complete** (Teslim edildi)

#### Kargo Bilgisi Güncelleme

Sipariş kargoya verildiğinde:

1. Sipariş detay sayfasında **Shipping** sekmesine gidin
2. **Tracking Number** alanına kargo takip numarasını girin
3. **Shipping Method** alanından kargo firmasını seçin
4. **Add Shipping Info** butonuna tıklayın

⚠️ **Önemli**: Kargo bilgileri otomatik olarak Trendyol'a gönderilir.

#### Sipariş İptali

Siparişi iptal etmek için:

1. Sipariş detay sayfasında **Cancel Order** butonuna tıklayın
2. İptal nedenini seçin:
   - Stok yetersizliği
   - Ürün hasarlı
   - Müşteri talebi
   - Diğer
3. İptal notunu yazın
4. **Confirm Cancellation** butonuna tıklayın

### Sipariş Raporları

#### Günlük Sipariş Raporu

**Trendyol Integration** > **Reports** > **Daily Orders** bölümünden:

```
📊 Günlük Sipariş Özeti
├── Toplam Sipariş: 45
├── Onaylanan: 42
├── İptal Edilen: 2
├── Bekleyen: 1
└── Toplam Ciro: ₺12,450
```

#### Aylık Performans Raporu

```
📈 Aylık Performans
├── Toplam Sipariş: 1,250
├── Ortalama Sipariş Tutarı: ₺285
├── En Çok Satan Ürün: [Ürün adı]
├── En Çok Satan Kategori: [Kategori adı]
└── Müşteri Memnuniyeti: %96
```

## Stok Yönetimi

### Otomatik Stok Senkronizasyonu

#### Stok Güncelleme Süreci

1. **OpenCart'ta stok değişikliği yapılır**
2. **15 dakika içinde Trendyol'a gönderilir**
3. **Trendyol'da stok güncellenir**
4. **Bildirim e-postası gönderilir** (isteğe bağlı)

#### Stok Uyarıları

Kritik stok seviyelerinde otomatik uyarılar:

```
🔴 Kritik Stok Uyarısı
Ürün: [Ürün adı]
Mevcut Stok: 2 adet
Minimum Stok: 5 adet
Eylem: Stok ekleyin veya ürünü pasif yapın
```

### Manuel Stok Yönetimi

#### Toplu Stok Güncelleme

1. **Catalog** > **Products** listesine gidin
2. Güncellemek istediğiniz ürünleri seçin
3. **Quick Edit** butonuna tıklayın
4. **Quantity** alanlarını güncelleyin
5. **Save All** butonuna tıklayın

#### CSV ile Stok Güncelleme

```bash
# CSV dosyası formatı:
# product_id,sku,quantity
# 1,PROD001,50
# 2,PROD002,25

php system/cli/update_stock.php --file=stock_update.csv
```

### Stok Takibi

#### Stok Raporu

**Trendyol Integration** > **Reports** > **Stock Report** bölümünden:

```
📦 Stok Durumu Raporu
├── Toplam Ürün: 1,500
├── Stokta Olan: 1,350
├── Stokta Olmayan: 150
├── Kritik Stok: 45
└── Fazla Stok: 120
```

#### Stok Hareketleri

Stok değişikliklerini takip etmek için:

1. **Trendyol Integration** > **Stock Movements** bölümüne gidin
2. Tarih aralığını seçin
3. **Generate Report** butonuna tıklayın

## Fiyat Yönetimi

### Dinamik Fiyatlandırma

#### Otomatik Fiyat Güncelleme

Fiyat değişiklikleri otomatik olarak senkronize edilir:

1. **OpenCart'ta fiyat değişikliği**
2. **5 dakika içinde Trendyol'a gönderim**
3. **Trendyol'da fiyat güncelleme**
4. **Rekabet analizi** (isteğe bağlı)

#### Fiyat Kuralları

**Trendyol Integration** > **Price Rules** bölümünden fiyat kuralları oluşturun:

```
📋 Fiyat Kuralı Örneği
├── Kural Adı: "Elektronik Ürünler %10 Kar"
├── Kategori: Elektronik
├── Maliyet Çarpanı: 1.10
├── Minimum Kar Marjı: %15
└── Maksimum İndirim: %20
```

### Promosyon Yönetimi

#### Kampanya Oluşturma

1. **Trendyol Integration** > **Promotions** bölümüne gidin
2. **Create New Promotion** butonuna tıklayın
3. Kampanya detaylarını doldurun:

```
Kampanya Bilgileri:
├── Kampanya Adı: [Kampanya adı]
├── Başlangıç Tarihi: [Tarih]
├── Bitiş Tarihi: [Tarih]
├── İndirim Oranı: [%]
├── Minimum Sipariş Tutarı: [Tutar]
└── Maksimum İndirim Tutarı: [Tutar]
```

#### Otomatik Promosyon Senkronizasyonu

Trendyol'daki kampanyalar otomatik olarak OpenCart'a senkronize edilir:

- **Flash Sale**: Anlık indirimler
- **Coupon Campaigns**: Kupon kampanyaları
- **Bundle Offers**: Paket teklifleri
- **Free Shipping**: Ücretsiz kargo

## Kategori Yönetimi

### Kategori Eşleştirme

#### Otomatik Eşleştirme

Sistem akıllı algoritma ile kategorileri otomatik eşleştirir:

```
OpenCart Kategorisi → Trendyol Kategorisi
├── Elektronik → Elektronik
├── Giyim → Kadın Giyim
├── Ayakkabı → Ayakkabı & Çanta
└── Ev & Bahçe → Ev & Yaşam
```

#### Manuel Eşleştirme

1. **Trendyol Integration** > **Category Mapping** bölümüne gidin
2. Eşleştirilmemiş kategorileri görüntüleyin
3. Her kategori için Trendyol karşılığını seçin
4. **Save Mapping** butonuna tıklayın

### Yeni Kategori Ekleme

#### OpenCart'ta Kategori Oluşturma

1. **Catalog** > **Categories** > **Add New** menüsüne gidin
2. Kategori bilgilerini doldurun:

```
Genel Bilgiler:
├── Category Name: [Kategori adı]
├── Description: [Kategori açıklaması]
├── Meta Title: [SEO başlığı]
├── Meta Description: [SEO açıklaması]
└── Meta Keywords: [Anahtar kelimeler]

SEO Ayarları:
├── SEO URL: [kategori-adi]
├── Parent Category: [Üst kategori]
├── Filters: [Filtreler]
└── Image: [Kategori görseli]
```

#### Trendyol Kategori Onayı

Yeni kategoriler için Trendyol onayı gerekebilir:

1. **Trendyol Satıcı Paneli**'ne giriş yapın
2. **Ürünler** > **Kategori Başvurusu** bölümüne gidin
3. Yeni kategori için başvuru yapın
4. Onay sürecini bekleyin (3-5 iş günü)

## Raporlar ve Analitik

### Satış Raporları

#### Günlük Satış Raporu

**Trendyol Integration** > **Reports** > **Sales Report** bölümünden:

```
📊 Günlük Satış Raporu - 21 Haziran 2025
├── Toplam Satış: ₺15,750
├── Sipariş Sayısı: 42
├── Ortalama Sipariş: ₺375
├── En Çok Satan: [Ürün adı]
├── En Az Satan: [Ürün adı]
└── Kar Marjı: %28
```

#### Aylık Trend Analizi

```
📈 Aylık Trend Analizi - Haziran 2025
├── Satış Artışı: %15 (önceki aya göre)
├── Yeni Müşteri: 156 kişi
├── Tekrar Eden Müşteri: %34
├── En Popüler Kategori: Elektronik
└── Sezonsal Trend: Yaz ürünleri ↗️
```

### Performans Metrikleri

#### API Performansı

```
⚡ API Performans Metrikleri
├── Ortalama Yanıt Süresi: 145ms
├── Başarı Oranı: %99.8
├── Günlük API Çağrısı: 12,450
├── Hata Oranı: %0.2
└── Uptime: %99.95
```

#### Senkronizasyon İstatistikleri

```
🔄 Senkronizasyon İstatistikleri
├── Ürün Sync: %100 başarılı
├── Sipariş Sync: %99.9 başarılı
├── Stok Sync: %100 başarılı
├── Fiyat Sync: %100 başarılı
└── Ortalama Sync Süresi: 2.3 saniye
```

### Özel Raporlar

#### Müşteri Analizi

```
👥 Müşteri Analizi Raporu
├── Toplam Müşteri: 2,450
├── Aktif Müşteri: 1,890
├── VIP Müşteri: 145
├── Ortalama Sipariş Sıklığı: 2.3/ay
└── Müşteri Memnuniyeti: %94
```

#### Ürün Performansı

```
🏆 En İyi Performans Gösteren Ürünler
1. [Ürün Adı] - ₺45,600 (156 satış)
2. [Ürün Adı] - ₺38,900 (134 satış)
3. [Ürün Adı] - ₺32,100 (98 satış)
4. [Ürün Adı] - ₺28,750 (87 satış)
5. [Ürün Adı] - ₺25,400 (76 satış)
```

## İzleme ve Uyarılar

### Gerçek Zamanlı İzleme

#### Dashboard Erişimi

**Trendyol Integration** > **Dashboard** bölümünden sistem durumunu izleyin:

```
🖥️ Sistem Durumu Dashboard
├── 🟢 API Bağlantısı: Aktif
├── 🟢 Senkronizasyon: Çalışıyor
├── 🟡 Stok Uyarısı: 12 ürün
├── 🔴 Hata: 2 başarısız işlem
└── 📊 Günlük İstatistikler
```

#### Canlı Aktivite Akışı

```
🔴 CANLI AKTİVİTE
├── 14:35 - Yeni sipariş alındı (#TR789456)
├── 14:34 - Stok güncellendi (PROD123)
├── 14:33 - Fiyat senkronize edildi (PROD456)
├── 14:32 - Ürün onaylandı (PROD789)
└── 14:31 - Kategori eşleştirildi
```

### Uyarı Sistemi

#### E-posta Uyarıları

Otomatik e-posta uyarıları için ayarlar:

```
📧 E-posta Uyarı Ayarları
├── ✅ Kritik Hatalar
├── ✅ Stok Uyarıları
├── ✅ Sipariş Bildirimleri
├── ❌ Günlük Raporlar
└── ✅ Sistem Bakım Bildirimleri
```

#### SMS Uyarıları

Kritik durumlar için SMS uyarıları:

```
📱 SMS Uyarı Ayarları
├── ✅ API Bağlantı Kesintisi
├── ✅ Kritik Sistem Hataları
├── ❌ Stok Uyarıları
├── ❌ Sipariş Bildirimleri
└── ✅ Güvenlik Uyarıları
```

#### Slack Entegrasyonu

Slack kanalına otomatik bildirimler:

```
💬 Slack Entegrasyon Ayarları
├── Kanal: #trendyol-alerts
├── Webhook URL: [Webhook URL]
├── Bildirim Türleri:
│   ├── ✅ Hatalar
│   ├── ✅ Başarılı İşlemler
│   └── ✅ Günlük Özetler
└── Bildirim Sıklığı: Anlık
```

## Sorun Giderme

### Yaygın Sorunlar

#### 1. Ürün Senkronizasyon Sorunları

**Sorun**: Ürünler Trendyol'a gönderilmiyor

**Kontrol Listesi**:
```
☐ API bağlantısı aktif mi?
☐ Ürün kategorisi eşleştirilmiş mi?
☐ Ürün görselleri uygun boyutta mı?
☐ Marka bilgisi doğru mu?
☐ Stok miktarı 0'dan büyük mü?
```

**Çözüm Adımları**:
1. **Trendyol Integration** > **Logs** bölümünden hata mesajlarını kontrol edin
2. Ürün detaylarını Trendyol gereksinimlerine göre düzenleyin
3. Manuel senkronizasyon deneyin
4. Sorun devam ederse destek ekibiyle iletişime geçin

#### 2. Sipariş Alma Sorunları

**Sorun**: Trendyol siparişleri OpenCart'a gelmiyor

**Kontrol Listesi**:
```
☐ Webhook URL'si doğru ayarlanmış mı?
☐ SSL sertifikası geçerli mi?
☐ Firewall ayarları uygun mu?
☐ Cron job'lar çalışıyor mu?
```

**Çözüm Adımları**:
1. **System** > **Maintenance** > **Error Logs** kontrol edin
2. Webhook URL'sini test edin
3. Cron job durumunu kontrol edin
4. Manuel sipariş çekme deneyin

#### 3. Stok Senkronizasyon Gecikmeleri

**Sorun**: Stok güncellemeleri geç yansıyor

**Kontrol Listesi**:
```
☐ Senkronizasyon aralığı uygun mu?
☐ Sunucu performansı yeterli mi?
☐ Veritabanı optimizasyonu yapıldı mı?
☐ Cache ayarları doğru mu?
```

**Çözüm Adımları**:
1. Senkronizasyon aralığını kısaltın (5 dakika → 2 dakika)
2. Veritabanı indekslerini kontrol edin
3. Cache'i temizleyin
4. Sunucu kaynaklarını artırın

### Hata Kodları ve Çözümleri

#### API Hata Kodları

| Kod | Açıklama | Çözüm |
|-----|----------|-------|
| 401 | Yetkilendirme hatası | API bilgilerini kontrol edin |
| 403 | Erişim reddedildi | Hesap durumunu kontrol edin |
| 404 | Kaynak bulunamadı | URL'yi kontrol edin |
| 429 | Rate limit aşıldı | İstek sıklığını azaltın |
| 500 | Sunucu hatası | Trendyol destek ekibiyle iletişime geçin |

#### Senkronizasyon Hata Kodları

| Kod | Açıklama | Çözüm |
|-----|----------|-------|
| SYNC001 | Kategori eşleştirme hatası | Kategori mapping yapın |
| SYNC002 | Görsel boyut hatası | Görselleri yeniden boyutlandırın |
| SYNC003 | Marka onay hatası | Onaylı marka listesinden seçin |
| SYNC004 | Stok yetersizliği | Stok miktarını artırın |
| SYNC005 | Fiyat format hatası | Fiyat formatını kontrol edin |

### Log Dosyaları

#### Log Dosyası Konumları

```
📁 Log Dosyaları
├── system/storage/logs/trendyol.log (Ana log)
├── system/storage/logs/trendyol_

👥 Müşteri Analizi Raporu
├── Toplam Müşteri: 2,450
├── Aktif Müşteri: 1,890
├── VIP Müşteri: 145
├── Ortalama Sipariş Sıklığı: 2.3/ay
└── Müşteri Memnuniyeti: %94
```

#### Ürün Performansı

```
🏆 En İyi Performans Gösteren Ürünler
1. [Ürün Adı] - ₺45,600 (156 satış)
2. [Ürün Adı] - ₺38,900 (134 satış)
3. [Ürün Adı] - ₺32,100 (98 satış)
4. [Ürün Adı] - ₺28,750 (87 satış)
5. [Ürün Adı] - ₺25,400 (76 satış)
```

## İzleme ve Uyarılar

### Gerçek Zamanlı İzleme

#### Dashboard Erişimi

**Trendyol Integration** > **Dashboard** bölümünden sistem durumunu izleyin:

```
🖥️ Sistem Durumu Dashboard
├── 🟢 API Bağlantısı: Aktif
├── 🟢 Senkronizasyon: Çalışıyor
├── 🟡 Stok Uyarısı: 12 ürün
├── 🔴 Hata: 2 başarısız işlem
└── 📊 Günlük İstatistikler
```

#### Canlı Aktivite Akışı

```
🔴 CANLI AKTİVİTE
├── 14:35 - Yeni sipariş alındı (#TR789456)
├── 14:34 - Stok güncellendi (PROD123)
├── 14:33 - Fiyat senkronize edildi (PROD456)
├── 14:32 - Ürün onaylandı (PROD789)
└── 14:31 - Kategori eşleştirildi
```

### Uyarı Sistemi

#### E-posta Uyarıları

Otomatik e-posta uyarıları için ayarlar:

```
📧 E-posta Uyarı Ayarları
├── ✅ Kritik Hatalar
├── ✅ Stok Uyarıları
├── ✅ Sipariş Bildirimleri
├── ❌ Günlük Raporlar
└── ✅ Sistem Bakım Bildirimleri
```

#### SMS Uyarıları

Kritik durumlar için SMS uyarıları:

```
📱 SMS Uyarı Ayarları
├── ✅ API Bağlantı Kesintisi
├── ✅ Kritik Sistem Hataları
├── ❌ Stok Uyarıları
├── ❌ Sipariş Bildirimleri
└── ✅ Güvenlik Uyarıları
```

#### Slack Entegrasyonu

Slack kanalına otomatik bildirimler:

```
💬 Slack Entegrasyon Ayarları
├── Kanal: #trendyol-alerts
├── Webhook URL: [Webhook URL]
├── Bildirim Türleri:
│   ├── ✅ Hatalar
│   ├── ✅ Başarılı İşlemler
│   └── ✅ Günlük Özetler
└── Bildirim Sıklığı: Anlık
```

## Sorun Giderme

### Yaygın Sorunlar

#### 1. Ürün Senkronizasyon Sorunları

**Sorun**: Ürünler Trendyol'a gönderilmiyor

**Kontrol Listesi**:
```
☐ API bağlantısı aktif mi?
☐ Ürün kategorisi eşleştirilmiş mi?
☐ Ürün görselleri uygun boyutta mı?
☐ Marka bilgisi doğru mu?
☐ Stok miktarı 0'dan büyük mü?
```

**Çözüm Adımları**:
1. **Trendyol Integration** > **Logs** bölümünden hata mesajlarını kontrol edin
2. Ürün detaylarını Trendyol gereksinimlerine göre düzenleyin
3. Manuel senkronizasyon deneyin
4. Sorun devam ederse destek ekibiyle iletişime geçin

#### 2. Sipariş Alma Sorunları

**Sorun**: Trendyol siparişleri OpenCart'a gelmiyor

**Kontrol Listesi**:
```
☐ Webhook URL'si doğru ayarlanmış mı?
☐ SSL sertifikası geçerli mi?
☐ Firewall ayarları uygun mu?
☐ Cron job'lar çalışıyor mu?
```

**Çözüm Adımları**:
1. **System** > **Maintenance** > **Error Logs** kontrol edin
2. Webhook URL'sini test edin
3. Cron job durumunu kontrol edin
4. Manuel sipariş çekme deneyin

#### 3. Stok Senkronizasyon Gecikmeleri

**Sorun**: Stok güncellemeleri geç yansıyor

**Kontrol Listesi**:
```
☐ Senkronizasyon aralığı uygun mu?
☐ Sunucu performansı yeterli mi?
☐ Veritabanı optimizasyonu yapıldı mı?
☐ Cache ayarları doğru mu?
```

**Çözüm Adımları**:
1. Senkronizasyon aralığını kısaltın (5 dakika → 2 dakika)
2. Veritabanı indekslerini kontrol edin
3. Cache'i temizleyin
4. Sunucu kaynaklarını artırın

### Hata Kodları ve Çözümleri

#### API Hata Kodları

| Kod | Açıklama | Çözüm |
|-----|----------|-------|
| 401 | Yetkilendirme hatası | API bilgilerini kontrol edin |
| 403 | Erişim reddedildi | Hesap durumunu kontrol edin |
| 404 | Kaynak bulunamadı | URL'yi kontrol edin |
| 429 | Rate limit aşıldı | İstek sıklığını azaltın |
| 500 | Sunucu hatası | Trendyol destek ekibiyle iletişime geçin |

#### Senkronizasyon Hata Kodları

| Kod | Açıklama | Çözüm |
|-----|----------|-------|
| SYNC001 | Kategori eşleştirme hatası | Kategori mapping yapın |
| SYNC002 | Görsel boyut hatası | Görselleri yeniden boyutlandırın |
| SYNC003 | Marka onay hatası | Onaylı marka listesinden seçin |
| SYNC004 | Stok yetersizliği | Stok miktarını artırın |
| SYNC005 | Fiyat format hatası | Fiyat formatını kontrol edin |

### Log Dosyaları

#### Log Dosyası Konumları

```
📁 Log Dosyaları
├── system/storage/logs/trendyol.log (Ana log)
├── system/storage/logs/trendyol_sync.log (Senkronizasyon)
├── system/storage/logs/trendyol_api.log (API çağrıları)
├── system/storage/logs/trendyol_error.log (Hatalar)
└── system/storage/logs/trendyol_webhook.log (Webhook)
```

#### Log Analizi

**Hata loglarını analiz etmek için**:
```bash
# Son 100 hata kaydını görüntüle
tail -n 100 system/storage/logs/trendyol_error.log

# Belirli bir tarih aralığındaki logları filtrele
grep "2025-06-21" system/storage/logs/trendyol.log

# API hata kodlarını say
grep -c "HTTP 4" system/storage/logs/trendyol_api.log
```

## İpuçları ve En İyi Uygulamalar

### Performans Optimizasyonu

#### 1. Veritabanı Optimizasyonu

```sql
-- Performans için önemli indeksler
CREATE INDEX idx_trendyol_sync ON oc_product (date_modified);
CREATE INDEX idx_trendyol_status ON oc_product (status);
CREATE INDEX idx_trendyol_stock ON oc_product (quantity);

-- Veritabanı temizliği (aylık)
DELETE FROM oc_session WHERE expire < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH));
OPTIMIZE TABLE oc_product;
OPTIMIZE TABLE oc_order;
```

#### 2. Görsel Optimizasyonu

**Önerilen Görsel Boyutları**:
```
Ana Ürün Görseli: 1200x1200px (maksimum 2MB)
Ek Görseller: 800x800px (maksimum 1MB)
Kategori Görseli: 600x400px (maksimum 500KB)
Format: JPG (kalite %85-90)
```

**Toplu Görsel Optimizasyonu**:
```bash
# ImageMagick ile toplu optimizasyon
find image/catalog/products -name "*.jpg" -exec mogrify -resize 1200x1200 -quality 85 {} \;
```

#### 3. Cache Ayarları

**Önerilen Cache Ayarları**:
```php
// config.php içine ekleyin
define('DIR_CACHE', DIR_SYSTEM . 'storage/cache/');
define('CACHE_DRIVER', 'file'); // veya 'redis'
define('CACHE_EXPIRE', 3600); // 1 saat
```

### Güvenlik En İyi Uygulamaları

#### 1. API Güvenliği

```env
# .env dosyasında güvenlik ayarları
API_RATE_LIMIT=1000
ENABLE_REQUEST_LOGGING=true
ENABLE_IP_WHITELIST=true
ALLOWED_IPS=192.168.1.0/24,10.0.0.0/8
```

#### 2. Dosya İzinleri

```bash
# Güvenli dosya izinleri
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 600 .env
chmod 600 config.php
```

#### 3. SSL/TLS Ayarları

```apache
# Apache SSL yapılandırması
SSLEngine on
SSLCertificateFile /path/to/certificate.crt
SSLCertificateKeyFile /path/to/private.key
SSLProtocol all -SSLv2 -SSLv3
SSLCipherSuite HIGH:!aNULL:!MD5
```

### Yedekleme Stratejisi

#### 1. Otomatik Yedekleme

```bash
#!/bin/bash
# daily_backup.sh

# Veritabanı yedeği
mysqldump -u username -p password opencart > backup/db_$(date +%Y%m%d).sql

# Dosya yedeği
tar -czf backup/files_$(date +%Y%m%d).tar.gz \
    --exclude='system/storage/cache' \
    --exclude='system/storage/logs' \
    .

# Eski yedekleri temizle (30 günden eski)
find backup/ -name "*.sql" -mtime +30 -delete
find backup/ -name "*.tar.gz" -mtime +30 -delete
```

#### 2. Yedekleme Cron Job'ı

```bash
# Günlük yedekleme (gece 02:00)
0 2 * * * /path/to/opencart/scripts/daily_backup.sh

# Haftalık tam yedekleme (Pazar 01:00)
0 1 * * 0 /path/to/opencart/scripts/full_backup.sh
```

### Monitoring ve Alerting

#### 1. Sistem İzleme

**Önemli Metrikler**:
```
🔍 İzlenmesi Gereken Metrikler
├── API Yanıt Süresi (< 500ms)
├── Senkronizasyon Başarı Oranı (> %99)
├── Hata Oranı (< %1)
├── Disk Kullanımı (< %80)
├── RAM Kullanımı (< %85)
├── CPU Kullanımı (< %70)
└── Veritabanı Bağlantı Sayısı
```

#### 2. Proaktif Uyarılar

```json
{
  "alerts": {
    "api_response_time": {
      "threshold": 1000,
      "action": "email_admin"
    },
    "sync_failure_rate": {
      "threshold": 5,
      "action": "sms_admin"
    },
    "disk_usage": {
      "threshold": 80,
      "action": "slack_notification"
    }
  }
}
```

### Müşteri Deneyimi Optimizasyonu

#### 1. Ürün Bilgileri

**Kaliteli Ürün Açıklaması İçin**:
```
✅ Ürün özelliklerini detaylı yazın
✅ Teknik spesifikasyonları ekleyin
✅ Kullanım talimatlarını belirtin
✅ Bakım bilgilerini paylaşın
✅ Garanti koşullarını açıklayın
✅ Anahtar kelimeleri kullanın
❌ Kopyala-yapıştır açıklamalar
❌ Hatalı bilgiler
❌ Eksik özellikler
```

#### 2. Görsel Kalitesi

**Profesyonel Ürün Fotoğrafları**:
```
📸 Fotoğraf Çekim İpuçları
├── Beyaz/nötr arka plan kullanın
├── Doğal ışık tercih edin
├── Ürünü farklı açılardan çekin
├── Detay fotoğrafları ekleyin
├── Kullanım fotoğrafları paylaşın
├── Boyut referansı gösterin
└── Yüksek çözünürlük kullanın
```

#### 3. Fiyatlandırma Stratejisi

**Rekabetçi Fiyatlandırma**:
```
💰 Fiyatlandırma İpuçları
├── Pazar araştırması yapın
├── Maliyet analizini doğru yapın
├── Kar marjınızı hesaplayın
├── Trendyol komisyonunu dahil edin
├── Kargo maliyetlerini göz önünde bulundurun
├── Promosyon alanı bırakın
└── Düzenli fiyat kontrolü yapın
```

### Satış Artırma Teknikleri

#### 1. SEO Optimizasyonu

**Trendyol SEO İpuçları**:
```
🔍 SEO Optimizasyonu
├── Başlıkta anahtar kelime kullanın
├── Açıklamada LSI kelimeler ekleyin
├── Ürün özelliklerini detaylandırın
├── Marka ve model bilgisini belirtin
├── Kategori seçimini doğru yapın
├── Etiketleri etkili kullanın
└── Düzenli içerik güncellemesi yapın
```

#### 2. Müşteri Hizmetleri

**Mükemmel Müşteri Deneyimi**:
```
🎯 Müşteri Memnuniyeti
├── Hızlı sipariş işleme (< 24 saat)
├── Güvenilir kargo seçimi
├── Proaktif iletişim
├── Sorunlara hızlı çözüm
├── İade/değişim kolaylığı
├── Müşteri geri bildirimlerini değerlendirme
└── Sürekli iyileştirme
```

#### 3. Stok Yönetimi

**Optimal Stok Seviyeleri**:
```
📦 Stok Yönetimi Stratejisi
├── ABC analizi yapın
├── Sezonsal trendleri takip edin
├── Lead time'ları hesaplayın
├── Güvenlik stoku belirleyin
├── Hızlı hareket eden ürünleri önceliklendirin
├── Yavaş hareket eden ürünleri optimize edin
└── Düzenli stok analizi yapın
```

### Raporlama ve Analiz

#### 1. KPI Takibi

**Önemli Performans Göstergeleri**:
```
📊 Takip Edilmesi Gereken KPI'lar
├── Dönüşüm Oranı (Conversion Rate)
├── Ortalama Sipariş Değeri (AOV)
├── Müşteri Yaşam Değeri (CLV)
├── Müşteri Edinme Maliyeti (CAC)
├── Stok Devir Hızı
├── Geri Dönüş Oranı
└── Net Promoter Score (NPS)
```

#### 2. Veri Analizi

**Satış Verilerini Analiz Etme**:
```sql
-- En çok satan ürünler (son 30 gün)
SELECT p.name, SUM(op.quantity) as total_sold, SUM(op.total) as revenue
FROM oc_order_product op
JOIN oc_product_description p ON op.product_id = p.product_id
JOIN oc_order o ON op.order_id = o.order_id
WHERE o.date_added >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY op.product_id
ORDER BY total_sold DESC
LIMIT 10;

-- Aylık satış trendi
SELECT DATE_FORMAT(date_added, '%Y-%m') as month,
       COUNT(*) as orders,
       SUM(total) as revenue
FROM oc_order
WHERE date_added >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
GROUP BY DATE_FORMAT(date_added, '%Y-%m')
ORDER BY month;
```

### Sorun Çözme Metodolojisi

#### 1. Sistematik Yaklaşım

**Sorun Çözme Adımları**:
```
🔧 Sorun Çözme Süreci
1. Sorunu tanımlayın
2. Belirtileri kaydedin
3. Olası nedenleri listeleyin
4. Hipotezleri test edin
5. Çözümü uygulayın
6. Sonuçları doğrulayın
7. Dokümante edin
8. Önleyici tedbirler alın
```

#### 2. Hızlı Tanı Araçları

```bash
# Sistem durumu kontrolü
./deployment/health_check.sh --full

# API bağlantı testi
php system/cli/test_api_connection.php

# Veritabanı performans kontrolü
php system/cli/db_performance_check.php

# Log analizi
php system/cli/analyze_logs.php --last-24h
```

---

## Sonuç

Bu kullanıcı rehberi, MesChain Trendyol Entegrasyonu'nu etkili bir şekilde kullanmanız için gereken tüm bilgileri içermektedir. Düzenli olarak bu rehberi gözden geçirin ve yeni özellikler hakkında bilgi sahibi olmak için güncellemeleri takip edin.

### Destek ve İletişim

**Teknik Destek**:
- 📧 E-posta: support@meschain.com
- 📞 Telefon: +90 XXX XXX XXXX
- 💬 Canlı Destek: https://support.meschain.com

**Topluluk**:
- 🌐 Forum: https://community.meschain.com
- 📚 Dokümantasyon: https://docs.meschain.com
- 🎥 Video Eğitimler: https://academy.meschain.com

**Sosyal Medya**:
- 🐦 Twitter: @MesChainTech
- 📘 LinkedIn: MesChain Technology
- 📺 YouTube: MesChain Academy

---

**MesChain Trendyol Entegrasyonu v1.0.0**
**Son Güncelleme**: 21 Haziran 2025
**Durum**: Aktif ve Destekleniyor ✅
