<?php
/**
 * OpenCart Configuration File Generator
 * This script creates the required config.php and admin/config.php files
 * with the known correct database credentials and paths, overcoming the lack
 * of shell commands like 'cp' or 'mv'.
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @date 2024-12-22
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "⚙️ ===================================================\n";
echo "⚙️ OpenCart `config.php` Oluşturucu\n";
echo "⚙️ ===================================================\n\n";

// --- Configuration ---
$base_path = __DIR__ . '/opencart_new/';
$http_server = 'http://localhost:8080/'; // Assuming 8080 from user prompt

// Database Credentials
$db_hostname = 'localhost';
$db_username = 'root';
$db_password = '1234';
$db_database = 'opencart_new';
$db_port = '3306';
$db_prefix = 'oc_';
// --- End Configuration ---


// --- Main Store config.php ---
$config_content = '<?php' . PHP_EOL;
$config_content .= '// APPLICATION' . PHP_EOL;
$config_content .= "define('APPLICATION', 'Catalog');" . PHP_EOL . PHP_EOL;
$config_content .= '// HTTP' . PHP_EOL;
$config_content .= "define('HTTP_SERVER', '{$http_server}');" . PHP_EOL . PHP_EOL;
$config_content .= '// DIR' . PHP_EOL;
$config_content .= "define('DIR_OPENCART', '{$base_path}');" . PHP_EOL;
$config_content .= "define('DIR_APPLICATION', DIR_OPENCART . 'catalog/');" . PHP_EOL;
$config_content .= "define('DIR_SYSTEM', DIR_OPENCART . 'system/');" . PHP_EOL;
$config_content .= "define('DIR_EXTENSION', DIR_OPENCART . 'extension/');" . PHP_EOL;
$config_content .= "define('DIR_IMAGE', DIR_OPENCART . 'image/');" . PHP_EOL;
$config_content .= "define('DIR_STORAGE', DIR_SYSTEM . 'storage/');" . PHP_EOL;
$config_content .= "define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');" . PHP_EOL;
$config_content .= "define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');" . PHP_EOL;
$config_content .= "define('DIR_CONFIG', DIR_SYSTEM . 'config/');" . PHP_EOL;
$config_content .= "define('DIR_CACHE', DIR_STORAGE . 'cache/');" . PHP_EOL;
$config_content .= "define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');" . PHP_EOL;
$config_content .= "define('DIR_LOGS', DIR_STORAGE . 'logs/');" . PHP_EOL;
$config_content .= "define('DIR_SESSION', DIR_STORAGE . 'session/');" . PHP_EOL;
$config_content .= "define('DIR_UPLOAD', DIR_STORAGE . 'upload/');" . PHP_EOL . PHP_EOL;
$config_content .= '// DB' . PHP_EOL;
$config_content .= "define('DB_DRIVER', 'mysqli');" . PHP_EOL;
$config_content .= "define('DB_HOSTNAME', '{$db_hostname}');" . PHP_EOL;
$config_content .= "define('DB_USERNAME', '{$db_username}');" . PHP_EOL;
$config_content .= "define('DB_PASSWORD', '{$db_password}');" . PHP_EOL;
$config_content .= "define('DB_DATABASE', '{$db_database}');" . PHP_EOL;
$config_content .= "define('DB_PREFIX', '{$db_prefix}');" . PHP_EOL;
$config_content .= "define('DB_PORT', '{$db_port}');" . PHP_EOL;

if (file_put_contents($base_path . 'config.php', $config_content)) {
    echo "✅ `opencart_new/config.php` başarıyla oluşturuldu.\n";
} else {
    echo "❌ `opencart_new/config.php` oluşturulamadı! Dizin izinlerini kontrol edin.\n";
}


// --- Admin config.php ---
$admin_config_content = '<?php' . PHP_EOL;
$admin_config_content .= '// APPLICATION' . PHP_EOL;
$admin_config_content .= "define('APPLICATION', 'Admin');" . PHP_EOL . PHP_EOL;
$admin_config_content .= '// HTTP' . PHP_EOL;
$admin_config_content .= "define('HTTP_SERVER', '{$http_server}admin/');" . PHP_EOL;
$admin_config_content .= "define('HTTP_CATALOG', '{$http_server}');" . PHP_EOL . PHP_EOL;
$admin_config_content .= '// DIR' . PHP_EOL;
$admin_config_content .= "define('DIR_OPENCART', '{$base_path}');" . PHP_EOL;
$admin_config_content .= "define('DIR_APPLICATION', DIR_OPENCART . 'admin/');" . PHP_EOL;
$admin_config_content .= "define('DIR_SYSTEM', DIR_OPENCART . 'system/');" . PHP_EOL;
$admin_config_content .= "define('DIR_EXTENSION', DIR_OPENCART . 'extension/');" . PHP_EOL;
$admin_config_content .= "define('DIR_IMAGE', DIR_OPENCART . 'image/');" . PHP_EOL;
$admin_config_content .= "define('DIR_STORAGE', DIR_SYSTEM . 'storage/');" . PHP_EOL;
$admin_config_content .= "define('DIR_CATALOG', DIR_OPENCART . 'catalog/');" . PHP_EOL;
$admin_config_content .= "define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');" . PHP_EOL;
$admin_config_content .= "define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');" . PHP_EOL;
$admin_config_content .= "define('DIR_CONFIG', DIR_SYSTEM . 'config/');" . PHP_EOL;
$admin_config_content .= "define('DIR_CACHE', DIR_STORAGE . 'cache/');" . PHP_EOL;
$admin_config_content .= "define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');" . PHP_EOL;
$admin_config_content .= "define('DIR_LOGS', DIR_STORAGE . 'logs/');" . PHP_EOL;
$admin_config_content .= "define('DIR_SESSION', DIR_STORAGE . 'session/');" . PHP_EOL;
$admin_config_content .= "define('DIR_UPLOAD', DIR_STORAGE . 'upload/');" . PHP_EOL . PHP_EOL;
$admin_config_content .= '// DB' . PHP_EOL;
$admin_config_content .= "define('DB_DRIVER', 'mysqli');" . PHP_EOL;
$admin_config_content .= "define('DB_HOSTNAME', '{$db_hostname}');" . PHP_EOL;
$admin_config_content .= "define('DB_USERNAME', '{$db_username}');" . PHP_EOL;
$admin_config_content .= "define('DB_PASSWORD', '{$db_password}');" . PHP_EOL;
$admin_config_content .= "define('DB_DATABASE', '{$db_database}');" . PHP_EOL;
$admin_config_content .= "define('DB_PREFIX', '{$db_prefix}');" . PHP_EOL;
$admin_config_content .= "define('DB_PORT', '{$db_port}');" . PHP_EOL;

if (file_put_contents($base_path . 'admin/config.php', $admin_config_content)) {
    echo "✅ `opencart_new/admin/config.php` başarıyla oluşturuldu.\n";
} else {
    echo "❌ `opencart_new/admin/config.php` oluşturulamadı! Dizin izinlerini kontrol edin.\n";
}

echo "\nİşlem tamamlandı. Şimdi kurulum betiğini çalıştırabilirsiniz.\n";
?> 