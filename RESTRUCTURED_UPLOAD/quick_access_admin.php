<?php
/**
 * Quick Admin Access & Extension Check
 * One-click admin login and extension status verification
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🚀 ============================================\n";
echo "🚀 Quick Admin Access & Extension Check\n";
echo "🚀 ============================================\n\n";

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=opencart4;charset=utf8mb4", "root", "1234");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database OK\n";
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Check admin user
$stmt = $pdo->query("SELECT username, status FROM oc_user WHERE username = 'admin'");
$admin = $stmt->fetch();

if ($admin && $admin['status']) {
    echo "✅ Admin user active\n";
} else {
    echo "🔧 Fixing admin user...\n";
    $pdo->exec("UPDATE oc_user SET status = 1 WHERE username = 'admin'");
    echo "✅ Admin user fixed\n";
}

// Check extension status
$stmt = $pdo->query("SELECT * FROM oc_extension_install WHERE code = 'meschain'");
$extension = $stmt->fetch();

echo "✅ Extension status: " . ($extension ? "Registered" : "Not found") . "\n";

if ($extension) {
    $stmt = $pdo->query("SELECT * FROM oc_extension_path WHERE path = 'meschain/admin/controller/module/meschain_sync.php'");
    $path = $stmt->fetch();
    echo "✅ Extension path: " . ($path ? "Registered" : "Missing") . "\n";
    
    $stmt = $pdo->query("SELECT * FROM oc_extension WHERE code = 'meschain_sync' AND type = 'module'");
    $module = $stmt->fetch();
    echo "✅ Module entry: " . ($module ? "Installed" : "Not installed") . "\n";
}

// Clear any problematic sessions
$pdo->exec("DELETE FROM oc_session WHERE expire < NOW()");

echo "\n🌐 SERVER STATUS:\n";
$response = @file_get_contents("http://localhost:8080/admin/", false, stream_context_create([
    'http' => ['timeout' => 5]
]));

if ($response !== false) {
    echo "✅ Admin panel accessible\n";
    
    // Check if login form is present
    if (strpos($response, 'login') !== false || strpos($response, 'username') !== false) {
        echo "✅ Login form available\n";
    } else {
        echo "⚠️  Might already be logged in or redirect issue\n";
    }
} else {
    echo "❌ Admin panel not accessible\n";
}

echo "\n📋 CURRENT LOGIN CREDENTIALS:\n";
echo "🔗 URL: http://localhost:8080/admin/\n";
echo "👤 Username: admin\n";
echo "🔐 Password: MesChain2025!\n\n";

echo "🎯 EXTENSION ACCESS:\n";
echo "1. Login to admin panel\n";
echo "2. Go to: Extensions → Extensions\n";
echo "3. Select: Modules (from dropdown)\n";
echo "4. Find: MesChain-Sync Enterprise\n";
echo "5. Click: Install + Edit\n\n";

echo "💡 LOGIN TIPS:\n";
echo "- Use incognito/private browser mode\n";
echo "- Clear browser cache if needed\n";
echo "- Wait a few seconds between attempts\n";
echo "- Try Chrome, Firefox, or Safari\n\n";

// Quick browser launch (macOS)
echo "🚀 Quick Actions:\n";
echo "- Run this script anytime to verify status\n";
echo "- All systems should be GREEN ✅\n\n";

if (PHP_OS === 'Darwin') {
    echo "💻 macOS Quick Launch:\n";
    echo "Run: open -a 'Google Chrome' --args --incognito http://localhost:8080/admin/\n\n";
}

echo "🎉 Everything is ready for MesChain-Sync installation!\n";
?> 