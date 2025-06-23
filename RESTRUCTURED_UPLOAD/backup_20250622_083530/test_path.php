<?php
echo "Current directory: " . __DIR__ . "\n";
echo "DIR_OPENCART: " . (defined('DIR_OPENCART') ? DIR_OPENCART : 'NOT DEFINED') . "\n";
echo "DIR_EXTENSION: " . (defined('DIR_EXTENSION') ? DIR_EXTENSION : 'NOT DEFINED') . "\n";
echo "DIR_APPLICATION: " . (defined('DIR_APPLICATION') ? DIR_APPLICATION : 'NOT DEFINED') . "\n";

// Test file existence
$test_paths = [
    __DIR__ . '/extension/meschain_sync/system/library/meschain/trendyol_api.php',
    dirname(__DIR__) . '/extension/meschain_sync/system/library/meschain/trendyol_api.php',
    __DIR__ . '/../extension/meschain_sync/system/library/meschain/trendyol_api.php'
];

foreach ($test_paths as $path) {
    echo "Testing path: $path\n";
    echo "Exists: " . (file_exists($path) ? 'YES' : 'NO') . "\n";
    echo "---\n";
}
