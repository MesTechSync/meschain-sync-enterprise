<?php
/**
 * Advanced Production Monitor Model - ATOM-M007
 * MesChain-Sync Enterprise Production Excellence
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0 - ATOM-M007
 * @author Musti DevOps Team
 * @date 2025-06-06
 */

class ModelExtensionModuleAdvancedProductionMonitor extends Model {
    
    /**
     * Create monitoring metrics table if not exists
     */
    public function createTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_monitoring_metrics` (
                `metric_id` int(11) NOT NULL AUTO_INCREMENT,
                `type` varchar(50) NOT NULL,
                `data` longtext NOT NULL,
                `timestamp` datetime NOT NULL,
                `processed` tinyint(1) DEFAULT 0,
                PRIMARY KEY (`metric_id`),
                KEY `idx_type` (`type`),
                KEY `idx_timestamp` (`timestamp`),
                KEY `idx_processed` (`processed`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create alerts table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_monitoring_alerts` (
                `alert_id` int(11) NOT NULL AUTO_INCREMENT,
                `alert_type` varchar(50) NOT NULL,
                `severity` enum('info','warning','error','critical') NOT NULL,
                `title` varchar(255) NOT NULL,
                `message` text NOT NULL,
                `data` text,
                `status` enum('open','acknowledged','resolved') DEFAULT 'open',
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `resolved_at` datetime DEFAULT NULL,
                PRIMARY KEY (`alert_id`),
                KEY `idx_type` (`alert_type`),
                KEY `idx_severity` (`severity`),
                KEY `idx_status` (`status`),
                KEY `idx_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create performance snapshots table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_performance_snapshots` (
                `snapshot_id` int(11) NOT NULL AUTO_INCREMENT,
                `system_health_score` decimal(5,2) NOT NULL,
                `api_response_avg` int(11) NOT NULL,
                `database_response_avg` int(11) NOT NULL,
                `memory_usage_percent` decimal(5,2) NOT NULL,
                `cpu_usage_percent` decimal(5,2) NOT NULL,
                `active_users` int(11) NOT NULL,
                `error_rate` decimal(5,4) NOT NULL,
                `uptime_percent` decimal(8,5) NOT NULL,
                `marketplace_status` json,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`snapshot_id`),
                KEY `idx_created_at` (`created_at`),
                KEY `idx_health_score` (`system_health_score`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    /**
     * Get historical metrics data
     *
     * @param string $type Metric type
     * @param int $hours Hours to look back
     * @return array
     */
    public function getHistoricalMetrics($type, $hours = 24) {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_monitoring_metrics 
            WHERE type = '" . $this->db->escape($type) . "' 
            AND timestamp >= DATE_SUB(NOW(), INTERVAL " . (int)$hours . " HOUR)
            ORDER BY timestamp ASC
        ");
        
        return $query->rows;
    }
    
    /**
     * Get performance trend data
     *
     * @param int $hours Hours to analyze
     * @return array
     */
    public function getPerformanceTrends($hours = 24) {
        $query = $this->db->query("
            SELECT 
                DATE_FORMAT(created_at, '%H:%i') as time_label,
                AVG(system_health_score) as avg_health_score,
                AVG(api_response_avg) as avg_api_response,
                AVG(database_response_avg) as avg_db_response,
                AVG(memory_usage_percent) as avg_memory_usage,
                AVG(cpu_usage_percent) as avg_cpu_usage,
                AVG(active_users) as avg_active_users,
                AVG(error_rate) as avg_error_rate
            FROM " . DB_PREFIX . "meschain_performance_snapshots 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL " . (int)$hours . " HOUR)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d %H:%i')
            ORDER BY created_at ASC
        ");
        
        return $query->rows;
    }
    
    /**
     * Store performance snapshot
     *
     * @param array $metrics Performance metrics
     * @return int Snapshot ID
     */
    public function storePerformanceSnapshot($metrics) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_performance_snapshots 
            (system_health_score, api_response_avg, database_response_avg, 
             memory_usage_percent, cpu_usage_percent, active_users, 
             error_rate, uptime_percent, marketplace_status, created_at) 
            VALUES (
                '" . (float)($metrics['health_score'] ?? 0) . "',
                '" . (int)($metrics['api_response_avg'] ?? 0) . "',
                '" . (int)($metrics['database_response_avg'] ?? 0) . "',
                '" . (float)($metrics['memory_usage_percent'] ?? 0) . "',
                '" . (float)($metrics['cpu_usage_percent'] ?? 0) . "',
                '" . (int)($metrics['active_users'] ?? 0) . "',
                '" . (float)($metrics['error_rate'] ?? 0) . "',
                '" . (float)($metrics['uptime_percent'] ?? 0) . "',
                '" . $this->db->escape(json_encode($metrics['marketplace_status'] ?? [])) . "',
                NOW()
            )
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Create alert
     *
     * @param array $alert_data Alert information
     * @return int Alert ID
     */
    public function createAlert($alert_data) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_monitoring_alerts 
            (alert_type, severity, title, message, data, created_at) 
            VALUES (
                '" . $this->db->escape($alert_data['type']) . "',
                '" . $this->db->escape($alert_data['severity']) . "',
                '" . $this->db->escape($alert_data['title']) . "',
                '" . $this->db->escape($alert_data['message']) . "',
                '" . $this->db->escape(json_encode($alert_data['data'] ?? [])) . "',
                NOW()
            )
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Get active alerts
     *
     * @param string $status Alert status filter
     * @return array
     */
    public function getActiveAlerts($status = 'open') {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_monitoring_alerts 
            WHERE status = '" . $this->db->escape($status) . "'
            ORDER BY 
                FIELD(severity, 'critical', 'error', 'warning', 'info'),
                created_at DESC
            LIMIT 50
        ");
        
        return $query->rows;
    }
    
    /**
     * Update alert status
     *
     * @param int $alert_id Alert ID
     * @param string $status New status
     * @return bool
     */
    public function updateAlertStatus($alert_id, $status) {
        $this->db->query("
            UPDATE " . DB_PREFIX . "meschain_monitoring_alerts 
            SET status = '" . $this->db->escape($status) . "',
                updated_at = NOW()
                " . ($status === 'resolved' ? ", resolved_at = NOW()" : "") . "
            WHERE alert_id = " . (int)$alert_id
        );
        
        return $this->db->countAffected() > 0;
    }
    
    /**
     * Get alert statistics
     *
     * @param int $hours Hours to analyze
     * @return array
     */
    public function getAlertStatistics($hours = 24) {
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total_alerts,
                SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) as critical_alerts,
                SUM(CASE WHEN severity = 'error' THEN 1 ELSE 0 END) as error_alerts,
                SUM(CASE WHEN severity = 'warning' THEN 1 ELSE 0 END) as warning_alerts,
                SUM(CASE WHEN severity = 'info' THEN 1 ELSE 0 END) as info_alerts,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_alerts,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_alerts
            FROM " . DB_PREFIX . "meschain_monitoring_alerts 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL " . (int)$hours . " HOUR)
        ");
        
        return $query->row;
    }
    
