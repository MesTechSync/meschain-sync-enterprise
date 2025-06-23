<?php

/**
 * MesChain Trendyol Webhook Controller - OpenCart 4.x
 * Mevcut TrendyolWebhookHandler'dan adapte edilmiştir
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @since OpenCart 4.0.2.3
 */

namespace Opencart\Catalog\Controller\Extension\Meschain\Webhook;

class Trendyol extends \Opencart\System\Engine\Controller
{

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

    public function index(): void
    {
        // Set JSON response header
        $this->response->addHeader('Content-Type: application/json');

        try {
            // Validate webhook request
            if (!$this->validateWebhook()) {
                $this->response->setOutput(json_encode([
                    'success' => false,
                    'error' => 'Invalid webhook signature'
                ]));
                return;
            }

            // Get payload
            $payload = json_decode(file_get_contents('php://input'), true);

            if (!$payload) {
                $this->response->setOutput(json_encode([
                    'success' => false,
                    'error' => 'Invalid payload'
                ]));
                return;
            }

            // Process webhook
            $result = $this->processWebhook($payload);

            $this->response->setOutput(json_encode($result));
        } catch (\Exception $e) {
            $this->log->write('Trendyol Webhook Error: ' . $e->getMessage());

            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Validate webhook signature
     */
    private function validateWebhook(): bool
    {
        $signature = $_SERVER['HTTP_X_TRENDYOL_SIGNATURE'] ?? '';

        if (empty($signature)) {
            $this->log->write('Trendyol Webhook: Missing signature header');
            return false;
        }

        $payload = file_get_contents('php://input');
        $webhookSecret = $this->config->get('meschain_trendyol_webhook_secret');

        if (empty($webhookSecret)) {
            $this->log->write('Trendyol Webhook: Webhook secret not configured');
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        if (!hash_equals($expectedSignature, $signature)) {
            $this->log->write('Trendyol Webhook: Invalid signature');
            return false;
        }

        return true;
    }

    /**
     * Process webhook payload
     */
    private function processWebhook(array $payload): array
    {
        $eventType = $payload['eventType'] ?? $payload['event_type'] ?? '';
        $orderNumber = $payload['orderNumber'] ?? $payload['order_number'] ?? 'N/A';

        // Log the webhook event
        $logId = $this->logWebhookEvent($eventType, $payload);

        try {
            // Check if event type is supported
            if (!in_array($eventType, $this->supportedEvents)) {
                throw new \Exception("Unsupported event type: {$eventType}");
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
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->log->write('Trendyol Webhook Processing Error: ' . $errorMessage);
            $this->updateWebhookLog($logId, false, $errorMessage);

            return ['success' => false, 'error' => $errorMessage];
        }
    }

    /**
     * Process Order Created Event
     */
    private function processOrderCreated(array $payload): array
    {
        try {
            $this->load->model('extension/meschain/trendyol');

            // Check if order already exists
            $existingOrder = $this->model_extension_meschain_trendyol->getTrendyolOrderByNumber($payload['orderNumber']);
            if ($existingOrder) {
                return ['success' => true, 'message' => 'Order already exists', 'action' => 'skipped'];
            }

            // Save order
            $saved = $this->model_extension_meschain_trendyol->saveTrendyolOrder($payload);

            if ($saved) {
                // Auto-convert to OpenCart order if enabled
                if ($this->config->get('meschain_trendyol_auto_convert_orders')) {
                    $this->convertToOpenCartOrder($payload);
                }

                return ['success' => true, 'message' => 'Order created successfully'];
            }

            return ['success' => false, 'error' => 'Failed to save order'];
        } catch (\Exception $e) {
            $this->log->write('Trendyol Webhook Error (Order Created): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Order Status Changed Event
     */
    private function processOrderStatusChanged(array $payload): array
    {
        try {
            $this->load->model('extension/meschain/trendyol');

            $orderNumber = $payload['orderNumber'];
            $newStatus = $payload['status'];

            // Update Trendyol order status
            $updated = $this->model_extension_meschain_trendyol->updateTrendyolOrderStatus($orderNumber, $newStatus);

            if ($updated) {
                // Update corresponding OpenCart order if exists
                $openCartOrder = $this->model_extension_meschain_trendyol->getOpenCartOrderByTrendyolOrder($orderNumber);
                if ($openCartOrder) {
                    $this->updateOpenCartOrderStatus($openCartOrder['order_id'], $newStatus);
                }

                return ['success' => true, 'message' => 'Order status updated successfully'];
            }

            return ['success' => false, 'error' => 'Order not found'];
        } catch (\Exception $e) {
            $this->log->write('Trendyol Webhook Error (Order Status): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Order Cancelled Event
     */
    private function processOrderCancelled(array $payload): array
    {
        try {
            $this->load->model('extension/meschain/trendyol');

            $orderNumber = $payload['orderNumber'];

            // Update Trendyol order status to cancelled
            $updated = $this->model_extension_meschain_trendyol->updateTrendyolOrderStatus($orderNumber, 'Cancelled');

            if ($updated) {
                // Update corresponding OpenCart order if exists
                $openCartOrder = $this->model_extension_meschain_trendyol->getOpenCartOrderByTrendyolOrder($orderNumber);
                if ($openCartOrder) {
                    $this->updateOpenCartOrderStatus($openCartOrder['order_id'], 'Cancelled');
                }

                return ['success' => true, 'message' => 'Order cancelled successfully'];
            }

            return ['success' => false, 'error' => 'Order not found'];
        } catch (\Exception $e) {
            $this->log->write('Trendyol Webhook Error (Order Cancelled): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Product Approved Event
     */
    private function processProductApproved(array $payload): array
    {
        try {
            $this->load->model('extension/meschain/trendyol');

            $barcode = $payload['barcode'];
            $approved = $payload['approved'] ?? true;

            // Update product approval status
            $updated = $this->model_extension_meschain_trendyol->updateProductApprovalStatus($barcode, $approved);

            if ($updated) {
                return ['success' => true, 'message' => 'Product approval status updated'];
            }

            return ['success' => false, 'error' => 'Product not found'];
        } catch (\Exception $e) {
            $this->log->write('Trendyol Webhook Error (Product Approved): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Product Rejected Event
     */
    private function processProductRejected(array $payload): array
    {
        try {
            $this->load->model('extension/meschain/trendyol');

            $barcode = $payload['barcode'];
            $rejectionReason = $payload['rejectionReason'] ?? 'No reason provided';

            // Update product approval status
            $updated = $this->model_extension_meschain_trendyol->updateProductApprovalStatus($barcode, false, $rejectionReason);

            if ($updated) {
                return ['success' => true, 'message' => 'Product rejection processed'];
            }

            return ['success' => false, 'error' => 'Product not found'];
        } catch (\Exception $e) {
            $this->log->write('Trendyol Webhook Error (Product Rejected): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Inventory Updated Event
     */
    private function processInventoryUpdated(array $payload): array
    {
        try {
            $this->load->model('extension/meschain/trendyol');

            $barcode = $payload['barcode'];
            $quantity = $payload['quantity'] ?? 0;

            // Update product inventory
            $updated = $this->model_extension_meschain_trendyol->updateProductInventory($barcode, $quantity);

            if ($updated) {
                return ['success' => true, 'message' => 'Inventory updated successfully'];
            }

            return ['success' => false, 'error' => 'Product not found'];
        } catch (\Exception $e) {
            $this->log->write('Trendyol Webhook Error (Inventory Updated): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Price Updated Event
     */
    private function processPriceUpdated(array $payload): array
    {
        try {
            $this->load->model('extension/meschain/trendyol');

            $barcode = $payload['barcode'];
            $listPrice = $payload['listPrice'] ?? 0;
            $salePrice = $payload['salePrice'] ?? 0;

            // Update product prices
            $updated = $this->model_extension_meschain_trendyol->updateProductPrices($barcode, $listPrice, $salePrice);

            if ($updated) {
                return ['success' => true, 'message' => 'Prices updated successfully'];
            }

            return ['success' => false, 'error' => 'Product not found'];
        } catch (\Exception $e) {
            $this->log->write('Trendyol Webhook Error (Price Updated): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Shipment Created Event
     */
    private function processShipmentCreated(array $payload): array
    {
        try {
            $this->load->model('extension/meschain/trendyol');

            $orderNumber = $payload['orderNumber'];
            $trackingNumber = $payload['trackingNumber'] ?? '';
            $cargoProvider = $payload['cargoProvider'] ?? '';

            // Update order with shipment info
            $updated = $this->model_extension_meschain_trendyol->updateOrderShipment($orderNumber, $trackingNumber, $cargoProvider);

            if ($updated) {
                // Update corresponding OpenCart order if exists
                $openCartOrder = $this->model_extension_meschain_trendyol->getOpenCartOrderByTrendyolOrder($orderNumber);
                if ($openCartOrder) {
                    $this->updateOpenCartOrderStatus($openCartOrder['order_id'], 'Shipped');

                    // Add tracking number to order history
                    $this->load->model('checkout/order');
                    $this->model_checkout_order->addOrderHistory(
                        $openCartOrder['order_id'],
                        3, // Shipped status
                        'Shipment created. Tracking: ' . $trackingNumber . ' (' . $cargoProvider . ')',
                        true
                    );
                }

                return ['success' => true, 'message' => 'Shipment information updated'];
            }

            return ['success' => false, 'error' => 'Order not found'];
        } catch (\Exception $e) {
            $this->log->write('Trendyol Webhook Error (Shipment Created): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Return Initiated Event
     */
    private function processReturnInitiated(array $payload): array
    {
        try {
            $this->load->model('extension/meschain/trendyol');

            $orderNumber = $payload['orderNumber'];
            $returnReason = $payload['returnReason'] ?? 'Customer return';

            // Update order status to return initiated
            $updated = $this->model_extension_meschain_trendyol->updateTrendyolOrderStatus($orderNumber, 'Return Initiated');

            if ($updated) {
                // Update corresponding OpenCart order if exists
                $openCartOrder = $this->model_extension_meschain_trendyol->getOpenCartOrderByTrendyolOrder($orderNumber);
                if ($openCartOrder) {
                    $this->load->model('checkout/order');
                    $this->model_checkout_order->addOrderHistory(
                        $openCartOrder['order_id'],
                        11, // Return status
                        'Return initiated: ' . $returnReason,
                        true
                    );
                }

                return ['success' => true, 'message' => 'Return processed successfully'];
            }

            return ['success' => false, 'error' => 'Order not found'];
        } catch (\Exception $e) {
            $this->log->write('Trendyol Webhook Error (Return Initiated): ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Helper Methods
     */
    private function isEventEnabled(string $eventType): bool
    {
        $query = $this->db->query("SELECT enabled FROM `" . DB_PREFIX . "trendyol_webhook_config` WHERE event_type = '" . $this->db->escape($eventType) . "'");

        if ($query->num_rows) {
            return (bool)$query->row['enabled'];
        }

        // Default to enabled if no configuration found
        return true;
    }

    private function logWebhookEvent(string $eventType, array $eventData, string $signature = '', bool $processed = false, ?string $error = null): int
    {
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

    private function updateWebhookLog(int $logId, bool $success, ?string $message = null): void
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "trendyol_webhook_logs` SET
            processed = '" . (int)$success . "',
            processed_at = NOW(),
            error_message = '" . $this->db->escape($message) . "'
            WHERE log_id = '" . (int)$logId . "'");
    }

    private function updateOpenCartOrderStatus(int $ocOrderId, string $trendyolStatus): void
    {
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

        $this->load->model('checkout/order');
        $this->model_checkout_order->addOrderHistory($ocOrderId, $ocStatusId, 'Status updated from Trendyol: ' . $trendyolStatus, false);
    }

    private function convertToOpenCartOrder(array $orderData): ?int
    {
        try {
            $this->load->model('checkout/order');
            $this->load->model('extension/meschain/trendyol');

            // Create OpenCart order data structure
            $ocOrderData = [
                'invoice_prefix' => 'TY-',
                'store_id' => 0,
                'store_name' => $this->config->get('config_name'),
                'store_url' => $this->config->get('config_url'),
                'customer_id' => 0,
                'customer_group_id' => $this->config->get('config_customer_group_id'),
                'firstname' => explode(' ', $orderData['customer_name'] ?? 'Trendyol Customer')[0] ?? 'Trendyol',
                'lastname' => explode(' ', $orderData['customer_name'] ?? 'Trendyol Customer', 2)[1] ?? 'Customer',
                'email' => $orderData['customer_email'] ?: 'noreply@trendyol.com',
                'telephone' => '',
                'custom_field' => [],
                'payment_firstname' => explode(' ', $orderData['customer_name'] ?? 'Trendyol Customer')[0] ?? 'Trendyol',
                'payment_lastname' => explode(' ', $orderData['customer_name'] ?? 'Trendyol Customer', 2)[1] ?? 'Customer',
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
                'shipping_firstname' => explode(' ', $orderData['customer_name'] ?? 'Trendyol Customer')[0] ?? 'Trendyol',
                'shipping_lastname' => explode(' ', $orderData['customer_name'] ?? 'Trendyol Customer', 2)[1] ?? 'Customer',
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
                'comment' => 'Order imported from Trendyol - Order #' . $orderData['orderNumber'],
                'total' => $orderData['grossAmount'] ?? 0,
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
            foreach ($orderData['lines'] ?? [] as $line) {
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
                    'value' => ($orderData['grossAmount'] ?? 0) - ($orderData['totalDiscount'] ?? 0),
                    'sort_order' => 1
                ],
                [
                    'code' => 'total',
                    'title' => 'Total',
                    'value' => $orderData['grossAmount'] ?? 0,
                    'sort_order' => 9
                ]
            ];

            // Create the order
            $this->model_checkout_order->addOrder($ocOrderData);
            $ocOrderId = $this->db->getLastId();

            // Link Trendyol order with OpenCart order
            $this->model_extension_meschain_trendyol->linkOrders($orderData['orderNumber'], $ocOrderId);

            return $ocOrderId;
        } catch (\Exception $e) {
            $this->log->write('Trendyol Order Conversion Error: ' . $e->getMessage());
            return null;
        }
    }
}
