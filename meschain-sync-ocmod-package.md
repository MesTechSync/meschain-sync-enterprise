# MesChain-Sync OCMOD Paketi Oluşturma Kılavuzu

Bu kılavuz, MesChain-Sync projesini OpenCart 3.0.4.0 için OCMOD paketi olarak hazırlamak için gerekli adımları içerir.

## OCMOD Paket Yapısı

```
meschain-sync.ocmod.zip
├── install.xml
└── upload/
    ├── admin/
    │   ├── controller/extension/module/
    │   │   ├── meschain_sync.php
    │   │   ├── trendyol.php
    │   │   ├── n11.php
    │   │   ├── amazon.php
    │   │   ├── hepsiburada.php
    │   │   ├── ozon.php
    │   │   ├── ebay.php
    │   │   ├── base_marketplace.php
    │   │   ├── dropshipping.php
    │   │   ├── user_management.php
    │   │   ├── user_settings.php
    │   │   ├── announcement.php
    │   │   ├── help.php
    │   │   ├── log_viewer.php
    │   │   ├── cache_monitor.php
    │   │   ├── n11_category.php
    │   │   ├── test_encryption.php
    │   │   └── security_helper.php
    │   ├── model/extension/module/
    │   │   ├── meschain_sync.php
    │   │   ├── trendyol.php
    │   │   ├── n11.php
    │   │   ├── amazon.php
    │   │   ├── hepsiburada.php
    │   │   ├── ebay.php
    │   │   └── ozon.php
    │   ├── view/
    │   │   ├── template/extension/module/
    │   │   │   ├── meschain_sync.twig
    │   │   │   ├── trendyol.twig
    │   │   │   ├── trendyol_dashboard.twig
    │   │   │   ├── trendyol_orders.twig
    │   │   │   ├── trendyol_order_detail.twig
    │   │   │   ├── trendyol_product_mapping.twig
    │   │   │   ├── trendyol_reports.twig
    │   │   │   ├── n11.twig
    │   │   │   ├── n11_dashboard.twig
    │   │   │   ├── n11_orders.twig
    │   │   │   ├── n11_order_detail.twig
    │   │   │   ├── n11_cancel_order.twig
    │   │   │   ├── n11_category_form.twig
    │   │   │   ├── n11_category_list.twig
    │   │   │   ├── amazon.twig
    │   │   │   ├── amazon_dashboard.twig
    │   │   │   ├── hepsiburada.twig
    │   │   │   ├── hepsiburada_dashboard.twig
    │   │   │   ├── hepsiburada_orders.twig
    │   │   │   ├── hepsiburada_order_detail.twig
    │   │   │   ├── ozon_dashboard.twig
    │   │   │   ├── ozon_products.twig
    │   │   │   ├── user_settings.twig
    │   │   │   ├── help.twig
    │   │   │   ├── cache_monitor.twig
    │   │   │   ├── test_encryption.twig
    │   │   │   └── ozon/
    │   │   │       ├── dashboard.twig
    │   │   │       ├── settings.twig
    │   │   │       ├── products.twig
    │   │   │       ├── orders.twig
    │   │   │       └── logs.twig
    │   │   └── stylesheet/
    │   │       └── meschain_sync.css
    │   └── language/
    │       ├── tr-tr/extension/module/
    │       │   ├── meschain_sync.php
    │       │   ├── ozon.php
    │       │   ├── n11.php
    │       │   ├── n11_category.php
    │       │   ├── dropshipping.php
    │       │   └── user_management.php
    │       └── en-gb/extension/module/
    │           ├── meschain_sync.php
    │           ├── trendyol.php
    │           ├── n11.php
    │           ├── amazon.php
    │           ├── hepsiburada.php
    │           ├── ozon.php
    │           └── ebay.php
    └── system/
        └── library/
            ├── entegrator/
            │   ├── ozon.php
            │   ├── config.php
            │   ├── config_ozon.php
            │   ├── config_n11.php
            │   ├── config_trendyol.php
            │   ├── config_amazon.php
            │   ├── config_hepsiburada.php
            │   ├── config_ebay.php
            │   └── helper/
            │       └── db_mysql.php
            └── meschain/
                └── helper/
                    ├── trendyol_helper.php
                    ├── n11_helper.php
                    ├── amazon_helper.php
                    ├── hepsiburada_helper.php
                    ├── ozon_helper.php
                    └── ebay_helper.php
```

