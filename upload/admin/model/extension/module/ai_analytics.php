<?php
/**
 * ATOM-M023: AI Analytics Model
 * Advanced AI analytics data management with quantum-enhanced processing
 * MesChain-Sync Enterprise v2.3.0 - Musti Team Implementation
 * 
 * @package    MesChain AI Analytics Model
 * @version    2.3.0
 * @author     MUSTI TAKIMI - ATOM Development Team
 * @date       June 7, 2025
 * @copyright  MesTechSync Solutions
 */

class ModelExtensionModuleAiAnalytics extends Model {
    
    private $ai_models_table = 'meschain_ai_models';
    private $predictions_table = 'meschain_ai_predictions';
    private $reports_table = 'meschain_ai_reports';
    private $insights_table = 'meschain_ai_insights';
    private $anomalies_table = 'meschain_ai_anomalies';
    private $segmentation_table = 'meschain_customer_segments';
    private $forecasts_table = 'meschain_sales_forecasts';
    private $optimization_table = 'meschain_inventory_optimization';
    
    public function __construct($registry) {
        parent::__construct($registry);
        $this->createTables();
    }
    
    /**
     * Create necessary database tables
     */
    private function createTables() {
        // AI Models table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->ai_models_table . "` (
                `model_id` int(11) NOT NULL AUTO_INCREMENT,
                `model_name` varchar(255) NOT NULL,
                `model_type` varchar(100) NOT NULL,
                `algorithm` varchar(255) NOT NULL,
                `accuracy` decimal(5,2) DEFAULT 0.00,
                `training_data_points` bigint(20) DEFAULT 0,
                `model_version` varchar(50) DEFAULT '1.0',
                `model_status` enum('training','active','deprecated') DEFAULT 'training',
                `hyperparameters` text,
                `performance_metrics` text,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `last_training` timestamp NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`model_id`),
                KEY `model_type` (`model_type`),
                KEY `model_status` (`model_status`),
                KEY `accuracy` (`accuracy`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // AI Predictions table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->predictions_table . "` (
                `prediction_id` int(11) NOT NULL AUTO_INCREMENT,
                `prediction_uuid` varchar(255) NOT NULL,
                `model_id` int(11) NOT NULL,
                `prediction_type` varchar(100) NOT NULL,
                `input_data` text NOT NULL,
                `prediction_result` text NOT NULL,
                `confidence_score` decimal(5,2) DEFAULT 0.00,
                `processing_time` decimal(8,3) DEFAULT 0.000,
                `quantum_acceleration` decimal(10,2) DEFAULT 0.00,
                `user_id` int(11) DEFAULT NULL,
                `status` enum('pending','completed','failed') DEFAULT 'pending',
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`prediction_id`),
                UNIQUE KEY `prediction_uuid` (`prediction_uuid`),
                KEY `model_id` (`model_id`),
                KEY `prediction_type` (`prediction_type`),
                KEY `status` (`status`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // AI Reports table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->reports_table . "` (
                `report_id` int(11) NOT NULL AUTO_INCREMENT,
                `report_uuid` varchar(255) NOT NULL,
                `report_name` varchar(255) NOT NULL,
                `report_type` varchar(100) NOT NULL,
                `report_period` varchar(50) NOT NULL,
                `report_data` longtext NOT NULL,
                `visualizations` text,
                `ai_insights` text,
                `recommendations` text,
                `generation_time` decimal(8,3) DEFAULT 0.000,
                `file_path` varchar(500) DEFAULT NULL,
                `file_format` varchar(20) DEFAULT 'json',
                `user_id` int(11) DEFAULT NULL,
                `status` enum('generating','completed','failed') DEFAULT 'generating',
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`report_id`),
                UNIQUE KEY `report_uuid` (`report_uuid`),
                KEY `report_type` (`report_type`),
                KEY `status` (`status`),
                KEY `user_id` (`user_id`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // AI Insights table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->insights_table . "` (
                `insight_id` int(11) NOT NULL AUTO_INCREMENT,
                `insight_uuid` varchar(255) NOT NULL,
                `insight_type` varchar(100) NOT NULL,
                `title` varchar(255) NOT NULL,
                `description` text NOT NULL,
                `confidence_score` decimal(5,2) DEFAULT 0.00,
                `impact_level` enum('low','medium','high','critical') DEFAULT 'medium',
                `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
                `recommendations` text,
                `data_source` varchar(255) DEFAULT NULL,
                `model_id` int(11) DEFAULT NULL,
                `user_id` int(11) DEFAULT NULL,
                `status` enum('new','reviewed','implemented','dismissed') DEFAULT 'new',
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `expires_at` timestamp NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`insight_id`),
                UNIQUE KEY `insight_uuid` (`insight_uuid`),
                KEY `insight_type` (`insight_type`),
                KEY `impact_level` (`impact_level`),
                KEY `priority` (`priority`),
                KEY `status` (`status`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // AI Anomalies table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->anomalies_table . "` (
                `anomaly_id` int(11) NOT NULL AUTO_INCREMENT,
                `anomaly_uuid` varchar(255) NOT NULL,
                `anomaly_type` varchar(100) NOT NULL,
                `data_source` varchar(255) NOT NULL,
                `anomaly_score` decimal(8,4) DEFAULT 0.0000,
                `severity_level` enum('low','medium','high','critical') DEFAULT 'medium',
                `detection_algorithm` varchar(100) NOT NULL,
                `anomaly_data` text NOT NULL,
                `context_data` text,
                `recommendations` text,
                `false_positive` tinyint(1) DEFAULT 0,
                `resolved` tinyint(1) DEFAULT 0,
                `resolution_notes` text,
                `model_id` int(11) DEFAULT NULL,
                `user_id` int(11) DEFAULT NULL,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `detected_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `resolved_at` timestamp NULL,
                PRIMARY KEY (`anomaly_id`),
                UNIQUE KEY `anomaly_uuid` (`anomaly_uuid`),
                KEY `anomaly_type` (`anomaly_type`),
                KEY `severity_level` (`severity_level`),
                KEY `detection_algorithm` (`detection_algorithm`),
                KEY `resolved` (`resolved`),
                KEY `detected_at` (`detected_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Customer Segments table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->segmentation_table . "` (
                `segment_id` int(11) NOT NULL AUTO_INCREMENT,
                `segmentation_uuid` varchar(255) NOT NULL,
                `segment_name` varchar(255) NOT NULL,
                `segment_description` text,
                `algorithm_used` varchar(100) NOT NULL,
                `customer_count` int(11) DEFAULT 0,
                `segment_characteristics` text,
                `behavioral_patterns` text,
                `value_metrics` text,
                `recommendations` text,
                `model_id` int(11) DEFAULT NULL,
                `accuracy_score` decimal(5,2) DEFAULT 0.00,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`segment_id`),
                UNIQUE KEY `segmentation_uuid` (`segmentation_uuid`),
                KEY `algorithm_used` (`algorithm_used`),
                KEY `customer_count` (`customer_count`),
                KEY `accuracy_score` (`accuracy_score`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Sales Forecasts table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->forecasts_table . "` (
                `forecast_id` int(11) NOT NULL AUTO_INCREMENT,
                `forecast_uuid` varchar(255) NOT NULL,
                `forecast_type` varchar(100) NOT NULL,
                `forecast_horizon` varchar(50) NOT NULL,
                `forecast_data` longtext NOT NULL,
                `accuracy_metrics` text,
                `trend_analysis` text,
                `seasonality_data` text,
                `confidence_level` decimal(5,2) DEFAULT 95.00,
                `model_id` int(11) DEFAULT NULL,
                `user_id` int(11) DEFAULT NULL,
                `status` enum('generating','completed','failed') DEFAULT 'generating',
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `forecast_date` date NOT NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`forecast_id`),
                UNIQUE KEY `forecast_uuid` (`forecast_uuid`),
                KEY `forecast_type` (`forecast_type`),
                KEY `status` (`status`),
                KEY `forecast_date` (`forecast_date`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Inventory Optimization table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->optimization_table . "` (
                `optimization_id` int(11) NOT NULL AUTO_INCREMENT,
                `optimization_uuid` varchar(255) NOT NULL,
                `optimization_type` varchar(100) NOT NULL,
                `algorithm_used` varchar(100) NOT NULL,
                `optimization_data` longtext NOT NULL,
                `cost_savings` decimal(15,2) DEFAULT 0.00,
                `efficiency_improvement` decimal(5,2) DEFAULT 0.00,
                `recommendations` text,
                `stock_alerts` text,
                `reorder_suggestions` text,
                `model_id` int(11) DEFAULT NULL,
                `user_id` int(11) DEFAULT NULL,
                `status` enum('processing','completed','failed') DEFAULT 'processing',
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `optimization_date` date NOT NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`optimization_id`),
                UNIQUE KEY `optimization_uuid` (`optimization_uuid`),
                KEY `optimization_type` (`optimization_type`),
                KEY `status` (`status`),
                KEY `optimization_date` (`optimization_date`),
                KEY `cost_savings` (`cost_savings`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    /**
     * Save AI model configuration
     */
    public function saveAIModel($model_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->ai_models_table . "` SET 
                `model_name` = '" . $this->db->escape($model_data['name']) . "',
                `model_type` = '" . $this->db->escape($model_data['type']) . "',
                `algorithm` = '" . $this->db->escape($model_data['algorithm']) . "',
                `accuracy` = '" . (float)$model_data['accuracy'] . "',
                `training_data_points` = '" . (int)$model_data['training_data_points'] . "',
                `model_version` = '" . $this->db->escape($model_data['version']) . "',
                `model_status` = '" . $this->db->escape($model_data['status']) . "',
                `hyperparameters` = '" . $this->db->escape(json_encode($model_data['hyperparameters'])) . "',
                `performance_metrics` = '" . $this->db->escape(json_encode($model_data['performance_metrics'])) . "',
                `quantum_enhanced` = '" . (int)$model_data['quantum_enhanced'] . "',
                `last_training` = '" . $this->db->escape($model_data['last_training']) . "'
                ON DUPLICATE KEY UPDATE
                `accuracy` = '" . (float)$model_data['accuracy'] . "',
                `training_data_points` = '" . (int)$model_data['training_data_points'] . "',
                `model_version` = '" . $this->db->escape($model_data['version']) . "',
                `model_status` = '" . $this->db->escape($model_data['status']) . "',
                `hyperparameters` = '" . $this->db->escape(json_encode($model_data['hyperparameters'])) . "',
                `performance_metrics` = '" . $this->db->escape(json_encode($model_data['performance_metrics'])) . "',
                `quantum_enhanced` = '" . (int)$model_data['quantum_enhanced'] . "',
                `last_training` = '" . $this->db->escape($model_data['last_training']) . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save prediction result
     */
    public function savePrediction($prediction_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->predictions_table . "` SET 
                `prediction_uuid` = '" . $this->db->escape($prediction_data['uuid']) . "',
                `model_id` = '" . (int)$prediction_data['model_id'] . "',
                `prediction_type` = '" . $this->db->escape($prediction_data['type']) . "',
                `input_data` = '" . $this->db->escape(json_encode($prediction_data['input_data'])) . "',
                `prediction_result` = '" . $this->db->escape(json_encode($prediction_data['result'])) . "',
                `confidence_score` = '" . (float)$prediction_data['confidence'] . "',
                `processing_time` = '" . (float)$prediction_data['processing_time'] . "',
                `quantum_acceleration` = '" . (float)$prediction_data['quantum_acceleration'] . "',
                `user_id` = '" . (int)$prediction_data['user_id'] . "',
                `status` = '" . $this->db->escape($prediction_data['status']) . "',
                `quantum_enhanced` = '" . (int)$prediction_data['quantum_enhanced'] . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save analytics report
     */
    public function saveReport($report_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->reports_table . "` SET 
                `report_uuid` = '" . $this->db->escape($report_data['uuid']) . "',
                `report_name` = '" . $this->db->escape($report_data['name']) . "',
                `report_type` = '" . $this->db->escape($report_data['type']) . "',
                `report_period` = '" . $this->db->escape($report_data['period']) . "',
                `report_data` = '" . $this->db->escape(json_encode($report_data['data'])) . "',
                `visualizations` = '" . $this->db->escape(json_encode($report_data['visualizations'])) . "',
                `ai_insights` = '" . $this->db->escape(json_encode($report_data['ai_insights'])) . "',
                `recommendations` = '" . $this->db->escape(json_encode($report_data['recommendations'])) . "',
                `generation_time` = '" . (float)$report_data['generation_time'] . "',
                `file_path` = '" . $this->db->escape($report_data['file_path']) . "',
                `file_format` = '" . $this->db->escape($report_data['file_format']) . "',
                `user_id` = '" . (int)$report_data['user_id'] . "',
                `status` = '" . $this->db->escape($report_data['status']) . "',
                `quantum_enhanced` = '" . (int)$report_data['quantum_enhanced'] . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save AI insight
     */
    public function saveInsight($insight_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->insights_table . "` SET 
                `insight_uuid` = '" . $this->db->escape($insight_data['uuid']) . "',
                `insight_type` = '" . $this->db->escape($insight_data['type']) . "',
                `title` = '" . $this->db->escape($insight_data['title']) . "',
                `description` = '" . $this->db->escape($insight_data['description']) . "',
                `confidence_score` = '" . (float)$insight_data['confidence'] . "',
                `impact_level` = '" . $this->db->escape($insight_data['impact_level']) . "',
                `priority` = '" . $this->db->escape($insight_data['priority']) . "',
                `recommendations` = '" . $this->db->escape(json_encode($insight_data['recommendations'])) . "',
                `data_source` = '" . $this->db->escape($insight_data['data_source']) . "',
                `model_id` = '" . (int)$insight_data['model_id'] . "',
                `user_id` = '" . (int)$insight_data['user_id'] . "',
                `status` = '" . $this->db->escape($insight_data['status']) . "',
                `quantum_enhanced` = '" . (int)$insight_data['quantum_enhanced'] . "',
                `expires_at` = '" . $this->db->escape($insight_data['expires_at']) . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save anomaly detection result
     */
    public function saveAnomaly($anomaly_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->anomalies_table . "` SET 
                `anomaly_uuid` = '" . $this->db->escape($anomaly_data['uuid']) . "',
                `anomaly_type` = '" . $this->db->escape($anomaly_data['type']) . "',
                `data_source` = '" . $this->db->escape($anomaly_data['data_source']) . "',
                `anomaly_score` = '" . (float)$anomaly_data['score'] . "',
                `severity_level` = '" . $this->db->escape($anomaly_data['severity']) . "',
                `detection_algorithm` = '" . $this->db->escape($anomaly_data['algorithm']) . "',
                `anomaly_data` = '" . $this->db->escape(json_encode($anomaly_data['data'])) . "',
                `context_data` = '" . $this->db->escape(json_encode($anomaly_data['context'])) . "',
                `recommendations` = '" . $this->db->escape(json_encode($anomaly_data['recommendations'])) . "',
                `false_positive` = '" . (int)$anomaly_data['false_positive'] . "',
                `resolved` = '" . (int)$anomaly_data['resolved'] . "',
                `resolution_notes` = '" . $this->db->escape($anomaly_data['resolution_notes']) . "',
                `model_id` = '" . (int)$anomaly_data['model_id'] . "',
                `user_id` = '" . (int)$anomaly_data['user_id'] . "',
                `quantum_enhanced` = '" . (int)$anomaly_data['quantum_enhanced'] . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save customer segmentation result
     */
    public function saveSegmentation($segmentation_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->segmentation_table . "` SET 
                `segmentation_uuid` = '" . $this->db->escape($segmentation_data['uuid']) . "',
                `segment_name` = '" . $this->db->escape($segmentation_data['name']) . "',
                `segment_description` = '" . $this->db->escape($segmentation_data['description']) . "',
                `algorithm_used` = '" . $this->db->escape($segmentation_data['algorithm']) . "',
                `customer_count` = '" . (int)$segmentation_data['customer_count'] . "',
                `segment_characteristics` = '" . $this->db->escape(json_encode($segmentation_data['characteristics'])) . "',
                `behavioral_patterns` = '" . $this->db->escape(json_encode($segmentation_data['patterns'])) . "',
                `value_metrics` = '" . $this->db->escape(json_encode($segmentation_data['metrics'])) . "',
                `recommendations` = '" . $this->db->escape(json_encode($segmentation_data['recommendations'])) . "',
                `model_id` = '" . (int)$segmentation_data['model_id'] . "',
                `accuracy_score` = '" . (float)$segmentation_data['accuracy'] . "',
                `quantum_enhanced` = '" . (int)$segmentation_data['quantum_enhanced'] . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save sales forecast
     */
    public function saveForecast($forecast_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->forecasts_table . "` SET 
                `forecast_uuid` = '" . $this->db->escape($forecast_data['uuid']) . "',
                `forecast_type` = '" . $this->db->escape($forecast_data['type']) . "',
                `forecast_horizon` = '" . $this->db->escape($forecast_data['horizon']) . "',
                `forecast_data` = '" . $this->db->escape(json_encode($forecast_data['data'])) . "',
                `accuracy_metrics` = '" . $this->db->escape(json_encode($forecast_data['accuracy_metrics'])) . "',
                `trend_analysis` = '" . $this->db->escape(json_encode($forecast_data['trend_analysis'])) . "',
                `seasonality_data` = '" . $this->db->escape(json_encode($forecast_data['seasonality'])) . "',
                `confidence_level` = '" . (float)$forecast_data['confidence_level'] . "',
                `model_id` = '" . (int)$forecast_data['model_id'] . "',
                `user_id` = '" . (int)$forecast_data['user_id'] . "',
                `status` = '" . $this->db->escape($forecast_data['status']) . "',
                `quantum_enhanced` = '" . (int)$forecast_data['quantum_enhanced'] . "',
                `forecast_date` = '" . $this->db->escape($forecast_data['forecast_date']) . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save inventory optimization
     */
    public function saveOptimization($optimization_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->optimization_table . "` SET 
                `optimization_uuid` = '" . $this->db->escape($optimization_data['uuid']) . "',
                `optimization_type` = '" . $this->db->escape($optimization_data['type']) . "',
                `algorithm_used` = '" . $this->db->escape($optimization_data['algorithm']) . "',
                `optimization_data` = '" . $this->db->escape(json_encode($optimization_data['data'])) . "',
                `cost_savings` = '" . (float)$optimization_data['cost_savings'] . "',
                `efficiency_improvement` = '" . (float)$optimization_data['efficiency_improvement'] . "',
                `recommendations` = '" . $this->db->escape(json_encode($optimization_data['recommendations'])) . "',
                `stock_alerts` = '" . $this->db->escape(json_encode($optimization_data['stock_alerts'])) . "',
                `reorder_suggestions` = '" . $this->db->escape(json_encode($optimization_data['reorder_suggestions'])) . "',
                `model_id` = '" . (int)$optimization_data['model_id'] . "',
                `user_id` = '" . (int)$optimization_data['user_id'] . "',
                `status` = '" . $this->db->escape($optimization_data['status']) . "',
                `quantum_enhanced` = '" . (int)$optimization_data['quantum_enhanced'] . "',
                `optimization_date` = '" . $this->db->escape($optimization_data['optimization_date']) . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Get AI models
     */
    public function getAIModels($filters = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->ai_models_table . "` WHERE 1=1";
        
        if (!empty($filters['model_type'])) {
            $sql .= " AND `model_type` = '" . $this->db->escape($filters['model_type']) . "'";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND `model_status` = '" . $this->db->escape($filters['status']) . "'";
        }
        
        if (!empty($filters['min_accuracy'])) {
            $sql .= " AND `accuracy` >= '" . (float)$filters['min_accuracy'] . "'";
        }
        
        $sql .= " ORDER BY `accuracy` DESC, `created_at` DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get predictions
     */
    public function getPredictions($filters = []) {
        $sql = "SELECT p.*, m.model_name, m.model_type 
                FROM `" . DB_PREFIX . $this->predictions_table . "` p
                LEFT JOIN `" . DB_PREFIX . $this->ai_models_table . "` m ON p.model_id = m.model_id
                WHERE 1=1";
        
        if (!empty($filters['prediction_type'])) {
            $sql .= " AND p.`prediction_type` = '" . $this->db->escape($filters['prediction_type']) . "'";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND p.`status` = '" . $this->db->escape($filters['status']) . "'";
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND p.`user_id` = '" . (int)$filters['user_id'] . "'";
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND p.`created_at` >= '" . $this->db->escape($filters['date_from']) . "'";
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND p.`created_at` <= '" . $this->db->escape($filters['date_to']) . "'";
        }
        
        $sql .= " ORDER BY p.`created_at` DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get reports
     */
    public function getReports($filters = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->reports_table . "` WHERE 1=1";
        
        if (!empty($filters['report_type'])) {
            $sql .= " AND `report_type` = '" . $this->db->escape($filters['report_type']) . "'";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND `status` = '" . $this->db->escape($filters['status']) . "'";
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND `user_id` = '" . (int)$filters['user_id'] . "'";
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND `created_at` >= '" . $this->db->escape($filters['date_from']) . "'";
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND `created_at` <= '" . $this->db->escape($filters['date_to']) . "'";
        }
        
        $sql .= " ORDER BY `created_at` DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get insights
     */
    public function getInsights($filters = []) {
        $sql = "SELECT i.*, m.model_name 
                FROM `" . DB_PREFIX . $this->insights_table . "` i
                LEFT JOIN `" . DB_PREFIX . $this->ai_models_table . "` m ON i.model_id = m.model_id
                WHERE 1=1";
        
        if (!empty($filters['insight_type'])) {
            $sql .= " AND i.`insight_type` = '" . $this->db->escape($filters['insight_type']) . "'";
        }
        
        if (!empty($filters['impact_level'])) {
            $sql .= " AND i.`impact_level` = '" . $this->db->escape($filters['impact_level']) . "'";
        }
        
        if (!empty($filters['priority'])) {
            $sql .= " AND i.`priority` = '" . $this->db->escape($filters['priority']) . "'";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND i.`status` = '" . $this->db->escape($filters['status']) . "'";
        }
        
        // Only get non-expired insights
        $sql .= " AND (i.`expires_at` IS NULL OR i.`expires_at` > NOW())";
        
        $sql .= " ORDER BY i.`priority` DESC, i.`confidence_score` DESC, i.`created_at` DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get anomalies
     */
    public function getAnomalies($filters = []) {
        $sql = "SELECT a.*, m.model_name 
                FROM `" . DB_PREFIX . $this->anomalies_table . "` a
                LEFT JOIN `" . DB_PREFIX . $this->ai_models_table . "` m ON a.model_id = m.model_id
                WHERE 1=1";
        
        if (!empty($filters['anomaly_type'])) {
            $sql .= " AND a.`anomaly_type` = '" . $this->db->escape($filters['anomaly_type']) . "'";
        }
        
        if (!empty($filters['severity_level'])) {
            $sql .= " AND a.`severity_level` = '" . $this->db->escape($filters['severity_level']) . "'";
        }
        
        if (!empty($filters['resolved'])) {
            $sql .= " AND a.`resolved` = '" . (int)$filters['resolved'] . "'";
        }
        
        if (!empty($filters['false_positive'])) {
            $sql .= " AND a.`false_positive` = '" . (int)$filters['false_positive'] . "'";
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND a.`detected_at` >= '" . $this->db->escape($filters['date_from']) . "'";
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND a.`detected_at` <= '" . $this->db->escape($filters['date_to']) . "'";
        }
        
        $sql .= " ORDER BY a.`severity_level` DESC, a.`anomaly_score` DESC, a.`detected_at` DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get analytics statistics
     */
    public function getAnalyticsStatistics($period = '24h') {
        $date_condition = "";
        
        switch ($period) {
            case '1h':
                $date_condition = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
                break;
            case '24h':
                $date_condition = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                break;
            case '7d':
                $date_condition = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case '30d':
                $date_condition = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
        }
        
        $statistics = [];
        
        // Total predictions
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->predictions_table . "` " . $date_condition);
        $statistics['total_predictions'] = $query->row['total'];
        
        // Total reports
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->reports_table . "` " . $date_condition);
        $statistics['total_reports'] = $query->row['total'];
        
        // Total insights
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->insights_table . "` " . $date_condition);
        $statistics['total_insights'] = $query->row['total'];
        
        // Total anomalies
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->anomalies_table . "` " . str_replace('created_at', 'detected_at', $date_condition));
        $statistics['total_anomalies'] = $query->row['total'];
        
        // Average processing time
        $query = $this->db->query("SELECT AVG(processing_time) as avg_time FROM `" . DB_PREFIX . $this->predictions_table . "` " . $date_condition);
        $statistics['average_processing_time'] = round($query->row['avg_time'], 3);
        
        // Active AI models
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->ai_models_table . "` WHERE model_status = 'active'");
        $statistics['active_models'] = $query->row['total'];
        
        // Model accuracy average
        $query = $this->db->query("SELECT AVG(accuracy) as avg_accuracy FROM `" . DB_PREFIX . $this->ai_models_table . "` WHERE model_status = 'active'");
        $statistics['average_model_accuracy'] = round($query->row['avg_accuracy'], 2);
        
        return $statistics;
    }
    
    /**
     * Get dashboard metrics
     */
    public function getDashboardMetrics() {
        $metrics = [];
        
        // Active AI models
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->ai_models_table . "` WHERE model_status = 'active'");
        $metrics['active_models'] = $query->row['total'];
        
        // Today's predictions
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->predictions_table . "` WHERE DATE(created_at) = CURDATE()");
        $metrics['predictions_today'] = $query->row['total'];
        
        // Today's reports
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->reports_table . "` WHERE DATE(created_at) = CURDATE()");
        $metrics['reports_today'] = $query->row['total'];
        
        // Unresolved anomalies
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->anomalies_table . "` WHERE resolved = 0 AND false_positive = 0");
        $metrics['unresolved_anomalies'] = $query->row['total'];
        
        // New insights
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->insights_table . "` WHERE status = 'new'");
        $metrics['new_insights'] = $query->row['total'];
        
        // Average model accuracy
        $query = $this->db->query("SELECT AVG(accuracy) as avg_accuracy FROM `" . DB_PREFIX . $this->ai_models_table . "` WHERE model_status = 'active'");
        $metrics['average_accuracy'] = round($query->row['avg_accuracy'], 2);
        
        return $metrics;
    }
} 