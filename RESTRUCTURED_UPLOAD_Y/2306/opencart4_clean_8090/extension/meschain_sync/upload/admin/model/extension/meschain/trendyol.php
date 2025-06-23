<?php

/**
 * MesChain Trendyol Model - OpenCart 4.x
 * Mevcut helper kodundan adapte edilmiştir
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @since OpenCart 4.0.2.3
 */

namespace Opencart\Admin\Model\Extension\Meschain;

class TrendyolMeschain extends \Opencart\System\Engine\Model
{

    // Trendyol API endpoints
    const API_BASE_URL = 'https://api.trendyol.com/sapigw/';
    const API_SANDBOX_URL = 'https://api.trendyol.com/stageapi/';

    // API rate limits
    const RATE_LIMIT_PER_MINUTE = 600;
    const RATE_LIMIT_PER_HOUR = 36000;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->createTables();
    }

    /**
     * Test API connection
     */
    public function testApiConnection(): array
    {
        try {
            $response = $this->makeApiRequest('suppliers/' . $this->config->get('meschain_trendyol_supplier_id'));

            if ($response['success']) {
                return [
                    'success' => true,
                    'data' => $response['data']
                ];
            }

            return [
                'success' => false,
                'error' => $response['error'] ?? 'API bağlantısı başarısız'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Make API request with error handling and retry logic
     * Mevcut helper'dan adapte edildi
     */
    public function makeApiRequest(string $endpoint, string $method = 'GET', array $data = [], int $retries = 3): array
    {
        $url = $this->getApiBaseUrl() . ltrim($endpoint, '/');
        $attempt = 0;

        while ($attempt < $retries) {
            $attempt++;

            try {
                $curl = curl_init();

                $options = [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_HTTPHEADER => $this->getApiHeaders(),
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 3
                ];

                // Set HTTP method and data
                switch (strtoupper($method)) {
                    case 'POST':
                        $options[CURLOPT_POST] = true;
                        if (!empty($data)) {
                            $options[CURLOPT_POSTFIELDS] = json_encode($data);
                        }
                        break;
                    case 'PUT':
                        $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
                        if (!empty($data)) {
                            $options[CURLOPT_POSTFIELDS] = json_encode($data);
                        }
                        break;
                    case 'DELETE':
                        $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                        break;
                    case 'PATCH':
                        $options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
                        if (!empty($data)) {
                            $options[CURLOPT_POSTFIELDS] = json_encode($data);
                        }
                        break;
                }

                curl_setopt_array($curl, $options);

                $response = curl_exec($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $error = curl_error($curl);

                curl_close($curl);

                if ($error) {
                    throw new \Exception('CURL Error: ' . $error);
                }

                // Parse response
                $decodedResponse = json_decode($response, true);

                if ($httpCode >= 200 && $httpCode < 300) {
                    $this->logApiCall($method, $url, $data, $httpCode, $decodedResponse, true);
                    return [
                        'success' => true,
                        'data' => $decodedResponse,
                        'http_code' => $httpCode
                    ];
                }

                // Handle rate limiting
                if ($httpCode == 429) {
                    if ($attempt < $retries) {
                        sleep(60); // Wait 1 minute for rate limit
                        continue;
                    }
                }

                // Handle server errors (5xx) - retry
                if ($httpCode >= 500 && $attempt < $retries) {
                    sleep(pow(2, $attempt)); // Exponential backoff
                    continue;
                }

                // Client error or final attempt
                $errorMessage = $decodedResponse['message'] ?? 'HTTP Error ' . $httpCode;
                $this->logApiCall($method, $url, $data, $httpCode, $decodedResponse, false);

                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'http_code' => $httpCode,
                    'response' => $decodedResponse
                ];
            } catch (\Exception $e) {
                if ($attempt >= $retries) {
                    $this->logApiCall($method, $url, $data, 0, null, false, $e->getMessage());
                    return [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'http_code' => 0
                    ];
                }
                sleep(pow(2, $attempt));
            }
        }

        return [
            'success' => false,
            'error' => 'Max retries exceeded',
            'http_code' => 0
        ];
    }

    /**
     * Get API base URL based on test mode
     */
    private function getApiBaseUrl(): string
    {
        return $this->config->get('meschain_trendyol_test_mode') ? self::API_SANDBOX_URL : self::API_BASE_URL;
    }

    /**
     * Generate API request headers
     */
    private function getApiHeaders(): array
    {
        return [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: MesChain-OpenCart/4.0',
            'Authorization: Basic ' . base64_encode(
                $this->config->get('meschain_trendyol_api_key') . ':' .
                    $this->config->get('meschain_trendyol_api_secret')
            )
        ];
    }

    /**
     * Sync products to Trendyol
     */
    public function syncProducts(): array
    {
        try {
            $this->load->model('catalog/product');

            $products = $this->model_catalog_product->getProducts(['start' => 0, 'limit' => 100]);
            $syncedCount = 0;
            $errors = [];

            foreach ($products as $product) {
                $result = $this->uploadProduct($product);
                if ($result['success']) {
                    $syncedCount++;
                    $this->saveTrendyolProduct($product['product_id'], $result['data']);
                } else {
                    $errors[] = $product['name'] . ': ' . $result['error'];
                }
            }

            return [
                'success' => true,
                'synced_count' => $syncedCount,
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
     * Sync orders from Trendyol
     */
    public function syncOrders(): array
    {
        try {
            $filters = [
                'startDate' => date('Y-m-d', strtotime('-30 days')),
                'endDate' => date('Y-m-d'),
                'page' => 0,
                'size' => 100
            ];

            $response = $this->getOrders($filters);

            if (!$response['success']) {
                return [
                    'success' => false,
                    'error' => $response['error']
                ];
            }

            $orders = $response['data']['content'] ?? [];
            $syncedCount = 0;

            foreach ($orders as $order) {
                if ($this->saveTrendyolOrder($order)) {
                    $syncedCount++;
                }
            }

            return [
                'success' => true,
                'synced_count' => $syncedCount
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload product to Trendyol
     */
    public function uploadProduct(array $product): array
    {
        $formattedProduct = $this->formatProductForTrendyol($product);

        return $this->makeApiRequest(
            'suppliers/' . $this->config->get('meschain_trendyol_supplier_id') . '/products',
            'POST',
            ['items' => [$formattedProduct]]
        );
    }

    /**
     * Get orders from Trendyol
     */
    public function getOrders(array $filters = []): array
    {
        $queryString = http_build_query($filters);
        $endpoint = 'suppliers/' . $this->config->get('meschain_trendyol_supplier_id') . '/orders';

        if ($queryString) {
            $endpoint .= '?' . $queryString;
        }

        return $this->makeApiRequest($endpoint);
    }

    /**
     * Format product data for Trendyol API
     * Mevcut helper'dan adapte edildi
     */
    private function formatProductForTrendyol(array $product): array
    {
        // Get product images
        $this->load->model('catalog/product');
        $images = $this->model_catalog_product->getProductImages($product['product_id']);

        $formatted = [
            'title' => $this->cleanString($product['name']),
            'productMainId' => $product['model'] ?: $product['sku'],
            'brandId' => $product['manufacturer_id'] ?? 1,
            'categoryId' => $this->mapCategoryToTrendyol($product['category_id'] ?? 0),
            'quantity' => max(0, (int)$product['quantity']),
            'stockCode' => $product['sku'] ?: $product['model'],
            'dimensionalWeight' => 1,
            'description' => $this->cleanHtml($product['description']),
            'currencyType' => 'TRY',
            'listPrice' => (float)$product['price'],
            'salePrice' => (float)($product['special'] ?: $product['price']),
            'vatRate' => 18,
            'cargoCompanyId' => 10, // Default cargo company
            'images' => $this->formatProductImages($images),
            'attributes' => []
        ];

        // Add barcode if available
        if (!empty($product['ean'])) {
            $formatted['barcode'] = $product['ean'];
        }

        return $formatted;
    }

    /**
     * Format product images for Trendyol
     */
    private function formatProductImages(array $images): array
    {
        $formatted = [];

        foreach ($images as $index => $image) {
            $formatted[] = [
                'url' => HTTP_CATALOG . 'image/' . $image['image'],
                'order' => $index + 1
            ];
        }

        return $formatted;
    }

    /**
     * Map OpenCart category to Trendyol category
     */
    private function mapCategoryToTrendyol(int $categoryId): int
    {
        // Get category mapping from database
        $query = $this->db->query("SELECT trendyol_category_id FROM `" . DB_PREFIX . "trendyol_category_mapping` WHERE opencart_category_id = '" . (int)$categoryId . "'");

        if ($query->num_rows) {
            return (int)$query->row['trendyol_category_id'];
        }

        // Default category ID
        return 411;
    }

    /**
     * Clean string for API submission
     * Mevcut helper'dan korundu
     */
    private function cleanString(string $text): string
    {
        // Remove HTML tags
        $text = strip_tags($text);

        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim
        $text = trim($text);

        // Remove special characters that Trendyol doesn't accept
        $text = preg_replace('/[^\p{L}\p{N}\s\-\_\.\,\!\?\(\)]/u', '', $text);

        return $text;
    }

    /**
     * Clean HTML content
     * Mevcut helper'dan korundu
     */
    private function cleanHtml(string $html): string
    {
        // Remove script and style tags
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        $html = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $html);

        // Convert to plain text
        $text = strip_tags($html);

        // Clean up
        return $this->cleanString($text);
    }

    /**
     * Save Trendyol product mapping
     */
    public function saveTrendyolProduct(int $productId, array $trendyolData): void
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_products` SET
            opencart_product_id = '" . (int)$productId . "',
            trendyol_product_id = '" . $this->db->escape($trendyolData['productId'] ?? '') . "',
            barcode = '" . $this->db->escape($trendyolData['barcode'] ?? '') . "',
            status = 'pending',
            created_at = NOW(),
            updated_at = NOW()
            ON DUPLICATE KEY UPDATE
            trendyol_product_id = VALUES(trendyol_product_id),
            barcode = VALUES(barcode),
            updated_at = NOW()");
    }

    /**
     * Save Trendyol order
     */
    public function saveTrendyolOrder(array $orderData): bool
    {
        try {
            // Check if order already exists
            $query = $this->db->query("SELECT order_id FROM `" . DB_PREFIX . "trendyol_orders` WHERE order_number = '" . $this->db->escape($orderData['orderNumber']) . "'");

            if ($query->num_rows) {
                return false; // Order already exists
            }

            $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_orders` SET
                order_number = '" . $this->db->escape($orderData['orderNumber']) . "',
                gross_amount = '" . (float)($orderData['grossAmount'] ?? 0) . "',
                total_discount = '" . (float)($orderData['totalDiscount'] ?? 0) . "',
                customer_name = '" . $this->db->escape(($orderData['customerFirstName'] ?? '') . ' ' . ($orderData['customerLastName'] ?? '')) . "',
                customer_email = '" . $this->db->escape($orderData['customerEmail'] ?? '') . "',
                order_date = '" . $this->db->escape(date('Y-m-d H:i:s', ($orderData['orderDate'] ?? time()) / 1000)) . "',
                status = '" . $this->db->escape($orderData['status'] ?? 'Created') . "',
                order_data = '" . $this->db->escape(json_encode($orderData)) . "',
                created_at = NOW(),
                updated_at = NOW()");

            return true;
        } catch (\Exception $e) {
            $this->log->write('Trendyol Order Save Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get products with filters
     */
    public function getProducts(array $filter = []): array
    {
        $sql = "SELECT tp.*, p.name, p.model, p.sku, p.price, p.quantity, p.status as product_status
                FROM `" . DB_PREFIX . "trendyol_products` tp
                LEFT JOIN `" . DB_PREFIX . "product` p ON tp.opencart_product_id = p.product_id
                WHERE 1=1";

        if (!empty($filter['filter_name'])) {
            $sql .= " AND p.name LIKE '%" . $this->db->escape($filter['filter_name']) . "%'";
        }

        if (!empty($filter['filter_status'])) {
            $sql .= " AND tp.status = '" . $this->db->escape($filter['filter_status']) . "'";
        }

        if (!empty($filter['filter_barcode'])) {
            $sql .= " AND tp.barcode LIKE '%" . $this->db->escape($filter['filter_barcode']) . "%'";
        }

        $sql .= " ORDER BY tp.updated_at DESC";

        if (isset($filter['start']) && isset($filter['limit'])) {
            $sql .= " LIMIT " . (int)$filter['start'] . "," . (int)$filter['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
     * Get total products count
     */
    public function getTotalProducts(array $filter = []): int
    {
        $sql = "SELECT COUNT(*) as total
                FROM `" . DB_PREFIX . "trendyol_products` tp
                LEFT JOIN `" . DB_PREFIX . "product` p ON tp.opencart_product_id = p.product_id
                WHERE 1=1";

        if (!empty($filter['filter_name'])) {
            $sql .= " AND p.name LIKE '%" . $this->db->escape($filter['filter_name']) . "%'";
        }

        if (!empty($filter['filter_status'])) {
            $sql .= " AND tp.status = '" . $this->db->escape($filter['filter_status']) . "'";
        }

        if (!empty($filter['filter_barcode'])) {
            $sql .= " AND tp.barcode LIKE '%" . $this->db->escape($filter['filter_barcode']) . "%'";
        }

        $query = $this->db->query($sql);

        return (int)$query->row['total'];
    }

    /**
     * Get orders from local database with filters
     */
    public function getLocalOrders(array $filter = []): array
    {
        $sql = "SELECT * FROM `" . DB_PREFIX . "trendyol_orders` WHERE 1=1";

        if (!empty($filter['filter_order_number'])) {
            $sql .= " AND order_number LIKE '%" . $this->db->escape($filter['filter_order_number']) . "%'";
        }

        if (!empty($filter['filter_status'])) {
            $sql .= " AND status = '" . $this->db->escape($filter['filter_status']) . "'";
        }

        if (!empty($filter['filter_date_from'])) {
            $sql .= " AND DATE(order_date) >= '" . $this->db->escape($filter['filter_date_from']) . "'";
        }

        if (!empty($filter['filter_date_to'])) {
            $sql .= " AND DATE(order_date) <= '" . $this->db->escape($filter['filter_date_to']) . "'";
        }

        $sql .= " ORDER BY order_date DESC";

        if (isset($filter['start']) && isset($filter['limit'])) {
            $sql .= " LIMIT " . (int)$filter['start'] . "," . (int)$filter['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
     * Get total orders count
     */
    public function getTotalOrders(array $filter = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM `" . DB_PREFIX . "trendyol_orders` WHERE 1=1";

        if (!empty($filter['filter_order_number'])) {
            $sql .= " AND order_number LIKE '%" . $this->db->escape($filter['filter_order_number']) . "%'";
        }

        if (!empty($filter['filter_status'])) {
            $sql .= " AND status = '" . $this->db->escape($filter['filter_status']) . "'";
        }

        if (!empty($filter['filter_date_from'])) {
            $sql .= " AND DATE(order_date) >= '" . $this->db->escape($filter['filter_date_from']) . "'";
        }

        if (!empty($filter['filter_date_to'])) {
            $sql .= " AND DATE(order_date) <= '" . $this->db->escape($filter['filter_date_to']) . "'";
        }

        $query = $this->db->query($sql);

        return (int)$query->row['total'];
    }

    /**
     * Get statistics methods
     */
    public function getOrdersCount(array $filter = []): int
    {
        return $this->getTotalOrders($filter);
    }

    public function getProductsCount(array $filter = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM `" . DB_PREFIX . "trendyol_products` WHERE 1=1";

        if (!empty($filter['status'])) {
            $sql .= " AND status = '" . $this->db->escape($filter['status']) . "'";
        }

        $query = $this->db->query($sql);

        return (int)$query->row['total'];
    }

    public function getRecentOrders(int $limit = 10): array
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_orders` ORDER BY created_at DESC LIMIT " . (int)$limit);

        return $query->rows;
    }

    public function getRecentApiLogs(int $limit = 10): array
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_api_logs` ORDER BY created_at DESC LIMIT " . (int)$limit);

        return $query->rows;
    }

    public function getWebhookStatus(): array
    {
        $query = $this->db->query("SELECT COUNT(*) as total,
                                          SUM(CASE WHEN processed = 1 THEN 1 ELSE 0 END) as processed,
                                          SUM(CASE WHEN processed = 0 THEN 1 ELSE 0 END) as pending
                                   FROM `" . DB_PREFIX . "trendyol_webhook_logs`
                                   WHERE DATE(received_at) = CURDATE()");

        if ($query->num_rows) {
            return $query->row;
        }

        return ['total' => 0, 'processed' => 0, 'pending' => 0];
    }

    public function getLastSyncTime(): ?string
    {
        $query = $this->db->query("SELECT MAX(updated_at) as last_sync FROM `" . DB_PREFIX . "trendyol_products`");

        if ($query->num_rows && $query->row['last_sync']) {
            return $query->row['last_sync'];
        }

        return null;
    }

    /**
     * Log API call for debugging
     */
    private function logApiCall(string $method, string $url, array $data, int $httpCode, ?array $response, bool $success, string $error = ''): void
    {
        if ($this->config->get('meschain_trendyol_debug')) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_api_logs` SET
                method = '" . $this->db->escape($method) . "',
                url = '" . $this->db->escape($url) . "',
                request_data = '" . $this->db->escape(json_encode($data)) . "',
                response_data = '" . $this->db->escape(json_encode($response)) . "',
                http_code = '" . (int)$httpCode . "',
                success = '" . (int)$success . "',
                error_message = '" . $this->db->escape($error) . "',
                created_at = NOW()");
        }
    }

    /**
     * Create necessary database tables
     */
    private function createTables(): void
    {
        // Trendyol products table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_products` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_product_id` int(11) NOT NULL,
            `trendyol_product_id` varchar(100),
            `barcode` varchar(50),
            `status` enum('pending','active','rejected','inactive') DEFAULT 'pending',
            `approval_status` tinyint(1) DEFAULT 0,
            `rejection_reason` text,
            `last_sync` datetime,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `opencart_product_id` (`opencart_product_id`),
            KEY `barcode` (`barcode`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Trendyol orders table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_orders` (
            `order_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_number` varchar(100) NOT NULL,
            `opencart_order_id` int(11) DEFAULT NULL,
            `gross_amount` decimal(15,4) DEFAULT 0.0000,
            `total_discount` decimal(15,4) DEFAULT 0.0000,
            `customer_name` varchar(255),
            `customer_email` varchar(255),
            `order_date` datetime,
            `status` varchar(50),
            `tracking_number` varchar(100),
            `cargo_provider` varchar(100),
            `order_data` json,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`order_id`),
            UNIQUE KEY `order_number` (`order_number`),
            KEY `opencart_order_id` (`opencart_order_id`),
            KEY `status` (`status`),
            KEY `order_date` (`order_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Trendyol API logs table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_api_logs` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `method` varchar(10) NOT NULL,
            `url` varchar(500) NOT NULL,
            `request_data` json,
            `response_data` json,
            `http_code` int(11) DEFAULT 0,
            `success` tinyint(1) DEFAULT 0,
            `error_message` text,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`log_id`),
            KEY `method` (`method`),
            KEY `success` (`success`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Trendyol webhook logs table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_webhook_logs` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `event_type` varchar(100) NOT NULL,
            `event_data` json NOT NULL,
            `signature` varchar(255),
            `processed` tinyint(1) DEFAULT 0,
            `processed_at` datetime DEFAULT NULL,
            `error_message` text,
            `response_sent` text,
            `ip_address` varchar(45),
            `user_agent` varchar(255),
            `received_at` datetime NOT NULL,
            PRIMARY KEY (`log_id`),
            KEY `event_type` (`event_type`),
            KEY `processed` (`processed`),
            KEY `received_at` (`received_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Category mapping table
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_category_mapping` (
            `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_category_id` int(11) NOT NULL,
            `trendyol_category_id` int(11) NOT NULL,
            `category_name` varchar(255),
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`mapping_id`),
            UNIQUE KEY `opencart_category_id` (`opencart_category_id`),
            KEY `trendyol_category_id` (`trendyol_category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }
}
