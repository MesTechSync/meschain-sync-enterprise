<?php
/**
 * ozon.php
 *
 * Amaç: Ozon modülünün OpenCart yönetici paneli (admin) tarafındaki model dosyasıdır. Veritabanı işlemleri burada tanımlanır.
 *
 * Loglama: Tüm önemli işlemler ve hatalar ozon_model.log dosyasına kaydedilmelidir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 *
 * Hata yönetimi: Hatalar loglanmalı ve kullanıcıya açıklayıcı mesaj gösterilmelidir.
 */
// Ozon modülünün veritabanı işlemleri için model dosyası

// ... OpenCart model fonksiyonları buraya eklenecek ... 

<?php
/**
 * Ozon Marketplace Model
 * MesChain-Sync v3.0 - OpenCart 3.0.4.0 Integration
 * Russian E-commerce Platform Model with FBO Support
 * 
 * @author MesChain Development Team
 * @version 3.0.0
 * @copyright 2024 MesChain Technologies
 */

class ModelExtensionModuleOzon extends Model {
    
    /**
     * Install module - create necessary tables
     */
    public function install() {
        // Create ozon_products table for tracking Ozon-specific product data
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_products` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `product_id` INT(11) NOT NULL,
            `ozon_product_id` VARCHAR(255) DEFAULT NULL,
            `ozon_sku` VARCHAR(255) DEFAULT NULL,
            `fbo_sku` VARCHAR(255) DEFAULT NULL,
            `offer_id` VARCHAR(255) DEFAULT NULL,
            `category_id` VARCHAR(255) DEFAULT NULL,
            `price` DECIMAL(15,4) DEFAULT NULL,
            `old_price` DECIMAL(15,4) DEFAULT NULL,
            `premium_price` DECIMAL(15,4) DEFAULT NULL,
            `min_price` DECIMAL(15,4) DEFAULT NULL,
            `marketing_price` DECIMAL(15,4) DEFAULT NULL,
            `status` ENUM('pending', 'synced', 'error', 'blocked') DEFAULT 'pending',
            `fbo_status` ENUM('none', 'pending', 'uploaded', 'active', 'inactive') DEFAULT 'none',
            `warehouse_id` VARCHAR(255) DEFAULT NULL,
            `stock_fbo` INT(11) DEFAULT 0,
            `stock_fbs` INT(11) DEFAULT 0,
            `visibility` ENUM('visible', 'invisible', 'disabled') DEFAULT 'visible',
            `last_sync` DATETIME DEFAULT NULL,
            `last_price_update` DATETIME DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `product_id` (`product_id`),
            INDEX `ozon_product_id` (`ozon_product_id`),
            INDEX `status` (`status`),
            INDEX `fbo_status` (`fbo_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create ozon_orders table for order management
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_orders` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) DEFAULT NULL,
            `ozon_order_id` VARCHAR(255) NOT NULL,
            `ozon_order_number` VARCHAR(255) DEFAULT NULL,
            `customer_id` INT(11) DEFAULT NULL,
            `status` ENUM('awaiting_packaging', 'awaiting_deliver', 'delivered', 'cancelled') DEFAULT 'awaiting_packaging',
            `delivery_method` VARCHAR(100) DEFAULT NULL,
            `warehouse_id` VARCHAR(255) DEFAULT NULL,
            `total_amount` DECIMAL(15,4) DEFAULT NULL,
            `currency` VARCHAR(3) DEFAULT 'RUB',
            `commission_amount` DECIMAL(15,4) DEFAULT NULL,
            `delivery_price` DECIMAL(15,4) DEFAULT NULL,
            `in_process_at` DATETIME DEFAULT NULL,
            `shipment_date` DATETIME DEFAULT NULL,
            `delivering_date` DATETIME DEFAULT NULL,
            `cancel_reason` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `ozon_order_id` (`ozon_order_id`),
            INDEX `order_id` (`order_id`),
            INDEX `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create ozon_order_items table for order line items
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_order_items` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `ozon_order_id` VARCHAR(255) NOT NULL,
            `product_id` INT(11) DEFAULT NULL,
            `ozon_product_id` VARCHAR(255) DEFAULT NULL,
            `sku` VARCHAR(255) DEFAULT NULL,
            `name` VARCHAR(500) DEFAULT NULL,
            `quantity` INT(11) DEFAULT 1,
            `price` DECIMAL(15,4) DEFAULT NULL,
            `total_discount_value` DECIMAL(15,4) DEFAULT NULL,
            `currency` VARCHAR(3) DEFAULT 'RUB',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `ozon_order_id` (`ozon_order_id`),
            INDEX `product_id` (`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create ozon_warehouses table for FBO warehouse management
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_warehouses` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `warehouse_id` VARCHAR(255) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `status` ENUM('active', 'inactive') DEFAULT 'active',
            `min_amount` DECIMAL(15,4) DEFAULT NULL,
            `has_entrusted_acceptance` TINYINT(1) DEFAULT 0,
            `city` VARCHAR(255) DEFAULT NULL,
            `region` VARCHAR(255) DEFAULT NULL,
            `delivery_days` INT(3) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `warehouse_id` (`warehouse_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Create ozon_sync_log table for tracking sync operations
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ozon_sync_log` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `operation_type` ENUM('product_sync', 'price_update', 'stock_update', 'order_sync', 'fbo_upload') NOT NULL,
            `product_id` INT(11) DEFAULT NULL,
            `status` ENUM('success', 'error', 'pending') NOT NULL,
            `message` TEXT DEFAULT NULL,
            `response_data` TEXT DEFAULT NULL,
            `execution_time` DECIMAL(8,3) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `operation_type` (`operation_type`),
            INDEX `status` (`status`),
            INDEX `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Insert default Russian warehouses
        $this->addDefaultWarehouses();
    }
    
