# 🔗 GitHub Bağlantı Komutları

Repository oluşturduktan sonra şu komutları çalıştırın:

```bash
# Repository dizinine gidin
cd /Users/mezbjen/Desktop/MesTech/MesChain-Sync

# GitHub remote ekleyin (YOUR_USERNAME yerine GitHub kullanıcı adınızı yazın)
git remote add origin https://github.com/YOUR_USERNAME/meschain-sync-enterprise.git

# Ana dal adını main olarak ayarlayın
git branch -M main

# Kodları GitHub'a push edin
git push -u origin main
```

## 📋 Bağlantı Kontrolü Komutları

```bash
# Remote bağlantısını kontrol edin
git remote -v

# Son commit'i kontrol edin
git log --oneline -n 1

# Repository durumunu kontrol edin
git status
```

## ✅ Başarı Kontrolü

Push işlemi başarılı olduğunda:
- GitHub sayfasında 1,431+ dosya görünecek
- README.md düzgün görüntülenecek
- .github/workflows klasöründe CI/CD dosyaları görünecek
- Tüm klasör yapısı GitHub'da görünecek

## 🚨 Hatalarla Karşılaştığınızda

Eğer push sırasında hata alırsanız:

```bash
# Force push (dikkatli kullanın)
git push -u origin main --force

# Veya pull işlemi gerekirse
git pull origin main --allow-unrelated-histories
git push -u origin main
```
