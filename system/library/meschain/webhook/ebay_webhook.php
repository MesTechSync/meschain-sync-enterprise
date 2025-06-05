<?php
/**
 * eBay Webhook Class
 * MesChain-Sync OpenCart Extension
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0
 * @author MesChain Development Team
 */

require_once(DIR_SYSTEM . 'library/meschain/webhook/base_webhook.php');

class EbayWebhook extends BaseWebhook {
    
    /**
     * Get marketplace name
     *
     * @return string Marketplace name
     */
    protected function getMarketplaceName() {
        return 'ebay';
    }
    
    /**
     * Get signature header name
     *
     * @return string Header name
     */
    protected function getSignatureHeader() {
        return 'X-EBAY-SIGNATURE';
    }
    
    /**
     * Get timestamp header name
     *
     * @return string Header name
     */
    protected function getTimestampHeader() {
        return 'X-EBAY-SIGNATURE-TIMESTAMP';
    }
    
    /**
     * Get secret key for signature validation
     *
     * @return string Secret key
     */
    protected function getSecretKey() {
        return $this->config['ebay_webhook_secret'] ?? '';
    }
    
    /**
     * Generate signature for payload
     *
     * @param string $payload Raw payload
     * @param string $secret Secret key
     * @return string Generated signature
     */
    protected function generateSignature($payload, $secret) {
        return hash_hmac('sha256', $payload, $secret);
    }
    
    /**
     * Get event type from webhook data
     *
     * @param array $data Webhook data
     * @return string Event type
     */
    protected function getEventType($data) {
        return $data['eventType'] ?? $data['notificationType'] ?? $data['type'] ?? 'unknown';
    }
    
    /**
     * Check if webhook is enabled
     *
     * @return bool Enabled status
     */
    protected function isWebhookEnabled() {
        return (bool)($this->config['ebay_webhook_enabled'] ?? false);
    }
    
    /**
     * Process order created event
     *
     * @param array $data Event data
     * @return array Processing result
     */
    protected function processOrderCreated($data) {
        try {
            // eBay order data structure
            $order_data = $data['order'] ?? $data['data'] ?? $data;
            
            if (!isset($order_data['orderId'])) {
                return [
                    'success' => false,
                    'error' => 'Missing eBay order ID',
                    'http_code' => 400
                ];
            }
            
            $ebay_order_id = $order_data['orderId'];
            
            // Check if order already exists
            $existing_order = $this->checkExistingOrder($ebay_order_id);
            if ($existing_order) {
                return [
                    'success' => true,
                    'message' => 'Order already exists',
                    'http_code' => 200
                ];
            }
            
            // Create order in OpenCart
            $result = $this->createOpenCartOrder($order_data);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'eBay order created successfully',
                    'order_id' => $result['order_id'],
                    'http_code' => 200
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['error'],
                    'http_code' => 500
                ];
            }
            
        } catch (Exception $e) {
            $this->logError('eBay order creation failed', [
                'error' => $e->getMessage(),
                'order_data' => $order_data
            ]);
            
            return [
                'success' => false,
                'error' => 'Order creation failed',
                'http_code' => 500
            ];
        }
    }
    
    // Additional helper methods...
    
    private function checkExistingOrder($ebay_order_id) {
        $query = $this->db->query("
            SELECT id FROM " . DB_PREFIX . "meschain_marketplace_orders 
            WHERE marketplace = 'ebay' AND marketplace_order_id = '" . $this->db->escape($ebay_order_id) . "'
        ");
        
        return $query->num_rows > 0;
    }
    
    private function createOpenCartOrder($order_data) {
        try {
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "meschain_marketplace_orders 
                (marketplace, marketplace_order_id, status, total, currency, customer_data, product_data, sync_status, date_added) 
                VALUES (
                    'ebay',
                    '" . $this->db->escape($order_data['orderId']) . "',
                    'pending',
                    '" . (float)($order_data['pricingSummary']['total']['value'] ?? 0) . "',
                    '" . $this->db->escape($order_data['pricingSummary']['total']['currency'] ?? 'USD') . "',
                    '" . $this->db->escape(json_encode($order_data['fulfillmentStartInstructions'] ?? [])) . "',
                    '" . $this->db->escape(json_encode($order_data['lineItems'] ?? [])) . "',
                    'pending',
                    NOW()
                )
            ");
            
            $order_id = $this->db->getLastId();
            
            return [
                'success' => true,
                'order_id' => $order_id
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
