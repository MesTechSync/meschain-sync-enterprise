# 🧠 Yapay Zekâ Kod Taraması Talimatı (Claude 3.7 için)

## 🎯 Hedef

Bu proje içinde yer alan tüm dosyaların her bir satırının analiz edilmesi, eksiklerin tespiti, `TODO.md` dosyalarının güncellenmesi, proje yapısına dair genel bir hakimiyetin sağlanması ve sistemli bir şekilde geliştirme adımlarının planlanmasıdır.

## 🧩 Altyapı ve Sistem

- Kod Geliştirme Editörü: **Cursor AI**
- Kullanılan Yapay Zekâ Modeli: **Claude 3.7 Sonnet**
  - Not: Claude 3.5 bazı konularda daha dengeli olabilir.
  - GPT-4 şu anda devre dışı/erişilemez.
- Proje Boyutu: **177 dosya, toplam 20,973 satır kod**
- Log Sistemi: Aktif

## 📁 Dosya Tabanlı İnceleme Talimatı

Yapay zekâdan beklentimiz:

1. **Her bir dosyayı satır satır taraması** ve içeriğe hâkim olması.
2. Var olan `README.md`, `TODO.md`, `plan.md`, `log.md` dosyalarını da dahil ederek **bütünsel analiz** yapması.
3. Kod üretmemesi, sadece **"ne var, ne eksik, ne problemli"** olduğunu anlaması.
4. Kod yorumları, kullanılmayan fonksiyonlar, eksik testler, dokümantasyon açıkları vb. tespit etmesi.
5. Eğer bir modülde `TODO.md` yoksa, yeni `TODO` içeriği önerebilecek hale gelmesi.

## 🚧 Geliştirme Durumu ve Dikkat Noktaları

- `TODO.md` dosyalarının çoğu **güncel değil**, bazıları eksik.
- `log.md` dosyaları mevcut fakat düzensiz olabilir.
- Kodlarda `# TODO`, `# FIXME`, `# NOTE` gibi yorum satırları da bulunmakta.
- Kod yeniden düzenlenmeye (refactor) hazır, ancak **öncelik analiz ve kavrama**.

## ✅ AI Talimatı

Aşağıdaki prompt ile işlemlere başlanabilir:

```text
Aşağıda verdiğim yapıyı referans alarak tüm yazılımı analiz et.
Kod yazma, düzeltme veya yorum yapma — sadece dosyaların içeriğine bütünsel şekilde hâkim ol.
Her dosyada ne var, ne eksik, ne güncel değil tespit et. TODO.md dosyaları varsa kontrol et, yoksa gerektiğinde öner.
Amaç, projeye tam hâkimiyet sağlamak. Kod üretme. İlk adım olarak sistem fotoğrafını çıkar.
```

## 🔍 Ek Bilgiler

- Sistem günlüğü log dosyalarında yer almakta.
- `Cursor AI` editörü Claude modelleriyle entegre çalışmaktadır.
- Kod yapısı modülerdir, her bileşen bağımsızdır.

Bu dosyadan bağımsız ama yazılımındosyalarının klasör ve yerlerine mevcut opencart dosyaları ile çakışmaması için bağımsız kendine ait bir bölümde ele almalıyız şuan
admin/controller/common/header.php opencart içidndeki admin/controller/common/header.php dosyası ile çakışıyor e üzerne ayzayım mı diye soruyor yüklerken (tüm yazılım parçalarında bunun öenüne geçmek için yazılımın çalışmasını etkilemeden önelmeler alıp rpofesyonel yapıya kavuşmalıyız)

Opencart dosya uyumlu yapıyada olmalıyız aşağıdaki bilgiler temsilidir. Bizim yazılım mümkün olduğunca opencart parçalarını ne etkilsin nede etkilensin sağlıklı çalışır hizmeti yakalayalım.

OpenCart için Yapay Zekâ Uyumlu Dosya Yapı Önerisi
plaintext
Kopyala
Düzenle
opencart-project/
├── catalog/
│   ├── controller/
│   │   ├── product/
│   │   │   ├── product.php
│   │   │   ├── README.md         ← Bu klasörün işlevi
│   │   │   ├── TODO.md           ← Yapılacak işler
│   │   │   ├── ANALYSIS.md       ← Kod içi problemler, eksikler
│   ├── model/
│   │   ├── catalog/
│   │   │   ├── product.php
│   │   │   ├── README.md
│   ├── view/
│   │   ├── theme/
│   │   │   ├── default/
│   │   │   │   ├── template/
│   │   │   │   │   ├── product/
│   │   │   │   │   │   ├── product.twig
│   │   │   │   │   │   ├── README.md
├── admin/
│   ├── controller/
│   │   ├── catalog/
│   │   │   ├── product.php
│   │   │   ├── README.md
│   │   │   ├── TODO.md
│   ├── model/
│   │   ├── catalog/
│   │   │   ├── product.php
│   ├── view/
│   │   ├── template/
│   │   │   ├── catalog/
│   │   │   │   ├── product_form.twig
├── system/
│   ├── library/
│   │   ├── custom_helper.php
│   │   ├── README.md
├── docs/
│   ├── PROJECT_OVERVIEW.md     ← Genel yapı tanımı
│   ├── TECH_STACK.md           ← Kullanılan sistemler (PHP, MySQL, Twig vs.)
│   ├── STRUCTURE.md            ← Klasör ağacı + açıklama
│   ├── MODULE_GUIDE.md         ← Modül geliştirme rehberi
│   ├── AI_PROMPT_GUIDE.md      ← Yapay zekâya kod nasıl analiz edilecek
│   ├── INSTALL_GUIDE.md        ← Kurulum adımları
├── logs/
│   ├── error.log
│   ├── admin_access.log
├── tests/
│   ├── unit/
│   │   ├── productModelTest.php
│   ├── functional/
│   │   ├── productControllerTest.php
├── .gitignore
├── composer.json (varsa)
├── README.md ← Projenin ana özeti

