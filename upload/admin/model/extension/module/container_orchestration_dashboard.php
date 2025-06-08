<?php
/**
 * MesChain-Sync Container Orchestration Dashboard Model
 * 
 * @package    MesChain-Sync
 * @author     MesChain Development Team
 * @copyright  2024 MesChain
 * @license    Commercial License
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

class ModelExtensionModuleContainerOrchestrationDashboard extends Model {
    
    /**
     * Container orchestration tablolarını oluşturur
     */
    public function install() {
        // Container Deployments tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_container_deployments` (
                `deployment_id` int(11) NOT NULL AUTO_INCREMENT,
                `deployment_name` varchar(100) NOT NULL,
                `namespace` varchar(100) NOT NULL DEFAULT 'meschain-sync',
                `image_name` varchar(255) NOT NULL,
                `image_tag` varchar(50) NOT NULL DEFAULT 'latest',
                `replica_count` int(11) NOT NULL DEFAULT 1,
                `cpu_request` varchar(20) DEFAULT '100m',
                `memory_request` varchar(20) DEFAULT '128Mi',
                `cpu_limit` varchar(20) DEFAULT '500m',
                `memory_limit` varchar(20) DEFAULT '512Mi',
                `container_port` int(11) DEFAULT 80,
                `environment_variables` text,
                `deployment_strategy` enum('rolling_update','blue_green','canary','recreate') NOT NULL DEFAULT 'rolling_update',
                `status` enum('pending','running','stopped','failed','terminating') NOT NULL DEFAULT 'pending',
                `kubernetes_uid` varchar(100) DEFAULT NULL,
                `health_check_path` varchar(255) DEFAULT '/health',
                `readiness_check_path` varchar(255) DEFAULT '/ready',
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `deployed_at` datetime DEFAULT NULL,
                `last_scaled_at` datetime DEFAULT NULL,
                PRIMARY KEY (`deployment_id`),
                UNIQUE KEY `unique_deployment` (`deployment_name`, `namespace`),
                KEY `idx_status` (`status`),
                KEY `idx_namespace` (`namespace`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Container Scaling Events tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_container_scaling_events` (
                `event_id` int(11) NOT NULL AUTO_INCREMENT,
                `deployment_id` int(11) NOT NULL,
                `event_type` enum('scale_up','scale_down','auto_scale','manual_scale') NOT NULL,
                `old_replica_count` int(11) NOT NULL,
                `new_replica_count` int(11) NOT NULL,
                `scaling_strategy` varchar(50) NOT NULL,
                `trigger_reason` text,
                `cpu_usage_before` decimal(5,2) DEFAULT NULL,
                `memory_usage_before` decimal(5,2) DEFAULT NULL,
                `cpu_usage_after` decimal(5,2) DEFAULT NULL,
                `memory_usage_after` decimal(5,2) DEFAULT NULL,
                `status` enum('pending','in_progress','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
                `started_at` datetime NOT NULL,
                `completed_at` datetime DEFAULT NULL,
                `duration_seconds` int(11) DEFAULT NULL,
                `error_message` text,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`event_id`),
                KEY `idx_deployment_id` (`deployment_id`),
                KEY `idx_event_type` (`event_type`),
                KEY `idx_status` (`status`),
                KEY `idx_started_at` (`started_at`),
                FOREIGN KEY (`deployment_id`) REFERENCES `" . DB_PREFIX . "meschain_container_deployments` (`deployment_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Container Health Checks tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_container_health_checks` (
                `health_check_id` int(11) NOT NULL AUTO_INCREMENT,
                `deployment_id` int(11) NOT NULL,
                `pod_name` varchar(100) NOT NULL,
                `check_type` enum('liveness','readiness','startup') NOT NULL,
                `endpoint` varchar(255) NOT NULL,
                `status` enum('healthy','unhealthy','unknown') NOT NULL,
                `response_time_ms` int(11) DEFAULT NULL,
                `response_code` int(11) DEFAULT NULL,
                `response_body` text,
                `error_message` text,
                `checked_at` datetime NOT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`health_check_id`),
                KEY `idx_deployment_id` (`deployment_id`),
                KEY `idx_pod_name` (`pod_name`),
                KEY `idx_check_type` (`check_type`),
                KEY `idx_status` (`status`),
                KEY `idx_checked_at` (`checked_at`),
                FOREIGN KEY (`deployment_id`) REFERENCES `" . DB_PREFIX . "meschain_container_deployments` (`deployment_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Container Resource Metrics tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_container_metrics` (
                `metric_id` int(11) NOT NULL AUTO_INCREMENT,
                `deployment_id` int(11) NOT NULL,
                `pod_name` varchar(100) NOT NULL,
                `container_name` varchar(100) NOT NULL,
                `metric_type` enum('cpu','memory','network','disk') NOT NULL,
                `metric_value` decimal(10,2) NOT NULL,
                `metric_unit` varchar(20) NOT NULL,
                `usage_percentage` decimal(5,2) DEFAULT NULL,
                `limit_value` decimal(10,2) DEFAULT NULL,
                `request_value` decimal(10,2) DEFAULT NULL,
                `timestamp` datetime NOT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`metric_id`),
                KEY `idx_deployment_id` (`deployment_id`),
                KEY `idx_pod_name` (`pod_name`),
                KEY `idx_metric_type` (`metric_type`),
                KEY `idx_timestamp` (`timestamp`),
                FOREIGN KEY (`deployment_id`) REFERENCES `" . DB_PREFIX . "meschain_container_deployments` (`deployment_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Container Auto-scaling Configuration tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_container_autoscaling` (
                `autoscaling_id` int(11) NOT NULL AUTO_INCREMENT,
                `deployment_id` int(11) NOT NULL,
                `hpa_name` varchar(100) NOT NULL,
                `min_replicas` int(11) NOT NULL DEFAULT 1,
                `max_replicas` int(11) NOT NULL DEFAULT 10,
                `cpu_threshold` int(11) DEFAULT 70,
                `memory_threshold` int(11) DEFAULT 80,
                `scale_up_cooldown` int(11) DEFAULT 300,
                `scale_down_cooldown` int(11) DEFAULT 600,
                `enable_vpa` tinyint(1) NOT NULL DEFAULT 0,
                `vpa_update_mode` enum('Off','Initial','Auto') DEFAULT 'Auto',
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`autoscaling_id`),
                UNIQUE KEY `unique_hpa` (`deployment_id`),
                KEY `idx_is_active` (`is_active`),
                FOREIGN KEY (`deployment_id`) REFERENCES `" . DB_PREFIX . "meschain_container_deployments` (`deployment_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Varsayılan deployment'ları ekle
        $this->insertDefaultDeployments();
    }
    
    /**
     * Container orchestration tablolarını kaldırır
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_container_autoscaling`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_container_metrics`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_container_health_checks`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_container_scaling_events`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_container_deployments`");
    }
    
    /**
     * Yeni deployment kaydeder
     */
    public function saveDeployment($data) {
        $sql = "INSERT INTO `" . DB_PREFIX . "meschain_container_deployments` SET 
                `deployment_name` = '" . $this->db->escape($data['deployment_name']) . "',
                `namespace` = '" . $this->db->escape($data['namespace'] ?? 'meschain-sync') . "',
                `image_name` = '" . $this->db->escape($data['image_name']) . "',
                `image_tag` = '" . $this->db->escape($data['image_tag'] ?? 'latest') . "',
                `replica_count` = '" . (int)$data['replica_count'] . "',
                `cpu_request` = '" . $this->db->escape($data['cpu_request'] ?? '100m') . "',
                `memory_request` = '" . $this->db->escape($data['memory_request'] ?? '128Mi') . "',
                `cpu_limit` = '" . $this->db->escape($data['cpu_limit'] ?? '500m') . "',
                `memory_limit` = '" . $this->db->escape($data['memory_limit'] ?? '512Mi') . "',
                `container_port` = '" . (int)($data['container_port'] ?? 80) . "',
                `environment_variables` = '" . $this->db->escape($data['environment_variables'] ?? '') . "',
                `deployment_strategy` = '" . $this->db->escape($data['deployment_strategy'] ?? 'rolling_update') . "',
                `status` = '" . $this->db->escape($data['status'] ?? 'pending') . "',
                `kubernetes_uid` = '" . $this->db->escape($data['kubernetes_uid'] ?? '') . "',
                `health_check_path` = '" . $this->db->escape($data['health_check_path'] ?? '/health') . "',
                `readiness_check_path` = '" . $this->db->escape($data['readiness_check_path'] ?? '/ready') . "'";
        
        if (!empty($data['deployed_at'])) {
            $sql .= ", `deployed_at` = '" . $this->db->escape($data['deployed_at']) . "'";
        }
        
        $this->db->query($sql);
        return $this->db->getLastId();
    }
    
    /**
     * Deployment'ı günceller
     */
    public function updateDeployment($deployment_id, $data) {
        $sql = "UPDATE `" . DB_PREFIX . "meschain_container_deployments` SET ";
        $updates = array();
        
        if (isset($data['replica_count'])) {
            $updates[] = "`replica_count` = '" . (int)$data['replica_count'] . "'";
        }
        
        if (isset($data['status'])) {
            $updates[] = "`status` = '" . $this->db->escape($data['status']) . "'";
        }
        
        if (isset($data['kubernetes_uid'])) {
            $updates[] = "`kubernetes_uid` = '" . $this->db->escape($data['kubernetes_uid']) . "'";
        }
        
        if (isset($data['deployed_at'])) {
            $updates[] = "`deployed_at` = '" . $this->db->escape($data['deployed_at']) . "'";
        }
        
        if (isset($data['last_scaled_at'])) {
            $updates[] = "`last_scaled_at` = '" . $this->db->escape($data['last_scaled_at']) . "'";
        }
        
        if (!empty($updates)) {
            $sql .= implode(', ', $updates);
            $sql .= " WHERE `deployment_id` = '" . (int)$deployment_id . "'";
            $this->db->query($sql);
        }
    }
    
    /**
     * Deployment'ı getirir
     */
    public function getDeployment($deployment_id) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_container_deployments` 
                WHERE `deployment_id` = '" . (int)$deployment_id . "'";
        
        $query = $this->db->query($sql);
        return $query->num_rows ? $query->row : null;
    }
    
    /**
     * Deployment'ı isimle getirir
     */
    public function getDeploymentByName($deployment_name, $namespace = 'meschain-sync') {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_container_deployments` 
                WHERE `deployment_name` = '" . $this->db->escape($deployment_name) . "' 
                AND `namespace` = '" . $this->db->escape($namespace) . "'";
        
        $query = $this->db->query($sql);
        return $query->num_rows ? $query->row : null;
    }
    
    /**
     * Tüm deployment'ları getirir
     */
    public function getDeployments($filters = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_container_deployments` WHERE 1=1";
        
        if (!empty($filters['namespace'])) {
            $sql .= " AND `namespace` = '" . $this->db->escape($filters['namespace']) . "'";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND `status` = '" . $this->db->escape($filters['status']) . "'";
        }
        
        if (!empty($filters['image_name'])) {
            $sql .= " AND `image_name` LIKE '%" . $this->db->escape($filters['image_name']) . "%'";
        }
        
        $sql .= " ORDER BY `created_at` DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Deployment'ı siler
     */
    public function deleteDeployment($deployment_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_container_deployments` 
                         WHERE `deployment_id` = '" . (int)$deployment_id . "'");
    }
    
    /**
     * Scaling event'i kaydeder
     */
    public function saveScalingEvent($data) {
        $sql = "INSERT INTO `" . DB_PREFIX . "meschain_container_scaling_events` SET 
                `deployment_id` = '" . (int)$data['deployment_id'] . "',
                `event_type` = '" . $this->db->escape($data['event_type']) . "',
                `old_replica_count` = '" . (int)$data['old_replica_count'] . "',
                `new_replica_count` = '" . (int)$data['new_replica_count'] . "',
                `scaling_strategy` = '" . $this->db->escape($data['scaling_strategy']) . "',
                `trigger_reason` = '" . $this->db->escape($data['trigger_reason'] ?? '') . "',
                `cpu_usage_before` = '" . ($data['cpu_usage_before'] ?? 'NULL') . "',
                `memory_usage_before` = '" . ($data['memory_usage_before'] ?? 'NULL') . "',
                `status` = '" . $this->db->escape($data['status'] ?? 'pending') . "',
                `started_at` = '" . $this->db->escape($data['started_at'] ?? date('Y-m-d H:i:s')) . "'";
        
        $this->db->query($sql);
        return $this->db->getLastId();
    }
    
    /**
     * Scaling event'ini günceller
     */
    public function updateScalingEvent($event_id, $data) {
        $sql = "UPDATE `" . DB_PREFIX . "meschain_container_scaling_events` SET ";
        $updates = array();
        
        if (isset($data['status'])) {
            $updates[] = "`status` = '" . $this->db->escape($data['status']) . "'";
        }
        
        if (isset($data['completed_at'])) {
            $updates[] = "`completed_at` = '" . $this->db->escape($data['completed_at']) . "'";
        }
        
        if (isset($data['duration_seconds'])) {
            $updates[] = "`duration_seconds` = '" . (int)$data['duration_seconds'] . "'";
        }
        
        if (isset($data['error_message'])) {
            $updates[] = "`error_message` = '" . $this->db->escape($data['error_message']) . "'";
        }
        
        if (isset($data['cpu_usage_after'])) {
            $updates[] = "`cpu_usage_after` = '" . (float)$data['cpu_usage_after'] . "'";
        }
        
        if (isset($data['memory_usage_after'])) {
            $updates[] = "`memory_usage_after` = '" . (float)$data['memory_usage_after'] . "'";
        }
        
        if (!empty($updates)) {
            $sql .= implode(', ', $updates);
            $sql .= " WHERE `event_id` = '" . (int)$event_id . "'";
            $this->db->query($sql);
        }
    }
    
    /**
     * Scaling event'lerini getirir
     */
    public function getScalingEvents($filters = array()) {
        $sql = "SELECT se.*, cd.deployment_name, cd.namespace 
                FROM `" . DB_PREFIX . "meschain_container_scaling_events` se
                LEFT JOIN `" . DB_PREFIX . "meschain_container_deployments` cd ON se.deployment_id = cd.deployment_id
                WHERE 1=1";
        
        if (!empty($filters['deployment_id'])) {
            $sql .= " AND se.deployment_id = '" . (int)$filters['deployment_id'] . "'";
        }
        
        if (!empty($filters['event_type'])) {
            $sql .= " AND se.event_type = '" . $this->db->escape($filters['event_type']) . "'";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND se.status = '" . $this->db->escape($filters['status']) . "'";
        }
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND se.started_at >= '" . $this->db->escape($filters['start_date']) . "'";
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND se.started_at <= '" . $this->db->escape($filters['end_date']) . "'";
        }
        
        $sql .= " ORDER BY se.started_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Health check kaydeder
     */
    public function saveHealthCheck($data) {
        $sql = "INSERT INTO `" . DB_PREFIX . "meschain_container_health_checks` SET 
                `deployment_id` = '" . (int)$data['deployment_id'] . "',
                `pod_name` = '" . $this->db->escape($data['pod_name']) . "',
                `check_type` = '" . $this->db->escape($data['check_type']) . "',
                `endpoint` = '" . $this->db->escape($data['endpoint']) . "',
                `status` = '" . $this->db->escape($data['status']) . "',
                `response_time_ms` = '" . (int)($data['response_time_ms'] ?? 0) . "',
                `response_code` = '" . (int)($data['response_code'] ?? 0) . "',
                `response_body` = '" . $this->db->escape($data['response_body'] ?? '') . "',
                `error_message` = '" . $this->db->escape($data['error_message'] ?? '') . "',
                `checked_at` = '" . $this->db->escape($data['checked_at'] ?? date('Y-m-d H:i:s')) . "'";
        
        $this->db->query($sql);
        return $this->db->getLastId();
    }
    
    /**
     * Health check'leri getirir
     */
    public function getHealthChecks($filters = array()) {
        $sql = "SELECT hc.*, cd.deployment_name, cd.namespace 
                FROM `" . DB_PREFIX . "meschain_container_health_checks` hc
                LEFT JOIN `" . DB_PREFIX . "meschain_container_deployments` cd ON hc.deployment_id = cd.deployment_id
                WHERE 1=1";
        
        if (!empty($filters['deployment_id'])) {
            $sql .= " AND hc.deployment_id = '" . (int)$filters['deployment_id'] . "'";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND hc.status = '" . $this->db->escape($filters['status']) . "'";
        }
        
        if (!empty($filters['check_type'])) {
            $sql .= " AND hc.check_type = '" . $this->db->escape($filters['check_type']) . "'";
        }
        
        if (!empty($filters['since'])) {
            $sql .= " AND hc.checked_at >= '" . $this->db->escape($filters['since']) . "'";
        }
        
        $sql .= " ORDER BY hc.checked_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Container metriği kaydeder
     */
    public function saveContainerMetric($data) {
        $sql = "INSERT INTO `" . DB_PREFIX . "meschain_container_metrics` SET 
                `deployment_id` = '" . (int)$data['deployment_id'] . "',
                `pod_name` = '" . $this->db->escape($data['pod_name']) . "',
                `container_name` = '" . $this->db->escape($data['container_name']) . "',
                `metric_type` = '" . $this->db->escape($data['metric_type']) . "',
                `metric_value` = '" . (float)$data['metric_value'] . "',
                `metric_unit` = '" . $this->db->escape($data['metric_unit']) . "',
                `usage_percentage` = '" . ($data['usage_percentage'] ?? 'NULL') . "',
                `limit_value` = '" . ($data['limit_value'] ?? 'NULL') . "',
                `request_value` = '" . ($data['request_value'] ?? 'NULL') . "',
                `timestamp` = '" . $this->db->escape($data['timestamp'] ?? date('Y-m-d H:i:s')) . "'";
        
        $this->db->query($sql);
        return $this->db->getLastId();
    }
    
    /**
     * Container metriklerini getirir
     */
    public function getContainerMetrics($filters = array()) {
        $sql = "SELECT cm.*, cd.deployment_name, cd.namespace 
                FROM `" . DB_PREFIX . "meschain_container_metrics` cm
                LEFT JOIN `" . DB_PREFIX . "meschain_container_deployments` cd ON cm.deployment_id = cd.deployment_id
                WHERE 1=1";
        
        if (!empty($filters['deployment_id'])) {
            $sql .= " AND cm.deployment_id = '" . (int)$filters['deployment_id'] . "'";
        }
        
        if (!empty($filters['metric_type'])) {
            $sql .= " AND cm.metric_type = '" . $this->db->escape($filters['metric_type']) . "'";
        }
        
        if (!empty($filters['pod_name'])) {
            $sql .= " AND cm.pod_name = '" . $this->db->escape($filters['pod_name']) . "'";
        }
        
        if (!empty($filters['start_time'])) {
            $sql .= " AND cm.timestamp >= '" . $this->db->escape($filters['start_time']) . "'";
        }
        
        if (!empty($filters['end_time'])) {
            $sql .= " AND cm.timestamp <= '" . $this->db->escape($filters['end_time']) . "'";
        }
        
        $sql .= " ORDER BY cm.timestamp DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Auto-scaling konfigürasyonu kaydeder
     */
    public function saveAutoscalingConfig($data) {
        $sql = "INSERT INTO `" . DB_PREFIX . "meschain_container_autoscaling` SET 
                `deployment_id` = '" . (int)$data['deployment_id'] . "',
                `hpa_name` = '" . $this->db->escape($data['hpa_name']) . "',
                `min_replicas` = '" . (int)$data['min_replicas'] . "',
                `max_replicas` = '" . (int)$data['max_replicas'] . "',
                `cpu_threshold` = '" . (int)($data['cpu_threshold'] ?? 70) . "',
                `memory_threshold` = '" . (int)($data['memory_threshold'] ?? 80) . "',
                `scale_up_cooldown` = '" . (int)($data['scale_up_cooldown'] ?? 300) . "',
                `scale_down_cooldown` = '" . (int)($data['scale_down_cooldown'] ?? 600) . "',
                `enable_vpa` = '" . (int)($data['enable_vpa'] ?? 0) . "',
                `vpa_update_mode` = '" . $this->db->escape($data['vpa_update_mode'] ?? 'Auto') . "',
                `is_active` = '" . (int)($data['is_active'] ?? 1) . "'
                ON DUPLICATE KEY UPDATE 
                `hpa_name` = '" . $this->db->escape($data['hpa_name']) . "',
                `min_replicas` = '" . (int)$data['min_replicas'] . "',
                `max_replicas` = '" . (int)$data['max_replicas'] . "',
                `cpu_threshold` = '" . (int)($data['cpu_threshold'] ?? 70) . "',
                `memory_threshold` = '" . (int)($data['memory_threshold'] ?? 80) . "',
                `scale_up_cooldown` = '" . (int)($data['scale_up_cooldown'] ?? 300) . "',
                `scale_down_cooldown` = '" . (int)($data['scale_down_cooldown'] ?? 600) . "',
                `enable_vpa` = '" . (int)($data['enable_vpa'] ?? 0) . "',
                `vpa_update_mode` = '" . $this->db->escape($data['vpa_update_mode'] ?? 'Auto') . "',
                `is_active` = '" . (int)($data['is_active'] ?? 1) . "'";
        
        $this->db->query($sql);
    }
    
    /**
     * Auto-scaling konfigürasyonunu getirir
     */
    public function getAutoscalingConfig($deployment_id) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_container_autoscaling` 
                WHERE `deployment_id` = '" . (int)$deployment_id . "'";
        
        $query = $this->db->query($sql);
        return $query->num_rows ? $query->row : null;
    }
    
    /**
     * Orchestration istatistiklerini getirir
     */
    public function getOrchestrationStats() {
        $stats = array();
        
        // Toplam deployment sayısı
        $sql = "SELECT COUNT(*) as total_deployments FROM `" . DB_PREFIX . "meschain_container_deployments`";
        $query = $this->db->query($sql);
        $stats['total_deployments'] = $query->row['total_deployments'];
        
        // Çalışan deployment sayısı
        $sql = "SELECT COUNT(*) as running_deployments FROM `" . DB_PREFIX . "meschain_container_deployments` 
                WHERE `status` = 'running'";
        $query = $this->db->query($sql);
        $stats['running_deployments'] = $query->row['running_deployments'];
        
        // Toplam replica sayısı
        $sql = "SELECT SUM(replica_count) as total_replicas FROM `" . DB_PREFIX . "meschain_container_deployments` 
                WHERE `status` = 'running'";
        $query = $this->db->query($sql);
        $stats['total_replicas'] = $query->row['total_replicas'] ?: 0;
        
        // Son 24 saatteki scaling event sayısı
        $sql = "SELECT COUNT(*) as scaling_events_24h FROM `" . DB_PREFIX . "meschain_container_scaling_events` 
                WHERE `started_at` >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $query = $this->db->query($sql);
        $stats['scaling_events_24h'] = $query->row['scaling_events_24h'];
        
        // Aktif auto-scaling konfigürasyonu sayısı
        $sql = "SELECT COUNT(*) as active_autoscaling FROM `" . DB_PREFIX . "meschain_container_autoscaling` 
                WHERE `is_active` = 1";
        $query = $this->db->query($sql);
        $stats['active_autoscaling'] = $query->row['active_autoscaling'];
        
        return $stats;
    }
    
    /**
     * Eski kayıtları temizler
     */
    public function cleanupOldRecords($days = 30) {
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        // Eski health check'leri sil
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_container_health_checks` 
                         WHERE `checked_at` < '" . $cutoff_date . "'");
        
        // Eski metrikleri sil
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_container_metrics` 
                         WHERE `timestamp` < '" . $cutoff_date . "'");
        
        // Eski scaling event'leri sil (completed olanları)
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_container_scaling_events` 
                         WHERE `started_at` < '" . $cutoff_date . "' AND `status` = 'completed'");
        
        return array(
            'cutoff_date' => $cutoff_date,
            'cleanup_completed' => true
        );
    }
    
    /**
     * Varsayılan deployment'ları ekler
     */
    private function insertDefaultDeployments() {
        $deployments = array(
            array(
                'deployment_name' => 'meschain-api',
                'image_name' => 'meschain/api',
                'image_tag' => 'v1.2.0',
                'replica_count' => 3,
                'cpu_request' => '200m',
                'memory_request' => '256Mi',
                'cpu_limit' => '1000m',
                'memory_limit' => '1Gi',
                'container_port' => 8080,
                'status' => 'running',
                'deployed_at' => date('Y-m-d H:i:s')
            ),
            array(
                'deployment_name' => 'meschain-worker',
                'image_name' => 'meschain/worker',
                'image_tag' => 'v1.1.0',
                'replica_count' => 2,
                'cpu_request' => '150m',
                'memory_request' => '192Mi',
                'cpu_limit' => '500m',
                'memory_limit' => '512Mi',
                'container_port' => 8081,
                'status' => 'running',
                'deployed_at' => date('Y-m-d H:i:s')
            ),
            array(
                'deployment_name' => 'meschain-scheduler',
                'image_name' => 'meschain/scheduler',
                'image_tag' => 'v1.0.5',
                'replica_count' => 1,
                'cpu_request' => '100m',
                'memory_request' => '128Mi',
                'cpu_limit' => '300m',
                'memory_limit' => '256Mi',
                'container_port' => 8082,
                'status' => 'running',
                'deployed_at' => date('Y-m-d H:i:s')
            )
        );
        
        foreach ($deployments as $deployment) {
            // Mevcut deployment'ı kontrol et
            $existing = $this->getDeploymentByName($deployment['deployment_name']);
            if (!$existing) {
                $this->saveDeployment($deployment);
            }
        }
    }
}
?> 