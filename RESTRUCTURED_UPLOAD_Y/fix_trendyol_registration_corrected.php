<?php
/**
 * Corrected Final Trendyol Extension Registration Fix
 * Fixes the table creation and registration issues
 */

// Database connection
$host = 'localhost';
$username = 'root';
$password = '1234';
$database = 'opencart_new';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CORRECTED TRENDYOL EXTENSION REGISTRATION ===\n";
    echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
    
    // 1. Clean up any existing registrations
    echo "1. CLEANING UP EXISTING REGISTRATIONS:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("DELETE FROM oc_extension WHERE code IN ('trendyol_importer', 'meschain_sync', 'meschain_trendyol')");
    $stmt->execute();
    $deleted_ext = $stmt->rowCount();
    echo "Deleted $deleted_ext existing extension records\n";
    
    $stmt = $pdo->prepare("DELETE FROM oc_module WHERE code IN ('trendyol_importer', 'meschain_sync', 'meschain_trendyol')");
    $stmt->execute();
    $deleted_mod = $stmt->rowCount();
    echo "Deleted $deleted_mod existing module records\n";
    
    // 2. Drop and recreate Trendyol tables to ensure proper structure
    echo "\n2. RECREATING TRENDYOL TABLES:\n";
    echo "----------------------------------------\n";
    
    $drop_tables = [
        'trendyol_logs',
        'trendyol_imports', 
        'trendyol_products',
        'trendyol_categories',
        'trendyol_config',
        'trendyol_statistics'
    ];
    
    foreach ($drop_tables as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS $table");
            echo "✅ Dropped table: $table\n";
        } catch (Exception $e) {
            echo "⚠️  Issue dropping table $table: " . $e->getMessage() . "\n";
        }
    }
    
    // Create tables with proper structure
    $tables = [
        'trendyol_config' => "
            CREATE TABLE trendyol_config (
                id INT AUTO_INCREMENT PRIMARY KEY,
                config_key VARCHAR(255) NOT NULL UNIQUE,
                config_value TEXT,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_config_key (config_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        'trendyol_categories' => "
            CREATE TABLE trendyol_categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                trendyol_category_id VARCHAR(255) NOT NULL UNIQUE,
                name VARCHAR(255) NOT NULL,
                parent_id INT DEFAULT 0,
                opencart_category_id INT DEFAULT NULL,
                status TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_trendyol_category_id (trendyol_category_id),
                INDEX idx_parent_id (parent_id),
                INDEX idx_opencart_category_id (opencart_category_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        'trendyol_products' => "
            CREATE TABLE trendyol_products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                opencart_product_id INT DEFAULT NULL,
                trendyol_product_id VARCHAR(255) NOT NULL,
                barcode VARCHAR(255),
                title VARCHAR(500) NOT NULL,
                description TEXT,
                brand VARCHAR(255),
                category_id INT,
                price DECIMAL(15,4) DEFAULT 0.0000,
                list_price DECIMAL(15,4) DEFAULT 0.0000,
                stock_quantity INT DEFAULT 0,
                images TEXT,
                attributes TEXT,
                variant_attributes TEXT,
                status TINYINT(1) DEFAULT 1,
                sync_status ENUM('pending', 'synced', 'error') DEFAULT 'pending',
                last_sync_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_trendyol_product (trendyol_product_id),
                INDEX idx_opencart_product_id (opencart_product_id),
                INDEX idx_barcode (barcode),
                INDEX idx_status (status),
                INDEX idx_sync_status (sync_status),
                FOREIGN KEY (category_id) REFERENCES trendyol_categories(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        'trendyol_imports' => "
            CREATE TABLE trendyol_imports (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(255) NOT NULL,
                import_type ENUM('manual', 'auto', 'cron') DEFAULT 'manual',
                total_products INT DEFAULT 0,
                processed_products INT DEFAULT 0,
                successful_imports INT DEFAULT 0,
                failed_imports INT DEFAULT 0,
                status ENUM('pending', 'processing', 'completed', 'failed', 'paused') DEFAULT 'pending',
                progress_percentage DECIMAL(5,2) DEFAULT 0.00,
                started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                completed_at TIMESTAMP NULL,
                error_message TEXT,
                import_data TEXT,
                INDEX idx_session_id (session_id),
                INDEX idx_status (status),
                INDEX idx_import_type (import_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        'trendyol_logs' => "
            CREATE TABLE trendyol_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                import_id INT DEFAULT NULL,
                level ENUM('debug', 'info', 'warning', 'error') DEFAULT 'info',
                message TEXT NOT NULL,
                context TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_import_id (import_id),
                INDEX idx_level (level),
                INDEX idx_created_at (created_at),
                FOREIGN KEY (import_id) REFERENCES trendyol_imports(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        'trendyol_statistics' => "
            CREATE TABLE trendyol_statistics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                stat_date DATE NOT NULL UNIQUE,
                total_products INT DEFAULT 0,
                active_products INT DEFAULT 0,
                inactive_products INT DEFAULT 0,
                imports_count INT DEFAULT 0,
                successful_imports INT DEFAULT 0,
                failed_imports INT DEFAULT 0,
                api_calls_count INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_stat_date (stat_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        "
    ];
    
    foreach ($tables as $table_name => $create_sql) {
        try {
            $pdo->exec($create_sql);
            echo "✅ Table created: $table_name\n";
        } catch (Exception $e) {
            echo "❌ Error creating table $table_name: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    // 3. Insert default configuration
    echo "\n3. INSERTING DEFAULT CONFIGURATION:\n";
    echo "----------------------------------------\n";
    
    $default_configs = [
        ['api_key', '', 'Trendyol API Key'],
        ['api_secret', '', 'Trendyol API Secret'], 
        ['supplier_id', '', 'Trendyol Supplier ID'],
        ['base_url', 'https://api.trendyol.com', 'Trendyol API Base URL'],
        ['import_batch_size', '50', 'Number of products to import per batch'],
        ['auto_update_stock', '1', 'Automatically update stock levels'],
        ['auto_update_prices', '1', 'Automatically update product prices'],
        ['default_category', '1', 'Default OpenCart category for imported products'],
        ['image_import', '1', 'Import product images'],
        ['import_variants', '1', 'Import product variants'],
        ['sync_interval', '3600', 'Auto sync interval in seconds'],
        ['last_import', '', 'Timestamp of last import'],
        ['last_sync', '', 'Timestamp of last sync'],
        ['debug_mode', '0', 'Enable debug mode'],
        ['webhook_enabled', '0', 'Enable webhook notifications']
    ];
    
    foreach ($default_configs as $config) {
        $stmt = $pdo->prepare("
            INSERT INTO trendyol_config (config_key, config_value, description) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            config_value = VALUES(config_value),
            description = VALUES(description)
        ");
        $stmt->execute($config);
        echo "✅ Config added/updated: " . $config[0] . "\n";
    }
    
    // 4. Register the extension
    echo "\n4. REGISTERING TRENDYOL EXTENSION:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO oc_extension (extension, type, code, status) 
        VALUES ('MesChain Trendyol Importer', 'module', 'meschain_trendyol', 1)
    ");
    $stmt->execute();
    $extension_id = $pdo->lastInsertId();
    echo "✅ Extension registered with ID: $extension_id\n";
    
    // 5. Register the module
    echo "\n5. REGISTERING TRENDYOL MODULE:\n";
    echo "----------------------------------------\n";
    
    $module_settings = json_encode([
        'name' => 'MesChain Trendyol Importer',
        'description' => 'Advanced Trendyol marketplace integration for OpenCart',
        'version' => '2.0.0',
        'author' => 'MesChain Technology',
        'enabled' => true,
        'auto_sync' => false,
        'debug_mode' => false
    ]);
    
    $stmt = $pdo->prepare("
        INSERT INTO oc_module (name, code, setting, status) 
        VALUES ('MesChain Trendyol Importer', 'meschain_trendyol', ?, 1)
    ");
    $stmt->execute([$module_settings]);
    $module_id = $pdo->lastInsertId();
    echo "✅ Module registered with ID: $module_id\n";
    
    // 6. Set up user permissions
    echo "\n6. SETTING UP USER PERMISSIONS:\n";
    echo "----------------------------------------\n";
    
    // Check which permission table exists
    $permission_tables = ['oc_user_group', 'oc_user_group_permission'];
    $permission_set = false;
    
    foreach ($permission_tables as $table) {
        try {
            $stmt = $pdo->prepare("SHOW TABLES LIKE '$table'");
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                echo "Found permissions table: $table\n";
                
                if ($table == 'oc_user_group') {
                    // Handle oc_user_group table with JSON permissions
                    $stmt = $pdo->prepare("SELECT user_group_id, permission FROM oc_user_group WHERE user_group_id = 1");
                    $stmt->execute();
                    $user_group = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($user_group) {
                        $permissions = json_decode($user_group['permission'], true) ?: [];
                        
                        $new_permissions = [
                            'extension/meschain/trendyol',
                            'extension/module/meschain_trendyol'
                        ];
                        
                        foreach ($new_permissions as $permission) {
                            if (!in_array($permission, $permissions['access'] ?? [])) {
                                $permissions['access'][] = $permission;
                            }
                            if (!in_array($permission, $permissions['modify'] ?? [])) {
                                $permissions['modify'][] = $permission;
                            }
                        }
                        
                        $stmt = $pdo->prepare("UPDATE oc_user_group SET permission = ? WHERE user_group_id = 1");
                        $stmt->execute([json_encode($permissions)]);
                        echo "✅ Permissions updated in user group\n";
                        $permission_set = true;
                    }
                } else {
                    // Handle oc_user_group_permission table
                    $permissions = [
                        'extension/meschain/trendyol',
                        'extension/module/meschain_trendyol'
                    ];
                    
                    foreach ($permissions as $permission) {
                        try {
                            $stmt = $pdo->prepare("INSERT IGNORE INTO $table (user_group_id, permission) VALUES (1, ?)");
                            $stmt->execute([$permission]);
                            echo "✅ Permission added: $permission\n";
                        } catch (Exception $e) {
                            echo "⚠️  Could not add permission: $permission - " . $e->getMessage() . "\n";
                        }
                    }
                    $permission_set = true;
                }
                break;
            }
        } catch (Exception $e) {
            continue;
        }
    }
    
    if (!$permission_set) {
        echo "⚠️  Could not set permissions - will need manual setup\n";
    }
    
    // 7. Final verification
    echo "\n7. FINAL VERIFICATION:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT * FROM oc_extension WHERE code = 'meschain_trendyol'");
    $stmt->execute();
    $extension = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($extension) {
        echo "✅ Extension verified:\n";
        echo "  - ID: " . $extension['extension_id'] . "\n";
        echo "  - Name: " . $extension['extension'] . "\n";
        echo "  - Type: " . $extension['type'] . "\n";
        echo "  - Code: " . $extension['code'] . "\n";
        echo "  - Status: " . ($extension['status'] ? 'ENABLED' : 'DISABLED') . "\n";
    } else {
        throw new Exception("Extension verification failed");
    }
    
    $stmt = $pdo->prepare("SELECT * FROM oc_module WHERE code = 'meschain_trendyol'");
    $stmt->execute();
    $module = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($module) {
        echo "✅ Module verified:\n";
        echo "  - ID: " . $module['module_id'] . "\n";
        echo "  - Name: " . $module['name'] . "\n";
        echo "  - Code: " . $module['code'] . "\n";
        echo "  - Status: " . ($module['status'] ? 'ENABLED' : 'DISABLED') . "\n";
    } else {
        throw new Exception("Module verification failed");
    }
    
    // Check table counts
    $table_counts = [];
    foreach (array_keys($tables) as $table_name) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table_name");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $table_counts[$table_name] = $result['count'];
    }
    
    echo "\n8. DATABASE TABLE STATUS:\n";
    echo "----------------------------------------\n";
    foreach ($table_counts as $table => $count) {
        echo "✅ $table: $count records\n";
    }
    
    echo "\n=== REGISTRATION COMPLETE ===\n";
    echo "✅ MesChain Trendyol extension successfully registered\n";
    echo "✅ All database tables created with proper structure\n";
    echo "✅ Default configuration inserted\n";
    echo "✅ Module enabled and ready for use\n";
    echo "\nThe extension should now be visible in:\n";
    echo "  - Extensions > Modules > MesChain Trendyol Importer\n";
    echo "\nNext steps:\n";
    echo "  1. Configure Trendyol API credentials\n";
    echo "  2. Test API connection\n";
    echo "  3. Start importing products\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
?>