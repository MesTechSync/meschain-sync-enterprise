<?php
/**
 * MesChain-Sync Çiçek Sepeti API Helper
 * 
 * @package     MesChain-Sync
 * @subpackage  Çiçek Sepeti API Helper
 * @category    API Integration
 * @author      MesChain Development Team
 * @copyright   2024 MesChain-Sync
 * @license     Commercial License
 * @version     1.0.0
 * @since       1.0.0
 */

class CiceksepetiApiHelper {
    
    /**
     * API Configuration
     */
    private $api_key;
    private $supplier_id;
    private $base_url = 'https://api.ciceksepeti.com/v2/';
    private $debug = false;
    private $timeout = 30;
    private $user_agent = 'MesChain-Sync/1.0';
    
    /**
     * Flower type mappings
     */
    private $flower_types = [
        'flower' => 'Çiçekler',
        'plant' => 'Bitkiler',
        'accessory' => 'Aksesuarlar',
        'special_occasion' => 'Özel Günler'
    ];
    
    /**
     * Constructor
     * 
     * @param string $api_key Çiçek Sepeti API Key
     * @param string $supplier_id Supplier ID
     * @param bool $debug Debug mode
     */
    public function __construct($api_key, $supplier_id, $debug = false) {
        $this->api_key = $api_key;
        $this->supplier_id = $supplier_id;
        $this->debug = $debug;
    }
    
