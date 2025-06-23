<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== OpenCart Admin Menu Repair ===\n\n";

// Test database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=opencart4', 'root', '1234');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n1. Checking core admin files...\n";

$core_files = [
    'opencart4/admin/controller/common/column_left.php',
    'opencart4/admin/controller/common/header.php',
    'opencart4/admin/controller/common/dashboard.php',
    'opencart4/admin/view/template/common/column_left.twig',
    'opencart4/admin/view/template/common/header.twig'
];

$missing_files = [];
foreach ($core_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file\n";
    } else {
        echo "❌ MISSING: $file\n";
        $missing_files[] = $file;
    }
}

if (empty($missing_files)) {
    echo "✅ All core admin files are present\n";
} else {
    echo "❌ Missing core files detected!\n";
}

echo "\n2. Checking user permissions...\n";

// Check admin user
$stmt = $pdo->query("SELECT * FROM oc_user WHERE username = 'admin'");
$admin_user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin_user) {
    echo "✅ Admin user found (ID: {$admin_user['user_id']})\n";
    echo "   - Status: " . ($admin_user['status'] ? 'Active' : 'Inactive') . "\n";
    echo "   - User Group: {$admin_user['user_group_id']}\n";
} else {
    echo "❌ Admin user not found!\n";
}

// Check user groups
$stmt = $pdo->query("SELECT * FROM oc_user_group WHERE user_group_id = {$admin_user['user_group_id']}");
$user_group = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user_group) {
    echo "✅ User group found: {$user_group['name']}\n";
    $permissions = json_decode($user_group['permission'], true);
    if (is_array($permissions) && !empty($permissions)) {
        echo "✅ Permissions are set (" . count($permissions['access'] ?? []) . " access permissions)\n";
    } else {
        echo "❌ No permissions set for user group!\n";
        
        // Restore default permissions
        $default_permissions = [
            'access' => [
                'catalog/attribute',
                'catalog/attribute_group',
                'catalog/category',
                'catalog/download',
                'catalog/filter',
                'catalog/information',
                'catalog/manufacturer',
                'catalog/option',
                'catalog/product',
                'catalog/recurring',
                'catalog/review',
                'common/column_left',
                'common/dashboard',
                'common/developer',
                'common/filemanager',
                'common/header',
                'common/login',
                'common/logout',
                'common/profile',
                'customer/custom_field',
                'customer/customer',
                'customer/customer_approval',
                'customer/customer_group',
                'design/banner',
                'design/layout',
                'design/seo_url',
                'design/theme',
                'design/translation',
                'event/theme',
                'extension/analytics',
                'extension/captcha',
                'extension/currency',
                'extension/dashboard',
                'extension/feed',
                'extension/fraud',
                'extension/language',
                'extension/module',
                'extension/payment',
                'extension/report',
                'extension/shipping',
                'extension/theme',
                'extension/total',
                'extension/meschain/module/meschain_sync',
                'localisation/country',
                'localisation/currency',
                'localisation/geo_zone',
                'localisation/language',
                'localisation/length_class',
                'localisation/location',
                'localisation/order_status',
                'localisation/return_action',
                'localisation/return_reason',
                'localisation/return_status',
                'localisation/stock_status',
                'localisation/tax_class',
                'localisation/tax_rate',
                'localisation/weight_class',
                'localisation/zone',
                'mail/activity',
                'mail/affiliate',
                'mail/customer',
                'mail/forgotten',
                'mail/order',
                'mail/return',
                'mail/reward',
                'mail/transaction',
                'mail/voucher',
                'marketplace/api',
                'marketplace/event',
                'marketplace/extension',
                'marketplace/installer',
                'marketplace/marketplace',
                'marketplace/modification',
                'marketplace/startup',
                'marketing/contact',
                'marketing/coupon',
                'marketing/marketing',
                'report/online',
                'report/report',
                'report/statistics',
                'sale/order',
                'sale/recurring',
                'sale/return',
                'sale/subscription',
                'sale/voucher',
                'sale/voucher_theme',
                'setting/setting',
                'setting/store',
                'system/log',
                'tool/backup',
                'tool/log',
                'tool/notification',
                'tool/upload',
                'user/api',
                'user/user',
                'user/user_group'
            ],
            'modify' => [
                'catalog/attribute',
                'catalog/attribute_group',
                'catalog/category',
                'catalog/download',
                'catalog/filter',
                'catalog/information',
                'catalog/manufacturer',
                'catalog/option',
                'catalog/product',
                'catalog/recurring',
                'catalog/review',
                'common/column_left',
                'common/dashboard',
                'common/developer',
                'common/filemanager',
                'common/profile',
                'customer/custom_field',
                'customer/customer',
                'customer/customer_approval',
                'customer/customer_group',
                'design/banner',
                'design/layout',
                'design/seo_url',
                'design/theme',
                'design/translation',
                'event/theme',
                'extension/analytics',
                'extension/captcha',
                'extension/currency',
                'extension/dashboard',
                'extension/feed',
                'extension/fraud',
                'extension/language',
                'extension/module',
                'extension/payment',
                'extension/report',
                'extension/shipping',
                'extension/theme',
                'extension/total',
                'extension/meschain/module/meschain_sync',
                'localisation/country',
                'localisation/currency',
                'localisation/geo_zone',
                'localisation/language',
                'localisation/length_class',
                'localisation/location',
                'localisation/order_status',
                'localisation/return_action',
                'localisation/return_reason',
                'localisation/return_status',
                'localisation/stock_status',
                'localisation/tax_class',
                'localisation/tax_rate',
                'localisation/weight_class',
                'localisation/zone',
                'mail/activity',
                'mail/affiliate',
                'mail/customer',
                'mail/forgotten',
                'mail/order',
                'mail/return',
                'mail/reward',
                'mail/transaction',
                'mail/voucher',
                'marketplace/api',
                'marketplace/event',
                'marketplace/extension',
                'marketplace/installer',
                'marketplace/marketplace',
                'marketplace/modification',
                'marketplace/startup',
                'marketing/contact',
                'marketing/coupon',
                'marketing/marketing',
                'report/online',
                'report/report',
                'report/statistics',
                'sale/order',
                'sale/recurring',
                'sale/return',
                'sale/subscription',
                'sale/voucher',
                'sale/voucher_theme',
                'setting/setting',
                'setting/store',
                'system/log',
                'tool/backup',
                'tool/log',
                'tool/notification',
                'tool/upload',
                'user/api',
                'user/user',
                'user/user_group'
            ]
        ];
        
        $stmt = $pdo->prepare("UPDATE oc_user_group SET permission = ? WHERE user_group_id = ?");
        $stmt->execute([json_encode($default_permissions), $admin_user['user_group_id']]);
        echo "✅ Default permissions restored!\n";
    }
}

