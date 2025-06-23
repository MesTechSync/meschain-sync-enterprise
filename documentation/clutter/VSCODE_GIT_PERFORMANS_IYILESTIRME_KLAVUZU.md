# VSCode için Git Performans İyileştirmeleri ve Sağlıklı Geliştirme Ortamı
# Version: 1.0.0
# Updated: June 19, 2025

# İşte VSCode'da sağlıklı bir geliştirme ortamı için Git ve VSCode yapılandırması:

## 1. Git Performans Ayarları

Git'i daha hızlı ve verimli hale getirmek için aşağıdaki komutu terminalde çalıştırın:

```bash
git config --global core.preloadindex true
git config --global core.fscache true
git config --global gc.auto 256
git config --global core.compression 9
```

## 2. Git Renk Kodları ve VSCode Dosya Durumu

VSCode'da dosyaların yanında görünen renk ve işaretler:

- Yeşil (U): Takip edilmeyen (untracked) yeni dosyalar
- Sarı (M): Değiştirilmiş (modified) dosyalar
- Kırmızı (D): Silinmiş (deleted) dosyalar
- Gri: Git tarafından yok sayılan (ignored) dosyalar

## 3. Git İş Akışı İyileştirmeleri

```bash
# Daha hızlı git status için
git config --global core.untrackedcache true

# Hızlı diff için
git config --global diff.algorithm patience

# Yerel değişiklikleri korumak için
git config --global merge.ff only
git config --global pull.ff only
```

## 4. VSCode Eklentileri

Aşağıdaki VSCode eklentilerini kurmanız önerilir:

- GitLens
- Git History
- Git Graph
- GitHub Pull Requests and Issues
- Error Lens
- ESLint
- Prettier

## 5. Performans İyileştirme Önerileri

- Proje veya çalışma alanını düzenli olarak yeniden yükleyin
- Büyük değişikliklerden önce ve sonra Git geçmişini temizleyin:
  `git gc --aggressive --prune=now`
- Node.js projelerinde node_modules klasörünün düzenli olarak silinip yeniden kurulması
- JavaScript/TypeScript projelerinde babel-cache, .cache vb. önbellek klasörlerinin temizlenmesi
