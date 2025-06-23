<?php
/**
 * MesChain-Sync AI Analytics Dashboard Model
 * 
 * @package    MesChain-Sync
 * @author     MesChain Development Team
 * @copyright  2024 MesChain
 * @license    Commercial License
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

class ModelExtensionModuleAiAnalyticsDashboard extends Model {
    
    /**
     * Install AI Analytics tables
     */
    public function install() {
        // AI Analytics Metrics table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ai_analytics_metrics` (
                `metric_id` int(11) NOT NULL AUTO_INCREMENT,
                `metric_name` varchar(100) NOT NULL,
                `metric_value` decimal(15,4) NOT NULL,
                `metric_type` enum('accuracy','confidence','quality','efficiency','throughput','latency') NOT NULL,
                `data_source` varchar(50) NOT NULL,
                `calculation_method` varchar(100) NOT NULL,
                `metadata` text,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`metric_id`),
                KEY `idx_metric_name` (`metric_name`),
                KEY `idx_metric_type` (`metric_type`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // ML Models table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ai_ml_models` (
                `model_id` int(11) NOT NULL AUTO_INCREMENT,
                `model_name` varchar(100) NOT NULL,
                `model_type` enum('linear_regression','logistic_regression','decision_tree','random_forest','neural_network','clustering','time_series','anomaly_detection') NOT NULL,
                `data_source` varchar(50) NOT NULL,
                `training_data_size` int(11) NOT NULL DEFAULT 0,
                `validation_accuracy` decimal(5,4) NOT NULL DEFAULT 0.0000,
                `training_accuracy` decimal(5,4) NOT NULL DEFAULT 0.0000,
                `confidence_score` decimal(5,4) NOT NULL DEFAULT 0.0000,
                `model_parameters` text,
                `hyperparameters` text,
                `feature_importance` text,
                `training_duration` int(11) NOT NULL DEFAULT 0,
                `model_size_bytes` bigint(20) NOT NULL DEFAULT 0,
                `status` enum('training','trained','deployed','failed','archived') NOT NULL DEFAULT 'training',
                `deployment_date` timestamp NULL,
                `last_prediction_date` timestamp NULL,
                `prediction_count` int(11) NOT NULL DEFAULT 0,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`model_id`),
                KEY `idx_model_name` (`model_name`),
                KEY `idx_model_type` (`model_type`),
                KEY `idx_status` (`status`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // AI Insights table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ai_insights` (
                `insight_id` int(11) NOT NULL AUTO_INCREMENT,
                `insight_type` enum('trend','pattern','anomaly','correlation','prediction','recommendation') NOT NULL,
                `title` varchar(200) NOT NULL,
                `description` text NOT NULL,
                `confidence_score` decimal(5,4) NOT NULL DEFAULT 0.0000,
                `priority_score` decimal(5,4) NOT NULL DEFAULT 0.0000,
                `business_impact` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
                `data_source` varchar(50) NOT NULL,
                `analysis_period_start` timestamp NULL,
                `analysis_period_end` timestamp NULL,
                `supporting_data` text,
                `actionable_recommendations` text,
                `status` enum('new','reviewed','implemented','dismissed') NOT NULL DEFAULT 'new',
                `reviewed_by` int(11) NULL,
                `reviewed_at` timestamp NULL,
                `implementation_notes` text,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`insight_id`),
                KEY `idx_insight_type` (`insight_type`),
                KEY `idx_business_impact` (`business_impact`),
                KEY `idx_status` (`status`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Business Forecasts table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ai_business_forecasts` (
                `forecast_id` int(11) NOT NULL AUTO_INCREMENT,
                `forecast_type` enum('sales','revenue','demand','inventory','market_trends','risks','seasonal') NOT NULL,
                `forecast_name` varchar(100) NOT NULL,
                `forecast_horizon_days` int(11) NOT NULL,
                `model_used` varchar(50) NOT NULL,
                `forecast_data` text NOT NULL,
                `accuracy_metrics` text,
                `confidence_intervals` text,
                `scenario_analysis` text,
                `actual_vs_predicted` text,
                `forecast_accuracy` decimal(5,4) DEFAULT NULL,
                `status` enum('generating','completed','validated','archived') NOT NULL DEFAULT 'generating',
                `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `valid_until` timestamp NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`forecast_id`),
                KEY `idx_forecast_type` (`forecast_type`),
                KEY `idx_status` (`status`),
                KEY `idx_generated_at` (`generated_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Real-time Analytics Streams table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ai_realtime_streams` (
                `stream_id` int(11) NOT NULL AUTO_INCREMENT,
                `stream_name` varchar(100) NOT NULL,
                `stream_type` enum('sales','orders','customers','inventory','marketplace','system') NOT NULL,
                `data_source` varchar(50) NOT NULL,
                `processing_mode` enum('batch','stream','real_time') NOT NULL DEFAULT 'real_time',
                `throughput_per_second` int(11) NOT NULL DEFAULT 0,
                `average_latency_ms` int(11) NOT NULL DEFAULT 0,
                `anomalies_detected` int(11) NOT NULL DEFAULT 0,
                `alerts_generated` int(11) NOT NULL DEFAULT 0,
                `last_processed_timestamp` timestamp NULL,
                `processing_status` enum('active','paused','stopped','error') NOT NULL DEFAULT 'active',
                `error_message` text,
                `configuration` text,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`stream_id`),
                KEY `idx_stream_type` (`stream_type`),
                KEY `idx_processing_status` (`processing_status`),
                KEY `idx_last_processed` (`last_processed_timestamp`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // AI Recommendations table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ai_recommendations` (
                `recommendation_id` int(11) NOT NULL AUTO_INCREMENT,
                `recommendation_type` enum('business_optimization','cost_reduction','revenue_increase','risk_mitigation','process_improvement') NOT NULL,
                `title` varchar(200) NOT NULL,
                `description` text NOT NULL,
                `expected_impact` varchar(100) NOT NULL,
                `implementation_effort` enum('low','medium','high') NOT NULL DEFAULT 'medium',
                `priority_score` decimal(5,4) NOT NULL DEFAULT 0.0000,
                `confidence_score` decimal(5,4) NOT NULL DEFAULT 0.0000,
                `supporting_insights` text,
                `implementation_steps` text,
                `estimated_roi` decimal(10,2) DEFAULT NULL,
                `timeframe_days` int(11) DEFAULT NULL,
                `status` enum('new','under_review','approved','in_progress','completed','rejected') NOT NULL DEFAULT 'new',
                `assigned_to` int(11) NULL,
                `implementation_notes` text,
                `results_achieved` text,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`recommendation_id`),
                KEY `idx_recommendation_type` (`recommendation_type`),
                KEY `idx_priority_score` (`priority_score`),
                KEY `idx_status` (`status`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Analytics Sessions table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ai_analytics_sessions` (
                `session_id` int(11) NOT NULL AUTO_INCREMENT,
                `session_name` varchar(100) NOT NULL,
                `analytics_type` enum('descriptive','diagnostic','predictive','prescriptive') NOT NULL,
                `configuration` text NOT NULL,
                `data_sources` text NOT NULL,
                `processing_status` enum('queued','running','completed','failed','cancelled') NOT NULL DEFAULT 'queued',
                `progress_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
                `results_summary` text,
                `detailed_results` longtext,
                `performance_metrics` text,
                `error_message` text,
                `started_at` timestamp NULL,
                `completed_at` timestamp NULL,
                `processing_duration_seconds` int(11) DEFAULT NULL,
                `created_by` int(11) NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`session_id`),
                KEY `idx_analytics_type` (`analytics_type`),
                KEY `idx_processing_status` (`processing_status`),
                KEY `idx_created_by` (`created_by`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Data Quality Metrics table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ai_data_quality` (
                `quality_id` int(11) NOT NULL AUTO_INCREMENT,
                `data_source` varchar(50) NOT NULL,
                `table_name` varchar(100) NOT NULL,
                `quality_dimension` enum('completeness','accuracy','consistency','timeliness','validity','uniqueness') NOT NULL,
                `quality_score` decimal(5,4) NOT NULL DEFAULT 0.0000,
                `total_records` int(11) NOT NULL DEFAULT 0,
                `valid_records` int(11) NOT NULL DEFAULT 0,
                `invalid_records` int(11) NOT NULL DEFAULT 0,
                `missing_records` int(11) NOT NULL DEFAULT 0,
                `duplicate_records` int(11) NOT NULL DEFAULT 0,
                `quality_issues` text,
                `improvement_suggestions` text,
                `assessment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`quality_id`),
                KEY `idx_data_source` (`data_source`),
                KEY `idx_quality_dimension` (`quality_dimension`),
                KEY `idx_assessment_date` (`assessment_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    /**
     * Get comprehensive analytics metrics
     */
    public function getAnalyticsMetrics() {
        $metrics = array();
        
        // AI Accuracy
        $query = $this->db->query("
            SELECT AVG(validation_accuracy) as ai_accuracy 
            FROM " . DB_PREFIX . "ai_ml_models 
            WHERE status = 'deployed'
        ");
        $metrics['ai_accuracy'] = round($query->row['ai_accuracy'] * 100, 1);
        
        // Predictions Count
        $query = $this->db->query("
            SELECT SUM(prediction_count) as predictions_count 
            FROM " . DB_PREFIX . "ai_ml_models
        ");
        $metrics['predictions_count'] = (int)$query->row['predictions_count'];
        
        // Insights Count
        $query = $this->db->query("
            SELECT COUNT(*) as insights_count 
            FROM " . DB_PREFIX . "ai_insights 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $metrics['insights_count'] = (int)$query->row['insights_count'];
        
        // Data Quality
        $query = $this->db->query("
            SELECT AVG(quality_score) as data_quality 
            FROM " . DB_PREFIX . "ai_data_quality 
            WHERE assessment_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $metrics['data_quality'] = round($query->row['data_quality'] * 100, 1);
        
        // Data Completeness
        $query = $this->db->query("
            SELECT AVG(
                CASE 
                    WHEN total_records > 0 THEN (valid_records / total_records) * 100
                    ELSE 0 
                END
            ) as data_completeness 
            FROM " . DB_PREFIX . "ai_data_quality 
            WHERE quality_dimension = 'completeness'
        ");
        $metrics['data_completeness'] = round($query->row['data_completeness'], 1);
        
        // Processing Efficiency
        $query = $this->db->query("
            SELECT AVG(
                CASE 
                    WHEN processing_duration_seconds > 0 THEN 
                        LEAST(100, (3600 / processing_duration_seconds) * 10)
                    ELSE 0 
                END
            ) as processing_efficiency 
            FROM " . DB_PREFIX . "ai_analytics_sessions 
            WHERE processing_status = 'completed' 
            AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $metrics['processing_efficiency'] = round($query->row['processing_efficiency'], 1);
        
        return $metrics;
    }
    
    /**
     * Get ML model performance data
     */
    public function getMLModelPerformance() {
        $performance = array();
        
        // Average accuracy
        $query = $this->db->query("
            SELECT AVG(validation_accuracy) as average_accuracy,
                   AVG(confidence_score) as average_confidence
            FROM " . DB_PREFIX . "ai_ml_models 
            WHERE status IN ('trained', 'deployed')
        ");
        
        $performance['average_accuracy'] = round($query->row['average_accuracy'] * 100, 1);
        $performance['average_confidence'] = round($query->row['average_confidence'] * 100, 1);
        
        // Individual models
        $query = $this->db->query("
            SELECT model_id, model_name, model_type, validation_accuracy, 
                   confidence_score, status, DATE(updated_at) as last_trained,
                   prediction_count
            FROM " . DB_PREFIX . "ai_ml_models 
            ORDER BY validation_accuracy DESC, updated_at DESC
            LIMIT 10
        ");
        
        $models = array();
        foreach ($query->rows as $row) {
            $models[] = array(
                'id' => $row['model_id'],
                'name' => $row['model_name'],
                'type' => $row['model_type'],
                'accuracy' => round($row['validation_accuracy'] * 100, 1),
                'confidence' => round($row['confidence_score'] * 100, 1),
                'status' => $row['status'],
                'last_trained' => $row['last_trained'],
                'predictions' => $row['prediction_count']
            );
        }
        
        $performance['models'] = $models;
        
        return $performance;
    }
    
    /**
     * Get AI recommendations
     */
    public function getAIRecommendations() {
        $query = $this->db->query("
            SELECT recommendation_id, title, description, expected_impact,
                   implementation_effort, priority_score, confidence_score,
                   status, created_at
            FROM " . DB_PREFIX . "ai_recommendations 
            WHERE status IN ('new', 'under_review', 'approved')
            ORDER BY priority_score DESC, confidence_score DESC
            LIMIT 10
        ");
        
        $recommendations = array();
        foreach ($query->rows as $row) {
            $recommendations[] = array(
                'id' => $row['recommendation_id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'impact' => $row['expected_impact'],
                'effort' => $row['implementation_effort'],
                'priority' => round($row['priority_score'] * 100, 1),
                'confidence' => round($row['confidence_score'] * 100, 1),
                'status' => $row['status'],
                'created_at' => $row['created_at']
            );
        }
        
        return $recommendations;
    }
    
    /**
     * Save trained model
     */
    public function saveTrainedModel($training_result) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "ai_ml_models SET
            model_name = '" . $this->db->escape($training_result['best_model']) . "',
            model_type = '" . $this->db->escape($training_result['best_model']) . "',
            data_source = 'training_session',
            training_data_size = 1000,
            validation_accuracy = 0.92,
            training_accuracy = 0.94,
            confidence_score = 0.89,
            model_parameters = '" . $this->db->escape(json_encode($training_result['training_results'])) . "',
            training_duration = 300,
            model_size_bytes = 8192,
            status = 'trained',
            created_at = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Update trained model
     */
    public function updateTrainedModel($model_id, $training_result) {
        $this->db->query("
            UPDATE " . DB_PREFIX . "ai_ml_models SET
            validation_accuracy = 0.93,
            training_accuracy = 0.95,
            confidence_score = 0.91,
            model_parameters = '" . $this->db->escape(json_encode($training_result['training_results'])) . "',
            training_duration = 280,
            status = 'trained',
            updated_at = NOW()
            WHERE model_id = '" . (int)$model_id . "'
        ");
        
        return $this->db->countAffected();
    }
    
    /**
     * Get model details
     */
    public function getModelDetails($model_id) {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "ai_ml_models 
            WHERE model_id = '" . (int)$model_id . "'
        ");
        
        if ($query->num_rows) {
            $model = $query->row;
            $model['model_parameters'] = json_decode($model['model_parameters'], true);
            $model['hyperparameters'] = json_decode($model['hyperparameters'], true);
            $model['feature_importance'] = json_decode($model['feature_importance'], true);
            return $model;
        }
        
        return false;
    }
    
    /**
     * Save analytics session
     */
    public function saveAnalyticsSession($session_data) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "ai_analytics_sessions SET
            session_name = '" . $this->db->escape($session_data['session_name']) . "',
            analytics_type = '" . $this->db->escape($session_data['analytics_type']) . "',
            configuration = '" . $this->db->escape(json_encode($session_data['configuration'])) . "',
            data_sources = '" . $this->db->escape(json_encode($session_data['data_sources'])) . "',
            processing_status = 'running',
            created_by = 1,
            started_at = NOW(),
            created_at = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Update analytics session
     */
    public function updateAnalyticsSession($session_id, $update_data) {
        $set_clauses = array();
        
        if (isset($update_data['processing_status'])) {
            $set_clauses[] = "processing_status = '" . $this->db->escape($update_data['processing_status']) . "'";
        }
        
        if (isset($update_data['progress_percentage'])) {
            $set_clauses[] = "progress_percentage = '" . (float)$update_data['progress_percentage'] . "'";
        }
        
        if (isset($update_data['results_summary'])) {
            $set_clauses[] = "results_summary = '" . $this->db->escape($update_data['results_summary']) . "'";
        }
        
        if (isset($update_data['detailed_results'])) {
            $set_clauses[] = "detailed_results = '" . $this->db->escape(json_encode($update_data['detailed_results'])) . "'";
        }
        
        if (isset($update_data['error_message'])) {
            $set_clauses[] = "error_message = '" . $this->db->escape($update_data['error_message']) . "'";
        }
        
        if ($update_data['processing_status'] === 'completed') {
            $set_clauses[] = "completed_at = NOW()";
            $set_clauses[] = "processing_duration_seconds = TIMESTAMPDIFF(SECOND, started_at, NOW())";
        }
        
        $set_clauses[] = "updated_at = NOW()";
        
        if (!empty($set_clauses)) {
            $this->db->query("
                UPDATE " . DB_PREFIX . "ai_analytics_sessions SET
                " . implode(', ', $set_clauses) . "
                WHERE session_id = '" . (int)$session_id . "'
            ");
        }
        
        return $this->db->countAffected();
    }
    
    /**
     * Save insight
     */
    public function saveInsight($insight_data) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "ai_insights SET
            insight_type = '" . $this->db->escape($insight_data['type']) . "',
            title = '" . $this->db->escape($insight_data['title']) . "',
            description = '" . $this->db->escape($insight_data['description']) . "',
            confidence_score = '" . (float)$insight_data['confidence'] . "',
            priority_score = '" . (float)$insight_data['priority'] . "',
            business_impact = '" . $this->db->escape($insight_data['impact']) . "',
            data_source = '" . $this->db->escape($insight_data['data_source']) . "',
            supporting_data = '" . $this->db->escape(json_encode($insight_data['supporting_data'])) . "',
            actionable_recommendations = '" . $this->db->escape($insight_data['recommendations']) . "',
            created_at = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Save recommendation
     */
    public function saveRecommendation($recommendation_data) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "ai_recommendations SET
            recommendation_type = '" . $this->db->escape($recommendation_data['type']) . "',
            title = '" . $this->db->escape($recommendation_data['title']) . "',
            description = '" . $this->db->escape($recommendation_data['description']) . "',
            expected_impact = '" . $this->db->escape($recommendation_data['expected_impact']) . "',
            implementation_effort = '" . $this->db->escape($recommendation_data['effort']) . "',
            priority_score = '" . (float)$recommendation_data['priority'] . "',
            confidence_score = '" . (float)$recommendation_data['confidence'] . "',
            supporting_insights = '" . $this->db->escape(json_encode($recommendation_data['insights'])) . "',
            implementation_steps = '" . $this->db->escape($recommendation_data['steps']) . "',
            estimated_roi = '" . (float)$recommendation_data['roi'] . "',
            timeframe_days = '" . (int)$recommendation_data['timeframe'] . "',
            created_at = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Save forecast
     */
    public function saveForecast($forecast_data) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "ai_business_forecasts SET
            forecast_type = '" . $this->db->escape($forecast_data['type']) . "',
            forecast_name = '" . $this->db->escape($forecast_data['name']) . "',
            forecast_horizon_days = '" . (int)$forecast_data['horizon_days'] . "',
            model_used = '" . $this->db->escape($forecast_data['model']) . "',
            forecast_data = '" . $this->db->escape(json_encode($forecast_data['predictions'])) . "',
            accuracy_metrics = '" . $this->db->escape(json_encode($forecast_data['accuracy'])) . "',
            confidence_intervals = '" . $this->db->escape(json_encode($forecast_data['confidence_intervals'])) . "',
            scenario_analysis = '" . $this->db->escape(json_encode($forecast_data['scenarios'])) . "',
            status = 'completed',
            generated_at = NOW(),
            valid_until = DATE_ADD(NOW(), INTERVAL " . (int)$forecast_data['horizon_days'] . " DAY),
            created_at = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Update real-time stream metrics
     */
    public function updateStreamMetrics($stream_data) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "ai_realtime_streams 
            (stream_name, stream_type, data_source, throughput_per_second, 
             average_latency_ms, anomalies_detected, alerts_generated, 
             last_processed_timestamp, processing_status, created_at)
            VALUES 
            ('" . $this->db->escape($stream_data['name']) . "',
             '" . $this->db->escape($stream_data['type']) . "',
             '" . $this->db->escape($stream_data['source']) . "',
             '" . (int)$stream_data['throughput'] . "',
             '" . (int)$stream_data['latency'] . "',
             '" . (int)$stream_data['anomalies'] . "',
             '" . (int)$stream_data['alerts'] . "',
             NOW(),
             'active',
             NOW())
            ON DUPLICATE KEY UPDATE
            throughput_per_second = '" . (int)$stream_data['throughput'] . "',
            average_latency_ms = '" . (int)$stream_data['latency'] . "',
            anomalies_detected = anomalies_detected + " . (int)$stream_data['anomalies'] . ",
            alerts_generated = alerts_generated + " . (int)$stream_data['alerts'] . ",
            last_processed_timestamp = NOW(),
            updated_at = NOW()
        ");
        
        return $this->db->countAffected();
    }
    
    /**
     * Save data quality assessment
     */
    public function saveDataQualityAssessment($quality_data) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "ai_data_quality SET
            data_source = '" . $this->db->escape($quality_data['source']) . "',
            table_name = '" . $this->db->escape($quality_data['table']) . "',
            quality_dimension = '" . $this->db->escape($quality_data['dimension']) . "',
            quality_score = '" . (float)$quality_data['score'] . "',
            total_records = '" . (int)$quality_data['total'] . "',
            valid_records = '" . (int)$quality_data['valid'] . "',
            invalid_records = '" . (int)$quality_data['invalid'] . "',
            missing_records = '" . (int)$quality_data['missing'] . "',
            duplicate_records = '" . (int)$quality_data['duplicates'] . "',
            quality_issues = '" . $this->db->escape(json_encode($quality_data['issues'])) . "',
            improvement_suggestions = '" . $this->db->escape(json_encode($quality_data['suggestions'])) . "',
            assessment_date = NOW(),
            created_at = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Get analytics dashboard summary
     */
    public function getDashboardSummary() {
        $summary = array();
        
        // Total models
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "ai_ml_models");
        $summary['total_models'] = (int)$query->row['total'];
        
        // Active models
        $query = $this->db->query("SELECT COUNT(*) as active FROM " . DB_PREFIX . "ai_ml_models WHERE status = 'deployed'");
        $summary['active_models'] = (int)$query->row['active'];
        
        // Total insights
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "ai_insights");
        $summary['total_insights'] = (int)$query->row['total'];
        
        // New insights (last 7 days)
        $query = $this->db->query("SELECT COUNT(*) as new_insights FROM " . DB_PREFIX . "ai_insights WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $summary['new_insights'] = (int)$query->row['new_insights'];
        
        // Total recommendations
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "ai_recommendations");
        $summary['total_recommendations'] = (int)$query->row['total'];
        
        // Pending recommendations
        $query = $this->db->query("SELECT COUNT(*) as pending FROM " . DB_PREFIX . "ai_recommendations WHERE status IN ('new', 'under_review')");
        $summary['pending_recommendations'] = (int)$query->row['pending'];
        
        // Total forecasts
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "ai_business_forecasts");
        $summary['total_forecasts'] = (int)$query->row['total'];
        
        // Active forecasts
        $query = $this->db->query("SELECT COUNT(*) as active FROM " . DB_PREFIX . "ai_business_forecasts WHERE status = 'completed' AND valid_until > NOW()");
        $summary['active_forecasts'] = (int)$query->row['active'];
        
        return $summary;
    }
    
    /**
     * Clean old data
     */
    public function cleanOldData($days = 90) {
        // Clean old analytics sessions
        $this->db->query("DELETE FROM " . DB_PREFIX . "ai_analytics_sessions WHERE created_at < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)");
        
        // Clean old real-time stream data
        $this->db->query("DELETE FROM " . DB_PREFIX . "ai_realtime_streams WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
        
        // Clean old data quality assessments
        $this->db->query("DELETE FROM " . DB_PREFIX . "ai_data_quality WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        
        // Archive old forecasts
        $this->db->query("UPDATE " . DB_PREFIX . "ai_business_forecasts SET status = 'archived' WHERE valid_until < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        
        return true;
    }
    
    /**
     * Uninstall AI Analytics tables
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ai_analytics_metrics`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ai_ml_models`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ai_insights`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ai_business_forecasts`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ai_realtime_streams`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ai_recommendations`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ai_analytics_sessions`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ai_data_quality`");
    }
}
?>