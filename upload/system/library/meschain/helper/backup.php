<?php
/**
 * MeschainBackupHelper - Enterprise Backup & Recovery Sistemi
 * 
 * Otomatik backup, recovery, incremental backup, cloud storage entegrasyonu
 * ve disaster recovery özellikleri
 * 
 * @author MesChain Development Team
 * @version 1.0.0
 * @since 2024-01-21
 */

class MeschainBackupHelper {
    
    private $registry;
    private $db;
    private $log;
    private $configHelper;
    private $eventHelper;
    private $monitoringHelper;
    
    // Backup türleri
    const TYPE_FULL = 'full';
    const TYPE_INCREMENTAL = 'incremental';
    const TYPE_DIFFERENTIAL = 'differential';
    const TYPE_CONFIG = 'config';
    const TYPE_FILES = 'files';
    const TYPE_DATABASE = 'database';
    
    // Backup durumları
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    
    // Storage providers
    const STORAGE_LOCAL = 'local';
    const STORAGE_FTP = 'ftp';
    const STORAGE_S3 = 's3';
    const STORAGE_DROPBOX = 'dropbox';
    const STORAGE_GDRIVE = 'google_drive';
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->log = new Log('meschain_backup.log');
        
        // Helper'ları yükle
        require_once(DIR_SYSTEM . 'library/meschain/helper/config.php');
        require_once(DIR_SYSTEM . 'library/meschain/helper/event.php');
        require_once(DIR_SYSTEM . 'library/meschain/helper/monitoring.php');
        
        $this->configHelper = new MeschainConfigHelper($registry);
        $this->eventHelper = new MeschainEventHelper($registry);
        $this->monitoringHelper = new MeschainMonitoringHelper($registry);
        
