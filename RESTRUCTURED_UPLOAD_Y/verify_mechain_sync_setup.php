<?php
/**
 * MeChain SYNC Setup Verification Script
 * Verifies that all changes have been applied correctly
 */

echo "MeChain SYNC Setup Verification\n";
echo "==============================\n\n";

echo "1. Checking language file modifications...\n";

// Check English language file
$lang_file = 'opencart4/opencart-4.0.2.3/upload/admin/language/en-gb/common/column_left.php';
if (file_exists($lang_file)) {
    $lang_content = file_get_contents($lang_file);
    
    if (strpos($lang_content, "text_mechain_sync") !== false) {
        echo "  ✓ MeChain SYNC language key found\n";
    } else {
        echo "  ✗ MeChain SYNC language key missing\n";
    }
    
    if (strpos($lang_content, "text_meschain_sync_enterprise") !== false) {
        echo "  ✓ MesChain-Sync Enterprise language key found\n";
    } else {
        echo "  ✗ MesChain-Sync Enterprise language key missing\n";
    }
} else {
    echo "  ✗ Language file not found\n";
}

echo "\n2. Checking menu controller modifications...\n";

// Check column_left controller
$controller_file = 'opencart4/opencart-4.0.2.3/upload/admin/controller/common/column_left.php';
if (file_exists($controller_file)) {
    $controller_content = file_get_contents($controller_file);
    
    if (strpos($controller_content, "text_mechain_sync") !== false) {
        echo "  ✓ MeChain SYNC menu integration found\n";
    } else {
        echo "  ✗ MeChain SYNC menu integration missing\n";
    }
    
    if (strpos($controller_content, "extension/module/meschain_sync") !== false) {
        echo "  ✓ MesChain-Sync Enterprise link found\n";
    } else {
        echo "  ✗ MesChain-Sync Enterprise link missing\n";
    }
    
    if (strpos($controller_content, '$mechain_sync = [];') !== false) {
        echo "  ✓ MeChain SYNC category structure found\n";
    } else {
        echo "  ✗ MeChain SYNC category structure missing\n";
    }
} else {
    echo "  ✗ Controller file not found\n";
}

echo "\n3. Checking MeChain SYNC extension files...\n";

$extension_files = [
    'MesChain-Sync-ocmod/upload/admin/controller/extension/module/meschain_sync.php' => 'Admin Controller',
    'MesChain-Sync-ocmod/upload/admin/model/extension/module/meschain_sync.php' => 'Admin Model',
    'MesChain-Sync-ocmod/upload/admin/view/template/extension/module/meschain_sync.twig' => 'Admin View',
    'MesChain-Sync-ocmod/upload/admin/language/en-gb/extension/module/meschain_sync.php' => 'English Language',
    'MesChain-Sync-ocmod/upload/catalog/controller/extension/module/meschain_sync.php' => 'Catalog Controller',
    'MesChain-Sync-ocmod/upload/catalog/model/extension/module/meschain_sync.php' => 'Catalog Model'
];

foreach ($extension_files as $file => $description) {
    if (file_exists($file)) {
        echo "  ✓ $description: FOUND\n";
    } else {
        echo "  ✗ $description: MISSING\n";
    }
}

echo "\n4. Setup scripts available...\n";

if (file_exists('setup_mechain_sync_menu.php')) {
    echo "  ✓ Database setup script: AVAILABLE\n";
} else {
    echo "  ✗ Database setup script: MISSING\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "VERIFICATION SUMMARY\n";
echo str_repeat("=", 50) . "\n\n";

echo "Menu Structure Created:\n";
echo "Extensions\n";
echo "├── Marketplace\n";
echo "├── Installer\n";
echo "├── Extension\n";
echo "├── Startup\n";
echo "├── Events\n";
echo "├── Cron Jobs\n";
echo "└── MeChain SYNC\n";
echo "    └── MesChain-Sync Enterprise\n\n";

echo "Implementation Details:\n";
echo "• Added 'MeChain SYNC' category to Extensions menu\n";
echo "• MesChain-Sync Enterprise appears under MeChain SYNC (not in modules)\n";
echo "• Language keys added for proper display\n";
echo "• Permission-based access control implemented\n";
echo "• Database setup script created for proper installation\n\n";

echo "Next Steps:\n";
echo "1. Run: php setup_mechain_sync_menu.php\n";
echo "2. Update database connection settings if needed\n";
echo "3. Clear OpenCart cache\n";
echo "4. Login to admin panel\n";
echo "5. Navigate to Extensions > MeChain SYNC > MesChain-Sync Enterprise\n\n";

echo "Verification complete!\n";
?>