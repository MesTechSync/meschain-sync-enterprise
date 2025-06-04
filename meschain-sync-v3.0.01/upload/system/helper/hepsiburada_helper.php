<?php
/**
 * Hepsiburada Helper Sınıfı
 * 
 * Hepsiburada API ile iletişim kurmak için gerekli fonksiyonları içerir.
 */
class HepsiburadaHelper {
    private $username;
    private $password;
    private $merchantId;
    private $baseUrl;
    private $logger;
    private $token;
    private $tokenExpiry;
    
    /**
     * Yapıcı fonksiyon
     * 
     * @param string $username API kullanıcı adı
     * @param string $password API şifresi
     * @param string $merchantId Satıcı ID
     */
    public function __construct($username, $password, $merchantId) {
        $this->username = $username;
        $this->password = $password;
        $this->merchantId = $merchantId;
        $this->baseUrl = 'https://mpop.hepsiburada.com/api/';
        
        // Logger başlat
        $this->logger = new Log('hepsiburada.log');
        
        // Token bilgileri
        $this->token = null;
        $this->tokenExpiry = null;
    }
    
    /**
     * API token alır
     * 
     * @return bool Token alma başarılı mı
     */
    private function authenticate() {
        // Token hala geçerliyse yeni token alma
        if ($this->token && $this->tokenExpiry && $this->tokenExpiry > time()) {
            return true;
        }
        
        try {
            $ch = curl_init($this->baseUrl . 'authenticate');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json'
            ));
            
            $data = array(
                'username' => $this->username,
                'password' => $this->password,
                'merchantId' => $this->merchantId
            );
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                $result = json_decode($response, true);
                
