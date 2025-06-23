<?php
/**
 * MesChain Extensions Safe Registration
 * Mevcut sistemi bozmadan sadece extension kayıtlarını ekler
 */

// Mevcut SQLite veritabanını kullan
$sqlite_db = 'storage/meschain_sync.sqlite';

if (!file_exists($sqlite_db)) {
    die("SQLite veritabanı bulunamadı: $sqlite_db\n");
}

try {
    $pdo = new PDO('sqlite:' . $sqlite_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✓ SQLite veritabanına bağlanıldı\n";

    // Extensions tablosu varsa kontrol et, yoksa oluştur
    $check_table = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='extensions'");

    if ($check_table->rowCount() == 0) {
        $create_extensions = "
        CREATE TABLE extensions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type VARCHAR(50) NOT NULL,
            code VARCHAR(100) NOT NULL,
            name VARCHAR(200) NOT NULL,
            status INTEGER DEFAULT 0,
            date_added DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(type, code)
        )";

        $pdo->exec($create_extensions);
        echo "✓ Extensions tablosu oluşturuldu\n";
    }

    // MesChain modüllerini kaydet
    $extensions = array(
        array('type' => 'module', 'code' => 'meschain_sync', 'name' => 'MesChain Sync - Ana Modül'),
        array('type' => 'module', 'code' => 'meschain_trendyol', 'name' => 'MesChain Sync - Trendyol'),
        array('type' => 'module', 'code' => 'meschain_n11', 'name' => 'MesChain Sync - N11'),
        array('type' => 'module', 'code' => 'meschain_hepsiburada', 'name' => 'MesChain Sync - Hepsiburada'),
        array('type' => 'module', 'code' => 'meschain_amazon', 'name' => 'MesChain Sync - Amazon'),
        array('type' => 'module', 'code' => 'meschain_ozon', 'name' => 'MesChain Sync - Ozon'),
        array('type' => 'module', 'code' => 'meschain_ebay', 'name' => 'MesChain Sync - eBay')
    );

    $stmt = $pdo->prepare("INSERT OR IGNORE INTO extensions (type, code, name, status) VALUES (?, ?, ?, 1)");

    foreach ($extensions as $ext) {
        $stmt->execute([$ext['type'], $ext['code'], $ext['name']]);
        echo "✓ Extension kaydedildi: " . $ext['name'] . "\n";
    }

    // Extension durumlarını kontrol et
    $check_stmt = $pdo->query("SELECT * FROM extensions WHERE type = 'module' AND code LIKE 'meschain_%'");
    $registered = $check_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\n=== Kayıtlı MesChain Extensions ===\n";
    foreach ($registered as $ext) {
        $status = $ext['status'] ? 'Aktif' : 'Pasif';
        echo "• {$ext['name']} - {$status}\n";
    }

    echo "\n✓ Extension kayıt işlemi tamamlandı!\n";
    echo "Mevcut sistem ve veritabanı korundu.\n";

} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?>