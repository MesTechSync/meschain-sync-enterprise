<?php
namespace MesChain\Cron;

/**
 * MesChain Sync Cron Job Handler
 * 
 * @package    MesChain Sync
 * @version    2.0.0
 * @author     MesChain Development Team
 * @copyright  2024 MesChain
 * @license    MIT License
 */

class SyncManager {
    
    private $registry;
    private $db;
    private $config;
    private $log;
    private $meschain_core;
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->log = $registry->get('log');
        
        // Load MesChain core
        $registry->get('load')->library('meschain/core');
        $this->meschain_core = new \MesChain\Core($registry);
    }
    
    /**
     * Main sync execution method
     */
    public function execute(): void {
        try {
            $this->log->write('MesChain Sync: Starting scheduled sync...');
            
            // Check if sync is already running
            if ($this->isSyncRunning()) {
                $this->log->write('MesChain Sync: Another sync process is already running. Skipping...');
                return;
            }
            
            // Set sync status to running
            $this->setSyncStatus('running');
            
            // Execute marketplace syncs
            $this->syncTrendyol();
            $this->syncAmazon();
            $this->syncHepsiburada();
            
            // Clean up old logs
            $this->cleanupLogs();
            
            // Set sync status to idle
            $this->setSyncStatus('idle');
            
            $this->log->write('MesChain Sync: Scheduled sync completed successfully.');
            
        } catch (\Exception $e) {
            $this->setSyncStatus('error');
            $this->log->write('MesChain Sync Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Sync with Trendyol
     */
    private function syncTrendyol(): void {
        if (!$this->config->get('meschain_trendyol_status')) {
            return;
        }
        
        if (!$this->config->get('meschain_trendyol_auto_sync')) {
            return;
        }
        
        try {
            $this->log->write('MesChain Sync: Starting Trendyol sync...');
            
            // Load Trendyol API
            $this->registry->get('load')->library('meschain/api/trendyol');
            $trendyol_api = new \MesChain\Api\Trendyol([
                'api_key' => $this->config->get('meschain_trendyol_api_key'),
                'api_secret' => $this->config->get('meschain_trendyol_api_secret'),
                'supplier_id' => $this->config->get('meschain_trendyol_supplier_id')
            ]);
            
            // Load models
            $this->registry->get('load')->model('extension/meschain/trendyol');
            $this->registry->get('load')->model('catalog/product');
            
            $trendyol_model = $this->registry->get('model_extension_meschain_trendyol');
            $product_model = $this->registry->get('model_catalog_product');
            
            // Get products that need syncing
            $products = $trendyol_model->getProductsForSync(50);
            $synced_count = 0;
            $error_count = 0;
            
            foreach ($products as $product) {
                try {
                    // Prepare product data
                    $this->registry->get('load')->library('meschain/helper/product');
                    $product_helper = new \MesChain\Helper\Product($this->registry);
                    
                    $product_data = $product_helper->prepareForMarketplace($product, 'trendyol');
                    
                    // Validate product data
                    $validation_errors = $product_helper->validateForMarketplace($product_data, 'trendyol');
                    
                    if (!empty($validation_errors)) {
                        $trendyol_model->saveProductMapping($product['product_id'], [
                            'trendyol_product_id' => '',
                            'sync_status' => 'error',
                            'sync_error' => implode(', ', $validation_errors),
                            'last_sync' => date('Y-m-d H:i:s')
                        ]);
                        $error_count++;
                        continue;
                    }
                    
                    // Check if product exists on Trendyol
                    $existing_mapping = $trendyol_model->getProductMapping($product['product_id']);
                    
                    if ($existing_mapping && $existing_mapping['trendyol_product_id']) {
                        // Update existing product
                        $result = $trendyol_api->updateProduct($existing_mapping['trendyol_product_id'], $product_data);
                    } else {
                        // Create new product
                        $result = $trendyol_api->createProduct($product_data);
                    }
                    
                    if ($result['success']) {
                        $trendyol_model->saveProductMapping($product['product_id'], [
                            'trendyol_product_id' => $result['data']['productId'] ?? $existing_mapping['trendyol_product_id'],
                            'sync_status' => 'synced',
                            'sync_error' => '',
                            'last_sync' => date('Y-m-d H:i:s'),
                            'trendyol_data' => $result['data'] ?? []
                        ]);
                        $synced_count++;
                    } else {
                        $trendyol_model->saveProductMapping($product['product_id'], [
                            'trendyol_product_id' => $existing_mapping['trendyol_product_id'] ?? '',
                            'sync_status' => 'error',
                            'sync_error' => $result['error'] ?? 'Unknown error',
                            'last_sync' => date('Y-m-d H:i:s')
                        ]);
                        $error_count++;
                    }
                    
                    // Small delay to prevent rate limiting
                    usleep(500000); // 0.5 seconds
                    
                } catch (\Exception $e) {
                    $trendyol_model->saveProductMapping($product['product_id'], [
                        'trendyol_product_id' => $existing_mapping['trendyol_product_id'] ?? '',
                        'sync_status' => 'error',
                        'sync_error' => $e->getMessage(),
                        'last_sync' => date('Y-m-d H:i:s')
                    ]);
                    $error_count++;
                }
            }
            
            // Sync orders from Trendyol
            $this->syncTrendyolOrders($trendyol_api, $trendyol_model);
            
            $this->log->write("MesChain Sync: Trendyol sync completed. Synced: {$synced_count}, Errors: {$error_count}");
            
        } catch (\Exception $e) {
            $this->log->write('MesChain Sync: Trendyol sync failed - ' . $e->getMessage());
        }
    }
    
    /**
     * Sync orders from Trendyol
     */
    private function syncTrendyolOrders($api, $model): void {
        try {
            // Get orders from last 24 hours
            $from_date = date('Y-m-d H:i:s', strtotime('-24 hours'));
            $orders = $api->getOrders(['startDate' => $from_date]);
            
            if ($orders['success'] && !empty($orders['data']['content'])) {
                $this->registry->get('load')->model('checkout/order');
                $order_model = $this->registry->get('model_checkout_order');
                
                foreach ($orders['data']['content'] as $trendyol_order) {
                    // Check if order already exists
                    $query = $this->db->query("SELECT order_id FROM `" . DB_PREFIX . "meschain_trendyol_orders` 
                        WHERE trendyol_order_id = '" . $this->db->escape($trendyol_order['orderNumber']) . "'");
                    
                    if (!$query->num_rows) {
                        // Create new order
                        $order_data = $this->transformTrendyolOrder($trendyol_order);
                        $order_id = $order_model->addOrder($order_data);
                        
                        if ($order_id) {
                            // Save order mapping
                            $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_trendyol_orders` SET 
                                `order_id` = '" . (int)$order_id . "',
                                `trendyol_order_id` = '" . $this->db->escape($trendyol_order['orderNumber']) . "',
                                `sync_status` = 'synced',
                                `trendyol_data` = '" . $this->db->escape(json_encode($trendyol_order)) . "',
                                `last_sync` = NOW()");
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->log->write('MesChain Sync: Trendyol order sync failed - ' . $e->getMessage());
        }
    }
    
    /**
     * Sync with Amazon (placeholder)
     */
    private function syncAmazon(): void {
        if (!$this->config->get('meschain_amazon_status')) {
            return;
        }
        
        // TODO: Implement Amazon sync
        $this->log->write('MesChain Sync: Amazon sync not implemented yet.');
    }
    
    /**
     * Sync with Hepsiburada (placeholder)
     */
    private function syncHepsiburada(): void {
        if (!$this->config->get('meschain_hepsiburada_status')) {
            return;
        }
        
        // TODO: Implement Hepsiburada sync
        $this->log->write('MesChain Sync: Hepsiburada sync not implemented yet.');
    }
    
    /**
     * Check if sync is currently running
     */
    private function isSyncRunning(): bool {
        $query = $this->db->query("SELECT setting_value FROM `" . DB_PREFIX . "setting` 
            WHERE store_id = '0' AND `code` = 'meschain_sync' AND `key` = 'meschain_sync_status'");
        
        return ($query->num_rows && $query->row['setting_value'] === 'running');
    }
    
    /**
     * Set sync status
     */
    private function setSyncStatus(string $status): void {
        $this->db->query("REPLACE INTO `" . DB_PREFIX . "setting` SET 
            store_id = '0', 
            `code` = 'meschain_sync', 
            `key` = 'meschain_sync_status', 
            `value` = '" . $this->db->escape($status) . "'");
        
        $this->db->query("REPLACE INTO `" . DB_PREFIX . "setting` SET 
            store_id = '0', 
            `code` = 'meschain_sync', 
            `key` = 'meschain_sync_last_run', 
            `value` = '" . date('Y-m-d H:i:s') . "'");
    }
    
    /**
     * Clean up old logs
     */
    private function cleanupLogs(): void {
        // Keep only last 30 days of logs
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_sync_logs` 
            WHERE date_added < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_trendyol_logs` 
            WHERE date_added < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    }
    
    /**
     * Transform Trendyol order to OpenCart format
     */
    private function transformTrendyolOrder(array $trendyol_order): array {
        $default_order_status = $this->config->get('meschain_trendyol_order_status') ?: 1;
        
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
            'payment_country_id' => 215,
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
            'shipping_country_id' => 215,
            'shipping_zone' => $trendyol_order['shippingAddress']['city'] ?? '',
            'shipping_zone_id' => 0,
            'shipping_method' => 'Trendyol Shipping',
            'shipping_code' => 'trendyol',
            'comment' => 'Order from Trendyol: ' . ($trendyol_order['orderNumber'] ?? ''),
            'total' => $trendyol_order['grossAmount'] ?? 0,
            'order_status_id' => $default_order_status,
            'language_id' => 2,
            'currency_id' => 1,
            'currency_code' => 'TRY',
            'currency_value' => 1,
            'date_added' => date('Y-m-d H:i:s'),
            'date_modified' => date('Y-m-d H:i:s')
        ];
    }
}
