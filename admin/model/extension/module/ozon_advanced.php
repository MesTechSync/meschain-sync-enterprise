<?php
/**
 * Ozon Advanced Model
 * MesChain-Sync Enterprise - OpenCart 3.0.4.0 Compatible
 * Russian E-commerce Platform Data Management
 * 
 * @author MUSTI TEAM - MesChain Development
 * @version 4.0.0
 * @since 2024
 * @copyright 2024 MesChain Technologies
 */

class ModelExtensionModuleOzonAdvanced extends Model {
    
    /**
     * Install module tables and initial data
     * 
     * @return void
     */
    public function install() {
        // Create main products tracking table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_products` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `ozon_product_id` varchar(50) DEFAULT NULL,
                `sku` varchar(100) DEFAULT NULL,
                `status` enum('active','inactive','draft','pending','error','blocked') DEFAULT 'draft',
                `localization_score` int(3) DEFAULT 0,
                `price_rub` decimal(15,4) DEFAULT 0.0000,
                `price_try` decimal(15,4) DEFAULT 0.0000,
                `exchange_rate` decimal(10,6) DEFAULT 0.000000,
                `fulfillment_type` enum('FBO','FBS','mixed') DEFAULT 'FBS',
                `compliance_status` enum('pending','approved','rejected','review') DEFAULT 'pending',
                `last_sync` datetime DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `product_id` (`product_id`),
                KEY `idx_ozon_product_id` (`ozon_product_id`),
                KEY `idx_status` (`status`),
                KEY `idx_fulfillment_type` (`fulfillment_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create analytics table for Russian market
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_analytics` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `date` date NOT NULL,
                `views` int(11) DEFAULT 0,
                `clicks` int(11) DEFAULT 0,
                `orders` int(11) DEFAULT 0,
                `revenue_rub` decimal(15,4) DEFAULT 0.0000,
                `revenue_try` decimal(15,4) DEFAULT 0.0000,
                `conversion_rate` decimal(5,2) DEFAULT 0.00,
                `ranking_position` int(11) DEFAULT NULL,
                `market_share_percent` decimal(5,2) DEFAULT 0.00,
                `regional_performance` text DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `product_date` (`product_id`, `date`),
                KEY `idx_date` (`date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create fulfillment tracking table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_fulfillment` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `order_id` int(11) NOT NULL,
                `ozon_order_id` varchar(50) NOT NULL,
                `fulfillment_type` enum('FBO','FBS') DEFAULT 'FBS',
                `warehouse_id` varchar(50) DEFAULT NULL,
                `tracking_number` varchar(100) DEFAULT NULL,
                `delivery_cost_rub` decimal(15,4) DEFAULT 0.0000,
                `estimated_delivery` date DEFAULT NULL,
                `actual_delivery` date DEFAULT NULL,
                `status` enum('preparing','shipped','in_transit','delivered','returned','cancelled') DEFAULT 'preparing',
                `customs_status` enum('pending','cleared','delayed','rejected') DEFAULT 'pending',
                `delivery_region` varchar(100) DEFAULT NULL,
                `optimization_reason` text DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `order_id` (`order_id`),
                KEY `idx_ozon_order` (`ozon_order_id`),
                KEY `idx_fulfillment_type` (`fulfillment_type`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create Russian category mapping table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `opencart_category_id` int(11) NOT NULL,
                `ozon_category_id` varchar(50) NOT NULL,
                `category_name_ru` varchar(255) NOT NULL,
                `category_name_en` varchar(255) DEFAULT NULL,
                `category_path` text DEFAULT NULL,
                `commission_rate` decimal(5,2) DEFAULT NULL,
                `attributes_mapping` text DEFAULT NULL,
                `compliance_requirements` text DEFAULT NULL,
                `seasonal_trends` text DEFAULT NULL,
                `is_active` tinyint(1) DEFAULT 1,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `mapping` (`opencart_category_id`, `ozon_category_id`),
                KEY `idx_opencart_cat` (`opencart_category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create currency exchange rates table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_currency_rates` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `from_currency` varchar(3) NOT NULL,
                `to_currency` varchar(3) NOT NULL,
                `exchange_rate` decimal(10,6) NOT NULL,
                `rate_date` date NOT NULL,
                `source` varchar(50) DEFAULT 'cbr.ru',
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `currency_date` (`from_currency`, `to_currency`, `rate_date`),
                KEY `idx_currencies` (`from_currency`, `to_currency`),
                KEY `idx_date` (`rate_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create Russian market performance table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_market_performance` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `metric_type` varchar(50) NOT NULL,
                `metric_date` date NOT NULL,
                `metric_value` decimal(15,4) DEFAULT 0.0000,
                `region` varchar(100) DEFAULT 'all',
                `additional_data` text DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `metric_date_type_region` (`metric_type`, `metric_date`, `region`),
                KEY `idx_metric_type` (`metric_type`),
                KEY `idx_date` (`metric_date`),
                KEY `idx_region` (`region`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create translation cache table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_translations` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `source_text` text NOT NULL,
                `translated_text` text NOT NULL,
                `source_language` varchar(5) DEFAULT 'tr',
                `target_language` varchar(5) DEFAULT 'ru',
                `translation_quality` enum('basic','professional','premium') DEFAULT 'professional',
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_languages` (`source_language`, `target_language`),
                KEY `idx_quality` (`translation_quality`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    /**
     * Uninstall module (remove tables)
     * 
     * @return void
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ozon_products`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ozon_analytics`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ozon_fulfillment`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ozon_categories`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ozon_currency_rates`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ozon_market_performance`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ozon_translations`");
    }
    
    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    public function getDashboardStats() {
        $stats = array();
        
        // Total products
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ozon_products`");
        $stats['total_products'] = $query->row['total'];
        
        // Active listings
        $query = $this->db->query("SELECT COUNT(*) as active FROM `" . DB_PREFIX . "ozon_products` WHERE status = 'active'");
        $stats['active_listings'] = $query->row['active'];
        
        // Today's sales in RUB
        $query = $this->db->query("
            SELECT SUM(revenue_rub) as today_revenue_rub, SUM(revenue_try) as today_revenue_try, SUM(orders) as today_orders 
            FROM `" . DB_PREFIX . "ozon_analytics` 
            WHERE date = CURDATE()
        ");
        $stats['today_revenue_rub'] = $query->row['today_revenue_rub'] ?: 0;
        $stats['today_revenue_try'] = $query->row['today_revenue_try'] ?: 0;
        $stats['today_orders'] = $query->row['today_orders'] ?: 0;
        
        // This month performance
        $query = $this->db->query("
            SELECT SUM(revenue_rub) as month_revenue_rub, SUM(revenue_try) as month_revenue_try, 
                   SUM(orders) as month_orders, AVG(conversion_rate) as avg_conversion,
                   AVG(market_share_percent) as avg_market_share
            FROM `" . DB_PREFIX . "ozon_analytics` 
            WHERE YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())
        ");
        $stats['month_revenue_rub'] = $query->row['month_revenue_rub'] ?: 0;
        $stats['month_revenue_try'] = $query->row['month_revenue_try'] ?: 0;
        $stats['month_orders'] = $query->row['month_orders'] ?: 0;
        $stats['avg_conversion_rate'] = $query->row['avg_conversion'] ?: 0;
        $stats['avg_market_share'] = $query->row['avg_market_share'] ?: 0;
        
        // Average localization score
        $query = $this->db->query("SELECT AVG(localization_score) as avg_score FROM `" . DB_PREFIX . "ozon_products` WHERE status = 'active'");
        $stats['avg_localization_score'] = round($query->row['avg_score'] ?: 0, 1);
        
        // Current exchange rate
        $query = $this->db->query("
            SELECT exchange_rate FROM `" . DB_PREFIX . "ozon_currency_rates` 
            WHERE from_currency = 'TRY' AND to_currency = 'RUB' 
            ORDER BY rate_date DESC LIMIT 1
        ");
        $stats['current_try_rub_rate'] = $query->row['exchange_rate'] ?? 0;
        
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
                   op.ozon_product_id, op.sku, op.status as listing_status, op.localization_score,
                   op.price_rub, op.price_try, op.exchange_rate, op.fulfillment_type, op.compliance_status
            FROM `" . DB_PREFIX . "product` p
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
            LEFT JOIN `" . DB_PREFIX . "ozon_products` op ON p.product_id = op.product_id
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
     * @param string $ozon_product_id
     * @return bool
     */
    public function updateListingStatus($product_id, $status, $ozon_product_id = null) {
        $update_data = array(
            'status' => $status,
            'last_sync' => 'NOW()',
            'updated_at' => 'NOW()'
        );
        
        if ($ozon_product_id) {
            $update_data['ozon_product_id'] = $ozon_product_id;
        }
        
        // Check if record exists
        $check_query = $this->db->query("SELECT id FROM `" . DB_PREFIX . "ozon_products` WHERE product_id = '" . (int)$product_id . "'");
        
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
                UPDATE `" . DB_PREFIX . "ozon_products` 
                SET " . implode(', ', $set_parts) . "
                WHERE product_id = '" . (int)$product_id . "'
            ");
        } else {
            // Insert new record
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "ozon_products` (product_id, status, ozon_product_id, last_sync) 
                VALUES ('" . (int)$product_id . "', '" . $this->db->escape($status) . "', '" . $this->db->escape($ozon_product_id) . "', NOW())
            ");
        }
        
        return true;
    }
    
    /**
     * Get order details for fulfillment optimization
     * 
     * @param int $order_id
     * @return array|null
     */
    public function getOrder($order_id) {
        $query = $this->db->query("
            SELECT o.*, CONCAT(o.shipping_firstname, ' ', o.shipping_lastname) as customer_name,
                   of.fulfillment_type, of.warehouse_id, of.tracking_number, of.delivery_cost_rub, of.estimated_delivery
            FROM `" . DB_PREFIX . "order` o
            LEFT JOIN `" . DB_PREFIX . "ozon_fulfillment` of ON o.order_id = of.order_id
            WHERE o.order_id = '" . (int)$order_id . "'
        ");
        
        if ($query->num_rows) {
            $order = $query->row;
            
            // Get order products
            $product_query = $this->db->query("
                SELECT op.*, p.weight, p.length, p.width, p.height,
                       ozp.price_rub, ozp.fulfillment_type as product_fulfillment_type
                FROM `" . DB_PREFIX . "order_product` op
                LEFT JOIN `" . DB_PREFIX . "product` p ON op.product_id = p.product_id
                LEFT JOIN `" . DB_PREFIX . "ozon_products` ozp ON op.product_id = ozp.product_id
                WHERE op.order_id = '" . (int)$order_id . "'
            ");
            $order['products'] = $product_query->rows;
            
            return $order;
        }
        
        return null;
    }
    
    /**
     * Update order fulfillment information
     * 
     * @param int $order_id
     * @param array $fulfillment_data
     * @return bool
     */
    public function updateOrderFulfillment($order_id, $fulfillment_data) {
        // Check if record exists
        $check_query = $this->db->query("SELECT id FROM `" . DB_PREFIX . "ozon_fulfillment` WHERE order_id = '" . (int)$order_id . "'");
        
        if ($check_query->num_rows) {
            // Update existing record
            $this->db->query("
                UPDATE `" . DB_PREFIX . "ozon_fulfillment` 
                SET fulfillment_type = '" . $this->db->escape($fulfillment_data['type']) . "',
                    warehouse_id = '" . $this->db->escape($fulfillment_data['warehouse']) . "',
                    delivery_cost_rub = '" . (float)$fulfillment_data['cost'] . "',
                    estimated_delivery = '" . $this->db->escape($fulfillment_data['delivery_time']) . "',
                    optimization_reason = '" . $this->db->escape($fulfillment_data['reason']) . "',
                    updated_at = NOW()
                WHERE order_id = '" . (int)$order_id . "'
            ");
        } else {
            // Insert new record
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "ozon_fulfillment` 
                (order_id, fulfillment_type, warehouse_id, delivery_cost_rub, estimated_delivery, optimization_reason) 
                VALUES (
                    '" . (int)$order_id . "',
                    '" . $this->db->escape($fulfillment_data['type']) . "',
                    '" . $this->db->escape($fulfillment_data['warehouse']) . "',
                    '" . (float)$fulfillment_data['cost'] . "',
                    '" . $this->db->escape($fulfillment_data['delivery_time']) . "',
                    '" . $this->db->escape($fulfillment_data['reason']) . "'
                )
            ");
        }
        
        return true;
    }
    
    /**
     * Store currency exchange rate
     * 
     * @param string $from_currency
     * @param string $to_currency
     * @param float $rate
     * @param string $date
     * @return bool
     */
    public function storeCurrencyRate($from_currency, $to_currency, $rate, $date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        // Check if rate exists for this date
        $check_query = $this->db->query("
            SELECT id FROM `" . DB_PREFIX . "ozon_currency_rates` 
            WHERE from_currency = '" . $this->db->escape($from_currency) . "' 
            AND to_currency = '" . $this->db->escape($to_currency) . "' 
            AND rate_date = '" . $this->db->escape($date) . "'
        ");
        
        if ($check_query->num_rows) {
            // Update existing rate
            $this->db->query("
                UPDATE `" . DB_PREFIX . "ozon_currency_rates` 
                SET exchange_rate = '" . (float)$rate . "'
                WHERE from_currency = '" . $this->db->escape($from_currency) . "' 
                AND to_currency = '" . $this->db->escape($to_currency) . "' 
                AND rate_date = '" . $this->db->escape($date) . "'
            ");
        } else {
            // Insert new rate
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "ozon_currency_rates` 
                (from_currency, to_currency, exchange_rate, rate_date) 
                VALUES (
                    '" . $this->db->escape($from_currency) . "',
                    '" . $this->db->escape($to_currency) . "',
                    '" . (float)$rate . "',
                    '" . $this->db->escape($date) . "'
                )
            ");
        }
        
        return true;
    }
    
    /**
     * Get Russian market trends
     * 
     * @return array
     */
    public function getRussianMarketTrends() {
        $query = $this->db->query("
            SELECT 
                DATE_FORMAT(metric_date, '%Y-%m') as month,
                SUM(CASE WHEN metric_type = 'total_sales_rub' THEN metric_value ELSE 0 END) as sales,
                AVG(CASE WHEN metric_type = 'avg_conversion_rate' THEN metric_value ELSE 0 END) as conversion,
                AVG(CASE WHEN metric_type = 'market_growth_rate' THEN metric_value ELSE 0 END) as growth
            FROM `" . DB_PREFIX . "ozon_market_performance` 
            WHERE metric_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(metric_date, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12
        ");
        
        return $query->rows;
    }
    
    /**
     * Get seasonal patterns for Russian market
     * 
     * @return array
     */
    public function getSeasonalPatterns() {
        $query = $this->db->query("
            SELECT 
                MONTH(metric_date) as month,
                AVG(metric_value) as avg_performance
            FROM `" . DB_PREFIX . "ozon_market_performance` 
            WHERE metric_type = 'seasonal_index'
            GROUP BY MONTH(metric_date)
            ORDER BY month
        ");
        
        return $query->rows;
    }
    
    /**
     * Get currency stability metrics
     * 
     * @return array
     */
    public function getCurrencyStability() {
        $query = $this->db->query("
            SELECT 
                rate_date,
                exchange_rate,
                LAG(exchange_rate) OVER (ORDER BY rate_date) as prev_rate
            FROM `" . DB_PREFIX . "ozon_currency_rates` 
            WHERE from_currency = 'TRY' AND to_currency = 'RUB'
            AND rate_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ORDER BY rate_date DESC
        ");
        
        $rates = $query->rows;
        $volatility = 0;
        
        if (count($rates) > 1) {
            $changes = array();
            foreach ($rates as $rate) {
                if ($rate['prev_rate']) {
                    $changes[] = abs(($rate['exchange_rate'] - $rate['prev_rate']) / $rate['prev_rate']) * 100;
                }
            }
            $volatility = count($changes) > 0 ? array_sum($changes) / count($changes) : 0;
        }
        
        return array(
            'current_rate' => $rates[0]['exchange_rate'] ?? 0,
            'avg_volatility_percent' => round($volatility, 2),
            'stability_rating' => $volatility < 2 ? 'Stable' : ($volatility < 5 ? 'Moderate' : 'Volatile')
        );
    }
    
    /**
     * Get regional preferences
     * 
     * @return array
     */
    public function getRegionalPreferences() {
        $query = $this->db->query("
            SELECT 
                region,
                SUM(CASE WHEN metric_type = 'sales_volume' THEN metric_value ELSE 0 END) as total_sales,
                AVG(CASE WHEN metric_type = 'avg_order_value' THEN metric_value ELSE 0 END) as avg_order_value,
                COUNT(DISTINCT metric_date) as data_points
            FROM `" . DB_PREFIX . "ozon_market_performance` 
            WHERE region != 'all' AND metric_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            GROUP BY region
            ORDER BY total_sales DESC
            LIMIT 10
        ");
        
        return $query->rows;
    }
    
    /**
     * Get competition intensity metrics
     * 
     * @return array
     */
    public function getCompetitionIntensity() {
        $query = $this->db->query("
            SELECT AVG(metric_value) as avg_intensity
            FROM `" . DB_PREFIX . "ozon_market_performance` 
            WHERE metric_type = 'competition_intensity' 
            AND metric_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        
        $intensity = $query->row['avg_intensity'] ?? 0;
        
        return array(
            'intensity_score' => round($intensity, 1),
            'competition_level' => $intensity < 3 ? 'Low' : ($intensity < 6 ? 'Medium' : 'High'),
            'market_saturation' => $intensity > 7 ? 'High' : 'Moderate'
        );
    }
    
    /**
     * Get total shipments count
     * 
     * @return int
     */
    public function getTotalShipments() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ozon_fulfillment`");
        return (int)$query->row['total'];
    }
    
    /**
     * Get Ozon fulfillment rate (FBO vs FBS)
     * 
     * @return array
     */
    public function getOzonFulfillmentRate() {
        $query = $this->db->query("
            SELECT 
                fulfillment_type,
                COUNT(*) as count,
                (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM `" . DB_PREFIX . "ozon_fulfillment`)) as percentage
            FROM `" . DB_PREFIX . "ozon_fulfillment` 
            GROUP BY fulfillment_type
        ");
        
        $result = array('FBO' => 0, 'FBS' => 0);
        foreach ($query->rows as $row) {
            $result[$row['fulfillment_type']] = round($row['percentage'], 2);
        }
        
        return $result;
    }
    
    /**
     * Get cross-border delivery time
     * 
     * @return float
     */
    public function getCrossBorderDeliveryTime() {
        $query = $this->db->query("
            SELECT AVG(DATEDIFF(actual_delivery, created_at)) as avg_days
            FROM `" . DB_PREFIX . "ozon_fulfillment` 
            WHERE actual_delivery IS NOT NULL 
            AND delivery_region NOT LIKE '%Россия%'
            AND created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
        ");
        
        return round($query->row['avg_days'] ?: 0, 1);
    }
    
    /**
     * Get warehouse efficiency metrics
     * 
     * @return array
     */
    public function getWarehouseEfficiency() {
        $query = $this->db->query("
            SELECT 
                warehouse_id,
                COUNT(*) as total_orders,
                AVG(DATEDIFF(actual_delivery, created_at)) as avg_delivery_time,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as successful_deliveries
            FROM `" . DB_PREFIX . "ozon_fulfillment` 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            AND warehouse_id IS NOT NULL
            GROUP BY warehouse_id
            ORDER BY successful_deliveries DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * Get customs clearance time
     * 
     * @return float
     */
    public function getCustomsClearanceTime() {
        $query = $this->db->query("
            SELECT AVG(
                CASE 
                    WHEN customs_status = 'cleared' THEN DATEDIFF(updated_at, created_at)
                    ELSE NULL
                END
            ) as avg_clearance_days
            FROM `" . DB_PREFIX . "ozon_fulfillment` 
            WHERE customs_status = 'cleared'
            AND created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
        ");
        
        return round($query->row['avg_clearance_days'] ?: 0, 1);
    }
    
    /**
     * Store analytics data with Russian market metrics
     * 
     * @param int $product_id
     * @param array $analytics_data
     * @return bool
     */
    public function storeAnalytics($product_id, $analytics_data) {
        $date = $analytics_data['date'] ?? date('Y-m-d');
        
        // Check if record exists for today
        $check_query = $this->db->query("
            SELECT id FROM `" . DB_PREFIX . "ozon_analytics` 
            WHERE product_id = '" . (int)$product_id . "' AND date = '" . $this->db->escape($date) . "'
        ");
        
        if ($check_query->num_rows) {
            // Update existing record
            $this->db->query("
                UPDATE `" . DB_PREFIX . "ozon_analytics` 
                SET views = '" . (int)($analytics_data['views'] ?? 0) . "',
                    clicks = '" . (int)($analytics_data['clicks'] ?? 0) . "',
                    orders = '" . (int)($analytics_data['orders'] ?? 0) . "',
                    revenue_rub = '" . (float)($analytics_data['revenue_rub'] ?? 0) . "',
                    revenue_try = '" . (float)($analytics_data['revenue_try'] ?? 0) . "',
                    conversion_rate = '" . (float)($analytics_data['conversion_rate'] ?? 0) . "',
                    ranking_position = '" . (int)($analytics_data['ranking_position'] ?? 0) . "',
                    market_share_percent = '" . (float)($analytics_data['market_share_percent'] ?? 0) . "',
                    regional_performance = '" . $this->db->escape(json_encode($analytics_data['regional_performance'] ?? array())) . "'
                WHERE product_id = '" . (int)$product_id . "' AND date = '" . $this->db->escape($date) . "'
            ");
        } else {
            // Insert new record
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "ozon_analytics` 
                (product_id, date, views, clicks, orders, revenue_rub, revenue_try, conversion_rate, ranking_position, market_share_percent, regional_performance) 
                VALUES (
                    '" . (int)$product_id . "',
                    '" . $this->db->escape($date) . "',
                    '" . (int)($analytics_data['views'] ?? 0) . "',
                    '" . (int)($analytics_data['clicks'] ?? 0) . "',
                    '" . (int)($analytics_data['orders'] ?? 0) . "',
                    '" . (float)($analytics_data['revenue_rub'] ?? 0) . "',
                    '" . (float)($analytics_data['revenue_try'] ?? 0) . "',
                    '" . (float)($analytics_data['conversion_rate'] ?? 0) . "',
                    '" . (int)($analytics_data['ranking_position'] ?? 0) . "',
                    '" . (float)($analytics_data['market_share_percent'] ?? 0) . "',
                    '" . $this->db->escape(json_encode($analytics_data['regional_performance'] ?? array())) . "'
                )
            ");
        }
        
        return true;
    }
}
?>
</rewritten_file>