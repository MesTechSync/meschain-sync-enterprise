markdown
# Çoklu Pazaryeri – Dropshipping Kontrol Paneli
OpenCart tabanlı, çok kullanıcılı ve rol bazlı, **“hepsi-bir-arada”** pazaryeri & dropshipping yönetim platformu  
Sürüm: 1.0   Tarih: 31-05-2025  
Hazırlayan: {Proje Ekibi}

---

## 1. Proje Amacı
•  Tek bir kontrol panelinden Amazon, Etsy, eBay, Trendyol, Hepsiburada, N11, ÇiçekSepeti, PttAVM vb. pazaryerlerinde  
 ürün, stok, fiyat ve siparişleri gerçek-zamanlı yönetmek  
•  Aynı envanteri dropshipping müşterilerine (B2B) katalog olarak açmak  
•  Rol bazlı erişim (Süper Admin, Admin, Entegratör, Teknik Servis, Dropshipper)  
•  Tamamı OpenCart (≥4.0) çekirdeği korunarak; modüler, API-odaklı, sürdürülebilir bir mimari

---

## 2. Paydaşlar & Roller
| Rol                 | Temel Yetkiler                                                                                 |
|---------------------|------------------------------------------------------------------------------------------------|
| Süper Admin         | Tüm ayarlar, pazar yeri anahtarları, kullanıcı/rol yönetimi, log & raporlar                   |
| Admin              | Mağaza konfigürasyonu, ürün ve sipariş yönetimi, katalog haritalama                            |
| Entegratör         | Pazaryeri API kimlik bilgileri ekleme, cron & webhook ayarları                                 |
| Teknik Servis      | Bakım modları, hata kayıtları, kuyruk (queue) denetimi, güncelleme                              |
| Dropshipper        | Atanmış katalogdan ürün seçme, kendi fiyatını belirleme, sipariş oluşturma & takip             |

---

## 3. Fonksiyonel Gereksinimler
1. Pazaryeri Bağlantısı  
   • OAuth2 / token tabanlı kimlik doğrulama sihirbazı  
   • Ürün – kategori – özellik eşleme (mapping)  
   • İki yönlü stok & fiyat senkronizasyonu  
   • Sipariş çekme, statü güncelleme, kargo barkodu gönderme

2. Dropshipping Modülü  
   • Tedarikçi ürünü → Dropshipper kataloğu → Pazaryeri ilanı  
   • Kâr yüzdesi / sabit marj kuralları  
   • Toplu ürün aktarma & varyant yönetimi

3. Kontrol Paneli  
   • Rol tabanlı menü / “feature flag”  
   • Takvim & görev listesi (cron, bakım, kampanya)  
   • Gerçek-zamanlı uyarılar (WebSocket + Toastr)  
   • Özet KPI (sipariş, iade, stok, satış hacmi)

4. Raporlama & Log  
   • Pazaryeri performans tabloları (SL, ODR, IPH, vb.)  
   • API hata & istek/saniye grafikleri  
   • CSV/XLSX dışa aktarma

---

## 4. Sistem Mimarisi

┌───────────────────┐ ┌──────────────────┐
│ React/Vue UI │ WebSocket │ API Gateway │
└────────┬──────────┘ └────────┬─────────┘
│ REST │
▼ ▼
OpenCart Core (Admin) Marketplace Micro-services
│ Events/OCMOD (Amazon, Etsy, eBay,
▼ Trendyol, …)
Service Layer (PHP) │
│ ▼
Message Queue (RabbitMQ) ──► Worker Pods (Node/PHP)
│ │
MySQL 8 ◄─────────────── Redis Cache ◄────────Cron/Jobs

yaml

• UI: mevcut OpenCart admin + Single-Page “Dashboard” (React/Vite)  
• API Gateway: Slim-PHP veya Lumen, JWT ile yetkilendirme  
• Her pazaryeri için bağımsız container (Docker, Kubernetes opsiyonel)  
• Queue tabanlı işlemler = daha hızlı panel, yeniden deneme (retry) kabiliyeti

---

## 5. Veritabanı Şeması (özet)

oc_marketplace oc_mp_product_map

