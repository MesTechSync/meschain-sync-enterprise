# Super Admin v5.0 - Enterprise: Team Contributions & Module Ownership

**Rapor Tarihi:** 15 Haziran 2025

Bu rapor, "Super Admin v5.0 - Enterprise" panelindeki menü öğeleri ve modüller için sorumlu takımları ve katkı alanlarını özetlemektedir. Bilgiler, `TAKIM_GOREVLENDIRMELERI_HAZIRAN15_2025.md` görevlendirme dosyasına ve genel proje sorumluluklarına dayanmaktadır.

## Genel Bakış ve Üst Düzey Kontroller

- **Panel Adı:** Super Admin v5.0 - Enterprise
- **Genel Güvenlik Seviyesi (ULTRA SECURE, MAXIMUM SECURITY ACCESS):**
  - **CURSOR TAKIMI:** UI/UX tasarımı, güvenlik göstergelerinin görselleştirilmesi.
  - **VSCODE TAKIMI:** Güvenlik altyapısının izlenmesi, anomali tespiti.
  - **MEZBJEN TAKIMI (Dolaylı):** Entegre edilen sistemlerin genel güvenliğe katkısı.
- **Oturum Süresi (Session):**
  - **CURSOR TAKIMI:** UI gösterimi.
  - **VSCODE TAKIMI / MEZBJEN TAKIMI (Auth Sistemleri):** Oturum yönetimi altyapısı.
- **Kullanıcı Bilgisi (MezBjen | ID: SA001):**
  - **CURSOR TAKIMI:** UI gösterimi.
  - **MEZBJEN TAKIMI (Auth Sistemleri):** Kimlik doğrulama ve kullanıcı verisi.
- **Dil Seçimi (TR) ve Tema (Aydınlık):**
  - **CURSOR TAKIMI:** UI/UX ve tema yönetimi.

## Menü Bazlı Takım Sorumlulukları

### 1. Ana Yönetim
   - **Genel Sorumluluk:** CURSOR TAKIMI (UI/UX), VSCODE TAKIMI (Veri ve İzleme)
   - **Dashboard:**
     - **CURSOR TAKIMI:** Tasarım, widget'lar, genel görünüm.
     - **VSCODE TAKIMI:** Gösterilecek verilerin sağlanması, gerçek zamanlı güncellemeler.
   - **Analitik:**
     - **CURSOR TAKIMI:** Grafiklerin ve raporların görselleştirilmesi.
     - **VSCODE TAKIMI:** Analitik verilerin toplanması ve işlenmesi.
   - **Takım Performansı:**
     - **CURSOR TAKIMI:** Performans metriklerinin sunumu.
     - **VSCODE TAKIMI:** Takım performans verilerinin toplanması.
   - **Sistem Durumu:**
     - **CURSOR TAKIMI:** Durum göstergelerinin tasarımı.
     - **VSCODE TAKIMI:** Sistem sağlık verilerinin gerçek zamanlı izlenmesi ve sağlanması.
   - **Performans İzleme:**
     - **CURSOR TAKIMI:** Performans grafik ve metriklerinin UI'ı.
     - **VSCODE TAKIMI:** Detaylı performans verilerinin toplanması ve analizi.
     - **MUSTİ TAKIMI:** Performans testleri ve darboğaz tespiti.
   - **Zincir Senkronizasyonu:**
     - **CURSOR TAKIMI:** Senkronizasyon durumu ve logların UI'ı.
     - **MEZBJEN TAKIMI / VSCODE TAKIMI:** Arka plan senkronizasyon mekanizmaları.
   - **Mesh Ağ Yönetimi:**
     - **CURSOR TAKIMI:** Ağ topolojisi ve durumunun görselleştirilmesi.
     - **MEZBJEN TAKIMI / VSCODE TAKIMI:** Mesh ağ altyapısı ve yönetimi.
   - **Gerçek Zamanlı İzleme:**
     - **CURSOR TAKIMI:** Canlı veri akışlarının ve uyarıların UI'ı.
     - **VSCODE TAKIMI:** Tüm sistemlerin gerçek zamanlı izleme altyapısı.

