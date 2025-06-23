<?php
/**
 * MesChain Extension Registration Script
 * Registers all missing MesChain marketplace extensions in OpenCart database
 * 
 * @author Debug Assistant
 * @version 1.0.0
 * @date 2025-06-21
 */

// Database configuration from OpenCart
require_once('opencart_new/config.php');

// Database connection
$connection = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

echo "=== MesChain Extension Registration Script ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Define all MesChain extensions to register
$extensions = [
    [
        'type' => 'module',
        'code' => 'meschain_sync',
        'name' => 'MesChain Sync',
        'description' => 'MesChain main synchronization module'
    ],
    [
        'type' => 'module', 
        'code' => 'meschain_trendyol',
        'name' => 'MesChain Trendyol',
        'description' => 'Trendyol marketplace integration'
    ],
    [
        'type' => 'module',
        'code' => 'meschain_amazon',
        'name' => 'MesChain Amazon',
        'description' => 'Amazon marketplace integration'
    ],
    [
        'type' => 'module',
        'code' => 'meschain_hepsiburada',
        'name' => 'MesChain Hepsiburada',
        'description' => 'Hepsiburada marketplace integration'
    ],
    [
        'type' => 'module',
        'code' => 'meschain_n11',
        'name' => 'MesChain N11',
        'description' => 'N11 marketplace integration'
    ],
    [
        'type' => 'module',
        'code' => 'meschain_ebay',
        'name' => 'MesChain eBay',
        'description' => 'eBay marketplace integration'
    ],
    [
        'type' => 'module',
        'code' => 'meschain_gittigidiyor',
        'name' => 'MesChain GittiGidiyor',
        'description' => 'GittiGidiyor marketplace integration'
    ],
    [
        'type' => 'module',
        'code' => 'meschain_pazarama',
        'name' => 'MesChain Pazarama', 
        'description' => 'Pazarama marketplace integration'
    ]
];

// Check existing extensions
echo "1. Checking existing extensions...\n";
$existing_query = "SELECT * FROM " . DB_PREFIX . "extension WHERE type = 'module' AND code LIKE 'meschain%'";
$existing_result = $connection->query($existing_query);

$existing_extensions = [];
if ($existing_result && $existing_result->num_rows > 0) {
    while ($row = $existing_result->fetch_assoc()) {
        $existing_extensions[] = $row['code'];
        echo "   - Found existing: {$row['code']}\n";
    }
} else {
    echo "   - No existing MesChain extensions found\n";
}

echo "\n2. Registering missing extensions...\n";

$registered_count = 0;
$skipped_count = 0;

foreach ($extensions as $extension) {
    if (in_array($extension['code'], $existing_extensions)) {
        echo "   - Skipped {$extension['code']} (already exists)\n";
        $skipped_count++;
        continue;
    }
    
    // Insert extension
    $insert_query = "INSERT INTO " . DB_PREFIX . "extension (extension, type, code) VALUES (?, ?, ?)";
    $stmt = $connection->prepare($insert_query);
    
    if ($stmt) {
        $extension_namespace = 'meschain';
        $stmt->bind_param('sss', $extension_namespace, $extension['type'], $extension['code']);
        
        if ($stmt->execute()) {
            echo "   ✓ Registered: {$extension['name']} ({$extension['code']})\n";
            $registered_count++;
        } else {
            echo "   ✗ Failed to register {$extension['code']}: " . $stmt->error . "\n";
        }
        
        $stmt->close();
    } else {
        echo "   ✗ Failed to prepare statement for {$extension['code']}: " . $connection->error . "\n";
    }
}

echo "\n3. Setting up extension settings...\n";

// Add default settings for each extension
foreach ($extensions as $extension) {
    $settings_data = [
        'status' => '0',
        'sort_order' => '1'
    ];
    
    foreach ($settings_data as $key => $value) {
        $setting_key = $extension['code'] . '_' . $key;
        
        // Check if setting already exists
        $check_query = "SELECT * FROM " . DB_PREFIX . "setting WHERE `key` = ? AND store_id = 0";
        $check_stmt = $connection->prepare($check_query);
        $check_stmt->bind_param('s', $setting_key);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            // Insert setting
            $insert_setting = "INSERT INTO " . DB_PREFIX . "setting (store_id, code, `key`, value, serialized) VALUES (0, ?, ?, ?, 0)";
            $setting_stmt = $connection->prepare($insert_setting);
            $setting_stmt->bind_param('sss', $extension['code'], $setting_key, $value);
            
            if ($setting_stmt->execute()) {
                echo "   ✓ Added setting: {$setting_key}\n";
            }
            $setting_stmt->close();
        }
        $check_stmt->close();
    }
}

echo "\n4. Verification check...\n";

// Verify all extensions are now registered
$verify_query = "SELECT * FROM " . DB_PREFIX . "extension WHERE type = 'module' AND code LIKE 'meschain%' ORDER BY code";
$verify_result = $connection->query($verify_query);

$final_extensions = [];
if ($verify_result && $verify_result->num_rows > 0) {
    echo "   Registered MesChain extensions:\n";
    while ($row = $verify_result->fetch_assoc()) {
        $final_extensions[] = $row['code'];
        echo "   - {$row['code']}\n";
    }
} else {
    echo "   ✗ No MesChain extensions found!\n";
}

echo "\n5. Cache clearing...\n";

// Clear OpenCart cache
$cache_dirs = [
    'opencart_new/system/storage/cache',
    'storagenew/cache'
];

foreach ($cache_dirs as $cache_dir) {
    if (is_dir($cache_dir)) {
        $cache_files = glob($cache_dir . '/*');
        foreach ($cache_files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "   ✓ Cleared cache: {$cache_dir}\n";
    }
}

// Summary
echo "\n=== REGISTRATION SUMMARY ===\n";
echo "Total extensions processed: " . count($extensions) . "\n";
echo "Extensions registered: {$registered_count}\n";
echo "Extensions skipped: {$skipped_count}\n";
echo "Final extension count: " . count($final_extensions) . "\n";

if (count($final_extensions) == count($extensions)) {
    echo "\n✅ SUCCESS: All MesChain extensions are now registered!\n";
    echo "\nNext steps:\n";
    echo "1. Visit: http://localhost:8000/admin/index.php?route=marketplace/extension&type=module\n";
    echo "2. Look for MesChain extensions in the module list\n";
    echo "3. Install and configure each extension as needed\n";
} else {
    echo "\n❌ WARNING: Some extensions may not be registered properly\n";
}

echo "\nAdmin Panel URL: http://localhost:8000/admin/index.php?route=marketplace/extension&type=module\n";
echo "Script completed at: " . date('Y-m-d H:i:s') . "\n";

$connection->close();
?>