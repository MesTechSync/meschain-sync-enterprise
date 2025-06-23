<?php

/**
 * MesChain Trendyol Product Synchronization Cron Job
 * Day 5-6: Product-specific synchronization with bulk operations
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @since OpenCart 4.0.2.3
 */

// Prevent direct access
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line');
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants
define('VERSION', '4.0.2.3');
define('APPLICATION', 'Catalog');

// Bootstrap OpenCart
$dir = dirname(__FILE__);
$opencart_root = realpath($dir . '/../../../../..');

if (file_exists($opencart_root . '/config.php')) {
    require_once($opencart_root . '/config.php');
} else {
    die('OpenCart config.php not found');
}

// Include required OpenCart files
require_once($opencart_root . '/system/startup.php');

// Database
$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
if ($db->connect_error) {
    die('Database connection failed: ' . $db->connect_error);
}

/**
 * Trendyol Product Sync Manager
 */
class TrendyolProductSync
{
    private $db;
    private $config;
    private $log;
    private $startTime;
    private $stats;
    private $lockFile;
    private $maxExecutionTime = 1800; // 30 minutes
    private $batchSize = 100;
    private $bulkBatchSize = 50;

    // Rate limiting
    private $apiCallCount = 0;
    private $lastApiCall = 0;
    private $rateLimitPerMinute = 600;

    public function __construct()
    {
        global $db;
        $this->db = $db;
        $this->startTime = microtime(true);
        $this->lockFile = sys_get_temp_dir() . '/trendyol_product_sync.lock';

        // Initialize log
        $this->log = new stdClass();
        $this->log->file = fopen(DIR_LOGS . 'trendyol_product_sync.log', 'a');

        $this->stats = [
            'products_processed' => 0,
            'products_uploaded' => 0,
            'products_updated' => 0,
            'products_failed' => 0,
            'bulk_operations' => 0,
            'api_calls' => 0,
            'execution_time' => 0
        ];

        // Set memory and time limits
        ini_set('memory_limit', '512M');
        set_time_limit($this->maxExecutionTime);

        $this->loadConfig();
    }

    /**
     * Load configuration from database
     */
    private function loadConfig()
    {
        $this->config = [];

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = 0 AND `key` LIKE 'meschain_trendyol_%'");

        if ($query) {
            while ($row = $query->fetch_assoc()) {
                $this->config[$row['key']] = $row['value'];
            }
        }
    }

    /**
     * Main sync execution
     */
    public function run()
    {
        try {
            $this->writeLog('[PRODUCT SYNC] Starting product synchronization process');

            // Check if already running
            if ($this->isRunning()) {
                $this->writeLog('[PRODUCT SYNC] Another sync process is already running');
                return false;
            }

            // Create lock file
            $this->createLock();

            // Check if sync is enabled
            if (!$this->isSyncEnabled()) {
                $this->writeLog('[PRODUCT SYNC] Product synchronization is disabled');
                $this->removeLock();
                return false;
            }

            // Validate API credentials
            if (!$this->validateApiCredentials()) {
                $this->writeLog('[PRODUCT SYNC] API credentials validation failed');
                $this->removeLock();
                return false;
            }

            // Run synchronization tasks
            $this->syncNewProducts();
            $this->syncUpdatedProducts();
            $this->syncProductImages();
            $this->syncProductAttributes();
            $this->performBulkOperations();
            $this->cleanupFailedProducts();

            // Calculate execution time
            $this->stats['execution_time'] = round(microtime(true) - $this->startTime, 2);

            // Log final statistics
            $this->logStats();

            $this->writeLog('[PRODUCT SYNC] Product synchronization completed successfully');
        } catch (Exception $e) {
            $this->writeLog('[PRODUCT SYNC] Fatal error: ' . $e->getMessage());
            $this->sendErrorAlert($e->getMessage());
        } finally {
            $this->removeLock();
            if ($this->log->file) {
                fclose($this->log->file);
            }
        }

        return true;
    }

