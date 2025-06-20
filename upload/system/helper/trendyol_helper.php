<?php
/**
 * trendyol_helper.php
 *
 * Amaç: Trendyol API entegrasyonu için yardımcı sınıf ve fonksiyonlar
 *
 * Bu dosya, Trendyol API'si ile iletişim kurmak için gerekli fonksiyonları içerir.
 * Ürün, sipariş, stok ve fiyat yönetimi için metotlar barındırır.
 */

// Trendyol API bağlantı fonksiyonu (eski sürüm için uyumluluk)
function trendyol_baglan($api_key, $api_secret) {
    $helper = new TrendyolHelper($api_key, $api_secret, null);
    return $helper->testConnection();
}

// Trendyol'a ürün gönderme fonksiyonu (eski sürüm için uyumluluk)
function trendyol_urun_gonder($product, $api_key, $api_secret, $supplier_id) {
    $helper = new TrendyolHelper($api_key, $api_secret, $supplier_id);
    return $helper->sendProduct($product);
}

// Trendyol'dan sipariş çekme fonksiyonu (eski sürüm için uyumluluk)
function trendyol_siparis_cek($date_range, $api_key, $api_secret, $supplier_id) {
    $helper = new TrendyolHelper($api_key, $api_secret, $supplier_id);
    $dates = explode(',', $date_range);

    if (count($dates) == 2) {
        return $helper->getOrders('Created', 0, 100, $dates[0], $dates[1]);
    }

    return $helper->getOrders('Created', 0, 100);
}

/**
 * Trendyol API yardımcı sınıfı
 */
class TrendyolHelper {
    private $api_key;
    private $api_secret;
    private $supplier_id;
    private $api_url = 'https://api.trendyol.com/sapigw/';
    private $log_file;

    /**
     * Constructor
     *
     * @param string $api_key API anahtarı
     * @param string $api_secret API gizli anahtarı
     * @param string $supplier_id Tedarikçi ID
     */
    public function __construct($api_key, $api_secret, $supplier_id) {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->supplier_id = $supplier_id;
        $this->log_file = DIR_LOGS . 'trendyol_helper.log';
    }

