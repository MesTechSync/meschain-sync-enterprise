<?php
/**
 * Enable Trendyol Extension and Fix Permissions
 * Fixes the disabled status and permission issues
 */

// Database connection
$host = 'localhost';
$username = 'root';
$password = '1234';
$database = 'opencart_new';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== ENABLING TRENDYOL EXTENSION ===\n";
    echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
    
    // 1. Check current extension structure
    echo "1. ANALYZING EXTENSION TABLE STRUCTURE:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("DESCRIBE oc_extension");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $has_status = false;
    foreach ($columns as $column) {
        echo "Column: " . $column['Field'] . " (Type: " . $column['Type'] . ")\n";
        if ($column['Field'] === 'status') {
            $has_status = true;
        }
    }
    
    // 2. Add status column if missing
    if (!$has_status) {
        echo "\n2. ADDING STATUS COLUMN TO EXTENSION TABLE:\n";
        echo "----------------------------------------\n";
        
        $pdo->exec("ALTER TABLE oc_extension ADD COLUMN status TINYINT(1) DEFAULT 1");
        echo "✅ Status column added to oc_extension\n";
    } else {
        echo "\n2. STATUS COLUMN EXISTS\n";
        echo "----------------------------------------\n";
        echo "✅ Status column already exists\n";
    }
    
    // 3. Check module table structure
    echo "\n3. ANALYZING MODULE TABLE STRUCTURE:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("DESCRIBE oc_module");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $module_has_status = false;
    foreach ($columns as $column) {
        echo "Column: " . $column['Field'] . " (Type: " . $column['Type'] . ")\n";
        if ($column['Field'] === 'status') {
            $module_has_status = true;
        }
    }
    
    // 4. Add status column to module if missing
    if (!$module_has_status) {
        echo "\n4. ADDING STATUS COLUMN TO MODULE TABLE:\n";
        echo "----------------------------------------\n";
        
        $pdo->exec("ALTER TABLE oc_module ADD COLUMN status TINYINT(1) DEFAULT 1");
        echo "✅ Status column added to oc_module\n";
    } else {
        echo "\n4. MODULE STATUS COLUMN EXISTS\n";
        echo "----------------------------------------\n";
        echo "✅ Status column already exists in oc_module\n";
    }
    
    // 5. Enable the extension
    echo "\n5. ENABLING TRENDYOL EXTENSION:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("UPDATE oc_extension SET status = 1 WHERE code = 'trendyol_importer' AND type = 'module'");
    $result = $stmt->execute();
    $affected = $stmt->rowCount();
    
    if ($affected > 0) {
        echo "✅ Extension enabled successfully ($affected rows affected)\n";
    } else {
        echo "⚠️  No extension rows were updated (may already be enabled)\n";
    }
    
    // 6. Enable the module
    echo "\n6. ENABLING TRENDYOL MODULE:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("UPDATE oc_module SET status = 1 WHERE code = 'trendyol_importer'");
    $result = $stmt->execute();
    $affected = $stmt->rowCount();
    
    if ($affected > 0) {
        echo "✅ Module enabled successfully ($affected rows affected)\n";
    } else {
        echo "⚠️  No module rows were updated (may already be enabled)\n";
    }
    
    // 7. Find correct permissions table
    echo "\n7. FINDING PERMISSIONS TABLE:\n";
    echo "----------------------------------------\n";
    
    $tables = $pdo->query("SHOW TABLES LIKE '%permission%'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "Found permissions table: $table\n";
    }
    
    // Check for user_group table
    $user_tables = $pdo->query("SHOW TABLES LIKE '%user_group%'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($user_tables as $table) {
        echo "Found user_group table: $table\n";
    }
    
    // 8. Add permissions (try different table structures)
    echo "\n8. SETTING UP PERMISSIONS:\n";
    echo "----------------------------------------\n";
    
    $permission_tables = [
        'oc_user_group_permission',
        'oc_permission',
        'oc_user_permission'
    ];
    
    $permission_set = false;
    
    foreach ($permission_tables as $table) {
        try {
            // Check if table exists
            $stmt = $pdo->prepare("SHOW TABLES LIKE '$table'");
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                echo "Using permissions table: $table\n";
                
                // Try to add permissions
                $permissions = [
                    'extension/meschain/trendyol_importer',
                    'extension/meschain/trendyol_importer/dashboard',
                    'extension/meschain/trendyol_importer/wizard',
                    'extension/meschain/trendyol_importer/import'
                ];
                
                foreach ($permissions as $permission) {
                    try {
                        // Insert both access and modify permissions for user_group_id = 1 (admin)
                        $stmt = $pdo->prepare("INSERT IGNORE INTO $table (user_group_id, permission, type) VALUES (1, ?, 'access')");
                        $stmt->execute([$permission]);
                        
                        $stmt = $pdo->prepare("INSERT IGNORE INTO $table (user_group_id, permission, type) VALUES (1, ?, 'modify')");
                        $stmt->execute([$permission]);
                        
                        echo "✅ Permission added: $permission\n";
                    } catch (Exception $e) {
                        // Try different structure
                        try {
                            $stmt = $pdo->prepare("INSERT IGNORE INTO $table (user_group_id, permission) VALUES (1, ?)");
                            $stmt->execute([$permission]);
                            echo "✅ Permission added (simple): $permission\n";
                        } catch (Exception $e2) {
                            echo "⚠️  Could not add permission: $permission\n";
                        }
                    }
                }
                
                $permission_set = true;
                break;
            }
        } catch (Exception $e) {
            continue;
        }
    }
    
    if (!$permission_set) {
        echo "⚠️  Could not find suitable permissions table\n";
    }
    
    // 9. Verify final status
    echo "\n9. FINAL VERIFICATION:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT * FROM oc_extension WHERE code = 'trendyol_importer' AND type = 'module'");
    $stmt->execute();
    $extension = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($extension) {
        echo "Extension Status: " . ($extension['status'] ? 'ENABLED ✅' : 'DISABLED ❌') . "\n";
    }
    
    $stmt = $pdo->prepare("SELECT * FROM oc_module WHERE code = 'trendyol_importer'");
    $stmt->execute();
    $module = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($module) {
        echo "Module Status: " . ($module['status'] ? 'ENABLED ✅' : 'DISABLED ❌') . "\n";
    }
    
    echo "\n=== EXTENSION ENABLING COMPLETE ===\n";
    echo "Extension should now be visible in admin panel Extensions > Modules\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>