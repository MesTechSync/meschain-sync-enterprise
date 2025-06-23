<?php
/**
 * Fix Extension Registration Script
 * Ensures MesChain Trendyol extension is properly registered in oc_extension table
 */

// Database connection
$host = 'localhost';
$username = 'root';
$password = '1234';
$database = 'opencart_new';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== FIXING EXTENSION REGISTRATION ===\n";
    echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Check if extension already exists
    $stmt = $pdo->prepare("SELECT * FROM oc_extension WHERE code = 'meschain_trendyol' AND type = 'module'");
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        echo "✅ Extension already registered with ID: " . $existing['extension_id'] . "\n";
    } else {
        echo "⚠️  Extension not found in oc_extension table. Registering...\n";
        
        // Register the extension
        $stmt = $pdo->prepare("INSERT INTO oc_extension (extension, type, code, status) VALUES (?, ?, ?, ?)");
        $stmt->execute(['MesChain Trendyol Importer', 'module', 'meschain_trendyol', 1]);
        
        $extension_id = $pdo->lastInsertId();
        echo "✅ Extension registered with ID: $extension_id\n";
    }
    
    // Verify module registration
    $stmt = $pdo->prepare("SELECT * FROM oc_module WHERE code = 'meschain_trendyol'");
    $stmt->execute();
    $module = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($module) {
        echo "✅ Module found with ID: " . $module['module_id'] . "\n";
        echo "   - Name: " . $module['name'] . "\n";
        echo "   - Status: " . ($module['status'] ? 'ENABLED' : 'DISABLED') . "\n";
    } else {
        echo "❌ Module not found in oc_module table\n";
    }
    
    // Check permissions
    $stmt = $pdo->prepare("SELECT permission FROM oc_user_group WHERE user_group_id = 1");
    $stmt->execute();
    $user_group = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user_group) {
        $permissions = json_decode($user_group['permission'], true);
        $has_access = false;
        $has_modify = false;
        
        if (isset($permissions['access']) && is_array($permissions['access'])) {
            $has_access = in_array('extension/module/meschain_trendyol', $permissions['access']);
        }
        
        if (isset($permissions['modify']) && is_array($permissions['modify'])) {
            $has_modify = in_array('extension/module/meschain_trendyol', $permissions['modify']);
        }
        
        echo "✅ Permissions check:\n";
        echo "   - Access: " . ($has_access ? 'GRANTED' : 'MISSING') . "\n";
        echo "   - Modify: " . ($has_modify ? 'GRANTED' : 'MISSING') . "\n";
        
        if (!$has_access || !$has_modify) {
            echo "⚠️  Adding missing permissions...\n";
            
            if (!$has_access) {
                $permissions['access'][] = 'extension/module/meschain_trendyol';
            }
            if (!$has_modify) {
                $permissions['modify'][] = 'extension/module/meschain_trendyol';
            }
            
            $stmt = $pdo->prepare("UPDATE oc_user_group SET permission = ? WHERE user_group_id = 1");
            $stmt->execute([json_encode($permissions)]);
            
            echo "✅ Permissions updated\n";
        }
    }
    
    // Final verification
    echo "\n=== FINAL VERIFICATION ===\n";
    
    $stmt = $pdo->prepare("SELECT e.extension_id, e.extension, e.type, e.code, e.status as ext_status,
                                  m.module_id, m.name, m.status as mod_status
                           FROM oc_extension e
                           LEFT JOIN oc_module m ON e.code = m.code
                           WHERE e.code = 'meschain_trendyol'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "✅ INTEGRATION STATUS: SUCCESS\n";
        echo "Extension Details:\n";
        echo "  - Extension ID: " . $result['extension_id'] . "\n";
        echo "  - Extension Name: " . $result['extension'] . "\n";
        echo "  - Extension Status: " . ($result['ext_status'] ? 'ENABLED' : 'DISABLED') . "\n";
        echo "  - Module ID: " . ($result['module_id'] ?: 'N/A') . "\n";
        echo "  - Module Name: " . ($result['name'] ?: 'N/A') . "\n";
        echo "  - Module Status: " . (isset($result['mod_status']) && $result['mod_status'] ? 'ENABLED' : 'DISABLED') . "\n";
        
        echo "\n✅ MesChain Trendyol extension is now fully registered and ready for use!\n";
        echo "📋 Access: Admin Panel → Extensions → Modules → MesChain Trendyol Importer\n";
    } else {
        echo "❌ INTEGRATION STATUS: FAILED\n";
        echo "Extension registration incomplete\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
?>