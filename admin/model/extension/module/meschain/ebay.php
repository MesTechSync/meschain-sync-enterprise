<?php
namespace Opencart\Admin\Model\Extension\Module\Meschain;

/**
 * eBay Model
 *
 * @package MesChain Sync Enterprise
 * @version 3.0.0
 */
class Ebay extends \Opencart\System\Engine\Model {

    /**
     * Install module tables
     */
    public function install(): void {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_ebay_products` (
                `product_id` int(11) NOT NULL,
                `ebay_item_id` varchar(100) NOT NULL,
                `ebay_title` varchar(255) NOT NULL,
                `ebay_price` decimal(15,4) NOT NULL,
                `ebay_quantity` int(11) NOT NULL,
                `ebay_status` varchar(50) NOT NULL,
                `sync_status` tinyint(1) NOT NULL DEFAULT '0',
                `last_sync` datetime DEFAULT NULL,
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`product_id`),
                KEY `ebay_item_id` (`ebay_item_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_ebay_orders` (
                `order_id` int(11) NOT NULL AUTO_INCREMENT,
                `ebay_order_id` varchar(100) NOT NULL,
                `opencart_order_id` int(11) DEFAULT NULL,
                `buyer_username` varchar(100) NOT NULL,
                `total` decimal(15,4) NOT NULL,
                `currency` varchar(3) NOT NULL,
                `order_status` varchar(50) NOT NULL,
                `payment_status` varchar(50) NOT NULL,
                `shipping_status` varchar(50) NOT NULL,
                `order_data` text NOT NULL,
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`order_id`),
                UNIQUE KEY `ebay_order_id` (`ebay_order_id`),
                KEY `opencart_order_id` (`opencart_order_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_ebay_logs` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `type` varchar(50) NOT NULL,
                `action` varchar(100) NOT NULL,
                `message` text NOT NULL,
                `data` text,
                `date_added` datetime NOT NULL,
                PRIMARY KEY (`log_id`),
                KEY `type` (`type`),
                KEY `date_added` (`date_added`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ");
    }

    /**
     * Uninstall module tables
     */
    public function uninstall(): void {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_ebay_products`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_ebay_orders`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_ebay_logs`");
    }

