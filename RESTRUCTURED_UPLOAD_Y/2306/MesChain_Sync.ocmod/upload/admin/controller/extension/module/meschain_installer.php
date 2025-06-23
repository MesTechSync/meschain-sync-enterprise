<?php
namespace Opencart\Admin\Controller\Extension\Module;

/**
 * MesChain Sync Enterprise Installer
 * Otomatik kurulum ve güncelleme işlemleri için kullanılır
 *
 * @author MesChain Development Team
 * @version 4.5.0
 */
class MeschainInstaller extends \Opencart\System\Engine\Controller {

    /**
     * Kurulum işlemlerini gerçekleştirir
     */
    public function install(): void {
        // Yönetici izinlerini ayarla
        $this->load->model('user/user_group');
        
        // Administrator grubuna tüm MesChain modüllerini yönetme izni ver
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/meschain_sync');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/meschain_sync');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/meschain_trendyol');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/meschain_trendyol');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/meschain_n11');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/meschain_n11');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/meschain_hepsiburada');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/meschain_hepsiburada');
        
        // Veritabanı tablolarını oluştur
        $this->createTables();
        
        // Varsayılan ayarları ekle
        $this->addDefaultSettings();
        
        // Event Listener'ları ekle
        $this->setupEventListeners();
    }
    
    /**
     * Kaldırma işlemlerini gerçekleştirir
     */
    public function uninstall(): void {
        // Eventleri kaldır
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('meschain_trendyol');
        $this->model_setting_event->deleteEventByCode('meschain_n11');
        $this->model_setting_event->deleteEventByCode('meschain_hepsiburada');
        
        // Ayarları temizle
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_meschain_sync');
        $this->model_setting_setting->deleteSetting('module_meschain_trendyol');
        $this->model_setting_setting->deleteSetting('module_meschain_n11');
        $this->model_setting_setting->deleteSetting('module_meschain_hepsiburada');
    }
    
    /**
     * Veritabanı tablolarını oluşturur
     */
    private function createTables(): void {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_logs` (
                `log_id` INT(11) NOT NULL AUTO_INCREMENT,
                `level` VARCHAR(10) NOT NULL,
                `message` TEXT NOT NULL,
                `data` TEXT DEFAULT NULL,
                `marketplace` VARCHAR(50) DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`log_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_product_sync` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `product_id` INT(11) NOT NULL,
                `marketplace` VARCHAR(50) NOT NULL,
                `marketplace_id` VARCHAR(100) DEFAULT NULL,
                `status` VARCHAR(25) NOT NULL DEFAULT 'pending',
                `sync_data` TEXT DEFAULT NULL,
                `last_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_product_marketplace` (`product_id`, `marketplace`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_order_integration` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `order_id` INT(11) NOT NULL,
                `marketplace` VARCHAR(50) NOT NULL,
                `marketplace_order_id` VARCHAR(100) NOT NULL,
                `status` VARCHAR(25) NOT NULL,
                `order_data` TEXT DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_marketplace_order` (`marketplace`, `marketplace_order_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_metrics` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `metric_name` VARCHAR(100) NOT NULL,
                `metric_value` DECIMAL(15,4) NOT NULL,
                `metric_type` VARCHAR(50) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_settings` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `setting_key` VARCHAR(100) NOT NULL,
                `setting_value` TEXT DEFAULT NULL,
                `marketplace` VARCHAR(50) DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_setting_key` (`setting_key`, `marketplace`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_slow_queries` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `query` TEXT NOT NULL,
                `execution_time` DECIMAL(10,4) NOT NULL,
                `executed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_alerts` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `alert_type` VARCHAR(50) NOT NULL,
                `alert_message` TEXT NOT NULL,
                `alert_data` TEXT DEFAULT NULL,
                `is_read` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
    }
    
    /**
     * Varsayılan ayarları ekler
     */
    private function addDefaultSettings(): void {
        $this->load->model('setting/setting');
        
        // MesChain Sync ana modül ayarları
        $this->model_setting_setting->editSetting('module_meschain_sync', [
            'module_meschain_sync_status' => 1,
            'module_meschain_sync_log_level' => 'info',
            'module_meschain_sync_version' => '4.5.0'
        ]);
        
        // Trendyol entegrasyonu için varsayılan ayarlar
        $this->model_setting_setting->editSetting('module_meschain_trendyol', [
            'module_meschain_trendyol_status' => 0,
            'module_meschain_trendyol_auto_sync' => 1,
            'module_meschain_trendyol_sync_interval' => 30,
            'module_meschain_trendyol_webhook_processing_enabled' => 1
        ]);
    }
    
    /**
     * Event Listener'ları ayarlar
     */
    private function setupEventListeners(): void {
        $this->load->model('setting/event');
        
        // Ürün güncellendiğinde pazaryeri senkronizasyonu
        $this->model_setting_event->addEvent([
            'code' => 'meschain_trendyol',
            'description' => 'Trendyol pazaryeri entegrasyonu için ürün güncellemesi',
            'trigger' => 'admin/model/catalog/product/editProduct/after',
            'action' => 'extension/module/meschain_trendyol|productUpdated',
            'status' => 1,
            'sort_order' => 0
        ]);
        
        // Sipariş durumu değiştiğinde pazaryeri bildirimi
        $this->model_setting_event->addEvent([
            'code' => 'meschain_order_status',
            'description' => 'Sipariş durumu değiştiğinde pazaryerlerine bildirim',
            'trigger' => 'catalog/model/checkout/order/addHistory/after',
            'action' => 'extension/module/meschain_sync|orderStatusChanged',
            'status' => 1,
            'sort_order' => 0
        ]);
    }
}
