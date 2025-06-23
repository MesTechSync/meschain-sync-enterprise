<?php
/**
 * MesChain Trendyol API Client
 * Native OpenCart 4.x API Library
 * Path: system/library/meschain/api/trendyol.php
 */

namespace MesChain\Api;

class Trendyol {
    
    private $api_key;
    private $api_secret;
    private $supplier_id;
    private $base_url;
    private $timeout;
    private $sandbox;
    
    public function __construct($credentials) {
        $this->api_key = $credentials['api_key'] ?? '';
        $this->api_secret = $credentials['api_secret'] ?? '';
        $this->supplier_id = $credentials['supplier_id'] ?? '';
        $this->sandbox = $credentials['environment'] === 'sandbox';
        
        $this->base_url = $this->sandbox 
            ? 'https://sandbox-api.trendyol.com' 
            : 'https://api.trendyol.com';
            
        $this->timeout = $credentials['timeout'] ?? 30;
    }
    
    /**
     * Test API connection
     */
    public function testConnection() {
        try {
            $response = $this->makeRequest('GET', '/sapigw/suppliers/' . $this->supplier_id);
            
            if (isset($response['id']) && $response['id'] == $this->supplier_id) {
                return [
                    'valid' => true,
                    'message' => 'Connection successful',
                    'data' => $response
                ];
            }
            
            return [
                'valid' => false,
                'message' => 'Invalid supplier ID'
            ];
            
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get supplier information
     */
    public function getSupplierInfo() {
        return $this->makeRequest('GET', '/sapigw/suppliers/' . $this->supplier_id);
    }
    
    /**
     * Get categories
     */
    public function getCategories() {
        return $this->makeRequest('GET', '/sapigw/product-categories');
    }
    
    /**
     * Get category attributes
     */
    public function getCategoryAttributes($category_id) {
        return $this->makeRequest('GET', '/sapigw/product-categories/' . $category_id . '/attributes');
    }
    
    /**
     * Create product
     */
    public function createProduct($product_data) {
        return $this->makeRequest('POST', '/sapigw/suppliers/' . $this->supplier_id . '/v2/products', $product_data);
    }
    
    /**
     * Update product
     */
    public function updateProduct($barcode, $product_data) {
        return $this->makeRequest('PUT', '/sapigw/suppliers/' . $this->supplier_id . '/v2/products', $product_data);
    }
    
    /**
     * Get product by barcode
     */
    public function getProduct($barcode) {
        return $this->makeRequest('GET', '/sapigw/suppliers/' . $this->supplier_id . '/products?barcode=' . $barcode);
    }
    
    /**
     * Update product price and inventory
     */
    public function updatePriceAndStock($items) {
        return $this->makeRequest('POST', '/sapigw/suppliers/' . $this->supplier_id . '/products/price-and-inventory', [
            'items' => $items
        ]);
    }
    
    /**
     * Get orders
     */
    public function getOrders($params = []) {
        $query_string = http_build_query($params);
        $endpoint = '/sapigw/suppliers/' . $this->supplier_id . '/orders';
        
        if ($query_string) {
            $endpoint .= '?' . $query_string;
        }
        
        return $this->makeRequest('GET', $endpoint);
    }
    
    /**
     * Get order details
     */
    public function getOrderDetails($order_number) {
        return $this->makeRequest('GET', '/sapigw/suppliers/' . $this->supplier_id . '/orders/' . $order_number);
    }
    
    /**
     * Update order items (approve/reject)
     */
    public function updateOrderItems($order_number, $items) {
        return $this->makeRequest('PUT', '/sapigw/suppliers/' . $this->supplier_id . '/orders/' . $order_number . '/items', [
            'orderItems' => $items
        ]);
    }
    
    /**
     * Create shipment
     */
    public function createShipment($shipment_data) {
        return $this->makeRequest('POST', '/sapigw/suppliers/' . $this->supplier_id . '/shipment-packages', $shipment_data);
    }
    
    /**
     * Update tracking information
     */
    public function updateTracking($package_id, $tracking_data) {
        return $this->makeRequest('PUT', '/sapigw/suppliers/' . $this->supplier_id . '/shipment-packages/' . $package_id, $tracking_data);
    }
    
    /**
     * Get brands
     */
    public function getBrands() {
        return $this->makeRequest('GET', '/sapigw/brands');
    }
    
    /**
     * Get settlement reports
     */
    public function getSettlements($params = []) {
        $query_string = http_build_query($params);
        $endpoint = '/sapigw/suppliers/' . $this->supplier_id . '/settlements';
        
        if ($query_string) {
            $endpoint .= '?' . $query_string;
        }
        
        return $this->makeRequest('GET', $endpoint);
    }
    
    /**
     * Get returns
     */
    public function getReturns($params = []) {
        $query_string = http_build_query($params);
        $endpoint = '/sapigw/suppliers/' . $this->supplier_id . '/returns';
        
        if ($query_string) {
            $endpoint .= '?' . $query_string;
        }
        
        return $this->makeRequest('GET', $endpoint);
    }
    
    /**
     * Handle webhook data
     */
    public function processWebhook($webhook_data) {
        // Validate webhook signature if needed
        // Process different webhook types (order, return, etc.)
        
        $event_type = $webhook_data['eventType'] ?? '';
        
        switch ($event_type) {
            case 'OrderCreated':
                return $this->processOrderWebhook($webhook_data);
                
            case 'OrderStatusChanged':
                return $this->processOrderStatusWebhook($webhook_data);
                
            case 'ReturnCreated':
                return $this->processReturnWebhook($webhook_data);
                
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown webhook event type: ' . $event_type
                ];
        }
    }
    
    /**
     * Make HTTP request to Trendyol API
     */
    private function makeRequest($method, $endpoint, $data = null) {
        $url = $this->base_url . $endpoint;
        
        $headers = [
            'Authorization: Basic ' . base64_encode($this->api_key . ':' . $this->api_secret),
            'Content-Type: application/json',
            'User-Agent: MesChain-OpenCart/2.0'
        ];
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ]);
        
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
                
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
                
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('cURL Error: ' . $error);
        }
        
        $decoded_response = json_decode($response, true);
        
        if ($http_code >= 400) {
            $error_message = 'HTTP ' . $http_code;
            
            if (isset($decoded_response['message'])) {
                $error_message .= ': ' . $decoded_response['message'];
            } elseif (isset($decoded_response['errors'])) {
                $error_message .= ': ' . implode(', ', $decoded_response['errors']);
            }
            
            throw new \Exception($error_message);
        }
        
        return $decoded_response;
    }
    
    /**
     * Process order webhook
     */
    private function processOrderWebhook($webhook_data) {
        // Implement order webhook processing logic
        return [
            'success' => true,
            'message' => 'Order webhook processed successfully'
        ];
    }
    
    /**
     * Process order status webhook
     */
    private function processOrderStatusWebhook($webhook_data) {
        // Implement order status webhook processing logic
        return [
            'success' => true,
            'message' => 'Order status webhook processed successfully'
        ];
    }
    
    /**
     * Process return webhook
     */
    private function processReturnWebhook($webhook_data) {
        // Implement return webhook processing logic
        return [
            'success' => true,
            'message' => 'Return webhook processed successfully'
        ];
    }
    
    /**
     * Format product data for Trendyol
     */
    public function formatProductData($opencart_product) {
        return [
            'barcode' => $opencart_product['model'] ?? '',
            'title' => $opencart_product['name'] ?? '',
            'description' => $opencart_product['description'] ?? '',
            'brandId' => $opencart_product['brand_id'] ?? null,
            'categoryId' => $opencart_product['trendyol_category_id'] ?? null,
            'listPrice' => (float)($opencart_product['price'] ?? 0),
            'salePrice' => (float)($opencart_product['special'] ?? $opencart_product['price'] ?? 0),
            'vatRate' => (int)($opencart_product['vat_rate'] ?? 18),
            'dimensionalWeight' => (int)($opencart_product['weight'] ?? 0),
            'cargoCompanyId' => $opencart_product['cargo_company_id'] ?? 10, // Default cargo
            'images' => $this->formatProductImages($opencart_product['images'] ?? []),
            'attributes' => $this->formatProductAttributes($opencart_product['attributes'] ?? []),
            'stockQuantity' => (int)($opencart_product['quantity'] ?? 0)
        ];
    }
    
    /**
     * Format product images for Trendyol
     */
    private function formatProductImages($images) {
        $formatted_images = [];
        
        foreach ($images as $image) {
            $formatted_images[] = [
                'url' => $image['url'] ?? $image
            ];
        }
        
        return $formatted_images;
    }
    
    /**
     * Format product attributes for Trendyol
     */
    private function formatProductAttributes($attributes) {
        $formatted_attributes = [];
        
        foreach ($attributes as $attribute) {
            $formatted_attributes[] = [
                'attributeId' => $attribute['attribute_id'] ?? 0,
                'attributeValueId' => $attribute['attribute_value_id'] ?? 0,
                'customAttributeValue' => $attribute['custom_value'] ?? null
            ];
        }
        
        return $formatted_attributes;
    }
}
?>
