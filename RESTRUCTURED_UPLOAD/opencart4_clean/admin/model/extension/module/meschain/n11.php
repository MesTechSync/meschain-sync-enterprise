<?php
namespace Opencart\Admin\Model\Extension\Module\Meschain;

/**
 * N11 Model
 *
 * @package MesChain Sync Enterprise
 * @version 3.0.0
 */
class N11 extends \Opencart\System\Engine\Model {

    /**
     * Install module tables
     */
    public function install(): void {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_n11_products` (
                `product_id` int(11) NOT NULL,
                `n11_product_id` varchar(100) NOT NULL,
                `n11_title` varchar(255) NOT NULL,
                `n11_price` decimal(15,4) NOT NULL,
                `n11_stock` int(11) NOT NULL,
                `n11_status` varchar(50) NOT NULL,
                `sync_status` tinyint(1) NOT NULL DEFAULT '0',
                `last_sync` datetime DEFAULT NULL,
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`product_id`),
                KEY `n11_product_id` (`n11_product_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_n11_orders` (
                `order_id` int(11) NOT NULL AUTO_INCREMENT,
                `n11_order_id` varchar(100) NOT NULL,
                `opencart_order_id` int(11) DEFAULT NULL,
                `buyer_name` varchar(100) NOT NULL,
                `total_amount` decimal(15,4) NOT NULL,
                `order_status` varchar(50) NOT NULL,
                `payment_type` varchar(50) NOT NULL,
                `shipping_status` varchar(50) NOT NULL,
                `order_data` text NOT NULL,
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`order_id`),
                UNIQUE KEY `n11_order_id` (`n11_order_id`),
                KEY `opencart_order_id` (`opencart_order_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_n11_logs` (
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
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_n11_products`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_n11_orders`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_n11_logs`");
    }

