<?php
/**
 * MesChain-Sync - Ozon API Entegrasyon Sınıfı
 * 
 * Bu sınıf, Ozon Marketplace API'si ile iletişim kurmak için gerekli metotları içerir.
 * Ürün ve sipariş yönetimi, kategori çekme, stok güncelleme gibi işlemleri sağlar.
 * 
 * @author      MesTech
 * @copyright   Copyright (c) 2023, MesTech
 * @license     MIT License
 * @version     1.0.0
 */
class EntegratorOzon {
    private $registry;
    private $apiKey;
    private $apiSecret;
    private $clientId;
    private $apiUrl;
    private $logger;
    private $config;
    private $db;
    private $log;
    
    /**
     * Sınıfı başlatır ve gerekli bağımlılıkları enjekte eder
     * 
     * @param Registry $registry OpenCart registry nesnesi
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $registry->get('config');
        $this->db = $registry->get('db');
        $this->log = $registry->get('log');
        
        // API bilgilerini yapılandırmadan yükle
        $this->apiKey = $this->config->get('module_mestech_ozon_api_key');
        $this->apiSecret = $this->config->get('module_mestech_ozon_api_secret');
        $this->clientId = $this->config->get('module_mestech_ozon_client_id');
        $this->apiUrl = $this->config->get('module_mestech_ozon_api_url') ?: 'https://api-seller.ozon.ru';
        
        // Logger sınıfını yükle
        $this->logger = new \Library\meschain\Logger('ozon');
    }
    
    /**
     * API bağlantısını test eder
     * 
     * @return bool Bağlantı başarılı ise true, değilse false
     */
    public function checkConnection() {
        try {
            $this->logger->info('API bağlantısı test ediliyor...');
            
            $response = $this->makeRequest('POST', '/v1/product/info');
            
            if (isset($response['error'])) {
                $this->logger->error('API bağlantı hatası: ' . json_encode($response['error']));
                return false;
            }
            
            $this->logger->info('API bağlantısı başarılı');
            return true;
        } catch (Exception $e) {
            $this->logger->error('API bağlantı hatası: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ürün listesini Ozon'dan çeker
     * 
     * @param array $parameters Filtreleme parametreleri
     * @return array Ürün listesi veya hata mesajı
     */
    public function getProducts($parameters = []) {
        try {
            $this->logger->info('Ürünler çekiliyor. Parametreler: ' . json_encode($parameters));
            
            $data = [
                'limit' => isset($parameters['limit']) ? (int)$parameters['limit'] : 100,
                'offset' => isset($parameters['offset']) ? (int)$parameters['offset'] : 0,
                'filter' => isset($parameters['filter']) ? $parameters['filter'] : [],
                'with' => [
                    'attributes' => true,
                    'images' => true,
                    'prices' => true,
                    'stocks' => true
                ]
            ];
            
            $response = $this->makeRequest('POST', '/v2/product/list', $data);
            
            if (isset($response['error'])) {
                $this->logger->error('Ürün çekme hatası: ' . json_encode($response['error']));
                return ['success' => false, 'error' => $response['error']['message']];
            }
            
            $this->logger->info('Ürünler başarıyla çekildi. Toplam: ' . (isset($response['result']['total']) ? $response['result']['total'] : 0));
            
            return [
                'success' => true,
                'products' => isset($response['result']['items']) ? $response['result']['items'] : [],
                'total' => isset($response['result']['total']) ? $response['result']['total'] : 0
            ];
        } catch (Exception $e) {
            $this->logger->error('Ürün çekme hatası: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Ürün detaylarını Ozon'dan çeker
     * 
     * @param int|array $product_id Ürün ID veya ID'ler dizisi
     * @return array Ürün detayları veya hata mesajı
     */
    public function getProductInfo($product_id) {
        try {
            $this->logger->info('Ürün detayları çekiliyor. ID: ' . (is_array($product_id) ? implode(',', $product_id) : $product_id));
            
            $data = [];
            
            if (is_array($product_id)) {
                $data['product_id'] = $product_id;
            } else {
                $data['product_id'] = [$product_id];
            }
            
            $response = $this->makeRequest('POST', '/v2/product/info/list', $data);
            
            if (isset($response['error'])) {
                $this->logger->error('Ürün detayları çekme hatası: ' . json_encode($response['error']));
                return ['success' => false, 'error' => $response['error']['message']];
            }
            
            $this->logger->info('Ürün detayları başarıyla çekildi.');
            
            return [
                'success' => true,
                'product' => isset($response['result']['items']) ? $response['result']['items'] : []
            ];
        } catch (Exception $e) {
            $this->logger->error('Ürün detayları çekme hatası: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Ozon'a yeni ürün ekler
     * 
     * @param array $product Ürün verileri
     * @return array İşlem sonucu
     */
    public function createProduct($product) {
        try {
            $this->logger->info('Yeni ürün oluşturuluyor. Ürün: ' . json_encode($product));
            
            $data = [
                'items' => [$product]
            ];
            
            $response = $this->makeRequest('POST', '/v2/product/import', $data);
            
            if (isset($response['error'])) {
                $this->logger->error('Ürün oluşturma hatası: ' . json_encode($response['error']));
                return ['success' => false, 'error' => $response['error']['message']];
            }
            
            $this->logger->info('Ürün başarıyla oluşturuldu. Yanıt: ' . json_encode($response['result']));
            
            return [
                'success' => true,
                'task_id' => isset($response['result']['task_id']) ? $response['result']['task_id'] : null
            ];
        } catch (Exception $e) {
            $this->logger->error('Ürün oluşturma hatası: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Ozon'daki ürünü günceller
     * 
     * @param array $product Ürün verileri
     * @return array İşlem sonucu
     */
    public function updateProduct($product) {
        try {
            $this->logger->info('Ürün güncelleniyor. Ürün: ' . json_encode($product));
            
            $data = [
                'items' => [$product]
            ];
            
            $response = $this->makeRequest('POST', '/v2/product/import', $data);
            
            if (isset($response['error'])) {
                $this->logger->error('Ürün güncelleme hatası: ' . json_encode($response['error']));
                return ['success' => false, 'error' => $response['error']['message']];
            }
            
            $this->logger->info('Ürün başarıyla güncellendi. Yanıt: ' . json_encode($response['result']));
            
            return [
                'success' => true,
                'task_id' => isset($response['result']['task_id']) ? $response['result']['task_id'] : null
            ];
        } catch (Exception $e) {
            $this->logger->error('Ürün güncelleme hatası: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Ürün stok bilgisini günceller
     * 
     * @param array $stocks Stok güncellemeleri
     * @return array İşlem sonucu
     */
    public function updateStocks($stocks) {
        try {
            $this->logger->info('Stok güncelleniyor. Stoklar: ' . json_encode($stocks));
            
            $data = [
                'stocks' => $stocks
            ];
            
            $response = $this->makeRequest('POST', '/v1/product/import/stocks', $data);
            
            if (isset($response['error'])) {
                $this->logger->error('Stok güncelleme hatası: ' . json_encode($response['error']));
                return ['success' => false, 'error' => $response['error']['message']];
            }
            
            $this->logger->info('Stok başarıyla güncellendi. Yanıt: ' . json_encode($response['result']));
            
            return [
                'success' => true,
                'task_id' => isset($response['result']['task_id']) ? $response['result']['task_id'] : null
            ];
        } catch (Exception $e) {
            $this->logger->error('Stok güncelleme hatası: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Ürün fiyat bilgisini günceller
     * 
     * @param array $prices Fiyat güncellemeleri
     * @return array İşlem sonucu
     */
    public function updatePrices($prices) {
        try {
            $this->logger->info('Fiyat güncelleniyor. Fiyatlar: ' . json_encode($prices));
            
            $data = [
                'prices' => $prices
            ];
            
            $response = $this->makeRequest('POST', '/v1/product/import/prices', $data);
            
            if (isset($response['error'])) {
                $this->logger->error('Fiyat güncelleme hatası: ' . json_encode($response['error']));
                return ['success' => false, 'error' => $response['error']['message']];
            }
            
            $this->logger->info('Fiyat başarıyla güncellendi. Yanıt: ' . json_encode($response['result']));
            
            return [
                'success' => true,
                'task_id' => isset($response['result']['task_id']) ? $response['result']['task_id'] : null
            ];
        } catch (Exception $e) {
            $this->logger->error('Fiyat güncelleme hatası: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Ozon'dan sipariş listesini çeker
     * 
     * @param array $parameters Filtreleme parametreleri
     * @return array Sipariş listesi veya hata mesajı
     */
    public function getOrders($parameters = []) {
        try {
            $this->logger->info('Siparişler çekiliyor. Parametreler: ' . json_encode($parameters));
            
            // Varsayılan başlangıç ve bitiş tarihleri (son 30 gün)
            $since = isset($parameters['since']) ? $parameters['since'] : date('Y-m-d\TH:i:s\Z', strtotime('-30 days'));
            $to = isset($parameters['to']) ? $parameters['to'] : date('Y-m-d\TH:i:s\Z');
            
            $data = [
                'dir' => isset($parameters['dir']) ? $parameters['dir'] : 'ASC',
                'filter' => [
                    'since' => $since,
                    'to' => $to,
                    'status' => isset($parameters['status']) ? $parameters['status'] : '',
                ],
                'limit' => isset($parameters['limit']) ? (int)$parameters['limit'] : 100,
                'offset' => isset($parameters['offset']) ? (int)$parameters['offset'] : 0,
                'with' => [
                    'analytics_data' => true,
                    'financial_data' => true
                ]
            ];
            
            $response = $this->makeRequest('POST', '/v2/posting/fbs/list', $data);
            
            if (isset($response['error'])) {
                $this->logger->error('Sipariş çekme hatası: ' . json_encode($response['error']));
                return ['success' => false, 'error' => $response['error']['message']];
            }
            
            $this->logger->info('Siparişler başarıyla çekildi. Toplam: ' . (isset($response['result']['total']) ? $response['result']['total'] : 0));
            
            return [
                'success' => true,
                'orders' => isset($response['result']['postings']) ? $response['result']['postings'] : [],
                'total' => isset($response['result']['total']) ? $response['result']['total'] : 0
            ];
        } catch (Exception $e) {
            $this->logger->error('Sipariş çekme hatası: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Sipariş detaylarını çeker
     * 
     * @param string $posting_number Sipariş numarası
     * @return array Sipariş detayları veya hata mesajı
     */
    public function getOrderInfo($posting_number) {
        try {
            $this->logger->info('Sipariş detayları çekiliyor. Sipariş Numarası: ' . $posting_number);
            
            $data = [
                'posting_number' => $posting_number,
                'with' => [
                    'analytics_data' => true,
                    'financial_data' => true
                ]
            ];
            
            $response = $this->makeRequest('POST', '/v2/posting/fbs/get', $data);
            
            if (isset($response['error'])) {
                $this->logger->error('Sipariş detayları çekme hatası: ' . json_encode($response['error']));
                return ['success' => false, 'error' => $response['error']['message']];
            }
            
            $this->logger->info('Sipariş detayları başarıyla çekildi.');
            
            return [
                'success' => true,
                'order' => isset($response['result']) ? $response['result'] : []
            ];
        } catch (Exception $e) {
            $this->logger->error('Sipariş detayları çekme hatası: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Sipariş durumunu günceller
     * 
     * @param string $posting_number Sipariş numarası
     * @param string $status Yeni durum
     * @return array İşlem sonucu
     */
    public function updateOrderStatus($posting_number, $status) {
        try {
            $this->logger->info('Sipariş durumu güncelleniyor. Sipariş Numarası: ' . $posting_number . ', Durum: ' . $status);
            
            $data = [
                'posting_number' => $posting_number,
                'status' => $status
            ];
            
            $response = $this->makeRequest('POST', '/v2/posting/fbs/status', $data);
            
            if (isset($response['error'])) {
                $this->logger->error('Sipariş durumu güncelleme hatası: ' . json_encode($response['error']));
                return ['success' => false, 'error' => $response['error']['message']];
            }
            
            $this->logger->info('Sipariş durumu başarıyla güncellendi.');
            
            return [
                'success' => true
            ];
        } catch (Exception $e) {
            $this->logger->error('Sipariş durumu güncelleme hatası: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Ozon'dan kategori listesini çeker
     * 
     * @param array $parameters Filtreleme parametreleri
     * @return array Kategori listesi veya hata mesajı
     */
    public function getCategories($parameters = []) {
        try {
            $this->logger->info('Kategoriler çekiliyor. Parametreler: ' . json_encode($parameters));
            
            $data = [
                'language' => isset($parameters['language']) ? $parameters['language'] : 'DEFAULT'
            ];
            
            $response = $this->makeRequest('POST', '/v2/category/tree', $data);
            
            if (isset($response['error'])) {
                $this->logger->error('Kategori çekme hatası: ' . json_encode($response['error']));
                return ['success' => false, 'error' => $response['error']['message']];
            }
            
            $this->logger->info('Kategoriler başarıyla çekildi.');
            
            return [
                'success' => true,
                'categories' => isset($response['result']) ? $response['result'] : []
            ];
        } catch (Exception $e) {
            $this->logger->error('Kategori çekme hatası: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Kategori özellikleri/niteliklerini çeker
     * 
     * @param int $category_id Kategori ID
     * @return array Kategori özellikleri veya hata mesajı
     */
    public function getCategoryAttributes($category_id) {
        try {
            $this->logger->info('Kategori özellikleri çekiliyor. Kategori ID: ' . $category_id);
            
            $data = [
                'category_id' => (int)$category_id,
                'language' => 'DEFAULT'
            ];
            
            $response = $this->makeRequest('POST', '/v2/category/attribute', $data);
            
            if (isset($response['error'])) {
                $this->logger->error('Kategori özellikleri çekme hatası: ' . json_encode($response['error']));
                return ['success' => false, 'error' => $response['error']['message']];
            }
            
            $this->logger->info('Kategori özellikleri başarıyla çekildi.');
            
            return [
                'success' => true,
                'attributes' => isset($response['result']) ? $response['result'] : []
            ];
        } catch (Exception $e) {
            $this->logger->error('Kategori özellikleri çekme hatası: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * API isteği gönderir
     * 
     * @param string $method HTTP metodu (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint'i
     * @param array $data İstek verileri
     * @return array API yanıtı
     * @throws Exception
     */
    private function makeRequest($method, $endpoint, $data = []) {
        if (empty($this->apiKey) || empty($this->clientId)) {
            throw new Exception('API kimlik bilgileri eksik. Lütfen ayarlarınızı kontrol edin.');
        }
        
        $url = rtrim($this->apiUrl, '/') . $endpoint;
        
        $headers = [
            'Client-Id: ' . $this->clientId,
            'Api-Key: ' . $this->apiKey,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('CURL Hatası: ' . $error);
        }
        
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $errorMessage = isset($decoded['message']) ? $decoded['message'] : 'API Hatası';
            throw new Exception('API Hatası (' . $httpCode . '): ' . $errorMessage);
        }
        
        return $decoded;
    }
    
    /**
     * Ürün verilerini OpenCart formatından Ozon formatına dönüştürür
     * 
     * @param array $opencart_product OpenCart ürün verileri
     * @return array Ozon formatında ürün verileri
     */
    public function formatProductForOzon($opencart_product) {
        $ozon_product = [
            'offer_id' => $opencart_product['model'],
            'name' => $opencart_product['name'],
            'description' => $opencart_product['description'],
            'category_id' => (int)$opencart_product['ozon_category_id'],
            'attributes' => [],
            'images' => [],
            'price' => $opencart_product['price'],
            'old_price' => isset($opencart_product['special']) ? $opencart_product['price'] : 0,
            'premium_price' => isset($opencart_product['premium_price']) ? $opencart_product['premium_price'] : 0,
            'vat' => isset($opencart_product['tax_class_id']) ? $this->mapTaxToVat($opencart_product['tax_class_id']) : '0',
            'weight' => isset($opencart_product['weight']) ? $opencart_product['weight'] : 0,
            'weight_unit' => isset($opencart_product['weight_class_id']) ? $this->mapWeightUnit($opencart_product['weight_class_id']) : 'g',
            'dimension_unit' => isset($opencart_product['length_class_id']) ? $this->mapDimensionUnit($opencart_product['length_class_id']) : 'mm',
            'width' => isset($opencart_product['width']) ? $opencart_product['width'] : 0,
            'height' => isset($opencart_product['height']) ? $opencart_product['height'] : 0,
            'depth' => isset($opencart_product['length']) ? $opencart_product['length'] : 0
        ];
        
        // Ürün resimleri
        if (!empty($opencart_product['image'])) {
            $ozon_product['images'][] = HTTPS_CATALOG . 'image/' . $opencart_product['image'];
        }
        
        if (!empty($opencart_product['product_image'])) {
            foreach ($opencart_product['product_image'] as $image) {
                $ozon_product['images'][] = HTTPS_CATALOG . 'image/' . $image['image'];
            }
        }
        
        // Ürün özellikleri
        if (!empty($opencart_product['product_attribute'])) {
            foreach ($opencart_product['product_attribute'] as $attribute) {
                $ozon_product['attributes'][] = [
                    'id' => (int)$attribute['ozon_attribute_id'],
                    'value' => $attribute['text']
                ];
            }
        }
        
        return $ozon_product;
    }
    
    /**
     * Ozon sipariş durumunu OpenCart sipariş durumuna dönüştürür
     * 
     * @param string $ozon_status Ozon sipariş durumu
     * @return int OpenCart sipariş durum ID'si
     */
    public function mapOzonStatusToOpencart($ozon_status) {
        $status_map = [
            'awaiting_packaging' => (int)$this->config->get('module_mestech_ozon_awaiting_packaging_status_id'),
            'awaiting_deliver' => (int)$this->config->get('module_mestech_ozon_awaiting_deliver_status_id'),
            'delivering' => (int)$this->config->get('module_mestech_ozon_delivering_status_id'),
            'delivered' => (int)$this->config->get('module_mestech_ozon_delivered_status_id'),
            'cancelled' => (int)$this->config->get('module_mestech_ozon_cancelled_status_id')
        ];
        
        return isset($status_map[$ozon_status]) ? $status_map[$ozon_status] : 1; // 1 = Beklemede (Varsayılan)
    }
    
    /**
     * OpenCart vergi sınıfını Ozon KDV oranına dönüştürür
     * 
     * @param int $tax_class_id OpenCart vergi sınıfı ID'si
     * @return string Ozon KDV oranı
     */
    private function mapTaxToVat($tax_class_id) {
        $tax_map = [
            0 => '0', // Vergisiz
            9 => '0.1', // %10
            10 => '0.2' // %20
        ];
        
        return isset($tax_map[$tax_class_id]) ? $tax_map[$tax_class_id] : '0.2'; // Varsayılan %20
    }
    
    /**
     * OpenCart ağırlık birimini Ozon ağırlık birimine dönüştürür
     * 
     * @param int $weight_class_id OpenCart ağırlık birimi ID'si
     * @return string Ozon ağırlık birimi
     */
    private function mapWeightUnit($weight_class_id) {
        $weight_map = [
            1 => 'kg', // Kilogram
            2 => 'g', // Gram
            5 => 'lb', // Pound
            6 => 'oz' // Ons
        ];
        
        return isset($weight_map[$weight_class_id]) ? $weight_map[$weight_class_id] : 'g'; // Varsayılan gram
    }
    
    /**
     * OpenCart boyut birimini Ozon boyut birimine dönüştürür
     * 
     * @param int $length_class_id OpenCart boyut birimi ID'si
     * @return string Ozon boyut birimi
     */
    private function mapDimensionUnit($length_class_id) {
        $dimension_map = [
            1 => 'cm', // Santimetre
            2 => 'mm', // Milimetre
            3 => 'in' // İnç
        ];
        
        return isset($dimension_map[$length_class_id]) ? $dimension_map[$length_class_id] : 'mm'; // Varsayılan milimetre
    }
} 