    /**
     * API bağlantısını test et
     *
     * @return boolean
     */
    public function testConnection() {
        try {
            // Suppliers endpoint'ini kullanarak bağlantıyı test et
            $url = $this->api_url . 'suppliers/' . $this->supplier_id . '/products?size=1';
            $response = $this->makeRequest($url, 'GET');

            if ($response && isset($response['totalElements'])) {
                $this->log('API_TEST', 'Bağlantı testi başarılı.');
                return true;
            }

            $this->log('API_TEST', 'Bağlantı testi başarısız.');
            return false;
        } catch (Exception $e) {
            $this->log('API_TEST_HATA', 'Bağlantı testi hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ürün gönder
     *
     * @param array $product Ürün bilgileri
     * @return array|boolean
     */
    public function sendProduct($product) {
        try {
            $url = $this->api_url . 'suppliers/' . $this->supplier_id . '/v2/products';
            $data = array('items' => array($product));

            $response = $this->makeRequest($url, 'POST', $data);

            if ($response && isset($response['batchId'])) {
                $this->log('URUN_GONDER', 'Ürün gönderildi. Batch ID: ' . $response['batchId']);

                // Batch durumunu kontrol et
                $batch_status = $this->checkBatchStatus($response['batchId']);

                if ($batch_status && isset($batch_status['status']) && $batch_status['status'] == 'COMPLETED') {
                    // Batch işlemi tamamlandı, ürün ID'si dönebilir
                    if (isset($batch_status['items']) && !empty($batch_status['items'])) {
                        $item = $batch_status['items'][0];

                        if (isset($item['productId'])) {
                            $response['productId'] = $item['productId'];
                        }
                    }
                }

                return $response;
            }

            $this->log('URUN_GONDER', 'Ürün gönderilemedi.');
            return false;
        } catch (Exception $e) {
            $this->log('URUN_GONDER_HATA', 'Ürün gönderme hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Siparişleri getir
     *
     * @param string $status Sipariş durumu
     * @param int $page Sayfa numarası
     * @param int $size Sayfa boyutu
     * @param string $startDate Başlangıç tarihi
     * @param string $endDate Bitiş tarihi
     * @return array|boolean
     */
    public function getOrders($status = 'Created', $page = 0, $size = 100, $startDate = null, $endDate = null) {
        try {
            $url = $this->api_url . 'suppliers/' . $this->supplier_id . '/orders';

            // Query parametreleri
            $params = array(
                'status' => $status,
                'page' => $page,
                'size' => $size
            );

            if ($startDate) {
                $params['startDate'] = $startDate;
            }

            if ($endDate) {
                $params['endDate'] = $endDate;
            }

            $url .= '?' . http_build_query($params);

            $response = $this->makeRequest($url, 'GET');

            if ($response) {
                $this->log('SIPARIS_GETIR', 'Siparişler getirildi. Sayfa: ' . $page . ', Toplam: ' . ($response['totalElements'] ?? 0));
                return $response;
            }

            $this->log('SIPARIS_GETIR', 'Siparişler getirilemedi.');
            return false;
        } catch (Exception $e) {
            $this->log('SIPARIS_GETIR_HATA', 'Sipariş getirme hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Stok güncelle
     *
     * @param array $stockItems Stok güncellemeleri
     * @return array|boolean
     */
    public function updateStock($stockItems) {
        try {
            $url = $this->api_url . 'suppliers/' . $this->supplier_id . '/products/price-and-inventory';
            $data = array('items' => $stockItems);

            $response = $this->makeRequest($url, 'POST', $data);

            if ($response && isset($response['batchId'])) {
                $this->log('STOK_GUNCELLE', 'Stok güncellemesi gönderildi. Batch ID: ' . $response['batchId']);
                return $response;
            }

            $this->log('STOK_GUNCELLE', 'Stok güncellemesi gönderilemedi.');
            return false;
        } catch (Exception $e) {
            $this->log('STOK_GUNCELLE_HATA', 'Stok güncelleme hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fiyat güncelle
     *
     * @param array $priceItems Fiyat güncellemeleri
     * @return array|boolean
     */
    public function updatePrices($priceItems) {
        try {
            $url = $this->api_url . 'suppliers/' . $this->supplier_id . '/products/price-and-inventory';
            $data = array('items' => $priceItems);

            $response = $this->makeRequest($url, 'POST', $data);

            if ($response && isset($response['batchId'])) {
                $this->log('FIYAT_GUNCELLE', 'Fiyat güncellemesi gönderildi. Batch ID: ' . $response['batchId']);
                return $response;
            }

            $this->log('FIYAT_GUNCELLE', 'Fiyat güncellemesi gönderilemedi.');
            return false;
        } catch (Exception $e) {
            $this->log('FIYAT_GUNCELLE_HATA', 'Fiyat güncelleme hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch durumunu kontrol et
     *
     * @param string $batchId Batch ID
     * @return array|boolean
     */
    public function checkBatchStatus($batchId) {
        try {
            $url = $this->api_url . 'suppliers/' . $this->supplier_id . '/products/batch-requests/' . $batchId;

            $response = $this->makeRequest($url, 'GET');

            if ($response) {
                $this->log('BATCH_DURUM', 'Batch durumu alındı. Batch ID: ' . $batchId . ', Durum: ' . ($response['status'] ?? 'UNKNOWN'));
                return $response;
            }

            $this->log('BATCH_DURUM', 'Batch durumu alınamadı.');
            return false;
        } catch (Exception $e) {
            $this->log('BATCH_DURUM_HATA', 'Batch durum kontrolü hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Trendyol kategorilerini getir
     *
     * @return array|boolean
     */
    public function getCategories() {
        try {
            $url = $this->api_url . 'product-categories';

            $response = $this->makeRequest($url, 'GET');

            if ($response && isset($response['categories'])) {
                $this->log('KATEGORI_GETIR', 'Kategoriler getirildi. Toplam: ' . count($response['categories']));
                return $response['categories'];
            }

            $this->log('KATEGORI_GETIR', 'Kategoriler getirilemedi.');
            return array();
        } catch (Exception $e) {
            $this->log('KATEGORI_GETIR_HATA', 'Kategori getirme hatası: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Trendyol markalarını getir
     *
     * @return array|boolean
     */
    public function getBrands() {
        try {
            $url = $this->api_url . 'brands';

            $response = $this->makeRequest($url, 'GET');

            if ($response && isset($response['brands'])) {
                $this->log('MARKA_GETIR', 'Markalar getirildi. Toplam: ' . count($response['brands']));
                return $response['brands'];
            }

            $this->log('MARKA_GETIR', 'Markalar getirilemedi.');
            return array();
        } catch (Exception $e) {
            $this->log('MARKA_GETIR_HATA', 'Marka getirme hatası: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * API isteği yap
     *
     * @param string $url URL
     * @param string $method HTTP metodu (GET, POST, PUT, DELETE)
     * @param array $data İstek verisi (POST ve PUT için)
     * @return array|boolean
     */
    private function makeRequest($url, $method = 'GET', $data = null) {
        $curl = curl_init();

        // HTTP başlıkları
        $headers = array(
            'Authorization: Basic ' . base64_encode($this->api_key . ':' . $this->api_secret),
            'Content-Type: application/json',
            'User-Agent: MesChain-Sync/1.0'
        );

        // CURL ayarları
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        // HTTP metoduna göre ayarlar
        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        } else if ($method == 'PUT') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        } else if ($method == 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        // İsteği yap
        $response = curl_exec($curl);
        $error = curl_error($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        // Hata kontrolü
        if ($error) {
            $this->log('API_ISTEK_HATA', 'CURL hatası: ' . $error);
            throw new Exception('CURL hatası: ' . $error);
        }

        // HTTP durum kodu kontrolü
        if ($status >= 400) {
            $this->log('API_ISTEK_HATA', 'HTTP hata kodu: ' . $status . ', Yanıt: ' . $response);
            throw new Exception('HTTP hata kodu: ' . $status . ', Yanıt: ' . $response);
        }

        // Yanıtı JSON olarak çözümle
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->log('API_ISTEK_HATA', 'JSON çözümleme hatası: ' . json_last_error_msg());
            throw new Exception('JSON çözümleme hatası: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Log kaydı oluştur
     *
     * @param string $action İşlem
     * @param string $message Mesaj
     */
    private function log($action, $message) {
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [API] [$action] $message\n";
        file_put_contents($this->log_file, $log, FILE_APPEND);
    }
}
