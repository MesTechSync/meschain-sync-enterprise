<?php
/**
 * Trendyol Helper - Advanced Integration Functions
 * MesChain-Sync Enterprise v4.5.0
 *
 * @author MesChain Development Team
 * @version 4.5.0 Enterprise
 * @copyright 2024 MesChain Technologies
 */

namespace MesChain\Helper;

class TrendyolHelper {

    private $registry;
    private $db;
    private $config;
    private $log;

    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->log = $registry->get('log');
    }

    /**
     * Format product data for Trendyol API
     */
    public function formatProductForTrendyol($product) {
        $formatted = [
            'barcode' => $product['sku'] ?: $this->generateBarcode($product['product_id']),
            'title' => $this->cleanTitle($product['name']),
            'description' => $this->cleanDescription($product['description']),
            'brand' => $product['manufacturer'] ?: 'Generic',
            'categoryId' => $this->mapCategory($product['category_id']),
            'quantity' => (int)$product['quantity'],
            'listPrice' => (float)$product['price'],
            'salePrice' => (float)($product['special'] ?: $product['price']),
            'images' => $this->formatImages($product['images']),
            'attributes' => $this->formatAttributes($product['attributes']),
            'stockCode' => $product['model'] ?: $product['sku'],
            'dimensionalWeight' => $this->calculateDimensionalWeight($product),
            'vatRate' => $this->getVatRate($product['tax_class_id']),
            'cargoCompanyId' => $this->getCargoCompanyId()
        ];

        return $this->validateProductData($formatted);
    }

    /**
     * Map OpenCart category to Trendyol category
     */
    public function mapCategory($opencart_category_id) {
        $query = $this->db->query("SELECT trendyol_category_id FROM `" . DB_PREFIX . "trendyol_category_mapping` WHERE opencart_category_id = '" . (int)$opencart_category_id . "'");

        if ($query->num_rows) {
            return (int)$query->row['trendyol_category_id'];
        }

        // Default category if no mapping found
        return $this->config->get('trendyol_default_category_id') ?: 1;
    }

    /**
     * Clean and validate product title
     */
    public function cleanTitle($title) {
        // Remove HTML tags
        $title = strip_tags($title);

        // Remove special characters that Trendyol doesn't allow
        $title = preg_replace('/[^\p{L}\p{N}\s\-\.]/u', '', $title);

        // Limit length
        $title = mb_substr($title, 0, 100);

        // Trim whitespace
        $title = trim($title);

        return $title ?: 'Ürün';
    }

    /**
     * Clean and validate product description
     */
    public function cleanDescription($description) {
        // Remove HTML tags but keep line breaks
        $description = strip_tags($description, '<br><p>');

        // Convert HTML line breaks to plain text
        $description = str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $description);
        $description = strip_tags($description);

        // Limit length
        $description = mb_substr($description, 0, 5000);

        // Trim whitespace
        $description = trim($description);

        return $description ?: 'Ürün açıklaması';
    }

    /**
     * Generate barcode for product
     */
    public function generateBarcode($product_id) {
        return 'OC' . str_pad($product_id, 10, '0', STR_PAD_LEFT);
    }

        /**
     * Format product images for Trendyol
     */
    public function formatImages($images) {
        $formatted_images = [];

        if (is_array($images)) {
            foreach ($images as $image) {
                if (!empty($image)) {
                    $image_url = $this->getFullImageUrl($image);
                    if ($this->validateImageUrl($image_url)) {
                        $formatted_images[] = ['url' => $image_url];
                    }
                }
            }
        }

        return $formatted_images;
    }

    /**
     * Format product attributes for Trendyol
     */
    public function formatAttributes($attributes) {
        $formatted_attributes = [];

        if (is_array($attributes)) {
            foreach ($attributes as $attribute) {
                $formatted_attributes[] = [
                    'attributeId' => $attribute['attribute_id'],
                    'customAttributeValue' => $attribute['text']
                ];
            }
        }

        return $formatted_attributes;
    }

    /**
     * Calculate dimensional weight
     */
    public function calculateDimensionalWeight($product) {
        $length = (float)($product['length'] ?? 0);
        $width = (float)($product['width'] ?? 0);
        $height = (float)($product['height'] ?? 0);

        if ($length && $width && $height) {
            return ($length * $width * $height) / 3000;
        }

        return (float)($product['weight'] ?? 0);
    }

    /**
     * Get VAT rate for product
     */
    public function getVatRate($tax_class_id) {
        if (!$tax_class_id) {
            return 18; // Default VAT rate in Turkey
        }

        $query = $this->db->query("SELECT rate FROM `" . DB_PREFIX . "tax_rate` WHERE tax_class_id = '" . (int)$tax_class_id . "' LIMIT 1");

        if ($query->num_rows) {
            return (int)$query->row['rate'];
        }

        return 18;
    }

    /**
     * Get cargo company ID
     */
    public function getCargoCompanyId() {
        return $this->config->get('trendyol_cargo_company_id') ?: 1;
    }

    /**
     * Get full image URL
     */
    private function getFullImageUrl($image_path) {
        if (filter_var($image_path, FILTER_VALIDATE_URL)) {
            return $image_path;
        }

        $base_url = $this->config->get('config_url');
        return rtrim($base_url, '/') . '/image/' . ltrim($image_path, '/');
    }

    /**
     * Validate image URL
     */
    private function validateImageUrl($url) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        return true; // Simplified validation
    }

    /**
     * Validate product data
     */
    public function validateProductData($data) {
        $errors = [];

        // Required fields validation
        if (empty($data['barcode'])) {
            $errors[] = 'Barcode is required';
        }

        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        }

        if (empty($data['description'])) {
            $errors[] = 'Description is required';
        }

        if (empty($data['categoryId'])) {
            $errors[] = 'Category ID is required';
        }

        if ($data['listPrice'] <= 0) {
            $errors[] = 'List price must be greater than 0';
        }

        if ($data['salePrice'] <= 0) {
            $errors[] = 'Sale price must be greater than 0';
        }

        if (!empty($errors)) {
            throw new \Exception('Product validation failed: ' . implode(', ', $errors));
        }

        return $data;
    }
}
