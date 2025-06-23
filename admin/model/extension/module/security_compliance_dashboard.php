<?php
/**
 * Security & Compliance Dashboard Model - ATOM-M009
 * MesChain-Sync Security & Compliance Excellence
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0 - ATOM-M009
 * @author Musti DevOps Team
 * @date 2025-06-11
 */

class ModelExtensionModuleSecurityComplianceDashboard extends Model {
    
    /**
     * Install security tables
     */
    public function install() {
        // Security Events Table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_security_events` (
            `event_id` int(11) NOT NULL AUTO_INCREMENT,
            `event_type` varchar(50) NOT NULL,
            `source` varchar(50) NOT NULL DEFAULT 'system',
            `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'low',
            `source_ip` varchar(45) DEFAULT NULL,
            `user_id` int(11) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `endpoint` varchar(255) DEFAULT NULL,
            `method` varchar(10) DEFAULT NULL,
            `threat_detected` tinyint(1) NOT NULL DEFAULT 0,
            `threat_score` decimal(3,2) DEFAULT 0.00,
            `threat_type` varchar(50) DEFAULT NULL,
            `event_data` longtext DEFAULT NULL,
            `processing_result` longtext DEFAULT NULL,
            `geo_data` text DEFAULT NULL,
            `timestamp` datetime NOT NULL,
            `processed_at` datetime DEFAULT NULL,
            PRIMARY KEY (`event_id`),
            KEY `idx_timestamp` (`timestamp`),
            KEY `idx_source` (`source`),
            KEY `idx_severity` (`severity`),
            KEY `idx_threat_detected` (`threat_detected`),
            KEY `idx_source_ip` (`source_ip`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Security Alerts Table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_security_alerts` (
            `alert_id` int(11) NOT NULL AUTO_INCREMENT,
            `alert_type` varchar(50) NOT NULL,
            `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
            `title` varchar(255) NOT NULL,
            `message` text NOT NULL,
            `source` varchar(50) NOT NULL DEFAULT 'system',
            `related_event_id` int(11) DEFAULT NULL,
            `status` enum('active','acknowledged','resolved','false_positive') NOT NULL DEFAULT 'active',
            `triggered_at` datetime NOT NULL,
            `acknowledged_at` datetime DEFAULT NULL,
            `acknowledged_by` varchar(100) DEFAULT NULL,
            `resolved_at` datetime DEFAULT NULL,
            `resolved_by` varchar(100) DEFAULT NULL,
            `resolution_notes` text DEFAULT NULL,
            `metadata` longtext DEFAULT NULL,
            PRIMARY KEY (`alert_id`),
            KEY `idx_triggered_at` (`triggered_at`),
            KEY `idx_status` (`status`),
            KEY `idx_severity` (`severity`),
            KEY `idx_alert_type` (`alert_type`),
            FOREIGN KEY (`related_event_id`) REFERENCES `" . DB_PREFIX . "meschain_security_events`(`event_id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Security Incidents Table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_security_incidents` (
            `incident_id` varchar(50) NOT NULL,
            `type` varchar(50) NOT NULL,
            `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
            `title` varchar(255) NOT NULL,
            `description` text NOT NULL,
            `status` enum('active','investigating','resolved','closed') NOT NULL DEFAULT 'active',
            `assigned_to` varchar(100) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            `resolved_at` datetime DEFAULT NULL,
            `resolution_summary` text DEFAULT NULL,
            `affected_systems` text DEFAULT NULL,
            `incident_data` longtext DEFAULT NULL,
            `response_actions` longtext DEFAULT NULL,
            `lessons_learned` text DEFAULT NULL,
            PRIMARY KEY (`incident_id`),
            KEY `idx_created_at` (`created_at`),
            KEY `idx_status` (`status`),
            KEY `idx_severity` (`severity`),
            KEY `idx_type` (`type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Vulnerability Scans Table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_vulnerability_scans` (
            `scan_id` varchar(50) NOT NULL,
            `scan_type` varchar(50) NOT NULL DEFAULT 'comprehensive',
            `target_type` varchar(50) NOT NULL DEFAULT 'web',
            `targets` text DEFAULT NULL,
            `status` enum('scheduled','running','completed','failed','cancelled') NOT NULL DEFAULT 'scheduled',
            `started_at` datetime NOT NULL,
            `completed_at` datetime DEFAULT NULL,
            `scan_duration` decimal(8,2) DEFAULT NULL,
            `vulnerabilities_found` int(11) NOT NULL DEFAULT 0,
            `critical_count` int(11) NOT NULL DEFAULT 0,
            `high_count` int(11) NOT NULL DEFAULT 0,
            `medium_count` int(11) NOT NULL DEFAULT 0,
            `low_count` int(11) NOT NULL DEFAULT 0,
            `info_count` int(11) NOT NULL DEFAULT 0,
            `scan_results` longtext DEFAULT NULL,
            `recommendations` longtext DEFAULT NULL,
            `scan_config` text DEFAULT NULL,
            `created_by` varchar(100) DEFAULT NULL,
            PRIMARY KEY (`scan_id`),
            KEY `idx_started_at` (`started_at`),
            KEY `idx_status` (`status`),
            KEY `idx_scan_type` (`scan_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Compliance Records Table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_compliance_records` (
            `record_id` int(11) NOT NULL AUTO_INCREMENT,
            `compliance_type` varchar(50) NOT NULL DEFAULT 'gdpr',
            `record_type` varchar(50) NOT NULL,
            `subject_id` varchar(100) DEFAULT NULL,
            `subject_type` varchar(50) DEFAULT NULL,
            `data_category` varchar(100) DEFAULT NULL,
            `processing_purpose` varchar(255) DEFAULT NULL,
            `legal_basis` varchar(100) DEFAULT NULL,
            `data_retention_period` int(11) DEFAULT NULL,
            `consent_given` tinyint(1) DEFAULT NULL,
            `consent_date` datetime DEFAULT NULL,
            `consent_withdrawn` tinyint(1) NOT NULL DEFAULT 0,
            `consent_withdrawn_date` datetime DEFAULT NULL,
            `data_exported` tinyint(1) NOT NULL DEFAULT 0,
            `data_exported_date` datetime DEFAULT NULL,
            `data_deleted` tinyint(1) NOT NULL DEFAULT 0,
            `data_deleted_date` datetime DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            `metadata` longtext DEFAULT NULL,
            PRIMARY KEY (`record_id`),
            KEY `idx_compliance_type` (`compliance_type`),
            KEY `idx_record_type` (`record_type`),
            KEY `idx_subject_id` (`subject_id`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Security Metrics Table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_security_metrics` (
            `metric_id` int(11) NOT NULL AUTO_INCREMENT,
            `metric_type` varchar(50) NOT NULL,
            `metric_name` varchar(100) NOT NULL,
            `metric_value` decimal(10,4) NOT NULL,
            `metric_unit` varchar(20) DEFAULT NULL,
            `source` varchar(50) NOT NULL DEFAULT 'system',
            `marketplace` varchar(50) DEFAULT NULL,
            `timestamp` datetime NOT NULL,
            `metadata` text DEFAULT NULL,
            PRIMARY KEY (`metric_id`),
            KEY `idx_timestamp` (`timestamp`),
            KEY `idx_metric_type` (`metric_type`),
            KEY `idx_metric_name` (`metric_name`),
            KEY `idx_source` (`source`),
            KEY `idx_marketplace` (`marketplace`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Security Configuration Table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_security_config` (
            `config_id` int(11) NOT NULL AUTO_INCREMENT,
            `config_group` varchar(50) NOT NULL DEFAULT 'general',
            `config_key` varchar(100) NOT NULL,
            `config_value` longtext NOT NULL,
            `config_type` enum('string','int','float','boolean','json','array') NOT NULL DEFAULT 'string',
            `description` text DEFAULT NULL,
            `is_sensitive` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` datetime NOT NULL,
            `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            `updated_by` varchar(100) DEFAULT NULL,
            PRIMARY KEY (`config_id`),
            UNIQUE KEY `idx_config_group_key` (`config_group`,`config_key`),
            KEY `idx_config_group` (`config_group`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Insert default security configuration
        $this->insertDefaultSecurityConfig();
        
        // Create indexes for performance
        $this->createSecurityIndexes();
    }
    
    /**
     * Uninstall security tables
     */
    public function uninstall() {
        $tables = [
            'meschain_security_events',
            'meschain_security_alerts',
            'meschain_security_incidents',
            'meschain_vulnerability_scans',
            'meschain_compliance_records',
            'meschain_security_metrics',
            'meschain_security_config'
        ];
        
        foreach ($tables as $table) {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . $table . "`");
        }
    }
    
    /**
     * Insert default security configuration
     */
    private function insertDefaultSecurityConfig() {
        $default_configs = [
            // SIEM Configuration
            ['siem', 'threat_detection_enabled', 'true', 'boolean', 'Enable threat detection system'],
            ['siem', 'anomaly_threshold', '0.8', 'float', 'Anomaly detection threshold (0-1)'],
            ['siem', 'real_time_analysis', 'true', 'boolean', 'Enable real-time threat analysis'],
            ['siem', 'behavioral_analysis', 'true', 'boolean', 'Enable behavioral analysis'],
            ['siem', 'geo_anomaly_detection', 'true', 'boolean', 'Enable geographical anomaly detection'],
            
            // Incident Response Configuration
            ['incident_response', 'auto_response_enabled', 'true', 'boolean', 'Enable automated incident response'],
            ['incident_response', 'escalation_timeout', '300', 'int', 'Escalation timeout in seconds'],
            ['incident_response', 'notification_channels', '["email","sms","slack"]', 'json', 'Notification channels for incidents'],
            ['incident_response', 'quarantine_enabled', 'true', 'boolean', 'Enable automatic quarantine for threats'],
            
            // Compliance Configuration
            ['compliance', 'gdpr_enabled', 'true', 'boolean', 'Enable GDPR compliance monitoring'],
            ['compliance', 'audit_retention_days', '365', 'int', 'Audit log retention period in days'],
            ['compliance', 'data_encryption', 'AES-256', 'string', 'Data encryption standard'],
            ['compliance', 'access_logging', 'true', 'boolean', 'Enable comprehensive access logging'],
            ['compliance', 'data_anonymization', 'true', 'boolean', 'Enable automatic data anonymization'],
            
            // Monitoring Configuration
            ['monitoring', 'log_sources', '["web","api","database","system","marketplace"]', 'json', 'Security log sources to monitor'],
            ['monitoring', 'real_time_correlation', 'true', 'boolean', 'Enable real-time event correlation'],
            ['monitoring', 'threat_intelligence', 'true', 'boolean', 'Enable threat intelligence feeds'],
            ['monitoring', 'vulnerability_scanning', 'true', 'boolean', 'Enable automated vulnerability scanning'],
            
            // API Security Configuration
            ['api_security', 'rate_limiting_enabled', 'true', 'boolean', 'Enable API rate limiting'],
            ['api_security', 'default_rate_limit', '1000', 'int', 'Default API rate limit per hour'],
            ['api_security', 'auth_failure_threshold', '5', 'int', 'Max authentication failures before blocking'],
            ['api_security', 'api_key_rotation_days', '90', 'int', 'API key rotation period in days'],
            
            // Marketplace Security Configuration
            ['marketplace_security', 'trendyol_security_level', 'high', 'string', 'Trendyol security monitoring level'],
            ['marketplace_security', 'n11_security_level', 'high', 'string', 'N11 security monitoring level'],
            ['marketplace_security', 'amazon_security_level', 'high', 'string', 'Amazon security monitoring level'],
            ['marketplace_security', 'hepsiburada_security_level', 'medium', 'string', 'Hepsiburada security monitoring level'],
            ['marketplace_security', 'ebay_security_level', 'medium', 'string', 'eBay security monitoring level'],
            ['marketplace_security', 'ozon_security_level', 'high', 'string', 'Ozon security monitoring level'],
            
            // Vulnerability Management
            ['vulnerability', 'scan_frequency_hours', '24', 'int', 'Vulnerability scan frequency in hours'],
            ['vulnerability', 'auto_remediation', 'false', 'boolean', 'Enable automatic vulnerability remediation'],
            ['vulnerability', 'critical_alert_threshold', '1', 'int', 'Critical vulnerability count for immediate alert'],
            ['vulnerability', 'scan_timeout_minutes', '60', 'int', 'Vulnerability scan timeout in minutes']
        ];
        
        foreach ($default_configs as $config) {
            $this->db->query("
                INSERT IGNORE INTO " . DB_PREFIX . "meschain_security_config 
                (config_group, config_key, config_value, config_type, description, created_at) 
                VALUES (
                    '" . $this->db->escape($config[0]) . "',
                    '" . $this->db->escape($config[1]) . "',
                    '" . $this->db->escape($config[2]) . "',
                    '" . $this->db->escape($config[3]) . "',
                    '" . $this->db->escape($config[4]) . "',
                    NOW()
                )
            ");
        }
    }
    
    /**
     * Create additional indexes for performance
     */
    private function createSecurityIndexes() {
        // Composite indexes for common queries
        $indexes = [
            "CREATE INDEX idx_events_threat_time ON " . DB_PREFIX . "meschain_security_events (threat_detected, timestamp)",
            "CREATE INDEX idx_events_source_severity ON " . DB_PREFIX . "meschain_security_events (source, severity)",
            "CREATE INDEX idx_alerts_status_severity ON " . DB_PREFIX . "meschain_security_alerts (status, severity)",
            "CREATE INDEX idx_metrics_type_time ON " . DB_PREFIX . "meschain_security_metrics (metric_type, timestamp)",
            "CREATE INDEX idx_compliance_type_subject ON " . DB_PREFIX . "meschain_compliance_records (compliance_type, subject_id)"
        ];
        
        foreach ($indexes as $index) {
            try {
                $this->db->query($index);
            } catch (Exception $e) {
                // Index might already exist, continue
            }
        }
    }
    
    /**
     * Record security event
     *
     * @param array $event_data Event data
     * @return int Event ID
     */
    public function recordSecurityEvent($event_data) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_security_events 
            (event_type, source, severity, source_ip, user_id, user_agent, endpoint, method, 
             threat_detected, threat_score, threat_type, event_data, processing_result, 
             geo_data, timestamp, processed_at) 
            VALUES (
                '" . $this->db->escape($event_data['event_type']) . "',
                '" . $this->db->escape($event_data['source'] ?? 'system') . "',
                '" . $this->db->escape($event_data['severity'] ?? 'low') . "',
                '" . $this->db->escape($event_data['source_ip'] ?? null) . "',
                " . (isset($event_data['user_id']) ? intval($event_data['user_id']) : 'NULL') . ",
                '" . $this->db->escape($event_data['user_agent'] ?? null) . "',
                '" . $this->db->escape($event_data['endpoint'] ?? null) . "',
                '" . $this->db->escape($event_data['method'] ?? null) . "',
                " . (isset($event_data['threat_detected']) ? intval($event_data['threat_detected']) : 0) . ",
                " . floatval($event_data['threat_score'] ?? 0) . ",
                '" . $this->db->escape($event_data['threat_type'] ?? null) . "',
                '" . $this->db->escape(json_encode($event_data['event_data'] ?? [])) . "',
                '" . $this->db->escape(json_encode($event_data['processing_result'] ?? [])) . "',
                '" . $this->db->escape(json_encode($event_data['geo_data'] ?? [])) . "',
                '" . $this->db->escape($event_data['timestamp'] ?? date('Y-m-d H:i:s')) . "',
                NOW()
            )
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Create security alert
     *
     * @param array $alert_data Alert data
     * @return int Alert ID
     */
    public function createSecurityAlert($alert_data) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_security_alerts 
            (alert_type, severity, title, message, source, related_event_id, status, 
             triggered_at, metadata) 
            VALUES (
                '" . $this->db->escape($alert_data['alert_type']) . "',
                '" . $this->db->escape($alert_data['severity'] ?? 'medium') . "',
                '" . $this->db->escape($alert_data['title']) . "',
                '" . $this->db->escape($alert_data['message']) . "',
                '" . $this->db->escape($alert_data['source'] ?? 'system') . "',
                " . (isset($alert_data['related_event_id']) ? intval($alert_data['related_event_id']) : 'NULL') . ",
                '" . $this->db->escape($alert_data['status'] ?? 'active') . "',
                '" . $this->db->escape($alert_data['triggered_at'] ?? date('Y-m-d H:i:s')) . "',
                '" . $this->db->escape(json_encode($alert_data['metadata'] ?? [])) . "'
            )
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Create security incident
     *
     * @param array $incident_data Incident data
     * @return string Incident ID
     */
    public function createSecurityIncident($incident_data) {
        $incident_id = $incident_data['incident_id'] ?? 'INC-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
        
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_security_incidents 
            (incident_id, type, severity, title, description, status, assigned_to, 
             created_at, affected_systems, incident_data, response_actions) 
            VALUES (
                '" . $this->db->escape($incident_id) . "',
                '" . $this->db->escape($incident_data['type']) . "',
                '" . $this->db->escape($incident_data['severity'] ?? 'medium') . "',
                '" . $this->db->escape($incident_data['title']) . "',
                '" . $this->db->escape($incident_data['description']) . "',
                '" . $this->db->escape($incident_data['status'] ?? 'active') . "',
                '" . $this->db->escape($incident_data['assigned_to'] ?? null) . "',
                '" . $this->db->escape($incident_data['created_at'] ?? date('Y-m-d H:i:s')) . "',
                '" . $this->db->escape(json_encode($incident_data['affected_systems'] ?? [])) . "',
                '" . $this->db->escape(json_encode($incident_data['incident_data'] ?? [])) . "',
                '" . $this->db->escape(json_encode($incident_data['response_actions'] ?? [])) . "'
            )
        ");
        
        return $incident_id;
    }
    
    /**
     * Store vulnerability scan results
     *
     * @param array $scan_data Scan data
     * @return string Scan ID
     */
    public function storeVulnerabilityScan($scan_data) {
        $scan_id = $scan_data['scan_id'] ?? 'SCAN-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
        
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_vulnerability_scans 
            (scan_id, scan_type, target_type, targets, status, started_at, completed_at, 
             scan_duration, vulnerabilities_found, critical_count, high_count, medium_count, 
             low_count, info_count, scan_results, recommendations, scan_config, created_by) 
            VALUES (
                '" . $this->db->escape($scan_id) . "',
                '" . $this->db->escape($scan_data['scan_type'] ?? 'comprehensive') . "',
                '" . $this->db->escape($scan_data['target_type'] ?? 'web') . "',
                '" . $this->db->escape(json_encode($scan_data['targets'] ?? [])) . "',
                '" . $this->db->escape($scan_data['status'] ?? 'completed') . "',
                '" . $this->db->escape($scan_data['started_at'] ?? date('Y-m-d H:i:s')) . "',
                '" . $this->db->escape($scan_data['completed_at'] ?? date('Y-m-d H:i:s')) . "',
                " . floatval($scan_data['scan_duration'] ?? 0) . ",
                " . intval($scan_data['vulnerabilities_found'] ?? 0) . ",
                " . intval($scan_data['critical_count'] ?? 0) . ",
                " . intval($scan_data['high_count'] ?? 0) . ",
                " . intval($scan_data['medium_count'] ?? 0) . ",
                " . intval($scan_data['low_count'] ?? 0) . ",
                " . intval($scan_data['info_count'] ?? 0) . ",
                '" . $this->db->escape(json_encode($scan_data['scan_results'] ?? [])) . "',
                '" . $this->db->escape(json_encode($scan_data['recommendations'] ?? [])) . "',
                '" . $this->db->escape(json_encode($scan_data['scan_config'] ?? [])) . "',
                '" . $this->db->escape($scan_data['created_by'] ?? 'system') . "'
            )
        ");
        
        return $scan_id;
    }
    
    /**
     * Record compliance data
     *
     * @param array $compliance_data Compliance data
     * @return int Record ID
     */
    public function recordComplianceData($compliance_data) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_compliance_records 
            (compliance_type, record_type, subject_id, subject_type, data_category, 
             processing_purpose, legal_basis, data_retention_period, consent_given, 
             consent_date, created_at, metadata) 
            VALUES (
                '" . $this->db->escape($compliance_data['compliance_type'] ?? 'gdpr') . "',
                '" . $this->db->escape($compliance_data['record_type']) . "',
                '" . $this->db->escape($compliance_data['subject_id'] ?? null) . "',
                '" . $this->db->escape($compliance_data['subject_type'] ?? null) . "',
                '" . $this->db->escape($compliance_data['data_category'] ?? null) . "',
                '" . $this->db->escape($compliance_data['processing_purpose'] ?? null) . "',
                '" . $this->db->escape($compliance_data['legal_basis'] ?? null) . "',
                " . (isset($compliance_data['data_retention_period']) ? intval($compliance_data['data_retention_period']) : 'NULL') . ",
                " . (isset($compliance_data['consent_given']) ? intval($compliance_data['consent_given']) : 'NULL') . ",
                '" . $this->db->escape($compliance_data['consent_date'] ?? null) . "',
                '" . $this->db->escape($compliance_data['created_at'] ?? date('Y-m-d H:i:s')) . "',
                '" . $this->db->escape(json_encode($compliance_data['metadata'] ?? [])) . "'
            )
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Store security metric
     *
     * @param array $metric_data Metric data
     * @return int Metric ID
     */
    public function storeSecurityMetric($metric_data) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_security_metrics 
            (metric_type, metric_name, metric_value, metric_unit, source, marketplace, 
             timestamp, metadata) 
            VALUES (
                '" . $this->db->escape($metric_data['metric_type']) . "',
                '" . $this->db->escape($metric_data['metric_name']) . "',
                " . floatval($metric_data['metric_value']) . ",
                '" . $this->db->escape($metric_data['metric_unit'] ?? null) . "',
                '" . $this->db->escape($metric_data['source'] ?? 'system') . "',
                '" . $this->db->escape($metric_data['marketplace'] ?? null) . "',
                '" . $this->db->escape($metric_data['timestamp'] ?? date('Y-m-d H:i:s')) . "',
                '" . $this->db->escape(json_encode($metric_data['metadata'] ?? [])) . "'
            )
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Get security configuration
     *
     * @param string $group Configuration group
     * @param string $key Configuration key
     * @return mixed Configuration value
     */
    public function getSecurityConfig($group, $key = null) {
        if ($key) {
            $query = $this->db->query("
                SELECT config_value, config_type 
                FROM " . DB_PREFIX . "meschain_security_config 
                WHERE config_group = '" . $this->db->escape($group) . "' 
                AND config_key = '" . $this->db->escape($key) . "'
            ");
            
            if ($query->num_rows) {
                return $this->castConfigValue($query->row['config_value'], $query->row['config_type']);
            }
            
            return null;
        } else {
            $query = $this->db->query("
                SELECT config_key, config_value, config_type 
                FROM " . DB_PREFIX . "meschain_security_config 
                WHERE config_group = '" . $this->db->escape($group) . "'
            ");
            
            $configs = [];
            foreach ($query->rows as $row) {
                $configs[$row['config_key']] = $this->castConfigValue($row['config_value'], $row['config_type']);
            }
            
            return $configs;
        }
    }
    
    /**
     * Set security configuration
     *
     * @param string $group Configuration group
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @param string $type Value type
     * @param string $updated_by Updated by user
     */
    public function setSecurityConfig($group, $key, $value, $type = 'string', $updated_by = 'system') {
        $config_value = $this->prepareConfigValue($value, $type);
        
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_security_config 
            (config_group, config_key, config_value, config_type, created_at, updated_by) 
            VALUES (
                '" . $this->db->escape($group) . "',
                '" . $this->db->escape($key) . "',
                '" . $this->db->escape($config_value) . "',
                '" . $this->db->escape($type) . "',
                NOW(),
                '" . $this->db->escape($updated_by) . "'
            ) 
            ON DUPLICATE KEY UPDATE 
                config_value = '" . $this->db->escape($config_value) . "',
                config_type = '" . $this->db->escape($type) . "',
                updated_at = NOW(),
                updated_by = '" . $this->db->escape($updated_by) . "'
        ");
    }
    
    /**
     * Get security dashboard statistics
     *
     * @param int $hours Time period in hours
     * @return array Statistics
     */
    public function getSecurityStatistics($hours = 24) {
        $stats = [];
        
        // Event statistics
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total_events,
                SUM(CASE WHEN threat_detected = 1 THEN 1 ELSE 0 END) as threats_detected,
                SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) as critical_events,
                SUM(CASE WHEN severity = 'high' THEN 1 ELSE 0 END) as high_events,
                SUM(CASE WHEN severity = 'medium' THEN 1 ELSE 0 END) as medium_events,
                SUM(CASE WHEN severity = 'low' THEN 1 ELSE 0 END) as low_events
            FROM " . DB_PREFIX . "meschain_security_events 
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL " . intval($hours) . " HOUR)
        ");
        $stats['events'] = $query->row;
        
        // Alert statistics
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total_alerts,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_alerts,
                SUM(CASE WHEN status = 'acknowledged' THEN 1 ELSE 0 END) as acknowledged_alerts,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_alerts,
                SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) as critical_alerts
            FROM " . DB_PREFIX . "meschain_security_alerts 
            WHERE triggered_at >= DATE_SUB(NOW(), INTERVAL " . intval($hours) . " HOUR)
        ");
        $stats['alerts'] = $query->row;
        
        // Incident statistics
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total_incidents,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_incidents,
                SUM(CASE WHEN status = 'investigating' THEN 1 ELSE 0 END) as investigating_incidents,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_incidents
            FROM " . DB_PREFIX . "meschain_security_incidents 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL " . intval($hours) . " HOUR)
        ");
        $stats['incidents'] = $query->row;
        
        // Vulnerability statistics
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total_scans,
                SUM(vulnerabilities_found) as total_vulnerabilities,
                SUM(critical_count) as critical_vulnerabilities,
                SUM(high_count) as high_vulnerabilities,
                SUM(medium_count) as medium_vulnerabilities,
                SUM(low_count) as low_vulnerabilities
            FROM " . DB_PREFIX . "meschain_vulnerability_scans 
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL " . intval($hours) . " HOUR)
        ");
        $stats['vulnerabilities'] = $query->row;
        
        return $stats;
    }
    
    /**
     * Clean up old security data
     *
     * @param int $retention_days Data retention period in days
     */
    public function cleanupSecurityData($retention_days = 365) {
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
        
        // Clean old events
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "meschain_security_events 
            WHERE timestamp < '" . $this->db->escape($cutoff_date) . "'
        ");
        
        // Clean old alerts
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "meschain_security_alerts 
            WHERE triggered_at < '" . $this->db->escape($cutoff_date) . "' 
            AND status IN ('resolved', 'false_positive')
        ");
        
        // Clean old metrics
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "meschain_security_metrics 
            WHERE timestamp < '" . $this->db->escape($cutoff_date) . "'
        ");
        
        // Clean old scan results
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "meschain_vulnerability_scans 
            WHERE started_at < '" . $this->db->escape($cutoff_date) . "'
        ");
    }
    
    // Private helper methods
    
    /**
     * Cast configuration value to proper type
     */
    private function castConfigValue($value, $type) {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'int':
                return intval($value);
            case 'float':
                return floatval($value);
            case 'json':
                return json_decode($value, true);
            case 'array':
                return unserialize($value);
            default:
                return $value;
        }
    }
    
    /**
     * Prepare configuration value for storage
     */
    private function prepareConfigValue($value, $type) {
        switch ($type) {
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'json':
                return json_encode($value);
            case 'array':
                return serialize($value);
            default:
                return strval($value);
        }
    }
} 