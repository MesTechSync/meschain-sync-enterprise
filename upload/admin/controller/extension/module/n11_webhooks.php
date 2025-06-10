<?php
/**
 * MesChain-Sync N11 Webhook Controller
 * N11 Marketplace Webhook Management
 * 
 * @author MesChain Team
 * @version 2.0
 * @date 2025-06-10
 * @license Commercial License
 */

class ControllerExtensionModuleN11Webhooks extends Controller {
    
    private $error = array();
    
    /**
     * N11 Webhook management index page
     */
    public function index() {
        $this->load->language('extension/module/n11');
        
        $this->document->setTitle($this->language->get('heading_title') . ' - N11 Webhooks');
        
        $this->load->model('extension/module/n11_webhook');
        
        // Get webhook list
        $data['webhooks'] = $this->model_extension_module_n11_webhook->getWebhooks();
        
        // Get notifications
        $data['notifications'] = $this->model_extension_module_n11_webhook->getNotifications(array(
            'start' => 0,
            'limit' => 10,
            'order' => 'DESC',
            'sort' => 'date_added'
        ));
        
        // Create new webhook
        if (isset($this->request->post['event_type']) && isset($this->request->post['url'])) {
            $webhook_data = array(
                'event_type' => $this->request->post['event_type'],
                'url' => $this->request->post['url'],
                'status' => isset($this->request->post['status']) ? (int)$this->request->post['status'] : 1,
                'secret_key' => $this->generateSecretKey()
            );
            
            $webhook_id = $this->model_extension_module_n11_webhook->addWebhook($webhook_data);
            
            if ($webhook_id) {
                // Register webhook with N11
                $this->registerWebhookWithN11($webhook_data);
                
                $this->session->data['success'] = 'N11 Webhook başarıyla oluşturuldu';
            } else {
                $this->session->data['error'] = 'Webhook oluşturulurken hata oluştu';
            }
            
            $this->response->redirect($this->url->link('extension/module/n11_webhooks', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // N11 webhook event types
        $data['event_types'] = array(
            'order-created' => 'Sipariş Oluşturuldu',
            'order-updated' => 'Sipariş Güncellendi',
            'order-cancelled' => 'Sipariş İptal Edildi',
            'product-created' => 'Ürün Oluşturuldu',
            'product-updated' => 'Ürün Güncellendi',
            'product-deleted' => 'Ürün Silindi',
            'stock-updated' => 'Stok Güncellendi',
            'price-updated' => 'Fiyat Güncellendi',
            'commission-updated' => 'Komisyon Güncellendi'
        );
        
        // Store webhook URL
        $data['store_webhook_url'] = HTTPS_CATALOG . 'index.php?route=extension/module/n11_webhook_receiver';
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'N11 Modülü',
            'href' => $this->url->link('extension/module/n11', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'N11 Webhooks',
            'href' => $this->url->link('extension/module/n11_webhooks', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // URLs
        $data['add_webhook_url'] = $this->url->link('extension/module/n11_webhooks', 'user_token=' . $this->session->data['user_token'], true);
        $data['back_url'] = $this->url->link('extension/module/n11', 'user_token=' . $this->session->data['user_token'], true);
        
        // Messages
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        if (isset($this->session->data['error'])) {
            $data['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $data['error_warning'] = '';
        }
        
        // Webhook statistics
        $data['webhook_stats'] = $this->model_extension_module_n11_webhook->getWebhookStatistics();
        
        // Load view
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/n11_webhooks', $data));
    }
    
    /**
     * Process incoming webhook from N11
     */
    public function receive() {
        $this->load->model('extension/module/n11_webhook');
        $this->load->library('meschain/helper/n11');
        
        $json = array('success' => false, 'message' => '');
        
        try {
            // Get raw POST data
            $raw_data = file_get_contents('php://input');
            $webhook_data = json_decode($raw_data, true);
            
            if (!$webhook_data) {
                throw new Exception('Invalid webhook data received');
            }
            
            // Validate webhook signature
            $signature = $_SERVER['HTTP_X_N11_SIGNATURE'] ?? '';
            if (!$this->validateWebhookSignature($raw_data, $signature)) {
                throw new Exception('Invalid webhook signature');
            }
            
            // Log incoming webhook
            $this->model_extension_module_n11_webhook->logNotification(array(
                'event_type' => $webhook_data['eventType'] ?? 'unknown',
                'data' => json_encode($webhook_data),
                'status' => 'received',
                'ip_address' => $this->request->server['REMOTE_ADDR']
            ));
            
            // Process webhook based on event type
            $result = $this->processWebhookByType($webhook_data);
            
            if ($result['success']) {
                $json['success'] = true;
                $json['message'] = 'Webhook processed successfully';
                
                // Update notification status
                $this->model_extension_module_n11_webhook->updateLastNotificationStatus('processed');
            } else {
                throw new Exception($result['error']);
            }
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['message'] = $e->getMessage();
            
            // Log error
            $this->log->write('N11 Webhook Error: ' . $e->getMessage());
            
            // Update notification status
            if (isset($webhook_data)) {
                $this->model_extension_module_n11_webhook->updateLastNotificationStatus('failed');
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Process webhook based on event type
     * 
     * @param array $webhook_data Webhook data
     * @return array
     */
    private function processWebhookByType($webhook_data) {
        $event_type = $webhook_data['eventType'] ?? '';
        
        switch ($event_type) {
            case 'order-created':
                return $this->processOrderCreated($webhook_data);
                
            case 'order-updated':
                return $this->processOrderUpdated($webhook_data);
                
            case 'order-cancelled':
                return $this->processOrderCancelled($webhook_data);
                
            case 'product-updated':
                return $this->processProductUpdated($webhook_data);
                
            case 'stock-updated':
                return $this->processStockUpdated($webhook_data);
                
            case 'price-updated':
                return $this->processPriceUpdated($webhook_data);
                
            default:
                return array('success' => true, 'message' => 'Event type not handled: ' . $event_type);
        }
    }
    
    /**
     * Process order created webhook
     * 
     * @param array $webhook_data Webhook data
     * @return array
     */
    private function processOrderCreated($webhook_data) {
        try {
            $this->load->model('extension/module/n11');
            
            $order_data = $webhook_data['data'] ?? array();
            $n11_order_id = $order_data['orderId'] ?? '';
            
            if (empty($n11_order_id)) {
                throw new Exception('Order ID missing in webhook data');
            }
            
            // Import order from N11
            $result = $this->model_extension_module_n11->importOrder($n11_order_id);
            
            if ($result['success']) {
                return array('success' => true, 'message' => 'Order imported successfully');
            } else {
                throw new Exception($result['error']);
            }
            
        } catch (Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }
    }
    
    /**
     * Process order updated webhook
     * 
     * @param array $webhook_data Webhook data
     * @return array
     */
    private function processOrderUpdated($webhook_data) {
        try {
            $this->load->model('extension/module/n11');
            
            $order_data = $webhook_data['data'] ?? array();
            $n11_order_id = $order_data['orderId'] ?? '';
            
            if (empty($n11_order_id)) {
                throw new Exception('Order ID missing in webhook data');
            }
            
            // Update order from N11
            $result = $this->model_extension_module_n11->updateOrderFromN11($n11_order_id);
            
            if ($result['success']) {
                return array('success' => true, 'message' => 'Order updated successfully');
            } else {
                throw new Exception($result['error']);
            }
            
        } catch (Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }
    }
    
    /**
     * Register webhook with N11
     * 
     * @param array $webhook_data Webhook configuration
     * @return bool
     */
    private function registerWebhookWithN11($webhook_data) {
        try {
            $this->load->library('meschain/helper/n11');
            
            $n11_webhook_data = array(
                'url' => $webhook_data['url'],
                'events' => array($webhook_data['event_type']),
                'active' => true
            );
            
            $result = $this->n11->registerWebhook($n11_webhook_data);
            
            if ($result['success']) {
                // Save N11 webhook ID
                $this->model_extension_module_n11_webhook->updateWebhookN11Id(
                    $webhook_data['id'], 
                    $result['webhook_id']
                );
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->log->write('N11 Webhook Registration Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate webhook signature
     * 
     * @param string $raw_data Raw webhook data
     * @param string $signature Received signature
     * @return bool
     */
    private function validateWebhookSignature($raw_data, $signature) {
        if (empty($signature)) {
            return false;
        }
        
        // Get webhook secret from settings
        $secret = $this->config->get('module_n11_webhook_secret');
        if (empty($secret)) {
            return false;
        }
        
        $expected_signature = hash_hmac('sha256', $raw_data, $secret);
        
        return hash_equals($expected_signature, $signature);
    }
    
    /**
     * Generate secret key for webhook security
     * 
     * @return string
     */
    private function generateSecretKey() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Install webhook tables
     */
    public function install() {
        $this->load->model('extension/module/n11_webhook');
        $this->model_extension_module_n11_webhook->install();
    }
    
    /**
     * Uninstall webhook tables
     */
    public function uninstall() {
        $this->load->model('extension/module/n11_webhook');
        $this->model_extension_module_n11_webhook->uninstall();
    }
}
