<?php
/**
 * Ozon API Helper Class
 * Dropshipping entegrasyonu için Ozon API işlemleri
 */
class OzonHelper {
    private $config;
    private $logFile;
    
    public function __construct($config) {
        $this->config = $config;
        $this->logFile = 'ozon_api.log';
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
            $this->writeLog('Ozon ürün listesi alınıyor...');
            
            // Ozon API çağrısı simülasyonu
            $products = [
                [
                    'id' => 'OZON001',
                    'name' => 'Ozon Ürün 1',
                    'sku' => 'OZON-SKU-001',
                    'price' => 59.99,
                    'quantity' => 35,
                    'description' => 'Ozon ürün açıklaması',
                    'barcode' => '4567890123456',
                    'category' => 'Electronics',
                    'brand' => 'Ozon Brand',
                    'weight' => 0.5,
                    'dimensions' => '10x10x5'
                ]
            ];
            
            return [
                'success' => true,
                'data' => $products
            ];
            
        } catch (Exception $e) {
            $this->writeLog('Ozon API Hatası: ' . $e->getMessage());
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
            $this->writeLog('Ozon siparişi oluşturuluyor: ' . json_encode($orderData));
            
            // Ozon API çağrısı simülasyonu
            $orderId = 'OZON-ORDER-' . time();
            
            return [
                'success' => true,
                'order_id' => $orderId,
                'status' => 'awaiting_packaging',
                'posting_number' => 'OZON-POST-' . substr($orderId, -8),
                'estimated_delivery' => date('Y-m-d', strtotime('+5 days'))
            ];
            
        } catch (Exception $e) {
            $this->writeLog('Ozon sipariş hatası: ' . $e->getMessage());
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
            $this->writeLog('Ozon sipariş durumu kontrol ediliyor: ' . $orderId);
            
            return [
                'success' => true,
                'status' => 'delivering',
                'tracking_number' => 'OZON-TRACK-' . substr($orderId, -6),
                'posting_number' => 'OZON-POST-' . substr($orderId, -8),
                'carrier' => 'Ozon Logistics',
                'estimated_delivery' => date('Y-m-d', strtotime('+2 days')),
                'delivery_address' => 'Pickup point or courier delivery'
            ];
            
        } catch (Exception $e) {
            $this->writeLog('Ozon sipariş durum hatası: ' . $e->getMessage());
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
            $this->writeLog("Ozon stok güncelleniyor: $productId -> $quantity");
            
            return [
                'success' => true,
                'message' => 'Stok güncellendi',
                'updated_quantity' => $quantity,
                'warehouse_id' => 'OZON-WH-001'
            ];
            
        } catch (Exception $e) {
            $this->writeLog('Ozon stok güncelleme hatası: ' . $e->getMessage());
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
            $this->writeLog("Ozon fiyat güncelleniyor: $productId -> $price");
            
            return [
                'success' => true,
                'message' => 'Fiyat güncellendi',
                'updated_price' => $price,
                'currency' => 'RUB'
            ];
            
        } catch (Exception $e) {
            $this->writeLog('Ozon fiyat güncelleme hatası: ' . $e->getMessage());
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
            $this->writeLog('Ozon kategori listesi alınıyor...');
            
            $categories = [
                ['id' => 1, 'name' => 'Elektronik', 'parent_id' => 0],
                ['id' => 2, 'name' => 'Giyim ve Ayakkabı', 'parent_id' => 0],
                ['id' => 3, 'name' => 'Ev ve Bahçe', 'parent_id' => 0],
                ['id' => 4, 'name' => 'Spor ve Outdoor', 'parent_id' => 0],
                ['id' => 5, 'name' => 'Kitap ve Kırtasiye', 'parent_id' => 0],
                ['id' => 6, 'name' => 'Kozmetik ve Kişisel Bakım', 'parent_id' => 0],
                ['id' => 7, 'name' => 'Oyuncak ve Hobi', 'parent_id' => 0],
                ['id' => 8, 'name' => 'Otomotiv', 'parent_id' => 0]
            ];
            
            return [
                'success' => true,
                'data' => $categories
            ];
            
        } catch (Exception $e) {
            $this->writeLog('Ozon kategori hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Ürün detaylarını al
     */
    public function getProductDetails($productId) {
        try {
            $this->writeLog("Ozon ürün detayları alınıyor: $productId");
            
            $product = [
                'id' => $productId,
                'title' => 'Ozon Ürün Detayı',
                'description' => 'Detaylı ürün açıklaması',
                'price' => 59.99,
                'quantity' => 35,
                'brand' => 'Ozon Brand',
                'weight' => 0.5,
                'dimensions' => '10x10x5',
                'images' => [
                    'https://example.com/image1.jpg',
                    'https://example.com/image2.jpg'
                ],
                'attributes' => [
                    'color' => 'Siyah',
                    'material' => 'Plastik',
                    'warranty' => '1 yıl'
                ],
                'delivery_schema' => 'FBO', // Fulfillment by Ozon
                'commission_percent' => 15.0
            ];
            
            return [
                'success' => true,
                'data' => $product
            ];
            
        } catch (Exception $e) {
            $this->writeLog('Ozon ürün detay hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Komisyon oranlarını al
     */
    public function getCommissionRates($categoryId = null) {
        try {
            $this->writeLog('Ozon komisyon oranları alınıyor...');
            
            $commissions = [
                ['category_id' => 1, 'category_name' => 'Elektronik', 'commission_percent' => 8.0],
                ['category_id' => 2, 'category_name' => 'Giyim', 'commission_percent' => 12.0],
                ['category_id' => 3, 'category_name' => 'Ev ve Bahçe', 'commission_percent' => 10.0],
                ['category_id' => 4, 'category_name' => 'Spor', 'commission_percent' => 15.0]
            ];
            
            if ($categoryId) {
                $commissions = array_filter($commissions, function($item) use ($categoryId) {
                    return $item['category_id'] == $categoryId;
                });
            }
            
            return [
                'success' => true,
                'data' => $commissions
            ];
            
        } catch (Exception $e) {
            $this->writeLog('Ozon komisyon hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} 