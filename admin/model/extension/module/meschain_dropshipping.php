<?php
/**
 * MesChain Dropshipping Database Model - MUSTI TEAM ADVANCED IMPLEMENTATION
 * 
 * @author Musti Team - Database Excellence Specialists
 * @version 2.1 DROPSHIPPING ARCHITECTURE SUPREMACY
 * @date 10 Haziran 2025, 19:30 UTC+3
 * @priority ULTRA HIGH - BUSINESS CRITICAL #1
 */

class ModelExtensionModuleMeschainDropshipping extends Model {
    
    private $logger;
    private $cache_prefix = 'meschain_dropshipping_';
    
    public function __construct($registry) {
        parent::__construct($registry);
        $this->logger = new Log('meschain_dropshipping.log');
        $this->createTables();
    }

    /**
     * 🚀 PHASE 1: CORE DATABASE TABLES CREATION
     * Create all essential dropshipping tables with optimizations
     */
    public function createTables() {
        try {
            // 1. SUPPLIERS TABLE - Tedarikçi Yönetimi
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "suppliers` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `name` varchar(255) NOT NULL,
                  `company_name` varchar(255) NOT NULL,
                  `email` varchar(255) NOT NULL,
                  `phone` varchar(50) DEFAULT NULL,
                  `website` varchar(255) DEFAULT NULL,
                  `country` varchar(100) NOT NULL,
                  `api_endpoint` varchar(500) DEFAULT NULL,
                  `api_key` text DEFAULT NULL,
                  `api_secret` text DEFAULT NULL,
                  `authentication_type` enum('api_key','oauth','basic') DEFAULT 'api_key',
                  `commission_rate` decimal(5,2) DEFAULT 0.00,
                  `rating` decimal(3,2) DEFAULT 0.00,
                  `total_orders` int(11) DEFAULT 0,
                  `successful_orders` int(11) DEFAULT 0,
                  `avg_delivery_time` int(11) DEFAULT 0,
                  `min_order_amount` decimal(10,2) DEFAULT 0.00,
                  `currency` varchar(3) DEFAULT 'USD',
                  `payment_terms` text DEFAULT NULL,
                  `shipping_methods` json DEFAULT NULL,
                  `return_policy` text DEFAULT NULL,
                  `status` enum('active','inactive','suspended','pending') DEFAULT 'pending',
                  `quality_score` decimal(3,2) DEFAULT 0.00,
                  `response_time` int(11) DEFAULT 0,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `email` (`email`),
                  KEY `idx_status` (`status`),
                  KEY `idx_rating` (`rating`),
                  KEY `idx_country` (`country`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // 2. DROPSHIPPING_PRODUCTS TABLE - Ürün Kataloğu
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropshipping_products` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `supplier_id` int(11) unsigned NOT NULL,
                  `supplier_product_id` varchar(100) NOT NULL,
                  `sku` varchar(100) NOT NULL,
                  `name` varchar(500) NOT NULL,
                  `description` longtext DEFAULT NULL,
                  `category` varchar(255) DEFAULT NULL,
                  `subcategory` varchar(255) DEFAULT NULL,
                  `brand` varchar(255) DEFAULT NULL,
                  `model` varchar(255) DEFAULT NULL,
                  `cost_price` decimal(10,2) NOT NULL,
                  `suggested_price` decimal(10,2) DEFAULT NULL,
                  `minimum_price` decimal(10,2) DEFAULT NULL,
                  `currency` varchar(3) DEFAULT 'USD',
                  `stock_quantity` int(11) DEFAULT 0,
                  `min_order_quantity` int(11) DEFAULT 1,
                  `max_order_quantity` int(11) DEFAULT NULL,
                  `weight` decimal(8,3) DEFAULT NULL,
                  `dimensions` json DEFAULT NULL,
                  `images` json DEFAULT NULL,
                  `specifications` json DEFAULT NULL,
                  `tags` json DEFAULT NULL,
                  `shipping_time` int(11) DEFAULT NULL,
                  `shipping_cost` decimal(10,2) DEFAULT 0.00,
                  `return_allowed` tinyint(1) DEFAULT 1,
                  `warranty_period` int(11) DEFAULT 0,
                  `availability_status` enum('in_stock','out_of_stock','limited','pre_order') DEFAULT 'in_stock',
                  `quality_rating` decimal(3,2) DEFAULT 0.00,
                  `last_sync` timestamp NULL DEFAULT NULL,
                  `status` enum('active','inactive','discontinued') DEFAULT 'active',
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  FOREIGN KEY (`supplier_id`) REFERENCES `" . DB_PREFIX . "suppliers`(`id`) ON DELETE CASCADE,
                  UNIQUE KEY `supplier_sku` (`supplier_id`, `supplier_product_id`),
                  KEY `idx_sku` (`sku`),
                  KEY `idx_category` (`category`),
                  KEY `idx_status` (`status`),
                  KEY `idx_stock` (`stock_quantity`),
                  KEY `idx_price` (`cost_price`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // 3. DROPSHIPPING_ORDERS TABLE - Sipariş Takibi
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropshipping_orders` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `order_id` varchar(100) NOT NULL,
                  `customer_order_id` varchar(100) DEFAULT NULL,
                  `supplier_id` int(11) unsigned NOT NULL,
                  `customer_id` int(11) unsigned DEFAULT NULL,
                  `customer_name` varchar(255) NOT NULL,
                  `customer_email` varchar(255) NOT NULL,
                  `customer_phone` varchar(50) DEFAULT NULL,
                  `shipping_address` json NOT NULL,
                  `billing_address` json DEFAULT NULL,
                  `products` json NOT NULL,
                  `subtotal` decimal(10,2) NOT NULL,
                  `shipping_cost` decimal(10,2) DEFAULT 0.00,
                  `tax_amount` decimal(10,2) DEFAULT 0.00,
                  `total_amount` decimal(10,2) NOT NULL,
                  `profit_margin` decimal(10,2) DEFAULT 0.00,
                  `currency` varchar(3) DEFAULT 'USD',
                  `payment_status` enum('pending','paid','failed','refunded','partial_refund') DEFAULT 'pending',
                  `order_status` enum('pending','confirmed','processing','shipped','delivered','cancelled','returned') DEFAULT 'pending',
                  `supplier_order_id` varchar(100) DEFAULT NULL,
                  `tracking_number` varchar(100) DEFAULT NULL,
                  `shipping_method` varchar(100) DEFAULT NULL,
                  `estimated_delivery` date DEFAULT NULL,
                  `actual_delivery` date DEFAULT NULL,
                  `notes` text DEFAULT NULL,
                  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
                  `source_marketplace` varchar(50) DEFAULT NULL,
                  `marketplace_order_id` varchar(100) DEFAULT NULL,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  FOREIGN KEY (`supplier_id`) REFERENCES `" . DB_PREFIX . "suppliers`(`id`) ON DELETE RESTRICT,
                  UNIQUE KEY `order_id` (`order_id`),
                  KEY `idx_customer_email` (`customer_email`),
                  KEY `idx_status` (`order_status`),
                  KEY `idx_payment_status` (`payment_status`),
                  KEY `idx_marketplace` (`source_marketplace`),
                  KEY `idx_created` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // 4. SUPPLIER_PERFORMANCE TABLE - Analitik
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "supplier_performance` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `supplier_id` int(11) unsigned NOT NULL,
                  `date` date NOT NULL,
                  `total_orders` int(11) DEFAULT 0,
                  `successful_orders` int(11) DEFAULT 0,
                  `cancelled_orders` int(11) DEFAULT 0,
                  `returned_orders` int(11) DEFAULT 0,
                  `avg_processing_time` decimal(5,2) DEFAULT 0.00,
                  `avg_shipping_time` decimal(5,2) DEFAULT 0.00,
                  `on_time_delivery_rate` decimal(5,2) DEFAULT 0.00,
                  `customer_satisfaction` decimal(3,2) DEFAULT 0.00,
                  `total_revenue` decimal(12,2) DEFAULT 0.00,
                  `total_profit` decimal(12,2) DEFAULT 0.00,
                  `return_rate` decimal(5,2) DEFAULT 0.00,
                  `defect_rate` decimal(5,2) DEFAULT 0.00,
                  `response_time` int(11) DEFAULT 0,
                  `api_uptime` decimal(5,2) DEFAULT 100.00,
                  `stock_accuracy` decimal(5,2) DEFAULT 100.00,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  FOREIGN KEY (`supplier_id`) REFERENCES `" . DB_PREFIX . "suppliers`(`id`) ON DELETE CASCADE,
                  UNIQUE KEY `supplier_date` (`supplier_id`, `date`),
                  KEY `idx_date` (`date`),
                  KEY `idx_performance` (`on_time_delivery_rate`, `customer_satisfaction`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // 5. INVENTORY_SYNC TABLE - Gerçek Zamanlı Stok
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "inventory_sync` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `product_id` int(11) unsigned NOT NULL,
                  `supplier_id` int(11) unsigned NOT NULL,
                  `current_stock` int(11) NOT NULL,
                  `reserved_stock` int(11) DEFAULT 0,
                  `available_stock` int(11) GENERATED ALWAYS AS (`current_stock` - `reserved_stock`) STORED,
                  `reorder_level` int(11) DEFAULT 0,
                  `max_stock_level` int(11) DEFAULT NULL,
                  `last_restock_date` date DEFAULT NULL,
                  `next_expected_restock` date DEFAULT NULL,
                  `stock_alerts_enabled` tinyint(1) DEFAULT 1,
                  `auto_reorder_enabled` tinyint(1) DEFAULT 0,
                  `sync_frequency` int(11) DEFAULT 60,
                  `last_sync` timestamp NULL DEFAULT NULL,
                  `sync_status` enum('success','failed','pending','in_progress') DEFAULT 'pending',
                  `sync_error` text DEFAULT NULL,
                  `price_last_updated` timestamp NULL DEFAULT NULL,
                  `stock_trend` enum('increasing','stable','decreasing') DEFAULT 'stable',
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  FOREIGN KEY (`product_id`) REFERENCES `" . DB_PREFIX . "dropshipping_products`(`id`) ON DELETE CASCADE,
                  FOREIGN KEY (`supplier_id`) REFERENCES `" . DB_PREFIX . "suppliers`(`id`) ON DELETE CASCADE,
                  UNIQUE KEY `product_supplier` (`product_id`, `supplier_id`),
                  KEY `idx_stock_level` (`available_stock`),
                  KEY `idx_sync_status` (`sync_status`),
                  KEY `idx_last_sync` (`last_sync`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // 6. PRICING_RULES TABLE - Kar Otomasyonu
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pricing_rules` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `name` varchar(255) NOT NULL,
                  `description` text DEFAULT NULL,
                  `rule_type` enum('fixed_margin','percentage_margin','tiered_pricing','competitor_based','dynamic') NOT NULL,
                  `supplier_id` int(11) unsigned DEFAULT NULL,
                  `category` varchar(255) DEFAULT NULL,
                  `min_cost_price` decimal(10,2) DEFAULT NULL,
                  `max_cost_price` decimal(10,2) DEFAULT NULL,
                  `fixed_margin` decimal(10,2) DEFAULT NULL,
                  `percentage_margin` decimal(5,2) DEFAULT NULL,
                  `minimum_profit` decimal(10,2) DEFAULT NULL,
                  `maximum_price` decimal(10,2) DEFAULT NULL,
                  `competitor_adjustment` decimal(5,2) DEFAULT 0.00,
                  `seasonal_multiplier` decimal(3,2) DEFAULT 1.00,
                  `volume_discounts` json DEFAULT NULL,
                  `currency` varchar(3) DEFAULT 'USD',
                  `priority` int(11) DEFAULT 1,
                  `status` enum('active','inactive','test') DEFAULT 'active',
                  `start_date` date DEFAULT NULL,
                  `end_date` date DEFAULT NULL,
                  `auto_apply` tinyint(1) DEFAULT 1,
                  `created_by` int(11) unsigned DEFAULT NULL,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  FOREIGN KEY (`supplier_id`) REFERENCES `" . DB_PREFIX . "suppliers`(`id`) ON DELETE CASCADE,
                  KEY `idx_rule_type` (`rule_type`),
                  KEY `idx_priority` (`priority`),
                  KEY `idx_status` (`status`),
                  KEY `idx_category` (`category`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $this->logger->write('MUSTI TEAM: Dropshipping tables created successfully - Phase 1 Complete');

        } catch (Exception $e) {
            $this->logger->write('MUSTI TEAM ERROR: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 💾 SUPPLIER MANAGEMENT OPERATIONS
     */
    public function addSupplier($data) {
        $sql = "INSERT INTO `" . DB_PREFIX . "suppliers` SET ";
        $sql .= "`name` = '" . $this->db->escape($data['name']) . "', ";
        $sql .= "`company_name` = '" . $this->db->escape($data['company_name']) . "', ";
        $sql .= "`email` = '" . $this->db->escape($data['email']) . "', ";
        $sql .= "`phone` = '" . $this->db->escape($data['phone']) . "', ";
        $sql .= "`website` = '" . $this->db->escape($data['website']) . "', ";
        $sql .= "`country` = '" . $this->db->escape($data['country']) . "', ";
        $sql .= "`api_endpoint` = '" . $this->db->escape($data['api_endpoint']) . "', ";
        $sql .= "`commission_rate` = '" . (float)$data['commission_rate'] . "', ";
        $sql .= "`status` = '" . $this->db->escape($data['status']) . "'";

        $this->db->query($sql);
        $supplier_id = $this->db->getLastId();

        $this->cache->delete($this->cache_prefix . 'suppliers');
        $this->logger->write('MUSTI TEAM: New supplier added - ID: ' . $supplier_id);

        return $supplier_id;
    }

    public function getSupplier($supplier_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "suppliers` WHERE `id` = '" . (int)$supplier_id . "'");
        return $query->row;
    }

    public function getSuppliers($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "suppliers`";

        $implode = array();

        if (!empty($data['filter_name'])) {
            $implode[] = "(`name` LIKE '%" . $this->db->escape($data['filter_name']) . "%' OR `company_name` LIKE '%" . $this->db->escape($data['filter_name']) . "%')";
        }

        if (!empty($data['filter_status'])) {
            $implode[] = "`status` = '" . $this->db->escape($data['filter_status']) . "'";
        }

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        $sql .= " ORDER BY `name` ASC";

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
    }

    /**
     * 📦 PRODUCT MANAGEMENT OPERATIONS
     */
    public function addDropshippingProduct($data) {
        $sql = "INSERT INTO `" . DB_PREFIX . "dropshipping_products` SET ";
        $sql .= "`supplier_id` = '" . (int)$data['supplier_id'] . "', ";
        $sql .= "`supplier_product_id` = '" . $this->db->escape($data['supplier_product_id']) . "', ";
        $sql .= "`sku` = '" . $this->db->escape($data['sku']) . "', ";
        $sql .= "`name` = '" . $this->db->escape($data['name']) . "', ";
        $sql .= "`description` = '" . $this->db->escape($data['description']) . "', ";
        $sql .= "`category` = '" . $this->db->escape($data['category']) . "', ";
        $sql .= "`cost_price` = '" . (float)$data['cost_price'] . "', ";
        $sql .= "`suggested_price` = '" . (float)$data['suggested_price'] . "', ";
        $sql .= "`stock_quantity` = '" . (int)$data['stock_quantity'] . "', ";
        $sql .= "`status` = '" . $this->db->escape($data['status']) . "'";

        if (!empty($data['images'])) {
            $sql .= ", `images` = '" . $this->db->escape(json_encode($data['images'])) . "'";
        }

        $this->db->query($sql);
        $product_id = $this->db->getLastId();

        // Initialize inventory sync
        $this->initializeInventorySync($product_id, $data['supplier_id'], $data['stock_quantity']);

        return $product_id;
    }

    public function getDropshippingProducts($data = array()) {
        $sql = "SELECT dp.*, s.name as supplier_name FROM `" . DB_PREFIX . "dropshipping_products` dp ";
        $sql .= "LEFT JOIN `" . DB_PREFIX . "suppliers` s ON (dp.supplier_id = s.id)";

        $implode = array();

        if (!empty($data['filter_name'])) {
            $implode[] = "dp.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_category'])) {
            $implode[] = "dp.category = '" . $this->db->escape($data['filter_category']) . "'";
        }

        if (!empty($data['filter_status'])) {
            $implode[] = "dp.status = '" . $this->db->escape($data['filter_status']) . "'";
        }

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        $sql .= " ORDER BY dp.name ASC";

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
    }

    /**
     * 🛒 ORDER MANAGEMENT OPERATIONS
     */
    public function createDropshippingOrder($data) {
        $order_id = 'DS' . time() . rand(1000, 9999);

        $sql = "INSERT INTO `" . DB_PREFIX . "dropshipping_orders` SET ";
        $sql .= "`order_id` = '" . $order_id . "', ";
        $sql .= "`supplier_id` = '" . (int)$data['supplier_id'] . "', ";
        $sql .= "`customer_name` = '" . $this->db->escape($data['customer_name']) . "', ";
        $sql .= "`customer_email` = '" . $this->db->escape($data['customer_email']) . "', ";
        $sql .= "`shipping_address` = '" . $this->db->escape(json_encode($data['shipping_address'])) . "', ";
        $sql .= "`products` = '" . $this->db->escape(json_encode($data['products'])) . "', ";
        $sql .= "`subtotal` = '" . (float)$data['subtotal'] . "', ";
        $sql .= "`total_amount` = '" . (float)$data['total_amount'] . "', ";
        $sql .= "`profit_margin` = '" . (float)$data['profit_margin'] . "', ";
        $sql .= "`source_marketplace` = '" . $this->db->escape($data['source_marketplace']) . "'";

        $this->db->query($sql);
        $id = $this->db->getLastId();

        // Reserve stock for products
        foreach ($data['products'] as $product) {
            $this->reserveStock($product['product_id'], $product['quantity']);
        }

        $this->logger->write('MUSTI TEAM: New dropshipping order created - ID: ' . $order_id);

        return $order_id;
    }

    /**
     * 📊 PERFORMANCE ANALYTICS
     */
    public function calculateSupplierPerformance($supplier_id, $date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }

        // Get orders for the date
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as successful_orders,
                SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN order_status = 'returned' THEN 1 ELSE 0 END) as returned_orders,
                AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_processing_time,
                AVG(CASE WHEN actual_delivery IS NOT NULL THEN DATEDIFF(actual_delivery, created_at) END) as avg_shipping_time,
                SUM(total_amount) as total_revenue,
                SUM(profit_margin) as total_profit
            FROM `" . DB_PREFIX . "dropshipping_orders` 
            WHERE supplier_id = '" . (int)$supplier_id . "' 
            AND DATE(created_at) = '" . $this->db->escape($date) . "'
        ");

        $data = $query->row;

        // Calculate on-time delivery rate
        $on_time_query = $this->db->query("
            SELECT 
                (COUNT(CASE WHEN actual_delivery <= estimated_delivery THEN 1 END) / COUNT(*)) * 100 as on_time_rate
            FROM `" . DB_PREFIX . "dropshipping_orders` 
            WHERE supplier_id = '" . (int)$supplier_id . "' 
            AND DATE(created_at) = '" . $this->db->escape($date) . "'
            AND actual_delivery IS NOT NULL
        ");

        $on_time_rate = $on_time_query->row['on_time_rate'] ?? 0;

        // Insert/Update performance record
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "supplier_performance` 
            (supplier_id, date, total_orders, successful_orders, cancelled_orders, returned_orders, 
             avg_processing_time, avg_shipping_time, on_time_delivery_rate, total_revenue, total_profit)
            VALUES 
            ('" . (int)$supplier_id . "', '" . $this->db->escape($date) . "', 
             '" . (int)$data['total_orders'] . "', '" . (int)$data['successful_orders'] . "', 
             '" . (int)$data['cancelled_orders'] . "', '" . (int)$data['returned_orders'] . "',
             '" . (float)$data['avg_processing_time'] . "', '" . (float)$data['avg_shipping_time'] . "',
             '" . (float)$on_time_rate . "', '" . (float)$data['total_revenue'] . "', 
             '" . (float)$data['total_profit'] . "')
            ON DUPLICATE KEY UPDATE
                total_orders = VALUES(total_orders),
                successful_orders = VALUES(successful_orders),
                cancelled_orders = VALUES(cancelled_orders),
                returned_orders = VALUES(returned_orders),
                avg_processing_time = VALUES(avg_processing_time),
                avg_shipping_time = VALUES(avg_shipping_time),
                on_time_delivery_rate = VALUES(on_time_delivery_rate),
                total_revenue = VALUES(total_revenue),
                total_profit = VALUES(total_profit)
        ");

        return $data;
    }

    /**
     * 🔄 INVENTORY MANAGEMENT
     */
    private function initializeInventorySync($product_id, $supplier_id, $initial_stock) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "inventory_sync` 
            (product_id, supplier_id, current_stock, sync_status) 
            VALUES 
            ('" . (int)$product_id . "', '" . (int)$supplier_id . "', '" . (int)$initial_stock . "', 'success')
        ");
    }

    private function reserveStock($product_id, $quantity) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "inventory_sync` 
            SET reserved_stock = reserved_stock + '" . (int)$quantity . "' 
            WHERE product_id = '" . (int)$product_id . "'
        ");
    }

    public function syncInventory($product_id, $new_stock) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "inventory_sync` 
            SET current_stock = '" . (int)$new_stock . "', 
                last_sync = NOW(), 
                sync_status = 'success' 
            WHERE product_id = '" . (int)$product_id . "'
        ");

        $this->logger->write('MUSTI TEAM: Inventory synced for product ID: ' . $product_id);
    }

