<?php
/**
 * Missing Index.php File Creator
 * This script creates the missing root index.php and the admin index.php
 * which are the root cause of the "404 Not Found" errors.
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @date 2024-12-23
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Configuration ---
define('TARGET_DIR', __DIR__ . '/opencart_new');
define('ADMIN_DIR_NAME', 'MesTech');
// --- End Configuration ---

class IndexFixer {

    public function __construct() {
        echo "✅ ===================================================\n";
        echo "✅ Eksik `index.php` Oluşturucu Başlatıldı\n";
        echo "✅ ===================================================\n\n";
    }

    public function fix() {
        if (!is_dir(TARGET_DIR)) {
            die("❌ HATA: `opencart_new` dizini bulunamadı!");
        }

        // --- Create Storefront index.php ---
        $storefront_index_content = "<?php\n// Version\ndefine('VERSION', '4.0.2.3');\n\n// Configuration\nif (is_file('config.php')) { require_once('config.php'); }\n\n// Startup\nrequire_once(DIR_SYSTEM . 'startup.php');\n\nstart('catalog');\n";
        $storefront_path = TARGET_DIR . '/index.php';
        
        if (file_put_contents($storefront_path, $storefront_index_content)) {
            echo "   - ✅ Ana `index.php` başarıyla oluşturuldu.\n";
        } else {
            echo "   - ❌ HATA: Ana `index.php` oluşturulamadı!\n";
        }
        
        // --- Create Admin index.php ---
        $admin_index_content = "<?php\n// Version\ndefine('VERSION', '4.0.2.3');\n\n// Configuration\nif (is_file('config.php')) { require_once('config.php'); }\n\n// Startup\nrequire_once(DIR_SYSTEM . 'startup.php');\n\nstart('admin');\n";
        $admin_path = TARGET_DIR . '/' . ADMIN_DIR_NAME . '/index.php';
        
        if (file_put_contents($admin_path, $admin_index_content)) {
            echo "   - ✅ Yönetici `".ADMIN_DIR_NAME."/index.php` başarıyla oluşturuldu.\n";
        } else {
            echo "   - ❌ HATA: Yönetici `index.php` oluşturulamadı!\n";
        }

        echo "\n🎉 İşlem tamamlandı. 404 hataları çözülmüş olmalı. Lütfen sunucuyu yeniden başlatıp kontrol edin.\n";
    }
}

$fixer = new IndexFixer();
$fixer->fix();
?> 