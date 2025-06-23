<?php
namespace Opencart\Catalog\Controller\Extension\MesChain;

/**
 * MesChain Sync Webhook Controller
 * 
 * @package    MesChain Sync
 * @version    2.0.0
 * @author     MesChain Development Team
 * @copyright  2024 MesChain
 * @license    MIT License
 */

class Webhook extends \Opencart\System\Engine\Controller {
    
    /**
     * Handle Trendyol webhooks
     */
    public function trendyol(): void {
        try {
            // Verify webhook signature
            if (!$this->verifyTrendyolSignature()) {
                $this->response->addHeader('HTTP/1.1 401 Unauthorized');
                $this->response->setOutput(json_encode(['error' => 'Invalid signature']));
                return;
            }
            
            // Get webhook data
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                $this->response->addHeader('HTTP/1.1 400 Bad Request');
                $this->response->setOutput(json_encode(['error' => 'Invalid JSON']));
                return;
            }
            
            // Load MesChain core
            $this->load->library('meschain/core');
            $meschain = new \MesChain\Core($this->registry);
            
            // Process webhook based on type
            $result = false;
            switch ($data['eventType'] ?? '') {
                case 'ORDER_CREATED':
                    $result = $this->processOrderCreated($data, $meschain);
                    break;
                    
                case 'ORDER_UPDATED':
                    $result = $this->processOrderUpdated($data, $meschain);
                    break;
                    
                case 'PRODUCT_UPDATED':
                    $result = $this->processProductUpdated($data, $meschain);
                    break;
                    
                case 'INVENTORY_UPDATED':
                    $result = $this->processInventoryUpdated($data, $meschain);
                    break;
                    
                default:
                    $this->log->write('MesChain Webhook: Unknown event type: ' . ($data['eventType'] ?? 'none'));
                    break;
            }
            
            if ($result) {
                $this->response->addHeader('HTTP/1.1 200 OK');
                $this->response->setOutput(json_encode(['success' => true]));
            } else {
                $this->response->addHeader('HTTP/1.1 500 Internal Server Error');
                $this->response->setOutput(json_encode(['error' => 'Processing failed']));
            }
            
        } catch (\Exception $e) {
            $this->log->write('MesChain Webhook Error: ' . $e->getMessage());
            $this->response->addHeader('HTTP/1.1 500 Internal Server Error');
            $this->response->setOutput(json_encode(['error' => 'Internal server error']));
        }
    }
    
    /**
     * Verify Trendyol webhook signature
     */
    private function verifyTrendyolSignature(): bool {
        $signature = $_SERVER['HTTP_X_TRENDYOL_SIGNATURE'] ?? '';
        $payload = file_get_contents('php://input');
        
        if (!$signature || !$payload) {
            return false;
        }
        
        $this->load->model('setting/setting');
        $api_secret = $this->model_setting_setting->getSettingValue('meschain_trendyol_api_secret');
        
        if (!$api_secret) {
            return false;
        }
        
        $expected_signature = hash_hmac('sha256', $payload, $api_secret);
        
        return hash_equals($signature, $expected_signature);
    }
    
    /**
     * Process order created webhook
     */
    private function processOrderCreated(array $data, $meschain): bool {
        try {
            if (!isset($data['order'])) {
                return false;
            }
            
            $order_data = $data['order'];
            
            // Transform Trendyol order to OpenCart format
            $opencart_order = $this->transformTrendyolOrder($order_data);
            
            // Create order in OpenCart
            $this->load->model('checkout/order');
            $order_id = $this->model_checkout_order->addOrder($opencart_order);
            
            if ($order_id) {
                // Store Trendyol order reference
                $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_trendyol_orders` SET 
                    `order_id` = '" . (int)$order_id . "',
                    `trendyol_order_id` = '" . $this->db->escape($order_data['orderNumber']) . "',
                    `trendyol_data` = '" . $this->db->escape(json_encode($order_data)) . "',
                    `date_added` = NOW()
                ");
                
                $meschain->log('Order created from Trendyol: ' . $order_data['orderNumber'], 'info');
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            $meschain->log('Order creation failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Process order updated webhook
     */
    private function processOrderUpdated(array $data, $meschain): bool {
        try {
            if (!isset($data['order'])) {
                return false;
            }
            
            $order_data = $data['order'];
            
            // Find OpenCart order by Trendyol order ID
            $query = $this->db->query("SELECT order_id FROM `" . DB_PREFIX . "meschain_trendyol_orders` 
                WHERE trendyol_order_id = '" . $this->db->escape($order_data['orderNumber']) . "'");
            
            if (!$query->num_rows) {
                $meschain->log('Order not found for update: ' . $order_data['orderNumber'], 'warning');
                return false;
            }
            
            $order_id = $query->row['order_id'];
            
            // Update order status based on Trendyol status
            $opencart_status = $this->mapTrendyolOrderStatus($order_data['status']);
            
            if ($opencart_status) {
                $this->load->model('checkout/order');
                $this->model_checkout_order->addHistory($order_id, $opencart_status, 
                    'Order status updated from Trendyol: ' . $order_data['status']);
                
                $meschain->log('Order updated from Trendyol: ' . $order_data['orderNumber'], 'info');
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            $meschain->log('Order update failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Process product updated webhook
     */
    private function processProductUpdated(array $data, $meschain): bool {
        try {
            if (!isset($data['product'])) {
                return false;
            }
            
            $product_data = $data['product'];
            
            // Find OpenCart product by Trendyol product ID
            $query = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "meschain_trendyol_products` 
                WHERE trendyol_product_id = '" . $this->db->escape($product_data['id']) . "'");
            
            if ($query->num_rows) {
                $product_id = $query->row['product_id'];
                
                // Update product data
                $this->load->model('catalog/product');
                $update_data = [
                    'price' => $product_data['price'] ?? 0,
                    'status' => $product_data['approved'] ? 1 : 0
                ];
                
                $this->model_catalog_product->editProduct($product_id, $update_data);
                
                $meschain->log('Product updated from Trendyol: ' . $product_data['id'], 'info');
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            $meschain->log('Product update failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Process inventory updated webhook
     */
    private function processInventoryUpdated(array $data, $meschain): bool {
        try {
            if (!isset($data['inventory'])) {
                return false;
            }
            
            $inventory_data = $data['inventory'];
            
            // Find OpenCart product by Trendyol product ID
            $query = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "meschain_trendyol_products` 
                WHERE trendyol_product_id = '" . $this->db->escape($inventory_data['productId']) . "'");
            
            if ($query->num_rows) {
                $product_id = $query->row['product_id'];
                
                // Update stock quantity
                $this->db->query("UPDATE `" . DB_PREFIX . "product` SET 
                    `quantity` = '" . (int)$inventory_data['quantity'] . "'
                    WHERE product_id = '" . (int)$product_id . "'");
                
                $meschain->log('Inventory updated from Trendyol: ' . $inventory_data['productId'], 'info');
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            $meschain->log('Inventory update failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Transform Trendyol order to OpenCart format
     */
    private function transformTrendyolOrder(array $trendyol_order): array {
        $this->load->model('setting/setting');
        $default_order_status = $this->model_setting_setting->getSettingValue('meschain_trendyol_order_status', 1);
        
        return [
            'invoice_prefix' => 'TR-',
            'store_id' => 0,
            'store_name' => $this->config->get('config_name'),
            'store_url' => $this->config->get('config_url'),
            'customer_id' => 0,
            'customer_group_id' => 1,
            'firstname' => $trendyol_order['customerFirstName'] ?? 'Trendyol',
            'lastname' => $trendyol_order['customerLastName'] ?? 'Customer',
            'email' => $trendyol_order['customerEmail'] ?? 'noreply@trendyol.com',
            'telephone' => $trendyol_order['customerPhone'] ?? '',
            'payment_firstname' => $trendyol_order['customerFirstName'] ?? 'Trendyol',
            'payment_lastname' => $trendyol_order['customerLastName'] ?? 'Customer',
            'payment_address_1' => $trendyol_order['invoiceAddress']['address'] ?? '',
            'payment_city' => $trendyol_order['invoiceAddress']['city'] ?? '',
            'payment_postcode' => $trendyol_order['invoiceAddress']['postalCode'] ?? '',
            'payment_country' => 'Turkey',
            'payment_country_id' => 215, // Turkey
            'payment_zone' => $trendyol_order['invoiceAddress']['city'] ?? '',
            'payment_zone_id' => 0,
            'payment_method' => 'Trendyol',
            'payment_code' => 'trendyol',
            'shipping_firstname' => $trendyol_order['customerFirstName'] ?? 'Trendyol',
            'shipping_lastname' => $trendyol_order['customerLastName'] ?? 'Customer',
            'shipping_address_1' => $trendyol_order['shippingAddress']['address'] ?? '',
            'shipping_city' => $trendyol_order['shippingAddress']['city'] ?? '',
            'shipping_postcode' => $trendyol_order['shippingAddress']['postalCode'] ?? '',
            'shipping_country' => 'Turkey',
            'shipping_country_id' => 215, // Turkey
            'shipping_zone' => $trendyol_order['shippingAddress']['city'] ?? '',
            'shipping_zone_id' => 0,
            'shipping_method' => 'Trendyol Shipping',
            'shipping_code' => 'trendyol',
            'comment' => 'Order from Trendyol: ' . ($trendyol_order['orderNumber'] ?? ''),
            'total' => $trendyol_order['grossAmount'] ?? 0,
            'order_status_id' => $default_order_status,
            'language_id' => 2, // Turkish
            'currency_id' => 1, // TRY
            'currency_code' => 'TRY',
            'currency_value' => 1,
            'date_added' => date('Y-m-d H:i:s'),
            'date_modified' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Map Trendyol order status to OpenCart status
     */
    private function mapTrendyolOrderStatus(string $trendyol_status): int {
        $status_map = [
            'Created' => 1,      // Pending
            'Approved' => 2,     // Processing
            'Picking' => 3,      // Shipped
            'Shipped' => 3,      // Shipped
            'Delivered' => 5,    // Complete
            'Cancelled' => 7,    // Cancelled
            'Returned' => 11,    // Refunded
        ];
        
        return $status_map[$trendyol_status] ?? 1;
    }
}
