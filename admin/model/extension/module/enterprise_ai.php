<?php
/**
 * ATOM-M021: Enterprise AI Model
 * AI & Machine Learning data management with quantum-enhanced processing
 * MesChain-Sync Enterprise v2.1.0 - Musti Team Implementation
 * 
 * @package    MesChain Enterprise AI Model
 * @version    2.1.0
 * @author     MUSTI TAKIMI - ATOM Development Team
 * @date       June 7, 2025
 * @copyright  MesTechSync Solutions
 */

class ModelExtensionModuleEnterpriseAI extends Model {
    
    private $ai_table = 'meschain_ai_models';
    private $predictions_table = 'meschain_ai_predictions';
    private $recommendations_table = 'meschain_ai_recommendations';
    private $behavior_analysis_table = 'meschain_ai_behavior_analysis';
    private $price_optimization_table = 'meschain_ai_price_optimization';
    private $market_trends_table = 'meschain_ai_market_trends';
    
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
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->ai_table . "` (
                `model_id` int(11) NOT NULL AUTO_INCREMENT,
                `model_name` varchar(255) NOT NULL,
                `model_type` varchar(100) NOT NULL,
                `algorithm` varchar(100) NOT NULL,
                `accuracy` decimal(5,2) DEFAULT 0.00,
                `training_data_size` int(11) DEFAULT 0,
                `features` text,
                `update_frequency` varchar(50) DEFAULT 'daily',
                `quantum_enhanced` tinyint(1) DEFAULT 0,
                `status` enum('active','training','inactive') DEFAULT 'active',
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`model_id`),
                KEY `model_type` (`model_type`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // AI Predictions table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->predictions_table . "` (
                `prediction_id` int(11) NOT NULL AUTO_INCREMENT,
                `model_id` int(11) NOT NULL,
                `prediction_type` varchar(100) NOT NULL,
                `input_data` text,
                `prediction_result` text,
                `confidence_score` decimal(5,2) DEFAULT 0.00,
                `processing_time` decimal(8,3) DEFAULT 0.000,
                `quantum_acceleration` decimal(10,2) DEFAULT 0.00,
                `status` enum('completed','processing','failed') DEFAULT 'processing',
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`prediction_id`),
                KEY `model_id` (`model_id`),
                KEY `prediction_type` (`prediction_type`),
                KEY `status` (`status`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // AI Recommendations table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->recommendations_table . "` (
                `recommendation_id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `recommended_products` text,
                `recommendation_score` decimal(5,2) DEFAULT 0.00,
                `personalization_level` decimal(5,2) DEFAULT 0.00,
                `expected_conversion_rate` decimal(5,2) DEFAULT 0.00,
                `click_through_rate` decimal(5,2) DEFAULT 0.00,
                `conversion_rate` decimal(5,2) DEFAULT 0.00,
                `revenue_generated` decimal(15,2) DEFAULT 0.00,
                `quantum_enhanced` tinyint(1) DEFAULT 0,
                `status` enum('active','expired','clicked','converted') DEFAULT 'active',
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `expires_at` timestamp NULL,
                PRIMARY KEY (`recommendation_id`),
                KEY `user_id` (`user_id`),
                KEY `status` (`status`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Customer Behavior Analysis table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->behavior_analysis_table . "` (
                `analysis_id` int(11) NOT NULL AUTO_INCREMENT,
                `customer_id` int(11) NOT NULL,
                `behavior_score` decimal(5,2) DEFAULT 0.00,
                `purchase_probability` decimal(5,2) DEFAULT 0.00,
                `churn_risk` decimal(5,2) DEFAULT 0.00,
                `lifetime_value_prediction` decimal(15,2) DEFAULT 0.00,
                `preferred_categories` text,
                `shopping_patterns` text,
                `engagement_metrics` text,
                `personalization_insights` text,
                `quantum_enhanced` tinyint(1) DEFAULT 0,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`analysis_id`),
                KEY `customer_id` (`customer_id`),
                KEY `churn_risk` (`churn_risk`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Price Optimization table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->price_optimization_table . "` (
                `optimization_id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `current_price` decimal(15,4) NOT NULL,
                `optimized_price` decimal(15,4) NOT NULL,
                `price_change_percentage` decimal(5,2) DEFAULT 0.00,
                `expected_revenue_increase` decimal(5,2) DEFAULT 0.00,
                `confidence_score` decimal(5,2) DEFAULT 0.00,
                `market_factors` text,
                `competitor_analysis` text,
                `demand_prediction` text,
                `quantum_enhanced` tinyint(1) DEFAULT 0,
                `status` enum('pending','applied','rejected') DEFAULT 'pending',
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `applied_at` timestamp NULL,
                PRIMARY KEY (`optimization_id`),
                KEY `product_id` (`product_id`),
                KEY `status` (`status`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Market Trends table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->market_trends_table . "` (
                `trend_id` int(11) NOT NULL AUTO_INCREMENT,
                `market_segment` varchar(255) NOT NULL,
                `trend_direction` enum('upward','downward','stable','volatile') DEFAULT 'stable',
                `trend_strength` decimal(5,2) DEFAULT 0.00,
                `demand_forecast` text,
                `seasonal_patterns` text,
                `market_opportunities` text,
                `risk_factors` text,
                `confidence_interval` text,
                `quantum_enhanced` tinyint(1) DEFAULT 0,
                `forecast_period` varchar(50) DEFAULT '30d',
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`trend_id`),
                KEY `market_segment` (`market_segment`),
                KEY `trend_direction` (`trend_direction`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    /**
     * Save AI model configuration
     */
    public function saveAIModel($model_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->ai_table . "` SET 
                `model_name` = '" . $this->db->escape($model_data['name']) . "',
                `model_type` = '" . $this->db->escape($model_data['type']) . "',
                `algorithm` = '" . $this->db->escape($model_data['algorithm']) . "',
                `accuracy` = '" . (float)$model_data['accuracy'] . "',
                `training_data_size` = '" . (int)$model_data['training_data_size'] . "',
                `features` = '" . $this->db->escape(json_encode($model_data['features'])) . "',
                `update_frequency` = '" . $this->db->escape($model_data['update_frequency']) . "',
                `quantum_enhanced` = '" . (int)$model_data['quantum_enhanced'] . "',
                `status` = '" . $this->db->escape($model_data['status']) . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save prediction result
     */
    public function savePrediction($prediction_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->predictions_table . "` SET 
                `model_id` = '" . (int)$prediction_data['model_id'] . "',
                `prediction_type` = '" . $this->db->escape($prediction_data['type']) . "',
                `input_data` = '" . $this->db->escape(json_encode($prediction_data['input'])) . "',
                `prediction_result` = '" . $this->db->escape(json_encode($prediction_data['result'])) . "',
                `confidence_score` = '" . (float)$prediction_data['confidence'] . "',
                `processing_time` = '" . (float)$prediction_data['processing_time'] . "',
                `quantum_acceleration` = '" . (float)$prediction_data['quantum_acceleration'] . "',
                `status` = '" . $this->db->escape($prediction_data['status']) . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save recommendation
     */
    public function saveRecommendation($recommendation_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->recommendations_table . "` SET 
                `user_id` = '" . (int)$recommendation_data['user_id'] . "',
                `recommended_products` = '" . $this->db->escape(json_encode($recommendation_data['products'])) . "',
                `recommendation_score` = '" . (float)$recommendation_data['score'] . "',
                `personalization_level` = '" . (float)$recommendation_data['personalization'] . "',
                `expected_conversion_rate` = '" . (float)$recommendation_data['conversion_rate'] . "',
                `quantum_enhanced` = '" . (int)$recommendation_data['quantum_enhanced'] . "',
                `expires_at` = DATE_ADD(NOW(), INTERVAL 24 HOUR)";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save behavior analysis
     */
    public function saveBehaviorAnalysis($analysis_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->behavior_analysis_table . "` SET 
                `customer_id` = '" . (int)$analysis_data['customer_id'] . "',
                `behavior_score` = '" . (float)$analysis_data['behavior_score'] . "',
                `purchase_probability` = '" . (float)$analysis_data['purchase_probability'] . "',
                `churn_risk` = '" . (float)$analysis_data['churn_risk'] . "',
                `lifetime_value_prediction` = '" . (float)$analysis_data['lifetime_value'] . "',
                `preferred_categories` = '" . $this->db->escape(json_encode($analysis_data['categories'])) . "',
                `shopping_patterns` = '" . $this->db->escape(json_encode($analysis_data['patterns'])) . "',
                `engagement_metrics` = '" . $this->db->escape(json_encode($analysis_data['engagement'])) . "',
                `personalization_insights` = '" . $this->db->escape(json_encode($analysis_data['insights'])) . "',
                `quantum_enhanced` = '" . (int)$analysis_data['quantum_enhanced'] . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save price optimization
     */
    public function savePriceOptimization($optimization_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->price_optimization_table . "` SET 
                `product_id` = '" . (int)$optimization_data['product_id'] . "',
                `current_price` = '" . (float)$optimization_data['current_price'] . "',
                `optimized_price` = '" . (float)$optimization_data['optimized_price'] . "',
                `price_change_percentage` = '" . (float)$optimization_data['price_change'] . "',
                `expected_revenue_increase` = '" . (float)$optimization_data['revenue_increase'] . "',
                `confidence_score` = '" . (float)$optimization_data['confidence'] . "',
                `market_factors` = '" . $this->db->escape(json_encode($optimization_data['market_factors'])) . "',
                `competitor_analysis` = '" . $this->db->escape(json_encode($optimization_data['competitor_analysis'])) . "',
                `demand_prediction` = '" . $this->db->escape(json_encode($optimization_data['demand_prediction'])) . "',
                `quantum_enhanced` = '" . (int)$optimization_data['quantum_enhanced'] . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save market trend prediction
     */
    public function saveMarketTrend($trend_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->market_trends_table . "` SET 
                `market_segment` = '" . $this->db->escape($trend_data['segment']) . "',
                `trend_direction` = '" . $this->db->escape($trend_data['direction']) . "',
                `trend_strength` = '" . (float)$trend_data['strength'] . "',
                `demand_forecast` = '" . $this->db->escape(json_encode($trend_data['forecast'])) . "',
                `seasonal_patterns` = '" . $this->db->escape(json_encode($trend_data['seasonal'])) . "',
                `market_opportunities` = '" . $this->db->escape(json_encode($trend_data['opportunities'])) . "',
                `risk_factors` = '" . $this->db->escape(json_encode($trend_data['risks'])) . "',
                `confidence_interval` = '" . $this->db->escape(json_encode($trend_data['confidence'])) . "',
                `quantum_enhanced` = '" . (int)$trend_data['quantum_enhanced'] . "',
                `forecast_period` = '" . $this->db->escape($trend_data['period']) . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Get AI models
     */
    public function getAIModels($filters = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->ai_table . "` WHERE 1=1";
        
        if (!empty($filters['model_type'])) {
            $sql .= " AND `model_type` = '" . $this->db->escape($filters['model_type']) . "'";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND `status` = '" . $this->db->escape($filters['status']) . "'";
        }
        
        $sql .= " ORDER BY `created_at` DESC";
        
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
                LEFT JOIN `" . DB_PREFIX . $this->ai_table . "` m ON p.model_id = m.model_id
                WHERE 1=1";
        
        if (!empty($filters['prediction_type'])) {
            $sql .= " AND p.`prediction_type` = '" . $this->db->escape($filters['prediction_type']) . "'";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND p.`status` = '" . $this->db->escape($filters['status']) . "'";
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
     * Get recommendations
     */
    public function getRecommendations($filters = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->recommendations_table . "` WHERE 1=1";
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND `user_id` = '" . (int)$filters['user_id'] . "'";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND `status` = '" . $this->db->escape($filters['status']) . "'";
        }
        
        $sql .= " ORDER BY `created_at` DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get behavior analyses
     */
    public function getBehaviorAnalyses($filters = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->behavior_analysis_table . "` WHERE 1=1";
        
        if (!empty($filters['customer_id'])) {
            $sql .= " AND `customer_id` = '" . (int)$filters['customer_id'] . "'";
        }
        
        if (!empty($filters['churn_risk_min'])) {
            $sql .= " AND `churn_risk` >= '" . (float)$filters['churn_risk_min'] . "'";
        }
        
        $sql .= " ORDER BY `created_at` DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get price optimizations
     */
    public function getPriceOptimizations($filters = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->price_optimization_table . "` WHERE 1=1";
        
        if (!empty($filters['product_id'])) {
            $sql .= " AND `product_id` = '" . (int)$filters['product_id'] . "'";
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND `status` = '" . $this->db->escape($filters['status']) . "'";
        }
        
        $sql .= " ORDER BY `created_at` DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get market trends
     */
    public function getMarketTrends($filters = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->market_trends_table . "` WHERE 1=1";
        
        if (!empty($filters['market_segment'])) {
            $sql .= " AND `market_segment` = '" . $this->db->escape($filters['market_segment']) . "'";
        }
        
        if (!empty($filters['trend_direction'])) {
            $sql .= " AND `trend_direction` = '" . $this->db->escape($filters['trend_direction']) . "'";
        }
        
        $sql .= " ORDER BY `created_at` DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get AI statistics
     */
    public function getAIStatistics($period = '24h') {
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
        
        // Successful predictions
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->predictions_table . "` " . $date_condition . " AND status = 'completed'");
        $statistics['successful_predictions'] = $query->row['total'];
        
        // Average accuracy
        $query = $this->db->query("SELECT AVG(confidence_score) as avg_accuracy FROM `" . DB_PREFIX . $this->predictions_table . "` " . $date_condition . " AND status = 'completed'");
        $statistics['average_accuracy'] = round($query->row['avg_accuracy'], 2);
        
        // Total recommendations
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->recommendations_table . "` " . $date_condition);
        $statistics['total_recommendations'] = $query->row['total'];
        
        // Recommendation conversion rate
        $query = $this->db->query("SELECT AVG(conversion_rate) as avg_conversion FROM `" . DB_PREFIX . $this->recommendations_table . "` " . $date_condition . " AND conversion_rate > 0");
        $statistics['recommendation_conversion_rate'] = round($query->row['avg_conversion'], 2);
        
        // Total revenue from recommendations
        $query = $this->db->query("SELECT SUM(revenue_generated) as total_revenue FROM `" . DB_PREFIX . $this->recommendations_table . "` " . $date_condition);
        $statistics['recommendation_revenue'] = round($query->row['total_revenue'], 2);
        
        return $statistics;
    }
    
    /**
     * Update recommendation metrics
     */
    public function updateRecommendationMetrics($recommendation_id, $metrics) {
        $sql = "UPDATE `" . DB_PREFIX . $this->recommendations_table . "` SET ";
        $updates = [];
        
        if (isset($metrics['click_through_rate'])) {
            $updates[] = "`click_through_rate` = '" . (float)$metrics['click_through_rate'] . "'";
        }
        
        if (isset($metrics['conversion_rate'])) {
            $updates[] = "`conversion_rate` = '" . (float)$metrics['conversion_rate'] . "'";
        }
        
        if (isset($metrics['revenue_generated'])) {
            $updates[] = "`revenue_generated` = '" . (float)$metrics['revenue_generated'] . "'";
        }
        
        if (isset($metrics['status'])) {
            $updates[] = "`status` = '" . $this->db->escape($metrics['status']) . "'";
        }
        
        if (!empty($updates)) {
            $sql .= implode(', ', $updates);
            $sql .= " WHERE `recommendation_id` = '" . (int)$recommendation_id . "'";
            
            $this->db->query($sql);
        }
    }
    
    /**
     * Apply price optimization
     */
    public function applyPriceOptimization($optimization_id) {
        $sql = "UPDATE `" . DB_PREFIX . $this->price_optimization_table . "` SET 
                `status` = 'applied',
                `applied_at` = NOW()
                WHERE `optimization_id` = '" . (int)$optimization_id . "'";
        
        $this->db->query($sql);
        
        // Get optimization details
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . $this->price_optimization_table . "` WHERE `optimization_id` = '" . (int)$optimization_id . "'");
        
        if ($query->num_rows) {
            $optimization = $query->row;
            
            // Update product price
            $this->db->query("UPDATE `" . DB_PREFIX . "product` SET `price` = '" . (float)$optimization['optimized_price'] . "' WHERE `product_id` = '" . (int)$optimization['product_id'] . "'");
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get dashboard metrics
     */
    public function getDashboardMetrics() {
        $metrics = [];
        
        // Active models
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->ai_table . "` WHERE status = 'active'");
        $metrics['active_models'] = $query->row['total'];
        
        // Today's predictions
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->predictions_table . "` WHERE DATE(created_at) = CURDATE()");
        $metrics['predictions_today'] = $query->row['total'];
        
        // Today's recommendations
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->recommendations_table . "` WHERE DATE(created_at) = CURDATE()");
        $metrics['recommendations_today'] = $query->row['total'];
        
        // Average accuracy
        $query = $this->db->query("SELECT AVG(accuracy) as avg_accuracy FROM `" . DB_PREFIX . $this->ai_table . "` WHERE status = 'active'");
        $metrics['average_accuracy'] = round($query->row['avg_accuracy'], 2);
        
        // Pending price optimizations
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->price_optimization_table . "` WHERE status = 'pending'");
        $metrics['pending_optimizations'] = $query->row['total'];
        
        // High-risk customers
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->behavior_analysis_table . "` WHERE churn_risk > 0.7 AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $metrics['high_risk_customers'] = $query->row['total'];
        
        return $metrics;
    }
} 