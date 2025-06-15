<?php
/**
 * MesChain-Sync Otomatik Yedekleme Sistemi
 * 
 * Bu script, proje dosyalarını otomatik olarak yedekler ve
 * dosya kaybına karşı koruma sağlar.
 * 
 * Kullanım: php backup_system.php [manual|auto|restore]
 */

class MesChainBackupSystem {
    private $projectRoot;
    private $backupDir;
    private $logFile;
    private $excludeDirs = ['.git', 'backups', 'logs', 'cache', 'node_modules'];
    private $maxBackups = 10; // Maksimum yedek sayısı
    
    public function __construct() {
        $this->projectRoot = dirname(__FILE__);
        $this->backupDir = $this->projectRoot . '/backups';
        $this->logFile = $this->projectRoot . '/logs/backup_system.log';
        
        // Gerekli dizinleri oluştur
        $this->createDirectories();
    }
    
    /**
     * Gerekli dizinleri oluştur
     */
    private function createDirectories() {
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
            $this->log("Backup dizini oluşturuldu: " . $this->backupDir);
        }
        
        $logsDir = dirname($this->logFile);
        if (!file_exists($logsDir)) {
            mkdir($logsDir, 0755, true);
            $this->log("Logs dizini oluşturuldu: " . $logsDir);
        }
    }
    
    /**
     * Manuel yedekleme yap
     */
    public function manualBackup($description = '') {
        $this->log("Manuel yedekleme başlatılıyor...");
        
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "manual_backup_{$timestamp}";
        
        if ($description) {
            $backupName .= "_" . $this->sanitizeFilename($description);
        }
        
        return $this->createBackup($backupName, 'manual', $description);
    }
    
    /**
     * Otomatik yedekleme yap
     */
    public function autoBackup() {
        $this->log("Otomatik yedekleme başlatılıyor...");
        
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "auto_backup_{$timestamp}";
        
        // Değişiklik kontrolü
        if (!$this->hasChanges()) {
            $this->log("Değişiklik tespit edilmedi, yedekleme atlanıyor.");
            return false;
        }
        
        return $this->createBackup($backupName, 'auto');
    }
    
    /**
     * Yedekleme oluştur
     */
    private function createBackup($backupName, $type = 'manual', $description = '') {
        $backupPath = $this->backupDir . '/' . $backupName . '.zip';
        
        // ZIP arşivi oluştur
        $zip = new ZipArchive();
        
        if ($zip->open($backupPath, ZipArchive::CREATE) !== TRUE) {
            $this->log("HATA: ZIP arşivi oluşturulamadı: " . $backupPath, 'ERROR');
            return false;
        }
        
        // Dosya sayacı
        $fileCount = 0;
        $totalSize = 0;
        
        // Proje dosyalarını ekle
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->projectRoot, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($iterator as $file) {
            // Hariç tutulacak dizinleri kontrol et
            $skip = false;
            foreach ($this->excludeDirs as $excludeDir) {
                if (strpos($file->getPathname(), DIRECTORY_SEPARATOR . $excludeDir . DIRECTORY_SEPARATOR) !== false) {
                    $skip = true;
                    break;
                }
            }
            
            if ($skip || !$file->isFile()) {
                continue;
            }
            
            $relativePath = str_replace($this->projectRoot . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $zip->addFile($file->getPathname(), $relativePath);
            
            $fileCount++;
            $totalSize += $file->getSize();
        }
        
        // Yedekleme meta verilerini ekle
        $metadata = [
            'backup_name' => $backupName,
            'backup_type' => $type,
            'description' => $description,
            'timestamp' => time(),
            'date' => date('Y-m-d H:i:s'),
            'file_count' => $fileCount,
            'total_size' => $totalSize,
            'php_version' => PHP_VERSION,
            'project_root' => $this->projectRoot
        ];
        
        $zip->addFromString('backup_metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));
        
        $zip->close();
        
        // Yedekleme başarılı
        $backupSizeMB = round(filesize($backupPath) / 1024 / 1024, 2);
        $this->log("Yedekleme tamamlandı: {$backupName} ({$fileCount} dosya, {$backupSizeMB} MB)");
        
        // Eski yedekleri temizle
        $this->cleanOldBackups();
        
        return [
            'success' => true,
            'backup_name' => $backupName,
            'backup_path' => $backupPath,
            'file_count' => $fileCount,
            'size_mb' => $backupSizeMB
        ];
    }
    
    /**
     * Yedekleme geri yükle
     */
    public function restoreBackup($backupName) {
        $this->log("Yedekleme geri yükleme başlatılıyor: " . $backupName);
        
        $backupPath = $this->backupDir . '/' . $backupName;
        
        if (!file_exists($backupPath)) {
            $backupPath .= '.zip';
            if (!file_exists($backupPath)) {
                $this->log("HATA: Yedek dosyası bulunamadı: " . $backupName, 'ERROR');
                return false;
            }
        }
        
        // Mevcut durumu yedekle (güvenlik için)
        $this->manualBackup('before_restore_' . date('Y-m-d_H-i-s'));
        
        // ZIP arşivini aç
        $zip = new ZipArchive();
        
        if ($zip->open($backupPath) !== TRUE) {
            $this->log("HATA: ZIP arşivi açılamadı: " . $backupPath, 'ERROR');
            return false;
        }
        
        // Metadata'yı oku
        $metadata = json_decode($zip->getFromName('backup_metadata.json'), true);
        
        // Geri yükleme dizini
        $restoreDir = $this->projectRoot . '/restore_temp';
        
        if (!file_exists($restoreDir)) {
            mkdir($restoreDir, 0755, true);
        }
        
        // Dosyaları çıkar
        $zip->extractTo($restoreDir);
        $zip->close();
        
        // Dosyaları kopyala (metadata hariç)
        $this->copyDirectory($restoreDir, $this->projectRoot, ['backup_metadata.json']);
        
        // Geçici dizini temizle
        $this->deleteDirectory($restoreDir);
        
        $this->log("Yedekleme başarıyla geri yüklendi: " . $backupName);
        
        return [
            'success' => true,
            'backup_name' => $backupName,
            'metadata' => $metadata
        ];
    }
    
    /**
     * Mevcut yedekleri listele
     */
    public function listBackups() {
        $backups = [];
        
        if (!file_exists($this->backupDir)) {
            return $backups;
        }
        
        $files = scandir($this->backupDir);
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'zip') {
                continue;
            }
            
            $filePath = $this->backupDir . '/' . $file;
            
            // Metadata'yı oku
            $zip = new ZipArchive();
            $metadata = null;
            
            if ($zip->open($filePath) === TRUE) {
                $metadataJson = $zip->getFromName('backup_metadata.json');
                if ($metadataJson) {
                    $metadata = json_decode($metadataJson, true);
                }
                $zip->close();
            }
            
            $backups[] = [
                'filename' => $file,
                'size_mb' => round(filesize($filePath) / 1024 / 1024, 2),
                'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
                'metadata' => $metadata
            ];
        }
        
        // Tarihe göre sırala (yeniden eskiye)
        usort($backups, function($a, $b) {
            return filemtime($this->backupDir . '/' . $b['filename']) - 
                   filemtime($this->backupDir . '/' . $a['filename']);
        });
        
        return $backups;
    }
    
    /**
     * Eski yedekleri temizle
     */
    private function cleanOldBackups() {
        $backups = $this->listBackups();
        
        // Otomatik yedekleri say
        $autoBackups = array_filter($backups, function($backup) {
            return isset($backup['metadata']['backup_type']) && 
                   $backup['metadata']['backup_type'] === 'auto';
        });
        
        // Maksimum sayıyı aşan otomatik yedekleri sil
        if (count($autoBackups) > $this->maxBackups) {
            $toDelete = array_slice($autoBackups, $this->maxBackups);
            
            foreach ($toDelete as $backup) {
                $filePath = $this->backupDir . '/' . $backup['filename'];
                if (unlink($filePath)) {
                    $this->log("Eski yedek silindi: " . $backup['filename']);
                }
            }
        }
    }
    
    /**
     * Değişiklik kontrolü
     */
    private function hasChanges() {
        $lastBackupFile = $this->projectRoot . '/.last_backup_hash';
        $currentHash = $this->calculateProjectHash();
        
        if (!file_exists($lastBackupFile)) {
            file_put_contents($lastBackupFile, $currentHash);
            return true;
        }
        
        $lastHash = file_get_contents($lastBackupFile);
        
        if ($currentHash !== $lastHash) {
            file_put_contents($lastBackupFile, $currentHash);
            return true;
        }
        
        return false;
    }
    
    /**
     * Proje hash'i hesapla
     */
    private function calculateProjectHash() {
        $hashes = [];
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->projectRoot, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($iterator as $file) {
            // Hariç tutulacak dizinleri kontrol et
            $skip = false;
            foreach ($this->excludeDirs as $excludeDir) {
                if (strpos($file->getPathname(), DIRECTORY_SEPARATOR . $excludeDir . DIRECTORY_SEPARATOR) !== false) {
                    $skip = true;
                    break;
                }
            }
            
            if ($skip || !$file->isFile()) {
                continue;
            }
            
            $relativePath = str_replace($this->projectRoot . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $hashes[$relativePath] = md5_file($file->getPathname());
        }
        
        return md5(json_encode($hashes));
    }
    
    /**
     * Dosya adını temizle
     */
    private function sanitizeFilename($filename) {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
    }
    
    /**
     * Dizin kopyala
     */
    private function copyDirectory($src, $dst, $exclude = []) {
        $dir = opendir($src);
        
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..' || in_array($file, $exclude)) {
                continue;
            }
            
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            
            if (is_dir($srcPath)) {
                if (!file_exists($dstPath)) {
                    mkdir($dstPath, 0755, true);
                }
                $this->copyDirectory($srcPath, $dstPath, $exclude);
            } else {
                copy($srcPath, $dstPath);
            }
        }
        
        closedir($dir);
    }
    
    /**
     * Dizin sil
     */
    private function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }
    
    /**
     * Log kaydet
     */
    private function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        
        // Konsola da yazdır
        echo $logMessage;
    }
}