marketplace_id PK - map_id PK
name - product_id FK(oc_product)
base_url - marketplace_sku
auth_type - marketplace_id FK
creds_json - status, last_sync
oc_mp_order_map oc_dropship_catalog

order_id PK - catalog_id PK
marketplace_order_id - supplier_product_id FK
marketplace_id FK - dropshipper_id FK
raw_payload JSON - selling_price, margin
sync_status
yaml

---

## 6. Entegrasyon Detayları

| Pazaryeri  | Yetkili API | Önemli Son Noktalar | Cron/Webhook |
|------------|-------------|--------------------|--------------|
| Amazon SP-API | Orders, Listings, Feeds | `/orders/v0`, `/listings/2021-08-01/items` | Webhook (SNS) |
| Etsy v3     | Listings, Inventory, Shipping | `/application/shops/{shop_id}/listings` | Cron 15 dk |
| eBay Sell   | Inventory, Fulfillment | `/sell/inventory/v1`, `/sell/fulfillment/v1` | Webhook |
| Trendyol    | Open API v2 | `/products`, `/orders` | Cron 10 dk |
| Hepsiburada | HB Open API | `/items`, `/orders` | Cron 10 dk |
| N11         | SOAP/REST   | `ProductService`, `OrderService` | Cron 10 dk |
| …           | …           | … | … |

Tüm servislerde:
• Rate-limit kontrolü (Redis leaky-bucket)  
• Request/Response log’u (DB + Sentry)  
• Mapping tablosu zorunlu (SKU ↔ PazarSKU)

---

## 7. Güvenlik & Uyumluluk
• TLS 1.3, HSTS, CSP  
• OAuth2 PKCE flow, refresh token saklama = AES-256-GCM  
• Yetki matrisi → OpenCart `user_group` + custom ACL middleware  
• GDPR/KVKK: kişisel veri maskeleme, veri imha prosedürü  
• PCI-DSS’e uygun barındırma (ödeme yönlendirmesi yapılır)

---

## 8. CI/CD & Geliştirme Süreci
• Kod editörü: VS Code + devcontainer.json  
• GitHub Actions:  
  – PHPUnit, PHPStan, ESLint  
  – Docker build & push (staging, prod)  
• Semantic versioning, conventional commits  
• Otomatik changelog & tag

---

## 9. Yol Haritası (12 Haftalık)

| Hafta | Başlık                              | Çıktı                        |
|-------|-------------------------------------|------------------------------|
| 1-2   | İhtiyaç analizi, mimari POC         | Dokümantasyon, ER diyagramı |
| 3-4   | OpenCart çekirdek modifikasyonları  | Module skeleton, ACL        |
| 5-6   | Amazon + Trendyol entegrasyonu      | İlk ürün & sipariş senk.    |
| 7-8   | Diğer pazar yerleri + queue sistemi | Çoklu worker, retry logic   |
| 9     | Dropshipping kataloğu & marj mot.   | UI + fiyat kuralları        |
| 10    | Raporlama & log ekranları           | KPI dashboard               |
| 11    | Güvenlik testleri, load test        | JMeter raporu               |
| 12    | Canlıya geçiş, kullanıcı eğitim     | Production release          |

---

## 10. Bakım & Ölçeklenebilirlik
• Yatay ölçek (Docker Swarm/K8s) → worker pod sayısı = istek hacmi  
• Failover MySQL (Group Replication)  
• Log/metric: Prometheus + Grafana + Loki  
• Otomatik backup (mysqldump + S3)  
• Modüler kod → yeni pazaryeri eklemek ≈ 3-5 gün

---

## 11. Riskler & Önlemler
1. Pazaryeri API limit/tier değişimi → Versiyon takip & “adapter” katmanı  
2. Stok uyuşmazlığı → Anlık rezerv + düşük stok eşiği uyarısı  
3. Kur farkı / komisyon dalgalanması → Dinamik fiyat kural motoru  
4. OpenCart çekirdek güncellemeleri → OCMOD yerine Event/Override, CI testleri

---

## 12. Ek Kaynaklar
• OpenCart Developer Docs: https://docs.opencart.com  
• Amazon SP-API Guides, Etsy OpenAPI, eBay Sell API  
• Trendyol Geliştirici Portalı, Hepsiburada API, N11 Doküman  
• PHP League OAuth2-Client, GuzzleHTTP, React-Admin

