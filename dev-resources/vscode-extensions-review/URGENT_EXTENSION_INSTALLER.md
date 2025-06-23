# 🚨 ACİL EKLENTI KURULUM REHBERİ

## CURSOR TEAM GÖREVLERİ İÇİN KRİTİK EKLENTILER

### 1. 🔴 AZURE ACCOUNT (KRİTİK)
```
Extension ID: ms-vscode.azure-account
Kurulum: Cursor > Extensions > "Azure Account" ara > Install
```

**Kurulum Adımları:**
1. Cursor'da `Cmd+Shift+X` (Extensions paneli)
2. Arama kutusuna: `Azure Account`
3. Microsoft tarafından yayınlanan resmi eklentiyi seç
4. **Install** butonuna tıkla
5. Kurulum sonrası **Reload** yap

### 2. 🟡 AZURE APP SERVICE (DEPLOYMENT)
```
Extension ID: ms-azuretools.vscode-azureappservice
Kurulum: Cursor > Extensions > "Azure App Service" ara > Install
```

**Kurulum Adımları:**
1. Extensions panelinde: `Azure App Service`
2. Microsoft Azure Tools tarafından yayınlanan eklentiyi seç
3. **Install** butonuna tıkla
4. Azure Account eklentisine bağımlı olduğu için önce onu kur

### 3. 🟣 PHP INTELEPHENSE PREMIUM KONTROL

**Mevcut Durum:** ✅ Aktif (v1.10.4)

**Premium Lisans Kontrolü:**
1. Cursor'da `Cmd+Shift+P` > "PHP Intelephense"
2. Settings'e git: `php.intelephense.licenceKey`
3. Premium özellikler için lisans anahtarı gerekli

**Premium Özellikler:**
- Advanced code completion
- Rename refactoring
- Find all implementations
- Go to type definition

---

## 🚀 HIZLI KURULUM KOMUTLARI

### Cursor Command Palette ile:
```bash
# 1. Cmd+Shift+P açın
# 2. "Extensions: Install Extensions" yazın
# 3. Aşağıdaki eklentileri sırayla arayın ve kurun:

1. "Azure Account" (ms-vscode.azure-account)
2. "Azure App Service" (ms-azuretools.vscode-azureappservice)
3. "Azure Functions" (ms-azuretools.vscode-azurefunctions) [İsteğe bağlı]
4. "Azure Storage" (ms-azuretools.vscode-azurestorage) [İsteğe bağlı]
```

---

## ⚡ KURULUM SONRASI YAPILANDIRILMASI

### Azure Account Yapılandırması:
1. Kurulum sonrası Cursor'u yeniden başlat
2. `Cmd+Shift+P` > "Azure: Sign In"
3. Azure hesabınızla giriş yapın
4. Subscription'ınızı seçin

### Azure App Service Yapılandırması:
1. Sol panelde Azure ikonu görünecek
2. App Services bölümünde projelerinizi görebilirsiniz
3. Deployment için resource group seçin

### PHP Intelephense Premium:
1. Settings > Extensions > PHP Intelephense
2. License Key alanına premium anahtarınızı girin
3. Cursor'u yeniden başlatın

---

## 🎯 KURULUM DOĞRULAMA

### Kontrol Listesi:
- [ ] Azure Account eklentisi kuruldu
- [ ] Azure App Service eklentisi kuruldu  
- [ ] Azure hesabına giriş yapıldı
- [ ] Sol panelde Azure ikonu görünüyor
- [ ] PHP Intelephense çalışıyor
- [ ] Premium lisans kontrol edildi

### Test Komutları:
```bash
# Cursor Command Palette'te test edin:
1. "Azure: Sign In" - Azure bağlantısını test et
2. "Azure: Select Subscriptions" - Subscription'ları listele
3. "PHP Intelephense: Index workspace" - PHP indexing test et
```

---

## 🚨 SORUN GİDERME

### Azure Eklentileri Görünmüyorsa:
1. Cursor'u tamamen kapatın
2. Yeniden açın
3. `Cmd+Shift+P` > "Developer: Reload Window"

### PHP Intelephense Çalışmıyorsa:
1. Settings > PHP > Executable Path kontrol et
2. Workspace'i yeniden index et
3. Output panelinde PHP Intelephense loglarını kontrol et

### Azure Bağlantı Sorunu:
1. Internet bağlantısını kontrol et
2. Firewall ayarlarını kontrol et
3. Azure hesap durumunu kontrol et

---

## ⏰ KURULUM SÜRECİ

**Tahmini Süre:** 10-15 dakika
**Öncelik Sırası:**
1. 🔴 Azure Account (5 dk)
2. 🟡 Azure App Service (3 dk)
3. 🟣 PHP Intelephense Premium (2 dk)
4. ⚙️ Yapılandırma (5 dk)

---

## 📞 DESTEK

Kurulum sırasında sorun yaşarsanız:
1. Cursor > Help > Toggle Developer Tools
2. Console'da hata mesajlarını kontrol edin
3. Extensions panelinde eklenti durumunu kontrol edin

**Acil Durum:** Eklentiler kurulmadan Cursor team görevleri tamamlanamaz!

---

**Son Güncelleme:** 13 Haziran 2025, 12:30
**Durum:** 🚨 ACİL - Hemen kurulmalı
