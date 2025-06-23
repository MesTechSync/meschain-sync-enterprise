<?php
/**
 * Dropshipping Automation Controller
 * MesChain-Sync v4.1 - OpenCart 3.0.4.0 Integration
 * Advanced Dropshipping Automation for Multiple Marketplaces
 * 
 * @author MesChain Development Team
 * @version 4.1.0
 * @copyright 2024 MesChain Technologies
 * @features Auto-order, Inventory sync, Profit tracking, Supplier management
 */

require_once DIR_SYSTEM . 'library/meschain/helper/dropshipping_helper.php';

class ControllerExtensionModuleDropshippingAutomation extends Controller {

    private $error = array();
    private $supported_suppliers = [
        'aliexpress', 'alibaba', 'cjdropshipping', 'oberlo', 'spocket', 'wholesale_central'
    ];

    public function __construct($registry) {
        parent::__construct($registry);
        $this->createDropshippingTables();
    }

    /**
     * Ana dropshipping yönetim sayfası
     */
    public function index() {
        $this->load->language('extension/module/dropshipping_automation');
        $this->document->setTitle($this->language->get('heading_title'));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_dropshipping_automation', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module/dropshipping_automation', 'user_token=' . $this->session->data['user_token'], true));
        }

        $data = $this->getViewData();
        $this->response->setOutput($this->load->view('extension/module/dropshipping_automation', $data));
    }

    /**
     * Otomatik sipariş işleme
     */
    public function processOrders() {
        $this->load->language('extension/module/dropshipping_automation');
        
        $json = array();
        
        try {
            $this->load->model('extension/module/dropshipping_automation');
            $this->load->library('meschain/helper/dropshipping_helper');
            
            // Bekleyen siparişleri al
            $pending_orders = $this->model_extension_module_dropshipping_automation->getPendingDropshipOrders();
            
            $processed_count = 0;
            $errors = array();
            
            foreach ($pending_orders as $order) {
                try {
                    // Tedarikçiye otomatik sipariş ver
                    $result = $this->dropshipping_helper->processAutomaticOrder($order);
                    
                    if ($result['success']) {
                        $processed_count++;
                        
                        // Sipariş durumunu güncelle
                        $this->model_extension_module_dropshipping_automation->updateOrderStatus(
                            $order['order_id'], 
                            'processing', 
                            $result['supplier_order_id']
                        );
                        
                        // Müşteriye bilgi maili gönder
                        if ($this->config->get('module_dropshipping_automation_notify_customer')) {
                            $this->dropshipping_helper->sendCustomerNotification($order, $result);
                        }
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
            $json['message'] = sprintf('Processed %d orders successfully', $processed_count);
            
            if (!empty($errors)) {
                $json['warnings'] = $errors;
            }
            
            $this->log->write('Dropshipping: ' . $processed_count . ' orders processed');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            $this->log->write('Dropshipping ERROR: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Stok senkronizasyonu
     */
    public function syncInventory() {
        $this->load->language('extension/module/dropshipping_automation');
        
        $json = array();
        
        try {
            $this->load->model('extension/module/dropshipping_automation');
            $this->load->library('meschain/helper/dropshipping_helper');
            
            // Dropshipping ürünlerini al
            $products = $this->model_extension_module_dropshipping_automation->getDropshippingProducts();
            
            $synced_count = 0;
            $out_of_stock = 0;
            $errors = array();
            
            foreach ($products as $product) {
                try {
                    // Tedarikçiden stok bilgisi al
                    $stock_info = $this->dropshipping_helper->getSupplierStockInfo($product);
                    
                    if ($stock_info['success']) {
                        // OpenCart stok güncelle
                        $this->model_extension_module_dropshipping_automation->updateProductStock(
                            $product['product_id'], 
                            $stock_info['quantity']
                        );
                        
                        $synced_count++;
                        
                        if ($stock_info['quantity'] <= 0) {
                            $out_of_stock++;
                            
                            // Stok bittiğinde marketplace'lerde de pasifleştir
                            if ($this->config->get('module_dropshipping_automation_auto_disable')) {
                                $this->dropshipping_helper->disableProductOnMarketplaces($product['product_id']);
                            }
                        }
                        
                        // Fiyat güncelleme de varsa
                        if ($this->config->get('module_dropshipping_automation_auto_price_update')) {
                            $this->updateProductPricing($product, $stock_info);
                        }
                    } else {
                        $errors[] = $product['name'] . ': ' . $stock_info['error'];
                    }
                } catch (Exception $e) {
                    $errors[] = $product['name'] . ': ' . $e->getMessage();
                }
                
                // Rate limiting
                usleep(250000); // 250ms delay
            }
            
            $json['success'] = true;
            $json['message'] = sprintf('Synced %d products, %d out of stock', $synced_count, $out_of_stock);
            
            if (!empty($errors)) {
                $json['warnings'] = $errors;
            }
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Kar analizi
     */
    public function profitAnalysis() {
        $this->load->language('extension/module/dropshipping_automation');
        
        $json = array();
        
        try {
            $this->load->model('extension/module/dropshipping_automation');
            
            $analysis = $this->model_extension_module_dropshipping_automation->getProfitAnalysis();
            
            $json['success'] = true;
            $json['data'] = $analysis;
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Tedarikçi yönetimi
     */
    public function manageSuppliers() {
        $this->load->language('extension/module/dropshipping_automation');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $this->load->model('extension/module/dropshipping_automation');
                
                $supplier_data = $this->request->post;
                $result = $this->model_extension_module_dropshipping_automation->saveSupplier($supplier_data);
                
                $json['success'] = true;
                $json['message'] = 'Supplier saved successfully';
                $json['supplier_id'] = $result['supplier_id'];
            } catch (Exception $e) {
                $json['success'] = false;
                $json['error'] = $e->getMessage();
            }
        } else {
            // GET isteği - tedarikçi listesi
            try {
                $this->load->model('extension/module/dropshipping_automation');
                $suppliers = $this->model_extension_module_dropshipping_automation->getSuppliers();
                
                $json['success'] = true;
                $json['data'] = $suppliers;
            } catch (Exception $e) {
                $json['success'] = false;
                $json['error'] = $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Kargo takibi
     */
    public function trackShipments() {
        $this->load->language('extension/module/dropshipping_automation');
        
        $json = array();
        
        try {
            $this->load->model('extension/module/dropshipping_automation');
            $this->load->library('meschain/helper/dropshipping_helper');
            
            // Kargo takibi bekleyen siparişler
            $shipments = $this->model_extension_module_dropshipping_automation->getPendingShipments();
            
            $updated_count = 0;
            $errors = array();
            
            foreach ($shipments as $shipment) {
                try {
                    // Tedarikçiden kargo bilgisi al
                    $tracking_info = $this->dropshipping_helper->getTrackingInfo($shipment);
                    
                    if ($tracking_info['success']) {
                        // Sipariş durumunu güncelle
                        $this->model_extension_module_dropshipping_automation->updateShipmentTracking(
                            $shipment['order_id'], 
                            $tracking_info
                        );
                        
                        $updated_count++;
                        
                        // Müşteriye kargo bilgisi gönder
                        if ($tracking_info['status'] == 'shipped') {
                            $this->dropshipping_helper->sendTrackingInfoToCustomer($shipment, $tracking_info);
                        }
                    } else {
                        $errors[] = 'Order #' . $shipment['order_id'] . ': ' . $tracking_info['error'];
                    }
                } catch (Exception $e) {
                    $errors[] = 'Order #' . $shipment['order_id'] . ': ' . $e->getMessage();
                }
            }
            
            $json['success'] = true;
            $json['message'] = sprintf('Updated %d shipments', $updated_count);
            
            if (!empty($errors)) {
                $json['warnings'] = $errors;
            }
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Otomatik fiyatlandırma kuralları
     */
    public function managePricingRules() {
        $this->load->language('extension/module/dropshipping_automation');
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            try {
                $this->load->model('extension/module/dropshipping_automation');
                
                $rules = $this->request->post['pricing_rules'];
                $this->model_extension_module_dropshipping_automation->savePricingRules($rules);
                
                $json['success'] = true;
                $json['message'] = 'Pricing rules updated successfully';
            } catch (Exception $e) {
                $json['success'] = false;
                $json['error'] = $e->getMessage();
            }
        } else {
            try {
                $this->load->model('extension/module/dropshipping_automation');
                $rules = $this->model_extension_module_dropshipping_automation->getPricingRules();
                
                $json['success'] = true;
                $json['data'] = $rules;
            } catch (Exception $e) {
                $json['success'] = false;
                $json['error'] = $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Dashboard verileri
     */
    public function getDashboardData() {
        $this->load->language('extension/module/dropshipping_automation');
        
        $json = array();
        
        try {
            $this->load->model('extension/module/dropshipping_automation');
            
            $data = [
                'pending_orders' => $this->model_extension_module_dropshipping_automation->getPendingOrdersCount(),
                'processing_orders' => $this->model_extension_module_dropshipping_automation->getProcessingOrdersCount(),
                'shipped_orders' => $this->model_extension_module_dropshipping_automation->getShippedOrdersCount(),
                'total_profit' => $this->model_extension_module_dropshipping_automation->getTotalProfit(),
                'avg_profit_margin' => $this->model_extension_module_dropshipping_automation->getAverageProfitMargin(),
                'top_products' => $this->model_extension_module_dropshipping_automation->getTopProfitableProducts(),
                'supplier_performance' => $this->model_extension_module_dropshipping_automation->getSupplierPerformance(),
                'recent_activities' => $this->model_extension_module_dropshipping_automation->getRecentActivities()
            ];
            
            $json['success'] = true;
            $json['data'] = $data;
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Ürün fiyatlandırmasını güncelle
     */
    private function updateProductPricing($product, $stock_info) {
        if (!isset($stock_info['cost_price'])) {
            return;
        }

        $cost_price = $stock_info['cost_price'];
        $pricing_rules = $this->getPricingRules($product['category_id']);
        
        // Kar marjını hesapla
        $profit_margin = $pricing_rules['profit_margin'] ?? 30; // %30 varsayılan
        $markup = $pricing_rules['markup'] ?? 0; // Sabit ekleme
        
        $selling_price = ($cost_price * (1 + $profit_margin / 100)) + $markup;
        
        // Minimum/maksimum fiyat kontrolü
        if (isset($pricing_rules['min_price']) && $selling_price < $pricing_rules['min_price']) {
            $selling_price = $pricing_rules['min_price'];
        }
        
        if (isset($pricing_rules['max_price']) && $selling_price > $pricing_rules['max_price']) {
            $selling_price = $pricing_rules['max_price'];
        }
        
        // Fiyatı güncelle
        $this->load->model('extension/module/dropshipping_automation');
        $this->model_extension_module_dropshipping_automation->updateProductPrice(
            $product['product_id'], 
            $selling_price
        );
    }

    /**
     * Kategori bazında fiyatlandırma kurallarını al
     */
    private function getPricingRules($category_id) {
        $default_rules = [
            'profit_margin' => 30,
            'markup' => 0,
            'min_price' => 10,
            'max_price' => 10000
        ];

        // Kategori özel kuralları varsa al
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "dropshipping_pricing_rules 
            WHERE category_id = " . (int)$category_id . " 
            OR category_id = 0 
            ORDER BY category_id DESC 
            LIMIT 1
        ");

        if ($query->num_rows) {
            return json_decode($query->row['rules'], true);
        }

        return $default_rules;
    }

    /**
     * Dropshipping tablolarını oluştur
     */
    private function createDropshippingTables() {
        // Dropshipping orders tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropshipping_orders` (
            `dropship_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `supplier_id` int(11) NOT NULL,
            `supplier_order_id` varchar(100),
            `cost_price` decimal(15,4) NOT NULL,
            `selling_price` decimal(15,4) NOT NULL,
            `profit` decimal(15,4) NOT NULL,
            `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
            `tracking_number` varchar(100),
            `shipping_method` varchar(100),
            `estimated_delivery` date,
            `actual_delivery` date,
            `notes` text,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`dropship_id`),
            KEY `order_id` (`order_id`),
            KEY `supplier_id` (`supplier_id`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // Dropshipping suppliers tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropshipping_suppliers` (
            `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `type` varchar(50) NOT NULL,
            `api_endpoint` varchar(500),
            `api_key` varchar(255),
            `api_secret` varchar(255),
            `contact_info` json,
            `performance_score` decimal(5,2) DEFAULT 0,
            `shipping_methods` json,
            `processing_time` int(11) DEFAULT 2,
            `return_policy` text,
            `status` tinyint(1) DEFAULT 1,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`supplier_id`),
            KEY `type` (`type`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // Dropshipping products tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropshipping_products` (
            `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `supplier_id` int(11) NOT NULL,
            `supplier_sku` varchar(100) NOT NULL,
            `cost_price` decimal(15,4) NOT NULL,
            `min_quantity` int(11) DEFAULT 1,
            `max_quantity` int(11) DEFAULT 999,
            `processing_time` int(11) DEFAULT 2,
            `variant_mapping` json,
            `last_stock_check` datetime,
            `last_price_update` datetime,
            `status` tinyint(1) DEFAULT 1,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`mapping_id`),
            UNIQUE KEY `product_supplier` (`product_id`, `supplier_id`),
            KEY `supplier_sku` (`supplier_sku`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        // Pricing rules tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropshipping_pricing_rules` (
            `rule_id` int(11) NOT NULL AUTO_INCREMENT,
            `category_id` int(11) DEFAULT 0,
            `supplier_id` int(11) DEFAULT 0,
            `rules` json NOT NULL,
            `priority` int(11) DEFAULT 1,
            `status` tinyint(1) DEFAULT 1,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`rule_id`),
            KEY `category_id` (`category_id`),
            KEY `supplier_id` (`supplier_id`),
            KEY `priority` (`priority`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    /**
     * View verilerini hazırla
     */
    private function getViewData() {
        $data = array();
        
        // Form fields
        $fields = [
            'status', 'auto_order', 'auto_inventory_sync', 'auto_price_update',
            'notify_customer', 'auto_disable', 'sync_interval', 'default_profit_margin'
        ];
        
        foreach ($fields as $field) {
            $key = 'module_dropshipping_automation_' . $field;
            if (isset($this->request->post[$key])) {
                $data[$key] = $this->request->post[$key];
            } else {
                $data[$key] = $this->config->get($key);
            }
        }
        
        // Links
        $data['action'] = $this->url->link('extension/module/dropshipping_automation', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        return $data;
    }

    /**
     * Form doğrulama
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/dropshipping_automation')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
} 