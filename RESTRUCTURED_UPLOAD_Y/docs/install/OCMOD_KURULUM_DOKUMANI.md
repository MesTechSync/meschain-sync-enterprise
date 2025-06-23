# MESCHAIN-SYNC ENTERPRISE OCMOD KURULUM DOKÜMANI

**Sürüm:** 3.0.0
**Son Güncelleme:** 19 Haziran 2025
**Platform:** OpenCart 4.0.2.3

## İçindekiler

1. [OCMOD Yapısı](#ocmod-yapısı)
2. [Kurulum Adımları](#kurulum-adımları)
3. [Veritabanı Yapısı](#veritabanı-yapısı)
4. [Azure Entegrasyonu](#azure-entegrasyonu)
5. [Pazaryeri Entegrasyonları](#pazaryeri-entegrasyonları)
6. [Sorun Giderme](#sorun-giderme)

## OCMOD Yapısı

### Paket İçeriği

```
meschain_sync_enterprise.ocmod.zip/
├── install.xml                    # OCMOD manifest dosyası
├── upload/                        # OpenCart dosya yapısı
│   ├── admin/                    # Yönetici panel dosyaları
│   │   ├── controller/
│   │   ├── model/
│   │   ├── view/
│   │   └── language/
│   ├── catalog/                  # Mağaza ön yüz dosyaları
│   └── system/                   # Sistem dosyaları
└── sql/
    └── install.sql              # Veritabanı kurulum dosyası
```

### install.xml Açıklaması

```xml
<?xml version="1.0" encoding="utf-8"?>
<modification>
    <name>MesChain-Sync Enterprise</name>
    <code>meschain_sync_enterprise</code>
    <version>3.0.0</version>
    <author>MesTech Development Team</author>
    <link>https://meschain.com</link>
    <description>
        Enterprise seviye pazaryeri entegrasyonu ve Azure bulut entegrasyonu.
        
        Özellikler:
        • Çoklu pazaryeri senkronizasyonu (7+ platform)
        • Azure bulut servisleri entegrasyonu
        • Gerçek zamanlı stok ve sipariş yönetimi
        • Yapay zeka destekli fiyatlandırma
        • Gelişmiş analitik ve raporlama
        • Performans optimizasyon motoru
        • Güvenli API uç noktaları
    </description>
</modification>
```

## Kurulum Adımları

### 1. Ön Gereksinimler

- OpenCart 4.0.2.3 kurulu olmalı
- PHP 8.0 veya üzeri
- MySQL 8.0 veya üzeri
- SSL sertifikası (zorunlu)
- Minimum 2GB RAM
- 10GB kullanılabilir disk alanı

### 2. Yedekleme

```bash
# Veritabanı yedeği
mysqldump -u [kullanıcı_adı] -p [veritabanı_adı] > yedek_$(date +%Y%m%d).sql

# Dosya yedeği
cp -r /opencart/yolu /yedek/yolu/
```

### 3. OCMOD Kurulumu

1. Admin Panel'e giriş yapın
2. Uzantılar → Yükleyici menüsüne gidin
3. `meschain_sync_enterprise.ocmod.zip` dosyasını yükleyin
4. Yükleme tamamlanana kadar bekleyin
5. Uzantılar → Uzantılar → Modüller menüsünden "MesChain Sync Enterprise" modülünü bulun
6. Kur butonuna tıklayın
7. Düzenle'ye tıklayarak yapılandırmaya başlayın

## Veritabanı Yapısı

### Ana Tablolar

1. `PREFIX_meschain_marketplace`
   - Pazaryeri yapılandırmaları
   - API anahtarları ve ayarlar
   - Durum bilgileri

2. `PREFIX_meschain_product`
   - Ürün senkronizasyon bilgileri
   - Pazaryeri ürün eşleştirmeleri
   - Senkronizasyon durumları

3. `PREFIX_meschain_order`
   - Sipariş entegrasyon bilgileri
   - Pazaryeri sipariş eşleştirmeleri
   - Durum takibi

### Azure Entegrasyon Tabloları

1. `PREFIX_meschain_azure_config`
   - Azure servis yapılandırmaları
   - Bağlantı bilgileri
   - Servis durumları

2. `PREFIX_meschain_azure_log`
   - Azure işlem kayıtları
   - Olay takibi
   - Hata logları

## Azure Entegrasyonu

### Desteklenen Azure Servisleri

1. **Azure Blob Storage**
   - Dosya depolama
   - Yedekleme
   - CDN entegrasyonu

2. **Azure Queue Storage**
   - Asenkron işlem sırası
   - Yük dengeleme
   - İşlem önceliklendirme

3. **Azure Application Insights**
   - Performans izleme
   - Hata takibi
   - Kullanım analizi

4. **Azure Key Vault**
   - Güvenli anahtar depolama
   - Sertifika yönetimi
   - Gizli bilgi yönetimi

### Azure Yapılandırması

1. Azure Portal'dan gerekli servisleri oluşturun
2. Bağlantı bilgilerini not alın
3. MesChain yönetim panelinden Azure ayarlarını yapın:
   - Bağlantı anahtarlarını girin
   - Servisleri aktifleştirin
   - Test bağlantısı yapın

## Pazaryeri Entegrasyonları

### Desteklenen Pazaryerleri

1. **Amazon**
   - Ürün senkronizasyonu
   - Stok yönetimi
   - Sipariş entegrasyonu

2. **Trendyol**
   - Tam entegrasyon
   - Gerçek zamanlı stok
   - Otomatik fiyatlandırma

3. **N11**
   - API entegrasyonu
   - Sipariş yönetimi
   - Ürün eşleştirme

4. **Hepsiburada**
   - Stok senkronizasyonu
   - Sipariş takibi
   - Fiyat optimizasyonu

5. **eBay**
   - Uluslararası satış
   - Çoklu hesap desteği
   - Otomatik çeviri

6. **GittiGidiyor**
   - Tam entegrasyon
   - Stok takibi
   - Sipariş yönetimi

7. **PttAVM**
   - API entegrasyonu
   - Ürün yönetimi
   - Kargo takibi

## Sorun Giderme

### Genel Sorunlar ve Çözümleri

1. **Kurulum Hataları**
   - OpenCart sürüm uyumluluğunu kontrol edin
   - PHP gereksinimlerini doğrulayın
   - Dosya izinlerini kontrol edin

2. **Senkronizasyon Sorunları**
   - API anahtarlarını kontrol edin
   - Ağ bağlantısını test edin
   - Log dosyalarını inceleyin

3. **Azure Bağlantı Hataları**
   - Bağlantı bilgilerini doğrulayın
   - Firewall ayarlarını kontrol edin
   - Servis durumlarını kontrol edin

### Log Dosyaları

Log dosyalarına erişim:
```
/system/storage/logs/meschain_error.log
/system/storage/logs/meschain_debug.log
/system/storage/logs/azure_integration.log
```

### Destek

- E-posta: support@meschain.com
- Telefon: +90 xxx xxx xx xx
- Dokümantasyon: https://docs.meschain.com
- Canlı Destek: https://meschain.com/support

---

## Güvenlik Tavsiyeleri

1. Tüm API anahtarlarını güvenli şekilde saklayın
2. SSL sertifikasını aktif tutun
3. Düzenli yedekleme alın
4. Güvenlik güncellemelerini takip edin
5. Erişim loglarını düzenli kontrol edin

## Performans Optimizasyonu

1. Veritabanı indekslerini kullanın
2. Azure CDN kullanın
3. Önbellek mekanizmalarını aktif edin
4. Zamanlanmış görevleri optimize edin
5. Sistem kaynaklarını düzenli monitör edin
