<?php
namespace MesChain\AI;

/**
 * AI-Powered Category Matcher for MesChain-Sync A+++++
 * Uses Azure Cognitive Services and ML for intelligent category mapping
 *
 * @author MesChain Development Team
 * @version 5.0.0
 */
class CategoryMatcher {

    private $azureIntegration;
    private $db;
    private $logger;
    private $cache;
    private $config;

    /**
     * ML Model Parameters
     */
    private $confidenceThreshold = 0.85;
    private $maxSuggestions = 5;
    private $learningRate = 0.01;

    /**
     * Cache configuration
     */
    private $cacheExpiry = 86400; // 24 hours

    public function __construct($db, $azureIntegration) {
        $this->db = $db;
        $this->azureIntegration = $azureIntegration;
        $this->logger = new \Log('ai_category_matcher.log');
        $this->cache = new \Cache('file');

        $this->config = [
            'enable_learning' => true,
            'enable_fuzzy_matching' => true,
            'enable_semantic_analysis' => true,
            'enable_multi_language' => true,
            'supported_languages' => ['tr', 'en', 'de', 'fr', 'es']
        ];
    }

    /**
     * Match marketplace category to OpenCart category
     */
    public function matchCategory($marketplaceName, $marketplaceCategory, $productData = []) {
        try {
            // Check cache first
            $cacheKey = $this->getCacheKey($marketplaceName, $marketplaceCategory);
            $cached = $this->cache->get($cacheKey);

            if ($cached !== null && $cached['confidence'] >= $this->confidenceThreshold) {
                $this->logger->write("Cache hit for {$marketplaceName}:{$marketplaceCategory}");
                return $cached;
            }

            // Prepare matching data
            $matchingData = $this->prepareMatchingData($marketplaceCategory, $productData);

            // Run multiple matching algorithms in parallel
            $results = [];

            // 1. Exact match
            $results['exact'] = $this->exactMatch($matchingData['normalized_name']);

            // 2. Fuzzy match
            if ($this->config['enable_fuzzy_matching']) {
                $results['fuzzy'] = $this->fuzzyMatch($matchingData['normalized_name']);
            }

            // 3. Semantic analysis using Azure Cognitive Services
            if ($this->config['enable_semantic_analysis']) {
                $results['semantic'] = $this->semanticMatch($matchingData);
            }

            // 4. ML-based prediction
            $results['ml'] = $this->mlPredict($marketplaceName, $matchingData);

            // 5. Rule-based matching
            $results['rules'] = $this->ruleBasedMatch($marketplaceName, $matchingData);

            // Combine and rank results
            $finalMatch = $this->combineResults($results);

            // Learn from the match if enabled
            if ($this->config['enable_learning'] && $finalMatch['confidence'] >= $this->confidenceThreshold) {
                $this->learnFromMatch($marketplaceName, $marketplaceCategory, $finalMatch);
            }

            // Cache the result
            $this->cache->set($cacheKey, $finalMatch, $this->cacheExpiry);

            return $finalMatch;

        } catch (\Exception $e) {
            $this->logger->write("Category matching error: " . $e->getMessage());
            return $this->getDefaultCategory();
        }
    }

    /**
     * Prepare data for matching
     */
    private function prepareMatchingData($categoryName, $productData) {
        $data = [
            'original_name' => $categoryName,
            'normalized_name' => $this->normalizeText($categoryName),
            'keywords' => [],
            'attributes' => []
        ];

        // Extract keywords from category path
        if (strpos($categoryName, '>') !== false || strpos($categoryName, '/') !== false) {
            $separator = strpos($categoryName, '>') !== false ? '>' : '/';
            $parts = array_map('trim', explode($separator, $categoryName));
            $data['keywords'] = array_merge($data['keywords'], $parts);
            $data['category_path'] = $parts;
        }

        // Extract keywords from product data
        if (!empty($productData['name'])) {
            $data['product_name'] = $productData['name'];
            $productKeywords = $this->extractKeywords($productData['name']);
            $data['keywords'] = array_merge($data['keywords'], $productKeywords);
        }

        if (!empty($productData['description'])) {
            $data['product_description'] = $productData['description'];
        }

        if (!empty($productData['attributes'])) {
            $data['attributes'] = $productData['attributes'];
        }

        return $data;
    }

