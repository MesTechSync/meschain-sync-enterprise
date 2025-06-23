<?php
/**
 * Hepsiburada Marketplace Model
 * MesChain-Sync v3.0 - OpenCart 3.0.4.0 Integration
 * Turkish E-commerce Platform Model with Cargo and Promotion Support
 * 
 * @author MesChain Development Team
 * @version 3.0.0
 * @copyright 2024 MesChain Technologies
 */

class ModelExtensionModuleHepsiburada extends Model {
    
    private $log;
    private $logFile = 'hepsiburada_model.log';
    
    /**
     * Kurucu metod
     */
    public function __construct($registry) {
        parent::__construct($registry);
        $this->log = new Log($this->logFile);
    }
    
    /**
     * Hepsiburada helper sınıfını yükle ve döndür
     * 
     * @return MeschainHepsiburadaHelper
     */
    private function getHepsiburadaHelper() {
        require_once(DIR_SYSTEM . 'library/meschain/helper/hepsiburada.php');
        return new MeschainHepsiburadaHelper($this->registry);
    }
    
    /**
     * Hepsiburada ayarlarını kaydet
     * 
     * @param array $settings Ayarlar dizisi
     * @return bool Başarılı mı?
     */
    public function saveSettings($settings) {
        try {
            $this->log->write('[INFO] Hepsiburada ayarları kaydediliyor');
            
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_hepsiburada', $settings);
            
            $this->log->write('[SUCCESS] Hepsiburada ayarları başarıyla kaydedildi');
            return true;
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Hepsiburada ayarları kaydedilemedi: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hepsiburada bağlantısını test et
     * 
     * @return array Test sonucu
     */
    public function testConnection() {
        try {
            $this->log->write('[INFO] Hepsiburada bağlantı testi başlatılıyor');
            
            $hepsiburadaHelper = $this->getHepsiburadaHelper();
            $result = $hepsiburadaHelper->testConnection();
            
            if ($result['success']) {
                $this->log->write('[SUCCESS] Hepsiburada bağlantı testi başarılı');
        } else {
                $this->log->write('[ERROR] Hepsiburada bağlantı testi başarısız: ' . $result['message']);
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Hepsiburada bağlantı testi exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Hepsiburada siparişlerini çek ve veritabanına kaydet
     * 
     * @param array $params Filtre parametreleri
     * @return array Sonuç
     */
    public function importOrders($params = []) {
        try {
            $this->log->write('[INFO] Hepsiburada siparişleri içe aktarılıyor');
            
            $hepsiburadaHelper = $this->getHepsiburadaHelper();
            $ordersResult = $hepsiburadaHelper->getOrders($params);
            
            if (!$ordersResult['success']) {
                return $ordersResult;
            }
            
            $this->load->model('sale/order');
            $importedCount = 0;
            $skippedCount = 0;
            $errors = [];
            
            foreach ($ordersResult['orders'] as $hepsiburadaOrder) {
                // Sipariş detayını al
                $detailResult = $hepsiburadaHelper->getOrderDetail($hepsiburadaOrder['id']);
                
                if ($detailResult['success']) {
                    // Sipariş zaten var mı kontrol et
                    if (!$this->orderExists($hepsiburadaOrder['id'])) {
                        // OpenCart formatına dönüştür
                        $convertResult = $hepsiburadaHelper->convertToOpenCartOrder($detailResult['order']);
                        
                        if ($convertResult['success']) {
                            // Siparişi kaydet
                            $orderId = $this->model_sale_order->addOrder($convertResult['order']);
                            
                            if ($orderId) {
                                // Hepsiburada sipariş mapping'ini kaydet
                                $this->saveOrderMapping($orderId, $hepsiburadaOrder['id'], json_encode($detailResult['order']));
                                $importedCount++;
                                $this->log->write('[SUCCESS] Hepsiburada siparişi içe aktarıldı: ' . $hepsiburadaOrder['id'] . ' -> OpenCart: ' . $orderId);
                            } else {
                                $errors[] = 'Sipariş kaydedilemedi: ' . $hepsiburadaOrder['id'];
                            }
                        } else {
                            $errors[] = 'Sipariş dönüştürülemedi: ' . $hepsiburadaOrder['id'];
                        }
                    } else {
                        $skippedCount++;
                        $this->log->write('[INFO] Hepsiburada siparişi zaten mevcut, atlandı: ' . $hepsiburadaOrder['id']);
                    }
                } else {
                    $errors[] = 'Sipariş detayı alınamadı: ' . $hepsiburadaOrder['id'];
                }
            }
            
            $this->log->write("[SUCCESS] Hepsiburada sipariş içe aktarma tamamlandı. İçe aktarılan: {$importedCount}, Atlanan: {$skippedCount}");
            
            return [
                'success' => true,
                'imported' => $importedCount,
                'skipped' => $skippedCount,
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Hepsiburada sipariş içe aktarma hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Hepsiburada kategorilerini çek ve cache'le
     * 
     * @return array Kategoriler
     */
    public function getCategories() {
        try {
            $this->log->write('[INFO] Hepsiburada kategorileri çekiliyor');
            
            // Cache kontrolü
            $cacheKey = 'hepsiburada_categories';
            $cached = $this->cache->get($cacheKey);
            
            if ($cached) {
                $this->log->write('[INFO] Hepsiburada kategorileri cache\'den alındı');
                return [
                    'success' => true,
                    'categories' => $cached,
                    'cached' => true
                ];
            }
            
            $hepsiburadaHelper = $this->getHepsiburadaHelper();
            $result = $hepsiburadaHelper->getCategories();
            
            if ($result['success']) {
                // Cache'le (24 saat)
                $this->cache->set($cacheKey, $result['categories'], 86400);
                $this->log->write('[SUCCESS] Hepsiburada kategorileri çekildi ve cache\'lendi: ' . count($result['categories']) . ' kategori');
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Hepsiburada kategori çekme hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Hepsiburada sipariş mapping tablosunu oluştur
     */
    public function createOrderMappingTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_order_mapping` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_order_id` int(11) NOT NULL,
            `hepsiburada_order_id` varchar(255) NOT NULL,
            `hepsiburada_data` text,
            `date_created` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `hepsiburada_order_id` (`hepsiburada_order_id`),
            KEY `opencart_order_id` (`opencart_order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        
        $this->db->query($sql);
        $this->log->write('[INFO] Hepsiburada sipariş mapping tablosu oluşturuldu/kontrol edildi');
    }
    
    /**
     * Hepsiburada ürün mapping tablosunu oluştur
     */
    public function createProductMappingTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_product_mapping` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_product_id` int(11) NOT NULL,
            `hepsiburada_sku` varchar(255),
            `hepsiburada_data` text,
            `status` enum('active','inactive','pending') DEFAULT 'pending',
            `date_created` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `opencart_product_id` (`opencart_product_id`),
            KEY `hepsiburada_sku` (`hepsiburada_sku`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        
        $this->db->query($sql);
        $this->log->write('[INFO] Hepsiburada ürün mapping tablosu oluşturuldu/kontrol edildi');
    }
    
    /**
     * Sipariş mapping'ini kaydet
     */
    private function saveOrderMapping($openCartOrderId, $hepsiburadaOrderId, $hepsiburadaData) {
        $this->createOrderMappingTable();
        
        $sql = "INSERT INTO `" . DB_PREFIX . "hepsiburada_order_mapping` 
                SET `opencart_order_id` = '" . (int)$openCartOrderId . "',
                    `hepsiburada_order_id` = '" . $this->db->escape($hepsiburadaOrderId) . "',
                    `hepsiburada_data` = '" . $this->db->escape($hepsiburadaData) . "',
                    `date_created` = NOW(),
                    `date_modified` = NOW()";
        
        $this->db->query($sql);
    }
    
    /**
     * Sipariş var mı kontrol et
     */
    private function orderExists($hepsiburadaOrderId) {
        $this->createOrderMappingTable();
        
        $query = $this->db->query("SELECT id FROM `" . DB_PREFIX . "hepsiburada_order_mapping` WHERE `hepsiburada_order_id` = '" . $this->db->escape($hepsiburadaOrderId) . "'");
        return $query->num_rows > 0;
    }
    
    /**
     * Hepsiburada sipariş istatistiklerini al
     */
    public function getOrderStats() {
        $this->createOrderMappingTable();
        
        $stats = [];
        
        // Toplam sipariş sayısı
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "hepsiburada_order_mapping`");
        $stats['total_orders'] = $query->row['total'];
        
        // Bu ay ki siparişler
        $query = $this->db->query("SELECT COUNT(*) as monthly FROM `" . DB_PREFIX . "hepsiburada_order_mapping` WHERE MONTH(`date_created`) = MONTH(NOW()) AND YEAR(`date_created`) = YEAR(NOW())");
        $stats['monthly_orders'] = $query->row['monthly'];
        
        // Bugünkü siparişler
        $query = $this->db->query("SELECT COUNT(*) as daily FROM `" . DB_PREFIX . "hepsiburada_order_mapping` WHERE DATE(`date_created`) = CURDATE()");
        $stats['daily_orders'] = $query->row['daily'];
        
        return $stats;
    }
    
    /**
     * Modül kurulum işlemleri
     */
    public function install() {
        $this->log->write('[INFO] Hepsiburada modülü kuruluyor');
        
        $this->createOrderMappingTable();
        $this->createProductMappingTable();
        
        // Create hepsiburada_products table for tracking Hepsiburada-specific product data
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_products` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `product_id` INT(11) NOT NULL,
            `hb_product_id` VARCHAR(255) DEFAULT NULL,
            `hb_sku` VARCHAR(255) DEFAULT NULL,
            `merchant_sku` VARCHAR(255) DEFAULT NULL,
            `barcode` VARCHAR(255) DEFAULT NULL,
            `category_id` VARCHAR(255) DEFAULT NULL,
            `price` DECIMAL(15,4) DEFAULT NULL,
            `list_price` DECIMAL(15,4) DEFAULT NULL,
            `sale_price` DECIMAL(15,4) DEFAULT NULL,
            `status` ENUM('pending', 'synced', 'error', 'rejected', 'approved') DEFAULT 'pending',
            `approval_status` ENUM('waiting', 'approved', 'rejected') DEFAULT 'waiting',
            `stock` INT(11) DEFAULT 0,
            `visibility` ENUM('visible', 'invisible', 'disabled') DEFAULT 'visible',
            `promotion_applied` TINYINT(1) DEFAULT 0,
            `commission_rate` DECIMAL(5,2) DEFAULT NULL,
            `cargo_company` VARCHAR(100) DEFAULT NULL,
            `preparation_time` INT(3) DEFAULT 1,
            `last_sync` DATETIME DEFAULT NULL,
            `last_price_update` DATETIME DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `product_id` (`product_id`),
            INDEX `hb_product_id` (`hb_product_id`),
            INDEX `status` (`status`),
            INDEX `approval_status` (`approval_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create hepsiburada_orders table for order management
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_orders` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) DEFAULT NULL,
            `hb_order_id` VARCHAR(255) NOT NULL,
            `hb_order_number` VARCHAR(255) DEFAULT NULL,
            `customer_id` INT(11) DEFAULT NULL,
            `status` ENUM('new', 'preparing', 'shipped', 'delivered', 'cancelled', 'returned') DEFAULT 'new',
            `payment_method` VARCHAR(100) DEFAULT NULL,
            `cargo_company` VARCHAR(100) DEFAULT NULL,
            `cargo_tracking_number` VARCHAR(255) DEFAULT NULL,
            `cargo_tracking_url` TEXT DEFAULT NULL,
            `total_amount` DECIMAL(15,4) DEFAULT NULL,
            `currency` VARCHAR(3) DEFAULT 'TRY',
            `commission_amount` DECIMAL(15,4) DEFAULT NULL,
            `shipping_amount` DECIMAL(15,4) DEFAULT NULL,
            `customer_first_name` VARCHAR(255) DEFAULT NULL,
            `customer_last_name` VARCHAR(255) DEFAULT NULL,
            `customer_email` VARCHAR(255) DEFAULT NULL,
            `customer_phone` VARCHAR(20) DEFAULT NULL,
            `shipping_address` TEXT DEFAULT NULL,
            `order_date` DATETIME DEFAULT NULL,
            `ship_by_date` DATETIME DEFAULT NULL,
            `estimated_delivery` DATETIME DEFAULT NULL,
            `cancel_reason` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `hb_order_id` (`hb_order_id`),
            INDEX `order_id` (`order_id`),
            INDEX `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create hepsiburada_order_items table for order line items
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_order_items` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `hb_order_id` VARCHAR(255) NOT NULL,
            `product_id` INT(11) DEFAULT NULL,
            `hb_product_id` VARCHAR(255) DEFAULT NULL,
            `merchant_sku` VARCHAR(255) DEFAULT NULL,
            `name` VARCHAR(500) DEFAULT NULL,
            `quantity` INT(11) DEFAULT 1,
            `price` DECIMAL(15,4) DEFAULT NULL,
            `total_amount` DECIMAL(15,4) DEFAULT NULL,
            `commission_amount` DECIMAL(15,4) DEFAULT NULL,
            `vat_rate` DECIMAL(5,2) DEFAULT NULL,
            `currency` VARCHAR(3) DEFAULT 'TRY',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `hb_order_id` (`hb_order_id`),
            INDEX `product_id` (`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create hepsiburada_categories table for category mapping
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_categories` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `hb_category_id` VARCHAR(255) NOT NULL,
            `opencart_category_id` INT(11) DEFAULT NULL,
            `name` VARCHAR(255) NOT NULL,
            `parent_id` VARCHAR(255) DEFAULT NULL,
            `level` INT(3) DEFAULT 0,
            `commission_rate` DECIMAL(5,2) DEFAULT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `attributes` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `hb_category_id` (`hb_category_id`),
            INDEX `opencart_category_id` (`opencart_category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create hepsiburada_promotions table for promotion management
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_promotions` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `promotion_id` VARCHAR(255) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `type` ENUM('discount', 'campaign', 'flash_sale', 'bundle') DEFAULT 'discount',
            `discount_type` ENUM('percentage', 'amount') DEFAULT 'percentage',
            `discount_value` DECIMAL(10,2) DEFAULT NULL,
            `min_quantity` INT(11) DEFAULT 1,
            `max_quantity` INT(11) DEFAULT NULL,
            `start_date` DATETIME DEFAULT NULL,
            `end_date` DATETIME DEFAULT NULL,
            `status` ENUM('active', 'inactive', 'expired') DEFAULT 'active',
            `applicable_categories` TEXT DEFAULT NULL,
            `applicable_products` TEXT DEFAULT NULL,
            `terms_conditions` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `promotion_id` (`promotion_id`),
            INDEX `status` (`status`),
            INDEX `start_date` (`start_date`),
            INDEX `end_date` (`end_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create hepsiburada_sync_log table for tracking sync operations
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_sync_log` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `operation_type` ENUM('product_sync', 'price_update', 'stock_update', 'order_sync', 'cargo_update', 'promotion_sync') NOT NULL,
            `product_id` INT(11) DEFAULT NULL,
            `status` ENUM('success', 'error', 'pending') NOT NULL,
            `message` TEXT DEFAULT NULL,
            `response_data` TEXT DEFAULT NULL,
            `execution_time` DECIMAL(8,3) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `operation_type` (`operation_type`),
            INDEX `status` (`status`),
            INDEX `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Insert default Turkish categories
        $this->addDefaultCategories();
        
        // Insert default cargo companies
        $this->addDefaultCargoCompanies();
        
        $this->log->write('[SUCCESS] Hepsiburada modülü başarıyla kuruldu');
    }
    
    /**
     * Modül kaldırma işlemleri
     */
    public function uninstall() {
        $this->log->write('[INFO] Hepsiburada modülü kaldırılıyor');
        
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hepsiburada_sync_log`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hepsiburada_promotions`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hepsiburada_order_items`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hepsiburada_orders`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hepsiburada_categories`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hepsiburada_products`");
        
        $this->log->write('[SUCCESS] Hepsiburada modülü başarıyla kaldırıldı');
    }
    
    /**
     * Add default Turkish categories
     */
    private function addDefaultCategories() {
        $categories = array(
            array('hb_category_id' => '18022298', 'name' => 'Elektronik', 'commission_rate' => 8.00),
            array('hb_category_id' => '18023006', 'name' => 'Moda', 'commission_rate' => 12.00),
            array('hb_category_id' => '18020000', 'name' => 'Ev & Yaşam', 'commission_rate' => 10.00),
            array('hb_category_id' => '18043490', 'name' => 'Spor & Outdoor', 'commission_rate' => 9.00),
            array('hb_category_id' => '18022372', 'name' => 'Otomotiv', 'commission_rate' => 7.00),
            array('hb_category_id' => '18023173', 'name' => 'Anne & Bebek', 'commission_rate' => 11.00),
            array('hb_category_id' => '18023329', 'name' => 'Kitap, Müzik, Film', 'commission_rate' => 15.00),
            array('hb_category_id' => '18022033', 'name' => 'Oyuncak', 'commission_rate' => 13.00),
            array('hb_category_id' => '18022518', 'name' => 'Kozmetik', 'commission_rate' => 14.00),
            array('hb_category_id' => '18023420', 'name' => 'Süpermarket', 'commission_rate' => 6.00)
        );
        
        foreach ($categories as $category) {
            $this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "hepsiburada_categories` 
                (`hb_category_id`, `name`, `commission_rate`) VALUES 
                ('" . $this->db->escape($category['hb_category_id']) . "', 
                 '" . $this->db->escape($category['name']) . "', 
                 " . (float)$category['commission_rate'] . ")");
        }
    }
    
    /**
     * Add default cargo companies
     */
    private function addDefaultCargoCompanies() {
        // This could be used to store cargo company preferences in the future
        $cargo_companies = array(
            array('code' => 'yurtici', 'name' => 'Yurtiçi Kargo', 'api_support' => 1),
            array('code' => 'mng', 'name' => 'MNG Kargo', 'api_support' => 1),
            array('code' => 'aras', 'name' => 'Aras Kargo', 'api_support' => 1),
            array('code' => 'ptt', 'name' => 'PTT Kargo', 'api_support' => 1),
            array('code' => 'ups', 'name' => 'UPS Kargo', 'api_support' => 0),
            array('code' => 'sendeo', 'name' => 'Sendeo', 'api_support' => 1)
        );
        
        // Log available cargo companies for reference
        $this->logSyncOperation('cargo_update', null, 'success', 'Default cargo companies initialized: ' . count($cargo_companies));
    }
    
    /**
     * Get products that need to be synced to Hepsiburada
     */
    public function getProductsForSync($limit = 100) {
        $query = $this->db->query("SELECT p.product_id, pd.name, p.model, p.sku, p.price, p.quantity, p.status,
                                         hp.id as hb_id, hp.status as hb_status, hp.last_sync, hp.approval_status
                                  FROM `" . DB_PREFIX . "product` p
                                  LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
                                  LEFT JOIN `" . DB_PREFIX . "hepsiburada_products` hp ON (p.product_id = hp.product_id)
                                  WHERE p.status = 1 
                                  AND (hp.status IS NULL OR hp.status = 'pending' OR hp.last_sync < DATE_SUB(NOW(), INTERVAL 12 HOUR))
                                  ORDER BY p.date_modified DESC
                                  LIMIT " . (int)$limit);
        
        return $query->rows;
    }
    
    /**
     * Get products that need price updates
     */
    public function getProductsForPriceUpdate($limit = 200) {
        $query = $this->db->query("SELECT p.product_id, pd.name, p.model, p.sku, p.price, 
                                         hp.price as hb_price, hp.hb_product_id, hp.last_price_update
                                  FROM `" . DB_PREFIX . "product` p
                                  LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
                                  INNER JOIN `" . DB_PREFIX . "hepsiburada_products` hp ON (p.product_id = hp.product_id)
                                  WHERE p.status = 1 
                                  AND hp.status = 'synced'
                                  AND hp.approval_status = 'approved'
                                  AND hp.hb_product_id IS NOT NULL
                                  AND (hp.price != p.price OR hp.last_price_update < DATE_SUB(NOW(), INTERVAL 4 HOUR))
                                  ORDER BY hp.last_price_update ASC
                                  LIMIT " . (int)$limit);
        
        return $query->rows;
    }
    
    /**
     * Get orders that need cargo tracking update
     */
    public function getOrdersForCargoUpdate($limit = 50) {
        $query = $this->db->query("SELECT ho.*, o.total
                                  FROM `" . DB_PREFIX . "hepsiburada_orders` ho
                                  LEFT JOIN `" . DB_PREFIX . "order` o ON (ho.order_id = o.order_id)
                                  WHERE ho.status = 'preparing'
                                  AND ho.cargo_tracking_number IS NOT NULL
                                  AND ho.cargo_tracking_number != ''
                                  ORDER BY ho.order_date ASC
                                  LIMIT " . (int)$limit);
        
        return $query->rows;
    }
    
    /**
     * Update product sync status
     */
    public function updateProductSyncStatus($product_id, $status, $hb_product_id = null) {
        $sql = "INSERT INTO `" . DB_PREFIX . "hepsiburada_products` (product_id, status, last_sync";
        
        if ($hb_product_id) {
            $sql .= ", hb_product_id";
        }
        
        $sql .= ") VALUES (" . (int)$product_id . ", '" . $this->db->escape($status) . "', NOW()";
        
        if ($hb_product_id) {
            $sql .= ", '" . $this->db->escape($hb_product_id) . "'";
        }
        
        $sql .= ") ON DUPLICATE KEY UPDATE status = '" . $this->db->escape($status) . "', last_sync = NOW()";
        
        if ($hb_product_id) {
            $sql .= ", hb_product_id = '" . $this->db->escape($hb_product_id) . "'";
        }
        
        $this->db->query($sql);
        
        // Log the operation
        $this->logSyncOperation('product_sync', $product_id, 'success', 'Product sync status updated to: ' . $status);
    }
    
    /**
     * Update product sync time
     */
    public function updateProductSyncTime($product_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . "hepsiburada_products` 
                         SET last_price_update = NOW() 
                         WHERE product_id = " . (int)$product_id);
    }
    
    /**
     * Update order cargo status
     */
    public function updateOrderCargoStatus($order_id, $status, $tracking_number = null, $tracking_url = null) {
        $sql = "UPDATE `" . DB_PREFIX . "hepsiburada_orders` 
                SET status = '" . $this->db->escape($status) . "'";
        
        if ($tracking_number) {
            $sql .= ", cargo_tracking_number = '" . $this->db->escape($tracking_number) . "'";
        }
        
        if ($tracking_url) {
            $sql .= ", cargo_tracking_url = '" . $this->db->escape($tracking_url) . "'";
        }
        
        $sql .= ", updated_at = NOW() WHERE order_id = " . (int)$order_id;
        
        $this->db->query($sql);
        
        // Log the operation
        $this->logSyncOperation('cargo_update', $order_id, 'success', 'Order cargo status updated to: ' . $status);
    }
    
    /**
     * Get dashboard metrics
     */
    public function getTotalProducts() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "hepsiburada_products`");
        return (int)$query->row['total'];
    }
    
    public function getSyncedProducts() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "hepsiburada_products` WHERE status = 'synced'");
        return (int)$query->row['total'];
    }
    
    public function getPendingProducts() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "hepsiburada_products` WHERE status = 'pending'");
        return (int)$query->row['total'];
    }
    
    public function getMonthlyOrders() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "hepsiburada_orders` 
                                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        return (int)$query->row['total'];
    }
    
    public function getMonthlyRevenue() {
        $query = $this->db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM `" . DB_PREFIX . "hepsiburada_orders` 
                                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status != 'cancelled'");
        return (float)$query->row['total'];
    }
    
    public function getLastSyncTime() {
        $query = $this->db->query("SELECT MAX(last_sync) as last_sync FROM `" . DB_PREFIX . "hepsiburada_products`");
        return $query->row['last_sync'];
    }
    
    public function getActivePromotions() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "hepsiburada_promotions` 
                                  WHERE status = 'active' AND start_date <= NOW() AND end_date >= NOW()");
        return (int)$query->row['total'];
    }
    
    public function getPendingShipments() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "hepsiburada_orders` 
                                  WHERE status = 'preparing'");
        return (int)$query->row['total'];
    }
    
    /**
     * Manage Hepsiburada orders
     */
    public function saveOrder($order_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . "hepsiburada_orders` 
                (hb_order_id, hb_order_number, status, payment_method, cargo_company, 
                 total_amount, currency, commission_amount, shipping_amount,
                 customer_first_name, customer_last_name, customer_email, customer_phone,
                 shipping_address, order_date, ship_by_date, estimated_delivery) 
                VALUES 
                ('" . $this->db->escape($order_data['order_id']) . "',
                 '" . $this->db->escape($order_data['order_number']) . "',
                 '" . $this->db->escape($order_data['status']) . "',
                 '" . $this->db->escape($order_data['payment_method']) . "',
                 '" . $this->db->escape($order_data['cargo_company']) . "',
                 " . (float)$order_data['total_amount'] . ",
                 '" . $this->db->escape($order_data['currency']) . "',
                 " . (float)$order_data['commission_amount'] . ",
                 " . (float)$order_data['shipping_amount'] . ",
                 '" . $this->db->escape($order_data['customer']['first_name']) . "',
                 '" . $this->db->escape($order_data['customer']['last_name']) . "',
                 '" . $this->db->escape($order_data['customer']['email']) . "',
                 '" . $this->db->escape($order_data['customer']['phone']) . "',
                 '" . $this->db->escape($order_data['shipping_address']) . "',
                 '" . $this->db->escape($order_data['order_date']) . "',
                 " . (isset($order_data['ship_by_date']) ? "'" . $this->db->escape($order_data['ship_by_date']) . "'" : "NULL") . ",
                 " . (isset($order_data['estimated_delivery']) ? "'" . $this->db->escape($order_data['estimated_delivery']) . "'" : "NULL") . ")
                ON DUPLICATE KEY UPDATE
                status = '" . $this->db->escape($order_data['status']) . "',
                total_amount = " . (float)$order_data['total_amount'] . ",
                commission_amount = " . (float)$order_data['commission_amount'] . ",
                shipping_amount = " . (float)$order_data['shipping_amount'] . ",
                updated_at = NOW()";
        
        $this->db->query($sql);
        
        // Save order items
        if (isset($order_data['items']) && is_array($order_data['items'])) {
            foreach ($order_data['items'] as $item) {
                $this->saveOrderItem($order_data['order_id'], $item);
            }
        }
    }
    
    /**
     * Save order item
     */
    private function saveOrderItem($hb_order_id, $item_data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "hepsiburada_order_items` 
                         (hb_order_id, hb_product_id, merchant_sku, name, quantity, price, total_amount, commission_amount, vat_rate, currency)
                         VALUES 
                         ('" . $this->db->escape($hb_order_id) . "',
                          '" . $this->db->escape($item_data['product_id']) . "',
                          '" . $this->db->escape($item_data['merchant_sku']) . "',
                          '" . $this->db->escape($item_data['product_name']) . "',
                          " . (int)$item_data['quantity'] . ",
                          " . (float)$item_data['price'] . ",
                          " . (float)$item_data['total_amount'] . ",
                          " . (float)$item_data['commission_amount'] . ",
                          " . (float)$item_data['vat_rate'] . ",
                          '" . $this->db->escape($item_data['currency']) . "')");
    }
    
    /**
     * Get categories
     */
    public function getCategories() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hepsiburada_categories` WHERE is_active = 1 ORDER BY name");
        return $query->rows;
    }
    
    /**
     * Update category mapping
     */
    public function updateCategoryMapping($hb_category_id, $opencart_category_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . "hepsiburada_categories` 
                         SET opencart_category_id = " . (int)$opencart_category_id . ", updated_at = NOW()
                         WHERE hb_category_id = '" . $this->db->escape($hb_category_id) . "'");
    }
    
    /**
     * Manage promotions
     */
    public function savePromotion($promotion_data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "hepsiburada_promotions` 
                         (promotion_id, name, type, discount_type, discount_value, min_quantity, max_quantity,
                          start_date, end_date, status, applicable_categories, applicable_products, terms_conditions)
                         VALUES 
                         ('" . $this->db->escape($promotion_data['id']) . "',
                          '" . $this->db->escape($promotion_data['name']) . "',
                          '" . $this->db->escape($promotion_data['type']) . "',
                          '" . $this->db->escape($promotion_data['discount_type']) . "',
                          " . (float)$promotion_data['discount_value'] . ",
                          " . (int)$promotion_data['min_quantity'] . ",
                          " . (isset($promotion_data['max_quantity']) ? (int)$promotion_data['max_quantity'] : "NULL") . ",
                          '" . $this->db->escape($promotion_data['start_date']) . "',
                          '" . $this->db->escape($promotion_data['end_date']) . "',
                          '" . $this->db->escape($promotion_data['status']) . "',
                          " . (isset($promotion_data['applicable_categories']) ? "'" . $this->db->escape(json_encode($promotion_data['applicable_categories'])) . "'" : "NULL") . ",
                          " . (isset($promotion_data['applicable_products']) ? "'" . $this->db->escape(json_encode($promotion_data['applicable_products'])) . "'" : "NULL") . ",
                          '" . $this->db->escape($promotion_data['terms_conditions']) . "')
                         ON DUPLICATE KEY UPDATE
                         name = '" . $this->db->escape($promotion_data['name']) . "',
                         status = '" . $this->db->escape($promotion_data['status']) . "',
                         updated_at = NOW()");
    }
    
    /**
     * Log sync operations for debugging and monitoring
     */
    public function logSyncOperation($operation_type, $product_id, $status, $message, $response_data = null, $execution_time = null) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "hepsiburada_sync_log` 
                         (operation_type, product_id, status, message, response_data, execution_time)
                         VALUES 
                         ('" . $this->db->escape($operation_type) . "',
                          " . ($product_id ? (int)$product_id : "NULL") . ",
                          '" . $this->db->escape($status) . "',
                          '" . $this->db->escape($message) . "',
                          " . ($response_data ? "'" . $this->db->escape(json_encode($response_data)) . "'" : "NULL") . ",
                          " . ($execution_time ? (float)$execution_time : "NULL") . ")");
    }
    
    /**
     * Get sync logs for monitoring
     */
    public function getSyncLogs($limit = 100, $operation_type = null) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "hepsiburada_sync_log`";
        
        if ($operation_type) {
            $sql .= " WHERE operation_type = '" . $this->db->escape($operation_type) . "'";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT " . (int)$limit;
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Clean old sync logs (keep last 30 days)
     */
    public function cleanSyncLogs() {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "hepsiburada_sync_log` WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    }
    
    /**
     * Get Hepsiburada product data
     */
    public function getHepsiburadaProduct($product_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hepsiburada_products` WHERE product_id = " . (int)$product_id);
        return $query->row;
    }
    
    /**
     * Update Hepsiburada product data
     */
    public function updateHepsiburadaProduct($product_id, $data) {
        $fields = array();
        
        foreach ($data as $key => $value) {
            if (in_array($key, array('hb_product_id', 'hb_sku', 'merchant_sku', 'barcode', 'category_id', 'status', 'approval_status', 'visibility', 'cargo_company'))) {
                $fields[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
            } elseif (in_array($key, array('price', 'list_price', 'sale_price', 'commission_rate'))) {
                $fields[] = "`" . $key . "` = " . (float)$value;
            } elseif (in_array($key, array('stock', 'preparation_time', 'promotion_applied'))) {
                $fields[] = "`" . $key . "` = " . (int)$value;
            }
        }
        
        if (!empty($fields)) {
            $sql = "INSERT INTO `" . DB_PREFIX . "hepsiburada_products` (product_id, " . implode(', ', array_keys($data)) . ") 
                    VALUES (" . (int)$product_id . ", " . implode(', ', array_values($data)) . ")
                    ON DUPLICATE KEY UPDATE " . implode(', ', $fields) . ", updated_at = NOW()";
            
            $this->db->query($sql);
        }
    }
} 