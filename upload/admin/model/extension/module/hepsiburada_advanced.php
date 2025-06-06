<?php
/**
 * Hepsiburada Advanced Model
 * MesChain-Sync Enterprise - OpenCart 3.0.4.0 Compatible
 * Turkish E-commerce Platform Data Management
 * 
 * @author MUSTI TEAM - MesChain Development
 * @version 4.0.0
 * @since 2024
 * @copyright 2024 MesChain Technologies
 */

class ModelExtensionModuleHepsiburadaAdvanced extends Model {
    
    /**
     * Install module tables and initial data
     * 
     * @return void
     */
    public function install() {
        // Create main tracking table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_products` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `hepsiburada_id` varchar(50) DEFAULT NULL,
                `listing_id` varchar(50) DEFAULT NULL,
                `merchant_sku` varchar(100) DEFAULT NULL,
                `status` enum('active','inactive','draft','pending','error') DEFAULT 'draft',
                `optimization_score` int(3) DEFAULT 0,
                `last_sync` datetime DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `product_id` (`product_id`),
                KEY `idx_hepsiburada_id` (`hepsiburada_id`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create analytics table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_analytics` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `date` date NOT NULL,
                `views` int(11) DEFAULT 0,
                `clicks` int(11) DEFAULT 0,
                `orders` int(11) DEFAULT 0,
                `revenue` decimal(15,4) DEFAULT 0.0000,
                `conversion_rate` decimal(5,2) DEFAULT 0.00,
                `ranking_position` int(11) DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `product_date` (`product_id`, `date`),
                KEY `idx_date` (`date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create cargo tracking table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_cargo` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `order_id` int(11) NOT NULL,
                `hepsiburada_order_id` varchar(50) NOT NULL,
                `cargo_provider` varchar(50) DEFAULT NULL,
                `tracking_number` varchar(100) DEFAULT NULL,
                `cargo_cost` decimal(15,4) DEFAULT 0.0000,
                `estimated_delivery` date DEFAULT NULL,
                `actual_delivery` date DEFAULT NULL,
                `status` enum('preparing','shipped','in_transit','delivered','returned','cancelled') DEFAULT 'preparing',
                `delivery_region` varchar(100) DEFAULT NULL,
                `optimization_reason` text DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `order_id` (`order_id`),
                KEY `idx_hepsiburada_order` (`hepsiburada_order_id`),
                KEY `idx_cargo_provider` (`cargo_provider`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create category mapping table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `opencart_category_id` int(11) NOT NULL,
                `hepsiburada_category_id` varchar(50) NOT NULL,
                `category_name_tr` varchar(255) NOT NULL,
                `category_path` text DEFAULT NULL,
                `commission_rate` decimal(5,2) DEFAULT NULL,
                `attributes_mapping` text DEFAULT NULL,
                `is_active` tinyint(1) DEFAULT 1,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `mapping` (`opencart_category_id`, `hepsiburada_category_id`),
                KEY `idx_opencart_cat` (`opencart_category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create performance metrics table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_performance` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `metric_type` varchar(50) NOT NULL,
                `metric_date` date NOT NULL,
                `metric_value` decimal(15,4) DEFAULT 0.0000,
                `additional_data` text DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `metric_date_type` (`metric_type`, `metric_date`),
                KEY `idx_metric_type` (`metric_type`),
                KEY `idx_date` (`metric_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    /**
     * Uninstall module (remove tables)
     * 
     * @return void
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hepsiburada_products`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hepsiburada_analytics`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hepsiburada_cargo`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hepsiburada_categories`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hepsiburada_performance`");
    }
    
    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    public function getDashboardStats() {
        $stats = array();
        
        // Total products
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "hepsiburada_products`");
        $stats['total_products'] = $query->row['total'];
        
        // Active listings
        $query = $this->db->query("SELECT COUNT(*) as active FROM `" . DB_PREFIX . "hepsiburada_products` WHERE status = 'active'");
        $stats['active_listings'] = $query->row['active'];
        
        // Today's sales
        $query = $this->db->query("
            SELECT SUM(revenue) as today_revenue, SUM(orders) as today_orders 
            FROM `" . DB_PREFIX . "hepsiburada_analytics` 
            WHERE date = CURDATE()
        ");
        $stats['today_revenue'] = $query->row['today_revenue'] ?: 0;
        $stats['today_orders'] = $query->row['today_orders'] ?: 0;
        
        // This month performance
        $query = $this->db->query("
            SELECT SUM(revenue) as month_revenue, SUM(orders) as month_orders, AVG(conversion_rate) as avg_conversion
            FROM `" . DB_PREFIX . "hepsiburada_analytics` 
            WHERE YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())
        ");
        $stats['month_revenue'] = $query->row['month_revenue'] ?: 0;
        $stats['month_orders'] = $query->row['month_orders'] ?: 0;
        $stats['avg_conversion_rate'] = $query->row['avg_conversion'] ?: 0;
        
        // Average optimization score
        $query = $this->db->query("SELECT AVG(optimization_score) as avg_score FROM `" . DB_PREFIX . "hepsiburada_products` WHERE status = 'active'");
        $stats['avg_optimization_score'] = round($query->row['avg_score'] ?: 0, 1);
        
        return $stats;
    }
    
    /**
     * Get product for optimization
     * 
     * @param int $product_id
     * @return array|null
     */
    public function getProduct($product_id) {
        $query = $this->db->query("
            SELECT p.*, pd.name, pd.description, pd.meta_title, pd.meta_description,
                   hp.hepsiburada_id, hp.listing_id, hp.status as listing_status, hp.optimization_score
            FROM `" . DB_PREFIX . "product` p
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
            LEFT JOIN `" . DB_PREFIX . "hepsiburada_products` hp ON p.product_id = hp.product_id
            WHERE p.product_id = '" . (int)$product_id . "' AND p.status = '1'
        ");
        
        if ($query->num_rows) {
            $product = $query->row;
            
            // Get product images
            $image_query = $this->db->query("SELECT image FROM `" . DB_PREFIX . "product_image` WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order");
            $product['images'] = array();
            foreach ($image_query->rows as $image) {
                $product['images'][] = $image['image'];
            }
            
            // Get product attributes
            $attr_query = $this->db->query("
                SELECT pa.attribute_id, ad.name as attribute_name, pa.text as attribute_value
                FROM `" . DB_PREFIX . "product_attribute` pa
                LEFT JOIN `" . DB_PREFIX . "attribute_description` ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "')
                WHERE pa.product_id = '" . (int)$product_id . "'
            ");
            $product['attributes'] = $attr_query->rows;
            
            return $product;
        }
        
        return null;
    }
    
    /**
     * Update listing status
     * 
     * @param int $product_id
     * @param string $status
     * @param string $listing_id
     * @return bool
     */
    public function updateListingStatus($product_id, $status, $listing_id = null) {
        $update_data = array(
            'status' => $status,
            'last_sync' => 'NOW()',
            'updated_at' => 'NOW()'
        );
        
        if ($listing_id) {
            $update_data['listing_id'] = $listing_id;
        }
        
        // Check if record exists
        $check_query = $this->db->query("SELECT id FROM `" . DB_PREFIX . "hepsiburada_products` WHERE product_id = '" . (int)$product_id . "'");
        
        if ($check_query->num_rows) {
            // Update existing record
            $set_parts = array();
            foreach ($update_data as $field => $value) {
                if ($value === 'NOW()') {
                    $set_parts[] = "`$field` = NOW()";
                } else {
                    $set_parts[] = "`$field` = '" . $this->db->escape($value) . "'";
                }
            }
            
            $this->db->query("
                UPDATE `" . DB_PREFIX . "hepsiburada_products` 
                SET " . implode(', ', $set_parts) . "
                WHERE product_id = '" . (int)$product_id . "'
            ");
        } else {
            // Insert new record
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "hepsiburada_products` (product_id, status, listing_id, last_sync) 
                VALUES ('" . (int)$product_id . "', '" . $this->db->escape($status) . "', '" . $this->db->escape($listing_id) . "', NOW())
            ");
        }
        
        return true;
    }
    
    /**
     * Get order details
     * 
     * @param int $order_id
     * @return array|null
     */
    public function getOrder($order_id) {
        $query = $this->db->query("
            SELECT o.*, CONCAT(o.shipping_firstname, ' ', o.shipping_lastname) as customer_name,
                   hc.cargo_provider, hc.tracking_number, hc.cargo_cost, hc.estimated_delivery
            FROM `" . DB_PREFIX . "order` o
            LEFT JOIN `" . DB_PREFIX . "hepsiburada_cargo` hc ON o.order_id = hc.order_id
            WHERE o.order_id = '" . (int)$order_id . "'
        ");
        
        if ($query->num_rows) {
            $order = $query->row;
            
            // Get order products
            $product_query = $this->db->query("
                SELECT op.*, p.weight, p.length, p.width, p.height
                FROM `" . DB_PREFIX . "order_product` op
                LEFT JOIN `" . DB_PREFIX . "product` p ON op.product_id = p.product_id
                WHERE op.order_id = '" . (int)$order_id . "'
            ");
            $order['products'] = $product_query->rows;
            
            return $order;
        }
        
        return null;
    }
    
    /**
     * Update order cargo information
     * 
     * @param int $order_id
     * @param array $cargo_data
     * @return bool
     */
    public function updateOrderCargo($order_id, $cargo_data) {
        // Check if record exists
        $check_query = $this->db->query("SELECT id FROM `" . DB_PREFIX . "hepsiburada_cargo` WHERE order_id = '" . (int)$order_id . "'");
        
        if ($check_query->num_rows) {
            // Update existing record
            $this->db->query("
                UPDATE `" . DB_PREFIX . "hepsiburada_cargo` 
                SET cargo_provider = '" . $this->db->escape($cargo_data['provider']) . "',
                    cargo_cost = '" . (float)$cargo_data['cost'] . "',
                    estimated_delivery = '" . $this->db->escape($cargo_data['estimated_delivery']) . "',
                    optimization_reason = '" . $this->db->escape($cargo_data['reason']) . "',
                    updated_at = NOW()
                WHERE order_id = '" . (int)$order_id . "'
            ");
        } else {
            // Insert new record
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "hepsiburada_cargo` 
                (order_id, cargo_provider, cargo_cost, estimated_delivery, optimization_reason) 
                VALUES (
                    '" . (int)$order_id . "',
                    '" . $this->db->escape($cargo_data['provider']) . "',
                    '" . (float)$cargo_data['cost'] . "',
                    '" . $this->db->escape($cargo_data['estimated_delivery']) . "',
                    '" . $this->db->escape($cargo_data['reason']) . "'
                )
            ");
        }
        
        return true;
    }
    
    /**
     * Get total listings count
     * 
     * @return int
     */
    public function getTotalListings() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "hepsiburada_products`");
        return (int)$query->row['total'];
    }
    
    /**
     * Get active listings count
     * 
     * @return int
     */
    public function getActiveListings() {
        $query = $this->db->query("SELECT COUNT(*) as active FROM `" . DB_PREFIX . "hepsiburada_products` WHERE status = 'active'");
        return (int)$query->row['active'];
    }
    
    /**
     * Get listing success rate
     * 
     * @return float
     */
    public function getListingSuccessRate() {
        $total_query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "hepsiburada_products`");
        $success_query = $this->db->query("SELECT COUNT(*) as success FROM `" . DB_PREFIX . "hepsiburada_products` WHERE status = 'active'");
        
        $total = (int)$total_query->row['total'];
        $success = (int)$success_query->row['success'];
        
        return $total > 0 ? round(($success / $total) * 100, 2) : 0;
    }
    
    /**
     * Get average optimization score
     * 
     * @return float
     */
    public function getAverageOptimizationScore() {
        $query = $this->db->query("SELECT AVG(optimization_score) as avg_score FROM `" . DB_PREFIX . "hepsiburada_products` WHERE status = 'active'");
        return round($query->row['avg_score'] ?: 0, 1);
    }
    
    /**
     * Get top performing categories
     * 
     * @return array
     */
    public function getTopPerformingCategories() {
        $query = $this->db->query("
            SELECT hc.category_name_tr, SUM(ha.revenue) as total_revenue, COUNT(hp.id) as product_count
            FROM `" . DB_PREFIX . "hepsiburada_categories` hc
            LEFT JOIN `" . DB_PREFIX . "hepsiburada_products` hp ON hc.opencart_category_id = hp.product_id
            LEFT JOIN `" . DB_PREFIX . "hepsiburada_analytics` ha ON hp.product_id = ha.product_id
            WHERE ha.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY hc.id
            ORDER BY total_revenue DESC
            LIMIT 5
        ");
        return $query->rows;
    }
    
    /**
     * Get total shipments count
     * 
     * @return int
     */
    public function getTotalShipments() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "hepsiburada_cargo`");
        return (int)$query->row['total'];
    }
    
    /**
     * Get on-time delivery rate
     * 
     * @return float
     */
    public function getOnTimeDeliveryRate() {
        $total_query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "hepsiburada_cargo` WHERE actual_delivery IS NOT NULL");
        $ontime_query = $this->db->query("SELECT COUNT(*) as ontime FROM `" . DB_PREFIX . "hepsiburada_cargo` WHERE actual_delivery <= estimated_delivery AND actual_delivery IS NOT NULL");
        
        $total = (int)$total_query->row['total'];
        $ontime = (int)$ontime_query->row['ontime'];
        
        return $total > 0 ? round(($ontime / $total) * 100, 2) : 0;
    }
    
    /**
     * Get average delivery time
     * 
     * @return float
     */
    public function getAverageDeliveryTime() {
        $query = $this->db->query("
            SELECT AVG(DATEDIFF(actual_delivery, created_at)) as avg_days
            FROM `" . DB_PREFIX . "hepsiburada_cargo` 
            WHERE actual_delivery IS NOT NULL AND created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
        ");
        return round($query->row['avg_days'] ?: 0, 1);
    }
    
    /**
     * Get cargo cost optimization percentage
     * 
     * @return float
     */
    public function getCargoCostOptimization() {
        // This would compare actual costs vs standard rates
        $query = $this->db->query("
            SELECT AVG(cargo_cost) as avg_cost
            FROM `" . DB_PREFIX . "hepsiburada_cargo` 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        
        $avg_cost = $query->row['avg_cost'] ?: 0;
        $standard_cost = 15.00; // Example standard shipping cost
        
        return $avg_cost < $standard_cost ? round((($standard_cost - $avg_cost) / $standard_cost) * 100, 2) : 0;
    }
    
    /**
     * Get preferred cargo providers
     * 
     * @return array
     */
    public function getPreferredCargoProviders() {
        $query = $this->db->query("
            SELECT cargo_provider, COUNT(*) as usage_count, AVG(cargo_cost) as avg_cost,
                   SUM(CASE WHEN actual_delivery <= estimated_delivery THEN 1 ELSE 0 END) as ontime_count
            FROM `" . DB_PREFIX . "hepsiburada_cargo` 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY cargo_provider
            ORDER BY usage_count DESC
            LIMIT 5
        ");
        return $query->rows;
    }
    
    /**
     * Store analytics data
     * 
     * @param int $product_id
     * @param array $analytics_data
     * @return bool
     */
    public function storeAnalytics($product_id, $analytics_data) {
        $date = $analytics_data['date'] ?? date('Y-m-d');
        
        // Check if record exists for today
        $check_query = $this->db->query("
            SELECT id FROM `" . DB_PREFIX . "hepsiburada_analytics` 
            WHERE product_id = '" . (int)$product_id . "' AND date = '" . $this->db->escape($date) . "'
        ");
        
        if ($check_query->num_rows) {
            // Update existing record
            $this->db->query("
                UPDATE `" . DB_PREFIX . "hepsiburada_analytics` 
                SET views = '" . (int)($analytics_data['views'] ?? 0) . "',
                    clicks = '" . (int)($analytics_data['clicks'] ?? 0) . "',
                    orders = '" . (int)($analytics_data['orders'] ?? 0) . "',
                    revenue = '" . (float)($analytics_data['revenue'] ?? 0) . "',
                    conversion_rate = '" . (float)($analytics_data['conversion_rate'] ?? 0) . "',
                    ranking_position = '" . (int)($analytics_data['ranking_position'] ?? 0) . "'
                WHERE product_id = '" . (int)$product_id . "' AND date = '" . $this->db->escape($date) . "'
            ");
        } else {
            // Insert new record
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "hepsiburada_analytics` 
                (product_id, date, views, clicks, orders, revenue, conversion_rate, ranking_position) 
                VALUES (
                    '" . (int)$product_id . "',
                    '" . $this->db->escape($date) . "',
                    '" . (int)($analytics_data['views'] ?? 0) . "',
                    '" . (int)($analytics_data['clicks'] ?? 0) . "',
                    '" . (int)($analytics_data['orders'] ?? 0) . "',
                    '" . (float)($analytics_data['revenue'] ?? 0) . "',
                    '" . (float)($analytics_data['conversion_rate'] ?? 0) . "',
                    '" . (int)($analytics_data['ranking_position'] ?? 0) . "'
                )
            ");
        }
        
        return true;
    }
}
?>