    /**
     * 📊 DASHBOARD ANALYTICS
     */
    public function getDashboardStats() {
        $stats = array();

        // Total suppliers
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "suppliers` WHERE status = 'active'");
        $stats['total_suppliers'] = $query->row['total'];

        // Total products
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "dropshipping_products` WHERE status = 'active'");
        $stats['total_products'] = $query->row['total'];

        // Today's orders
        $query = $this->db->query("SELECT COUNT(*) as total, SUM(total_amount) as revenue FROM `" . DB_PREFIX . "dropshipping_orders` WHERE DATE(created_at) = CURDATE()");
        $stats['today_orders'] = $query->row['total'];
        $stats['today_revenue'] = $query->row['revenue'] ?? 0;

        // Low stock alerts
        $query = $this->db->query("
            SELECT COUNT(*) as total FROM `" . DB_PREFIX . "inventory_sync` 
            WHERE available_stock <= reorder_level AND stock_alerts_enabled = 1
        ");
        $stats['low_stock_alerts'] = $query->row['total'];

        return $stats;
    }

    /**
     * 🔍 ADVANCED SEARCH AND FILTERS
     */
    public function searchProducts($keyword, $filters = array()) {
        $sql = "SELECT dp.*, s.name as supplier_name, inv.available_stock 
                FROM `" . DB_PREFIX . "dropshipping_products` dp 
                LEFT JOIN `" . DB_PREFIX . "suppliers` s ON (dp.supplier_id = s.id)
                LEFT JOIN `" . DB_PREFIX . "inventory_sync` inv ON (dp.id = inv.product_id)
                WHERE dp.status = 'active'";

        if ($keyword) {
            $sql .= " AND (dp.name LIKE '%" . $this->db->escape($keyword) . "%' 
                     OR dp.description LIKE '%" . $this->db->escape($keyword) . "%'
                     OR dp.sku LIKE '%" . $this->db->escape($keyword) . "%')";
        }

        if (!empty($filters['category'])) {
            $sql .= " AND dp.category = '" . $this->db->escape($filters['category']) . "'";
        }

        if (!empty($filters['price_min'])) {
            $sql .= " AND dp.cost_price >= '" . (float)$filters['price_min'] . "'";
        }

        if (!empty($filters['price_max'])) {
            $sql .= " AND dp.cost_price <= '" . (float)$filters['price_max'] . "'";
        }

        $sql .= " ORDER BY dp.name ASC LIMIT 50";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    /**
     * 🚀 MUSTI TEAM SUCCESS VALIDATION
     */
    public function validateMissionSuccess() {
        $validation = array(
            'tables_created' => $this->validateTablesExist(),
            'indexes_optimized' => $this->validateIndexes(),
            'data_integrity' => $this->validateDataIntegrity(),
            'performance_metrics' => $this->validatePerformance()
        );

        $this->logger->write('MUSTI TEAM: Mission validation completed - ' . json_encode($validation));
        
        return $validation;
    }

    private function validateTablesExist() {
        $required_tables = array(
            'suppliers', 'dropshipping_products', 'dropshipping_orders',
            'supplier_performance', 'inventory_sync', 'pricing_rules'
        );

        foreach ($required_tables as $table) {
            $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
            if (!$query->num_rows) {
                return false;
            }
        }

        return true;
    }

    private function validateIndexes() {
        // Check if critical indexes exist
        $query = $this->db->query("SHOW INDEX FROM `" . DB_PREFIX . "suppliers` WHERE Key_name = 'idx_status'");
        return $query->num_rows > 0;
    }

    private function validateDataIntegrity() {
        // Check foreign key constraints
        $query = $this->db->query("
            SELECT COUNT(*) as orphans 
            FROM `" . DB_PREFIX . "dropshipping_products` dp 
            LEFT JOIN `" . DB_PREFIX . "suppliers` s ON dp.supplier_id = s.id 
            WHERE s.id IS NULL
        ");
        
        return $query->row['orphans'] == 0;
    }

    private function validatePerformance() {
        // Test query performance
        $start_time = microtime(true);
        $this->db->query("SELECT COUNT(*) FROM `" . DB_PREFIX . "dropshipping_products`");
        $end_time = microtime(true);
        
        $execution_time = ($end_time - $start_time) * 1000; // milliseconds
        return $execution_time < 50; // Target: <50ms
    }
} 