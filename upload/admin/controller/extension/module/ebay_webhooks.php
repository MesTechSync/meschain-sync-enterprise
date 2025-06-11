<?php
/**
 * eBay Webhook Controller
 * MesChain-Sync v4.1 - OpenCart 3.0.4.0 Integration
 * eBay Marketplace Webhook Integration for Real-time Order & Inventory Management
 * 
 * @author MesChain Development Team
 * @version 4.1.0
 * @copyright 2024 MesChain Technologies
 * @supports eBay Notifications: OrderLineItemId, Item.*, Feedback.*, Dispute.*
 */

class ControllerExtensionModuleEbayWebhooks extends Controller {
    
    private $log;
    private $logFile = 'ebay_webhooks.log';
    private $encryption;
    
    public function __construct($registry) {
        parent::__construct($registry);
        $this->log = new Log($this->logFile);
        
        // Şifreleme helper'ı yükle
        require_once(DIR_SYSTEM . 'library/meschain/encryption.php');
        $this->encryption = new MeschainEncryption();
    }
    
    /**
     * Ana webhook handler - eBay'den gelen tüm webhook'ları karşılar
     */
    public function index() {
        $this->log->write('[INFO] eBay webhook endpoint called');
        
        try {
            // Raw POST data al
            $raw_data = file_get_contents('php://input');
            $headers = getallheaders();
            
            // Webhook doğrulama
            if (!$this->validateWebhook($raw_data, $headers)) {
                $this->log->write('[ERROR] eBay webhook validation failed');
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                return;
            }
            
            // JSON decode
            $webhook_data = json_decode($raw_data, true);
            
            if (!$webhook_data) {
                $this->log->write('[ERROR] Invalid JSON in eBay webhook');
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON']);
                return;
            }
            
            $this->log->write('[INFO] eBay webhook data received: ' . json_encode($webhook_data));
            
            // Notification tipine göre işlem
            $notification_type = $webhook_data['metadata']['topic'] ?? null;
            
            switch ($notification_type) {
                case 'MARKETPLACE_ACCOUNT_DELETION':
                    $this->handleAccountDeletion($webhook_data);
                    break;
                    
                case 'ITEM_SOLD':
                    $this->handleItemSold($webhook_data);
                    break;
                    
                case 'ITEM_ENDED':
                    $this->handleItemEnded($webhook_data);
                    break;
                    
                case 'ITEM_UPDATED':
                    $this->handleItemUpdated($webhook_data);
                    break;
                    
                case 'FIXED_PRICE_TRANSACTION':
                    $this->handleFixedPriceTransaction($webhook_data);
                    break;
                    
                case 'AUCTION_CHECKOUT_COMPLETE':
                    $this->handleAuctionCheckoutComplete($webhook_data);
                    break;
                    
                case 'PAYMENT_DISPUTE_CREATED':
                    $this->handlePaymentDispute($webhook_data);
                    break;
                    
                case 'FEEDBACK_RECEIVED':
                    $this->handleFeedbackReceived($webhook_data);
                    break;
                    
                case 'BUYER_CANCEL_REQUESTED':
                    $this->handleBuyerCancelRequest($webhook_data);
                    break;
                    
                case 'RETURN_CREATED':
                    $this->handleReturnCreated($webhook_data);
                    break;
                    
                default:
                    $this->log->write('[WARNING] Unknown eBay notification type: ' . $notification_type);
                    break;
            }
            
            // Başarılı yanıt
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Webhook processed successfully']);
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] eBay webhook processing failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
    
    /**
     * eBay webhook'u doğrula
     */
    private function validateWebhook($raw_data, $headers) {
        $verification_token = $this->config->get('module_ebay_webhook_verification_token');
        $endpoint_url = $this->config->get('module_ebay_webhook_endpoint_url');
        
        if (!$verification_token) {
            $this->log->write('[ERROR] eBay webhook verification token not configured');
            return false;
        }
        
        // eBay challenge/response doğrulama
        if (isset($_GET['challenge_code'])) {
            $challenge_code = $_GET['challenge_code'];
            $verification_response = hash_hmac('sha256', $challenge_code, $verification_token);
            
            $this->log->write('[INFO] eBay webhook challenge received: ' . $challenge_code);
            echo $verification_response;
            return false; // Challenge işlemini bitir
        }
        
        // Signature doğrulama
        $signature = $headers['X-EBAY-SIGNATURE'] ?? '';
        if (!$signature) {
            $this->log->write('[ERROR] eBay webhook signature missing');
            return false;
        }
        
        $calculated_signature = hash_hmac('sha256', $raw_data, $verification_token);
        
        if (!hash_equals($calculated_signature, $signature)) {
            $this->log->write('[ERROR] eBay webhook signature mismatch');
            return false;
        }
        
        return true;
    }
    
    /**
     * Hesap silme işlemi
     */
    private function handleAccountDeletion($data) {
        $this->log->write('[INFO] eBay account deletion notification received');
        
        $this->load->model('extension/module/ebay');
        
        // Kullanıcı verilerini temizle
        $user_id = $data['notificationPayload']['userId'] ?? null;
        if ($user_id) {
            $this->model_extension_module_ebay->deleteUserData($user_id);
            $this->log->write('[INFO] eBay user data deleted for user: ' . $user_id);
        }
    }
    
    /**
     * Ürün satış işlemi
     */
    private function handleItemSold($data) {
        $this->log->write('[INFO] eBay item sold notification received');
        
        $this->load->model('extension/module/ebay');
        $this->load->model('catalog/product');
        
        $item_id = $data['notificationPayload']['itemId'] ?? null;
        $quantity_sold = $data['notificationPayload']['quantitySold'] ?? 1;
        
        if (!$item_id) {
            $this->log->write('[ERROR] eBay item ID missing in sold notification');
            return;
        }
        
        // OpenCart ürün ID'sini bul
        $product_id = $this->model_extension_module_ebay->getProductIdByItemId($item_id);
        
        if ($product_id) {
            // Stok güncelle
            $current_stock = $this->model_catalog_product->getProduct($product_id)['quantity'];
            $new_stock = max(0, $current_stock - $quantity_sold);
            
            $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = '" . (int)$new_stock . "' WHERE product_id = '" . (int)$product_id . "'");
            
            // Satış kaydını güncelle
            $this->model_extension_module_ebay->updateItemSoldStatus($item_id, $quantity_sold);
            
            $this->log->write('[INFO] eBay item sold - Product ID: ' . $product_id . ', Quantity: ' . $quantity_sold . ', New Stock: ' . $new_stock);
        }
    }
    
    /**
     * Ürün listing bitiş işlemi
     */
    private function handleItemEnded($data) {
        $this->log->write('[INFO] eBay item ended notification received');
        
        $this->load->model('extension/module/ebay');
        
        $item_id = $data['notificationPayload']['itemId'] ?? null;
        $end_reason = $data['notificationPayload']['endReason'] ?? 'Unknown';
        
        if ($item_id) {
            $this->model_extension_module_ebay->updateListingStatus($item_id, 'ended', $end_reason);
            
            // Otomatik yeniden listeleme kontrolü
            if ($this->config->get('module_ebay_auto_relist') && $end_reason !== 'Sold') {
                $this->scheduleRelist($item_id);
            }
            
            $this->log->write('[INFO] eBay item ended - Item ID: ' . $item_id . ', Reason: ' . $end_reason);
        }
    }
    
    /**
     * Ürün güncelleme işlemi
     */
    private function handleItemUpdated($data) {
        $this->log->write('[INFO] eBay item updated notification received');
        
        $this->load->model('extension/module/ebay');
        
        $item_id = $data['notificationPayload']['itemId'] ?? null;
        $update_type = $data['notificationPayload']['updateType'] ?? 'Unknown';
        
        if ($item_id) {
            $this->model_extension_module_ebay->syncItemFromEbay($item_id);
            $this->log->write('[INFO] eBay item updated - Item ID: ' . $item_id . ', Type: ' . $update_type);
        }
    }
    
    /**
     * Sabit fiyat işlem tamamlama
     */
    private function handleFixedPriceTransaction($data) {
        $this->log->write('[INFO] eBay fixed price transaction notification received');
        
        $this->load->model('extension/module/ebay');
        
        $transaction_id = $data['notificationPayload']['transactionId'] ?? null;
        $item_id = $data['notificationPayload']['itemId'] ?? null;
        $buyer_user_id = $data['notificationPayload']['buyerUserId'] ?? null;
        $amount_paid = $data['notificationPayload']['amountPaid'] ?? 0;
        
        if ($transaction_id && $item_id) {
            // Sipariş oluştur
            $order_data = [
                'transaction_id' => $transaction_id,
                'item_id' => $item_id,
                'buyer_user_id' => $buyer_user_id,
                'amount_paid' => $amount_paid,
                'status' => 'paid'
            ];
            
            $this->model_extension_module_ebay->createOrderFromTransaction($order_data);
            
            $this->log->write('[INFO] eBay fixed price transaction created - Transaction ID: ' . $transaction_id);
        }
    }
    
    /**
     * Açık artırma ödeme tamamlama
     */
    private function handleAuctionCheckoutComplete($data) {
        $this->log->write('[INFO] eBay auction checkout complete notification received');
        
        $this->load->model('extension/module/ebay');
        
        $item_id = $data['notificationPayload']['itemId'] ?? null;
        $winning_bid = $data['notificationPayload']['winningBid'] ?? 0;
        $winner_user_id = $data['notificationPayload']['winnerUserId'] ?? null;
        
        if ($item_id && $winner_user_id) {
            $order_data = [
                'item_id' => $item_id,
                'buyer_user_id' => $winner_user_id,
                'amount_paid' => $winning_bid,
                'status' => 'auction_won',
                'order_type' => 'auction'
            ];
            
            $this->model_extension_module_ebay->createOrderFromAuction($order_data);
            
            $this->log->write('[INFO] eBay auction checkout complete - Item ID: ' . $item_id . ', Winning Bid: ' . $winning_bid);
        }
    }
    
    /**
     * Ödeme itirazı işlemi
     */
    private function handlePaymentDispute($data) {
        $this->log->write('[INFO] eBay payment dispute notification received');
        
        $this->load->model('extension/module/ebay');
        
        $dispute_id = $data['notificationPayload']['disputeId'] ?? null;
        $item_id = $data['notificationPayload']['itemId'] ?? null;
        $dispute_reason = $data['notificationPayload']['disputeReason'] ?? 'Unknown';
        
        if ($dispute_id && $item_id) {
            $this->model_extension_module_ebay->createDispute([
                'dispute_id' => $dispute_id,
                'item_id' => $item_id,
                'reason' => $dispute_reason,
                'status' => 'open'
            ]);
            
            // Admin bildirim gönder
            $this->sendAdminNotification('eBay Payment Dispute', 'Dispute ID: ' . $dispute_id . ', Item ID: ' . $item_id);
            
            $this->log->write('[INFO] eBay payment dispute created - Dispute ID: ' . $dispute_id);
        }
    }
    
    /**
     * Feedback alma işlemi
     */
    private function handleFeedbackReceived($data) {
        $this->log->write('[INFO] eBay feedback received notification');
        
        $this->load->model('extension/module/ebay');
        
        $feedback_id = $data['notificationPayload']['feedbackId'] ?? null;
        $item_id = $data['notificationPayload']['itemId'] ?? null;
        $rating = $data['notificationPayload']['rating'] ?? 'Positive';
        $comment = $data['notificationPayload']['comment'] ?? '';
        
        if ($feedback_id && $item_id) {
            $this->model_extension_module_ebay->saveFeedback([
                'feedback_id' => $feedback_id,
                'item_id' => $item_id,
                'rating' => $rating,
                'comment' => $comment,
                'type' => 'received'
            ]);
            
            $this->log->write('[INFO] eBay feedback received - Feedback ID: ' . $feedback_id . ', Rating: ' . $rating);
        }
    }
    
    /**
     * Alıcı iptal talebi
     */
    private function handleBuyerCancelRequest($data) {
        $this->log->write('[INFO] eBay buyer cancel request notification received');
        
        $this->load->model('extension/module/ebay');
        
        $cancel_id = $data['notificationPayload']['cancelId'] ?? null;
        $item_id = $data['notificationPayload']['itemId'] ?? null;
        $reason = $data['notificationPayload']['reason'] ?? 'Unknown';
        
        if ($cancel_id && $item_id) {
            $this->model_extension_module_ebay->createCancelRequest([
                'cancel_id' => $cancel_id,
                'item_id' => $item_id,
                'reason' => $reason,
                'status' => 'pending'
            ]);
            
            // Admin bildirim gönder
            $this->sendAdminNotification('eBay Cancel Request', 'Cancel ID: ' . $cancel_id . ', Reason: ' . $reason);
            
            $this->log->write('[INFO] eBay buyer cancel request - Cancel ID: ' . $cancel_id);
        }
    }
    
    /**
     * İade oluşturma işlemi
     */
    private function handleReturnCreated($data) {
        $this->log->write('[INFO] eBay return created notification received');
        
        $this->load->model('extension/module/ebay');
        
        $return_id = $data['notificationPayload']['returnId'] ?? null;
        $item_id = $data['notificationPayload']['itemId'] ?? null;
        $reason = $data['notificationPayload']['reason'] ?? 'Unknown';
        
        if ($return_id && $item_id) {
            $this->model_extension_module_ebay->createReturn([
                'return_id' => $return_id,
                'item_id' => $item_id,
                'reason' => $reason,
                'status' => 'pending'
            ]);
            
            // Admin bildirim gönder
            $this->sendAdminNotification('eBay Return Request', 'Return ID: ' . $return_id . ', Reason: ' . $reason);
            
            $this->log->write('[INFO] eBay return created - Return ID: ' . $return_id);
        }
    }
    
    /**
     * Yeniden listeleme zamanla
     */
    private function scheduleRelist($item_id) {
        // Cron job için zamanla
        $this->db->query("INSERT INTO " . DB_PREFIX . "ebay_relist_queue (item_id, scheduled_at, status) VALUES ('" . $this->db->escape($item_id) . "', NOW() + INTERVAL 1 HOUR, 'pending')");
        
        $this->log->write('[INFO] eBay item scheduled for relist: ' . $item_id);
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
        $this->load->language('extension/module/ebay');
        $this->document->setTitle('eBay Webhook Settings');
        $this->load->model('setting/setting');
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            $this->model_setting_setting->editSetting('module_ebay_webhook', $this->request->post);
            $this->session->data['success'] = 'eBay webhook settings saved successfully!';
            $this->response->redirect($this->url->link('extension/module/ebay_webhooks/settings', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => 'Home',
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        $data['action'] = $this->url->link('extension/module/ebay_webhooks/settings', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/module/ebay', 'user_token=' . $this->session->data['user_token'], true);
        
        // Webhook URL
        $data['webhook_url'] = HTTPS_CATALOG . 'index.php?route=extension/module/ebay_webhooks';
        
        // Mevcut ayarları yükle
        $data['module_ebay_webhook_verification_token'] = $this->config->get('module_ebay_webhook_verification_token');
        $data['module_ebay_webhook_endpoint_url'] = $this->config->get('module_ebay_webhook_endpoint_url');
        $data['module_ebay_webhook_enabled'] = $this->config->get('module_ebay_webhook_enabled');
        
        $this->response->setOutput($this->load->view('extension/module/ebay_webhooks_settings', $data));
    }
    
    /**
     * Webhook test
     */
    public function test() {
        $this->log->write('[INFO] eBay webhook test initiated');
        
        // Test webhook verisi
        $test_data = [
            'metadata' => [
                'topic' => 'ITEM_SOLD',
                'schemaVersion' => '1.0',
                'deprecated' => false
            ],
            'notification' => [
                'notificationId' => 'test-' . time(),
                'publishedDate' => date('c'),
                'notificationPayload' => [
                    'itemId' => 'TEST123456789',
                    'quantitySold' => 1,
                    'salePrice' => 29.99,
                    'currency' => 'USD'
                ]
            ]
        ];
        
        try {
            $this->handleItemSold($test_data);
            
            $this->log->write('[INFO] eBay webhook test completed successfully');
            echo json_encode(['status' => 'success', 'message' => 'Webhook test completed successfully']);
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] eBay webhook test failed: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Webhook test failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Validation kontrolü
     */
    private function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/ebay_webhooks')) {
            $this->error['warning'] = 'You do not have permission to modify eBay webhook settings!';
        }
        
        return !$this->error;
    }
} 