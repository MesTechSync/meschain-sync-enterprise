<?php
namespace MesChain\Monitoring;

/**
 * MesChain Real-time Monitor
 *
 * Real-time system monitoring and health tracking
 * Features: Performance metrics, Health checks, Alerts, Dashboard data
 *
 * @author MesChain Development Team
 * @version 3.0.0
 */
class RealtimeMonitor {
    private $db;
    private $cache;
    private $config;
    private $alert_manager;
    private $metrics = [];

    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->cache = $registry->get('cache');
        $this->config = $registry->get('config');
        $this->alert_manager = new AlertManager($registry);
    }

    /**
     * Collect all system metrics
     *
     * @return array System metrics
     */
    public function collectMetrics() {
        $this->metrics = [
            'system' => $this->getSystemMetrics(),
            'marketplace' => $this->getMarketplaceMetrics(),
            'performance' => $this->getPerformanceMetrics(),
            'errors' => $this->getErrorMetrics(),
            'security' => $this->getSecurityMetrics()
        ];

        // Store metrics for historical analysis
        $this->storeMetrics($this->metrics);

        // Check for alerts
        $this->checkAlerts($this->metrics);

        return $this->metrics;
    }

    /**
     * Get system metrics
     *
     * @return array System metrics
     */
    private function getSystemMetrics() {
        return [
            'cpu_usage' => $this->getCPUUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'database_connections' => $this->getDatabaseConnections(),
            'uptime' => $this->getUptime(),
            'php_version' => PHP_VERSION,
            'opencart_version' => VERSION ?? '4.0.2.3'
        ];
    }

    /**
     * Get marketplace metrics
     *
     * @return array Marketplace metrics
     */
    private function getMarketplaceMetrics() {
        $metrics = [];
        $marketplaces = ['hepsiburada', 'trendyol', 'amazon', 'ebay', 'n11', 'gittigidiyor', 'pazarama'];

        foreach ($marketplaces as $marketplace) {
            $metrics[$marketplace] = [
                'status' => $this->getMarketplaceStatus($marketplace),
                'last_sync' => $this->getLastSyncTime($marketplace),
                'sync_queue' => $this->getSyncQueueSize($marketplace),
                'error_rate' => $this->getErrorRate($marketplace),
                'api_response_time' => $this->getApiResponseTime($marketplace)
            ];
        }

        return $metrics;
    }

    /**
     * Get performance metrics
     *
     * @return array Performance metrics
     */
    private function getPerformanceMetrics() {
        // Get average query time for last hour
        $query = $this->db->query("
            SELECT
                AVG(execution_time) as avg_time,
                MAX(execution_time) as max_time,
                COUNT(*) as total_queries
            FROM " . DB_PREFIX . "meschain_slow_queries
            WHERE date_added > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");

        return [
            'avg_response_time' => $this->getAverageResponseTime(),
            'requests_per_minute' => $this->getRequestsPerMinute(),
            'cache_hit_rate' => $this->getCacheHitRate(),
            'database_query_time' => [
                'average' => $query->row['avg_time'] ?? 0,
                'max' => $query->row['max_time'] ?? 0,
                'total' => $query->row['total_queries'] ?? 0
            ],
            'active_users' => $this->getActiveUsers()
        ];
    }

    /**
     * Get error metrics
     *
     * @return array Error metrics
     */
    private function getErrorMetrics() {
        $query = $this->db->query("
            SELECT
                log_level,
                COUNT(*) as count
            FROM " . DB_PREFIX . "meschain_logs
            WHERE date_added > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            GROUP BY log_level
        ");

        $errors_by_level = [];
        foreach ($query->rows as $row) {
            $errors_by_level[$row['log_level']] = (int)$row['count'];
        }

        return [
            'total_errors' => array_sum($errors_by_level),
            'errors_by_level' => $errors_by_level,
            'error_rate' => $this->calculateErrorRate(),
            'recent_errors' => $this->getRecentErrors(5)
        ];
    }

    /**
     * Get security metrics
     *
     * @return array Security metrics
     */
    private function getSecurityMetrics() {
        return [
            'failed_logins' => $this->getFailedLogins(),
            'rate_limit_hits' => $this->getRateLimitHits(),
            'suspicious_activities' => $this->getSuspiciousActivities(),
            'last_security_scan' => $this->getLastSecurityScan()
        ];
    }

    /**
     * Get CPU usage
     *
     * @return float CPU usage percentage
     */
    private function getCPUUsage() {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return round($load[0] * 100 / $this->getCPUCores(), 2);
        }
        return 0;
    }

    /**
     * Get memory usage
     *
     * @return array Memory usage data
     */
    private function getMemoryUsage() {
        return [
            'used' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => $this->getMemoryLimit(),
            'percentage' => round((memory_get_usage(true) / $this->getMemoryLimit()) * 100, 2)
        ];
    }

    /**
     * Get disk usage
     *
     * @return array Disk usage data
     */
    private function getDiskUsage() {
        $path = DIR_APPLICATION;
        return [
            'free' => disk_free_space($path),
            'total' => disk_total_space($path),
            'used' => disk_total_space($path) - disk_free_space($path),
            'percentage' => round(((disk_total_space($path) - disk_free_space($path)) / disk_total_space($path)) * 100, 2)
        ];
    }

    /**
     * Get marketplace status
     *
     * @param string $marketplace Marketplace name
     * @return string Status
     */
    private function getMarketplaceStatus($marketplace) {
        // Check if marketplace is enabled
        $query = $this->db->query("
            SELECT setting_value
            FROM " . DB_PREFIX . "meschain_settings
            WHERE marketplace = '" . $this->db->escape($marketplace) . "'
            AND setting_key = 'status'
        ");

        if ($query->num_rows && $query->row['setting_value'] == '1') {
            // Check last sync status
            $sync_query = $this->db->query("
                SELECT sync_status
                FROM " . DB_PREFIX . "meschain_product_sync
                WHERE marketplace = '" . $this->db->escape($marketplace) . "'
                AND last_sync > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY last_sync DESC
                LIMIT 1
            ");

            if ($sync_query->num_rows) {
                return $sync_query->row['sync_status'] == 'success' ? 'healthy' : 'warning';
            }

            return 'idle';
        }

        return 'disabled';
    }

    /**
     * Store metrics for historical analysis
     *
     * @param array $metrics Metrics to store
     */
    private function storeMetrics($metrics) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_metrics
            SET metric_type = 'system_health',
                metric_data = '" . $this->db->escape(json_encode($metrics)) . "',
                date_added = NOW()
        ");
    }

    /**
     * Check for alerts based on metrics
     *
     * @param array $metrics Current metrics
     */
    private function checkAlerts($metrics) {
        // CPU usage alert
        if ($metrics['system']['cpu_usage'] > 80) {
            $this->alert_manager->trigger('high_cpu_usage', [
                'current' => $metrics['system']['cpu_usage'],
                'threshold' => 80
            ]);
        }

        // Memory usage alert
        if ($metrics['system']['memory_usage']['percentage'] > 85) {
            $this->alert_manager->trigger('high_memory_usage', [
                'current' => $metrics['system']['memory_usage']['percentage'],
                'threshold' => 85
            ]);
        }

        // Error rate alert
        if ($metrics['errors']['error_rate'] > 5) {
            $this->alert_manager->trigger('high_error_rate', [
                'current' => $metrics['errors']['error_rate'],
                'threshold' => 5
            ]);
        }

        // Marketplace health alerts
        foreach ($metrics['marketplace'] as $marketplace => $data) {
            if ($data['status'] == 'warning' || $data['status'] == 'error') {
                $this->alert_manager->trigger('marketplace_unhealthy', [
                    'marketplace' => $marketplace,
                    'status' => $data['status']
                ]);
            }
        }
    }

    /**
     * Get dashboard data
     *
     * @return array Dashboard data
     */
    public function getDashboardData() {
        $metrics = $this->collectMetrics();

        return [
            'overview' => [
                'system_health' => $this->calculateSystemHealth($metrics),
                'total_products' => $this->getTotalProducts(),
                'total_orders' => $this->getTotalOrders(),
                'active_marketplaces' => $this->getActiveMarketplaces()
            ],
            'charts' => [
                'performance_timeline' => $this->getPerformanceTimeline(),
                'marketplace_activity' => $this->getMarketplaceActivity(),
                'error_trends' => $this->getErrorTrends()
            ],
            'alerts' => $this->alert_manager->getActiveAlerts(),
            'recent_activity' => $this->getRecentActivity(),
            'metrics' => $metrics
        ];
    }

    /**
     * Calculate overall system health score
     *
     * @param array $metrics System metrics
     * @return int Health score (0-100)
     */
    private function calculateSystemHealth($metrics) {
        $score = 100;

        // Deduct points for high resource usage
        if ($metrics['system']['cpu_usage'] > 70) {
            $score -= min(20, ($metrics['system']['cpu_usage'] - 70) / 2);
        }

        if ($metrics['system']['memory_usage']['percentage'] > 70) {
            $score -= min(20, ($metrics['system']['memory_usage']['percentage'] - 70) / 2);
        }

        // Deduct points for errors
        if ($metrics['errors']['error_rate'] > 1) {
            $score -= min(30, $metrics['errors']['error_rate'] * 3);
        }

        // Deduct points for unhealthy marketplaces
        $unhealthy_count = 0;
        foreach ($metrics['marketplace'] as $data) {
            if ($data['status'] == 'warning' || $data['status'] == 'error') {
                $unhealthy_count++;
            }
        }
        $score -= min(20, $unhealthy_count * 5);

        return max(0, round($score));
    }

    /**
     * Get performance timeline data
     *
     * @return array Timeline data
     */
    private function getPerformanceTimeline() {
        $query = $this->db->query("
            SELECT
                DATE_FORMAT(date_added, '%Y-%m-%d %H:00:00') as hour,
                AVG(JSON_EXTRACT(metric_data, '$.performance.avg_response_time')) as avg_response,
                AVG(JSON_EXTRACT(metric_data, '$.system.cpu_usage')) as avg_cpu,
                AVG(JSON_EXTRACT(metric_data, '$.system.memory_usage.percentage')) as avg_memory
            FROM " . DB_PREFIX . "meschain_metrics
            WHERE metric_type = 'system_health'
            AND date_added > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY hour
            ORDER BY hour
        ");

        return $query->rows;
    }

    /**
     * Get recent activity
     *
     * @return array Recent activities
     */
    private function getRecentActivity() {
        $query = $this->db->query("
            SELECT
                'sync' as type,
                marketplace,
                'Product sync completed' as description,
                last_sync as timestamp
            FROM " . DB_PREFIX . "meschain_product_sync
            WHERE sync_status = 'success'
            AND last_sync > DATE_SUB(NOW(), INTERVAL 1 HOUR)

            UNION ALL

            SELECT
                'order' as type,
                marketplace,
                CONCAT('Order ', marketplace_order_id, ' integrated') as description,
                date_integrated as timestamp
            FROM " . DB_PREFIX . "meschain_order_integration
            WHERE date_integrated > DATE_SUB(NOW(), INTERVAL 1 HOUR)

            ORDER BY timestamp DESC
            LIMIT 10
        ");

        return $query->rows;
    }

    /**
     * Helper methods
     */
    private function getCPUCores() {
        if (is_file('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            return count($matches[0]);
        }
        return 1;
    }

    private function getMemoryLimit() {
        $limit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $limit, $matches)) {
            $value = $matches[1];
            switch (strtoupper($matches[2])) {
                case 'G':
                    $value *= 1024;
                case 'M':
                    $value *= 1024;
                case 'K':
                    $value *= 1024;
            }
            return $value;
        }
        return 134217728; // Default 128MB
    }
}

/**
 * Alert Manager Class
 */
class AlertManager {
    private $db;
    private $config;

    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
    }

    /**
     * Trigger an alert
     *
     * @param string $type Alert type
     * @param array $data Alert data
     */
    public function trigger($type, $data) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_alerts
            SET alert_type = '" . $this->db->escape($type) . "',
                alert_data = '" . $this->db->escape(json_encode($data)) . "',
                status = 'active',
                date_added = NOW()
        ");

        // Send notifications if configured
        $this->sendNotifications($type, $data);
    }

    /**
     * Get active alerts
     *
     * @return array Active alerts
     */
    public function getActiveAlerts() {
        $query = $this->db->query("
            SELECT *
            FROM " . DB_PREFIX . "meschain_alerts
            WHERE status = 'active'
            AND date_added > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY date_added DESC
            LIMIT 10
        ");

        return $query->rows;
    }

    /**
     * Send notifications for alerts
     *
     * @param string $type Alert type
     * @param array $data Alert data
     */
    private function sendNotifications($type, $data) {
        // Implementation would depend on notification preferences
        // Could send email, SMS, webhook, etc.
    }
}
