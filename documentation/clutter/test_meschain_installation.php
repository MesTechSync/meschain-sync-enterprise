<?php
/**
 * MesChain-Sync Installation Test
 * OpenCart 4.0 Compatibility Test
 */

// OpenCart paths
$opencart_path = '/Users/mezbjen/Desktop/opencart4_clean';
$meschain_files = [
    'admin/controller/extension/module/meschain_sync.php',
    'admin/model/extension/module/meschain_sync.php',
    'admin/view/template/extension/module/meschain_sync.twig',
    'admin/language/en-gb/extension/module/meschain_sync.php',
    'system/library/meschain/bootstrap.php',
    'system/library/meschain/helper/UtilityHelper.php',
    'system/library/meschain/logger/SystemLogger.php'
];

echo "=== MesChain-Sync Kurulum Doğrulama ===\n\n";

// File existence check
echo "1. Dosya Kontrolü:\n";
foreach ($meschain_files as $file) {
    $full_path = $opencart_path . '/' . $file;
    if (file_exists($full_path)) {
        echo "✓ " . $file . " - OK\n";
    } else {
        echo "✗ " . $file . " - EKSIK\n";
    }
}

// Database connection test
echo "\n2. Veritabanı Kontrolü:\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=opencart4', 'opencart4_user', '1234');
    echo "✓ Veritabanı bağlantısı - OK\n";

    // Check MesChain tables
    $tables = ['oc_meschain_marketplace', 'oc_meschain_product', 'oc_meschain_order', 'oc_meschain_azure_config'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Tablo $table - OK\n";
        } else {
            echo "✗ Tablo $table - EKSIK\n";
        }
    }

    // Check data
    $stmt = $pdo->query("SELECT COUNT(*) FROM oc_meschain_marketplace");
    $count = $stmt->fetchColumn();
    echo "✓ Marketplace verisi: $count kayıt - " . ($count > 0 ? "OK" : "EKSIK") . "\n";

} catch (PDOException $e) {
    echo "✗ Veritabanı bağlantısı - HATA: " . $e->getMessage() . "\n";
}

// Extension registration check
echo "\n3. Extension Kaydı:\n";
try {
    $stmt = $pdo->query("SELECT * FROM oc_extension WHERE type='module' AND code='meschain_sync'");
    if ($stmt->rowCount() > 0) {
        echo "✓ MesChain extension kaydı - OK\n";
    } else {
        echo "✗ MesChain extension kaydı - EKSIK\n";
    }
} catch (PDOException $e) {
    echo "✗ Extension kontrolü - HATA: " . $e->getMessage() . "\n";
}

// Bootstrap test
echo "\n4. Bootstrap Kontrolü:\n";
$bootstrap_path = $opencart_path . '/system/library/meschain/bootstrap.php';
if (file_exists($bootstrap_path)) {
    require_once $bootstrap_path;
    echo "✓ MesChain Bootstrap yüklemesi - OK\n";
} else {
    echo "✗ MesChain Bootstrap - EKSIK\n";
}

echo "\n=== Kurulum Doğrulama Tamamlandı ===\n";
echo "✓ Yeşil işaretler başarılı kurulumu gösterir\n";
echo "✗ Kırmızı işaretler eksik veya hatalı kurulumu gösterir\n\n";

// Admin panel access info
echo "Admin Panel Erişim:\n";
echo "URL: http://localhost:8080/admin/\n";
echo "Modül Yolu: Extensions > Modules > MesChain Sync\n";
?>
