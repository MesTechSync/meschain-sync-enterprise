<?php
/**
 * db_oracle.php
 *
 * Amaç: OpenCart modülünde Oracle veritabanı işlemlerini yöneten yardımcı fonksiyonları içerir.
 *
 * Loglama: Tüm veritabanı işlemleri ve hatalar helper_log_example.log dosyasına kaydedilmelidir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 */
require_once __DIR__ . '/../config.php';
function getDbOracle() {
    try {
        return new PDO(DB_ORACLE_DSN, DB_USER, DB_PASS);
    } catch (PDOException $e) {
        logDbError('ORACLE_BAGLANTI_HATASI', $e->getMessage());
        return null;
    }
}
function insertOrderOracle($data) {
    $db = getDbOracle();
    if (!$db) return false;
    try {
        $stmt = $db->prepare('INSERT INTO MPM_ORDERS (USER, PRODUCT, TOTAL, CREATED_AT) VALUES (:user, :product, :total, SYSDATE)');
        return $stmt->execute([':user'=>$data['user'], ':product'=>$data['product'], ':total'=>$data['total']]);
    } catch (PDOException $e) {
        logDbError('ORACLE_INSERT_HATA', $e->getMessage());
        return false;
    }
}
function getOrdersOracle($user) {
    $db = getDbOracle();
    if (!$db) return [];
    try {
        $stmt = $db->prepare('SELECT * FROM MPM_ORDERS WHERE USER = :user ORDER BY CREATED_AT DESC');
        $stmt->execute([':user'=>$user]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logDbError('ORACLE_SELECT_HATA', $e->getMessage());
        return [];
    }
}
function updateOrderOracle($orderId, $data) {
    $db = getDbOracle();
    if (!$db) return false;
    try {
        $stmt = $db->prepare('UPDATE MPM_ORDERS SET PRODUCT = :product, TOTAL = :total WHERE ID = :id');
        return $stmt->execute([':product'=>$data['product'], ':total'=>$data['total'], ':id'=>$orderId]);
    } catch (PDOException $e) {
        logDbError('ORACLE_UPDATE_HATA', $e->getMessage());
        return false;
    }
}
function deleteOrderOracle($orderId) {
    $db = getDbOracle();
    if (!$db) return false;
    try {
        $stmt = $db->prepare('DELETE FROM MPM_ORDERS WHERE ID = :id');
        return $stmt->execute([':id'=>$orderId]);
    } catch (PDOException $e) {
        logDbError('ORACLE_DELETE_HATA', $e->getMessage());
        return false;
    }
}
function logDbError($islem, $mesaj) {
    $log = sprintf("[%s] [admin] [%s] %s\n", date('Y-m-d H:i:s'), $islem, $mesaj);
    file_put_contents(__DIR__.'/helper_log_example.log', $log, FILE_APPEND);
} 