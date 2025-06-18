# TEKNİK RAPOR: GITHUB REPOSİTORY ENTEGRASYONU VE VSCODE KURULUM DOKÜMANTASYONU
**Hazırlayan:** MesChain-Sync Teknik Ekibi  
**Tarih:** 18 Haziran 2025  
**Döküman Versiyonu:** 1.0.0  
**Gizlilik Derecesi:** Düşük - Eğitim Amaçlı

---

## BÖLÜM 1: YÖNETİCİ ÖZETİ

Bu teknik rapor, akademik araştırma ve geliştirme projelerinde GitHub version kontrol sistemi ile Visual Studio Code (VSCode) entegrasyonunun kurulum ve yapılandırma sürecini detaylandırmaktadır. Meschain-Sync Enterprise projesi özelinde hazırlanan bu doküman, tüm teknik ekipler ve akademisyenler için standart bir kurulum prosedürü sunmaktadır.

### 1.1. Amaç ve Kapsam

Bu rapor şunları içermektedir:
- GitHub hesap yapılandırması ve SSH güvenlik protokolleri
- VSCode ortamında GitHub repository erişimi
- Sıfırdan kurulum ve mevcut projenin VSCode'a aktarılması
- Takım çalışması için branch ve çalışma akışı yapılandırması
- Olası sorunlarda çözüm yöntemleri

### 1.2. Hedef Kitle

- Akademik araştırmacılar
- Teknik proje ekipleri
- Bilgisayar Mühendisliği ve Yazılım Mühendisliği öğrencileri
- Açık kaynak katkıcıları

---

## BÖLÜM 2: GITHUB TEMEL KAVRAMLARI

### 2.1. Version Kontrol Sistemi Olarak GitHub

GitHub, Git dağıtık versiyon kontrol sistemini kullanarak kod ve diğer dijital varlıkların depolanması, yönetilmesi ve işbirliği içinde geliştirilmesini sağlayan web tabanlı bir platformdur. Git, yazılım geliştirme sürecinde değişiklikleri izleme, birden fazla geliştirici arasında koordinasyonu sağlama ve yapılan çalışmaların versiyonlarını koruma amacıyla kullanılır.

**Temel Avantajları:**
- Dağıtık Mimari: Merkezi bir sunucuya bağımlı olmadan çalışabilme
- Paralel Geliştirme: Branch sistemi ile aynı projede birden fazla özellik geliştirilebilir
- Versiyon Kontrolü: Tüm değişiklikler kayıt altında tutulur
- İşbirliği: Çoklu geliştirici desteği ve değerlendirme mekanizmaları

### 2.2. GitHub Terminolojisi

| Terim | Tanım |
|-------|-------|
| Repository (Repo) | Projenin dosyalarının, geçmişinin ve değişikliklerinin saklandığı dijital depo |
| Clone | Remote repository'nin yerel makinaya kopyalanması |
| Commit | Değişikliklerin kaydedilmesi işlemi |
| Branch | Ana koddan ayrılan ve paralel geliştirme yapılabilen kod dalı |
| Pull Request | Değişikliklerin ana koda dahil edilmesi için yapılan talep |
| Fork | Başkasının repository'sinin kişinin kendi hesabına kopyalanması |
| Merge | Branch'lerin birleştirilmesi işlemi |

---

## BÖLÜM 3: VSCODE GİTHUB ENTEGRASYONU - SIFIRDAN KURULUM KILAVUZU

### 3.1. Ön Gereksinimler

