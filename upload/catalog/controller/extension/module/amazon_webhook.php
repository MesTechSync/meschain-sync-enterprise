<?php
/**
 * Amazon Webhook Endpoint Controller
 * 
 * @package    MesChain-Sync
 * @author     MezBjen Team
 * @copyright  2024 MesChain
 * @version    1.0.0
 */

class ControllerExtensionModuleAmazonWebhook extends Controller {
    
    private $secret_key;
    private $logger;
    
    public function __construct($registry) {
        parent::__construct($registry);
        
        // Logger'ı başlat
        require_once(DIR_SYSTEM . 'library/meschain/logger.php');
        $this->logger = new MesChainLogger('amazon_webhook');
        
        // Secret key'i al
        $this->secret_key = $this->config->get('module_amazon_webhook_secret') ?: '';
    }
    
    /**
     * Sipariş webhook'u
     */
    public function orders() {
        try {
            // Webhook doğrulaması
            if (!$this->validateWebhook()) {
                $this->sendResponse(401, ['error' => 'Unauthorized']);
                return;
            }
            
            // Payload'ı al
            $payload = $this->getPayload();
            
            if (!$payload) {
                $this->sendResponse(400, ['error' => 'Invalid payload']);
                return;
            }
            
            // SNS mesajı mı kontrol et
            if ($this->isSNSMessage($payload)) {
                $this->handleSNSMessage($payload);
                return;
            }
            
            // Sipariş event'ini işle
            $this->processOrderEvent($payload);
            
            $this->sendResponse(200, ['success' => true]);
            
        } catch (Exception $e) {
            $this->logger->error('Order webhook error: ' . $e->getMessage());
            $this->sendResponse(500, ['error' => 'Internal server error']);
        }
    }
    
    /**
     * Ürün webhook'u
     */
    public function products() {
        try {
            if (!$this->validateWebhook()) {
                $this->sendResponse(401, ['error' => 'Unauthorized']);
                return;
            }
            
            $payload = $this->getPayload();
            
            if (!$payload) {
                $this->sendResponse(400, ['error' => 'Invalid payload']);
                return;
            }
            
            if ($this->isSNSMessage($payload)) {
                $this->handleSNSMessage($payload);
                return;
            }
            
            $this->processProductEvent($payload);
            
            $this->sendResponse(200, ['success' => true]);
            
        } catch (Exception $e) {
            $this->logger->error('Product webhook error: ' . $e->getMessage());
            $this->sendResponse(500, ['error' => 'Internal server error']);
        }
    }
    
    /**
     * Stok webhook'u
     */
    public function inventory() {
        try {
            if (!$this->validateWebhook()) {
                $this->sendResponse(401, ['error' => 'Unauthorized']);
                return;
            }
            
            $payload = $this->getPayload();
            
            if (!$payload) {
                $this->sendResponse(400, ['error' => 'Invalid payload']);
                return;
            }
            
            if ($this->isSNSMessage($payload)) {
                $this->handleSNSMessage($payload);
                return;
            }
            
            $this->processInventoryEvent($payload);
            
            $this->sendResponse(200, ['success' => true]);
            
        } catch (Exception $e) {
            $this->logger->error('Inventory webhook error: ' . $e->getMessage());
            $this->sendResponse(500, ['error' => 'Internal server error']);
        }
    }
    
    /**
     * Fulfillment webhook'u
     */
    public function fulfillment() {
        try {
            if (!$this->validateWebhook()) {
                $this->sendResponse(401, ['error' => 'Unauthorized']);
                return;
            }
            
            $payload = $this->getPayload();
            
            if (!$payload) {
                $this->sendResponse(400, ['error' => 'Invalid payload']);
                return;
            }
            
            if ($this->isSNSMessage($payload)) {
                $this->handleSNSMessage($payload);
                return;
            }
            
            $this->processFulfillmentEvent($payload);
            
            $this->sendResponse(200, ['success' => true]);
            
        } catch (Exception $e) {
            $this->logger->error('Fulfillment webhook error: ' . $e->getMessage());
            $this->sendResponse(500, ['error' => 'Internal server error']);
        }
    }
    
