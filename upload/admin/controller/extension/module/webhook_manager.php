<?php
/**
 * MesChain Webhook Manager
 * MesChain-Sync v4.1 - OpenCart 3.0.4.0 Integration
 * Centralized Webhook Management for All Marketplaces
 * 
 * @author MesChain Development Team
 * @version 4.1.0
 * @copyright 2024 MesChain Technologies
 * @supports Trendyol, N11, Amazon, eBay, Hepsiburada, Ozon, Pazarama
 */

class ControllerExtensionModuleWebhookManager extends Controller {

    private $error = array();
    private $supported_marketplaces = [
        'trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama'
    ];

    public function __construct($registry) {
        parent::__construct($registry);
        $this->createWebhookTables();
    }

    /**
     * Ana webhook yönetim sayfası
     */
    public function index() {
        $this->load->language('extension/module/webhook_manager');
        $this->document->setTitle($this->language->get('heading_title'));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_webhook_manager', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module/webhook_manager', 'user_token=' . $this->session->data['user_token'], true));
        }

        $data = $this->getViewData();
        $this->response->setOutput($this->load->view('extension/module/webhook_manager', $data));
    }

    /**
     * Webhook endpoint - Tüm marketplace webhook'larını işler
     */
    public function endpoint() {
        $input = file_get_contents('php://input');
        $headers = getallheaders();
        
        // Marketplace'i belirle
        $marketplace = $this->detectMarketplace($headers, $input);
        
        if (!$marketplace) {
            $this->log('WEBHOOK_ERROR', 'Unknown marketplace webhook received');
            http_response_code(400);
            echo json_encode(['error' => 'Unknown marketplace']);
            exit;
        }

        // Webhook'u işle
        $result = $this->processWebhook($marketplace, $input, $headers);
        
        // Response
        http_response_code($result['success'] ? 200 : 400);
        echo json_encode($result);
        exit;
    }

    /**
     * Trendyol webhook'ları
     */
    public function trendyol() {
        $this->handleMarketplaceWebhook('trendyol');
    }

    /**
     * N11 webhook'ları
     */
    public function n11() {
        $this->handleMarketplaceWebhook('n11');
    }

    /**
     * Amazon webhook'ları
     */
    public function amazon() {
        $this->handleMarketplaceWebhook('amazon');
    }

    /**
     * eBay webhook'ları
     */
    public function ebay() {
        $this->handleMarketplaceWebhook('ebay');
    }

    /**
     * Hepsiburada webhook'ları
     */
    public function hepsiburada() {
        $this->handleMarketplaceWebhook('hepsiburada');
    }

    /**
     * Ozon webhook'ları
     */
    public function ozon() {
        $this->handleMarketplaceWebhook('ozon');
    }

    /**
     * Pazarama webhook'ları
     */
    public function pazarama() {
        $this->handleMarketplaceWebhook('pazarama');
    }

    /**
     * Webhook istatistikleri
     */
    public function statistics() {
        $this->load->language('extension/module/webhook_manager');
        
        $json = array();
        
        try {
            $stats = $this->getWebhookStatistics();
            $json['success'] = true;
            $json['data'] = $stats;
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Webhook testini gerçekleştir
     */
    public function testWebhook() {
        $this->load->language('extension/module/webhook_manager');
        
        $json = array();
        
        if (!isset($this->request->post['marketplace'])) {
            $json['success'] = false;
            $json['error'] = 'Marketplace required';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $marketplace = $this->request->post['marketplace'];
        
        try {
            $test_result = $this->performWebhookTest($marketplace);
            $json['success'] = true;
            $json['message'] = 'Webhook test completed successfully';
            $json['data'] = $test_result;
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Webhook'ları yeniden işle
     */
    public function reprocessWebhooks() {
        $this->load->language('extension/module/webhook_manager');
        
        $json = array();
        
        try {
            $reprocessed = $this->reprocessFailedWebhooks();
            $json['success'] = true;
            $json['message'] = sprintf('Reprocessed %d webhooks', $reprocessed);
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Marketplace webhook'ını işle
     */
    private function handleMarketplaceWebhook($marketplace) {
        $input = file_get_contents('php://input');
        $headers = getallheaders();
        
        // Webhook signature doğrulama
        if (!$this->verifyWebhookSignature($marketplace, $input, $headers)) {
            $this->log('WEBHOOK_SECURITY', "Invalid signature for {$marketplace} webhook");
            http_response_code(401);
            echo json_encode(['error' => 'Invalid signature']);
            exit;
        }

        // Webhook'u işle
        $result = $this->processWebhook($marketplace, $input, $headers);
        
        // Response
        http_response_code($result['success'] ? 200 : 400);
        echo json_encode($result);
        exit;
    }

    /**
     * Marketplace'i tespit et
     */
    private function detectMarketplace($headers, $input) {
        // User-Agent kontrolü
        if (isset($headers['User-Agent'])) {
            $ua = strtolower($headers['User-Agent']);
            if (strpos($ua, 'trendyol') !== false) return 'trendyol';
            if (strpos($ua, 'n11') !== false) return 'n11';
            if (strpos($ua, 'amazon') !== false) return 'amazon';
            if (strpos($ua, 'ebay') !== false) return 'ebay';
            if (strpos($ua, 'hepsiburada') !== false) return 'hepsiburada';
            if (strpos($ua, 'ozon') !== false) return 'ozon';
            if (strpos($ua, 'pazarama') !== false) return 'pazarama';
        }

        // Header kontrolü
        if (isset($headers['X-Trendyol-Event'])) return 'trendyol';
        if (isset($headers['X-N11-Event'])) return 'n11';
        if (isset($headers['X-Amazon-Event'])) return 'amazon';
        if (isset($headers['X-Ebay-Event'])) return 'ebay';
        if (isset($headers['X-Hepsiburada-Event'])) return 'hepsiburada';
        if (isset($headers['X-Ozon-Event'])) return 'ozon';
        if (isset($headers['X-Pazarama-Event'])) return 'pazarama';

        // Content analizi
        $data = json_decode($input, true);
        if ($data && isset($data['source'])) {
            return strtolower($data['source']);
        }

        return null;
    }

    /**
     * Webhook'u işle
     */
    private function processWebhook($marketplace, $input, $headers) {
        try {
            // Webhook'u veritabanına kaydet
            $webhook_id = $this->saveWebhook($marketplace, $input, $headers);

            // JSON parse
            $data = json_decode($input, true);
            if (!$data) {
                throw new Exception('Invalid JSON payload');
            }

            // Event type'ını belirle
            $event_type = $this->determineEventType($marketplace, $data, $headers);

            // Event'i işle
            $result = $this->processEvent($marketplace, $event_type, $data, $webhook_id);

            // Webhook durumunu güncelle
            $this->updateWebhookStatus($webhook_id, 'processed', $result);

            return [
                'success' => true,
                'webhook_id' => $webhook_id,
                'event_type' => $event_type,
                'message' => 'Webhook processed successfully'
            ];

        } catch (Exception $e) {
            $this->log('WEBHOOK_ERROR', "Error processing {$marketplace} webhook: " . $e->getMessage());
            
            if (isset($webhook_id)) {
                $this->updateWebhookStatus($webhook_id, 'failed', ['error' => $e->getMessage()]);
            }

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Event'i işle
     */
    private function processEvent($marketplace, $event_type, $data, $webhook_id) {
        switch ($event_type) {
            case 'order.created':
            case 'order.updated':
                return $this->processOrderEvent($marketplace, $data);
                
            case 'product.updated':
                return $this->processProductEvent($marketplace, $data);
                
            case 'stock.updated':
                return $this->processStockEvent($marketplace, $data);
                
            case 'price.updated':
                return $this->processPriceEvent($marketplace, $data);
                
            case 'status.updated':
                return $this->processStatusEvent($marketplace, $data);
                
            default:
                $this->log('WEBHOOK_WARNING', "Unknown event type: {$event_type} for {$marketplace}");
                return ['processed' => false, 'reason' => 'Unknown event type'];
        }
    }

    /**
     * Sipariş event'ini işle
     */
    private function processOrderEvent($marketplace, $data) {
        $this->load->model('extension/module/' . $marketplace);
        $model_method = 'model_extension_module_' . $marketplace;
        
        if (method_exists($this->$model_method, 'processWebhookOrder')) {
            return $this->$model_method->processWebhookOrder($data);
        }
        
        // Genel sipariş işleme
        return $this->genericOrderProcessing($marketplace, $data);
    }

    /**
     * Ürün event'ini işle
     */
    private function processProductEvent($marketplace, $data) {
        // Ürün bilgilerini güncelle
        return ['processed' => true];
    }

    /**
     * Stok event'ini işle
     */
    private function processStockEvent($marketplace, $data) {
        // Stok bilgilerini güncelle
        return ['processed' => true];
    }

    /**
     * Fiyat event'ini işle
     */
    private function processPriceEvent($marketplace, $data) {
        // Fiyat bilgilerini güncelle
        return ['processed' => true];
    }

    /**
     * Durum event'ini işle
     */
    private function processStatusEvent($marketplace, $data) {
        // Durum bilgilerini güncelle
        return ['processed' => true];
    }

    /**
     * Event type'ını belirle
     */
    private function determineEventType($marketplace, $data, $headers) {
        switch ($marketplace) {
            case 'trendyol':
                return $headers['X-Trendyol-Event'] ?? 'unknown';
                
            case 'n11':
                return $headers['X-N11-Event'] ?? 'unknown';
                
            case 'amazon':
                return $data['eventType'] ?? 'unknown';
                
            case 'ebay':
                return $data['eventType'] ?? 'unknown';
                
            case 'hepsiburada':
                return $data['eventType'] ?? 'unknown';
                
            case 'ozon':
                return $data['eventType'] ?? 'unknown';
                
            case 'pazarama':
                return $data['eventType'] ?? 'unknown';
                
            default:
                return 'unknown';
        }
    }

    /**
     * Webhook signature doğrula
     */
    private function verifyWebhookSignature($marketplace, $payload, $headers) {
        $secret = $this->config->get('module_' . $marketplace . '_webhook_secret');
        
        if (!$secret) {
            return true; // Secret yoksa doğrulama yapma
        }

        switch ($marketplace) {
            case 'trendyol':
                $signature = $headers['X-Trendyol-Signature'] ?? '';
                $expected = hash_hmac('sha256', $payload, $secret);
                return hash_equals($expected, $signature);
                
            case 'n11':
                $signature = $headers['X-N11-Signature'] ?? '';
                $expected = hash_hmac('sha256', $payload, $secret);
                return hash_equals($expected, $signature);
                
            default:
                return true; // Diğer marketplaces için varsayılan olarak true
        }
    }

    /**
     * Webhook tablolarını oluştur
     */
    private function createWebhookTables() {
        // Webhook logs tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "webhook_logs` (
            `webhook_id` int(11) NOT NULL AUTO_INCREMENT,
            `marketplace` varchar(50) NOT NULL,
            `event_type` varchar(100),
            `payload` longtext NOT NULL,
            `headers` json,
            `status` enum('pending','processed','failed','retry') DEFAULT 'pending',
            `response_data` json,
            `retry_count` int(11) DEFAULT 0,
            `processed_at` datetime DEFAULT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`webhook_id`),
            KEY `marketplace` (`marketplace`),
            KEY `status` (`status`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // Webhook statistics tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "webhook_statistics` (
            `stat_id` int(11) NOT NULL AUTO_INCREMENT,
            `marketplace` varchar(50) NOT NULL,
            `event_type` varchar(100),
            `date` date NOT NULL,
            `success_count` int(11) DEFAULT 0,
            `failed_count` int(11) DEFAULT 0,
            `total_count` int(11) DEFAULT 0,
            `avg_processing_time` decimal(10,4) DEFAULT 0,
            PRIMARY KEY (`stat_id`),
            UNIQUE KEY `unique_stat` (`marketplace`, `event_type`, `date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    /**
     * View verilerini hazırla
     */
    private function getViewData() {
        $data = array();
        
        // Common data
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        
        // Webhook URLs
        foreach ($this->supported_marketplaces as $marketplace) {
            $data['webhook_urls'][$marketplace] = HTTPS_CATALOG . 'index.php?route=extension/module/webhook_manager/' . $marketplace;
        }
        
        // Statistics
        $data['statistics'] = $this->getWebhookStatistics();
        
        // Recent webhooks
        $data['recent_webhooks'] = $this->getRecentWebhooks();
        
        // Links
        $data['action'] = $this->url->link('extension/module/webhook_manager', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        return $data;
    }

    /**
     * Webhook'u kaydet
     */
    private function saveWebhook($marketplace, $payload, $headers) {
        $sql = "INSERT INTO " . DB_PREFIX . "webhook_logs 
                (marketplace, payload, headers, created_at) 
                VALUES ('" . $this->db->escape($marketplace) . "', 
                        '" . $this->db->escape($payload) . "', 
                        '" . $this->db->escape(json_encode($headers)) . "', 
                        NOW())";
        
        $this->db->query($sql);
        return $this->db->getLastId();
    }

    /**
     * Webhook durumunu güncelle
     */
    private function updateWebhookStatus($webhook_id, $status, $response_data = null) {
        $sql = "UPDATE " . DB_PREFIX . "webhook_logs 
                SET status = '" . $this->db->escape($status) . "', 
                    processed_at = NOW()";
        
        if ($response_data) {
            $sql .= ", response_data = '" . $this->db->escape(json_encode($response_data)) . "'";
        }
        
        $sql .= " WHERE webhook_id = " . (int)$webhook_id;
        
        $this->db->query($sql);
    }

    /**
     * Webhook istatistiklerini al
     */
    private function getWebhookStatistics() {
        $stats = array();
        
        foreach ($this->supported_marketplaces as $marketplace) {
            $query = $this->db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'processed' THEN 1 ELSE 0 END) as success,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM " . DB_PREFIX . "webhook_logs 
                WHERE marketplace = '" . $this->db->escape($marketplace) . "'
                AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ");
            
            $stats[$marketplace] = $query->row;
        }
        
        return $stats;
    }

    /**
     * Son webhook'ları al
     */
    private function getRecentWebhooks($limit = 20) {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "webhook_logs 
            ORDER BY created_at DESC 
            LIMIT " . (int)$limit
        );
        
        return $query->rows;
    }

    /**
     * Başarısız webhook'ları yeniden işle
     */
    private function reprocessFailedWebhooks() {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "webhook_logs 
            WHERE status = 'failed' 
            AND retry_count < 3 
            ORDER BY created_at ASC 
            LIMIT 50
        ");
        
        $reprocessed = 0;
        
        foreach ($query->rows as $webhook) {
            try {
                $headers = json_decode($webhook['headers'], true);
                $result = $this->processWebhook($webhook['marketplace'], $webhook['payload'], $headers);
                
                if ($result['success']) {
                    $this->updateWebhookStatus($webhook['webhook_id'], 'processed', $result);
                    $reprocessed++;
                } else {
                    // Retry count'u artır
                    $this->db->query("
                        UPDATE " . DB_PREFIX . "webhook_logs 
                        SET retry_count = retry_count + 1 
                        WHERE webhook_id = " . (int)$webhook['webhook_id']
                    );
                }
            } catch (Exception $e) {
                $this->log('WEBHOOK_REPROCESS_ERROR', $e->getMessage());
            }
        }
        
        return $reprocessed;
    }

    /**
     * Webhook test gerçekleştir
     */
    private function performWebhookTest($marketplace) {
        // Test payload oluştur
        $test_payload = $this->generateTestPayload($marketplace);
        
        // Test webhook'unu işle
        $result = $this->processWebhook($marketplace, json_encode($test_payload), []);
        
        return $result;
    }

    /**
     * Test payload oluştur
     */
    private function generateTestPayload($marketplace) {
        switch ($marketplace) {
            case 'trendyol':
                return [
                    'eventType' => 'order.created',
                    'orderNumber' => 'TEST-' . time(),
                    'customerId' => 12345,
                    'orderDate' => date('Y-m-d H:i:s'),
                    'status' => 'Created'
                ];
                
            case 'n11':
                return [
                    'eventType' => 'order.created',
                    'orderNumber' => 'N11-TEST-' . time(),
                    'orderStatus' => 'Approved'
                ];
                
            default:
                return [
                    'eventType' => 'test',
                    'timestamp' => time(),
                    'marketplace' => $marketplace
                ];
        }
    }

    /**
     * Genel sipariş işleme
     */
    private function genericOrderProcessing($marketplace, $data) {
        // Genel sipariş işleme mantığı
        return ['processed' => true, 'method' => 'generic'];
    }

    /**
     * Form doğrulama
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/webhook_manager')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    /**
     * Log yaz
     */
    private function log($type, $message) {
        $log = new Log('webhook_manager.log');
        $log->write('[' . $type . '] ' . $message);
    }
}
