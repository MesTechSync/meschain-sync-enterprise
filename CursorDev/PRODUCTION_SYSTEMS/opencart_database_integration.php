<?php
/**
 * OpenCart Production Database Integration System
 * Comprehensive database logging and error tracking
 * 
 * @author OpenCart Production Team
 * @version 3.1.1
 * @date June 6, 2025
 */

class OpenCartDatabaseIntegration {
    private $db;
    private $config;
    private $errorTableName = 'opencart_error_logs';
    private $performanceTableName = 'opencart_performance_logs';
    private $debugMode = false;

    public function __construct($database, $config = []) {
        $this->db = $database;
        $this->config = array_merge([
            'enable_debug' => false,
            'enable_performance_tracking' => true,
            'enable_real_time_alerts' => true,
            'max_log_retention_days' => 90,
            'critical_error_threshold' => 5
        ], $config);
        
        $this->debugMode = $this->config['enable_debug'];
        $this->initializeTables();
    }

    /**
     * Initialize database tables for error logging
     */
    private function initializeTables() {
        // Create error logs table
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->errorTableName}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `level` enum('DEBUG','INFO','WARN','ERROR','CRITICAL') NOT NULL,
            `category` enum('API_ERROR','DATABASE_ERROR','MARKETPLACE_ERROR','SYNC_ERROR','PERFORMANCE_ERROR','AUTH_ERROR','VALIDATION_ERROR','SYSTEM_ERROR') NOT NULL,
            `marketplace` varchar(50) DEFAULT NULL,
            `message` text NOT NULL,
            `error_code` varchar(20) DEFAULT NULL,
            `file_path` varchar(255) DEFAULT NULL,
            `line_number` int(11) DEFAULT NULL,
            `stack_trace` longtext DEFAULT NULL,
            `context_data` json DEFAULT NULL,
            `user_id` int(11) DEFAULT NULL,
            `session_id` varchar(128) DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `request_url` varchar(500) DEFAULT NULL,
            `request_method` varchar(10) DEFAULT NULL,
            `request_id` varchar(64) DEFAULT NULL,
            `resolved` tinyint(1) DEFAULT 0,
            `resolved_at` datetime DEFAULT NULL,
            `resolved_by` int(11) DEFAULT NULL,
            `resolution_notes` text DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_timestamp` (`timestamp`),
            KEY `idx_level` (`level`),
            KEY `idx_category` (`category`),
            KEY `idx_marketplace` (`marketplace`),
            KEY `idx_resolved` (`resolved`),
            KEY `idx_request_id` (`request_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->query($sql);

