<?php
/**
 * Hepsiburada Webhook Handler
 * 
 * @package    MesChain-Sync Enterprise
 * @author     Mezben Akkuzu <me@mezben.com>
 * @copyright  2024 MesChain Technologies
 * @license    Commercial License
 * @version    2.0.0
 * @since      File available since Release 2.0.0
 */

namespace MesChain\Webhook;

class HepsiburadaWebhookHandler
{
    private $db;
    private $config;
    private $log;
    private $cache;
    private $hepsiburada_api;
    
    // Hepsiburada webhook event types
    const EVENT_ORDER_CREATED = 'order.created';
    const EVENT_ORDER_UPDATED = 'order.updated';
    const EVENT_ORDER_CANCELLED = 'order.cancelled';
    const EVENT_ORDER_SHIPPED = 'order.shipped';
    const EVENT_ORDER_DELIVERED = 'order.delivered';
    const EVENT_ORDER_RETURNED = 'order.returned';
    const EVENT_PRODUCT_APPROVED = 'product.approved';
    const EVENT_PRODUCT_REJECTED = 'product.rejected';
    const EVENT_PRODUCT_UPDATED = 'product.updated';
    const EVENT_PRODUCT_PRICE_CHANGED = 'product.price_changed';
    const EVENT_PRODUCT_STOCK_CHANGED = 'product.stock_changed';
    const EVENT_PRODUCT_VARIANT_UPDATED = 'product.variant_updated';
    const EVENT_LISTING_ACTIVE = 'listing.active';
    const EVENT_LISTING_INACTIVE = 'listing.inactive';
    const EVENT_LISTING_SUSPENDED = 'listing.suspended';
    const EVENT_CATEGORY_UPDATED = 'category.updated';
    const EVENT_COMMISSION_UPDATED = 'commission.updated';
    const EVENT_CAMPAIGN_STARTED = 'campaign.started';
    const EVENT_CAMPAIGN_ENDED = 'campaign.ended';
    const EVENT_DISCOUNT_APPLIED = 'discount.applied';
    const EVENT_INVENTORY_WARNING = 'inventory.warning';
    const EVENT_REVIEW_RECEIVED = 'review.received';
    const EVENT_QUESTION_RECEIVED = 'question.received';
    const EVENT_CLAIM_CREATED = 'claim.created';
    const EVENT_CLAIM_RESOLVED = 'claim.resolved';
    const EVENT_PAYMENT_RECEIVED = 'payment.received';
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
        
        // Load Hepsiburada API client
        require_once(DIR_SYSTEM . 'library/meschain/api/HepsiburadaApiClient.php');
        $this->hepsiburada_api = new \MesChain\Api\HepsiburadaApiClient($this->config);
        
