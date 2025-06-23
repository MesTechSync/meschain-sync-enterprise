<?php
/**
 * Diagnose Extension Visibility Issues
 * Check why MesChain marketplace extensions aren't showing in admin panel
 */

// Database configuration from OpenCart
require_once('opencart_new/config.php');

// Database connection
$connection = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

echo "=== MesChain Extension Visibility Diagnosis ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Check database registrations
echo "1. Database Extension Registrations:\n";
$db_query = "SELECT * FROM " . DB_PREFIX . "extension WHERE code LIKE 'meschain%' ORDER BY code";
$db_result = $connection->query($db_query);

if ($db_result && $db_result->num_rows > 0) {
    while ($row = $db_result->fetch_assoc()) {
        echo "   ✓ {$row['code']} (extension: {$row['extension']}, type: {$row['type']}, status: {$row['status']})\n";
    }
} else {
    echo "   ✗ No MesChain extensions found in database\n";
}

// 2. Check controller files
echo "\n2. Controller File Structure:\n";
$controller_path = 'opencart_new/admin/controller/extension/module/';
$expected_controllers = [
    'meschain_sync.php',
    'meschain_trendyol.php', 
    'meschain_amazon.php',
    'meschain_hepsiburada.php',
    'meschain_n11.php',
    'meschain_ebay.php',
    'meschain_gittigidiyor.php',
    'meschain_pazarama.php'
];

foreach ($expected_controllers as $controller) {
    $full_path = $controller_path . $controller;
    if (file_exists($full_path)) {
        echo "   ✓ {$controller} exists\n";
        
        // Check class name in file
        $content = file_get_contents($full_path);
        if (preg_match('/class\s+([a-zA-Z_]+)/i', $content, $matches)) {
            echo "      Class: {$matches[1]}\n";
        }
    } else {
        echo "   ✗ {$controller} missing\n";
    }
}

// 3. Check language files
echo "\n3. Language File Structure:\n";
$lang_path = 'opencart_new/admin/language/en-gb/extension/module/';
$expected_langs = [
    'meschain_sync.php',
    'meschain_trendyol.php',
    'meschain_amazon.php', 
    'meschain_hepsiburada.php',
    'meschain_n11.php',
    'meschain_ebay.php',
    'meschain_gittigidiyor.php',
    'meschain_pazarama.php'
];

foreach ($expected_langs as $lang_file) {
    $full_path = $lang_path . $lang_file;
    if (file_exists($full_path)) {
        echo "   ✓ {$lang_file} exists\n";
    } else {
        echo "   ✗ {$lang_file} missing\n";
    }
}

// 4. Check OpenCart extension loading mechanism
echo "\n4. OpenCart Extension Loading Check:\n";

// Check the marketplace/extension controller to see how it discovers extensions
$marketplace_controller = 'opencart_new/admin/controller/marketplace/extension.php';
if (file_exists($marketplace_controller)) {
    echo "   ✓ Marketplace extension controller exists\n";
    
    // Read and analyze the controller
    $content = file_get_contents($marketplace_controller);
    if (strpos($content, 'extension') !== false) {
        echo "   ✓ Extension loading mechanism found\n";
    }
} else {
    echo "   ✗ Marketplace extension controller missing\n";
}

// 5. Check if there are any error logs
echo "\n5. Error Log Check:\n";
$error_log_paths = [
    'opencart_new/system/storage/logs/error.log',
    'opencart_new/error.log',
    'storagenew/logs/error.log'
];

foreach ($error_log_paths as $log_path) {
    if (file_exists($log_path)) {
        $log_content = file_get_contents($log_path);
        if (!empty($log_content)) {
            echo "   ! Found errors in {$log_path}:\n";
            $recent_lines = array_slice(explode("\n", $log_content), -10);
            foreach ($recent_lines as $line) {
                if (!empty(trim($line))) {
                    echo "     {$line}\n";
                }
            }
        }
    }
}

// 6. Check extension installation status
echo "\n6. Extension Installation Status:\n";
$setting_query = "SELECT * FROM " . DB_PREFIX . "setting WHERE code LIKE 'meschain%' AND `key` LIKE '%status%'";
$setting_result = $connection->query($setting_query);

if ($setting_result && $setting_result->num_rows > 0) {
    while ($row = $setting_result->fetch_assoc()) {
        echo "   {$row['code']}: {$row['key']} = {$row['value']}\n";
    }
} else {
    echo "   No extension settings found\n";
}

// 7. Recommendations
echo "\n=== RECOMMENDATIONS ===\n";
echo "Based on the diagnosis:\n";

// Count existing controllers vs database entries
$controller_count = 0;
foreach ($expected_controllers as $controller) {
    if (file_exists($controller_path . $controller)) {
        $controller_count++;
    }
}

$db_count = $db_result ? $db_result->num_rows : 0;

if ($controller_count < count($expected_controllers)) {
    echo "1. Missing controller files - need to create missing controllers\n";
}

if ($db_count != count($expected_controllers)) {
    echo "2. Database registration mismatch - need to verify all extensions are registered\n";
}

echo "3. Clear cache and refresh admin panel\n";
echo "4. Check that all extensions have proper language files\n";

$connection->close();
?>