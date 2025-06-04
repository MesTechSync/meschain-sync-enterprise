<?php
/**
 * eBay Webhooks Controller
 * eBay webhook yönetimi için controller
 */
class ControllerExtensionModuleEbayWebhooks extends Controller {
    private $error = array();
    
    public function index() {
        $this->load->language('extension/module/ebay');
        $this->load->model('extension/module/ebay');
        
        $this->document->setTitle('eBay Webhooks');
        
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => 'Ana Sayfa',
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'eBay Webhooks',
            'href' => $this->url->link('extension/module/ebay_webhooks', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Webhook listesi
        $data['webhooks'] = $this->model_extension_module_ebay->getWebhooks();
        
        // Webhook URL'leri
        $data['webhook_urls'] = [
            'orders' => HTTPS_CATALOG . 'index.php?route=extension/module/ebay_webhook/orders',
            'listings' => HTTPS_CATALOG . 'index.php?route=extension/module/ebay_webhook/listings',
            'inventory' => HTTPS_CATALOG . 'index.php?route=extension/module/ebay_webhook/inventory',
            'disputes' => HTTPS_CATALOG . 'index.php?route=extension/module/ebay_webhook/disputes'
        ];
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/ebay_webhooks', $data));
    }
    
    /**
     * Webhook ayarlarını kaydet
     */
    public function save() {
        $this->load->language('extension/module/ebay');
        $this->load->model('extension/module/ebay');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                // Webhook ayarlarını kaydet
                $webhookData = [
                    'orders_enabled' => isset($this->request->post['orders_enabled']) ? 1 : 0,
                    'listings_enabled' => isset($this->request->post['listings_enabled']) ? 1 : 0,
                    'inventory_enabled' => isset($this->request->post['inventory_enabled']) ? 1 : 0,
                    'disputes_enabled' => isset($this->request->post['disputes_enabled']) ? 1 : 0,
                    'verification_token' => $this->request->post['verification_token'] ?? '',
                    'endpoint_url' => $this->request->post['endpoint_url'] ?? '',
                ];
                
                $this->model_extension_module_ebay->saveWebhookSettings($webhookData);
                
                $json['success'] = 'eBay webhook ayarları başarıyla kaydedildi';
                
            } catch (Exception $e) {
                $json['error'] = 'eBay webhook ayarları kaydedilirken hata oluştu: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Webhook test et
     */
    public function test() {
        $this->load->language('extension/module/ebay');
        $this->load->model('extension/module/ebay');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $webhookType = $this->request->post['webhook_type'] ?? 'orders';
                
                // Test verisi oluştur
                $testData = [
                    'metadata' => [
                        'topic' => 'MARKETPLACE_ACCOUNT_DELETION',
                        'schemaVersion' => '1.0',
                        'deprecated' => false
                    ],
                    'notification' => [
                        'notificationId' => 'EBAY-TEST-' . time(),
                        'eventDate' => date('c'),
                        'publishDate' => date('c'),
                        'sourceRecordId' => 'TEST-RECORD-123',
                        'data' => [
                            'username' => 'test_user',
                            'userId' => 'TEST-USER-' . time(),
                            'eiasToken' => 'test-token'
                        ]
                    ]
                ];
                
                // Webhook URL'ini çağır
                $webhookUrl = HTTPS_CATALOG . 'index.php?route=extension/module/ebay_webhook/' . $webhookType;
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $webhookUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'X-EBAY-SIGNATURE: test-signature',
                    'User-Agent: eBay-Marketplace-Notifications/1.0'
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode == 200) {
                    $json['success'] = 'eBay webhook testi başarılı';
                } else {
                    $json['error'] = 'eBay webhook testi başarısız. HTTP Code: ' . $httpCode;
                }
                
            } catch (Exception $e) {
                $json['error'] = 'eBay webhook testi sırasında hata: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Webhook loglarını görüntüle
     */
    public function logs() {
        $this->load->language('extension/module/ebay');
        $this->load->model('extension/module/ebay');
        
        $data['logs'] = $this->model_extension_module_ebay->getWebhookLogs();
        
        $this->response->setOutput($this->load->view('extension/module/ebay_webhook_logs', $data));
    }
    
    /**
     * Webhook challenge doğrulama
     */
    public function verifyChallenge() {
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'GET') {
            try {
                $challengeCode = isset($this->request->get['challenge_code']) ? $this->request->get['challenge_code'] : '';
                $verificationToken = $this->config->get('module_ebay_verification_token');
                
                // Challenge code'u hash'le ve geri döndür
                $challengeResponse = hash('sha256', $challengeCode . $verificationToken . $challengeCode);
                
                $json['challengeResponse'] = $challengeResponse;
                
            } catch (Exception $e) {
                $json['error'] = 'Challenge doğrulama hatası: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Subscription yönetimi
     */
    public function manageSubscription() {
        $this->load->language('extension/module/ebay');
        $this->load->model('extension/module/ebay');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $action = $this->request->post['action'] ?? 'create';
                $topicId = $this->request->post['topic_id'] ?? '';
                
                switch ($action) {
                    case 'create':
                        $result = $this->model_extension_module_ebay->createSubscription($topicId);
                        $json['success'] = 'eBay subscription oluşturuldu';
                        break;
                    case 'update':
                        $result = $this->model_extension_module_ebay->updateSubscription($topicId);
                        $json['success'] = 'eBay subscription güncellendi';
                        break;
                    case 'delete':
                        $result = $this->model_extension_module_ebay->deleteSubscription($topicId);
                        $json['success'] = 'eBay subscription silindi';
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