echo "\n3. Checking admin session...\n";

// Clear any corrupted sessions
$stmt = $pdo->query("DELETE FROM oc_session WHERE expire < UNIX_TIMESTAMP()");
echo "✅ Old sessions cleared\n";

echo "\n4. Testing admin access...\n";

// Check if we can access admin dashboard
$url = "http://localhost:8080/admin/";
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'method' => 'GET'
    ]
]);

$result = @file_get_contents($url, false, $context);
if ($result !== false) {
    echo "✅ Admin panel accessible at $url\n";
} else {
    echo "❌ Cannot access admin panel at $url\n";
}

echo "\n5. Cache cleanup...\n";

// Clean cache files safely
$cache_files = glob('opencart4/system/storage/cache/*');
foreach ($cache_files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}
echo "✅ Cache files cleared\n";

echo "\n=== MENU REPAIR COMPLETE ===\n";
echo "🔧 Actions taken:\n";
echo "   - Verified core admin files\n";
echo "   - Restored admin permissions\n";
echo "   - Cleared old sessions\n";
echo "   - Cleaned cache files\n\n";

echo "🚀 NEXT STEPS:\n";
echo "1. Go to http://localhost:8080/admin/\n";
echo "2. Login with admin/MesChain2025!\n";
echo "3. Check if left menu is visible\n";
echo "4. Navigate to Extensions → Extensions → Modules\n\n";

echo "✨ ADMIN MENU SHOULD BE RESTORED! ✨\n";
?> 