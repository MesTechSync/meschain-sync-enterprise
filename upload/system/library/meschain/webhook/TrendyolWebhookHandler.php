<?php
/**
 * Trendyol Webhook Handler - Advanced Integration
 * MesChain-Sync Enterprise v4.5.0
 *
 * @author MesChain Development Team
 * @version 4.5.0 Enterprise
 * @copyright 2024 MesChain Technologies
 */

use MesChain\Api\TrendyolApiClient;

class TrendyolWebhookHandler {
    private $apiClient;
    private $registry;
    private $db;
    private $log;
    private $config;

    // Supported webhook events
    private $supportedEvents = [
        'ORDER_CREATED',
        'ORDER_CANCELLED',
        'ORDER_STATUS_CHANGED',
        'PRODUCT_APPROVED',
        'PRODUCT_REJECTED',
        'INVENTORY_UPDATED',
        'PRICE_UPDATED',
        'SHIPMENT_CREATED',
        'RETURN_INITIATED'
    ];

    public function __construct(TrendyolApiClient $apiClient, $registry) {
        $this->apiClient = $apiClient;
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->log = $registry->get('log');
        $this->config = $registry->get('config');

        // Create webhook logs table if not exists
        $this->createWebhookTables();
    }

        /**
     * Validates an incoming webhook request from Trendyol.
     * @param object $request The OpenCart request object.
     * @return bool True if the signature is valid, false otherwise.
     */
    public function validate($request) {
        $signature = $request->server['HTTP_X_TRENDYOL_SIGNATURE'] ?? '';

        if (empty($signature)) {
            $this->log->write('Trendyol Webhook: Missing signature header');
            return false;
        }

        $payload = file_get_contents('php://input');

        // Use API client's validation method
        $isValid = $this->apiClient->validateWebhookSignature($payload, $signature);

        if (!$isValid) {
            $this->log->write('Trendyol Webhook: Invalid signature');
        }

        return $isValid;
    }

    /**
     * Processes a validated webhook payload from Trendyol.
     * @param array $payload The decoded JSON payload from the webhook.
     * @return array A result array with 'success' and 'message' keys.
     */
    public function process($payload) {
        $eventType = $payload['eventType'] ?? $payload['event_type'] ?? '';
        $orderNumber = $payload['orderNumber'] ?? $payload['order_number'] ?? 'N/A';

        // Log the webhook event
        $logId = $this->logWebhookEvent($eventType, $payload);

        try {
            // Check if event type is supported
            if (!in_array($eventType, $this->supportedEvents)) {
                throw new Exception("Unsupported event type: {$eventType}");
            }

            // Check if event processing is enabled
            if (!$this->isEventEnabled($eventType)) {
                return ['success' => true, 'message' => "Event {$eventType} is disabled", 'action' => 'skipped'];
            }

            $result = ['success' => false, 'message' => 'Unknown error'];

            // Process based on event type
            switch ($eventType) {
                case 'ORDER_CREATED':
                case 'NewOrder':
                case 'OrderCreated':
                    $result = $this->processOrderCreated($payload);
                    break;

                case 'ORDER_STATUS_CHANGED':
                case 'OrderStatusChanged':
                    $result = $this->processOrderStatusChanged($payload);
                    break;

                case 'ORDER_CANCELLED':
                case 'OrderCancelled':
                    $result = $this->processOrderCancelled($payload);
                    break;

                case 'PRODUCT_APPROVED':
                case 'ProductApproved':
                    $result = $this->processProductApproved($payload);
                    break;

                case 'PRODUCT_REJECTED':
                case 'ProductRejected':
                    $result = $this->processProductRejected($payload);
                    break;

                case 'INVENTORY_UPDATED':
                case 'InventoryUpdated':
                    $result = $this->processInventoryUpdated($payload);
                    break;

                case 'PRICE_UPDATED':
                case 'PriceUpdated':
                    $result = $this->processPriceUpdated($payload);
                    break;

                case 'SHIPMENT_CREATED':
                case 'ShipmentCreated':
                    $result = $this->processShipmentCreated($payload);
                    break;

                case 'RETURN_INITIATED':
                case 'ReturnInitiated':
                    $result = $this->processReturnInitiated($payload);
                    break;

                default:
                    $result = ['success' => false, 'message' => "Unsupported event type: {$eventType}"];
            }

            // Update webhook log with result
            $this->updateWebhookLog($logId, $result['success'], $result['message'] ?? null);

            return $result;

        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $this->log->write('Trendyol Webhook Processing Error: ' . $errorMessage);
            $this->updateWebhookLog($logId, false, $errorMessage);

            return ['success' => false, 'error' => $errorMessage];
        }
    }

