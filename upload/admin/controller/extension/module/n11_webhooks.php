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
                    'eventType' => $webhookType . '.test',
                    'order' => [
                        'id' => 'TEST-' . time(),
                        'totalPrice' => 100.00,
                        'customer' => [
                            'name' => 'Test Customer',
                            'email' => 'test@example.com'
                        ],
                        'items' => [
                            [
                                'sku' => 'TEST-SKU-001',
                                'quantity' => 1,
                                'price' => 100.00
                            ]
                        ]
                    ]
                ];
                
                // Load webhook processor
                require_once(DIR_SYSTEM . 'library/meschain/webhook/n11_webhook.php');
                
                $config = [
                    'n11_webhook_enabled' => true,
                    'n11_webhook_secret' => $this->config->get('module_n11_webhook_secret') ?: 'test-secret'
                ];
                
                $webhook = new N11Webhook($this->registry, $config);
                
                // Generate test signature
                $payload = json_encode($testData);
                $signature = hash_hmac('sha256', $payload, $config['n11_webhook_secret']);
                
                $headers = [
                    'X-N11-Signature' => $signature,
                    'X-N11-Timestamp' => time(),
                    'Content-Type' => 'application/json'
                ];
                
                // Process webhook
                $result = $webhook->processWebhook($headers, $payload);
                
                if ($result['success']) {
                    $json['success'] = 'Webhook testi başarılı: ' . $result['message'];
                    $json['details'] = $result;
                } else {
                    $json['error'] = 'Webhook testi başarısız: ' . $result['error'];
                    $json['details'] = $result;
                }
                
            } catch (Exception $e) {
                $json['error'] = 'Webhook testi sırasında hata: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Webhook endpoint handler
     */
    public function webhook() {
        try {
            // Get headers
            $headers = getallheaders() ?: [];
            
            // Get raw payload
            $payload = file_get_contents('php://input');
            
            if (empty($payload)) {
                http_response_code(400);
                echo json_encode(['error' => 'Empty payload']);
                return;
            }
            
            // Load webhook processor
            require_once(DIR_SYSTEM . 'library/meschain/webhook/n11_webhook.php');
            
            $config = [
                'n11_webhook_enabled' => (bool)$this->config->get('module_n11_webhook_enabled'),
                'n11_webhook_secret' => $this->config->get('module_n11_webhook_secret') ?: ''
            ];
            
            $webhook = new N11Webhook($this->registry, $config);
            
            // Process webhook
            $result = $webhook->processWebhook($headers, $payload);
            
            // Set response code
            http_response_code($result['http_code'] ?? 200);
            
            // Return response
            echo json_encode([
                'success' => $result['success'],
                'message' => $result['message'] ?? $result['error'] ?? 'Processed'
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
            
            // Log error
            error_log('N11 Webhook Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Webhook istatistikleri
     */
    public function stats() {
        $this->load->model('extension/module/n11');
        
        $data = [];
        
        // Son 24 saatteki webhook çağrıları
        $data['recent_webhooks'] = $this->getRecentWebhookStats(24);
        
        // Webhook türlerine göre istatistikler
        $data['webhook_types'] = $this->getWebhookTypeStats();
        
        // Başarı oranları
        $data['success_rates'] = $this->getWebhookSuccessRates();
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
    
    /**
     * Son webhook istatistiklerini getir
     */
    private function getRecentWebhookStats($hours = 24) {
        $query = $this->db->query("
            SELECT 
                DATE_FORMAT(date_added, '%H:00') as hour,
                COUNT(*) as count,
                SUM(CASE WHEN level = 'info' THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN level = 'error' THEN 1 ELSE 0 END) as error_count
            FROM " . DB_PREFIX . "meschain_marketplace_logs 
            WHERE marketplace = 'n11' 
            AND category = 'webhook'
            AND date_added >= DATE_SUB(NOW(), INTERVAL " . (int)$hours . " HOUR)
            GROUP BY DATE_FORMAT(date_added, '%H:00')
            ORDER BY hour
        ");
        
        return $query->rows;
    }
    
    /**
     * Webhook türlerine göre istatistikler
     */
    private function getWebhookTypeStats() {
        $query = $this->db->query("
            SELECT 
                JSON_EXTRACT(context, '$.event_type') as event_type,
                COUNT(*) as count,
                SUM(CASE WHEN level = 'info' THEN 1 ELSE 0 END) as success_count
            FROM " . DB_PREFIX . "meschain_marketplace_logs 
            WHERE marketplace = 'n11' 
            AND category = 'webhook'
            AND date_added >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY JSON_EXTRACT(context, '$.event_type')
            ORDER BY count DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * Webhook başarı oranları
     */
    private function getWebhookSuccessRates() {
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN level = 'info' THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN level = 'error' THEN 1 ELSE 0 END) as errors,
                ROUND((SUM(CASE WHEN level = 'info' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as success_rate
            FROM " . DB_PREFIX . "meschain_marketplace_logs 
            WHERE marketplace = 'n11' 
            AND category = 'webhook'
            AND date_added >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        return $query->num_rows ? $query->row : [
            'total' => 0,
            'success' => 0,
            'errors' => 0,
            'success_rate' => 0
        ];
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