### 2. Marketplace
   - **Genel Sorumluluk:** MEZBJEN TAKIMI (Entegrasyonlar), CURSOR TAKIMI (UI/UX)
   - **Marketplace Genel Bakış:**
     - **CURSOR TAKIMI:** Genel durum ve metriklerin UI'ı.
     - **MEZBJEN TAKIMI:** Tüm marketplace'lerden veri toplama.
   - **Marketplace Sağlık:**
     - **CURSOR TAKIMI:** Sağlık durumlarının görselleştirilmesi.
     - **MEZBJEN TAKIMI:** Entegrasyonların sağlık kontrolleri.
     - **VSCODE TAKIMI:** Sağlık verilerinin izlenmesi.
   - **Trendyol, Amazon, N11, Hepsiburada, EBay, Ozon, Pazarama, PttAVM:**
     - **MEZBJEN TAKIMI:** Her bir marketplace için API entegrasyonu, veri senkronizasyonu, OCMOD geliştirme (Trendyol öncelikli).
     - **CURSOR TAKIMI:** Her bir marketplace'e özel UI elemanları, listeleme ve yönetim arayüzleri.
   - **Marketplace Entegrasyonlar:**
     - **MEZBJEN TAKIMI:** Yeni entegrasyonların eklenmesi ve yönetimi.
     - **CURSOR TAKIMI:** Entegrasyon ayarları için UI.

### 3. Envanter
   - **Genel Sorumluluk:** MEZBJEN TAKIMI (Veri Yönetimi), CURSOR TAKIMI (UI/UX)
   - **Envanter Genel Bakış:**
     - **CURSOR TAKIMI:** Envanter özetinin UI'ı.
     - **MEZBJEN TAKIMI:** Envanter verilerinin toplanması ve yönetimi.
   - **Stok Yönetimi, Ürün Kataloğu, Depo Yönetimi:**
     - **CURSOR TAKIMI:** Bu modüller için kullanıcı arayüzleri.
     - **MEZBJEN TAKIMI:** Stok, ürün ve depo verilerinin arka plan yönetimi.
   - **Düşük Stok Uyarıları:**
     - **CURSOR TAKIMI:** Uyarıların UI'da gösterimi.
     - **MEZBJEN TAKIMI:** Uyarı mekanizmasının mantığı.
     - **VSCODE TAKIMI:** Uyarı sisteminin izlenmesi.
   - **Envanter Senkronizasyonu:**
     - **CURSOR TAKIMI:** Senkronizasyon durumu UI'ı.
     - **MEZBJEN TAKIMI:** Farklı platformlar arası envanter senkronizasyonu.

### 4. Raporlama
   - **Genel Sorumluluk:** VSCODE TAKIMI (Veri Toplama ve İşleme), CURSOR TAKIMI (UI/UX)
   - **Satış Raporları (:3018), Mali Raporlar (:3019), Performans Raporları (:3020), Envanter Raporları (:3021), Özel Raporlar (:3022):**
     - **CURSOR TAKIMI:** Raporların görselleştirilmesi, filtreleme ve çıktı arayüzleri.
     - **VSCODE TAKIMI:** İlgili portlardan (3018-3022) veri çekme, rapor oluşturma mantığı.
     - **MEZBJEN TAKIMI:** Raporlamaya konu olan sistemlerin (örn: satış, envanter) veri bütünlüğü.
   - **Veri Dışa Aktarma (:3025):**
     - **CURSOR TAKIMI:** Dışa aktarma seçenekleri ve süreci için UI.
     - **VSCODE TAKIMI / MEZBJEN TAKIMI:** Veri dışa aktarma mekanizması.

