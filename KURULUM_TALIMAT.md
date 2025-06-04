# MesChain-Sync v3.0.0 Kurulum Talimatları

## 🚀 Yeni Temiz Kurulum Rehberi

### Sistem Gereksinimleri
- ✅ OpenCart 3.0.4.0 veya üstü
- ✅ PHP 7.4 veya üstü  
- ✅ MySQL 5.6 veya üstü
- ✅ cURL ve JSON extension'ları aktif
- ✅ SSL sertifikası (webhook için önerilen)

### 📦 Paket İçeriği
- `MesChain-Sync-v3.0.0.ocmod.zip` - Ana kurulum paketi
- `install.xml` - OCMOD kurulum dosyası
- `README.md` - Detaylı dokümantasyon
- `CHANGELOG.md` - Versiyon değişiklikleri
- `upload/` klasörü - Tüm sistem dosyaları

## 📋 Adım Adım Kurulum

### 1. Hazırlık İşlemleri
```bash
# Mevcut sistem yedeğini alın
1. OpenCart admin paneli → Tools → Backup/Restore
2. Database backup oluşturun
3. Dosya sisteminin yedeğini alın
```

### 2. OCMOD Kurulumu
1. **OpenCart Admin Paneli**'ne giriş yapın
2. **Extensions → Installer** menüsüne gidin
3. **Upload** butonuna tıklayın
4. `MesChain-Sync-v3.0.0.ocmod.zip` dosyasını seçin
5. Dosya yüklendikten sonra **Continue** butonuna basın

### 3. Modification Refresh
1. **Extensions → Modifications** menüsüne gidin
2. **Refresh** butonuna basın (❗ ÖNEMLİ)
3. Hata yoksa yeşil ✅ işareti görünecek

### 4. Cache Temizleme
1. **Dashboard → Settings** → Cache kısmından **Clear** butonuna basın
2. Browser cache'inizi temizleyin (Ctrl+F5)

### 5. Modül Aktivasyonu
1. **Extensions → Extensions** menüsüne gidin
2. **Choose the extension type:** dropdown'ından **Modules** seçin
3. **MesChain-Sync** modülünü bulun
4. **Install** (+ ikonu) butonuna basın
5. **Edit** (kalem ikonu) butonuna basın

## ⚙️ İlk Yapılandırma

### Genel Ayarlar
1. **Module Name:** MesChain-Sync
2. **Status:** Enabled
3. **Log Level:** Info (geliştirme için Debug)
4. **Auto Sync:** Enable (önerilen)

### Pazaryeri Modüllerini Aktifleştirme

#### Trendyol Kurulumu
1. **Extensions → Extensions → Modules** → **Trendyol**
2. **Install** → **Edit**
3. Gerekli bilgileri girin:
   - **Supplier ID:** Trendyol mağaza ID'niz
   - **API Key:** API anahtarınız
   - **Secret Key:** Gizli anahtarınız
   - **Environment:** Test/Production seçimi
4. **Test Connection** ile bağlantıyı test edin
5. **Save** ve **Status: Enabled** yapın

#### Diğer Modüller (Ozon, N11, Amazon, vb.)
- Aynı şekilde her pazaryeri için modülü aktifleştirin
- API bilgilerini girin
- Bağlantı testlerini yapın

## 🔧 Webhook Yapılandırması

### Trendyol Webhook
1. **Trendyol Modülü → Webhooks** sekmesi
2. Webhook URL'ini kopyalayın: 
   ```
   https://siteniz.com/index.php?route=extension/module/trendyol_webhook
   ```
3. Trendyol Partner Paneli → Entegrasyonlar → Webhook
4. URL'yi yapıştırın ve aktifleştirin

### SSL Kontrolü
```bash
# SSL sertifikası kontrolü
curl -I https://siteniz.com/index.php?route=extension/module/trendyol_webhook
```

## 📊 Doğrulama ve Test

### 1. Sistem Kontrolleri
- [ ] **Admin menüde MesChain-Sync görünüyor mu?**
- [ ] **Tüm modüller kuruldu mu?**
- [ ] **Log dosyaları oluşuyor mu?**
- [ ] **API bağlantıları çalışıyor mu?**

