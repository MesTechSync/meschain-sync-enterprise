<?php
namespace Opencart\Catalog\Model\Extension\Meschain\Sync;
require_once(DIR_SYSTEM . 'library/meschain/sync/BaseSyncTrait.php');

/**
 * Trendyol Product Synchronization Model
 */
class Product extends \Opencart\System\Engine\Model {
    use \Meschain\Sync\BaseSyncTrait;
    
    /**
     * Add a product to the sync queue
     */
    public function addToSyncQueue($product_id, $operation = 'add') {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "trendyol_product_queue` SET 
            `opencart_product_id` = '" . (int)$product_id . "',
            `operation` = '" . $this->db->escape($operation) . "',
            `sync_status` = 'pending',
            `retry_count` = 0,
            `created_at` = NOW(),
            `updated_at` = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Process a queue item
     */
    public function processQueueItem($product_id) {
        $product = $this->getProductDetails($product_id);
        
        if (!$product) {
            $this->logError('product_sync', $product_id, 'Product not found');
            return false;
        }
        
        try {
            $trendyol_api = $this->getTrendyolApi();
            
            // Check if product already exists in Trendyol
            $existing = $this->getExistingTrendyolProduct($product_id);
            
            if ($existing) {
                // Update existing product
                return $this->updateProductInTrendyol($product, $existing);
            } else {
                // Create new product
                return $this->createProductInTrendyol($product);
            }
        } catch (\Exception $e) {
            $this->logError('product_sync', $product_id, $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get product details
     */
    private function getProductDetails($product_id) {
        $this->load->model('catalog/product');
        return $this->model_catalog_product->getProduct($product_id);
    }
    
    /**
     * Get existing Trendyol product
     */
    private function getExistingTrendyolProduct($product_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_product` 
            WHERE opencart_product_id = '" . (int)$product_id . "'
        ");
        
        return $query->row;
    }
    
    /**
     * Create a new product in Trendyol
     */
    private function createProductInTrendyol($product) {
        $log_id = $this->logEvent('product_create', $product['product_id'], [
            'name' => $product['name'],
            'model' => $product['model'],
            'status' => $product['status']
        ]);
        
        try {
            // Get required mappings
            $category_mapping = $this->getCategoryMapping($product['product_id']);
            $brand_mapping = $this->getBrandMapping($product['manufacturer_id']);
            
            if (!$category_mapping || !$brand_mapping) {
                $this->updateEventStatus($log_id, 'error', 'Missing category or brand mapping');
                return false;
            }
            
            // Format product for Trendyol
            $trendyol_product = $this->formatProductForTrendyol($product, $category_mapping, $brand_mapping);
            
            // Call Trendyol API
            $trendyol_api = $this->getTrendyolApi();
            $result = $trendyol_api->createProduct($trendyol_product);
            
            if (isset($result['success']) && $result['success']) {
                // Save relationship to database
                $this->saveProductRelationship($product['product_id'], $result['data']);
                $this->updateEventStatus($log_id, 'completed', 'Product successfully created in Trendyol');
                return true;
            } else {
                $this->updateEventStatus($log_id, 'error', $result['message'] ?? 'Unknown error');
                return false;
            }
        } catch (\Exception $e) {
            $this->updateEventStatus($log_id, 'error', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update existing product in Trendyol
     */
    private function updateProductInTrendyol($product, $existing_trendyol_product) {
        $log_id = $this->logEvent('product_update', $product['product_id'], [
            'name' => $product['name'],
            'model' => $product['model'],
            'trendyol_product_id' => $existing_trendyol_product['trendyol_product_id']
        ]);
        
        try {
            // Get required mappings
            $category_mapping = $this->getCategoryMapping($product['product_id']);
            $brand_mapping = $this->getBrandMapping($product['manufacturer_id']);
            
            if (!$category_mapping || !$brand_mapping) {
                $this->updateEventStatus($log_id, 'error', 'Missing category or brand mapping');
                return false;
            }
            
            // Format product for Trendyol
            $trendyol_product = $this->formatProductForTrendyol($product, $category_mapping, $brand_mapping);
            $trendyol_product['trendyolProductId'] = $existing_trendyol_product['trendyol_product_id'];
            
            // Call Trendyol API
            $trendyol_api = $this->getTrendyolApi();
            $result = $trendyol_api->updateProduct($trendyol_product);
            
            if (isset($result['success']) && $result['success']) {
                // Update relationship in database
                $this->updateProductRelationship($product['product_id'], $result['data']);
                $this->updateEventStatus($log_id, 'completed', 'Product successfully updated in Trendyol');
                return true;
            } else {
                $this->updateEventStatus($log_id, 'error', $result['message'] ?? 'Unknown error');
                return false;
            }
        } catch (\Exception $e) {
            $this->updateEventStatus($log_id, 'error', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get category mapping
     */
    private function getCategoryMapping($product_id) {
        // Get product categories
        $categories = $this->db->query("
            SELECT category_id 
            FROM `" . DB_PREFIX . "product_to_category` 
            WHERE product_id = '" . (int)$product_id . "'
        ")->rows;
        
        if (!$categories) {
            return null;
        }
        
        // Check for mappings for each category
        foreach ($categories as $category) {
            $mapping = $this->db->query("
                SELECT * FROM `" . DB_PREFIX . "trendyol_category_mapping` 
                WHERE opencart_category_id = '" . (int)$category['category_id'] . "'
                AND is_active = 1
            ")->row;
            
            if ($mapping) {
                return $mapping;
            }
        }
        
        return null;
    }
    
    /**
     * Get brand mapping
     */
    private function getBrandMapping($manufacturer_id) {
        if (!$manufacturer_id) {
            return null;
        }
        
        $mapping = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_brand_mapping` 
            WHERE opencart_manufacturer_id = '" . (int)$manufacturer_id . "'
            AND is_active = 1
        ")->row;
        
        return $mapping;
    }
    
    /**
     * Format product data for Trendyol
     */
    private function formatProductForTrendyol($product, $category_mapping, $brand_mapping) {
        // Get product attributes
        $this->load->model('catalog/product');
        $attributes = $this->model_catalog_product->getAttributes($product['product_id']);
        
        // Format product data according to Trendyol API format
        $trendyol_product = [
            'barcode' => $product['model'],
            'title' => $product['name'],
            'productMainId' => 'OC' . $product['product_id'],
            'brandId' => (int)$brand_mapping['trendyol_brand_id'],
            'categoryId' => (int)$category_mapping['trendyol_category_id'],
            'quantity' => (int)$product['quantity'],
            'stockCode' => $product['sku'] ?: 'OC-' . $product['product_id'],
            'dimensionalWeight' => ($product['weight'] > 0) ? (float)$product['weight'] : 1,
            'description' => $product['description'],
            'pricingType' => 'BUY_PRICE',
            'price' => [
                'originalPrice' => (float)$product['price'],
                'discountedPrice' => (float)$product['special'] > 0 ? (float)$product['special'] : (float)$product['price']
            ],
            'images' => $this->getProductImages($product['product_id']),
            'attributes' => $this->getProductAttributesForTrendyol($product['product_id'], (int)$category_mapping['trendyol_category_id'])
        ];
        
        return $trendyol_product;
    }
    
    /**
     * Get product images
     */
    private function getProductImages($product_id) {
        $images = [];
        
        // Main image
        $product_info = $this->db->query("
            SELECT image FROM `" . DB_PREFIX . "product`
            WHERE product_id = '" . (int)$product_id . "'
        ")->row;
        
        if (!empty($product_info['image'])) {
            $images[] = [
                'url' => HTTPS_CATALOG . 'image/' . $product_info['image']
            ];
        }
        
        // Additional images
        $additional_images = $this->db->query("
            SELECT image FROM `" . DB_PREFIX . "product_image`
            WHERE product_id = '" . (int)$product_id . "'
            ORDER BY sort_order ASC
        ")->rows;
        
        foreach ($additional_images as $image) {
            if (!empty($image['image'])) {
                $images[] = [
                    'url' => HTTPS_CATALOG . 'image/' . $image['image']
                ];
            }
        }
        
        return $images;
    }
    
    /**
     * Get mapped product attributes for Trendyol
     */
    private function getProductAttributesForTrendyol($product_id, $trendyol_category_id) {
        $attributes = [];
        
        // Get product attributes
        $product_attributes = $this->db->query("
            SELECT pa.attribute_id, a.attribute_group_id, ad.name as attribute_name, pa.text
            FROM `" . DB_PREFIX . "product_attribute` pa
            LEFT JOIN `" . DB_PREFIX . "attribute` a ON (pa.attribute_id = a.attribute_id)
            LEFT JOIN `" . DB_PREFIX . "attribute_description` ad ON (a.attribute_id = ad.attribute_id)
            WHERE pa.product_id = '" . (int)$product_id . "'
            AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "'
        ")->rows;
        
        if (!$product_attributes) {
            return [];
        }
        
        // Get attribute mappings for this category
        $attribute_mappings = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_attribute_mapping`
            WHERE trendyol_category_id = '" . (int)$trendyol_category_id . "'
            AND is_active = 1
        ")->rows;
        
        if (!$attribute_mappings) {
            return [];
        }
        
        // Create attribute mapping lookup
        $mapping_lookup = [];
        foreach ($attribute_mappings as $mapping) {
            $mapping_lookup[$mapping['opencart_attribute_id']] = $mapping;
        }
        
        // Map attributes
        foreach ($product_attributes as $attr) {
            if (isset($mapping_lookup[$attr['attribute_id']])) {
                $mapping = $mapping_lookup[$attr['attribute_id']];
                
                $attributes[] = [
                    'attributeId' => (int)$mapping['trendyol_attribute_id'],
                    'attributeValueId' => 0, // Custom value
                    'customAttributeValue' => $attr['text']
                ];
            }
        }
        
        return $attributes;
    }
    
    /**
     * Save product relationship in database
     */
    private function saveProductRelationship($product_id, $trendyol_data) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "trendyol_product` SET
            `opencart_product_id` = '" . (int)$product_id . "',
            `trendyol_product_id` = '" . $this->db->escape($trendyol_data['productId'] ?? '') . "',
            `barcode` = '" . $this->db->escape($trendyol_data['barcode'] ?? '') . "',
            `approved` = " . ($trendyol_data['approved'] ?? false ? '1' : '0') . ",
            `last_sync_status` = 'success',
            `last_sync_date` = NOW(),
            `created_at` = NOW(),
            `updated_at` = NOW()
        ");
    }
    
    /**
     * Update product relationship in database
     */
    private function updateProductRelationship($product_id, $trendyol_data) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "trendyol_product` SET
            `trendyol_product_id` = '" . $this->db->escape($trendyol_data['productId'] ?? '') . "',
            `barcode` = '" . $this->db->escape($trendyol_data['barcode'] ?? '') . "',
            `approved` = " . ($trendyol_data['approved'] ?? false ? '1' : '0') . ",
            `last_sync_status` = 'success',
            `last_sync_date` = NOW(),
            `updated_at` = NOW()
            WHERE opencart_product_id = '" . (int)$product_id . "'
        ");
    }
}
