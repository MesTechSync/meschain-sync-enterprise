<?php
/**
 * MesChain Automated Pricing Engine
 * AI-powered dynamic pricing system for multi-marketplace optimization
 * 
 * @package MesChain
 * @subpackage AI
 * @version 2.0.0
 * @author Gemini Team
 * @date 2025-06-09
 */

class AutomatedPricingEngine {
    
    private $db;
    private $config;
    private $log;
    private $pricing_strategies = [];
    private $competitor_apis = [];
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->log = new Log('automated_pricing.log');
        
        $this->initializePricingStrategies();
        $this->initializeCompetitorAPIs();
    }
    
    /**
     * Initialize pricing strategies
     */
    private function initializePricingStrategies() {
        $this->pricing_strategies = [
            'competitive' => new CompetitivePricingStrategy(),
            'profit_maximization' => new ProfitMaximizationStrategy(),
            'market_penetration' => new MarketPenetrationStrategy(),
            'dynamic_demand' => new DynamicDemandStrategy(),
            'inventory_based' => new InventoryBasedStrategy()
        ];
    }
    
    /**
     * Initialize competitor API connections
     */
    private function initializeCompetitorAPIs() {
        $this->competitor_apis = [
            'trendyol' => new TrendyolCompetitorAPI(),
            'n11' => new N11CompetitorAPI(),
            'amazon' => new AmazonCompetitorAPI(),
            'hepsiburada' => new HepsiburadaCompetitorAPI()
        ];
    }
    
    /**
     * Calculate optimal price for product
     * 
     * @param array $product_data
     * @param string $marketplace
     * @param array $options
     * @return array
     */
    public function calculateOptimalPrice($product_data, $marketplace, $options = []) {
        try {
            $start_time = microtime(true);
            
            // Get current market data
            $market_data = $this->getMarketData($product_data, $marketplace);
            
            // Analyze competitor prices
            $competitor_analysis = $this->analyzeCompetitorPrices($product_data, $marketplace);
            
            // Calculate demand elasticity
            $demand_elasticity = $this->calculateDemandElasticity($product_data, $marketplace);
            
            // Get inventory levels
            $inventory_data = $this->getInventoryData($product_data);
            
            // Apply pricing strategies
            $strategy_results = [];
            foreach ($this->pricing_strategies as $strategy_name => $strategy) {
                $strategy_results[$strategy_name] = $strategy->calculatePrice([
                    'product' => $product_data,
                    'market_data' => $market_data,
                    'competitors' => $competitor_analysis,
                    'demand_elasticity' => $demand_elasticity,
                    'inventory' => $inventory_data,
                    'marketplace' => $marketplace
                ]);
            }
            
            // Select best strategy based on current conditions
            $optimal_strategy = $this->selectOptimalStrategy($strategy_results, $options);
            
            // Calculate final price with constraints
            $final_price = $this->applyPriceConstraints(
                $strategy_results[$optimal_strategy]['price'],
                $product_data,
                $marketplace
            );
            
            // Calculate profit margins
            $profit_analysis = $this->calculateProfitAnalysis($final_price, $product_data);
            
            $processing_time = (microtime(true) - $start_time) * 1000;
            
            $result = [
                'optimal_price' => $final_price,
                'current_price' => $product_data['price'] ?? 0,
                'price_change' => $final_price - ($product_data['price'] ?? 0),
                'price_change_percent' => $this->calculatePriceChangePercent($final_price, $product_data['price'] ?? 0),
                'strategy_used' => $optimal_strategy,
                'strategy_results' => $strategy_results,
                'competitor_analysis' => $competitor_analysis,
                'profit_analysis' => $profit_analysis,
                'confidence_score' => $this->calculateConfidenceScore($strategy_results, $market_data),
                'processing_time_ms' => round($processing_time, 2),
                'marketplace' => $marketplace,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            $this->logPricingResult($product_data, $result);
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('Error in price calculation: ' . $e->getMessage());
            return [
                'optimal_price' => $product_data['price'] ?? 0,
                'error' => $e->getMessage(),
                'confidence_score' => 0
            ];
        }
    }
    
    /**
     * Get market data for product
     */
    private function getMarketData($product_data, $marketplace) {
        $market_data = [];
        
        // Get historical sales data
        $sales_history = $this->getSalesHistory($product_data['id'], $marketplace);
        $market_data['sales_history'] = $sales_history;
        $market_data['avg_daily_sales'] = $this->calculateAverageDailySales($sales_history);
        
        // Get category trends
        $category_trends = $this->getCategoryTrends($product_data['category_id'], $marketplace);
        $market_data['category_trends'] = $category_trends;
        
        // Get seasonal factors
        $seasonal_factors = $this->getSeasonalFactors($product_data['category_id']);
        $market_data['seasonal_factors'] = $seasonal_factors;
        
        // Get market share data
        $market_share = $this->getMarketShare($product_data, $marketplace);
        $market_data['market_share'] = $market_share;
        
        return $market_data;
    }
    
    /**
     * Analyze competitor prices
     */
    private function analyzeCompetitorPrices($product_data, $marketplace) {
        $competitor_data = [];
        
        foreach ($this->competitor_apis as $competitor => $api) {
            if ($competitor !== $marketplace) {
                try {
                    $competitor_prices = $api->getProductPrices($product_data);
                    $competitor_data[$competitor] = [
                        'prices' => $competitor_prices,
                        'min_price' => min($competitor_prices),
                        'max_price' => max($competitor_prices),
                        'avg_price' => array_sum($competitor_prices) / count($competitor_prices),
                        'price_count' => count($competitor_prices)
                    ];
                } catch (Exception $e) {
                    $this->log->write("Error getting competitor prices from {$competitor}: " . $e->getMessage());
                    $competitor_data[$competitor] = ['error' => $e->getMessage()];
                }
            }
        }
        
        // Calculate overall competitor statistics
        $all_prices = [];
        foreach ($competitor_data as $competitor => $data) {
            if (isset($data['prices'])) {
                $all_prices = array_merge($all_prices, $data['prices']);
            }
        }
        
        if (!empty($all_prices)) {
            $competitor_data['overall'] = [
                'min_price' => min($all_prices),
                'max_price' => max($all_prices),
                'avg_price' => array_sum($all_prices) / count($all_prices),
                'median_price' => $this->calculateMedian($all_prices),
                'price_variance' => $this->calculateVariance($all_prices)
            ];
        }
        
        return $competitor_data;
    }
    
    /**
     * Calculate demand elasticity
     */
    private function calculateDemandElasticity($product_data, $marketplace) {
        // Get price-sales correlation data
        $price_sales_data = $this->getPriceSalesCorrelation($product_data['id'], $marketplace);
        
        if (count($price_sales_data) < 5) {
            // Not enough data, use category average
            return $this->getCategoryElasticity($product_data['category_id']);
        }
        
        // Calculate elasticity using regression analysis
        $elasticity = $this->calculateElasticityRegression($price_sales_data);
        
        return [
            'elasticity_coefficient' => $elasticity,
            'elasticity_type' => $this->classifyElasticity($elasticity),
            'data_points' => count($price_sales_data),
            'confidence' => $this->calculateElasticityConfidence($price_sales_data)
        ];
    }
    
    /**
     * Select optimal pricing strategy
     */
    private function selectOptimalStrategy($strategy_results, $options) {
        $strategy_scores = [];
        
        // Default weights for strategy selection
        $weights = [
            'profit_potential' => 0.3,
            'market_competitiveness' => 0.25,
            'sales_velocity' => 0.2,
            'inventory_turnover' => 0.15,
            'risk_factor' => 0.1
        ];
        
        // Override with custom weights if provided
        if (isset($options['strategy_weights'])) {
            $weights = array_merge($weights, $options['strategy_weights']);
        }
        
        foreach ($strategy_results as $strategy_name => $result) {
            $score = 0;
            
            // Calculate weighted score for each strategy
            $score += $result['profit_potential'] * $weights['profit_potential'];
            $score += $result['competitiveness'] * $weights['market_competitiveness'];
            $score += $result['sales_velocity'] * $weights['sales_velocity'];
            $score += $result['inventory_impact'] * $weights['inventory_turnover'];
            $score -= $result['risk_score'] * $weights['risk_factor']; // Risk is negative
            
            $strategy_scores[$strategy_name] = $score;
        }
        
        // Return strategy with highest score
        return array_keys($strategy_scores, max($strategy_scores))[0];
    }
    
    /**
     * Apply price constraints
     */
    private function applyPriceConstraints($calculated_price, $product_data, $marketplace) {
        // Get product constraints
        $constraints = $this->getProductConstraints($product_data, $marketplace);
        
        $final_price = $calculated_price;
        
        // Apply minimum price constraint
        if (isset($constraints['min_price']) && $final_price < $constraints['min_price']) {
            $final_price = $constraints['min_price'];
        }
        
        // Apply maximum price constraint
        if (isset($constraints['max_price']) && $final_price > $constraints['max_price']) {
            $final_price = $constraints['max_price'];
        }
        
        // Apply minimum profit margin constraint
        if (isset($constraints['min_profit_margin'])) {
            $cost = $product_data['cost'] ?? 0;
            $min_price_for_margin = $cost * (1 + $constraints['min_profit_margin']);
            if ($final_price < $min_price_for_margin) {
                $final_price = $min_price_for_margin;
            }
        }
        
        // Apply maximum price change constraint
        if (isset($constraints['max_price_change_percent'])) {
            $current_price = $product_data['price'] ?? 0;
            $max_change = $current_price * $constraints['max_price_change_percent'];
            
            if (abs($final_price - $current_price) > $max_change) {
                if ($final_price > $current_price) {
                    $final_price = $current_price + $max_change;
                } else {
                    $final_price = $current_price - $max_change;
                }
            }
        }
        
        // Round to appropriate precision
        $final_price = $this->roundPrice($final_price, $marketplace);
        
        return $final_price;
    }
    
    /**
     * Calculate profit analysis
     */
    private function calculateProfitAnalysis($price, $product_data) {
        $cost = $product_data['cost'] ?? 0;
        $current_price = $product_data['price'] ?? 0;
        
        $analysis = [
            'new_profit_margin' => $cost > 0 ? (($price - $cost) / $price) * 100 : 0,
            'current_profit_margin' => $cost > 0 && $current_price > 0 ? (($current_price - $cost) / $current_price) * 100 : 0,
            'profit_per_unit' => $price - $cost,
            'current_profit_per_unit' => $current_price - $cost,
            'profit_change' => ($price - $cost) - ($current_price - $cost),
            'margin_change' => 0
        ];
        
        if ($analysis['current_profit_margin'] > 0) {
            $analysis['margin_change'] = $analysis['new_profit_margin'] - $analysis['current_profit_margin'];
        }
        
        return $analysis;
    }
    
    /**
     * Calculate confidence score for pricing decision
     */
    private function calculateConfidenceScore($strategy_results, $market_data) {
        $confidence_factors = [];
        
        // Data quality factor
        $data_quality = $this->assessDataQuality($market_data);
        $confidence_factors['data_quality'] = $data_quality;
        
        // Strategy consensus factor
        $strategy_consensus = $this->calculateStrategyConsensus($strategy_results);
        $confidence_factors['strategy_consensus'] = $strategy_consensus;
        
        // Market volatility factor
        $market_volatility = $this->calculateMarketVolatility($market_data);
        $confidence_factors['market_stability'] = 1 - $market_volatility;
        
        // Historical accuracy factor
        $historical_accuracy = $this->getHistoricalAccuracy();
        $confidence_factors['historical_accuracy'] = $historical_accuracy;
        
        // Calculate weighted confidence score
        $weights = [
            'data_quality' => 0.3,
            'strategy_consensus' => 0.25,
            'market_stability' => 0.25,
            'historical_accuracy' => 0.2
        ];
        
        $confidence_score = 0;
        foreach ($confidence_factors as $factor => $value) {
            $confidence_score += $value * $weights[$factor];
        }
        
        return min(100, max(0, $confidence_score * 100));
    }
    
    /**
     * Bulk price optimization for multiple products
     */
    public function bulkPriceOptimization($product_ids, $marketplace, $options = []) {
        $results = [];
        $batch_size = $options['batch_size'] ?? 50;
        
        // Process in batches to avoid memory issues
        $batches = array_chunk($product_ids, $batch_size);
        
        foreach ($batches as $batch) {
            foreach ($batch as $product_id) {
                $product_data = $this->getProductData($product_id);
                if ($product_data) {
                    $pricing_result = $this->calculateOptimalPrice($product_data, $marketplace, $options);
                    $results[$product_id] = $pricing_result;
                }
            }
            
            // Small delay between batches to avoid API rate limits
            usleep(100000); // 100ms
        }
        
        return $results;
    }
    
    /**
     * Schedule automatic price updates
     */
    public function scheduleAutomaticUpdates($product_id, $marketplace, $schedule_options) {
        $schedule_data = [
            'product_id' => $product_id,
            'marketplace' => $marketplace,
            'frequency' => $schedule_options['frequency'] ?? 'daily',
            'time_of_day' => $schedule_options['time'] ?? '02:00',
            'enabled' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_pricing_schedule 
            SET " . $this->buildInsertQuery($schedule_data) . "
            ON DUPLICATE KEY UPDATE 
                frequency = VALUES(frequency),
                time_of_day = VALUES(time_of_day),
                enabled = VALUES(enabled),
                updated_at = NOW()
        ");
        
        return true;
    }
    
    /**
     * Get pricing performance metrics
     */
    public function getPricingMetrics($date_range = 7) {
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total_optimizations,
                AVG(confidence_score) as avg_confidence,
                AVG(processing_time_ms) as avg_processing_time,
                AVG(price_change_percent) as avg_price_change,
                marketplace,
                DATE(created_at) as optimization_date
            FROM " . DB_PREFIX . "meschain_pricing_log 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$date_range} DAY)
            GROUP BY marketplace, DATE(created_at)
            ORDER BY optimization_date DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * Log pricing result
     */
    private function logPricingResult($product_data, $result) {
        $log_data = [
            'product_id' => $product_data['id'],
            'marketplace' => $result['marketplace'],
            'old_price' => $result['current_price'],
            'new_price' => $result['optimal_price'],
            'price_change_percent' => $result['price_change_percent'],
            'strategy_used' => $result['strategy_used'],
            'confidence_score' => $result['confidence_score'],
            'processing_time_ms' => $result['processing_time_ms'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_pricing_log 
            SET " . $this->buildInsertQuery($log_data)
        );
        
        $this->log->write('Pricing optimization completed: ' . json_encode($log_data));
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
    
    /**
     * Get product data by ID
     */
    private function getProductData($product_id) {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "product 
            WHERE product_id = '" . (int)$product_id . "'
        ");
        
        return $query->row;
    }
    
    /**
     * Calculate median value
     */
    private function calculateMedian($values) {
        sort($values);
        $count = count($values);
        $middle = floor($count / 2);
        
        if ($count % 2 == 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        } else {
            return $values[$middle];
        }
    }
    
    /**
     * Calculate variance
     */
    private function calculateVariance($values) {
        $mean = array_sum($values) / count($values);
        $variance = 0;
        
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        
        return $variance / count($values);
    }
    
    /**
     * Round price according to marketplace conventions
     */
    private function roundPrice($price, $marketplace) {
        // Different marketplaces may have different rounding conventions
        switch ($marketplace) {
            case 'trendyol':
                // Round to nearest 0.01
                return round($price, 2);
            case 'n11':
                // Round to nearest 0.05
                return round($price * 20) / 20;
            case 'amazon':
                // Round to nearest 0.01
                return round($price, 2);
            default:
                return round($price, 2);
        }
    }
    
    /**
     * Calculate price change percentage
     */
    private function calculatePriceChangePercent($new_price, $old_price) {
        if ($old_price == 0) {
            return 0;
        }
        return (($new_price - $old_price) / $old_price) * 100;
    }
}

/**
 * Abstract base class for pricing strategies
 */
abstract class PricingStrategy {
    abstract public function calculatePrice($data);
}

/**
 * Competitive pricing strategy
 */
class CompetitivePricingStrategy extends PricingStrategy {
    public function calculatePrice($data) {
        $competitor_avg = $data['competitors']['overall']['avg_price'] ?? $data['product']['price'];
        $competitive_price = $competitor_avg * 0.98; // 2% below average
        
        return [
            'price' => $competitive_price,
            'profit_potential' => 0.7,
            'competitiveness' => 0.9,
            'sales_velocity' => 0.8,
            'inventory_impact' => 0.6,
            'risk_score' => 0.3
        ];
    }
}

/**
 * Profit maximization strategy
 */
class ProfitMaximizationStrategy extends PricingStrategy {
    public function calculatePrice($data) {
        $cost = $data['product']['cost'] ?? 0;
        $target_margin = 0.4; // 40% margin
        $profit_price = $cost / (1 - $target_margin);
        
        return [
            'price' => $profit_price,
            'profit_potential' => 0.95,
            'competitiveness' => 0.5,
            'sales_velocity' => 0.4,
            'inventory_impact' => 0.3,
            'risk_score' => 0.6
        ];
    }
}

/**
 * Market penetration strategy
 */
class MarketPenetrationStrategy extends PricingStrategy {
    public function calculatePrice($data) {
        $competitor_min = $data['competitors']['overall']['min_price'] ?? $data['product']['price'];
        $penetration_price = $competitor_min * 0.95; // 5% below minimum
        
        return [
            'price' => $penetration_price,
            'profit_potential' => 0.3,
            'competitiveness' => 0.95,
            'sales_velocity' => 0.9,
            'inventory_impact' => 0.8,
            'risk_score' => 0.4
        ];
    }
}

/**
 * Dynamic demand strategy
 */
class DynamicDemandStrategy extends PricingStrategy {
    public function calculatePrice($data) {
        $base_price = $data['product']['price'];
        $elasticity = $data['demand_elasticity']['elasticity_coefficient'] ?? -1;
        
        // Adjust price based on demand elasticity
        if ($elasticity > -1) {
            // Inelastic demand - can increase price
            $dynamic_price = $base_price * 1.1;
        } else {
            // Elastic demand - should be careful with price increases
            $dynamic_price = $base_price * 0.98;
        }
        
        return [
            'price' => $dynamic_price,
            'profit_potential' => 0.8,
            'competitiveness' => 0.7,
            'sales_velocity' => 0.7,
            'inventory_impact' => 0.6,
            'risk_score' => 0.5
        ];
    }
}

/**
 * Inventory-based strategy
 */
class InventoryBasedStrategy extends PricingStrategy {
    public function calculatePrice($data) {
        $base_price = $data['product']['price'];
        $inventory_level = $data['inventory']['quantity'] ?? 0;
        $avg_sales = $data['market_data']['avg_daily_sales'] ?? 1;
        
        // Calculate days of inventory
        $days_of_inventory = $avg_sales > 0 ? $inventory_level / $avg_sales : 30;
        
        if ($days_of_inventory < 7) {
            // Low inventory - increase price
            $inventory_price = $base_price * 1.15;
        } elseif ($days_of_inventory > 60) {
            // High inventory - decrease price
            $inventory_price = $base_price * 0.9;
        } else {
            // Normal inventory
            $inventory_price = $base_price;
        }
        
        return [
            'price' => $inventory_price,
            'profit_potential' => 0.6,
            'competitiveness' => 0.6,
            'sales_velocity' => 0.8,
            'inventory_impact' => 0.9,
            'risk_score' => 0.2
        ];
    }
}
?> 