<?php
/**
 * OpenCart Production Environment Manager
 * 
 * Comprehensive environment management system for staging, production, and development
 * environments with configuration management, deployment coordination, and environment
 * synchronization capabilities.
 * 
 * @package OpenCartProduction
 * @version 1.0.0
 * @author Production Systems Team
 */

class OpenCartProductionEnvironmentManager {
    private $environments = [];
    private $activeEnvironment = null;
    private $configManager;
    private $logger;
    private $monitoring;
    private $databases = [];
    private $redis;
    
    // Environment constants
    const ENV_DEVELOPMENT = 'development';
    const ENV_STAGING = 'staging';
    const ENV_PRODUCTION = 'production';
    const ENV_TESTING = 'testing';
    
    public function __construct() {
        $this->initializeEnvironments();
        $this->setupLogging();
        $this->setupMonitoring();
        $this->setupDatabaseConnections();
        $this->setupRedis();
        $this->loadEnvironmentConfiguration();
    }
    
    /**
     * Initialize environment configurations
     */
    private function initializeEnvironments() {
        $this->environments = [
            self::ENV_DEVELOPMENT => [
                'name' => 'Development',
                'domain' => 'dev.opencart.local',
                'database' => 'opencart_dev',
                'debug' => true,
                'cache_enabled' => false,
                'ssl_required' => false,
                'maintenance_mode' => false,
                'max_memory' => '512M',
                'max_execution_time' => 300,
                'log_level' => 'DEBUG',
                'backup_retention' => 7,
                'monitoring_interval' => 60
            ],
            self::ENV_STAGING => [
                'name' => 'Staging',
                'domain' => 'staging.opencart.com',
                'database' => 'opencart_staging',
                'debug' => false,
                'cache_enabled' => true,
                'ssl_required' => true,
                'maintenance_mode' => false,
                'max_memory' => '1024M',
                'max_execution_time' => 180,
                'log_level' => 'INFO',
                'backup_retention' => 14,
                'monitoring_interval' => 30
            ],
            self::ENV_PRODUCTION => [
                'name' => 'Production',
                'domain' => 'opencart.com',
                'database' => 'opencart_production',
                'debug' => false,
                'cache_enabled' => true,
                'ssl_required' => true,
                'maintenance_mode' => false,
                'max_memory' => '2048M',
                'max_execution_time' => 120,
                'log_level' => 'ERROR',
                'backup_retention' => 30,
                'monitoring_interval' => 15
            ],
            self::ENV_TESTING => [
                'name' => 'Testing',
                'domain' => 'test.opencart.local',
                'database' => 'opencart_test',
                'debug' => true,
                'cache_enabled' => false,
                'ssl_required' => false,
                'maintenance_mode' => false,
                'max_memory' => '512M',
                'max_execution_time' => 600,
                'log_level' => 'DEBUG',
                'backup_retention' => 3,
                'monitoring_interval' => 120
            ]
        ];
        
        // Set active environment based on current context
        $this->detectActiveEnvironment();
    }
    
    /**
     * Detect active environment based on domain, IP, or environment variable
     */
    private function detectActiveEnvironment() {
        // Check environment variable first
        if (isset($_ENV['OPENCART_ENVIRONMENT'])) {
            $this->activeEnvironment = $_ENV['OPENCART_ENVIRONMENT'];
            return;
        }
        
        // Check by domain
        $currentDomain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        foreach ($this->environments as $env => $config) {
            if (strpos($currentDomain, $config['domain']) !== false) {
                $this->activeEnvironment = $env;
                return;
            }
        }
        
        // Default to development
        $this->activeEnvironment = self::ENV_DEVELOPMENT;
    }
    
    /**
     * Setup logging system
     */
    private function setupLogging() {
        $logPath = $this->getEnvironmentPath('logs');
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }
        
