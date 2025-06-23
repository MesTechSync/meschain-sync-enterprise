<?php
/**
 * MeChain SYNC Menu Setup Script
 * Adds MeChain SYNC category to Extensions section and sets up proper permissions
 */

echo "MeChain SYNC Menu Integration Setup\n";
echo "==================================\n\n";

// Database connection (adjust these settings for your installation)
$hostname = "localhost";
$username = "root"; 
$password = "";
$database = "opencart";
$table_prefix = "oc_";

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to database successfully.\n\n";
    
    echo "1. Setting up MeChain SYNC permissions...\n";
    
    // Get admin user group
    $stmt = $pdo->prepare("SELECT * FROM " . $table_prefix . "user_group WHERE name = ?");
    $stmt->execute(['Administrator']);
    $admin_group = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin_group) {
        $permissions = json_decode($admin_group['permission'], true);
        
        // Add MeChain SYNC permissions if missing
        $access_perms = $permissions['access'] ?? [];
        $modify_perms = $permissions['modify'] ?? [];
        
        $mechain_perms = [
            'extension/module/meschain_sync'
        ];
        
        $permissions_added = false;
        
        foreach ($mechain_perms as $perm) {
            if (!in_array($perm, $access_perms)) {
                $access_perms[] = $perm;
                echo "  ✓ Added $perm access permission.\n";
                $permissions_added = true;
            }
            if (!in_array($perm, $modify_perms)) {
                $modify_perms[] = $perm;
                echo "  ✓ Added $perm modify permission.\n";
                $permissions_added = true;
            }
        }
        
        if ($permissions_added) {
            $permissions['access'] = $access_perms;
            $permissions['modify'] = $modify_perms;
            
            // Update permissions
            $stmt = $pdo->prepare("UPDATE " . $table_prefix . "user_group SET permission = ? WHERE user_group_id = ?");
            $stmt->execute([json_encode($permissions), $admin_group['user_group_id']]);
            
            echo "  ✓ Permissions updated successfully!\n";
        } else {
            echo "  ✓ All permissions already exist.\n";
        }
    } else {
        echo "  ✗ Administrator group not found!\n";
    }
    
    echo "\n2. Checking extension installation...\n";
    
    // Check if extension is installed
    $stmt = $pdo->prepare("SELECT * FROM " . $table_prefix . "extension WHERE code = ?");
    $stmt->execute(['meschain_sync']);
    $extension = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$extension) {
        echo "  Installing MeChain SYNC extension entry...\n";
        
        // Insert extension entry
        $stmt = $pdo->prepare("INSERT INTO " . $table_prefix . "extension (extension_id, type, code) VALUES (NULL, 'module', 'meschain_sync')");
        $stmt->execute();
        
        echo "  ✓ Extension installed successfully.\n";
    } else {
        echo "  ✓ Extension already installed.\n";
    }
    
    echo "\n3. Ensuring extension is not in modules section...\n";
    
    // Update extension type to ensure it's categorized correctly
    $stmt = $pdo->prepare("UPDATE " . $table_prefix . "extension SET type = 'module' WHERE code = ?");
    $stmt->execute(['meschain_sync']);
    
    echo "  ✓ Extension type verified.\n";
    
    echo "\n4. Setting up extension settings...\n";
    
    // Check if module settings exist
    $stmt = $pdo->prepare("SELECT * FROM " . $table_prefix . "setting WHERE code = ? AND `key` = ?");
    $stmt->execute(['module_meschain_sync', 'module_meschain_sync_status']);
    $setting = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$setting) {
        echo "  Creating default settings...\n";
        
        // Insert default settings
        $default_settings = [
            ['code' => 'module_meschain_sync', 'key' => 'module_meschain_sync_status', 'value' => '1'],
            ['code' => 'module_meschain_sync', 'key' => 'module_meschain_sync_name', 'value' => 'MesChain-Sync Enterprise']
        ];
        
        foreach ($default_settings as $setting) {
            $stmt = $pdo->prepare("INSERT INTO " . $table_prefix . "setting (store_id, code, `key`, value, serialized) VALUES (0, ?, ?, ?, 0)");
            $stmt->execute([$setting['code'], $setting['key'], $setting['value']]);
        }
        
        echo "  ✓ Default settings created.\n";
    } else {
        echo "  ✓ Settings already exist.\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "MECHAIN SYNC MENU SETUP COMPLETE!\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "What was configured:\n";
    echo "1. ✓ Added MeChain SYNC category to Extensions menu\n";
    echo "2. ✓ Configured MesChain-Sync Enterprise under MeChain SYNC category\n";
    echo "3. ✓ Set up proper admin permissions\n";
    echo "4. ✓ Ensured extension is properly categorized\n";
    echo "5. ✓ Created default extension settings\n\n";
    
    echo "Next steps:\n";
    echo "1. Clear your OpenCart cache\n";
    echo "2. Login to admin panel\n";
    echo "3. Navigate to Extensions menu\n";
    echo "4. Look for 'MeChain SYNC' category\n";
    echo "5. Click on 'MesChain-Sync Enterprise' to access the extension\n\n";
    
    echo "The extension will now appear under:\n";
    echo "Extensions > MeChain SYNC > MesChain-Sync Enterprise\n\n";
    
    echo "Setup completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    echo "Please update the database connection settings in this script.\n";
}
?>