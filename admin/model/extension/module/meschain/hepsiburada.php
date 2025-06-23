<?php
namespace Opencart\Admin\Model\Extension\Module\Meschain;

/**
 * Hepsiburada Marketplace Model
 *
 * @author Cursor Development Team
 * @version 1.0.0
 */
class Hepsiburada extends \Opencart\System\Engine\Model {

    /**
     * Get marketplace products
     */
    public function getMarketplaceProducts(): array {
        // Get Hepsiburada API client
        $api = $this->getApiClient();

        try {
            $products = $api->getProducts();
            return $products;
        } catch (Exception $e) {
            $this->log->write('Hepsiburada API Error: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Sync products with OpenCart
     */
    public function syncProducts($products): array {
        $synced = 0;
        $failed = 0;
        $total = count($products);

        foreach ($products as $product) {
            try {
                // Check if product exists
                $product_id = $this->getProductByMarketplaceId($product['id']);

                if ($product_id) {
                    // Update existing product
                    $this->updateProduct($product_id, $product);
                } else {
                    // Create new product
                    $this->createProduct($product);
                }

                $synced++;
            } catch (Exception $e) {
                $failed++;
                $this->log->write('Product sync error: ' . $e->getMessage());
            }
        }

        return array(
            'total' => $total,
            'synced' => $synced,
            'failed' => $failed
        );
    }

    /**
     * Get total products
     */
    public function getTotalProducts(): int {
        $query = $this->db->query("
            SELECT COUNT(*) as total
            FROM " . DB_PREFIX . "meschain_product_sync
            WHERE marketplace = 'hepsiburada'
        ");

        return (int)$query->row['total'];
    }

    /**
     * Get synced products count
     */
    public function getSyncedProducts(): int {
        $query = $this->db->query("
            SELECT COUNT(*) as total
            FROM " . DB_PREFIX . "meschain_product_sync
            WHERE marketplace = 'hepsiburada'
            AND sync_status = 'success'
        ");

        return (int)$query->row['total'];
    }

    /**
     * Get pending orders
     */
    public function getPendingOrders(): int {
        $query = $this->db->query("
            SELECT COUNT(*) as total
            FROM " . DB_PREFIX . "meschain_order_integration
            WHERE marketplace = 'hepsiburada'
            AND integration_status = 'pending'
        ");

        return (int)$query->row['total'];
    }

    /**
     * Get total revenue
     */
    public function getTotalRevenue(): float {
        $query = $this->db->query("
            SELECT SUM(o.total) as revenue
            FROM " . DB_PREFIX . "order o
            INNER JOIN " . DB_PREFIX . "meschain_order_integration moi
            ON o.order_id = moi.order_id
            WHERE moi.marketplace = 'hepsiburada'
            AND o.order_status_id > 0
        ");

        return (float)($query->row['revenue'] ?? 0);
    }

    /**
     * Update inventory
     */
    public function updateInventory($product_id, $quantity): bool {
        // Update OpenCart stock
        $this->db->query("
            UPDATE " . DB_PREFIX . "product
            SET quantity = '" . (int)$quantity . "',
                date_modified = NOW()
            WHERE product_id = '" . (int)$product_id . "'
        ");

        // Update marketplace stock
        $api = $this->getApiClient();
        $marketplace_product_id = $this->getMarketplaceProductId($product_id);

        if ($marketplace_product_id) {
            return $api->updateStock($marketplace_product_id, $quantity);
        }

        return false;
    }

    /**
     * Get sales analytics
     */
    public function getSalesAnalytics(): array {
        $data = array();

        // Daily sales for last 30 days
        $query = $this->db->query("
            SELECT DATE(o.date_added) as date,
                   COUNT(*) as orders,
                   SUM(o.total) as revenue
            FROM " . DB_PREFIX . "order o
            INNER JOIN " . DB_PREFIX . "meschain_order_integration moi
            ON o.order_id = moi.order_id
            WHERE moi.marketplace = 'hepsiburada'
            AND o.date_added >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(o.date_added)
            ORDER BY date ASC
        ");

        $data['daily_sales'] = $query->rows;

        // Top selling products
        $query = $this->db->query("
            SELECT op.product_id,
                   op.name,
                   SUM(op.quantity) as quantity,
                   SUM(op.total) as revenue
            FROM " . DB_PREFIX . "order_product op
            INNER JOIN " . DB_PREFIX . "order o ON op.order_id = o.order_id
            INNER JOIN " . DB_PREFIX . "meschain_order_integration moi
            ON o.order_id = moi.order_id
            WHERE moi.marketplace = 'hepsiburada'
            AND o.date_added >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY op.product_id
            ORDER BY quantity DESC
            LIMIT 10
        ");

        $data['top_products'] = $query->rows;

        return $data;
    }

    /**
     * Get product performance
     */
    public function getProductPerformance(): array {
        $query = $this->db->query("
            SELECT p.product_id,
                   pd.name,
                   p.viewed,
                   COUNT(DISTINCT op.order_id) as orders,
                   SUM(op.quantity) as quantity_sold,
                   AVG(r.rating) as avg_rating
            FROM " . DB_PREFIX . "product p
            LEFT JOIN " . DB_PREFIX . "product_description pd
            ON p.product_id = pd.product_id
            LEFT JOIN " . DB_PREFIX . "order_product op
            ON p.product_id = op.product_id
            LEFT JOIN " . DB_PREFIX . "review r
            ON p.product_id = r.product_id
            INNER JOIN " . DB_PREFIX . "meschain_product_sync mps
            ON p.product_id = mps.product_id
            WHERE mps.marketplace = 'hepsiburada'
            AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            GROUP BY p.product_id
            ORDER BY quantity_sold DESC
            LIMIT 20
        ");

        return $query->rows;
    }

    /**
     * Get order statistics
     */
    public function getOrderStatistics(): array {
        $stats = array();

        // Status distribution
        $query = $this->db->query("
            SELECT os.name, COUNT(*) as count
            FROM " . DB_PREFIX . "order o
            INNER JOIN " . DB_PREFIX . "order_status os
            ON o.order_status_id = os.order_status_id
            INNER JOIN " . DB_PREFIX . "meschain_order_integration moi
            ON o.order_id = moi.order_id
            WHERE moi.marketplace = 'hepsiburada'
            AND os.language_id = '" . (int)$this->config->get('config_language_id') . "'
            GROUP BY o.order_status_id
        ");

        $stats['status_distribution'] = $query->rows;

        // Average order value
        $query = $this->db->query("
            SELECT AVG(o.total) as avg_order_value
            FROM " . DB_PREFIX . "order o
            INNER JOIN " . DB_PREFIX . "meschain_order_integration moi
            ON o.order_id = moi.order_id
            WHERE moi.marketplace = 'hepsiburada'
        ");

        $stats['avg_order_value'] = (float)$query->row['avg_order_value'];

        return $stats;
    }

    /**
     * Get API client
     */
    private function getApiClient() {
        require_once DIR_SYSTEM . 'library/meschain/api/hepsiburada.php';

        $config = array(
            'api_key' => $this->config->get('meschain_hepsiburada_api_key'),
            'api_secret' => $this->config->get('meschain_hepsiburada_api_secret'),
            'merchant_id' => $this->config->get('meschain_hepsiburada_merchant_id')
        );

        return new \MesChain\Api\Hepsiburada($config);
    }

    /**
     * Get product by marketplace ID
     */
    private function getProductByMarketplaceId($marketplace_id): int {
        $query = $this->db->query("
            SELECT product_id
            FROM " . DB_PREFIX . "meschain_product_sync
            WHERE marketplace = 'hepsiburada'
            AND marketplace_product_id = '" . $this->db->escape($marketplace_id) . "'
        ");

        return $query->row ? (int)$query->row['product_id'] : 0;
    }

    /**
     * Get marketplace product ID
     */
    private function getMarketplaceProductId($product_id): ?string {
        $query = $this->db->query("
            SELECT marketplace_product_id
            FROM " . DB_PREFIX . "meschain_product_sync
            WHERE marketplace = 'hepsiburada'
            AND product_id = '" . (int)$product_id . "'
        ");

        return $query->row ? $query->row['marketplace_product_id'] : null;
    }

    /**
     * Create product
     */
    private function createProduct($marketplace_product): int {
        // Implementation for creating product
        // This would map marketplace data to OpenCart product structure
        return 0;
    }

    /**
     * Update product
     */
    private function updateProduct($product_id, $marketplace_product): bool {
        // Implementation for updating product
        // This would map marketplace data to OpenCart product structure
        return true;
    }
}
