<?php
/**
 * eBay Marketplace Model
 * MesChain-Sync v3.0 - OpenCart 3.0.4.0 Integration
 * Global E-commerce Platform Model with Auction and International Features
 * 
 * @author MesChain Development Team
 * @version 3.0.0
 * @copyright 2024 MesChain Technologies
 */

class ModelExtensionModuleEbay extends Model {
    
    private $log;
    private $logFile = 'ebay_model.log';
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        parent::__construct($registry);
        $this->log = new Log($this->logFile);
    }
    
    /**
     * Get eBay helper class
     * 
     * @return MeschainEbayHelper
     */
    private function getEbayHelper() {
        require_once(DIR_SYSTEM . 'library/meschain/helper/ebay_helper.php');
        return new MeschainEbayHelper($this->registry);
    }
    
    /**
     * Install module and create tables
     */
    public function install() {
        $this->log->write('[INFO] eBay module installation started');
        
        // Create ebay_listings table for product listings
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ebay_listings` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `product_id` INT(11) NOT NULL,
            `item_id` VARCHAR(20) DEFAULT NULL,
            `title` VARCHAR(255) NOT NULL,
            `sku` VARCHAR(100) DEFAULT NULL,
            `listing_type` ENUM('FixedPriceItem', 'Chinese', 'StoreFixedPrice', 'LeadGeneration') DEFAULT 'FixedPriceItem',
            `start_price` DECIMAL(15,4) DEFAULT NULL,
            `buy_it_now_price` DECIMAL(15,4) DEFAULT NULL,
            `reserve_price` DECIMAL(15,4) DEFAULT NULL,
            `quantity` INT(11) DEFAULT 1,
            `duration` VARCHAR(20) DEFAULT 'GTC',
            `category_id` VARCHAR(20) DEFAULT NULL,
            `site_id` INT(3) DEFAULT 0,
            `currency` VARCHAR(3) DEFAULT 'USD',
            `condition` ENUM('New', 'Used', 'Refurbished', 'ForPartsNotWorking') DEFAULT 'New',
            `status` ENUM('draft', 'listed', 'sold', 'ended', 'error') DEFAULT 'draft',
            `listing_url` TEXT DEFAULT NULL,
            `gallery_url` TEXT DEFAULT NULL,
            `watch_count` INT(11) DEFAULT 0,
            `hit_count` INT(11) DEFAULT 0,
            `question_count` INT(11) DEFAULT 0,
            `bid_count` INT(11) DEFAULT 0,
            `current_price` DECIMAL(15,4) DEFAULT NULL,
            `shipping_cost` DECIMAL(15,4) DEFAULT NULL,
            `international_shipping` TINYINT(1) DEFAULT 1,
            `global_shipping` TINYINT(1) DEFAULT 1,
            `best_offer` TINYINT(1) DEFAULT 0,
            `promoted` TINYINT(1) DEFAULT 0,
            `promotion_rate` DECIMAL(5,2) DEFAULT NULL,
            `start_time` DATETIME DEFAULT NULL,
            `end_time` DATETIME DEFAULT NULL,
            `last_sync` DATETIME DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `product_id` (`product_id`),
            UNIQUE KEY `item_id` (`item_id`),
            INDEX `listing_type` (`listing_type`),
            INDEX `status` (`status`),
            INDEX `site_id` (`site_id`),
            INDEX `promoted` (`promoted`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create ebay_orders table for order management
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ebay_orders` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) DEFAULT NULL,
            `ebay_order_id` VARCHAR(50) NOT NULL,
            `ebay_transaction_id` VARCHAR(50) DEFAULT NULL,
            `buyer_user_id` VARCHAR(50) DEFAULT NULL,
            `buyer_username` VARCHAR(100) DEFAULT NULL,
            `buyer_email` VARCHAR(255) DEFAULT NULL,
            `item_id` VARCHAR(20) NOT NULL,
            `listing_title` VARCHAR(255) DEFAULT NULL,
            `sku` VARCHAR(100) DEFAULT NULL,
            `quantity_purchased` INT(11) DEFAULT 1,
            `transaction_price` DECIMAL(15,4) DEFAULT NULL,
            `shipping_cost` DECIMAL(15,4) DEFAULT NULL,
            `total_amount` DECIMAL(15,4) DEFAULT NULL,
            `currency` VARCHAR(3) DEFAULT 'USD',
            `payment_method` VARCHAR(50) DEFAULT NULL,
            `payment_status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
            `checkout_status` ENUM('incomplete', 'complete') DEFAULT 'incomplete',
            `order_status` ENUM('new', 'paid', 'shipped', 'delivered', 'cancelled', 'dispute') DEFAULT 'new',
            `shipping_service` VARCHAR(100) DEFAULT NULL,
            `tracking_number` VARCHAR(100) DEFAULT NULL,
            `shipped_time` DATETIME DEFAULT NULL,
            `delivery_date` DATETIME DEFAULT NULL,
            `feedback_left` TINYINT(1) DEFAULT 0,
            `feedback_received` TINYINT(1) DEFAULT 0,
            `feedback_score` ENUM('Positive', 'Neutral', 'Negative') DEFAULT NULL,
            `dispute_opened` TINYINT(1) DEFAULT 0,
            `buyer_protection_status` VARCHAR(50) DEFAULT NULL,
            `sale_date` DATETIME DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `ebay_order_id` (`ebay_order_id`),
            INDEX `order_id` (`order_id`),
            INDEX `item_id` (`item_id`),
            INDEX `buyer_user_id` (`buyer_user_id`),
            INDEX `order_status` (`order_status`),
            INDEX `payment_status` (`payment_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create ebay_categories table for category mapping
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ebay_categories` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `category_id` VARCHAR(20) NOT NULL,
            `parent_id` VARCHAR(20) DEFAULT NULL,
            `level` INT(3) DEFAULT 0,
            `category_name` VARCHAR(255) NOT NULL,
            `opencart_category_id` INT(11) DEFAULT NULL,
            `site_id` INT(3) DEFAULT 0,
            `leaf_category` TINYINT(1) DEFAULT 0,
            `auto_pay_enabled` TINYINT(1) DEFAULT 0,
            `best_offer_enabled` TINYINT(1) DEFAULT 0,
            `listing_duration` TEXT DEFAULT NULL,
            `insertion_fee` DECIMAL(10,4) DEFAULT NULL,
            `final_value_fee` DECIMAL(5,2) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `category_site` (`category_id`, `site_id`),
            INDEX `parent_id` (`parent_id`),
            INDEX `opencart_category_id` (`opencart_category_id`),
            INDEX `leaf_category` (`leaf_category`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create ebay_feedback table for feedback management
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ebay_feedback` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `feedback_id` VARCHAR(50) NOT NULL,
            `user_id` VARCHAR(50) NOT NULL,
            `item_id` VARCHAR(20) DEFAULT NULL,
            `transaction_id` VARCHAR(50) DEFAULT NULL,
            `commenting_user` VARCHAR(100) DEFAULT NULL,
            `commenting_user_score` INT(11) DEFAULT NULL,
            `comment_type` ENUM('Positive', 'Neutral', 'Negative') NOT NULL,
            `comment_text` TEXT DEFAULT NULL,
            `comment_time` DATETIME DEFAULT NULL,
            `response` TEXT DEFAULT NULL,
            `followup` TEXT DEFAULT NULL,
            `item_title` VARCHAR(255) DEFAULT NULL,
            `item_price` DECIMAL(15,4) DEFAULT NULL,
            `currency` VARCHAR(3) DEFAULT 'USD',
            `role` ENUM('Buyer', 'Seller') NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `feedback_id` (`feedback_id`),
            INDEX `user_id` (`user_id`),
            INDEX `item_id` (`item_id`),
            INDEX `comment_type` (`comment_type`),
            INDEX `role` (`role`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create ebay_seller_metrics table for performance tracking
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ebay_seller_metrics` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` VARCHAR(50) NOT NULL,
            `feedback_score` INT(11) DEFAULT 0,
            `positive_feedback_percent` DECIMAL(5,2) DEFAULT 0,
            `feedback_private` TINYINT(1) DEFAULT 0,
            `feedback_rating_star` VARCHAR(20) DEFAULT NULL,
            `top_rated_seller` TINYINT(1) DEFAULT 0,
            `stores_subscription_level` VARCHAR(50) DEFAULT NULL,
            `power_seller_status` VARCHAR(50) DEFAULT NULL,
            `ebay_good_standing` TINYINT(1) DEFAULT 1,
            `quick_listing` TINYINT(1) DEFAULT 0,
            `transaction_defect_rate` DECIMAL(5,2) DEFAULT NULL,
            `late_shipment_rate` DECIMAL(5,2) DEFAULT NULL,
            `cases_not_as_described_rate` DECIMAL(5,2) DEFAULT NULL,
            `seller_level` VARCHAR(50) DEFAULT 'Above Standard',
            `total_sales_amount` DECIMAL(15,4) DEFAULT 0,
            `total_sales_count` INT(11) DEFAULT 0,
            `monthly_sales_amount` DECIMAL(15,4) DEFAULT 0,
            `monthly_sales_count` INT(11) DEFAULT 0,
            `last_updated` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_id` (`user_id`),
            INDEX `top_rated_seller` (`top_rated_seller`),
            INDEX `seller_level` (`seller_level`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create ebay_shipping table for shipping options
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ebay_shipping` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `listing_id` INT(11) NOT NULL,
            `service_name` VARCHAR(100) NOT NULL,
            `service_cost` DECIMAL(10,4) DEFAULT NULL,
            `service_additional_cost` DECIMAL(10,4) DEFAULT NULL,
            `service_priority` INT(3) DEFAULT 1,
            `shipping_type` ENUM('Flat', 'Calculated', 'FreightFlat', 'NotSpecified') DEFAULT 'Flat',
            `international_service` TINYINT(1) DEFAULT 0,
            `ship_to_locations` TEXT DEFAULT NULL,
            `exclude_ship_to_locations` TEXT DEFAULT NULL,
            `global_shipping` TINYINT(1) DEFAULT 0,
            `handling_time` INT(3) DEFAULT 1,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `listing_id` (`listing_id`),
            INDEX `international_service` (`international_service`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create ebay_sync_log table for operation tracking
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ebay_sync_log` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `operation_type` ENUM('listing_create', 'listing_update', 'listing_end', 'order_sync', 'feedback_sync', 'promotion_update', 'category_sync') NOT NULL,
            `item_id` VARCHAR(20) DEFAULT NULL,
            `product_id` INT(11) DEFAULT NULL,
            `status` ENUM('success', 'error', 'pending', 'warning') NOT NULL,
            `message` TEXT DEFAULT NULL,
            `request_data` TEXT DEFAULT NULL,
            `response_data` TEXT DEFAULT NULL,
            `execution_time` DECIMAL(8,3) DEFAULT NULL,
            `api_call_count` INT(11) DEFAULT 1,
            `rate_limit_remaining` INT(11) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `operation_type` (`operation_type`),
            INDEX `status` (`status`),
            INDEX `item_id` (`item_id`),
            INDEX `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Insert default global categories
        $this->addDefaultCategories();
        
        // Insert default shipping services
        $this->addDefaultShippingServices();
        
        $this->log->write('[SUCCESS] eBay module installed successfully');
    }
    
    /**
     * Uninstall module
     */
    public function uninstall() {
        $this->log->write('[INFO] eBay module uninstallation started');
        
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ebay_sync_log`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ebay_shipping`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ebay_seller_metrics`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ebay_feedback`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ebay_categories`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ebay_orders`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ebay_listings`");
        
        $this->log->write('[SUCCESS] eBay module uninstalled successfully');
    }
    
    /**
     * Add default eBay categories
     */
    private function addDefaultCategories() {
        $categories = array(
            // US Site Categories
            array('category_id' => '58058', 'name' => 'Cell Phones & Accessories', 'site_id' => 0),
            array('category_id' => '175672', 'name' => 'Computers/Tablets & Networking', 'site_id' => 0),
            array('category_id' => '11450', 'name' => 'Clothing, Shoes & Accessories', 'site_id' => 0),
            array('category_id' => '625', 'name' => 'Cameras & Photo', 'site_id' => 0),
            array('category_id' => '293', 'name' => 'Consumer Electronics', 'site_id' => 0),
            array('category_id' => '267', 'name' => 'Books, Movies & Music', 'site_id' => 0),
            array('category_id' => '2984', 'name' => 'Baby', 'site_id' => 0),
            array('category_id' => '550', 'name' => 'Art', 'site_id' => 0),
            array('category_id' => '15032', 'name' => 'Musical Instruments & Gear', 'site_id' => 0),
            array('category_id' => '6000', 'name' => 'Motors', 'site_id' => 0),
            
            // UK Site Categories  
            array('category_id' => '9355', 'name' => 'Mobile & Smart Phones', 'site_id' => 3),
            array('category_id' => '58058', 'name' => 'Computers/Tablets & Networking', 'site_id' => 3),
            array('category_id' => '11450', 'name' => 'Clothes, Shoes & Accessories', 'site_id' => 3),
            
            // Germany Site Categories
            array('category_id' => '9355', 'name' => 'Handys & Kommunikation', 'site_id' => 77),
            array('category_id' => '175672', 'name' => 'Computer, Tablets & Netzwerk', 'site_id' => 77),
            
            // Turkey Categories (GittiGidiyor)
            array('category_id' => '1001', 'name' => 'Elektronik', 'site_id' => 207),
            array('category_id' => '1002', 'name' => 'Moda', 'site_id' => 207),
            array('category_id' => '1003', 'name' => 'Ev & Bahçe', 'site_id' => 207)
        );
        
        foreach ($categories as $category) {
            $this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "ebay_categories` 
                (`category_id`, `category_name`, `site_id`, `leaf_category`) VALUES 
                ('" . $this->db->escape($category['category_id']) . "', 
                 '" . $this->db->escape($category['name']) . "', 
                 " . (int)$category['site_id'] . ", 1)");
        }
    }
    
    /**
     * Add default shipping services
     */
    private function addDefaultShippingServices() {
        // Log available shipping services for reference
        $this->logSyncOperation('category_sync', null, 'success', 'Default shipping services initialized');
    }
    
    /**
     * Get products for eBay listing
     */
    public function getProductsForListing($limit = 50) {
        $query = $this->db->query("SELECT p.product_id, pd.name, p.model, p.sku, p.price, p.quantity, p.status, p.image,
                                         pd.description, pd.meta_title, pd.meta_description,
                                         el.id as listing_id, el.status as listing_status, el.item_id, el.last_sync
                                  FROM `" . DB_PREFIX . "product` p
                                  LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
                                  LEFT JOIN `" . DB_PREFIX . "ebay_listings` el ON (p.product_id = el.product_id)
                                  WHERE p.status = 1 
                                  AND p.quantity > 0
                                  AND (el.status IS NULL OR el.status = 'draft' OR el.last_sync < DATE_SUB(NOW(), INTERVAL 24 HOUR))
                                  ORDER BY p.date_modified DESC
                                  LIMIT " . (int)$limit);
        
        return $query->rows;
    }
    
    /**
     * Get listings that need updates
     */
    public function getListingsForUpdate($limit = 100) {
        $query = $this->db->query("SELECT el.*, p.price, p.quantity, pd.name, p.model
                                  FROM `" . DB_PREFIX . "ebay_listings` el
                                  INNER JOIN `" . DB_PREFIX . "product` p ON (el.product_id = p.product_id)
                                  LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
                                  WHERE el.status = 'listed'
                                  AND el.item_id IS NOT NULL
                                  AND (el.buy_it_now_price != p.price 
                                       OR el.quantity != p.quantity 
                                       OR el.last_sync < DATE_SUB(NOW(), INTERVAL 6 HOUR))
                                  ORDER BY el.last_sync ASC
                                  LIMIT " . (int)$limit);
        
        return $query->rows;
    }
    
    /**
     * Get eligible listings for promotion
     */
    public function getEligibleListingsForPromotion($limit = 20) {
        $query = $this->db->query("SELECT el.*, p.price, pd.name, 
                                         (el.watch_count + el.hit_count) as popularity_score
                                  FROM `" . DB_PREFIX . "ebay_listings` el
                                  INNER JOIN `" . DB_PREFIX . "product` p ON (el.product_id = p.product_id)
                                  LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
                                  WHERE el.status = 'listed'
                                  AND el.promoted = 0
                                  AND el.watch_count > 5
                                  AND p.price > 10
                                  ORDER BY popularity_score DESC
                                  LIMIT " . (int)$limit);
        
        return $query->rows;
    }
    
    /**
     * Get orders for feedback
     */
    public function getOrdersForFeedback($limit = 10) {
        $query = $this->db->query("SELECT eo.*, el.title as listing_title
                                  FROM `" . DB_PREFIX . "ebay_orders` eo
                                  LEFT JOIN `" . DB_PREFIX . "ebay_listings` el ON (eo.item_id = el.item_id)
                                  WHERE eo.order_status = 'delivered'
                                  AND eo.feedback_left = 0
                                  AND eo.delivery_date < DATE_SUB(NOW(), INTERVAL 3 DAY)
                                  AND eo.delivery_date > DATE_SUB(NOW(), INTERVAL 60 DAY)
                                  ORDER BY eo.delivery_date ASC
                                  LIMIT " . (int)$limit);
        
        return $query->rows;
    }
    
    /**
     * Update product listing status
     */
    public function updateProductListingStatus($product_id, $status, $item_id = null) {
        $sql = "INSERT INTO `" . DB_PREFIX . "ebay_listings` (product_id, status, last_sync";
        
        if ($item_id) {
            $sql .= ", item_id";
        }
        
        $sql .= ") VALUES (" . (int)$product_id . ", '" . $this->db->escape($status) . "', NOW()";
        
        if ($item_id) {
            $sql .= ", '" . $this->db->escape($item_id) . "'";
        }
        
        $sql .= ") ON DUPLICATE KEY UPDATE status = '" . $this->db->escape($status) . "', last_sync = NOW()";
        
        if ($item_id) {
            $sql .= ", item_id = '" . $this->db->escape($item_id) . "'";
        }
        
        $this->db->query($sql);
        
        // Log the operation
        $this->logSyncOperation('listing_create', $item_id, 'success', 'Product listing status updated to: ' . $status, $product_id);
    }
    
    /**
     * Update listing sync time
     */
    public function updateListingSyncTime($item_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . "ebay_listings` 
                         SET last_sync = NOW() 
                         WHERE item_id = '" . $this->db->escape($item_id) . "'");
    }
    
    /**
     * Update promotion status
     */
    public function updatePromotionStatus($item_id, $status, $promotion_rate = null) {
        $sql = "UPDATE `" . DB_PREFIX . "ebay_listings` 
                SET promoted = " . ($status == 'promoted' ? 1 : 0);
        
        if ($promotion_rate !== null) {
            $sql .= ", promotion_rate = " . (float)$promotion_rate;
        }
        
        $sql .= ", updated_at = NOW() WHERE item_id = '" . $this->db->escape($item_id) . "'";
        
        $this->db->query($sql);
        
        // Log the operation
        $this->logSyncOperation('promotion_update', $item_id, 'success', 'Promotion status updated to: ' . $status);
    }
    
    /**
     * Save order mapping
     */
    public function saveOrderMapping($opencart_order_id, $ebay_order_data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "ebay_orders` 
                         (order_id, ebay_order_id, ebay_transaction_id, buyer_user_id, buyer_username, buyer_email,
                          item_id, listing_title, sku, quantity_purchased, transaction_price, shipping_cost, total_amount,
                          currency, payment_method, payment_status, order_status, sale_date)
                         VALUES 
                         (" . (int)$opencart_order_id . ",
                          '" . $this->db->escape($ebay_order_data['order_id']) . "',
                          '" . $this->db->escape($ebay_order_data['transaction_id']) . "',
                          '" . $this->db->escape($ebay_order_data['buyer_user_id']) . "',
                          '" . $this->db->escape($ebay_order_data['buyer_username']) . "',
                          '" . $this->db->escape($ebay_order_data['buyer_email']) . "',
                          '" . $this->db->escape($ebay_order_data['item_id']) . "',
                          '" . $this->db->escape($ebay_order_data['title']) . "',
                          '" . $this->db->escape($ebay_order_data['sku']) . "',
                          " . (int)$ebay_order_data['quantity'] . ",
                          " . (float)$ebay_order_data['price'] . ",
                          " . (float)$ebay_order_data['shipping_cost'] . ",
                          " . (float)$ebay_order_data['total'] . ",
                          '" . $this->db->escape($ebay_order_data['currency']) . "',
                          '" . $this->db->escape($ebay_order_data['payment_method']) . "',
                          '" . $this->db->escape($ebay_order_data['payment_status']) . "',
                          'new',
                          '" . $this->db->escape($ebay_order_data['sale_date']) . "')
                         ON DUPLICATE KEY UPDATE
                         payment_status = '" . $this->db->escape($ebay_order_data['payment_status']) . "',
                         updated_at = NOW()");
    }
    
    /**
     * Update seller metrics
     */
    public function updateSellerMetrics($metrics_data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "ebay_seller_metrics` 
                         (user_id, feedback_score, positive_feedback_percent, feedback_rating_star, top_rated_seller,
                          transaction_defect_rate, late_shipment_rate, seller_level, last_updated)
                         VALUES 
                         ('" . $this->db->escape($metrics_data['user_id']) . "',
                          " . (int)$metrics_data['feedback_score'] . ",
                          " . (float)$metrics_data['positive_feedback_percent'] . ",
                          '" . $this->db->escape($metrics_data['feedback_rating_star']) . "',
                          " . (int)$metrics_data['top_rated_seller'] . ",
                          " . (float)$metrics_data['transaction_defect_rate'] . ",
                          " . (float)$metrics_data['late_shipment_rate'] . ",
                          '" . $this->db->escape($metrics_data['seller_level']) . "',
                          NOW())
                         ON DUPLICATE KEY UPDATE
                         feedback_score = " . (int)$metrics_data['feedback_score'] . ",
                         positive_feedback_percent = " . (float)$metrics_data['positive_feedback_percent'] . ",
                         feedback_rating_star = '" . $this->db->escape($metrics_data['feedback_rating_star']) . "',
                         top_rated_seller = " . (int)$metrics_data['top_rated_seller'] . ",
                         transaction_defect_rate = " . (float)$metrics_data['transaction_defect_rate'] . ",
                         late_shipment_rate = " . (float)$metrics_data['late_shipment_rate'] . ",
                         seller_level = '" . $this->db->escape($metrics_data['seller_level']) . "',
                         last_updated = NOW()");
    }
    
    /**
     * Dashboard metrics methods
     */
    public function getTotalListings() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ebay_listings`");
        return (int)$query->row['total'];
    }
    
    public function getActiveListings() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ebay_listings` WHERE status = 'listed'");
        return (int)$query->row['total'];
    }
    
    public function getWatchingCount() {
        $query = $this->db->query("SELECT COALESCE(SUM(watch_count), 0) as total FROM `" . DB_PREFIX . "ebay_listings` WHERE status = 'listed'");
        return (int)$query->row['total'];
    }
    
    public function getMonthlySales() {
        $query = $this->db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM `" . DB_PREFIX . "ebay_orders` 
                                  WHERE sale_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND order_status != 'cancelled'");
        return (float)$query->row['total'];
    }
    
    public function getMonthlyFees() {
        // This would typically come from eBay Seller API fees endpoint
        $query = $this->db->query("SELECT COUNT(*) as listings FROM `" . DB_PREFIX . "ebay_listings` 
                                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        return (float)$query->row['listings'] * 0.35; // Estimated insertion fee
    }
    
    public function getFeedbackScore() {
        $query = $this->db->query("SELECT feedback_score FROM `" . DB_PREFIX . "ebay_seller_metrics` ORDER BY last_updated DESC LIMIT 1");
        return $query->num_rows ? (int)$query->row['feedback_score'] : 0;
    }
    
    public function getLastSyncTime() {
        $query = $this->db->query("SELECT MAX(last_sync) as last_sync FROM `" . DB_PREFIX . "ebay_listings`");
        return $query->row['last_sync'];
    }
    
    public function getPromotedListings() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ebay_listings` WHERE promoted = 1");
        return (int)$query->row['total'];
    }
    
    public function getInternationalSales() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ebay_orders` eo
                                  INNER JOIN `" . DB_PREFIX . "ebay_listings` el ON (eo.item_id = el.item_id)
                                  WHERE el.international_shipping = 1 
                                  AND eo.sale_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        return (int)$query->row['total'];
    }
    
    public function getDefectRate() {
        $query = $this->db->query("SELECT transaction_defect_rate FROM `" . DB_PREFIX . "ebay_seller_metrics` ORDER BY last_updated DESC LIMIT 1");
        return $query->num_rows ? (float)$query->row['transaction_defect_rate'] : 0;
    }
    
    /**
     * Get eBay categories
     */
    public function getCategories($site_id = 0) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ebay_categories` 
                                  WHERE site_id = " . (int)$site_id . " 
                                  ORDER BY category_name");
        return $query->rows;
    }
    
    /**
     * Update category mapping
     */
    public function updateCategoryMapping($ebay_category_id, $opencart_category_id, $site_id = 0) {
        $this->db->query("UPDATE `" . DB_PREFIX . "ebay_categories` 
                         SET opencart_category_id = " . (int)$opencart_category_id . ", updated_at = NOW()
                         WHERE category_id = '" . $this->db->escape($ebay_category_id) . "' 
                         AND site_id = " . (int)$site_id);
    }
    
    /**
     * Log sync operations
     */
    public function logSyncOperation($operation_type, $item_id, $status, $message, $product_id = null, $response_data = null, $execution_time = null) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "ebay_sync_log` 
                         (operation_type, item_id, product_id, status, message, response_data, execution_time)
                         VALUES 
                         ('" . $this->db->escape($operation_type) . "',
                          " . ($item_id ? "'" . $this->db->escape($item_id) . "'" : "NULL") . ",
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
        $sql = "SELECT * FROM `" . DB_PREFIX . "ebay_sync_log`";
        
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
        $this->db->query("DELETE FROM `" . DB_PREFIX . "ebay_sync_log` WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    }
    
    /**
     * Get eBay listing data
     */
    public function getEbayListing($product_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ebay_listings` WHERE product_id = " . (int)$product_id);
        return $query->row;
    }
    
    /**
     * Update eBay listing data
     */
    public function updateEbayListing($product_id, $data) {
        $fields = array();
        
        foreach ($data as $key => $value) {
            if (in_array($key, array('item_id', 'title', 'sku', 'listing_type', 'duration', 'category_id', 'currency', 'condition', 'status', 'listing_url'))) {
                $fields[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
            } elseif (in_array($key, array('start_price', 'buy_it_now_price', 'reserve_price', 'current_price', 'shipping_cost', 'promotion_rate'))) {
                $fields[] = "`" . $key . "` = " . (float)$value;
            } elseif (in_array($key, array('quantity', 'watch_count', 'hit_count', 'question_count', 'bid_count', 'site_id'))) {
                $fields[] = "`" . $key . "` = " . (int)$value;
            } elseif (in_array($key, array('international_shipping', 'global_shipping', 'best_offer', 'promoted'))) {
                $fields[] = "`" . $key . "` = " . ((bool)$value ? 1 : 0);
            }
        }
        
        if (!empty($fields)) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "ebay_listings` (product_id) VALUES (" . (int)$product_id . ")
                             ON DUPLICATE KEY UPDATE " . implode(', ', $fields) . ", updated_at = NOW()");
        }
    }
} 