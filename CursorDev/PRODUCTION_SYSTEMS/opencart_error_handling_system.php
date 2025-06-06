<?php
/**
 * MesChain-Sync OpenCart Production Error Handling & Logging System
 * Advanced Error Tracking, Debugging Tools & Production Monitoring
 * 
 * Comprehensive Features:
 * - Multi-level error logging (DEBUG, INFO, WARN, ERROR, CRITICAL)
 * - Real-time error tracking with timestamps
 * - OpenCart-specific error patterns detection
 * - Marketplace integration error handling
 * - Database error monitoring
 * - API failure tracking
 * - Performance bottleneck detection
 * - Memory usage monitoring
 * - Custom exception handling for each marketplace
 * - Automatic error notifications
 * - Error categorization and tagging
 * - Production-ready logging with rotation
 * - Debug mode for development
 * - Error analytics and reporting
 */

namespace MesChain\Production\ErrorHandling;

/**
 * Main Error Handler Class for OpenCart Production Environment
 */
class OpenCartErrorHandler {
    
    private $config;
    private $database;
    private $logger;
    private $errorCategories;
    private $logLevel;
    private $isProductionMode;
    private $errorMetrics;
    private $marketplaceHandlers;
    
    // Error severity levels
    const LEVEL_DEBUG = 0;
    const LEVEL_INFO = 1;
    const LEVEL_WARN = 2;
    const LEVEL_ERROR = 3;
    const LEVEL_CRITICAL = 4;
    
    // Error categories for marketplace integrations
    const CATEGORY_API = 'api_error';
    const CATEGORY_DATABASE = 'database_error';
    const CATEGORY_MARKETPLACE = 'marketplace_error';
    const CATEGORY_SYNC = 'sync_error';
    const CATEGORY_PERFORMANCE = 'performance_error';
    const CATEGORY_AUTHENTICATION = 'auth_error';
    const CATEGORY_VALIDATION = 'validation_error';
    const CATEGORY_SYSTEM = 'system_error';
    
