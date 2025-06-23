<?php
/**
 * OpenCart Blank Page Diagnoser
 * This script forces error reporting and uses a shutdown function to catch the
 * exact fatal error that is causing the blank pages on the storefront and admin area.
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @date 2024-12-23
 */

// Forcefully enable maximum error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// This function will run when the script exits, allowing us to catch fatal errors.
function capture_fatal_error() {
    $error = error_get_last();
    
    // Check if it's a fatal error
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR])) {
        echo "\n\n🚨 ===================================================\n";
        echo "🚨 GİZLİ FATAL HATA YAKALANDI!\n";
        echo "🚨 ===================================================\n";
        echo "Boş sayfanın nedeni bu hatadır:\n\n";
        echo "  - **Hata Mesajı:** " . htmlspecialchars($error['message']) . "\n";
        echo "  - **Hata Dosyası:** " . htmlspecialchars($error['file']) . "\n";
        echo "  - **Satır Numarası:** " . $error['line'] . "\n\n";
        echo "Bu bilgi, sorunu çözmek için gereken en önemli veridir.\n";
        echo "===================================================\n";
    }
}

// Register our error catcher
register_shutdown_function('capture_fatal_error');


echo "🩺 ===================================================\n";
echo "🩺 OpenCart Boş Sayfa Teşhis Aracı Başlatıldı\n";
echo "🩺 ===================================================\n\n";

// --- Test Storefront ---
echo "1. Mağaza Ön Yüzü (`/index.php`) test ediliyor...\n";

// Define constants as if we are loading from the root
define('OPENCART_SERVER', 'http://localhost:8080/');
define('DIR_CWD', __DIR__ . '/opencart_new/');

// We must require the config file before startup
if (file_exists(DIR_CWD . 'config.php')) {
    require_once(DIR_CWD . 'config.php');
    echo "   - `config.php` yüklendi.\n";
} else {
    die("   - ❌ KRİTİK: `config.php` bulunamadı!\n");
}

// We must require the startup file to boot OpenCart
if (file_exists(DIR_SYSTEM . 'startup.php')) {
    require_once(DIR_SYSTEM . 'startup.php');
    echo "   - `system/startup.php` yüklendi.\n";
    
    // The start() function is what kicks everything off
    // This call will likely trigger the fatal error
    start('catalog');
    
} else {
    die("   - ❌ KRİTİK: `system/startup.php` bulunamadı!\n");
}
// Note: If a fatal error occurs in start(), the script will stop here,
// and our registered shutdown function will report it.

echo "\nTeşhis betiği çalışmasını tamamladı. Eğer yukarıda bir 'GİZLİ FATAL HATA' raporu görmüyorsanız, sorun daha karmaşık demektir.\n";

// We won't test the admin panel in the same run, as a fatal error in the catalog
// part would stop the script. We analyze one thing at a time for clarity.
?> 