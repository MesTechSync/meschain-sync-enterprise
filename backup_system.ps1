# MesChain-Sync Gelişmiş Yedekleme Sistemi
# PowerShell Script

param(
    [Parameter()]
    [string]$Action = "help",
    
    [Parameter()]
    [string]$Description = ""
)

# Yapılandırma
$projectRoot = Get-Location
$backupDir = Join-Path $projectRoot "backups"
$logsDir = Join-Path $projectRoot "logs"
$excludePatterns = @(
    "*.git*",
    "*\backups\*",
    "*\logs\*", 
    "*\cache\*",
    "*\node_modules\*",
    "*\vendor\*",
    "*.tmp",
    "*.log"
)

# Dizinleri oluştur
if (-not (Test-Path $backupDir)) {
    New-Item -ItemType Directory -Path $backupDir | Out-Null
    Write-Host "[OK] Backup dizini oluşturuldu: $backupDir" -ForegroundColor Green
}

if (-not (Test-Path $logsDir)) {
    New-Item -ItemType Directory -Path $logsDir | Out-Null
    Write-Host "[OK] Logs dizini oluşturuldu: $logsDir" -ForegroundColor Green
}

# Log fonksiyonu
function Write-BackupLog {
    param($Message, $Level = "INFO")
    
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] [$Level] $Message"
    $logFile = Join-Path $logsDir "backup_system.log"
    
    Add-Content -Path $logFile -Value $logMessage
    
    switch ($Level) {
        "ERROR" { Write-Host $logMessage -ForegroundColor Red }
        "WARNING" { Write-Host $logMessage -ForegroundColor Yellow }
        "SUCCESS" { Write-Host $logMessage -ForegroundColor Green }
        default { Write-Host $logMessage }
    }
}

# Manuel yedekleme
function New-ManualBackup {
    param($Description)
    
    Write-Host "`n======================================" -ForegroundColor Cyan
    Write-Host "Manuel Yedekleme Başlatılıyor" -ForegroundColor Cyan
    Write-Host "======================================`n" -ForegroundColor Cyan
    
    $timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
    $backupName = "manual_backup_$timestamp"
    
    if ($Description) {
        $safeName = $Description -replace '[^a-zA-Z0-9_-]', '_'
        $backupName += "_$safeName"
    }
    
    $zipPath = Join-Path $backupDir "$backupName.zip"
    
    Write-BackupLog "Manuel yedekleme başlatılıyor: $backupName"
    
    # Dosya listesi oluştur
    Write-Host "Dosyalar taranıyor..." -ForegroundColor Yellow
    
    $files = Get-ChildItem -Path $projectRoot -Recurse -File | Where-Object {
        $include = $true
        foreach ($pattern in $excludePatterns) {
            if ($_.FullName -like $pattern) {
                $include = $false
                break
            }
        }
        return $include
    }
    
    $fileCount = $files.Count
    $totalSize = ($files | Measure-Object -Property Length -Sum).Sum
    $totalSizeMB = [math]::Round($totalSize / 1MB, 2)
    
    Write-Host "Toplam $fileCount dosya bulundu ($totalSizeMB MB)" -ForegroundColor Yellow
    
    # ZIP oluştur
    Write-Host "Yedekleme oluşturuluyor..." -ForegroundColor Yellow
    
    try {
        # Metadata oluştur
        $metadata = @{
            BackupName = $backupName
            BackupType = "manual"
            Description = $Description
            Timestamp = (Get-Date).ToString("o")
            FileCount = $fileCount
            TotalSize = $totalSize
            ProjectRoot = $projectRoot.Path
            ExcludePatterns = $excludePatterns
        }
        
        $metadataFile = Join-Path $env:TEMP "backup_metadata.json"
        $metadata | ConvertTo-Json -Depth 10 | Out-File $metadataFile -Encoding UTF8
        
        # Geçici dizin oluştur
        $tempDir = Join-Path $env:TEMP $backupName
        New-Item -ItemType Directory -Path $tempDir -Force | Out-Null
        
        # Dosyaları kopyala
        $progress = 0
        foreach ($file in $files) {
            $relativePath = $file.FullName.Substring($projectRoot.Path.Length + 1)
            $destPath = Join-Path $tempDir $relativePath
            $destDir = Split-Path $destPath -Parent
            
            if (-not (Test-Path $destDir)) {
                New-Item -ItemType Directory -Path $destDir -Force | Out-Null
            }
            
            Copy-Item -Path $file.FullName -Destination $destPath -Force
            
            $progress++
            if ($progress % 100 -eq 0) {
                Write-Progress -Activity "Dosyalar kopyalanıyor" -Status "$progress / $fileCount" -PercentComplete (($progress / $fileCount) * 100)
            }
        }
        
        # Metadata'yı ekle
        Copy-Item -Path $metadataFile -Destination (Join-Path $tempDir "backup_metadata.json")
        
        # ZIP oluştur
        Write-Progress -Activity "ZIP arşivi oluşturuluyor" -Status "Lütfen bekleyin..."
        
        Add-Type -Assembly "System.IO.Compression.FileSystem"
        [System.IO.Compression.ZipFile]::CreateFromDirectory($tempDir, $zipPath, [System.IO.Compression.CompressionLevel]::Optimal, $false)
        
        # Temizlik
        Remove-Item -Path $tempDir -Recurse -Force
        Remove-Item -Path $metadataFile -Force
        
        $zipSize = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
        
        Write-Progress -Completed -Activity "Tamamlandı"
        
        Write-BackupLog "Yedekleme tamamlandı: $backupName ($fileCount dosya, $zipSize MB)" "SUCCESS"
        
        Write-Host "`n[OK] Yedekleme başarıyla tamamlandı!" -ForegroundColor Green
        Write-Host "Dosya: $zipPath" -ForegroundColor Green
        Write-Host "Boyut: $zipSize MB" -ForegroundColor Green
        Write-Host "Dosya Sayısı: $fileCount" -ForegroundColor Green
        
        # Eski yedekleri temizle
        Remove-OldBackups
        
        return @{
            Success = $true
            BackupName = $backupName
            BackupPath = $zipPath
            FileCount = $fileCount
            SizeMB = $zipSize
        }
    }
    catch {
        Write-BackupLog "Yedekleme hatası: $_" "ERROR"
        Write-Host "`n[HATA] Yedekleme başarısız: $_" -ForegroundColor Red
        return @{ Success = $false; Error = $_.ToString() }
    }
}

