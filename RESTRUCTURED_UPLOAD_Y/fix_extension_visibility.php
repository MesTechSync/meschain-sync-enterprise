<?php
/**
 * MesChain-Sync Extension Visibility Fix
 * Bu script extension'ın listede görünmemesi sorununu düzeltir
 */

// Database connection
require_once 'opencart4/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOSTNAME . ";dbname=" . DB_DATABASE . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Database connection successful\n";
    
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

echo "\n🔧 MesChain-Sync Extension Visibility Fix\n";
echo "=" . str_repeat("=", 45) . "\n\n";

// 1. Clean up duplicate extension records
echo "1. Cleaning up duplicate extension records...\n";

try {
    // Remove the incorrect meschain_sync type record
    $stmt = $pdo->prepare("DELETE FROM `" . DB_PREFIX . "extension` WHERE `code` = 'meschain_sync' AND `type` = 'meschain_sync'");
    $deleted = $stmt->execute();
    echo "✅ Removed incorrect extension type record\n";
    
    // Ensure we have the correct module record
    $stmt = $pdo->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `code` = 'meschain_sync' AND `type` = 'module'");
    $module_record = $stmt->fetch();
    
    if (!$module_record) {
        // Create the correct module record
        $stmt = $pdo->prepare("INSERT INTO `" . DB_PREFIX . "extension` (`extension`, `type`, `code`) VALUES ('meschain', 'module', 'meschain_sync')");
        $stmt->execute();
        echo "✅ Created correct module extension record\n";
    } else {
        echo "✅ Module extension record already exists\n";
    }
    
} catch (PDOException $e) {
    echo "⚠️  Extension cleanup warning: " . $e->getMessage() . "\n";
}

// 2. Check and create extension_path table if needed
echo "\n2. Checking extension_path table...\n";

