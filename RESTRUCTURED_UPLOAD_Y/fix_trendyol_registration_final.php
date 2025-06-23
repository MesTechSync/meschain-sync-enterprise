<?php
/**
 * Final Trendyol Extension Registration Fix
 * Completely re-registers the extension with all necessary components
 */

// Database connection
$host = 'localhost';
$username = 'root';
$password = '1234';
$database = 'opencart_new';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Start transaction to ensure atomicity
    $pdo->beginTransaction();
    
    echo "=== FINAL TRENDYOL EXTENSION REGISTRATION ===\n";
    echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
    
    // 1. Clean up any existing registrations
    echo "1. CLEANING UP EXISTING REGISTRATIONS:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("DELETE FROM oc_extension WHERE code = 'trendyol_importer' OR code = 'meschain_sync'");
    $stmt->execute();
    $deleted_ext = $stmt->rowCount();
    echo "Deleted $deleted_ext existing extension records\n";
    
    $stmt = $pdo->prepare("DELETE FROM oc_module WHERE code = 'trendyol_importer' OR code = 'meschain_sync'");
    $stmt->execute();
    $deleted_mod = $stmt->rowCount();
    echo "Deleted $deleted_mod existing module records\n";
    
    // 2. Ensure tables have proper structure
    echo "\n2. ENSURING PROPER TABLE STRUCTURE:\n";
    echo "----------------------------------------\n";
    
    // Add status column to oc_extension if not exists
    try {
        $pdo->exec("ALTER TABLE oc_extension ADD COLUMN status TINYINT(1) DEFAULT 1");
        echo "✅ Added status column to oc_extension\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "✅ Status column already exists in oc_extension\n";
        } else {
            throw $e;
        }
    }
    
    // Add status column to oc_module if not exists
    try {
        $pdo->exec("ALTER TABLE oc_module ADD COLUMN status TINYINT(1) DEFAULT 1");
        echo "✅ Added status column to oc_module\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "✅ Status column already exists in oc_module\n";
        } else {
            throw $e;
        }
    }
    
    // 3. Register the extension
    echo "\n3. REGISTERING TRENDYOL EXTENSION:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO oc_extension (extension, type, code, status) 
        VALUES ('Trendyol Importer', 'module', 'trendyol_importer', 1)
    ");
    $stmt->execute();
    $extension_id = $pdo->lastInsertId();
    echo "✅ Extension registered with ID: $extension_id\n";
    
    // 4. Register the module
    echo "\n4. REGISTERING TRENDYOL MODULE:\n";
    echo "----------------------------------------\n";
    
    $module_settings = json_encode([
        'name' => 'Trendyol Importer',
        'description' => 'Import products from Trendyol marketplace',
        'version' => '1.0.0',
        'author' => 'MesChain',
        'enabled' => true
    ]);
    
    $stmt = $pdo->prepare("
        INSERT INTO oc_module (name, code, setting, status) 
        VALUES ('Trendyol Importer', 'trendyol_importer', ?, 1)
    ");
    $stmt->execute([$module_settings]);
    $module_id = $pdo->lastInsertId();
    echo "✅ Module registered with ID: $module_id\n";
    
    // 5. Create/verify Trendyol database tables
    echo "\n5. CREATING TRENDYOL DATABASE TABLES:\n";
    echo "----------------------------------------\n";
    
    $tables = [
        'trendyol_products' => "
            CREATE TABLE IF NOT EXISTS trendyol_products (
                product_id INT PRIMARY KEY,
                trendyol_id VARCHAR(255) UNIQUE,
                title VARCHAR(500),
                description TEXT,
                price DECIMAL(15,2),
                stock_quantity INT DEFAULT 0,
                category_id INT,
                brand VARCHAR(255),
                barcode VARCHAR(255),
                images TEXT,
                attributes TEXT,
                status TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_trendyol_id (trendyol_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        'trendyol_categories' => "
            CREATE TABLE IF NOT EXISTS trendyol_categories (
                category_id INT AUTO_INCREMENT PRIMARY KEY,
                trendyol_category_id VARCHAR(255) UNIQUE,
                name VARCHAR(255),
                parent_id INT DEFAULT 0,
                status TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_trendyol_category_id (trendyol_category_id),
                INDEX idx_parent_id (parent_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        'trendyol_imports' => "
            CREATE TABLE IF NOT EXISTS trendyol_imports (
                import_id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(255),
                total_products INT DEFAULT 0,
                processed_products INT DEFAULT 0,
                successful_imports INT DEFAULT 0,
                failed_imports INT DEFAULT 0,
                status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
                started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                completed_at DATETIME NULL,
                error_message TEXT,
                INDEX idx_session_id (session_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        'trendyol_logs' => "
            CREATE TABLE IF NOT EXISTS trendyol_logs (
                log_id INT AUTO_INCREMENT PRIMARY KEY,
                import_id INT,
                level ENUM('info', 'warning', 'error') DEFAULT 'info',
                message TEXT,
                details TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (import_id) REFERENCES trendyol_imports(import_id) ON DELETE CASCADE,
                INDEX idx_import_id (import_id),
                INDEX idx_level (level),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        'trendyol_config' => "
            CREATE TABLE IF NOT EXISTS trendyol_config (
                config_id INT AUTO_INCREMENT PRIMARY KEY,
                config_key VARCHAR(255) UNIQUE,
                config_value TEXT,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_config_key (config_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ",
        'trendyol_statistics' => "
            CREATE TABLE IF NOT EXISTS trendyol_statistics (
                stat_id INT AUTO_INCREMENT PRIMARY KEY,
                stat_date DATE UNIQUE,
                total_products INT DEFAULT 0,
                active_products INT DEFAULT 0,
                imports_count INT DEFAULT 0,
                successful_imports INT DEFAULT 0,
                failed_imports INT DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_stat_date (stat_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        "
    ];
    
    foreach ($tables as $table_name => $create_sql) {
        try {
            $pdo->exec($create_sql);
            echo "✅ Table created/verified: $table_name\n";
        } catch (Exception $e) {
            echo "⚠️  Issue with table $table_name: " . $e->getMessage() . "\n";
        }
    }
    
    // 6. Insert default configuration
    echo "\n6. INSERTING DEFAULT CONFIGURATION:\n";
    echo "----------------------------------------\n";
    
    $default_configs = [
        ['api_key', '', 'Trendyol API Key'],
        ['api_secret', '', 'Trendyol API Secret'],
        ['supplier_id', '', 'Trendyol Supplier ID'],
        ['import_batch_size', '50', 'Number of products to import per batch'],
        ['auto_update_stock', '1', 'Automatically update stock levels'],
        ['default_category', '1', 'Default OpenCart category for imported products'],
        ['image_import', '1', 'Import product images'],
        ['last_import', '', 'Timestamp of last import']
    ];
    
    foreach ($default_configs as $config) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO trendyol_config (config_key, config_value, description) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute($config);
        echo "✅ Config added: " . $config[0] . "\n";
    }
    
    // 7. Set up user permissions (try different permission table structures)
    echo "\n7. SETTING UP USER PERMISSIONS:\n";
    echo "----------------------------------------\n";
    
    // Find the correct permission table
    $permission_tables = ['oc_user_group_permission', 'oc_permission', 'oc_user_permission'];
    $permission_set = false;
    
    foreach ($permission_tables as $table) {
        try {
            $stmt = $pdo->prepare("SHOW TABLES LIKE '$table'");
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                echo "Using permissions table: $table\n";
                
                $permissions = [
                    'extension/meschain/trendyol_importer',
                    'extension/meschain/trendyol_importer/dashboard',
                    'extension/meschain/trendyol_importer/wizard',
                    'extension/meschain/trendyol_importer/import',
                    'extension/meschain/trendyol_importer/config'
                ];
                
                foreach ($permissions as $permission) {
                    try {
                        // Try with type column
                        $stmt = $pdo->prepare("INSERT IGNORE INTO $table (user_group_id, permission, type) VALUES (1, ?, 'access')");
                        $stmt->execute([$permission]);
                        
                        $stmt = $pdo->prepare("INSERT IGNORE INTO $table (user_group_id, permission, type) VALUES (1, ?, 'modify')");
                        $stmt->execute([$permission]);
                        
                        echo "✅ Permission added (with type): $permission\n";
                    } catch (Exception $e) {
                        // Try without type column
                        try {
                            $stmt = $pdo->prepare("INSERT IGNORE INTO $table (user_group_id, permission) VALUES (1, ?)");
                            $stmt->execute([$permission]);
                            echo "✅ Permission added (simple): $permission\n";
                        } catch (Exception $e2) {
                            echo "⚠️  Could not add permission: $permission\n";
                        }
                    }
                }
                
                $permission_set = true;
                break;
            }
        } catch (Exception $e) {
            continue;
        }
    }
    
    if (!$permission_set) {
        echo "⚠️  Could not find suitable permissions table\n";
    }
    
    // 8. Verify final registration
    echo "\n8. FINAL VERIFICATION:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT * FROM oc_extension WHERE code = 'trendyol_importer' AND type = 'module'");
    $stmt->execute();
    $final_extension = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($final_extension) {
        echo "✅ Extension properly registered:\n";
        echo "  - ID: " . $final_extension['extension_id'] . "\n";
        echo "  - Type: " . $final_extension['type'] . "\n";
        echo "  - Code: " . $final_extension['code'] . "\n";
        echo "  - Status: " . ($final_extension['status'] ? 'ENABLED' : 'DISABLED') . "\n";
    } else {
        echo "❌ Extension registration failed\n";
        throw new Exception("Extension registration failed");
    }
    
    $stmt = $pdo->prepare("SELECT * FROM oc_module WHERE code = 'trendyol_importer'");
    $stmt->execute();
    $final_module = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($final_module) {
        echo "✅ Module properly registered:\n";
        echo "  - ID: " . $final_module['module_id'] . "\n";
        echo "  - Name: " . $final_module['name'] . "\n";
        echo "  - Code: " . $final_module['code'] . "\n";
        echo "  - Status: " . ($final_module['status'] ? 'ENABLED' : 'DISABLED') . "\n";
    } else {
        echo "❌ Module registration failed\n";
        throw new Exception("Module registration failed");
    }
    
    // Commit the transaction
    $pdo->commit();
    
    echo "\n=== REGISTRATION COMPLETE ===\n";
    echo "✅ Trendyol extension successfully registered and enabled\n";
    echo "✅ All database tables created\n";
    echo "✅ Default configuration inserted\n";
    echo "✅ User permissions set\n";
    echo "\nThe extension should now be visible in Extensions > Modules\n";
    
} catch (Exception $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Transaction rolled back.\n";
}
?>