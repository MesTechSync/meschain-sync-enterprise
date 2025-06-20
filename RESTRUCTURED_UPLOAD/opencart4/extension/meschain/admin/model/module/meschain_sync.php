<?php
namespace Opencart\Admin\Model\Extension\Meschain\Module;

class MeschainSync extends \Opencart\System\Engine\Model {
    
    public function install(): void {
        // Create MesChain marketplaces table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_marketplaces` (
            `marketplace_id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `code` varchar(50) NOT NULL UNIQUE,
            `api_endpoint` varchar(255) NOT NULL,
            `api_key` varchar(255),
            `api_secret` varchar(255),
            `commission_rate` decimal(5,2) DEFAULT 0.00,
            `status` tinyint(1) DEFAULT 1,
            `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`marketplace_id`),
            UNIQUE KEY `code` (`code`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        // Create MesChain products table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_products` (
            `meschain_product_id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `marketplace_id` int(11) NOT NULL,
            `marketplace_product_id` varchar(100),
            `marketplace_sku` varchar(100),
            `sync_status` enum('pending','synced','error') DEFAULT 'pending',
            `last_sync` datetime,
            `sync_error` text,
            `price` decimal(15,4),
            `stock_quantity` int(11),
            `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`meschain_product_id`),
            KEY `product_id` (`product_id`),
            KEY `marketplace_id` (`marketplace_id`),
            KEY `sync_status` (`sync_status`),
            KEY `last_sync` (`last_sync`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        // Create MesChain orders table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_orders` (
            `meschain_order_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11),
            `marketplace_id` int(11) NOT NULL,
            `marketplace_order_id` varchar(100) NOT NULL,
            `sync_status` enum('pending','imported','error') DEFAULT 'pending',
            `order_data` text,
            `import_status` enum('new','processing','shipped','delivered','cancelled') DEFAULT 'new',
            `last_import` datetime,
            `import_error` text,
            `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`meschain_order_id`),
            KEY `order_id` (`order_id`),
            KEY `marketplace_id` (`marketplace_id`),
            KEY `marketplace_order_id` (`marketplace_order_id`),
            KEY `sync_status` (`sync_status`),
            KEY `import_status` (`import_status`),
            KEY `last_import` (`last_import`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        // Create MesChain logs table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_logs` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `marketplace_id` int(11),
            `type` varchar(50) NOT NULL,
            `action` varchar(100) NOT NULL,
            `message` text NOT NULL,
            `level` enum('info','warning','error','debug') DEFAULT 'info',
            `data` text,
            `ip_address` varchar(45),
            `user_id` int(11),
            `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`log_id`),
            KEY `marketplace_id` (`marketplace_id`),
            KEY `type` (`type`),
            KEY `level` (`level`),
            KEY `date_added` (`date_added`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        
        // Check if marketplaces already exist to prevent duplicates
        $existing_count = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_marketplaces`");
        
        if ((int)$existing_count->row['total'] === 0) {
            // Insert default marketplace configurations only if none exist
            $marketplaces = [
                ['name' => 'Trendyol', 'code' => 'trendyol', 'api_endpoint' => 'https://api.trendyol.com', 'commission_rate' => 8.00],
                ['name' => 'Hepsiburada', 'code' => 'hepsiburada', 'api_endpoint' => 'https://mpop-sit.hepsiburada.com', 'commission_rate' => 12.00],
                ['name' => 'Amazon TR', 'code' => 'amazon_tr', 'api_endpoint' => 'https://sellingpartnerapi-eu.amazon.com', 'commission_rate' => 15.00],
                ['name' => 'N11', 'code' => 'n11', 'api_endpoint' => 'https://api.n11.com', 'commission_rate' => 9.00],
                ['name' => 'eBay', 'code' => 'ebay', 'api_endpoint' => 'https://api.ebay.com', 'commission_rate' => 10.00],
                ['name' => 'GittiGidiyor', 'code' => 'gittigidiyor', 'api_endpoint' => 'https://dev.gittigidiyor.com', 'commission_rate' => 5.00],
                ['name' => 'Pazarama', 'code' => 'pazarama', 'api_endpoint' => 'https://isortagim.pazarama.com', 'commission_rate' => 7.00]
            ];

            foreach ($marketplaces as $marketplace) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_marketplaces` 
                    (`name`, `code`, `api_endpoint`, `commission_rate`, `status`, `date_added`, `date_modified`) 
                    VALUES ('" . $this->db->escape($marketplace['name']) . "', 
                           '" . $this->db->escape($marketplace['code']) . "', 
                           '" . $this->db->escape($marketplace['api_endpoint']) . "', 
                           " . (float)$marketplace['commission_rate'] . ", 1, NOW(), NOW())");
            }
        }
    }

    public function uninstall(): void {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_products`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_orders`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_logs`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_marketplaces`");
    }

    public function getStats(): array {
        $stats = [];
        
        // Total products
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "product`");
        $stats['total_products'] = $query->row['total'];
        
        // Synced products
        $query = $this->db->query("SELECT COUNT(DISTINCT product_id) as synced FROM `" . DB_PREFIX . "meschain_products` WHERE sync_status = 'synced'");
        $stats['synced_products'] = $query->row['synced'];
        
        // Pending orders
        $query = $this->db->query("SELECT COUNT(*) as pending FROM `" . DB_PREFIX . "meschain_orders` WHERE sync_status = 'pending'");
        $stats['pending_orders'] = $query->row['pending'];
        
        // Last sync
        $query = $this->db->query("SELECT MAX(last_sync) as last_sync FROM `" . DB_PREFIX . "meschain_products`");
        $stats['last_sync'] = $query->row['last_sync'] ?: 'Never';
        
        return $stats;
    }

    // Removed duplicate getMarketplaces method

    public function log(string $type, string $action, string $message, array $data = [], ?int $marketplace_id = null, string $level = 'info'): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_logs` 
            SET `marketplace_id` = " . (int)$marketplace_id . ", 
                `type` = '" . $this->db->escape($type) . "', 
                `action` = '" . $this->db->escape($action) . "', 
                `message` = '" . $this->db->escape($message) . "', 
                `level` = '" . $this->db->escape($level) . "', 
                `data` = '" . $this->db->escape(json_encode($data)) . "', 
                `ip_address` = '" . $this->db->escape($this->request->server['REMOTE_ADDR'] ?? '') . "', 
                `user_id` = " . (int)($this->user->getId() ?? 0));
    }

    public function getTotalMarketplaces(): int {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_marketplaces` WHERE `status` = 1");
        return (int)$query->row['total'];
    }

