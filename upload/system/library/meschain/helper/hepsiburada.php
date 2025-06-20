<?php
/**
 * MeschainHepsiburadaHelper
 *
 * Hepsiburada API entegrasyonu için yardımcı sınıf.
 * Bu sınıf Hepsiburada API'si ile iletişim kurma, sipariş oluşturma ve ürün senkronizasyonu gibi işlemleri yönetir.
 */
class MeschainHepsiburadaHelper {
    private $registry;
    private $config;
    private $log;
    private $db;
    private $session;
    private $currency;
    private $apiUrl = 'https://listing-external.hepsiburada.com/';
    private $mpApiUrl = 'https://mpop-sit.hepsiburada.com/';
    private $merchantId;
    private $username;
    private $password;
    private $accessToken;
    private $refreshToken;
    private $logFile = 'hepsiburada_helper.log';

    /**
     * Kurucu metod
     *
     * @param object $registry OpenCart registry objesi
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $registry->get('config');
        $this->db = $registry->get('db');
        $this->session = $registry->get('session');
        $this->currency = $registry->get('currency');

        // Logger başlat
        $this->log = new Log($this->logFile);

        // API bilgilerini yükle
        $this->loadApiCredentials();
    }

    /**
     * API kimlik bilgilerini yükle
     */
    private function loadApiCredentials() {
        $this->merchantId = $this->config->get('module_hepsiburada_merchant_id');
        $this->username = $this->config->get('module_hepsiburada_username');
        $this->password = $this->config->get('module_hepsiburada_password');

        if (empty($this->merchantId) || empty($this->username) || empty($this->password)) {
            $this->writeLog('UYARI', 'Hepsiburada API kimlik bilgileri eksik');
        }
    }

