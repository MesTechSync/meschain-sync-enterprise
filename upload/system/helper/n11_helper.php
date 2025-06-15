<?php
/**
 * n11 Helper Sınıfı
 * 
 * n11 API ile iletişim kurmak için gerekli fonksiyonları içerir.
 */
class N11Helper {
    private $appKey;
    private $appSecret;
    private $baseUrl;
    private $logger;
    
    /**
     * Yapıcı fonksiyon
     * 
     * @param string $appKey Uygulama anahtarı
     * @param string $appSecret Uygulama gizli anahtarı
     */
    public function __construct($appKey, $appSecret) {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->baseUrl = 'https://api.n11.com/ws/';
        
        // Logger başlat
        $this->logger = new Log('n11.log');
    }
    
    /**
     * n11 API'ye SOAP isteği gönderir
     * 
     * @param string $service Servis adı
     * @param string $operation İşlem adı
     * @param array $parameters İstek parametreleri
     * @return array|bool Yanıt veya hata durumunda false
     */
    public function sendRequest($service, $operation, $parameters = array()) {
        try {
            // SOAP istek parametrelerini hazırla
            $auth = array(
                'appKey' => $this->appKey,
                'appSecret' => $this->appSecret
            );
            
            $params = array_merge($auth, $parameters);
            
            // SOAP istemcisini oluştur
            $client = new SoapClient($this->baseUrl . $service . '?wsdl', array(
                'trace' => true,
                'exceptions' => true,
                'encoding' => 'UTF-8'
            ));
            
            // İsteği gönder
            $response = $client->__call($operation, array($params));
            
            // Yanıtı kontrol et
            if (isset($response->result) && $response->result->status == 'success') {
                return $this->objectToArray($response);
            } else {
                $errorMessage = isset($response->result->errorMessage) ? $response->result->errorMessage : 'Bilinmeyen hata';
                $this->logger->write('n11 API Error: ' . $errorMessage);
                return false;
            }
        } catch (Exception $e) {
            $this->logger->write('n11 API Exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Nesneyi diziye dönüştürür
     * 
     * @param object $object Nesne
     * @return array Dizi
     */
    private function objectToArray($object) {
        if (is_object($object)) {
            $object = get_object_vars($object);
        }
        
        if (is_array($object)) {
            return array_map(array($this, 'objectToArray'), $object);
        } else {
            return $object;
        }
    }
    
    /**
     * Ürünleri getirir
     * 
     * @param int $page Sayfa numarası
     * @param int $size Sayfa başına ürün sayısı
     * @return array|bool Ürünler veya hata durumunda false
     */
    public function getProducts($page = 0, $size = 50) {
        $parameters = array(
            'pagingData' => array(
                'currentPage' => $page,
                'pageSize' => $size
            )
        );
        
        $result = $this->sendRequest('ProductService', 'GetProductList', $parameters);
        
        if ($result && isset($result['products'])) {
            return $result;
        } else {
            $this->logger->write('n11 Get Products Failed');
            return false;
        }
    }
    
    /**
     * Ürün ekler
     * 
     * @param array $product Ürün bilgileri
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function addProduct($product) {
        $result = $this->sendRequest('ProductService', 'SaveProduct', array('product' => $product));
        
        if ($result && isset($result['result'])) {
            $this->logger->write('n11 Add Product: ' . $product['productSellerCode']);
            return $result;
        } else {
            $this->logger->write('n11 Add Product Failed');
            return false;
        }
    }
    
    /**
     * Ürün günceller
     * 
     * @param array $product Ürün bilgileri
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function updateProduct($product) {
        $result = $this->sendRequest('ProductService', 'UpdateProduct', array('product' => $product));
        
        if ($result && isset($result['result'])) {
            $this->logger->write('n11 Update Product: ' . $product['productSellerCode']);
            return $result;
        } else {
            $this->logger->write('n11 Update Product Failed');
            return false;
        }
    }
    
    /**
     * Stok günceller
     * 
     * @param string $sellerCode Satıcı kodu
     * @param int $quantity Miktar
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function updateStock($sellerCode, $quantity) {
        $parameters = array(
            'productSellerCode' => $sellerCode,
            'quantity' => $quantity
        );
        
        $result = $this->sendRequest('ProductStockService', 'UpdateProductStock', $parameters);
        
        if ($result && isset($result['result'])) {
            $this->logger->write('n11 Update Stock: ' . $sellerCode . ' -> ' . $quantity);
            return $result;
        } else {
            $this->logger->write('n11 Update Stock Failed');
            return false;
        }
    }
    
    /**
     * Fiyat günceller
     * 
     * @param string $sellerCode Satıcı kodu
     * @param float $price Fiyat
     * @param float $discountPrice İndirimli fiyat (opsiyonel)
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function updatePrice($sellerCode, $price, $discountPrice = null) {
        $parameters = array(
            'productSellerCode' => $sellerCode,
            'price' => $price
        );
        
        if ($discountPrice !== null) {
            $parameters['discountPrice'] = $discountPrice;
        }
        
        $result = $this->sendRequest('ProductPriceService', 'UpdateProductPrice', $parameters);
        
        if ($result && isset($result['result'])) {
            $this->logger->write('n11 Update Price: ' . $sellerCode . ' -> ' . $price);
            return $result;
        } else {
            $this->logger->write('n11 Update Price Failed');
            return false;
        }
    }
    
    /**
     * Siparişleri getirir
     * 
     * @param string $status Sipariş durumu
     * @param int $page Sayfa numarası
     * @param int $size Sayfa başına sipariş sayısı
     * @param string $startDate Başlangıç tarihi (yyyy-MM-dd HH:mm:ss formatında)
     * @param string $endDate Bitiş tarihi (yyyy-MM-dd HH:mm:ss formatında)
     * @return array|bool Siparişler veya hata durumunda false
     */
    public function getOrders($status = 'New', $page = 0, $size = 50, $startDate = null, $endDate = null) {
        $parameters = array(
            'pagingData' => array(
                'currentPage' => $page,
                'pageSize' => $size
            ),
            'status' => $status
        );
        
        if ($startDate && $endDate) {
            $parameters['period'] = array(
                'startDate' => $startDate,
                'endDate' => $endDate
            );
        }
        
        $result = $this->sendRequest('OrderService', 'OrderList', $parameters);
        
        if ($result && isset($result['orderList'])) {
            return $result;
        } else {
            $this->logger->write('n11 Get Orders Failed');
            return false;
        }
    }
    
    /**
     * Sipariş detayını getirir
     * 
     * @param int $orderId Sipariş ID
     * @return array|bool Sipariş detayı veya hata durumunda false
     */
    public function getOrderDetail($orderId) {
        $parameters = array(
            'orderRequest' => array(
                'id' => $orderId
            )
        );
        
        $result = $this->sendRequest('OrderService', 'OrderDetail', $parameters);
        
        if ($result && isset($result['orderDetail'])) {
            return $result;
        } else {
            $this->logger->write('n11 Get Order Detail Failed');
            return false;
        }
    }
    
    /**
     * Kategorileri getirir
     * 
     * @return array|bool Kategoriler veya hata durumunda false
     */
    public function getCategories() {
        $result = $this->sendRequest('CategoryService', 'GetTopLevelCategories', array());
        
        if ($result && isset($result['categoryList'])) {
            return $result['categoryList'];
        } else {
            $this->logger->write('n11 Get Categories Failed');
            return false;
        }
    }
    
    /**
     * Alt kategorileri getirir
     * 
     * @param int $categoryId Kategori ID
     * @return array|bool Alt kategoriler veya hata durumunda false
     */
    public function getSubCategories($categoryId) {
        $parameters = array(
            'categoryId' => $categoryId
        );
        
        $result = $this->sendRequest('CategoryService', 'GetSubCategories', $parameters);
        
        if ($result && isset($result['category']['subCategoryList'])) {
            return $result['category']['subCategoryList'];
        } else {
            $this->logger->write('n11 Get Sub Categories Failed');
            return false;
        }
    }
    
    /**
     * Kategori özelliklerini getirir
     * 
     * @param int $categoryId Kategori ID
     * @return array|bool Kategori özellikleri veya hata durumunda false
     */
    public function getCategoryAttributes($categoryId) {
        $parameters = array(
            'categoryId' => $categoryId
        );
        
        $result = $this->sendRequest('CategoryService', 'GetCategoryAttributes', $parameters);
        
        if ($result && isset($result['category']['categoryAttributes'])) {
            return $result['category']['categoryAttributes'];
        } else {
            $this->logger->write('n11 Get Category Attributes Failed');
            return false;
        }
    }
} 