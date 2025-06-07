<?php
/**
 * MesChain-Sync Hepsiburada Model
 * Hepsiburada Marketplace Integration Model
 * 
 * @package MesChain
 * @subpackage Hepsiburada
 * @author MezBjen Team - DevOps & Backend Enhancement Specialist
 * @version 3.0.4.0
 * @since June 7, 2025
 */

class ModelExtensionMestechHepsiburada extends Model {
    
    private $table_product = 'mestech_hepsiburada_product';
    private $table_order = 'mestech_hepsiburada_order';
    private $table_category = 'mestech_hepsiburada_category';
    private $table_log = 'mestech_hepsiburada_log';
    
    /**
     * Install database tables
     */
    public function install() {
        $this->createProductTable();
        $this->createOrderTable();
        $this->createCategoryTable();
        $this->createLogTable();
        
        $this->log->write('[HEPSIBURADA-MODEL] Database tables created successfully');
        return true;
    }
    
    /**
     * Uninstall database tables
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . $this->table_product . "`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . $this->table_order . "`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . $this->table_category . "`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . $this->table_log . "`");
        
        $this->log->write('[HEPSIBURADA-MODEL] Database tables dropped successfully');
        return true;
    }
    
    /**
     * Create product table
     */
    private function createProductTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->table_product . "` (
            `hepsiburada_product_id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `hepsiburada_id` varchar(255) DEFAULT NULL,
            `merchant_sku` varchar(255) NOT NULL,
            `barcode` varchar(255) DEFAULT NULL,
            `title` varchar(500) NOT NULL,
            `description` text,
            `brand` varchar(255) DEFAULT NULL,
            `category_id` int(11) DEFAULT NULL,
            `hepsiburada_category_id` varchar(255) DEFAULT NULL,
            `price` decimal(15,4) DEFAULT 0.0000,
            `list_price` decimal(15,4) DEFAULT 0.0000,
            `sale_price` decimal(15,4) DEFAULT 0.0000,
            `currency` varchar(3) DEFAULT 'TRY',
            `stock_quantity` int(11) DEFAULT 0,
            `min_stock_quantity` int(11) DEFAULT 0,
            `max_stock_quantity` int(11) DEFAULT 999999,
            `weight` decimal(8,2) DEFAULT 0.00,
            `dimensions_length` decimal(8,2) DEFAULT 0.00,
            `dimensions_width` decimal(8,2) DEFAULT 0.00,
            `dimensions_height` decimal(8,2) DEFAULT 0.00,
            `shipping_template` varchar(255) DEFAULT NULL,
            `delivery_option` varchar(100) DEFAULT 'standard',
            `fast_delivery` tinyint(1) DEFAULT 0,
            `same_day_delivery` tinyint(1) DEFAULT 0,
            `free_shipping` tinyint(1) DEFAULT 0,
            `images` text,
            `main_image` varchar(500) DEFAULT NULL,
            `additional_images` text,
            `attributes` text,
            `variants` text,
            `seo_title` varchar(255) DEFAULT NULL,
            `seo_description` text,
            `seo_keywords` varchar(500) DEFAULT NULL,
            `tags` varchar(500) DEFAULT NULL,
            `warranty_period` int(11) DEFAULT 0,
            `warranty_type` varchar(100) DEFAULT NULL,
            `return_policy` varchar(100) DEFAULT 'standard',
            `age_restriction` int(11) DEFAULT 0,
            `hazardous_material` tinyint(1) DEFAULT 0,
            `origin_country` varchar(100) DEFAULT 'TR',
            `manufacturer` varchar(255) DEFAULT NULL,
            `model_number` varchar(255) DEFAULT NULL,
            `color` varchar(100) DEFAULT NULL,
            `size` varchar(100) DEFAULT NULL,
            `material` varchar(255) DEFAULT NULL,
            `gender` varchar(50) DEFAULT NULL,
            `age_group` varchar(50) DEFAULT NULL,
            `season` varchar(50) DEFAULT NULL,
            `collection` varchar(255) DEFAULT NULL,
            `pattern` varchar(100) DEFAULT NULL,
            `style` varchar(100) DEFAULT NULL,
            `occasion` varchar(100) DEFAULT NULL,
            `care_instructions` text,
            `ingredients` text,
            `nutritional_info` text,
            `allergen_info` text,
            `expiry_date` date DEFAULT NULL,
            `batch_number` varchar(255) DEFAULT NULL,
            `commission_rate` decimal(5,2) DEFAULT 0.00,
            `listing_fee` decimal(10,4) DEFAULT 0.0000,
            `service_fee` decimal(10,4) DEFAULT 0.0000,
            `status` varchar(50) DEFAULT 'draft',
            `approval_status` varchar(50) DEFAULT 'pending',
            `visibility` varchar(50) DEFAULT 'visible',
            `featured` tinyint(1) DEFAULT 0,
            `promotion_eligible` tinyint(1) DEFAULT 1,
            `last_sync_date` datetime DEFAULT NULL,
            `sync_status` varchar(50) DEFAULT 'pending',
            `sync_errors` text,
            `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
            `modified_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`hepsiburada_product_id`),
            UNIQUE KEY `product_merchant_sku` (`product_id`, `merchant_sku`),
            KEY `idx_product_id` (`product_id`),
            KEY `idx_hepsiburada_id` (`hepsiburada_id`),
            KEY `idx_merchant_sku` (`merchant_sku`),
            KEY `idx_status` (`status`),
            KEY `idx_sync_status` (`sync_status`),
            KEY `idx_last_sync` (`last_sync_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->query($sql);
    }
    
    /**
     * Create order table
     */
    private function createOrderTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->table_order . "` (
            `hepsiburada_order_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) DEFAULT NULL,
            `hepsiburada_order_number` varchar(255) NOT NULL,
            `package_number` varchar(255) DEFAULT NULL,
            `customer_id` int(11) DEFAULT NULL,
            `customer_email` varchar(255) DEFAULT NULL,
            `customer_name` varchar(255) DEFAULT NULL,
            `customer_phone` varchar(50) DEFAULT NULL,
            `billing_address` text,
            `shipping_address` text,
            `billing_city` varchar(100) DEFAULT NULL,
            `billing_district` varchar(100) DEFAULT NULL,
            `billing_postal_code` varchar(20) DEFAULT NULL,
            `shipping_city` varchar(100) DEFAULT NULL,
            `shipping_district` varchar(100) DEFAULT NULL,
            `shipping_postal_code` varchar(20) DEFAULT NULL,
            `order_date` datetime DEFAULT NULL,
            `ship_by_date` datetime DEFAULT NULL,
            `delivery_date` datetime DEFAULT NULL,
            `estimated_delivery` datetime DEFAULT NULL,
            `order_status` varchar(50) DEFAULT 'new',
            `payment_status` varchar(50) DEFAULT 'pending',
            `shipping_status` varchar(50) DEFAULT 'not_shipped',
            `fulfillment_status` varchar(50) DEFAULT 'unfulfilled',
            `payment_method` varchar(100) DEFAULT NULL,
            `payment_date` datetime DEFAULT NULL,
            `currency` varchar(3) DEFAULT 'TRY',
            `subtotal` decimal(15,4) DEFAULT 0.0000,
            `tax_amount` decimal(15,4) DEFAULT 0.0000,
            `shipping_cost` decimal(15,4) DEFAULT 0.0000,
            `discount_amount` decimal(15,4) DEFAULT 0.0000,
            `commission_amount` decimal(15,4) DEFAULT 0.0000,
            `service_fee` decimal(15,4) DEFAULT 0.0000,
            `total_amount` decimal(15,4) DEFAULT 0.0000,
            `items` text,
            `item_count` int(11) DEFAULT 0,
            `shipping_method` varchar(100) DEFAULT NULL,
            `shipping_company` varchar(100) DEFAULT NULL,
            `tracking_number` varchar(255) DEFAULT NULL,
            `tracking_url` varchar(500) DEFAULT NULL,
            `delivery_type` varchar(50) DEFAULT 'standard',
            `fast_delivery` tinyint(1) DEFAULT 0,
            `same_day_delivery` tinyint(1) DEFAULT 0,
            `cargo_company` varchar(100) DEFAULT NULL,
            `cargo_tracking_number` varchar(255) DEFAULT NULL,
            `cargo_status` varchar(50) DEFAULT NULL,
            `invoice_number` varchar(255) DEFAULT NULL,
            `invoice_date` datetime DEFAULT NULL,
            `invoice_amount` decimal(15,4) DEFAULT 0.0000,
            `return_status` varchar(50) DEFAULT 'none',
            `return_reason` varchar(500) DEFAULT NULL,
            `return_date` datetime DEFAULT NULL,
            `refund_amount` decimal(15,4) DEFAULT 0.0000,
            `refund_date` datetime DEFAULT NULL,
            `notes` text,
            `internal_notes` text,
            `priority` varchar(20) DEFAULT 'normal',
            `source` varchar(50) DEFAULT 'hepsiburada',
            `marketplace_fees` decimal(15,4) DEFAULT 0.0000,
            `profit_margin` decimal(15,4) DEFAULT 0.0000,
            `last_sync_date` datetime DEFAULT NULL,
            `sync_status` varchar(50) DEFAULT 'pending',
            `sync_errors` text,
            `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
            `modified_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`hepsiburada_order_id`),
            UNIQUE KEY `hepsiburada_order_number` (`hepsiburada_order_number`),
            KEY `idx_order_id` (`order_id`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_order_status` (`order_status`),
            KEY `idx_payment_status` (`payment_status`),
            KEY `idx_shipping_status` (`shipping_status`),
            KEY `idx_order_date` (`order_date`),
            KEY `idx_sync_status` (`sync_status`),
            KEY `idx_last_sync` (`last_sync_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->query($sql);
    }
    
    /**
     * Create category table
     */
    private function createCategoryTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->table_category . "` (
            `hepsiburada_category_id` int(11) NOT NULL AUTO_INCREMENT,
            `category_id` int(11) DEFAULT NULL,
            `hepsiburada_id` varchar(255) NOT NULL,
            `parent_id` varchar(255) DEFAULT NULL,
            `name` varchar(255) NOT NULL,
            `path` varchar(1000) DEFAULT NULL,
            `level` int(11) DEFAULT 0,
            `commission_rate` decimal(5,2) DEFAULT 0.00,
            `attributes` text,
            `requirements` text,
            `status` varchar(50) DEFAULT 'active',
            `last_sync_date` datetime DEFAULT NULL,
            `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
            `modified_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`hepsiburada_category_id`),
            UNIQUE KEY `hepsiburada_id` (`hepsiburada_id`),
            KEY `idx_category_id` (`category_id`),
            KEY `idx_parent_id` (`parent_id`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->query($sql);
    }
    
    /**
     * Create log table
     */
    private function createLogTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->table_log . "` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `operation_type` varchar(100) NOT NULL,
            `operation_id` varchar(255) DEFAULT NULL,
            `level` varchar(20) DEFAULT 'info',
            `message` text NOT NULL,
            `request_data` longtext,
            `response_data` longtext,
            `execution_time` decimal(8,3) DEFAULT 0.000,
            `memory_usage` int(11) DEFAULT 0,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` varchar(500) DEFAULT NULL,
            `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`log_id`),
            KEY `idx_operation_type` (`operation_type`),
            KEY `idx_level` (`level`),
            KEY `idx_created_date` (`created_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->query($sql);
    }
    
    // PRODUCT CRUD OPERATIONS
    
    /**
     * Add product
     */
    public function addProduct($data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->table_product . "` SET ";
        $sql .= "`product_id` = '" . (int)$data['product_id'] . "', ";
        $sql .= "`merchant_sku` = '" . $this->db->escape($data['merchant_sku']) . "', ";
        $sql .= "`title` = '" . $this->db->escape($data['title']) . "', ";
        $sql .= "`description` = '" . $this->db->escape($data['description'] ?? '') . "', ";
        $sql .= "`brand` = '" . $this->db->escape($data['brand'] ?? '') . "', ";
        $sql .= "`price` = '" . (float)($data['price'] ?? 0) . "', ";
        $sql .= "`stock_quantity` = '" . (int)($data['stock_quantity'] ?? 0) . "', ";
        $sql .= "`status` = '" . $this->db->escape($data['status'] ?? 'draft') . "', ";
        $sql .= "`created_date` = NOW()";
        
        $this->db->query($sql);
        return $this->db->getLastId();
    }
    
    /**
     * Update product
     */
    public function updateProduct($hepsiburada_product_id, $data) {
        $sql = "UPDATE `" . DB_PREFIX . $this->table_product . "` SET ";
        
        $updates = [];
        foreach ($data as $key => $value) {
            if (in_array($key, ['title', 'description', 'brand', 'status', 'merchant_sku'])) {
                $updates[] = "`{$key}` = '" . $this->db->escape($value) . "'";
            } elseif (in_array($key, ['price', 'list_price', 'sale_price'])) {
                $updates[] = "`{$key}` = '" . (float)$value . "'";
            } elseif (in_array($key, ['stock_quantity', 'product_id'])) {
                $updates[] = "`{$key}` = '" . (int)$value . "'";
            }
        }
        
        if (!empty($updates)) {
            $sql .= implode(', ', $updates) . ", ";
            $sql .= "`modified_date` = NOW() ";
            $sql .= "WHERE `hepsiburada_product_id` = '" . (int)$hepsiburada_product_id . "'";
            
            $this->db->query($sql);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get product
     */
    public function getProduct($hepsiburada_product_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . $this->table_product . "` WHERE `hepsiburada_product_id` = '" . (int)$hepsiburada_product_id . "'");
        return $query->row;
    }
    
    /**
     * Get products
     */
    public function getProducts($data = []) {
        $sql = "SELECT hp.*, p.name as opencart_name, p.model, p.price as opencart_price, p.quantity as opencart_quantity 
                FROM `" . DB_PREFIX . $this->table_product . "` hp 
                LEFT JOIN `" . DB_PREFIX . "product` p ON hp.product_id = p.product_id 
                WHERE 1=1";
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND hp.status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_sync_status'])) {
            $sql .= " AND hp.sync_status = '" . $this->db->escape($data['filter_sync_status']) . "'";
        }
        
        if (!empty($data['filter_name'])) {
            $sql .= " AND (hp.title LIKE '%" . $this->db->escape($data['filter_name']) . "%' OR p.name LIKE '%" . $this->db->escape($data['filter_name']) . "%')";
        }
        
        $sql .= " ORDER BY hp.created_date DESC";
        
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
     * Get total products
     */
    public function getTotalProducts($data = []) {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . $this->table_product . "` hp WHERE 1=1";
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND hp.status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_sync_status'])) {
            $sql .= " AND hp.sync_status = '" . $this->db->escape($data['filter_sync_status']) . "'";
        }
        
        if (!empty($data['filter_name'])) {
            $sql .= " AND hp.title LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }
        
        $query = $this->db->query($sql);
        return $query->row['total'];
    }
    
    /**
     * Delete product
     */
    public function deleteProduct($hepsiburada_product_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . $this->table_product . "` WHERE `hepsiburada_product_id` = '" . (int)$hepsiburada_product_id . "'");
        return true;
    }
    
    // ORDER CRUD OPERATIONS
    
    /**
     * Add order
     */
    public function addOrder($data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->table_order . "` SET ";
        $sql .= "`hepsiburada_order_number` = '" . $this->db->escape($data['hepsiburada_order_number']) . "', ";
        $sql .= "`customer_name` = '" . $this->db->escape($data['customer_name'] ?? '') . "', ";
        $sql .= "`customer_email` = '" . $this->db->escape($data['customer_email'] ?? '') . "', ";
        $sql .= "`order_status` = '" . $this->db->escape($data['order_status'] ?? 'new') . "', ";
        $sql .= "`total_amount` = '" . (float)($data['total_amount'] ?? 0) . "', ";
        $sql .= "`currency` = '" . $this->db->escape($data['currency'] ?? 'TRY') . "', ";
        $sql .= "`order_date` = '" . $this->db->escape($data['order_date'] ?? date('Y-m-d H:i:s')) . "', ";
        $sql .= "`created_date` = NOW()";
        
        $this->db->query($sql);
        return $this->db->getLastId();
    }
    
    /**
     * Update order
     */
    public function updateOrder($hepsiburada_order_id, $data) {
        $sql = "UPDATE `" . DB_PREFIX . $this->table_order . "` SET ";
        
        $updates = [];
        foreach ($data as $key => $value) {
            if (in_array($key, ['order_status', 'payment_status', 'shipping_status', 'tracking_number'])) {
                $updates[] = "`{$key}` = '" . $this->db->escape($value) . "'";
            } elseif (in_array($key, ['total_amount', 'shipping_cost', 'tax_amount'])) {
                $updates[] = "`{$key}` = '" . (float)$value . "'";
            }
        }
        
        if (!empty($updates)) {
            $sql .= implode(', ', $updates) . ", ";
            $sql .= "`modified_date` = NOW() ";
            $sql .= "WHERE `hepsiburada_order_id` = '" . (int)$hepsiburada_order_id . "'";
            
            $this->db->query($sql);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get order
     */
    public function getOrder($hepsiburada_order_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . $this->table_order . "` WHERE `hepsiburada_order_id` = '" . (int)$hepsiburada_order_id . "'");
        return $query->row;
    }
    
    /**
     * Get orders
     */
    public function getOrders($data = []) {
        $sql = "SELECT ho.*, o.order_id as opencart_order_id 
                FROM `" . DB_PREFIX . $this->table_order . "` ho 
                LEFT JOIN `" . DB_PREFIX . "order` o ON ho.order_id = o.order_id 
                WHERE 1=1";
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND ho.order_status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_payment_status'])) {
            $sql .= " AND ho.payment_status = '" . $this->db->escape($data['filter_payment_status']) . "'";
        }
        
        if (!empty($data['filter_order_number'])) {
            $sql .= " AND ho.hepsiburada_order_number LIKE '%" . $this->db->escape($data['filter_order_number']) . "%'";
        }
        
        $sql .= " ORDER BY ho.order_date DESC";
        
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
     * Get total orders
     */
    public function getTotalOrders($data = []) {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . $this->table_order . "` ho WHERE 1=1";
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND ho.order_status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_payment_status'])) {
            $sql .= " AND ho.payment_status = '" . $this->db->escape($data['filter_payment_status']) . "'";
        }
        
        $query = $this->db->query($sql);
        return $query->row['total'];
    }
    
    // CATEGORY OPERATIONS
    
    /**
     * Add category
     */
    public function addCategory($data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->table_category . "` SET ";
        $sql .= "`hepsiburada_id` = '" . $this->db->escape($data['hepsiburada_id']) . "', ";
        $sql .= "`name` = '" . $this->db->escape($data['name']) . "', ";
        $sql .= "`parent_id` = '" . $this->db->escape($data['parent_id'] ?? '') . "', ";
        $sql .= "`level` = '" . (int)($data['level'] ?? 0) . "', ";
        $sql .= "`commission_rate` = '" . (float)($data['commission_rate'] ?? 0) . "', ";
        $sql .= "`created_date` = NOW()";
        
        $this->db->query($sql);
        return $this->db->getLastId();
    }
    
    /**
     * Get categories
     */
    public function getCategories() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . $this->table_category . "` ORDER BY `level`, `name`");
        return $query->rows;
    }
    
    // LOG OPERATIONS
    
    /**
     * Add log
     */
    public function addLog($operation_type, $message, $level = 'info', $data = []) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->table_log . "` SET ";
        $sql .= "`operation_type` = '" . $this->db->escape($operation_type) . "', ";
        $sql .= "`level` = '" . $this->db->escape($level) . "', ";
        $sql .= "`message` = '" . $this->db->escape($message) . "', ";
        $sql .= "`request_data` = '" . $this->db->escape(json_encode($data['request'] ?? [])) . "', ";
        $sql .= "`response_data` = '" . $this->db->escape(json_encode($data['response'] ?? [])) . "', ";
        $sql .= "`execution_time` = '" . (float)($data['execution_time'] ?? 0) . "', ";
        $sql .= "`ip_address` = '" . $this->db->escape($_SERVER['REMOTE_ADDR'] ?? '') . "', ";
        $sql .= "`created_date` = NOW()";
        
        $this->db->query($sql);
        return $this->db->getLastId();
    }
    
    /**
     * Get logs
     */
    public function getLogs($data = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->table_log . "` WHERE 1=1";
        
        if (!empty($data['filter_operation'])) {
            $sql .= " AND operation_type = '" . $this->db->escape($data['filter_operation']) . "'";
        }
        
        if (!empty($data['filter_level'])) {
            $sql .= " AND level = '" . $this->db->escape($data['filter_level']) . "'";
        }
        
        $sql .= " ORDER BY created_date DESC";
        
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
    }
    
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $stats = [];
        
        // Product statistics
        $query = $this->db->query("SELECT 
            COUNT(*) as total_products,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_products,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_products,
            SUM(CASE WHEN sync_status = 'synced' THEN 1 ELSE 0 END) as synced_products,
            SUM(CASE WHEN sync_status = 'error' THEN 1 ELSE 0 END) as error_products
            FROM `" . DB_PREFIX . $this->table_product . "`");
        $stats['products'] = $query->row;
        
        // Order statistics
        $query = $this->db->query("SELECT 
            COUNT(*) as total_orders,
            SUM(CASE WHEN order_status = 'new' THEN 1 ELSE 0 END) as new_orders,
            SUM(CASE WHEN order_status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
            SUM(CASE WHEN order_status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
            SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
            SUM(total_amount) as total_revenue,
            AVG(total_amount) as average_order_value
            FROM `" . DB_PREFIX . $this->table_order . "`");
        $stats['orders'] = $query->row;
        
        // Recent activity
        $query = $this->db->query("SELECT COUNT(*) as recent_logs FROM `" . DB_PREFIX . $this->table_log . "` WHERE created_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stats['recent_activity'] = $query->row['recent_logs'];
        
        return $stats;
    }
    
    /**
     * Sync product with Hepsiburada
     */
    public function syncProduct($hepsiburada_product_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . $this->table_product . "` SET 
            `sync_status` = 'syncing', 
            `last_sync_date` = NOW() 
            WHERE `hepsiburada_product_id` = '" . (int)$hepsiburada_product_id . "'");
        
        return true;
    }
    
    /**
     * Update sync status
     */
    public function updateSyncStatus($table, $id, $status, $errors = null) {
        $sql = "UPDATE `" . DB_PREFIX . $table . "` SET 
                `sync_status` = '" . $this->db->escape($status) . "', 
                `last_sync_date` = NOW()";
        
        if ($errors) {
            $sql .= ", `sync_errors` = '" . $this->db->escape(json_encode($errors)) . "'";
        }
        
        if ($table == $this->table_product) {
            $sql .= " WHERE `hepsiburada_product_id` = '" . (int)$id . "'";
        } elseif ($table == $this->table_order) {
            $sql .= " WHERE `hepsiburada_order_id` = '" . (int)$id . "'";
        }
        
        $this->db->query($sql);
        return true;
    }
    
    /**
     * Clear old logs
     */
    public function clearOldLogs($days = 30) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . $this->table_log . "` WHERE created_date < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)");
        return $this->db->countAffected();
    }
}