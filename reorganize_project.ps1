# MesChain-Sync Proje Reorganizasyon Scripti
# Bu script dosyaları OpenCart standartlarına uygun olarak yeniden organize eder

param(
    [Parameter()]
    [switch]$DryRun = $false,
    
    [Parameter()]
    [switch]$Force = $false
)

Write-Host "`n=====================================" -ForegroundColor Cyan
Write-Host "MesChain-Sync Reorganizasyon" -ForegroundColor Cyan
Write-Host "=====================================`n" -ForegroundColor Cyan

# Güvenlik kontrolü
if (-not $Force -and -not $DryRun) {
    Write-Host "[UYARI] Bu işlem dosyaları taşıyacak ve dizin yapısını değiştirecek!" -ForegroundColor Yellow
    Write-Host "Önce -DryRun parametresi ile test edin." -ForegroundColor Yellow
    return
}

# Oluşturulacak dizinler
$directoriesToCreate = @(
    "upload/system/library/meschain/api",
    "upload/system/library/meschain/helper",
    "upload/system/library/meschain/logger",
    "upload/admin/view/stylesheet/meschain",
    "docs",
    "docs/api",
    "docs/modules",
    "config"
)

# Taşınacak dosyalar
$filesToMove = @{
    # CSS dosyaları
    "upload/admin/view/template/extension/module/meschain_theme.css" = "upload/admin/view/stylesheet/meschain/theme.css"
    
    # Yanlış konumdaki helper'lar (artık silindiler, yenileri oluşturulacak)
    
    # Controller'daki dashboard template'i
    "upload/admin/controller/extension/module/trendyol_dashboard.twig" = "upload/admin/view/template/extension/module/trendyol_dashboard.twig"
}

# Birleştirilecek dokümantasyon dosyaları
$docsToConsolidate = @{
    "CHANGELOG.md" = @(
        "CHANGELOG.md",
        "meschain-sync/CHANGELOG.md",
        "docs/CHANGELOG.md",
        "upload/admin/controller/extension/module/CHANGELOG.md"
    )
    "README.md" = @(
        "README.md",
        "upload/README.md", 
        "upload/admin/README.md"
    )
    "PROJECT_OVERVIEW.md" = @(
        "PROJECT_OVERVIEW.md",
        "meschain-sync/PROJECT_OVERVIEW.md"
    )
}

# İşlem özeti
Write-Host "İşlem Planı:" -ForegroundColor Yellow
Write-Host "============" -ForegroundColor Yellow

# Dizin oluşturma
Write-Host "`n1. Oluşturulacak Dizinler:" -ForegroundColor Cyan
foreach ($dir in $directoriesToCreate) {
    if (-not (Test-Path $dir)) {
        Write-Host "   [+] $dir" -ForegroundColor Green
    } else {
        Write-Host "   [✓] $dir (zaten var)" -ForegroundColor Gray
    }
}

# Dosya taşıma
Write-Host "`n2. Taşınacak Dosyalar:" -ForegroundColor Cyan
foreach ($source in $filesToMove.Keys) {
    if (Test-Path $source) {
        Write-Host "   [→] $source" -ForegroundColor Yellow
        Write-Host "       └─> $($filesToMove[$source])" -ForegroundColor Green
    }
}

# Dokümantasyon birleştirme
Write-Host "`n3. Birleştirilecek Dokümantasyon:" -ForegroundColor Cyan
foreach ($target in $docsToConsolidate.Keys) {
    $sources = $docsToConsolidate[$target] | Where-Object { Test-Path $_ }
    if ($sources.Count -gt 1) {
        Write-Host "   [≡] docs/$target ← $($sources.Count) dosya" -ForegroundColor Yellow
    }
}

if ($DryRun) {
    Write-Host "`n[DRY RUN] Hiçbir değişiklik yapılmadı." -ForegroundColor Cyan
    Write-Host "Gerçek reorganizasyon için: .\reorganize_project.ps1 -Force" -ForegroundColor Cyan
    return
}

# İşlemleri gerçekleştir
Write-Host "`nReorganizasyon başlıyor..." -ForegroundColor Green

# 1. Dizinleri oluştur
foreach ($dir in $directoriesToCreate) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "[OK] Dizin oluşturuldu: $dir" -ForegroundColor Green
    }
}

# 2. Dosyaları taşı
foreach ($source in $filesToMove.Keys) {
    if (Test-Path $source) {
        $dest = $filesToMove[$source]
        $destDir = Split-Path $dest -Parent
        
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        
        Move-Item -Path $source -Destination $dest -Force
        Write-Host "[OK] Taşındı: $source → $dest" -ForegroundColor Green
    }
}

