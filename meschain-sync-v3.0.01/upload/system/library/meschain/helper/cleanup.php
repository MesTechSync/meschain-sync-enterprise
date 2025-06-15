<?php
/**
 * MeschainCleanupHelper - Sistem Temizliği ve Bakımı
 * 
 * Eski veriler, loglar, geçici dosyalar ve cache temizliği
 * 
 * @author MesChain Development Team
 * @version 1.0.0
 * @since 2024-01-21
 */

class MeschainCleanupHelper {
    
    private $registry;
    private $db;
    private $log;
    private $config;
    
    // Temizlik türleri
    const TYPE_LOGS = 'logs';
    const TYPE_CACHE = 'cache';
    const TYPE_TEMP_FILES = 'temp_files';
    const TYPE_DATABASE = 'database';
    const TYPE_SESSIONS = 'sessions';
    const TYPE_UPLOADS = 'uploads';
    const TYPE_EXPORTS = 'exports';
    
    // Varsayılan retention sürelerine (gün)
    const DEFAULT_RETENTION = [
        'logs' => 30,
        'cache' => 7,
        'temp_files' => 1,
        'sessions' => 30,
        'task_executions' => 90,
        'health_checks' => 60,
        'performance_logs' => 30,
        'user_activities' => 180,
        'exports' => 7,
        'uploads_temp' => 1
    ];
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->log = new Log('meschain_cleanup.log');
        $this->config = $registry->get('config');
        
