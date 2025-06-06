<?php
/**
 * trendyol_webhook.php (Refactored)
 *
 * Handles incoming webhook notifications from Trendyol using the standardized ApiClient.
 */
class ControllerExtensionModuleTrendyolWebhook extends Controller {
    
    public function index() {
        $log = new Log('trendyol_webhook.log');
        $log->write('Webhook request received.');

        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            $log->write('Invalid HTTP method used.');
            $this->response->addHeader('HTTP/1.0 405 Method Not Allowed');
            $this->response->setOutput(json_encode(['error' => 'Method Not Allowed']));
            return;
        }

        try {
            // Load the standard Trendyol API Client
            require_once(DIR_SYSTEM . 'library/meschain/api/TrendyolApiClient.php');
            
            // Get credentials - This assumes a single user/credential set for webhook processing.
            // A more robust system might pass a user token in the webhook URL to fetch specific credentials.
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user_api_settings WHERE marketplace = 'trendyol' ORDER BY user_id ASC LIMIT 1");
            if (!$query->num_rows) {
                throw new Exception("Trendyol API settings not found in user_api_settings.");
            }
            $settings = json_decode($query->row['settings'], true);

            require_once(DIR_SYSTEM . 'library/meschain/encryption.php');
            $encryption = new MeschainEncryption();
            $decrypted_settings = $encryption->decryptApiCredentials($settings);
            
            $apiClient = new TrendyolApiClient($decrypted_settings);

            // Validate Signature
            $signature = $this->request->server['HTTP_X_TRENDYOL_SIGNATURE'] ?? '';
            $payload = file_get_contents('php://input');
            
            if (!$this->validateTrendyolSignature($signature, $payload, $decrypted_settings['api_secret'])) {
                $log->write('Forbidden: Invalid signature.');
                $this->response->addHeader('HTTP/1.0 403 Forbidden');
                $this->response->setOutput(json_encode(['error' => 'Invalid signature']));
                return;
            }

            // Process Payload
            $data = json_decode($payload, true);
            if (empty($data)) {
                throw new Exception("Invalid JSON payload.");
            }
            
            $this->processWebhookPayload($data);

            $this->response->addHeader('HTTP/1.0 200 OK');
            $this->response->setOutput(json_encode(['status' => 'success']));

        } catch (Exception $e) {
            $log->write('ERROR: ' . $e->getMessage());
            $this->response->addHeader('HTTP/1.0 500 Internal Server Error');
            $this->response->setOutput(json_encode(['error' => 'Internal Server Error']));
        }
    }
      
    private function validateTrendyolSignature($signature, $payload, $apiSecret) {
        if (empty($signature) || empty($apiSecret)) {
            return false;
        }
        $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $apiSecret, true));
        return hash_equals($expectedSignature, $signature);
    }

    private function processWebhookPayload($payload) {
        $log = new Log('trendyol_webhook.log');
        $eventType = $payload['eventType'] ?? '';
        $orderNumber = $payload['orderNumber'] ?? 'N/A';

        $log->write("Processing event: {$eventType} for order: {$orderNumber}");

        switch ($eventType) {
            case 'NewOrder':
            case 'OrderCreated':
                // Here, we would ideally call a centralized order processing model.
                // For example:
                // $this->load->model('extension/meschain/order');
                // $this->model_extension_meschain_order->createOrderFromWebhook('trendyol', $payload);
                break;
            case 'OrderStatusChanged':
                // $this->load->model('extension/meschain/order');
                // $this->model_extension_meschain_order->updateOrderStatusFromWebhook('trendyol', $payload);
                break;
        }
        return true;
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