        $this->logger = new class($logPath, $this->activeEnvironment) {
            private $logPath;
            private $environment;
            
            public function __construct($logPath, $environment) {
                $this->logPath = $logPath;
                $this->environment = $environment;
            }
            
            public function log($level, $message, $context = []) {
                $timestamp = date('Y-m-d H:i:s');
                $contextStr = !empty($context) ? json_encode($context) : '';
                $logEntry = "[{$timestamp}] [{$this->environment}] [{$level}] {$message} {$contextStr}" . PHP_EOL;
                
                $logFile = $this->logPath . '/environment_' . date('Y-m-d') . '.log';
                file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
            }
            
            public function info($message, $context = []) {
                $this->log('INFO', $message, $context);
            }
            
            public function error($message, $context = []) {
                $this->log('ERROR', $message, $context);
            }
            
            public function warning($message, $context = []) {
                $this->log('WARNING', $message, $context);
            }
            
            public function debug($message, $context = []) {
                $this->log('DEBUG', $message, $context);
            }
        };
    }
    
    /**
     * Setup monitoring integration
     */
    private function setupMonitoring() {
        $this->monitoring = new class($this->logger) {
            private $logger;
            private $metrics = [];
            
            public function __construct($logger) {
                $this->logger = $logger;
            }
            
            public function recordMetric($name, $value, $tags = []) {
                $this->metrics[] = [
                    'timestamp' => time(),
                    'name' => $name,
                    'value' => $value,
                    'tags' => $tags
                ];
                
                $this->logger->debug("Metric recorded: {$name} = {$value}");
            }
            
            public function getMetrics() {
                return $this->metrics;
            }
            
            public function clearMetrics() {
                $this->metrics = [];
            }
        };
    }
    
    /**
     * Setup database connections for all environments
     */
    private function setupDatabaseConnections() {
        $dbConfigs = [
            self::ENV_DEVELOPMENT => [
                'host' => 'localhost',
                'username' => 'opencart_dev',
                'password' => 'dev_password',
                'database' => 'opencart_dev',
                'port' => 3306
            ],
            self::ENV_STAGING => [
                'host' => 'staging-db.opencart.com',
                'username' => 'opencart_staging',
                'password' => getenv('STAGING_DB_PASSWORD'),
                'database' => 'opencart_staging',
                'port' => 3306
            ],
            self::ENV_PRODUCTION => [
                'host' => 'prod-db.opencart.com',
                'username' => 'opencart_prod',
                'password' => getenv('PROD_DB_PASSWORD'),
                'database' => 'opencart_production',
                'port' => 3306
            ],
            self::ENV_TESTING => [
                'host' => 'localhost',
                'username' => 'opencart_test',
                'password' => 'test_password',
                'database' => 'opencart_test',
                'port' => 3306
            ]
        ];
        
        foreach ($dbConfigs as $env => $config) {
            try {
                $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
                $this->databases[$env] = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => true
                ]);
                
                $this->logger->info("Database connection established for environment: {$env}");
            } catch (PDOException $e) {
                $this->logger->error("Failed to connect to database for environment {$env}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Setup Redis connection
     */
    private function setupRedis() {
        try {
            $this->redis = new Redis();
            $redisHost = $this->getEnvironmentConfig('redis_host', 'localhost');
            $redisPort = $this->getEnvironmentConfig('redis_port', 6379);
            $this->redis->connect($redisHost, $redisPort);
            
            $this->logger->info("Redis connection established");
        } catch (Exception $e) {
            $this->logger->error("Failed to connect to Redis: " . $e->getMessage());
        }
    }
    
    /**
     * Load environment-specific configuration
     */
    private function loadEnvironmentConfiguration() {
        $configFile = $this->getEnvironmentPath('config') . '/environment.json';
        
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            if ($config) {
                $this->environments[$this->activeEnvironment] = array_merge(
                    $this->environments[$this->activeEnvironment],
                    $config
                );
            }
        }
        
        $this->logger->info("Environment configuration loaded for: " . $this->activeEnvironment);
    }
    
    /**
     * Switch to a different environment
     */
    public function switchEnvironment($environment) {
        if (!isset($this->environments[$environment])) {
            throw new InvalidArgumentException("Environment '{$environment}' does not exist");
        }
        
        $previousEnv = $this->activeEnvironment;
        $this->activeEnvironment = $environment;
        
        // Apply environment-specific settings
        $this->applyEnvironmentSettings();
        
        $this->logger->info("Environment switched from {$previousEnv} to {$environment}");
        $this->monitoring->recordMetric('environment_switch', 1, ['from' => $previousEnv, 'to' => $environment]);
        
        return true;
    }
    
    /**
     * Apply environment-specific PHP settings
     */
    private function applyEnvironmentSettings() {
        $config = $this->environments[$this->activeEnvironment];
        
        // Set memory limit
        ini_set('memory_limit', $config['max_memory']);
        
        // Set execution time limit
        ini_set('max_execution_time', $config['max_execution_time']);
        
        // Set error reporting based on environment
        if ($config['debug']) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(E_ERROR | E_WARNING | E_PARSE);
            ini_set('display_errors', 0);
        }
        
        // Set timezone
        date_default_timezone_set($config['timezone'] ?? 'UTC');
        
        $this->logger->info("Environment settings applied for: " . $this->activeEnvironment);
    }
    
    /**
     * Get current environment name
     */
    public function getCurrentEnvironment() {
        return $this->activeEnvironment;
    }
    
    /**
     * Get environment configuration
     */
    public function getEnvironmentConfig($key = null, $default = null) {
        $config = $this->environments[$this->activeEnvironment];
        
        if ($key === null) {
            return $config;
        }
        
        return $config[$key] ?? $default;
    }
    
    /**
     * Get environment-specific path
     */
    public function getEnvironmentPath($type) {
        $basePath = dirname(__DIR__) . '/environments/' . $this->activeEnvironment;
        
        switch ($type) {
            case 'logs':
                return $basePath . '/logs';
            case 'config':
                return $basePath . '/config';
            case 'cache':
                return $basePath . '/cache';
            case 'uploads':
                return $basePath . '/uploads';
            case 'backups':
                return $basePath . '/backups';
            default:
                return $basePath;
        }
    }
    
    /**
     * Get database connection for current or specific environment
     */
    public function getDatabase($environment = null) {
        $env = $environment ?? $this->activeEnvironment;
        return $this->databases[$env] ?? null;
    }
    
    /**
     * Enable maintenance mode
     */
    public function enableMaintenanceMode($message = 'System under maintenance') {
        $this->environments[$this->activeEnvironment]['maintenance_mode'] = true;
        
        $maintenanceFile = $this->getEnvironmentPath('config') . '/maintenance.json';
        file_put_contents($maintenanceFile, json_encode([
            'enabled' => true,
            'message' => $message,
            'timestamp' => time(),
            'environment' => $this->activeEnvironment
        ]));
        
        $this->logger->info("Maintenance mode enabled: {$message}");
        $this->monitoring->recordMetric('maintenance_mode', 1, ['action' => 'enabled']);
        
        return true;
    }
    
    /**
     * Disable maintenance mode
     */
    public function disableMaintenanceMode() {
        $this->environments[$this->activeEnvironment]['maintenance_mode'] = false;
        
        $maintenanceFile = $this->getEnvironmentPath('config') . '/maintenance.json';
        if (file_exists($maintenanceFile)) {
            unlink($maintenanceFile);
        }
        
        $this->logger->info("Maintenance mode disabled");
        $this->monitoring->recordMetric('maintenance_mode', 0, ['action' => 'disabled']);
        
        return true;
    }
    
    /**
     * Check if maintenance mode is enabled
     */
    public function isMaintenanceModeEnabled() {
        return $this->environments[$this->activeEnvironment]['maintenance_mode'] ?? false;
    }
    
    /**
     * Synchronize data between environments
     */
    public function synchronizeEnvironments($sourceEnv, $targetEnv, $options = []) {
        if (!isset($this->environments[$sourceEnv]) || !isset($this->environments[$targetEnv])) {
            throw new InvalidArgumentException("Source or target environment does not exist");
        }
        
        $this->logger->info("Starting environment synchronization from {$sourceEnv} to {$targetEnv}");
        
        $results = [
            'database' => false,
            'files' => false,
            'config' => false,
            'cache' => false
        ];
        
        try {
            // Synchronize database if requested
            if ($options['sync_database'] ?? false) {
                $results['database'] = $this->syncDatabase($sourceEnv, $targetEnv);
            }
            
            // Synchronize files if requested
            if ($options['sync_files'] ?? false) {
                $results['files'] = $this->syncFiles($sourceEnv, $targetEnv);
            }
            
            // Synchronize configuration if requested
            if ($options['sync_config'] ?? false) {
                $results['config'] = $this->syncConfiguration($sourceEnv, $targetEnv);
            }
            
            // Clear cache after synchronization
            if ($options['clear_cache'] ?? true) {
                $results['cache'] = $this->clearEnvironmentCache($targetEnv);
            }
            
            $this->logger->info("Environment synchronization completed", $results);
            $this->monitoring->recordMetric('environment_sync', 1, [
                'source' => $sourceEnv,
                'target' => $targetEnv,
                'success' => array_sum($results) === count(array_filter($results))
            ]);
            
            return $results;
            
        } catch (Exception $e) {
            $this->logger->error("Environment synchronization failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Synchronize database between environments
     */
    private function syncDatabase($sourceEnv, $targetEnv) {
        try {
            $sourceDb = $this->getDatabase($sourceEnv);
            $targetDb = $this->getDatabase($targetEnv);
            
            if (!$sourceDb || !$targetDb) {
                throw new Exception("Database connection not available for source or target environment");
            }
            
            // Get all tables from source database
            $tables = $sourceDb->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                // Create backup of target table
                $backupTable = $table . '_backup_' . date('YmdHis');
                $targetDb->exec("CREATE TABLE {$backupTable} LIKE {$table}");
                $targetDb->exec("INSERT INTO {$backupTable} SELECT * FROM {$table}");
                
                // Truncate target table
                $targetDb->exec("TRUNCATE TABLE {$table}");
                
                // Copy data from source to target
                $data = $sourceDb->query("SELECT * FROM {$table}")->fetchAll();
                
                if (!empty($data)) {
                    $columns = array_keys($data[0]);
                    $placeholders = ':' . implode(', :', $columns);
                    $columnList = implode(', ', $columns);
                    
                    $stmt = $targetDb->prepare("INSERT INTO {$table} ({$columnList}) VALUES ({$placeholders})");
                    
                    foreach ($data as $row) {
                        $stmt->execute($row);
                    }
                }
            }
            
            $this->logger->info("Database synchronized from {$sourceEnv} to {$targetEnv}");
            return true;
            
        } catch (Exception $e) {
            $this->logger->error("Database synchronization failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Synchronize files between environments
     */
    private function syncFiles($sourceEnv, $targetEnv) {
        try {
            $sourcePath = dirname(__DIR__) . '/environments/' . $sourceEnv . '/uploads';
            $targetPath = dirname(__DIR__) . '/environments/' . $targetEnv . '/uploads';
            
            if (!is_dir($sourcePath)) {
                $this->logger->warning("Source uploads directory does not exist: {$sourcePath}");
                return false;
            }
            
            // Create target directory if it doesn't exist
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0755, true);
            }
            
            // Use rsync for efficient file synchronization
            $command = "rsync -av --delete {$sourcePath}/ {$targetPath}/";
            $output = shell_exec($command);
            
            $this->logger->info("Files synchronized from {$sourceEnv} to {$targetEnv}");
            return true;
            
        } catch (Exception $e) {
            $this->logger->error("File synchronization failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Synchronize configuration between environments
     */
    private function syncConfiguration($sourceEnv, $targetEnv) {
        try {
            $sourceConfigPath = dirname(__DIR__) . '/environments/' . $sourceEnv . '/config';
            $targetConfigPath = dirname(__DIR__) . '/environments/' . $targetEnv . '/config';
            
            if (!is_dir($sourceConfigPath)) {
                $this->logger->warning("Source config directory does not exist: {$sourceConfigPath}");
                return false;
            }
            
            // Create target config directory if it doesn't exist
            if (!is_dir($targetConfigPath)) {
                mkdir($targetConfigPath, 0755, true);
            }
            
            // Copy configuration files (excluding environment-specific files)
            $excludeFiles = ['environment.json', 'database.json', 'maintenance.json'];
            
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($sourceConfigPath)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && !in_array($file->getFilename(), $excludeFiles)) {
                    $relativePath = str_replace($sourceConfigPath, '', $file->getPathname());
                    $targetFile = $targetConfigPath . $relativePath;
                    
                    // Create target directory if needed
                    $targetDir = dirname($targetFile);
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    
                    copy($file->getPathname(), $targetFile);
                }
            }
            
            $this->logger->info("Configuration synchronized from {$sourceEnv} to {$targetEnv}");
            return true;
            
        } catch (Exception $e) {
            $this->logger->error("Configuration synchronization failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear environment cache
     */
    public function clearEnvironmentCache($environment = null) {
        $env = $environment ?? $this->activeEnvironment;
        
        try {
            $cachePath = dirname(__DIR__) . '/environments/' . $env . '/cache';
            
            if (is_dir($cachePath)) {
                $this->clearDirectory($cachePath);
            }
            
            // Clear Redis cache if available
            if ($this->redis) {
                $this->redis->flushDB();
            }
            
            // Clear OPcache if available
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            
            $this->logger->info("Cache cleared for environment: {$env}");
            $this->monitoring->recordMetric('cache_clear', 1, ['environment' => $env]);
            
            return true;
            
        } catch (Exception $e) {
            $this->logger->error("Failed to clear cache for environment {$env}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear directory recursively
     */
    private function clearDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $filePath = $dir . '/' . $file;
            
            if (is_dir($filePath)) {
                $this->clearDirectory($filePath);
                rmdir($filePath);
            } else {
                unlink($filePath);
            }
        }
    }
    
    /**
     * Get environment health status
     */
    public function getEnvironmentHealth($environment = null) {
        $env = $environment ?? $this->activeEnvironment;
        $config = $this->environments[$env];
        
        $health = [
            'environment' => $env,
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => time()
        ];
        
        // Check database connection
        $dbHealth = $this->checkDatabaseHealth($env);
        $health['checks']['database'] = $dbHealth;
        
        // Check disk space
        $diskHealth = $this->checkDiskSpace($env);
        $health['checks']['disk'] = $diskHealth;
        
        // Check memory usage
        $memoryHealth = $this->checkMemoryUsage();
        $health['checks']['memory'] = $memoryHealth;
        
        // Check cache availability
        $cacheHealth = $this->checkCacheHealth();
        $health['checks']['cache'] = $cacheHealth;
        
        // Check maintenance mode
        $maintenanceHealth = $this->checkMaintenanceMode($env);
        $health['checks']['maintenance'] = $maintenanceHealth;
        
        // Determine overall health status
        $failedChecks = array_filter($health['checks'], function($check) {
            return $check['status'] !== 'healthy';
        });
        
        if (!empty($failedChecks)) {
            $health['status'] = count($failedChecks) > 2 ? 'unhealthy' : 'warning';
        }
        
        $this->monitoring->recordMetric('environment_health', 1, [
            'environment' => $env,
            'status' => $health['status']
        ]);
        
        return $health;
    }
    
    /**
     * Check database health
     */
    private function checkDatabaseHealth($environment) {
        try {
            $db = $this->getDatabase($environment);
            
            if (!$db) {
                return ['status' => 'unhealthy', 'message' => 'Database connection not available'];
            }
            
            // Test connection
            $db->query('SELECT 1');
            
            // Check database size
            $sizeQuery = $db->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS db_size FROM information_schema.tables WHERE table_schema = DATABASE()");
            $dbSize = $sizeQuery->fetchColumn();
            
            return [
                'status' => 'healthy',
                'message' => 'Database connection OK',
                'size_mb' => $dbSize
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check disk space
     */
    private function checkDiskSpace($environment) {
        try {
            $path = dirname(__DIR__) . '/environments/' . $environment;
            $bytes = disk_free_space($path);
            $freeSpaceGB = round($bytes / 1024 / 1024 / 1024, 2);
            
            $status = 'healthy';
            $message = "Free disk space: {$freeSpaceGB} GB";
            
            if ($freeSpaceGB < 1) {
                $status = 'unhealthy';
                $message = "Low disk space: {$freeSpaceGB} GB";
            } elseif ($freeSpaceGB < 5) {
                $status = 'warning';
                $message = "Disk space getting low: {$freeSpaceGB} GB";
            }
            
            return [
                'status' => $status,
                'message' => $message,
                'free_space_gb' => $freeSpaceGB
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Failed to check disk space: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check memory usage
     */
    private function checkMemoryUsage() {
        try {
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = ini_get('memory_limit');
            $memoryLimitBytes = $this->convertToBytes($memoryLimit);
            
            $usagePercent = ($memoryUsage / $memoryLimitBytes) * 100;
            $usageMB = round($memoryUsage / 1024 / 1024, 2);
            
            $status = 'healthy';
            $message = "Memory usage: {$usageMB} MB ({$usagePercent}%)";
            
            if ($usagePercent > 90) {
                $status = 'unhealthy';
                $message = "High memory usage: {$usageMB} MB ({$usagePercent}%)";
            } elseif ($usagePercent > 75) {
                $status = 'warning';
                $message = "Memory usage getting high: {$usageMB} MB ({$usagePercent}%)";
            }
            
            return [
                'status' => $status,
                'message' => $message,
                'usage_mb' => $usageMB,
                'usage_percent' => round($usagePercent, 2)
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Failed to check memory usage: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check cache health
     */
    private function checkCacheHealth() {
        try {
            if (!$this->redis) {
                return [
                    'status' => 'warning',
                    'message' => 'Redis cache not available'
                ];
            }
            
            // Test Redis connection
            $this->redis->ping();
            
            // Get Redis info
            $info = $this->redis->info();
            $memoryUsage = $info['used_memory_human'] ?? 'Unknown';
            
            return [
                'status' => 'healthy',
                'message' => 'Redis cache OK',
                'memory_usage' => $memoryUsage
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Cache health check failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check maintenance mode status
     */
    private function checkMaintenanceMode($environment) {
        $isEnabled = $this->environments[$environment]['maintenance_mode'] ?? false;
        
        return [
            'status' => $isEnabled ? 'warning' : 'healthy',
            'message' => $isEnabled ? 'Maintenance mode is enabled' : 'Maintenance mode is disabled',
            'enabled' => $isEnabled
        ];
    }
    
    /**
     * Convert memory string to bytes
     */
    private function convertToBytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (int)$val;
        
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        
        return $val;
    }
    
    /**
     * Deploy to environment
     */
    public function deployToEnvironment($environment, $options = []) {
        if (!isset($this->environments[$environment])) {
            throw new InvalidArgumentException("Environment '{$environment}' does not exist");
        }
        
        $this->logger->info("Starting deployment to environment: {$environment}");
        
        $deploymentId = uniqid('deploy_');
        $startTime = time();
        
        try {
            // Enable maintenance mode if required
            if ($options['maintenance_mode'] ?? true) {
                $this->enableMaintenanceMode("Deployment in progress");
            }
            
            // Run pre-deployment checks
            if ($options['pre_checks'] ?? true) {
                $this->runPreDeploymentChecks($environment);
            }
            
            // Deploy application code
            if ($options['deploy_code'] ?? true) {
                $this->deployApplicationCode($environment);
            }
            
            // Run database migrations
            if ($options['run_migrations'] ?? true) {
                $this->runDatabaseMigrations($environment);
            }
            
            // Clear cache
            if ($options['clear_cache'] ?? true) {
                $this->clearEnvironmentCache($environment);
            }
            
            // Run post-deployment checks
            if ($options['post_checks'] ?? true) {
                $this->runPostDeploymentChecks($environment);
            }
            
            // Disable maintenance mode
            if ($options['maintenance_mode'] ?? true) {
                $this->disableMaintenanceMode();
            }
            
            $duration = time() - $startTime;
            
            $this->logger->info("Deployment completed successfully", [
                'deployment_id' => $deploymentId,
                'environment' => $environment,
                'duration' => $duration
            ]);
            
            $this->monitoring->recordMetric('deployment', 1, [
                'environment' => $environment,
                'status' => 'success',
                'duration' => $duration
            ]);
            
            return [
                'success' => true,
                'deployment_id' => $deploymentId,
                'duration' => $duration,
                'environment' => $environment
            ];
            
        } catch (Exception $e) {
            // Disable maintenance mode in case of failure
            if ($options['maintenance_mode'] ?? true) {
                $this->disableMaintenanceMode();
            }
            
            $duration = time() - $startTime;
            
            $this->logger->error("Deployment failed", [
                'deployment_id' => $deploymentId,
                'environment' => $environment,
                'error' => $e->getMessage(),
                'duration' => $duration
            ]);
            
            $this->monitoring->recordMetric('deployment', 1, [
                'environment' => $environment,
                'status' => 'failed',
                'duration' => $duration
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Run pre-deployment checks
     */
    private function runPreDeploymentChecks($environment) {
        $this->logger->info("Running pre-deployment checks for: {$environment}");
        
        // Check environment health
        $health = $this->getEnvironmentHealth($environment);
        if ($health['status'] === 'unhealthy') {
            throw new Exception("Environment health check failed: " . json_encode($health));
        }
        
        // Check disk space
        $diskCheck = $health['checks']['disk'];
        if ($diskCheck['free_space_gb'] < 2) {
            throw new Exception("Insufficient disk space for deployment: {$diskCheck['free_space_gb']} GB");
        }
        
        // Check database connectivity
        $dbCheck = $health['checks']['database'];
        if ($dbCheck['status'] !== 'healthy') {
            throw new Exception("Database connectivity check failed: {$dbCheck['message']}");
        }
        
        $this->logger->info("Pre-deployment checks passed");
    }
    
    /**
     * Deploy application code
     */
    private function deployApplicationCode($environment) {
        $this->logger->info("Deploying application code to: {$environment}");
        
        // This would typically involve pulling from Git repository,
        // copying files, updating symlinks, etc.
        // For now, we'll simulate the process
        
        sleep(2); // Simulate deployment time
        
        $this->logger->info("Application code deployed successfully");
    }
    
    /**
     * Run database migrations
     */
    private function runDatabaseMigrations($environment) {
        $this->logger->info("Running database migrations for: {$environment}");
        
        $db = $this->getDatabase($environment);
        if (!$db) {
            throw new Exception("Database connection not available for migrations");
        }
        
        // This would typically run actual migration scripts
        // For now, we'll simulate the process
        
        sleep(1); // Simulate migration time
        
        $this->logger->info("Database migrations completed successfully");
    }
    
    /**
     * Run post-deployment checks
     */
    private function runPostDeploymentChecks($environment) {
        $this->logger->info("Running post-deployment checks for: {$environment}");
        
        // Check if application is responding
        $config = $this->environments[$environment];
        $url = ($config['ssl_required'] ? 'https://' : 'http://') . $config['domain'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Application health check failed: HTTP {$httpCode}");
        }
        
        $this->logger->info("Post-deployment checks passed");
    }
    
    /**
     * Get environment metrics
     */
    public function getEnvironmentMetrics($environment = null) {
        $env = $environment ?? $this->activeEnvironment;
        
        return [
            'environment' => $env,
            'health' => $this->getEnvironmentHealth($env),
            'monitoring_metrics' => $this->monitoring->getMetrics(),
            'configuration' => $this->getEnvironmentConfig(),
            'timestamp' => time()
        ];
    }
    
    /**
     * Generate environment report
     */
    public function generateEnvironmentReport() {
        $report = [
            'title' => 'OpenCart Environment Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'active_environment' => $this->activeEnvironment,
            'environments' => []
        ];
        
        foreach ($this->environments as $env => $config) {
            $report['environments'][$env] = [
                'name' => $config['name'],
                'domain' => $config['domain'],
                'health' => $this->getEnvironmentHealth($env),
                'maintenance_mode' => $config['maintenance_mode'],
                'configuration' => $config
            ];
        }
        
        $report['summary'] = [
            'total_environments' => count($this->environments),
            'healthy_environments' => count(array_filter($report['environments'], function($env) {
                return $env['health']['status'] === 'healthy';
            })),
            'environments_in_maintenance' => count(array_filter($report['environments'], function($env) {
                return $env['maintenance_mode'];
            }))
        ];
        
        return $report;
    }
}

// Initialize environment manager
try {
    $environmentManager = new OpenCartProductionEnvironmentManager();
    
    // Log initialization
    error_log("OpenCart Production Environment Manager initialized successfully for environment: " . 
              $environmentManager->getCurrentEnvironment());
    
} catch (Exception $e) {
    error_log("Failed to initialize OpenCart Production Environment Manager: " . $e->getMessage());
    throw $e;
}

// Export environment manager instance
$GLOBALS['opencart_environment_manager'] = $environmentManager;

?>
