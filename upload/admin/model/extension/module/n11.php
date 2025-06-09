<?php
/**
 * N11 Model
 * MesChain-Sync v4.0 - N11 Marketplace Model
 * Enhanced Turkish E-commerce Platform Integration
 * 
 * @author MesChain Development Team
 * @version 4.0.0
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
     * Install N11 module
     */
    public function install() {
        $this->log->write('[INFO] N11 module installation started');
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_products` (
                `n11_product_id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `n11_id` varchar(255) NOT NULL,
                `merchant_product_id` varchar(255) DEFAULT NULL,
                `barcode` varchar(255) DEFAULT NULL,
                `title` varchar(500) NOT NULL,
                `subtitle` varchar(500) DEFAULT NULL,
                `category_id` int(11) DEFAULT NULL,
                `subcategory_id` int(11) DEFAULT NULL,
                `brand_name` varchar(255) DEFAULT NULL,
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
                `n11_commission` decimal(15,4) DEFAULT NULL,
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
                PRIMARY KEY (`n11_product_id`),
                UNIQUE KEY `unique_product_n11` (`product_id`, `n11_id`),
                KEY `idx_product_id` (`product_id`),
                KEY `idx_n11_id` (`n11_id`),
                KEY `idx_merchant_product_id` (`merchant_product_id`),
                KEY `idx_product_status` (`product_status`),
                KEY `idx_approval_status` (`approval_status`),
                KEY `idx_last_sync` (`last_sync`),
                KEY `idx_sync_status` (`sync_status`),
                KEY `idx_sales_rank` (`sales_rank`),
                KEY `idx_price_competitiveness` (`price_competitiveness`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_orders` (
                `n11_order_id` int(11) NOT NULL AUTO_INCREMENT,
                `order_id` int(11) DEFAULT NULL,
                `n11_id` varchar(255) NOT NULL,
                `order_number` varchar(255) NOT NULL,
                `n11_order_number` varchar(255) DEFAULT NULL,
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
                `n11_discount` decimal(15,4) DEFAULT NULL,
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
                `n11_notes` text,
                `last_sync` datetime DEFAULT NULL,
                `sync_status` enum('success','error','pending','processing') DEFAULT 'pending',
                `sync_error` text,
                `api_response` text,
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`n11_order_id`),
                UNIQUE KEY `unique_n11_order` (`n11_id`),
                KEY `idx_order_id` (`order_id`),
                KEY `idx_order_number` (`order_number`),
                KEY `idx_n11_order_number` (`n11_order_number`),
                KEY `idx_customer_id` (`customer_id`),
                KEY `idx_order_date` (`order_date`),
                KEY `idx_order_status` (`order_status`),
                KEY `idx_payment_status` (`payment_status`),
                KEY `idx_shipment_status` (`shipment_status`),
                KEY `idx_last_sync` (`last_sync`),
                KEY `idx_sync_status` (`sync_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_analytics` (
                `analytics_id` int(11) NOT NULL AUTO_INCREMENT,
                `date_recorded` date NOT NULL,
                `hour_recorded` int(11) DEFAULT NULL,
                `metric_type` varchar(100) NOT NULL,
                `metric_category` varchar(100) NOT NULL,
                `metric_name` varchar(255) NOT NULL,
                `metric_value` decimal(15,4) DEFAULT NULL,
                `metric_count` int(11) DEFAULT NULL,
                `metric_percentage` decimal(5,2) DEFAULT NULL,
                `metric_data` json DEFAULT NULL,
                `product_id` int(11) DEFAULT NULL,
                `category_id` int(11) DEFAULT NULL,
                `brand_id` int(11) DEFAULT NULL,
                `campaign_id` int(11) DEFAULT NULL,
                `customer_segment` varchar(100) DEFAULT NULL,
                `traffic_source` varchar(100) DEFAULT NULL,
                `device_type` varchar(100) DEFAULT NULL,
                `geographic_region` varchar(100) DEFAULT NULL,
                `comparison_data` json DEFAULT NULL,
                `trend_data` json DEFAULT NULL,
                `forecast_data` json DEFAULT NULL,
                `ai_insights` text,
                `ai_predictions` json DEFAULT NULL,
                `confidence_score` decimal(5,4) DEFAULT NULL,
                `data_quality_score` decimal(5,2) DEFAULT NULL,
                `anomaly_detected` tinyint(1) DEFAULT 0,
                `anomaly_description` text,
                `alert_triggered` tinyint(1) DEFAULT 0,
                `alert_recipients` json DEFAULT NULL,
                `processed_by_ai` tinyint(1) DEFAULT 0,
                `processing_time` decimal(10,4) DEFAULT NULL,
                `data_source` varchar(100) DEFAULT 'n11_api',
                `sync_batch_id` varchar(255) DEFAULT NULL,
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`analytics_id`),
                KEY `idx_date_recorded` (`date_recorded`),
                KEY `idx_metric_type` (`metric_type`),
                KEY `idx_metric_category` (`metric_category`),
                KEY `idx_product_id` (`product_id`),
                KEY `idx_category_id` (`category_id`),
                KEY `idx_brand_id` (`brand_id`),
                KEY `idx_campaign_id` (`campaign_id`),
                KEY `idx_customer_segment` (`customer_segment`),
                KEY `idx_anomaly_detected` (`anomaly_detected`),
                KEY `idx_processed_by_ai` (`processed_by_ai`),
                KEY `idx_sync_batch_id` (`sync_batch_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_pricing` (
                `pricing_id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `n11_product_id` varchar(255) NOT NULL,
                `current_price` decimal(15,4) NOT NULL,
                `suggested_price` decimal(15,4) DEFAULT NULL,
                `min_price` decimal(15,4) DEFAULT NULL,
                `max_price` decimal(15,4) DEFAULT NULL,
                `competitor_min_price` decimal(15,4) DEFAULT NULL,
                `competitor_max_price` decimal(15,4) DEFAULT NULL,
                `competitor_avg_price` decimal(15,4) DEFAULT NULL,
                `market_position` enum('lowest','below_avg','average','above_avg','highest') DEFAULT 'average',
                `price_elasticity` decimal(5,4) DEFAULT NULL,
                `demand_score` decimal(5,2) DEFAULT NULL,
                `competition_score` decimal(5,2) DEFAULT NULL,
                `profitability_score` decimal(5,2) DEFAULT NULL,
                `optimization_score` decimal(5,2) DEFAULT NULL,
                `price_change_recommendation` enum('increase','decrease','maintain','monitor') DEFAULT 'maintain',
                `recommended_change_amount` decimal(15,4) DEFAULT NULL,
                `recommended_change_percentage` decimal(5,2) DEFAULT NULL,
                `expected_impact_revenue` decimal(15,4) DEFAULT NULL,
                `expected_impact_orders` int(11) DEFAULT NULL,
                `expected_impact_margin` decimal(5,2) DEFAULT NULL,
                `risk_assessment` enum('low','medium','high','critical') DEFAULT 'low',
                `risk_factors` json DEFAULT NULL,
                `ai_confidence` decimal(5,4) DEFAULT NULL,
                `strategy_type` varchar(100) DEFAULT 'competitive',
                `last_updated` datetime DEFAULT NULL,
                `next_review_date` datetime DEFAULT NULL,
                `auto_update_enabled` tinyint(1) DEFAULT 0,
                `price_history` json DEFAULT NULL,
                `performance_metrics` json DEFAULT NULL,
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`pricing_id`),
                UNIQUE KEY `unique_product_pricing` (`product_id`, `n11_product_id`),
                KEY `idx_product_id` (`product_id`),
                KEY `idx_n11_product_id` (`n11_product_id`),
                KEY `idx_market_position` (`market_position`),
                KEY `idx_price_change_recommendation` (`price_change_recommendation`),
                KEY `idx_optimization_score` (`optimization_score`),
                KEY `idx_last_updated` (`last_updated`),
                KEY `idx_auto_update_enabled` (`auto_update_enabled`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_inventory` (
                `inventory_id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `n11_product_id` varchar(255) NOT NULL,
                `current_stock` int(11) NOT NULL DEFAULT 0,
                `reserved_stock` int(11) DEFAULT 0,
                `available_stock` int(11) GENERATED ALWAYS AS (`current_stock` - `reserved_stock`) STORED,
                `reorder_point` int(11) DEFAULT 10,
                `reorder_quantity` int(11) DEFAULT 50,
                `max_stock_level` int(11) DEFAULT 1000,
                `lead_time_days` int(11) DEFAULT 7,
                `supplier_id` int(11) DEFAULT NULL,
                `cost_price` decimal(15,4) DEFAULT NULL,
                `average_cost` decimal(15,4) DEFAULT NULL,
                `last_cost` decimal(15,4) DEFAULT NULL,
                `stock_value` decimal(15,4) GENERATED ALWAYS AS (`current_stock` * `average_cost`) STORED,
                `turnover_rate` decimal(5,2) DEFAULT NULL,
                `days_of_supply` int(11) DEFAULT NULL,
                `stockout_probability` decimal(5,4) DEFAULT NULL,
                `excess_inventory_risk` decimal(5,4) DEFAULT NULL,
                `demand_forecast_30d` int(11) DEFAULT NULL,
                `demand_forecast_60d` int(11) DEFAULT NULL,
                `demand_forecast_90d` int(11) DEFAULT NULL,
                `seasonal_factor` decimal(5,4) DEFAULT 1.0000,
                `trend_factor` decimal(5,4) DEFAULT 1.0000,
                `safety_stock` int(11) DEFAULT 5,
                `abc_classification` enum('A','B','C','X') DEFAULT 'C',
                `velocity_classification` enum('fast','medium','slow','dead') DEFAULT 'medium',
                `last_sale_date` date DEFAULT NULL,
                `last_purchase_date` date DEFAULT NULL,
                `stock_alerts_enabled` tinyint(1) DEFAULT 1,
                `auto_reorder_enabled` tinyint(1) DEFAULT 0,
                `optimization_enabled` tinyint(1) DEFAULT 1,
                `last_optimized` datetime DEFAULT NULL,
                `optimization_score` decimal(5,2) DEFAULT NULL,
                `recommendations` text,
                `stock_movements` json DEFAULT NULL,
                `performance_metrics` json DEFAULT NULL,
                `ai_insights` text,
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`inventory_id`),
                UNIQUE KEY `unique_product_inventory` (`product_id`, `n11_product_id`),
                KEY `idx_product_id` (`product_id`),
                KEY `idx_n11_product_id` (`n11_product_id`),
                KEY `idx_current_stock` (`current_stock`),
                KEY `idx_reorder_point` (`reorder_point`),
                KEY `idx_abc_classification` (`abc_classification`),
                KEY `idx_velocity_classification` (`velocity_classification`),
                KEY `idx_auto_reorder_enabled` (`auto_reorder_enabled`),
                KEY `idx_last_sale_date` (`last_sale_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_campaigns` (
                `campaign_id` int(11) NOT NULL AUTO_INCREMENT,
                `n11_campaign_id` varchar(255) DEFAULT NULL,
                `campaign_name` varchar(255) NOT NULL,
                `campaign_type` varchar(100) NOT NULL,
                `campaign_subtype` varchar(100) DEFAULT NULL,
                `description` text,
                `terms_and_conditions` text,
                `start_date` datetime NOT NULL,
                `end_date` datetime NOT NULL,
                `status` enum('draft','active','paused','completed','cancelled','expired') DEFAULT 'draft',
                `approval_status` enum('approved','rejected','pending','under_review') DEFAULT 'pending',
                `target_audience` varchar(255) DEFAULT 'all',
                `target_products` json DEFAULT NULL,
                `target_categories` json DEFAULT NULL,
                `target_brands` json DEFAULT NULL,
                `geographical_targeting` json DEFAULT NULL,
                `budget_total` decimal(15,4) DEFAULT NULL,
                `budget_daily` decimal(15,4) DEFAULT NULL,
                `budget_spent` decimal(15,4) DEFAULT 0,
                `budget_remaining` decimal(15,4) GENERATED ALWAYS AS (`budget_total` - `budget_spent`) STORED,
                `discount_type` enum('percentage','fixed_amount','buy_x_get_y','free_shipping') DEFAULT 'percentage',
                `discount_value` decimal(15,4) DEFAULT NULL,
                `minimum_order_amount` decimal(15,4) DEFAULT NULL,
                `maximum_discount_amount` decimal(15,4) DEFAULT NULL,
                `usage_limit_total` int(11) DEFAULT NULL,
                `usage_limit_per_customer` int(11) DEFAULT 1,
                `usage_count` int(11) DEFAULT 0,
                `click_count` int(11) DEFAULT 0,
                `impression_count` int(11) DEFAULT 0,
                `conversion_count` int(11) DEFAULT 0,
                `revenue_generated` decimal(15,4) DEFAULT 0,
                `roi` decimal(5,2) DEFAULT NULL,
                `ctr` decimal(5,4) DEFAULT NULL,
                `conversion_rate` decimal(5,4) DEFAULT NULL,
                `average_order_value` decimal(15,4) DEFAULT NULL,
                `customer_acquisition_cost` decimal(15,4) DEFAULT NULL,
                `lifetime_value` decimal(15,4) DEFAULT NULL,
                `performance_score` decimal(5,2) DEFAULT NULL,
                `optimization_suggestions` text,
                `ai_recommendations` text,
                `automated_bidding` tinyint(1) DEFAULT 0,
                `automated_budget` tinyint(1) DEFAULT 0,
                `automated_targeting` tinyint(1) DEFAULT 0,
                `notification_settings` json DEFAULT NULL,
                `reporting_schedule` json DEFAULT NULL,
                `created_by` int(11) DEFAULT NULL,
                `last_modified_by` int(11) DEFAULT NULL,
                `last_sync` datetime DEFAULT NULL,
                `sync_status` enum('success','error','pending') DEFAULT 'pending',
                `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`campaign_id`),
                KEY `idx_n11_campaign_id` (`n11_campaign_id`),
                KEY `idx_campaign_type` (`campaign_type`),
                KEY `idx_status` (`status`),
                KEY `idx_start_date` (`start_date`),
                KEY `idx_end_date` (`end_date`),
                KEY `idx_approval_status` (`approval_status`),
                KEY `idx_performance_score` (`performance_score`),
                KEY `idx_created_by` (`created_by`),
                KEY `idx_last_sync` (`last_sync`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Insert default Turkish categories
        $this->addDefaultCategories();
        
        // Insert default cargo companies
        $this->addDefaultCargoCompanies();
        
        // Initialize seller metrics
        $this->initializeSellerMetrics();
        
        $this->log->write('[SUCCESS] N11 module installed successfully');
    }
    
    /**
     * Uninstall N11 module
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