<?php
/**
 * trendyol_helper.php
 *
 * Amaç: Trendyol API işlemleri ve yardımcı fonksiyonlar.
 *
 * Loglama: Tüm önemli işlemler ve hatalar trendyol_helper.log dosyasına kaydedilir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 *
 * Geliştirici: Her fonksiyon başında açıklama ve log şablonu bulunmalıdır.
 */
// Trendyol'a ürün gönderme, sipariş çekme gibi fonksiyonlar buraya yazılır

/**
 * Trendyol API'ye bağlantı kurar
 * @return bool Başarı durumu
 */
function trendyol_baglan($api_key, $api_secret) {
    // Bağlantı kodu örneği
    $result = false;
    try {
        // API bağlantı testi (örnek)
        $result = true; // Gerçek bağlantı kodu burada olacak
        trendyol_logla('SISTEM', 'BAGLAN', 'Trendyol API bağlantısı başarılı.');
    } catch (Exception $e) {
        trendyol_logla('SISTEM', 'HATA', 'Bağlantı hatası: ' . $e->getMessage());
    }
    return $result;
}

/**
 * Trendyol'a ürün gönderir
 * @param array $urun Ürün verisi
 * @return bool
 */
function trendyol_urun_gonder($urun) {
    try {
        // Ürün gönderme kodu (örnek)
        trendyol_logla('SISTEM', 'URUN_GONDER', 'Ürün gönderildi: ' . json_encode($urun));
        return true;
    } catch (Exception $e) {
        trendyol_logla('SISTEM', 'HATA', 'Ürün gönderme hatası: ' . $e->getMessage());
        return false;
    }
}

/**
 * Trendyol'dan sipariş çeker
 * @param string $tarih_araligi
 * @return array
 */
function trendyol_siparis_cek($tarih_araligi) {
    try {
        // Sipariş çekme kodu (örnek)
        $siparisler = array();
        trendyol_logla('SISTEM', 'SIPARIS_CEK', 'Siparişler çekildi: ' . $tarih_araligi);
        return $siparisler;
    } catch (Exception $e) {
        trendyol_logla('SISTEM', 'HATA', 'Sipariş çekme hatası: ' . $e->getMessage());
        return array();
    }
}

/**
 * Loglama fonksiyonu
 */
function trendyol_logla($user, $action, $message) {
    $log_file = DIR_LOGS . 'trendyol_helper.log';
    $date = date('Y-m-d H:i:s');
    $log = "[$date] [$user] [$action] $message\n";
    file_put_contents($log_file, $log, FILE_APPEND);
}

// ... Yardımcı fonksiyonlar buraya eklenecek ... 

class TrendyolHelper {
    /**
     * Trendyol API'den siparişleri çeker (gerçek API, parametre destekli)
     * @param array $settings (api_key, api_secret, supplier_id, endpoint)
     * @param array $params (opsiyonel, ör: status, size)
     * @return array|false
     */
    public static function getOrders($settings, $params = []) {
        $url = rtrim($settings['endpoint'], '/') . '/suppliers/' . $settings['supplier_id'] . '/orders';
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        $headers = [
            'Authorization: Basic ' . base64_encode($settings['api_key'] . ':' . $settings['api_secret']),
            'Content-Type: application/json'
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            self::writeLog('system', 'API_ERROR', 'Sipariş çekme cURL hatası: ' . curl_error($ch) . ' | Parametreler: ' . json_encode($params));
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        if ($httpcode !== 200) {
            self::writeLog('system', 'API_ERROR', 'Sipariş çekme HTTP hata kodu: ' . $httpcode . ' - Yanıt: ' . $response . ' | Parametreler: ' . json_encode($params));
            return false;
        }
        self::writeLog('system', 'API_CALL', 'Siparişler başarıyla çekildi. Parametreler: ' . json_encode($params));
        return json_decode($response, true);
    }
    /**
     * Trendyol API'den ürünleri çeker (gerçek API)
     * @param array $settings (api_key, api_secret, supplier_id, endpoint)
     * @return array|false
     */
    public static function getProducts($settings) {
        $url = rtrim($settings['endpoint'], '/') . '/suppliers/' . $settings['supplier_id'] . '/products';
        $headers = [
            'Authorization: Basic ' . base64_encode($settings['api_key'] . ':' . $settings['api_secret']),
            'Content-Type: application/json'
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            self::writeLog('system', 'API_ERROR', 'Ürün çekme cURL hatası: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        if ($httpcode !== 200) {
            self::writeLog('system', 'API_ERROR', 'Ürün çekme HTTP hata kodu: ' . $httpcode . ' - Yanıt: ' . $response);
            return false;
        }
        self::writeLog('system', 'API_CALL', 'Ürünler başarıyla çekildi.');
        return json_decode($response, true);
    }
    /**
     * Atomik log fonksiyonu
     * @param string $user
     * @param string $action
     * @param string $message
     */
    public static function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'trendyol_helper.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 