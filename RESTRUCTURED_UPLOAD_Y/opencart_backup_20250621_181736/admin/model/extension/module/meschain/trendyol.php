<?php
namespace Opencart\Admin\Model\Extension\Module\Meschain;

/**
 * Trendyol Marketplace Model
 *
 * @author Cursor Development Team
 * @version 1.0.0
 */
class Trendyol extends \Opencart\System\Engine\Model {

    /**
     * Get settings
     */
    public function getSettings(): array {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "meschain_settings WHERE marketplace = 'trendyol'");

        $settings = array();
        foreach ($query->rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    /**
     * Save settings
     */
    public function saveSettings($data): void {
        // Clear existing settings
        $this->db->query("DELETE FROM " . DB_PREFIX . "meschain_settings WHERE marketplace = 'trendyol'");

        // Save new settings
        foreach ($data as $key => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "meschain_settings SET
                marketplace = 'trendyol',
                setting_group = 'general',
                setting_key = '" . $this->db->escape($key) . "',
                setting_value = '" . $this->db->escape($value) . "'");
        }
    }

    /**
     * Sync products with Trendyol
     */
    public function syncProducts(): array {
        $api = $this->getApiClient();

        try {
            // Get products from Trendyol
            $trendyol_products = $api->getProducts();

            $synced_count = 0;
            $total_count = count($trendyol_products);

            foreach ($trendyol_products as $product) {
                if ($this->syncProduct($product)) {
                    $synced_count++;
                }
            }

            // Log sync activity
            $this->logActivity('product_sync', sprintf('Synced %d products from Trendyol', $synced_count));

            return array(
                'success' => true,
                'synced_count' => $synced_count,
                'total_count' => $total_count
            );

        } catch (Exception $e) {
            $this->log->write('Trendyol Sync Error: ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Sync single product
     */
    private function syncProduct($trendyol_product): bool {
        // Check if product exists
        $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "meschain_product_mapping
            WHERE marketplace = 'trendyol' AND marketplace_product_id = '" . $this->db->escape($trendyol_product['barcode']) . "'");

        if ($query->num_rows) {
            // Update existing product
            $product_id = $query->row['product_id'];
            $this->updateProduct($product_id, $trendyol_product);
        } else {
            // Create new product
            $product_id = $this->createProduct($trendyol_product);

            // Create mapping
            $this->db->query("INSERT INTO " . DB_PREFIX . "meschain_product_mapping SET
                product_id = '" . (int)$product_id . "',
                marketplace = 'trendyol',
                marketplace_product_id = '" . $this->db->escape($trendyol_product['barcode']) . "',
                date_added = NOW()");
        }

        return true;
    }

    /**
     * Create product
     */
    private function createProduct($trendyol_product): int {
        $this->load->model('catalog/product');

        $product_data = array(
            'model' => $trendyol_product['stockCode'] ?? '',
            'sku' => $trendyol_product['barcode'] ?? '',
            'upc' => '',
            'ean' => '',
            'jan' => '',
            'isbn' => '',
            'mpn' => '',
            'location' => '',
            'quantity' => $trendyol_product['quantity'] ?? 0,
            'minimum' => 1,
            'subtract' => 1,
            'stock_status_id' => 7,
            'date_available' => date('Y-m-d'),
            'manufacturer_id' => 0,
            'shipping' => 1,
            'price' => $trendyol_product['salePrice'] ?? 0,
            'points' => 0,
            'weight' => 0,
            'weight_class_id' => 1,
            'length' => 0,
            'width' => 0,
            'height' => 0,
            'length_class_id' => 1,
            'status' => 1,
            'tax_class_id' => 0,
            'sort_order' => 0,
            'product_description' => array(
                1 => array(
                    'name' => $trendyol_product['title'] ?? '',
                    'description' => $trendyol_product['description'] ?? '',
                    'tag' => '',
                    'meta_title' => $trendyol_product['title'] ?? '',
                    'meta_description' => '',
                    'meta_keyword' => ''
                )
            ),
            'product_store' => array(0),
            'product_category' => array()
        );

        return $this->model_catalog_product->addProduct($product_data);
    }

    /**
     * Update product
     */
    private function updateProduct($product_id, $trendyol_product): void {
        $this->db->query("UPDATE " . DB_PREFIX . "product SET
            quantity = '" . (int)$trendyol_product['quantity'] . "',
            price = '" . (float)$trendyol_product['salePrice'] . "',
            date_modified = NOW()
            WHERE product_id = '" . (int)$product_id . "'");
    }

    /**
     * Get orders from Trendyol
     */
    public function getOrders($filter_data = array()): array {
        $api = $this->getApiClient();

        try {
            $orders = $api->getOrders($filter_data);

            // Process and save orders
            foreach ($orders['content'] as $order) {
                $this->processOrder($order);
            }

            return array(
                'data' => $orders['content'],
                'total' => $orders['totalElements']
            );

        } catch (Exception $e) {
            $this->log->write('Trendyol Orders Error: ' . $e->getMessage());
            return array(
                'data' => array(),
                'total' => 0
            );
        }
    }

    /**
     * Process order
     */
    private function processOrder($trendyol_order): void {
        // Check if order exists
        $query = $this->db->query("SELECT order_id FROM " . DB_PREFIX . "meschain_order_mapping
            WHERE marketplace = 'trendyol' AND marketplace_order_id = '" . $this->db->escape($trendyol_order['orderNumber']) . "'");

        if (!$query->num_rows) {
            // Create new order
            $this->load->model('checkout/order');

            // Map Trendyol order to OpenCart order format
            $order_data = $this->mapTrendyolOrder($trendyol_order);

            // Add order
            $order_id = $this->model_checkout_order->addOrder($order_data);

            // Create mapping
            $this->db->query("INSERT INTO " . DB_PREFIX . "meschain_order_mapping SET
                order_id = '" . (int)$order_id . "',
                marketplace = 'trendyol',
                marketplace_order_id = '" . $this->db->escape($trendyol_order['orderNumber']) . "',
                date_added = NOW()");
        }
    }

    /**
     * Update inventory
     */
    public function updateInventory($product_id, $quantity): array {
        $api = $this->getApiClient();

        try {
            // Get marketplace product ID
            $query = $this->db->query("SELECT marketplace_product_id FROM " . DB_PREFIX . "meschain_product_mapping
                WHERE marketplace = 'trendyol' AND product_id = '" . (int)$product_id . "'");

            if ($query->num_rows) {
                $barcode = $query->row['marketplace_product_id'];

                // Update on Trendyol
                $result = $api->updateStock($barcode, $quantity);

                if ($result) {
                    // Update local stock
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET
                        quantity = '" . (int)$quantity . "'
                        WHERE product_id = '" . (int)$product_id . "'");

                    return array('success' => true);
                }
            }

            return array('success' => false, 'error' => 'Product not found');

        } catch (Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }
    }

    /**
     * Get analytics
     */
    public function getAnalytics($period = '7days'): array {
        $analytics = array(
            'sales' => $this->getSalesData($period),
            'orders' => $this->getOrdersData($period),
            'products' => $this->getProductsData($period),
            'performance' => $this->getPerformanceData($period)
        );

        return $analytics;
    }

    /**
     * Get campaigns
     */
    public function getCampaigns(): array {
        $api = $this->getApiClient();

        try {
            return $api->getCampaigns();
        } catch (Exception $e) {
            $this->log->write('Trendyol Campaigns Error: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Get API client
     */
    private function getApiClient() {
        $settings = $this->getSettings();

        require_once DIR_SYSTEM . 'library/meschain/api/trendyol.php';

        return new \MesChain\Api\Trendyol(array(
            'api_key' => $settings['api_key'] ?? '',
            'api_secret' => $settings['api_secret'] ?? '',
            'supplier_id' => $settings['supplier_id'] ?? ''
        ));
    }

    /**
     * Log activity
     */
    private function logActivity($type, $message): void {
        $this->db->query("INSERT INTO " . DB_PREFIX . "meschain_activity_log SET
            marketplace = 'trendyol',
            activity_type = '" . $this->db->escape($type) . "',
            message = '" . $this->db->escape($message) . "',
            date_added = NOW()");
    }

    /**
     * Map Trendyol order to OpenCart format
     */
    private function mapTrendyolOrder($trendyol_order): array {
        // Basic implementation - expand as needed
        return array(
            'invoice_prefix' => 'TY-',
            'store_id' => 0,
            'store_name' => 'Trendyol',
            'store_url' => '',
            'customer_id' => 0,
            'customer_group_id' => 1,
            'firstname' => $trendyol_order['customerFirstName'] ?? '',
            'lastname' => $trendyol_order['customerLastName'] ?? '',
            'email' => $trendyol_order['customerEmail'] ?? '',
            'telephone' => '',
            'custom_field' => array(),
            'payment_firstname' => $trendyol_order['customerFirstName'] ?? '',
            'payment_lastname' => $trendyol_order['customerLastName'] ?? '',
            'payment_company' => '',
            'payment_address_1' => $trendyol_order['shipmentAddress']['address1'] ?? '',
            'payment_address_2' => '',
            'payment_city' => $trendyol_order['shipmentAddress']['city'] ?? '',
            'payment_postcode' => $trendyol_order['shipmentAddress']['postalCode'] ?? '',
            'payment_country' => 'Turkey',
            'payment_country_id' => 215,
            'payment_zone' => $trendyol_order['shipmentAddress']['city'] ?? '',
            'payment_zone_id' => 0,
            'payment_address_format' => '',
            'payment_custom_field' => array(),
            'payment_method' => 'Trendyol Payment',
            'payment_code' => 'trendyol',
            'shipping_firstname' => $trendyol_order['customerFirstName'] ?? '',
            'shipping_lastname' => $trendyol_order['customerLastName'] ?? '',
            'shipping_company' => '',
            'shipping_address_1' => $trendyol_order['shipmentAddress']['address1'] ?? '',
            'shipping_address_2' => '',
            'shipping_city' => $trendyol_order['shipmentAddress']['city'] ?? '',
            'shipping_postcode' => $trendyol_order['shipmentAddress']['postalCode'] ?? '',
            'shipping_country' => 'Turkey',
            'shipping_country_id' => 215,
            'shipping_zone' => $trendyol_order['shipmentAddress']['city'] ?? '',
            'shipping_zone_id' => 0,
            'shipping_address_format' => '',
            'shipping_custom_field' => array(),
            'shipping_method' => 'Trendyol Shipping',
            'shipping_code' => 'trendyol',
            'comment' => 'Trendyol Order: ' . $trendyol_order['orderNumber'],
            'total' => $trendyol_order['grossAmount'] ?? 0,
            'order_status_id' => 1,
            'affiliate_id' => 0,
            'commission' => 0,
            'marketing_id' => 0,
            'tracking' => '',
            'language_id' => 1,
            'currency_id' => 1,
            'currency_code' => 'TRY',
            'currency_value' => 1.00000000,
            'ip' => '',
            'forwarded_ip' => '',
            'user_agent' => 'Trendyol API',
            'accept_language' => 'tr',
            'products' => $this->mapOrderProducts($trendyol_order['lines'] ?? array())
        );
    }

    /**
     * Map order products
     */
    private function mapOrderProducts($lines): array {
        $products = array();

        foreach ($lines as $line) {
            $products[] = array(
                'product_id' => 0, // Will be mapped later
                'name' => $line['productName'] ?? '',
                'model' => $line['productCode'] ?? '',
                'option' => array(),
                'download' => array(),
                'quantity' => $line['quantity'] ?? 1,
                'subtract' => 1,
                'price' => $line['price'] ?? 0,
                'total' => ($line['price'] ?? 0) * ($line['quantity'] ?? 1),
                'tax' => 0,
                'reward' => 0
            );
        }

        return $products;
    }

    /**
     * Get sales data for analytics
     */
    private function getSalesData($period): array {
        // Implementation for sales analytics
        return array();
    }

    /**
     * Get orders data for analytics
     */
    private function getOrdersData($period): array {
        // Implementation for orders analytics
        return array();
    }

    /**
     * Get products data for analytics
     */
    private function getProductsData($period): array {
        // Implementation for products analytics
        return array();
    }

    /**
     * Get performance data for analytics
     */
    private function getPerformanceData($period): array {
        // Implementation for performance analytics
        return array();
    }
}
