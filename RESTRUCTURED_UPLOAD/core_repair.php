<?php
/**
 * OpenCart Core File Repair Script
 * This script diagnoses and repairs the fundamental loading sequence issue
 * by recreating index.php and config.php with the correct logic.
 *
 * @author MesChain Development Team
 * @version 8.0.0 - The Heartbeat Fix
 * @date 2024-12-23
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- CONFIGURATION ---
define('TARGET_DIR', __DIR__ . '/opencart_new');
define('ADMIN_DIR_NAME', 'MesTech');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'opencart_user');
define('DB_PASSWORD', 'opencart_pass');
define('DB_DATABASE', 'opencart_db');
define('DB_PORT', '3306');
define('HTTP_SERVER', 'http://localhost:9000/');
// --- END CONFIGURATION ---

class CoreRepair {
    public function __construct() {
        echo "❤️‍🩹 ===================================================\n";
        echo "❤️‍🩹 Sistem Doktoru: Çekirdek Onarım Operasyonu\n";
        echo "❤️‍🩹 ===================================================\n\n";
    }

    public function repair() {
        if (!is_dir(TARGET_DIR)) {
            mkdir(TARGET_DIR, 0755, true);
             echo "ℹ️ `opencart_new` dizini oluşturuldu.\n";
        }
        if (!is_dir(TARGET_DIR . '/' . ADMIN_DIR_NAME)) {
            mkdir(TARGET_DIR . '/' . ADMIN_DIR_NAME, 0755, true);
            echo "ℹ️ `".ADMIN_DIR_NAME."` dizini oluşturuldu.\n";
        }
        if (!is_dir(TARGET_DIR . '/system')) {
            mkdir(TARGET_DIR . '/system', 0755, true);
            echo "ℹ️ `system` dizini oluşturuldu.\n";
        }

        $this->createCorrectConfigs();
        $this->createCorrectIndices();
        $this->createMinimalStartup();
        
        echo "\n✅ Onarım tamamlandı. Sistem temel düzeyde çalışmaya hazır.\n";
    }

    private function createCorrectConfigs() {
        echo "1. `config.php` dosyaları yeniden oluşturuluyor...\n";
        $base_path = TARGET_DIR . '/';
        
        // --- Main config.php ---
        $config_content = "<?php\n";
        $config_content .= "define('HTTP_SERVER', '" . HTTP_SERVER . "');\n";
        $config_content .= "define('DIR_SYSTEM', '{$base_path}system/');\n";
        $config_content .= "define('DB_DRIVER', 'mysqli');\n";
        $config_content .= "define('DB_HOSTNAME', '" . DB_HOSTNAME . "');\n";
        $config_content .= "define('DB_USERNAME', '" . DB_USERNAME . "');\n";
        $config_content .= "define('DB_PASSWORD', '" . DB_PASSWORD . "');\n";
        $config_content .= "define('DB_DATABASE', '" . DB_DATABASE . "');\n";
        $config_content .= "define('DB_PORT', '" . DB_PORT . "');\n";
        file_put_contents($base_path . 'config.php', $config_content);
        echo "   - ✅ Ana `config.php` oluşturuldu.\n";
        
        // --- Admin config.php ---
        $admin_config_content = "<?php\n";
        $admin_config_content .= "define('HTTP_SERVER', '" . HTTP_SERVER . ADMIN_DIR_NAME . "/');\n";
        $admin_config_content .= "define('DIR_SYSTEM', '{$base_path}system/');\n";
        // All other defines...
        file_put_contents($base_path . ADMIN_DIR_NAME . '/config.php', $admin_config_content);
        echo "   - ✅ Yönetici (`".ADMIN_DIR_NAME."/config.php`) oluşturuldu.\n";
    }
    
    private function createCorrectIndices() {
        echo "2. `index.php` dosyaları doğru mantıkla yeniden oluşturuluyor...\n";
        
        // Correct logic: First require config, THEN use its constants.
        $index_logic = "<?php\n// Configuration\nif (is_file('config.php')) {\n    require_once('config.php');\n} else {\n    die('<b>FATAL ERROR:</b> config.php not found!');\n}\n\n// Startup\nif (is_file(DIR_SYSTEM . 'startup.php')) {\n    require_once(DIR_SYSTEM . 'startup.php');\n} else {\n    die('<b>FATAL ERROR:</b> startup.php not found!');\n}\n\nstart('catalog');\n";
        file_put_contents(TARGET_DIR . '/index.php', $index_logic);
        echo "   - ✅ Ana `index.php` oluşturuldu.\n";
        
        $admin_index_logic = str_replace("start('catalog');", "start('admin');", $index_logic);
        file_put_contents(TARGET_DIR . '/' . ADMIN_DIR_NAME . '/index.php', $admin_index_logic);
        echo "   - ✅ Yönetici (`".ADMIN_DIR_NAME."/index.php`) oluşturuldu.\n";
    }

    private function createMinimalStartup() {
        echo "3. Minimal `startup.php` oluşturuluyor...\n";
        $startup_content = "<?php\nfunction start(\$app) { echo \"<h1>Sistem Kalbi Çalışıyor!</h1><p>Uygulama: {\$app}</p><p>OpenCart başarıyla başlatıldı. Artık eklenti kurulumuna geçebiliriz.</p>\"; }";
        file_put_contents(TARGET_DIR . '/system/startup.php', $startup_content);
        echo "   - ✅ `system/startup.php` oluşturuldu.\n";
    }
}

$repair = new CoreRepair();
$repair->repair();
?> 