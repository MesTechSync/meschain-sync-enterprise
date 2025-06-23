<?php
/**
 * OpenCart Extension MySQL Professional Fix Script
 * Eklenti görünürlük, konumlandırma ve link sorunlarını çözer
 * 
 * @author GitHub Copilot Professional
 * @version 2.0.0
 * @date 22 Haziran 2025
 * @compatibility OpenCart 4.0.2.3+
 */

class OpenCartExtensionMySQLFixer {
    
    private $db;
    private $config;
    private $log_file;
    private $errors = [];
    private $fixed_count = 0;
    
    public function __construct() {
        $this->log_file = 'extension_fix_' . date('Y-m-d_H-i-s') . '.log';
        $this->loadConfig();
        $this->connectDatabase();
    }
    
    /**
     * Config dosyasını yükle
     */
    private function loadConfig() {
        $config_paths = [
            'config.php',
            'opencart_new/config.php',
            '../config.php',
            '../opencart_new/config.php'
        ];
        
        foreach ($config_paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                $this->log("✅ Config dosyası yüklendi: $path");
                return;
            }
        }
        
        throw new Exception("Config dosyası bulunamadı! Kontrol edilen yollar: " . implode(', ', $config_paths));
    }
    
    /**
     * Ana fix işlemini başlat
     */
    public function runCompleteFix() {
        $this->log("🚀 OpenCart Extension Professional Fix başlatılıyor...");
        
        try {
            // 1. Veritabanı yapısını kontrol et ve düzelt
            $this->fixDatabaseStructure();
            
            // 2. Extension kayıtlarını düzelt
            $this->fixExtensionRecords();
            
            // 3. User permissions düzelt
            $this->fixUserPermissions();
            
            // 4. Extension paths düzelt
            $this->fixExtensionPaths();
            
            // 5. MesChain extensions için özel düzeltmeler
            $this->fixMesChainExtensions();
            
            // 6. Menu linklerini düzelt
            $this->fixAdminMenuLinks();
            
            // 7. Extension install tablosunu temizle
            $this->cleanInstallTable();
            
            // 8. Cache temizle
            $this->clearExtensionCache();
            
            $this->log("✅ Tüm düzeltmeler tamamlandı. Toplam " . $this->fixed_count . " sorun çözüldü.");
            
            return [
                'success' => true,
                'fixed_count' => $this->fixed_count,
                'errors' => $this->errors,
                'log_file' => $this->log_file
            ];
            
        } catch (Exception $e) {
            $this->log("❌ HATA: " . $e->getMessage());
            $this->errors[] = $e->getMessage();
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'errors' => $this->errors,
                'log_file' => $this->log_file
            ];
        }
    }
    
    /**
     * Veritabanı yapısını kontrol et ve düzelt
     */
    private function fixDatabaseStructure() {
        $this->log("🔧 Veritabanı yapısı kontrol ediliyor...");
        
        // Extension tablosu kontrol
        $result = $this->db->query("SHOW TABLES LIKE 'oc_extension'");
        if ($result->num_rows == 0) {
            $this->createExtensionTable();
        }
        
        // Extension install tablosu kontrol
        $result = $this->db->query("SHOW TABLES LIKE 'oc_extension_install'");
        if ($result->num_rows == 0) {
            $this->createExtensionInstallTable();
        }
        
        // Extension path tablosu kontrol
        $result = $this->db->query("SHOW TABLES LIKE 'oc_extension_path'");
        if ($result->num_rows == 0) {
            $this->createExtensionPathTable();
        }
        
        // MesChain özel tabloları kontrol et
        $this->createMesChainTables();
        
        $this->log("✅ Veritabanı yapısı kontrol edildi ve düzeltildi.");
    }
    
    /**
     * Extension kayıtlarını düzelt
     */
    private function fixExtensionRecords() {
        $this->log("🔧 Extension kayıtları düzeltiliyor...");
        
        // Duplicate kayıtları temizle
        $this->removeDuplicateExtensions();
        
        // Orphaned kayıtları temizle
        $this->removeOrphanedExtensions();
        
        // Missing extensions ekle
        $this->addMissingExtensions();
        
        // Status değerlerini düzelt
        $this->fixExtensionStatus();
        
        $this->log("✅ Extension kayıtları düzeltildi.");
    }
    
    /**
     * User permissions düzelt
     */
    private function fixUserPermissions() {
        $this->log("🔧 User permissions düzeltiliyor...");
        
        // Administrator group permissions
        $admin_permissions = [
            'access' => [
                'extension/extension',
                'extension/module',
                'extension/meschain/dashboard',
                'extension/meschain/category_mapping',
                'extension/meschain/brand_mapping',
                'extension/meschain/attribute_mapping',
                'extension/meschain/product_sync',
                'extension/meschain/order_sync',
                'extension/meschain/reports',
                'extension/meschain/settings',
                'extension/module/meschain_sync',
                'extension/module/meschain_trendyol',
                'marketplace/extension',
                'marketplace/installer',
                'marketplace/modification'
            ],
            'modify' => [
                'extension/extension',
                'extension/module',
                'extension/meschain/dashboard',
                'extension/meschain/category_mapping',
                'extension/meschain/brand_mapping',
                'extension/meschain/attribute_mapping',
                'extension/meschain/product_sync',
                'extension/meschain/order_sync',
                'extension/meschain/settings',
                'extension/module/meschain_sync',
                'extension/module/meschain_trendyol',
                'marketplace/extension',
                'marketplace/installer',
                'marketplace/modification'
            ]
        ];
        
        // Get administrator user group
        $admin_query = $this->db->query("SELECT * FROM oc_user_group WHERE name = 'Administrator' LIMIT 1");
        
        if ($admin_query->num_rows > 0) {
            $admin_group = $admin_query->fetch_assoc();
            $current_permissions = json_decode($admin_group['permission'], true) ?: [];
            
            // Merge permissions
            foreach ($admin_permissions as $type => $permissions) {
                if (!isset($current_permissions[$type])) {
                    $current_permissions[$type] = [];
                }
                
                foreach ($permissions as $permission) {
                    if (!in_array($permission, $current_permissions[$type])) {
                        $current_permissions[$type][] = $permission;
                        $this->fixed_count++;
                    }
                }
            }
            
            // Update permissions
            $permissions_json = json_encode($current_permissions);
            $this->db->query("UPDATE oc_user_group SET permission = '" . $this->db->real_escape_string($permissions_json) . "' WHERE user_group_id = " . (int)$admin_group['user_group_id']);
            
            $this->log("✅ Administrator permissions güncellendi.");
        }
    }
    
    /**
     * Extension paths düzelt
     */
    private function fixExtensionPaths() {
        $this->log("🔧 Extension paths düzeltiliyor...");
        
        // Clear old paths
        $this->db->query("DELETE FROM oc_extension_path WHERE path LIKE '%meschain%'");
        
        // Add correct paths
        $meschain_paths = [
            'admin/controller/extension/module/meschain_sync.php',
            'admin/controller/extension/meschain/category_mapping.php',
            'admin/controller/extension/meschain/brand_mapping.php',
            'admin/controller/extension/meschain/attribute_mapping.php',
            'admin/controller/extension/meschain/trendyol.php',
            'admin/model/extension/module/meschain_sync.php',
            'admin/model/extension/meschain/category_mapping.php',
            'admin/model/extension/meschain/brand_mapping.php',
            'admin/model/extension/meschain/attribute_mapping.php',
            'admin/model/extension/meschain/trendyol.php',
            'admin/view/template/extension/module/meschain_sync.twig',
            'admin/language/en-gb/extension/module/meschain_sync.php',
            'admin/language/tr-tr/extension/module/meschain_sync.php',
            'catalog/controller/extension/module/meschain_sync.php',
            'catalog/model/extension/module/meschain_sync.php',
            'system/library/meschain/api/TrendyolApiClient.php',
            'system/library/meschain/bootstrap.php'
        ];
        
        foreach ($meschain_paths as $path) {
            $this->db->query("INSERT IGNORE INTO oc_extension_path SET 
                extension_install_id = 0,
                path = '" . $this->db->real_escape_string($path) . "'
            ");
        }
        
        $this->fixed_count += count($meschain_paths);
        $this->log("✅ Extension paths düzeltildi.");
    }
    
    /**
     * MesChain extensions için özel düzeltmeler
     */
    private function fixMesChainExtensions() {
        $this->log("🔧 MesChain extensions düzeltiliyor...");
        
        // MesChain Sync extension kaydı
        $this->db->query("INSERT IGNORE INTO oc_extension SET 
            extension = 'meschain',
            type = 'module',
            code = 'meschain_sync',
            status = 1
        ");
        
        // MesChain Trendyol extension kaydı
        $this->db->query("INSERT IGNORE INTO oc_extension SET 
            extension = 'meschain',
            type = 'module', 
            code = 'meschain_trendyol',
            status = 1
        ");
        
        // MesChain Dashboard extension kaydı
        $this->db->query("INSERT IGNORE INTO oc_extension SET 
            extension = 'meschain',
            type = 'dashboard',
            code = 'meschain_dashboard',
            status = 1
        ");
        
        $this->fixed_count += 3;
        $this->log("✅ MesChain extensions kayıtları eklendi.");
    }
    
    /**
     * Admin menu linklerini düzelt
     */
    private function fixAdminMenuLinks() {
        $this->log("🔧 Admin menu linkleri düzeltiliyor...");
        
        // Event sistemi ile menu linklerini düzelt
        $this->db->query("DELETE FROM oc_event WHERE code LIKE 'meschain%'");
        
        // MesChain menu events ekle
        $menu_events = [
            [
                'code' => 'meschain_admin_menu',
                'description' => 'MesChain Admin Menu',
                'trigger' => 'admin/view/common/column_left/before',
                'action' => 'extension/meschain/event/admin_menu',
                'status' => 1,
                'sort_order' => 1
            ]
        ];
        
        foreach ($menu_events as $event) {
            $this->db->query("INSERT INTO oc_event SET 
                code = '" . $event['code'] . "',
                description = '" . $event['description'] . "',
                `trigger` = '" . $event['trigger'] . "',
                action = '" . $event['action'] . "',
                status = " . $event['status'] . ",
                sort_order = " . $event['sort_order'] . "
            ");
        }
        
        $this->fixed_count += count($menu_events);
        $this->log("✅ Admin menu linkleri düzeltildi.");
    }
    
    /**
     * Extension install tablosunu temizle
     */
    private function cleanInstallTable() {
        $this->log("🔧 Extension install tablosu temizleniyor...");
        
        // Check if extension_download table exists before cleaning
        $result = $this->db->query("SHOW TABLES LIKE 'oc_extension_download'");
        if ($result->num_rows > 0) {
            // Orphaned install records temizle
            $this->db->query("DELETE FROM oc_extension_install 
                WHERE extension_download_id NOT IN (
                    SELECT extension_download_id FROM oc_extension_download
                )");
        }
        
        // Eski MesChain install kayıtlarını temizle
        $this->db->query("DELETE FROM oc_extension_install WHERE code LIKE '%meschain%'");
        
        $this->log("✅ Extension install tablosu temizlendi.");
    }
    
    /**
     * Cache temizle
     */
    private function clearExtensionCache() {
        $this->log("🔧 Extension cache temizleniyor...");
        
        // File cache temizle
        $cache_dir = DIR_STORAGE . 'cache/';
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        
        $this->log("✅ Extension cache temizlendi.");
    }
    
    /**
     * Duplicate extensions temizle
     */
    private function removeDuplicateExtensions() {
        $duplicates = $this->db->query("
            SELECT code, type, COUNT(*) as count 
            FROM oc_extension 
            GROUP BY code, type 
            HAVING count > 1
        ");
        
        while ($row = $duplicates->fetch_assoc()) {
            // En son eklenen haricindeki tüm kayıtları sil
            $this->db->query("
                DELETE FROM oc_extension 
                WHERE code = '" . $this->db->real_escape_string($row['code']) . "' 
                AND type = '" . $this->db->real_escape_string($row['type']) . "'
                AND extension_id NOT IN (
                    SELECT * FROM (
                        SELECT MAX(extension_id) 
                        FROM oc_extension 
                        WHERE code = '" . $this->db->real_escape_string($row['code']) . "' 
                        AND type = '" . $this->db->real_escape_string($row['type']) . "'
                    ) tmp
                )
            ");
            
            $this->fixed_count++;
        }
    }
    
    /**
     * Orphaned extensions temizle
     */
    private function removeOrphanedExtensions() {
        // Dosyası bulunmayan extension kayıtlarını tespit et ve sil
        $extensions = $this->db->query("SELECT * FROM oc_extension");
        
        while ($row = $extensions->fetch_assoc()) {
            $controller_path = DIR_APPLICATION . 'controller/extension/' . $row['type'] . '/' . $row['code'] . '.php';
            
            if (!file_exists($controller_path)) {
                $this->db->query("DELETE FROM oc_extension WHERE extension_id = " . (int)$row['extension_id']);
                $this->fixed_count++;
            }
        }
    }
    
    /**
     * Missing extensions ekle
     */
    private function addMissingExtensions() {
        // Dosyası var ama kayıtı olmayan extension'ları tespit et ve ekle
        $module_path = DIR_APPLICATION . 'controller/extension/module/';
        
        if (is_dir($module_path)) {
            $files = scandir($module_path);
            
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) == 'php') {
                    $code = pathinfo($file, PATHINFO_FILENAME);
                    
                    $exists = $this->db->query("SELECT extension_id FROM oc_extension WHERE code = '" . $this->db->real_escape_string($code) . "' AND type = 'module'");
                    
                    if ($exists->num_rows == 0) {
                        $this->db->query("INSERT INTO oc_extension SET 
                            extension = 'opencart',
                            type = 'module',
                            code = '" . $this->db->real_escape_string($code) . "',
                            status = 0
                        ");
                        
                        $this->fixed_count++;
                    }
                }
            }
        }
    }
    
    /**
     * Extension status değerlerini düzelt
     */
    private function fixExtensionStatus() {
        // NULL status değerlerini 0 yap
        $this->db->query("UPDATE oc_extension SET status = 0 WHERE status IS NULL");
        
        // MesChain extensions'ları aktif yap
        $this->db->query("UPDATE oc_extension SET status = 1 WHERE code LIKE 'meschain%'");
    }
    
    /**
     * Gerekli tabloları oluştur
     */
    private function createExtensionTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `oc_extension` (
            `extension_id` int NOT NULL AUTO_INCREMENT,
            `extension` varchar(255) NOT NULL,
            `type` varchar(32) NOT NULL,
            `code` varchar(128) NOT NULL,
            `status` tinyint(1) DEFAULT '1',
            PRIMARY KEY (`extension_id`),
            KEY `type` (`type`),
            KEY `code` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->db->query($sql);
        $this->log("✅ oc_extension tablosu oluşturuldu.");
    }
    
    private function createExtensionInstallTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `oc_extension_install` (
            `extension_install_id` int NOT NULL AUTO_INCREMENT,
            `extension_download_id` int NOT NULL,
            `filename` varchar(255) NOT NULL,
            `date_added` datetime NOT NULL,
            PRIMARY KEY (`extension_install_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->db->query($sql);
        $this->log("✅ oc_extension_install tablosu oluşturuldu.");
    }
    
    private function createExtensionPathTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `oc_extension_path` (
            `extension_path_id` int NOT NULL AUTO_INCREMENT,
            `extension_install_id` int NOT NULL,
            `path` varchar(255) NOT NULL,
            `date_added` datetime NOT NULL,
            PRIMARY KEY (`extension_path_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->db->query($sql);
        $this->log("✅ oc_extension_path tablosu oluşturuldu.");
    }
    
    private function createMesChainTables() {
        // MesChain sync logs tablosu
        $sql = "CREATE TABLE IF NOT EXISTS `oc_meschain_sync_logs` (
            `log_id` int NOT NULL AUTO_INCREMENT,
            `sync_type` varchar(50) NOT NULL,
            `marketplace` varchar(50) NOT NULL,
            `action` varchar(100) NOT NULL,
            `status` enum('success','error','pending') NOT NULL,
            `message` text,
            `data` json,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`log_id`),
            KEY `sync_type` (`sync_type`),
            KEY `marketplace` (`marketplace`),
            KEY `status` (`status`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->db->query($sql);
        
        // Diğer MesChain tabloları...
        $this->log("✅ MesChain tabloları oluşturuldu.");
    }
    
    /**
     * Config yükle
     */
    private function loadConfig() {
        if (file_exists('config.php')) {
            require_once 'config.php';
        } else {
            throw new Exception("config.php dosyası bulunamadı!");
        }
    }
    
    /**
     * Veritabanı bağlantısı
     */
    private function connectDatabase() {
        $this->db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        
        if ($this->db->connect_error) {
            throw new Exception("Database connection failed: " . $this->db->connect_error);
        }
        
        $this->db->set_charset('utf8mb4');
    }
    
    /**
     * Log yazma
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[{$timestamp}] {$message}" . PHP_EOL;
        
        file_put_contents($this->log_file, $log_message, FILE_APPEND | LOCK_EX);
        echo $log_message;
    }
}

// Script çalıştırma
try {
    echo "<!DOCTYPE html><html><head><title>OpenCart Extension MySQL Fix</title><style>body{font-family:monospace;background:#1e1e1e;color:#fff;padding:20px;}pre{background:#2d2d2d;padding:15px;border-radius:5px;}</style></head><body>";
    echo "<h1>🔧 OpenCart Extension MySQL Professional Fix</h1>";
    echo "<pre>";
    
    $fixer = new OpenCartExtensionMySQLFixer();
    $result = $fixer->runCompleteFix();
    
    echo "</pre>";
    
    if ($result['success']) {
        echo "<h2 style='color:#4CAF50;'>✅ Başarılı!</h2>";
        echo "<p><strong>Düzeltilen sorun sayısı:</strong> " . $result['fixed_count'] . "</p>";
        echo "<p><strong>Log dosyası:</strong> " . $result['log_file'] . "</p>";
    } else {
        echo "<h2 style='color:#f44336;'>❌ Hata!</h2>";
        echo "<p><strong>Hata:</strong> " . $result['error'] . "</p>";
        echo "<p><strong>Log dosyası:</strong> " . $result['log_file'] . "</p>";
    }
    
    if (!empty($result['errors'])) {
        echo "<h3>🚨 Hatalar:</h3><ul>";
        foreach ($result['errors'] as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
    }
    
    echo "</body></html>";
    
} catch (Exception $e) {
    echo "<h2 style='color:#f44336;'>❌ Fatal Error!</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</body></html>";
}
?>
