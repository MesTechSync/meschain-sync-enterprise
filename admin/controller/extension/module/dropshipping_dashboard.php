<?php
/**
 * Dropshipping Dashboard Controller
 * Dropshipping yönetimi ve takip sistemi
 */
class ControllerExtensionModuleDropshippingDashboard extends Controller {
    private $error = array();
    
    public function index() {
        $this->load->language('extension/module/dropshipping');
        $this->load->model('extension/module/dropshipping');
        
        $this->document->setTitle('Dropshipping Dashboard');
        
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => 'Ana Sayfa',
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Dropshipping Dashboard',
            'href' => $this->url->link('extension/module/dropshipping_dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Dashboard istatistikleri
        $data['stats'] = $this->model_extension_module_dropshipping->getDashboardStats();
        
        // Aktif siparişler
        $data['active_orders'] = $this->model_extension_module_dropshipping->getActiveOrders();
        
        // Tedarikçi performansı
        $data['supplier_performance'] = $this->model_extension_module_dropshipping->getSupplierPerformance();
        
        // Son aktiviteler
        $data['recent_activities'] = $this->model_extension_module_dropshipping->getRecentActivities();
        
        // Tedarikçi listesi
        $data['suppliers'] = $this->model_extension_module_dropshipping->getSuppliers();
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/dropshipping_dashboard', $data));
    }
    
    /**
     * Otomatik sipariş işleme
     */
    public function processOrders() {
        $this->load->model('extension/module/dropshipping');
        
        $json = array();
        
        try {
            $result = $this->model_extension_module_dropshipping->processAutomaticOrders();
            
            $json['success'] = 'Otomatik sipariş işleme tamamlandı';
            $json['processed'] = $result['processed'];
            $json['failed'] = $result['failed'];
            
        } catch (Exception $e) {
            $json['error'] = 'Sipariş işleme hatası: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Sipariş durumu güncelle
     */
    public function updateOrderStatus() {
        $this->load->model('extension/module/dropshipping');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $orderId = $this->request->post['order_id'] ?? '';
                $status = $this->request->post['status'] ?? '';
                $trackingNumber = $this->request->post['tracking_number'] ?? '';
                
                $result = $this->model_extension_module_dropshipping->updateOrderStatus($orderId, $status, $trackingNumber);
                
                if ($result) {
                    $json['success'] = 'Sipariş durumu güncellendi';
                } else {
                    $json['error'] = 'Sipariş durumu güncellenemedi';
                }
                
            } catch (Exception $e) {
                $json['error'] = 'Güncelleme hatası: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Tedarikçi ekle/düzenle
     */
    public function saveSupplier() {
        $this->load->model('extension/module/dropshipping');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $supplierData = [
                    'supplier_id' => $this->request->post['supplier_id'] ?? '',
                    'name' => $this->request->post['name'] ?? '',
                    'marketplace' => $this->request->post['marketplace'] ?? '',
                    'api_key' => $this->request->post['api_key'] ?? '',
                    'api_secret' => $this->request->post['api_secret'] ?? '',
                    'commission_rate' => $this->request->post['commission_rate'] ?? 0,
                    'auto_order' => isset($this->request->post['auto_order']) ? 1 : 0,
                    'status' => isset($this->request->post['status']) ? 1 : 0,
                ];
                
                $result = $this->model_extension_module_dropshipping->saveSupplier($supplierData);
                
                if ($result) {
                    $json['success'] = 'Tedarikçi başarıyla kaydedildi';
                } else {
                    $json['error'] = 'Tedarikçi kaydedilemedi';
                }
                
            } catch (Exception $e) {
                $json['error'] = 'Kaydetme hatası: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Tedarikçi sil
     */
    public function deleteSupplier() {
        $this->load->model('extension/module/dropshipping');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $supplierId = $this->request->post['supplier_id'] ?? '';
                
                $result = $this->model_extension_module_dropshipping->deleteSupplier($supplierId);
                
                if ($result) {
                    $json['success'] = 'Tedarikçi silindi';
                } else {
                    $json['error'] = 'Tedarikçi silinemedi';
                }
                
            } catch (Exception $e) {
                $json['error'] = 'Silme hatası: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Stok senkronizasyonu
     */
    public function syncStock() {
        $this->load->model('extension/module/dropshipping');
        
        $json = array();
        
        try {
            $result = $this->model_extension_module_dropshipping->syncAllStock();
            
            $json['success'] = 'Stok senkronizasyonu tamamlandı';
            $json['updated'] = $result['updated'];
            $json['failed'] = $result['failed'];
            
        } catch (Exception $e) {
            $json['error'] = 'Stok senkronizasyon hatası: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Fiyat senkronizasyonu
     */
    public function syncPrices() {
        $this->load->model('extension/module/dropshipping');
        
        $json = array();
        
        try {
            $result = $this->model_extension_module_dropshipping->syncAllPrices();
            
            $json['success'] = 'Fiyat senkronizasyonu tamamlandı';
            $json['updated'] = $result['updated'];
            $json['failed'] = $result['failed'];
            
        } catch (Exception $e) {
            $json['error'] = 'Fiyat senkronizasyon hatası: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Sipariş detayları
     */
    public function getOrderDetails() {
        $this->load->model('extension/module/dropshipping');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'GET') {
            try {
                $orderId = $this->request->get['order_id'] ?? '';
                
                $orderDetails = $this->model_extension_module_dropshipping->getOrderDetails($orderId);
                
                if ($orderDetails) {
                    $json['success'] = true;
                    $json['data'] = $orderDetails;
                } else {
                    $json['error'] = 'Sipariş bulunamadı';
                }
                
            } catch (Exception $e) {
                $json['error'] = 'Sipariş detay hatası: ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Dashboard verilerini yenile
     */
    public function refreshDashboard() {
        $this->load->model('extension/module/dropshipping');
        
        $json = array();
        
        try {
            $json['stats'] = $this->model_extension_module_dropshipping->getDashboardStats();
            $json['active_orders'] = $this->model_extension_module_dropshipping->getActiveOrders();
            $json['supplier_performance'] = $this->model_extension_module_dropshipping->getSupplierPerformance();
            $json['recent_activities'] = $this->model_extension_module_dropshipping->getRecentActivities();
            $json['success'] = true;
            
        } catch (Exception $e) {
            $json['error'] = 'Dashboard yenileme hatası: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
} 