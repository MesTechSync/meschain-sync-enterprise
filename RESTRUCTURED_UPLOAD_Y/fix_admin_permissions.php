<?php
/**
 * Fix Admin Permissions Script
 * 
 * This script adds missing permissions for all MesChain Sync controllers (including Trendyol)
 * to the admin user group (user_group_id = 1).
 */

// Configuration
require_once('opencart_new/config.php');
require_once('opencart_new/admin/config.php');

// Connect to database
try {
    $pdo = new PDO("mysql:host=" . DB_HOSTNAME . ";dbname=" . DB_DATABASE . ";charset=utf8", DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

echo "<html><head><title>MesChain Sync Admin Permissions Fix</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1 { color: #2a6496; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    tr:nth-child(even) { background-color: #f9f9f9; }
</style>";
echo "</head><body>";
echo "<h1>MesChain Sync Admin Permissions Fix</h1>";

// Define the controllers that need permissions
$controllers = [
    // MesChain Sync core
    'extension/module/meschain_sync',
    
    // Trendyol controllers
    'extension/module/meschain_trendyol',
    'extension/meschain/category_mapping',
    'extension/meschain/brand_mapping',
    'extension/meschain/attribute_mapping',
    'extension/meschain/cron/trendyol',
    'extension/meschain/module/meschain_trendyol', 
    'extension/meschain/trendyol',
    
    // Other marketplace controllers (for consistent permissions)
    'extension/module/meschain_hepsiburada',
    'extension/module/meschain_n11',
    'extension/module/meschain_gittigidiyor',
    'extension/module/meschain_pazarama',
    'extension/module/meschain_amazon',
    'extension/module/meschain_ebay'
];

// Check current permissions
echo "<h2>Checking Current Permissions</h2>";
echo "<table>";
echo "<tr><th>Controller</th><th>Access</th><th>Modify</th><th>Status</th></tr>";

$existingPermissions = [];
$stmt = $pdo->query("SELECT * FROM " . DB_PREFIX . "user_group_permission WHERE user_group_id = 1");
while ($row = $stmt->fetch()) {
    $existingPermissions[$row['route'] . '|' . $row['type']] = true;
}

$changes = 0;

foreach ($controllers as $controller) {
    $accessExists = isset($existingPermissions[$controller . '|access']);
    $modifyExists = isset($existingPermissions[$controller . '|modify']);
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($controller) . "</td>";
    echo "<td>" . ($accessExists ? "✅" : "❌") . "</td>";
    echo "<td>" . ($modifyExists ? "✅" : "❌") . "</td>";
    
    if ($accessExists && $modifyExists) {
        echo "<td><span class='success'>Already configured</span></td>";
    } else {
        echo "<td><span class='warning'>Missing permissions</span></td>";
    }
    
    echo "</tr>";
    
    // Add missing permissions
    if (!$accessExists) {
        try {
            $stmt = $pdo->prepare("INSERT INTO " . DB_PREFIX . "user_group_permission (user_group_id, route, type) VALUES (1, :route, 'access')");
            $stmt->execute(['route' => $controller]);
            $changes++;
        } catch (PDOException $e) {
            echo "<p class='error'>Error adding access permission for " . htmlspecialchars($controller) . ": " . $e->getMessage() . "</p>";
        }
    }
    
    if (!$modifyExists) {
        try {
            $stmt = $pdo->prepare("INSERT INTO " . DB_PREFIX . "user_group_permission (user_group_id, route, type) VALUES (1, :route, 'modify')");
            $stmt->execute(['route' => $controller]);
            $changes++;
        } catch (PDOException $e) {
            echo "<p class='error'>Error adding modify permission for " . htmlspecialchars($controller) . ": " . $e->getMessage() . "</p>";
        }
    }
}

echo "</table>";

if ($changes > 0) {
    echo "<p class='success'>✅ Added " . $changes . " missing permissions to admin user group.</p>";
} else {
    echo "<p class='success'>✅ All permissions were already correctly configured.</p>";
}

// Fix module naming consistency
echo "<h2>Checking Module Name Consistency</h2>";

// Load marketplace module names from language files
$moduleNames = [];
$langFiles = glob('upload/admin/language/*/extension/module/meschain_*.php');
foreach ($langFiles as $langFile) {
    $content = file_get_contents($langFile);
    if (preg_match('/_\[\'heading_title\'\]\s*=\s*[\'"](.+?)[\'"]/i', $content, $matches)) {
        $module = basename($langFile, '.php');
        $moduleNames[$module] = $matches[1];
    }
}

echo "<table>";
echo "<tr><th>Module</th><th>Current Name</th><th>Standardized Name</th><th>Status</th></tr>";

// Define desired prefix
$desiredPrefix = "MesChain Sync - ";

foreach ($moduleNames as $module => $name) {
    $marketplace = str_replace('meschain_', '', $module);
    $marketplace = ucfirst($marketplace);
    
    $standardizedName = $desiredPrefix . $marketplace;
    $needsUpdate = ($name !== $standardizedName);
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($module) . "</td>";
    echo "<td>" . htmlspecialchars($name) . "</td>";
    echo "<td>" . htmlspecialchars($standardizedName) . "</td>";
    
    if ($needsUpdate) {
        echo "<td><span class='warning'>Needs update</span></td>";
        
        // Update language files for this module
        $moduleFiles = glob('upload/admin/language/*/extension/module/' . $module . '.php');
        foreach ($moduleFiles as $langFile) {
            $content = file_get_contents($langFile);
            $updated = preg_replace('/_\[\'heading_title\'\]\s*=\s*[\'"](.+?)[\'"]/i', 
                                   "_['heading_title'] = '" . $standardizedName . "'", 
                                   $content);
            
            if ($content !== $updated) {
                file_put_contents($langFile, $updated);
                $lang = basename(dirname(dirname(dirname($langFile))));
                echo "<p>✅ Updated " . htmlspecialchars($module) . " name in " . htmlspecialchars($lang) . " language file</p>";
            }
        }
    } else {
        echo "<td><span class='success'>Already standardized</span></td>";
    }
    
    echo "</tr>";
}

echo "</table>";

// Check if OpenCart admin cache might need flushing
echo "<h2>Cache Considerations</h2>";
echo "<p>In some OpenCart installations, you may need to clear the admin cache for changes to take effect.</p>";
echo "<p>Please access the admin panel and check if the permission issues are resolved. If not, try clearing the OpenCart cache.</p>";

// Complete
echo "<h2>Summary</h2>";
echo "<p class='success'>✓ Permission fixes applied</p>";
echo "<p class='success'>✓ Module naming standardization applied</p>";
echo "<p>You should now be able to access all MesChain Sync module sections in the admin panel.</p>";
echo "<p>For any remaining issues, please check:</p>";
echo "<ul>";
echo "<li>OpenCart admin cache (may need clearing)</li>";
echo "<li>User group settings in admin panel</li>";
echo "<li>File permissions on the server</li>";
echo "</ul>";

echo "</body></html>";
