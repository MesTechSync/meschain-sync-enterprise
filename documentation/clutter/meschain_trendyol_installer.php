<?php
/**
 * MesChain-Sync Trendyol Integration Installer
 * Advanced Enterprise Installation Script v4.5.0
 *
 * Bu script, tüm Trendyol entegrasyon bileşenlerini OpenCart sistemine kurar
 *
 * @author MesChain Development Team
 * @version 4.5.0 Enterprise
 * @copyright 2024 MesChain Technologies
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if running in CLI or web
$isCLI = (php_sapi_name() === 'cli');

if (!$isCLI) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>MesChain-Sync Trendyol Installer</title>";
    echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:40px auto;padding:20px;background:#f5f5f5}";
    echo ".container{background:white;padding:30px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1)}";
    echo ".success{color:#27ae60;background:#d5f4e6;padding:10px;border-radius:4px;margin:10px 0}";
    echo ".error{color:#e74c3c;background:#fadbd8;padding:10px;border-radius:4px;margin:10px 0}";
    echo ".info{color:#3498db;background:#d6eaf8;padding:10px;border-radius:4px;margin:10px 0}";
    echo ".warning{color:#f39c12;background:#fcf3cf;padding:10px;border-radius:4px;margin:10px 0}";
    echo "pre{background:#2c3e50;color:#ecf0f1;padding:15px;border-radius:4px;overflow-x:auto}";
    echo ".progress{background:#ecf0f1;height:20px;border-radius:10px;margin:10px 0}";
    echo ".progress-bar{background:#27ae60;height:100%;border-radius:10px;transition:width 0.3s}";
    echo "</style></head><body><div class='container'>";
    echo "<h1>🚀 MesChain-Sync Trendyol Integration Installer</h1>";
}

class TrendyolInstaller {
    private $opencartPath;
    private $dbConfig;
    private $db;
    private $errors = [];
    private $warnings = [];
    private $installed = [];

    // Installation steps
    private $steps = [
        'check_requirements' => 'Sistem Gereksinimlerini Kontrol Et',
        'connect_database' => 'Veritabanına Bağlan',
        'backup_database' => 'Veritabanı Yedeklemesi Al',
        'create_tables' => 'Veritabanı Tablolarını Oluştur',
        'copy_files' => 'Dosyaları Kopyala',
        'install_ocmod' => 'OCMOD Modifikasyonlarını Yükle',
        'set_permissions' => 'İzinleri Ayarla',
        'configure_webhooks' => 'Webhook Yapılandırması',
        'test_installation' => 'Kurulumu Test Et',
        'cleanup' => 'Temizlik İşlemleri'
    ];

    private $currentStep = 0;

    public function __construct() {
        $this->detectOpenCartPath();
        $this->loadDatabaseConfig();
    }

    /**
     * Ana kurulum fonksiyonu
     */
    public function install() {
        $this->printHeader();

        foreach ($this->steps as $stepKey => $stepName) {
            $this->currentStep++;
            $this->printStepHeader($stepName);

            $result = $this->executeStep($stepKey);

            if (!$result) {
                $this->printError("❌ Adım başarısız: {$stepName}");
                $this->printErrors();
                return false;
            }

            $this->printSuccess("✅ Tamamlandı: {$stepName}");
            $this->printProgress();
        }

        $this->printInstallationComplete();
        return true;
    }

    /**
     * OpenCart yolunu tespit et
     */
    private function detectOpenCartPath() {
        // Mevcut dizinde config.php var mı kontrol et
        if (file_exists('config.php')) {
            $this->opencartPath = getcwd();
            return;
        }

        // upload/ dizininde mi kontrol et
        if (file_exists('upload/config.php')) {
            $this->opencartPath = getcwd() . '/upload';
            return;
        }

        // Bir üst dizinde kontrol et
        if (file_exists('../config.php')) {
            $this->opencartPath = dirname(getcwd());
            return;
        }

        $this->errors[] = 'OpenCart kurulum dizini bulunamadı. config.php dosyası mevcut değil.';
    }

    /**
     * Veritabanı konfigürasyonunu yükle
     */
    private function loadDatabaseConfig() {
        if (!$this->opencartPath) {
            return;
        }

        $configFile = $this->opencartPath . '/config.php';

        if (!file_exists($configFile)) {
            $this->errors[] = "Config dosyası bulunamadı: {$configFile}";
            return;
        }

        // Config dosyasını güvenli şekilde oku
        $configContent = file_get_contents($configFile);

        // DB ayarlarını parse et
        preg_match("/define\('DB_HOSTNAME', '(.+?)'\);/", $configContent, $hostname);
        preg_match("/define\('DB_USERNAME', '(.+?)'\);/", $configContent, $username);
        preg_match("/define\('DB_PASSWORD', '(.+?)'\);/", $configContent, $password);
        preg_match("/define\('DB_DATABASE', '(.+?)'\);/", $configContent, $database);
        preg_match("/define\('DB_PREFIX', '(.+?)'\);/", $configContent, $prefix);

        $this->dbConfig = [
            'hostname' => $hostname[1] ?? 'localhost',
            'username' => $username[1] ?? '',
            'password' => $password[1] ?? '',
            'database' => $database[1] ?? '',
            'prefix' => $prefix[1] ?? 'oc_'
        ];
    }

    /**
     * Adım çalıştır
     */
    private function executeStep($stepKey) {
        switch ($stepKey) {
            case 'check_requirements':
                return $this->checkRequirements();
            case 'connect_database':
                return $this->connectDatabase();
            case 'backup_database':
                return $this->backupDatabase();
            case 'create_tables':
                return $this->createTables();
            case 'copy_files':
                return $this->copyFiles();
            case 'install_ocmod':
                return $this->installOCMOD();
            case 'set_permissions':
                return $this->setPermissions();
            case 'configure_webhooks':
                return $this->configureWebhooks();
            case 'test_installation':
                return $this->testInstallation();
            case 'cleanup':
                return $this->cleanup();
            default:
                return false;
        }
    }

    /**
     * Sistem gereksinimlerini kontrol et
     */
    private function checkRequirements() {
        $this->printInfo("PHP versiyonu kontrol ediliyor...");

        // PHP versiyonu
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $this->errors[] = 'PHP 7.4 veya üzeri gerekli. Mevcut versiyon: ' . PHP_VERSION;
            return false;
        }

        // Gerekli eklentiler
        $requiredExtensions = ['mysqli', 'curl', 'json', 'mbstring', 'zip'];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $this->errors[] = "Gerekli PHP eklentisi bulunamadı: {$ext}";
                return false;
            }
        }

        // OpenCart dizini
        if (!$this->opencartPath) {
            $this->errors[] = 'OpenCart kurulum dizini bulunamadı';
            return false;
        }

        // Yazma izinleri
        $writableDirs = [
            $this->opencartPath . '/admin',
            $this->opencartPath . '/catalog',
            $this->opencartPath . '/system'
        ];

        foreach ($writableDirs as $dir) {
            if (!is_writable($dir)) {
                $this->warnings[] = "Dizin yazılabilir değil: {$dir}";
            }
        }

        $this->printInfo("✓ PHP " . PHP_VERSION . " uyumlu");
        $this->printInfo("✓ Gerekli eklentiler mevcut");
        $this->printInfo("✓ OpenCart dizini: " . $this->opencartPath);

        return true;
    }

    /**
     * Veritabanına bağlan
     */
    private function connectDatabase() {
        if (!$this->dbConfig) {
            $this->errors[] = 'Veritabanı konfigürasyonu yüklenemedi';
            return false;
        }

        $this->printInfo("Veritabanına bağlanılıyor: " . $this->dbConfig['database']);

        try {
            $this->db = new mysqli(
                $this->dbConfig['hostname'],
                $this->dbConfig['username'],
                $this->dbConfig['password'],
                $this->dbConfig['database']
            );

            if ($this->db->connect_error) {
                throw new Exception('Bağlantı hatası: ' . $this->db->connect_error);
            }

            $this->db->set_charset('utf8mb4');

            $this->printInfo("✓ Veritabanı bağlantısı başarılı");
            return true;

        } catch (Exception $e) {
            $this->errors[] = 'Veritabanı bağlantı hatası: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Veritabanı yedeklemesi al
     */
    private function backupDatabase() {
        $this->printInfo("Mevcut Trendyol tabloları yedekleniyor...");

        $backupDir = $this->opencartPath . '/system/storage/backup';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupFile = $backupDir . '/trendyol_backup_' . date('Y-m-d_H-i-s') . '.sql';

        $tables = [
            'trendyol_order',
            'trendyol_order_product',
            'trendyol_order_relation',
            'trendyol_products',
            'trendyol_webhook_logs',
            'trendyol_webhook_config'
        ];

        $backupContent = "-- MesChain Trendyol Backup " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($tables as $table) {
            $fullTableName = $this->dbConfig['prefix'] . $table;

            // Tablo var mı kontrol et
            $result = $this->db->query("SHOW TABLES LIKE '{$fullTableName}'");
            if ($result && $result->num_rows > 0) {
                $this->printInfo("Yedekleniyor: {$fullTableName}");

                // Tablo yapısını al
                $createResult = $this->db->query("SHOW CREATE TABLE `{$fullTableName}`");
                if ($createResult && $row = $createResult->fetch_assoc()) {
                    $backupContent .= "DROP TABLE IF EXISTS `{$fullTableName}`;\n";
                    $backupContent .= $row['Create Table'] . ";\n\n";
                }

                // Veriyi al
                $dataResult = $this->db->query("SELECT * FROM `{$fullTableName}`");
                if ($dataResult && $dataResult->num_rows > 0) {
                    while ($row = $dataResult->fetch_assoc()) {
                        $values = array_map(function($value) {
                            return $value === null ? 'NULL' : "'" . $this->db->real_escape_string($value) . "'";
                        }, $row);

                        $backupContent .= "INSERT INTO `{$fullTableName}` VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $backupContent .= "\n";
                }
            }
        }

        if (file_put_contents($backupFile, $backupContent)) {
            $this->printInfo("✓ Yedek dosyası oluşturuldu: " . basename($backupFile));
            return true;
        } else {
            $this->warnings[] = 'Yedek dosyası oluşturulamadı';
            return true; // Non-critical
        }
    }

    /**
     * Veritabanı tablolarını oluştur
     */
    private function createTables() {
        $this->printInfo("Trendyol tabloları oluşturuluyor...");

        $tables = $this->getTrendyolTables();

        foreach ($tables as $tableName => $sql) {
            $fullTableName = $this->dbConfig['prefix'] . $tableName;
            $sql = str_replace('{{TABLE_NAME}}', $fullTableName, $sql);

            $this->printInfo("Oluşturuluyor: {$fullTableName}");

            if (!$this->db->query($sql)) {
                $this->errors[] = "Tablo oluşturma hatası ({$tableName}): " . $this->db->error;
                return false;
            }
        }

        $this->printInfo("✓ Tüm tablolar başarıyla oluşturuldu");
        return true;
    }

    /**
     * Dosyaları kopyala
     */
    private function copyFiles() {
        $this->printInfo("Trendyol dosyaları kopyalanıyor...");

        $filesToCopy = $this->getFilesToCopy();

        foreach ($filesToCopy as $source => $destination) {
            $destPath = $this->opencartPath . '/' . $destination;

            // Hedef dizini oluştur
            $destDir = dirname($destPath);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            if (file_exists($source)) {
                if (copy($source, $destPath)) {
                    $this->printInfo("✓ Kopyalandı: " . basename($destination));
                } else {
                    $this->errors[] = "Dosya kopyalanamadı: {$source} -> {$destPath}";
                    return false;
                }
            } else {
                $this->warnings[] = "Kaynak dosya bulunamadı: {$source}";
            }
        }

        return true;
    }

    /**
     * OCMOD yükle
     */
    private function installOCMOD() {
        $this->printInfo("OCMOD modifikasyonları yükleniyor...");

        $ocmodFile = 'install.xml';

        if (!file_exists($ocmodFile)) {
            $this->warnings[] = 'OCMOD dosyası bulunamadı. Manuel olarak yüklemeniz gerekebilir.';
            return true;
        }

        // OCMOD içeriğini oku ve işle
        $ocmodContent = file_get_contents($ocmodFile);

        // Basit OCMOD parser (gerçek uygulamada daha gelişmiş olmalı)
        $this->printInfo("✓ OCMOD dosyası hazır: " . basename($ocmodFile));
        $this->printWarning("⚠️  OCMOD'u admin panelden manuel olarak yüklemeyi unutmayın!");

        return true;
    }

    /**
     * İzinleri ayarla
     */
    private function setPermissions() {
        $this->printInfo("Dosya izinleri ayarlanıyor...");

        $permissions = [
            'admin/controller/extension/module/trendyol.php' => 0644,
            'admin/model/extension/module/trendyol.php' => 0644,
            'catalog/controller/extension/module/trendyol_webhook.php' => 0644,
            'system/library/meschain/api/TrendyolApiClient.php' => 0644,
            'system/library/meschain/webhook/TrendyolWebhookHandler.php' => 0644,
            'system/library/meschain/helper/trendyol.php' => 0644
        ];

        foreach ($permissions as $file => $permission) {
            $fullPath = $this->opencartPath . '/' . $file;
            if (file_exists($fullPath)) {
                chmod($fullPath, $permission);
                $this->printInfo("✓ İzin ayarlandı: " . basename($file));
            }
        }

        return true;
    }

    /**
     * Webhook yapılandırması
     */
    private function configureWebhooks() {
        $this->printInfo("Webhook yapılandırması oluşturuluyor...");

        // Varsayılan webhook konfigürasyonları
        $webhookEvents = [
            'ORDER_CREATED' => ['enabled' => 1, 'auto_process' => 1, 'retry_count' => 3],
            'ORDER_CANCELLED' => ['enabled' => 1, 'auto_process' => 1, 'retry_count' => 3],
            'ORDER_STATUS_CHANGED' => ['enabled' => 1, 'auto_process' => 1, 'retry_count' => 3],
            'PRODUCT_APPROVED' => ['enabled' => 1, 'auto_process' => 1, 'retry_count' => 2],
            'PRODUCT_REJECTED' => ['enabled' => 1, 'auto_process' => 1, 'retry_count' => 2],
            'INVENTORY_UPDATED' => ['enabled' => 1, 'auto_process' => 1, 'retry_count' => 3],
            'PRICE_UPDATED' => ['enabled' => 1, 'auto_process' => 1, 'retry_count' => 3],
            'SHIPMENT_CREATED' => ['enabled' => 1, 'auto_process' => 1, 'retry_count' => 3],
            'RETURN_INITIATED' => ['enabled' => 1, 'auto_process' => 1, 'retry_count' => 3]
        ];

        foreach ($webhookEvents as $eventType => $config) {
            $sql = "INSERT IGNORE INTO `" . $this->dbConfig['prefix'] . "trendyol_webhook_config`
                    (event_type, enabled, auto_process, retry_count, created_at, updated_at)
                    VALUES ('{$eventType}', {$config['enabled']}, {$config['auto_process']}, {$config['retry_count']}, NOW(), NOW())";

            if (!$this->db->query($sql)) {
                $this->warnings[] = "Webhook konfigürasyonu eklenemedi: {$eventType}";
            } else {
                $this->printInfo("✓ Webhook yapılandırıldı: {$eventType}");
            }
        }

        return true;
    }

    /**
     * Kurulumu test et
     */
    private function testInstallation() {
        $this->printInfo("Kurulum test ediliyor...");

        // Tabloları kontrol et
        $tables = ['trendyol_products', 'trendyol_webhook_logs', 'trendyol_webhook_config'];
        foreach ($tables as $table) {
            $fullTableName = $this->dbConfig['prefix'] . $table;
            $result = $this->db->query("SHOW TABLES LIKE '{$fullTableName}'");
            if (!$result || $result->num_rows === 0) {
                $this->errors[] = "Tablo bulunamadı: {$fullTableName}";
                return false;
            }
        }

        // Dosyaları kontrol et
        $criticalFiles = [
            'admin/controller/extension/module/trendyol.php',
            'admin/model/extension/module/trendyol.php',
            'system/library/meschain/api/TrendyolApiClient.php'
        ];

        foreach ($criticalFiles as $file) {
            $fullPath = $this->opencartPath . '/' . $file;
            if (!file_exists($fullPath)) {
                $this->errors[] = "Kritik dosya bulunamadı: {$file}";
                return false;
            }
        }

        $this->printInfo("✓ Tüm testler başarılı");
        return true;
    }

    /**
     * Temizlik işlemleri
     */
    private function cleanup() {
        $this->printInfo("Temizlik işlemleri yapılıyor...");

        // Geçici dosyaları temizle
        $tempFiles = glob('/tmp/trendyol_*');
        foreach ($tempFiles as $tempFile) {
            if (is_file($tempFile)) {
                unlink($tempFile);
            }
        }

        $this->printInfo("✓ Temizlik tamamlandı");
        return true;
    }

    /**
     * Trendyol tablolarını tanımla
     */
    private function getTrendyolTables() {
        return [
            'trendyol_products' => "
                CREATE TABLE IF NOT EXISTS `{{TABLE_NAME}}` (
                    `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
                    `opencart_product_id` int(11) NOT NULL,
                    `trendyol_product_id` varchar(100) DEFAULT NULL,
                    `barcode` varchar(100) NOT NULL,
                    `content_id` varchar(100) DEFAULT NULL,
                    `approved` tinyint(1) DEFAULT 0,
                    `status` enum('pending','approved','rejected','passive') DEFAULT 'pending',
                    `category_id` int(11) DEFAULT NULL,
                    `brand_id` int(11) DEFAULT NULL,
                    `last_sync` datetime DEFAULT NULL,
                    `sync_status` enum('synced','pending','error') DEFAULT 'pending',
                    `error_message` text,
                    `rejection_reason` text,
                    `list_price` decimal(15,4) DEFAULT NULL,
                    `sale_price` decimal(15,4) DEFAULT NULL,
                    `quantity` int(11) DEFAULT 0,
                    `tenant_id` int(11) DEFAULT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`mapping_id`),
                    UNIQUE KEY `barcode_unique` (`barcode`),
                    KEY `opencart_product_id` (`opencart_product_id`),
                    KEY `trendyol_product_id` (`trendyol_product_id`),
                    KEY `status` (`status`),
                    KEY `sync_status` (`sync_status`),
                    KEY `tenant_id` (`tenant_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ",

            'trendyol_orders' => "
                CREATE TABLE IF NOT EXISTS `{{TABLE_NAME}}` (
                    `trendyol_order_id` int(11) NOT NULL AUTO_INCREMENT,
                    `opencart_order_id` int(11) DEFAULT NULL,
                    `shipment_package_id` varchar(100) DEFAULT NULL,
                    `order_number` varchar(100) NOT NULL,
                    `gross_amount` decimal(15,4) NOT NULL DEFAULT 0,
                    `total_discount` decimal(15,4) DEFAULT 0,
                    `total_tax` decimal(15,4) DEFAULT 0,
                    `delivery_type` varchar(50) DEFAULT NULL,
                    `time_slot_id` varchar(50) DEFAULT NULL,
                    `estimated_delivery` datetime DEFAULT NULL,
                    `status` varchar(50) NOT NULL,
                    `order_date` datetime NOT NULL,
                    `cargo_tracking_number` varchar(100) DEFAULT NULL,
                    `cargo_provider_name` varchar(100) DEFAULT NULL,
                    `lines` json DEFAULT NULL,
                    `customer_info` json DEFAULT NULL,
                    `invoice_address` json DEFAULT NULL,
                    `delivery_address` json DEFAULT NULL,
                    `sync_status` enum('pending','synced','error') DEFAULT 'pending',
                    `error_message` text,
                    `tenant_id` int(11) DEFAULT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`trendyol_order_id`),
                    UNIQUE KEY `order_number_unique` (`order_number`),
                    KEY `opencart_order_id` (`opencart_order_id`),
                    KEY `status` (`status`),
                    KEY `order_date` (`order_date`),
                    KEY `sync_status` (`sync_status`),
                    KEY `tenant_id` (`tenant_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ",

            'trendyol_webhook_logs' => "
                CREATE TABLE IF NOT EXISTS `{{TABLE_NAME}}` (
                    `log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `event_type` varchar(100) NOT NULL,
                    `event_data` json NOT NULL,
                    `signature` varchar(255) DEFAULT NULL,
                    `processed` tinyint(1) DEFAULT 0,
                    `processed_at` datetime DEFAULT NULL,
                    `error_message` text,
                    `response_sent` text,
                    `ip_address` varchar(45) DEFAULT NULL,
                    `user_agent` varchar(255) DEFAULT NULL,
                    `received_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`log_id`),
                    KEY `event_type` (`event_type`),
                    KEY `processed` (`processed`),
                    KEY `received_at` (`received_at`),
                    KEY `event_processed` (`event_type`, `processed`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ",

            'trendyol_webhook_config' => "
                CREATE TABLE IF NOT EXISTS `{{TABLE_NAME}}` (
                    `config_id` int(11) NOT NULL AUTO_INCREMENT,
                    `event_type` varchar(100) NOT NULL,
                    `enabled` tinyint(1) DEFAULT 1,
                    `auto_process` tinyint(1) DEFAULT 1,
                    `retry_count` int(11) DEFAULT 3,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`config_id`),
                    UNIQUE KEY `event_type_unique` (`event_type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ",

            'trendyol_analytics' => "
                CREATE TABLE IF NOT EXISTS `{{TABLE_NAME}}` (
                    `analytics_id` int(11) NOT NULL AUTO_INCREMENT,
                    `metric_type` varchar(100) NOT NULL,
                    `metric_value` decimal(15,4) NOT NULL,
                    `metric_data` json DEFAULT NULL,
                    `date_recorded` date NOT NULL,
                    `hour_recorded` tinyint(2) DEFAULT NULL,
                    `tenant_id` int(11) DEFAULT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`analytics_id`),
                    KEY `metric_type` (`metric_type`),
                    KEY `date_recorded` (`date_recorded`),
                    KEY `metric_date` (`metric_type`, `date_recorded`),
                    KEY `tenant_id` (`tenant_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            "
        ];
    }

    /**
     * Kopyalanacak dosyaları tanımla
     */
    private function getFilesToCopy() {
        return [
            // Controller files
            'upload/admin/controller/extension/module/trendyol.php' => 'admin/controller/extension/module/trendyol.php',
            'upload/admin/controller/extension/module/trendyol_advanced.php' => 'admin/controller/extension/module/trendyol_advanced.php',
            'upload/catalog/controller/extension/module/trendyol_webhook.php' => 'catalog/controller/extension/module/trendyol_webhook.php',

            // Model files
            'upload/admin/model/extension/module/trendyol.php' => 'admin/model/extension/module/trendyol.php',
            'upload/admin/model/extension/module/trendyol_advanced.php' => 'admin/model/extension/module/trendyol_advanced.php',

            // System libraries
            'upload/system/library/meschain/api/TrendyolApiClient.php' => 'system/library/meschain/api/TrendyolApiClient.php',
            'upload/system/library/meschain/webhook/TrendyolWebhookHandler.php' => 'system/library/meschain/webhook/TrendyolWebhookHandler.php',
            'upload/system/library/meschain/helper/trendyol.php' => 'system/library/meschain/helper/trendyol.php',

            // Template files
            'upload/admin/view/template/extension/module/trendyol.twig' => 'admin/view/template/extension/module/trendyol.twig',
            'upload/admin/view/template/extension/module/trendyol_advanced.twig' => 'admin/view/template/extension/module/trendyol_advanced.twig',

            // Language files
            'upload/admin/language/tr-tr/extension/module/trendyol.php' => 'admin/language/tr-tr/extension/module/trendyol.php',
            'upload/admin/language/en-gb/extension/module/trendyol.php' => 'admin/language/en-gb/extension/module/trendyol.php',
            'upload/admin/language/tr-tr/extension/module/trendyol_advanced.php' => 'admin/language/tr-tr/extension/module/trendyol_advanced.php',
            'upload/admin/language/en-gb/extension/module/trendyol_advanced.php' => 'admin/language/en-gb/extension/module/trendyol_advanced.php',

            // JavaScript files
            'upload/admin/view/javascript/meschain/trendyol_advanced.js' => 'admin/view/javascript/meschain/trendyol_advanced.js'
        ];
    }

    // Output methods
    private function printHeader() {
        if (php_sapi_name() === 'cli') {
            echo "=== MesChain-Sync Trendyol Integration Installer v4.5.0 ===\n\n";
        } else {
            echo "<h2>🔧 Kurulum İşlemi Başlıyor...</h2>";
        }
    }

    private function printStepHeader($stepName) {
        if (php_sapi_name() === 'cli') {
            echo "[{$this->currentStep}/" . count($this->steps) . "] {$stepName}\n";
        } else {
            echo "<h3>[{$this->currentStep}/" . count($this->steps) . "] {$stepName}</h3>";
        }
    }

    private function printProgress() {
        if (php_sapi_name() !== 'cli') {
            $percentage = ($this->currentStep / count($this->steps)) * 100;
            echo "<div class='progress'><div class='progress-bar' style='width: {$percentage}%'></div></div>";
        }
    }

    private function printSuccess($message) {
        if (php_sapi_name() === 'cli') {
            echo "✓ {$message}\n";
        } else {
            echo "<div class='success'>{$message}</div>";
        }
    }

    private function printError($message) {
        if (php_sapi_name() === 'cli') {
            echo "✗ {$message}\n";
        } else {
            echo "<div class='error'>{$message}</div>";
        }
    }

    private function printWarning($message) {
        if (php_sapi_name() === 'cli') {
            echo "⚠ {$message}\n";
        } else {
            echo "<div class='warning'>{$message}</div>";
        }
    }

    private function printInfo($message) {
        if (php_sapi_name() === 'cli') {
            echo "ℹ {$message}\n";
        } else {
            echo "<div class='info'>{$message}</div>";
        }
    }

    private function printErrors() {
        foreach ($this->errors as $error) {
            $this->printError($error);
        }
    }

    private function printInstallationComplete() {
        if (php_sapi_name() === 'cli') {
            echo "\n=== KURULUM TAMAMLANDI ===\n";
            echo "🎉 MesChain-Sync Trendyol entegrasyonu başarıyla kuruldu!\n\n";
            echo "Sonraki adımlar:\n";
            echo "1. OpenCart admin paneline giriş yapın\n";
            echo "2. Extensions > Installer'dan install.xml dosyasını yükleyin\n";
            echo "3. Extensions > Modifications'dan Refresh butonuna tıklayın\n";
            echo "4. Extensions > Extensions > MesChain SYNC'den Trendyol modülünü etkinleştirin\n";
            echo "5. Trendyol API ayarlarınızı yapılandırın\n\n";
        } else {
            echo "<div class='success'>";
            echo "<h2>🎉 Kurulum Başarıyla Tamamlandı!</h2>";
            echo "<h3>Sonraki Adımlar:</h3>";
            echo "<ol>";
            echo "<li>OpenCart admin paneline giriş yapın</li>";
            echo "<li><strong>Extensions > Installer</strong>'dan <code>install.xml</code> dosyasını yükleyin</li>";
            echo "<li><strong>Extensions > Modifications</strong>'dan <strong>Refresh</strong> butonuna tıklayın</li>";
            echo "<li><strong>Extensions > Extensions > MesChain SYNC</strong>'den Trendyol modülünü etkinleştirin</li>";
            echo "<li>Trendyol API ayarlarınızı yapılandırın</li>";
            echo "</ol>";
            echo "<h3>Webhook URL'niz:</h3>";
            echo "<pre>https://yourstore.com/index.php?route=extension/module/trendyol_webhook</pre>";
            echo "</div>";
        }
    }
}

// Kurulumu başlat
try {
    $installer = new TrendyolInstaller();
    $result = $installer->install();

    if (!$result) {
        exit(1);
    }

} catch (Exception $e) {
    if (php_sapi_name() === 'cli') {
        echo "FATAL ERROR: " . $e->getMessage() . "\n";
    } else {
        echo "<div class='error'><strong>FATAL ERROR:</strong> " . $e->getMessage() . "</div>";
    }
    exit(1);
}

if (php_sapi_name() !== 'cli') {
    echo "</div></body></html>";
}
