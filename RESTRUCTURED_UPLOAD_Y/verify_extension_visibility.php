<?php
/**
 * Comprehensive Extension Visibility Verification Script
 * Diagnoses all aspects of extension registration and admin interface access
 */

// Database connection
$host = 'localhost';
$username = 'root';
$password = '1234';
$database = 'opencart_new';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== TRENDYOL EXTENSION VISIBILITY VERIFICATION ===\n";
    echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
    
    // 1. Check extension registration
    echo "1. EXTENSION REGISTRATION STATUS:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT * FROM oc_extension WHERE code LIKE '%trendyol%' OR code LIKE '%meschain%'");
    $stmt->execute();
    $extensions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($extensions) {
        foreach ($extensions as $ext) {
            echo "Extension ID: " . $ext['extension_id'] . "\n";
            echo "Type: " . $ext['type'] . "\n";
            echo "Code: " . $ext['code'] . "\n";
            echo "Status: " . ($ext['status'] ? 'ENABLED' : 'DISABLED') . "\n";
            echo "---\n";
        }
    } else {
        echo "❌ NO EXTENSIONS FOUND\n";
    }
    
    // 2. Check module registration
    echo "\n2. MODULE REGISTRATION STATUS:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT * FROM oc_module WHERE code LIKE '%trendyol%' OR code LIKE '%meschain%'");
    $stmt->execute();
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($modules) {
        foreach ($modules as $mod) {
            echo "Module ID: " . $mod['module_id'] . "\n";
            echo "Name: " . $mod['name'] . "\n";
            echo "Code: " . $mod['code'] . "\n";
            echo "Status: " . ($mod['status'] ? 'ENABLED' : 'DISABLED') . "\n";
            echo "---\n";
        }
    } else {
        echo "❌ NO MODULES FOUND\n";
    }
    
    // 3. Check all extension types in database
    echo "\n3. ALL AVAILABLE EXTENSION TYPES:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT type, COUNT(*) as count FROM oc_extension GROUP BY type ORDER BY type");
    $stmt->execute();
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($types as $type) {
        echo $type['type'] . " (" . $type['count'] . ")\n";
    }
    
    // 4. Check user permissions
    echo "\n4. USER PERMISSIONS:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT * FROM oc_user_group_permission WHERE permission LIKE '%extension/meschain%' OR permission LIKE '%trendyol%'");
    $stmt->execute();
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($permissions) {
        foreach ($permissions as $perm) {
            echo "User Group ID: " . $perm['user_group_id'] . "\n";
            echo "Permission: " . $perm['permission'] . "\n";
            echo "Type: " . $perm['type'] . "\n";
            echo "---\n";
        }
    } else {
        echo "❌ NO PERMISSIONS FOUND\n";
    }
    
    // 5. Check if controller file exists
    echo "\n5. CONTROLLER FILE VERIFICATION:\n";
    echo "----------------------------------------\n";
    
    $controller_path = 'opencart_new/admin/controller/extension/meschain/trendyol_importer.php';
    if (file_exists($controller_path)) {
        $file_size = filesize($controller_path);
        echo "✅ Controller file exists: $controller_path\n";
        echo "File size: $file_size bytes\n";
        
        // Check if file contains required class
        $content = file_get_contents($controller_path);
        if (strpos($content, 'class TrendyolImporter') !== false) {
            echo "✅ TrendyolImporter class found\n";
        } else {
            echo "❌ TrendyolImporter class NOT found\n";
        }
        
        if (strpos($content, 'namespace Opencart\\Admin\\Controller\\Extension\\Meschain') !== false) {
            echo "✅ Correct namespace found\n";
        } else {
            echo "❌ Correct namespace NOT found\n";
        }
    } else {
        echo "❌ Controller file does not exist: $controller_path\n";
    }
    
    // 6. Check language file
    echo "\n6. LANGUAGE FILE VERIFICATION:\n";
    echo "----------------------------------------\n";
    
    $language_path = 'opencart_new/admin/language/en-gb/extension/meschain/trendyol_importer.php';
    if (file_exists($language_path)) {
        echo "✅ Language file exists: $language_path\n";
    } else {
        echo "❌ Language file does not exist: $language_path\n";
    }
    
    // 7. Check template files
    echo "\n7. TEMPLATE FILES VERIFICATION:\n";
    echo "----------------------------------------\n";
    
    $templates = [
        'opencart_new/admin/view/template/extension/meschain/trendyol_importer_dashboard.twig',
        'opencart_new/admin/view/template/extension/meschain/trendyol_importer_wizard.twig',
        'opencart_new/admin/view/template/extension/meschain/trendyol_importer_progress.twig'
    ];
    
    foreach ($templates as $template) {
        if (file_exists($template)) {
            echo "✅ Template exists: " . basename($template) . "\n";
        } else {
            echo "❌ Template missing: " . basename($template) . "\n";
        }
    }
    
    // 8. Specific diagnostic for extension visibility
    echo "\n8. EXTENSION VISIBILITY DIAGNOSIS:\n";
    echo "----------------------------------------\n";
    
    // Check if extension is registered as 'module' type
    $stmt = $pdo->prepare("SELECT * FROM oc_extension WHERE type = 'module' AND code = 'trendyol_importer'");
    $stmt->execute();
    $module_ext = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($module_ext) {
        echo "✅ Extension registered as module type\n";
        echo "Extension ID: " . $module_ext['extension_id'] . "\n";
        echo "Status: " . ($module_ext['status'] ? 'ENABLED' : 'DISABLED') . "\n";
        
        if (!$module_ext['status']) {
            echo "⚠️  WARNING: Extension is DISABLED - this prevents visibility\n";
        }
    } else {
        echo "❌ Extension NOT registered as module type\n";
        
        // Check what type it is registered as
        $stmt = $pdo->prepare("SELECT * FROM oc_extension WHERE code = 'trendyol_importer'");
        $stmt->execute();
        $any_ext = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($any_ext) {
            echo "Found extension registered as type: " . $any_ext['type'] . "\n";
            echo "❌ PROBLEM: Extension should be registered as 'module' not '" . $any_ext['type'] . "'\n";
        }
    }
    
    // 9. Check OpenCart version compatibility
    echo "\n9. OPENCART VERSION COMPATIBILITY:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT * FROM oc_setting WHERE `key` = 'config_version'");
    $stmt->execute();
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($version) {
        echo "OpenCart Version: " . $version['value'] . "\n";
        if (version_compare($version['value'], '4.0.0', '>=')) {
            echo "✅ Compatible with OpenCart 4.x architecture\n";
        } else {
            echo "❌ Version compatibility issue\n";
        }
    }
    
    // 10. Generate fix recommendations
    echo "\n10. FIX RECOMMENDATIONS:\n";
    echo "----------------------------------------\n";
    
    $issues = [];
    
    if (!$module_ext) {
        $issues[] = "Extension not registered as 'module' type";
    } elseif (!$module_ext['status']) {
        $issues[] = "Extension is disabled";
    }
    
    if (!file_exists($controller_path)) {
        $issues[] = "Controller file missing";
    }
    
    if (empty($permissions)) {
        $issues[] = "User permissions not set";
    }
    
    if (empty($issues)) {
        echo "✅ No major issues detected. Extension should be visible.\n";
        echo "If dropdown still not working, this may be a JavaScript/frontend issue.\n";
    } else {
        echo "❌ Issues found:\n";
        foreach ($issues as $issue) {
            echo "- $issue\n";
        }
    }
    
    echo "\n=== VERIFICATION COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>