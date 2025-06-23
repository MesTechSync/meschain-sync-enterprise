# MesChain-Sync Enterprise Entegrasyon Kılavuzu

**Versiyon:** 3.0.0
**Tarih:** 19 Haziran 2025
**Hedef Platform:** OpenCart 4.0.2.3

## İçindekiler

1. [Kurulum Öncesi Hazırlık](#kurulum-öncesi-hazırlık)
2. [Kurulum Adımları](#kurulum-adımları)
3. [Pazaryeri Entegrasyonları](#pazaryeri-entegrasyonları)
4. [Sorun Giderme](#sorun-giderme)

## Kurulum Öncesi Hazırlık

### Sistem Gereksinimleri Kontrolü
```bash
# PHP Versiyon Kontrolü
php -v  # 8.0 veya üzeri olmalı

# MySQL Versiyon Kontrolü
mysql --version  # 8.0 veya üzeri olmalı

# Bellek Kontrolü
php -i | grep memory_limit  # En az 512M olmalı
```

### Yedekleme
```bash
# Veritabanı Yedekleme
mysqldump -u [kullanıcı_adı] -p [veritabanı_adı] > yedek_$(date +%Y%m%d).sql

# Dosya Sistemi Yedekleme
cp -r /opencart/yolu /yedek/yolu/
```

## Kurulum Adımları

1. **OCMOD Dosyasının Yüklenmesi**
   - Admin Panel → Eklentiler → Eklenti Yükleyici
   - `meschain_sync_enterprise.ocmod.zip` dosyasını yükleyin
   - Yükleme onayını bekleyin

2. **Eklenti Kurulumu**
   - Eklentiler → Eklentiler → Modüller
   - "MesChain Sync Enterprise" bulun
   - Kur düğmesine tıklayın
   - Düzenle ile yapılandırın

3. **Veritabanı Tabloları**
   - Otomatik oluşturulacak tablolar:
     - `meschain_products`
     - `meschain_orders`
     - `meschain_sync_log`
     - `meschain_settings`

## Pazaryeri Entegrasyonları

### Trendyol Entegrasyonu
```
Admin → MesChain Sync → Pazaryerleri → Trendyol
• API Anahtarı: [API_KEY]
• API Gizli: [API_SECRET]
• Tedarikçi ID: [SUPPLIER_ID]
→ Bağlantıyı Test Et
```

### Hepsiburada Entegrasyonu
```
Admin → MesChain Sync → Pazaryerleri → Hepsiburada
• Kullanıcı: [MERCHANT_ID]
• Şifre: [API_KEY]
• Mağaza ID: [STORE_ID]
→ Bağlantıyı Test Et
```

### Amazon Entegrasyonu
```
Admin → MesChain Sync → Pazaryerleri → Amazon
• Satıcı ID: [SELLER_ID]
• SP-API Anahtarı: [SP_API_KEY]
• AWS Bölge: [AWS_REGION]
→ Bağlantıyı Test Et
```

## Sorun Giderme

### Genel Sorunlar ve Çözümleri

1. **Bağlantı Hataları**
   - SSL sertifikasını kontrol edin
   - API anahtarlarını doğrulayın
   - Güvenlik duvarı ayarlarını kontrol edin

2. **Performans Sorunları**
   - PHP bellek limitini artırın
   - MySQL önbelleğini optimize edin
   - Cron görevlerini düzenleyin

3. **Senkronizasyon Hataları**
   - Log dosyalarını kontrol edin
   - API limitlerini kontrol edin
   - Veri format uyumluluğunu doğrulayın

### Otomatizasyon (Cron Görevleri)

```bash
# Ürün Senkronizasyonu (5 dakikada bir)
*/5 * * * * php /opencart/yolu/meschain-cron.php sync-products

# Sipariş İçe Aktarma (2 dakikada bir)
*/2 * * * * php /opencart/yolu/meschain-cron.php import-orders

# Stok Güncelleme (10 dakikada bir)
*/10 * * * * php /opencart/yolu/meschain-cron.php sync-inventory
```

## Güvenlik Önlemleri

1. **API Güvenliği**
   - API anahtarlarını güvenli saklayın
   - Rate limiting uygulayın
   - IP kısıtlamaları ekleyin

2. **Veri Güvenliği**
   - Düzenli yedekleme alın
   - SSL kullanın
   - Hassas verileri şifreleyin

3. **Erişim Kontrolü**
   - Kullanıcı yetkilerini sınırlayın
   - İşlem loglarını tutun
   - Güvenlik denetimlerini düzenli yapın
