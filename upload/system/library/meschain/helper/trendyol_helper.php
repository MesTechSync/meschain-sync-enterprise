<?php
/**
 * trendyol_helper.php
 *
 * Amaç: Trendyol API işlemleri ve yardımcı fonksiyonlar.
 *
 * Loglama: Tüm önemli işlemler ve hatalar trendyol_helper.log dosyasına kaydedilir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 *
 * Geliştirici: Her fonksiyon başında açıklama ve log şablonu bulunmalıdır.
 */

class MeschainTrendyolHelper {
    private $api_key;
    private $api_secret;
    private $supplier_id;
    private $api_url = 'https://api.trendyol.com/sapigw';
    
    /**
     * Constructor
     * @param string $api_key
     * @param string $api_secret
     * @param string $supplier_id
     */
    public function __construct($api_key, $api_secret, $supplier_id) {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->supplier_id = $supplier_id;
    }
    
    /**
     * Trendyol API'ye bağlantı kurar
     * @return bool Başarı durumu
     */
    public function testConnection() {
        $result = false;
        try {
            // API bağlantı testi
            $url = $this->api_url . '/suppliers/brands';
            $headers = [
                'Authorization: Basic ' . base64_encode($this->api_key . ':' . $this->api_secret),
                'Content-Type: application/json'
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpcode == 200) {
                $result = true;
                $this->writeLog('SISTEM', 'BAGLAN', 'Trendyol API bağlantısı başarılı.');
            } else {
                $this->writeLog('SISTEM', 'HATA', 'Bağlantı hatası: HTTP ' . $httpcode);
            }
        } catch (Exception $e) {
            $this->writeLog('SISTEM', 'HATA', 'Bağlantı hatası: ' . $e->getMessage());
        }
        return $result;
    }

