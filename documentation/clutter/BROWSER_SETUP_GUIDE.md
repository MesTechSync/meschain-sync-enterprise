# 🎯 ADIM ADIM GITHUB AYARLAR KILAVUZU
## Browser'daki Tab'leri Kullanarak 5 Dakikada Tamamlayın

### 🌐 Açık Browser Tab'leri:
1. Repository Ana Sayfa
2. **Manage Access** (Takım erişimleri)
3. **Branches** (Branch protection)
4. **Settings** (Repository özellikleri)
5. **Security & Analysis** (Güvenlik)
6. **Actions** (CI/CD ayarları)
7. **Labels** (Etiketler)

---

## 1️⃣ **TAKIM ERİŞİMLERİ** (Manage Access Tab)

### Adımlar:
1. **"Invite a collaborator"** butonuna tıklayın
2. **VSCode Team üyeleri için**:
   - GitHub username/email girin
   - Role: **"Admin"** seçin
   - **"Add [username] to this repository"** tıklayın
3. **Cursor Team üyeleri için**:
   - GitHub username/email girin
   - Role: **"Write"** seçin
   - **"Add [username] to this repository"** tıklayın
4. **MUSTI Team üyeleri için**:
   - GitHub username/email girin
   - Role: **"Admin"** seçin
   - **"Add [username] to this repository"** tıklayın

### ✅ Sonuç: Takım üyeleri davetiye alacak

---

## 2️⃣ **BRANCH PROTECTION** (Branches Tab)

### Adımlar:
1. **"Add rule"** butonuna tıklayın
2. **Branch name pattern**: `main` yazın
3. **Şu seçenekleri işaretleyin**:
   ```
   ✅ Require pull request reviews before merging
      └─ Required number of reviewers: 2
      └─ Dismiss stale PR review approvals when new commits are pushed
   
   ✅ Require status checks to pass before merging
      └─ Require branches to be up to date before merging
   
   ✅ Require conversation resolution before merging
   ✅ Restrict pushes that create files larger than 100 MB
   ```
4. **"Create"** butonuna tıklayın

### ✅ Sonuç: main branch korunacak

---

## 3️⃣ **REPOSITORY ÖZELLİKLERİ** (Settings Tab)

### Features Bölümünde:
```
✅ Issues (zaten açık olabilir)
✅ Projects 
✅ Wiki
✅ Discussions (opsiyonel)
```

### Danger Zone'da:
```
✅ Archive this repository (kapalı tutun)
✅ Delete this repository (kapalı tutun)
```

**"Save changes"** tıklayın

### ✅ Sonuç: Repository özellikleri aktif

---

## 4️⃣ **GÜVENLİK AYARLARI** (Security & Analysis Tab)

### Şu seçenekleri aktifleştirin:
```
✅ Dependency graph (otomatik açık olabilir)
✅ Dependabot alerts
✅ Dependabot security updates
✅ Code scanning alerts
✅ Secret scanning alerts
```

**Her biri için "Enable" butonuna tıklayın**

### ✅ Sonuç: Güvenlik özellikleri aktif

---

## 5️⃣ **ACTIONS AYARLARI** (Actions Tab)

### General → Actions permissions:
```
🔘 Allow all actions and reusable workflows (seçin)
```

### Workflow permissions:
```
🔘 Read and write permissions (seçin)
✅ Allow GitHub Actions to create pull requests
```

**"Save"** tıklayın

### ✅ Sonuç: CI/CD pipeline aktif

---

## 6️⃣ **ETIKETLER** (Labels Tab)

### Yeni etiketler oluşturun:

#### **Team Labels** (New label → Name/Color):
```
🤖 vscode-team     | #0052CC (mavi)
🎨 cursor-team     | #FF5722 (turuncu)
🚀 musti-team      | #4CAF50 (yeşil)
```

#### **Priority Labels**:
```
🔥 critical        | #FF0000 (kırmızı)
⚡ high-priority   | #FF6600 (koyu turuncu)
📋 medium-priority | #FFA500 (açık turuncu)
📝 low-priority    | #FFFF00 (sarı)
```

#### **Marketplace Labels**:
```
🔴 trendyol        | #FF6600
🟠 amazon          | #FF9900
🔵 ebay            | #0064D2
🟢 n11             | #00AA00
🟡 hepsiburada     | #FFD700
🟣 ozon            | #9C27B0
```

### Her etiket için:
1. **"New label"** tıklayın
2. **Label name** girin
3. **Color** kodunu girin
4. **"Create label"** tıklayın

### ✅ Sonuç: Takım etiketleri hazır

---

## ✅ **TAMAMLAMA KONTROLÜ**

### Repository ana sayfasında görmeli:
- ✅ Settings wheel (⚙️) simgesi
- ✅ Issues tab aktif
- ✅ Actions tab aktif
- ✅ Security tab aktif
- ✅ Branch protection simgesi

### Final test:
```bash
# Terminal'de kontrol
cd /Users/mezbjen/Desktop/MesTech/MesChain-Sync
git status
git log --oneline -3
```

---

## 🎉 **BAŞARI!**

Tüm ayarlar tamamlandığında:
- 👥 Takım üyeleri davetiye alacak
- 🛡️ Branch protection aktif olacak
- 🔒 Güvenlik özellikleri çalışacak
- 🏷️ Etiketler hazır olacak
- 🚀 CI/CD pipeline aktif olacak

**Repository tamamen production ready! 🎯**

---

## 🆘 **SORUN ÇÖZME**

### Eğer bir ayar işe yaramazsa:
1. **Browser'ı yenileyin** (Cmd+R)
2. **Sayfayı tekrar açın**
3. **GitHub durumunu kontrol edin**: https://status.github.com
4. **5 dakika bekleyip tekrar deneyin**

### Yardım için:
- Repository'de issue oluşturun
- "urgent" etiketi ekleyin
- Sorunu detaylandırın
