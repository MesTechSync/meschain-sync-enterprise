<?php
/**
 * MesChain-Sync Installation Script
 * Developer 1: Automated installation system
 */

// CLI veya Web'den çalışabilir
if (php_sapi_name() === 'cli') {
    // CLI modu
    $install_mode = 'cli';
} else {
    // Web modu
    $install_mode = 'web';
    
    // Web modunda HTML başlangıcı
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>MesChain-Sync Kurulum</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .step { margin: 15px 0; padding: 10px; border-left: 4px solid #007cba; background: #f8f9fa; }
            .success { border-left-color: #28a745; }
            .error { border-left-color: #dc3545; }
            .warning { border-left-color: #ffc107; }
            pre { background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 5px; overflow-x: auto; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🚀 MesChain-Sync Kurulum</h1>
            <p>Multi-User OpenCart Marketplace Integration System</p>';
}

class MesChainInstaller {
    private $db;
    private $config_file;
    private $base_path;
    private $errors = array();
    private $warnings = array();
    private $success_count = 0;
    
    public function __construct($base_path = null) {
        $this->base_path = $base_path ?: dirname(dirname(__FILE__));
        $this->config_file = $this->base_path . '/config.php';
        
        $this->log("🎯 MesChain-Sync Kurulum Başlatılıyor...", 'info');
        $this->log("📁 Base Path: " . $this->base_path, 'info');
    }
    
    /**
     * Ana kurulum fonksiyonu
     */
    public function install() {
        try {
            // 1. Ön kontroller
            $this->log("🔍 1. Sistem Kontrolü", 'step');
            $this->checkSystemRequirements();
            
            // 2. Veritabanı bağlantısı
            $this->log("🗄️ 2. Veritabanı Bağlantısı", 'step');
            $this->connectDatabase();
            
            // 3. Tabloları oluştur
            $this->log("📊 3. Veritabanı Tabloları", 'step');
            $this->createTables();
            
            // 4. Dosya izinleri
            $this->log("📝 4. Dosya İzinleri", 'step');
            $this->checkFilePermissions();
            
            // 5. Varsayılan veriler
            $this->log("🎛️ 5. Varsayılan Veriler", 'step');
            $this->insertDefaultData();
            
            // 6. Cron job kurulumu
            $this->log("⏰ 6. Cron Job Kurulumu", 'step');
            $this->setupCronJobs();
            
            // 7. Final kontroller
            $this->log("✅ 7. Final Kontroller", 'step');
            $this->finalChecks();
            
            // Özet
            $this->showSummary();
            
        } catch (Exception $e) {
            $this->log("❌ Kurulum Hatası: " . $e->getMessage(), 'error');
            $this->log("🔧 Lütfen hataları düzeltin ve tekrar deneyin.", 'warning');
        }
    }
    
    /**
     * Sistem gereksinimleri kontrolü
     */
    private function checkSystemRequirements() {
        // PHP versiyonu
        if (version_compare(PHP_VERSION, '7.2.0', '<')) {
            $this->addError("PHP 7.2.0 veya üstü gerekli. Mevcut: " . PHP_VERSION);
        } else {
            $this->log("✅ PHP Versiyonu: " . PHP_VERSION, 'success');
        }
        
        // Gerekli extensionlar
        $required_extensions = array('curl', 'json', 'mbstring', 'openssl', 'mysqli', 'zip');
        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $this->addError("PHP Extension gerekli: " . $ext);
            } else {
                $this->log("✅ Extension: " . $ext, 'success');
            }
        }
        
        // OpenCart kontrolü
        if (!file_exists($this->config_file)) {
            $this->addError("OpenCart config.php bulunamadı: " . $this->config_file);
        } else {
            $this->log("✅ OpenCart config.php bulundu", 'success');
        }
        
        // Memory limit
        $memory_limit = ini_get('memory_limit');
        $memory_mb = intval($memory_limit);
        if ($memory_mb < 256 && $memory_limit !== '-1') {
            $this->addWarning("Memory limit düşük: " . $memory_limit . " (256M önerilir)");
        } else {
            $this->log("✅ Memory Limit: " . $memory_limit, 'success');
        }
        
        // Max execution time
        $max_execution = ini_get('max_execution_time');
        if ($max_execution < 300 && $max_execution != 0) {
            $this->addWarning("Max execution time düşük: " . $max_execution . "s (300s önerilir)");
        } else {
            $this->log("✅ Max Execution Time: " . $max_execution . "s", 'success');
        }
    }
    
    /**
     * Veritabanı bağlantısı
     */
    private function connectDatabase() {
        require($this->config_file);
        
        try {
            $this->db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
            
            if ($this->db->connect_error) {
                throw new Exception("Veritabanı bağlantı hatası: " . $this->db->connect_error);
            }
            
            $this->db->set_charset("utf8");
            $this->log("✅ Veritabanı bağlantısı başarılı", 'success');
            
        } catch (Exception $e) {
            $this->addError("Veritabanı bağlantısı kurulamadı: " . $e->getMessage());
        }
    }
    
    /**
     * Tabloları oluştur
     */
    private function createTables() {
        $sql_files = array(
            dirname(__FILE__) . '/multi_user_tables.sql',
            dirname(__FILE__) . '/api_settings_table.sql'
        );
        
        $created_tables = 0;
        
        foreach ($sql_files as $sql_file) {
            if (!file_exists($sql_file)) {
                $this->addError("SQL dosyası bulunamadı: " . $sql_file);
                continue;
            }
            
            $sql_content = file_get_contents($sql_file);
            $sql_statements = explode(';', $sql_content);
            
            foreach ($sql_statements as $sql) {
                $sql = trim($sql);
                if (empty($sql) || strpos($sql, '--') === 0) continue;
                
                try {
                    if ($this->db->query($sql)) {
                        if (stripos($sql, 'CREATE TABLE') !== false) {
                            $created_tables++;
                            // Tablo adını extract et
                            preg_match('/CREATE TABLE.*?`([^`]+)`/i', $sql, $matches);
                            $table_name = isset($matches[1]) ? $matches[1] : 'Unknown';
                            $this->log("✅ Tablo oluşturuldu: " . $table_name, 'success');
                        }
                    } else {
                        $this->addError("SQL Hatası: " . $this->db->error . "\nSQL: " . substr($sql, 0, 100) . "...");
                    }
                } catch (Exception $e) {
                    $this->addError("Tablo oluşturma hatası: " . $e->getMessage());
                }
            }
        }
        
        $this->log("📊 Toplam " . $created_tables . " tablo oluşturuldu", 'info');
    }
    
    /**
     * Dosya izinleri kontrolü
     */
    private function checkFilePermissions() {
        $paths_to_check = array(
            'system/storage/cache' => 0755,
            'system/storage/logs' => 0755,
            'system/storage/session' => 0755,
            'system/storage/upload' => 0755,
            'image/cache' => 0755,
            'image/catalog' => 0755
        );
        
        foreach ($paths_to_check as $path => $required_permission) {
            $full_path = $this->base_path . '/' . $path;
            
            if (!is_dir($full_path)) {
                if (mkdir($full_path, $required_permission, true)) {
                    $this->log("✅ Klasör oluşturuldu: " . $path, 'success');
                } else {
                    $this->addError("Klasör oluşturulamadı: " . $path);
                    continue;
                }
            }
            
            $current_permission = fileperms($full_path) & 0777;
            if ($current_permission < $required_permission) {
                if (chmod($full_path, $required_permission)) {
                    $this->log("✅ İzin düzeltildi: " . $path . " (" . decoct($required_permission) . ")", 'success');
                } else {
                    $this->addWarning("İzin düzeltilemedi: " . $path . " (Manuel olarak " . decoct($required_permission) . " yapın)");
                }
            } else {
                $this->log("✅ İzin OK: " . $path . " (" . decoct($current_permission) . ")", 'success');
            }
        }
    }
    
    /**
     * Varsayılan veriler
     */
    private function insertDefaultData() {
        // İlk kullanıcıyı süper admin yap
        $first_user_query = "SELECT user_id FROM " . DB_PREFIX . "user ORDER BY user_id ASC LIMIT 1";
        $result = $this->db->query($first_user_query);
        
        if ($result && $result->num_rows > 0) {
            $user_id = $result->fetch_assoc()['user_id'];
            
            $default_admin_sql = "
                INSERT IGNORE INTO " . DB_PREFIX . "user_meschain_settings SET
                    user_id = '" . (int)$user_id . "',
                    role = 'super_admin',
                    marketplace_access = '" . json_encode(array('n11', 'trendyol', 'amazon', 'ebay', 'hepsiburada', 'ozon')) . "',
                    dropshipping_enabled = '1',
                    commission_rate = '0.00',
                    status = '1',
                    created_date = NOW(),
                    created_by = '" . (int)$user_id . "'
            ";
            
            if ($this->db->query($default_admin_sql)) {
                $this->log("✅ Varsayılan süper admin oluşturuldu (User ID: $user_id)", 'success');
            } else {
                $this->addWarning("Varsayılan süper admin oluşturulamadı");
            }
        }
        
        // Sync log tablosu oluştur (log sistemi için)
        $sync_log_sql = "
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_sync_log` (
              `log_id` INT(11) NOT NULL AUTO_INCREMENT,
              `user_id` INT(11) NOT NULL DEFAULT '0',
              `marketplace` VARCHAR(32) NOT NULL,
              `action` VARCHAR(64) NOT NULL,
              `status` ENUM('info','success','warning','error') NOT NULL DEFAULT 'info',
              `message` TEXT NOT NULL,
              `data` TEXT,
              `date_added` DATETIME NOT NULL,
              PRIMARY KEY (`log_id`),
              KEY `user_id` (`user_id`),
              KEY `marketplace` (`marketplace`),
              KEY `status` (`status`),
              KEY `date_added` (`date_added`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ";
        
        if ($this->db->query($sync_log_sql)) {
            $this->log("✅ Sync log tablosu oluşturuldu", 'success');
        }
    }
    
    /**
     * Cron job kurulumu
     */
    private function setupCronJobs() {
        $cron_script = $this->base_path . '/system/helper/cron_manager.php';
        
        if (!file_exists($cron_script)) {
            $this->addWarning("Cron script bulunamadı: " . $cron_script);
            return;
        }
        
        $this->log("📋 Cron Job komutları:", 'info');
        $this->log("# MesChain-Sync Cron Jobs", 'info');
        $this->log("# Her 15 dakikada bir tüm kullanıcılar için senkronizasyon", 'info');
        $this->log("*/15 * * * * /usr/bin/php " . $cron_script . " all", 'info');
        $this->log("", 'info');
        $this->log("# Günlük sistem temizliği (03:00)", 'info');
        $this->log("0 3 * * * /usr/bin/php " . $cron_script . " cleanup", 'info');
        $this->log("", 'info');
        $this->log("Bu komutları crontab'a manuel olarak eklemeniz gerekiyor:", 'warning');
        $this->log("crontab -e", 'warning');
    }
    
    /**
     * Final kontroller
     */
    private function finalChecks() {
        // Model dosyaları kontrolü
        $required_files = array(
            'admin/model/extension/module/user_management.php',
            'admin/controller/extension/module/user_management.php',
            'admin/controller/extension/module/base_marketplace.php',
            'admin/controller/extension/module/security_helper.php',
            'system/helper/cron_manager.php'
        );
        
        foreach ($required_files as $file) {
            $full_path = $this->base_path . '/' . $file;
            if (file_exists($full_path)) {
                $this->log("✅ Dosya: " . $file, 'success');
            } else {
                $this->addError("Eksik dosya: " . $file);
            }
        }
        
        // Veritabanı tabloları kontrolü
        $required_tables = array(
            'user_meschain_settings',
            'user_api_settings',
            'user_activity_log',
            'dropshipping_orders',
            'dropshipping_suppliers',
            'product_sync_queue'
        );
        
        foreach ($required_tables as $table) {
            $table_check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
            if ($table_check && $table_check->num_rows > 0) {
                $this->log("✅ Tablo: " . DB_PREFIX . $table, 'success');
            } else {
                $this->addError("Eksik tablo: " . DB_PREFIX . $table);
            }
        }
    }
    
    /**
     * Kurulum özeti
     */
    private function showSummary() {
        $this->log("", 'info');
        $this->log("🎉 KURULUM TAMAMLANDI!", 'step');
        $this->log("", 'info');
        
        if (!empty($this->errors)) {
            $this->log("❌ Hatalar (" . count($this->errors) . "):", 'error');
            foreach ($this->errors as $error) {
                $this->log("   • " . $error, 'error');
            }
        }
        
        if (!empty($this->warnings)) {
            $this->log("⚠️ Uyarılar (" . count($this->warnings) . "):", 'warning');
            foreach ($this->warnings as $warning) {
                $this->log("   • " . $warning, 'warning');
            }
        }
        
        $this->log("✅ Başarılı İşlemler: " . $this->success_count, 'success');
        
        if (empty($this->errors)) {
            $this->log("", 'info');
            $this->log("🚀 SONRAKI ADIMLAR:", 'step');
            $this->log("1. OpenCart admin panelinde Extensions > Modules > MesChain-Sync'i aktifleştirin", 'info');
            $this->log("2. User Management > Roles'de kullanıcı rollerini ayarlayın", 'info');
            $this->log("3. Her kullanıcı için API ayarlarını yapılandırın", 'info');
            $this->log("4. Cron job'ları sisteme ekleyin", 'info');
            $this->log("5. Test senkronizasyonu yapın", 'info');
            $this->log("", 'info');
            $this->log("📚 Dokümantasyon: DEVELOPER1_PROGRESS_REPORT.md", 'info');
        }
    }
    
    // Yardımcı fonksiyonlar
    private function addError($message) {
        $this->errors[] = $message;
        $this->log("❌ " . $message, 'error');
    }
    
    private function addWarning($message) {
        $this->warnings[] = $message;
        $this->log("⚠️ " . $message, 'warning');
    }
    
    private function log($message, $type = 'info') {
        global $install_mode;
        
        if ($type === 'success') {
            $this->success_count++;
        }
        
        if ($install_mode === 'cli') {
            echo $message . "\n";
        } else {
            $class = $type === 'step' ? '' : $type;
            echo '<div class="step ' . $class . '">' . htmlspecialchars($message) . '</div>';
            flush();
        }
    }
}

// Kurulumu başlat
try {
    $installer = new MesChainInstaller();
    $installer->install();
} catch (Exception $e) {
    if ($install_mode === 'cli') {
        echo "❌ Kritik Hata: " . $e->getMessage() . "\n";
    } else {
        echo '<div class="step error">❌ Kritik Hata: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Web modu için HTML sonu
if ($install_mode === 'web') {
    echo '
        </div>
    </body>
    </html>';
}
?> 