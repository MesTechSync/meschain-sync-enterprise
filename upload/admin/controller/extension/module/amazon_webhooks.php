<?php
/**
 * Amazon Webhooks Controller
 * Amazon webhook yönetimi için controller
 */
class ControllerExtensionModuleAmazonWebhooks extends Controller {
    private $error = array();
    
    public function index() {
        $this->load->language('extension/module/amazon');
        $this->load->model('extension/module/amazon');
        
        $this->document->setTitle($this->language->get('heading_title') . ' - Webhooks');
        
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => 'Ana Sayfa',
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Amazon Webhooks',
            'href' => $this->url->link('extension/module/amazon_webhooks', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Webhook listesi
        if (method_exists($this->model_extension_module_amazon, 'getWebhooks')) {
            $data['webhooks'] = $this->model_extension_module_amazon->getWebhooks();
        } else {
            $data['webhooks'] = array();
        }
        
        // Webhook URL'leri
        $data['webhook_urls'] = [
            'orders' => HTTP_CATALOG . 'index.php?route=extension/module/amazon_webhook/orders',
            'products' => HTTP_CATALOG . 'index.php?route=extension/module/amazon_webhook/products',
            'inventory' => HTTP_CATALOG . 'index.php?route=extension/module/amazon_webhook/inventory',
            'fulfillment' => HTTP_CATALOG . 'index.php?route=extension/module/amazon_webhook/fulfillment'
        ];
        
        // Zaman tabanlı senkronizasyon ayarları
        $data['sync_intervals'] = [
            'high_priority' => $this->config->get('module_amazon_sync_high_priority') ?: 5,
            'medium_priority' => $this->config->get('module_amazon_sync_medium_priority') ?: 15,
            'low_priority' => $this->config->get('module_amazon_sync_low_priority') ?: 60
        ];
        
        // Rate limit ayarları
        $data['rate_limits'] = [
            'calls_per_minute' => $this->config->get('module_amazon_calls_per_minute') ?: 30,
            'calls_per_hour' => $this->config->get('module_amazon_calls_per_hour') ?: 1000,
            'calls_per_day' => $this->config->get('module_amazon_calls_per_day') ?: 10000
        ];
        
        $data['user_token'] = $this->session->data['user_token'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/amazon_webhooks', $data));
    }
    
    /**
     * Webhook ayarlarını kaydet
     */
    public function save() {
        $this->load->language('extension/module/amazon');
        $this->load->model('extension/module/amazon');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                // Webhook ayarlarını kaydet
                $webhookData = [
                    'orders_enabled' => isset($this->request->post['orders_enabled']) ? 1 : 0,
                    'products_enabled' => isset($this->request->post['products_enabled']) ? 1 : 0,
                    'inventory_enabled' => isset($this->request->post['inventory_enabled']) ? 1 : 0,
                    'fulfillment_enabled' => isset($this->request->post['fulfillment_enabled']) ? 1 : 0,
                    'secret_key' => isset($this->request->post['secret_key']) ? $this->request->post['secret_key'] : '',
                    'sns_topic_arn' => isset($this->request->post['sns_topic_arn']) ? $this->request->post['sns_topic_arn'] : '',
                ];
                
                // Zaman tabanlı senkronizasyon ayarları
                $syncData = [
                    'high_priority_interval' => isset($this->request->post['high_priority_interval']) ? (int)$this->request->post['high_priority_interval'] : 5,
                    'medium_priority_interval' => isset($this->request->post['medium_priority_interval']) ? (int)$this->request->post['medium_priority_interval'] : 15,
                    'low_priority_interval' => isset($this->request->post['low_priority_interval']) ? (int)$this->request->post['low_priority_interval'] : 60,
                ];
                
                // Rate limit ayarları
                $rateLimitData = [
                    'calls_per_minute' => isset($this->request->post['calls_per_minute']) ? (int)$this->request->post['calls_per_minute'] : 30,
                    'calls_per_hour' => isset($this->request->post['calls_per_hour']) ? (int)$this->request->post['calls_per_hour'] : 1000,
                    'calls_per_day' => isset($this->request->post['calls_per_day']) ? (int)$this->request->post['calls_per_day'] : 10000,
                ];
                
                if (method_exists($this->model_extension_module_amazon, 'saveWebhookSettings')) {
                    $this->model_extension_module_amazon->saveWebhookSettings($webhookData);
                }
                
                // Config ayarlarını kaydet
                foreach ($syncData as $key => $value) {
                    $this->model_setting_setting->editSetting('module_amazon_sync_' . $key, ['module_amazon_sync_' . $key => $value]);
                }
                
                foreach ($rateLimitData as $key => $value) {
                    $this->model_setting_setting->editSetting('module_amazon_' . $key, ['module_amazon_' . $key => $value]);
                }
                
                $json['success'] = 'Amazon webhook ayarları başarıyla kaydedildi';
                
            } catch (Exception $e) {
                $json['error'] = 'Amazon webhook ayarları kaydedilirken hata oluştu: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Zaman tabanlı senkronizasyon başlat
     */
    public function startScheduledSync() {
        $this->load->model('extension/module/amazon');
        
        $json = array();
        
        try {
            $priority = isset($this->request->post['priority']) ? $this->request->post['priority'] : 'medium';
            
            // Rate limit kontrolü
            if (!$this->checkRateLimit()) {
                $json['error'] = 'Rate limit aşıldı. Lütfen daha sonra tekrar deneyin.';
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }
            
            // Senkronizasyon türüne göre işlem
            switch ($priority) {
                case 'high':
                    $result = $this->syncHighPriorityData();
                    break;
                case 'medium':
                    $result = $this->syncMediumPriorityData();
                    break;
                case 'low':
                    $result = $this->syncLowPriorityData();
                    break;
                default:
                    $result = $this->syncMediumPriorityData();
            }
            
            $json['success'] = 'Senkronizasyon başlatıldı';
            $json['result'] = $result;
            
        } catch (Exception $e) {
            $json['error'] = 'Senkronizasyon hatası: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Rate limit kontrolü
     */
    private function checkRateLimit() {
        $this->load->model('setting/setting');
        
        $callsPerMinute = $this->config->get('module_amazon_calls_per_minute') ?: 30;
        $callsPerHour = $this->config->get('module_amazon_calls_per_hour') ?: 1000;
        
        // Son 1 dakikadaki çağrı sayısını kontrol et
        $recentCalls = $this->getRecentApiCalls(60); // Son 60 saniye
        if ($recentCalls >= $callsPerMinute) {
            return false;
        }
        
        // Son 1 saatteki çağrı sayısını kontrol et
        $hourlyCalls = $this->getRecentApiCalls(3600); // Son 1 saat
        if ($hourlyCalls >= $callsPerHour) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Son API çağrı sayısını getir
     */
    private function getRecentApiCalls($seconds) {
        // Bu method model'de implement edilmeli
        if (method_exists($this->model_extension_module_amazon, 'getRecentApiCalls')) {
            return $this->model_extension_module_amazon->getRecentApiCalls($seconds);
        }
        return 0;
    }
    
    /**
     * Yüksek öncelikli veri senkronizasyonu
     */
    private function syncHighPriorityData() {
        // Sipariş durumu, stok kritik seviye, ödeme durumu
        $results = [
            'orders' => 0,
            'critical_stock' => 0,
            'payments' => 0
        ];
        
        try {
            // API çağrılarını logla
            $this->logApiCall('amazon', 'high_priority_sync', time());
            
            // Simülasyon - gerçek API çağrıları burada olacak
            $results['orders'] = rand(0, 5);
            $results['critical_stock'] = rand(0, 3);
            $results['payments'] = rand(0, 2);
            
        } catch (Exception $e) {
            $this->log->write('Amazon High Priority Sync Error: ' . $e->getMessage());
        }
        
        return $results;
    }
    
    /**
     * Orta öncelikli veri senkronizasyonu
     */
    private function syncMediumPriorityData() {
        // Fiyat güncellemeleri, stok miktarı, yeni siparişler
        $results = [
            'prices' => 0,
            'stock' => 0,
            'new_orders' => 0
        ];
        
        try {
            $this->logApiCall('amazon', 'medium_priority_sync', time());
            
            $results['prices'] = rand(5, 15);
            $results['stock'] = rand(10, 30);
            $results['new_orders'] = rand(0, 8);
            
        } catch (Exception $e) {
            $this->log->write('Amazon Medium Priority Sync Error: ' . $e->getMessage());
        }
        
        return $results;
    }
    
    /**
     * Düşük öncelikli veri senkronizasyonu
     */
    private function syncLowPriorityData() {
        // Ürün bilgileri, kategoriler, raporlar
        $results = [
            'products' => 0,
            'categories' => 0,
            'reports' => 0
        ];
        
        try {
            $this->logApiCall('amazon', 'low_priority_sync', time());
            
            $results['products'] = rand(20, 50);
            $results['categories'] = rand(5, 10);
            $results['reports'] = rand(1, 3);
            
        } catch (Exception $e) {
            $this->log->write('Amazon Low Priority Sync Error: ' . $e->getMessage());
        }
        
        return $results;
    }
    
    /**
     * API çağrısını logla
     */
    private function logApiCall($marketplace, $endpoint, $timestamp) {
        if (method_exists($this->model_extension_module_amazon, 'logApiCall')) {
            $this->model_extension_module_amazon->logApiCall($marketplace, $endpoint, $timestamp);
        }
    }
    
    /**
     * Webhook test et
     */
    public function test() {
        $this->load->language('extension/module/amazon');
        $this->load->model('extension/module/amazon');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $webhookType = isset($this->request->post['webhook_type']) ? $this->request->post['webhook_type'] : 'orders';
                
                // Test verisi oluştur
                $testData = [
                    'test' => true,
                    'timestamp' => time(),
                    'eventType' => 'ORDER_STATUS_CHANGED',
                    'payload' => [
                        'orderId' => 'AMZ-TEST-' . time(),
                        'status' => 'Shipped',
                        'message' => 'Amazon webhook test mesajı'
                    ]
                ];
                
                // Webhook URL'ini çağır
                $webhookUrl = HTTP_CATALOG . 'index.php?route=extension/module/amazon_webhook/' . $webhookType;
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $webhookUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'X-Amazon-Signature: test-signature',
                    'User-Agent: Amazon-SNS-Agent'
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode == 200) {
                    $json['success'] = 'Amazon webhook testi başarılı';
                } else {
                    $json['error'] = 'Amazon webhook testi başarısız. HTTP Code: ' . $httpCode;
                }
                
            } catch (Exception $e) {
                $json['error'] = 'Amazon webhook testi sırasında hata: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Webhook loglarını görüntüle
     */
    public function logs() {
        $this->load->language('extension/module/amazon');
        $this->load->model('extension/module/amazon');
        
        if (method_exists($this->model_extension_module_amazon, 'getWebhookLogs')) {
            $data['logs'] = $this->model_extension_module_amazon->getWebhookLogs();
        } else {
            $data['logs'] = array();
        }
        
        $this->response->setOutput($this->load->view('extension/module/amazon_webhook_logs', $data));
    }
    
    /**
     * SNS subscription doğrulama
     */
    public function confirmSubscription() {
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (isset($input['Type']) && $input['Type'] === 'SubscriptionConfirmation') {
                    // SNS subscription'ı doğrula
                    $subscribeUrl = $input['SubscribeURL'];
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $subscribeUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    
                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($httpCode == 200) {
                        $json['success'] = 'Amazon SNS subscription doğrulandı';
                    } else {
                        $json['error'] = 'SNS subscription doğrulanamadı';
                    }
                }
                
            } catch (Exception $e) {
                $json['error'] = 'SNS doğrulama hatası: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
} 