### 5. Sistem-Araclari
   - **Genel Sorumluluk:** MUSTİ TAKIMI (Araç Geliştirme ve Test), CURSOR TAKIMI (UI/UX), VSCODE TAKIMI (İzleme)
   - **Kod Düzeltici (:4500), Log İzleyici (:4500), Sistem Sağlık İzleyici (:4500), Performans Optimize Edici (:4500):**
     - **CURSOR TAKIMI:** Bu araçlar için kullanıcı arayüzleri.
     - **MUSTİ TAKIMI:** Araçların temel işlevselliği, geliştirilmesi ve test edilmesi.
     - **VSCODE TAKIMI:** Bu araçların ve izledikleri sistemlerin genel performans ve sağlık takibi (Port 4500 üzerinden).
   - **Yedekleme Sistemi (:3024):**
     - **CURSOR TAKIMI:** Yedekleme ve geri yükleme işlemleri için UI.
     - **MEZBJEN TAKIMI / VSCODE TAKIMI:** Yedekleme altyapısı ve yönetimi.

### 6. Otomasyon
   - **Genel Sorumluluk:** MEZBJEN TAKIMI (İş Akışları), CURSOR TAKIMI (UI/UX), VSCODE TAKIMI (İzleme)
   - **Otomasyon Genel Bakış, İş Akışları, Otomatik Fiyatlandırma, Otomatik Listeleme, Zamanlanmış Görevler, Otomasyon Kuralları:**
     - **CURSOR TAKIMI:** Otomasyon kurallarının ve iş akışlarının tanımlanması, izlenmesi için UI.
     - **MEZBJEN TAKIMI:** Otomasyon motorunun ve kurallarının arka plan mantığı, özellikle marketplace ve envanterle ilgili otomasyonlar.
     - **VSCODE TAKIMI:** Otomasyon süreçlerinin düzgün çalışıp çalışmadığının izlenmesi.

### 7. Servis Yönetimi
   - **Genel Sorumluluk:** MEZBJEN TAKIMI (Servis Entegrasyonları), VSCODE TAKIMI (İzleme), CURSOR TAKIMI (UI/UX)
   - **Tüm Servisler, Entegrasyonlar, Otomasyon, İş Akışları, API Yönetimi, Webhook Yönetimi, Servis Sağlığı, Mikroservisler, Yük Dengeleyici:**
     - **CURSOR TAKIMI:** Servislerin listelenmesi, konfigürasyonu ve sağlık durumlarının gösterimi için UI.
     - **MEZBJEN TAKIMI:** Servislerin (özellikle 30XX portlarındaki) canlıya alınması, entegrasyonları ve API'lerinin yönetimi.
     - **VSCODE TAKIMI:** Tüm bu servislerin sağlık ve performansının gerçek zamanlı izlenmesi.
     - **MUSTİ TAKIMI:** Servislerin API testleri.

### 8. Dokümantasyon
   - **Genel Sorumluluk:** Tüm takımlar kendi alanlarıyla ilgili dokümantasyon sağlar.
   - **Kullanım Kılavuzu, Teknik Kılavuz, API Dokümantasyonu, Video Eğitimler, Sık Sorulan Sorular, Değişiklik Günlüğü:**
     - **CURSOR TAKIMI:** Dokümantasyonun sunulacağı arayüz, okunabilirlik, erişilebilirlik.
     - **MEZBJEN TAKIMI:** Özellikle API ve entegrasyon dokümantasyonları.
     - **MUSTİ TAKIMI:** Test süreçleri ve hata giderme kılavuzları.
     - **VSCODE TAKIMI:** İzleme sistemleri ve mimari dokümantasyonu.

### 9. Kullanıcı Yönetimi
   - **Genel Sorumluluk:** MEZBJEN TAKIMI (Auth/RBAC Altyapısı), CURSOR TAKIMI (UI/UX)
   - **Kullanıcı Hesapları, Rol Tabanlı Erişim, Güvenlik Politikaları, Oturum Yönetimi, Kullanıcı Aktivitesi:**
     - **CURSOR TAKIMI:** Kullanıcı ve rol yönetimi, politika ayarları için UI.
     - **MEZBJEN TAKIMI (Auth/Security Modülleri):** Kimlik doğrulama, yetkilendirme, oturum yönetimi ve güvenlik politikalarının uygulanması.
     - **VSCODE TAKIMI:** Kullanıcı aktivitelerinin ve güvenlik olaylarının izlenmesi.

