<?php
/**
 * OpenCart Extension Analysis - Simplified Version
 * Tüm eklentilerin gerçek durumunu analiz eder
 */

echo "🔍 OPENCART EKLENTİ DURUMU ANALİZİ\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Database connection
$db = new mysqli('localhost', 'root', '1234', 'opencart_new', 3306);
if ($db->connect_error) {
    die("❌ Database bağlantısı başarısız: " . $db->connect_error . "\n");
}

echo "✅ Database bağlantısı başarılı\n\n";

// 1. oc_extension tablosu analizi
echo "📊 1. VERİTABANI EXTENSION KAYITLARI\n";
echo "-" . str_repeat("-", 40) . "\n";

$extension_query = "SELECT * FROM oc_extension ORDER BY type, code";
$extensions_result = $db->query($extension_query);

$extensions_by_type = [];
$total_extensions = 0;

if ($extensions_result && $extensions_result->num_rows > 0) {
    while ($row = $extensions_result->fetch_assoc()) {
        $extensions_by_type[$row['type']][] = $row;
        $total_extensions++;
    }
    
    echo "📈 Toplam Extension Kaydı: $total_extensions\n\n";
    
    foreach ($extensions_by_type as $type => $extensions) {
        echo "🏷️  " . strtoupper($type) . " (" . count($extensions) . " adet):\n";
        foreach ($extensions as $ext) {
            echo "   ├─ {$ext['code']}\n";
        }
        echo "\n";
    }
} else {
    echo "❌ Hiç extension kaydı bulunamadı!\n\n";
}

// 2. oc_module tablosu analizi
echo "🔧 2. AKTIF MODÜLLER (oc_module)\n";
echo "-" . str_repeat("-", 40) . "\n";

$module_query = "SELECT * FROM oc_module ORDER BY code";
$modules_result = $db->query($module_query);

if ($modules_result && $modules_result->num_rows > 0) {
    echo "📋 Toplam Modül: " . $modules_result->num_rows . "\n\n";
    while ($row = $modules_result->fetch_assoc()) {
        $settings = json_decode($row['setting'], true);
        $status = isset($settings['status']) ? ($settings['status'] ? '✅ Aktif' : '❌ Pasif') : '❓ Belirsiz';
        echo "   ├─ {$row['code']} (ID: {$row['module_id']}) - $status\n";
        
        // Modül ayarlarını göster
        if (isset($settings['name'])) {
            echo "      └─ İsim: {$settings['name']}\n";
        }
    }
} else {
    echo "❌ Hiç aktif modül bulunamadı\n";
}
echo "\n";

// 3. Trendyol/MesChain özel analiz
echo "🎯 3. TRENDYOL/MESCHAIN EKLENTİLERİ\n";
echo "-" . str_repeat("-", 40) . "\n";

$trendyol_query = "SELECT * FROM oc_extension WHERE code LIKE '%trendyol%' OR code LIKE '%meschain%'";
$trendyol_result = $db->query($trendyol_query);

if ($trendyol_result && $trendyol_result->num_rows > 0) {
    echo "📊 Database'deki Trendyol/MesChain kayıtları:\n";
    while ($row = $trendyol_result->fetch_assoc()) {
        echo "   ├─ {$row['type']}/{$row['code']}\n";
    }
} else {
    echo "❌ Database'de Trendyol/MesChain kaydı bulunamadı\n";
}
echo "\n";

// 4. Fiziksel dosya kontrolü
echo "📁 4. FİZİKSEL DOSYA KONTROLÜ\n";
echo "-" . str_repeat("-", 40) . "\n";

$controller_paths = [
    'opencart_new/admin/controller/extension/',
    'upload/admin/controller/extension/'
];

$found_controllers = [];

foreach ($controller_paths as $base_path) {
    if (is_dir($base_path)) {
        echo "🔍 Kontrol: $base_path\n";
        $controllers = scanControllers($base_path);
        $found_controllers = array_merge($found_controllers, $controllers);
    }
}

echo "\n📋 Bulunan Controller Dosyaları:\n";
foreach ($found_controllers as $controller) {
    echo "   ├─ $controller\n";
}
echo "\n";

// 5. Özel Trendyol dosya kontrolü
echo "🔍 5. TRENDYOL DOSYA KONTROLÜ\n";
echo "-" . str_repeat("-", 40) . "\n";

