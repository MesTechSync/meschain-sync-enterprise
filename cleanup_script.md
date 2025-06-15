# 🧹 MesChain-Sync Temizlik Script'i

Bu dosya, MesChain-Sync projesindeki gereksiz ve tekrar eden dosyaları temizlemek için kullanılacak komutları içerir.

**DİKKAT:** Bu komutları çalıştırmadan önce mutlaka yedek alın!

## Windows PowerShell Komutları

```powershell
# Proje dizinine gidin
cd "C:\Users\musta\OneDrive\Desktop\Mustafa Firma\cursor\MesTech\MesChain-Sync"

# 1. TEKRAR EDEN CONTROLLER DOSYALARINI SİL
Remove-Item "upload\admin\controller\extension\module\trendyol_enhanced.php" -Force
Remove-Item "upload\admin\controller\extension\module\n11_enhanced.php" -Force
Remove-Item "upload\admin\controller\extension\module\n11_optimized.php" -Force
Remove-Item "upload\admin\controller\extension\module\dropshipping_manager.php" -Force

# 2. TÜM .TPL DOSYALARINI SİL (OpenCart 3.x .twig kullanır)
Get-ChildItem -Path "upload\" -Filter "*.tpl" -Recurse | Remove-Item -Force

# 3. MODÜL BAZLI DOKÜMANTASYON DOSYALARINI SİL
Get-ChildItem -Path "upload\admin\controller\extension\module\" -Filter "CHANGELOG_*.md" | Remove-Item -Force
Get-ChildItem -Path "upload\admin\controller\extension\module\" -Filter "VERSIYON_*.md" | Remove-Item -Force
Get-ChildItem -Path "upload\admin\controller\extension\module\" -Filter "LOG_README_*.md" | Remove-Item -Force

# 4. GEREKSİZ VERİTABANI DOSYALARINI SİL
Remove-Item "upload\system\library\entegrator\helper\db_oracle.php" -Force
Remove-Item "upload\system\library\entegrator\helper\db_blockchain.php" -Force
Remove-Item "upload\system\library\entegrator\helper\db_sqlite.php" -Force
Remove-Item "upload\system\library\entegrator\helper\helper_log_example.log" -Force

# 5. YANLIŞ KONUMDAKİ DOSYAYI TAŞI
Move-Item "upload\admin\controller\extension\module\trendyol_dashboard.twig" "upload\admin\view\template\extension\module\" -Force

# 6. BOŞ/DUMMY DOSYALARI SİL
Remove-Item "upload\admin\controller\extension\module\dashboard.php" -Force
Remove-Item "upload\admin\controller\extension\module\config_trendyol.php" -Force
Remove-Item "upload\admin\controller\extension\module\entegrator_controller.log" -Force

# 7. KÜÇÜK DUMMY VIEW DOSYALARINI SİL
Remove-Item "upload\admin\view\template\extension\module\ozon.twig" -Force -ErrorAction SilentlyContinue
Remove-Item "upload\admin\view\template\extension\module\ebay.twig" -Force -ErrorAction SilentlyContinue
Remove-Item "upload\admin\view\template\extension\module\dashboard.twig" -Force -ErrorAction SilentlyContinue

# 8. CSS DOSYASINI DOĞRU KONUMA TAŞI
New-Item -ItemType Directory -Path "upload\admin\view\stylesheet\" -Force
Move-Item "upload\admin\view\template\extension\module\meschain_theme.css" "upload\admin\view\stylesheet\" -Force

# 9. LOGS DİZİNİ OLUŞTUR
New-Item -ItemType Directory -Path "logs" -Force

# 10. HELPER DOSYALARINI DOĞRU DİZİNE TAŞI
New-Item -ItemType Directory -Path "upload\system\library\meschain\helper\" -Force
Move-Item "upload\admin\controller\extension\module\*_helper.php" "upload\system\library\meschain\helper\" -Force

Write-Host "Temizlik tamamlandı!" -ForegroundColor Green
```

## Linux/Mac Bash Komutları

