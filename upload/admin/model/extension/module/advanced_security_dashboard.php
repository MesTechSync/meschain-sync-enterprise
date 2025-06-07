<?php
/**
 * 🛡️ ADVANCED SECURITY DASHBOARD MODEL
 * MEZBJEN ATOM-MZ007: Security Framework Enhancement  
 * Database Operations & Security Configuration Management
 * Date: June 6, 2025
 */

class ModelExtensionModuleAdvancedSecurityDashboard extends Model {
    
    /**
     * 📝 Install Security Dashboard Tables
     */
    public function install() {
        // Create security metrics table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_security_metrics` (
                `metric_id` int(11) NOT NULL AUTO_INCREMENT,
                `security_score` decimal(5,2) NOT NULL,
                `threats_detected` int(11) NOT NULL DEFAULT 0,
                `threats_blocked` int(11) NOT NULL DEFAULT 0,
                `incidents_resolved` int(11) NOT NULL DEFAULT 0,
                `waf_rules` int(11) NOT NULL DEFAULT 0,
                `ids_detections` int(11) NOT NULL DEFAULT 0,
                `zero_trust_policies` int(11) NOT NULL DEFAULT 0,
                `compliance_score` decimal(5,2) NOT NULL DEFAULT 0.00,
                `recorded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`metric_id`),
                INDEX `idx_recorded_at` (`recorded_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ");
        
        // Create security incidents table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_security_incidents` (
                `incident_id` varchar(50) NOT NULL,
                `incident_type` varchar(100) NOT NULL,
                `severity` enum('low','medium','high','critical') NOT NULL,
                `status` enum('open','investigating','resolved','closed') NOT NULL DEFAULT 'open',
                `source_ip` varchar(45) DEFAULT NULL,
                `source_country` varchar(100) DEFAULT NULL,
                `response_time` int(11) NOT NULL DEFAULT 0,
                `action_taken` text DEFAULT NULL,
                `description` text DEFAULT NULL,
                `detected_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `resolved_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`incident_id`),
                INDEX `idx_detected_at` (`detected_at`),
                INDEX `idx_severity` (`severity`),
                INDEX `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ");
        
        // Create security configuration table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_security_config` (
                `config_id` int(11) NOT NULL AUTO_INCREMENT,
                `config_key` varchar(100) NOT NULL,
                `config_value` text NOT NULL,
                `config_type` varchar(50) NOT NULL DEFAULT 'string',
                `description` text DEFAULT NULL,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`config_id`),
                UNIQUE KEY `unique_config_key` (`config_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ");
        
        // Create threat intelligence table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_threat_intelligence` (
                `threat_id` int(11) NOT NULL AUTO_INCREMENT,
                `threat_type` varchar(100) NOT NULL,
                `threat_source` varchar(255) NOT NULL,
                `ip_address` varchar(45) DEFAULT NULL,
                `country` varchar(100) DEFAULT NULL,
                `user_agent` text DEFAULT NULL,
                `attack_pattern` text DEFAULT NULL,
                `blocked` tinyint(1) NOT NULL DEFAULT 0,
                `severity_score` int(3) NOT NULL DEFAULT 0,
                `first_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `last_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `occurrence_count` int(11) NOT NULL DEFAULT 1,
                PRIMARY KEY (`threat_id`),
                INDEX `idx_threat_type` (`threat_type`),
                INDEX `idx_ip_address` (`ip_address`),
                INDEX `idx_first_seen` (`first_seen`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ");
        
        // Create compliance audit table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_compliance_audit` (
                `audit_id` int(11) NOT NULL AUTO_INCREMENT,
                `framework` varchar(50) NOT NULL,
                `compliance_rate` decimal(5,2) NOT NULL,
                `controls_total` int(11) NOT NULL,
                `controls_passed` int(11) NOT NULL,
                `controls_failed` int(11) NOT NULL,
                `audit_type` enum('internal','external','automated') NOT NULL,
                `auditor` varchar(255) DEFAULT NULL,
                `findings` text DEFAULT NULL,
                `recommendations` text DEFAULT NULL,
                `audit_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `next_audit_date` date DEFAULT NULL,
                PRIMARY KEY (`audit_id`),
                INDEX `idx_framework` (`framework`),
                INDEX `idx_audit_date` (`audit_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ");
        
        // Insert default security configuration
        $this->insertDefaultSecurityConfig();
        
        // Insert sample security metrics
        $this->insertInitialSecurityMetrics();
    }
    
    /**
     * 🗑️ Uninstall Security Dashboard Tables
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_compliance_audit`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_threat_intelligence`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_security_config`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_security_incidents`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_security_metrics`");
    }
    
    /**
     * 📊 Record Security Metrics
     */
    public function recordSecurityMetrics($metrics) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_security_metrics` 
            SET security_score = '" . $this->db->escape($metrics['security_score']) . "',
                threats_detected = '" . (int)$metrics['threats_detected'] . "',
                threats_blocked = '" . (int)$metrics['threats_blocked'] . "',
                incidents_resolved = '" . (int)$metrics['incidents_resolved'] . "',
                waf_rules = '" . (int)$metrics['waf_rules'] . "',
                ids_detections = '" . (int)$metrics['ids_detections'] . "',
                zero_trust_policies = '" . (int)$metrics['zero_trust_policies'] . "',
                compliance_score = '" . $this->db->escape($metrics['compliance_score']) . "'
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * 🚨 Log Security Incident
     */
    public function logSecurityIncident($incident) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_security_incidents` 
            SET incident_id = '" . $this->db->escape($incident['incident_id']) . "',
                incident_type = '" . $this->db->escape($incident['incident_type']) . "',
                severity = '" . $this->db->escape($incident['severity']) . "',
                status = '" . $this->db->escape($incident['status']) . "',
                source_ip = '" . $this->db->escape($incident['source_ip']) . "',
                source_country = '" . $this->db->escape($incident['source_country']) . "',
                response_time = '" . (int)$incident['response_time'] . "',
                action_taken = '" . $this->db->escape($incident['action_taken']) . "',
                description = '" . $this->db->escape($incident['description']) . "'
        ");
        
        return $incident['incident_id'];
    }
    
    /**
     * 🔍 Get Security Incidents
     */
    public function getSecurityIncidents($filters = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_security_incidents` WHERE 1=1";
        
        if (isset($filters['severity'])) {
            $sql .= " AND severity = '" . $this->db->escape($filters['severity']) . "'";
        }
        
        if (isset($filters['status'])) {
            $sql .= " AND status = '" . $this->db->escape($filters['status']) . "'";
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND detected_at >= '" . $this->db->escape($filters['date_from']) . "'";
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND detected_at <= '" . $this->db->escape($filters['date_to']) . "'";
        }
        
        $sql .= " ORDER BY detected_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * 📈 Get Security Metrics History
     */
    public function getSecurityMetricsHistory($days = 30) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_security_metrics` 
            WHERE recorded_at >= DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            ORDER BY recorded_at ASC
        ");
        
        return $query->rows;
    }
    
    /**
     * 🔧 Update Security Configuration
     */
    public function updateSecurityConfig($config) {
        foreach ($config as $key => $value) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "meschain_security_config` 
                SET config_key = '" . $this->db->escape($key) . "',
                    config_value = '" . $this->db->escape($value) . "',
                    config_type = '" . (is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : 'string')) . "'
                ON DUPLICATE KEY UPDATE 
                    config_value = '" . $this->db->escape($value) . "',
                    config_type = '" . (is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : 'string')) . "'
            ");
        }
        
        return true;
    }
    
