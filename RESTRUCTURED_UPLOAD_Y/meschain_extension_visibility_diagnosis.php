<?php
// Comprehensive Extension Visibility Diagnosis Script
// This script will identify why the Trendyol Importer extension is not appearing in the admin panel

echo "<h1>🔍 MesChain-Sync Extension Visibility Diagnosis</h1>\n";
echo "<p>Analyzing why the Trendyol Importer extension is not visible in the admin panel...</p>\n";

// Database connection
$host = 'localhost';
$username = 'root';
$password = '1234';
$database = 'opencart_new';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Database connection successful</p>\n";
} catch (PDOException $e) {
    die("<p>❌ Database connection failed: " . $e->getMessage() . "</p>\n");
}

echo "<hr>\n";

// 1. Verify Extension Registration
echo "<h2>1. 📊 Extension Registration Status</h2>\n";
$stmt = $pdo->query("SELECT * FROM oc_extension WHERE code = 'trendyol_importer'");
$extension = $stmt->fetch(PDO::FETCH_ASSOC);

if ($extension) {
    echo "<p>✅ Extension found in database:</p>\n";
    echo "<ul>\n";
    echo "<li><strong>Extension ID:</strong> " . $extension['extension_id'] . "</li>\n";
    echo "<li><strong>Extension:</strong> " . $extension['extension'] . "</li>\n";
    echo "<li><strong>Type:</strong> " . $extension['type'] . "</li>\n";
    echo "<li><strong>Code:</strong> " . $extension['code'] . "</li>\n";
    echo "</ul>\n";
} else {
    echo "<p>❌ Extension not found in oc_extension table</p>\n";
}

echo "<hr>\n";

// 2. Check File Structure
echo "<h2>2. 📁 File Structure Verification</h2>\n";

$base_path = '/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart_new';
$required_files = [
    'admin/controller/extension/meschain/trendyol_importer.php',
    'admin/model/extension/meschain/trendyol_importer.php',
    'admin/language/en-gb/extension/meschain/trendyol_importer.php',
    'admin/view/template/extension/meschain/trendyol_importer_dashboard.twig',
    'admin/view/template/extension/meschain/trendyol_importer_wizard.twig',
    'admin/view/template/extension/meschain/trendyol_importer_progress.twig'
];

foreach ($required_files as $file) {
    $full_path = $base_path . '/' . $file;
    if (file_exists($full_path)) {
        echo "<p>✅ Found: $file</p>\n";
        echo "<p>&nbsp;&nbsp;&nbsp;Size: " . number_format(filesize($full_path)) . " bytes</p>\n";
    } else {
        echo "<p>❌ Missing: $file</p>\n";
    }
}

echo "<hr>\n";

// 3. Check Controller Structure
echo "<h2>3. 🎮 Controller Structure Analysis</h2>\n";

$controller_path = $base_path . '/admin/controller/extension/meschain/trendyol_importer.php';
if (file_exists($controller_path)) {
    $controller_content = file_get_contents($controller_path);
    
    // Check for required class and methods
    $checks = [
        'class.*ControllerExtensionMeschainTrendyolImporter' => 'Controller class definition',
        'function index' => 'index() method',
        'function install' => 'install() method', 
        'function uninstall' => 'uninstall() method'
    ];
    
    foreach ($checks as $pattern => $description) {
        if (preg_match('/' . $pattern . '/i', $controller_content)) {
            echo "<p>✅ $description found</p>\n";
        } else {
            echo "<p>❌ $description missing</p>\n";
        }
    }
    
    // Check for syntax errors
    $syntax_check = shell_exec("php -l $controller_path 2>&1");
    if (strpos($syntax_check, 'No syntax errors') !== false) {
        echo "<p>✅ Controller syntax is valid</p>\n";
    } else {
        echo "<p>❌ Controller syntax errors:</p>\n";
        echo "<pre>$syntax_check</pre>\n";
    }
} else {
    echo "<p>❌ Controller file not found</p>\n";
}

echo "<hr>\n";

// 4. Check Language File Structure
echo "<h2>4. 🌐 Language File Analysis</h2>\n";

