<?php

/**
 * MesChain Trendyol Webhook Processor
 * Day 5-6: Background webhook processing with queue system
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
 * Trendyol Webhook Processor
 */
class TrendyolWebhookProcessor
{
    private $db;
    private $config;
    private $log;
    private $startTime;
    private $stats;
    private $lockFile;
    private $maxExecutionTime = 300; // 5 minutes
    private $batchSize = 50;
    private $maxRetries = 3;

    // Webhook event priorities
    private $eventPriorities = [
        'ORDER_CREATED' => 1,
        'ORDER_CANCELLED' => 1,
        'ORDER_STATUS_CHANGED' => 2,
        'SHIPMENT_CREATED' => 2,
        'INVENTORY_UPDATED' => 3,
        'PRICE_UPDATED' => 3,
        'PRODUCT_APPROVED' => 4,
        'PRODUCT_REJECTED' => 4,
        'RETURN_INITIATED' => 2
    ];

    public function __construct()
    {
        global $db;
        $this->db = $db;
        $this->startTime = microtime(true);
        $this->lockFile = sys_get_temp_dir() . '/trendyol_webhook_processor.lock';

        // Initialize log
        $this->log = new stdClass();
        $this->log->file = fopen(DIR_LOGS . 'trendyol_webhook_processor.log', 'a');

        $this->stats = [
            'webhooks_processed' => 0,
            'webhooks_success' => 0,
            'webhooks_failed' => 0,
            'webhooks_retried' => 0,
            'orders_processed' => 0,
            'products_processed' => 0,
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
    }

    /**
     * Main processing execution
     */
    public function run()
    {
        try {
            $this->writeLog('[WEBHOOK PROCESSOR] Starting webhook processing');

            // Check if already running
            if ($this->isRunning()) {
                $this->writeLog('[WEBHOOK PROCESSOR] Another processor is already running');
                return false;
            }

            // Create lock file
            $this->createLock();

            // Check if processing is enabled
            if (!$this->isProcessingEnabled()) {
                $this->writeLog('[WEBHOOK PROCESSOR] Webhook processing is disabled');
                $this->removeLock();
                return false;
            }

            // Process webhook queue
            $this->processWebhookQueue();
            $this->processFailedWebhooks();
            $this->cleanupOldWebhooks();
            $this->updateWebhookStats();

            // Calculate execution time
            $this->stats['execution_time'] = round(microtime(true) - $this->startTime, 2);

            // Log final statistics
            $this->logStats();

            $this->writeLog('[WEBHOOK PROCESSOR] Webhook processing completed successfully');
        } catch (Exception $e) {
            $this->writeLog('[WEBHOOK PROCESSOR] Fatal error: ' . $e->getMessage());
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
     * Process webhook queue
     */
    private function processWebhookQueue()
    {
        $this->writeLog('[WEBHOOK PROCESSOR] Processing webhook queue');

        try {
            // Get unprocessed webhooks ordered by priority and received time
            $query = $this->db->query(
                "
                SELECT wl.*,
                       CASE wl.event_type
                           WHEN 'ORDER_CREATED' THEN 1
                           WHEN 'ORDER_CANCELLED' THEN 1
                           WHEN 'ORDER_STATUS_CHANGED' THEN 2
                           WHEN 'SHIPMENT_CREATED' THEN 2
                           WHEN 'RETURN_INITIATED' THEN 2
                           WHEN 'INVENTORY_UPDATED' THEN 3
                           WHEN 'PRICE_UPDATED' THEN 3
                           WHEN 'PRODUCT_APPROVED' THEN 4
                           WHEN 'PRODUCT_REJECTED' THEN 4
                           ELSE 5
                       END as priority
                FROM " . DB_PREFIX . "trendyol_webhook_logs wl
                WHERE wl.processed = 0
                AND wl.retry_count < " . $this->maxRetries . "
                AND wl.received_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY priority ASC, wl.received_at ASC
                LIMIT " . $this->batchSize
            );

            if (!$query) {
                return;
            }

            $webhooks = [];
            while ($row = $query->fetch_assoc()) {
                $webhooks[] = $row;
            }

            foreach ($webhooks as $webhook) {
                if ($this->isExecutionTimeExceeded()) {
                    $this->writeLog('[WEBHOOK PROCESSOR] Execution time limit reached, stopping processing');
                    break;
                }

                $this->processWebhookEvent($webhook);
                $this->stats['webhooks_processed']++;
            }

            $this->writeLog('[WEBHOOK PROCESSOR] Webhook queue processing completed. Processed: ' . count($webhooks));
        } catch (Exception $e) {
            $this->writeLog('[WEBHOOK PROCESSOR] Webhook queue processing error: ' . $e->getMessage());
        }
    }

    /**
     * Process single webhook event
     */
    private function processWebhookEvent($webhook)
    {
        try {
            $this->writeLog('[WEBHOOK PROCESSOR] Processing webhook: ' . $webhook['event_type'] . ' (ID: ' . $webhook['log_id'] . ')');

            // Mark as processing
            $this->markWebhookAsProcessing($webhook['log_id']);

            $eventData = json_decode($webhook['event_data'], true);
            $eventType = $webhook['event_type'];
            $success = false;
            $errorMessage = null;

            switch ($eventType) {
                case 'ORDER_CREATED':
                case 'NewOrder':
                case 'OrderCreated':
                    $result = $this->processOrderCreatedEvent($eventData);
                    $success = $result['success'];
                    $errorMessage = $result['error'] ?? null;
                    if ($success) $this->stats['orders_processed']++;
                    break;

                case 'ORDER_STATUS_CHANGED':
                case 'OrderStatusChanged':
                    $result = $this->processOrderStatusChangedEvent($eventData);
                    $success = $result['success'];
                    $errorMessage = $result['error'] ?? null;
                    if ($success) $this->stats['orders_processed']++;
                    break;

                case 'ORDER_CANCELLED':
                case 'OrderCancelled':
                    $result = $this->processOrderCancelledEvent($eventData);
                    $success = $result['success'];
                    $errorMessage = $result['error'] ?? null;
                    if ($success) $this->stats['orders_processed']++;
                    break;

                case 'SHIPMENT_CREATED':
                case 'ShipmentCreated':
                    $result = $this->processShipmentCreatedEvent($eventData);
                    $success = $result['success'];
                    $errorMessage = $result['error'] ?? null;
                    if ($success) $this->stats['orders_processed']++;
                    break;

                case 'PRODUCT_APPROVED':
                case 'ProductApproved':
                    $result = $this->processProductApprovedEvent($eventData);
                    $success = $result['success'];
                    $errorMessage = $result['error'] ?? null;
                    if ($success) $this->stats['products_processed']++;
                    break;

                case 'PRODUCT_REJECTED':
                case 'ProductRejected':
                    $result = $this->processProductRejectedEvent($eventData);
                    $success = $result['success'];
                    $errorMessage = $result['error'] ?? null;
                    if ($success) $this->stats['products_processed']++;
                    break;

                case 'INVENTORY_UPDATED':
                case 'InventoryUpdated':
                    $result = $this->processInventoryUpdatedEvent($eventData);
                    $success = $result['success'];
                    $errorMessage = $result['error'] ?? null;
                    if ($success) $this->stats['products_processed']++;
                    break;

                case 'PRICE_UPDATED':
                case 'PriceUpdated':
                    $result = $this->processPriceUpdatedEvent($eventData);
                    $success = $result['success'];
                    $errorMessage = $result['error'] ?? null;
                    if ($success) $this->stats['products_processed']++;
                    break;

                case 'RETURN_INITIATED':
                case 'ReturnInitiated':
                    $result = $this->processReturnInitiatedEvent($eventData);
                    $success = $result['success'];
                    $errorMessage = $result['error'] ?? null;
                    if ($success) $this->stats['orders_processed']++;
                    break;

                default:
                    $success = false;
                    $errorMessage = "Unsupported event type: {$eventType}";
                    $this->writeLog('[WEBHOOK PROCESSOR] Unknown event type: ' . $eventType);
            }

            // Update webhook status
            if ($success) {
                $this->markWebhookAsProcessed($webhook['log_id'], true, null);
                $this->stats['webhooks_success']++;
                $this->writeLog('[WEBHOOK PROCESSOR] Successfully processed webhook: ' . $webhook['event_type']);
            } else {
                $this->markWebhookAsProcessed($webhook['log_id'], false, $errorMessage);
                $this->stats['webhooks_failed']++;
                $this->writeLog('[WEBHOOK PROCESSOR] Failed to process webhook: ' . $webhook['event_type'] . ' - ' . $errorMessage);
            }
        } catch (Exception $e) {
            $this->markWebhookAsProcessed($webhook['log_id'], false, $e->getMessage());
            $this->stats['webhooks_failed']++;
            $this->writeLog('[WEBHOOK PROCESSOR] Webhook processing exception: ' . $e->getMessage());
        }
    }

    /**
     * Process Order Created Event
     */
    private function processOrderCreatedEvent($eventData)
    {
        try {
            $orderNumber = $eventData['orderNumber'] ?? '';

            if (empty($orderNumber)) {
                return ['success' => false, 'error' => 'Missing order number'];
            }

            // Check if order already exists
            $query = $this->db->query("
                SELECT order_id FROM " . DB_PREFIX . "trendyol_orders
                WHERE order_number = '" . $this->db->escape($orderNumber) . "'
            ");

            if ($query && $query->num_rows > 0) {
                return ['success' => true, 'message' => 'Order already exists'];
            }

            // Insert new order
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "trendyol_orders SET
                order_number = '" . $this->db->escape($orderNumber) . "',
                gross_amount = '" . (float)($eventData['grossAmount'] ?? 0) . "',
                total_discount = '" . (float)($eventData['totalDiscount'] ?? 0) . "',
                customer_name = '" . $this->db->escape(($eventData['customerFirstName'] ?? '') . ' ' . ($eventData['customerLastName'] ?? '')) . "',
                customer_email = '" . $this->db->escape($eventData['customerEmail'] ?? '') . "',
                order_date = '" . $this->db->escape(date('Y-m-d H:i:s', ($eventData['orderDate'] ?? time()) / 1000)) . "',
                status = '" . $this->db->escape($eventData['status'] ?? 'Created') . "',
                order_data = '" . $this->db->escape(json_encode($eventData)) . "',
                created_at = NOW(),
                updated_at = NOW()
            ");

            // Auto-convert to OpenCart order if enabled
            if (!empty($this->config['meschain_trendyol_auto_convert_orders'])) {
                $this->convertToOpenCartOrder($eventData);
            }

            return ['success' => true, 'message' => 'Order created successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Order Status Changed Event
     */
    private function processOrderStatusChangedEvent($eventData)
    {
        try {
            $orderNumber = $eventData['orderNumber'] ?? '';
            $newStatus = $eventData['status'] ?? '';

            if (empty($orderNumber) || empty($newStatus)) {
                return ['success' => false, 'error' => 'Missing order number or status'];
            }

            // Update order status
            $this->db->query("
                UPDATE " . DB_PREFIX . "trendyol_orders
                SET status = '" . $this->db->escape($newStatus) . "', updated_at = NOW()
                WHERE order_number = '" . $this->db->escape($orderNumber) . "'
            ");

            // Update corresponding OpenCart order if exists
            $query = $this->db->query("
                SELECT opencart_order_id FROM " . DB_PREFIX . "trendyol_orders
                WHERE order_number = '" . $this->db->escape($orderNumber) . "'
            ");

            if ($query && $query->num_rows > 0) {
                $row = $query->fetch_assoc();
                if ($row['opencart_order_id']) {
                    $this->updateOpenCartOrderStatus($row['opencart_order_id'], $newStatus);
                }
            }

            return ['success' => true, 'message' => 'Order status updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Order Cancelled Event
     */
    private function processOrderCancelledEvent($eventData)
    {
        try {
            $orderNumber = $eventData['orderNumber'] ?? '';

            if (empty($orderNumber)) {
                return ['success' => false, 'error' => 'Missing order number'];
            }

            // Update order status to cancelled
            $this->db->query("
                UPDATE " . DB_PREFIX . "trendyol_orders
                SET status = 'Cancelled', updated_at = NOW()
                WHERE order_number = '" . $this->db->escape($orderNumber) . "'
            ");

            // Update corresponding OpenCart order if exists
            $query = $this->db->query("
                SELECT opencart_order_id FROM " . DB_PREFIX . "trendyol_orders
                WHERE order_number = '" . $this->db->escape($orderNumber) . "'
            ");

            if ($query && $query->num_rows > 0) {
                $row = $query->fetch_assoc();
                if ($row['opencart_order_id']) {
                    $this->updateOpenCartOrderStatus($row['opencart_order_id'], 'Cancelled');
                }
            }

            return ['success' => true, 'message' => 'Order cancelled successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Shipment Created Event
     */
    private function processShipmentCreatedEvent($eventData)
    {
        try {
            $orderNumber = $eventData['orderNumber'] ?? '';
            $trackingNumber = $eventData['trackingNumber'] ?? '';
            $cargoProvider = $eventData['cargoProvider'] ?? '';

            if (empty($orderNumber)) {
                return ['success' => false, 'error' => 'Missing order number'];
            }

            // Update order with shipment info
            $this->db->query("
                UPDATE " . DB_PREFIX . "trendyol_orders
                SET tracking_number = '" . $this->db->escape($trackingNumber) . "',
                    cargo_provider = '" . $this->db->escape($cargoProvider) . "',
                    status = 'Shipped',
                    updated_at = NOW()
                WHERE order_number = '" . $this->db->escape($orderNumber) . "'
            ");

            // Update corresponding OpenCart order if exists
            $query = $this->db->query("
                SELECT opencart_order_id FROM " . DB_PREFIX . "trendyol_orders
                WHERE order_number = '" . $this->db->escape($orderNumber) . "'
            ");

            if ($query && $query->num_rows > 0) {
                $row = $query->fetch_assoc();
                if ($row['opencart_order_id']) {
                    $this->updateOpenCartOrderStatus($row['opencart_order_id'], 'Shipped');

                    // Add tracking info to order history
                    $this->addOrderHistory(
                        $row['opencart_order_id'],
                        3, // Shipped status
                        'Shipment created. Tracking: ' . $trackingNumber . ' (' . $cargoProvider . ')'
                    );
                }
            }

            return ['success' => true, 'message' => 'Shipment information updated'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Product Approved Event
     */
    private function processProductApprovedEvent($eventData)
    {
        try {
            $barcode = $eventData['barcode'] ?? '';

            if (empty($barcode)) {
                return ['success' => false, 'error' => 'Missing barcode'];
            }

            // Update product approval status
            $this->db->query("
                UPDATE " . DB_PREFIX . "trendyol_products
                SET status = 'active',
                    approval_status = 1,
                    rejection_reason = NULL,
                    updated_at = NOW()
                WHERE barcode = '" . $this->db->escape($barcode) . "'
            ");

            return ['success' => true, 'message' => 'Product approved successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Product Rejected Event
     */
    private function processProductRejectedEvent($eventData)
    {
        try {
            $barcode = $eventData['barcode'] ?? '';
            $rejectionReason = $eventData['rejectionReason'] ?? 'No reason provided';

            if (empty($barcode)) {
                return ['success' => false, 'error' => 'Missing barcode'];
            }

            // Update product approval status
            $this->db->query("
                UPDATE " . DB_PREFIX . "trendyol_products
                SET status = 'rejected',
                    approval_status = 0,
                    rejection_reason = '" . $this->db->escape($rejectionReason) . "',
                    updated_at = NOW()
                WHERE barcode = '" . $this->db->escape($barcode) . "'
            ");

            return ['success' => true, 'message' => 'Product rejection processed'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Inventory Updated Event
     */
    private function processInventoryUpdatedEvent($eventData)
    {
        try {
            $barcode = $eventData['barcode'] ?? '';
            $quantity = (int)($eventData['quantity'] ?? 0);

            if (empty($barcode)) {
                return ['success' => false, 'error' => 'Missing barcode'];
            }

            // Update product inventory in OpenCart
            $this->db->query("
                UPDATE " . DB_PREFIX . "product p
                INNER JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                SET p.quantity = " . $quantity . "
                WHERE tp.barcode = '" . $this->db->escape($barcode) . "'
            ");

            // Update sync status
            $this->db->query("
                UPDATE " . DB_PREFIX . "trendyol_products
                SET last_stock_sync = NOW(), updated_at = NOW()
                WHERE barcode = '" . $this->db->escape($barcode) . "'
            ");

            return ['success' => true, 'message' => 'Inventory updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Price Updated Event
     */
    private function processPriceUpdatedEvent($eventData)
    {
        try {
            $barcode = $eventData['barcode'] ?? '';
            $listPrice = (float)($eventData['listPrice'] ?? 0);
            $salePrice = (float)($eventData['salePrice'] ?? 0);

            if (empty($barcode)) {
                return ['success' => false, 'error' => 'Missing barcode'];
            }

            // Update product prices in OpenCart
            $this->db->query("
                UPDATE " . DB_PREFIX . "product p
                INNER JOIN " . DB_PREFIX . "trendyol_products tp ON p.product_id = tp.opencart_product_id
                SET p.price = " . $listPrice . ",
                    p.special = " . ($salePrice != $listPrice ? $salePrice : 0) . "
                WHERE tp.barcode = '" . $this->db->escape($barcode) . "'
            ");

            // Update sync status
            $this->db->query("
                UPDATE " . DB_PREFIX . "trendyol_products
                SET last_price_sync = NOW(), updated_at = NOW()
                WHERE barcode = '" . $this->db->escape($barcode) . "'
            ");

            return ['success' => true, 'message' => 'Prices updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Return Initiated Event
     */
    private function processReturnInitiatedEvent($eventData)
    {
        try {
            $orderNumber = $eventData['orderNumber'] ?? '';
            $returnReason = $eventData['returnReason'] ?? 'Customer return';

            if (empty($orderNumber)) {
                return ['success' => false, 'error' => 'Missing order number'];
            }

            // Update order status to return initiated
            $this->db->query("
                UPDATE " . DB_PREFIX . "trendyol_orders
                SET status = 'Return Initiated', updated_at = NOW()
                WHERE order_number = '" . $this->db->escape($orderNumber) . "'
            ");

            // Update corresponding OpenCart order if exists
            $query = $this->db->query("
                SELECT opencart_order_id FROM " . DB_PREFIX . "trendyol_orders
                WHERE order_number = '" . $this->db->escape($orderNumber) . "'
            ");

            if ($query && $query->num_rows > 0) {
                $row = $query->fetch_assoc();
                if ($row['opencart_order_id']) {
                    $this->addOrderHistory(
                        $row['opencart_order_id'],
                        11, // Return status
                        'Return initiated: ' . $returnReason
                    );
                }
            }

            return ['success' => true, 'message' => 'Return processed successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process failed webhooks (retry mechanism)
     */
    private function processFailedWebhooks()
    {
        $this->writeLog('[WEBHOOK PROCESSOR] Processing failed webhooks for retry');

        try {
            // Get failed webhooks that can be retried
            $query = $this->db->query(
                "
                SELECT * FROM " . DB_PREFIX . "trendyol_webhook_logs
                WHERE processed = 0
                AND retry_count < " . $this->maxRetries . "
                AND retry_count > 0
                AND next_retry_at <= NOW()
                ORDER BY received_at ASC
                LIMIT " . ($this->batchSize / 2)
            );

            if (!$query) {
                return;
            }

            $webhooks = [];
            while ($row = $query->fetch_assoc()) {
                $webhooks[] = $row;
            }

            foreach ($webhooks as $webhook) {
                if ($this->isExecutionTimeExceeded()) {
                    break;
                }

                $this->processWebhookEvent($webhook);
                $this->stats['webhooks_retried']++;
            }

            $this->writeLog('[WEBHOOK PROCESSOR] Failed webhook retry completed. Retried: ' . count($webhooks));
        } catch (Exception $e) {
            $this->writeLog('[WEBHOOK PROCESSOR] Failed webhook retry error: ' . $e->getMessage());
        }
    }

    /**
     * Cleanup old webhooks
     */
    private function cleanupOldWebhooks()
    {
        try {
            // Delete processed webhooks older than 30 days
            $this->db->query("
                DELETE FROM " . DB_PREFIX . "trendyol_webhook_logs
                WHERE processed = 1
                AND processed_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");

            // Delete failed webhooks older than 7 days that exceeded max retries
            $this->db->query("
                DELETE FROM " . DB_PREFIX . "trendyol_webhook_logs
                WHERE processed = 0
                AND retry_count >= " . $this->maxRetries . "
                AND received_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");

            $this->writeLog('[WEBHOOK PROCESSOR] Old webhooks cleanup completed');
        } catch (Exception $e) {
            $this->writeLog('[WEBHOOK PROCESSOR] Webhook cleanup error: ' . $e->getMessage());
        }
    }

    /**
     * Update webhook statistics
     */
    private function updateWebhookStats()
    {
        try {
            // Update daily webhook statistics
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "trendyol_webhook_stats SET
                date = CURDATE(),
                total_received = (SELECT COUNT(*) FROM " . DB_PREFIX . "trendyol_webhook_logs WHERE DATE(received_at) = CURDATE()),
                total_processed = (SELECT COUNT(*) FROM " . DB_PREFIX . "trendyol_webhook_logs WHERE DATE(processed_at) = CURDATE() AND processed = 1),
                total_failed = (SELECT COUNT(*) FROM " . DB_PREFIX . "trendyol_webhook_logs WHERE DATE(received_at) = CURDATE() AND processed = 0 AND retry_count >= " . $this->maxRetries . "),
                created_at = NOW()
                ON DUPLICATE KEY UPDATE
                total_received = VALUES(total_received),
                total_processed = VALUES(total_processed),
                total_failed = VALUES(total_failed),
                updated_at = NOW()
            ");
        } catch (Exception $e) {
            $this->writeLog('[WEBHOOK PROCESSOR] Webhook stats update error: ' . $e->getMessage());
        }
    }

    /**
     * Helper methods
     */
    private function markWebhookAsProcessing($logId)
    {
        $this->db->query(
            "
            UPDATE " . DB_PREFIX . "trendyol_webhook_logs
            SET processing = 1, processing_started_at = NOW()
            WHERE log_id = " . (int)$logId
        );
    }

    private function markWebhookAsProcessed($logId, $success, $errorMessage = null)
    {
        if ($success) {
            $this->db->query(
                "
                UPDATE " . DB_PREFIX . "trendyol_webhook_logs
                SET processed = 1,
                    processing = 0,
                    processed_at = NOW(),
                    error_message = NULL
                WHERE log_id = " . (int)$logId
            );
        } else {
            // Increment retry count and set next retry time
            $nextRetryDelay = pow(2, min(5, $this->getRetryCount($logId) + 1)) * 60; // Exponential backoff in minutes

            $this->db->query(
                "
                UPDATE " . DB_PREFIX . "trendyol_webhook_logs
                SET retry_count = retry_count + 1,
                    processing = 0,
                    error_message = '" . $this->db->escape($errorMessage) . "',
                    next_retry_at = DATE_ADD(NOW(), INTERVAL " . $nextRetryDelay . " MINUTE)
                WHERE log_id = " . (int)$logId
            );
        }
    }

    private function getRetryCount($logId)
    {
        $query = $this->db->query(
            "
            SELECT retry_count FROM " . DB_PREFIX . "trendyol_webhook_logs
            WHERE log_id = " . (int)$logId
        );

        if ($query && $query->num_rows > 0) {
            $row = $query->fetch_assoc();
            return (int)$row['retry_count'];
        }

        return 0;
    }

    private function convertToOpenCartOrder($eventData)
    {
        try {
            // This would contain the logic to convert Trendyol order to OpenCart order
            // Implementation depends on specific business requirements
            $this->writeLog('[WEBHOOK PROCESSOR] Auto-converting order to OpenCart: ' . ($eventData['orderNumber'] ?? 'Unknown'));

            // Basic implementation - would need to be expanded based on requirements
            return true;
        } catch (Exception $e) {
            $this->writeLog('[WEBHOOK PROCESSOR] Order conversion error: ' . $e->getMessage());
            return false;
        }
    }

    private function updateOpenCartOrderStatus($orderid, $status)
    {
        try {
            // Map Trendyol status to OpenCart status
            $statusMap = [
                'Created' => 1,
                'Approved' => 2,
                'Shipped' => 3,
                'Delivered' => 5,
                'Cancelled' => 7,
                'Return Initiated' => 11
            ];

            $opencartStatus = $statusMap[$status] ?? 1;

            $this->db->query(
                "
                UPDATE " . DB_PREFIX . "order
                SET order_status_id = " . $opencartStatus . ", date_modified = NOW()
                WHERE order_id = " . (int)$orderid
            );

            return true;
        } catch (Exception $e) {
            $this->writeLog('[WEBHOOK PROCESSOR] OpenCart order status update error: ' . $e->getMessage());
            return false;
        }
    }

    private function addOrderHistory($orderId, $statusId, $comment)
    {
        try {
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "order_history SET
                order_id = " . (int)$orderId . ",
                order_status_id = " . (int)$statusId . ",
                notify = 0,
                comment = '" . $this->db->escape($comment) . "',
                date_added = NOW()
            ");

            return true;
        } catch (Exception $e) {
            $this->writeLog('[WEBHOOK PROCESSOR] Order history add error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if processing is enabled
     */
    private function isProcessingEnabled()
    {
        return !empty($this->config['meschain_trendyol_webhook_processing_enabled']);
    }

    /**
     * Check if another instance is running
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

        // Fallback: check file age
        return (time() - filemtime($this->lockFile)) < $this->maxExecutionTime;
    }

    /**
     * Create lock file
     */
    private function createLock()
    {
        file_put_contents($this->lockFile, getmypid());
    }

    /**
     * Remove lock file
     */
    private function removeLock()
    {
        if (file_exists($this->lockFile)) {
            unlink($this->lockFile);
        }
    }

    /**
     * Check if execution time limit is exceeded
     */
    private function isExecutionTimeExceeded()
    {
        return (microtime(true) - $this->startTime) > ($this->maxExecutionTime - 30);
    }

    /**
     * Write to log file
     */
    private function writeLog($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;

        if ($this->log->file) {
            fwrite($this->log->file, $logMessage);
        }

        // Also output to console for CLI
        echo $logMessage;
    }

    /**
     * Log final statistics
     */
    private function logStats()
    {
        $this->writeLog('[WEBHOOK PROCESSOR] === PROCESSING STATISTICS ===');
        $this->writeLog('[WEBHOOK PROCESSOR] Webhooks Processed: ' . $this->stats['webhooks_processed']);
        $this->writeLog('[WEBHOOK PROCESSOR] Webhooks Success: ' . $this->stats['webhooks_success']);
        $this->writeLog('[WEBHOOK PROCESSOR] Webhooks Failed: ' . $this->stats['webhooks_failed']);
        $this->writeLog('[WEBHOOK PROCESSOR] Webhooks Retried: ' . $this->stats['webhooks_retried']);
        $this->writeLog('[WEBHOOK PROCESSOR] Orders Processed: ' . $this->stats['orders_processed']);
        $this->writeLog('[WEBHOOK PROCESSOR] Products Processed: ' . $this->stats['products_processed']);
        $this->writeLog('[WEBHOOK PROCESSOR] Execution Time: ' . $this->stats['execution_time'] . 's');
        $this->writeLog('[WEBHOOK PROCESSOR] Memory Usage: ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB');
        $this->writeLog('[WEBHOOK PROCESSOR] ================================');
    }

    /**
     * Send error alert
     */
    private function sendErrorAlert($message)
    {
        try {
            // Send email alert if configured
            if (!empty($this->config['meschain_trendyol_alert_email'])) {
                $subject = 'Trendyol Webhook Processor Error';
                $body = "Webhook processor encountered an error:\n\n" . $message . "\n\nTime: " . date('Y-m-d H:i:s');

                mail($this->config['meschain_trendyol_alert_email'], $subject, $body);
            }

            // Log to database
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "trendyol_sync_logs SET
                sync_type = 'webhook_processor',
                status = 'error',
                message = '" . $this->db->escape($message) . "',
                created_at = NOW()
            ");
        } catch (Exception $e) {
            $this->writeLog('[WEBHOOK PROCESSOR] Alert sending failed: ' . $e->getMessage());
        }
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $processor = new TrendyolWebhookProcessor();
    $processor->run();
}