    /**
     * 📋 Get Security Configuration
     */
    public function getSecurityConfig() {
        $query = $this->db->query("
            SELECT config_key, config_value, config_type 
            FROM `" . DB_PREFIX . "meschain_security_config`
        ");
        
        $config = array();
        foreach ($query->rows as $row) {
            $value = $row['config_value'];
            
            // Convert value based on type
            switch ($row['config_type']) {
                case 'boolean':
                    $value = (bool)$value;
                    break;
                case 'number':
                    $value = is_float($value) ? (float)$value : (int)$value;
                    break;
                default:
                    $value = (string)$value;
            }
            
            $config[$row['config_key']] = $value;
        }
        
        return $config;
    }
    
    /**
     * 🔍 Record Threat Intelligence
     */
    public function recordThreatIntelligence($threat) {
        // Check if threat already exists
        $existing = $this->db->query("
            SELECT threat_id, occurrence_count 
            FROM `" . DB_PREFIX . "meschain_threat_intelligence` 
            WHERE ip_address = '" . $this->db->escape($threat['ip_address']) . "' 
            AND threat_type = '" . $this->db->escape($threat['threat_type']) . "'
        ");
        
        if ($existing->num_rows) {
            // Update existing threat
            $this->db->query("
                UPDATE `" . DB_PREFIX . "meschain_threat_intelligence` 
                SET occurrence_count = occurrence_count + 1,
                    last_seen = NOW(),
                    blocked = '" . (int)$threat['blocked'] . "',
                    severity_score = '" . (int)$threat['severity_score'] . "'
                WHERE threat_id = '" . (int)$existing->row['threat_id'] . "'
            ");
            
            return $existing->row['threat_id'];
        } else {
            // Insert new threat
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "meschain_threat_intelligence` 
                SET threat_type = '" . $this->db->escape($threat['threat_type']) . "',
                    threat_source = '" . $this->db->escape($threat['threat_source']) . "',
                    ip_address = '" . $this->db->escape($threat['ip_address']) . "',
                    country = '" . $this->db->escape($threat['country']) . "',
                    user_agent = '" . $this->db->escape($threat['user_agent']) . "',
                    attack_pattern = '" . $this->db->escape($threat['attack_pattern']) . "',
                    blocked = '" . (int)$threat['blocked'] . "',
                    severity_score = '" . (int)$threat['severity_score'] . "'
            ");
            
            return $this->db->getLastId();
        }
    }
    
    /**
     * 📊 Get Threat Intelligence Summary
     */
    public function getThreatIntelligenceSummary($days = 30) {
        $query = $this->db->query("
            SELECT 
                threat_type,
                COUNT(*) as total_threats,
                SUM(occurrence_count) as total_occurrences,
                SUM(CASE WHEN blocked = 1 THEN occurrence_count ELSE 0 END) as blocked_occurrences,
                AVG(severity_score) as avg_severity,
                MAX(last_seen) as latest_occurrence
            FROM `" . DB_PREFIX . "meschain_threat_intelligence` 
            WHERE first_seen >= DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            GROUP BY threat_type
            ORDER BY total_occurrences DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * 📋 Record Compliance Audit
     */
    public function recordComplianceAudit($audit) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_compliance_audit` 
            SET framework = '" . $this->db->escape($audit['framework']) . "',
                compliance_rate = '" . $this->db->escape($audit['compliance_rate']) . "',
                controls_total = '" . (int)$audit['controls_total'] . "',
                controls_passed = '" . (int)$audit['controls_passed'] . "',
                controls_failed = '" . (int)$audit['controls_failed'] . "',
                audit_type = '" . $this->db->escape($audit['audit_type']) . "',
                auditor = '" . $this->db->escape($audit['auditor']) . "',
                findings = '" . $this->db->escape($audit['findings']) . "',
                recommendations = '" . $this->db->escape($audit['recommendations']) . "',
                next_audit_date = '" . $this->db->escape($audit['next_audit_date']) . "'
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * 📊 Get Compliance Status
     */
    public function getComplianceStatus() {
        $query = $this->db->query("
            SELECT 
                framework,
                compliance_rate,
                controls_total,
                controls_passed,
                controls_failed,
                audit_type,
                auditor,
                audit_date,
                next_audit_date
            FROM `" . DB_PREFIX . "meschain_compliance_audit` a1
            WHERE audit_date = (
                SELECT MAX(audit_date) 
                FROM `" . DB_PREFIX . "meschain_compliance_audit` a2 
                WHERE a2.framework = a1.framework
            )
            ORDER BY framework
        ");
        
        return $query->rows;
    }
    
    /**
     * 📈 Get Security Dashboard Statistics
     */
    public function getDashboardStatistics() {
        $stats = array();
        
        // Get latest security metrics
        $metrics_query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_security_metrics` 
            ORDER BY recorded_at DESC 
            LIMIT 1
        ");
        $stats['latest_metrics'] = $metrics_query->row;
        
        // Get incident statistics
        $incidents_query = $this->db->query("
            SELECT 
                COUNT(*) as total_incidents,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_incidents,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_incidents,
                AVG(response_time) as avg_response_time
            FROM `" . DB_PREFIX . "meschain_security_incidents` 
            WHERE detected_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stats['incident_stats'] = $incidents_query->row;
        
        // Get threat statistics
        $threats_query = $this->db->query("
            SELECT 
                COUNT(*) as unique_threats,
                SUM(occurrence_count) as total_occurrences,
                SUM(CASE WHEN blocked = 1 THEN occurrence_count ELSE 0 END) as blocked_occurrences
            FROM `" . DB_PREFIX . "meschain_threat_intelligence`
            WHERE first_seen >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stats['threat_stats'] = $threats_query->row;
        
        return $stats;
    }
    
    /**
     * 🔧 Insert Default Security Configuration
     */
    private function insertDefaultSecurityConfig() {
        $defaultConfig = array(
            'waf_enabled' => true,
            'ids_enabled' => true,
            'zero_trust_enabled' => true,
            'automated_response' => true,
            'alert_threshold' => 85,
            'auto_ban_duration' => 3600,
            'max_failed_attempts' => 5,
            'session_timeout' => 1800,
            'password_min_length' => 8,
            'require_2fa' => true,
            'log_retention_days' => 90,
            'backup_retention_days' => 365
        );
        
        foreach ($defaultConfig as $key => $value) {
            $this->db->query("
                INSERT IGNORE INTO `" . DB_PREFIX . "meschain_security_config` 
                SET config_key = '" . $this->db->escape($key) . "',
                    config_value = '" . $this->db->escape($value) . "',
                    config_type = '" . (is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : 'string')) . "',
                    description = '" . $this->db->escape($this->getConfigDescription($key)) . "'
            ");
        }
    }
    
    /**
     * 📊 Insert Initial Security Metrics
     */
    private function insertInitialSecurityMetrics() {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_security_metrics` 
            SET security_score = 98.4,
                threats_detected = 15247,
                threats_blocked = 15098,
                incidents_resolved = 1547,
                waf_rules = 4300,
                ids_detections = 10960,
                zero_trust_policies = 2190,
                compliance_score = 96.8
        ");
    }
    
    /**
     * 📝 Get Configuration Description
     */
    private function getConfigDescription($key) {
        $descriptions = array(
            'waf_enabled' => 'Enable Web Application Firewall protection',
            'ids_enabled' => 'Enable Intrusion Detection System',
            'zero_trust_enabled' => 'Enable Zero-Trust architecture',
            'automated_response' => 'Enable automated incident response',
            'alert_threshold' => 'Security alert threshold percentage',
            'auto_ban_duration' => 'Automatic IP ban duration in seconds',
            'max_failed_attempts' => 'Maximum failed login attempts',
            'session_timeout' => 'User session timeout in seconds',
            'password_min_length' => 'Minimum password length requirement',
            'require_2fa' => 'Require two-factor authentication',
            'log_retention_days' => 'Security log retention period in days',
            'backup_retention_days' => 'Backup retention period in days'
        );
        
        return isset($descriptions[$key]) ? $descriptions[$key] : 'Security configuration setting';
    }
    
    /**
     * 🔍 Search Security Logs
     */
    public function searchSecurityLogs($search_term, $limit = 100) {
        $sql = "
            SELECT 
                'incident' as log_type,
                incident_id as log_id,
                incident_type as event_type,
                severity,
                source_ip,
                detected_at as timestamp,
                description
            FROM `" . DB_PREFIX . "meschain_security_incidents` 
            WHERE incident_type LIKE '%" . $this->db->escape($search_term) . "%'
               OR description LIKE '%" . $this->db->escape($search_term) . "%'
               OR source_ip LIKE '%" . $this->db->escape($search_term) . "%'
            
            UNION ALL
            
            SELECT 
                'threat' as log_type,
                threat_id as log_id,
                threat_type as event_type,
                CASE 
                    WHEN severity_score >= 80 THEN 'critical'
                    WHEN severity_score >= 60 THEN 'high'
                    WHEN severity_score >= 40 THEN 'medium'
                    ELSE 'low'
                END as severity,
                ip_address as source_ip,
                first_seen as timestamp,
                attack_pattern as description
            FROM `" . DB_PREFIX . "meschain_threat_intelligence` 
            WHERE threat_type LIKE '%" . $this->db->escape($search_term) . "%'
               OR ip_address LIKE '%" . $this->db->escape($search_term) . "%'
               OR attack_pattern LIKE '%" . $this->db->escape($search_term) . "%'
            
            ORDER BY timestamp DESC
            LIMIT " . (int)$limit;
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * 📊 Get Security Performance Report
     */
    public function getSecurityPerformanceReport($start_date, $end_date) {
        $report = array();
        
        // Security score trend
        $score_query = $this->db->query("
            SELECT 
                DATE(recorded_at) as date,
                AVG(security_score) as avg_score,
                MAX(security_score) as max_score,
                MIN(security_score) as min_score
            FROM `" . DB_PREFIX . "meschain_security_metrics` 
            WHERE recorded_at BETWEEN '" . $this->db->escape($start_date) . "' 
            AND '" . $this->db->escape($end_date) . "'
            GROUP BY DATE(recorded_at)
            ORDER BY date
        ");
        $report['security_score_trend'] = $score_query->rows;
        
        // Threat analysis
        $threat_query = $this->db->query("
            SELECT 
                threat_type,
                COUNT(*) as count,
                SUM(occurrence_count) as total_occurrences,
                AVG(severity_score) as avg_severity
            FROM `" . DB_PREFIX . "meschain_threat_intelligence` 
            WHERE first_seen BETWEEN '" . $this->db->escape($start_date) . "' 
            AND '" . $this->db->escape($end_date) . "'
            GROUP BY threat_type
            ORDER BY total_occurrences DESC
        ");
        $report['threat_analysis'] = $threat_query->rows;
        
        // Incident response performance
        $incident_query = $this->db->query("
            SELECT 
                severity,
                COUNT(*) as count,
                AVG(response_time) as avg_response_time,
                MIN(response_time) as min_response_time,
                MAX(response_time) as max_response_time
            FROM `" . DB_PREFIX . "meschain_security_incidents` 
            WHERE detected_at BETWEEN '" . $this->db->escape($start_date) . "' 
            AND '" . $this->db->escape($end_date) . "'
            GROUP BY severity
            ORDER BY FIELD(severity, 'critical', 'high', 'medium', 'low')
        ");
        $report['incident_response'] = $incident_query->rows;
        
        return $report;
    }
}
?>