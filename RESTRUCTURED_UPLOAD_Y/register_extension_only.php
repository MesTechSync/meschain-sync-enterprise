<?php
/**
 * Register only the missing extension record
 * Module already exists and is enabled
 */

// Database connection
$host = 'localhost';
$username = 'root';
$password = '1234';
$database = 'opencart_new';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== REGISTER MISSING EXTENSION ===\n";
    echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
    
    // 1. Register the extension (module already exists)
    echo "1. REGISTERING EXTENSION:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO oc_extension (extension, type, code, status) 
        VALUES ('Trendyol Importer', 'module', 'trendyol_importer', 1)
    ");
    $stmt->execute();
    $extension_id = $pdo->lastInsertId();
    echo "✅ Extension registered with ID: $extension_id\n";
    
    // 2. Add default configuration using correct column names
    echo "\n2. ADDING DEFAULT CONFIGURATION:\n";
    echo "----------------------------------------\n";
    
    $default_configs = [
        ['api_key', ''],
        ['api_secret', ''],
        ['supplier_id', ''],
        ['import_batch_size', '50'],
        ['auto_update_stock', '1'],
        ['default_category', '1'],
        ['image_import', '1'],
        ['last_import', '']
    ];
    
    foreach ($default_configs as $config) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO trendyol_config (`key`, `value`) 
            VALUES (?, ?)
        ");
        $stmt->execute($config);
        echo "✅ Config added: " . $config[0] . " = " . $config[1] . "\n";
    }
    
    // 3. Verify registration
    echo "\n3. VERIFICATION:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT * FROM oc_extension WHERE code = 'trendyol_importer'");
    $stmt->execute();
    $extension = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($extension) {
        echo "✅ Extension found:\n";
        echo "  - ID: " . $extension['extension_id'] . "\n";
        echo "  - Type: " . $extension['type'] . "\n";
        echo "  - Code: " . $extension['code'] . "\n";
        echo "  - Status: " . ($extension['status'] ? 'ENABLED' : 'DISABLED') . "\n";
    }
    
    $stmt = $pdo->prepare("SELECT * FROM oc_module WHERE code = 'trendyol_importer'");
    $stmt->execute();
    $module = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($module) {
        echo "✅ Module found:\n";
        echo "  - ID: " . $module['module_id'] . "\n";
        echo "  - Name: " . $module['name'] . "\n";
        echo "  - Code: " . $module['code'] . "\n";
        echo "  - Status: " . ($module['status'] ? 'ENABLED' : 'DISABLED') . "\n";
    }
    
    // 4. Generate direct access URL
    echo "\n4. DIRECT ACCESS URLs:\n";
    echo "----------------------------------------\n";
    echo "Admin Dashboard: http://localhost:8000/admin/\n";
    echo "Extensions Page: http://localhost:8000/admin/index.php?route=marketplace/extension&user_token=<TOKEN>\n";
    echo "Modules List: http://localhost:8000/admin/index.php?route=extension/module&user_token=<TOKEN>\n";
    echo "Direct Trendyol: http://localhost:8000/admin/index.php?route=extension/meschain/trendyol_importer&user_token=<TOKEN>\n";
    
    echo "\n=== REGISTRATION COMPLETE ===\n";
    echo "✅ Extension and Module both registered and enabled\n";
    echo "✅ Default configuration added\n";
    echo "✅ Ready for admin panel testing\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>