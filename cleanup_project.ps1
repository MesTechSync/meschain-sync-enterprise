# MesChain-Sync Proje Temizlik Scripti
# Bu script tekrar eden ve gereksiz dosyaları güvenli bir şekilde temizler

param(
    [Parameter()]
    [switch]$DryRun = $false,
    
    [Parameter()]
    [switch]$Force = $false
)

# Renk kodları
$colors = @{
    'Success' = 'Green'
    'Warning' = 'Yellow'
    'Error' = 'Red'
    'Info' = 'Cyan'
}

function Write-ColorOutput {
    param($Message, $Type = 'Info')
    Write-Host $Message -ForegroundColor $colors[$Type]
}

Write-ColorOutput "`n=====================================" "Info"
Write-ColorOutput "MesChain-Sync Temizlik Sistemi" "Info"
Write-ColorOutput "=====================================`n" "Info"

# Güvenlik kontrolü
if (-not $Force) {
    $backupCheck = Get-ChildItem -Path "backups" -Filter "*temizlik_oncesi*" -ErrorAction SilentlyContinue | 
                   Sort-Object LastWriteTime -Descending | Select-Object -First 1
    
    if (-not $backupCheck) {
        Write-ColorOutput "[HATA] Temizlik öncesi yedek bulunamadı!" "Error"
        Write-ColorOutput "Önce yedekleme yapın: .\backup_system.ps1 -Action manual -Description 'temizlik_oncesi'" "Warning"
        return
    }
    
    Write-ColorOutput "[OK] Yedek bulundu: $($backupCheck.Name)" "Success"
}

# Temizlenecek dosyalar listesi
$filesToDelete = @{
    "Tekrar Eden Controller Dosyaları" = @(
        "upload/admin/controller/extension/module/trendyol_enhanced.php",
        "upload/admin/controller/extension/module/n11_enhanced.php",
        "upload/admin/controller/extension/module/n11_optimized.php",
        "upload/admin/controller/extension/module/dropshipping_manager.php"
    )
    
    "Yanlış Konumdaki Helper Dosyaları" = @(
        "upload/admin/controller/extension/module/amazon_helper.php",
        "upload/admin/controller/extension/module/ebay_helper.php",
        "upload/admin/controller/extension/module/hepsiburada_helper.php",
        "upload/admin/controller/extension/module/n11_helper.php",
        "upload/admin/controller/extension/module/ozon_helper.php",
        "upload/admin/controller/extension/module/security_helper.php"
    )
    
    "Eski Template Dosyaları (.tpl)" = @(
        "upload/admin/view/template/extension/module/announcement_form.tpl",
        "upload/admin/view/template/extension/module/announcement_list.tpl",
        "upload/admin/view/template/extension/module/announcement_popup.tpl",
        "upload/admin/view/template/extension/module/help.tpl",
        "upload/admin/view/template/extension/module/user_profile.tpl",
        "upload/admin/view/template/extension/module/user_settings.tpl"
    )
    
    "Gereksiz Database Helper Dosyaları" = @(
        "upload/system/library/entegrator/helper/db_oracle.php",
        "upload/system/library/entegrator/helper/db_blockchain.php",
        "upload/system/library/entegrator/helper/db_sqlite.php"
    )
    
    "Log ve Geçici Dosyalar" = @(
        "upload/admin/controller/extension/module/entegrator_controller.log",
        "upload/admin/controller/extension/module/*.log"
    )
    
    "Boş veya Dummy Dosyalar" = @(
        "upload/admin/controller/extension/module/dashboard.php",
        "upload/admin/controller/extension/module/ebay.php",
        "upload/admin/controller/extension/module/config_trendyol.php"
    )
    
    "Tekrar Eden Dokümantasyon" = @(
        "upload/admin/controller/extension/module/CHANGELOG_*.md",
        "upload/admin/controller/extension/module/VERSIYON_*.md",
        "upload/admin/controller/extension/module/LOG_README_*.md",
        "upload/admin/controller/extension/module/README.md",
        "upload/admin/controller/extension/module/STRUCTURE.md"
    )
}

# Temizlik özeti
$totalFiles = 0
$totalSize = 0

Write-ColorOutput "Temizlenecek Dosyalar:" "Warning"
Write-ColorOutput ("=" * 50) "Info"

foreach ($category in $filesToDelete.Keys) {
    Write-ColorOutput "`n[$category]" "Info"
    
    foreach ($pattern in $filesToDelete[$category]) {
        $files = Get-ChildItem -Path $pattern -ErrorAction SilentlyContinue
        
        if ($files) {
            foreach ($file in $files) {
                $sizeMB = [math]::Round($file.Length / 1MB, 3)
                Write-Host "  - $($file.FullName) ($sizeMB MB)"
                $totalFiles++
                $totalSize += $file.Length
            }
        }
    }
}

