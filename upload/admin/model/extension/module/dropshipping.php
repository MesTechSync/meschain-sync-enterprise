<?php
/**
 * Dropshipping Model
 * 
 * Dropshipping yönetimi ve otomatik sipariş işleme modeli
 * 
 * @category   Model
 * @package    MesChain-Sync
 * @version    2.5.0
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

class ModelExtensionModuleDropshipping extends Model {
    
    /**
     * Install module tables
     */
    public function install() {
        // Create dropshipping suppliers table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropshipping_suppliers` (
                `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
                `supplier_name` varchar(255) NOT NULL,
                `contact_name` varchar(255) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `phone` varchar(50) DEFAULT NULL,
                `website` varchar(255) DEFAULT NULL,
                `api_endpoint` varchar(500) DEFAULT NULL,
                `api_key` varchar(255) DEFAULT NULL,
                `api_secret` varchar(255) DEFAULT NULL,
                `commission_rate` decimal(5,2) DEFAULT '0.00',
                `minimum_order` decimal(10,2) DEFAULT '0.00',
                `shipping_cost` decimal(10,2) DEFAULT '0.00',
                `processing_time` int(3) DEFAULT '1',
                `status` tinyint(1) DEFAULT '1',
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`supplier_id`),
                KEY `status` (`status`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Create dropshipping products table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropshipping_products` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `supplier_id` int(11) NOT NULL,
                `supplier_sku` varchar(100) NOT NULL,
                `supplier_price` decimal(10,4) NOT NULL DEFAULT '0.0000',
                `markup_type` enum('fixed','percentage') DEFAULT 'percentage',
                `markup_value` decimal(10,4) DEFAULT '0.0000',
                `stock_quantity` int(11) DEFAULT '0',
                `stock_sync` tinyint(1) DEFAULT '1',
                `auto_order` tinyint(1) DEFAULT '0',
                `last_sync` datetime DEFAULT NULL,
                `sync_status` enum('pending','success','error') DEFAULT 'pending',
                `status` tinyint(1) DEFAULT '1',
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `product_supplier` (`product_id`, `supplier_id`),
                KEY `supplier_id` (`supplier_id`),
                KEY `sync_status` (`sync_status`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Create dropshipping orders table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropshipping_orders` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `opencart_order_id` int(11) NOT NULL,
                `supplier_id` int(11) NOT NULL,
                `supplier_order_id` varchar(100) DEFAULT NULL,
                `status` enum('pending','processing','shipped','delivered','cancelled','error') DEFAULT 'pending',
                `total_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
                `commission` decimal(15,4) DEFAULT '0.0000',
                `tracking_number` varchar(100) DEFAULT NULL,
                `shipping_carrier` varchar(100) DEFAULT NULL,
                `notes` text,
                `order_data` text,
                `error_message` text,
                `submitted_at` datetime DEFAULT NULL,
                `shipped_at` datetime DEFAULT NULL,
                `delivered_at` datetime DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `opencart_order_id` (`opencart_order_id`),
                KEY `supplier_id` (`supplier_id`),
                KEY `status` (`status`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Create dropshipping automation rules table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropshipping_rules` (
                `rule_id` int(11) NOT NULL AUTO_INCREMENT,
                `rule_name` varchar(255) NOT NULL,
                `marketplace` varchar(50) DEFAULT NULL,
                `supplier_id` int(11) DEFAULT NULL,
                `category_id` int(11) DEFAULT NULL,
                `conditions` text,
                `actions` text,
                `is_active` tinyint(1) DEFAULT '1',
                `priority` int(3) DEFAULT '1',
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`rule_id`),
                KEY `marketplace` (`marketplace`),
                KEY `supplier_id` (`supplier_id`),
                KEY `is_active` (`is_active`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        $this->writeLog('INSTALL', 'Dropshipping tables created successfully');
    }
    
    /**
     * Uninstall module
     */
    public function uninstall() {
        // Don't drop tables to preserve data
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE code LIKE 'module_dropshipping%'");
        $this->writeLog('UNINSTALL', 'Dropshipping module settings removed');
    }
    
    /**
     * Get suppliers
     */
    public function getSuppliers($filter = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "dropshipping_suppliers WHERE 1=1";
        
        if (isset($filter['status'])) {
            $sql .= " AND status = '" . (int)$filter['status'] . "'";
        }
        
        if (isset($filter['name'])) {
            $sql .= " AND supplier_name LIKE '%" . $this->db->escape($filter['name']) . "%'";
        }
        
        $sql .= " ORDER BY supplier_name";
        
        if (isset($filter['start']) && isset($filter['limit'])) {
            $sql .= " LIMIT " . (int)$filter['start'] . ", " . (int)$filter['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Add supplier
     */
    public function addSupplier($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "dropshipping_suppliers SET
            supplier_name = '" . $this->db->escape($data['supplier_name']) . "',
            contact_name = '" . $this->db->escape($data['contact_name'] ?? '') . "',
            email = '" . $this->db->escape($data['email'] ?? '') . "',
            phone = '" . $this->db->escape($data['phone'] ?? '') . "',
            website = '" . $this->db->escape($data['website'] ?? '') . "',
            api_endpoint = '" . $this->db->escape($data['api_endpoint'] ?? '') . "',
            api_key = '" . $this->db->escape($data['api_key'] ?? '') . "',
            api_secret = '" . $this->db->escape($data['api_secret'] ?? '') . "',
            commission_rate = '" . (float)($data['commission_rate'] ?? 0) . "',
            minimum_order = '" . (float)($data['minimum_order'] ?? 0) . "',
            shipping_cost = '" . (float)($data['shipping_cost'] ?? 0) . "',
            processing_time = '" . (int)($data['processing_time'] ?? 1) . "',
            status = '" . (int)($data['status'] ?? 1) . "',
            created_at = NOW(),
            updated_at = NOW()");
        
        $supplier_id = $this->db->getLastId();
        $this->writeLog('ADD_SUPPLIER', 'Supplier added: ' . $data['supplier_name']);
        return $supplier_id;
    }
    
    /**
     * Get dropshipping products
     */
    public function getDropshippingProducts($filter = array()) {
        $sql = "SELECT dp.*, p.model, p.price as opencart_price, pd.name as product_name,
                       s.supplier_name
                FROM " . DB_PREFIX . "dropshipping_products dp
                LEFT JOIN " . DB_PREFIX . "product p ON dp.product_id = p.product_id
                LEFT JOIN " . DB_PREFIX . "product_description pd ON dp.product_id = pd.product_id
                LEFT JOIN " . DB_PREFIX . "dropshipping_suppliers s ON dp.supplier_id = s.supplier_id
                WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        
        if (isset($filter['supplier_id'])) {
            $sql .= " AND dp.supplier_id = '" . (int)$filter['supplier_id'] . "'";
        }
        
        if (isset($filter['sync_status'])) {
            $sql .= " AND dp.sync_status = '" . $this->db->escape($filter['sync_status']) . "'";
        }
        
        if (isset($filter['status'])) {
            $sql .= " AND dp.status = '" . (int)$filter['status'] . "'";
        }
        
        $sql .= " ORDER BY dp.updated_at DESC";
        
        if (isset($filter['start']) && isset($filter['limit'])) {
            $sql .= " LIMIT " . (int)$filter['start'] . ", " . (int)$filter['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Get dropshipping orders
     */
    public function getDropshippingOrders($filter = array()) {
        $sql = "SELECT do.*, s.supplier_name, o.order_id, o.total as order_total
                FROM " . DB_PREFIX . "dropshipping_orders do
                LEFT JOIN " . DB_PREFIX . "dropshipping_suppliers s ON do.supplier_id = s.supplier_id
                LEFT JOIN " . DB_PREFIX . "order o ON do.opencart_order_id = o.order_id
                WHERE 1=1";
        
        if (isset($filter['status'])) {
            $sql .= " AND do.status = '" . $this->db->escape($filter['status']) . "'";
        }
        
        if (isset($filter['supplier_id'])) {
            $sql .= " AND do.supplier_id = '" . (int)$filter['supplier_id'] . "'";
        }
        
        $sql .= " ORDER BY do.created_at DESC";
        
        if (isset($filter['start']) && isset($filter['limit'])) {
            $sql .= " LIMIT " . (int)$filter['start'] . ", " . (int)$filter['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Process automatic order
     */
    public function processAutoOrder($order_id) {
        $this->load->model('sale/order');
        $order_info = $this->model_sale_order->getOrder($order_id);
        
        if (!$order_info) {
            return false;
        }
        
        $order_products = $this->model_sale_order->getOrderProducts($order_id);
        $processed = false;
        
        foreach ($order_products as $product) {
            // Check if product has dropshipping setup
            $query = $this->db->query("
                SELECT dp.*, s.supplier_name, s.api_endpoint, s.api_key
                FROM " . DB_PREFIX . "dropshipping_products dp
                JOIN " . DB_PREFIX . "dropshipping_suppliers s ON dp.supplier_id = s.supplier_id
                WHERE dp.product_id = '" . (int)$product['product_id'] . "'
                AND dp.auto_order = 1
                AND dp.status = 1
                AND s.status = 1
            ");
            
            if ($query->num_rows) {
                $dropship_product = $query->row;
                
                // Create dropshipping order
                $dropship_order_id = $this->createDropshippingOrder($order_id, $dropship_product, $product);
                
                if ($dropship_order_id) {
                    $processed = true;
                    $this->writeLog('AUTO_ORDER', "Auto order created for product {$product['product_id']} in order {$order_id}");
                }
            }
        }
        
        return $processed;
    }
    
    /**
     * Create dropshipping order
     */
    private function createDropshippingOrder($opencart_order_id, $dropship_product, $order_product) {
        $order_data = array(
            'opencart_order_id' => $opencart_order_id,
            'product_id' => $order_product['product_id'],
            'supplier_sku' => $dropship_product['supplier_sku'],
            'quantity' => $order_product['quantity'],
            'price' => $dropship_product['supplier_price']
        );
        
        $this->db->query("INSERT INTO " . DB_PREFIX . "dropshipping_orders SET
            opencart_order_id = '" . (int)$opencart_order_id . "',
            supplier_id = '" . (int)$dropship_product['supplier_id'] . "',
            status = 'pending',
            total_amount = '" . (float)($dropship_product['supplier_price'] * $order_product['quantity']) . "',
            commission = '" . (float)($dropship_product['supplier_price'] * $order_product['quantity'] * $dropship_product['commission_rate'] / 100) . "',
            order_data = '" . $this->db->escape(json_encode($order_data)) . "',
            created_at = NOW(),
            updated_at = NOW()");
        
        return $this->db->getLastId();
    }
    
    /**
     * Sync stock from supplier
     */
    public function syncStock($supplier_id = null) {
        $filter = array('stock_sync' => 1, 'status' => 1);
        if ($supplier_id) {
            $filter['supplier_id'] = $supplier_id;
        }
        
        $products = $this->getDropshippingProducts($filter);
        $updated = 0;
        
        foreach ($products as $product) {
            // Here you would call supplier API to get current stock
            // For now, we'll simulate stock update
            $new_stock = $this->getSupplierStock($product['supplier_id'], $product['supplier_sku']);
            
            if ($new_stock !== false && $new_stock != $product['stock_quantity']) {
                // Update dropshipping product stock
                $this->db->query("UPDATE " . DB_PREFIX . "dropshipping_products SET
                    stock_quantity = '" . (int)$new_stock . "',
                    last_sync = NOW(),
                    sync_status = 'success',
                    updated_at = NOW()
                    WHERE id = '" . (int)$product['id'] . "'");
                
                // Update OpenCart product stock
                $this->db->query("UPDATE " . DB_PREFIX . "product SET
                    quantity = '" . (int)$new_stock . "'
                    WHERE product_id = '" . (int)$product['product_id'] . "'");
                
                $updated++;
            }
        }
        
        $this->writeLog('SYNC_STOCK', "Stock synchronized for $updated products");
        return $updated;
    }
    
    /**
     * Get supplier stock (placeholder for API call)
     */
    private function getSupplierStock($supplier_id, $supplier_sku) {
        // This would normally make an API call to the supplier
        // For demo purposes, return random stock between 0-100
        return rand(0, 100);
    }
    
    /**
     * Dashboard statistics
     */
    public function getDashboardStats() {
        $stats = array();
        
        // Total dropshipping products
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dropshipping_products WHERE status = 1");
        $stats['total_products'] = $query->row['total'];
        
        // Total dropshipping orders
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dropshipping_orders");
        $stats['total_orders'] = $query->row['total'];
        
        // Active suppliers
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dropshipping_suppliers WHERE status = 1");
        $stats['total_sync'] = $query->row['total'];
        
        // Last stock sync
        $query = $this->db->query("SELECT MAX(last_sync) as last_sync FROM " . DB_PREFIX . "dropshipping_products");
        $stats['last_sync'] = $query->row['last_sync'] ? date('d.m.Y H:i', strtotime($query->row['last_sync'])) : 'Hiçbir zaman';
        
        // System status
        $active_suppliers = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dropshipping_suppliers WHERE status = 1")->row['total'];
        $stats['status'] = ($active_suppliers > 0) ? 'connected' : 'error';
        
        // Recent activity
        $query = $this->db->query("SELECT status FROM " . DB_PREFIX . "dropshipping_orders ORDER BY created_at DESC LIMIT 1");
        if ($query->num_rows) {
            $stats['recent_activity'] = 'Son sipariş durumu: ' . $query->row['status'];
        } else {
            $stats['recent_activity'] = 'Henüz dropshipping siparişi yok';
        }
        
        return $stats;
    }
    
    /**
     * Get dropshipping settings (for controller)
     */
    public function getSettings() {
        $settings = array();
        
        try {
            // Module status
            $settings['status'] = $this->config->get('module_dropshipping_status') ? 1 : 0;
            
            // Auto-order settings
            $settings['auto_order'] = $this->config->get('module_dropshipping_auto_order') ? 1 : 0;
            
            // Auto stock sync settings
            $settings['auto_stock_sync'] = $this->config->get('module_dropshipping_auto_stock_sync') ? 1 : 0;
            
            // Sync interval (minutes)
            $settings['sync_interval'] = $this->config->get('module_dropshipping_sync_interval') ? $this->config->get('module_dropshipping_sync_interval') : 60;
            
            // Default markup percentage
            $settings['default_markup'] = $this->config->get('module_dropshipping_default_markup') ? $this->config->get('module_dropshipping_default_markup') : 20;
            
            // Default processing time
            $settings['default_processing_time'] = $this->config->get('module_dropshipping_default_processing_time') ? $this->config->get('module_dropshipping_default_processing_time') : 3;
            
            // Log level
            $settings['log_level'] = $this->config->get('module_dropshipping_log_level') ? $this->config->get('module_dropshipping_log_level') : 'info';
            
            // Total statistics
            $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dropshipping_suppliers WHERE status = 1");
            $settings['total_suppliers'] = $query->row['total'];
            
            $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dropshipping_products WHERE status = 1");
            $settings['total_products'] = $query->row['total'];
            
            $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dropshipping_orders WHERE status != 'cancelled'");
            $settings['total_orders'] = $query->row['total'];
            
            // Recent sync info
            $query = $this->db->query("SELECT MAX(last_sync) as last_sync FROM " . DB_PREFIX . "dropshipping_products");
            $settings['last_sync'] = $query->row['last_sync'] ? date('d.m.Y H:i', strtotime($query->row['last_sync'])) : 'Henüz senkronizasyon yapılmamış';
            
        } catch (Exception $e) {
            // Error handling
            $settings = array(
                'status' => 0,
                'auto_order' => 0,
                'auto_stock_sync' => 0,
                'sync_interval' => 60,
                'default_markup' => 20,
                'default_processing_time' => 3,
                'log_level' => 'info',
                'total_suppliers' => 0,
                'total_products' => 0,
                'total_orders' => 0,
                'last_sync' => 'Hata: ' . $e->getMessage()
            );
        }
        
        return $settings;
    }
    
    /**
     * Get products (alias for getDropshippingProducts)
     */
    public function getProducts($filter = array()) {
        return $this->getDropshippingProducts($filter);
    }
    
    /**
     * Write log
     */
    private function writeLog($action, $message) {
        $log_file = DIR_LOGS . 'dropshipping_model.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [MODEL] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
    
    /**
     * Load API settings for supplier
     */
    private function loadApiSettings($supplier) {
        try {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "dropshipping_suppliers WHERE name = '" . $this->db->escape($supplier) . "' AND status = 1");
            
            if ($query->num_rows) {
                $settings = json_decode($query->row['api_settings'], true);
                return $settings ?: [];
            }
            
            return false;
        } catch (Exception $e) {
            $this->writeLog('API_SETTINGS', 'API ayarları yüklenirken hata: ' . $e->getMessage());
            return false;
        }
    }
} 