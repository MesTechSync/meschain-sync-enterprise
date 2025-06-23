<?php
/**
 * MesChain-Sync Database Integration Manager
 * Comprehensive database operations for marketplace API data
 * 
 * @version 1.0.0
 * @date June 2, 2025
 * @author MesChain Development Team
 */

class MeschainDatabaseManager {
    
    private $db;
    private $error_handler;
    private $transaction_stack;
    private $query_cache;
    private $performance_tracker;
    
    public function __construct($db, $error_handler = null) {
        $this->db = $db;
        $this->error_handler = $error_handler;
        $this->transaction_stack = [];
        $this->query_cache = [];
        $this->performance_tracker = new DatabasePerformanceTracker();
        
        $this->initializeSchema();
    }
    
    /**
     * Initialize database schema for marketplace integrations
     */
    private function initializeSchema() {
        $tables = [
            'meschain_api_logs' => $this->getApiLogsSchema(),
            'meschain_marketplace_products' => $this->getMarketplaceProductsSchema(),
            'meschain_marketplace_orders' => $this->getMarketplaceOrdersSchema(),
            'meschain_sync_queue' => $this->getSyncQueueSchema(),
            'meschain_rate_limit_violations' => $this->getRateLimitViolationsSchema(),
            'meschain_webhook_events' => $this->getWebhookEventsSchema(),
            'meschain_marketplace_credentials' => $this->getMarketplaceCredentialsSchema(),
            'meschain_performance_metrics' => $this->getPerformanceMetricsSchema(),
            'meschain_error_tracking' => $this->getErrorTrackingSchema(),
            'meschain_cache_storage' => $this->getCacheStorageSchema()
        ];
        
        foreach ($tables as $table_name => $schema) {
            $this->createTableIfNotExists($table_name, $schema);
        }
    }
    
