Harika. Yazılım temizliği, yapılandırma planlaması ve Cursor.ai üzerinden sürdürülebilir gelişim için eksiksiz bir yol haritası oluşturacağım.

* Yazılımda gereksiz, çalışmayan ya da tekrar eden dosyaların belirlenmesi
* Atomik ve modüler yapıya uygun sistemsel yeniden düzenleme
* Log, config, modül ve backup mantığını destekleyecek klasör mimarisi önerisi
* Cursor.ai’nin yazılıma tam hâkim olması için yönlendirme yapısı
* İlk odaklanılacak modül: Genel sistem mimarisi, ardından Trendyol

Rapor, `.md` formatında oluşturulacak ve Cursor’a doğrudan yüklenebilir yapıda olacak.

Kısa süre içinde sana detaylı planı sunacağım.


# MesTech Çoklu Pazaryeri Entegrasyon Projesi Yol Haritası

## Sistem Mimarisi

&#x20;*Şekil: OpenCart’ın MVC(L) tabanlı mimarisi. Kullanıcı istekleri denetleyiciler (controller) aracılığıyla modellere (model) ve görünümlere (view) yönlendirilir.* OpenCart 3.0.4.0 altyapısı, ayrı katalog (müşteri) ve yönetici (admin) bölümleriyle iki uygulamaya ayrılan MVC(L) tasarım desenini kullanır. Bu yapıda her modül, `admin/controller/extension/module` gibi belirli klasörlere yerleştirilecek kendi denetleyicilerine, modellerine, şablonlarına ve dil dosyalarına sahiptir. Projenin genel mimarisi, bu katmanlı yaklaşımı baz alarak tasarlanmalı ve belgelendirilmelidir. Örneğin sipariş yönetimi, ürün senkronizasyonu veya bildirim gibi işlevsel bloklar net hatlarla ayrılarak, her bir modülün bağımsız çalışması ve genişletilebilir olması sağlanmalıdır.

## Dosya Yapısının Analizi ve Temizlenmesi

Projede **atomic** prensiplere uygun olarak dosyalar küçük ve bağımsız bileşenler şeklinde düzenlenmelidir. Gereksiz ve tekrar eden dosyalar (örneğin hiç kullanılmayan kontrolörler, modeller veya kopya şablonlar) tespit edilip silinmelidir. Tekrarlanan kod parçaları DRY (*Don’t Repeat Yourself*) ilkesiyle birleştirilerek, bakım kolaylaştırılmalıdır. Atomic Design yaklaşımında olduğu gibi her bileşen “küçük ve yeniden kullanılabilir” birimlere bölünmelidir. Bu süreçte dosya ve klasör adlandırmaları standart hale getirilmeli, versiyon kontrolüne uygun bir şema kurulmalıdır. Örneğin, modül dosyaları belirli isimlendirme kuralları (CamelCase veya küçük harf) izleyerek düzenlenebilir.

## Dokümantasyonun Korunması

Proje ile ilgili bilgi içeren dökümantasyon dosyaları (README, geliştirici notları, API referansları vb.) proje içinde **saklanmalıdır**. Bu dosyalar sisteme ek yük getirmeyen açıklayıcı kaynaklardır ve Cursor.ai gibi araçların bağlam oluşturmasına yardımcı olur. Kaynak kodu yönetiminde `docs/` gibi ayrı bir klasör altında güncel tutulan Markdown dosyaları, projenin fonksiyonlarını ve tasarım kararlarını açıklar. Ayrıca, `.cursorrules` dosyası oluşturularak kodlama standartları, yorumlama yönergeleri veya projenin amaçları AI’ya iletilebilir. Bu sayede yapay zeka geliştiriciye proje rehberi olarak markdown dosyalarını ve kuralları otomatik bağlamda sunabilir.

## Kod, Konfigürasyon ve Log Dosyalarının Organizasyonu

Kod, konfigürasyon ve loglar ayrı klasörlerde düzenlenmelidir. Örneğin; kaynak kodlar `src/` veya `code/`, yapılandırma dosyaları `config/` gibi klasörlerde toplanabilir. Yapılandırma dosyaları, koddan tamamen ayrılarak ayrı tutulmalı, böylece uygulama davranışı kod değiştirilmeden güncellenebilir. Hassas bilgiler (API anahtarları vb.) `.env` veya benzeri ortam dosyalarında saklanmalı, doğrudan versiyon kontrolüne dahil edilmemelidir. Log dosyaları için OpenCart’ın standart konumu kullanılmalıdır: her modül örneğin `$log = new Log('moduladi.log'); $log->write(...)` şeklinde oluşturulacak günlükler otomatik olarak `system/logs/` dizinine yazılır. Her modül kendi log dosyasını (örn. `trendyol.log`) oluşturarak hataları ve uyarıları kaydetmelidir. Kritik yapılandırmalar ve veritabanı düzenli aralıklarla yedeklenmeli, proje dizininde `backups/` gibi bir klasör altında saklanacak şekilde planlanmalıdır. Bu yapı, ileride GitHub gibi bir kaynak kod yönetim sistemine geçişi kolaylaştırır.

## Cursor.ai İçin Proje Rehberi

