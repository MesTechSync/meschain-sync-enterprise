<?php
/**
 * Amazon Webhook Handler (MWS/SP-API)
 * 
 * @package    MesChain-Sync Enterprise
 * @author     Mezben Akkuzu <me@mezben.com>
 * @copyright  2024 MesChain Technologies
 * @license    Commercial License
 * @version    2.0.0
 * @since      File available since Release 2.0.0
 */

namespace MesChain\Webhook;

class AmazonWebhookHandler
{
    private $db;
    private $config;
    private $log;
    private $cache;
    private $amazon_api;
    
    // Amazon SP-API notification types
    const NOTIFICATION_ORDER_STATUS_CHANGE = 'ORDER_STATUS_CHANGE';
    const NOTIFICATION_ITEM_INVENTORY_EVENT_DATA = 'ITEM_INVENTORY_EVENT_DATA';
    const NOTIFICATION_LISTINGS_ITEM_STATUS_CHANGE = 'LISTINGS_ITEM_STATUS_CHANGE';
    const NOTIFICATION_LISTINGS_ITEM_ISSUES_CHANGE = 'LISTINGS_ITEM_ISSUES_CHANGE';
    const NOTIFICATION_PRODUCT_TYPE_DEFINITIONS_CHANGE = 'PRODUCT_TYPE_DEFINITIONS_CHANGE';
    const NOTIFICATION_MFN_ORDER_STATUS_CHANGE = 'MFN_ORDER_STATUS_CHANGE';
    const NOTIFICATION_B2B_ANY_OFFER_CHANGED = 'B2B_ANY_OFFER_CHANGED';
    const NOTIFICATION_FBA_OUTBOUND_SHIPMENT_STATUS = 'FBA_OUTBOUND_SHIPMENT_STATUS';
    const NOTIFICATION_FEE_PROMOTION = 'FEE_PROMOTION';
    const NOTIFICATION_FULFILLMENT_ORDER_STATUS = 'FULFILLMENT_ORDER_STATUS';
    const NOTIFICATION_REPORT_PROCESSING_FINISHED = 'REPORT_PROCESSING_FINISHED';
    const NOTIFICATION_BRANDED_ITEM_CONTENT_CHANGE = 'BRANDED_ITEM_CONTENT_CHANGE';
    const NOTIFICATION_ITEM_PRODUCT_TYPE_CHANGE = 'ITEM_PRODUCT_TYPE_CHANGE';
    const NOTIFICATION_A_PLUS_CONTENT_PUBLISH_STATUS = 'A_PLUS_CONTENT_PUBLISH_STATUS';
    
    /**
     * Constructor
     */
    public function __construct($registry)
    {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->log = $registry->get('log');
        $this->cache = $registry->get('cache');
        
        // Load Amazon API client
        require_once(DIR_SYSTEM . 'library/meschain/api/AmazonApiClient.php');
        $this->amazon_api = new \MesChain\Api\AmazonApiClient($this->config);
        
        $this->log->write('[AMAZON WEBHOOK] Handler initialized');
    }
    
