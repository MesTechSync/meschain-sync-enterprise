<?php
/**
 * MesChain Performance Monitor
 * Real-time performance tracking ve optimization sistemi
 * 
 * @author Musti DevOps Team
 * @version 2.0
 */
class PerformanceMonitor {
    
    private $registry;
    private $db;
    private $log;
    private $startTime;
    private $memoryStart;
    private $metrics = [];
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->log = $registry->get('log');
        $this->startTime = microtime(true);
        $this->memoryStart = memory_get_usage(true);
    }
    
    /**
     * API call performance tracking başlat
     */
    public function startApiCall($marketplace, $endpoint) {
        $callId = uniqid($marketplace . '_', true);
        
        $this->metrics[$callId] = [
            'marketplace' => $marketplace,
            'endpoint' => $endpoint,
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true),
            'cpu_start' => $this->getCpuUsage()
        ];
        
        return $callId;
    }
    
    /**
     * API call performance tracking bitir
     */
    public function endApiCall($callId, $responseSize = 0, $statusCode = 200) {
        if (!isset($this->metrics[$callId])) {
            return false;
        }
        
        $metric = &$this->metrics[$callId];
        $metric['end_time'] = microtime(true);
        $metric['end_memory'] = memory_get_usage(true);
        $metric['cpu_end'] = $this->getCpuUsage();
        
        // Hesaplamalar
        $metric['execution_time'] = $metric['end_time'] - $metric['start_time'];
        $metric['memory_usage'] = $metric['end_memory'] - $metric['start_memory'];
        $metric['cpu_usage'] = $metric['cpu_end'] - $metric['cpu_start'];
        $metric['response_size'] = $responseSize;
        $metric['status_code'] = $statusCode;
        $metric['timestamp'] = date('Y-m-d H:i:s');
        
        // Performance warning kontrolü
        if ($metric['execution_time'] > 5.0) {
            $this->logPerformanceWarning('SLOW_API_CALL', $metric);
        }
        
        if ($metric['memory_usage'] > 50 * 1024 * 1024) { // 50MB
            $this->logPerformanceWarning('HIGH_MEMORY_USAGE', $metric);
        }
        
        // Database'e kaydet
        $this->saveMetricToDatabase($metric);
        
        return $metric;
    }
    
    /**
     * Database query performance tracking
     */
    public function trackDatabaseQuery($query, $executionTime, $affectedRows = 0) {
        $metric = [
            'type' => 'database_query',
            'query' => substr($query, 0, 500), // İlk 500 karakter
            'execution_time' => $executionTime,
            'affected_rows' => $affectedRows,
            'timestamp' => date('Y-m-d H:i:s'),
            'memory_usage' => memory_get_usage(true)
        ];
        
        // Slow query kontrolü
        if ($executionTime > 2.0) {
            $this->logPerformanceWarning('SLOW_QUERY', $metric);
        }
        
        $this->saveQueryMetric($metric);
        
        return $metric;
    }
    
    /**
     * System resource monitoring
     */
    public function getSystemMetrics() {
        $metrics = [
            'timestamp' => date('Y-m-d H:i:s'),
            'memory' => [
                'current_usage' => memory_get_usage(true),
                'peak_usage' => memory_get_peak_usage(true),
                'limit' => $this->getMemoryLimit(),
                'usage_percentage' => $this->getMemoryUsagePercentage()
            ],
            'cpu' => [
                'usage' => $this->getCpuUsage(),
                'load_average' => $this->getLoadAverage()
            ],
            'disk' => [
                'free_space' => disk_free_space('.'),
                'total_space' => disk_total_space('.'),
                'usage_percentage' => $this->getDiskUsagePercentage()
            ],
            'database' => [
                'active_connections' => $this->getActiveDbConnections(),
                'slow_queries' => $this->getSlowQueryCount(),
                'cache_hit_ratio' => $this->getCacheHitRatio()
            ]
        ];
        
        // Critical thresholds check
        if ($metrics['memory']['usage_percentage'] > 85) {
            $this->logPerformanceWarning('HIGH_MEMORY_SYSTEM', $metrics['memory']);
        }
        
        if ($metrics['disk']['usage_percentage'] > 90) {
            $this->logPerformanceWarning('LOW_DISK_SPACE', $metrics['disk']);
        }
        
        // Save system metrics
        $this->saveSystemMetrics($metrics);
        
        return $metrics;
    }
    
    /**
     * API rate limiting monitoring
     */
    public function trackRateLimit($marketplace, $endpoint, $remaining, $limit, $resetTime) {
        $metric = [
            'marketplace' => $marketplace,
            'endpoint' => $endpoint,
            'requests_remaining' => $remaining,
            'requests_limit' => $limit,
            'reset_time' => $resetTime,
            'usage_percentage' => (($limit - $remaining) / $limit) * 100,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Rate limit warning
        if ($metric['usage_percentage'] > 80) {
            $this->logPerformanceWarning('RATE_LIMIT_WARNING', $metric);
        }
        
        $this->saveRateLimitMetric($metric);
        
        return $metric;
    }
    
    /**
     * Cache performance monitoring
     */
    public function trackCacheOperation($operation, $key, $hit = true, $size = 0) {
        $metric = [
            'operation' => $operation, // get, set, delete
            'cache_key' => $key,
            'hit' => $hit,
            'size' => $size,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->saveCacheMetric($metric);
        
        return $metric;
    }
    
    /**
     * Performance dashboard data
     */
    public function getDashboardMetrics($hours = 24) {
        $sql = "
            SELECT 
                marketplace,
                AVG(execution_time) as avg_response_time,
                MAX(execution_time) as max_response_time,
                COUNT(*) as total_calls,
                SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count,
                AVG(memory_usage) as avg_memory_usage
            FROM " . DB_PREFIX . "meschain_performance_metrics 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL $hours HOUR)
            GROUP BY marketplace
        ";
        
        $query = $this->db->query($sql);
        
        $dashboardData = [
            'api_performance' => $query->rows,
            'system_health' => $this->getSystemHealth(),
            'top_slow_endpoints' => $this->getSlowEndpoints($hours),
            'error_rate_trend' => $this->getErrorRateTrend($hours),
            'memory_trend' => $this->getMemoryTrend($hours)
        ];
        
        return $dashboardData;
    }
    
    /**
     * Automatic performance optimization
     */
    public function runPerformanceOptimization() {
        $optimizations = [];
        
        // 1. OpCache optimization
        if (function_exists('opcache_get_status')) {
            $opcacheStatus = opcache_get_status();
            if ($opcacheStatus['opcache_statistics']['hit_rate'] < 85) {
                $optimizations[] = $this->optimizeOpCache();
            }
        }
        
        // 2. Database optimization
        $slowQueries = $this->getRecentSlowQueries();
        if (count($slowQueries) > 10) {
            $optimizations[] = $this->optimizeDatabase();
        }
        
        // 3. Memory optimization
        $memoryUsage = $this->getMemoryUsagePercentage();
        if ($memoryUsage > 80) {
            $optimizations[] = $this->optimizeMemory();
        }
        
        // 4. Cache optimization
        $cacheStats = $this->getCacheStatistics();
        if ($cacheStats['hit_ratio'] < 70) {
            $optimizations[] = $this->optimizeCache();
        }
        
        return $optimizations;
    }
    
    /**
     * Performance alerts
     */
    public function checkPerformanceAlerts() {
        $alerts = [];
        
        // Critical response time
        $slowEndpoints = $this->getSlowEndpoints(1); // Son 1 saat
        if (!empty($slowEndpoints)) {
            $alerts[] = [
                'level' => 'critical',
                'type' => 'slow_response',
                'message' => 'Slow API endpoints detected',
                'data' => $slowEndpoints
            ];
        }
        
        // High error rate
        $errorRate = $this->getErrorRate(1);
        if ($errorRate > 5) {
            $alerts[] = [
                'level' => 'warning',
                'type' => 'high_error_rate',
                'message' => "Error rate is $errorRate%",
                'data' => ['error_rate' => $errorRate]
            ];
        }
        
        // System resource alerts
        $systemMetrics = $this->getSystemMetrics();
        if ($systemMetrics['memory']['usage_percentage'] > 90) {
            $alerts[] = [
                'level' => 'critical',
                'type' => 'high_memory',
                'message' => 'Memory usage critical',
                'data' => $systemMetrics['memory']
            ];
        }
        
        return $alerts;
    }
    
    // Private helper methods
    private function getCpuUsage() {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0];
        }
        return 0;
    }
    
    private function getLoadAverage() {
        if (function_exists('sys_getloadavg')) {
            return sys_getloadavg();
        }
        return [0, 0, 0];
    }
    
    private function getMemoryLimit() {
        $limit = ini_get('memory_limit');
        return $this->convertToBytes($limit);
    }
    
    private function getMemoryUsagePercentage() {
        $current = memory_get_usage(true);
        $limit = $this->getMemoryLimit();
        return ($current / $limit) * 100;
    }
    
    private function getDiskUsagePercentage() {
        $free = disk_free_space('.');
        $total = disk_total_space('.');
        return (($total - $free) / $total) * 100;
    }
    
    private function convertToBytes($value) {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;
        
        switch ($last) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        
        return $value;
    }
    
    private function logPerformanceWarning($type, $data) {
        $message = "Performance Warning [$type]: " . json_encode($data);
        $this->log->write($message);
        
        // Send to monitoring system
        $this->sendToMonitoringSystem($type, $data);
    }
    
    private function saveMetricToDatabase($metric) {
        try {
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "meschain_performance_metrics SET
                marketplace = '" . $this->db->escape($metric['marketplace']) . "',
                endpoint = '" . $this->db->escape($metric['endpoint']) . "',
                execution_time = '" . (float)$metric['execution_time'] . "',
                memory_usage = '" . (int)$metric['memory_usage'] . "',
                cpu_usage = '" . (float)$metric['cpu_usage'] . "',
                response_size = '" . (int)$metric['response_size'] . "',
                status_code = '" . (int)$metric['status_code'] . "',
                created_at = NOW()
            ");
        } catch (Exception $e) {
            error_log('Performance Monitor DB Error: ' . $e->getMessage());
        }
    }
    
    private function sendToMonitoringSystem($type, $data) {
        // Integration with external monitoring systems
        // Slack, Discord, email notifications vs.
    }
    
    // Database helper methods placeholder
    private function getActiveDbConnections() { return 0; }
    private function getSlowQueryCount() { return 0; }
    private function getCacheHitRatio() { return 95; }
    private function getSystemHealth() { return 'good'; }
    private function getSlowEndpoints($hours) { return []; }
    private function getErrorRateTrend($hours) { return []; }
    private function getMemoryTrend($hours) { return []; }
    private function getRecentSlowQueries() { return []; }
    private function getErrorRate($hours) { return 0; }
    private function optimizeOpCache() { return 'OpCache optimized'; }
    private function optimizeDatabase() { return 'Database optimized'; }
    private function optimizeMemory() { return 'Memory optimized'; }
    private function optimizeCache() { return 'Cache optimized'; }
    private function getCacheStatistics() { return ['hit_ratio' => 85]; }
    private function saveQueryMetric($metric) { }
    private function saveSystemMetrics($metrics) { }
    private function saveRateLimitMetric($metric) { }
    private function saveCacheMetric($metric) { }
}
?>