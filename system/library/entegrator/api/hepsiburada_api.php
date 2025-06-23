<?php
/**
 * Hepsiburada API Helper Class
 * Dropshipping entegrasyonu için Hepsiburada API işlemleri
 */
class HepsiburadaHelper {
    private $config;
    private $logFile;
    
    public function __construct($config) {
        $this->config = $config;
        $this->logFile = 'hepsiburada_api.log';
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
            $this->writeLog('Hepsiburada ürün listesi alınıyor...');
            
            // Hepsiburada API çağrısı simülasyonu
            $products = [
                [
                    'id' => 'HB001',
                    'name' => 'Hepsiburada Ürün 1',
                    'sku' => 'HB-SKU-001',
                    'price' => 39.99,
                    'quantity' => 75,
                    'description' => 'Hepsiburada ürün açıklaması',
                    'barcode' => '5432167890123',
                    'category' => 'Home & Garden'
                ]
            ];
            
            return [
                'success' => true,
                'data' => $products
            ];
            
        } catch (Exception $e) {
            $this->writeLog('Hepsiburada API Hatası: ' . $e->getMessage());
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
            $this->writeLog('Hepsiburada siparişi oluşturuluyor: ' . json_encode($orderData));
            
            // Hepsiburada API çağrısı simülasyonu
            $orderId = 'HB-ORDER-' . time();
            
            return [
                'success' => true,
                'order_id' => $orderId,
                'status' => 'created'
            ];
            
        } catch (Exception $e) {
            $this->writeLog('Hepsiburada sipariş hatası: ' . $e->getMessage());
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
            $this->writeLog('Hepsiburada sipariş durumu kontrol ediliyor: ' . $orderId);
            
            return [
                'success' => true,
                'status' => 'delivered',
                'tracking_number' => 'HB-TRACK-' . substr($orderId, -6)
            ];
            
        } catch (Exception $e) {
            $this->writeLog('Hepsiburada sipariş durum hatası: ' . $e->getMessage());
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
            $this->writeLog("Hepsiburada stok güncelleniyor: $productId -> $quantity");
            
            return [
                'success' => true,
                'message' => 'Stok güncellendi'
            ];
            
        } catch (Exception $e) {
            $this->writeLog('Hepsiburada stok güncelleme hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Ürün fiyatını güncelle
     */
    public function updatePrice($productId, $price) {
        try {
            $this->writeLog("Hepsiburada fiyat güncelleniyor: $productId -> $price");
            
            return [
                'success' => true,
                'message' => 'Fiyat güncellendi'
            ];
            
        } catch (Exception $e) {
            $this->writeLog('Hepsiburada fiyat güncelleme hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} 