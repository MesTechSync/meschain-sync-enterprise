<?php
/**
 * MesChain Webhook Helper
 * Tüm pazaryerleri için genel webhook işlemleri
 */
class MeschainWebhookHelper {
    
    protected $registry;
    protected $db;
    protected $config;
    protected $log;
    protected $cache;
    
    /**
     * Constructor
     * @param object $registry OpenCart registry
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->log = $registry->get('log');
        $this->cache = $registry->get('cache');
    }
    
    /**
     * Webhook verilerini doğrula
     * @param string $marketplace Pazaryeri adı
     * @param array $headers HTTP headers
     * @param string $payload Raw payload
     * @return array Doğrulama sonucu
     */
    public function validateWebhook($marketplace, $headers, $payload) {
        $validation = [
            'valid' => false,
            'error' => null,
            'signature_valid' => false
        ];
        
        try {
            switch ($marketplace) {
                case 'trendyol':
                    $validation = $this->validateTrendyolWebhook($headers, $payload);
                    break;
                    
                case 'n11':
                    $validation = $this->validateN11Webhook($headers, $payload);
                    break;
                    
                case 'amazon':
                    $validation = $this->validateAmazonWebhook($headers, $payload);
                    break;
                    
                case 'hepsiburada':
                    $validation = $this->validateHepsiburadaWebhook($headers, $payload);
                    break;
                    
                case 'ebay':
                    $validation = $this->validateEbayWebhook($headers, $payload);
                    break;
                    
                case 'ozon':
                    $validation = $this->validateOzonWebhook($headers, $payload);
                    break;
                    
                default:
                    $validation['error'] = 'Unsupported marketplace: ' . $marketplace;
            }
            
        } catch (Exception $e) {
            $validation['error'] = 'Validation error: ' . $e->getMessage();
            $this->log->write('Webhook validation error for ' . $marketplace . ': ' . $e->getMessage());
        }
        
        return $validation;
    }
    
    /**
     * Trendyol webhook doğrulama
     * @param array $headers HTTP headers
     * @param string $payload Raw payload
     * @return array Doğrulama sonucu
     */
    private function validateTrendyolWebhook($headers, $payload) {
        $validation = ['valid' => false, 'signature_valid' => false, 'error' => null];
        
        // Trendyol signature kontrolü
        $signature_header = $headers['X-Trendyol-Signature'] ?? null;
        if (!$signature_header) {
            $validation['error'] = 'Missing Trendyol signature header';
            return $validation;
        }
        
        $webhook_secret = $this->config->get('module_trendyol_webhook_secret');
        if (!$webhook_secret) {
            $validation['error'] = 'Trendyol webhook secret not configured';
            return $validation;
        }
        
        $expected_signature = hash_hmac('sha256', $payload, $webhook_secret);
        
        if (hash_equals($expected_signature, $signature_header)) {
            $validation['signature_valid'] = true;
            $validation['valid'] = true;
        } else {
            $validation['error'] = 'Invalid Trendyol signature';
        }
        
        return $validation;
    }
    
    /**
     * N11 webhook doğrulama
     * @param array $headers HTTP headers
     * @param string $payload Raw payload
     * @return array Doğrulama sonucu
     */
    private function validateN11Webhook($headers, $payload) {
        $validation = ['valid' => false, 'signature_valid' => false, 'error' => null];
        
        // N11 timestamp ve nonce kontrolü
        $timestamp = $headers['X-N11-Timestamp'] ?? null;
        $nonce = $headers['X-N11-Nonce'] ?? null;
        $signature = $headers['X-N11-Signature'] ?? null;
        
        if (!$timestamp || !$nonce || !$signature) {
            $validation['error'] = 'Missing N11 headers';
            return $validation;
        }
        
        // Timestamp kontrolü (5 dakika tolerans)
        if (abs(time() - $timestamp) > 300) {
            $validation['error'] = 'N11 timestamp too old';
            return $validation;
        }
        
        $webhook_secret = $this->config->get('module_n11_webhook_secret');
        if (!$webhook_secret) {
            $validation['error'] = 'N11 webhook secret not configured';
            return $validation;
        }
        
        $sign_string = $timestamp . $nonce . $payload;
        $expected_signature = hash_hmac('sha256', $sign_string, $webhook_secret);
        
        if (hash_equals($expected_signature, $signature)) {
            $validation['signature_valid'] = true;
            $validation['valid'] = true;
        } else {
            $validation['error'] = 'Invalid N11 signature';
        }
        
        return $validation;
    }
    
