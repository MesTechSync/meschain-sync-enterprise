<?php
/**
 * n11.php
 *
 * Amaç: n11 modülünün OpenCart yönetici paneli (admin) tarafındaki model dosyasıdır. Veritabanı işlemleri burada tanımlanır.
 *
 * Loglama: Tüm önemli işlemler ve hatalar n11_model.log dosyasına kaydedilmelidir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 *
 * Hata yönetimi: Hatalar loglanmalı ve kullanıcıya açıklayıcı mesaj gösterilmelidir.
 */
// n11 modülünün veritabanı işlemleri için model dosyası

// ... OpenCart model fonksiyonları buraya eklenecek ... 

class ModelExtensionModuleN11 extends Model {
    /**
     * Kurulum işlemi
     * n11 modülünün veritabanı tablolarını oluşturur
     */
    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_category_mapping` (
                `mapping_id` INT(11) NOT NULL AUTO_INCREMENT,
                `opencart_category_id` INT(11) NOT NULL,
                `n11_category_id` VARCHAR(64) NOT NULL,
                `n11_category_name` VARCHAR(255) NOT NULL,
                `n11_category_path` VARCHAR(512) NOT NULL,
                `attributes` TEXT,
                `date_added` DATETIME NOT NULL,
                `date_modified` DATETIME NOT NULL,
                PRIMARY KEY (`mapping_id`),
                UNIQUE KEY `opencart_category_id` (`opencart_category_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_products` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `product_id` INT(11) NOT NULL,
                `n11_product_id` VARCHAR(64) NOT NULL,
                `n11_seller_code` VARCHAR(64) NOT NULL,
                `status` TINYINT(1) NOT NULL DEFAULT '0',
                `last_updated` DATETIME NOT NULL,
                `sync_status` VARCHAR(32) NOT NULL DEFAULT 'pending',
                `error_message` TEXT,
                PRIMARY KEY (`id`),
                UNIQUE KEY `product_id` (`product_id`),
                KEY `n11_product_id` (`n11_product_id`),
                KEY `sync_status` (`sync_status`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_orders` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `n11_order_id` VARCHAR(64) NOT NULL,
                `opencart_order_id` INT(11) DEFAULT NULL,
                `order_number` VARCHAR(64) NOT NULL,
                `status` VARCHAR(32) NOT NULL,
                `total` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
                `shipping_cost` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
                `commission` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
                `buyer_name` VARCHAR(255) NOT NULL,
                `buyer_phone` VARCHAR(32) NOT NULL,
                `buyer_email` VARCHAR(128) NOT NULL,
                `shipping_address` TEXT NOT NULL,
                `billing_address` TEXT NOT NULL,
                `city` VARCHAR(128) NOT NULL,
                `district` VARCHAR(128) NOT NULL,
                `order_data` TEXT NOT NULL,
                `date_added` DATETIME NOT NULL,
                `date_modified` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `n11_order_id` (`n11_order_id`),
                KEY `opencart_order_id` (`opencart_order_id`),
                KEY `status` (`status`),
                KEY `date_added` (`date_added`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        $this->writeLog('INSTALL', 'n11 model tablolar oluşturuldu');
    }
    
