<?php
/**
 * MesChain-Sync Infrastructure Scaling Dashboard Model
 * 
 * @package    MesChain-Sync
 * @author     MesChain Development Team
 * @copyright  2024 MesChain
 * @license    Commercial License
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

class ModelExtensionModuleInfrastructureScalingDashboard extends Model {
    
    /**
     * Scaling dashboard tablolarını oluşturur
     */
    public function install() {
        // Infrastructure Scaling Metrics tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_infrastructure_metrics` (
                `metric_id` int(11) NOT NULL AUTO_INCREMENT,
                `metric_type` varchar(50) NOT NULL,
                `metric_name` varchar(100) NOT NULL,
                `metric_value` decimal(10,2) NOT NULL,
                `metric_unit` varchar(20) DEFAULT NULL,
                `node_id` varchar(50) DEFAULT NULL,
                `cluster_name` varchar(100) DEFAULT NULL,
                `namespace` varchar(100) DEFAULT NULL,
                `timestamp` datetime NOT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`metric_id`),
                KEY `idx_metric_type` (`metric_type`),
                KEY `idx_timestamp` (`timestamp`),
                KEY `idx_node_cluster` (`node_id`, `cluster_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Scaling Events tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_scaling_events` (
                `event_id` int(11) NOT NULL AUTO_INCREMENT,
                `event_type` enum('scale_up','scale_down','deployment','rollback','health_check') NOT NULL,
                `resource_type` varchar(50) NOT NULL,
                `resource_name` varchar(100) NOT NULL,
                `old_value` varchar(255) DEFAULT NULL,
                `new_value` varchar(255) DEFAULT NULL,
                `trigger_reason` text,
                `status` enum('pending','in_progress','completed','failed') NOT NULL DEFAULT 'pending',
                `started_at` datetime NOT NULL,
                `completed_at` datetime DEFAULT NULL,
                `error_message` text,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`event_id`),
                KEY `idx_event_type` (`event_type`),
                KEY `idx_resource` (`resource_type`, `resource_name`),
                KEY `idx_status` (`status`),
                KEY `idx_started_at` (`started_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Infrastructure Configuration tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_infrastructure_config` (
                `config_id` int(11) NOT NULL AUTO_INCREMENT,
                `config_category` varchar(50) NOT NULL,
                `config_key` varchar(100) NOT NULL,
                `config_value` text NOT NULL,
                `config_description` text,
                `is_encrypted` tinyint(1) NOT NULL DEFAULT 0,
                `environment` varchar(20) NOT NULL DEFAULT 'production',
                `updated_by` int(11) DEFAULT NULL,
                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`config_id`),
                UNIQUE KEY `unique_config` (`config_category`, `config_key`, `environment`),
                KEY `idx_category` (`config_category`),
                KEY `idx_environment` (`environment`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Varsayılan konfigürasyonları ekle
        $this->insertDefaultConfigurations();
    }
    
    /**
     * Scaling dashboard tablolarını kaldırır
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_infrastructure_metrics`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_scaling_events`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_infrastructure_config`");
    }
    
    /**
     * Infrastructure metriği kaydeder
     */
    public function saveMetric($data) {
        $sql = "INSERT INTO `" . DB_PREFIX . "meschain_infrastructure_metrics` SET 
                `metric_type` = '" . $this->db->escape($data['metric_type']) . "',
                `metric_name` = '" . $this->db->escape($data['metric_name']) . "',
                `metric_value` = '" . (float)$data['metric_value'] . "',
                `metric_unit` = '" . $this->db->escape($data['metric_unit'] ?? '') . "',
                `node_id` = '" . $this->db->escape($data['node_id'] ?? '') . "',
                `cluster_name` = '" . $this->db->escape($data['cluster_name'] ?? '') . "',
                `namespace` = '" . $this->db->escape($data['namespace'] ?? '') . "',
                `timestamp` = '" . $this->db->escape($data['timestamp'] ?? date('Y-m-d H:i:s')) . "'";
        
        $this->db->query($sql);
        return $this->db->getLastId();
    }
    
    /**
     * Belirli zaman aralığındaki metrikleri getirir
     */
    public function getMetrics($filters = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_infrastructure_metrics` WHERE 1=1";
        
        if (!empty($filters['metric_type'])) {
            $sql .= " AND `metric_type` = '" . $this->db->escape($filters['metric_type']) . "'";
        }
        
        if (!empty($filters['metric_name'])) {
            $sql .= " AND `metric_name` = '" . $this->db->escape($filters['metric_name']) . "'";
        }
        
        if (!empty($filters['node_id'])) {
            $sql .= " AND `node_id` = '" . $this->db->escape($filters['node_id']) . "'";
        }
        
        if (!empty($filters['cluster_name'])) {
            $sql .= " AND `cluster_name` = '" . $this->db->escape($filters['cluster_name']) . "'";
        }
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND `timestamp` >= '" . $this->db->escape($filters['start_date']) . "'";
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND `timestamp` <= '" . $this->db->escape($filters['end_date']) . "'";
        }
        
        $sql .= " ORDER BY `timestamp` DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Scaling event'i kaydeder
     */
    public function saveScalingEvent($data) {
        $sql = "INSERT INTO `" . DB_PREFIX . "meschain_scaling_events` SET 
                `event_type` = '" . $this->db->escape($data['event_type']) . "',
                `resource_type` = '" . $this->db->escape($data['resource_type']) . "',
                `resource_name` = '" . $this->db->escape($data['resource_name']) . "',
                `old_value` = '" . $this->db->escape($data['old_value'] ?? '') . "',
                `new_value` = '" . $this->db->escape($data['new_value'] ?? '') . "',
                `trigger_reason` = '" . $this->db->escape($data['trigger_reason'] ?? '') . "',
                `status` = '" . $this->db->escape($data['status'] ?? 'pending') . "',
                `started_at` = '" . $this->db->escape($data['started_at'] ?? date('Y-m-d H:i:s')) . "'";
        
        if (!empty($data['completed_at'])) {
            $sql .= ", `completed_at` = '" . $this->db->escape($data['completed_at']) . "'";
        }
        
        if (!empty($data['error_message'])) {
            $sql .= ", `error_message` = '" . $this->db->escape($data['error_message']) . "'";
        }
        
        $this->db->query($sql);
        return $this->db->getLastId();
    }
    
    /**
     * Scaling event'ini günceller
     */
    public function updateScalingEvent($event_id, $data) {
        $sql = "UPDATE `" . DB_PREFIX . "meschain_scaling_events` SET ";
        $updates = array();
        
        if (isset($data['status'])) {
            $updates[] = "`status` = '" . $this->db->escape($data['status']) . "'";
        }
        
        if (isset($data['completed_at'])) {
            $updates[] = "`completed_at` = '" . $this->db->escape($data['completed_at']) . "'";
        }
        
        if (isset($data['error_message'])) {
            $updates[] = "`error_message` = '" . $this->db->escape($data['error_message']) . "'";
        }
        
        if (isset($data['new_value'])) {
            $updates[] = "`new_value` = '" . $this->db->escape($data['new_value']) . "'";
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
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_scaling_events` WHERE 1=1";
        
        if (!empty($filters['event_type'])) {
            $sql .= " AND `event_type` = '" . $this->db->escape($filters['event_type']) . "'";
        }
        
        if (!empty($filters['resource_type'])) {
            $sql .= " AND `resource_type` = '" . $this->db->escape($filters['resource_type']) . "'";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND `status` = '" . $this->db->escape($filters['status']) . "'";
        }
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND `started_at` >= '" . $this->db->escape($filters['start_date']) . "'";
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND `started_at` <= '" . $this->db->escape($filters['end_date']) . "'";
        }
        
        $sql .= " ORDER BY `started_at` DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Konfigürasyon değerini kaydeder
     */
    public function saveConfiguration($category, $key, $value, $description = '', $environment = 'production') {
        $sql = "INSERT INTO `" . DB_PREFIX . "meschain_infrastructure_config` SET 
                `config_category` = '" . $this->db->escape($category) . "',
                `config_key` = '" . $this->db->escape($key) . "',
                `config_value` = '" . $this->db->escape($value) . "',
                `config_description` = '" . $this->db->escape($description) . "',
                `environment` = '" . $this->db->escape($environment) . "'
                ON DUPLICATE KEY UPDATE 
                `config_value` = '" . $this->db->escape($value) . "',
                `config_description` = '" . $this->db->escape($description) . "'";
        
        $this->db->query($sql);
    }
    
    /**
     * Konfigürasyon değerini getirir
     */
    public function getConfiguration($category, $key, $environment = 'production') {
        $sql = "SELECT `config_value` FROM `" . DB_PREFIX . "meschain_infrastructure_config` 
                WHERE `config_category` = '" . $this->db->escape($category) . "' 
                AND `config_key` = '" . $this->db->escape($key) . "' 
                AND `environment` = '" . $this->db->escape($environment) . "'";
        
        $query = $this->db->query($sql);
        return $query->num_rows ? $query->row['config_value'] : null;
    }
    
    /**
     * Kategori bazında konfigürasyonları getirir
     */
    public function getConfigurationsByCategory($category, $environment = 'production') {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_infrastructure_config` 
                WHERE `config_category` = '" . $this->db->escape($category) . "' 
                AND `environment` = '" . $this->db->escape($environment) . "'
                ORDER BY `config_key`";
        
        $query = $this->db->query($sql);
        
        $configs = array();
        foreach ($query->rows as $row) {
            $configs[$row['config_key']] = $row['config_value'];
        }
        
        return $configs;
    }
    
    /**
     * Performans istatistiklerini getirir
     */
    public function getPerformanceStats($hours = 24) {
        $start_time = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        
        $stats = array();
        
        // CPU kullanım ortalaması
        $sql = "SELECT AVG(metric_value) as avg_cpu FROM `" . DB_PREFIX . "meschain_infrastructure_metrics` 
                WHERE `metric_type` = 'kubernetes' AND `metric_name` = 'cpu_usage' 
                AND `timestamp` >= '" . $start_time . "'";
        $query = $this->db->query($sql);
        $stats['avg_cpu_usage'] = $query->num_rows ? round($query->row['avg_cpu'], 2) : 0;
        
        // Memory kullanım ortalaması
        $sql = "SELECT AVG(metric_value) as avg_memory FROM `" . DB_PREFIX . "meschain_infrastructure_metrics` 
                WHERE `metric_type` = 'kubernetes' AND `metric_name` = 'memory_usage' 
                AND `timestamp` >= '" . $start_time . "'";
        $query = $this->db->query($sql);
        $stats['avg_memory_usage'] = $query->num_rows ? round($query->row['avg_memory'], 2) : 0;
        
        // Toplam scaling event sayısı
        $sql = "SELECT COUNT(*) as total_events FROM `" . DB_PREFIX . "meschain_scaling_events` 
                WHERE `started_at` >= '" . $start_time . "'";
        $query = $this->db->query($sql);
        $stats['total_scaling_events'] = $query->num_rows ? $query->row['total_events'] : 0;
        
        // Başarılı scaling event sayısı
        $sql = "SELECT COUNT(*) as successful_events FROM `" . DB_PREFIX . "meschain_scaling_events` 
                WHERE `started_at` >= '" . $start_time . "' AND `status` = 'completed'";
        $query = $this->db->query($sql);
        $stats['successful_scaling_events'] = $query->num_rows ? $query->row['successful_events'] : 0;
        
        return $stats;
    }
    
    /**
     * Eski metrikleri temizler
     */
    public function cleanupOldMetrics($days = 30) {
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        // Eski metrikleri sil
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_infrastructure_metrics` 
                         WHERE `timestamp` < '" . $cutoff_date . "'");
        
        // Eski event'leri sil
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_scaling_events` 
                         WHERE `started_at` < '" . $cutoff_date . "'");
        
        return array(
            'deleted_metrics' => $this->db->countAffected(),
            'cutoff_date' => $cutoff_date
        );
    }
    
    /**
     * Varsayılan konfigürasyonları ekler
     */
    private function insertDefaultConfigurations() {
        $configs = array(
            array('kubernetes', 'cluster_name', 'meschain-production', 'Ana Kubernetes cluster adı'),
            array('kubernetes', 'namespace', 'meschain-sync', 'Varsayılan namespace'),
            array('kubernetes', 'cpu_threshold', '70', 'CPU kullanım eşiği (%)'),
            array('kubernetes', 'memory_threshold', '80', 'Memory kullanım eşiği (%)'),
            array('scaling', 'min_replicas', '2', 'Minimum replica sayısı'),
            array('scaling', 'max_replicas', '10', 'Maximum replica sayısı'),
            array('scaling', 'scale_up_cooldown', '300', 'Scale up cooldown süresi (saniye)'),
            array('scaling', 'scale_down_cooldown', '600', 'Scale down cooldown süresi (saniye)'),
            array('database', 'cluster_nodes', '3', 'Database cluster node sayısı'),
            array('database', 'replication_type', 'master-slave', 'Replikasyon tipi'),
            array('loadbalancer', 'algorithm', 'round_robin', 'Load balancing algoritması'),
            array('loadbalancer', 'health_check_interval', '30', 'Health check aralığı (saniye)'),
            array('cicd', 'pipeline_timeout', '1800', 'Pipeline timeout süresi (saniye)'),
            array('cicd', 'auto_deploy', '1', 'Otomatik deployment aktif mi'),
            array('monitoring', 'metric_retention_days', '30', 'Metrik saklama süresi (gün)'),
            array('monitoring', 'alert_cooldown', '300', 'Alert cooldown süresi (saniye)')
        );
        
        foreach ($configs as $config) {
            $this->saveConfiguration($config[0], $config[1], $config[2], $config[3]);
        }
    }
}