<?php
/**
 * MeschainMonitoringHelper - Sistem Monitoring ve Health Check
 * 
 * Sistem performansı, sağlık durumu ve metrik toplaması
 * 
 * @author MesChain Development Team
 * @version 1.0.0
 * @since 2024-01-21
 */

class MeschainMonitoringHelper {
    
    private $registry;
    private $db;
    private $log;
    private $cache;
    private $startTime;
    
    // Health check türleri
    const CHECK_DATABASE = 'database';
    const CHECK_API = 'api';
    const CHECK_CACHE = 'cache';
    const CHECK_STORAGE = 'storage';
    const CHECK_MARKETPLACE = 'marketplace';
    const CHECK_EVENTS = 'events';
    
    // Alert seviyeleri
    const ALERT_INFO = 'info';
    const ALERT_WARNING = 'warning';
    const ALERT_ERROR = 'error';
    const ALERT_CRITICAL = 'critical';
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->log = new Log('meschain_monitoring.log');
        $this->cache = $registry->get('cache');
        $this->startTime = microtime(true);
        
        $this->createMonitoringTables();
    }
    
    /**
     * Monitoring tablolarını oluştur
     */
    private function createMonitoringTables() {
        // Sistem metrikleri tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_system_metrics` (
            `metric_id` int(11) NOT NULL AUTO_INCREMENT,
            `metric_name` varchar(100) NOT NULL,
            `metric_value` decimal(15,4) NOT NULL,
            `metric_unit` varchar(20) DEFAULT NULL,
            `tenant_id` int(11) DEFAULT NULL,
            `tags` json,
            `timestamp` datetime NOT NULL,
            PRIMARY KEY (`metric_id`),
            KEY `metric_name` (`metric_name`),
            KEY `timestamp` (`timestamp`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Health check sonuçları tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_health_checks` (
            `check_id` int(11) NOT NULL AUTO_INCREMENT,
            `check_name` varchar(100) NOT NULL,
            `check_type` varchar(50) NOT NULL,
            `status` enum('healthy','warning','error','critical') NOT NULL,
            `response_time` decimal(10,4) DEFAULT NULL,
            `error_message` text,
            `details` json,
            `checked_at` datetime NOT NULL,
            PRIMARY KEY (`check_id`),
            KEY `check_name` (`check_name`),
            KEY `status` (`status`),
            KEY `checked_at` (`checked_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Performans logları tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_performance_logs` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `request_url` varchar(500),
            `request_method` varchar(10),
            `controller` varchar(100),
            `action` varchar(100),
            `execution_time` decimal(10,4) NOT NULL,
            `memory_usage` int(11) DEFAULT NULL,
            `database_queries` int(11) DEFAULT NULL,
            `api_calls` int(11) DEFAULT NULL,
            `user_id` int(11) DEFAULT NULL,
            `tenant_id` int(11) DEFAULT NULL,
            `ip_address` varchar(45),
            `user_agent` varchar(255),
            `timestamp` datetime NOT NULL,
            PRIMARY KEY (`log_id`),
            KEY `controller` (`controller`),
            KEY `execution_time` (`execution_time`),
            KEY `timestamp` (`timestamp`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Alert rules tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_alert_rules` (
            `rule_id` int(11) NOT NULL AUTO_INCREMENT,
            `rule_name` varchar(100) NOT NULL,
            `metric_name` varchar(100) NOT NULL,
            `condition_type` enum('greater_than','less_than','equals','not_equals') NOT NULL,
            `threshold_value` decimal(15,4) NOT NULL,
            `severity` enum('info','warning','error','critical') NOT NULL,
            `notification_channels` json,
            `is_active` tinyint(1) DEFAULT 1,
            `tenant_id` int(11) DEFAULT NULL,
            `created_by` int(11) DEFAULT NULL,
            `date_created` datetime NOT NULL,
            PRIMARY KEY (`rule_id`),
            KEY `metric_name` (`metric_name`),
            KEY `is_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Alert geçmişi tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_alerts` (
            `alert_id` int(11) NOT NULL AUTO_INCREMENT,
            `rule_id` int(11) NOT NULL,
            `metric_value` decimal(15,4) NOT NULL,
            `threshold_value` decimal(15,4) NOT NULL,
            `severity` enum('info','warning','error','critical') NOT NULL,
            `message` text NOT NULL,
            `status` enum('open','acknowledged','resolved') DEFAULT 'open',
            `acknowledged_by` int(11) DEFAULT NULL,
            `acknowledged_at` datetime DEFAULT NULL,
            `resolved_at` datetime DEFAULT NULL,
            `tenant_id` int(11) DEFAULT NULL,
            `triggered_at` datetime NOT NULL,
            PRIMARY KEY (`alert_id`),
            KEY `rule_id` (`rule_id`),
            KEY `severity` (`severity`),
            KEY `status` (`status`),
            KEY `triggered_at` (`triggered_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        $this->log->write('Monitoring tabloları oluşturuldu/kontrol edildi');
    }
    
    /**
     * Sistem metriği kaydet
     */
    public function recordMetric($name, $value, $unit = null, $tags = [], $tenantId = null) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_system_metrics` SET
            metric_name = '" . $this->db->escape($name) . "',
            metric_value = " . (float)$value . ",
            metric_unit = " . ($unit ? "'" . $this->db->escape($unit) . "'" : "NULL") . ",
            tenant_id = " . ($tenantId ? (int)$tenantId : "NULL") . ",
            tags = '" . $this->db->escape(json_encode($tags)) . "',
            timestamp = NOW()
        ");
        
        // Alert kurallarını kontrol et
        $this->checkAlertRules($name, $value, $tenantId);
        
        return true;
    }
    
    /**
     * Performance metriği kaydet
     */
    public function recordPerformance($data) {
        $executionTime = $data['execution_time'] ?? (microtime(true) - $this->startTime);
        $memoryUsage = $data['memory_usage'] ?? memory_get_usage(true);
        
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_performance_logs` SET
            request_url = '" . $this->db->escape($data['url'] ?? $_SERVER['REQUEST_URI'] ?? '') . "',
            request_method = '" . $this->db->escape($data['method'] ?? $_SERVER['REQUEST_METHOD'] ?? '') . "',
            controller = '" . $this->db->escape($data['controller'] ?? '') . "',
            action = '" . $this->db->escape($data['action'] ?? '') . "',
            execution_time = " . (float)$executionTime . ",
            memory_usage = " . (int)$memoryUsage . ",
            database_queries = " . (int)($data['db_queries'] ?? 0) . ",
            api_calls = " . (int)($data['api_calls'] ?? 0) . ",
            user_id = " . (int)$this->getCurrentUserId() . ",
            tenant_id = " . (int)$this->getCurrentTenantId() . ",
            ip_address = '" . $this->db->escape($_SERVER['REMOTE_ADDR'] ?? '') . "',
            user_agent = '" . $this->db->escape(substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)) . "',
            timestamp = NOW()
        ");
        
        // Performans metriklerini kaydet
        $this->recordMetric('response_time', $executionTime, 'seconds', ['controller' => $data['controller'] ?? '']);
        $this->recordMetric('memory_usage', $memoryUsage, 'bytes');
        
        return true;
    }
    
    /**
     * Kapsamlı sistem health check
     */
    public function runHealthCheck() {
        $checks = [
            $this->checkDatabase(),
            $this->checkCache(),
            $this->checkStorage(),
            $this->checkMarketplaceAPIs(),
            $this->checkEventQueue(),
            $this->checkSystemResources()
        ];
        
        $overallStatus = 'healthy';
        $healthyCount = 0;
        $warningCount = 0;
        $errorCount = 0;
        $criticalCount = 0;
        
        foreach ($checks as $check) {
            switch ($check['status']) {
                case 'healthy':
                    $healthyCount++;
                    break;
                case 'warning':
                    $warningCount++;
                    if ($overallStatus === 'healthy') $overallStatus = 'warning';
                    break;
                case 'error':
                    $errorCount++;
                    if (in_array($overallStatus, ['healthy', 'warning'])) $overallStatus = 'error';
                    break;
                case 'critical':
                    $criticalCount++;
                    $overallStatus = 'critical';
                    break;
            }
        }
        
        $summary = [
            'status' => $overallStatus,
            'timestamp' => date('Y-m-d H:i:s'),
            'total_checks' => count($checks),
            'healthy' => $healthyCount,
            'warning' => $warningCount,
            'error' => $errorCount,
            'critical' => $criticalCount,
            'checks' => $checks
        ];
        
        // Cache'le
        if ($this->cache) {
            $this->cache->set('system_health_status', $summary, 300); // 5 dakika
        }
        
        return $summary;
    }
    
    /**
     * Database health check
     */
    private function checkDatabase() {
        $startTime = microtime(true);
        $status = 'healthy';
        $details = [];
        $errorMessage = null;
        
        try {
            // Connection test
            $result = $this->db->query("SELECT 1 as test");
            if (!$result->num_rows) {
                throw new Exception('Database connection failed');
            }
            
            // Query performance test
            $testStart = microtime(true);
            $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "user`");
            $queryTime = microtime(true) - $testStart;
            
            if ($queryTime > 1.0) {
                $status = 'warning';
                $details['slow_query'] = true;
            }
            
            // Table existence check
            $tables = ['user', 'product', 'order', 'meschain_tenants'];
            foreach ($tables as $table) {
                $result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
                if (!$result->num_rows) {
                    $status = 'error';
                    $details['missing_tables'][] = $table;
                }
            }
            
            $details['query_time'] = $queryTime;
            
        } catch (Exception $e) {
            $status = 'critical';
            $errorMessage = $e->getMessage();
        }
        
        $responseTime = microtime(true) - $startTime;
        
        $check = [
            'name' => 'Database',
            'type' => self::CHECK_DATABASE,
            'status' => $status,
            'response_time' => $responseTime,
            'error_message' => $errorMessage,
            'details' => $details
        ];
        
        $this->saveHealthCheck($check);
        
        return $check;
    }
    
    /**
     * Cache health check
     */
    private function checkCache() {
        $startTime = microtime(true);
        $status = 'healthy';
        $details = [];
        $errorMessage = null;
        
        try {
            if ($this->cache) {
                // Write test
                $testKey = 'health_check_test';
                $testValue = time();
                
                $writeResult = $this->cache->set($testKey, $testValue, 60);
                if (!$writeResult) {
                    $status = 'warning';
                    $details['write_failed'] = true;
                }
                
                // Read test
                $readValue = $this->cache->get($testKey);
                if ($readValue !== $testValue) {
                    $status = 'warning';
                    $details['read_failed'] = true;
                }
                
                // Delete test
                $this->cache->delete($testKey);
                
                $details['cache_enabled'] = true;
            } else {
                $status = 'warning';
                $details['cache_enabled'] = false;
            }
            
        } catch (Exception $e) {
            $status = 'error';
            $errorMessage = $e->getMessage();
        }
        
        $responseTime = microtime(true) - $startTime;
        
        $check = [
            'name' => 'Cache',
            'type' => self::CHECK_CACHE,
            'status' => $status,
            'response_time' => $responseTime,
            'error_message' => $errorMessage,
            'details' => $details
        ];
        
        $this->saveHealthCheck($check);
        
        return $check;
    }
    
    /**
     * Storage health check
     */
    private function checkStorage() {
        $startTime = microtime(true);
        $status = 'healthy';
        $details = [];
        $errorMessage = null;
        
        try {
            $paths = [
                'logs' => DIR_LOGS,
                'image' => DIR_IMAGE,
                'storage' => DIR_STORAGE
            ];
            
            foreach ($paths as $name => $path) {
                if (!is_dir($path)) {
                    $status = 'error';
                    $details['missing_directories'][] = $name;
                } elseif (!is_writable($path)) {
                    $status = 'error';
                    $details['readonly_directories'][] = $name;
                } else {
                    // Disk space check
                    $freeSpace = disk_free_space($path);
                    $totalSpace = disk_total_space($path);
                    $usagePercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;
                    
                    if ($usagePercent > 90) {
                        $status = 'critical';
                        $details['disk_usage_critical'][] = $name;
                    } elseif ($usagePercent > 80) {
                        $status = 'warning';
                        $details['disk_usage_warning'][] = $name;
                    }
                    
                    $details['disk_usage'][$name] = [
                        'free' => $freeSpace,
                        'total' => $totalSpace,
                        'usage_percent' => round($usagePercent, 2)
                    ];
                }
            }
            
        } catch (Exception $e) {
            $status = 'error';
            $errorMessage = $e->getMessage();
        }
        
        $responseTime = microtime(true) - $startTime;
        
        $check = [
            'name' => 'Storage',
            'type' => self::CHECK_STORAGE,
            'status' => $status,
            'response_time' => $responseTime,
            'error_message' => $errorMessage,
            'details' => $details
        ];
        
        $this->saveHealthCheck($check);
        
        return $check;
    }
    
    /**
     * Marketplace API health check
     */
    private function checkMarketplaceAPIs() {
        $startTime = microtime(true);
        $status = 'healthy';
        $details = [];
        $errorMessage = null;
        
        $marketplaces = ['trendyol', 'n11', 'amazon', 'hepsiburada', 'ozon', 'ebay'];
        $healthyCount = 0;
        $totalCount = 0;
        
        foreach ($marketplaces as $marketplace) {
            try {
                $helperFile = DIR_SYSTEM . 'library/meschain/helper/' . $marketplace . '.php';
                if (file_exists($helperFile)) {
                    require_once($helperFile);
                    $className = 'Meschain' . ucfirst($marketplace) . 'Helper';
                    
                    if (class_exists($className)) {
                        $helper = new $className($this->registry);
                        
                        if (method_exists($helper, 'healthCheck')) {
                            $result = $helper->healthCheck();
                            $details['marketplaces'][$marketplace] = $result;
                            
                            if ($result['status'] === 'healthy') {
                                $healthyCount++;
                            }
                            $totalCount++;
                        }
                    }
                }
            } catch (Exception $e) {
                $details['marketplaces'][$marketplace] = [
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
                $totalCount++;
            }
        }
        
        if ($healthyCount === 0 && $totalCount > 0) {
            $status = 'critical';
        } elseif ($healthyCount < $totalCount * 0.5) {
            $status = 'error';
        } elseif ($healthyCount < $totalCount) {
            $status = 'warning';
        }
        
        $details['healthy_count'] = $healthyCount;
        $details['total_count'] = $totalCount;
        
        $responseTime = microtime(true) - $startTime;
        
        $check = [
            'name' => 'Marketplace APIs',
            'type' => self::CHECK_MARKETPLACE,
            'status' => $status,
            'response_time' => $responseTime,
            'error_message' => $errorMessage,
            'details' => $details
        ];
        
        $this->saveHealthCheck($check);
        
        return $check;
    }
    
    /**
     * Event queue health check
     */
    private function checkEventQueue() {
        $startTime = microtime(true);
        $status = 'healthy';
        $details = [];
        $errorMessage = null;
        
        try {
            // Pending events count
            $pendingQuery = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_event_queue` WHERE status = 'pending'");
            $pendingCount = $pendingQuery->row['count'];
            
            // Failed events count
            $failedQuery = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_event_queue` WHERE status = 'failed'");
            $failedCount = $failedQuery->row['count'];
            
            // Processing time check
            $processingQuery = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_event_queue` WHERE status = 'processing' AND started_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            $stuckCount = $processingQuery->row['count'];
            
            $details['pending_events'] = $pendingCount;
            $details['failed_events'] = $failedCount;
            $details['stuck_events'] = $stuckCount;
            
            if ($stuckCount > 0) {
                $status = 'error';
            } elseif ($failedCount > 10) {
                $status = 'warning';
            } elseif ($pendingCount > 100) {
                $status = 'warning';
            }
            
        } catch (Exception $e) {
            $status = 'error';
            $errorMessage = $e->getMessage();
        }
        
        $responseTime = microtime(true) - $startTime;
        
        $check = [
            'name' => 'Event Queue',
            'type' => self::CHECK_EVENTS,
            'status' => $status,
            'response_time' => $responseTime,
            'error_message' => $errorMessage,
            'details' => $details
        ];
        
        $this->saveHealthCheck($check);
        
        return $check;
    }
    
    /**
     * Sistem kaynaklarını kontrol et
     */
    private function checkSystemResources() {
        $startTime = microtime(true);
        $status = 'healthy';
        $details = [];
        $errorMessage = null;
        
        try {
            // Memory usage
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = $this->parseBytes(ini_get('memory_limit'));
            $memoryPercent = ($memoryUsage / $memoryLimit) * 100;
            
            // CPU load (Linux only)
            $cpuLoad = null;
            if (function_exists('sys_getloadavg')) {
                $load = sys_getloadavg();
                $cpuLoad = $load[0];
            }
            
            $details['memory'] = [
                'usage' => $memoryUsage,
                'limit' => $memoryLimit,
                'percent' => round($memoryPercent, 2)
            ];
            
            if ($cpuLoad !== null) {
                $details['cpu_load'] = $cpuLoad;
            }
            
            // Alert conditions
            if ($memoryPercent > 90) {
                $status = 'critical';
            } elseif ($memoryPercent > 80) {
                $status = 'warning';
            }
            
            if ($cpuLoad !== null && $cpuLoad > 5.0) {
                $status = 'warning';
            }
            
        } catch (Exception $e) {
            $status = 'error';
            $errorMessage = $e->getMessage();
        }
        
        $responseTime = microtime(true) - $startTime;
        
        $check = [
            'name' => 'System Resources',
            'type' => 'system',
            'status' => $status,
            'response_time' => $responseTime,
            'error_message' => $errorMessage,
            'details' => $details
        ];
        
        $this->saveHealthCheck($check);
        
        return $check;
    }
    
    /**
     * Health check sonucunu kaydet
     */
    private function saveHealthCheck($check) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_health_checks` SET
            check_name = '" . $this->db->escape($check['name']) . "',
            check_type = '" . $this->db->escape($check['type']) . "',
            status = '" . $this->db->escape($check['status']) . "',
            response_time = " . (float)$check['response_time'] . ",
            error_message = " . ($check['error_message'] ? "'" . $this->db->escape($check['error_message']) . "'" : "NULL") . ",
            details = '" . $this->db->escape(json_encode($check['details'])) . "',
            checked_at = NOW()
        ");
    }
    
    /**
     * Alert kurallarını kontrol et
     */
    private function checkAlertRules($metricName, $value, $tenantId = null) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_alert_rules` 
                WHERE metric_name = '" . $this->db->escape($metricName) . "'
                AND is_active = 1";
        
        if ($tenantId) {
            $sql .= " AND (tenant_id = " . (int)$tenantId . " OR tenant_id IS NULL)";
        }
        
        $query = $this->db->query($sql);
        
        foreach ($query->rows as $rule) {
            $triggered = false;
            
            switch ($rule['condition_type']) {
                case 'greater_than':
                    $triggered = $value > $rule['threshold_value'];
                    break;
                case 'less_than':
                    $triggered = $value < $rule['threshold_value'];
                    break;
                case 'equals':
                    $triggered = $value == $rule['threshold_value'];
                    break;
                case 'not_equals':
                    $triggered = $value != $rule['threshold_value'];
                    break;
            }
            
            if ($triggered) {
                $this->triggerAlert($rule, $value);
            }
        }
    }
    
    /**
     * Alert tetikle
     */
    private function triggerAlert($rule, $currentValue) {
        // Son 5 dakikada aynı rule için alert var mı kontrol et
        $recentAlert = $this->db->query("SELECT alert_id FROM `" . DB_PREFIX . "meschain_alerts` 
            WHERE rule_id = " . (int)$rule['rule_id'] . "
            AND triggered_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            AND status = 'open'");
        
        if ($recentAlert->num_rows > 0) {
            return; // Duplicate alert önlemi
        }
        
        $message = "Alert: {$rule['rule_name']} - {$rule['metric_name']} değeri {$currentValue} (eşik: {$rule['threshold_value']})";
        
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_alerts` SET
            rule_id = " . (int)$rule['rule_id'] . ",
            metric_value = " . (float)$currentValue . ",
            threshold_value = " . (float)$rule['threshold_value'] . ",
            severity = '" . $this->db->escape($rule['severity']) . "',
            message = '" . $this->db->escape($message) . "',
            tenant_id = " . ($rule['tenant_id'] ? (int)$rule['tenant_id'] : "NULL") . ",
            triggered_at = NOW()
        ");
        
        $this->log->write("Alert tetiklendi: {$message}");
        
        // Notification gönder
        $this->sendAlertNotification($rule, $message);
    }
    
    /**
     * Alert notification gönder
     */
    private function sendAlertNotification($rule, $message) {
        $channels = json_decode($rule['notification_channels'], true) ?? [];
        
        foreach ($channels as $channel) {
            switch ($channel['type']) {
                case 'email':
                    // Email notification
                    break;
                case 'webhook':
                    // Webhook notification
                    break;
                case 'log':
                    $this->log->write("ALERT [{$rule['severity']}]: {$message}");
                    break;
            }
        }
    }
    
    /**
     * Bytes parse et
     */
    private function parseBytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (int)$val;
        
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        
        return $val;
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
    
    /**
     * Sistem istatistiklerini al
     */
    public function getSystemStats($timeframe = '24 hours') {
        $stats = [];
        
        // Performance stats
        $perfQuery = $this->db->query("SELECT 
            AVG(execution_time) as avg_response_time,
            MAX(execution_time) as max_response_time,
            AVG(memory_usage) as avg_memory_usage,
            COUNT(*) as total_requests
            FROM `" . DB_PREFIX . "meschain_performance_logs` 
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL {$timeframe})");
        
        $stats['performance'] = $perfQuery->row;
        
        // Health check stats
        $healthQuery = $this->db->query("SELECT 
            status,
            COUNT(*) as count
            FROM `" . DB_PREFIX . "meschain_health_checks` 
            WHERE checked_at >= DATE_SUB(NOW(), INTERVAL {$timeframe})
            GROUP BY status");
        
        $stats['health'] = [];
        foreach ($healthQuery->rows as $row) {
            $stats['health'][$row['status']] = $row['count'];
        }
        
        // Alert stats
        $alertQuery = $this->db->query("SELECT 
            severity,
            COUNT(*) as count
            FROM `" . DB_PREFIX . "meschain_alerts` 
            WHERE triggered_at >= DATE_SUB(NOW(), INTERVAL {$timeframe})
            GROUP BY severity");
        
        $stats['alerts'] = [];
        foreach ($alertQuery->rows as $row) {
            $stats['alerts'][$row['severity']] = $row['count'];
        }
        
        return $stats;
    }
}
?> 