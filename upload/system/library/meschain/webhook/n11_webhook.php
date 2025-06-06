<?php
/**
 * N11 Webhook Processor
 * N11 pazaryerinden gelen webhook'ları işler
 */
class N11Webhook {
    
    private $registry;
    private $db;
    private $config;
    private $log;
    private $settings;
    
    public function __construct($registry, $config = []) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->log = $registry->get('log');
        $this->settings = $config;
    }
    
    /**
     * Webhook'u işle
     */
    public function processWebhook($headers, $payload) {
        try {
            // Webhook aktif mi kontrol et
            if (!$this->isWebhookEnabled()) {
                return [
                    'success' => false,
                    'error' => 'N11 webhook disabled',
                    'http_code' => 404
                ];
            }
            
            // İmza doğrulama
            if (!$this->verifySignature($headers, $payload)) {
                $this->logWebhook('SECURITY_VIOLATION', 'Invalid signature', $headers, $payload);
                return [
                    'success' => false,
                    'error' => 'Invalid signature',
                    'http_code' => 401
                ];
            }
            
            // Payload'u decode et
            $data = json_decode($payload, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'error' => 'Invalid JSON payload',
                    'http_code' => 400
                ];
            }
            
            // Event type'ı belirle
            $eventType = $data['eventType'] ?? $data['type'] ?? 'unknown';
            
            // Log webhook
            $this->logWebhook('RECEIVED', $eventType, $headers, $payload);
            
            // Event'i işle
            $result = $this->processEvent($eventType, $data);
            
            if ($result['success']) {
                $this->logWebhook('SUCCESS', $eventType, [], $result['message'] ?? 'Processed successfully');
                return [
                    'success' => true,
                    'message' => $result['message'] ?? 'Webhook processed successfully',
                    'http_code' => 200
                ];
            } else {
                $this->logWebhook('ERROR', $eventType, [], $result['error'] ?? 'Processing failed');
                return [
                    'success' => false,
                    'error' => $result['error'] ?? 'Processing failed',
                    'http_code' => 500
                ];
            }
            
        } catch (Exception $e) {
            $this->logWebhook('EXCEPTION', 'webhook_processing', [], $e->getMessage());
            return [
                'success' => false,
                'error' => 'Internal processing error',
                'http_code' => 500
            ];
        }
    }
    
    /**
     * Event'i işle
     */
    private function processEvent($eventType, $data) {
        switch ($eventType) {
            case 'order.created':
            case 'order.updated':
                return $this->processOrderEvent($data);
                
            case 'product.stock.updated':
                return $this->processStockEvent($data);
                
            case 'product.price.updated':
                return $this->processPriceEvent($data);
                
            case 'orders.test':
                return $this->processTestEvent($data);
                
            default:
                return [
                    'success' => false,
                    'error' => 'Unknown event type: ' . $eventType
                ];
        }
    }
    
    /**
     * Sipariş event'ini işle
     */
    private function processOrderEvent($data) {
        try {
            $this->load->model('extension/module/n11');
            
            $orderData = $data['order'] ?? $data;
            $orderId = $orderData['id'] ?? $orderData['orderNumber'] ?? null;
            
            if (!$orderId) {
                return ['success' => false, 'error' => 'Order ID not found'];
            }
            
            // Sipariş var mı kontrol et
            $existingOrder = $this->model_extension_module_n11->getOrderByMarketplaceId($orderId);
            
            if ($existingOrder) {
                // Mevcut siparişi güncelle
                $updateData = [
                    'status' => $orderData['status'] ?? $orderData['orderState'] ?? 'processing',
                    'updated_at' => date('Y-m-d H:i:s'),
                    'webhook_data' => json_encode($orderData)
                ];
                
                $this->model_extension_module_n11->updateOrder($existingOrder['order_id'], $updateData);
                $message = 'Order updated: ' . $orderId;
            } else {
                // Yeni sipariş oluştur
                $newOrderData = [
                    'marketplace_order_id' => $orderId,
                    'customer_name' => $orderData['customer']['name'] ?? 'N11 Customer',
                    'customer_email' => $orderData['customer']['email'] ?? '',
                    'total_price' => $orderData['totalPrice'] ?? 0,
                    'status' => $orderData['status'] ?? 'processing',
                    'order_date' => date('Y-m-d H:i:s'),
                    'webhook_data' => json_encode($orderData)
                ];
                
                $this->model_extension_module_n11->createOrder($newOrderData);
                $message = 'New order created: ' . $orderId;
            }
            
            return ['success' => true, 'message' => $message];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Stok event'ini işle
     */
    private function processStockEvent($data) {
        try {
            $this->load->model('extension/module/n11');
            
            $productData = $data['product'] ?? $data;
            $sku = $productData['sku'] ?? $productData['productSellerCode'] ?? null;
            $stock = $productData['stock'] ?? $productData['quantity'] ?? 0;
            
            if (!$sku) {
                return ['success' => false, 'error' => 'Product SKU not found'];
            }
            
            // Ürünü bul ve stok güncelle
            $product = $this->model_extension_module_n11->getProductBySku($sku);
            
            if ($product) {
                $this->model_extension_module_n11->updateProductStock($product['product_id'], $stock);
                $message = 'Stock updated for SKU: ' . $sku . ' to ' . $stock;
            } else {
                $message = 'Product not found for SKU: ' . $sku;
            }
            
            return ['success' => true, 'message' => $message];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Fiyat event'ini işle
     */
    private function processPriceEvent($data) {
        try {
            $this->load->model('extension/module/n11');
            
            $productData = $data['product'] ?? $data;
            $sku = $productData['sku'] ?? $productData['productSellerCode'] ?? null;
            $price = $productData['price'] ?? $productData['salePrice'] ?? 0;
            
            if (!$sku) {
                return ['success' => false, 'error' => 'Product SKU not found'];
            }
            
            // Ürünü bul ve fiyat güncelle
            $product = $this->model_extension_module_n11->getProductBySku($sku);
            
            if ($product) {
                $this->model_extension_module_n11->updateProductPrice($product['product_id'], $price);
                $message = 'Price updated for SKU: ' . $sku . ' to ' . $price;
            } else {
                $message = 'Product not found for SKU: ' . $sku;
            }
            
            return ['success' => true, 'message' => $message];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Test event'ini işle
     */
    private function processTestEvent($data) {
        return [
            'success' => true,
            'message' => 'Test webhook processed successfully',
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data
        ];
    }
    
    /**
     * İmzayı doğrula
     */
    private function verifySignature($headers, $payload) {
        $signature = $headers['X-N11-Signature'] ?? $headers['x-n11-signature'] ?? '';
        $timestamp = $headers['X-N11-Timestamp'] ?? $headers['x-n11-timestamp'] ?? '';
        
        if (empty($signature) || empty($this->settings['n11_webhook_secret'])) {
            return false;
        }
        
        // Timestamp kontrolü (5 dakika tolerance)
        if ($timestamp && (abs(time() - $timestamp) > 300)) {
            return false;
        }
        
        // İmzayı hesapla
        $expectedSignature = hash_hmac('sha256', $payload, $this->settings['n11_webhook_secret']);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Webhook aktif mi kontrol et
     */
    private function isWebhookEnabled() {
        return !empty($this->settings['n11_webhook_enabled']);
    }
    
    /**
     * Webhook'u logla
     */
    private function logWebhook($status, $eventType, $headers = [], $data = '') {
        try {
            // Database log
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "meschain_webhook_logs SET
                marketplace = 'n11',
                event_type = '" . $this->db->escape($eventType) . "',
                status = '" . $this->db->escape($status) . "',
                headers = '" . $this->db->escape(json_encode($headers)) . "',
                payload = '" . $this->db->escape(is_string($data) ? $data : json_encode($data)) . "',
                created_at = NOW()
            ");
            
            // File log
            if ($this->log) {
                $logMessage = "N11 Webhook [$status] $eventType: " . (is_string($data) ? $data : json_encode($data));
                $this->log->write($logMessage);
            }
            
        } catch (Exception $e) {
            error_log('N11 Webhook Log Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Model yükle
     */
    private function load($path) {
        if (!isset($this->registry->get('load'))) {
            // Basit model loading için
            $modelPath = DIR_APPLICATION . 'model/' . str_replace('/', DIRECTORY_SEPARATOR, $path) . '.php';
            if (file_exists($modelPath)) {
                require_once($modelPath);
            }
        } else {
            $this->registry->get('load')->model($path);
        }
    }
}
?>