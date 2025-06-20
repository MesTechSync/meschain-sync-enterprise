<?php
echo "=== Dashboard Modules Check ===\n\n";

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=opencart4', 'root', '1234');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "1. Checking existing dashboard extensions...\n";

// Check current dashboard extensions
$stmt = $pdo->query("SELECT * FROM oc_extension WHERE type = 'dashboard'");
$current_dashboards = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Current dashboard extensions: " . count($current_dashboards) . "\n";
foreach ($current_dashboards as $dash) {
    echo "- {$dash['code']} ({$dash['extension']})\n";
}

echo "\n2. Checking dashboard settings...\n";

// Check dashboard settings
$stmt = $pdo->query("SELECT * FROM oc_setting WHERE `key` LIKE 'dashboard_%_status'");
$dashboard_settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Dashboard settings found: " . count($dashboard_settings) . "\n";
foreach ($dashboard_settings as $setting) {
    echo "- {$setting['key']}: {$setting['value']}\n";
}

echo "\n3. Installing missing dashboard modules...\n";

// Standard OpenCart 4 dashboard modules
$default_dashboards = [
    ['code' => 'activity', 'extension' => 'opencart'],
    ['code' => 'chart', 'extension' => 'opencart'],
    ['code' => 'customer', 'extension' => 'opencart'],
    ['code' => 'map', 'extension' => 'opencart'],
    ['code' => 'online', 'extension' => 'opencart'],
    ['code' => 'order', 'extension' => 'opencart'],
    ['code' => 'recent', 'extension' => 'opencart'],
    ['code' => 'sale', 'extension' => 'opencart']
];

// Install missing dashboard modules
foreach ($default_dashboards as $dashboard) {
    // Check if exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM oc_extension WHERE code = ? AND type = 'dashboard'");
    $stmt->execute([$dashboard['code']]);
    $exists = $stmt->fetchColumn();
    
    if (!$exists) {
        // Install dashboard module
        $stmt = $pdo->prepare("INSERT INTO oc_extension (extension, type, code) VALUES (?, 'dashboard', ?)");
        $stmt->execute([$dashboard['extension'], $dashboard['code']]);
        echo "✅ Installed dashboard: {$dashboard['code']}\n";
    } else {
        echo "✅ Already exists: {$dashboard['code']}\n";
    }
    
    // Enable the dashboard module
    $stmt = $pdo->prepare("INSERT IGNORE INTO oc_setting (store_id, `code`, `key`, `value`, serialized) VALUES (0, 'dashboard_{$dashboard['code']}', 'dashboard_{$dashboard['code']}_status', '1', 0)");
    $stmt->execute();
    
    // Set width and sort order
    $sort_orders = [
        'activity' => 1, 'chart' => 2, 'customer' => 3, 'map' => 4,
        'online' => 5, 'order' => 6, 'recent' => 7, 'sale' => 8
    ];
    
    $width = 4; // Default width
    if (in_array($dashboard['code'], ['chart', 'map'])) $width = 6;
    if (in_array($dashboard['code'], ['recent'])) $width = 12;
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO oc_setting (store_id, `code`, `key`, `value`, serialized) VALUES (0, 'dashboard_{$dashboard['code']}', 'dashboard_{$dashboard['code']}_width', ?, 0)");
    $stmt->execute([$width]);
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO oc_setting (store_id, `code`, `key`, `value`, serialized) VALUES (0, 'dashboard_{$dashboard['code']}', 'dashboard_{$dashboard['code']}_sort_order', ?, 0)");
    $stmt->execute([$sort_orders[$dashboard['code']] ?? 99]);
}

echo "\n4. Verifying dashboard files exist...\n";

$dashboard_files = [
    'opencart4/admin/controller/extension/opencart/dashboard/activity.php',
    'opencart4/admin/controller/extension/opencart/dashboard/chart.php',
    'opencart4/admin/controller/extension/opencart/dashboard/customer.php',
    'opencart4/admin/controller/extension/opencart/dashboard/map.php',
    'opencart4/admin/controller/extension/opencart/dashboard/online.php',
    'opencart4/admin/controller/extension/opencart/dashboard/order.php',
    'opencart4/admin/controller/extension/opencart/dashboard/recent.php',
    'opencart4/admin/controller/extension/opencart/dashboard/sale.php'
];

$missing_files = [];
foreach ($dashboard_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file\n";
    } else {
        echo "❌ MISSING: $file\n";
        $missing_files[] = $file;
    }
}

echo "\n5. Clear cache...\n";
$cache_files = glob('opencart4/system/storage/cache/*');
foreach ($cache_files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}
echo "✅ Cache cleared\n";

echo "\n=== DASHBOARD REPAIR COMPLETE ===\n";

if (empty($missing_files)) {
    echo "🎉 ALL DASHBOARD MODULES RESTORED!\n";
    echo "📊 Now you should see:\n";
    echo "   - Sales Chart\n";
    echo "   - World Map\n"; 
    echo "   - Recent Activity\n";
    echo "   - Customer Statistics\n";
    echo "   - Order Statistics\n";
    echo "   - Online Users\n";
    echo "   - Sales Reports\n\n";
    echo "🔄 Refresh your admin dashboard to see all widgets!\n";
} else {
    echo "⚠️  Some dashboard files are missing. Core OpenCart installation may be incomplete.\n";
    echo "Missing files: " . count($missing_files) . "\n";
}
?> 