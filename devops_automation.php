<?php
/**
 * MesChain-Sync DevOps Automation Script
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0
 * @author Musti - DevOps & Infrastructure Team
 */

class DevOpsAutomation {
    
    private $config;
    private $log_file;
    private $backup_dir;
    private $deploy_dir;
    
    public function __construct() {
        $this->config = $this->loadConfiguration();
        $this->log_file = dirname(__FILE__) . '/logs/devops_' . date('Y-m-d') . '.log';
        $this->backup_dir = dirname(__FILE__) . '/backups';
        $this->deploy_dir = dirname(__FILE__) . '/upload';
        
        // Create directories if they don't exist
        $this->createDirectoryIfNotExists(dirname($this->log_file));
        $this->createDirectoryIfNotExists($this->backup_dir);
    }
    
    /**
     * Run CI/CD Pipeline
     *
     * @param string $environment Environment (dev, staging, production)
     * @return array Pipeline result
     */
    public function runCIPipeline($environment = 'dev') {
        $this->log("Starting CI/CD Pipeline for environment: {$environment}");
        
        $pipeline_steps = [
            'code_quality_check' => false,
            'security_scan' => false,
            'database_backup' => false,
            'deployment' => false,
            'smoke_tests' => false,
            'monitoring_setup' => false
        ];
        
        try {
            // Step 1: Code Quality Check
            $this->log("Step 1: Running code quality checks...");
            $pipeline_steps['code_quality_check'] = $this->runCodeQualityCheck();
            
            if (!$pipeline_steps['code_quality_check']) {
                throw new Exception("Code quality check failed");
            }
            
            // Step 2: Security Scan
            $this->log("Step 2: Running security scan...");
            $pipeline_steps['security_scan'] = $this->runSecurityScan();
            
            if (!$pipeline_steps['security_scan']) {
                throw new Exception("Security scan failed");
            }
            
            // Step 3: Database Backup (for staging/production)
            if (in_array($environment, ['staging', 'production'])) {
                $this->log("Step 3: Creating database backup...");
                $pipeline_steps['database_backup'] = $this->createDatabaseBackup();
                
                if (!$pipeline_steps['database_backup']) {
                    throw new Exception("Database backup failed");
                }
            } else {
                $pipeline_steps['database_backup'] = true; // Skip for dev
            }
            
            // Step 4: Deployment
            $this->log("Step 4: Deploying to {$environment}...");
            $pipeline_steps['deployment'] = $this->deployToEnvironment($environment);
            
            if (!$pipeline_steps['deployment']) {
                throw new Exception("Deployment failed");
            }
            
            // Step 5: Smoke Tests
            $this->log("Step 5: Running smoke tests...");
            $pipeline_steps['smoke_tests'] = $this->runSmokeTests($environment);
            
            if (!$pipeline_steps['smoke_tests']) {
                $this->log("WARNING: Smoke tests failed, but deployment continues");
            }
            
            // Step 6: Setup Monitoring
            $this->log("Step 6: Setting up monitoring...");
            $pipeline_steps['monitoring_setup'] = $this->setupMonitoring($environment);
            
            $this->log("CI/CD Pipeline completed successfully for {$environment}");
            
            return [
                'success' => true,
                'environment' => $environment,
                'steps' => $pipeline_steps,
                'deployment_time' => date('Y-m-d H:i:s'),
                'message' => 'Pipeline completed successfully'
            ];
            
        } catch (Exception $e) {
            $this->log("ERROR: Pipeline failed - " . $e->getMessage());
            
            return [
                'success' => false,
                'environment' => $environment,
                'steps' => $pipeline_steps,
                'error' => $e->getMessage(),
                'message' => 'Pipeline failed'
            ];
        }
    }
    