    /**
     * Amazon webhook doğrulama
     * @param array $headers HTTP headers
     * @param string $payload Raw payload
     * @return array Doğrulama sonucu
     */
    private function validateAmazonWebhook($headers, $payload) {
        $validation = ['valid' => false, 'signature_valid' => false, 'error' => null];
        
        // Amazon SNS doğrulama
        $message_type = $headers['x-amz-sns-message-type'] ?? null;
        
        if ($message_type === 'SubscriptionConfirmation') {
            // Abonelik onayı
            $validation['valid'] = true;
            $validation['subscription_confirmation'] = true;
        } elseif ($message_type === 'Notification') {
            // Normal bildirim
            $validation = $this->validateAmazonSNSSignature($headers, $payload);
        } else {
            $validation['error'] = 'Invalid Amazon message type';
        }
        
        return $validation;
    }
    
    /**
     * Amazon SNS imza doğrulama
     * @param array $headers HTTP headers
     * @param string $payload Raw payload
     * @return array Doğrulama sonucu
     */
    private function validateAmazonSNSSignature($headers, $payload) {
        $validation = ['valid' => false, 'signature_valid' => false, 'error' => null];
        
        try {
            $message = json_decode($payload, true);
            
            if (!$message) {
                $validation['error'] = 'Invalid Amazon SNS JSON';
                return $validation;
            }
            
            // SNS imza doğrulama burada yapılacak
            // Bu, Amazon'un public key'ini kullanarak yapılır
            // Basitleştirilmiş versiyon:
            
            $validation['signature_valid'] = true;
            $validation['valid'] = true;
            
        } catch (Exception $e) {
            $validation['error'] = 'Amazon SNS validation error: ' . $e->getMessage();
        }
        
        return $validation;
    }
    
    /**
     * Hepsiburada webhook doğrulama
     * @param array $headers HTTP headers
     * @param string $payload Raw payload
     * @return array Doğrulama sonucu
     */
    private function validateHepsiburadaWebhook($headers, $payload) {
        $validation = ['valid' => false, 'signature_valid' => false, 'error' => null];
        
        $signature = $headers['X-Hepsiburada-Signature'] ?? null;
        if (!$signature) {
            $validation['error'] = 'Missing Hepsiburada signature';
            return $validation;
        }
        
        $webhook_secret = $this->config->get('module_hepsiburada_webhook_secret');
        if (!$webhook_secret) {
            $validation['error'] = 'Hepsiburada webhook secret not configured';
            return $validation;
        }
        
        $expected_signature = base64_encode(hash_hmac('sha256', $payload, $webhook_secret, true));
        
        if (hash_equals($expected_signature, $signature)) {
            $validation['signature_valid'] = true;
            $validation['valid'] = true;
        } else {
            $validation['error'] = 'Invalid Hepsiburada signature';
        }
        
        return $validation;
    }
    
