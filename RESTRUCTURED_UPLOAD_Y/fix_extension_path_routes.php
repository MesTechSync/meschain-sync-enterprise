<?php
/**
 * Fix Extension Path Routes - OpenCart 4.x Debugging
 * 
 * PROBLEM: Extensions are registered with wrong paths in oc_extension_path table
 * SOLUTION: Update paths from meschain_sync/* to extension/*
 */

$host = 'localhost';
$username = 'root';
$password = '1234';
$database = 'opencart_new';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== FIXING EXTENSION PATH ROUTES ===\n\n";
    
    // 1. Show current problematic paths
    echo "1. Current problematic extension paths:\n";
    $stmt = $pdo->query("SELECT * FROM oc_extension_path WHERE path LIKE 'meschain_sync/%'");
    $problematicPaths = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($problematicPaths)) {
        echo "   No problematic paths found. Extensions may already be fixed.\n";
        exit;
    }
    
    foreach ($problematicPaths as $path) {
        echo "   ID: {$path['extension_path_id']}, Path: {$path['path']}\n";
    }
    
    // 2. Create mapping of wrong -> correct paths
    $pathMappings = [];
    foreach ($problematicPaths as $path) {
        $oldPath = $path['path'];
        // Convert: meschain_sync/admin/controller/module/meschain_trendyol.php
        // To: extension/admin/controller/module/meschain_trendyol.php
        $newPath = str_replace('meschain_sync/', 'extension/', $oldPath);
        
        $pathMappings[] = [
            'id' => $path['extension_path_id'],
            'old' => $oldPath,
            'new' => $newPath
        ];
    }
    
    echo "\n2. Planned path corrections:\n";
    foreach ($pathMappings as $mapping) {
        echo "   ID {$mapping['id']}:\n";
        echo "     FROM: {$mapping['old']}\n";
        echo "     TO:   {$mapping['new']}\n\n";
    }
    
    // 3. Ask for confirmation
    echo "3. Do you want to apply these fixes? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $confirmation = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($confirmation) !== 'y') {
        echo "Operation cancelled.\n";
        exit;
    }
    
    // 4. Apply the fixes
    echo "\n4. Applying fixes...\n";
    $pdo->beginTransaction();
    
    try {
        $updateStmt = $pdo->prepare("UPDATE oc_extension_path SET path = ? WHERE extension_path_id = ?");
        
        foreach ($pathMappings as $mapping) {
            $updateStmt->execute([$mapping['new'], $mapping['id']]);
            echo "   ✓ Updated ID {$mapping['id']}\n";
        }
        
        $pdo->commit();
        echo "\n✅ All extension paths updated successfully!\n";
        
        // 5. Verify the changes
        echo "\n5. Verification - Updated paths:\n";
        $stmt = $pdo->query("SELECT * FROM oc_extension_path WHERE path LIKE 'extension/%' AND path LIKE '%meschain%'");
        $updatedPaths = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($updatedPaths as $path) {
            echo "   ID: {$path['extension_path_id']}, Path: {$path['path']}\n";
        }
        
        echo "\n6. Next Steps:\n";
        echo "   - Clear OpenCart cache (if any)\n";
        echo "   - Test extension access in admin panel\n";
        echo "   - Routes should now work as: extension/module/meschain_*\n";
        
    } catch (Exception $e) {
        $pdo->rollback();
        echo "❌ Error during update: " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
?>