// CLI kullanımı
if (php_sapi_name() === 'cli') {
    $backup = new MesChainBackupSystem();
    
    $command = $argv[1] ?? 'help';
    
    switch ($command) {
        case 'manual':
            $description = $argv[2] ?? '';
            $result = $backup->manualBackup($description);
            if ($result) {
                echo "\nYedekleme başarılı!\n";
                print_r($result);
            }
            break;
            
        case 'auto':
            $result = $backup->autoBackup();
            if ($result) {
                echo "\nOtomatik yedekleme başarılı!\n";
                print_r($result);
            }
            break;
            
        case 'list':
            $backups = $backup->listBackups();
            echo "\nMevcut Yedekler:\n";
            echo str_repeat("-", 80) . "\n";
            
            foreach ($backups as $b) {
                echo sprintf(
                    "%-40s %8s MB  %s  %s\n",
                    $b['filename'],
                    $b['size_mb'],
                    $b['modified'],
                    $b['metadata']['description'] ?? ''
                );
            }
            break;
            
        case 'restore':
            $backupName = $argv[2] ?? '';
            if (!$backupName) {
                echo "Hata: Yedek dosya adı belirtilmedi!\n";
                echo "Kullanım: php backup_system.php restore <backup_filename>\n";
                break;
            }
            
            $result = $backup->restoreBackup($backupName);
            if ($result) {
                echo "\nGeri yükleme başarılı!\n";
                print_r($result);
            }
            break;
            
        default:
            echo "\nMesChain-Sync Yedekleme Sistemi\n";
            echo str_repeat("=", 40) . "\n";
            echo "Kullanım:\n";
            echo "  php backup_system.php manual [açıklama]  - Manuel yedekleme\n";
            echo "  php backup_system.php auto               - Otomatik yedekleme\n";
            echo "  php backup_system.php list               - Yedekleri listele\n";
            echo "  php backup_system.php restore <dosya>    - Yedek geri yükle\n";
            echo "\n";
    }
} 