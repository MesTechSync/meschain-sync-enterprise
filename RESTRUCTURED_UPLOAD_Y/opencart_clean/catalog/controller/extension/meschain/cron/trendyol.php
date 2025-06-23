<?php
namespace Opencart\Catalog\Controller\Extension\Meschain\Cron;

/**
 * Trendyol Cron Controller
 * Handles scheduled tasks for Trendyol integration
 */
class Trendyol extends \Opencart\System\Engine\Controller {
    
    /**
     * Trendyol cron process entry point
     * URL: index.php?route=extension/meschain/cron/trendyol
     */
    public function index(): void {
        // Verify access token if security is enabled
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_meschain_trendyol');
        
        if (!empty($settings['module_meschain_trendyol_cron_token'])) {
            $token = $this->request->get['token'] ?? '';
            
            if ($token != $settings['module_meschain_trendyol_cron_token']) {
                http_response_code(403);
                die('Invalid token');
            }
        }
        
        // Check if module is enabled
        if (empty($settings['module_meschain_trendyol_status'])) {
            $this->outputJson(['success' => false, 'message' => 'Module is disabled']);
            return;
        }
        
        $action = $this->request->get['action'] ?? 'all';
        $limit = (int)($this->request->get['limit'] ?? 100);
        
        // Process the requested action
        switch ($action) {
            case 'import_orders':
                $this->importOrders();
                break;
                
            case 'sync_products':
                $this->syncProducts($limit);
                break;
                
            case 'sync_stock':
                $this->syncStock($limit);
                break;
                
            case 'sync_orders':
                $this->syncOrders($limit);
                break;
                
            case 'all':
                // Process everything
                $this->importOrders();
                $this->syncProducts($limit);
                $this->syncStock($limit);
                $this->syncOrders($limit);
                break;
                
            default:
                $this->outputJson(['success' => false, 'message' => 'Invalid action']);
                return;
        }
        
        // Log successful cron execution
        $this->logCronExecution($action);
        
        $this->outputJson(['success' => true, 'message' => 'Cron tasks completed']);
    }
    
    /**
     * Import new orders from Trendyol
     */
    private function importOrders(): void {
        try {
            $this->load->model('extension/meschain/sync/order');
            $result = $this->model_extension_meschain_sync_order->processIncomingOrders();
            
            $this->outputJson(['success' => true, 'message' => 'Order import process completed', 'result' => $result]);
        } catch (\Exception $e) {
            $this->outputJson(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Synchronize products in queue
     */
    private function syncProducts($limit): void {
        try {
            // Get pending products from queue
            $products = $this->getPendingProductQueue($limit);
            
            if (empty($products)) {
                $this->outputJson(['success' => true, 'message' => 'No pending products in queue']);
                return;
            }
            
            $success_count = 0;
            $failed_count = 0;
            
            $this->load->model('extension/meschain/sync/product');
            
            foreach ($products as $product) {
                $result = $this->model_extension_meschain_sync_product->processQueueItem($product['opencart_product_id']);
                
                if ($result) {
                    $success_count++;
                    $this->markQueueItemProcessed($product['id'], 'trendyol_product_queue');
                } else {
                    $failed_count++;
                    $this->incrementQueueRetry($product['id'], 'trendyol_product_queue');
                }
            }
            
            $this->outputJson([
                'success' => true, 
                'message' => "Processed {$success_count} products successfully, {$failed_count} failed"
            ]);
        } catch (\Exception $e) {
            $this->outputJson(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Synchronize stock in queue
     */
    private function syncStock($limit): void {
        try {
            // Get pending stock updates from queue
            $items = $this->getPendingStockQueue($limit);
            
            if (empty($items)) {
                $this->outputJson(['success' => true, 'message' => 'No pending stock updates in queue']);
                return;
            }
            
            $this->load->model('extension/meschain/sync/stock');
            
            // Process as batch if possible
            $product_ids = array_column($items, 'opencart_product_id');
            $result = $this->model_extension_meschain_sync_stock->processBatchStockUpdate($product_ids);
            
            if ($result) {
                // Mark all as processed
                foreach ($items as $item) {
                    $this->markQueueItemProcessed($item['id'], 'trendyol_stock_queue');
                }
                
                $this->outputJson([
                    'success' => true, 
                    'message' => "Processed " . count($items) . " stock updates successfully"
                ]);
            } else {
                // Increment retry for all
                foreach ($items as $item) {
                    $this->incrementQueueRetry($item['id'], 'trendyol_stock_queue');
                }
                
                $this->outputJson([
                    'success' => false, 
                    'message' => "Failed to process batch stock update"
                ]);
            }
        } catch (\Exception $e) {
            $this->outputJson(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Synchronize order status updates in queue
     */
    private function syncOrders($limit): void {
        try {
            // Get pending order updates from queue
            $items = $this->getPendingOrderQueue($limit);
            
            if (empty($items)) {
                $this->outputJson(['success' => true, 'message' => 'No pending order updates in queue']);
                return;
            }
            
            $success_count = 0;
            $failed_count = 0;
            
            $this->load->model('extension/meschain/sync/order');
            
            foreach ($items as $item) {
                $result = $this->model_extension_meschain_sync_order->processQueueItem($item['trendyol_order_id']);
                
                if ($result) {
                    $success_count++;
                    $this->markQueueItemProcessed($item['id'], 'trendyol_order_queue');
                } else {
                    $failed_count++;
                    $this->incrementQueueRetry($item['id'], 'trendyol_order_queue');
                }
            }
            
            $this->outputJson([
                'success' => true, 
                'message' => "Processed {$success_count} order updates successfully, {$failed_count} failed"
            ]);
        } catch (\Exception $e) {
            $this->outputJson(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Get pending product sync queue items
     */
    private function getPendingProductQueue($limit) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_product_queue` 
            WHERE sync_status = 'pending' 
            AND retry_count < 3
            ORDER BY created_at ASC
            LIMIT " . (int)$limit
        );
        
        return $query->rows;
    }
    
    /**
     * Get pending stock sync queue items
     */
    private function getPendingStockQueue($limit) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_stock_queue` 
            WHERE sync_status = 'pending' 
            AND retry_count < 3
            ORDER BY created_at ASC
            LIMIT " . (int)$limit
        );
        
        return $query->rows;
    }
    
    /**
     * Get pending order sync queue items
     */
    private function getPendingOrderQueue($limit) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_order_queue` 
            WHERE sync_status = 'pending' 
            AND retry_count < 3
            ORDER BY created_at ASC
            LIMIT " . (int)$limit
        );
        
        return $query->rows;
    }
    
    /**
     * Mark queue item as processed
     */
    private function markQueueItemProcessed($id, $table) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . $table . "` SET
            `sync_status` = 'completed',
            `updated_at` = NOW()
            WHERE id = '" . (int)$id . "'
        ");
    }
    
    /**
     * Increment queue retry count
     */
    private function incrementQueueRetry($id, $table) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . $table . "` SET
            `retry_count` = retry_count + 1,
            `updated_at` = NOW()
            WHERE id = '" . (int)$id . "'
        ");
    }
    
    /**
     * Log cron execution
     */
    private function logCronExecution($action) {
        // Log to event log
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "trendyol_event_log` SET
            `event_type` = 'cron_execution',
            `related_id` = 0,
            `data` = '" . $this->db->escape(json_encode([
                'action' => $action,
                'timestamp' => date('Y-m-d H:i:s')
            ])) . "',
            `status` = 'completed',
            `created_at` = NOW()
        ");
    }
    
    /**
     * Output JSON response and exit
     */
    private function outputJson($data) {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
}
