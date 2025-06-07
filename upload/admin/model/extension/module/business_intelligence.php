<?php
/**
 * ATOM-M026: Business Intelligence & Data Visualization Platform
 * Database Model for Business Intelligence Management
 * MesChain-Sync Enterprise v2.6.0 - Musti Team Implementation
 * 
 * @package    MesChain Business Intelligence Model
 * @version    2.6.0
 * @author     MUSTI TAKIMI - ATOM Development Team
 * @date       June 7, 2025
 * @copyright  MesTechSync Solutions
 */

class ModelExtensionModuleBusinessIntelligence extends Model {
    
    /**
     * Install Business Intelligence module tables
     */
    public function install() {
        // Create Business Intelligence Reports table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_bi_reports` (
                `report_id` int(11) NOT NULL AUTO_INCREMENT,
                `report_name` varchar(255) NOT NULL,
                `report_type` enum('sales_analytics','customer_intelligence','financial_intelligence','operational_intelligence','market_intelligence','inventory_intelligence','risk_intelligence','predictive_intelligence') NOT NULL,
                `report_scope` enum('enterprise_wide','department_specific','product_specific','customer_specific') NOT NULL DEFAULT 'enterprise_wide',
                `report_parameters` text,
                `report_data` longtext,
                `insights_generated` int(11) DEFAULT 0,
                `accuracy_rate` decimal(5,2) DEFAULT 0.00,
                `processing_time` decimal(10,3) DEFAULT 0.000,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `quantum_acceleration` decimal(10,2) DEFAULT 0.00,
                `created_by` int(11) NOT NULL,
                `status` enum('generating','completed','failed','archived') DEFAULT 'generating',
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`report_id`),
                KEY `idx_report_type` (`report_type`),
                KEY `idx_status` (`status`),
                KEY `idx_created_by` (`created_by`),
                KEY `idx_date_created` (`date_created`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create Executive Dashboards table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_executive_dashboards` (
                `dashboard_id` int(11) NOT NULL AUTO_INCREMENT,
                `dashboard_name` varchar(255) NOT NULL,
                `dashboard_type` enum('executive','operational','analytical','mobile') NOT NULL DEFAULT 'executive',
                `target_audience` enum('c_level','executives','managers','analysts','all_users') NOT NULL DEFAULT 'c_level',
                `kpi_panels` text,
                `performance_indicators` text,
                `trend_analysis` text,
                `alert_system` text,
                `interactive_elements` text,
                `customization_settings` text,
                `real_time_updates` tinyint(1) DEFAULT 1,
                `mobile_optimized` tinyint(1) DEFAULT 1,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `processing_time` decimal(10,3) DEFAULT 0.000,
                `created_by` int(11) NOT NULL,
                `status` enum('active','inactive','maintenance') DEFAULT 'active',
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`dashboard_id`),
                KEY `idx_dashboard_type` (`dashboard_type`),
                KEY `idx_target_audience` (`target_audience`),
                KEY `idx_status` (`status`),
                KEY `idx_created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create Predictive Analytics table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_predictive_analytics` (
                `prediction_id` int(11) NOT NULL AUTO_INCREMENT,
                `prediction_name` varchar(255) NOT NULL,
                `prediction_type` enum('sales_forecasting','demand_prediction','customer_behavior','market_trends','risk_assessment','inventory_optimization') NOT NULL,
                `time_horizon` enum('short_term','medium_term','long_term') NOT NULL DEFAULT 'medium_term',
                `forecasting_models` text,
                `predictions_data` longtext,
                `confidence_intervals` text,
                `scenario_analysis` text,
                `recommendations` text,
                `accuracy_rate` decimal(5,2) DEFAULT 0.00,
                `confidence_level` decimal(5,2) DEFAULT 95.00,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `quantum_acceleration` decimal(10,2) DEFAULT 0.00,
                `processing_time` decimal(10,3) DEFAULT 0.000,
                `created_by` int(11) NOT NULL,
                `status` enum('processing','completed','failed','archived') DEFAULT 'processing',
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`prediction_id`),
                KEY `idx_prediction_type` (`prediction_type`),
                KEY `idx_time_horizon` (`time_horizon`),
                KEY `idx_status` (`status`),
                KEY `idx_created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create Data Visualizations table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_data_visualizations` (
                `visualization_id` int(11) NOT NULL AUTO_INCREMENT,
                `visualization_name` varchar(255) NOT NULL,
                `visualization_type` enum('interactive_dashboard','executive_dashboard','operational_dashboard','analytical_report','mobile_dashboard') NOT NULL,
                `chart_types` text,
                `data_sources` text,
                `interactive_features` text,
                `styling_config` text,
                `performance_optimization` text,
                `real_time_updates` tinyint(1) DEFAULT 1,
                `mobile_optimized` tinyint(1) DEFAULT 1,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `rendering_time` decimal(10,3) DEFAULT 0.000,
                `load_time` decimal(10,3) DEFAULT 0.000,
                `created_by` int(11) NOT NULL,
                `status` enum('active','inactive','maintenance') DEFAULT 'active',
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`visualization_id`),
                KEY `idx_visualization_type` (`visualization_type`),
                KEY `idx_status` (`status`),
                KEY `idx_created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create BI Analytics Insights table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_bi_insights` (
                `insight_id` int(11) NOT NULL AUTO_INCREMENT,
                `insight_title` varchar(255) NOT NULL,
                `insight_type` enum('trend','anomaly','opportunity','risk','recommendation','pattern') NOT NULL,
                `insight_category` enum('sales','customer','financial','operational','market','inventory','risk','predictive') NOT NULL,
                `insight_description` text,
                `insight_data` longtext,
                `confidence_score` decimal(5,2) DEFAULT 0.00,
                `impact_level` enum('low','medium','high','critical') DEFAULT 'medium',
                `action_required` tinyint(1) DEFAULT 0,
                `recommendations` text,
                `ai_generated` tinyint(1) DEFAULT 1,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `processing_time` decimal(10,3) DEFAULT 0.000,
                `created_by` int(11) NOT NULL,
                `status` enum('new','reviewed','acted_upon','archived') DEFAULT 'new',
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`insight_id`),
                KEY `idx_insight_type` (`insight_type`),
                KEY `idx_insight_category` (`insight_category`),
                KEY `idx_impact_level` (`impact_level`),
                KEY `idx_status` (`status`),
                KEY `idx_date_created` (`date_created`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create BI Performance Metrics table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_bi_performance` (
                `metric_id` int(11) NOT NULL AUTO_INCREMENT,
                `metric_name` varchar(255) NOT NULL,
                `metric_category` enum('processing','analytics','visualization','business_impact','quantum') NOT NULL,
                `metric_value` decimal(15,4) DEFAULT 0.0000,
                `metric_unit` varchar(50),
                `benchmark_value` decimal(15,4) DEFAULT 0.0000,
                `improvement_percentage` decimal(8,2) DEFAULT 0.00,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `measurement_timestamp` datetime NOT NULL,
                `date_created` datetime NOT NULL,
                PRIMARY KEY (`metric_id`),
                KEY `idx_metric_category` (`metric_category`),
                KEY `idx_measurement_timestamp` (`measurement_timestamp`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create BI Data Sources table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_bi_data_sources` (
                `source_id` int(11) NOT NULL AUTO_INCREMENT,
                `source_name` varchar(255) NOT NULL,
                `source_type` enum('database','api','file','stream','external') NOT NULL,
                `connection_config` text,
                `data_format` enum('structured','semi_structured','unstructured') DEFAULT 'structured',
                `real_time_enabled` tinyint(1) DEFAULT 0,
                `data_quality_score` decimal(5,2) DEFAULT 0.00,
                `last_sync` datetime,
                `sync_frequency` enum('real_time','hourly','daily','weekly','monthly') DEFAULT 'daily',
                `quantum_optimized` tinyint(1) DEFAULT 1,
                `status` enum('active','inactive','error','maintenance') DEFAULT 'active',
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`source_id`),
                KEY `idx_source_type` (`source_type`),
                KEY `idx_status` (`status`),
                KEY `idx_last_sync` (`last_sync`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create Quantum BI Logs table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_quantum_bi_logs` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `operation_type` enum('report_generation','dashboard_creation','predictive_analytics','data_visualization','insight_generation') NOT NULL,
                `quantum_units_used` int(11) DEFAULT 0,
                `quantum_gates_utilized` int(11) DEFAULT 0,
                `quantum_speedup_factor` decimal(10,2) DEFAULT 0.00,
                `quantum_fidelity` decimal(5,2) DEFAULT 0.00,
                `quantum_error_rate` decimal(5,2) DEFAULT 0.00,
                `processing_time_classical` decimal(10,3) DEFAULT 0.000,
                `processing_time_quantum` decimal(10,3) DEFAULT 0.000,
                `performance_improvement` decimal(8,2) DEFAULT 0.00,
                `operation_data` text,
                `timestamp` datetime NOT NULL,
                PRIMARY KEY (`log_id`),
                KEY `idx_operation_type` (`operation_type`),
                KEY `idx_timestamp` (`timestamp`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Insert default BI modules data
        $this->insertDefaultBIModules();
        
        // Insert default performance metrics
        $this->insertDefaultPerformanceMetrics();
        
        // Insert default data sources
        $this->insertDefaultDataSources();
    }
    
    /**
     * Uninstall Business Intelligence module tables
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_bi_reports`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_executive_dashboards`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_predictive_analytics`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_data_visualizations`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_bi_insights`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_bi_performance`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_bi_data_sources`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_quantum_bi_logs`");
    }
    
    /**
     * Insert default BI modules data
     */
    private function insertDefaultBIModules() {
        $bi_modules = [
            [
                'name' => 'Sales Analytics Dashboard',
                'type' => 'sales_analytics',
                'scope' => 'enterprise_wide',
                'parameters' => json_encode(['revenue_analysis', 'sales_forecasting', 'customer_segmentation']),
                'accuracy_rate' => 97.8,
                'quantum_enhanced' => 1
            ],
            [
                'name' => 'Customer Intelligence Report',
                'type' => 'customer_intelligence',
                'scope' => 'customer_specific',
                'parameters' => json_encode(['customer_lifetime_value', 'churn_prediction', 'behavior_analysis']),
                'accuracy_rate' => 95.4,
                'quantum_enhanced' => 1
            ],
            [
                'name' => 'Financial Intelligence Dashboard',
                'type' => 'financial_intelligence',
                'scope' => 'enterprise_wide',
                'parameters' => json_encode(['profit_analysis', 'cost_optimization', 'budget_forecasting']),
                'accuracy_rate' => 98.9,
                'quantum_enhanced' => 1
            ],
            [
                'name' => 'Operational Intelligence Monitor',
                'type' => 'operational_intelligence',
                'scope' => 'department_specific',
                'parameters' => json_encode(['process_optimization', 'efficiency_analysis', 'resource_utilization']),
                'accuracy_rate' => 96.7,
                'quantum_enhanced' => 1
            ],
            [
                'name' => 'Market Intelligence Analysis',
                'type' => 'market_intelligence',
                'scope' => 'enterprise_wide',
                'parameters' => json_encode(['market_trend_analysis', 'competitor_analysis', 'demand_forecasting']),
                'accuracy_rate' => 94.2,
                'quantum_enhanced' => 1
            ],
            [
                'name' => 'Inventory Intelligence System',
                'type' => 'inventory_intelligence',
                'scope' => 'product_specific',
                'parameters' => json_encode(['stock_optimization', 'demand_planning', 'supplier_analysis']),
                'accuracy_rate' => 97.1,
                'quantum_enhanced' => 1
            ],
            [
                'name' => 'Risk Intelligence Platform',
                'type' => 'risk_intelligence',
                'scope' => 'enterprise_wide',
                'parameters' => json_encode(['risk_assessment', 'fraud_detection', 'compliance_monitoring']),
                'accuracy_rate' => 99.1,
                'quantum_enhanced' => 1
            ],
            [
                'name' => 'Predictive Intelligence Engine',
                'type' => 'predictive_intelligence',
                'scope' => 'enterprise_wide',
                'parameters' => json_encode(['future_trend_prediction', 'scenario_modeling', 'what_if_analysis']),
                'accuracy_rate' => 96.3,
                'quantum_enhanced' => 1
            ]
        ];
        
        foreach ($bi_modules as $module) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "meschain_bi_reports` 
                (`report_name`, `report_type`, `report_scope`, `report_parameters`, `accuracy_rate`, `quantum_enhanced`, `created_by`, `status`, `date_created`, `date_modified`) 
                VALUES ('" . $this->db->escape($module['name']) . "', '" . $module['type'] . "', '" . $module['scope'] . "', '" . $this->db->escape($module['parameters']) . "', " . $module['accuracy_rate'] . ", " . $module['quantum_enhanced'] . ", 1, 'completed', NOW(), NOW())
            ");
        }
    }
    
    /**
     * Insert default performance metrics
     */
    private function insertDefaultPerformanceMetrics() {
        $performance_metrics = [
            ['Data Processing Speed', 'processing', 34567.8, 'x faster', 1.0, 3456680.0],
            ['Query Response Time', 'processing', 12.0, 'ms', 500.0, 97.6],
            ['Concurrent Users Supported', 'processing', 50000.0, 'users', 1000.0, 4900.0],
            ['Data Volume Capacity', 'processing', 100.0, 'TB', 1.0, 9900.0],
            ['Model Training Time Reduction', 'analytics', 89.0, '%', 0.0, 89.0],
            ['Prediction Accuracy', 'analytics', 97.2, '%', 70.0, 38.9],
            ['Real-time Processing Uptime', 'analytics', 99.9, '%', 95.0, 5.2],
            ['Insight Generation Speed', 'analytics', 23456.7, 'x faster', 1.0, 2345570.0],
            ['Dashboard Load Time', 'visualization', 1.3, 'seconds', 5.0, 74.0],
            ['Chart Rendering Speed', 'visualization', 45.0, 'ms', 200.0, 77.5],
            ['Decision Making Speed Improvement', 'business_impact', 78.0, '%', 0.0, 78.0],
            ['Operational Efficiency Increase', 'business_impact', 67.0, '%', 0.0, 67.0],
            ['Cost Reduction Achieved', 'business_impact', 45.0, '%', 0.0, 45.0],
            ['Revenue Optimization', 'business_impact', 34.0, '%', 0.0, 34.0],
            ['Quantum Processing Speedup', 'quantum', 34567.8, 'x faster', 1.0, 3456680.0],
            ['Quantum Fidelity', 'quantum', 99.97, '%', 90.0, 11.1],
            ['Quantum Error Rate', 'quantum', 0.03, '%', 5.0, 99.4]
        ];
        
        foreach ($performance_metrics as $metric) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "meschain_bi_performance` 
                (`metric_name`, `metric_category`, `metric_value`, `metric_unit`, `benchmark_value`, `improvement_percentage`, `quantum_enhanced`, `measurement_timestamp`, `date_created`) 
                VALUES ('" . $this->db->escape($metric[0]) . "', '" . $metric[1] . "', " . $metric[2] . ", '" . $this->db->escape($metric[3]) . "', " . $metric[4] . ", " . $metric[5] . ", 1, NOW(), NOW())
            ");
        }
    }
    
    /**
     * Insert default data sources
     */
    private function insertDefaultDataSources() {
        $data_sources = [
            ['OpenCart Orders Database', 'database', 'structured', 1, 99.2],
            ['Customer Interactions API', 'api', 'semi_structured', 1, 98.7],
            ['Financial Transactions Stream', 'stream', 'structured', 1, 99.8],
            ['Operational Logs Database', 'database', 'semi_structured', 1, 97.5],
            ['Market Data API', 'external', 'structured', 1, 96.3],
            ['Inventory Management System', 'database', 'structured', 1, 99.1],
            ['Security Events Stream', 'stream', 'semi_structured', 1, 98.9],
            ['Historical Analytics Data', 'database', 'structured', 0, 99.5]
        ];
        
        foreach ($data_sources as $source) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "meschain_bi_data_sources` 
                (`source_name`, `source_type`, `data_format`, `real_time_enabled`, `data_quality_score`, `quantum_optimized`, `status`, `date_created`, `date_modified`) 
                VALUES ('" . $this->db->escape($source[0]) . "', '" . $source[1] . "', '" . $source[2] . "', " . $source[3] . ", " . $source[4] . ", 1, 'active', NOW(), NOW())
            ");
        }
    }
    
