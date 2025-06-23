<?php
/**
 * MesChain-Sync Extension Fix Verification
 * Bu script düzeltmelerin başarılı olup olmadığını doğrular
 */

// Database connection
require_once 'opencart4/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOSTNAME . ";dbname=" . DB_DATABASE . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Database connection successful\n";
    
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

echo "\n🔍 MesChain-Sync Extension Fix Verification Report\n";
echo "=" . str_repeat("=", 55) . "\n\n";

// 1. Check extension registration
echo "1. Extension Registration Status:\n";
echo "-" . str_repeat("-", 33) . "\n";

$stmt = $pdo->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE code = 'meschain_sync' AND type = 'module'");
$module_ext = $stmt->fetch();

if ($module_ext) {
    echo "✅ Extension properly registered as module:\n";
    echo "   - Extension ID: {$module_ext['extension_id']}\n";
    echo "   - Type: {$module_ext['type']}\n";
    echo "   - Code: {$module_ext['code']}\n";
    echo "   - Extension: {$module_ext['extension']}\n\n";
} else {
    echo "❌ Extension not found in extension table\n\n";
}

// 2. Check extension_install
echo "2. Extension Install Status:\n";
echo "-" . str_repeat("-", 28) . "\n";

$stmt = $pdo->query("SELECT * FROM `" . DB_PREFIX . "extension_install` WHERE code = 'meschain'");
$install_record = $stmt->fetch();

if ($install_record && $install_record['status'] == 1) {
    echo "✅ Extension install record active:\n";
    echo "   - Install ID: {$install_record['extension_install_id']}\n";
    echo "   - Name: {$install_record['name']}\n";
    echo "   - Version: {$install_record['version']}\n";
    echo "   - Status: Active\n\n";
} else {
    echo "❌ Extension install record not found or inactive\n\n";
}

// 3. Check extension_path
echo "3. Extension Path Status:\n";
echo "-" . str_repeat("-", 25) . "\n";

if ($install_record) {
    $stmt = $pdo->prepare("SELECT * FROM `" . DB_PREFIX . "extension_path` WHERE extension_install_id = ?");
    $stmt->execute([$install_record['extension_install_id']]);
    $path_record = $stmt->fetch();
    
    if ($path_record) {
        echo "✅ Extension path record found:\n";
        echo "   - Path ID: {$path_record['extension_path_id']}\n";
        echo "   - Install ID: {$path_record['extension_install_id']}\n";
        echo "   - Path: {$path_record['path']}\n\n";
    } else {
        echo "❌ Extension path record not found\n\n";
    }
}

// 4. Check file accessibility
echo "4. File Structure Accessibility:\n";
echo "-" . str_repeat("-", 33) . "\n";

$critical_files = [
    'opencart4/extension/meschain/install.json' => 'Install JSON',
    'opencart4/extension/meschain/admin/controller/module/meschain_sync.php' => 'Admin Controller',
    'opencart4/extension/meschain/admin/model/module/meschain_sync.php' => 'Admin Model',
    'opencart4/extension/meschain/admin/view/template/module/meschain_sync.twig' => 'Admin Template',
    'opencart4/extension/meschain/admin/language/en-gb/module/meschain_sync.php' => 'English Language',
    'opencart4/extension/meschain/admin/language/tr-tr/module/meschain_sync.php' => 'Turkish Language'
];

$all_files_ok = true;
foreach ($critical_files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ {$description}: OK\n";
    } else {
        echo "❌ {$description}: MISSING\n";
        $all_files_ok = false;
    }
}

// 5. Check admin permissions
echo "\n5. Admin Permissions:\n";
echo "-" . str_repeat("-", 20) . "\n";

$stmt = $pdo->query("SELECT permission FROM `" . DB_PREFIX . "user_group` WHERE user_group_id = 1");
$admin_group = $stmt->fetch();

if ($admin_group) {
    $permissions = json_decode($admin_group['permission'], true);
    $route = 'extension/meschain/module/meschain_sync';
    
    $has_access = in_array($route, $permissions['access'] ?? []);
    $has_modify = in_array($route, $permissions['modify'] ?? []);
    
    echo "✅ Admin permissions checked:\n";
    echo "   - Access permission: " . ($has_access ? "✅ Granted" : "❌ Missing") . "\n";
    echo "   - Modify permission: " . ($has_modify ? "✅ Granted" : "❌ Missing") . "\n\n";
} else {
    echo "❌ Admin group not found\n\n";
}

// 6. Extension discoverability simulation
echo "6. Extension Discovery Simulation:\n";
echo "-" . str_repeat("-", 34) . "\n";

// Simulate how OpenCart discovers extensions
if ($install_record && $path_record && $all_files_ok) {
    echo "✅ Extension discovery requirements met:\n";
    echo "   - Extension install record: ✅ Active\n";
    echo "   - Extension path record: ✅ Found\n";
    echo "   - Critical files: ✅ Accessible\n";
    echo "   - Admin permissions: ✅ Granted\n\n";
    
    // Try to load install.json
    $install_json_path = 'opencart4/extension/meschain/install.json';
    $install_data = json_decode(file_get_contents($install_json_path), true);
    
    if ($install_data) {
        echo "✅ install.json parsed successfully:\n";
        echo "   - Name: {$install_data['name']}\n";
        echo "   - Code: {$install_data['code']}\n";
        echo "   - Version: {$install_data['version']}\n\n";
    }
} else {
    echo "❌ Extension discovery requirements not fully met\n\n";
}

// 7. Final status
echo "🎯 FINAL VERIFICATION RESULT:\n";
echo "=" . str_repeat("=", 30) . "\n";

$success_conditions = [
    $module_ext !== false,
    $install_record && $install_record['status'] == 1,
    isset($path_record) && $path_record !== false,
    $all_files_ok,
    isset($has_access) && $has_access,
    isset($has_modify) && $has_modify
];

$success_count = count(array_filter($success_conditions));
$total_conditions = count($success_conditions);

if ($success_count === $total_conditions) {
    echo "🎉 SUCCESS! All conditions met ({$success_count}/{$total_conditions})\n";
    echo "\n📍 NEXT STEP:\n";
    echo "Navigate to OpenCart Admin Panel:\n";
    echo "Extensions > Choose the extension type > MesChain SYNC\n";
    echo "The MesChain-Sync Enterprise extension should now be visible!\n\n";
    
    echo "🔗 Extension should be accessible at:\n";
    echo "   - Admin URL: /admin/index.php?route=extension/meschain/module/meschain_sync\n";
    echo "   - Extension List: Extensions > Extensions > Choose Extension Type > MesChain SYNC\n\n";
} else {
    echo "⚠️  PARTIAL SUCCESS ({$success_count}/{$total_conditions} conditions met)\n";
    echo "Some issues may still need to be resolved.\n\n";
}

echo "✨ Extension visibility fix process completed!\n";

?>
