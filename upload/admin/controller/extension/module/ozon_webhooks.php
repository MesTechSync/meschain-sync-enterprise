<?php
/**
 * Ozon Webhooks Controller
 * Ozon webhook yönetimi için controller
 */
class ControllerExtensionModuleOzonWebhooks extends Controller {
    private $error = array();
    
    public function index() {
        $this->load->language('extension/module/ozon');
        $this->load->model('extension/module/ozon');
        
        $this->document->setTitle('Ozon Webhooks');
        
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => 'Ana Sayfa',
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Ozon Webhooks',
            'href' => $this->url->link('extension/module/ozon_webhooks', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Webhook listesi
        $data['webhooks'] = $this->model_extension_module_ozon->getWebhooks();
        
        // Webhook URL'leri
        $data['webhook_urls'] = [
            'orders' => HTTPS_CATALOG . 'index.php?route=extension/module/ozon_webhook/orders',
            'products' => HTTPS_CATALOG . 'index.php?route=extension/module/ozon_webhook/products',
            'stocks' => HTTPS_CATALOG . 'index.php?route=extension/module/ozon_webhook/stocks',
            'prices' => HTTPS_CATALOG . 'index.php?route=extension/module/ozon_webhook/prices',
            'returns' => HTTPS_CATALOG . 'index.php?route=extension/module/ozon_webhook/returns'
        ];
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/ozon_webhooks', $data));
    }
    
    /**
     * Webhook ayarlarını kaydet
     */
    public function save() {
        $this->load->language('extension/module/ozon');
        $this->load->model('extension/module/ozon');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                // Webhook ayarlarını kaydet
                $webhookData = [
                    'orders_enabled' => isset($this->request->post['orders_enabled']) ? 1 : 0,
                    'products_enabled' => isset($this->request->post['products_enabled']) ? 1 : 0,
                    'stocks_enabled' => isset($this->request->post['stocks_enabled']) ? 1 : 0,
                    'prices_enabled' => isset($this->request->post['prices_enabled']) ? 1 : 0,
                    'returns_enabled' => isset($this->request->post['returns_enabled']) ? 1 : 0,
                    'secret_key' => $this->request->post['secret_key'] ?? '',
                    'client_id' => $this->request->post['client_id'] ?? '',
                ];
                
                $this->model_extension_module_ozon->saveWebhookSettings($webhookData);
                
                $json['success'] = 'Ozon webhook ayarları başarıyla kaydedildi';
                
            } catch (Exception $e) {
                $json['error'] = 'Ozon webhook ayarları kaydedilirken hata oluştu: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Webhook test et
     */
    public function test() {
        $this->load->language('extension/module/ozon');
        $this->load->model('extension/module/ozon');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $webhookType = $this->request->post['webhook_type'] ?? 'orders';
                
                // Test verisi oluştur
                $testData = [
                    'message_type' => 'TYPE_NEW_POSTING',
                    'version' => '1',
                    'time' => date('c'),
                    'data' => [
                        [
                            'posting_number' => 'OZON-TEST-' . time(),
                            'order_id' => time(),
                            'order_number' => 'ORDER-' . time(),
                            'status' => 'awaiting_packaging',
                            'delivery_method' => [
                                'id' => 1,
                                'name' => 'Ozon Logistics'
                            ],
                            'tracking_number' => 'OZON-TRACK-123456',
                            'tpl_integration_type' => 'ozon'
                        ]
                    ]
                ];
                
                // Webhook URL'ini çağır
                $webhookUrl = HTTPS_CATALOG . 'index.php?route=extension/module/ozon_webhook/' . $webhookType;
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $webhookUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'X-Ozon-Signature: test-signature',
                    'User-Agent: Ozon-Webhook/1.0'
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode == 200) {
                    $json['success'] = 'Ozon webhook testi başarılı';
                } else {
                    $json['error'] = 'Ozon webhook testi başarısız. HTTP Code: ' . $httpCode;
                }
                
            } catch (Exception $e) {
                $json['error'] = 'Ozon webhook testi sırasında hata: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Webhook loglarını görüntüle
     */
    public function logs() {
        $this->load->language('extension/module/ozon');
        $this->load->model('extension/module/ozon');
        
        $data['logs'] = $this->model_extension_module_ozon->getWebhookLogs();
        
        $this->response->setOutput($this->load->view('extension/module/ozon_webhook_logs', $data));
    }
    
    /**
     * Webhook imza doğrulama
     */
    public function verifySignature() {
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $payload = file_get_contents('php://input');
                $signature = isset($_SERVER['HTTP_X_OZON_SIGNATURE']) ? $_SERVER['HTTP_X_OZON_SIGNATURE'] : '';
                $secretKey = $this->config->get('module_ozon_webhook_secret');
                
                // İmza doğrulama
                $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $secretKey, true));
                
                if (hash_equals($expectedSignature, $signature)) {
                    $json['success'] = 'Ozon webhook imzası doğrulandı';
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
    
    /**
     * Webhook subscription yönetimi
     */
    public function manageSubscription() {
        $this->load->language('extension/module/ozon');
        $this->load->model('extension/module/ozon');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $action = $this->request->post['action'] ?? 'subscribe';
                $eventType = $this->request->post['event_type'] ?? 'TYPE_NEW_POSTING';
                
                switch ($action) {
                    case 'subscribe':
                        $result = $this->model_extension_module_ozon->subscribeWebhook($eventType);
                        $json['success'] = 'Ozon webhook subscription oluşturuldu';
                        break;
                    case 'unsubscribe':
                        $result = $this->model_extension_module_ozon->unsubscribeWebhook($eventType);
                        $json['success'] = 'Ozon webhook subscription iptal edildi';
                        break;
                    case 'list':
                        $result = $this->model_extension_module_ozon->listSubscriptions();
                        $json['data'] = $result;
                        $json['success'] = 'Subscription listesi alındı';
                        break;
                    default:
                        $json['error'] = 'Geçersiz işlem';
                        break;
                }
                
            } catch (Exception $e) {
                $json['error'] = 'Subscription işlemi hatası: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
} 