    /**
     * Check if event processing is enabled
     */
    private function isEventEnabled($eventType) {
        $query = $this->db->query("SELECT enabled FROM `" . DB_PREFIX . "trendyol_webhook_config` WHERE event_type = '" . $this->db->escape($eventType) . "'");

        if ($query->num_rows) {
            return (bool)$query->row['enabled'];
        }

        // Default to enabled if no configuration found
        return true;
    }

    /**
     * Update webhook log with processing result
     */
    private function updateWebhookLog($logId, $success, $message = null) {
        $this->db->query("UPDATE `" . DB_PREFIX . "trendyol_webhook_logs` SET
            processed = '" . (int)$success . "',
            processed_at = NOW(),
            error_message = '" . $this->db->escape($message) . "'
            WHERE log_id = '" . (int)$logId . "'");
    }

    /**
     * Process Order Cancelled Event
     */
    private function processOrderCancelled($payload) {
        try {
            $this->registry->get('load')->model('extension/module/trendyol');
            $model = $this->registry->get('model_extension_module_trendyol');

            $orderNumber = $payload['orderNumber'];

            // Update Trendyol order status to cancelled
            $updated = $model->updateTrendyolOrderStatus($orderNumber, 'Cancelled');

            if ($updated) {
                // Update corresponding OpenCart order if exists
                $openCartOrder = $model->getOpenCartOrderByTrendyolOrder($orderNumber);
                if ($openCartOrder) {
                    $this->updateOpenCartOrderStatus($openCartOrder['order_id'], 'Cancelled');
                }

                return ['success' => true, 'message' => 'Order cancelled successfully'];
            }

            return ['success' => false, 'error' => 'Order not found'];

        } catch (Exception $e) {
            $this->log->write('Trendyol Webhook Error (Order Cancelled): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Product Rejected Event
     */
    private function processProductRejected($payload) {
        try {
            $this->registry->get('load')->model('extension/module/trendyol');
            $model = $this->registry->get('model_extension_module_trendyol');

            $barcode = $payload['barcode'];
            $rejectionReason = $payload['rejectionReason'] ?? 'No reason provided';

            // Update product approval status
            $updated = $model->updateProductApprovalStatus($barcode, false, $rejectionReason);

            if ($updated) {
                return ['success' => true, 'message' => 'Product rejection processed'];
            }

            return ['success' => false, 'error' => 'Product not found'];

        } catch (Exception $e) {
            $this->log->write('Trendyol Webhook Error (Product Rejected): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Inventory Updated Event
     */
    private function processInventoryUpdated($payload) {
        try {
            $this->registry->get('load')->model('extension/module/trendyol');
            $model = $this->registry->get('model_extension_module_trendyol');

            $barcode = $payload['barcode'];
            $quantity = $payload['quantity'] ?? 0;

            // Update product inventory
            $updated = $model->updateProductInventory($barcode, $quantity);

            if ($updated) {
                return ['success' => true, 'message' => 'Inventory updated successfully'];
            }

            return ['success' => false, 'error' => 'Product not found'];

        } catch (Exception $e) {
            $this->log->write('Trendyol Webhook Error (Inventory Updated): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Price Updated Event
     */
    private function processPriceUpdated($payload) {
        try {
            $this->registry->get('load')->model('extension/module/trendyol');
            $model = $this->registry->get('model_extension_module_trendyol');

            $barcode = $payload['barcode'];
            $listPrice = $payload['listPrice'] ?? 0;
            $salePrice = $payload['salePrice'] ?? 0;

            // Update product prices
            $updated = $model->updateProductPrices($barcode, $listPrice, $salePrice);

            if ($updated) {
                return ['success' => true, 'message' => 'Prices updated successfully'];
            }

            return ['success' => false, 'error' => 'Product not found'];

        } catch (Exception $e) {
            $this->log->write('Trendyol Webhook Error (Price Updated): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Shipment Created Event
     */
    private function processShipmentCreated($payload) {
        try {
            $this->registry->get('load')->model('extension/module/trendyol');
            $model = $this->registry->get('model_extension_module_trendyol');

            $orderNumber = $payload['orderNumber'];
            $trackingNumber = $payload['trackingNumber'] ?? '';
            $cargoProvider = $payload['cargoProvider'] ?? '';

            // Update order with shipment info
            $updated = $model->updateOrderShipment($orderNumber, $trackingNumber, $cargoProvider);

            if ($updated) {
                // Update corresponding OpenCart order if exists
                $openCartOrder = $model->getOpenCartOrderByTrendyolOrder($orderNumber);
                if ($openCartOrder) {
                    $this->updateOpenCartOrderStatus($openCartOrder['order_id'], 'Shipped');

                    // Add tracking number to order history
                    $this->registry->get('load')->model('checkout/order');
                    $this->registry->get('model_checkout_order')->addOrderHistory(
                        $openCartOrder['order_id'],
                        3, // Shipped status
                        'Shipment created. Tracking: ' . $trackingNumber . ' (' . $cargoProvider . ')',
                        true
                    );
                }

                return ['success' => true, 'message' => 'Shipment information updated'];
            }

            return ['success' => false, 'error' => 'Order not found'];

        } catch (Exception $e) {
            $this->log->write('Trendyol Webhook Error (Shipment Created): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Return Initiated Event
     */
    private function processReturnInitiated($payload) {
        try {
            $this->registry->get('load')->model('extension/module/trendyol');
            $model = $this->registry->get('model_extension_module_trendyol');

            $orderNumber = $payload['orderNumber'];
            $returnReason = $payload['returnReason'] ?? 'Customer return';

            // Update order status to return initiated
            $updated = $model->updateTrendyolOrderStatus($orderNumber, 'Return Initiated');

            if ($updated) {
                // Update corresponding OpenCart order if exists
                $openCartOrder = $model->getOpenCartOrderByTrendyolOrder($orderNumber);
                if ($openCartOrder) {
                    $this->registry->get('load')->model('checkout/order');
                    $this->registry->get('model_checkout_order')->addOrderHistory(
                        $openCartOrder['order_id'],
                        11, // Return status
                        'Return initiated: ' . $returnReason,
                        true
                    );
                }

                return ['success' => true, 'message' => 'Return processed successfully'];
            }

            return ['success' => false, 'error' => 'Order not found'];

        } catch (Exception $e) {
            $this->log->write('Trendyol Webhook Error (Return Initiated): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create webhook tables
     */
    private function createWebhookTables() {
        // Webhook logs table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_webhook_logs` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `event_type` varchar(100) NOT NULL,
            `event_data` json NOT NULL,
            `signature` varchar(255),
            `processed` tinyint(1) DEFAULT 0,
            `processed_at` datetime DEFAULT NULL,
            `error_message` text,
            `response_sent` text,
            `ip_address` varchar(45),
            `user_agent` varchar(255),
            `received_at` datetime NOT NULL,
            PRIMARY KEY (`log_id`),
            KEY `event_type` (`event_type`),
            KEY `processed` (`processed`),
            KEY `received_at` (`received_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Webhook configurations table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_webhook_config` (
            `config_id` int(11) NOT NULL AUTO_INCREMENT,
            `event_type` varchar(100) NOT NULL,
            `enabled` tinyint(1) DEFAULT 1,
            `auto_process` tinyint(1) DEFAULT 1,
            `retry_count` int(11) DEFAULT 3,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`config_id`),
            UNIQUE KEY `event_type_unique` (`event_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    /**
     * Log webhook event
     */
    private function logWebhookEvent($eventType, $eventData, $signature = '', $processed = false, $error = null) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_webhook_logs` SET
            event_type = '" . $this->db->escape($eventType) . "',
            event_data = '" . $this->db->escape(json_encode($eventData)) . "',
            signature = '" . $this->db->escape($signature) . "',
            processed = '" . (int)$processed . "',
            error_message = '" . $this->db->escape($error) . "',
            ip_address = '" . $this->db->escape($_SERVER['REMOTE_ADDR'] ?? '') . "',
            user_agent = '" . $this->db->escape($_SERVER['HTTP_USER_AGENT'] ?? '') . "',
            received_at = NOW()");

        return $this->db->getLastId();
    }

    /**
     * Process Order Created Event
     */
    private function processOrderCreated($payload) {
        try {
            $this->registry->get('load')->model('extension/module/trendyol');
            $model = $this->registry->get('model_extension_module_trendyol');

            // Check if order already exists
            $existingOrder = $model->getTrendyolOrder($payload['orderNumber']);
            if ($existingOrder) {
                return ['success' => true, 'message' => 'Order already exists', 'action' => 'skipped'];
            }

            // Create order data
            $orderData = [
                'order_number' => $payload['orderNumber'],
                'gross_amount' => $payload['grossAmount'] ?? 0,
                'total_discount' => $payload['totalDiscount'] ?? 0,
                'customer_name' => $payload['customerFirstName'] . ' ' . $payload['customerLastName'],
                'customer_email' => $payload['customerEmail'] ?? '',
                'order_date' => date('Y-m-d H:i:s', $payload['orderDate'] / 1000), // Convert from milliseconds
                'status' => $payload['status'] ?? 'Created',
                'lines' => $payload['lines'] ?? []
            ];

            // Save to database
            $orderId = $model->addTrendyolOrder($orderData);

            // Auto-convert to OpenCart order if enabled
            if ($this->config->get('trendyol_auto_convert_orders')) {
                $this->convertToOpenCartOrder($orderId, $orderData);
            }

            return ['success' => true, 'message' => 'Order created successfully', 'order_id' => $orderId];

        } catch (Exception $e) {
            $this->log->write('Trendyol Webhook Error (Order Created): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Order Status Changed Event
     */
    private function processOrderStatusChanged($payload) {
        try {
            $this->registry->get('load')->model('extension/module/trendyol');
            $model = $this->registry->get('model_extension_module_trendyol');

            $orderNumber = $payload['orderNumber'];
            $newStatus = $payload['status'];

            // Update Trendyol order status
            $updated = $model->updateTrendyolOrderStatus($orderNumber, $newStatus);

            if ($updated) {
                // Update corresponding OpenCart order if exists
                $openCartOrder = $model->getOpenCartOrderByTrendyolOrder($orderNumber);
                if ($openCartOrder) {
                    $this->updateOpenCartOrderStatus($openCartOrder['order_id'], $newStatus);
                }

                return ['success' => true, 'message' => 'Order status updated successfully'];
            }

            return ['success' => false, 'error' => 'Order not found'];

        } catch (Exception $e) {
            $this->log->write('Trendyol Webhook Error (Order Status): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Product Approved Event
     */
    private function processProductApproved($payload) {
        try {
            $this->registry->get('load')->model('extension/module/trendyol');
            $model = $this->registry->get('model_extension_module_trendyol');

            $barcode = $payload['barcode'];
            $approved = $payload['approved'] ?? true;

            // Update product approval status
            $updated = $model->updateProductApprovalStatus($barcode, $approved);

            if ($updated) {
                return ['success' => true, 'message' => 'Product approval status updated'];
            }

            return ['success' => false, 'error' => 'Product not found'];

        } catch (Exception $e) {
            $this->log->write('Trendyol Webhook Error (Product Approved): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Convert Trendyol order to OpenCart order
     */
    private function convertToOpenCartOrder($trendyolOrderId, $orderData) {
        try {
            $this->registry->get('load')->model('checkout/order');
            $this->registry->get('load')->model('extension/module/trendyol');

            // Create OpenCart order data structure
            $ocOrderData = [
                'invoice_prefix' => 'TY-',
                'store_id' => 0,
                'store_name' => $this->config->get('config_name'),
                'store_url' => $this->config->get('config_url'),
                'customer_id' => 0,
                'customer_group_id' => $this->config->get('config_customer_group_id'),
                'firstname' => explode(' ', $orderData['customer_name'])[0] ?? 'Trendyol',
                'lastname' => explode(' ', $orderData['customer_name'], 2)[1] ?? 'Customer',
                'email' => $orderData['customer_email'] ?: 'noreply@trendyol.com',
                'telephone' => '',
                'custom_field' => [],
                'payment_firstname' => explode(' ', $orderData['customer_name'])[0] ?? 'Trendyol',
                'payment_lastname' => explode(' ', $orderData['customer_name'], 2)[1] ?? 'Customer',
                'payment_company' => '',
                'payment_address_1' => 'Trendyol Order',
                'payment_address_2' => '',
                'payment_city' => 'Istanbul',
                'payment_postcode' => '34000',
                'payment_zone' => 'Istanbul',
                'payment_zone_id' => 0,
                'payment_country' => 'Turkey',
                'payment_country_id' => 215,
                'payment_address_format' => '',
                'payment_custom_field' => [],
                'payment_method' => 'Trendyol Payment',
                'payment_code' => 'trendyol',
                'shipping_firstname' => explode(' ', $orderData['customer_name'])[0] ?? 'Trendyol',
                'shipping_lastname' => explode(' ', $orderData['customer_name'], 2)[1] ?? 'Customer',
                'shipping_company' => '',
                'shipping_address_1' => 'Trendyol Order',
                'shipping_address_2' => '',
                'shipping_city' => 'Istanbul',
                'shipping_postcode' => '34000',
                'shipping_zone' => 'Istanbul',
                'shipping_zone_id' => 0,
                'shipping_country' => 'Turkey',
                'shipping_country_id' => 215,
                'shipping_address_format' => '',
                'shipping_custom_field' => [],
                'shipping_method' => 'Trendyol Shipping',
                'shipping_code' => 'trendyol',
                'comment' => 'Order imported from Trendyol - Order #' . $orderData['order_number'],
                'total' => $orderData['gross_amount'],
                'affiliate_id' => 0,
                'commission' => 0,
                'marketing_id' => 0,
                'tracking' => '',
                'language_id' => $this->config->get('config_language_id'),
                'currency_id' => $this->config->get('config_currency_id'),
                'currency_code' => 'TRY',
                'currency_value' => 1.0,
                'ip' => '',
                'forwarded_ip' => '',
                'user_agent' => 'Trendyol Webhook',
                'accept_language' => 'tr-TR,tr;q=0.9'
            ];

            // Add products
            $ocOrderData['products'] = [];
            foreach ($orderData['lines'] as $line) {
                $ocOrderData['products'][] = [
                    'product_id' => 0, // Will be mapped later
                    'name' => $line['productName'] ?? 'Trendyol Product',
                    'model' => $line['barcode'] ?? '',
                    'option' => [],
                    'download' => [],
                    'quantity' => $line['quantity'] ?? 1,
                    'subtract' => false,
                    'price' => $line['price'] ?? 0,
                    'total' => $line['totalPrice'] ?? 0,
                    'tax' => 0,
                    'reward' => 0
                ];
            }

            // Add totals
            $ocOrderData['totals'] = [
                [
                    'code' => 'sub_total',
                    'title' => 'Sub-Total',
                    'value' => $orderData['gross_amount'] - ($orderData['total_discount'] ?? 0),
                    'sort_order' => 1
                ],
                [
                    'code' => 'total',
                    'title' => 'Total',
                    'value' => $orderData['gross_amount'],
                    'sort_order' => 9
                ]
            ];

            // Create the order
            $this->registry->get('model_checkout_order')->addOrder($ocOrderData);
            $ocOrderId = $this->db->getLastId();

            // Link Trendyol order with OpenCart order
            $this->registry->get('model_extension_module_trendyol')->linkOrders($trendyolOrderId, $ocOrderId);

            return $ocOrderId;

        } catch (Exception $e) {
            $this->log->write('Trendyol Order Conversion Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update OpenCart order status based on Trendyol status
     */
    private function updateOpenCartOrderStatus($ocOrderId, $trendyolStatus) {
        $statusMapping = [
            'Created' => 1,        // Pending
            'Picking' => 2,        // Processing
            'Invoiced' => 3,       // Shipped
            'Shipped' => 3,        // Shipped
            'Delivered' => 5,      // Complete
            'Cancelled' => 7,      // Cancelled
            'Returned' => 11       // Returned
        ];

        $ocStatusId = $statusMapping[$trendyolStatus] ?? 1;

        $this->registry->get('load')->model('checkout/order');
        $this->registry->get('model_checkout_order')->addOrderHistory($ocOrderId, $ocStatusId, 'Status updated from Trendyol: ' . $trendyolStatus, false);
    }

    /**
     * Helper method to get the secret from the API client.
     */
    private function getSecretFromClient() {
        // Use the API client's validation method instead of reflection
        return $this->apiClient->validateWebhookSignature('test', 'test') !== null;
    }
}
