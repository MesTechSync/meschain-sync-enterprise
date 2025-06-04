# MesTech Sync - OpenCart MesTech Entegrasyonu v1.0.2

Bu proje, OpenCart için MesTech adı altında özel bir eklenti grubu oluşturan ve çeşitli pazaryeri entegrasyonları (Trendyol, n11, Amazon, eBay, Hepsiburada, Ozon) sağlayan bir modüldür.

## Yeni Versiyon 1.0.2 - 29.05.2025

- Tüm dosyalar ve klasörler yeniden düzenlendi
- XML yapısı güncellendi ve optimize edildi
- Kurulum ve kaldırma işlemleri iyileştirildi
- Dil dosyaları güncellendi
- Performans iyileştirmeleri yapıldı

## Özellikler

- OpenCart yönetici panelinde **"MesTech"** adında özel bir eklenti grubu oluşturur
- Standart modül yapısı yerine özel bir yapı kullanır (`extension/module` yerine `extension/mestech`)
- Çeşitli pazaryerleri için entegrasyon sağlar:
  - Trendyol
  - n11
  - Amazon
  - eBay
  - Hepsiburada
  - Ozon
- Detaylı loglama sistemi
- Kullanıcı bazlı ayarlar
- Duyuru sistemi
- Özelleştirilebilir temalar

## Kurulum

1. Modülü OpenCart mağazanıza yükleyin
2. OCMOD XML dosyası otomatik olarak gerekli değişiklikleri yapacaktır
3. Yükleme tamamlandığında, OpenCart yönetici panelinde "Eklentiler > MesTech" bölümünde modül görünecektir

## Dosya Yapısı

```
upload/
│
├── admin/
│   ├── controller/
│   │   └── extension/
│   │       └── mestech/
│   │           └── mestech_sync.php
│   ├── language/
│   │   └── tr-tr/
│   │       └── extension/
│   │           └── mestech/
│   │               └── mestech_sync.php
│   └── view/
│       └── template/
│           └── extension/
│               └── mestech/
│                   └── mestech_sync.twig
├── catalog/
│   ├── controller/
│   │   └── extension/
│   │       └── mestech/
│   └── model/
│       └── extension/
│           └── mestech/
├── system/
│   └── library/
│       └── mestech/
├── mestech_sync.ocmod.xml
└── install.php
```

## Teknik Detaylar

Bu modül, OpenCart'ın standart modül yapısını kullanmak yerine, özel bir "MesTech" eklenti tipi oluşturur. Bu sayede:

1. Eklentiler > Eklentiler menüsünde "MesTech" başlığı altında listelenir
2. Sol menüde "MesTech" adında özel bir menü oluşturulur
3. Tüm MesTech eklentileri bu menü altında gruplanır

Modül, OpenCart'ın OCMOD sistemini kullanarak gerekli değişiklikleri yapar ve hiçbir çekirdek dosyayı değiştirmez.

## Sistem Gereksinimleri

- OpenCart 3.0.x veya daha yüksek
- PHP 7.3 veya daha yüksek
- MySQL 5.7 veya daha yüksek
- cURL etkin
- OpenSSL PHP eklentisi
- ZipArchive PHP kütüphanesi

## Destek

Destek için lütfen support@mestech-sync.com adresine e-posta gönderin veya https://mestech-sync.com/support adresindeki web sitemizi ziyaret edin.

## Lisans

MesTech-Sync, [MIT Lisansı](LICENSE) altında lisanslanmıştır. 