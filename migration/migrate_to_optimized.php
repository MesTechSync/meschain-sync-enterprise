<?php
/**
 * Migration Script
 * Mevcut MesChain-Sync kodunu optimize edilmiş yapıya geçirir
 */

class MesChainSyncMigration {
    private $db;
    private $backup_dir;
    
    public function __construct($db) {
        $this->db = $db;
        $this->backup_dir = DIR_STORAGE . 'backup_' . date('Y-m-d_H-i-s') . '/';
    }
    
    /**
     * Ana migration işlemi
     */
    public function migrate() {
        echo "MesChain-Sync Optimizasyon Migration Başlıyor...\n";
        
        // 1. Backup al
        $this->createBackup();
        
        // 2. Veritabanı güncellemeleri
        $this->updateDatabase();
        
        // 3. Dosya güncellemeleri
        $this->updateFiles();
        
        // 4. Ayarları güncelle
        $this->updateSettings();
        
        // 5. Cache temizle
        $this->clearCache();
        
        echo "Migration tamamlandı!\n";
    }
    
    /**
     * Backup oluştur
     */
    private function createBackup() {
        echo "Backup alınıyor...\n";
        
        if (!is_dir($this->backup_dir)) {
            mkdir($this->backup_dir, 0755, true);
        }
        
        // Controller dosyalarını yedekle
        $controllers = array(
            'admin/controller/extension/module/n11.php',
            'admin/controller/extension/module/trendyol.php',
            'admin/controller/extension/module/amazon.php',
            'admin/controller/extension/module/ebay.php',
            'admin/controller/extension/module/hepsiburada.php',
            'admin/controller/extension/module/ozon.php'
        );
        
        foreach ($controllers as $file) {
            if (file_exists(DIR_APPLICATION . '../' . $file)) {
                $dest = $this->backup_dir . $file;
                $dest_dir = dirname($dest);
                if (!is_dir($dest_dir)) {
                    mkdir($dest_dir, 0755, true);
                }
                copy(DIR_APPLICATION . '../' . $file, $dest);
            }
        }
        
        // Veritabanı yedeği
        $this->backupDatabase();
        
        echo "Backup tamamlandı: " . $this->backup_dir . "\n";
    }
    
    /**
     * Veritabanı yedeği
     */
    private function backupDatabase() {
        $tables = array(
            'meschain_sync_settings',
            'meschain_sync_log',
            'n11_category_mapping',
            'n11_products',
            'n11_orders',
            'trendyol_category_mapping',
            'trendyol_products',
            'amazon_category_mapping',
            'amazon_products',
            'amazon_orders'
        );
        
        $sql = "";
        foreach ($tables as $table) {
            $full_table = DB_PREFIX . $table;
            
            // Tablo var mı kontrol et
            $result = $this->db->query("SHOW TABLES LIKE '" . $full_table . "'");
            if ($result->num_rows > 0) {
                // Tablo yapısı
                $create = $this->db->query("SHOW CREATE TABLE `" . $full_table . "`");
                $sql .= "\n\n" . $create->row['Create Table'] . ";\n\n";
                
                // Tablo verileri
                $rows = $this->db->query("SELECT * FROM `" . $full_table . "`");
                foreach ($rows->rows as $row) {
                    $sql .= "INSERT INTO `" . $full_table . "` VALUES (";
                    $values = array();
                    foreach ($row as $value) {
                        $values[] = "'" . $this->db->escape($value) . "'";
                    }
                    $sql .= implode(",", $values) . ");\n";
                }
            }
        }
        
        file_put_contents($this->backup_dir . 'database_backup.sql', $sql);
    }
    