        $this->createCleanupTables();
    }
    
    /**
     * Cleanup tablolarını oluştur
     */
    private function createCleanupTables() {
        // Cleanup jobs tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_cleanup_jobs` (
            `cleanup_id` int(11) NOT NULL AUTO_INCREMENT,
            `cleanup_type` varchar(50) NOT NULL,
            `target_path` varchar(500),
            `retention_days` int(11) NOT NULL,
            `files_deleted` int(11) DEFAULT 0,
            `records_deleted` int(11) DEFAULT 0,
            `bytes_freed` bigint(20) DEFAULT 0,
            `started_at` datetime NOT NULL,
            `completed_at` datetime DEFAULT NULL,
            `status` enum('running','completed','failed') DEFAULT 'running',
            `error_message` text,
            `tenant_id` int(11) DEFAULT NULL,
            PRIMARY KEY (`cleanup_id`),
            KEY `cleanup_type` (`cleanup_type`),
            KEY `started_at` (`started_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        $this->log->write('Cleanup tabloları oluşturuldu/kontrol edildi');
    }
    
    /**
     * Tüm temizlik işlemlerini çalıştır
     */
    public function cleanupOldData($retentionDays = null) {
        $this->log->write("Genel cleanup işlemi başlatıldı");
        
        $results = [];
        $totalBytesFreed = 0;
        $totalFilesDeleted = 0;
        $totalRecordsDeleted = 0;
        
        try {
            // Log temizliği
            $logResult = $this->cleanupLogs($retentionDays['logs'] ?? self::DEFAULT_RETENTION['logs']);
            $results['logs'] = $logResult;
            $totalBytesFreed += $logResult['bytes_freed'];
            $totalFilesDeleted += $logResult['files_deleted'];
            
            // Cache temizliği
            $cacheResult = $this->cleanupCache($retentionDays['cache'] ?? self::DEFAULT_RETENTION['cache']);
            $results['cache'] = $cacheResult;
            $totalBytesFreed += $cacheResult['bytes_freed'];
            $totalFilesDeleted += $cacheResult['files_deleted'];
            
            // Geçici dosya temizliği
            $tempResult = $this->cleanupTempFiles($retentionDays['temp_files'] ?? self::DEFAULT_RETENTION['temp_files']);
            $results['temp_files'] = $tempResult;
            $totalBytesFreed += $tempResult['bytes_freed'];
            $totalFilesDeleted += $tempResult['files_deleted'];
            
            // Database temizliği
            $dbResult = $this->cleanupDatabase($retentionDays);
            $results['database'] = $dbResult;
            $totalRecordsDeleted += $dbResult['records_deleted'];
            
            // Session temizliği
            $sessionResult = $this->cleanupSessions($retentionDays['sessions'] ?? self::DEFAULT_RETENTION['sessions']);
            $results['sessions'] = $sessionResult;
            $totalRecordsDeleted += $sessionResult['records_deleted'];
            
            // Upload klasörü temizliği
            $uploadResult = $this->cleanupUploads($retentionDays['uploads_temp'] ?? self::DEFAULT_RETENTION['uploads_temp']);
            $results['uploads'] = $uploadResult;
            $totalBytesFreed += $uploadResult['bytes_freed'];
            $totalFilesDeleted += $uploadResult['files_deleted'];
            
            $summary = [
                'success' => true,
                'total_bytes_freed' => $totalBytesFreed,
                'total_files_deleted' => $totalFilesDeleted,
                'total_records_deleted' => $totalRecordsDeleted,
                'human_readable_size' => $this->formatBytes($totalBytesFreed),
                'results' => $results
            ];
            
            $this->log->write("Cleanup tamamlandı. {$totalFilesDeleted} dosya, {$totalRecordsDeleted} kayıt silindi. " . $this->formatBytes($totalBytesFreed) . " alan boşaltıldı.");
            
            return $summary;
            
        } catch (Exception $e) {
            $this->log->write("Cleanup hatası: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Log dosyalarını temizle
     */
    public function cleanupLogs($retentionDays = 30) {
        $cleanupId = $this->startCleanupJob(self::TYPE_LOGS, DIR_LOGS, $retentionDays);
        
        try {
            $this->log->write("Log temizliği başlatıldı (retention: {$retentionDays} gün)");
            
            $filesDeleted = 0;
            $bytesFreed = 0;
            $cutoffDate = date('Y-m-d', strtotime("-{$retentionDays} days"));
            
            // Log dizinindeki dosyaları tara
            $logFiles = glob(DIR_LOGS . '*.log*');
            
            foreach ($logFiles as $file) {
                if (is_file($file)) {
                    $fileDate = date('Y-m-d', filemtime($file));
                    
                    if ($fileDate < $cutoffDate) {
                        $fileSize = filesize($file);
                        
                        if (unlink($file)) {
                            $filesDeleted++;
                            $bytesFreed += $fileSize;
                            $this->log->write("Log dosyası silindi: " . basename($file));
                        }
                    }
                }
            }
            
            // Rotated log dosyaları (*.log.1, *.log.2, vb.)
            $rotatedLogs = glob(DIR_LOGS . '*.log.[0-9]*');
            
            foreach ($rotatedLogs as $file) {
                if (is_file($file)) {
                    $fileDate = date('Y-m-d', filemtime($file));
                    
                    if ($fileDate < $cutoffDate) {
                        $fileSize = filesize($file);
                        
                        if (unlink($file)) {
                            $filesDeleted++;
                            $bytesFreed += $fileSize;
                            $this->log->write("Rotated log dosyası silindi: " . basename($file));
                        }
                    }
                }
            }
            
            $this->completeCleanupJob($cleanupId, $filesDeleted, 0, $bytesFreed);
            
            return [
                'files_deleted' => $filesDeleted,
                'bytes_freed' => $bytesFreed,
                'human_readable_size' => $this->formatBytes($bytesFreed)
            ];
            
        } catch (Exception $e) {
            $this->failCleanupJob($cleanupId, $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cache dosyalarını temizle
     */
    public function cleanupCache($retentionDays = 7) {
        $cleanupId = $this->startCleanupJob(self::TYPE_CACHE, DIR_CACHE, $retentionDays);
        
        try {
            $this->log->write("Cache temizliği başlatıldı (retention: {$retentionDays} gün)");
            
            $filesDeleted = 0;
            $bytesFreed = 0;
            $cutoffTime = time() - ($retentionDays * 24 * 60 * 60);
            
            // Cache dosyalarını temizle
            $this->cleanupDirectory(DIR_CACHE, $cutoffTime, $filesDeleted, $bytesFreed);
            
            // OpenCart cache'ini de temizle
            if ($this->registry->has('cache')) {
                $cache = $this->registry->get('cache');
                if (method_exists($cache, 'delete') && method_exists($cache, 'clear')) {
                    $cache->clear();
                    $this->log->write("OpenCart cache temizlendi");
                }
            }
            
            $this->completeCleanupJob($cleanupId, $filesDeleted, 0, $bytesFreed);
            
            return [
                'files_deleted' => $filesDeleted,
                'bytes_freed' => $bytesFreed,
                'human_readable_size' => $this->formatBytes($bytesFreed)
            ];
            
        } catch (Exception $e) {
            $this->failCleanupJob($cleanupId, $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Geçici dosyaları temizle
     */
    public function cleanupTempFiles($retentionDays = 1) {
        $cleanupId = $this->startCleanupJob(self::TYPE_TEMP_FILES, sys_get_temp_dir(), $retentionDays);
        
        try {
            $this->log->write("Geçici dosya temizliği başlatıldı (retention: {$retentionDays} gün)");
            
            $filesDeleted = 0;
            $bytesFreed = 0;
            $cutoffTime = time() - ($retentionDays * 24 * 60 * 60);
            
            // Sistem temp dizini
            $this->cleanupDirectory(sys_get_temp_dir(), $cutoffTime, $filesDeleted, $bytesFreed, 'meschain_*');
            
            // Upload temp dizini
            if (defined('DIR_UPLOAD') && is_dir(DIR_UPLOAD . 'temp/')) {
                $this->cleanupDirectory(DIR_UPLOAD . 'temp/', $cutoffTime, $filesDeleted, $bytesFreed);
            }
            
            // Image cache dizini
            if (defined('DIR_IMAGE') && is_dir(DIR_IMAGE . 'cache/')) {
                $this->cleanupDirectory(DIR_IMAGE . 'cache/', $cutoffTime, $filesDeleted, $bytesFreed);
            }
            
            $this->completeCleanupJob($cleanupId, $filesDeleted, 0, $bytesFreed);
            
            return [
                'files_deleted' => $filesDeleted,
                'bytes_freed' => $bytesFreed,
                'human_readable_size' => $this->formatBytes($bytesFreed)
            ];
            
        } catch (Exception $e) {
            $this->failCleanupJob($cleanupId, $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Database kayıtlarını temizle
     */
    public function cleanupDatabase($retentionDays = null) {
        $cleanupId = $this->startCleanupJob(self::TYPE_DATABASE, 'database', 0);
        
        try {
            $this->log->write("Database temizliği başlatıldı");
            
            $totalRecordsDeleted = 0;
            $retention = array_merge(self::DEFAULT_RETENTION, $retentionDays ?: []);
            
            // Task executions temizliği
            $taskRetention = $retention['task_executions'];
            $result = $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_task_executions` 
                WHERE started_at < DATE_SUB(NOW(), INTERVAL {$taskRetention} DAY)");
            $deleted = $this->db->countAffected();
            $totalRecordsDeleted += $deleted;
            $this->log->write("Task executions temizlendi: {$deleted} kayıt");
            
            // Health checks temizliği
            $healthRetention = $retention['health_checks'];
            $result = $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_health_checks` 
                WHERE checked_at < DATE_SUB(NOW(), INTERVAL {$healthRetention} DAY)");
            $deleted = $this->db->countAffected();
            $totalRecordsDeleted += $deleted;
            $this->log->write("Health checks temizlendi: {$deleted} kayıt");
            
            // Performance logs temizliği
            $perfRetention = $retention['performance_logs'];
            $result = $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_performance_logs` 
                WHERE timestamp < DATE_SUB(NOW(), INTERVAL {$perfRetention} DAY)");
            $deleted = $this->db->countAffected();
            $totalRecordsDeleted += $deleted;
            $this->log->write("Performance logs temizlendi: {$deleted} kayıt");
            
            // System metrics temizliği (eski metrikler)
            $metricsRetention = $retention['performance_logs'];
            $result = $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_system_metrics` 
                WHERE timestamp < DATE_SUB(NOW(), INTERVAL {$metricsRetention} DAY)");
            $deleted = $this->db->countAffected();
            $totalRecordsDeleted += $deleted;
            $this->log->write("System metrics temizlendi: {$deleted} kayıt");
            
            // User activities temizliği
            $activityRetention = $retention['user_activities'];
            $result = $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_user_activities` 
                WHERE timestamp < DATE_SUB(NOW(), INTERVAL {$activityRetention} DAY)");
            $deleted = $this->db->countAffected();
            $totalRecordsDeleted += $deleted;
            $this->log->write("User activities temizlendi: {$deleted} kayıt");
            
            // Resolved alerts temizliği (çözülmüş alertler)
            $alertRetention = 90; // 3 ay
            $result = $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_alerts` 
                WHERE status = 'resolved' 
                AND resolved_at < DATE_SUB(NOW(), INTERVAL {$alertRetention} DAY)");
            $deleted = $this->db->countAffected();
            $totalRecordsDeleted += $deleted;
            $this->log->write("Resolved alerts temizlendi: {$deleted} kayıt");
            
            // Expired task locks temizliği
            $result = $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_task_locks` 
                WHERE expires_at < NOW()");
            $deleted = $this->db->countAffected();
            $totalRecordsDeleted += $deleted;
            $this->log->write("Expired task locks temizlendi: {$deleted} kayıt");
            
            // Event queue temizliği (completed/failed events)
            $eventRetention = 30;
            $result = $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_event_queue` 
                WHERE status IN ('completed', 'failed') 
                AND created_at < DATE_SUB(NOW(), INTERVAL {$eventRetention} DAY)");
            $deleted = $this->db->countAffected();
            $totalRecordsDeleted += $deleted;
            $this->log->write("Event queue temizlendi: {$deleted} kayıt");
            
            // OpenCart session temizliği
            $sessionRetention = $retention['sessions'];
            $result = $this->db->query("DELETE FROM `" . DB_PREFIX . "session` 
                WHERE expire < DATE_SUB(NOW(), INTERVAL {$sessionRetention} DAY)");
            $deleted = $this->db->countAffected();
            $totalRecordsDeleted += $deleted;
            $this->log->write("OpenCart sessions temizlendi: {$deleted} kayıt");
            
            // Database optimize
            $this->optimizeDatabase();
            
            $this->completeCleanupJob($cleanupId, 0, $totalRecordsDeleted, 0);
            
            return [
                'records_deleted' => $totalRecordsDeleted,
                'tables_optimized' => true
            ];
            
        } catch (Exception $e) {
            $this->failCleanupJob($cleanupId, $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Sessions temizliği
     */
    public function cleanupSessions($retentionDays = 30) {
        $cleanupId = $this->startCleanupJob(self::TYPE_SESSIONS, 'sessions', $retentionDays);
        
        try {
            $this->log->write("Session temizliği başlatıldı (retention: {$retentionDays} gün)");
            
            // MesChain user sessions
            $result = $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_user_sessions` 
                WHERE last_activity < DATE_SUB(NOW(), INTERVAL {$retentionDays} DAY)");
            $deleted = $this->db->countAffected();
            
            $this->completeCleanupJob($cleanupId, 0, $deleted, 0);
            
            return [
                'records_deleted' => $deleted
            ];
            
        } catch (Exception $e) {
            $this->failCleanupJob($cleanupId, $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Upload klasörünü temizle
     */
    public function cleanupUploads($retentionDays = 1) {
        $cleanupId = $this->startCleanupJob(self::TYPE_UPLOADS, DIR_UPLOAD, $retentionDays);
        
        try {
            $this->log->write("Upload temizliği başlatıldı (retention: {$retentionDays} gün)");
            
            $filesDeleted = 0;
            $bytesFreed = 0;
            $cutoffTime = time() - ($retentionDays * 24 * 60 * 60);
            
            // Geçici upload dosyaları
            if (is_dir(DIR_UPLOAD . 'temp/')) {
                $this->cleanupDirectory(DIR_UPLOAD . 'temp/', $cutoffTime, $filesDeleted, $bytesFreed);
            }
            
            // Backup dosyaları (eski olanlar)
            if (is_dir(DIR_UPLOAD . 'backup/')) {
                $backupRetention = time() - (7 * 24 * 60 * 60); // 7 gün
                $this->cleanupDirectory(DIR_UPLOAD . 'backup/', $backupRetention, $filesDeleted, $bytesFreed, '*.sql*');
            }
            
            // Export dosyaları
            if (is_dir(DIR_UPLOAD . 'export/')) {
                $exportRetention = time() - ($retentionDays * 24 * 60 * 60);
                $this->cleanupDirectory(DIR_UPLOAD . 'export/', $exportRetention, $filesDeleted, $bytesFreed);
            }
            
            $this->completeCleanupJob($cleanupId, $filesDeleted, 0, $bytesFreed);
            
            return [
                'files_deleted' => $filesDeleted,
                'bytes_freed' => $bytesFreed,
                'human_readable_size' => $this->formatBytes($bytesFreed)
            ];
            
        } catch (Exception $e) {
            $this->failCleanupJob($cleanupId, $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Dizin temizliği
     */
    private function cleanupDirectory($directory, $cutoffTime, &$filesDeleted, &$bytesFreed, $pattern = '*') {
        if (!is_dir($directory)) {
            return;
        }
        
        $files = glob($directory . DIRECTORY_SEPARATOR . $pattern);
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $fileTime = filemtime($file);
                
                if ($fileTime < $cutoffTime) {
                    $fileSize = filesize($file);
                    
                    if (unlink($file)) {
                        $filesDeleted++;
                        $bytesFreed += $fileSize;
                        $this->log->write("Dosya silindi: " . basename($file));
                    }
                }
            } elseif (is_dir($file) && !in_array(basename($file), ['.', '..'])) {
                // Alt dizinleri recursive temizle
                $this->cleanupDirectory($file, $cutoffTime, $filesDeleted, $bytesFreed, $pattern);
                
                // Boş dizini sil
                if ($this->isDirEmpty($file)) {
                    if (rmdir($file)) {
                        $this->log->write("Boş dizin silindi: " . basename($file));
                    }
                }
            }
        }
    }
    
    /**
     * Database optimize
     */
    private function optimizeDatabase() {
        $this->log->write("Database optimize başlatıldı");
        
        // MesChain tablolarını optimize et
        $tables = [
            'meschain_task_executions',
            'meschain_health_checks',
            'meschain_performance_logs',
            'meschain_system_metrics',
            'meschain_user_activities',
            'meschain_alerts',
            'meschain_event_queue'
        ];
        
        foreach ($tables as $table) {
            try {
                $this->db->query("OPTIMIZE TABLE `" . DB_PREFIX . $table . "`");
                $this->log->write("Tablo optimize edildi: {$table}");
            } catch (Exception $e) {
                $this->log->write("Tablo optimize hatası ({$table}): " . $e->getMessage());
            }
        }
        
        // ANALYZE TABLE de çalıştır
        foreach ($tables as $table) {
            try {
                $this->db->query("ANALYZE TABLE `" . DB_PREFIX . $table . "`");
            } catch (Exception $e) {
                // Hata loglamaya gerek yok, analyze optional
            }
        }
    }
    
    /**
     * Cleanup job başlat
     */
    private function startCleanupJob($type, $path, $retentionDays) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_cleanup_jobs` SET
            cleanup_type = '" . $this->db->escape($type) . "',
            target_path = '" . $this->db->escape($path) . "',
            retention_days = " . (int)$retentionDays . ",
            started_at = NOW(),
            tenant_id = " . (int)$this->getCurrentTenantId()
        );
        
        return $this->db->getLastId();
    }
    
    /**
     * Cleanup job tamamla
     */
    private function completeCleanupJob($cleanupId, $filesDeleted, $recordsDeleted, $bytesFreed) {
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_cleanup_jobs` SET
            status = 'completed',
            completed_at = NOW(),
            files_deleted = " . (int)$filesDeleted . ",
            records_deleted = " . (int)$recordsDeleted . ",
            bytes_freed = " . (int)$bytesFreed . "
            WHERE cleanup_id = " . (int)$cleanupId
        );
    }
    
    /**
     * Cleanup job başarısız işaretle
     */
    private function failCleanupJob($cleanupId, $errorMessage) {
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_cleanup_jobs` SET
            status = 'failed',
            completed_at = NOW(),
            error_message = '" . $this->db->escape($errorMessage) . "'
            WHERE cleanup_id = " . (int)$cleanupId
        );
    }
    
    /**
     * Dizin boş mu kontrol et
     */
    private function isDirEmpty($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = scandir($dir);
        return count($files) <= 2; // . ve .. dışında dosya yok
    }
    
    /**
     * Bytes'ı human readable formata çevir
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Cleanup geçmişini al
     */
    public function getCleanupHistory($limit = 50) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_cleanup_jobs` 
            ORDER BY started_at DESC LIMIT " . (int)$limit);
        
        $history = [];
        foreach ($query->rows as $row) {
            $row['human_readable_size'] = $this->formatBytes($row['bytes_freed']);
            $history[] = $row;
        }
        
        return $history;
    }
    
    /**
     * Cleanup istatistikleri
     */
    public function getCleanupStats($timeframe = '30 days') {
        $stats = [];
        
        // Toplam temizlik stats
        $totalQuery = $this->db->query("SELECT 
            cleanup_type,
            COUNT(*) as total_jobs,
            SUM(files_deleted) as total_files_deleted,
            SUM(records_deleted) as total_records_deleted,
            SUM(bytes_freed) as total_bytes_freed,
            AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_duration
            FROM `" . DB_PREFIX . "meschain_cleanup_jobs` 
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL {$timeframe})
            AND status = 'completed'
            GROUP BY cleanup_type");
        
        foreach ($totalQuery->rows as $row) {
            $stats[$row['cleanup_type']] = [
                'total_jobs' => $row['total_jobs'],
                'total_files_deleted' => $row['total_files_deleted'],
                'total_records_deleted' => $row['total_records_deleted'],
                'total_bytes_freed' => $row['total_bytes_freed'],
                'human_readable_size' => $this->formatBytes($row['total_bytes_freed']),
                'avg_duration' => round($row['avg_duration'], 2)
            ];
        }
        
        // Genel özet
        $summaryQuery = $this->db->query("SELECT 
            COUNT(*) as total_cleanup_jobs,
            SUM(files_deleted) as total_files_deleted,
            SUM(records_deleted) as total_records_deleted,
            SUM(bytes_freed) as total_bytes_freed
            FROM `" . DB_PREFIX . "meschain_cleanup_jobs` 
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL {$timeframe})
            AND status = 'completed'");
        
        if ($summaryQuery->num_rows) {
            $summary = $summaryQuery->row;
            $summary['human_readable_size'] = $this->formatBytes($summary['total_bytes_freed']);
            $stats['summary'] = $summary;
        }
        
        return $stats;
    }
    
    /**
     * Disk kullanım analizi
     */
    public function getDiskUsageAnalysis() {
        $analysis = [];
        
        $directories = [
            'logs' => DIR_LOGS,
            'cache' => DIR_CACHE,
            'uploads' => DIR_UPLOAD,
            'images' => DIR_IMAGE
        ];
        
        foreach ($directories as $name => $path) {
            if (is_dir($path)) {
                $size = $this->getDirectorySize($path);
                $analysis[$name] = [
                    'path' => $path,
                    'size_bytes' => $size,
                    'human_readable_size' => $this->formatBytes($size),
                    'file_count' => $this->getFileCount($path),
                    'free_space' => disk_free_space($path),
                    'total_space' => disk_total_space($path)
                ];
                
                $analysis[$name]['usage_percent'] = round(
                    (($analysis[$name]['total_space'] - $analysis[$name]['free_space']) / $analysis[$name]['total_space']) * 100, 
                    2
                );
            }
        }
        
        return $analysis;
    }
    
    /**
     * Dizin boyutunu hesapla
     */
    private function getDirectorySize($directory) {
        $size = 0;
        
        if (is_dir($directory)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        
        return $size;
    }
    
    /**
     * Dizindeki dosya sayısını say
     */
    private function getFileCount($directory) {
        $count = 0;
        
        if (is_dir($directory)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Manuel cleanup çalıştır
     */
    public function runManualCleanup($types = [], $retentionOverride = []) {
        $this->log->write("Manuel cleanup başlatıldı");
        
        $availableTypes = [
            self::TYPE_LOGS,
            self::TYPE_CACHE,
            self::TYPE_TEMP_FILES,
            self::TYPE_DATABASE,
            self::TYPE_SESSIONS,
            self::TYPE_UPLOADS
        ];
        
        if (empty($types)) {
            $types = $availableTypes;
        }
        
        $results = [];
        
        foreach ($types as $type) {
            try {
                switch ($type) {
                    case self::TYPE_LOGS:
                        $results[$type] = $this->cleanupLogs($retentionOverride['logs'] ?? self::DEFAULT_RETENTION['logs']);
                        break;
                    case self::TYPE_CACHE:
                        $results[$type] = $this->cleanupCache($retentionOverride['cache'] ?? self::DEFAULT_RETENTION['cache']);
                        break;
                    case self::TYPE_TEMP_FILES:
                        $results[$type] = $this->cleanupTempFiles($retentionOverride['temp_files'] ?? self::DEFAULT_RETENTION['temp_files']);
                        break;
                    case self::TYPE_DATABASE:
                        $results[$type] = $this->cleanupDatabase($retentionOverride);
                        break;
                    case self::TYPE_SESSIONS:
                        $results[$type] = $this->cleanupSessions($retentionOverride['sessions'] ?? self::DEFAULT_RETENTION['sessions']);
                        break;
                    case self::TYPE_UPLOADS:
                        $results[$type] = $this->cleanupUploads($retentionOverride['uploads_temp'] ?? self::DEFAULT_RETENTION['uploads_temp']);
                        break;
                }
            } catch (Exception $e) {
                $results[$type] = [
                    'error' => $e->getMessage(),
                    'success' => false
                ];
                $this->log->write("Manuel cleanup hatası ({$type}): " . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    /**
     * Mevcut tenant ID'sini al
     */
    private function getCurrentTenantId() {
        if ($this->registry->has('session')) {
            $session = $this->registry->get('session');
            return $session->data['tenant_id'] ?? 1;
        }
        return 1;
    }
}
?> 