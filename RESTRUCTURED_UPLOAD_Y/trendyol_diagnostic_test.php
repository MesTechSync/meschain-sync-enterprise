<?php
/**
 * Trendyol Extension Diagnostic Test
 * Problem kaynağını tespit etmek için detaylı test
 */

echo "🔬 TRENDYOL EKLENTİSİ DİAGNOSTİK TESTİ\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Database connection
$db = new mysqli('localhost', 'root', '1234', 'opencart_new', 3306);
if ($db->connect_error) {
    die("❌ Database bağlantısı başarısız: " . $db->connect_error . "\n");
}

echo "✅ Database bağlantısı başarılı\n\n";

// 1. Database kayıt durumu kontrol
echo "📊 1. DATABASE KAYIT DURUMU\n";
echo "-" . str_repeat("-", 30) . "\n";

$queries = [
    "Meschain Extension Kayıtları" => "SELECT * FROM oc_extension WHERE code LIKE '%meschain%' OR code LIKE '%trendyol%'",
    "Meschain Module Kayıtları" => "SELECT * FROM oc_module WHERE code LIKE '%meschain%' OR code LIKE '%trendyol%'",
    "Tüm Module Extension Kayıtları" => "SELECT * FROM oc_extension WHERE type = 'module'"
];

foreach ($queries as $desc => $query) {
    echo "🔍 $desc:\n";
    $result = $db->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (isset($row['extension_id'])) {
                echo "   ├─ Extension: {$row['type']}/{$row['code']} (ID: {$row['extension_id']})\n";
            }
            if (isset($row['module_id'])) {
                $settings = json_decode($row['setting'], true);
                $status = isset($settings['status']) ? ($settings['status'] ? 'Aktif' : 'Pasif') : 'Belirsiz';
                echo "   ├─ Module: {$row['code']} (ID: {$row['module_id']}) - Status: $status\n";
            }
        }
    } else {
        echo "   ❌ Kayıt bulunamadı\n";
    }
    echo "\n";
}

// 2. Eksik extension kayıtlarını tespit et
echo "🎯 2. EKSİK EXTENSION KAYITLARI TESPİTİ\n";
echo "-" . str_repeat("-", 30) . "\n";

$meschain_extensions = [
    'meschain_trendyol' => 'module',
    'meschain_amazon' => 'module', 
    'meschain_hepsiburada' => 'module',
    'meschain_n11' => 'module',
    'meschain_ebay' => 'module',
    'meschain_gittigidiyor' => 'module',
    'meschain_pazarama' => 'module'
];

$missing_extensions = [];

foreach ($meschain_extensions as $code => $type) {
    $check_query = "SELECT * FROM oc_extension WHERE type = '$type' AND code = '$code'";
    $result = $db->query($check_query);
    
    if (!$result || $result->num_rows == 0) {
        $missing_extensions[] = ['type' => $type, 'code' => $code];
        echo "❌ Eksik: $type/$code\n";
    } else {
        echo "✅ Mevcut: $type/$code\n";
    }
}

echo "\n";

// 3. Dosya erişim testleri
echo "📁 3. DOSYA ERİŞİM TESTLERİ\n";
echo "-" . str_repeat("-", 30) . "\n";

$controller_files = [
    'meschain_trendyol (upload)' => 'upload/admin/controller/extension/module/meschain_trendyol.php',
    'meschain_trendyol (opencart)' => 'opencart_new/admin/controller/extension/module/meschain_trendyol.php',
    'trendyol (upload)' => 'upload/admin/controller/extension/meschain/trendyol.php',
    'trendyol (opencart)' => 'opencart_new/admin/controller/extension/meschain/trendyol.php'
];

foreach ($controller_files as $name => $file) {
    if (file_exists($file)) {
        echo "✅ $name: Mevcut (" . number_format(filesize($file)) . " bytes)\n";
        
        // Dosya içeriğini kısmen kontrol et
        $content = file_get_contents($file);
        if (strpos($content, 'class Controller') !== false) {
            echo "   └─ Class tanımı: ✅\n";
        } else {
            echo "   └─ Class tanımı: ❌\n";
        }
    } else {
        echo "❌ $name: Bulunamadı\n";
    }
}

