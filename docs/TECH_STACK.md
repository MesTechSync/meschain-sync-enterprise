# 🛠️ MesChain-Sync: Teknoloji Altyapısı

Bu doküman, MesChain-Sync projesinde kullanılan teknolojileri, kütüphaneleri ve araçları detaylandırır.

## 🖥️ Ana Geliştirme Ortamı

| Kategori | Teknoloji | Sürüm | Açıklama |
|----------|-----------|-------|----------|
| Platform | **OpenCart** | 3.x | E-ticaret platformu |
| Dil | **PHP** | 7.3+ | Ana programlama dili |
| Veritabanı | **MySQL** | 5.7+ | Veri depolama |
| Şablonlama | **Twig** | 2.x | Görünüm katmanı |
| Paket Yöneticisi | **OCMOD** | - | OpenCart modifikasyon sistemi |

## 🌐 Pazaryeri API Entegrasyonları

| Pazaryeri | API Sürümü | Protokol | Format | Doküman URL |
|-----------|------------|----------|--------|-------------|
| **Trendyol** | v2 | REST | JSON | [Trendyol API Docs](https://developers.trendyol.com/en) |
| **Amazon** | SP-API | REST | JSON | [Amazon SP-API Docs](https://developer-docs.amazon.com/sp-api/) |
| **N11** | v1 | SOAP/REST | XML/JSON | [N11 API Docs](https://api.n11.com/ws/) |
| **Hepsiburada** | v1 | REST | JSON | [Hepsiburada API Docs](https://developers.hepsiburada.com/) |
| **eBay** | v1 | REST | JSON | [eBay API Docs](https://developer.ebay.com/docs) |
| **Ozon** | v3 | REST | JSON | [Ozon API Docs](https://docs.ozon.ru/api/seller) |

## 📊 Ön Yüz (Frontend) Teknolojileri

| Teknoloji | Sürüm | Kullanım Alanı |
|-----------|-------|----------------|
| **Bootstrap** | 4.x | UI Framework |
| **jQuery** | 3.x | DOM Manipülasyonu |
| **Chart.js** | 2.9.x | Grafikler ve Raporlama |
| **Font Awesome** | 5.x | İkonlar |
| **DataTables** | 1.10.x | Veri Tabloları |
| **Select2** | 4.x | Gelişmiş Seçim Kutuları |
| **Moment.js** | 2.x | Tarih/Zaman İşlemleri |

## 🔐 Güvenlik Bileşenleri

| Bileşen | Açıklama |
|---------|----------|
| **OpenCart Güvenlik** | OpenCart'ın dahili güvenlik özellikleri |
| **Token Sistemleri** | API istekleri için token tabanlı yetkilendirme |
| **IP Kısıtlamaları** | Admin paneli erişim kontrolleri |
| **Şifreleme** | API anahtarlarının güvenli depolanması |
| **XSS Koruması** | Cross-site scripting koruması |
| **CSRF Koruması** | Cross-site request forgery koruması |

## 📦 PHP Kütüphaneleri ve Bağımlılıklar

| Kütüphane | Sürüm | Amaç |
|-----------|-------|------|
| **GuzzleHTTP** | 7.x | HTTP İstekleri |
| **Monolog** | 2.x | Loglama Sistemi |
| **PHP-JWT** | 5.x | JWT Token İşlemleri |

## 🧪 Test Araçları

| Araç | Türü | Açıklama |
|------|------|----------|
| **PHPUnit** | Unit Testing | Birim testleri |
| **OpenCart Test Framework** | Entegrasyon Testleri | OpenCart entegrasyon testleri |
| **Postman** | API Testleri | API endpoint testleri |

## 📁 Veri Formatları

| Format | Kullanım Alanı |
|--------|----------------|
| **JSON** | API iletişimi, konfigürasyon dosyaları |
| **XML** | OpenCart yapılandırması, N11 API |
| **CSV** | Veri içe/dışa aktarma |

## 🚀 Dağıtım ve Kurulum

| Araç | Amaç |
|------|------|
| **OCMOD** | OpenCart modifikasyon sistemi |
| **ZIP** | Paketleme formatı |
| **PHP Installer** | Kurulum scripti |

## 💻 Geliştirme Araçları

| Araç | Amaç |
|------|------|
| **Cursor AI** | AI destekli kod geliştirme |
| **Git** | Sürüm kontrolü |
| **VS Code / PHPStorm** | IDE |
| **Composer** | PHP bağımlılık yönetimi |

## 🌍 Dil ve Yerelleştirme

| Dil | Durum |
|-----|-------|
| **Türkçe** | Tam Destek |
| **İngilizce** | Tam Destek |
| **Rusça** | Kısmi Destek (Ozon) |

## 📋 Sistem Gereksinimleri

| Gereksinim | Minimum | Önerilen |
|------------|---------|----------|
| **PHP** | 7.3+ | 7.4+ |
| **MySQL** | 5.7+ | 8.0+ |
| **Web Sunucusu** | Apache 2.4+ | Nginx 1.18+ |
| **SSL** | Gerekli | Gerekli |
| **Bellek Limiti** | 128MB | 256MB+ |
| **PHP Uzantıları** | curl, json, zip, xml, mbstring | + opcache, intl |

Bu teknoloji yığını, MesChain-Sync'in çeşitli pazaryerleriyle entegrasyonunu, güvenliğini ve performansını sağlamak üzere tasarlanmıştır. 