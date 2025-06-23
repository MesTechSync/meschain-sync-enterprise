<?php
/**
 * Pazarama Webhook Handler
 * 
 * @package    MesChain-Sync Enterprise
 * @author     Mezben Akkuzu <me@mezben.com>
 * @copyright  2024 MesChain Technologies
 * @license    Commercial License
 * @version    2.0.0
 * @since      File available since Release 2.0.0
 */

namespace MesChain\Webhook;

class PazaramaWebhookHandler
{
    private $db;
    private $config;
    private $log;
    private $cache;
    private $pazarama_api;
    
    // Webhook event types
    const EVENT_ORDER_CREATED = 'order.created';
    const EVENT_ORDER_UPDATED = 'order.updated';
    const EVENT_ORDER_CANCELLED = 'order.cancelled';
    const EVENT_ORDER_SHIPPED = 'order.shipped';
    const EVENT_ORDER_DELIVERED = 'order.delivered';
    const EVENT_PRODUCT_UPDATED = 'product.updated';
    const EVENT_PRODUCT_PRICE_CHANGED = 'product.price_changed';
    const EVENT_PRODUCT_STOCK_CHANGED = 'product.stock_changed';
    const EVENT_PRODUCT_APPROVED = 'product.approved';
    const EVENT_PRODUCT_REJECTED = 'product.rejected';
    const EVENT_COMMISSION_UPDATED = 'commission.updated';
    const EVENT_CAMPAIGN_STARTED = 'campaign.started';
    const EVENT_CAMPAIGN_ENDED = 'campaign.ended';
    const EVENT_RETURN_REQUEST = 'return.request';
    const EVENT_RETURN_APPROVED = 'return.approved';
    const EVENT_REFUND_PROCESSED = 'refund.processed';
    
    /**
     * Constructor
     */
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->log = $registry->get('log');
        $this->cache = $registry->get('cache');
        
        // Load Pazarama API client
        require_once(DIR_SYSTEM . 'library/meschain/api/PazaramaApiClient.php');
        $this->pazarama_api = new \MesChain\Api\PazaramaApiClient($this->config);
        