📘 Açıklamalar:
Dosya/Klasör Adı	Açıklama
README.md	Her modülde “Bu dosya ne işe yarar?” açıklaması
TODO.md	Modüle özel eksikler veya yapılacaklar
ANALYSIS.md	Teknik borçlar, iyileştirme önerileri
AI_PROMPT_GUIDE.md	AI’ın projeye nasıl yaklaşması gerektiğini açıklayan özel rehber
PROJECT_OVERVIEW.md	Tüm sistemin amacı, mantığı, bileşen ilişkileri
TECH_STACK.md	PHP, MySQL, Twig gibi kullanılan teknolojilerin versiyonları

🤖 Yapay Zekânın Verimli Analiz Etmesi İçin Ekstra İpuçları:
Her controller, model, view klasöründe ayrı README.md olmalı.

Dosya başlarında açıklayıcı yorumlar (/** controller for x */) olmalı.

Yapay zekâya yazılımı verirken önce PROJECT_OVERVIEW.md + STRUCTURE.md dosyasını okutmak en etkili yöntemdir.

AI’a “Şu modülü refactor et, plan yap” gibi görevler verebilmek için her klasörde TODO.md'ler kritik.

✅ DOSYA YAPISI DEĞERLENDİRMESİ (Genel Bakış)
Mevcut yapın şu açılardan başarılı:

Özellik	Durum	Not
admin/controller ayrımı	✅	OpenCart yapısına uygun
Her pazar yeri için modül klasörü	✅	Modülerlik iyi
meschain_sync_todo_plan.md	✅	Merkezi kontrol dosyası mevcut
YENI_YAZILIM_HARITASI.md	✅	Geliştirme yol haritası düşünülmüş
README.md	✅	Ana giriş belgesi var
Helper & Language dosya yapısı	⚠️	Mevcut ama eksik içerikli
TODO.md klasör bazlı	❌	Henüz her modülde yok

📌 İyileştirme:
Her pazar yeri modülünün içinde şu dosyalar mutlaka olmalı:

README.md

TODO.md

plan.md

test_cases.md (manuel testler için)

🧭 STRATEJİK YÖNLENDİRME (Adım Adım Plan)
🔹 1. Kök Dizin Kontrolü (1 Günlük İş)
✅ Yapılmış dosyaları tamamla:

 AI_Kod_Analiz_Talimatı.md

 YENI_YAZILIM_HARITASI.md

 meschain_sync_todo_plan.md

 PROJECT_OVERVIEW.md → Genel amaç, modüller, kullanım özeti

 STRUCTURE.md → Klasör yapısını kısa açıklamalarla anlatan dosya

📍 Komut:

text
Kopyala
Düzenle
Lütfen klasör ve dosya yapısını referans alarak STRUCTURE.md dosyasını oluştur.
Her klasörün amacı, içinde neler olmalı kısaca belirt.
🔹 2. Modül Bazlı Yapılandırma (1–3 Günlük Aşamalar)
Her pazaryeri için:

controller, model, view, language dosyalarını tamamla

README.md, TODO.md oluştur

Helper fonksiyonları yaz

📍 Komut:

text
Kopyala
Düzenle
Lütfen extension/mestech/trendyol klasörünü analiz et.
Eksik olan controller, helper, twig, language dosyalarını belirle.
Eğer mevcutsa içerik tutarsızlıklarını raporla ama kod yazma.
🔹 3. İş Takibi & Güncellemeler (Süreç boyunca aktif)
meschain_sync_todo_plan.md dosyasını sürekli güncel tut

AI'ya şunu yazabilirsin:

text
Kopyala
Düzenle
Lütfen meschain_sync_todo_plan.md dosyasını mevcut klasör ve dosya içeriğiyle karşılaştır.
Yapılmamış ama planda yer alan modülleri, yapılmış ama planda olmayanları tespit et.
🔹 4. Test ve Log Sistemlerinin Yapılandırılması
logs/ klasörü: işlem logları, hata kayıtları

tests/ klasörü: her modül için manuel test belgeleri

Örnek: tests/trendyol/test_cases.md

📍 Komut:

text
Kopyala
Düzenle
Lütfen trendyol modülü için test_cases.md öner. Hangi adımlar manuel test edilmelidir?
🔚 SONUÇ VE STRATEJİ
Adım	Hedef	Süre Tahmini
1	Genel yapı dosyalarının tamamlanması	1 gün
2	Her modül için iç dosyaların tamamlanması	3–5 gün
3	Log ve test sisteminin devreye alınması	2 gün
4	AI ile analiz + günlük kontrol sistemi kurulması	Sürekli

📄 İsteğe Bağlı Örnek Komutlar
text
Kopyala
Düzenle
Lütfen trendyol modülünü analiz et. Tüm controller, model, view, language ve helper dosyalarını tek tek incele. 
Hangi dosyada ne var, ne eksik raporla ama kod üretme.
text
Kopyala
Düzenle
meschain_sync_todo_plan.md dosyasını güncellemek için tüm sistemle kıyasla. Geliştirme sürecindeki uyumsuzlukları yaz.
text
Kopyala
Düzenle
Projenin genel fotoğrafını çıkart. Hangi modüller tamamlanmış, hangileri eksik? test ve dokümantasyon açısından hangi parçalar zayıf?
İstersen ilk olarak PROJECT_OVERVIEW.md ve STRUCTURE.md dosyalarını birlikte başlatalım. Hangisini önce hazırlamamı istersin?