<?php
namespace MesChain\Helper;

/**
 * MesChain Trendyol Helper Class
 *
 * Trendyol API ile iletişim için yardımcı fonksiyonlar
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @since OpenCart 3.0.4.0
 */
class TrendyolHelper {

    private $config;
    private $log;

    // Trendyol API endpoints
    const API_BASE_URL = 'https://api.trendyol.com/sapigw/';
    const API_SANDBOX_URL = 'https://api.trendyol.com/stageapi/';

    // API rate limits
    const RATE_LIMIT_PER_MINUTE = 600;
    const RATE_LIMIT_PER_HOUR = 36000;

    public function __construct($config, $log = null) {
        $this->config = $config;
        $this->log = $log;
    }

    /**
     * Get API base URL based on test mode
     */
    public function getApiBaseUrl(): string {
        return $this->config->get('module_trendyol_test_mode') ? self::API_SANDBOX_URL : self::API_BASE_URL;
    }

    /**
     * Generate API request headers
     */
    public function getApiHeaders(): array {
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: MesChain-OpenCart/1.0',
            'Authorization: Basic ' . base64_encode(
                $this->config->get('module_trendyol_api_key') . ':' .
                $this->config->get('module_trendyol_api_secret')
            )
        ];

        return $headers;
    }

    /**
     * Make API request with error handling and retry logic
     */
    public function makeApiRequest(string $endpoint, string $method = 'GET', array $data = [], int $retries = 3): array {
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
                    $retryAfter = curl_getinfo($curl, CURLINFO_RETRY_AFTER) ?: 60;
                    if ($attempt < $retries) {
                        sleep(min($retryAfter, 60));
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
     * Validate API credentials
     */
    public function validateApiCredentials(): array {
        $response = $this->makeApiRequest('suppliers/' . $this->config->get('module_trendyol_supplier_id'));

        if ($response['success']) {
            return [
                'valid' => true,
                'supplier_info' => $response['data']
            ];
        }

        return [
            'valid' => false,
            'error' => $response['error'] ?? 'Invalid credentials'
        ];
    }

    /**
     * Format product data for Trendyol API
     */
    public function formatProductForTrendyol(array $product): array {
        $formatted = [
            'title' => $this->cleanString($product['name']),
            'productMainId' => $product['model'] ?: $product['sku'],
            'brandId' => $product['brand_id'] ?? 1,
            'categoryId' => $this->mapCategoryToTrendyol($product['category_id']),
            'quantity' => max(0, (int)$product['quantity']),
            'stockCode' => $product['sku'] ?: $product['model'],
            'dimensionalWeight' => 1,
            'description' => $this->cleanHtml($product['description']),
            'currencyType' => 'TRY',
            'listPrice' => (float)$product['price'],
            'salePrice' => (float)$product['special'] ?: (float)$product['price'],
            'vatRate' => 18,
            'cargoCompanyId' => 10, // Default cargo company
            'images' => $this->formatProductImages($product['images'] ?? []),
            'attributes' => $this->formatProductAttributes($product['attributes'] ?? [])
        ];

        // Add barcode if available
        if (!empty($product['ean'])) {
            $formatted['barcode'] = $product['ean'];
        }

        return $formatted;
    }

    /**
     * Format order data from Trendyol
     */
    public function formatOrderFromTrendyol(array $trendyolOrder): array {
        $formatted = [
            'marketplace_order_id' => $trendyolOrder['orderNumber'],
            'customer_name' => $trendyolOrder['customerFirstName'] . ' ' . $trendyolOrder['customerLastName'],
            'customer_email' => $trendyolOrder['customerEmail'] ?? '',
            'customer_phone' => $trendyolOrder['customerTckn'] ?? '',
            'total_amount' => (float)$trendyolOrder['totalPrice'],
            'currency_code' => 'TRY',
            'order_status' => $this->mapTrendyolOrderStatus($trendyolOrder['status']),
            'marketplace_status' => $trendyolOrder['status'],
            'order_date' => date('Y-m-d H:i:s', strtotime($trendyolOrder['orderDate'])),
            'shipping_address' => $this->formatShippingAddress($trendyolOrder['shipmentAddress']),
            'billing_address' => $this->formatBillingAddress($trendyolOrder['invoiceAddress']),
            'order_items' => $this->formatOrderItems($trendyolOrder['lines'])
        ];

        return $formatted;
    }

    /**
     * Map OpenCart category to Trendyol category
     */
    public function mapCategoryToTrendyol(int $categoryId): int {
        // This should be implemented based on category mapping configuration
        // For now, return default category
        return 411; // Default category ID
    }

    /**
     * Map Trendyol order status to OpenCart order status
     */
    public function mapTrendyolOrderStatus(string $trendyolStatus): string {
        $statusMap = [
            'Created' => 'pending',
            'Picking' => 'processing',
            'Invoiced' => 'processing',
            'Shipped' => 'shipped',
            'Delivered' => 'complete',
            'UnDelivered' => 'failed',
            'Cancelled' => 'canceled',
            'Returned' => 'returned'
        ];

        return $statusMap[$trendyolStatus] ?? 'pending';
    }

    /**
     * Clean string for API submission
     */
    public function cleanString(string $text): string {
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
     */
    public function cleanHtml(string $html): string {
        // Remove script and style tags
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        $html = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $html);

        // Convert to plain text
        $text = strip_tags($html);

        // Clean up
        return $this->cleanString($text);
    }

    /**
     * Format product images for Trendyol
     */
    private function formatProductImages(array $images): array {
        $formatted = [];

        foreach ($images as $index => $image) {
            $formatted[] = [
                'url' => $image['url'],
                'order' => $index + 1
            ];
        }

        return $formatted;
    }

    /**
     * Format product attributes for Trendyol
     */
    private function formatProductAttributes(array $attributes): array {
        $formatted = [];

        foreach ($attributes as $attribute) {
            $formatted[] = [
                'attributeId' => $attribute['attribute_id'],
                'attributeValueId' => $attribute['value_id'],
                'customAttributeValue' => $attribute['text'] ?? null
            ];
        }

        return $formatted;
    }

    /**
     * Format shipping address from Trendyol order
     */
    private function formatShippingAddress(array $address): array {
        return [
            'firstname' => $address['firstName'] ?? '',
            'lastname' => $address['lastName'] ?? '',
            'company' => $address['company'] ?? '',
            'address_1' => $address['address1'] ?? '',
            'address_2' => $address['address2'] ?? '',
            'city' => $address['city'] ?? '',
            'postcode' => $address['postalCode'] ?? '',
            'zone' => $address['district'] ?? '',
            'country' => $address['country'] ?? 'Turkey'
        ];
    }

    /**
     * Format billing address from Trendyol order
     */
    private function formatBillingAddress(array $address): array {
        return [
            'firstname' => $address['firstName'] ?? '',
            'lastname' => $address['lastName'] ?? '',
            'company' => $address['company'] ?? '',
            'address_1' => $address['address1'] ?? '',
            'address_2' => $address['address2'] ?? '',
            'city' => $address['city'] ?? '',
            'postcode' => $address['postalCode'] ?? '',
            'zone' => $address['district'] ?? '',
            'country' => $address['country'] ?? 'Turkey',
            'tax_id' => $address['taxNumber'] ?? ''
        ];
    }

    /**
     * Format order items from Trendyol order
     */
    private function formatOrderItems(array $lines): array {
        $items = [];

        foreach ($lines as $line) {
            $items[] = [
                'product_id' => $this->findProductByBarcode($line['barcode']),
                'name' => $line['productName'],
                'model' => $line['productCode'],
                'quantity' => (int)$line['quantity'],
                'price' => (float)$line['price'],
                'total' => (float)$line['totalPrice'],
                'barcode' => $line['barcode'],
                'marketplace_data' => json_encode($line)
            ];
        }

        return $items;
    }

    /**
     * Find product by barcode
     */
    private function findProductByBarcode(string $barcode): ?int {
        // This should query the database to find product by barcode/EAN
        // Implementation depends on the database structure
        return null; // Placeholder
    }

    /**
     * Log API call for debugging
     */
    private function logApiCall(string $method, string $url, array $data, int $httpCode, ?array $response, bool $success, string $error = ''): void {
        if ($this->config->get('module_trendyol_debug') && $this->log) {
            $logData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'method' => $method,
                'url' => $url,
                'request_data' => $data,
                'http_code' => $httpCode,
                'response' => $response,
                'success' => $success,
                'error' => $error
            ];

            $this->log->write('[TRENDYOL API] ' . json_encode($logData));
        }
    }

    /**
     * Generate webhook signature
     */
    public function generateWebhookSignature(string $payload, string $secret): string {
        return hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool {
        $expectedSignature = $this->generateWebhookSignature($payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Get category tree from Trendyol
     */
    public function getCategoryTree(): array {
        $response = $this->makeApiRequest('product-categories');

        if ($response['success']) {
            return $response['data']['categories'] ?? [];
        }

        return [];
    }

    /**
     * Get brand list from Trendyol
     */
    public function getBrandList(): array {
        $response = $this->makeApiRequest('brands');

        if ($response['success']) {
            return $response['data']['brands'] ?? [];
        }

        return [];
    }

    /**
     * Upload product to Trendyol
     */
    public function uploadProduct(array $product): array {
        $formattedProduct = $this->formatProductForTrendyol($product);

        return $this->makeApiRequest(
            'suppliers/' . $this->config->get('module_trendyol_supplier_id') . '/products',
            'POST',
            ['items' => [$formattedProduct]]
        );
    }

    /**
     * Update product on Trendyol
     */
    public function updateProduct(string $barcode, array $updates): array {
        return $this->makeApiRequest(
            'suppliers/' . $this->config->get('module_trendyol_supplier_id') . '/products/price-and-inventory',
            'POST',
            ['items' => [$updates]]
        );
    }

    /**
     * Get orders from Trendyol
     */
    public function getOrders(array $filters = []): array {
        $queryString = http_build_query($filters);
        $endpoint = 'suppliers/' . $this->config->get('module_trendyol_supplier_id') . '/orders';

        if ($queryString) {
            $endpoint .= '?' . $queryString;
        }

        return $this->makeApiRequest($endpoint);
    }
}