## Adım 1: Temizlik Script'ini Çalıştırın

Önce `cleanup_script.md` dosyasındaki PowerShell komutlarını çalıştırarak gereksiz dosyaları temizleyin:

```powershell
cd "C:\Users\musta\OneDrive\Desktop\Mustafa Firma\cursor\MesTech\MesChain-Sync"
# cleanup_script.md dosyasındaki komutları çalıştırın
```

## Adım 2: Upload Dizini Oluşturma

```powershell
# Ana OCMOD dizini oluştur
New-Item -ItemType Directory -Path "meschain-sync-ocmod" -Force

# Upload dizinini ve alt dizinlerini oluştur
New-Item -ItemType Directory -Path "meschain-sync-ocmod\upload" -Force
New-Item -ItemType Directory -Path "meschain-sync-ocmod\upload\admin" -Force
New-Item -ItemType Directory -Path "meschain-sync-ocmod\upload\system" -Force
```

## Adım 3: Dosyaları Kopyalama

### Admin Controller Dosyaları
```powershell
# Controller dosyalarını kopyala
$sourceController = "upload\admin\controller\extension\module"
$destController = "meschain-sync-ocmod\upload\admin\controller\extension\module"
New-Item -ItemType Directory -Path $destController -Force

# Ana modül dosyaları
Copy-Item "$sourceController\meschain_sync.php" $destController
Copy-Item "$sourceController\base_marketplace.php" $destController
Copy-Item "$sourceController\trendyol.php" $destController
Copy-Item "$sourceController\n11.php" $destController
Copy-Item "$sourceController\amazon.php" $destController
Copy-Item "$sourceController\hepsiburada.php" $destController
Copy-Item "$sourceController\ozon.php" $destController
Copy-Item "$sourceController\dropshipping.php" $destController
Copy-Item "$sourceController\user_management.php" $destController
Copy-Item "$sourceController\user_settings.php" $destController
Copy-Item "$sourceController\announcement.php" $destController
Copy-Item "$sourceController\help.php" $destController
Copy-Item "$sourceController\log_viewer.php" $destController
Copy-Item "$sourceController\cache_monitor.php" $destController
Copy-Item "$sourceController\n11_category.php" $destController
Copy-Item "$sourceController\test_encryption.php" $destController
Copy-Item "$sourceController\security_helper.php" $destController

# NOT: ebay.php dosyası dummy olduğu için yeniden yazılmalı
```

### Admin Model Dosyaları
```powershell
$sourceModel = "upload\admin\model\extension\module"
$destModel = "meschain-sync-ocmod\upload\admin\model\extension\module"
New-Item -ItemType Directory -Path $destModel -Force

Copy-Item "$sourceModel\ozon.php" $destModel -ErrorAction SilentlyContinue
# Diğer model dosyaları yoksa oluşturulmalı
```

### Admin View Dosyaları
```powershell
$sourceView = "upload\admin\view\template\extension\module"
$destView = "meschain-sync-ocmod\upload\admin\view\template\extension\module"
New-Item -ItemType Directory -Path $destView -Force
New-Item -ItemType Directory -Path "$destView\ozon" -Force

# View dosyalarını kopyala
Copy-Item "$sourceView\*.twig" $destView -ErrorAction SilentlyContinue
Copy-Item "$sourceView\ozon\*.twig" "$destView\ozon" -ErrorAction SilentlyContinue
```

