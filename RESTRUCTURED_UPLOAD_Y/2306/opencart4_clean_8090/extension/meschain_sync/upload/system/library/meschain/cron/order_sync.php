<?php

/**
 * MesChain Trendyol Order Synchronization Cron Job
 * Day 5-6: Order-specific synchronization with real-time processing
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
 * Trendyol Order Sync Manager
 */
class TrendyolOrderSync
{
    private $db;
    private $config;
    private $log;
    private $startTime;
    private $stats;
    private $lockFile;
    private $maxExecutionTime = 900; // 15 minutes
    private $batchSize = 50;

    // Rate limiting
    private $apiCallCount = 0;
    private $lastApiCall = 0;
    private $rateLimitPerMinute = 600;

    public function __construct()
    {
        global $db;
        $this->db = $db;
        $this->startTime = microtime(true);
        $this->lockFile = sys_get_temp_dir() . '/trendyol_order_sync.lock';

        // Initialize log
        $this->log = new stdClass();
        $this->log->file = fopen(DIR_LOGS . 'trendyol_order_sync.log', 'a');

        $this->stats = [
            'orders_processed' => 0,
            'orders_imported' => 0,
            'orders_updated' => 0,
            'orders_converted' => 0,
            'orders_failed' => 0,
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
    }

    /**
     * Main sync execution
     */
    public function run()
    {
        try {
            $this->writeLog('[ORDER SYNC] Starting order synchronization process');

            // Check if already running
            if ($this->isRunning()) {
                $this->writeLog('[ORDER SYNC] Another sync process is already running');
                return false;
            }

            // Create lock file
            $this->createLock();

            // Check if sync is enabled
            if (!$this->isSyncEnabled()) {
                $this->writeLog('[ORDER SYNC] Order synchronization is disabled');
                $this->removeLock();
                return false;
            }

            // Validate API credentials
            if (!$this->validateApiCredentials()) {
                $this->writeLog('[ORDER SYNC] API credentials validation failed');
                $this->removeLock();
                return false;
            }

            // Run synchronization tasks
            $this->syncNewOrders();
            $this->syncOrderUpdates();
            $this->processOrderConversions();
            $this->updateOrderStatuses();
            $this->syncShipmentInfo();
            $this->processReturns();
            $this->cleanupOldOrders();

            // Calculate execution time
            $this->stats['execution_time'] = round(microtime(true) - $this->startTime, 2);

            // Log final statistics
            $this->logStats();

            $this->writeLog('[ORDER SYNC] Order synchronization completed successfully');
        } catch (Exception $e) {
            $this->writeLog('[ORDER SYNC] Fatal error: ' . $e->getMessage());
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
     * Sync new orders from Trendyol
     */
    private function syncNewOrders()
    {
        $this->writeLog('[ORDER SYNC] Starting new order synchronization');

        try {
            // Load Trendyol API client
            require_once(DIR_SYSTEM . 'library/meschain/api/trendyol_client.php');
            $trendyolClient = new \MesChain\Api\TrendyolClient((object)$this->config, $this);

            // Get orders from last 24 hours
            $filters = [
                'startDate' => date('Y-m-d', strtotime('-1 day')),
                'endDate' => date('Y-m-d'),
                'page' => 0,
                'size' => $this->batchSize,
                'orderByField' => 'PackageLastModifiedDate',
                'orderByDirection' => 'DESC'
            ];

            $response = $trendyolClient->getOrders($filters);
            $this->stats['api_calls']++;

            if ($response['success']) {
                $orders = $response['data']['content'] ?? [];

                foreach ($orders as $order) {
                    if ($this->isExecutionTimeExceeded()) {
                        $this->writeLog('[ORDER SYNC] Execution time limit reached, stopping new order sync');
                        break;
                    }

                    $this->processNewOrder($order);
                    $this->stats['orders_processed']++;

                    // Rate limiting
                    $this->enforceRateLimit();
                }

                $this->writeLog('[ORDER SYNC] New order synchronization completed. Processed: ' . count($orders));
            } else {
                $this->writeLog('[ORDER SYNC] Failed to fetch new orders: ' . $response['error']);
                $this->stats['orders_failed']++;
            }
        } catch (Exception $e) {
            $this->writeLog('[ORDER SYNC] New order sync error: ' . $e->getMessage());
            $this->stats['orders_failed']++;
        }
    }

    /**
     * Process single new order
     */
    private function processNewOrder($order)
    {
        try {
            // Check if order already exists
            $query = $this->db->query("
                SELECT order_id FROM " . DB_PREFIX . "trendyol_orders
                WHERE order_number = '" . $this->db->escape($order['orderNumber']) . "'
            ");

            if ($query && $query->num_rows > 0) {
                // Order exists, update it
                $this->updateTrendyolOrder($order);
                $this->stats['orders_updated']++;
                $this->writeLog('[ORDER SYNC] Order updated: ' . $order['orderNumber']);
            } else {
                // New order, insert it
                $this->insertTrendyolOrder($order);
                $this->stats['orders_imported']++;
                $this->writeLog('[ORDER SYNC] New order imported: ' . $order['orderNumber']);

                // Auto-convert to OpenCart order if enabled
                if (!empty($this->config['meschain_trendyol_auto_convert_orders'])) {
                    $this->convertToOpenCartOrder($order);
                }
            }
        } catch (Exception $e) {
            $this->writeLog('[ORDER SYNC] Process new order error: ' . $e->getMessage());
            $this->stats['orders_failed']++;
        }
    }

    /**
     * Sync order updates
     */
    private function syncOrderUpdates()
    {
        $this->writeLog('[ORDER SYNC] Starting order updates synchronization');

        try {
            // Get orders that need status updates
            $query = $this->db->query(
                "
                SELECT * FROM " . DB_PREFIX . "trendyol_orders
                WHERE status NOT IN ('Delivered', 'Cancelled', 'Returned')
                AND updated_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY order_date DESC
                LIMIT " . $this->batchSize
            );

            if (!$query) {
                return;
            }

            $orders = [];
            while ($row = $query->fetch_assoc()) {
                $orders[] = $row;
            }

            // Load Trendyol API client
            require_once(DIR_SYSTEM . 'library/meschain/api/trendyol_client.php');
            $trendyolClient = new \MesChain\Api\TrendyolClient((object)$this->config, $this);

            foreach ($orders as $order) {
                if ($this->isExecutionTimeExceeded()) {
                    break;
                }

                // Get updated order details
                $response = $trendyolClient->makeApiRequest(
                    'suppliers/' . $this->config['meschain_trendyol_supplier_id'] . '/orders/' . $order['order_number']
                );

                $this->stats['api_calls']++;

                if ($response['success']) {
                    $updatedOrder = $response['data'];
                    $this->updateTrendyolOrder($updatedOrder);

                    // Update corresponding OpenCart order if exists
                    if ($order['opencart_order_id']) {
                        $this->updateOpenCartOrderStatus($order['opencart_order_id'], $updatedOrder['status']);
                    }

                    $this->stats['orders_updated']++;
                }

                // Rate limiting
                $this->enforceRateLimit();
            }

            $this->writeLog('[ORDER SYNC] Order updates synchronization completed. Updated: ' . $this->stats['orders_updated']);
        } catch (Exception $e) {
            $this->writeLog('[ORDER SYNC] Order updates sync error: ' . $e->getMessage());
        }
    }

    /**
     * Process order conversions to OpenCart
     */
    private function processOrderConversions()
    {
        $this->writeLog('[ORDER SYNC] Starting order conversions');

        try {
            // Get Trendyol orders that haven't been converted to OpenCart
            $query = $this->db->query(
                "
                SELECT * FROM " . DB_PREFIX . "trendyol_orders
                WHERE opencart_order_id IS NULL
                AND status NOT IN ('Cancelled')
                ORDER BY order_date DESC
                LIMIT " . ($this->batchSize / 2)
            );

            if (!$query) {
                return;
            }

            $orders = [];
            while ($row = $query->fetch_assoc()) {
                $orders[] = $row;
            }

            foreach ($orders as $order) {
                if ($this->isExecutionTimeExceeded()) {
                    break;
                }

                $orderData = json_decode($order['order_data'], true);
                if ($orderData) {
                    $ocOrderId = $this->convertToOpenCartOrder($orderData);
                    if ($ocOrderId) {
                        // Link the orders
                        $this->db->query(
                            "
                            UPDATE " . DB_PREFIX . "trendyol_orders
                            SET opencart_order_id = " . (int)$ocOrderId . ", updated_at = NOW()
                            WHERE order_id = " . (int)$order['order_id']
                        );

                        $this->stats['orders_converted']++;
                        $this->writeLog('[ORDER SYNC] Order converted: ' . $order['order_number'] . ' -> OC Order ID: ' . $ocOrderId);
                    }
                }
            }

            $this->writeLog('[ORDER SYNC] Order conversions completed. Converted: ' . $this->stats['orders_converted']);
        } catch (Exception $e) {
            $this->writeLog('[ORDER SYNC] Order conversions error: ' . $e->getMessage());
        }
    }

    /**
     * Update order statuses
     */
    private function updateOrderStatuses()
    {
        $this->writeLog('[ORDER SYNC] Starting order status updates');

        try {
            // Get orders with linked OpenCart orders
            $query = $this->db->query(
                "
                SELECT to.*, o.order_status_id
                FROM " . DB_PREFIX . "trendyol_orders to
                INNER JOIN " . DB_PREFIX . "`order` o ON to.opencart_order_id = o.order_id
                WHERE to.status != 'Delivered'
                AND to.updated_at > DATE_SUB(NOW(), INTERVAL 2 HOUR)
                LIMIT " . $this->batchSize
            );

            if (!$query) {
                return;
            }

            $orders = [];
            while ($row = $query->fetch_assoc()) {
                $orders[] = $row;
            }

            foreach ($orders as $order) {
                $this->syncOrderStatusBidirectional($order);
            }

            $this->writeLog('[ORDER SYNC] Order status updates completed. Processed: ' . count($orders));
        } catch (Exception $e) {
            $this->writeLog('[ORDER SYNC] Order status updates error: ' . $e->getMessage());
        }
    }

    /**
     * Sync shipment information
     */
    private function syncShipmentInfo()
    {
        $this->writeLog('[ORDER SYNC] Starting shipment info synchronization');

        try {
            // Get orders that are shipped but don't have tracking info
            $query = $this->db->query(
                "
                SELECT * FROM " . DB_PREFIX . "trendyol_orders
                WHERE status IN ('Shipped', 'InTransit')
                AND (tracking_number IS NULL OR tracking_number = '')
                AND updated_at > DATE_SUB(NOW(), INTERVAL 6 HOUR)
                LIMIT " . ($this->batchSize / 2)
            );

            if (!$query) {
                return;
            }

            $orders = [];
            while ($row = $query->fetch_assoc()) {
                $orders[] = $row;
            }

            // Load Trendyol API client
            require_once(DIR_SYSTEM . 'library/meschain/api/trendyol_client.php');
            $trendyolClient = new \MesChain\Api\TrendyolClient((object)$this->config, $this);

            foreach ($orders as $order) {
                if ($this->isExecutionTimeExceeded()) {
                    break;
                }

                // Get shipment details
                $response = $trendyolClient->makeApiRequest(
                    'suppliers/' . $this->config['meschain_trendyol_supplier_id'] . '/orders/' . $order['order_number'] . '/shipment-packages'
                );

                $this->stats['api_calls']++;

                if ($response['success'] && !empty($response['data'])) {
                    $shipmentData = $response['data'][0] ?? [];

                    if (!empty($shipmentData['trackingNumber'])) {
                        // Update tracking information
                        $this->db->query(
                            "
                            UPDATE " . DB_PREFIX . "trendyol_orders
                            SET tracking_number = '" . $this->db->escape($shipmentData['trackingNumber']) . "',
                                cargo_provider = '" . $this->db->escape($shipmentData['cargoProviderName'] ?? '') . "',
                                updated_at = NOW()
                            WHERE order_id = " . (int)$order['order_id']
                        );

                        // Update OpenCart order if linked
                        if ($order['opencart_order_id']) {
                            $this->addOrderHistory(
                                $order['opencart_order_id'],
                                3, // Shipped status
                                'Tracking Number: ' . $shipmentData['trackingNumber'] . ' (' . ($shipmentData['cargoProviderName'] ?? '') . ')'
                            );
                        }

                        $this->writeLog('[ORDER SYNC] Shipment info updated for order: ' . $order['order_number']);
                    }
                }

                // Rate limiting
                $this->enforceRateLimit();
            }

            $this->writeLog('[ORDER SYNC] Shipment info synchronization completed');
        } catch (Exception $e) {
            $this->writeLog('[ORDER SYNC] Shipment info sync error: ' . $e->getMessage());
        }
    }

    /**
     * Process returns
     */
    private function processReturns()
    {
        $this->writeLog('[ORDER SYNC] Starting returns processing');

        try {
            // Load Trendyol API client
            require_once(DIR_SYSTEM . 'library/meschain/api/trendyol_client.php');
            $trendyolClient = new \MesChain\Api\TrendyolClient((object)$this->config, $this);

            // Get returns from last 7 days
            $response = $trendyolClient->makeApiRequest(
                'suppliers/' . $this->config['meschain_trendyol_supplier_id'] . '/claims?startDate=' .
                    date('Y-m-d', strtotime('-7 days')) . '&endDate=' . date('Y-m-d')
            );

            $this->stats['api_calls']++;

            if ($response['success']) {
                $returns = $response['data']['content'] ?? [];

                foreach ($returns as $return) {
                    $this->processReturn($return);
                }

                $this->writeLog('[ORDER SYNC] Returns processing completed. Processed: ' . count($returns));
            }
        } catch (Exception $e) {
            $this->writeLog('[ORDER SYNC] Returns processing error: ' . $e->getMessage());
        }
    }

    /**
     * Process single return
     */
    private function processReturn($return)
    {
        try {
            $orderNumber = $return['orderNumber'] ?? '';

            if ($orderNumber) {
                // Update order status to returned
                $this->db->query("
                    UPDATE " . DB_PREFIX . "trendyol_orders
                    SET status = 'Returned', updated_at = NOW()
                    WHERE order_number = '" . $this->db->escape($orderNumber) . "'
                ");

                // Update OpenCart order if linked
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
                            'Return initiated: ' . ($return['claimReason'] ?? 'Customer return')
                        );
                    }
                }

                $this->writeLog('[ORDER SYNC] Return processed for order: ' . $orderNumber);
            }
        } catch (Exception $e) {
            $this->writeLog('[ORDER SYNC] Process return error: ' . $e->getMessage());
        }
    }

    /**
     * Cleanup old orders
     */
    private function cleanupOldOrders()
    {
        try {
            // Archive orders older than 90 days
            $this->db->query("
                UPDATE " . DB_PREFIX . "trendyol_orders
                SET archived = 1
                WHERE order_date < DATE_SUB(NOW(), INTERVAL 90 DAY)
                AND archived = 0
            ");

            $this->writeLog('[ORDER SYNC] Old orders cleanup completed');
        } catch (Exception $e) {
            $this->writeLog('[ORDER SYNC] Old orders cleanup error: ' . $e->getMessage());
        }
    }

    /**
     * Helper methods
     */
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

    private function convertToOpenCartOrder($orderData)
    {
        try {
            // Create OpenCart order data structure
            $ocOrderData = [
                'invoice_prefix' => 'TY-',
                'store_id' => 0,
                'store_name' => 'Trendyol Store',
                'store_url' => '',
                'customer_id' => 0,
                'customer_group_id' => 1,
                'firstname' => explode(' ', $orderData['customerFirstName'] ?? 'Trendyol')[0] ?? 'Trendyol',
                'lastname' => explode(' ', $orderData['customerLastName'] ?? 'Customer')[0] ?? 'Customer',
                'email' => $orderData['customerEmail'] ?: 'noreply@trendyol.com',
                'telephone' => $orderData['customerTckn'] ?? '',
                'payment_firstname' => explode(' ', $orderData['customerFirstName'] ?? 'Trendyol')[0] ?? 'Trendyol',
                'payment_lastname' => explode(' ', $orderData['customerLastName'] ?? 'Customer')[0] ?? 'Customer',
                'payment_company' => '',
                'payment_address_1' => 'Trendyol Order',
                'payment_address_2' => '',
                'payment_city' => 'Istanbul',
                'payment_postcode' => '34000',
                'payment_zone' => 'Istanbul',
                'payment_zone_id' => 0,
                'payment_country' => 'Turkey',
                'payment_country_id' => 215,
                'payment_method' => 'Trendyol Payment',
                'payment_code' => 'trendyol',
                'shipping_firstname' => explode(' ', $orderData['customerFirstName'] ?? 'Trendyol')[0] ?? 'Trendyol',
                'shipping_lastname' => explode(' ', $orderData['customerLastName'] ?? 'Customer')[0] ?? 'Customer',
                'shipping_company' => '',
                'shipping_address_1' => 'Trendyol Order',
                'shipping_address_2' => '',
                'shipping_city' => 'Istanbul',
                'shipping_postcode' => '34000',
                'shipping_zone' => 'Istanbul',
                'shipping_zone_id' => 0,
                'shipping_country' => 'Turkey',
                'shipping_country_id' => 215,
                'shipping_method' => 'Trendyol Shipping',
                'shipping_code' => 'trendyol',
                'comment' => 'Order imported from Trendyol - Order #' . $orderData['orderNumber'],
                'total' => $orderData['grossAmount'] ?? 0,
                'order_status_id' => $this->mapTrendyolStatusToOpenCart($orderData['status'] ?? 'Created'),
                'language_id' => 1,
                'currency_id' => 1,
                'currency_code' => 'TRY',
                'currency_value' => 1.0,
                'date_added' => date('Y-m-d H:i:s', ($orderData['orderDate'] ?? time()) / 1000),
                'date_modified' => date('Y-m-d H:i:s')
            ];

            // Insert order
            $fields = implode(', ', array_keys($ocOrderData));
            $values = "'" . implode("', '", array_map([$this->db, 'escape'], array_values($ocOrderData))) . "'";

            $this->db->query("INSERT INTO " . DB_PREFIX . "`order` ({$fields}) VALUES ({$values})");
            $ocOrderId = $this->db->insert_id;

            // Add order products
            foreach ($orderData['lines'] ?? [] as $line) {
                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "order_product SET
                    order_id = " . (int)$ocOrderId . ",
                    product_id = 0,
                    name = '" . $this->db->escape($line['productName'] ?? 'Trendyol Product') . "',
                    model = '" . $this->db->escape($line['barcode'] ?? '') . "',
                    quantity = " . (int)($line['quantity'] ?? 1) . ",
                    price = " . (float)($line['price'] ?? 0) . ",
                    total = " . (float)($line['totalPrice'] ?? 0) . ",
                    tax = 0,
                    reward = 0
                ");
            }

            // Add order totals
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "order_total SET
                order_id = " . (int)$ocOrderId . ",
                code = 'sub_total',
                title = 'Sub-Total',
                value = " . (float)(($orderData['grossAmount'] ?? 0) - ($orderData['totalDiscount'] ?? 0)) . ",
                sort_order = 1
            ");

            $this->db->query("
                INSERT INTO " . DB_PREFIX . "order_total SET
                order_id = " . (int)$ocOrderId . ",
                code = 'total',
                title = 'Total',
                value = " . (float)($orderData['grossAmount'] ?? 0) . ",
                sort_order = 9
            ");

            return $ocOrderId;
        } catch (Exception $e) {
            $this->writeLog('[ORDER SYNC] Order conversion error: ' . $e->getMessage());
            return null;
        }
    }

    private function mapTrendyolStatusToOpenCart($trendyolStatus)
    {
        $statusMap = [
            'Created' => 1,        // Pending
            'Picking' => 2,        // Processing
            'Invoiced' => 3,       // Shipped
            'Shipped' => 3,        // Shipped
            'Delivered' => 5,      // Complete
            'Cancelled' => 7,      // Cancelled
            'Returned' => 11       // Returned
        ];

        return $statusMap[$trendyolStatus] ?? 1;
    }

    private function updateOpenCartOrderStatus($ocOrderId, $trendyolStatus)
    {
        $statusId = $this->mapTrendyolStatusToOpenCart($trendyolStatus);

        $this->addOrderHistory($ocOrderId, $statusId, 'Status updated from Trendyol: ' . $trendyolStatus);
    }

    private function addOrderHistory($orderId, $statusId, $comment)
    {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "order_history SET
            order_id = " . (int)$orderId . ",
            order_status_id = " . (int)$statusId . ",
            notify = 0,
            comment = '" . $this->db->escape($comment) . "',
            date_added = NOW()
        ");

        // Update order status
        $this->db->query(
            "
            UPDATE " . DB_PREFIX . "`order`
            SET order_status_id = " . (int)$statusId . ", date_modified = NOW()
            WHERE order_id = " . (int)$orderId
        );
    }

    private function syncOrderStatusBidirectional($order)
    {
        // This method would handle bidirectional status sync between Trendyol and OpenCart
        // Implementation depends on business requirements
    }

    private function isRunning()
    {
        if (!file_exists($this->lockFile)) {
            return false;
        }

        return (time() - filemtime($this->lockFile)) < 900; // 15 minutes
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
            !empty($this->config['meschain_trendyol_order_sync']);
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
            '[ORDER SYNC] Statistics - Processed: %d, Imported: %d, Updated: %d, Converted: %d, Failed: %d, API Calls: %d, Time: %s seconds',
            $this->stats['orders_processed'],
            $this->stats['orders_imported'],
            $this->stats['orders_updated'],
            $this->stats['orders_converted'],
            $this->stats['orders_failed'],
            $this->stats['api_calls'],
            $this->stats['execution_time']
        );

        $this->writeLog($statsMessage);

        // Save stats to database
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "trendyol_sync_stats SET
            sync_type = 'order',
            orders_processed = " . (int)$this->stats['orders_processed'] . ",
            orders_imported = " . (int)$this->stats['orders_imported'] . ",
            orders_updated = " . (int)$this->stats['orders_updated'] . ",
            orders_converted = " . (int)$this->stats['orders_converted'] . ",
            orders_failed = " . (int)$this->stats['orders_failed'] . ",
            api_calls = " . (int)$this->stats['api_calls'] . ",
            execution_time = " . (float)$this->stats['execution_time'] . ",
            created_at = NOW()
        ");
    }

    private function sendErrorAlert($message)
    {
        $this->writeLog('[ORDER SYNC] ERROR ALERT: ' . $message);

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
    $orderSync = new TrendyolOrderSync();
    $orderSync->run();
    echo "Trendyol order synchronization completed successfully\n";
} catch (Exception $e) {
    echo "Trendyol order synchronization failed: " . $e->getMessage() . "\n";
    exit(1);
}
