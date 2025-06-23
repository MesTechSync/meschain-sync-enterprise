# 🚀 GitHub Yedekleme ve Dosya Kurtarma Kılavuzu
**MesChain-Sync Enterprise Projesi için**
**Tarih:** 18 Haziran 2025

## 📊 GitHub Sistem Durumu

GitHub sisteminde herhangi bir hata tespit edilmemiştir. MesChain-Sync Enterprise projesi aşağıdaki GitHub deposunda yedeklenmektedir:

- ✅ **Repository URL:** `https://github.com/MesTechSync/meschain-sync-enterprise.git`
- ✅ **Erişim Durumu:** Aktif
- ✅ **Dosya Sayısı:** 1,434+ dosya başarıyla yüklenmiş durumda

## 🔄 GitHub'ta Yedekleme Sistemi

GitHub, doğası gereği bir versiyon kontrol ve yedekleme sistemidir. Tüm dosyalarınız GitHub'ta aşağıdaki şekillerde yedeklenmektedir:

### 1. Git Commit Geçmişi
Her commit, projenizin tam bir anlık görüntüsünü (snapshot) saklar. Tüm dosyalar, yapılan her değişiklik için geçmişte korunur.

### 2. Branch Sistemi
Farklı branch'ler, farklı sürümleri korur. Ana geliştirme için `main` branch, özellik geliştirme için feature branch'leri mevcuttur.

### 3. Release Etiketleri
Önemli sürümler için oluşturulan etiketler, belirli noktalara kolayca dönmenizi sağlar.

## ⚠️ Dosyalar Kaybolursa Geri Getirme İşlemleri

### 1. Tek Dosya Kaybolması Durumu
Eğer bir dosyayı yanlışlıkla silerseniz veya zarar verirseniz:

```bash
# Belirli bir dosyanın önceki haline döndürme
git checkout HEAD~1 -- dosya_adi.js

# Veya belirli bir commit'teki haline döndürme
git checkout COMMIT_HASH -- dosya_adi.js
```

### 2. Çoklu Dosya Kaybı Durumu
Çok sayıda dosya kaybedilirse ya da zarar görürse:

```bash
# Son commit'i geri alma (değişiklikleri kaybetmeden)
git reset --soft HEAD~1

# Veya belirli bir commit'e geri dönme
git reset --soft COMMIT_HASH
```

### 3. Tüm Çalışma Dizini Kaybı Durumu
Eğer tüm çalışma dizini bozulursa veya kaybolursa:

```bash
# Yeni bir dizinde tüm projeyi yeniden indirme
mkdir meschain-kurtarma
cd meschain-kurtarma
git clone https://github.com/MesTechSync/meschain-sync-enterprise.git
cd meschain-sync-enterprise
```

### 4. Commitleri Geri Almak İsterseniz
Eğer hatalı bir commit yapıp geri almak isterseniz:

```bash
# Son commit'i tamamen geri alma
git revert HEAD

# Belirli bir commit'i geri alma
git revert COMMIT_HASH
```

### 5. Kayıp Değişiklikleri Geri Getirme
Henüz commit edilmemiş değişiklikler kaybolursa:

```bash
# Stash edilmiş değişiklikleri görüntüleme
git stash list

# Son stash'i geri getirme
git stash apply
```

## 🆘 Acil Durum Kurtarma Adımları

Eğer projenizde ciddi bir veri kaybı yaşarsanız:

### 1. Panik Yapmayın!
Git ve GitHub, veri kaybını önlemek için tasarlanmıştır. Neredeyse her zaman verilerinizi kurtarma imkânınız vardır.

### 2. Yerel Değişikliklerinizi Koruyun
Daha fazla veri kaybını önlemek için, mevcut çalışma dizininizi yedekleyin:

```bash
cp -r /Users/mezbjen/Desktop/meschain-sync-enterprise-1 /Users/mezbjen/Desktop/meschain-backup
```

### 3. Reflog Kontrol Edin
Git reflog, Git'in yaptığı tüm işlemleri kaydeder:

```bash
git reflog
```

Bu komut, HEAD'in tüm geçmiş konumlarını gösterir ve neredeyse her durumda kurtarma yapmanızı sağlar.

### 4. GitHub Ekibi İle İletişime Geçin
Ciddi durumlar için [GitHub Destek](https://support.github.com/contact) ile iletişime geçebilirsiniz.

## 📈 En İyi Pratikler

Veri kaybını önlemek için:

1. **Düzenli Commit Yapın**: Küçük değişiklikler için bile düzenli commit'ler yapın
2. **Push'lamayı Unutmayın**: Değişiklikleri düzenli olarak GitHub'a gönderin
3. **Branch Kullanın**: Her büyük özellik için ayrı branch oluşturun
4. **Etiketleme Yapın**: Önemli sürümleri etiketleyin (`git tag`)
5. **İkinci Remote Ekleyin**: Ekstra güvenlik için ikinci bir remote repository ekleyebilirsiniz:

```bash
git remote add backup https://github.com/MesTechSync/meschain-sync-enterprise-backup.git
git push backup main
```

---

## 📚 Yararlı Git Komutları

```bash
# Commit geçmişini görüntüle
git log --oneline --graph

# Belirli dosyanın geçmişini görüntüle
git log --follow -p -- dosya_adi.js

# Tüm branch'leri listele
git branch -a

# Remote bilgisi görüntüle
git remote -v
```

Bu kılavuz, GitHub'ta yedekleme ve kurtarma işlemleri için temel bilgileri içermektedir. Daha karmaşık senaryolar için ekip liderinize danışabilirsiniz.
