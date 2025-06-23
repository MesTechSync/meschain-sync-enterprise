<?php
/**
 * MesChain-Sync DevOps Automation Dashboard Model
 * 
 * @package    MesChain-Sync
 * @author     MesChain Development Team
 * @copyright  2024 MesChain
 * @license    Commercial License
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

class ModelExtensionModuleDevopsAutomationDashboard extends Model {
    
    /**
     * Database tablolarını oluşturur
     */
    public function install() {
        // CI/CD Pipelines tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_devops_pipelines` (
                `pipeline_id` varchar(50) NOT NULL,
                `name` varchar(255) NOT NULL,
                `repository_url` varchar(500) NOT NULL,
                `branch` varchar(100) NOT NULL DEFAULT 'main',
                `trigger_type` enum('push','pull_request','schedule','manual') NOT NULL DEFAULT 'push',
                `status` enum('pending','running','success','failed','cancelled') NOT NULL DEFAULT 'pending',
                `config` text,
                `result` text,
                `duration` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `started_at` datetime DEFAULT NULL,
                `completed_at` datetime DEFAULT NULL,
                PRIMARY KEY (`pipeline_id`),
                KEY `idx_status` (`status`),
                KEY `idx_created_at` (`created_at`),
                KEY `idx_repository` (`repository_url`(255))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // GitOps Workflows tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_devops_gitops_workflows` (
                `workflow_id` varchar(50) NOT NULL,
                `repository` varchar(255) NOT NULL,
                `branch` varchar(100) NOT NULL,
                `status` enum('pending','running','success','failed','no_changes') NOT NULL DEFAULT 'pending',
                `config` text,
                `result` text,
                `changes_detected` text,
                `deployment_strategy` varchar(50) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `completed_at` datetime DEFAULT NULL,
                PRIMARY KEY (`workflow_id`),
                KEY `idx_status` (`status`),
                KEY `idx_repository` (`repository`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Test Executions tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_devops_test_executions` (
                `test_suite_id` varchar(50) NOT NULL,
                `test_type` enum('unit','integration','e2e','performance','security') NOT NULL,
                `environment` varchar(50) NOT NULL,
                `status` enum('pending','running','success','failed') NOT NULL DEFAULT 'pending',
                `config` text,
                `result` text,
                `total_tests` int(11) DEFAULT 0,
                `passed_tests` int(11) DEFAULT 0,
                `failed_tests` int(11) DEFAULT 0,
                `coverage_percentage` decimal(5,2) DEFAULT NULL,
                `duration` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `completed_at` datetime DEFAULT NULL,
                PRIMARY KEY (`test_suite_id`),
                KEY `idx_test_type` (`test_type`),
                KEY `idx_status` (`status`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Deployments tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_devops_deployments` (
                `deployment_id` varchar(50) NOT NULL,
                `strategy` enum('rolling','blue_green','canary','a_b_test') NOT NULL DEFAULT 'rolling',
                `environment` varchar(50) NOT NULL,
                `status` enum('pending','running','success','failed','rolled_back') NOT NULL DEFAULT 'pending',
                `config` text,
                `result` text,
                `replicas` int(11) DEFAULT 1,
                `health_check_passed` tinyint(1) DEFAULT NULL,
                `rollback_executed` tinyint(1) DEFAULT 0,
                `duration` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `completed_at` datetime DEFAULT NULL,
                PRIMARY KEY (`deployment_id`),
                KEY `idx_strategy` (`strategy`),
                KEY `idx_environment` (`environment`),
                KEY `idx_status` (`status`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Infrastructure as Code Executions tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_devops_iac_executions` (
                `iac_id` varchar(50) NOT NULL,
                `template_id` varchar(100) NOT NULL,
                `provider` varchar(50) NOT NULL DEFAULT 'terraform',
                `operation` enum('plan','apply','destroy') NOT NULL DEFAULT 'apply',
                `status` enum('pending','running','success','failed') NOT NULL DEFAULT 'pending',
                `config` text,
                `result` text,
                `plan_output` text,
                `apply_output` text,
                `resources_created` int(11) DEFAULT 0,
                `resources_updated` int(11) DEFAULT 0,
                `resources_destroyed` int(11) DEFAULT 0,
                `duration` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `completed_at` datetime DEFAULT NULL,
                PRIMARY KEY (`iac_id`),
                KEY `idx_template_id` (`template_id`),
                KEY `idx_provider` (`provider`),
                KEY `idx_operation` (`operation`),
                KEY `idx_status` (`status`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Webhook Events tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_devops_webhook_events` (
                `event_id` int(11) NOT NULL AUTO_INCREMENT,
                `event_type` varchar(50) NOT NULL,
                `repository` varchar(255) DEFAULT NULL,
                `branch` varchar(100) DEFAULT NULL,
                `commit_hash` varchar(40) DEFAULT NULL,
                `webhook_data` text,
                `processing_result` text,
                `status` enum('received','processed','failed','ignored') NOT NULL DEFAULT 'received',
                `triggered_pipeline_id` varchar(50) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `processed_at` datetime DEFAULT NULL,
                PRIMARY KEY (`event_id`),
                KEY `idx_event_type` (`event_type`),
                KEY `idx_repository` (`repository`),
                KEY `idx_status` (`status`),
                KEY `idx_created_at` (`created_at`),
                KEY `idx_pipeline_id` (`triggered_pipeline_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // DevOps Metrics tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_devops_metrics` (
                `metric_id` int(11) NOT NULL AUTO_INCREMENT,
                `metric_type` varchar(50) NOT NULL,
                `metric_name` varchar(100) NOT NULL,
                `metric_value` decimal(10,2) NOT NULL,
                `metric_unit` varchar(20) DEFAULT NULL,
                `environment` varchar(50) DEFAULT NULL,
                `tags` text,
                `recorded_at` datetime NOT NULL,
                PRIMARY KEY (`metric_id`),
                KEY `idx_metric_type` (`metric_type`),
                KEY `idx_metric_name` (`metric_name`),
                KEY `idx_environment` (`environment`),
                KEY `idx_recorded_at` (`recorded_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // DevOps Activities tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_devops_activities` (
                `activity_id` int(11) NOT NULL AUTO_INCREMENT,
                `activity_type` varchar(50) NOT NULL,
                `description` text NOT NULL,
                `user_id` int(11) DEFAULT NULL,
                `user_name` varchar(100) DEFAULT NULL,
                `status` enum('pending','running','completed','failed','cancelled') NOT NULL DEFAULT 'completed',
                `metadata` text,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`activity_id`),
                KEY `idx_activity_type` (`activity_type`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_status` (`status`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // DevOps Alerts tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_devops_alerts` (
                `alert_id` int(11) NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `message` text NOT NULL,
                `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
                `alert_type` varchar(50) NOT NULL,
                `source` varchar(100) DEFAULT NULL,
                `status` enum('active','acknowledged','resolved') NOT NULL DEFAULT 'active',
                `metadata` text,
                `created_at` datetime NOT NULL,
                `acknowledged_at` datetime DEFAULT NULL,
                `resolved_at` datetime DEFAULT NULL,
                PRIMARY KEY (`alert_id`),
                KEY `idx_severity` (`severity`),
                KEY `idx_alert_type` (`alert_type`),
                KEY `idx_status` (`status`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    /**
     * Database tablolarını kaldırır
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_devops_alerts`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_devops_activities`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_devops_metrics`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_devops_webhook_events`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_devops_iac_executions`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_devops_deployments`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_devops_test_executions`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_devops_gitops_workflows`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_devops_pipelines`");
    }
    
    /**
     * Yeni pipeline ekler
     */
    public function addPipeline($config, $result) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_devops_pipelines` 
            SET 
                `pipeline_id` = '" . $this->db->escape($result['pipeline_id']) . "',
                `name` = '" . $this->db->escape($config['name']) . "',
                `repository_url` = '" . $this->db->escape($config['repository_url']) . "',
                `branch` = '" . $this->db->escape($config['branch']) . "',
                `trigger_type` = '" . $this->db->escape($config['trigger_type']) . "',
                `status` = '" . $this->db->escape($result['status']) . "',
                `config` = '" . $this->db->escape(json_encode($config)) . "',
                `result` = '" . $this->db->escape(json_encode($result)) . "',
                `duration` = " . (int)($result['duration'] ?? 0) . ",
                `created_at` = NOW(),
                `started_at` = NOW()
        ");
        
        // Activity kaydı
        $this->addActivity('pipeline_created', 'Pipeline created: ' . $config['name'], 'system');
        
        return $this->db->getLastId();
    }
    
    /**
     * Pipeline durumunu günceller
     */
    public function updatePipelineStatus($pipeline_id, $status, $result = null) {
        $sql = "
            UPDATE `" . DB_PREFIX . "meschain_devops_pipelines` 
            SET 
                `status` = '" . $this->db->escape($status) . "',
                `updated_at` = NOW()
        ";
        
        if ($result) {
            $sql .= ", `result` = '" . $this->db->escape(json_encode($result)) . "'";
        }
        
        if (in_array($status, ['success', 'failed', 'cancelled'])) {
            $sql .= ", `completed_at` = NOW()";
        }
        
        $sql .= " WHERE `pipeline_id` = '" . $this->db->escape($pipeline_id) . "'";
        
        $this->db->query($sql);
    }
    
    /**
     * GitOps workflow ekler
     */
    public function addGitOpsWorkflow($config, $result) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_devops_gitops_workflows` 
            SET 
                `workflow_id` = '" . $this->db->escape($result['workflow_id']) . "',
                `repository` = '" . $this->db->escape($config['repository']) . "',
                `branch` = '" . $this->db->escape($config['branch']) . "',
                `status` = '" . $this->db->escape($result['status']) . "',
                `config` = '" . $this->db->escape(json_encode($config)) . "',
                `result` = '" . $this->db->escape(json_encode($result)) . "',
                `changes_detected` = '" . $this->db->escape(json_encode($result['changes'] ?? array())) . "',
                `deployment_strategy` = '" . $this->db->escape($result['deployment_strategy'] ?? '') . "',
                `created_at` = NOW()
        ");
        
        // Activity kaydı
        $this->addActivity('gitops_workflow', 'GitOps workflow triggered for ' . $config['repository'], 'system');
        
        return $this->db->getLastId();
    }
    
    /**
     * Test execution ekler
     */
    public function addTestExecution($config, $result) {
        $test_results = $result['results'][$config['test_types'][0]] ?? array();
        
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_devops_test_executions` 
            SET 
                `test_suite_id` = '" . $this->db->escape($result['test_suite_id']) . "',
                `test_type` = '" . $this->db->escape($config['test_types'][0]) . "',
                `environment` = '" . $this->db->escape($config['environment']) . "',
                `status` = '" . $this->db->escape($result['status']) . "',
                `config` = '" . $this->db->escape(json_encode($config)) . "',
                `result` = '" . $this->db->escape(json_encode($result)) . "',
                `total_tests` = " . (int)($test_results['total'] ?? 0) . ",
                `passed_tests` = " . (int)($test_results['passed_count'] ?? 0) . ",
                `failed_tests` = " . (int)($test_results['failed_count'] ?? 0) . ",
                `coverage_percentage` = " . (float)($test_results['coverage'] ?? 0) . ",
                `created_at` = NOW()
        ");
        
        // Activity kaydı
        $this->addActivity('test_execution', 'Test suite executed: ' . $config['test_types'][0], 'system');
        
        return $this->db->getLastId();
    }
    
    /**
     * Deployment ekler
     */
    public function addDeployment($config, $result) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_devops_deployments` 
            SET 
                `deployment_id` = '" . $this->db->escape($result['deployment_id']) . "',
                `strategy` = '" . $this->db->escape($config['strategy']) . "',
                `environment` = '" . $this->db->escape($config['environment']) . "',
                `status` = '" . $this->db->escape($result['status']) . "',
                `config` = '" . $this->db->escape(json_encode($config)) . "',
                `result` = '" . $this->db->escape(json_encode($result)) . "',
                `replicas` = " . (int)($config['replicas'] ?? 1) . ",
                `health_check_passed` = " . (int)($result['post_checks']['passed'] ?? 1) . ",
                `rollback_executed` = " . (int)($result['status'] === 'rolled_back' ? 1 : 0) . ",
                `created_at` = NOW()
        ");
        
        // Activity kaydı
        $this->addActivity('deployment', 'Deployment to ' . $config['environment'] . ' using ' . $config['strategy'], 'system');
        
        return $this->db->getLastId();
    }
    
    /**
     * IaC execution ekler
     */
    public function addIaCExecution($config, $result) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_devops_iac_executions` 
            SET 
                `iac_id` = '" . $this->db->escape($result['iac_id']) . "',
                `template_id` = '" . $this->db->escape($config['template_id']) . "',
                `provider` = '" . $this->db->escape($config['provider']) . "',
                `operation` = '" . $this->db->escape($config['dry_run'] ? 'plan' : 'apply') . "',
                `status` = '" . $this->db->escape($result['status']) . "',
                `config` = '" . $this->db->escape(json_encode($config)) . "',
                `result` = '" . $this->db->escape(json_encode($result)) . "',
                `plan_output` = '" . $this->db->escape(json_encode($result['plan'] ?? array())) . "',
                `apply_output` = '" . $this->db->escape(json_encode($result['apply'] ?? array())) . "',
                `created_at` = NOW()
        ");
        
        // Activity kaydı
        $operation = $config['dry_run'] ? 'planned' : 'applied';
        $this->addActivity('iac_execution', 'Infrastructure ' . $operation . ': ' . $config['template_id'], 'system');
        
        return $this->db->getLastId();
    }
    
    /**
     * Webhook event ekler
     */
    public function addWebhookEvent($webhook_data, $result) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_devops_webhook_events` 
            SET 
                `event_type` = '" . $this->db->escape($webhook_data['event_type'] ?? 'unknown') . "',
                `repository` = '" . $this->db->escape($webhook_data['repository'] ?? '') . "',
                `branch` = '" . $this->db->escape($webhook_data['branch'] ?? '') . "',
                `commit_hash` = '" . $this->db->escape($webhook_data['commit_hash'] ?? '') . "',
                `webhook_data` = '" . $this->db->escape(json_encode($webhook_data)) . "',
                `processing_result` = '" . $this->db->escape(json_encode($result)) . "',
                `status` = '" . $this->db->escape($result['status']) . "',
                `triggered_pipeline_id` = '" . $this->db->escape($result['pipeline_id'] ?? '') . "',
                `created_at` = NOW(),
                `processed_at` = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Metrik ekler
     */
    public function addMetric($type, $name, $value, $unit = null, $environment = null, $tags = array()) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_devops_metrics` 
            SET 
                `metric_type` = '" . $this->db->escape($type) . "',
                `metric_name` = '" . $this->db->escape($name) . "',
                `metric_value` = " . (float)$value . ",
                `metric_unit` = '" . $this->db->escape($unit) . "',
                `environment` = '" . $this->db->escape($environment) . "',
                `tags` = '" . $this->db->escape(json_encode($tags)) . "',
                `recorded_at` = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Activity ekler
     */
    public function addActivity($type, $description, $user_name = null, $status = 'completed', $metadata = array()) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_devops_activities` 
            SET 
                `activity_type` = '" . $this->db->escape($type) . "',
                `description` = '" . $this->db->escape($description) . "',
                `user_name` = '" . $this->db->escape($user_name) . "',
                `status` = '" . $this->db->escape($status) . "',
                `metadata` = '" . $this->db->escape(json_encode($metadata)) . "',
                `created_at` = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Alert ekler
     */
    public function addAlert($title, $message, $severity = 'medium', $type = 'general', $source = null, $metadata = array()) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_devops_alerts` 
            SET 
                `title` = '" . $this->db->escape($title) . "',
                `message` = '" . $this->db->escape($message) . "',
                `severity` = '" . $this->db->escape($severity) . "',
                `alert_type` = '" . $this->db->escape($type) . "',
                `source` = '" . $this->db->escape($source) . "',
                `metadata` = '" . $this->db->escape(json_encode($metadata)) . "',
                `created_at` = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Pipeline listesini getirir
     */
    public function getPipelines($limit = 20, $offset = 0) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_devops_pipelines` 
            ORDER BY `created_at` DESC 
            LIMIT " . (int)$offset . ", " . (int)$limit
        );
        
        return $query->rows;
    }
    
    /**
     * Pipeline detayını getirir
     */
    public function getPipeline($pipeline_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_devops_pipelines` 
            WHERE `pipeline_id` = '" . $this->db->escape($pipeline_id) . "'
        ");
        
        return $query->row;
    }
    
    /**
     * GitOps workflow listesini getirir
     */
    public function getGitOpsWorkflows($limit = 20, $offset = 0) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_devops_gitops_workflows` 
            ORDER BY `created_at` DESC 
            LIMIT " . (int)$offset . ", " . (int)$limit
        );
        
        return $query->rows;
    }
    
    /**
     * Test execution listesini getirir
     */
    public function getTestExecutions($limit = 20, $offset = 0) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_devops_test_executions` 
            ORDER BY `created_at` DESC 
            LIMIT " . (int)$offset . ", " . (int)$limit
        );
        
        return $query->rows;
    }
    
    /**
     * Deployment listesini getirir
     */
    public function getDeployments($limit = 20, $offset = 0) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_devops_deployments` 
            ORDER BY `created_at` DESC 
            LIMIT " . (int)$offset . ", " . (int)$limit
        );
        
        return $query->rows;
    }
    
    /**
     * IaC execution listesini getirir
     */
    public function getIaCExecutions($limit = 20, $offset = 0) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_devops_iac_executions` 
            ORDER BY `created_at` DESC 
            LIMIT " . (int)$offset . ", " . (int)$limit
        );
        
        return $query->rows;
    }
    
    /**
     * Activity listesini getirir
     */
    public function getActivities($limit = 50, $offset = 0) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_devops_activities` 
            ORDER BY `created_at` DESC 
            LIMIT " . (int)$offset . ", " . (int)$limit
        );
        
        return $query->rows;
    }
    
    /**
     * Alert listesini getirir
     */
    public function getAlerts($status = 'active', $limit = 20, $offset = 0) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_devops_alerts` 
            WHERE `status` = '" . $this->db->escape($status) . "'
            ORDER BY `severity` DESC, `created_at` DESC 
            LIMIT " . (int)$offset . ", " . (int)$limit
        );
        
        return $query->rows;
    }
    
    /**
     * Metrikleri getirir
     */
    public function getMetrics($type = null, $name = null, $environment = null, $hours = 24) {
        $sql = "
            SELECT * FROM `" . DB_PREFIX . "meschain_devops_metrics` 
            WHERE `recorded_at` >= DATE_SUB(NOW(), INTERVAL " . (int)$hours . " HOUR)
        ";
        
        if ($type) {
            $sql .= " AND `metric_type` = '" . $this->db->escape($type) . "'";
        }
        
        if ($name) {
            $sql .= " AND `metric_name` = '" . $this->db->escape($name) . "'";
        }
        
        if ($environment) {
            $sql .= " AND `environment` = '" . $this->db->escape($environment) . "'";
        }
        
        $sql .= " ORDER BY `recorded_at` DESC";
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Pipeline istatistiklerini getirir
     */
    public function getPipelineStats($days = 30) {
        $query = $this->db->query("
            SELECT 
                `status`,
                COUNT(*) as count,
                AVG(`duration`) as avg_duration
            FROM `" . DB_PREFIX . "meschain_devops_pipelines` 
            WHERE `created_at` >= DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            GROUP BY `status`
        ");
        
        return $query->rows;
    }
    
    /**
     * Deployment istatistiklerini getirir
     */
    public function getDeploymentStats($days = 30) {
        $query = $this->db->query("
            SELECT 
                `environment`,
                `strategy`,
                `status`,
                COUNT(*) as count,
                AVG(`duration`) as avg_duration
            FROM `" . DB_PREFIX . "meschain_devops_deployments` 
            WHERE `created_at` >= DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            GROUP BY `environment`, `strategy`, `status`
        ");
        
        return $query->rows;
    }
    
    /**
     * Test istatistiklerini getirir
     */
    public function getTestStats($days = 30) {
        $query = $this->db->query("
            SELECT 
                `test_type`,
                `environment`,
                COUNT(*) as total_executions,
                AVG(`coverage_percentage`) as avg_coverage,
                SUM(`total_tests`) as total_tests,
                SUM(`passed_tests`) as total_passed,
                SUM(`failed_tests`) as total_failed
            FROM `" . DB_PREFIX . "meschain_devops_test_executions` 
            WHERE `created_at` >= DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            GROUP BY `test_type`, `environment`
        ");
        
        return $query->rows;
    }
    
    /**
     * DORA metrikleri hesaplar
     */
    public function calculateDORAMetrics($days = 30) {
        // Deployment Frequency
        $deployment_frequency_query = $this->db->query("
            SELECT COUNT(*) / " . (int)$days . " as deployment_frequency
            FROM `" . DB_PREFIX . "meschain_devops_deployments` 
            WHERE `status` = 'success' 
            AND `created_at` >= DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
        ");
        
        // Lead Time (Pipeline duration ortalama)
        $lead_time_query = $this->db->query("
            SELECT AVG(`duration`) / 3600 as lead_time_hours
            FROM `" . DB_PREFIX . "meschain_devops_pipelines` 
            WHERE `status` = 'success' 
            AND `created_at` >= DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
        ");
        
        // MTTR (Mean Time To Recovery)
        $mttr_query = $this->db->query("
            SELECT AVG(`duration`) / 60 as mttr_minutes
            FROM `" . DB_PREFIX . "meschain_devops_deployments` 
            WHERE `status` = 'rolled_back' 
            AND `created_at` >= DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
        ");
        
        // Change Failure Rate
        $change_failure_query = $this->db->query("
            SELECT 
                (SELECT COUNT(*) FROM `" . DB_PREFIX . "meschain_devops_deployments` 
                 WHERE `status` IN ('failed', 'rolled_back') 
                 AND `created_at` >= DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)) /
                (SELECT COUNT(*) FROM `" . DB_PREFIX . "meschain_devops_deployments` 
                 WHERE `created_at` >= DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)) * 100 
                as change_failure_rate
        ");
        
        return array(
            'deployment_frequency' => round($deployment_frequency_query->row['deployment_frequency'] ?? 0, 2),
            'lead_time' => round($lead_time_query->row['lead_time_hours'] ?? 0, 2),
            'mttr' => round($mttr_query->row['mttr_minutes'] ?? 0, 2),
            'change_failure_rate' => round($change_failure_query->row['change_failure_rate'] ?? 0, 2)
        );
    }
    
    /**
     * Alert'i acknowledge eder
     */
    public function acknowledgeAlert($alert_id) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "meschain_devops_alerts` 
            SET 
                `status` = 'acknowledged',
                `acknowledged_at` = NOW()
            WHERE `alert_id` = " . (int)$alert_id
        );
    }
    
    /**
     * Alert'i resolve eder
     */
    public function resolveAlert($alert_id) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "meschain_devops_alerts` 
            SET 
                `status` = 'resolved',
                `resolved_at` = NOW()
            WHERE `alert_id` = " . (int)$alert_id
        );
    }
    
    /**
     * Eski kayıtları temizler
     */
    public function cleanupOldRecords($days = 90) {
        $tables = array(
            'meschain_devops_pipelines',
            'meschain_devops_gitops_workflows',
            'meschain_devops_test_executions',
            'meschain_devops_deployments',
            'meschain_devops_iac_executions',
            'meschain_devops_webhook_events',
            'meschain_devops_metrics',
            'meschain_devops_activities'
        );
        
        foreach ($tables as $table) {
            $this->db->query("
                DELETE FROM `" . DB_PREFIX . $table . "` 
                WHERE `created_at` < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            ");
        }
        
        // Resolved alerts'leri temizle
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "meschain_devops_alerts` 
            WHERE `status` = 'resolved' 
            AND `resolved_at` < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
        ");
    }
}
?> 