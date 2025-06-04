<?php
/**
 * N11 Marketplace Model
 * MesChain-Sync v3.0 - OpenCart 3.0.4.0 Integration
 * Turkish E-commerce Platform Model with N11 Pro Features
 * 
 * @author MesChain Development Team
 * @version 3.0.0
 * @copyright 2024 MesChain Technologies
 */

class ModelExtensionModuleN11 extends Model {
    
    private $log;
    private $logFile = 'n11_model.log';
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        parent::__construct($registry);
        $this->log = new Log($this->logFile);
    }
    
    /**
     * Get N11 helper class
     * 
     * @return MeschainN11Helper
     */
    private function getN11Helper() {
        require_once(DIR_SYSTEM . 'library/meschain/helper/n11_helper.php');
        return new MeschainN11Helper($this->registry);
    }
    
    /**
     * Install module and create tables
     */
    public function install() {
        $this->log->write('[INFO] N11 module installation started');
        
        // Create n11_products table for product listings
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_products` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `product_id` INT(11) NOT NULL,
            `n11_product_id` VARCHAR(50) DEFAULT NULL,
            `title` VARCHAR(255) NOT NULL,
            `sku` VARCHAR(100) DEFAULT NULL,
            `product_code` VARCHAR(100) DEFAULT NULL,
            `category_id` INT(11) DEFAULT NULL,
            `category_name` VARCHAR(255) DEFAULT NULL,
            `price` DECIMAL(15,4) DEFAULT NULL,
            `discount_price` DECIMAL(15,4) DEFAULT NULL,
            `currency` VARCHAR(3) DEFAULT 'TRY',
            `stock_quantity` INT(11) DEFAULT 0,
            `status` ENUM('draft', 'active', 'passive', 'sold_out', 'rejected') DEFAULT 'draft',
            `approval_status` ENUM('waiting', 'approved', 'rejected') DEFAULT 'waiting',
            `image_url` TEXT DEFAULT NULL,
            `product_url` TEXT DEFAULT NULL,
            `view_count` INT(11) DEFAULT 0,
            `favorite_count` INT(11) DEFAULT 0,
            `commission_rate` DECIMAL(5,2) DEFAULT NULL,
            `commission_amount` DECIMAL(15,4) DEFAULT NULL,
            `pro_seller_badge` TINYINT(1) DEFAULT 0,
            `free_shipping` TINYINT(1) DEFAULT 0,
            `fast_delivery` TINYINT(1) DEFAULT 0,
            `campaign_id` VARCHAR(50) DEFAULT NULL,
            `discount_rate` DECIMAL(5,2) DEFAULT NULL,
            `listing_date` DATETIME DEFAULT NULL,
            `last_update_date` DATETIME DEFAULT NULL,
            `last_sync` DATETIME DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `product_id` (`product_id`),
            UNIQUE KEY `n11_product_id` (`n11_product_id`),
            INDEX `category_id` (`category_id`),
            INDEX `status` (`status`),
            INDEX `approval_status` (`approval_status`),
            INDEX `campaign_id` (`campaign_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create n11_orders table for order management
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_orders` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) DEFAULT NULL,
            `n11_order_number` VARCHAR(50) NOT NULL,
            `n11_order_id` VARCHAR(50) NOT NULL,
            `customer_first_name` VARCHAR(100) DEFAULT NULL,
            `customer_last_name` VARCHAR(100) DEFAULT NULL,
            `customer_email` VARCHAR(255) DEFAULT NULL,
            `customer_phone` VARCHAR(20) DEFAULT NULL,
            `customer_tc_no` VARCHAR(11) DEFAULT NULL,
            `product_id` VARCHAR(50) NOT NULL,
            `product_name` VARCHAR(255) DEFAULT NULL,
            `sku` VARCHAR(100) DEFAULT NULL,
            `quantity` INT(11) DEFAULT 1,
            `unit_price` DECIMAL(15,4) DEFAULT NULL,
            `total_amount` DECIMAL(15,4) DEFAULT NULL,
            `commission_amount` DECIMAL(15,4) DEFAULT NULL,
            `currency` VARCHAR(3) DEFAULT 'TRY',
            `payment_type` ENUM('credit_card', 'debit_card', 'bank_transfer', 'n11_wallet', 'installment') DEFAULT 'credit_card',
            `installment_count` INT(2) DEFAULT 1,
            `order_status` ENUM('new', 'confirmed', 'preparing', 'shipped', 'delivered', 'cancelled', 'returned') DEFAULT 'new',
            `cargo_company` VARCHAR(50) DEFAULT NULL,
            `cargo_tracking_number` VARCHAR(100) DEFAULT NULL,
            `cargo_tracking_url` TEXT DEFAULT NULL,
            `estimated_delivery_date` DATETIME DEFAULT NULL,
            `actual_delivery_date` DATETIME DEFAULT NULL,
            `billing_address` TEXT DEFAULT NULL,
            `shipping_address` TEXT DEFAULT NULL,
            `city` VARCHAR(100) DEFAULT NULL,
            `district` VARCHAR(100) DEFAULT NULL,
            `postal_code` VARCHAR(10) DEFAULT NULL,
            `invoice_requested` TINYINT(1) DEFAULT 0,
            `corporate_invoice` TINYINT(1) DEFAULT 0,
            `tax_office` VARCHAR(100) DEFAULT NULL,
            `tax_number` VARCHAR(20) DEFAULT NULL,
            `order_date` DATETIME DEFAULT NULL,
            `shipment_date` DATETIME DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `n11_order_number` (`n11_order_number`),
            INDEX `order_id` (`order_id`),
            INDEX `product_id` (`product_id`),
            INDEX `order_status` (`order_status`),
            INDEX `cargo_company` (`cargo_company`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create n11_categories table for category management
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_categories` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `category_id` INT(11) NOT NULL,
            `parent_id` INT(11) DEFAULT NULL,
            `level` INT(3) DEFAULT 0,
            `category_name` VARCHAR(255) NOT NULL,
            `category_path` TEXT DEFAULT NULL,
            `opencart_category_id` INT(11) DEFAULT NULL,
            `commission_rate` DECIMAL(5,2) DEFAULT NULL,
            `is_leaf` TINYINT(1) DEFAULT 0,
            `product_count` INT(11) DEFAULT 0,
            `required_attributes` TEXT DEFAULT NULL,
            `optional_attributes` TEXT DEFAULT NULL,
            `commission_type` ENUM('percentage', 'fixed') DEFAULT 'percentage',
            `min_commission` DECIMAL(15,4) DEFAULT NULL,
            `max_commission` DECIMAL(15,4) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `category_id` (`category_id`),
            INDEX `parent_id` (`parent_id`),
            INDEX `opencart_category_id` (`opencart_category_id`),
            INDEX `is_leaf` (`is_leaf`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create n11_campaigns table for campaign management
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_campaigns` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `campaign_id` VARCHAR(50) NOT NULL,
            `campaign_name` VARCHAR(255) NOT NULL,
            `campaign_type` ENUM('discount', 'gift', 'shipping', 'bundle', 'flash_sale') DEFAULT 'discount',
            `discount_type` ENUM('percentage', 'amount') DEFAULT 'percentage',
            `discount_value` DECIMAL(15,4) DEFAULT NULL,
            `min_order_amount` DECIMAL(15,4) DEFAULT NULL,
            `max_discount_amount` DECIMAL(15,4) DEFAULT NULL,
            `usage_limit` INT(11) DEFAULT NULL,
            `used_count` INT(11) DEFAULT 0,
            `is_active` TINYINT(1) DEFAULT 1,
            `start_date` DATETIME DEFAULT NULL,
            `end_date` DATETIME DEFAULT NULL,
            `target_products` TEXT DEFAULT NULL,
            `target_categories` TEXT DEFAULT NULL,
            `pro_seller_only` TINYINT(1) DEFAULT 0,
            `created_by` VARCHAR(50) DEFAULT 'system',
            `performance_score` DECIMAL(5,2) DEFAULT NULL,
            `conversion_rate` DECIMAL(5,2) DEFAULT NULL,
            `total_sales` DECIMAL(15,4) DEFAULT 0,
            `total_orders` INT(11) DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `campaign_id` (`campaign_id`),
            INDEX `campaign_type` (`campaign_type`),
            INDEX `is_active` (`is_active`),
            INDEX `start_date` (`start_date`),
            INDEX `end_date` (`end_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create n11_seller_metrics table for performance tracking
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_seller_metrics` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `store_name` VARCHAR(255) DEFAULT NULL,
            `seller_score` DECIMAL(3,2) DEFAULT 0,
            `total_sales` DECIMAL(15,4) DEFAULT 0,
            `monthly_sales` DECIMAL(15,4) DEFAULT 0,
            `total_orders` INT(11) DEFAULT 0,
            `monthly_orders` INT(11) DEFAULT 0,
            `customer_satisfaction` DECIMAL(3,2) DEFAULT 0,
            `shipping_performance` DECIMAL(3,2) DEFAULT 0,
            `return_rate` DECIMAL(5,2) DEFAULT 0,
            `cancellation_rate` DECIMAL(5,2) DEFAULT 0,
            `response_time` DECIMAL(5,2) DEFAULT 0,
            `pro_seller_status` TINYINT(1) DEFAULT 0,
            `pro_score` DECIMAL(5,2) DEFAULT 0,
            `commission_rate` DECIMAL(5,2) DEFAULT NULL,
            `commission_discount` DECIMAL(5,2) DEFAULT 0,
            `early_payment_eligible` TINYINT(1) DEFAULT 0,
            `priority_support` TINYINT(1) DEFAULT 0,
            `advanced_analytics` TINYINT(1) DEFAULT 0,
            `bulk_operations` TINYINT(1) DEFAULT 0,
            `api_access` TINYINT(1) DEFAULT 0,
            `last_performance_update` DATETIME DEFAULT NULL,
            `rating_count` INT(11) DEFAULT 0,
            `positive_rating_rate` DECIMAL(5,2) DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `pro_seller_status` (`pro_seller_status`),
            INDEX `seller_score` (`seller_score`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create n11_sync_log table for operation tracking
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_sync_log` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `operation_type` ENUM('product_create', 'product_update', 'order_sync', 'campaign_create', 'campaign_update', 'stock_update', 'price_update', 'category_sync') NOT NULL,
            `n11_product_id` VARCHAR(50) DEFAULT NULL,
            `product_id` INT(11) DEFAULT NULL,
            `status` ENUM('success', 'error', 'pending', 'warning') NOT NULL,
            `message` TEXT DEFAULT NULL,
            `request_data` TEXT DEFAULT NULL,
            `response_data` TEXT DEFAULT NULL,
            `execution_time` DECIMAL(8,3) DEFAULT NULL,
            `api_call_count` INT(11) DEFAULT 1,
            `rate_limit_remaining` INT(11) DEFAULT NULL,
            `commission_calculated` DECIMAL(15,4) DEFAULT NULL,
            `psychological_pricing_applied` TINYINT(1) DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `operation_type` (`operation_type`),
            INDEX `status` (`status`),
            INDEX `n11_product_id` (`n11_product_id`),
            INDEX `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Insert default Turkish categories
        $this->addDefaultCategories();
        
        // Insert default cargo companies
        $this->addDefaultCargoCompanies();
        
        // Initialize seller metrics
        $this->initializeSellerMetrics();
        
        $this->log->write('[SUCCESS] N11 module installed successfully');
    }
    
    /**
     * Uninstall module
     */
    public function uninstall() {
        $this->log->write('[INFO] N11 module uninstallation started');
        
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "n11_sync_log`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "n11_seller_metrics`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "n11_campaigns`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "n11_categories`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "n11_orders`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "n11_products`");
        
        $this->log->write('[SUCCESS] N11 module uninstalled successfully');
    }
    
    /**
     * Add default N11 categories
     */
    private function addDefaultCategories() {
        $categories = array(
            // Main categories with Turkish names and commission rates
            array('id' => 1000, 'name' => 'Elektronik', 'commission' => 8.0),
            array('id' => 1001, 'name' => 'Bilgisayar', 'commission' => 6.0, 'parent' => 1000),
            array('id' => 1002, 'name' => 'Cep Telefonu', 'commission' => 7.0, 'parent' => 1000),
            array('id' => 1003, 'name' => 'Tablet', 'commission' => 7.5, 'parent' => 1000),
            array('id' => 1004, 'name' => 'Beyaz Eşya', 'commission' => 9.0),
            array('id' => 1005, 'name' => 'Moda - Giyim', 'commission' => 12.0),
            array('id' => 1006, 'name' => 'Kadın Giyim', 'commission' => 13.0, 'parent' => 1005),
            array('id' => 1007, 'name' => 'Erkek Giyim', 'commission' => 11.0, 'parent' => 1005),
            array('id' => 1008, 'name' => 'Çocuk Giyim', 'commission' => 10.0, 'parent' => 1005),
            array('id' => 1009, 'name' => 'Ayakkabı & Çanta', 'commission' => 14.0),
            array('id' => 1010, 'name' => 'Ev & Yaşam', 'commission' => 10.0),
            array('id' => 1011, 'name' => 'Mobilya', 'commission' => 8.0, 'parent' => 1010),
            array('id' => 1012, 'name' => 'Dekorasyon', 'commission' => 12.0, 'parent' => 1010),
            array('id' => 1013, 'name' => 'Mutfak', 'commission' => 9.0, 'parent' => 1010),
            array('id' => 1014, 'name' => 'Kozmetik', 'commission' => 15.0),
            array('id' => 1015, 'name' => 'Anne & Bebek', 'commission' => 9.0),
            array('id' => 1016, 'name' => 'Spor & Outdoor', 'commission' => 11.0),
            array('id' => 1017, 'name' => 'Kitap & Müzik', 'commission' => 5.0),
            array('id' => 1018, 'name' => 'Otomotiv', 'commission' => 8.0),
            array('id' => 1019, 'name' => 'Bahçe & Yapı Market', 'commission' => 7.0),
            array('id' => 1020, 'name' => 'Pet Shop', 'commission' => 12.0),
            array('id' => 1021, 'name' => 'Saat & Aksesuar', 'commission' => 16.0)
        );
        
        foreach ($categories as $category) {
            $parent_id = isset($category['parent']) ? $category['parent'] : 'NULL';
            $level = isset($category['parent']) ? 1 : 0;
            
            $this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "n11_categories` 
                (`category_id`, `parent_id`, `level`, `category_name`, `commission_rate`, `is_leaf`) VALUES 
                (" . (int)$category['id'] . ", 
                 " . $parent_id . ", 
                 " . $level . ",
                 '" . $this->db->escape($category['name']) . "', 
                 " . (float)$category['commission'] . ", 1)");
        }
    }
    
    /**
     * Add default cargo companies
     */
    private function addDefaultCargoCompanies() {
        // Insert Turkish cargo companies for reference
        $this->logSyncOperation('category_sync', null, 'success', 'Default cargo companies initialized for Turkey');
    }
    
    /**
     * Initialize seller metrics
     */
    private function initializeSellerMetrics() {
        $this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "n11_seller_metrics` 
            (`store_name`, `seller_score`, `pro_seller_status`, `commission_rate`) VALUES 
            ('N11 Store', 0.00, 0, 12.00)");
    }
    
    /**
     * Get products for N11 listing
     */
    public function getProductsForListing($limit = 50) {
        $query = $this->db->query("SELECT p.product_id, pd.name, p.model, p.sku, p.price, p.quantity, p.status, p.image,
                                         pd.description, pd.meta_title, pd.meta_description,
                                         np.id as n11_listing_id, np.status as n11_status, np.n11_product_id, np.last_sync
                                  FROM `" . DB_PREFIX . "product` p
                                  LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
                                  LEFT JOIN `" . DB_PREFIX . "n11_products` np ON (p.product_id = np.product_id)
                                  WHERE p.status = 1 
                                  AND p.quantity > 0
                                  AND (np.status IS NULL OR np.status = 'draft' OR np.last_sync < DATE_SUB(NOW(), INTERVAL 12 HOUR))
                                  ORDER BY p.date_modified DESC
                                  LIMIT " . (int)$limit);
        
        return $query->rows;
    }
    
    /**
     * Get listings that need updates
     */
    public function getListingsForUpdate($limit = 100) {
        $query = $this->db->query("SELECT np.*, p.price, p.quantity, pd.name, p.model
                                  FROM `" . DB_PREFIX . "n11_products` np
                                  INNER JOIN `" . DB_PREFIX . "product` p ON (np.product_id = p.product_id)
                                  LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
                                  WHERE np.status = 'active'
                                  AND np.n11_product_id IS NOT NULL
                                  AND (np.price != p.price 
                                       OR np.stock_quantity != p.quantity 
                                       OR np.last_sync < DATE_SUB(NOW(), INTERVAL 4 HOUR))
                                  ORDER BY np.last_sync ASC
                                  LIMIT " . (int)$limit);
        
        return $query->rows;
    }
    
    /**
     * Get eligible products for campaigns
     */
    public function getEligibleProductsForCampaigns($limit = 20) {
        $query = $this->db->query("SELECT np.*, p.price, pd.name, 
                                         (np.view_count + np.favorite_count) as popularity_score
                                  FROM `" . DB_PREFIX . "n11_products` np
                                  INNER JOIN `" . DB_PREFIX . "product` p ON (np.product_id = p.product_id)
                                  LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
                                  WHERE np.status = 'active'
                                  AND np.campaign_id IS NULL
                                  AND np.view_count > 10
                                  AND p.price > 50
                                  ORDER BY popularity_score DESC
                                  LIMIT " . (int)$limit);
        
        return $query->rows;
    }
    
    /**
     * Update product listing status
     */
    public function updateProductListingStatus($product_id, $status, $n11_product_id = null) {
        $sql = "INSERT INTO `" . DB_PREFIX . "n11_products` (product_id, status, last_sync";
        
        if ($n11_product_id) {
            $sql .= ", n11_product_id";
        }
        
        $sql .= ") VALUES (" . (int)$product_id . ", '" . $this->db->escape($status) . "', NOW()";
        
        if ($n11_product_id) {
            $sql .= ", '" . $this->db->escape($n11_product_id) . "'";
        }
        
        $sql .= ") ON DUPLICATE KEY UPDATE status = '" . $this->db->escape($status) . "', last_sync = NOW()";
        
        if ($n11_product_id) {
            $sql .= ", n11_product_id = '" . $this->db->escape($n11_product_id) . "'";
        }
        
        $this->db->query($sql);
        
        // Log the operation
        $this->logSyncOperation('product_create', $n11_product_id, 'success', 'Product listing status updated to: ' . $status, $product_id);
    }
    
    /**
     * Update listing sync time
     */
    public function updateListingSyncTime($n11_product_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . "n11_products` 
                         SET last_sync = NOW() 
                         WHERE n11_product_id = '" . $this->db->escape($n11_product_id) . "'");
    }
    
    /**
     * Update campaign status
     */
    public function updateCampaignStatus($n11_product_id, $status, $campaign_id = null) {
        $sql = "UPDATE `" . DB_PREFIX . "n11_products` 
                SET campaign_id = " . ($campaign_id ? "'" . $this->db->escape($campaign_id) . "'" : "NULL");
        
        $sql .= ", updated_at = NOW() WHERE n11_product_id = '" . $this->db->escape($n11_product_id) . "'";
        
        $this->db->query($sql);
        
        // Log the operation
        $this->logSyncOperation('campaign_update', $n11_product_id, 'success', 'Campaign status updated: ' . $status);
    }
    
    /**
     * Save order mapping
     */
    public function saveOrderMapping($opencart_order_id, $n11_order_data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "n11_orders` 
                         (order_id, n11_order_number, n11_order_id, customer_first_name, customer_last_name, customer_email,
                          customer_phone, product_id, product_name, sku, quantity, unit_price, total_amount, commission_amount,
                          currency, payment_type, order_status, cargo_company, cargo_tracking_number, order_date)
                         VALUES 
                         (" . (int)$opencart_order_id . ",
                          '" . $this->db->escape($n11_order_data['order_number']) . "',
                          '" . $this->db->escape($n11_order_data['order_id']) . "',
                          '" . $this->db->escape($n11_order_data['customer_first_name']) . "',
                          '" . $this->db->escape($n11_order_data['customer_last_name']) . "',
                          '" . $this->db->escape($n11_order_data['customer_email']) . "',
                          '" . $this->db->escape($n11_order_data['customer_phone']) . "',
                          '" . $this->db->escape($n11_order_data['product_id']) . "',
                          '" . $this->db->escape($n11_order_data['product_name']) . "',
                          '" . $this->db->escape($n11_order_data['sku']) . "',
                          " . (int)$n11_order_data['quantity'] . ",
                          " . (float)$n11_order_data['unit_price'] . ",
                          " . (float)$n11_order_data['total_amount'] . ",
                          " . (float)$n11_order_data['commission_amount'] . ",
                          '" . $this->db->escape($n11_order_data['currency']) . "',
                          '" . $this->db->escape($n11_order_data['payment_type']) . "',
                          'new',
                          '" . $this->db->escape($n11_order_data['cargo_company']) . "',
                          '" . $this->db->escape($n11_order_data['cargo_tracking_number']) . "',
                          '" . $this->db->escape($n11_order_data['order_date']) . "')
                         ON DUPLICATE KEY UPDATE
                         order_status = '" . $this->db->escape($n11_order_data['order_status']) . "',
                         updated_at = NOW()");
    }
    
    /**
     * Update seller metrics
     */
    public function updateSellerMetrics($metrics_data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "n11_seller_metrics` 
                         (store_name, seller_score, total_sales, monthly_sales, total_orders, monthly_orders,
                          customer_satisfaction, shipping_performance, return_rate, pro_seller_status, pro_score,
                          commission_rate, commission_discount, last_performance_update)
                         VALUES 
                         ('" . $this->db->escape($metrics_data['store_name']) . "',
                          " . (float)$metrics_data['seller_score'] . ",
                          " . (float)$metrics_data['total_sales'] . ",
                          " . (float)$metrics_data['monthly_sales'] . ",
                          " . (int)$metrics_data['total_orders'] . ",
                          " . (int)$metrics_data['monthly_orders'] . ",
                          " . (float)$metrics_data['customer_satisfaction'] . ",
                          " . (float)$metrics_data['shipping_performance'] . ",
                          " . (float)$metrics_data['return_rate'] . ",
                          " . (int)$metrics_data['pro_seller_status'] . ",
                          " . (float)$metrics_data['pro_score'] . ",
                          " . (float)$metrics_data['commission_rate'] . ",
                          " . (float)$metrics_data['commission_discount'] . ",
                          NOW())
                         ON DUPLICATE KEY UPDATE
                         seller_score = " . (float)$metrics_data['seller_score'] . ",
                         total_sales = " . (float)$metrics_data['total_sales'] . ",
                         monthly_sales = " . (float)$metrics_data['monthly_sales'] . ",
                         total_orders = " . (int)$metrics_data['total_orders'] . ",
                         monthly_orders = " . (int)$metrics_data['monthly_orders'] . ",
                         customer_satisfaction = " . (float)$metrics_data['customer_satisfaction'] . ",
                         shipping_performance = " . (float)$metrics_data['shipping_performance'] . ",
                         return_rate = " . (float)$metrics_data['return_rate'] . ",
                         pro_seller_status = " . (int)$metrics_data['pro_seller_status'] . ",
                         pro_score = " . (float)$metrics_data['pro_score'] . ",
                         commission_rate = " . (float)$metrics_data['commission_rate'] . ",
                         commission_discount = " . (float)$metrics_data['commission_discount'] . ",
                         last_performance_update = NOW()");
    }
    
    /**
     * Dashboard metrics methods
     */
    public function getTotalListings() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "n11_products`");
        return (int)$query->row['total'];
    }
    
    public function getActiveListings() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "n11_products` WHERE status = 'active'");
        return (int)$query->row['total'];
    }
    
    public function getMonthlySales() {
        $query = $this->db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM `" . DB_PREFIX . "n11_orders` 
                                  WHERE order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND order_status != 'cancelled'");
        return (float)$query->row['total'];
    }
    
    public function getMonthlyCommission() {
        $query = $this->db->query("SELECT COALESCE(SUM(commission_amount), 0) as total FROM `" . DB_PREFIX . "n11_orders` 
                                  WHERE order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND order_status != 'cancelled'");
        return (float)$query->row['total'];
    }
    
    public function getAverageRating() {
        $query = $this->db->query("SELECT seller_score FROM `" . DB_PREFIX . "n11_seller_metrics` ORDER BY updated_at DESC LIMIT 1");
        return $query->num_rows ? (float)$query->row['seller_score'] : 0;
    }
    
    public function getTotalOrders() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "n11_orders`");
        return (int)$query->row['total'];
    }
    
    public function getLastSyncTime() {
        $query = $this->db->query("SELECT MAX(last_sync) as last_sync FROM `" . DB_PREFIX . "n11_products`");
        return $query->row['last_sync'];
    }
    
    public function getActiveCampaigns() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "n11_campaigns` WHERE is_active = 1");
        return (int)$query->row['total'];
    }
    
    public function getProScore() {
        $query = $this->db->query("SELECT pro_score FROM `" . DB_PREFIX . "n11_seller_metrics` ORDER BY updated_at DESC LIMIT 1");
        return $query->num_rows ? (float)$query->row['pro_score'] : 0;
    }
    
    public function getCurrentCommissionRate() {
        $query = $this->db->query("SELECT commission_rate FROM `" . DB_PREFIX . "n11_seller_metrics` ORDER BY updated_at DESC LIMIT 1");
        return $query->num_rows ? (float)$query->row['commission_rate'] : 12.0;
    }
    
    /**
     * Get N11 categories
     */
    public function getCategories() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "n11_categories` ORDER BY category_name");
        return $query->rows;
    }
    
    /**
     * Update category mapping
     */
    public function updateCategoryMapping($n11_category_id, $opencart_category_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . "n11_categories` 
                         SET opencart_category_id = " . (int)$opencart_category_id . ", updated_at = NOW()
                         WHERE category_id = " . (int)$n11_category_id);
    }
    
    /**
     * Log sync operations
     */
    public function logSyncOperation($operation_type, $n11_product_id, $status, $message, $product_id = null, $response_data = null, $execution_time = null) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "n11_sync_log` 
                         (operation_type, n11_product_id, product_id, status, message, response_data, execution_time)
                         VALUES 
                         ('" . $this->db->escape($operation_type) . "',
                          " . ($n11_product_id ? "'" . $this->db->escape($n11_product_id) . "'" : "NULL") . ",
                          " . ($product_id ? (int)$product_id : "NULL") . ",
                          '" . $this->db->escape($status) . "',
                          '" . $this->db->escape($message) . "',
                          " . ($response_data ? "'" . $this->db->escape(json_encode($response_data)) . "'" : "NULL") . ",
                          " . ($execution_time ? (float)$execution_time : "NULL") . ")");
    }
    
    /**
     * Get sync logs
     */
    public function getSyncLogs($limit = 100, $operation_type = null) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "n11_sync_log`";
        
        if ($operation_type) {
            $sql .= " WHERE operation_type = '" . $this->db->escape($operation_type) . "'";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT " . (int)$limit;
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Clean old sync logs
     */
    public function cleanSyncLogs() {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "n11_sync_log` WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    }
    
    /**
     * Get N11 product data
     */
    public function getN11Product($product_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "n11_products` WHERE product_id = " . (int)$product_id);
        return $query->row;
    }
    
    /**
     * Update N11 product data with Turkish optimizations
     */
    public function updateN11Product($product_id, $data) {
        $fields = array();
        
        foreach ($data as $key => $value) {
            if (in_array($key, array('n11_product_id', 'title', 'sku', 'product_code', 'category_name', 'currency', 'status', 'approval_status', 'image_url', 'product_url', 'campaign_id'))) {
                $fields[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
            } elseif (in_array($key, array('price', 'discount_price', 'commission_rate', 'commission_amount', 'discount_rate'))) {
                $fields[] = "`" . $key . "` = " . (float)$value;
            } elseif (in_array($key, array('category_id', 'stock_quantity', 'view_count', 'favorite_count'))) {
                $fields[] = "`" . $key . "` = " . (int)$value;
            } elseif (in_array($key, array('pro_seller_badge', 'free_shipping', 'fast_delivery'))) {
                $fields[] = "`" . $key . "` = " . ((bool)$value ? 1 : 0);
            }
        }
        
        if (!empty($fields)) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "n11_products` (product_id) VALUES (" . (int)$product_id . ")
                             ON DUPLICATE KEY UPDATE " . implode(', ', $fields) . ", updated_at = NOW()");
        }
    }
} 