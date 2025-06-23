<?php
/**
 * Pazarama Webhook Controller
 * MesChain-Sync v3.0 - Pazarama Marketplace Integration
 * 
 * Handles webhook management for Pazarama marketplace integration
 * Features: Event registration, webhook testing, log management
 */

class ControllerExtensionModulePazaramaWebhooks extends Controller {
    
    private $error = array();
    
    public function index() {
        $this->load->language('extension/module/pazarama_webhooks');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/module/pazarama_webhook');
        
        // Webhook listesini getir
        $data['webhooks'] = $this->model_extension_module_pazarama_webhook->getWebhooks([
            'start' => 0,
            'limit' => 20,
            'order' => 'DESC',
            'sort' => 'date_added'
        ]);
        
        // Bildirim listesini getir
        $data['notifications'] = $this->model_extension_module_pazarama_webhook->getNotifications([
            'start' => 0,
            'limit' => 10,
            'order' => 'DESC',
            'sort' => 'date_added'
        ]);
        
        // Yeni webhook oluşturma
        if (isset($this->request->post['event_type']) && isset($this->request->post['url'])) {
            $this->model_extension_module_pazarama_webhook->addWebhook([
                'event_type' => $this->request->post['event_type'],
                'url' => $this->request->post['url'],
                'status' => isset($this->request->post['status']) ? (int)$this->request->post['status'] : 1
            ]);
            
            $this->session->data['success'] = $this->language->get('text_webhook_added');
            
            $this->response->redirect($this->url->link('extension/module/pazarama_webhooks', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Webhook silme
        if (isset($this->request->get['delete_webhook']) && (int)$this->request->get['delete_webhook'] > 0) {
            $this->model_extension_module_pazarama_webhook->deleteWebhook((int)$this->request->get['delete_webhook']);
            
            $this->session->data['success'] = $this->language->get('text_webhook_deleted');
            
            $this->response->redirect($this->url->link('extension/module/pazarama_webhooks', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Webhook durumunu değiştir
        if (isset($this->request->get['toggle_webhook']) && (int)$this->request->get['toggle_webhook'] > 0) {
            $webhook = $this->model_extension_module_pazarama_webhook->getWebhook((int)$this->request->get['toggle_webhook']);
            
            if ($webhook) {
                $this->model_extension_module_pazarama_webhook->updateWebhookStatus(
                    (int)$this->request->get['toggle_webhook'],
                    $webhook['status'] ? 0 : 1
                );
                
                $this->session->data['success'] = $this->language->get('text_webhook_updated');
            }
            
            $this->response->redirect($this->url->link('extension/module/pazarama_webhooks', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Bildirim silme
        if (isset($this->request->get['delete_notification']) && (int)$this->request->get['delete_notification'] > 0) {
            $this->model_extension_module_pazarama_webhook->deleteNotification((int)$this->request->get['delete_notification']);
            
            $this->session->data['success'] = $this->language->get('text_notification_deleted');
            
            $this->response->redirect($this->url->link('extension/module/pazarama_webhooks', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Event türleri
        $data['event_types'] = [
            'order_created' => $this->language->get('text_order_created'),
            'order_updated' => $this->language->get('text_order_updated'),
            'order_cancelled' => $this->language->get('text_order_cancelled'),
            'product_approved' => $this->language->get('text_product_approved'),
            'product_rejected' => $this->language->get('text_product_rejected'),
            'inventory_updated' => $this->language->get('text_inventory_updated'),
            'payment_completed' => $this->language->get('text_payment_completed')
        ];
        
        // Breadcrumb
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/pazarama_webhooks', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Form URL'leri
        $data['add_webhook'] = $this->url->link('extension/module/pazarama_webhooks', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        // Mesajlar
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        if (isset($this->session->data['error'])) {
            $data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $data['error'] = '';
        }
        
        // Dil değişkenleri
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_add'] = $this->language->get('text_add');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_no_webhooks'] = $this->language->get('text_no_webhooks');
        $data['text_no_notifications'] = $this->language->get('text_no_notifications');
        $data['text_webhooks'] = $this->language->get('text_webhooks');
        $data['text_notifications'] = $this->language->get('text_notifications');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['text_enable'] = $this->language->get('text_enable');
        $data['text_disable'] = $this->language->get('text_disable');
        
        $data['column_event_type'] = $this->language->get('column_event_type');
        $data['column_url'] = $this->language->get('column_url');
        $data['column_status'] = $this->language->get('column_status');
        $data['column_date_added'] = $this->language->get('column_date_added');
        $data['column_action'] = $this->language->get('column_action');
        $data['column_message'] = $this->language->get('column_message');
        
        $data['entry_event_type'] = $this->language->get('entry_event_type');
        $data['entry_url'] = $this->language->get('entry_url');
        $data['entry_status'] = $this->language->get('entry_status');
        
        $data['button_add'] = $this->language->get('button_add');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_cancel'] = $this->language->get('button_cancel');
        
        // Header ve footer
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/pazarama_webhooks', $data));
    }
    
    /**
     * Webhook testi
     */
    public function test() {
        $this->load->language('extension/module/pazarama_webhooks');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $webhook_id = isset($this->request->post['webhook_id']) ? (int)$this->request->post['webhook_id'] : 0;
            
            if ($webhook_id) {
                $this->load->model('extension/module/pazarama_webhook');
                
                $webhook = $this->model_extension_module_pazarama_webhook->getWebhook($webhook_id);
                
                if ($webhook) {
                    // Test webhook'u çağır
                    $test_data = [
                        'event_type' => $webhook['event_type'],
                        'test' => true,
                        'timestamp' => date('Y-m-d H:i:s'),
                        'data' => [
                            'test_message' => 'Bu bir test mesajıdır'
                        ]
                    ];
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $webhook['url']);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'User-Agent: Pazarama-Webhook-Test'
                    ]);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    
                    $response = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $error = curl_error($ch);
                    curl_close($ch);
                    
                    if ($error) {
                        $json['error'] = 'CURL Error: ' . $error;
                    } elseif ($http_code >= 200 && $http_code < 300) {
                        $json['success'] = $this->language->get('text_webhook_test_success');
                        $json['response'] = $response;
                        $json['http_code'] = $http_code;
                        
                        // Test sonucunu bildirim olarak kaydet
                        $this->model_extension_module_pazarama_webhook->addNotification([
                            'type' => 'webhook_test',
                            'message' => 'Webhook test başarılı: ' . $webhook['url'],
                            'status' => 'success'
                        ]);
                    } else {
                        $json['error'] = 'HTTP Error: ' . $http_code;
                        $json['response'] = $response;
                        
                        // Hata bildirimi kaydet
                        $this->model_extension_module_pazarama_webhook->addNotification([
                            'type' => 'webhook_test',
                            'message' => 'Webhook test başarısız: ' . $webhook['url'] . ' (HTTP ' . $http_code . ')',
                            'status' => 'error'
                        ]);
                    }
                } else {
                    $json['error'] = $this->language->get('error_webhook_not_found');
                }
            } else {
                $json['error'] = $this->language->get('error_webhook_id_required');
            }
        } else {
            $json['error'] = $this->language->get('error_method_not_allowed');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Webhook loglarını getir
     */
    public function logs() {
        $this->load->language('extension/module/pazarama_webhooks');
        
        $json = array();
        
        $this->load->model('extension/module/pazarama_webhook');
        
        $logs = $this->model_extension_module_pazarama_webhook->getWebhookLogs([
            'start' => 0,
            'limit' => 50,
            'order' => 'DESC',
            'sort' => 'date_added'
        ]);
        
        $json['success'] = true;
        $json['logs'] = $logs;
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Webhook loglarını temizle
     */
    public function clearLogs() {
        $this->load->language('extension/module/pazarama_webhooks');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/pazarama_webhook');
            
            $result = $this->model_extension_module_pazarama_webhook->clearWebhookLogs();
            
            if ($result) {
                $json['success'] = $this->language->get('text_logs_cleared');
                
                // Temizleme işlemini bildirim olarak kaydet
                $this->model_extension_module_pazarama_webhook->addNotification([
                    'type' => 'logs_cleared',
                    'message' => 'Webhook logları temizlendi',
                    'status' => 'info'
                ]);
            } else {
                $json['error'] = $this->language->get('error_logs_clear_failed');
            }
        } else {
            $json['error'] = $this->language->get('error_method_not_allowed');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Webhook konfigürasyonu
     */
    public function configuration() {
        $this->load->language('extension/module/pazarama_webhooks');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            // Konfigürasyon kaydet
            $events = isset($this->request->post['events']) ? $this->request->post['events'] : [];
            
            $this->load->model('extension/module/pazarama_webhook');
            
            $result = $this->model_extension_module_pazarama_webhook->saveWebhookConfiguration($events);
            
            if ($result) {
                $json['success'] = $this->language->get('text_configuration_saved');
            } else {
                $json['error'] = $this->language->get('error_configuration_save_failed');
            }
        } else {
            // Konfigürasyon getir
            $this->load->model('extension/module/pazarama_webhook');
            
            $configuration = $this->model_extension_module_pazarama_webhook->getWebhookConfiguration();
            
            $json['success'] = true;
            $json['configuration'] = $configuration;
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Webhook URL endpoint - Pazarama'dan gelen webhook'ları işle
     */
    public function webhook() {
        $this->load->model('extension/module/pazarama_webhook');
        
        // Raw POST verilerini al
        $raw_data = file_get_contents('php://input');
        $data = json_decode($raw_data, true);
        
        // Log the incoming webhook
        $this->model_extension_module_pazarama_webhook->logWebhookEvent(
            'INCOMING_WEBHOOK',
            'Webhook received: ' . $raw_data,
            'info'
        );
        
        // Webhook güvenlik kontrolü
        $signature = isset($_SERVER['HTTP_X_PAZARAMA_SIGNATURE']) ? $_SERVER['HTTP_X_PAZARAMA_SIGNATURE'] : '';
        
        if (!$this->verifyWebhookSignature($raw_data, $signature)) {
            $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                'SECURITY_ERROR',
                'Invalid webhook signature',
                'error'
            );
            
            http_response_code(401);
            echo json_encode(['error' => 'Invalid signature']);
            return;
        }
        
        // Event türüne göre işlem yap
        if (isset($data['event_type'])) {
            switch ($data['event_type']) {
                case 'order_created':
                    $this->handleOrderCreated($data);
                    break;
                case 'order_updated':
                    $this->handleOrderUpdated($data);
                    break;
                case 'order_cancelled':
                    $this->handleOrderCancelled($data);
                    break;
                case 'product_approved':
                    $this->handleProductApproved($data);
                    break;
                case 'product_rejected':
                    $this->handleProductRejected($data);
                    break;
                case 'inventory_updated':
                    $this->handleInventoryUpdated($data);
                    break;
                case 'payment_completed':
                    $this->handlePaymentCompleted($data);
                    break;
                default:
                    $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                        'UNKNOWN_EVENT',
                        'Unknown event type: ' . $data['event_type'],
                        'warning'
                    );
            }
        }
        
        // Başarılı yanıt döndür
        http_response_code(200);
        echo json_encode(['status' => 'success']);
    }
    
    /**
     * Webhook imza doğrulama
     */
    private function verifyWebhookSignature($data, $signature) {
        $this->load->model('extension/module/pazarama');
        
        $secret = $this->model_extension_module_pazarama->getSetting('pazarama_webhook_secret');
        
        if (!$secret) {
            return false;
        }
        
        $expected_signature = hash_hmac('sha256', $data, $secret);
        
        return hash_equals($expected_signature, $signature);
    }
    
    /**
     * Yeni sipariş oluşturuldu
     */
    private function handleOrderCreated($data) {
        $this->load->model('extension/module/pazarama');
        
        try {
            $order_data = $data['data'];
            
            // Siparişi sisteme kaydet
            $result = $this->model_extension_module_pazarama->createOrderFromWebhook($order_data);
            
            if ($result) {
                $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                    'ORDER_CREATED',
                    'Order created successfully: ' . $order_data['order_id'],
                    'success'
                );
                
                // Bildirim ekle
                $this->model_extension_module_pazarama_webhook->addNotification([
                    'type' => 'order_created',
                    'message' => 'Yeni sipariş oluşturuldu: #' . $order_data['order_id'],
                    'status' => 'success'
                ]);
            } else {
                throw new Exception('Failed to create order');
            }
            
        } catch (Exception $e) {
            $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                'ORDER_CREATE_ERROR',
                'Failed to create order: ' . $e->getMessage(),
                'error'
            );
        }
    }
    
    /**
     * Sipariş güncellendi
     */
    private function handleOrderUpdated($data) {
        $this->load->model('extension/module/pazarama');
        
        try {
            $order_data = $data['data'];
            
            // Siparişi güncelle
            $result = $this->model_extension_module_pazarama->updateOrderFromWebhook($order_data);
            
            if ($result) {
                $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                    'ORDER_UPDATED',
                    'Order updated successfully: ' . $order_data['order_id'],
                    'success'
                );
                
                // Bildirim ekle
                $this->model_extension_module_pazarama_webhook->addNotification([
                    'type' => 'order_updated',
                    'message' => 'Sipariş güncellendi: #' . $order_data['order_id'],
                    'status' => 'info'
                ]);
            } else {
                throw new Exception('Failed to update order');
            }
            
        } catch (Exception $e) {
            $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                'ORDER_UPDATE_ERROR',
                'Failed to update order: ' . $e->getMessage(),
                'error'
            );
        }
    }
    
    /**
     * Sipariş iptal edildi
     */
    private function handleOrderCancelled($data) {
        $this->load->model('extension/module/pazarama');
        
        try {
            $order_data = $data['data'];
            
            // Siparişi iptal et
            $result = $this->model_extension_module_pazarama->cancelOrderFromWebhook($order_data);
            
            if ($result) {
                $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                    'ORDER_CANCELLED',
                    'Order cancelled successfully: ' . $order_data['order_id'],
                    'success'
                );
                
                // Bildirim ekle
                $this->model_extension_module_pazarama_webhook->addNotification([
                    'type' => 'order_cancelled',
                    'message' => 'Sipariş iptal edildi: #' . $order_data['order_id'],
                    'status' => 'warning'
                ]);
            } else {
                throw new Exception('Failed to cancel order');
            }
            
        } catch (Exception $e) {
            $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                'ORDER_CANCEL_ERROR',
                'Failed to cancel order: ' . $e->getMessage(),
                'error'
            );
        }
    }
    
    /**
     * Ürün onaylandı
     */
    private function handleProductApproved($data) {
        $this->load->model('extension/module/pazarama');
        
        try {
            $product_data = $data['data'];
            
            // Ürün durumunu güncelle
            $result = $this->model_extension_module_pazarama->updateProductStatusFromWebhook($product_data, 'approved');
            
            if ($result) {
                $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                    'PRODUCT_APPROVED',
                    'Product approved: ' . $product_data['product_id'],
                    'success'
                );
                
                // Bildirim ekle
                $this->model_extension_module_pazarama_webhook->addNotification([
                    'type' => 'product_approved',
                    'message' => 'Ürün onaylandı: ' . $product_data['product_name'],
                    'status' => 'success'
                ]);
            } else {
                throw new Exception('Failed to update product status');
            }
            
        } catch (Exception $e) {
            $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                'PRODUCT_APPROVE_ERROR',
                'Failed to approve product: ' . $e->getMessage(),
                'error'
            );
        }
    }
    
    /**
     * Ürün reddedildi
     */
    private function handleProductRejected($data) {
        $this->load->model('extension/module/pazarama');
        
        try {
            $product_data = $data['data'];
            
            // Ürün durumunu güncelle
            $result = $this->model_extension_module_pazarama->updateProductStatusFromWebhook($product_data, 'rejected');
            
            if ($result) {
                $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                    'PRODUCT_REJECTED',
                    'Product rejected: ' . $product_data['product_id'],
                    'warning'
                );
                
                // Bildirim ekle
                $this->model_extension_module_pazarama_webhook->addNotification([
                    'type' => 'product_rejected',
                    'message' => 'Ürün reddedildi: ' . $product_data['product_name'] . ' - Sebep: ' . ($product_data['rejection_reason'] ?? 'Belirtilmemiş'),
                    'status' => 'error'
                ]);
            } else {
                throw new Exception('Failed to update product status');
            }
            
        } catch (Exception $e) {
            $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                'PRODUCT_REJECT_ERROR',
                'Failed to reject product: ' . $e->getMessage(),
                'error'
            );
        }
    }
    
    /**
     * Stok güncellendi
     */
    private function handleInventoryUpdated($data) {
        $this->load->model('extension/module/pazarama');
        
        try {
            $inventory_data = $data['data'];
            
            // Stok bilgilerini güncelle
            $result = $this->model_extension_module_pazarama->updateInventoryFromWebhook($inventory_data);
            
            if ($result) {
                $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                    'INVENTORY_UPDATED',
                    'Inventory updated for product: ' . $inventory_data['product_id'],
                    'success'
                );
                
                // Bildirim ekle
                $this->model_extension_module_pazarama_webhook->addNotification([
                    'type' => 'inventory_updated',
                    'message' => 'Stok güncellendi - Ürün: ' . $inventory_data['product_name'] . ', Yeni stok: ' . $inventory_data['quantity'],
                    'status' => 'info'
                ]);
            } else {
                throw new Exception('Failed to update inventory');
            }
            
        } catch (Exception $e) {
            $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                'INVENTORY_UPDATE_ERROR',
                'Failed to update inventory: ' . $e->getMessage(),
                'error'
            );
        }
    }
    
    /**
     * Ödeme tamamlandı
     */
    private function handlePaymentCompleted($data) {
        $this->load->model('extension/module/pazarama');
        
        try {
            $payment_data = $data['data'];
            
            // Ödeme durumunu güncelle
            $result = $this->model_extension_module_pazarama->updatePaymentStatusFromWebhook($payment_data);
            
            if ($result) {
                $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                    'PAYMENT_COMPLETED',
                    'Payment completed for order: ' . $payment_data['order_id'],
                    'success'
                );
                
                // Bildirim ekle
                $this->model_extension_module_pazarama_webhook->addNotification([
                    'type' => 'payment_completed',
                    'message' => 'Ödeme tamamlandı - Sipariş: #' . $payment_data['order_id'] . ', Tutar: ' . $payment_data['amount'] . ' TL',
                    'status' => 'success'
                ]);
            } else {
                throw new Exception('Failed to update payment status');
            }
            
        } catch (Exception $e) {
            $this->model_extension_module_pazarama_webhook->logWebhookEvent(
                'PAYMENT_ERROR',
                'Failed to process payment: ' . $e->getMessage(),
                'error'
            );
        }
    }
}
?>