Projenin Cursor.ai gibi bir AI kod yardımcı aracıyla sürdürülebilir geliştirmesi için kapsamlı bir kılavuz hazırlanmalıdır. Proje kökünde `.cursorrules` dosyası oluşturarak; kod stil kuralları, tercih edilen kütüphaneler veya güncel olmayan yöntemler gibi “sistem düzeyinde” talimatlar verilebilir. Ayrıca, fonksiyonel açıklamalar içeren markdown dosyaları (`README.md`, modül tanımları vb.) proje bağlamını ortaya koymak için kullanılmalıdır. Cursor.ai’ın Composer ve Chat özellikleri bağlamı projenin tamamından aldığı için bu dosyalar *geliştirici istemlerinde* belirtilerek yapay zekaya aktarılmalıdır. Örneğin bir prompt’ta “Bakınız proje\_kilavuzu.md dosyası” diye referans vererek AI’nin projenin detaylarını anlaması sağlanabilir. Ayrıca kod gözden geçirme veya veri dönüşümü gibi yardım taleplerinde bu rehber dosyaları bağlam olarak sunulmalıdır. Cursor.ai’ın tüm projeyi göz önünde bulundurarak öneri üretme özelliği sayesinde, tutarlı bir stil sağlamak için bu dökümantasyon ve kural dosyalarından yararlanılır.

## Geliştirme Öncelikleri

1. **Genel Mimari ve Altyapı:** Mevcut kod analiz edilip temizlenmeli, projeyi modüler hâle getirecek şekilde klasör yapısı ve veri modelleri tasarlanmalıdır. API iskeletleri oluşturulmalı, genel hata işleme ve loglama altyapıları hazır hale getirilmelidir.
2. **Temel Yapılandırma:** Proje depo ve klasör yapısı (`config/`, `logs/` vb.) oluşturulmalı, `.cursorrules` ve dokümantasyon dosyaları eklenmelidir. Veritabanı şeması ve gerekli tablolar hazırlanmalıdır.
3. **Trendyol Modülü:** İlk entegrasyon olarak Trendyol modülü geliştirilmelidir. Trendyol API’sine bağlantı kurulup ürün ve sipariş senkronizasyonu test edilmelidir. (Ayrıntılar modül planında aşağıda.)
4. **Diğer Modüller (Öncelik Sırasıyla):** Trendyol’dan sonra sırasıyla Amazon, N11, eBay, Hepsiburada ve Ozon modülleri planlanarak geliştirilecektir. Her biri için önce basit bir “hello world” entegrasyonu (örneğin test amaçlı basit API çağrısı) oluşturulmalı, ardından kapsamlı sipariş/ürün akışları eklenmelidir.
5. **Test ve Entegrasyon:** Her aşamada birimler halinde testler yazılmalı, yeni değişikliklerin geriye dönük uyumluluğu kontrol edilmelidir. Cursor.ai’dan gelen kod önerileri mevcut testlere karşı denenmelidir.

## Modül Bazlı Planlama

* **Trendyol Modülü:** Trendyol için gerekli API anahtarları `config/` klasöründe ayrı bir dosyaya veya OpenCart ayarlarına kaydedilmelidir. Modül `admin/controller/extension/module/trendyol.php` vb. yollarda MVC şeklinde oluşturulur. Sipariş, ürün ve stok senkronizasyonu işlemleri için özel *helper* sınıfları yazılmalıdır. Örneğin **`TrendyolApiHelper`** sınıfı API iletişimi, **`TrendyolLogger`** ise hata/olay kaydı için kullanılabilir. Hataları ve önemli olayları kaydetmek için `$log = new Log('trendyol.log')` ile `system/logs/` altına günlük yazılmalıdır.
* **Amazon Modülü:** Amazon SP-API ile entegrasyon sağlanacak. Gerekli kimlik bilgileri `config/amazon.php` gibi ayrı bir dosyada tutulmalı, farklı pazarlar (ör. amazon.com vs amazon.com.tr) desteklenmeli. Amazon modülü için **`AmazonApiHelper`** gibi yardımcı sınıflar ve isteklere özgü istisna sınıfları oluşturulmalıdır. Loglama `amazon.log` dosyasında tutulur ve detaylı hata raporları aktif edilmelidir.
* **N11 Modülü:** Türkiye’nin N11 pazaryeri için geliştirilecek. N11 API sertifikaları `config/n11.php` içinde saklanmalıdır. **`N11ApiHelper`** ile ürün listeleme, stok güncelleme vb. işlemler tek bir noktadan yapılmalıdır. Loglama için `n11.log` kullanılır.
* **Hepsiburada Modülü:** Hepsiburada API erişimi için kullanıcı bilgileri ayrı tutulur. Ürün eşleme ve sipariş işlemleri **`HepsiApiHelper`** sınıfında işlenir. Olası farklı veri formatları (XML/JSON) bu sınıfa göre ele alınmalıdır. Log dosyası `hepsiburada.log` şeklinde düzenlenir.
* **eBay Modülü:** eBay REST API kullanarak global entegrasyon sağlanacak. OAuth token yönetimi ve global site ID’leri yönetimi `config/ebay.php` içinde yer alır. **`EbayApiHelper`** ile ürün listeleme ve sipariş çekme işlemleri kodlanır. Günlükler `ebay.log` dosyasına yazılır.
* **Ozon Modülü:** Ozon için (Rusya pazaryeri) API anahtarları `config/ozon.php` altında saklanmalıdır. **`OzonApiHelper`** sınıfı ile ürün ve sipariş yönetimi yapılır. Loglama `ozon.log` dosyasında tutulmalı, talep ve dönüşüm logları detaylı tutulmalıdır.

Her modülde ortak olarak, **loglama** `$log = new Log('moduladi.log')` kullanılarak `system/logs/` altında yapılmalı. **Config** dosyaları proje kökünde `config/` klasöründe tutulup, **helper** sınıfları aracılığıyla API entegrasyonu soyutlanmalıdır. Bu yapı, Cursor.ai ile her modülün geliştirilmesinde tutarlı bir yaklaşım sergilenmesini sağlayacaktır.

**Kaynaklar:** OpenCart modül geliştirme ve mimari kılavuzları, Atomic Design prensipleri, sürüm kontrolünde yapılandırma dosyalarının rolü, ve Cursor.ai kullanım ipuçları esas alınmıştır.
