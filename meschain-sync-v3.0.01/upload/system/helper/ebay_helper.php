<?php
/**
 * eBay Helper Sınıfı
 * 
 * eBay API ile iletişim kurmak için gerekli fonksiyonları içerir.
 */
class EbayHelper {
    private $devId;
    private $appId;
    private $certId;
    private $token;
    private $sandbox;
    private $baseUrl;
    private $logger;
    
    /**
     * Yapıcı fonksiyon
     * 
     * @param string $devId Geliştirici ID
     * @param string $appId Uygulama ID
     * @param string $certId Sertifika ID
     * @param string $token Kullanıcı token
     * @param bool $sandbox Sandbox modu (test ortamı)
     */
    public function __construct($devId, $appId, $certId, $token, $sandbox = false) {
        $this->devId = $devId;
        $this->appId = $appId;
        $this->certId = $certId;
        $this->token = $token;
        $this->sandbox = $sandbox;
        
        // API URL'sini belirle
        if ($sandbox) {
            $this->baseUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
        } else {
            $this->baseUrl = 'https://api.ebay.com/ws/api.dll';
        }
        
        // Logger başlat
        $this->logger = new Log('ebay.log');
    }
    
    /**
     * eBay API'ye XML isteği gönderir
     * 
     * @param string $callName API çağrı adı
     * @param array $requestBody İstek gövdesi
     * @return array|bool Yanıt veya hata durumunda false
     */
    public function sendRequest($callName, $requestBody = array()) {
        try {
            // XML başlığı
            $requestXml = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
            $requestXml .= '<' . $callName . 'Request xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
            
            // Kimlik doğrulama bilgileri
            $requestXml .= '<RequesterCredentials>' . "\n";
            $requestXml .= '<eBayAuthToken>' . $this->token . '</eBayAuthToken>' . "\n";
            $requestXml .= '</RequesterCredentials>' . "\n";
            
            // İstek gövdesi
            foreach ($requestBody as $key => $value) {
                if (is_array($value)) {
                    $requestXml .= $this->arrayToXml($key, $value);
                } else {
                    $requestXml .= '<' . $key . '>' . htmlspecialchars($value) . '</' . $key . '>' . "\n";
                }
            }
            
            // XML sonu
            $requestXml .= '</' . $callName . 'Request>';
            
            // cURL isteği hazırla
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestXml);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-EBAY-API-COMPATIBILITY-LEVEL: 1155',
                'X-EBAY-API-DEV-NAME: ' . $this->devId,
                'X-EBAY-API-APP-NAME: ' . $this->appId,
                'X-EBAY-API-CERT-NAME: ' . $this->certId,
                'X-EBAY-API-CALL-NAME: ' . $callName,
                'X-EBAY-API-SITEID: 0',
                'Content-Type: text/xml'
            ));
            
            // İsteği gönder
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // HTTP yanıt kodunu kontrol et
            if ($httpCode != 200) {
                $this->logger->write('eBay API HTTP Error: ' . $httpCode);
                return false;
            }
            
            // XML yanıtını diziye dönüştür
            $responseArray = $this->xmlToArray($response);
            
            // Yanıt durumunu kontrol et
            if (isset($responseArray['Ack']) && ($responseArray['Ack'] == 'Success' || $responseArray['Ack'] == 'Warning')) {
                if ($responseArray['Ack'] == 'Warning') {
                    $this->logger->write('eBay API Warning: ' . json_encode($responseArray['Errors']));
                }
                return $responseArray;
            } else {
                $errorMessage = isset($responseArray['Errors']['LongMessage']) ? $responseArray['Errors']['LongMessage'] : 'Bilinmeyen hata';
                $this->logger->write('eBay API Error: ' . $errorMessage);
                return false;
            }
        } catch (Exception $e) {
            $this->logger->write('eBay API Exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Diziyi XML'e dönüştürür
     * 
     * @param string $rootElement Kök eleman adı
     * @param array $array Dönüştürülecek dizi
     * @return string XML
     */
    private function arrayToXml($rootElement, $array) {
        $xml = '';
        
        if (isset($array[0])) {
            // Dizi indeksli ise
            foreach ($array as $value) {
                if (is_array($value)) {
                    $xml .= '<' . $rootElement . '>' . "\n";
                    foreach ($value as $key => $val) {
                        if (is_array($val)) {
                            $xml .= $this->arrayToXml($key, $val);
                        } else {
                            $xml .= '<' . $key . '>' . htmlspecialchars($val) . '</' . $key . '>' . "\n";
                        }
                    }
                    $xml .= '</' . $rootElement . '>' . "\n";
                } else {
                    $xml .= '<' . $rootElement . '>' . htmlspecialchars($value) . '</' . $rootElement . '>' . "\n";
                }
            }
        } else {
            // Dizi ilişkisel ise
            $xml .= '<' . $rootElement . '>' . "\n";
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $xml .= $this->arrayToXml($key, $value);
                } else {
                    $xml .= '<' . $key . '>' . htmlspecialchars($value) . '</' . $key . '>' . "\n";
                }
            }
            $xml .= '</' . $rootElement . '>' . "\n";
        }
        
        return $xml;
    }
    
    /**
     * XML'i diziye dönüştürür
     * 
     * @param string $xml XML
     * @return array Dizi
     */
    private function xmlToArray($xml) {
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $array = json_decode($json, true);
        return $array;
    }
    
    /**
     * Ürün ekler
     * 
     * @param array $item Ürün bilgileri
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function addItem($item) {
        $result = $this->sendRequest('AddItem', $item);
        
        if ($result && isset($result['ItemID'])) {
            $this->logger->write('eBay Add Item: ' . $result['ItemID']);
            return $result;
        } else {
            $this->logger->write('eBay Add Item Failed');
            return false;
        }
    }
    
    /**
     * Ürün günceller
     * 
     * @param array $item Ürün bilgileri
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function reviseItem($item) {
        $result = $this->sendRequest('ReviseItem', $item);
        
        if ($result && isset($result['ItemID'])) {
            $this->logger->write('eBay Revise Item: ' . $result['ItemID']);
            return $result;
        } else {
            $this->logger->write('eBay Revise Item Failed');
            return false;
        }
    }
    
    /**
     * Stok günceller
     * 
     * @param string $itemId Ürün ID
     * @param int $quantity Miktar
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function updateQuantity($itemId, $quantity) {
        $item = array(
            'Item' => array(
                'ItemID' => $itemId,
                'Quantity' => $quantity
            )
        );
        
        $result = $this->sendRequest('ReviseItem', $item);
        
        if ($result && isset($result['ItemID'])) {
            $this->logger->write('eBay Update Quantity: ' . $itemId . ' -> ' . $quantity);
            return $result;
        } else {
            $this->logger->write('eBay Update Quantity Failed');
            return false;
        }
    }
    
    /**
     * Fiyat günceller
     * 
     * @param string $itemId Ürün ID
     * @param float $price Fiyat
     * @return array|bool Sonuç veya hata durumunda false
     */
    public function updatePrice($itemId, $price) {
        $item = array(
            'Item' => array(
                'ItemID' => $itemId,
                'StartPrice' => $price
            )
        );
        
        $result = $this->sendRequest('ReviseItem', $item);
        
        if ($result && isset($result['ItemID'])) {
            $this->logger->write('eBay Update Price: ' . $itemId . ' -> ' . $price);
            return $result;
        } else {
            $this->logger->write('eBay Update Price Failed');
            return false;
        }
    }
    
    /**
     * Siparişleri getirir
     * 
     * @param int $page Sayfa numarası
     * @param int $entriesPerPage Sayfa başına sipariş sayısı
     * @param int $days Kaç gün önceki siparişler
     * @return array|bool Siparişler veya hata durumunda false
     */
    public function getOrders($page = 1, $entriesPerPage = 100, $days = 30) {
        $params = array(
            'CreateTimeFrom' => gmdate('Y-m-d\TH:i:s.000\Z', strtotime('-' . $days . ' days')),
            'CreateTimeTo' => gmdate('Y-m-d\TH:i:s.000\Z'),
            'OrderStatus' => 'All',
            'Pagination' => array(
                'EntriesPerPage' => $entriesPerPage,
                'PageNumber' => $page
            )
        );
        
        $result = $this->sendRequest('GetOrders', $params);
        
        if ($result && isset($result['OrderArray']['Order'])) {
            return $result;
        } else {
            $this->logger->write('eBay Get Orders Failed');
            return false;
        }
    }
    
    /**
     * Sipariş detayını getirir
     * 
     * @param string $orderId Sipariş ID
     * @return array|bool Sipariş detayı veya hata durumunda false
     */
    public function getOrder($orderId) {
        $params = array(
            'OrderID' => $orderId
        );
        
        $result = $this->sendRequest('GetOrder', $params);
        
        if ($result && isset($result['Order'])) {
            return $result;
        } else {
            $this->logger->write('eBay Get Order Failed');
            return false;
        }
    }
    
    /**
     * Ürün bilgilerini getirir
     * 
     * @param string $itemId Ürün ID
     * @return array|bool Ürün bilgileri veya hata durumunda false
     */
    public function getItem($itemId) {
        $params = array(
            'ItemID' => $itemId
        );
        
        $result = $this->sendRequest('GetItem', $params);
        
        if ($result && isset($result['Item'])) {
            return $result;
        } else {
            $this->logger->write('eBay Get Item Failed');
            return false;
        }
    }
    
    /**
     * Satıcının ürünlerini getirir
     * 
     * @param int $page Sayfa numarası
     * @param int $entriesPerPage Sayfa başına ürün sayısı
     * @return array|bool Ürünler veya hata durumunda false
     */
    public function getSellerList($page = 1, $entriesPerPage = 100) {
        $params = array(
            'StartTimeFrom' => gmdate('Y-m-d\TH:i:s.000\Z', strtotime('-30 days')),
            'StartTimeTo' => gmdate('Y-m-d\TH:i:s.000\Z'),
            'Pagination' => array(
                'EntriesPerPage' => $entriesPerPage,
                'PageNumber' => $page
            )
        );
        
        $result = $this->sendRequest('GetSellerList', $params);
        
        if ($result && isset($result['ItemArray']['Item'])) {
            return $result;
        } else {
            $this->logger->write('eBay Get Seller List Failed');
            return false;
        }
    }
} 