### Language Dosyaları
```powershell
# TR dil dosyaları
$sourceLangTR = "upload\admin\language\tr-tr\extension\module"
$destLangTR = "meschain-sync-ocmod\upload\admin\language\tr-tr\extension\module"
New-Item -ItemType Directory -Path $destLangTR -Force

Copy-Item "$sourceLangTR\*.php" $destLangTR -ErrorAction SilentlyContinue

# EN dil dosyaları
$destLangEN = "meschain-sync-ocmod\upload\admin\language\en-gb\extension\module"
New-Item -ItemType Directory -Path $destLangEN -Force

# meschain_sync.php dosyası daha önce oluşturuldu, diğerleri eklenecek
```

### System Library Dosyaları
```powershell
# Entegrator
$sourceEntegrator = "upload\system\library\entegrator"
$destEntegrator = "meschain-sync-ocmod\upload\system\library\entegrator"
New-Item -ItemType Directory -Path $destEntegrator -Force
New-Item -ItemType Directory -Path "$destEntegrator\helper" -Force

Copy-Item "$sourceEntegrator\*.php" $destEntegrator -ErrorAction SilentlyContinue
Copy-Item "$sourceEntegrator\helper\db_mysql.php" "$destEntegrator\helper" -ErrorAction SilentlyContinue

# MesChain Helper
$destMesChain = "meschain-sync-ocmod\upload\system\library\meschain\helper"
New-Item -ItemType Directory -Path $destMesChain -Force

# Helper dosyaları taşınmalı
```

### CSS Dosyası
```powershell
$destStylesheet = "meschain-sync-ocmod\upload\admin\view\stylesheet"
New-Item -ItemType Directory -Path $destStylesheet -Force

# CSS dosyasını kopyala veya oluştur
```

## Adım 4: install.xml Dosyasını Kopyalama

```powershell
Copy-Item "meschain-sync.ocmod\install.xml" "meschain-sync-ocmod\" -Force
```

## Adım 5: ZIP Paketi Oluşturma

```powershell
# PowerShell ile ZIP oluşturma
Compress-Archive -Path "meschain-sync-ocmod\*" -DestinationPath "meschain-sync.ocmod.zip" -Force
```

## Adım 6: OpenCart'a Yükleme

1. OpenCart admin paneline giriş yapın
2. Extensions > Installer'a gidin
3. Upload butonuna tıklayın
4. `meschain-sync.ocmod.zip` dosyasını seçin
5. Yükleme tamamlandıktan sonra Extensions > Modifications'a gidin
6. Refresh butonuna tıklayın
7. Extensions > Extensions > Modules'a gidin
8. MesChain Sync modülünü bulun ve Install edin

## Önemli Notlar

1. **Veritabanı Tabloları**: install.xml içindeki SQL komutları ilk kurulumda otomatik çalışmayabilir. Manuel olarak çalıştırmanız gerekebilir.

2. **Eksik Model Dosyaları**: Birçok marketplace için model dosyaları eksik. Bunların oluşturulması gerekiyor.

3. **Dil Dosyaları**: Sadece meschain_sync için İngilizce dil dosyası var, diğer modüller için eklenmelidir.

4. **Helper Dosyaları**: Controller dizinindeki helper dosyaları system/library/meschain/helper dizinine taşınmalı.

5. **Temizlik**: Tekrar eden ve gereksiz dosyalar temizlenmeli.

## Sorun Giderme

**Modification'da görünmüyorsa:**
- install.xml dosyasının UTF-8 kodlamasında olduğundan emin olun
- XML syntax hatası olmadığını kontrol edin
- OpenCart error.log dosyasını kontrol edin

**Dosyalar yüklenmiyorsa:**
- ZIP dosyası yapısının doğru olduğundan emin olun
- Dosya izinlerini kontrol edin (755 dizinler, 644 dosyalar)

**SQL hataları alıyorsanız:**
- Tablo prefix'inin doğru olduğundan emin olun
- SQL komutlarını phpMyAdmin'den manuel çalıştırın

---

Bu kılavuz, MesChain-Sync projesini OpenCart 3.0.4.0'a OCMOD olarak yüklemek için gerekli tüm adımları içerir. 