---

> Bu doküman, VS Code üzerinde geliştirilecek OpenCart tabanlı çoklu pazaryeri & dropshipping platformu için yüksek seviyeli analiz ve teknik gereksinimleri özetler. Detaylı UML diyagramları, API şema dosyaları (`/docs/openapi/*.yaml`) ve sprint backlog’u ayrıca paylaşılacaktır.


Tabii, ihtiyacınız olan platformun özelliklerini ve teknik gereksinimlerini detaylandıran kapsamlı bir analiz raporu hazırlayabilirim. Analizi ve teknik şartları Markdown (md) dosya formatında, platformun mimari ve süreçlerine odaklanarak aşağıda listeliyorum.

Çok Kullanıcılı, Rol Bazlı Çoklu Pazaryeri & Dropshipping Yönetim Platformu Analiz Raporu
1. Proje Tanımı
Bu proje, OpenCart altyapısı kullanılarak geliştirilecek çok kullanıcılı, çok rollü, tüm büyük yerli/yabancı pazaryerleri ile entegre çalışabilen, dropshipping odaklı bir yönetim ve sipariş platformudur. Temel amaç:

Farklı pazaryerlerindeki mağaza ve ürünlerin tek panelden yönetimi,
Her kullanıcı rolünün ihtiyaçlarına uygun bir kontrol paneli sunmak,
Çoklu mağaza/dropshipping katmanında idari ve teknik kolaylık sağlamak.
2. Temel Bileşenler ve Modüller
2.1 Kullanıcı Rolleri
Süper Admin: Tüm sistem ve kullanıcıları yönetir, raporları ve altyapıyı görür
Admin: Kendi pazar yeri mağazaları ve kullanıcılarını yönetir
Teknik Servis: Entegrasyon, hata ve sorun destek kullanıcıları
Entegratör: API entegrasyonlarını kuran/geliştiren kullanıcı
Dropshipping Kullanıcısı: Panelde belirlediği ürünleri seçip kendi mağazalarına aktarır, tüm tedarik/sipariş yönetimini yapar
Müşteri/Toptancı: Ürünleri yükleyen veya satışa çıkartan kullanıcı
2.2 Pazaryeri ve Ürün Yönetimi
Yerel pazaryerleri: Trendyol, Hepsiburada, N11, PttAVM, ÇiçekSepeti, GittiGidiyor, vb.
Global pazaryerleri: Amazon, Etsy, eBay, AliExpress, Walmart, Alibaba, Wayfair, vb.
Çoklu mağaza ekleme & yetkilendirme (her kullanıcıya 1+ mağaza)
Pazaryeri API bağlantıları ile:
Ürün çekme, stok/güncel durum, sipariş yönetimi
Entegre fiyat, stok ve kategori eşleştirme
2.3 Dropshipping Entegrasyonu
Kendi ürün portföyünden seçilecek ürünlerle dropshipping mağazası yönetimi
Otomatik ürün aktarımı & mağazalara yayımı
Sipariş geldiğinde otomatik yönlendirme ve entegrasyon
Entegre fiyat kuralı, kar marjı, kupon kampanyası opsiyonu
2.4 Panel ve Takvim Yönetimi Fonksiyonları
Tüm kullanıcılar için rol bazlı özel dashboard
Mağaza yönetim ekranı: mağaza hedefleri, anlık istatistikler, satış/sipariş yönetimi
Takvim ve etkinlik yönetimi (Randevu, görev atama, kampanya tarihleri)
Bildirim merkezi: Sipariş, mesaj, entegrasyon hatası/sorunu vb.
Süper admin/teknik ekip için sistem genel uyarı, bakım, raporlama.
2.5 API ve Entegrasyon Yapısı
Pazaryerlerine RESTful API ile bağlanabilme
Geliştirilebilen modüler entegrasyon yapısı (her pazar yeri için ayrı connector)
Otomatik/kısmi ürün güncelleme, veri çekme/senkronizasyon için zamanlanmış görevler (cronjob)
API üzerinden kullanıcı/müşteri yetkilendirmesi (OAuth, API Key)
2.6 Yönetim Paneli / Kontrol Paneli Özellikleri
Ortak Özellikler
Responsive, çoklu dil desteği
Rol bazlı yetkilendirme ve menü özelleştirme
Analitik dashboard (satış, kazanç, sipariş, mağaza sağlığı)
Gelişmiş arama & filtreleme
Raporlama ve veri ihracı (CSV/Excel/PDF)
Yönetici/Süper Admin
Tüm mağazalar ve kullanıcılar üzerinde tam kontrol
API anahtar yönetimi
Sistem altyapı sağlığı, log yönetimi
Erişim ve izin/rol tanımları
Dropshipper Kullanıcı
Mağaza ekleme, ürün/içerik aktarımı
Sipariş modülü: Kendi ve bağlı tedarikçilerden gelen/giden siparişler
Ürün güncelleme, envanter takibi
Destek & mesaj sistemi
Entegratör/Teknik Destek
API bağlantı log/istatistiklerini görüntüleme
Entegrasyon test senaryoları ve hata ayıklama ekranı
Zamanlanmış görevleri takip & yönetim
2.7 Güvenlik ve Sürdürülebilirlik
JWT tabanlı oturum, token/cookie tabanlı yetkilendirme
Açık API anahtarı gizliliği
Her role özel erişimin sınırlandırılması
Orta/uzun vadede microservice veya plugin tabanlı modüler altyapı konumu
3. Teknik Altyapı ve Kullanılacak Teknolojiler
Back-end: OpenCart çekirdeği (PHP), REST API modülleri, ek PHP tabanlı API connector’lar
Front-end: OpenCart admin, özelleştirilmiş panel (Bootstrap, React veya Vue.js ile yönetim paneli modülleri)
Veritabanı: MySQL/MariaDB
API & Entegrasyon: cURL, Guzzle, OpenCart modül yapısı ile
Sunucu: LAMP/LEMP stack, yük dengelemeye açık yapı
Ekstra araçlar: Redis (önbellek), RabbitMQ (mesaj/sipariş kuyruğu)
Takvim/Etkinlik: open-source Calendar modülleri (örn. FullCalendar)
Loglama: Monolog veya OpenCart log modülü
4. Kullanıcı Akışları (Örnek Senaryolar)
Dropshipper İçin
Paneline giriş yapar.
Ürün listesinden istediği ürünleri seçer, ‘Kendi mağazama aktar’ der.
Hangi pazaryerine aktaracağını seçer.
O pazaryerinin mağazası ile eşleşen kategoride ürün yayına alınır.
Sipariş geldiğinde panelde/siparişlerim ekranında takip eder.
Admin İçin
Kullanıcı ve mağaza yönetimi yapar.
API anahtarlarını düzenler.
Raporda satış trendlerini görür, kullanıcı aktivitelerini inceler.
5. Sürdürülebilirlik ve Geliştirilebilirlik
API tabanlı açık/kapalı marketplace ekleme çıkarmada kolaylık.
Her pazar yeri için ayrı connector ile yenilenebilir ek modüller.
Geliştirici dokümantasyonu.
Uzaktan güncellenebilir API/connector modülleri.
Veritabanı ve dosya yedekleme planı.
6. Mevcut Pazaryeri API Kaynakları (örnek linkler)
Tablo
Pazaryeri	API Dökümantasyon Linki
Amazon	https://developer.amazonservices.com/
Etsy	https://developers.etsy.com/documentation/
eBay	https://developer.ebay.com/tools
Trendyol	https://developers.trendyol.com.tr/
Hepsiburada	https://developer.hepsiburada.com/
N11	https://api.n11.com/
Diğer pazaryerleri için resmi doküman ve entegrasyon partnerleri takip edilmelidir.

