<?php
/**
 * Trendyol Product Importer - Enterprise Level
 * MesChain-Sync Enterprise v4.5.0
 * 
 * Implements the comprehensive product import system from Trendyol to OpenCart
 * Based on the system design document requirements
 *
 * @author MesChain Development Team
 * @version 4.5.0 Enterprise
 * @copyright 2024 MesChain Technologies
 */

namespace MesChain\Importer;

use MesChain\Api\TrendyolApiClient;

class TrendyolProductImporter {

    private $registry;
    private $db;
    private $log;
    private $config;
    private $api_client;
    private $session_id;
    private $total_products = 0;
    private $processed_products = 0;
    private $successful_imports = 0;
    private $failed_imports = 0;
    private $batch_size = 50;
    private $memory_limit = '512M';
    private $max_execution_time = 300;

    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->log = $registry->get('log');
        $this->config = $registry->get('config');
        
        // Set memory and execution limits
        ini_set('memory_limit', $this->memory_limit);
        set_time_limit($this->max_execution_time);
        
        // Initialize API client
        $this->initializeApiClient();
    }

    /**
     * Initialize Trendyol API Client
     */
    private function initializeApiClient() {
        $api_config = [
            'api_key' => $this->config->get('meschain_trendyol_api_key'),
            'api_secret' => $this->config->get('meschain_trendyol_api_secret'),
            'supplier_id' => $this->config->get('meschain_trendyol_supplier_id'),
            'test_mode' => $this->config->get('meschain_trendyol_test_mode'),
            'timeout' => 30,
            'retry_count' => 3
        ];
        
        $this->api_client = new TrendyolApiClient($api_config);
    }

    /**
     * Start import session
     */
    public function startImportSession($session_name, $settings = []) {
        try {
            // Test API connection first
            $connection_test = $this->api_client->testConnection();
            if (!$connection_test['success']) {
                throw new \Exception('Trendyol API connection failed: ' . $connection_test['message']);
            }

            // Create import session
            $this->session_id = $this->createImportSession($session_name, $settings);
            
            return [
                'success' => true,
                'session_id' => $this->session_id,
                'message' => 'Import session started successfully',
                'api_connection' => $connection_test
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to start import session: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Import products from Trendyol
     */
    public function importProducts($filters = []) {
        try {
            if (!$this->session_id) {
                throw new \Exception('No active import session');
            }

            $this->updateSessionStatus('running');
            
            // Get total product count
            $this->total_products = $this->getTotalProductCount($filters);
            $this->updateSessionProgress();

            $page = 0;
            $size = $this->batch_size;
            $all_products = [];

            // Fetch all products with pagination
            do {
                $products_response = $this->api_client->getProducts($page, $size);
                
                if (!isset($products_response['content'])) {
                    break;
                }
                
                $products = $products_response['content'];
                
                foreach ($products as $product) {
                    $import_result = $this->importSingleProduct($product);
                    
                    if ($import_result['success']) {
                        $this->successful_imports++;
                    } else {
                        $this->failed_imports++;
                        $this->logError('Product import failed', $product, $import_result['error']);
                    }
                    
                    $this->processed_products++;
                    $this->updateSessionProgress();
                    
                    // Memory management
                    if ($this->processed_products % 100 == 0) {
                        $this->cleanupMemory();
                    }
                }
                
                $page++;
                
            } while (count($products) == $size && $page < 100); // Safety limit

            $this->updateSessionStatus('completed');
            
            return [
                'success' => true,
                'session_id' => $this->session_id,
                'total_products' => $this->total_products,
                'processed_products' => $this->processed_products,
                'successful_imports' => $this->successful_imports,
                'failed_imports' => $this->failed_imports,
                'message' => 'Import completed successfully'
            ];
            
        } catch (\Exception $e) {
            $this->updateSessionStatus('failed', $e->getMessage());
            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'session_id' => $this->session_id
            ];
        }
    }

    /**
     * Import single product from Trendyol to OpenCart
     */
    private function importSingleProduct($trendyol_product) {
        try {
            // Check if product already exists
            $existing_product = $this->findExistingProduct($trendyol_product);
            
            if ($existing_product) {
                // Update existing product
                $result = $this->updateExistingProduct($existing_product['product_id'], $trendyol_product);
            } else {
                // Create new product
                $result = $this->createNewProduct($trendyol_product);
            }
            
            if ($result['success']) {
                // Create product mapping
                $this->createProductMapping($trendyol_product, $result['product_id']);
                
                // Download and save images
                $this->processProductImages($result['product_id'], $trendyol_product);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create new OpenCart product from Trendyol data
     */
    private function createNewProduct($trendyol_product) {
        try {
            // Transform Trendyol product data to OpenCart format
            $opencart_product = $this->transformProductData($trendyol_product);
            
            // Insert product
            $sql = "INSERT INTO " . DB_PREFIX . "product SET 
                    model = '" . $this->db->escape($opencart_product['model']) . "',
                    sku = '" . $this->db->escape($opencart_product['sku']) . "',
                    upc = '" . $this->db->escape($opencart_product['upc']) . "',
                    ean = '" . $this->db->escape($opencart_product['ean']) . "',
                    jan = '" . $this->db->escape($opencart_product['jan']) . "',
                    isbn = '" . $this->db->escape($opencart_product['isbn']) . "',
                    mpn = '" . $this->db->escape($opencart_product['mpn']) . "',
                    location = '" . $this->db->escape($opencart_product['location']) . "',
                    quantity = '" . (int)$opencart_product['quantity'] . "',
                    stock_status_id = '" . (int)$opencart_product['stock_status_id'] . "',
                    image = '" . $this->db->escape($opencart_product['image']) . "',
                    manufacturer_id = '" . (int)$opencart_product['manufacturer_id'] . "',
                    shipping = '" . (int)$opencart_product['shipping'] . "',
                    price = '" . (float)$opencart_product['price'] . "',
                    points = '" . (int)$opencart_product['points'] . "',
                    tax_class_id = '" . (int)$opencart_product['tax_class_id'] . "',
                    date_available = '" . $this->db->escape($opencart_product['date_available']) . "',
                    weight = '" . (float)$opencart_product['weight'] . "',
                    weight_class_id = '" . (int)$opencart_product['weight_class_id'] . "',
                    length = '" . (float)$opencart_product['length'] . "',
                    width = '" . (float)$opencart_product['width'] . "',
                    height = '" . (float)$opencart_product['height'] . "',
                    length_class_id = '" . (int)$opencart_product['length_class_id'] . "',
                    subtract = '" . (int)$opencart_product['subtract'] . "',
                    minimum = '" . (int)$opencart_product['minimum'] . "',
                    sort_order = '" . (int)$opencart_product['sort_order'] . "',
                    status = '" . (int)$opencart_product['status'] . "',
                    viewed = '0',
                    date_added = NOW(),
                    date_modified = NOW()";
            
            $this->db->query($sql);
            $product_id = $this->db->getLastId();
            
            // Insert product description
            $this->insertProductDescription($product_id, $opencart_product);
            
            // Insert product to category
            $this->insertProductToCategory($product_id, $opencart_product['category_id']);
            
            // Insert product to store
            $this->insertProductToStore($product_id);
            
            // Insert SEO URL
            $this->insertSeoUrl($product_id, $opencart_product['name']);
            
            return [
                'success' => true,
                'product_id' => $product_id,
                'action' => 'created'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Transform Trendyol product data to OpenCart format
     */
    private function transformProductData($trendyol_product) {
        // Map Trendyol category to OpenCart category
        $category_id = $this->mapTrendyolCategory($trendyol_product['categoryId'] ?? 0);
        
        // Get or create manufacturer
        $manufacturer_id = $this->getOrCreateManufacturer($trendyol_product['brandName'] ?? 'Unknown');
        
        // Generate model and SKU
        $model = $trendyol_product['productCode'] ?? 'TRENDYOL_' . uniqid();
        $sku = $trendyol_product['barcode'] ?? $model;
        
        // Calculate price
        $price = (float)($trendyol_product['salePrice'] ?? $trendyol_product['listPrice'] ?? 0);
        
        // Determine stock status
        $quantity = (int)($trendyol_product['quantity'] ?? 0);
        $stock_status_id = $quantity > 0 ? $this->config->get('config_stock_status_id') : $this->config->get('config_stock_checkout_id');
        
        return [
            'model' => $model,
            'sku' => $sku,
            'upc' => '',
            'ean' => $trendyol_product['barcode'] ?? '',
            'jan' => '',
            'isbn' => '',
            'mpn' => '',
            'location' => '',
            'quantity' => $quantity,
            'stock_status_id' => $stock_status_id,
            'image' => '',
            'manufacturer_id' => $manufacturer_id,
            'shipping' => 1,
            'price' => $price,
            'points' => 0,
            'tax_class_id' => $this->config->get('config_tax_class_id'),
            'date_available' => date('Y-m-d'),
            'weight' => (float)($trendyol_product['dimensionalWeight'] ?? 0),
            'weight_class_id' => $this->config->get('config_weight_class_id'),
            'length' => 0,
            'width' => 0,
            'height' => 0,
            'length_class_id' => $this->config->get('config_length_class_id'),
            'subtract' => 1,
            'minimum' => 1,
            'sort_order' => 0,
            'status' => 1,
            'name' => $trendyol_product['title'] ?? 'Imported Product',
            'description' => $this->cleanHtml($trendyol_product['description'] ?? ''),
            'meta_title' => $trendyol_product['title'] ?? 'Imported Product',
            'meta_description' => substr(strip_tags($trendyol_product['description'] ?? ''), 0, 160),
            'meta_keyword' => '',
            'tag' => '',
            'category_id' => $category_id
        ];
    }

    /**
     * Clean HTML content
     */
    private function cleanHtml($html) {
        // Remove scripts and styles
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        $html = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $html);
        
        // Clean up HTML
        $html = strip_tags($html, '<p><br><div><span><b><strong><i><em><ul><ol><li><h1><h2><h3><h4><h5><h6>');
        
        return $html;
    }

    /**
     * Insert product description
     */
    private function insertProductDescription($product_id, $product_data) {
        $sql = "INSERT INTO " . DB_PREFIX . "product_description SET 
                product_id = '" . (int)$product_id . "',
                language_id = '" . (int)$this->config->get('config_language_id') . "',
                name = '" . $this->db->escape($product_data['name']) . "',
                description = '" . $this->db->escape($product_data['description']) . "',
                tag = '" . $this->db->escape($product_data['tag']) . "',
                meta_title = '" . $this->db->escape($product_data['meta_title']) . "',
                meta_description = '" . $this->db->escape($product_data['meta_description']) . "',
                meta_keyword = '" . $this->db->escape($product_data['meta_keyword']) . "'";
        
        $this->db->query($sql);
    }

    /**
     * Insert product to category
     */
    private function insertProductToCategory($product_id, $category_id) {
        $sql = "INSERT INTO " . DB_PREFIX . "product_to_category SET 
                product_id = '" . (int)$product_id . "',
                category_id = '" . (int)$category_id . "'";
        
        $this->db->query($sql);
    }

    /**
     * Insert product to store
     */
    private function insertProductToStore($product_id) {
        $sql = "INSERT INTO " . DB_PREFIX . "product_to_store SET 
                product_id = '" . (int)$product_id . "',
                store_id = '0'";
        
        $this->db->query($sql);
    }

    /**
     * Insert SEO URL
     */
    private function insertSeoUrl($product_id, $name) {
        $keyword = $this->generateSeoKeyword($name);
        
        $sql = "INSERT INTO " . DB_PREFIX . "seo_url SET 
                store_id = '0',
                language_id = '" . (int)$this->config->get('config_language_id') . "',
                key = 'product_id',
                value = '" . (int)$product_id . "',
                keyword = '" . $this->db->escape($keyword) . "'";
        
        $this->db->query($sql);
    }

    /**
     * Generate SEO keyword from product name
     */
    private function generateSeoKeyword($name) {
        $keyword = strtolower($name);
        $keyword = preg_replace('/[^a-z0-9\s-]/', '', $keyword);
        $keyword = preg_replace('/\s+/', '-', $keyword);
        $keyword = trim($keyword, '-');
        
        // Check if keyword exists and make it unique
        $original_keyword = $keyword;
        $counter = 1;
        
        while ($this->keywordExists($keyword)) {
            $keyword = $original_keyword . '-' . $counter;
            $counter++;
        }
        
        return $keyword;
    }

    /**
     * Check if SEO keyword exists
     */
    private function keywordExists($keyword) {
        $query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($keyword) . "'");
        return $query->num_rows > 0;
    }

    /**
     * Map Trendyol category to OpenCart category
     */
    private function mapTrendyolCategory($trendyol_category_id) {
        // Check if mapping exists
        $query = $this->db->query("SELECT opencart_category_id FROM " . DB_PREFIX . "trendyol_category_mapping WHERE trendyol_category_id = '" . (int)$trendyol_category_id . "'");
        
        if ($query->num_rows) {
            return $query->row['opencart_category_id'];
        }
        
        // Return default category if no mapping found
        return $this->config->get('config_category_id') ?: 1;
    }

    /**
     * Get or create manufacturer
     */
    private function getOrCreateManufacturer($brand_name) {
        // Check if manufacturer exists
        $query = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "manufacturer WHERE name = '" . $this->db->escape($brand_name) . "'");
        
        if ($query->num_rows) {
            return $query->row['manufacturer_id'];
        }
        
        // Create new manufacturer
        $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($brand_name) . "', sort_order = 0");
        
        return $this->db->getLastId();
    }

    /**
     * Create import session
     */
    private function createImportSession($session_name, $settings) {
        $sql = "INSERT INTO " . DB_PREFIX . "trendyol_import_sessions SET 
                session_name = '" . $this->db->escape($session_name) . "',
                status = 'pending',
                settings = '" . $this->db->escape(json_encode($settings)) . "',
                created_at = NOW(),
                updated_at = NOW()";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }

    /**
     * Update session status
     */
    private function updateSessionStatus($status, $error_log = '') {
        $sql = "UPDATE " . DB_PREFIX . "trendyol_import_sessions SET 
                status = '" . $this->db->escape($status) . "',
                updated_at = NOW()";
        
        if ($error_log) {
            $sql .= ", error_log = '" . $this->db->escape($error_log) . "'";
        }
        
        if ($status == 'completed') {
            $sql .= ", end_time = NOW()";
        } elseif ($status == 'running') {
            $sql .= ", start_time = NOW()";
        }
        
        $sql .= " WHERE session_id = '" . (int)$this->session_id . "'";
        
        $this->db->query($sql);
    }

    /**
     * Update session progress
     */
    private function updateSessionProgress() {
        $sql = "UPDATE " . DB_PREFIX . "trendyol_import_sessions SET 
                total_products = '" . (int)$this->total_products . "',
                processed_products = '" . (int)$this->processed_products . "',
                successful_imports = '" . (int)$this->successful_imports . "',
                failed_imports = '" . (int)$this->failed_imports . "',
                updated_at = NOW()
                WHERE session_id = '" . (int)$this->session_id . "'";
        
        $this->db->query($sql);
    }

    /**
     * Create product mapping
     */
    private function createProductMapping($trendyol_product, $opencart_product_id) {
        $sql = "INSERT INTO " . DB_PREFIX . "trendyol_product_mapping SET 
                trendyol_product_id = '" . $this->db->escape($trendyol_product['productCode'] ?? '') . "',
                trendyol_barcode = '" . $this->db->escape($trendyol_product['barcode'] ?? '') . "',
                opencart_product_id = '" . (int)$opencart_product_id . "',
                import_session_id = '" . (int)$this->session_id . "',
                sync_status = 'imported',
                last_sync = NOW(),
                created_at = NOW()";
        
        $this->db->query($sql);
    }

    /**
     * Find existing product by barcode or product code
     */
    private function findExistingProduct($trendyol_product) {
        $barcode = $trendyol_product['barcode'] ?? '';
        $product_code = $trendyol_product['productCode'] ?? '';
        
        if ($barcode) {
            $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE ean = '" . $this->db->escape($barcode) . "' OR sku = '" . $this->db->escape($barcode) . "'");
            if ($query->num_rows) {
                return $query->row;
            }
        }
        
        if ($product_code) {
            $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE model = '" . $this->db->escape($product_code) . "'");
            if ($query->num_rows) {
                return $query->row;
            }
        }
        
        return false;
    }

    /**
     * Update existing product
     */
    private function updateExistingProduct($product_id, $trendyol_product) {
        try {
            $opencart_product = $this->transformProductData($trendyol_product);
            
            $sql = "UPDATE " . DB_PREFIX . "product SET 
                    quantity = '" . (int)$opencart_product['quantity'] . "',
                    price = '" . (float)$opencart_product['price'] . "',
                    stock_status_id = '" . (int)$opencart_product['stock_status_id'] . "',
                    date_modified = NOW()
                    WHERE product_id = '" . (int)$product_id . "'";
            
            $this->db->query($sql);
            
            return [
                'success' => true,
                'product_id' => $product_id,
                'action' => 'updated'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process product images
     */
    private function processProductImages($product_id, $trendyol_product) {
        if (!isset($trendyol_product['images']) || !is_array($trendyol_product['images'])) {
            return;
        }
        
        $image_dir = DIR_IMAGE . 'catalog/trendyol/';
        if (!is_dir($image_dir)) {
            mkdir($image_dir, 0755, true);
        }
        
        foreach ($trendyol_product['images'] as $index => $image_url) {
            try {
                $image_name = 'product_' . $product_id . '_' . $index . '.jpg';
                $image_path = $image_dir . $image_name;
                
                // Download image
                $image_data = file_get_contents($image_url);
                if ($image_data) {
                    file_put_contents($image_path, $image_data);
                    
                    $relative_path = 'catalog/trendyol/' . $image_name;
                    
                    if ($index == 0) {
                        // Set as main image
                        $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($relative_path) . "' WHERE product_id = '" . (int)$product_id . "'");
                    } else {
                        // Add as additional image
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($relative_path) . "', sort_order = '" . $index . "'");
                    }
                }
            } catch (\Exception $e) {
                $this->logError('Image download failed', ['product_id' => $product_id, 'image_url' => $image_url], $e->getMessage());
            }
        }
    }

    /**
     * Get total product count
     */
    private function getTotalProductCount($filters = []) {
        try {
            $response = $this->api_client->getProducts(0, 1);
            return $response['totalElements'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Log error
     */
    private function logError($message, $context = [], $error = '') {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'session_id' => $this->session_id,
            'message' => $message,
            'context' => $context,
            'error' => $error
        ];
        
        $this->log->write('[TRENDYOL_IMPORT] ' . json_encode($log_entry));
    }

    /**
     * Memory cleanup
     */
    private function cleanupMemory() {
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    /**
     * Get import session status
     */
    public function getSessionStatus($session_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trendyol_import_sessions WHERE session_id = '" . (int)$session_id . "'");
        
        if ($query->num_rows) {
            return $query->row;
        }
        
        return false;
    }

    /**
     * Get import sessions list
     */
    public function getImportSessions($limit = 20, $offset = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "trendyol_import_sessions 
                ORDER BY created_at DESC 
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
}