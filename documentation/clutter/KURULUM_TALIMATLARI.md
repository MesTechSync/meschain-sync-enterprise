# 🚀 MesChain-Sync v2.5.0 - Kurulum Talimatları

## 📦 Kurulum Dosyaları

Aşağıdaki zip dosyalarından birini kullanabilirsiniz:
- `MesChain-Sync-v2.5.0-FINAL.ocmod.zip` (702KB) - **ÖNERİLEN**
- `MesChain-Sync-v2.5.0-Production-Ready.ocmod.zip` (702KB) - Alternatif

## 🎯 Hızlı Kurulum (5 Dakika)

### 1. Admin Panel'e Giriş
```
https://yourdomain.com/admin
```

### 2. Extension Installer'a Git
```
Extensions > Installer
```

### 3. Zip Dosyasını Yükle
- **Sürükle-Bırak:** Zip dosyasını installer alanına sürükleyin
- **Veya Browse:** "Choose File" ile dosyayı seçin
- **Upload:** Yükleme işlemini başlatın

### 4. Kurulumu Tamamla
```
✅ Upload successful
✅ Files extracted
✅ Install completed
```

### 5. Modifications'ı Yenile
```
Extensions > Modifications > Refresh (🔄)
```

### 6. Modülü Etkinleştir
```
Extensions > Extensions > Modules
MesChain-Sync > Install > Edit
Status: Enabled > Save
```

## ✅ Kurulum Doğrulama

### 1. Admin Menüsünde Kontrol
Sol menüde **"MesChain-Sync"** linkini görmelisiniz.

### 2. Dashboard'a Erişim
```
Admin Menu > MesChain-Sync
```

### 3. Başarılı Kurulum Göstergeleri
- ✅ Dashboard açılıyor
- ✅ 6 pazaryeri sekmesi görünüyor
- ✅ Ayarlar sayfası çalışıyor
- ✅ Test bağlantı butonları aktif

## 🔧 İlk Yapılandırma

### 1. Genel Ayarlar
```
Status: Enabled
Debug Mode: Enabled (test için)
Auto Sync: Disabled (başlangıçta)
```

### 2. API Anahtarları
Her pazaryeri için API bilgilerini girin:

#### Trendyol
```
API Key: your_api_key
API Secret: your_api_secret
Supplier ID: your_supplier_id
```

#### Amazon
```
Client ID: your_client_id
Client Secret: your_client_secret
Refresh Token: your_refresh_token
Seller ID: your_seller_id
Marketplace: US/UK/DE/FR/IT/ES/CA/AU/JP/MX/BR
```

#### eBay
```
Client ID: your_client_id
Client Secret: your_client_secret
Refresh Token: your_refresh_token
Marketplace: US/UK/DE/FR/IT/ES/CA/AU
```

### 3. Bağlantı Testleri
Her pazaryeri için **"Test Connection"** butonuna tıklayın.

## 🐛 Sorun Giderme

### Kurulum Hataları

#### "Permission Denied"
```
Çözüm: FTP ile dosya izinlerini kontrol edin
Directories: 755
Files: 644
```

#### "Modification Failed"
```
Çözüm: 
1. Extensions > Modifications > Clear
2. Zip dosyasını tekrar yükleyin
3. Refresh yapın
```

#### "Module Not Found"
```
Çözüm:
1. Extensions > Extensions > Modules
2. Sayfayı yenileyin (F5)
3. MesChain-Sync'i arayın
```

### API Bağlantı Hataları

#### "SSL Certificate Error"
```
Çözüm: SSL sertifikasını kontrol edin
HTTPS zorunludur
```

#### "Invalid API Key"
```
Çözüm: API anahtarlarını kontrol edin
Boşluk karakteri olmamalı
```

#### "Rate Limit Exceeded"
```
Çözüm: 5 dakika bekleyip tekrar deneyin
```

## 📊 Test Senaryoları

### 1. Temel Test
```
1. Dashboard açılıyor mu? ✅
2. Ayarlar kaydediliyor mu? ✅
3. Log dosyaları oluşuyor mu? ✅
```

### 2. API Test
```
1. Test Connection başarılı mı? ✅
2. Hata mesajları görünüyor mu? ✅
3. Log'larda detay var mı? ✅
```

### 3. Senkronizasyon Test
```
1. Ürün listesi çekiliyor mu? ✅
2. Kategori eşleştirme çalışıyor mu? ✅
3. Sipariş senkronizasyonu aktif mi? ✅
```

## 🎯 Üretim Ortamına Geçiş

### 1. Debug Modunu Kapat
```
Debug Mode: Disabled
```

### 2. Otomatik Senkronizasyonu Aç
```
Auto Sync: Enabled
Sync Interval: 60 minutes
```

### 3. Cron Job Kur
```bash
*/15 * * * * curl -s "https://yourdomain.com/admin/index.php?route=extension/module/meschain_sync/cron&token=YOUR_TOKEN"
```

### 4. SSL Sertifikasını Kontrol Et
```
https://yourdomain.com - SSL aktif olmalı
```

### 5. Backup Al
```
Database backup
File backup
API key backup
```

## 📞 Destek

### Teknik Destek
- **Email:** support@meschain.com
- **Website:** https://meschain.com/support

### Log Dosyaları
```
system/logs/amazon.log
system/logs/ebay.log
system/logs/hepsiburada.log
system/logs/n11.log
system/logs/ozon.log
system/logs/trendyol.log
```

### Hata Raporlama
Hata durumunda şunları gönderin:
1. Log dosyaları
2. Hata ekran görüntüsü
3. OpenCart versiyonu
4. PHP versiyonu
5. Yapılandırma detayları

---

**© 2024 MesTech Team - MesChain-Sync v2.5.0**  
**Kurulum Başarıyla Tamamlandı! 🎉** 