$trendyol_files = [
    'upload/admin/controller/extension/meschain/trendyol.php',
    'upload/admin/controller/extension/module/meschain_trendyol.php',
    'opencart_new/admin/controller/extension/meschain/trendyol.php',
    'upload/admin/model/extension/meschain/trendyol.php',
    'upload/admin/view/template/extension/meschain/trendyol.twig',
    'upload/system/library/meschain/api/trendyol_client.php'
];

foreach ($trendyol_files as $file) {
    $exists = file_exists($file) ? '✅' : '❌';
    $size = file_exists($file) ? ' (' . number_format(filesize($file)) . ' bytes)' : '';
    echo "   $exists $file$size\n";
}
echo "\n";

// 6. Database vs Dosya karşılaştırması
echo "⚖️  6. DATABASE vs DOSYA KARŞILAŞTIRMASI\n";
echo "-" . str_repeat("-", 40) . "\n";

$problems = [];

// Database'de olup dosyası olmayan
if (!empty($extensions_by_type)) {
    foreach ($extensions_by_type as $type => $extensions) {
        foreach ($extensions as $ext) {
            $possible_paths = [
                "opencart_new/admin/controller/extension/{$type}/{$ext['code']}.php",
                "opencart_new/admin/controller/extension/{$ext['code']}.php",
                "upload/admin/controller/extension/{$type}/{$ext['code']}.php",
                "upload/admin/controller/extension/module/{$ext['code']}.php"
            ];
            
            $found = false;
            foreach ($possible_paths as $path) {
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
}

// Dosyası olup database'de olmayan
foreach ($found_controllers as $controller) {
    $filename = basename($controller, '.php');
    
    $found_in_db = false;
    if (!empty($extensions_by_type)) {
        foreach ($extensions_by_type as $type => $extensions) {
            foreach ($extensions as $ext) {
                if ($ext['code'] === $filename) {
                    $found_in_db = true;
                    break 2;
                }
            }
        }
    }
    
    if (!$found_in_db) {
        $problems[] = "⚠️  $controller - Database kaydı eksik";
    }
}

if (empty($problems)) {
    echo "✅ Önemli problem tespit edilmedi\n";
} else {
    echo "🔍 Tespit edilen problemler:\n";
    foreach ($problems as $problem) {
        echo "   $problem\n";
    }
}
echo "\n";

// 7. Çözüm önerileri
echo "💡 7. ÇÖZÜM ÖNERİLERİ\n";
echo "-" . str_repeat("-", 40) . "\n";

echo "🔧 Admin panelde sadece 'meschain sync' görünme sorunu:\n";
echo "   1. Extension kayıtlarını database'e manuel ekle\n";
echo "   2. Cache dizinini temizle (storagenew/cache/)\n";
echo "   3. Admin kullanıcı yetkilerini kontrol et\n";
echo "   4. Extension install/uninstall işlemini yap\n\n";

echo "🎯 Trendyol eklentilerini aktif hale getirmek için:\n";
echo "   1. oc_extension tablosuna kayıt ekle:\n";
echo "      INSERT INTO oc_extension (type, code) VALUES ('module', 'meschain_trendyol');\n";
echo "   2. Controller dosyalarını doğru konuma kopyala\n";
echo "   3. Template ve language dosyalarını kontrol et\n";
echo "   4. Admin menüsü için route ekle\n\n";

// Test URL'leri
echo "🌐 TEST URL'LERİ:\n";
echo "   • Modules: http://localhost:8000/admin/index.php?route=marketplace/extension&type=module\n";
echo "   • Trendyol: http://localhost:8000/admin/index.php?route=extension/meschain/trendyol\n";
echo "   • MesChain: http://localhost:8000/admin/index.php?route=extension/module/meschain_trendyol\n\n";

$db->close();

echo "📋 ANALİZ TAMAMLANDI!\n";
echo "Detaylı bilgiler için yukarıdaki raporu inceleyin.\n\n";

/**
 * Controller dosyalarını tarar
 */
function scanControllers($dir, $results = []) {
    if (!is_dir($dir)) {
        return $results;
    }
    
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . $file;
        if (is_dir($path)) {
            $results = scanControllers($path . '/', $results);
        } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $results[] = $path;
        }
    }
    
    return $results;
}
?>