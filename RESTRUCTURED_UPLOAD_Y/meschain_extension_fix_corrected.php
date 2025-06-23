<?php
/**
 * MesChain-Sync Extension Registration Fix - Corrected Version
 * Fixes the extension registration with proper field values
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load OpenCart configuration
require_once 'opencart_new/config.php';

echo "<h2>🔧 MesChain-Sync Extension Registration Fix (Corrected)</h2>\n";
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
        echo "<div>- Extension: {$ext['extension']}, Type: {$ext['type']}, Code: {$ext['code']}</div>\n";
    }
    
    echo "<h3>🔧 Fixing Extension Registration</h3>\n";
    
    // Step 1: Check if trendyol_importer already exists
    $existing_check = $pdo->query("SELECT * FROM " . DB_PREFIX . "extension WHERE code = 'trendyol_importer'");
    if ($existing_check->rowCount() > 0) {
        echo "<div style='color: orange;'>⚠️ Extension 'trendyol_importer' already exists</div>\n";
    } else {
        // Step 2: Add correct registration with all required fields
        $insert_query = $pdo->prepare("INSERT INTO " . DB_PREFIX . "extension (extension, type, code) VALUES (?, ?, ?)");
        $insert_result = $insert_query->execute(['trendyol_importer', 'module', 'trendyol_importer']);
        
        if ($insert_result) {
            echo "<div style='color: green;'>✅ Added correct 'trendyol_importer' registration</div>\n";
        } else {
            echo "<div style='color: red;'>❌ Failed to add extension registration</div>\n";
        }
    }
    
    // Step 3: Remove any remaining incorrect registrations
    $delete_query = $pdo->prepare("DELETE FROM " . DB_PREFIX . "extension WHERE code = 'meschain_sync'");
    $delete_result = $delete_query->execute();
    
    if ($delete_result) {
        echo "<div style='color: green;'>✅ Cleaned up any remaining incorrect registrations</div>\n";
    }
    
    // Step 4: Verify the fix
    echo "<h3>✅ Verification</h3>\n";
    $verify_query = $pdo->query("SELECT * FROM " . DB_PREFIX . "extension WHERE code = 'trendyol_importer'");
    $verified_extension = $verify_query->fetch(PDO::FETCH_ASSOC);
    
    if ($verified_extension) {
        echo "<div style='color: green; font-weight: bold;'>✅ Extension properly registered:</div>\n";
        echo "<div>- Extension: {$verified_extension['extension']}</div>\n";
        echo "<div>- Type: {$verified_extension['type']}</div>\n";
        echo "<div>- Code: {$verified_extension['code']}</div>\n";
        echo "<div>- Extension ID: {$verified_extension['extension_id']}</div>\n";
    } else {
        echo "<div style='color: red;'>❌ Extension registration failed</div>\n";
    }
    
    // Step 5: Final verification - show all extension entries
    echo "<h3>📋 All Current Extensions</h3>\n";
    $all_extensions_query = $pdo->query("SELECT * FROM " . DB_PREFIX . "extension WHERE code LIKE '%meschain%' OR code LIKE '%trendyol%' ORDER BY extension_id");
    $all_extensions = $all_extensions_query->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($all_extensions as $ext) {
        $highlight = ($ext['code'] === 'trendyol_importer') ? ' style="color: green; font-weight: bold;"' : '';
        echo "<div$highlight>- ID: {$ext['extension_id']}, Extension: {$ext['extension']}, Type: {$ext['type']}, Code: {$ext['code']}</div>\n";
    }
    
    echo "<h3>🎯 Final Status</h3>\n";
    if ($verified_extension) {
        echo "<div style='color: green; background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>\n";
        echo "<strong>✅ Extension Registration Fixed!</strong><br>\n";
        echo "The MesChain-Sync Trendyol Integration should now appear in:<br>\n";
        echo "Admin Panel → Extensions → Modules → Trendyol Importer<br>\n";
        echo "You can now install and configure the extension from the admin panel.\n";
        echo "</div>\n";
    } else {
        echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>\n";
        echo "<strong>❌ Extension Registration Failed!</strong><br>\n";
        echo "The extension is not properly registered. Please check the logs for details.\n";
        echo "</div>\n";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "❌ Fix failed: " . $e->getMessage();
    echo "</div>\n";
}

echo "</div>\n";
?>