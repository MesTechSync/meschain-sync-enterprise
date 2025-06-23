<?php
/**
 * OpenCart 4 Admin Login Fix
 * Solves session, cache and password issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔧 ============================================\n";
echo "🔧 OpenCart 4 Admin Login Fix\n";
echo "🔧 ============================================\n\n";

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

echo "🔍 1. Checking admin user status...\n";
$stmt = $pdo->query("SELECT user_id, username, firstname, lastname, email, status FROM {$db_config['prefix']}user WHERE username = 'admin'");
$admin = $stmt->fetch();

if ($admin) {
    echo "✅ Admin user found:\n";
    echo "   - User ID: {$admin['user_id']}\n";
    echo "   - Username: {$admin['username']}\n";
    echo "   - Name: {$admin['firstname']} {$admin['lastname']}\n";
    echo "   - Email: {$admin['email']}\n";
    echo "   - Status: " . ($admin['status'] ? 'Active' : 'Inactive') . "\n\n";
    
    if (!$admin['status']) {
        echo "🔧 Activating admin user...\n";
        $pdo->exec("UPDATE {$db_config['prefix']}user SET status = 1 WHERE username = 'admin'");
        echo "✅ Admin user activated\n\n";
    }
} else {
    echo "❌ Admin user not found!\n";
    exit(1);
}

echo "🔧 2. Resetting admin password to 'MesChain2025!'...\n";
$new_password = 'MesChain2025!';
$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE {$db_config['prefix']}user SET password = ? WHERE username = 'admin'");
$stmt->execute([$password_hash]);

echo "✅ Password updated successfully\n";
echo "   New password: {$new_password}\n\n";

echo "🧹 3. Clearing sessions and cache...\n";

// Clear session table
$pdo->exec("DELETE FROM {$db_config['prefix']}session");
echo "✅ Sessions cleared\n";

// Clear cache files if they exist
$cache_paths = [
    'opencart4/system/storage/cache',
    'opencart4/system/storage/session',
    'opencart4/system/storage/logs'
];

foreach ($cache_paths as $cache_path) {
    if (is_dir($cache_path)) {
        // Clear cache files but keep directories
        $files = glob($cache_path . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "✅ Cleared: {$cache_path}\n";
    }
}

echo "\n🔧 4. Fixing user permissions...\n";
$stmt = $pdo->query("SELECT COUNT(*) as count FROM {$db_config['prefix']}user_group WHERE user_group_id = 1");
$group_exists = $stmt->fetch()['count'] > 0;

if (!$group_exists) {
    echo "🔧 Creating administrator user group...\n";
    $pdo->exec("INSERT INTO {$db_config['prefix']}user_group (user_group_id, name, permission) VALUES (1, 'Administrator', '{\"access\":[\"*\"],\"modify\":[\"*\"]}')");
    echo "✅ Administrator group created\n";
}

// Ensure admin is in administrator group
$pdo->exec("UPDATE {$db_config['prefix']}user SET user_group_id = 1 WHERE username = 'admin'");
echo "✅ Admin assigned to administrator group\n\n";

echo "🔧 5. Checking OpenCart configuration...\n";

$config_file = 'opencart4/admin/config.php';
if (file_exists($config_file)) {
    $config_content = file_get_contents($config_file);
    
    // Check session configuration
    if (strpos($config_content, "SESSION_ENGINE") !== false) {
        echo "✅ Session engine configured\n";
    } else {
        echo "⚠️  Session engine not explicitly configured\n";
    }
    
    // Check if HTTP_SERVER matches our localhost
    if (strpos($config_content, "localhost:8080") !== false) {
        echo "✅ HTTP_SERVER configuration correct\n";
    } else {
        echo "⚠️  HTTP_SERVER might need adjustment\n";
    }
} else {
    echo "❌ Admin config file not found\n";
}

echo "\n🌐 6. Testing database connection from OpenCart perspective...\n";

// Test with OpenCart database credentials
try {
    $oc_config = [];
    if (file_exists('opencart4/config.php')) {
        $config_content = file_get_contents('opencart4/config.php');
        if (preg_match("/define\('DB_HOSTNAME', '([^']+)'\)/", $config_content, $matches)) {
            $oc_config['host'] = $matches[1];
        }
        if (preg_match("/define\('DB_USERNAME', '([^']+)'\)/", $config_content, $matches)) {
            $oc_config['user'] = $matches[1];
        }
        if (preg_match("/define\('DB_DATABASE', '([^']+)'\)/", $config_content, $matches)) {
            $oc_config['database'] = $matches[1];
        }
        
        echo "✅ OpenCart database config:\n";
        echo "   - Host: " . ($oc_config['host'] ?? 'not found') . "\n";
        echo "   - User: " . ($oc_config['user'] ?? 'not found') . "\n";
        echo "   - Database: " . ($oc_config['database'] ?? 'not found') . "\n\n";
    }
} catch (Exception $e) {
    echo "⚠️  Could not read OpenCart config: " . $e->getMessage() . "\n\n";
}

echo "🎯 ============================================\n";
echo "🎯 LOGIN FIX COMPLETED\n";
echo "🎯 ============================================\n\n";

echo "📋 LOGIN INSTRUCTIONS:\n";
echo "1. 🌐 Open a new browser tab (incognito/private mode recommended)\n";
echo "2. 🔗 Go to: http://localhost:8080/admin/\n";
echo "3. 🔑 Username: admin\n";
echo "4. 🔐 Password: MesChain2025!\n";
echo "5. 🚀 Click Login\n\n";

echo "💡 TROUBLESHOOTING TIPS:\n";
echo "- 🧹 Use incognito/private browser mode\n";
echo "- 🔄 Clear browser cache and cookies\n";
echo "- 🕐 Wait 30 seconds between login attempts\n";
echo "- 📱 Try different browser if still fails\n\n";

echo "🔧 If still having issues, run this script again\n";
echo "✅ Password is now consistently: MesChain2025!\n";
?> 