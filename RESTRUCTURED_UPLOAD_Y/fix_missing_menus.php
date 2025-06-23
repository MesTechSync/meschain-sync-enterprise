<?php
echo "=== MISSING MENU ITEMS FIX ===\n\n";

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=opencart4', 'root', '1234');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "1. Checking current permissions...\n";

// Get current permissions
$stmt = $pdo->query("SELECT permission FROM oc_user_group WHERE user_group_id = 1");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$permissions = json_decode($result['permission'], true);

echo "Current access permissions: " . count($permissions['access']) . "\n";
echo "Current modify permissions: " . count($permissions['modify']) . "\n";

echo "\n2. Adding missing permissions...\n";

$missing_permissions = [
    'setting/setting',
    'setting/store', 
    'user/user',
    'user/user_group',
    'user/api',
    'tool/backup',
    'tool/upload',
    'tool/log',
    'tool/upgrade',
    'tool/notification',
    'system/log',
    'sale/order',
    'sale/subscription',
    'sale/returns',
    'sale/voucher',
    'sale/voucher_theme',
    'marketing/marketing',
    'marketing/affiliate',
    'marketing/coupon',
    'marketing/contact',
    'report/report',
    'report/online',
    'report/statistics',
    'localisation/language',
    'localisation/currency',
    'localisation/stock_status',
    'localisation/order_status',
    'localisation/return_action',
    'localisation/return_reason',
    'localisation/return_status',
    'localisation/country',
    'localisation/zone',
    'localisation/geo_zone',
    'localisation/tax_class',
    'localisation/tax_rate',
    'localisation/length_class',
    'localisation/weight_class',
    'localisation/location',
    'localisation/address_format'
];

$added_count = 0;
foreach ($missing_permissions as $perm) {
    if (!in_array($perm, $permissions['access'] ?? [])) {
        $permissions['access'][] = $perm;
        $added_count++;
        echo "✅ Added access: $perm\n";
    }
    
    if (!in_array($perm, $permissions['modify'] ?? [])) {
        $permissions['modify'][] = $perm;
        echo "✅ Added modify: $perm\n";
    }
}

if ($added_count > 0) {
    $stmt = $pdo->prepare("UPDATE oc_user_group SET permission = ? WHERE user_group_id = 1");
    $stmt->execute([json_encode($permissions)]);
    echo "✅ $added_count permissions added to database\n";
} else {
    echo "✅ All permissions already exist\n";
}

echo "\n3. Creating missing controllers...\n";

// Create tool/notification controller
if (!file_exists('opencart4/admin/controller/tool/notification.php')) {
    if (!is_dir('opencart4/admin/controller/tool')) {
        mkdir('opencart4/admin/controller/tool', 0755, true);
    }
    
    $content = '<?php
namespace Opencart\Admin\Controller\Tool;
class Notification extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language("tool/notification");
        $this->document->setTitle($this->language->get("heading_title"));
        $data["breadcrumbs"] = [];
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("text_home"),
            "href" => $this->url->link("common/dashboard", "user_token=" . $this->session->data["user_token"])
        ];
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("heading_title"),
            "href" => $this->url->link("tool/notification", "user_token=" . $this->session->data["user_token"])
        ];
        $data["user_token"] = $this->session->data["user_token"];
        $data["header"] = $this->load->controller("common/header");
        $data["column_left"] = $this->load->controller("common/column_left");
        $data["footer"] = $this->load->controller("common/footer");
        $this->response->setOutput($this->load->view("tool/notification", $data));
    }
}
?>';
    
    file_put_contents('opencart4/admin/controller/tool/notification.php', $content);
    echo "✅ Created tool/notification controller\n";
}

echo "\n4. Cache cleared\n";
$cache_files = glob('opencart4/system/storage/cache/*');
foreach ($cache_files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}

echo "\n=== MISSING MENUS FIXED ===\n";
echo "🔄 Refresh admin panel to see all menu items!\n";
?> 