# GitHub'da Çoklu Eklenti Yönetimi için En İyi Uygulamalar

Bu döküman, MesChain-Sync projesi gibi birden fazla eklenti veya modül içeren projelerin GitHub üzerinde etkin bir şekilde yönetilmesi için en iyi uygulamaları ve önerileri içermektedir.

## 1. Depo (Repository) Yapısı

Projenizin karmaşıklığına ve eklentilerin birbiriyle olan ilişkisine bağlı olarak farklı depo yapıları seçebilirsiniz.

### a. Monorepo (Tek Depo)

Tüm eklentilerin ve ana projenin tek bir Git deposunda barındırılmasıdır.

**Avantajları:**
- **Kolay Kod Paylaşımı:** Eklentiler arasında ortak kod ve kütüphanelerin paylaşımı basittir.
- **Tek Bir Gerçek Kaynağı (Single Source of Truth):** Tüm proje tek bir yerden yönetilir.
- **Atomik Değişiklikler (Atomic Commits):** Birden fazla eklentiyi etkileyen değişiklikler tek bir commit ile yapılabilir.
- **Kolaylaştırılmış Bağımlılık Yönetimi:** Tüm bağımlılıklar tek bir `composer.json` veya `package.json` dosyasında yönetilebilir.

**Dezavantajları:**
- **Büyük Depo Boyutu:** Depo zamanla çok büyüyebilir, bu da klonlama ve checkout sürelerini uzatabilir.
- **Karmaşık CI/CD Pijamaları:** Sadece değişen eklentileri test etmek ve dağıtmak için karmaşık CI/CD yapılandırmaları gerekebilir.

**Uygulama:**
- `upload/` dizinindeki her bir modül, kendi alt dizininde geliştirilir.
- Ortak kütüphaneler `system/library/meschain` gibi merkezi bir yerde tutulur.
- GitHub Actions veya GitLab CI kullanarak, sadece değişen dosyalara göre ilgili testleri ve dağıtımları tetikleyebilirsiniz.

### b. Multirepo (Çoklu Depo)

Her eklentinin kendi Git deposunda barındırılmasıdır.

**Avantajları:**
- **Bağımsız Geliştirme:** Her eklenti kendi yaşam döngüsüne sahip olabilir (sürümleme, test, dağıtım).
- **Daha Küçük ve Hızlı Depolar:** Her depo küçük ve odaklıdır.
- **Açık Kaynak için İdeal:** Eklentileri ayrı ayrı açık kaynak olarak yayınlamak daha kolaydır.

**Dezavantajları:**
- **Zor Kod Paylaşımı:** Ortak kodları yönetmek için Composer/NPM paketleri gibi çözümler gerekir.
- **Bağımlılık Yönetimi:** Eklentiler arasındaki bağımlılıkları yönetmek karmaşıklaşabilir ("dependency hell").
- **Koordinasyon Zorluğu:** Birden fazla depoda değişiklik yapmak ve bunları senkronize etmek zordur.

## 2. Dallanma (Branching) Stratejisi

Projeniz için tutarlı bir dallanma stratejisi benimsemek, geliştirme sürecini düzenli tutar. **Git Flow** veya **GitHub Flow** popüler seçeneklerdir.

### GitHub Flow (Önerilen)

Basit ve etkili bir modeldir.
- `main` dalı her zaman dağıtıma hazır (production-ready) kodu içerir.
- Yeni bir özellik veya hata düzeltmesi için `main` dalından yeni bir dal (feature branch) oluşturulur (örn: `feature/trendyol-webhook` veya `fix/n11-api-bug`).
- Geliştirme bu dal üzerinde yapılır.
- İş tamamlandığında, `main` dalına bir Pull Request (PR) açılır.
- Kod gözden geçirilir (code review), testler çalıştırılır ve onaylandıktan sonra `main` dalına birleştirilir.

## 3. Sürümleme (Versioning)

**Semantik Sürümleme (Semantic Versioning - SemVer)** kullanmak, projenizin ve eklentilerinizin sürümlerini anlamlı hale getirir.

- **MAJOR (Ana Sürüm):** Geriye dönük uyumlu olmayan API değişiklikleri.
- **MINOR (Alt Sürüm):** Geriye dönük uyumlu yeni özellikler.
- **PATCH (Yama Sürümü):** Geriye dönük uyumlu hata düzeltmeleri.

Her eklentinin ve ana projenin kendi sürüm numarasını `CHANGELOG.md` dosyasında takip edin. Git etiketleri (tags) ile sürümleri işaretleyin (örn: `git tag -a v1.2.3 -m "Sürüm 1.2.3"`).

## 4. Belgeler (Documentation)

- **README.md:** Her eklenti dizininde, o eklentinin ne işe yaradığını, nasıl kurulacağını ve yapılandırılacağını anlatan bir `README.md` dosyası bulunsun.
- **CHANGELOG.md:** Her eklenti ve ana proje için değişiklik günlüğü tutun. Bu, kullanıcıların ve geliştiricilerin yeni sürümlerdeki değişiklikleri takip etmesini kolaylaştırır.
- **CONTRIBUTING.md:** Projeye katkıda bulunma kurallarını içeren bir dosya oluşturun.

## 5. Sürekli Entegrasyon ve Dağıtım (CI/CD)

GitHub Actions kullanarak CI/CD süreçlerinizi otomatikleştirin.
- **Test:** Her Pull Request'te otomatik testleri (PHPUnit, PHPStan) çalıştırın.
- **Kod Kalitesi:** Kod kalitesi araçlarını (örn: PHP_CodeSniffer) entegre edin.
- **Dağıtım (Deployment):** `main` dalına birleştirildiğinde veya yeni bir sürüm etiketlendiğinde, projenizi otomatik olarak test veya canlı ortama dağıtın.

## Örnek Dosya Yapısı (Monorepo için)

```
.github/
  workflows/
    ci.yml             # Sürekli Entegrasyon
    cd.yml             # Sürekli Dağıtım
upload/
  admin/
    controller/extension/module/
      meschain_trendyol.php
      meschain_n11.php
      ...
  system/
    library/
      meschain/
        trendyol/
          ApiClient.php
        n11/
          ApiClient.php
...
docs/
  ...
README.md
CHANGELOG.md
CONTRIBUTING.md
composer.json
```
