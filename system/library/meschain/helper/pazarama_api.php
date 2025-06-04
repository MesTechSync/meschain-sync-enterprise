<?php
/**
 * MesChain-Sync Pazarama API Helper
 * 
 * @package     MesChain-Sync
 * @subpackage  Pazarama API Helper
 * @category    API Integration
 * @author      MesChain Development Team
 * @copyright   2024 MesChain-Sync
 * @license     Commercial License
 * @version     1.0.0
 * @since       1.0.0
 */

class PazaramaApiHelper {
    
    /**
     * API Configuration
     */
    private $api_key;
    private $secret_key;
    private $base_url = 'https://api.pazarama.com/v1/';
    private $debug = false;
    private $timeout = 30;
    private $user_agent = 'MesChain-Sync/1.0';
    
    /**
     * Constructor
     * 
     * @param string $api_key Pazarama API Key
     * @param string $secret_key Pazarama Secret Key
     * @param bool $debug Debug mode
     */
    public function __construct($api_key, $secret_key, $debug = false) {
        $this->api_key = $api_key;
        $this->secret_key = $secret_key;
        $this->debug = $debug;
    }
    
    /**
     * Test API connection
     * 
     * @return array Response with success/error status
     */
    public function testConnection() {
        try {
            $response = $this->makeRequest('GET', 'auth/test');
            
            if ($response && isset($response['status']) && $response['status'] === 'success') {
                return [
                    'success' => true,
                    'message' => 'Pazarama API bağlantısı başarılı',
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
     * Get product categories
     * 
     * @return array Categories list
     */
    public function getCategories() {
        try {
            $response = $this->makeRequest('GET', 'categories');
            
            if ($response && isset($response['data'])) {
                return [
                    'success' => true,
                    'categories' => $response['data']
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
     * Upload product to Pazarama
     * 
     * @param array $product_data Product information
     * @return array Upload response
     */
    public function uploadProduct($product_data) {
        try {
            $pazarama_product = $this->formatProductForPazarama($product_data);
            
            $response = $this->makeRequest('POST', 'products', $pazarama_product);
            
            if ($response && isset($response['product_id'])) {
                return [
                    'success' => true,
                    'pazarama_id' => $response['product_id'],
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
     * Update product on Pazarama
     * 
     * @param string $pazarama_id Pazarama product ID
     * @param array $product_data Updated product information
     * @return array Update response
     */
    public function updateProduct($pazarama_id, $product_data) {
        try {
            $pazarama_product = $this->formatProductForPazarama($product_data);
            
            $response = $this->makeRequest('PUT', 'products/' . $pazarama_id, $pazarama_product);
            
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
     * @param string $pazarama_id Pazarama product ID
     * @param int $stock Stock quantity
     * @return array Update response
     */
    public function updateStock($pazarama_id, $stock) {
        try {
            $stock_data = [
                'product_id' => $pazarama_id,
                'stock' => (int)$stock
            ];
            
            $response = $this->makeRequest('PUT', 'products/' . $pazarama_id . '/stock', $stock_data);
            
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
     * @param string $pazarama_id Pazarama product ID
     * @param float $price Product price
     * @return array Update response
     */
    public function updatePrice($pazarama_id, $price) {
        try {
            $price_data = [
                'product_id' => $pazarama_id,
                'price' => (float)$price
            ];
            
            $response = $this->makeRequest('PUT', 'products/' . $pazarama_id . '/price', $price_data);
            
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
     * Get orders from Pazarama
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
     * @param string $order_id Pazarama order ID
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
     * @param string $order_id Pazarama order ID
     * @param string $status New order status
     * @param string $tracking_number Tracking number (optional)
     * @return array Update response
     */
    public function updateOrderStatus($order_id, $status, $tracking_number = null) {
        try {
            $status_data = [
                'status' => $status
            ];
            
            if ($tracking_number) {
                $status_data['tracking_number'] = $tracking_number;
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
     * Delete product from Pazarama
     * 
     * @param string $pazarama_id Pazarama product ID
     * @return array Delete response
     */
    public function deleteProduct($pazarama_id) {
        try {
            $response = $this->makeRequest('DELETE', 'products/' . $pazarama_id);
            
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
     * Format product data for Pazarama API
     * 
     * @param array $product_data OpenCart product data
     * @return array Pazarama formatted product data
     */
    private function formatProductForPazarama($product_data) {
        $pazarama_product = [
            'title' => $product_data['name'] ?? '',
            'description' => $product_data['description'] ?? '',
            'price' => (float)($product_data['price'] ?? 0),
            'stock' => (int)($product_data['quantity'] ?? 0),
            'sku' => $product_data['sku'] ?? $product_data['model'] ?? '',
            'category_id' => $product_data['category_id'] ?? null,
            'brand' => $product_data['manufacturer'] ?? '',
            'weight' => (float)($product_data['weight'] ?? 0),
            'dimensions' => [
                'length' => (float)($product_data['length'] ?? 0),
                'width' => (float)($product_data['width'] ?? 0),
                'height' => (float)($product_data['height'] ?? 0)
            ],
            'images' => $product_data['images'] ?? [],
            'attributes' => $product_data['attributes'] ?? [],
            'variants' => $product_data['variants'] ?? []
        ];
        
        // Remove empty fields
        return array_filter($pazarama_product, function($value) {
            return !empty($value) || $value === 0;
        });
    }
    
    /**
     * Generate authentication signature
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return string Authentication signature
     */
    private function generateSignature($method, $endpoint, $data = []) {
        $timestamp = time();
        $nonce = uniqid();
        
        $string_to_sign = $method . "\n" . 
                         $endpoint . "\n" . 
                         $timestamp . "\n" . 
                         $nonce . "\n" . 
                         (!empty($data) ? json_encode($data) : '');
        
        return base64_encode(hash_hmac('sha256', $string_to_sign, $this->secret_key, true));
    }
    
    /**
     * Make HTTP request to Pazarama API
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return array|null API response
     */
    private function makeRequest($method, $endpoint, $data = []) {
        try {
            $url = $this->base_url . ltrim($endpoint, '/');
            
            // Generate authentication
            $timestamp = time();
            $nonce = uniqid();
            $signature = $this->generateSignature($method, $endpoint, $data);
            
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->api_key,
                'X-Timestamp: ' . $timestamp,
                'X-Nonce: ' . $nonce,
                'X-Signature: ' . $signature,
                'User-Agent: ' . $this->user_agent
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
                error_log('Pazarama API Request: ' . $method . ' ' . $url);
                error_log('Pazarama API Response Code: ' . $http_code);
                error_log('Pazarama API Response: ' . $response);
            }
            
            $decoded_response = json_decode($response, true);
            
            if ($http_code >= 400) {
                throw new Exception('HTTP Error ' . $http_code . ': ' . ($decoded_response['message'] ?? $response));
            }
            
            return $decoded_response;
            
        } catch (Exception $e) {
            if ($this->debug) {
                error_log('Pazarama API Error: ' . $e->getMessage());
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
        
        if (empty($this->secret_key)) {
            $errors[] = 'Secret Key gerekli';
        } elseif (strlen($this->secret_key) < 32) {
            $errors[] = 'Secret Key çok kısa (minimum 32 karakter)';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Get API rate limit status
     * 
     * @return array Rate limit information
     */
    public function getRateLimitStatus() {
        try {
            $response = $this->makeRequest('GET', 'rate-limit');
            
            if ($response) {
                return [
                    'success' => true,
                    'limit' => $response['limit'] ?? 1000,
                    'remaining' => $response['remaining'] ?? 1000,
                    'reset_time' => $response['reset_time'] ?? time() + 3600
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Rate limit bilgisi alınamadı'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Rate limit hatası: ' . $e->getMessage()
            ];
        }
    }
}
?> 