    /**
     * Sync products with N11
     */
    public function syncProducts($filter = array()): array {
        try {
            $this->load->library('meschain/api/n11');

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
                    // Prepare N11 product data
                    $product_data = $this->prepareProductData($product);

                    // Check if product already exists on N11
                    $existing = $this->getN11Product($product['product_id']);

                    if ($existing) {
                        // Update existing product
                        $result = $this->api->updateProduct($existing['n11_product_id'], $product_data);
                    } else {
                        // Create new product
                        $result = $this->api->saveProduct($product_data);
                    }

                    if ($result['success']) {
                        $this->saveN11Product($product['product_id'], $result['product_id'], $product_data);
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
     * Sync orders from N11
     */
    public function syncOrders($filter = array()): array {
        try {
            $this->load->library('meschain/api/n11');

            $result = $this->api->getOrders($filter);

            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Failed to fetch orders');
            }

            $order_count = 0;
            $orders = array();

            foreach ($result['orders'] as $n11_order) {
                // Check if order already exists
                if (!$this->orderExists($n11_order['id'])) {
                    $opencart_order_id = $this->createOrder($n11_order);

                    if ($opencart_order_id) {
                        $this->saveN11Order($n11_order, $opencart_order_id);
                        $order_count++;
                        $orders[] = array(
                            'n11_order_id' => $n11_order['id'],
                            'opencart_order_id' => $opencart_order_id,
                            'total' => $n11_order['total_amount']
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
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_n11_products");
        $stats['total_products'] = $query->row['total'];

        // Active products
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_n11_products WHERE n11_status = 'Active'");
        $stats['active_products'] = $query->row['total'];

        // Total orders
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_n11_orders");
        $stats['total_orders'] = $query->row['total'];

        // Today's orders
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_n11_orders WHERE DATE(date_created) = CURDATE()");
        $stats['today_orders'] = $query->row['total'];

        // Revenue
        $query = $this->db->query("SELECT SUM(total_amount) as revenue FROM " . DB_PREFIX . "meschain_n11_orders WHERE order_status = 'Completed'");
        $stats['total_revenue'] = $query->row['revenue'] ?? 0;

        return $stats;
    }

    /**
     * Prepare product data for N11
     */
    private function prepareProductData($product): array {
        return array(
            'productSellerCode' => $product['sku'] ?: 'SKU-' . $product['product_id'],
            'title' => $product['name'],
            'subtitle' => '',
            'description' => html_entity_decode($product['description']),
            'price' => $product['price'],
            'displayPrice' => $product['price'],
            'stockItems' => array(
                array(
                    'quantity' => $product['quantity'],
                    'sellerStockCode' => $product['sku'] ?: 'SKU-' . $product['product_id']
                )
            ),
            'brand' => $product['manufacturer'] ?? '',
            'category' => array(
                'id' => $this->mapCategory($product['product_id'])
            ),
            'preparingDay' => 3
        );
    }

    /**
     * Map OpenCart category to N11 category
     */
    private function mapCategory($product_id): int {
        // TODO: Implement category mapping logic
        return 1000372; // Default to a generic category
    }

    /**
     * Get N11 product by product ID
     */
    private function getN11Product($product_id): array {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_n11_products
            WHERE product_id = '" . (int)$product_id . "'
        ");

        return $query->row ?: array();
    }

    /**
     * Save N11 product mapping
     */
    private function saveN11Product($product_id, $n11_product_id, $data): void {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_n11_products
            SET product_id = '" . (int)$product_id . "',
                n11_product_id = '" . $this->db->escape($n11_product_id) . "',
                n11_title = '" . $this->db->escape($data['title']) . "',
                n11_price = '" . (float)$data['price'] . "',
                n11_stock = '" . (int)$data['stockItems'][0]['quantity'] . "',
                n11_status = 'Active',
                sync_status = '1',
                last_sync = NOW(),
                date_added = NOW(),
                date_modified = NOW()
            ON DUPLICATE KEY UPDATE
                n11_product_id = '" . $this->db->escape($n11_product_id) . "',
                n11_title = '" . $this->db->escape($data['title']) . "',
                n11_price = '" . (float)$data['price'] . "',
                n11_stock = '" . (int)$data['stockItems'][0]['quantity'] . "',
                sync_status = '1',
                last_sync = NOW(),
                date_modified = NOW()
        ");
    }

    /**
     * Check if order exists
     */
    private function orderExists($n11_order_id): bool {
        $query = $this->db->query("
            SELECT order_id FROM " . DB_PREFIX . "meschain_n11_orders
            WHERE n11_order_id = '" . $this->db->escape($n11_order_id) . "'
        ");

        return !empty($query->row);
    }

    /**
     * Create OpenCart order from N11 order
     */
    private function createOrder($n11_order): int {
        // TODO: Implement full order creation logic
        // This is a simplified version
        return 0;
    }

    /**
     * Save N11 order mapping
     */
    private function saveN11Order($n11_order, $opencart_order_id): void {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_n11_orders
            SET n11_order_id = '" . $this->db->escape($n11_order['id']) . "',
                opencart_order_id = '" . (int)$opencart_order_id . "',
                buyer_name = '" . $this->db->escape($n11_order['buyer_name']) . "',
                total_amount = '" . (float)$n11_order['total_amount'] . "',
                order_status = '" . $this->db->escape($n11_order['status']) . "',
                payment_type = '" . $this->db->escape($n11_order['payment_type']) . "',
                shipping_status = '" . $this->db->escape($n11_order['shipping_status']) . "',
                order_data = '" . $this->db->escape(json_encode($n11_order)) . "',
                date_created = NOW(),
                date_modified = NOW()
        ");
    }

    /**
     * Add log entry
     */
    private function addLog($type, $action, $message, $data = null): void {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_n11_logs
            SET type = '" . $this->db->escape($type) . "',
                action = '" . $this->db->escape($action) . "',
                message = '" . $this->db->escape($message) . "',
                data = '" . $this->db->escape(json_encode($data)) . "',
                date_added = NOW()
        ");
    }
}
