<?php
/**
 * MeschainSchedulerHelper - Task Scheduling ve Cron Job Yönetimi
 * 
 * Zamanlı görevler, recurring tasklar ve background job yönetimi
 * 
 * @author MesChain Development Team
 * @version 1.0.0
 * @since 2024-01-21
 */

class MeschainSchedulerHelper {
    
    private $registry;
    private $db;
    private $log;
    private $eventHelper;
    private $configHelper;
    
    // Task türleri
    const TYPE_SYNC = 'sync';
    const TYPE_IMPORT = 'import';
    const TYPE_EXPORT = 'export';
    const TYPE_CLEANUP = 'cleanup';
    const TYPE_NOTIFICATION = 'notification';
    const TYPE_BACKUP = 'backup';
    const TYPE_HEALTH_CHECK = 'health_check';
    const TYPE_CUSTOM = 'custom';
    
    // Task durumları
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PAUSED = 'paused';
    
    // Recurring patterns
    const FREQUENCY_HOURLY = 'hourly';
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_CUSTOM = 'custom';
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->log = new Log('meschain_scheduler.log');
        
        // Helper'ları yükle
        require_once(DIR_SYSTEM . 'library/meschain/helper/event.php');
        require_once(DIR_SYSTEM . 'library/meschain/helper/config.php');
        
        $this->eventHelper = new MeschainEventHelper($registry);
        $this->configHelper = new MeschainConfigHelper($registry);
        
