<?php
/**
 * N11 Advanced Marketplace Model
 * MesChain-Sync Enterprise - OpenCart 3.0.4.0 Compatible
 * Database operations for N11 advanced features
 * 
 * @author MUSTI TEAM - MesChain Development
 * @version 4.0.0
 * @since 2024
 * @copyright 2024 MesChain Technologies
 */

class ModelExtensionModuleN11Advanced extends Model {
    
    private $logger;
    private $n11_api;
    
    public function __construct($registry) {
        parent::__construct($registry);
        $this->logger = new Log('n11_advanced_model.log');
    }
    
    /**
     * Install N11 Advanced module tables
     * 
     * @return void
     */
    public function install() {
        try {
            // N11 Advanced Product Mappings
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_advanced_products` (
                    `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
                    `product_id` int(11) NOT NULL,
                    `n11_product_id` varchar(50) NOT NULL,
                    `n11_sku` varchar(100) NOT NULL,
                    `listing_status` enum('pending','listed','rejected','delisted') DEFAULT 'pending',
                    `seo_score` decimal(5,2) DEFAULT 0.00,
                    `competitor_count` int(11) DEFAULT 0,
                    `price_position` enum('lowest','competitive','premium','overpriced') DEFAULT 'competitive',
                    `listing_quality` decimal(5,2) DEFAULT 0.00,
                    `campaign_eligibility` tinyint(1) DEFAULT 0,
                    `auto_optimization_enabled` tinyint(1) DEFAULT 1,
                    `last_price_update` datetime DEFAULT NULL,
                    `last_stock_update` datetime DEFAULT NULL,
                    `last_optimization` datetime DEFAULT NULL,
                    `performance_score` decimal(5,2) DEFAULT 0.00,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`mapping_id`),
                    UNIQUE KEY `product_n11_unique` (`product_id`, `n11_product_id`),
                    KEY `listing_status` (`listing_status`),
                    KEY `performance_score` (`performance_score`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // N11 Advanced Analytics
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_advanced_analytics` (
                    `analytics_id` int(11) NOT NULL AUTO_INCREMENT,
                    `product_id` int(11) NOT NULL,
                    `n11_product_id` varchar(50) NOT NULL,
                    `date` date NOT NULL,
                    `views` int(11) DEFAULT 0,
                    `clicks` int(11) DEFAULT 0,
                    `conversions` int(11) DEFAULT 0,
                    `revenue` decimal(10,2) DEFAULT 0.00,
                    `ranking_position` int(11) DEFAULT 0,
                    `competitor_avg_price` decimal(10,2) DEFAULT 0.00,
                    `our_price` decimal(10,2) DEFAULT 0.00,
                    `price_advantage` decimal(5,2) DEFAULT 0.00,
                    `stock_level` int(11) DEFAULT 0,
                    `listing_quality_score` decimal(5,2) DEFAULT 0.00,
                    `seo_score` decimal(5,2) DEFAULT 0.00,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`analytics_id`),
                    UNIQUE KEY `product_date_unique` (`product_id`, `date`),
                    KEY `date_index` (`date`),
                    KEY `product_id` (`product_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // N11 Advanced Campaigns
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_advanced_campaigns` (
                    `campaign_id` int(11) NOT NULL AUTO_INCREMENT,
                    `campaign_name` varchar(255) NOT NULL,
                    `campaign_type` enum('discount','featured','premium','seasonal') NOT NULL,
                    `n11_campaign_id` varchar(50) DEFAULT NULL,
                    `status` enum('active','paused','ended','draft') DEFAULT 'draft',
                    `start_date` datetime NOT NULL,
                    `end_date` datetime NOT NULL,
                    `discount_percentage` decimal(5,2) DEFAULT 0.00,
                    `budget` decimal(10,2) DEFAULT 0.00,
                    `spent_budget` decimal(10,2) DEFAULT 0.00,
                    `target_products` text,
                    `target_categories` text,
                    `performance_metrics` text,
                    `auto_optimization` tinyint(1) DEFAULT 0,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`campaign_id`),
                    KEY `status` (`status`),
                    KEY `campaign_type` (`campaign_type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // N11 Advanced Orders
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_advanced_orders` (
                    `n11_order_id` varchar(50) NOT NULL,
                    `opencart_order_id` int(11) DEFAULT NULL,
                    `order_status` enum('received','processing','shipped','delivered','cancelled','returned') NOT NULL,
                    `n11_order_number` varchar(100) NOT NULL,
                    `customer_info` text,
                    `shipping_info` text,
                    `payment_info` text,
                    `order_items` text,
                    `total_amount` decimal(10,2) NOT NULL,
                    `commission_rate` decimal(5,2) DEFAULT 0.00,
                    `commission_amount` decimal(10,2) DEFAULT 0.00,
                    `net_amount` decimal(10,2) DEFAULT 0.00,
                    `tracking_number` varchar(100) DEFAULT NULL,
                    `shipping_company` varchar(100) DEFAULT NULL,
                    `estimated_delivery` date DEFAULT NULL,
                    `sync_status` enum('pending','synced','error') DEFAULT 'pending',
                    `sync_error` text,
                    `last_sync` datetime DEFAULT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`n11_order_id`),
                    KEY `opencart_order_id` (`opencart_order_id`),
                    KEY `order_status` (`order_status`),
                    KEY `sync_status` (`sync_status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // N11 Advanced Competitor Analysis
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_competitor_analysis` (
                    `analysis_id` int(11) NOT NULL AUTO_INCREMENT,
                    `product_id` int(11) NOT NULL,
                    `competitor_seller_id` varchar(50) NOT NULL,
                    `competitor_product_id` varchar(50) NOT NULL,
                    `competitor_name` varchar(255) NOT NULL,
                    `competitor_price` decimal(10,2) NOT NULL,
                    `competitor_rating` decimal(3,2) DEFAULT 0.00,
                    `competitor_review_count` int(11) DEFAULT 0,
                    `similarity_score` decimal(5,2) DEFAULT 0.00,
                    `price_difference` decimal(10,2) DEFAULT 0.00,
                    `price_advantage` enum('cheaper','same','expensive') DEFAULT 'same',
                    `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`analysis_id`),
                    KEY `product_id` (`product_id`),
                    KEY `last_updated` (`last_updated`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // N11 Advanced SEO Optimization
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_seo_optimization` (
                    `seo_id` int(11) NOT NULL AUTO_INCREMENT,
                    `product_id` int(11) NOT NULL,
                    `original_title` varchar(500) NOT NULL,
                    `optimized_title` varchar(500) NOT NULL,
                    `original_description` text,
                    `optimized_description` text,
                    `keywords` text,
                    `optimization_score` decimal(5,2) DEFAULT 0.00,
                    `ranking_improvement` int(11) DEFAULT 0,
                    `click_improvement` decimal(5,2) DEFAULT 0.00,
                    `conversion_improvement` decimal(5,2) DEFAULT 0.00,
                    `optimization_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`seo_id`),
                    KEY `product_id` (`product_id`),
                    KEY `optimization_score` (`optimization_score`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            $this->logger->write('N11 Advanced module tables created successfully');
            
        } catch (Exception $e) {
            $this->logger->write('Error creating N11 Advanced tables: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Uninstall N11 Advanced module tables
     * 
     * @return void
     */
    public function uninstall() {
        try {
            $tables = array(
                'n11_advanced_products',
                'n11_advanced_analytics', 
                'n11_advanced_campaigns',
                'n11_advanced_orders',
                'n11_competitor_analysis',
                'n11_seo_optimization'
            );
            
            foreach ($tables as $table) {
                $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . $table . "`");
            }
            
            $this->logger->write('N11 Advanced module tables removed successfully');
            
        } catch (Exception $e) {
            $this->logger->write('Error removing N11 Advanced tables: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    public function getDashboardStats() {
        try {
            $stats = array();
            
            // Total listed products
            $query = $this->db->query("
                SELECT COUNT(*) as total_listed 
                FROM `" . DB_PREFIX . "n11_advanced_products` 
                WHERE listing_status = 'listed'
            ");
            $stats['total_listed'] = (int)$query->row['total_listed'];
            
            // Pending listings
            $query = $this->db->query("
                SELECT COUNT(*) as pending_listings 
                FROM `" . DB_PREFIX . "n11_advanced_products` 
                WHERE listing_status = 'pending'
            ");
            $stats['pending_listings'] = (int)$query->row['pending_listings'];
            
            // Active campaigns
            $query = $this->db->query("
                SELECT COUNT(*) as active_campaigns 
                FROM `" . DB_PREFIX . "n11_advanced_campaigns` 
                WHERE status = 'active' 
                AND start_date <= NOW() 
                AND end_date >= NOW()
            ");
            $stats['active_campaigns'] = (int)$query->row['active_campaigns'];
            
            // Today's orders
            $query = $this->db->query("
                SELECT COUNT(*) as todays_orders 
                FROM `" . DB_PREFIX . "n11_advanced_orders` 
                WHERE DATE(created_at) = CURDATE()
            ");
            $stats['todays_orders'] = (int)$query->row['todays_orders'];
            
            // Average SEO score
            $query = $this->db->query("
                SELECT AVG(seo_score) as avg_seo_score 
                FROM `" . DB_PREFIX . "n11_advanced_products` 
                WHERE listing_status = 'listed'
            ");
            $stats['avg_seo_score'] = round((float)$query->row['avg_seo_score'], 2);
            
            // Average performance score
            $query = $this->db->query("
                SELECT AVG(performance_score) as avg_performance 
                FROM `" . DB_PREFIX . "n11_advanced_products` 
                WHERE listing_status = 'listed'
            ");
            $stats['avg_performance'] = round((float)$query->row['avg_performance'], 2);
            
            // This week's revenue
            $query = $this->db->query("
                SELECT SUM(total_amount) as weekly_revenue 
                FROM `" . DB_PREFIX . "n11_advanced_orders` 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            $stats['weekly_revenue'] = round((float)$query->row['weekly_revenue'], 2);
            
            // Competitor analysis count
            $query = $this->db->query("
                SELECT COUNT(DISTINCT product_id) as analyzed_products 
                FROM `" . DB_PREFIX . "n11_competitor_analysis` 
                WHERE last_updated >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $stats['analyzed_products'] = (int)$query->row['analyzed_products'];
            
            return $stats;
            
        } catch (Exception $e) {
            $this->logger->write('Error getting dashboard stats: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Get recent activities
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentActivities($limit = 10) {
        try {
            $activities = array();
            
            // Recent listings
            $query = $this->db->query("
                SELECT 
                    'listing' as activity_type,
                    CONCAT('Product ', p.name, ' listed on N11') as description,
                    np.created_at as activity_time
                FROM `" . DB_PREFIX . "n11_advanced_products` np
                LEFT JOIN `" . DB_PREFIX . "product_description` p ON np.product_id = p.product_id
                WHERE np.listing_status = 'listed'
                AND p.language_id = 1
                ORDER BY np.created_at DESC
                LIMIT " . (int)($limit / 2) . "
            ");
            
            foreach ($query->rows as $row) {
                $activities[] = array(
                    'type' => $row['activity_type'],
                    'description' => $row['description'],
                    'time' => $row['activity_time'],
                    'icon' => 'fa-plus-circle',
                    'color' => 'success'
                );
            }
            
            // Recent orders
            $query = $this->db->query("
                SELECT 
                    'order' as activity_type,
                    CONCAT('Order #', n11_order_number, ' received') as description,
                    created_at as activity_time
                FROM `" . DB_PREFIX . "n11_advanced_orders`
                ORDER BY created_at DESC
                LIMIT " . (int)($limit / 2) . "
            ");
            
            foreach ($query->rows as $row) {
                $activities[] = array(
                    'type' => $row['activity_type'],
                    'description' => $row['description'],
                    'time' => $row['activity_time'],
                    'icon' => 'fa-shopping-cart',
                    'color' => 'info'
                );
            }
            
            // Sort by time
            usort($activities, function($a, $b) {
                return strtotime($b['time']) - strtotime($a['time']);
            });
            
            return array_slice($activities, 0, $limit);
            
        } catch (Exception $e) {
            $this->logger->write('Error getting recent activities: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Get product for N11 operations
     * 
     * @param int $product_id
     * @return array|null
     */
    public function getProduct($product_id) {
        try {
            $query = $this->db->query("
                SELECT 
                    p.*,
                    pd.name,
                    pd.description,
                    pd.meta_title,
                    pd.meta_description,
                    pd.meta_keyword,
                    cd.name as category_name,
                    m.name as manufacturer_name,
                    np.n11_product_id,
                    np.listing_status,
                    np.seo_score,
                    np.performance_score
                FROM `" . DB_PREFIX . "product` p
                LEFT JOIN `" . DB_PREFIX . "product_description` pd ON p.product_id = pd.product_id
                LEFT JOIN `" . DB_PREFIX . "category_description` cd ON p.master_id = cd.category_id
                LEFT JOIN `" . DB_PREFIX . "manufacturer` m ON p.manufacturer_id = m.manufacturer_id
                LEFT JOIN `" . DB_PREFIX . "n11_advanced_products` np ON p.product_id = np.product_id
                WHERE p.product_id = '" . (int)$product_id . "'
                AND pd.language_id = 1
                AND cd.language_id = 1
            ");
            
            if ($query->num_rows) {
                return $query->row;
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->logger->write('Error getting product: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update product title for SEO
     * 
     * @param int $product_id
     * @param string $optimized_title
     * @return bool
     */
    public function updateProductTitle($product_id, $optimized_title) {
        try {
            // Get original title
            $query = $this->db->query("
                SELECT name as original_title
                FROM `" . DB_PREFIX . "product_description` 
                WHERE product_id = '" . (int)$product_id . "' 
                AND language_id = 1
            ");
            
            if (!$query->num_rows) {
                return false;
            }
            
            $original_title = $query->row['original_title'];
            
            // Update product title
            $this->db->query("
                UPDATE `" . DB_PREFIX . "product_description` 
                SET name = '" . $this->db->escape($optimized_title) . "'
                WHERE product_id = '" . (int)$product_id . "' 
                AND language_id = 1
            ");
            
            // Log optimization
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "n11_seo_optimization` 
                (product_id, original_title, optimized_title, optimization_date)
                VALUES (
                    '" . (int)$product_id . "',
                    '" . $this->db->escape($original_title) . "',
                    '" . $this->db->escape($optimized_title) . "',
                    NOW()
                )
            ");
            
            return true;
            
        } catch (Exception $e) {
            $this->logger->write('Error updating product title: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get listing success rate
     * 
     * @return float
     */
    public function getListingSuccessRate() {
        try {
            $query = $this->db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN listing_status = 'listed' THEN 1 ELSE 0 END) as listed
                FROM `" . DB_PREFIX . "n11_advanced_products`
            ");
            
            if ($query->row['total'] > 0) {
                return round(($query->row['listed'] / $query->row['total']) * 100, 2);
            }
            
            return 0.00;
            
        } catch (Exception $e) {
            $this->logger->write('Error getting listing success rate: ' . $e->getMessage());
            return 0.00;
        }
    }
    
    /**
     * Get average listing time
     * 
     * @return string
     */
    public function getAverageListingTime() {
        try {
            $query = $this->db->query("
                SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_minutes
                FROM `" . DB_PREFIX . "n11_advanced_products`
                WHERE listing_status = 'listed'
            ");
            
            $minutes = (int)$query->row['avg_minutes'];
            
            if ($minutes < 60) {
                return $minutes . ' dakika';
            } else {
                $hours = floor($minutes / 60);
                $remaining_minutes = $minutes % 60;
                return $hours . ' saat ' . $remaining_minutes . ' dakika';
            }
            
        } catch (Exception $e) {
            $this->logger->write('Error getting average listing time: ' . $e->getMessage());
            return '0 dakika';
        }
    }
    
    /**
     * Get order sync rate
     * 
     * @return float
     */
    public function getOrderSyncRate() {
        try {
            $query = $this->db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN sync_status = 'synced' THEN 1 ELSE 0 END) as synced
                FROM `" . DB_PREFIX . "n11_advanced_orders`
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            
            if ($query->row['total'] > 0) {
                return round(($query->row['synced'] / $query->row['total']) * 100, 2);
            }
            
            return 100.00;
            
        } catch (Exception $e) {
            $this->logger->write('Error getting order sync rate: ' . $e->getMessage());
            return 0.00;
        }
    }
    
    /**
     * Get price competitiveness score
     * 
     * @return float
     */
    public function getPriceCompetitiveness() {
        try {
            $query = $this->db->query("
                SELECT AVG(
                    CASE 
                        WHEN price_position = 'lowest' THEN 100
                        WHEN price_position = 'competitive' THEN 80
                        WHEN price_position = 'premium' THEN 60
                        WHEN price_position = 'overpriced' THEN 30
                        ELSE 50
                    END
                ) as competitiveness
                FROM `" . DB_PREFIX . "n11_advanced_products`
                WHERE listing_status = 'listed'
            ");
            
            return round((float)$query->row['competitiveness'], 2);
            
        } catch (Exception $e) {
            $this->logger->write('Error getting price competitiveness: ' . $e->getMessage());
            return 50.00;
        }
    }
    
    /**
     * Add product to N11 advanced tracking
     * 
     * @param int $product_id
     * @param array $n11_data
     * @return bool
     */
    public function addProductTracking($product_id, $n11_data) {
        try {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "n11_advanced_products` 
                (product_id, n11_product_id, n11_sku, listing_status, created_at)
                VALUES (
                    '" . (int)$product_id . "',
                    '" . $this->db->escape($n11_data['product_id']) . "',
                    '" . $this->db->escape($n11_data['sku']) . "',
                    '" . $this->db->escape($n11_data['status']) . "',
                    NOW()
                )
                ON DUPLICATE KEY UPDATE
                    n11_product_id = VALUES(n11_product_id),
                    n11_sku = VALUES(n11_sku),
                    listing_status = VALUES(listing_status),
                    updated_at = NOW()
            ");
            
            return true;
            
        } catch (Exception $e) {
            $this->logger->write('Error adding product tracking: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update competitor analysis
     * 
     * @param int $product_id
     * @param array $competitors
     * @return bool
     */
    public function updateCompetitorAnalysis($product_id, $competitors) {
        try {
            // Clear old analysis
            $this->db->query("
                DELETE FROM `" . DB_PREFIX . "n11_competitor_analysis` 
                WHERE product_id = '" . (int)$product_id . "'
            ");
            
            // Insert new analysis
            foreach ($competitors as $competitor) {
                $this->db->query("
                    INSERT INTO `" . DB_PREFIX . "n11_competitor_analysis` 
                    (product_id, competitor_seller_id, competitor_product_id, competitor_name, 
                     competitor_price, competitor_rating, competitor_review_count, 
                     similarity_score, price_difference, price_advantage, last_updated)
                    VALUES (
                        '" . (int)$product_id . "',
                        '" . $this->db->escape($competitor['seller_id']) . "',
                        '" . $this->db->escape($competitor['product_id']) . "',
                        '" . $this->db->escape($competitor['name']) . "',
                        '" . (float)$competitor['price'] . "',
                        '" . (float)$competitor['rating'] . "',
                        '" . (int)$competitor['review_count'] . "',
                        '" . (float)$competitor['similarity_score'] . "',
                        '" . (float)$competitor['price_difference'] . "',
                        '" . $this->db->escape($competitor['price_advantage']) . "',
                        NOW()
                    )
                ");
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->logger->write('Error updating competitor analysis: ' . $e->getMessage());
            return false;
        }
    }
}
?>