# 🎯 TAKIM ÜYELERİ İÇİN HIZLI BAŞLANGIÇ KILAVUZU
## MesChain-Sync Enterprise Repository

### 🔗 **Repository URL**
https://github.com/MesTechSync/meschain-sync-enterprise

---

## 👋 **YENİ TAKIM ÜYESİ KURULUMU**

### **1. GitHub Davetiyenizi Kabul Edin**
- Email'inizdeki GitHub davetiyesini kabul edin
- Repository'ye erişim izninizi onaylayın

### **2. Repository'yi Local'e Clone Edin**
```bash
# Terminal/Command Prompt'ta çalıştırın
cd ~/Desktop
git clone https://github.com/MesTechSync/meschain-sync-enterprise.git
cd meschain-sync-enterprise
```

### **3. Development Branch Oluşturun**
```bash
# Kendi geliştirme branch'inizi oluşturun
git checkout -b feature/[isminiz]-[özellik-adı]

# Örnek:
git checkout -b feature/john-frontend-optimization
```

---

## 🤖 **VSCode TAKIM (BACKEND)**

### **Sorumlu Olduğunuz Klasörler**:
```
/backend/                    - Node.js API
/marketplace-integrations/   - PHP integrations
/database/                   - Database schemas
/docs/api/                   - API documentation
```

### **İlk Adımlar**:
```bash
# Backend bağımlılıklarını yükleyin
cd backend
npm install

# Database kurulumunu yapın
npm run db:setup

# Development server'ı başlatın
npm run dev
```

### **Test Komutları**:
```bash
npm test              # Unit tests
npm run test:api      # API endpoint tests
npm run test:db       # Database tests
```

---

## 🎨 **CURSOR TAKIM (FRONTEND)**

### **Sorumlu Olduğunuz Klasörler**:
```
/frontend/          - React TypeScript app
/frontend/src/      - Source code
/frontend/public/   - Static assets
/docs/frontend/     - Frontend documentation
```

### **İlk Adımlar**:
```bash
# Frontend bağımlılıklarını yükleyin
cd frontend
npm install

# Development server'ı başlatın
npm start

# Browser'da açın: http://localhost:3000
```

### **Test & Build Komutları**:
```bash
npm test              # React tests
npm run build         # Production build
npm run preview       # Build preview
```

---

## 🚀 **MUSTI TAKIM (DEVOPS)**

### **Sorumlu Olduğunuz Klasörler**:
```
/deployment/        - Docker & deployment configs
/scripts/           - Automation scripts
/monitoring/        - Monitoring & logging
/docs/deployment/   - Deployment documentation
```

### **İlk Adımlar**:
```bash
# Docker konteynerlerini başlatın
cd deployment
docker-compose up -d

# Monitoring dashboard'ı kontrol edin
docker ps

# Log'ları kontrol edin
docker-compose logs -f
```

### **Deployment Komutları**:
```bash
./scripts/deploy.sh staging    # Staging deployment
./scripts/deploy.sh production # Production deployment
./scripts/backup.sh            # Database backup
```

---

## 📋 **ORTAK GELİŞTİRME SÜRECİ**

### **1. Issue'den Başlayın**
- Repository → Issues → Size assigned olan issue'yu seçin
- Issue'nun gereksinimlerini okuyun
- Branch adınızı issue'ya göre oluşturun

### **2. Kod Geliştirme**
```bash
# Güncel kodu çekin
git checkout main
git pull origin main

# Yeni branch oluşturun
git checkout -b feature/issue-123-açıklama

# Kodunuzu yazın ve test edin
# ...

# Commit yapın
git add .
git commit -m "feat: issue #123 - özellik açıklaması"
```

### **3. Pull Request Oluşturun**
```bash
# Branch'i GitHub'a push edin
git push origin feature/issue-123-açıklama
```

**GitHub'da**:
- Repository → Pull Requests → New Pull Request
- Base: `main` ← Compare: `feature/issue-123-açıklama`
- Title: Issue açıklaması
- Reviewers: Diğer takım liderleri
- Labels: Uygun etiketleri ekleyin

---

## 🔧 **YARALI KOMUTLAR**

### **Güncel Kodu Çekme**:
```bash
git checkout main
git pull origin main
```

### **Branch Değiştirme**:
```bash
git checkout [branch-adı]
git checkout main
```

### **Status Kontrol**:
```bash
git status               # Değişiklikleri gör
git log --oneline -5     # Son 5 commit'i gör
git branch -a            # Tüm branch'leri gör
```

### **Hata Durumunda**:
```bash
git reset --hard HEAD    # Son commit'e geri dön
git clean -fd            # Tracked olmayan dosyaları sil
```

---

## 🏷️ **ISSUE VE PR ETİKETLERİ**

### **Takım Etiketleri**:
- `🤖 vscode-team` - Backend issues
- `🎨 cursor-team` - Frontend issues  
- `🚀 musti-team` - DevOps issues

### **Öncelik Etiketleri**:
- `🔥 critical` - Acil production sorunları
- `⚡ high-priority` - Önemli özellikler
- `📋 medium-priority` - Standart geliştirme
- `📝 low-priority` - İyileştirmeler

---

## 📞 **YARDIM VE DESTEK**

### **Teknik Sorular**:
- Repository Issues'da yeni issue oluşturun
- İlgili takım etiketini ekleyin
- Detaylı açıklama yazın

### **Acil Durumlar**:
- Issue title'a `🚨 URGENT:` ekleyin
- `critical` etiketi ekleyin
- MezBjen'i assign edin

### **Takım Koordinasyonu**:
- GitHub Discussions kullanın
- Daily standup'larda durumu paylaşın
- Cross-team issues için `team-coordination` etiketi kullanın

---

## ✅ **İLK GÜN KONTROL LİSTESİ**

```
□ GitHub davetiyesini kabul ettim
□ Repository'yi local'e clone ettim
□ Development environment'ı kurdum
□ İlk test komutu çalıştırdım
□ Kendi branch'imi oluşturdum
□ İlk issue'mı seçtim
□ Takım üyeleriyle tanıştım
□ Development workflow'u öğrendim
```

---

<div align="center">

**🎯 PRODUCTION GO-LIVE: BUGÜN**  
**🚀 TAKIMINIZ HAZIR!**  
**💪 BAŞARILI GELIŞTIRME DILERIZ!**

</div>