# Yedekleri listele
function Get-BackupList {
    Write-Host "`n======================================" -ForegroundColor Cyan
    Write-Host "Mevcut Yedekler" -ForegroundColor Cyan
    Write-Host "======================================`n" -ForegroundColor Cyan
    
    $backups = Get-ChildItem -Path $backupDir -Filter "*.zip" | Sort-Object LastWriteTime -Descending
    
    if ($backups.Count -eq 0) {
        Write-Host "Yedek bulunamadı." -ForegroundColor Yellow
        return
    }
    
    $backupList = @()
    
    foreach ($backup in $backups) {
        $sizeMB = [math]::Round($backup.Length / 1MB, 2)
        $backupInfo = @{
            Name = $backup.Name
            Size = "$sizeMB MB"
            Date = $backup.LastWriteTime.ToString("yyyy-MM-dd HH:mm:ss")
            Type = if ($backup.Name -match "^manual_") { "Manual" } else { "Auto" }
        }
        
        # Metadata oku
        try {
            Add-Type -Assembly "System.IO.Compression.FileSystem"
            $zip = [System.IO.Compression.ZipFile]::OpenRead($backup.FullName)
            $metadataEntry = $zip.Entries | Where-Object { $_.Name -eq "backup_metadata.json" } | Select-Object -First 1
            
            if ($metadataEntry) {
                $reader = New-Object System.IO.StreamReader($metadataEntry.Open())
                $metadataJson = $reader.ReadToEnd()
                $reader.Close()
                $metadata = $metadataJson | ConvertFrom-Json
                
                if ($metadata.Description) {
                    $backupInfo.Description = $metadata.Description
                }
                if ($metadata.FileCount) {
                    $backupInfo.FileCount = $metadata.FileCount
                }
            }
            
            $zip.Dispose()
        }
        catch {
            # Metadata okunamazsa devam et
        }
        
        $backupList += $backupInfo
    }
    
    # Tablo olarak göster
    $backupList | Format-Table -Property @(
        @{Label="Dosya Adı"; Expression={$_.Name}; Width=50}
        @{Label="Boyut"; Expression={$_.Size}; Width=10}
        @{Label="Tarih"; Expression={$_.Date}; Width=20}
        @{Label="Tür"; Expression={$_.Type}; Width=8}
        @{Label="Açıklama"; Expression={$_.Description}; Width=30}
    ) -AutoSize
    
    Write-Host "`nToplam: $($backups.Count) yedek" -ForegroundColor Yellow
    $totalSize = [math]::Round(($backups | Measure-Object -Property Length -Sum).Sum / 1MB, 2)
    Write-Host "Toplam Boyut: $totalSize MB" -ForegroundColor Yellow
}