    public function getTotalProducts(): int {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_products`");
        return (int)$query->row['total'];
    }

         public function getTotalOrders(): int {
         $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_orders`");
         return (int)$query->row['total'];
     }

     public function getMarketplaces(): array {
         $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_marketplaces` ORDER BY `name`");
         
         $marketplaces = [];
         foreach ($query->rows as $row) {
             $marketplaces[] = [
                 'marketplace_id' => $row['marketplace_id'],
                 'name' => $row['name'],
                 'code' => $row['code'],
                 'status' => $row['status'],
                 'api_endpoint' => $row['api_endpoint'],
                 'commission_rate' => $row['commission_rate'],
                 'product_count' => $this->getMarketplaceProductCount($row['marketplace_id']),
                 'order_count' => $this->getMarketplaceOrderCount($row['marketplace_id']),
                 'last_sync' => $this->getMarketplaceLastSync($row['marketplace_id'])
             ];
         }
         
         return $marketplaces;
     }

     private function getMarketplaceProductCount(int $marketplace_id): int {
         $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_products` WHERE `marketplace_id` = " . (int)$marketplace_id);
         return (int)$query->row['total'];
     }

     private function getMarketplaceOrderCount(int $marketplace_id): int {
         $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_orders` WHERE `marketplace_id` = " . (int)$marketplace_id);
         return (int)$query->row['total'];
     }

     private function getMarketplaceLastSync(int $marketplace_id): ?string {
         $query = $this->db->query("SELECT MAX(last_sync) as last_sync FROM `" . DB_PREFIX . "meschain_products` WHERE `marketplace_id` = " . (int)$marketplace_id . " AND last_sync IS NOT NULL");
         return $query->row['last_sync'] ?? null;
     }

    public function getAnalytics(): array {
        // Sales analytics
        $sales_query = $this->db->query("SELECT 
            COUNT(*) as total_orders,
            SUM(CASE WHEN sync_status = 'imported' THEN 1 ELSE 0 END) as imported_orders,
            SUM(CASE WHEN sync_status = 'error' THEN 1 ELSE 0 END) as error_orders
            FROM `" . DB_PREFIX . "meschain_orders`");
        
        // Product analytics
        $products_query = $this->db->query("SELECT 
            COUNT(*) as total_products,
            SUM(CASE WHEN sync_status = 'synced' THEN 1 ELSE 0 END) as synced_products,
            SUM(CASE WHEN sync_status = 'error' THEN 1 ELSE 0 END) as error_products
            FROM `" . DB_PREFIX . "meschain_products`");
        
        // Marketplace performance
        $marketplace_query = $this->db->query("SELECT 
            m.name,
            m.code,
            COUNT(DISTINCT p.meschain_product_id) as product_count,
            COUNT(DISTINCT o.meschain_order_id) as order_count
            FROM `" . DB_PREFIX . "meschain_marketplaces` m
            LEFT JOIN `" . DB_PREFIX . "meschain_products` p ON m.marketplace_id = p.marketplace_id
            LEFT JOIN `" . DB_PREFIX . "meschain_orders` o ON m.marketplace_id = o.marketplace_id
            WHERE m.status = 1
            GROUP BY m.marketplace_id
            ORDER BY order_count DESC");
        
        // Recent activity
        $activity_query = $this->db->query("SELECT 
            level as type, message, date_added 
            FROM `" . DB_PREFIX . "meschain_logs` 
            ORDER BY date_added DESC 
            LIMIT 20");
        
        return [
            'sales' => $sales_query->row,
            'products' => $products_query->row,
            'marketplaces' => $marketplace_query->rows,
            'recent_activity' => $activity_query->rows,
            'sync_health' => $this->getSyncHealth()
        ];
    }

    private function getSyncHealth(): array {
        $total_query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_products`");
        $error_query = $this->db->query("SELECT COUNT(*) as errors FROM `" . DB_PREFIX . "meschain_products` WHERE sync_status = 'error'");
        
        $total = (int)$total_query->row['total'];
        $errors = (int)$error_query->row['errors'];
        
        $health_percentage = $total > 0 ? round((($total - $errors) / $total) * 100, 1) : 100;
        
        return [
            'total_products' => $total,
            'error_count' => $errors,
            'health_percentage' => $health_percentage,
            'status' => $health_percentage >= 90 ? 'excellent' : ($health_percentage >= 70 ? 'good' : 'needs_attention')
        ];
    }
}
