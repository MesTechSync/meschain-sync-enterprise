<?php
/**
 * MesChain-Sync Extension Registration Fix
 * Corrects the extension registration to make it appear in OpenCart admin
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load OpenCart configuration
require_once 'opencart_new/config.php';

echo "<h2>🔧 MesChain-Sync Extension Registration Fix</h2>\n";
echo "<div style='font-family: monospace; background: #f8f9fa; padding: 20px; border-radius: 5px;'>\n";

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host=" . DB_HOSTNAME . ";port=" . DB_PORT . ";dbname=" . DB_DATABASE . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<h3>📊 Current Extension Status</h3>\n";
    
    // Check current registration
    $current_query = $pdo->query("SELECT * FROM " . DB_PREFIX . "extension WHERE code LIKE '%meschain%' OR code LIKE '%trendyol%'");
    $current_extensions = $current_query->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Current Extensions:</h4>\n";
    foreach ($current_extensions as $ext) {
        echo "<div>- Type: {$ext['type']}, Code: {$ext['code']}</div>\n";
    }
    
    echo "<h3>🔧 Fixing Extension Registration</h3>\n";
    
    // Step 1: Remove incorrect registration
    $delete_query = $pdo->prepare("DELETE FROM " . DB_PREFIX . "extension WHERE code = 'meschain_sync'");
    $delete_result = $delete_query->execute();
    
    if ($delete_result) {
        echo "<div style='color: green;'>✅ Removed incorrect 'meschain_sync' registration</div>\n";
    }
    
    // Step 2: Add correct registration
    $insert_query = $pdo->prepare("INSERT INTO " . DB_PREFIX . "extension (type, code) VALUES ('module', 'trendyol_importer')");
    $insert_result = $insert_query->execute();
    
    if ($insert_result) {
        echo "<div style='color: green;'>✅ Added correct 'trendyol_importer' registration</div>\n";
    }
    
    // Step 3: Verify the fix
    echo "<h3>✅ Verification</h3>\n";
    $verify_query = $pdo->query("SELECT * FROM " . DB_PREFIX . "extension WHERE code = 'trendyol_importer'");
    $verified_extension = $verify_query->fetch(PDO::FETCH_ASSOC);
    
    if ($verified_extension) {
        echo "<div style='color: green; font-weight: bold;'>✅ Extension properly registered:</div>\n";
        echo "<div>- Type: {$verified_extension['type']}</div>\n";
        echo "<div>- Code: {$verified_extension['code']}</div>\n";
        echo "<div>- Extension ID: {$verified_extension['extension_id']}</div>\n";
    } else {
        echo "<div style='color: red;'>❌ Extension registration failed</div>\n";
    }
    
    // Step 4: Check if we need to create an install entry
    echo "<h3>📦 Extension Install Entry</h3>\n";
    
    $install_check_query = $pdo->query("SELECT * FROM " . DB_PREFIX . "extension_install WHERE code = 'trendyol_importer'");
    $install_entry = $install_check_query->fetch(PDO::FETCH_ASSOC);
    
    if (!$install_entry) {
        echo "<div>Creating extension install entry...</div>\n";
        
        $install_insert = $pdo->prepare("INSERT INTO " . DB_PREFIX . "extension_install (extension_id, extension_download_id, name, code, version, author, link, status, date_added) VALUES (?, 0, 'MesChain-Sync Trendyol Integration', 'trendyol_importer', '1.0.0', 'MesChain Development Team', 'https://meschain.com', 1, NOW())");
        $install_result = $install_insert->execute([$verified_extension['extension_id']]);
        
        if ($install_result) {
            echo "<div style='color: green;'>✅ Extension install entry created</div>\n";
        } else {
            echo "<div style='color: red;'>❌ Failed to create install entry</div>\n";
        }
    } else {
        echo "<div style='color: green;'>✅ Extension install entry already exists</div>\n";
    }
    
    echo "<h3>🎯 Final Status</h3>\n";
    echo "<div style='color: green; background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>\n";
    echo "<strong>✅ Extension Registration Fixed!</strong><br>\n";
    echo "The MesChain-Sync Trendyol Integration should now appear in:<br>\n";
    echo "Admin Panel → Extensions → Modules → Trendyol Importer<br>\n";
    echo "You can now install and configure the extension from the admin panel.\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "❌ Fix failed: " . $e->getMessage();
    echo "</div>\n";
}

echo "</div>\n";
?>