$totalSizeMB = [math]::Round($totalSize / 1MB, 2)
Write-ColorOutput "`n`nÖzet:" "Info"
Write-ColorOutput "  Toplam dosya: $totalFiles" "Warning"
Write-ColorOutput "  Toplam boyut: $totalSizeMB MB" "Warning"

# Dry run modu
if ($DryRun) {
    Write-ColorOutput "`n[DRY RUN] Hiçbir dosya silinmedi." "Info"
    Write-ColorOutput "Gerçek temizlik için: .\cleanup_project.ps1 -Force" "Info"
    return
}

# Onay al
if (-not $Force) {
    Write-ColorOutput "`nBu dosyaları silmek istediğinizden emin misiniz?" "Warning"
    $confirm = Read-Host "Devam etmek için 'EVET' yazın"
    
    if ($confirm -ne "EVET") {
        Write-ColorOutput "`n[İPTAL] Temizlik iptal edildi." "Warning"
        return
    }
}

# Temizliği gerçekleştir
Write-ColorOutput "`nTemizlik başlıyor..." "Info"

$deletedCount = 0
$errorCount = 0
$logFile = "logs/cleanup_$(Get-Date -Format 'yyyy-MM-dd_HH-mm-ss').log"

# Log başlığı
$logContent = @"
MesChain-Sync Temizlik Log
Tarih: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
=====================================

"@

foreach ($category in $filesToDelete.Keys) {
    $logContent += "`n[$category]`n"
    
    foreach ($pattern in $filesToDelete[$category]) {
        $files = Get-ChildItem -Path $pattern -ErrorAction SilentlyContinue
        
        if ($files) {
            foreach ($file in $files) {
                try {
                    $fileInfo = "$($file.FullName) ($('{0:N3}' -f ($file.Length / 1MB)) MB)"
                    Remove-Item -Path $file.FullName -Force
                    Write-ColorOutput "  [SİLİNDİ] $fileInfo" "Success"
                    $logContent += "  [SİLİNDİ] $fileInfo`n"
                    $deletedCount++
                }
                catch {
                    Write-ColorOutput "  [HATA] $($file.FullName): $_" "Error"
                    $logContent += "  [HATA] $($file.FullName): $_`n"
                    $errorCount++
                }
            }
        }
    }
}

# Boş dizinleri temizle
Write-ColorOutput "`nBoş dizinler temizleniyor..." "Info"
$emptyDirs = Get-ChildItem -Path "upload" -Recurse -Directory | 
             Where-Object { (Get-ChildItem $_.FullName -Force).Count -eq 0 } |
             Sort-Object { $_.FullName.Length } -Descending

foreach ($dir in $emptyDirs) {
    try {
        Remove-Item -Path $dir.FullName -Force
        Write-ColorOutput "  [KALDIRILDI] $($dir.FullName)" "Success"
        $logContent += "  [BOŞ DİZİN KALDIRILDI] $($dir.FullName)`n"
    }
    catch {
        # Boş dizin silinemezse sessizce devam et
    }
}

# Özet
$logContent += @"

=====================================
ÖZET:
  Silinen dosya: $deletedCount
  Hata: $errorCount
  Temizlenen alan: $totalSizeMB MB
=====================================
"@

# Log kaydet
$logContent | Out-File -FilePath $logFile -Encoding UTF8

Write-ColorOutput "`n=====================================" "Info"
Write-ColorOutput "TEMİZLİK TAMAMLANDI" "Success"
Write-ColorOutput "=====================================" "Info"
Write-ColorOutput "  Silinen dosya: $deletedCount" "Success"
Write-ColorOutput "  Hata: $errorCount" $(if ($errorCount -gt 0) { "Error" } else { "Success" })
Write-ColorOutput "  Temizlenen alan: $totalSizeMB MB" "Success"
Write-ColorOutput "  Log dosyası: $logFile" "Info"

# Reorganizasyon önerisi
Write-ColorOutput "`n[ÖNERİ] Şimdi dosya reorganizasyonu yapabilirsiniz:" "Info"
Write-ColorOutput "  1. Helper dosyalarını system/library/meschain/helper/ dizinine taşıyın" "Info"
Write-ColorOutput "  2. CSS dosyalarını view/stylesheet/ dizinine taşıyın" "Info"
Write-ColorOutput "  3. Dokümantasyonu tek bir docs/ dizininde toplayın" "Info" 