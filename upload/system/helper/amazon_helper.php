<?php
/**
 * Amazon Helper Sınıfı
 * 
 * Amazon API ile iletişim kurmak için gerekli fonksiyonları içerir.
 */
class AmazonHelper {
    private $apiKey;
    private $secretKey;
    private $sellerId;
    private $token;
    private $marketplaceId;
    private $region;
    private $logger;
    
    /**
     * Yapıcı fonksiyon
     * 
     * @param string $apiKey API anahtarı
     * @param string $secretKey Secret key
     * @param string $sellerId Satıcı ID
     * @param string $token Token
     * @param string $region Bölge (eu, na, fe)
     */
    public function __construct($apiKey, $secretKey, $sellerId, $token, $region = 'eu') {
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->sellerId = $sellerId;
        $this->token = $token;
        $this->region = $region;
        
        // Bölgeye göre marketplace ID'sini ayarla
        switch ($region) {
            case 'eu':
                $this->marketplaceId = 'A1PA6795UKMFR9'; // Almanya
                break;
            case 'na':
                $this->marketplaceId = 'ATVPDKIKX0DER'; // ABD
                break;
            case 'fe':
                $this->marketplaceId = 'A1VC38T7YXB528'; // Japonya
                break;
            default:
                $this->marketplaceId = 'A1PA6795UKMFR9'; // Varsayılan: Almanya
        }
        
        // Logger başlat
        $this->logger = new Log('amazon.log');
    }
    
