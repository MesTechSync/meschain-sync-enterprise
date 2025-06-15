# CHANGELOG

## [1.0.0] - 2024-06-01
- OpenCart 3.0.4.0'a tam uyumlu modül altyapısı oluşturuldu.
- Atomik ve standart loglama sistemi eklendi (her platform için ayrı log dosyası).
- Panelde heading_title kaldırıldı, dinamik platform adı ve kullanıcıya özel tema/rol gösterimi sağlandı.
- API anahtarı şifreli (base64) saklanacak şekilde güncellendi.
- Dashboard'da rol bazlı özel panel arayüzü hazırlandı.
- Otomatik izin atama ve oturum güvenliği eklendi.
- LOG_README ve dokümantasyon güncellendi.
- Tüm fonksiyonlara açıklama ve log şablonları eklendi.

## 2025-05-28
- Trendyol modülü OpenCart yapısına taşındı ve tüm dosyalar atomik olarak loglanacak şekilde düzenlendi.
- Controller, model, helper, view, config ve test scriptleri eklendi.
- Loglama ve hata yönetimi standartları uygulandı.

## 2025-05-29
- Ek helper fonksiyonları ve hata yönetimi eklendi.
- Kodun sürdürülebilirliği ve izlenebilirliği artırıldı.

## [1.1.0] - 2024-06-02
- Gerçek Trendyol API entegrasyonu: Siparişler panelden canlı çekilebiliyor.
- Çekilen siparişler tablo olarak panelde gösteriliyor.
- Tüm API işlemleri ve hatalar atomik olarak loglanıyor.
- LOG_README, VERSIYON ve yeni HELP.md dosyası güncellendi/eklendi.
- Orijinal yazılımdaki log, help ve versiyon harita dosyası açıklamaları entegre edildi. 