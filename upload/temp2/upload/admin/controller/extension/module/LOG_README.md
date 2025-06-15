# LOG_README

## Amaç
Bu dosya, Trendyol modülünün tüm loglama altyapısını ve analiz yöntemlerini açıklar. Tüm önemli işlemler ve hatalar atomik olarak log dosyalarına kaydedilir.

## Log Formatı
[YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]

## Log Dosyaları
- trendyol_controller.log: Panel ve ayar işlemleri
- trendyol_helper.log: API ve yardımcı işlemler

## Örnek Loglar
[2024-06-02 12:00:00] [admin] [API_TEST] Trendyol bağlantı testi başarılı.
[2024-06-02 12:01:00] [admin] [FETCH_ORDERS] Trendyol siparişleri çekildi.
[2024-06-02 12:02:00] [system] [API_ERROR] Sipariş çekme HTTP hata kodu: 401 - Yanıt: ...

## Analiz ve Otomasyon
- Hata ve uyarı loglarını filtreleyerek hızlıca sorun kaynağı tespit edebilirsiniz.
- Her işlem türü için (API_CALL, ERROR, KURULUM, KALDIRMA, AYAR_GUNCELLEME) ayrı analiz yapılabilir.
- Loglar, zaman damgası ve kullanıcı bazında sıralanarak süreç takibi yapılabilir.
- Loglar atomik ve açıklamalı tutulur.
- Otomasyon için grep/findstr ile arama yapılabilir. 