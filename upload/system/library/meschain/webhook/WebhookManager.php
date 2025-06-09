<?php
/**
 * MesChain Webhook Manager
 * Advanced Webhook Management System for Multi-Marketplace Integration
 * 
 * @author MesChain Development Team
 * @version 4.0.0
 * @copyright 2024 MesChain Technologies
 */

class WebhookManager {
    
    private $db;
    private $config;
    private $logger;
    private $security;
    private $supported_events;
    private $marketplace_configs;

    public function __construct($db, $config, $logger = null) {
        $this->db = $db;
        $this->config = $config;
        $this->logger = $logger;
        $this->security = new WebhookSecurity();
        
        $this->initializeSupportedEvents();
        $this->initializeMarketplaceConfigs();
    }

    /**
     * Initialize supported webhook events
     */
    private function initializeSupportedEvents() {
        $this->supported_events = [
            // Order Events
            'order.created' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleOrderCreated',
                'priority' => 'high'
            ],
            'order.updated' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleOrderUpdated',
                'priority' => 'high'
            ],
            'order.cancelled' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleOrderCancelled',
                'priority' => 'high'
            ],
            'order.shipped' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleOrderShipped',
                'priority' => 'medium'
            ],
            'order.delivered' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleOrderDelivered',
                'priority' => 'medium'
            ],
            'order.returned' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleOrderReturned',
                'priority' => 'high'
            ],

            // Product Events
            'product.created' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleProductCreated',
                'priority' => 'medium'
            ],
            'product.updated' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleProductUpdated',
                'priority' => 'medium'
            ],
            'product.deleted' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleProductDeleted',
                'priority' => 'medium'
            ],
            'product.approved' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleProductApproved',
                'priority' => 'medium'
            ],
            'product.rejected' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleProductRejected',
                'priority' => 'high'
            ],

            // Inventory Events
            'inventory.updated' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleInventoryUpdated',
                'priority' => 'medium'
            ],
            'inventory.low_stock' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleLowStock',
                'priority' => 'high'
            ],
            'inventory.out_of_stock' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleOutOfStock',
                'priority' => 'high'
            ],

            // Price Events
            'price.updated' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handlePriceUpdated',
                'priority' => 'medium'
            ],
            'price.competitor_change' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleCompetitorPriceChange',
                'priority' => 'medium'
            ],

            // Customer Events
            'customer.question' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleCustomerQuestion',
                'priority' => 'high'
            ],
            'customer.review' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleCustomerReview',
                'priority' => 'medium'
            ],
            'customer.complaint' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleCustomerComplaint',
                'priority' => 'high'
            ],

            // Campaign Events
            'campaign.started' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleCampaignStarted',
                'priority' => 'low'
            ],
            'campaign.ended' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleCampaignEnded',
                'priority' => 'low'
            ],

            // System Events
            'system.error' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleSystemError',
                'priority' => 'critical'
            ],
            'system.maintenance' => [
                'marketplaces' => ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'],
                'handler' => 'handleSystemMaintenance',
                'priority' => 'medium'
            ]
        ];
    }

    /**
     * Initialize marketplace-specific configurations
     */
    private function initializeMarketplaceConfigs() {
        $this->marketplace_configs = [
            'trendyol' => [
                'webhook_url' => $this->config->get('trendyol_webhook_url'),
                'secret_key' => $this->config->get('trendyol_webhook_secret'),
                'signature_header' => 'X-Trendyol-Signature',
                'event_header' => 'X-Trendyol-Event',
                'timestamp_header' => 'X-Trendyol-Timestamp'
            ],
            'n11' => [
                'webhook_url' => $this->config->get('n11_webhook_url'),
                'secret_key' => $this->config->get('n11_webhook_secret'),
                'signature_header' => 'X-N11-Signature',
                'event_header' => 'X-N11-Event',
                'timestamp_header' => 'X-N11-Timestamp'
            ],
            'amazon' => [
                'webhook_url' => $this->config->get('amazon_webhook_url'),
                'secret_key' => $this->config->get('amazon_webhook_secret'),
                'signature_header' => 'X-Amazon-Signature',
                'event_header' => 'X-Amazon-Event',
                'timestamp_header' => 'X-Amazon-Timestamp'
            ],
            'ebay' => [
                'webhook_url' => $this->config->get('ebay_webhook_url'),
                'secret_key' => $this->config->get('ebay_webhook_secret'),
                'signature_header' => 'X-eBay-Signature',
                'event_header' => 'X-eBay-Event',
                'timestamp_header' => 'X-eBay-Timestamp'
            ],
            'hepsiburada' => [
                'webhook_url' => $this->config->get('hepsiburada_webhook_url'),
                'secret_key' => $this->config->get('hepsiburada_webhook_secret'),
                'signature_header' => 'X-Hepsiburada-Signature',
                'event_header' => 'X-Hepsiburada-Event',
                'timestamp_header' => 'X-Hepsiburada-Timestamp'
            ],
            'ozon' => [
                'webhook_url' => $this->config->get('ozon_webhook_url'),
                'secret_key' => $this->config->get('ozon_webhook_secret'),
                'signature_header' => 'X-Ozon-Signature',
                'event_header' => 'X-Ozon-Event',
                'timestamp_header' => 'X-Ozon-Timestamp'
            ],
            'pazarama' => [
                'webhook_url' => $this->config->get('pazarama_webhook_url'),
                'secret_key' => $this->config->get('pazarama_webhook_secret'),
                'signature_header' => 'X-Pazarama-Signature',
                'event_header' => 'X-Pazarama-Event',
                'timestamp_header' => 'X-Pazarama-Timestamp'
            ]
        ];
    }

    /**
     * Process incoming webhook
     */
    public function processWebhook($marketplace, $headers, $payload) {
        try {
            // Validate marketplace
            if (!$this->isMarketplaceSupported($marketplace)) {
                throw new Exception("Unsupported marketplace: $marketplace");
            }

            // Verify webhook signature
            if (!$this->verifySignature($marketplace, $headers, $payload)) {
                throw new Exception("Invalid webhook signature");
            }

            // Parse webhook data
            $webhook_data = $this->parseWebhookData($marketplace, $headers, $payload);

            // Validate event type
            if (!$this->isEventSupported($webhook_data['event'])) {
                throw new Exception("Unsupported event type: " . $webhook_data['event']);
            }

            // Check if marketplace supports this event
            if (!$this->isEventSupportedByMarketplace($webhook_data['event'], $marketplace)) {
                throw new Exception("Event not supported by marketplace: " . $webhook_data['event']);
            }

            // Store webhook for processing
            $webhook_id = $this->storeWebhook($marketplace, $webhook_data);

            // Process webhook based on priority
            $this->processWebhookByPriority($webhook_id, $webhook_data);

            $this->log("Webhook processed successfully", [
                'marketplace' => $marketplace,
                'event' => $webhook_data['event'],
                'webhook_id' => $webhook_id
            ]);

            return [
                'success' => true,
                'webhook_id' => $webhook_id,
                'message' => 'Webhook processed successfully'
            ];

        } catch (Exception $e) {
            $this->log("Webhook processing failed: " . $e->getMessage(), [
                'marketplace' => $marketplace,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify webhook signature
     */
    private function verifySignature($marketplace, $headers, $payload) {
        $config = $this->marketplace_configs[$marketplace];
        
        if (empty($config['secret_key'])) {
            return true; // Skip verification if no secret key configured
        }

        $signature_header = $config['signature_header'];
        $received_signature = $headers[$signature_header] ?? '';

        if (empty($received_signature)) {
            return false;
        }

        $expected_signature = $this->security->generateSignature($payload, $config['secret_key']);

        return hash_equals($expected_signature, $received_signature);
    }

    /**
     * Parse webhook data
     */
    private function parseWebhookData($marketplace, $headers, $payload) {
        $config = $this->marketplace_configs[$marketplace];
        
        $event_header = $config['event_header'];
        $timestamp_header = $config['timestamp_header'];

        $event_type = $headers[$event_header] ?? '';
        $timestamp = $headers[$timestamp_header] ?? time();

        $data = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON payload");
        }

        return [
            'event' => $event_type,
            'timestamp' => $timestamp,
            'data' => $data,
            'raw_payload' => $payload
        ];
    }

    /**
     * Store webhook for processing
     */
    private function storeWebhook($marketplace, $webhook_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . "meschain_webhooks` SET
                marketplace = '" . $this->db->escape($marketplace) . "',
                event_type = '" . $this->db->escape($webhook_data['event']) . "',
                payload = '" . $this->db->escape($webhook_data['raw_payload']) . "',
                webhook_data = '" . $this->db->escape(json_encode($webhook_data['data'])) . "',
                event_timestamp = FROM_UNIXTIME('" . (int)$webhook_data['timestamp'] . "'),
                status = 'pending',
                priority = '" . $this->getEventPriority($webhook_data['event']) . "',
                created_at = NOW()";

        $this->db->query($sql);
        return $this->db->getLastId();
    }

    /**
     * Process webhook based on priority
     */
    private function processWebhookByPriority($webhook_id, $webhook_data) {
        $priority = $this->getEventPriority($webhook_data['event']);

        switch ($priority) {
            case 'critical':
                // Process immediately
                $this->processWebhookImmediate($webhook_id, $webhook_data);
                break;
            case 'high':
                // Process within 1 minute
                $this->queueWebhookProcessing($webhook_id, 60);
                break;
            case 'medium':
                // Process within 5 minutes
                $this->queueWebhookProcessing($webhook_id, 300);
                break;
            case 'low':
                // Process within 30 minutes
                $this->queueWebhookProcessing($webhook_id, 1800);
                break;
            default:
                $this->queueWebhookProcessing($webhook_id, 300);
                break;
        }
    }

    /**
     * Process webhook immediately
     */
    private function processWebhookImmediate($webhook_id, $webhook_data) {
        try {
            $this->updateWebhookStatus($webhook_id, 'processing');

            $handler = $this->supported_events[$webhook_data['event']]['handler'];
            $result = $this->$handler($webhook_data);

            if ($result['success']) {
                $this->updateWebhookStatus($webhook_id, 'completed', $result['message']);
            } else {
                $this->updateWebhookStatus($webhook_id, 'failed', $result['error']);
            }

        } catch (Exception $e) {
            $this->updateWebhookStatus($webhook_id, 'failed', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Queue webhook for later processing
     */
    private function queueWebhookProcessing($webhook_id, $delay_seconds = 0) {
        $process_at = date('Y-m-d H:i:s', time() + $delay_seconds);

        $sql = "UPDATE `" . DB_PREFIX . "meschain_webhooks` SET
                status = 'queued',
                process_at = '" . $process_at . "'
                WHERE webhook_id = '" . (int)$webhook_id . "'";

        $this->db->query($sql);
    }

    /**
     * Update webhook status
     */
    private function updateWebhookStatus($webhook_id, $status, $message = '') {
        $sql = "UPDATE `" . DB_PREFIX . "meschain_webhooks` SET
                status = '" . $this->db->escape($status) . "',
                processed_at = NOW(),
                response_message = '" . $this->db->escape($message) . "'
                WHERE webhook_id = '" . (int)$webhook_id . "'";

        $this->db->query($sql);
    }

    /**
     * Get event priority
     */
    private function getEventPriority($event) {
        return $this->supported_events[$event]['priority'] ?? 'medium';
    }

    /**
     * Check if marketplace is supported
     */
    private function isMarketplaceSupported($marketplace) {
        return isset($this->marketplace_configs[$marketplace]);
    }

    /**
     * Check if event is supported
     */
    private function isEventSupported($event) {
        return isset($this->supported_events[$event]);
    }

    /**
     * Check if event is supported by marketplace
     */
    private function isEventSupportedByMarketplace($event, $marketplace) {
        return in_array($marketplace, $this->supported_events[$event]['marketplaces']);
    }

    /**
     * Handle order created event
     */
    private function handleOrderCreated($webhook_data) {
        try {
            // Extract order data
            $order_data = $webhook_data['data'];
            
            // Process order creation
            $order_processor = new OrderProcessor($this->db, $this->config);
            $result = $order_processor->createOrder($order_data);

            // Send notifications
            $this->sendOrderNotification('order_created', $order_data);

            // Update analytics
            $this->updateOrderAnalytics('created', $order_data);

            return ['success' => true, 'message' => 'Order created successfully'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle order updated event
     */
    private function handleOrderUpdated($webhook_data) {
        try {
            $order_data = $webhook_data['data'];
            
            $order_processor = new OrderProcessor($this->db, $this->config);
            $result = $order_processor->updateOrder($order_data);

            $this->sendOrderNotification('order_updated', $order_data);
            $this->updateOrderAnalytics('updated', $order_data);

            return ['success' => true, 'message' => 'Order updated successfully'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle order cancelled event
     */
    private function handleOrderCancelled($webhook_data) {
        try {
            $order_data = $webhook_data['data'];
            
            $order_processor = new OrderProcessor($this->db, $this->config);
            $result = $order_processor->cancelOrder($order_data);

            // Restore inventory
            $this->restoreInventory($order_data);

            $this->sendOrderNotification('order_cancelled', $order_data);
            $this->updateOrderAnalytics('cancelled', $order_data);

            return ['success' => true, 'message' => 'Order cancelled successfully'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle product approved event
     */
    private function handleProductApproved($webhook_data) {
        try {
            $product_data = $webhook_data['data'];
            
            $product_processor = new ProductProcessor($this->db, $this->config);
            $result = $product_processor->approveProduct($product_data);

            $this->sendProductNotification('product_approved', $product_data);
            $this->updateProductAnalytics('approved', $product_data);

            return ['success' => true, 'message' => 'Product approved successfully'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle low stock event
     */
    private function handleLowStock($webhook_data) {
        try {
            $inventory_data = $webhook_data['data'];
            
            // Send low stock alert
            $this->sendLowStockAlert($inventory_data);

            // Trigger automatic reorder if enabled
            $this->triggerAutoReorder($inventory_data);

            return ['success' => true, 'message' => 'Low stock handled successfully'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle customer question event
     */
    private function handleCustomerQuestion($webhook_data) {
        try {
            $question_data = $webhook_data['data'];
            
            // Auto-respond if possible
            $auto_response = $this->generateAutoResponse($question_data);
            if ($auto_response) {
                $this->sendAutoResponse($question_data, $auto_response);
            }

            // Notify customer service team
            $this->notifyCustomerService($question_data);

            return ['success' => true, 'message' => 'Customer question handled successfully'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send notifications
     */
    private function sendOrderNotification($type, $order_data) {
        // Implementation for sending notifications
        // This could integrate with email, SMS, push notifications, etc.
    }

    private function sendProductNotification($type, $product_data) {
        // Implementation for product notifications
    }

    private function sendLowStockAlert($inventory_data) {
        // Implementation for low stock alerts
    }

    /**
     * Analytics updates
     */
    private function updateOrderAnalytics($action, $order_data) {
        // Implementation for updating order analytics
    }

    private function updateProductAnalytics($action, $product_data) {
        // Implementation for updating product analytics
    }

    /**
     * Utility methods
     */
    private function restoreInventory($order_data) {
        // Implementation for inventory restoration
    }

    private function triggerAutoReorder($inventory_data) {
        // Implementation for automatic reordering
    }

    private function generateAutoResponse($question_data) {
        // Implementation for generating automatic responses
        return null;
    }

    private function sendAutoResponse($question_data, $response) {
        // Implementation for sending auto responses
    }

    private function notifyCustomerService($question_data) {
        // Implementation for notifying customer service
    }

    /**
     * Logging
     */
    private function log($message, $context = [], $level = 'info') {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Get webhook statistics
     */
    public function getWebhookStats($period = '24_hours') {
        $where_clause = "";
        
        switch ($period) {
            case '1_hour':
                $where_clause = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
                break;
            case '24_hours':
                $where_clause = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                break;
            case '7_days':
                $where_clause = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case '30_days':
                $where_clause = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
        }

        $sql = "SELECT 
                    COUNT(*) as total_webhooks,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_webhooks,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_webhooks,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_webhooks,
                    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_webhooks
                FROM `" . DB_PREFIX . "meschain_webhooks`
                $where_clause";

        $query = $this->db->query($sql);
        return $query->row;
    }

    /**
     * Get webhook history
     */
    public function getWebhookHistory($filters = []) {
        $where_conditions = [];
        
        if (!empty($filters['marketplace'])) {
            $where_conditions[] = "marketplace = '" . $this->db->escape($filters['marketplace']) . "'";
        }
        
        if (!empty($filters['event_type'])) {
            $where_conditions[] = "event_type = '" . $this->db->escape($filters['event_type']) . "'";
        }
        
        if (!empty($filters['status'])) {
            $where_conditions[] = "status = '" . $this->db->escape($filters['status']) . "'";
        }
        
        if (!empty($filters['date_from'])) {
            $where_conditions[] = "created_at >= '" . $this->db->escape($filters['date_from']) . "'";
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = "created_at <= '" . $this->db->escape($filters['date_to']) . "'";
        }

        $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_webhooks`
                $where_clause
                ORDER BY created_at DESC
                LIMIT " . (int)($filters['limit'] ?? 100);

        $query = $this->db->query($sql);
        return $query->rows;
    }
}

/**
 * Webhook Security Helper Class
 */
class WebhookSecurity {
    
    /**
     * Generate webhook signature
     */
    public function generateSignature($payload, $secret) {
        return hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Validate timestamp to prevent replay attacks
     */
    public function validateTimestamp($timestamp, $tolerance = 300) {
        $current_time = time();
        $webhook_time = is_numeric($timestamp) ? (int)$timestamp : strtotime($timestamp);
        
        return abs($current_time - $webhook_time) <= $tolerance;
    }

    /**
     * Sanitize webhook payload
     */
    public function sanitizePayload($payload) {
        // Remove potentially harmful content
        $sanitized = preg_replace('/[^\w\s\-_\.@,{}[\]":\/\\\\]/', '', $payload);
        return $sanitized;
    }

    /**
     * Rate limiting check
     */
    public function checkRateLimit($marketplace, $limit_per_minute = 100) {
        // Implementation for rate limiting
        return true;
    }
}
?> 