    /**
     * Get marketplace performance summary
     *
     * @param int $hours Hours to analyze
     * @return array
     */
    public function getMarketplacePerformance($hours = 24) {
        $marketplaces = ['trendyol', 'amazon', 'n11', 'hepsiburada', 'ebay', 'ozon'];
        $performance = [];
        
        foreach ($marketplaces as $marketplace) {
            // Get API response times from monitoring metrics
            $query = $this->db->query("
                SELECT 
                    AVG(JSON_EXTRACT(data, '$.performance.api_response_times.endpoints." . $marketplace . "_api')) as avg_response_time,
                    COUNT(*) as total_calls,
                    SUM(CASE WHEN JSON_EXTRACT(data, '$.performance.api_response_times.endpoints." . $marketplace . "_api') > 300 THEN 1 ELSE 0 END) as slow_calls
                FROM " . DB_PREFIX . "meschain_monitoring_metrics 
                WHERE type = 'apm_metrics' 
                AND timestamp >= DATE_SUB(NOW(), INTERVAL " . (int)$hours . " HOUR)
                AND JSON_EXTRACT(data, '$.performance.api_response_times.endpoints." . $marketplace . "_api') IS NOT NULL
            ");
            
            $result = $query->row;
            
            $performance[$marketplace] = [
                'avg_response_time' => (float)($result['avg_response_time'] ?? 0),
                'total_calls' => (int)($result['total_calls'] ?? 0),
                'slow_calls' => (int)($result['slow_calls'] ?? 0),
                'performance_score' => $this->calculateMarketplaceScore((float)($result['avg_response_time'] ?? 0)),
                'status' => $this->getMarketplaceStatus((float)($result['avg_response_time'] ?? 0))
            ];
        }
        
        return $performance;
    }
    
    /**
     * Get system health overview
     *
     * @return array
     */
    public function getSystemHealthOverview() {
        // Get latest snapshot
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_performance_snapshots 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        
        $latest = $query->row;
        
        if (!$latest) {
            return [
                'health_score' => 0,
                'status' => 'unknown',
                'last_update' => null
            ];
        }
        
        // Calculate trend
        $trend_query = $this->db->query("
            SELECT AVG(system_health_score) as avg_score 
            FROM " . DB_PREFIX . "meschain_performance_snapshots 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        
        $trend_score = (float)($trend_query->row['avg_score'] ?? 0);
        $current_score = (float)$latest['system_health_score'];
        
        return [
            'health_score' => $current_score,
            'status' => $this->getHealthStatus($current_score),
            'trend' => $this->getHealthTrend($current_score, $trend_score),
            'last_update' => $latest['created_at'],
            'api_response' => (int)$latest['api_response_avg'],
            'memory_usage' => (float)$latest['memory_usage_percent'],
            'active_users' => (int)$latest['active_users'],
            'error_rate' => (float)$latest['error_rate']
        ];
    }
    
    /**
     * Clean old data
     *
     * @param int $days Days to keep
     */
    public function cleanOldData($days = 30) {
        // Clean old metrics
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "meschain_monitoring_metrics 
            WHERE timestamp < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
        ");
        
        // Clean old snapshots
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "meschain_performance_snapshots 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
        ");
        
        // Clean resolved alerts older than 7 days
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "meschain_monitoring_alerts 
            WHERE status = 'resolved' 
            AND resolved_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
    }
    
    /**
     * Get performance report data
     *
     * @param string $period Period (day, week, month)
     * @return array
     */
    public function getPerformanceReport($period = 'day') {
        $hours = [
            'day' => 24,
            'week' => 168,
            'month' => 720
        ];
        
        $interval = $hours[$period] ?? 24;
        
        return [
            'period' => $period,
            'interval_hours' => $interval,
            'performance_trends' => $this->getPerformanceTrends($interval),
            'alert_statistics' => $this->getAlertStatistics($interval),
            'marketplace_performance' => $this->getMarketplacePerformance($interval),
            'system_overview' => $this->getSystemHealthOverview()
        ];
    }
    
    // Helper methods
    private function calculateMarketplaceScore($response_time) {
        if ($response_time <= 100) return 100;
        if ($response_time <= 200) return 85;
        if ($response_time <= 300) return 70;
        if ($response_time <= 500) return 50;
        return 25;
    }
    
    private function getMarketplaceStatus($response_time) {
        if ($response_time <= 200) return 'excellent';
        if ($response_time <= 300) return 'good';
        if ($response_time <= 500) return 'warning';
        return 'critical';
    }
    
    private function getHealthStatus($score) {
        if ($score >= 90) return 'excellent';
        if ($score >= 80) return 'good';
        if ($score >= 70) return 'warning';
        if ($score >= 60) return 'poor';
        return 'critical';
    }
    
    private function getHealthTrend($current, $average) {
        $diff = $current - $average;
        if ($diff > 5) return 'improving';
        if ($diff < -5) return 'declining';
        return 'stable';
    }
} 