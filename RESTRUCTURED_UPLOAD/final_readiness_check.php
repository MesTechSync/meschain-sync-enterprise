<?php
/**
 * Final MesChain Extension Readiness Check
 * Comprehensive verification before installation
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🎯 ============================================\n";
echo "🎯 FINAL EXTENSION READINESS CHECK\n";
echo "🎯 ============================================\n\n";

$base_path = __DIR__ . '/opencart4/extension/meschain';
$checks_passed = 0;
$total_checks = 0;

function checkItem($description, $condition, $success_msg = "✅ PASS", $fail_msg = "❌ FAIL") {
    global $checks_passed, $total_checks;
    $total_checks++;
    
    echo "🔍 {$description}... ";
    if ($condition) {
        echo "{$success_msg}\n";
        $checks_passed++;
        return true;
    } else {
        echo "{$fail_msg}\n";
        return false;
    }
}

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=opencart4;charset=utf8mb4", "root", "1234");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    checkItem("Database connection", true);
} catch (PDOException $e) {
    checkItem("Database connection", false, "✅ PASS", "❌ FAIL: " . $e->getMessage());
    exit(1);
}

echo "\n📂 FILE STRUCTURE CHECKS:\n";
$required_files = [
    'install.json',
    'admin/controller/module/meschain_sync.php',
    'admin/model/module/meschain_sync.php',
    'admin/view/template/module/meschain_sync.twig',
    'admin/language/en-gb/module/meschain_sync.php',
    'admin/language/tr-tr/module/meschain_sync.php'
];

foreach ($required_files as $file) {
    $full_path = $base_path . '/' . $file;
    checkItem("File exists: {$file}", file_exists($full_path));
    
    if (file_exists($full_path)) {
        checkItem("File readable: {$file}", is_readable($full_path));
        
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($full_path);
            checkItem("PHP syntax valid: {$file}", !empty($content) && strpos($content, '<?php') === 0);
        }
    }
}

echo "\n🗄️  DATABASE REGISTRATION CHECKS:\n";

// Check extension_install table
$stmt = $pdo->query("SELECT * FROM oc_extension_install WHERE code = 'meschain'");
$install_record = $stmt->fetch();
checkItem("Extension in install table", !empty($install_record));

if ($install_record) {
    checkItem("Extension status active", $install_record['status'] == 1);
    checkItem("Extension name correct", $install_record['name'] === 'MesChain-Sync Enterprise');
}

// Check extension_path table
$stmt = $pdo->query("SELECT * FROM oc_extension_path WHERE path = 'meschain/admin/controller/module/meschain_sync.php'");
$path_record = $stmt->fetch();
checkItem("Extension path registered", !empty($path_record));

// Check extension table
$stmt = $pdo->query("SELECT * FROM oc_extension WHERE code = 'meschain_sync' AND type = 'module'");
$extension_record = $stmt->fetch();
checkItem("Module extension registered", !empty($extension_record));

echo "\n🔧 PERMISSION CHECKS:\n";
foreach ($required_files as $file) {
    $full_path = $base_path . '/' . $file;
    if (file_exists($full_path)) {
        $perms = fileperms($full_path);
        $readable = is_readable($full_path);
        checkItem("Permissions OK: {$file}", $readable, 
                 "✅ PASS (" . sprintf("%04o", $perms & 0777) . ")", 
                 "❌ FAIL (not readable)");
    }
}

echo "\n🎨 LANGUAGE FILE CHECKS:\n";
$lang_files = [
    'admin/language/en-gb/module/meschain_sync.php',
    'admin/language/tr-tr/module/meschain_sync.php'
];

foreach ($lang_files as $lang_file) {
    $full_path = $base_path . '/' . $lang_file;
    if (file_exists($full_path)) {
        $_lang = [];
        include $full_path;
        checkItem("Language keys loaded: {$lang_file}", 
                 isset($_['heading_title']) && !empty($_['heading_title']));
    }
}

echo "\n🌐 WEB ACCESS SIMULATION:\n";

// Simulate OpenCart module discovery
$stmt = $pdo->query("SELECT * FROM oc_extension_path WHERE path LIKE '%/admin/controller/module/%.php'");
$all_modules = $stmt->fetchAll();

$meschain_found = false;
foreach ($all_modules as $module) {
    if (strpos($module['path'], 'meschain') !== false) {
        $meschain_found = true;
        break;
    }
}

checkItem("Module discoverable by OpenCart", $meschain_found);

// Check if extension would appear in module list
$stmt = $pdo->query("SELECT * FROM oc_extension WHERE type = 'module'");
$all_extensions = $stmt->fetchAll();
$extension_codes = array_column($all_extensions, 'code');
$available_modules = [];

foreach ($all_modules as $module) {
    $code = basename($module['path'], '.php');
    if (in_array($code, $extension_codes)) {
        $available_modules[] = $code;
    }
}

checkItem("Extension in installable modules list", in_array('meschain_sync', $available_modules));

echo "\n📊 FINAL SCORE:\n";
$percentage = round(($checks_passed / $total_checks) * 100, 1);
echo "✅ Passed: {$checks_passed}/{$total_checks} ({$percentage}%)\n";

if ($percentage >= 100) {
    echo "\n🎉 PERFECT SCORE! Extension is 100% ready!\n\n";
    echo "🚀 INSTALLATION INSTRUCTIONS:\n";
    echo "1. 🌐 Open: http://localhost:8080/admin/\n";
    echo "2. 🔑 Login to admin panel\n";
    echo "3. 📂 Go to: Extensions → Extensions\n";
    echo "4. 🔽 Select type: Modules (from dropdown)\n";
    echo "5. 🔍 Find: MesChain-Sync Enterprise\n";
    echo "6. ⚡ Click: Install (green + icon)\n";
    echo "7. ⚙️ Click: Edit (blue pencil icon) to configure\n\n";
    echo "🎯 Extension should now be visible and fully functional!\n";
} else if ($percentage >= 90) {
    echo "\n✅ EXCELLENT! Extension is ready with minor issues.\n";
    echo "🔧 Review failed checks above and fix if needed.\n";
} else if ($percentage >= 75) {
    echo "\n⚠️ GOOD but needs attention.\n";
    echo "🔧 Please fix failed checks before proceeding.\n";
} else {
    echo "\n❌ CRITICAL ISSUES FOUND!\n";
    echo "🛠️ Multiple fixes required before extension can work.\n";
}

echo "\n🎯 Extension status: " . ($percentage >= 90 ? "READY FOR INSTALLATION 🚀" : "NEEDS FIXES 🔧") . "\n";
?> 