-- AI Testing Framework Database Schema
-- Part of COPILOT-TASK-002: AI-Powered Testing & Quality Assurance
-- Database tables for storing AI testing framework data, metrics, and results

-- Table for storing quality assessments
CREATE TABLE IF NOT EXISTS `meschain_quality_assessments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assessment_date` datetime NOT NULL,
  `project_path` varchar(500) DEFAULT NULL,
  `deployment_ready` tinyint(1) NOT NULL DEFAULT 0,
  `confidence_score` decimal(5,3) NOT NULL DEFAULT 0.000,
  `risk_level` decimal(5,3) NOT NULL DEFAULT 0.000,
  `quality_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `threshold_compliance` decimal(5,2) NOT NULL DEFAULT 0.00,
  `execution_time` decimal(8,3) NOT NULL DEFAULT 0.000,
  `detailed_metrics` longtext,
  `failed_gates` text,
  `recommendations` longtext,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_assessment_date` (`assessment_date`),
  KEY `idx_project_path` (`project_path`(255)),
  KEY `idx_quality_score` (`quality_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table for storing predictive quality reports
CREATE TABLE IF NOT EXISTS `meschain_quality_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` varchar(50) NOT NULL,
  `project_id` varchar(100) NOT NULL,
  `report_type` varchar(50) NOT NULL DEFAULT 'comprehensive',
  `generation_timestamp` datetime NOT NULL,
  `time_horizon` varchar(20) NOT NULL DEFAULT '30 days',
  `predictions` longtext,
  `trend_analysis` longtext,
  `early_warnings` longtext,
  `actionable_insights` longtext,
  `recommendations` longtext,
  `data_quality_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `prediction_confidence` decimal(5,3) NOT NULL DEFAULT 0.000,
  `dashboard_data` longtext,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_report_id` (`report_id`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_generation_timestamp` (`generation_timestamp`),
  KEY `idx_report_type` (`report_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table for storing AI testing framework integration results
CREATE TABLE IF NOT EXISTS `meschain_ai_integrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_path` varchar(500) NOT NULL,
  `integration_timestamp` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `execution_time` decimal(8,3) NOT NULL DEFAULT 0.000,
  `enhanced_test_suites` longtext,
  `integration_metrics` longtext,
  `configuration` longtext,
  `phpunit_integration` text,
  `quality_gates_setup` text,
  `reporting_setup` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_project_path` (`project_path`(255)),
  KEY `idx_integration_timestamp` (`integration_timestamp`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table for storing AI workflow executions
CREATE TABLE IF NOT EXISTS `meschain_ai_workflows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_id` varchar(50) NOT NULL,
  `project_path` varchar(500) NOT NULL,
  `workflow_timestamp` datetime NOT NULL,
  `total_execution_time` decimal(8,3) NOT NULL DEFAULT 0.000,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `phases` longtext,
  `overall_results` longtext,
  `framework_version` varchar(20) NOT NULL DEFAULT '3.1.0',
  `options` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_workflow_id` (`workflow_id`),
  KEY `idx_project_path` (`project_path`(255)),
  KEY `idx_workflow_timestamp` (`workflow_timestamp`),
  KEY `idx_status` (`status`),
  KEY `idx_framework_version` (`framework_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table for storing test generation results
CREATE TABLE IF NOT EXISTS `meschain_test_generation_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `generation_id` varchar(50) NOT NULL,
  `project_path` varchar(500) NOT NULL,
  `source_file` varchar(500) NOT NULL,
  `test_file` varchar(500) NOT NULL,
  `generation_timestamp` datetime NOT NULL,
  `tests_generated` int(11) NOT NULL DEFAULT 0,
  `coverage_improvement` decimal(5,2) NOT NULL DEFAULT 0.00,
  `generation_time` decimal(8,3) NOT NULL DEFAULT 0.000,
  `ai_confidence` decimal(5,3) NOT NULL DEFAULT 0.000,
  `test_methods` longtext,
  `coverage_analysis` text,
  `complexity_analysis` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_generation_id` (`generation_id`),
  KEY `idx_project_path` (`project_path`(255)),
  KEY `idx_source_file` (`source_file`(255)),
  KEY `idx_generation_timestamp` (`generation_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table for storing technical debt analysis results
CREATE TABLE IF NOT EXISTS `meschain_technical_debt_analysis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `analysis_id` varchar(50) NOT NULL,
  `project_path` varchar(500) NOT NULL,
  `analysis_timestamp` datetime NOT NULL,
  `overall_debt_ratio` decimal(5,2) NOT NULL DEFAULT 0.00,
  `debt_categories` longtext,
  `roi_analysis` longtext,
  `refactoring_roadmap` longtext,
  `severity_distribution` text,
  `cost_analysis` text,
  `payback_period` decimal(8,2) DEFAULT NULL,
  `ml_confidence` decimal(5,3) NOT NULL DEFAULT 0.000,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_analysis_id` (`analysis_id`),
  KEY `idx_project_path` (`project_path`(255)),
  KEY `idx_analysis_timestamp` (`analysis_timestamp`),
  KEY `idx_debt_ratio` (`overall_debt_ratio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table for storing test execution metrics
CREATE TABLE IF NOT EXISTS `meschain_test_execution_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `execution_id` varchar(50) NOT NULL,
  `project_path` varchar(500) NOT NULL,
  `execution_timestamp` datetime NOT NULL,
  `total_tests` int(11) NOT NULL DEFAULT 0,
  `passed_tests` int(11) NOT NULL DEFAULT 0,
  `failed_tests` int(11) NOT NULL DEFAULT 0,
  `skipped_tests` int(11) NOT NULL DEFAULT 0,
  `execution_time` decimal(8,3) NOT NULL DEFAULT 0.000,
  `optimization_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `optimization_impact` decimal(5,2) DEFAULT NULL,
  `parallel_execution` tinyint(1) NOT NULL DEFAULT 0,
  `failure_predictions` text,
  `execution_strategy` varchar(50) DEFAULT NULL,
  `performance_metrics` longtext,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_execution_id` (`execution_id`),
  KEY `idx_project_path` (`project_path`(255)),
  KEY `idx_execution_timestamp` (`execution_timestamp`),
  KEY `idx_optimization_enabled` (`optimization_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table for storing AI model performance metrics
CREATE TABLE IF NOT EXISTS `meschain_ai_model_performance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(100) NOT NULL,
  `model_version` varchar(20) NOT NULL DEFAULT '1.0',
  `performance_date` datetime NOT NULL,
  `accuracy_score` decimal(5,3) NOT NULL DEFAULT 0.000,
  `precision_score` decimal(5,3) NOT NULL DEFAULT 0.000,
  `recall_score` decimal(5,3) NOT NULL DEFAULT 0.000,
  `f1_score` decimal(5,3) NOT NULL DEFAULT 0.000,
  `training_data_size` int(11) DEFAULT NULL,
  `prediction_count` int(11) NOT NULL DEFAULT 0,
  `execution_time` decimal(8,3) NOT NULL DEFAULT 0.000,
  `model_parameters` text,
  `performance_notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_model_name` (`model_name`),
  KEY `idx_performance_date` (`performance_date`),
  KEY `idx_accuracy_score` (`accuracy_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table for storing quality gate configurations
CREATE TABLE IF NOT EXISTS `meschain_quality_gate_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_id` varchar(50) NOT NULL,
  `project_path` varchar(500) NOT NULL,
  `environment` varchar(50) NOT NULL DEFAULT 'production',
  `gate_name` varchar(100) NOT NULL,
  `threshold_config` longtext,
  `custom_rules` longtext,
  `notification_config` text,
  `activation_status` varchar(20) NOT NULL DEFAULT 'active',
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_config_id` (`config_id`),
  KEY `idx_project_path` (`project_path`(255)),
  KEY `idx_environment` (`environment`),
  KEY `idx_activation_status` (`activation_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table for storing predictive alerts
CREATE TABLE IF NOT EXISTS `meschain_predictive_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_id` varchar(50) NOT NULL,
  `project_path` varchar(500) NOT NULL,
  `alert_type` varchar(50) NOT NULL,
  `severity` varchar(20) NOT NULL DEFAULT 'medium',
  `alert_timestamp` datetime NOT NULL,
  `prediction_horizon` varchar(20) NOT NULL DEFAULT '7 days',
  `confidence_score` decimal(5,3) NOT NULL DEFAULT 0.000,
  `alert_message` text NOT NULL,
  `recommended_actions` longtext,
  `alert_data` longtext,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `acknowledged_by` varchar(100) DEFAULT NULL,
  `acknowledged_at` datetime DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_alert_id` (`alert_id`),
  KEY `idx_project_path` (`project_path`(255)),
  KEY `idx_alert_type` (`alert_type`),
  KEY `idx_severity` (`severity`),
  KEY `idx_status` (`status`),
  KEY `idx_alert_timestamp` (`alert_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table for storing framework configuration and settings
CREATE TABLE IF NOT EXISTS `meschain_ai_framework_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL,
  `config_value` longtext,
  `config_type` varchar(50) NOT NULL DEFAULT 'string',
  `description` text,
  `is_system_config` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_config_key` (`config_key`),
  KEY `idx_config_type` (`config_type`),
  KEY `idx_is_system_config` (`is_system_config`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insert default framework configuration
INSERT INTO `meschain_ai_framework_config` (`config_key`, `config_value`, `config_type`, `description`, `is_system_config`) VALUES
('framework_version', '3.1.0', 'string', 'AI Testing Framework Version', 1),
('default_quality_thresholds', '{"code_coverage": 95.0, "code_quality_score": 85.0, "security_score": 90.0, "performance_score": 80.0, "technical_debt_ratio": 5.0, "bug_likelihood": 15.0, "test_pass_rate": 98.0, "documentation_coverage": 80.0}', 'json', 'Default Quality Gate Thresholds', 1),
('ai_model_accuracy_targets', '{"code_quality_prediction": 88.2, "technical_debt_detection": 93.7, "test_failure_prediction": 89.3, "deployment_risk_assessment": 91.5}', 'json', 'AI Model Accuracy Targets', 1),
('performance_targets', '{"test_coverage": 95.0, "execution_time_optimization": 34.2, "quality_gate_response_time": 2.5, "prediction_accuracy": 90.0}', 'json', 'Framework Performance Targets', 1),
('default_prediction_horizon', '30 days', 'string', 'Default Prediction Time Horizon', 1),
('enable_parallel_execution', '1', 'boolean', 'Enable Parallel Test Execution', 0),
('enable_ai_optimization', '1', 'boolean', 'Enable AI-Powered Optimization', 0),
('enable_predictive_analysis', '1', 'boolean', 'Enable Predictive Quality Analysis', 0),
('max_execution_workers', '4', 'integer', 'Maximum Number of Execution Workers', 0),
('report_retention_days', '365', 'integer', 'Report Retention Period (Days)', 0);

-- Create indexes for optimal performance
CREATE INDEX idx_quality_assessments_composite ON meschain_quality_assessments(project_path(255), assessment_date);
CREATE INDEX idx_quality_reports_composite ON meschain_quality_reports(project_id, generation_timestamp);
CREATE INDEX idx_workflows_composite ON meschain_ai_workflows(project_path(255), workflow_timestamp, status);
CREATE INDEX idx_alerts_composite ON meschain_predictive_alerts(project_path(255), alert_timestamp, status);

-- Create views for common queries
CREATE VIEW v_latest_quality_assessments AS
SELECT 
    qa.*,
    RANK() OVER (PARTITION BY project_path ORDER BY assessment_date DESC) as ranking
FROM meschain_quality_assessments qa;

CREATE VIEW v_framework_performance_summary AS
SELECT 
    DATE(workflow_timestamp) as execution_date,
    COUNT(*) as total_workflows,
    AVG(total_execution_time) as avg_execution_time,
    AVG(JSON_EXTRACT(overall_results, '$.phase_success_rate')) as avg_success_rate,
    AVG(JSON_EXTRACT(overall_results, '$.overall_quality_score')) as avg_quality_score,
    SUM(JSON_EXTRACT(overall_results, '$.total_tests_generated')) as total_tests_generated
FROM meschain_ai_workflows 
WHERE status = 'completed'
GROUP BY DATE(workflow_timestamp)
ORDER BY execution_date DESC;

CREATE VIEW v_active_quality_alerts AS
SELECT 
    alert_id,
    project_path,
    alert_type,
    severity,
    alert_timestamp,
    confidence_score,
    alert_message,
    DATEDIFF(NOW(), alert_timestamp) as days_active
FROM meschain_predictive_alerts 
WHERE status = 'active'
ORDER BY severity DESC, alert_timestamp DESC;

-- Stored procedures for common operations
DELIMITER $$

CREATE PROCEDURE GetProjectQualityTrend(IN p_project_path VARCHAR(500), IN p_days INT)
BEGIN
    SELECT 
        DATE(assessment_date) as assessment_date,
        AVG(quality_score) as avg_quality_score,
        AVG(confidence_score) as avg_confidence,
        COUNT(*) as assessment_count
    FROM meschain_quality_assessments
    WHERE project_path = p_project_path 
        AND assessment_date >= DATE_SUB(NOW(), INTERVAL p_days DAY)
    GROUP BY DATE(assessment_date)
    ORDER BY assessment_date;
END$$

CREATE PROCEDURE GetFrameworkPerformanceMetrics(IN p_days INT)
BEGIN
    SELECT 
        framework_version,
        COUNT(*) as total_executions,
        AVG(total_execution_time) as avg_execution_time,
        AVG(JSON_EXTRACT(overall_results, '$.phase_success_rate')) as avg_success_rate,
        AVG(JSON_EXTRACT(overall_results, '$.framework_effectiveness')) as avg_effectiveness
    FROM meschain_ai_workflows
    WHERE workflow_timestamp >= DATE_SUB(NOW(), INTERVAL p_days DAY)
        AND status = 'completed'
    GROUP BY framework_version
    ORDER BY framework_version DESC;
END$$

CREATE PROCEDURE CleanupOldRecords(IN p_retention_days INT)
BEGIN
    -- Clean up old quality assessments
    DELETE FROM meschain_quality_assessments 
    WHERE assessment_date < DATE_SUB(NOW(), INTERVAL p_retention_days DAY);
    
    -- Clean up old reports
    DELETE FROM meschain_quality_reports 
    WHERE generation_timestamp < DATE_SUB(NOW(), INTERVAL p_retention_days DAY);
    
    -- Clean up old workflow records
    DELETE FROM meschain_ai_workflows 
    WHERE workflow_timestamp < DATE_SUB(NOW(), INTERVAL p_retention_days DAY);
    
    -- Clean up resolved alerts older than retention period
    DELETE FROM meschain_predictive_alerts 
    WHERE resolved_at IS NOT NULL 
        AND resolved_at < DATE_SUB(NOW(), INTERVAL p_retention_days DAY);
        
    SELECT ROW_COUNT() as records_cleaned;
END$$

DELIMITER ;

-- Triggers for data integrity and auditing
DELIMITER $$

CREATE TRIGGER tr_quality_assessment_audit 
AFTER INSERT ON meschain_quality_assessments
FOR EACH ROW
BEGIN
    -- Log quality assessment for trend analysis
    INSERT INTO meschain_ai_model_performance (
        model_name, 
        performance_date, 
        accuracy_score, 
        prediction_count
    ) VALUES (
        'quality_assessment_engine',
        NEW.assessment_date,
        NEW.confidence_score,
        1
    ) ON DUPLICATE KEY UPDATE 
        prediction_count = prediction_count + 1,
        accuracy_score = (accuracy_score + NEW.confidence_score) / 2;
END$$

CREATE TRIGGER tr_workflow_completion_audit
AFTER UPDATE ON meschain_ai_workflows
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        -- Update framework performance metrics
        INSERT INTO meschain_ai_model_performance (
            model_name,
            performance_date,
            execution_time,
            prediction_count
        ) VALUES (
            'ai_testing_orchestrator',
            NEW.workflow_timestamp,
            NEW.total_execution_time,
            1
        ) ON DUPLICATE KEY UPDATE
            prediction_count = prediction_count + 1,
            execution_time = (execution_time + NEW.total_execution_time) / 2;
    END IF;
END$$

DELIMITER ;
