<?php

/**
 * MesChain Trendyol Stock Synchronization Cron Job
 * Day 5-6: Stock-specific synchronization with real-time updates
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
 * Trendyol Stock Sync Manager
 */
class TrendyolStockSync
{
    private $db;
    private $config;
    private $log;
    private $startTime;
    private $stats;
    private $lockFile;
    private $maxExecutionTime = 600; // 10 minutes
    private $batchSize = 100;
    private $bulkBatchSize = 50;

    // Rate limiting
    private $apiCallCount = 0;
    private $lastApiCall = 0;
    private $rateLimitPerMinute = 600;

    // Stock thresholds
    private $lowStockThreshold = 5;
    private $outOfStockThreshold = 0;

    public function __construct()
    {
        global $db;
        $this->db = $db;
        $this->startTime = microtime(true);
        $this->lockFile = sys_get_temp_dir() . '/trendyol_stock_sync.lock';

        // Initialize log
        $this->log = new stdClass();
        $this->log->file = fopen(DIR_LOGS . 'trendyol_stock_sync.log', 'a');

        $this->stats = [
            'products_processed' => 0,
            'stock_updated' => 0,
            'price_updated' => 0,
            'bulk_operations' => 0,
            'low_stock_alerts' => 0,
            'out_of_stock_alerts' => 0,
            'api_calls' => 0,
            'execution_time' => 0
        ];

        // Set memory and time limits
        ini_set('memory_limit', '256M');
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

        // Load stock thresholds
        $this->lowStockThreshold = (int)($this->config['meschain_trendyol_low_stock_threshold'] ?? 5);
        $this->outOfStockThreshold = (int)($this->config['meschain_trendyol_out_of_stock_threshold'] ?? 0);
    }

