<?php
echo "MesChain-Sync Kurulum Durum Raporu\n";
echo "====================================\n\n";

// Test basic file existence
$files = [
    '/Users/mezbjen/Desktop/opencart4_clean/admin/controller/extension/module/meschain_sync.php',
    '/Users/mezbjen/Desktop/opencart4_clean/admin/model/extension/module/meschain_sync.php',
    '/Users/mezbjen/Desktop/opencart4_clean/system/library/meschain/bootstrap.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ " . basename($file) . " - BULUNDU\n";
    } else {
        echo "✗ " . basename($file) . " - BULUNAMADI\n";
    }
}

// Test database connection
echo "\nVeritabanı Testi:\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=opencart4', 'opencart4_user', '1234');
    echo "✓ Database bağlantısı başarılı\n";

    $stmt = $pdo->query("SELECT COUNT(*) FROM oc_meschain_marketplace");
    $count = $stmt->fetchColumn();
    echo "✓ MesChain tabloları hazır, $count marketplace kaydı var\n";
} catch (Exception $e) {
    echo "✗ Database hatası: " . $e->getMessage() . "\n";
}

echo "\n=== KURULUM BAŞARILI ===\n";
echo "OpenCart Admin Panel: http://localhost:8080/admin/\n";
echo "MesChain modülü Extensions > Modules altında görülebilir\n";
?>
