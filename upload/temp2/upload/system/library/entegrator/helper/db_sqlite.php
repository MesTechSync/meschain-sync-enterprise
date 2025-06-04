<?php
/**
 * db_sqlite.php
 *
 * Amaç: OpenCart modülünde SQLite veritabanı işlemlerini yöneten yardımcı fonksiyonları içerir.
 *
 * Loglama: Tüm veritabanı işlemleri ve hatalar helper_log_example.log dosyasına kaydedilmelidir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 */
require_once __DIR__ . '/../config.php';
function getDbSqlite() {
    try {
        return new PDO('sqlite:' . SQLITE_FILE);
    } catch (PDOException $e) {
        logDbError('SQLITE_BAGLANTI_HATASI', $e->getMessage());
        return null;
    }
}
function insertOrderSqlite($data) {
    $db = getDbSqlite();
    if (!$db) return false;
    try {
        $stmt = $db->prepare('INSERT INTO mpm_orders (user, product, total, created_at) VALUES (?, ?, ?, datetime("now"))');
        return $stmt->execute([$data['user'], $data['product'], $data['total']]);
    } catch (PDOException $e) {
        logDbError('SQLITE_INSERT_HATA', $e->getMessage());
        return false;
    }
}
function getOrdersSqlite($user) {
    $db = getDbSqlite();
    if (!$db) return [];
    try {
        $stmt = $db->prepare('SELECT * FROM mpm_orders WHERE user = ? ORDER BY created_at DESC');
        $stmt->execute([$user]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logDbError('SQLITE_SELECT_HATA', $e->getMessage());
        return [];
    }
}
function updateOrderSqlite($orderId, $data) {
    $db = getDbSqlite();
    if (!$db) return false;
    try {
        $stmt = $db->prepare('UPDATE mpm_orders SET product = ?, total = ? WHERE id = ?');
        return $stmt->execute([$data['product'], $data['total'], $orderId]);
    } catch (PDOException $e) {
        logDbError('SQLITE_UPDATE_HATA', $e->getMessage());
        return false;
    }
}
function deleteOrderSqlite($orderId) {
    $db = getDbSqlite();
    if (!$db) return false;
    try {
        $stmt = $db->prepare('DELETE FROM mpm_orders WHERE id = ?');
        return $stmt->execute([$orderId]);
    } catch (PDOException $e) {
        logDbError('SQLITE_DELETE_HATA', $e->getMessage());
        return false;
    }
}
function logDbError($islem, $mesaj) {
    $log = sprintf("[%s] [admin] [%s] %s\n", date('Y-m-d H:i:s'), $islem, $mesaj);
    file_put_contents(__DIR__.'/helper_log_example.log', $log, FILE_APPEND);
} 