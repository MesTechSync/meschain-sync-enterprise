<?php
namespace Opencart\Admin\Model\Extension\Module;

/**
 * Trendyol OpenCart Integration Install Model
 * V2 Design Implementation
 *
 * @author Meschain Development Team
 * @version 2.0.0
 */
class MeschainTrendyolInstall extends \Opencart\System\Engine\Model {

    /**
     * Trendyol entegrasyonu için gerekli tüm tabloları oluştur
     */
    public function install(): void {
        // Ana Trendyol ayarları tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_settings` (
                `setting_id` int(11) NOT NULL AUTO_INCREMENT,
                `supplier_id` varchar(50) NOT NULL,
                `api_key` varchar(255) NOT NULL,
                `api_secret` varchar(255) NOT NULL,
                `environment` varchar(10) DEFAULT 'prod',
                `is_active` tinyint(1) DEFAULT 0,
                `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`setting_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Kategori ilişkilendirme tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_category` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `trendyol_category_id` int(11) NOT NULL,
                `name` varchar(255) NOT NULL,
                `parent_id` int(11) DEFAULT NULL,
                `path` varchar(1000) DEFAULT NULL,
                `leaf` tinyint(1) DEFAULT 0,
                `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `trendyol_category_id` (`trendyol_category_id`),
                KEY `parent_id` (`parent_id`),
                KEY `leaf` (`leaf`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Kategori eşleştirmeleri
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_category_mapping` (
                `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
                `opencart_category_id` int(11) NOT NULL,
                `trendyol_category_id` int(11) NOT NULL,
                `mapping_type` enum('manual','auto','ai_suggested') DEFAULT 'manual',
                `confidence_score` decimal(3,2) DEFAULT NULL,
                `is_active` tinyint(1) DEFAULT 1,
                `created_by` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`mapping_id`),
                UNIQUE KEY `opencart_category_id` (`opencart_category_id`),
                KEY `trendyol_category_id` (`trendyol_category_id`),
                KEY `mapping_type` (`mapping_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Marka tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_brand` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `trendyol_brand_id` int(11) NOT NULL,
                `name` varchar(255) NOT NULL,
                `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `trendyol_brand_id` (`trendyol_brand_id`),
                KEY `name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Marka eşleştirme tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_brand_mapping` (
                `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
                `opencart_manufacturer_id` int(11) NOT NULL,
                `trendyol_brand_id` int(11) NOT NULL,
                `opencart_manufacturer_name` varchar(255) NOT NULL,
                `trendyol_brand_name` varchar(255) NOT NULL,
                `mapping_type` enum('manual','auto','ai_suggested') DEFAULT 'manual',
                `confidence_score` decimal(3,2) DEFAULT NULL,
                `is_active` tinyint(1) DEFAULT 1,
                `created_by` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`mapping_id`),
                UNIQUE KEY `opencart_manufacturer_id` (`opencart_manufacturer_id`),
                KEY `trendyol_brand_id` (`trendyol_brand_id`),
                KEY `mapping_type` (`mapping_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Özellik eşleştirme tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_attribute_mapping` (
                `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
                `trendyol_category_id` int(11) NOT NULL,
                `opencart_attribute_id` int(11) NOT NULL,
                `trendyol_attribute_id` int(11) NOT NULL,
                `trendyol_attribute_name` varchar(255) NOT NULL,
                `opencart_attribute_name` varchar(255) NOT NULL,
                `mapping_type` enum('manual','auto','ai_suggested') DEFAULT 'manual',
                `confidence_score` decimal(3,2) DEFAULT NULL,
                `is_active` tinyint(1) DEFAULT 1,
                `value_mapping` text DEFAULT NULL COMMENT 'JSON of mapped values',
                `created_by` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`mapping_id`),
                UNIQUE KEY `category_attribute_unique` (`trendyol_category_id`,`opencart_attribute_id`),
                KEY `trendyol_attribute_id` (`trendyol_attribute_id`),
                KEY `opencart_attribute_id` (`opencart_attribute_id`),
                KEY `mapping_type` (`mapping_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Kategori özellik tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_category_attribute` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `trendyol_category_id` int(11) NOT NULL,
                `trendyol_attribute_id` int(11) NOT NULL,
                `name` varchar(255) NOT NULL,
                `required` tinyint(1) DEFAULT 0,
                `varianter` tinyint(1) DEFAULT 0,
                `allowCustom` tinyint(1) DEFAULT 0,
                `attribute_values` text DEFAULT NULL COMMENT 'JSON array of allowed values',
                `attribute_type` varchar(50) DEFAULT NULL,
                `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `category_attribute_unique` (`trendyol_category_id`,`trendyol_attribute_id`),
                KEY `trendyol_category_id` (`trendyol_category_id`),
                KEY `required` (`required`),
                KEY `varianter` (`varianter`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Sipariş senkronizasyon tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_order` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `trendyol_order_id` varchar(255) NOT NULL,
                `opencart_order_id` int(11) DEFAULT NULL,
                `order_status` varchar(50) NOT NULL,
                `order_data` text NOT NULL COMMENT 'JSON order data from Trendyol',
                `sync_status` varchar(50) NOT NULL DEFAULT 'pending',
                `error_message` text DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `trendyol_order_id` (`trendyol_order_id`),
                KEY `opencart_order_id` (`opencart_order_id`),
                KEY `order_status` (`order_status`),
                KEY `sync_status` (`sync_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Ürün senkronizasyon tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_product` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `opencart_product_id` int(11) NOT NULL,
                `trendyol_product_id` varchar(50) DEFAULT NULL,
                `barcode` varchar(255) NOT NULL,
                `approved` tinyint(1) DEFAULT 0,
                `last_sync_status` varchar(50) DEFAULT NULL,
                `last_sync_message` text DEFAULT NULL,
                `last_sync_date` datetime DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `opencart_product_id` (`opencart_product_id`),
                KEY `trendyol_product_id` (`trendyol_product_id`),
                KEY `barcode` (`barcode`),
                KEY `approved` (`approved`),
                KEY `last_sync_status` (`last_sync_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Olay log tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_event_log` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `event_type` varchar(50) NOT NULL,
                `related_id` int(11) DEFAULT NULL,
                `data` text DEFAULT NULL,
                `status` varchar(50) NOT NULL,
                `message` text DEFAULT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`log_id`),
                KEY `event_type` (`event_type`),
                KEY `related_id` (`related_id`),
                KEY `status` (`status`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Trendyol event kayıtları
        $this->load->model('setting/event');
        
        // Product ekleme/güncelleme event
        $this->model_setting_event->addEvent([
            'code' => 'meschain_trendyol_product_update',
            'description' => 'Ürün güncellendiğinde Trendyol senkronizasyonu tetikler',
            'trigger' => 'admin/model/catalog/product/addProduct/after',
            'action' => 'extension/meschain/event/product_event|addProduct',
            'status' => 1,
            'sort_order' => 0
        ]);
        
        $this->model_setting_event->addEvent([
            'code' => 'meschain_trendyol_product_edit',
            'description' => 'Ürün düzenlendiğinde Trendyol senkronizasyonu tetikler',
            'trigger' => 'admin/model/catalog/product/editProduct/after',
            'action' => 'extension/meschain/event/product_event|editProduct',
            'status' => 1,
            'sort_order' => 0
        ]);

        // Stok güncelleme event
        $this->model_setting_event->addEvent([
            'code' => 'meschain_trendyol_stock_update',
            'description' => 'Stok güncellendiğinde Trendyol senkronizasyonu tetikler',
            'trigger' => 'catalog/model/checkout/order/addOrderHistory/after',
            'action' => 'extension/meschain/event/stock_event|updateStock',
            'status' => 1,
            'sort_order' => 0
        ]);

        // Sipariş durumu güncelleme event
        $this->model_setting_event->addEvent([
            'code' => 'meschain_trendyol_order_status',
            'description' => 'Sipariş durumu değiştiğinde Trendyol bildirimini tetikler',
            'trigger' => 'catalog/model/checkout/order/addOrderHistory/after',
            'action' => 'extension/meschain/event/order_event|updateOrderStatus',
            'status' => 1,
            'sort_order' => 0
        ]);
    }

    /**
     * Trendyol entegrasyonu tablolarını kaldır
     */
    public function uninstall(): void {
        // Trendyol eventlerini kaldır
        $this->load->model('setting/event');
        
        $this->model_setting_event->deleteEventByCode('meschain_trendyol_product_update');
        $this->model_setting_event->deleteEventByCode('meschain_trendyol_product_edit');
        $this->model_setting_event->deleteEventByCode('meschain_trendyol_stock_update');
        $this->model_setting_event->deleteEventByCode('meschain_trendyol_order_status');
        
        // Eğer istenirse tabloları kaldır
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_settings`");
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_category`");
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_category_mapping`");
        // ...
    }
}
