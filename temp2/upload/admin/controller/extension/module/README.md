# Klasör: admin/controller/extension/module

## Amaç
Bu klasör, OpenCart yönetici panelinde modül controller dosyalarını barındırır. Her modül için ayrı bir PHP dosyası bulunur.

## İçerik
- Modül controller dosyaları (ör: entegrator.php)
- Her dosya, ilgili modülün yönetim paneli işlevlerini kontrol eder.

## Standartlar
- Dosya isimleri: <modül_adı>.php
- Her dosyanın başında açıklama bloğu bulunmalı.
- Hatalar ve önemli işlemler için log kaydı tutulmalı.

## Örnek Dosya
- entegrator.php

## Log Şablonu
- Her işlem için ayrı log dosyası (ör: entegrator_controller.log)
- Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]

---
Bu klasördeki her dosya ve log, hem geliştiriciler hem de yapay zekâlar için kolayca anlaşılır olmalıdır. 