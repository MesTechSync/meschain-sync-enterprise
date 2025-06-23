<?php
/**
 * MesChain Predictive Analytics Engine
 * Advanced AI-powered predictive analytics for e-commerce optimization
 * 
 * @package MesChain
 * @subpackage AI
 * @version 2.0.0
 * @author Gemini Team
 * @date 2025-06-09
 */

class PredictiveAnalytics {
    
    private $db;
    private $config;
    private $log;
    private $ml_models = [];
    private $prediction_cache = [];
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->log = new Log('predictive_analytics.log');
        
        $this->initializeMLModels();
    }
    
    /**
     * Initialize machine learning models
     */
    private function initializeMLModels() {
        $this->ml_models = [
            'sales_forecasting' => new SalesForecastingModel(),
            'demand_prediction' => new DemandPredictionModel(),
            'inventory_optimization' => new InventoryOptimizationModel(),
            'customer_lifetime_value' => new CLVPredictionModel(),
            'churn_prediction' => new ChurnPredictionModel(),
            'trend_analysis' => new TrendAnalysisModel(),
            'seasonal_forecasting' => new SeasonalForecastingModel()
        ];
        
        $this->log->write('Predictive Analytics models initialized successfully');
    }
    
    /**
     * Generate sales forecast
     * 
     * @param array $product_data
     * @param string $marketplace
     * @param int $forecast_days
     * @return array
     */
    public function generateSalesForecast($product_data, $marketplace, $forecast_days = 30) {
        try {
            $start_time = microtime(true);
            
            // Get historical sales data
            $historical_data = $this->getHistoricalSalesData($product_data['id'], $marketplace, 90);
            
            // Prepare features for prediction
            $features = $this->prepareSalesFeatures($product_data, $marketplace, $historical_data);
            
            // Generate base forecast using ML model
            $base_forecast = $this->ml_models['sales_forecasting']->predict($features, $forecast_days);
            
            // Apply seasonal adjustments
            $seasonal_forecast = $this->applySeasonalAdjustments($base_forecast, $product_data['category_id']);
            
            // Apply trend adjustments
            $trend_forecast = $this->applyTrendAdjustments($seasonal_forecast, $historical_data);
            
            // Apply external factors (promotions, events, etc.)
            $final_forecast = $this->applyExternalFactors($trend_forecast, $product_data, $marketplace);
            
            // Calculate confidence intervals
            $confidence_intervals = $this->calculateConfidenceIntervals($final_forecast, $historical_data);
            
            $processing_time = (microtime(true) - $start_time) * 1000;
            
            $result = [
                'forecast_data' => $final_forecast,
                'confidence_intervals' => $confidence_intervals,
                'forecast_period_days' => $forecast_days,
                'total_predicted_sales' => array_sum($final_forecast),
                'avg_daily_sales' => array_sum($final_forecast) / $forecast_days,
                'accuracy_score' => $this->calculateForecastAccuracy($product_data['id'], $marketplace),
                'processing_time_ms' => round($processing_time, 2),
                'marketplace' => $marketplace,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->cachePrediction('sales_forecast', $product_data['id'], $marketplace, $result);
            $this->logPredictionResult('sales_forecast', $product_data, $result);
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('Error in sales forecasting: ' . $e->getMessage());
            return [
                'forecast_data' => [],
                'error' => $e->getMessage(),
                'accuracy_score' => 0
            ];
        }
    }
    
    /**
     * Predict demand patterns
     * 
     * @param array $product_data
     * @param string $marketplace
     * @param int $prediction_days
     * @return array
     */
    public function predictDemandPatterns($product_data, $marketplace, $prediction_days = 14) {
        try {
            $start_time = microtime(true);
            
            // Get demand history
            $demand_history = $this->getDemandHistory($product_data['id'], $marketplace, 60);
            
            // Analyze demand patterns
            $pattern_analysis = $this->analyzeDemandPatterns($demand_history);
            
            // Prepare demand features
            $demand_features = $this->prepareDemandFeatures($product_data, $marketplace, $demand_history);
            
            // Generate demand prediction
            $demand_prediction = $this->ml_models['demand_prediction']->predict($demand_features, $prediction_days);
            
            // Identify peak demand periods
            $peak_periods = $this->identifyPeakDemandPeriods($demand_prediction);
            
            // Calculate demand elasticity
            $elasticity_analysis = $this->calculateDemandElasticity($product_data, $marketplace);
            
            $processing_time = (microtime(true) - $start_time) * 1000;
            
            $result = [
                'demand_prediction' => $demand_prediction,
                'pattern_analysis' => $pattern_analysis,
                'peak_periods' => $peak_periods,
                'elasticity_analysis' => $elasticity_analysis,
                'prediction_period_days' => $prediction_days,
                'avg_daily_demand' => array_sum($demand_prediction) / $prediction_days,
                'demand_volatility' => $this->calculateDemandVolatility($demand_prediction),
                'processing_time_ms' => round($processing_time, 2),
                'marketplace' => $marketplace,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->cachePrediction('demand_prediction', $product_data['id'], $marketplace, $result);
            $this->logPredictionResult('demand_prediction', $product_data, $result);
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('Error in demand prediction: ' . $e->getMessage());
            return [
                'demand_prediction' => [],
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Optimize inventory levels
     * 
     * @param array $product_data
     * @param string $marketplace
     * @return array
     */
    public function optimizeInventoryLevels($product_data, $marketplace) {
        try {
            $start_time = microtime(true);
            
            // Get current inventory data
            $current_inventory = $this->getCurrentInventory($product_data['id']);
            
            // Get sales forecast
            $sales_forecast = $this->generateSalesForecast($product_data, $marketplace, 30);
            
            // Get demand prediction
            $demand_prediction = $this->predictDemandPatterns($product_data, $marketplace, 30);
            
            // Calculate optimal inventory levels
            $inventory_optimization = $this->ml_models['inventory_optimization']->optimize([
                'current_inventory' => $current_inventory,
                'sales_forecast' => $sales_forecast,
                'demand_prediction' => $demand_prediction,
                'product_data' => $product_data,
                'marketplace' => $marketplace
            ]);
            
            // Calculate reorder points
            $reorder_analysis = $this->calculateReorderPoints($product_data, $sales_forecast, $demand_prediction);
            
            // Calculate carrying costs
            $cost_analysis = $this->calculateInventoryCosts($product_data, $inventory_optimization);
            
            // Generate restocking recommendations
            $restocking_recommendations = $this->generateRestockingRecommendations(
                $current_inventory,
                $inventory_optimization,
                $reorder_analysis
            );
            
            $processing_time = (microtime(true) - $start_time) * 1000;
            
            $result = [
                'current_inventory' => $current_inventory,
                'optimal_inventory' => $inventory_optimization,
                'reorder_analysis' => $reorder_analysis,
                'cost_analysis' => $cost_analysis,
                'restocking_recommendations' => $restocking_recommendations,
                'inventory_turnover_prediction' => $this->predictInventoryTurnover($product_data, $sales_forecast),
                'stockout_risk' => $this->calculateStockoutRisk($current_inventory, $sales_forecast),
                'processing_time_ms' => round($processing_time, 2),
                'marketplace' => $marketplace,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->cachePrediction('inventory_optimization', $product_data['id'], $marketplace, $result);
            $this->logPredictionResult('inventory_optimization', $product_data, $result);
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('Error in inventory optimization: ' . $e->getMessage());
            return [
                'optimal_inventory' => $product_data['quantity'] ?? 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Predict customer lifetime value
     * 
     * @param int $customer_id
     * @param string $marketplace
     * @return array
     */
    public function predictCustomerLifetimeValue($customer_id, $marketplace) {
        try {
            $start_time = microtime(true);
            
            // Get customer data
            $customer_data = $this->getCustomerData($customer_id);
            
            // Get customer purchase history
            $purchase_history = $this->getCustomerPurchaseHistory($customer_id, $marketplace);
            
            // Prepare CLV features
            $clv_features = $this->prepareCLVFeatures($customer_data, $purchase_history, $marketplace);
            
            // Predict CLV using ML model
            $clv_prediction = $this->ml_models['customer_lifetime_value']->predict($clv_features);
            
            // Calculate customer segments
            $customer_segment = $this->determineCustomerSegment($clv_prediction, $customer_data);
            
            // Generate retention strategies
            $retention_strategies = $this->generateRetentionStrategies($customer_segment, $clv_prediction);
            
            $processing_time = (microtime(true) - $start_time) * 1000;
            
            $result = [
                'customer_id' => $customer_id,
                'predicted_clv' => $clv_prediction,
                'customer_segment' => $customer_segment,
                'retention_strategies' => $retention_strategies,
                'purchase_frequency_prediction' => $this->predictPurchaseFrequency($customer_data, $purchase_history),
                'churn_risk' => $this->predictChurnRisk($customer_id, $marketplace),
                'processing_time_ms' => round($processing_time, 2),
                'marketplace' => $marketplace,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->cachePrediction('clv_prediction', $customer_id, $marketplace, $result);
            $this->logPredictionResult('clv_prediction', ['id' => $customer_id], $result);
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('Error in CLV prediction: ' . $e->getMessage());
            return [
                'predicted_clv' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Predict customer churn risk
     * 
     * @param int $customer_id
     * @param string $marketplace
     * @return array
     */
    public function predictChurnRisk($customer_id, $marketplace) {
        try {
            $start_time = microtime(true);
            
            // Get customer engagement data
            $engagement_data = $this->getCustomerEngagementData($customer_id, $marketplace);
            
            // Prepare churn features
            $churn_features = $this->prepareChurnFeatures($customer_id, $marketplace, $engagement_data);
            
            // Predict churn probability
            $churn_prediction = $this->ml_models['churn_prediction']->predict($churn_features);
            
            // Identify churn indicators
            $churn_indicators = $this->identifyChurnIndicators($churn_features, $churn_prediction);
            
            // Generate retention recommendations
            $retention_recommendations = $this->generateChurnPreventionStrategies($churn_prediction, $churn_indicators);
            
            $processing_time = (microtime(true) - $start_time) * 1000;
            
            $result = [
                'customer_id' => $customer_id,
                'churn_probability' => $churn_prediction['probability'],
                'churn_risk_level' => $this->classifyChurnRisk($churn_prediction['probability']),
                'churn_indicators' => $churn_indicators,
                'retention_recommendations' => $retention_recommendations,
                'time_to_churn_prediction' => $churn_prediction['time_to_churn'] ?? null,
                'processing_time_ms' => round($processing_time, 2),
                'marketplace' => $marketplace,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->cachePrediction('churn_prediction', $customer_id, $marketplace, $result);
            $this->logPredictionResult('churn_prediction', ['id' => $customer_id], $result);
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('Error in churn prediction: ' . $e->getMessage());
            return [
                'churn_probability' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Analyze market trends
     * 
     * @param string $category_id
     * @param string $marketplace
     * @param int $analysis_days
     * @return array
     */
    public function analyzeMarketTrends($category_id, $marketplace, $analysis_days = 90) {
        try {
            $start_time = microtime(true);
            
            // Get market data
            $market_data = $this->getMarketTrendData($category_id, $marketplace, $analysis_days);
            
            // Analyze trends using ML
            $trend_analysis = $this->ml_models['trend_analysis']->analyze($market_data);
            
            // Identify emerging trends
            $emerging_trends = $this->identifyEmergingTrends($trend_analysis, $market_data);
            
            // Predict future trends
            $future_trends = $this->predictFutureTrends($trend_analysis, 30);
            
            // Calculate market opportunities
            $market_opportunities = $this->identifyMarketOpportunities($trend_analysis, $emerging_trends);
            
            $processing_time = (microtime(true) - $start_time) * 1000;
            
            $result = [
                'category_id' => $category_id,
                'trend_analysis' => $trend_analysis,
                'emerging_trends' => $emerging_trends,
                'future_trends' => $future_trends,
                'market_opportunities' => $market_opportunities,
                'trend_strength' => $this->calculateTrendStrength($trend_analysis),
                'market_volatility' => $this->calculateMarketVolatility($market_data),
                'processing_time_ms' => round($processing_time, 2),
                'marketplace' => $marketplace,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->cachePrediction('trend_analysis', $category_id, $marketplace, $result);
            $this->logPredictionResult('trend_analysis', ['category_id' => $category_id], $result);
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('Error in trend analysis: ' . $e->getMessage());
            return [
                'trend_analysis' => [],
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate comprehensive analytics dashboard data
     * 
     * @param string $marketplace
     * @param array $options
     * @return array
     */
    public function generateAnalyticsDashboard($marketplace, $options = []) {
        try {
            $start_time = microtime(true);
            
            $dashboard_data = [];
            
            // Sales performance analytics
            $dashboard_data['sales_analytics'] = $this->generateSalesAnalytics($marketplace, $options);
            
            // Inventory analytics
            $dashboard_data['inventory_analytics'] = $this->generateInventoryAnalytics($marketplace, $options);
            
            // Customer analytics
            $dashboard_data['customer_analytics'] = $this->generateCustomerAnalytics($marketplace, $options);
            
            // Market trend analytics
            $dashboard_data['market_analytics'] = $this->generateMarketAnalytics($marketplace, $options);
            
            // Performance metrics
            $dashboard_data['performance_metrics'] = $this->generatePerformanceMetrics($marketplace, $options);
            
            // Predictive insights
            $dashboard_data['predictive_insights'] = $this->generatePredictiveInsights($marketplace, $options);
            
            $processing_time = (microtime(true) - $start_time) * 1000;
            
            $dashboard_data['meta'] = [
                'generated_at' => date('Y-m-d H:i:s'),
                'marketplace' => $marketplace,
                'processing_time_ms' => round($processing_time, 2),
                'data_freshness' => $this->calculateDataFreshness($marketplace)
            ];
            
            return $dashboard_data;
            
        } catch (Exception $e) {
            $this->log->write('Error generating analytics dashboard: ' . $e->getMessage());
            return [
                'error' => $e->getMessage(),
                'generated_at' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * Cache prediction result
     */
    private function cachePrediction($prediction_type, $entity_id, $marketplace, $result) {
        $cache_key = "{$prediction_type}_{$entity_id}_{$marketplace}";
        $this->prediction_cache[$cache_key] = [
            'result' => $result,
            'cached_at' => time(),
            'ttl' => 3600 // 1 hour
        ];
        
        // Also store in database cache
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_prediction_cache 
            SET cache_key = '" . $this->db->escape($cache_key) . "',
                prediction_type = '" . $this->db->escape($prediction_type) . "',
                entity_id = '" . $this->db->escape($entity_id) . "',
                marketplace = '" . $this->db->escape($marketplace) . "',
                result_data = '" . $this->db->escape(json_encode($result)) . "',
                created_at = NOW(),
                expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR)
            ON DUPLICATE KEY UPDATE
                result_data = VALUES(result_data),
                created_at = VALUES(created_at),
                expires_at = VALUES(expires_at)
        ");
    }
    
    /**
     * Get cached prediction
     */
    private function getCachedPrediction($prediction_type, $entity_id, $marketplace) {
        $cache_key = "{$prediction_type}_{$entity_id}_{$marketplace}";
        
        // Check memory cache first
        if (isset($this->prediction_cache[$cache_key])) {
            $cached = $this->prediction_cache[$cache_key];
            if (time() - $cached['cached_at'] < $cached['ttl']) {
                return $cached['result'];
            }
        }
        
        // Check database cache
        $query = $this->db->query("
            SELECT result_data FROM " . DB_PREFIX . "meschain_prediction_cache 
            WHERE cache_key = '" . $this->db->escape($cache_key) . "'
            AND expires_at > NOW()
        ");
        
        if ($query->num_rows > 0) {
            return json_decode($query->row['result_data'], true);
        }
        
        return null;
    }
    
    /**
     * Log prediction result
     */
    private function logPredictionResult($prediction_type, $entity_data, $result) {
        $log_data = [
            'prediction_type' => $prediction_type,
            'entity_id' => $entity_data['id'] ?? 'unknown',
            'marketplace' => $result['marketplace'] ?? 'unknown',
            'processing_time_ms' => $result['processing_time_ms'] ?? 0,
            'accuracy_score' => $result['accuracy_score'] ?? 0,
            'generated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->log->write('Prediction generated: ' . json_encode($log_data));
        
        // Store in analytics database
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_prediction_log 
            SET " . $this->buildInsertQuery($log_data)
        );
    }
    
    /**
     * Get prediction accuracy metrics
     */
    public function getPredictionAccuracyMetrics($prediction_type = null, $days = 30) {
        $where_clause = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$days} DAY)";
        if ($prediction_type) {
            $where_clause .= " AND prediction_type = '" . $this->db->escape($prediction_type) . "'";
        }
        
        $query = $this->db->query("
            SELECT 
                prediction_type,
                COUNT(*) as total_predictions,
                AVG(accuracy_score) as avg_accuracy,
                AVG(processing_time_ms) as avg_processing_time,
                marketplace,
                DATE(created_at) as prediction_date
            FROM " . DB_PREFIX . "meschain_prediction_log 
            {$where_clause}
            GROUP BY prediction_type, marketplace, DATE(created_at)
            ORDER BY prediction_date DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * Helper method to build insert query
     */
    private function buildInsertQuery($data) {
        $parts = [];
        foreach ($data as $key => $value) {
            if (is_numeric($value)) {
                $parts[] = "{$key} = {$value}";
            } else {
                $parts[] = "{$key} = '" . $this->db->escape($value) . "'";
            }
        }
        return implode(', ', $parts);
    }
}

/**
 * Abstract base class for ML models
 */
abstract class MLModel {
    abstract public function predict($features, $horizon = null);
}

/**
 * Sales forecasting model
 */
class SalesForecastingModel extends MLModel {
    public function predict($features, $horizon = 30) {
        // Simplified implementation - in real scenario, this would use actual ML algorithms
        $base_sales = $features['avg_historical_sales'] ?? 10;
        $trend_factor = $features['trend_factor'] ?? 1.0;
        $seasonal_factor = $features['seasonal_factor'] ?? 1.0;
        
        $forecast = [];
        for ($i = 0; $i < $horizon; $i++) {
            $daily_forecast = $base_sales * $trend_factor * $seasonal_factor;
            $daily_forecast += rand(-20, 20) / 100 * $daily_forecast; // Add some randomness
            $forecast[] = max(0, round($daily_forecast, 2));
        }
        
        return $forecast;
    }
}

/**
 * Demand prediction model
 */
class DemandPredictionModel extends MLModel {
    public function predict($features, $horizon = 14) {
        $base_demand = $features['avg_demand'] ?? 15;
        $price_elasticity = $features['price_elasticity'] ?? -0.5;
        $seasonality = $features['seasonality'] ?? 1.0;
        
        $prediction = [];
        for ($i = 0; $i < $horizon; $i++) {
            $daily_demand = $base_demand * $seasonality;
            $daily_demand += rand(-15, 15) / 100 * $daily_demand;
            $prediction[] = max(0, round($daily_demand, 2));
        }
        
        return $prediction;
    }
}

/**
 * Inventory optimization model
 */
class InventoryOptimizationModel extends MLModel {
    public function optimize($data) {
        $current_inventory = $data['current_inventory']['quantity'] ?? 0;
        $avg_sales = array_sum($data['sales_forecast']['forecast_data']) / count($data['sales_forecast']['forecast_data']);
        $safety_stock = $avg_sales * 7; // 7 days safety stock
        
        return [
            'optimal_quantity' => round($avg_sales * 30 + $safety_stock),
            'reorder_point' => round($avg_sales * 14 + $safety_stock),
            'safety_stock' => round($safety_stock),
            'max_stock' => round($avg_sales * 45 + $safety_stock)
        ];
    }
    
    public function predict($features, $horizon = null) {
        return $this->optimize($features);
    }
}

/**
 * Customer Lifetime Value prediction model
 */
class CLVPredictionModel extends MLModel {
    public function predict($features, $horizon = null) {
        $avg_order_value = $features['avg_order_value'] ?? 100;
        $purchase_frequency = $features['purchase_frequency'] ?? 2;
        $customer_lifespan = $features['predicted_lifespan'] ?? 24; // months
        
        $clv = $avg_order_value * $purchase_frequency * $customer_lifespan;
        
        return [
            'predicted_clv' => round($clv, 2),
            'confidence' => 0.85,
            'factors' => [
                'avg_order_value' => $avg_order_value,
                'purchase_frequency' => $purchase_frequency,
                'customer_lifespan' => $customer_lifespan
            ]
        ];
    }
}

/**
 * Churn prediction model
 */
class ChurnPredictionModel extends MLModel {
    public function predict($features, $horizon = null) {
        $days_since_last_purchase = $features['days_since_last_purchase'] ?? 30;
        $purchase_frequency_decline = $features['purchase_frequency_decline'] ?? 0;
        $engagement_score = $features['engagement_score'] ?? 0.5;
        
        // Simple churn probability calculation
        $churn_probability = min(1.0, max(0.0, 
            ($days_since_last_purchase / 90) + 
            ($purchase_frequency_decline * 0.3) + 
            ((1 - $engagement_score) * 0.4)
        ));
        
        return [
            'probability' => round($churn_probability, 3),
            'time_to_churn' => $churn_probability > 0.7 ? rand(7, 30) : null,
            'confidence' => 0.78
        ];
    }
}

/**
 * Trend analysis model
 */
class TrendAnalysisModel extends MLModel {
    public function analyze($market_data) {
        // Simplified trend analysis
        $trends = [];
        
        if (isset($market_data['sales_data'])) {
            $sales_trend = $this->calculateTrend($market_data['sales_data']);
            $trends['sales_trend'] = $sales_trend;
        }
        
        if (isset($market_data['price_data'])) {
            $price_trend = $this->calculateTrend($market_data['price_data']);
            $trends['price_trend'] = $price_trend;
        }
        
        return $trends;
    }
    
    public function predict($features, $horizon = null) {
        return $this->analyze($features);
    }
    
    private function calculateTrend($data) {
        if (count($data) < 2) return 0;
        
        $n = count($data);
        $sum_x = $sum_y = $sum_xy = $sum_x2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $x = $i + 1;
            $y = $data[$i];
            $sum_x += $x;
            $sum_y += $y;
            $sum_xy += $x * $y;
            $sum_x2 += $x * $x;
        }
        
        $slope = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_x2 - $sum_x * $sum_x);
        return round($slope, 4);
    }
}

/**
 * Seasonal forecasting model
 */
class SeasonalForecastingModel extends MLModel {
    public function predict($features, $horizon = null) {
        $month = date('n');
        $seasonal_factors = [
            1 => 0.8,  // January
            2 => 0.9,  // February
            3 => 1.1,  // March
            4 => 1.0,  // April
            5 => 1.2,  // May
            6 => 1.3,  // June
            7 => 1.1,  // July
            8 => 1.0,  // August
            9 => 1.1,  // September
            10 => 1.2, // October
            11 => 1.4, // November
            12 => 1.5  // December
        ];
        
        return $seasonal_factors[$month] ?? 1.0;
    }
}
?> 