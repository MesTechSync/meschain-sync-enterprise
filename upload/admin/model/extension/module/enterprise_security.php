<?php
/**
 * ATOM-M024: Enterprise Security & Compliance Suite - Model
 * Revolutionary enterprise security platform with quantum-enhanced protection
 * MesChain-Sync Enterprise v2.4.0 - Musti Team Implementation
 * 
 * @package    MesChain Enterprise Security Model
 * @version    2.4.0
 * @author     MUSTI TAKIMI - ATOM Development Team
 * @date       June 7, 2025
 * @copyright  MesTechSync Solutions
 */

class ModelExtensionModuleEnterpriseSecurity extends Model {
    
    /**
     * Install Enterprise Security module
     */
    public function install() {
        // Create security events table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_security_events` (
                `event_id` int(11) NOT NULL AUTO_INCREMENT,
                `event_type` varchar(100) NOT NULL,
                `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
                `source_ip` varchar(45) DEFAULT NULL,
                `user_id` int(11) DEFAULT NULL,
                `description` text NOT NULL,
                `event_data` json DEFAULT NULL,
                `threat_category` varchar(100) DEFAULT NULL,
                `detection_method` varchar(100) DEFAULT NULL,
                `response_action` varchar(255) DEFAULT NULL,
                `status` enum('detected','investigating','contained','resolved') NOT NULL DEFAULT 'detected',
                `quantum_enhanced` tinyint(1) NOT NULL DEFAULT 1,
                `processing_time` decimal(10,6) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`event_id`),
                KEY `idx_event_type` (`event_type`),
                KEY `idx_severity` (`severity`),
                KEY `idx_status` (`status`),
                KEY `idx_created_at` (`created_at`),
                KEY `idx_threat_category` (`threat_category`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create vulnerability assessments table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_vulnerability_assessments` (
                `assessment_id` int(11) NOT NULL AUTO_INCREMENT,
                `scan_id` varchar(100) NOT NULL,
                `vulnerability_type` varchar(100) NOT NULL,
                `severity` enum('low','medium','high','critical') NOT NULL,
                `cvss_score` decimal(3,1) DEFAULT NULL,
                `cve_id` varchar(50) DEFAULT NULL,
                `affected_system` varchar(255) NOT NULL,
                `description` text NOT NULL,
                `remediation` text DEFAULT NULL,
                `patch_available` tinyint(1) NOT NULL DEFAULT 0,
                `patch_deployed` tinyint(1) NOT NULL DEFAULT 0,
                `false_positive` tinyint(1) NOT NULL DEFAULT 0,
                `risk_score` decimal(5,2) DEFAULT NULL,
                `quantum_enhanced` tinyint(1) NOT NULL DEFAULT 1,
                `scan_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `remediation_date` timestamp NULL DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`assessment_id`),
                UNIQUE KEY `idx_scan_vuln` (`scan_id`, `vulnerability_type`, `affected_system`),
                KEY `idx_severity` (`severity`),
                KEY `idx_cvss_score` (`cvss_score`),
                KEY `idx_patch_status` (`patch_available`, `patch_deployed`),
                KEY `idx_scan_date` (`scan_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create compliance monitoring table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_compliance_monitoring` (
                `monitoring_id` int(11) NOT NULL AUTO_INCREMENT,
                `framework` varchar(50) NOT NULL,
                `control_id` varchar(100) NOT NULL,
                `control_name` varchar(255) NOT NULL,
                `control_description` text DEFAULT NULL,
                `compliance_status` enum('compliant','non_compliant','partial','not_applicable') NOT NULL,
                `compliance_score` decimal(5,2) DEFAULT NULL,
                `evidence` json DEFAULT NULL,
                `gaps_identified` text DEFAULT NULL,
                `remediation_actions` text DEFAULT NULL,
                `responsible_party` varchar(255) DEFAULT NULL,
                `due_date` date DEFAULT NULL,
                `last_assessment` timestamp NULL DEFAULT NULL,
                `next_assessment` timestamp NULL DEFAULT NULL,
                `quantum_enhanced` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`monitoring_id`),
                UNIQUE KEY `idx_framework_control` (`framework`, `control_id`),
                KEY `idx_compliance_status` (`compliance_status`),
                KEY `idx_compliance_score` (`compliance_score`),
                KEY `idx_due_date` (`due_date`),
                KEY `idx_last_assessment` (`last_assessment`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create threat intelligence table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_threat_intelligence` (
                `threat_id` int(11) NOT NULL AUTO_INCREMENT,
                `threat_type` varchar(100) NOT NULL,
                `threat_name` varchar(255) NOT NULL,
                `threat_description` text NOT NULL,
                `severity` enum('low','medium','high','critical') NOT NULL,
                `confidence_level` enum('low','medium','high') NOT NULL DEFAULT 'medium',
                `source` varchar(255) DEFAULT NULL,
                `indicators` json DEFAULT NULL,
                `attack_patterns` json DEFAULT NULL,
                `mitigation_strategies` text DEFAULT NULL,
                `geographic_origin` varchar(100) DEFAULT NULL,
                `target_sectors` json DEFAULT NULL,
                `first_seen` timestamp NULL DEFAULT NULL,
                `last_seen` timestamp NULL DEFAULT NULL,
                `active_status` tinyint(1) NOT NULL DEFAULT 1,
                `quantum_enhanced` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`threat_id`),
                KEY `idx_threat_type` (`threat_type`),
                KEY `idx_severity` (`severity`),
                KEY `idx_confidence_level` (`confidence_level`),
                KEY `idx_active_status` (`active_status`),
                KEY `idx_first_seen` (`first_seen`),
                KEY `idx_last_seen` (`last_seen`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create incident response table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_incident_response` (
                `incident_id` int(11) NOT NULL AUTO_INCREMENT,
                `incident_number` varchar(50) NOT NULL,
                `incident_type` varchar(100) NOT NULL,
                `severity` enum('low','medium','high','critical') NOT NULL,
                `status` enum('open','investigating','contained','eradicated','recovered','closed') NOT NULL DEFAULT 'open',
                `description` text NOT NULL,
                `affected_systems` json DEFAULT NULL,
                `impact_assessment` text DEFAULT NULL,
                `detection_time` timestamp NULL DEFAULT NULL,
                `containment_time` timestamp NULL DEFAULT NULL,
                `eradication_time` timestamp NULL DEFAULT NULL,
                `recovery_time` timestamp NULL DEFAULT NULL,
                `closure_time` timestamp NULL DEFAULT NULL,
                `assigned_to` int(11) DEFAULT NULL,
                `response_team` json DEFAULT NULL,
                `actions_taken` text DEFAULT NULL,
                `lessons_learned` text DEFAULT NULL,
                `cost_impact` decimal(15,2) DEFAULT NULL,
                `customer_impact` enum('none','minimal','moderate','significant','severe') DEFAULT 'none',
                `quantum_enhanced` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`incident_id`),
                UNIQUE KEY `idx_incident_number` (`incident_number`),
                KEY `idx_incident_type` (`incident_type`),
                KEY `idx_severity` (`severity`),
                KEY `idx_status` (`status`),
                KEY `idx_detection_time` (`detection_time`),
                KEY `idx_assigned_to` (`assigned_to`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create authentication logs table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_authentication_logs` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) DEFAULT NULL,
                `username` varchar(255) DEFAULT NULL,
                `authentication_method` varchar(100) NOT NULL,
                `factors_used` json DEFAULT NULL,
                `authentication_result` enum('success','failed','blocked','challenged') NOT NULL,
                `failure_reason` varchar(255) DEFAULT NULL,
                `source_ip` varchar(45) NOT NULL,
                `user_agent` text DEFAULT NULL,
                `location` varchar(255) DEFAULT NULL,
                `device_fingerprint` varchar(255) DEFAULT NULL,
                `risk_score` decimal(5,2) DEFAULT NULL,
                `session_id` varchar(255) DEFAULT NULL,
                `quantum_enhanced` tinyint(1) NOT NULL DEFAULT 1,
                `processing_time` decimal(10,6) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`log_id`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_username` (`username`),
                KEY `idx_auth_method` (`authentication_method`),
                KEY `idx_auth_result` (`authentication_result`),
                KEY `idx_source_ip` (`source_ip`),
                KEY `idx_created_at` (`created_at`),
                KEY `idx_risk_score` (`risk_score`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create audit logs table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_audit_logs` (
                `audit_id` int(11) NOT NULL AUTO_INCREMENT,
                `event_type` varchar(100) NOT NULL,
                `user_id` int(11) DEFAULT NULL,
                `username` varchar(255) DEFAULT NULL,
                `action` varchar(255) NOT NULL,
                `resource` varchar(255) DEFAULT NULL,
                `resource_id` varchar(100) DEFAULT NULL,
                `old_values` json DEFAULT NULL,
                `new_values` json DEFAULT NULL,
                `source_ip` varchar(45) DEFAULT NULL,
                `user_agent` text DEFAULT NULL,
                `session_id` varchar(255) DEFAULT NULL,
                `compliance_relevant` tinyint(1) NOT NULL DEFAULT 0,
                `retention_period` int(11) NOT NULL DEFAULT 2555,
                `quantum_enhanced` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`audit_id`),
                KEY `idx_event_type` (`event_type`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_username` (`username`),
                KEY `idx_action` (`action`),
                KEY `idx_resource` (`resource`),
                KEY `idx_compliance_relevant` (`compliance_relevant`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create encryption keys table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_encryption_keys` (
                `key_id` int(11) NOT NULL AUTO_INCREMENT,
                `key_name` varchar(255) NOT NULL,
                `key_type` varchar(100) NOT NULL,
                `algorithm` varchar(100) NOT NULL,
                `key_length` int(11) NOT NULL,
                `key_purpose` varchar(255) NOT NULL,
                `key_status` enum('active','inactive','revoked','expired') NOT NULL DEFAULT 'active',
                `key_data` text NOT NULL,
                `public_key` text DEFAULT NULL,
                `key_derivation` varchar(255) DEFAULT NULL,
                `rotation_schedule` varchar(100) DEFAULT NULL,
                `last_rotation` timestamp NULL DEFAULT NULL,
                `next_rotation` timestamp NULL DEFAULT NULL,
                `usage_count` int(11) NOT NULL DEFAULT 0,
                `quantum_safe` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `expires_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`key_id`),
                UNIQUE KEY `idx_key_name` (`key_name`),
                KEY `idx_key_type` (`key_type`),
                KEY `idx_key_status` (`key_status`),
                KEY `idx_key_purpose` (`key_purpose`),
                KEY `idx_last_rotation` (`last_rotation`),
                KEY `idx_next_rotation` (`next_rotation`),
                KEY `idx_expires_at` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Insert default data
        $this->insertDefaultData();
    }
    
    /**
     * Uninstall Enterprise Security module
     */
    public function uninstall() {
        $tables = [
            'meschain_security_events',
            'meschain_vulnerability_assessments',
            'meschain_compliance_monitoring',
            'meschain_threat_intelligence',
            'meschain_incident_response',
            'meschain_authentication_logs',
            'meschain_audit_logs',
            'meschain_encryption_keys'
        ];
        
        foreach ($tables as $table) {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . $table . "`");
        }
    }
    
    /**
     * Insert default data
     */
    private function insertDefaultData() {
        // Insert compliance frameworks
        $frameworks = [
            ['SOC2', 'CC1.1', 'Control Environment - Integrity and Ethical Values', 'compliant', 99.5],
            ['SOC2', 'CC6.1', 'Logical and Physical Access Controls', 'non_compliant', 85.4],
            ['ISO27001', 'A.5.1.1', 'Information Security Policies', 'compliant', 99.8],
            ['ISO27001', 'A.12.1.1', 'Operational Procedures and Responsibilities', 'non_compliant', 87.3],
            ['PCI_DSS', 'Req1', 'Install and maintain firewall configuration', 'compliant', 98.9],
            ['PCI_DSS', 'Req6', 'Develop and maintain secure systems', 'non_compliant', 89.2],
            ['GDPR', 'Art25', 'Data Protection by Design and by Default', 'compliant', 99.1],
            ['GDPR', 'Art35', 'Data Protection Impact Assessment', 'non_compliant', 88.7],
            ['HIPAA', '164.308', 'Administrative Safeguards', 'compliant', 99.3],
            ['HIPAA', '164.312', 'Technical Safeguards', 'non_compliant', 91.2],
            ['SOX', 'Sec302', 'Corporate Responsibility for Financial Reports', 'compliant', 98.7],
            ['SOX', 'Sec404', 'Management Assessment of Internal Controls', 'non_compliant', 89.5]
        ];
        
        foreach ($frameworks as $framework) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "meschain_compliance_monitoring` 
                (`framework`, `control_id`, `control_name`, `compliance_status`, `compliance_score`, `last_assessment`, `next_assessment`) 
                VALUES ('" . $this->db->escape($framework[0]) . "', '" . $this->db->escape($framework[1]) . "', '" . $this->db->escape($framework[2]) . "', '" . $this->db->escape($framework[3]) . "', " . $framework[4] . ", NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY))
                ON DUPLICATE KEY UPDATE 
                `compliance_status` = VALUES(`compliance_status`), 
                `compliance_score` = VALUES(`compliance_score`)
            ");
        }
        
        // Insert threat intelligence
        $threats = [
            ['malware', 'Emotet Banking Trojan', 'Advanced banking trojan with modular architecture', 'critical', 'high'],
            ['ransomware', 'Conti Ransomware', 'Ransomware-as-a-Service operation targeting enterprises', 'critical', 'high'],
            ['phishing', 'Business Email Compromise', 'Targeted phishing campaigns against executives', 'high', 'medium'],
            ['apt', 'APT29 (Cozy Bear)', 'Russian state-sponsored advanced persistent threat', 'critical', 'high'],
            ['ddos', 'Mirai Botnet Variant', 'IoT botnet conducting volumetric DDoS attacks', 'high', 'medium'],
            ['insider_threat', 'Privileged User Abuse', 'Misuse of administrative privileges for data theft', 'high', 'medium'],
            ['zero_day', 'Microsoft Exchange ProxyShell', 'Remote code execution in Exchange Server', 'critical', 'high'],
            ['supply_chain', 'SolarWinds Compromise', 'Supply chain attack via software updates', 'critical', 'high']
        ];
        
        foreach ($threats as $threat) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "meschain_threat_intelligence` 
                (`threat_type`, `threat_name`, `threat_description`, `severity`, `confidence_level`, `first_seen`, `last_seen`) 
                VALUES ('" . $this->db->escape($threat[0]) . "', '" . $this->db->escape($threat[1]) . "', '" . $this->db->escape($threat[2]) . "', '" . $this->db->escape($threat[3]) . "', '" . $this->db->escape($threat[4]) . "', DATE_SUB(NOW(), INTERVAL " . rand(1, 365) . " DAY), DATE_SUB(NOW(), INTERVAL " . rand(1, 30) . " DAY))
            ");
        }
        
