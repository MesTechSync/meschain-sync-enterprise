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
    
    /**
     * Advanced analytics for dropshipping performance
     */
    public function getAdvancedAnalytics($period = '30d') {
        $analytics = [];
        
        // Determine date range
        switch ($period) {
            case '7d':
                $date_condition = "DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                $group_by = "DATE(created_at)";
                break;
            case '30d':
                $date_condition = "DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                $group_by = "DATE(created_at)";
                break;
            case '12m':
                $date_condition = "DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
                $group_by = "DATE_FORMAT(created_at, '%Y-%m')";
                break;
            default:
                $date_condition = "DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                $group_by = "DATE(created_at)";
        }
        
        // Revenue trends
        $query = $this->db->query("
            SELECT 
                " . $group_by . " as period,
                COUNT(*) as order_count,
                SUM(total_amount) as revenue,
                SUM(commission) as total_commission,
                AVG(total_amount) as avg_order_value
            FROM " . DB_PREFIX . "dropshipping_orders 
            WHERE " . $date_condition . " AND status != 'cancelled'
            GROUP BY " . $group_by . "
            ORDER BY period
        ");
        $analytics['revenue_trends'] = $query->rows;
        
        // Supplier performance comparison
        $query = $this->db->query("
            SELECT 
                s.supplier_name,
                COUNT(do.id) as order_count,
                SUM(do.total_amount) as revenue,
                AVG(CASE 
                    WHEN do.shipped_at IS NOT NULL AND do.created_at IS NOT NULL 
                    THEN DATEDIFF(do.shipped_at, do.created_at) 
                    ELSE NULL 
                END) as avg_processing_days,
                (COUNT(CASE WHEN do.status = 'delivered' THEN 1 END) / COUNT(*)) * 100 as success_rate,
                s.commission_rate
            FROM " . DB_PREFIX . "dropshipping_suppliers s
            LEFT JOIN " . DB_PREFIX . "dropshipping_orders do ON s.supplier_id = do.supplier_id
            WHERE " . $date_condition . " OR do.id IS NULL
            GROUP BY s.supplier_id
            ORDER BY revenue DESC
        ");
        $analytics['supplier_performance'] = $query->rows;
        
        // Product profitability analysis
        $query = $this->db->query("
            SELECT 
                pd.name as product_name,
                p.model,
                dp.supplier_sku,
                COUNT(CASE WHEN do.status != 'cancelled' THEN 1 END) as sales_count,
                SUM(CASE WHEN do.status != 'cancelled' THEN do.total_amount ELSE 0 END) as total_revenue,
                dp.supplier_price,
                p.price as selling_price,
                (p.price - dp.supplier_price) as profit_per_unit,
                ((p.price - dp.supplier_price) / dp.supplier_price) * 100 as profit_margin_percent
            FROM " . DB_PREFIX . "dropshipping_products dp
            LEFT JOIN " . DB_PREFIX . "product p ON dp.product_id = p.product_id
            LEFT JOIN " . DB_PREFIX . "product_description pd ON p.product_id = pd.product_id
            LEFT JOIN " . DB_PREFIX . "dropshipping_orders do ON dp.supplier_id = do.supplier_id
            WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            AND (" . $date_condition . " OR do.id IS NULL)
            GROUP BY dp.id
            HAVING sales_count > 0
            ORDER BY total_revenue DESC
            LIMIT 20
        ");
        $analytics['product_profitability'] = $query->rows;
        
        // Automation efficiency metrics
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN submitted_at IS NOT NULL THEN 1 ELSE 0 END) as auto_submitted,
                SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as failed_orders,
                AVG(CASE 
                    WHEN submitted_at IS NOT NULL AND created_at IS NOT NULL 
                    THEN TIMESTAMPDIFF(MINUTE, created_at, submitted_at) 
                    ELSE NULL 
                END) as avg_processing_minutes
            FROM " . DB_PREFIX . "dropshipping_orders 
            WHERE " . $date_condition
        );
        $automation_data = $query->row;
        
        $analytics['automation_metrics'] = [
            'total_orders' => (int)$automation_data['total_orders'],
            'auto_submitted' => (int)$automation_data['auto_submitted'],
            'failed_orders' => (int)$automation_data['failed_orders'],
            'automation_rate' => $automation_data['total_orders'] > 0 ? 
                round(($automation_data['auto_submitted'] / $automation_data['total_orders']) * 100, 2) : 0,
            'avg_processing_minutes' => round($automation_data['avg_processing_minutes'] ?? 0, 2)
        ];
        
        return $analytics;
    }
    
    /**
     * Bulk operations for dropshipping products
     */
    public function bulkUpdateProducts($product_ids, $action, $data = []) {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        if (empty($product_ids) || !is_array($product_ids)) {
            return $results;
        }
        
        foreach ($product_ids as $product_id) {
            try {
                switch ($action) {
                    case 'update_markup':
                        if (isset($data['markup_type']) && isset($data['markup_value'])) {
                            $this->db->query("
                                UPDATE " . DB_PREFIX . "dropshipping_products 
                                SET markup_type = '" . $this->db->escape($data['markup_type']) . "',
                                    markup_value = '" . (float)$data['markup_value'] . "',
                                    updated_at = NOW()
                                WHERE product_id = '" . (int)$product_id . "'
                            ");
                            
                            // Update actual product price
                            $this->updateProductPrice($product_id, $data['markup_type'], $data['markup_value']);
                        }
                        break;
                        
                    case 'sync_stock':
                        $this->syncProductStock($product_id);
                        break;
                        
                    case 'enable_auto_order':
                        $this->db->query("
                            UPDATE " . DB_PREFIX . "dropshipping_products 
                            SET auto_order = '1', updated_at = NOW()
                            WHERE product_id = '" . (int)$product_id . "'
                        ");
                        break;
                        
                    case 'disable_auto_order':
                        $this->db->query("
                            UPDATE " . DB_PREFIX . "dropshipping_products 
                            SET auto_order = '0', updated_at = NOW()
                            WHERE product_id = '" . (int)$product_id . "'
                        ");
                        break;
                        
                    case 'enable_stock_sync':
                        $this->db->query("
                            UPDATE " . DB_PREFIX . "dropshipping_products 
                            SET stock_sync = '1', updated_at = NOW()
                            WHERE product_id = '" . (int)$product_id . "'
                        ");
                        break;
                        
                    case 'disable_stock_sync':
                        $this->db->query("
                            UPDATE " . DB_PREFIX . "dropshipping_products 
                            SET stock_sync = '0', updated_at = NOW()
                            WHERE product_id = '" . (int)$product_id . "'
                        ");
                        break;
                }
                
                $results['success']++;
                
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Product ID {$product_id}: " . $e->getMessage();
            }
        }
        
        $this->writeLog('BULK_UPDATE', "Bulk operation '{$action}' completed. Success: {$results['success']}, Failed: {$results['failed']}");
        
        return $results;
    }
    
    /**
     * Update product price based on markup
     */
    private function updateProductPrice($product_id, $markup_type, $markup_value) {
        $query = $this->db->query("
            SELECT dp.supplier_price 
            FROM " . DB_PREFIX . "dropshipping_products dp
            WHERE dp.product_id = '" . (int)$product_id . "'
        ");
        
        if ($query->num_rows) {
            $supplier_price = (float)$query->row['supplier_price'];
            
            if ($markup_type == 'percentage') {
                $new_price = $supplier_price * (1 + $markup_value / 100);
            } else {
                $new_price = $supplier_price + $markup_value;
            }
            
            $this->db->query("
                UPDATE " . DB_PREFIX . "product 
                SET price = '" . (float)$new_price . "'
                WHERE product_id = '" . (int)$product_id . "'
            ");
        }
    }
    
    /**
     * Sync individual product stock
     */
    private function syncProductStock($product_id) {
        $query = $this->db->query("
            SELECT dp.*, s.api_endpoint, s.api_key, s.api_secret
            FROM " . DB_PREFIX . "dropshipping_products dp
            LEFT JOIN " . DB_PREFIX . "dropshipping_suppliers s ON dp.supplier_id = s.supplier_id
            WHERE dp.product_id = '" . (int)$product_id . "' AND dp.stock_sync = 1
        ");
        
        if ($query->num_rows) {
            $product = $query->row;
            $stock = $this->getSupplierStock($product['supplier_id'], $product['supplier_sku']);
            
            if ($stock !== false) {
                // Update stock in OpenCart
                $this->db->query("
                    UPDATE " . DB_PREFIX . "product 
                    SET quantity = '" . (int)$stock . "'
                    WHERE product_id = '" . (int)$product_id . "'
                ");
                
                // Update dropshipping table
                $this->db->query("
                    UPDATE " . DB_PREFIX . "dropshipping_products 
                    SET stock_quantity = '" . (int)$stock . "',
                        last_sync = NOW(),
                        sync_status = 'success',
                        updated_at = NOW()
                    WHERE product_id = '" . (int)$product_id . "'
                ");
                
                return true;
            } else {
                // Mark sync as failed
                $this->db->query("
                    UPDATE " . DB_PREFIX . "dropshipping_products 
                    SET sync_status = 'error',
                        updated_at = NOW()
                    WHERE product_id = '" . (int)$product_id . "'
                ");
                
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Automated profit optimization
     */
    public function optimizeProfitMargins($supplier_id = null) {
        $optimized = 0;
        $min_margin = 15; // Minimum 15% margin
        $target_margin = 30; // Target 30% margin
        
        $sql = "SELECT dp.*, p.price as current_price, s.commission_rate
                FROM " . DB_PREFIX . "dropshipping_products dp
                LEFT JOIN " . DB_PREFIX . "product p ON dp.product_id = p.product_id
                LEFT JOIN " . DB_PREFIX . "dropshipping_suppliers s ON dp.supplier_id = s.supplier_id
                WHERE dp.status = 1 AND dp.supplier_price > 0";
        
        if ($supplier_id) {
            $sql .= " AND dp.supplier_id = '" . (int)$supplier_id . "'";
        }
        
        $query = $this->db->query($sql);
        
        foreach ($query->rows as $product) {
            $supplier_cost = (float)$product['supplier_price'];
            $commission = $supplier_cost * ((float)$product['commission_rate'] / 100);
            $total_cost = $supplier_cost + $commission;
            
            // Calculate current margin
            $current_price = (float)$product['current_price'];
            $current_margin = $current_price > 0 ? (($current_price - $total_cost) / $total_cost) * 100 : 0;
            
            // Check if optimization is needed
            if ($current_margin < $min_margin || abs($current_margin - $target_margin) > 5) {
                $optimal_price = $total_cost * (1 + $target_margin / 100);
                
                // Update product price
                $this->db->query("
                    UPDATE " . DB_PREFIX . "product 
                    SET price = '" . (float)$optimal_price . "'
                    WHERE product_id = '" . (int)$product['product_id'] . "'
                ");
                
                // Update markup in dropshipping table
                $new_markup = (($optimal_price - $supplier_cost) / $supplier_cost) * 100;
                $this->db->query("
                    UPDATE " . DB_PREFIX . "dropshipping_products 
                    SET markup_type = 'percentage',
                        markup_value = '" . (float)$new_markup . "',
                        updated_at = NOW()
                    WHERE product_id = '" . (int)$product['product_id'] . "'
                ");
                
                $optimized++;
            }
        }
        
        $this->writeLog('PROFIT_OPTIMIZATION', "Optimized pricing for {$optimized} products");
        
        return $optimized;
    }
    
    /**
     * Get automation rules
     */
    public function getAutomationRules($filter = []) {
        $sql = "SELECT * FROM " . DB_PREFIX . "dropshipping_rules WHERE 1=1";
        
        if (isset($filter['marketplace'])) {
            $sql .= " AND marketplace = '" . $this->db->escape($filter['marketplace']) . "'";
        }
        
        if (isset($filter['is_active'])) {
            $sql .= " AND is_active = '" . (int)$filter['is_active'] . "'";
        }
        
        $sql .= " ORDER BY priority DESC, rule_name";
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Add automation rule
     */
    public function addAutomationRule($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "dropshipping_rules SET
            rule_name = '" . $this->db->escape($data['rule_name']) . "',
            marketplace = '" . $this->db->escape($data['marketplace'] ?? '') . "',
            supplier_id = " . (isset($data['supplier_id']) ? "'" . (int)$data['supplier_id'] . "'" : "NULL") . ",
            category_id = " . (isset($data['category_id']) ? "'" . (int)$data['category_id'] . "'" : "NULL") . ",
            conditions = '" . $this->db->escape(json_encode($data['conditions'] ?? [])) . "',
            actions = '" . $this->db->escape(json_encode($data['actions'] ?? [])) . "',
            is_active = '" . (int)($data['is_active'] ?? 1) . "',
            priority = '" . (int)($data['priority'] ?? 1) . "',
            created_at = NOW(),
            updated_at = NOW()");
        
        $rule_id = $this->db->getLastId();
        $this->writeLog('ADD_RULE', 'Automation rule added: ' . $data['rule_name']);
        return $rule_id;
    }
} 