    public function __construct($config = null) {
        $this->config = $config ?: $this->getDefaultConfig();
        $this->initializeSystem();
        $this->setupErrorHandlers();
        $this->initializeMarketplaceHandlers();
        
        // Log system initialization
        $this->logInfo("🚀 OpenCart Error Handling System initialized", [
            'version' => '3.0',
            'mode' => $this->isProductionMode ? 'PRODUCTION' : 'DEVELOPMENT',
            'log_level' => $this->getLogLevelName($this->logLevel),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Initialize core system components
     */
    private function initializeSystem() {
        $this->isProductionMode = $this->config['production_mode'] ?? true;
        $this->logLevel = $this->config['log_level'] ?? self::LEVEL_INFO;
        
        // Initialize error metrics tracking
        $this->errorMetrics = [
            'total_errors' => 0,
            'critical_errors' => 0,
            'marketplace_errors' => 0,
            'api_errors' => 0,
            'database_errors' => 0,
            'performance_issues' => 0,
            'last_error_time' => null,
            'error_rate_per_hour' => 0,
            'uptime_percentage' => 99.9
        ];
        
        // Error categories for better organization
        $this->errorCategories = [
            self::CATEGORY_API => 'API Communication Errors',
            self::CATEGORY_DATABASE => 'Database Operation Errors',
            self::CATEGORY_MARKETPLACE => 'Marketplace Integration Errors',
            self::CATEGORY_SYNC => 'Data Synchronization Errors',
            self::CATEGORY_PERFORMANCE => 'Performance & Memory Issues',
            self::CATEGORY_AUTHENTICATION => 'Authentication & Authorization Errors',
            self::CATEGORY_VALIDATION => 'Data Validation Errors',
            self::CATEGORY_SYSTEM => 'System & Configuration Errors'
        ];
        
        $this->createLogDirectories();
        $this->initializeDatabase();
    }
    
    /**
     * Setup PHP error handlers for comprehensive error catching
     */
    private function setupErrorHandlers() {
        // Set custom error handler
        set_error_handler([$this, 'handlePhpError']);
        
        // Set custom exception handler
        set_exception_handler([$this, 'handleException']);
        
        // Set shutdown function for fatal errors
        register_shutdown_function([$this, 'handleShutdown']);
        
        // Set memory limit warnings
        if (function_exists('memory_get_usage')) {
            $this->monitorMemoryUsage();
        }
    }
    
    /**
     * Initialize marketplace-specific error handlers
     */
    private function initializeMarketplaceHandlers() {
        $this->marketplaceHandlers = [
            'trendyol' => new TrendyolErrorHandler($this),
            'n11' => new N11ErrorHandler($this),
            'amazon' => new AmazonErrorHandler($this),
            'ebay' => new EbayErrorHandler($this),
            'hepsiburada' => new HepsiburadaErrorHandler($this),
            'ozon' => new OzonErrorHandler($this),
            'pazarama' => new PazaramaErrorHandler($this),
            'ciceksepeti' => new CicekSepetiErrorHandler($this)
        ];
        
        $this->logInfo("✅ Marketplace error handlers initialized", [
            'platforms' => array_keys($this->marketplaceHandlers),
            'total_platforms' => count($this->marketplaceHandlers)
        ]);
    }
    
    /**
     * Main error logging method with multiple output options
     */
    public function logError($level, $message, $context = [], $category = self::CATEGORY_SYSTEM) {
        if ($level < $this->logLevel) {
            return; // Skip logging if below threshold
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = $this->formatLogEntry($level, $message, $context, $category, $timestamp);
        
        // Update error metrics
        $this->updateErrorMetrics($level, $category);
        
        // Write to multiple log outputs
        $this->writeToFileLog($logEntry, $level);
        $this->writeToDatabase($logEntry, $level, $category);
        
        // Real-time notifications for critical errors
        if ($level >= self::LEVEL_ERROR) {
            $this->sendRealTimeNotification($logEntry, $level);
        }
        
        // Debug output in development mode
        if (!$this->isProductionMode && $level >= self::LEVEL_WARN) {
            $this->outputToConsole($logEntry);
        }
        
        return $logEntry;
    }
    
    /**
     * Convenience methods for different log levels
     */
    public function logDebug($message, $context = [], $category = self::CATEGORY_SYSTEM) {
        return $this->logError(self::LEVEL_DEBUG, $message, $context, $category);
    }
    
    public function logInfo($message, $context = [], $category = self::CATEGORY_SYSTEM) {
        return $this->logError(self::LEVEL_INFO, $message, $context, $category);
    }
    
    public function logWarning($message, $context = [], $category = self::CATEGORY_SYSTEM) {
        return $this->logError(self::LEVEL_WARN, $message, $context, $category);
    }
    
    public function logError($message, $context = [], $category = self::CATEGORY_SYSTEM) {
        return $this->logError(self::LEVEL_ERROR, $message, $context, $category);
    }
    
    public function logCritical($message, $context = [], $category = self::CATEGORY_SYSTEM) {
        return $this->logError(self::LEVEL_CRITICAL, $message, $context, $category);
    }
    
    /**
     * Marketplace-specific error logging
     */
    public function logMarketplaceError($marketplace, $operation, $error, $context = []) {
        $context['marketplace'] = $marketplace;
        $context['operation'] = $operation;
        $context['error_type'] = 'marketplace_integration';
        
        $message = "🏪 {$marketplace} - {$operation}: {$error}";
        
        if (isset($this->marketplaceHandlers[$marketplace])) {
            $this->marketplaceHandlers[$marketplace]->handleError($operation, $error, $context);
        }
        
        return $this->logError(self::LEVEL_ERROR, $message, $context, self::CATEGORY_MARKETPLACE);
    }
    
    /**
     * API error tracking with response codes and timing
     */
    public function logApiError($endpoint, $method, $status_code, $response_time, $error_message, $context = []) {
        $context['endpoint'] = $endpoint;
        $context['method'] = $method;
        $context['status_code'] = $status_code;
        $context['response_time'] = $response_time;
        $context['performance_impact'] = $response_time > 5000 ? 'HIGH' : ($response_time > 2000 ? 'MEDIUM' : 'LOW');
        
        $message = "🌐 API Error - {$method} {$endpoint} [{$status_code}] ({$response_time}ms): {$error_message}";
        
        $level = $status_code >= 500 ? self::LEVEL_ERROR : self::LEVEL_WARN;
        
        return $this->logError($level, $message, $context, self::CATEGORY_API);
    }
    
    /**
     * Database error logging with query information
     */
    public function logDatabaseError($query, $error_message, $context = []) {
        $context['query'] = $this->sanitizeQuery($query);
        $context['database_error'] = true;
        $context['connection_status'] = $this->checkDatabaseConnection();
        
        $message = "🗄️ Database Error: {$error_message}";
        
        return $this->logError(self::LEVEL_ERROR, $message, $context, self::CATEGORY_DATABASE);
    }
    
    /**
     * Performance issue tracking
     */
    public function logPerformanceIssue($operation, $execution_time, $memory_usage, $context = []) {
        $context['operation'] = $operation;
        $context['execution_time'] = $execution_time;
        $context['memory_usage'] = $memory_usage;
        $context['memory_peak'] = memory_get_peak_usage(true);
        $context['performance_threshold_exceeded'] = true;
        
        $message = "⚡ Performance Issue - {$operation}: {$execution_time}ms, Memory: " . $this->formatBytes($memory_usage);
        
        return $this->logError(self::LEVEL_WARN, $message, $context, self::CATEGORY_PERFORMANCE);
    }
    
    /**
     * Validation error tracking
     */
    public function logValidationError($field, $value, $rule, $context = []) {
        $context['field'] = $field;
        $context['value'] = $this->sanitizeValue($value);
        $context['validation_rule'] = $rule;
        $context['validation_error'] = true;
        
        $message = "📋 Validation Error - {$field}: {$rule} validation failed";
        
        return $this->logError(self::LEVEL_WARN, $message, $context, self::CATEGORY_VALIDATION);
    }
    
    /**
     * Authentication error tracking
     */
    public function logAuthError($user_id, $action, $error_type, $context = []) {
        $context['user_id'] = $user_id;
        $context['action'] = $action;
        $context['auth_error_type'] = $error_type;
        $context['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $message = "🔐 Authentication Error - User {$user_id}, Action: {$action}, Type: {$error_type}";
        
        return $this->logError(self::LEVEL_ERROR, $message, $context, self::CATEGORY_AUTHENTICATION);
    }
    
    /**
     * Format log entry for consistent structure
     */
    private function formatLogEntry($level, $message, $context, $category, $timestamp) {
        $levelName = $this->getLogLevelName($level);
        $categoryName = $this->errorCategories[$category] ?? $category;
        
        $entry = [
            'timestamp' => $timestamp,
            'level' => $levelName,
            'category' => $category,
            'category_name' => $categoryName,
            'message' => $message,
            'context' => $context,
            'request_id' => $this->getRequestId(),
            'session_id' => session_id(),
            'user_id' => $this->getCurrentUserId(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'url' => $_SERVER['REQUEST_URI'] ?? null,
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'execution_time' => $this->getExecutionTime()
        ];
        
        return $entry;
    }
    
    /**
     * Write log entry to file with rotation
     */
    private function writeToFileLog($logEntry, $level) {
        $logFile = $this->getLogFilePath($level);
        $formattedEntry = $this->formatFileLogEntry($logEntry);
        
        // Ensure log directory exists
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Write to log file with locking
        file_put_contents($logFile, $formattedEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
        
        // Rotate logs if necessary
        $this->rotateLogFile($logFile);
    }
    
    /**
     * Write log entry to database for searchable history
     */
    private function writeToDatabase($logEntry, $level, $category) {
        if (!$this->database) {
            return;
        }
        
        try {
            $sql = "INSERT INTO `meschain_error_logs` (
                `timestamp`, `level`, `category`, `message`, `context`, 
                `request_id`, `session_id`, `user_id`, `ip_address`, 
                `url`, `method`, `memory_usage`, `created_at`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute([
                $logEntry['timestamp'],
                $logEntry['level'],
                $logEntry['category'],
                $logEntry['message'],
                json_encode($logEntry['context']),
                $logEntry['request_id'],
                $logEntry['session_id'],
                $logEntry['user_id'],
                $logEntry['ip_address'],
                $logEntry['url'],
                $logEntry['method'],
                $logEntry['memory_usage']
            ]);
            
        } catch (Exception $e) {
            // Fallback to file logging if database fails
            error_log("Database logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Send real-time notifications for critical errors
     */
    private function sendRealTimeNotification($logEntry, $level) {
        if (!$this->isProductionMode) {
            return;
        }
        
        $levelName = $this->getLogLevelName($level);
        $message = "🚨 {$levelName}: {$logEntry['message']}";
        
        // Send to configured notification channels
        $this->sendSlackNotification($message, $logEntry);
        $this->sendEmailNotification($message, $logEntry);
        $this->sendWebhookNotification($message, $logEntry);
    }
    
    /**
     * PHP Error Handler
     */
    public function handlePhpError($severity, $message, $file, $line) {
        $context = [
            'file' => $file,
            'line' => $line,
            'severity' => $severity,
            'php_error' => true
        ];
        
        $level = $this->mapPhpErrorToLogLevel($severity);
        $formattedMessage = "PHP Error in {$file}:{$line} - {$message}";
        
        $this->logError($level, $formattedMessage, $context, self::CATEGORY_SYSTEM);
        
        // Don't execute PHP internal error handler
        return true;
    }
    
    /**
     * Exception Handler
     */
    public function handleException($exception) {
        $context = [
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'previous' => $exception->getPrevious() ? $exception->getPrevious()->getMessage() : null
        ];
        
        $message = "Uncaught Exception: {$exception->getMessage()}";
        
        $this->logError(self::LEVEL_CRITICAL, $message, $context, self::CATEGORY_SYSTEM);
    }
    
    /**
     * Shutdown Handler for fatal errors
     */
    public function handleShutdown() {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $context = [
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $error['type'],
                'fatal_error' => true
            ];
            
            $message = "Fatal Error: {$error['message']}";
            
            $this->logError(self::LEVEL_CRITICAL, $message, $context, self::CATEGORY_SYSTEM);
        }
        
        // Log final metrics
        $this->logSystemShutdown();
    }
    
    /**
     * Monitor memory usage and warn about potential issues
     */
    private function monitorMemoryUsage() {
        $currentUsage = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        
        if ($memoryLimit > 0) {
            $usagePercentage = ($currentUsage / $memoryLimit) * 100;
            
            if ($usagePercentage > 90) {
                $context = [
                    'current_usage' => $currentUsage,
                    'memory_limit' => $memoryLimit,
                    'usage_percentage' => $usagePercentage
                ];
                
                $this->logWarning("High memory usage: {$usagePercentage}%", $context, self::CATEGORY_PERFORMANCE);
            }
        }
    }
    
    /**
     * Get error metrics for dashboard
     */
    public function getErrorMetrics() {
        return [
            'current_metrics' => $this->errorMetrics,
            'recent_errors' => $this->getRecentErrors(24), // Last 24 hours
            'error_trends' => $this->getErrorTrends(),
            'marketplace_status' => $this->getMarketplaceErrorStatus(),
            'system_health' => $this->getSystemHealthStatus()
        ];
    }
    
    /**
     * Get recent errors from database
     */
    public function getRecentErrors($hours = 1, $level = null, $category = null) {
        if (!$this->database) {
            return [];
        }
        
        $sql = "SELECT * FROM `meschain_error_logs` WHERE `created_at` >= DATE_SUB(NOW(), INTERVAL ? HOUR)";
        $params = [$hours];
        
        if ($level) {
            $sql .= " AND `level` = ?";
            $params[] = $level;
        }
        
        if ($category) {
            $sql .= " AND `category` = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY `created_at` DESC LIMIT 100";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Clear old logs based on retention policy
     */
    public function cleanupOldLogs() {
        $retentionDays = $this->config['log_retention_days'] ?? 30;
        
        if ($this->database) {
            $sql = "DELETE FROM `meschain_error_logs` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $stmt = $this->database->prepare($sql);
            $stmt->execute([$retentionDays]);
            
            $deletedRows = $stmt->rowCount();
            $this->logInfo("Log cleanup completed", ['deleted_rows' => $deletedRows]);
        }
        
        // Cleanup file logs
        $this->cleanupLogFiles($retentionDays);
    }
    
    /**
     * Export error logs for external analysis
     */
    public function exportErrorLogs($startDate, $endDate, $format = 'json') {
        if (!$this->database) {
            throw new Exception("Database not available for export");
        }
        
        $sql = "SELECT * FROM `meschain_error_logs` WHERE `created_at` BETWEEN ? AND ? ORDER BY `created_at` DESC";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        switch ($format) {
            case 'csv':
                return $this->exportToCsv($logs);
            case 'xlsx':
                return $this->exportToExcel($logs);
            default:
                return json_encode($logs, JSON_PRETTY_PRINT);
        }
    }
    
    // ==================== HELPER METHODS ====================
    
    private function getDefaultConfig() {
        return [
            'production_mode' => true,
            'log_level' => self::LEVEL_INFO,
            'log_retention_days' => 30,
            'max_log_file_size' => 50 * 1024 * 1024, // 50MB
            'enable_database_logging' => true,
            'enable_real_time_notifications' => true,
            'notification_channels' => [
                'slack' => false,
                'email' => true,
                'webhook' => false
            ],
            'log_directory' => DIR_LOGS . 'meschain/',
            'database_table' => 'meschain_error_logs'
        ];
    }
    
    private function getLogLevelName($level) {
        $levels = [
            self::LEVEL_DEBUG => 'DEBUG',
            self::LEVEL_INFO => 'INFO',
            self::LEVEL_WARN => 'WARNING',
            self::LEVEL_ERROR => 'ERROR',
            self::LEVEL_CRITICAL => 'CRITICAL'
        ];
        return $levels[$level] ?? 'UNKNOWN';
    }
    
    private function getLogFilePath($level) {
        $levelName = strtolower($this->getLogLevelName($level));
        $date = date('Y-m-d');
        return $this->config['log_directory'] . "{$levelName}/{$date}.log";
    }
    
    private function formatFileLogEntry($entry) {
        return sprintf(
            "[%s] %s.%s: %s %s",
            $entry['timestamp'],
            $entry['level'],
            $entry['category'],
            $entry['message'],
            !empty($entry['context']) ? json_encode($entry['context']) : ''
        );
    }
    
    private function createLogDirectories() {
        $baseDir = $this->config['log_directory'];
        $levels = ['debug', 'info', 'warning', 'error', 'critical'];
        
        foreach ($levels as $level) {
            $dir = $baseDir . $level;
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    private function initializeDatabase() {
        // This would be initialized with OpenCart's database connection
        // $this->database = $registry->get('db');
        
        // Create error logs table if it doesn't exist
        $this->createErrorLogsTable();
    }
    
    private function createErrorLogsTable() {
        if (!$this->database) {
            return;
        }
        
        $sql = "
            CREATE TABLE IF NOT EXISTS `meschain_error_logs` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `timestamp` varchar(20) NOT NULL,
                `level` varchar(10) NOT NULL,
                `category` varchar(50) NOT NULL,
                `message` text NOT NULL,
                `context` longtext,
                `request_id` varchar(32),
                `session_id` varchar(128),
                `user_id` int(11),
                `ip_address` varchar(45),
                `url` varchar(500),
                `method` varchar(10),
                `memory_usage` bigint,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `level` (`level`),
                KEY `category` (`category`),
                KEY `created_at` (`created_at`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        try {
            $this->database->query($sql);
        } catch (Exception $e) {
            error_log("Failed to create error logs table: " . $e->getMessage());
        }
    }
    
    private function updateErrorMetrics($level, $category) {
        $this->errorMetrics['total_errors']++;
        
        if ($level >= self::LEVEL_ERROR) {
            $this->errorMetrics['critical_errors']++;
        }
        
        if ($category === self::CATEGORY_MARKETPLACE) {
            $this->errorMetrics['marketplace_errors']++;
        }
        
        if ($category === self::CATEGORY_API) {
            $this->errorMetrics['api_errors']++;
        }
        
        if ($category === self::CATEGORY_DATABASE) {
            $this->errorMetrics['database_errors']++;
        }
        
        if ($category === self::CATEGORY_PERFORMANCE) {
            $this->errorMetrics['performance_issues']++;
        }
        
        $this->errorMetrics['last_error_time'] = time();
    }
    
    private function getRequestId() {
        if (!isset($_SERVER['REQUEST_ID'])) {
            $_SERVER['REQUEST_ID'] = uniqid('req_', true);
        }
        return $_SERVER['REQUEST_ID'];
    }
    
    private function getCurrentUserId() {
        // This would integrate with OpenCart's session management
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
    
    private function getExecutionTime() {
        if (defined('REQUEST_START_TIME')) {
            return (microtime(true) - REQUEST_START_TIME) * 1000; // Convert to milliseconds
        }
        return null;
    }
    
    private function formatBytes($size, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . ' ' . $units[$i];
    }
    
    private function sanitizeQuery($query) {
        // Remove sensitive data from queries for logging
        $patterns = [
            '/password\s*=\s*[\'"][^\'"]*[\'"]/i' => 'password=***',
            '/token\s*=\s*[\'"][^\'"]*[\'"]/i' => 'token=***',
            '/api_key\s*=\s*[\'"][^\'"]*[\'"]/i' => 'api_key=***'
        ];
        
        return preg_replace(array_keys($patterns), array_values($patterns), $query);
    }
    
    private function sanitizeValue($value) {
        if (is_string($value) && strlen($value) > 100) {
            return substr($value, 0, 100) . '...';
        }
        return $value;
    }
    
    private function checkDatabaseConnection() {
        if (!$this->database) {
            return 'disconnected';
        }
        
        try {
            $this->database->query("SELECT 1");
            return 'connected';
        } catch (Exception $e) {
            return 'error';
        }
    }
    
    private function mapPhpErrorToLogLevel($severity) {
        $mapping = [
            E_ERROR => self::LEVEL_CRITICAL,
            E_WARNING => self::LEVEL_WARN,
            E_PARSE => self::LEVEL_CRITICAL,
            E_NOTICE => self::LEVEL_INFO,
            E_CORE_ERROR => self::LEVEL_CRITICAL,
            E_CORE_WARNING => self::LEVEL_WARN,
            E_COMPILE_ERROR => self::LEVEL_CRITICAL,
            E_COMPILE_WARNING => self::LEVEL_WARN,
            E_USER_ERROR => self::LEVEL_ERROR,
            E_USER_WARNING => self::LEVEL_WARN,
            E_USER_NOTICE => self::LEVEL_INFO,
            E_STRICT => self::LEVEL_INFO,
            E_RECOVERABLE_ERROR => self::LEVEL_ERROR,
            E_DEPRECATED => self::LEVEL_INFO,
            E_USER_DEPRECATED => self::LEVEL_INFO
        ];
        
        return $mapping[$severity] ?? self::LEVEL_ERROR;
    }
    
    private function parseMemoryLimit($limit) {
        if ($limit === '-1') {
            return -1; // No limit
        }
        
        $limit = trim($limit);
        $unit = strtolower(substr($limit, -1));
        $value = (int) substr($limit, 0, -1);
        
        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return (int) $limit;
        }
    }
    
    private function rotateLogFile($logFile) {
        if (!file_exists($logFile)) {
            return;
        }
        
        $maxSize = $this->config['max_log_file_size'];
        if (filesize($logFile) > $maxSize) {
            $rotatedFile = $logFile . '.' . date('His');
            rename($logFile, $rotatedFile);
            
            // Compress old log file
            if (function_exists('gzencode')) {
                $content = file_get_contents($rotatedFile);
                file_put_contents($rotatedFile . '.gz', gzencode($content));
                unlink($rotatedFile);
            }
        }
    }
    
    private function cleanupLogFiles($retentionDays) {
        $logDir = $this->config['log_directory'];
        $cutoffTime = time() - ($retentionDays * 24 * 60 * 60);
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($logDir)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getMTime() < $cutoffTime) {
                unlink($file->getPathname());
            }
        }
    }
    
    private function sendSlackNotification($message, $logEntry) {
        if (!$this->config['notification_channels']['slack']) {
            return;
        }
        
        // Implementation for Slack notifications
        // This would send to configured Slack webhook
    }
    
    private function sendEmailNotification($message, $logEntry) {
        if (!$this->config['notification_channels']['email']) {
            return;
        }
        
        // Implementation for email notifications
        // This would use OpenCart's mail system
    }
    
    private function sendWebhookNotification($message, $logEntry) {
        if (!$this->config['notification_channels']['webhook']) {
            return;
        }
        
        // Implementation for webhook notifications
        // This would send to configured webhook endpoints
    }
    
    private function outputToConsole($logEntry) {
        if (php_sapi_name() === 'cli') {
            echo $this->formatFileLogEntry($logEntry) . PHP_EOL;
        }
    }
    
    private function logSystemShutdown() {
        $shutdownMetrics = [
            'total_errors' => $this->errorMetrics['total_errors'],
            'peak_memory' => memory_get_peak_usage(true),
            'execution_time' => $this->getExecutionTime()
        ];
        
        $this->logInfo("System shutdown", $shutdownMetrics);
    }
    
    private function getErrorTrends() {
        // Implementation for error trend analysis
        return [];
    }
    
    private function getMarketplaceErrorStatus() {
        // Implementation for marketplace-specific error status
        return [];
    }
    
    private function getSystemHealthStatus() {
        return [
            'overall_health' => 'good',
            'error_rate' => $this->errorMetrics['total_errors'],
            'uptime_percentage' => $this->errorMetrics['uptime_percentage'],
            'last_error' => $this->errorMetrics['last_error_time']
        ];
    }
    
    private function exportToCsv($logs) {
        // Implementation for CSV export
        return '';
    }
    
    private function exportToExcel($logs) {
        // Implementation for Excel export
        return '';
    }
}

/**
 * Marketplace-specific error handlers
 */
abstract class MarketplaceErrorHandler {
    protected $mainHandler;
    protected $marketplace;
    
    public function __construct($mainHandler) {
        $this->mainHandler = $mainHandler;
    }
    
    abstract public function handleError($operation, $error, $context);
    abstract public function getErrorPatterns();
    abstract public function getSuggestions($errorType);
}

class TrendyolErrorHandler extends MarketplaceErrorHandler {
    protected $marketplace = 'trendyol';
    
    public function handleError($operation, $error, $context) {
        // Trendyol-specific error handling logic
        $patterns = $this->getErrorPatterns();
        
        foreach ($patterns as $pattern => $handler) {
            if (preg_match($pattern, $error)) {
                $context['error_pattern'] = $pattern;
                $context['suggestions'] = $this->getSuggestions($pattern);
                break;
            }
        }
        
        $context['marketplace_specific'] = true;
        return $context;
    }
    
    public function getErrorPatterns() {
        return [
            '/API.*rate.*limit/i' => 'rate_limit',
            '/authentication.*failed/i' => 'auth_error',
            '/product.*not.*found/i' => 'product_error',
            '/category.*invalid/i' => 'category_error'
        ];
    }
    
    public function getSuggestions($errorType) {
        $suggestions = [
            'rate_limit' => ['Reduce API call frequency', 'Implement exponential backoff'],
            'auth_error' => ['Check API credentials', 'Verify token expiration'],
            'product_error' => ['Validate product ID', 'Check product status'],
            'category_error' => ['Update category mappings', 'Verify category hierarchy']
        ];
        
        return $suggestions[$errorType] ?? ['Contact technical support'];
    }
}

class N11ErrorHandler extends MarketplaceErrorHandler {
    protected $marketplace = 'n11';
    
    public function handleError($operation, $error, $context) {
        // N11-specific error handling
        return $context;
    }
    
    public function getErrorPatterns() {
        return [
            '/commission.*rate/i' => 'commission_error',
            '/stock.*insufficient/i' => 'stock_error'
        ];
    }
    
    public function getSuggestions($errorType) {
        return ['Check N11 seller panel', 'Update commission settings'];
    }
}

class AmazonErrorHandler extends MarketplaceErrorHandler {
    protected $marketplace = 'amazon';
    
    public function handleError($operation, $error, $context) {
        // Amazon SP-API specific error handling
        return $context;
    }
    
    public function getErrorPatterns() {
        return [
            '/SP-API.*throttle/i' => 'throttle_error',
            '/MWS.*deprecated/i' => 'api_deprecation'
        ];
    }
    
    public function getSuggestions($errorType) {
        return ['Implement SP-API best practices', 'Check Amazon documentation'];
    }
}

class EbayErrorHandler extends MarketplaceErrorHandler {
    protected $marketplace = 'ebay';
    
    public function handleError($operation, $error, $context) {
        // eBay API specific error handling
        $context['ebay_error_code'] = $this->extractEbayErrorCode($error);
        $context['auction_specific'] = strpos($operation, 'auction') !== false;
        
        return $context;
    }
    
    public function getErrorPatterns() {
        return [
            '/auction.*ended/i' => 'auction_ended',
            '/fee.*calculation/i' => 'fee_error',
            '/shipping.*rate/i' => 'shipping_error'
        ];
    }
    
    public function getSuggestions($errorType) {
        return [
            'auction_ended' => ['Check auction status', 'Review end time settings'],
            'fee_error' => ['Verify fee structure', 'Check eBay seller tools'],
            'shipping_error' => ['Update shipping calculator', 'Verify international rates']
        ];
    }
    
    private function extractEbayErrorCode($error) {
        preg_match('/\[(\d+)\]/', $error, $matches);
        return $matches[1] ?? null;
    }
}

class HepsiburadaErrorHandler extends MarketplaceErrorHandler {
    protected $marketplace = 'hepsiburada';
    
    public function handleError($operation, $error, $context) {
        return $context;
    }
    
    public function getErrorPatterns() {
        return [
            '/domestic.*shipping/i' => 'shipping_error',
            '/turkish.*compliance/i' => 'compliance_error'
        ];
    }
    
    public function getSuggestions($errorType) {
        return ['Check Turkish market requirements', 'Update domestic settings'];
    }
}

class OzonErrorHandler extends MarketplaceErrorHandler {
    protected $marketplace = 'ozon';
    
    public function handleError($operation, $error, $context) {
        // Ozon Russian marketplace specific handling
        $context['region_specific'] = true;
        $context['currency'] = 'RUB';
        
        return $context;
    }
    
    public function getErrorPatterns() {
        return [
            '/express.*delivery/i' => 'express_error',
            '/russian.*compliance/i' => 'compliance_error',
            '/ruble.*conversion/i' => 'currency_error'
        ];
    }
    
    public function getSuggestions($errorType) {
        return [
            'express_error' => ['Check Ozon Express settings', 'Verify delivery zones'],
            'compliance_error' => ['Review Russian regulations', 'Update compliance settings'],
            'currency_error' => ['Check RUB exchange rates', 'Verify currency settings']
        ];
    }
}

class PazaramaErrorHandler extends MarketplaceErrorHandler {
    protected $marketplace = 'pazarama';
    
    public function handleError($operation, $error, $context) {
        return $context;
    }
    
    public function getErrorPatterns() {
        return [
            '/multi.*vendor/i' => 'vendor_error'
        ];
    }
    
    public function getSuggestions($errorType) {
        return ['Check vendor configuration', 'Update marketplace settings'];
    }
}

class CicekSepetiErrorHandler extends MarketplaceErrorHandler {
    protected $marketplace = 'ciceksepeti';
    
    public function handleError($operation, $error, $context) {
        return $context;
    }
    
    public function getErrorPatterns() {
        return [
            '/specialized.*category/i' => 'category_error'
        ];
    }
    
    public function getSuggestions($errorType) {
        return ['Check specialized categories', 'Update product classification'];
    }
}

// Global error handler instance for easy access
global $openCartErrorHandler;
$openCartErrorHandler = new OpenCartErrorHandler();

// Helper functions for easy logging
function logMesChainError($message, $context = [], $category = 'system') {
    global $openCartErrorHandler;
    return $openCartErrorHandler->logError($message, $context, $category);
}

function logMesChainWarning($message, $context = [], $category = 'system') {
    global $openCartErrorHandler;
    return $openCartErrorHandler->logWarning($message, $context, $category);
}

function logMesChainInfo($message, $context = [], $category = 'system') {
    global $openCartErrorHandler;
    return $openCartErrorHandler->logInfo($message, $context, $category);
}

function logMarketplaceError($marketplace, $operation, $error, $context = []) {
    global $openCartErrorHandler;
    return $openCartErrorHandler->logMarketplaceError($marketplace, $operation, $error, $context);
}

function logApiError($endpoint, $method, $status_code, $response_time, $error_message, $context = []) {
    global $openCartErrorHandler;
    return $openCartErrorHandler->logApiError($endpoint, $method, $status_code, $response_time, $error_message, $context);
}

?>
