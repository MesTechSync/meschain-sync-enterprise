@echo off
REM MesChain-Sync Windows Yedekleme Scripti
REM Proje dosyalarını ZIP arşivine yedekler

SET backup_dir=backups
SET logs_dir=logs
SET timestamp=%date:~-4,4%-%date:~-10,2%-%date:~-7,2%_%time:~0,2%-%time:~3,2%-%time:~6,2%
SET timestamp=%timestamp: =0%
SET backup_name=backup_%timestamp%

echo ======================================
echo MesChain-Sync Yedekleme Sistemi
echo ======================================
echo.

REM Dizinleri oluştur
if not exist "%backup_dir%" (
    mkdir "%backup_dir%"
    echo Backup dizini olusturuldu: %backup_dir%
)

if not exist "%logs_dir%" (
    mkdir "%logs_dir%"
    echo Logs dizini olusturuldu: %logs_dir%
)

REM Parametre kontrolü
if "%1"=="" (
    echo Kullanim: backup_windows.bat [manual^|list^|clean]
    echo.
    echo Komutlar:
    echo   manual - Manuel yedekleme yap
    echo   list   - Mevcut yedekleri listele
    echo   clean  - Eski yedekleri temizle
    goto :end
)

if "%1"=="manual" goto :manual
if "%1"=="list" goto :list
if "%1"=="clean" goto :clean
goto :end

:manual
echo Manuel yedekleme baslatiliyor...
echo Tarih: %date% %time%
echo.

REM Temizlik öncesi dosyaları listele
echo Yedeklenecek dosyalar kontrol ediliyor...
echo.

REM ZIP dosyası oluştur (PowerShell kullanarak)
SET zip_file=%backup_dir%\%backup_name%.zip

echo Yedekleme olusturuluyor: %zip_file%
echo Bu islem biraz zaman alabilir...

powershell -Command "& {Add-Type -A 'System.IO.Compression.FileSystem'; [IO.Compression.ZipFile]::CreateFromDirectory('.', '%zip_file%', 'Optimal', $false, [Text.Encoding]::UTF8); }"

if %errorlevel%==0 (
    echo.
    echo [OK] Yedekleme basariyla tamamlandi!
    echo Dosya: %zip_file%
    
    REM Log kaydı
    echo [%date% %time%] Manual backup created: %backup_name% >> %logs_dir%\backup.log
) else (
    echo.
    echo [HATA] Yedekleme olusturulamadi!
    echo [%date% %time%] Backup failed: %backup_name% >> %logs_dir%\backup.log
)
goto :end

:list
echo Mevcut Yedekler:
echo ======================================
dir /b /o-d "%backup_dir%\*.zip" 2>nul
if %errorlevel% neq 0 (
    echo Yedek bulunamadi.
)
goto :end

:clean
echo Eski yedekler temizleniyor...
REM En son 5 yedek haricindeki tüm yedekleri sil
for /f "skip=5 delims=" %%i in ('dir /b /o-d "%backup_dir%\*.zip" 2^>nul') do (
    del "%backup_dir%\%%i"
    echo Silindi: %%i
)
echo Temizlik tamamlandi.
goto :end

:end
echo. 