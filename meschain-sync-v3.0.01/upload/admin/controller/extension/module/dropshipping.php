<?php
/**
 * dropshipping.php
 *
 * Amaç: Dropshipping özelliği için OpenCart yönetici paneli controller dosyası.
 *
 * Loglama: Tüm önemli işlemler ve hatalar dropshipping.log dosyasına kaydedilir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 */

class ControllerExtensionModuleDropshipping extends Controller {
    private $error = array();
    
    /**
     * Ana sayfa
     */
    public function index() {
        $this->load->language('extension/module/dropshipping');
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/module/dropshipping');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_extension_module_dropshipping->saveSettings($this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module/dropshipping', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_auto_order'] = $this->language->get('entry_auto_order');
        $data['entry_price_markup'] = $this->language->get('entry_price_markup');
        $data['entry_default_supplier'] = $this->language->get('entry_default_supplier');
        
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
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
            'href' => $this->url->link('extension/module/dropshipping', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['action'] = $this->url->link('extension/module/dropshipping', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        // Ayarları yükle
        $settings = $this->model_extension_module_dropshipping->getSettings();
        
        if (isset($this->request->post['dropshipping_status'])) {
            $data['dropshipping_status'] = $this->request->post['dropshipping_status'];
        } else {
            $data['dropshipping_status'] = $settings['status'] ?? 0;
        }
        
        if (isset($this->request->post['dropshipping_auto_order'])) {
            $data['dropshipping_auto_order'] = $this->request->post['dropshipping_auto_order'];
        } else {
            $data['dropshipping_auto_order'] = $settings['auto_order'] ?? 0;
        }
        
        if (isset($this->request->post['dropshipping_price_markup'])) {
            $data['dropshipping_price_markup'] = $this->request->post['dropshipping_price_markup'];
        } else {
            $data['dropshipping_price_markup'] = $settings['price_markup'] ?? 20;
        }
        
        if (isset($this->request->post['dropshipping_default_supplier'])) {
            $data['dropshipping_default_supplier'] = $this->request->post['dropshipping_default_supplier'];
        } else {
            $data['dropshipping_default_supplier'] = $settings['default_supplier'] ?? 'trendyol';
        }
        
        // Tedarikçi listesi
        $data['suppliers'] = [
            'trendyol' => 'Trendyol',
            'n11' => 'n11',
            'amazon' => 'Amazon',
            'ebay' => 'eBay',
            'hepsiburada' => 'Hepsiburada',
            'ozon' => 'Ozon'
        ];
        
        // Ürün listesini yükle
        $data['products'] = $this->getDropshippingProducts();
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/dropshipping', $data));
    }
    
    /**
     * Dropshipping ürünlerini getirir
     */
    private function getDropshippingProducts() {
        $this->load->model('extension/module/dropshipping');
        $this->load->model('catalog/product');
        
        $products = $this->model_extension_module_dropshipping->getProducts();
        
        $result = [];
        foreach ($products as $product) {
            $opencart_product = $this->model_catalog_product->getProduct($product['opencart_product_id']);
            
            if ($opencart_product) {
                $result[] = [
                    'product_id' => $product['product_id'],
                    'opencart_product_id' => $product['opencart_product_id'],
                    'name' => $opencart_product['name'],
                    'model' => $opencart_product['model'],
                    'supplier' => $product['supplier'],
                    'supplier_product_id' => $product['supplier_product_id'],
                    'supplier_price' => $product['supplier_price'],
                    'opencart_price' => $opencart_product['price'],
                    'profit' => $opencart_product['price'] - $product['supplier_price'],
                    'profit_percent' => $product['supplier_price'] > 0 ? round(($opencart_product['price'] - $product['supplier_price']) / $product['supplier_price'] * 100, 2) : 0,
                    'status' => $product['status']
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Ürün arama
     */
    public function search() {
        $this->load->language('extension/module/dropshipping');
        
        $json = [];
        
        if (isset($this->request->get['supplier']) && isset($this->request->get['keyword'])) {
            $supplier = $this->request->get['supplier'];
            $keyword = $this->request->get['keyword'];
            
            $this->load->model('extension/module/dropshipping');
            $results = $this->model_extension_module_dropshipping->searchProducts($supplier, $keyword);
            
            if ($results) {
                $json = $results;
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Ürün içe aktarma
     */
    public function import() {
        $this->load->language('extension/module/dropshipping');
        
        $json = [];
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (isset($this->request->post['supplier']) && isset($this->request->post['products'])) {
                $supplier = $this->request->post['supplier'];
                $products = $this->request->post['products'];
                
                $this->load->model('extension/module/dropshipping');
                $imported = $this->model_extension_module_dropshipping->importProducts($supplier, $products);
                
                $json['success'] = sprintf($this->language->get('text_imported'), $imported);
            } else {
                $json['error'] = $this->language->get('error_invalid_data');
            }
        } else {
            $json['error'] = $this->language->get('error_permission');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Sipariş oluşturma
     */
    public function order() {
        $this->load->language('extension/module/dropshipping');
        
        $json = [];
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (isset($this->request->post['opencart_order_id'])) {
                $opencart_order_id = $this->request->post['opencart_order_id'];
                
                $this->load->model('extension/module/dropshipping');
                $result = $this->model_extension_module_dropshipping->createSupplierOrder($opencart_order_id);
                
                if ($result) {
                    $json['success'] = $this->language->get('text_order_created');
                } else {
                    $json['error'] = $this->language->get('error_order_creation');
                }
            } else {
                $json['error'] = $this->language->get('error_invalid_data');
            }
        } else {
            $json['error'] = $this->language->get('error_permission');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Kurulum
     */
    public function install() {
        $this->load->model('extension/module/dropshipping');
        $this->model_extension_module_dropshipping->install();
        
        $this->writeLog('SYSTEM', 'INSTALL', 'Dropshipping modülü kuruldu');
    }
    
    /**
     * Kaldırma
     */
    public function uninstall() {
        $this->load->model('extension/module/dropshipping');
        $this->model_extension_module_dropshipping->uninstall();
        
        $this->writeLog('SYSTEM', 'UNINSTALL', 'Dropshipping modülü kaldırıldı');
    }
    
    /**
     * Doğrulama
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/dropshipping')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        return !$this->error;
    }
    
    /**
     * Log kaydı
     */
    private function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'dropshipping.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 