# Eski yedekleri temizle
function Remove-OldBackups {
    $maxAutoBackups = 10
    
    $autoBackups = Get-ChildItem -Path $backupDir -Filter "auto_backup_*.zip" | Sort-Object LastWriteTime -Descending
    
    if ($autoBackups.Count -gt $maxAutoBackups) {
        $toDelete = $autoBackups | Select-Object -Skip $maxAutoBackups
        
        foreach ($backup in $toDelete) {
            Remove-Item -Path $backup.FullName -Force
            Write-BackupLog "Eski yedek silindi: $($backup.Name)" "WARNING"
        }
        
        Write-Host "`n[INFO] $($toDelete.Count) eski otomatik yedek silindi." -ForegroundColor Yellow
    }
}

# Temizlik öncesi analiz
function Get-CleanupAnalysis {
    Write-Host "`n======================================" -ForegroundColor Cyan
    Write-Host "Temizlik Öncesi Analiz" -ForegroundColor Cyan
    Write-Host "======================================`n" -ForegroundColor Cyan
    
    Write-BackupLog "Temizlik analizi başlatılıyor"
    
    # Tekrar eden dosyaları bul
    Write-Host "Tekrar eden dosyalar aranıyor..." -ForegroundColor Yellow
    
    $duplicates = @{
        "trendyol.php" = @(
            "upload/admin/controller/extension/module/trendyol.php",
            "upload/admin/controller/extension/module/trendyol_enhanced.php"
        )
        "n11.php" = @(
            "upload/admin/controller/extension/module/n11.php",
            "upload/admin/controller/extension/module/n11_enhanced.php",
            "upload/admin/controller/extension/module/n11_optimized.php"
        )
        "dropshipping.php" = @(
            "upload/admin/controller/extension/module/dropshipping.php",
            "upload/admin/controller/extension/module/dropshipping_manager.php"
        )
    }
    
    Write-Host "`nTekrar Eden Dosyalar:" -ForegroundColor Red
    foreach ($key in $duplicates.Keys) {
        Write-Host "`n  $key grubunda:" -ForegroundColor Yellow
        foreach ($file in $duplicates[$key]) {
            if (Test-Path $file) {
                $size = (Get-Item $file).Length / 1KB
                Write-Host "    - $file ($([math]::Round($size, 2)) KB)" -ForegroundColor White
            }
        }
    }
    
    # Gereksiz dosyaları bul
    Write-Host "`n`nGereksiz Dosyalar:" -ForegroundColor Red
    
    $unnecessaryFiles = @(
        "upload/admin/controller/extension/module/*.log",
        "upload/admin/controller/extension/module/*_helper.php",
        "upload/admin/view/template/extension/module/*.tpl",
        "upload/system/library/entegrator/helper/db_oracle.php",
        "upload/system/library/entegrator/helper/db_blockchain.php",
        "upload/system/library/entegrator/helper/db_sqlite.php"
    )
    
    $totalUnnecessary = 0
    foreach ($pattern in $unnecessaryFiles) {
        $files = Get-ChildItem -Path $pattern -ErrorAction SilentlyContinue
        foreach ($file in $files) {
            Write-Host "  - $($file.FullName) ($([math]::Round($file.Length / 1KB, 2)) KB)" -ForegroundColor White
            $totalUnnecessary += $file.Length
        }
    }
    
    Write-Host "`n`nÖzet:" -ForegroundColor Cyan
    Write-Host "  - Toplam gereksiz dosya boyutu: $([math]::Round($totalUnnecessary / 1MB, 2)) MB" -ForegroundColor Yellow
    Write-Host "  - Önerilen işlem: Yedekleme sonrası temizlik" -ForegroundColor Green
    
    Write-BackupLog "Temizlik analizi tamamlandı"
}

# Ana işlem
switch ($Action.ToLower()) {
    "manual" {
        New-ManualBackup -Description $Description
    }
    "list" {
        Get-BackupList
    }
    "clean" {
        Remove-OldBackups
        Write-Host "`n[OK] Eski yedekler temizlendi." -ForegroundColor Green
    }
    "analyze" {
        Get-CleanupAnalysis
    }
    default {
        Write-Host "`n======================================" -ForegroundColor Cyan
        Write-Host "MesChain-Sync Yedekleme Sistemi" -ForegroundColor Cyan
        Write-Host "======================================`n" -ForegroundColor Cyan
        Write-Host "Kullanım:" -ForegroundColor Yellow
        Write-Host "  .\backup_system.ps1 -Action manual [-Description 'açıklama']  - Manuel yedekleme"
        Write-Host "  .\backup_system.ps1 -Action list                               - Yedekleri listele"
        Write-Host "  .\backup_system.ps1 -Action clean                              - Eski yedekleri temizle"
        Write-Host "  .\backup_system.ps1 -Action analyze                            - Temizlik analizi"
        Write-Host "`nÖrnekler:" -ForegroundColor Yellow
        Write-Host '  .\backup_system.ps1 -Action manual -Description "temizlik_oncesi"'
        Write-Host '  .\backup_system.ps1 -Action list'
        Write-Host ""
    }
} 