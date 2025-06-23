<?php

/**
 * MesChain Trendyol Main Synchronization Cron Job
 * Day 5-6: Automatic Synchronization System
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

// Database
$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
if ($db->connect_error) {
    die('Database connection failed: ' . $db->connect_error);
}

// Registry
$registry = new Registry();
$registry->set('db', $db);

// Config
$config = new Config();
$config->load('default');
$registry->set('config', $config);

// Log
$log = new Log('trendyol_sync.log');
$registry->set('log', $log);

/**
 * Trendyol Sync Manager Class
 */
class TrendyolSyncManager
{
    private $registry;
    private $db;
    private $config;
    private $log;
    private $startTime;
    private $stats;
    private $lockFile;
    private $maxExecutionTime = 3600; // 1 hour
    private $batchSize = 50;

    // Rate limiting
    private $apiCallCount = 0;
    private $lastApiCall = 0;
    private $rateLimitPerMinute = 600;
    private $rateLimitPerHour = 36000;

    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->log = $registry->get('log');
        $this->startTime = microtime(true);
        $this->lockFile = sys_get_temp_dir() . '/trendyol_sync.lock';

        $this->stats = [
            'products_synced' => 0,
            'orders_synced' => 0,
            'stock_updated' => 0,
            'errors' => 0,
            'api_calls' => 0,
            'execution_time' => 0
        ];

