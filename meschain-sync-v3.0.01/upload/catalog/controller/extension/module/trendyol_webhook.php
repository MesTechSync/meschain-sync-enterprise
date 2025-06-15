<?php
/**
 * trendyol_webhook.php
 *
 * Amaç: Trendyol webhook isteklerini dinleyen ve işleyen catalog tarafı controller.
 * Trendyol'dan gelen sipariş bildirimleri, stok değişiklikleri ve durum güncellemeleri
 * için webhook entegrasyonunu sağlar.
 *
 * Teknik: Gelen POST isteğinin geçerliliği kontrol edilir, ardından olay tipine göre
 * ilgili işlem gerçekleştirilir.
 */
class ControllerExtensionModuleTrendyolWebhook extends Controller {
    private $logFile = 'trendyol_webhook.log';
    
    /**
     * Webhook isteğini işle
     */
    public function index() {
        $this->writeLog('INFO', 'Webhook isteği alındı', [
            'remote_ip' => $this->request->server['REMOTE_ADDR'],
            'method' => $this->request->server['REQUEST_METHOD']
        ]);
        
        // Sadece POST isteklerini kabul et
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $this->writeLog('ERROR', 'Geçersiz HTTP metodu', [
                'method' => $this->request->server['REQUEST_METHOD']
            ]);
            
            $this->response->addHeader('HTTP/1.0 405 Method Not Allowed');
            $this->response->setOutput(json_encode(['error' => 'Method Not Allowed']));
            return;
        }
        
        // API anahtarı ve imza doğrulaması
        $apiKey = $this->config->get('module_trendyol_api_key');
        $signature = isset($this->request->server['HTTP_X_TRENDYOL_SIGNATURE']) ? $this->request->server['HTTP_X_TRENDYOL_SIGNATURE'] : '';
        
        if (!$this->validateSignature($signature, $apiKey)) {
            $this->writeLog('ERROR', 'Geçersiz imza', [
                'signature' => $signature
            ]);
            
            $this->response->addHeader('HTTP/1.0 403 Forbidden');
            $this->response->setOutput(json_encode(['error' => 'Invalid signature']));
            return;
        }
        
        // JSON verisini al
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (empty($data)) {
            $this->writeLog('ERROR', 'Geçersiz JSON verisi');
            
            $this->response->addHeader('HTTP/1.0 400 Bad Request');
            $this->response->setOutput(json_encode(['error' => 'Invalid JSON data']));
            return;
        }
        
        // Olay tipine göre işlem yap
        $eventType = isset($data['eventType']) ? $data['eventType'] : '';
        
        switch ($eventType) {
            case 'order-created':
                $this->processOrderCreated($data);
                break;
                
            case 'order-status-changed':
                $this->processOrderStatusChanged($data);
                break;
                
            case 'stock-updated':
                $this->processStockUpdated($data);
                break;
                
            default:
                $this->writeLog('WARNING', 'Desteklenmeyen olay tipi', [
                    'eventType' => $eventType
                ]);
                
                $this->response->addHeader('HTTP/1.0 400 Bad Request');
                $this->response->setOutput(json_encode(['error' => 'Unsupported event type']));
                return;
        }
        
