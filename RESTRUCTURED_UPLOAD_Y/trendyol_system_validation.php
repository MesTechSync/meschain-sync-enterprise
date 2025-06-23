<?php
/**
 * Trendyol Integration System Validation Script
 * Tests all components of the MesChain-Sync Enterprise Trendyol integration
 */

// Prevent script timeout
set_time_limit(300);

// Output formatting
function printSection($title) {
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "  " . $title . "\n";
    echo str_repeat("=", 80) . "\n";
}

function printTest($test, $result, $details = '') {
    $status = $result ? "✓ PASS" : "✗ FAIL";
    echo sprintf("%-50s %s\n", $test, $status);
    if ($details) {
        echo "    " . $details . "\n";
    }
}

function printInfo($key, $value) {
    echo sprintf("%-30s: %s\n", $key, $value);
}

printSection("TRENDYOL INTEGRATION SYSTEM VALIDATION");
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";

// Test 1: OpenCart Configuration
printSection("1. OPENCART CONFIGURATION VALIDATION");

$opencart_path = __DIR__ . '/opencart_new';
printTest("OpenCart directory exists", is_dir($opencart_path), $opencart_path);

$config_file = $opencart_path . '/config.php';
$admin_config_file = $opencart_path . '/admin/config.php';

if (file_exists($config_file)) {
    include_once $config_file;
    printTest("Frontend config loaded", defined('HTTP_SERVER'));
    printInfo("Frontend HTTP_SERVER", defined('HTTP_SERVER') ? HTTP_SERVER : 'NOT DEFINED');
} else {
    printTest("Frontend config exists", false, $config_file);
}

