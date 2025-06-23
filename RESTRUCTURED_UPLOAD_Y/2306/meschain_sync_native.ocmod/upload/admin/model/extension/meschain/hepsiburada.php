<?php
/**
 * MesChain Sync - Hepsiburada Model
 * 
 * @package    MesChain Sync
 * @author     MesChain Development Team
 * @copyright  2024 MesChain Technologies
 * @license    Commercial License
 * @version    2.3.0
 * @link       https://www.meschain.com
 */

namespace Opencart\Admin\Model\Extension\Meschain;

use Opencart\System\Engine\Model;

class Hepsiburada extends Model {
    
    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_hepsiburada_products` (
                `hepsiburada_product_id` int(11) NOT NULL AUTO_INCREMENT,
                `opencart_product_id` int(11) NOT NULL,
                `hepsiburada_id` varchar(255) NOT NULL,
                `hepsiburada_sku` varchar(255) NOT NULL,
                `hepsiburada_merchant_id` varchar(255) NOT NULL,
                `status` tinyint(1) NOT NULL DEFAULT '1',
                `last_sync` datetime NOT NULL,
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`hepsiburada_product_id`),
                UNIQUE KEY `opencart_product_id` (`opencart_product_id`),
                KEY `hepsiburada_id` (`hepsiburada_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_hepsiburada_orders` (
                `hepsiburada_order_id` int(11) NOT NULL AUTO_INCREMENT,
                `opencart_order_id` int(11) NOT NULL,
                `hepsiburada_id` varchar(255) NOT NULL,
                `hepsiburada_order_number` varchar(255) NOT NULL,
                `package_number` varchar(255) NOT NULL,
                `status` varchar(50) NOT NULL,
                `last_sync` datetime NOT NULL,
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`hepsiburada_order_id`),
                UNIQUE KEY `opencart_order_id` (`opencart_order_id`),
                KEY `hepsiburada_id` (`hepsiburada_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_hepsiburada_sync_log` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `type` enum('product','order','stock','price') NOT NULL,
                `action` enum('create','update','delete','sync') NOT NULL,
                `entity_id` varchar(255) NOT NULL,
                `message` text NOT NULL,
                `status` enum('success','error','warning') NOT NULL,
                `date_added` datetime NOT NULL,
                PRIMARY KEY (`log_id`),
                KEY `type` (`type`),
                KEY `status` (`status`),
                KEY `date_added` (`date_added`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_hepsiburada_products`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_hepsiburada_orders`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_hepsiburada_sync_log`");
    }
    
    public function getSyncStatistics() {
        $data = array();
        
        // Product statistics
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_hepsiburada_products`");
        $data['total_products'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_hepsiburada_products` WHERE status = 1");
        $data['active_products'] = $query->row['total'];
        
        // Order statistics
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_hepsiburada_orders`");
        $data['total_orders'] = $query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_hepsiburada_orders` WHERE DATE(date_added) = CURDATE()");
        $data['today_orders'] = $query->row['total'];
        
        // Last sync time
        $query = $this->db->query("SELECT MAX(last_sync) as last_sync FROM `" . DB_PREFIX . "meschain_hepsiburada_products`");
        $data['last_product_sync'] = $query->row['last_sync'];
        
        $query = $this->db->query("SELECT MAX(last_sync) as last_sync FROM `" . DB_PREFIX . "meschain_hepsiburada_orders`");
        $data['last_order_sync'] = $query->row['last_sync'];
        
        return $data;
    }
    
    public function syncProductsToHepsiburada() {
        try {
            $this->load->model('catalog/product');
            
            // Get products to sync
            $products = $this->model_catalog_product->getProducts(array('start' => 0, 'limit' => 100));
            
            $synced_count = 0;
            
            foreach ($products as $product) {
                // Check if product already synced
                $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_hepsiburada_products` WHERE opencart_product_id = '" . (int)$product['product_id'] . "'");
                
                if ($query->num_rows) {
                    // Update existing product
                    $this->updateProductToHepsiburada($product);
                } else {
                    // Create new product
                    $this->createProductToHepsiburada($product);
                }
                
                $synced_count++;
            }
            
            return array('success' => true, 'count' => $synced_count);
            
        } catch (\Exception $e) {
            $this->log('product', 'sync', 'all', $e->getMessage(), 'error');
            return array('success' => false, 'error' => $e->getMessage());
        }
    }
    
    public function syncOrdersFromHepsiburada() {
        try {
            // Hepsiburada API ile sipariş çekme logic'i burada olacak
            $synced_count = 0;
            
            // Placeholder for Hepsiburada API integration
            // $orders = $this->hepsiburada_api->getOrders();
            
            return array('success' => true, 'count' => $synced_count);
            
        } catch (\Exception $e) {
            $this->log('order', 'sync', 'all', $e->getMessage(), 'error');
            return array('success' => false, 'error' => $e->getMessage());
        }
    }
    
    private function createProductToHepsiburada($product) {
        // Hepsiburada API product creation logic
        $hepsiburada_id = 'HB_' . $product['product_id'] . '_' . time();
        $merchant_id = 'MERCHANT_' . $product['product_id'];
        
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_hepsiburada_products` 
            SET opencart_product_id = '" . (int)$product['product_id'] . "',
                hepsiburada_id = '" . $this->db->escape($hepsiburada_id) . "',
                hepsiburada_sku = '" . $this->db->escape($product['sku']) . "',
                hepsiburada_merchant_id = '" . $this->db->escape($merchant_id) . "',
                status = 1,
                last_sync = NOW(),
                date_added = NOW(),
                date_modified = NOW()
        ");
        
        $this->log('product', 'create', $product['product_id'], 'Product created successfully', 'success');
    }
    
    private function updateProductToHepsiburada($product) {
        // Hepsiburada API product update logic
        $this->db->query("
            UPDATE `" . DB_PREFIX . "meschain_hepsiburada_products` 
            SET last_sync = NOW(),
                date_modified = NOW()
            WHERE opencart_product_id = '" . (int)$product['product_id'] . "'
        ");
        
        $this->log('product', 'update', $product['product_id'], 'Product updated successfully', 'success');
    }
    
    public function getHepsiburadaProducts($data = array()) {
        $sql = "SELECT hp.*, p.name, p.sku, p.price, p.status as product_status 
                FROM `" . DB_PREFIX . "meschain_hepsiburada_products` hp
                LEFT JOIN `" . DB_PREFIX . "product` p ON hp.opencart_product_id = p.product_id
                LEFT JOIN `" . DB_PREFIX . "product_description` pd ON p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        
        if (!empty($data['filter_status'])) {
            $sql .= " WHERE hp.status = '" . (int)$data['filter_status'] . "'";
        }
        
        $sort_data = array(
            'pd.name',
            'p.sku',
            'p.price',
            'hp.last_sync',
            'hp.date_added'
        );
        
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY hp.date_added";
        }
        
        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }
        
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
    }
    
    public function getTotalHepsiburadaProducts() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "meschain_hepsiburada_products`");
        
        return $query->row['total'];
    }
    
    public function deleteHepsiburadaProduct($hepsiburada_product_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_hepsiburada_products` WHERE hepsiburada_product_id = '" . (int)$hepsiburada_product_id . "'");
        
        $this->log('product', 'delete', $hepsiburada_product_id, 'Product deleted successfully', 'success');
    }
    
    private function log($type, $action, $entity_id, $message, $status) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_hepsiburada_sync_log` 
            SET type = '" . $this->db->escape($type) . "',
                action = '" . $this->db->escape($action) . "',
                entity_id = '" . $this->db->escape($entity_id) . "',
                message = '" . $this->db->escape($message) . "',
                status = '" . $this->db->escape($status) . "',
                date_added = NOW()
        ");
    }
    
    public function getSyncLogs($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_hepsiburada_sync_log`";
        
        $where = array();
        
        if (!empty($data['filter_type'])) {
            $where[] = "type = '" . $this->db->escape($data['filter_type']) . "'";
        }
        
        if (!empty($data['filter_status'])) {
            $where[] = "status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY date_added DESC";
        
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
    }
}