```bash
#!/bin/bash

# Proje dizinine gidin
cd "/path/to/MesChain-Sync"

# 1. TEKRAR EDEN CONTROLLER DOSYALARINI SİL
rm -f upload/admin/controller/extension/module/trendyol_enhanced.php
rm -f upload/admin/controller/extension/module/n11_enhanced.php
rm -f upload/admin/controller/extension/module/n11_optimized.php
rm -f upload/admin/controller/extension/module/dropshipping_manager.php

# 2. TÜM .TPL DOSYALARINI SİL
find upload/ -name "*.tpl" -type f -delete

# 3. MODÜL BAZLI DOKÜMANTASYON DOSYALARINI SİL
rm -f upload/admin/controller/extension/module/CHANGELOG_*.md
rm -f upload/admin/controller/extension/module/VERSIYON_*.md
rm -f upload/admin/controller/extension/module/LOG_README_*.md

# 4. GEREKSİZ VERİTABANI DOSYALARINI SİL
rm -f upload/system/library/entegrator/helper/db_oracle.php
rm -f upload/system/library/entegrator/helper/db_blockchain.php
rm -f upload/system/library/entegrator/helper/db_sqlite.php
rm -f upload/system/library/entegrator/helper/helper_log_example.log

# 5. YANLIŞ KONUMDAKİ DOSYAYI TAŞI
mv upload/admin/controller/extension/module/trendyol_dashboard.twig upload/admin/view/template/extension/module/

# 6. BOŞ/DUMMY DOSYALARI SİL
rm -f upload/admin/controller/extension/module/dashboard.php
rm -f upload/admin/controller/extension/module/config_trendyol.php
rm -f upload/admin/controller/extension/module/entegrator_controller.log

# 7. KÜÇÜK DUMMY VIEW DOSYALARINI SİL
rm -f upload/admin/view/template/extension/module/ozon.twig
rm -f upload/admin/view/template/extension/module/ebay.twig
rm -f upload/admin/view/template/extension/module/dashboard.twig

# 8. CSS DOSYASINI DOĞRU KONUMA TAŞI
mkdir -p upload/admin/view/stylesheet/
mv upload/admin/view/template/extension/module/meschain_theme.css upload/admin/view/stylesheet/

# 9. LOGS DİZİNİ OLUŞTUR
mkdir -p logs

# 10. HELPER DOSYALARINI DOĞRU DİZİNE TAŞI
mkdir -p upload/system/library/meschain/helper/
mv upload/admin/controller/extension/module/*_helper.php upload/system/library/meschain/helper/

echo "Temizlik tamamlandı!"
```

## Manuel Kontrol Listesi

Otomatik temizlik sonrası manuel olarak kontrol edilmesi gerekenler:

1. [ ] Tekrar eden dokümantasyon dosyalarını birleştir
   - CHANGELOG.md (4 farklı yerde)
   - STRUCTURE.md (3 farklı yerde)
   - PROJECT_OVERVIEW.md (2 farklı yerde)

2. [ ] Model dosyalarını oluştur/tamamla
   - model/extension/module/n11.php
   - model/extension/module/hepsiburada.php
   - model/extension/module/amazon.php
   - model/extension/module/ebay.php

3. [ ] Eksik dil dosyalarını ekle
   - language/en-gb/extension/module/*.php
   - language/tr-tr/extension/module/trendyol.php
   - language/tr-tr/extension/module/amazon.php
   - language/tr-tr/extension/module/ebay.php
   - language/tr-tr/extension/module/hepsiburada.php

4. [ ] Base controller'ı kontrol et ve düzelt
   - Tüm modüller `base_marketplace.php` kullanmalı

5. [ ] API implementasyonlarını tamamla
   - eBay API entegrasyonu
   - Amazon API tamamlama
   - N11 backend implementasyonu
   - Hepsiburada backend implementasyonu

## Temizlik Sonrası Yapılacaklar

1. **Test Et:** Temizlik sonrası tüm modüllerin çalıştığından emin ol
2. **Commit Et:** Temizliği git'e commit et
3. **Dokümante Et:** Yapılan değişiklikleri CHANGELOG'a ekle
4. **Yedek Al:** Düzenli yedekleme stratejisi oluştur

---

**Not:** Bu script'i çalıştırmadan önce mutlaka projenin yedeğini alın. Bazı dosyalar silinecek veya taşınacaktır. 