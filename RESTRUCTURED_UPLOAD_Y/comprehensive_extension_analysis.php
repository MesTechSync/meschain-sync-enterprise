<?php
/**
 * OpenCart Kapsamlı Eklenti Analizi
 * Tüm eklentilerin gerçek durumunu analiz eder
 */

// Database connection
$db = new mysqli('localhost', 'root', '1234', 'opencart_new', 3306);
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
}

// Database prefix
define('DB_PREFIX', 'oc_');
    
    echo "🔍 OPENCART EKLENTİ DURUMU KAPSAMLI ANALİZİ\n";
    echo "=" . str_repeat("=", 70) . "\n\n";
    
    // 1. VERITABANI KAYITLARI ANALİZİ
    echo "📊 1. VERİTABANI KAYITLARI\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    // oc_extension tablosu analizi
    $extension_query = "SELECT * FROM " . DB_PREFIX . "extension ORDER BY type, code";
    $extensions_result = $db->query($extension_query);
    
    $extensions_by_type = [];
    $total_extensions = 0;
    
    if ($extensions_result && $extensions_result->num_rows > 0) {
        while ($row = $extensions_result->fetch_assoc()) {
            $extensions_by_type[$row['type']][] = $row;
            $total_extensions++;
        }
    }
    
    echo "📈 Toplam Extension Kaydı: $total_extensions\n\n";
    
    foreach ($extensions_by_type as $type => $extensions) {
        echo "🏷️  $type (" . count($extensions) . " adet):\n";
        foreach ($extensions as $ext) {
            echo "   ├─ {$ext['code']}\n";
        }
        echo "\n";
    }
    
    // oc_module tablosu analizi
    echo "🔧 2. AKTIF MODÜLLER (oc_module)\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    $module_query = "SELECT * FROM " . DB_PREFIX . "module ORDER BY code";
    $modules_result = $db->query($module_query);
    
    $active_modules = [];
    if ($modules_result && $modules_result->num_rows > 0) {
        while ($row = $modules_result->fetch_assoc()) {
            $active_modules[] = $row;
            $settings = json_decode($row['setting'], true);
            $status = isset($settings['status']) ? ($settings['status'] ? '✅ Aktif' : '❌ Pasif') : '❓ Belirsiz';
            echo "   ├─ {$row['code']} (ID: {$row['module_id']}) - $status\n";
        }
    } else {
        echo "   ❌ Aktif modül bulunamadı\n";
    }
    echo "\n";
    
    // 3. FİZİKSEL DOSYA KONTROLÜ
    echo "📁 3. FİZİKSEL DOSYA KONTROLÜ\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    $admin_paths = [
        'opencart_new/admin/controller/extension/',
        'opencart_new/extension/',
        'upload/admin/controller/extension/'
    ];
    
    $found_controllers = [];
    
    foreach ($admin_paths as $path) {
        if (is_dir($path)) {
            echo "🔍 Kontrol ediliyor: $path\n";
            $scan_result = scanExtensionDirectory($path, '');
            $found_controllers = array_merge($found_controllers, $scan_result);
        }
    }
    
    echo "\n📋 Bulunan Controller Dosyaları:\n";
    foreach ($found_controllers as $controller) {
        echo "   ├─ $controller\n";
    }
    
    // 4. TRENDYOL ÖZELİNDE DETAY ANALİZ
    echo "\n🎯 4. TRENDYOL EKLENTİLERİ DETAY ANALİZ\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    $trendyol_extensions = [];
    
    // Database'de Trendyol ile ilgili kayıtları ara
    $trendyol_db_query = "SELECT * FROM " . DB_PREFIX . "extension WHERE code LIKE '%trendyol%' OR code LIKE '%meschain%'";
    $trendyol_result = $db->query($trendyol_db_query);
    
    if ($trendyol_result && $trendyol_result->num_rows > 0) {
        echo "📊 Database'deki Trendyol/MesChain kayıtları:\n";
        while ($row = $trendyol_result->fetch_assoc()) {
            echo "   ├─ {$row['type']}/{$row['code']}\n";
            $trendyol_extensions[] = $row;
        }
    } else {
        echo "❌ Database'de Trendyol/MesChain kaydı bulunamadı\n";
    }
    
    // Trendyol dosya kontrolü
    $trendyol_files = [
        'upload/admin/controller/extension/meschain/trendyol.php',
        'upload/admin/controller/extension/module/meschain_trendyol.php',
        'opencart_new/admin/controller/extension/meschain/trendyol.php',
        'opencart_new/admin/controller/extension/module/meschain_trendyol.php',
        'upload/admin/model/extension/meschain/trendyol.php',
        'upload/admin/view/template/extension/meschain/trendyol.twig'
    ];
    
    echo "\n📁 Trendyol Dosya Kontrolü:\n";
    foreach ($trendyol_files as $file) {
        $exists = file_exists($file) ? '✅' : '❌';
        echo "   $exists $file\n";
    }
    
    // 5. ADMIN PANEL ERİŞİM TEST
    echo "\n🔐 5. ADMIN PANEL ERİŞİM TESTİ\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    $test_urls = [
        'http://localhost:8000/admin/index.php?route=marketplace/extension&type=module',
        'http://localhost:8000/admin/index.php?route=extension/meschain/trendyol',
        'http://localhost:8000/admin/index.php?route=extension/module/meschain_trendyol',
        'http://localhost:8000/admin/index.php?route=extension/module/meschain_sync'
    ];
    
    foreach ($test_urls as $url) {
        $response = testUrlAccess($url);
        echo "🌐 $url\n";
        echo "   └─ $response\n\n";
    }
    
    // 6. EKSIK/BOZUK EKLENTİLER
    echo "⚠️  6. PROBLEM TESPİTİ\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    $problems = [];
    
    // Database'de olup dosyası olmayan eklentiler
    foreach ($extensions_by_type as $type => $extensions) {
        foreach ($extensions as $ext) {
            $controller_paths = [
                "opencart_new/admin/controller/extension/{$type}/{$ext['code']}.php",
                "opencart_new/admin/controller/extension/{$ext['code']}.php",
                "upload/admin/controller/extension/{$type}/{$ext['code']}.php"
            ];
            
            $found = false;
            foreach ($controller_paths as $path) {
                if (file_exists($path)) {
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $problems[] = "❌ {$ext['type']}/{$ext['code']} - Controller dosyası bulunamadı";
            }
        }
    }
    
    // Dosyası olup database'de olmayan eklentiler
    foreach ($found_controllers as $controller) {
        $parts = explode('/', $controller);
        $filename = end($parts);
        $code = str_replace('.php', '', $filename);
        
        $found_in_db = false;
        foreach ($extensions_by_type as $type => $extensions) {
            foreach ($extensions as $ext) {
                if ($ext['code'] === $code) {
                    $found_in_db = true;
                    break 2;
                }
            }
        }
        
        if (!$found_in_db) {
            $problems[] = "⚠️  $controller - Database kaydı bulunamadı";
        }
    }
    
    if (empty($problems)) {
        echo "✅ Problem tespit edilmedi\n";
    } else {
        foreach ($problems as $problem) {
            echo "$problem\n";
        }
    }
    
    // 7. ÖNERİLER
    echo "\n💡 7. ÖNERİLER VE ÇÖZÜMLER\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    echo "🔧 Admin panelde sadece 'meschain sync' görünüyorsa:\n";
    echo "   1. Extension kayıtlarını yeniden oluştur\n";
    echo "   2. Cache'i temizle\n";
    echo "   3. Trendyol eklentisini manuel olarak kaydet\n";
    echo "   4. Admin kullanıcı yetkilerini kontrol et\n\n";
    
    echo "🎯 Trendyol eklentilerini aktif hale getirmek için:\n";
    echo "   1. Database'e extension kaydı ekle\n";
    echo "   2. Controller ve model dosyalarını doğru konuma kopyala\n";
    echo "   3. Admin menüsüne route ekle\n";
    echo "   4. Language dosyalarını kontrol et\n\n";
    
    $db->close();

/**
 * Extension directory tarama fonksiyonu
 */
function scanExtensionDirectory($dir, $prefix = '') {
    $controllers = [];
    
    if (!is_dir($dir)) {
        return $controllers;
    }
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $path = $dir . $item;
        $full_prefix = $prefix ? $prefix . '/' . $item : $item;
        
        if (is_dir($path)) {
            $sub_controllers = scanExtensionDirectory($path . '/', $full_prefix);
            $controllers = array_merge($controllers, $sub_controllers);
        } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'php') {
            $controllers[] = $dir . $item;
        }
    }
    
    return $controllers;
}

/**
 * URL erişim test fonksiyonu
 */
function testUrlAccess($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: OpenCart Extension Analyzer'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return "❌ Curl Error: $error";
    }
    
    switch ($http_code) {
        case 200:
            return "✅ Erişilebilir (HTTP 200)";
        case 404:
            return "❌ Sayfa bulunamadı (HTTP 404)";
        case 403:
            return "⚠️  Erişim reddedildi (HTTP 403)";
        case 500:
            return "💥 Server hatası (HTTP 500)";
        default:
            return "❓ HTTP $http_code";
    }
}

echo "\n📋 RAPOR TAMAMLANDI!\n";
echo "Detaylı analiz için yukarıdaki bilgileri inceleyin.\n\n";
?>