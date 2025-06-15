<?php
/**
 * MesChain-Sync - All Marketplace Permissions Fix Script
 * Bu script tüm pazaryeri modülleri için OpenCart veritabanında gerekli permission'ları ekler
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
    
    echo "<h2>MesChain-Sync - Tüm Pazaryerleri Permission Düzeltme Script'i</h2>";
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
    
    // Tüm pazaryerleri için permission'lar
    $marketplaces = [
        'trendyol' => 'Trendyol',
        'n11' => 'N11',
        'amazon' => 'Amazon',
        'ebay' => 'eBay',
        'hepsiburada' => 'Hepsiburada',
        'ozon' => 'Ozon',
        'pazarama' => 'Pazarama',
        'ciceksepeti' => 'Çiçek Sepeti'
    ];
    
    $total_success = 0;
    $total_permissions = 0;
    
    foreach ($marketplaces as $marketplace => $display_name) {
        echo "<h3>🛒 {$display_name} Pazaryeri</h3>";
        
        $permissions = [
            ["extension/module/{$marketplace}", 'access'],
            ["extension/module/{$marketplace}", 'modify'],
            ["extension/module/{$marketplace}/dashboard", 'access'],
            ["extension/module/{$marketplace}/dashboard", 'modify'],
            ["extension/module/{$marketplace}/test_connection", 'access'],
            ["extension/module/{$marketplace}/test_connection", 'modify'],
            ["extension/module/{$marketplace}/get_orders", 'access'],
            ["extension/module/{$marketplace}/get_orders", 'modify'],
            ["extension/module/{$marketplace}/sync_products", 'access'],
            ["extension/module/{$marketplace}/sync_products", 'modify'],
            ["extension/module/{$marketplace}/update_stock", 'access'],
            ["extension/module/{$marketplace}/update_stock", 'modify']
        ];
        
        $marketplace_success = 0;
        
        foreach ($permissions as $permission) {
            $route = $permission[0];
            $type = $permission[1];
            $total_permissions++;
            
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
                    echo "<p style='margin-left: 20px;'>✓ Permission eklendi: {$route} ({$type})</p>";
                    $marketplace_success++;
                    $total_success++;
                } else {
                    echo "<p style='margin-left: 20px;'>✗ Permission eklenemedi: {$route} ({$type}) - Hata: " . $mysqli->error . "</p>";
                }
            } else {
                echo "<p style='margin-left: 20px;'>- Permission zaten mevcut: {$route} ({$type})</p>";
                $marketplace_success++;
                $total_success++;
            }
        }
        
        // Extension tablosuna modülü ekle
        $extension_check = $mysqli->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE type = 'module' AND code = '{$marketplace}'");
        
        if ($extension_check->num_rows == 0) {
            $extension_insert = $mysqli->query("INSERT INTO `" . DB_PREFIX . "extension` (type, code) VALUES ('module', '{$marketplace}')");
            if ($extension_insert) {
                echo "<p style='margin-left: 20px;'>✓ {$display_name} modülü extension tablosuna eklendi.</p>";
            } else {
                echo "<p style='margin-left: 20px;'>✗ {$display_name} modülü extension tablosuna eklenemedi: " . $mysqli->error . "</p>";
            }
        } else {
            echo "<p style='margin-left: 20px;'>- {$display_name} modülü zaten extension tablosunda mevcut.</p>";
        }
        
        echo "<p style='margin-left: 20px; font-weight: bold;'>{$display_name}: {$marketplace_success}/" . count($permissions) . " permission işlendi</p>";
        echo "<br>";
    }
    
    // MesChain-Sync ana modülü permission'ları
    echo "<h3>⚙️ MesChain-Sync Ana Modülü</h3>";
    $main_permissions = [
        ['extension/module/meschain_sync', 'access'],
        ['extension/module/meschain_sync', 'modify'],
        ['extension/module/help', 'access'],
        ['extension/module/help', 'modify'],
        ['extension/module/log_viewer', 'access'],
        ['extension/module/log_viewer', 'modify'],
        ['extension/module/announcement', 'access'],
        ['extension/module/announcement', 'modify']
    ];
    
    foreach ($main_permissions as $permission) {
        $route = $permission[0];
        $type = $permission[1];
        $total_permissions++;
        
        $check_query = "SELECT * FROM `" . DB_PREFIX . "user_group_permission` 
                       WHERE user_group_id = ? AND permission = ? AND type = ?";
        $check_stmt = $mysqli->prepare($check_query);
        $check_stmt->bind_param("iss", $admin_group_id, $route, $type);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows == 0) {
            $insert_query = "INSERT INTO `" . DB_PREFIX . "user_group_permission` 
                           (user_group_id, permission, type) VALUES (?, ?, ?)";
            $insert_stmt = $mysqli->prepare($insert_query);
            $insert_stmt->bind_param("iss", $admin_group_id, $route, $type);
            
            if ($insert_stmt->execute()) {
                echo "<p style='margin-left: 20px;'>✓ Permission eklendi: {$route} ({$type})</p>";
                $total_success++;
            } else {
                echo "<p style='margin-left: 20px;'>✗ Permission eklenemedi: {$route} ({$type}) - Hata: " . $mysqli->error . "</p>";
            }
        } else {
            echo "<p style='margin-left: 20px;'>- Permission zaten mevcut: {$route} ({$type})</p>";
            $total_success++;
        }
    }
    
    echo "<hr>";
    echo "<h3>📊 Genel Sonuç</h3>";
    echo "<p><strong>Toplam: {$total_success}/{$total_permissions} permission başarıyla işlendi.</strong></p>";
    
    if ($total_success == $total_permissions) {
        echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 Tüm pazaryeri permission'ları başarıyla eklendi!</p>";
        echo "<p style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        echo "<strong>Artık tüm pazaryeri modüllerine erişebilirsiniz:</strong><br>";
        foreach ($marketplaces as $marketplace => $display_name) {
            echo "• <a href='admin/index.php?route=extension/module/{$marketplace}' target='_blank'>{$display_name}</a><br>";
        }
        echo "</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Bazı permission'lar eklenemedi veya zaten mevcuttu.</p>";
    }
    
    // Cache temizleme önerisi
    echo "<hr>";
    echo "<h3>💡 Önemli Notlar</h3>";
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px;'>";
    echo "<p><strong>Permission değişikliklerinin etkili olması için:</strong></p>";
    echo "<ul>";
    echo "<li>Admin panelinden çıkış yapıp tekrar giriş yapın</li>";
    echo "<li>Tarayıcı cache'ini temizleyin (Ctrl+Shift+Delete)</li>";
    echo "<li>Mümkünse OpenCart cache'ini temizleyin</li>";
    echo "<li>Bu script'i silmeyi unutmayın (güvenlik için)</li>";
    echo "</ul>";
    echo "</div>";
    
    // Pazaryeri listesi
    echo "<hr>";
    echo "<h3>🛍️ Desteklenen Pazaryerleri</h3>";
    echo "<div style='background: #e7f3ff; padding: 15px; border: 1px solid #b8daff; border-radius: 5px;'>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;'>";
    foreach ($marketplaces as $marketplace => $display_name) {
        echo "<div style='background: white; padding: 10px; border-radius: 5px; text-align: center;'>";
        echo "<strong>{$display_name}</strong><br>";
        echo "<small>extension/module/{$marketplace}</small>";
        echo "</div>";
    }
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red; background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<strong>Hata:</strong> " . $e->getMessage();
    echo "</p>";
} finally {
    if (isset($mysqli)) {
        $mysqli->close();
    }
}
?> 