        // Set memory and time limits
        ini_set('memory_limit', '512M');
        set_time_limit($this->maxExecutionTime);
    }

    /**
     * Main sync execution
     */
    public function run()
    {
        try {
            $this->log->write('[TRENDYOL SYNC] Starting main synchronization process');

            // Check if already running
            if ($this->isRunning()) {
                $this->log->write('[TRENDYOL SYNC] Another sync process is already running');
                return false;
            }

            // Create lock file
            $this->createLock();

            // Check if sync is enabled
            if (!$this->isSyncEnabled()) {
                $this->log->write('[TRENDYOL SYNC] Synchronization is disabled');
                $this->removeLock();
                return false;
            }

            // Validate API credentials
            if (!$this->validateApiCredentials()) {
                $this->log->write('[TRENDYOL SYNC] API credentials validation failed');
                $this->removeLock();
                return false;
            }

            // Run synchronization tasks
            $this->syncProducts();
            $this->syncOrders();
            $this->syncStock();
            $this->processWebhookQueue();
            $this->cleanupOldLogs();

            // Calculate execution time
            $this->stats['execution_time'] = round(microtime(true) - $this->startTime, 2);

            // Log final statistics
            $this->logStats();

            // Send performance alert if needed
            $this->checkPerformanceAlerts();

            $this->log->write('[TRENDYOL SYNC] Synchronization completed successfully');
        } catch (Exception $e) {
            $this->log->write('[TRENDYOL SYNC] Fatal error: ' . $e->getMessage());
            $this->stats['errors']++;

            // Send error alert
            $this->sendErrorAlert($e->getMessage());
        } finally {
            $this->removeLock();
        }

        return true;
    }

    /**
     * Sync products to Trendyol
     */
    private function syncProducts()
    {
        $this->log->write('[TRENDYOL SYNC] Starting product synchronization');

        try {
            // Get products that need sync
            $query = $this->db->query(
                "
                SELECT p.*, tp.barcode, tp.last_sync, tp.status as trendyol_status
                FROM " . DB_PREFIX . "product p
                LEFT JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                WHERE p.status = 1
                AND (tp.last_sync IS NULL OR tp.last_sync < DATE_SUB(NOW(), INTERVAL 6 HOUR))
                ORDER BY p.date_modified DESC
                LIMIT " . $this->batchSize
            );

            $products = $query->rows;

            foreach ($products as $product) {
                if ($this->isExecutionTimeExceeded()) {
                    $this->log->write('[TRENDYOL SYNC] Execution time limit reached, stopping product sync');
                    break;
                }

                $this->syncSingleProduct($product);
                $this->stats['products_synced']++;

                // Rate limiting
                $this->enforceRateLimit();
            }

            $this->log->write('[TRENDYOL SYNC] Product synchronization completed. Synced: ' . $this->stats['products_synced']);
        } catch (Exception $e) {
            $this->log->write('[TRENDYOL SYNC] Product sync error: ' . $e->getMessage());
            $this->stats['errors']++;
        }
    }

    /**
     * Sync single product
     */
    private function syncSingleProduct($product)
    {
        try {
            // Load Trendyol API client
            require_once(DIR_SYSTEM . 'library/meschain/api/trendyol_client.php');
            $trendyolClient = new \MesChain\Api\TrendyolClient($this->config, $this->log);

            // Format product for Trendyol
            $formattedProduct = $trendyolClient->formatProductForTrendyol($product);

            // Upload or update product
            if (empty($product['trendyol_status'])) {
                // New product - upload
                $response = $trendyolClient->uploadProduct($formattedProduct);
            } else {
                // Existing product - update stock and price
                $response = $trendyolClient->updateProduct($product['barcode'], [
                    'barcode' => $product['barcode'],
                    'quantity' => max(0, (int)$product['quantity']),
                    'listPrice' => (float)$product['price'],
                    'salePrice' => (float)($product['special'] ?: $product['price'])
                ]);
            }

            $this->stats['api_calls']++;

            if ($response['success']) {
                // Update sync status
                $this->updateProductSyncStatus($product['product_id'], 'success');
                $this->log->write('[TRENDYOL SYNC] Product synced: ' . $product['name']);
            } else {
                $this->updateProductSyncStatus($product['product_id'], 'error', $response['error']);
                $this->log->write('[TRENDYOL SYNC] Product sync failed: ' . $product['name'] . ' - ' . $response['error']);
                $this->stats['errors']++;
            }
        } catch (Exception $e) {
            $this->log->write('[TRENDYOL SYNC] Single product sync error: ' . $e->getMessage());
            $this->stats['errors']++;
        }
    }

    /**
     * Sync orders from Trendyol
     */
    private function syncOrders()
    {
        $this->log->write('[TRENDYOL SYNC] Starting order synchronization');

        try {
            // Load Trendyol API client
            require_once(DIR_SYSTEM . 'library/meschain/api/trendyol_client.php');
            $trendyolClient = new \MesChain\Api\TrendyolClient($this->config, $this->log);

            // Get orders from last 24 hours
            $filters = [
                'startDate' => date('Y-m-d', strtotime('-1 day')),
                'endDate' => date('Y-m-d'),
                'page' => 0,
                'size' => $this->batchSize
            ];

            $response = $trendyolClient->getOrders($filters);
            $this->stats['api_calls']++;

            if ($response['success']) {
                $orders = $response['data']['content'] ?? [];

                foreach ($orders as $order) {
                    if ($this->isExecutionTimeExceeded()) {
                        $this->log->write('[TRENDYOL SYNC] Execution time limit reached, stopping order sync');
                        break;
                    }

                    $this->syncSingleOrder($order);
                    $this->stats['orders_synced']++;

                    // Rate limiting
                    $this->enforceRateLimit();
                }

                $this->log->write('[TRENDYOL SYNC] Order synchronization completed. Synced: ' . $this->stats['orders_synced']);
            } else {
                $this->log->write('[TRENDYOL SYNC] Order sync failed: ' . $response['error']);
                $this->stats['errors']++;
            }
        } catch (Exception $e) {
            $this->log->write('[TRENDYOL SYNC] Order sync error: ' . $e->getMessage());
            $this->stats['errors']++;
        }
    }

    /**
     * Sync single order
     */
    private function syncSingleOrder($order)
    {
        try {
            // Check if order already exists
            $query = $this->db->query("
                SELECT order_id FROM " . DB_PREFIX . "trendyol_orders
                WHERE order_number = '" . $this->db->escape($order['orderNumber']) . "'
            ");

            if ($query->num_rows > 0) {
                // Update existing order
                $this->updateTrendyolOrder($order);
            } else {
                // Insert new order
                $this->insertTrendyolOrder($order);
            }

            $this->log->write('[TRENDYOL SYNC] Order synced: ' . $order['orderNumber']);
        } catch (Exception $e) {
            $this->log->write('[TRENDYOL SYNC] Single order sync error: ' . $e->getMessage());
            $this->stats['errors']++;
        }
    }

    /**
     * Sync stock levels
     */
    private function syncStock()
    {
        $this->log->write('[TRENDYOL SYNC] Starting stock synchronization');

        try {
            // Get products with stock changes
            $query = $this->db->query(
                "
                SELECT p.product_id, p.quantity, p.model, tp.barcode, tp.trendyol_product_id
                FROM " . DB_PREFIX . "product p
                INNER JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                WHERE tp.status = 'active'
                AND (tp.last_sync IS NULL OR tp.last_sync < DATE_SUB(NOW(), INTERVAL 1 HOUR))
                ORDER BY p.date_modified DESC
                LIMIT " . $this->batchSize
            );

            $products = $query->rows;

            // Load Trendyol API client
            require_once(DIR_SYSTEM . 'library/meschain/api/trendyol_client.php');
            $trendyolClient = new \MesChain\Api\TrendyolClient($this->config, $this->log);

            foreach ($products as $product) {
                if ($this->isExecutionTimeExceeded()) {
                    $this->log->write('[TRENDYOL SYNC] Execution time limit reached, stopping stock sync');
                    break;
                }

                // Update stock on Trendyol
                $response = $trendyolClient->updateProduct($product['barcode'], [
                    'barcode' => $product['barcode'],
                    'quantity' => max(0, (int)$product['quantity'])
                ]);

                $this->stats['api_calls']++;

                if ($response['success']) {
                    $this->updateProductSyncStatus($product['product_id'], 'success');
                    $this->stats['stock_updated']++;
                } else {
                    $this->log->write('[TRENDYOL SYNC] Stock update failed for product: ' . $product['model'] . ' - ' . $response['error']);
                    $this->stats['errors']++;
                }

                // Rate limiting
                $this->enforceRateLimit();
            }

            $this->log->write('[TRENDYOL SYNC] Stock synchronization completed. Updated: ' . $this->stats['stock_updated']);
        } catch (Exception $e) {
            $this->log->write('[TRENDYOL SYNC] Stock sync error: ' . $e->getMessage());
            $this->stats['errors']++;
        }
    }

    /**
     * Process webhook queue
     */
    private function processWebhookQueue()
    {
        $this->log->write('[TRENDYOL SYNC] Processing webhook queue');

        try {
            // Get unprocessed webhooks
            $query = $this->db->query("
                SELECT * FROM " . DB_PREFIX . "trendyol_webhook_logs
                WHERE processed = 0
                AND received_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY received_at ASC
                LIMIT 100
            ");

            $webhooks = $query->rows;
            $processed = 0;

            foreach ($webhooks as $webhook) {
                if ($this->isExecutionTimeExceeded()) {
                    break;
                }

                $this->processWebhookEvent($webhook);
                $processed++;
            }

            $this->log->write('[TRENDYOL SYNC] Webhook queue processed. Events: ' . $processed);
        } catch (Exception $e) {
            $this->log->write('[TRENDYOL SYNC] Webhook queue processing error: ' . $e->getMessage());
            $this->stats['errors']++;
        }
    }

    /**
     * Process single webhook event
     */
    private function processWebhookEvent($webhook)
    {
        try {
            $eventData = json_decode($webhook['event_data'], true);
            $eventType = $webhook['event_type'];

            switch ($eventType) {
                case 'ORDER_CREATED':
                case 'NewOrder':
                    $this->processOrderCreatedWebhook($eventData);
                    break;

                case 'ORDER_STATUS_CHANGED':
                    $this->processOrderStatusWebhook($eventData);
                    break;

                case 'INVENTORY_UPDATED':
                    $this->processInventoryWebhook($eventData);
                    break;

                default:
                    $this->log->write('[TRENDYOL SYNC] Unknown webhook event type: ' . $eventType);
            }

            // Mark as processed
            $this->db->query(
                "
                UPDATE " . DB_PREFIX . "trendyol_webhook_logs
                SET processed = 1, processed_at = NOW()
                WHERE log_id = " . (int)$webhook['log_id']
            );
        } catch (Exception $e) {
            $this->log->write('[TRENDYOL SYNC] Webhook event processing error: ' . $e->getMessage());

            // Mark as error
            $this->db->query(
                "
                UPDATE " . DB_PREFIX . "trendyol_webhook_logs
                SET processed = 1, processed_at = NOW(), error_message = '" . $this->db->escape($e->getMessage()) . "'
                WHERE log_id = " . (int)$webhook['log_id']
            );
        }
    }

    /**
     * Helper methods
     */
    private function isRunning()
    {
        if (!file_exists($this->lockFile)) {
            return false;
        }

        $pid = file_get_contents($this->lockFile);

        // Check if process is still running (Unix/Linux)
        if (function_exists('posix_kill')) {
            return posix_kill($pid, 0);
        }

        // For Windows or when posix functions are not available
        return file_exists($this->lockFile) && (time() - filemtime($this->lockFile)) < 3600;
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
        return (bool)$this->config->get('meschain_trendyol_auto_sync');
    }

    private function validateApiCredentials()
    {
        $apiKey = $this->config->get('meschain_trendyol_api_key');
        $apiSecret = $this->config->get('meschain_trendyol_api_secret');
        $supplierId = $this->config->get('meschain_trendyol_supplier_id');

        return !empty($apiKey) && !empty($apiSecret) && !empty($supplierId);
    }

    private function isExecutionTimeExceeded()
    {
        return (microtime(true) - $this->startTime) > ($this->maxExecutionTime - 60);
    }

    private function enforceRateLimit()
    {
        $this->apiCallCount++;
        $currentTime = time();

        // Check if we need to wait
        if ($this->apiCallCount >= $this->rateLimitPerMinute) {
            $timeDiff = $currentTime - $this->lastApiCall;
            if ($timeDiff < 60) {
                sleep(60 - $timeDiff);
            }
            $this->apiCallCount = 0;
        }

        $this->lastApiCall = $currentTime;
        usleep(100000); // 0.1 second delay between API calls
    }

    private function updateProductSyncStatus($productId, $status, $error = null)
    {
        $this->db->query(
            "
            INSERT INTO " . DB_PREFIX . "trendyol_products
            (opencart_product_id, status, last_sync, updated_at)
            VALUES (" . (int)$productId . ", '" . $this->db->escape($status) . "', NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            status = VALUES(status),
            last_sync = VALUES(last_sync),
            updated_at = VALUES(updated_at)" .
                ($error ? ", rejection_reason = '" . $this->db->escape($error) . "'" : "")
        );
    }

    private function insertTrendyolOrder($order)
    {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "trendyol_orders SET
            order_number = '" . $this->db->escape($order['orderNumber']) . "',
            gross_amount = '" . (float)($order['grossAmount'] ?? 0) . "',
            total_discount = '" . (float)($order['totalDiscount'] ?? 0) . "',
            customer_name = '" . $this->db->escape(($order['customerFirstName'] ?? '') . ' ' . ($order['customerLastName'] ?? '')) . "',
            customer_email = '" . $this->db->escape($order['customerEmail'] ?? '') . "',
            order_date = '" . $this->db->escape(date('Y-m-d H:i:s', ($order['orderDate'] ?? time()) / 1000)) . "',
            status = '" . $this->db->escape($order['status'] ?? 'Created') . "',
            order_data = '" . $this->db->escape(json_encode($order)) . "',
            created_at = NOW(),
            updated_at = NOW()
        ");
    }

    private function updateTrendyolOrder($order)
    {
        $this->db->query("
            UPDATE " . DB_PREFIX . "trendyol_orders SET
            gross_amount = '" . (float)($order['grossAmount'] ?? 0) . "',
            total_discount = '" . (float)($order['totalDiscount'] ?? 0) . "',
            status = '" . $this->db->escape($order['status'] ?? 'Created') . "',
            order_data = '" . $this->db->escape(json_encode($order)) . "',
            updated_at = NOW()
            WHERE order_number = '" . $this->db->escape($order['orderNumber']) . "'
        ");
    }

    private function processOrderCreatedWebhook($eventData)
    {
        $this->insertTrendyolOrder($eventData);

        // Auto-convert to OpenCart order if enabled
        if ($this->config->get('meschain_trendyol_auto_convert_orders')) {
            $this->convertToOpenCartOrder($eventData);
        }
    }

    private function processOrderStatusWebhook($eventData)
    {
        $this->db->query("
            UPDATE " . DB_PREFIX . "trendyol_orders
            SET status = '" . $this->db->escape($eventData['status']) . "', updated_at = NOW()
            WHERE order_number = '" . $this->db->escape($eventData['orderNumber']) . "'
        ");
    }

    private function processInventoryWebhook($eventData)
    {
        // Update local inventory based on Trendyol webhook
        $barcode = $eventData['barcode'] ?? '';
        $quantity = (int)($eventData['quantity'] ?? 0);

        if ($barcode) {
            $this->db->query("
                UPDATE " . DB_PREFIX . "product p
                INNER JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                SET p.quantity = " . $quantity . "
                WHERE tp.barcode = '" . $this->db->escape($barcode) . "'
            ");
        }
    }

    private function convertToOpenCartOrder($orderData)
    {
        // Implementation for converting Trendyol order to OpenCart order
        // This would be similar to the webhook controller's convertToOpenCartOrder method
    }

    private function cleanupOldLogs()
    {
        // Clean up old API logs (older than 30 days)
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "trendyol_api_logs
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");

        // Clean up old webhook logs (older than 30 days)
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "trendyol_webhook_logs
            WHERE received_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
    }

    private function logStats()
    {
        $statsMessage = sprintf(
            '[TRENDYOL SYNC] Statistics - Products: %d, Orders: %d, Stock: %d, API Calls: %d, Errors: %d, Time: %s seconds',
            $this->stats['products_synced'],
            $this->stats['orders_synced'],
            $this->stats['stock_updated'],
            $this->stats['api_calls'],
            $this->stats['errors'],
            $this->stats['execution_time']
        );

        $this->log->write($statsMessage);

        // Save stats to database
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "trendyol_sync_stats SET
            products_synced = " . (int)$this->stats['products_synced'] . ",
            orders_synced = " . (int)$this->stats['orders_synced'] . ",
            stock_updated = " . (int)$this->stats['stock_updated'] . ",
            api_calls = " . (int)$this->stats['api_calls'] . ",
            errors = " . (int)$this->stats['errors'] . ",
            execution_time = " . (float)$this->stats['execution_time'] . ",
            created_at = NOW()
        ");
    }

    private function checkPerformanceAlerts()
    {
        // Send alert if execution time is too long
        if ($this->stats['execution_time'] > 1800) { // 30 minutes
            $this->sendPerformanceAlert('Long execution time: ' . $this->stats['execution_time'] . ' seconds');
        }

        // Send alert if too many errors
        if ($this->stats['errors'] > 10) {
            $this->sendPerformanceAlert('High error count: ' . $this->stats['errors'] . ' errors');
        }
    }

    private function sendErrorAlert($message)
    {
        $this->log->write('[TRENDYOL SYNC] ERROR ALERT: ' . $message);

        // Here you could implement email notifications, Slack webhooks, etc.
        // For now, we'll just log it
    }

    private function sendPerformanceAlert($message)
    {
        $this->log->write('[TRENDYOL SYNC] PERFORMANCE ALERT: ' . $message);

        // Here you could implement performance monitoring alerts
    }
}

// Create sync manager and run
try {
    $syncManager = new TrendyolSyncManager($registry);
    $syncManager->run();
    echo "Trendyol synchronization completed successfully\n";
} catch (Exception $e) {
    echo "Trendyol synchronization failed: " . $e->getMessage() . "\n";
    exit(1);
}