    /**
     * Test API connection
     * 
     * @return array Response with success/error status
     */
    public function testConnection() {
        try {
            $response = $this->makeRequest('GET', 'auth/validate');
            
            if ($response && isset($response['status']) && $response['status'] === 'active') {
                return [
                    'success' => true,
                    'message' => 'Çiçek Sepeti API bağlantısı başarılı',
                    'data' => $response
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'API bağlantısı başarısız: ' . ($response['message'] ?? 'Bilinmeyen hata')
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Bağlantı hatası: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get flower categories
     * 
     * @param string $flower_type Flower type filter
     * @return array Categories list
     */
    public function getCategories($flower_type = null) {
        try {
            $endpoint = 'categories';
            if ($flower_type) {
                $endpoint .= '?type=' . urlencode($flower_type);
            }
            
            $response = $this->makeRequest('GET', $endpoint);
            
            if ($response && isset($response['categories'])) {
                return [
                    'success' => true,
                    'categories' => $response['categories']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Kategoriler alınamadı'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Kategori alma hatası: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Upload product to Çiçek Sepeti
     * 
     * @param array $product_data Product information
     * @return array Upload response
     */
    public function uploadProduct($product_data) {
        try {
            $ciceksepeti_product = $this->formatProductForCiceksepeti($product_data);
            
            $response = $this->makeRequest('POST', 'products', $ciceksepeti_product);
            
            if ($response && isset($response['product_id'])) {
                return [
                    'success' => true,
                    'ciceksepeti_id' => $response['product_id'],
                    'message' => 'Ürün başarıyla yüklendi',
                    'data' => $response
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Ürün yüklenemedi: ' . ($response['error'] ?? 'Bilinmeyen hata')
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Ürün yükleme hatası: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update product on Çiçek Sepeti
     * 
     * @param string $ciceksepeti_id Çiçek Sepeti product ID
     * @param array $product_data Updated product information
     * @return array Update response
     */
    public function updateProduct($ciceksepeti_id, $product_data) {
        try {
            $ciceksepeti_product = $this->formatProductForCiceksepeti($product_data);
            
            $response = $this->makeRequest('PUT', 'products/' . $ciceksepeti_id, $ciceksepeti_product);
            
            if ($response && isset($response['success']) && $response['success']) {
                return [
                    'success' => true,
                    'message' => 'Ürün başarıyla güncellendi',
                    'data' => $response
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Ürün güncellenemedi: ' . ($response['error'] ?? 'Bilinmeyen hata')
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Ürün güncelleme hatası: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update product stock
     * 
     * @param string $ciceksepeti_id Çiçek Sepeti product ID
     * @param int $stock Stock quantity
     * @return array Update response
     */
    public function updateStock($ciceksepeti_id, $stock) {
        try {
            $stock_data = [
                'product_id' => $ciceksepeti_id,
                'stock_quantity' => (int)$stock,
                'availability' => $stock > 0 ? 'in_stock' : 'out_of_stock'
            ];
            
            $response = $this->makeRequest('PUT', 'products/' . $ciceksepeti_id . '/stock', $stock_data);
            
            if ($response && isset($response['success']) && $response['success']) {
                return [
                    'success' => true,
                    'message' => 'Stok başarıyla güncellendi',
                    'data' => $response
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Stok güncellenemedi: ' . ($response['error'] ?? 'Bilinmeyen hata')
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Stok güncelleme hatası: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update product price
     * 
     * @param string $ciceksepeti_id Çiçek Sepeti product ID
     * @param float $price Product price
     * @return array Update response
     */
    public function updatePrice($ciceksepeti_id, $price) {
        try {
            $price_data = [
                'product_id' => $ciceksepeti_id,
                'price' => (float)$price,
                'currency' => 'TRY'
            ];
            
            $response = $this->makeRequest('PUT', 'products/' . $ciceksepeti_id . '/price', $price_data);
            
            if ($response && isset($response['success']) && $response['success']) {
                return [
                    'success' => true,
                    'message' => 'Fiyat başarıyla güncellendi',
                    'data' => $response
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Fiyat güncellenemedi: ' . ($response['error'] ?? 'Bilinmeyen hata')
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Fiyat güncelleme hatası: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get orders from Çiçek Sepeti
     * 
     * @param array $filters Order filters
     * @return array Orders list
     */
    public function getOrders($filters = []) {
        try {
            $params = [];
            
            if (isset($filters['start_date'])) {
                $params['start_date'] = $filters['start_date'];
            }
            
            if (isset($filters['end_date'])) {
                $params['end_date'] = $filters['end_date'];
            }
            
            if (isset($filters['status'])) {
                $params['status'] = $filters['status'];
            }
            
            if (isset($filters['delivery_date'])) {
                $params['delivery_date'] = $filters['delivery_date'];
            }
            
            $query_string = !empty($params) ? '?' . http_build_query($params) : '';
            
            $response = $this->makeRequest('GET', 'orders' . $query_string);
            
            if ($response && isset($response['orders'])) {
                return [
                    'success' => true,
                    'orders' => $response['orders'],
                    'total' => $response['total'] ?? count($response['orders'])
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Siparişler alınamadı'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Sipariş alma hatası: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get specific order details
     * 
     * @param string $order_id Çiçek Sepeti order ID
     * @return array Order details
     */
    public function getOrderDetails($order_id) {
        try {
            $response = $this->makeRequest('GET', 'orders/' . $order_id);
            
            if ($response && isset($response['order'])) {
                return [
                    'success' => true,
                    'order' => $response['order']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Sipariş detayları alınamadı'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Sipariş detay alma hatası: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update order status
     * 
     * @param string $order_id Çiçek Sepeti order ID
     * @param string $status New order status
     * @param array $delivery_info Delivery information
     * @return array Update response
     */
    public function updateOrderStatus($order_id, $status, $delivery_info = []) {
        try {
            $status_data = [
                'status' => $status
            ];
            
            if (isset($delivery_info['tracking_number'])) {
                $status_data['tracking_number'] = $delivery_info['tracking_number'];
            }
            
            if (isset($delivery_info['delivery_date'])) {
                $status_data['delivery_date'] = $delivery_info['delivery_date'];
            }
            
            if (isset($delivery_info['delivery_time'])) {
                $status_data['delivery_time'] = $delivery_info['delivery_time'];
            }
            
            $response = $this->makeRequest('PUT', 'orders/' . $order_id . '/status', $status_data);
            
            if ($response && isset($response['success']) && $response['success']) {
                return [
                    'success' => true,
                    'message' => 'Sipariş durumu güncellendi',
                    'data' => $response
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Sipariş durumu güncellenemedi: ' . ($response['error'] ?? 'Bilinmeyen hata')
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Sipariş durumu güncelleme hatası: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get delivery schedules
     * 
     * @param array $filters Date and location filters
     * @return array Available delivery slots
     */
    public function getDeliverySchedules($filters = []) {
        try {
            $params = [];
            
            if (isset($filters['date'])) {
                $params['date'] = $filters['date'];
            }
            
            if (isset($filters['city'])) {
                $params['city'] = $filters['city'];
            }
            
            if (isset($filters['district'])) {
                $params['district'] = $filters['district'];
            }
            
            $query_string = !empty($params) ? '?' . http_build_query($params) : '';
            
            $response = $this->makeRequest('GET', 'delivery/schedules' . $query_string);
            
            if ($response && isset($response['schedules'])) {
                return [
                    'success' => true,
                    'schedules' => $response['schedules']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Teslimat programları alınamadı'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Teslimat programı alma hatası: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete product from Çiçek Sepeti
     * 
     * @param string $ciceksepeti_id Çiçek Sepeti product ID
     * @return array Delete response
     */
    public function deleteProduct($ciceksepeti_id) {
        try {
            $response = $this->makeRequest('DELETE', 'products/' . $ciceksepeti_id);
            
            if ($response && isset($response['success']) && $response['success']) {
                return [
                    'success' => true,
                    'message' => 'Ürün başarıyla silindi'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Ürün silinemedi: ' . ($response['error'] ?? 'Bilinmeyen hata')
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Ürün silme hatası: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Format product data for Çiçek Sepeti API
     * 
     * @param array $product_data OpenCart product data
     * @return array Çiçek Sepeti formatted product data
     */
    private function formatProductForCiceksepeti($product_data) {
        $ciceksepeti_product = [
            'name' => $product_data['name'] ?? '',
            'description' => $product_data['description'] ?? '',
            'short_description' => $this->truncateText($product_data['description'] ?? '', 200),
            'price' => (float)($product_data['price'] ?? 0),
            'stock_quantity' => (int)($product_data['quantity'] ?? 0),
            'sku' => $product_data['sku'] ?? $product_data['model'] ?? '',
            'flower_type' => $product_data['flower_type'] ?? 'flower',
            'category_id' => $product_data['category_id'] ?? null,
            'brand' => $product_data['manufacturer'] ?? '',
            'weight' => (float)($product_data['weight'] ?? 0),
            'dimensions' => [
                'length' => (float)($product_data['length'] ?? 0),
                'width' => (float)($product_data['width'] ?? 0),
                'height' => (float)($product_data['height'] ?? 0)
            ],
            'images' => $product_data['images'] ?? [],
            'attributes' => $this->formatFlowerAttributes($product_data),
            'care_instructions' => $product_data['care_instructions'] ?? '',
            'occasion_tags' => $product_data['occasion_tags'] ?? [],
            'seasonal_availability' => $product_data['seasonal_availability'] ?? true,
            'delivery_options' => [
                'same_day' => $product_data['same_day_delivery'] ?? false,
                'scheduled' => $product_data['scheduled_delivery'] ?? true,
                'gift_wrapping' => $product_data['gift_wrapping'] ?? true
            ]
        ];
        
        // Add flower-specific fields
        if ($ciceksepeti_product['flower_type'] === 'flower') {
            $ciceksepeti_product['flower_color'] = $product_data['flower_color'] ?? '';
            $ciceksepeti_product['flower_count'] = (int)($product_data['flower_count'] ?? 0);
            $ciceksepeti_product['arrangement_type'] = $product_data['arrangement_type'] ?? 'bouquet';
        } elseif ($ciceksepeti_product['flower_type'] === 'plant') {
            $ciceksepeti_product['plant_type'] = $product_data['plant_type'] ?? '';
            $ciceksepeti_product['pot_included'] = $product_data['pot_included'] ?? true;
            $ciceksepeti_product['light_requirement'] = $product_data['light_requirement'] ?? 'medium';
            $ciceksepeti_product['water_requirement'] = $product_data['water_requirement'] ?? 'medium';
        }
        
        // Remove empty fields
        return array_filter($ciceksepeti_product, function($value) {
            return !empty($value) || $value === 0 || $value === false;
        });
    }
    
    /**
     * Format flower-specific attributes
     * 
     * @param array $product_data Product data
     * @return array Formatted attributes
     */
    private function formatFlowerAttributes($product_data) {
        $attributes = [];
        
        // Basic attributes
        if (isset($product_data['attributes'])) {
            $attributes = $product_data['attributes'];
        }
        
        // Flower-specific attributes
        $flower_attributes = [
            'Çiçek Türü' => $this->flower_types[$product_data['flower_type'] ?? 'flower'] ?? 'Çiçek',
            'Renk' => $product_data['flower_color'] ?? '',
            'Boyut' => $product_data['size'] ?? '',
            'Mevsim' => $product_data['season'] ?? '',
            'Koku' => $product_data['fragrance'] ?? ''
        ];
        
        // Filter out empty values and merge
        $flower_attributes = array_filter($flower_attributes);
        
        return array_merge($attributes, $flower_attributes);
    }
    
    /**
     * Truncate text to specified length
     * 
     * @param string $text Text to truncate
     * @param int $length Maximum length
     * @return string Truncated text
     */
    private function truncateText($text, $length = 200) {
        $text = strip_tags($text);
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length - 3) . '...';
    }
    
    /**
     * Make HTTP request to Çiçek Sepeti API
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return array|null API response
     */
    private function makeRequest($method, $endpoint, $data = []) {
        try {
            $url = $this->base_url . ltrim($endpoint, '/');
            
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->api_key,
                'X-Supplier-ID: ' . $this->supplier_id,
                'User-Agent: ' . $this->user_agent,
                'Accept: application/json'
            ];
            
            $curl = curl_init();
            
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);
            
            if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            }
            
            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            
            curl_close($curl);
            
            if ($error) {
                throw new Exception('cURL Error: ' . $error);
            }
            
            if ($this->debug) {
                error_log('Çiçek Sepeti API Request: ' . $method . ' ' . $url);
                error_log('Çiçek Sepeti API Response Code: ' . $http_code);
                error_log('Çiçek Sepeti API Response: ' . $response);
            }
            
            $decoded_response = json_decode($response, true);
            
            if ($http_code >= 400) {
                throw new Exception('HTTP Error ' . $http_code . ': ' . ($decoded_response['message'] ?? $response));
            }
            
            return $decoded_response;
            
        } catch (Exception $e) {
            if ($this->debug) {
                error_log('Çiçek Sepeti API Error: ' . $e->getMessage());
            }
            throw $e;
        }
    }
    
    /**
     * Validate API credentials format
     * 
     * @return array Validation result
     */
    public function validateCredentials() {
        $errors = [];
        
        if (empty($this->api_key)) {
            $errors[] = 'API Key gerekli';
        } elseif (strlen($this->api_key) < 20) {
            $errors[] = 'API Key çok kısa (minimum 20 karakter)';
        }
        
        if (empty($this->supplier_id)) {
            $errors[] = 'Supplier ID gerekli';
        } elseif (!is_numeric($this->supplier_id)) {
            $errors[] = 'Supplier ID sayısal olmalı';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Get flower type options
     * 
     * @return array Flower type options
     */
    public function getFlowerTypes() {
        return $this->flower_types;
    }
    
    /**
     * Get seasonal availability info
     * 
     * @param string $flower_type Flower type
     * @return array Seasonal information
     */
    public function getSeasonalInfo($flower_type = 'flower') {
        try {
            $response = $this->makeRequest('GET', 'seasonal-info?type=' . urlencode($flower_type));
            
            if ($response && isset($response['seasonal_info'])) {
                return [
                    'success' => true,
                    'seasonal_info' => $response['seasonal_info']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Mevsimsel bilgi alınamadı'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Mevsimsel bilgi alma hatası: ' . $e->getMessage()
            ];
        }
    }
}
?> 