7. Teknik ve İdari Süreçler
İlk analiz/değerlendirme: Müşteri ihtiyaç analizi ve gereksinimler
Dokümantasyon: Her adım için developer ve kullanıcı dokümanları hazırlanması
Test Süreçleri: API, panel, kullanıcı akışı ve entegrasyon testleri
Kullanıcı eğitimi: Rol bazlı eğitim materyali
8. Ekstra Notlar & Öneriler
Tüm süreci modüler plugin sistemi ile yönetmek, sürdürülebilirliği kolaylaştırır.
API güvenliğine özellikle dikkat edilmeli, pazar yeri yetkilendirmelerinde minimum erişim (scoped) sağlanmalı.
Müşteri ve yönetici için bilgi/veri paneli sade & hızlı olmalı, tablo ve grafiklerle desteklenmeli.
Loglama ve hata ayıklama her aşamada açık olmalı.
9. Yol Haritası (Roadmap)
Analiz & Proje Tasarım
MVP (Temel modüllerle prototip)
Tüm Pazaryerleri Entegrasyonu
Rol Bazlı Panel Geliştirmesi
Dropshipping Kurgusu & Otomasyon
Test & Canlıya Alma
Destek ve Eğitim
10. Özet
Bu platform; çoklu kullanıcı, çoklu rol, çoklu mağaza ve tüm büyük pazaryerleri entegrasyonuna sahip, sürdürülebilir ve modüler bir çözüm olacak. Gelişmiş API yönetimi ve rol temelli idiari/teknik kontrol ile pazar yeri yönetimini en üst seviyeye çıkaracaktır.