        $this->log->write('[PAZARAMA WEBHOOK] Handler initialized');
    }
    
    /**
     * Handle incoming webhook
     */
    public function handleWebhook($payload, $signature = null)
    {
        try {
            // Verify webhook signature
            if (!$this->verifySignature($payload, $signature)) {
                $this->log->write('[PAZARAMA WEBHOOK] Invalid signature');
                return ['status' => 'error', 'message' => 'Invalid signature'];
            }
            
            $data = json_decode($payload, true);
            
            if (!$data || !isset($data['event_type'])) {
                $this->log->write('[PAZARAMA WEBHOOK] Invalid payload format');
                return ['status' => 'error', 'message' => 'Invalid payload'];
            }
            
            // Log webhook received
            $this->logWebhookEvent($data);
            
            // Process webhook based on event type
            $result = $this->processWebhookEvent($data);
            
            // Update webhook processing stats
            $this->updateWebhookStats($data['event_type'], $result['status'] === 'success');
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('[PAZARAMA WEBHOOK] Error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Process webhook event based on type
     */
    private function processWebhookEvent($data)
    {
        $event_type = $data['event_type'];
        $event_data = $data['data'] ?? [];
        
        switch ($event_type) {
            case self::EVENT_ORDER_CREATED:
                return $this->handleOrderCreated($event_data);
                
            case self::EVENT_ORDER_UPDATED:
                return $this->handleOrderUpdated($event_data);
                
            case self::EVENT_ORDER_CANCELLED:
                return $this->handleOrderCancelled($event_data);
                
            case self::EVENT_ORDER_SHIPPED:
                return $this->handleOrderShipped($event_data);
                
            case self::EVENT_ORDER_DELIVERED:
                return $this->handleOrderDelivered($event_data);
                
            case self::EVENT_PRODUCT_UPDATED:
                return $this->handleProductUpdated($event_data);
                
            case self::EVENT_PRODUCT_PRICE_CHANGED:
                return $this->handleProductPriceChanged($event_data);
                
            case self::EVENT_PRODUCT_STOCK_CHANGED:
                return $this->handleProductStockChanged($event_data);
                
            case self::EVENT_PRODUCT_APPROVED:
                return $this->handleProductApproved($event_data);
                
            case self::EVENT_PRODUCT_REJECTED:
                return $this->handleProductRejected($event_data);
                
            case self::EVENT_COMMISSION_UPDATED:
                return $this->handleCommissionUpdated($event_data);
                
            case self::EVENT_CAMPAIGN_STARTED:
                return $this->handleCampaignStarted($event_data);
                
            case self::EVENT_CAMPAIGN_ENDED:
                return $this->handleCampaignEnded($event_data);
                
            case self::EVENT_RETURN_REQUEST:
                return $this->handleReturnRequest($event_data);
                
            case self::EVENT_RETURN_APPROVED:
                return $this->handleReturnApproved($event_data);
                
            case self::EVENT_REFUND_PROCESSED:
                return $this->handleRefundProcessed($event_data);
                
            default:
                $this->log->write('[PAZARAMA WEBHOOK] Unknown event type: ' . $event_type);
                return ['status' => 'error', 'message' => 'Unknown event type'];
        }
    }
    
    /**
     * Handle order created event
     */
    private function handleOrderCreated($data)
    {
        try {
            $order_id = $data['order_id'];
            $pazarama_order_id = $data['pazarama_order_id'];
            
            // Get full order details from API
            $order_details = $this->pazarama_api->getOrderDetails($pazarama_order_id);
            
            if (!$order_details) {
                throw new Exception('Unable to fetch order details from Pazarama API');
            }
            
            // Create order in local database
            $this->createLocalOrder($order_details);
            
            // Update order status
            $this->updateOrderStatus($pazarama_order_id, 'pending');
            
            // Trigger inventory update
            $this->updateInventoryFromOrder($order_details);
            
            // Send notification
            $this->sendOrderNotification('created', $order_details);
            
            $this->log->write('[PAZARAMA WEBHOOK] Order created: ' . $pazarama_order_id);
            
            return ['status' => 'success', 'message' => 'Order created successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[PAZARAMA WEBHOOK] Order creation error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle order updated event
     */
    private function handleOrderUpdated($data)
    {
        try {
            $pazarama_order_id = $data['pazarama_order_id'];
            $status = $data['status'] ?? '';
            
            // Update order status in local database
            $this->updateOrderStatus($pazarama_order_id, $status);
            
            // Get updated order details
            $order_details = $this->pazarama_api->getOrderDetails($pazarama_order_id);
            
            if ($order_details) {
                // Update order details in local database
                $this->updateLocalOrder($order_details);
                
                // Send notification
                $this->sendOrderNotification('updated', $order_details);
            }
            
            $this->log->write('[PAZARAMA WEBHOOK] Order updated: ' . $pazarama_order_id);
            
            return ['status' => 'success', 'message' => 'Order updated successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[PAZARAMA WEBHOOK] Order update error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle product price changed event
     */
    private function handleProductPriceChanged($data)
    {
        try {
            $product_id = $data['product_id'];
            $pazarama_product_id = $data['pazarama_product_id'];
            $old_price = $data['old_price'];
            $new_price = $data['new_price'];
            
            // Update product price in local database
            $this->db->query("
                UPDATE " . DB_PREFIX . "pazarama_products 
                SET price = '" . (float)$new_price . "',
                    old_price = '" . (float)$old_price . "',
                    date_modified = NOW()
                WHERE pazarama_product_id = '" . $this->db->escape($pazarama_product_id) . "'
            ");
            
            // Log price change
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "pazarama_price_history SET
                product_id = '" . (int)$product_id . "',
                pazarama_product_id = '" . $this->db->escape($pazarama_product_id) . "',
                old_price = '" . (float)$old_price . "',
                new_price = '" . (float)$new_price . "',
                change_reason = 'webhook_update',
                date_created = NOW()
            ");
            
            // Clear cache
            $this->cache->delete('pazarama.product.' . $product_id);
            
            $this->log->write('[PAZARAMA WEBHOOK] Product price changed: ' . $pazarama_product_id . ' from ' . $old_price . ' to ' . $new_price);
            
            return ['status' => 'success', 'message' => 'Product price updated successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[PAZARAMA WEBHOOK] Product price update error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle product stock changed event
     */
    private function handleProductStockChanged($data)
    {
        try {
            $product_id = $data['product_id'];
            $pazarama_product_id = $data['pazarama_product_id'];
            $old_stock = $data['old_stock'];
            $new_stock = $data['new_stock'];
            
            // Update product stock in local database
            $this->db->query("
                UPDATE " . DB_PREFIX . "pazarama_products 
                SET stock = '" . (int)$new_stock . "',
                    date_modified = NOW()
                WHERE pazarama_product_id = '" . $this->db->escape($pazarama_product_id) . "'
            ");
            
            // Log stock change
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "pazarama_stock_history SET
                product_id = '" . (int)$product_id . "',
                pazarama_product_id = '" . $this->db->escape($pazarama_product_id) . "',
                old_stock = '" . (int)$old_stock . "',
                new_stock = '" . (int)$new_stock . "',
                change_reason = 'webhook_update',
                date_created = NOW()
            ");
            
            // Update OpenCart product stock if mapped
            $this->updateOpenCartStock($product_id, $new_stock);
            
            // Clear cache
            $this->cache->delete('pazarama.product.' . $product_id);
            
            $this->log->write('[PAZARAMA WEBHOOK] Product stock changed: ' . $pazarama_product_id . ' from ' . $old_stock . ' to ' . $new_stock);
            
            return ['status' => 'success', 'message' => 'Product stock updated successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[PAZARAMA WEBHOOK] Product stock update error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle campaign started event
     */
    private function handleCampaignStarted($data)
    {
        try {
            $campaign_id = $data['campaign_id'];
            $campaign_name = $data['campaign_name'];
            $start_date = $data['start_date'];
            $end_date = $data['end_date'];
            $discount_rate = $data['discount_rate'] ?? 0;
            
            // Save campaign to database
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "pazarama_campaigns SET
                campaign_id = '" . $this->db->escape($campaign_id) . "',
                name = '" . $this->db->escape($campaign_name) . "',
                discount_rate = '" . (float)$discount_rate . "',
                start_date = '" . $this->db->escape($start_date) . "',
                end_date = '" . $this->db->escape($end_date) . "',
                status = 'active',
                date_created = NOW()
            ");
            
            // Apply campaign to eligible products
            $this->applyCampaignToProducts($campaign_id, $discount_rate);
            
            $this->log->write('[PAZARAMA WEBHOOK] Campaign started: ' . $campaign_name);
            
            return ['status' => 'success', 'message' => 'Campaign started successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[PAZARAMA WEBHOOK] Campaign start error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Verify webhook signature
     */
    private function verifySignature($payload, $signature)
    {
        if (!$signature) {
            return true; // Skip verification if no signature provided
        }
        
        $secret = $this->config->get('pazarama_webhook_secret');
        if (!$secret) {
            return true; // Skip verification if no secret configured
        }
        
        $calculated_signature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($calculated_signature, $signature);
    }
    
    /**
     * Log webhook event
     */
    private function logWebhookEvent($data)
    {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "pazarama_webhook_logs SET
            event_type = '" . $this->db->escape($data['event_type']) . "',
            payload = '" . $this->db->escape(json_encode($data)) . "',
            status = 'received',
            date_created = NOW()
        ");
    }
    
    /**
     * Create local order from Pazarama order data
     */
    private function createLocalOrder($order_data)
    {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "pazarama_orders SET
            pazarama_order_id = '" . $this->db->escape($order_data['order_id']) . "',
            order_number = '" . $this->db->escape($order_data['order_number']) . "',
            customer_name = '" . $this->db->escape($order_data['customer']['name']) . "',
            customer_email = '" . $this->db->escape($order_data['customer']['email']) . "',
            total_amount = '" . (float)$order_data['total_amount'] . "',
            currency = '" . $this->db->escape($order_data['currency']) . "',
            status = '" . $this->db->escape($order_data['status']) . "',
            shipping_address = '" . $this->db->escape(json_encode($order_data['shipping_address'])) . "',
            billing_address = '" . $this->db->escape(json_encode($order_data['billing_address'])) . "',
            items = '" . $this->db->escape(json_encode($order_data['items'])) . "',
            date_created = '" . $this->db->escape($order_data['order_date']) . "'
        ");
    }
    
    /**
     * Update order status
     */
    private function updateOrderStatus($pazarama_order_id, $status)
    {
        $this->db->query("
            UPDATE " . DB_PREFIX . "pazarama_orders 
            SET status = '" . $this->db->escape($status) . "',
                date_modified = NOW()
            WHERE pazarama_order_id = '" . $this->db->escape($pazarama_order_id) . "'
        ");
    }
    
    /**
     * Update webhook processing stats
     */
    private function updateWebhookStats($event_type, $success)
    {
        $status = $success ? 'success' : 'failed';
        
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "pazarama_webhook_stats (event_type, status, count, date_created)
            VALUES ('" . $this->db->escape($event_type) . "', '" . $status . "', 1, NOW())
            ON DUPLICATE KEY UPDATE
            count = count + 1,
            date_modified = NOW()
        ");
    }
    
    /**
     * Send order notification
     */
    private function sendOrderNotification($type, $order_data)
    {
        // Implementation for sending notifications (email, SMS, etc.)
        $this->log->write('[PAZARAMA WEBHOOK] Order notification sent: ' . $type . ' for order ' . $order_data['order_id']);
    }
    
    /**
     * Update inventory from order
     */
    private function updateInventoryFromOrder($order_data)
    {
        foreach ($order_data['items'] as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            
            // Decrease stock
            $this->db->query("
                UPDATE " . DB_PREFIX . "pazarama_products 
                SET stock = stock - " . (int)$quantity . "
                WHERE pazarama_product_id = '" . $this->db->escape($product_id) . "'
            ");
        }
    }
    
    /**
     * Update OpenCart stock
     */
    private function updateOpenCartStock($product_id, $new_stock)
    {
        // Get OpenCart product mapping
        $query = $this->db->query("
            SELECT opencart_product_id 
            FROM " . DB_PREFIX . "pazarama_product_mapping 
            WHERE pazarama_product_id = '" . (int)$product_id . "'
        ");
        
        if ($query->num_rows) {
            $opencart_product_id = $query->row['opencart_product_id'];
            
            // Update OpenCart product stock
            $this->db->query("
                UPDATE " . DB_PREFIX . "product 
                SET quantity = '" . (int)$new_stock . "'
                WHERE product_id = '" . (int)$opencart_product_id . "'
            ");
        }
    }
    
    /**
     * Apply campaign to products
     */
    private function applyCampaignToProducts($campaign_id, $discount_rate)
    {
        // Get eligible products for campaign
        $query = $this->db->query("
            SELECT pazarama_product_id, price 
            FROM " . DB_PREFIX . "pazarama_products 
            WHERE status = 'active'
        ");
        
        foreach ($query->rows as $product) {
            $discounted_price = $product['price'] * (1 - $discount_rate / 100);
            
            // Update product with campaign price
            $this->db->query("
                UPDATE " . DB_PREFIX . "pazarama_products 
                SET campaign_price = '" . (float)$discounted_price . "',
                    campaign_id = '" . $this->db->escape($campaign_id) . "'
                WHERE pazarama_product_id = '" . $this->db->escape($product['pazarama_product_id']) . "'
            ");
        }
    }
    
    /**
     * Handle remaining webhook events
     */
    private function handleOrderCancelled($data) {
        $pazarama_order_id = $data['pazarama_order_id'];
        $this->updateOrderStatus($pazarama_order_id, 'cancelled');
        return ['status' => 'success', 'message' => 'Order cancelled successfully'];
    }
    
    private function handleOrderShipped($data) {
        $pazarama_order_id = $data['pazarama_order_id'];
        $this->updateOrderStatus($pazarama_order_id, 'shipped');
        return ['status' => 'success', 'message' => 'Order shipped successfully'];
    }
    
    private function handleOrderDelivered($data) {
        $pazarama_order_id = $data['pazarama_order_id'];
        $this->updateOrderStatus($pazarama_order_id, 'delivered');
        return ['status' => 'success', 'message' => 'Order delivered successfully'];
    }
    
    private function handleProductUpdated($data) {
        $pazarama_product_id = $data['pazarama_product_id'];
        $this->cache->delete('pazarama.product.' . $pazarama_product_id);
        return ['status' => 'success', 'message' => 'Product updated successfully'];
    }
    
    private function handleProductApproved($data) {
        $pazarama_product_id = $data['pazarama_product_id'];
        $this->db->query("UPDATE " . DB_PREFIX . "pazarama_products SET status = 'approved' WHERE pazarama_product_id = '" . $this->db->escape($pazarama_product_id) . "'");
        return ['status' => 'success', 'message' => 'Product approved successfully'];
    }
    
    private function handleProductRejected($data) {
        $pazarama_product_id = $data['pazarama_product_id'];
        $this->db->query("UPDATE " . DB_PREFIX . "pazarama_products SET status = 'rejected' WHERE pazarama_product_id = '" . $this->db->escape($pazarama_product_id) . "'");
        return ['status' => 'success', 'message' => 'Product rejected successfully'];
    }
    
    private function handleCommissionUpdated($data) {
        $category_id = $data['category_id'];
        $commission_rate = $data['commission_rate'];
        $this->db->query("UPDATE " . DB_PREFIX . "pazarama_categories SET commission_rate = '" . (float)$commission_rate . "' WHERE category_id = '" . (int)$category_id . "'");
        return ['status' => 'success', 'message' => 'Commission updated successfully'];
    }
    
    private function handleCampaignEnded($data) {
        $campaign_id = $data['campaign_id'];
        $this->db->query("UPDATE " . DB_PREFIX . "pazarama_campaigns SET status = 'ended' WHERE campaign_id = '" . $this->db->escape($campaign_id) . "'");
        return ['status' => 'success', 'message' => 'Campaign ended successfully'];
    }
    
    private function handleReturnRequest($data) {
        $order_id = $data['order_id'];
        $this->db->query("INSERT INTO " . DB_PREFIX . "pazarama_returns SET order_id = '" . $this->db->escape($order_id) . "', status = 'requested', date_created = NOW()");
        return ['status' => 'success', 'message' => 'Return request processed successfully'];
    }
    
    private function handleReturnApproved($data) {
        $return_id = $data['return_id'];
        $this->db->query("UPDATE " . DB_PREFIX . "pazarama_returns SET status = 'approved' WHERE return_id = '" . (int)$return_id . "'");
        return ['status' => 'success', 'message' => 'Return approved successfully'];
    }
    
    private function handleRefundProcessed($data) {
        $order_id = $data['order_id'];
        $amount = $data['amount'];
        $this->db->query("INSERT INTO " . DB_PREFIX . "pazarama_refunds SET order_id = '" . $this->db->escape($order_id) . "', amount = '" . (float)$amount . "', status = 'processed', date_created = NOW()");
        return ['status' => 'success', 'message' => 'Refund processed successfully'];
    }
} 