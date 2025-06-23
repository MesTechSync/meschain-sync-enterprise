<?php
namespace Opencart\Catalog\Model\Extension\Meschain\Sync;
require_once(DIR_SYSTEM . 'library/meschain/sync/BaseSyncTrait.php');

/**
 * Trendyol Stock Synchronization Model
 */
class Stock extends \Opencart\System\Engine\Model {
    use \Meschain\Sync\BaseSyncTrait;
    
    /**
     * Add a product to stock sync queue
     */
    public function addToSyncQueue($product_id, $operation = 'stock_update') {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "trendyol_stock_queue` SET 
            `opencart_product_id` = '" . (int)$product_id . "',
            `operation` = '" . $this->db->escape($operation) . "',
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
    public function processQueueItem($product_id) {
        // Get product data
        $this->load->model('catalog/product');
        $product = $this->model_catalog_product->getProduct($product_id);
        
        if (!$product) {
            $this->logError('stock_sync', $product_id, 'Product not found');
            return false;
        }
        
        // Get Trendyol product relation
        $trendyol_product = $this->getTrendyolProduct($product_id);
        
        if (!$trendyol_product) {
            $this->logError('stock_sync', $product_id, 'Product not found in Trendyol');
            return false;
        }
        
        // Update stock in Trendyol
        return $this->updateStockInTrendyol($product, $trendyol_product);
    }
    
    /**
     * Get Trendyol product
     */
    private function getTrendyolProduct($product_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_product` 
            WHERE opencart_product_id = '" . (int)$product_id . "'
            AND trendyol_product_id IS NOT NULL
            AND approved = 1
        ");
        
        return $query->row;
    }
    
    /**
     * Update stock in Trendyol
     */
    private function updateStockInTrendyol($product, $trendyol_product) {
        $log_id = $this->logEvent('stock_update', $product['product_id'], [
            'quantity' => $product['quantity'],
            'trendyol_product_id' => $trendyol_product['trendyol_product_id']
        ]);
        
        try {
            // Format stock data
            $stock_data = [
                'items' => [
                    [
                        'barcode' => $trendyol_product['barcode'],
                        'quantity' => (int)$product['quantity']
                    ]
                ]
            ];
            
            // Call Trendyol API
            $trendyol_api = $this->getTrendyolApi();
            $result = $trendyol_api->updateStock($stock_data);
            
            if (isset($result['success']) && $result['success']) {
                // Update last sync info
                $this->db->query("
                    UPDATE `" . DB_PREFIX . "trendyol_product` SET
                    `last_sync_status` = 'success',
                    `last_sync_message` = 'Stock updated successfully',
                    `last_sync_date` = NOW(),
                    `updated_at` = NOW()
                    WHERE opencart_product_id = '" . (int)$product['product_id'] . "'
                ");
                
                $this->updateEventStatus($log_id, 'completed', 'Stock updated successfully');
                return true;
            } else {
                $error_message = $result['message'] ?? 'Unknown error';
                $this->updateEventStatus($log_id, 'error', $error_message);
                
                // Update last sync info
                $this->db->query("
                    UPDATE `" . DB_PREFIX . "trendyol_product` SET
                    `last_sync_status` = 'error',
                    `last_sync_message` = '" . $this->db->escape($error_message) . "',
                    `last_sync_date` = NOW(),
                    `updated_at` = NOW()
                    WHERE opencart_product_id = '" . (int)$product['product_id'] . "'
                ");
                
                return false;
            }
        } catch (\Exception $e) {
            $this->updateEventStatus($log_id, 'error', $e->getMessage());
            
            // Update last sync info
            $this->db->query("
                UPDATE `" . DB_PREFIX . "trendyol_product` SET
                `last_sync_status` = 'error',
                `last_sync_message` = '" . $this->db->escape($e->getMessage()) . "',
                `last_sync_date` = NOW(),
                `updated_at` = NOW()
                WHERE opencart_product_id = '" . (int)$product['product_id'] . "'
            ");
            
            return false;
        }
    }
    
    /**
     * Process batch stock update for multiple products
     */
    public function processBatchStockUpdate($product_ids = []) {
        if (empty($product_ids)) {
            // If no specific products, get all from queue
            $product_ids = $this->getProductsFromQueue();
        }
        
        if (empty($product_ids)) {
            return true; // Nothing to process
        }
        
        $batch_items = [];
        $processed_ids = [];
        
        // Prepare batch data
        foreach ($product_ids as $product_id) {
            // Get product data
            $this->load->model('catalog/product');
            $product = $this->model_catalog_product->getProduct($product_id);
            
            // Get Trendyol product relation
            $trendyol_product = $this->getTrendyolProduct($product_id);
            
            if ($product && $trendyol_product) {
                $batch_items[] = [
                    'barcode' => $trendyol_product['barcode'],
                    'quantity' => (int)$product['quantity']
                ];
                
                $processed_ids[] = $product_id;
            }
        }
        
        if (empty($batch_items)) {
            return true; // Nothing to process
        }
        
        $log_id = $this->logEvent('batch_stock_update', 0, [
            'product_count' => count($batch_items)
        ]);
        
        try {
            // Format stock data
            $stock_data = [
                'items' => $batch_items
            ];
            
            // Call Trendyol API
            $trendyol_api = $this->getTrendyolApi();
            $result = $trendyol_api->updateStock($stock_data);
            
            if (isset($result['success']) && $result['success']) {
                // Update last sync info for all products
                foreach ($processed_ids as $product_id) {
                    $this->db->query("
                        UPDATE `" . DB_PREFIX . "trendyol_product` SET
                        `last_sync_status` = 'success',
                        `last_sync_message` = 'Stock updated via batch',
                        `last_sync_date` = NOW(),
                        `updated_at` = NOW()
                        WHERE opencart_product_id = '" . (int)$product_id . "'
                    ");
                }
                
                $this->updateEventStatus($log_id, 'completed', 'Batch stock update successful');
                return true;
            } else {
                $error_message = $result['message'] ?? 'Unknown error';
                $this->updateEventStatus($log_id, 'error', $error_message);
                return false;
            }
        } catch (\Exception $e) {
            $this->updateEventStatus($log_id, 'error', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get products from queue
     */
    private function getProductsFromQueue($limit = 100) {
        $query = $this->db->query("
            SELECT opencart_product_id 
            FROM `" . DB_PREFIX . "trendyol_stock_queue` 
            WHERE sync_status = 'pending' 
            AND retry_count < 3
            ORDER BY created_at ASC
            LIMIT " . (int)$limit
        );
        
        $products = [];
        foreach ($query->rows as $row) {
            $products[] = $row['opencart_product_id'];
        }
        
        return $products;
    }
}
