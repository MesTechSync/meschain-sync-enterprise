<?php
/*
 * OpenCart User Permissions Debug Tool
 * Kullanıcı izinlerini kontrol eder ve eksik route'ları tespit eder
 */

// OpenCart config dosyasını dahil et
require_once('config.php');

try {
    // Veritabanı bağlantısı
    $mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    
    if ($mysqli->connect_error) {
        die("Veritabanı bağlantısı hatası: " . $mysqli->connect_error);
    }
    
    echo "<h2>🔍 User Permissions Debug Report</h2>";
    echo "<h3>Tarih: " . date('Y-m-d H:i:s') . "</h3><hr>";
    
    // 1. Admin kullanıcılarını listele
    echo "<h3>📋 1. Admin Kullanıcıları</h3>";
    $query = "SELECT user_id, username, firstname, lastname, user_group_id, status 
              FROM " . DB_PREFIX . "user 
              WHERE status = 1 
              ORDER BY user_id";
    $result = $mysqli->query($query);
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>User ID</th><th>Username</th><th>Ad Soyad</th><th>User Group ID</th><th>Status</th>";
        echo "</tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['user_id']}</td>";
            echo "<td><strong>{$row['username']}</strong></td>";
            echo "<td>{$row['firstname']} {$row['lastname']}</td>";
            echo "<td>{$row['user_group_id']}</td>";
            echo "<td>" . ($row['status'] ? '✅ Aktif' : '❌ Pasif') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Admin kullanıcısı bulunamadı!</p>";
    }
    
    // 2. User Group'ları listele
    echo "<h3>👥 2. User Group'lar</h3>";
    $query = "SELECT user_group_id, name 
              FROM " . DB_PREFIX . "user_group 
              ORDER BY user_group_id";
    $result = $mysqli->query($query);
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Group ID</th><th>Group Name</th>";
        echo "</tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['user_group_id']}</td>";
            echo "<td><strong>{$row['name']}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Her user group için permissions kontrol et
    echo "<h3>🔐 3. User Group Permissions</h3>";
    $query = "SELECT user_group_id, name 
              FROM " . DB_PREFIX . "user_group 
              ORDER BY user_group_id";
    $result = $mysqli->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($group = $result->fetch_assoc()) {
            echo "<h4>📂 Group: {$group['name']} (ID: {$group['user_group_id']})</h4>";
            
            // Access permissions
            $access_query = "SELECT route 
                           FROM " . DB_PREFIX . "user_group_permission 
                           WHERE user_group_id = {$group['user_group_id']} 
                           AND type = 'access' 
                           ORDER BY route";
            $access_result = $mysqli->query($access_query);
            
            echo "<strong>🚪 Access Permissions:</strong><br>";
            if ($access_result && $access_result->num_rows > 0) {
                $access_routes = [];
                while ($route = $access_result->fetch_assoc()) {
                    $access_routes[] = $route['route'];
                }
                echo "<div style='margin-left: 20px; font-family: monospace;'>";
                foreach ($access_routes as $route) {
                    $highlight = (strpos($route, 'user') !== false) ? "style='background-color: yellow;'" : "";
                    echo "<span {$highlight}>• {$route}</span><br>";
                }
                echo "</div>";
                
                // user/user_permission route'u kontrol et
                if (in_array('user/user_permission', $access_routes)) {
                    echo "<p style='color: green;'>✅ user/user_permission route access izni mevcut</p>";
                } else {
                    echo "<p style='color: red;'>❌ user/user_permission route access izni EKSİK!</p>";
                }
            } else {
                echo "<p style='color: red; margin-left: 20px;'>❌ Access permission bulunamadı!</p>";
            }
            
            // Modify permissions
            $modify_query = "SELECT route 
                           FROM " . DB_PREFIX . "user_group_permission 
                           WHERE user_group_id = {$group['user_group_id']} 
                           AND type = 'modify' 
                           ORDER BY route";
            $modify_result = $mysqli->query($modify_query);
            
            echo "<br><strong>✏️ Modify Permissions:</strong><br>";
            if ($modify_result && $modify_result->num_rows > 0) {
                $modify_routes = [];
                while ($route = $modify_result->fetch_assoc()) {
                    $modify_routes[] = $route['route'];
                }
                echo "<div style='margin-left: 20px; font-family: monospace;'>";
                foreach ($modify_routes as $route) {
                    $highlight = (strpos($route, 'user') !== false) ? "style='background-color: yellow;'" : "";
                    echo "<span {$highlight}>• {$route}</span><br>";
                }
                echo "</div>";
                
                // user/user_permission route'u kontrol et
                if (in_array('user/user_permission', $modify_routes)) {
                    echo "<p style='color: green;'>✅ user/user_permission route modify izni mevcut</p>";
                } else {
                    echo "<p style='color: red;'>❌ user/user_permission route modify izni EKSİK!</p>";
                }
            } else {
                echo "<p style='color: red; margin-left: 20px;'>❌ Modify permission bulunamadı!</p>";
            }
            
            echo "<hr>";
        }
    }
    
    // 4. user/user_permission controller dosyasının varlığını kontrol et
    echo "<h3>📁 4. Controller Dosya Kontrolü</h3>";
    $controller_paths = [
        'admin/controller/user/user_permission.php',
        'admin/controller/user/permission.php',
        'admin/controller/system/user_permission.php'
    ];
    
    foreach ($controller_paths as $path) {
        if (file_exists($path)) {
            echo "<p style='color: green;'>✅ Controller bulundu: {$path}</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Controller bulunamadı: {$path}</p>";
        }
    }
    
    // 5. Öneriler
    echo "<h3>💡 5. Çözüm Önerileri</h3>";
    echo "<div style='background-color: #f9f9f9; padding: 15px; border-left: 5px solid #007bff;'>";
    echo "<strong>Eksik izinleri eklemek için:</strong><br>";
    echo "<code>INSERT INTO " . DB_PREFIX . "user_group_permission (user_group_id, type, route) VALUES (1, 'access', 'user/user_permission');</code><br>";
    echo "<code>INSERT INTO " . DB_PREFIX . "user_group_permission (user_group_id, type, route) VALUES (1, 'modify', 'user/user_permission');</code><br><br>";
    echo "<em>Not: user_group_id'yi kullandığınız grup ID'siyle değiştirin.</em>";
    echo "</div>";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Hata: " . $e->getMessage() . "</p>";
}
?>
