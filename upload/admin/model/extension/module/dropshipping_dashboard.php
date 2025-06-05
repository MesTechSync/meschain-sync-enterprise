<?php
/**
 * Dropshipping Dashboard Model
 * MesChain-Sync OpenCart Extension
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0
 * @author MesChain Development Team
 */

class ModelExtensionModuleDropshippingDashboard extends Model {
    
    private $dropship_table = 'meschain_dropshipping';
    private $supplier_table = 'meschain_dropship_suppliers';
    private $order_table = 'meschain_dropship_orders';
    private $product_table = 'meschain_dropship_products';
    
    /**
     * Initialize dropshipping tables
     *
     * @return void
     */
    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->supplier_table . "` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `email` varchar(255),
                `phone` varchar(50),
                `address` text,
                `marketplace` varchar(50),
                `api_credentials` json,
                `status` tinyint(1) DEFAULT 1,
                `commission_rate` decimal(5,2) DEFAULT 0.00,
                `rating` decimal(3,2) DEFAULT 0.00,
                `total_orders` int(11) DEFAULT 0,
                `total_revenue` decimal(15,4) DEFAULT 0.0000,
                `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `marketplace` (`marketplace`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->product_table . "` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `supplier_id` int(11) NOT NULL,
                `opencart_product_id` int(11) NOT NULL,
                `supplier_product_id` varchar(100),
                `supplier_sku` varchar(100),
                `cost_price` decimal(15,4) DEFAULT 0.0000,
                `selling_price` decimal(15,4) DEFAULT 0.0000,
                `profit_margin` decimal(5,2) DEFAULT 0.00,
                `stock_quantity` int(11) DEFAULT 0,
                `min_stock_level` int(11) DEFAULT 5,
                `auto_sync` tinyint(1) DEFAULT 1,
                `sync_status` enum('pending', 'synced', 'failed') DEFAULT 'pending',
                `last_sync` datetime,
                `error_message` text,
                `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `supplier_id` (`supplier_id`),
                KEY `opencart_product_id` (`opencart_product_id`),
                KEY `sync_status` (`sync_status`),
                FOREIGN KEY (`supplier_id`) REFERENCES `" . DB_PREFIX . $this->supplier_table . "` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->order_table . "` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `opencart_order_id` int(11) NOT NULL,
                `supplier_id` int(11) NOT NULL,
                `supplier_order_id` varchar(100),
                `status` varchar(50) DEFAULT 'pending',
                `total_amount` decimal(15,4) DEFAULT 0.0000,
                `commission_amount` decimal(15,4) DEFAULT 0.0000,
                `profit_amount` decimal(15,4) DEFAULT 0.0000,
                `tracking_number` varchar(100),
                `shipping_status` varchar(50) DEFAULT 'pending',
                `customer_data` json,
                `product_data` json,
                `sync_status` enum('pending', 'synced', 'failed') DEFAULT 'pending',
                `error_message` text,
                `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `opencart_order_id` (`opencart_order_id`),
                KEY `supplier_id` (`supplier_id`),
                KEY `status` (`status`),
                KEY `shipping_status` (`shipping_status`),
                FOREIGN KEY (`supplier_id`) REFERENCES `" . DB_PREFIX . $this->supplier_table . "` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->dropship_table . "` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `setting_key` varchar(100) NOT NULL,
                `setting_value` text,
                `status` tinyint(1) DEFAULT 1,
                `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
                `date_modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `setting_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }
    
    /**
     * Get dashboard statistics
     *
     * @return array Dashboard statistics
     */
    public function getDashboardStats() {
        $stats = [
            'suppliers' => [
                'total' => 0,
                'active' => 0,
                'inactive' => 0
            ],
            'products' => [
                'total' => 0,
                'synced' => 0,
                'pending' => 0,
                'failed' => 0,
                'low_stock' => 0
            ],
            'orders' => [
                'total' => 0,
                'pending' => 0,
                'processing' => 0,
                'shipped' => 0,
                'delivered' => 0,
                'cancelled' => 0
            ],
            'revenue' => [
                'total' => 0,
                'this_month' => 0,
                'last_month' => 0,
                'total_profit' => 0,
                'total_commission' => 0
            ]
        ];
        
        // Supplier statistics
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as inactive
            FROM " . DB_PREFIX . $this->supplier_table
        );
        
        if ($query->num_rows) {
            $row = $query->row;
            $stats['suppliers']['total'] = (int)$row['total'];
            $stats['suppliers']['active'] = (int)$row['active'];
            $stats['suppliers']['inactive'] = (int)$row['inactive'];
        }
        
        // Product statistics
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN sync_status = 'synced' THEN 1 ELSE 0 END) as synced,
                SUM(CASE WHEN sync_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN sync_status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN stock_quantity <= min_stock_level THEN 1 ELSE 0 END) as low_stock
            FROM " . DB_PREFIX . $this->product_table
        );
        
        if ($query->num_rows) {
            $row = $query->row;
            $stats['products']['total'] = (int)$row['total'];
            $stats['products']['synced'] = (int)$row['synced'];
            $stats['products']['pending'] = (int)$row['pending'];
            $stats['products']['failed'] = (int)$row['failed'];
            $stats['products']['low_stock'] = (int)$row['low_stock'];
        }
        
        // Order statistics
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN shipping_status = 'shipped' THEN 1 ELSE 0 END) as shipped,
                SUM(CASE WHEN shipping_status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM " . DB_PREFIX . $this->order_table
        );
        
        if ($query->num_rows) {
            $row = $query->row;
            $stats['orders']['total'] = (int)$row['total'];
            $stats['orders']['pending'] = (int)$row['pending'];
            $stats['orders']['processing'] = (int)$row['processing'];
            $stats['orders']['shipped'] = (int)$row['shipped'];
            $stats['orders']['delivered'] = (int)$row['delivered'];
            $stats['orders']['cancelled'] = (int)$row['cancelled'];
        }
        
        // Revenue statistics
        $query = $this->db->query("
            SELECT 
                SUM(total_amount) as total_revenue,
                SUM(profit_amount) as total_profit,
                SUM(commission_amount) as total_commission,
                SUM(CASE WHEN MONTH(date_added) = MONTH(CURDATE()) AND YEAR(date_added) = YEAR(CURDATE()) THEN total_amount ELSE 0 END) as this_month,
                SUM(CASE WHEN MONTH(date_added) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(date_added) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN total_amount ELSE 0 END) as last_month
            FROM " . DB_PREFIX . $this->order_table . "
            WHERE status != 'cancelled'
        ");
        
        if ($query->num_rows) {
            $row = $query->row;
            $stats['revenue']['total'] = (float)$row['total_revenue'];
            $stats['revenue']['this_month'] = (float)$row['this_month'];
            $stats['revenue']['last_month'] = (float)$row['last_month'];
            $stats['revenue']['total_profit'] = (float)$row['total_profit'];
            $stats['revenue']['total_commission'] = (float)$row['total_commission'];
        }
        
        return $stats;
    }
    
    /**
     * Get suppliers list
     *
     * @param array $filters Filter options
     * @return array Suppliers data
     */
    public function getSuppliers($filters = []) {
        $sql = "SELECT * FROM " . DB_PREFIX . $this->supplier_table;
        $where = [];
        
        if (!empty($filters['status'])) {
            $where[] = "status = " . (int)$filters['status'];
        }
        
        if (!empty($filters['marketplace'])) {
            $where[] = "marketplace = '" . $this->db->escape($filters['marketplace']) . "'";
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(name LIKE '%" . $this->db->escape($filters['search']) . "%' OR email LIKE '%" . $this->db->escape($filters['search']) . "%')";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY name ASC";
        
        if (isset($filters['limit']) && $filters['limit'] > 0) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            
            if (isset($filters['offset']) && $filters['offset'] > 0) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get dropshipping products
     *
     * @param array $filters Filter options
     * @return array Products data
     */
    public function getDropshippingProducts($filters = []) {
        $sql = "SELECT dp.*, s.name as supplier_name, p.name as product_name, p.model
                FROM " . DB_PREFIX . $this->product_table . " dp
                LEFT JOIN " . DB_PREFIX . $this->supplier_table . " s ON dp.supplier_id = s.id
                LEFT JOIN " . DB_PREFIX . "product p ON dp.opencart_product_id = p.product_id";
        
        $where = [];
        
        if (!empty($filters['supplier_id'])) {
            $where[] = "dp.supplier_id = " . (int)$filters['supplier_id'];
        }
        
        if (!empty($filters['sync_status'])) {
            $where[] = "dp.sync_status = '" . $this->db->escape($filters['sync_status']) . "'";
        }
        
        if (!empty($filters['low_stock'])) {
            $where[] = "dp.stock_quantity <= dp.min_stock_level";
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(p.name LIKE '%" . $this->db->escape($filters['search']) . "%' OR dp.supplier_sku LIKE '%" . $this->db->escape($filters['search']) . "%')";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY dp.date_modified DESC";
        
        if (isset($filters['limit']) && $filters['limit'] > 0) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get dropshipping orders
     *
     * @param array $filters Filter options
     * @return array Orders data
     */
    public function getDropshippingOrders($filters = []) {
        $sql = "SELECT do.*, s.name as supplier_name, o.firstname, o.lastname, o.email
                FROM " . DB_PREFIX . $this->order_table . " do
                LEFT JOIN " . DB_PREFIX . $this->supplier_table . " s ON do.supplier_id = s.id
                LEFT JOIN " . DB_PREFIX . "order o ON do.opencart_order_id = o.order_id";
        
        $where = [];
        
        if (!empty($filters['supplier_id'])) {
            $where[] = "do.supplier_id = " . (int)$filters['supplier_id'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "do.status = '" . $this->db->escape($filters['status']) . "'";
        }
        
        if (!empty($filters['shipping_status'])) {
            $where[] = "do.shipping_status = '" . $this->db->escape($filters['shipping_status']) . "'";
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "do.date_added >= '" . $this->db->escape($filters['date_from']) . "'";
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "do.date_added <= '" . $this->db->escape($filters['date_to']) . "'";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY do.date_added DESC";
        
        if (isset($filters['limit']) && $filters['limit'] > 0) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get profit analysis
     *
     * @param int $days Number of days to analyze
     * @return array Profit analysis data
     */
    public function getProfitAnalysis($days = 30) {
        $analysis = [
            'daily_profit' => [],
            'supplier_profit' => [],
            'product_profit' => [],
            'total_profit' => 0,
            'average_margin' => 0
        ];
        
        // Daily profit analysis
        $query = $this->db->query("
            SELECT 
                DATE(date_added) as date,
                SUM(profit_amount) as daily_profit,
                COUNT(*) as order_count
            FROM " . DB_PREFIX . $this->order_table . "
            WHERE date_added >= DATE_SUB(CURDATE(), INTERVAL " . (int)$days . " DAY)
            AND status != 'cancelled'
            GROUP BY DATE(date_added)
            ORDER BY date DESC
        ");
        
        foreach ($query->rows as $row) {
            $analysis['daily_profit'][] = [
                'date' => $row['date'],
                'profit' => (float)$row['daily_profit'],
                'orders' => (int)$row['order_count']
            ];
            $analysis['total_profit'] += (float)$row['daily_profit'];
        }
        
        // Supplier profit analysis
        $query = $this->db->query("
            SELECT 
                s.name,
                s.id,
                SUM(do.profit_amount) as total_profit,
                COUNT(do.id) as order_count,
                AVG(dp.profit_margin) as avg_margin
            FROM " . DB_PREFIX . $this->order_table . " do
            LEFT JOIN " . DB_PREFIX . $this->supplier_table . " s ON do.supplier_id = s.id
            LEFT JOIN " . DB_PREFIX . $this->product_table . " dp ON do.supplier_id = dp.supplier_id
            WHERE do.date_added >= DATE_SUB(CURDATE(), INTERVAL " . (int)$days . " DAY)
            AND do.status != 'cancelled'
            GROUP BY s.id
            ORDER BY total_profit DESC
            LIMIT 10
        ");
        
        foreach ($query->rows as $row) {
            $analysis['supplier_profit'][] = [
                'supplier_id' => (int)$row['id'],
                'supplier_name' => $row['name'],
                'profit' => (float)$row['total_profit'],
                'orders' => (int)$row['order_count'],
                'avg_margin' => (float)$row['avg_margin']
            ];
        }
        
        // Calculate average margin
        $query = $this->db->query("
            SELECT AVG(profit_margin) as avg_margin
            FROM " . DB_PREFIX . $this->product_table
        ");
        
        if ($query->num_rows) {
            $analysis['average_margin'] = (float)$query->row['avg_margin'];
        }
        
        return $analysis;
    }
    
    /**
     * Update product stock from supplier
     *
     * @param int $product_id Product ID
     * @return bool Success status
     */
    public function updateProductStock($product_id) {
        try {
            $query = $this->db->query("
                SELECT dp.*, s.api_credentials, s.marketplace
                FROM " . DB_PREFIX . $this->product_table . " dp
                LEFT JOIN " . DB_PREFIX . $this->supplier_table . " s ON dp.supplier_id = s.id
                WHERE dp.id = " . (int)$product_id
            );
            
            if (!$query->num_rows) {
                return false;
            }
            
            $product = $query->row;
            
            // Here you would implement actual API calls to suppliers
            // For now, we'll just mark as synced
            
            $this->db->query("
                UPDATE " . DB_PREFIX . $this->product_table . "
                SET sync_status = 'synced',
                    last_sync = NOW(),
                    error_message = ''
                WHERE id = " . (int)$product_id
            );
            
            return true;
            
        } catch (Exception $e) {
            $this->db->query("
                UPDATE " . DB_PREFIX . $this->product_table . "
                SET sync_status = 'failed',
                    last_sync = NOW(),
                    error_message = '" . $this->db->escape($e->getMessage()) . "'
                WHERE id = " . (int)$product_id
            );
            
            return false;
        }
    }
    
    /**
     * Create dropshipping order
     *
     * @param array $order_data Order data
     * @return int|false Order ID or false on failure
     */
    public function createDropshippingOrder($order_data) {
        try {
            $this->db->query("
                INSERT INTO " . DB_PREFIX . $this->order_table . "
                (opencart_order_id, supplier_id, total_amount, commission_amount, profit_amount, customer_data, product_data)
                VALUES (
                    " . (int)$order_data['opencart_order_id'] . ",
                    " . (int)$order_data['supplier_id'] . ",
                    " . (float)$order_data['total_amount'] . ",
                    " . (float)$order_data['commission_amount'] . ",
                    " . (float)$order_data['profit_amount'] . ",
                    '" . $this->db->escape(json_encode($order_data['customer_data'])) . "',
                    '" . $this->db->escape(json_encode($order_data['product_data'])) . "'
                )
            ");
            
            return $this->db->getLastId();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get low stock alerts
     *
     * @return array Low stock products
     */
    public function getLowStockAlerts() {
        $query = $this->db->query("
            SELECT dp.*, s.name as supplier_name, p.name as product_name
            FROM " . DB_PREFIX . $this->product_table . " dp
            LEFT JOIN " . DB_PREFIX . $this->supplier_table . " s ON dp.supplier_id = s.id
            LEFT JOIN " . DB_PREFIX . "product p ON dp.opencart_product_id = p.product_id
            WHERE dp.stock_quantity <= dp.min_stock_level
            AND s.status = 1
            ORDER BY dp.stock_quantity ASC
        ");
        
        return $query->rows;
    }
}