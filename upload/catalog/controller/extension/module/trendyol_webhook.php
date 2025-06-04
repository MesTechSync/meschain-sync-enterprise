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
        
        try {
            // Trendyol helper'ı yükle
            $this->load->library('meschain/helper/trendyol');
            $trendyolHelper = new MeschainTrendyolHelper($this->registry);
            
            // Helper'daki processWebhook fonksiyonunu kullan
            $result = $trendyolHelper->processWebhook($eventType, $data);
            
            if ($result) {
                $this->writeLog('SUCCESS', 'Webhook başarıyla işlendi', [
                    'eventType' => $eventType,
                    'data_keys' => array_keys($data)
                ]);
            } else {
                $this->writeLog('ERROR', 'Webhook işlenemedi', [
                    'eventType' => $eventType
                ]);
                
                $this->response->addHeader('HTTP/1.0 500 Internal Server Error');
                $this->response->setOutput(json_encode(['error' => 'Webhook processing failed']));
                return;
            }
            
        } catch (Exception $e) {
            $this->writeLog('ERROR', 'Webhook işlenirken hata oluştu', [
                'eventType' => $eventType,
                'error' => $e->getMessage()
            ]);
            
            $this->response->addHeader('HTTP/1.0 500 Internal Server Error');
            $this->response->setOutput(json_encode(['error' => 'Internal server error']));
            return;
        }
        
        // Başarılı yanıt
        $this->response->addHeader('HTTP/1.0 200 OK');
        $this->response->setOutput(json_encode(['status' => 'success']));
    }
      
    /**
     * Enhanced webhook imzasını doğrula - Gerçek Trendyol protokolüne uygun
     * 
     * @param string $signature İmza
     * @param string $apiKey API anahtarı
     * @return bool
     */
    private function validateSignature($signature, $apiKey) {
        // Gelen POST verisini al
        $body = file_get_contents('php://input');
        
        if (empty($signature) || empty($apiKey)) {
            return false;
        }
        
        // Trendyol webhook imza doğrulaması
        $apiSecret = $this->config->get('module_trendyol_api_secret');
        if (empty($apiSecret)) {
            $this->writeLog('ERROR', 'API secret bulunamadı');
            return false;
        }
        
        // HMAC-SHA256 ile imza oluştur
        $expectedSignature = base64_encode(hash_hmac('sha256', $body, $apiSecret, true));
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Log kaydı oluştur - Enhanced version
     * 
     * @param string $level Log seviyesi (INFO, WARNING, ERROR, SUCCESS)
     * @param string $message Log mesajı
     * @param array $context Ek veriler
     */
    private function writeLog($level, $message, $context = []) {
        $logEntry = '[' . date('Y-m-d H:i:s') . '] [' . $level . '] [WEBHOOK] ' . $message;
        
        if (!empty($context)) {
            $logEntry .= ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        
        $logEntry .= PHP_EOL;
        
        // Log dosyasına yaz
        file_put_contents(DIR_LOGS . $this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Ayrıca OpenCart log sistemine de kaydet
        if (class_exists('Log')) {
            $log = new Log('trendyol_webhook.log');
            $log->write($level . ': ' . $message . (!empty($context) ? ' | ' . json_encode($context) : ''));
        }
    }

    /**
     * Webhook endpoint status kontrolü
     */
    public function status() {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'status' => 'active',
            'webhook_enabled' => $this->config->get('module_trendyol_webhook_enabled'),
            'last_webhook_received' => $this->getLastWebhookReceived(),
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '3.0.1'
        ]));
    }    /**
     * Son webhook alım zamanını getir
     */
    private function getLastWebhookReceived() {
        $query = $this->db->query("
            SELECT received_at 
            FROM " . DB_PREFIX . "trendyol_webhooks 
            ORDER BY received_at DESC 
            LIMIT 1
        ");
        
        return $query->num_rows ? $query->row['received_at'] : null;
    }
    
    /**
     * Test endpoint - Webhook sistemi test amaçlı
     */
    public function test() {
        $this->writeLog('INFO', 'Test webhook endpoint çağrıldı');
        
        // Test verisi
        $testData = [
            'eventType' => 'TEST_EVENT',
            'orderNumber' => 'TEST-' . time(),
            'timestamp' => date('c'),
            'test' => true
        ];
        
        try {
            // Trendyol helper'ı yükle
            $this->load->library('meschain/helper/trendyol');
            $trendyolHelper = new MeschainTrendyolHelper($this->registry);
            
            // Test webhook işleme
            $result = $trendyolHelper->processWebhook('TEST_EVENT', $testData);
            
            $this->writeLog('SUCCESS', 'Test webhook işlendi', [
                'result' => $result,
                'testData' => $testData
            ]);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'status' => 'success',
                'message' => 'Test webhook successfully processed',
                'result' => $result,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
              } catch (Exception $e) {
            $this->writeLog('ERROR', 'Test webhook hatası', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->response->addHeader('HTTP/1.0 500 Internal Server Error');
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'message' => 'Test webhook failed: ' . $e->getMessage()
            ]));
        }
    }
}