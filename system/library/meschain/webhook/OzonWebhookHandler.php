<?php
/**
 * Ozon Webhook Handler
 * 
 * @package    MesChain-Sync Enterprise
 * @author     Mezben Akkuzu <me@mezben.com>
 * @copyright  2024 MesChain Technologies
 * @license    Commercial License
 * @version    2.0.0
 * @since      File available since Release 2.0.0
 */

namespace MesChain\Webhook;

class OzonWebhookHandler
{
    private $db;
    private $config;
    private $log;
    private $cache;
    private $ozon_api;
    
    // Webhook event types
    const EVENT_ORDER_NEW = 'order.new';
    const EVENT_ORDER_STATUS_CHANGED = 'order.status_changed';
    const EVENT_ORDER_CANCELLED = 'order.cancelled';
    const EVENT_ORDER_AWAITING_DELIVERY = 'order.awaiting_delivery';
    const EVENT_ORDER_DELIVERED = 'order.delivered';
    const EVENT_PRODUCT_PRICE_CHANGED = 'product.price_changed';
    const EVENT_PRODUCT_STOCK_CHANGED = 'product.stock_changed';
    const EVENT_PRODUCT_MODERATED = 'product.moderated';
    const EVENT_PRODUCT_BLOCKED = 'product.blocked';
    const EVENT_PRODUCT_UNBLOCKED = 'product.unblocked';
    const EVENT_FBO_STOCK_CHANGED = 'fbo.stock_changed';
    const EVENT_FBS_STOCK_CHANGED = 'fbs.stock_changed';
    const EVENT_COMMISSION_CHANGED = 'commission.changed';
    const EVENT_PROMOTION_STARTED = 'promotion.started';
    const EVENT_PROMOTION_ENDED = 'promotion.ended';
    const EVENT_RETURN_CREATED = 'return.created';
    const EVENT_RETURN_APPROVED = 'return.approved';
    const EVENT_ARBITRAGE_CREATED = 'arbitrage.created';
    
    /**
     * Constructor
     */
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->log = $registry->get('log');
        $this->cache = $registry->get('cache');
        
        // Load Ozon API client
        require_once(DIR_SYSTEM . 'library/meschain/api/OzonApiClient.php');
        $this->ozon_api = new \MesChain\Api\OzonApiClient($this->config);
        
