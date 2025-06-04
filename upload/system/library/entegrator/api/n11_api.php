<?php
/**
 * N11 API Helper Class
 * Dropshipping entegrasyonu için N11 API işlemleri
 */
class N11Helper {
    private $config;
    private $logFile;
    
    public function __construct($config) {
        $this->config = $config;
        $this->logFile = 'n11_api.log';
    }
    
    /**
     * Log yazma metodu
     */
    private function writeLog($message) {
        $date = date('Y-m-d H:i:s');
        $logMessage = "[$date] $message\n";
        file_put_contents(DIR_LOGS . $this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Ürün listesini al
     */
    public function getProducts($params = []) {
        try {
            $this->writeLog('N11 ürün listesi alınıyor...');
            
            // N11 API çağrısı simülasyonu
            $products = [
                [
                    'id' => 'N11001',
                    'name' => 'N11 Ürün 1',
                    'sku' => 'N11-SKU-001',
                    'price' => 19.99,
                    'quantity' => 50,
                    'description' => 'N11 ürün açıklaması',
                    'barcode' => '9876543210987',
                    'category' => 'Fashion'
                ]
            ];
            
            return [
                'success' => true,
                'data' => $products
            ];
            
        } catch (Exception $e) {
            $this->writeLog('N11 API Hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Sipariş oluştur
     */
    public function createOrder($orderData) {
        try {
            $this->writeLog('N11 siparişi oluşturuluyor: ' . json_encode($orderData));
            
            // N11 API çağrısı simülasyonu
            $orderId = 'N11-ORDER-' . time();
            
            return [
                'success' => true,
                'order_id' => $orderId,
                'status' => 'created'
            ];
            
        } catch (Exception $e) {
            $this->writeLog('N11 sipariş hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Sipariş durumunu kontrol et
     */
    public function getOrderStatus($orderId) {
        try {
            $this->writeLog('N11 sipariş durumu kontrol ediliyor: ' . $orderId);
            
            return [
                'success' => true,
                'status' => 'shipped',
                'tracking_number' => 'N11-TRACK-' . substr($orderId, -6)
            ];
            
        } catch (Exception $e) {
            $this->writeLog('N11 sipariş durum hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Stok güncelle
     */
    public function updateStock($productId, $quantity) {
        try {
            $this->writeLog("N11 stok güncelleniyor: $productId -> $quantity");
            
            return [
                'success' => true,
                'message' => 'Stok güncellendi'
            ];
            
        } catch (Exception $e) {
            $this->writeLog('N11 stok güncelleme hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Kategori listesini al
     */
    public function getCategories() {
        try {
            $this->writeLog('N11 kategori listesi alınıyor...');
            
            $categories = [
                ['id' => 1, 'name' => 'Elektronik', 'parent_id' => 0],
                ['id' => 2, 'name' => 'Moda', 'parent_id' => 0],
                ['id' => 3, 'name' => 'Ev & Yaşam', 'parent_id' => 0]
            ];
            
            return [
                'success' => true,
                'data' => $categories
            ];
            
        } catch (Exception $e) {
            $this->writeLog('N11 kategori hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} 