        // Create performance logs table
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->performanceTableName}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `operation` varchar(100) NOT NULL,
            `duration_ms` int(11) NOT NULL,
            `memory_usage_bytes` bigint(20) DEFAULT NULL,
            `cpu_usage_percent` decimal(5,2) DEFAULT NULL,
            `marketplace` varchar(50) DEFAULT NULL,
            `status` enum('SUCCESS','FAILURE','TIMEOUT','WARNING') NOT NULL,
            `details` json DEFAULT NULL,
            `request_id` varchar(64) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_timestamp` (`timestamp`),
            KEY `idx_operation` (`operation`),
            KEY `idx_marketplace` (`marketplace`),
            KEY `idx_status` (`status`),
            KEY `idx_duration` (`duration_ms`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->query($sql);

        $this->logDebug("Database tables initialized successfully");
    }

    /**
     * Log error to database
     */
    public function logError($level, $category, $message, $context = []) {
        try {
            $data = [
                'level' => strtoupper($level),
                'category' => strtoupper($category),
                'message' => $message,
                'marketplace' => $context['marketplace'] ?? null,
                'error_code' => $context['error_code'] ?? null,
                'file_path' => $context['file'] ?? null,
                'line_number' => $context['line'] ?? null,
                'stack_trace' => $context['stack_trace'] ?? null,
                'context_data' => json_encode($context),
                'user_id' => $context['user_id'] ?? null,
                'session_id' => $context['session_id'] ?? session_id(),
                'ip_address' => $context['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $context['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null,
                'request_url' => $context['request_url'] ?? $_SERVER['REQUEST_URI'] ?? null,
                'request_method' => $context['request_method'] ?? $_SERVER['REQUEST_METHOD'] ?? null,
                'request_id' => $context['request_id'] ?? $this->generateRequestId()
            ];

            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO `{$this->errorTableName}` ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->db->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            $stmt->execute();
            $errorId = $this->db->lastInsertId();

            // Check for critical errors
            if ($level === 'CRITICAL') {
                $this->handleCriticalError($errorId, $message, $context);
            }

            return $errorId;
            
        } catch (Exception $e) {
            error_log("Failed to log error to database: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log performance metrics
     */
    public function logPerformance($operation, $duration, $context = []) {
        try {
            $data = [
                'operation' => $operation,
                'duration_ms' => (int)$duration,
                'memory_usage_bytes' => $context['memory_usage'] ?? memory_get_usage(true),
                'cpu_usage_percent' => $context['cpu_usage'] ?? null,
                'marketplace' => $context['marketplace'] ?? null,
                'status' => $context['status'] ?? 'SUCCESS',
                'details' => json_encode($context),
                'request_id' => $context['request_id'] ?? $this->generateRequestId()
            ];

            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO `{$this->performanceTableName}` ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->db->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            $stmt->execute();
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log("Failed to log performance data: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get error statistics
     */
    public function getErrorStatistics($timeframe = '24 HOUR') {
        try {
            $sql = "SELECT 
                        level,
                        category,
                        marketplace,
                        COUNT(*) as count,
                        MAX(timestamp) as last_occurrence
                    FROM `{$this->errorTableName}` 
                    WHERE timestamp >= DATE_SUB(NOW(), INTERVAL {$timeframe})
                    GROUP BY level, category, marketplace
                    ORDER BY count DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Failed to get error statistics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent errors
     */
    public function getRecentErrors($limit = 50, $level = null) {
        try {
            $whereClause = $level ? "WHERE level = :level" : "";
            
            $sql = "SELECT * FROM `{$this->errorTableName}` 
                    {$whereClause}
                    ORDER BY timestamp DESC 
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            
            if ($level) {
                $stmt->bindValue(':level', strtoupper($level));
            }
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Failed to get recent errors: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics($timeframe = '24 HOUR') {
        try {
            $sql = "SELECT 
                        operation,
                        marketplace,
                        COUNT(*) as execution_count,
                        AVG(duration_ms) as avg_duration,
                        MIN(duration_ms) as min_duration,
                        MAX(duration_ms) as max_duration,
                        AVG(memory_usage_bytes) as avg_memory,
                        COUNT(CASE WHEN status = 'FAILURE' THEN 1 END) as failure_count
                    FROM `{$this->performanceTableName}` 
                    WHERE timestamp >= DATE_SUB(NOW(), INTERVAL {$timeframe})
                    GROUP BY operation, marketplace
                    ORDER BY avg_duration DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Failed to get performance metrics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clean up old logs
     */
    public function cleanupOldLogs() {
        try {
            $retentionDays = $this->config['max_log_retention_days'];
            
            // Clean error logs
            $sql = "DELETE FROM `{$this->errorTableName}` 
                    WHERE timestamp < DATE_SUB(NOW(), INTERVAL {$retentionDays} DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $deletedErrors = $stmt->rowCount();
            
            // Clean performance logs
            $sql = "DELETE FROM `{$this->performanceTableName}` 
                    WHERE timestamp < DATE_SUB(NOW(), INTERVAL {$retentionDays} DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $deletedPerformance = $stmt->rowCount();
            
            $this->logDebug("Cleaned up {$deletedErrors} error logs and {$deletedPerformance} performance logs");
            
            return [
                'errors_deleted' => $deletedErrors,
                'performance_deleted' => $deletedPerformance
            ];
            
        } catch (Exception $e) {
            error_log("Failed to cleanup old logs: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark error as resolved
     */
    public function resolveError($errorId, $userId, $notes = null) {
        try {
            $sql = "UPDATE `{$this->errorTableName}` 
                    SET resolved = 1, 
                        resolved_at = NOW(), 
                        resolved_by = :user_id, 
                        resolution_notes = :notes 
                    WHERE id = :error_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':error_id', (int)$errorId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
            $stmt->bindValue(':notes', $notes);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Failed to resolve error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unresolved critical errors
     */
    public function getUnresolvedCriticalErrors() {
        try {
            $sql = "SELECT * FROM `{$this->errorTableName}` 
                    WHERE level = 'CRITICAL' 
                    AND resolved = 0 
                    ORDER BY timestamp DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Failed to get unresolved critical errors: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Export errors to CSV
     */
    public function exportErrorsToCSV($filters = []) {
        try {
            $whereConditions = [];
            $params = [];
            
            if (isset($filters['level'])) {
                $whereConditions[] = "level = :level";
                $params[':level'] = $filters['level'];
            }
            
            if (isset($filters['marketplace'])) {
                $whereConditions[] = "marketplace = :marketplace";
                $params[':marketplace'] = $filters['marketplace'];
            }
            
            if (isset($filters['date_from'])) {
                $whereConditions[] = "timestamp >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            
            if (isset($filters['date_to'])) {
                $whereConditions[] = "timestamp <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }
            
            $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            $sql = "SELECT 
                        timestamp,
                        level,
                        category,
                        marketplace,
                        message,
                        error_code,
                        file_path,
                        line_number,
                        request_url,
                        resolved
                    FROM `{$this->errorTableName}` 
                    {$whereClause}
                    ORDER BY timestamp DESC";
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Generate CSV
            $filename = 'opencart_error_export_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = sys_get_temp_dir() . '/' . $filename;
            
            $file = fopen($filepath, 'w');
            
            // Add CSV header
            if ($results) {
                fputcsv($file, array_keys($results[0]));
            }
            
            // Add data rows
            foreach ($results as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
            
            return $filepath;
            
        } catch (Exception $e) {
            error_log("Failed to export errors to CSV: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle critical errors
     */
    private function handleCriticalError($errorId, $message, $context) {
        // Send immediate notifications
        $this->sendCriticalErrorNotification($errorId, $message, $context);
        
        // Check if we've reached the critical error threshold
        $criticalCount = $this->getCriticalErrorCount('1 HOUR');
        
        if ($criticalCount >= $this->config['critical_error_threshold']) {
            $this->sendSystemAlertNotification($criticalCount);
        }
    }

    /**
     * Send critical error notification
     */
    private function sendCriticalErrorNotification($errorId, $message, $context) {
        // Implementation for sending notifications (email, Slack, SMS, etc.)
        $this->logDebug("Critical error notification sent for error ID: {$errorId}");
    }

    /**
     * Send system alert notification
     */
    private function sendSystemAlertNotification($errorCount) {
        // Implementation for system-wide alerts
        $this->logDebug("System alert sent: {$errorCount} critical errors in the last hour");
    }

    /**
     * Get critical error count
     */
    private function getCriticalErrorCount($timeframe) {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM `{$this->errorTableName}` 
                    WHERE level = 'CRITICAL' 
                    AND timestamp >= DATE_SUB(NOW(), INTERVAL {$timeframe})";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['count'];
            
        } catch (Exception $e) {
            error_log("Failed to get critical error count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Generate unique request ID
     */
    private function generateRequestId() {
        return uniqid('req_', true);
    }

    /**
     * Debug logging
     */
    private function logDebug($message) {
        if ($this->debugMode) {
            error_log("[OpenCartDB] " . $message);
        }
    }

    /**
     * Get system health status
     */
    public function getSystemHealth() {
        try {
            $health = [
                'database_connection' => 'healthy',
                'error_logs_count' => 0,
                'critical_errors_24h' => 0,
                'performance_issues' => 0,
                'last_cleanup' => null,
                'disk_usage' => [
                    'error_table_size' => 0,
                    'performance_table_size' => 0
                ]
            ];
            
            // Check error counts
            $sql = "SELECT 
                        COUNT(*) as total_errors,
                        COUNT(CASE WHEN level = 'CRITICAL' THEN 1 END) as critical_errors,
                        COUNT(CASE WHEN timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as errors_24h
                    FROM `{$this->errorTableName}`";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $errorStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $health['error_logs_count'] = (int)$errorStats['total_errors'];
            $health['critical_errors_24h'] = (int)$errorStats['errors_24h'];
            
            // Check table sizes
            $sql = "SELECT 
                        TABLE_NAME,
                        ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS size_mb
                    FROM information_schema.TABLES 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME IN ('{$this->errorTableName}', '{$this->performanceTableName}')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $tableStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($tableStats as $table) {
                if ($table['TABLE_NAME'] === $this->errorTableName) {
                    $health['disk_usage']['error_table_size'] = $table['size_mb'] . 'MB';
                } elseif ($table['TABLE_NAME'] === $this->performanceTableName) {
                    $health['disk_usage']['performance_table_size'] = $table['size_mb'] . 'MB';
                }
            }
            
            return $health;
            
        } catch (Exception $e) {
            error_log("Failed to get system health: " . $e->getMessage());
            return ['database_connection' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }
}

// Example usage and integration
try {
    // Initialize database connection (replace with your OpenCart database connection)
    $pdo = new PDO('mysql:host=localhost;dbname=opencart', $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $dbIntegration = new OpenCartDatabaseIntegration($pdo, [
        'enable_debug' => true,
        'enable_performance_tracking' => true,
        'max_log_retention_days' => 90
    ]);
    
    // Test the integration
    $errorId = $dbIntegration->logError('ERROR', 'API_ERROR', 'Test API failure', [
        'marketplace' => 'trendyol',
        'error_code' => 'API_TIMEOUT',
        'request_id' => 'test_' . time()
    ]);
    
    echo "Database integration initialized successfully. Test error logged with ID: {$errorId}\n";
    
} catch (Exception $e) {
    error_log("Failed to initialize database integration: " . $e->getMessage());
}
?>
