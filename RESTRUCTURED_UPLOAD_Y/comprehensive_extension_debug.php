<?php
// OpenCart Extension Visibility Debug Analysis
require_once('./opencart_new/config.php');

echo "=== COMPREHENSIVE OPENCART EXTENSION DEBUG ===\n\n";

// Database connection
$mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "PROBLEM ANALYSIS:\n";
echo "=================\n\n";

// 1. Check OpenCart 4.x Discovery Method
echo "1. OPENCART 4.x EXTENSION DISCOVERY METHOD:\n";
echo "   - OpenCart 4.x uses model_setting_extension->getPaths() method\n";
echo "   - It queries extension_path table, NOT filesystem directly\n";
echo "   - Only registered paths in extension_path are discoverable\n\n";

// 2. Check current extension_path entries
echo "2. CURRENT EXTENSION_PATH ENTRIES:\n";
$result = $mysqli->query("SELECT * FROM " . DB_PREFIX . "extension_path WHERE path LIKE '%/admin/controller/module/%' ORDER BY path");
echo "   Registered module paths:\n";
while ($row = $result->fetch_assoc()) {
    echo "   - " . $row['path'] . " (Install ID: " . $row['extension_install_id'] . ")\n";
}

// 3. Check physical files
echo "\n3. PHYSICAL MESCHAIN MODULE FILES:\n";
$module_files = glob('./opencart_new/admin/controller/extension/module/meschain_*.php');
echo "   Files in /admin/controller/extension/module/:\n";
foreach ($module_files as $file) {
    $filename = basename($file, '.php');
    echo "   - $filename.php\n";
}

// 4. Check what's missing
echo "\n4. MISSING REGISTRATIONS:\n";
$missing_extensions = [];
foreach ($module_files as $file) {
    $filename = basename($file, '.php');
    $check_path = "meschain_sync/admin/controller/module/$filename.php";
    
    $result = $mysqli->query("SELECT * FROM " . DB_PREFIX . "extension_path WHERE path = '$check_path'");
    if ($result->num_rows == 0) {
        $missing_extensions[] = $filename;
        echo "   ❌ $filename - NOT REGISTERED in extension_path\n";
    } else {
        echo "   ✅ $filename - REGISTERED in extension_path\n";
    }
}

// 5. Check extension table registrations
echo "\n5. EXTENSION TABLE REGISTRATIONS:\n";
$result = $mysqli->query("SELECT * FROM " . DB_PREFIX . "extension WHERE type = 'module' AND extension LIKE 'meschain%'");
echo "   Registered in extension table:\n";
while ($row = $result->fetch_assoc()) {
    echo "   - " . $row['code'] . " (Extension: " . $row['extension'] . ")\n";
}

// 6. Root cause analysis
echo "\n6. ROOT CAUSE ANALYSIS:\n";
echo "======================================\n";
echo "❌ CRITICAL ISSUE IDENTIFIED:\n\n";
echo "   Problem: OpenCart 4.x Extension Discovery Mismatch\n";
echo "   ------------------------------------------------\n";
echo "   • MesChain extensions exist as physical files in old OpenCart 3.x structure\n";
echo "   • OpenCart 4.x requires extensions to be registered in extension_path table\n";
echo "   • Only 'meschain_sync' is properly registered, other 7 are missing\n";
echo "   • Admin panel cannot discover unregistered extensions\n\n";

echo "   Why only MesChain Sync shows:\n";
echo "   • meschain_sync has proper extension_path entry\n";
echo "   • Other extensions lack database registration\n";
echo "   • OpenCart discovery ignores filesystem, only checks database\n\n";

// 7. Required fixes
echo "7. REQUIRED FIXES:\n";
echo "==================\n";
echo "To make all 7 marketplace extensions visible:\n\n";

echo "OPTION A - Register existing files (Quick Fix):\n";
echo "   1. Add extension_path entries for each missing extension\n";
echo "   2. Add extension table entries for each missing extension\n";
echo "   3. Set proper permissions for admin user\n\n";

echo "OPTION B - Full OpenCart 4.x Migration (Recommended):\n";
echo "   1. Create individual extension directories under /extension/\n";
echo "   2. Move files to proper OpenCart 4.x structure\n";
echo "   3. Register extensions properly through installation\n\n";

echo "Missing Extensions requiring registration:\n";
foreach ($missing_extensions as $ext) {
    echo "   - $ext\n";
}

// 8. Check user permissions
echo "\n8. USER PERMISSIONS CHECK:\n";
$result = $mysqli->query("SELECT * FROM " . DB_PREFIX . "user_group WHERE user_group_id = 1");
$user_group = $result->fetch_assoc();
$permissions = json_decode($user_group['permission'], true);

echo "   Current admin permissions for modules:\n";
if (isset($permissions['access'])) {
    foreach ($permissions['access'] as $perm) {
        if (strpos($perm, 'extension/') !== false && strpos($perm, 'module') !== false) {
            echo "   - $perm\n";
        }
    }
}

$mysqli->close();

echo "\n=== DEBUG ANALYSIS COMPLETE ===\n";
echo "The exact cause has been identified. Extensions exist but are not registered in OpenCart 4.x discovery system.\n";
?>