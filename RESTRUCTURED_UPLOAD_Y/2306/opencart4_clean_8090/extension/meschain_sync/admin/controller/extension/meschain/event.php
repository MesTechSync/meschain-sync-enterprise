<?php
/**
 * MesChain Event Handlers
 * Native OpenCart 4.x Event System
 * Path: admin/controller/extension/meschain/event.php
 */

namespace Opencart\Admin\Controller\Extension\Meschain;

class Event extends \Opencart\System\Engine\Controller {
    
    /**
     * Product synchronization event
     * Triggered on: admin/model/catalog/product/editProduct/after
     */
    public function productSync(&$route, &$args, &$output) {
        if (!$this->config->get('meschain_status')) {
            return;
        }
        
        try {
            $this->load->model('extension/meschain/event');
            
            $product_id = $args[0] ?? null;
            $product_data = $args[1] ?? [];
            
            if ($product_id) {
                $this->model_extension_meschain_event->syncProduct($product_id, $product_data);
            }
            
        } catch (\Exception $e) {
            $this->log->write('MesChain Product Sync Event Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Order synchronization event
     * Triggered on: admin/model/sale/order/editOrder/after
     */
    public function orderSync(&$route, &$args, &$output) {
        if (!$this->config->get('meschain_status')) {
            return;
        }
        
        try {
            $this->load->model('extension/meschain/event');
            
            $order_id = $args[0] ?? null;
            $order_data = $args[1] ?? [];
            
            if ($order_id) {
                $this->model_extension_meschain_event->syncOrder($order_id, $order_data);
            }
            
        } catch (\Exception $e) {
            $this->log->write('MesChain Order Sync Event Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Product deletion event
     * Triggered on: admin/model/catalog/product/deleteProduct/before
     */
    public function productDelete(&$route, &$args, &$output) {
        if (!$this->config->get('meschain_status')) {
            return;
        }
        
        try {
            $this->load->model('extension/meschain/event');
            
            $product_id = $args[0] ?? null;
            
            if ($product_id) {
                $this->model_extension_meschain_event->deleteProduct($product_id);
            }
            
        } catch (\Exception $e) {
            $this->log->write('MesChain Product Delete Event Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Stock update event
     * Triggered on product stock changes
     */
    public function stockUpdate(&$route, &$args, &$output) {
        if (!$this->config->get('meschain_status') || !$this->config->get('meschain_auto_stock_sync')) {
            return;
        }
        
        try {
            $this->load->model('extension/meschain/event');
            
            $product_id = $args[0] ?? null;
            $quantity = $args[1] ?? null;
            
            if ($product_id !== null && $quantity !== null) {
                $this->model_extension_meschain_event->syncStock($product_id, $quantity);
            }
            
        } catch (\Exception $e) {
            $this->log->write('MesChain Stock Update Event Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Price update event
     * Triggered on product price changes
     */
    public function priceUpdate(&$route, &$args, &$output) {
        if (!$this->config->get('meschain_status') || !$this->config->get('meschain_auto_price_sync')) {
            return;
        }
        
        try {
            $this->load->model('extension/meschain/event');
            
            $product_id = $args[0] ?? null;
            $price_data = $args[1] ?? [];
            
            if ($product_id && !empty($price_data)) {
                $this->model_extension_meschain_event->syncPrice($product_id, $price_data);
            }
            
        } catch (\Exception $e) {
            $this->log->write('MesChain Price Update Event Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Order status change event
     * Triggered when order status changes
     */
    public function orderStatusChange(&$route, &$args, &$output) {
        if (!$this->config->get('meschain_status') || !$this->config->get('meschain_auto_order_sync')) {
            return;
        }
        
        try {
            $this->load->model('extension/meschain/event');
            
            $order_id = $args[0] ?? null;
            $order_status_id = $args[1] ?? null;
            $comment = $args[2] ?? '';
            $notify = $args[3] ?? false;
            
            if ($order_id && $order_status_id) {
                $this->model_extension_meschain_event->syncOrderStatus($order_id, $order_status_id, $comment, $notify);
            }
            
        } catch (\Exception $e) {
            $this->log->write('MesChain Order Status Change Event Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Category update event
     * Triggered when categories are modified
     */
    public function categoryUpdate(&$route, &$args, &$output) {
        if (!$this->config->get('meschain_status') || !$this->config->get('meschain_auto_category_sync')) {
            return;
        }
        
        try {
            $this->load->model('extension/meschain/event');
            
            $category_id = $args[0] ?? null;
            $category_data = $args[1] ?? [];
            
            if ($category_id) {
                $this->model_extension_meschain_event->syncCategory($category_id, $category_data);
            }
            
        } catch (\Exception $e) {
            $this->log->write('MesChain Category Update Event Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Customer registration event
     * For marketplace customer sync
     */
    public function customerRegister(&$route, &$args, &$output) {
        if (!$this->config->get('meschain_status') || !$this->config->get('meschain_customer_sync')) {
            return;
        }
        
        try {
            $this->load->model('extension/meschain/event');
            
            $customer_data = $args[0] ?? [];
            
            if (!empty($customer_data)) {
                $this->model_extension_meschain_event->syncCustomer($customer_data);
            }
            
        } catch (\Exception $e) {
            $this->log->write('MesChain Customer Register Event Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Manual event trigger for bulk operations
     */
    public function bulkSync(&$route, &$args, &$output) {
        try {
            $this->load->model('extension/meschain/event');
            
            $sync_type = $args[0] ?? 'product';
            $entity_ids = $args[1] ?? [];
            $marketplace = $args[2] ?? 'all';
            
            if (!empty($entity_ids)) {
                $result = $this->model_extension_meschain_event->bulkSync($sync_type, $entity_ids, $marketplace);
                
                // Store result in session for later retrieval
                $this->session->data['meschain_bulk_sync_result'] = $result;
            }
            
        } catch (\Exception $e) {
            $this->log->write('MesChain Bulk Sync Event Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Cleanup event
     * Triggered periodically to clean old data
     */
    public function cleanup(&$route, &$args, &$output) {
        try {
            $this->load->model('extension/meschain/event');
            
            $days_to_keep = $this->config->get('meschain_log_retention_days') ?: 30;
            
            $this->model_extension_meschain_event->cleanupOldLogs($days_to_keep);
            $this->model_extension_meschain_event->cleanupFailedSyncs();
            
        } catch (\Exception $e) {
            $this->log->write('MesChain Cleanup Event Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Health check event
     * Monitor system health and alert if needed
     */
    public function healthCheck(&$route, &$args, &$output) {
        try {
            $this->load->model('extension/meschain/event');
            
            $health_status = $this->model_extension_meschain_event->checkSystemHealth();
            
            if ($health_status['overall'] < 70) {
                // Alert administrators about system health issues
                $this->model_extension_meschain_event->sendHealthAlert($health_status);
            }
            
        } catch (\Exception $e) {
            $this->log->write('MesChain Health Check Event Error: ' . $e->getMessage());
        }
    }
    
    /**
     * API rate limit handler
     * Manage API calls to prevent rate limiting
     */
    public function rateLimit(&$route, &$args, &$output) {
        try {
            $this->load->model('extension/meschain/event');
            
            $marketplace = $args[0] ?? '';
            $endpoint = $args[1] ?? '';
            
            if ($marketplace && $endpoint) {
                $can_proceed = $this->model_extension_meschain_event->checkRateLimit($marketplace, $endpoint);
                
                if (!$can_proceed) {
                    // Store rate limit hit for retry mechanism
                    $this->session->data['meschain_rate_limit_hit'] = [
                        'marketplace' => $marketplace,
                        'endpoint' => $endpoint,
                        'timestamp' => time()
                    ];
                }
            }
            
        } catch (\Exception $e) {
            $this->log->write('MesChain Rate Limit Event Error: ' . $e->getMessage());
        }
    }
}
?>
