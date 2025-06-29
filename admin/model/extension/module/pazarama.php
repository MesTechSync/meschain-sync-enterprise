<?php
/**
 * MesChain-Sync Pazarama Model
 * 
 * @package     MesChain-Sync
 * @subpackage  Pazarama Model
 * @category    Marketplace Integration
 * @author      MesChain Development Team
 * @copyright   2024 MesChain-Sync
 * @license     Commercial License
 * @version     1.0.0
 * @since       1.0.0
 */

class ModelExtensionModulePazarama extends Model {
    
    /**
     * Install Pazarama tables
     * 
     * @return void
     */
    public function install() {
        try {
            // Pazarama Products Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pazarama_products` (
                    `pazarama_product_id` int(11) NOT NULL AUTO_INCREMENT,
                    `product_id` int(11) NOT NULL,
                    `pazarama_id` varchar(255) NOT NULL,
                    `merchant_product_id` varchar(255) DEFAULT NULL,
                    `barcode` varchar(255) DEFAULT NULL,
                    `title` varchar(500) NOT NULL,
                    `brand_id` int(11) DEFAULT NULL,
                    `category_id` int(11) DEFAULT NULL,
                    `subcategory_id` int(11) DEFAULT NULL,
                    `list_price` decimal(15,4) DEFAULT NULL,
                    `sale_price` decimal(15,4) DEFAULT NULL,
                    `discount_price` decimal(15,4) DEFAULT NULL,
                    `currency_code` varchar(3) DEFAULT 'TRY',
                    `stock_quantity` int(11) DEFAULT 0,
                    `stock_code` varchar(255) DEFAULT NULL,
                    `description` text,
                    `short_description` text,
                    `product_images` text,
                    `variant_images` text,
                    `product_attributes` text,
                    `variant_attributes` text,
                    `size_chart` text,
                    `shipping_template_id` int(11) DEFAULT NULL,
                    `delivery_duration` int(11) DEFAULT 3,
                    `cargo_company` varchar(100) DEFAULT NULL,
                    `free_shipping` tinyint(1) DEFAULT 0,
                    `free_shipping_limit` decimal(15,4) DEFAULT NULL,
                    `product_status` enum('active','passive','draft','deleted') DEFAULT 'draft',
                    `approval_status` enum('approved','rejected','pending','under_review') DEFAULT 'pending',
                    `rejection_reason` text,
                    `quality_score` decimal(3,2) DEFAULT NULL,
                    `click_rate` decimal(5,4) DEFAULT NULL,
                    `conversion_rate` decimal(5,4) DEFAULT NULL,
                    `sales_rank` int(11) DEFAULT NULL,
                    `review_count` int(11) DEFAULT 0,
                    `average_rating` decimal(3,2) DEFAULT NULL,
                    `commission_rate` decimal(5,2) DEFAULT NULL,
                    `pazarama_commission` decimal(15,4) DEFAULT NULL,
                    `profit_margin` decimal(5,2) DEFAULT NULL,
                    `competitor_count` int(11) DEFAULT 0,
                    `min_competitor_price` decimal(15,4) DEFAULT NULL,
                    `max_competitor_price` decimal(15,4) DEFAULT NULL,
                    `avg_competitor_price` decimal(15,4) DEFAULT NULL,
                    `price_competitiveness` enum('low','competitive','high','premium') DEFAULT 'competitive',
                    `seo_title` varchar(500) DEFAULT NULL,
                    `seo_description` text,
                    `seo_keywords` varchar(1000) DEFAULT NULL,
                    `featured_product` tinyint(1) DEFAULT 0,
                    `campaign_eligible` tinyint(1) DEFAULT 1,
                    `seasonal_product` tinyint(1) DEFAULT 0,
                    `seasonal_months` varchar(100) DEFAULT NULL,
                    `last_sync` datetime DEFAULT NULL,
                    `sync_status` enum('success','error','pending','processing') DEFAULT 'pending',
                    `sync_error` text,
                    `api_response` text,
                    `performance_data` json DEFAULT NULL,
                    `analytics_data` json DEFAULT NULL,
                    `ai_insights` text,
                    `optimization_suggestions` text,
                    `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`pazarama_product_id`),
                    UNIQUE KEY `unique_product_pazarama` (`product_id`, `pazarama_id`),
                    KEY `idx_product_id` (`product_id`),
                    KEY `idx_pazarama_id` (`pazarama_id`),
                    KEY `idx_merchant_product_id` (`merchant_product_id`),
                    KEY `idx_product_status` (`product_status`),
                    KEY `idx_approval_status` (`approval_status`),
                    KEY `idx_last_sync` (`last_sync`),
                    KEY `idx_sync_status` (`sync_status`),
                    KEY `idx_sales_rank` (`sales_rank`),
                    KEY `idx_price_competitiveness` (`price_competitiveness`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Pazarama Orders Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pazarama_orders` (
                    `pazarama_order_id` int(11) NOT NULL AUTO_INCREMENT,
                    `order_id` int(11) DEFAULT NULL,
                    `pazarama_id` varchar(255) NOT NULL,
                    `order_number` varchar(255) NOT NULL,
                    `pazarama_order_number` varchar(255) DEFAULT NULL,
                    `customer_id` int(11) DEFAULT NULL,
                    `customer_info` json DEFAULT NULL,
                    `billing_address` json DEFAULT NULL,
                    `shipping_address` json DEFAULT NULL,
                    `order_date` datetime DEFAULT NULL,
                    `estimated_delivery_date` datetime DEFAULT NULL,
                    `order_status` varchar(100) DEFAULT 'Created',
                    `payment_status` varchar(100) DEFAULT 'Pending',
                    `shipment_status` varchar(100) DEFAULT 'NotShipped',
                    `payment_method` varchar(100) DEFAULT NULL,
                    `payment_type` varchar(100) DEFAULT NULL,
                    `installment_count` int(11) DEFAULT 1,
                    `total_amount` decimal(15,4) DEFAULT NULL,
                    `subtotal` decimal(15,4) DEFAULT NULL,
                    `tax_amount` decimal(15,4) DEFAULT NULL,
                    `shipping_cost` decimal(15,4) DEFAULT NULL,
                    `discount_amount` decimal(15,4) DEFAULT NULL,
                    `pazarama_discount` decimal(15,4) DEFAULT NULL,
                    `merchant_discount` decimal(15,4) DEFAULT NULL,
                    `commission_amount` decimal(15,4) DEFAULT NULL,
                    `commission_rate` decimal(5,2) DEFAULT NULL,
                    `currency_code` varchar(3) DEFAULT 'TRY',
                    `order_items` json DEFAULT NULL,
                    `cargo_tracking_number` varchar(255) DEFAULT NULL,
                    `cargo_tracking_url` varchar(500) DEFAULT NULL,
                    `cargo_company` varchar(100) DEFAULT NULL,
                    `invoice_number` varchar(255) DEFAULT NULL,
                    `invoice_date` datetime DEFAULT NULL,
                    `tax_office` varchar(255) DEFAULT NULL,
                    `tax_number` varchar(255) DEFAULT NULL,
                    `is_corporate` tinyint(1) DEFAULT 0,
                    `special_requests` text,
                    `gift_message` text,
                    `delivery_instructions` text,
                    `priority_level` int(11) DEFAULT 0,
                    `is_urgent` tinyint(1) DEFAULT 0,
                    `estimated_preparation_time` int(11) DEFAULT NULL,
                    `actual_ship_date` datetime DEFAULT NULL,
                    `delivered_date` datetime DEFAULT NULL,
                    `return_request` tinyint(1) DEFAULT 0,
                    `return_reason` text,
                    `return_date` datetime DEFAULT NULL,
                    `refund_amount` decimal(15,4) DEFAULT NULL,
                    `refund_status` varchar(100) DEFAULT NULL,
                    `customer_notes` text,
                    `merchant_notes` text,
                    `pazarama_notes` text,
                    `last_sync` datetime DEFAULT NULL,
                    `sync_status` enum('success','error','pending','processing') DEFAULT 'pending',
                    `sync_error` text,
                    `api_response` text,
                    `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`pazarama_order_id`),
                    UNIQUE KEY `unique_pazarama_order` (`pazarama_id`),
                    KEY `idx_order_id` (`order_id`),
                    KEY `idx_order_number` (`order_number`),
                    KEY `idx_pazarama_order_number` (`pazarama_order_number`),
                    KEY `idx_customer_id` (`customer_id`),
                    KEY `idx_order_date` (`order_date`),
                    KEY `idx_order_status` (`order_status`),
                    KEY `idx_payment_status` (`payment_status`),
                    KEY `idx_shipment_status` (`shipment_status`),
                    KEY `idx_last_sync` (`last_sync`),
                    KEY `idx_sync_status` (`sync_status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Pazarama Logs Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pazarama_logs` (
                    `log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `type` enum('info','warning','error','success') NOT NULL DEFAULT 'info',
                    `action` varchar(100) NOT NULL,
                    `message` text NOT NULL,
                    `data` longtext DEFAULT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`log_id`),
                    KEY `type` (`type`),
                    KEY `action` (`action`),
                    KEY `created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;            ");

            // Initialize webhook tables as well
            $this->installWebhookTables();

            $this->log('info', 'install', 'Pazarama tables created successfully');
        } catch (Exception $e) {
            $this->log('error', 'install', 'Failed to create Pazarama tables: ' . $e->getMessage());
        }
    }

    /**
     * Install webhook tables
     */
    private function installWebhookTables() {
        // Webhooks table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pazarama_webhooks` (
                `webhook_id` int(11) NOT NULL AUTO_INCREMENT,
                `event_type` varchar(100) NOT NULL,
                `url` varchar(500) NOT NULL,
                `secret` varchar(255) DEFAULT NULL,
                `status` tinyint(1) NOT NULL DEFAULT '1',
                `last_triggered` datetime DEFAULT NULL,
                `success_count` int(11) NOT NULL DEFAULT '0',
                `error_count` int(11) NOT NULL DEFAULT '0',
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`webhook_id`),
                KEY `event_type` (`event_type`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Webhook events log table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pazarama_webhook_events` (
                `event_id` int(11) NOT NULL AUTO_INCREMENT,
                `webhook_id` int(11) DEFAULT NULL,
                `event_type` varchar(100) NOT NULL,
                `payload` text NOT NULL,
                `response_code` int(11) DEFAULT NULL,
                `response_body` text DEFAULT NULL,
                `execution_time` decimal(10,4) DEFAULT NULL,
                `status` enum('success','error','pending') NOT NULL DEFAULT 'pending',
                `error_message` text DEFAULT NULL,
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`event_id`),
                KEY `webhook_id` (`webhook_id`),
                KEY `event_type` (`event_type`),
                KEY `status` (`status`),
                KEY `date_added` (`date_added`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Webhook notifications table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pazarama_webhook_notifications` (
                `notification_id` int(11) NOT NULL AUTO_INCREMENT,
                `type` varchar(100) NOT NULL,
                `title` varchar(255) NOT NULL,
                `message` text NOT NULL,
                `status` enum('success','error','warning','info') NOT NULL DEFAULT 'info',
                `is_read` tinyint(1) NOT NULL DEFAULT '0',
                `metadata` json DEFAULT NULL,
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`notification_id`),
                KEY `type` (`type`),
                KEY `status` (`status`),
                KEY `is_read` (`is_read`),
                KEY `date_added` (`date_added`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * Uninstall Pazarama tables
     * 
     * @return void
     */    public function uninstall() {
        try {
            // Drop webhook tables first (due to foreign key constraints)
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "pazarama_webhook_notifications`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "pazarama_webhook_events`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "pazarama_webhooks`");
            
            // Drop main tables
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "pazarama_products`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "pazarama_orders`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "pazarama_logs`");
            
            $this->log('info', 'uninstall', 'Pazarama tables dropped successfully');
        } catch (Exception $e) {
            $this->log('error', 'uninstall', 'Failed to drop Pazarama tables: ' . $e->getMessage());
        }
    }

    /**
     * Get product by OpenCart product ID
     * 
     * @param int $product_id OpenCart product ID
     * @return array|null Product data or null if not found
     */
    public function getProduct($product_id) {
        try {
            $query = $this->db->query("
                SELECT * FROM `" . DB_PREFIX . "pazarama_products` 
                WHERE `product_id` = '" . (int)$product_id . "'
            ");
            
            return $query->num_rows ? $query->row : null;
        } catch (Exception $e) {
            $this->log('error', 'getProduct', 'Failed to get product: ' . $e->getMessage(), ['product_id' => $product_id]);
            return null;
        }
    }

    /**
     * Get product by Pazarama ID
     * 
     * @param string $pazarama_id Pazarama product ID
     * @return array|null Product data or null if not found
     */
    public function getProductByPazaramaId($pazarama_id) {
        try {
            $query = $this->db->query("
                SELECT * FROM `" . DB_PREFIX . "pazarama_products` 
                WHERE `pazarama_id` = '" . $this->db->escape($pazarama_id) . "'
            ");
            
            return $query->num_rows ? $query->row : null;
        } catch (Exception $e) {
            $this->log('error', 'getProductByPazaramaId', 'Failed to get product by Pazarama ID: ' . $e->getMessage(), ['pazarama_id' => $pazarama_id]);
            return null;
        }
    }

    /**
     * Add or update product
     * 
     * @param array $data Product data
     * @return bool Success status
     */
    public function addProduct($data) {
        try {
            if (!isset($data['product_id']) || !isset($data['pazarama_id'])) {
                throw new Exception('Required fields missing: product_id, pazarama_id');
            }

            $existing = $this->getProduct($data['product_id']);
            
            if ($existing) {
                return $this->updateProduct($data);
            }

            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "pazarama_products` SET
                `product_id` = '" . (int)$data['product_id'] . "',
                `pazarama_id` = '" . $this->db->escape($data['pazarama_id']) . "',
                `status` = '" . (int)($data['status'] ?? 1) . "',
                `price` = '" . (float)($data['price'] ?? 0) . "',
                `stock_quantity` = '" . (int)($data['stock_quantity'] ?? 0) . "',
                `last_sync` = NOW()
            ");

            $this->log('success', 'addProduct', 'Product added successfully', $data);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'addProduct', 'Failed to add product: ' . $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Update product
     * 
     * @param array $data Product data
     * @return bool Success status
     */
    public function updateProduct($data) {
        try {
            if (!isset($data['product_id'])) {
                throw new Exception('Product ID is required');
            }

            $sql = "UPDATE `" . DB_PREFIX . "pazarama_products` SET ";
            $updates = [];

            if (isset($data['pazarama_id'])) {
                $updates[] = "`pazarama_id` = '" . $this->db->escape($data['pazarama_id']) . "'";
            }
            if (isset($data['status'])) {
                $updates[] = "`status` = '" . (int)$data['status'] . "'";
            }
            if (isset($data['price'])) {
                $updates[] = "`price` = '" . (float)$data['price'] . "'";
            }
            if (isset($data['stock_quantity'])) {
                $updates[] = "`stock_quantity` = '" . (int)$data['stock_quantity'] . "'";
            }
            
            $updates[] = "`last_sync` = NOW()";

            if (empty($updates)) {
                throw new Exception('No data to update');
            }

            $sql .= implode(', ', $updates);
            $sql .= " WHERE `product_id` = '" . (int)$data['product_id'] . "'";

            $this->db->query($sql);

            $this->log('success', 'updateProduct', 'Product updated successfully', $data);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'updateProduct', 'Failed to update product: ' . $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Delete product
     * 
     * @param int $product_id OpenCart product ID
     * @return bool Success status
     */
    public function deleteProduct($product_id) {
        try {
            $this->db->query("
                DELETE FROM `" . DB_PREFIX . "pazarama_products` 
                WHERE `product_id` = '" . (int)$product_id . "'
            ");

            $this->log('success', 'deleteProduct', 'Product deleted successfully', ['product_id' => $product_id]);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'deleteProduct', 'Failed to delete product: ' . $e->getMessage(), ['product_id' => $product_id]);
            return false;
        }
    }

    /**
     * Get all products with pagination
     * 
     * @param array $data Query parameters
     * @return array Products list
     */
    public function getProducts($data = []) {
        try {
            $sql = "SELECT pp.*, p.name, p.model, p.sku, p.price as opencart_price, p.quantity as opencart_quantity 
                    FROM `" . DB_PREFIX . "pazarama_products` pp
                    LEFT JOIN `" . DB_PREFIX . "product_description` p ON (pp.product_id = p.product_id)
                    WHERE p.language_id = '" . (int)$this->config->get('config_language_id') . "'";

            if (isset($data['filter_status'])) {
                $sql .= " AND pp.status = '" . (int)$data['filter_status'] . "'";
            }

            $sort_data = ['pp.last_sync', 'p.name', 'pp.price', 'pp.stock_quantity'];
            
            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $sql .= " ORDER BY " . $data['sort'];
            } else {
                $sql .= " ORDER BY pp.last_sync";
            }

            if (isset($data['order']) && ($data['order'] == 'DESC')) {
                $sql .= " DESC";
            } else {
                $sql .= " ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $query = $this->db->query($sql);
            return $query->rows;
        } catch (Exception $e) {
            $this->log('error', 'getProducts', 'Failed to get products: ' . $e->getMessage(), $data);
            return [];
        }
    }

    /**
     * Get total products count
     * 
     * @param array $data Filter parameters
     * @return int Total count
     */
    public function getTotalProducts($data = []) {
        try {
            $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "pazarama_products` pp";

            if (isset($data['filter_status'])) {
                $sql .= " WHERE pp.status = '" . (int)$data['filter_status'] . "'";
            }

            $query = $this->db->query($sql);
            return $query->row['total'];
        } catch (Exception $e) {
            $this->log('error', 'getTotalProducts', 'Failed to get total products: ' . $e->getMessage(), $data);
            return 0;
        }
    }

    /**
     * Get order by Pazarama order number
     * 
     * @param string $pazarama_order_number Pazarama order number
     * @return array|null Order data or null if not found
     */
    public function getOrderByPazaramaNumber($pazarama_order_number) {
        try {
            $query = $this->db->query("
                SELECT * FROM `" . DB_PREFIX . "pazarama_orders` 
                WHERE `pazarama_order_number` = '" . $this->db->escape($pazarama_order_number) . "'
            ");
            
            return $query->num_rows ? $query->row : null;
        } catch (Exception $e) {
            $this->log('error', 'getOrderByPazaramaNumber', 'Failed to get order: ' . $e->getMessage(), ['pazarama_order_number' => $pazarama_order_number]);
            return null;
        }
    }

    /**
     * Add order
     * 
     * @param array $data Order data
     * @return bool Success status
     */
    public function addOrder($data) {
        try {
            if (!isset($data['pazarama_order_number'])) {
                throw new Exception('Pazarama order number is required');
            }

            $existing = $this->getOrderByPazaramaNumber($data['pazarama_order_number']);
            if ($existing) {
                return $this->updateOrder($data);
            }

            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "pazarama_orders` SET
                `order_id` = " . (isset($data['order_id']) ? "'" . (int)$data['order_id'] . "'" : "NULL") . ",
                `pazarama_order_number` = '" . $this->db->escape($data['pazarama_order_number']) . "',
                `pazarama_status` = '" . $this->db->escape($data['pazarama_status'] ?? 'unknown') . "',
                `customer_name` = '" . $this->db->escape($data['customer_name'] ?? '') . "',
                `customer_email` = '" . $this->db->escape($data['customer_email'] ?? '') . "',
                `customer_phone` = '" . $this->db->escape($data['customer_phone'] ?? '') . "',
                `total_amount` = '" . (float)($data['total_amount'] ?? 0) . "',
                `currency` = '" . $this->db->escape($data['currency'] ?? 'TRY') . "',
                `order_date` = '" . $this->db->escape($data['order_date'] ?? date('Y-m-d H:i:s')) . "',
                `sync_status` = '" . $this->db->escape($data['sync_status'] ?? 'pending') . "',
                `notes` = '" . $this->db->escape($data['notes'] ?? '') . "'
            ");

            $this->log('success', 'addOrder', 'Order added successfully', $data);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'addOrder', 'Failed to add order: ' . $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Update order
     * 
     * @param array $data Order data
     * @return bool Success status
     */
    public function updateOrder($data) {
        try {
            if (!isset($data['pazarama_order_number'])) {
                throw new Exception('Pazarama order number is required');
            }

            $sql = "UPDATE `" . DB_PREFIX . "pazarama_orders` SET ";
            $updates = [];

            if (isset($data['order_id'])) {
                $updates[] = "`order_id` = '" . (int)$data['order_id'] . "'";
            }
            if (isset($data['pazarama_status'])) {
                $updates[] = "`pazarama_status` = '" . $this->db->escape($data['pazarama_status']) . "'";
            }
            if (isset($data['sync_status'])) {
                $updates[] = "`sync_status` = '" . $this->db->escape($data['sync_status']) . "'";
            }
            if (isset($data['sync_date'])) {
                $updates[] = "`sync_date` = '" . $this->db->escape($data['sync_date']) . "'";
            }
            if (isset($data['notes'])) {
                $updates[] = "`notes` = '" . $this->db->escape($data['notes']) . "'";
            }

            if (empty($updates)) {
                throw new Exception('No data to update');
            }

            $sql .= implode(', ', $updates);
            $sql .= " WHERE `pazarama_order_number` = '" . $this->db->escape($data['pazarama_order_number']) . "'";

            $this->db->query($sql);

            $this->log('success', 'updateOrder', 'Order updated successfully', $data);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'updateOrder', 'Failed to update order: ' . $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Add log entry
     * 
     * @param string $type Log type (info, warning, error, success)
     * @param string $action Action name
     * @param string $message Log message
     * @param array $data Additional data
     * @return void
     */
    public function log($type, $action, $message, $data = null) {
        try {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "pazarama_logs` SET
                `type` = '" . $this->db->escape($type) . "',
                `action` = '" . $this->db->escape($action) . "',
                `message` = '" . $this->db->escape($message) . "',
                `data` = " . ($data ? "'" . $this->db->escape(json_encode($data)) . "'" : "NULL") . "
            ");
        } catch (Exception $e) {
            // Silent fail to prevent infinite loops
            error_log('Pazarama Model Log Error: ' . $e->getMessage());
        }
    }

    /**
     * Get logs with pagination
     * 
     * @param array $data Query parameters
     * @return array Logs list
     */
    public function getLogs($data = []) {
        try {
            $sql = "SELECT * FROM `" . DB_PREFIX . "pazarama_logs`";
            
            $where = [];
            
            if (isset($data['filter_type'])) {
                $where[] = "`type` = '" . $this->db->escape($data['filter_type']) . "'";
            }
            
            if (isset($data['filter_action'])) {
                $where[] = "`action` = '" . $this->db->escape($data['filter_action']) . "'";
            }

            if (!empty($where)) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }

            $sql .= " ORDER BY `created_at` DESC";

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 50;
                }

                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $query = $this->db->query($sql);
            return $query->rows;
        } catch (Exception $e) {
            error_log('Pazarama Model getLogs Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        try {
            $stats = [];

            // Total products
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "pazarama_products`");
            $stats['total_products'] = $query->row['total'];

            // Active products
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "pazarama_products` WHERE status = 1");
            $stats['active_products'] = $query->row['total'];

            // Total orders
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "pazarama_orders`");
            $stats['total_orders'] = $query->row['total'];

            // Pending orders
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "pazarama_orders` WHERE sync_status = 'pending'");
            $stats['pending_orders'] = $query->row['total'];

            // Last sync date
            $query = $this->db->query("SELECT MAX(last_sync) as last_sync FROM `" . DB_PREFIX . "pazarama_products`");
            $stats['last_sync'] = $query->row['last_sync'];

            return $stats;
        } catch (Exception $e) {
            $this->log('error', 'getStatistics', 'Failed to get statistics: ' . $e->getMessage());
            return [
                'total_products' => 0,
                'active_products' => 0,
                'total_orders' => 0,
                'pending_orders' => 0,
                'last_sync' => null
            ];
        }
    }

    /**
     * Clear old logs
     * 
     * @param int $days Days to keep (default: 30)
     * @return bool Success status
     */
    public function clearOldLogs($days = 30) {
        try {
            $this->db->query("
                DELETE FROM `" . DB_PREFIX . "pazarama_logs` 
                WHERE `created_at` < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            ");

            $this->log('info', 'clearOldLogs', "Cleared logs older than {$days} days");
            return true;
        } catch (Exception $e) {
            $this->log('error', 'clearOldLogs', 'Failed to clear old logs: ' . $e->getMessage());
            return false;
        }
    }
}
?> 