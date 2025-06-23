<?php
/**
 * MesChain AI Analytics Installer
 * Database installer for AI Analytics system
 * 
 * @category   MesChain
 * @package    AI Analytics Helper
 * @author     MesChain Development Team
 * @copyright  2025 MesChain
 * @license    https://meschain.com/license
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

class MesChainAIInstaller {
    
    private $registry;
    private $db;
    private $log;
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->log = new Log('meschain_ai_installer.log');
    }
    
    /**
     * Install AI Analytics Tables
     */
    public function install() {
        try {
            // Create AI Predictions table
            $this->createAIPredictionsTable();
            
            // Create AI Models table
            $this->createAIModelsTable();
            
            // Create AI Training Data table
            $this->createAITrainingDataTable();
            
            // Create AI Feature Store table
            $this->createAIFeatureStoreTable();
            
            // Create AI Model Performance table
            $this->createAIModelPerformanceTable();
            
            // Create AI Anomalies table
            $this->createAIAnomaliesTable();
            
            // Create AI Recommendations table
            $this->createAIRecommendationsTable();
            
            // Insert initial data
            $this->insertInitialData();
            
            $this->log->write('AI Analytics tables installed successfully');
            
            return [
                'success' => true,
                'message' => 'AI Analytics system installed successfully'
            ];
            
        } catch (Exception $e) {
            $this->log->write('AI Analytics installation error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Uninstall AI Analytics Tables
     */
    public function uninstall() {
        try {
            $tables = [
                'meschain_ai_predictions',
                'meschain_ai_models',
                'meschain_ai_training_data',
                'meschain_ai_feature_store',
                'meschain_ai_model_performance',
                'meschain_ai_anomalies',
                'meschain_ai_recommendations'
            ];
            
            foreach ($tables as $table) {
                $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . $table);
            }
            
            $this->log->write('AI Analytics tables uninstalled successfully');
            
            return [
                'success' => true,
                'message' => 'AI Analytics system uninstalled successfully'
            ];
            
        } catch (Exception $e) {
            $this->log->write('AI Analytics uninstallation error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create AI Predictions table
     */
    private function createAIPredictionsTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "meschain_ai_predictions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                prediction_type VARCHAR(100) NOT NULL,
                context VARCHAR(255) DEFAULT NULL,
                input_data JSON DEFAULT NULL,
                result_data JSON DEFAULT NULL,
                accuracy_score DECIMAL(5,4) DEFAULT NULL,
                confidence_level DECIMAL(5,4) DEFAULT NULL,
                model_version VARCHAR(50) DEFAULT NULL,
                execution_time_ms INT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_prediction_type (prediction_type),
                INDEX idx_context (context),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create AI Models table
     */
    private function createAIModelsTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "meschain_ai_models (
                id INT AUTO_INCREMENT PRIMARY KEY,
                model_name VARCHAR(100) NOT NULL UNIQUE,
                model_type VARCHAR(50) NOT NULL,
                model_description TEXT DEFAULT NULL,
                algorithm VARCHAR(100) DEFAULT NULL,
                hyperparameters JSON DEFAULT NULL,
                training_data_size INT DEFAULT NULL,
                accuracy_score DECIMAL(5,4) DEFAULT NULL,
                precision_score DECIMAL(5,4) DEFAULT NULL,
                recall_score DECIMAL(5,4) DEFAULT NULL,
                f1_score DECIMAL(5,4) DEFAULT NULL,
                model_file_path VARCHAR(500) DEFAULT NULL,
                model_status ENUM('training', 'active', 'deprecated', 'error') DEFAULT 'training',
                version VARCHAR(20) DEFAULT '1.0.0',
                last_trained_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_model_name (model_name),
                INDEX idx_model_type (model_type),
                INDEX idx_status (model_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create AI Training Data table
     */
    private function createAITrainingDataTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "meschain_ai_training_data (
                id INT AUTO_INCREMENT PRIMARY KEY,
                data_source VARCHAR(100) NOT NULL,
                data_type VARCHAR(50) NOT NULL,
                feature_vector JSON DEFAULT NULL,
                target_value JSON DEFAULT NULL,
                data_quality_score DECIMAL(5,4) DEFAULT NULL,
                is_labeled BOOLEAN DEFAULT FALSE,
                validation_set BOOLEAN DEFAULT FALSE,
                marketplace VARCHAR(50) DEFAULT NULL,
                product_id INT DEFAULT NULL,
                order_id INT DEFAULT NULL,
                data_hash VARCHAR(64) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_data_source (data_source),
                INDEX idx_data_type (data_type),
                INDEX idx_marketplace (marketplace),
                INDEX idx_product_id (product_id),
                INDEX idx_data_hash (data_hash)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create AI Feature Store table
     */
    private function createAIFeatureStoreTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "meschain_ai_feature_store (
                id INT AUTO_INCREMENT PRIMARY KEY,
                feature_name VARCHAR(100) NOT NULL,
                feature_group VARCHAR(50) NOT NULL,
                feature_description TEXT DEFAULT NULL,
                data_type ENUM('numeric', 'categorical', 'text', 'datetime', 'boolean') NOT NULL,
                feature_value TEXT DEFAULT NULL,
                entity_id VARCHAR(100) NOT NULL,
                entity_type VARCHAR(50) NOT NULL,
                feature_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                feature_version INT DEFAULT 1,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_feature_name (feature_name),
                INDEX idx_feature_group (feature_group),
                INDEX idx_entity_id (entity_id),
                INDEX idx_entity_type (entity_type),
                INDEX idx_feature_timestamp (feature_timestamp)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create AI Model Performance table
     */
    private function createAIModelPerformanceTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "meschain_ai_model_performance (
                id INT AUTO_INCREMENT PRIMARY KEY,
                model_name VARCHAR(100) NOT NULL,
                metric_name VARCHAR(50) NOT NULL,
                metric_value DECIMAL(10,6) NOT NULL,
                evaluation_date DATE NOT NULL,
                test_data_size INT DEFAULT NULL,
                evaluation_context VARCHAR(255) DEFAULT NULL,
                benchmark_comparison JSON DEFAULT NULL,
                performance_notes TEXT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_model_name (model_name),
                INDEX idx_metric_name (metric_name),
                INDEX idx_evaluation_date (evaluation_date),
                FOREIGN KEY (model_name) REFERENCES " . DB_PREFIX . "meschain_ai_models(model_name) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create AI Anomalies table
     */
    private function createAIAnomaliesTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "meschain_ai_anomalies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                anomaly_type VARCHAR(100) NOT NULL,
                severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
                entity_type VARCHAR(50) NOT NULL,
                entity_id VARCHAR(100) NOT NULL,
                metric_name VARCHAR(100) NOT NULL,
                expected_value DECIMAL(15,4) DEFAULT NULL,
                actual_value DECIMAL(15,4) DEFAULT NULL,
                anomaly_score DECIMAL(5,4) NOT NULL,
                detection_method VARCHAR(100) NOT NULL,
                anomaly_description TEXT DEFAULT NULL,
                context_data JSON DEFAULT NULL,
                is_resolved BOOLEAN DEFAULT FALSE,
                resolved_at TIMESTAMP NULL DEFAULT NULL,
                resolution_notes TEXT DEFAULT NULL,
                detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_anomaly_type (anomaly_type),
                INDEX idx_severity (severity),
                INDEX idx_entity_type (entity_type),
                INDEX idx_entity_id (entity_id),
                INDEX idx_detected_at (detected_at),
                INDEX idx_is_resolved (is_resolved)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create AI Recommendations table
     */
    private function createAIRecommendationsTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "meschain_ai_recommendations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                recommendation_type VARCHAR(100) NOT NULL,
                target_entity_type VARCHAR(50) NOT NULL,
                target_entity_id VARCHAR(100) NOT NULL,
                recommended_entity_type VARCHAR(50) NOT NULL,
                recommended_entity_id VARCHAR(100) NOT NULL,
                confidence_score DECIMAL(5,4) NOT NULL,
                relevance_score DECIMAL(5,4) DEFAULT NULL,
                recommendation_context VARCHAR(255) DEFAULT NULL,
                algorithm_used VARCHAR(100) DEFAULT NULL,
                explanation TEXT DEFAULT NULL,
                recommendation_data JSON DEFAULT NULL,
                is_active BOOLEAN DEFAULT TRUE,
                click_count INT DEFAULT 0,
                conversion_count INT DEFAULT 0,
                last_shown_at TIMESTAMP NULL DEFAULT NULL,
                expires_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_recommendation_type (recommendation_type),
                INDEX idx_target_entity (target_entity_type, target_entity_id),
                INDEX idx_recommended_entity (recommended_entity_type, recommended_entity_id),
                INDEX idx_confidence_score (confidence_score),
                INDEX idx_is_active (is_active),
                INDEX idx_expires_at (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Insert initial data
     */
    private function insertInitialData() {
        // Insert default AI models
        $default_models = [
            [
                'model_name' => 'sales_forecast_arima',
                'model_type' => 'time_series',
                'model_description' => 'ARIMA-based sales forecasting model',
                'algorithm' => 'ARIMA',
                'accuracy_score' => 0.85,
                'model_status' => 'active',
                'version' => '1.0.0',
                'last_trained_at' => date('Y-m-d H:i:s')
            ],
            [
                'model_name' => 'demand_prediction_rf',
                'model_type' => 'regression',
                'model_description' => 'Random Forest demand prediction model',
                'algorithm' => 'Random Forest',
                'accuracy_score' => 0.82,
                'model_status' => 'active',
                'version' => '1.0.0',
                'last_trained_at' => date('Y-m-d H:i:s')
            ],
            [
                'model_name' => 'price_optimization_ensemble',
                'model_type' => 'ensemble',
                'model_description' => 'Ensemble model for price optimization',
                'algorithm' => 'Gradient Boosting + Neural Network',
                'accuracy_score' => 0.78,
                'model_status' => 'active',
                'version' => '1.0.0',
                'last_trained_at' => date('Y-m-d H:i:s')
            ],
            [
                'model_name' => 'anomaly_detection_isolation_forest',
                'model_type' => 'anomaly_detection',
                'model_description' => 'Isolation Forest anomaly detection model',
                'algorithm' => 'Isolation Forest',
                'accuracy_score' => 0.88,
                'model_status' => 'active',
                'version' => '1.0.0',
                'last_trained_at' => date('Y-m-d H:i:s')
            ],
            [
                'model_name' => 'product_recommendation_collaborative',
                'model_type' => 'recommendation',
                'model_description' => 'Collaborative filtering recommendation model',
                'algorithm' => 'Matrix Factorization',
                'accuracy_score' => 0.76,
                'model_status' => 'active',
                'version' => '1.0.0',
                'last_trained_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($default_models as $model) {
            $this->db->query("
                INSERT IGNORE INTO " . DB_PREFIX . "meschain_ai_models 
                (model_name, model_type, model_description, algorithm, accuracy_score, model_status, version, last_trained_at) 
                VALUES (
                    '" . $this->db->escape($model['model_name']) . "',
                    '" . $this->db->escape($model['model_type']) . "',
                    '" . $this->db->escape($model['model_description']) . "',
                    '" . $this->db->escape($model['algorithm']) . "',
                    '" . $model['accuracy_score'] . "',
                    '" . $model['model_status'] . "',
                    '" . $model['version'] . "',
                    '" . $model['last_trained_at'] . "'
                )
            ");
        }
        
        // Insert feature definitions
        $default_features = [
            ['sales_volume', 'sales', 'Total sales volume', 'numeric'],
            ['sales_trend', 'sales', 'Sales trend indicator', 'numeric'],
            ['seasonality_factor', 'temporal', 'Seasonal adjustment factor', 'numeric'],
            ['price_elasticity', 'pricing', 'Price elasticity coefficient', 'numeric'],
            ['competitor_price_ratio', 'pricing', 'Ratio to competitor average price', 'numeric'],
            ['inventory_level', 'inventory', 'Current inventory level', 'numeric'],
            ['demand_volatility', 'demand', 'Demand volatility measure', 'numeric'],
            ['customer_rating', 'product', 'Average customer rating', 'numeric'],
            ['marketplace_category', 'product', 'Product marketplace category', 'categorical'],
            ['promotional_activity', 'marketing', 'Promotional activity indicator', 'boolean']
        ];
        
        foreach ($default_features as $feature) {
            $this->db->query("
                INSERT IGNORE INTO " . DB_PREFIX . "meschain_ai_feature_store 
                (feature_name, feature_group, feature_description, data_type, entity_type, entity_id, feature_value) 
                VALUES (
                    '" . $feature[0] . "',
                    '" . $feature[1] . "',
                    '" . $feature[2] . "',
                    '" . $feature[3] . "',
                    'system',
                    'default',
                    '0'
                )
            ");
        }
    }
    
    /**
     * Check if tables exist
     */
    public function checkInstallation() {
        $tables = [
            'meschain_ai_predictions',
            'meschain_ai_models',
            'meschain_ai_training_data',
            'meschain_ai_feature_store',
            'meschain_ai_model_performance',
            'meschain_ai_anomalies',
            'meschain_ai_recommendations'
        ];
        
        $existing_tables = [];
        $missing_tables = [];
        
        foreach ($tables as $table) {
            $result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
            
            if ($result->num_rows > 0) {
                $existing_tables[] = $table;
            } else {
                $missing_tables[] = $table;
            }
        }
        
        return [
            'installed' => empty($missing_tables),
            'existing_tables' => $existing_tables,
            'missing_tables' => $missing_tables,
            'total_tables' => count($tables)
        ];
    }
    
    /**
     * Get installation status
     */
    public function getInstallationStatus() {
        $status = $this->checkInstallation();
        
        if ($status['installed']) {
            // Get statistics
            $stats = [];
            
            $stats['predictions_count'] = $this->db->query("
                SELECT COUNT(*) as count FROM " . DB_PREFIX . "meschain_ai_predictions
            ")->row['count'];
            
            $stats['models_count'] = $this->db->query("
                SELECT COUNT(*) as count FROM " . DB_PREFIX . "meschain_ai_models
            ")->row['count'];
            
            $stats['active_models'] = $this->db->query("
                SELECT COUNT(*) as count FROM " . DB_PREFIX . "meschain_ai_models 
                WHERE model_status = 'active'
            ")->row['count'];
            
            $stats['training_data_count'] = $this->db->query("
                SELECT COUNT(*) as count FROM " . DB_PREFIX . "meschain_ai_training_data
            ")->row['count'];
            
            $stats['anomalies_count'] = $this->db->query("
                SELECT COUNT(*) as count FROM " . DB_PREFIX . "meschain_ai_anomalies 
                WHERE is_resolved = FALSE
            ")->row['count'];
            
            return array_merge($status, [
                'statistics' => $stats,
                'last_prediction' => $this->getLastPrediction()
            ]);
        }
        
        return $status;
    }
    
    /**
     * Get last prediction
     */
    private function getLastPrediction() {
        $result = $this->db->query("
            SELECT prediction_type, created_at 
            FROM " . DB_PREFIX . "meschain_ai_predictions 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        
        return $result->num_rows > 0 ? $result->row : null;
    }
} 