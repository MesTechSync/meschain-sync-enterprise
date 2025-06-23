<?php
namespace Opencart\Catalog\Model\Extension\Meschain\Sync;
require_once(DIR_SYSTEM . 'library/meschain/sync/BaseSyncTrait.php');

/**
 * Trendyol Order Synchronization Model
 */
class Order extends \Opencart\System\Engine\Model {
    use \Meschain\Sync\BaseSyncTrait;
    
    /**
     * Add an order to sync queue
     */
    public function addToSyncQueue($trendyol_order_id, $data = []) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "trendyol_order_queue` SET 
            `trendyol_order_id` = '" . (int)$trendyol_order_id . "',
            `data` = '" . $this->db->escape(json_encode($data)) . "',
            `sync_status` = 'pending',
            `retry_count` = 0,
            `created_at` = NOW(),
            `updated_at` = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Process a queue item
     */
    public function processQueueItem($trendyol_order_id) {
        // Get order details
        $order = $this->getTrendyolOrderDetails($trendyol_order_id);
        
        if (!$order) {
            $this->logError('order_sync', $trendyol_order_id, 'Order not found');
            return false;
        }
        
        // Get queue data with status information
        $queue_item = $this->getQueueItem($trendyol_order_id);
        
        if (!$queue_item) {
            $this->logError('order_sync', $trendyol_order_id, 'Queue item not found');
            return false;
        }
        
        $data = json_decode($queue_item['data'], true);
        
        // Update order status in Trendyol
        return $this->updateOrderStatusInTrendyol($order, $data);
    }
    
    /**
     * Get Trendyol order details
     */
    private function getTrendyolOrderDetails($trendyol_order_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_order` 
            WHERE id = '" . (int)$trendyol_order_id . "'
        ");
        
        return $query->row;
    }
    
    /**
     * Get queue item
     */
    private function getQueueItem($trendyol_order_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_order_queue` 
            WHERE trendyol_order_id = '" . (int)$trendyol_order_id . "'
            AND sync_status = 'pending'
            ORDER BY created_at DESC
            LIMIT 1
        ");
        
        return $query->row;
    }
    
