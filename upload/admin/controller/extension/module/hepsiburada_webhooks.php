<?php
/**
 * Hepsiburada Webhooks Controller
 * Hepsiburada webhook yönetimi için controller
 */
class ControllerExtensionModuleHepsiburadaWebhooks extends Controller {
    private $error = array();
    
    public function index() {
        $this->load->language('extension/module/hepsiburada');
        $this->load->model('extension/module/hepsiburada');
        
        $this->document->setTitle('Hepsiburada Webhooks');
        
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => 'Ana Sayfa',
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Hepsiburada Webhooks',
            'href' => $this->url->link('extension/module/hepsiburada_webhooks', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Webhook listesi
        $data['webhooks'] = $this->model_extension_module_hepsiburada->getWebhooks();
        
        // Webhook URL'leri
        $data['webhook_urls'] = [
            'orders' => HTTPS_CATALOG . 'index.php?route=extension/module/hepsiburada_webhook/orders',
            'products' => HTTPS_CATALOG . 'index.php?route=extension/module/hepsiburada_webhook/products',
            'inventory' => HTTPS_CATALOG . 'index.php?route=extension/module/hepsiburada_webhook/inventory',
            'prices' => HTTPS_CATALOG . 'index.php?route=extension/module/hepsiburada_webhook/prices'
        ];
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/hepsiburada_webhooks', $data));
    }
    
    /**
     * Webhook ayarlarını kaydet
     */
    public function save() {
        $this->load->language('extension/module/hepsiburada');
        $this->load->model('extension/module/hepsiburada');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                // Webhook ayarlarını kaydet
                $webhookData = [
                    'orders_enabled' => isset($this->request->post['orders_enabled']) ? 1 : 0,
                    'products_enabled' => isset($this->request->post['products_enabled']) ? 1 : 0,
                    'inventory_enabled' => isset($this->request->post['inventory_enabled']) ? 1 : 0,
                    'prices_enabled' => isset($this->request->post['prices_enabled']) ? 1 : 0,
                    'secret_key' => $this->request->post['secret_key'] ?? '',
                    'merchant_id' => $this->request->post['merchant_id'] ?? '',
                ];
                
                $this->model_extension_module_hepsiburada->saveWebhookSettings($webhookData);
                
                $json['success'] = 'Hepsiburada webhook ayarları başarıyla kaydedildi';
                
            } catch (Exception $e) {
                $json['error'] = 'Hepsiburada webhook ayarları kaydedilirken hata oluştu: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Webhook test et
     */
    public function test() {
        $this->load->language('extension/module/hepsiburada');
        $this->load->model('extension/module/hepsiburada');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $webhookType = $this->request->post['webhook_type'] ?? 'orders';
                
                // Test verisi oluştur
                $testData = [
                    'test' => true,
                    'timestamp' => date('c'),
                    'eventType' => 'ORDER_STATUS_CHANGED',
                    'data' => [
                        'orderId' => 'HB-TEST-' . time(),
                        'status' => 'Shipped',
                        'trackingNumber' => 'HB-TRACK-123456',
                        'message' => 'Hepsiburada webhook test mesajı'
                    ]
                ];
                
                // Webhook URL'ini çağır
                $webhookUrl = HTTPS_CATALOG . 'index.php?route=extension/module/hepsiburada_webhook/' . $webhookType;
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $webhookUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'X-Hepsiburada-Signature: test-signature',
                    'User-Agent: Hepsiburada-Webhook/1.0'
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode == 200) {
                    $json['success'] = 'Hepsiburada webhook testi başarılı';
                } else {
                    $json['error'] = 'Hepsiburada webhook testi başarısız. HTTP Code: ' . $httpCode;
                }
                
            } catch (Exception $e) {
                $json['error'] = 'Hepsiburada webhook testi sırasında hata: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Webhook loglarını görüntüle
     */
    public function logs() {
        $this->load->language('extension/module/hepsiburada');
        $this->load->model('extension/module/hepsiburada');
        
        $data['logs'] = $this->model_extension_module_hepsiburada->getWebhookLogs();
        
        $this->response->setOutput($this->load->view('extension/module/hepsiburada_webhook_logs', $data));
    }
    
    /**
     * Webhook imza doğrulama
     */
    public function verifySignature() {
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $payload = file_get_contents('php://input');
                $signature = isset($_SERVER['HTTP_X_HEPSIBURADA_SIGNATURE']) ? $_SERVER['HTTP_X_HEPSIBURADA_SIGNATURE'] : '';
                $secretKey = $this->config->get('module_hepsiburada_webhook_secret');
                
                // İmza doğrulama
                $expectedSignature = hash_hmac('sha256', $payload, $secretKey);
                
                if (hash_equals($expectedSignature, $signature)) {
                    $json['success'] = 'Hepsiburada webhook imzası doğrulandı';
                } else {
                    $json['error'] = 'Webhook imzası geçersiz';
                }
                
            } catch (Exception $e) {
                $json['error'] = 'İmza doğrulama hatası: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
} 