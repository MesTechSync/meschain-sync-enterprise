<?php
/**
 * Check actual structure of Trendyol tables
 */

// Database connection
$host = 'localhost';
$username = 'root';
$password = '1234';
$database = 'opencart_new';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== TRENDYOL TABLES STRUCTURE CHECK ===\n";
    echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Check all Trendyol tables
    $trendyol_tables = [
        'trendyol_products',
        'trendyol_categories', 
        'trendyol_imports',
        'trendyol_logs',
        'trendyol_config',
        'trendyol_statistics'
    ];
    
    foreach ($trendyol_tables as $table) {
        echo "TABLE: $table\n";
        echo "----------------------------------------\n";
        
        try {
            $stmt = $pdo->prepare("DESCRIBE $table");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($columns) {
                foreach ($columns as $column) {
                    echo sprintf("  %-20s %-20s %s\n", 
                        $column['Field'], 
                        $column['Type'], 
                        $column['Null'] === 'YES' ? 'NULL' : 'NOT NULL'
                    );
                }
            } else {
                echo "  No columns found\n";
            }
        } catch (Exception $e) {
            echo "  ❌ Table does not exist or error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    // Also check extension and module tables
    echo "EXTENSION REGISTRATION CHECK:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT * FROM oc_extension WHERE code LIKE '%trendyol%' OR code LIKE '%meschain%'");
    $stmt->execute();
    $extensions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($extensions) {
        foreach ($extensions as $ext) {
            echo "Extension ID: " . $ext['extension_id'] . "\n";
            echo "  Type: " . $ext['type'] . "\n";
            echo "  Code: " . $ext['code'] . "\n";
            echo "  Extension: " . $ext['extension'] . "\n";
            echo "  Status: " . (isset($ext['status']) ? ($ext['status'] ? 'ENABLED' : 'DISABLED') : 'NO STATUS COLUMN') . "\n\n";
        }
    } else {
        echo "No extensions found\n";
    }
    
    echo "MODULE REGISTRATION CHECK:\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT * FROM oc_module WHERE code LIKE '%trendyol%' OR code LIKE '%meschain%'");
    $stmt->execute();
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($modules) {
        foreach ($modules as $mod) {
            echo "Module ID: " . $mod['module_id'] . "\n";
            echo "  Name: " . $mod['name'] . "\n";
            echo "  Code: " . $mod['code'] . "\n";
            echo "  Status: " . (isset($mod['status']) ? ($mod['status'] ? 'ENABLED' : 'DISABLED') : 'NO STATUS COLUMN') . "\n\n";
        }
    } else {
        echo "No modules found\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>