        $this->createBackupTables();
        $this->loadDefaultConfigs();
    }
    
    /**
     * Backup tablolarını oluştur
     */
    private function createBackupTables() {
        // Backup jobs tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_backups` (
            `backup_id` int(11) NOT NULL AUTO_INCREMENT,
            `backup_name` varchar(255) NOT NULL,
            `backup_type` enum('full','incremental','differential','config','files','database') NOT NULL,
            `status` enum('pending','running','completed','failed','cancelled') DEFAULT 'pending',
            `started_at` datetime DEFAULT NULL,
            `completed_at` datetime DEFAULT NULL,
            `file_path` varchar(500),
            `file_size` bigint(20) DEFAULT NULL,
            `compressed_size` bigint(20) DEFAULT NULL,
            `compression_ratio` decimal(5,2) DEFAULT NULL,
            `checksum_md5` varchar(32),
            `checksum_sha256` varchar(64),
            `storage_provider` enum('local','ftp','s3','dropbox','google_drive') DEFAULT 'local',
            `storage_path` varchar(500),
            `upload_status` enum('pending','uploading','uploaded','failed') DEFAULT 'pending',
            `retention_days` int(11) DEFAULT 30,
            `expires_at` datetime,
            `database_tables` json,
            `included_files` json,
            `excluded_patterns` json,
            `progress` decimal(5,2) DEFAULT 0,
            `error_message` text,
            `metadata` json,
            `tenant_id` int(11) DEFAULT NULL,
            `created_by` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`backup_id`),
            KEY `backup_type` (`backup_type`),
            KEY `status` (`status`),
            KEY `created_at` (`created_at`),
            KEY `expires_at` (`expires_at`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Backup schedules tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_backup_schedules` (
            `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
            `schedule_name` varchar(255) NOT NULL,
            `backup_type` enum('full','incremental','differential','config','files','database') NOT NULL,
            `frequency` enum('hourly','daily','weekly','monthly','custom') NOT NULL,
            `cron_expression` varchar(100),
            `enabled` tinyint(1) DEFAULT 1,
            `last_run` datetime DEFAULT NULL,
            `next_run` datetime NOT NULL,
            `retention_days` int(11) DEFAULT 30,
            `max_backups` int(11) DEFAULT 10,
            `storage_provider` enum('local','ftp','s3','dropbox','google_drive') DEFAULT 'local',
            `compression_enabled` tinyint(1) DEFAULT 1,
            `encryption_enabled` tinyint(1) DEFAULT 0,
            `include_patterns` json,
            `exclude_patterns` json,
            `notification_enabled` tinyint(1) DEFAULT 1,
            `notification_email` varchar(255),
            `tenant_id` int(11) DEFAULT NULL,
            `created_by` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`schedule_id`),
            KEY `backup_type` (`backup_type`),
            KEY `enabled` (`enabled`),
            KEY `next_run` (`next_run`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Recovery jobs tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_recoveries` (
            `recovery_id` int(11) NOT NULL AUTO_INCREMENT,
            `backup_id` int(11) NOT NULL,
            `recovery_type` enum('full','selective','database_only','files_only') NOT NULL,
            `target_location` varchar(500),
            `status` enum('pending','running','completed','failed','cancelled') DEFAULT 'pending',
            `started_at` datetime DEFAULT NULL,
            `completed_at` datetime DEFAULT NULL,
            `progress` decimal(5,2) DEFAULT 0,
            `recovered_items` json,
            `error_message` text,
            `verification_status` enum('pending','verified','failed') DEFAULT 'pending',
            `verification_errors` json,
            `tenant_id` int(11) DEFAULT NULL,
            `initiated_by` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`recovery_id`),
            KEY `backup_id` (`backup_id`),
            KEY `status` (`status`),
            KEY `created_at` (`created_at`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Storage providers configuration tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_storage_providers` (
            `provider_id` int(11) NOT NULL AUTO_INCREMENT,
            `provider_name` varchar(100) NOT NULL,
            `provider_type` enum('local','ftp','s3','dropbox','google_drive') NOT NULL,
            `connection_config` json NOT NULL,
            `is_active` tinyint(1) DEFAULT 1,
            `is_default` tinyint(1) DEFAULT 0,
            `test_status` enum('untested','success','failed') DEFAULT 'untested',
            `last_tested` datetime DEFAULT NULL,
            `usage_stats` json,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`provider_id`),
            KEY `provider_type` (`provider_type`),
            KEY `is_active` (`is_active`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        $this->log->write('Backup tabloları oluşturuldu/kontrol edildi');
    }
    
    /**
     * Varsayılan konfigürasyonları yükle
     */
    private function loadDefaultConfigs() {
        $defaults = [
            'backup.enabled' => true,
            'backup.base_path' => DIR_STORAGE . 'backups/',
            'backup.compression_enabled' => true,
            'backup.encryption_enabled' => false,
            'backup.default_retention_days' => 30,
            'backup.max_backups_per_type' => 10,
            'backup.chunk_size' => 1048576, // 1MB
            'backup.timeout' => 3600, // 1 hour
            'backup.notification_enabled' => true,
            'backup.auto_cleanup_enabled' => true,
            'backup.verify_after_backup' => true,
            'backup.parallel_processing' => false,
            'backup.max_parallel_jobs' => 2
        ];
        
        foreach ($defaults as $key => $value) {
            $existing = $this->configHelper->get($key);
            if ($existing === null) {
                $this->configHelper->set($key, $value, [
                    'type' => 'system',
                    'description' => 'Backup system configuration'
                ]);
            }
        }
        
        // Backup dizinini oluştur
        $backupPath = $this->configHelper->get('backup.base_path');
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }
        
        // Default storage provider ekle
        $this->createDefaultStorageProvider();
    }
    
    /**
     * Varsayılan storage provider oluştur
     */
    private function createDefaultStorageProvider() {
        $existing = $this->db->query("SELECT provider_id FROM `" . DB_PREFIX . "meschain_storage_providers` 
            WHERE provider_type = 'local' AND is_default = 1");
        
        if (!$existing->num_rows) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_storage_providers` SET
                provider_name = 'Local Storage',
                provider_type = 'local',
                connection_config = '" . $this->db->escape(json_encode([
                    'base_path' => $this->configHelper->get('backup.base_path')
                ])) . "',
                is_active = 1,
                is_default = 1,
                test_status = 'success',
                created_at = NOW(),
                updated_at = NOW()
            ");
        }
    }
    
    /**
     * Full system backup oluştur
     */
    public function createFullBackup($options = []) {
        $this->log->write("Full backup başlatılıyor");
        
        $backupName = $options['name'] ?? 'full_backup_' . date('Y-m-d_H-i-s');
        $tenantId = $options['tenant_id'] ?? null;
        
        // Backup kaydı oluştur
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_backups` SET
            backup_name = '" . $this->db->escape($backupName) . "',
            backup_type = '" . self::TYPE_FULL . "',
            status = '" . self::STATUS_PENDING . "',
            retention_days = " . (int)($options['retention_days'] ?? $this->configHelper->get('backup.default_retention_days', 30)) . ",
            expires_at = DATE_ADD(NOW(), INTERVAL " . (int)($options['retention_days'] ?? 30) . " DAY),
            storage_provider = '" . ($options['storage_provider'] ?? self::STORAGE_LOCAL) . "',
            tenant_id = " . ($tenantId ? (int)$tenantId : "NULL") . ",
            created_by = " . (int)$this->getCurrentUserId() . ",
            created_at = NOW(),
            updated_at = NOW()
        ");
        
        $backupId = $this->db->getLastId();
        
        try {
            // Backup işlemini başlat
            $this->updateBackupStatus($backupId, self::STATUS_RUNNING);
            
            $result = $this->performFullBackup($backupId, $options);
            
            $this->updateBackupStatus($backupId, self::STATUS_COMPLETED);
            
            // Event tetikle
            $this->eventHelper->trigger('backup.completed', [
                'backup_id' => $backupId,
                'backup_type' => self::TYPE_FULL,
                'file_size' => $result['file_size'],
                'duration' => $result['duration']
            ]);
            
            $this->log->write("Full backup tamamlandı: ID {$backupId}");
            
            return [
                'success' => true,
                'backup_id' => $backupId,
                'file_path' => $result['file_path'],
                'file_size' => $result['file_size'],
                'duration' => $result['duration']
            ];
            
        } catch (Exception $e) {
            $this->updateBackupStatus($backupId, self::STATUS_FAILED, $e->getMessage());
            
            // Event tetikle
            $this->eventHelper->trigger('backup.failed', [
                'backup_id' => $backupId,
                'backup_type' => self::TYPE_FULL,
                'error' => $e->getMessage()
            ], ['type' => 'async', 'priority' => 4]);
            
            $this->log->write("Full backup başarısız: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Full backup işlemini gerçekleştir
     */
    private function performFullBackup($backupId, $options) {
        $startTime = microtime(true);
        $backupPath = $this->configHelper->get('backup.base_path');
        $timestamp = date('Y-m-d_H-i-s');
        $fileName = "full_backup_{$timestamp}.tar.gz";
        $filePath = $backupPath . $fileName;
        
        // Progress tracking
        $totalSteps = 4; // Database, Files, Compress, Upload
        $currentStep = 0;
        
        // 1. Database backup
        $this->updateProgress($backupId, ($currentStep++ / $totalSteps) * 100);
        $dbBackupFile = $this->createDatabaseBackup($backupId);
        
        // 2. Files backup
        $this->updateProgress($backupId, ($currentStep++ / $totalSteps) * 100);
        $filesBackupDir = $this->createFilesBackup($backupId, $options);
        
        // 3. Create compressed archive
        $this->updateProgress($backupId, ($currentStep++ / $totalSteps) * 100);
        $this->createCompressedArchive($filePath, [$dbBackupFile, $filesBackupDir]);
        
        // 4. Upload to storage (if not local)
        $this->updateProgress($backupId, ($currentStep++ / $totalSteps) * 100);
        $storageProvider = $options['storage_provider'] ?? self::STORAGE_LOCAL;
        if ($storageProvider !== self::STORAGE_LOCAL) {
            $this->uploadToStorage($backupId, $filePath, $storageProvider);
        }
        
        // Verify backup
        if ($this->configHelper->get('backup.verify_after_backup', true)) {
            $this->verifyBackup($filePath);
        }
        
        // Calculate metrics
        $fileSize = filesize($filePath);
        $duration = microtime(true) - $startTime;
        
        // Update backup record
        $checksum = hash_file('md5', $filePath);
        $checksumSha256 = hash_file('sha256', $filePath);
        
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_backups` SET
            file_path = '" . $this->db->escape($filePath) . "',
            file_size = " . (int)$fileSize . ",
            compressed_size = " . (int)$fileSize . ",
            checksum_md5 = '" . $checksum . "',
            checksum_sha256 = '" . $checksumSha256 . "',
            completed_at = NOW(),
            updated_at = NOW()
            WHERE backup_id = " . (int)$backupId);
        
        // Cleanup temporary files
        if (file_exists($dbBackupFile)) {
            unlink($dbBackupFile);
        }
        if (is_dir($filesBackupDir)) {
            $this->removeDirectory($filesBackupDir);
        }
        
        return [
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'duration' => $duration,
            'checksum' => $checksum
        ];
    }
    
    /**
     * Database backup oluştur
     */
    private function createDatabaseBackup($backupId) {
        $this->log->write("Database backup oluşturuluyor");
        
        $backupPath = $this->configHelper->get('backup.base_path');
        $fileName = "database_backup_" . date('Y-m-d_H-i-s') . ".sql";
        $filePath = $backupPath . $fileName;
        
        // Tüm tabloları al
        $tables = [];
        $query = $this->db->query("SHOW TABLES");
        foreach ($query->rows as $row) {
            $tables[] = array_values($row)[0];
        }
        
        $handle = fopen($filePath, 'w');
        
        if (!$handle) {
            throw new Exception("Database backup dosyası oluşturulamadı: {$filePath}");
        }
        
        // SQL header
        fwrite($handle, "-- MesChain-Sync Database Backup\n");
        fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Backup ID: {$backupId}\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");
        
        $totalTables = count($tables);
        $processedTables = 0;
        
        foreach ($tables as $table) {
            // Table structure
            $createQuery = $this->db->query("SHOW CREATE TABLE `{$table}`");
            if ($createQuery->num_rows) {
                fwrite($handle, "-- Table structure for `{$table}`\n");
                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
                fwrite($handle, $createQuery->row['Create Table'] . ";\n\n");
            }
            
            // Table data
            $dataQuery = $this->db->query("SELECT * FROM `{$table}`");
            if ($dataQuery->num_rows) {
                fwrite($handle, "-- Data for table `{$table}`\n");
                fwrite($handle, "INSERT INTO `{$table}` VALUES ");
                
                $first = true;
                foreach ($dataQuery->rows as $row) {
                    if (!$first) {
                        fwrite($handle, ",\n");
                    }
                    
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . addslashes($value) . "'";
                        }
                    }
                    
                    fwrite($handle, "(" . implode(', ', $values) . ")");
                    $first = false;
                }
                
                fwrite($handle, ";\n\n");
            }
            
            $processedTables++;
            // Update progress periodically
            if ($processedTables % 5 === 0) {
                $progress = ($processedTables / $totalTables) * 25; // 25% of total backup
                $this->updateProgress($backupId, $progress);
            }
        }
        
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
        
        // Update backup metadata
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_backups` SET
            database_tables = '" . $this->db->escape(json_encode($tables)) . "',
            updated_at = NOW()
            WHERE backup_id = " . (int)$backupId);
        
        $this->log->write("Database backup tamamlandı: {$totalTables} tablo");
        
        return $filePath;
    }
    
    /**
     * Files backup oluştur
     */
    private function createFilesBackup($backupId, $options) {
        $this->log->write("Files backup oluşturuluyor");
        
        $backupPath = $this->configHelper->get('backup.base_path');
        $filesBackupDir = $backupPath . 'files_backup_' . date('Y-m-d_H-i-s') . '/';
        
        if (!mkdir($filesBackupDir, 0755, true)) {
            throw new Exception("Files backup dizini oluşturulamadı: {$filesBackupDir}");
        }
        
        // Include patterns (defaults)
        $includePatterns = $options['include_patterns'] ?? [
            DIR_APPLICATION,
            DIR_CATALOG,
            DIR_SYSTEM,
            DIR_IMAGE,
            DIR_STORAGE . 'upload/',
            'config.php',
            '.htaccess'
        ];
        
        // Exclude patterns
        $excludePatterns = $options['exclude_patterns'] ?? [
            '*/cache/*',
            '*/logs/*',
            '*/tmp/*',
            '*/backups/*',
            '*.log',
            '*.tmp'
        ];
        
        $copiedFiles = [];
        
        foreach ($includePatterns as $pattern) {
            if (is_file($pattern)) {
                // Single file
                $relativePath = str_replace(DIR_ROOT, '', $pattern);
                $destPath = $filesBackupDir . ltrim($relativePath, '/');
                $destDir = dirname($destPath);
                
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                
                if (copy($pattern, $destPath)) {
                    $copiedFiles[] = $relativePath;
                }
                
            } elseif (is_dir($pattern)) {
                // Directory
                $this->copyDirectoryRecursive($pattern, $filesBackupDir, $excludePatterns, $copiedFiles);
            }
        }
        
        // Update backup metadata
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_backups` SET
            included_files = '" . $this->db->escape(json_encode($copiedFiles)) . "',
            excluded_patterns = '" . $this->db->escape(json_encode($excludePatterns)) . "',
            updated_at = NOW()
            WHERE backup_id = " . (int)$backupId);
        
        $this->log->write("Files backup tamamlandı: " . count($copiedFiles) . " dosya");
        
        return $filesBackupDir;
    }
    
    /**
     * Directory recursive copy
     */
    private function copyDirectoryRecursive($source, $backupDir, $excludePatterns, &$copiedFiles) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $relativePath = str_replace(DIR_ROOT, '', $item->getPathname());
            
            // Check exclude patterns
            if ($this->matchesExcludePattern($relativePath, $excludePatterns)) {
                continue;
            }
            
            $destPath = $backupDir . ltrim($relativePath, '/');
            
            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } elseif ($item->isFile()) {
                $destDir = dirname($destPath);
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                
                if (copy($item->getPathname(), $destPath)) {
                    $copiedFiles[] = $relativePath;
                }
            }
        }
    }
    
    /**
     * Exclude pattern kontrolü
     */
    private function matchesExcludePattern($path, $patterns) {
        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Compressed archive oluştur
     */
    private function createCompressedArchive($archivePath, $sources) {
        $this->log->write("Compressed archive oluşturuluyor: {$archivePath}");
        
        if (!$this->configHelper->get('backup.compression_enabled', true)) {
            // No compression, just tar
            $archivePath = str_replace('.tar.gz', '.tar', $archivePath);
        }
        
        $phar = new PharData($archivePath);
        
        foreach ($sources as $source) {
            if (is_file($source)) {
                $phar->addFile($source, basename($source));
            } elseif (is_dir($source)) {
                $phar->buildFromDirectory($source);
            }
        }
        
        // Compress if enabled
        if ($this->configHelper->get('backup.compression_enabled', true)) {
            $phar->compress(Phar::GZ);
            unlink($archivePath); // Remove uncompressed version
        }
        
        $this->log->write("Archive oluşturuldu: " . filesize($archivePath) . " bytes");
    }
    
    /**
     * Storage'a yükle
     */
    private function uploadToStorage($backupId, $filePath, $storageProvider) {
        $this->log->write("Storage'a yükleniyor: {$storageProvider}");
        
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_backups` SET
            upload_status = 'uploading',
            updated_at = NOW()
            WHERE backup_id = " . (int)$backupId);
        
        try {
            switch ($storageProvider) {
                case self::STORAGE_FTP:
                    $this->uploadToFtp($backupId, $filePath);
                    break;
                case self::STORAGE_S3:
                    $this->uploadToS3($backupId, $filePath);
                    break;
                default:
                    throw new Exception("Desteklenmeyen storage provider: {$storageProvider}");
            }
            
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_backups` SET
                upload_status = 'uploaded',
                updated_at = NOW()
                WHERE backup_id = " . (int)$backupId);
            
        } catch (Exception $e) {
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_backups` SET
                upload_status = 'failed',
                error_message = '" . $this->db->escape($e->getMessage()) . "',
                updated_at = NOW()
                WHERE backup_id = " . (int)$backupId);
            
            throw $e;
        }
    }
    
    /**
     * Backup'ı restore et
     */
    public function restoreBackup($backupId, $options = []) {
        $this->log->write("Backup restore başlatılıyor: ID {$backupId}");
        
        $backup = $this->getBackup($backupId);
        if (!$backup) {
            throw new Exception("Backup bulunamadı: {$backupId}");
        }
        
        if ($backup['status'] !== self::STATUS_COMPLETED) {
            throw new Exception("Backup tamamlanmamış veya başarısız: {$backup['status']}");
        }
        
        // Recovery kaydı oluştur
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_recoveries` SET
            backup_id = " . (int)$backupId . ",
            recovery_type = '" . ($options['type'] ?? 'full') . "',
            target_location = '" . $this->db->escape($options['target'] ?? DIR_ROOT) . "',
            status = '" . self::STATUS_RUNNING . "',
            started_at = NOW(),
            tenant_id = " . ($backup['tenant_id'] ? (int)$backup['tenant_id'] : "NULL") . ",
            initiated_by = " . (int)$this->getCurrentUserId() . ",
            created_at = NOW(),
            updated_at = NOW()
        ");
        
        $recoveryId = $this->db->getLastId();
        
        try {
            $result = $this->performRestore($recoveryId, $backup, $options);
            
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_recoveries` SET
                status = '" . self::STATUS_COMPLETED . "',
                completed_at = NOW(),
                progress = 100,
                recovered_items = '" . $this->db->escape(json_encode($result['recovered_items'])) . "',
                updated_at = NOW()
                WHERE recovery_id = " . (int)$recoveryId);
            
            // Event tetikle
            $this->eventHelper->trigger('backup.restored', [
                'recovery_id' => $recoveryId,
                'backup_id' => $backupId,
                'recovery_type' => $options['type'] ?? 'full'
            ]);
            
            $this->log->write("Backup restore tamamlandı: Recovery ID {$recoveryId}");
            
            return [
                'success' => true,
                'recovery_id' => $recoveryId,
                'recovered_items' => $result['recovered_items']
            ];
            
        } catch (Exception $e) {
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_recoveries` SET
                status = '" . self::STATUS_FAILED . "',
                completed_at = NOW(),
                error_message = '" . $this->db->escape($e->getMessage()) . "',
                updated_at = NOW()
                WHERE recovery_id = " . (int)$recoveryId);
            
            $this->log->write("Backup restore başarısız: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Backup verify et
     */
    private function verifyBackup($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception("Backup dosyası bulunamadı: {$filePath}");
        }
        
        // File integrity check
        try {
            $phar = new PharData($filePath);
            $phar->rewind();
            return true;
        } catch (Exception $e) {
            throw new Exception("Backup dosyası bozuk: " . $e->getMessage());
        }
    }
    
    /**
     * Progress güncelle
     */
    private function updateProgress($backupId, $progress) {
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_backups` SET
            progress = " . (float)$progress . ",
            updated_at = NOW()
            WHERE backup_id = " . (int)$backupId);
    }
    
    /**
     * Backup status güncelle
     */
    private function updateBackupStatus($backupId, $status, $errorMessage = null) {
        $sql = "UPDATE `" . DB_PREFIX . "meschain_backups` SET
            status = '" . $this->db->escape($status) . "',
            updated_at = NOW()";
        
        if ($status === self::STATUS_RUNNING) {
            $sql .= ", started_at = NOW()";
        } elseif (in_array($status, [self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_CANCELLED])) {
            $sql .= ", completed_at = NOW()";
        }
        
        if ($errorMessage) {
            $sql .= ", error_message = '" . $this->db->escape($errorMessage) . "'";
        }
        
        $sql .= " WHERE backup_id = " . (int)$backupId;
        
        $this->db->query($sql);
    }
    
    /**
     * Otomatik cleanup
     */
    public function performAutoCleanup() {
        if (!$this->configHelper->get('backup.auto_cleanup_enabled', true)) {
            return;
        }
        
        $this->log->write("Otomatik backup cleanup başlatılıyor");
        
        // Süresi dolmuş backup'ları sil
        $expiredQuery = $this->db->query("SELECT backup_id, file_path FROM `" . DB_PREFIX . "meschain_backups` 
            WHERE expires_at <= NOW() AND status = 'completed'");
        
        $deletedCount = 0;
        
        foreach ($expiredQuery->rows as $backup) {
            try {
                if (file_exists($backup['file_path'])) {
                    unlink($backup['file_path']);
                }
                
                $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_backups` 
                    WHERE backup_id = " . (int)$backup['backup_id']);
                
                $deletedCount++;
                
            } catch (Exception $e) {
                $this->log->write("Backup silme hatası: " . $e->getMessage());
            }
        }
        
        $this->log->write("Otomatik cleanup tamamlandı: {$deletedCount} backup silindi");
        
        return $deletedCount;
    }
    
    /**
     * Directory silme utility
     */
    private function removeDirectory($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->removeDirectory($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
    
    /**
     * Backup bilgilerini al
     */
    public function getBackup($backupId) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_backups` 
            WHERE backup_id = " . (int)$backupId);
        
        if ($query->num_rows) {
            $backup = $query->row;
            $backup['database_tables'] = json_decode($backup['database_tables'], true);
            $backup['included_files'] = json_decode($backup['included_files'], true);
            $backup['excluded_patterns'] = json_decode($backup['excluded_patterns'], true);
            $backup['metadata'] = json_decode($backup['metadata'], true);
            return $backup;
        }
        
        return null;
    }
    
    /**
     * Tüm backup'ları listele
     */
    public function getAllBackups($filters = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_backups` WHERE 1=1";
        
        if (isset($filters['backup_type'])) {
            $sql .= " AND backup_type = '" . $this->db->escape($filters['backup_type']) . "'";
        }
        
        if (isset($filters['status'])) {
            $sql .= " AND status = '" . $this->db->escape($filters['status']) . "'";
        }
        
        if (isset($filters['tenant_id'])) {
            $sql .= " AND tenant_id = " . (int)$filters['tenant_id'];
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Mevcut kullanıcı ID'sini al
     */
    private function getCurrentUserId() {
        if ($this->registry->has('user')) {
            $user = $this->registry->get('user');
            return $user->getId();
        }
        return 0;
    }
    
    // Placeholder methods for different storage providers
    private function uploadToFtp($backupId, $filePath) {
        // FTP upload implementation
        throw new Exception("FTP upload henüz implement edilmedi");
    }
    
    private function uploadToS3($backupId, $filePath) {
        // S3 upload implementation
        throw new Exception("S3 upload henüz implement edilmedi");
    }
    
    private function performRestore($recoveryId, $backup, $options) {
        // Restore implementation
        throw new Exception("Restore functionality henüz implement edilmedi");
    }
}
?> 