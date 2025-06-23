<?php
/**
 * Comprehensive Admin Panel Diagnosis for Trendyol Integration
 * Purpose: Identify and resolve all remaining admin interface issues
 * 
 * MesChain-Sync Enterprise v1.0.0
 * Debug Mode: Comprehensive Analysis
 */

// Database configuration
$host = 'localhost';
$port = 3306;
$username = 'root';
$password = '1234';
$database = 'opencart_new';

echo "=== COMPREHENSIVE ADMIN PANEL DIAGNOSIS ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "Database: {$database}\n\n";

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", 
                   $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "✅ Database connection successful\n\n";

    // 1. Check Extension Registration Status
    echo "1. EXTENSION REGISTRATION ANALYSIS\n";
    echo "=====================================\n";
    
    $stmt = $pdo->query("SELECT * FROM oc_extension WHERE type = 'module'");
    $extensions = $stmt->fetchAll();
    
    echo "Found " . count($extensions) . " module extensions:\n";
    foreach ($extensions as $ext) {
        echo "  - ID: {$ext['extension_id']}, Code: '{$ext['code']}', Type: '{$ext['type']}'\n";
        
        if (strpos($ext['code'], 'meschain') !== false || strpos($ext['code'], 'trendyol') !== false) {
            echo "    🎯 TRENDYOL EXTENSION FOUND!\n";
        }
    }
    echo "\n";

    // 2. Check Module Table
    echo "2. MODULE TABLE ANALYSIS\n";
    echo "========================\n";
    
    $stmt = $pdo->query("SELECT * FROM oc_module");
    $modules = $stmt->fetchAll();
    
    echo "Found " . count($modules) . " modules:\n";
    foreach ($modules as $module) {
        echo "  - ID: {$module['module_id']}, Name: '{$module['name']}', Code: '{$module['code']}', Status: {$module['status']}\n";
        
        if (strpos($module['code'], 'meschain') !== false || strpos($module['code'], 'trendyol') !== false) {
            echo "    🎯 TRENDYOL MODULE FOUND!\n";
            echo "    Settings: " . substr($module['setting'], 0, 100) . "...\n";
        }
    }
    echo "\n";

    // 3. Check Controller File Accessibility
    echo "3. CONTROLLER FILE VERIFICATION\n";
    echo "===============================\n";
    
    $controller_paths = [
        'opencart_new/admin/controller/extension/meschain/trendyol_importer.php',
        'opencart_new/admin/controller/extension/meschain_sync/module/meschain_sync.php',
        'opencart_new/admin/controller/extension/trendyol_importer/module/trendyol_importer.php'
    ];
    
    foreach ($controller_paths as $path) {
        if (file_exists($path)) {
            $size = filesize($path);
            echo "  ✅ {$path} (Size: {$size} bytes)\n";
            
            // Check controller content
            $content = file_get_contents($path);
            if (strpos($content, 'class TrendyolImporter') !== false) {
                echo "    ✅ TrendyolImporter class found\n";
            }
            if (strpos($content, 'namespace Opencart') !== false) {
                echo "    ✅ OpenCart 4.x namespace found\n";
            }
        } else {
            echo "  ❌ {$path} - NOT FOUND\n";
        }
    }
    echo "\n";

    // 4. Check Template Files
    echo "4. TEMPLATE FILE VERIFICATION\n";
    echo "=============================\n";
    
    $template_paths = [
        'opencart_new/admin/view/template/extension/meschain/trendyol_importer_dashboard.twig',
        'opencart_new/admin/view/template/extension/meschain/trendyol_importer_wizard.twig',
        'opencart_new/admin/view/template/extension/meschain/trendyol_importer_progress.twig'
    ];
    
    foreach ($template_paths as $path) {
        if (file_exists($path)) {
            $size = filesize($path);
            echo "  ✅ {$path} (Size: {$size} bytes)\n";
        } else {
            echo "  ❌ {$path} - NOT FOUND\n";
        }
    }
    echo "\n";

    // 5. Check Language Files
    echo "5. LANGUAGE FILE VERIFICATION\n";
    echo "=============================\n";
    
    $language_paths = [
        'opencart_new/admin/language/en-gb/extension/meschain/trendyol_importer.php'
    ];
    
    foreach ($language_paths as $path) {
        if (file_exists($path)) {
            $size = filesize($path);
            echo "  ✅ {$path} (Size: {$size} bytes)\n";
        } else {
            echo "  ❌ {$path} - NOT FOUND\n";
        }
    }
    echo "\n";

    // 6. Route Analysis
    echo "6. ROUTE MAPPING ANALYSIS\n";
    echo "=========================\n";
    
    $possible_routes = [
        'extension/meschain/trendyol_importer',
        'extension/meschain_sync/module/meschain_sync',
        'extension/trendyol_importer/module/trendyol_importer'
    ];
    
    echo "Expected routes for Trendyol integration:\n";
    foreach ($possible_routes as $route) {
        echo "  - {$route}\n";
    }
    echo "\n";

    // 7. Check User Permissions
    echo "7. USER PERMISSIONS CHECK\n";
    echo "=========================\n";
    
    $stmt = $pdo->query("SELECT * FROM oc_user_group WHERE user_group_id = 1");
    $admin_group = $stmt->fetch();
    
    if ($admin_group) {
        echo "Administrator group found:\n";
        echo "  Name: {$admin_group['name']}\n";
        
        $permissions = json_decode($admin_group['permission'], true);
        
        if (isset($permissions['access'])) {
            $access_perms = $permissions['access'];
            echo "  Access permissions count: " . count($access_perms) . "\n";
            
            $trendyol_perms = array_filter($access_perms, function($perm) {
                return strpos($perm, 'meschain') !== false || strpos($perm, 'trendyol') !== false;
            });
            
            if (!empty($trendyol_perms)) {
                echo "  🎯 Trendyol-related permissions:\n";
                foreach ($trendyol_perms as $perm) {
                    echo "    - {$perm}\n";
                }
            } else {
                echo "  ⚠️  No Trendyol-specific permissions found\n";
            }
        }
    }
    echo "\n";

    // 8. Check Database Schema
    echo "8. TRENDYOL DATABASE SCHEMA CHECK\n";
    echo "=================================\n";
    
    $trendyol_tables = [
        'trendyol_products',
        'trendyol_categories', 
        'trendyol_imports',
        'trendyol_logs',
        'trendyol_config',
        'trendyol_statistics'
    ];
    
    foreach ($trendyol_tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() > 0) {
                $count_stmt = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
                $count = $count_stmt->fetch()['count'];
                echo "  ✅ {$table} (Records: {$count})\n";
            } else {
                echo "  ❌ {$table} - NOT FOUND\n";
            }
        } catch (Exception $e) {
            echo "  ❌ {$table} - ERROR: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";

    // 9. Problem Identification and Solutions
    echo "9. PROBLEM IDENTIFICATION & SOLUTIONS\n";
    echo "=====================================\n";
    
    $problems = [];
    $solutions = [];
    
    // Check if extension is registered but not visible
    $meschain_extension = null;
    foreach ($extensions as $ext) {
        if (strpos($ext['code'], 'meschain') !== false || strpos($ext['code'], 'trendyol') !== false) {
            $meschain_extension = $ext;
            break;
        }
    }
    
    if ($meschain_extension) {
        echo "✅ Extension is registered in database\n";
        
        // Check if module entry exists
        $meschain_module = null;
        foreach ($modules as $module) {
            if ($module['code'] === $meschain_extension['code']) {
                $meschain_module = $module;
                break;
            }
        }
        
        if (!$meschain_module) {
            $problems[] = "Extension registered but no module entry found";
            $solutions[] = "CREATE module entry in oc_module table";
        } else {
            echo "✅ Module entry exists\n";
            
            if ($meschain_module['status'] == 0) {
                $problems[] = "Module is disabled";
                $solutions[] = "ENABLE module in oc_module table";
            }
        }
    } else {
        $problems[] = "Extension not found in database";
        $solutions[] = "REGISTER extension in oc_extension table";
    }
    
    // Check controller file
    $main_controller_exists = file_exists('opencart_new/admin/controller/extension/meschain/trendyol_importer.php');
    if (!$main_controller_exists) {
        $problems[] = "Main controller file missing";
        $solutions[] = "VERIFY controller file location and permissions";
    }
    
    echo "\n🔍 IDENTIFIED PROBLEMS:\n";
    foreach ($problems as $i => $problem) {
        echo "  " . ($i + 1) . ". {$problem}\n";
    }
    
    echo "\n🛠️  RECOMMENDED SOLUTIONS:\n";
    foreach ($solutions as $i => $solution) {
        echo "  " . ($i + 1) . ". {$solution}\n";
    }

    echo "\n=== DIAGNOSIS COMPLETE ===\n";
    echo "Next steps: Apply identified solutions to resolve admin panel access issues.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>