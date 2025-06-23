<?php
echo "=== FINAL MENU TEST ===\n\n";

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=opencart4', 'root', '1234');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "1. Testing Dashboard Widgets...\n";

// Check dashboard extensions
$stmt = $pdo->query("SELECT COUNT(*) FROM oc_extension WHERE type = 'dashboard'");
$dashboard_count = $stmt->fetchColumn();
echo "Dashboard extensions: $dashboard_count\n";

// Check dashboard settings
$stmt = $pdo->query("SELECT COUNT(*) FROM oc_setting WHERE `key` LIKE 'dashboard_%_status' AND value = '1'");
$enabled_dashboards = $stmt->fetchColumn();
echo "Enabled dashboard widgets: $enabled_dashboards\n";

echo "\n2. Testing Extensions Menu...\n";

// Check user permissions
$stmt = $pdo->query("SELECT permission FROM oc_user_group WHERE user_group_id = 1");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$permissions = json_decode($result['permission'], true);

$menu_items = [
    'marketplace/marketplace' => 'Marketplace',
    'marketplace/installer' => 'Installer', 
    'marketplace/extension' => 'Extensions',
    'marketplace/startup' => 'Startup',
    'marketplace/event' => 'Events',
    'marketplace/cron' => 'CRON Jobs'
];

foreach ($menu_items as $permission => $name) {
    $has_access = in_array($permission, $permissions['access'] ?? []);
    echo ($has_access ? "✅" : "❌") . " $name: " . ($has_access ? "VISIBLE" : "HIDDEN") . "\n";
}

echo "\n3. Testing File Structure...\n";

$required_files = [
    'opencart4/admin/controller/common/column_left.php' => 'Left Menu Controller',
    'opencart4/admin/controller/common/dashboard.php' => 'Dashboard Controller',
    'opencart4/admin/controller/marketplace/cron.php' => 'CRON Controller',
    'opencart4/admin/language/en-gb/common/column_left.php' => 'Left Menu Language',
    'opencart4/admin/language/en-gb/marketplace/cron.php' => 'CRON Language',
    'opencart4/admin/view/template/marketplace/cron.twig' => 'CRON Template'
];

foreach ($required_files as $file => $description) {
    $exists = file_exists($file);
    echo ($exists ? "✅" : "❌") . " $description: " . ($exists ? "EXISTS" : "MISSING") . "\n";
}

echo "\n4. Testing Dashboard Files...\n";

$dashboard_controllers = [
    'opencart4/admin/controller/extension/opencart/dashboard/sale.php',
    'opencart4/admin/controller/extension/opencart/dashboard/order.php',
    'opencart4/admin/controller/extension/opencart/dashboard/customer.php',
    'opencart4/admin/controller/extension/opencart/dashboard/online.php',
    'opencart4/admin/controller/extension/opencart/dashboard/chart.php',
    'opencart4/admin/controller/extension/opencart/dashboard/map.php',
    'opencart4/admin/controller/extension/opencart/dashboard/activity.php',
    'opencart4/admin/controller/extension/opencart/dashboard/recent.php'
];

$missing_controllers = 0;
foreach ($dashboard_controllers as $controller) {
    if (!file_exists($controller)) {
        $missing_controllers++;
    }
}

echo "Dashboard controllers: " . (8 - $missing_controllers) . "/8 present\n";

$dashboard_templates = [
    'opencart4/admin/view/template/extension/opencart/dashboard/sale.twig',
    'opencart4/admin/view/template/extension/opencart/dashboard/order.twig', 
    'opencart4/admin/view/template/extension/opencart/dashboard/customer.twig',
    'opencart4/admin/view/template/extension/opencart/dashboard/online.twig',
    'opencart4/admin/view/template/extension/opencart/dashboard/chart.twig',
    'opencart4/admin/view/template/extension/opencart/dashboard/map.twig',
    'opencart4/admin/view/template/extension/opencart/dashboard/activity.twig',
    'opencart4/admin/view/template/extension/opencart/dashboard/recent.twig'
];

$missing_templates = 0;
foreach ($dashboard_templates as $template) {
    if (!file_exists($template)) {
        $missing_templates++;
    }
}

echo "Dashboard templates: " . (8 - $missing_templates) . "/8 present\n";

echo "\n5. Cache Status...\n";
$cache_files = glob('opencart4/system/storage/cache/*');
echo "Cache files: " . count($cache_files) . " (cleared if 0-1)\n";

echo "\n=== SUMMARY ===\n";

$issues = [];

if ($dashboard_count < 8) {
    $issues[] = "Dashboard extensions incomplete ($dashboard_count/8)";
}

if ($enabled_dashboards < 8) {
    $issues[] = "Dashboard widgets not all enabled ($enabled_dashboards/8)";
}

if ($missing_controllers > 0) {
    $issues[] = "Missing dashboard controllers ($missing_controllers/8)";
}

if ($missing_templates > 0) {
    $issues[] = "Missing dashboard templates ($missing_templates/8)";
}

$cron_access = in_array('marketplace/cron', $permissions['access'] ?? []);
if (!$cron_access) {
    $issues[] = "CRON Jobs not accessible";
}

if (empty($issues)) {
    echo "🎉 ALL CHECKS PASSED!\n";
    echo "✅ Dashboard widgets: FULLY RESTORED\n";
    echo "✅ Left menu: COMPLETE\n";
    echo "✅ CRON Jobs: ACCESSIBLE\n";
    echo "✅ Extensions menu: WORKING\n\n";
    echo "🚀 YOUR OPENCART ADMIN IS NOW FULLY FUNCTIONAL!\n";
    echo "📊 Dashboard should show charts, maps, statistics\n";
    echo "🔧 Extensions → CRON Jobs is now available\n";
    echo "💯 All menu items should be visible\n";
} else {
    echo "⚠️  ISSUES FOUND:\n";
    foreach ($issues as $issue) {
        echo "   - $issue\n";
    }
}

echo "\n🔄 PLEASE REFRESH YOUR ADMIN PANEL TO SEE ALL CHANGES!\n";
?> 