Ek detay, örnek panel ekranı, teknik akış diyagramı veya daha fazla teknoloji önerisi isterseniz detaylandırabilirim.

Bu raporu .md dosyası olarak kullanabilir, revize ve ek gelişmeler için tekrar ulaşabilirsiniz!


OpenCart Çok Kullanıcılı Pazaryeri Platformu Analiz Raporu
Proje Özeti
Bu proje, OpenCart platformu üzerinde çok kullanıcılı ve çoklu pazaryeri entegrasyonlarını destekleyen kapsamlı bir e-ticaret çözümü geliştirmeyi amaçlamaktadır. Sistem, Amazon, Etsy, eBay ve Türkiye'deki tüm pazaryerlerini destekleyecek ve dropshipping iş modeline uygun olarak tasarlanacaktır.

Sistem Mimarisi
Temel Yapı
OpenCart Çekirdeği: Temel e-ticaret fonksiyonları için OpenCart'ın mevcut altyapısı kullanılacak
Çok Kullanıcılı Sistem: Farklı rol ve izinlere sahip kullanıcı tipleri
Pazaryeri Entegrasyonları: API tabanlı entegrasyonlar
Merkezi Kontrol Paneli: Tüm modülleri tek noktadan yönetme imkanı
Kullanıcı Rolleri ve İzinler
Süper Admin

Tüm sistem üzerinde tam kontrol
Kullanıcı rollerini ve izinlerini yönetme
Platform genelindeki ayarları yapılandırma
Admin

Belirli pazaryerleri veya satıcıları yönetme
Raporları görüntüleme ve analiz etme
Sınırlı sistem ayarlarına erişim
Teknik Servis

Sistem hatalarını giderme
Entegrasyon sorunlarını çözme
Teknik destek sağlama
Entegratör

Yeni pazaryeri entegrasyonları oluşturma
Mevcut entegrasyonları güncelleme
API bağlantılarını yönetme
Dropshipping Kullanıcısı

Ürün kataloglarına erişim
Sipariş oluşturma ve takip etme
Kendi müşterilerine özel panel erişimi
Pazaryeri Entegrasyonları
Desteklenen Platformlar
Amazon (Global ve Türkiye)
Etsy
eBay
Trendyol
Hepsiburada
N11
GittiGidiyor
Diğer Türk pazaryerleri
Entegrasyon Özellikleri
Ürün bilgilerini çekme ve senkronize etme
Stok durumlarını gerçek zamanlı takip etme
Fiyat güncellemelerini otomatik yönetme
Sipariş bilgilerini alma ve işleme
Müşteri mesajlarını yönetme
Teknik Detaylar
Backend Yapısı
bash
/admin
  /controller
    /marketplace
      /amazon.php
      /etsy.php
      /ebay.php
      /trendyol.php
      ...
  /model
    /marketplace
    /users
    /roles
  /view
    /template
      /marketplace
      /dashboard
