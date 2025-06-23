# Proje Dosya Haritası

Bu döküman, projenin yeniden yapılandırılmış dosya düzenini ve önemli dizinlerin açıklamalarını içerir.

## Ana Dizinler

- **`upload/`**: OpenCart e-ticaret platformu için ana uygulama kodunu içerir. Bu dizin, OpenCart'ın standart MVC(L) yapısını takip eder.
    - **`admin/`**: Yönetici paneli ile ilgili dosyalar.
        - `controller/`: Yönetici paneli denetleyicileri.
        - `model/`: Veritabanı işlemleri için modeller.
        - `view/`: Yönetici paneli arayüz şablonları (`.twig`).
        - `language/`: Dil dosyaları.
    - **`catalog/`**: Müşteri arayüzü (mağaza ön yüzü) ile ilgili dosyalar.
    - **`system/`**: OpenCart sistem dosyaları.
        - `library/`: Çekirdek ve üçüncü parti kütüphaneler.
            - **`meschain/`**: Projeye özel geliştirilmiş tüm MesChain kütüphaneleri, yardımcı sınıflar (helper) ve API entegrasyonları burada bulunur.

- **`documentation/`**: Proje ile ilgili tüm dökümantasyon, teknik analizler, raporlar ve geliştirme notları bu dizinde toplanmıştır.
    - **`code/`**: Geliştirme sürecinde kullanılan betikler ve kod parçacıkları.
    - **`...`**: Diğer alt dökümantasyon dizinleri.

- **`dev-resources/`**: Geliştirme ortamı ve araçlarıyla ilgili kaynakları içerir. Bu dizin, uygulamanın çalışması için gerekli değildir.
    - **`vscode-extensions-review/`**: VSCode eklenti incelemeleri ve önerileri.

- **`backups/`**: Veritabanı ve dosya yedeklerini içeren dizin.

- **`tests/`**: Otomatik testler için ayrılmış dizin.

## Önemli Dosyalar

- **`README.md`**: Proje hakkında genel bilgiler ve başlangıç talimatları.
- **`GITHUB_BEST_PRACTICES.md`**: GitHub üzerinde çoklu eklenti yönetimi için en iyi uygulamalar.
- **`FILE_MAP.md`**: (Bu dosya) Proje dosya yapısının haritası.
- **`MesChain-Sync.code-workspace`**: VSCode çalışma alanı dosyası.
- **`RESTRUCTURED_UPLOAD`**: **DOKUNULMAMASI GEREKEN**, yeniden yapılandırılmış dosyaların bulunduğu referans dizin.
