<?php
/**
 * MesChain-Sync Çiçek Sepeti Model
 * 
 * @package     MesChain-Sync
 * @subpackage  Çiçek Sepeti Model
 * @category    Marketplace Integration
 * @author      MesChain Development Team
 * @copyright   2024 MesChain-Sync
 * @license     Commercial License
 * @version     1.0.0
 * @since       1.0.0
 */

class ModelExtensionModuleCiceksepeti extends Model {
    
    /**
     * Install Çiçek Sepeti tables
     * 
     * @return void
     */
    public function install() {
        try {
            // Çiçek Sepeti Products Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ciceksepeti_products` (
                    `ciceksepeti_product_id` int(11) NOT NULL AUTO_INCREMENT,
                    `product_id` int(11) NOT NULL,
                    `ciceksepeti_id` varchar(255) NOT NULL,
                    `status` tinyint(1) NOT NULL DEFAULT '1',
                    `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
                    `stock_quantity` int(11) NOT NULL DEFAULT '0',
                    `category_id` varchar(100) DEFAULT NULL,
                    `flower_type` enum('flower','plant','accessory','special_occasion') DEFAULT 'flower',
                    `last_sync` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`ciceksepeti_product_id`),
                    UNIQUE KEY `product_id` (`product_id`),
                    KEY `ciceksepeti_id` (`ciceksepeti_id`),
                    KEY `status` (`status`),
                    KEY `flower_type` (`flower_type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
            ");

            // Çiçek Sepeti Orders Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ciceksepeti_orders` (
                    `ciceksepeti_order_id` int(11) NOT NULL AUTO_INCREMENT,
                    `order_id` int(11) DEFAULT NULL,
                    `ciceksepeti_order_number` varchar(255) NOT NULL,
                    `ciceksepeti_status` varchar(100) NOT NULL,
                    `customer_name` varchar(255) NOT NULL,
                    `customer_email` varchar(255) DEFAULT NULL,
                    `customer_phone` varchar(50) DEFAULT NULL,
                    `delivery_address` text DEFAULT NULL,
                    `delivery_date` date DEFAULT NULL,
                    `delivery_time` varchar(50) DEFAULT NULL,
                    `gift_message` text DEFAULT NULL,
                    `total_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
                    `currency` varchar(3) NOT NULL DEFAULT 'TRY',
                    `order_date` datetime NOT NULL,
                    `sync_status` enum('pending','synced','failed') NOT NULL DEFAULT 'pending',
                    `sync_date` datetime DEFAULT NULL,
                    `notes` text DEFAULT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`ciceksepeti_order_id`),
                    UNIQUE KEY `ciceksepeti_order_number` (`ciceksepeti_order_number`),
                    KEY `order_id` (`order_id`),
                    KEY `sync_status` (`sync_status`),
                    KEY `delivery_date` (`delivery_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
            ");

            // Çiçek Sepeti Categories Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ciceksepeti_categories` (
                    `ciceksepeti_category_id` int(11) NOT NULL AUTO_INCREMENT,
                    `category_id` int(11) NOT NULL,
                    `ciceksepeti_id` varchar(100) NOT NULL,
                    `category_name` varchar(255) NOT NULL,
                    `flower_type` enum('flower','plant','accessory','special_occasion') NOT NULL,
                    `status` tinyint(1) NOT NULL DEFAULT '1',
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`ciceksepeti_category_id`),
                    UNIQUE KEY `category_mapping` (`category_id`, `flower_type`),
                    KEY `ciceksepeti_id` (`ciceksepeti_id`),
                    KEY `flower_type` (`flower_type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
            ");

            // Çiçek Sepeti Logs Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ciceksepeti_logs` (
                    `log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `type` enum('info','warning','error','success') NOT NULL DEFAULT 'info',
                    `action` varchar(100) NOT NULL,
                    `message` text NOT NULL,
                    `data` longtext DEFAULT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`log_id`),
                    KEY `type` (`type`),
                    KEY `action` (`action`),
                    KEY `created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
            ");

            $this->log('info', 'install', 'Çiçek Sepeti tables created successfully');
        } catch (Exception $e) {
            $this->log('error', 'install', 'Failed to create Çiçek Sepeti tables: ' . $e->getMessage());
        }
    }

    /**
     * Uninstall Çiçek Sepeti tables
     * 
     * @return void
     */
    public function uninstall() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ciceksepeti_products`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ciceksepeti_orders`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ciceksepeti_categories`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ciceksepeti_logs`");
            
            $this->log('info', 'uninstall', 'Çiçek Sepeti tables dropped successfully');
        } catch (Exception $e) {
            $this->log('error', 'uninstall', 'Failed to drop Çiçek Sepeti tables: ' . $e->getMessage());
        }
    }

    /**
     * Get product by OpenCart product ID
     * 
     * @param int $product_id OpenCart product ID
     * @return array|null Product data or null if not found
     */
    public function getProduct($product_id) {
        try {
            $query = $this->db->query("
                SELECT * FROM `" . DB_PREFIX . "ciceksepeti_products` 
                WHERE `product_id` = '" . (int)$product_id . "'
            ");
            
            return $query->num_rows ? $query->row : null;
        } catch (Exception $e) {
            $this->log('error', 'getProduct', 'Failed to get product: ' . $e->getMessage(), ['product_id' => $product_id]);
            return null;
        }
    }

    /**
     * Get product by Çiçek Sepeti ID
     * 
     * @param string $ciceksepeti_id Çiçek Sepeti product ID
     * @return array|null Product data or null if not found
     */
    public function getProductByCiceksepetiId($ciceksepeti_id) {
        try {
            $query = $this->db->query("
                SELECT * FROM `" . DB_PREFIX . "ciceksepeti_products` 
                WHERE `ciceksepeti_id` = '" . $this->db->escape($ciceksepeti_id) . "'
            ");
            
            return $query->num_rows ? $query->row : null;
        } catch (Exception $e) {
            $this->log('error', 'getProductByCiceksepetiId', 'Failed to get product by Çiçek Sepeti ID: ' . $e->getMessage(), ['ciceksepeti_id' => $ciceksepeti_id]);
            return null;
        }
    }

    /**
     * Add or update product
     * 
     * @param array $data Product data
     * @return bool Success status
     */
    public function addProduct($data) {
        try {
            if (!isset($data['product_id']) || !isset($data['ciceksepeti_id'])) {
                throw new Exception('Required fields missing: product_id, ciceksepeti_id');
            }

            $existing = $this->getProduct($data['product_id']);
            
            if ($existing) {
                return $this->updateProduct($data);
            }

            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "ciceksepeti_products` SET
                `product_id` = '" . (int)$data['product_id'] . "',
                `ciceksepeti_id` = '" . $this->db->escape($data['ciceksepeti_id']) . "',
                `status` = '" . (int)($data['status'] ?? 1) . "',
                `price` = '" . (float)($data['price'] ?? 0) . "',
                `stock_quantity` = '" . (int)($data['stock_quantity'] ?? 0) . "',
                `category_id` = '" . $this->db->escape($data['category_id'] ?? '') . "',
                `flower_type` = '" . $this->db->escape($data['flower_type'] ?? 'flower') . "',
                `last_sync` = NOW()
            ");

            $this->log('success', 'addProduct', 'Product added successfully', $data);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'addProduct', 'Failed to add product: ' . $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Update product
     * 
     * @param array $data Product data
     * @return bool Success status
     */
    public function updateProduct($data) {
        try {
            if (!isset($data['product_id'])) {
                throw new Exception('Product ID is required');
            }

            $sql = "UPDATE `" . DB_PREFIX . "ciceksepeti_products` SET ";
            $updates = [];

            if (isset($data['ciceksepeti_id'])) {
                $updates[] = "`ciceksepeti_id` = '" . $this->db->escape($data['ciceksepeti_id']) . "'";
            }
            if (isset($data['status'])) {
                $updates[] = "`status` = '" . (int)$data['status'] . "'";
            }
            if (isset($data['price'])) {
                $updates[] = "`price` = '" . (float)$data['price'] . "'";
            }
            if (isset($data['stock_quantity'])) {
                $updates[] = "`stock_quantity` = '" . (int)$data['stock_quantity'] . "'";
            }
            if (isset($data['category_id'])) {
                $updates[] = "`category_id` = '" . $this->db->escape($data['category_id']) . "'";
            }
            if (isset($data['flower_type'])) {
                $updates[] = "`flower_type` = '" . $this->db->escape($data['flower_type']) . "'";
            }
            
            $updates[] = "`last_sync` = NOW()";

            if (empty($updates)) {
                throw new Exception('No data to update');
            }

            $sql .= implode(', ', $updates);
            $sql .= " WHERE `product_id` = '" . (int)$data['product_id'] . "'";

            $this->db->query($sql);

            $this->log('success', 'updateProduct', 'Product updated successfully', $data);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'updateProduct', 'Failed to update product: ' . $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Delete product
     * 
     * @param int $product_id OpenCart product ID
     * @return bool Success status
     */
    public function deleteProduct($product_id) {
        try {
            $this->db->query("
                DELETE FROM `" . DB_PREFIX . "ciceksepeti_products` 
                WHERE `product_id` = '" . (int)$product_id . "'
            ");

            $this->log('success', 'deleteProduct', 'Product deleted successfully', ['product_id' => $product_id]);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'deleteProduct', 'Failed to delete product: ' . $e->getMessage(), ['product_id' => $product_id]);
            return false;
        }
    }

    /**
     * Get products by flower type
     * 
     * @param string $flower_type Flower type (flower, plant, accessory, special_occasion)
     * @param array $data Query parameters
     * @return array Products list
     */
    public function getProductsByFlowerType($flower_type, $data = []) {
        try {
            $sql = "SELECT cp.*, p.name, p.model, p.sku, p.price as opencart_price, p.quantity as opencart_quantity 
                    FROM `" . DB_PREFIX . "ciceksepeti_products` cp
                    LEFT JOIN `" . DB_PREFIX . "product_description` p ON (cp.product_id = p.product_id)
                    WHERE p.language_id = '" . (int)$this->config->get('config_language_id') . "'
                    AND cp.flower_type = '" . $this->db->escape($flower_type) . "'";

            if (isset($data['filter_status'])) {
                $sql .= " AND cp.status = '" . (int)$data['filter_status'] . "'";
            }

            $sql .= " ORDER BY cp.last_sync DESC";

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $query = $this->db->query($sql);
            return $query->rows;
        } catch (Exception $e) {
            $this->log('error', 'getProductsByFlowerType', 'Failed to get products by flower type: ' . $e->getMessage(), ['flower_type' => $flower_type]);
            return [];
        }
    }

    /**
     * Get order by Çiçek Sepeti order number
     * 
     * @param string $ciceksepeti_order_number Çiçek Sepeti order number
     * @return array|null Order data or null if not found
     */
    public function getOrderByCiceksepetiNumber($ciceksepeti_order_number) {
        try {
            $query = $this->db->query("
                SELECT * FROM `" . DB_PREFIX . "ciceksepeti_orders` 
                WHERE `ciceksepeti_order_number` = '" . $this->db->escape($ciceksepeti_order_number) . "'
            ");
            
            return $query->num_rows ? $query->row : null;
        } catch (Exception $e) {
            $this->log('error', 'getOrderByCiceksepetiNumber', 'Failed to get order: ' . $e->getMessage(), ['ciceksepeti_order_number' => $ciceksepeti_order_number]);
            return null;
        }
    }

    /**
     * Add order
     * 
     * @param array $data Order data
     * @return bool Success status
     */
    public function addOrder($data) {
        try {
            if (!isset($data['ciceksepeti_order_number'])) {
                throw new Exception('Çiçek Sepeti order number is required');
            }

            $existing = $this->getOrderByCiceksepetiNumber($data['ciceksepeti_order_number']);
            if ($existing) {
                return $this->updateOrder($data);
            }

            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "ciceksepeti_orders` SET
                `order_id` = " . (isset($data['order_id']) ? "'" . (int)$data['order_id'] . "'" : "NULL") . ",
                `ciceksepeti_order_number` = '" . $this->db->escape($data['ciceksepeti_order_number']) . "',
                `ciceksepeti_status` = '" . $this->db->escape($data['ciceksepeti_status'] ?? 'unknown') . "',
                `customer_name` = '" . $this->db->escape($data['customer_name'] ?? '') . "',
                `customer_email` = '" . $this->db->escape($data['customer_email'] ?? '') . "',
                `customer_phone` = '" . $this->db->escape($data['customer_phone'] ?? '') . "',
                `delivery_address` = '" . $this->db->escape($data['delivery_address'] ?? '') . "',
                `delivery_date` = " . (isset($data['delivery_date']) ? "'" . $this->db->escape($data['delivery_date']) . "'" : "NULL") . ",
                `delivery_time` = '" . $this->db->escape($data['delivery_time'] ?? '') . "',
                `gift_message` = '" . $this->db->escape($data['gift_message'] ?? '') . "',
                `total_amount` = '" . (float)($data['total_amount'] ?? 0) . "',
                `currency` = '" . $this->db->escape($data['currency'] ?? 'TRY') . "',
                `order_date` = '" . $this->db->escape($data['order_date'] ?? date('Y-m-d H:i:s')) . "',
                `sync_status` = '" . $this->db->escape($data['sync_status'] ?? 'pending') . "',
                `notes` = '" . $this->db->escape($data['notes'] ?? '') . "'
            ");

            $this->log('success', 'addOrder', 'Order added successfully', $data);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'addOrder', 'Failed to add order: ' . $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Update order
     * 
     * @param array $data Order data
     * @return bool Success status
     */
    public function updateOrder($data) {
        try {
            if (!isset($data['ciceksepeti_order_number'])) {
                throw new Exception('Çiçek Sepeti order number is required');
            }

            $sql = "UPDATE `" . DB_PREFIX . "ciceksepeti_orders` SET ";
            $updates = [];

            if (isset($data['order_id'])) {
                $updates[] = "`order_id` = '" . (int)$data['order_id'] . "'";
            }
            if (isset($data['ciceksepeti_status'])) {
                $updates[] = "`ciceksepeti_status` = '" . $this->db->escape($data['ciceksepeti_status']) . "'";
            }
            if (isset($data['sync_status'])) {
                $updates[] = "`sync_status` = '" . $this->db->escape($data['sync_status']) . "'";
            }
            if (isset($data['sync_date'])) {
                $updates[] = "`sync_date` = '" . $this->db->escape($data['sync_date']) . "'";
            }
            if (isset($data['delivery_date'])) {
                $updates[] = "`delivery_date` = '" . $this->db->escape($data['delivery_date']) . "'";
            }
            if (isset($data['notes'])) {
                $updates[] = "`notes` = '" . $this->db->escape($data['notes']) . "'";
            }

            if (empty($updates)) {
                throw new Exception('No data to update');
            }

            $sql .= implode(', ', $updates);
            $sql .= " WHERE `ciceksepeti_order_number` = '" . $this->db->escape($data['ciceksepeti_order_number']) . "'";

            $this->db->query($sql);

            $this->log('success', 'updateOrder', 'Order updated successfully', $data);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'updateOrder', 'Failed to update order: ' . $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Get category mapping
     * 
     * @param int $category_id OpenCart category ID
     * @param string $flower_type Flower type
     * @return array|null Category mapping or null if not found
     */
    public function getCategoryMapping($category_id, $flower_type) {
        try {
            $query = $this->db->query("
                SELECT * FROM `" . DB_PREFIX . "ciceksepeti_categories` 
                WHERE `category_id` = '" . (int)$category_id . "' 
                AND `flower_type` = '" . $this->db->escape($flower_type) . "'
            ");
            
            return $query->num_rows ? $query->row : null;
        } catch (Exception $e) {
            $this->log('error', 'getCategoryMapping', 'Failed to get category mapping: ' . $e->getMessage(), ['category_id' => $category_id, 'flower_type' => $flower_type]);
            return null;
        }
    }

    /**
     * Add category mapping
     * 
     * @param array $data Category mapping data
     * @return bool Success status
     */
    public function addCategoryMapping($data) {
        try {
            if (!isset($data['category_id']) || !isset($data['flower_type']) || !isset($data['ciceksepeti_id'])) {
                throw new Exception('Required fields missing: category_id, flower_type, ciceksepeti_id');
            }

            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "ciceksepeti_categories` SET
                `category_id` = '" . (int)$data['category_id'] . "',
                `ciceksepeti_id` = '" . $this->db->escape($data['ciceksepeti_id']) . "',
                `category_name` = '" . $this->db->escape($data['category_name'] ?? '') . "',
                `flower_type` = '" . $this->db->escape($data['flower_type']) . "',
                `status` = '" . (int)($data['status'] ?? 1) . "'
                ON DUPLICATE KEY UPDATE
                `ciceksepeti_id` = VALUES(`ciceksepeti_id`),
                `category_name` = VALUES(`category_name`),
                `status` = VALUES(`status`)
            ");

            $this->log('success', 'addCategoryMapping', 'Category mapping added successfully', $data);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'addCategoryMapping', 'Failed to add category mapping: ' . $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Add log entry
     * 
     * @param string $type Log type (info, warning, error, success)
     * @param string $action Action name
     * @param string $message Log message
     * @param array $data Additional data
     * @return void
     */
    public function log($type, $action, $message, $data = null) {
        try {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "ciceksepeti_logs` SET
                `type` = '" . $this->db->escape($type) . "',
                `action` = '" . $this->db->escape($action) . "',
                `message` = '" . $this->db->escape($message) . "',
                `data` = " . ($data ? "'" . $this->db->escape(json_encode($data)) . "'" : "NULL") . "
            ");
        } catch (Exception $e) {
            // Silent fail to prevent infinite loops
            error_log('Çiçek Sepeti Model Log Error: ' . $e->getMessage());
        }
    }

    /**
     * Get flower type statistics
     * 
     * @return array Flower type statistics
     */
    public function getFlowerTypeStatistics() {
        try {
            $stats = [];

            $flower_types = ['flower', 'plant', 'accessory', 'special_occasion'];

            foreach ($flower_types as $type) {
                $query = $this->db->query("
                    SELECT COUNT(*) as total 
                    FROM `" . DB_PREFIX . "ciceksepeti_products` 
                    WHERE `flower_type` = '" . $this->db->escape($type) . "' 
                    AND `status` = 1
                ");
                $stats[$type] = $query->row['total'];
            }

            return $stats;
        } catch (Exception $e) {
            $this->log('error', 'getFlowerTypeStatistics', 'Failed to get flower type statistics: ' . $e->getMessage());
            return [
                'flower' => 0,
                'plant' => 0,
                'accessory' => 0,
                'special_occasion' => 0
            ];
        }
    }

    /**
     * Get statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        try {
            $stats = [];

            // Total products
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ciceksepeti_products`");
            $stats['total_products'] = $query->row['total'];

            // Active products
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ciceksepeti_products` WHERE status = 1");
            $stats['active_products'] = $query->row['total'];

            // Total orders
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ciceksepeti_orders`");
            $stats['total_orders'] = $query->row['total'];

            // Pending orders
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ciceksepeti_orders` WHERE sync_status = 'pending'");
            $stats['pending_orders'] = $query->row['total'];

            // Orders with delivery dates
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ciceksepeti_orders` WHERE delivery_date IS NOT NULL");
            $stats['scheduled_deliveries'] = $query->row['total'];

            // Last sync date
            $query = $this->db->query("SELECT MAX(last_sync) as last_sync FROM `" . DB_PREFIX . "ciceksepeti_products`");
            $stats['last_sync'] = $query->row['last_sync'];

            // Flower type statistics
            $stats['flower_types'] = $this->getFlowerTypeStatistics();

            return $stats;
        } catch (Exception $e) {
            $this->log('error', 'getStatistics', 'Failed to get statistics: ' . $e->getMessage());
            return [
                'total_products' => 0,
                'active_products' => 0,
                'total_orders' => 0,
                'pending_orders' => 0,
                'scheduled_deliveries' => 0,
                'last_sync' => null,
                'flower_types' => [
                    'flower' => 0,
                    'plant' => 0,
                    'accessory' => 0,
                    'special_occasion' => 0
                ]
            ];
        }
    }

    /**
     * Clear old logs
     * 
     * @param int $days Days to keep (default: 30)
     * @return bool Success status
     */
    public function clearOldLogs($days = 30) {
        try {
            $this->db->query("
                DELETE FROM `" . DB_PREFIX . "ciceksepeti_logs` 
                WHERE `created_at` < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            ");

            $this->log('info', 'clearOldLogs', "Cleared logs older than {$days} days");
            return true;
        } catch (Exception $e) {
            $this->log('error', 'clearOldLogs', 'Failed to clear old logs: ' . $e->getMessage());
            return false;
        }
    }
}
?> 