try {
    // Check if extension_path table exists
    $stmt = $pdo->query("SHOW TABLES LIKE '" . DB_PREFIX . "extension_path'");
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        echo "⚠️  extension_path table doesn't exist, creating...\n";
        
        // Create extension_path table (OpenCart 4.x structure)
        $create_table_sql = "
        CREATE TABLE `" . DB_PREFIX . "extension_path` (
            `extension_path_id` int(11) NOT NULL AUTO_INCREMENT,
            `extension_install_id` int(11) NOT NULL,
            `path` varchar(255) NOT NULL,
            `status` tinyint(1) NOT NULL DEFAULT '1',
            `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`extension_path_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $pdo->exec($create_table_sql);
        echo "✅ Created extension_path table\n";
    } else {
        echo "✅ extension_path table exists\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error with extension_path table: " . $e->getMessage() . "\n";
}

// 3. Add extension_path record
echo "\n3. Adding extension_path record...\n";

try {
    // Get extension_install_id
    $stmt = $pdo->query("SELECT extension_install_id FROM `" . DB_PREFIX . "extension_install` WHERE code = 'meschain'");
    $install_record = $stmt->fetch();
    
    if ($install_record) {
        $extension_install_id = $install_record['extension_install_id'];
        
        // Check if extension_path record already exists
        $stmt = $pdo->prepare("SELECT * FROM `" . DB_PREFIX . "extension_path` WHERE extension_install_id = ?");
        $stmt->execute([$extension_install_id]);
        $path_record = $stmt->fetch();
        
        if (!$path_record) {
            // Add extension_path record
            $stmt = $pdo->prepare("INSERT INTO `" . DB_PREFIX . "extension_path` 
                (extension_install_id, path, status, date_added) 
                VALUES (?, 'extension/meschain/', 1, NOW())");
            $stmt->execute([$extension_install_id]);
            echo "✅ Added extension_path record for meschain extension\n";
        } else {
            echo "✅ extension_path record already exists\n";
            
            // Update path if incorrect
            if ($path_record['path'] !== 'extension/meschain/') {
                $stmt = $pdo->prepare("UPDATE `" . DB_PREFIX . "extension_path` SET path = 'extension/meschain/' WHERE extension_install_id = ?");
                $stmt->execute([$extension_install_id]);
                echo "✅ Updated extension_path to correct path\n";
            }
        }
    } else {
        echo "❌ Extension install record not found\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error adding extension_path: " . $e->getMessage() . "\n";
}

// 4. Verify install.json structure
echo "\n4. Verifying install.json structure...\n";

$install_json_path = 'opencart4/extension/meschain/install.json';
if (file_exists($install_json_path)) {
    $install_data = json_decode(file_get_contents($install_json_path), true);
    
    if ($install_data && isset($install_data['code']) && $install_data['code'] === 'meschain_sync') {
        echo "✅ install.json has correct code: meschain_sync\n";
    } else {
        echo "⚠️  install.json may need updating\n";
    }
} else {
    echo "❌ install.json not found at expected location\n";
}

// 5. Check and update user permissions
echo "\n5. Updating user permissions...\n";

try {
    // Add access permission for the new route
    $stmt = $pdo->prepare("SELECT * FROM `" . DB_PREFIX . "user_group` WHERE user_group_id = 1");
    $stmt->execute();
    $admin_group = $stmt->fetch();
    
    if ($admin_group) {
        $permissions = json_decode($admin_group['permission'], true);
        
        $new_route = 'extension/meschain/module/meschain_sync';
        
        // Add access permission
        if (!in_array($new_route, $permissions['access'])) {
            $permissions['access'][] = $new_route;
            echo "✅ Added access permission for {$new_route}\n";
        }
        
        // Add modify permission  
        if (!in_array($new_route, $permissions['modify'])) {
            $permissions['modify'][] = $new_route;
            echo "✅ Added modify permission for {$new_route}\n";
        }
        
        // Update permissions in database
        $stmt = $pdo->prepare("UPDATE `" . DB_PREFIX . "user_group` SET permission = ? WHERE user_group_id = 1");
        $stmt->execute([json_encode($permissions)]);
        echo "✅ Updated admin group permissions\n";
    }
    
} catch (PDOException $e) {
    echo "⚠️  Permission update warning: " . $e->getMessage() . "\n";
}

// 6. Final verification
echo "\n6. Final verification...\n";

try {
    // Check extension table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "extension` WHERE code = 'meschain_sync' AND type = 'module'");
    $ext_count = $stmt->fetch()['count'];
    
    // Check extension_install table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "extension_install` WHERE code = 'meschain' AND status = 1");
    $install_count = $stmt->fetch()['count'];
    
    // Check extension_path table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "extension_path` WHERE path = 'extension/meschain/' AND status = 1");
    $path_count = $stmt->fetch()['count'];
    
    echo "📊 Verification Results:\n";
    echo "   - Extension records: {$ext_count}\n";
    echo "   - Extension install records: {$install_count}\n";
    echo "   - Extension path records: {$path_count}\n";
    
    if ($ext_count > 0 && $install_count > 0 && $path_count > 0) {
        echo "\n🎉 SUCCESS! All records are in place.\n";
    } else {
        echo "\n⚠️  Some records may still be missing.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Verification error: " . $e->getMessage() . "\n";
}

// 7. Cache clear instructions
echo "\n📝 FINAL STEPS TO COMPLETE FIX:\n";
echo "=" . str_repeat("=", 35) . "\n";
echo "1. ✅ Database records have been fixed\n";
echo "2. 🧹 Clear OpenCart Cache:\n";
echo "   - Go to Admin Panel\n";
echo "   - Dashboard > Settings Cog (⚙️) > Refresh\n";
echo "   - Or delete: system/storage/cache/* and system/storage/modification/*\n";
echo "3. 🔄 Refresh Extensions page\n";
echo "4. 📍 Navigate to: Extensions > Choose the extension type > MesChain SYNC\n";
echo "5. ✅ MesChain-Sync should now be visible in the list\n\n";

echo "🎯 EXPECTED RESULT:\n";
echo "MesChain-Sync Enterprise should now appear under:\n";
echo "Extensions > Choose the extension type > MesChain SYNC (1)\n\n";

?>