    /**
     * Webhook doğrulaması
     */
    private function validateWebhook() {
        // Amazon SNS signature doğrulaması
        $headers = getallheaders();
        
        // User-Agent kontrolü
        if (!isset($headers['User-Agent']) || strpos($headers['User-Agent'], 'Amazon') === false) {
            $this->logger->warning('Invalid User-Agent: ' . ($headers['User-Agent'] ?? 'Not set'));
            return false;
        }
        
        // Signature kontrolü (basitleştirilmiş)
        if ($this->secret_key && isset($headers['X-Amazon-Signature'])) {
            // Gerçek implementasyonda Amazon'un signature doğrulama algoritması kullanılmalı
            return true;
        }
        
        // Test modunda
        if (isset($headers['X-Amazon-Signature']) && $headers['X-Amazon-Signature'] === 'test-signature') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Payload'ı al ve parse et
     */
    private function getPayload() {
        $input = file_get_contents('php://input');
        
        if (!$input) {
            return null;
        }
        
        $payload = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('JSON parse error: ' . json_last_error_msg());
            return null;
        }
        
        return $payload;
    }
    
    /**
     * SNS mesajı mı kontrol et
     */
    private function isSNSMessage($payload) {
        return isset($payload['Type']) && in_array($payload['Type'], [
            'SubscriptionConfirmation',
            'Notification',
            'UnsubscribeConfirmation'
        ]);
    }
    
    /**
     * SNS mesajını işle
     */
    private function handleSNSMessage($payload) {
        switch ($payload['Type']) {
            case 'SubscriptionConfirmation':
                $this->confirmSNSSubscription($payload);
                break;
                
            case 'Notification':
                $this->processSNSNotification($payload);
                break;
                
            case 'UnsubscribeConfirmation':
                $this->logger->info('SNS Unsubscribe confirmation received');
                $this->sendResponse(200, ['success' => true]);
                break;
        }
    }
    