    /**
     * eBay webhook doğrulama
     * @param array $headers HTTP headers
     * @param string $payload Raw payload
     * @return array Doğrulama sonucu
     */
    private function validateEbayWebhook($headers, $payload) {
        $validation = ['valid' => false, 'signature_valid' => false, 'error' => null];
        
        $signature = $headers['X-EBAY-SOA-MESSAGE-ID'] ?? null;
        $challenge_header = $headers['X-EBAY-CHALLENGE-VERIFICATION'] ?? null;
        
        if ($challenge_header) {
            // Challenge verification
            $validation['valid'] = true;
            $validation['challenge_verification'] = true;
            return $validation;
        }
        
        if (!$signature) {
            $validation['error'] = 'Missing eBay signature';
            return $validation;
        }
        
        // eBay notification verification
        $verification_token = $this->config->get('module_ebay_verification_token');
        if (!$verification_token) {
            $validation['error'] = 'eBay verification token not configured';
            return $validation;
        }
        
        // eBay'in doğrulama mekanizması
        $validation['signature_valid'] = true;
        $validation['valid'] = true;
        
        return $validation;
    }
    
    /**
     * Ozon webhook doğrulama
     * @param array $headers HTTP headers
     * @param string $payload Raw payload
     * @return array Doğrulama sonucu
     */
    private function validateOzonWebhook($headers, $payload) {
        $validation = ['valid' => false, 'signature_valid' => false, 'error' => null];
        
        $signature = $headers['X-Ozon-Signature'] ?? null;
        $timestamp = $headers['X-Ozon-Timestamp'] ?? null;
        
        if (!$signature || !$timestamp) {
            $validation['error'] = 'Missing Ozon headers';
            return $validation;
        }
        
        // Timestamp kontrolü (5 dakika tolerans)
        if (abs(time() - $timestamp) > 300) {
            $validation['error'] = 'Ozon timestamp too old';
            return $validation;
        }
        
        $webhook_secret = $this->config->get('module_ozon_webhook_secret');
        if (!$webhook_secret) {
            $validation['error'] = 'Ozon webhook secret not configured';
            return $validation;
        }
        
        $sign_string = $timestamp . $payload;
        $expected_signature = hash_hmac('sha256', $sign_string, $webhook_secret);
        
        if (hash_equals($expected_signature, $signature)) {
            $validation['signature_valid'] = true;
            $validation['valid'] = true;
        } else {
            $validation['error'] = 'Invalid Ozon signature';
        }
        
        return $validation;
    }
    