    /**
     * Uninstall module - remove tables
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ozon_sync_log`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ozon_order_items`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ozon_orders`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ozon_warehouses`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ozon_products`");
    }
    
    /**
     * Add default Russian warehouses
     */
    private function addDefaultWarehouses() {
        $warehouses = array(
            array(
                'warehouse_id' => 'moscow_fbo_001',
                'name' => 'Москва FBO',
                'city' => 'Москва',
                'region' => 'Московская область',
                'delivery_days' => 2
            ),
            array(
                'warehouse_id' => 'spb_fbo_001',
                'name' => 'Санкт-Петербург FBO',
                'city' => 'Санкт-Петербург',
                'region' => 'Ленинградская область',
                'delivery_days' => 3
            ),
            array(
                'warehouse_id' => 'ekb_fbo_001',
                'name' => 'Екатеринбург FBO',
                'city' => 'Екатеринбург',
                'region' => 'Свердловская область',
                'delivery_days' => 4
            )
        );
        
        foreach ($warehouses as $warehouse) {
            $this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "ozon_warehouses` 
                (`warehouse_id`, `name`, `city`, `region`, `delivery_days`) VALUES 
                ('" . $this->db->escape($warehouse['warehouse_id']) . "', 
                 '" . $this->db->escape($warehouse['name']) . "', 
                 '" . $this->db->escape($warehouse['city']) . "', 
                 '" . $this->db->escape($warehouse['region']) . "', 
                 " . (int)$warehouse['delivery_days'] . ")");
        }
    }
    
