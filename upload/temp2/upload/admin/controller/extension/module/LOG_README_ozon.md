# Log Analiz Rehberi (ozon)

## Amaç
Bu klasördeki her log dosyası, Ozon modülünün işleyişi ve hataları hakkında atomik bilgi sağlar. Loglar, hem insanlar hem de yapay zekâlar tarafından kolayca analiz edilebilecek şekilde tasarlanmıştır.

## Log Formatı
[YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]

## Analiz Önerileri
- Hata ve uyarı loglarını filtreleyerek hızlıca sorun kaynağını tespit edebilirsiniz.
- Her işlem türü için (INSERT, UPDATE, DELETE, API_CALL, ERROR) ayrı analiz yapılabilir.
- Loglar, zaman damgası ve kullanıcı bazında sıralanarak süreç takibi yapılabilir.

## Otomasyon
- Log dosyaları, basit bir Python veya bash scripti ile otomatik olarak analiz edilebilir.
- Örnek: Belirli bir hata kodunu veya kullanıcıyı aramak için `grep` veya `findstr` kullanılabilir. 