    /**
     * Amazon API'ye istek gönderir
     * 
     * @param string $action API işlemi
     * @param array $parameters İstek parametreleri
     * @return array|bool Yanıt veya hata durumunda false
     */
    public function sendRequest($action, $parameters = array()) {
        try {
            // İstek parametrelerini hazırla
            $params = array_merge(array(
                'Action' => $action,
                'SellerId' => $this->sellerId,
                'MarketplaceId' => $this->marketplaceId,
                'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
                'Version' => '2013-09-01',
                'AWSAccessKeyId' => $this->apiKey
            ), $parameters);
            
            // Parametreleri sırala
            ksort($params);
            
            // İstek URL'sini oluştur
            $url = $this->getEndpoint();
            
            // İmza oluştur
            $stringToSign = "GET\n" . parse_url($url, PHP_URL_HOST) . "\n/\n";
            
            $queryString = '';
            foreach ($params as $key => $value) {
                $queryString .= '&' . $key . '=' . rawurlencode($value);
            }
            $queryString = ltrim($queryString, '&');
            
            $stringToSign .= $queryString;
            
            // HMAC-SHA256 imzası oluştur
            $signature = base64_encode(hash_hmac('sha256', $stringToSign, $this->secretKey, true));
            
            // İmzayı URL'ye ekle
            $url .= '?' . $queryString . '&Signature=' . rawurlencode($signature);
            
            // API isteğini gönder
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'x-amazon-user-agent: MesTech Sync/1.0.2 (Language=PHP)',
                'Content-Type: application/xml',
                'Authorization: Bearer ' . $this->token
            ));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode != 200) {
                $this->logger->write('Amazon API Error: ' . $httpCode . ' - ' . $response);
                return false;
            }
            
            // XML yanıtını diziye dönüştür
            $xml = simplexml_load_string($response);
            $result = json_decode(json_encode($xml), true);
            
            return $result;
        } catch (Exception $e) {
            $this->logger->write('Amazon API Exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Bölgeye göre API endpoint URL'sini döndürür
     * 
     * @return string Endpoint URL
     */
    private function getEndpoint() {
        switch ($this->region) {
            case 'eu':
                return 'https://mws-eu.amazonservices.com';
            case 'na':
                return 'https://mws.amazonservices.com';
            case 'fe':
                return 'https://mws.amazonservices.jp';
            default:
                return 'https://mws-eu.amazonservices.com';
        }
    }
    
    /**
     * Ürünleri Amazon'a gönderir
     * 
     * @param array $products Ürün listesi
     * @return bool Başarılı/başarısız
     */
    public function submitProducts($products) {
        // Ürün gönderme XML'ini oluştur
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">';
        $xml .= '<Header>';
        $xml .= '<DocumentVersion>1.01</DocumentVersion>';
        $xml .= '<MerchantIdentifier>' . $this->sellerId . '</MerchantIdentifier>';
        $xml .= '</Header>';
        $xml .= '<MessageType>Product</MessageType>';
        
        $messageId = 1;
        foreach ($products as $product) {
            $xml .= '<Message>';
            $xml .= '<MessageID>' . $messageId . '</MessageID>';
            $xml .= '<OperationType>Update</OperationType>';
            $xml .= '<Product>';
            $xml .= '<SKU>' . $product['sku'] . '</SKU>';
            $xml .= '<StandardProductID>';
            $xml .= '<Type>EAN</Type>';
            $xml .= '<Value>' . $product['ean'] . '</Value>';
            $xml .= '</StandardProductID>';
            $xml .= '<ProductTaxCode>A_GEN_STANDARD</ProductTaxCode>';
            $xml .= '<DescriptionData>';
            $xml .= '<Title>' . $product['title'] . '</Title>';
            $xml .= '<Brand>' . $product['brand'] . '</Brand>';
            $xml .= '<Description>' . $product['description'] . '</Description>';
            $xml .= '</DescriptionData>';
            $xml .= '<ProductData>';
            $xml .= '<Health>';
            $xml .= '<ProductType>';
            $xml .= '<HealthMisc>';
            $xml .= '</HealthMisc>';
            $xml .= '</ProductType>';
            $xml .= '</Health>';
            $xml .= '</ProductData>';
            $xml .= '</Product>';
            $xml .= '</Message>';
            
            $messageId++;
        }
        
        $xml .= '</AmazonEnvelope>';
        
        // Feed gönderme isteği
        $params = array(
            'Action' => 'SubmitFeed',
            'FeedType' => '_POST_PRODUCT_DATA_',
            'PurgeAndReplace' => 'false',
            'ContentMD5Value' => base64_encode(md5($xml, true))
        );
        
        // İsteği gönder
        $result = $this->sendRequest('SubmitFeed', $params);
        
        if ($result && isset($result['SubmitFeedResult']['FeedSubmissionInfo']['FeedSubmissionId'])) {
            $this->logger->write('Amazon Feed Submitted: ' . $result['SubmitFeedResult']['FeedSubmissionInfo']['FeedSubmissionId']);
            return true;
        } else {
            $this->logger->write('Amazon Feed Submission Failed');
            return false;
        }
    }
    
    /**
     * Stok ve fiyat güncellemesi yapar
     * 
     * @param array $items Stok ve fiyat bilgileri
     * @return bool Başarılı/başarısız
     */
    public function updateInventory($items) {
        // Stok güncelleme XML'ini oluştur
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">';
        $xml .= '<Header>';
        $xml .= '<DocumentVersion>1.01</DocumentVersion>';
        $xml .= '<MerchantIdentifier>' . $this->sellerId . '</MerchantIdentifier>';
        $xml .= '</Header>';
        $xml .= '<MessageType>Inventory</MessageType>';
        
        $messageId = 1;
        foreach ($items as $item) {
            $xml .= '<Message>';
            $xml .= '<MessageID>' . $messageId . '</MessageID>';
            $xml .= '<OperationType>Update</OperationType>';
            $xml .= '<Inventory>';
            $xml .= '<SKU>' . $item['sku'] . '</SKU>';
            $xml .= '<Quantity>' . $item['quantity'] . '</Quantity>';
            $xml .= '<Price>' . $item['price'] . '</Price>';
            $xml .= '</Inventory>';
            $xml .= '</Message>';
            
            $messageId++;
        }
        
        $xml .= '</AmazonEnvelope>';
        
        // Feed gönderme isteği
        $params = array(
            'Action' => 'SubmitFeed',
            'FeedType' => '_POST_INVENTORY_AVAILABILITY_DATA_',
            'PurgeAndReplace' => 'false',
            'ContentMD5Value' => base64_encode(md5($xml, true))
        );
        
        // İsteği gönder
        $result = $this->sendRequest('SubmitFeed', $params);
        
        if ($result && isset($result['SubmitFeedResult']['FeedSubmissionInfo']['FeedSubmissionId'])) {
            $this->logger->write('Amazon Inventory Updated: ' . $result['SubmitFeedResult']['FeedSubmissionInfo']['FeedSubmissionId']);
            return true;
        } else {
            $this->logger->write('Amazon Inventory Update Failed');
            return false;
        }
    }
    
    /**
     * Siparişleri getirir
     * 
     * @param string $startDate Başlangıç tarihi (ISO 8601 formatında)
     * @param string $endDate Bitiş tarihi (ISO 8601 formatında)
     * @return array|bool Siparişler veya hata durumunda false
     */
    public function getOrders($startDate, $endDate) {
        $params = array(
            'CreatedAfter' => $startDate,
            'CreatedBefore' => $endDate,
            'OrderStatus.Status.1' => 'Unshipped',
            'OrderStatus.Status.2' => 'PartiallyShipped',
            'OrderStatus.Status.3' => 'Shipped',
            'FulfillmentChannel.Channel.1' => 'MFN'
        );
        
        $result = $this->sendRequest('ListOrders', $params);
        
        if ($result && isset($result['ListOrdersResult']['Orders']['Order'])) {
            $orders = $result['ListOrdersResult']['Orders']['Order'];
            
            // Tek bir sipariş varsa, dizi yapısını düzelt
            if (isset($orders['AmazonOrderId'])) {
                $orders = array($orders);
            }
            
            $this->logger->write('Amazon Get Orders: ' . count($orders) . ' orders retrieved');
            return $orders;
        } else {
            $this->logger->write('Amazon Get Orders Failed');
            return false;
        }
    }
    
    /**
     * Sipariş detaylarını getirir
     * 
     * @param string $orderId Sipariş ID
     * @return array|bool Sipariş detayları veya hata durumunda false
     */
    public function getOrderItems($orderId) {
        $params = array(
            'AmazonOrderId' => $orderId
        );
        
        $result = $this->sendRequest('ListOrderItems', $params);
        
        if ($result && isset($result['ListOrderItemsResult']['OrderItems']['OrderItem'])) {
            $items = $result['ListOrderItemsResult']['OrderItems']['OrderItem'];
            
            // Tek bir ürün varsa, dizi yapısını düzelt
            if (isset($items['ASIN'])) {
                $items = array($items);
            }
            
            return $items;
        } else {
            $this->logger->write('Amazon Get Order Items Failed for Order: ' . $orderId);
            return false;
        }
    }
    
    /**
     * Sipariş durumunu günceller (kargo bildirimi)
     * 
     * @param string $orderId Sipariş ID
     * @param string $trackingNumber Kargo takip numarası
     * @param string $carrier Kargo firması
     * @return bool Başarılı/başarısız
     */
    public function updateOrderStatus($orderId, $trackingNumber, $carrier) {
        // Kargo bildirimi XML'ini oluştur
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">';
        $xml .= '<Header>';
        $xml .= '<DocumentVersion>1.01</DocumentVersion>';
        $xml .= '<MerchantIdentifier>' . $this->sellerId . '</MerchantIdentifier>';
        $xml .= '</Header>';
        $xml .= '<MessageType>OrderFulfillment</MessageType>';
        $xml .= '<Message>';
        $xml .= '<MessageID>1</MessageID>';
        $xml .= '<OrderFulfillment>';
        $xml .= '<AmazonOrderID>' . $orderId . '</AmazonOrderID>';
        $xml .= '<FulfillmentDate>' . gmdate('Y-m-d\TH:i:s\Z') . '</FulfillmentDate>';
        $xml .= '<FulfillmentData>';
        $xml .= '<CarrierCode>' . $carrier . '</CarrierCode>';
        $xml .= '<ShippingMethod>Standard</ShippingMethod>';
        $xml .= '<ShipperTrackingNumber>' . $trackingNumber . '</ShipperTrackingNumber>';
        $xml .= '</FulfillmentData>';
        $xml .= '</OrderFulfillment>';
        $xml .= '</Message>';
        $xml .= '</AmazonEnvelope>';
        
        // Feed gönderme isteği
        $params = array(
            'Action' => 'SubmitFeed',
            'FeedType' => '_POST_ORDER_FULFILLMENT_DATA_',
            'PurgeAndReplace' => 'false',
            'ContentMD5Value' => base64_encode(md5($xml, true))
        );
        
        // İsteği gönder
        $result = $this->sendRequest('SubmitFeed', $params);
        
        if ($result && isset($result['SubmitFeedResult']['FeedSubmissionInfo']['FeedSubmissionId'])) {
            $this->logger->write('Amazon Order Status Updated: ' . $orderId);
            return true;
        } else {
            $this->logger->write('Amazon Order Status Update Failed for Order: ' . $orderId);
            return false;
        }
    }
    
    /**
     * Feed durumunu kontrol eder
     * 
     * @param string $feedSubmissionId Feed ID
     * @return array|bool Feed durumu veya hata durumunda false
     */
    public function getFeedSubmissionResult($feedSubmissionId) {
        $params = array(
            'FeedSubmissionId' => $feedSubmissionId
        );
        
        $result = $this->sendRequest('GetFeedSubmissionResult', $params);
        
        if ($result) {
            return $result;
        } else {
            $this->logger->write('Amazon Get Feed Submission Result Failed for Feed: ' . $feedSubmissionId);
            return false;
        }
    }
} 