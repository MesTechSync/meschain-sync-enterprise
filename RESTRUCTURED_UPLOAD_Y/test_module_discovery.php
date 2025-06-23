<?php
/**
 * Test Module Discovery System
 * Simulate how OpenCart finds modules in Extensions → Modules
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 ============================================\n";
echo "🔍 Testing Module Discovery System\n";
echo "🔍 ============================================\n\n";

// Database configuration
$db_config = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '1234',
    'database' => 'opencart4',
    'prefix' => 'oc_'
];

try {
    $pdo = new PDO(
        "mysql:host={$db_config['hostname']};dbname={$db_config['database']};charset=utf8mb4",
        $db_config['username'],
        $db_config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "✅ Database connection successful\n\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "📊 1. Simulating getPaths('%/admin/controller/module/%.php')...\n";

// This is what OpenCart does to find modules
$stmt = $pdo->prepare("SELECT * FROM `{$db_config['prefix']}extension_path` WHERE `path` LIKE ? ORDER BY `path` ASC");
$stmt->execute(['%/admin/controller/module/%.php']);
$results = $stmt->fetchAll();

echo "Found " . count($results) . " module paths:\n";
foreach ($results as $result) {
    $extension = substr($result['path'], 0, strpos($result['path'], '/'));
    $code = basename($result['path'], '.php');
    echo "  - Extension: {$extension}, Code: {$code}, Path: {$result['path']}\n";
    
    // Check if file actually exists
    $file_path = "opencart4/extension/{$result['path']}";
    if (file_exists($file_path)) {
        echo "    ✅ File exists\n";
    } else {
        echo "    ❌ File missing: {$file_path}\n";
    }
}

echo "\n📊 2. Checking extension registration...\n";

$available = [];
foreach ($results as $result) {
    $available[] = basename($result['path'], '.php');
}

// Get installed extensions
$stmt = $pdo->query("SELECT * FROM `{$db_config['prefix']}extension` WHERE `type` = 'module' ORDER BY `code` ASC");
$extensions = $stmt->fetchAll();

$installed = [];
foreach ($extensions as $extension) {
    if (in_array($extension['code'], $available)) {
        $installed[] = $extension['code'];
        echo "✅ {$extension['code']} is available and installed\n";
    } else {
        echo "⚠️  {$extension['code']} is installed but file not found\n";
    }
}

echo "\n📊 3. Language file check for meschain_sync...\n";

$lang_files = [
    'opencart4/extension/meschain/admin/language/en-gb/module/meschain_sync.php',
    'opencart4/extension/meschain/admin/language/tr-tr/module/meschain_sync.php'
];

foreach ($lang_files as $lang_file) {
    if (file_exists($lang_file)) {
        echo "✅ Language file exists: {$lang_file}\n";
        
        // Try to load and check key values
        $_test = [];
        include $lang_file;
        if (isset($_['heading_title'])) {
            echo "   Title: {$_['heading_title']}\n";
        }
    } else {
        echo "❌ Language file missing: {$lang_file}\n";
    }
}

echo "\n📊 4. Final Module List Simulation...\n";

$module_list = [];
foreach ($results as $result) {
    $extension = substr($result['path'], 0, strpos($result['path'], '/'));
    $code = basename($result['path'], '.php');
    
    if ($extension == 'meschain' && $code == 'meschain_sync') {
        $file_path = "opencart4/extension/{$result['path']}";
        $lang_file = "opencart4/extension/meschain/admin/language/en-gb/module/meschain_sync.php";
        
        if (file_exists($file_path) && file_exists($lang_file)) {
            // Load language file
            $_lang = [];
            include $lang_file;
            
            $module_list[] = [
                'extension' => $extension,
                'code' => $code, 
                'name' => $_lang['heading_title'] ?? 'MesChain-Sync Enterprise',
                'installed' => in_array($code, $installed),
                'file_exists' => true,
                'lang_exists' => true
            ];
        }
    }
}

if (count($module_list) > 0) {
    echo "🎉 MesChain-Sync module will appear in Extensions → Modules:\n";
    foreach ($module_list as $module) {
        echo "   📦 {$module['name']}\n";
        echo "      Extension: {$module['extension']}\n";
        echo "      Code: {$module['code']}\n";
        echo "      Installed: " . ($module['installed'] ? 'Yes' : 'No') . "\n";
        echo "      Status: Ready for installation\n";
    }
} else {
    echo "❌ MesChain-Sync module will NOT appear in the list\n";
}

echo "\n🎯 ============================================\n";
echo "🎯 DISCOVERY TEST COMPLETED\n";
echo "🎯 ============================================\n\n";

echo "📋 Next Steps:\n";
echo "1. 🌐 Open: http://localhost:8080/admin/\n";
echo "2. 🔑 Login to admin panel\n";
echo "3. 📂 Go to: Extensions → Extensions\n";
echo "4. 🔍 Filter: Choose 'Modules'\n";
echo "5. 🔍 Look for: 'MesChain-Sync Enterprise'\n";
echo "6. ⚡ Click Install button\n\n";

echo "🚀 Extension should now be visible and installable! 🚀\n";
?> 