<?php
/**
 * Ozon Helper Sınıfı
 * 
 * Ozon API ile iletişim kurmak için gerekli fonksiyonları içerir.
 */
class OzonHelper {
    private $clientId;
    private $apiKey;
    private $baseUrl;
    private $logger;
    
    /**
     * Yapıcı fonksiyon
     * 
     * @param string $clientId API istemci ID
     * @param string $apiKey API anahtarı
     */
    public function __construct($clientId, $apiKey) {
        $this->clientId = $clientId;
        $this->apiKey = $apiKey;
        $this->baseUrl = 'https://api-seller.ozon.ru/';
        
        // Logger başlat
        $this->logger = new Log('ozon.log');
    }
    
    /**
     * Ozon API'ye istek gönderir
     * 
     * @param string $endpoint API endpoint
     * @param array $data İstek verileri
     * @param string $method HTTP metodu (GET, POST, PUT, DELETE)
     * @return array|bool Yanıt veya hata durumunda false
     */
    public function sendRequest($endpoint, $data = array(), $method = 'POST') {
        try {
            $url = $this->baseUrl . $endpoint;
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Client-Id: ' . $this->clientId,
                'Api-Key: ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json'
            ));
            
            switch ($method) {
                case 'POST':
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    break;
                case 'PUT':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    break;
                case 'DELETE':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    break;
                case 'GET':
                    if (!empty($data)) {
                        $url .= '?' . http_build_query($data);
                        curl_setopt($ch, CURLOPT_URL, $url);
                    }
                    break;
            }
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // HTTP yanıt kodunu kontrol et
            if ($httpCode < 200 || $httpCode >= 300) {
                $this->logger->write('Ozon API Error: ' . $httpCode . ' - ' . $response);
                return false;
            }
            