### 10. Sistem Güvenliği
    - **Genel Sorumluluk:** MEZBJEN TAKIMI (Güvenlik Modülleri), VSCODE TAKIMI (İzleme ve Tehdit Tespiti), CURSOR TAKIMI (UI/UX)
    - **Tehdit Tespiti, Güvenlik Logları, IP Engelleme, 2FA Yönetimi, Güvenlik Panosu:**
        - **CURSOR TAKIMI:** Güvenlik panosu, loglar ve ayarlar için UI.
        - **MEZBJEN TAKIMI (Security.js, Auth.js):** 2FA, IP engelleme gibi güvenlik mekanizmalarının geliştirilmesi ve entegrasyonu.
        - **VSCODE TAKIMI:** Tehditlerin aktif olarak izlenmesi, güvenlik loglarının analizi, alarm üretimi.
        - **MUSTİ TAKIMI:** Güvenlik açıklarının test edilmesi (penetration testing gibi).

### 11. API Yönetimi
    - **Genel Sorumluluk:** MEZBJEN TAKIMI (API Geliştirme ve Entegrasyon), CURSOR TAKIMI (UI/UX), VSCODE TAKIMI (İzleme)
    - **API Anahtarları, Rate Limiting, Token Yönetimi, API Logları, API Analitiği:**
        - **CURSOR TAKIMI:** API anahtar yönetimi, rate limiting kuralları ve analitiklerin gösterimi için UI.
        - **MEZBJEN TAKIMI:** API'lerin geliştirilmesi, güvenliği (token, rate limiting), ve loglama mekanizmaları.
        - **VSCODE TAKIMI:** API kullanımının, performansının ve olası güvenlik ihlallerinin izlenmesi.
        - **MUSTİ TAKIMI:** API'ların fonksiyonel ve güvenlik testleri.

### 12. RBAC Yönetimi (Rol Tabanlı Erişim Kontrolü)
    - **Genel Sorumluluk:** MEZBJEN TAKIMI (RBAC Altyapısı), CURSOR TAKIMI (UI/UX)
    - **Rol Tanımları, İzin Matrisi, Hiyerarşik Roller, Dinamik İzinler, Erişim Denetimi:**
        - **CURSOR TAKIMI:** Rol ve izinlerin tanımlanması, matrisin görselleştirilmesi için UI.
        - **MEZBJEN TAKIMI (Auth.js/Security.js):** RBAC mantığının ve altyapısının geliştirilmesi, izinlerin uygulanması.

### 13. Veritabanı Yönetimi
    - **Genel Sorumluluk:** VSCODE TAKIMI (İzleme ve Optimizasyon), MEZBJEN TAKIMI (Veri Bütünlüğü), CURSOR TAKIMI (UI/UX)
    - **Performans İzleme, Yedekleme Yönetimi, Sorgu Optimizasyonu, Veri Şifreleme, Veritabanı Sağlığı:**
        - **CURSOR TAKIMI:** Veritabanı sağlık durumu, performans metrikleri ve yönetim görevleri için UI.
        - **VSCODE TAKIMI:** Veritabanı performansının sürekli izlenmesi, sorgu optimizasyonu için analizler, yedekleme süreçlerinin takibi.
        - **MEZBJEN TAKIMI:** Veri bütünlüğü, şifreleme politikaları ve yedekleme stratejilerinin belirlenmesi.
        - **MUSTİ TAKIMI:** Veritabanı performans ve güvenlik testleri.