echo "\n";

// 4. Admin panel erişim simülasyonu
echo "🔐 4. ADMIN PANEL ERİŞİM SİMÜLASYONU\n";
echo "-" . str_repeat("-", 30) . "\n";

// Admin user control
$admin_query = "SELECT * FROM oc_user LIMIT 1";
$admin_result = $db->query($admin_query);

if ($admin_result && $admin_result->num_rows > 0) {
    $admin = $admin_result->fetch_assoc();
    echo "✅ Admin kullanıcı mevcut: {$admin['username']} (ID: {$admin['user_id']})\n";
    
    // User group permissions check
    $permission_query = "SELECT * FROM oc_user_group WHERE user_group_id = {$admin['user_group_id']}";
    $perm_result = $db->query($permission_query);
    
    if ($perm_result && $perm_result->num_rows > 0) {
        $permissions = $perm_result->fetch_assoc();
        $access_perms = json_decode($permissions['permission'], true);
        
        echo "👤 User Group: {$permissions['name']}\n";
        
        // Extension access kontrolü
        $extension_access = false;
        if (isset($access_perms['access'])) {
            foreach ($access_perms['access'] as $perm) {
                if (strpos($perm, 'extension') !== false) {
                    $extension_access = true;
                    break;
                }
            }
        }
        
        echo "🔑 Extension erişim yetkisi: " . ($extension_access ? "✅" : "❌") . "\n";
    }
} else {
    echo "❌ Admin kullanıcı bulunamadı\n";
}

echo "\n";

// 5. Cache durumu kontrol
echo "💾 5. CACHE DURUMU KONTROLÜ\n";
echo "-" . str_repeat("-", 30) . "\n";

$cache_dirs = [
    'storagenew/cache/',
    'opencart_new/system/storage/cache/'
];

foreach ($cache_dirs as $cache_dir) {
    if (is_dir($cache_dir)) {
        $cache_files = glob($cache_dir . "*");
        echo "📂 $cache_dir: " . count($cache_files) . " dosya\n";
        
        // Extension ile ilgili cache dosyalarını ara
        $extension_cache = glob($cache_dir . "*extension*");
        if (!empty($extension_cache)) {
            echo "   └─ Extension cache: " . count($extension_cache) . " dosya\n";
        }
    } else {
        echo "❌ $cache_dir: Dizin bulunamadı\n";
    }
}

echo "\n";

// 6. Çözüm önerileri ve otomatik düzeltme seçenekleri
echo "💡 6. ÇÖZÜM ÖNERİLERİ\n";
echo "-" . str_repeat("-", 30) . "\n";

if (!empty($missing_extensions)) {
    echo "🔧 Eksik Extension Kayıtları Tespit Edildi!\n";
    echo "Otomatik düzeltme için şu SQL komutlarını çalıştırabilirsiniz:\n\n";
    
    foreach ($missing_extensions as $ext) {
        echo "INSERT INTO oc_extension (type, code) VALUES ('{$ext['type']}', '{$ext['code']}');\n";
    }
    echo "\n";
}

echo "📋 Admin panelde extension'ları görmek için:\n";
echo "1. Eksik database kayıtlarını ekle\n";
echo "2. Cache'i temizle\n";  
echo "3. Admin oturumunu yenile\n";
echo "4. Marketplace/Extensions sayfasını kontrol et\n\n";

// 7. Test URL'leri ve direkt erişim
echo "🌐 7. TEST URL'LERİ\n";
echo "-" . str_repeat("-", 30) . "\n";

$test_urls = [
    'Module List' => 'http://localhost:8000/admin/index.php?route=marketplace/extension&type=module',
    'Trendyol (meschain)' => 'http://localhost:8000/admin/index.php?route=extension/meschain/trendyol',
    'Trendyol (module)' => 'http://localhost:8000/admin/index.php?route=extension/module/meschain_trendyol'
];

foreach ($test_urls as $name => $url) {
    echo "🔗 $name:\n";
    echo "   $url\n\n";
}

$db->close();

echo "📊 DİAGNOSTİK TEST TAMAMLANDI!\n";
echo "Yukarıdaki bilgilere göre problemin kaynağı tespit edildi.\n\n";
?>