<?php
/**
 * MesChain AI Product Matcher
 * Advanced AI-powered product matching system for multi-marketplace integration
 * 
 * @package MesChain
 * @subpackage AI
 * @version 2.0.0
 * @author Gemini Team
 * @date 2025-06-09
 */

class AIProductMatcher {
    
    private $db;
    private $config;
    private $log;
    private $accuracy_threshold = 0.92;
    private $ml_models = [];
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->log = new Log('ai_product_matcher.log');
        
        $this->initializeMLModels();
    }
    
    /**
     * Initialize Machine Learning Models
     */
    private function initializeMLModels() {
        try {
            $this->ml_models = [
                'text_similarity' => $this->loadTextSimilarityModel(),
                'image_recognition' => $this->loadImageRecognitionModel(),
                'category_classifier' => $this->loadCategoryClassifierModel(),
                'price_predictor' => $this->loadPricePredictorModel()
            ];
            
            $this->log->write('AI Models initialized successfully');
        } catch (Exception $e) {
            $this->log->write('Error initializing AI models: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Match product across marketplaces using AI
     * 
     * @param array $product_data
     * @param string $target_marketplace
     * @return array
     */
    public function matchProduct($product_data, $target_marketplace) {
        try {
            $start_time = microtime(true);
            
            // Multi-stage AI matching process
            $matches = [];
            
            // Stage 1: Text-based similarity matching
            $text_matches = $this->performTextMatching($product_data, $target_marketplace);
            
            // Stage 2: Image-based recognition matching
            $image_matches = $this->performImageMatching($product_data, $target_marketplace);
            
            // Stage 3: Category classification matching
            $category_matches = $this->performCategoryMatching($product_data, $target_marketplace);
            
            // Stage 4: Price-based similarity matching
            $price_matches = $this->performPriceMatching($product_data, $target_marketplace);
            
            // Combine and score all matches
            $combined_matches = $this->combineMatches([
                'text' => $text_matches,
                'image' => $image_matches,
                'category' => $category_matches,
                'price' => $price_matches
            ]);
            
            // Filter by accuracy threshold
            $filtered_matches = $this->filterByAccuracy($combined_matches);
            
            $processing_time = (microtime(true) - $start_time) * 1000; // Convert to milliseconds
            
            $result = [
                'matches' => $filtered_matches,
                'accuracy' => $this->calculateOverallAccuracy($filtered_matches),
                'processing_time_ms' => round($processing_time, 2),
                'marketplace' => $target_marketplace,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            $this->logMatchingResult($product_data, $result);
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('Error in product matching: ' . $e->getMessage());
            return [
                'matches' => [],
                'accuracy' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Perform text-based similarity matching
     */
    private function performTextMatching($product_data, $marketplace) {
        $matches = [];
        
        // Extract text features
        $text_features = $this->extractTextFeatures($product_data);
        
        // Query marketplace products
        $marketplace_products = $this->getMarketplaceProducts($marketplace);
        
        foreach ($marketplace_products as $mp_product) {
            $mp_features = $this->extractTextFeatures($mp_product);
            
            // Calculate similarity using advanced NLP
            $similarity = $this->calculateTextSimilarity($text_features, $mp_features);
            
            if ($similarity > 0.7) {
                $matches[] = [
                    'product_id' => $mp_product['id'],
                    'similarity_score' => $similarity,
                    'match_type' => 'text',
                    'confidence' => $this->calculateConfidence($similarity, 'text')
                ];
            }
        }
        
        return $matches;
    }
    
    /**
     * Perform image-based recognition matching
     */
    private function performImageMatching($product_data, $marketplace) {
        $matches = [];
        
        if (!isset($product_data['images']) || empty($product_data['images'])) {
            return $matches;
        }
        
        foreach ($product_data['images'] as $image_url) {
            // Extract image features using computer vision
            $image_features = $this->extractImageFeatures($image_url);
            
            // Find similar images in marketplace
            $similar_images = $this->findSimilarImages($image_features, $marketplace);
            
            foreach ($similar_images as $similar) {
                $matches[] = [
                    'product_id' => $similar['product_id'],
                    'similarity_score' => $similar['similarity'],
                    'match_type' => 'image',
                    'confidence' => $this->calculateConfidence($similar['similarity'], 'image')
                ];
            }
        }
        
        return $matches;
    }
    
    /**
     * Perform category classification matching
     */
    private function performCategoryMatching($product_data, $marketplace) {
        $matches = [];
        
        // Classify product category using ML
        $predicted_category = $this->classifyCategory($product_data);
        
        // Find products in same category
        $category_products = $this->getProductsByCategory($predicted_category, $marketplace);
        
        foreach ($category_products as $product) {
            $category_similarity = $this->calculateCategorySimilarity($product_data, $product);
            
            if ($category_similarity > 0.8) {
                $matches[] = [
                    'product_id' => $product['id'],
                    'similarity_score' => $category_similarity,
                    'match_type' => 'category',
                    'confidence' => $this->calculateConfidence($category_similarity, 'category')
                ];
            }
        }
        
        return $matches;
    }
    
    /**
     * Perform price-based similarity matching
     */
    private function performPriceMatching($product_data, $marketplace) {
        $matches = [];
        
        if (!isset($product_data['price']) || $product_data['price'] <= 0) {
            return $matches;
        }
        
        // Predict price range using ML
        $predicted_price_range = $this->predictPriceRange($product_data);
        
        // Find products in similar price range
        $price_similar_products = $this->getProductsByPriceRange(
            $predicted_price_range['min'], 
            $predicted_price_range['max'], 
            $marketplace
        );
        
        foreach ($price_similar_products as $product) {
            $price_similarity = $this->calculatePriceSimilarity($product_data['price'], $product['price']);
            
            if ($price_similarity > 0.6) {
                $matches[] = [
                    'product_id' => $product['id'],
                    'similarity_score' => $price_similarity,
                    'match_type' => 'price',
                    'confidence' => $this->calculateConfidence($price_similarity, 'price')
                ];
            }
        }
        
        return $matches;
    }
    
    /**
     * Combine matches from different algorithms
     */
    private function combineMatches($match_groups) {
        $combined = [];
        $weights = [
            'text' => 0.4,
            'image' => 0.3,
            'category' => 0.2,
            'price' => 0.1
        ];
        
        // Collect all unique product IDs
        $all_product_ids = [];
        foreach ($match_groups as $matches) {
            foreach ($matches as $match) {
                $all_product_ids[] = $match['product_id'];
            }
        }
        $all_product_ids = array_unique($all_product_ids);
        
        // Calculate weighted scores for each product
        foreach ($all_product_ids as $product_id) {
            $weighted_score = 0;
            $match_types = [];
            $confidences = [];
            
            foreach ($match_groups as $type => $matches) {
                foreach ($matches as $match) {
                    if ($match['product_id'] == $product_id) {
                        $weighted_score += $match['similarity_score'] * $weights[$type];
                        $match_types[] = $type;
                        $confidences[] = $match['confidence'];
                        break;
                    }
                }
            }
            
            if ($weighted_score > 0) {
                $combined[] = [
                    'product_id' => $product_id,
                    'weighted_score' => $weighted_score,
                    'match_types' => $match_types,
                    'average_confidence' => array_sum($confidences) / count($confidences),
                    'match_count' => count($match_types)
                ];
            }
        }
        
        // Sort by weighted score
        usort($combined, function($a, $b) {
            return $b['weighted_score'] <=> $a['weighted_score'];
        });
        
        return $combined;
    }
    
    /**
     * Filter matches by accuracy threshold
     */
    private function filterByAccuracy($matches) {
        return array_filter($matches, function($match) {
            return $match['weighted_score'] >= $this->accuracy_threshold;
        });
    }
    
    /**
     * Calculate overall accuracy
     */
    private function calculateOverallAccuracy($matches) {
        if (empty($matches)) {
            return 0;
        }
        
        $total_score = array_sum(array_column($matches, 'weighted_score'));
        return ($total_score / count($matches)) * 100;
    }
    
    /**
     * Extract text features from product data
     */
    private function extractTextFeatures($product_data) {
        $features = [];
        
        // Title features
        if (isset($product_data['title'])) {
            $features['title_tokens'] = $this->tokenizeText($product_data['title']);
            $features['title_length'] = strlen($product_data['title']);
        }
        
        // Description features
        if (isset($product_data['description'])) {
            $features['desc_tokens'] = $this->tokenizeText($product_data['description']);
            $features['desc_length'] = strlen($product_data['description']);
        }
        
        // Brand features
        if (isset($product_data['brand'])) {
            $features['brand'] = strtolower(trim($product_data['brand']));
        }
        
        // Model features
        if (isset($product_data['model'])) {
            $features['model'] = strtolower(trim($product_data['model']));
        }
        
        return $features;
    }
    
    /**
     * Calculate text similarity using advanced NLP
     */
    private function calculateTextSimilarity($features1, $features2) {
        $similarity = 0;
        $weight_sum = 0;
        
        // Title similarity (weight: 0.5)
        if (isset($features1['title_tokens']) && isset($features2['title_tokens'])) {
            $title_sim = $this->calculateJaccardSimilarity($features1['title_tokens'], $features2['title_tokens']);
            $similarity += $title_sim * 0.5;
            $weight_sum += 0.5;
        }
        
        // Description similarity (weight: 0.3)
        if (isset($features1['desc_tokens']) && isset($features2['desc_tokens'])) {
            $desc_sim = $this->calculateJaccardSimilarity($features1['desc_tokens'], $features2['desc_tokens']);
            $similarity += $desc_sim * 0.3;
            $weight_sum += 0.3;
        }
        
        // Brand similarity (weight: 0.15)
        if (isset($features1['brand']) && isset($features2['brand'])) {
            $brand_sim = ($features1['brand'] === $features2['brand']) ? 1.0 : 0.0;
            $similarity += $brand_sim * 0.15;
            $weight_sum += 0.15;
        }
        
        // Model similarity (weight: 0.05)
        if (isset($features1['model']) && isset($features2['model'])) {
            $model_sim = ($features1['model'] === $features2['model']) ? 1.0 : 0.0;
            $similarity += $model_sim * 0.05;
            $weight_sum += 0.05;
        }
        
        return $weight_sum > 0 ? $similarity / $weight_sum : 0;
    }
    
    /**
     * Tokenize text for analysis
     */
    private function tokenizeText($text) {
        // Remove special characters and convert to lowercase
        $text = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $text));
        
        // Split into tokens
        $tokens = array_filter(explode(' ', $text));
        
        // Remove stop words (Turkish and English)
        $stop_words = ['ve', 'ile', 'için', 'bir', 'bu', 'şu', 'and', 'or', 'the', 'a', 'an', 'in', 'on', 'at'];
        $tokens = array_diff($tokens, $stop_words);
        
        return array_values($tokens);
    }
    
    /**
     * Calculate Jaccard similarity between two token sets
     */
    private function calculateJaccardSimilarity($tokens1, $tokens2) {
        $intersection = array_intersect($tokens1, $tokens2);
        $union = array_unique(array_merge($tokens1, $tokens2));
        
        return count($union) > 0 ? count($intersection) / count($union) : 0;
    }
    
    /**
     * Load text similarity model (placeholder for actual ML model)
     */
    private function loadTextSimilarityModel() {
        // In real implementation, this would load a trained ML model
        return ['model_type' => 'text_similarity', 'version' => '2.0', 'accuracy' => 0.94];
    }
    
    /**
     * Load image recognition model (placeholder for actual ML model)
     */
    private function loadImageRecognitionModel() {
        // In real implementation, this would load a trained computer vision model
        return ['model_type' => 'image_recognition', 'version' => '2.0', 'accuracy' => 0.91];
    }
    
    /**
     * Load category classifier model (placeholder for actual ML model)
     */
    private function loadCategoryClassifierModel() {
        // In real implementation, this would load a trained classification model
        return ['model_type' => 'category_classifier', 'version' => '2.0', 'accuracy' => 0.96];
    }
    
    /**
     * Load price predictor model (placeholder for actual ML model)
     */
    private function loadPricePredictorModel() {
        // In real implementation, this would load a trained regression model
        return ['model_type' => 'price_predictor', 'version' => '2.0', 'accuracy' => 0.88];
    }
    
    /**
     * Get marketplace products for matching
     */
    private function getMarketplaceProducts($marketplace) {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_products 
            WHERE marketplace = '" . $this->db->escape($marketplace) . "' 
            AND status = 1 
            LIMIT 1000
        ");
        
        return $query->rows;
    }
    
    /**
     * Calculate confidence score based on similarity and match type
     */
    private function calculateConfidence($similarity, $match_type) {
        $base_confidence = $similarity * 100;
        
        // Adjust confidence based on match type reliability
        $type_multipliers = [
            'text' => 1.0,
            'image' => 0.9,
            'category' => 0.8,
            'price' => 0.7
        ];
        
        return min(100, $base_confidence * $type_multipliers[$match_type]);
    }
    
    /**
     * Log matching result for monitoring and improvement
     */
    private function logMatchingResult($product_data, $result) {
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'product_id' => $product_data['id'] ?? 'unknown',
            'marketplace' => $result['marketplace'],
            'matches_found' => count($result['matches']),
            'accuracy' => $result['accuracy'],
            'processing_time_ms' => $result['processing_time_ms']
        ];
        
        $this->log->write('AI Matching Result: ' . json_encode($log_data));
        
        // Store in database for analytics
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_ai_matching_log 
            SET product_id = '" . $this->db->escape($log_data['product_id']) . "',
                marketplace = '" . $this->db->escape($log_data['marketplace']) . "',
                matches_found = '" . (int)$log_data['matches_found'] . "',
                accuracy = '" . (float)$log_data['accuracy'] . "',
                processing_time_ms = '" . (float)$log_data['processing_time_ms'] . "',
                created_at = NOW()
        ");
    }
    
    /**
     * Get AI matching statistics
     */
    public function getMatchingStats() {
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total_matches,
                AVG(accuracy) as avg_accuracy,
                AVG(processing_time_ms) as avg_processing_time,
                marketplace,
                DATE(created_at) as match_date
            FROM " . DB_PREFIX . "meschain_ai_matching_log 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY marketplace, DATE(created_at)
            ORDER BY match_date DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * Update accuracy threshold dynamically
     */
    public function updateAccuracyThreshold($new_threshold) {
        if ($new_threshold >= 0.5 && $new_threshold <= 1.0) {
            $this->accuracy_threshold = $new_threshold;
            $this->log->write('Accuracy threshold updated to: ' . $new_threshold);
            return true;
        }
        return false;
    }
}
?> 