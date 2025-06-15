<?php
/**
 * db_mysql.php
 *
 * Amaç: OpenCart modülünde MySQL veritabanı işlemlerini yöneten yardımcı fonksiyonları içerir.
 *
 * Loglama: Tüm veritabanı işlemleri ve hatalar helper_log_example.log dosyasına kaydedilmelidir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 */
require_once __DIR__ . '/../config.php';
function getDb() {
    try {
        return new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
    } catch (PDOException $e) {
        logDbError('DB_BAGLANTI_HATASI', $e->getMessage());
        return null;
    }
}
function insertOrder($data) {
    $db = getDb();
    if (!$db) return false;
    try {
        $stmt = $db->prepare('INSERT INTO mpm_orders (user, product, total, created_at) VALUES (?, ?, ?, NOW())');
        return $stmt->execute([$data['user'], $data['product'], $data['total']]);
    } catch (PDOException $e) {
        logDbError('INSERT_HATA', $e->getMessage());
        return false;
    }
}
function getOrders($user) {
    $db = getDb();
    if (!$db) return [];
    try {
        $stmt = $db->prepare('SELECT * FROM mpm_orders WHERE user = ? ORDER BY created_at DESC');
        $stmt->execute([$user]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logDbError('SELECT_HATA', $e->getMessage());
        return [];
    }
}
function updateOrder($orderId, $data) {
    $db = getDb();
    if (!$db) return false;
    try {
        $stmt = $db->prepare('UPDATE mpm_orders SET product = ?, total = ? WHERE id = ?');
        return $stmt->execute([$data['product'], $data['total'], $orderId]);
    } catch (PDOException $e) {
        logDbError('UPDATE_HATA', $e->getMessage());
        return false;
    }
}
function deleteOrder($orderId) {
    $db = getDb();
    if (!$db) return false;
    try {
        $stmt = $db->prepare('DELETE FROM mpm_orders WHERE id = ?');
        return $stmt->execute([$orderId]);
    } catch (PDOException $e) {
        logDbError('DELETE_HATA', $e->getMessage());
        return false;
    }
}
function logDbError($islem, $mesaj) {
    $log = sprintf("[%s] [admin] [%s] %s\n", date('Y-m-d H:i:s'), $islem, $mesaj);
    file_put_contents(__DIR__.'/helper_log_example.log', $log, FILE_APPEND);
} 