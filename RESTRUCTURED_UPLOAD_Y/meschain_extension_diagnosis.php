<?php
/**
 * MesChain-Sync Extension Registration Diagnosis
 * Debug script to identify why the extension is not appearing in OpenCart admin
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load OpenCart configuration
require_once 'opencart_new/config.php';

echo "<h2>🔍 MesChain-Sync Extension Registration Diagnosis</h2>\n";
echo "<div style='font-family: monospace; background: #f8f9fa; padding: 20px; border-radius: 5px;'>\n";

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host=" . DB_HOSTNAME . ";port=" . DB_PORT . ";dbname=" . DB_DATABASE . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<h3>📊 Database Analysis</h3>\n";
    
    // 1. Check if oc_extension table exists
    echo "<h4>1. Extension Table Check</h4>\n";
    $tables_query = $pdo->query("SHOW TABLES LIKE '" . DB_PREFIX . "extension'");
    $extension_table_exists = $tables_query->rowCount() > 0;
    
    if ($extension_table_exists) {
        echo "✅ oc_extension table exists\n";
        
        // Check current extensions
        $extensions_query = $pdo->query("SELECT * FROM " . DB_PREFIX . "extension ORDER BY type, code");
        $extensions = $extensions_query->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h5>Current Extensions (" . count($extensions) . " total):</h5>\n";
        foreach ($extensions as $ext) {
            $highlight = (strpos($ext['code'], 'meschain') !== false || strpos($ext['code'], 'trendyol') !== false) ? ' style="color: red; font-weight: bold;"' : '';
            echo "<div$highlight>- {$ext['type']}: {$ext['code']}</div>\n";
        }
        
        // Check for MesChain extensions specifically
        $meschain_query = $pdo->query("SELECT * FROM " . DB_PREFIX . "extension WHERE code LIKE '%meschain%' OR code LIKE '%trendyol%'");
        $meschain_extensions = $meschain_query->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($meschain_extensions)) {
            echo "<div style='color: red; font-weight: bold;'>❌ PROBLEM FOUND: No MesChain/Trendyol extensions registered!</div>\n";
        } else {
            echo "<div style='color: green;'>✅ MesChain extensions found: " . count($meschain_extensions) . "</div>\n";
        }
        
    } else {
        echo "❌ oc_extension table does not exist!\n";
    }
    
    // 2. Check if menu table exists
    echo "<h4>2. Menu Table Check</h4>\n";
    $menu_tables_query = $pdo->query("SHOW TABLES LIKE '" . DB_PREFIX . "menu'");
    $menu_table_exists = $menu_tables_query->rowCount() > 0;
    
    if ($menu_table_exists) {
        echo "✅ oc_menu table exists\n";
        
        // Check for MesChain menu items
        $menu_query = $pdo->query("SELECT * FROM " . DB_PREFIX . "menu WHERE name LIKE '%MesChain%' OR name LIKE '%Trendyol%'");
        $menu_items = $menu_query->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($menu_items)) {
            echo "<div style='color: orange;'>⚠️ No MesChain menu items found in menu table</div>\n";
        } else {
            echo "<div style='color: green;'>✅ MesChain menu items found: " . count($menu_items) . "</div>\n";
            foreach ($menu_items as $item) {
                echo "<div>- {$item['name']} (ID: {$item['menu_id']}, Link: {$item['link']})</div>\n";
            }
        }
        
    } else {
        echo "❌ oc_menu table does not exist!\n";
    }
    
    // 3. Check OpenCart version and structure
    echo "<h4>3. OpenCart Structure Analysis</h4>\n";
    
    // Check what extension-related tables exist
    $extension_tables_query = $pdo->query("SHOW TABLES LIKE '" . DB_PREFIX . "%extension%'");
    $extension_tables = $extension_tables_query->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h5>Extension-related tables:</h5>\n";
    foreach ($extension_tables as $table) {
        echo "- $table\n";
    }
    
    // Check admin-related tables
    $admin_tables_query = $pdo->query("SHOW TABLES LIKE '" . DB_PREFIX . "%admin%'");
    $admin_tables = $admin_tables_query->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h5>Admin-related tables:</h5>\n";
    foreach ($admin_tables as $table) {
        echo "- $table\n";
    }
    
    // 4. Check file structure
    echo "<h4>4. File Structure Check</h4>\n";
    
    $controller_file = 'opencart_new/admin/controller/extension/meschain/trendyol_importer.php';
    $model_file = 'opencart_new/admin/model/extension/meschain/trendyol_importer.php';
    $language_file = 'opencart_new/admin/language/en-gb/extension/meschain/trendyol_importer.php';
    
    echo "Controller file: " . (file_exists($controller_file) ? "✅ EXISTS" : "❌ MISSING") . "\n";
    echo "Model file: " . (file_exists($model_file) ? "✅ EXISTS" : "❌ MISSING") . "\n";
    echo "Language file: " . (file_exists($language_file) ? "✅ EXISTS" : "❌ MISSING") . "\n";
    
    // 5. Diagnosis Summary
    echo "<h3>🎯 Diagnosis Summary</h3>\n";
    
    $issues = [];
    
    if ($extension_table_exists && empty($meschain_extensions)) {
        $issues[] = "Extension not registered in oc_extension table";
    }
    
    if (!$extension_table_exists) {
        $issues[] = "Extension table structure unknown";
    }
    
    if ($menu_table_exists && empty($menu_items)) {
        $issues[] = "Menu items not created properly";
    }
    
    if (!$menu_table_exists) {
        $issues[] = "Menu table structure unknown";
    }
    
    if (!file_exists($controller_file)) {
        $issues[] = "Controller file missing";
    }
    
    echo "<h4>Issues Found:</h4>\n";
    foreach ($issues as $issue) {
        echo "<div style='color: red;'>❌ $issue</div>\n";
    }
    
    echo "<h4>Most Likely Problem:</h4>\n";
    if ($extension_table_exists && empty($meschain_extensions)) {
        echo "<div style='color: red; font-weight: bold; background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7;'>";
        echo "🎯 MAIN ISSUE: The MesChain-Sync extension is not registered in the oc_extension table.<br>";
        echo "This is required for extensions to appear in the OpenCart admin Extensions list.<br>";
        echo "The extension needs to be registered with type='module' and code='trendyol_importer'";
        echo "</div>\n";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "❌ Database connection failed: " . $e->getMessage();
    echo "</div>\n";
}

echo "</div>\n";
?>