### 14. Sistem Izleme
    - **Genel Sorumluluk:** VSCODE TAKIMI (Ana İzleme Altyapısı), CURSOR TAKIMI (UI/UX)
    - **CPU/Memory İzleme, Disk Kullanımı, Ağ Trafiği, Alert Yönetimi, Sistem Sağlığı:**
        - **CURSOR TAKIMI:** Tüm bu metriklerin ve uyarıların kullanıcı dostu bir şekilde gösterildiği dashboard ve UI elemanları.
        - **VSCODE TAKIMI:** Bu sistem kaynaklarının ve genel sağlık durumunun gerçek zamanlı izlenmesi, veri toplanması ve alarm mekanizmalarının yönetimi.

### 15. Gelişmiş Özellikler
    - **Genel Sorumluluk:** Genellikle birden fazla takımın kesişim alanı.
    - **Gelir Analitiği:**
        - **CURSOR TAKIMI:** Gelir verilerinin görselleştirilmesi.
        - **VSCODE TAKIMI:** Gelirle ilgili verilerin toplanması, analizi (muhtemelen Satış ve Mali Raporlarla bağlantılı).
        - **MEZBJEN TAKIMI:** Gelir getiren sistemlerin (örn: marketplace) veri doğruluğu.
    - **Sistem İzleme (Tekrar):** Bkz. Madde 14.
    - **Güvenlik Merkezi:**
        - **CURSOR TAKIMI:** Tüm güvenlik modüllerini bir araya getiren merkezi dashboard UI.
        - **VSCODE TAKIMI:** Güvenlik olaylarının merkezi olarak toplanması ve korelasyonu.
        - **MEZBJEN TAKIMI (Security/Auth):** Güvenlik modüllerinin entegrasyonu.
        - **MUSTİ TAKIMI:** Genel güvenlik denetimleri ve testleri.
    - **Yedekleme & Kurtarma:** Bkz. Sistem-Araclari altındaki Yedekleme Sistemi ve Veritabanı Yönetimi altındaki Yedekleme Yönetimi.

## Takım Bazlı Özet Sorumluluklar

- **CURSOR TAKIMI (TASARIM TİMİ):**
  - Tüm panelin A+++++ kalite UI/UX tasarımı.
  - Veri görselleştirme, kullanıcı etkileşimleri, tema ve dil yönetimi.
  - Güvenlik göstergeleri, bildirimler ve kullanıcı geri bildirim arayüzleri.

- **MUSTİ TAKIMI (HATA DÜZELTME VE TEST TİMİ):**
  - Sistem genelinde hata düzeltme.
  - Otomatik ve manuel test sistemlerinin kurulumu ve işletilmesi (Jest/Mocha, Selenium vb.).
  - Performans, yük, stres ve API testleri.
  - Kalite kontrol ve QA süreçleri.

- **MEZBJEN TAKIMI (TRENDYOL & OPENCART TİMİ / GENEL ENTEGRASYON):**
  - Trendyol ve diğer marketplace API entegrasyonları, OpenCart (OCMOD) geliştirmeleri.
  - 30XX, 40XX, 60XX portlarındaki servislerin canlıya alınması ve yönetimi.
  - Authentication (auth.js) ve Security (security.js) modüllerinin temel geliştirme ve entegrasyonu (RBAC dahil).
  - Envanter, otomasyon ve servis yönetimi arka plan mantığı.
  - API geliştirme ve yönetimi (anahtarlar, rate limiting, tokenlar).

- **VSCODE TAKIMI (OTOMATIK İZLEME TİMİ):**
  - 3023 portundaki ana yönetim panelinin ve diğer tüm servislerin gerçek zamanlı izlenmesi.
  - CPU, memory, disk, ağ trafiği, response time, error rate gibi performans metriklerinin takibi.
  - Alarm sistemleri ve anomali tespiti.
  - Raporlama modülleri için veri toplama ve işleme.
  - Veritabanı performansı izleme ve optimizasyon önerileri.
  - Güvenlik loglarının merkezi analizi ve tehdit tespiti.

Bu rapor, mevcut bilgilere dayanarak hazırlanmıştır ve projenin dinamik yapısına göre güncellenebilir.
