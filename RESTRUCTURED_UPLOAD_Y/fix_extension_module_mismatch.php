<?php
/**
 * Fix Extension-Module Registration Mismatch
 * Purpose: Align extension and module registrations for proper admin panel access
 * 
 * MesChain-Sync Enterprise v1.0.0
 * Issue: Extension registered as 'meschain_sync' but module as 'trendyol_importer'
 * Solution: Standardize both to 'trendyol_importer' for consistency
 */

// Database configuration
$host = 'localhost';
$port = 3306;
$username = 'root';
$password = '1234';
$database = 'opencart_new';

echo "=== FIXING EXTENSION-MODULE MISMATCH ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", 
                   $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "✅ Database connection successful\n\n";

    // 1. Check current state
    echo "1. CURRENT STATE ANALYSIS\n";
    echo "=========================\n";
    
    $stmt = $pdo->query("SELECT * FROM oc_extension WHERE extension_id = 56");
    $extension = $stmt->fetch();
    
    if ($extension) {
        echo "Extension (ID: 56): Code='{$extension['code']}', Type='{$extension['type']}'\n";
    }
    
    $stmt = $pdo->query("SELECT * FROM oc_module WHERE module_id = 5");
    $module = $stmt->fetch();
    
    if ($module) {
        echo "Module (ID: 5): Code='{$module['code']}', Name='{$module['name']}'\n";
    }
    echo "\n";

    // 2. Update extension registration to match module
    echo "2. UPDATING EXTENSION REGISTRATION\n";
    echo "==================================\n";
    
    $update_extension = $pdo->prepare("UPDATE oc_extension SET code = ? WHERE extension_id = 56");
    $result = $update_extension->execute(['trendyol_importer']);
    
    if ($result) {
        echo "✅ Extension code updated from 'meschain_sync' to 'trendyol_importer'\n";
    } else {
        echo "❌ Failed to update extension code\n";
    }

    // 3. Verify the update
    echo "\n3. VERIFICATION\n";
    echo "===============\n";
    
    $stmt = $pdo->query("SELECT * FROM oc_extension WHERE extension_id = 56");
    $updated_extension = $stmt->fetch();
    
    $stmt = $pdo->query("SELECT * FROM oc_module WHERE module_id = 5");
    $current_module = $stmt->fetch();
    
    if ($updated_extension && $current_module) {
        echo "Extension code: '{$updated_extension['code']}'\n";
        echo "Module code: '{$current_module['code']}'\n";
        
        if ($updated_extension['code'] === $current_module['code']) {
            echo "✅ SUCCESS: Extension and Module codes now match!\n";
        } else {
            echo "❌ MISMATCH: Codes still don't match\n";
        }
    }

    // 4. Update user permissions if needed
    echo "\n4. UPDATING USER PERMISSIONS\n";
    echo "============================\n";
    
    $stmt = $pdo->query("SELECT * FROM oc_user_group WHERE user_group_id = 1");
    $admin_group = $stmt->fetch();
    
    if ($admin_group) {
        $permissions = json_decode($admin_group['permission'], true);
        $updated = false;
        
        // Add new permission for the correct route
        if (isset($permissions['access'])) {
            if (!in_array('extension/trendyol_importer/module/trendyol_importer', $permissions['access'])) {
                $permissions['access'][] = 'extension/trendyol_importer/module/trendyol_importer';
                $updated = true;
            }
            
            if (!in_array('extension/module/trendyol_importer', $permissions['access'])) {
                $permissions['access'][] = 'extension/module/trendyol_importer';
                $updated = true;
            }
        }
        
        if (isset($permissions['modify'])) {
            if (!in_array('extension/trendyol_importer/module/trendyol_importer', $permissions['modify'])) {
                $permissions['modify'][] = 'extension/trendyol_importer/module/trendyol_importer';
                $updated = true;
            }
            
            if (!in_array('extension/module/trendyol_importer', $permissions['modify'])) {
                $permissions['modify'][] = 'extension/module/trendyol_importer';
                $updated = true;
            }
        }
        
        if ($updated) {
            $update_perms = $pdo->prepare("UPDATE oc_user_group SET permission = ? WHERE user_group_id = 1");
            $result = $update_perms->execute([json_encode($permissions)]);
            
            if ($result) {
                echo "✅ User permissions updated with new routes\n";
            } else {
                echo "❌ Failed to update user permissions\n";
            }
        } else {
            echo "ℹ️  User permissions already include required routes\n";
        }
    }

    // 5. Create/Install missing database tables
    echo "\n5. INSTALLING MISSING DATABASE TABLES\n";
    echo "=====================================\n";
    
    $tables_sql = [
        'trendyol_products' => "CREATE TABLE IF NOT EXISTS `trendyol_products` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `trendyol_id` varchar(255) NOT NULL,
            `opencart_product_id` int(11) DEFAULT NULL,
            `barcode` varchar(255) DEFAULT NULL,
            `title` text NOT NULL,
            `brand` varchar(255) DEFAULT NULL,
            `category_id` int(11) DEFAULT NULL,
            `price` decimal(10,2) DEFAULT NULL,
            `sale_price` decimal(10,2) DEFAULT NULL,
            `stock_quantity` int(11) DEFAULT 0,
            `images` text DEFAULT NULL,
            `description` text DEFAULT NULL,
            `attributes` text DEFAULT NULL,
            `status` tinyint(1) DEFAULT 1,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `trendyol_id` (`trendyol_id`),
            KEY `opencart_product_id` (`opencart_product_id`),
            KEY `category_id` (`category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        'trendyol_categories' => "CREATE TABLE IF NOT EXISTS `trendyol_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `trendyol_category_id` int(11) NOT NULL,
            `opencart_category_id` int(11) DEFAULT NULL,
            `name` varchar(255) NOT NULL,
            `parent_id` int(11) DEFAULT NULL,
            `level` int(11) DEFAULT 0,
            `status` tinyint(1) DEFAULT 1,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `trendyol_category_id` (`trendyol_category_id`),
            KEY `opencart_category_id` (`opencart_category_id`),
            KEY `parent_id` (`parent_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        'trendyol_imports' => "CREATE TABLE IF NOT EXISTS `trendyol_imports` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `session_id` varchar(255) NOT NULL,
            `total_products` int(11) DEFAULT 0,
            `processed_products` int(11) DEFAULT 0,
            `successful_imports` int(11) DEFAULT 0,
            `failed_imports` int(11) DEFAULT 0,
            `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
            `started_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `completed_at` timestamp NULL DEFAULT NULL,
            `error_message` text DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `session_id` (`session_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        'trendyol_logs' => "CREATE TABLE IF NOT EXISTS `trendyol_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `level` enum('info','warning','error','debug') DEFAULT 'info',
            `message` text NOT NULL,
            `context` text DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `level` (`level`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        'trendyol_config' => "CREATE TABLE IF NOT EXISTS `trendyol_config` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `key` varchar(255) NOT NULL,
            `value` text DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `key` (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        'trendyol_statistics' => "CREATE TABLE IF NOT EXISTS `trendyol_statistics` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `metric` varchar(255) NOT NULL,
            `value` int(11) DEFAULT 0,
            `date` date NOT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `metric_date` (`metric`, `date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    
    foreach ($tables_sql as $table_name => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Table '{$table_name}' created/verified\n";
        } catch (Exception $e) {
            echo "❌ Error creating table '{$table_name}': " . $e->getMessage() . "\n";
        }
    }

    echo "\n6. FINAL VERIFICATION\n";
    echo "====================\n";
    
    // Check extension-module alignment
    $stmt = $pdo->query("SELECT e.code as ext_code, m.code as mod_code 
                         FROM oc_extension e, oc_module m 
                         WHERE e.extension_id = 56 AND m.module_id = 5");
    $alignment = $stmt->fetch();
    
    if ($alignment && $alignment['ext_code'] === $alignment['mod_code']) {
        echo "✅ Extension-Module alignment: FIXED\n";
    } else {
        echo "❌ Extension-Module alignment: STILL MISMATCHED\n";
    }
    
    // Check database tables
    $table_count = 0;
    foreach (array_keys($tables_sql) as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
        if ($stmt->rowCount() > 0) {
            $table_count++;
        }
    }
    echo "✅ Database tables: {$table_count}/6 created\n";

    echo "\n=== FIX COMPLETE ===\n";
    echo "The Extension-Module mismatch has been resolved.\n";
    echo "Next step: Test admin panel access to Trendyol integration.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>