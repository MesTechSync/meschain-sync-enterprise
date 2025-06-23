<?php
namespace MesChain\Helper;

/**
 * MesChain Product Helper
 * 
 * @package    MesChain Sync
 * @version    2.0.0
 * @author     MesChain Development Team
 * @copyright  2024 MesChain
 * @license    MIT License
 */

class Product {
    
    private $registry;
    private $db;
    private $config;
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
    }
    
    /**
     * Prepare product data for marketplace sync
     */
    public function prepareForMarketplace(array $product, string $marketplace = 'trendyol'): array {
        $prepared = [];
        
        switch ($marketplace) {
            case 'trendyol':
                $prepared = $this->prepareForTrendyol($product);
                break;
            case 'amazon':
                $prepared = $this->prepareForAmazon($product);
                break;
            case 'hepsiburada':
                $prepared = $this->prepareForHepsiburada($product);
                break;
            default:
                $prepared = $product; // Return as-is for unknown marketplaces
                break;
        }
        
        return $prepared;
    }
    
    /**
     * Prepare product for Trendyol
     */
    private function prepareForTrendyol(array $product): array {
        // Get product descriptions
        $descriptions = $this->getProductDescriptions($product['product_id']);
        
        // Get product images
        $images = $this->getProductImages($product['product_id']);
        
        // Get product attributes
        $attributes = $this->getProductAttributes($product['product_id']);
        
        // Get product categories
        $categories = $this->getProductCategories($product['product_id']);
        
        return [
            'barcode' => $product['sku'] ?: $product['model'],
            'title' => $descriptions[1]['name'] ?? $product['name'] ?? '',
            'productMainId' => $product['model'],
            'brandId' => $this->getBrandId($product),
            'categoryId' => $this->getTrendyolCategoryId($categories),
            'quantity' => (int)$product['quantity'],
            'listPrice' => (float)$product['price'],
            'salePrice' => $this->getSalePrice($product),
            'vatRate' => $this->getVatRate($product),
            'dimensionalWeight' => $this->getDimensionalWeight($product),
            'description' => $this->cleanDescription($descriptions[1]['description'] ?? ''),
            'images' => array_slice($images, 0, 8), // Trendyol max 8 images
            'attributes' => $this->formatTrendyolAttributes($attributes),
            'productContentDetails' => $this->getProductContentDetails($product, $attributes)
        ];
    }
    
    /**
     * Prepare product for Amazon
     */
    private function prepareForAmazon(array $product): array {
        $descriptions = $this->getProductDescriptions($product['product_id']);
        $images = $this->getProductImages($product['product_id']);
        $attributes = $this->getProductAttributes($product['product_id']);
        
        return [
            'sku' => $product['sku'] ?: $product['model'],
            'product-id' => $product['ean'] ?: $product['upc'] ?: $product['model'],
            'product-id-type' => $this->getProductIdType($product),
            'title' => $descriptions[1]['name'] ?? $product['name'] ?? '',
            'description' => $this->cleanDescription($descriptions[1]['description'] ?? ''),
            'listing-price' => (float)$product['price'],
            'shipping-price' => $this->getShippingPrice($product),
            'quantity' => (int)$product['quantity'],
            'images' => array_slice($images, 0, 9), // Amazon max 9 images
            'product_type' => $this->getAmazonProductType($product),
            'brand_name' => $this->getBrandName($product),
            'manufacturer' => $this->getManufacturer($product)
        ];
    }
    
    /**
     * Prepare product for Hepsiburada
     */
    private function prepareForHepsiburada(array $product): array {
        $descriptions = $this->getProductDescriptions($product['product_id']);
        $images = $this->getProductImages($product['product_id']);
        $attributes = $this->getProductAttributes($product['product_id']);
        
        return [
            'merchantSku' => $product['sku'] ?: $product['model'],
            'VatRate' => $this->getVatRate($product),
            'Barcode' => $product['ean'] ?: $product['upc'] ?: $product['model'],
            'Title' => $descriptions[1]['name'] ?? $product['name'] ?? '',
            'ProductDescription' => $this->cleanDescription($descriptions[1]['description'] ?? ''),
            'BrandName' => $this->getBrandName($product),
            'CategoryId' => $this->getHepsiburadaCategoryId($product),
            'Price' => (float)$product['price'],
            'AvailableStock' => (int)$product['quantity'],
            'Images' => array_slice($images, 0, 5), // Hepsiburada max 5 images
            'Attributes' => $this->formatHepsiburadaAttributes($attributes)
        ];
    }
    
    /**
     * Get product descriptions in all languages
     */
    private function getProductDescriptions(int $product_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_description` 
            WHERE product_id = '" . (int)$product_id . "'");
        
        $descriptions = [];
        foreach ($query->rows as $row) {
            $descriptions[$row['language_id']] = $row;
        }
        
        return $descriptions;
    }
    
    /**
     * Get product images
     */
    private function getProductImages(int $product_id): array {
        $images = [];
        
        // Main image
        $query = $this->db->query("SELECT image FROM `" . DB_PREFIX . "product` 
            WHERE product_id = '" . (int)$product_id . "' AND image != ''");
        
        if ($query->num_rows && $query->row['image']) {
            $images[] = $this->config->get('config_url') . 'image/' . $query->row['image'];
        }
        
        // Additional images
        $query = $this->db->query("SELECT image FROM `" . DB_PREFIX . "product_image` 
            WHERE product_id = '" . (int)$product_id . "' 
            ORDER BY sort_order");
        
        foreach ($query->rows as $row) {
            if ($row['image']) {
                $images[] = $this->config->get('config_url') . 'image/' . $row['image'];
            }
        }
        
        return $images;
    }
    
    /**
     * Get product attributes
     */
    private function getProductAttributes(int $product_id): array {
        $query = $this->db->query("SELECT pa.*, ad.name as attribute_name, pad.text as attribute_text 
            FROM `" . DB_PREFIX . "product_attribute` pa
            LEFT JOIN `" . DB_PREFIX . "attribute_description` ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id = '1')
            LEFT JOIN `" . DB_PREFIX . "product_attribute` pad ON (pa.product_id = pad.product_id AND pa.attribute_id = pad.attribute_id AND pad.language_id = '1')
            WHERE pa.product_id = '" . (int)$product_id . "' AND pa.language_id = '1'");
        
        return $query->rows;
    }
    
    /**
     * Get product categories
     */
    private function getProductCategories(int $product_id): array {
        $query = $this->db->query("SELECT pc.category_id, cd.name 
            FROM `" . DB_PREFIX . "product_to_category` pc
            LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (pc.category_id = cd.category_id AND cd.language_id = '1')
            WHERE pc.product_id = '" . (int)$product_id . "'");
        
        return $query->rows;
    }
    
    /**
     * Clean HTML from description
     */
    private function cleanDescription(string $description): string {
        // Remove HTML tags
        $description = strip_tags($description);
        
        // Decode HTML entities
        $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
        
        // Remove extra whitespace
        $description = preg_replace('/\s+/', ' ', $description);
        
        // Trim
        $description = trim($description);
        
        // Limit length (most marketplaces have limits)
        if (strlen($description) > 3000) {
            $description = substr($description, 0, 2997) . '...';
        }
        
        return $description;
    }
    
    /**
     * Get brand ID for Trendyol
     */
    private function getBrandId(array $product): int {
        // This should be implemented based on your brand mapping
        return 1; // Default brand ID
    }
    
    /**
     * Get Trendyol category ID
     */
    private function getTrendyolCategoryId(array $categories): int {
        // This should be implemented based on your category mapping
        return 1; // Default category ID
    }
    
    /**
     * Get sale price (with discount)
     */
    private function getSalePrice(array $product): float {
        $price = (float)$product['price'];
        
        // Check for special prices
        $query = $this->db->query("SELECT price FROM `" . DB_PREFIX . "product_special` 
            WHERE product_id = '" . (int)$product['product_id'] . "' 
            AND ((date_start = '0000-00-00' OR date_start < NOW()) 
            AND (date_end = '0000-00-00' OR date_end > NOW())) 
            ORDER BY priority ASC, price ASC LIMIT 1");
        
        if ($query->num_rows) {
            $price = (float)$query->row['price'];
        }
        
        return $price;
    }
    
    /**
     * Get VAT rate
     */
    private function getVatRate(array $product): int {
        // Get tax rate for the product
        $query = $this->db->query("SELECT tr.rate FROM `" . DB_PREFIX . "product` p
            LEFT JOIN `" . DB_PREFIX . "tax_rule` tr ON (p.tax_class_id = tr.tax_class_id)
            WHERE p.product_id = '" . (int)$product['product_id'] . "' LIMIT 1");
        
        if ($query->num_rows) {
            return (int)$query->row['rate'];
        }
        
        return 18; // Default VAT rate for Turkey
    }
    
    /**
     * Get dimensional weight
     */
    private function getDimensionalWeight(array $product): float {
        $weight = (float)$product['weight'];
        
        // If no weight specified, calculate from dimensions
        if (!$weight && isset($product['length']) && isset($product['width']) && isset($product['height'])) {
            // Dimensional weight formula: (L x W x H) / 5000
            $weight = ($product['length'] * $product['width'] * $product['height']) / 5000;
        }
        
        return $weight ?: 0.1; // Minimum weight
    }
    
    /**
     * Format attributes for Trendyol
     */
    private function formatTrendyolAttributes(array $attributes): array {
        $formatted = [];
        
        foreach ($attributes as $attribute) {
            $formatted[] = [
                'attributeId' => $this->mapAttributeToTrendyol($attribute['attribute_name']),
                'attributeValueId' => $this->mapAttributeValueToTrendyol($attribute['attribute_name'], $attribute['text'])
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Get product content details for Trendyol
     */
    private function getProductContentDetails(array $product, array $attributes): array {
        $details = [];
        
        foreach ($attributes as $attribute) {
            $details[] = [
                'contentType' => 'text',
                'description' => $attribute['attribute_name'] . ': ' . $attribute['text']
            ];
        }
        
        return $details;
    }
    
    /**
     * Map attribute name to Trendyol attribute ID
     */
    private function mapAttributeToTrendyol(string $attribute_name): int {
        // This should be implemented based on Trendyol's attribute mapping
        $mapping = [
            'Color' => 333,
            'Size' => 334,
            'Material' => 335,
            // Add more mappings as needed
        ];
        
        return $mapping[$attribute_name] ?? 0;
    }
    
    /**
     * Map attribute value to Trendyol attribute value ID
     */
    private function mapAttributeValueToTrendyol(string $attribute_name, string $value): int {
        // This should be implemented based on Trendyol's attribute value mapping
        return 0;
    }
    
    /**
     * Validate product data for marketplace
     */
    public function validateForMarketplace(array $product_data, string $marketplace): array {
        $errors = [];
        
        switch ($marketplace) {
            case 'trendyol':
                $errors = $this->validateForTrendyol($product_data);
                break;
            case 'amazon':
                $errors = $this->validateForAmazon($product_data);
                break;
            case 'hepsiburada':
                $errors = $this->validateForHepsiburada($product_data);
                break;
        }
        
        return $errors;
    }
    
    /**
     * Validate for Trendyol
     */
    private function validateForTrendyol(array $data): array {
        $errors = [];
        
        if (empty($data['barcode'])) {
            $errors[] = 'Barcode is required';
        }
        
        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        }
        
        if (empty($data['categoryId'])) {
            $errors[] = 'Category ID is required';
        }
        
        if ($data['listPrice'] <= 0) {
            $errors[] = 'List price must be greater than 0';
        }
        
        if (empty($data['images'])) {
            $errors[] = 'At least one image is required';
        }
        
        return $errors;
    }
    
    /**
     * Validate for Amazon
     */
    private function validateForAmazon(array $data): array {
        $errors = [];
        
        if (empty($data['sku'])) {
            $errors[] = 'SKU is required';
        }
        
        if (empty($data['product-id'])) {
            $errors[] = 'Product ID is required';
        }
        
        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        }
        
        if ($data['listing-price'] <= 0) {
            $errors[] = 'Listing price must be greater than 0';
        }
        
        return $errors;
    }
    
    /**
     * Validate for Hepsiburada
     */
    private function validateForHepsiburada(array $data): array {
        $errors = [];
        
        if (empty($data['merchantSku'])) {
            $errors[] = 'Merchant SKU is required';
        }
        
        if (empty($data['Title'])) {
            $errors[] = 'Title is required';
        }
        
        if (empty($data['CategoryId'])) {
            $errors[] = 'Category ID is required';
        }
        
        if ($data['Price'] <= 0) {
            $errors[] = 'Price must be greater than 0';
        }
        
        return $errors;
    }
    
    // Additional helper methods for other marketplaces...
    private function getProductIdType(array $product): string {
        if (!empty($product['ean'])) return 'EAN';
        if (!empty($product['upc'])) return 'UPC';
        return 'SKU';
    }
    
    private function getShippingPrice(array $product): float {
        // Implement shipping price calculation
        return 0.0;
    }
    
    private function getAmazonProductType(array $product): string {
        // Map to Amazon product type
        return 'generic';
    }
    
    private function getBrandName(array $product): string {
        // Get brand name from manufacturer or attributes
        return $product['manufacturer'] ?? 'Generic';
    }
    
    private function getManufacturer(array $product): string {
        return $product['manufacturer'] ?? '';
    }
    
    private function getHepsiburadaCategoryId(array $product): int {
        // Map to Hepsiburada category
        return 1;
    }
    
    private function formatHepsiburadaAttributes(array $attributes): array {
        // Format attributes for Hepsiburada
        return [];
    }
}