                if (isset($result['token'])) {
                    $this->token = $result['token'];
                    // Token 24 saat geçerli, 23 saat sonra yenilemek için
                    $this->tokenExpiry = time() + (23 * 3600);
                    return true;
                }
            }
            
            $this->logger->write('Hepsiburada Authentication Failed: ' . $response);
            return false;
        } catch (Exception $e) {
            $this->logger->write('Hepsiburada Authentication Exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hepsiburada API'ye istek gönderir
     * 
     * @param string $endpoint API endpoint
     * @param string $method HTTP metodu (GET, POST, PUT, DELETE)
     * @param array $data İstek verileri
     * @return array|bool Yanıt veya hata durumunda false
     */
    public function sendRequest($endpoint, $method = 'GET', $data = null) {
        // Önce token al
        if (!$this->authenticate()) {
            return false;
        }
        
        try {
            $url = $this->baseUrl . $endpoint;
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
                'Accept: application/json'
            ));
            
            switch ($method) {
                case 'POST':
                    curl_setopt($ch, CURLOPT_POST, true);
                    if ($data) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    }
                    break;
                case 'PUT':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                    if ($data) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    }
                    break;
                case 'DELETE':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    break;
                default: // GET
                    if ($data) {
                        $url .= '?' . http_build_query($data);
                        curl_setopt($ch, CURLOPT_URL, $url);
                    }
            }
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // HTTP yanıt kodunu kontrol et
            if ($httpCode < 200 || $httpCode >= 300) {
                $this->logger->write('Hepsiburada API Error: ' . $httpCode . ' - ' . $response);
                return false;
            }
            
            return json_decode($response, true);
        } catch (Exception $e) {
            $this->logger->write('Hepsiburada API Exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ürünleri getirir
     * 
     * @param int $offset Başlangıç indeksi
     * @param int $limit Sayfa başına ürün sayısı
     * @return array|bool Ürünler veya hata durumunda false
     */
    public function getProducts($offset = 0, $limit = 50) {
        $params = array(
            'offset' => $offset,
            'limit' => $limit
        );
        
        $result = $this->sendRequest('listings', 'GET', $params);
        
        if ($result && isset($result['listings'])) {
            return $result;
        } else {
            $this->logger->write('Hepsiburada Get Products Failed');
            return false;
        }
    }
    
    /**
     * Ürün detayını getirir
     * 
     * @param string $merchantSku Satıcı ürün kodu
     * @return array|bool Ürün detayı veya hata durumunda false
     */
    public function getProduct($merchantSku) {
        $result = $this->sendRequest('listings/' . urlencode($merchantSku));
        
        if ($result && isset($result['listing'])) {
            return $result['listing'];
        } else {
            $this->logger->write('Hepsiburada Get Product Failed: ' . $merchantSku);
            return false;
        }
    }
    
    /**
     * Stok günceller
     * 
     * @param string $merchantSku Satıcı ürün kodu
     * @param int $quantity Miktar
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function updateStock($merchantSku, $quantity) {
        $data = array(
            'merchantSku' => $merchantSku,
            'quantity' => $quantity
        );
        
        $result = $this->sendRequest('listings/inventory', 'POST', $data);
        
        if ($result && isset($result['status']) && $result['status'] == 'success') {
            $this->logger->write('Hepsiburada Update Stock: ' . $merchantSku . ' -> ' . $quantity);
            return $result;
        } else {
            $this->logger->write('Hepsiburada Update Stock Failed: ' . $merchantSku);
            return false;
        }
    }
    
    /**
     * Fiyat günceller
     * 
     * @param string $merchantSku Satıcı ürün kodu
     * @param float $price Fiyat
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function updatePrice($merchantSku, $price) {
        $data = array(
            'merchantSku' => $merchantSku,
            'price' => $price
        );
        
        $result = $this->sendRequest('listings/price', 'POST', $data);
        
        if ($result && isset($result['status']) && $result['status'] == 'success') {
            $this->logger->write('Hepsiburada Update Price: ' . $merchantSku . ' -> ' . $price);
            return $result;
        } else {
            $this->logger->write('Hepsiburada Update Price Failed: ' . $merchantSku);
            return false;
        }
    }
    
    /**
     * Siparişleri getirir
     * 
     * @param string $status Sipariş durumu (open, shipped, delivered, cancelled)
     * @param string $startDate Başlangıç tarihi (yyyy-MM-dd formatında)
     * @param string $endDate Bitiş tarihi (yyyy-MM-dd formatında)
     * @param int $offset Başlangıç indeksi
     * @param int $limit Sayfa başına sipariş sayısı
     * @return array|bool Siparişler veya hata durumunda false
     */
    public function getOrders($status = 'open', $startDate = null, $endDate = null, $offset = 0, $limit = 50) {
        $params = array(
            'status' => $status,
            'offset' => $offset,
            'limit' => $limit
        );
        
        if ($startDate) {
            $params['startDate'] = $startDate;
        }
        
        if ($endDate) {
            $params['endDate'] = $endDate;
        }
        
        $result = $this->sendRequest('orders', 'GET', $params);
        
        if ($result && isset($result['orders'])) {
            return $result;
        } else {
            $this->logger->write('Hepsiburada Get Orders Failed');
            return false;
        }
    }
    
    /**
     * Sipariş detayını getirir
     * 
     * @param string $orderNumber Sipariş numarası
     * @return array|bool Sipariş detayı veya hata durumunda false
     */
    public function getOrder($orderNumber) {
        $result = $this->sendRequest('orders/' . urlencode($orderNumber));
        
        if ($result && isset($result['order'])) {
            return $result['order'];
        } else {
            $this->logger->write('Hepsiburada Get Order Failed: ' . $orderNumber);
            return false;
        }
    }
    
    /**
     * Sipariş durumunu günceller
     * 
     * @param string $orderNumber Sipariş numarası
     * @param string $status Yeni durum (shipped, delivered, cancelled)
     * @param string $trackingNumber Kargo takip numarası (opsiyonel)
     * @param string $trackingCompany Kargo şirketi (opsiyonel)
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function updateOrderStatus($orderNumber, $status, $trackingNumber = null, $trackingCompany = null) {
        $data = array(
            'status' => $status
        );
        
        if ($trackingNumber && $trackingCompany) {
            $data['trackingNumber'] = $trackingNumber;
            $data['trackingCompany'] = $trackingCompany;
        }
        
        $result = $this->sendRequest('orders/' . urlencode($orderNumber) . '/status', 'PUT', $data);
        
        if ($result && isset($result['status']) && $result['status'] == 'success') {
            $this->logger->write('Hepsiburada Update Order Status: ' . $orderNumber . ' -> ' . $status);
            return $result;
        } else {
            $this->logger->write('Hepsiburada Update Order Status Failed: ' . $orderNumber);
            return false;
        }
    }
    
    /**
     * Kategorileri getirir
     * 
     * @return array|bool Kategoriler veya hata durumunda false
     */
    public function getCategories() {
        $result = $this->sendRequest('categories');
        
        if ($result && isset($result['categories'])) {
            return $result['categories'];
        } else {
            $this->logger->write('Hepsiburada Get Categories Failed');
            return false;
        }
    }
    
    /**
     * Kategori özelliklerini getirir
     * 
     * @param string $categoryId Kategori ID
     * @return array|bool Kategori özellikleri veya hata durumunda false
     */
    public function getCategoryAttributes($categoryId) {
        $result = $this->sendRequest('categories/' . urlencode($categoryId) . '/attributes');
        
        if ($result && isset($result['attributes'])) {
            return $result['attributes'];
        } else {
            $this->logger->write('Hepsiburada Get Category Attributes Failed: ' . $categoryId);
            return false;
        }
    }
} 