    /**
     * Access token al
     *
     * @return string|false Access token
     */
    private function getAccessToken() {
        if (!empty($this->accessToken)) {
            return $this->accessToken;
        }

        try {
            $this->writeLog('INFO', 'Hepsiburada access token alınıyor');

            $authUrl = $this->mpApiUrl . 'user/merchant/login';

            $postData = [
                'merchantId' => $this->merchantId,
                'username' => $this->username,
                'password' => $this->password
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $authUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'User-Agent: MesChain-Sync/1.0'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new Exception('CURL Error: ' . $error);
            }

            curl_close($ch);

            if ($httpCode !== 200) {
                throw new Exception('HTTP Error: ' . $httpCode);
            }

            $tokenData = json_decode($response, true);

            if (isset($tokenData['data']['token'])) {
                $this->accessToken = $tokenData['data']['token'];
                $this->refreshToken = $tokenData['data']['refreshToken'] ?? '';
                $this->writeLog('BASARILI', 'Access token alındı');
                return $this->accessToken;
            } else {
                throw new Exception('Access token alınamadı: ' . json_encode($tokenData));
            }

        } catch (Exception $e) {
            $this->writeLog('HATA', 'Access token alınamadı: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hepsiburada API bağlantısını test et
     *
     * @return array Test sonucu
     */
    public function testConnection() {
        try {
            $this->writeLog('INFO', 'Hepsiburada API bağlantı testi başlatılıyor');

            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Access token alınamadı'
                ];
            }

            // Merchant bilgilerini test et
            $response = $this->makeApiRequest('merchants/current', [], 'GET');

            if (isset($response['data'])) {
                $this->writeLog('BASARILI', 'Hepsiburada API bağlantı testi başarılı');
                return [
                    'success' => true,
                    'message' => 'Hepsiburada API bağlantısı başarılı',
                    'data' => $response['data']
                ];
            } else {
                $errorMsg = isset($response['errors']) ? json_encode($response['errors']) : 'Bilinmeyen hata';
                $this->writeLog('HATA', 'Hepsiburada API bağlantı testi başarısız: ' . $errorMsg);
                return [
                    'success' => false,
                    'message' => 'Hepsiburada API bağlantısı başarısız: ' . $errorMsg
                ];
            }
        } catch (Exception $e) {
            $this->writeLog('HATA', 'Hepsiburada API bağlantı testi exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Bağlantı hatası: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Hepsiburada siparişlerini çek
     *
     * @param array $params Filtre parametreleri
     * @return array Siparişler
     */
    public function getOrders($params = []) {
        try {
            $this->writeLog('INFO', 'Hepsiburada siparişleri çekiliyor');

            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Access token alınamadı'
                ];
            }

            // Sipariş listesi parametreleri
            $queryParams = [
                'offset' => $params['offset'] ?? 0,
                'limit' => $params['limit'] ?? 100,
                'status' => $params['status'] ?? null,
                'beginDate' => $params['begin_date'] ?? date('Y-m-d', strtotime('-30 days')),
                'endDate' => $params['end_date'] ?? date('Y-m-d')
            ];

            // Boş değerleri temizle
            $queryParams = array_filter($queryParams, function($value) {
                return $value !== null && $value !== '';
            });

            $endpoint = 'orders?' . http_build_query($queryParams);
            $response = $this->makeApiRequest($endpoint, [], 'GET');

            if (isset($response['data']['orders'])) {
                $orders = $response['data']['orders'];
                $this->writeLog('BASARILI', count($orders) . ' Hepsiburada siparişi çekildi');
                return [
                    'success' => true,
                    'orders' => $orders,
                    'total' => count($orders),
                    'totalCount' => $response['data']['totalCount'] ?? count($orders)
                ];
            } else {
                $errorMsg = isset($response['errors']) ? json_encode($response['errors']) : 'Bilinmeyen hata';
                $this->writeLog('HATA', 'Hepsiburada sipariş çekme başarısız: ' . $errorMsg);
                return [
                    'success' => false,
                    'message' => $errorMsg
                ];
            }
        } catch (Exception $e) {
            $this->writeLog('HATA', 'Hepsiburada sipariş çekme exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Sipariş çekme hatası: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Hepsiburada sipariş detayını çek
     *
     * @param string $orderId Hepsiburada Order ID
     * @return array Sipariş detayı
     */
    public function getOrderDetail($orderId) {
        try {
            $this->writeLog('INFO', 'Hepsiburada sipariş detayı çekiliyor: ' . $orderId);

            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Access token alınamadı'
                ];
            }

            $response = $this->makeApiRequest('orders/' . $orderId, [], 'GET');

            if (isset($response['data'])) {
                $this->writeLog('BASARILI', 'Hepsiburada sipariş detayı çekildi: ' . $orderId);
                return [
                    'success' => true,
                    'order' => $response['data']
                ];
            } else {
                $errorMsg = isset($response['errors']) ? json_encode($response['errors']) : 'Sipariş bulunamadı';
                $this->writeLog('HATA', 'Hepsiburada sipariş detayı çekme başarısız: ' . $errorMsg);
                return [
                    'success' => false,
                    'message' => $errorMsg
                ];
            }
        } catch (Exception $e) {
            $this->writeLog('HATA', 'Hepsiburada sipariş detayı çekme exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Sipariş detayı çekme hatası: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Hepsiburada kategorilerini çek
     *
     * @return array Kategoriler
     */
    public function getCategories() {
        try {
            $this->writeLog('INFO', 'Hepsiburada kategorileri çekiliyor');

            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Access token alınamadı'
                ];
            }

            $response = $this->makeApiRequest('categories', [], 'GET');

            if (isset($response['data'])) {
                $categories = $response['data'];
                $this->writeLog('BASARILI', count($categories) . ' Hepsiburada kategorisi çekildi');
                return [
                    'success' => true,
                    'categories' => $categories
                ];
            } else {
                $errorMsg = isset($response['errors']) ? json_encode($response['errors']) : 'Bilinmeyen hata';
                $this->writeLog('HATA', 'Hepsiburada kategori çekme başarısız: ' . $errorMsg);
                return [
                    'success' => false,
                    'message' => $errorMsg
                ];
            }
        } catch (Exception $e) {
            $this->writeLog('HATA', 'Hepsiburada kategori çekme exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Kategori çekme hatası: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Hepsiburada'ya ürün gönder
     *
     * @param array $productData Ürün verisi
     * @return array Gönderme sonucu
     */
    public function sendProduct($productData) {
        try {
            $this->writeLog('INFO', 'Ürün Hepsiburada\'ya gönderiliyor: ' . $productData['merchantSku']);

            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Access token alınamadı'
                ];
            }

            $preparedData = $this->prepareProductData($productData);
            $response = $this->makeApiRequest('products', $preparedData, 'POST');

            if (isset($response['data'])) {
                $this->writeLog('BASARILI', 'Ürün Hepsiburada\'ya gönderildi: ' . $productData['merchantSku']);
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            } else {
                $errorMsg = isset($response['errors']) ? json_encode($response['errors']) : 'Bilinmeyen hata';
                $this->writeLog('HATA', 'Hepsiburada ürün gönderme başarısız: ' . $errorMsg);
                return [
                    'success' => false,
                    'message' => $errorMsg
                ];
            }
        } catch (Exception $e) {
            $this->writeLog('HATA', 'Hepsiburada ürün gönderme exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ürün gönderme hatası: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Hepsiburada stok güncelle
     *
     * @param array $stockUpdates Stok güncellemeleri
     * @return array Güncelleme sonucu
     */
    public function updateStock($stockUpdates) {
        try {
            $this->writeLog('INFO', 'Hepsiburada stok güncelleniyor');

            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Access token alınamadı'
                ];
            }

            $response = $this->makeApiRequest('products/stocks', $stockUpdates, 'POST');

            if (isset($response['data'])) {
                $this->writeLog('BASARILI', 'Hepsiburada stok güncellendi');
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            } else {
                $errorMsg = isset($response['errors']) ? json_encode($response['errors']) : 'Bilinmeyen hata';
                $this->writeLog('HATA', 'Hepsiburada stok güncelleme başarısız: ' . $errorMsg);
                return [
                    'success' => false,
                    'message' => $errorMsg
                ];
            }
        } catch (Exception $e) {
            $this->writeLog('HATA', 'Hepsiburada stok güncelleme exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Stok güncelleme hatası: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Hepsiburada fiyat güncelle
     *
     * @param array $priceUpdates Fiyat güncellemeleri
     * @return array Güncelleme sonucu
     */
    public function updatePrice($priceUpdates) {
        try {
            $this->writeLog('INFO', 'Hepsiburada fiyatları güncelleniyor');

            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Access token alınamadı'
                ];
            }

            $response = $this->makeApiRequest('products/prices', $priceUpdates, 'POST');

            if (isset($response['data'])) {
                $this->writeLog('BASARILI', 'Hepsiburada fiyatları güncellendi');
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            } else {
                $errorMsg = isset($response['errors']) ? json_encode($response['errors']) : 'Bilinmeyen hata';
                $this->writeLog('HATA', 'Hepsiburada fiyat güncelleme başarısız: ' . $errorMsg);
                return [
                    'success' => false,
                    'message' => $errorMsg
                ];
            }
        } catch (Exception $e) {
            $this->writeLog('HATA', 'Hepsiburada fiyat güncelleme exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Fiyat güncelleme hatası: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Hepsiburada siparişini OpenCart'a dönüştür
     *
     * @param array $hepsiburadaOrder Hepsiburada sipariş objesi
     * @return array OpenCart sipariş verisi
     */
    public function convertToOpenCartOrder($hepsiburadaOrder) {
        try {
            $this->writeLog('INFO', 'Hepsiburada siparişi OpenCart formatına dönüştürülüyor: ' . $hepsiburadaOrder['id']);

            // Müşteri bilgileri
            $customer = [
                'customer_id' => 0, // Guest order
                'customer_group_id' => $this->config->get('config_customer_group_id'),
                'firstname' => $this->extractFirstName($hepsiburadaOrder['customer']['name'] ?? 'Hepsiburada Customer'),
                'lastname' => $this->extractLastName($hepsiburadaOrder['customer']['name'] ?? 'Hepsiburada Customer'),
                'email' => $hepsiburadaOrder['customer']['email'] ?? 'customer@hepsiburada.com',
                'telephone' => $hepsiburadaOrder['shippingAddress']['phone'] ?? '',
                'custom_field' => []
            ];

            // Adres bilgileri
            $paymentAddress = $this->extractAddress($hepsiburadaOrder['billingAddress'] ?? []);
            $shippingAddress = $this->extractAddress($hepsiburadaOrder['shippingAddress'] ?? []);

            // Ürünler
            $products = [];
            foreach ($hepsiburadaOrder['items'] as $item) {
                $products[] = $this->convertHepsiburadaProduct($item);
            }

            // Sipariş toplamları
            $totals = $this->calculateOrderTotals($hepsiburadaOrder);

            $openCartOrder = [
                'invoice_prefix' => $this->config->get('config_invoice_prefix'),
                'store_id' => 0,
                'store_name' => $this->config->get('config_name'),
                'store_url' => $this->config->get('config_url'),
                'customer_id' => $customer['customer_id'],
                'customer_group_id' => $customer['customer_group_id'],
                'firstname' => $customer['firstname'],
                'lastname' => $customer['lastname'],
                'email' => $customer['email'],
                'telephone' => $customer['telephone'],
                'custom_field' => $customer['custom_field'],
                'payment_firstname' => $paymentAddress['firstname'],
                'payment_lastname' => $paymentAddress['lastname'],
                'payment_company' => $paymentAddress['company'],
                'payment_address_1' => $paymentAddress['address_1'],
                'payment_address_2' => $paymentAddress['address_2'],
                'payment_city' => $paymentAddress['city'],
                'payment_postcode' => $paymentAddress['postcode'],
                'payment_country' => $paymentAddress['country'],
                'payment_country_id' => $paymentAddress['country_id'],
                'payment_zone' => $paymentAddress['zone'],
                'payment_zone_id' => $paymentAddress['zone_id'],
                'payment_address_format' => $paymentAddress['address_format'],
                'payment_custom_field' => [],
                'payment_method' => 'Hepsiburada Pay',
                'payment_code' => 'hepsiburada_pay',
                'shipping_firstname' => $shippingAddress['firstname'],
                'shipping_lastname' => $shippingAddress['lastname'],
                'shipping_company' => $shippingAddress['company'],
                'shipping_address_1' => $shippingAddress['address_1'],
                'shipping_address_2' => $shippingAddress['address_2'],
                'shipping_city' => $shippingAddress['city'],
                'shipping_postcode' => $shippingAddress['postcode'],
                'shipping_country' => $shippingAddress['country'],
                'shipping_country_id' => $shippingAddress['country_id'],
                'shipping_zone' => $shippingAddress['zone'],
                'shipping_zone_id' => $shippingAddress['zone_id'],
                'shipping_address_format' => $shippingAddress['address_format'],
                'shipping_custom_field' => [],
                'shipping_method' => 'Hepsiburada Shipping',
                'shipping_code' => 'hepsiburada_shipping',
                'products' => $products,
                'totals' => $totals,
                'comment' => 'Hepsiburada Order ID: ' . $hepsiburadaOrder['id'],
                'total' => $hepsiburadaOrder['totalAmount'] ?? 0,
                'affiliate_id' => 0,
                'commission' => 0,
                'marketing_id' => 0,
                'tracking' => '',
                'language_id' => $this->config->get('config_language_id'),
                'currency_id' => $this->currency->getId('TRY'),
                'currency_code' => 'TRY',
                'currency_value' => 1.0,
                'ip' => '',
                'forwarded_ip' => '',
                'user_agent' => 'Hepsiburada API',
                'accept_language' => '',
                'date_added' => $hepsiburadaOrder['orderDate'] ?? date('Y-m-d H:i:s'),
                'date_modified' => date('Y-m-d H:i:s'),
                'order_status_id' => $this->getOrderStatusId($hepsiburadaOrder['status']),
                'order_status' => $this->getOrderStatusName($hepsiburadaOrder['status'])
            ];

            $this->writeLog('BASARILI', 'Hepsiburada siparişi OpenCart formatına dönüştürüldü');
            return [
                'success' => true,
                'order' => $openCartOrder
            ];

        } catch (Exception $e) {
            $this->writeLog('HATA', 'Sipariş dönüştürme hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Sipariş dönüştürme hatası: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Hepsiburada API'ye istek gönder
     *
     * @param string $endpoint API endpoint
     * @param array $data İstek verisi
     * @param string $method HTTP metodu
     * @return array API yanıtı
     */
    private function makeApiRequest($endpoint, $data = [], $method = 'GET') {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            throw new Exception('Access token alınamadı');
        }

        $url = $this->mpApiUrl . $endpoint;

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'User-Agent: MesChain-Sync/1.0'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('CURL Error: ' . $error);
        }

        curl_close($ch);

        if ($httpCode >= 400) {
            throw new Exception('HTTP Error: ' . $httpCode . ' - ' . $response);
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON Decode Error: ' . json_last_error_msg());
        }

        return $decodedResponse;
    }

    /**
     * Log yaz
     *
     * @param string $level Log seviyesi
     * @param string $message Mesaj
     */
    private function writeLog($level, $message) {
        $this->log->write('[' . $level . '] ' . $message);
    }

    /**
     * İsimden ad çıkar
     */
    private function extractFirstName($fullName) {
        $parts = explode(' ', trim($fullName));
        return isset($parts[0]) ? $parts[0] : 'Hepsiburada';
    }

    /**
     * İsimden soyad çıkar
     */
    private function extractLastName($fullName) {
        $parts = explode(' ', trim($fullName));
        if (count($parts) > 1) {
            return implode(' ', array_slice($parts, 1));
        }
        return 'Customer';
    }

    /**
     * Hepsiburada durumunu OpenCart sipariş durumuna çevir
     */
    private function getOrderStatusId($hepsiburadaStatus) {
        $statusMap = [
            'Approved' => 2,          // Processing
            'Shipped' => 3,           // Shipped
            'Delivered' => 5,         // Complete
            'Cancelled' => 7,         // Cancelled
            'Returned' => 11,         // Refunded
            'Processing' => 2,        // Processing
            'Confirmed' => 2          // Processing
        ];

        return isset($statusMap[$hepsiburadaStatus]) ? $statusMap[$hepsiburadaStatus] : 1;
    }

    /**
     * Hepsiburada durumunu OpenCart sipariş durum adına çevir
     */
    private function getOrderStatusName($hepsiburadaStatus) {
        $statusMap = [
            'Approved' => 'Processing',
            'Shipped' => 'Shipped',
            'Delivered' => 'Complete',
            'Cancelled' => 'Cancelled',
            'Returned' => 'Refunded',
            'Processing' => 'Processing',
            'Confirmed' => 'Processing'
        ];

        return isset($statusMap[$hepsiburadaStatus]) ? $statusMap[$hepsiburadaStatus] : 'Pending';
    }

    /**
     * Hepsiburada ürünü OpenCart ürünü formatına çevir
     */
    private function convertHepsiburadaProduct($hepsiburadaItem) {
        // Ürün ID'sini merchantSku'dan bul
        $productId = $this->findProductByMerchantSku($hepsiburadaItem['merchantSku'] ?? '');

        return [
            'product_id' => $productId,
            'name' => $hepsiburadaItem['productName'] ?? 'Hepsiburada Product',
            'model' => $hepsiburadaItem['merchantSku'] ?? '',
            'quantity' => $hepsiburadaItem['quantity'] ?? 1,
            'price' => $hepsiburadaItem['price'] ?? 0,
            'total' => ($hepsiburadaItem['price'] ?? 0) * ($hepsiburadaItem['quantity'] ?? 1),
            'tax' => $hepsiburadaItem['vatAmount'] ?? 0,
            'reward' => 0
        ];
    }

    /**
     * MerchantSku'ya göre ürün bul
     */
    private function findProductByMerchantSku($merchantSku) {
        $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE model = '" . $this->db->escape($merchantSku) . "'");
        return $query->num_rows ? $query->row['product_id'] : 0;
    }

    /**
     * Adres bilgilerini çıkar
     */
    private function extractAddress($addressData) {
        return [
            'firstname' => $this->extractFirstName($addressData['firstName'] ?? $addressData['name'] ?? ''),
            'lastname' => $this->extractLastName($addressData['lastName'] ?? $addressData['name'] ?? ''),
            'company' => $addressData['company'] ?? '',
            'address_1' => $addressData['address'] ?? $addressData['address1'] ?? '',
            'address_2' => $addressData['address2'] ?? '',
            'city' => $addressData['city'] ?? '',
            'postcode' => $addressData['zipCode'] ?? '',
            'country' => $addressData['country'] ?? 'TR',
            'country_id' => $this->getCountryId($addressData['country'] ?? 'TR'),
            'zone' => $addressData['district'] ?? '',
            'zone_id' => $this->getZoneId($addressData['district'] ?? '', $addressData['country'] ?? 'TR'),
            'address_format' => ''
        ];
    }

    /**
     * Ülke ID'sini bul
     */
    private function getCountryId($countryCode) {
        $query = $this->db->query("SELECT country_id FROM " . DB_PREFIX . "country WHERE iso_code_2 = '" . $this->db->escape($countryCode) . "'");
        return $query->num_rows ? $query->row['country_id'] : 215; // Turkey default
    }

    /**
     * Bölge ID'sini bul
     */
    private function getZoneId($zoneName, $countryCode) {
        $countryId = $this->getCountryId($countryCode);
        if ($countryId) {
            $query = $this->db->query("SELECT zone_id FROM " . DB_PREFIX . "zone WHERE name = '" . $this->db->escape($zoneName) . "' AND country_id = '" . (int)$countryId . "'");
            return $query->num_rows ? $query->row['zone_id'] : 0;
        }
        return 0;
    }

    /**
     * Sipariş toplamlarını hesapla
     */
    private function calculateOrderTotals($hepsiburadaOrder) {
        $totals = [];

        $totalAmount = $hepsiburadaOrder['totalAmount'] ?? 0;
        $shippingAmount = $hepsiburadaOrder['shippingAmount'] ?? 0;
        $subTotal = $totalAmount - $shippingAmount;

        // Alt toplam
        $totals[] = [
            'code' => 'sub_total',
            'title' => 'Sub-Total',
            'value' => $subTotal,
            'sort_order' => 1
        ];

        // Kargo
        if ($shippingAmount > 0) {
            $totals[] = [
                'code' => 'shipping',
                'title' => 'Shipping',
                'value' => $shippingAmount,
                'sort_order' => 2
            ];
        }

        // Toplam
        $totals[] = [
            'code' => 'total',
            'title' => 'Total',
            'value' => $totalAmount,
            'sort_order' => 9
        ];

        return $totals;
    }

    /**
     * Ürün verisini Hepsiburada formatına hazırla
     */
    private function prepareProductData($productData) {
        return [
            'merchantSku' => $productData['model'],
            'hepsiburadaSku' => $productData['hepsiburada_sku'] ?? '',
            'VaryantGroupId' => $productData['variant_group_id'] ?? '',
            'Barcode' => $productData['barcode'] ?? $productData['ean'] ?? '',
            'Title' => $productData['name'],
            'ProductDescription' => $productData['description'] ?? '',
            'Brand' => $productData['manufacturer'] ?? '',
            'CategoryId' => $productData['hepsiburada_category_id'] ?? 0,
            'Price' => $productData['price'] ?? 0,
            'AvailableStock' => $productData['quantity'] ?? 0,
            'DispatchTime' => $productData['dispatch_time'] ?? 1,
            'CargoCompanyId' => $productData['cargo_company_id'] ?? 1,
            'Images' => isset($productData['images']) ? $productData['images'] : [],
            'Attributes' => isset($productData['attributes']) ? $productData['attributes'] : []
        ];
    }
}