            return json_decode($response, true);
        } catch (Exception $e) {
            $this->logger->write('Ozon API Exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ürünleri getirir
     * 
     * @param int $page Sayfa numarası
     * @param int $pageSize Sayfa başına ürün sayısı
     * @return array|bool Ürünler veya hata durumunda false
     */
    public function getProducts($page = 1, $pageSize = 50) {
        $data = array(
            'page' => $page,
            'page_size' => $pageSize
        );
        
        $result = $this->sendRequest('v2/product/list', $data);
        
        if ($result && isset($result['result']['items'])) {
            return $result['result'];
        } else {
            $this->logger->write('Ozon Get Products Failed');
            return false;
        }
    }
    
    /**
     * Ürün detayını getirir
     * 
     * @param array $productIds Ürün ID'leri
     * @return array|bool Ürün detayları veya hata durumunda false
     */
    public function getProductInfo($productIds) {
        $data = array(
            'product_id' => $productIds
        );
        
        $result = $this->sendRequest('v2/product/info/list', $data);
        
        if ($result && isset($result['result']['items'])) {
            return $result['result']['items'];
        } else {
            $this->logger->write('Ozon Get Product Info Failed');
            return false;
        }
    }
    
    /**
     * Stok günceller
     * 
     * @param array $stocks Stok bilgileri [['product_id' => id, 'stock' => miktar], ...]
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function updateStocks($stocks) {
        $data = array(
            'stocks' => $stocks
        );
        
        $result = $this->sendRequest('v1/product/import/stocks', $data);
        
        if ($result && isset($result['result'])) {
            $this->logger->write('Ozon Update Stocks: ' . count($stocks) . ' ürün');
            return $result['result'];
        } else {
            $this->logger->write('Ozon Update Stocks Failed');
            return false;
        }
    }
    
    /**
     * Fiyat günceller
     * 
     * @param array $prices Fiyat bilgileri [['product_id' => id, 'price' => fiyat, 'old_price' => eski_fiyat], ...]
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function updatePrices($prices) {
        $data = array(
            'prices' => $prices
        );
        
        $result = $this->sendRequest('v1/product/import/prices', $data);
        
        if ($result && isset($result['result'])) {
            $this->logger->write('Ozon Update Prices: ' . count($prices) . ' ürün');
            return $result['result'];
        } else {
            $this->logger->write('Ozon Update Prices Failed');
            return false;
        }
    }
    
    /**
     * Siparişleri getirir
     * 
     * @param string $since Başlangıç tarihi (ISO 8601 formatında)
     * @param string $to Bitiş tarihi (ISO 8601 formatında)
     * @param string $status Sipariş durumu
     * @param int $page Sayfa numarası
     * @param int $pageSize Sayfa başına sipariş sayısı
     * @return array|bool Siparişler veya hata durumunda false
     */
    public function getOrders($since = null, $to = null, $status = null, $page = 1, $pageSize = 50) {
        $data = array(
            'page' => $page,
            'page_size' => $pageSize
        );
        
        if ($since) {
            $data['filter']['since'] = $since;
        }
        
        if ($to) {
            $data['filter']['to'] = $to;
        }
        
        if ($status) {
            $data['filter']['status'] = $status;
        }
        
        $result = $this->sendRequest('v2/posting/fbs/list', $data);
        
        if ($result && isset($result['result']['postings'])) {
            return $result['result'];
        } else {
            $this->logger->write('Ozon Get Orders Failed');
            return false;
        }
    }
    
    /**
     * Sipariş detayını getirir
     * 
     * @param string $postingNumber Sipariş numarası
     * @return array|bool Sipariş detayı veya hata durumunda false
     */
    public function getOrderInfo($postingNumber) {
        $data = array(
            'posting_number' => $postingNumber
        );
        
        $result = $this->sendRequest('v2/posting/fbs/get', $data);
        
        if ($result && isset($result['result'])) {
            return $result['result'];
        } else {
            $this->logger->write('Ozon Get Order Info Failed: ' . $postingNumber);
            return false;
        }
    }
    
    /**
     * Sipariş durumunu günceller
     * 
     * @param string $postingNumber Sipariş numarası
     * @param string $status Yeni durum
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function updateOrderStatus($postingNumber, $status) {
        $data = array(
            'posting_number' => $postingNumber,
            'status' => $status
        );
        
        $result = $this->sendRequest('v2/posting/fbs/status', $data);
        
        if ($result && isset($result['result'])) {
            $this->logger->write('Ozon Update Order Status: ' . $postingNumber . ' -> ' . $status);
            return $result['result'];
        } else {
            $this->logger->write('Ozon Update Order Status Failed: ' . $postingNumber);
            return false;
        }
    }
    
    /**
     * Kategorileri getirir
     * 
     * @param string $language Dil kodu (ru, en)
     * @return array|bool Kategoriler veya hata durumunda false
     */
    public function getCategories($language = 'ru') {
        $data = array(
            'language' => $language
        );
        
        $result = $this->sendRequest('v2/category/tree', $data);
        
        if ($result && isset($result['result'])) {
            return $result['result'];
        } else {
            $this->logger->write('Ozon Get Categories Failed');
            return false;
        }
    }
    
    /**
     * Kategori özelliklerini getirir
     * 
     * @param int $categoryId Kategori ID
     * @param string $language Dil kodu (ru, en)
     * @return array|bool Kategori özellikleri veya hata durumunda false
     */
    public function getCategoryAttributes($categoryId, $language = 'ru') {
        $data = array(
            'category_id' => $categoryId,
            'language' => $language
        );
        
        $result = $this->sendRequest('v3/category/attribute', $data);
        
        if ($result && isset($result['result'])) {
            return $result['result'];
        } else {
            $this->logger->write('Ozon Get Category Attributes Failed: ' . $categoryId);
            return false;
        }
    }
    
    /**
     * Ürün ekler
     * 
     * @param array $items Ürün bilgileri
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function createProducts($items) {
        $data = array(
            'items' => $items
        );
        
        $result = $this->sendRequest('v2/product/import', $data);
        
        if ($result && isset($result['result']['task_id'])) {
            $this->logger->write('Ozon Create Products: Task ID ' . $result['result']['task_id']);
            return $result['result'];
        } else {
            $this->logger->write('Ozon Create Products Failed');
            return false;
        }
    }
    
    /**
     * İşlem durumunu kontrol eder
     * 
     * @param int $taskId İşlem ID
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function getTaskStatus($taskId) {
        $data = array(
            'task_id' => $taskId
        );
        
        $result = $this->sendRequest('v1/task/status', $data);
        
        if ($result && isset($result['result'])) {
            return $result['result'];
        } else {
            $this->logger->write('Ozon Get Task Status Failed: ' . $taskId);
            return false;
        }
    }
} 