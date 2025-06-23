<?php
/**
 * trendyol_api.php
 *
 * Amaç: Trendyol API entegrasyonu için gerekli fonksiyonları içerir.
 *
 * Loglama: Tüm API çağrıları ve hatalar api_log tablosuna kaydedilir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 */

class TrendyolHelper {
    /**
     * API çağrısı yapar
     * @param array $settings API ayarları
     * @param string $method HTTP metodu (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint
     * @param array $data İstek verisi (opsiyonel)
     * @return array|false
     */
    private static function apiRequest($settings, $method, $endpoint, $data = null) {
        $url = rtrim($settings['endpoint'], '/') . $endpoint;
        $headers = [
            'Authorization: Basic ' . base64_encode($settings['api_key'] . ':' . $settings['api_secret']),
            'Content-Type: application/json',
            'User-Agent: MesChain-Sync/1.0'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($data && in_array($method, ['POST', 'PUT'])) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        }
        
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            self::logApiCall($settings, $method, $endpoint, $data, null, 0, $executionTime, curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        self::logApiCall($settings, $method, $endpoint, $data, $responseData, $httpCode, $executionTime);
        
        if ($httpCode < 200 || $httpCode >= 300) {
            return false;
        }
        
        return $responseData;
    }
    
    /**
     * API çağrısını loglar
     */
    private static function logApiCall($settings, $method, $endpoint, $requestData, $responseData, $httpCode, $executionTime, $error = null) {
        global $db;
        
        // Veritabanı bağlantısı yoksa dosyaya yaz
        if (!isset($db)) {
            $logFile = DIR_LOGS . 'trendyol_api.log';
            $date = date('Y-m-d H:i:s');
            $log = "[$date] [SYSTEM] [API_CALL] Method: $method, Endpoint: $endpoint, HTTP Code: $httpCode\n";
            if ($error) {
                $log .= "[$date] [SYSTEM] [API_ERROR] $error\n";
            }
            file_put_contents($logFile, $log, FILE_APPEND);
            return;
        }
        
        // Veritabanına kaydet
        $requestJson = $requestData ? json_encode($requestData) : null;
        $responseJson = $responseData ? json_encode($responseData) : null;
        
        if ($error) {
            $responseJson = json_encode(['error' => $error]);
        }
        
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        $ipAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        
        $db->query("INSERT INTO " . DB_PREFIX . "meschain_api_log SET
            marketplace = 'trendyol',
            request_type = '" . $db->escape($method) . "',
            endpoint = '" . $db->escape($endpoint) . "',
            request_data = '" . $db->escape($requestJson) . "',
            response_data = '" . $db->escape($responseJson) . "',
            http_code = '" . (int)$httpCode . "',
            execution_time = '" . (float)$executionTime . "',
            date_added = NOW(),
            user_id = '" . (int)$userId . "',
            ip_address = '" . $db->escape($ipAddress) . "'");
    }
    
    /**
     * Siparişleri çeker
     * @param array $settings API ayarları
     * @param array $params Sorgu parametreleri
     * @return array|false
     */
    public static function getOrders($settings, $params = []) {
        $endpoint = '/suppliers/' . $settings['supplier_id'] . '/orders';
        
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }
        
        return self::apiRequest($settings, 'GET', $endpoint);
    }
    
    /**
     * Sipariş detayını çeker
     * @param array $settings API ayarları
     * @param string $orderId Sipariş ID
     * @return array|false
     */
    public static function getOrderDetails($settings, $orderId) {
        $endpoint = '/suppliers/' . $settings['supplier_id'] . '/orders/' . $orderId;
        return self::apiRequest($settings, 'GET', $endpoint);
    }
    
    /**
     * Ürünleri çeker
     * @param array $settings API ayarları
     * @param array $params Sorgu parametreleri
     * @return array|false
     */
    public static function getProducts($settings, $params = []) {
        $endpoint = '/suppliers/' . $settings['supplier_id'] . '/products';
        
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }
        
        return self::apiRequest($settings, 'GET', $endpoint);
    }
    
    /**
     * Ürün ekler
     * @param array $settings API ayarları
     * @param array $product Ürün verisi
     * @return array|false
     */
    public static function createProduct($settings, $product) {
        $endpoint = '/suppliers/' . $settings['supplier_id'] . '/v2/products';
        return self::apiRequest($settings, 'POST', $endpoint, $product);
    }
    
    /**
     * Ürün günceller
     * @param array $settings API ayarları
     * @param array $product Ürün verisi
     * @return array|false
     */
    public static function updateProduct($settings, $product) {
        $endpoint = '/suppliers/' . $settings['supplier_id'] . '/v2/products';
        return self::apiRequest($settings, 'PUT', $endpoint, $product);
    }
    
    /**
     * Ürün stok ve fiyatını günceller
     * @param array $settings API ayarları
     * @param array $items Ürün listesi
     * @return array|false
     */
    public static function updatePriceAndInventory($settings, $items) {
        $endpoint = '/suppliers/' . $settings['supplier_id'] . '/products/price-and-inventory';
        return self::apiRequest($settings, 'POST', $endpoint, ['items' => $items]);
    }
    
    /**
     * Kategorileri çeker
     * @param array $settings API ayarları
     * @return array|false
     */
    public static function getCategories($settings) {
        $endpoint = '/product-categories';
        return self::apiRequest($settings, 'GET', $endpoint);
    }
    
    /**
     * Kategori özelliklerini çeker
     * @param array $settings API ayarları
     * @param int $categoryId Kategori ID
     * @return array|false
     */
    public static function getCategoryAttributes($settings, $categoryId) {
        $endpoint = '/product-categories/' . $categoryId . '/attributes';
        return self::apiRequest($settings, 'GET', $endpoint);
    }
    
    /**
     * Markaları çeker
     * @param array $settings API ayarları
     * @param string $name Marka adı (opsiyonel)
     * @return array|false
     */
    public static function getBrands($settings, $name = null) {
        $endpoint = '/brands';
        
        if ($name) {
            $endpoint .= '?name=' . urlencode($name);
        }
        
        return self::apiRequest($settings, 'GET', $endpoint);
    }
    
    /**
     * Kargo şirketlerini çeker
     * @param array $settings API ayarları
     * @return array|false
     */
    public static function getShipmentProviders($settings) {
        $endpoint = '/shipment-providers';
        return self::apiRequest($settings, 'GET', $endpoint);
    }
    
    /**
     * Sipariş durumunu günceller
     * @param array $settings API ayarları
     * @param string $orderId Sipariş ID
     * @param string $status Yeni durum
     * @return array|false
     */
    public static function updateOrderStatus($settings, $orderId, $status) {
        $endpoint = '/suppliers/' . $settings['supplier_id'] . '/orders/' . $orderId;
        return self::apiRequest($settings, 'PUT', $endpoint, ['status' => $status]);
    }
} 