        $this->createSchedulerTables();
        $this->loadDefaultTasks();
    }
    
    /**
     * Scheduler tablolarını oluştur
     */
    private function createSchedulerTables() {
        // Scheduled tasks tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_scheduled_tasks` (
            `task_id` int(11) NOT NULL AUTO_INCREMENT,
            `task_name` varchar(100) NOT NULL,
            `task_type` varchar(50) NOT NULL,
            `description` text,
            `handler_class` varchar(255) NOT NULL,
            `handler_method` varchar(100) NOT NULL,
            `parameters` json,
            `frequency` enum('hourly','daily','weekly','monthly','custom') DEFAULT 'daily',
            `cron_expression` varchar(100),
            `next_run` datetime NOT NULL,
            `last_run` datetime DEFAULT NULL,
            `run_count` int(11) DEFAULT 0,
            `max_runtime` int(11) DEFAULT 3600,
            `timeout_action` enum('kill','notify','ignore') DEFAULT 'notify',
            `retry_attempts` int(11) DEFAULT 3,
            `retry_delay` int(11) DEFAULT 300,
            `is_active` tinyint(1) DEFAULT 1,
            `tenant_id` int(11) DEFAULT NULL,
            `created_by` int(11) DEFAULT NULL,
            `date_created` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`task_id`),
            KEY `task_type` (`task_type`),
            KEY `next_run` (`next_run`),
            KEY `is_active` (`is_active`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Task execution history tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_task_executions` (
            `execution_id` int(11) NOT NULL AUTO_INCREMENT,
            `task_id` int(11) NOT NULL,
            `status` enum('pending','running','completed','failed','cancelled','timeout') DEFAULT 'pending',
            `started_at` datetime DEFAULT NULL,
            `completed_at` datetime DEFAULT NULL,
            `execution_time` decimal(10,4) DEFAULT NULL,
            `memory_usage` int(11) DEFAULT NULL,
            `output` longtext,
            `error_message` text,
            `progress` decimal(5,2) DEFAULT 0,
            `pid` int(11) DEFAULT NULL,
            `triggered_by` enum('cron','manual','api','event') DEFAULT 'cron',
            `triggered_by_user` int(11) DEFAULT NULL,
            `date_created` datetime NOT NULL,
            PRIMARY KEY (`execution_id`),
            KEY `task_id` (`task_id`),
            KEY `status` (`status`),
            KEY `started_at` (`started_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Task dependencies tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_task_dependencies` (
            `dependency_id` int(11) NOT NULL AUTO_INCREMENT,
            `task_id` int(11) NOT NULL,
            `depends_on_task_id` int(11) NOT NULL,
            `dependency_type` enum('success','completion','always') DEFAULT 'success',
            `date_created` datetime NOT NULL,
            PRIMARY KEY (`dependency_id`),
            UNIQUE KEY `task_dependency` (`task_id`, `depends_on_task_id`),
            KEY `task_id` (`task_id`),
            KEY `depends_on_task_id` (`depends_on_task_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Task locks tablosu (concurrent execution önlemi)
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_task_locks` (
            `lock_id` varchar(255) NOT NULL,
            `task_id` int(11) NOT NULL,
            `locked_by` varchar(255) NOT NULL,
            `locked_at` datetime NOT NULL,
            `expires_at` datetime NOT NULL,
            PRIMARY KEY (`lock_id`),
            KEY `task_id` (`task_id`),
            KEY `expires_at` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        $this->log->write('Scheduler tabloları oluşturuldu/kontrol edildi');
    }
    
    /**
     * Varsayılan taskları yükle
     */
    private function loadDefaultTasks() {
        $defaultTasks = [
            [
                'task_name' => 'System Health Check',
                'task_type' => self::TYPE_HEALTH_CHECK,
                'description' => 'Sistem sağlık durumu kontrolü',
                'handler_class' => 'MeschainMonitoringHelper',
                'handler_method' => 'runHealthCheck',
                'frequency' => self::FREQUENCY_HOURLY,
                'parameters' => []
            ],
            [
                'task_name' => 'Event Queue Processor',
                'task_type' => self::TYPE_SYNC,
                'description' => 'Event queue işleme görevi',
                'handler_class' => 'MeschainEventHelper',
                'handler_method' => 'processQueue',
                'frequency' => self::FREQUENCY_CUSTOM,
                'cron_expression' => '*/5 * * * *', // Her 5 dakika
                'parameters' => ['limit' => 50]
            ],
            [
                'task_name' => 'Database Cleanup',
                'task_type' => self::TYPE_CLEANUP,
                'description' => 'Eski log ve geçici verileri temizle',
                'handler_class' => 'MeschainCleanupHelper',
                'handler_method' => 'cleanupOldData',
                'frequency' => self::FREQUENCY_DAILY,
                'parameters' => ['retention_days' => 30]
            ],
            [
                'task_name' => 'Marketplace Sync',
                'task_type' => self::TYPE_SYNC,
                'description' => 'Tüm marketplace\'lerden veri senkronizasyonu',
                'handler_class' => 'MeschainSyncHelper',
                'handler_method' => 'syncAllMarketplaces',
                'frequency' => self::FREQUENCY_HOURLY,
                'parameters' => []
            ],
            [
                'task_name' => 'Config Backup',
                'task_type' => self::TYPE_BACKUP,
                'description' => 'Sistem konfigürasyonlarının yedeği',
                'handler_class' => 'MeschainBackupHelper',
                'handler_method' => 'backupConfigs',
                'frequency' => self::FREQUENCY_DAILY,
                'parameters' => []
            ]
        ];
        
        foreach ($defaultTasks as $task) {
            $this->createTaskIfNotExists($task);
        }
    }
    
    /**
     * Task oluştur (eğer yoksa)
     */
    private function createTaskIfNotExists($taskData) {
        $existing = $this->db->query("SELECT task_id FROM `" . DB_PREFIX . "meschain_scheduled_tasks` 
            WHERE task_name = '" . $this->db->escape($taskData['task_name']) . "'");
        
        if (!$existing->num_rows) {
            $this->createTask($taskData);
        }
    }
    
    /**
     * Yeni task oluştur
     */
    public function createTask($data) {
        $nextRun = $this->calculateNextRun($data['frequency'], $data['cron_expression'] ?? null);
        
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_scheduled_tasks` SET
            task_name = '" . $this->db->escape($data['task_name']) . "',
            task_type = '" . $this->db->escape($data['task_type']) . "',
            description = '" . $this->db->escape($data['description'] ?? '') . "',
            handler_class = '" . $this->db->escape($data['handler_class']) . "',
            handler_method = '" . $this->db->escape($data['handler_method']) . "',
            parameters = '" . $this->db->escape(json_encode($data['parameters'] ?? [])) . "',
            frequency = '" . $this->db->escape($data['frequency']) . "',
            cron_expression = " . (isset($data['cron_expression']) ? "'" . $this->db->escape($data['cron_expression']) . "'" : "NULL") . ",
            next_run = '" . $nextRun . "',
            max_runtime = " . (int)($data['max_runtime'] ?? 3600) . ",
            timeout_action = '" . $this->db->escape($data['timeout_action'] ?? 'notify') . "',
            retry_attempts = " . (int)($data['retry_attempts'] ?? 3) . ",
            retry_delay = " . (int)($data['retry_delay'] ?? 300) . ",
            tenant_id = " . (isset($data['tenant_id']) ? (int)$data['tenant_id'] : "NULL") . ",
            created_by = " . (int)$this->getCurrentUserId() . ",
            date_created = NOW(),
            date_modified = NOW()
        ");
        
        $taskId = $this->db->getLastId();
        
        $this->log->write("Task oluşturuldu: {$data['task_name']} (ID: {$taskId})");
        
        return $taskId;
    }
    
    /**
     * Task'ı güncelle
     */
    public function updateTask($taskId, $data) {
        $updates = [];
        
        foreach ($data as $field => $value) {
            switch ($field) {
                case 'task_name':
                case 'description':
                case 'handler_class':
                case 'handler_method':
                case 'task_type':
                case 'frequency':
                case 'cron_expression':
                case 'timeout_action':
                    $updates[] = "`{$field}` = '" . $this->db->escape($value) . "'";
                    break;
                case 'parameters':
                    $updates[] = "`{$field}` = '" . $this->db->escape(json_encode($value)) . "'";
                    break;
                case 'max_runtime':
                case 'retry_attempts':
                case 'retry_delay':
                case 'is_active':
                    $updates[] = "`{$field}` = " . (int)$value;
                    break;
            }
        }
        
        if (!empty($updates)) {
            $updates[] = "`date_modified` = NOW()";
            
            // Next run'ı yeniden hesapla
            if (isset($data['frequency']) || isset($data['cron_expression'])) {
                $task = $this->getTask($taskId);
                $nextRun = $this->calculateNextRun(
                    $data['frequency'] ?? $task['frequency'],
                    $data['cron_expression'] ?? $task['cron_expression']
                );
                $updates[] = "`next_run` = '" . $nextRun . "'";
            }
            
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_scheduled_tasks` SET " . 
                implode(', ', $updates) . " WHERE task_id = " . (int)$taskId);
            
            $this->log->write("Task güncellendi: ID {$taskId}");
        }
        
        return true;
    }
    
    /**
     * Scheduler'ı çalıştır (cron job olarak çağrılır)
     */
    public function run() {
        $this->log->write("Scheduler başlatıldı");
        
        // Kilitleri temizle (expired)
        $this->cleanupExpiredLocks();
        
        // Çalışmaya hazır taskları al
        $tasks = $this->getPendingTasks();
        
        $executed = 0;
        
        foreach ($tasks as $task) {
            try {
                if ($this->canExecuteTask($task)) {
                    $this->executeTask($task);
                    $executed++;
                }
            } catch (Exception $e) {
                $this->log->write("Task execution hatası: {$task['task_name']} - " . $e->getMessage());
            }
        }
        
        $this->log->write("Scheduler tamamlandı. {$executed} task çalıştırıldı.");
        
        return [
            'executed_tasks' => $executed,
            'total_pending' => count($tasks)
        ];
    }
    
    /**
     * Bekleyen taskları al
     */
    private function getPendingTasks() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_scheduled_tasks` 
            WHERE is_active = 1 
            AND next_run <= NOW()
            ORDER BY next_run ASC");
        
        return $query->rows;
    }
    
    /**
     * Task çalıştırılabilir mi kontrol et
     */
    private function canExecuteTask($task) {
        // Dependency kontrolü
        if (!$this->checkTaskDependencies($task['task_id'])) {
            return false;
        }
        
        // Lock kontrolü
        if ($this->isTaskLocked($task['task_id'])) {
            return false;
        }
        
        // Concurrent execution kontrolü
        $runningQuery = $this->db->query("SELECT execution_id FROM `" . DB_PREFIX . "meschain_task_executions` 
            WHERE task_id = " . (int)$task['task_id'] . " 
            AND status = 'running'");
        
        if ($runningQuery->num_rows > 0) {
            $this->log->write("Task zaten çalışıyor: {$task['task_name']}");
            return false;
        }
        
        return true;
    }
    
    /**
     * Task'ı çalıştır
     */
    private function executeTask($task) {
        $taskId = $task['task_id'];
        $lockId = $this->acquireTaskLock($taskId);
        
        if (!$lockId) {
            $this->log->write("Task lock alınamadı: {$task['task_name']}");
            return false;
        }
        
        // Execution kaydı oluştur
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_task_executions` SET
            task_id = " . (int)$taskId . ",
            status = 'running',
            started_at = NOW(),
            triggered_by = 'cron',
            date_created = NOW()
        ");
        
        $executionId = $this->db->getLastId();
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        try {
            $this->log->write("Task başlatılıyor: {$task['task_name']}");
            
            // Handler'ı yükle ve çalıştır
            $result = $this->callTaskHandler($task);
            
            $executionTime = microtime(true) - $startTime;
            $memoryUsage = memory_get_usage(true) - $startMemory;
            
            // Başarılı tamamlandı
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_task_executions` SET
                status = 'completed',
                completed_at = NOW(),
                execution_time = " . (float)$executionTime . ",
                memory_usage = " . (int)$memoryUsage . ",
                output = '" . $this->db->escape(json_encode($result)) . "',
                progress = 100
                WHERE execution_id = " . (int)$executionId);
            
            // Task next run güncelle
            $nextRun = $this->calculateNextRun($task['frequency'], $task['cron_expression']);
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_scheduled_tasks` SET
                last_run = NOW(),
                next_run = '" . $nextRun . "',
                run_count = run_count + 1
                WHERE task_id = " . (int)$taskId);
            
            $this->log->write("Task tamamlandı: {$task['task_name']} ({$executionTime}s)");
            
            // Event tetikle
            $this->eventHelper->trigger('task.completed', [
                'task_id' => $taskId,
                'task_name' => $task['task_name'],
                'execution_time' => $executionTime,
                'result' => $result
            ], ['type' => 'async']);
            
        } catch (Exception $e) {
            $executionTime = microtime(true) - $startTime;
            
            // Hata ile tamamlandı
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_task_executions` SET
                status = 'failed',
                completed_at = NOW(),
                execution_time = " . (float)$executionTime . ",
                error_message = '" . $this->db->escape($e->getMessage()) . "'
                WHERE execution_id = " . (int)$executionId);
            
            $this->log->write("Task başarısız: {$task['task_name']} - " . $e->getMessage());
            
            // Retry logic
            $this->handleTaskFailure($task, $e);
            
            // Event tetikle
            $this->eventHelper->trigger('task.failed', [
                'task_id' => $taskId,
                'task_name' => $task['task_name'],
                'error' => $e->getMessage()
            ], ['type' => 'async']);
            
        } finally {
            // Lock'u serbest bırak
            $this->releaseTaskLock($lockId);
        }
        
        return true;
    }
    
    /**
     * Task handler'ını çağır
     */
    private function callTaskHandler($task) {
        $className = $task['handler_class'];
        $methodName = $task['handler_method'];
        $parameters = json_decode($task['parameters'], true) ?? [];
        
        // Helper sınıfını yükle
        if (strpos($className, 'Meschain') === 0) {
            $helperFile = DIR_SYSTEM . 'library/meschain/helper/' . 
                strtolower(str_replace(['Meschain', 'Helper'], '', $className)) . '.php';
            
            if (file_exists($helperFile)) {
                require_once($helperFile);
            }
        }
        
        if (!class_exists($className)) {
            throw new Exception("Handler class not found: {$className}");
        }
        
        $instance = new $className($this->registry);
        
        if (!method_exists($instance, $methodName)) {
            throw new Exception("Handler method not found: {$className}::{$methodName}");
        }
        
        // Method'u parametrelerle çağır
        return call_user_func_array([$instance, $methodName], $parameters);
    }
    
    /**
     * Task başarısızlığını işle
     */
    private function handleTaskFailure($task, $exception) {
        $taskId = $task['task_id'];
        $retryAttempts = $task['retry_attempts'];
        $retryDelay = $task['retry_delay'];
        
        // Son executions'ları kontrol et
        $failedQuery = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_task_executions` 
            WHERE task_id = " . (int)$taskId . " 
            AND status = 'failed' 
            AND started_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        
        $recentFailures = $failedQuery->row['count'];
        
        if ($recentFailures < $retryAttempts) {
            // Retry planla
            $retryTime = date('Y-m-d H:i:s', strtotime("+{$retryDelay} seconds"));
            
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_scheduled_tasks` SET
                next_run = '" . $retryTime . "'
                WHERE task_id = " . (int)$taskId);
            
            $this->log->write("Task retry planlandı: {$task['task_name']} - {$retryTime}");
        } else {
            // Max retry aşıldı - task'ı deaktive et
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_scheduled_tasks` SET
                is_active = 0
                WHERE task_id = " . (int)$taskId);
            
            $this->log->write("Task deaktive edildi (max retry): {$task['task_name']}");
            
            // Critical alert
            $this->eventHelper->trigger('task.max_retry_exceeded', [
                'task_id' => $taskId,
                'task_name' => $task['task_name'],
                'error' => $exception->getMessage()
            ], ['type' => 'async', 'priority' => 4]);
        }
    }
    
    /**
     * Task lock al
     */
    private function acquireTaskLock($taskId) {
        $lockId = "task_{$taskId}_" . uniqid();
        $lockedBy = gethostname() . "_" . getmypid();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        try {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_task_locks` SET
                lock_id = '" . $this->db->escape($lockId) . "',
                task_id = " . (int)$taskId . ",
                locked_by = '" . $this->db->escape($lockedBy) . "',
                locked_at = NOW(),
                expires_at = '" . $expiresAt . "'
            ");
            
            return $lockId;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Task lock serbest bırak
     */
    private function releaseTaskLock($lockId) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_task_locks` 
            WHERE lock_id = '" . $this->db->escape($lockId) . "'");
    }
    
    /**
     * Task kilitli mi kontrol et
     */
    private function isTaskLocked($taskId) {
        $query = $this->db->query("SELECT lock_id FROM `" . DB_PREFIX . "meschain_task_locks` 
            WHERE task_id = " . (int)$taskId . " 
            AND expires_at > NOW()");
        
        return $query->num_rows > 0;
    }
    
    /**
     * Süresi dolmuş lock'ları temizle
     */
    private function cleanupExpiredLocks() {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_task_locks` 
            WHERE expires_at <= NOW()");
    }
    
    /**
     * Task dependency'lerini kontrol et
     */
    private function checkTaskDependencies($taskId) {
        $query = $this->db->query("SELECT td.*, st.task_name 
            FROM `" . DB_PREFIX . "meschain_task_dependencies` td
            LEFT JOIN `" . DB_PREFIX . "meschain_scheduled_tasks` st ON (td.depends_on_task_id = st.task_id)
            WHERE td.task_id = " . (int)$taskId);
        
        foreach ($query->rows as $dependency) {
            $dependsOnTaskId = $dependency['depends_on_task_id'];
            $dependencyType = $dependency['dependency_type'];
            
            // Son execution'ı kontrol et
            $lastExecQuery = $this->db->query("SELECT status, completed_at 
                FROM `" . DB_PREFIX . "meschain_task_executions` 
                WHERE task_id = " . (int)$dependsOnTaskId . "
                ORDER BY execution_id DESC LIMIT 1");
            
            if (!$lastExecQuery->num_rows) {
                // Hiç çalışmamış
                if ($dependencyType !== 'always') {
                    return false;
                }
            } else {
                $lastStatus = $lastExecQuery->row['status'];
                
                switch ($dependencyType) {
                    case 'success':
                        if ($lastStatus !== 'completed') {
                            $this->log->write("Dependency failed: Task {$taskId} waiting for {$dependsOnTaskId} success");
                            return false;
                        }
                        break;
                    case 'completion':
                        if (!in_array($lastStatus, ['completed', 'failed'])) {
                            $this->log->write("Dependency failed: Task {$taskId} waiting for {$dependsOnTaskId} completion");
                            return false;
                        }
                        break;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Sonraki çalışma zamanını hesapla
     */
    private function calculateNextRun($frequency, $cronExpression = null) {
        if ($frequency === self::FREQUENCY_CUSTOM && $cronExpression) {
            return $this->calculateCronNextRun($cronExpression);
        }
        
        $baseTime = time();
        
        switch ($frequency) {
            case self::FREQUENCY_HOURLY:
                return date('Y-m-d H:i:s', strtotime('+1 hour', $baseTime));
            case self::FREQUENCY_DAILY:
                return date('Y-m-d H:i:s', strtotime('+1 day', $baseTime));
            case self::FREQUENCY_WEEKLY:
                return date('Y-m-d H:i:s', strtotime('+1 week', $baseTime));
            case self::FREQUENCY_MONTHLY:
                return date('Y-m-d H:i:s', strtotime('+1 month', $baseTime));
            default:
                return date('Y-m-d H:i:s', strtotime('+1 hour', $baseTime));
        }
    }
    
    /**
     * Cron expression'dan sonraki çalışma zamanını hesapla
     */
    private function calculateCronNextRun($cronExpression) {
        // Basit cron parser (tam featured değil)
        $parts = explode(' ', $cronExpression);
        
        if (count($parts) !== 5) {
            throw new Exception("Invalid cron expression: {$cronExpression}");
        }
        
        [$minute, $hour, $day, $month, $dayOfWeek] = $parts;
        
        $now = time();
        $nextRun = $now;
        
        // Basit örnekler
        if ($cronExpression === '*/5 * * * *') {
            // Her 5 dakika
            $nextMinute = (int)(date('i', $now) / 5) * 5 + 5;
            $nextRun = mktime(date('H', $now), $nextMinute, 0, date('n', $now), date('j', $now), date('Y', $now));
            
            if ($nextMinute >= 60) {
                $nextRun = strtotime('+1 hour', mktime(date('H', $now), 0, 0, date('n', $now), date('j', $now), date('Y', $now)));
            }
        } elseif ($cronExpression === '0 * * * *') {
            // Her saat başı
            $nextRun = strtotime('+1 hour', mktime(date('H', $now), 0, 0, date('n', $now), date('j', $now), date('Y', $now)));
        } else {
            // Fallback - 1 saat sonra
            $nextRun = strtotime('+1 hour', $now);
        }
        
        return date('Y-m-d H:i:s', $nextRun);
    }
    
    /**
     * Task'ı manuel çalıştır
     */
    public function runTaskManually($taskId, $userId = null) {
        $task = $this->getTask($taskId);
        
        if (!$task) {
            throw new Exception("Task bulunamadı: {$taskId}");
        }
        
        if (!$task['is_active']) {
            throw new Exception("Task aktif değil: {$task['task_name']}");
        }
        
        // Lock kontrolü
        if ($this->isTaskLocked($taskId)) {
            throw new Exception("Task kilitli: {$task['task_name']}");
        }
        
        $lockId = $this->acquireTaskLock($taskId);
        
        if (!$lockId) {
            throw new Exception("Task lock alınamadı: {$task['task_name']}");
        }
        
        try {
            // Execution kaydı oluştur
            $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_task_executions` SET
                task_id = " . (int)$taskId . ",
                status = 'running',
                started_at = NOW(),
                triggered_by = 'manual',
                triggered_by_user = " . (int)($userId ?: $this->getCurrentUserId()) . ",
                date_created = NOW()
            ");
            
            $executionId = $this->db->getLastId();
            $startTime = microtime(true);
            
            // Handler'ı çalıştır
            $result = $this->callTaskHandler($task);
            
            $executionTime = microtime(true) - $startTime;
            
            // Başarılı tamamlandı
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_task_executions` SET
                status = 'completed',
                completed_at = NOW(),
                execution_time = " . (float)$executionTime . ",
                output = '" . $this->db->escape(json_encode($result)) . "',
                progress = 100
                WHERE execution_id = " . (int)$executionId);
            
            $this->log->write("Task manuel çalıştırıldı: {$task['task_name']} by User#{$userId}");
            
            return [
                'success' => true,
                'execution_id' => $executionId,
                'execution_time' => $executionTime,
                'result' => $result
            ];
            
        } catch (Exception $e) {
            // Hata ile tamamlandı
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_task_executions` SET
                status = 'failed',
                completed_at = NOW(),
                error_message = '" . $this->db->escape($e->getMessage()) . "'
                WHERE execution_id = " . (int)$executionId);
            
            throw $e;
            
        } finally {
            $this->releaseTaskLock($lockId);
        }
    }
    
    /**
     * Task bilgilerini al
     */
    public function getTask($taskId) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_scheduled_tasks` 
            WHERE task_id = " . (int)$taskId);
        
        if ($query->num_rows) {
            $task = $query->row;
            $task['parameters'] = json_decode($task['parameters'], true);
            return $task;
        }
        
        return null;
    }
    
    /**
     * Tüm taskları listele
     */
    public function getAllTasks($filters = []) {
        $sql = "SELECT st.*, 
            (SELECT COUNT(*) FROM `" . DB_PREFIX . "meschain_task_executions` te WHERE te.task_id = st.task_id) as total_executions,
            (SELECT COUNT(*) FROM `" . DB_PREFIX . "meschain_task_executions` te WHERE te.task_id = st.task_id AND te.status = 'completed') as successful_executions
            FROM `" . DB_PREFIX . "meschain_scheduled_tasks` st WHERE 1=1";
        
        if (isset($filters['task_type'])) {
            $sql .= " AND st.task_type = '" . $this->db->escape($filters['task_type']) . "'";
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND st.is_active = " . (int)$filters['is_active'];
        }
        
        if (isset($filters['tenant_id'])) {
            $sql .= " AND st.tenant_id = " . (int)$filters['tenant_id'];
        }
        
        $sql .= " ORDER BY st.next_run ASC";
        
        $query = $this->db->query($sql);
        
        $tasks = [];
        foreach ($query->rows as $row) {
            $row['parameters'] = json_decode($row['parameters'], true);
            $tasks[] = $row;
        }
        
        return $tasks;
    }
    
    /**
     * Task istatistiklerini al
     */
    public function getTaskStats($timeframe = '24 hours') {
        $stats = [];
        
        // Execution stats
        $execQuery = $this->db->query("SELECT 
            status,
            COUNT(*) as count,
            AVG(execution_time) as avg_time,
            MAX(execution_time) as max_time
            FROM `" . DB_PREFIX . "meschain_task_executions` 
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL {$timeframe})
            GROUP BY status");
        
        $stats['executions'] = [];
        foreach ($execQuery->rows as $row) {
            $stats['executions'][$row['status']] = [
                'count' => $row['count'],
                'avg_time' => round($row['avg_time'], 4),
                'max_time' => round($row['max_time'], 4)
            ];
        }
        
        // Task type stats
        $typeQuery = $this->db->query("SELECT 
            st.task_type,
            COUNT(*) as total_tasks,
            SUM(CASE WHEN st.is_active = 1 THEN 1 ELSE 0 END) as active_tasks
            FROM `" . DB_PREFIX . "meschain_scheduled_tasks` st
            GROUP BY st.task_type");
        
        $stats['task_types'] = [];
        foreach ($typeQuery->rows as $row) {
            $stats['task_types'][$row['task_type']] = [
                'total' => $row['total_tasks'],
                'active' => $row['active_tasks']
            ];
        }
        
        return $stats;
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
}
?> 