    /**
     * Sync products with eBay
     */
    public function syncProducts($filter = array()): array {
        try {
            $this->load->library('meschain/api/ebay');

            $start = $filter['start'] ?? 0;
            $limit = $filter['limit'] ?? 50;

            // Get products to sync
            $query = $this->db->query("
                SELECT p.*, pd.name, pd.description, m.name as manufacturer
                FROM " . DB_PREFIX . "product p
                LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
                LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id)
                WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                AND p.status = '1'
                ORDER BY p.product_id ASC
                LIMIT " . (int)$start . "," . (int)$limit
            );

            $synced_count = 0;
            $failed_count = 0;

            foreach ($query->rows as $product) {
                try {
                    // Prepare eBay listing data
                    $listing_data = $this->prepareListingData($product);

                    // Check if product already exists on eBay
                    $existing = $this->getEbayProduct($product['product_id']);

                    if ($existing) {
                        // Update existing listing
                        $result = $this->api->updateListing($existing['ebay_item_id'], $listing_data);
                    } else {
                        // Create new listing
                        $result = $this->api->createListing($listing_data);
                    }

                    if ($result['success']) {
                        $this->saveEbayProduct($product['product_id'], $result['item_id'], $listing_data);
                        $synced_count++;
                    } else {
                        $failed_count++;
                        $this->addLog('error', 'product_sync', $result['error'] ?? 'Unknown error', $product);
                    }
                } catch (\Exception $e) {
                    $failed_count++;
                    $this->addLog('error', 'product_sync', $e->getMessage(), $product);
                }
            }

            return array(
                'success' => true,
                'synced_count' => $synced_count,
                'failed_count' => $failed_count
            );
        } catch (\Exception $e) {
            $this->addLog('error', 'sync_products', $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Sync orders from eBay
     */
    public function syncOrders(): array {
        try {
            $this->load->library('meschain/api/ebay');

            $result = $this->api->getOrders();

            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Failed to fetch orders');
            }

            $order_count = 0;
            $orders = array();

            foreach ($result['orders'] as $ebay_order) {
                // Check if order already exists
                if (!$this->orderExists($ebay_order['order_id'])) {
                    $opencart_order_id = $this->createOrder($ebay_order);

                    if ($opencart_order_id) {
                        $this->saveEbayOrder($ebay_order, $opencart_order_id);
                        $order_count++;
                        $orders[] = array(
                            'ebay_order_id' => $ebay_order['order_id'],
                            'opencart_order_id' => $opencart_order_id,
                            'total' => $ebay_order['total']
                        );
                    }
                }
            }

            return array(
                'success' => true,
                'order_count' => $order_count,
                'orders' => $orders
            );
        } catch (\Exception $e) {
            $this->addLog('error', 'sync_orders', $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Get marketplace statistics
     */
    public function getStats(): array {
        $stats = array();

        // Total products
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_ebay_products");
        $stats['total_products'] = $query->row['total'];

        // Active listings
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_ebay_products WHERE ebay_status = 'Active'");
        $stats['active_listings'] = $query->row['total'];

        // Total orders
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_ebay_orders");
        $stats['total_orders'] = $query->row['total'];

        // Today's orders
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_ebay_orders WHERE DATE(date_created) = CURDATE()");
        $stats['today_orders'] = $query->row['total'];

        // Revenue
        $query = $this->db->query("SELECT SUM(total) as revenue FROM " . DB_PREFIX . "meschain_ebay_orders WHERE order_status = 'Completed'");
        $stats['total_revenue'] = $query->row['revenue'] ?? 0;

        return $stats;
    }

    /**
     * Prepare listing data for eBay
     */
    private function prepareListingData($product): array {
        return array(
            'title' => $product['name'],
            'description' => html_entity_decode($product['description']),
            'price' => $product['price'],
            'quantity' => $product['quantity'],
            'sku' => $product['sku'] ?: 'SKU-' . $product['product_id'],
            'brand' => $product['manufacturer'] ?? '',
            'condition' => 'New',
            'category_id' => $this->mapCategory($product['product_id'])
        );
    }

    /**
     * Map OpenCart category to eBay category
     */
    private function mapCategory($product_id): string {
        // TODO: Implement category mapping logic
        return '9355'; // Default to Electronics category
    }

    /**
     * Get eBay product by product ID
     */
    private function getEbayProduct($product_id): array {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_ebay_products
            WHERE product_id = '" . (int)$product_id . "'
        ");

        return $query->row ?: array();
    }

    /**
     * Save eBay product mapping
     */
    private function saveEbayProduct($product_id, $ebay_item_id, $data): void {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_ebay_products
            SET product_id = '" . (int)$product_id . "',
                ebay_item_id = '" . $this->db->escape($ebay_item_id) . "',
                ebay_title = '" . $this->db->escape($data['title']) . "',
                ebay_price = '" . (float)$data['price'] . "',
                ebay_quantity = '" . (int)$data['quantity'] . "',
                ebay_status = 'Active',
                sync_status = '1',
                last_sync = NOW(),
                date_added = NOW(),
                date_modified = NOW()
            ON DUPLICATE KEY UPDATE
                ebay_item_id = '" . $this->db->escape($ebay_item_id) . "',
                ebay_title = '" . $this->db->escape($data['title']) . "',
                ebay_price = '" . (float)$data['price'] . "',
                ebay_quantity = '" . (int)$data['quantity'] . "',
                sync_status = '1',
                last_sync = NOW(),
                date_modified = NOW()
        ");
    }

    /**
     * Check if order exists
     */
    private function orderExists($ebay_order_id): bool {
        $query = $this->db->query("
            SELECT order_id FROM " . DB_PREFIX . "meschain_ebay_orders
            WHERE ebay_order_id = '" . $this->db->escape($ebay_order_id) . "'
        ");

        return !empty($query->row);
    }

    /**
     * Create OpenCart order from eBay order
     */
    private function createOrder($ebay_order): int {
        // TODO: Implement full order creation logic
        // This is a simplified version
        return 0;
    }

    /**
     * Save eBay order mapping
     */
    private function saveEbayOrder($ebay_order, $opencart_order_id): void {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_ebay_orders
            SET ebay_order_id = '" . $this->db->escape($ebay_order['order_id']) . "',
                opencart_order_id = '" . (int)$opencart_order_id . "',
                buyer_username = '" . $this->db->escape($ebay_order['buyer_username']) . "',
                total = '" . (float)$ebay_order['total'] . "',
                currency = '" . $this->db->escape($ebay_order['currency']) . "',
                order_status = '" . $this->db->escape($ebay_order['status']) . "',
                payment_status = '" . $this->db->escape($ebay_order['payment_status']) . "',
                shipping_status = '" . $this->db->escape($ebay_order['shipping_status']) . "',
                order_data = '" . $this->db->escape(json_encode($ebay_order)) . "',
                date_created = NOW(),
                date_modified = NOW()
        ");
    }

    /**
     * Add log entry
     */
    private function addLog($type, $action, $message, $data = null): void {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_ebay_logs
            SET type = '" . $this->db->escape($type) . "',
                action = '" . $this->db->escape($action) . "',
                message = '" . $this->db->escape($message) . "',
                data = '" . $this->db->escape(json_encode($data)) . "',
                date_added = NOW()
        ");
    }
}
