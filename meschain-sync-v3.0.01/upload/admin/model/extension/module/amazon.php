<?php
/**
 * amazon.php
 *
 * Amaç: Amazon modülünün OpenCart yönetici paneli (admin) tarafındaki model dosyasıdır. Veritabanı işlemleri burada tanımlanır.
 *
 * Loglama: Tüm önemli işlemler ve hatalar amazon_model.log dosyasına kaydedilmelidir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 *
 * Hata yönetimi: Hatalar loglanmalı ve kullanıcıya açıklayıcı mesaj gösterilmelidir.
 */

class ModelExtensionModuleAmazon extends Model {
    
    private $log;
    private $logFile = 'amazon_model.log';
    
    /**
     * Kurucu metod
     */
    public function __construct($registry) {
        parent::__construct($registry);
        $this->log = new Log($this->logFile);
    }
    
    /**
     * Amazon helper sınıfını yükle ve döndür
     * 
     * @return MeschainAmazonHelper
     */
    private function getAmazonHelper() {
        require_once(DIR_SYSTEM . 'library/meschain/helper/amazon.php');
        return new MeschainAmazonHelper($this->registry);
    }
    
    /**
     * Amazon ayarlarını kaydet
     * 
     * @param array $settings Ayarlar dizisi
     * @return bool Başarılı mı?
     */
    public function saveSettings($settings) {
        try {
            $this->log->write('[INFO] Amazon ayarları kaydediliyor');
            
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_amazon', $settings);
            
            $this->log->write('[SUCCESS] Amazon ayarları başarıyla kaydedildi');
            return true;
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Amazon ayarları kaydedilemedi: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Amazon bağlantısını test et
     * 
     * @return array Test sonucu
     */
    public function testConnection() {
        try {
            $this->log->write('[INFO] Amazon bağlantı testi başlatılıyor');
            
            $amazonHelper = $this->getAmazonHelper();
            $result = $amazonHelper->testConnection();
            
            if ($result['success']) {
                $this->log->write('[SUCCESS] Amazon bağlantı testi başarılı');
            } else {
                $this->log->write('[ERROR] Amazon bağlantı testi başarısız: ' . $result['message']);
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Amazon bağlantı testi exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Amazon siparişlerini çek ve veritabanına kaydet
     * 
     * @param array $params Filtre parametreleri
     * @return array Sonuç
     */
    public function importOrders($params = []) {
        try {
            $this->log->write('[INFO] Amazon siparişleri içe aktarılıyor');
            
            $amazonHelper = $this->getAmazonHelper();
            $ordersResult = $amazonHelper->getOrders($params);
            
            if (!$ordersResult['success']) {
                return $ordersResult;
            }
            
            $this->load->model('sale/order');
            $importedCount = 0;
            $skippedCount = 0;
            $errors = [];
            
            foreach ($ordersResult['orders'] as $amazonOrder) {
                // Sipariş detayını al
                $detailResult = $amazonHelper->getOrderDetail($amazonOrder['AmazonOrderId']);
                
                if ($detailResult['success']) {
                    // Sipariş zaten var mı kontrol et
                    if (!$this->orderExists($amazonOrder['AmazonOrderId'])) {
                        // OpenCart formatına dönüştür
                        $convertResult = $amazonHelper->convertToOpenCartOrder($detailResult['order']);
                        
                        if ($convertResult['success']) {
                            // Siparişi kaydet
                            $orderId = $this->model_sale_order->addOrder($convertResult['order']);
                            
                            if ($orderId) {
                                // Amazon sipariş mapping'ini kaydet
                                $this->saveOrderMapping($orderId, $amazonOrder['AmazonOrderId'], json_encode($detailResult['order']));
                                $importedCount++;
                                $this->log->write('[SUCCESS] Amazon siparişi içe aktarıldı: ' . $amazonOrder['AmazonOrderId'] . ' -> OpenCart: ' . $orderId);
                            } else {
                                $errors[] = 'Sipariş kaydedilemedi: ' . $amazonOrder['AmazonOrderId'];
                            }
                        } else {
                            $errors[] = 'Sipariş dönüştürülemedi: ' . $amazonOrder['AmazonOrderId'];
                        }
                    } else {
                        $skippedCount++;
                        $this->log->write('[INFO] Amazon siparişi zaten mevcut, atlandı: ' . $amazonOrder['AmazonOrderId']);
                    }
                } else {
                    $errors[] = 'Sipariş detayı alınamadı: ' . $amazonOrder['AmazonOrderId'];
                }
            }
            
            $this->log->write("[SUCCESS] Amazon sipariş içe aktarma tamamlandı. İçe aktarılan: {$importedCount}, Atlanan: {$skippedCount}");
            
            return [
                'success' => true,
                'imported' => $importedCount,
                'skipped' => $skippedCount,
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Amazon sipariş içe aktarma hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Amazon ürünlerini çek
     * 
     * @param array $params Arama parametreleri
     * @return array Ürünler
     */
    public function searchProducts($params = []) {
        try {
            $this->log->write('[INFO] Amazon ürünleri aranıyor');
            
            $amazonHelper = $this->getAmazonHelper();
            $result = $amazonHelper->getProducts($params);
            
            if ($result['success']) {
                $this->log->write('[SUCCESS] Amazon ürünleri bulundu: ' . count($result['products']));
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Amazon ürün arama hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Amazon stok güncelle
     * 
     * @param array $updates Stok güncellemeleri
     * @return array Sonuç
     */
    public function updateInventory($updates) {
        try {
            $this->log->write('[INFO] Amazon stok güncelleniyor: ' . count($updates) . ' ürün');
            
            $amazonHelper = $this->getAmazonHelper();
            $result = $amazonHelper->updateInventory($updates);
            
            if ($result['success']) {
                $this->log->write('[SUCCESS] Amazon stok güncellendi');
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Amazon stok güncelleme hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Amazon fiyat güncelle
     * 
     * @param array $updates Fiyat güncellemeleri
     * @return array Sonuç
     */
    public function updatePrices($updates) {
        try {
            $this->log->write('[INFO] Amazon fiyatları güncelleniyor: ' . count($updates) . ' ürün');
            
            $amazonHelper = $this->getAmazonHelper();
            $result = $amazonHelper->updatePrices($updates);
            
            if ($result['success']) {
                $this->log->write('[SUCCESS] Amazon fiyatları güncellendi');
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Amazon fiyat güncelleme hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Amazon sipariş mapping tablosunu oluştur
     */
    public function createOrderMappingTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "amazon_order_mapping` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_order_id` int(11) NOT NULL,
            `amazon_order_id` varchar(255) NOT NULL,
            `amazon_data` text,
            `date_created` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `amazon_order_id` (`amazon_order_id`),
            KEY `opencart_order_id` (`opencart_order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        
        $this->db->query($sql);
        $this->log->write('[INFO] Amazon sipariş mapping tablosu oluşturuldu/kontrol edildi');
    }
    
    /**
     * Amazon ürün mapping tablosunu oluştur
     */
    public function createProductMappingTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "amazon_product_mapping` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_product_id` int(11) NOT NULL,
            `amazon_sku` varchar(255),
            `amazon_asin` varchar(255),
            `amazon_data` text,
            `status` enum('active','inactive','pending') DEFAULT 'pending',
            `date_created` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `opencart_product_id` (`opencart_product_id`),
            KEY `amazon_sku` (`amazon_sku`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        
        $this->db->query($sql);
        $this->log->write('[INFO] Amazon ürün mapping tablosu oluşturuldu/kontrol edildi');
    }
    
    /**
     * Sipariş mapping'ini kaydet
     */
    private function saveOrderMapping($openCartOrderId, $amazonOrderId, $amazonData) {
        $this->createOrderMappingTable();
        
        $sql = "INSERT INTO `" . DB_PREFIX . "amazon_order_mapping` 
                SET `opencart_order_id` = '" . (int)$openCartOrderId . "',
                    `amazon_order_id` = '" . $this->db->escape($amazonOrderId) . "',
                    `amazon_data` = '" . $this->db->escape($amazonData) . "',
                    `date_created` = NOW(),
                    `date_modified` = NOW()";
        
        $this->db->query($sql);
    }
    
    /**
     * Amazon sipariş istatistiklerini al
     */
    public function getOrderStats() {
        $this->createOrderMappingTable();
        
        $stats = [];
        
        // Toplam sipariş sayısı
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "amazon_order_mapping`");
        $stats['total_orders'] = $query->row['total'];
        
        // Bu ay ki siparişler
        $query = $this->db->query("SELECT COUNT(*) as monthly FROM `" . DB_PREFIX . "amazon_order_mapping` WHERE MONTH(`date_created`) = MONTH(NOW()) AND YEAR(`date_created`) = YEAR(NOW())");
        $stats['monthly_orders'] = $query->row['monthly'];
        
        // Bugünkü siparişler
        $query = $this->db->query("SELECT COUNT(*) as daily FROM `" . DB_PREFIX . "amazon_order_mapping` WHERE DATE(`date_created`) = CURDATE()");
        $stats['daily_orders'] = $query->row['daily'];
        
        return $stats;
    }
    
    /**
     * Amazon ürün istatistiklerini al
     */
    public function getProductStats() {
        $this->createProductMappingTable();
        
        $stats = [];
        
        // Toplam eşleşen ürün
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "amazon_product_mapping`");
        $stats['total_products'] = $query->row['total'];
        
        // Aktif ürünler
        $query = $this->db->query("SELECT COUNT(*) as active FROM `" . DB_PREFIX . "amazon_product_mapping` WHERE `status` = 'active'");
        $stats['active_products'] = $query->row['active'];
        
        // Bekleyen ürünler
        $query = $this->db->query("SELECT COUNT(*) as pending FROM `" . DB_PREFIX . "amazon_product_mapping` WHERE `status` = 'pending'");
        $stats['pending_products'] = $query->row['pending'];
        
        return $stats;
    }
    
    /**
     * Veritabanı tablolarını oluştur
     */
    public function install() {
        // Amazon siparişleri tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "amazon_orders` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `amazon_order_id` varchar(50) NOT NULL,
            `purchase_date` datetime NOT NULL,
            `last_update_date` datetime DEFAULT NULL,
            `order_status` varchar(50) NOT NULL,
            `fulfillment_channel` varchar(20) DEFAULT NULL,
            `buyer_email` varchar(100) DEFAULT NULL,
            `buyer_name` varchar(100) DEFAULT NULL,
            `marketplace_id` varchar(50) NOT NULL,
            `number_of_items_shipped` int(11) DEFAULT 0,
            `number_of_items_unshipped` int(11) DEFAULT 0,
            `order_total_amount` decimal(10,2) DEFAULT 0.00,
            `order_total_currency_code` varchar(3) DEFAULT 'EUR',
            `is_business_order` tinyint(1) DEFAULT 0,
            `is_prime` tinyint(1) DEFAULT 0,
            `is_premium_order` tinyint(1) DEFAULT 0,
            `order_data` text,
            `opencart_order_id` int(11) DEFAULT NULL,
            `date_created` timestamp DEFAULT CURRENT_TIMESTAMP,
            `date_modified` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `amazon_order_id` (`amazon_order_id`),
            KEY `idx_order_status` (`order_status`),
            KEY `idx_purchase_date` (`purchase_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Amazon ürünleri tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "amazon_products` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `amazon_sku` varchar(100) NOT NULL,
            `amazon_asin` varchar(20) DEFAULT NULL,
            `amazon_title` varchar(500) DEFAULT NULL,
            `amazon_price` decimal(10,2) DEFAULT 0.00,
            `amazon_quantity` int(11) DEFAULT 0,
            `marketplace_id` varchar(50) NOT NULL,
            `feed_id` varchar(50) DEFAULT NULL,
            `sync_status` enum('pending','synced','error','disabled') DEFAULT 'pending',
            `last_sync_date` datetime DEFAULT NULL,
            `sync_error` text,
            `is_active` tinyint(1) DEFAULT 1,
            `date_created` timestamp DEFAULT CURRENT_TIMESTAMP,
            `date_modified` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `amazon_sku` (`amazon_sku`),
            KEY `idx_product_id` (`product_id`),
            KEY `idx_sync_status` (`sync_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        $this->writeLog('system', 'KURULUM', 'Amazon modülü veritabanı tabloları oluşturuldu.');
    }
    
    /**
     * Modül kaldırılırken tabloları sil
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "amazon_products`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "amazon_orders`");
        
        $this->writeLog('system', 'KALDIRMA', 'Amazon modülü veritabanı tabloları silindi.');
    }
    
    /**
     * Sipariş var mı kontrol et
     */
    public function orderExists($amazon_order_id) {
        $query = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "amazon_orders` WHERE `amazon_order_id` = '" . $this->db->escape($amazon_order_id) . "'");
        return $query->row['count'] > 0;
    }
    
    /**
     * Amazon siparişi ekle
     */
    public function addOrder($order_data) {
        if ($this->orderExists($order_data['AmazonOrderId'])) {
            $this->writeLog('model', 'SIPARIS_EKLEME', 'Sipariş zaten mevcut: ' . $order_data['AmazonOrderId']);
            return false;
        }
        
        $sql = "INSERT INTO `" . DB_PREFIX . "amazon_orders` SET
            `amazon_order_id` = '" . $this->db->escape($order_data['AmazonOrderId']) . "',
            `purchase_date` = '" . $this->db->escape(date('Y-m-d H:i:s', strtotime($order_data['PurchaseDate']))) . "',
            `order_status` = '" . $this->db->escape($order_data['OrderStatus']) . "',
            `fulfillment_channel` = '" . $this->db->escape($order_data['FulfillmentChannel'] ?? 'MFN') . "',
            `buyer_email` = '" . $this->db->escape($order_data['BuyerInfo']['BuyerEmail'] ?? '') . "',
            `buyer_name` = '" . $this->db->escape($order_data['BuyerInfo']['BuyerName'] ?? '') . "',
            `marketplace_id` = '" . $this->db->escape($order_data['MarketplaceId']) . "',
            `number_of_items_shipped` = " . (int)($order_data['NumberOfItemsShipped'] ?? 0) . ",
            `number_of_items_unshipped` = " . (int)($order_data['NumberOfItemsUnshipped'] ?? 0) . ",
            `order_total_amount` = " . (float)($order_data['OrderTotal']['Amount'] ?? 0) . ",
            `order_total_currency_code` = '" . $this->db->escape($order_data['OrderTotal']['CurrencyCode'] ?? 'EUR') . "',
            `is_business_order` = " . ((isset($order_data['IsBusinessOrder']) && $order_data['IsBusinessOrder']) ? 1 : 0) . ",
            `is_prime` = " . ((isset($order_data['IsPrime']) && $order_data['IsPrime']) ? 1 : 0) . ",
            `is_premium_order` = " . ((isset($order_data['IsPremiumOrder']) && $order_data['IsPremiumOrder']) ? 1 : 0) . ",
            `order_data` = '" . $this->db->escape(json_encode($order_data)) . "'";
        
        $this->db->query($sql);
        $order_id = $this->db->getLastId();
        
        $this->writeLog('model', 'SIPARIS_EKLEME', 'Amazon siparişi eklendi: ' . $order_data['AmazonOrderId']);
        
        return $order_id;
    }
    
    /**
     * Sipariş detaylarını güncelle
     */
    public function updateOrderDetails($amazon_order_id, $order_data) {
        $sql = "UPDATE `" . DB_PREFIX . "amazon_orders` SET
            `last_update_date` = '" . $this->db->escape(date('Y-m-d H:i:s', strtotime($order_data['LastUpdateDate'] ?? 'now'))) . "',
            `order_status` = '" . $this->db->escape($order_data['OrderStatus']) . "',
            `number_of_items_shipped` = " . (int)($order_data['NumberOfItemsShipped'] ?? 0) . ",
            `number_of_items_unshipped` = " . (int)($order_data['NumberOfItemsUnshipped'] ?? 0) . ",
            `order_data` = '" . $this->db->escape(json_encode($order_data)) . "'
            WHERE `amazon_order_id` = '" . $this->db->escape($amazon_order_id) . "'";
        
        $this->db->query($sql);
        
        $this->writeLog('model', 'SIPARIS_GUNCELLEME', 'Amazon siparişi güncellendi: ' . $amazon_order_id);
    }
    
    /**
     * Siparişi OpenCart ile ilişkilendir
     */
    public function linkOrderToOpencart($amazon_order_id, $opencart_order_id) {
        $sql = "UPDATE `" . DB_PREFIX . "amazon_orders` SET
            `opencart_order_id` = " . (int)$opencart_order_id . "
            WHERE `amazon_order_id` = '" . $this->db->escape($amazon_order_id) . "'";
        
        $this->db->query($sql);
        
        $this->writeLog('model', 'SIPARIS_ILISKILENDIRME', 'Amazon siparişi #' . $amazon_order_id . ' OpenCart siparişi #' . $opencart_order_id . ' ile ilişkilendirildi');
    }
    
    /**
     * Sipariş getir
     */
    public function getOrder($order_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "amazon_orders` WHERE `id` = " . (int)$order_id);
        return $query->row;
    }
    
    /**
     * Siparişleri getir
     */
    public function getOrders($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "amazon_orders` WHERE 1=1";
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND `order_status` = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        $sql .= " ORDER BY `purchase_date` DESC";
        
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
     * Toplam sipariş sayısı
     */
    public function getTotalOrders($data = array()) {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "amazon_orders` WHERE 1=1";
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND `order_status` = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        $query = $this->db->query($sql);
        
        return $query->row['total'];
    }
    
    /**
     * OpenCart ürün ID'sine göre Amazon ürünü getir
     */
    public function getProductByOpencartId($product_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "amazon_products` WHERE `product_id` = " . (int)$product_id);
        return $query->row;
    }
    
    /**
     * Amazon ürünü ekle
     */
    public function addProduct($data) {
        $sql = "INSERT INTO `" . DB_PREFIX . "amazon_products` SET
            `product_id` = " . (int)$data['product_id'] . ",
            `amazon_sku` = '" . $this->db->escape($data['amazon_sku']) . "',
            `amazon_asin` = '" . $this->db->escape($data['amazon_asin'] ?? '') . "',
            `amazon_title` = '" . $this->db->escape($data['amazon_title'] ?? '') . "',
            `amazon_price` = " . (float)($data['amazon_price'] ?? 0) . ",
            `amazon_quantity` = " . (int)($data['amazon_quantity'] ?? 0) . ",
            `marketplace_id` = '" . $this->db->escape($data['marketplace_id'] ?? '') . "',
            `feed_id` = '" . $this->db->escape($data['feed_id'] ?? '') . "',
            `sync_status` = '" . $this->db->escape($data['sync_status'] ?? 'pending') . "',
            `is_active` = " . (isset($data['is_active']) ? (int)$data['is_active'] : 1);
        
        $this->db->query($sql);
        $product_id = $this->db->getLastId();
        
        $this->writeLog('model', 'URUN_EKLEME', 'Amazon ürünü eklendi: ' . $data['amazon_sku']);
        
        return $product_id;
    }
    
    /**
     * Amazon ürünü güncelle
     */
    public function updateProduct($id, $data) {
        $sql = "UPDATE `" . DB_PREFIX . "amazon_products` SET ";
        $update_fields = array();
        
        if (isset($data['amazon_price'])) {
            $update_fields[] = "`amazon_price` = " . (float)$data['amazon_price'];
        }
        
        if (isset($data['amazon_quantity'])) {
            $update_fields[] = "`amazon_quantity` = " . (int)$data['amazon_quantity'];
        }
        
        if (isset($data['sync_status'])) {
            $update_fields[] = "`sync_status` = '" . $this->db->escape($data['sync_status']) . "'";
        }
        
        if (isset($data['last_sync_date'])) {
            $update_fields[] = "`last_sync_date` = '" . $this->db->escape($data['last_sync_date']) . "'";
        }
        
        if (!empty($update_fields)) {
            $sql .= implode(', ', $update_fields);
            $sql .= " WHERE `id` = " . (int)$id;
            
            $this->db->query($sql);
            
            $this->writeLog('model', 'URUN_GUNCELLEME', 'Amazon ürünü güncellendi: ID #' . $id);
        }
    }
    
    /**
     * Amazon ürünlerini getir
     */
    public function getProducts($data = array()) {
        $sql = "SELECT ap.*, pd.name as product_name 
                FROM `" . DB_PREFIX . "amazon_products` ap
                LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (ap.product_id = pd.product_id AND pd.language_id = " . (int)$this->config->get('config_language_id') . ")
                WHERE 1=1";
        
        if (!empty($data['sync_status'])) {
            $sql .= " AND ap.`sync_status` = '" . $this->db->escape($data['sync_status']) . "'";
        }
        
        $sql .= " ORDER BY ap.`date_created` DESC";
        
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
     * Log dosyasına yaz
     */
    private function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'amazon_model.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 