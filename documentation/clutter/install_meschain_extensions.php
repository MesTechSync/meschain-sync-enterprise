<?php
/**
 * MesChain Sync Extensions Installer
 * OpenCart 3.0.4.0 uyumlu
 */

// OpenCart konfigürasyonunu yükle
require_once('upload/config.php');
require_once('upload/admin/config.php');

// Veritabanı bağlantısı
try {
    $db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    $db->set_charset('utf8');

    if ($db->connect_error) {
        die("Veritabanı bağlantı hatası: " . $db->connect_error);
    }

    echo "✓ Veritabanı bağlantısı başarılı\n";

    // MesChain Sync kategorisini ekle
    $extensions = array(
        array(
            'type' => 'module',
            'code' => 'meschain_sync',
            'name' => 'MesChain Sync - Ana Modül'
        ),
        array(
            'type' => 'module',
            'code' => 'meschain_trendyol',
            'name' => 'MesChain Sync - Trendyol'
        ),
        array(
            'type' => 'module',
            'code' => 'meschain_n11',
            'name' => 'MesChain Sync - N11'
        ),
        array(
            'type' => 'module',
            'code' => 'meschain_hepsiburada',
            'name' => 'MesChain Sync - Hepsiburada'
        ),
        array(
            'type' => 'module',
            'code' => 'meschain_amazon',
            'name' => 'MesChain Sync - Amazon'
        ),
        array(
            'type' => 'module',
            'code' => 'meschain_ozon',
            'name' => 'MesChain Sync - Ozon'
        ),
        array(
            'type' => 'module',
            'code' => 'meschain_ebay',
            'name' => 'MesChain Sync - eBay'
        )
    );

    foreach ($extensions as $extension) {
        // Extension'ı kontrol et
        $check_query = "SELECT extension_id FROM " . DB_PREFIX . "extension WHERE type = '" . $extension['type'] . "' AND code = '" . $extension['code'] . "'";
        $result = $db->query($check_query);

        if ($result->num_rows == 0) {
            // Extension'ı ekle
            $insert_query = "INSERT INTO " . DB_PREFIX . "extension (type, code) VALUES ('" . $extension['type'] . "', '" . $extension['code'] . "')";

            if ($db->query($insert_query)) {
                echo "✓ Extension eklendi: " . $extension['name'] . "\n";
            } else {
                echo "✗ Extension eklenemedi: " . $extension['name'] . " - " . $db->error . "\n";
            }
        } else {
            echo "• Extension zaten mevcut: " . $extension['name'] . "\n";
        }
    }

    // User group permissions ekle
    $user_groups_query = "SELECT user_group_id FROM " . DB_PREFIX . "user_group WHERE name = 'Administrator'";
    $user_groups_result = $db->query($user_groups_query);

    if ($user_groups_result->num_rows > 0) {
        $user_group = $user_groups_result->fetch_assoc();
        $user_group_id = $user_group['user_group_id'];

        $permissions = array(
            'extension/module/meschain_sync',
            'extension/module/meschain_trendyol',
            'extension/module/meschain_n11',
            'extension/module/meschain_hepsiburada',
            'extension/module/meschain_amazon',
            'extension/module/meschain_ozon',
            'extension/module/meschain_ebay'
        );

        foreach ($permissions as $permission) {
            // Access permission
            $check_access = "SELECT * FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . $user_group_id . "' AND permission LIKE '%\"access\":%\"" . $permission . "\"%'";
            $access_result = $db->query($check_access);

            if ($access_result->num_rows == 0) {
                // Mevcut permissions'ı al
                $get_permissions = "SELECT permission FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . $user_group_id . "'";
                $perm_result = $db->query($get_permissions);

                if ($perm_result->num_rows > 0) {
                    $perm_data = $perm_result->fetch_assoc();
                    $current_permissions = json_decode($perm_data['permission'], true);

                    if (!isset($current_permissions['access'])) {
                        $current_permissions['access'] = array();
                    }
                    if (!isset($current_permissions['modify'])) {
                        $current_permissions['modify'] = array();
                    }

                    // Yeni permissions ekle
                    if (!in_array($permission, $current_permissions['access'])) {
                        $current_permissions['access'][] = $permission;
                    }
                    if (!in_array($permission, $current_permissions['modify'])) {
                        $current_permissions['modify'][] = $permission;
                    }

                    // Permissions'ı güncelle
                    $update_permissions = "UPDATE " . DB_PREFIX . "user_group SET permission = '" . json_encode($current_permissions) . "' WHERE user_group_id = '" . $user_group_id . "'";

                    if ($db->query($update_permissions)) {
                        echo "✓ Permission eklendi: " . $permission . "\n";
                    } else {
                        echo "✗ Permission eklenemedi: " . $permission . " - " . $db->error . "\n";
                    }
                }
            } else {
                echo "• Permission zaten mevcut: " . $permission . "\n";
            }
        }
    }

    // MesChain tabloları oluştur
    $tables = array(
        "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_sync_log` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `marketplace` varchar(50) NOT NULL,
            `action` varchar(100) NOT NULL,
            `message` text,
            `status` enum('success','error','warning') NOT NULL DEFAULT 'success',
            `date_added` datetime NOT NULL,
            PRIMARY KEY (`log_id`),
            KEY `marketplace` (`marketplace`),
            KEY `status` (`status`),
            KEY `date_added` (`date_added`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

        "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_product_mapping` (
            `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `marketplace` varchar(50) NOT NULL,
            `marketplace_product_id` varchar(100) NOT NULL,
            `marketplace_sku` varchar(100),
            `last_sync` datetime,
            `status` enum('active','inactive','error') NOT NULL DEFAULT 'active',
            PRIMARY KEY (`mapping_id`),
            UNIQUE KEY `product_marketplace` (`product_id`, `marketplace`),
            KEY `marketplace` (`marketplace`),
            KEY `marketplace_product_id` (`marketplace_product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

        "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_order_mapping` (
            `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `marketplace` varchar(50) NOT NULL,
            `marketplace_order_id` varchar(100) NOT NULL,
            `marketplace_status` varchar(50),
            `last_sync` datetime,
            PRIMARY KEY (`mapping_id`),
            UNIQUE KEY `order_marketplace` (`order_id`, `marketplace`),
            KEY `marketplace` (`marketplace`),
            KEY `marketplace_order_id` (`marketplace_order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
    );

    foreach ($tables as $table_sql) {
        if ($db->query($table_sql)) {
            echo "✓ Tablo oluşturuldu\n";
        } else {
            echo "✗ Tablo oluşturulamadı: " . $db->error . "\n";
        }
    }

    echo "\n=== MesChain Sync Extensions Kurulumu Tamamlandı ===\n";
    echo "Artık OpenCart Admin Panel > Extensions > Modules bölümünden MesChain modüllerini görebilirsiniz.\n";

} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?>