    /**
     * API logs table schema
     */
    private function getApiLogsSchema() {
        return "CREATE TABLE IF NOT EXISTS `meschain_api_logs` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `marketplace` VARCHAR(50) NOT NULL,
            `endpoint` VARCHAR(255) NOT NULL,
            `method` VARCHAR(10) NOT NULL,
            `request_data` LONGTEXT NULL,
            `response_data` LONGTEXT NULL,
            `response_code` INT(11) NULL,
            `execution_time` DECIMAL(10,3) NULL,
            `memory_usage` INT(11) NULL,
            `user_id` INT(11) NULL,
            `ip_address` VARCHAR(45) NULL,
            `user_agent` TEXT NULL,
            `error_message` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_marketplace` (`marketplace`),
            INDEX `idx_endpoint` (`endpoint`),
            INDEX `idx_created_at` (`created_at`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_response_code` (`response_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    }
    
    /**
     * Marketplace products table schema
     */
    private function getMarketplaceProductsSchema() {
        return "CREATE TABLE IF NOT EXISTS `meschain_marketplace_products` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `marketplace` VARCHAR(50) NOT NULL,
            `marketplace_product_id` VARCHAR(255) NOT NULL,
            `opencart_product_id` INT(11) NOT NULL,
            `sku` VARCHAR(255) NOT NULL,
            `title` VARCHAR(500) NOT NULL,
            `description` LONGTEXT NULL,
            `price` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `sale_price` DECIMAL(15,4) NULL,
            `quantity` INT(11) NOT NULL DEFAULT 0,
            `status` ENUM('active', 'inactive', 'pending', 'rejected', 'out_of_stock') NOT NULL DEFAULT 'pending',
            `category_id` VARCHAR(255) NULL,
            `brand` VARCHAR(255) NULL,
            `images` TEXT NULL,
            `attributes` LONGTEXT NULL,
            `marketplace_data` LONGTEXT NULL,
            `last_sync` TIMESTAMP NULL,
            `sync_status` ENUM('synced', 'pending', 'error', 'partial') NOT NULL DEFAULT 'pending',
            `sync_errors` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_marketplace_product` (`marketplace`, `marketplace_product_id`),
            INDEX `idx_opencart_product` (`opencart_product_id`),
            INDEX `idx_sku` (`sku`),
            INDEX `idx_marketplace` (`marketplace`),
            INDEX `idx_sync_status` (`sync_status`),
            INDEX `idx_status` (`status`),
            INDEX `idx_last_sync` (`last_sync`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    }
    
    /**
     * Marketplace orders table schema
     */
    private function getMarketplaceOrdersSchema() {
        return "CREATE TABLE IF NOT EXISTS `meschain_marketplace_orders` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `marketplace` VARCHAR(50) NOT NULL,
            `marketplace_order_id` VARCHAR(255) NOT NULL,
            `opencart_order_id` INT(11) NULL,
            `order_number` VARCHAR(255) NULL,
            `customer_name` VARCHAR(255) NOT NULL,
            `customer_email` VARCHAR(255) NULL,
            `customer_phone` VARCHAR(50) NULL,
            `billing_address` TEXT NULL,
            `shipping_address` TEXT NULL,
            `order_status` VARCHAR(100) NOT NULL,
            `payment_status` VARCHAR(100) NULL,
            `payment_method` VARCHAR(100) NULL,
            `shipping_method` VARCHAR(100) NULL,
            `order_total` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `shipping_cost` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `tax_amount` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `discount_amount` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `currency_code` VARCHAR(3) NOT NULL DEFAULT 'USD',
            `order_items` LONGTEXT NULL,
            `marketplace_data` LONGTEXT NULL,
            `order_date` TIMESTAMP NULL,
            `imported_at` TIMESTAMP NULL,
            `last_sync` TIMESTAMP NULL,
            `sync_status` ENUM('synced', 'pending', 'error', 'partial') NOT NULL DEFAULT 'pending',
            `sync_errors` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_marketplace_order` (`marketplace`, `marketplace_order_id`),
            INDEX `idx_opencart_order` (`opencart_order_id`),
            INDEX `idx_marketplace` (`marketplace`),
            INDEX `idx_order_status` (`order_status`),
            INDEX `idx_sync_status` (`sync_status`),
            INDEX `idx_order_date` (`order_date`),
            INDEX `idx_customer_email` (`customer_email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    }
    
    /**
     * Sync queue table schema
     */
    private function getSyncQueueSchema() {
        return "CREATE TABLE IF NOT EXISTS `meschain_sync_queue` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `marketplace` VARCHAR(50) NOT NULL,
            `sync_type` ENUM('product_sync', 'order_sync', 'inventory_sync', 'price_sync', 'full_sync') NOT NULL,
            `entity_type` ENUM('product', 'order', 'inventory', 'category', 'webhook') NOT NULL,
            `entity_id` VARCHAR(255) NOT NULL,
            `priority` TINYINT(1) NOT NULL DEFAULT 1,
            `status` ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
            `retry_count` TINYINT(1) NOT NULL DEFAULT 0,
            `max_retries` TINYINT(1) NOT NULL DEFAULT 3,
            `data` LONGTEXT NULL,
            `error_message` TEXT NULL,
            `scheduled_at` TIMESTAMP NULL,
            `started_at` TIMESTAMP NULL,
            `completed_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_marketplace` (`marketplace`),
            INDEX `idx_sync_type` (`sync_type`),
            INDEX `idx_status` (`status`),
            INDEX `idx_priority` (`priority`),
            INDEX `idx_scheduled_at` (`scheduled_at`),
            INDEX `idx_entity` (`entity_type`, `entity_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    }
    
    /**
     * Rate limit violations table schema
     */
    private function getRateLimitViolationsSchema() {
        return "CREATE TABLE IF NOT EXISTS `meschain_rate_limit_violations` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `ip_address` VARCHAR(45) NOT NULL,
            `user_id` INT(11) NULL,
            `marketplace` VARCHAR(50) NULL,
            `endpoint` VARCHAR(255) NULL,
            `violation_type` ENUM('rate_limit', 'burst_limit', 'daily_quota', 'suspicious_activity') NOT NULL,
            `request_count` INT(11) NOT NULL DEFAULT 1,
            `time_window` INT(11) NOT NULL,
            `blocked_until` TIMESTAMP NULL,
            `user_agent` TEXT NULL,
            `request_data` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_ip_address` (`ip_address`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_marketplace` (`marketplace`),
            INDEX `idx_violation_type` (`violation_type`),
            INDEX `idx_blocked_until` (`blocked_until`),
            INDEX `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    }
    
    /**
     * Webhook events table schema
     */
    private function getWebhookEventsSchema() {
        return "CREATE TABLE IF NOT EXISTS `meschain_webhook_events` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `marketplace` VARCHAR(50) NOT NULL,
            `event_type` VARCHAR(100) NOT NULL,
            `event_id` VARCHAR(255) NULL,
            `webhook_url` VARCHAR(500) NOT NULL,
            `payload` LONGTEXT NOT NULL,
            `headers` TEXT NULL,
            `signature` VARCHAR(255) NULL,
            `status` ENUM('pending', 'processing', 'processed', 'failed', 'ignored') NOT NULL DEFAULT 'pending',
            `retry_count` TINYINT(1) NOT NULL DEFAULT 0,
            `max_retries` TINYINT(1) NOT NULL DEFAULT 3,
            `processed_at` TIMESTAMP NULL,
            `error_message` TEXT NULL,
            `response_data` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_marketplace` (`marketplace`),
            INDEX `idx_event_type` (`event_type`),
            INDEX `idx_status` (`status`),
            INDEX `idx_created_at` (`created_at`),
            INDEX `idx_event_id` (`event_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    }
    
    /**
     * Marketplace credentials table schema
     */
    private function getMarketplaceCredentialsSchema() {
        return "CREATE TABLE IF NOT EXISTS `meschain_marketplace_credentials` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `marketplace` VARCHAR(50) NOT NULL,
            `user_id` INT(11) NOT NULL,
            `credential_type` ENUM('api_key', 'oauth_token', 'app_credentials') NOT NULL,
            `encrypted_data` LONGTEXT NOT NULL,
            `encryption_key_id` VARCHAR(100) NOT NULL,
            `metadata` TEXT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `expires_at` TIMESTAMP NULL,
            `last_used` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_user_marketplace` (`user_id`, `marketplace`, `credential_type`),
            INDEX `idx_marketplace` (`marketplace`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_is_active` (`is_active`),
            INDEX `idx_expires_at` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    }
    
    /**
     * Performance metrics table schema
     */
    private function getPerformanceMetricsSchema() {
        return "CREATE TABLE IF NOT EXISTS `meschain_performance_metrics` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `metric_type` VARCHAR(100) NOT NULL,
            `marketplace` VARCHAR(50) NULL,
            `endpoint` VARCHAR(255) NULL,
            `value` DECIMAL(15,6) NOT NULL,
            `unit` VARCHAR(20) NOT NULL,
            `threshold` DECIMAL(15,6) NULL,
            `status` ENUM('normal', 'warning', 'critical') NOT NULL DEFAULT 'normal',
            `additional_data` TEXT NULL,
            `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_metric_type` (`metric_type`),
            INDEX `idx_marketplace` (`marketplace`),
            INDEX `idx_endpoint` (`endpoint`),
            INDEX `idx_status` (`status`),
            INDEX `idx_recorded_at` (`recorded_at`),
            INDEX `idx_metric_marketplace_time` (`metric_type`, `marketplace`, `recorded_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    }
    
    /**
     * Error tracking table schema
     */
    private function getErrorTrackingSchema() {
        return "CREATE TABLE IF NOT EXISTS `meschain_error_tracking` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `error_code` VARCHAR(50) NOT NULL,
            `error_type` VARCHAR(100) NOT NULL,
            `marketplace` VARCHAR(50) NULL,
            `endpoint` VARCHAR(255) NULL,
            `error_message` TEXT NOT NULL,
            `stack_trace` LONGTEXT NULL,
            `context_data` LONGTEXT NULL,
            `user_id` INT(11) NULL,
            `ip_address` VARCHAR(45) NULL,
            `user_agent` TEXT NULL,
            `request_id` VARCHAR(100) NULL,
            `severity` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
            `is_resolved` TINYINT(1) NOT NULL DEFAULT 0,
            `resolved_at` TIMESTAMP NULL,
            `resolved_by` INT(11) NULL,
            `resolution_notes` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_error_code` (`error_code`),
            INDEX `idx_error_type` (`error_type`),
            INDEX `idx_marketplace` (`marketplace`),
            INDEX `idx_severity` (`severity`),
            INDEX `idx_is_resolved` (`is_resolved`),
            INDEX `idx_created_at` (`created_at`),
            INDEX `idx_user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    }
    
    /**
     * Cache storage table schema
     */
    private function getCacheStorageSchema() {
        return "CREATE TABLE IF NOT EXISTS `meschain_cache_storage` (
            `cache_key` VARCHAR(255) NOT NULL,
            `cache_value` LONGTEXT NOT NULL,
            `marketplace` VARCHAR(50) NULL,
            `cache_type` VARCHAR(100) NOT NULL,
            `expires_at` TIMESTAMP NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`cache_key`),
            INDEX `idx_marketplace` (`marketplace`),
            INDEX `idx_cache_type` (`cache_type`),
            INDEX `idx_expires_at` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    }
    
    /**
     * Create table if it doesn't exist
     */
    private function createTableIfNotExists($table_name, $schema) {
        try {
            $this->db->query($schema);
        } catch (Exception $e) {
            if ($this->error_handler) {
                throw new Exception("Failed to create table {$table_name}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Log API request/response
     */
    public function logApiRequest($marketplace, $endpoint, $method, $request_data = null, 
                                 $response_data = null, $response_code = null, 
                                 $execution_time = null, $error_message = null) {
        try {
            $this->db->query("
                INSERT INTO meschain_api_logs 
                (marketplace, endpoint, method, request_data, response_data, response_code, 
                 execution_time, memory_usage, user_id, ip_address, user_agent, error_message)
                VALUES (
                    '" . $this->db->escape($marketplace) . "',
                    '" . $this->db->escape($endpoint) . "',
                    '" . $this->db->escape($method) . "',
                    '" . $this->db->escape(json_encode($request_data)) . "',
                    '" . $this->db->escape(json_encode($response_data)) . "',
                    " . ($response_code ? (int)$response_code : 'NULL') . ",
                    " . ($execution_time ? (float)$execution_time : 'NULL') . ",
                    " . memory_get_usage(true) . ",
                    " . (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'NULL') . ",
                    '" . $this->db->escape($_SERVER['REMOTE_ADDR'] ?? '') . "',
                    '" . $this->db->escape($_SERVER['HTTP_USER_AGENT'] ?? '') . "',
                    " . ($error_message ? "'" . $this->db->escape($error_message) . "'" : 'NULL') . "
                )
            ");
            
            return $this->db->getLastId();
        } catch (Exception $e) {
            // Fail silently for logging errors to prevent infinite loops
            return false;
        }
    }
    
    /**
     * Save marketplace product
     */
    public function saveMarketplaceProduct($marketplace, $product_data) {
        try {
            $this->beginTransaction();
            
            $sql = "
                INSERT INTO meschain_marketplace_products 
                (marketplace, marketplace_product_id, opencart_product_id, sku, title, description, 
                 price, sale_price, quantity, status, category_id, brand, images, attributes, 
                 marketplace_data, sync_status)
                VALUES (
                    '" . $this->db->escape($marketplace) . "',
                    '" . $this->db->escape($product_data['marketplace_product_id']) . "',
                    " . (int)$product_data['opencart_product_id'] . ",
                    '" . $this->db->escape($product_data['sku']) . "',
                    '" . $this->db->escape($product_data['title']) . "',
                    '" . $this->db->escape($product_data['description'] ?? '') . "',
                    " . (float)($product_data['price'] ?? 0) . ",
                    " . ($product_data['sale_price'] ? (float)$product_data['sale_price'] : 'NULL') . ",
                    " . (int)($product_data['quantity'] ?? 0) . ",
                    '" . $this->db->escape($product_data['status'] ?? 'pending') . "',
                    '" . $this->db->escape($product_data['category_id'] ?? '') . "',
                    '" . $this->db->escape($product_data['brand'] ?? '') . "',
                    '" . $this->db->escape(json_encode($product_data['images'] ?? [])) . "',
                    '" . $this->db->escape(json_encode($product_data['attributes'] ?? [])) . "',
                    '" . $this->db->escape(json_encode($product_data['marketplace_data'] ?? [])) . "',
                    'synced'
                )
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    description = VALUES(description),
                    price = VALUES(price),
                    sale_price = VALUES(sale_price),
                    quantity = VALUES(quantity),
                    status = VALUES(status),
                    category_id = VALUES(category_id),
                    brand = VALUES(brand),
                    images = VALUES(images),
                    attributes = VALUES(attributes),
                    marketplace_data = VALUES(marketplace_data),
                    sync_status = 'synced',
                    last_sync = CURRENT_TIMESTAMP,
                    updated_at = CURRENT_TIMESTAMP
            ";
            
            $this->db->query($sql);
            $product_id = $this->db->getLastId();
            
            $this->commitTransaction();
            
            return $product_id;
            
        } catch (Exception $e) {
            $this->rollbackTransaction();
            throw new Exception("Failed to save marketplace product: " . $e->getMessage());
        }
    }
    
    /**
     * Save marketplace order
     */
    public function saveMarketplaceOrder($marketplace, $order_data) {
        try {
            $this->beginTransaction();
            
            $sql = "
                INSERT INTO meschain_marketplace_orders 
                (marketplace, marketplace_order_id, opencart_order_id, order_number, customer_name, 
                 customer_email, customer_phone, billing_address, shipping_address, order_status, 
                 payment_status, payment_method, shipping_method, order_total, shipping_cost, 
                 tax_amount, discount_amount, currency_code, order_items, marketplace_data, 
                 order_date, sync_status)
                VALUES (
                    '" . $this->db->escape($marketplace) . "',
                    '" . $this->db->escape($order_data['marketplace_order_id']) . "',
                    " . ($order_data['opencart_order_id'] ? (int)$order_data['opencart_order_id'] : 'NULL') . ",
                    '" . $this->db->escape($order_data['order_number'] ?? '') . "',
                    '" . $this->db->escape($order_data['customer_name']) . "',
                    '" . $this->db->escape($order_data['customer_email'] ?? '') . "',
                    '" . $this->db->escape($order_data['customer_phone'] ?? '') . "',
                    '" . $this->db->escape(json_encode($order_data['billing_address'] ?? [])) . "',
                    '" . $this->db->escape(json_encode($order_data['shipping_address'] ?? [])) . "',
                    '" . $this->db->escape($order_data['order_status']) . "',
                    '" . $this->db->escape($order_data['payment_status'] ?? '') . "',
                    '" . $this->db->escape($order_data['payment_method'] ?? '') . "',
                    '" . $this->db->escape($order_data['shipping_method'] ?? '') . "',
                    " . (float)($order_data['order_total'] ?? 0) . ",
                    " . (float)($order_data['shipping_cost'] ?? 0) . ",
                    " . (float)($order_data['tax_amount'] ?? 0) . ",
                    " . (float)($order_data['discount_amount'] ?? 0) . ",
                    '" . $this->db->escape($order_data['currency_code'] ?? 'USD') . "',
                    '" . $this->db->escape(json_encode($order_data['order_items'] ?? [])) . "',
                    '" . $this->db->escape(json_encode($order_data['marketplace_data'] ?? [])) . "',
                    '" . $this->db->escape($order_data['order_date']) . "',
                    'synced'
                )
                ON DUPLICATE KEY UPDATE
                    opencart_order_id = VALUES(opencart_order_id),
                    order_status = VALUES(order_status),
                    payment_status = VALUES(payment_status),
                    marketplace_data = VALUES(marketplace_data),
                    sync_status = 'synced',
                    last_sync = CURRENT_TIMESTAMP,
                    updated_at = CURRENT_TIMESTAMP
            ";
            
            $this->db->query($sql);
            $order_id = $this->db->getLastId();
            
            $this->commitTransaction();
            
            return $order_id;
            
        } catch (Exception $e) {
            $this->rollbackTransaction();
            throw new Exception("Failed to save marketplace order: " . $e->getMessage());
        }
    }
    
    /**
     * Add item to sync queue
     */
    public function addToSyncQueue($marketplace, $sync_type, $entity_type, $entity_id, 
                                  $data = null, $priority = 1, $scheduled_at = null) {
        try {
            $sql = "
                INSERT INTO meschain_sync_queue 
                (marketplace, sync_type, entity_type, entity_id, priority, data, scheduled_at)
                VALUES (
                    '" . $this->db->escape($marketplace) . "',
                    '" . $this->db->escape($sync_type) . "',
                    '" . $this->db->escape($entity_type) . "',
                    '" . $this->db->escape($entity_id) . "',
                    " . (int)$priority . ",
                    '" . $this->db->escape(json_encode($data)) . "',
                    " . ($scheduled_at ? "'" . $this->db->escape($scheduled_at) . "'" : 'NULL') . "
                )
            ";
            
            $this->db->query($sql);
            
            return $this->db->getLastId();
            
        } catch (Exception $e) {
            throw new Exception("Failed to add sync queue item: " . $e->getMessage());
        }
    }
    
    /**
     * Get next sync queue item
     */
    public function getNextSyncQueueItem($marketplace = null) {
        try {
            $where_clause = "status = 'pending' AND (scheduled_at IS NULL OR scheduled_at <= NOW())";
            
            if ($marketplace) {
                $where_clause .= " AND marketplace = '" . $this->db->escape($marketplace) . "'";
            }
            
            $sql = "
                SELECT * FROM meschain_sync_queue 
                WHERE {$where_clause}
                ORDER BY priority DESC, created_at ASC 
                LIMIT 1
            ";
            
            $result = $this->db->query($sql);
            
            if ($result->num_rows > 0) {
                $item = $result->row;
                
                // Mark as processing
                $this->db->query("
                    UPDATE meschain_sync_queue 
                    SET status = 'processing', started_at = NOW() 
                    WHERE id = " . (int)$item['id']
                );
                
                return $item;
            }
            
            return null;
            
        } catch (Exception $e) {
            throw new Exception("Failed to get sync queue item: " . $e->getMessage());
        }
    }
    
    /**
     * Update sync queue item status
     */
    public function updateSyncQueueStatus($item_id, $status, $error_message = null) {
        try {
            $sql = "
                UPDATE meschain_sync_queue 
                SET status = '" . $this->db->escape($status) . "',
                    completed_at = " . ($status === 'completed' ? 'NOW()' : 'NULL') . ",
                    error_message = " . ($error_message ? "'" . $this->db->escape($error_message) . "'" : 'NULL') . ",
                    retry_count = retry_count + " . ($status === 'failed' ? '1' : '0') . "
                WHERE id = " . (int)$item_id
            ;
            
            $this->db->query($sql);
            
            return true;
            
        } catch (Exception $e) {
            throw new Exception("Failed to update sync queue status: " . $e->getMessage());
        }
    }
    
    /**
     * Record rate limit violation
     */
    public function recordRateLimitViolation($ip_address, $user_id, $marketplace, $endpoint, 
                                           $violation_type, $request_count, $time_window, 
                                           $blocked_until = null) {
        try {
            $sql = "
                INSERT INTO meschain_rate_limit_violations 
                (ip_address, user_id, marketplace, endpoint, violation_type, request_count, 
                 time_window, blocked_until, user_agent)
                VALUES (
                    '" . $this->db->escape($ip_address) . "',
                    " . ($user_id ? (int)$user_id : 'NULL') . ",
                    '" . $this->db->escape($marketplace) . "',
                    '" . $this->db->escape($endpoint) . "',
                    '" . $this->db->escape($violation_type) . "',
                    " . (int)$request_count . ",
                    " . (int)$time_window . ",
                    " . ($blocked_until ? "'" . $this->db->escape($blocked_until) . "'" : 'NULL') . ",
                    '" . $this->db->escape($_SERVER['HTTP_USER_AGENT'] ?? '') . "'
                )
            ";
            
            $this->db->query($sql);
            
            return $this->db->getLastId();
            
        } catch (Exception $e) {
            throw new Exception("Failed to record rate limit violation: " . $e->getMessage());
        }
    }
    
    /**
     * Save webhook event
     */
    public function saveWebhookEvent($marketplace, $event_type, $event_id, $webhook_url, 
                                   $payload, $headers = null, $signature = null) {
        try {
            $sql = "
                INSERT INTO meschain_webhook_events 
                (marketplace, event_type, event_id, webhook_url, payload, headers, signature)
                VALUES (
                    '" . $this->db->escape($marketplace) . "',
                    '" . $this->db->escape($event_type) . "',
                    '" . $this->db->escape($event_id) . "',
                    '" . $this->db->escape($webhook_url) . "',
                    '" . $this->db->escape(json_encode($payload)) . "',
                    '" . $this->db->escape(json_encode($headers)) . "',
                    '" . $this->db->escape($signature) . "'
                )
            ";
            
            $this->db->query($sql);
            
            return $this->db->getLastId();
            
        } catch (Exception $e) {
            throw new Exception("Failed to save webhook event: " . $e->getMessage());
        }
    }
    
    /**
     * Record performance metric
     */
    public function recordPerformanceMetric($metric_type, $value, $unit, $marketplace = null, 
                                          $endpoint = null, $threshold = null) {
        try {
            $status = 'normal';
            if ($threshold && $value > $threshold) {
                $status = $value > ($threshold * 2) ? 'critical' : 'warning';
            }
            
            $sql = "
                INSERT INTO meschain_performance_metrics 
                (metric_type, marketplace, endpoint, value, unit, threshold, status)
                VALUES (
                    '" . $this->db->escape($metric_type) . "',
                    " . ($marketplace ? "'" . $this->db->escape($marketplace) . "'" : 'NULL') . ",
                    " . ($endpoint ? "'" . $this->db->escape($endpoint) . "'" : 'NULL') . ",
                    " . (float)$value . ",
                    '" . $this->db->escape($unit) . "',
                    " . ($threshold ? (float)$threshold : 'NULL') . ",
                    '" . $this->db->escape($status) . "'
                )
            ";
            
            $this->db->query($sql);
            
            return $this->db->getLastId();
            
        } catch (Exception $e) {
            throw new Exception("Failed to record performance metric: " . $e->getMessage());
        }
    }
    
    /**
     * Begin database transaction
     */
    public function beginTransaction() {
        $this->db->query("START TRANSACTION");
        $this->transaction_stack[] = true;
    }
    
    /**
     * Commit database transaction
     */
    public function commitTransaction() {
        if (!empty($this->transaction_stack)) {
            $this->db->query("COMMIT");
            array_pop($this->transaction_stack);
        }
    }
    
    /**
     * Rollback database transaction
     */
    public function rollbackTransaction() {
        if (!empty($this->transaction_stack)) {
            $this->db->query("ROLLBACK");
            array_pop($this->transaction_stack);
        }
    }
    
    /**
     * Get marketplace products with filters
     */
    public function getMarketplaceProducts($marketplace = null, $filters = []) {
        $where_conditions = [];
        
        if ($marketplace) {
            $where_conditions[] = "marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        if (isset($filters['status'])) {
            $where_conditions[] = "status = '" . $this->db->escape($filters['status']) . "'";
        }
        
        if (isset($filters['sync_status'])) {
            $where_conditions[] = "sync_status = '" . $this->db->escape($filters['sync_status']) . "'";
        }
        
        if (isset($filters['sku'])) {
            $where_conditions[] = "sku LIKE '%" . $this->db->escape($filters['sku']) . "%'";
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $limit_clause = '';
        if (isset($filters['limit'])) {
            $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;
            $limit_clause = "LIMIT {$offset}, " . (int)$filters['limit'];
        }
        
        $sql = "
            SELECT * FROM meschain_marketplace_products 
            {$where_clause}
            ORDER BY updated_at DESC
            {$limit_clause}
        ";
        
        $result = $this->db->query($sql);
        
        return $result->rows;
    }
    
    /**
     * Get marketplace orders with filters
     */
    public function getMarketplaceOrders($marketplace = null, $filters = []) {
        $where_conditions = [];
        
        if ($marketplace) {
            $where_conditions[] = "marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        if (isset($filters['order_status'])) {
            $where_conditions[] = "order_status = '" . $this->db->escape($filters['order_status']) . "'";
        }
        
        if (isset($filters['date_from'])) {
            $where_conditions[] = "order_date >= '" . $this->db->escape($filters['date_from']) . "'";
        }
        
        if (isset($filters['date_to'])) {
            $where_conditions[] = "order_date <= '" . $this->db->escape($filters['date_to']) . "'";
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $limit_clause = '';
        if (isset($filters['limit'])) {
            $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;
            $limit_clause = "LIMIT {$offset}, " . (int)$filters['limit'];
        }
        
        $sql = "
            SELECT * FROM meschain_marketplace_orders 
            {$where_clause}
            ORDER BY order_date DESC
            {$limit_clause}
        ";
        
        $result = $this->db->query($sql);
        
        return $result->rows;
    }
    
    /**
     * Get API call statistics
     */
    public function getApiStatistics($marketplace = null, $period_hours = 24) {
        $where_conditions = [];
        
        if ($marketplace) {
            $where_conditions[] = "marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        $where_conditions[] = "created_at >= DATE_SUB(NOW(), INTERVAL {$period_hours} HOUR)";
        
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        
        $sql = "
            SELECT 
                COUNT(*) as total_requests,
                AVG(execution_time) as avg_execution_time,
                MAX(execution_time) as max_execution_time,
                AVG(memory_usage) as avg_memory_usage,
                COUNT(CASE WHEN response_code >= 400 THEN 1 END) as error_count,
                COUNT(CASE WHEN response_code >= 200 AND response_code < 300 THEN 1 END) as success_count
            FROM meschain_api_logs 
            {$where_clause}
        ";
        
        $result = $this->db->query($sql);
        
        return $result->row;
    }
    
    /**
     * Clean up old data
     */
    public function cleanupOldData($days = 30) {
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $cleanup_queries = [
            "DELETE FROM meschain_api_logs WHERE created_at < '{$cutoff_date}'",
            "DELETE FROM meschain_rate_limit_violations WHERE created_at < '{$cutoff_date}'",
            "DELETE FROM meschain_webhook_events WHERE created_at < '{$cutoff_date}' AND status = 'processed'",
            "DELETE FROM meschain_performance_metrics WHERE recorded_at < '{$cutoff_date}'",
            "DELETE FROM meschain_sync_queue WHERE created_at < '{$cutoff_date}' AND status IN ('completed', 'cancelled')",
            "DELETE FROM meschain_cache_storage WHERE expires_at < NOW()"
        ];
        
        $cleaned_records = 0;
        
        foreach ($cleanup_queries as $query) {
            try {
                $this->db->query($query);
                $cleaned_records += $this->db->countAffected();
            } catch (Exception $e) {
                // Log cleanup errors but continue
                error_log("Cleanup error: " . $e->getMessage());
            }
        }
        
        return $cleaned_records;
    }
}

/**
 * Database Performance Tracker
 */
class DatabasePerformanceTracker {
    private $query_times = [];
    private $slow_query_threshold = 1.0; // seconds
    
    public function startQuery($query) {
        $query_id = md5($query);
        $this->query_times[$query_id] = [
            'query' => $query,
            'start_time' => microtime(true),
            'memory_start' => memory_get_usage(true)
        ];
        
        return $query_id;
    }
    
    public function endQuery($query_id) {
        if (!isset($this->query_times[$query_id])) {
            return null;
        }
        
        $execution_time = microtime(true) - $this->query_times[$query_id]['start_time'];
        $memory_used = memory_get_usage(true) - $this->query_times[$query_id]['memory_start'];
        
        $performance_data = [
            'query' => $this->query_times[$query_id]['query'],
            'execution_time' => $execution_time,
            'memory_used' => $memory_used,
            'is_slow' => $execution_time > $this->slow_query_threshold
        ];
        
        unset($this->query_times[$query_id]);
        
        return $performance_data;
    }
}