$language_path = $base_path . '/admin/language/en-gb/extension/meschain/trendyol_importer.php';
if (file_exists($language_path)) {
    $language_content = file_get_contents($language_path);
    
    // Check for required language entries
    $required_entries = [
        '_text_title',
        '_heading_title', 
        '_text_extension'
    ];
    
    foreach ($required_entries as $entry) {
        if (strpos($language_content, $entry) !== false) {
            echo "<p>✅ Language entry '$entry' found</p>\n";
        } else {
            echo "<p>⚠️ Language entry '$entry' missing</p>\n";
        }
    }
    
    // Check for syntax errors
    $syntax_check = shell_exec("php -l $language_path 2>&1");
    if (strpos($syntax_check, 'No syntax errors') !== false) {
        echo "<p>✅ Language file syntax is valid</p>\n";
    } else {
        echo "<p>❌ Language file syntax errors:</p>\n";
        echo "<pre>$syntax_check</pre>\n";
    }
} else {
    echo "<p>❌ Language file not found</p>\n";
}

echo "<hr>\n";

// 5. Check OpenCart Extension Loading System
echo "<h2>5. ⚙️ OpenCart Extension Loading Analysis</h2>\n";

// Check if we can manually load the extension
$opencart_path = $base_path;
if (file_exists($opencart_path . '/admin/config.php')) {
    echo "<p>✅ OpenCart admin config found</p>\n";
    
    // Try to include OpenCart framework and load the extension
    try {
        // Include OpenCart startup
        if (file_exists($opencart_path . '/system/startup.php')) {
            echo "<p>✅ OpenCart startup.php found</p>\n";
        } else {
            echo "<p>❌ OpenCart startup.php not found</p>\n";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error loading OpenCart framework: " . $e->getMessage() . "</p>\n";
    }
} else {
    echo "<p>❌ OpenCart admin config not found</p>\n";
}

echo "<hr>\n";

// 6. Check for Module vs Extension Type Issues
echo "<h2>6. 🔄 Extension Type Compatibility</h2>\n";

// In OpenCart 4.x, modules should have specific structure
echo "<p>🔍 Checking OpenCart 4.0.2.3 module requirements...</p>\n";

// Check if extension follows module naming convention
$controller_class_expected = 'ControllerExtensionMeschainTrendyolImporter';
if (file_exists($controller_path)) {
    $controller_content = file_get_contents($controller_path);
    if (strpos($controller_content, $controller_class_expected) !== false) {
        echo "<p>✅ Controller class name follows OpenCart 4.x convention</p>\n";
    } else {
        echo "<p>❌ Controller class name doesn't follow OpenCart 4.x convention</p>\n";
        echo "<p>&nbsp;&nbsp;&nbsp;Expected: $controller_class_expected</p>\n";
    }
}

echo "<hr>\n";

// 7. Generate Extension Access URL
echo "<h2>7. 🌐 Direct Extension Access Test</h2>\n";

$extension_url = "http://localhost:8090/admin/index.php?route=extension/meschain/trendyol_importer";
echo "<p>Direct extension URL: <a href='$extension_url' target='_blank'>$extension_url</a></p>\n";

echo "<hr>\n";

// 8. Check OpenCart Extension Cache
echo "<h2>8. 🗂️ OpenCart Cache Analysis</h2>\n";

$cache_dirs = [
    $base_path . '/system/storage/cache',
    $base_path . '/admin/cache',
    $base_path . '/cache'
];

foreach ($cache_dirs as $cache_dir) {
    if (is_dir($cache_dir)) {
        $cache_files = glob($cache_dir . '/*');
        echo "<p>✅ Cache directory found: $cache_dir (" . count($cache_files) . " files)</p>\n";
    } else {
        echo "<p>⚠️ Cache directory not found: $cache_dir</p>\n";
    }
}

echo "<hr>\n";

// 9. Recommendations
echo "<h2>9. 💡 Diagnostic Recommendations</h2>\n";

$recommendations = [];

if (!$extension) {
    $recommendations[] = "Re-run the extension registration fix script";
}

if (!file_exists($controller_path)) {
    $recommendations[] = "Verify controller file exists and has correct permissions";
}

if (!file_exists($language_path)) {
    $recommendations[] = "Create missing language file";
}

$recommendations[] = "Clear OpenCart cache and refresh admin panel";
$recommendations[] = "Try accessing the extension directly via URL";
$recommendations[] = "Check OpenCart error logs for detailed error messages";

echo "<ol>\n";
foreach ($recommendations as $rec) {
    echo "<li>$rec</li>\n";
}
echo "</ol>\n";

echo "<hr>\n";
echo "<p><strong>🎯 Next Steps:</strong> Based on the analysis above, address any missing files or configuration issues, then test the extension visibility again.</p>\n";

?>