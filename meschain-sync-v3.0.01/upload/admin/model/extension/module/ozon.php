<?php
/**
 * ozon.php
 *
 * Amaç: Ozon modülünün OpenCart yönetici paneli (admin) tarafındaki model dosyasıdır. Veritabanı işlemleri burada tanımlanır.
 *
 * Loglama: Tüm önemli işlemler ve hatalar ozon_model.log dosyasına kaydedilmelidir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 *
 * Hata yönetimi: Hatalar loglanmalı ve kullanıcıya açıklayıcı mesaj gösterilmelidir.
 */
// Ozon modülünün veritabanı işlemleri için model dosyası

/**
 * Ozon Marketplace Model
 * MesChain-Sync entegrasyonu için Ozon pazaryeri model sınıfı.
 */
class ModelExtensionModuleOzon extends Model {
    
    /**
     * Kurulum - Ozon modülü için veritabanı tabloları oluştur
     */
    public function install() {
        // Ozon ürünleri tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "user_ozon_products` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) NOT NULL,
                `product_id` INT(11) NOT NULL,
                `ozon_product_id` VARCHAR(64) NOT NULL,
                `ozon_seller_code` VARCHAR(64) NOT NULL,
                `status` TINYINT(1) NOT NULL DEFAULT '0',
                `last_updated` DATETIME NOT NULL,
                `sync_status` VARCHAR(32) NOT NULL DEFAULT 'pending',
                `error_message` TEXT,
                PRIMARY KEY (`id`),
                UNIQUE KEY `user_product` (`user_id`, `product_id`),
                KEY `ozon_product_id` (`ozon_product_id`),
                KEY `sync_status` (`sync_status`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Ozon siparişleri tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "user_ozon_orders` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) NOT NULL,
                `ozon_order_id` VARCHAR(64) NOT NULL,
                `opencart_order_id` INT(11) DEFAULT NULL,
                `status` VARCHAR(32) NOT NULL,
                `total` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
                `buyer_name` VARCHAR(255) NOT NULL,
                `buyer_phone` VARCHAR(32) NOT NULL,
                `shipping_address` TEXT NOT NULL,
                `order_data` TEXT NOT NULL,
                `date_added` DATETIME NOT NULL,
                `date_modified` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `user_ozon_order` (`user_id`, `ozon_order_id`),
                KEY `opencart_order_id` (`opencart_order_id`),
                KEY `status` (`status`),
                KEY `date_added` (`date_added`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Ozon kategori eşleştirme tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_category_mapping` (
                `mapping_id` INT(11) NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) NOT NULL,
                `opencart_category_id` INT(11) NOT NULL,
                `ozon_category_id` VARCHAR(64) NOT NULL,
                `ozon_category_name` VARCHAR(255) NOT NULL,
                `ozon_category_path` VARCHAR(512) NOT NULL,
                `attributes` TEXT,
                `date_added` DATETIME NOT NULL,
                `date_modified` DATETIME NOT NULL,
                PRIMARY KEY (`mapping_id`),
                UNIQUE KEY `user_opencart_category` (`user_id`, `opencart_category_id`),
                KEY `ozon_category_id` (`ozon_category_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
    }
    
    /**
     * Kaldırma - Ozon tabloları kaldır (opsiyonel)
     */
    public function uninstall() {
        // Tablo silme işlemi genellikle tehlikeli olduğu için varsayılan olarak kapalı
        // Eğer gerekirse aşağıdaki kodları aktifleştirin
        /*
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "user_ozon_products`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "user_ozon_orders`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ozon_category_mapping`");
        */
    }
    
    /**
     * Ozon ürün mapping kaydı
     * 
     * @param int $user_id Kullanıcı ID
     * @param int $product_id Ürün ID
     * @param array $ozon_data Ozon verileri
     * @return bool
     */
    public function saveProductMapping($user_id, $product_id, $ozon_data) {
        try {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "user_ozon_products` SET
                    `user_id` = '" . (int)$user_id . "',
                    `product_id` = '" . (int)$product_id . "',
                    `ozon_product_id` = '" . $this->db->escape($ozon_data['product_id']) . "',
                    `ozon_seller_code` = '" . $this->db->escape($ozon_data['offer_id']) . "',
                    `status` = '" . (int)$ozon_data['status'] . "',
                    `last_updated` = NOW(),
                    `sync_status` = 'success'
                ON DUPLICATE KEY UPDATE
                    `ozon_product_id` = '" . $this->db->escape($ozon_data['product_id']) . "',
                    `ozon_seller_code` = '" . $this->db->escape($ozon_data['offer_id']) . "',
                    `status` = '" . (int)$ozon_data['status'] . "',
                    `last_updated` = NOW(),
                    `sync_status` = 'success',
                    `error_message` = NULL
            ");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Ozon ürün senkronizasyon hatası kaydet
     * 
     * @param int $user_id Kullanıcı ID
     * @param int $product_id Ürün ID
     * @param string $error_message Hata mesajı
     * @return bool
     */
    public function saveProductError($user_id, $product_id, $error_message) {
        try {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "user_ozon_products` SET
                    `user_id` = '" . (int)$user_id . "',
                    `product_id` = '" . (int)$product_id . "',
                    `ozon_product_id` = '',
                    `ozon_seller_code` = '',
                    `status` = '0',
                    `last_updated` = NOW(),
                    `sync_status` = 'error',
                    `error_message` = '" . $this->db->escape($error_message) . "'
                ON DUPLICATE KEY UPDATE
                    `last_updated` = NOW(),
                    `sync_status` = 'error',
                    `error_message` = '" . $this->db->escape($error_message) . "'
            ");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Ozon ürün listesini getir
     * 
     * @param int $user_id Kullanıcı ID
     * @param array $filter Filtre kriterleri
     * @param int $start Başlangıç satırı
     * @param int $limit Satır limiti
     * @return array
     */
    public function getOzonProducts($user_id, $filter = array(), $start = 0, $limit = 20) {
        $sql = "
            SELECT op.*, p.model, p.price, p.quantity, pd.name
            FROM `" . DB_PREFIX . "user_ozon_products` op
            LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id)
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id)
            WHERE op.user_id = '" . (int)$user_id . "'
            AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
        ";
        
        // Filtre uygula
        if (!empty($filter['name'])) {
            $sql .= " AND pd.name LIKE '%" . $this->db->escape($filter['name']) . "%'";
        }
        
        if (!empty($filter['model'])) {
            $sql .= " AND p.model LIKE '%" . $this->db->escape($filter['model']) . "%'";
        }
        
        if (isset($filter['status']) && $filter['status'] !== '*') {
            $sql .= " AND op.status = '" . (int)$filter['status'] . "'";
        }
        
        // Sıralama
        $sql .= " ORDER BY pd.name ASC";
        
        // Limit
        if ($limit) {
            $sql .= " LIMIT " . (int)$start . "," . (int)$limit;
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Toplam Ozon ürün sayısını getir
     * 
     * @param int $user_id Kullanıcı ID
     * @param array $filter Filtre kriterleri
     * @return int
     */
    public function getTotalOzonProducts($user_id, $filter = array()) {
        $sql = "
            SELECT COUNT(*) AS total
            FROM `" . DB_PREFIX . "user_ozon_products` op
            LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id)
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id)
            WHERE op.user_id = '" . (int)$user_id . "'
            AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
        ";
        
        // Filtre uygula
        if (!empty($filter['name'])) {
            $sql .= " AND pd.name LIKE '%" . $this->db->escape($filter['name']) . "%'";
        }
        
        if (!empty($filter['model'])) {
            $sql .= " AND p.model LIKE '%" . $this->db->escape($filter['model']) . "%'";
        }
        
        if (isset($filter['status']) && $filter['status'] !== '*') {
            $sql .= " AND op.status = '" . (int)$filter['status'] . "'";
        }
        
        $query = $this->db->query($sql);
        
        return $query->row['total'];
    }
    
    /**
     * Ozon ürün detayını getir
     * 
     * @param int $user_id Kullanıcı ID
     * @param int $product_id Ürün ID
     * @return array|bool
     */
    public function getOzonProduct($user_id, $product_id) {
        $query = $this->db->query("
            SELECT op.*, p.model, p.price, p.quantity, pd.name
            FROM `" . DB_PREFIX . "user_ozon_products` op
            LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id)
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id)
            WHERE op.user_id = '" . (int)$user_id . "'
            AND op.product_id = '" . (int)$product_id . "'
            AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
        ");
        
        if ($query->num_rows) {
            return $query->row;
        } else {
            return false;
        }
    }
    
    /**
     * Ozon sipariş kaydet
     * 
     * @param int $user_id Kullanıcı ID
     * @param array $ozon_order Ozon sipariş verisi
     * @param int $opencart_order_id OpenCart sipariş ID
     * @return bool
     */
    public function saveOzonOrder($user_id, $ozon_order, $opencart_order_id) {
        try {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "user_ozon_orders` SET
                    `user_id` = '" . (int)$user_id . "',
                    `ozon_order_id` = '" . $this->db->escape($ozon_order['posting_number']) . "',
                    `opencart_order_id` = '" . (int)$opencart_order_id . "',
                    `status` = '" . $this->db->escape($ozon_order['status']) . "',
                    `total` = '" . (float)$ozon_order['order_total'] . "',
                    `buyer_name` = '" . $this->db->escape($ozon_order['addressee']['name']) . "',
                    `buyer_phone` = '" . $this->db->escape($ozon_order['addressee']['phone']) . "',
                    `shipping_address` = '" . $this->db->escape($ozon_order['address']['address_tail']) . "',
                    `order_data` = '" . $this->db->escape(json_encode($ozon_order)) . "',
                    `date_added` = NOW(),
                    `date_modified` = NOW()
                ON DUPLICATE KEY UPDATE
                    `opencart_order_id` = '" . (int)$opencart_order_id . "',
                    `status` = '" . $this->db->escape($ozon_order['status']) . "',
                    `total` = '" . (float)$ozon_order['order_total'] . "',
                    `order_data` = '" . $this->db->escape(json_encode($ozon_order)) . "',
                    `date_modified` = NOW()
            ");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Ozon siparişleri getir
     * 
     * @param int $user_id Kullanıcı ID
     * @param array $filter Filtre kriterleri
     * @param int $start Başlangıç satırı
     * @param int $limit Satır limiti
     * @return array
     */
    public function getOzonOrders($user_id, $filter = array(), $start = 0, $limit = 20) {
        $sql = "
            SELECT * FROM `" . DB_PREFIX . "user_ozon_orders`
            WHERE user_id = '" . (int)$user_id . "'
        ";
        
        // Filtre uygula
        if (!empty($filter['status'])) {
            $sql .= " AND status = '" . $this->db->escape($filter['status']) . "'";
        }
        
        if (!empty($filter['date_from'])) {
            $sql .= " AND date_added >= '" . $this->db->escape($filter['date_from']) . " 00:00:00'";
        }
        
        if (!empty($filter['date_to'])) {
            $sql .= " AND date_added <= '" . $this->db->escape($filter['date_to']) . " 23:59:59'";
        }
        
        // Sıralama
        $sql .= " ORDER BY date_added DESC";
        
        // Limit
        if ($limit) {
            $sql .= " LIMIT " . (int)$start . "," . (int)$limit;
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Toplam Ozon sipariş sayısını getir
     * 
     * @param int $user_id Kullanıcı ID
     * @param array $filter Filtre kriterleri
     * @return int
     */
    public function getTotalOzonOrders($user_id, $filter = array()) {
        $sql = "
            SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "user_ozon_orders`
            WHERE user_id = '" . (int)$user_id . "'
        ";
        
        // Filtre uygula
        if (!empty($filter['status'])) {
            $sql .= " AND status = '" . $this->db->escape($filter['status']) . "'";
        }
        
        if (!empty($filter['date_from'])) {
            $sql .= " AND date_added >= '" . $this->db->escape($filter['date_from']) . " 00:00:00'";
        }
        
        if (!empty($filter['date_to'])) {
            $sql .= " AND date_added <= '" . $this->db->escape($filter['date_to']) . " 23:59:59'";
        }
        
        $query = $this->db->query($sql);
        
        return $query->row['total'];
    }
    
    /**
     * Ozon istatistikleri getir
     * 
     * @param int $user_id Kullanıcı ID
     * @return array
     */
    public function getOzonStatistics($user_id) {
        $stats = array(
            'total_products' => 0,
            'active_products' => 0,
            'inactive_products' => 0,
            'total_orders' => 0,
            'pending_orders' => 0,
            'shipped_orders' => 0,
            'total_revenue' => '0.00',
            'today_revenue' => '0.00',
            'month_revenue' => '0.00',
            'last_sync' => '',
            'last_sync_status' => 'error',
            'sales_data' => array(
                'monday' => 0,
                'tuesday' => 0,
                'wednesday' => 0,
                'thursday' => 0,
                'friday' => 0,
                'saturday' => 0,
                'sunday' => 0
            )
        );
        
        // Ürün istatistikleri
        $query = $this->db->query("
            SELECT 
                COUNT(*) AS total,
                SUM(status = 1) AS active,
                SUM(status = 0) AS inactive
            FROM `" . DB_PREFIX . "user_ozon_products`
            WHERE user_id = '" . (int)$user_id . "'
        ");
        
        if ($query->num_rows) {
            $stats['total_products'] = (int)$query->row['total'];
            $stats['active_products'] = (int)$query->row['active'];
            $stats['inactive_products'] = (int)$query->row['inactive'];
        }
        
        // Sipariş istatistikleri
        $query = $this->db->query("
            SELECT 
                COUNT(*) AS total,
                SUM(status IN ('awaiting_packaging', 'awaiting_deliver')) AS pending,
                SUM(status IN ('delivering', 'delivered')) AS shipped,
                SUM(total) AS revenue
            FROM `" . DB_PREFIX . "user_ozon_orders`
            WHERE user_id = '" . (int)$user_id . "'
        ");
        
        if ($query->num_rows) {
            $stats['total_orders'] = (int)$query->row['total'];
            $stats['pending_orders'] = (int)$query->row['pending'];
            $stats['shipped_orders'] = (int)$query->row['shipped'];
            $stats['total_revenue'] = $this->currency->format($query->row['revenue'], $this->config->get('config_currency'));
        }
        
        // Bugünkü gelir
        $query = $this->db->query("
            SELECT SUM(total) AS revenue
            FROM `" . DB_PREFIX . "user_ozon_orders`
            WHERE user_id = '" . (int)$user_id . "'
            AND DATE(date_added) = CURDATE()
        ");
        
        if ($query->num_rows) {
            $stats['today_revenue'] = $this->currency->format($query->row['revenue'] ? $query->row['revenue'] : 0, $this->config->get('config_currency'));
        }
        
        // Aylık gelir
        $query = $this->db->query("
            SELECT SUM(total) AS revenue
            FROM `" . DB_PREFIX . "user_ozon_orders`
            WHERE user_id = '" . (int)$user_id . "'
            AND MONTH(date_added) = MONTH(CURDATE())
            AND YEAR(date_added) = YEAR(CURDATE())
        ");
        
        if ($query->num_rows) {
            $stats['month_revenue'] = $this->currency->format($query->row['revenue'] ? $query->row['revenue'] : 0, $this->config->get('config_currency'));
        }
        
        // Son senkronizasyon
        $query = $this->db->query("
            SELECT last_updated, sync_status
            FROM `" . DB_PREFIX . "user_ozon_products`
            WHERE user_id = '" . (int)$user_id . "'
            ORDER BY last_updated DESC
            LIMIT 1
        ");
        
        if ($query->num_rows) {
            $stats['last_sync'] = $query->row['last_updated'];
            $stats['last_sync_status'] = $query->row['sync_status'] == 'success' ? 'success' : 'error';
        }
        
        // Haftalık satış verileri
        $query = $this->db->query("
            SELECT 
                DAYOFWEEK(date_added) AS day_of_week,
                COUNT(*) AS order_count
            FROM `" . DB_PREFIX . "user_ozon_orders`
            WHERE user_id = '" . (int)$user_id . "'
            AND date_added >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DAYOFWEEK(date_added)
        ");
        
        if ($query->num_rows) {
            foreach ($query->rows as $row) {
                // MySQL'de DAYOFWEEK: 1=Pazar, 2=Pazartesi, ..., 7=Cumartesi
                switch ($row['day_of_week']) {
                    case 1: // Pazar
                        $stats['sales_data']['sunday'] = (int)$row['order_count'];
                        break;
                    case 2: // Pazartesi
                        $stats['sales_data']['monday'] = (int)$row['order_count'];
                        break;
                    case 3: // Salı
                        $stats['sales_data']['tuesday'] = (int)$row['order_count'];
                        break;
                    case 4: // Çarşamba
                        $stats['sales_data']['wednesday'] = (int)$row['order_count'];
                        break;
                    case 5: // Perşembe
                        $stats['sales_data']['thursday'] = (int)$row['order_count'];
                        break;
                    case 6: // Cuma
                        $stats['sales_data']['friday'] = (int)$row['order_count'];
                        break;
                    case 7: // Cumartesi
                        $stats['sales_data']['saturday'] = (int)$row['order_count'];
                        break;
                }
            }
        }
        
        return $stats;
    }
    
    /**
     * Kullanıcı aktivitelerini getir
     * 
     * @param int $user_id Kullanıcı ID
     * @param int $limit Limit
     * @return array
     */
    public function getUserActivities($user_id, $limit = 10) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "user_activity_log`
            WHERE user_id = '" . (int)$user_id . "'
            AND module = 'OZON'
            ORDER BY created_date DESC
            LIMIT " . (int)$limit
        ");
        
        return $query->rows;
    }
}