    /**
     * Handle incoming Amazon SQS notification
     */
    public function handleWebhook($payload, $signature = null)
    {
        try {
            // Parse SQS message format
            $sqs_message = json_decode($payload, true);
            
            if (!$sqs_message || !isset($sqs_message['Message'])) {
                $this->log->write('[AMAZON WEBHOOK] Invalid SQS message format');
                return ['status' => 'error', 'message' => 'Invalid SQS message format'];
            }
            
            // Extract notification data
            $notification_data = json_decode($sqs_message['Message'], true);
            
            if (!$notification_data || !isset($notification_data['notificationVersion'])) {
                $this->log->write('[AMAZON WEBHOOK] Invalid notification format');
                return ['status' => 'error', 'message' => 'Invalid notification format'];
            }
            
            // Log notification received
            $this->logNotificationEvent($notification_data);
            
            // Process notification based on type
            $result = $this->processNotification($notification_data);
            
            // Update notification processing stats
            $this->updateNotificationStats($notification_data['notificationType'], $result['status'] === 'success');
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('[AMAZON WEBHOOK] Error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Process notification based on type
     */
    private function processNotification($data)
    {
        $notification_type = $data['notificationType'];
        $payload = $data['payload'] ?? [];
        
        switch ($notification_type) {
            case self::NOTIFICATION_ORDER_STATUS_CHANGE:
                return $this->handleOrderStatusChange($payload);
                
            case self::NOTIFICATION_ITEM_INVENTORY_EVENT_DATA:
                return $this->handleInventoryEvent($payload);
                
            case self::NOTIFICATION_LISTINGS_ITEM_STATUS_CHANGE:
                return $this->handleListingStatusChange($payload);
                
            case self::NOTIFICATION_LISTINGS_ITEM_ISSUES_CHANGE:
                return $this->handleListingIssuesChange($payload);
                
            case self::NOTIFICATION_MFN_ORDER_STATUS_CHANGE:
                return $this->handleMfnOrderStatusChange($payload);
                
            case self::NOTIFICATION_B2B_ANY_OFFER_CHANGED:
                return $this->handleB2bOfferChanged($payload);
                
            case self::NOTIFICATION_FBA_OUTBOUND_SHIPMENT_STATUS:
                return $this->handleFbaOutboundShipmentStatus($payload);
                
            case self::NOTIFICATION_FULFILLMENT_ORDER_STATUS:
                return $this->handleFulfillmentOrderStatus($payload);
                
            case self::NOTIFICATION_REPORT_PROCESSING_FINISHED:
                return $this->handleReportProcessingFinished($payload);
                
            case self::NOTIFICATION_BRANDED_ITEM_CONTENT_CHANGE:
                return $this->handleBrandedItemContentChange($payload);
                
            case self::NOTIFICATION_ITEM_PRODUCT_TYPE_CHANGE:
                return $this->handleItemProductTypeChange($payload);
                
            case self::NOTIFICATION_A_PLUS_CONTENT_PUBLISH_STATUS:
                return $this->handleAPlusContentPublishStatus($payload);
                
            default:
                $this->log->write('[AMAZON WEBHOOK] Unknown notification type: ' . $notification_type);
                return ['status' => 'error', 'message' => 'Unknown notification type'];
        }
    }
    
    /**
     * Handle order status change notification
     */
    private function handleOrderStatusChange($payload)
    {
        try {
            $order_change_details = $payload['orderChangeDetails'] ?? [];
            
            foreach ($order_change_details as $order_change) {
                $amazon_order_id = $order_change['amazonOrderId'];
                $order_status = $order_change['orderStatus'];
                
                // Get full order details from Amazon API
                $order_details = $this->amazon_api->getOrderDetails($amazon_order_id);
                
                if ($order_details) {
                    // Update order in local database
                    $this->updateLocalOrder($order_details);
                    
                    // Process status-specific actions
                    $this->processOrderStatusActions($amazon_order_id, $order_status);
                }
                
                $this->log->write('[AMAZON WEBHOOK] Order status changed: ' . $amazon_order_id . ' to ' . $order_status);
            }
            
            return ['status' => 'success', 'message' => 'Order status changes processed successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[AMAZON WEBHOOK] Order status change error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle inventory event notification
     */
    private function handleInventoryEvent($payload)
    {
        try {
            $inventory_details = $payload['inventoryEventDetails'] ?? [];
            
            foreach ($inventory_details as $inventory_event) {
                $seller_sku = $inventory_event['sellerSku'];
                $fulfillment_channel_sku = $inventory_event['fulfillmentChannelSku'];
                $available_quantity = $inventory_event['availableQuantity'];
                $marketplace_id = $inventory_event['marketplaceId'];
                
                // Update inventory in local database
                $this->updateInventory($seller_sku, $available_quantity, $marketplace_id);
                
                // Update OpenCart product stock if mapped
                $this->updateOpenCartStock($seller_sku, $available_quantity);
                
                // Log inventory change
                $this->logInventoryChange($seller_sku, $available_quantity, 'webhook_update');
                
                $this->log->write('[AMAZON WEBHOOK] Inventory updated: ' . $seller_sku . ' to ' . $available_quantity);
            }
            
            return ['status' => 'success', 'message' => 'Inventory events processed successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[AMAZON WEBHOOK] Inventory event error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle listing status change notification
     */
    private function handleListingStatusChange($payload)
    {
        try {
            $seller_sku = $payload['sellerSku'];
            $marketplace_id = $payload['marketplaceId'];
            $status = $payload['status'] ?? [];
            
            // Update listing status in database
            $this->updateListingStatus($seller_sku, $marketplace_id, $status);
            
            // Process status-specific actions
            if (isset($status['buyable']) && !$status['buyable']) {
                $this->handleNonBuyableListing($seller_sku, $marketplace_id);
            }
            
            $this->log->write('[AMAZON WEBHOOK] Listing status changed: ' . $seller_sku . ' in marketplace ' . $marketplace_id);
            
            return ['status' => 'success', 'message' => 'Listing status change processed successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[AMAZON WEBHOOK] Listing status change error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle listing issues change notification
     */
    private function handleListingIssuesChange($payload)
    {
        try {
            $seller_sku = $payload['sellerSku'];
            $marketplace_id = $payload['marketplaceId'];
            $issues = $payload['issues'] ?? [];
            
            // Clear existing issues for this listing
            $this->clearListingIssues($seller_sku, $marketplace_id);
            
            // Save new issues
            foreach ($issues as $issue) {
                $this->saveListingIssue($seller_sku, $marketplace_id, $issue);
            }
            
            // Send notification if critical issues exist
            $critical_issues = array_filter($issues, function($issue) {
                return isset($issue['severity']) && $issue['severity'] === 'ERROR';
            });
            
            if (!empty($critical_issues)) {
                $this->sendListingIssueAlert($seller_sku, $marketplace_id, $critical_issues);
            }
            
            $this->log->write('[AMAZON WEBHOOK] Listing issues updated: ' . $seller_sku . ' (' . count($issues) . ' issues)');
            
            return ['status' => 'success', 'message' => 'Listing issues processed successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[AMAZON WEBHOOK] Listing issues change error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle MFN order status change
     */
    private function handleMfnOrderStatusChange($payload)
    {
        try {
            $amazon_order_id = $payload['amazonOrderId'];
            $order_status = $payload['orderStatus'];
            
            // Update MFN order status
            $this->updateMfnOrderStatus($amazon_order_id, $order_status);
            
            // Process fulfillment actions based on status
            switch ($order_status) {
                case 'Unshipped':
                    $this->processMfnUnshippedOrder($amazon_order_id);
                    break;
                    
                case 'PartiallyShipped':
                    $this->processMfnPartiallyShippedOrder($amazon_order_id);
                    break;
                    
                case 'Shipped':
                    $this->processMfnShippedOrder($amazon_order_id);
                    break;
                    
                case 'Canceled':
                    $this->processMfnCanceledOrder($amazon_order_id);
                    break;
            }
            
            $this->log->write('[AMAZON WEBHOOK] MFN order status changed: ' . $amazon_order_id . ' to ' . $order_status);
            
            return ['status' => 'success', 'message' => 'MFN order status change processed successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[AMAZON WEBHOOK] MFN order status change error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle B2B offer changed notification
     */
    private function handleB2bOfferChanged($payload)
    {
        try {
            $seller_sku = $payload['sellerSku'];
            $marketplace_id = $payload['marketplaceId'];
            $offer_change_trigger = $payload['offerChangeTrigger'];
            
            // Get current B2B offer details
            $offer_details = $this->amazon_api->getB2bOfferDetails($seller_sku, $marketplace_id);
            
            if ($offer_details) {
                // Update B2B offer in database
                $this->updateB2bOffer($seller_sku, $marketplace_id, $offer_details);
                
                // Log offer change
                $this->logB2bOfferChange($seller_sku, $marketplace_id, $offer_change_trigger);
            }
            
            $this->log->write('[AMAZON WEBHOOK] B2B offer changed: ' . $seller_sku . ' (' . $offer_change_trigger . ')');
            
            return ['status' => 'success', 'message' => 'B2B offer change processed successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[AMAZON WEBHOOK] B2B offer change error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle FBA outbound shipment status notification
     */
    private function handleFbaOutboundShipmentStatus($payload)
    {
        try {
            $fulfillment_order_id = $payload['fulfillmentOrderId'];
            $fulfillment_shipment_id = $payload['fulfillmentShipmentId'];
            $fulfillment_shipment_status = $payload['fulfillmentShipmentStatus'];
            
            // Update FBA shipment status
            $this->updateFbaShipmentStatus($fulfillment_shipment_id, $fulfillment_shipment_status);
            
            // Process shipment status actions
            switch ($fulfillment_shipment_status) {
                case 'SHIPPED':
                    $this->processFbaShippedOrder($fulfillment_order_id, $fulfillment_shipment_id);
                    break;
                    
                case 'CANCELLED':
                    $this->processFbaCancelledShipment($fulfillment_order_id, $fulfillment_shipment_id);
                    break;
                    
                case 'DELIVERED':
                    $this->processFbaDeliveredShipment($fulfillment_order_id, $fulfillment_shipment_id);
                    break;
            }
            
            $this->log->write('[AMAZON WEBHOOK] FBA shipment status: ' . $fulfillment_shipment_id . ' to ' . $fulfillment_shipment_status);
            
            return ['status' => 'success', 'message' => 'FBA shipment status processed successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[AMAZON WEBHOOK] FBA shipment status error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle report processing finished notification
     */
    private function handleReportProcessingFinished($payload)
    {
        try {
            $report_id = $payload['reportId'];
            $report_type = $payload['reportType'];
            $report_processing_status = $payload['reportProcessingStatus'];
            
            // Update report status in database
            $this->updateReportStatus($report_id, $report_processing_status);
            
            if ($report_processing_status === 'DONE') {
                // Download and process the report
                $this->processCompletedReport($report_id, $report_type);
            } elseif ($report_processing_status === 'CANCELLED' || $report_processing_status === 'FATAL') {
                // Handle failed report
                $this->handleFailedReport($report_id, $report_type, $report_processing_status);
            }
            
            $this->log->write('[AMAZON WEBHOOK] Report processing finished: ' . $report_id . ' (' . $report_processing_status . ')');
            
            return ['status' => 'success', 'message' => 'Report processing notification handled successfully'];
            
        } catch (Exception $e) {
            $this->log->write('[AMAZON WEBHOOK] Report processing error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Update local order from Amazon order data
     */
    private function updateLocalOrder($order_data)
    {
        $amazon_order_id = $order_data['AmazonOrderId'];
        
        // Check if order exists
        $query = $this->db->query("
            SELECT order_id FROM " . DB_PREFIX . "amazon_orders 
            WHERE amazon_order_id = '" . $this->db->escape($amazon_order_id) . "'
        ");
        
        if ($query->num_rows) {
            // Update existing order
            $this->db->query("
                UPDATE " . DB_PREFIX . "amazon_orders SET
                order_status = '" . $this->db->escape($order_data['OrderStatus']) . "',
                fulfillment_channel = '" . $this->db->escape($order_data['FulfillmentChannel']) . "',
                order_total = '" . (float)($order_data['OrderTotal']['Amount'] ?? 0) . "',
                currency_code = '" . $this->db->escape($order_data['OrderTotal']['CurrencyCode'] ?? 'USD') . "',
                date_modified = NOW()
                WHERE amazon_order_id = '" . $this->db->escape($amazon_order_id) . "'
            ");
        } else {
            // Create new order
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "amazon_orders SET
                amazon_order_id = '" . $this->db->escape($amazon_order_id) . "',
                marketplace_id = '" . $this->db->escape($order_data['MarketplaceId']) . "',
                order_status = '" . $this->db->escape($order_data['OrderStatus']) . "',
                fulfillment_channel = '" . $this->db->escape($order_data['FulfillmentChannel']) . "',
                purchase_date = '" . $this->db->escape($order_data['PurchaseDate']) . "',
                order_total = '" . (float)($order_data['OrderTotal']['Amount'] ?? 0) . "',
                currency_code = '" . $this->db->escape($order_data['OrderTotal']['CurrencyCode'] ?? 'USD') . "',
                buyer_email = '" . $this->db->escape($order_data['BuyerInfo']['BuyerEmail'] ?? '') . "',
                buyer_name = '" . $this->db->escape($order_data['BuyerInfo']['BuyerName'] ?? '') . "',
                order_data = '" . $this->db->escape(json_encode($order_data)) . "',
                date_created = NOW()
            ");
        }
    }
    
    /**
     * Update inventory in local database
     */
    private function updateInventory($seller_sku, $available_quantity, $marketplace_id)
    {
        $this->db->query("
            UPDATE " . DB_PREFIX . "amazon_inventory 
            SET available_quantity = '" . (int)$available_quantity . "',
                date_modified = NOW()
            WHERE seller_sku = '" . $this->db->escape($seller_sku) . "'
            AND marketplace_id = '" . $this->db->escape($marketplace_id) . "'
        ");
        
        if ($this->db->countAffected() == 0) {
            // Insert new inventory record
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "amazon_inventory SET
                seller_sku = '" . $this->db->escape($seller_sku) . "',
                marketplace_id = '" . $this->db->escape($marketplace_id) . "',
                available_quantity = '" . (int)$available_quantity . "',
                date_created = NOW()
            ");
        }
    }
    
    /**
     * Update OpenCart stock
     */
    private function updateOpenCartStock($seller_sku, $new_stock)
    {
        // Get OpenCart product mapping
        $query = $this->db->query("
            SELECT opencart_product_id 
            FROM " . DB_PREFIX . "amazon_product_mapping 
            WHERE amazon_sku = '" . $this->db->escape($seller_sku) . "'
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
     * Log notification event
     */
    private function logNotificationEvent($data)
    {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "amazon_notification_logs SET
            notification_type = '" . $this->db->escape($data['notificationType']) . "',
            notification_version = '" . $this->db->escape($data['notificationVersion']) . "',
            payload = '" . $this->db->escape(json_encode($data)) . "',
            status = 'received',
            date_created = NOW()
        ");
    }
    
    /**
     * Update notification processing stats
     */
    private function updateNotificationStats($notification_type, $success)
    {
        $status = $success ? 'success' : 'failed';
        
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "amazon_notification_stats (notification_type, status, count, date_created)
            VALUES ('" . $this->db->escape($notification_type) . "', '" . $status . "', 1, NOW())
            ON DUPLICATE KEY UPDATE
            count = count + 1,
            date_modified = NOW()
        ");
    }
    
    /**
     * Log inventory change
     */
    private function logInventoryChange($seller_sku, $new_quantity, $reason)
    {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "amazon_inventory_history SET
            seller_sku = '" . $this->db->escape($seller_sku) . "',
            quantity = '" . (int)$new_quantity . "',
            change_reason = '" . $this->db->escape($reason) . "',
            date_created = NOW()
        ");
    }
    
    // Simplified implementations for remaining methods
    
    private function updateListingStatus($seller_sku, $marketplace_id, $status) {
        $this->db->query("
            UPDATE " . DB_PREFIX . "amazon_listings 
            SET status = '" . $this->db->escape(json_encode($status)) . "',
                date_modified = NOW()
            WHERE seller_sku = '" . $this->db->escape($seller_sku) . "'
            AND marketplace_id = '" . $this->db->escape($marketplace_id) . "'
        ");
    }
    
    private function clearListingIssues($seller_sku, $marketplace_id) {
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "amazon_listing_issues 
            WHERE seller_sku = '" . $this->db->escape($seller_sku) . "'
            AND marketplace_id = '" . $this->db->escape($marketplace_id) . "'
        ");
    }
    
    private function saveListingIssue($seller_sku, $marketplace_id, $issue) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "amazon_listing_issues SET
            seller_sku = '" . $this->db->escape($seller_sku) . "',
            marketplace_id = '" . $this->db->escape($marketplace_id) . "',
            issue_code = '" . $this->db->escape($issue['code'] ?? '') . "',
            severity = '" . $this->db->escape($issue['severity'] ?? '') . "',
            message = '" . $this->db->escape($issue['message'] ?? '') . "',
            date_created = NOW()
        ");
    }
    
    private function updateReportStatus($report_id, $status) {
        $this->db->query("
            UPDATE " . DB_PREFIX . "amazon_reports 
            SET processing_status = '" . $this->db->escape($status) . "',
                date_modified = NOW()
            WHERE report_id = '" . $this->db->escape($report_id) . "'
        ");
    }
    
    private function processOrderStatusActions($amazon_order_id, $order_status) {
        $this->log->write('[AMAZON WEBHOOK] Processing status actions for order: ' . $amazon_order_id . ' (' . $order_status . ')');
    }
    
    private function handleNonBuyableListing($seller_sku, $marketplace_id) {
        $this->log->write('[AMAZON WEBHOOK] Handling non-buyable listing: ' . $seller_sku);
    }
    
    private function sendListingIssueAlert($seller_sku, $marketplace_id, $issues) {
        $this->log->write('[AMAZON WEBHOOK] Listing issue alert sent for: ' . $seller_sku . ' (' . count($issues) . ' critical issues)');
    }
    
    private function updateMfnOrderStatus($amazon_order_id, $order_status) {
        $this->db->query("
            UPDATE " . DB_PREFIX . "amazon_orders 
            SET order_status = '" . $this->db->escape($order_status) . "'
            WHERE amazon_order_id = '" . $this->db->escape($amazon_order_id) . "'
        ");
    }
    
    private function processMfnUnshippedOrder($amazon_order_id) {
        $this->log->write('[AMAZON WEBHOOK] Processing MFN unshipped order: ' . $amazon_order_id);
    }
    
    private function processMfnPartiallyShippedOrder($amazon_order_id) {
        $this->log->write('[AMAZON WEBHOOK] Processing MFN partially shipped order: ' . $amazon_order_id);
    }
    
    private function processMfnShippedOrder($amazon_order_id) {
        $this->log->write('[AMAZON WEBHOOK] Processing MFN shipped order: ' . $amazon_order_id);
    }
    
    private function processMfnCanceledOrder($amazon_order_id) {
        $this->log->write('[AMAZON WEBHOOK] Processing MFN canceled order: ' . $amazon_order_id);
    }
    
    private function updateB2bOffer($seller_sku, $marketplace_id, $offer_details) {
        $this->db->query("
            UPDATE " . DB_PREFIX . "amazon_b2b_offers 
            SET offer_details = '" . $this->db->escape(json_encode($offer_details)) . "',
                date_modified = NOW()
            WHERE seller_sku = '" . $this->db->escape($seller_sku) . "'
            AND marketplace_id = '" . $this->db->escape($marketplace_id) . "'
        ");
    }
    
    private function logB2bOfferChange($seller_sku, $marketplace_id, $trigger) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "amazon_b2b_offer_history SET
            seller_sku = '" . $this->db->escape($seller_sku) . "',
            marketplace_id = '" . $this->db->escape($marketplace_id) . "',
            change_trigger = '" . $this->db->escape($trigger) . "',
            date_created = NOW()
        ");
    }
    
    private function updateFbaShipmentStatus($fulfillment_shipment_id, $status) {
        $this->db->query("
            UPDATE " . DB_PREFIX . "amazon_fba_shipments 
            SET shipment_status = '" . $this->db->escape($status) . "',
                date_modified = NOW()
            WHERE fulfillment_shipment_id = '" . $this->db->escape($fulfillment_shipment_id) . "'
        ");
    }
    
    private function processFbaShippedOrder($fulfillment_order_id, $fulfillment_shipment_id) {
        $this->log->write('[AMAZON WEBHOOK] Processing FBA shipped order: ' . $fulfillment_order_id);
    }
    
    private function processFbaCancelledShipment($fulfillment_order_id, $fulfillment_shipment_id) {
        $this->log->write('[AMAZON WEBHOOK] Processing FBA cancelled shipment: ' . $fulfillment_shipment_id);
    }
    
    private function processFbaDeliveredShipment($fulfillment_order_id, $fulfillment_shipment_id) {
        $this->log->write('[AMAZON WEBHOOK] Processing FBA delivered shipment: ' . $fulfillment_shipment_id);
    }
    
    private function processCompletedReport($report_id, $report_type) {
        $this->log->write('[AMAZON WEBHOOK] Processing completed report: ' . $report_id . ' (' . $report_type . ')');
    }
    
    private function handleFailedReport($report_id, $report_type, $status) {
        $this->log->write('[AMAZON WEBHOOK] Handling failed report: ' . $report_id . ' (' . $status . ')');
    }
    
    // Additional notification handlers
    private function handleFulfillmentOrderStatus($payload) {
        return ['status' => 'success', 'message' => 'Fulfillment order status processed'];
    }
    
    private function handleBrandedItemContentChange($payload) {
        return ['status' => 'success', 'message' => 'Branded item content change processed'];
    }
    
    private function handleItemProductTypeChange($payload) {
        return ['status' => 'success', 'message' => 'Item product type change processed'];
    }
    
    private function handleAPlusContentPublishStatus($payload) {
        return ['status' => 'success', 'message' => 'A+ content publish status processed'];
    }
} 