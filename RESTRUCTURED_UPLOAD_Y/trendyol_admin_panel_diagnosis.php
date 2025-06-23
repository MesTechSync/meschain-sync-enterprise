<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Trendyol Admin Panel Diagnosis</h1>\n";

// Database connection
$host = 'localhost';
$port = 3306;
$username = 'root';
$password = '1234';
$database = 'opencart_new';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connection successful<br>\n";
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "<br>\n";
    exit;
}

echo "<h2>1. Controller File Validation</h2>\n";

// Check if admin controller exists
$controller_paths = [
    'opencart_new/admin/controller/extension/meschain/trendyol_importer.php',
    'opencart_new/admin/controller/extension/meschain_sync/trendyol_importer.php', 
    'opencart_new/admin/controller/extension/meschain_sync/module/meschain_sync.php',
    'opencart_new/admin/controller/extension/meschain/module/trendyol_importer.php'
];

foreach ($controller_paths as $path) {
    if (file_exists($path)) {
        echo "✓ Controller found: $path<br>\n";
        echo "  - File size: " . filesize($path) . " bytes<br>\n";
        echo "  - File permissions: " . substr(sprintf('%o', fileperms($path)), -4) . "<br>\n";
        
        // Check if it's readable
        if (is_readable($path)) {
            echo "  - File is readable<br>\n";
            
            // Check namespace/class content
            $content = file_get_contents($path);
            if (strpos($content, 'namespace') !== false) {
                preg_match('/namespace\s+([^;]+);/', $content, $matches);
                if ($matches) {
                    echo "  - Namespace found: " . $matches[1] . "<br>\n";
                }
            }
            
            if (strpos($content, 'class') !== false) {
                preg_match('/class\s+(\w+)/', $content, $matches);
                if ($matches) {
                    echo "  - Class found: " . $matches[1] . "<br>\n";
                }
            }
        } else {
            echo "  - ✗ File is not readable<br>\n";
        }
    } else {
        echo "✗ Controller not found: $path<br>\n";
    }
}

echo "<h2>2. Extension Registration Validation</h2>\n";

// Check extension registration
$stmt = $pdo->prepare("SELECT * FROM oc_extension WHERE type = 'module' AND code LIKE '%meschain%' OR code LIKE '%trendyol%'");
$stmt->execute();
$extensions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($extensions) {
    echo "✓ Extension registrations found:<br>\n";
    foreach ($extensions as $ext) {
        echo "  - ID: {$ext['extension_id']}, Type: {$ext['type']}, Code: {$ext['code']}<br>\n";
    }
} else {
    echo "✗ No extension registrations found<br>\n";
}

// Check module registration
$stmt = $pdo->prepare("SELECT * FROM oc_module WHERE code LIKE '%meschain%' OR code LIKE '%trendyol%' OR name LIKE '%trendyol%'");
$stmt->execute();
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($modules) {
    echo "✓ Module registrations found:<br>\n";
    foreach ($modules as $mod) {
        echo "  - ID: {$mod['module_id']}, Name: {$mod['name']}, Code: {$mod['code']}, Status: {$mod['status']}<br>\n";
    }
} else {
    echo "✗ No module registrations found<br>\n";
}

echo "<h2>3. Menu Integration Validation</h2>\n";

// Check if menu integration is working
$menu_paths = [
    'opencart_new/admin/language/en-gb/common/column_left.php',
    'opencart_new/admin/controller/common/column_left.php'
];

foreach ($menu_paths as $path) {
    if (file_exists($path)) {
        echo "✓ Menu file found: $path<br>\n";
        $content = file_get_contents($path);
        
        if (strpos($content, 'meschain') !== false || strpos($content, 'trendyol') !== false) {
            echo "  - ✓ Menu integration detected<br>\n";
        } else {
            echo "  - ✗ No menu integration found<br>\n";
        }
    } else {
        echo "✗ Menu file not found: $path<br>\n";
    }
}

echo "<h2>4. Route Testing</h2>\n";

// Test if OpenCart routing can find the controller
$test_routes = [
    'extension/meschain/trendyol_importer',
    'extension/meschain_sync/trendyol_importer',
    'extension/meschain_sync/module/meschain_sync',
    'extension/meschain/module/trendyol_importer'
];

foreach ($test_routes as $route) {
    $route_path = str_replace('/', DIRECTORY_SEPARATOR, $route);
    $controller_file = "opencart_new/admin/controller/{$route_path}.php";
    
    if (file_exists($controller_file)) {
        echo "✓ Route controller exists: $route -> $controller_file<br>\n";
    } else {
        echo "✗ Route controller missing: $route -> $controller_file<br>\n";
    }
}

echo "<h2>5. Template File Validation</h2>\n";

// Check template files
$template_paths = [
    'opencart_new/admin/view/template/extension/meschain/trendyol_importer_dashboard.twig',
    'opencart_new/admin/view/template/extension/meschain/trendyol_importer_wizard.twig',
    'opencart_new/admin/view/template/extension/meschain_sync/trendyol_importer.twig'
];

foreach ($template_paths as $path) {
    if (file_exists($path)) {
        echo "✓ Template found: $path<br>\n";
        echo "  - File size: " . filesize($path) . " bytes<br>\n";
    } else {
        echo "✗ Template not found: $path<br>\n";
    }
}

echo "<h2>6. Language File Validation</h2>\n";

// Check language files
$language_paths = [
    'opencart_new/admin/language/en-gb/extension/meschain/trendyol_importer.php',
    'opencart_new/admin/language/en-gb/extension/meschain_sync/trendyol_importer.php'
];

foreach ($language_paths as $path) {
    if (file_exists($path)) {
        echo "✓ Language file found: $path<br>\n";
        echo "  - File size: " . filesize($path) . " bytes<br>\n";
    } else {
        echo "✗ Language file not found: $path<br>\n";
    }
}

echo "<h2>7. Recommendation</h2>\n";

echo "<p><strong>Based on the diagnosis above, the most likely issue is:</strong></p>\n";
echo "<ul>\n";
echo "<li>Missing or incorrectly named admin controller file</li>\n";
echo "<li>Incorrect routing configuration for OpenCart 4.x</li>\n";
echo "<li>Extension not properly registered with the correct route</li>\n";
echo "</ul>\n";

echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ol>\n";
echo "<li>Verify the correct controller file location</li>\n";
echo "<li>Check the extension registration with correct route</li>\n";
echo "<li>Ensure OpenCart 4.x namespace compliance</li>\n";
echo "</ol>\n";

echo "<p>Diagnosis completed at: " . date('Y-m-d H:i:s') . "</p>\n";
?>