**Yazılım Gereksinimleri:**
- Git (https://git-scm.com/downloads)
- Visual Studio Code (https://code.visualstudio.com/)
- GitHub Hesabı (https://github.com/)

**VSCode Eklentileri:**
- Git History
- GitLens
- GitHub Pull Requests and Issues

### 3.2. Git Kurulumu ve Yapılandırma

**Git İndirme ve Kurulum:**
```bash
# macOS (Homebrew ile)
brew install git

# Ubuntu/Debian
sudo apt update
sudo apt install git

# Windows için Git web sitesinden indirip kurulum yapılmalıdır
```

**Git Temel Yapılandırma:**
```bash
# Kullanıcı bilgilerini ayarlama
git config --global user.name "Akademisyen Adı"
git config --global user.email "akademik@email.edu.tr"

# Editör ayarlama
git config --global core.editor "code --wait"

# Yapılandırmayı kontrol etme
git config --list
```

### 3.3. SSH Key Oluşturma ve GitHub'a Yükleme

SSH (Secure Shell), GitHub'a güvenli bir şekilde bağlanmak için kullanılan şifreleme yöntemidir. Bu, her seferinde şifre girmeden GitHub ile güvenli iletişim kurmanızı sağlar.

```bash
# SSH key oluşturma
ssh-keygen -t ed25519 -C "akademik@email.edu.tr"

# SSH agent'ı başlatma
eval "$(ssh-agent -s)"

# SSH key'i agent'a ekleme
ssh-add ~/.ssh/id_ed25519

# Public key'i kopyalama
cat ~/.ssh/id_ed25519.pub
# Çıktıyı kopyalayın
```

**GitHub'a SSH Key Ekleme:**
1. GitHub hesabınıza giriş yapın
2. Sağ üst köşedeki profil resmine tıklayın > Settings
3. Sol menüde "SSH and GPG keys" > "New SSH key"
4. Title: "Akademik Bilgisayar" (ya da tanımlayıcı bir isim)
5. Key alanına kopyaladığınız public key'i yapıştırın
6. "Add SSH key" butonuna tıklayın

**SSH Bağlantısını Test Etme:**
```bash
ssh -T git@github.com
# "Hi username! You've successfully authenticated..." mesajı görmelisiniz
```

---

## BÖLÜM 4: MESCHAIN-SYNC ENTERPRISE REPOSİTORY KURULUMU

### 4.1. VSCode'da GitHub Repository Klonlama

**SSH ile Klonlama (Önerilen):**

1. VSCode'u açın
2. Command Palette'i açın: `Cmd+Shift+P` (macOS) veya `Ctrl+Shift+P` (Windows/Linux)
3. "Git: Clone" komutunu seçin
4. Repository URL'sini girin: `git@github.com:MesTechSync/meschain-sync-enterprise.git`
5. Yerel klasörü seçin (örn: `~/Desktop/meschain-sync-enterprise`)
6. Repository'yi açmak için "Open" seçeneğine tıklayın

**Alternatif Yöntem - Terminal ile Klonlama:**
```bash
# Çalışma dizininize gidin
cd ~/Desktop

# Repository'yi klonlayın
git clone git@github.com:MesTechSync/meschain-sync-enterprise.git

# Proje dizinine girin
cd meschain-sync-enterprise

# VSCode ile projeyi açın
code .
```

### 4.2. VSCode'da Repository Yapılandırması

**Proje Yapısını İnceleme:**
1. Explorer panelinden dosya yapısını inceleyin
2. Ana yapı dosyalarına göz atın:
   - `README.md`: Proje hakkında genel bilgiler
   - `package.json`: Bağımlılıklar ve komutlar
   - `.gitignore`: Git tarafından yok sayılacak dosyalar
   - `LICENSE`: Lisans bilgileri

**Branch'leri İnceleme:**
1. VSCode sol alt köşedeki branch ismini (genelde "main" veya "master") tıklayın
2. Mevcut tüm branch'lerin listesi görüntülenecektir
3. İlgili çalışma branch'ini seçin veya yeni branch oluşturun

**VSCode Git Entegrasyonu Ayarları:**
1. Settings > Extensions > Git
2. "Git: Enable Smart Commit" seçeneğini aktifleştirin
3. "Git: Confirm Sync" seçeneğini aktifleştirin
4. "Git: Fetch On Pull" seçeneğini aktifleştirin

### 4.3. Repository Bağımlılıklarını Kurma

Meschain-Sync Enterprise projesi için Node.js bağımlılıklarının kurulması:

```bash
# Node.js bağımlılıklarını kurma
npm install

# Kurulum sonrası kontrol
npm ls --depth=0
```

---

## BÖLÜM 5: MESCHAIN-SYNC ENTERPRISE GELİŞTİRME ORTAMI YAPISI

### 5.1. Proje Yapısı

Meschain-Sync Enterprise projesi şu ana bölümlerden oluşmaktadır:

```
meschain-sync-enterprise/
├── frontend/                 # Önyüz bileşenleri
│   ├── components/           # Yeniden kullanılabilir UI bileşenleri
│   ├── pages/                # Sayfa şablonları
│   └── styles/               # CSS/SCSS dosyaları
├── backend/                  # Sunucu tarafı kodları
│   ├── api/                  # API endpoint'leri
│   ├── models/               # Veri modelleri
│   └── services/             # İş mantığı servisleri
├── super_admin_modular/      # Admin panel
│   ├── components/           # Modüler panel bileşenleri
│   ├── js/                   # JavaScript dosyaları
│   └── styles/               # CSS dosyaları
├── docs/                     # Dokümantasyon
├── tests/                    # Test dosyaları
└── scripts/                  # Yardımcı scriptler
```

### 5.2. Geliştirme Ortamını Başlatma

```bash
# Geliştirme sunucusunu başlatma
npm run start:dev

# Admin paneli başlatma
npm run start:admin

# Marketplace entegrasyonlarını başlatma
npm run start:marketplace
```

---

## BÖLÜM 6: ETKİLİ GİT/GITHUB ÇALIŞMA STRATEJİSİ

### 6.1. Branching Stratejisi

**Önerilen Branch Yapısı:**

- `main`: Üretim kodu (stabil)
- `develop`: Geliştirme branch'i (test edilmiş, stabile yakın)
- `feature/*`: Yeni özellikler için (ör: `feature/login-page`)
- `bugfix/*`: Hata düzeltmeleri için
- `release/*`: Sürüm hazırlığı için
- `hotfix/*`: Acil düzeltmeler için

### 6.2. Commit ve Push İşlemleri

**Commit İşlemleri:**

```bash
# Değişiklikleri staging alanına ekleme
git add .

# Değişiklikleri commit etme
git commit -m "[FEATURE] Login sayfası tasarımı eklendi"

# Değişiklikleri remote repository'ye gönderme
git push origin feature/login-page
```

**Commit Mesaj Formatı:**
```
[TİP] Kısa açıklama

Detaylı açıklama (gerekirse)
```

**Tip Kategorileri:**
- `[FEATURE]`: Yeni özellik
- `[FIX]`: Hata düzeltmesi
- `[DOCS]`: Dokümantasyon değişiklikleri
- `[STYLE]`: Kod stili değişiklikleri
- `[REFACTOR]`: Kod yapısı değişiklikleri
- `[TEST]`: Test değişiklikleri
- `[CHORE]`: Bakım işlemleri

### 6.3. Pull Request ve Code Review İşlemleri

**Pull Request Oluşturma:**
1. GitHub repository sayfasına gidin
2. "Pull requests" sekmesine tıklayın
3. "New pull request" butonuna tıklayın
4. Base branch (genelde `develop`) ve compare branch (feature branch'iniz) seçin
5. "Create pull request" butonuna tıklayın
6. Başlık ve açıklama girin, reviewers atayın

**Code Review İşlemleri:**
- Atanan reviewers kodu inceler
- Yorumlar ve öneriler paylaşılır
- Gerekli düzeltmeler yapılır
- Onay sonrası merge işlemi gerçekleştirilir

---

## BÖLÜM 7: SORUN GİDERME VE YARDIMCI KAYNAKLAR

### 7.1. Sık Karşılaşılan Sorunlar ve Çözümleri

**SSH Bağlantı Sorunları:**
```bash
# SSH bağlantısını debug modunda test etme
ssh -vT git@github.com

# SSH agent'ı yeniden başlatma
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_ed25519
```

**Merge Conflict Çözümü:**
```bash
# Çakışmaları görüntüleme
git status

# Çakışma olan dosyaları düzenleme (VSCode'da çakışmaları görsel olarak çözebilirsiniz)
code [çakışma_olan_dosya]

# Çözümü tamamlama
git add [çakışma_olan_dosya]
git commit -m "Merge conflicts resolved"
```

**Repository Senkronizasyon Sorunları:**
```bash
# Uzak repository'den en son değişiklikleri alma
git fetch origin

# Yerel branch'i uzak branch ile güncelleme
git pull origin [branch_name]

# Yerel değişiklikleri geçici olarak kaydetme
git stash

# Değişiklikleri geri yükleme
git stash pop
```

### 7.2. Önerilen VSCode Eklentileri

**Verimli GitHub Entegrasyonu İçin:**
- GitLens: Git geçmişi ve değişiklik bilgilerini görüntüleme
- GitHub Pull Requests: Pull request'leri VSCode içinden yönetme
- Git Graph: Branch ve commit geçmişini görselleştirme
- Git History: Commit geçmişini inceleme
- GitLab Workflow: GitLab entegrasyonu için

**Genel Geliştirme Verimliliği İçin:**
- ESLint: JavaScript kod kalitesi kontrolleri
- Prettier: Kod formatlaması
- Live Server: Yerel geliştirme sunucusu
- Error Lens: Hata görselleştirme

---

## BÖLÜM 8: SONUÇ VE ÖNERİLER

Bu rapor, MesChain-Sync Enterprise projesinin GitHub repository'sinin VSCode ortamında sıfırdan nasıl kurulacağını ve yapılandırılacağını detaylı bir şekilde açıklamaktadır. Akademik ve profesyonel projelerde version kontrol sistemlerinin kullanımı, projenin başarılı bir şekilde yönetilmesi ve ekip işbirliğinin sağlanması için kritik önem taşımaktadır.

**Temel Öneriler:**

1. Git ve GitHub'ı günlük geliştirme akışınıza entegre edin
2. Düzenli commit yapın ve açıklayıcı mesajlar kullanın
3. Branch stratejisini proje ihtiyaçlarınıza göre adapte edin
4. Code review süreçlerini standartlaştırın
5. Dokümantasyonu güncel tutun

**İleri Seviye Öneriler:**
1. GitHub Actions ile CI/CD pipeline'ları oluşturun
2. Git hook'ları kullanarak kod kalitesini otomatik kontrol edin
3. Semantic versioning kullanarak sürümleri takip edin
4. GitHub Issues ve Projects özelliklerini kullanarak proje yönetimini iyileştirin

---

## EK KAYNAKLAR

- [Pro Git Book](https://git-scm.com/book/en/v2)
- [GitHub Skills](https://skills.github.com/)
- [VSCode Git Dokümantasyonu](https://code.visualstudio.com/docs/sourcecontrol/overview)
- [GitHub Learning Lab](https://lab.github.com/)
- [MesChain-Sync Enterprise İç Dokümantasyonu](/docs/internal)

---

**Bu doküman MesChain-Sync Enterprise Teknik Ekibi tarafından akademik ve eğitim amaçlı olarak hazırlanmıştır. Her hakkı saklıdır. © 2025**
