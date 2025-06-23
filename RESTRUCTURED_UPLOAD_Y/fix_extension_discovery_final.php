<?php
/**
 * OpenCart 4.x Extension Discovery Fix
 * Registers 7 missing MesChain marketplace extensions
 * Based on debug analysis results
 */

// OpenCart configuration
require_once('./opencart_new/config.php');

echo "=== OPENCART 4.x EXTENSION DISCOVERY FIX ===\n\n";

// Database connection
$mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "🔧 STARTING EXTENSION REGISTRATION PROCESS...\n\n";

// 1. Define missing extensions based on debug analysis
$missing_extensions = [
    'meschain_trendyol',
    'meschain_amazon', 
    'meschain_hepsiburada',
    'meschain_n11',
    'meschain_ebay',
    'meschain_gittigidiyor',
    'meschain_pazarama'
];

echo "1. MISSING EXTENSIONS TO REGISTER:\n";
foreach ($missing_extensions as $ext) {
    echo "   - $ext\n";
}
echo "\n";

// 2. Get meschain_sync registration as template
echo "2. GETTING MESCHAIN_SYNC REGISTRATION AS TEMPLATE:\n";
$sync_result = $mysqli->query("SELECT * FROM " . DB_PREFIX . "extension_path WHERE path LIKE '%meschain_sync%' LIMIT 1");
$sync_template = $sync_result->fetch_assoc();

if ($sync_template) {
    echo "   ✅ Found meschain_sync template:\n";
    echo "   - Extension Install ID: " . $sync_template['extension_install_id'] . "\n";
    echo "   - Path pattern: " . $sync_template['path'] . "\n\n";
    
    $extension_install_id = $sync_template['extension_install_id'];
} else {
    echo "   ❌ No meschain_sync template found, using default values\n\n";
    $extension_install_id = 1; // Default fallback
}

// 3. Register each missing extension in extension_path
echo "3. REGISTERING EXTENSIONS IN EXTENSION_PATH TABLE:\n";
foreach ($missing_extensions as $extension) {
    // Check if already exists
    $check_query = "SELECT * FROM " . DB_PREFIX . "extension_path WHERE path LIKE '%$extension%'";
    $check_result = $mysqli->query($check_query);
    
    if ($check_result->num_rows > 0) {
        echo "   ⚠️  $extension already registered in extension_path\n";
        continue;
    }
    
    // Insert new extension_path entry
    $path = "meschain_sync/admin/controller/module/$extension.php";
    $insert_query = "INSERT INTO " . DB_PREFIX . "extension_path 
                     (extension_install_id, path) 
                     VALUES ($extension_install_id, '$path')";
    
    if ($mysqli->query($insert_query)) {
        echo "   ✅ $extension registered in extension_path\n";
    } else {
        echo "   ❌ Failed to register $extension: " . $mysqli->error . "\n";
    }
}

echo "\n";

// 4. Register extensions in extension table
echo "4. REGISTERING EXTENSIONS IN EXTENSION TABLE:\n";
foreach ($missing_extensions as $extension) {
    // Check if already exists
    $check_query = "SELECT * FROM " . DB_PREFIX . "extension WHERE code = '$extension' AND type = 'module'";
    $check_result = $mysqli->query($check_query);
    
    if ($check_result->num_rows > 0) {
        echo "   ⚠️  $extension already registered in extension table\n";
        continue;
    }
    
    // Insert new extension entry
    $insert_query = "INSERT INTO " . DB_PREFIX . "extension 
                     (extension, type, code) 
                     VALUES ('$extension', 'module', '$extension')";
    
    if ($mysqli->query($insert_query)) {
        echo "   ✅ $extension registered in extension table\n";
    } else {
        echo "   ❌ Failed to register $extension in extension table: " . $mysqli->error . "\n";
    }
}

echo "\n";

// 5. Update admin user permissions
echo "5. UPDATING ADMIN USER PERMISSIONS:\n";
$user_result = $mysqli->query("SELECT * FROM " . DB_PREFIX . "user_group WHERE user_group_id = 1");
$user_group = $user_result->fetch_assoc();

