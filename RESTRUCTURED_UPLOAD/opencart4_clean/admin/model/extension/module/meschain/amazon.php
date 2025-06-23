<?php
namespace Opencart\Admin\Model\Extension\Module\Meschain;

/**
 * Amazon Model
 *
 * @package MesChain Sync Enterprise
 * @version 3.0.0
 */
class Amazon extends \Opencart\System\Engine\Model {

    /**
     * Install module tables
     */
    public function install(): void {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_amazon_products` (
                `product_id` int(11) NOT NULL,
                `asin` varchar(20) NOT NULL,
                `sku` varchar(100) NOT NULL,
                `amazon_title` varchar(255) NOT NULL,
                `amazon_price` decimal(15,4) NOT NULL,
                `amazon_quantity` int(11) NOT NULL,
                `fulfillment_channel` varchar(20) NOT NULL DEFAULT 'MFN',
                `listing_status` varchar(50) NOT NULL,
                `sync_status` tinyint(1) NOT NULL DEFAULT '0',
                `last_sync` datetime DEFAULT NULL,
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`product_id`),
                KEY `asin` (`asin`),
                KEY `sku` (`sku`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_amazon_orders` (
                `order_id` int(11) NOT NULL AUTO_INCREMENT,
                `amazon_order_id` varchar(50) NOT NULL,
                `opencart_order_id` int(11) DEFAULT NULL,
                `purchase_date` datetime NOT NULL,
                `order_status` varchar(50) NOT NULL,
                `fulfillment_channel` varchar(20) NOT NULL,
                `order_total` decimal(15,4) NOT NULL,
                `currency_code` varchar(3) NOT NULL,
                `buyer_email` varchar(100) NOT NULL,
                `buyer_name` varchar(100) NOT NULL,
                `shipping_address` text NOT NULL,
                `order_data` text NOT NULL,
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`order_id`),
                UNIQUE KEY `amazon_order_id` (`amazon_order_id`),
                KEY `opencart_order_id` (`opencart_order_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_amazon_logs` (
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
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_amazon_products`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_amazon_orders`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_amazon_logs`");
    }

    /**
     * Sync products with Amazon
     */
    public function syncProducts($filter = array()): array {
        try {
            $this->load->library('meschain/api/amazon');

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
            $details = array();

            foreach ($query->rows as $product) {
                try {
                    // Prepare Amazon listing data
                    $listing_data = $this->prepareListingData($product);

                    // Check if product already exists on Amazon
                    $existing = $this->getAmazonProduct($product['product_id']);

                    if ($existing) {
                        // Update existing listing
                        $result = $this->api->updateListing($existing['sku'], $listing_data);
                    } else {
                        // Create new listing
                        $result = $this->api->createListing($listing_data);
                    }

                    if ($result['success']) {
                        $this->saveAmazonProduct($product['product_id'], $result['asin'] ?? '', $listing_data);
                        $synced_count++;
                        $details[] = array(
                            'product_id' => $product['product_id'],
                            'name' => $product['name'],
                            'status' => 'success'
                        );
                    } else {
                        $failed_count++;
                        $this->addLog('error', 'product_sync', $result['error'] ?? 'Unknown error', $product);
                        $details[] = array(
                            'product_id' => $product['product_id'],
                            'name' => $product['name'],
                            'status' => 'failed',
                            'error' => $result['error'] ?? 'Unknown error'
                        );
                    }
                } catch (\Exception $e) {
                    $failed_count++;
                    $this->addLog('error', 'product_sync', $e->getMessage(), $product);
                    $details[] = array(
                        'product_id' => $product['product_id'],
                        'name' => $product['name'],
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    );
                }
            }

            return array(
                'success' => true,
                'synced_count' => $synced_count,
                'failed_count' => $failed_count,
                'details' => $details
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
     * Sync orders from Amazon
     */
    public function syncOrders($filter = array()): array {
        try {
            $this->load->library('meschain/api/amazon');

            $result = $this->api->getOrders($filter);

            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Failed to fetch orders');
            }

            $order_count = 0;
            $orders = array();

            foreach ($result['orders'] as $amazon_order) {
                // Check if order already exists
                if (!$this->orderExists($amazon_order['AmazonOrderId'])) {
                    $opencart_order_id = $this->createOrder($amazon_order);

                    if ($opencart_order_id) {
                        $this->saveAmazonOrder($amazon_order, $opencart_order_id);
                        $order_count++;
                        $orders[] = array(
                            'amazon_order_id' => $amazon_order['AmazonOrderId'],
                            'opencart_order_id' => $opencart_order_id,
                            'total' => $amazon_order['OrderTotal']['Amount'] ?? 0
                        );
                    }
                }
            }

            return array(
                'success' => true,
                'order_count' => $order_count,
                'orders' => $orders,
                'total' => count($result['orders'])
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
     * Sync inventory with Amazon
     */
    public function syncInventory(): array {
        try {
            $this->load->library('meschain/api/amazon');

            // Get all Amazon products
            $query = $this->db->query("
                SELECT p.product_id, p.quantity, ap.sku
                FROM " . DB_PREFIX . "product p
                INNER JOIN " . DB_PREFIX . "meschain_amazon_products ap ON (p.product_id = ap.product_id)
                WHERE ap.fulfillment_channel = 'MFN'
            ");

            $updated_count = 0;
            $details = array();

            foreach ($query->rows as $product) {
                $result = $this->api->updateInventory($product['sku'], $product['quantity']);

                if ($result['success']) {
                    $updated_count++;
                    $details[] = array(
                        'sku' => $product['sku'],
                        'quantity' => $product['quantity'],
                        'status' => 'updated'
                    );
                } else {
                    $details[] = array(
                        'sku' => $product['sku'],
                        'quantity' => $product['quantity'],
                        'status' => 'failed',
                        'error' => $result['error'] ?? 'Unknown error'
                    );
                }
            }

            return array(
                'success' => true,
                'updated_count' => $updated_count,
                'details' => $details
            );
        } catch (\Exception $e) {
            $this->addLog('error', 'sync_inventory', $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Get FBA inventory
     */
    public function getFBAInventory(): array {
        try {
            $this->load->library('meschain/api/amazon');

            $result = $this->api->getFBAInventory();

            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Failed to fetch FBA inventory');
            }

            $inventory = $result['inventory'] ?? array();
            $summary = array(
                'total_items' => count($inventory),
                'total_quantity' => 0,
                'total_reserved' => 0
            );

            foreach ($inventory as $item) {
                $summary['total_quantity'] += $item['totalQuantity'] ?? 0;
                $summary['total_reserved'] += $item['reservedQuantity'] ?? 0;
            }

            return array(
                'success' => true,
                'inventory' => $inventory,
                'summary' => $summary
            );
        } catch (\Exception $e) {
            $this->addLog('error', 'get_fba_inventory', $e->getMessage());
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
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_amazon_products");
        $stats['total_products'] = $query->row['total'];

        // Active listings
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_amazon_products WHERE listing_status = 'Active'");
        $stats['active_listings'] = $query->row['total'];

        // FBA products
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_amazon_products WHERE fulfillment_channel = 'AFN'");
        $stats['fba_products'] = $query->row['total'];

        // Total orders
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_amazon_orders");
        $stats['total_orders'] = $query->row['total'];

        // Today's orders
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_amazon_orders WHERE DATE(date_created) = CURDATE()");
        $stats['today_orders'] = $query->row['total'];

        // Revenue
        $query = $this->db->query("SELECT SUM(order_total) as revenue FROM " . DB_PREFIX . "meschain_amazon_orders WHERE order_status IN ('Shipped', 'Delivered')");
        $stats['total_revenue'] = $query->row['revenue'] ?? 0;

        return $stats;
    }

    /**
     * Prepare listing data for Amazon
     */
    private function prepareListingData($product): array {
        return array(
            'sku' => $product['sku'] ?: 'SKU-' . $product['product_id'],
            'title' => $product['name'],
            'description' => html_entity_decode($product['description']),
            'price' => $product['price'],
            'quantity' => $product['quantity'],
            'brand' => $product['manufacturer'] ?? '',
            'product_type' => $this->mapProductType($product['product_id']),
            'condition' => 'new'
        );
    }

    /**
     * Map OpenCart product to Amazon product type
     */
    private function mapProductType($product_id): string {
        // TODO: Implement product type mapping logic
        return 'PRODUCT'; // Default product type
    }

    /**
     * Get Amazon product by product ID
     */
    private function getAmazonProduct($product_id): array {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_amazon_products
            WHERE product_id = '" . (int)$product_id . "'
        ");

        return $query->row ?: array();
    }

    /**
     * Save Amazon product mapping
     */
    private function saveAmazonProduct($product_id, $asin, $data): void {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_amazon_products
            SET product_id = '" . (int)$product_id . "',
                asin = '" . $this->db->escape($asin) . "',
                sku = '" . $this->db->escape($data['sku']) . "',
                amazon_title = '" . $this->db->escape($data['title']) . "',
                amazon_price = '" . (float)$data['price'] . "',
                amazon_quantity = '" . (int)$data['quantity'] . "',
                fulfillment_channel = 'MFN',
                listing_status = 'Active',
                sync_status = '1',
                last_sync = NOW(),
                date_added = NOW(),
                date_modified = NOW()
            ON DUPLICATE KEY UPDATE
                asin = '" . $this->db->escape($asin) . "',
                sku = '" . $this->db->escape($data['sku']) . "',
                amazon_title = '" . $this->db->escape($data['title']) . "',
                amazon_price = '" . (float)$data['price'] . "',
                amazon_quantity = '" . (int)$data['quantity'] . "',
                sync_status = '1',
                last_sync = NOW(),
                date_modified = NOW()
        ");
    }

    /**
     * Check if order exists
     */
    private function orderExists($amazon_order_id): bool {
        $query = $this->db->query("
            SELECT order_id FROM " . DB_PREFIX . "meschain_amazon_orders
            WHERE amazon_order_id = '" . $this->db->escape($amazon_order_id) . "'
        ");

        return !empty($query->row);
    }

    /**
     * Create OpenCart order from Amazon order
     */
    private function createOrder($amazon_order): int {
        // TODO: Implement full order creation logic
        // This is a simplified version
        return 0;
    }

    /**
     * Save Amazon order mapping
     */
    private function saveAmazonOrder($amazon_order, $opencart_order_id): void {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_amazon_orders
            SET amazon_order_id = '" . $this->db->escape($amazon_order['AmazonOrderId']) . "',
                opencart_order_id = '" . (int)$opencart_order_id . "',
                purchase_date = '" . $this->db->escape($amazon_order['PurchaseDate']) . "',
                order_status = '" . $this->db->escape($amazon_order['OrderStatus']) . "',
                fulfillment_channel = '" . $this->db->escape($amazon_order['FulfillmentChannel']) . "',
                order_total = '" . (float)($amazon_order['OrderTotal']['Amount'] ?? 0) . "',
                currency_code = '" . $this->db->escape($amazon_order['OrderTotal']['CurrencyCode'] ?? 'USD') . "',
                buyer_email = '" . $this->db->escape($amazon_order['BuyerEmail'] ?? '') . "',
                buyer_name = '" . $this->db->escape($amazon_order['BuyerName'] ?? '') . "',
                shipping_address = '" . $this->db->escape(json_encode($amazon_order['ShippingAddress'] ?? array())) . "',
                order_data = '" . $this->db->escape(json_encode($amazon_order)) . "',
                date_created = NOW(),
                date_modified = NOW()
        ");
    }

    /**
     * Add log entry
     */
    private function addLog($type, $action, $message, $data = null): void {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_amazon_logs
            SET type = '" . $this->db->escape($type) . "',
                action = '" . $this->db->escape($action) . "',
                message = '" . $this->db->escape($message) . "',
                data = '" . $this->db->escape(json_encode($data)) . "',
                date_added = NOW()
        ");
    }
}
