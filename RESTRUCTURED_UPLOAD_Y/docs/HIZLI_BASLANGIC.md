# MesChain Trendyol Entegrasyonu - Hızlı Başlangıç Rehberi

## 🚀 5 Dakikada Kurulum

Bu rehber, MesChain Trendyol Entegrasyonu'nu en hızlı şekilde kurup çalıştırmanız için hazırlanmıştır.

### ✅ Ön Koşullar (2 dakika)

**Sistem Kontrolleri**:
```bash
# PHP sürümü (7.4+ gerekli)
php -v

# OpenCart sürümü (4.x gerekli)
grep "VERSION" system/startup.php

# Gerekli PHP eklentileri
php -m | grep -E "(curl|json|mbstring|mysql|zip)"
```

**Trendyol Bilgileri**:
- ✅ Supplier ID: `_______________`
- ✅ API Key: `_______________`
- ✅ API Secret: `_______________`

### 📦 Kurulum (2 dakika)

#### Yöntem 1: OCMOD Kurulumu (Önerilen)
```bash
# 1. Admin paneline giriş yapın
# 2. Extensions > Installer > Upload
# 3. meschain-trendyol.ocmod.zip dosyasını yükleyin
# 4. Extensions > Extensions > Modules > Trendyol Integration > Install
```

#### Yöntem 2: Hızlı CLI Kurulumu
```bash
cd RESTRUCTURED_UPLOAD
php scripts/quick_install.php --opencart-path=/path/to/opencart
```

### ⚙️ Temel Yapılandırma (1 dakika)

**Admin Paneli Ayarları**:
1. **Extensions** > **Extensions** > **Modules** > **Trendyol Integration** > **Edit**
2. **API Configuration** sekmesi:
   ```
   Status: Enabled
   API URL: https://api.trendyol.com
   Supplier ID: [Trendyol'dan aldığınız ID]
   API Key: [Trendyol'dan aldığınız Key]
   API Secret: [Trendyol'dan aldığınız Secret]
   Test Mode: Enabled
   ```
3. **Save** butonuna tıklayın

### 🧪 Test (30 saniye)

**Bağlantı Testi**:
```bash
# Admin panelinden
Trendyol Integration > Test Connection

# Beklenen sonuç: ✅ "Connection successful"
```

**İlk Ürün Testi**:
```bash
# Test ürünü oluşturun
Catalog > Products > Add New
- Product Name: "Test Ürünü"
- Model: "TEST001"
- Price: 100
- Quantity: 10
- Trendyol Tab > Send to Trendyol: ✅
- Save
```

---

## 🎯 Hızlı Kullanım Kılavuzu

### 1. Ürün Gönderme (30 saniye)
```bash
# Tek ürün gönderme
Catalog > Products > [Ürün Seç] > Edit > Trendyol Tab > Send to Trendyol ✅ > Save

# Toplu ürün gönderme
Trendyol Integration > Bulk Operations > Send Products > Select All > Send
```

### 2. Sipariş Alma (Otomatik)
```bash
# Otomatik: Her 5 dakikada bir çalışır
# Manuel: Trendyol Integration > Orders > Fetch New Orders
```

### 3. Stok Senkronizasyonu (Otomatik)
```bash
# Otomatik: Her 15 dakikada bir çalışır
# Manuel: Trendyol Integration > Stock > Sync Now
```

---

## 🔧 Hızlı Sorun Giderme

### Yaygın Sorunlar ve Anında Çözümler

#### ❌ "Connection Failed" Hatası
```bash
✅ Çözüm:
1. API bilgilerini kontrol edin
2. SSL sertifikasını kontrol edin: curl -I https://api.trendyol.com
3. Firewall ayarlarını kontrol edin
```

#### ❌ "Product Not Sent" Hatası
```bash
✅ Çözüm:
1. Ürün durumu "Enabled" olmalı
2. Trendyol kategorisi seçilmeli
3. Ürün görseli eklenmiş olmalı
4. Fiyat bilgisi girilmiş olmalı
```

#### ❌ "Orders Not Syncing" Hatası
```bash
✅ Çözüm:
1. Cron job'ları kontrol edin: crontab -l
2. API bağlantısını test edin
3. Log dosyalarını kontrol edin: tail -f system/storage/logs/trendyol.log
```

---

## 📊 Hızlı Dashboard Kontrolleri

### Günlük Kontrol Listesi (2 dakika)
```bash
✅ Dashboard Durumu
- API Status: 🟢 Active
- Sync Status: 🟢 Running
- Error Count: 0
- Last Sync: [Son senkronizasyon zamanı]

✅ Günlük Rakamlar
- Yeni Siparişler: ___
- Gönderilen Ürünler: ___
- Stok Uyarıları: ___
- Hata Sayısı: ___
```

### Haftalık Kontrol Listesi (5 dakika)
```bash
✅ Performans Metrikleri
- Toplam Satış: _____ TL
- Sipariş Sayısı: _____
- Ortalama Sipariş Tutarı: _____ TL
- İade Oranı: _____%

✅ Sistem Sağlığı
- API Response Time: ___ms
- Sync Success Rate: _____%
- Uptime: _____%
- Error Rate: _____%
```

