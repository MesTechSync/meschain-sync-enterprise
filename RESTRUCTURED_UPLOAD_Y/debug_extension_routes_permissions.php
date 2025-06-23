<?php
/**
 * Debug Extension Routes and Permissions
 * 
 * This script investigates:
 * 1. Route registration issues in oc_extension_path
 * 2. Permission problems in oc_user_group  
 * 3. Extension discovery vs actual file paths
 * 4. Controller route mapping inconsistencies
 */

// Database connection
$host = 'localhost';
$username = 'root';
$password = '1234';
$database = 'opencart_new';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>🔍 Extension Routes & Permissions Debug Analysis</h1>\n";
    echo "<div style='font-family: monospace; line-height: 1.6;'>\n";
    
    // === 1. Check oc_extension_path table ===
    echo "<h2>1️⃣ Extension Path Registrations (oc_extension_path)</h2>\n";
    
    $stmt = $pdo->query("
        SELECT extension_path_id, extension_install_id, `path`, `output` 
        FROM oc_extension_path 
        WHERE `path` LIKE '%meschain%' 
        ORDER BY `path`
    ");
    
    $extension_paths = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($extension_paths)) {
        echo "<p style='color: red;'>❌ NO MESCHAIN EXTENSIONS FOUND IN oc_extension_path!</p>\n";
    } else {
        echo "<table border='1' cellpadding='5'>\n";
        echo "<tr><th>Path ID</th><th>Install ID</th><th>Route Path</th><th>Output Class</th><th>Analysis</th></tr>\n";
        
        foreach ($extension_paths as $path) {
            $analysis = "";
            
            // Check if route follows correct OpenCart 4.x pattern
            if (strpos($path['path'], 'extension/meschain_sync/module/') === 0) {
                $analysis = "<span style='color: red;'>❌ WRONG ROUTE PATTERN</span>";
            } elseif (strpos($path['path'], 'extension/module/meschain_') === 0) {
                $analysis = "<span style='color: green;'>✅ CORRECT ROUTE PATTERN</span>";
            } else {
                $analysis = "<span style='color: orange;'>⚠️ UNKNOWN PATTERN</span>";
            }
            
            echo "<tr>";
            echo "<td>{$path['extension_path_id']}</td>";
            echo "<td>{$path['extension_install_id']}</td>";
            echo "<td><strong>{$path['path']}</strong></td>";
            echo "<td>{$path['output']}</td>";
            echo "<td>{$analysis}</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    // === 2. Check oc_extension table ===
    echo "<h2>2️⃣ Extension Registrations (oc_extension)</h2>\n";
    
    $stmt = $pdo->query("
        SELECT extension_id, extension_install_id, type, code 
        FROM oc_extension 
        WHERE code LIKE '%meschain%' 
        ORDER BY type, code
    ");
    
    $extensions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($extensions)) {
        echo "<p style='color: red;'>❌ NO MESCHAIN EXTENSIONS FOUND IN oc_extension!</p>\n";
    } else {
        echo "<table border='1' cellpadding='5'>\n";
        echo "<tr><th>Extension ID</th><th>Install ID</th><th>Type</th><th>Code</th></tr>\n";
        
        foreach ($extensions as $ext) {
            echo "<tr>";
            echo "<td>{$ext['extension_id']}</td>";
            echo "<td>{$ext['extension_install_id']}</td>";
            echo "<td>{$ext['type']}</td>";
            echo "<td><strong>{$ext['code']}</strong></td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    // === 3. Check User Group Permissions ===
    echo "<h2>3️⃣ Admin User Group Permissions (oc_user_group)</h2>\n";
    
    $stmt = $pdo->query("
        SELECT user_group_id, name, permission 
        FROM oc_user_group 
        WHERE user_group_id = 1
    ");
    
    $user_group = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user_group) {
        echo "<h3>Admin Group: {$user_group['name']} (ID: {$user_group['user_group_id']})</h3>\n";
        
        $permissions = json_decode($user_group['permission'], true);
        
        if ($permissions) {
            echo "<h4>Access Permissions:</h4>\n";
            if (isset($permissions['access'])) {
                $meschain_access = array_filter($permissions['access'], function($perm) {
                    return strpos($perm, 'meschain') !== false;
                });
                
                if (empty($meschain_access)) {
                    echo "<p style='color: red;'>❌ NO MESCHAIN ACCESS PERMISSIONS FOUND!</p>\n";
                } else {
                    echo "<ul>\n";
                    foreach ($meschain_access as $perm) {
                        echo "<li><code>{$perm}</code></li>\n";
                    }
                    echo "</ul>\n";
                }
            } else {
                echo "<p style='color: red;'>❌ NO ACCESS PERMISSIONS ARRAY!</p>\n";
            }
            
            echo "<h4>Modify Permissions:</h4>\n";
            if (isset($permissions['modify'])) {
                $meschain_modify = array_filter($permissions['modify'], function($perm) {
                    return strpos($perm, 'meschain') !== false;
                });
                
                if (empty($meschain_modify)) {
                    echo "<p style='color: red;'>❌ NO MESCHAIN MODIFY PERMISSIONS FOUND!</p>\n";
                } else {
                    echo "<ul>\n";
                    foreach ($meschain_modify as $perm) {
                        echo "<li><code>{$perm}</code></li>\n";
                    }
                    echo "</ul>\n";
                }
            } else {
                echo "<p style='color: red;'>❌ NO MODIFY PERMISSIONS ARRAY!</p>\n";
            }
        } else {
            echo "<p style='color: red;'>❌ INVALID PERMISSIONS JSON!</p>\n";
        }
    } else {
        echo "<p style='color: red;'>❌ ADMIN USER GROUP NOT FOUND!</p>\n";
    }
    
    // === 4. File Structure vs Database Comparison ===
    echo "<h2>4️⃣ File Structure vs Database Route Mapping</h2>\n";
    
    $admin_controller_dir = __DIR__ . '/opencart_new/admin/controller/extension/module/';
    
    if (is_dir($admin_controller_dir)) {
        $meschain_files = glob($admin_controller_dir . 'meschain_*.php');
        
        echo "<h3>Actual Controller Files Found:</h3>\n";
        echo "<ul>\n";
        foreach ($meschain_files as $file) {
            $filename = basename($file, '.php');
            $expected_route = "extension/module/{$filename}";
            echo "<li><code>{$filename}.php</code> → Expected route: <strong>{$expected_route}</strong></li>\n";
        }
        echo "</ul>\n";
        
        echo "<h3>Route Registration vs File Existence Analysis:</h3>\n";
        if (!empty($extension_paths)) {
            echo "<table border='1' cellpadding='5'>\n";
            echo "<tr><th>Database Route</th><th>Expected File</th><th>File Exists?</th><th>Status</th></tr>\n";
            
            foreach ($extension_paths as $path) {
                $route_parts = explode('/', $path['path']);
                
                if (count($route_parts) >= 3 && $route_parts[0] === 'extension') {
                    $expected_file = $admin_controller_dir . end($route_parts) . '.php';
                    $file_exists = file_exists($expected_file);
                    
                    $status = $file_exists ? 
                        "<span style='color: green;'>✅ MATCH</span>" : 
                        "<span style='color: red;'>❌ MISSING FILE</span>";
                    
                    echo "<tr>";
                    echo "<td><code>{$path['path']}</code></td>";
                    echo "<td><code>" . basename($expected_file) . "</code></td>";
                    echo "<td>" . ($file_exists ? 'YES' : 'NO') . "</td>";
                    echo "<td>{$status}</td>";
                    echo "</tr>\n";
                }
            }
            echo "</table>\n";
        }
    } else {
        echo "<p style='color: red;'>❌ Admin controller directory not found!</p>\n";
    }
    
    // === 5. Specific Route Analysis ===
    echo "<h2>5️⃣ Specific Route Analysis</h2>\n";
    
    $problematic_routes = [
        'extension/meschain_sync/module/meschain_hepsiburada',
        'extension/meschain_sync/module/meschain_ebay',
        'extension/meschain_sync/module/meschain_trendyol'
    ];
    
    $correct_routes = [
        'extension/module/meschain_hepsiburada',
        'extension/module/meschain_ebay', 
        'extension/module/meschain_trendyol'
    ];
    
    echo "<h3>Problematic Routes (From Terminal Logs):</h3>\n";
    echo "<table border='1' cellpadding='5'>\n";
    echo "<tr><th>Wrong Route</th><th>Correct Route</th><th>In Database?</th><th>File Exists?</th></tr>\n";
    
    for ($i = 0; $i < count($problematic_routes); $i++) {
        $wrong_route = $problematic_routes[$i];
        $correct_route = $correct_routes[$i];
        
        // Check if wrong route is in database
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM oc_extension_path WHERE `path` = ?");
        $stmt->execute([$wrong_route]);
        $wrong_in_db = $stmt->fetchColumn() > 0;
        
        // Check if correct route is in database  
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM oc_extension_path WHERE `path` = ?");
        $stmt->execute([$correct_route]);
        $correct_in_db = $stmt->fetchColumn() > 0;
        
        // Check if file exists for correct route
        $controller_name = basename($correct_route);
        $file_path = $admin_controller_dir . $controller_name . '.php';
        $file_exists = file_exists($file_path);
        
        echo "<tr>";
        echo "<td><code style='color: red;'>{$wrong_route}</code></td>";
        echo "<td><code style='color: green;'>{$correct_route}</code></td>";
        echo "<td>" . ($wrong_in_db ? 'WRONG ❌' : ($correct_in_db ? 'CORRECT ✅' : 'MISSING ❌')) . "</td>";
        echo "<td>" . ($file_exists ? 'YES ✅' : 'NO ❌') . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // === 6. Diagnostic Summary ===
    echo "<h2>6️⃣ Diagnostic Summary & Root Cause Analysis</h2>\n";
    
    $issues = [];
    $fixes = [];
    
    // Analyze issues
    if (empty($extension_paths)) {
        $issues[] = "No extensions registered in oc_extension_path table";
        $fixes[] = "Register extensions with correct routes";
    }
    
    if (empty($extensions)) {
        $issues[] = "No extensions registered in oc_extension table";
        $fixes[] = "Install/register all Meschain extensions";
    }
    
    if (isset($permissions) && (empty($permissions['access']) || empty($permissions['modify']))) {
        $issues[] = "Admin user group missing extension permissions";
        $fixes[] = "Add proper access/modify permissions for extension routes";
    }
    
    // Check for wrong route patterns
    foreach ($extension_paths as $path) {
        if (strpos($path['path'], 'extension/meschain_sync/module/') === 0) {
            $wrong_route = $path['path'];
            $correct_route = str_replace('extension/meschain_sync/module/', 'extension/module/', $wrong_route);
            $issues[] = "Wrong route pattern: {$wrong_route} should be {$correct_route}";
            $fixes[] = "Update oc_extension_path table with correct route: {$correct_route}";
        }
    }
    
    echo "<h3>🚨 Issues Detected:</h3>\n";
    if (empty($issues)) {
        echo "<p style='color: green;'>✅ No obvious issues detected!</p>\n";
    } else {
        echo "<ol>\n";
        foreach ($issues as $issue) {
            echo "<li style='color: red;'>{$issue}</li>\n";
        }
        echo "</ol>\n";
    }
    
    echo "<h3>🔧 Recommended Fixes:</h3>\n";
    if (empty($fixes)) {
        echo "<p style='color: green;'>✅ No fixes needed!</p>\n";
    } else {
        echo "<ol>\n";
        foreach ($fixes as $fix) {
            echo "<li style='color: blue;'>{$fix}</li>\n";
        }
        echo "</ol>\n";
    }
    
    echo "</div>\n";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>\n";
}
?>