    /**
     * Trendyol'a ürün gönderir
     * @param array $urun Ürün verisi
     * @return bool
     */
    public function sendProduct($product) {
        try {
            $url = $this->api_url . '/suppliers/' . $this->supplier_id . '/products';
            $headers = [
                'Authorization: Basic ' . base64_encode($this->api_key . ':' . $this->api_secret),
                'Content-Type: application/json'
            ];
            
            // Ürün verisini hazırla
            $product_data = [
                'items' => [$product]
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($product_data));
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpcode == 200) {
                $this->writeLog('SISTEM', 'URUN_GONDER', 'Ürün başarıyla gönderildi: ' . json_encode($product));
                return true;
            } else {
                $this->writeLog('SISTEM', 'HATA', 'Ürün gönderme hatası: HTTP ' . $httpcode . ' - ' . $response);
                return false;
            }
        } catch (Exception $e) {
            $this->writeLog('SISTEM', 'HATA', 'Ürün gönderme hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Trendyol'dan sipariş çeker
     * @param string $startDate Başlangıç tarihi (YYYY-MM-DD)
     * @param string $endDate Bitiş tarihi (YYYY-MM-DD)
     * @return array
     */
    public function getOrders($startDate = null, $endDate = null) {
        try {
            if (!$startDate) {
                $startDate = date('Y-m-d', strtotime('-7 days'));
            }
            
            if (!$endDate) {
                $endDate = date('Y-m-d');
            }
            
            // ISO 8601 formatına dönüştür
            $startDateFormatted = $startDate . 'T00:00:00.000Z';
            $endDateFormatted = $endDate . 'T23:59:59.999Z';
            
            $url = $this->api_url . '/suppliers/' . $this->supplier_id . '/orders';
            $url .= '?startDate=' . urlencode($startDateFormatted) . '&endDate=' . urlencode($endDateFormatted);
            
            $headers = [
                'Authorization: Basic ' . base64_encode($this->api_key . ':' . $this->api_secret),
                'Content-Type: application/json'
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpcode == 200) {
                $siparisler = json_decode($response, true);
                $this->writeLog('SISTEM', 'SIPARIS_CEK', 'Siparişler başarıyla çekildi: ' . $startDate . ' - ' . $endDate);
                return $siparisler;
            } else {
                $this->writeLog('SISTEM', 'HATA', 'Sipariş çekme hatası: HTTP ' . $httpcode . ' - ' . $response);
                return array();
            }
        } catch (Exception $e) {
            $this->writeLog('SISTEM', 'HATA', 'Sipariş çekme hatası: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Sipariş detaylarını çeker
     * @param string $order_id Sipariş ID
     * @return array|false
     */
    public function getOrderDetails($order_id) {
        try {
            $url = $this->api_url . '/suppliers/' . $this->supplier_id . '/orders/' . $order_id;
            
            $headers = [
                'Authorization: Basic ' . base64_encode($this->api_key . ':' . $this->api_secret),
                'Content-Type: application/json'
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpcode == 200) {
                $siparis = json_decode($response, true);
                $this->writeLog('SISTEM', 'SIPARIS_DETAY', 'Sipariş detayları başarıyla çekildi: ' . $order_id);
                return $siparis;
            } else {
                $this->writeLog('SISTEM', 'HATA', 'Sipariş detayları çekme hatası: HTTP ' . $httpcode . ' - ' . $response);
                return false;
            }
        } catch (Exception $e) {
            $this->writeLog('SISTEM', 'HATA', 'Sipariş detayları çekme hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Stok güncelleme fonksiyonu
     * @param array $stok_bilgisi Stok bilgisi (barcode, quantity)
     * @return bool
     */
    public function updateStock($stock_info) {
        try {
            $url = $this->api_url . '/suppliers/' . $this->supplier_id . '/products/price-and-inventory';
            $headers = [
                'Authorization: Basic ' . base64_encode($this->api_key . ':' . $this->api_secret),
                'Content-Type: application/json'
            ];
            
            // Stok verisini hazırla
            $inventory_data = [
                'items' => [$stock_info]
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($inventory_data));
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpcode == 200) {
                $this->writeLog('SISTEM', 'STOK_GUNCELLE', 'Stok başarıyla güncellendi: ' . json_encode($stock_info));
                return true;
            } else {
                $this->writeLog('SISTEM', 'HATA', 'Stok güncelleme hatası: HTTP ' . $httpcode . ' - ' . $response);
                return false;
            }
        } catch (Exception $e) {
            $this->writeLog('SISTEM', 'HATA', 'Stok güncelleme hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fiyat güncelleme fonksiyonu
     * @param array $fiyat_bilgisi Fiyat bilgisi (barcode, price, discountedPrice)
     * @return bool
     */
    public function updatePrice($price_info) {
        try {
            $url = $this->api_url . '/suppliers/' . $this->supplier_id . '/products/price-and-inventory';
            $headers = [
                'Authorization: Basic ' . base64_encode($this->api_key . ':' . $this->api_secret),
                'Content-Type: application/json'
            ];
            
            // Fiyat verisini hazırla
            $price_data = [
                'items' => [$price_info]
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($price_data));
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpcode == 200) {
                $this->writeLog('SISTEM', 'FIYAT_GUNCELLE', 'Fiyat başarıyla güncellendi: ' . json_encode($price_info));
                return true;
            } else {
                $this->writeLog('SISTEM', 'HATA', 'Fiyat güncelleme hatası: HTTP ' . $httpcode . ' - ' . $response);
                return false;
            }
        } catch (Exception $e) {
            $this->writeLog('SISTEM', 'HATA', 'Fiyat güncelleme hatası: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sipariş durumu güncelleme fonksiyonu
     * @param string $siparis_no Sipariş numarası
     * @param string $durum Yeni durum
     * @return bool
     */
    public function updateOrderStatus($order_number, $status) {
        try {
            $url = $this->api_url . '/suppliers/' . $this->supplier_id . '/orders';
            $headers = [
                'Authorization: Basic ' . base64_encode($this->api_key . ':' . $this->api_secret),
                'Content-Type: application/json'
            ];
            
            // Sipariş durumu verisini hazırla
            $status_data = [
                'orderNumber' => $order_number,
                'status' => $status
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($status_data));
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpcode == 200) {
                $this->writeLog('SISTEM', 'SIPARIS_DURUM', 'Sipariş durumu başarıyla güncellendi: ' . $order_number . ' -> ' . $status);
                return true;
            } else {
                $this->writeLog('SISTEM', 'HATA', 'Sipariş durumu güncelleme hatası: HTTP ' . $httpcode . ' - ' . $response);
                return false;
            }
        } catch (Exception $e) {
            $this->writeLog('SISTEM', 'HATA', 'Sipariş durumu güncelleme hatası: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Trendyol API'den markaları çeker
     * @return array|false
     */
    public function getBrands() {
        $url = $this->api_url . '/brands';
        $headers = [
            'Authorization: Basic ' . base64_encode($this->api_key . ':' . $this->api_secret),
            'Content-Type: application/json'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $this->writeLog('system', 'API_ERROR', 'Marka çekme cURL hatası: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        if ($httpcode !== 200) {
            $this->writeLog('system', 'API_ERROR', 'Marka çekme HTTP hata kodu: ' . $httpcode . ' - Yanıt: ' . $response);
            return false;
        }
        
        $this->writeLog('system', 'API_CALL', 'Markalar başarıyla çekildi.');
        return json_decode($response, true);
    }
    
    /**
     * Trendyol API'den kategorileri çeker
     * @return array|false
     */
    public function getCategories() {
        $url = $this->api_url . '/product-categories';
        $headers = [
            'Authorization: Basic ' . base64_encode($this->api_key . ':' . $this->api_secret),
            'Content-Type: application/json'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $this->writeLog('system', 'API_ERROR', 'Kategori çekme cURL hatası: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        if ($httpcode !== 200) {
            $this->writeLog('system', 'API_ERROR', 'Kategori çekme HTTP hata kodu: ' . $httpcode . ' - Yanıt: ' . $response);
            return false;
        }
        
        $this->writeLog('system', 'API_CALL', 'Kategoriler başarıyla çekildi.');
        return json_decode($response, true);
    }
    
    /**
     * Trendyol API'den kategori özelliklerini çeker
     * @param int $category_id Kategori ID
     * @return array|false
     */
    public function getCategoryAttributes($category_id) {
        $url = $this->api_url . '/product-categories/' . $category_id . '/attributes';
        $headers = [
            'Authorization: Basic ' . base64_encode($this->api_key . ':' . $this->api_secret),
            'Content-Type: application/json'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $this->writeLog('system', 'API_ERROR', 'Kategori özellikleri çekme cURL hatası: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        if ($httpcode !== 200) {
            $this->writeLog('system', 'API_ERROR', 'Kategori özellikleri çekme HTTP hata kodu: ' . $httpcode . ' - Yanıt: ' . $response);
            return false;
        }
        
        $this->writeLog('system', 'API_CALL', 'Kategori özellikleri başarıyla çekildi.');
        return json_decode($response, true);
    }
    
    /**
     * Ürünü OpenCart formatından Trendyol formatına dönüştürür
     * @param array $opencart_product OpenCart ürün verisi
     * @return array Trendyol formatında ürün
     */
    public function formatProductForTrendyol($opencart_product) {
        // Trendyol gerekli alanları
        $trendyol_product = [
            'barcode' => $opencart_product['sku'],
            'title' => $opencart_product['name'],
            'productMainId' => 'OC' . $opencart_product['product_id'],
            'brandId' => $opencart_product['manufacturer_id'],
            'categoryId' => $opencart_product['trendyol_category_id'] ?? 0,
            'quantity' => $opencart_product['quantity'],
            'stockCode' => $opencart_product['model'],
            'dimensionalWeight' => $opencart_product['weight'],
            'description' => $opencart_product['description'],
            'pricingType' => 'BUY_BOX',
            'price' => $opencart_product['price'],
            'cargoCompanyId' => 1, // Varsayılan kargo şirketi ID'si
        ];
        
        // Ürün resimleri
        $images = [];
        if (!empty($opencart_product['image'])) {
            $images[] = HTTPS_CATALOG . 'image/' . $opencart_product['image'];
        }
        
        // Ek resimler varsa ekle
        if (isset($opencart_product['product_image']) && is_array($opencart_product['product_image'])) {
            foreach ($opencart_product['product_image'] as $image) {
                $images[] = HTTPS_CATALOG . 'image/' . $image['image'];
            }
        }
        
        $trendyol_product['images'] = $images;
        
        // Özellikler/Nitelikler
        $attributes = [];
        if (isset($opencart_product['product_attribute']) && is_array($opencart_product['product_attribute'])) {
            foreach ($opencart_product['product_attribute'] as $attribute) {
                $attributes[] = [
                    'attributeId' => $attribute['trendyol_attribute_id'] ?? 0,
                    'attributeValueId' => $attribute['trendyol_attribute_value_id'] ?? 0
                ];
            }
        }
        
        $trendyol_product['attributes'] = $attributes;
        
        return $trendyol_product;
    }
    
    /**
     * Loglama fonksiyonu
     */
    private function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'trendyol_helper.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 