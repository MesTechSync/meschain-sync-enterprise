<?php
/**
 * MesChain eBay API Integration
 * eBay Trading API ve Finding API desteği
 * @author MesChain-Sync Team
 * @version 1.0.0
 */

require_once(DIR_SYSTEM . 'library/meschain/logger/logger.php');

class EbayAPI {
    
    private $app_id;
    private $dev_id;
    private $cert_id;
    private $user_token;
    private $sandbox;
    private $logger;
    
    // eBay API endpoints
    private $trading_endpoint;
    private $finding_endpoint;
    private $shopping_endpoint;
    
    public function __construct($config = array()) {
        // API credentials
        $this->app_id = isset($config['app_id']) ? $config['app_id'] : '';
        $this->dev_id = isset($config['dev_id']) ? $config['dev_id'] : '';
        $this->cert_id = isset($config['cert_id']) ? $config['cert_id'] : '';
        $this->user_token = isset($config['user_token']) ? $config['user_token'] : '';
        $this->sandbox = isset($config['sandbox']) ? $config['sandbox'] : true;
        
        // Set endpoints
        if ($this->sandbox) {
            $this->trading_endpoint = 'https://api.sandbox.ebay.com/ws/api/ebay/ebaycb/v1/';
            $this->finding_endpoint = 'https://svcs.sandbox.ebay.com/services/search/FindingService/v1';
            $this->shopping_endpoint = 'https://open.api.sandbox.ebay.com/shopping';
        } else {
            $this->trading_endpoint = 'https://api.ebay.com/ws/api/ebay/ebaycb/v1/';
            $this->finding_endpoint = 'https://svcs.ebay.com/services/search/FindingService/v1';
            $this->shopping_endpoint = 'https://open.api.ebay.com/shopping';
        }
        
        $this->logger = new MesChainLogger('ebay');
    }
    
    /**
     * Test API bağlantısı
     */
    public function testConnection() {
        try {
            $response = $this->callTradingAPI('GeteBayOfficialTime', array());
            
            if (isset($response['Timestamp'])) {
                $this->logger->info('eBay API bağlantısı başarılı');
                return array('success' => true, 'timestamp' => $response['Timestamp']);
            }
            
            return array('success' => false, 'error' => 'Invalid response');
            
        } catch (Exception $e) {
            $this->logger->error('eBay API bağlantı hatası: ' . $e->getMessage());
            return array('success' => false, 'error' => $e->getMessage());
        }
    }
    
    /**
     * Ürün listeleme
     */
    public function addFixedPriceItem($item_data) {
        try {
            $xml_request = $this->buildAddItemXML($item_data);
            $response = $this->callTradingAPI('AddFixedPriceItem', $xml_request);
            
            if (isset($response['ItemID'])) {
                $this->logger->info('eBay ürün listelendi: ' . $response['ItemID']);
                return array('success' => true, 'item_id' => $response['ItemID']);
            }
            
            $error_msg = isset($response['Errors']) ? $response['Errors'][0]['LongMessage'] : 'Unknown error';
            throw new Exception($error_msg);
            
        } catch (Exception $e) {
            $this->logger->error('eBay ürün listeleme hatası: ' . $e->getMessage());
            return array('success' => false, 'error' => $e->getMessage());
        }
    }
    
