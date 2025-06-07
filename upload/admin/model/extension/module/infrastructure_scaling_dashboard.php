<?php
/**
 * Infrastructure Scaling Dashboard Model - ATOM-M008
 * MesChain-Sync Infrastructure Scaling Preparation
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0 - ATOM-M008
 * @author Musti DevOps Team
 * @date 2025-06-08
 */

class ModelExtensionModuleInfrastructureScalingDashboard extends Model {
    
    /**
     * Install infrastructure scaling tables
     */
    public function install() {
        $this->createTables();
    }
    
    /**
     * Uninstall infrastructure scaling tables
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_scaling_metrics`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_scaling_alerts`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_scaling_snapshots`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_infrastructure_config`");
    }
    
    /**
     * Create required database tables
     */
    private function createTables() {
        // Scaling metrics table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_scaling_metrics` (
                `metric_id` INT(11) NOT NULL AUTO_INCREMENT,
                `type` VARCHAR(100) NOT NULL,
                `data` LONGTEXT NOT NULL,
                `timestamp` DATETIME NOT NULL,
                `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`metric_id`),
                INDEX `idx_type_timestamp` (`type`, `timestamp`),
                INDEX `idx_timestamp` (`timestamp`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Scaling alerts table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_scaling_alerts` (
                `alert_id` INT(11) NOT NULL AUTO_INCREMENT,
                `alert_type` VARCHAR(50) NOT NULL,
                `severity` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
                `title` VARCHAR(255) NOT NULL,
                `message` TEXT NOT NULL,
                `data` JSON,
                `status` ENUM('active', 'acknowledged', 'resolved') NOT NULL DEFAULT 'active',
                `triggered_at` DATETIME NOT NULL,
                `acknowledged_at` DATETIME NULL,
                `resolved_at` DATETIME NULL,
                `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `date_modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`alert_id`),
                INDEX `idx_status_severity` (`status`, `severity`),
                INDEX `idx_alert_type` (`alert_type`),
                INDEX `idx_triggered_at` (`triggered_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Performance snapshots table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_scaling_snapshots` (
                `snapshot_id` INT(11) NOT NULL AUTO_INCREMENT,
                `snapshot_type` VARCHAR(50) NOT NULL,
                `cpu_usage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                `memory_usage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                `disk_usage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                `active_connections` INT(11) NOT NULL DEFAULT 0,
                `response_time_avg` INT(11) NOT NULL DEFAULT 0,
                `response_time_p95` INT(11) NOT NULL DEFAULT 0,
                `response_time_p99` INT(11) NOT NULL DEFAULT 0,
                `throughput` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `error_rate` DECIMAL(5,4) NOT NULL DEFAULT 0.00,
                `marketplace_statuses` JSON,
                `scaling_events` JSON,
                `timestamp` DATETIME NOT NULL,
                `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`snapshot_id`),
                INDEX `idx_timestamp` (`timestamp`),
                INDEX `idx_snapshot_type` (`snapshot_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Infrastructure configuration table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_infrastructure_config` (
                `config_id` INT(11) NOT NULL AUTO_INCREMENT,
                `config_key` VARCHAR(100) NOT NULL UNIQUE,
                `config_value` LONGTEXT NOT NULL,
                `config_type` ENUM('string', 'integer', 'float', 'boolean', 'json', 'array') NOT NULL DEFAULT 'string',
                `description` TEXT,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `date_modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`config_id`),
                UNIQUE KEY `uk_config_key` (`config_key`),
                INDEX `idx_is_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Insert default infrastructure configuration
        $this->insertDefaultConfiguration();
    }
    
    /**
     * Insert default infrastructure configuration
     */
    private function insertDefaultConfiguration() {
        $default_configs = [
            [
                'config_key' => 'infrastructure_scaling_cpu_threshold',
                'config_value' => '75',
                'config_type' => 'integer',
                'description' => 'CPU usage threshold for scaling trigger (percentage)'
            ],
            [
                'config_key' => 'infrastructure_scaling_memory_threshold',
                'config_value' => '80',
                'config_type' => 'integer',
                'description' => 'Memory usage threshold for scaling trigger (percentage)'
            ],
            [
                'config_key' => 'infrastructure_scaling_response_threshold',
                'config_value' => '300',
                'config_type' => 'integer',
                'description' => 'Response time threshold for scaling trigger (milliseconds)'
            ],
            [
                'config_key' => 'infrastructure_scaling_min_instances',
                'config_value' => '2',
                'config_type' => 'integer',
                'description' => 'Minimum number of instances'
            ],
            [
                'config_key' => 'infrastructure_scaling_max_instances',
                'config_value' => '10',
                'config_type' => 'integer',
                'description' => 'Maximum number of instances'
            ],
            [
                'config_key' => 'infrastructure_scaling_target_cpu',
                'config_value' => '60',
                'config_type' => 'integer',
                'description' => 'Target CPU utilization for auto-scaling'
            ],
            [
                'config_key' => 'infrastructure_scaling_scale_up_cooldown',
                'config_value' => '300',
                'config_type' => 'integer',
                'description' => 'Scale up cooldown period (seconds)'
            ],
            [
                'config_key' => 'infrastructure_scaling_scale_down_cooldown',
                'config_value' => '900',
                'config_type' => 'integer',
                'description' => 'Scale down cooldown period (seconds)'
            ],
            [
                'config_key' => 'infrastructure_scaling_enable_microservices',
                'config_value' => 'true',
                'config_type' => 'boolean',
                'description' => 'Enable microservices architecture'
            ],
            [
                'config_key' => 'infrastructure_scaling_enable_auto_scaling',
                'config_value' => 'true',
                'config_type' => 'boolean',
                'description' => 'Enable automatic scaling'
            ],
            [
                'config_key' => 'infrastructure_scaling_enable_predictive',
                'config_value' => 'true',
                'config_type' => 'boolean',
                'description' => 'Enable predictive scaling'
            ],
            [
                'config_key' => 'infrastructure_scaling_kubernetes_config',
                'config_value' => '{"cluster_version": "1.28", "networking": "Calico", "container_runtime": "containerd"}',
                'config_type' => 'json',
                'description' => 'Kubernetes cluster configuration'
            ],
            [
                'config_key' => 'infrastructure_scaling_database_config',
                'config_value' => '{"topology": "master_slave_with_read_replicas", "read_replicas": 3, "backup_retention": 30}',
                'config_type' => 'json',
                'description' => 'Database clustering configuration'
            ],
            [
                'config_key' => 'infrastructure_scaling_cdn_config',
                'config_value' => '{"provider": "CloudFlare", "edge_locations": 200, "cache_hit_ratio": 95.2}',
                'config_type' => 'json',
                'description' => 'CDN configuration'
            ],
            [
                'config_key' => 'infrastructure_scaling_cicd_config',
                'config_value' => '{"blue_green_enabled": true, "canary_enabled": true, "quality_gates": true}',
                'config_type' => 'json',
                'description' => 'CI/CD pipeline configuration'
            ]
        ];
        
        foreach ($default_configs as $config) {
            $this->db->query("
                INSERT IGNORE INTO `" . DB_PREFIX . "meschain_infrastructure_config` 
                (`config_key`, `config_value`, `config_type`, `description`) 
                VALUES (
                    '" . $this->db->escape($config['config_key']) . "',
                    '" . $this->db->escape($config['config_value']) . "',
                    '" . $this->db->escape($config['config_type']) . "',
                    '" . $this->db->escape($config['description']) . "'
                )
            ");
        }
    }
    
    /**
     * Store performance snapshot
     *
     * @param array $snapshot_data Performance snapshot data
     * @return int Snapshot ID
     */
    public function storePerformanceSnapshot($snapshot_data) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_scaling_snapshots` (
                `snapshot_type`,
                `cpu_usage`,
                `memory_usage`,
                `disk_usage`,
                `active_connections`,
                `response_time_avg`,
                `response_time_p95`,
                `response_time_p99`,
                `throughput`,
                `error_rate`,
                `marketplace_statuses`,
                `scaling_events`,
                `timestamp`
            ) VALUES (
                '" . $this->db->escape($snapshot_data['type'] ?? 'general') . "',
                '" . floatval($snapshot_data['cpu_usage'] ?? 0) . "',
                '" . floatval($snapshot_data['memory_usage'] ?? 0) . "',
                '" . floatval($snapshot_data['disk_usage'] ?? 0) . "',
                '" . intval($snapshot_data['active_connections'] ?? 0) . "',
                '" . intval($snapshot_data['response_time_avg'] ?? 0) . "',
                '" . intval($snapshot_data['response_time_p95'] ?? 0) . "',
                '" . intval($snapshot_data['response_time_p99'] ?? 0) . "',
                '" . floatval($snapshot_data['throughput'] ?? 0) . "',
                '" . floatval($snapshot_data['error_rate'] ?? 0) . "',
                '" . $this->db->escape(json_encode($snapshot_data['marketplace_statuses'] ?? [])) . "',
                '" . $this->db->escape(json_encode($snapshot_data['scaling_events'] ?? [])) . "',
                NOW()
            )
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Create scaling alert
     *
     * @param string $alert_type Alert type
     * @param string $severity Alert severity
     * @param string $title Alert title
     * @param string $message Alert message
     * @param array $data Additional alert data
     * @return int Alert ID
     */
    public function createAlert($alert_type, $severity, $title, $message, $data = []) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_scaling_alerts` (
                `alert_type`,
                `severity`,
                `title`,
                `message`,
                `data`,
                `triggered_at`
            ) VALUES (
                '" . $this->db->escape($alert_type) . "',
                '" . $this->db->escape($severity) . "',
                '" . $this->db->escape($title) . "',
                '" . $this->db->escape($message) . "',
                '" . $this->db->escape(json_encode($data)) . "',
                NOW()
            )
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Get active alerts
     *
     * @param int $limit Number of alerts to return
     * @return array Active alerts
     */
    public function getActiveAlerts($limit = 50) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_scaling_alerts`
            WHERE `status` = 'active'
            ORDER BY `severity` DESC, `triggered_at` DESC
            LIMIT " . intval($limit) . "
        ");
        
        $alerts = [];
        foreach ($query->rows as $row) {
            $row['data'] = json_decode($row['data'], true);
            $alerts[] = $row;
        }
        
        return $alerts;
    }
    
    /**
     * Acknowledge alert
     *
     * @param int $alert_id Alert ID
     * @return bool Success status
     */
    public function acknowledgeAlert($alert_id) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "meschain_scaling_alerts`
            SET `status` = 'acknowledged', `acknowledged_at` = NOW()
            WHERE `alert_id` = '" . intval($alert_id) . "'
        ");
        
        return $this->db->countAffected() > 0;
    }
    
    /**
     * Resolve alert
     *
     * @param int $alert_id Alert ID
     * @return bool Success status
     */
    public function resolveAlert($alert_id) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "meschain_scaling_alerts`
            SET `status` = 'resolved', `resolved_at` = NOW()
            WHERE `alert_id` = '" . intval($alert_id) . "'
        ");
        
        return $this->db->countAffected() > 0;
    }
    
    /**
     * Get performance snapshots
     *
     * @param string $type Snapshot type
     * @param int $hours Number of hours to look back
     * @param int $limit Maximum number of records
     * @return array Performance snapshots
     */
    public function getPerformanceSnapshots($type = null, $hours = 24, $limit = 100) {
        $where_clause = "WHERE `timestamp` >= DATE_SUB(NOW(), INTERVAL " . intval($hours) . " HOUR)";
        
        if ($type) {
            $where_clause .= " AND `snapshot_type` = '" . $this->db->escape($type) . "'";
        }
        
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_scaling_snapshots`
            {$where_clause}
            ORDER BY `timestamp` DESC
            LIMIT " . intval($limit) . "
        ");
        
        $snapshots = [];
        foreach ($query->rows as $row) {
            $row['marketplace_statuses'] = json_decode($row['marketplace_statuses'], true);
            $row['scaling_events'] = json_decode($row['scaling_events'], true);
            $snapshots[] = $row;
        }
        
        return $snapshots;
    }
    
    /**
     * Get infrastructure configuration
     *
     * @param string $config_key Configuration key
     * @return mixed Configuration value
     */
    public function getInfrastructureConfig($config_key) {
        $query = $this->db->query("
            SELECT `config_value`, `config_type` FROM `" . DB_PREFIX . "meschain_infrastructure_config`
            WHERE `config_key` = '" . $this->db->escape($config_key) . "' AND `is_active` = 1
        ");
        
        if ($query->num_rows) {
            $value = $query->row['config_value'];
            $type = $query->row['config_type'];
            
            switch ($type) {
                case 'integer':
                    return intval($value);
                case 'float':
                    return floatval($value);
                case 'boolean':
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                case 'json':
                    return json_decode($value, true);
                case 'array':
                    return unserialize($value);
                default:
                    return $value;
            }
        }
        
        return null;
    }
    
    /**
     * Set infrastructure configuration
     *
     * @param string $config_key Configuration key
     * @param mixed $config_value Configuration value
     * @param string $config_type Configuration type
     * @return bool Success status
     */
    public function setInfrastructureConfig($config_key, $config_value, $config_type = 'string') {
        // Encode value based on type
        switch ($config_type) {
            case 'json':
                $encoded_value = json_encode($config_value);
                break;
            case 'array':
                $encoded_value = serialize($config_value);
                break;
            case 'boolean':
                $encoded_value = $config_value ? 'true' : 'false';
                break;
            default:
                $encoded_value = (string)$config_value;
        }
        
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_infrastructure_config` 
            (`config_key`, `config_value`, `config_type`) 
            VALUES (
                '" . $this->db->escape($config_key) . "',
                '" . $this->db->escape($encoded_value) . "',
                '" . $this->db->escape($config_type) . "'
            )
            ON DUPLICATE KEY UPDATE 
                `config_value` = '" . $this->db->escape($encoded_value) . "',
                `config_type` = '" . $this->db->escape($config_type) . "',
                `date_modified` = NOW()
        ");
        
        return $this->db->countAffected() > 0;
    }
    
    /**
     * Get scaling metrics
     *
     * @param string $type Metric type
     * @param int $hours Number of hours to look back
     * @param int $limit Maximum number of records
     * @return array Scaling metrics
     */
    public function getScalingMetrics($type = null, $hours = 24, $limit = 100) {
        $where_clause = "WHERE `timestamp` >= DATE_SUB(NOW(), INTERVAL " . intval($hours) . " HOUR)";
        
        if ($type) {
            $where_clause .= " AND `type` = '" . $this->db->escape($type) . "'";
        }
        
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_scaling_metrics`
            {$where_clause}
            ORDER BY `timestamp` DESC
            LIMIT " . intval($limit) . "
        ");
        
        $metrics = [];
        foreach ($query->rows as $row) {
            $row['data'] = json_decode($row['data'], true);
            $metrics[] = $row;
        }
        
        return $metrics;
    }
    
    /**
     * Store scaling metric
     *
     * @param string $type Metric type
     * @param array $data Metric data
     * @return int Metric ID
     */
    public function storeScalingMetric($type, $data) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_scaling_metrics` (
                `type`,
                `data`,
                `timestamp`
            ) VALUES (
                '" . $this->db->escape($type) . "',
                '" . $this->db->escape(json_encode($data)) . "',
                NOW()
            )
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Clean old records
     *
     * @param int $days Number of days to keep
     * @return array Cleanup statistics
     */
    public function cleanOldRecords($days = 30) {
        $stats = [];
        
        // Clean old metrics
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "meschain_scaling_metrics`
            WHERE `timestamp` < DATE_SUB(NOW(), INTERVAL " . intval($days) . " DAY)
        ");
        $stats['metrics_deleted'] = $this->db->countAffected();
        
        // Clean old snapshots
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "meschain_scaling_snapshots`
            WHERE `timestamp` < DATE_SUB(NOW(), INTERVAL " . intval($days) . " DAY)
        ");
        $stats['snapshots_deleted'] = $this->db->countAffected();
        
        // Clean resolved alerts older than specified days
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "meschain_scaling_alerts`
            WHERE `status` = 'resolved' AND `resolved_at` < DATE_SUB(NOW(), INTERVAL " . intval($days) . " DAY)
        ");
        $stats['alerts_deleted'] = $this->db->countAffected();
        
        return $stats;
    }
    
    /**
     * Get scaling statistics
     *
     * @return array Scaling statistics
     */
    public function getScalingStatistics() {
        $stats = [];
        
        // Total metrics count
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_scaling_metrics`");
        $stats['total_metrics'] = $query->row['total'];
        
        // Total snapshots count
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_scaling_snapshots`");
        $stats['total_snapshots'] = $query->row['total'];
        
        // Active alerts count
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_scaling_alerts` WHERE `status` = 'active'");
        $stats['active_alerts'] = $query->row['total'];
        
        // Critical alerts count
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_scaling_alerts` WHERE `status` = 'active' AND `severity` = 'critical'");
        $stats['critical_alerts'] = $query->row['total'];
        
        // Average response time (last 24 hours)
        $query = $this->db->query("
            SELECT AVG(`response_time_avg`) as avg_response_time 
            FROM `" . DB_PREFIX . "meschain_scaling_snapshots` 
            WHERE `timestamp` >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stats['avg_response_time_24h'] = round($query->row['avg_response_time'] ?? 0, 2);
        
        // Current scaling readiness score (simulated)
        $stats['scaling_readiness_score'] = 87.5;
        
        return $stats;
    }
}