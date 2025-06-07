<?php
/**
 * MesChain-Sync - Ozon Pazaryeri Entegrasyonu Model
 * 
 * Bu model, Ozon pazaryeri entegrasyonu için gerekli veritabanı işlemlerini içerir.
 * Ürün yönetimi, sipariş yönetimi, kategori yönetimi ve log yönetimi fonksiyonları sağlar.
 * 
 * @author      MesTech
 * @copyright   Copyright (c) 2023, MesTech
 * @license     MIT License
 * @version     1.0.0
 */
class ModelExtensionMestechOzon extends Model {
    
    /**
     * Modül kurulumu - Veritabanı tablolarını oluştur
     */
    public function install() {
        // Ozon ürün tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mestech_ozon_product` (
                `ozon_product_id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `ozon_id` varchar(100) NOT NULL,
                `offer_id` varchar(100) DEFAULT NULL,
                `name` varchar(255) NOT NULL,
                `description` text,
                `category_id` int(11) DEFAULT NULL,
                `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
                `old_price` decimal(15,4) DEFAULT '0.0000',
                `premium_price` decimal(15,4) DEFAULT '0.0000',
                `vat` varchar(10) DEFAULT '0.20',
                `min_ozon_price` decimal(15,4) DEFAULT '0.0000',
                `marketing_price` decimal(15,4) DEFAULT '0.0000',
                `marketing_seller_price` decimal(15,4) DEFAULT '0.0000',
                `min_price` decimal(15,4) DEFAULT '0.0000',
                `currency_code` varchar(3) DEFAULT 'RUB',
                `recommended_price` decimal(15,4) DEFAULT '0.0000',
                `auto_action_enabled` varchar(20) DEFAULT 'UNKNOWN',
                `auto_action_enabled_by_seller` varchar(20) DEFAULT 'UNKNOWN',
                `images` text,
                `color_image` varchar(500) DEFAULT NULL,
                `primary_image` varchar(500) DEFAULT NULL,
                `status` enum('imported','active','inactive','archived','failed_moderation','failed_validation','processing','processed') DEFAULT 'imported',
                `state` varchar(50) DEFAULT 'processing',
                `visible` tinyint(1) DEFAULT '1',
                `buybox_price` decimal(15,4) DEFAULT '0.0000',
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                `errors` text,
                `stocks` int(11) DEFAULT '0',
                `reserved` int(11) DEFAULT '0',
                `fbo_sku` varchar(100) DEFAULT NULL,
                `fbs_sku` varchar(100) DEFAULT NULL,
                `archived` tinyint(1) DEFAULT '0',
                `is_fbo_visible` tinyint(1) DEFAULT '1',
                `is_fbs_visible` tinyint(1) DEFAULT '1',
                `commission_percent` decimal(5,2) DEFAULT '0.00',
                `price_index` varchar(50) DEFAULT 'WITHOUT_INDEX',
                `commissions_fbo` decimal(15,4) DEFAULT '0.0000',
                `commissions_fbs` decimal(15,4) DEFAULT '0.0000',
                `marketing_actions` text,
                `stocks_fbs` int(11) DEFAULT '0',
                `stocks_fbo` int(11) DEFAULT '0',
                `sku` varchar(100) DEFAULT NULL,
                `product_sources` text,
                `barcodes` text,
                `updated_at_ozon` datetime DEFAULT NULL,
                `min_ozon_price_indexed` varchar(20) DEFAULT 'NOT_INDEXED',
                `color_image_ozon` varchar(500) DEFAULT NULL,
                `primary_image_ozon` varchar(500) DEFAULT NULL,
                `images_ozon` text,
                `has_discounted_item` tinyint(1) DEFAULT '0',
                `is_discounted` tinyint(1) DEFAULT '0',
                `discounted_stocks_fbo` int(11) DEFAULT '0',
                `discounted_stocks_fbs` int(11) DEFAULT '0',
                `sync_status` enum('synced','pending','error') DEFAULT 'pending',
                `last_sync` datetime DEFAULT NULL,
                PRIMARY KEY (`ozon_product_id`),
                UNIQUE KEY `product_ozon` (`product_id`, `ozon_id`),
                KEY `ozon_id` (`ozon_id`),
                KEY `offer_id` (`offer_id`),
                KEY `status` (`status`),
                KEY `sync_status` (`sync_status`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Ozon sipariş tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mestech_ozon_order` (
                `ozon_order_id` int(11) NOT NULL AUTO_INCREMENT,
                `order_id` int(11) DEFAULT NULL,
                `ozon_id` varchar(100) NOT NULL,
                `order_number` varchar(100) NOT NULL,
                `posting_number` varchar(100) DEFAULT NULL,
                `status` varchar(50) NOT NULL,
                `substatus` varchar(50) DEFAULT NULL,
                `delivery_method_id` int(11) DEFAULT NULL,
                `delivery_method_name` varchar(255) DEFAULT NULL,
                `tracking_number` varchar(100) DEFAULT NULL,
                `tpl_integration_type` varchar(50) DEFAULT NULL,
                `in_process_at` datetime DEFAULT NULL,
                `shipment_date` datetime DEFAULT NULL,
                `delivering_date` datetime DEFAULT NULL,
                `cancel_reason_id` int(11) DEFAULT NULL,
                `cancelled_at` datetime DEFAULT NULL,
                `customer_id` int(11) DEFAULT NULL,
                `customer_email` varchar(255) DEFAULT NULL,
                `customer_name` varchar(255) DEFAULT NULL,
                `customer_phone` varchar(50) DEFAULT NULL,
                `address_name` varchar(255) DEFAULT NULL,
                `address_phone` varchar(50) DEFAULT NULL,
                `address_postcode` varchar(20) DEFAULT NULL,
                `address_country` varchar(100) DEFAULT NULL,
                `address_region` varchar(100) DEFAULT NULL,
                `address_city` varchar(100) DEFAULT NULL,
                `address_district` varchar(100) DEFAULT NULL,
                `address_street` varchar(255) DEFAULT NULL,
                `address_building` varchar(50) DEFAULT NULL,
                `address_apartment` varchar(50) DEFAULT NULL,
                `address_comment` text,
                `barcodes` text,
                `analytics_data` text,
                `financial_data` text,
                `is_express` tinyint(1) DEFAULT '0',
                `requirements` text,
                `product_exemplars` text,
                `addressee` text,
                `delivery_price` decimal(15,4) DEFAULT '0.0000',
                `total_discount_value` decimal(15,4) DEFAULT '0.0000',
                `total_discount_percent` decimal(5,2) DEFAULT '0.00',
                `order_total` decimal(15,4) NOT NULL DEFAULT '0.0000',
                `currency_code` varchar(3) DEFAULT 'RUB',
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `prr_option` varchar(50) DEFAULT NULL,
                `arbitration` text,
                `cancellation` text,
                `multi_box_qty` int(11) DEFAULT '1',
                `partial_num` int(11) DEFAULT '0',
                `products` text,
                `related_postings` text,
                `tpl_provider_id` int(11) DEFAULT NULL,
                `tpl_provider_name` varchar(255) DEFAULT NULL,
                `tpl_provider` text,
                `warehouse_id` int(11) DEFAULT NULL,
                `warehouse_name` varchar(255) DEFAULT NULL,
                `tax_value` decimal(15,4) DEFAULT '0.0000',
                `is_multibox` tinyint(1) DEFAULT '0',
                `parent_posting_number` varchar(100) DEFAULT NULL,
                `available_actions` text,
                `sync_status` enum('synced','pending','error') DEFAULT 'pending',
                `last_sync` datetime DEFAULT NULL,
                PRIMARY KEY (`ozon_order_id`),
                UNIQUE KEY `ozon_id` (`ozon_id`),
                KEY `order_id` (`order_id`),
                KEY `order_number` (`order_number`),
                KEY `posting_number` (`posting_number`),
                KEY `status` (`status`),
                KEY `sync_status` (`sync_status`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Ozon kategori tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mestech_ozon_category` (
                `ozon_category_id` int(11) NOT NULL AUTO_INCREMENT,
                `category_id` int(11) DEFAULT NULL,
                `ozon_id` int(11) NOT NULL,
                `parent_id` int(11) DEFAULT NULL,
                `name` varchar(255) NOT NULL,
                `type_id` int(11) DEFAULT NULL,
                `type_name` varchar(255) DEFAULT NULL,
                `disabled` tinyint(1) DEFAULT '0',
                `attributes` text,
                `description_category_id` int(11) DEFAULT NULL,
                `category_path` text,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`ozon_category_id`),
                UNIQUE KEY `ozon_id` (`ozon_id`),
                KEY `category_id` (`category_id`),
                KEY `parent_id` (`parent_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Ozon log tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mestech_ozon_log` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `type` enum('info','warning','error','success') DEFAULT 'info',
                `action` varchar(100) NOT NULL,
                `message` text NOT NULL,
                `request_data` text,
                `response_data` text,
                `execution_time` decimal(8,4) DEFAULT NULL,
                `memory_usage` varchar(50) DEFAULT NULL,
                `user_id` int(11) DEFAULT NULL,
                `ip_address` varchar(45) DEFAULT NULL,
                `user_agent` text,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`log_id`),
                KEY `type` (`type`),
                KEY `action` (`action`),
                KEY `created_at` (`created_at`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        $this->writeLog('install', 'Ozon entegrasyonu tabloları başarıyla oluşturuldu');
    }
    
    /**
     * Modül kaldırma
     */
    public function uninstall() {
        // Tabloları silme (isteğe bağlı - veri kaybını önlemek için yorum satırında)
        /*
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mestech_ozon_product`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mestech_ozon_order`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mestech_ozon_category`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mestech_ozon_log`");
        */
        
        // Ayarları sil
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE code LIKE 'module_mestech_ozon%'");
        
        $this->writeLog('uninstall', 'Ozon entegrasyonu ayarları silindi');
    }
    
    /**
     * API bağlantı durumunu kontrol et
     */
    public function checkApiConnection() {
        $settings = $this->getSettings();
        
        if (empty($settings['module_mestech_ozon_api_key']) || 
            empty($settings['module_mestech_ozon_client_id'])) {
            return false;
        }
        
        try {
            // Basit bir API çağrısı yaparak bağlantıyı test et
            $api = $this->getApiClient();
            $result = $api->testConnection();
            
            return $result['success'] ?? false;
        } catch (Exception $e) {
            $this->writeLog('error', 'API bağlantı testi başarısız: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ayarları kaydet
     */
    public function saveSettings($data) {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_mestech_ozon', $data);
        
        $this->writeLog('settings', 'Ozon ayarları güncellendi');
    }
    
    /**
     * Ayarları getir
     */
    public function getSettings() {
        $this->load->model('setting/setting');
        return $this->model_setting_setting->getSetting('module_mestech_ozon');
    }
    
    /**
     * Toplam ürün sayısını getir
     */
    public function getTotalProducts($filter = array()) {
        $sql = "SELECT COUNT(*) as total FROM " . DB_PREFIX . "mestech_ozon_product p WHERE 1=1";
        
        if (!empty($filter['filter_name'])) {
            $sql .= " AND p.name LIKE '%" . $this->db->escape($filter['filter_name']) . "%'";
        }
        
        if (!empty($filter['filter_status'])) {
            $sql .= " AND p.status = '" . $this->db->escape($filter['filter_status']) . "'";
        }
        
        $query = $this->db->query($sql);
        return $query->row['total'];
    }
    
    /**
     * Ürünleri getir
     */
    public function getProducts($filter = array()) {
        $sql = "SELECT p.*, op.name as opencart_name, op.model, op.status as opencart_status 
                FROM " . DB_PREFIX . "mestech_ozon_product p 
                LEFT JOIN " . DB_PREFIX . "product op ON (p.product_id = op.product_id) 
                WHERE 1=1";
        
        if (!empty($filter['filter_name'])) {
            $sql .= " AND p.name LIKE '%" . $this->db->escape($filter['filter_name']) . "%'";
        }
        
        if (!empty($filter['filter_model'])) {
            $sql .= " AND op.model LIKE '%" . $this->db->escape($filter['filter_model']) . "%'";
        }
        
        if (!empty($filter['filter_status'])) {
            $sql .= " AND p.status = '" . $this->db->escape($filter['filter_status']) . "'";
        }
        
        $sort_data = array(
            'p.name',
            'op.model',
            'p.price',
            'p.status',
            'p.created_at'
        );
        
        if (isset($filter['sort']) && in_array($filter['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $filter['sort'];
        } else {
            $sql .= " ORDER BY p.created_at";
        }
        
        if (isset($filter['order']) && ($filter['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }
        
        if (isset($filter['start']) || isset($filter['limit'])) {
            if ($filter['start'] < 0) {
                $filter['start'] = 0;
            }
            
            if ($filter['limit'] < 1) {
                $filter['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$filter['start'] . "," . (int)$filter['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Toplam sipariş sayısını getir
     */
    public function getTotalOrders($filter = array()) {
        $sql = "SELECT COUNT(*) as total FROM " . DB_PREFIX . "mestech_ozon_order o WHERE 1=1";
        
        if (!empty($filter['filter_order_number'])) {
            $sql .= " AND o.order_number LIKE '%" . $this->db->escape($filter['filter_order_number']) . "%'";
        }
        
        if (!empty($filter['filter_status'])) {
            $sql .= " AND o.status = '" . $this->db->escape($filter['filter_status']) . "'";
        }
        
        $query = $this->db->query($sql);
        return $query->row['total'];
    }
    
    /**
     * Bekleyen sipariş sayısını getir
     */
    public function getPendingOrders() {
        $sql = "SELECT COUNT(*) as total FROM " . DB_PREFIX . "mestech_ozon_order 
                WHERE status IN ('awaiting_packaging', 'awaiting_deliver', 'arbitration')";
        
        $query = $this->db->query($sql);
        return $query->row['total'];
    }
    
    /**
     * Siparişleri getir
     */
    public function getOrders($filter = array()) {
        $sql = "SELECT o.*, oo.order_id as opencart_order_id, oo.total as opencart_total 
                FROM " . DB_PREFIX . "mestech_ozon_order o 
                LEFT JOIN " . DB_PREFIX . "order oo ON (o.order_id = oo.order_id) 
                WHERE 1=1";
        
        if (!empty($filter['filter_order_number'])) {
            $sql .= " AND o.order_number LIKE '%" . $this->db->escape($filter['filter_order_number']) . "%'";
        }
        
        if (!empty($filter['filter_posting_number'])) {
            $sql .= " AND o.posting_number LIKE '%" . $this->db->escape($filter['filter_posting_number']) . "%'";
        }
        
        if (!empty($filter['filter_status'])) {
            $sql .= " AND o.status = '" . $this->db->escape($filter['filter_status']) . "'";
        }
        
        $sort_data = array(
            'o.order_number',
            'o.posting_number',
            'o.status',
            'o.order_total',
            'o.created_at'
        );
        
        if (isset($filter['sort']) && in_array($filter['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $filter['sort'];
        } else {
            $sql .= " ORDER BY o.created_at";
        }
        
        if (isset($filter['order']) && ($filter['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }
        
        if (isset($filter['start']) || isset($filter['limit'])) {
            if ($filter['start'] < 0) {
                $filter['start'] = 0;
            }
            
            if ($filter['limit'] < 1) {
                $filter['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$filter['start'] . "," . (int)$filter['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Son siparişleri getir
     */
    public function getLatestOrders($limit = 5) {
        $sql = "SELECT * FROM " . DB_PREFIX . "mestech_ozon_order 
                ORDER BY created_at DESC 
                LIMIT " . (int)$limit;
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Kategorileri getir
     */
    public function getCategories($filter = array()) {
        $sql = "SELECT c.*, oc.name as opencart_name 
                FROM " . DB_PREFIX . "mestech_ozon_category c 
                LEFT JOIN " . DB_PREFIX . "category_description oc ON (c.category_id = oc.category_id AND oc.language_id = '" . (int)$this->config->get('config_language_id') . "') 
                WHERE 1=1";
        
        if (!empty($filter['filter_name'])) {
            $sql .= " AND c.name LIKE '%" . $this->db->escape($filter['filter_name']) . "%'";
        }
        
        if (isset($filter['filter_disabled'])) {
            $sql .= " AND c.disabled = '" . (int)$filter['filter_disabled'] . "'";
        }
        
        $sql .= " ORDER BY c.name ASC";
        
        if (isset($filter['start']) || isset($filter['limit'])) {
            if ($filter['start'] < 0) {
                $filter['start'] = 0;
            }
            
            if ($filter['limit'] < 1) {
                $filter['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$filter['start'] . "," . (int)$filter['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Kategori eşleştirmesi kaydet
     */
    public function saveCategoryMapping($opencart_category_id, $ozon_category_id) {
        $this->db->query("UPDATE " . DB_PREFIX . "mestech_ozon_category 
                         SET category_id = '" . (int)$opencart_category_id . "', 
                             updated_at = NOW() 
                         WHERE ozon_id = '" . (int)$ozon_category_id . "'");
        
        $this->writeLog('category_mapping', "Kategori eşleştirmesi kaydedildi: OpenCart ID {$opencart_category_id} -> Ozon ID {$ozon_category_id}");
    }
    
    /**
     * Log kayıtlarını getir
     */
    public function getLogs($filter = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "mestech_ozon_log WHERE 1=1";
        
        if (!empty($filter['filter_type'])) {
            $sql .= " AND type = '" . $this->db->escape($filter['filter_type']) . "'";
        }
        
        if (!empty($filter['filter_action'])) {
            $sql .= " AND action LIKE '%" . $this->db->escape($filter['filter_action']) . "%'";
        }
        
        if (!empty($filter['filter_date_start'])) {
            $sql .= " AND DATE(created_at) >= '" . $this->db->escape($filter['filter_date_start']) . "'";
        }
        
        if (!empty($filter['filter_date_end'])) {
            $sql .= " AND DATE(created_at) <= '" . $this->db->escape($filter['filter_date_end']) . "'";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if (isset($filter['start']) || isset($filter['limit'])) {
            if ($filter['start'] < 0) {
                $filter['start'] = 0;
            }
            
            if ($filter['limit'] < 1) {
                $filter['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$filter['start'] . "," . (int)$filter['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Log kayıtlarını temizle
     */
    public function clearLogs($days = 30) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "mestech_ozon_log 
                         WHERE created_at < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)");
        
        $affected_rows = $this->db->countAffected();
        $this->writeLog('clear_logs', "{$affected_rows} adet log kaydı temizlendi");
        
        return $affected_rows;
    }
    
    /**
     * Ürün senkronizasyonu
     */
    public function syncProducts() {
        try {
            $api = $this->getApiClient();
            $products = $api->getProducts();
            
            $synced_count = 0;
            $error_count = 0;
            
            foreach ($products as $product) {
                try {
                    $this->saveProduct($product);
                    $synced_count++;
                } catch (Exception $e) {
                    $error_count++;
                    $this->writeLog('error', 'Ürün senkronizasyon hatası: ' . $e->getMessage(), $product);
                }
            }
            
            $this->writeLog('sync_products', "Ürün senkronizasyonu tamamlandı. Başarılı: {$synced_count}, Hatalı: {$error_count}");
            
            return array(
                'success' => true,
                'synced' => $synced_count,
                'errors' => $error_count
            );
            
        } catch (Exception $e) {
            $this->writeLog('error', 'Ürün senkronizasyon hatası: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }
    
    /**
     * Sipariş senkronizasyonu
     */
    public function syncOrders() {
        try {
            $api = $this->getApiClient();
            $orders = $api->getOrders();
            
            $synced_count = 0;
            $error_count = 0;
            
            foreach ($orders as $order) {
                try {
                    $this->saveOrder($order);
                    $synced_count++;
                } catch (Exception $e) {
                    $error_count++;
                    $this->writeLog('error', 'Sipariş senkronizasyon hatası: ' . $e->getMessage(), $order);
                }
            }
            
            $this->writeLog('sync_orders', "Sipariş senkronizasyonu tamamlandı. Başarılı: {$synced_count}, Hatalı: {$error_count}");
            
            return array(
                'success' => true,
                'synced' => $synced_count,
                'errors' => $error_count
            );
            
        } catch (Exception $e) {
            $this->writeLog('error', 'Sipariş senkronizasyon hatası: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }
    
    /**
     * Ürün kaydet/güncelle
     */
    private function saveProduct($product_data) {
        $ozon_id = $product_data['id'];
        
        // Mevcut ürünü kontrol et
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mestech_ozon_product WHERE ozon_id = '" . $this->db->escape($ozon_id) . "'");
        
        $data = array(
            'ozon_id' => $ozon_id,
            'offer_id' => $product_data['offer_id'] ?? '',
            'name' => $product_data['name'] ?? '',
            'description' => $product_data['description'] ?? '',
            'price' => $product_data['price'] ?? 0,
            'old_price' => $product_data['old_price'] ?? 0,
            'premium_price' => $product_data['premium_price'] ?? 0,
            'vat' => $product_data['vat'] ?? '0.20',
            'currency_code' => $product_data['currency_code'] ?? 'RUB',
            'images' => json_encode($product_data['images'] ?? array()),
            'status' => $product_data['status'] ?? 'imported',
            'state' => $product_data['state'] ?? 'processing',
            'visible' => $product_data['visible'] ?? 1,
            'stocks' => $product_data['stocks'] ?? 0,
            'sku' => $product_data['sku'] ?? '',
            'sync_status' => 'synced',
            'last_sync' => 'NOW()',
            'updated_at' => 'NOW()'
        );
        
        if ($query->num_rows) {
            // Güncelle
            $sql = "UPDATE " . DB_PREFIX . "mestech_ozon_product SET ";
            $updates = array();
            foreach ($data as $key => $value) {
                if ($value === 'NOW()') {
                    $updates[] = "`{$key}` = NOW()";
                } else {
                    $updates[] = "`{$key}` = '" . $this->db->escape($value) . "'";
                }
            }
            $sql .= implode(', ', $updates);
            $sql .= " WHERE ozon_id = '" . $this->db->escape($ozon_id) . "'";
        } else {
            // Ekle
            $data['created_at'] = 'NOW()';
            $sql = "INSERT INTO " . DB_PREFIX . "mestech_ozon_product SET ";
            $inserts = array();
            foreach ($data as $key => $value) {
                if ($value === 'NOW()') {
                    $inserts[] = "`{$key}` = NOW()";
                } else {
                    $inserts[] = "`{$key}` = '" . $this->db->escape($value) . "'";
                }
            }
            $sql .= implode(', ', $inserts);
        }
        
        $this->db->query($sql);
    }
    
    /**
     * Sipariş kaydet/güncelle
     */
    private function saveOrder($order_data) {
        $ozon_id = $order_data['order_id'];
        
        // Mevcut siparişi kontrol et
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mestech_ozon_order WHERE ozon_id = '" . $this->db->escape($ozon_id) . "'");
        
        $data = array(
            'ozon_id' => $ozon_id,
            'order_number' => $order_data['order_number'] ?? '',
            'posting_number' => $order_data['posting_number'] ?? '',
            'status' => $order_data['status'] ?? '',
            'substatus' => $order_data['substatus'] ?? '',
            'tracking_number' => $order_data['tracking_number'] ?? '',
            'customer_email' => $order_data['customer']['email'] ?? '',
            'customer_name' => $order_data['customer']['name'] ?? '',
            'order_total' => $order_data['total_price'] ?? 0,
            'currency_code' => $order_data['currency_code'] ?? 'RUB',
            'products' => json_encode($order_data['products'] ?? array()),
            'sync_status' => 'synced',
            'last_sync' => 'NOW()',
            'updated_at' => 'NOW()'
        );
        
        if ($query->num_rows) {
            // Güncelle
            $sql = "UPDATE " . DB_PREFIX . "mestech_ozon_order SET ";
            $updates = array();
            foreach ($data as $key => $value) {
                if ($value === 'NOW()') {
                    $updates[] = "`{$key}` = NOW()";
                } else {
                    $updates[] = "`{$key}` = '" . $this->db->escape($value) . "'";
                }
            }
            $sql .= implode(', ', $updates);
            $sql .= " WHERE ozon_id = '" . $this->db->escape($ozon_id) . "'";
        } else {
            // Ekle
            $data['created_at'] = 'NOW()';
            $sql = "INSERT INTO " . DB_PREFIX . "mestech_ozon_order SET ";
            $inserts = array();
            foreach ($data as $key => $value) {
                if ($value === 'NOW()') {
                    $inserts[] = "`{$key}` = NOW()";
                } else {
                    $inserts[] = "`{$key}` = '" . $this->db->escape($value) . "'";
                }
            }
            $sql .= implode(', ', $inserts);
        }
        
        $this->db->query($sql);
    }
    
    /**
     * API istemcisini getir
     */
    private function getApiClient() {
        $settings = $this->getSettings();
        
        // EntegratorOzon sınıfını yükle
        require_once(DIR_SYSTEM . 'library/entegrator/EntegratorOzon.php');
        
        $config = array(
            'api_key' => $settings['module_mestech_ozon_api_key'] ?? '',
            'client_id' => $settings['module_mestech_ozon_client_id'] ?? '',
            'api_url' => $settings['module_mestech_ozon_api_url'] ?? 'https://api-seller.ozon.ru'
        );
        
        return new EntegratorOzon($config);
    }
    
    /**
     * Log kaydı yaz
     */
    private function writeLog($action, $message, $data = null) {
        $log_data = array(
            'type' => 'info',
            'action' => $action,
            'message' => $message,
            'request_data' => $data ? json_encode($data) : null,
            'user_id' => isset($this->user) ? $this->user->getId() : null,
            'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'created_at' => 'NOW()'
        );
        
        $sql = "INSERT INTO " . DB_PREFIX . "mestech_ozon_log SET ";
        $inserts = array();
        foreach ($log_data as $key => $value) {
            if ($value === 'NOW()') {
                $inserts[] = "`{$key}` = NOW()";
            } elseif ($value === null) {
                $inserts[] = "`{$key}` = NULL";
            } else {
                $inserts[] = "`{$key}` = '" . $this->db->escape($value) . "'";
            }
        }
        $sql .= implode(', ', $inserts);
        
        $this->db->query($sql);
    }
}