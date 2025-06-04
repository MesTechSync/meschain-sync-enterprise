<?php
/**
 * N11 Webhooks Controller
 * N11 webhook yönetimi için controller
 */
class ControllerExtensionModuleN11Webhooks extends Controller {
    private $error = array();
    
    public function index() {
        $this->load->language('extension/module/n11');
        $this->load->model('extension/module/n11');
        
        $this->document->setTitle($this->language->get('heading_title') . ' - Webhooks');
        
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/n11_webhooks', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Webhook listesi
        $data['webhooks'] = $this->model_extension_module_n11->getWebhooks();
        
        // Webhook URL'leri
        $data['webhook_urls'] = [
            'orders' => HTTPS_CATALOG . 'index.php?route=extension/module/n11_webhook/orders',
            'products' => HTTPS_CATALOG . 'index.php?route=extension/module/n11_webhook/products',
            'inventory' => HTTPS_CATALOG . 'index.php?route=extension/module/n11_webhook/inventory'
        ];
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/n11_webhooks', $data));
    }
    
    /**
     * Webhook ayarlarını kaydet
     */
    public function save() {
        $this->load->language('extension/module/n11');
        $this->load->model('extension/module/n11');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                // Webhook ayarlarını kaydet
                $webhookData = [
                    'orders_enabled' => isset($this->request->post['orders_enabled']) ? 1 : 0,
                    'products_enabled' => isset($this->request->post['products_enabled']) ? 1 : 0,
                    'inventory_enabled' => isset($this->request->post['inventory_enabled']) ? 1 : 0,
                    'secret_key' => $this->request->post['secret_key'] ?? '',
                ];
                
                $this->model_extension_module_n11->saveWebhookSettings($webhookData);
                
                $json['success'] = 'Webhook ayarları başarıyla kaydedildi';
                
            } catch (Exception $e) {
                $json['error'] = 'Webhook ayarları kaydedilirken hata oluştu: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Webhook test et
     */
    public function test() {
        $this->load->language('extension/module/n11');
        $this->load->model('extension/module/n11');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $webhookType = $this->request->post['webhook_type'] ?? 'orders';
                
                // Test verisi oluştur
                $testData = [
                    'test' => true,
                    'timestamp' => time(),
                    'event' => $webhookType . '.test',
                    'data' => [
                        'message' => 'N11 webhook test mesajı'
                    ]
                ];
                
                // Webhook URL'ini çağır
                $webhookUrl = HTTPS_CATALOG . 'index.php?route=extension/module/n11_webhook/' . $webhookType;
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $webhookUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'X-N11-Signature: test-signature'
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode == 200) {
                    $json['success'] = 'Webhook testi başarılı';
                } else {
                    $json['error'] = 'Webhook testi başarısız. HTTP Code: ' . $httpCode;
                }
                
            } catch (Exception $e) {
                $json['error'] = 'Webhook testi sırasında hata: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Webhook loglarını görüntüle
     */
    public function logs() {
        $this->load->language('extension/module/n11');
        $this->load->model('extension/module/n11');
        
        $data['logs'] = $this->model_extension_module_n11->getWebhookLogs();
        
        $this->response->setOutput($this->load->view('extension/module/n11_webhook_logs', $data));
    }
} 