<?php
namespace Opencart\Admin\Model\Extension\Module\Meschain;

/**
 * MesChain Trendyol Model
 * OpenCart 4.0.2.3 Compatible
 *
 * @author Cursor Development Team
 * @version 1.0.0
 */
class Trendyol extends \Opencart\System\Engine\Model {

    /**
     * Get Trendyol API Client
     */
    public function getApiClient() {
        // Load Trendyol API configuration
        $config = [
            'api_key' => $this->config->get('module_meschain_sync_trendyol_api_key') ?: 'f4KhSfv7ihjXcJFlJiem',
            'api_secret' => $this->config->get('module_meschain_sync_trendyol_api_secret') ?: 'GLs2YLpJwPJtEX6dSPbi',
            'supplier_id' => $this->config->get('module_meschain_sync_trendyol_supplier_id') ?: '1076956'
        ];

        // Load the Trendyol API class
        require_once DIR_SYSTEM . 'library/meschain/api/trendyol.php';
        
        return new \MesChain\Api\Trendyol($config);
    }

    /**
     * Test Trendyol connection
     */
    public function testConnection(): array {
        try {
            $api = $this->getApiClient();
            return $api->testConnection();
        } catch (\Exception $e) {
            throw new \Exception('Trendyol connection test failed: ' . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Sync Trendyol products
     */
    public function syncProducts(): array {
        try {
            $api = $this->getApiClient();
            
            // Get products from Trendyol
            $trendyolProducts = $api->getProducts([
                'page' => 0,
                'size' => 100
            ]);

            $syncedCount = 0;
            $errors = [];

            if (isset($trendyolProducts['content'])) {
                foreach ($trendyolProducts['content'] as $product) {
                    try {
                        $this->syncSingleProduct($product);
                        $syncedCount++;
                    } catch (\Exception $e) {
                        $errors[] = [
                            'product_id' => $product['id'] ?? 'unknown',
                            'error' => $e->getMessage()
                        ];
                    }
                }
            }

            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'total_products' => count($trendyolProducts['content'] ?? []),
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Sync single product from Trendyol
     */
    private function syncSingleProduct(array $trendyolProduct): void {
        // Check if product exists in OpenCart
        $existingProduct = $this->getProductByBarcode($trendyolProduct['barcode'] ?? '');

        if ($existingProduct) {
            // Update existing product
            $this->updateProduct($existingProduct['product_id'], $trendyolProduct);
        } else {
            // Create new product
            $this->createProduct($trendyolProduct);
        }

        // Log sync activity
        $this->logSync($trendyolProduct['id'] ?? 'unknown', 'success');
    }

    /**
     * Get product by barcode
     */
    private function getProductByBarcode(string $barcode): ?array {
        if (empty($barcode)) {
            return null;
        }

        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "product 
            WHERE ean = '" . $this->db->escape($barcode) . "' 
            OR upc = '" . $this->db->escape($barcode) . "' 
            OR isbn = '" . $this->db->escape($barcode) . "' 
            OR mpn = '" . $this->db->escape($barcode) . "'
            LIMIT 1
        ");

        return $query->row ?: null;
    }

    /**
     * Update existing product
     */
    private function updateProduct(int $productId, array $trendyolProduct): void {
        // Update product data
        $this->db->query("
            UPDATE " . DB_PREFIX . "product 
            SET 
                quantity = '" . (int)($trendyolProduct['stockQuantity'] ?? 0) . "',
                price = '" . (float)($trendyolProduct['salePrice'] ?? 0) . "',
                date_modified = NOW()
            WHERE product_id = " . (int)$productId
        );

        // Update sync status
        $this->updateSyncStatus($productId, 'trendyol', $trendyolProduct['id'] ?? '', 'success');
    }

    /**
     * Create new product
     */
    private function createProduct(array $trendyolProduct): int {
        // Insert basic product data
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "product 
            (model, sku, upc, ean, quantity, price, status, date_added, date_modified)
            VALUES (
                '" . $this->db->escape($trendyolProduct['productCode'] ?? '') . "',
                '" . $this->db->escape($trendyolProduct['productCode'] ?? '') . "',
                '" . $this->db->escape($trendyolProduct['barcode'] ?? '') . "',
                '" . $this->db->escape($trendyolProduct['barcode'] ?? '') . "',
                '" . (int)($trendyolProduct['stockQuantity'] ?? 0) . "',
                '" . (float)($trendyolProduct['salePrice'] ?? 0) . "',
                '1',
                NOW(),
                NOW()
            )
        ");

        $productId = $this->db->getLastId();

        // Insert product description
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "product_description 
            (product_id, language_id, name, description, meta_title)
            VALUES (
                " . (int)$productId . ",
                " . (int)$this->config->get('config_language_id') . ",
                '" . $this->db->escape($trendyolProduct['title'] ?? 'Trendyol Product') . "',
                '" . $this->db->escape($trendyolProduct['description'] ?? '') . "',
                '" . $this->db->escape($trendyolProduct['title'] ?? 'Trendyol Product') . "'
            )
        ");

        // Update sync status
        $this->updateSyncStatus($productId, 'trendyol', $trendyolProduct['id'] ?? '', 'success');

        return $productId;
    }

    /**
     * Update sync status
     */
    private function updateSyncStatus(int $productId, string $marketplace, string $marketplaceProductId, string $status): void {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_product_sync 
            (product_id, marketplace, marketplace_product_id, sync_status, last_sync)
            VALUES (
                " . (int)$productId . ",
                '" . $this->db->escape($marketplace) . "',
                '" . $this->db->escape($marketplaceProductId) . "',
                '" . $this->db->escape($status) . "',
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                marketplace_product_id = VALUES(marketplace_product_id),
                sync_status = VALUES(sync_status),
                last_sync = VALUES(last_sync)
        ");
    }

    /**
     * Log sync activity
     */
    private function logSync(string $productId, string $status, string $message = ''): void {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_logs 
            (log_level, log_type, log_message, marketplace, date_added)
            VALUES (
                'info',
                'product_sync',
                'Trendyol product sync: " . $this->db->escape($productId) . " - " . $this->db->escape($status) . " " . $this->db->escape($message) . "',
                'trendyol',
                NOW()
            )
        ");
    }
}