API Entegrasyon Yapısı
RESTful API mimarisi
OAuth 2.0 güvenlik protokolü
Webhook desteği
Zamanlanmış görevler için cron job yapısı
Veritabanı Şeması Genişletmeleri
oc_marketplace_integrations - Pazaryeri entegrasyonları
oc_user_roles - Kullanıcı rolleri
oc_user_permissions - Kullanıcı izinleri
oc_marketplace_products - Pazaryeri ürünleri
oc_dropshipping_orders - Dropshipping siparişleri
Kontrol Paneli Özellikleri
Dashboard
Tüm pazaryerlerinden satış istatistikleri
Sipariş durumları ve özet bilgiler
Stok uyarıları ve dikkat edilmesi gereken noktalar
Performans grafikleri
Takvim Görünümü
Önemli satış dönemleri
Pazaryeri kampanyaları
Planlanan stok güncellemeleri
Sipariş teslim tarihleri
Sipariş Yönetimi
Tek ekrandan tüm pazaryeri siparişlerini görüntüleme
Sipariş durumu güncelleme ve takip
Toplu işlem yapabilme
Dropshipping siparişlerini doğrudan tedarikçiye yönlendirme
Ürün Yönetimi
Merkezi ürün kataloğu
Pazaryerlerine göre özelleştirilebilir ürün bilgileri
Toplu ürün güncelleme
Otomatik fiyatlandırma kuralları
Entegrasyon Ayarları
API anahtarlarını yönetme
Senkronizasyon sıklığını ayarlama
Hata loglarını görüntüleme
Test modunu etkinleştirme
Teknik Gereksinimler
Sunucu Gereksinimleri
PHP 8.0+
MySQL 8.0+
Yeterli RAM (min. 4GB)
SSD depolama
Güvenilir hosting (yüksek uptime)
Ek Yazılım Gereksinimleri
Composer
Node.js (admin paneli için)
Redis (önbellek için)
Elasticsearch (büyük kataloglar için arama)
Uygulama Mimarisi (VS Code'da Geliştirme İçin)
Önerilen VS Code Eklentileri
PHP Intelephense
ESLint
PHP Debug
REST Client
Git Graph
Geliştirme İş Akışı
OpenCart çekirdeğini değiştirmeden modüler yapı kullanma
Geliştirme, test ve canlı ortamları ayrıştırma
Git ile versiyon kontrolü
CI/CD pipeline kurulumu
Güvenlik Önlemleri
HTTPS zorunluluğu
API anahtarlarının güvenli saklanması
Role-based access control (RBAC)
İki faktörlü kimlik doğrulama (2FA)
Düzenli güvenlik denetimleri ve güncellemeler
Geliştirme Roadmap
Faz 1: Temel Altyapı (2-3 ay)
OpenCart çekirdeğinin çok kullanıcılı yapıya uyarlanması
Kullanıcı rolleri ve izin sisteminin geliştirilmesi
Admin panelinin temel tasarımı
Faz 2: Pazaryeri Entegrasyonları (3-4 ay)
Amazon entegrasyonu
Etsy entegrasyonu
Türk pazaryerleri entegrasyonları
Ürün senkronizasyonu
Faz 3: Dropshipping Özellikleri (2-3 ay)
Dropshipping kullanıcı paneli
Otomatik sipariş yönlendirme
Fiyatlandırma ve kar marjı hesaplayıcı
Faz 4: Gelişmiş Özellikler ve Optimizasyon (2-3 ay)
Raporlama ve analitik araçlar
Performans optimizasyonu
Mobil uyumluluk
Gelişmiş arama özellikleri
Sürdürülebilirlik Stratejisi
Modüler mimari ile kolay güncellenebilirlik
Pazaryeri API değişikliklerine hızlı adaptasyon
Düzenli bakım ve güncelleme planı
Kapsamlı dokümantasyon
Topluluk desteği ve geri bildirim mekanizması
Sonuç
Bu proje, OpenCart platformu üzerinde gelişmiş bir çok kullanıcılı ve çoklu pazaryeri entegrasyonu sağlayan bir e-ticaret çözümü sunacaktır. Dropshipping iş modeline uygun tasarımı, farklı kullanıcı rolleri ve merkezi kontrol paneli ile kullanıcılara güçlü bir yönetim aracı sağlayacaktır. Sistem, sürdürülebilir ve ölçeklenebilir bir yapıda tasarlanarak gelecekteki ihtiyaçlara uyum sağlayabilecektir.