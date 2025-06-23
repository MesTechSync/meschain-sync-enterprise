<?php
/**
 * Hepsiburada Webhook Controller
 * MesChain-Sync v4.1 - OpenCart 3.0.4.0 Integration
 * Hepsiburada Marketplace Webhook Integration for Real-time Order & Inventory Management
 * 
 * @author MesChain Development Team
 * @version 4.1.0
 * @copyright 2024 MesChain Technologies
 * @supports Hepsiburada API v3 Webhooks, Order Notifications, Stock Updates
 */

class ControllerExtensionModuleHepsiburadaWebhooks extends Controller {
    
    private $log;
    private $logFile = 'hepsiburada_webhooks.log';
    private $encryption;
    
    public function __construct($registry) {
        parent::__construct($registry);
        $this->log = new Log($this->logFile);
        
        // Şifreleme helper'ı yükle
        require_once(DIR_SYSTEM . 'library/meschain/encryption.php');
        $this->encryption = new MeschainEncryption();
    }
    
    /**
     * Ana webhook handler - Hepsiburada'dan gelen tüm webhook'ları karşılar
     */
    public function index() {
        $this->log->write('[INFO] Hepsiburada webhook endpoint called');
        
        try {
            // Raw POST data al
            $raw_data = file_get_contents('php://input');
            $headers = getallheaders();
            
            // Webhook doğrulama
            if (!$this->validateWebhook($raw_data, $headers)) {
                $this->log->write('[ERROR] Hepsiburada webhook validation failed');
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                return;
            }
            
            // JSON decode
            $webhook_data = json_decode($raw_data, true);
            
            if (!$webhook_data) {
                $this->log->write('[ERROR] Invalid JSON in Hepsiburada webhook');
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON']);
                return;
            }
            
            $this->log->write('[INFO] Hepsiburada webhook data received: ' . json_encode($webhook_data));
            
            // Event tipine göre işlem
            $event_type = $webhook_data['eventType'] ?? $webhook_data['event'] ?? null;
            
            switch ($event_type) {
                case 'order.created':
                    $this->handleOrderCreated($webhook_data);
                    break;
                    
                case 'order.status.changed':
                    $this->handleOrderStatusChanged($webhook_data);
                    break;
                    
                case 'order.cancelled':
                    $this->handleOrderCancelled($webhook_data);
                    break;
                    
                case 'order.refunded':
                    $this->handleOrderRefunded($webhook_data);
                    break;
                    
                case 'product.stock.changed':
                    $this->handleProductStockChanged($webhook_data);
                    break;
                    
                case 'product.price.changed':
                    $this->handleProductPriceChanged($webhook_data);
                    break;
                    
                case 'product.status.changed':
                    $this->handleProductStatusChanged($webhook_data);
                    break;
                    
                case 'listing.rejected':
                    $this->handleListingRejected($webhook_data);
                    break;
                    
                case 'listing.approved':
                    $this->handleListingApproved($webhook_data);
                    break;
                    
                case 'campaign.started':
                    $this->handleCampaignStarted($webhook_data);
                    break;
                    
                case 'campaign.ended':
                    $this->handleCampaignEnded($webhook_data);
                    break;
                    
                case 'return.request.created':
                    $this->handleReturnRequestCreated($webhook_data);
                    break;
                    
                case 'return.request.approved':
                    $this->handleReturnRequestApproved($webhook_data);
                    break;
                    
                case 'question.received':
                    $this->handleQuestionReceived($webhook_data);
                    break;
                    
                case 'review.received':
                    $this->handleReviewReceived($webhook_data);
                    break;
                    
                default:
                    $this->log->write('[WARNING] Unknown Hepsiburada event type: ' . $event_type);
                    break;
            }
            
            // Başarılı yanıt
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Webhook processed successfully']);
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Hepsiburada webhook processing failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
    
    /**
     * Hepsiburada webhook'u doğrula
     */
    private function validateWebhook($raw_data, $headers) {
        $webhook_secret = $this->config->get('module_hepsiburada_webhook_secret');
        
        if (!$webhook_secret) {
            $this->log->write('[ERROR] Hepsiburada webhook secret not configured');
            return false;
        }
        
        // Hepsiburada signature doğrulama
        $signature = $headers['X-Hepsiburada-Signature'] ?? $headers['x-hepsiburada-signature'] ?? '';
        
        if (!$signature) {
            $this->log->write('[ERROR] Hepsiburada webhook signature missing');
            return false;
        }
        
        // Signature hesapla
        $calculated_signature = 'sha256=' . hash_hmac('sha256', $raw_data, $webhook_secret);
        
        if (!hash_equals($calculated_signature, $signature)) {
            $this->log->write('[ERROR] Hepsiburada webhook signature mismatch');
            return false;
        }
        
        return true;
    }
    
    /**
     * Yeni sipariş oluşturma işlemi
     */
    private function handleOrderCreated($data) {
        $this->log->write('[INFO] Hepsiburada order created notification received');
        
        $this->load->model('extension/module/hepsiburada');
        $this->load->model('sale/order');
        
        $order_data = $data['orderData'] ?? $data['data'] ?? [];
        $order_number = $order_data['orderNumber'] ?? null;
        
        if (!$order_number) {
            $this->log->write('[ERROR] Hepsiburada order number missing');
            return;
        }
        
        try {
            // Sipariş zaten var mı kontrol et
            $existing_order = $this->model_extension_module_hepsiburada->getOrderByHepsiburadaOrderNumber($order_number);
            
            if (!$existing_order) {
                // Yeni sipariş oluştur
                $opencart_order_data = $this->prepareOpenCartOrderData($order_data);
                $order_id = $this->model_extension_module_hepsiburada->createOrder($opencart_order_data);
                
                // Hepsiburada sipariş mapping'i kaydet
                $this->model_extension_module_hepsiburada->saveOrderMapping($order_id, $order_number, $order_data);
                
                $this->log->write('[INFO] Hepsiburada order created - Order Number: ' . $order_number . ', OpenCart Order ID: ' . $order_id);
                
                // Stok güncelle
                $this->updateStockFromOrder($order_data);
                
                // Admin bildirim gönder
                $this->sendAdminNotification('Yeni Hepsiburada Siparişi', 'Sipariş No: ' . $order_number . ' - ' . count($order_data['items'] ?? []) . ' ürün');
                
            } else {
                $this->log->write('[INFO] Hepsiburada order already exists - Order Number: ' . $order_number);
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to create Hepsiburada order: ' . $e->getMessage());
        }
    }
    
    /**
     * Sipariş durum değişiklik işlemi
     */
    private function handleOrderStatusChanged($data) {
        $this->log->write('[INFO] Hepsiburada order status changed notification received');
        
        $this->load->model('extension/module/hepsiburada');
        
        $order_data = $data['orderData'] ?? $data['data'] ?? [];
        $order_number = $order_data['orderNumber'] ?? null;
        $new_status = $order_data['status'] ?? null;
        
        if (!$order_number || !$new_status) {
            $this->log->write('[ERROR] Hepsiburada order number or status missing');
            return;
        }
        
        // OpenCart sipariş ID'sini bul
        $order_id = $this->model_extension_module_hepsiburada->getOrderIdByHepsiburadaOrderNumber($order_number);
        
        if ($order_id) {
            // OpenCart status'u dönüştür
            $opencart_status = $this->mapHepsiburadaStatusToOpenCart($new_status);
            
            // Sipariş durumunu güncelle
            $this->model_extension_module_hepsiburada->updateOrderStatus($order_id, $opencart_status);
            
            $this->log->write('[INFO] Hepsiburada order status updated - Order Number: ' . $order_number . ', Status: ' . $new_status);
            
            // Önemli durum değişikliklerinde bildirim gönder
            if (in_array($new_status, ['Cancelled', 'Refunded', 'Delivered', 'Shipped'])) {
                $this->sendAdminNotification('Hepsiburada Sipariş Durumu', 'Sipariş No: ' . $order_number . ' - Durum: ' . $new_status);
            }
        }
    }
    
    /**
     * Sipariş iptal işlemi
     */
    private function handleOrderCancelled($data) {
        $this->log->write('[INFO] Hepsiburada order cancelled notification received');
        
        $this->load->model('extension/module/hepsiburada');
        
        $order_data = $data['orderData'] ?? $data['data'] ?? [];
        $order_number = $order_data['orderNumber'] ?? null;
        $cancel_reason = $order_data['cancelReason'] ?? 'Unknown';
        
        if (!$order_number) {
            $this->log->write('[ERROR] Hepsiburada order number missing in cancel notification');
            return;
        }
        
        $order_id = $this->model_extension_module_hepsiburada->getOrderIdByHepsiburadaOrderNumber($order_number);
        
        if ($order_id) {
            // Sipariş durumunu iptal olarak güncelle
            $this->model_extension_module_hepsiburada->updateOrderStatus($order_id, 7); // 7 = Cancelled
            
            // Stok geri yükle
            $this->restoreStockFromCancelledOrder($order_data);
            
            $this->log->write('[INFO] Hepsiburada order cancelled - Order Number: ' . $order_number . ', Reason: ' . $cancel_reason);
            
            // Admin bildirim gönder
            $this->sendAdminNotification('Hepsiburada Sipariş İptali', 'Sipariş No: ' . $order_number . ' - Sebep: ' . $cancel_reason);
        }
    }
    
    /**
     * Sipariş iade işlemi
     */
    private function handleOrderRefunded($data) {
        $this->log->write('[INFO] Hepsiburada order refunded notification received');
        
        $this->load->model('extension/module/hepsiburada');
        
        $order_data = $data['orderData'] ?? $data['data'] ?? [];
        $order_number = $order_data['orderNumber'] ?? null;
        $refund_amount = $order_data['refundAmount'] ?? 0;
        
        if (!$order_number) {
            $this->log->write('[ERROR] Hepsiburada order number missing in refund notification');
            return;
        }
        
        $order_id = $this->model_extension_module_hepsiburada->getOrderIdByHepsiburadaOrderNumber($order_number);
        
        if ($order_id) {
            // İade kaydını oluştur
            $this->model_extension_module_hepsiburada->createRefundRecord($order_id, $refund_amount, $order_data);
            
            $this->log->write('[INFO] Hepsiburada order refunded - Order Number: ' . $order_number . ', Amount: ' . $refund_amount);
            
            // Admin bildirim gönder
            $this->sendAdminNotification('Hepsiburada Sipariş İadesi', 'Sipariş No: ' . $order_number . ' - Tutar: ' . $refund_amount . ' TL');
        }
    }
    
    /**
     * Ürün stok değişiklik işlemi
     */
    private function handleProductStockChanged($data) {
        $this->log->write('[INFO] Hepsiburada product stock changed notification received');
        
        $this->load->model('extension/module/hepsiburada');
        $this->load->model('catalog/product');
        
        $product_data = $data['productData'] ?? $data['data'] ?? [];
        $sku = $product_data['sku'] ?? null;
        $new_stock = $product_data['stock'] ?? 0;
        
        if (!$sku) {
            $this->log->write('[ERROR] Hepsiburada product SKU missing in stock change notification');
            return;
        }
        
        // SKU'ya göre ürün bul
        $product_id = $this->model_extension_module_hepsiburada->getProductIdBySku($sku);
        
        if ($product_id) {
            // Stok güncelle
            $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = '" . (int)$new_stock . "' WHERE product_id = '" . (int)$product_id . "'");
            
            $this->log->write('[INFO] Hepsiburada product stock updated - SKU: ' . $sku . ', New Stock: ' . $new_stock);
            
            // Kritik stok seviyesinde uyarı gönder
            $critical_stock_level = $this->config->get('module_hepsiburada_critical_stock_level') ?? 5;
            if ($new_stock <= $critical_stock_level) {
                $this->sendAdminNotification('Hepsiburada Kritik Stok', 'SKU: ' . $sku . ' - Stok: ' . $new_stock);
            }
        }
    }
    
    /**
     * Ürün fiyat değişiklik işlemi
     */
    private function handleProductPriceChanged($data) {
        $this->log->write('[INFO] Hepsiburada product price changed notification received');
        
        $this->load->model('extension/module/hepsiburada');
        
        $product_data = $data['productData'] ?? $data['data'] ?? [];
        $sku = $product_data['sku'] ?? null;
        $new_price = $product_data['price'] ?? 0;
        
        if (!$sku) {
            $this->log->write('[ERROR] Hepsiburada product SKU missing in price change notification');
            return;
        }
        
        $product_id = $this->model_extension_module_hepsiburada->getProductIdBySku($sku);
        
        if ($product_id) {
            // Fiyat güncelle
            $this->db->query("UPDATE " . DB_PREFIX . "product SET price = '" . (float)$new_price . "' WHERE product_id = '" . (int)$product_id . "'");
            
            $this->log->write('[INFO] Hepsiburada product price updated - SKU: ' . $sku . ', New Price: ' . $new_price);
        }
    }
    
    /**
     * Ürün durum değişiklik işlemi
     */
    private function handleProductStatusChanged($data) {
        $this->log->write('[INFO] Hepsiburada product status changed notification received');
        
        $this->load->model('extension/module/hepsiburada');
        
        $product_data = $data['productData'] ?? $data['data'] ?? [];
        $sku = $product_data['sku'] ?? null;
        $new_status = $product_data['status'] ?? null;
        
        if (!$sku || !$new_status) {
            $this->log->write('[ERROR] Hepsiburada product SKU or status missing');
            return;
        }
        
        $product_id = $this->model_extension_module_hepsiburada->getProductIdBySku($sku);
        
        if ($product_id) {
            // OpenCart status'u dönüştür
            $opencart_status = ($new_status === 'ACTIVE') ? 1 : 0;
            
            // Ürün durumunu güncelle
            $this->db->query("UPDATE " . DB_PREFIX . "product SET status = '" . (int)$opencart_status . "' WHERE product_id = '" . (int)$product_id . "'");
            
            $this->log->write('[INFO] Hepsiburada product status updated - SKU: ' . $sku . ', Status: ' . $new_status);
            
            // Status değişikliklerinde bildirim gönder
            if ($new_status === 'INACTIVE' || $new_status === 'REJECTED') {
                $this->sendAdminNotification('Hepsiburada Ürün Durumu', 'SKU: ' . $sku . ' - Durum: ' . $new_status);
            }
        }
    }
    
    /**
     * Listing red işlemi
     */
    private function handleListingRejected($data) {
        $this->log->write('[INFO] Hepsiburada listing rejected notification received');
        
        $this->load->model('extension/module/hepsiburada');
        
        $listing_data = $data['listingData'] ?? $data['data'] ?? [];
        $sku = $listing_data['sku'] ?? null;
        $reject_reason = $listing_data['rejectReason'] ?? 'Unknown';
        
        if (!$sku) {
            $this->log->write('[ERROR] Hepsiburada product SKU missing in listing rejection');
            return;
        }
        
        // Reject kaydını oluştur
        $this->model_extension_module_hepsiburada->createListingRejection($sku, $reject_reason, $listing_data);
        
        $this->log->write('[INFO] Hepsiburada listing rejected - SKU: ' . $sku . ', Reason: ' . $reject_reason);
        
        // Admin bildirim gönder
        $this->sendAdminNotification('Hepsiburada Listing Reddedildi', 'SKU: ' . $sku . ' - Sebep: ' . $reject_reason);
    }
    
    /**
     * Listing onay işlemi
     */
    private function handleListingApproved($data) {
        $this->log->write('[INFO] Hepsiburada listing approved notification received');
        
        $this->load->model('extension/module/hepsiburada');
        
        $listing_data = $data['listingData'] ?? $data['data'] ?? [];
        $sku = $listing_data['sku'] ?? null;
        $listing_id = $listing_data['listingId'] ?? null;
        
        if (!$sku) {
            $this->log->write('[ERROR] Hepsiburada product SKU missing in listing approval');
            return;
        }
        
        // Listing onay kaydını güncelle
        $this->model_extension_module_hepsiburada->updateListingStatus($sku, 'approved', $listing_id);
        
        $this->log->write('[INFO] Hepsiburada listing approved - SKU: ' . $sku . ', Listing ID: ' . $listing_id);
    }
    
    /**
     * Kampanya başlatma işlemi
     */
    private function handleCampaignStarted($data) {
        $this->log->write('[INFO] Hepsiburada campaign started notification received');
        
        $campaign_data = $data['campaignData'] ?? $data['data'] ?? [];
        $campaign_id = $campaign_data['campaignId'] ?? null;
        $campaign_name = $campaign_data['campaignName'] ?? 'Unknown';
        
        if ($campaign_id) {
            $this->sendAdminNotification('Hepsiburada Kampanya Başladı', 'Kampanya: ' . $campaign_name . ' (ID: ' . $campaign_id . ')');
            $this->log->write('[INFO] Hepsiburada campaign started - ID: ' . $campaign_id . ', Name: ' . $campaign_name);
        }
    }
    
    /**
     * Kampanya bitiş işlemi
     */
    private function handleCampaignEnded($data) {
        $this->log->write('[INFO] Hepsiburada campaign ended notification received');
        
        $campaign_data = $data['campaignData'] ?? $data['data'] ?? [];
        $campaign_id = $campaign_data['campaignId'] ?? null;
        $campaign_name = $campaign_data['campaignName'] ?? 'Unknown';
        
        if ($campaign_id) {
            $this->sendAdminNotification('Hepsiburada Kampanya Bitti', 'Kampanya: ' . $campaign_name . ' (ID: ' . $campaign_id . ')');
            $this->log->write('[INFO] Hepsiburada campaign ended - ID: ' . $campaign_id . ', Name: ' . $campaign_name);
        }
    }
    
    /**
     * İade talebi oluşturma işlemi
     */
    private function handleReturnRequestCreated($data) {
        $this->log->write('[INFO] Hepsiburada return request created notification received');
        
        $this->load->model('extension/module/hepsiburada');
        
        $return_data = $data['returnData'] ?? $data['data'] ?? [];
        $return_id = $return_data['returnId'] ?? null;
        $order_number = $return_data['orderNumber'] ?? null;
        $reason = $return_data['reason'] ?? 'Unknown';
        
        if ($return_id && $order_number) {
            $this->model_extension_module_hepsiburada->createReturnRequest($return_id, $order_number, $reason, $return_data);
            
            $this->log->write('[INFO] Hepsiburada return request created - Return ID: ' . $return_id . ', Order: ' . $order_number);
            
            // Admin bildirim gönder
            $this->sendAdminNotification('Hepsiburada İade Talebi', 'İade ID: ' . $return_id . ' - Sipariş: ' . $order_number . ' - Sebep: ' . $reason);
        }
    }
    
    /**
     * İade talebi onay işlemi
     */
    private function handleReturnRequestApproved($data) {
        $this->log->write('[INFO] Hepsiburada return request approved notification received');
        
        $this->load->model('extension/module/hepsiburada');
        
        $return_data = $data['returnData'] ?? $data['data'] ?? [];
        $return_id = $return_data['returnId'] ?? null;
        
        if ($return_id) {
            $this->model_extension_module_hepsiburada->updateReturnRequestStatus($return_id, 'approved');
            
            $this->log->write('[INFO] Hepsiburada return request approved - Return ID: ' . $return_id);
            
            // Admin bildirim gönder
            $this->sendAdminNotification('Hepsiburada İade Onayı', 'İade ID: ' . $return_id . ' onaylandı');
        }
    }
    
    /**
     * Soru alma işlemi
     */
    private function handleQuestionReceived($data) {
        $this->log->write('[INFO] Hepsiburada question received notification');
        
        $this->load->model('extension/module/hepsiburada');
        
        $question_data = $data['questionData'] ?? $data['data'] ?? [];
        $question_id = $question_data['questionId'] ?? null;
        $sku = $question_data['sku'] ?? null;
        $question_text = $question_data['question'] ?? '';
        
        if ($question_id && $sku) {
            $this->model_extension_module_hepsiburada->saveQuestion($question_id, $sku, $question_text, $question_data);
            
            $this->log->write('[INFO] Hepsiburada question received - Question ID: ' . $question_id . ', SKU: ' . $sku);
            
            // Admin bildirim gönder
            $this->sendAdminNotification('Hepsiburada Yeni Soru', 'SKU: ' . $sku . ' - Soru: ' . substr($question_text, 0, 100) . '...');
        }
    }
    
    /**
     * Değerlendirme alma işlemi
     */
    private function handleReviewReceived($data) {
        $this->log->write('[INFO] Hepsiburada review received notification');
        
        $this->load->model('extension/module/hepsiburada');
        
        $review_data = $data['reviewData'] ?? $data['data'] ?? [];
        $review_id = $review_data['reviewId'] ?? null;
        $sku = $review_data['sku'] ?? null;
        $rating = $review_data['rating'] ?? 0;
        $review_text = $review_data['review'] ?? '';
        
        if ($review_id && $sku) {
            $this->model_extension_module_hepsiburada->saveReview($review_id, $sku, $rating, $review_text, $review_data);
            
            $this->log->write('[INFO] Hepsiburada review received - Review ID: ' . $review_id . ', SKU: ' . $sku . ', Rating: ' . $rating);
            
            // Düşük puanlı değerlendirmelerde uyarı gönder
            if ($rating <= 2) {
                $this->sendAdminNotification('Hepsiburada Düşük Puan', 'SKU: ' . $sku . ' - Puan: ' . $rating . '/5 - ' . substr($review_text, 0, 100) . '...');
            }
        }
    }
    
    /**
     * OpenCart sipariş verisi hazırla
     */
    private function prepareOpenCartOrderData($hepsiburada_order) {
        // Hepsiburada sipariş verilerini OpenCart formatına dönüştür
        $order_data = [
            'invoice_prefix' => 'HB-',
            'store_id' => 0,
            'store_name' => $this->config->get('config_name'),
            'store_url' => HTTP_CATALOG,
            'customer_id' => 0,
            'customer_group_id' => 1,
            'firstname' => $hepsiburada_order['shippingAddress']['firstName'] ?? 'Hepsiburada',
            'lastname' => $hepsiburada_order['shippingAddress']['lastName'] ?? 'Customer',
            'email' => 'hepsiburada@' . parse_url(HTTP_CATALOG, PHP_URL_HOST),
            'telephone' => $hepsiburada_order['shippingAddress']['phone'] ?? '',
            'custom_field' => [],
            'payment_firstname' => $hepsiburada_order['billingAddress']['firstName'] ?? 'Hepsiburada',
            'payment_lastname' => $hepsiburada_order['billingAddress']['lastName'] ?? 'Customer',
            'payment_company' => '',
            'payment_address_1' => $hepsiburada_order['billingAddress']['address'] ?? '',
            'payment_address_2' => '',
            'payment_city' => $hepsiburada_order['billingAddress']['city'] ?? '',
            'payment_postcode' => $hepsiburada_order['billingAddress']['zipCode'] ?? '',
            'payment_country' => 'Turkey',
            'payment_country_id' => 215,
            'payment_zone' => $hepsiburada_order['billingAddress']['district'] ?? '',
            'payment_zone_id' => 0,
            'payment_custom_field' => [],
            'payment_method' => 'Hepsiburada Payment',
            'payment_code' => 'hepsiburada_payment',
            'shipping_firstname' => $hepsiburada_order['shippingAddress']['firstName'] ?? 'Hepsiburada',
            'shipping_lastname' => $hepsiburada_order['shippingAddress']['lastName'] ?? 'Customer',
            'shipping_company' => '',
            'shipping_address_1' => $hepsiburada_order['shippingAddress']['address'] ?? '',
            'shipping_address_2' => '',
            'shipping_city' => $hepsiburada_order['shippingAddress']['city'] ?? '',
            'shipping_postcode' => $hepsiburada_order['shippingAddress']['zipCode'] ?? '',
            'shipping_country' => 'Turkey',
            'shipping_country_id' => 215,
            'shipping_zone' => $hepsiburada_order['shippingAddress']['district'] ?? '',
            'shipping_zone_id' => 0,
            'shipping_custom_field' => [],
            'shipping_method' => 'Hepsiburada Shipping',
            'shipping_code' => 'hepsiburada_shipping',
            'comment' => 'Hepsiburada Order: ' . ($hepsiburada_order['orderNumber'] ?? ''),
            'total' => $hepsiburada_order['totalAmount'] ?? 0,
            'affiliate_id' => 0,
            'commission' => 0,
            'marketing_id' => 0,
            'tracking' => '',
            'language_id' => 2, // Turkish
            'currency_id' => 3, // TRY
            'currency_code' => 'TRY',
            'currency_value' => 1,
            'ip' => '',
            'forwarded_ip' => '',
            'user_agent' => 'Hepsiburada Webhook',
            'accept_language' => 'tr-TR'
        ];
        
        // Ürün bilgilerini ekle
        $order_data['order_product'] = [];
        foreach ($hepsiburada_order['items'] ?? [] as $item) {
            $order_data['order_product'][] = [
                'product_id' => $this->model_extension_module_hepsiburada->getProductIdBySku($item['sku'] ?? ''),
                'name' => $item['productName'] ?? 'Hepsiburada Product',
                'model' => $item['sku'] ?? '',
                'option' => [],
                'download' => [],
                'quantity' => $item['quantity'] ?? 1,
                'subtract' => 1,
                'price' => $item['price'] ?? 0,
                'total' => ($item['price'] ?? 0) * ($item['quantity'] ?? 1),
                'tax' => 0,
                'reward' => 0
            ];
        }
        
        return $order_data;
    }
    
    /**
     * Siparişteki ürünlerin stokunu güncelle
     */
    private function updateStockFromOrder($order_data) {
        $this->load->model('extension/module/hepsiburada');
        $this->load->model('catalog/product');
        
        foreach ($order_data['items'] ?? [] as $item) {
            $sku = $item['sku'] ?? null;
            $quantity = $item['quantity'] ?? 1;
            
            if ($sku) {
                $product_id = $this->model_extension_module_hepsiburada->getProductIdBySku($sku);
                
                if ($product_id) {
                    $current_stock = $this->model_catalog_product->getProduct($product_id)['quantity'];
                    $new_stock = max(0, $current_stock - $quantity);
                    
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = '" . (int)$new_stock . "' WHERE product_id = '" . (int)$product_id . "'");
                    
                    $this->log->write('[INFO] Stock updated from Hepsiburada order - SKU: ' . $sku . ', Quantity sold: ' . $quantity . ', New stock: ' . $new_stock);
                }
            }
        }
    }
    
    /**
     * İptal edilen siparişteki ürünlerin stokunu geri yükle
     */
    private function restoreStockFromCancelledOrder($order_data) {
        $this->load->model('extension/module/hepsiburada');
        $this->load->model('catalog/product');
        
        foreach ($order_data['items'] ?? [] as $item) {
            $sku = $item['sku'] ?? null;
            $quantity = $item['quantity'] ?? 1;
            
            if ($sku) {
                $product_id = $this->model_extension_module_hepsiburada->getProductIdBySku($sku);
                
                if ($product_id) {
                    $current_stock = $this->model_catalog_product->getProduct($product_id)['quantity'];
                    $new_stock = $current_stock + $quantity;
                    
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = '" . (int)$new_stock . "' WHERE product_id = '" . (int)$product_id . "'");
                    
                    $this->log->write('[INFO] Stock restored from cancelled Hepsiburada order - SKU: ' . $sku . ', Quantity restored: ' . $quantity . ', New stock: ' . $new_stock);
                }
            }
        }
    }
    
    /**
     * Hepsiburada durum kodlarını OpenCart'a dönüştür
     */
    private function mapHepsiburadaStatusToOpenCart($hepsiburada_status) {
        $status_map = [
            'New' => 1,          // Pending
            'Preparing' => 2,    // Processing
            'Shipped' => 3,      // Shipped
            'Delivered' => 5,    // Complete
            'Cancelled' => 7,    // Cancelled
            'Refunded' => 11,    // Refunded
            'Returned' => 12     // Returned
        ];
        
        return $status_map[$hepsiburada_status] ?? 1;
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
        $this->load->language('extension/module/hepsiburada');
        $this->document->setTitle('Hepsiburada Webhook Settings');
        $this->load->model('setting/setting');
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            $this->model_setting_setting->editSetting('module_hepsiburada_webhook', $this->request->post);
            $this->session->data['success'] = 'Hepsiburada webhook settings saved successfully!';
            $this->response->redirect($this->url->link('extension/module/hepsiburada_webhooks/settings', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => 'Home',
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        $data['action'] = $this->url->link('extension/module/hepsiburada_webhooks/settings', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/module/hepsiburada', 'user_token=' . $this->session->data['user_token'], true);
        
        // Webhook URL
        $data['webhook_url'] = HTTPS_CATALOG . 'index.php?route=extension/module/hepsiburada_webhooks';
        
        // Mevcut ayarları yükle
        $data['module_hepsiburada_webhook_secret'] = $this->config->get('module_hepsiburada_webhook_secret');
        $data['module_hepsiburada_webhook_enabled'] = $this->config->get('module_hepsiburada_webhook_enabled');
        $data['module_hepsiburada_critical_stock_level'] = $this->config->get('module_hepsiburada_critical_stock_level') ?? 5;
        
        $this->response->setOutput($this->load->view('extension/module/hepsiburada_webhooks_settings', $data));
    }
    
    /**
     * Webhook test
     */
    public function test() {
        $this->log->write('[INFO] Hepsiburada webhook test initiated');
        
        // Test webhook verisi
        $test_data = [
            'eventType' => 'order.created',
            'orderData' => [
                'orderNumber' => 'TEST-HB-' . time(),
                'totalAmount' => 99.99,
                'status' => 'New',
                'items' => [
                    [
                        'sku' => 'TEST-SKU-001',
                        'productName' => 'Test Product',
                        'quantity' => 1,
                        'price' => 99.99
                    ]
                ],
                'shippingAddress' => [
                    'firstName' => 'Test',
                    'lastName' => 'Customer',
                    'address' => 'Test Address',
                    'city' => 'Istanbul',
                    'district' => 'Kadikoy',
                    'zipCode' => '34710',
                    'phone' => '5555555555'
                ],
                'billingAddress' => [
                    'firstName' => 'Test',
                    'lastName' => 'Customer',
                    'address' => 'Test Address',
                    'city' => 'Istanbul',
                    'district' => 'Kadikoy',
                    'zipCode' => '34710'
                ]
            ]
        ];
        
        try {
            $this->handleOrderCreated($test_data);
            
            $this->log->write('[INFO] Hepsiburada webhook test completed successfully');
            echo json_encode(['status' => 'success', 'message' => 'Webhook test completed successfully']);
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Hepsiburada webhook test failed: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Webhook test failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Validation kontrolü
     */
    private function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/hepsiburada_webhooks')) {
            $this->error['warning'] = 'You do not have permission to modify Hepsiburada webhook settings!';
        }
        
        return !$this->error;
    }
} 