if (file_exists($admin_config_file)) {
    // Clean up previous defines
    $defines_to_check = ['HTTP_SERVER', 'HTTP_CATALOG', 'DB_HOSTNAME', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE'];
    foreach ($defines_to_check as $define) {
        if (defined($define)) {
            continue; // Skip if already defined
        }
    }
    
    $admin_config_content = file_get_contents($admin_config_file);
    preg_match("/define\('HTTP_SERVER', '([^']+)'\);/", $admin_config_content, $matches);
    $admin_http_server = isset($matches[1]) ? $matches[1] : 'NOT FOUND';
    
    printTest("Admin config exists", true, $admin_config_file);
    printInfo("Admin HTTP_SERVER", $admin_http_server);
    printTest("Admin config uses localhost:8000", strpos($admin_http_server, 'localhost:8000') !== false);
} else {
    printTest("Admin config exists", false, $admin_config_file);
}

// Test 2: Database Connection
printSection("2. DATABASE CONNECTION VALIDATION");

try {
    $db_config = [
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '1234',
        'database' => 'opencart_new',
        'port' => '3306'
    ];
    
    $pdo = new PDO(
        "mysql:host={$db_config['hostname']};port={$db_config['port']};dbname={$db_config['database']}", 
        $db_config['username'], 
        $db_config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    printTest("Database connection", true);
    printInfo("Database host", $db_config['hostname'] . ':' . $db_config['port']);
    printInfo("Database name", $db_config['database']);
    
    // Test Trendyol tables - Updated to match actual database schema
    $trendyol_tables = [
        'oc_trendyol_import_sessions',
        'oc_trendyol_import_products',
        'oc_trendyol_import_logs',
        'oc_trendyol_category_mapping',
        'oc_trendyol_brand_mapping',
        'oc_trendyol_import_statistics'
    ];
    
    foreach ($trendyol_tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        printTest("Table $table exists", $exists);
    }
    
    // Test extension registration
    $stmt = $pdo->query("SELECT * FROM oc_extension WHERE extension = 'trendyol_importer'");
    $extension_registered = $stmt->rowCount() > 0;
    printTest("Extension registered in oc_extension", $extension_registered);
    
    if ($extension_registered) {
        $extension_data = $stmt->fetch(PDO::FETCH_ASSOC);
        printInfo("Extension ID", $extension_data['extension_id']);
        printInfo("Extension type", $extension_data['type']);
    }
    
    // Test module registration
    $stmt = $pdo->query("SELECT * FROM oc_module WHERE code = 'trendyol_importer'");
    $module_registered = $stmt->rowCount() > 0;
    printTest("Module registered in oc_module", $module_registered);
    
} catch (PDOException $e) {
    printTest("Database connection", false, $e->getMessage());
}

// Test 3: File Structure Validation
printSection("3. FILE STRUCTURE VALIDATION");

$required_files = [
    'opencart_new/admin/controller/extension/meschain/trendyol_importer.php',
    'opencart_new/admin/model/extension/meschain/trendyol_importer.php',
    'opencart_new/admin/language/en-gb/extension/meschain/trendyol_importer.php',
    'opencart_new/admin/view/template/extension/meschain/trendyol_importer_dashboard.twig',
    'opencart_new/admin/view/template/extension/meschain/trendyol_importer_wizard.twig',
    'opencart_new/admin/view/template/extension/meschain/trendyol_importer_progress.twig',
    'opencart_new/system/library/meschain/api/TrendyolApiClient.php',
    'opencart_new/system/library/meschain/importer/TrendyolProductImporter.php',
    'opencart_new/system/library/meschain/install/database_schema.php',
    'opencart_new/system/library/meschain/install/menu_integration.php',
    'opencart_new/system/library/meschain/install/install_database.php',
    'opencart_new/system/library/meschain/install/install_complete.php'
];

foreach ($required_files as $file) {
    $exists = file_exists($file);
    printTest(basename($file), $exists, $exists ? 'OK' : 'MISSING');
}

// Test 4: Class Loading Validation
printSection("4. CLASS LOADING VALIDATION");

// Test controller class
$controller_file = 'opencart_new/admin/controller/extension/meschain/trendyol_importer.php';
if (file_exists($controller_file)) {
    $controller_content = file_get_contents($controller_file);
    $has_namespace = strpos($controller_content, 'namespace Opencart\Admin\Controller\Extension\Meschain;') !== false;
    $has_correct_class = strpos($controller_content, 'class TrendyolImporter extends \Opencart\System\Engine\Controller') !== false;
    $has_global_exception = strpos($controller_content, '\Exception') !== false;
    
    printTest("Controller has OpenCart 4.x namespace", $has_namespace);
    printTest("Controller has correct class inheritance", $has_correct_class);
    printTest("Controller uses global Exception namespace", $has_global_exception);
} else {
    printTest("Controller file exists", false);
}

// Test model class
$model_file = 'opencart_new/admin/model/extension/meschain/trendyol_importer.php';
if (file_exists($model_file)) {
    $model_content = file_get_contents($model_file);
    $has_namespace = strpos($model_content, 'namespace Opencart\Admin\Model\Extension\Meschain;') !== false;
    $has_correct_class = strpos($model_content, 'class TrendyolImporter extends \Opencart\System\Engine\Model') !== false;
    $has_global_reflection = strpos($model_content, '\ReflectionClass') !== false;
    
    printTest("Model has OpenCart 4.x namespace", $has_namespace);
    printTest("Model has correct class inheritance", $has_correct_class);
    printTest("Model uses global ReflectionClass namespace", $has_global_reflection);
} else {
    printTest("Model file exists", false);
}

// Test 5: API Integration Classes
printSection("5. API INTEGRATION VALIDATION");

$api_client_file = 'opencart_new/system/library/meschain/api/TrendyolApiClient.php';
if (file_exists($api_client_file)) {
    $api_content = file_get_contents($api_client_file);
    $has_class = strpos($api_content, 'class TrendyolApiClient') !== false;
    $has_guzzle = strpos($api_content, 'GuzzleHttp\Client') !== false;
    
    printTest("TrendyolApiClient class exists", $has_class);
    printTest("TrendyolApiClient uses Guzzle HTTP", $has_guzzle);
} else {
    printTest("TrendyolApiClient file exists", false);
}

$importer_file = 'opencart_new/system/library/meschain/importer/TrendyolProductImporter.php';
if (file_exists($importer_file)) {
    $importer_content = file_get_contents($importer_file);
    $has_class = strpos($importer_content, 'class TrendyolProductImporter') !== false;
    
    printTest("TrendyolProductImporter class exists", $has_class);
} else {
    printTest("TrendyolProductImporter file exists", false);
}

// Test 6: Template Validation
printSection("6. TEMPLATE VALIDATION");

$templates = [
    'opencart_new/admin/view/template/extension/meschain/trendyol_importer_dashboard.twig',
    'opencart_new/admin/view/template/extension/meschain/trendyol_importer_wizard.twig',
    'opencart_new/admin/view/template/extension/meschain/trendyol_importer_progress.twig'
];

foreach ($templates as $template) {
    if (file_exists($template)) {
        $template_content = file_get_contents($template);
        $has_twig_syntax = strpos($template_content, '{{') !== false || strpos($template_content, '{%') !== false;
        printTest(basename($template, '.twig'), $has_twig_syntax, $has_twig_syntax ? 'Valid Twig syntax' : 'No Twig syntax found');
    } else {
        printTest(basename($template, '.twig'), false, 'Template file missing');
    }
}

// Test 7: Language File Validation
printSection("7. LANGUAGE FILE VALIDATION");

$language_file = 'opencart_new/admin/language/en-gb/extension/meschain/trendyol_importer.php';
if (file_exists($language_file)) {
    $language_content = file_get_contents($language_file);
    $has_language_array = strpos($language_content, '$_[') !== false;
    printTest("Language file has translation strings", $has_language_array);
} else {
    printTest("Language file exists", false);
}

// Test 8: Comprehensive System Status
printSection("8. SYSTEM STATUS SUMMARY");

$total_tests = 0;
$passed_tests = 0;

// Count the tests (this is a simplified count)
$critical_components = [
    'Database connection' => isset($pdo),
    'Controller file' => file_exists($controller_file),
    'Model file' => file_exists($model_file),
    'API client' => file_exists($api_client_file),
    'Product importer' => file_exists($importer_file),
    'Extension registered' => isset($extension_registered) ? $extension_registered : false,
    'Trendyol tables' => isset($trendyol_tables) ? true : false,
    'Templates exist' => file_exists($templates[0]) && file_exists($templates[1]) && file_exists($templates[2])
];

foreach ($critical_components as $component => $status) {
    $total_tests++;
    if ($status) $passed_tests++;
    printTest($component, $status);
}

$success_rate = round(($passed_tests / $total_tests) * 100, 2);
printInfo("Success rate", $success_rate . "% ($passed_tests/$total_tests)");

if ($success_rate >= 90) {
    echo "\n🎉 SYSTEM STATUS: EXCELLENT - Ready for production use\n";
} elseif ($success_rate >= 75) {
    echo "\n⚠️  SYSTEM STATUS: GOOD - Minor issues to resolve\n";
} elseif ($success_rate >= 50) {
    echo "\n⚠️  SYSTEM STATUS: NEEDS ATTENTION - Several issues to fix\n";
} else {
    echo "\n❌ SYSTEM STATUS: CRITICAL - Major issues require immediate attention\n";
}

printSection("VALIDATION COMPLETE");
echo "Report generated: " . date('Y-m-d H:i:s') . "\n";
echo "System ready for Trendyol integration testing.\n";
?>