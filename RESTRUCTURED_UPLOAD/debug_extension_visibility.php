<?php
/**
 * MesChain-Sync Extension Visibility Debug Tool
 * Bu script extension’ın neden listede görünmediğini tespit eder
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
    
    echo "✅ Database connection successful\n\n";
    
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

echo "🔍 MesChain-Sync Extension Visibility Debug Report\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// 1. Check extension table
echo "1. Extension Table Status:\n";
echo "-" . str_repeat("-", 30) . "\n";

$stmt = $pdo->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE code = 'meschain_sync'");
$extensions = $stmt->fetchAll();

if (empty($extensions)) {
    echo "❌ NO RECORDS found in extension table for 'meschain_sync'\n";
    echo "🔧 Solution: Extension needs to be installed\n\n";
} else {
    foreach ($extensions as $ext) {
        echo "✅ Extension found:\n";
        echo "   - Extension ID: {$ext['extension_id']}\n";
        echo "   - Type: {$ext['type']}\n";
        echo "   - Code: {$ext['code']}\n";
        echo "   - Extension: {$ext['extension']}\n";
        echo "   - Date Added: {$ext['date_added']}\n\n";
    }
}

// 2. Check extension_install table
echo "2. Extension Install Table Status:\n";
echo "-" . str_repeat("-", 35) . "\n";

$stmt = $pdo->query("SELECT * FROM `" . DB_PREFIX . "extension_install` WHERE code LIKE '%meschain%'");
$installs = $stmt->fetchAll();

if (empty($installs)) {
    echo "❌ NO RECORDS found in extension_install table\n";
    echo "🔧 Solution: Extension install record needs to be created\n\n";
} else {
    foreach ($installs as $install) {
        echo "✅ Extension install record found:\n";
        echo "   - Extension Install ID: {$install['extension_install_id']}\n";
        echo "   - Name: {$install['name']}\n";
        echo "   - Code: {$install['code']}\n";
        echo "   - Version: {$install['version']}\n";
        echo "   - Status: " . ($install['status'] ? 'Active' : 'Inactive') . "\n";
        echo "   - Date Added: {$install['date_added']}\n\n";
    }
}

// 3. Check extension_path table (OpenCart 4.x specific)
echo "3. Extension Path Table Status:\n";
echo "-" . str_repeat("-", 33) . "\n";

try {
    $stmt = $pdo->query("SELECT * FROM `" . DB_PREFIX . "extension_path` WHERE code = 'meschain'");
    $paths = $stmt->fetchAll();
    
    if (empty($paths)) {
        echo "❌ NO RECORDS found in extension_path table\n";
        echo "🔧 Solution: Extension path needs to be registered\n\n";
    } else {
        foreach ($paths as $path) {
            echo "✅ Extension path found:\n";
            echo "   - Extension Path ID: {$path['extension_path_id']}\n";
            echo "   - Code: {$path['code']}\n";
            echo "   - Path: {$path['path']}\n";
            echo "   - Status: " . ($path['status'] ? 'Active' : 'Inactive') . "\n";
            echo "   - Date Added: {$path['date_added']}\n\n";
        }
    }
} catch (PDOException $e) {
    echo "⚠️  extension_path table may not exist (older OpenCart version)\n\n";
}

// 4. Check file structure
echo "4. File Structure Check:\n";
echo "-" . str_repeat("-", 25) . "\n";

$required_files = [
    'extension/meschain/install.json',
    'extension/meschain/admin/controller/module/meschain_sync.php',
    'extension/meschain/admin/model/module/meschain_sync.php',
    'extension/meschain/admin/view/template/module/meschain_sync.twig',
    'extension/meschain/admin/language/en-gb/module/meschain_sync.php',
    'extension/meschain/admin/language/tr-tr/module/meschain_sync.php'
];

$opencart4_dir = 'opencart4/';
foreach ($required_files as $file) {
    $full_path = $opencart4_dir . $file;
    if (file_exists($full_path)) {
        echo "✅ {$file}\n";
    } else {
        echo "❌ {$file} - MISSING\n";
    }
}

// 5. Check install.json content
echo "\n5. Install.json Content:\n";
echo "-" . str_repeat("-", 25) . "\n";

$install_json_path = $opencart4_dir . 'extension/meschain/install.json';
if (file_exists($install_json_path)) {
    $install_data = json_decode(file_get_contents($install_json_path), true);
    if ($install_data) {
        echo "✅ install.json is valid JSON:\n";
        foreach ($install_data as $key => $value) {
            echo "   - {$key}: {$value}\n";
        }
    } else {
        echo "❌ install.json is invalid JSON\n";
    }
} else {
    echo "❌ install.json not found\n";
}

// 6. Check marketplace/extension controller access
echo "\n6. Extension Type Registration:\n";
echo "-" . str_repeat("-", 30) . "\n";

// Check if extension is registered with correct type
$stmt = $pdo->query("SELECT DISTINCT type, COUNT(*) as count FROM `" . DB_PREFIX . "extension` GROUP BY type");
$types = $stmt->fetchAll();

echo "Available extension types:\n";
foreach ($types as $type) {
    echo "   - {$type['type']} ({$type['count']} extensions)\n";
}

// Check specifically for module type
$stmt = $pdo->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE type = 'module' AND code = 'meschain_sync'");
$module_ext = $stmt->fetch();

if ($module_ext) {
    echo "\n✅ MesChain-Sync is registered as module type\n";
    echo "   Extension field value: '{$module_ext['extension']}'\n";
} else {
    echo "\n❌ MesChain-Sync is NOT registered as module type\n";
}

// 7. Generate fix recommendations
echo "\n🔧 RECOMMENDED FIXES:\n";
echo "=" . str_repeat("=", 20) . "\n";

$fixes = [];

// Check what's missing and provide fixes
if (empty($extensions)) {
    $fixes[] = "1. Run extension installation script";
    $fixes[] = "2. Insert extension record into database";
}

if (empty($installs)) {
    $fixes[] = "3. Add extension_install record";
}

if (empty($paths)) {
    $fixes[] = "4. Register extension path (if using OpenCart 4.x)";
}

if (!file_exists($install_json_path)) {
    $fixes[] = "5. Create proper install.json file";
}

if (empty($fixes)) {
    echo "✅ No critical issues found. Extension should be visible.\n";
    echo "💡 Try refreshing Extension Cache or checking permissions.\n";
} else {
    foreach ($fixes as $fix) {
        echo "{$fix}\n";
    }
}

echo "\n📝 NEXT STEPS:\n";
echo "-" . str_repeat("-", 12) . "\n";
echo "1. Run this script to identify issues\n";
echo "2. Apply recommended fixes\n";
echo "3. Clear OpenCart cache (Admin > Dashboard > Settings Cog)\n";
echo "4. Refresh Extensions page\n";
echo "5. Check Extensions > Choose extension type > MesChain SYNC\n";

?>
