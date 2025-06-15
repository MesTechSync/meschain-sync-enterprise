# 🤖 MesChain-Sync: Yapay Zeka Kullanım Rehberi

Bu dokümantasyon, MesChain-Sync projesi geliştirme sürecinde Claude, GPT veya benzeri yapay zeka modellerinin en verimli şekilde kullanılması için tasarlanmıştır.

## 📋 İçindekiler

- [Genel İlkeler](#genel-i̇lkeler)
- [Kod Analizi İçin Prompt Şablonları](#kod-analizi-i̇çin-prompt-şablonları)
- [Hata Ayıklama İçin Prompt Şablonları](#hata-ayıklama-i̇çin-prompt-şablonları)
- [Kod Üretimi İçin Prompt Şablonları](#kod-üretimi-i̇çin-prompt-şablonları)
- [Dokümantasyon İçin Prompt Şablonları](#dokümantasyon-i̇çin-prompt-şablonları)
- [Test Senaryoları İçin Prompt Şablonları](#test-senaryoları-i̇çin-prompt-şablonları)
- [En İyi Uygulamalar](#en-i̇yi-uygulamalar)

## 🧭 Genel İlkeler

Yapay zeka ile çalışırken aşağıdaki ilkeleri göz önünde bulundurun:

1. **Bağlam Sağlayın:** Yapay zekaya mevcut proje yapısı, dosya isimleri ve ilgili kodlar hakkında yeterli bağlam verin.
2. **Spesifik Olun:** Genel sorular yerine, belirli dosya ve işlevlerle ilgili sorular sorun.
3. **Aşamalı İlerleyin:** Karmaşık görevleri küçük adımlara bölün ve sırayla ilerleyin.
4. **Çıktıyı Doğrulayın:** AI çıktılarını her zaman manuel olarak kontrol edin ve test edin.
5. **Kod Stilini Belirtin:** OpenCart ve MesChain-Sync kod stillerine uygun çıktılar isteyin.

## 📝 Kod Analizi İçin Prompt Şablonları

### 1. Dosya Yapısı Analizi

```
Lütfen [klasör_yolu] klasörünün içeriğini analiz et.
Bu klasördeki dosyaların işlevlerini, birbirleriyle ilişkilerini ve genel amacını açıkla.
Eksik veya iyileştirilebilecek bileşenleri tespit et, ancak kod üretme.
```

### 2. Belirli Bir Dosyanın Analizi

```
Lütfen [dosya_yolu] dosyasını satır satır analiz et.
Dosyadaki ana işlevleri, sınıfları ve önemli yöntemleri açıkla.
Eksik veya iyileştirilebilecek bölümleri tespit et, ancak kod üretme.
Kodun OpenCart standartlarına uygunluğunu değerlendir.
```

### 3. API Entegrasyonu Analizi

```
Lütfen [pazaryeri] API entegrasyonunu analiz et.
API bağlantısı, istek gönderme, yanıt işleme ve hata yönetimi akışını açıkla.
Eksik veya iyileştirilebilecek noktaları tespit et, ancak kod üretme.
```

## 🐛 Hata Ayıklama İçin Prompt Şablonları

### 1. Hata Tespiti

```
Aşağıdaki kodda bir hata alıyorum:

[hata_mesajı]

İlgili kod parçası:

[kod_parçası]

Bu hatanın nedenini ve nasıl düzeltilebileceğini açıkla.
```

### 2. Performans İyileştirme

```
Aşağıdaki kod parçası performans sorunlarına neden oluyor:

[kod_parçası]

Bu kodu daha verimli hale getirmek için öneriler sun, ancak doğrudan kod üretme.
```

### 3. Güvenlik Açığı Analizi

```
Aşağıdaki API entegrasyon kodunu güvenlik açısından analiz et:

[kod_parçası]

Potansiyel güvenlik açıklarını ve iyileştirme önerilerini belirt, ancak doğrudan kod üretme.
```

## 💻 Kod Üretimi İçin Prompt Şablonları

### 1. Yeni Controller Oluşturma

```
Lütfen [pazaryeri] için OpenCart uyumlu bir controller oluştur.
Controller, şu işlevleri içermeli:
- Dashboard görünümü
- API ayarları sayfası
- Ürün yönetimi
- Sipariş yönetimi

Dosya yolu: upload/admin/controller/extension/mestech/[pazaryeri]/controller.php

OpenCart standartlarına uygun ve MesChain-Sync mimarisine entegre olacak şekilde kod üret.
```

### 2. API Entegrasyon Sınıfı Oluşturma

```
Lütfen [pazaryeri] için bir API entegrasyon sınıfı oluştur.
Sınıf, şu işlevleri içermeli:
- API bağlantısı kurma
- Ürün listeleme/güncelleme
- Sipariş çekme/güncelleme
- Kategori çekme
- Hata yönetimi ve loglama

Dosya yolu: upload/system/library/entegrator/[pazaryeri].php

Mevcut logger.php sınıfını kullanacak şekilde ve try-catch blokları ile hata yönetimini içerecek şekilde kod üret.
```

### 3. Twig Şablonu Oluşturma

```
Lütfen [pazaryeri] için bir dashboard Twig şablonu oluştur.
Şablon, şu bileşenleri içermeli:
- İstatistikler (toplam ürün, sipariş, vb.)
- Son siparişler tablosu
- Hızlı işlem butonları
- API durum göstergesi

Dosya yolu: upload/admin/view/template/extension/mestech/[pazaryeri]/dashboard.twig

Bootstrap ve FontAwesome kullanan, OpenCart admin paneli tarzında bir şablon üret.
```

## 📄 Dokümantasyon İçin Prompt Şablonları

### 1. README.md Oluşturma

```
Lütfen [pazaryeri] modülü için bir README.md oluştur.
Dokümantasyon şunları içermeli:
- Modülün genel açıklaması
- Kurulum adımları
- Konfigürasyon seçenekleri
- Temel kullanım
- Sorun giderme

Markdown formatında, açık ve anlaşılır bir dokümantasyon üret.
```

### 2. TODO.md Oluşturma

```
Lütfen [pazaryeri] modülü için bir TODO.md oluştur.
Dokümantasyon şunları içermeli:
- Tamamlanmış işlerin listesi
- Devam eden işlerin listesi
- Planlanmış gelecek geliştirmeler
- Bilinen sorunlar veya kısıtlamalar

Markdown formatında, kontrol listeleri kullanarak düzenli bir yapıda oluştur.
```

## 🧪 Test Senaryoları İçin Prompt Şablonları

### 1. Manuel Test Senaryoları Oluşturma

```
Lütfen [pazaryeri] modülü için manuel test senaryoları oluştur.
Test senaryoları şu alanları kapsamalı:
- API bağlantı testleri
- Ürün senkronizasyon testleri
- Sipariş senkronizasyon testleri
- Hata durumları testleri
- Arayüz testleri

Her test senaryosu için adım adım talimatlar ve beklenen sonuçlar içeren bir liste oluştur.
```

### 2. API Test Senaryoları Oluşturma

```
Lütfen [pazaryeri] API entegrasyonu için test senaryoları oluştur.
Test senaryoları şu endpoint'leri kapsamalı:
- Ürün listesi çekme
- Ürün güncelleme
- Sipariş listesi çekme
- Sipariş durum güncelleme

Her API çağrısı için örnek istek parametreleri, beklenen yanıtlar ve hata durumları içeren senaryolar oluştur.
```

## 🌟 En İyi Uygulamalar

### 1. Analiz İş Akışı

1. Önce genel proje yapısını anlamak için `PROJECT_OVERVIEW.md` ve `STRUCTURE.md` dosyalarını yapay zekaya verin.
2. Ardından belirli bir modül veya dosya hakkında detaylı analiz isteyin.
3. Son olarak, tespit edilen sorunlar veya iyileştirmeler için öneriler alın.

### 2. Geliştirme İş Akışı

1. Önce mevcut benzer modülleri analiz edin ve anlaşılmasını sağlayın.
2. Yeni modül için gereksinim ve yapı tanımı yapın.
3. Controller, model, view ve dil dosyalarını sırayla oluşturun.
4. Oluşturulan kodları test edin ve hataları düzeltin.
5. Dokümantasyon ve test senaryolarını hazırlayın.

### 3. Kod Kalitesi İçin İpuçları

- Yapay zekadan alınan kodu her zaman manuel olarak inceleyin ve test edin.
- OpenCart kod stiline uygun olduğundan emin olun.
- Hata yönetimi ve loglama mekanizmalarının eklendiğinden emin olun.
- Güvenlik önlemlerinin uygulandığından emin olun.
- Kodun modüler ve bakımı kolay olduğundan emin olun.

Bu rehber, MesChain-Sync projesi geliştirme sürecinde yapay zeka araçlarının verimli kullanımını sağlamak için tasarlanmıştır. 