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
        // Trendyol siparişleri tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_order` (
                `trendyol_order_id` int(11) NOT NULL AUTO_INCREMENT,
                `order_id` varchar(128) NOT NULL,
                `order_number` varchar(128) NOT NULL,
                `status` varchar(32) NOT NULL,
                `total_price` decimal(15,4) NOT NULL DEFAULT '0.0000',
                `shipping_cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
                `customer_name` varchar(128) NOT NULL,
                `customer_email` varchar(128) NOT NULL,
                `customer_phone` varchar(32) NOT NULL,
                `shipping_address` text NOT NULL,
                `shipping_city` varchar(128) NOT NULL,
                `shipping_district` varchar(128) NOT NULL,
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`trendyol_order_id`),
                KEY `order_id` (`order_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Trendyol sipariş ürünleri tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_order_product` (
                `trendyol_order_product_id` int(11) NOT NULL AUTO_INCREMENT,
                `trendyol_order_id` int(11) NOT NULL,
                `name` varchar(255) NOT NULL,
                `barcode` varchar(64) NOT NULL,
                `quantity` int(4) NOT NULL DEFAULT '0',
                `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
                `total` decimal(15,4) NOT NULL DEFAULT '0.0000',
                PRIMARY KEY (`trendyol_order_product_id`),
                KEY `trendyol_order_id` (`trendyol_order_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Trendyol - OpenCart sipariş ilişki tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_order_relation` (
                `relation_id` int(11) NOT NULL AUTO_INCREMENT,
                `trendyol_order_id` varchar(128) NOT NULL,
                `opencart_order_id` int(11) NOT NULL,
                `date_added` datetime NOT NULL,
                PRIMARY KEY (`relation_id`),
                KEY `trendyol_order_id` (`trendyol_order_id`),
                KEY `opencart_order_id` (`opencart_order_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Trendyol ürün eşleştirme tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_product_mapping` (
                `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `trendyol_category_id` int(11) NOT NULL,
                `trendyol_brand_id` int(11) NOT NULL,
                `trendyol_product_id` varchar(128) NOT NULL,
                `is_active` tinyint(1) NOT NULL DEFAULT '0',
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`mapping_id`),
                KEY `product_id` (`product_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Trendyol log tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_log` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `user` varchar(64) NOT NULL,
                `action` varchar(64) NOT NULL,
                `message` text NOT NULL,
                `date_added` datetime NOT NULL,
                PRIMARY KEY (`log_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
    }
    
    /**
     * Trendyol modülü için kaldırma işlemini gerçekleştir
     * Sadece tabloları kaldırır, ayarları kaldırmaz (güvenlik amaçlı)
     */
    public function uninstall() {
        // Siparişler ve ürünler kalsın, diğer tabloları kaldır
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_order`");
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_order_product`");
        
        // İlişkilendirme ve eşleştirme tabloları
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_order_relation`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_product_mapping`");
        
        // Log tablosu da önemli bilgiler içerebilir, opsiyonel olarak kaldırılabilir
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_log`");
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
} 