<?php
/**
 * Amazon Webhook Controller
 * MesChain-Sync v4.1 - OpenCart 3.0.4.0 Integration
 * Amazon Marketplace Webhook Integration for Real-time Order & Inventory Management
 * 
 * @author MesChain Development Team
 * @version 4.1.0
 * @copyright 2024 MesChain Technologies
 * @supports Amazon SP-API Notifications, SQS Integration, EventBridge
 */

class ControllerExtensionModuleAmazonWebhooks extends Controller {
    
    private $log;
    private $logFile = 'amazon_webhooks.log';
    private $encryption;
    
    public function __construct($registry) {
        parent::__construct($registry);
        $this->log = new Log($this->logFile);
        
        // Şifreleme helper'ı yükle
        require_once(DIR_SYSTEM . 'library/meschain/encryption.php');
        $this->encryption = new MeschainEncryption();
    }
    
    /**
     * Ana webhook handler - Amazon SQS/SNS webhook'larını karşılar
     */
    public function index() {
        $this->log->write('[INFO] Amazon webhook endpoint called');
        
        try {
            // Raw POST data al
            $raw_data = file_get_contents('php://input');
            $headers = getallheaders();
            
            // Content-Type kontrolü
            $content_type = $headers['Content-Type'] ?? '';
            
            if (strpos($content_type, 'application/json') === false) {
                $this->log->write('[ERROR] Invalid content type for Amazon webhook: ' . $content_type);
                http_response_code(400);
                echo json_encode(['error' => 'Invalid content type']);
                return;
            }
            
            // JSON decode
            $webhook_data = json_decode($raw_data, true);
            
            if (!$webhook_data) {
                $this->log->write('[ERROR] Invalid JSON in Amazon webhook');
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON']);
                return;
            }
            
            $this->log->write('[INFO] Amazon webhook data received: ' . json_encode($webhook_data));
            
            // SNS subscription confirmation
            if (isset($webhook_data['Type']) && $webhook_data['Type'] === 'SubscriptionConfirmation') {
                $this->handleSubscriptionConfirmation($webhook_data);
                return;
            }
            
            // SNS notification işleme
            if (isset($webhook_data['Type']) && $webhook_data['Type'] === 'Notification') {
                $this->handleSnsNotification($webhook_data);
                return;
            }
            
            // SQS message işleme
            if (isset($webhook_data['Records'])) {
                $this->handleSqsMessages($webhook_data['Records']);
                return;
            }
            
            // Direct SP-API notification
            if (isset($webhook_data['notificationType'])) {
                $this->handleSpApiNotification($webhook_data);
                return;
            }
            
            $this->log->write('[WARNING] Unknown Amazon webhook format');
            http_response_code(400);
            echo json_encode(['error' => 'Unknown webhook format']);
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Amazon webhook processing failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
    
    /**
     * SNS subscription confirmation işlemi
     */
    private function handleSubscriptionConfirmation($data) {
        $this->log->write('[INFO] Amazon SNS subscription confirmation received');
        
        $subscribe_url = $data['SubscribeURL'] ?? null;
        
        if ($subscribe_url) {
            // Subscription'ı onayla
            $response = file_get_contents($subscribe_url);
            
            if ($response !== false) {
                $this->log->write('[INFO] Amazon SNS subscription confirmed successfully');
                echo json_encode(['status' => 'subscription_confirmed']);
            } else {
                $this->log->write('[ERROR] Failed to confirm Amazon SNS subscription');
                http_response_code(500);
                echo json_encode(['error' => 'Subscription confirmation failed']);
            }
        } else {
            $this->log->write('[ERROR] Amazon SNS subscription URL missing');
            http_response_code(400);
            echo json_encode(['error' => 'SubscribeURL missing']);
        }
    }
    
    /**
     * SNS notification işleme
     */
    private function handleSnsNotification($data) {
        $this->log->write('[INFO] Amazon SNS notification received');
        
        $message = json_decode($data['Message'] ?? '{}', true);
        
        if (!$message) {
            $this->log->write('[ERROR] Invalid SNS message format');
            return;
        }
        
        $this->processAmazonNotification($message);
    }
    
    /**
     * SQS messages işleme
     */
    private function handleSqsMessages($records) {
        $this->log->write('[INFO] Amazon SQS messages received: ' . count($records));
        
        foreach ($records as $record) {
            if (isset($record['body'])) {
                $message = json_decode($record['body'], true);
                
                if ($message) {
                    $this->processAmazonNotification($message);
                } else {
                    $this->log->write('[ERROR] Invalid SQS message body');
                }
            }
        }
    }
    
    /**
     * SP-API notification işleme
     */
    private function handleSpApiNotification($data) {
        $this->log->write('[INFO] Amazon SP-API notification received');
        $this->processAmazonNotification($data);
    }
    
    /**
     * Amazon notification'larını işle
     */
    private function processAmazonNotification($notification) {
        $notification_type = $notification['notificationType'] ?? $notification['NotificationType'] ?? null;
        
        if (!$notification_type) {
            $this->log->write('[ERROR] Amazon notification type missing');
            return;
        }
        
        switch ($notification_type) {
            case 'ORDER_CHANGE':
                $this->handleOrderChange($notification);
                break;
                
            case 'ORDER_STATUS_CHANGE':
                $this->handleOrderStatusChange($notification);
                break;
                
            case 'INVENTORY_TRACKING':
                $this->handleInventoryTracking($notification);
                break;
                
            case 'FBA_INVENTORY_AVAILABILITY':
                $this->handleFbaInventoryAvailability($notification);
                break;
                
            case 'LISTING_OFFER_CHANGE':
                $this->handleListingOfferChange($notification);
                break;
                
            case 'PRICING_HEALTH':
                $this->handlePricingHealth($notification);
                break;
                
            case 'ACCOUNT_STATUS_CHANGE':
                $this->handleAccountStatusChange($notification);
                break;
                
            case 'BRANDED_ITEM_CONTENT_CHANGE':
                $this->handleBrandedItemContentChange($notification);
                break;
                
            case 'ITEM_PRODUCT_TYPE_CHANGE':
                $this->handleItemProductTypeChange($notification);
                break;
                
            case 'MFN_ORDER_STATUS_CHANGE':
                $this->handleMfnOrderStatusChange($notification);
                break;
                
            case 'B2B_ORDER_CHANGE':
                $this->handleB2bOrderChange($notification);
                break;
                
            case 'FEED_PROCESSING_FINISHED':
                $this->handleFeedProcessingFinished($notification);
                break;
                
            default:
                $this->log->write('[WARNING] Unknown Amazon notification type: ' . $notification_type);
                break;
        }
        
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Notification processed successfully']);
    }
    
    /**
     * Sipariş değişiklik işlemi
     */
    private function handleOrderChange($notification) {
        $this->log->write('[INFO] Amazon order change notification received');
        
        $this->load->model('extension/module/amazon');
        
        $payload = $notification['payload'] ?? [];
        $order_change_info = $payload['orderChangeInfo'] ?? [];
        
        $amazon_order_id = $order_change_info['amazonOrderId'] ?? null;
        $change_type = $order_change_info['changeType'] ?? null;
        
        if (!$amazon_order_id) {
            $this->log->write('[ERROR] Amazon order ID missing in order change notification');
            return;
        }
        
        // OpenCart sipariş ID'sini bul
        $order_id = $this->model_extension_module_amazon->getOrderIdByAmazonOrderId($amazon_order_id);
        
        if ($order_id) {
            switch ($change_type) {
                case 'OrderItemQuantityChanged':
                    $this->handleOrderItemQuantityChange($order_id, $order_change_info);
                    break;
                    
                case 'OrderItemPriceChanged':
                    $this->handleOrderItemPriceChange($order_id, $order_change_info);
                    break;
                    
                case 'OrderCanceled':
                    $this->handleOrderCanceled($order_id, $order_change_info);
                    break;
                    
                default:
                    $this->log->write('[WARNING] Unknown Amazon order change type: ' . $change_type);
                    break;
            }
        } else {
            // Yeni sipariş olabilir, senkronize et
            $this->model_extension_module_amazon->syncOrderFromAmazon($amazon_order_id);
        }
        
        $this->log->write('[INFO] Amazon order change processed - Order ID: ' . $amazon_order_id . ', Change Type: ' . $change_type);
    }
    
    /**
     * Sipariş durum değişiklik işlemi
     */
    private function handleOrderStatusChange($notification) {
        $this->log->write('[INFO] Amazon order status change notification received');
        
        $this->load->model('extension/module/amazon');
        
        $payload = $notification['payload'] ?? [];
        $order_status_info = $payload['orderStatusInfo'] ?? [];
        
        $amazon_order_id = $order_status_info['amazonOrderId'] ?? null;
        $order_status = $order_status_info['orderStatus'] ?? null;
        
        if ($amazon_order_id && $order_status) {
            $order_id = $this->model_extension_module_amazon->getOrderIdByAmazonOrderId($amazon_order_id);
            
            if ($order_id) {
                // OpenCart order status'u güncelle
                $opencart_status = $this->mapAmazonStatusToOpenCart($order_status);
                $this->model_extension_module_amazon->updateOrderStatus($order_id, $opencart_status);
                
                $this->log->write('[INFO] Amazon order status updated - Order ID: ' . $amazon_order_id . ', Status: ' . $order_status);
            }
        }
    }
    
    /**
     * Envanter takip işlemi
     */
    private function handleInventoryTracking($notification) {
        $this->log->write('[INFO] Amazon inventory tracking notification received');
        
        $this->load->model('extension/module/amazon');
        $this->load->model('catalog/product');
        
        $payload = $notification['payload'] ?? [];
        $inventory_info = $payload['inventoryInfo'] ?? [];
        
        foreach ($inventory_info as $inventory_item) {
            $sku = $inventory_item['sku'] ?? null;
            $quantity = $inventory_item['quantity'] ?? 0;
            
            if ($sku) {
                // SKU'ya göre ürün bul
                $product_id = $this->model_extension_module_amazon->getProductIdBySku($sku);
                
                if ($product_id) {
                    // Stok güncelle
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = '" . (int)$quantity . "' WHERE product_id = '" . (int)$product_id . "'");
                    
                    $this->log->write('[INFO] Amazon inventory updated - SKU: ' . $sku . ', Quantity: ' . $quantity);
                }
            }
        }
    }
    
    /**
     * FBA envanter durumu işlemi
     */
    private function handleFbaInventoryAvailability($notification) {
        $this->log->write('[INFO] Amazon FBA inventory availability notification received');
        
        $this->load->model('extension/module/amazon');
        
        $payload = $notification['payload'] ?? [];
        $fba_inventory = $payload['fbaInventory'] ?? [];
        
        foreach ($fba_inventory as $inventory_item) {
            $sku = $inventory_item['sku'] ?? null;
            $fulfillable_quantity = $inventory_item['fulfillableQuantity'] ?? 0;
            $inbound_quantity = $inventory_item['inboundQuantity'] ?? 0;
            
            if ($sku) {
                $this->model_extension_module_amazon->updateFbaInventory($sku, [
                    'fulfillable_quantity' => $fulfillable_quantity,
                    'inbound_quantity' => $inbound_quantity,
                    'last_updated' => date('Y-m-d H:i:s')
                ]);
                
                $this->log->write('[INFO] Amazon FBA inventory updated - SKU: ' . $sku . ', Fulfillable: ' . $fulfillable_quantity);
            }
        }
    }
    
    /**
     * Listing/Offer değişiklik işlemi
     */
    private function handleListingOfferChange($notification) {
        $this->log->write('[INFO] Amazon listing offer change notification received');
        
        $this->load->model('extension/module/amazon');
        
        $payload = $notification['payload'] ?? [];
        $offer_info = $payload['offerInfo'] ?? [];
        
        $sku = $offer_info['sku'] ?? null;
        $selling_price = $offer_info['sellingPrice'] ?? null;
        $quantity = $offer_info['quantity'] ?? null;
        
        if ($sku) {
            $product_id = $this->model_extension_module_amazon->getProductIdBySku($sku);
            
            if ($product_id) {
                $update_data = [];
                
                if ($selling_price !== null) {
                    $update_data['price'] = $selling_price;
                }
                
                if ($quantity !== null) {
                    $update_data['quantity'] = $quantity;
                }
                
                if (!empty($update_data)) {
                    $this->model_extension_module_amazon->updateProductFromAmazon($product_id, $update_data);
                    
                    $this->log->write('[INFO] Amazon listing offer updated - SKU: ' . $sku);
                }
            }
        }
    }
    
    /**
     * Fiyat sağlık durumu işlemi
     */
    private function handlePricingHealth($notification) {
        $this->log->write('[INFO] Amazon pricing health notification received');
        
        $this->load->model('extension/module/amazon');
        
        $payload = $notification['payload'] ?? [];
        $pricing_info = $payload['pricingInfo'] ?? [];
        
        foreach ($pricing_info as $pricing_item) {
            $sku = $pricing_item['sku'] ?? null;
            $health_status = $pricing_item['healthStatus'] ?? null;
            $recommended_price = $pricing_item['recommendedPrice'] ?? null;
            
            if ($sku && $health_status) {
                $this->model_extension_module_amazon->updatePricingHealth($sku, [
                    'health_status' => $health_status,
                    'recommended_price' => $recommended_price,
                    'last_updated' => date('Y-m-d H:i:s')
                ]);
                
                // Kritik durumlarda admin bilgilendir
                if ($health_status === 'POOR' || $health_status === 'CRITICAL') {
                    $this->sendAdminNotification('Amazon Pricing Health Alert', 'SKU: ' . $sku . ' has ' . $health_status . ' pricing health status.');
                }
                
                $this->log->write('[INFO] Amazon pricing health updated - SKU: ' . $sku . ', Status: ' . $health_status);
            }
        }
    }
    
    /**
     * Hesap durum değişiklik işlemi
     */
    private function handleAccountStatusChange($notification) {
        $this->log->write('[INFO] Amazon account status change notification received');
        
        $payload = $notification['payload'] ?? [];
        $account_status = $payload['accountStatus'] ?? null;
        $marketplace_id = $payload['marketplaceId'] ?? null;
        
        if ($account_status && $marketplace_id) {
            // Hesap durumunu kaydet
            $this->db->query("INSERT INTO " . DB_PREFIX . "amazon_account_status (marketplace_id, status, status_date) VALUES ('" . $this->db->escape($marketplace_id) . "', '" . $this->db->escape($account_status) . "', NOW()) ON DUPLICATE KEY UPDATE status = VALUES(status), status_date = VALUES(status_date)");
            
            // Kritik durumda admin bilgilendir
            if (in_array($account_status, ['SUSPENDED', 'DEACTIVATED', 'UNDER_REVIEW'])) {
                $this->sendAdminNotification('Amazon Account Status Alert', 'Account status changed to: ' . $account_status . ' for marketplace: ' . $marketplace_id);
            }
            
            $this->log->write('[INFO] Amazon account status updated - Marketplace: ' . $marketplace_id . ', Status: ' . $account_status);
        }
    }
    
    /**
     * Feed işleme tamamlanma
     */
    private function handleFeedProcessingFinished($notification) {
        $this->log->write('[INFO] Amazon feed processing finished notification received');
        
        $this->load->model('extension/module/amazon');
        
        $payload = $notification['payload'] ?? [];
        $feed_id = $payload['feedId'] ?? null;
        $processing_status = $payload['processingStatus'] ?? null;
        
        if ($feed_id && $processing_status) {
            $this->model_extension_module_amazon->updateFeedStatus($feed_id, $processing_status);
            
            // Feed sonuç raporunu al
            if ($processing_status === 'DONE') {
                $this->model_extension_module_amazon->processFeedResult($feed_id);
            }
            
            $this->log->write('[INFO] Amazon feed processing finished - Feed ID: ' . $feed_id . ', Status: ' . $processing_status);
        }
    }
    
    /**
     * Amazon durum kodlarını OpenCart'a dönüştür
     */
    private function mapAmazonStatusToOpenCart($amazon_status) {
        $status_map = [
            'Pending' => 1,
            'Unshipped' => 2,
            'PartiallyShipped' => 3,
            'Shipped' => 4,
            'Canceled' => 7,
            'Unfulfillable' => 8
        ];
        
        return $status_map[$amazon_status] ?? 1;
    }
    
    /**
     * Admin bildirim gönder
     */
    private function sendAdminNotification($subject, $message) {
        $admin_email = $this->config->get('config_email');
        if ($admin_email) {
            $mail = new Mail();
            $mail->protocol = $this->config->get('config_mail_protocol');
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
            
            $mail->setTo($admin_email);
            $mail->setFrom($this->config->get('config_email'));
            $mail->setSender($this->config->get('config_name'));
            $mail->setSubject('MesChain-Sync: ' . $subject);
            $mail->setHtml($message);
            $mail->send();
        }
    }
    
    /**
     * Webhook ayarları sayfası
     */
    public function settings() {
        $this->load->language('extension/module/amazon');
        $this->document->setTitle('Amazon Webhook Settings');
        $this->load->model('setting/setting');
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            $this->model_setting_setting->editSetting('module_amazon_webhook', $this->request->post);
            $this->session->data['success'] = 'Amazon webhook settings saved successfully!';
            $this->response->redirect($this->url->link('extension/module/amazon_webhooks/settings', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => 'Home',
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        $data['action'] = $this->url->link('extension/module/amazon_webhooks/settings', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/module/amazon', 'user_token=' . $this->session->data['user_token'], true);
        
        // Webhook URL
        $data['webhook_url'] = HTTPS_CATALOG . 'index.php?route=extension/module/amazon_webhooks';
        
        // Mevcut ayarları yükle
        $data['module_amazon_webhook_sns_topic_arn'] = $this->config->get('module_amazon_webhook_sns_topic_arn');
        $data['module_amazon_webhook_sqs_queue_url'] = $this->config->get('module_amazon_webhook_sqs_queue_url');
        $data['module_amazon_webhook_enabled'] = $this->config->get('module_amazon_webhook_enabled');
        
        $this->response->setOutput($this->load->view('extension/module/amazon_webhooks_settings', $data));
    }
    
    /**
     * Webhook test
     */
    public function test() {
        $this->log->write('[INFO] Amazon webhook test initiated');
        
        // Test webhook verisi
        $test_data = [
            'notificationType' => 'ORDER_CHANGE',
            'payload' => [
                'orderChangeInfo' => [
                    'amazonOrderId' => 'TEST-123-456-789',
                    'changeType' => 'OrderItemQuantityChanged',
                    'orderItems' => [
                        [
                            'sku' => 'TEST-SKU-001',
                            'quantityOrdered' => 2,
                            'quantityShipped' => 0
                        ]
                    ]
                ]
            ],
            'eventTime' => date('c')
        ];
        
        try {
            $this->handleOrderChange($test_data);
            
            $this->log->write('[INFO] Amazon webhook test completed successfully');
            echo json_encode(['status' => 'success', 'message' => 'Webhook test completed successfully']);
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Amazon webhook test failed: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Webhook test failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Validation kontrolü
     */
    private function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/amazon_webhooks')) {
            $this->error['warning'] = 'You do not have permission to modify Amazon webhook settings!';
        }
        
        return !$this->error;
    }
} 