# MesChain-Sync v3.0.0 - Multi-Marketplace Integration System

## Proje Hakkında
MesChain-Sync, OpenCart 3.0.4.0+ tabanlı profesyonel çoklu pazaryeri entegrasyon sistemidir. Türkiye ve dünya genelindeki önde gelen e-ticaret platformları ile sorunsuz entegrasyon sağlar.

## Desteklenen Pazaryerleri
- ✅ **Trendyol** - %80 Tamamlandı (Webhook desteği aktif)
- ⚡ **Ozon** - %65 Tamamlandı  
- 🔧 **N11** - %30 Tamamlandı
- 🔧 **Amazon** - %15 Tamamlandı
- 🔧 **Hepsiburada** - %25 Tamamlandı
- 📋 **eBay** - Planlama aşamasında

## Özellikler
### ✨ Temel Özellikler
- **Çoklu Pazaryeri Yönetimi** - Tek panelden tüm pazaryerlerini yönetin
- **Gerçek Zamanlı Senkronizasyon** - Ürün, stok ve fiyat güncellemeleri
- **Otomatik Sipariş İthalı** - Pazaryeri siparişlerini otomatik OpenCart'a aktarın
- **Gelişmiş Loglama** - Tüm işlemler detaylı loglanır
- **Webhook Desteği** - Anlık sipariş ve güncelleme bildirimleri

### 🔧 Teknik Özellikler
- **OpenCart 3.x Uyumlu** - MVC(L) mimarisine tam uyum
- **PHP 7.4+ Desteği** - Modern PHP standartları
- **Güvenli API Bağlantıları** - Şifrelenmiş veri iletimi
- **Modüler Yapı** - Her pazaryeri bağımsız modül
- **Çok Dilli Destek** - Türkçe ve İngilizce arayüz

## Sistem Gereksinimleri
- OpenCart 3.0.4.0 veya üstü
- PHP 7.4 veya üstü
- MySQL 5.6 veya üstü
- cURL extension
- JSON extension
- SSL sertifikası (webhook için)

## Kurulum

### 1. OCMOD Kurulumu
1. OpenCart admin paneline giriş yapın
2. **Extensions > Installer** menüsüne gidin
3. `MesChain-Sync-v3.0.0.ocmod.zip` dosyasını yükleyin
4. **Extensions > Modifications** menüsünden `Refresh` butonuna basın

### 2. Modül Aktivasyonu
1. **Extensions > Extensions** menüsüne gidin
2. **Choose the extension type:** olarak **Modules** seçin
3. **MesChain-Sync** modülünü bulun ve **Install** butonuna basın
4. **Edit** butonuna basarak modülü yapılandırın

### 3. Pazaryeri Yapılandırması
Her pazaryeri için:
1. İlgili pazaryeri modülüne gidin
2. API bilgilerini girin (Mağaza ID, API Key, Secret Key vb.)
3. **Test Connection** ile bağlantıyı test edin
4. Modülü **Enable** yapın

## Kullanım

### Ürün Senkronizasyonu
1. **Products > Sync to Marketplace** menüsüne gidin
2. Senkronize edilecek ürünleri seçin
3. Hedef pazaryerini belirleyin
4. **Sync Products** butonuna basın

### Sipariş Yönetimi
1. **Orders > Marketplace Orders** menüsüne gidin
2. **Import Orders** ile yeni siparişleri çekin
3. Sipariş detaylarını görüntüleyin ve işleyin
4. Sipariş durumunu güncelleyin

### Stok ve Fiyat Güncellemeleri
1. **Products > Stock Management** menüsüne gidin
2. **Update Stock** veya **Update Prices** seçeneklerini kullanın
3. Toplu güncellemeler için CSV dosyası yükleyin

## Yapılandırma

### API Bilgileri
Her pazaryeri için gerekli API bilgileri:

#### Trendyol
- Supplier ID
- API Key
- Secret Key
- Test/Production Mode

#### N11
- API Key
- Secret Key
- Company Code

#### Amazon
- Access Key ID
- Secret Access Key
- Merchant ID
- Marketplace ID

### Webhook Yapılandırması
1. **Settings > Webhooks** menüsüne gidin
2. Her pazaryeri için webhook URL'lerini kopyalayın
3. Pazaryeri admin panelinde webhook URL'lerini tanımlayın
4. SSL sertifikası gereklidir

## Loglama ve Hata Ayıklama

### Log Dosyaları
```
/system/storage/logs/meschain/
├── trendyol.log
├── n11.log
├── amazon.log
├── hepsiburada.log
├── ozon.log
└── ebay.log
```

### Log Görüntüleme
1. **Tools > Log Viewer** menüsüne gidin
2. İlgili pazaryeri logunu seçin
3. Hata ve bilgi mesajlarını inceleyin

## Güncelleme Notları

### v3.0.0 (Mevcut Sürüm)
- ✅ Trendyol entegrasyonu tamamlandı
- ✅ Webhook sistemi eklendi
- ✅ Helper sınıfları yeniden yapılandırıldı
- ✅ Gelişmiş loglama sistemi
- ✅ Çok dilli destek eklendi
- ✅ Güvenlik güncellemeleri

### v2.5.0
- Ozon entegrasyonu geliştirildi
- N11 kategori mapping sistemi
- Dropshipping desteği eklendi

## Troubleshooting

### Sık Karşılaşılan Sorunlar

**1. API Bağlantı Hatası**
- API bilgilerini kontrol edin
- Test/Production mode ayarını doğrulayın
- İnternet bağlantısını kontrol edin

**2. Webhook Çalışmıyor**
- SSL sertifikası kontrolü yapın
- Webhook URL'lerini doğrulayın
- Firewall ayarlarını kontrol edin

**3. Ürün Senkronizasyon Hatası**
- Ürün bilgilerinin eksiksiz olduğunu kontrol edin
- Kategori eşleştirmelerini kontrol edin
- Log dosyalarından hata detaylarını inceleyin

## Teknik Destek
- **Email:** support@mestech.com.tr
- **Telefon:** +90 xxx xxx xx xx
- **Website:** https://mestech.com.tr
- **Dokümantasyon:** https://docs.mestech.com.tr

## Lisans
Bu yazılım MesTech Solutions tarafından geliştirilmiştir. Kullanım koşulları için lisans sözleşmesini inceleyiniz.

---
**Copyright © 2024 MesTech Solutions. Tüm hakları saklıdır.** 