### 2. Test İşlemleri
```php
// Test bağlantısı
Extensions → Modules → Trendyol → Test Connection

// Test ürün gönderimi
Products → Select Product → Actions → Sync to Trendyol

// Test sipariş çekme  
Orders → Import Orders → Trendyol
```

### 3. Log Kontrolleri
```bash
# Log dosyaları konumu
/system/storage/logs/meschain/
├── trendyol.log
├── ozon.log
├── n11.log
└── error.log
```

## 🎯 Optimizasyon Önerileri

### Performance
1. **PHP Memory Limit:** Minimum 256MB
2. **Max Execution Time:** 300 saniye
3. **Upload Max Filesize:** 32MB
4. **MySQL Query Cache:** Aktif

### Güvenlik
1. **SSL/HTTPS:** Zorunlu
2. **API Keys:** Şifrelenmiş saklama
3. **Webhook Security:** Token doğrulama
4. **Regular Backups:** Günlük yedek

### Cron Jobs (Opsiyonel)
```bash
# Otomatik sipariş senkronizasyonu (15 dakikada bir)
*/15 * * * * curl -s "https://siteniz.com/index.php?route=extension/module/meschain_sync/cron&token=YOUR_TOKEN"

# Günlük stok güncelleme (gece yarısı)
0 0 * * * curl -s "https://siteniz.com/index.php?route=extension/module/meschain_sync/stock_sync&token=YOUR_TOKEN"
```

## ⚠️ Sık Karşılaşılan Sorunlar

### Kurulum Hataları
```
❌ "Install failed" hatası
✅ Çözüm: Modification refresh yapın, cache temizleyin

❌ "Permission denied" hatası  
✅ Çözüm: Klasör izinlerini 755, dosya izinlerini 644 yapın

❌ "Class not found" hatası
✅ Çözüm: Helper dosyalarının doğru konumda olduğunu kontrol edin
```

### API Bağlantı Sorunları
```
❌ "Connection timeout"
✅ Çözüm: PHP timeout ayarlarını artırın, proxy kontrol edin

❌ "Invalid credentials"
✅ Çözüm: API anahtarlarını kontrol edin, test/production modunu doğrulayın

❌ "SSL certificate error"
✅ Çözüm: cURL SSL doğrulamayı geçici devre dışı bırakın (sadece test için)
```

### Webhook Sorunları
```
❌ Webhook çalışmıyor
✅ Çözüm: 
   1. SSL sertifikası kontrolü
   2. Firewall/CDN ayarları
   3. URL erişilebilirlik testi
   4. Log dosyalarını inceleyin
```

## 📞 Destek ve Yardım

### Teknik Destek
- **Email:** support@mestech.com.tr
- **Telefon:** +90 XXX XXX XX XX
- **WhatsApp:** +90 XXX XXX XX XX

### Dokümantasyon
- **Online Docs:** https://docs.mestech.com.tr
- **Video Tutorials:** https://youtube.com/mestech
- **FAQ:** https://mestech.com.tr/faq

### Community
- **Forum:** https://forum.mestech.com.tr
- **Discord:** https://discord.gg/mestech
- **Telegram:** @mestech_support

## 📝 Notlar

### Önemli Hatırlatmalar
- ✅ Kurulum öncesi mutlaka yedek alın
- ✅ Test ortamında önce deneyin
- ✅ API limitlerini aşmamaya dikkat edin
- ✅ Log dosyalarını düzenli kontrol edin
- ✅ Güvenlik güncellemelerini takip edin

### Next Steps
1. Pazaryeri hesaplarınızı bağlayın
2. Ürün kategori eşleştirmelerini yapın
3. Fiyat ve stok stratejilerinizi belirleyin
4. Otomatik senkronizasyon ayarlarını optimize edin
5. Raporlama ve analiz araçlarını keşfedin

---

**MesChain-Sync v3.0.0 ile pazaryeri entegrasyonlarınızı profesyonel seviyeye taşıyın! 🚀** 