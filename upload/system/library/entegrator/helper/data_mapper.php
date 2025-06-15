<?php
/**
 * Data Mapper Helper Class
 * 
 * Bu sınıf marketplace'ler arasında veri dönüşümü ve eşleştirme işlemleri sağlar.
 * Kategori eşleştirme, attribute mapping ve field transformations içerir.
 * 
 * @category   Helper
 * @package    MesChain-Sync
 * @subpackage Helper
 * @version    1.0.0
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

namespace MesChain\Library\Entegrator\Helper;

class DataMapper {
    
    private $registry;
    private $config;
    private $log;
    private $cache;
    private $db;
    
    /**
     * Constructor
     * 
     * @param object $registry OpenCart registry nesnesi
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $this->registry->get('config');
        $this->log = new \Log('data_mapper.log');
        $this->cache = $this->registry->get('cache');
        $this->db = $this->registry->get('db');
    }
    
    /**
     * Map OpenCart category to marketplace category
     * 
     * @param int $opencart_category_id OpenCart category ID
     * @param string $marketplace Marketplace name
     * @return array Mapping result
     */
    public function mapCategory($opencart_category_id, $marketplace) {
        $cache_key = "category_mapping_{$marketplace}_{$opencart_category_id}";
        $cached_mapping = $this->cache->get($cache_key);
        
        if ($cached_mapping) {
            return $cached_mapping;
        }
        
        // Get OpenCart category details
        $oc_category = $this->getOpenCartCategory($opencart_category_id);
        if (!$oc_category) {
            return [
                'success' => false,
                'error' => 'OpenCart category not found'
            ];
        }
        
        // Check for manual mapping in database
        $manual_mapping = $this->getManualCategoryMapping($opencart_category_id, $marketplace);
        if ($manual_mapping) {
            $result = [
                'success' => true,
                'marketplace_category_id' => $manual_mapping['marketplace_category_id'],
                'marketplace_category_name' => $manual_mapping['marketplace_category_name'],
                'mapping_type' => 'manual'
            ];
            
            $this->cache->set($cache_key, $result, 3600); // Cache for 1 hour
            return $result;
        }
        
        // Auto-mapping based on category name and keywords
        $auto_mapping = $this->autoMapCategory($oc_category, $marketplace);
        if ($auto_mapping['success']) {
            $this->cache->set($cache_key, $auto_mapping, 3600);
        }
        
        return $auto_mapping;
    }
    
    /**
     * Map product attributes to marketplace format
     * 
     * @param array $attributes OpenCart attributes
     * @param string $marketplace Marketplace name
     * @return array Mapped attributes
     */
    public function mapAttributes($attributes, $marketplace) {
        $mapped_attributes = [];
        
        foreach ($attributes as $attribute) {
            $mapping = $this->getAttributeMapping($attribute['attribute'], $marketplace);
            
            if ($mapping) {
                $mapped_attributes[] = [
                    'name' => $mapping['marketplace_attribute'],
                    'value' => $this->transformAttributeValue($attribute['text'], $mapping['transform_rule']),
                    'original_name' => $attribute['attribute'],
                    'original_value' => $attribute['text']
                ];
            } else {
                // Keep original if no mapping found
                $mapped_attributes[] = [
                    'name' => $attribute['attribute'],
                    'value' => $attribute['text'],
                    'original_name' => $attribute['attribute'],
                    'original_value' => $attribute['text']
                ];
            }
        }
        
        return $mapped_attributes;
    }
    
    /**
     * Map product fields to marketplace-specific format
     * 
     * @param array $product OpenCart product data
     * @param string $marketplace Marketplace name
     * @return array Mapped product data
     */
    public function mapProduct($product, $marketplace) {
        $mapped_product = $product;
        
        // Get marketplace-specific field mappings
        $field_mappings = $this->getFieldMappings($marketplace);
        
        foreach ($field_mappings as $oc_field => $mp_config) {
            if (isset($product[$oc_field])) {
                $value = $product[$oc_field];
                
                // Apply transformation rules
                if (isset($mp_config['transform'])) {
                    $value = $this->applyTransformation($value, $mp_config['transform']);
                }
                
                // Set mapped field
                $mapped_product[$mp_config['marketplace_field']] = $value;
                
                // Remove original field if different
                if ($oc_field !== $mp_config['marketplace_field'] && isset($mp_config['remove_original']) && $mp_config['remove_original']) {
                    unset($mapped_product[$oc_field]);
                }
            }
        }
        
        // Apply marketplace-specific rules
        $mapped_product = $this->applyMarketplaceRules($mapped_product, $marketplace);
        
        return $mapped_product;
    }
    
    /**
     * Map order data from marketplace to OpenCart format
     * 
     * @param array $order Marketplace order data
     * @param string $marketplace Marketplace name
     * @return array OpenCart formatted order
     */
    public function mapOrder($order, $marketplace) {
        $mapping_config = $this->getOrderMappingConfig($marketplace);
        $mapped_order = [];
        
        foreach ($mapping_config as $oc_field => $mp_field) {
            if (is_array($mp_field)) {
                // Complex mapping with nested fields
                $mapped_order[$oc_field] = $this->extractNestedValue($order, $mp_field['path']);
                
                if (isset($mp_field['transform'])) {
                    $mapped_order[$oc_field] = $this->applyTransformation($mapped_order[$oc_field], $mp_field['transform']);
                }
            } else {
                // Simple field mapping
                $mapped_order[$oc_field] = $order[$mp_field] ?? null;
            }
        }
        
        // Apply additional order processing
        $mapped_order = $this->processOrderData($mapped_order, $marketplace);
        
        return $mapped_order;
    }
    
    /**
     * Create or update category mapping
     * 
     * @param int $opencart_category_id OpenCart category ID
     * @param string $marketplace Marketplace name
     * @param string $marketplace_category_id Marketplace category ID
     * @param string $marketplace_category_name Marketplace category name
     * @return bool Success status
     */
    public function saveCategoryMapping($opencart_category_id, $marketplace, $marketplace_category_id, $marketplace_category_name) {
        try {
            // Check if mapping already exists
            $existing = $this->getManualCategoryMapping($opencart_category_id, $marketplace);
            
            if ($existing) {
                // Update existing mapping
                $this->db->query("
                    UPDATE " . DB_PREFIX . "meschain_category_mapping 
                    SET marketplace_category_id = '" . $this->db->escape($marketplace_category_id) . "',
                        marketplace_category_name = '" . $this->db->escape($marketplace_category_name) . "',
                        updated_at = NOW()
                    WHERE opencart_category_id = '" . (int)$opencart_category_id . "' 
                    AND marketplace = '" . $this->db->escape($marketplace) . "'
                ");
            } else {
                // Create new mapping
                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "meschain_category_mapping 
                    (opencart_category_id, marketplace, marketplace_category_id, marketplace_category_name, created_at, updated_at)
                    VALUES (
                        '" . (int)$opencart_category_id . "',
                        '" . $this->db->escape($marketplace) . "',
                        '" . $this->db->escape($marketplace_category_id) . "',
                        '" . $this->db->escape($marketplace_category_name) . "',
                        NOW(),
                        NOW()
                    )
                ");
            }
            
            // Clear cache for this mapping
            $cache_key = "category_mapping_{$marketplace}_{$opencart_category_id}";
            $this->cache->delete($cache_key);
            
            $this->log->write("Category mapping saved: OC {$opencart_category_id} -> {$marketplace} {$marketplace_category_id}");
            
            return true;
            
        } catch (Exception $e) {
            $this->log->write("Error saving category mapping: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get category mapping statistics
     * 
     * @param string $marketplace Marketplace name
     * @return array Statistics
     */
    public function getCategoryMappingStats($marketplace) {
        // Total OpenCart categories
        $total_categories = $this->db->query("
            SELECT COUNT(*) as total 
            FROM " . DB_PREFIX . "category 
            WHERE status = 1
        ")->row['total'];
        
        // Mapped categories for marketplace
        $mapped_categories = $this->db->query("
            SELECT COUNT(*) as mapped 
            FROM " . DB_PREFIX . "meschain_category_mapping 
            WHERE marketplace = '" . $this->db->escape($marketplace) . "'
        ")->row['mapped'];
        
        // Categories with products
        $categories_with_products = $this->db->query("
            SELECT COUNT(DISTINCT category_id) as with_products
            FROM " . DB_PREFIX . "product_to_category ptc
            JOIN " . DB_PREFIX . "product p ON ptc.product_id = p.product_id
            WHERE p.status = 1
        ")->row['with_products'];
        
        $mapping_coverage = $total_categories > 0 ? round(($mapped_categories / $total_categories) * 100, 2) : 0;
        
        return [
            'total_categories' => $total_categories,
            'mapped_categories' => $mapped_categories,
            'unmapped_categories' => $total_categories - $mapped_categories,
            'categories_with_products' => $categories_with_products,
            'mapping_coverage' => $mapping_coverage
        ];
    }
    
    /**
     * Get suggested category mappings based on AI/ML algorithms
     * 
     * @param string $marketplace Marketplace name
     * @param int $limit Number of suggestions
     * @return array Suggested mappings
     */
    public function getSuggestedMappings($marketplace, $limit = 10) {
        // Get unmapped categories with products
        $unmapped_categories = $this->db->query("
            SELECT DISTINCT c.category_id, cd.name, cd.description
            FROM " . DB_PREFIX . "category c
            JOIN " . DB_PREFIX . "category_description cd ON c.category_id = cd.category_id
            JOIN " . DB_PREFIX . "product_to_category ptc ON c.category_id = ptc.category_id
            JOIN " . DB_PREFIX . "product p ON ptc.product_id = p.product_id
            LEFT JOIN " . DB_PREFIX . "meschain_category_mapping mcm ON c.category_id = mcm.opencart_category_id AND mcm.marketplace = '" . $this->db->escape($marketplace) . "'
            WHERE c.status = 1 
            AND p.status = 1 
            AND mcm.id IS NULL
            AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ORDER BY c.category_id
            LIMIT " . (int)$limit . "
        ");
        
        $suggestions = [];
        
        foreach ($unmapped_categories->rows as $category) {
            $auto_mapping = $this->autoMapCategory($category, $marketplace);
            
            if ($auto_mapping['success']) {
                $suggestions[] = [
                    'opencart_category_id' => $category['category_id'],
                    'opencart_category_name' => $category['name'],
                    'suggested_marketplace_category_id' => $auto_mapping['marketplace_category_id'],
                    'suggested_marketplace_category_name' => $auto_mapping['marketplace_category_name'],
                    'confidence' => $auto_mapping['confidence'] ?? 0,
                    'reasoning' => $auto_mapping['reasoning'] ?? ''
                ];
            }
        }
        
        // Sort by confidence score
        usort($suggestions, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });
        
        return $suggestions;
    }
    
    /**
     * Auto-map category based on name similarity and keywords
     * 
     * @param array $oc_category OpenCart category data
     * @param string $marketplace Marketplace name
     * @return array Auto-mapping result
     */
    private function autoMapCategory($oc_category, $marketplace) {
        $marketplace_categories = $this->getMarketplaceCategories($marketplace);
        
        if (empty($marketplace_categories)) {
            return [
                'success' => false,
                'error' => 'No marketplace categories available'
            ];
        }
        
        $category_name = strtolower($oc_category['name']);
        $best_match = null;
        $best_score = 0;
        
        foreach ($marketplace_categories as $mp_category) {
            $mp_name = strtolower($mp_category['name']);
            
            // Calculate similarity score
            $score = 0;
            
            // Exact match
            if ($category_name === $mp_name) {
                $score = 100;
            }
            // Substring match
            elseif (strpos($mp_name, $category_name) !== false || strpos($category_name, $mp_name) !== false) {
                $score = 80;
            }
            // Similar text
            else {
                similar_text($category_name, $mp_name, $percent);
                $score = $percent;
            }
            
            // Keyword matching bonus
            $keywords = $this->extractKeywords($category_name);
            $mp_keywords = $this->extractKeywords($mp_name);
            $keyword_matches = count(array_intersect($keywords, $mp_keywords));
            if ($keyword_matches > 0) {
                $score += $keyword_matches * 10;
            }
            
            if ($score > $best_score && $score >= 60) { // Minimum 60% similarity
                $best_score = $score;
                $best_match = $mp_category;
            }
        }
        
        if ($best_match) {
            return [
                'success' => true,
                'marketplace_category_id' => $best_match['id'],
                'marketplace_category_name' => $best_match['name'],
                'confidence' => $best_score,
                'mapping_type' => 'auto',
                'reasoning' => "Similarity score: {$best_score}%"
            ];
        }
        
        return [
            'success' => false,
            'error' => 'No suitable category match found'
        ];
    }
    
    /**
     * Get OpenCart category details
     * 
     * @param int $category_id Category ID
     * @return array|null Category data
     */
    private function getOpenCartCategory($category_id) {
        $query = $this->db->query("
            SELECT c.*, cd.name, cd.description
            FROM " . DB_PREFIX . "category c
            JOIN " . DB_PREFIX . "category_description cd ON c.category_id = cd.category_id
            WHERE c.category_id = '" . (int)$category_id . "'
            AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
        ");
        
        return $query->num_rows ? $query->row : null;
    }
    
    /**
     * Get manual category mapping from database
     * 
     * @param int $opencart_category_id OpenCart category ID
     * @param string $marketplace Marketplace name
     * @return array|null Mapping data
     */
    private function getManualCategoryMapping($opencart_category_id, $marketplace) {
        $query = $this->db->query("
            SELECT * 
            FROM " . DB_PREFIX . "meschain_category_mapping 
            WHERE opencart_category_id = '" . (int)$opencart_category_id . "' 
            AND marketplace = '" . $this->db->escape($marketplace) . "'
        ");
        
        return $query->num_rows ? $query->row : null;
    }
    
    /**
     * Get marketplace categories (cached)
     * 
     * @param string $marketplace Marketplace name
     * @return array Marketplace categories
     */
    private function getMarketplaceCategories($marketplace) {
        $cache_key = "marketplace_categories_{$marketplace}";
        $cached_categories = $this->cache->get($cache_key);
        
        if ($cached_categories) {
            return $cached_categories;
        }
        
        // This would typically fetch from marketplace API or local database
        // For now, return predefined categories
        $categories = $this->getPredefinedCategories($marketplace);
        
        $this->cache->set($cache_key, $categories, 86400); // Cache for 24 hours
        
        return $categories;
    }
    
    /**
     * Get predefined categories for marketplace
     * 
     * @param string $marketplace Marketplace name
     * @return array Categories
     */
    private function getPredefinedCategories($marketplace) {
        $categories = [
            'amazon' => [
                ['id' => '15684181', 'name' => 'Electronics'],
                ['id' => '1036592', 'name' => 'Clothing & Accessories'],
                ['id' => '165793011', 'name' => 'Home & Kitchen'],
                ['id' => '3760931', 'name' => 'Sports & Outdoors'],
                ['id' => '284507', 'name' => 'Books'],
            ],
            'ebay' => [
                ['id' => '9355', 'name' => 'Electronics'],
                ['id' => '11450', 'name' => 'Clothing, Shoes & Accessories'],
                ['id' => '11700', 'name' => 'Home & Garden'],
                ['id' => '888', 'name' => 'Sports & Recreation'],
                ['id' => '267', 'name' => 'Books'],
            ],
            'hepsiburada' => [
                ['id' => '371', 'name' => 'Elektronik'],
                ['id' => '1', 'name' => 'Moda'],
                ['id' => '2', 'name' => 'Ev & Yaşam'],
                ['id' => '3', 'name' => 'Spor & Outdoor'],
                ['id' => '4', 'name' => 'Kitap'],
            ]
        ];
        
        return $categories[$marketplace] ?? [];
    }
    
    /**
     * Extract keywords from category name
     * 
     * @param string $text Category name
     * @return array Keywords
     */
    private function extractKeywords($text) {
        // Remove common stop words
        $stop_words = ['and', 've', 'ile', 'için', 'den', 'dan', 'a', 'an', 'the'];
        
        $words = preg_split('/[\s\-_&]+/', strtolower($text));
        $keywords = array_filter($words, function($word) use ($stop_words) {
            return strlen($word) > 2 && !in_array($word, $stop_words);
        });
        
        return array_values($keywords);
    }
    
    /**
     * Get attribute mapping configuration
     * 
     * @param string $attribute_name Attribute name
     * @param string $marketplace Marketplace name
     * @return array|null Mapping configuration
     */
    private function getAttributeMapping($attribute_name, $marketplace) {
        // This would typically come from database or configuration
        $mappings = [
            'amazon' => [
                'Renk' => ['marketplace_attribute' => 'Color', 'transform_rule' => 'none'],
                'Beden' => ['marketplace_attribute' => 'Size', 'transform_rule' => 'none'],
                'Marka' => ['marketplace_attribute' => 'Brand', 'transform_rule' => 'none'],
            ],
            'ebay' => [
                'Renk' => ['marketplace_attribute' => 'Color', 'transform_rule' => 'none'],
                'Beden' => ['marketplace_attribute' => 'Size', 'transform_rule' => 'none'],
                'Marka' => ['marketplace_attribute' => 'Brand', 'transform_rule' => 'none'],
            ]
        ];
        
        return $mappings[$marketplace][$attribute_name] ?? null;
    }
    
    /**
     * Transform attribute value based on rules
     * 
     * @param string $value Original value
     * @param string $rule Transformation rule
     * @return string Transformed value
     */
    private function transformAttributeValue($value, $rule) {
        switch ($rule) {
            case 'uppercase':
                return strtoupper($value);
            case 'lowercase':
                return strtolower($value);
            case 'capitalize':
                return ucwords(strtolower($value));
            case 'none':
            default:
                return $value;
        }
    }
    
    /**
     * Get field mappings for marketplace
     * 
     * @param string $marketplace Marketplace name
     * @return array Field mappings
     */
    private function getFieldMappings($marketplace) {
        $mappings = [
            'amazon' => [
                'name' => ['marketplace_field' => 'title', 'transform' => 'none'],
                'description' => ['marketplace_field' => 'description', 'transform' => 'strip_tags'],
                'model' => ['marketplace_field' => 'model_number', 'transform' => 'none'],
            ],
            'ebay' => [
                'name' => ['marketplace_field' => 'title', 'transform' => 'none'],
                'description' => ['marketplace_field' => 'description', 'transform' => 'strip_tags'],
                'model' => ['marketplace_field' => 'mpn', 'transform' => 'none'],
            ]
        ];
        
        return $mappings[$marketplace] ?? [];
    }
    
    /**
     * Apply transformation to value
     * 
     * @param mixed $value Value to transform
     * @param string $transformation Transformation type
     * @return mixed Transformed value
     */
    private function applyTransformation($value, $transformation) {
        switch ($transformation) {
            case 'strip_tags':
                return strip_tags($value);
            case 'uppercase':
                return strtoupper($value);
            case 'lowercase':
                return strtolower($value);
            case 'trim':
                return trim($value);
            case 'none':
            default:
                return $value;
        }
    }
    
    /**
     * Apply marketplace-specific business rules
     * 
     * @param array $product Product data
     * @param string $marketplace Marketplace name
     * @return array Modified product data
     */
    private function applyMarketplaceRules($product, $marketplace) {
        switch ($marketplace) {
            case 'amazon':
                // Amazon requires certain fields
                if (empty($product['brand'])) {
                    $product['brand'] = 'Generic';
                }
                break;
                
            case 'ebay':
                // eBay title length limit
                if (isset($product['title']) && strlen($product['title']) > 80) {
                    $product['title'] = substr($product['title'], 0, 77) . '...';
                }
                break;
        }
        
        return $product;
    }
    
    /**
     * Get order mapping configuration for marketplace
     * 
     * @param string $marketplace Marketplace name
     * @return array Order mapping configuration
     */
    private function getOrderMappingConfig($marketplace) {
        $configs = [
            'amazon' => [
                'order_id' => 'AmazonOrderId',
                'total' => ['path' => 'OrderTotal.Amount', 'transform' => 'float'],
                'currency' => ['path' => 'OrderTotal.CurrencyCode'],
                'date_added' => ['path' => 'PurchaseDate', 'transform' => 'datetime'],
            ],
            'ebay' => [
                'order_id' => 'orderId',
                'total' => ['path' => 'pricingSummary.total.value', 'transform' => 'float'],
                'currency' => ['path' => 'pricingSummary.total.currency'],
                'date_added' => ['path' => 'creationDate', 'transform' => 'datetime'],
            ]
        ];
        
        return $configs[$marketplace] ?? [];
    }
    
    /**
     * Extract nested value from array using dot notation
     * 
     * @param array $data Data array
     * @param string $path Dot notation path
     * @return mixed Extracted value
     */
    private function extractNestedValue($data, $path) {
        $keys = explode('.', $path);
        $value = $data;
        
        foreach ($keys as $key) {
            if (is_array($value) && isset($value[$key])) {
                $value = $value[$key];
            } else {
                return null;
            }
        }
        
        return $value;
    }
    
    /**
     * Process order data after mapping
     * 
     * @param array $order Mapped order data
     * @param string $marketplace Marketplace name
     * @return array Processed order data
     */
    private function processOrderData($order, $marketplace) {
        // Convert datetime formats
        if (isset($order['date_added'])) {
            $order['date_added'] = date('Y-m-d H:i:s', strtotime($order['date_added']));
        }
        
        // Ensure required fields have default values
        $order['store_id'] = 0;
        $order['customer_id'] = 0;
        $order['language_id'] = $this->config->get('config_language_id');
        
        return $order;
    }
} 