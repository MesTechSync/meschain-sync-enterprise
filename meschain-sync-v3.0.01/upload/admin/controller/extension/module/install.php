<?php
/**
 * install.php
 *
 * Amaç: MesChain Sync modülü için veritabanı tablolarını oluşturur.
 *
 * Loglama: Kurulum işlemleri install.log dosyasına kaydedilir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 */

class ControllerExtensionModuleInstall extends Controller {
    public function index() {
        $this->load->language('extension/module/trendyol');
        $this->document->setTitle($this->language->get('heading_title') . ' - Kurulum');
        
        $this->createTables();
        
        $this->session->data['success'] = 'MesChain Sync modülü başarıyla kuruldu!';
        $this->response->redirect($this->url->link('extension/module/trendyol', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    private function createTables() {
        $this->writeLog('SISTEM', 'KURULUM_BASLADI', 'Veritabanı tabloları oluşturuluyor...');
        
        // Pazaryeri siparişleri tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_orders` (
            `order_id` INT(11) NOT NULL AUTO_INCREMENT,
            `marketplace` VARCHAR(32) NOT NULL COMMENT 'trendyol, n11, amazon, ebay, hepsiburada, ozon',
            `marketplace_order_id` VARCHAR(64) NOT NULL,
            `order_status` VARCHAR(32) NOT NULL,
            `customer_id` INT(11) NOT NULL DEFAULT 0,
            `shipping_address` TEXT NOT NULL,
            `total_amount` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `currency_code` VARCHAR(3) NOT NULL DEFAULT 'TRY',
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            `sync_status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0: beklemede, 1: senkronize edildi, 2: hata',
            `error_message` TEXT NULL,
            PRIMARY KEY (`order_id`),
            UNIQUE KEY `marketplace_order` (`marketplace`, `marketplace_order_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Pazaryeri ürünleri tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_products` (
            `product_id` INT(11) NOT NULL AUTO_INCREMENT,
            `opencart_product_id` INT(11) NOT NULL,
            `marketplace` VARCHAR(32) NOT NULL COMMENT 'trendyol, n11, amazon, ebay, hepsiburada, ozon',
            `marketplace_product_id` VARCHAR(64) NOT NULL,
            `sku` VARCHAR(64) NOT NULL,
            `barcode` VARCHAR(64) NOT NULL,
            `price` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `quantity` INT(4) NOT NULL DEFAULT 0,
            `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0: pasif, 1: aktif',
            `sync_status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0: beklemede, 1: senkronize edildi, 2: hata',
            `error_message` TEXT NULL,
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`product_id`),
            UNIQUE KEY `marketplace_product` (`marketplace`, `marketplace_product_id`),
            KEY `opencart_product_id` (`opencart_product_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // API log tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_api_log` (
            `log_id` INT(11) NOT NULL AUTO_INCREMENT,
            `marketplace` VARCHAR(32) NOT NULL,
            `request_type` VARCHAR(10) NOT NULL COMMENT 'GET, POST, PUT, DELETE',
            `endpoint` VARCHAR(255) NOT NULL,
            `request_data` TEXT NULL,
            `response_data` TEXT NULL,
            `http_code` INT(3) NULL,
            `execution_time` FLOAT NOT NULL DEFAULT 0,
            `date_added` DATETIME NOT NULL,
            `user_id` INT(11) NOT NULL DEFAULT 0,
            `ip_address` VARCHAR(40) NOT NULL,
            PRIMARY KEY (`log_id`),
            KEY `marketplace` (`marketplace`),
            KEY `date_added` (`date_added`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Kullanıcı ayarları tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_user_settings` (
            `user_id` INT(11) NOT NULL,
            `marketplace` VARCHAR(32) NOT NULL,
            `api_key` VARCHAR(255) NOT NULL,
            `api_secret` VARCHAR(255) NOT NULL,
            `supplier_id` VARCHAR(64) NULL,
            `additional_settings` TEXT NULL,
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`user_id`, `marketplace`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        $this->writeLog('SISTEM', 'KURULUM_TAMAMLANDI', 'Veritabanı tabloları başarıyla oluşturuldu.');
    }
    
    private function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'install.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 