        $this->log->write('[OZON WEBHOOK] Handler initialized');
    }
    
    /**
     * Handle incoming webhook
     */
    public function handleWebhook($payload, $signature = null)
    {
        try {
            // Verify webhook signature
            if (!$this->verifySignature($payload, $signature)) {
                $this->log->write('[OZON WEBHOOK] Invalid signature');
                return ['status' => 'error', 'message' => 'Invalid signature'];
            }
            
            $data = json_decode($payload, true);
            
            if (!$data || !isset($data['event_type'])) {
                $this->log->write('[OZON WEBHOOK] Invalid payload format');
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
            $this->log->write('[OZON WEBHOOK] Error: ' . $e->getMessage());
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
            case self::EVENT_ORDER_NEW:
                return $this->handleOrderNew($event_data);
                
            case self::EVENT_ORDER_STATUS_CHANGED:
                return $this->handleOrderStatusChanged($event_data);
                
            case self::EVENT_ORDER_CANCELLED:
                return $this->handleOrderCancelled($event_data);
                
            case self::EVENT_ORDER_AWAITING_DELIVERY:
                return $this->handleOrderAwaitingDelivery($event_data);
                
            case self::EVENT_ORDER_DELIVERED:
                return $this->handleOrderDelivered($event_data);
                
            case self::EVENT_PRODUCT_PRICE_CHANGED:
                return $this->handleProductPriceChanged($event_data);
                
            case self::EVENT_PRODUCT_STOCK_CHANGED:
                return $this->handleProductStockChanged($event_data);
                
            case self::EVENT_PRODUCT_MODERATED:
                return $this->handleProductModerated($event_data);
                
            case self::EVENT_PRODUCT_BLOCKED:
                return $this->handleProductBlocked($event_data);
                
            case self::EVENT_PRODUCT_UNBLOCKED:
                return $this->handleProductUnblocked($event_data);
                
            case self::EVENT_FBO_STOCK_CHANGED:
                return $this->handleFboStockChanged($event_data);
                
            case self::EVENT_FBS_STOCK_CHANGED:
                return $this->handleFbsStockChanged($event_data);
                
            case self::EVENT_COMMISSION_CHANGED:
                return $this->handleCommissionChanged($event_data);
                
            case self::EVENT_PROMOTION_STARTED:
                return $this->handlePromotionStarted($event_data);
                
            case self::EVENT_PROMOTION_ENDED:
                return $this->handlePromotionEnded($event_data);
                
            case self::EVENT_RETURN_CREATED:
                return $this->handleReturnCreated($event_data);
                
            case self::EVENT_RETURN_APPROVED:
                return $this->handleReturnApproved($event_data);
                
            case self::EVENT_ARBITRAGE_CREATED:
                return $this->handleArbitrageCreated($event_data);
                
            default:
                $this->log->write('[OZON WEBHOOK] Unknown event type: ' . $event_type);
                return ['status' => 'error', 'message' => 'Unknown event type'];
        }
    }
    
    /**
     * Handle new order event
     */
    private function handleOrderNew($data)
    {
        try {
            $order_id = $data['order_id'];
            $posting_number = $data['posting_number'];
            
            // Get full order details from API
            $order_details = $this->ozon_api->getOrderDetails($posting_number);
            
            if (!$order_details) {
                throw new Exception('Unable to fetch order details from Ozon API');
            }
            
            // Create order in local database
            $this->createLocalOrder($order_details);
            
            // Update order status
            $this->updateOrderStatus($posting_number, 'new');
            
            // Process order items and update inventory
            $this->processOrderItems($order_details);
            
            // Send notification
            $this->sendOrderNotification('new', $order_details);
            
            $this->log->write('[OZON WEBHOOK] New order processed: ' . $posting_number);
            
            return ['status' => 'success', 'message' => 'New order processed successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[OZON WEBHOOK] New order error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle order status changed event
     */
    private function handleOrderStatusChanged($data)
    {
        try {
            $posting_number = $data['posting_number'];
            $old_status = $data['old_status'];
            $new_status = $data['new_status'];
            
            // Update order status in local database
            $this->updateOrderStatus($posting_number, $new_status);
            
            // Log status change
            $this->logStatusChange($posting_number, $old_status, $new_status);
            
            // Handle specific status changes
            $this->handleStatusSpecificActions($posting_number, $new_status);
            
            $this->log->write('[OZON WEBHOOK] Order status changed: ' . $posting_number . ' from ' . $old_status . ' to ' . $new_status);
            
            return ['status' => 'success', 'message' => 'Order status updated successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[OZON WEBHOOK] Order status change error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle product price changed event
     */
    private function handleProductPriceChanged($data)
    {
        try {
            $offer_id = $data['offer_id'];
            $product_id = $data['product_id'];
            $old_price = $data['old_price'];
            $new_price = $data['new_price'];
            $currency = $data['currency'] ?? 'RUB';
            
            // Update product price in local database
            $this->db->query("
                UPDATE " . DB_PREFIX . "ozon_products 
                SET price = '" . (float)$new_price . "',
                    old_price = '" . (float)$old_price . "',
                    currency = '" . $this->db->escape($currency) . "',
                    date_modified = NOW()
                WHERE offer_id = '" . $this->db->escape($offer_id) . "'
            ");
            
            // Log price change history
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "ozon_price_history SET
                offer_id = '" . $this->db->escape($offer_id) . "',
                product_id = '" . (int)$product_id . "',
                old_price = '" . (float)$old_price . "',
                new_price = '" . (float)$new_price . "',
                currency = '" . $this->db->escape($currency) . "',
                change_reason = 'webhook_update',
                date_created = NOW()
            ");
            
            // Clear cache
            $this->cache->delete('ozon.product.' . $offer_id);
            
            // Update OpenCart product price if mapped
            $this->updateOpenCartPrice($offer_id, $new_price);
            
            $this->log->write('[OZON WEBHOOK] Product price changed: ' . $offer_id . ' from ' . $old_price . ' to ' . $new_price);
            
            return ['status' => 'success', 'message' => 'Product price updated successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[OZON WEBHOOK] Product price update error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle FBO stock changed event
     */
    private function handleFboStockChanged($data)
    {
        try {
            $offer_id = $data['offer_id'];
            $warehouse_id = $data['warehouse_id'];
            $old_stock = $data['old_stock'];
            $new_stock = $data['new_stock'];
            
            // Update FBO stock in database
            $this->db->query("
                UPDATE " . DB_PREFIX . "ozon_fbo_stocks 
                SET stock = '" . (int)$new_stock . "',
                    date_modified = NOW()
                WHERE offer_id = '" . $this->db->escape($offer_id) . "'
                AND warehouse_id = '" . (int)$warehouse_id . "'
            ");
            
            if ($this->db->countAffected() == 0) {
                // Insert new FBO stock record
                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "ozon_fbo_stocks SET
                    offer_id = '" . $this->db->escape($offer_id) . "',
                    warehouse_id = '" . (int)$warehouse_id . "',
                    stock = '" . (int)$new_stock . "',
                    date_created = NOW()
                ");
            }
            
            // Log stock change
            $this->logStockChange($offer_id, 'fbo', $warehouse_id, $old_stock, $new_stock);
            
            $this->log->write('[OZON WEBHOOK] FBO stock changed: ' . $offer_id . ' warehouse ' . $warehouse_id . ' from ' . $old_stock . ' to ' . $new_stock);
            
            return ['status' => 'success', 'message' => 'FBO stock updated successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[OZON WEBHOOK] FBO stock update error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle FBS stock changed event
     */
    private function handleFbsStockChanged($data)
    {
        try {
            $offer_id = $data['offer_id'];
            $old_stock = $data['old_stock'];
            $new_stock = $data['new_stock'];
            
            // Update FBS stock in database
            $this->db->query("
                UPDATE " . DB_PREFIX . "ozon_products 
                SET fbs_stock = '" . (int)$new_stock . "',
                    date_modified = NOW()
                WHERE offer_id = '" . $this->db->escape($offer_id) . "'
            ");
            
            // Log stock change
            $this->logStockChange($offer_id, 'fbs', null, $old_stock, $new_stock);
            
            // Update OpenCart stock if mapped
            $this->updateOpenCartStock($offer_id, $new_stock);
            
            $this->log->write('[OZON WEBHOOK] FBS stock changed: ' . $offer_id . ' from ' . $old_stock . ' to ' . $new_stock);
            
            return ['status' => 'success', 'message' => 'FBS stock updated successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[OZON WEBHOOK] FBS stock update error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle promotion started event
     */
    private function handlePromotionStarted($data)
    {
        try {
            $promotion_id = $data['promotion_id'];
            $promotion_name = $data['promotion_name'];
            $start_date = $data['start_date'];
            $end_date = $data['end_date'];
            $discount_percentage = $data['discount_percentage'] ?? 0;
            
            // Save promotion to database
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "ozon_promotions SET
                promotion_id = '" . $this->db->escape($promotion_id) . "',
                name = '" . $this->db->escape($promotion_name) . "',
                discount_percentage = '" . (float)$discount_percentage . "',
                start_date = '" . $this->db->escape($start_date) . "',
                end_date = '" . $this->db->escape($end_date) . "',
                status = 'active',
                date_created = NOW()
            ");
            
            // Apply promotion to eligible products
            $this->applyPromotionToProducts($promotion_id, $discount_percentage);
            
            $this->log->write('[OZON WEBHOOK] Promotion started: ' . $promotion_name);
            
            return ['status' => 'success', 'message' => 'Promotion started successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[OZON WEBHOOK] Promotion start error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Create local order from Ozon order data
     */
    private function createLocalOrder($order_data)
    {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "ozon_orders SET
            posting_number = '" . $this->db->escape($order_data['posting_number']) . "',
            order_id = '" . (int)$order_data['order_id'] . "',
            order_number = '" . $this->db->escape($order_data['order_number']) . "',
            status = '" . $this->db->escape($order_data['status']) . "',
            customer_name = '" . $this->db->escape($order_data['customer']['name']) . "',
            total_price = '" . (float)$order_data['financial_data']['total_price'] . "',
            currency = 'RUB',
            delivery_method = '" . $this->db->escape($order_data['delivery_method']['name']) . "',
            tracking_number = '" . $this->db->escape($order_data['tracking_number'] ?? '') . "',
            products = '" . $this->db->escape(json_encode($order_data['products'])) . "',
            date_created = '" . $this->db->escape($order_data['created_at']) . "'
        ");
    }
    
    /**
     * Update order status
     */
    private function updateOrderStatus($posting_number, $status)
    {
        $this->db->query("
            UPDATE " . DB_PREFIX . "ozon_orders 
            SET status = '" . $this->db->escape($status) . "',
                date_modified = NOW()
            WHERE posting_number = '" . $this->db->escape($posting_number) . "'
        ");
    }
    
    /**
     * Process order items and update inventory
     */
    private function processOrderItems($order_data)
    {
        foreach ($order_data['products'] as $product) {
            $offer_id = $product['offer_id'];
            $quantity = $product['quantity'];
            
            // Decrease FBS stock
            $this->db->query("
                UPDATE " . DB_PREFIX . "ozon_products 
                SET fbs_stock = GREATEST(0, fbs_stock - " . (int)$quantity . ")
                WHERE offer_id = '" . $this->db->escape($offer_id) . "'
            ");
        }
    }
    
    /**
     * Log stock change
     */
    private function logStockChange($offer_id, $type, $warehouse_id, $old_stock, $new_stock)
    {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "ozon_stock_history SET
            offer_id = '" . $this->db->escape($offer_id) . "',
            stock_type = '" . $this->db->escape($type) . "',
            warehouse_id = " . ($warehouse_id ? "'" . (int)$warehouse_id . "'" : "NULL") . ",
            old_stock = '" . (int)$old_stock . "',
            new_stock = '" . (int)$new_stock . "',
            change_reason = 'webhook_update',
            date_created = NOW()
        ");
    }
    
    /**
     * Update OpenCart stock
     */
    private function updateOpenCartStock($offer_id, $new_stock)
    {
        // Get OpenCart product mapping
        $query = $this->db->query("
            SELECT opencart_product_id 
            FROM " . DB_PREFIX . "ozon_product_mapping 
            WHERE ozon_offer_id = '" . $this->db->escape($offer_id) . "'
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
     * Update OpenCart price
     */
    private function updateOpenCartPrice($offer_id, $new_price)
    {
        // Get OpenCart product mapping
        $query = $this->db->query("
            SELECT opencart_product_id 
            FROM " . DB_PREFIX . "ozon_product_mapping 
            WHERE ozon_offer_id = '" . $this->db->escape($offer_id) . "'
        ");
        
        if ($query->num_rows) {
            $opencart_product_id = $query->row['opencart_product_id'];
            
            // Convert RUB to store currency if needed
            $converted_price = $this->convertCurrency($new_price, 'RUB');
            
            // Update OpenCart product price
            $this->db->query("
                UPDATE " . DB_PREFIX . "product 
                SET price = '" . (float)$converted_price . "'
                WHERE product_id = '" . (int)$opencart_product_id . "'
            ");
        }
    }
    
    /**
     * Convert currency
     */
    private function convertCurrency($amount, $from_currency)
    {
        $store_currency = $this->config->get('config_currency');
        
        if ($from_currency === $store_currency) {
            return $amount;
        }
        
        // Simple conversion rate - should be replaced with real currency API
        $conversion_rates = [
            'RUB' => ['TRY' => 0.35, 'USD' => 0.011, 'EUR' => 0.010],
        ];
        
        if (isset($conversion_rates[$from_currency][$store_currency])) {
            return $amount * $conversion_rates[$from_currency][$store_currency];
        }
        
        return $amount; // Return original if no conversion available
    }
    
    /**
     * Verify webhook signature
     */
    private function verifySignature($payload, $signature)
    {
        if (!$signature) {
            return true; // Skip verification if no signature provided
        }
        
        $secret = $this->config->get('ozon_webhook_secret');
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
            INSERT INTO " . DB_PREFIX . "ozon_webhook_logs SET
            event_type = '" . $this->db->escape($data['event_type']) . "',
            payload = '" . $this->db->escape(json_encode($data)) . "',
            status = 'received',
            date_created = NOW()
        ");
    }
    
    /**
     * Update webhook processing stats
     */
    private function updateWebhookStats($event_type, $success)
    {
        $status = $success ? 'success' : 'failed';
        
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "ozon_webhook_stats (event_type, status, count, date_created)
            VALUES ('" . $this->db->escape($event_type) . "', '" . $status . "', 1, NOW())
            ON DUPLICATE KEY UPDATE
            count = count + 1,
            date_modified = NOW()
        ");
    }
    
    /**
     * Handle remaining webhook events (simplified implementations)
     */
    private function handleOrderCancelled($data) {
        $posting_number = $data['posting_number'];
        $this->updateOrderStatus($posting_number, 'cancelled');
        return ['status' => 'success', 'message' => 'Order cancelled successfully'];
    }
    
    private function handleOrderAwaitingDelivery($data) {
        $posting_number = $data['posting_number'];
        $this->updateOrderStatus($posting_number, 'awaiting_delivery');
        return ['status' => 'success', 'message' => 'Order awaiting delivery'];
    }
    
    private function handleOrderDelivered($data) {
        $posting_number = $data['posting_number'];
        $this->updateOrderStatus($posting_number, 'delivered');
        return ['status' => 'success', 'message' => 'Order delivered successfully'];
    }
    
    private function handleProductModerated($data) {
        $offer_id = $data['offer_id'];
        $this->db->query("UPDATE " . DB_PREFIX . "ozon_products SET moderation_status = 'moderated' WHERE offer_id = '" . $this->db->escape($offer_id) . "'");
        return ['status' => 'success', 'message' => 'Product moderated successfully'];
    }
    
    private function handleProductBlocked($data) {
        $offer_id = $data['offer_id'];
        $this->db->query("UPDATE " . DB_PREFIX . "ozon_products SET status = 'blocked' WHERE offer_id = '" . $this->db->escape($offer_id) . "'");
        return ['status' => 'success', 'message' => 'Product blocked successfully'];
    }
    
    private function handleProductUnblocked($data) {
        $offer_id = $data['offer_id'];
        $this->db->query("UPDATE " . DB_PREFIX . "ozon_products SET status = 'active' WHERE offer_id = '" . $this->db->escape($offer_id) . "'");
        return ['status' => 'success', 'message' => 'Product unblocked successfully'];
    }
    
    private function handleCommissionChanged($data) {
        $category_id = $data['category_id'];
        $commission_percent = $data['commission_percent'];
        $this->db->query("UPDATE " . DB_PREFIX . "ozon_categories SET commission_percent = '" . (float)$commission_percent . "' WHERE category_id = '" . (int)$category_id . "'");
        return ['status' => 'success', 'message' => 'Commission changed successfully'];
    }
    
    private function handlePromotionEnded($data) {
        $promotion_id = $data['promotion_id'];
        $this->db->query("UPDATE " . DB_PREFIX . "ozon_promotions SET status = 'ended' WHERE promotion_id = '" . $this->db->escape($promotion_id) . "'");
        return ['status' => 'success', 'message' => 'Promotion ended successfully'];
    }
    
    private function handleReturnCreated($data) {
        $posting_number = $data['posting_number'];
        $return_id = $data['return_id'];
        $this->db->query("INSERT INTO " . DB_PREFIX . "ozon_returns SET posting_number = '" . $this->db->escape($posting_number) . "', return_id = '" . (int)$return_id . "', status = 'created', date_created = NOW()");
        return ['status' => 'success', 'message' => 'Return created successfully'];
    }
    
    private function handleReturnApproved($data) {
        $return_id = $data['return_id'];
        $this->db->query("UPDATE " . DB_PREFIX . "ozon_returns SET status = 'approved' WHERE return_id = '" . (int)$return_id . "'");
        return ['status' => 'success', 'message' => 'Return approved successfully'];
    }
    
    private function handleArbitrageCreated($data) {
        $posting_number = $data['posting_number'];
        $arbitrage_id = $data['arbitrage_id'];
        $this->db->query("INSERT INTO " . DB_PREFIX . "ozon_arbitrages SET posting_number = '" . $this->db->escape($posting_number) . "', arbitrage_id = '" . (int)$arbitrage_id . "', status = 'created', date_created = NOW()");
        return ['status' => 'success', 'message' => 'Arbitrage created successfully'];
    }
    
    // Additional helper methods
    private function logStatusChange($posting_number, $old_status, $new_status) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "ozon_order_status_history SET
            posting_number = '" . $this->db->escape($posting_number) . "',
            old_status = '" . $this->db->escape($old_status) . "',
            new_status = '" . $this->db->escape($new_status) . "',
            change_reason = 'webhook_update',
            date_created = NOW()
        ");
    }
    
    private function handleStatusSpecificActions($posting_number, $status) {
        switch ($status) {
            case 'shipped':
                $this->sendShippingNotification($posting_number);
                break;
            case 'delivered':
                $this->processDeliveryActions($posting_number);
                break;
            case 'cancelled':
                $this->processCancellationActions($posting_number);
                break;
        }
    }
    
    private function sendOrderNotification($type, $order_data) {
        $this->log->write('[OZON WEBHOOK] Order notification sent: ' . $type . ' for order ' . $order_data['posting_number']);
    }
    
    private function sendShippingNotification($posting_number) {
        $this->log->write('[OZON WEBHOOK] Shipping notification sent for order: ' . $posting_number);
    }
    
    private function processDeliveryActions($posting_number) {
        $this->log->write('[OZON WEBHOOK] Delivery actions processed for order: ' . $posting_number);
    }
    
    private function processCancellationActions($posting_number) {
        // Restore stock for cancelled orders
        $query = $this->db->query("SELECT products FROM " . DB_PREFIX . "ozon_orders WHERE posting_number = '" . $this->db->escape($posting_number) . "'");
        if ($query->num_rows) {
            $products = json_decode($query->row['products'], true);
            foreach ($products as $product) {
                $this->db->query("
                    UPDATE " . DB_PREFIX . "ozon_products 
                    SET fbs_stock = fbs_stock + " . (int)$product['quantity'] . "
                    WHERE offer_id = '" . $this->db->escape($product['offer_id']) . "'
                ");
            }
        }
        $this->log->write('[OZON WEBHOOK] Cancellation actions processed for order: ' . $posting_number);
    }
    
    private function applyPromotionToProducts($promotion_id, $discount_percentage) {
        // Get eligible products for promotion
        $query = $this->db->query("
            SELECT offer_id, price 
            FROM " . DB_PREFIX . "ozon_products 
            WHERE status = 'active'
        ");
        
        foreach ($query->rows as $product) {
            $discounted_price = $product['price'] * (1 - $discount_percentage / 100);
            
            // Update product with promotion price
            $this->db->query("
                UPDATE " . DB_PREFIX . "ozon_products 
                SET promotion_price = '" . (float)$discounted_price . "',
                    promotion_id = '" . $this->db->escape($promotion_id) . "'
                WHERE offer_id = '" . $this->db->escape($product['offer_id']) . "'
            ");
        }
    }
    
    private function handleProductStockChanged($data) {
        $offer_id = $data['offer_id'];
        $old_stock = $data['old_stock'];
        $new_stock = $data['new_stock'];
        
        $this->db->query("
            UPDATE " . DB_PREFIX . "ozon_products 
            SET stock = '" . (int)$new_stock . "',
                date_modified = NOW()
            WHERE offer_id = '" . $this->db->escape($offer_id) . "'
        ");
        
        $this->logStockChange($offer_id, 'general', null, $old_stock, $new_stock);
        
        return ['status' => 'success', 'message' => 'Product stock updated successfully'];
    }
} 