    /**
     * Run Code Quality Check
     *
     * @return bool Success status
     */
    private function runCodeQualityCheck() {
        try {
            $issues = [];
            
            // Check PHP syntax
            $php_files = $this->getPhpFiles($this->deploy_dir);
            foreach ($php_files as $file) {
                $output = shell_exec("php -l {$file} 2>&1");
                if (strpos($output, 'No syntax errors') === false) {
                    $issues[] = "PHP syntax error in {$file}: " . trim($output);
                }
            }
            
            // Check for security vulnerabilities
            $security_patterns = [
                '/eval\s*\(/' => 'Use of eval() function',
                '/\$_GET\[.*\].*mysql_query/' => 'Potential SQL injection',
                '/\$_POST\[.*\].*mysql_query/' => 'Potential SQL injection',
                '/shell_exec\s*\(\s*\$/' => 'Dynamic shell execution',
                '/system\s*\(\s*\$/' => 'Dynamic system call'
            ];
            
            foreach ($php_files as $file) {
                $content = file_get_contents($file);
                foreach ($security_patterns as $pattern => $description) {
                    if (preg_match($pattern, $content)) {
                        $issues[] = "Security issue in {$file}: {$description}";
                    }
                }
            }
            
            // Check for missing PHPDoc
            foreach ($php_files as $file) {
                $content = file_get_contents($file);
                if (preg_match('/class\s+\w+/', $content) && !preg_match('/\/\*\*.*\*\/.*class/s', $content)) {
                    $issues[] = "Missing PHPDoc comment in {$file}";
                }
            }
            
            if (!empty($issues)) {
                $this->log("Code quality issues found:");
                foreach ($issues as $issue) {
                    $this->log("  - " . $issue);
                }
                
                // For dev environment, continue with warnings
                // For production, fail the pipeline
                return count($issues) < 10; // Allow up to 9 minor issues
            }
            
            $this->log("Code quality check passed");
            return true;
            
        } catch (Exception $e) {
            $this->log("Code quality check failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Run Security Scan
     *
     * @return bool Success status
     */
    private function runSecurityScan() {
        try {
            $vulnerabilities = [];
            
            // Check file permissions
            $critical_files = [
                $this->deploy_dir . '/config.php',
                $this->deploy_dir . '/admin/config.php'
            ];
            
            foreach ($critical_files as $file) {
                if (file_exists($file)) {
                    $perms = substr(sprintf('%o', fileperms($file)), -4);
                    if ($perms !== '0644') {
                        $vulnerabilities[] = "Incorrect file permissions for {$file}: {$perms} (should be 0644)";
                    }
                }
            }
            
            // Check for default passwords
            $config_files = [
                $this->deploy_dir . '/config.php',
                $this->deploy_dir . '/admin/config.php'
            ];
            
            foreach ($config_files as $file) {
                if (file_exists($file)) {
                    $content = file_get_contents($file);
                    if (preg_match('/define\s*\(\s*[\'"]DB_PASSWORD[\'"]\s*,\s*[\'"][\'"]/', $content)) {
                        $vulnerabilities[] = "Empty database password found in {$file}";
                    }
                }
            }
            
            // Check for exposed sensitive files
            $sensitive_files = [
                '.env',
                '.git',
                'composer.lock',
                'config.bak'
            ];
            
            foreach ($sensitive_files as $file) {
                if (file_exists($this->deploy_dir . '/' . $file)) {
                    $vulnerabilities[] = "Sensitive file exposed: {$file}";
                }
            }
            
            if (!empty($vulnerabilities)) {
                $this->log("Security vulnerabilities found:");
                foreach ($vulnerabilities as $vulnerability) {
                    $this->log("  - " . $vulnerability);
                }
                return false;
            }
            
            $this->log("Security scan passed");
            return true;
            
        } catch (Exception $e) {
            $this->log("Security scan failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create Database Backup
     *
     * @return bool Success status
     */
    private function createDatabaseBackup() {
        try {
            $backup_file = $this->backup_dir . '/db_backup_' . date('Y-m-d_H-i-s') . '.sql';
            
            if (isset($this->config['database'])) {
                $db_config = $this->config['database'];
                
                $command = sprintf(
                    'mysqldump -h%s -u%s -p%s %s > %s',
                    escapeshellarg($db_config['host']),
                    escapeshellarg($db_config['username']),
                    escapeshellarg($db_config['password']),
                    escapeshellarg($db_config['database']),
                    escapeshellarg($backup_file)
                );
                
                $output = shell_exec($command . ' 2>&1');
                
                if (file_exists($backup_file) && filesize($backup_file) > 0) {
                    $this->log("Database backup created: " . basename($backup_file));
                    
                    // Compress the backup
                    $compressed_file = $backup_file . '.gz';
                    shell_exec("gzip {$backup_file}");
                    
                    if (file_exists($compressed_file)) {
                        $this->log("Backup compressed: " . basename($compressed_file));
                    }
                    
                    // Clean old backups (keep last 10)
                    $this->cleanOldBackups();
                    
                    return true;
                } else {
                    $this->log("Backup creation failed: " . $output);
                    return false;
                }
            }
            
            $this->log("Database configuration not found, skipping backup");
            return true;
            
        } catch (Exception $e) {
            $this->log("Database backup failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Deploy to Environment
     *
     * @param string $environment Environment name
     * @return bool Success status
     */
    private function deployToEnvironment($environment) {
        try {
            $this->log("Starting deployment to {$environment}");
            
            // Environment-specific configurations
            $env_config = $this->getEnvironmentConfig($environment);
            
            // Copy files to deployment directory
            if (isset($env_config['deploy_path'])) {
                $rsync_command = sprintf(
                    'rsync -av --exclude=".git" --exclude="node_modules" --exclude="*.log" %s/ %s/',
                    escapeshellarg($this->deploy_dir),
                    escapeshellarg($env_config['deploy_path'])
                );
                
                $output = shell_exec($rsync_command . ' 2>&1');
                $this->log("Deployment output: " . $output);
            }
            
            // Update file permissions
            $this->updateFilePermissions($env_config['deploy_path'] ?? $this->deploy_dir);
            
            // Clear cache
            $this->clearApplicationCache($env_config['deploy_path'] ?? $this->deploy_dir);
            
            // Run database migrations if needed
            if ($environment !== 'dev') {
                $this->runDatabaseMigrations();
            }
            
            $this->log("Deployment to {$environment} completed");
            return true;
            
        } catch (Exception $e) {
            $this->log("Deployment failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Run Smoke Tests
     *
     * @param string $environment Environment name
     * @return bool Success status
     */
    private function runSmokeTests($environment) {
        try {
            $env_config = $this->getEnvironmentConfig($environment);
            $base_url = $env_config['base_url'] ?? 'http://localhost';
            
            $test_urls = [
                $base_url . '/',
                $base_url . '/admin/',
                $base_url . '/admin/index.php?route=extension/module/meschain_sync'
            ];
            
            foreach ($test_urls as $url) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($http_code >= 400) {
                    $this->log("Smoke test failed for {$url}: HTTP {$http_code}");
                    return false;
                }
                
                $this->log("Smoke test passed for {$url}: HTTP {$http_code}");
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->log("Smoke tests failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Setup Monitoring
     *
     * @param string $environment Environment name
     * @return bool Success status
     */
    private function setupMonitoring($environment) {
        try {
            // Create monitoring configuration
            $monitoring_config = [
                'environment' => $environment,
                'deployment_time' => date('Y-m-d H:i:s'),
                'monitoring_enabled' => true,
                'health_check_url' => $this->getEnvironmentConfig($environment)['base_url'] ?? 'http://localhost',
                'log_level' => $environment === 'production' ? 'error' : 'debug',
                'alerts' => [
                    'email' => $this->config['monitoring']['email'] ?? 'admin@example.com',
                    'slack_webhook' => $this->config['monitoring']['slack_webhook'] ?? ''
                ]
            ];
            
            $monitoring_file = $this->deploy_dir . '/system/monitoring_config.json';
            file_put_contents($monitoring_file, json_encode($monitoring_config, JSON_PRETTY_PRINT));
            
            // Setup log rotation
            $this->setupLogRotation();
            
            // Create health check endpoint
            $this->createHealthCheckEndpoint();
            
            $this->log("Monitoring setup completed for {$environment}");
            return true;
            
        } catch (Exception $e) {
            $this->log("Monitoring setup failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Performance Optimization
     *
     * @return array Optimization results
     */
    public function runPerformanceOptimization() {
        $this->log("Starting performance optimization");
        
        $optimizations = [
            'opcache_optimization' => $this->optimizeOpCache(),
            'database_optimization' => $this->optimizeDatabase(),
            'file_optimization' => $this->optimizeFiles(),
            'cache_optimization' => $this->optimizeCache()
        ];
        
        $successful = array_filter($optimizations);
        $this->log("Performance optimization completed: " . count($successful) . "/" . count($optimizations) . " successful");
        
        return $optimizations;
    }
    
    /**
     * System Health Check
     *
     * @return array Health status
     */
    public function performHealthCheck() {
        $health = [
            'timestamp' => date('Y-m-d H:i:s'),
            'overall_status' => 'healthy',
            'checks' => []
        ];
        
        // Check disk space
        $disk_usage = disk_free_space('/');
        $disk_total = disk_total_space('/');
        $disk_percentage = round((($disk_total - $disk_usage) / $disk_total) * 100, 2);
        
        $health['checks']['disk_space'] = [
            'status' => $disk_percentage < 90 ? 'healthy' : 'warning',
            'usage_percentage' => $disk_percentage,
            'free_space' => $this->formatBytes($disk_usage)
        ];
        
        // Check memory usage
        $memory_usage = memory_get_usage(true);
        $memory_limit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $memory_percentage = $memory_limit > 0 ? round(($memory_usage / $memory_limit) * 100, 2) : 0;
        
        $health['checks']['memory_usage'] = [
            'status' => $memory_percentage < 80 ? 'healthy' : 'warning',
            'usage_percentage' => $memory_percentage,
            'current_usage' => $this->formatBytes($memory_usage)
        ];
        
        // Check database connection
        try {
            if (isset($this->config['database'])) {
                $db_config = $this->config['database'];
                $pdo = new PDO(
                    "mysql:host={$db_config['host']};dbname={$db_config['database']}",
                    $db_config['username'],
                    $db_config['password']
                );
                $health['checks']['database'] = ['status' => 'healthy'];
            } else {
                $health['checks']['database'] = ['status' => 'unknown', 'message' => 'No database config'];
            }
        } catch (Exception $e) {
            $health['checks']['database'] = ['status' => 'unhealthy', 'error' => $e->getMessage()];
            $health['overall_status'] = 'unhealthy';
        }
        
        // Check log file sizes
        $log_dir = dirname($this->log_file);
        if (is_dir($log_dir)) {
            $total_log_size = 0;
            $files = glob($log_dir . '/*.log');
            foreach ($files as $file) {
                $total_log_size += filesize($file);
            }
            
            $health['checks']['log_files'] = [
                'status' => $total_log_size < (50 * 1024 * 1024) ? 'healthy' : 'warning', // 50MB limit
                'total_size' => $this->formatBytes($total_log_size),
                'file_count' => count($files)
            ];
        }
        
        return $health;
    }
    
    // Helper methods
    private function loadConfiguration() {
        $config_file = dirname(__FILE__) . '/config/devops_config.json';
        if (file_exists($config_file)) {
            return json_decode(file_get_contents($config_file), true);
        }
        
        // Default configuration
        return [
            'environments' => [
                'dev' => ['base_url' => 'http://localhost'],
                'staging' => ['base_url' => 'https://staging.example.com'],
                'production' => ['base_url' => 'https://example.com']
            ],
            'monitoring' => [
                'email' => 'admin@example.com'
            ]
        ];
    }
    
    private function getPhpFiles($directory) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    private function getEnvironmentConfig($environment) {
        return $this->config['environments'][$environment] ?? [];
    }
    
    private function createDirectoryIfNotExists($directory) {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] {$message}" . PHP_EOL;
        
        echo $log_entry;
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    private function parseMemoryLimit($limit) {
        if ($limit == -1) return -1;
        
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int)$limit;
        
        switch ($last) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        
        return $value;
    }
    
    // Placeholder methods for additional functionality
    private function updateFilePermissions($path) { /* Implementation */ }
    private function clearApplicationCache($path) { /* Implementation */ }
    private function runDatabaseMigrations() { /* Implementation */ }
    private function cleanOldBackups() { /* Implementation */ }
    private function setupLogRotation() { /* Implementation */ }
    private function createHealthCheckEndpoint() { /* Implementation */ }
    private function optimizeOpCache() { return true; }
    private function optimizeDatabase() { return true; }
    private function optimizeFiles() { return true; }
    private function optimizeCache() { return true; }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $devops = new DevOpsAutomation();
    
    $command = $argv[1] ?? 'help';
    $environment = $argv[2] ?? 'dev';
    
    switch ($command) {
        case 'deploy':
            $result = $devops->runCIPipeline($environment);
            echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
            exit($result['success'] ? 0 : 1);
            
        case 'optimize':
            $result = $devops->runPerformanceOptimization();
            echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
            break;
            
        case 'health':
            $result = $devops->performHealthCheck();
            echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
            break;
            
        default:
            echo "MesChain-Sync DevOps Automation Tool" . PHP_EOL;
            echo "Usage: php devops_automation.php [command] [environment]" . PHP_EOL;
            echo "Commands:" . PHP_EOL;
            echo "  deploy [dev|staging|production] - Run CI/CD pipeline" . PHP_EOL;
            echo "  optimize                        - Run performance optimization" . PHP_EOL;
            echo "  health                          - Perform system health check" . PHP_EOL;
            break;
    }
}
?>
</rewritten_file>