# 🚨 MANUEL KURULUM REHBERİ - ACİL

## CURSOR TEAM GÖREVLERİ İÇİN KRİTİK EKLENTILER

Otomatik kurulum başarısız oldu. Manuel kurulum yapılmalı.

---

## 🔴 1. AZURE ACCOUNT (KRİTİK - HEMEN KURULMALI)

### Kurulum Adımları:
1. **Cursor'u açın**
2. **`Cmd + Shift + X`** tuşlarına basın (Extensions paneli)
3. Arama kutusuna **`Azure Account`** yazın
4. **Microsoft** tarafından yayınlanan resmi eklentiyi bulun
5. **`Install`** butonuna tıklayın
6. Kurulum tamamlandığında **`Reload Window`** yapın

### Doğrulama:
- Sol panelde **Azure ikonu** görünmeli
- Command Palette'te (`Cmd+Shift+P`) **"Azure: Sign In"** komutu mevcut olmalı

---

## 🟡 2. AZURE APP SERVICE (DEPLOYMENT İÇİN GEREKLİ)

### Kurulum Adımları:
1. Extensions panelinde **`Azure App Service`** arayın
2. **Microsoft Azure Tools** tarafından yayınlanan eklentiyi seçin
3. **`Install`** butonuna tıklayın
4. Azure Account'a bağımlı olduğu için önce onu kurduğunuzdan emin olun

### Doğrulama:
- Azure panelinde **App Services** bölümü görünmeli
- Deployment komutları mevcut olmalı

---

## 🟣 3. PHP INTELEPHENSE PREMIUM KONTROL

### Mevcut Durum: ✅ Aktif (v1.10.4)

### Premium Lisans Kontrolü:
1. **`Cmd + ,`** (Settings)
2. Arama kutusuna **`php.intelephense.licenceKey`** yazın
3. Premium lisans anahtarınızı girin
4. Cursor'u yeniden başlatın

### Premium Özellikler:
- ✨ Advanced code completion
- 🔄 Rename refactoring  
- 🔍 Find all implementations
- 📍 Go to type definition

---

## ⚡ HIZLI KURULUM KONTROL LİSTESİ

### Adım 1: Extensions Paneli
- [ ] `Cmd + Shift + X` ile Extensions panelini açtım
- [ ] Arama kutusu görünüyor

### Adım 2: Azure Account
- [ ] "Azure Account" aradım
- [ ] Microsoft'un resmi eklentisini buldum
- [ ] Install butonuna tıkladım
- [ ] Kurulum tamamlandı
- [ ] Reload window yaptım

### Adım 3: Azure App Service  
- [ ] "Azure App Service" aradım
- [ ] Microsoft Azure Tools eklentisini buldum
- [ ] Install butonuna tıkladım
- [ ] Kurulum tamamlandı

### Adım 4: Doğrulama
- [ ] Sol panelde Azure ikonu görünüyor
- [ ] `Cmd+Shift+P` > "Azure: Sign In" komutu mevcut
- [ ] PHP Intelephense çalışıyor

---

## 🚀 KURULUM SONRASI YAPILANDIRMA

### Azure Hesap Bağlantısı:
1. **`Cmd + Shift + P`** (Command Palette)
2. **`Azure: Sign In`** yazın ve Enter
3. Browser açılacak, Azure hesabınızla giriş yapın
4. Cursor'a geri dönün
5. Subscription'ınızı seçin

### Doğrulama Testleri:
```
Command Palette'te test edin:
✅ "Azure: Sign In" - Çalışmalı
✅ "Azure: Select Subscriptions" - Subscription'ları listeler
✅ "Azure: Create Web App" - Deployment seçenekleri
```

---

## 🎯 CURSOR TEAM GÖREVLERİ İÇİN HAZIRLIK

### Gerekli Eklentiler Durumu:
- 🔴 **Azure Account**: KURULMALI (Kritik)
- 🟡 **Azure App Service**: KURULMALI (Deployment)
- 🟣 **PHP Intelephense**: ✅ Aktif (Premium kontrol et)

### Görev Tamamlama Oranları:
- **Super Admin Panel**: %35 → %60 (Azure deployment gerekli)
- **Trendyol API**: %40 → %70 (Azure hosting gerekli)
- **Frontend Performance**: Devam ediyor
- **Cross-browser Testing**: Devam ediyor

---

## 🚨 SORUN GİDERME

### Eklenti Görünmüyorsa:
1. Cursor'u tamamen kapatın
2. Yeniden açın
3. `Cmd+Shift+P` > "Developer: Reload Window"

### Azure Bağlantı Sorunu:
1. Internet bağlantısını kontrol edin
2. Firewall ayarlarını kontrol edin
3. Azure hesap durumunu kontrol edin
4. Browser'da manuel olarak Azure'a giriş yapmayı deneyin

### PHP Intelephense Sorunu:
1. Output panelini açın (`Cmd+Shift+U`)
2. "PHP Intelephense" seçin
3. Hata mesajlarını kontrol edin
4. Workspace'i yeniden index edin

---

## ⏰ ZAMAN KISITI

**🚨 ACİL**: Bu eklentiler kurulmadan Cursor team görevleri tamamlanamaz!

**Tahmini Süre**: 10-15 dakika
1. Azure Account kurulumu: 5 dakika
2. Azure App Service kurulumu: 3 dakika  
3. Yapılandırma: 5 dakika
4. Test: 2 dakika

---

## 📞 DESTEK

Kurulum sırasında sorun yaşarsanız:
1. **Developer Tools**: `Cmd+Option+I`
2. **Console** sekmesinde hata mesajlarını kontrol edin
3. **Extensions** panelinde eklenti durumunu kontrol edin

**Son Güncelleme**: 13 Haziran 2025, 12:30
**Durum**: 🚨 ACİL - Hemen kurulmalı

---

## ✅ KURULUM TAMAMLANDI MI?

Kurulum tamamlandığında:
1. Sol panelde Azure ikonu görünmeli
2. Command Palette'te Azure komutları mevcut olmalı
3. Azure hesabına giriş yapılmış olmalı
4. PHP Intelephense premium kontrol edilmeli

**Sonraki adım**: Cursor team görevlerine devam!