# 3. Dokümantasyonu birleştir
foreach ($target in $docsToConsolidate.Keys) {
    $sources = $docsToConsolidate[$target] | Where-Object { Test-Path $_ }
    
    if ($sources.Count -gt 0) {
        $targetPath = "docs/$target"
        $content = ""
        
        foreach ($source in $sources) {
            if ($content) {
                $content += "`n`n---`n`n"
            }
            $content += Get-Content $source -Raw
        }
        
        $content | Out-File -FilePath $targetPath -Encoding UTF8
        Write-Host "[OK] Birleştirildi: docs/$target ($($sources.Count) dosya)" -ForegroundColor Green
        
        # Eski dosyaları sil (ilk dosya hariç)
        foreach ($source in $sources | Select-Object -Skip 1) {
            Remove-Item -Path $source -Force
            Write-Host "  [-] Silindi: $source" -ForegroundColor Gray
        }
    }
}

# 4. Örnek yapılandırma dosyaları oluştur
Write-Host "`n4. Yapılandırma Dosyaları Oluşturuluyor..." -ForegroundColor Cyan

# Örnek .env dosyası
$envExample = @'
# MesChain-Sync Ortam Değişkenleri
# Bu dosyayı .env olarak kopyalayın ve değerleri doldurun

# Trendyol API
TRENDYOL_API_KEY=your_api_key_here
TRENDYOL_API_SECRET=your_api_secret_here
TRENDYOL_SUPPLIER_ID=your_supplier_id_here

# N11 API
N11_API_KEY=your_api_key_here
N11_API_SECRET=your_api_secret_here

# Amazon API
AMAZON_SELLER_ID=your_seller_id_here
AMAZON_MWS_TOKEN=your_mws_token_here

# Diğer API'ler...
'@

$envExample | Out-File -FilePath "config/.env.example" -Encoding UTF8
Write-Host "[OK] Oluşturuldu: config/.env.example" -ForegroundColor Green

# .cursorrules dosyası
$cursorRules = @'
# MesChain-Sync Cursor.ai Kuralları

## Proje Hakkında
MesChain-Sync, OpenCart 3.0.4.0 tabanlı çoklu pazaryeri entegrasyon sistemidir.

## Kodlama Standartları
- PHP 7.4+ uyumlu kod yazın
- OpenCart MVC(L) yapısına uyun
- Her fonksiyon için PHPDoc yorumu ekleyin
- Hata işleme ve loglama kullanın

## Dizin Yapısı
- Controller: upload/admin/controller/extension/module/
- Model: upload/admin/model/extension/module/
- View: upload/admin/view/template/extension/module/
- Language: upload/admin/language/
- Helper: upload/system/library/meschain/helper/

## Önemli Notlar
- .tpl dosyaları kullanmayın, sadece .twig
- Helper dosyaları system/library altında olmalı
- Her modül için ayrı log dosyası kullanın
- API anahtarlarını config/ dizininde saklayın

## Test Edilmiş Modüller
- Trendyol: %80 tamamlandı
- Ozon: %65 tamamlandı
- N11: %30 tamamlandı
- Diğerleri: Başlangıç aşamasında
'@

$cursorRules | Out-File -FilePath ".cursorrules" -Encoding UTF8
Write-Host "[OK] Oluşturuldu: .cursorrules" -ForegroundColor Green

# 5. Özet rapor
$reportDate = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
$dirsText = $directoriesToCreate -join "`n"

$reportContent = @"
# MesChain-Sync Reorganizasyon Raporu
Tarih: $reportDate

## Yapılan İşlemler

### 1. Oluşturulan Dizinler
$dirsText

### 2. Taşınan Dosyalar
$(foreach ($s in $filesToMove.Keys) { if (Test-Path $filesToMove[$s]) { "- $s → $($filesToMove[$s])" } })

### 3. Birleştirilen Dokümantasyon
$(foreach ($t in $docsToConsolidate.Keys) { if (Test-Path "docs/$t") { "- docs/$t" } })

### 4. Oluşturulan Yapılandırma Dosyaları
- config/.env.example
- .cursorrules

## Sonraki Adımlar
1. Helper dosyalarını system/library/meschain/helper/ altında yeniden oluşturun
2. Model dosyalarını eksik modüller için tamamlayın
3. API anahtarlarını config/.env dosyasına taşıyın
4. Dokümantasyonu güncelleyin
"@

$reportContent | Out-File -FilePath "docs/reorganization_report.md" -Encoding UTF8

Write-Host "`n=====================================" -ForegroundColor Green
Write-Host "REORGANİZASYON TAMAMLANDI" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green
Write-Host "Rapor: docs/reorganization_report.md" -ForegroundColor Cyan

# Temizlik sonrası yedekleme önerisi
Write-Host "`n[ÖNERİ] Reorganizasyon sonrası yedekleme yapın:" -ForegroundColor Yellow
Write-Host '.\backup_system.ps1 -Action manual -Description "reorganizasyon_sonrasi"' -ForegroundColor White 