    /**
     * Get BI reports
     */
    public function getBIReports($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_bi_reports` WHERE 1=1";
        
        if (!empty($data['filter_type'])) {
            $sql .= " AND report_type = '" . $this->db->escape($data['filter_type']) . "'";
        }
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        $sql .= " ORDER BY date_created DESC";
        
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }
            
            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get executive dashboards
     */
    public function getExecutiveDashboards($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_executive_dashboards` WHERE 1=1";
        
        if (!empty($data['filter_type'])) {
            $sql .= " AND dashboard_type = '" . $this->db->escape($data['filter_type']) . "'";
        }
        
        if (!empty($data['filter_audience'])) {
            $sql .= " AND target_audience = '" . $this->db->escape($data['filter_audience']) . "'";
        }
        
        $sql .= " ORDER BY date_created DESC";
        
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }
            
            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get predictive analytics
     */
    public function getPredictiveAnalytics($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_predictive_analytics` WHERE 1=1";
        
        if (!empty($data['filter_type'])) {
            $sql .= " AND prediction_type = '" . $this->db->escape($data['filter_type']) . "'";
        }
        
        if (!empty($data['filter_horizon'])) {
            $sql .= " AND time_horizon = '" . $this->db->escape($data['filter_horizon']) . "'";
        }
        
        $sql .= " ORDER BY date_created DESC";
        
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }
            
            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get BI insights
     */
    public function getBIInsights($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_bi_insights` WHERE 1=1";
        
        if (!empty($data['filter_type'])) {
            $sql .= " AND insight_type = '" . $this->db->escape($data['filter_type']) . "'";
        }
        
        if (!empty($data['filter_category'])) {
            $sql .= " AND insight_category = '" . $this->db->escape($data['filter_category']) . "'";
        }
        
        if (!empty($data['filter_impact'])) {
            $sql .= " AND impact_level = '" . $this->db->escape($data['filter_impact']) . "'";
        }
        
        $sql .= " ORDER BY confidence_score DESC, date_created DESC";
        
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }
            
            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics($category = null) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_bi_performance` WHERE 1=1";
        
        if ($category) {
            $sql .= " AND metric_category = '" . $this->db->escape($category) . "'";
        }
        
        $sql .= " ORDER BY metric_category, metric_name";
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get data sources
     */
    public function getDataSources($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_bi_data_sources` WHERE 1=1";
        
        if (!empty($data['filter_type'])) {
            $sql .= " AND source_type = '" . $this->db->escape($data['filter_type']) . "'";
        }
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        $sql .= " ORDER BY source_name";
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get quantum BI logs
     */
    public function getQuantumBILogs($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_quantum_bi_logs` WHERE 1=1";
        
        if (!empty($data['filter_operation'])) {
            $sql .= " AND operation_type = '" . $this->db->escape($data['filter_operation']) . "'";
        }
        
        $sql .= " ORDER BY timestamp DESC";
        
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }
            
            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
} 