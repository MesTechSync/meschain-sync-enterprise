<?php
/**
 * Direct Access Test for Trendyol Extension
 * Tests if the extension controller is accessible and working
 */

// Database connection
$host = 'localhost';
$username = 'root';
$password = '1234';
$database = 'opencart_new';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== TRENDYOL EXTENSION ACCESS TEST ===\n";
    echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
    
    // 1. Verify extension registration
    echo "1. EXTENSION REGISTRATION VERIFICATION:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT * FROM oc_extension WHERE code = 'trendyol_importer' AND type = 'module'");
    $stmt->execute();
    $extension = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($extension) {
        echo "✅ Extension Found:\n";
        echo "  - ID: " . $extension['extension_id'] . "\n";
        echo "  - Extension: " . $extension['extension'] . "\n";
        echo "  - Type: " . $extension['type'] . "\n";
        echo "  - Code: " . $extension['code'] . "\n";
        echo "  - Status: " . ($extension['status'] ? 'ENABLED' : 'DISABLED') . "\n";
    } else {
        echo "❌ Extension not found in database\n";
        exit;
    }
    
    // 2. Verify module registration
    echo "\n2. MODULE REGISTRATION VERIFICATION:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT * FROM oc_module WHERE code = 'trendyol_importer'");
    $stmt->execute();
    $module = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($module) {
        echo "✅ Module Found:\n";
        echo "  - ID: " . $module['module_id'] . "\n";
        echo "  - Name: " . $module['name'] . "\n";
        echo "  - Code: " . $module['code'] . "\n";
        echo "  - Status: " . ($module['status'] ? 'ENABLED' : 'DISABLED') . "\n";
    } else {
        echo "❌ Module not found in database\n";
    }
    
    // 3. Check all module extensions count
    echo "\n3. ALL MODULE EXTENSIONS COUNT:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM oc_extension WHERE type = 'module' AND status = 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total enabled module extensions: " . $result['count'] . "\n";
    
    // List all enabled modules
    $stmt = $pdo->prepare("SELECT * FROM oc_extension WHERE type = 'module' AND status = 1");
    $stmt->execute();
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nEnabled Modules:\n";
    foreach ($modules as $mod) {
        echo "  - " . $mod['code'] . " (ID: " . $mod['extension_id'] . ")\n";
    }
    
    // 4. Test controller file accessibility
    echo "\n4. CONTROLLER FILE ACCESSIBILITY:\n";
    echo "----------------------------------------\n";
    
    $controller_path = 'opencart_new/admin/controller/extension/meschain/trendyol_importer.php';
    if (file_exists($controller_path)) {
        echo "✅ Controller file exists: $controller_path\n";
        
        // Check if it's readable
        if (is_readable($controller_path)) {
            echo "✅ Controller file is readable\n";
            
            // Check class content
            $content = file_get_contents($controller_path);
            if (strpos($content, 'class TrendyolImporter') !== false) {
                echo "✅ TrendyolImporter class found\n";
            } else {
                echo "❌ TrendyolImporter class not found\n";
            }
            
            // Check namespace
            if (strpos($content, 'namespace Opencart\\Admin\\Controller\\Extension\\Meschain') !== false) {
                echo "✅ Correct namespace found\n";
            } else {
                echo "❌ Correct namespace not found\n";
            }
            
            // Check index method
            if (strpos($content, 'public function index()') !== false) {
                echo "✅ Index method found\n";
            } else {
                echo "❌ Index method not found\n";
            }
        } else {
            echo "❌ Controller file is not readable\n";
        }
    } else {
        echo "❌ Controller file does not exist: $controller_path\n";
    }
    
    // 5. Generate direct access URLs
    echo "\n5. DIRECT ACCESS URLS:\n";
    echo "----------------------------------------\n";
    
    // Get a valid user token (simulate admin login)
    $stmt = $pdo->prepare("SELECT * FROM oc_user WHERE username = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Generate a simple token for testing
        $test_token = md5('test_token_' . time());
        
        echo "Admin User ID: " . $user['user_id'] . "\n";
        echo "Admin Username: " . $user['username'] . "\n";
        
        $base_url = "http://localhost:8000/admin/index.php";
        
        echo "\nDirect Access URLs to test:\n";
        echo "1. Extension List (Modules): {$base_url}?route=marketplace/extension&type=module&user_token={$test_token}\n";
        echo "2. Trendyol Direct Access: {$base_url}?route=extension/meschain/trendyol_importer&user_token={$test_token}\n";
        echo "3. Trendyol Dashboard: {$base_url}?route=extension/meschain/trendyol_importer/dashboard&user_token={$test_token}\n";
    }
    
    // 6. Test Extension Visibility Query
    echo "\n6. EXTENSION VISIBILITY QUERY TEST:\n";
    echo "----------------------------------------\n";
    
    // This is the query that OpenCart uses to populate the extensions list
    $query = "
        SELECT DISTINCT e.extension_id, e.extension, e.type, e.code, e.status
        FROM oc_extension e
        WHERE e.type = 'module'
        ORDER BY e.extension
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $all_modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Extensions visible in admin (type=module):\n";
    foreach ($all_modules as $ext) {
        $status_text = $ext['status'] ? 'ENABLED' : 'DISABLED';
        echo "  - " . $ext['code'] . " (" . $ext['extension'] . ") - " . $status_text . "\n";
        
        if ($ext['code'] === 'trendyol_importer') {
            echo "    ✅ TRENDYOL IMPORTER FOUND IN QUERY RESULTS!\n";
        }
    }
    
    // 7. Check OpenCart version compatibility
    echo "\n7. OPENCART VERSION COMPATIBILITY:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT * FROM oc_setting WHERE `key` = 'config_version'");
    $stmt->execute();
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($version) {
        echo "OpenCart Version: " . $version['value'] . "\n";
        if (version_compare($version['value'], '4.0.0', '>=')) {
            echo "✅ Version 4.x compatible\n";
        } else {
            echo "❌ Version compatibility issue - need 4.x\n";
        }
    }
    
    echo "\n=== ACCESS TEST COMPLETE ===\n";
    echo "If extension appears in query results but not in admin interface,\n";
    echo "the issue is likely with frontend JavaScript or template rendering.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>