---

## 🎨 Hızlı Özelleştirme

### Logo ve Marka Ayarları
```bash
# Admin panelinden
Trendyol Integration > Settings > Branding
- Company Logo: [Logo yükleyin]
- Company Name: [Şirket adınız]
- Support Email: [Destek e-postanız]
```

### Bildirim Ayarları
```bash
# E-posta bildirimleri
Trendyol Integration > Settings > Notifications
- Order Notifications: ✅
- Error Notifications: ✅
- Daily Reports: ✅
- Email: [E-posta adresiniz]
```

### Otomatik Kurallar
```bash
# Fiyat kuralları
Trendyol Integration > Settings > Pricing Rules
- Profit Margin: 15%
- Auto Price Update: ✅
- Campaign Prices: ✅

# Stok kuralları
Trendyol Integration > Settings > Stock Rules
- Safety Stock: 5
- Auto Stock Update: ✅
- Low Stock Alert: 10
```

---

## 📱 Mobil Erişim

### Dashboard Mobil Görünümü
```bash
# Mobil tarayıcıdan erişim
https://your-store.com/admin/trendyol/mobile-dashboard

# Özellikler:
- Gerçek zamanlı veriler
- Push bildirimleri
- Hızlı sipariş onaylama
- Stok güncelleme
```

---

## 🆘 Acil Durum Kılavuzu

### Sistem Durduğunda
```bash
# 1. Sistem durumunu kontrol edin
php admin/cli/system_health_check.php

# 2. Servisleri yeniden başlatın
php admin/cli/restart_services.php

# 3. Acil onarım
php admin/cli/emergency_repair.php
```

### API Bağlantısı Kesildiğinde
```bash
# 1. Bağlantıyı test edin
php admin/cli/test_api_connection.php

# 2. Alternatif endpoint'e geçin
php admin/cli/switch_to_backup_api.php

# 3. Manuel senkronizasyon
php admin/cli/manual_sync.php
```

### Veri Kaybı Durumunda
```bash
# 1. Yedekten geri yükleme
php admin/cli/restore_from_backup.php --date=2025-06-20

# 2. Veri doğrulama
php admin/cli/validate_data.php

# 3. Eksik verileri tamamlama
php admin/cli/complete_missing_data.php
```

---

## 📞 Hızlı Destek

### Kendi Kendine Yardım
```bash
# Otomatik tanı
php admin/cli/auto_diagnose.php

# Sistem onarımı
php admin/cli/auto_repair.php

# Log analizi
php admin/cli/analyze_logs.php --last-24h
```

### Canlı Destek
- **Telefon**: +90 XXX XXX XXXX
- **WhatsApp**: +90 XXX XXX XXXX
- **E-posta**: support@meschain.com
- **Canlı Chat**: https://support.meschain.com/chat

### Acil Durum Desteği (7/24)
- **Acil Hat**: +90 XXX XXX XXXX
- **Telegram**: @MesChainSupport
- **SMS**: "ACIL [Sorun]" → 1234

---

## 🎉 Başarı Kontrol Listesi

### ✅ Kurulum Tamamlandı
- [ ] OCMOD paketi yüklendi
- [ ] API bağlantısı test edildi
- [ ] İlk ürün gönderildi
- [ ] İlk sipariş alındı
- [ ] Dashboard erişimi sağlandı

### ✅ Sistem Çalışıyor
- [ ] Otomatik senkronizasyon aktif
- [ ] Cron job'lar çalışıyor
- [ ] Bildirimler geliyor
- [ ] Raporlar oluşturuluyor
- [ ] Yedekleme çalışıyor

### ✅ İş Süreci Hazır
- [ ] Ürün kataloğu hazırlandı
- [ ] Fiyat stratejisi belirlendi
- [ ] Stok seviyeleri ayarlandı
- [ ] Kargo anlaşmaları yapıldı
- [ ] Müşteri hizmetleri eğitildi

---

## 🚀 Sonraki Adımlar

### 1. Optimizasyon (1. Hafta)
- Performans ayarlarını optimize edin
- Kategori eşleştirmelerini tamamlayın
- Fiyat kurallarını ince ayarlayın

### 2. Büyüme (1. Ay)
- Ürün kataloğunu genişletin
- Pazarlama stratejilerini uygulayın
- Müşteri geri bildirimlerini değerlendirin

### 3. Ölçeklendirme (3. Ay)
- Diğer pazaryerlerine entegrasyon
- Gelişmiş raporlama araçları
- Otomatik pazarlama kampanyaları

---

**🎯 Hızlı Başlangıç Tamamlandı!**

Artık MesChain Trendyol Entegrasyonu ile satışlarınızı artırmaya hazırsınız!

**⏱️ Toplam Kurulum Süresi**: ~5 dakika
**🎯 İlk Satış Hedefi**: 24 saat içinde
**📈 Büyüme Hedefi**: %300 artış (3 ay içinde)

---

**MesChain Trendyol Entegrasyonu v1.0.0**
**Hızlı Başlangıç Rehberi**
**Son Güncelleme**: 21 Haziran 2025
**Durum**: Aktif ve Destekleniyor ✅

**Başarılar Dileriz!** 🚀🎉