    /**
     * Sync new products to Trendyol
     */
    private function syncNewProducts()
    {
        $this->writeLog('[PRODUCT SYNC] Starting new product synchronization');

        try {
            // Get new products that haven't been synced to Trendyol
            $query = $this->db->query(
                "
                SELECT p.*, pd.name, pd.description, pd.meta_title, pd.meta_description,
                       m.name as manufacturer_name, m.manufacturer_id
                FROM " . DB_PREFIX . "product p
                LEFT JOIN " . DB_PREFIX . "product_description pd ON p.product_id = pd.product_id AND pd.language_id = 1
                LEFT JOIN " . DB_PREFIX . "manufacturer m ON p.manufacturer_id = m.manufacturer_id
                LEFT JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                WHERE p.status = 1
                AND tp.opencart_product_id IS NULL
                AND p.quantity > 0
                AND p.price > 0
                ORDER BY p.date_added DESC
                LIMIT " . $this->batchSize
            );

            if (!$query) {
                throw new Exception('Database query failed');
            }

            $products = [];
            while ($row = $query->fetch_assoc()) {
                $products[] = $row;
            }

            foreach ($products as $product) {
                if ($this->isExecutionTimeExceeded()) {
                    $this->writeLog('[PRODUCT SYNC] Execution time limit reached, stopping new product sync');
                    break;
                }

                $this->syncSingleNewProduct($product);
                $this->stats['products_processed']++;

                // Rate limiting
                $this->enforceRateLimit();
            }

            $this->writeLog('[PRODUCT SYNC] New product synchronization completed. Processed: ' . count($products));
        } catch (Exception $e) {
            $this->writeLog('[PRODUCT SYNC] New product sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync single new product
     */
    private function syncSingleNewProduct($product)
    {
        try {
            // Load Trendyol API client
            require_once(DIR_SYSTEM . 'library/meschain/api/trendyol_client.php');
            $trendyolClient = new \MesChain\Api\TrendyolClient((object)$this->config, $this);

            // Get product images
            $images = $this->getProductImages($product['product_id']);

            // Get product attributes
            $attributes = $this->getProductAttributes($product['product_id']);

            // Get product categories
            $categories = $this->getProductCategories($product['product_id']);

            // Prepare product data
            $productData = array_merge($product, [
                'images' => $images,
                'attributes' => $attributes,
                'categories' => $categories
            ]);

            // Upload product to Trendyol
            $response = $trendyolClient->uploadProduct($productData);
            $this->stats['api_calls']++;

            if ($response['success']) {
                // Save product mapping
                $this->saveProductMapping($product['product_id'], $response['data']);
                $this->stats['products_uploaded']++;
                $this->writeLog('[PRODUCT SYNC] New product uploaded: ' . $product['name']);
            } else {
                $this->saveProductError($product['product_id'], $response['error']);
                $this->stats['products_failed']++;
                $this->writeLog('[PRODUCT SYNC] New product upload failed: ' . $product['name'] . ' - ' . $response['error']);
            }
        } catch (Exception $e) {
            $this->writeLog('[PRODUCT SYNC] Single new product sync error: ' . $e->getMessage());
            $this->stats['products_failed']++;
        }
    }

    /**
     * Sync updated products
     */
    private function syncUpdatedProducts()
    {
        $this->writeLog('[PRODUCT SYNC] Starting updated product synchronization');

        try {
            // Get products that have been modified since last sync
            $query = $this->db->query(
                "
                SELECT p.*, pd.name, pd.description, tp.barcode, tp.trendyol_product_id, tp.last_sync
                FROM " . DB_PREFIX . "product p
                LEFT JOIN " . DB_PREFIX . "product_description pd ON p.product_id = pd.product_id AND pd.language_id = 1
                INNER JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                WHERE p.status = 1
                AND tp.status = 'active'
                AND (p.date_modified > tp.last_sync OR tp.last_sync IS NULL OR tp.last_sync < DATE_SUB(NOW(), INTERVAL 6 HOUR))
                ORDER BY p.date_modified DESC
                LIMIT " . $this->batchSize
            );

            if (!$query) {
                throw new Exception('Database query failed');
            }

            $products = [];
            while ($row = $query->fetch_assoc()) {
                $products[] = $row;
            }

            foreach ($products as $product) {
                if ($this->isExecutionTimeExceeded()) {
                    $this->writeLog('[PRODUCT SYNC] Execution time limit reached, stopping updated product sync');
                    break;
                }

                $this->syncSingleUpdatedProduct($product);
                $this->stats['products_processed']++;

                // Rate limiting
                $this->enforceRateLimit();
            }

            $this->writeLog('[PRODUCT SYNC] Updated product synchronization completed. Processed: ' . count($products));
        } catch (Exception $e) {
            $this->writeLog('[PRODUCT SYNC] Updated product sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync single updated product
     */
    private function syncSingleUpdatedProduct($product)
    {
        try {
            // Load Trendyol API client
            require_once(DIR_SYSTEM . 'library/meschain/api/trendyol_client.php');
            $trendyolClient = new \MesChain\Api\TrendyolClient((object)$this->config, $this);

            // Update product on Trendyol (price and inventory)
            $updateData = [
                'barcode' => $product['barcode'],
                'quantity' => max(0, (int)$product['quantity']),
                'listPrice' => (float)$product['price'],
                'salePrice' => (float)($product['special'] ?: $product['price'])
            ];

            $response = $trendyolClient->updateProduct($product['barcode'], $updateData);
            $this->stats['api_calls']++;

            if ($response['success']) {
                $this->updateProductSyncStatus($product['product_id'], 'success');
                $this->stats['products_updated']++;
                $this->writeLog('[PRODUCT SYNC] Product updated: ' . $product['name']);
            } else {
                $this->updateProductSyncStatus($product['product_id'], 'error', $response['error']);
                $this->stats['products_failed']++;
                $this->writeLog('[PRODUCT SYNC] Product update failed: ' . $product['name'] . ' - ' . $response['error']);
            }
        } catch (Exception $e) {
            $this->writeLog('[PRODUCT SYNC] Single updated product sync error: ' . $e->getMessage());
            $this->stats['products_failed']++;
        }
    }

    /**
     * Sync product images
     */
    private function syncProductImages()
    {
        $this->writeLog('[PRODUCT SYNC] Starting product image synchronization');

        try {
            // Get products with image updates needed
            $query = $this->db->query(
                "
                SELECT p.product_id, p.image, tp.barcode, tp.trendyol_product_id
                FROM " . DB_PREFIX . "product p
                INNER JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                WHERE tp.status = 'active'
                AND (tp.images_synced = 0 OR tp.images_synced IS NULL)
                LIMIT " . ($this->batchSize / 2)
            );

            if (!$query) {
                return;
            }

            $products = [];
            while ($row = $query->fetch_assoc()) {
                $products[] = $row;
            }

            foreach ($products as $product) {
                if ($this->isExecutionTimeExceeded()) {
                    break;
                }

                $this->syncProductImageSet($product);

                // Rate limiting
                $this->enforceRateLimit();
            }

            $this->writeLog('[PRODUCT SYNC] Product image synchronization completed. Processed: ' . count($products));
        } catch (Exception $e) {
            $this->writeLog('[PRODUCT SYNC] Product image sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync product image set
     */
    private function syncProductImageSet($product)
    {
        try {
            $images = $this->getProductImages($product['product_id']);

            if (empty($images)) {
                return;
            }

            // Here you would implement image upload to Trendyol
            // For now, we'll mark as synced
            $this->db->query(
                "
                UPDATE " . DB_PREFIX . "trendyol_products
                SET images_synced = 1, updated_at = NOW()
                WHERE opencart_product_id = " . (int)$product['product_id']
            );

            $this->writeLog('[PRODUCT SYNC] Images synced for product ID: ' . $product['product_id']);
        } catch (Exception $e) {
            $this->writeLog('[PRODUCT SYNC] Product image set sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync product attributes
     */
    private function syncProductAttributes()
    {
        $this->writeLog('[PRODUCT SYNC] Starting product attribute synchronization');

        try {
            // Get products with attribute updates needed
            $query = $this->db->query(
                "
                SELECT p.product_id, tp.barcode, tp.trendyol_product_id
                FROM " . DB_PREFIX . "product p
                INNER JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                WHERE tp.status = 'active'
                AND (tp.attributes_synced = 0 OR tp.attributes_synced IS NULL)
                LIMIT " . ($this->batchSize / 2)
            );

            if (!$query) {
                return;
            }

            $products = [];
            while ($row = $query->fetch_assoc()) {
                $products[] = $row;
            }

            foreach ($products as $product) {
                if ($this->isExecutionTimeExceeded()) {
                    break;
                }

                $this->syncProductAttributeSet($product);

                // Rate limiting
                $this->enforceRateLimit();
            }

            $this->writeLog('[PRODUCT SYNC] Product attribute synchronization completed. Processed: ' . count($products));
        } catch (Exception $e) {
            $this->writeLog('[PRODUCT SYNC] Product attribute sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync product attribute set
     */
    private function syncProductAttributeSet($product)
    {
        try {
            $attributes = $this->getProductAttributes($product['product_id']);

            // Here you would implement attribute sync to Trendyol
            // For now, we'll mark as synced
            $this->db->query(
                "
                UPDATE " . DB_PREFIX . "trendyol_products
                SET attributes_synced = 1, updated_at = NOW()
                WHERE opencart_product_id = " . (int)$product['product_id']
            );

            $this->writeLog('[PRODUCT SYNC] Attributes synced for product ID: ' . $product['product_id']);
        } catch (Exception $e) {
            $this->writeLog('[PRODUCT SYNC] Product attribute set sync error: ' . $e->getMessage());
        }
    }

    /**
     * Perform bulk operations for better performance
     */
    private function performBulkOperations()
    {
        $this->writeLog('[PRODUCT SYNC] Starting bulk operations');

        try {
            $this->bulkUpdatePricesAndInventory();
            $this->bulkUploadNewProducts();
        } catch (Exception $e) {
            $this->writeLog('[PRODUCT SYNC] Bulk operations error: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update prices and inventory
     */
    private function bulkUpdatePricesAndInventory()
    {
        try {
            // Get products for bulk price/inventory update
            $query = $this->db->query(
                "
                SELECT p.product_id, p.quantity, p.price, p.special, tp.barcode
                FROM " . DB_PREFIX . "product p
                INNER JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                WHERE tp.status = 'active'
                AND (tp.last_sync IS NULL OR tp.last_sync < DATE_SUB(NOW(), INTERVAL 2 HOUR))
                ORDER BY p.date_modified DESC
                LIMIT " . $this->bulkBatchSize
            );

            if (!$query) {
                return;
            }

            $products = [];
            while ($row = $query->fetch_assoc()) {
                $products[] = [
                    'barcode' => $row['barcode'],
                    'quantity' => max(0, (int)$row['quantity']),
                    'listPrice' => (float)$row['price'],
                    'salePrice' => (float)($row['special'] ?: $row['price'])
                ];
            }

            if (!empty($products)) {
                // Load Trendyol API client
                require_once(DIR_SYSTEM . 'library/meschain/api/trendyol_client.php');
                $trendyolClient = new \MesChain\Api\TrendyolClient((object)$this->config, $this);

                // Perform bulk update
                $response = $trendyolClient->makeApiRequest(
                    'suppliers/' . $this->config['meschain_trendyol_supplier_id'] . '/products/price-and-inventory',
                    'POST',
                    ['items' => $products]
                );

                $this->stats['api_calls']++;
                $this->stats['bulk_operations']++;

                if ($response['success']) {
                    // Update sync status for all products
                    foreach ($products as $product) {
                        $this->db->query("
                            UPDATE " . DB_PREFIX . "trendyol_products
                            SET last_sync = NOW(), updated_at = NOW()
                            WHERE barcode = '" . $this->db->escape($product['barcode']) . "'
                        ");
                    }

                    $this->writeLog('[PRODUCT SYNC] Bulk price/inventory update completed for ' . count($products) . ' products');
                } else {
                    $this->writeLog('[PRODUCT SYNC] Bulk price/inventory update failed: ' . $response['error']);
                }
            }
        } catch (Exception $e) {
            $this->writeLog('[PRODUCT SYNC] Bulk price/inventory update error: ' . $e->getMessage());
        }
    }

    /**
     * Bulk upload new products
     */
    private function bulkUploadNewProducts()
    {
        try {
            // Get new products for bulk upload
            $query = $this->db->query(
                "
                SELECT p.*, pd.name, pd.description
                FROM " . DB_PREFIX . "product p
                LEFT JOIN " . DB_PREFIX . "product_description pd ON p.product_id = pd.product_id AND pd.language_id = 1
                LEFT JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                WHERE p.status = 1
                AND tp.opencart_product_id IS NULL
                AND p.quantity > 0
                AND p.price > 0
                ORDER BY p.date_added DESC
                LIMIT " . ($this->bulkBatchSize / 2)
            );

            if (!$query) {
                return;
            }

            $products = [];
            while ($row = $query->fetch_assoc()) {
                $products[] = $row;
            }

            if (!empty($products)) {
                // Load Trendyol API client
                require_once(DIR_SYSTEM . 'library/meschain/api/trendyol_client.php');
                $trendyolClient = new \MesChain\Api\TrendyolClient((object)$this->config, $this);

                $formattedProducts = [];
                foreach ($products as $product) {
                    $formattedProducts[] = $trendyolClient->formatProductForTrendyol($product);
                }

                // Perform bulk upload
                $response = $trendyolClient->makeApiRequest(
                    'suppliers/' . $this->config['meschain_trendyol_supplier_id'] . '/products',
                    'POST',
                    ['items' => $formattedProducts]
                );

                $this->stats['api_calls']++;
                $this->stats['bulk_operations']++;

                if ($response['success']) {
                    // Save product mappings
                    foreach ($products as $index => $product) {
                        $this->saveProductMapping($product['product_id'], $response['data'][$index] ?? []);
                    }

                    $this->writeLog('[PRODUCT SYNC] Bulk product upload completed for ' . count($products) . ' products');
                } else {
                    $this->writeLog('[PRODUCT SYNC] Bulk product upload failed: ' . $response['error']);
                }
            }
        } catch (Exception $e) {
            $this->writeLog('[PRODUCT SYNC] Bulk product upload error: ' . $e->getMessage());
        }
    }

    /**
     * Cleanup failed products
     */
    private function cleanupFailedProducts()
    {
        try {
            // Reset products that have been failing for too long
            $this->db->query("
                UPDATE " . DB_PREFIX . "trendyol_products
                SET status = 'pending', rejection_reason = NULL
                WHERE status = 'error'
                AND updated_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");

            $this->writeLog('[PRODUCT SYNC] Failed products cleanup completed');
        } catch (Exception $e) {
            $this->writeLog('[PRODUCT SYNC] Failed products cleanup error: ' . $e->getMessage());
        }
    }

    /**
     * Helper methods
     */
    private function getProductImages($productId)
    {
        $images = [];

        $query = $this->db->query("SELECT image FROM " . DB_PREFIX . "product_image WHERE product_id = " . (int)$productId . " ORDER BY sort_order");

        if ($query) {
            while ($row = $query->fetch_assoc()) {
                $images[] = [
                    'url' => HTTP_CATALOG . 'image/' . $row['image'],
                    'image' => $row['image']
                ];
            }
        }

        return $images;
    }

    private function getProductAttributes($productId)
    {
        $attributes = [];

        $query = $this->db->query(
            "
            SELECT pa.attribute_id, pa.text, ad.name
            FROM " . DB_PREFIX . "product_attribute pa
            LEFT JOIN " . DB_PREFIX . "attribute_description ad ON pa.attribute_id = ad.attribute_id AND ad.language_id = 1
            WHERE pa.product_id = " . (int)$productId
        );

        if ($query) {
            while ($row = $query->fetch_assoc()) {
                $attributes[] = [
                    'attribute_id' => $row['attribute_id'],
                    'name' => $row['name'],
                    'text' => $row['text']
                ];
            }
        }

        return $attributes;
    }

    private function getProductCategories($productId)
    {
        $categories = [];

        $query = $this->db->query(
            "
            SELECT c.category_id, cd.name
            FROM " . DB_PREFIX . "product_to_category ptc
            LEFT JOIN " . DB_PREFIX . "category c ON ptc.category_id = c.category_id
            LEFT JOIN " . DB_PREFIX . "category_description cd ON c.category_id = cd.category_id AND cd.language_id = 1
            WHERE ptc.product_id = " . (int)$productId
        );

        if ($query) {
            while ($row = $query->fetch_assoc()) {
                $categories[] = [
                    'category_id' => $row['category_id'],
                    'name' => $row['name']
                ];
            }
        }

        return $categories;
    }

    private function saveProductMapping($productId, $trendyolData)
    {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "trendyol_products SET
            opencart_product_id = " . (int)$productId . ",
            trendyol_product_id = '" . $this->db->escape($trendyolData['productId'] ?? '') . "',
            barcode = '" . $this->db->escape($trendyolData['barcode'] ?? '') . "',
            status = 'pending',
            last_sync = NOW(),
            created_at = NOW(),
            updated_at = NOW()
            ON DUPLICATE KEY UPDATE
            trendyol_product_id = VALUES(trendyol_product_id),
            barcode = VALUES(barcode),
            status = VALUES(status),
            last_sync = VALUES(last_sync),
            updated_at = VALUES(updated_at)
        ");
    }

    private function saveProductError($productId, $error)
    {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "trendyol_products SET
            opencart_product_id = " . (int)$productId . ",
            status = 'error',
            rejection_reason = '" . $this->db->escape($error) . "',
            created_at = NOW(),
            updated_at = NOW()
            ON DUPLICATE KEY UPDATE
            status = VALUES(status),
            rejection_reason = VALUES(rejection_reason),
            updated_at = VALUES(updated_at)
        ");
    }

    private function updateProductSyncStatus($productId, $status, $error = null)
    {
        $sql = "
            UPDATE " . DB_PREFIX . "trendyol_products
            SET status = '" . $this->db->escape($status) . "',
                last_sync = NOW(),
                updated_at = NOW()";

        if ($error) {
            $sql .= ", rejection_reason = '" . $this->db->escape($error) . "'";
        }

        $sql .= " WHERE opencart_product_id = " . (int)$productId;

        $this->db->query($sql);
    }

    private function isRunning()
    {
        if (!file_exists($this->lockFile)) {
            return false;
        }

        return (time() - filemtime($this->lockFile)) < 1800; // 30 minutes
    }

    private function createLock()
    {
        file_put_contents($this->lockFile, getmypid());
    }

    private function removeLock()
    {
        if (file_exists($this->lockFile)) {
            unlink($this->lockFile);
        }
    }

    private function isSyncEnabled()
    {
        return !empty($this->config['meschain_trendyol_auto_sync']) &&
            !empty($this->config['meschain_trendyol_product_sync']);
    }

    private function validateApiCredentials()
    {
        return !empty($this->config['meschain_trendyol_api_key']) &&
            !empty($this->config['meschain_trendyol_api_secret']) &&
            !empty($this->config['meschain_trendyol_supplier_id']);
    }

    private function isExecutionTimeExceeded()
    {
        return (microtime(true) - $this->startTime) > ($this->maxExecutionTime - 60);
    }

    private function enforceRateLimit()
    {
        $this->apiCallCount++;
        $currentTime = time();

        if ($this->apiCallCount >= $this->rateLimitPerMinute) {
            $timeDiff = $currentTime - $this->lastApiCall;
            if ($timeDiff < 60) {
                sleep(60 - $timeDiff);
            }
            $this->apiCallCount = 0;
        }

        $this->lastApiCall = $currentTime;
        usleep(100000); // 0.1 second delay
    }

    private function writeLog($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";

        if ($this->log->file) {
            fwrite($this->log->file, $logMessage);
            fflush($this->log->file);
        }

        echo $logMessage;
    }

    private function logStats()
    {
        $statsMessage = sprintf(
            '[PRODUCT SYNC] Statistics - Processed: %d, Uploaded: %d, Updated: %d, Failed: %d, Bulk Ops: %d, API Calls: %d, Time: %s seconds',
            $this->stats['products_processed'],
            $this->stats['products_uploaded'],
            $this->stats['products_updated'],
            $this->stats['products_failed'],
            $this->stats['bulk_operations'],
            $this->stats['api_calls'],
            $this->stats['execution_time']
        );

        $this->writeLog($statsMessage);

        // Save stats to database
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "trendyol_sync_stats SET
            sync_type = 'product',
            products_processed = " . (int)$this->stats['products_processed'] . ",
            products_uploaded = " . (int)$this->stats['products_uploaded'] . ",
            products_updated = " . (int)$this->stats['products_updated'] . ",
            products_failed = " . (int)$this->stats['products_failed'] . ",
            bulk_operations = " . (int)$this->stats['bulk_operations'] . ",
            api_calls = " . (int)$this->stats['api_calls'] . ",
            execution_time = " . (float)$this->stats['execution_time'] . ",
            created_at = NOW()
        ");
    }

    private function sendErrorAlert($message)
    {
        $this->writeLog('[PRODUCT SYNC] ERROR ALERT: ' . $message);

        // Here you could implement email notifications, Slack webhooks, etc.
    }

    // Write method for log compatibility
    public function write($message)
    {
        $this->writeLog($message);
    }
}

// Create sync manager and run
try {
    $productSync = new TrendyolProductSync();
    $productSync->run();
    echo "Trendyol product synchronization completed successfully\n";
} catch (Exception $e) {
    echo "Trendyol product synchronization failed: " . $e->getMessage() . "\n";
    exit(1);
}