    /**
     * Update order status in Trendyol
     */
    private function updateOrderStatusInTrendyol($order, $data) {
        $log_id = $this->logEvent('order_status_update', $order['id'], [
            'opencart_order_id' => $order['opencart_order_id'],
            'trendyol_order_number' => $order['trendyol_order_number'],
            'trendyol_status' => $data['trendyol_status'],
            'opencart_status_id' => $data['opencart_status_id']
        ]);
        
        try {
            // Format order status data
            $status_data = [
                'orderNumber' => $order['trendyol_order_number'],
                'status' => $data['trendyol_status'],
                'shipmentPackageIds' => [$order['shipment_package_id']]
            ];
            
            // Call Trendyol API
            $trendyol_api = $this->getTrendyolApi();
            $result = $trendyol_api->updateOrderStatus($status_data);
            
            if (isset($result['success']) && $result['success']) {
                // Update last sync info
                $this->db->query("
                    UPDATE `" . DB_PREFIX . "trendyol_order` SET
                    `last_sync_status` = 'success',
                    `last_sync_message` = 'Order status updated successfully',
                    `last_sync_date` = NOW(),
                    `trendyol_status` = '" . $this->db->escape($data['trendyol_status']) . "',
                    `updated_at` = NOW()
                    WHERE id = '" . (int)$order['id'] . "'
                ");
                
                // Mark queue item as processed
                $this->db->query("
                    UPDATE `" . DB_PREFIX . "trendyol_order_queue` SET
                    `sync_status` = 'completed',
                    `updated_at` = NOW()
                    WHERE trendyol_order_id = '" . (int)$order['id'] . "'
                    AND sync_status = 'pending'
                ");
                
                $this->updateEventStatus($log_id, 'completed', 'Order status updated successfully');
                return true;
            } else {
                $error_message = $result['message'] ?? 'Unknown error';
                $this->updateEventStatus($log_id, 'error', $error_message);
                
                // Update last sync info
                $this->db->query("
                    UPDATE `" . DB_PREFIX . "trendyol_order` SET
                    `last_sync_status` = 'error',
                    `last_sync_message` = '" . $this->db->escape($error_message) . "',
                    `last_sync_date` = NOW(),
                    `updated_at` = NOW()
                    WHERE id = '" . (int)$order['id'] . "'
                ");
                
                // Increment retry count
                $this->db->query("
                    UPDATE `" . DB_PREFIX . "trendyol_order_queue` SET
                    `retry_count` = retry_count + 1,
                    `updated_at` = NOW()
                    WHERE trendyol_order_id = '" . (int)$order['id'] . "'
                    AND sync_status = 'pending'
                ");
                
                return false;
            }
        } catch (\Exception $e) {
            $this->updateEventStatus($log_id, 'error', $e->getMessage());
            
            // Update last sync info
            $this->db->query("
                UPDATE `" . DB_PREFIX . "trendyol_order` SET
                `last_sync_status` = 'error',
                `last_sync_message` = '" . $this->db->escape($e->getMessage()) . "',
                `last_sync_date` = NOW(),
                `updated_at` = NOW()
                WHERE id = '" . (int)$order['id'] . "'
            ");
            
            // Increment retry count
            $this->db->query("
                UPDATE `" . DB_PREFIX . "trendyol_order_queue` SET
                `retry_count` = retry_count + 1,
                `updated_at` = NOW()
                WHERE trendyol_order_id = '" . (int)$order['id'] . "'
                AND sync_status = 'pending'
            ");
            
            return false;
        }
    }
    
    /**
     * Process incoming orders from Trendyol
     */
    public function processIncomingOrders() {
        $log_id = $this->logEvent('import_orders', 0, [
            'start_time' => date('Y-m-d H:i:s')
        ]);
        
        try {
            // Call Trendyol API to get orders
            $trendyol_api = $this->getTrendyolApi();
            $result = $trendyol_api->getOrders([
                'status' => 'Created',
                'startDate' => date('Y-m-d', strtotime('-1 day')),
                'endDate' => date('Y-m-d', strtotime('+1 day'))
            ]);
            
            if (!isset($result['success']) || !$result['success']) {
                $error_message = $result['message'] ?? 'Unknown error';
                $this->updateEventStatus($log_id, 'error', $error_message);
                return false;
            }
            
            $orders = $result['data']['content'] ?? [];
            
            if (empty($orders)) {
                $this->updateEventStatus($log_id, 'completed', 'No new orders found');
                return true;
            }
            
            $processed_count = 0;
            
            foreach ($orders as $trendyol_order) {
                // Check if order already exists
                if ($this->isOrderExists($trendyol_order['orderNumber'])) {
                    continue;
                }
                
                // Create OpenCart order
                $opencart_order_id = $this->createOpencartOrder($trendyol_order);
                
                if ($opencart_order_id) {
                    // Save Trendyol order in database
                    $this->saveTrendyolOrder($trendyol_order, $opencart_order_id);
                    $processed_count++;
                }
            }
            
            $this->updateEventStatus($log_id, 'completed', "Imported {$processed_count} new orders");
            return true;
        } catch (\Exception $e) {
            $this->updateEventStatus($log_id, 'error', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if Trendyol order already exists
     */
    private function isOrderExists($order_number) {
        $query = $this->db->query("
            SELECT id FROM `" . DB_PREFIX . "trendyol_order` 
            WHERE trendyol_order_number = '" . $this->db->escape($order_number) . "'
        ");
        
        return $query->num_rows > 0;
    }
    
    /**
     * Create OpenCart order from Trendyol order
     */
    private function createOpencartOrder($trendyol_order) {
        // Get settings
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_meschain_trendyol');
        
        // Get default customer ID
        $default_customer_id = $settings['module_meschain_trendyol_default_customer_id'] ?? 0;
        
        if (!$default_customer_id) {
            $this->logError('order_import', 0, 'Default customer ID is not set');
            return false;
        }
        
        // Get default order status
        $default_order_status_id = $settings['module_meschain_trendyol_default_order_status'] ?? 1; // Pending
        
        try {
            // Format order data
            $this->load->model('sale/order');
            
            $order_data = [
                'customer_id' => $default_customer_id,
                'customer_group_id' => 1, // Default customer group
                'firstname' => $trendyol_order['customerFirstName'] ?? 'Trendyol',
                'lastname' => $trendyol_order['customerLastName'] ?? 'Customer',
                'email' => $trendyol_order['customerEmail'] ?? 'trendyol@example.com',
                'telephone' => $trendyol_order['customerPhone'] ?? '',
                'payment_firstname' => $trendyol_order['invoiceAddress']['firstName'] ?? 'Trendyol',
                'payment_lastname' => $trendyol_order['invoiceAddress']['lastName'] ?? 'Customer',
                'payment_company' => '',
                'payment_address_1' => $trendyol_order['invoiceAddress']['address'] ?? 'Trendyol Address',
                'payment_address_2' => '',
                'payment_city' => $trendyol_order['invoiceAddress']['city'] ?? 'Unknown',
                'payment_postcode' => $trendyol_order['invoiceAddress']['postalCode'] ?? '',
                'payment_country' => 'Turkey',
                'payment_country_id' => 215, // Turkey default
                'payment_zone' => $trendyol_order['invoiceAddress']['district'] ?? '',
                'payment_zone_id' => 0,
                'payment_method' => 'Trendyol',
                'payment_code' => 'trendyol',
                'shipping_firstname' => $trendyol_order['shipmentAddress']['firstName'] ?? 'Trendyol',
                'shipping_lastname' => $trendyol_order['shipmentAddress']['lastName'] ?? 'Customer',
                'shipping_company' => '',
                'shipping_address_1' => $trendyol_order['shipmentAddress']['address'] ?? 'Trendyol Address',
                'shipping_address_2' => '',
                'shipping_city' => $trendyol_order['shipmentAddress']['city'] ?? 'Unknown',
                'shipping_postcode' => $trendyol_order['shipmentAddress']['postalCode'] ?? '',
                'shipping_country' => 'Turkey',
                'shipping_country_id' => 215, // Turkey default
                'shipping_zone' => $trendyol_order['shipmentAddress']['district'] ?? '',
                'shipping_zone_id' => 0,
                'shipping_method' => 'Trendyol',
                'shipping_code' => 'trendyol',
                'comment' => 'Trendyol Order: ' . $trendyol_order['orderNumber'],
                'total' => $trendyol_order['totalPrice'] ?? 0,
                'order_status_id' => $default_order_status_id,
                'affiliate_id' => 0,
                'commission' => 0,
                'marketing_id' => 0,
                'tracking' => '',
                'currency_code' => 'TRY',
                'currency_id' => 1, // Assumed TRY is ID 1
                'language_id' => (int)$this->config->get('config_language_id'),
                'products' => [],
                'totals' => [
                    [
                        'code' => 'sub_total',
                        'title' => 'Sub-Total',
                        'value' => $trendyol_order['totalPrice'] ?? 0,
                        'sort_order' => 1
                    ],
                    [
                        'code' => 'shipping',
                        'title' => 'Trendyol Shipping',
                        'value' => 0,
                        'sort_order' => 3
                    ],
                    [
                        'code' => 'total',
                        'title' => 'Total',
                        'value' => $trendyol_order['totalPrice'] ?? 0,
                        'sort_order' => 9
                    ]
                ]
            ];
            
            // Add order items
            if (!empty($trendyol_order['lines'])) {
                foreach ($trendyol_order['lines'] as $line) {
                    // Get product by barcode/model
                    $barcode = $line['barcode'] ?? '';
                    $product = $this->getProductByBarcode($barcode);
                    
                    if ($product) {
                        $order_data['products'][] = [
                            'product_id' => $product['product_id'],
                            'name' => $product['name'],
                            'model' => $product['model'],
                            'quantity' => $line['quantity'] ?? 1,
                            'price' => $line['price'] ?? 0,
                            'total' => ($line['price'] ?? 0) * ($line['quantity'] ?? 1),
                            'tax' => 0,
                            'reward' => 0
                        ];
                    } else {
                        // If product not found, create generic line item
                        $order_data['products'][] = [
                            'product_id' => 0,
                            'name' => $line['productName'] ?? 'Trendyol Product',
                            'model' => $barcode,
                            'quantity' => $line['quantity'] ?? 1,
                            'price' => $line['price'] ?? 0,
                            'total' => ($line['price'] ?? 0) * ($line['quantity'] ?? 1),
                            'tax' => 0,
                            'reward' => 0
                        ];
                    }
                }
            }
            
            // Add order
            $opencart_order_id = $this->model_sale_order->addOrder($order_data);
            return $opencart_order_id;
        } catch (\Exception $e) {
            $this->logError('order_import', 0, 'Failed to create OpenCart order: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get product by barcode (model)
     */
    private function getProductByBarcode($barcode) {
        if (!$barcode) {
            return false;
        }
        
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "product` 
            WHERE model = '" . $this->db->escape($barcode) . "'
        ");
        
        return $query->row;
    }
    
    /**
     * Save Trendyol order in database
     */
    private function saveTrendyolOrder($trendyol_order, $opencart_order_id) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "trendyol_order` SET
            `opencart_order_id` = '" . (int)$opencart_order_id . "',
            `trendyol_order_number` = '" . $this->db->escape($trendyol_order['orderNumber']) . "',
            `trendyol_order_id` = '" . $this->db->escape($trendyol_order['id'] ?? '') . "',
            `shipment_package_id` = '" . $this->db->escape($trendyol_order['shipmentPackageId'] ?? '') . "',
            `trendyol_status` = '" . $this->db->escape($trendyol_order['status'] ?? 'Created') . "',
            `order_data` = '" . $this->db->escape(json_encode($trendyol_order)) . "',
            `created_at` = NOW(),
            `updated_at` = NOW()
        ");
        
        return $this->db->getLastId();
    }
}
