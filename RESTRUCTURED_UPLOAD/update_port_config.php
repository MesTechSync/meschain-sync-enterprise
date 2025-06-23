<?php
/**
 * OpenCart Port Updater
 * This script updates the HTTP_SERVER and HTTP_CATALOG constants in both
 * config.php files to use the new specified port (9000).
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @date 2024-12-23
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- CONFIGURATION ---
define('TARGET_DIR', __DIR__ . '/opencart_new');
define('ADMIN_DIR_NAME', 'MesTech');
define('NEW_PORT', '9000');
// --- END CONFIGURATION ---

class PortUpdater {

    public function __construct() {
        echo "🔄 ===================================================\n";
        echo "🔄 OpenCart Port Güncelleyici Başlatıldı (Port: " . NEW_PORT . ")\n";
        echo "🔄 ===================================================\n\n";
    }

    public function update() {
        $this->updateFile(TARGET_DIR . '/config.php');
        $this->updateFile(TARGET_DIR . '/' . ADMIN_DIR_NAME . '/config.php', true);
        echo "\n✅ Yapılandırma dosyaları yeni porta göre başarıyla güncellendi.\n";
    }

    private function updateFile($path, $is_admin = false) {
        if (!file_exists($path)) {
            echo "   - ❌ HATA: `{$path}` bulunamadı. Güncelleme atlanıyor.\n";
            return;
        }

        $content = file_get_contents($path);

        // Replace the server definitions
        $new_http_server = 'http://localhost:' . NEW_PORT . '/';
        
        if ($is_admin) {
            $content = preg_replace("/define\('HTTP_SERVER', '.*?'\);/", "define('HTTP_SERVER', '{$new_http_server}" . ADMIN_DIR_NAME . "/');", $content);
            $content = preg_replace("/define\('HTTP_CATALOG', '.*?'\);/", "define('HTTP_CATALOG', '{$new_http_server}');", $content);
        } else {
            $content = preg_replace("/define\('HTTP_SERVER', '.*?'\);/", "define('HTTP_SERVER', '{$new_http_server}');", $content);
        }
        
        file_put_contents($path, $content);
        echo "   - ✅ `{$path}` güncellendi.\n";
    }
}

$updater = new PortUpdater();
$updater->update();
?> 