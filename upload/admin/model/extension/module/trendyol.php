<?php
/**
 * trendyol.php (Model)
 *
 * Amaç: Trendyol modülünün veritabanı işlemlerini yöneten model dosyası.
 * Bu dosya sipariş yönetimi, ürün eşleştirme ve raporlama işlemleri için veritabanı fonksiyonlarını içerir.
 */
class ModelExtensionModuleTrendyol extends Model {
    /**
     * Trendyol siparişini veritabanına kaydet
     * 
     * @param array $data Sipariş verileri
     * @return int Eklenen siparişin ID'si
     */
    public function addOrder($data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_order` SET 
            order_id = '" . $this->db->escape($data['order_id']) . "', 
            order_number = '" . $this->db->escape($data['order_number']) . "', 
            status = '" . $this->db->escape($data['status']) . "', 
            total_price = '" . (float)$data['total_price'] . "', 
            shipping_cost = '" . (float)$data['shipping_cost'] . "', 
            customer_name = '" . $this->db->escape($data['customer_name']) . "', 
            customer_email = '" . $this->db->escape($data['customer_email']) . "', 
            customer_phone = '" . $this->db->escape($data['customer_phone']) . "', 
            shipping_address = '" . $this->db->escape($data['shipping_address']) . "', 
            shipping_city = '" . $this->db->escape($data['shipping_city']) . "', 
            shipping_district = '" . $this->db->escape($data['shipping_district']) . "', 
            date_added = '" . $this->db->escape($data['date_added']) . "', 
            date_modified = NOW()");
        
        $order_id = $this->db->getLastId();
        
        // Sipariş ürünleri
        if (isset($data['products']) && is_array($data['products'])) {
            foreach ($data['products'] as $product) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_order_product` SET 
                    trendyol_order_id = '" . (int)$order_id . "', 
                    name = '" . $this->db->escape($product['name']) . "', 
                    barcode = '" . $this->db->escape($product['barcode']) . "', 
                    quantity = '" . (int)$product['quantity'] . "', 
                    price = '" . (float)$product['price'] . "', 
                    total = '" . (float)$product['total'] . "'");
            }
        }
        
        return $order_id;
    }
    
    /**
     * Trendyol siparişini güncelle
     * 
     * @param int $order_id Sipariş ID
     * @param array $data Güncellenecek veriler
     * @return bool
     */
    public function updateOrder($order_id, $data) {
        $sql = "UPDATE `" . DB_PREFIX . "trendyol_order` SET ";
        
        $updates = array();
        
        if (isset($data['status'])) {
            $updates[] = "status = '" . $this->db->escape($data['status']) . "'";
        }
        
        if (isset($data['total_price'])) {
            $updates[] = "total_price = '" . (float)$data['total_price'] . "'";
        }
        
        if (isset($data['shipping_cost'])) {
            $updates[] = "shipping_cost = '" . (float)$data['shipping_cost'] . "'";
        }
        
        // Diğer alanları da ekleyebilirsiniz
        
        $updates[] = "date_modified = NOW()";
        
        if (empty($updates)) {
            return false;
        }
        
        $sql .= implode(', ', $updates);
        $sql .= " WHERE order_id = '" . $this->db->escape($order_id) . "'";
        
        $this->db->query($sql);
        
        return true;
    }
    
    /**
     * Trendyol siparişi için OpenCart siparişi oluşturulduğunda ilişki kur
     * 
     * @param string $trendyol_order_id Trendyol sipariş ID
     * @param int $opencart_order_id OpenCart sipariş ID
     * @return bool
     */
    public function addOrderRelation($trendyol_order_id, $opencart_order_id) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_order_relation` SET 
            trendyol_order_id = '" . $this->db->escape($trendyol_order_id) . "', 
            opencart_order_id = '" . (int)$opencart_order_id . "', 
            date_added = NOW()");
        
        return true;
    }
    
    /**
     * Trendyol siparişinin OpenCart'a dönüştürülüp dönüştürülmediğini kontrol et
     * 
     * @param string $trendyol_order_id Trendyol sipariş ID
     * @return array|bool OpenCart sipariş bilgileri veya false
     */
    public function getOpenCartOrderId($trendyol_order_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_order_relation` WHERE trendyol_order_id = '" . $this->db->escape($trendyol_order_id) . "'");
        
        if ($query->num_rows) {
            return $query->row;
        } else {
            return false;
        }
    }
    
    /**
     * Trendyol sipariş detayını getir
     * 
     * @param string $order_id Sipariş ID
     * @return array|bool Sipariş bilgileri veya false
     */
    public function getOrder($order_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_order` WHERE order_id = '" . $this->db->escape($order_id) . "'");
        
        if ($query->num_rows) {
            return $query->row;
        } else {
            return false;
        }
    }
    
    /**
     * Trendyol sipariş ürünlerini getir
     * 
     * @param string $order_id Sipariş ID
     * @return array Sipariş ürünleri
     */
    public function getOrderProducts($order_id) {
        $query = $this->db->query("SELECT op.* FROM `" . DB_PREFIX . "trendyol_order_product` op 
            LEFT JOIN `" . DB_PREFIX . "trendyol_order` o ON (op.trendyol_order_id = o.trendyol_order_id) 
            WHERE o.order_id = '" . $this->db->escape($order_id) . "'");
        
        return $query->rows;
    }
    
    /**
     * Trendyol siparişlerini getir (filtre desteği ile)
     * 
     * @param array $data Filtre verileri
     * @return array Siparişler
     */
    public function getOrders($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "trendyol_order` WHERE 1";
        
        if (!empty($data['filter_order_id'])) {
            $sql .= " AND order_id LIKE '%" . $this->db->escape($data['filter_order_id']) . "%'";
        }
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (isset($data['filter_convert_status']) && $data['filter_convert_status'] !== '') {
            if ($data['filter_convert_status']) {
                $sql .= " AND order_id IN (SELECT trendyol_order_id FROM `" . DB_PREFIX . "trendyol_order_relation`)";
            } else {
                $sql .= " AND order_id NOT IN (SELECT trendyol_order_id FROM `" . DB_PREFIX . "trendyol_order_relation`)";
            }
        }
        
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        $sort_data = array(
            'order_id',
            'status',
            'date_added'
        );
        
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY date_added";
        }
        
        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " DESC";
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
    }
    
    /**
     * Trendyol siparişlerinin toplam sayısını getir (filtre desteği ile)
     * 
     * @param array $data Filtre verileri
     * @return int Toplam sipariş sayısı
     */
    public function getTotalOrders($data = array()) {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "trendyol_order` WHERE 1";
        
        if (!empty($data['filter_order_id'])) {
            $sql .= " AND order_id LIKE '%" . $this->db->escape($data['filter_order_id']) . "%'";
        }
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (isset($data['filter_convert_status']) && $data['filter_convert_status'] !== '') {
            if ($data['filter_convert_status']) {
                $sql .= " AND order_id IN (SELECT trendyol_order_id FROM `" . DB_PREFIX . "trendyol_order_relation`)";
            } else {
                $sql .= " AND order_id NOT IN (SELECT trendyol_order_id FROM `" . DB_PREFIX . "trendyol_order_relation`)";
            }
        }
        
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        $query = $this->db->query($sql);
        
        return $query->row['total'];
    }
    
    /**
     * Trendyol modülü için kurulum işlemini gerçekleştir
     */
    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_products` (
                `trendyol_product_id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `trendyol_id` varchar(255) NOT NULL,
                `barcode` varchar(255) DEFAULT NULL,
                `title` varchar(500) NOT NULL,
                `brand_id` int(11) DEFAULT NULL,
                `category_id` int(11) DEFAULT NULL,
                `list_price` decimal(15,4) DEFAULT NULL,
                `sale_price` decimal(15,4) DEFAULT NULL,
                `quantity` int(11) DEFAULT 0,
                `stock_code` varchar(255) DEFAULT NULL,
                `description` text,
                `images` text,
                `attributes` text,
                `variants` text,
                `status` enum('active','passive','draft') DEFAULT 'active',
                `approval_status` varchar(50) DEFAULT 'pending',
                `rejection_reason` text,
                `commission_rate` decimal(5,2) DEFAULT NULL,
                `cargo_company_id` int(11) DEFAULT NULL,
                `delivery_duration` int(11) DEFAULT NULL,
                `stock_unit_type` varchar(50) DEFAULT 'piece',
                `vat_rate` decimal(5,2) DEFAULT NULL,
                `marketplace_commission` decimal(15,4) DEFAULT NULL,
                `profit_margin` decimal(5,2) DEFAULT NULL,
                `competitor_analysis` text,
                `last_sync` datetime DEFAULT NULL,
                `sync_status` varchar(50) DEFAULT 'pending',
                `error_message` text,
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`trendyol_product_id`),
                KEY `product_id` (`product_id`),
                KEY `trendyol_id` (`trendyol_id`),
                KEY `status` (`status`),
                KEY `approval_status` (`approval_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_orders` (
                `trendyol_order_id` int(11) NOT NULL AUTO_INCREMENT,
                `order_id` int(11) DEFAULT NULL,
                `trendyol_id` varchar(255) NOT NULL,
                `order_number` varchar(255) NOT NULL,
                `customer_id` int(11) DEFAULT NULL,
                `customer_first_name` varchar(255) DEFAULT NULL,
                `customer_last_name` varchar(255) DEFAULT NULL,
                `customer_email` varchar(255) DEFAULT NULL,
                `customer_phone` varchar(255) DEFAULT NULL,
                `order_date` datetime DEFAULT NULL,
                `status` varchar(50) DEFAULT 'Created',
                `shipment_status` varchar(50) DEFAULT 'waiting',
                `invoice_address` text,
                `delivery_address` text,
                `total_amount` decimal(15,4) DEFAULT NULL,
                `total_discount` decimal(15,4) DEFAULT NULL,
                `cargo_tracking_number` varchar(255) DEFAULT NULL,
                `cargo_tracking_link` varchar(500) DEFAULT NULL,
                `cargo_sender_number` varchar(255) DEFAULT NULL,
                `cargo_provider_name` varchar(255) DEFAULT NULL,
                `estimated_delivery` datetime DEFAULT NULL,
                `fast_delivery` tinyint(1) DEFAULT 0,
                `commercial_invoice_number` varchar(255) DEFAULT NULL,
                `package_number` varchar(255) DEFAULT NULL,
                `currency_code` varchar(3) DEFAULT 'TRY',
                `gross_amount` decimal(15,4) DEFAULT NULL,
                `total_tyc_discount` decimal(15,4) DEFAULT NULL,
                `order_items` text,
                `payment_type` varchar(50) DEFAULT NULL,
                `installment` int(11) DEFAULT 1,
                `tax_number` varchar(255) DEFAULT NULL,
                `invoice_requested` tinyint(1) DEFAULT 0,
                `notes` text,
                `tags` varchar(500) DEFAULT NULL,
                `priority_level` int(11) DEFAULT 0,
                `processing_time` int(11) DEFAULT NULL,
                `return_request` tinyint(1) DEFAULT 0,
                `return_reason` text,
                `last_sync` datetime DEFAULT NULL,
                `sync_status` varchar(50) DEFAULT 'pending',
                `error_message` text,
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`trendyol_order_id`),
                KEY `order_id` (`order_id`),
                KEY `trendyol_id` (`trendyol_id`),
                KEY `order_number` (`order_number`),
                KEY `status` (`status`),
                KEY `order_date` (`order_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_categories` (
                `trendyol_category_id` int(11) NOT NULL AUTO_INCREMENT,
                `category_id` int(11) NOT NULL,
                `trendyol_id` int(11) NOT NULL,
                `name` varchar(255) NOT NULL,
                `parent_id` int(11) DEFAULT NULL,
                `level` int(11) DEFAULT 0,
                `full_path` varchar(1000) DEFAULT NULL,
                `commission_rate` decimal(5,2) DEFAULT NULL,
                `has_size_chart` tinyint(1) DEFAULT 0,
                `size_chart_template` text,
                `required_attributes` text,
                `variable_attributes` text,
                `category_attributes` text,
                `allowed_brands` text,
                `status` enum('active','passive') DEFAULT 'active',
                `last_sync` datetime DEFAULT NULL,
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`trendyol_category_id`),
                KEY `category_id` (`category_id`),
                KEY `trendyol_id` (`trendyol_id`),
                KEY `parent_id` (`parent_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_brands` (
                `trendyol_brand_id` int(11) NOT NULL AUTO_INCREMENT,
                `trendyol_id` int(11) NOT NULL,
                `name` varchar(255) NOT NULL,
                `approved` tinyint(1) DEFAULT 0,
                `logo_url` varchar(500) DEFAULT NULL,
                `description` text,
                `website_url` varchar(500) DEFAULT NULL,
                `category_ids` text,
                `status` enum('active','passive') DEFAULT 'active',
                `last_sync` datetime DEFAULT NULL,
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`trendyol_brand_id`),
                KEY `trendyol_id` (`trendyol_id`),
                KEY `name` (`name`),
                KEY `approved` (`approved`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_analytics` (
                `analytics_id` int(11) NOT NULL AUTO_INCREMENT,
                `date` date NOT NULL,
                `metric_type` varchar(100) NOT NULL,
                `metric_name` varchar(255) NOT NULL,
                `metric_value` decimal(15,4) DEFAULT NULL,
                `metric_data` text,
                `product_id` int(11) DEFAULT NULL,
                `category_id` int(11) DEFAULT NULL,
                `brand_id` int(11) DEFAULT NULL,
                `period_type` enum('daily','weekly','monthly','yearly') DEFAULT 'daily',
                `comparison_data` text,
                `insights` text,
                `recommendations` text,
                `ai_processed` tinyint(1) DEFAULT 0,
                `confidence_score` decimal(5,4) DEFAULT NULL,
                `data_source` varchar(100) DEFAULT 'trendyol_api',
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`analytics_id`),
                KEY `date` (`date`),
                KEY `metric_type` (`metric_type`),
                KEY `product_id` (`product_id`),
                KEY `period_type` (`period_type`),
                KEY `ai_processed` (`ai_processed`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_campaigns` (
                `campaign_id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `campaign_type` varchar(100) NOT NULL,
                `status` enum('active','passive','completed','cancelled') DEFAULT 'active',
                `start_date` datetime NOT NULL,
                `end_date` datetime NOT NULL,
                `budget` decimal(15,4) DEFAULT NULL,
                `spent_amount` decimal(15,4) DEFAULT 0,
                `target_products` text,
                `campaign_settings` text,
                `performance_data` text,
                `conversion_rate` decimal(5,4) DEFAULT NULL,
                `roi` decimal(5,4) DEFAULT NULL,
                `impressions` int(11) DEFAULT 0,
                `clicks` int(11) DEFAULT 0,
                `orders` int(11) DEFAULT 0,
                `revenue` decimal(15,4) DEFAULT 0,
                `automation_rules` text,
                `ai_optimization` tinyint(1) DEFAULT 0,
                `optimization_score` decimal(5,2) DEFAULT NULL,
                `last_optimized` datetime DEFAULT NULL,
                `created_by` int(11) DEFAULT NULL,
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`campaign_id`),
                KEY `campaign_type` (`campaign_type`),
                KEY `status` (`status`),
                KEY `start_date` (`start_date`),
                KEY `end_date` (`end_date`),
                KEY `ai_optimization` (`ai_optimization`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_inventory_tracking` (
                `tracking_id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `trendyol_product_id` varchar(255) DEFAULT NULL,
                `stock_quantity` int(11) NOT NULL,
                `reserved_quantity` int(11) DEFAULT 0,
                `available_quantity` int(11) GENERATED ALWAYS AS (`stock_quantity` - `reserved_quantity`) STORED,
                `minimum_stock_level` int(11) DEFAULT 5,
                `maximum_stock_level` int(11) DEFAULT 1000,
                `reorder_point` int(11) DEFAULT 10,
                `reorder_quantity` int(11) DEFAULT 50,
                `supplier_lead_time` int(11) DEFAULT 7,
                `average_daily_sales` decimal(10,2) DEFAULT NULL,
                `forecasted_demand` int(11) DEFAULT NULL,
                `stock_value` decimal(15,4) DEFAULT NULL,
                `last_order_date` datetime DEFAULT NULL,
                `next_reorder_date` datetime DEFAULT NULL,
                `stock_alerts` text,
                `inventory_status` enum('in_stock','low_stock','out_of_stock','excess_stock') DEFAULT 'in_stock',
                `ai_recommendations` text,
                `optimization_score` decimal(5,2) DEFAULT NULL,
                `seasonal_factor` decimal(5,4) DEFAULT 1.0000,
                `trend_factor` decimal(5,4) DEFAULT 1.0000,
                `last_sync` datetime DEFAULT NULL,
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`tracking_id`),
                KEY `product_id` (`product_id`),
                KEY `trendyol_product_id` (`trendyol_product_id`),
                KEY `inventory_status` (`inventory_status`),
                KEY `reorder_point` (`reorder_point`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Create settings table for Trendyol configuration
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_settings` (
                `setting_id` int(11) NOT NULL AUTO_INCREMENT,
                `store_id` int(11) NOT NULL DEFAULT 0,
                `setting_group` varchar(100) NOT NULL,
                `setting_key` varchar(255) NOT NULL,
                `setting_value` text,
                `encrypted` tinyint(1) DEFAULT 0,
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`setting_id`),
                KEY `store_id` (`store_id`),
                KEY `setting_group` (`setting_group`),
                KEY `setting_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    /**
     * Trendyol modülü için kaldırma işlemini gerçekleştir
     * Sadece tabloları kaldırır, ayarları kaldırmaz (güvenlik amaçlı)
     */
    public function uninstall() {
        // Optional: Keep tables for data retention
        // Uncomment to completely remove all data
        /*
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_products`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_orders`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_categories`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_brands`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_analytics`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_campaigns`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_inventory_tracking`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_settings`");
        */
    }
    
    /**
     * Log girdisi ekle
     * 
     * @param string $user Kullanıcı bilgisi
     * @param string $action İşlem
     * @param string $message Mesaj
     * @return bool
     */
    public function addLog($user, $action, $message) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_log` SET 
            user = '" . $this->db->escape($user) . "', 
            action = '" . $this->db->escape($action) . "', 
            message = '" . $this->db->escape($message) . "', 
            date_added = NOW()");
        
        return true;
    }
    
    /**
     * Logları getir (filtre desteği ile)
     * 
     * @param array $data Filtre verileri
     * @return array Loglar
     */
    public function getLogs($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "trendyol_log` WHERE 1";
        
        if (!empty($data['filter_user'])) {
            $sql .= " AND user LIKE '%" . $this->db->escape($data['filter_user']) . "%'";
        }
        
        if (!empty($data['filter_action'])) {
            $sql .= " AND action LIKE '%" . $this->db->escape($data['filter_action']) . "%'";
        }
        
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        $sql .= " ORDER BY date_added DESC";
        
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
     * Toplam log sayısını getir (filtre desteği ile)
     * 
     * @param array $data Filtre verileri
     * @return int Toplam log sayısı
     */
    public function getTotalLogs($data = array()) {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "trendyol_log` WHERE 1";
        
        if (!empty($data['filter_user'])) {
            $sql .= " AND user LIKE '%" . $this->db->escape($data['filter_user']) . "%'";
        }
        
        if (!empty($data['filter_action'])) {
            $sql .= " AND action LIKE '%" . $this->db->escape($data['filter_action']) . "%'";
        }
        
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        $query = $this->db->query($sql);
        
        return $query->row['total'];
    }
    
    /**
     * Log kayıtlarını temizle (belirli tarihten önceki)
     * 
     * @param string $date_before Tarih (YYYY-MM-DD formatında)
     * @return bool
     */
    public function clearLogs($date_before) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "trendyol_log` WHERE DATE(date_added) < '" . $this->db->escape($date_before) . "'");
        
        return true;
    }
    
    /**
     * Trendyol istatistiklerini getir (Dashboard için)
     * 
     * @return array İstatistik verileri
     */
    public function getStats() {
        $stats = array();
        
        try {
            // Toplam sipariş sayısı
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "trendyol_order`");
            $stats['total_orders'] = $query->row['total'];
            
            // Bu ayki sipariş sayısı
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "trendyol_order` WHERE MONTH(date_added) = MONTH(NOW()) AND YEAR(date_added) = YEAR(NOW())");
            $stats['monthly_orders'] = $query->row['total'];
            
            // Toplam satış tutarı
            $query = $this->db->query("SELECT SUM(total_price) as total FROM `" . DB_PREFIX . "trendyol_order`");
            $stats['total_sales'] = $query->row['total'] ? $query->row['total'] : 0;
            
            // Bu ayki satış tutarı
            $query = $this->db->query("SELECT SUM(total_price) as total FROM `" . DB_PREFIX . "trendyol_order` WHERE MONTH(date_added) = MONTH(NOW()) AND YEAR(date_added) = YEAR(NOW())");
            $stats['monthly_sales'] = $query->row['total'] ? $query->row['total'] : 0;
            
            // Son sipariş tarihi
            $query = $this->db->query("SELECT MAX(date_added) as last_order FROM `" . DB_PREFIX . "trendyol_order`");
            $stats['last_order_date'] = $query->row['last_order'] ? date('d.m.Y H:i', strtotime($query->row['last_order'])) : 'Henüz sipariş yok';
            
            // API durumu (ayarlara göre)
            $api_key = $this->config->get('module_trendyol_api_key');
            $api_secret = $this->config->get('module_trendyol_api_secret');
            $supplier_id = $this->config->get('module_trendyol_supplier_id');
            
            if (!empty($api_key) && !empty($api_secret) && !empty($supplier_id)) {
                $stats['api_status'] = 'configured';
                $stats['api_status_text'] = 'API Yapılandırılmış';
            } else {
                $stats['api_status'] = 'not_configured';
                $stats['api_status_text'] = 'API Yapılandırılmamış';
            }
            
            // Bekleyen siparişler
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "trendyol_order` WHERE status IN ('Created', 'Approved')");
            $stats['pending_orders'] = $query->row['total'];
            
            // Kargo beklenen siparişler
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "trendyol_order` WHERE status = 'Picking'");
            $stats['shipping_orders'] = $query->row['total'];
            
        } catch (Exception $e) {
            // Hata durumunda varsayılan değerler
            $stats = array(
                'total_orders' => 0,
                'monthly_orders' => 0,
                'total_sales' => 0,
                'monthly_sales' => 0,
                'last_order_date' => 'Veri yok',
                'api_status' => 'error',
                'api_status_text' => 'Hata: ' . $e->getMessage(),
                'pending_orders' => 0,
                'shipping_orders' => 0
            );
        }
          return $stats;
    }

    /**
     * Webhook Management Methods
     */

    /**
     * Check if webhook is enabled
     */
    public function isWebhookEnabled() {
        try {
            $query = $this->db->query("SELECT value FROM `" . DB_PREFIX . "setting` WHERE `key` = 'trendyol_webhook_enabled'");
            return $query->num_rows > 0 ? (bool)$query->row['value'] : false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get webhook events count for today
     */
    public function getWebhookEventsCount() {
        try {
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "trendyol_webhook_log` WHERE DATE(timestamp) = CURDATE()");
            return $query->row['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get last webhook event
     */
    public function getLastWebhookEvent() {
        try {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_webhook_log` ORDER BY timestamp DESC LIMIT 1");
            if ($query->num_rows > 0) {
                return $query->row['timestamp'] . ' - ' . $query->row['event_type'];
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get webhook configuration
     */
    public function getWebhookConfiguration() {
        try {
            $query = $this->db->query("SELECT `key`, value FROM `" . DB_PREFIX . "setting` WHERE `key` LIKE 'trendyol_webhook_%'");
            
            $config = [];
            foreach ($query->rows as $row) {
                $key = str_replace('trendyol_webhook_', '', $row['key']);
                $config[$key] = $row['value'];
            }
            
            return $config;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Toggle webhook for specific event type
     */
    public function toggleWebhook($eventType, $enabled) {
        try {
            $key = 'trendyol_webhook_' . $eventType;
            
            // Check if setting exists
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "setting` WHERE `key` = '" . $this->db->escape($key) . "'");
            
            if ($query->row['total'] > 0) {
                // Update existing
                $this->db->query("UPDATE `" . DB_PREFIX . "setting` SET value = '" . ($enabled ? '1' : '0') . "' WHERE `key` = '" . $this->db->escape($key) . "'");
            } else {
                // Insert new
                $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `key` = '" . $this->db->escape($key) . "', value = '" . ($enabled ? '1' : '0') . "', store_id = 0");
            }
            
            // Log the change
            $this->logWebhookEvent('CONFIG_CHANGE', $eventType . ' webhook ' . ($enabled ? 'enabled' : 'disabled'), 'success');
            
            return true;
        } catch (Exception $e) {
            $this->logWebhookEvent('CONFIG_ERROR', 'Failed to toggle ' . $eventType . ' webhook: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Get webhook URL
     */
    public function getWebhookUrl() {
        $baseUrl = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTP_CATALOG;
        return $baseUrl . 'index.php?route=extension/module/trendyol_webhook';
    }

    /**
     * Get webhook secret key
     */
    public function getWebhookSecret() {
        try {
            $query = $this->db->query("SELECT value FROM `" . DB_PREFIX . "setting` WHERE `key` = 'trendyol_webhook_secret'");
            
            if ($query->num_rows > 0) {
                return $query->row['value'];
            } else {
                // Generate new secret
                $secret = bin2hex(random_bytes(16));
                $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `key` = 'trendyol_webhook_secret', value = '" . $this->db->escape($secret) . "', store_id = 0");
                return $secret;
            }
        } catch (Exception $e) {
            return 'error_generating_secret';
        }
    }

    /**
     * Save webhook configuration
     */
    public function saveWebhookConfiguration($events) {
        try {
            foreach ($events as $eventType => $enabled) {
                $this->toggleWebhook($eventType, $enabled);
            }
            
            $this->logWebhookEvent('CONFIG_SAVE', 'Webhook configuration saved', 'success');
            return true;
        } catch (Exception $e) {
            $this->logWebhookEvent('CONFIG_ERROR', 'Failed to save webhook configuration: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Get webhook logs
     */
    public function getWebhookLogs($limit = 20) {
        try {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_webhook_log` ORDER BY timestamp DESC LIMIT " . (int)$limit);
            return $query->rows;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Clear webhook logs
     */
    public function clearWebhookLogs() {
        try {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "trendyol_webhook_log`");
            $this->logWebhookEvent('LOGS_CLEAR', 'Webhook logs cleared', 'success');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Log webhook event
     */
    public function logWebhookEvent($eventType, $message, $status = 'success') {
        try {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_webhook_log` SET 
                event_type = '" . $this->db->escape($eventType) . "',
                message = '" . $this->db->escape($message) . "',
                status = '" . $this->db->escape($status) . "',
                timestamp = NOW()");
            return true;
        } catch (Exception $e) {
            // If logging fails, write to system log
            error_log('Trendyol webhook log failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test database connection
     */
    public function testDatabaseConnection() {
        try {
            $this->db->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get monthly sales amount
     */
    public function getMonthlySales() {
        try {
            $query = $this->db->query("SELECT SUM(total_price) as total FROM `" . DB_PREFIX . "trendyol_order` WHERE MONTH(date_added) = MONTH(NOW()) AND YEAR(date_added) = YEAR(NOW())");
            return $query->row['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get active products count
     */
    public function getActiveProductsCount() {
        try {
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "trendyol_product` WHERE status = 'active'");
            return $query->row['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get pending orders count
     */
    public function getPendingOrdersCount() {
        try {
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "trendyol_order` WHERE status IN ('Created', 'Approved')");
            return $query->row['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get seller rating (placeholder)
     */
    public function getSellerRating() {
        // This would typically come from Trendyol API
        return 4.5;
    }

    /**
     * Process orders (placeholder)
     */
    public function processOrders() {
        // This would implement order processing logic
        return rand(1, 5); // Mock processed count
    }

    /**
     * Trendyol modül ayarlarını getir
     * 
     * @return array Modül ayarları
     */
    public function getSettings() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `code` = 'module_trendyol'");
        
        $settings = array();
        
        foreach ($query->rows as $result) {
            $key = str_replace('module_trendyol_', '', $result['key']);
            $settings[$key] = $result['value'];
        }
        
        // Varsayılan değerler
        if (!isset($settings['api_key'])) $settings['api_key'] = '';
        if (!isset($settings['secret_key'])) $settings['secret_key'] = '';
        if (!isset($settings['supplier_id'])) $settings['supplier_id'] = '';
        if (!isset($settings['sandbox_mode'])) $settings['sandbox_mode'] = true;
        if (!isset($settings['status'])) $settings['status'] = 0;
        
        return $settings;
    }

    /**
     * Trendyol modül ayarlarını kaydet
     * 
     * @param array $data Ayar verileri
     * @return bool
     */
    public function saveSettings($data) {
        // Önce mevcut ayarları sil
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'module_trendyol'");
        
        // Yeni ayarları kaydet
        foreach ($data as $key => $value) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET 
                `store_id` = '0', 
                `code` = 'module_trendyol', 
                `key` = 'module_trendyol_" . $this->db->escape($key) . "', 
                `value` = '" . $this->db->escape($value) . "', 
                `serialized` = '0'");
        }
        
        return true;
    }

    /**
     * Get product by ID
     */
    public function getProduct($product_id) {
        $query = $this->db->query("
            SELECT p.*, tp.* 
            FROM `" . DB_PREFIX . "product` p 
            LEFT JOIN `" . DB_PREFIX . "trendyol_products` tp ON (p.product_id = tp.product_id) 
            WHERE p.product_id = '" . (int)$product_id . "'
        ");
        
        return $query->row;
    }

    /**
     * Get products with Trendyol integration data
     */
    public function getProducts($data = array()) {
        $sql = "
            SELECT p.product_id, p.name, p.model, p.price, p.quantity, p.status,
                   tp.trendyol_id, tp.approval_status, tp.sync_status, tp.last_sync,
                   tp.commission_rate, tp.profit_margin
            FROM `" . DB_PREFIX . "product` p 
            LEFT JOIN `" . DB_PREFIX . "trendyol_products` tp ON (p.product_id = tp.product_id)
        ";

        $conditions = array();
        
        if (!empty($data['filter_name'])) {
            $conditions[] = "p.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }
        
        if (!empty($data['filter_status'])) {
            $conditions[] = "tp.status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_approval_status'])) {
            $conditions[] = "tp.approval_status = '" . $this->db->escape($data['filter_approval_status']) . "'";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sort_data = array(
            'p.name',
            'p.model', 
            'p.price',
            'p.quantity',
            'tp.approval_status',
            'tp.last_sync'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY p.name";
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
    }

    /**
     * Get total products count
     */
    public function getTotalProducts($data = array()) {
        $sql = "
            SELECT COUNT(DISTINCT p.product_id) AS total 
            FROM `" . DB_PREFIX . "product` p 
            LEFT JOIN `" . DB_PREFIX . "trendyol_products` tp ON (p.product_id = tp.product_id)
        ";

        $conditions = array();
        
        if (!empty($data['filter_name'])) {
            $conditions[] = "p.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }
        
        if (!empty($data['filter_status'])) {
            $conditions[] = "tp.status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_approval_status'])) {
            $conditions[] = "tp.approval_status = '" . $this->db->escape($data['filter_approval_status']) . "'";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    /**
     * Save product to Trendyol
     */
    public function saveProduct($product_id, $data) {
        $existing = $this->db->query("
            SELECT trendyol_product_id 
            FROM `" . DB_PREFIX . "trendyol_products` 
            WHERE product_id = '" . (int)$product_id . "'
        ");

        $data['date_modified'] = date('Y-m-d H:i:s');
        
        if ($existing->num_rows) {
            $sql = "UPDATE `" . DB_PREFIX . "trendyol_products` SET ";
            $update_data = array();
            
            foreach ($data as $key => $value) {
                if ($key !== 'date_added') {
                    $update_data[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
                }
            }
            
            $sql .= implode(", ", $update_data);
            $sql .= " WHERE product_id = '" . (int)$product_id . "'";
            
            $this->db->query($sql);
        } else {
            $data['product_id'] = $product_id;
            $data['date_added'] = date('Y-m-d H:i:s');
            
            $sql = "INSERT INTO `" . DB_PREFIX . "trendyol_products` SET ";
            $insert_data = array();
            
            foreach ($data as $key => $value) {
                $insert_data[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
            }
            
            $sql .= implode(", ", $insert_data);
            
            $this->db->query($sql);
        }
    }

    /**
     * Get Trendyol orders
     */
    public function getOrders($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "trendyol_orders`";
        
        $conditions = array();
        
        if (!empty($data['filter_status'])) {
            $conditions[] = "status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_date_from'])) {
            $conditions[] = "order_date >= '" . $this->db->escape($data['filter_date_from']) . "'";
        }
        
        if (!empty($data['filter_date_to'])) {
            $conditions[] = "order_date <= '" . $this->db->escape($data['filter_date_to']) . "'";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY order_date DESC";

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
     * Get analytics data
     */
    public function getAnalytics($date_range = 'last_30_days', $metric_type = null) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "trendyol_analytics`";
        
        $conditions = array();
        
        // Date range filtering
        switch ($date_range) {
            case 'today':
                $conditions[] = "date = CURDATE()";
                break;
            case 'yesterday':
                $conditions[] = "date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                break;
            case 'last_7_days':
                $conditions[] = "date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                break;
            case 'last_30_days':
                $conditions[] = "date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                break;
            case 'this_month':
                $conditions[] = "YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())";
                break;
            case 'last_month':
                $conditions[] = "date >= DATE_SUB(DATE_SUB(CURDATE(), INTERVAL DAY(CURDATE())-1 DAY), INTERVAL 1 MONTH) AND date < DATE_SUB(CURDATE(), INTERVAL DAY(CURDATE())-1 DAY)";
                break;
        }
        
        if ($metric_type) {
            $conditions[] = "metric_type = '" . $this->db->escape($metric_type) . "'";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY date DESC, metric_type ASC";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
     * Save analytics data
     */
    public function saveAnalytics($data) {
        $data['date_added'] = date('Y-m-d H:i:s');
        $data['date_modified'] = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO `" . DB_PREFIX . "trendyol_analytics` SET ";
        $insert_data = array();
        
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            $insert_data[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
        }
        
        $sql .= implode(", ", $insert_data);
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }

    /**
     * Get inventory tracking data
     */
    public function getInventoryTracking($product_id = null) {
        $sql = "
            SELECT it.*, p.name as product_name, p.model, p.price 
            FROM `" . DB_PREFIX . "trendyol_inventory_tracking` it 
            LEFT JOIN `" . DB_PREFIX . "product` p ON (it.product_id = p.product_id)
        ";
        
        if ($product_id) {
            $sql .= " WHERE it.product_id = '" . (int)$product_id . "'";
        }
        
        $sql .= " ORDER BY it.date_modified DESC";
        
        $query = $this->db->query($sql);
        
        return $product_id ? $query->row : $query->rows;
    }

    /**
     * Update inventory tracking
     */
    public function updateInventoryTracking($product_id, $data) {
        $existing = $this->db->query("
            SELECT tracking_id 
            FROM `" . DB_PREFIX . "trendyol_inventory_tracking` 
            WHERE product_id = '" . (int)$product_id . "'
        ");

        $data['date_modified'] = date('Y-m-d H:i:s');
        
        if ($existing->num_rows) {
            $sql = "UPDATE `" . DB_PREFIX . "trendyol_inventory_tracking` SET ";
            $update_data = array();
            
            foreach ($data as $key => $value) {
                if ($key !== 'date_added' && $key !== 'tracking_id') {
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    }
                    $update_data[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
                }
            }
            
            $sql .= implode(", ", $update_data);
            $sql .= " WHERE product_id = '" . (int)$product_id . "'";
            
            $this->db->query($sql);
        } else {
            $data['product_id'] = $product_id;
            $data['date_added'] = date('Y-m-d H:i:s');
            
            $sql = "INSERT INTO `" . DB_PREFIX . "trendyol_inventory_tracking` SET ";
            $insert_data = array();
            
            foreach ($data as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                $insert_data[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
            }
            
            $sql .= implode(", ", $insert_data);
            
            $this->db->query($sql);
        }
    }

    /**
     * Get campaigns
     */
    public function getCampaigns($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "trendyol_campaigns`";
        
        $conditions = array();
        
        if (!empty($data['filter_status'])) {
            $conditions[] = "status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_type'])) {
            $conditions[] = "campaign_type = '" . $this->db->escape($data['filter_type']) . "'";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY date_added DESC";

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
     * Save campaign
     */
    public function saveCampaign($data) {
        $data['date_added'] = date('Y-m-d H:i:s');
        $data['date_modified'] = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO `" . DB_PREFIX . "trendyol_campaigns` SET ";
        $insert_data = array();
        
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            $insert_data[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
        }
        
        $sql .= implode(", ", $insert_data);
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
} 