    /**
     * Ürün güncelleme
     */
    public function reviseFixedPriceItem($item_id, $update_data) {
        try {
            $xml_request = $this->buildReviseItemXML($item_id, $update_data);
            $response = $this->callTradingAPI('ReviseFixedPriceItem', $xml_request);
            
            if (isset($response['ItemID'])) {
                $this->logger->info('eBay ürün güncellendi', array(
                    'item_id' => $item_id
                ));
                
                return array(
                    'success' => true,
                    'item_id' => $response['ItemID']
                );
            }
            
            $error_msg = isset($response['Errors']) ? $response['Errors'][0]['LongMessage'] : 'Unknown error';
            throw new Exception($error_msg);
            
        } catch (Exception $e) {
            $this->logger->error('eBay ürün güncelleme hatası', array(
                'error' => $e->getMessage(),
                'item_id' => $item_id
            ));
            
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Ürün sonlandırma
     */
    public function endItem($item_id, $reason = 'NotAvailable') {
        try {
            $xml_request = array(
                'ItemID' => $item_id,
                'EndingReason' => $reason
            );
            
            $response = $this->callTradingAPI('EndItem', $xml_request);
            
            if (isset($response['EndTime'])) {
                $this->logger->info('eBay ürün sonlandırıldı', array(
                    'item_id' => $item_id,
                    'end_time' => $response['EndTime']
                ));
                
                return array(
                    'success' => true,
                    'end_time' => $response['EndTime']
                );
            }
            
            $error_msg = isset($response['Errors']) ? $response['Errors'][0]['LongMessage'] : 'Unknown error';
            throw new Exception($error_msg);
            
        } catch (Exception $e) {
            $this->logger->error('eBay ürün sonlandırma hatası', array(
                'error' => $e->getMessage(),
                'item_id' => $item_id
            ));
            
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Siparişleri getir
     */
    public function getOrders($date_from = null, $date_to = null) {
        try {
            $xml_request = array('OrderRole' => 'Seller', 'OrderStatus' => 'All');
            
            if ($date_from) {
                $xml_request['CreateTimeFrom'] = date('c', strtotime($date_from));
            }
            
            $response = $this->callTradingAPI('GetOrders', $xml_request);
            
            if (isset($response['OrderArray']['Order'])) {
                $orders = $response['OrderArray']['Order'];
                if (!isset($orders[0])) {
                    $orders = array($orders);
                }
                
                $this->logger->info('eBay siparişler alındı: ' . count($orders));
                return array('success' => true, 'orders' => $orders);
            }
            
            return array('success' => true, 'orders' => array());
            
        } catch (Exception $e) {
            $this->logger->error('eBay sipariş alma hatası: ' . $e->getMessage());
            return array('success' => false, 'error' => $e->getMessage());
        }
    }
    
    /**
     * Aktif ürünleri getir
     */
    public function getMyActiveItems($page = 1, $entries_per_page = 50) {
        try {
            $xml_request = array(
                'Pagination' => array(
                    'EntriesPerPage' => $entries_per_page,
                    'PageNumber' => $page
                )
            );
            
            $response = $this->callTradingAPI('GetMyeBaySelling', $xml_request);
            
            if (isset($response['ActiveList']['ItemArray']['Item'])) {
                $items = $response['ActiveList']['ItemArray']['Item'];
                
                // Tek ürün varsa array'e çevir
                if (!isset($items[0])) {
                    $items = array($items);
                }
                
                $this->logger->info('eBay aktif ürünler alındı', array(
                    'count' => count($items)
                ));
                
                return array(
                    'success' => true,
                    'items' => $items,
                    'pagination' => isset($response['ActiveList']['PaginationResult']) ? $response['ActiveList']['PaginationResult'] : array()
                );
            }
            
            return array(
                'success' => true,
                'items' => array()
            );
            
        } catch (Exception $e) {
            $this->logger->error('eBay aktif ürün alma hatası', array(
                'error' => $e->getMessage()
            ));
            
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Kategori listesi
     */
    public function getCategories($parent_id = null) {
        try {
            $xml_request = array(
                'DetailLevel' => 'ReturnAll',
                'ViewAllNodes' => true
            );
            
            if ($parent_id) {
                $xml_request['CategoryParent'] = $parent_id;
            }
            
            $response = $this->callTradingAPI('GetCategories', $xml_request);
            
            if (isset($response['CategoryArray']['Category'])) {
                $categories = $response['CategoryArray']['Category'];
                
                $this->logger->info('eBay kategoriler alındı', array(
                    'count' => count($categories)
                ));
                
                return array(
                    'success' => true,
                    'categories' => $categories
                );
            }
            
            return array(
                'success' => true,
                'categories' => array()
            );
            
        } catch (Exception $e) {
            $this->logger->error('eBay kategori alma hatası', array(
                'error' => $e->getMessage()
            ));
            
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Ürün detayı
     */
    public function getItem($item_id) {
        try {
            $xml_request = array(
                'ItemID' => $item_id,
                'DetailLevel' => 'ReturnAll'
            );
            
            $response = $this->callTradingAPI('GetItem', $xml_request);
            
            if (isset($response['Item'])) {
                $this->logger->info('eBay ürün detayı alındı', array(
                    'item_id' => $item_id
                ));
                
                return array(
                    'success' => true,
                    'item' => $response['Item']
                );
            }
            
            $error_msg = isset($response['Errors']) ? $response['Errors'][0]['LongMessage'] : 'Item not found';
            throw new Exception($error_msg);
            
        } catch (Exception $e) {
            $this->logger->error('eBay ürün detay alma hatası', array(
                'error' => $e->getMessage(),
                'item_id' => $item_id
            ));
            
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Trading API çağrısı
     */
    private function callTradingAPI($call_name, $request_data) {
        $headers = array(
            'X-EBAY-API-COMPATIBILITY-LEVEL: 967',
            'X-EBAY-API-DEV-NAME: ' . $this->dev_id,
            'X-EBAY-API-APP-NAME: ' . $this->app_id,
            'X-EBAY-API-CERT-NAME: ' . $this->cert_id,
            'X-EBAY-API-CALL-NAME: ' . $call_name,
            'X-EBAY-API-SITEID: 0',
            'Content-Type: text/xml'
        );
        
        $xml_request = '<?xml version="1.0" encoding="utf-8"?>';
        $xml_request .= '<' . $call_name . 'Request xmlns="urn:ebay:apis:eBLBaseComponents">';
        $xml_request .= '<RequesterCredentials>';
        $xml_request .= '<eBayAuthToken>' . $this->user_token . '</eBayAuthToken>';
        $xml_request .= '</RequesterCredentials>';
        
        if (is_array($request_data)) {
            $xml_request .= $this->arrayToXML($request_data);
        } else {
            $xml_request .= $request_data;
        }
        
        $xml_request .= '</' . $call_name . 'Request>';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->trading_endpoint);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            throw new Exception('cURL Error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($http_code !== 200) {
            throw new Exception('HTTP Error: ' . $http_code);
        }
        
        $xml = simplexml_load_string($response);
        if ($xml === false) {
            throw new Exception('Invalid XML response');
        }
        
        return json_decode(json_encode($xml), true);
    }
    
    /**
     * Ürün ekleme XML oluştur
     */
    private function buildAddItemXML($item_data) {
        $xml = '<Item>';
        $xml .= '<Title>' . htmlspecialchars($item_data['title']) . '</Title>';
        $xml .= '<Description><![CDATA[' . $item_data['description'] . ']]></Description>';
        $xml .= '<PrimaryCategory><CategoryID>' . $item_data['category_id'] . '</CategoryID></PrimaryCategory>';
        $xml .= '<StartPrice>' . $item_data['price'] . '</StartPrice>';
        $xml .= '<Quantity>' . $item_data['quantity'] . '</Quantity>';
        $xml .= '<Currency>USD</Currency>';
        $xml .= '<Country>US</Country>';
        $xml .= '<Location>' . (isset($item_data['location']) ? $item_data['location'] : 'United States') . '</Location>';
        $xml .= '<ListingType>FixedPriceItem</ListingType>';
        $xml .= '<ListingDuration>GTC</ListingDuration>';
        
        // Resimler
        if (isset($item_data['images']) && !empty($item_data['images'])) {
            $xml .= '<PictureDetails>';
            foreach ($item_data['images'] as $image_url) {
                $xml .= '<PictureURL>' . htmlspecialchars($image_url) . '</PictureURL>';
            }
            $xml .= '</PictureDetails>';
        }
        
        // Kargo
        if (isset($item_data['shipping'])) {
            $xml .= '<ShippingDetails>';
            $xml .= '<ShippingServiceOptions>';
            $xml .= '<ShippingServicePriority>1</ShippingServicePriority>';
            $xml .= '<ShippingService>' . $item_data['shipping']['service'] . '</ShippingService>';
            $xml .= '<ShippingServiceCost>' . $item_data['shipping']['cost'] . '</ShippingServiceCost>';
            $xml .= '</ShippingServiceOptions>';
            $xml .= '</ShippingDetails>';
        }
        
        // Return policy
        $xml .= '<ReturnPolicy>';
        $xml .= '<ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>';
        $xml .= '<RefundOption>MoneyBack</RefundOption>';
        $xml .= '<ReturnsWithinOption>Days_30</ReturnsWithinOption>';
        $xml .= '<ShippingCostPaidByOption>Buyer</ShippingCostPaidByOption>';
        $xml .= '</ReturnPolicy>';
        
        $xml .= '</Item>';
        
        return $xml;
    }
    
    /**
     * Ürün güncelleme XML oluştur
     */
    private function buildReviseItemXML($item_id, $update_data) {
        $xml = '<Item>';
        $xml .= '<ItemID>' . $item_id . '</ItemID>';
        
        if (isset($update_data['title'])) {
            $xml .= '<Title>' . htmlspecialchars($update_data['title']) . '</Title>';
        }
        
        if (isset($update_data['description'])) {
            $xml .= '<Description><![CDATA[' . $update_data['description'] . ']]></Description>';
        }
        
        if (isset($update_data['price'])) {
            $xml .= '<StartPrice>' . $update_data['price'] . '</StartPrice>';
        }
        
        if (isset($update_data['quantity'])) {
            $xml .= '<Quantity>' . $update_data['quantity'] . '</Quantity>';
        }
        
        $xml .= '</Item>';
        
        return $xml;
    }
    
    /**
     * Array'i XML'e çevir
     */
    private function arrayToXML($array, $xml = '') {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $xml .= '<' . $key . '>';
                $xml .= $this->arrayToXML($value);
                $xml .= '</' . $key . '>';
            } else {
                $xml .= '<' . $key . '>' . htmlspecialchars($value) . '</' . $key . '>';
            }
        }
        
        return $xml;
    }
}

?> 