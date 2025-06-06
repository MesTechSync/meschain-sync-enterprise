<?php
/**
 * Dropshipping Automation Controller
 * 
 * @package    MesChain-Sync
 * @author     MezBjen Team
 * @copyright  2024 MesChain
 * @version    1.0.0
 */

class ControllerExtensionModuleDropshippingAutomation extends Controller {
    
    private $error = array();
    
    /**
     * Ana sayfa - Otomasyon dashboard
     */
    public function index() {
        $this->load->language('extension/module/dropshipping');
        $this->document->setTitle($this->language->get('heading_title') . ' - Automation');
        
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => 'Dropshipping Automation',
            'href' => $this->url->link('extension/module/dropshipping_automation', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Otomasyon istatistikleri
        $this->load->model('extension/module/dropshipping');
        $data['stats'] = $this->getAutomationStats();
        
        // Aktif kurallar
        $data['rules'] = $this->getAutomationRules();
        
        // Son işlemler
        $data['recent_activities'] = $this->getRecentActivities();
        
        // Tedarikçi performansı
        $data['supplier_performance'] = $this->getSupplierPerformance();
        
        $data['user_token'] = $this->session->data['user_token'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/dropshipping_automation', $data));
    }
    
    /**
     * Otomatik sipariş işleme
     */
    public function processAutoOrders() {
        $this->load->model('extension/module/dropshipping');
        $this->load->model('sale/order');
        
        $json = array();
        
        try {
            // Otomatik sipariş özelliği aktif mi?
            if (!$this->config->get('module_dropshipping_auto_order')) {
                throw new Exception('Auto order processing is disabled');
            }
            
            // İşlenmeyi bekleyen siparişleri al
            $pending_orders = $this->model_extension_module_dropshipping->getPendingDropshipOrders();
            
            $processed = 0;
            $errors = array();
            
            foreach ($pending_orders as $order) {
                try {
                    $result = $this->processDropshipOrder($order);
                    if ($result['success']) {
                        $processed++;
                        
                        // Sipariş durumunu güncelle
                        $this->model_extension_module_dropshipping->updateDropshipOrderStatus(
                            $order['order_id'],
                            'processing',
                            $result['supplier_order_id']
                        );
                        
                        // OpenCart sipariş durumunu güncelle
                        $this->model_sale_order->addOrderHistory(
                            $order['order_id'],
                            $this->config->get('module_dropshipping_processing_status_id') ?: 2,
                            'Dropship order sent to supplier: ' . $order['supplier_name'],
                            true
                        );
                    } else {
                        $errors[] = 'Order #' . $order['order_id'] . ': ' . $result['error'];
                    }
                } catch (Exception $e) {
                    $errors[] = 'Order #' . $order['order_id'] . ': ' . $e->getMessage();
                }
                
                // Rate limiting
                usleep(500000); // 500ms delay
            }
            
            $json['success'] = true;
            $json['message'] = sprintf('%d orders processed successfully', $processed);
            
            if (!empty($errors)) {
                $json['warnings'] = $errors;
            }
            
            // Log işlemi
            $this->log('AUTO_ORDER_PROCESS', sprintf('Processed %d orders', $processed));
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            
            $this->log('AUTO_ORDER_ERROR', $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Tek bir dropship siparişini işle
     */
    private function processDropshipOrder($order) {
        $this->load->library('meschain/helper/dropshipping/' . $order['supplier_name']);
        
        $helper_class = 'MesChain' . ucfirst($order['supplier_name']) . 'DropshipHelper';
        
        if (!class_exists($helper_class)) {
            return array(
                'success' => false,
                'error' => 'Supplier helper not found: ' . $order['supplier_name']
            );
        }
        
        $helper = new $helper_class($this->registry);
        
        // Tedarikçi API bilgilerini al
        $supplier = $this->model_extension_module_dropshipping->getSupplier($order['supplier_id']);
        
        if (!$supplier) {
            return array(
                'success' => false,
                'error' => 'Supplier not found'
            );
        }
        
        // Sipariş detaylarını hazırla
        $order_data = $this->prepareSupplierOrderData($order);
        
        // Tedarikçiye siparişi gönder
        return $helper->createOrder($order_data, $supplier);
    }
    
    /**
     * Tedarikçi siparişi için veri hazırla
     */
    private function prepareSupplierOrderData($order) {
        $this->load->model('sale/order');
        
        // OpenCart sipariş bilgilerini al
        $oc_order = $this->model_sale_order->getOrder($order['order_id']);
        $oc_products = $this->model_sale_order->getOrderProducts($order['order_id']);
        
        $products = array();
        foreach ($oc_products as $product) {
            // Dropship ürün eşleşmesini kontrol et
            $dropship_product = $this->model_extension_module_dropshipping->getDropshipProduct(
                $product['product_id'],
                $order['supplier_id']
            );
            
            if ($dropship_product) {
                $products[] = array(
                    'supplier_product_id' => $dropship_product['supplier_product_id'],
                    'quantity' => $product['quantity'],
                    'price' => $dropship_product['supplier_price'],
                    'name' => $product['name'],
                    'model' => $product['model']
                );
            }
        }
        
        return array(
            'order_id' => $order['order_id'],
            'customer' => array(
                'firstname' => $oc_order['shipping_firstname'],
                'lastname' => $oc_order['shipping_lastname'],
                'email' => $oc_order['email'],
                'telephone' => $oc_order['telephone']
            ),
            'shipping_address' => array(
                'address_1' => $oc_order['shipping_address_1'],
                'address_2' => $oc_order['shipping_address_2'],
                'city' => $oc_order['shipping_city'],
                'postcode' => $oc_order['shipping_postcode'],
                'zone' => $oc_order['shipping_zone'],
                'country' => $oc_order['shipping_country']
            ),
            'products' => $products,
            'shipping_method' => $oc_order['shipping_method'],
            'comment' => $oc_order['comment']
        );
    }
    
    /**
     * Stok senkronizasyonu
     */
    public function syncInventory() {
        $this->load->model('extension/module/dropshipping');
        
        $json = array();
        
        try {
            $supplier_id = isset($this->request->post['supplier_id']) ? (int)$this->request->post['supplier_id'] : 0;
            
            if ($supplier_id) {
                // Tek tedarikçi senkronizasyonu
                $result = $this->syncSupplierInventory($supplier_id);
                $json = $result;
            } else {
                // Tüm tedarikçiler için senkronizasyon
                $suppliers = $this->model_extension_module_dropshipping->getActiveSuppliers();
                $total_synced = 0;
                $errors = array();
                
                foreach ($suppliers as $supplier) {
                    $result = $this->syncSupplierInventory($supplier['supplier_id']);
                    if ($result['success']) {
                        $total_synced += $result['synced_count'];
                    } else {
                        $errors[] = $supplier['supplier_name'] . ': ' . $result['error'];
                    }
                }
                
                $json['success'] = true;
                $json['message'] = sprintf('%d products synced', $total_synced);
                
                if (!empty($errors)) {
                    $json['warnings'] = $errors;
                }
            }
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Tek tedarikçi için stok senkronizasyonu
     */
    private function syncSupplierInventory($supplier_id) {
        $supplier = $this->model_extension_module_dropshipping->getSupplier($supplier_id);
        
        if (!$supplier) {
            return array(
                'success' => false,
                'error' => 'Supplier not found'
            );
        }
        
        $this->load->library('meschain/helper/dropshipping/' . $supplier['supplier_name']);
        $helper_class = 'MesChain' . ucfirst($supplier['supplier_name']) . 'DropshipHelper';
        
        if (!class_exists($helper_class)) {
            return array(
                'success' => false,
                'error' => 'Supplier helper not found'
            );
        }
        
        $helper = new $helper_class($this->registry);
        
        // Dropship ürünlerini al
        $products = $this->model_extension_module_dropshipping->getSupplierProducts($supplier_id);
        $synced = 0;
        
        foreach ($products as $product) {
            try {
                // Tedarikçiden stok bilgisini al
                $stock_info = $helper->getProductStock($product['supplier_product_id'], $supplier);
                
                if ($stock_info['success']) {
                    // OpenCart stok güncelle
                    $this->model_extension_module_dropshipping->updateProductStock(
                        $product['opencart_product_id'],
                        $stock_info['quantity'],
                        $stock_info['price'] ?? null
                    );
                    $synced++;
                }
            } catch (Exception $e) {
                // Hata logla ama devam et
                $this->log('INVENTORY_SYNC_ERROR', $e->getMessage());
            }
        }
        
        return array(
            'success' => true,
            'synced_count' => $synced
        );
    }
    
    /**
     * Fiyat kuralları yönetimi
     */
    public function pricingRules() {
        $this->load->language('extension/module/dropshipping');
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $json = array();
            
            try {
                $rules = isset($this->request->post['rules']) ? $this->request->post['rules'] : array();
                
                $this->load->model('extension/module/dropshipping');
                $this->model_extension_module_dropshipping->savePricingRules($rules);
                
                $json['success'] = true;
                $json['message'] = 'Pricing rules saved successfully';
                
            } catch (Exception $e) {
                $json['success'] = false;
                $json['error'] = $e->getMessage();
            }
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }
        
        // GET request - show pricing rules page
        $data['rules'] = $this->model_extension_module_dropshipping->getPricingRules();
        $data['suppliers'] = $this->model_extension_module_dropshipping->getActiveSuppliers();
        
        $this->response->setOutput($this->load->view('extension/module/dropshipping_pricing_rules', $data));
    }
    
    /**
     * Sipariş takibi güncelleme
     */
    public function updateTracking() {
        $this->load->model('extension/module/dropshipping');
        $this->load->model('sale/order');
        
        $json = array();
        
        try {
            // Takip bilgisi olan siparişleri al
            $orders = $this->model_extension_module_dropshipping->getOrdersForTrackingUpdate();
            
            $updated = 0;
            foreach ($orders as $order) {
                $tracking = $this->getSupplierTracking($order);
                
                if ($tracking['success']) {
                    // Takip bilgisini güncelle
                    $this->model_extension_module_dropshipping->updateOrderTracking(
                        $order['order_id'],
                        $tracking['tracking_number'],
                        $tracking['carrier'],
                        $tracking['status']
                    );
                    
                    // Müşteriye bildirim gönder
                    if ($tracking['status'] == 'shipped' && !$order['tracking_notified']) {
                        $this->notifyCustomerTracking($order['order_id'], $tracking);
                    }
                    
                    $updated++;
                }
            }
            
            $json['success'] = true;
            $json['message'] = sprintf('%d tracking information updated', $updated);
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Tedarikçiden takip bilgisi al
     */
    private function getSupplierTracking($order) {
        $supplier = $this->model_extension_module_dropshipping->getSupplier($order['supplier_id']);
        
        $this->load->library('meschain/helper/dropshipping/' . $supplier['supplier_name']);
        $helper_class = 'MesChain' . ucfirst($supplier['supplier_name']) . 'DropshipHelper';
        
        if (!class_exists($helper_class)) {
            return array('success' => false);
        }
        
        $helper = new $helper_class($this->registry);
        
        return $helper->getOrderTracking($order['supplier_order_id'], $supplier);
    }
    
    /**
     * Müşteriye takip bildirimi gönder
     */
    private function notifyCustomerTracking($order_id, $tracking) {
        $this->load->model('sale/order');
        $order = $this->model_sale_order->getOrder($order_id);
        
        if ($order) {
            // E-posta gönder
            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
            
            $mail->setTo($order['email']);
            $mail->setFrom($this->config->get('config_email'));
            $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
            $mail->setSubject('Your order has been shipped - Order #' . $order_id);
            
            $message = "Dear " . $order['firstname'] . ",\n\n";
            $message .= "Your order #" . $order_id . " has been shipped!\n\n";
            $message .= "Tracking Number: " . $tracking['tracking_number'] . "\n";
            $message .= "Carrier: " . $tracking['carrier'] . "\n\n";
            $message .= "You can track your shipment using the tracking number above.\n\n";
            $message .= "Thank you for your order!\n";
            
            $mail->setText($message);
            $mail->send();
            
            // Bildirim gönderildi olarak işaretle
            $this->model_extension_module_dropshipping->markTrackingNotified($order_id);
        }
    }
    
    /**
     * Otomasyon istatistikleri
     */
    private function getAutomationStats() {
        $this->load->model('extension/module/dropshipping');
        
        return array(
            'total_automated_orders' => $this->model_extension_module_dropshipping->getTotalAutomatedOrders(),
            'pending_orders' => $this->model_extension_module_dropshipping->getTotalPendingOrders(),
            'processing_orders' => $this->model_extension_module_dropshipping->getTotalProcessingOrders(),
            'completed_orders' => $this->model_extension_module_dropshipping->getTotalCompletedOrders(),
            'failed_orders' => $this->model_extension_module_dropshipping->getTotalFailedOrders(),
            'total_revenue' => $this->model_extension_module_dropshipping->getTotalDropshipRevenue(),
            'total_profit' => $this->model_extension_module_dropshipping->getTotalDropshipProfit(),
            'average_processing_time' => $this->model_extension_module_dropshipping->getAverageProcessingTime()
        );
    }
    
    /**
     * Otomasyon kuralları
     */
    private function getAutomationRules() {
        $this->load->model('extension/module/dropshipping');
        
        return $this->model_extension_module_dropshipping->getAutomationRules();
    }
    
    /**
     * Son aktiviteler
     */
    private function getRecentActivities() {
        $this->load->model('extension/module/dropshipping');
        
        return $this->model_extension_module_dropshipping->getRecentActivities(20);
    }
    
    /**
     * Tedarikçi performansı
     */
    private function getSupplierPerformance() {
        $this->load->model('extension/module/dropshipping');
        
        $suppliers = $this->model_extension_module_dropshipping->getActiveSuppliers();
        $performance = array();
        
        foreach ($suppliers as $supplier) {
            $performance[] = array(
                'supplier_name' => $supplier['supplier_name'],
                'total_orders' => $this->model_extension_module_dropshipping->getSupplierOrderCount($supplier['supplier_id']),
                'success_rate' => $this->model_extension_module_dropshipping->getSupplierSuccessRate($supplier['supplier_id']),
                'average_processing_time' => $this->model_extension_module_dropshipping->getSupplierAvgProcessingTime($supplier['supplier_id']),
                'total_revenue' => $this->model_extension_module_dropshipping->getSupplierRevenue($supplier['supplier_id'])
            );
        }
        
        return $performance;
    }
    
    /**
     * Log kaydı
     */
    private function log($action, $message) {
        $log_file = DIR_LOGS . 'dropshipping_automation.log';
        $date = date('Y-m-d H:i:s');
        $user = $this->user->getUserName();
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
    
    /**
     * Doğrulama
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/dropshipping_automation')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        return !$this->error;
    }
} 