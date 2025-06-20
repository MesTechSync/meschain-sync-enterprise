<?php
/**
 * OpenCart 4.0.2.3 Permission Fix for Admin Menu
 */

// Database connection (adjust these settings for your installation)
$hostname = "localhost";
$username = "root"; 
$password = "";
$database = "opencart";

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Get admin user group
    $stmt = $pdo->prepare("SELECT * FROM oc_user_group WHERE name = ?");
    $stmt->execute(['Administrator']);
    $admin_group = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin_group) {
        $permissions = json_decode($admin_group['permission'], true);
        
        // Add cron permissions if missing
        $access_perms = $permissions['access'] ?? [];
        $modify_perms = $permissions['modify'] ?? [];
        
        if (!in_array('marketplace/cron', $access_perms)) {
            $access_perms[] = 'marketplace/cron';
            echo "Added cron access permission.\n";
        }
        
        if (!in_array('marketplace/cron', $modify_perms)) {
            $modify_perms[] = 'marketplace/cron';
            echo "Added cron modify permission.\n";
        }
        
        // Add user management permissions
        $user_perms = ['user/user', 'user/user_permission', 'user/api'];
        foreach ($user_perms as $perm) {
            if (!in_array($perm, $access_perms)) {
                $access_perms[] = $perm;
                echo "Added $perm access permission.\n";
            }
            if (!in_array($perm, $modify_perms)) {
                $modify_perms[] = $perm;
                echo "Added $perm modify permission.\n";
            }
        }
        
        $permissions['access'] = $access_perms;
        $permissions['modify'] = $modify_perms;
        
        // Update permissions
        $stmt = $pdo->prepare("UPDATE oc_user_group SET permission = ? WHERE user_group_id = ?");
        $stmt->execute([json_encode($permissions), $admin_group['user_group_id']]);
        
        echo "Permissions updated successfully!\n";
    } else {
        echo "Administrator group not found!\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    echo "Please update the database connection settings in this script.\n";
}
?>