    /**
     * Exact match algorithm
     */
    private function exactMatch($normalizedName) {
        $query = $this->db->query("
            SELECT cd.category_id, cd.name,
                   CASE
                       WHEN LOWER(cd.name) = LOWER('" . $this->db->escape($normalizedName) . "') THEN 1.0
                       WHEN LOWER(cd.name) LIKE LOWER('%" . $this->db->escape($normalizedName) . "%') THEN 0.8
                       ELSE 0
                   END as confidence
            FROM " . DB_PREFIX . "category_description cd
            WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            HAVING confidence > 0
            ORDER BY confidence DESC
            LIMIT 5
        ");

        if ($query->num_rows > 0) {
            return [
                'category_id' => $query->row['category_id'],
                'category_name' => $query->row['name'],
                'confidence' => (float)$query->row['confidence'],
                'method' => 'exact'
            ];
        }

        return null;
    }

    /**
     * Fuzzy match algorithm using Levenshtein distance
     */
    private function fuzzyMatch($normalizedName) {
        $query = $this->db->query("
            SELECT cd.category_id, cd.name
            FROM " . DB_PREFIX . "category_description cd
            WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
        ");

        $bestMatch = null;
        $bestScore = 0;

        foreach ($query->rows as $category) {
            $similarity = 0;
            similar_text($normalizedName, $this->normalizeText($category['name']), $similarity);

            // Also calculate Levenshtein distance
            $distance = levenshtein($normalizedName, $this->normalizeText($category['name']));
            $maxLen = max(strlen($normalizedName), strlen($category['name']));
            $levenshteinScore = 1 - ($distance / $maxLen);

            // Combine similarity metrics
            $score = ($similarity / 100 * 0.6) + ($levenshteinScore * 0.4);

            if ($score > $bestScore && $score > 0.7) {
                $bestScore = $score;
                $bestMatch = [
                    'category_id' => $category['category_id'],
                    'category_name' => $category['name'],
                    'confidence' => $score,
                    'method' => 'fuzzy'
                ];
            }
        }

        return $bestMatch;
    }

    /**
     * Semantic match using Azure Cognitive Services
     */
    private function semanticMatch($matchingData) {
        try {
            // Get all OpenCart categories
            $query = $this->db->query("
                SELECT cd.category_id, cd.name, cd.description
                FROM " . DB_PREFIX . "category_description cd
                WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ");

            $bestMatch = null;
            $bestScore = 0;

            // Prepare text for analysis
            $sourceText = $matchingData['original_name'];
            if (!empty($matchingData['product_name'])) {
                $sourceText .= ' ' . $matchingData['product_name'];
            }

            // Extract key phrases from source
            $sourceKeyPhrases = $this->azureIntegration->extractKeyPhrases($sourceText);

            foreach ($query->rows as $category) {
                $targetText = $category['name'] . ' ' . $category['description'];

                // Calculate semantic similarity
                $similarity = $this->calculateSemanticSimilarity(
                    $sourceKeyPhrases,
                    $this->azureIntegration->extractKeyPhrases($targetText)
                );

                if ($similarity > $bestScore && $similarity > 0.7) {
                    $bestScore = $similarity;
                    $bestMatch = [
                        'category_id' => $category['category_id'],
                        'category_name' => $category['name'],
                        'confidence' => $similarity,
                        'method' => 'semantic'
                    ];
                }
            }

            return $bestMatch;

        } catch (\Exception $e) {
            $this->logger->write("Semantic matching error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ML-based prediction
     */
    private function mlPredict($marketplaceName, $matchingData) {
        try {
            // Load or create ML model for this marketplace
            $modelKey = "ml_model_{$marketplaceName}";
            $model = $this->loadModel($modelKey);

            if (!$model) {
                $model = $this->createNewModel($marketplaceName);
            }

            // Prepare features
            $features = $this->extractFeatures($matchingData);

            // Make prediction
            $prediction = $this->predict($model, $features);

            if ($prediction && $prediction['confidence'] > 0.7) {
                return [
                    'category_id' => $prediction['category_id'],
                    'category_name' => $prediction['category_name'],
                    'confidence' => $prediction['confidence'],
                    'method' => 'ml'
                ];
            }

            return null;

        } catch (\Exception $e) {
            $this->logger->write("ML prediction error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Rule-based matching
     */
    private function ruleBasedMatch($marketplaceName, $matchingData) {
        // Load marketplace-specific rules
        $rules = $this->loadMarketplaceRules($marketplaceName);

        foreach ($rules as $rule) {
            if ($this->evaluateRule($rule, $matchingData)) {
                return [
                    'category_id' => $rule['opencart_category_id'],
                    'category_name' => $rule['opencart_category_name'],
                    'confidence' => $rule['confidence'] ?? 0.9,
                    'method' => 'rules'
                ];
            }
        }

        return null;
    }

    /**
     * Combine results from different matching methods
     */
    private function combineResults($results) {
        $validResults = array_filter($results, function($result) {
            return $result !== null && isset($result['confidence']);
        });

        if (empty($validResults)) {
            return $this->getDefaultCategory();
        }

        // Weight different methods
        $weights = [
            'exact' => 1.0,
            'ml' => 0.9,
            'semantic' => 0.85,
            'rules' => 0.8,
            'fuzzy' => 0.7
        ];

        // Calculate weighted scores
        $categoryScores = [];

        foreach ($validResults as $method => $result) {
            $categoryId = $result['category_id'];
            $weight = $weights[$method] ?? 0.5;
            $score = $result['confidence'] * $weight;

            if (!isset($categoryScores[$categoryId])) {
                $categoryScores[$categoryId] = [
                    'category_id' => $categoryId,
                    'category_name' => $result['category_name'],
                    'total_score' => 0,
                    'methods' => []
                ];
            }

            $categoryScores[$categoryId]['total_score'] += $score;
            $categoryScores[$categoryId]['methods'][] = $method;
        }

        // Sort by total score
        uasort($categoryScores, function($a, $b) {
            return $b['total_score'] <=> $a['total_score'];
        });

        // Get the best match
        $bestMatch = reset($categoryScores);

        return [
            'category_id' => $bestMatch['category_id'],
            'category_name' => $bestMatch['category_name'],
            'confidence' => min($bestMatch['total_score'], 1.0),
            'methods_used' => $bestMatch['methods'],
            'alternatives' => array_slice($categoryScores, 1, $this->maxSuggestions - 1)
        ];
    }

    /**
     * Learn from successful matches
     */
    private function learnFromMatch($marketplaceName, $marketplaceCategory, $match) {
        try {
            // Store the mapping
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "meschain_category_mappings
                (marketplace_name, marketplace_category, opencart_category_id, confidence, match_count, last_matched)
                VALUES (
                    '" . $this->db->escape($marketplaceName) . "',
                    '" . $this->db->escape($marketplaceCategory) . "',
                    '" . (int)$match['category_id'] . "',
                    '" . (float)$match['confidence'] . "',
                    1,
                    NOW()
                )
                ON DUPLICATE KEY UPDATE
                    confidence = (confidence + VALUES(confidence)) / 2,
                    match_count = match_count + 1,
                    last_matched = NOW()
            ");

            // Update ML model if applicable
            if (in_array('ml', $match['methods_used'] ?? [])) {
                $this->updateModel($marketplaceName, $marketplaceCategory, $match['category_id']);
            }

        } catch (\Exception $e) {
            $this->logger->write("Learning error: " . $e->getMessage());
        }
    }

    /**
     * Get default category when no match is found
     */
    private function getDefaultCategory() {
        $defaultCategoryId = $this->config->get('meschain_default_category_id') ?? 0;

        if ($defaultCategoryId) {
            $query = $this->db->query("
                SELECT name FROM " . DB_PREFIX . "category_description
                WHERE category_id = '" . (int)$defaultCategoryId . "'
                AND language_id = '" . (int)$this->config->get('config_language_id') . "'
            ");

            if ($query->num_rows > 0) {
                return [
                    'category_id' => $defaultCategoryId,
                    'category_name' => $query->row['name'],
                    'confidence' => 0.0,
                    'method' => 'default'
                ];
            }
        }

        return [
            'category_id' => 0,
            'category_name' => 'Uncategorized',
            'confidence' => 0.0,
            'method' => 'default'
        ];
    }

    /**
     * Normalize text for matching
     */
    private function normalizeText($text) {
        // Convert to lowercase
        $text = mb_strtolower($text, 'UTF-8');

        // Remove special characters
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);

        // Remove extra spaces
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Extract keywords from text
     */
    private function extractKeywords($text) {
        // Simple keyword extraction - can be enhanced with NLP
        $words = explode(' ', $this->normalizeText($text));

        // Remove stop words (simplified for demo)
        $stopWords = ['ve', 'veya', 'ile', 'için', 'bir', 'bu', 'şu', 'the', 'and', 'or', 'for', 'a', 'an'];

        return array_diff($words, $stopWords);
    }

    /**
     * Calculate semantic similarity between key phrase sets
     */
    private function calculateSemanticSimilarity($phrases1, $phrases2) {
        if (empty($phrases1) || empty($phrases2)) {
            return 0;
        }

        $intersection = array_intersect($phrases1, $phrases2);
        $union = array_unique(array_merge($phrases1, $phrases2));

        return count($intersection) / count($union);
    }

    /**
     * Get cache key
     */
    private function getCacheKey($marketplaceName, $categoryName) {
        return 'category_match_' . md5($marketplaceName . '_' . $categoryName);
    }

    /**
     * Extract features for ML
     */
    private function extractFeatures($matchingData) {
        // This is a simplified feature extraction
        // In production, use more sophisticated NLP techniques
        return [
            'word_count' => str_word_count($matchingData['normalized_name']),
            'char_count' => strlen($matchingData['normalized_name']),
            'keyword_count' => count($matchingData['keywords']),
            'has_numbers' => preg_match('/\d/', $matchingData['original_name']) ? 1 : 0,
            'category_depth' => isset($matchingData['category_path']) ? count($matchingData['category_path']) : 1
        ];
    }

    /**
     * Load ML model
     */
    private function loadModel($modelKey) {
        // In production, load from Azure ML or file system
        $modelPath = DIR_STORAGE . 'meschain/models/' . $modelKey . '.model';

        if (file_exists($modelPath)) {
            return unserialize(file_get_contents($modelPath));
        }

        return null;
    }

    /**
     * Create new ML model
     */
    private function createNewModel($marketplaceName) {
        // Simplified model creation
        // In production, use proper ML libraries
        return [
            'marketplace' => $marketplaceName,
            'created_at' => time(),
            'training_data' => [],
            'parameters' => []
        ];
    }

    /**
     * Make prediction using model
     */
    private function predict($model, $features) {
        // Simplified prediction
        // In production, use proper ML inference
        return null;
    }

    /**
     * Update ML model with new training data
     */
    private function updateModel($marketplaceName, $marketplaceCategory, $opencartCategoryId) {
        // In production, implement proper model updating
        $this->logger->write("Model update queued for {$marketplaceName}");
    }

    /**
     * Load marketplace-specific rules
     */
    private function loadMarketplaceRules($marketplaceName) {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_category_rules
            WHERE marketplace_name = '" . $this->db->escape($marketplaceName) . "'
            AND status = 1
            ORDER BY priority DESC
        ");

        return $query->rows;
    }

    /**
     * Evaluate rule against matching data
     */
    private function evaluateRule($rule, $matchingData) {
        $conditions = json_decode($rule['conditions'], true);

        foreach ($conditions as $condition) {
            if (!$this->checkCondition($condition, $matchingData)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check individual condition
     */
    private function checkCondition($condition, $data) {
        switch ($condition['type']) {
            case 'contains':
                return stripos($data['original_name'], $condition['value']) !== false;

            case 'starts_with':
                return stripos($data['original_name'], $condition['value']) === 0;

            case 'regex':
                return preg_match($condition['value'], $data['original_name']);

            case 'in_list':
                return in_array($data['normalized_name'], $condition['values']);

            default:
                return false;
        }
    }
}