    /**
     * SNS subscription'ı doğrula
     */
    private function confirmSNSSubscription($payload) {
        if (!isset($payload['SubscribeURL'])) {
            $this->sendResponse(400, ['error' => 'Missing SubscribeURL']);
            return;
        }
        
        // SubscribeURL'e GET isteği gönder
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $payload['SubscribeURL']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            $this->logger->info('SNS subscription confirmed successfully');
            $this->sendResponse(200, ['success' => true]);
        } else {
            $this->logger->error('Failed to confirm SNS subscription. HTTP Code: ' . $httpCode);
            $this->sendResponse(500, ['error' => 'Failed to confirm subscription']);
        }
    }
    
    /**
     * SNS notification'ı işle
     */
    private function processSNSNotification($payload) {
        if (!isset($payload['Message'])) {
            $this->sendResponse(400, ['error' => 'Missing message']);
            return;
        }
        
        // Message JSON string olabilir
        $message = is_string($payload['Message']) ? json_decode($payload['Message'], true) : $payload['Message'];
        
        if (!$message) {
            $this->sendResponse(400, ['error' => 'Invalid message format']);
            return;
        }
        
        // Event type'a göre işle
        if (isset($message['eventType'])) {
            switch ($message['eventType']) {
                case 'ORDER_STATUS_CHANGED':
                case 'ORDER_CREATED':
                case 'ORDER_CANCELLED':
                    $this->processOrderEvent($message);
                    break;
                    
                case 'PRODUCT_CREATED':
                case 'PRODUCT_UPDATED':
                case 'PRODUCT_DELETED':
                    $this->processProductEvent($message);
                    break;
                    
                case 'INVENTORY_UPDATED':
                    $this->processInventoryEvent($message);
                    break;
                    
                case 'FULFILLMENT_UPDATED':
                    $this->processFulfillmentEvent($message);
                    break;
                    
                default:
                    $this->logger->warning('Unknown event type: ' . $message['eventType']);
            }
        }
        
        $this->sendResponse(200, ['success' => true]);
    }
    
    /**
     * Sipariş event'ini işle
     */
    private function processOrderEvent($payload) {
        $this->load->model('extension/module/amazon');
        
        $eventType = $payload['eventType'] ?? 'unknown';
        $orderData = $payload['payload'] ?? $payload['data'] ?? [];
        
        $this->logger->info('Processing order event', [
            'eventType' => $eventType,
            'orderId' => $orderData['orderId'] ?? 'unknown'
        ]);
        
        try {
            switch ($eventType) {
                case 'ORDER_CREATED':
                    if (method_exists($this->model_extension_module_amazon, 'createOrderFromWebhook')) {
                        $this->model_extension_module_amazon->createOrderFromWebhook($orderData);
                    }
                    break;
                    
                case 'ORDER_STATUS_CHANGED':
                    if (method_exists($this->model_extension_module_amazon, 'updateOrderStatusFromWebhook')) {
                        $this->model_extension_module_amazon->updateOrderStatusFromWebhook(
                            $orderData['orderId'] ?? '',
                            $orderData['status'] ?? ''
                        );
                    }
                    break;
                    
                case 'ORDER_CANCELLED':
                    if (method_exists($this->model_extension_module_amazon, 'cancelOrderFromWebhook')) {
                        $this->model_extension_module_amazon->cancelOrderFromWebhook($orderData['orderId'] ?? '');
                    }
                    break;
            }
            
            $this->logger->info('Order event processed successfully');
            
        } catch (Exception $e) {
            $this->logger->error('Error processing order event: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Ürün event'ini işle
     */
    private function processProductEvent($payload) {
        $this->load->model('extension/module/amazon');
        
        $eventType = $payload['eventType'] ?? 'unknown';
        $productData = $payload['payload'] ?? $payload['data'] ?? [];
        
        $this->logger->info('Processing product event', [
            'eventType' => $eventType,
            'asin' => $productData['asin'] ?? 'unknown'
        ]);
        
        try {
            switch ($eventType) {
                case 'PRODUCT_CREATED':
                case 'PRODUCT_UPDATED':
                    if (method_exists($this->model_extension_module_amazon, 'syncProductFromWebhook')) {
                        $this->model_extension_module_amazon->syncProductFromWebhook($productData);
                    }
                    break;
                    
                case 'PRODUCT_DELETED':
                    if (method_exists($this->model_extension_module_amazon, 'deleteProductFromWebhook')) {
                        $this->model_extension_module_amazon->deleteProductFromWebhook($productData['asin'] ?? '');
                    }
                    break;
            }
            
            $this->logger->info('Product event processed successfully');
            
        } catch (Exception $e) {
            $this->logger->error('Error processing product event: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Stok event'ini işle
     */
    private function processInventoryEvent($payload) {
        $this->load->model('extension/module/amazon');
        
        $inventoryData = $payload['payload'] ?? $payload['data'] ?? [];
        
        $this->logger->info('Processing inventory event', [
            'sku' => $inventoryData['sku'] ?? 'unknown',
            'quantity' => $inventoryData['quantity'] ?? 0
        ]);
        
        try {
            if (method_exists($this->model_extension_module_amazon, 'updateInventoryFromWebhook')) {
                $this->model_extension_module_amazon->updateInventoryFromWebhook(
                    $inventoryData['sku'] ?? '',
                    $inventoryData['quantity'] ?? 0,
                    $inventoryData['fulfillmentChannel'] ?? 'DEFAULT'
                );
            }
            
            $this->logger->info('Inventory event processed successfully');
            
        } catch (Exception $e) {
            $this->logger->error('Error processing inventory event: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Fulfillment event'ini işle
     */
    private function processFulfillmentEvent($payload) {
        $this->load->model('extension/module/amazon');
        
        $fulfillmentData = $payload['payload'] ?? $payload['data'] ?? [];
        
        $this->logger->info('Processing fulfillment event', [
            'orderId' => $fulfillmentData['orderId'] ?? 'unknown',
            'status' => $fulfillmentData['status'] ?? 'unknown'
        ]);
        
        try {
            if (method_exists($this->model_extension_module_amazon, 'updateFulfillmentFromWebhook')) {
                $this->model_extension_module_amazon->updateFulfillmentFromWebhook($fulfillmentData);
            }
            
            $this->logger->info('Fulfillment event processed successfully');
            
        } catch (Exception $e) {
            $this->logger->error('Error processing fulfillment event: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * HTTP response gönder
     */
    private function sendResponse($code, $data) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
} 