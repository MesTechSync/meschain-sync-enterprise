<?php
/**
 * MesChain-Sync Advanced BI Dashboard Model
 * 
 * ATOM-MZ008: Advanced Business Intelligence Engine
 * Developed by: MezBjen Team - Business Intelligence Specialist
 * Date: June 8, 2025
 * 
 * @package    MesChain-Sync Enterprise
 * @subpackage Business Intelligence Model
 * @version    3.0.0
 * @author     MezBjen Development Team
 * @copyright  2025 MesTechSync Solutions
 * @license    Enterprise License
 */

class ModelExtensionModuleAdvancedBiDashboard extends Model {
    
    private $logger;
    
    /**
     * Initialize BI Dashboard Model
     */
    public function __construct($registry) {
        parent::__construct($registry);
        
        // Initialize logger
        require_once(DIR_SYSTEM . 'library/meschain/logger/bi_logger.php');
        $this->logger = new \MesChain\Logger\BILogger('bi_dashboard_model');
        
        // Create required tables
        $this->createTables();
    }
    
    /**
     * Create Required Database Tables
     */
    private function createTables() {
        try {
            // BI Configuration Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `oc_meschain_bi_configuration` (
                    `config_id` int(11) NOT NULL AUTO_INCREMENT,
                    `config_key` varchar(255) NOT NULL,
                    `config_value` text NOT NULL,
                    `config_group` varchar(100) DEFAULT 'general',
                    `description` text,
                    `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`config_id`),
                    UNIQUE KEY `config_key` (`config_key`),
                    KEY `config_group` (`config_group`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // BI Reports Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `oc_meschain_bi_reports` (
                    `report_id` int(11) NOT NULL AUTO_INCREMENT,
                    `report_name` varchar(255) NOT NULL,
                    `report_type` varchar(100) NOT NULL,
                    `report_category` varchar(100) DEFAULT 'general',
                    `report_data` longtext NOT NULL,
                    `report_config` text,
                    `generated_by` int(11) NOT NULL,
                    `file_path` varchar(500),
                    `file_size` bigint(20) DEFAULT 0,
                    `status` enum('generating','completed','failed','archived') DEFAULT 'generating',
                    `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`report_id`),
                    KEY `report_type` (`report_type`),
                    KEY `report_category` (`report_category`),
                    KEY `generated_by` (`generated_by`),
                    KEY `status` (`status`),
                    KEY `date_created` (`date_created`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // BI Scheduled Reports Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `oc_meschain_bi_scheduled_reports` (
                    `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
                    `report_type` varchar(100) NOT NULL,
                    `report_name` varchar(255) NOT NULL,
                    `schedule_config` text NOT NULL,
                    `recipients` text,
                    `frequency` enum('daily','weekly','monthly','quarterly','yearly') NOT NULL,
                    `next_run` datetime NOT NULL,
                    `last_run` datetime,
                    `status` enum('active','paused','disabled') DEFAULT 'active',
                    `created_by` int(11) NOT NULL,
                    `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`schedule_id`),
                    KEY `report_type` (`report_type`),
                    KEY `frequency` (`frequency`),
                    KEY `status` (`status`),
                    KEY `next_run` (`next_run`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // BI Analytics Cache Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `oc_meschain_bi_analytics_cache` (
                    `cache_id` int(11) NOT NULL AUTO_INCREMENT,
                    `cache_key` varchar(255) NOT NULL,
                    `cache_data` longtext NOT NULL,
                    `cache_tags` varchar(500),
                    `expires_at` datetime NOT NULL,
                    `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`cache_id`),
                    UNIQUE KEY `cache_key` (`cache_key`),
                    KEY `expires_at` (`expires_at`),
                    KEY `cache_tags` (`cache_tags`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // BI Performance Metrics Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `oc_meschain_bi_performance_metrics` (
                    `metric_id` int(11) NOT NULL AUTO_INCREMENT,
                    `metric_name` varchar(255) NOT NULL,
                    `metric_value` decimal(15,4) NOT NULL,
                    `metric_unit` varchar(50),
                    `metric_category` varchar(100) DEFAULT 'general',
                    `recorded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`metric_id`),
                    KEY `metric_name` (`metric_name`),
                    KEY `metric_category` (`metric_category`),
                    KEY `recorded_at` (`recorded_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // BI User Activity Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `oc_meschain_bi_user_activity` (
                    `activity_id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_id` int(11) NOT NULL,
                    `activity_type` varchar(100) NOT NULL,
                    `activity_description` text,
                    `activity_data` text,
                    `ip_address` varchar(45),
                    `user_agent` text,
                    `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`activity_id`),
                    KEY `user_id` (`user_id`),
                    KEY `activity_type` (`activity_type`),
                    KEY `date_created` (`date_created`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Insert default configuration
            $this->insertDefaultConfiguration();
            
            $this->logger->info('BI Dashboard database tables created successfully');
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to create BI Dashboard tables: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Insert Default Configuration
     */
    private function insertDefaultConfiguration() {
        $default_configs = [
            [
                'config_key' => 'bi_engine_status',
                'config_value' => '1',
                'config_group' => 'engine',
                'description' => 'BI Engine operational status'
            ],
            [
                'config_key' => 'bi_cache_enabled',
                'config_value' => '1',
                'config_group' => 'performance',
                'description' => 'Enable BI data caching'
            ],
            [
                'config_key' => 'bi_cache_ttl',
                'config_value' => '300',
                'config_group' => 'performance',
                'description' => 'Cache time-to-live in seconds'
            ],
            [
                'config_key' => 'bi_dashboard_refresh_rate',
                'config_value' => '30',
                'config_group' => 'dashboard',
                'description' => 'Dashboard auto-refresh rate in seconds'
            ],
            [
                'config_key' => 'bi_ai_confidence_threshold',
                'config_value' => '0.85',
                'config_group' => 'ai',
                'description' => 'AI recommendation confidence threshold'
            ],
            [
                'config_key' => 'bi_predictive_accuracy_target',
                'config_value' => '94.5',
                'config_group' => 'predictive',
                'description' => 'Target predictive accuracy percentage'
            ],
            [
                'config_key' => 'bi_olap_dimensions',
                'config_value' => '12',
                'config_group' => 'olap',
                'description' => 'Number of OLAP cube dimensions'
            ],
            [
                'config_key' => 'bi_real_time_enabled',
                'config_value' => '1',
                'config_group' => 'real_time',
                'description' => 'Enable real-time analytics'
            ]
        ];
        
        foreach ($default_configs as $config) {
            $existing = $this->db->query("
                SELECT config_id FROM oc_meschain_bi_configuration 
                WHERE config_key = '" . $this->db->escape($config['config_key']) . "'
            ");
            
            if (!$existing->num_rows) {
                $this->db->query("
                    INSERT INTO oc_meschain_bi_configuration 
                    (config_key, config_value, config_group, description) 
                    VALUES (
                        '" . $this->db->escape($config['config_key']) . "',
                        '" . $this->db->escape($config['config_value']) . "',
                        '" . $this->db->escape($config['config_group']) . "',
                        '" . $this->db->escape($config['description']) . "'
                    )
                ");
            }
        }
    }
    
    /**
     * Get Configuration
     * 
     * @param string $group Configuration group filter
     * @return array Configuration data
     */
    public function getConfiguration($group = null) {
        try {
            $sql = "SELECT * FROM oc_meschain_bi_configuration";
            
            if ($group) {
                $sql .= " WHERE config_group = '" . $this->db->escape($group) . "'";
            }
            
            $sql .= " ORDER BY config_group, config_key";
            
            $query = $this->db->query($sql);
            
            $config = [];
            foreach ($query->rows as $row) {
                $config[$row['config_key']] = [
                    'value' => $row['config_value'],
                    'group' => $row['config_group'],
                    'description' => $row['description']
                ];
            }
            
            return $config;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get BI configuration: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update Configuration
     * 
     * @param array $config_data Configuration data to update
     * @return bool Success status
     */
    public function updateConfiguration($config_data) {
        try {
            foreach ($config_data as $key => $value) {
                if (strpos($key, 'bi_') === 0) {
                    $this->db->query("
                        UPDATE oc_meschain_bi_configuration 
                        SET config_value = '" . $this->db->escape($value) . "',
                            date_modified = NOW()
                        WHERE config_key = '" . $this->db->escape($key) . "'
                    ");
                }
            }
            
            $this->logger->info('BI configuration updated successfully', $config_data);
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to update BI configuration: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Save Report
     * 
     * @param array $report_data Report data
     * @return int Report ID
     */
    public function saveReport($report_data) {
        try {
            $this->db->query("
                INSERT INTO oc_meschain_bi_reports 
                (report_name, report_type, report_category, report_data, report_config, generated_by, status) 
                VALUES (
                    '" . $this->db->escape($report_data['name'] ?? 'Untitled Report') . "',
                    '" . $this->db->escape($report_data['type'] ?? 'custom') . "',
                    '" . $this->db->escape($report_data['category'] ?? 'general') . "',
                    '" . $this->db->escape(json_encode($report_data['data'])) . "',
                    '" . $this->db->escape(json_encode($report_data['config'] ?? [])) . "',
                    '" . (int)($this->user->getId() ?? 0) . "',
                    'completed'
                )
            ");
            
            $report_id = $this->db->getLastId();
            
            $this->logger->info('BI report saved successfully', [
                'report_id' => $report_id,
                'report_type' => $report_data['type'] ?? 'custom'
            ]);
            
            return $report_id;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to save BI report: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get Recent Reports
     * 
     * @param int $limit Number of reports to retrieve
     * @return array Recent reports
     */
    public function getRecentReports($limit = 10) {
        try {
            $query = $this->db->query("
                SELECT r.*, u.username as generated_by_name
                FROM oc_meschain_bi_reports r
                LEFT JOIN oc_user u ON r.generated_by = u.user_id
                ORDER BY r.date_created DESC
                LIMIT " . (int)$limit
            );
            
            return $query->rows;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get recent BI reports: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get Scheduled Reports
     * 
     * @return array Scheduled reports
     */
    public function getScheduledReports() {
        try {
            $query = $this->db->query("
                SELECT s.*, u.username as created_by_name
                FROM oc_meschain_bi_scheduled_reports s
                LEFT JOIN oc_user u ON s.created_by = u.user_id
                WHERE s.status = 'active'
                ORDER BY s.next_run ASC
            ");
            
            return $query->rows;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get scheduled BI reports: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Record Performance Metric
     * 
     * @param string $metric_name Metric name
     * @param float $metric_value Metric value
     * @param string $metric_unit Metric unit
     * @param string $metric_category Metric category
     */
    public function recordPerformanceMetric($metric_name, $metric_value, $metric_unit = null, $metric_category = 'general') {
        try {
            $this->db->query("
                INSERT INTO oc_meschain_bi_performance_metrics 
                (metric_name, metric_value, metric_unit, metric_category) 
                VALUES (
                    '" . $this->db->escape($metric_name) . "',
                    '" . (float)$metric_value . "',
                    '" . $this->db->escape($metric_unit) . "',
                    '" . $this->db->escape($metric_category) . "'
                )
            ");
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to record performance metric: ' . $e->getMessage());
        }
    }
    
    /**
     * Get Performance Metrics
     * 
     * @param string $category Metric category filter
     * @param int $hours Hours to look back
     * @return array Performance metrics
     */
    public function getPerformanceMetrics($category = null, $hours = 24) {
        try {
            $sql = "
                SELECT metric_name, AVG(metric_value) as avg_value, 
                       MAX(metric_value) as max_value, MIN(metric_value) as min_value,
                       COUNT(*) as sample_count, metric_unit, metric_category
                FROM oc_meschain_bi_performance_metrics 
                WHERE recorded_at >= DATE_SUB(NOW(), INTERVAL " . (int)$hours . " HOUR)
            ";
            
            if ($category) {
                $sql .= " AND metric_category = '" . $this->db->escape($category) . "'";
            }
            
            $sql .= " GROUP BY metric_name, metric_unit, metric_category ORDER BY metric_category, metric_name";
            
            $query = $this->db->query($sql);
            
            return $query->rows;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get performance metrics: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Log User Activity
     * 
     * @param int $user_id User ID
     * @param string $activity_type Activity type
     * @param string $description Activity description
     * @param array $data Additional activity data
     */
    public function logUserActivity($user_id, $activity_type, $description, $data = []) {
        try {
            $this->db->query("
                INSERT INTO oc_meschain_bi_user_activity 
                (user_id, activity_type, activity_description, activity_data, ip_address, user_agent) 
                VALUES (
                    '" . (int)$user_id . "',
                    '" . $this->db->escape($activity_type) . "',
                    '" . $this->db->escape($description) . "',
                    '" . $this->db->escape(json_encode($data)) . "',
                    '" . $this->db->escape($_SERVER['REMOTE_ADDR'] ?? '') . "',
                    '" . $this->db->escape($_SERVER['HTTP_USER_AGENT'] ?? '') . "'
                )
            ");
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to log user activity: ' . $e->getMessage());
        }
    }
    
    /**
     * Get User Activity
     * 
     * @param int $user_id User ID filter
     * @param int $limit Number of activities to retrieve
     * @return array User activities
     */
    public function getUserActivity($user_id = null, $limit = 50) {
        try {
            $sql = "
                SELECT a.*, u.username
                FROM oc_meschain_bi_user_activity a
                LEFT JOIN oc_user u ON a.user_id = u.user_id
            ";
            
            if ($user_id) {
                $sql .= " WHERE a.user_id = " . (int)$user_id;
            }
            
            $sql .= " ORDER BY a.date_created DESC LIMIT " . (int)$limit;
            
            $query = $this->db->query($sql);
            
            return $query->rows;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get user activity: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Cache Analytics Data
     * 
     * @param string $cache_key Cache key
     * @param mixed $data Data to cache
     * @param int $ttl Time to live in seconds
     * @param array $tags Cache tags
     */
    public function cacheAnalyticsData($cache_key, $data, $ttl = 300, $tags = []) {
        try {
            // Remove existing cache entry
            $this->db->query("
                DELETE FROM oc_meschain_bi_analytics_cache 
                WHERE cache_key = '" . $this->db->escape($cache_key) . "'
            ");
            
            // Insert new cache entry
            $expires_at = date('Y-m-d H:i:s', time() + $ttl);
            
            $this->db->query("
                INSERT INTO oc_meschain_bi_analytics_cache 
                (cache_key, cache_data, cache_tags, expires_at) 
                VALUES (
                    '" . $this->db->escape($cache_key) . "',
                    '" . $this->db->escape(json_encode($data)) . "',
                    '" . $this->db->escape(implode(',', $tags)) . "',
                    '" . $expires_at . "'
                )
            ");
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to cache analytics data: ' . $e->getMessage());
        }
    }
    
    /**
     * Get Cached Analytics Data
     * 
     * @param string $cache_key Cache key
     * @return mixed Cached data or null
     */
    public function getCachedAnalyticsData($cache_key) {
        try {
            $query = $this->db->query("
                SELECT cache_data 
                FROM oc_meschain_bi_analytics_cache 
                WHERE cache_key = '" . $this->db->escape($cache_key) . "' 
                AND expires_at > NOW()
            ");
            
            if ($query->num_rows) {
                return json_decode($query->row['cache_data'], true);
            }
            
            return null;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get cached analytics data: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Clear Expired Cache
     */
    public function clearExpiredCache() {
        try {
            $this->db->query("
                DELETE FROM oc_meschain_bi_analytics_cache 
                WHERE expires_at <= NOW()
            ");
            
            $this->logger->info('Expired BI cache cleared successfully');
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to clear expired cache: ' . $e->getMessage());
        }
    }
    
    /**
     * Get Dashboard Statistics
     * 
     * @return array Dashboard statistics
     */
    public function getDashboardStatistics() {
        try {
            $stats = [];
            
            // Total reports generated
            $query = $this->db->query("SELECT COUNT(*) as total FROM oc_meschain_bi_reports");
            $stats['total_reports'] = $query->row['total'];
            
            // Reports generated today
            $query = $this->db->query("
                SELECT COUNT(*) as today 
                FROM oc_meschain_bi_reports 
                WHERE DATE(date_created) = CURDATE()
            ");
            $stats['reports_today'] = $query->row['today'];
            
            // Active scheduled reports
            $query = $this->db->query("
                SELECT COUNT(*) as active 
                FROM oc_meschain_bi_scheduled_reports 
                WHERE status = 'active'
            ");
            $stats['active_schedules'] = $query->row['active'];
            
            // Cache hit ratio
            $query = $this->db->query("
                SELECT COUNT(*) as total_cache_entries 
                FROM oc_meschain_bi_analytics_cache
            ");
            $stats['cache_entries'] = $query->row['total_cache_entries'];
            
            // User activity count (last 24 hours)
            $query = $this->db->query("
                SELECT COUNT(*) as activities 
                FROM oc_meschain_bi_user_activity 
                WHERE date_created >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $stats['recent_activities'] = $query->row['activities'];
            
            return $stats;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get dashboard statistics: ' . $e->getMessage());
            return [];
        }
    }
}

?> 