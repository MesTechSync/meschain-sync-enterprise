<?php
/**
 * MesChain SYNC Core Installation Script
 * OpenCart 3.0.4.0+ Compatible
 *
 * Bu betik MesChain SYNC sistemi için gereken tüm yapılandırmaları yapar:
 * - OpenCart extension sistemi entegrasyonu
 * - Veritabanı yapısı oluşturma
 * - MesChain SYNC kategorisi oluşturma
 * - Gerekli izinlerin verilmesi
 * - Sistem dosyalarının kontrolü
 *
 * @author MesChain Development Team
 * @version 2.0.0
 * @since 2024-06-20
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'opencart');
define('DB_PREFIX', 'oc_');

class MesChainCoreInstaller {

    private $db;
    private $errors = [];
    private $warnings = [];
    private $success = [];

    public function __construct() {
        $this->connectDatabase();
    }

    /**
     * Veritabanı bağlantısı
     */
    private function connectDatabase() {
        try {
            $this->db = new PDO(
                "mysql:host=" . DB_HOSTNAME . ";dbname=" . DB_DATABASE . ";charset=utf8mb4",
                DB_USERNAME,
                DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            $this->success[] = "✅ Veritabanı bağlantısı başarılı";
        } catch (PDOException $e) {
            $this->errors[] = "❌ Veritabanı bağlantı hatası: " . $e->getMessage();
            exit;
        }
    }

    /**
     * Ana kurulum işlemini başlat
     */
    public function install() {
        echo "🚀 MesChain SYNC Core Installation Started\n";
        echo "==========================================\n\n";

        // Sistem gereksinimleri kontrolü
        $this->checkSystemRequirements();

        // Mevcut yapıyı temizle
        $this->cleanupExistingStructure();

        // OpenCart extension type oluştur
        $this->createExtensionType();

        // Veritabanı tablolarını oluştur
        $this->createDatabaseTables();

        // Extension'ları kaydet
        $this->registerExtensions();

        // Kullanıcı izinlerini ayarla
        $this->setupUserPermissions();

        // Varsayılan ayarları oluştur
        $this->createDefaultSettings();

        // Sistem dosyalarını kontrol et
        $this->verifySystemFiles();

        // Cron job'ları oluştur
        $this->setupCronJobs();

        // Sonuçları göster
        $this->displayResults();
    }

    /**
     * Sistem gereksinimleri kontrolü
     */
    private function checkSystemRequirements() {
        echo "🔍 Sistem Gereksinimleri Kontrolü...\n";

        // PHP versiyon kontrolü
        if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
            $this->success[] = "✅ PHP Version: " . PHP_VERSION . " (OK)";
        } else {
            $this->errors[] = "❌ PHP 7.4+ gerekli. Mevcut: " . PHP_VERSION;
        }

        // Gerekli extension'lar
        $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'curl', 'openssl'];
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                $this->success[] = "✅ PHP Extension: {$ext} (OK)";
            } else {
                $this->errors[] = "❌ Gerekli PHP extension eksik: {$ext}";
            }
        }

        // Dizin yazma izinleri
        $writableDirs = [
            'system/storage/logs',
            'system/storage/cache',
            'admin/view/template/extension/module',
            'admin/language',
            'system/library'
        ];

        foreach ($writableDirs as $dir) {
            if (is_writable($dir)) {
                $this->success[] = "✅ Yazma izni: {$dir} (OK)";
            } else {
                $this->warnings[] = "⚠️ Yazma izni gerekli: {$dir}";
            }
        }

        echo "\n";
    }

    /**
     * Mevcut yapıyı temizle
     */
    private function cleanupExistingStructure() {
        echo "🧹 Mevcut Yapı Temizleniyor...\n";

        try {
            // Eski extension kayıtlarını sil
            $stmt = $this->db->prepare("DELETE FROM `" . DB_PREFIX . "extension` WHERE `code` IN ('meschain_sync', 'trendyol', 'amazon', 'n11', 'ebay', 'hepsiburada', 'ozon')");
            $stmt->execute();

            // Eski extension_path kayıtlarını sil
            $stmt = $this->db->prepare("DELETE FROM `" . DB_PREFIX . "extension_path` WHERE `path` = 'extension/meschain_sync'");
            $stmt->execute();

            // Eski setting'leri temizle
            $stmt = $this->db->prepare("DELETE FROM `" . DB_PREFIX . "setting` WHERE `store_id` = 0 AND `code` LIKE 'module_meschain_%'");
            $stmt->execute();

            $this->success[] = "✅ Eski yapı temizlendi";

        } catch (PDOException $e) {
            $this->warnings[] = "⚠️ Temizlik sırasında uyarı: " . $e->getMessage();
        }

        echo "\n";
    }

    /**
     * OpenCart extension type oluştur
     */
    private function createExtensionType() {
        echo "📦 MesChain SYNC Extension Type Oluşturuluyor...\n";

        try {
            // Extension path tablosuna özel tip ekle
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO `" . DB_PREFIX . "extension_path`
                (`extension_path_id`, `path`, `value`)
                VALUES (NULL, 'extension/meschain_sync', 'extension/meschain_sync')
            ");
            $stmt->execute();

            $this->success[] = "✅ MesChain SYNC extension type oluşturuldu";

        } catch (PDOException $e) {
            $this->errors[] = "❌ Extension type oluşturma hatası: " . $e->getMessage();
        }

        echo "\n";
    }

    /**
     * Veritabanı tablolarını oluştur
     */
    private function createDatabaseTables() {
        echo "🗄️ Veritabanı Tabloları Oluşturuluyor...\n";

        $tables = [
            // MesChain Marketplaces Tablosu
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_marketplaces` (
                `marketplace_id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(50) NOT NULL,
                `name` varchar(100) NOT NULL,
                `status` tinyint(1) NOT NULL DEFAULT 0,
                `api_url` varchar(255) DEFAULT NULL,
                `api_key` varchar(255) DEFAULT NULL,
                `api_secret` varchar(255) DEFAULT NULL,
                `settings` text DEFAULT NULL,
                `last_sync` datetime DEFAULT NULL,
                `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
                `modified_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`marketplace_id`),
                UNIQUE KEY `code` (`code`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

            // MesChain Products Tablosu
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_products` (
                `meschain_product_id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `marketplace_id` int(11) NOT NULL,
                `marketplace_product_id` varchar(100) DEFAULT NULL,
                `barcode` varchar(100) DEFAULT NULL,
                `sync_status` enum('pending','synced','failed','updated','deleted') DEFAULT 'pending',
                `last_sync_date` datetime DEFAULT NULL,
                `error_message` text DEFAULT NULL,
                `error_count` int(11) DEFAULT 0,
                `marketplace_data` text DEFAULT NULL,
                `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
                `modified_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`meschain_product_id`),
                KEY `product_marketplace` (`product_id`, `marketplace_id`),
                KEY `sync_status` (`sync_status`),
                KEY `last_sync_date` (`last_sync_date`),
                FOREIGN KEY (`marketplace_id`) REFERENCES `" . DB_PREFIX . "meschain_marketplaces` (`marketplace_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

            // MesChain Orders Tablosu
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_orders` (
                `meschain_order_id` int(11) NOT NULL AUTO_INCREMENT,
                `order_id` int(11) DEFAULT NULL,
                `marketplace_id` int(11) NOT NULL,
                `marketplace_order_id` varchar(100) NOT NULL,
                `marketplace_order_number` varchar(100) DEFAULT NULL,
                `customer_name` varchar(100) DEFAULT NULL,
                `customer_email` varchar(150) DEFAULT NULL,
                `customer_phone` varchar(50) DEFAULT NULL,
                `shipping_address` text DEFAULT NULL,
                `billing_address` text DEFAULT NULL,
                `total_amount` decimal(15,4) DEFAULT 0.0000,
                `currency_code` varchar(3) DEFAULT 'TRY',
                `order_status` varchar(50) DEFAULT 'pending',
                `marketplace_status` varchar(50) DEFAULT NULL,
                `sync_status` enum('pending','synced','failed','updated') DEFAULT 'pending',
                `last_sync_date` datetime DEFAULT NULL,
                `error_message` text DEFAULT NULL,
                `marketplace_data` text DEFAULT NULL,
                `order_date` datetime DEFAULT NULL,
                `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
                `modified_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`meschain_order_id`),
                UNIQUE KEY `marketplace_order` (`marketplace_id`, `marketplace_order_id`),
                KEY `order_id` (`order_id`),
                KEY `sync_status` (`sync_status`),
                KEY `order_date` (`order_date`),
                FOREIGN KEY (`marketplace_id`) REFERENCES `" . DB_PREFIX . "meschain_marketplaces` (`marketplace_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

            // MesChain Logs Tablosu
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_logs` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `marketplace_id` int(11) DEFAULT NULL,
                `log_type` enum('info','warning','error','debug') DEFAULT 'info',
                `log_category` varchar(50) DEFAULT 'general',
                `message` text NOT NULL,
                `context_data` text DEFAULT NULL,
                `user_id` int(11) DEFAULT NULL,
                `ip_address` varchar(45) DEFAULT NULL,
                `user_agent` varchar(255) DEFAULT NULL,
                `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`log_id`),
                KEY `marketplace_id` (`marketplace_id`),
                KEY `log_type` (`log_type`),
                KEY `log_category` (`log_category`),
                KEY `created_date` (`created_date`),
                FOREIGN KEY (`marketplace_id`) REFERENCES `" . DB_PREFIX . "meschain_marketplaces` (`marketplace_id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

            // MesChain Webhooks Tablosu
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_webhooks` (
                `webhook_id` int(11) NOT NULL AUTO_INCREMENT,
                `marketplace_id` int(11) NOT NULL,
                `event_type` varchar(100) NOT NULL,
                `webhook_url` varchar(255) NOT NULL,
                `secret_key` varchar(255) DEFAULT NULL,
                `status` tinyint(1) DEFAULT 1,
                `retry_count` int(11) DEFAULT 0,
                `max_retries` int(11) DEFAULT 3,
                `last_called` datetime DEFAULT NULL,
                `last_response` text DEFAULT NULL,
                `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
                `modified_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`webhook_id`),
                KEY `marketplace_event` (`marketplace_id`, `event_type`),
                KEY `status` (`status`),
                FOREIGN KEY (`marketplace_id`) REFERENCES `" . DB_PREFIX . "meschain_marketplaces` (`marketplace_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

            // MesChain Cron Jobs Tablosu
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_cron` (
                `cron_id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(100) NOT NULL,
                `name` varchar(100) NOT NULL,
                `action` varchar(255) NOT NULL,
                `schedule` varchar(100) NOT NULL,
                `status` tinyint(1) DEFAULT 1,
                `last_run` datetime DEFAULT NULL,
                `next_run` datetime DEFAULT NULL,
                `run_count` int(11) DEFAULT 0,
                `error_count` int(11) DEFAULT 0,
                `last_error` text DEFAULT NULL,
                `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
                `modified_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`cron_id`),
                UNIQUE KEY `code` (`code`),
                KEY `status` (`status`),
                KEY `next_run` (`next_run`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
        ];

        foreach ($tables as $table) {
            try {
                $this->db->exec($table);
                $tableName = preg_match('/CREATE TABLE.*?`([^`]+)`/', $table, $matches) ? $matches[1] : 'Unknown';
                $this->success[] = "✅ Tablo oluşturuldu: {$tableName}";
            } catch (PDOException $e) {
                $tableName = preg_match('/CREATE TABLE.*?`([^`]+)`/', $table, $matches) ? $matches[1] : 'Unknown';
                $this->errors[] = "❌ Tablo oluşturma hatası ({$tableName}): " . $e->getMessage();
            }
        }

        echo "\n";
    }

    /**
     * Extension'ları kaydet
     */
    private function registerExtensions() {
        echo "📋 Extension'lar Kaydediliyor...\n";

        $extensions = [
            ['type' => 'meschain_sync', 'code' => 'meschain_sync'],
            ['type' => 'module', 'code' => 'meschain_trendyol'],
            ['type' => 'module', 'code' => 'meschain_amazon'],
            ['type' => 'module', 'code' => 'meschain_n11'],
            ['type' => 'module', 'code' => 'meschain_ebay'],
            ['type' => 'module', 'code' => 'meschain_hepsiburada'],
            ['type' => 'module', 'code' => 'meschain_ozon']
        ];

        foreach ($extensions as $extension) {
            try {
                $stmt = $this->db->prepare("
                    INSERT IGNORE INTO `" . DB_PREFIX . "extension`
                    (`extension_id`, `type`, `code`)
                    VALUES (NULL, :type, :code)
                ");
                $stmt->execute([
                    ':type' => $extension['type'],
                    ':code' => $extension['code']
                ]);

                $this->success[] = "✅ Extension kaydedildi: {$extension['code']} ({$extension['type']})";

            } catch (PDOException $e) {
                $this->errors[] = "❌ Extension kayıt hatası ({$extension['code']}): " . $e->getMessage();
            }
        }

        echo "\n";
    }

    /**
     * Kullanıcı izinlerini ayarla
     */
    private function setupUserPermissions() {
        echo "🔐 Kullanıcı İzinleri Ayarlanıyor...\n";

        try {
            // Administrator grubunu bul
            $stmt = $this->db->prepare("SELECT `user_group_id` FROM `" . DB_PREFIX . "user_group` WHERE `name` = 'Administrator' LIMIT 1");
            $stmt->execute();
            $adminGroup = $stmt->fetch();

            if ($adminGroup) {
                $userGroupId = $adminGroup['user_group_id'];

                // Mevcut izinleri al
                $stmt = $this->db->prepare("SELECT `permission` FROM `" . DB_PREFIX . "user_group` WHERE `user_group_id` = :user_group_id");
                $stmt->execute([':user_group_id' => $userGroupId]);
                $permissions = $stmt->fetch();

                if ($permissions) {
                    $permission = json_decode($permissions['permission'], true);

                    // MesChain izinlerini ekle
                    $meschainPermissions = [
                        'extension/meschain_sync',
                        'extension/module/meschain_sync',
                        'extension/module/meschain_trendyol',
                        'extension/module/meschain_amazon',
                        'extension/module/meschain_n11',
                        'extension/module/meschain_ebay',
                        'extension/module/meschain_hepsiburada',
                        'extension/module/meschain_ozon'
                    ];

                    foreach ($meschainPermissions as $perm) {
                        if (!in_array($perm, $permission['access'])) {
                            $permission['access'][] = $perm;
                        }
                        if (!in_array($perm, $permission['modify'])) {
                            $permission['modify'][] = $perm;
                        }
                    }

                    // İzinleri güncelle
                    $stmt = $this->db->prepare("UPDATE `" . DB_PREFIX . "user_group` SET `permission` = :permission WHERE `user_group_id` = :user_group_id");
                    $stmt->execute([
                        ':permission' => json_encode($permission),
                        ':user_group_id' => $userGroupId
                    ]);

                    $this->success[] = "✅ Administrator grubu izinleri güncellendi";
                }
            } else {
                $this->warnings[] = "⚠️ Administrator grubu bulunamadı";
            }

        } catch (PDOException $e) {
            $this->errors[] = "❌ İzin ayarlama hatası: " . $e->getMessage();
        }

        echo "\n";
    }

    /**
     * Varsayılan ayarları oluştur
     */
    private function createDefaultSettings() {
        echo "⚙️ Varsayılan Ayarlar Oluşturuluyor...\n";

        try {
            // Marketplace verilerini ekle
            $marketplaces = [
                ['code' => 'trendyol', 'name' => 'Trendyol', 'api_url' => 'https://api.trendyol.com/sapigw/'],
                ['code' => 'amazon', 'name' => 'Amazon', 'api_url' => 'https://sellingpartnerapi-eu.amazon.com/'],
                ['code' => 'n11', 'name' => 'n11', 'api_url' => 'https://api.n11.com/ws/'],
                ['code' => 'ebay', 'name' => 'eBay', 'api_url' => 'https://api.ebay.com/'],
                ['code' => 'hepsiburada', 'name' => 'Hepsiburada', 'api_url' => 'https://mpop.hepsiburada.com/'],
                ['code' => 'ozon', 'name' => 'Ozon', 'api_url' => 'https://api-seller.ozon.ru/']
            ];

            foreach ($marketplaces as $marketplace) {
                $stmt = $this->db->prepare("
                    INSERT IGNORE INTO `" . DB_PREFIX . "meschain_marketplaces`
                    (`code`, `name`, `api_url`, `status`)
                    VALUES (:code, :name, :api_url, 0)
                ");
                $stmt->execute($marketplace);

                $this->success[] = "✅ Marketplace eklendi: {$marketplace['name']}";
            }

            // Ana modül ayarları
            $settings = [
                ['code' => 'module_meschain_sync', 'key' => 'module_meschain_sync_status', 'value' => '1'],
                ['code' => 'module_meschain_sync', 'key' => 'module_meschain_sync_debug', 'value' => '0'],
                ['code' => 'module_meschain_sync', 'key' => 'module_meschain_sync_log_level', 'value' => 'info'],
                ['code' => 'module_meschain_sync', 'key' => 'module_meschain_sync_cron_token', 'value' => md5(uniqid())],
                ['code' => 'module_meschain_sync', 'key' => 'module_meschain_sync_webhook_secret', 'value' => bin2hex(random_bytes(32))]
            ];

            foreach ($settings as $setting) {
                $stmt = $this->db->prepare("
                    INSERT IGNORE INTO `" . DB_PREFIX . "setting`
                    (`store_id`, `code`, `key`, `value`, `serialized`)
                    VALUES (0, :code, :key, :value, 0)
                ");
                $stmt->execute($setting);
            }

            $this->success[] = "✅ Varsayılan ayarlar oluşturuldu";

        } catch (PDOException $e) {
            $this->errors[] = "❌ Varsayılan ayar oluşturma hatası: " . $e->getMessage();
        }

        echo "\n";
    }

    /**
     * Sistem dosyalarını kontrol et
     */
    private function verifySystemFiles() {
        echo "📁 Sistem Dosyaları Kontrol Ediliyor...\n";

        $requiredFiles = [
            'admin/controller/extension/module/meschain_sync.php',
            'admin/model/extension/module/meschain_sync.php',
            'admin/view/template/extension/module/meschain_sync.twig',
            'admin/language/tr-tr/extension/module/meschain_sync.php',
            'admin/language/en-gb/extension/module/meschain_sync.php',
            'system/library/meschain/bootstrap.php'
        ];

        foreach ($requiredFiles as $file) {
            if (file_exists($file)) {
                $this->success[] = "✅ Dosya mevcut: {$file}";
            } else {
                $this->warnings[] = "⚠️ Dosya eksik: {$file}";
            }
        }

        echo "\n";
    }

    /**
     * Cron job'ları oluştur
     */
    private function setupCronJobs() {
        echo "⏰ Cron Job'ları Oluşturuluyor...\n";

        try {
            $cronJobs = [
                [
                    'code' => 'meschain_sync_products',
                    'name' => 'MesChain Product Sync',
                    'action' => 'extension/module/meschain_sync/cron&task=sync_products',
                    'schedule' => '*/15 * * * *'
                ],
                [
                    'code' => 'meschain_sync_orders',
                    'name' => 'MesChain Order Sync',
                    'action' => 'extension/module/meschain_sync/cron&task=sync_orders',
                    'schedule' => '*/5 * * * *'
                ],
                [
                    'code' => 'meschain_cleanup',
                    'name' => 'MesChain Cleanup',
                    'action' => 'extension/module/meschain_sync/cron&task=cleanup',
                    'schedule' => '0 2 * * *'
                ]
            ];

            foreach ($cronJobs as $job) {
                $stmt = $this->db->prepare("
                    INSERT IGNORE INTO `" . DB_PREFIX . "meschain_cron`
                    (`code`, `name`, `action`, `schedule`, `next_run`)
                    VALUES (:code, :name, :action, :schedule, NOW() + INTERVAL 1 MINUTE)
                ");
                $stmt->execute($job);

                $this->success[] = "✅ Cron job oluşturuldu: {$job['name']}";
            }

        } catch (PDOException $e) {
            $this->errors[] = "❌ Cron job oluşturma hatası: " . $e->getMessage();
        }

        echo "\n";
    }

    /**
     * Sonuçları göster
     */
    private function displayResults() {
        echo "\n";
        echo "=== KURULUM SONUÇLARI ===\n\n";

        if (!empty($this->success)) {
            echo "✅ BAŞARILI İŞLEMLER:\n";
            foreach ($this->success as $message) {
                echo "   " . $message . "\n";
            }
            echo "\n";
        }

        if (!empty($this->warnings)) {
            echo "⚠️ UYARILAR:\n";
            foreach ($this->warnings as $message) {
                echo "   " . $message . "\n";
            }
            echo "\n";
        }

        if (!empty($this->errors)) {
            echo "❌ HATALAR:\n";
            foreach ($this->errors as $message) {
                echo "   " . $message . "\n";
            }
            echo "\n";
        }

        if (empty($this->errors)) {
            echo "🎉 MesChain SYNC Core başarıyla kuruldu!\n\n";
            echo "📋 SONRAKİ ADIMLAR:\n";
            echo "   1. OpenCart Admin Paneline giriş yapın\n";
            echo "   2. Extensions > Extensions menüsüne gidin\n";
            echo "   3. Dropdown'dan 'MesChain SYNC' seçin\n";
            echo "   4. Modülleri Install (+) butonuyla kurun\n";
            echo "   5. Her modülü Edit butonuyla yapılandırın\n\n";
            echo "🔗 Yardım: https://docs.meschain.com/sync/\n";
        } else {
            echo "❌ Kurulum tamamlanamadı. Lütfen hataları düzeltin ve tekrar deneyin.\n";
        }

        echo "\n=========================\n";
    }
}

// Kurulumu başlat
$installer = new MesChainCoreInstaller();
$installer->install();