    /**
     * Veritabanı güncellemeleri
     */
    private function updateDatabase() {
        echo "Veritabanı güncelleniyor...\n";
        
        // Yeni tablolar ekle
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_queue` (
                `queue_id` INT(11) NOT NULL AUTO_INCREMENT,
                `job_type` VARCHAR(64) NOT NULL,
                `job_data` TEXT NOT NULL,
                `priority` INT(11) NOT NULL DEFAULT '0',
                `status` VARCHAR(32) NOT NULL DEFAULT 'pending',
                `attempts` INT(11) NOT NULL DEFAULT '0',
                `error_message` TEXT,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                PRIMARY KEY (`queue_id`),
                KEY `status` (`status`),
                KEY `priority` (`priority`),
                KEY `created_at` (`created_at`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Index'ler ekle
        $indexes = array(
            "ALTER TABLE `" . DB_PREFIX . "n11_products` ADD INDEX IF NOT EXISTS `idx_sync_status` (`sync_status`)",
            "ALTER TABLE `" . DB_PREFIX . "n11_products` ADD INDEX IF NOT EXISTS `idx_last_updated` (`last_updated`)",
            "ALTER TABLE `" . DB_PREFIX . "n11_orders` ADD INDEX IF NOT EXISTS `idx_date_added` (`date_added`)",
            "ALTER TABLE `" . DB_PREFIX . "meschain_sync_log` ADD INDEX IF NOT EXISTS `idx_marketplace_date` (`marketplace`, `date_added`)"
        );
        
        foreach ($indexes as $query) {
            try {
                $this->db->query($query);
            } catch (Exception $e) {
                echo "Index eklenemedi: " . $e->getMessage() . "\n";
            }
        }
        
        // Yeni kolonlar ekle
        $this->addColumnIfNotExists('meschain_sync_settings', 'encrypted', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `serialized`');
        $this->addColumnIfNotExists('meschain_sync_log', 'execution_time', 'DECIMAL(10,4) DEFAULT NULL AFTER `data`');
        
        echo "Veritabanı güncellemeleri tamamlandı\n";
    }
    
    /**
     * Kolon ekle (varsa ekleme)
     */
    private function addColumnIfNotExists($table, $column, $definition) {
        $full_table = DB_PREFIX . $table;
        $result = $this->db->query("SHOW COLUMNS FROM `" . $full_table . "` LIKE '" . $column . "'");
        
        if ($result->num_rows == 0) {
            $this->db->query("ALTER TABLE `" . $full_table . "` ADD `" . $column . "` " . $definition);
        }
    }
    
    /**
     * Dosya güncellemeleri
     */
    private function updateFiles() {
        echo "Dosyalar güncelleniyor...\n";
        
        // Controller dosyalarını güncelle
        $this->updateControllerFiles();
        
        // Model dosyalarını güncelle
        $this->updateModelFiles();
        
        // Language dosyalarını güncelle
        $this->updateLanguageFiles();
        
        echo "Dosya güncellemeleri tamamlandı\n";
    }
    
    /**
     * Controller dosyalarını güncelle
     */
    private function updateControllerFiles() {
        // N11 controller'ı güncelle
        $n11_controller = DIR_APPLICATION . 'controller/extension/module/n11.php';
        if (file_exists($n11_controller)) {
            $content = file_get_contents($n11_controller);
            
            // Base controller require ekle
            if (strpos($content, 'base_marketplace.php') === false) {
                $content = str_replace(
                    '<?php',
                    "<?php\nrequire_once(DIR_APPLICATION . 'controller/extension/module/base_marketplace.php');",
                    $content
                );
            }
            
            // Güvenlik helper kullan
            if (strpos($content, 'SecurityHelper') === false) {
                $content = str_replace(
                    'class ControllerExtensionModuleN11',
                    "require_once(DIR_APPLICATION . 'controller/extension/module/security_helper.php');\n\nclass ControllerExtensionModuleN11",
                    $content
                );
            }
            
            // API key şifreleme güncelle
            $content = preg_replace(
                '/base64_encode\s*\(\s*([^)]+)\s*\)/',
                'SecurityHelper::encryptApiKey($1)',
                $content
            );
            
            $content = preg_replace(
                '/base64_decode\s*\(\s*([^)]+)\s*\)/',
                'SecurityHelper::decryptApiKey($1)',
                $content
            );
            
            file_put_contents($n11_controller, $content);
        }
    }
    
    /**
     * Model dosyalarını güncelle
     */
    private function updateModelFiles() {
        // Model dosyaları için base model oluştur
        $base_model = '<?php
abstract class ModelExtensionModuleBaseMarketplace extends Model {
    protected $marketplace_name;
    
    public function install() {
        // Ortak kurulum işlemleri
        $this->createTables();
        $this->addPermissions();
    }
    
    public function uninstall() {
        // Ortak kaldırma işlemleri
        $this->removeTables();
        $this->removePermissions();
    }
    
    abstract protected function createTables();
    abstract protected function removeTables();
    
    protected function addPermissions() {
        $this->load->model("user/user_group");
        $this->model_user_user_group->addPermission($this->user->getGroupId(), "access", "extension/module/" . $this->marketplace_name);
        $this->model_user_user_group->addPermission($this->user->getGroupId(), "modify", "extension/module/" . $this->marketplace_name);
    }
    
    protected function removePermissions() {
        $this->load->model("user/user_group");
        $this->model_user_user_group->removePermission($this->user->getGroupId(), "access", "extension/module/" . $this->marketplace_name);
        $this->model_user_user_group->removePermission($this->user->getGroupId(), "modify", "extension/module/" . $this->marketplace_name);
    }
}';
        
        file_put_contents(DIR_APPLICATION . 'model/extension/module/base_marketplace.php', $base_model);
    }
    
    /**
     * Language dosyalarını güncelle
     */
    private function updateLanguageFiles() {
        // Ortak language değişkenleri
        $common_lang = array(
            'text_success' => 'Başarılı: Ayarlar kaydedildi!',
            'text_test_success' => 'API bağlantısı başarılı!',
            'text_test_failed' => 'API bağlantısı başarısız!',
            'text_missing_keys' => 'API bilgileri eksik!',
            'error_permission' => 'Uyarı: Bu modülü değiştirme yetkiniz yok!',
            'error_api_connect' => 'API bağlantısı kurulamadı!',
            'text_sync_complete' => '%s ürün senkronize edildi, %s başarısız.',
            'text_orders_imported' => '%s sipariş import edildi.'
        );
        
        // Her dil için güncelle
        $languages = array('tr-tr', 'en-gb');
        $modules = array('n11', 'trendyol', 'amazon', 'ebay', 'hepsiburada', 'ozon');
        
        foreach ($languages as $lang) {
            foreach ($modules as $module) {
                $file = DIR_APPLICATION . 'language/' . $lang . '/extension/module/' . $module . '.php';
                if (file_exists($file)) {
                    $content = file_get_contents($file);
                    
                    foreach ($common_lang as $key => $value) {
                        if (strpos($content, '$_["' . $key . '"]') === false) {
                            $content = str_replace('?>', '$_["' . $key . '"] = "' . $value . '";\n?>', $content);
                        }
                    }
                    
                    file_put_contents($file, $content);
                }
            }
        }
    }
    
    /**
     * Ayarları güncelle
     */
    private function updateSettings() {
        echo "Ayarlar güncelleniyor...\n";
        
        // Mevcut API key'leri şifrele
        $marketplaces = array('n11', 'trendyol', 'amazon', 'ebay', 'hepsiburada', 'ozon');
        
        foreach ($marketplaces as $marketplace) {
            // API key'leri al
            $api_key = $this->getSetting('module_' . $marketplace . '_api_key');
            $api_secret = $this->getSetting('module_' . $marketplace . '_api_secret');
            
            if ($api_key && strpos($api_key, 'encrypted:') !== 0) {
                // Şifrele ve güncelle
                require_once(DIR_APPLICATION . 'controller/extension/module/security_helper.php');
                
                $encrypted_key = 'encrypted:' . SecurityHelper::encryptApiKey($api_key);
                $this->setSetting('module_' . $marketplace . '_api_key', $encrypted_key);
                
                if ($api_secret) {
                    $encrypted_secret = 'encrypted:' . SecurityHelper::encryptApiKey($api_secret);
                    $this->setSetting('module_' . $marketplace . '_api_secret', $encrypted_secret);
                }
            }
        }
        
        // Cache ayarları ekle
        $this->setSetting('meschain_cache_enabled', 1);
        $this->setSetting('meschain_cache_ttl', 3600);
        
        echo "Ayarlar güncellendi\n";
    }
    
    /**
     * Ayar al
     */
    private function getSetting($key) {
        $query = $this->db->query("
            SELECT value FROM " . DB_PREFIX . "setting 
            WHERE `key` = '" . $this->db->escape($key) . "' 
            AND store_id = '0'
        ");
        
        return $query->num_rows ? $query->row['value'] : null;
    }
    
    /**
     * Ayar kaydet
     */
    private function setSetting($key, $value) {
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "setting 
            WHERE `key` = '" . $this->db->escape($key) . "' 
            AND store_id = '0'
        ");
        
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "setting SET 
            store_id = '0',
            `code` = 'meschain_sync',
            `key` = '" . $this->db->escape($key) . "',
            `value` = '" . $this->db->escape($value) . "'
        ");
    }
    
    /**
     * Cache temizle
     */
    private function clearCache() {
        echo "Cache temizleniyor...\n";
        
        // OpenCart cache
        $files = glob(DIR_CACHE . 'cache.*');
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        // Custom cache
        if (file_exists(DIR_SYSTEM . 'helper/cache_helper.php')) {
            require_once(DIR_SYSTEM . 'helper/cache_helper.php');
            $cache = CacheHelper::getInstance();
            $cache->clearAll();
        }
        
        echo "Cache temizlendi\n";
    }
}

// Migration'ı çalıştır
if (php_sapi_name() === 'cli') {
    // CLI'den çalıştırılıyor
    require_once('../config.php');
    require_once(DIR_SYSTEM . 'startup.php');
    
    // Registry
    $registry = new Registry();
    
    // Database
    $db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
    $registry->set('db', $db);
    
    // Migration
    $migration = new MesChainSyncMigration($db);
    $migration->migrate();
} else {
    echo "Bu script sadece komut satırından çalıştırılabilir!\n";
    echo "Kullanım: php migration/migrate_to_optimized.php\n";
} 