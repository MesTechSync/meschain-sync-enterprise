<?php
/**
 * Ozon Module Permission Fix Script
 * Bu script Ozon modülü için OpenCart veritabanında gerekli permission'ları ekler
 */

// OpenCart konfigürasyon dosyasını dahil et
if (file_exists('config.php')) {
    require_once('config.php');
} else {
    die('config.php dosyası bulunamadı. Bu dosyayı OpenCart ana dizininde çalıştırın.');
}

// Veritabanı bağlantısı oluştur
try {
    $mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
    
    if ($mysqli->connect_error) {
        die('Veritabanı bağlantısı başarısız: ' . $mysqli->connect_error);
    }
    
    echo "<h2>Ozon Modülü Permission Düzeltme Script'i</h2>";
    echo "<hr>";
    
    // Admin kullanıcı grubunu bul
    $admin_group_result = $mysqli->query("SELECT user_group_id FROM `" . DB_PREFIX . "user_group` WHERE name = 'Administrator' LIMIT 1");
    
    if ($admin_group_result && $admin_group_result->num_rows > 0) {
        $admin_group = $admin_group_result->fetch_assoc();
        $admin_group_id = $admin_group['user_group_id'];
        echo "<p>✓ Admin grup ID bulundu: " . $admin_group_id . "</p>";
    } else {
        $admin_group_id = 1; // Varsayılan admin grup ID
        echo "<p>⚠ Admin grup bulunamadı, varsayılan ID (1) kullanılıyor.</p>";
    }
    
    // Permission'ları ekle
    $permissions = [
        ['extension/module/ozon', 'access'],
        ['extension/module/ozon', 'modify'],
        ['extension/module/ozon/dashboard', 'access'],
        ['extension/module/ozon/dashboard', 'modify'],
        ['extension/module/ozon/test_connection', 'access'],
        ['extension/module/ozon/test_connection', 'modify'],
        ['extension/module/ozon/get_orders', 'access'],
        ['extension/module/ozon/get_orders', 'modify'],
        ['extension/module/ozon/sync_products', 'access'],
        ['extension/module/ozon/sync_products', 'modify'],
        ['extension/module/ozon/update_stock', 'access'],
        ['extension/module/ozon/update_stock', 'modify']
    ];
    
    $success_count = 0;
    $total_count = count($permissions);
    
    foreach ($permissions as $permission) {
        $route = $permission[0];
        $type = $permission[1];
        
        // Permission'ın zaten var olup olmadığını kontrol et
        $check_query = "SELECT * FROM `" . DB_PREFIX . "user_group_permission` 
                       WHERE user_group_id = ? AND permission = ? AND type = ?";
        $check_stmt = $mysqli->prepare($check_query);
        $check_stmt->bind_param("iss", $admin_group_id, $route, $type);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            // Permission yoksa ekle
            $insert_query = "INSERT INTO `" . DB_PREFIX . "user_group_permission` 
                           (user_group_id, permission, type) VALUES (?, ?, ?)";
            $insert_stmt = $mysqli->prepare($insert_query);
            $insert_stmt->bind_param("iss", $admin_group_id, $route, $type);
            
            if ($insert_stmt->execute()) {
                echo "<p>✓ Permission eklendi: {$route} ({$type})</p>";
                $success_count++;
            } else {
                echo "<p>✗ Permission eklenemedi: {$route} ({$type}) - Hata: " . $mysqli->error . "</p>";
            }
        } else {
            echo "<p>- Permission zaten mevcut: {$route} ({$type})</p>";
            $success_count++;
        }
    }
    
    // Extension tablosuna modülü ekle
    $extension_check = $mysqli->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE type = 'module' AND code = 'ozon'");
    
    if ($extension_check->num_rows == 0) {
        $extension_insert = $mysqli->query("INSERT INTO `" . DB_PREFIX . "extension` (type, code) VALUES ('module', 'ozon')");
        if ($extension_insert) {
            echo "<p>✓ Ozon modülü extension tablosuna eklendi.</p>";
        } else {
            echo "<p>✗ Ozon modülü extension tablosuna eklenemedi: " . $mysqli->error . "</p>";
        }
    } else {
        echo "<p>- Ozon modülü zaten extension tablosunda mevcut.</p>";
    }
    
    echo "<hr>";
    echo "<h3>Sonuç</h3>";
    echo "<p><strong>{$success_count}/{$total_count} permission başarıyla işlendi.</strong></p>";
    
    if ($success_count == $total_count) {
        echo "<p style='color: green;'>✓ Tüm permission'lar başarıyla eklendi!</p>";
        echo "<p>Artık admin panelinde Ozon modülüne erişebilirsiniz.</p>";
        echo "<p><a href='admin/index.php?route=extension/module/ozon'>Ozon Modülüne Git</a></p>";
    } else {
        echo "<p style='color: orange;'>⚠ Bazı permission'lar eklenemedi veya zaten mevcuttu.</p>";
    }
    
    // Cache temizleme önerisi
    echo "<hr>";
    echo "<h3>Önemli Not</h3>";
    echo "<p>Permission değişikliklerinin etkili olması için:</p>";
    echo "<ul>";
    echo "<li>Admin panelinden çıkış yapıp tekrar giriş yapın</li>";
    echo "<li>Mümkünse OpenCart cache'ini temizleyin</li>";
    echo "<li>Tarayıcı cache'ini temizleyin</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Hata: " . $e->getMessage() . "</p>";
} finally {
    if (isset($mysqli)) {
        $mysqli->close();
    }
}
?> 