        // Başarılı yanıt
        $this->response->addHeader('HTTP/1.0 200 OK');
        $this->response->setOutput(json_encode(['status' => 'success']));
    }
    
    /**
     * Webhook imzasını doğrula
     * 
     * @param string $signature İmza
     * @param string $apiKey API anahtarı
     * @return bool
     */
    private function validateSignature($signature, $apiKey) {
        // Trendyol'dan gelen webhook isteklerinin imza doğrulaması
        // Gerçek implementasyonda Trendyol API dokümantasyonuna göre
        // imza doğrulaması yapılmalıdır
        
        // Basit doğrulama örneği
        if (empty($signature)) {
            return false;
        }
        
        // İmza kontrolü - bu örnek değiştirilmelidir
        // Gerçek uygulamada, Trendyol tarafından belirlenen imza algoritması kullanılmalıdır
        $expectedSignature = hash_hmac('sha256', $apiKey, $this->config->get('module_trendyol_api_secret'));
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Yeni sipariş oluşturulduğunda işlem yap
     * 
     * @param array $data Olay verileri
     */
    private function processOrderCreated($data) {
        $this->writeLog('INFO', 'Yeni sipariş oluşturuldu', [
            'orderId' => $data['orderId'] ?? 'unknown'
        ]);
        
        if (!isset($data['orderId'])) {
            $this->writeLog('ERROR', 'Sipariş ID bulunamadı', $data);
            return;
        }
        
        // Admin panelinde bildirim oluştur
        $this->load->model('extension/module/trendyol_webhook');
        $this->model_extension_module_trendyol_webhook->addNotification([
            'type' => 'order_created',
            'title' => 'Yeni Trendyol Siparişi',
            'message' => 'Trendyol\'da yeni bir sipariş oluşturuldu: ' . $data['orderId'],
            'data' => json_encode($data),
            'status' => 0, // Okunmadı
            'date_added' => date('Y-m-d H:i:s')
        ]);
        
        // Siparişi otomatik olarak OpenCart'a aktarma ayarı açıksa
        // sipariş dönüşümünü yap
        if ($this->config->get('module_trendyol_auto_import_orders')) {
            // Trendyol helper sınıfını yükle
            $this->load->library('meschain/helper/trendyol');
            $trendyolHelper = new MeschainTrendyolHelper($this->registry);
            
            // Sipariş detaylarını al
            $orderDetails = $trendyolHelper->getOrderDetails($data['orderId']);
            
            if (isset($orderDetails['status']) && $orderDetails['status']) {
                // OpenCart siparişine dönüştür
                $this->load->model('extension/module/trendyol');
                $this->model_extension_module_trendyol->createOrderFromTrendyol($orderDetails['data']);
            } else {
                $this->writeLog('ERROR', 'Sipariş detayları alınamadı', [
                    'orderId' => $data['orderId'],
                    'error' => $orderDetails['message'] ?? 'Unknown error'
                ]);
            }
        }
    }
    
    /**
     * Sipariş durumu değiştiğinde işlem yap
     * 
     * @param array $data Olay verileri
     */
    private function processOrderStatusChanged($data) {
        $this->writeLog('INFO', 'Sipariş durumu değişti', [
            'orderId' => $data['orderId'] ?? 'unknown',
            'oldStatus' => $data['oldStatus'] ?? 'unknown',
            'newStatus' => $data['newStatus'] ?? 'unknown'
        ]);
        
        if (!isset($data['orderId']) || !isset($data['newStatus'])) {
            $this->writeLog('ERROR', 'Sipariş ID veya yeni durum bulunamadı', $data);
            return;
        }
        
        // Admin panelinde bildirim oluştur
        $this->load->model('extension/module/trendyol_webhook');
        $this->model_extension_module_trendyol_webhook->addNotification([
            'type' => 'order_status_changed',
            'title' => 'Trendyol Sipariş Durumu Değişti',
            'message' => 'Trendyol siparişinin durumu değişti: ' . $data['orderId'] . ' - ' . $data['newStatus'],
            'data' => json_encode($data),
            'status' => 0, // Okunmadı
            'date_added' => date('Y-m-d H:i:s')
        ]);
        
        // İlişkili OpenCart siparişini bul ve durumunu güncelle
        $this->load->model('extension/module/trendyol');
        $opencartOrderId = $this->model_extension_module_trendyol->getOpenCartOrderId($data['orderId']);
        
        if ($opencartOrderId) {
            // Trendyol durumunu OpenCart durumuna çevir
            $opencartStatusId = $this->mapTrendyolStatusToOpenCart($data['newStatus']);
            
            // OpenCart siparişini güncelle
            $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($opencartOrderId, $opencartStatusId, 'Trendyol\'dan otomatik güncellendi', true);
            
            $this->writeLog('INFO', 'OpenCart siparişi güncellendi', [
                'trendyolOrderId' => $data['orderId'],
                'opencartOrderId' => $opencartOrderId,
                'newStatus' => $opencartStatusId
            ]);
        } else {
            $this->writeLog('WARNING', 'İlişkili OpenCart siparişi bulunamadı', [
                'trendyolOrderId' => $data['orderId']
            ]);
        }
    }
    
    /**
     * Stok güncellendiğinde işlem yap
     * 
     * @param array $data Olay verileri
     */
    private function processStockUpdated($data) {
        $this->writeLog('INFO', 'Stok güncellendi', [
            'productCount' => count($data['products'] ?? [])
        ]);
        
        if (!isset($data['products']) || !is_array($data['products'])) {
            $this->writeLog('ERROR', 'Ürün bilgileri bulunamadı', $data);
            return;
        }
        
        // Admin panelinde bildirim oluştur
        $this->load->model('extension/module/trendyol_webhook');
        $this->model_extension_module_trendyol_webhook->addNotification([
            'type' => 'stock_updated',
            'title' => 'Trendyol Stok Güncellendi',
            'message' => count($data['products']) . ' ürünün stok durumu Trendyol\'da güncellendi',
            'data' => json_encode($data),
            'status' => 0, // Okunmadı
            'date_added' => date('Y-m-d H:i:s')
        ]);
        
        // OpenCart'ta ilgili ürünlerin stokunu güncelle
        if ($this->config->get('module_trendyol_sync_stock_from_trendyol')) {
            $this->load->model('extension/module/trendyol');
            
            foreach ($data['products'] as $product) {
                if (isset($product['barcode']) && isset($product['quantity'])) {
                    // Ürünü barkoda göre bul
                    $opencartProductId = $this->model_extension_module_trendyol->getProductIdByBarcode($product['barcode']);
                    
                    if ($opencartProductId) {
                        // Ürün stokunu güncelle
                        $this->model_extension_module_trendyol->updateProductStock($opencartProductId, $product['quantity']);
                        
                        $this->writeLog('INFO', 'Ürün stoku güncellendi', [
                            'barcode' => $product['barcode'],
                            'productId' => $opencartProductId,
                            'quantity' => $product['quantity']
                        ]);
                    } else {
                        $this->writeLog('WARNING', 'Ürün bulunamadı', [
                            'barcode' => $product['barcode']
                        ]);
                    }
                }
            }
        }
    }
    
    /**
     * Trendyol sipariş durumunu OpenCart sipariş durumuna dönüştür
     * 
     * @param string $trendyolStatus Trendyol sipariş durumu
     * @return int OpenCart sipariş durumu ID
     */
    private function mapTrendyolStatusToOpenCart($trendyolStatus) {
        $statusMap = [
            'Created' => 1, // Pending
            'Picking' => 2, // Processing
            'Invoiced' => 2, // Processing
            'Shipped' => 3, // Shipped
            'Cancelled' => 7, // Canceled
            'Delivered' => 5, // Complete
            'UnDelivered' => 10, // Failed
            'Returned' => 11, // Refunded
        ];
        
        return isset($statusMap[$trendyolStatus]) ? $statusMap[$trendyolStatus] : 1; // Varsayılan: Pending
    }
    
    /**
     * Log kaydı oluştur
     * 
     * @param string $level Log seviyesi (INFO, WARNING, ERROR)
     * @param string $message Log mesajı
     * @param array $context Ek veriler
     */
    private function writeLog($level, $message, $context = []) {
        $logEntry = '[' . date('Y-m-d H:i:s') . '] [' . $level . '] ' . $message;
        
        if (!empty($context)) {
            $logEntry .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        $logEntry .= PHP_EOL;
        
        file_put_contents(DIR_LOGS . $this->logFile, $logEntry, FILE_APPEND);
    }
} 