    /**
     * Main sync execution
     */
    public function run()
    {
        try {
            $this->writeLog('[STOCK SYNC] Starting stock synchronization process');

            // Check if already running
            if ($this->isRunning()) {
                $this->writeLog('[STOCK SYNC] Another sync process is already running');
                return false;
            }

            // Create lock file
            $this->createLock();

            // Check if sync is enabled
            if (!$this->isSyncEnabled()) {
                $this->writeLog('[STOCK SYNC] Stock synchronization is disabled');
                $this->removeLock();
                return false;
            }

            // Validate API credentials
            if (!$this->validateApiCredentials()) {
                $this->writeLog('[STOCK SYNC] API credentials validation failed');
                $this->removeLock();
                return false;
            }

            // Run synchronization tasks
            $this->syncStockLevels();
            $this->syncPriceChanges();
            $this->performBulkStockUpdate();
            $this->checkLowStockAlerts();
            $this->syncFromTrendyol();
            $this->updateStockHistory();

            // Calculate execution time
            $this->stats['execution_time'] = round(microtime(true) - $this->startTime, 2);

            // Log final statistics
            $this->logStats();

            $this->writeLog('[STOCK SYNC] Stock synchronization completed successfully');
        } catch (Exception $e) {
            $this->writeLog('[STOCK SYNC] Fatal error: ' . $e->getMessage());
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
     * Sync stock levels to Trendyol
     */
    private function syncStockLevels()
    {
        $this->writeLog('[STOCK SYNC] Starting stock level synchronization');

        try {
            // Get products with stock changes
            $query = $this->db->query(
                "
                SELECT p.product_id, p.quantity, p.price, p.special, p.model, p.sku,
                       tp.barcode, tp.trendyol_product_id, tp.last_sync, tp.last_stock_sync
                FROM " . DB_PREFIX . "product p
                INNER JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                WHERE tp.status = 'active'
                AND p.status = 1
                AND (tp.last_stock_sync IS NULL
                     OR tp.last_stock_sync < DATE_SUB(NOW(), INTERVAL 1 HOUR)
                     OR p.date_modified > tp.last_stock_sync)
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

            // Load Trendyol API client
            require_once(DIR_SYSTEM . 'library/meschain/api/trendyol_client.php');
            $trendyolClient = new \MesChain\Api\TrendyolClient((object)$this->config, $this);

            foreach ($products as $product) {
                if ($this->isExecutionTimeExceeded()) {
                    $this->writeLog('[STOCK SYNC] Execution time limit reached, stopping stock sync');
                    break;
                }

                $this->syncSingleProductStock($product, $trendyolClient);
                $this->stats['products_processed']++;

                // Rate limiting
                $this->enforceRateLimit();
            }

            $this->writeLog('[STOCK SYNC] Stock level synchronization completed. Processed: ' . count($products));
        } catch (Exception $e) {
            $this->writeLog('[STOCK SYNC] Stock level sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync single product stock
     */
    private function syncSingleProductStock($product, $trendyolClient)
    {
        try {
            $updateData = [
                'barcode' => $product['barcode'],
                'quantity' => max(0, (int)$product['quantity']),
                'listPrice' => (float)$product['price'],
                'salePrice' => (float)($product['special'] ?: $product['price'])
            ];

            $response = $trendyolClient->updateProduct($product['barcode'], $updateData);
            $this->stats['api_calls']++;

            if ($response['success']) {
                // Update sync status
                $this->updateStockSyncStatus($product['product_id'], 'success');
                $this->stats['stock_updated']++;

                // Check for low stock alerts
                if ($product['quantity'] <= $this->lowStockThreshold && $product['quantity'] > $this->outOfStockThreshold) {
                    $this->createLowStockAlert($product);
                    $this->stats['low_stock_alerts']++;
                } elseif ($product['quantity'] <= $this->outOfStockThreshold) {
                    $this->createOutOfStockAlert($product);
                    $this->stats['out_of_stock_alerts']++;
                }

                $this->writeLog('[STOCK SYNC] Stock updated for product: ' . $product['model'] . ' (Qty: ' . $product['quantity'] . ')');
            } else {
                $this->updateStockSyncStatus($product['product_id'], 'error', $response['error']);
                $this->writeLog('[STOCK SYNC] Stock update failed for product: ' . $product['model'] . ' - ' . $response['error']);
            }
        } catch (Exception $e) {
            $this->writeLog('[STOCK SYNC] Single product stock sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync price changes
     */
    private function syncPriceChanges()
    {
        $this->writeLog('[STOCK SYNC] Starting price change synchronization');

        try {
            // Get products with price changes
            $query = $this->db->query(
                "
                SELECT p.product_id, p.price, p.special, p.model, tp.barcode, tp.last_price_sync
                FROM " . DB_PREFIX . "product p
                INNER JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                WHERE tp.status = 'active'
                AND p.status = 1
                AND (tp.last_price_sync IS NULL
                     OR tp.last_price_sync < DATE_SUB(NOW(), INTERVAL 2 HOUR)
                     OR p.date_modified > tp.last_price_sync)
                ORDER BY p.date_modified DESC
                LIMIT " . ($this->batchSize / 2)
            );

            if (!$query) {
                return;
            }

            $products = [];
            while ($row = $query->fetch_assoc()) {
                $products[] = $row;
            }

            // Load Trendyol API client
            require_once(DIR_SYSTEM . 'library/meschain/api/trendyol_client.php');
            $trendyolClient = new \MesChain\Api\TrendyolClient((object)$this->config, $this);

            foreach ($products as $product) {
                if ($this->isExecutionTimeExceeded()) {
                    break;
                }

                $this->syncSingleProductPrice($product, $trendyolClient);

                // Rate limiting
                $this->enforceRateLimit();
            }

            $this->writeLog('[STOCK SYNC] Price change synchronization completed. Processed: ' . count($products));
        } catch (Exception $e) {
            $this->writeLog('[STOCK SYNC] Price change sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync single product price
     */
    private function syncSingleProductPrice($product, $trendyolClient)
    {
        try {
            $updateData = [
                'barcode' => $product['barcode'],
                'listPrice' => (float)$product['price'],
                'salePrice' => (float)($product['special'] ?: $product['price'])
            ];

            $response = $trendyolClient->updateProduct($product['barcode'], $updateData);
            $this->stats['api_calls']++;

            if ($response['success']) {
                // Update price sync status
                $this->updatePriceSyncStatus($product['product_id'], 'success');
                $this->stats['price_updated']++;

                $this->writeLog('[STOCK SYNC] Price updated for product: ' . $product['model'] .
                    ' (List: ' . $product['price'] . ', Sale: ' . ($product['special'] ?: $product['price']) . ')');
            } else {
                $this->updatePriceSyncStatus($product['product_id'], 'error', $response['error']);
                $this->writeLog('[STOCK SYNC] Price update failed for product: ' . $product['model'] . ' - ' . $response['error']);
            }
        } catch (Exception $e) {
            $this->writeLog('[STOCK SYNC] Single product price sync error: ' . $e->getMessage());
        }
    }

    /**
     * Perform bulk stock update for better performance
     */
    private function performBulkStockUpdate()
    {
        $this->writeLog('[STOCK SYNC] Starting bulk stock update');

        try {
            // Get products for bulk update
            $query = $this->db->query(
                "
                SELECT p.product_id, p.quantity, p.price, p.special, tp.barcode
                FROM " . DB_PREFIX . "product p
                INNER JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                WHERE tp.status = 'active'
                AND p.status = 1
                AND (tp.last_stock_sync IS NULL OR tp.last_stock_sync < DATE_SUB(NOW(), INTERVAL 30 MINUTE))
                ORDER BY p.date_modified DESC
                LIMIT " . $this->bulkBatchSize
            );

            if (!$query) {
                return;
            }

            $products = [];
            while ($row = $query->fetch_assoc()) {
                $products[] = [
                    'product_id' => $row['product_id'],
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

                // Prepare bulk update data
                $bulkData = [];
                foreach ($products as $product) {
                    $bulkData[] = [
                        'barcode' => $product['barcode'],
                        'quantity' => $product['quantity'],
                        'listPrice' => $product['listPrice'],
                        'salePrice' => $product['salePrice']
                    ];
                }

                // Perform bulk update
                $response = $trendyolClient->makeApiRequest(
                    'suppliers/' . $this->config['meschain_trendyol_supplier_id'] . '/products/price-and-inventory',
                    'POST',
                    ['items' => $bulkData]
                );

                $this->stats['api_calls']++;
                $this->stats['bulk_operations']++;

                if ($response['success']) {
                    // Update sync status for all products
                    foreach ($products as $product) {
                        $this->updateStockSyncStatus($product['product_id'], 'success');
                    }

                    $this->stats['stock_updated'] += count($products);
                    $this->writeLog('[STOCK SYNC] Bulk stock update completed for ' . count($products) . ' products');
                } else {
                    $this->writeLog('[STOCK SYNC] Bulk stock update failed: ' . $response['error']);
                }
            }
        } catch (Exception $e) {
            $this->writeLog('[STOCK SYNC] Bulk stock update error: ' . $e->getMessage());
        }
    }

    /**
     * Check for low stock alerts
     */
    private function checkLowStockAlerts()
    {
        $this->writeLog('[STOCK SYNC] Checking low stock alerts');

        try {
            // Get products with low stock
            $query = $this->db->query("
                SELECT p.product_id, p.quantity, p.model, pd.name, tp.barcode
                FROM " . DB_PREFIX . "product p
                LEFT JOIN " . DB_PREFIX . "product_description pd ON p.product_id = pd.product_id AND pd.language_id = 1
                INNER JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                WHERE tp.status = 'active'
                AND p.status = 1
                AND p.quantity <= " . $this->lowStockThreshold . "
                AND p.quantity > " . $this->outOfStockThreshold . "
                AND (tp.low_stock_alerted = 0 OR tp.low_stock_alerted IS NULL)
            ");

            if (!$query) {
                return;
            }

            $products = [];
            while ($row = $query->fetch_assoc()) {
                $products[] = $row;
            }

            foreach ($products as $product) {
                $this->createLowStockAlert($product);
                $this->stats['low_stock_alerts']++;
            }

            $this->writeLog('[STOCK SYNC] Low stock alerts checked. Alerts created: ' . count($products));
        } catch (Exception $e) {
            $this->writeLog('[STOCK SYNC] Low stock alerts error: ' . $e->getMessage());
        }
    }

    /**
     * Sync stock from Trendyol (bidirectional sync)
     */
    private function syncFromTrendyol()
    {
        $this->writeLog('[STOCK SYNC] Starting bidirectional stock sync from Trendyol');

        try {
            // This would implement pulling stock updates from Trendyol
            // For now, we'll just log that this feature is available
            $this->writeLog('[STOCK SYNC] Bidirectional sync from Trendyol - feature ready for implementation');
        } catch (Exception $e) {
            $this->writeLog('[STOCK SYNC] Sync from Trendyol error: ' . $e->getMessage());
        }
    }

    /**
     * Update stock history for tracking
     */
    private function updateStockHistory()
    {
        $this->writeLog('[STOCK SYNC] Updating stock history');

        try {
            // Create stock history entries for tracking
            $query = $this->db->query("
                SELECT p.product_id, p.quantity, p.price, p.special
                FROM " . DB_PREFIX . "product p
                INNER JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                WHERE tp.status = 'active'
                AND tp.last_stock_sync > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                LIMIT 50
            ");

            if (!$query) {
                return;
            }

            $products = [];
            while ($row = $query->fetch_assoc()) {
                $products[] = $row;
            }

            foreach ($products as $product) {
                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "trendyol_stock_history SET
                    product_id = " . (int)$product['product_id'] . ",
                    quantity = " . (int)$product['quantity'] . ",
                    price = " . (float)$product['price'] . ",
                    special_price = " . (float)($product['special'] ?: 0) . ",
                    sync_date = NOW()
                ");
            }

            // Clean up old history (keep last 30 days)
            $this->db->query("
                DELETE FROM " . DB_PREFIX . "trendyol_stock_history
                WHERE sync_date < DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");

            $this->writeLog('[STOCK SYNC] Stock history updated for ' . count($products) . ' products');
        } catch (Exception $e) {
            $this->writeLog('[STOCK SYNC] Stock history update error: ' . $e->getMessage());
        }
    }

    /**
     * Helper methods
     */
    private function createLowStockAlert($product)
    {
        try {
            // Insert low stock alert
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "trendyol_stock_alerts SET
                product_id = " . (int)$product['product_id'] . ",
                alert_type = 'low_stock',
                current_quantity = " . (int)$product['quantity'] . ",
                threshold = " . $this->lowStockThreshold . ",
                message = 'Low stock alert for product: " . $this->db->escape($product['name'] ?? $product['model']) . " (Qty: " . (int)$product['quantity'] . ")',
                created_at = NOW()
            ");

            // Mark as alerted
            $this->db->query(
                "
                UPDATE " . DB_PREFIX . "trendyol_products
                SET low_stock_alerted = 1, updated_at = NOW()
                WHERE opencart_product_id = " . (int)$product['product_id']
            );

            $this->writeLog('[STOCK SYNC] Low stock alert created for: ' . ($product['name'] ?? $product['model']));
        } catch (Exception $e) {
            $this->writeLog('[STOCK SYNC] Create low stock alert error: ' . $e->getMessage());
        }
    }

    private function createOutOfStockAlert($product)
    {
        try {
            // Insert out of stock alert
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "trendyol_stock_alerts SET
                product_id = " . (int)$product['product_id'] . ",
                alert_type = 'out_of_stock',
                current_quantity = " . (int)$product['quantity'] . ",
                threshold = " . $this->outOfStockThreshold . ",
                message = 'Out of stock alert for product: " . $this->db->escape($product['name'] ?? $product['model']) . " (Qty: " . (int)$product['quantity'] . ")',
                created_at = NOW()
            ");

            // Mark as alerted
            $this->db->query(
                "
                UPDATE " . DB_PREFIX . "trendyol_products
                SET out_of_stock_alerted = 1, updated_at = NOW()
                WHERE opencart_product_id = " . (int)$product['product_id']
            );

            $this->writeLog('[STOCK SYNC] Out of stock alert created for: ' . ($product['name'] ?? $product['model']));
        } catch (Exception $e) {
            $this->writeLog('[STOCK SYNC] Create out of stock alert error: ' . $e->getMessage());
        }
    }

    private function updateStockSyncStatus($productId, $status, $error = null)
    {
        $sql = "
            UPDATE " . DB_PREFIX . "trendyol_products
            SET last_stock_sync = NOW(), updated_at = NOW()";

        if ($status === 'success') {
            $sql .= ", low_stock_alerted = 0, out_of_stock_alerted = 0";
        }

        if ($error) {
            $sql .= ", rejection_reason = '" . $this->db->escape($error) . "'";
        }

        $sql .= " WHERE opencart_product_id = " . (int)$productId;

        $this->db->query($sql);
    }

    private function updatePriceSyncStatus($productId, $status, $error = null)
    {
        $sql = "
            UPDATE " . DB_PREFIX . "trendyol_products
            SET last_price_sync = NOW(), updated_at = NOW()";

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

        return (time() - filemtime($this->lockFile)) < 600; // 10 minutes
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
            !empty($this->config['meschain_trendyol_stock_sync']);
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
            '[STOCK SYNC] Statistics - Processed: %d, Stock Updated: %d, Price Updated: %d, Bulk Ops: %d, Low Stock: %d, Out of Stock: %d, API Calls: %d, Time: %s seconds',
            $this->stats['products_processed'],
            $this->stats['stock_updated'],
            $this->stats['price_updated'],
            $this->stats['bulk_operations'],
            $this->stats['low_stock_alerts'],
            $this->stats['out_of_stock_alerts'],
            $this->stats['api_calls'],
            $this->stats['execution_time']
        );

        $this->writeLog($statsMessage);

        // Save stats to database
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "trendyol_sync_stats SET
            sync_type = 'stock',
            products_processed = " . (int)$this->stats['products_processed'] . ",
            stock_updated = " . (int)$this->stats['stock_updated'] . ",
            price_updated = " . (int)$this->stats['price_updated'] . ",
            bulk_operations = " . (int)$this->stats['bulk_operations'] . ",
            low_stock_alerts = " . (int)$this->stats['low_stock_alerts'] . ",
            out_of_stock_alerts = " . (int)$this->stats['out_of_stock_alerts'] . ",
            api_calls = " . (int)$this->stats['api_calls'] . ",
            execution_time = " . (float)$this->stats['execution_time'] . ",
            created_at = NOW()
        ");
    }

    private function sendErrorAlert($message)
    {
        $this->writeLog('[STOCK SYNC] ERROR ALERT: ' . $message);

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
    $stockSync = new TrendyolStockSync();
    $stockSync->run();
    echo "Trendyol stock synchronization completed successfully\n";
} catch (Exception $e) {
    echo "Trendyol stock synchronization failed: " . $e->getMessage() . "\n";
    exit(1);
}
