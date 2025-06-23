<?php
/**
 * Amazon Model Class
 * 
 * Amazon SP-API entegrasyonu için model sınıfı
 * Ürün, sipariş, stok ve kategori yönetimi işlemleri
 * 
 * @category   Model
 * @package    MesChain-Sync
 * @subpackage Amazon
 * @version    3.0.4.0
 * @author     MezBjen Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

class ModelExtensionMestechAmazon extends Model {
    
    private $log;
    private $entegrator;
    
    public function __construct($registry) {
        parent::__construct($registry);
        
        // Log dosyası oluştur
        $this->log = new Log('amazon.log');
        
        // Amazon entegratör sınıfını yükle
        require_once(DIR_SYSTEM . 'library/entegrator/amazon.php');
        $this->entegrator = new \MesChain\Library\Entegrator\Amazon($registry);
    }
    
    /**
     * Amazon modülü kurulum
     */
    public function install() {
        try {
            $this->createTables();
            $this->log->write('Amazon modülü başarıyla kuruldu');
            return true;
        } catch (Exception $e) {
            $this->log->write('Amazon modülü kurulum hatası: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Amazon modülü kaldırma
     */
    public function uninstall() {
        try {
            $this->dropTables();
            $this->log->write('Amazon modülü başarıyla kaldırıldı');
            return true;
        } catch (Exception $e) {
            $this->log->write('Amazon modülü kaldırma hatası: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Veritabanı tablolarını oluştur
     */
    private function createTables() {
        // Amazon ürün tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mestech_amazon_product` (
                `amazon_product_id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `asin` varchar(20) NOT NULL,
                `sku` varchar(100) NOT NULL,
                `seller_sku` varchar(100) DEFAULT NULL,
                `marketplace_id` varchar(20) NOT NULL,
                `product_type` varchar(50) DEFAULT NULL,
                `brand` varchar(100) DEFAULT NULL,
                `manufacturer` varchar(100) DEFAULT NULL,
                `part_number` varchar(100) DEFAULT NULL,
                `model_number` varchar(100) DEFAULT NULL,
                `title` text NOT NULL,
                `description` longtext DEFAULT NULL,
                `bullet_points` text DEFAULT NULL,
                `keywords` text DEFAULT NULL,
                `category_id` int(11) DEFAULT NULL,
                `browse_node_id` varchar(20) DEFAULT NULL,
                `item_type` varchar(50) DEFAULT NULL,
                `condition_type` enum('New','Used','Collectible','Refurbished') DEFAULT 'New',
                `condition_note` text DEFAULT NULL,
                `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
                `sale_price` decimal(15,4) DEFAULT NULL,
                `currency` varchar(3) DEFAULT 'USD',
                `quantity` int(11) NOT NULL DEFAULT '0',
                `min_quantity` int(11) DEFAULT '1',
                `max_quantity` int(11) DEFAULT NULL,
                `fulfillment_channel` enum('FBA','FBM') DEFAULT 'FBM',
                `shipping_template` varchar(100) DEFAULT NULL,
                `tax_code` varchar(20) DEFAULT NULL,
                `weight` decimal(8,2) DEFAULT NULL,
                `weight_unit` varchar(10) DEFAULT 'kg',
                `dimensions_length` decimal(8,2) DEFAULT NULL,
                `dimensions_width` decimal(8,2) DEFAULT NULL,
                `dimensions_height` decimal(8,2) DEFAULT NULL,
                `dimension_unit` varchar(10) DEFAULT 'cm',
                `package_weight` decimal(8,2) DEFAULT NULL,
                `package_length` decimal(8,2) DEFAULT NULL,
                `package_width` decimal(8,2) DEFAULT NULL,
                `package_height` decimal(8,2) DEFAULT NULL,
                `main_image` varchar(500) DEFAULT NULL,
                `additional_images` text DEFAULT NULL,
                `swatch_images` text DEFAULT NULL,
                `variation_theme` varchar(50) DEFAULT NULL,
                `parent_asin` varchar(20) DEFAULT NULL,
                `child_asins` text DEFAULT NULL,
                `variation_data` text DEFAULT NULL,
                `listing_status` enum('Active','Inactive','Incomplete','Suppressed') DEFAULT 'Inactive',
                `buybox_eligible` tinyint(1) DEFAULT '0',
                `buybox_price` decimal(15,4) DEFAULT NULL,
                `lowest_price` decimal(15,4) DEFAULT NULL,
                `competitive_price` decimal(15,4) DEFAULT NULL,
                `sales_rank` int(11) DEFAULT NULL,
                `sales_rank_category` varchar(100) DEFAULT NULL,
                `review_count` int(11) DEFAULT '0',
                `review_rating` decimal(3,2) DEFAULT NULL,
                `fba_fees` decimal(15,4) DEFAULT NULL,
                `referral_fees` decimal(15,4) DEFAULT NULL,
                `closing_fees` decimal(15,4) DEFAULT NULL,
                `total_fees` decimal(15,4) DEFAULT NULL,
                `profit_margin` decimal(5,2) DEFAULT NULL,
                `last_sync` datetime DEFAULT NULL,
                `sync_status` enum('pending','syncing','completed','failed') DEFAULT 'pending',
                `sync_errors` text DEFAULT NULL,
                `api_response` longtext DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `status` tinyint(1) NOT NULL DEFAULT '1',
                PRIMARY KEY (`amazon_product_id`),
                UNIQUE KEY `asin_marketplace` (`asin`, `marketplace_id`),
                UNIQUE KEY `sku_marketplace` (`sku`, `marketplace_id`),
                KEY `product_id` (`product_id`),
                KEY `listing_status` (`listing_status`),
                KEY `sync_status` (`sync_status`),
                KEY `last_sync` (`last_sync`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Amazon sipariş tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mestech_amazon_order` (
                `amazon_order_id` int(11) NOT NULL AUTO_INCREMENT,
                `order_id` int(11) DEFAULT NULL,
                `amazon_order_number` varchar(50) NOT NULL,
                `seller_order_id` varchar(50) DEFAULT NULL,
                `marketplace_id` varchar(20) NOT NULL,
                `purchase_date` datetime NOT NULL,
                `last_update_date` datetime DEFAULT NULL,
                `order_status` enum('Pending','Unshipped','PartiallyShipped','Shipped','Cancelled','Unfulfillable') NOT NULL,
                `fulfillment_channel` enum('FBA','FBM') NOT NULL,
                `sales_channel` varchar(50) DEFAULT NULL,
                `order_channel` varchar(50) DEFAULT NULL,
                `ship_service_level` varchar(50) DEFAULT NULL,
                `order_type` varchar(50) DEFAULT NULL,
                `earliest_ship_date` datetime DEFAULT NULL,
                `latest_ship_date` datetime DEFAULT NULL,
                `earliest_delivery_date` datetime DEFAULT NULL,
                `latest_delivery_date` datetime DEFAULT NULL,
                `is_business_order` tinyint(1) DEFAULT '0',
                `is_prime` tinyint(1) DEFAULT '0',
                `is_premium_order` tinyint(1) DEFAULT '0',
                `is_global_express_enabled` tinyint(1) DEFAULT '0',
                `replaced_order_id` varchar(50) DEFAULT NULL,
                `is_replacement_order` tinyint(1) DEFAULT '0',
                `promise_response_due_date` datetime DEFAULT NULL,
                `is_estimated_ship_date_set` tinyint(1) DEFAULT '0',
                `buyer_name` varchar(100) DEFAULT NULL,
                `buyer_email` varchar(150) DEFAULT NULL,
                `buyer_phone` varchar(20) DEFAULT NULL,
                `shipping_name` varchar(100) DEFAULT NULL,
                `shipping_address_line1` varchar(200) DEFAULT NULL,
                `shipping_address_line2` varchar(200) DEFAULT NULL,
                `shipping_address_line3` varchar(200) DEFAULT NULL,
                `shipping_city` varchar(100) DEFAULT NULL,
                `shipping_county` varchar(100) DEFAULT NULL,
                `shipping_district` varchar(100) DEFAULT NULL,
                `shipping_state_or_region` varchar(100) DEFAULT NULL,
                `shipping_postal_code` varchar(20) DEFAULT NULL,
                `shipping_country_code` varchar(2) DEFAULT NULL,
                `shipping_phone` varchar(20) DEFAULT NULL,
                `billing_name` varchar(100) DEFAULT NULL,
                `billing_address_line1` varchar(200) DEFAULT NULL,
                `billing_address_line2` varchar(200) DEFAULT NULL,
                `billing_address_line3` varchar(200) DEFAULT NULL,
                `billing_city` varchar(100) DEFAULT NULL,
                `billing_county` varchar(100) DEFAULT NULL,
                `billing_district` varchar(100) DEFAULT NULL,
                `billing_state_or_region` varchar(100) DEFAULT NULL,
                `billing_postal_code` varchar(20) DEFAULT NULL,
                `billing_country_code` varchar(2) DEFAULT NULL,
                `billing_phone` varchar(20) DEFAULT NULL,
                `order_total` decimal(15,4) NOT NULL DEFAULT '0.0000',
                `currency_code` varchar(3) DEFAULT 'USD',
                `number_of_items_shipped` int(11) DEFAULT '0',
                `number_of_items_unshipped` int(11) DEFAULT '0',
                `payment_execution_detail` text DEFAULT NULL,
                `payment_method` varchar(50) DEFAULT NULL,
                `payment_method_details` text DEFAULT NULL,
                `marketplace_tax_info` text DEFAULT NULL,
                `seller_display_name` varchar(100) DEFAULT NULL,
                `shipment_service_level_category` varchar(50) DEFAULT NULL,
                `shipped_by_amazon_tfm` tinyint(1) DEFAULT '0',
                `tfm_shipment_status` varchar(50) DEFAULT NULL,
                `cba_displayable_shipping_label` varchar(100) DEFAULT NULL,
                `order_language` varchar(10) DEFAULT NULL,
                `easy_ship_shipment_status` varchar(50) DEFAULT NULL,
                `electronic_invoice_status` varchar(50) DEFAULT NULL,
                `last_sync` datetime DEFAULT NULL,
                `sync_status` enum('pending','syncing','completed','failed') DEFAULT 'pending',
                `sync_errors` text DEFAULT NULL,
                `api_response` longtext DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `status` tinyint(1) NOT NULL DEFAULT '1',
                PRIMARY KEY (`amazon_order_id`),
                UNIQUE KEY `amazon_order_number` (`amazon_order_number`),
                KEY `order_id` (`order_id`),
                KEY `order_status` (`order_status`),
                KEY `purchase_date` (`purchase_date`),
                KEY `sync_status` (`sync_status`),
                KEY `marketplace_id` (`marketplace_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Amazon kategori tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mestech_amazon_category` (
                `amazon_category_id` int(11) NOT NULL AUTO_INCREMENT,
                `category_id` int(11) DEFAULT NULL,
                `browse_node_id` varchar(20) NOT NULL,
                `parent_browse_node_id` varchar(20) DEFAULT NULL,
                `marketplace_id` varchar(20) NOT NULL,
                `category_name` varchar(200) NOT NULL,
                `category_path` text DEFAULT NULL,
                `product_type` varchar(100) DEFAULT NULL,
                `item_type_keyword` varchar(100) DEFAULT NULL,
                `refinements` text DEFAULT NULL,
                `is_root` tinyint(1) DEFAULT '0',
                `has_children` tinyint(1) DEFAULT '0',
                `level` int(11) DEFAULT '0',
                `sort_order` int(11) DEFAULT '0',
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `status` tinyint(1) NOT NULL DEFAULT '1',
                PRIMARY KEY (`amazon_category_id`),
                UNIQUE KEY `browse_node_marketplace` (`browse_node_id`, `marketplace_id`),
                KEY `category_id` (`category_id`),
                KEY `parent_browse_node_id` (`parent_browse_node_id`),
                KEY `marketplace_id` (`marketplace_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Amazon log tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mestech_amazon_log` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `operation_type` enum('product_sync','order_sync','inventory_sync','category_sync','api_call','webhook','error','info') NOT NULL,
                `operation_id` varchar(100) DEFAULT NULL,
                `level` enum('emergency','alert','critical','error','warning','notice','info','debug') DEFAULT 'info',
                `message` text NOT NULL,
                `context_data` longtext DEFAULT NULL,
                `request_data` longtext DEFAULT NULL,
                `response_data` longtext DEFAULT NULL,
                `execution_time` decimal(8,3) DEFAULT NULL,
                `memory_usage` int(11) DEFAULT NULL,
                `user_id` int(11) DEFAULT NULL,
                `ip_address` varchar(45) DEFAULT NULL,
                `user_agent` text DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`log_id`),
                KEY `operation_type` (`operation_type`),
                KEY `level` (`level`),
                KEY `created_at` (`created_at`),
                KEY `operation_id` (`operation_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    /**
     * Veritabanı tablolarını sil
     */
    private function dropTables() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mestech_amazon_log`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mestech_amazon_category`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mestech_amazon_order`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "mestech_amazon_product`");
    }
    
    /**
     * Amazon ürün ekle/güncelle
     */
    public function addProduct($data) {
        try {
            $sql = "INSERT INTO " . DB_PREFIX . "mestech_amazon_product SET ";
            $sql .= "product_id = '" . (int)$data['product_id'] . "', ";
            $sql .= "asin = '" . $this->db->escape($data['asin']) . "', ";
            $sql .= "sku = '" . $this->db->escape($data['sku']) . "', ";
            $sql .= "marketplace_id = '" . $this->db->escape($data['marketplace_id']) . "', ";
            $sql .= "title = '" . $this->db->escape($data['title']) . "', ";
            $sql .= "price = '" . (float)$data['price'] . "', ";
            $sql .= "quantity = '" . (int)$data['quantity'] . "', ";
            $sql .= "listing_status = '" . $this->db->escape($data['listing_status'] ?? 'Inactive') . "', ";
            $sql .= "created_at = NOW(), updated_at = NOW()";
            $sql .= " ON DUPLICATE KEY UPDATE ";
            $sql .= "title = '" . $this->db->escape($data['title']) . "', ";
            $sql .= "price = '" . (float)$data['price'] . "', ";
            $sql .= "quantity = '" . (int)$data['quantity'] . "', ";
            $sql .= "listing_status = '" . $this->db->escape($data['listing_status'] ?? 'Inactive') . "', ";
            $sql .= "updated_at = NOW()";
            
            $this->db->query($sql);
            
            $this->log->write('Amazon ürün eklendi/güncellendi: ' . $data['sku']);
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Amazon ürün ekleme hatası: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Amazon ürün listesi getir
     */
    public function getProducts($data = []) {
        $sql = "SELECT ap.*, p.name as product_name, p.model, p.image 
                FROM " . DB_PREFIX . "mestech_amazon_product ap 
                LEFT JOIN " . DB_PREFIX . "product p ON (ap.product_id = p.product_id) 
                WHERE ap.status = '1'";
        
        if (!empty($data['filter_sku'])) {
            $sql .= " AND ap.sku LIKE '%" . $this->db->escape($data['filter_sku']) . "%'";
        }
        
        if (!empty($data['filter_asin'])) {
            $sql .= " AND ap.asin LIKE '%" . $this->db->escape($data['filter_asin']) . "%'";
        }
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND ap.listing_status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        $sort_data = [
            'ap.title',
            'ap.sku',
            'ap.asin',
            'ap.price',
            'ap.quantity',
            'ap.listing_status',
            'ap.last_sync'
        ];
        
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY ap.updated_at";
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
     * Amazon sipariş ekle/güncelle
     */
    public function addOrder($data) {
        try {
            $sql = "INSERT INTO " . DB_PREFIX . "mestech_amazon_order SET ";
            $sql .= "amazon_order_number = '" . $this->db->escape($data['amazon_order_number']) . "', ";
            $sql .= "marketplace_id = '" . $this->db->escape($data['marketplace_id']) . "', ";
            $sql .= "purchase_date = '" . $this->db->escape($data['purchase_date']) . "', ";
            $sql .= "order_status = '" . $this->db->escape($data['order_status']) . "', ";
            $sql .= "fulfillment_channel = '" . $this->db->escape($data['fulfillment_channel']) . "', ";
            $sql .= "order_total = '" . (float)$data['order_total'] . "', ";
            $sql .= "currency_code = '" . $this->db->escape($data['currency_code'] ?? 'USD') . "', ";
            $sql .= "buyer_name = '" . $this->db->escape($data['buyer_name'] ?? '') . "', ";
            $sql .= "buyer_email = '" . $this->db->escape($data['buyer_email'] ?? '') . "', ";
            $sql .= "created_at = NOW(), updated_at = NOW()";
            $sql .= " ON DUPLICATE KEY UPDATE ";
            $sql .= "order_status = '" . $this->db->escape($data['order_status']) . "', ";
            $sql .= "order_total = '" . (float)$data['order_total'] . "', ";
            $sql .= "updated_at = NOW()";
            
            $this->db->query($sql);
            
            $this->log->write('Amazon sipariş eklendi/güncellendi: ' . $data['amazon_order_number']);
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Amazon sipariş ekleme hatası: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Amazon sipariş listesi getir
     */
    public function getOrders($data = []) {
        $sql = "SELECT ao.*, o.order_id as opencart_order_id 
                FROM " . DB_PREFIX . "mestech_amazon_order ao 
                LEFT JOIN " . DB_PREFIX . "order o ON (ao.order_id = o.order_id) 
                WHERE ao.status = '1'";
        
        if (!empty($data['filter_order_number'])) {
            $sql .= " AND ao.amazon_order_number LIKE '%" . $this->db->escape($data['filter_order_number']) . "%'";
        }
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND ao.order_status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(ao.purchase_date) >= DATE('" . $this->db->escape($data['filter_date_start']) . "')";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(ao.purchase_date) <= DATE('" . $this->db->escape($data['filter_date_end']) . "')";
        }
        
        $sql .= " ORDER BY ao.purchase_date DESC";
        
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
     * Amazon API bağlantı testi
     */
    public function testConnection() {
        try {
            $result = $this->entegrator->testConnection();
            
            if ($result['success']) {
                $this->addLog('api_call', 'info', 'Amazon API bağlantı testi başarılı', $result);
                return [
                    'success' => true,
                    'message' => 'Amazon SP-API bağlantısı başarılı!'
                ];
            } else {
                $this->addLog('api_call', 'error', 'Amazon API bağlantı testi başarısız', $result);
                return [
                    'success' => false,
                    'message' => 'Amazon SP-API bağlantısı başarısız: ' . ($result['error'] ?? 'Bilinmeyen hata')
                ];
            }
            
        } catch (Exception $e) {
            $this->addLog('api_call', 'error', 'Amazon API bağlantı testi hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Bağlantı testi hatası: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Ürün senkronizasyonu
     */
    public function syncProducts($limit = 50) {
        try {
            $products = $this->getProducts(['limit' => $limit, 'filter_status' => 'Active']);
            $sync_results = [
                'success' => 0,
                'failed' => 0,
                'total' => count($products)
            ];
            
            foreach ($products as $product) {
                $result = $this->entegrator->updateListing($product['asin'], [
                    'price' => $product['price'],
                    'quantity' => $product['quantity']
                ]);
                
                if ($result['success']) {
                    $sync_results['success']++;
                    $this->updateProductSyncStatus($product['amazon_product_id'], 'completed');
                } else {
                    $sync_results['failed']++;
                    $this->updateProductSyncStatus($product['amazon_product_id'], 'failed', $result['error'] ?? '');
                }
            }
            
            $this->addLog('product_sync', 'info', 'Ürün senkronizasyonu tamamlandı', $sync_results);
            return $sync_results;
            
        } catch (Exception $e) {
            $this->addLog('product_sync', 'error', 'Ürün senkronizasyon hatası: ' . $e->getMessage());
            return ['success' => 0, 'failed' => 0, 'total' => 0, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Sipariş senkronizasyonu
     */
    public function syncOrders($days = 7) {
        try {
            $created_after = date('Y-m-d\TH:i:s\Z', strtotime('-' . $days . ' days'));
            $result = $this->entegrator->getOrders(['CreatedAfter' => $created_after]);
            
            if (!$result['success']) {
                throw new Exception($result['error'] ?? 'Sipariş getirme hatası');
            }
            
            $sync_results = [
                'success' => 0,
                'failed' => 0,
                'total' => 0
            ];
            
            if (isset($result['data']['Orders'])) {
                $orders = $result['data']['Orders'];
                $sync_results['total'] = count($orders);
                
                foreach ($orders as $order) {
                    $order_data = [
                        'amazon_order_number' => $order['AmazonOrderId'],
                        'marketplace_id' => $order['MarketplaceId'],
                        'purchase_date' => $order['PurchaseDate'],
                        'order_status' => $order['OrderStatus'],
                        'fulfillment_channel' => $order['FulfillmentChannel'],
                        'order_total' => $order['OrderTotal']['Amount'] ?? 0,
                        'currency_code' => $order['OrderTotal']['CurrencyCode'] ?? 'USD',
                        'buyer_name' => $order['BuyerInfo']['BuyerName'] ?? '',
                        'buyer_email' => $order['BuyerInfo']['BuyerEmail'] ?? ''
                    ];
                    
                    if ($this->addOrder($order_data)) {
                        $sync_results['success']++;
                    } else {
                        $sync_results['failed']++;
                    }
                }
            }
            
            $this->addLog('order_sync', 'info', 'Sipariş senkronizasyonu tamamlandı', $sync_results);
            return $sync_results;
            
        } catch (Exception $e) {
            $this->addLog('order_sync', 'error', 'Sipariş senkronizasyon hatası: ' . $e->getMessage());
            return ['success' => 0, 'failed' => 0, 'total' => 0, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Ürün senkronizasyon durumunu güncelle
     */
    private function updateProductSyncStatus($amazon_product_id, $status, $error = '') {
        $sql = "UPDATE " . DB_PREFIX . "mestech_amazon_product SET ";
        $sql .= "sync_status = '" . $this->db->escape($status) . "', ";
        $sql .= "last_sync = NOW(), ";
        $sql .= "sync_errors = '" . $this->db->escape($error) . "', ";
        $sql .= "updated_at = NOW() ";
        $sql .= "WHERE amazon_product_id = '" . (int)$amazon_product_id . "'";
        
        $this->db->query($sql);
    }
    
    /**
     * Log kaydı ekle
     */
    public function addLog($operation_type, $level, $message, $context_data = null) {
        $sql = "INSERT INTO " . DB_PREFIX . "mestech_amazon_log SET ";
        $sql .= "operation_type = '" . $this->db->escape($operation_type) . "', ";
        $sql .= "level = '" . $this->db->escape($level) . "', ";
        $sql .= "message = '" . $this->db->escape($message) . "', ";
        $sql .= "context_data = '" . $this->db->escape(json_encode($context_data)) . "', ";
        $sql .= "ip_address = '" . $this->db->escape($this->request->server['REMOTE_ADDR'] ?? '') . "', ";
        $sql .= "user_agent = '" . $this->db->escape($this->request->server['HTTP_USER_AGENT'] ?? '') . "', ";
        $sql .= "created_at = NOW()";
        
        $this->db->query($sql);
    }
    
    /**
     * Log kayıtlarını getir
     */
    public function getLogs($data = []) {
        $sql = "SELECT * FROM " . DB_PREFIX . "mestech_amazon_log WHERE 1=1";
        
        if (!empty($data['filter_operation'])) {
            $sql .= " AND operation_type = '" . $this->db->escape($data['filter_operation']) . "'";
        }
        
        if (!empty($data['filter_level'])) {
            $sql .= " AND level = '" . $this->db->escape($data['filter_level']) . "'";
        }
        
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(created_at) >= DATE('" . $this->db->escape($data['filter_date_start']) . "')";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(created_at) <= DATE('" . $this->db->escape($data['filter_date_end']) . "')";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
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
     * İstatistikleri getir
     */
    public function getStatistics() {
        $stats = [];
        
        // Toplam ürün sayısı
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "mestech_amazon_product WHERE status = '1'");
        $stats['total_products'] = $query->row['total'];
        
        // Aktif ürün sayısı
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "mestech_amazon_product WHERE status = '1' AND listing_status = 'Active'");
        $stats['active_products'] = $query->row['total'];
        
        // Toplam sipariş sayısı
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "mestech_amazon_order WHERE status = '1'");
        $stats['total_orders'] = $query->row['total'];
        
        // Bu ayki sipariş sayısı
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "mestech_amazon_order WHERE status = '1' AND MONTH(purchase_date) = MONTH(NOW()) AND YEAR(purchase_date) = YEAR(NOW())");
        $stats['monthly_orders'] = $query->row['total'];
        
        // Toplam satış tutarı
        $query = $this->db->query("SELECT SUM(order_total) as total FROM " . DB_PREFIX . "mestech_amazon_order WHERE status = '1' AND order_status NOT IN ('Cancelled', 'Unfulfillable')");
        $stats['total_sales'] = $query->row['total'] ?? 0;
        
        // Son senkronizasyon tarihi
        $query = $this->db->query("SELECT MAX(last_sync) as last_sync FROM " . DB_PREFIX . "mestech_amazon_product WHERE last_sync IS NOT NULL");
        $stats['last_sync'] = $query->row['last_sync'];
        
        return $stats;
    }
} 