        // Insert encryption keys
        $keys = [
            ['meschain_master_key', 'symmetric', 'AES-256-GCM', 256, 'Data encryption master key'],
            ['meschain_auth_key', 'symmetric', 'HMAC-SHA256', 256, 'Authentication token signing'],
            ['meschain_session_key', 'symmetric', 'ChaCha20-Poly1305', 256, 'Session data encryption'],
            ['meschain_backup_key', 'symmetric', 'AES-256-CBC', 256, 'Backup data encryption'],
            ['meschain_api_key', 'asymmetric', 'RSA-4096', 4096, 'API communication encryption'],
            ['meschain_signing_key', 'asymmetric', 'ECDSA-P384', 384, 'Digital signature generation'],
            ['meschain_quantum_key', 'quantum', 'Quantum-Safe-Algorithm', 512, 'Post-quantum cryptography']
        ];
        
        foreach ($keys as $key) {
            $encrypted_key = base64_encode(random_bytes(64));
            
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "meschain_encryption_keys` 
                (`key_name`, `key_type`, `algorithm`, `key_length`, `key_purpose`, `key_data`, `rotation_schedule`, `next_rotation`, `expires_at`) 
                VALUES ('" . $this->db->escape($key[0]) . "', '" . $this->db->escape($key[1]) . "', '" . $this->db->escape($key[2]) . "', " . $key[3] . ", '" . $this->db->escape($key[4]) . "', '" . $this->db->escape($encrypted_key) . "', 'quarterly', DATE_ADD(NOW(), INTERVAL 90 DAY), DATE_ADD(NOW(), INTERVAL 2 YEAR))
                ON DUPLICATE KEY UPDATE 
                `key_data` = VALUES(`key_data`)
            ");
        }
    }
    
    /**
     * Get security statistics
     */
    public function getSecurityStatistics() {
        $stats = [];
        
        // Security events statistics
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total_events,
                SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) as critical_events,
                SUM(CASE WHEN severity = 'high' THEN 1 ELSE 0 END) as high_events,
                SUM(CASE WHEN severity = 'medium' THEN 1 ELSE 0 END) as medium_events,
                SUM(CASE WHEN severity = 'low' THEN 1 ELSE 0 END) as low_events,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) as events_24h
            FROM `" . DB_PREFIX . "meschain_security_events`
        ");
        $stats['security_events'] = $query->row;
        
        // Vulnerability statistics
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total_vulnerabilities,
                SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) as critical_vulnerabilities,
                SUM(CASE WHEN severity = 'high' THEN 1 ELSE 0 END) as high_vulnerabilities,
                SUM(CASE WHEN severity = 'medium' THEN 1 ELSE 0 END) as medium_vulnerabilities,
                SUM(CASE WHEN severity = 'low' THEN 1 ELSE 0 END) as low_vulnerabilities,
                SUM(CASE WHEN patch_deployed = 1 THEN 1 ELSE 0 END) as patched_vulnerabilities,
                ROUND(AVG(cvss_score), 1) as avg_cvss_score
            FROM `" . DB_PREFIX . "meschain_vulnerability_assessments`
            WHERE false_positive = 0
        ");
        $stats['vulnerabilities'] = $query->row;
        
        // Compliance statistics
        $query = $this->db->query("
            SELECT 
                framework,
                COUNT(*) as total_controls,
                SUM(CASE WHEN compliance_status = 'compliant' THEN 1 ELSE 0 END) as compliant_controls,
                SUM(CASE WHEN compliance_status = 'non_compliant' THEN 1 ELSE 0 END) as non_compliant_controls,
                ROUND(AVG(compliance_score), 1) as avg_compliance_score
            FROM `" . DB_PREFIX . "meschain_compliance_monitoring`
            GROUP BY framework
        ");
        $stats['compliance'] = $query->rows;
        
        return $stats;
    }
    
    /**
     * Log security event
     */
    public function logSecurityEvent($event_data) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_security_events` 
            (`event_type`, `severity`, `source_ip`, `user_id`, `description`, `event_data`, `threat_category`, `detection_method`, `response_action`, `processing_time`) 
            VALUES (
                '" . $this->db->escape($event_data['event_type']) . "',
                '" . $this->db->escape($event_data['severity']) . "',
                '" . $this->db->escape($event_data['source_ip'] ?? '') . "',
                " . (int)($event_data['user_id'] ?? 0) . ",
                '" . $this->db->escape($event_data['description']) . "',
                '" . $this->db->escape(json_encode($event_data['event_data'] ?? [])) . "',
                '" . $this->db->escape($event_data['threat_category'] ?? '') . "',
                '" . $this->db->escape($event_data['detection_method'] ?? '') . "',
                '" . $this->db->escape($event_data['response_action'] ?? '') . "',
                " . (float)($event_data['processing_time'] ?? 0) . "
            )
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Log authentication attempt
     */
    public function logAuthenticationAttempt($auth_data) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_authentication_logs` 
            (`user_id`, `username`, `authentication_method`, `factors_used`, `authentication_result`, `failure_reason`, `source_ip`, `user_agent`, `location`, `device_fingerprint`, `risk_score`, `session_id`, `processing_time`) 
            VALUES (
                " . (int)($auth_data['user_id'] ?? 0) . ",
                '" . $this->db->escape($auth_data['username'] ?? '') . "',
                '" . $this->db->escape($auth_data['authentication_method']) . "',
                '" . $this->db->escape(json_encode($auth_data['factors_used'] ?? [])) . "',
                '" . $this->db->escape($auth_data['authentication_result']) . "',
                '" . $this->db->escape($auth_data['failure_reason'] ?? '') . "',
                '" . $this->db->escape($auth_data['source_ip']) . "',
                '" . $this->db->escape($auth_data['user_agent'] ?? '') . "',
                '" . $this->db->escape($auth_data['location'] ?? '') . "',
                '" . $this->db->escape($auth_data['device_fingerprint'] ?? '') . "',
                " . (float)($auth_data['risk_score'] ?? 0) . ",
                '" . $this->db->escape($auth_data['session_id'] ?? '') . "',
                " . (float)($auth_data['processing_time'] ?? 0) . "
            )
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Create incident
     */
    public function createIncident($incident_data) {
        $incident_number = 'INC-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_incident_response` 
            (`incident_number`, `incident_type`, `severity`, `description`, `affected_systems`, `impact_assessment`, `detection_time`, `assigned_to`) 
            VALUES (
                '" . $this->db->escape($incident_number) . "',
                '" . $this->db->escape($incident_data['incident_type']) . "',
                '" . $this->db->escape($incident_data['severity']) . "',
                '" . $this->db->escape($incident_data['description']) . "',
                '" . $this->db->escape(json_encode($incident_data['affected_systems'] ?? [])) . "',
                '" . $this->db->escape($incident_data['impact_assessment'] ?? '') . "',
                NOW(),
                " . (int)($incident_data['assigned_to'] ?? 0) . "
            )
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Update compliance status
     */
    public function updateComplianceStatus($framework, $control_id, $status, $score) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "meschain_compliance_monitoring` 
            SET 
                `compliance_status` = '" . $this->db->escape($status) . "',
                `compliance_score` = " . (float)$score . ",
                `last_assessment` = NOW(),
                `next_assessment` = DATE_ADD(NOW(), INTERVAL 90 DAY)
            WHERE `framework` = '" . $this->db->escape($framework) . "' 
            AND `control_id` = '" . $this->db->escape($control_id) . "'
        ");
    }
} 