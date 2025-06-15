# 📁 MesChain-Sync: Klasör Yapısı ve Organizasyon

Bu doküman, MesChain-Sync projesinin klasör yapısını ve dosya organizasyonunu detaylı bir şekilde açıklar.

## 📂 Kök Dizin Organizasyonu

```
meschain-sync/
├── docs/                      # Proje dokümantasyonu
├── meschain-sync/             # Proje planlama ve yönetim dosyaları
├── upload/                    # OpenCart entegrasyonu için kurulum dosyaları
├── CHANGELOG.md               # Sürüm değişiklikleri
├── README.md                  # Proje genel açıklaması
└── VERSION                    # Sürüm bilgisi
```

## 📑 Dokümantasyon (`docs/`)

```
docs/
├── PROJECT_OVERVIEW.md        # Proje genel bakış
├── STRUCTURE.md               # Bu dosya - Klasör yapısı açıklaması
├── TECH_STACK.md              # Kullanılan teknolojiler
├── MODULE_GUIDE.md            # Modül geliştirme rehberi
├── API_PROMPT_GUIDE.md        # API kullanım rehberi
├── installation.md            # Kurulum kılavuzu
├── troubleshooting.md         # Sorun giderme
├── user_guide.md              # Kullanıcı kılavuzu
└── README.md                  # Dokümantasyon ana sayfası
```

## 🔧 Proje Yönetimi (`meschain-sync/`)

```
meschain-sync/
├── AI_Kod_Analiz_Talimatı.md  # AI ile kod analizi talimatları
├── meschain_sync_todo_plan.md # Yapılacaklar ve geliştirme planı
├── meschain_sync_updated_todo.md # Güncellenmiş yapılacaklar
├── MesTech_Klasor_ac.md       # OpenCart klasör yapısı açıklaması
├── YENI_YAZILIM_HARITASI.md   # Yeni yapı önerisi
└── ORJINAL_YAZILIM_HARITASI.md # Orijinal yapı haritası
```

## 🔄 OpenCart Entegrasyonu (`upload/`)

### Admin Paneli

```
upload/admin/
├── controller/
│   └── extension/
│       └── mestech/                # MesTech ana kontrolcü klasörü
│           ├── mestech_sync.php    # Ana senkronizasyon kontrolcüsü
│           ├── trendyol/           # Trendyol entegrasyon modülü
│           │   ├── README.md       # Modül açıklaması
│           │   └── TODO.md         # Modül yapılacakları
│           ├── amazon/             # Amazon entegrasyon modülü
│           ├── n11/                # N11 entegrasyon modülü
│           ├── hepsiburada/        # Hepsiburada entegrasyon modülü
│           ├── ebay/               # eBay entegrasyon modülü
│           └── ozon/               # Ozon entegrasyon modülü
├── language/
│   ├── en-gb/                      # İngilizce dil dosyaları
│   │   └── extension/
│   │       └── mestech/
│   │           ├── mestech_sync.php
│   │           └── [pazaryeri]/
│   └── tr-tr/                      # Türkçe dil dosyaları
│       └── extension/
│           └── mestech/
│               ├── mestech_sync.php
│               └── [pazaryeri]/
└── view/
    └── template/
        └── extension/
            └── mestech/
                ├── mestech_sync.twig   # Ana senkronizasyon şablonu
                └── [pazaryeri]/        # Pazaryeri şablonları
```

### Sistem Kütüphaneleri

```
upload/system/
└── library/
    ├── meschain/                   # MesChain yardımcı kütüphaneleri
    │   ├── logger.php              # Loglama sistemi
    │   ├── api.php                 # API yardımcıları
    │   └── helper.php              # Genel yardımcı fonksiyonlar
    └── entegrator/                 # Entegrasyon yardımcıları
        ├── trendyol.php            # Trendyol API entegrasyonu
        ├── amazon.php              # Amazon API entegrasyonu
        ├── n11.php                 # N11 API entegrasyonu
        ├── hepsiburada.php         # Hepsiburada API entegrasyonu
        ├── ebay.php                # eBay API entegrasyonu
        └── ozon.php                # Ozon API entegrasyonu
```

### Catalog Entegrasyonu

```
upload/catalog/
├── controller/
│   └── extension/
│       └── mestech/
│           └── mestech_sync.php    # Mağaza tarafı entegrasyonu
├── model/
│   └── extension/
│       └── mestech/
│           └── mestech_sync.php    # Mağaza tarafı model
└── view/
    └── theme/
        └── default/
            └── template/
                └── extension/
                    └── mestech/
                        └── mestech_sync.twig # Mağaza tarafı şablonu
```

### Kurulum ve Güncelleme

```
upload/
├── install.php                     # Kurulum scripti
├── uninstall.php                   # Kaldırma scripti
└── mestech_sync_v1.0.2.ocmod.zip   # OCMOD paket dosyası
```

## 📊 Modül Mimarisi

Her pazaryeri modülü (Trendyol, Amazon, N11, vb.) şu standart bileşenlere sahiptir:

1. **Controller:**
   - API ayarları yönetimi
   - Dashboard (Kontrol Paneli)
   - Ürün senkronizasyonu
   - Sipariş yönetimi

2. **Model:**
   - Veritabanı işlemleri
   - Veri dönüştürme fonksiyonları

3. **View (Twig şablonları):**
   - Dashboard görünümü
   - Ayarlar sayfası
   - Ürün yönetimi
   - Sipariş yönetimi

4. **Dil Dosyaları:**
   - Arayüz metinleri
   - Hata mesajları
   - Başlıklar ve açıklamalar

5. **Yardımcı Dosyalar:**
   - README.md (Modül açıklaması)
   - TODO.md (Yapılacaklar listesi)
   - Helper sınıfları

## 🔄 OpenCart Entegrasyon Yapısı

MesChain-Sync, OpenCart'ın mevcut yapısını şu şekilde genişletir:

1. `extension/mestech/` dizini ile özel bir eklenti kategorisi oluşturur
2. `mestech_sync.php` ana kontrolcüsü ile tüm pazaryeri modüllerini yönetir
3. Her pazaryeri için ayrı kontrol, model ve görünüm dosyaları içerir
4. OpenCart'ın dil sistemini kullanarak çoklu dil desteği sağlar
5. OCMOD sistemi üzerinden OpenCart çekirdeğini değiştirmeden entegre olur

## 📝 Dosya Standardizasyonu

MesChain-Sync projesinde, aşağıdaki dosya standardizasyonu uygulanmaktadır:

1. Her klasörde açıklayıcı README.md dosyaları
2. Her modül için ayrı TODO.md dosyaları
3. OpenCart standartlarına uygun controller, model ve view dosyaları
4. Tutarlı dosya isimlendirme kuralları (snake_case)
5. Dil dosyalarında tutarlı anahtar kullanımı

Bu yapı, modülün sürdürülebilir, genişletilebilir ve anlaşılır olmasını sağlar. 