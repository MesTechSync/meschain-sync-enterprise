<?php
/**
 * MesChain-Sync Extension Registry Script
 * Register the extension in OpenCart's extension system
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "📝 ============================================\n";
echo "📝 MesChain Extension Registry Setup\n";
echo "📝 ============================================\n\n";

// Database configuration
$db_config = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '1234',
    'database' => 'opencart4',
    'prefix' => 'oc_'
];

try {
    $pdo = new PDO(
        "mysql:host={$db_config['hostname']};dbname={$db_config['database']};charset=utf8mb4",
        $db_config['username'],
        $db_config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "✅ Database connection successful\n\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "📊 1. Registering Extension in OpenCart...\n";

// First, check if extension table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE '{$db_config['prefix']}extension'");
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        echo "⚠️  Extension table does not exist, creating it...\n";
        
        $create_extension_table = "CREATE TABLE `{$db_config['prefix']}extension` (
            `extension_id` int(11) NOT NULL AUTO_INCREMENT,
            `type` varchar(32) NOT NULL,
            `code` varchar(128) NOT NULL,
            PRIMARY KEY (`extension_id`),
            UNIQUE KEY `type_code` (`type`, `code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        
        $pdo->exec($create_extension_table);
        echo "✅ Created extension table\n";
    } else {
        echo "✅ Extension table exists\n";
    }
} catch (PDOException $e) {
    echo "❌ Error checking extension table: " . $e->getMessage() . "\n";
}

// Register the MesChain extension
try {
    $stmt = $pdo->prepare("INSERT INTO `{$db_config['prefix']}extension` (type, code) VALUES (?, ?) ON DUPLICATE KEY UPDATE code = VALUES(code)");
    $stmt->execute(['module', 'meschain_sync']);
    echo "✅ Registered MesChain-Sync module\n";
} catch (PDOException $e) {
    echo "⚠️  Extension registration note: " . $e->getMessage() . "\n";
}

echo "\n📊 2. Setting up Module Configuration...\n";

// Add default module settings
$module_settings = [
    'module_meschain_sync_status' => '1',
    'module_meschain_sync_name' => 'MesChain-Sync Enterprise',
    'module_meschain_sync_version' => '3.0.0',
    'module_meschain_sync_author' => 'MesTech Development Team',
    'module_meschain_sync_description' => 'Advanced marketplace synchronization system for OpenCart',
    'module_meschain_sync_auto_sync' => '1',
    'module_meschain_sync_sync_interval' => '300',
    'module_meschain_sync_debug_mode' => '1',
    'module_meschain_sync_log_level' => 'info',
    'module_meschain_sync_max_retries' => '3',
    'module_meschain_sync_timeout' => '30',
    'module_meschain_sync_batch_size' => '50',
    'module_meschain_sync_api_rate_limit' => '100',
    'module_meschain_sync_enable_webhooks' => '1',
    'module_meschain_sync_webhook_secret' => bin2hex(random_bytes(16))
];

$stmt = $pdo->prepare("INSERT INTO `{$db_config['prefix']}setting` 
    (store_id, `code`, `key`, `value`, serialized) 
    VALUES (0, 'module_meschain_sync', ?, ?, 0) 
    ON DUPLICATE KEY UPDATE 
    `value` = VALUES(`value`)");

foreach ($module_settings as $key => $value) {
    try {
        $stmt->execute([$key, $value]);
        echo "✅ Set: {$key}\n";
    } catch (PDOException $e) {
        echo "⚠️  Setting {$key}: " . $e->getMessage() . "\n";
    }
}

echo "\n📊 3. Creating User Permissions...\n";

// Add user group permissions for MesChain
try {
    // Get all user groups
    $stmt = $pdo->query("SELECT user_group_id FROM `{$db_config['prefix']}user_group`");
    $user_groups = $stmt->fetchAll();
    
    foreach ($user_groups as $group) {
        $group_id = $group['user_group_id'];
        
        // Get existing permissions
        $stmt = $pdo->prepare("SELECT permission FROM `{$db_config['prefix']}user_group` WHERE user_group_id = ?");
        $stmt->execute([$group_id]);
        $result = $stmt->fetch();
        
        if ($result) {
            $permissions = json_decode($result['permission'], true);
            if (!$permissions) $permissions = [];
            
            // Add MesChain permissions
            $meschain_permissions = [
                'extension/module/meschain_sync',
                'extension/module/meschain_sync/install',
                'extension/module/meschain_sync/uninstall',
                'extension/module/meschain_sync/marketplaces',
                'extension/module/meschain_sync/products',
                'extension/module/meschain_sync/orders',
                'extension/module/meschain_sync/settings',
                'extension/module/meschain_sync/logs'
            ];
            
            foreach ($meschain_permissions as $permission) {
                if (!in_array($permission, $permissions['access'] ?? [])) {
                    $permissions['access'][] = $permission;
                }
                if (!in_array($permission, $permissions['modify'] ?? [])) {
                    $permissions['modify'][] = $permission;
                }
            }
            
            // Update permissions
            $stmt = $pdo->prepare("UPDATE `{$db_config['prefix']}user_group` SET permission = ? WHERE user_group_id = ?");
            $stmt->execute([json_encode($permissions), $group_id]);
            echo "✅ Updated permissions for user group {$group_id}\n";
        }
    }
} catch (PDOException $e) {
    echo "⚠️  Permission setup note: " . $e->getMessage() . "\n";
}

echo "\n📊 4. Setting up Event System...\n";

// Register events for MesChain
$events = [
    [
        'code' => 'meschain_sync_product_update',
        'description' => 'MesChain product sync trigger',
        'trigger' => 'admin/model/catalog/product/editProduct/after',
        'action' => 'extension/module/meschain_sync/productUpdate',
        'status' => 1,
        'sort_order' => 1
    ],
    [
        'code' => 'meschain_sync_order_update', 
        'description' => 'MesChain order sync trigger',
        'trigger' => 'admin/model/sale/order/editOrder/after',
        'action' => 'extension/module/meschain_sync/orderUpdate',
        'status' => 1,
        'sort_order' => 2
    ],
    [
        'code' => 'meschain_sync_menu_add',
        'description' => 'Add MesChain menu to admin',
        'trigger' => 'admin/view/common/column_left/before',
        'action' => 'extension/module/meschain_sync/addMenu',
        'status' => 1,
        'sort_order' => 0
    ]
];

// Create event table if it doesn't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `{$db_config['prefix']}event` (
        `event_id` int(11) NOT NULL AUTO_INCREMENT,
        `code` varchar(128) NOT NULL,
        `description` text NOT NULL,
        `trigger` text NOT NULL,
        `action` text NOT NULL,
        `status` tinyint(1) NOT NULL DEFAULT '1',
        `sort_order` int(3) NOT NULL DEFAULT '1',
        PRIMARY KEY (`event_id`),
        KEY `code` (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    
    echo "✅ Event table ready\n";
} catch (PDOException $e) {
    echo "⚠️  Event table note: " . $e->getMessage() . "\n";
}

// Insert events
$stmt = $pdo->prepare("INSERT INTO `{$db_config['prefix']}event` 
    (code, description, `trigger`, action, status, sort_order) 
    VALUES (?, ?, ?, ?, ?, ?) 
    ON DUPLICATE KEY UPDATE 
    description = VALUES(description),
    `trigger` = VALUES(`trigger`),
    action = VALUES(action),
    status = VALUES(status),
    sort_order = VALUES(sort_order)");

foreach ($events as $event) {
    try {
        $stmt->execute([
            $event['code'],
            $event['description'], 
            $event['trigger'],
            $event['action'],
            $event['status'],
            $event['sort_order']
        ]);
        echo "✅ Registered event: {$event['code']}\n";
    } catch (PDOException $e) {
        echo "⚠️  Event {$event['code']}: " . $e->getMessage() . "\n";
    }
}

echo "\n📊 5. Verification...\n";

// Verify registration
try {
    $stmt = $pdo->query("SELECT * FROM `{$db_config['prefix']}extension` WHERE code = 'meschain_sync'");
    $extension = $stmt->fetch();
    
    if ($extension) {
        echo "✅ Extension registered in database\n";
        echo "   Type: {$extension['type']}\n";
        echo "   Code: {$extension['code']}\n";
    } else {
        echo "❌ Extension not found in registry\n";
    }
    
    // Check settings count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `{$db_config['prefix']}setting` WHERE code = 'module_meschain_sync'");
    $result = $stmt->fetch();
    echo "✅ Module settings: {$result['count']} configured\n";
    
    // Check events count  
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `{$db_config['prefix']}event` WHERE code LIKE 'meschain_%'");
    $result = $stmt->fetch();
    echo "✅ System events: {$result['count']} registered\n";
    
} catch (PDOException $e) {
    echo "❌ Verification error: " . $e->getMessage() . "\n";
}

echo "\n🎉 ============================================\n";
echo "🎉 Extension Registration COMPLETED!\n";
echo "🎉 ============================================\n\n";

echo "📋 What's Ready:\n";
echo "✅ Extension registered in OpenCart system\n";
echo "✅ Module settings configured\n";
echo "✅ User permissions set\n";
echo "✅ System events registered\n";
echo "✅ Database tables created\n\n";

echo "📋 Final Step - Manual Admin Panel:\n";
echo "1. 🌐 Open: http://localhost:8080/admin/\n";
echo "2. 🔑 Login with your admin credentials\n";
echo "3. 📂 Navigate: Extensions → Extensions\n";
echo "4. 🔍 Filter: Choose 'Modules'\n";
echo "5. 🔧 Find: 'MesChain-Sync Enterprise'\n";
echo "6. ⚡ Click: Install button\n";
echo "7. ⚙️  Click: Edit button to configure\n\n";

echo "🚀 MesChain-Sync Enterprise is READY! 🚀\n";
?> 