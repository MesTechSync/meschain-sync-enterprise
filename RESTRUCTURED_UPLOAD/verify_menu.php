<?php
/**
 * OpenCart 4.0.2.3 Menu Verification Script
 */

echo "OpenCart Admin Menu Verification\n";
echo "================================\n\n";

// Check column_left.php for menu items
$column_left = file_get_contents("opencart4/opencart-4.0.2.3/upload/admin/controller/common/column_left.php");

echo "1. Checking menu items in column_left.php:\n";

// Check cron menu
if (strpos($column_left, "text_cron") !== false) {
    echo "✓ Cron menu item: FOUND\n";
} else {
    echo "✗ Cron menu item: MISSING\n";
}

// Check user menu items
if (strpos($column_left, "text_users") !== false) {
    echo "✓ Users menu item: FOUND\n";
} else {
    echo "✗ Users menu item: MISSING\n";
}

if (strpos($column_left, "text_user_group") !== false) {
    echo "✓ User Groups menu item: FOUND\n";
} else {
    echo "✗ User Groups menu item: MISSING\n";
}

if (strpos($column_left, "text_api") !== false) {
    echo "✓ API menu item: FOUND\n";
} else {
    echo "✗ API menu item: MISSING\n";
}

echo "\n2. Checking required files:\n";

$required_files = [
    "opencart4/opencart-4.0.2.3/upload/admin/controller/marketplace/cron.php" => "Cron Controller",
    "opencart4/opencart-4.0.2.3/upload/admin/model/setting/cron.php" => "Cron Model",
    "opencart4/opencart-4.0.2.3/upload/admin/view/template/marketplace/cron.twig" => "Cron View",
    "opencart4/opencart-4.0.2.3/upload/admin/view/template/marketplace/cron_list.twig" => "Cron List View",
    "opencart4/opencart-4.0.2.3/upload/admin/language/en-gb/marketplace/cron.php" => "Cron Language"
];

foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        echo "✓ $description: FOUND\n";
    } else {
        echo "✗ $description: MISSING\n";
    }
}

echo "\nVerification complete!\n";
?>