    /**
     * Get products that need to be synced to Ozon
     */
    public function getProductsForSync($limit = 100) {
        $query = $this->db->query("SELECT p.product_id, pd.name, p.model, p.sku, p.price, p.quantity, p.status,
                                         op.id as ozon_id, op.status as ozon_status, op.last_sync
                                  FROM `" . DB_PREFIX . "product` p
                                  LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
                                  LEFT JOIN `" . DB_PREFIX . "ozon_products` op ON (p.product_id = op.product_id)
                                  WHERE p.status = 1 
                                  AND (op.status IS NULL OR op.status = 'pending' OR op.last_sync < DATE_SUB(NOW(), INTERVAL 24 HOUR))
                                  ORDER BY p.date_modified DESC
                                  LIMIT " . (int)$limit);
        
        return $query->rows;
    }
    
    /**
     * Get products that need price updates
     */
    public function getProductsForPriceUpdate($limit = 200) {
        $query = $this->db->query("SELECT p.product_id, pd.name, p.model, p.sku, p.price, 
                                         op.price as ozon_price, op.ozon_product_id, op.last_price_update
                                  FROM `" . DB_PREFIX . "product` p
                                  LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
                                  INNER JOIN `" . DB_PREFIX . "ozon_products` op ON (p.product_id = op.product_id)
                                  WHERE p.status = 1 
                                  AND op.status = 'synced'
                                  AND op.ozon_product_id IS NOT NULL
                                  AND (op.price != p.price OR op.last_price_update < DATE_SUB(NOW(), INTERVAL 6 HOUR))
                                  ORDER BY op.last_price_update ASC
                                  LIMIT " . (int)$limit);
        
        return $query->rows;
    }
    
    /**
     * Get products for FBO upload
     */
    public function getProductsForFboUpload($limit = 50) {
        $query = $this->db->query("SELECT p.product_id, pd.name, p.model, p.sku, p.price, p.quantity,
                                         op.ozon_product_id, op.fbo_status, op.warehouse_id
                                  FROM `" . DB_PREFIX . "product` p
                                  LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
                                  INNER JOIN `" . DB_PREFIX . "ozon_products` op ON (p.product_id = op.product_id)
                                  WHERE p.status = 1 
                                  AND p.quantity > 0
                                  AND op.status = 'synced'
                                  AND op.ozon_product_id IS NOT NULL
                                  AND (op.fbo_status = 'none' OR op.fbo_status = 'pending')
                                  ORDER BY p.quantity DESC
                                  LIMIT " . (int)$limit);
        
        return $query->rows;
    }
    
    /**
     * Update product sync status
     */
    public function updateProductSyncStatus($product_id, $status, $ozon_product_id = null) {
        $sql = "INSERT INTO `" . DB_PREFIX . "ozon_products` (product_id, status, last_sync";
        
        if ($ozon_product_id) {
            $sql .= ", ozon_product_id";
        }
        
        $sql .= ") VALUES (" . (int)$product_id . ", '" . $this->db->escape($status) . "', NOW()";
        
        if ($ozon_product_id) {
            $sql .= ", '" . $this->db->escape($ozon_product_id) . "'";
        }
        
        $sql .= ") ON DUPLICATE KEY UPDATE status = '" . $this->db->escape($status) . "', last_sync = NOW()";
        
        if ($ozon_product_id) {
            $sql .= ", ozon_product_id = '" . $this->db->escape($ozon_product_id) . "'";
        }
        
        $this->db->query($sql);
        
        // Log the operation
        $this->logSyncOperation('product_sync', $product_id, 'success', 'Product sync status updated to: ' . $status);
    }
    
    /**
     * Update product sync time
     */
    public function updateProductSyncTime($product_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . "ozon_products` 
                         SET last_price_update = NOW() 
                         WHERE product_id = " . (int)$product_id);
    }
    
    /**
     * Update product FBO status
     */
    public function updateProductFboStatus($product_id, $fbo_status, $warehouse_id = null) {
        $sql = "UPDATE `" . DB_PREFIX . "ozon_products` 
                SET fbo_status = '" . $this->db->escape($fbo_status) . "'";
        
        if ($warehouse_id) {
            $sql .= ", warehouse_id = '" . $this->db->escape($warehouse_id) . "'";
        }
        
        $sql .= " WHERE product_id = " . (int)$product_id;
        
        $this->db->query($sql);
        
        // Log the operation
        $this->logSyncOperation('fbo_upload', $product_id, 'success', 'FBO status updated to: ' . $fbo_status);
    }
    
    /**
     * Get dashboard metrics
     */
    public function getTotalProducts() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ozon_products`");
        return (int)$query->row['total'];
    }
    
    public function getSyncedProducts() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ozon_products` WHERE status = 'synced'");
        return (int)$query->row['total'];
    }
    
    public function getPendingProducts() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ozon_products` WHERE status = 'pending'");
        return (int)$query->row['total'];
    }
    
    public function getMonthlyOrders() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ozon_orders` 
                                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        return (int)$query->row['total'];
    }
    
    public function getMonthlyRevenue() {
        $query = $this->db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM `" . DB_PREFIX . "ozon_orders` 
                                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status != 'cancelled'");
        return (float)$query->row['total'];
    }
    
    public function getLastSyncTime() {
        $query = $this->db->query("SELECT MAX(last_sync) as last_sync FROM `" . DB_PREFIX . "ozon_products`");
        return $query->row['last_sync'];
    }
    
    /**
     * Manage Ozon orders
     */
    public function saveOrder($order_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . "ozon_orders` 
                (ozon_order_id, ozon_order_number, status, delivery_method, warehouse_id, 
                 total_amount, currency, commission_amount, delivery_price, in_process_at, 
                 shipment_date, delivering_date) 
                VALUES 
                ('" . $this->db->escape($order_data['order_id']) . "',
                 '" . $this->db->escape($order_data['order_number']) . "',
                 '" . $this->db->escape($order_data['status']) . "',
                 '" . $this->db->escape($order_data['delivery_method']['name']) . "',
                 '" . $this->db->escape($order_data['delivery_method']['warehouse_id']) . "',
                 " . (float)$order_data['total_amount'] . ",
                 '" . $this->db->escape($order_data['currency']) . "',
                 " . (float)$order_data['commission_amount'] . ",
                 " . (float)$order_data['delivery_price'] . ",
                 " . (isset($order_data['in_process_at']) ? "'" . $this->db->escape($order_data['in_process_at']) . "'" : "NULL") . ",
                 " . (isset($order_data['shipment_date']) ? "'" . $this->db->escape($order_data['shipment_date']) . "'" : "NULL") . ",
                 " . (isset($order_data['delivering_date']) ? "'" . $this->db->escape($order_data['delivering_date']) . "'" : "NULL") . ")
                ON DUPLICATE KEY UPDATE
                status = '" . $this->db->escape($order_data['status']) . "',
                total_amount = " . (float)$order_data['total_amount'] . ",
                commission_amount = " . (float)$order_data['commission_amount'] . ",
                delivery_price = " . (float)$order_data['delivery_price'] . ",
                updated_at = NOW()";
        
        $this->db->query($sql);
        
        // Save order items
        if (isset($order_data['products']) && is_array($order_data['products'])) {
            foreach ($order_data['products'] as $product) {
                $this->saveOrderItem($order_data['order_id'], $product);
            }
        }
    }
    
    /**
     * Save order item
     */
    private function saveOrderItem($ozon_order_id, $product_data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "ozon_order_items` 
                         (ozon_order_id, ozon_product_id, sku, name, quantity, price, total_discount_value, currency)
                         VALUES 
                         ('" . $this->db->escape($ozon_order_id) . "',
                          '" . $this->db->escape($product_data['offer_id']) . "',
                          '" . $this->db->escape($product_data['sku']) . "',
                          '" . $this->db->escape($product_data['name']) . "',
                          " . (int)$product_data['quantity'] . ",
                          " . (float)$product_data['price'] . ",
                          " . (float)$product_data['total_discount_value'] . ",
                          '" . $this->db->escape($product_data['currency']) . "')");
    }
    
    /**
     * Get FBO warehouses
     */
    public function getWarehouses() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ozon_warehouses` WHERE status = 'active' ORDER BY name");
        return $query->rows;
    }
    
    /**
     * Update warehouse information
     */
    public function updateWarehouse($warehouse_data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "ozon_warehouses` 
                         (warehouse_id, name, status, min_amount, has_entrusted_acceptance, city, region, delivery_days)
                         VALUES 
                         ('" . $this->db->escape($warehouse_data['warehouse_id']) . "',
                          '" . $this->db->escape($warehouse_data['name']) . "',
                          '" . $this->db->escape($warehouse_data['status']) . "',
                          " . (isset($warehouse_data['min_amount']) ? (float)$warehouse_data['min_amount'] : "NULL") . ",
                          " . (int)$warehouse_data['has_entrusted_acceptance'] . ",
                          '" . $this->db->escape($warehouse_data['city']) . "',
                          '" . $this->db->escape($warehouse_data['region']) . "',
                          " . (int)$warehouse_data['delivery_days'] . ")
                         ON DUPLICATE KEY UPDATE
                         name = '" . $this->db->escape($warehouse_data['name']) . "',
                         status = '" . $this->db->escape($warehouse_data['status']) . "',
                         min_amount = " . (isset($warehouse_data['min_amount']) ? (float)$warehouse_data['min_amount'] : "NULL") . ",
                         has_entrusted_acceptance = " . (int)$warehouse_data['has_entrusted_acceptance'] . ",
                         city = '" . $this->db->escape($warehouse_data['city']) . "',
                         region = '" . $this->db->escape($warehouse_data['region']) . "',
                         delivery_days = " . (int)$warehouse_data['delivery_days'] . ",
                         updated_at = NOW()");
    }
    
