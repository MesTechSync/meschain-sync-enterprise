<?php
// Final Extension Registration Fix - Using Correct Database Credentials
echo "<h1>🔧 MesChain-Sync Extension Registration - Final Fix</h1>\n";

// Use correct database credentials from OpenCart config
$host = 'localhost';
$username = 'root';
$password = '1234';  // Correct password from config.php
$database = 'opencart_new';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Database connection successful</p>\n";
} catch (PDOException $e) {
    die("<p>❌ Database connection failed: " . $e->getMessage() . "</p>\n");
}

// First, check if extension already exists
echo "<h2>1. 🔍 Checking Current Extension Status</h2>\n";
$stmt = $pdo->query("SELECT * FROM oc_extension WHERE code = 'trendyol_importer'");
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo "<p>⚠️ Extension already registered:</p>\n";
    echo "<ul><li>ID: " . $existing['extension_id'] . "</li>";
    echo "<li>Extension: " . $existing['extension'] . "</li>";
    echo "<li>Type: " . $existing['type'] . "</li>";
    echo "<li>Code: " . $existing['code'] . "</li></ul>\n";
} else {
    echo "<p>❌ Extension not found - proceeding with registration</p>\n";
}

// Register the extension
echo "<h2>2. 📝 Registering Trendyol Importer Extension</h2>\n";

try {
    // Delete any existing entries first (cleanup)
    $pdo->exec("DELETE FROM oc_extension WHERE code = 'trendyol_importer' OR code = 'meschain_sync'");
    echo "<p>🧹 Cleaned up any existing entries</p>\n";
    
    // Insert the correct extension registration
    $stmt = $pdo->prepare("INSERT INTO oc_extension (extension, type, code) VALUES (?, ?, ?)");
    $stmt->execute(['trendyol_importer', 'module', 'trendyol_importer']);
    
    $extension_id = $pdo->lastInsertId();
    echo "<p>✅ Extension registered successfully!</p>\n";
    echo "<p><strong>Extension ID:</strong> $extension_id</p>\n";
    echo "<p><strong>Extension:</strong> trendyol_importer</p>\n";
    echo "<p><strong>Type:</strong> module</p>\n";
    echo "<p><strong>Code:</strong> trendyol_importer</p>\n";
    
} catch (PDOException $e) {
    echo "<p>❌ Error registering extension: " . $e->getMessage() . "</p>\n";
}

// Verify the registration
echo "<h2>3. ✅ Verification</h2>\n";
$stmt = $pdo->query("SELECT * FROM oc_extension WHERE code = 'trendyol_importer'");
$verification = $stmt->fetch(PDO::FETCH_ASSOC);

if ($verification) {
    echo "<p>🎉 <strong>SUCCESS!</strong> Extension is now properly registered:</p>\n";
    echo "<ul>";
    echo "<li><strong>Extension ID:</strong> " . $verification['extension_id'] . "</li>";
    echo "<li><strong>Extension:</strong> " . $verification['extension'] . "</li>";
    echo "<li><strong>Type:</strong> " . $verification['type'] . "</li>";
    echo "<li><strong>Code:</strong> " . $verification['code'] . "</li>";
    echo "</ul>\n";
    
    echo "<h2>4. 🌐 Next Steps</h2>\n";
    echo "<ol>";
    echo "<li>Clear browser cache and refresh admin panel</li>";
    echo "<li>Navigate to Extensions → Modules in admin panel</li>";
    echo "<li>Look for 'Trendyol Importer' in the modules list</li>";
    echo "<li>Extension should now be visible and accessible</li>";
    echo "</ol>\n";
    
    echo "<p><strong>Direct Access URL:</strong> <a href='http://localhost:8090/admin/index.php?route=extension/meschain/trendyol_importer' target='_blank'>http://localhost:8090/admin/index.php?route=extension/meschain/trendyol_importer</a></p>\n";
    
} else {
    echo "<p>❌ Verification failed - extension not found after registration</p>\n";
}

echo "<hr>\n";
echo "<p><strong>🎯 Status:</strong> Extension registration process completed. Please test the admin panel now.</p>\n";
?>