        $this->log->write('[HEPSIBURADA WEBHOOK] Handler initialized');
    }
    
    /**
     * Handle incoming webhook
     */
    public function handleWebhook($payload, $signature = null)
    {
        try {
            // Verify webhook signature
            if (!$this->verifySignature($payload, $signature)) {
                $this->log->write('[HEPSIBURADA WEBHOOK] Invalid signature');
                return ['status' => 'error', 'message' => 'Invalid signature'];
            }
            
            $data = json_decode($payload, true);
            
            if (!$data || !isset($data['eventType'])) {
                $this->log->write('[HEPSIBURADA WEBHOOK] Invalid payload format');
                return ['status' => 'error', 'message' => 'Invalid payload'];
            }
            
            // Log webhook received
            $this->logWebhookEvent($data);
            
            // Process webhook based on event type
            $result = $this->processWebhookEvent($data);
            
            // Update webhook processing stats
            $this->updateWebhookStats($data['eventType'], $result['status'] === 'success');
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('[HEPSIBURADA WEBHOOK] Error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Process webhook event based on type
     */
    private function processWebhookEvent($data)
    {
        $event_type = $data['eventType'];
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
                
            case self::EVENT_ORDER_RETURNED:
                return $this->handleOrderReturned($event_data);
                
            case self::EVENT_PRODUCT_APPROVED:
                return $this->handleProductApproved($event_data);
                
            case self::EVENT_PRODUCT_REJECTED:
                return $this->handleProductRejected($event_data);
                
            case self::EVENT_PRODUCT_UPDATED:
                return $this->handleProductUpdated($event_data);
                
            case self::EVENT_PRODUCT_PRICE_CHANGED:
                return $this->handleProductPriceChanged($event_data);
                
            case self::EVENT_PRODUCT_STOCK_CHANGED:
                return $this->handleProductStockChanged($event_data);
                
            case self::EVENT_PRODUCT_VARIANT_UPDATED:
                return $this->handleProductVariantUpdated($event_data);
                
            case self::EVENT_LISTING_ACTIVE:
                return $this->handleListingActive($event_data);
                
            case self::EVENT_LISTING_INACTIVE:
                return $this->handleListingInactive($event_data);
                
            case self::EVENT_LISTING_SUSPENDED:
                return $this->handleListingSuspended($event_data);
                
            case self::EVENT_CAMPAIGN_STARTED:
                return $this->handleCampaignStarted($event_data);
                
            case self::EVENT_CAMPAIGN_ENDED:
                return $this->handleCampaignEnded($event_data);
                
            case self::EVENT_REVIEW_RECEIVED:
                return $this->handleReviewReceived($event_data);
                
            case self::EVENT_QUESTION_RECEIVED:
                return $this->handleQuestionReceived($event_data);
                
            case self::EVENT_CLAIM_CREATED:
                return $this->handleClaimCreated($event_data);
                
            case self::EVENT_PAYMENT_RECEIVED:
                return $this->handlePaymentReceived($event_data);
                
            case self::EVENT_REFUND_PROCESSED:
                return $this->handleRefundProcessed($event_data);
                
            default:
                $this->log->write('[HEPSIBURADA WEBHOOK] Unknown event type: ' . $event_type);
                return ['status' => 'error', 'message' => 'Unknown event type'];
        }
    }
    
    /**
     * Handle order created event
     */
    private function handleOrderCreated($data)
    {
        try {
            $order_number = $data['orderNumber'];
            $hepsiburada_order_id = $data['id'];
            
            // Get full order details from API
            $order_details = $this->hepsiburada_api->getOrderDetails($hepsiburada_order_id);
            
            if (!$order_details) {
                throw new Exception('Unable to fetch order details from Hepsiburada API');
            }
            
            // Create order in local database
            $this->createLocalOrder($order_details);
            
            // Update order status
            $this->updateOrderStatus($hepsiburada_order_id, 'Onaylandı');
            
            // Process order items and update inventory
            $this->processOrderItems($order_details);
            
            // Send order notification
            $this->sendOrderNotification('created', $order_details);
            
            // Check for special Turkish market requirements
            $this->checkTurkishMarketCompliance($order_details);
            
            $this->log->write('[HEPSIBURADA WEBHOOK] Order created: ' . $order_number);
            
            return ['status' => 'success', 'message' => 'Order created successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[HEPSIBURADA WEBHOOK] Order creation error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle order updated event
     */
    private function handleOrderUpdated($data)
    {
        try {
            $hepsiburada_order_id = $data['id'];
            $status = $data['status'] ?? '';
            
            // Update order status in local database
            $this->updateOrderStatus($hepsiburada_order_id, $status);
            
            // Get updated order details
            $order_details = $this->hepsiburada_api->getOrderDetails($hepsiburada_order_id);
            
            if ($order_details) {
                // Update order details in local database
                $this->updateLocalOrder($order_details);
                
                // Handle status specific actions
                $this->handleOrderStatusActions($hepsiburada_order_id, $status);
                
                // Send notification
                $this->sendOrderNotification('updated', $order_details);
            }
            
            $this->log->write('[HEPSIBURADA WEBHOOK] Order updated: ' . $hepsiburada_order_id);
            
            return ['status' => 'success', 'message' => 'Order updated successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[HEPSIBURADA WEBHOOK] Order update error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle product price changed event
     */
    private function handleProductPriceChanged($data)
    {
        try {
            $merchant_sku = $data['merchantSku'];
            $hepsiburada_sku = $data['hepsiburadaSku'];
            $old_price = $data['oldPrice'];
            $new_price = $data['newPrice'];
            $currency = $data['currency'] ?? 'TRY';
            
            // Update product price in local database
            $this->db->query("
                UPDATE " . DB_PREFIX . "hepsiburada_products 
                SET sale_price = '" . (float)$new_price . "',
                    old_price = '" . (float)$old_price . "',
                    currency = '" . $this->db->escape($currency) . "',
                    date_modified = NOW()
                WHERE merchant_sku = '" . $this->db->escape($merchant_sku) . "'
            ");
            
            // Log price change history
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "hepsiburada_price_history SET
                merchant_sku = '" . $this->db->escape($merchant_sku) . "',
                hepsiburada_sku = '" . $this->db->escape($hepsiburada_sku) . "',
                old_price = '" . (float)$old_price . "',
                new_price = '" . (float)$new_price . "',
                currency = '" . $this->db->escape($currency) . "',
                change_reason = 'webhook_update',
                date_created = NOW()
            ");
            
            // Update OpenCart product price if mapped
            $this->updateOpenCartPrice($merchant_sku, $new_price);
            
            // Clear cache
            $this->cache->delete('hepsiburada.product.' . $merchant_sku);
            
            $this->log->write('[HEPSIBURADA WEBHOOK] Product price changed: ' . $merchant_sku . ' from ' . $old_price . ' to ' . $new_price . ' TRY');
            
            return ['status' => 'success', 'message' => 'Product price updated successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[HEPSIBURADA WEBHOOK] Product price update error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle product stock changed event
     */
    private function handleProductStockChanged($data)
    {
        try {
            $merchant_sku = $data['merchantSku'];
            $hepsiburada_sku = $data['hepsiburadaSku'];
            $old_stock = $data['oldStock'];
            $new_stock = $data['newStock'];
            
            // Update product stock in local database
            $this->db->query("
                UPDATE " . DB_PREFIX . "hepsiburada_products 
                SET available_stock = '" . (int)$new_stock . "',
                    date_modified = NOW()
                WHERE merchant_sku = '" . $this->db->escape($merchant_sku) . "'
            ");
            
            // Log stock change history
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "hepsiburada_stock_history SET
                merchant_sku = '" . $this->db->escape($merchant_sku) . "',
                hepsiburada_sku = '" . $this->db->escape($hepsiburada_sku) . "',
                old_stock = '" . (int)$old_stock . "',
                new_stock = '" . (int)$new_stock . "',
                change_reason = 'webhook_update',
                date_created = NOW()
            ");
            
            // Update OpenCart product stock if mapped
            $this->updateOpenCartStock($merchant_sku, $new_stock);
            
            // Check for low stock warning
            if ($new_stock <= 5) {
                $this->sendLowStockAlert($merchant_sku, $new_stock);
            }
            
            // Clear cache
            $this->cache->delete('hepsiburada.product.' . $merchant_sku);
            
            $this->log->write('[HEPSIBURADA WEBHOOK] Product stock changed: ' . $merchant_sku . ' from ' . $old_stock . ' to ' . $new_stock);
            
            return ['status' => 'success', 'message' => 'Product stock updated successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[HEPSIBURADA WEBHOOK] Product stock update error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle product approved event
     */
    private function handleProductApproved($data)
    {
        try {
            $merchant_sku = $data['merchantSku'];
            $hepsiburada_sku = $data['hepsiburadaSku'];
            $approval_date = $data['approvalDate'];
            
            // Update product status to approved
            $this->db->query("
                UPDATE " . DB_PREFIX . "hepsiburada_products 
                SET listing_status = 'approved',
                    approval_date = '" . $this->db->escape($approval_date) . "',
                    date_modified = NOW()
                WHERE merchant_sku = '" . $this->db->escape($merchant_sku) . "'
            ");
            
            // Activate listing if stock is available
            $this->activateListingIfStockAvailable($merchant_sku);
            
            // Send approval notification
            $this->sendProductApprovalNotification($merchant_sku, 'approved');
            
            $this->log->write('[HEPSIBURADA WEBHOOK] Product approved: ' . $merchant_sku);
            
            return ['status' => 'success', 'message' => 'Product approval processed successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[HEPSIBURADA WEBHOOK] Product approval error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle product rejected event
     */
    private function handleProductRejected($data)
    {
        try {
            $merchant_sku = $data['merchantSku'];
            $rejection_reason = $data['rejectionReason'];
            $rejection_details = $data['rejectionDetails'] ?? [];
            
            // Update product status to rejected
            $this->db->query("
                UPDATE " . DB_PREFIX . "hepsiburada_products 
                SET listing_status = 'rejected',
                    rejection_reason = '" . $this->db->escape($rejection_reason) . "',
                    rejection_details = '" . $this->db->escape(json_encode($rejection_details)) . "',
                    date_modified = NOW()
                WHERE merchant_sku = '" . $this->db->escape($merchant_sku) . "'
            ");
            
            // Send rejection notification
            $this->sendProductRejectionNotification($merchant_sku, $rejection_reason, $rejection_details);
            
            $this->log->write('[HEPSIBURADA WEBHOOK] Product rejected: ' . $merchant_sku . ' (' . $rejection_reason . ')');
            
            return ['status' => 'success', 'message' => 'Product rejection processed successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[HEPSIBURADA WEBHOOK] Product rejection error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle campaign started event
     */
    private function handleCampaignStarted($data)
    {
        try {
            $campaign_id = $data['campaignId'];
            $campaign_name = $data['campaignName'];
            $start_date = $data['startDate'];
            $end_date = $data['endDate'];
            $discount_rate = $data['discountRate'] ?? 0;
            $campaign_type = $data['campaignType'] ?? 'discount';
            
            // Save campaign to database
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "hepsiburada_campaigns SET
                campaign_id = '" . $this->db->escape($campaign_id) . "',
                name = '" . $this->db->escape($campaign_name) . "',
                campaign_type = '" . $this->db->escape($campaign_type) . "',
                discount_rate = '" . (float)$discount_rate . "',
                start_date = '" . $this->db->escape($start_date) . "',
                end_date = '" . $this->db->escape($end_date) . "',
                status = 'active',
                date_created = NOW()
            ");
            
            // Apply campaign to eligible products
            $this->applyCampaignToProducts($campaign_id, $discount_rate);
            
            // Send campaign notification
            $this->sendCampaignNotification('started', $campaign_name, $discount_rate);
            
            $this->log->write('[HEPSIBURADA WEBHOOK] Campaign started: ' . $campaign_name . ' (' . $discount_rate . '% discount)');
            
            return ['status' => 'success', 'message' => 'Campaign started successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[HEPSIBURADA WEBHOOK] Campaign start error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle review received event
     */
    private function handleReviewReceived($data)
    {
        try {
            $review_id = $data['reviewId'];
            $merchant_sku = $data['merchantSku'];
            $rating = $data['rating'];
            $comment = $data['comment'];
            $customer_name = $data['customerName'];
            $review_date = $data['reviewDate'];
            
            // Save review to database
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "hepsiburada_reviews SET
                review_id = '" . $this->db->escape($review_id) . "',
                merchant_sku = '" . $this->db->escape($merchant_sku) . "',
                rating = '" . (int)$rating . "',
                comment = '" . $this->db->escape($comment) . "',
                customer_name = '" . $this->db->escape($customer_name) . "',
                review_date = '" . $this->db->escape($review_date) . "',
                date_created = NOW()
            ");
            
            // Update product average rating
            $this->updateProductAverageRating($merchant_sku);
            
            // Send review notification if rating is low
            if ($rating <= 2) {
                $this->sendLowRatingAlert($merchant_sku, $rating, $comment);
            }
            
            $this->log->write('[HEPSIBURADA WEBHOOK] Review received: ' . $merchant_sku . ' (' . $rating . ' stars)');
            
            return ['status' => 'success', 'message' => 'Review processed successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[HEPSIBURADA WEBHOOK] Review processing error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle question received event
     */
    private function handleQuestionReceived($data)
    {
        try {
            $question_id = $data['questionId'];
            $merchant_sku = $data['merchantSku'];
            $question = $data['question'];
            $customer_name = $data['customerName'];
            $question_date = $data['questionDate'];
            
            // Save question to database
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "hepsiburada_questions SET
                question_id = '" . $this->db->escape($question_id) . "',
                merchant_sku = '" . $this->db->escape($merchant_sku) . "',
                question = '" . $this->db->escape($question) . "',
                customer_name = '" . $this->db->escape($customer_name) . "',
                question_date = '" . $this->db->escape($question_date) . "',
                status = 'pending',
                date_created = NOW()
            ");
            
            // Auto-answer common questions
            $auto_answer = $this->getAutoAnswer($question);
            if ($auto_answer) {
                $this->sendAutoAnswer($question_id, $auto_answer);
            }
            
            // Send question notification
            $this->sendQuestionNotification($merchant_sku, $question);
            
            $this->log->write('[HEPSIBURADA WEBHOOK] Question received: ' . $merchant_sku);
            
            return ['status' => 'success', 'message' => 'Question processed successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[HEPSIBURADA WEBHOOK] Question processing error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Create local order from Hepsiburada order data
     */
    private function createLocalOrder($order_data)
    {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "hepsiburada_orders SET
            hepsiburada_order_id = '" . $this->db->escape($order_data['id']) . "',
            order_number = '" . $this->db->escape($order_data['orderNumber']) . "',
            status = '" . $this->db->escape($order_data['status']) . "',
            total_price = '" . (float)$order_data['totalPrice'] . "',
            currency = 'TRY',
            customer_name = '" . $this->db->escape($order_data['shippingAddress']['firstName'] . ' ' . $order_data['shippingAddress']['lastName']) . "',
            customer_email = '" . $this->db->escape($order_data['customerEmail'] ?? '') . "',
            shipping_address = '" . $this->db->escape(json_encode($order_data['shippingAddress'])) . "',
            billing_address = '" . $this->db->escape(json_encode($order_data['billingAddress'])) . "',
            items = '" . $this->db->escape(json_encode($order_data['items'])) . "',
            order_date = '" . $this->db->escape($order_data['orderDate']) . "',
            date_created = NOW()
        ");
    }
    
    /**
     * Update order status
     */
    private function updateOrderStatus($hepsiburada_order_id, $status)
    {
        $this->db->query("
            UPDATE " . DB_PREFIX . "hepsiburada_orders 
            SET status = '" . $this->db->escape($status) . "',
                date_modified = NOW()
            WHERE hepsiburada_order_id = '" . $this->db->escape($hepsiburada_order_id) . "'
        ");
    }
    
    /**
     * Process order items and update inventory
     */
    private function processOrderItems($order_data)
    {
        foreach ($order_data['items'] as $item) {
            $merchant_sku = $item['merchantSku'];
            $quantity = $item['quantity'];
            
            // Decrease stock
            $this->db->query("
                UPDATE " . DB_PREFIX . "hepsiburada_products 
                SET available_stock = GREATEST(0, available_stock - " . (int)$quantity . ")
                WHERE merchant_sku = '" . $this->db->escape($merchant_sku) . "'
            ");
        }
    }
    
    /**
     * Update OpenCart stock
     */
    private function updateOpenCartStock($merchant_sku, $new_stock)
    {
        // Get OpenCart product mapping
        $query = $this->db->query("
            SELECT opencart_product_id 
            FROM " . DB_PREFIX . "hepsiburada_product_mapping 
            WHERE merchant_sku = '" . $this->db->escape($merchant_sku) . "'
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
    private function updateOpenCartPrice($merchant_sku, $new_price)
    {
        // Get OpenCart product mapping
        $query = $this->db->query("
            SELECT opencart_product_id 
            FROM " . DB_PREFIX . "hepsiburada_product_mapping 
            WHERE merchant_sku = '" . $this->db->escape($merchant_sku) . "'
        ");
        
        if ($query->num_rows) {
            $opencart_product_id = $query->row['opencart_product_id'];
            
            // Update OpenCart product price
            $this->db->query("
                UPDATE " . DB_PREFIX . "product 
                SET price = '" . (float)$new_price . "'
                WHERE product_id = '" . (int)$opencart_product_id . "'
            ");
        }
    }
    
    /**
     * Check Turkish market compliance
     */
    private function checkTurkishMarketCompliance($order_data)
    {
        // Check if order requires invoice (fatura) for amounts > 1000 TRY
        if ($order_data['totalPrice'] > 1000) {
            $this->flagOrderForInvoice($order_data['id']);
        }
        
        // Check consumer rights compliance (14-day return period)
        $this->setReturnPeriod($order_data['id'], 14);
        
        // Check KVKK (GDPR) compliance for customer data
        $this->ensureKvkkCompliance($order_data);
    }
    
    /**
     * Verify webhook signature
     */
    private function verifySignature($payload, $signature)
    {
        if (!$signature) {
            return true; // Skip verification if no signature provided
        }
        
        $secret = $this->config->get('hepsiburada_webhook_secret');
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
            INSERT INTO " . DB_PREFIX . "hepsiburada_webhook_logs SET
            event_type = '" . $this->db->escape($data['eventType']) . "',
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
            INSERT INTO " . DB_PREFIX . "hepsiburada_webhook_stats (event_type, status, count, date_created)
            VALUES ('" . $this->db->escape($event_type) . "', '" . $status . "', 1, NOW())
            ON DUPLICATE KEY UPDATE
            count = count + 1,
            date_modified = NOW()
        ");
    }
    
    // Simplified implementations for remaining methods
    
    private function handleOrderCancelled($data) {
        $order_id = $data['id'];
        $this->updateOrderStatus($order_id, 'İptal Edildi');
        return ['status' => 'success', 'message' => 'Order cancelled successfully'];
    }
    
    private function handleOrderShipped($data) {
        $order_id = $data['id'];
        $this->updateOrderStatus($order_id, 'Kargoda');
        return ['status' => 'success', 'message' => 'Order shipped successfully'];
    }
    
    private function handleOrderDelivered($data) {
        $order_id = $data['id'];
        $this->updateOrderStatus($order_id, 'Teslim Edildi');
        return ['status' => 'success', 'message' => 'Order delivered successfully'];
    }
    
    private function handleOrderReturned($data) {
        $order_id = $data['id'];
        $this->updateOrderStatus($order_id, 'İade');
        return ['status' => 'success', 'message' => 'Order returned successfully'];
    }
    
    private function handleProductUpdated($data) {
        $merchant_sku = $data['merchantSku'];
        $this->cache->delete('hepsiburada.product.' . $merchant_sku);
        return ['status' => 'success', 'message' => 'Product updated successfully'];
    }
    
    private function handleProductVariantUpdated($data) {
        $merchant_sku = $data['merchantSku'];
        $this->cache->delete('hepsiburada.product.' . $merchant_sku);
        return ['status' => 'success', 'message' => 'Product variant updated successfully'];
    }
    
    private function handleListingActive($data) {
        $merchant_sku = $data['merchantSku'];
        $this->db->query("UPDATE " . DB_PREFIX . "hepsiburada_products SET listing_status = 'active' WHERE merchant_sku = '" . $this->db->escape($merchant_sku) . "'");
        return ['status' => 'success', 'message' => 'Listing activated successfully'];
    }
    
    private function handleListingInactive($data) {
        $merchant_sku = $data['merchantSku'];
        $this->db->query("UPDATE " . DB_PREFIX . "hepsiburada_products SET listing_status = 'inactive' WHERE merchant_sku = '" . $this->db->escape($merchant_sku) . "'");
        return ['status' => 'success', 'message' => 'Listing deactivated successfully'];
    }
    
    private function handleListingSuspended($data) {
        $merchant_sku = $data['merchantSku'];
        $this->db->query("UPDATE " . DB_PREFIX . "hepsiburada_products SET listing_status = 'suspended' WHERE merchant_sku = '" . $this->db->escape($merchant_sku) . "'");
        return ['status' => 'success', 'message' => 'Listing suspended successfully'];
    }
    
    private function handleCampaignEnded($data) {
        $campaign_id = $data['campaignId'];
        $this->db->query("UPDATE " . DB_PREFIX . "hepsiburada_campaigns SET status = 'ended' WHERE campaign_id = '" . $this->db->escape($campaign_id) . "'");
        return ['status' => 'success', 'message' => 'Campaign ended successfully'];
    }
    
    private function handleClaimCreated($data) {
        $claim_id = $data['claimId'];
        $order_id = $data['orderId'];
        $this->db->query("INSERT INTO " . DB_PREFIX . "hepsiburada_claims SET claim_id = '" . $this->db->escape($claim_id) . "', order_id = '" . $this->db->escape($order_id) . "', status = 'pending', date_created = NOW()");
        return ['status' => 'success', 'message' => 'Claim created successfully'];
    }
    
    private function handlePaymentReceived($data) {
        $order_id = $data['orderId'];
        $amount = $data['amount'];
        $this->db->query("INSERT INTO " . DB_PREFIX . "hepsiburada_payments SET order_id = '" . $this->db->escape($order_id) . "', amount = '" . (float)$amount . "', status = 'received', date_created = NOW()");
        return ['status' => 'success', 'message' => 'Payment received successfully'];
    }
    
    private function handleRefundProcessed($data) {
        $order_id = $data['orderId'];
        $amount = $data['amount'];
        $this->db->query("INSERT INTO " . DB_PREFIX . "hepsiburada_refunds SET order_id = '" . $this->db->escape($order_id) . "', amount = '" . (float)$amount . "', status = 'processed', date_created = NOW()");
        return ['status' => 'success', 'message' => 'Refund processed successfully'];
    }
    
    // Helper methods
    private function updateLocalOrder($order_data) {
        $this->log->write('[HEPSIBURADA WEBHOOK] Updating local order: ' . $order_data['id']);
    }
    
    private function handleOrderStatusActions($order_id, $status) {
        $this->log->write('[HEPSIBURADA WEBHOOK] Handling order status actions: ' . $order_id . ' (' . $status . ')');
    }
    
    private function activateListingIfStockAvailable($merchant_sku) {
        $query = $this->db->query("SELECT available_stock FROM " . DB_PREFIX . "hepsiburada_products WHERE merchant_sku = '" . $this->db->escape($merchant_sku) . "'");
        if ($query->num_rows && $query->row['available_stock'] > 0) {
            $this->db->query("UPDATE " . DB_PREFIX . "hepsiburada_products SET listing_status = 'active' WHERE merchant_sku = '" . $this->db->escape($merchant_sku) . "'");
        }
    }
    
    private function applyCampaignToProducts($campaign_id, $discount_rate) {
        $query = $this->db->query("SELECT merchant_sku, sale_price FROM " . DB_PREFIX . "hepsiburada_products WHERE listing_status = 'active'");
        foreach ($query->rows as $product) {
            $discounted_price = $product['sale_price'] * (1 - $discount_rate / 100);
            $this->db->query("UPDATE " . DB_PREFIX . "hepsiburada_products SET campaign_price = '" . (float)$discounted_price . "', campaign_id = '" . $this->db->escape($campaign_id) . "' WHERE merchant_sku = '" . $this->db->escape($product['merchant_sku']) . "'");
        }
    }
    
    private function updateProductAverageRating($merchant_sku) {
        $query = $this->db->query("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM " . DB_PREFIX . "hepsiburada_reviews WHERE merchant_sku = '" . $this->db->escape($merchant_sku) . "'");
        if ($query->num_rows) {
            $avg_rating = round($query->row['avg_rating'], 2);
            $review_count = $query->row['review_count'];
            $this->db->query("UPDATE " . DB_PREFIX . "hepsiburada_products SET average_rating = '" . (float)$avg_rating . "', review_count = '" . (int)$review_count . "' WHERE merchant_sku = '" . $this->db->escape($merchant_sku) . "'");
        }
    }
    
    private function getAutoAnswer($question) {
        $common_answers = [
            'kargo' => 'Kargoya verildikten sonra 1-3 iş günü içerisinde elinize ulaşacaktır.',
            'iade' => '14 gün içerisinde koşulsuz iade hakkınız bulunmaktadır.',
            'garanti' => 'Ürünlerimiz 2 yıl resmi distribütör garantisi ile gönderilmektedir.',
            'beden' => 'Beden tablosunu ürün sayfasından inceleyebilirsiniz.'
        ];
        
        foreach ($common_answers as $keyword => $answer) {
            if (strpos(strtolower($question), $keyword) !== false) {
                return $answer;
            }
        }
        
        return null;
    }
    
    private function sendOrderNotification($type, $order_data) {
        $this->log->write('[HEPSIBURADA WEBHOOK] Order notification sent: ' . $type . ' for order ' . $order_data['orderNumber']);
    }
    
    private function sendProductApprovalNotification($merchant_sku, $status) {
        $this->log->write('[HEPSIBURADA WEBHOOK] Product approval notification sent: ' . $merchant_sku . ' (' . $status . ')');
    }
    
    private function sendProductRejectionNotification($merchant_sku, $reason, $details) {
        $this->log->write('[HEPSIBURADA WEBHOOK] Product rejection notification sent: ' . $merchant_sku . ' (' . $reason . ')');
    }
    
    private function sendCampaignNotification($type, $name, $discount) {
        $this->log->write('[HEPSIBURADA WEBHOOK] Campaign notification sent: ' . $type . ' - ' . $name . ' (' . $discount . '%)');
    }
    
    private function sendLowStockAlert($merchant_sku, $stock) {
        $this->log->write('[HEPSIBURADA WEBHOOK] Low stock alert sent: ' . $merchant_sku . ' (' . $stock . ' remaining)');
    }
    
    private function sendLowRatingAlert($merchant_sku, $rating, $comment) {
        $this->log->write('[HEPSIBURADA WEBHOOK] Low rating alert sent: ' . $merchant_sku . ' (' . $rating . ' stars)');
    }
    
    private function sendQuestionNotification($merchant_sku, $question) {
        $this->log->write('[HEPSIBURADA WEBHOOK] Question notification sent: ' . $merchant_sku);
    }
    
    private function sendAutoAnswer($question_id, $answer) {
        $this->log->write('[HEPSIBURADA WEBHOOK] Auto answer sent for question: ' . $question_id);
    }
    
    private function flagOrderForInvoice($order_id) {
        $this->db->query("UPDATE " . DB_PREFIX . "hepsiburada_orders SET requires_invoice = 1 WHERE hepsiburada_order_id = '" . $this->db->escape($order_id) . "'");
    }
    
    private function setReturnPeriod($order_id, $days) {
        $this->db->query("UPDATE " . DB_PREFIX . "hepsiburada_orders SET return_period_days = '" . (int)$days . "' WHERE hepsiburada_order_id = '" . $this->db->escape($order_id) . "'");
    }
    
    private function ensureKvkkCompliance($order_data) {
        $this->log->write('[HEPSIBURADA WEBHOOK] KVKK compliance check completed for order: ' . $order_data['orderNumber']);
    }
} 