    /**
     * Log sync operations for debugging and monitoring
     */
    public function logSyncOperation($operation_type, $product_id, $status, $message, $response_data = null, $execution_time = null) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "ozon_sync_log` 
                         (operation_type, product_id, status, message, response_data, execution_time)
                         VALUES 
                         ('" . $this->db->escape($operation_type) . "',
                          " . ($product_id ? (int)$product_id : "NULL") . ",
                          '" . $this->db->escape($status) . "',
                          '" . $this->db->escape($message) . "',
                          " . ($response_data ? "'" . $this->db->escape(json_encode($response_data)) . "'" : "NULL") . ",
                          " . ($execution_time ? (float)$execution_time : "NULL") . ")");
    }
    
    /**
     * Get sync logs for monitoring
     */
    public function getSyncLogs($limit = 100, $operation_type = null) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "ozon_sync_log`";
        
        if ($operation_type) {
            $sql .= " WHERE operation_type = '" . $this->db->escape($operation_type) . "'";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT " . (int)$limit;
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Clean old sync logs (keep last 30 days)
     */
    public function cleanSyncLogs() {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "ozon_sync_log` WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    }
    
    /**
     * Get Ozon product data
     */
    public function getOzonProduct($product_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ozon_products` WHERE product_id = " . (int)$product_id);
        return $query->row;
    }
    
    /**
     * Update Ozon product data
     */
    public function updateOzonProduct($product_id, $data) {
        $fields = array();
        
        foreach ($data as $key => $value) {
            if (in_array($key, array('ozon_product_id', 'ozon_sku', 'fbo_sku', 'offer_id', 'category_id', 'warehouse_id', 'status', 'fbo_status', 'visibility'))) {
                $fields[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
            } elseif (in_array($key, array('price', 'old_price', 'premium_price', 'min_price', 'marketing_price'))) {
                $fields[] = "`" . $key . "` = " . (float)$value;
            } elseif (in_array($key, array('stock_fbo', 'stock_fbs'))) {
                $fields[] = "`" . $key . "` = " . (int)$value;
            }
        }
        
        if (!empty($fields)) {
            $sql = "INSERT INTO `" . DB_PREFIX . "ozon_products` (product_id, " . implode(', ', array_keys($data)) . ") 
                    VALUES (" . (int)$product_id . ", " . implode(', ', array_values($data)) . ")
                    ON DUPLICATE KEY UPDATE " . implode(', ', $fields) . ", updated_at = NOW()";
            
            $this->db->query($sql);
        }
    }
} 