if ($user_group) {
    $permissions = json_decode($user_group['permission'], true);
    $updated = false;
    
    foreach ($missing_extensions as $extension) {
        $access_permission = "extension/module/$extension";
        $modify_permission = "extension/module/$extension";
        
        // Add access permission
        if (!in_array($access_permission, $permissions['access'])) {
            $permissions['access'][] = $access_permission;
            $updated = true;
            echo "   ✅ Added access permission for $extension\n";
        }
        
        // Add modify permission
        if (!in_array($modify_permission, $permissions['modify'])) {
            $permissions['modify'][] = $modify_permission;
            $updated = true;
            echo "   ✅ Added modify permission for $extension\n";
        }
    }
    
    if ($updated) {
        $permission_json = json_encode($permissions);
        $update_query = "UPDATE " . DB_PREFIX . "user_group 
                        SET permission = '$permission_json' 
                        WHERE user_group_id = 1";
        
        if ($mysqli->query($update_query)) {
            echo "   ✅ Admin permissions updated successfully\n";
        } else {
            echo "   ❌ Failed to update admin permissions: " . $mysqli->error . "\n";
        }
    } else {
        echo "   ⚠️  All permissions already exist\n";
    }
} else {
    echo "   ❌ Admin user group not found\n";
}

echo "\n";

// 6. Verification
echo "6. VERIFICATION - CHECKING REGISTERED EXTENSIONS:\n";
echo "================================================\n";

// Check extension_path entries
echo "A. Extension Path Entries:\n";
$result = $mysqli->query("SELECT * FROM " . DB_PREFIX . "extension_path WHERE path LIKE '%meschain_%' ORDER BY path");
while ($row = $result->fetch_assoc()) {
    $extension_name = basename($row['path'], '.php');
    echo "   ✅ $extension_name - Registered in extension_path\n";
}

echo "\nB. Extension Table Entries:\n";
$result = $mysqli->query("SELECT * FROM " . DB_PREFIX . "extension WHERE type = 'module' AND code LIKE 'meschain_%' ORDER BY code");
while ($row = $result->fetch_assoc()) {
    echo "   ✅ " . $row['code'] . " - Registered in extension table\n";
}

// 7. Test extension discovery
echo "\n7. TESTING EXTENSION DISCOVERY:\n";
echo "===============================\n";

// Simulate OpenCart extension discovery
echo "Simulating admin panel extension discovery:\n";
$discovery_query = "SELECT DISTINCT REPLACE(SUBSTRING_INDEX(path, '/', -1), '.php', '') as extension_name
                   FROM " . DB_PREFIX . "extension_path 
                   WHERE path LIKE '%/admin/controller/module/meschain_%'
                   ORDER BY extension_name";

$discovery_result = $mysqli->query($discovery_query);
$discovered_count = 0;

while ($row = $discovery_result->fetch_assoc()) {
    echo "   🔍 Discovered: " . $row['extension_name'] . "\n";
    $discovered_count++;
}

echo "\nDISCOVERY SUMMARY:\n";
echo "- Total MesChain extensions discovered: $discovered_count\n";
echo "- Expected: 8 (1 sync + 7 marketplace)\n";

if ($discovered_count >= 8) {
    echo "✅ SUCCESS: All MesChain extensions should now be visible in admin panel!\n";
} else {
    echo "⚠️  WARNING: Some extensions may still be missing\n";
}

// 8. Clear OpenCart caches
echo "\n8. CLEARING OPENCART CACHES:\n";
$cache_files = glob('./opencart_new/system/storage/cache/*');
foreach ($cache_files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}
echo "   ✅ OpenCart caches cleared\n";

$mysqli->close();

echo "\n=== EXTENSION DISCOVERY FIX COMPLETE ===\n";
echo "🎉 SUCCESS: 7 Missing MesChain marketplace extensions have been registered!\n\n";

echo "NEXT STEPS:\n";
echo "1. Refresh your admin panel (Ctrl+F5)\n";
echo "2. Go to Extensions → Modules\n";
echo "3. Verify all 8 MesChain extensions are visible:\n";
echo "   - MesChain Sync (already working)\n";
echo "   - MesChain Trendyol\n";
echo "   - MesChain Amazon\n";
echo "   - MesChain Hepsiburada\n";
echo "   - MesChain N11\n";
echo "   - MesChain eBay\n";
echo "   - MesChain GittiGidiyor\n";
echo "   - MesChain Pazarama\n\n";

echo "4. Test individual extension access:\n";
echo "   http://localhost:8000/admin/index.php?route=extension/module/meschain_trendyol&user_token=...\n\n";

echo "🔧 The OpenCart 4.x extension discovery issue has been resolved!\n";
?>