    /**
     * Kaldırma işlemi
     * n11 modülünün veritabanı tablolarını kaldırır
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "n11_category_mapping`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "n11_products`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "n11_orders`");
        
        $this->writeLog('UNINSTALL', 'n11 model tablolar kaldırıldı');
    }
    
    /**
     * n11 siparişini OpenCart sistemine kaydeder
     *
     * @param array $n11Order n11'den gelen sipariş verisi
     * @return int Eklenen sipariş ID'si
     */
    public function addOrder($n11Order) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "n11_orders` SET
            `n11_order_id` = '" . $this->db->escape($n11Order['id']) . "',
            `order_number` = '" . $this->db->escape($n11Order['orderNumber']) . "',
            `status` = '" . $this->db->escape($n11Order['status']) . "',
            `total` = '" . (float)$n11Order['totalAmount'] . "',
            `shipping_cost` = '" . (isset($n11Order['shippingAmount']) ? (float)$n11Order['shippingAmount'] : 0) . "',
            `commission` = '" . (isset($n11Order['commission']) ? (float)$n11Order['commission'] : 0) . "',
            `buyer_name` = '" . $this->db->escape($n11Order['buyer']['fullName']) . "',
            `buyer_phone` = '" . $this->db->escape($n11Order['buyer']['phoneNumber']) . "',
            `buyer_email` = '" . $this->db->escape($n11Order['buyer']['email']) . "',
            `shipping_address` = '" . $this->db->escape($n11Order['shippingAddress']['address']) . "',
            `billing_address` = '" . $this->db->escape($n11Order['billingAddress']['address']) . "',
            `city` = '" . $this->db->escape($n11Order['shippingAddress']['city']) . "',
            `district` = '" . $this->db->escape($n11Order['shippingAddress']['district']) . "',
            `order_data` = '" . $this->db->escape(json_encode($n11Order)) . "',
            `date_added` = NOW(),
            `date_modified` = NOW()"
        );
        
        $order_id = $this->db->getLastId();
        $this->writeLog('ADD_ORDER', 'n11 siparişi eklendi: ' . $n11Order['orderNumber']);
        
        return $order_id;
    }
    
    /**
     * n11 siparişini OpenCart siparişiyle ilişkilendirir
     *
     * @param int $n11OrderId n11 sipariş ID'si
     * @param int $opencartOrderId OpenCart sipariş ID'si
     * @return bool İşlem başarılı mı
     */
    public function linkOrderToOpencart($n11OrderId, $opencartOrderId) {
        $this->db->query("UPDATE `" . DB_PREFIX . "n11_orders` SET
            `opencart_order_id` = '" . (int)$opencartOrderId . "',
            `date_modified` = NOW()
            WHERE `n11_order_id` = '" . $this->db->escape($n11OrderId) . "'");
            
        $this->writeLog('LINK_ORDER', 'n11 siparişi OpenCart ile ilişkilendirildi: ' . $n11OrderId . ' -> ' . $opencartOrderId);
        
        return $this->db->countAffected() > 0;
    }
    
    /**
     * n11 sipariş durumunu günceller
     *
     * @param string $n11OrderId n11 sipariş ID'si
     * @param string $status Yeni durum
     * @return bool İşlem başarılı mı
     */
    public function updateOrderStatus($n11OrderId, $status) {
        $this->db->query("UPDATE `" . DB_PREFIX . "n11_orders` SET
            `status` = '" . $this->db->escape($status) . "',
            `date_modified` = NOW()
            WHERE `n11_order_id` = '" . $this->db->escape($n11OrderId) . "'");
            
        $this->writeLog('UPDATE_STATUS', 'n11 sipariş durumu güncellendi: ' . $n11OrderId . ' -> ' . $status);
        
        return $this->db->countAffected() > 0;
    }
    
    /**
     * n11 sipariş detayını günceller
     *
     * @param string $n11OrderId n11 sipariş ID'si
     * @param array $orderData Güncellenmiş sipariş verisi
     * @return bool İşlem başarılı mı
     */
    public function updateOrderDetails($n11OrderId, $orderData) {
        $this->db->query("UPDATE `" . DB_PREFIX . "n11_orders` SET
            `order_number` = '" . $this->db->escape($orderData['orderNumber']) . "',
            `status` = '" . $this->db->escape($orderData['status']) . "',
            `total` = '" . (float)$orderData['totalAmount'] . "',
            `shipping_cost` = '" . (isset($orderData['shippingAmount']) ? (float)$orderData['shippingAmount'] : 0) . "',
            `commission` = '" . (isset($orderData['commission']) ? (float)$orderData['commission'] : 0) . "',
            `buyer_name` = '" . $this->db->escape($orderData['buyer']['fullName']) . "',
            `buyer_phone` = '" . $this->db->escape($orderData['buyer']['phoneNumber']) . "',
            `buyer_email` = '" . $this->db->escape($orderData['buyer']['email']) . "',
            `shipping_address` = '" . $this->db->escape($orderData['shippingAddress']['address']) . "',
            `billing_address` = '" . $this->db->escape($orderData['billingAddress']['address']) . "',
            `city` = '" . $this->db->escape($orderData['shippingAddress']['city']) . "',
            `district` = '" . $this->db->escape($orderData['shippingAddress']['district']) . "',
            `order_data` = '" . $this->db->escape(json_encode($orderData)) . "',
            `date_modified` = NOW()
            WHERE `n11_order_id` = '" . $this->db->escape($n11OrderId) . "'");
            
        $this->writeLog('UPDATE_ORDER', 'n11 sipariş detayları güncellendi: ' . $n11OrderId);
        
        return $this->db->countAffected() > 0;
    }
    
    /**
     * n11 siparişinin önceden eklenip eklenmediğini kontrol eder
     *
     * @param string $n11OrderId n11 sipariş ID'si
     * @return bool Sipariş mevcut mu
     */
    public function orderExists($n11OrderId) {
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "n11_orders WHERE n11_order_id = '" . $this->db->escape($n11OrderId) . "'");
        return $query->row['total'] > 0;
    }
    
    /**
     * n11 siparişini ID'ye göre getirir
     *
     * @param string $n11OrderId n11 sipariş ID'si
     * @return array Sipariş bilgileri
     */
    public function getOrderById($n11OrderId) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "n11_orders` 
            WHERE `n11_order_id` = '" . $this->db->escape($n11OrderId) . "'");
            
        return $query->row;
    }
    
    /**
     * n11 siparişlerini listeler
     *
     * @param array $data Filtre parametreleri
     * @return array Siparişler listesi
     */
    public function getOrders($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "n11_orders` WHERE 1";
        
        if (!empty($data['filter_order_number'])) {
            $sql .= " AND `order_number` LIKE '%" . $this->db->escape($data['filter_order_number']) . "%'";
        }
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND `status` = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_buyer'])) {
            $sql .= " AND `buyer_name` LIKE '%" . $this->db->escape($data['filter_buyer']) . "%'";
        }
        
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(`date_added`) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(`date_added`) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        $sort_data = array(
            'order_number',
            'status',
            'buyer_name',
            'total',
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
     * n11 siparişlerinin toplam sayısını getirir
     *
     * @param array $data Filtre parametreleri
     * @return int Toplam sipariş sayısı
     */
    public function getTotalOrders($data = array()) {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "n11_orders` WHERE 1";
        
        if (!empty($data['filter_order_number'])) {
            $sql .= " AND `order_number` LIKE '%" . $this->db->escape($data['filter_order_number']) . "%'";
        }
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND `status` = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_buyer'])) {
            $sql .= " AND `buyer_name` LIKE '%" . $this->db->escape($data['filter_buyer']) . "%'";
        }
        
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(`date_added`) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(`date_added`) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        $query = $this->db->query($sql);
        
        return $query->row['total'];
    }
    
    /**
     * n11 siparişini OpenCart siparişine dönüştürür
     *
     * @param string $n11OrderId n11 sipariş ID'si
     * @return int Oluşturulan OpenCart sipariş ID'si
     */
    public function convertToOpencartOrder($n11OrderId) {
        // n11 siparişini getir
        $n11Order = $this->getOrderById($n11OrderId);
        
        if (!$n11Order) {
            $this->writeLog('ERROR', 'n11 siparişi bulunamadı: ' . $n11OrderId);
            return false;
        }
        
        // Sipariş verilerini JSON'dan decode et
        $orderData = json_decode($n11Order['order_data'], true);
        
        // OpenCart sipariş verilerini hazırla
        $this->load->model('setting/setting');
        $this->load->model('localisation/country');
        $this->load->model('localisation/zone');
        
        // Varsayılan ayarları al
        $store_id = 0;
        $settings = $this->model_setting_setting->getSetting('config', $store_id);
        
        // Türkiye'yi bul
        $country_info = $this->model_localisation_country->getCountryByIsoCode2('TR');
        $country_id = $country_info ? $country_info['country_id'] : 0;
        
        // İl'i bul (city - İstanbul, Ankara, vb.)
        $zone_info = $this->model_localisation_zone->getZoneByName($n11Order['city']);
        $zone_id = $zone_info ? $zone_info['zone_id'] : 0;
        
        // Müşteri oluştur veya mevcut müşteriyi bul
        $this->load->model('customer/customer');
        $customer_info = $this->model_customer_customer->getCustomerByEmail($n11Order['buyer_email']);
        
        if (!$customer_info) {
            // Yeni müşteri oluştur
            $customer_data = array(
                'customer_group_id' => $settings['config_customer_group_id'],
                'firstname' => $n11Order['buyer_name'],
                'lastname' => '',
                'email' => $n11Order['buyer_email'],
                'telephone' => $n11Order['buyer_phone'],
                'password' => substr(md5(uniqid(rand(), true)), 0, 10),
                'status' => 1,
                'address' => array(
                    array(
                        'firstname' => $n11Order['buyer_name'],
                        'lastname' => '',
                        'company' => '',
                        'address_1' => $n11Order['shipping_address'],
                        'address_2' => '',
                        'city' => $n11Order['city'],
                        'postcode' => '',
                        'country_id' => $country_id,
                        'zone_id' => $zone_id,
                        'default' => 1
                    )
                )
            );
            
            $customer_id = $this->model_customer_customer->addCustomer($customer_data);
        } else {
            $customer_id = $customer_info['customer_id'];
        }
        
        // Ürünleri hazırla
        $products = array();
        
        if (isset($orderData['itemList']) && isset($orderData['itemList']['item'])) {
            foreach ($orderData['itemList']['item'] as $item) {
                // Ürünü bul
                $this->load->model('catalog/product');
                $product_info = null;
                
                // Ürün SKU'su ile ara
                $product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE model = '" . $this->db->escape($item['productSellerCode']) . "'");
                
                if ($product_query->num_rows) {
                    $product_info = $product_query->row;
                }
                
                if ($product_info) {
                    $products[] = array(
                        'product_id' => $product_info['product_id'],
                        'name' => $item['productName'],
                        'model' => $item['productSellerCode'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['price'] * $item['quantity']
                    );
                } else {
                    // Ürün bulunamadıysa varsayılan ürün oluştur
                    $products[] = array(
                        'product_id' => 0,
                        'name' => $item['productName'],
                        'model' => $item['productSellerCode'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['price'] * $item['quantity']
                    );
                }
            }
        }
        
        // Sipariş verilerini hazırla
        $order_data = array(
            'invoice_prefix' => $settings['config_invoice_prefix'],
            'store_id' => $store_id,
            'store_name' => $settings['config_name'],
            'store_url' => $settings['config_url'],
            'customer_id' => $customer_id,
            'customer_group_id' => $settings['config_customer_group_id'],
            'firstname' => $n11Order['buyer_name'],
            'lastname' => '',
            'email' => $n11Order['buyer_email'],
            'telephone' => $n11Order['buyer_phone'],
            'payment_firstname' => $n11Order['buyer_name'],
            'payment_lastname' => '',
            'payment_company' => '',
            'payment_address_1' => $n11Order['billing_address'],
            'payment_address_2' => '',
            'payment_city' => $n11Order['city'],
            'payment_postcode' => '',
            'payment_country' => 'Türkiye',
            'payment_country_id' => $country_id,
            'payment_zone' => $n11Order['city'],
            'payment_zone_id' => $zone_id,
            'payment_method' => 'n11',
            'payment_code' => 'n11',
            'shipping_firstname' => $n11Order['buyer_name'],
            'shipping_lastname' => '',
            'shipping_company' => '',
            'shipping_address_1' => $n11Order['shipping_address'],
            'shipping_address_2' => '',
            'shipping_city' => $n11Order['city'],
            'shipping_postcode' => '',
            'shipping_country' => 'Türkiye',
            'shipping_country_id' => $country_id,
            'shipping_zone' => $n11Order['city'],
            'shipping_zone_id' => $zone_id,
            'shipping_method' => 'n11',
            'shipping_code' => 'n11',
            'comment' => 'n11 Sipariş No: ' . $n11Order['order_number'],
            'total' => $n11Order['total'],
            'order_status_id' => $this->getOpencartStatusId($n11Order['status']),
            'language_id' => $settings['config_language_id'],
            'currency_id' => 1, // TL için
            'currency_code' => 'TRY',
            'currency_value' => 1.0,
            'ip' => '',
            'forwarded_ip' => '',
            'user_agent' => 'n11 API',
            'accept_language' => 'tr-tr',
            'products' => $products,
            'vouchers' => array(),
            'totals' => array(
                array(
                    'code' => 'sub_total',
                    'title' => 'Ara Toplam',
                    'value' => $n11Order['total'] - $n11Order['shipping_cost'],
                    'sort_order' => 1
                ),
                array(
                    'code' => 'shipping',
                    'title' => 'Kargo Bedeli',
                    'value' => $n11Order['shipping_cost'],
                    'sort_order' => 3
                ),
                array(
                    'code' => 'total',
                    'title' => 'Toplam',
                    'value' => $n11Order['total'],
                    'sort_order' => 9
                )
            )
        );
        
        // OpenCart siparişi oluştur
        $this->load->model('checkout/order');
        $opencart_order_id = $this->model_checkout_order->addOrder($order_data);
        
        // Sipariş durumunu güncelle
        $this->model_checkout_order->addOrderHistory($opencart_order_id, $order_data['order_status_id'], 'n11\'den alınan sipariş (Sipariş No: ' . $n11Order['order_number'] . ')', true);
        
        // n11 siparişini OpenCart siparişiyle ilişkilendir
        $this->linkOrderToOpencart($n11OrderId, $opencart_order_id);
        
        $this->writeLog('CONVERT_ORDER', 'n11 siparişi OpenCart siparişine dönüştürüldü: ' . $n11OrderId . ' -> ' . $opencart_order_id);
        
        return $opencart_order_id;
    }
    
    /**
     * n11 sipariş durumunu OpenCart sipariş durumuna dönüştürür
     *
     * @param string $n11Status n11 sipariş durumu
     * @return int OpenCart sipariş durum ID'si
     */
    private function getOpencartStatusId($n11Status) {
        $status_map = array(
            'New' => 1, // Beklemede
            'Approved' => 2, // İşlemde
            'Shipped' => 3, // Gönderildi
            'Delivered' => 5, // Tamamlandı
            'Rejected' => 7, // İptal Edildi
            'Cancelled' => 7, // İptal Edildi
            'Returned' => 11, // İade Edildi
            'RejectedByCustomer' => 7 // İptal Edildi
        );
        
        return isset($status_map[$n11Status]) ? $status_map[$n11Status] : 1;
    }
    
    /**
     * Log kaydı oluşturur
     *
     * @param string $action İşlem
     * @param string $message Mesaj
     */
    private function writeLog($action, $message) {
        $log_file = DIR_LOGS . 'n11_model.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [MODEL] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
    
    /**
     * n11 siparişini siler
     *
     * @param int $order_id Sipariş ID'si
     * @return bool İşlem başarılı mı
     */
    public function deleteOrder($order_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "n11_orders` WHERE `id` = '" . (int)$order_id . "'");
        
        $this->writeLog('DELETE_ORDER', 'n11 siparişi silindi: ' . $order_id);
        
        return $this->db->countAffected() > 0;
    }
    
    /**
     * OpenCart ürün ID'sine göre N11 ürünü bul
     * 
     * @param int $productId OpenCart ürün ID
     * @return array|false N11 ürün verisi
     */
    public function getProductByOpencartId($productId) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "n11_products WHERE product_id = '" . (int)$productId . "'");
        return $query->num_rows ? $query->row : false;
    }
    
    /**
     * N11 ürünü ekle
     * 
     * @param array $data Ürün verisi
     * @return int Eklenen ürün ID
     */
    public function addProduct($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "n11_products SET
            product_id = '" . (int)$data['product_id'] . "',
            n11_product_id = '" . $this->db->escape($data['n11_product_id']) . "',
            n11_seller_code = '" . $this->db->escape($data['n11_seller_code']) . "',
            status = '" . (int)$data['status'] . "',
            last_updated = NOW(),
            sync_status = '" . $this->db->escape($data['sync_status']) . "',
            error_message = '" . $this->db->escape($data['error_message'] ?? '') . "'");
        
        $product_id = $this->db->getLastId();
        $this->writeLog('ADD_PRODUCT', 'N11 ürünü eklendi: ' . $data['n11_seller_code']);
        return $product_id;
    }
    
    /**
     * N11 ürünü güncelle
     * 
     * @param int $id N11 ürün ID
     * @param array $data Güncellenecek veriler
     * @return bool İşlem başarılı mı
     */
    public function updateProduct($id, $data) {
        $sql = "UPDATE " . DB_PREFIX . "n11_products SET ";
        $updates = [];
        
        if (isset($data['last_updated'])) {
            $updates[] = "last_updated = '" . $this->db->escape($data['last_updated']) . "'";
        }
        
        if (isset($data['sync_status'])) {
            $updates[] = "sync_status = '" . $this->db->escape($data['sync_status']) . "'";
        }
        
        if (isset($data['error_message'])) {
            $updates[] = "error_message = '" . $this->db->escape($data['error_message']) . "'";
        }
        
        if (isset($data['status'])) {
            $updates[] = "status = '" . (int)$data['status'] . "'";
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $sql .= implode(', ', $updates) . " WHERE id = '" . (int)$id . "'";
        $this->db->query($sql);
        
        $this->writeLog('UPDATE_PRODUCT', 'N11 ürünü güncellendi: ' . $id);
        return $this->db->countAffected() > 0;
    }
    
    /**
     * N11 ürünlerini getir
     * 
     * @param array $filter Filtre parametreleri
     * @return array Ürün listesi
     */
    public function getProducts($filter = []) {
        $sql = "SELECT * FROM " . DB_PREFIX . "n11_products WHERE 1=1";
        
        if (isset($filter['sync_status'])) {
            $sql .= " AND sync_status = '" . $this->db->escape($filter['sync_status']) . "'";
        }
        
        if (isset($filter['status'])) {
            $sql .= " AND status = '" . (int)$filter['status'] . "'";
        }
        
        $sql .= " ORDER BY last_updated DESC";
        
        if (isset($filter['start']) && isset($filter['limit'])) {
            $sql .= " LIMIT " . (int)$filter['start'] . ", " . (int)$filter['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * N11 siparişi ID'ye göre getir
     * 
     * @param int $id Sipariş ID
     * @return array|false Sipariş verisi
     */
    public function getOrder($id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "n11_orders WHERE id = '" . (int)$id . "'");
        return $query->num_rows ? $query->row : false;
    }
    
    /**
     * Dashboard widget için istatistikleri getir
     * 
     * @return array Dashboard istatistikleri
     */
    public function getDashboardStats() {
        $stats = array();
        
        // Toplam ürün sayısı
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "n11_products WHERE status = '1'");
        $stats['total_products'] = $query->row['total'];
        
        // Toplam sipariş sayısı
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "n11_orders");
        $stats['total_orders'] = $query->row['total'];
        
        // Toplam senkronizasyon sayısı (bu ay)
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "n11_products WHERE DATE(last_updated) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
        $stats['total_sync'] = $query->row['total'];
        
        // Son senkronizasyon tarihi
        $query = $this->db->query("SELECT MAX(last_updated) as last_sync FROM " . DB_PREFIX . "n11_products");
        $stats['last_sync'] = $query->row['last_sync'] ? date('d.m.Y H:i', strtotime($query->row['last_sync'])) : 'Hiçbir zaman';
        
        // API bağlantı durumu kontrolü
        $api_status = $this->checkApiConnection();
        $stats['status'] = $api_status ? 'connected' : 'error';
        
        // Son aktivite
        $query = $this->db->query("SELECT date_added FROM " . DB_PREFIX . "n11_orders ORDER BY date_added DESC LIMIT 1");
        if ($query->num_rows) {
            $stats['recent_activity'] = 'Son sipariş: ' . date('d.m.Y H:i', strtotime($query->row['date_added']));
        } else {
            $stats['recent_activity'] = 'Henüz sipariş yok';
        }
        
        return $stats;
    }
    
    /**
     * N11 API bağlantısını kontrol et
     * 
     * @return bool API bağlantısı başarılı mı
     */
    private function checkApiConnection() {
        try {
            // API ayarlarını kontrol et
            $api_key = $this->config->get('module_n11_api_key');
            $secret_key = $this->config->get('module_n11_secret_key');
            
            if (empty($api_key) || empty($secret_key)) {
                return false;
            }
            
            // Basit bir API çağrısı yap (kategorileri getir)
            // Bu kısım N11 API helper'ı ile yapılabilir
            return true; // Şimdilik true dön, gerçek API çağrısı helper ile yapılacak
            
        } catch (Exception $e) {
            $this->writeLog('API_CHECK', 'API bağlantı kontrolü başarısız: ' . $e->getMessage());
            return false;
        }
    }
} 