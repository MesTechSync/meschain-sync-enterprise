<?php
// Simple database structure check
$host = 'localhost';
$username = 'root';
$password = '1234';
$database = 'opencart_new';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== DATABASE ANALYSIS FOR EXTENSION ROUTING DEBUG ===\n\n";
    
    // Check if oc_extension_path table exists
    echo "1. Checking if oc_extension_path table exists:\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'oc_extension_path'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "✓ oc_extension_path table exists\n\n";
        
        // Get table structure
        echo "2. oc_extension_path table structure:\n";
        $stmt = $pdo->query("DESCRIBE oc_extension_path");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "   - {$row['Field']} ({$row['Type']})\n";
        }
        echo "\n";
        
        // Get all extension path data
        echo "3. Current extension path registrations:\n";
        $stmt = $pdo->query("SELECT * FROM oc_extension_path");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($rows)) {
            echo "   No data found in oc_extension_path table\n";
        } else {
            foreach ($rows as $row) {
                echo "   ID: {$row['extension_path_id']}, Path: {$row['path']}\n";
            }
        }
    } else {
        echo "✗ oc_extension_path table does not exist\n";
    }
    
    echo "\n4. Checking oc_extension table:\n";
    $stmt = $pdo->query("SELECT * FROM oc_extension WHERE type = 'module' ORDER BY code");
    $extensions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Found " . count($extensions) . " module extensions:\n";
    foreach ($extensions as $ext) {
        echo "   - Code: {$ext['code']}, Type: {$ext['type']}\n";
    }
    
    echo "\n5. Checking oc_user_group permissions:\n";
    $stmt = $pdo->query("SELECT user_group_id, name, permission FROM oc_user_group WHERE user_group_id = 1");
    $userGroup = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userGroup) {
        echo "   Admin group permissions:\n";
        $permissions = json_decode($userGroup['permission'], true);
        
        if (isset($permissions['access'])) {
            echo "   ACCESS permissions count: " . count($permissions['access']) . "\n";
            // Look for extension related permissions
            $extensionAccess = array_filter($permissions['access'], function($perm) {
                return strpos($perm, 'extension/') === 0 && strpos($perm, 'meschain') !== false;
            });
            echo "   Meschain extension access permissions:\n";
            foreach ($extensionAccess as $perm) {
                echo "     - $perm\n";
            }
        }
        
        if (isset($permissions['modify'])) {
            echo "   MODIFY permissions count: " . count($permissions['modify']) . "\n";
            $extensionModify = array_filter($permissions['modify'], function($perm) {
                return strpos($perm, 'extension/') === 0 && strpos($perm, 'meschain') !== false;
            });
            echo "   Meschain extension modify permissions:\n";
            foreach ($extensionModify as $perm) {
                echo "     - $perm\n";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
?>