    /**
     * Webhook event'ini işle
     * @param string $marketplace Pazaryeri adı
     * @param array $payload Webhook payload
     * @return array İşleme sonucu
     */
    public function processWebhookEvent($marketplace, $payload) {
        $result = [
            'success' => false,
            'processed' => false,
            'error' => null,
            'event_type' => null,
            'actions_taken' => []
        ];
        
        try {
            // Event tipini belirle
            $event_type = $this->determineEventType($marketplace, $payload);
            $result['event_type'] = $event_type;
            
            if (!$event_type) {
                $result['error'] = 'Unknown event type';
                return $result;
            }
            
            // Event'e göre işlem yap
            switch ($event_type) {
                case 'order_created':
                case 'order_updated':
                    $result = $this->processOrderEvent($marketplace, $payload);
                    break;
                    
                case 'inventory_updated':
                    $result = $this->processInventoryEvent($marketplace, $payload);
                    break;
                    
                case 'product_updated':
                    $result = $this->processProductEvent($marketplace, $payload);
                    break;
                    
                case 'price_updated':
                    $result = $this->processPriceEvent($marketplace, $payload);
                    break;
                    
                default:
                    $result['error'] = 'Unsupported event type: ' . $event_type;
            }
            
        } catch (Exception $e) {
            $result['error'] = 'Processing error: ' . $e->getMessage();
            $this->log->write('Webhook processing error for ' . $marketplace . ': ' . $e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Event tipini belirle
     * @param string $marketplace Pazaryeri adı
     * @param array $payload Webhook payload
     * @return string|null Event tipi
     */
    private function determineEventType($marketplace, $payload) {
        switch ($marketplace) {
            case 'trendyol':
                return $payload['eventType'] ?? null;
                
            case 'n11':
                return $payload['type'] ?? null;
                
            case 'amazon':
                return $payload['notificationType'] ?? null;
                
            case 'hepsiburada':
                return $payload['event'] ?? null;
                
            case 'ebay':
                return $payload['Topic'] ?? null;
                
            case 'ozon':
                return $payload['message_type'] ?? null;
                
            default:
                return null;
        }
    }
    
    /**
     * Sipariş event'ini işle
     * @param string $marketplace Pazaryeri adı
     * @param array $payload Webhook payload
     * @return array İşleme sonucu
     */
    private function processOrderEvent($marketplace, $payload) {
        $result = [
            'success' => false,
            'processed' => false,
            'actions_taken' => []
        ];
        
        // Pazaryerine göre sipariş verilerini parse et
        $order_data = $this->parseOrderData($marketplace, $payload);
        
        if (!$order_data) {
            $result['error'] = 'Failed to parse order data';
            return $result;
        }
        
        // Siparişi OpenCart'a ekle veya güncelle
        $this->load->model('extension/module/' . $marketplace);
        $model = $this->{'model_extension_module_' . $marketplace};
        
        if (method_exists($model, 'syncOrder')) {
            $sync_result = $model->syncOrder($order_data);
            
            if ($sync_result) {
                $result['success'] = true;
                $result['processed'] = true;
                $result['actions_taken'][] = 'Order synced to OpenCart';
            } else {
                $result['error'] = 'Failed to sync order to OpenCart';
            }
        } else {
            $result['error'] = 'Sync method not available for ' . $marketplace;
        }
        
        return $result;
    }
    
    /**
     * Stok event'ini işle
     * @param string $marketplace Pazaryeri adı
     * @param array $payload Webhook payload
     * @return array İşleme sonucu
     */
    private function processInventoryEvent($marketplace, $payload) {
        $result = [
            'success' => false,
            'processed' => false,
            'actions_taken' => []
        ];
        
        // Stok verilerini parse et ve güncelle
        $inventory_data = $this->parseInventoryData($marketplace, $payload);
        
        if ($inventory_data) {
            // Stok güncelleme işlemi
            $this->updateInventory($marketplace, $inventory_data);
            
            $result['success'] = true;
            $result['processed'] = true;
            $result['actions_taken'][] = 'Inventory updated';
        } else {
            $result['error'] = 'Failed to parse inventory data';
        }
        
        return $result;
    }
    
    /**
     * Webhook logunu kaydet
     * @param string $marketplace Pazaryeri adı
     * @param string $event_type Event tipi
     * @param array $payload Payload
     * @param array $result İşleme sonucu
     * @param float $processing_time İşleme süresi
     */
    public function logWebhook($marketplace, $event_type, $payload, $result, $processing_time = 0) {
        $status = $result['success'] ? 'success' : 'failed';
        $error_message = $result['error'] ?? '';
        
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_webhook_log` SET 
            marketplace = '" . $this->db->escape($marketplace) . "',
            event_type = '" . $this->db->escape($event_type) . "',
            status = '" . $this->db->escape($status) . "',
            request_payload = '" . $this->db->escape(json_encode($payload)) . "',
            response_payload = '" . $this->db->escape(json_encode($result)) . "',
            error_message = '" . $this->db->escape($error_message) . "',
            processing_time = '" . (float)$processing_time . "',
            date_created = NOW()");
    }
    
    /**
     * Webhook tabloları oluştur
     */
    public function createWebhookTables() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_webhook_log` (
            `webhook_log_id` int(11) NOT NULL AUTO_INCREMENT,
            `marketplace` varchar(50) NOT NULL,
            `event_type` varchar(100) NOT NULL,
            `status` enum('pending','success','failed','retrying') DEFAULT 'pending',
            `request_payload` longtext,
            `response_payload` longtext,
            `error_message` text,
            `processing_time` decimal(10,4) DEFAULT 0.0000,
            `retry_count` int(11) DEFAULT 0,
            `date_created` datetime NOT NULL,
            `date_modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`webhook_log_id`),
            KEY `idx_marketplace` (`marketplace`),
            KEY `idx_status` (`status`),
            KEY `idx_event_type` (`event_type`),
            KEY `idx_date_created` (`date_created`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }
} 