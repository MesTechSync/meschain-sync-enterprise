<?php
/**
 * AI-Powered Analytics Model
 * MesChain-Sync v4.0 - AI-Powered Analytics Model
 * Machine Learning & Predictive Analytics
 * 
 * @author MesChain Development Team
 * @version 4.0.0
 * @copyright 2024 MesChain Technologies
 */

class ModelExtensionModuleAiPoweredAnalytics extends Model {

    /**
     * Install AI analytics tables
     */
    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ai_models` (
                `model_id` int(11) NOT NULL AUTO_INCREMENT,
                `model_name` varchar(255) NOT NULL,
                `model_type` varchar(100) NOT NULL,
                `model_version` varchar(50) DEFAULT '1.0',
                `description` text,
                `algorithm` varchar(100) DEFAULT NULL,
                `accuracy_score` decimal(5,4) DEFAULT NULL,
                `precision_score` decimal(5,4) DEFAULT NULL,
                `recall_score` decimal(5,4) DEFAULT NULL,
                `f1_score` decimal(5,4) DEFAULT NULL,
                `training_data_size` int(11) DEFAULT NULL,
                `features_count` int(11) DEFAULT NULL,
                `hyperparameters` json DEFAULT NULL,
                `model_file_path` varchar(500) DEFAULT NULL,
                `model_size_mb` decimal(10,2) DEFAULT NULL,
                `training_duration` int(11) DEFAULT NULL,
                `last_trained` datetime DEFAULT NULL,
                `status` enum('training','ready','deployed','deprecated','error') DEFAULT 'training',
                `performance_metrics` json DEFAULT NULL,
                `validation_results` json DEFAULT NULL,
                `configuration` json DEFAULT NULL,
                `created_by` int(11) DEFAULT NULL,
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`model_id`),
                UNIQUE KEY `unique_model_name_version` (`model_name`, `model_version`),
                KEY `idx_model_type` (`model_type`),
                KEY `idx_status` (`status`),
                KEY `idx_accuracy_score` (`accuracy_score`),
                KEY `idx_last_trained` (`last_trained`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ai_predictions` (
                `prediction_id` int(11) NOT NULL AUTO_INCREMENT,
                `model_id` int(11) NOT NULL,
                `prediction_type` varchar(100) NOT NULL,
                `entity_type` varchar(100) NOT NULL,
                `entity_id` varchar(255) NOT NULL,
                `prediction_date` datetime NOT NULL,
                `prediction_period` varchar(100) DEFAULT NULL,
                `predicted_value` decimal(15,4) DEFAULT NULL,
                `predicted_category` varchar(255) DEFAULT NULL,
                `predicted_probability` decimal(5,4) DEFAULT NULL,
                `confidence_score` decimal(5,4) DEFAULT NULL,
                `prediction_data` json DEFAULT NULL,
                `features_used` json DEFAULT NULL,
                `actual_value` decimal(15,4) DEFAULT NULL,
                `actual_category` varchar(255) DEFAULT NULL,
                `accuracy_measured` decimal(5,4) DEFAULT NULL,
                `error_rate` decimal(5,4) DEFAULT NULL,
                `prediction_status` enum('pending','validated','expired','error') DEFAULT 'pending',
                `marketplace` varchar(100) DEFAULT NULL,
                `category_id` int(11) DEFAULT NULL,
                `product_id` int(11) DEFAULT NULL,
                `customer_id` int(11) DEFAULT NULL,
                `validation_date` datetime DEFAULT NULL,
                `feedback_score` decimal(5,2) DEFAULT NULL,
                `business_impact` decimal(15,4) DEFAULT NULL,
                `recommended_actions` text,
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`prediction_id`),
                KEY `idx_model_id` (`model_id`),
                KEY `idx_prediction_type` (`prediction_type`),
                KEY `idx_entity_type_id` (`entity_type`, `entity_id`),
                KEY `idx_prediction_date` (`prediction_date`),
                KEY `idx_marketplace` (`marketplace`),
                KEY `idx_confidence_score` (`confidence_score`),
                KEY `idx_prediction_status` (`prediction_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ai_insights` (
                `insight_id` int(11) NOT NULL AUTO_INCREMENT,
                `insight_type` varchar(100) NOT NULL,
                `insight_category` varchar(100) NOT NULL,
                `title` varchar(255) NOT NULL,
                `description` text NOT NULL,
                `severity` enum('low','medium','high','critical') DEFAULT 'medium',
                `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
                `confidence_level` decimal(5,4) DEFAULT NULL,
                `data_sources` json DEFAULT NULL,
                `affected_entities` json DEFAULT NULL,
                `metrics_data` json DEFAULT NULL,
                `recommendations` text,
                `action_items` json DEFAULT NULL,
                `expected_impact` text,
                `potential_revenue` decimal(15,4) DEFAULT NULL,
                `risk_assessment` text,
                `timeframe` varchar(100) DEFAULT NULL,
                `auto_generated` tinyint(1) DEFAULT 1,
                `ai_model_used` varchar(255) DEFAULT NULL,
                `reviewed_by` int(11) DEFAULT NULL,
                `review_date` datetime DEFAULT NULL,
                `review_status` enum('pending','approved','rejected','implemented') DEFAULT 'pending',
                `implementation_status` enum('not_started','in_progress','completed','cancelled') DEFAULT 'not_started',
                `implementation_date` datetime DEFAULT NULL,
                `results_measured` json DEFAULT NULL,
                `feedback_rating` decimal(3,2) DEFAULT NULL,
                `tags` varchar(500) DEFAULT NULL,
                `visibility` enum('public','private','team','admin') DEFAULT 'team',
                `expiry_date` datetime DEFAULT NULL,
                `notification_sent` tinyint(1) DEFAULT 0,
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`insight_id`),
                KEY `idx_insight_type` (`insight_type`),
                KEY `idx_insight_category` (`insight_category`),
                KEY `idx_severity` (`severity`),
                KEY `idx_priority` (`priority`),
                KEY `idx_confidence_level` (`confidence_level`),
                KEY `idx_review_status` (`review_status`),
                KEY `idx_implementation_status` (`implementation_status`),
                KEY `idx_expiry_date` (`expiry_date`),
                KEY `idx_auto_generated` (`auto_generated`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ai_training_jobs` (
                `job_id` int(11) NOT NULL AUTO_INCREMENT,
                `model_id` int(11) NOT NULL,
                `job_name` varchar(255) NOT NULL,
                `job_type` enum('training','retraining','validation','inference','batch_prediction') DEFAULT 'training',
                `dataset_source` varchar(255) DEFAULT NULL,
                `dataset_size` int(11) DEFAULT NULL,
                `training_parameters` json DEFAULT NULL,
                `resource_requirements` json DEFAULT NULL,
                `start_time` datetime DEFAULT NULL,
                `end_time` datetime DEFAULT NULL,
                `duration_seconds` int(11) DEFAULT NULL,
                `status` enum('queued','running','completed','failed','cancelled','paused') DEFAULT 'queued',
                `progress_percentage` decimal(5,2) DEFAULT 0,
                `current_epoch` int(11) DEFAULT 0,
                `total_epochs` int(11) DEFAULT 0,
                `loss_value` decimal(15,8) DEFAULT NULL,
                `accuracy_value` decimal(5,4) DEFAULT NULL,
                `validation_loss` decimal(15,8) DEFAULT NULL,
                `validation_accuracy` decimal(5,4) DEFAULT NULL,
                `cpu_usage` decimal(5,2) DEFAULT NULL,
                `memory_usage` decimal(5,2) DEFAULT NULL,
                `gpu_usage` decimal(5,2) DEFAULT NULL,
                `logs` longtext,
                `error_message` text,
                `output_data` json DEFAULT NULL,
                `model_artifacts` json DEFAULT NULL,
                `created_by` int(11) DEFAULT NULL,
                `notification_settings` json DEFAULT NULL,
                `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`job_id`),
                KEY `idx_model_id` (`model_id`),
                KEY `idx_job_type` (`job_type`),
                KEY `idx_status` (`status`),
                KEY `idx_start_time` (`start_time`),
                KEY `idx_priority` (`priority`),
                KEY `idx_created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * Get AI models
     */
    public function getModels($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "ai_models`";
        
        $conditions = array();
        
        if (!empty($data['filter_type'])) {
            $conditions[] = "model_type = '" . $this->db->escape($data['filter_type']) . "'";
        }
        
        if (!empty($data['filter_status'])) {
            $conditions[] = "status = '" . $this->db->escape($data['filter_status']) . "'";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY accuracy_score DESC, date_added DESC";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
     * Get AI model by ID
     */
    public function getModel($model_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "ai_models` 
            WHERE model_id = '" . (int)$model_id . "'
        ");
        
        return $query->row;
    }

    /**
     * Save AI model
     */
    public function saveModel($model_id, $data) {
        if ($model_id) {
            $sql = "UPDATE `" . DB_PREFIX . "ai_models` SET ";
            $update_data = array();
            
            foreach ($data as $key => $value) {
                if ($key !== 'date_added' && $key !== 'model_id') {
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    }
                    $update_data[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
                }
            }
            
            $sql .= implode(", ", $update_data);
            $sql .= " WHERE model_id = '" . (int)$model_id . "'";
            
            $this->db->query($sql);
        } else {
            $data['date_added'] = date('Y-m-d H:i:s');
            
            $sql = "INSERT INTO `" . DB_PREFIX . "ai_models` SET ";
            $insert_data = array();
            
            foreach ($data as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                $insert_data[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
            }
            
            $sql .= implode(", ", $insert_data);
            
            $this->db->query($sql);
            
            return $this->db->getLastId();
        }
    }

    /**
     * Get predictions
     */
    public function getPredictions($data = array()) {
        $sql = "SELECT p.*, m.model_name, m.model_type 
                FROM `" . DB_PREFIX . "ai_predictions` p
                LEFT JOIN `" . DB_PREFIX . "ai_models` m ON (p.model_id = m.model_id)";
        
        $conditions = array();
        
        if (!empty($data['filter_type'])) {
            $conditions[] = "p.prediction_type = '" . $this->db->escape($data['filter_type']) . "'";
        }
        
        if (!empty($data['filter_marketplace'])) {
            $conditions[] = "p.marketplace = '" . $this->db->escape($data['filter_marketplace']) . "'";
        }
        
        if (!empty($data['filter_status'])) {
            $conditions[] = "p.prediction_status = '" . $this->db->escape($data['filter_status']) . "'";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY p.confidence_score DESC, p.prediction_date DESC";

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
     * Save prediction
     */
    public function savePrediction($data) {
        $data['date_added'] = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO `" . DB_PREFIX . "ai_predictions` SET ";
        $insert_data = array();
        
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            $insert_data[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
        }
        
        $sql .= implode(", ", $insert_data);
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }

    /**
     * Get insights
     */
    public function getInsights($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "ai_insights`";
        
        $conditions = array();
        
        if (!empty($data['filter_type'])) {
            $conditions[] = "insight_type = '" . $this->db->escape($data['filter_type']) . "'";
        }
        
        if (!empty($data['filter_category'])) {
            $conditions[] = "insight_category = '" . $this->db->escape($data['filter_category']) . "'";
        }
        
        if (!empty($data['filter_severity'])) {
            $conditions[] = "severity = '" . $this->db->escape($data['filter_severity']) . "'";
        }
        
        if (!empty($data['filter_status'])) {
            $conditions[] = "review_status = '" . $this->db->escape($data['filter_status']) . "'";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY priority DESC, confidence_level DESC, date_added DESC";

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
     * Save insight
     */
    public function saveInsight($data) {
        $data['date_added'] = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO `" . DB_PREFIX . "ai_insights` SET ";
        $insert_data = array();
        
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            $insert_data[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
        }
        
        $sql .= implode(", ", $insert_data);
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }

    /**
     * Get training jobs
     */
    public function getTrainingJobs($data = array()) {
        $sql = "SELECT j.*, m.model_name, m.model_type 
                FROM `" . DB_PREFIX . "ai_training_jobs` j
                LEFT JOIN `" . DB_PREFIX . "ai_models` m ON (j.model_id = m.model_id)";
        
        $conditions = array();
        
        if (!empty($data['filter_status'])) {
            $conditions[] = "j.status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_type'])) {
            $conditions[] = "j.job_type = '" . $this->db->escape($data['filter_type']) . "'";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY j.date_added DESC";

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
     * Create training job
     */
    public function createTrainingJob($data) {
        $data['date_added'] = date('Y-m-d H:i:s');
        $data['status'] = 'queued';
        
        $sql = "INSERT INTO `" . DB_PREFIX . "ai_training_jobs` SET ";
        $insert_data = array();
        
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            $insert_data[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
        }
        
        $sql .= implode(", ", $insert_data);
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }

    /**
     * Update training job status
     */
    public function updateTrainingJob($job_id, $data) {
        $data['date_modified'] = date('Y-m-d H:i:s');
        
        $sql = "UPDATE `" . DB_PREFIX . "ai_training_jobs` SET ";
        $update_data = array();
        
        foreach ($data as $key => $value) {
            if ($key !== 'date_added' && $key !== 'job_id') {
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                $update_data[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
            }
        }
        
        $sql .= implode(", ", $update_data);
        $sql .= " WHERE job_id = '" . (int)$job_id . "'";
        
        $this->db->query($sql);
    }

    /**
     * Get model performance metrics
     */
    public function getModelPerformance($model_id) {
        $query = $this->db->query("
            SELECT 
                accuracy_score,
                precision_score,
                recall_score,
                f1_score,
                performance_metrics,
                validation_results
            FROM `" . DB_PREFIX . "ai_models` 
            WHERE model_id = '" . (int)$model_id . "'
        ");
        
        return $query->row;
    }

    /**
     * Get prediction accuracy statistics
     */
    public function getPredictionAccuracy($model_id = null, $days = 30) {
        $sql = "
            SELECT 
                prediction_type,
                COUNT(*) as total_predictions,
                AVG(confidence_score) as avg_confidence,
                AVG(accuracy_measured) as avg_accuracy,
                AVG(error_rate) as avg_error_rate
            FROM `" . DB_PREFIX . "ai_predictions` 
            WHERE prediction_date >= DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            AND accuracy_measured IS NOT NULL
        ";
        
        if ($model_id) {
            $sql .= " AND model_id = '" . (int)$model_id . "'";
        }
        
        $sql .= " GROUP BY prediction_type ORDER BY avg_accuracy DESC";
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $stats = array();
        
        // Model statistics
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ai_models`");
        $stats['total_models'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as deployed FROM `" . DB_PREFIX . "ai_models` WHERE status = 'deployed'");
        $stats['deployed_models'] = $query->row['deployed'];
        
        // Prediction statistics
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ai_predictions` WHERE prediction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['monthly_predictions'] = $query->row['total'];
        
        $query = $this->db->query("SELECT AVG(confidence_score) as avg_confidence FROM `" . DB_PREFIX . "ai_predictions` WHERE prediction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['avg_confidence'] = round($query->row['avg_confidence'] ?? 0, 4);
        
        // Insight statistics
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ai_insights` WHERE date_added >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['monthly_insights'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as high_priority FROM `" . DB_PREFIX . "ai_insights` WHERE priority IN ('high', 'urgent') AND review_status = 'pending'");
        $stats['high_priority_insights'] = $query->row['high_priority'];
        
        // Training job statistics
        $query = $this->db->query("SELECT COUNT(*) as running FROM `" . DB_PREFIX . "ai_training_jobs` WHERE status = 'running'");
        $stats['running_jobs'] = $query->row['running'];
        
        $query = $this->db->query("SELECT COUNT(*) as completed FROM `" . DB_PREFIX . "ai_training_jobs` WHERE status = 'completed' AND DATE(date_added) = CURDATE()");
        $stats['daily_completed_jobs'] = $query->row['completed'];
        
        return $stats;
    }
} 