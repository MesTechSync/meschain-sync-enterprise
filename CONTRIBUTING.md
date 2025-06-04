# MesChain-Sync: Katkı Sağlama Rehberi

Bu belge, MesChain-Sync Trendyol entegrasyon modülüne katkıda bulunmak isteyen geliştiriciler için hazırlanmıştır.

## Kodlama Standartları

### Genel Kodlama Standartları

- PSR-2 kod standartlarına uyun
- Sınıf adları PascalCase, metod ve değişken adları camelCase olmalıdır
- Her dosyanın başında amaç ve standart açıklaması bulunmalıdır
- Tüm metodlar için dokümantasyon yorumları yazılmalıdır
- Hata ayıklama için uygun loglama kullanılmalıdır

### OpenCart Standartları

- OpenCart MVC mimarisine uyun
- Controller, model ve view dosyaları OpenCart dizin yapısına göre yerleştirilmelidir
- Mevcut OpenCart fonksiyonlarını kullanın, mümkün olduğunca kendiniz yeniden yazmayın
- OpenCart'ın mevcut hook ve event sistemini kullanın

### Dil Dosyaları

- Tüm kullanıcıya gösterilen metinler dil dosyalarından alınmalıdır
- En az Türkçe ve İngilizce dil desteği sağlanmalıdır
- Dil anahtarları açıklayıcı ve tutarlı olmalıdır

## Geliştirme Ortamı

### Gereksinimler

- PHP 7.3+
- OpenCart 3.0.0+
- MySQL/MariaDB 5.7+
- Composer (bağımlılıklar için)
- Git

### Kurulum

1. Depoyu klonlayın: `git clone https://github.com/meschain-sync/trendyol-integration.git`
2. Dosyaları OpenCart kurulumunuza kopyalayın
3. Geliştirme ortamınızda modülü yükleyin

## Branching Stratejisi

- `main`: Kararlı sürüm, üretim için hazır kod
- `develop`: Geliştirme dalı, sonraki sürüm için hazırlanan kod
- `feature/özellik-adı`: Yeni özellikler için
- `bugfix/hata-adı`: Hata düzeltmeleri için
- `hotfix/acil-düzeltme`: Acil üretim düzeltmeleri için

## Pull Request Süreci

1. Uygun bir branch oluşturun (feature, bugfix veya hotfix)
2. Değişikliklerinizi yapın ve test edin
3. Commit mesajlarınızı açıklayıcı yazın
4. PR (Pull Request) açın ve detaylı açıklama ekleyin
5. Code review sürecine hazır olun

## Test Etme

- Yeni eklenen her özellik için test yazın
- Testleri manuel olarak çalıştırın ve sonuçları belgelendirin
- Mevcut özelliklerde regresyonları önlemek için entegrasyon testleri yapın

## Belgelendirme

- Kod içi yorumları güncel tutun
- README.md ve diğer belgeleri güncelleyin
- Karmaşık işlevler için ayrı belgelendirme yapın

## Sürüm Yönetimi

- Semantic Versioning (SemVer) kullanıyoruz: MAJOR.MINOR.PATCH
- MAJOR: Geriye dönük uyumsuz API değişiklikleri
- MINOR: Geriye dönük uyumlu yeni özellikler
- PATCH: Geriye dönük uyumlu hata düzeltmeleri

## İletişim

Geliştirme ile ilgili sorular için:
- GitHub Issues kullanın
- E-posta: development@meschain-sync.com

## Lisans

Katkıda bulunmadan önce lisans anlaşmasını okuduğunuzdan emin olun. Tüm katkılar, MesChain-Sync lisansı altında dağıtılacaktır. 