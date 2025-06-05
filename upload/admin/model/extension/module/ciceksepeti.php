<?php
/**
 * MesChain-Sync Çiçek Sepeti Model
 * 
 * @package     MesChain-Sync
 * @subpackage  Çiçek Sepeti Model
 * @category    Marketplace Integration
 * @author      MesChain Development Team
 * @copyright   2024 MesChain-Sync
 * @license     Commercial License
 * @version     1.0.0
 * @since       1.0.0
 */

class ModelExtensionModuleCiceksepeti extends Model {
    
    private $api_url = 'https://api.ciceksepeti.com/v1/';
    private $api_key;
    private $api_secret;
    
    public function __construct($registry) {
        parent::__construct($registry);
        
        $this->api_key = $this->config->get('module_ciceksepeti_api_key');
        $this->api_secret = $this->config->get('module_ciceksepeti_api_secret');
    }
    
    /**
     * Get API credentials status
     *
     * @return bool Credentials are set
     */
    public function hasCredentials() {
        return !empty($this->api_key) && !empty($this->api_secret);
    }
    
    /**
     * Test API connection
     *
     * @return array Test result
     */
    public function testConnection() {
        if (!$this->hasCredentials()) {
            return [
                'success' => false,
                'error' => 'API credentials not configured'
            ];
        }
        
        try {
            $response = $this->makeApiRequest('GET', 'account/info');
            
            if ($response && isset($response['success']) && $response['success']) {
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                    'data' => $response['data'] ?? []
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['message'] ?? 'Unknown error'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get categories
     *
     * @return array Categories
     */
    public function getCategories() {
        try {
            $response = $this->makeApiRequest('GET', 'categories');
            
            if ($response && isset($response['data'])) {
                return $response['data'];
            }
            
            return [];
            
        } catch (Exception $e) {
            $this->log('error', 'Failed to get categories', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Get products
     *
     * @param array $filters Filter options
     * @return array Products
     */
    public function getProducts($filters = []) {
        try {
            $params = [];
            
            if (isset($filters['page'])) {
                $params['page'] = (int)$filters['page'];
            }
            
            if (isset($filters['limit'])) {
                $params['limit'] = (int)$filters['limit'];
            }
            
            if (isset($filters['category_id'])) {
                $params['category_id'] = (int)$filters['category_id'];
            }
            
            $response = $this->makeApiRequest('GET', 'products', $params);
            
            if ($response && isset($response['data'])) {
                return $response['data'];
            }
            
            return [];
            
        } catch (Exception $e) {
            $this->log('error', 'Failed to get products', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Create product
     *
     * @param array $product_data Product data
     * @return array Result
     */
    public function createProduct($product_data) {
        try {
            $response = $this->makeApiRequest('POST', 'products', $product_data);
            
            if ($response && isset($response['success']) && $response['success']) {
                $this->log('info', 'Product created successfully', [
                    'product_id' => $response['data']['id'] ?? null,
                    'sku' => $product_data['sku'] ?? null
                ]);
                
                return [
                    'success' => true,
                    'product_id' => $response['data']['id'] ?? null,
                    'data' => $response['data'] ?? []
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['message'] ?? 'Product creation failed'
                ];
            }
            
        } catch (Exception $e) {
            $this->log('error', 'Product creation failed', [
                'error' => $e->getMessage(),
                'product_data' => $product_data
            ]);
            
            return [
                'success' => false,
                'error' => 'Product creation failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update product
     *
     * @param string $product_id Ciceksepeti product ID
     * @param array $product_data Product data
     * @return array Result
     */
    public function updateProduct($product_id, $product_data) {
        try {
            $response = $this->makeApiRequest('PUT', 'products/' . $product_id, $product_data);
            
            if ($response && isset($response['success']) && $response['success']) {
                $this->log('info', 'Product updated successfully', [
                    'product_id' => $product_id,
                    'sku' => $product_data['sku'] ?? null
                ]);
                
                return [
                    'success' => true,
                    'data' => $response['data'] ?? []
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['message'] ?? 'Product update failed'
                ];
            }
            
        } catch (Exception $e) {
            $this->log('error', 'Product update failed', [
                'error' => $e->getMessage(),
                'product_id' => $product_id,
                'product_data' => $product_data
            ]);
            
            return [
                'success' => false,
                'error' => 'Product update failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update product stock
     *
     * @param string $product_id Ciceksepeti product ID
     * @param int $stock Stock quantity
     * @return array Result
     */
    public function updateStock($product_id, $stock) {
        try {
            $data = [
                'stock' => (int)$stock
            ];
            
            $response = $this->makeApiRequest('PUT', 'products/' . $product_id . '/stock', $data);
            
            if ($response && isset($response['success']) && $response['success']) {
                $this->log('info', 'Stock updated successfully', [
                    'product_id' => $product_id,
                    'stock' => $stock
                ]);
                
                return [
                    'success' => true,
                    'data' => $response['data'] ?? []
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['message'] ?? 'Stock update failed'
                ];
            }
            
        } catch (Exception $e) {
            $this->log('error', 'Stock update failed', [
                'error' => $e->getMessage(),
                'product_id' => $product_id,
                'stock' => $stock
            ]);
            
            return [
                'success' => false,
                'error' => 'Stock update failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update product price
     *
     * @param string $product_id Ciceksepeti product ID
     * @param float $price Price
     * @return array Result
     */
    public function updatePrice($product_id, $price) {
        try {
            $data = [
                'price' => (float)$price
            ];
            
            $response = $this->makeApiRequest('PUT', 'products/' . $product_id . '/price', $data);
            
            if ($response && isset($response['success']) && $response['success']) {
                $this->log('info', 'Price updated successfully', [
                    'product_id' => $product_id,
                    'price' => $price
                ]);
                
                return [
                    'success' => true,
                    'data' => $response['data'] ?? []
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['message'] ?? 'Price update failed'
                ];
            }
            
        } catch (Exception $e) {
            $this->log('error', 'Price update failed', [
                'error' => $e->getMessage(),
                'product_id' => $product_id,
                'price' => $price
            ]);
            
            return [
                'success' => false,
                'error' => 'Price update failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get orders
     *
     * @param array $filters Filter options
     * @return array Orders
     */
    public function getOrders($filters = []) {
        try {
            $params = [];
            
            if (isset($filters['status'])) {
                $params['status'] = $filters['status'];
            }
            
            if (isset($filters['date_from'])) {
                $params['date_from'] = $filters['date_from'];
            }
            
            if (isset($filters['date_to'])) {
                $params['date_to'] = $filters['date_to'];
            }
            
            if (isset($filters['page'])) {
                $params['page'] = (int)$filters['page'];
            }
            
            if (isset($filters['limit'])) {
                $params['limit'] = (int)$filters['limit'];
            }
            
            $response = $this->makeApiRequest('GET', 'orders', $params);
            
            if ($response && isset($response['data'])) {
                return $response['data'];
            }
            
            return [];
            
        } catch (Exception $e) {
            $this->log('error', 'Failed to get orders', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Update order status
     *
     * @param string $order_id Ciceksepeti order ID
     * @param string $status New status
     * @param array $tracking_info Tracking information
     * @return array Result
     */
    public function updateOrderStatus($order_id, $status, $tracking_info = []) {
        try {
            $data = [
                'status' => $status
            ];
            
            if (!empty($tracking_info)) {
                $data['tracking'] = $tracking_info;
            }
            
            $response = $this->makeApiRequest('PUT', 'orders/' . $order_id . '/status', $data);
            
            if ($response && isset($response['success']) && $response['success']) {
                $this->log('info', 'Order status updated successfully', [
                    'order_id' => $order_id,
                    'status' => $status
                ]);
                
                return [
                    'success' => true,
                    'data' => $response['data'] ?? []
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['message'] ?? 'Order status update failed'
                ];
            }
            
        } catch (Exception $e) {
            $this->log('error', 'Order status update failed', [
                'error' => $e->getMessage(),
                'order_id' => $order_id,
                'status' => $status
            ]);
            
            return [
                'success' => false,
                'error' => 'Order status update failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Make API request
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return array|null Response data
     */
    private function makeApiRequest($method, $endpoint, $data = []) {
        $url = $this->api_url . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->generateAuthToken(),
            'X-API-Key: ' . $this->api_key
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
                
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
                
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
                
            case 'GET':
            default:
                if (!empty($data)) {
                    $url .= '?' . http_build_query($data);
                    curl_setopt($ch, CURLOPT_URL, $url);
                }
                break;
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        if ($http_code >= 400) {
            throw new Exception('HTTP Error: ' . $http_code . ' - ' . $response);
        }
        
        $decoded_response = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . json_last_error_msg());
        }
        
        return $decoded_response;
    }
    
    /**
     * Generate authentication token
     *
     * @return string Auth token
     */
    private function generateAuthToken() {
        $timestamp = time();
        $nonce = uniqid();
        
        $signature = hash_hmac('sha256', $this->api_key . $timestamp . $nonce, $this->api_secret);
        
        return base64_encode($this->api_key . ':' . $timestamp . ':' . $nonce . ':' . $signature);
    }
    
    /**
     * Log activity
     *
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Additional context
     * @return void
     */
    private function log($level, $message, $context = []) {
        $this->load->model('extension/module/base_marketplace');
        $this->model_extension_module_base_marketplace->log($level, $message, $context, 'ciceksepeti');
    }

    /**
     * Install Çiçek Sepeti tables
     * 
     * @return void
     */
    public function install() {
        try {
            // Çiçek Sepeti Products Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ciceksepeti_products` (
                    `ciceksepeti_product_id` int(11) NOT NULL AUTO_INCREMENT,
                    `product_id` int(11) NOT NULL,
                    `ciceksepeti_id` varchar(255) NOT NULL,
                    `status` tinyint(1) NOT NULL DEFAULT '1',
                    `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
                    `stock_quantity` int(11) NOT NULL DEFAULT '0',
                    `category_id` varchar(100) DEFAULT NULL,
                    `flower_type` enum('flower','plant','accessory','special_occasion') DEFAULT 'flower',
                    `last_sync` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`ciceksepeti_product_id`),
                    UNIQUE KEY `product_id` (`product_id`),
                    KEY `ciceksepeti_id` (`ciceksepeti_id`),
                    KEY `status` (`status`),
                    KEY `flower_type` (`flower_type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
            ");

            // Çiçek Sepeti Orders Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ciceksepeti_orders` (
                    `ciceksepeti_order_id` int(11) NOT NULL AUTO_INCREMENT,
                    `order_id` int(11) DEFAULT NULL,
                    `ciceksepeti_order_number` varchar(255) NOT NULL,
                    `ciceksepeti_status` varchar(100) NOT NULL,
                    `customer_name` varchar(255) NOT NULL,
                    `customer_email` varchar(255) DEFAULT NULL,
                    `customer_phone` varchar(50) DEFAULT NULL,
                    `delivery_address` text DEFAULT NULL,
                    `delivery_date` date DEFAULT NULL,
                    `delivery_time` varchar(50) DEFAULT NULL,
                    `gift_message` text DEFAULT NULL,
                    `total_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
                    `currency` varchar(3) NOT NULL DEFAULT 'TRY',
                    `order_date` datetime NOT NULL,
                    `sync_status` enum('pending','synced','failed') NOT NULL DEFAULT 'pending',
                    `sync_date` datetime DEFAULT NULL,
                    `notes` text DEFAULT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`ciceksepeti_order_id`),
                    UNIQUE KEY `ciceksepeti_order_number` (`ciceksepeti_order_number`),
                    KEY `order_id` (`order_id`),
                    KEY `sync_status` (`sync_status`),
                    KEY `delivery_date` (`delivery_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
            ");

            // Çiçek Sepeti Categories Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ciceksepeti_categories` (
                    `ciceksepeti_category_id` int(11) NOT NULL AUTO_INCREMENT,
                    `category_id` int(11) NOT NULL,
                    `ciceksepeti_id` varchar(100) NOT NULL,
                    `category_name` varchar(255) NOT NULL,
                    `flower_type` enum('flower','plant','accessory','special_occasion') NOT NULL,
                    `status` tinyint(1) NOT NULL DEFAULT '1',
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`ciceksepeti_category_id`),
                    UNIQUE KEY `category_mapping` (`category_id`, `flower_type`),
                    KEY `ciceksepeti_id` (`ciceksepeti_id`),
                    KEY `flower_type` (`flower_type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
            ");

            // Çiçek Sepeti Logs Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ciceksepeti_logs` (
                    `log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `type` enum('info','warning','error','success') NOT NULL DEFAULT 'info',
                    `action` varchar(100) NOT NULL,
                    `message` text NOT NULL,
                    `data` longtext DEFAULT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`log_id`),
                    KEY `type` (`type`),
                    KEY `action` (`action`),
                    KEY `created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
            ");

            $this->log('info', 'install', 'Çiçek Sepeti tables created successfully');
        } catch (Exception $e) {
            $this->log('error', 'install', 'Failed to create Çiçek Sepeti tables: ' . $e->getMessage());
        }
    }

    /**
     * Uninstall Çiçek Sepeti tables
     * 
     * @return void
     */
    public function uninstall() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ciceksepeti_products`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ciceksepeti_orders`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ciceksepeti_categories`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ciceksepeti_logs`");
            
            $this->log('info', 'uninstall', 'Çiçek Sepeti tables dropped successfully');
        } catch (Exception $e) {
            $this->log('error', 'uninstall', 'Failed to drop Çiçek Sepeti tables: ' . $e->getMessage());
        }
    }

    /**
     * Get product by OpenCart product ID
     * 
     * @param int $product_id OpenCart product ID
     * @return array|null Product data or null if not found
     */
    public function getProduct($product_id) {
        try {
            $query = $this->db->query("
                SELECT * FROM `" . DB_PREFIX . "ciceksepeti_products` 
                WHERE `product_id` = '" . (int)$product_id . "'
            ");
            
            return $query->num_rows ? $query->row : null;
        } catch (Exception $e) {
            $this->log('error', 'getProduct', 'Failed to get product: ' . $e->getMessage(), ['product_id' => $product_id]);
            return null;
        }
    }

    /**
     * Get product by Çiçek Sepeti ID
     * 
     * @param string $ciceksepeti_id Çiçek Sepeti product ID
     * @return array|null Product data or null if not found
     */
    public function getProductByCiceksepetiId($ciceksepeti_id) {
        try {
            $query = $this->db->query("
                SELECT * FROM `" . DB_PREFIX . "ciceksepeti_products` 
                WHERE `ciceksepeti_id` = '" . $this->db->escape($ciceksepeti_id) . "'
            ");
            
            return $query->num_rows ? $query->row : null;
        } catch (Exception $e) {
            $this->log('error', 'getProductByCiceksepetiId', 'Failed to get product by Çiçek Sepeti ID: ' . $e->getMessage(), ['ciceksepeti_id' => $ciceksepeti_id]);
            return null;
        }
    }

    /**
     * Add or update product
     * 
     * @param array $data Product data
     * @return bool Success status
     */
    public function addProduct($data) {
        try {
            if (!isset($data['product_id']) || !isset($data['ciceksepeti_id'])) {
                throw new Exception('Required fields missing: product_id, ciceksepeti_id');
            }

            $existing = $this->getProduct($data['product_id']);
            
            if ($existing) {
                return $this->updateProduct($data);
            }

            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "ciceksepeti_products` SET
                `product_id` = '" . (int)$data['product_id'] . "',
                `ciceksepeti_id` = '" . $this->db->escape($data['ciceksepeti_id']) . "',
                `status` = '" . (int)($data['status'] ?? 1) . "',
                `price` = '" . (float)($data['price'] ?? 0) . "',
                `stock_quantity` = '" . (int)($data['stock_quantity'] ?? 0) . "',
                `category_id` = '" . $this->db->escape($data['category_id'] ?? '') . "',
                `flower_type` = '" . $this->db->escape($data['flower_type'] ?? 'flower') . "',
                `last_sync` = NOW()
            ");

            $this->log('success', 'addProduct', 'Product added successfully', $data);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'addProduct', 'Failed to add product: ' . $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Update product
     * 
     * @param array $data Product data
     * @return bool Success status
     */
    public function updateProduct($data) {
        try {
            if (!isset($data['product_id'])) {
                throw new Exception('Product ID is required');
            }

            $sql = "UPDATE `" . DB_PREFIX . "ciceksepeti_products` SET ";
            $updates = [];

            if (isset($data['ciceksepeti_id'])) {
                $updates[] = "`ciceksepeti_id` = '" . $this->db->escape($data['ciceksepeti_id']) . "'";
            }
            if (isset($data['status'])) {
                $updates[] = "`status` = '" . (int)$data['status'] . "'";
            }
            if (isset($data['price'])) {
                $updates[] = "`price` = '" . (float)$data['price'] . "'";
            }
            if (isset($data['stock_quantity'])) {
                $updates[] = "`stock_quantity` = '" . (int)$data['stock_quantity'] . "'";
            }
            if (isset($data['category_id'])) {
                $updates[] = "`category_id` = '" . $this->db->escape($data['category_id']) . "'";
            }
            if (isset($data['flower_type'])) {
                $updates[] = "`flower_type` = '" . $this->db->escape($data['flower_type']) . "'";
            }
            
            $updates[] = "`last_sync` = NOW()";

            if (empty($updates)) {
                throw new Exception('No data to update');
            }

            $sql .= implode(', ', $updates);
            $sql .= " WHERE `product_id` = '" . (int)$data['product_id'] . "'";

            $this->db->query($sql);

            $this->log('success', 'updateProduct', 'Product updated successfully', $data);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'updateProduct', 'Failed to update product: ' . $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Delete product
     * 
     * @param int $product_id OpenCart product ID
     * @return bool Success status
     */
    public function deleteProduct($product_id) {
        try {
            $this->db->query("
                DELETE FROM `" . DB_PREFIX . "ciceksepeti_products` 
                WHERE `product_id` = '" . (int)$product_id . "'
            ");

            $this->log('success', 'deleteProduct', 'Product deleted successfully', ['product_id' => $product_id]);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'deleteProduct', 'Failed to delete product: ' . $e->getMessage(), ['product_id' => $product_id]);
            return false;
        }
    }

    /**
     * Get products by flower type
     * 
     * @param string $flower_type Flower type (flower, plant, accessory, special_occasion)
     * @param array $data Query parameters
     * @return array Products list
     */
    public function getProductsByFlowerType($flower_type, $data = []) {
        try {
            $sql = "SELECT cp.*, p.name, p.model, p.sku, p.price as opencart_price, p.quantity as opencart_quantity 
                    FROM `" . DB_PREFIX . "ciceksepeti_products` cp
                    LEFT JOIN `" . DB_PREFIX . "product_description` p ON (cp.product_id = p.product_id)
                    WHERE p.language_id = '" . (int)$this->config->get('config_language_id') . "'
                    AND cp.flower_type = '" . $this->db->escape($flower_type) . "'";

            if (isset($data['filter_status'])) {
                $sql .= " AND cp.status = '" . (int)$data['filter_status'] . "'";
            }

            $sql .= " ORDER BY cp.last_sync DESC";

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $query = $this->db->query($sql);
            return $query->rows;
        } catch (Exception $e) {
            $this->log('error', 'getProductsByFlowerType', 'Failed to get products by flower type: ' . $e->getMessage(), ['flower_type' => $flower_type]);
            return [];
        }
    }

    /**
     * Get order by Çiçek Sepeti order number
     * 
     * @param string $ciceksepeti_order_number Çiçek Sepeti order number
     * @return array|null Order data or null if not found
     */
    public function getOrderByCiceksepetiNumber($ciceksepeti_order_number) {
        try {
            $query = $this->db->query("
                SELECT * FROM `" . DB_PREFIX . "ciceksepeti_orders` 
                WHERE `ciceksepeti_order_number` = '" . $this->db->escape($ciceksepeti_order_number) . "'
            ");
            
            return $query->num_rows ? $query->row : null;
        } catch (Exception $e) {
            $this->log('error', 'getOrderByCiceksepetiNumber', 'Failed to get order: ' . $e->getMessage(), ['ciceksepeti_order_number' => $ciceksepeti_order_number]);
            return null;
        }
    }

    /**
     * Add order
     * 
     * @param array $data Order data
     * @return bool Success status
     */
    public function addOrder($data) {
        try {
            if (!isset($data['ciceksepeti_order_number'])) {
                throw new Exception('Çiçek Sepeti order number is required');
            }

            $existing = $this->getOrderByCiceksepetiNumber($data['ciceksepeti_order_number']);
            if ($existing) {
                return $this->updateOrder($data);
            }

            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "ciceksepeti_orders` SET
                `order_id` = " . (isset($data['order_id']) ? "'" . (int)$data['order_id'] . "'" : "NULL") . ",
                `ciceksepeti_order_number` = '" . $this->db->escape($data['ciceksepeti_order_number']) . "',
                `ciceksepeti_status` = '" . $this->db->escape($data['ciceksepeti_status'] ?? 'unknown') . "',
                `customer_name` = '" . $this->db->escape($data['customer_name'] ?? '') . "',
                `customer_email` = '" . $this->db->escape($data['customer_email'] ?? '') . "',
                `customer_phone` = '" . $this->db->escape($data['customer_phone'] ?? '') . "',
                `delivery_address` = '" . $this->db->escape($data['delivery_address'] ?? '') . "',
                `delivery_date` = " . (isset($data['delivery_date']) ? "'" . $this->db->escape($data['delivery_date']) . "'" : "NULL") . ",
                `delivery_time` = '" . $this->db->escape($data['delivery_time'] ?? '') . "',
                `gift_message` = '" . $this->db->escape($data['gift_message'] ?? '') . "',
                `total_amount` = '" . (float)($data['total_amount'] ?? 0) . "',
                `currency` = '" . $this->db->escape($data['currency'] ?? 'TRY') . "',
                `order_date` = '" . $this->db->escape($data['order_date'] ?? date('Y-m-d H:i:s')) . "',
                `sync_status` = '" . $this->db->escape($data['sync_status'] ?? 'pending') . "',
                `notes` = '" . $this->db->escape($data['notes'] ?? '') . "'
            ");

            $this->log('success', 'addOrder', 'Order added successfully', $data);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'addOrder', 'Failed to add order: ' . $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Update order
     * 
     * @param array $data Order data
     * @return bool Success status
     */
    public function updateOrder($data) {
        try {
            if (!isset($data['ciceksepeti_order_number'])) {
                throw new Exception('Çiçek Sepeti order number is required');
            }

            $sql = "UPDATE `" . DB_PREFIX . "ciceksepeti_orders` SET ";
            $updates = [];

            if (isset($data['order_id'])) {
                $updates[] = "`order_id` = '" . (int)$data['order_id'] . "'";
            }
            if (isset($data['ciceksepeti_status'])) {
                $updates[] = "`ciceksepeti_status` = '" . $this->db->escape($data['ciceksepeti_status']) . "'";
            }
            if (isset($data['sync_status'])) {
                $updates[] = "`sync_status` = '" . $this->db->escape($data['sync_status']) . "'";
            }
            if (isset($data['sync_date'])) {
                $updates[] = "`sync_date` = '" . $this->db->escape($data['sync_date']) . "'";
            }
            if (isset($data['delivery_date'])) {
                $updates[] = "`delivery_date` = '" . $this->db->escape($data['delivery_date']) . "'";
            }
            if (isset($data['notes'])) {
                $updates[] = "`notes` = '" . $this->db->escape($data['notes']) . "'";
            }

            if (empty($updates)) {
                throw new Exception('No data to update');
            }

            $sql .= implode(', ', $updates);
            $sql .= " WHERE `ciceksepeti_order_number` = '" . $this->db->escape($data['ciceksepeti_order_number']) . "'";

            $this->db->query($sql);

            $this->log('success', 'updateOrder', 'Order updated successfully', $data);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'updateOrder', 'Failed to update order: ' . $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Get category mapping
     * 
     * @param int $category_id OpenCart category ID
     * @param string $flower_type Flower type
     * @return array|null Category mapping or null if not found
     */
    public function getCategoryMapping($category_id, $flower_type) {
        try {
            $query = $this->db->query("
                SELECT * FROM `" . DB_PREFIX . "ciceksepeti_categories` 
                WHERE `category_id` = '" . (int)$category_id . "' 
                AND `flower_type` = '" . $this->db->escape($flower_type) . "'
            ");
            
            return $query->num_rows ? $query->row : null;
        } catch (Exception $e) {
            $this->log('error', 'getCategoryMapping', 'Failed to get category mapping: ' . $e->getMessage(), ['category_id' => $category_id, 'flower_type' => $flower_type]);
            return null;
        }
    }

    /**
     * Add category mapping
     * 
     * @param array $data Category mapping data
     * @return bool Success status
     */
    public function addCategoryMapping($data) {
        try {
            if (!isset($data['category_id']) || !isset($data['flower_type']) || !isset($data['ciceksepeti_id'])) {
                throw new Exception('Required fields missing: category_id, flower_type, ciceksepeti_id');
            }

            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "ciceksepeti_categories` SET
                `category_id` = '" . (int)$data['category_id'] . "',
                `ciceksepeti_id` = '" . $this->db->escape($data['ciceksepeti_id']) . "',
                `category_name` = '" . $this->db->escape($data['category_name'] ?? '') . "',
                `flower_type` = '" . $this->db->escape($data['flower_type']) . "',
                `status` = '" . (int)($data['status'] ?? 1) . "'
                ON DUPLICATE KEY UPDATE
                `ciceksepeti_id` = VALUES(`ciceksepeti_id`),
                `category_name` = VALUES(`category_name`),
                `status` = VALUES(`status`)
            ");

            $this->log('success', 'addCategoryMapping', 'Category mapping added successfully', $data);
            return true;
        } catch (Exception $e) {
            $this->log('error', 'addCategoryMapping', 'Failed to add category mapping: ' . $e->getMessage(), $data);
            return false;
        }
    }

    /**
     * Get flower type statistics
     * 
     * @return array Flower type statistics
     */
    public function getFlowerTypeStatistics() {
        try {
            $stats = [];

            $flower_types = ['flower', 'plant', 'accessory', 'special_occasion'];

            foreach ($flower_types as $type) {
                $query = $this->db->query("
                    SELECT COUNT(*) as total 
                    FROM `" . DB_PREFIX . "ciceksepeti_products` 
                    WHERE `flower_type` = '" . $this->db->escape($type) . "' 
                    AND `status` = 1
                ");
                $stats[$type] = $query->row['total'];
            }

            return $stats;
        } catch (Exception $e) {
            $this->log('error', 'getFlowerTypeStatistics', 'Failed to get flower type statistics: ' . $e->getMessage());
            return [
                'flower' => 0,
                'plant' => 0,
                'accessory' => 0,
                'special_occasion' => 0
            ];
        }
    }

    /**
     * Get statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        try {
            $stats = [];

            // Total products
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ciceksepeti_products`");
            $stats['total_products'] = $query->row['total'];

            // Active products
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ciceksepeti_products` WHERE status = 1");
            $stats['active_products'] = $query->row['total'];

            // Total orders
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ciceksepeti_orders`");
            $stats['total_orders'] = $query->row['total'];

            // Pending orders
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ciceksepeti_orders` WHERE sync_status = 'pending'");
            $stats['pending_orders'] = $query->row['total'];

            // Orders with delivery dates
            $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "ciceksepeti_orders` WHERE delivery_date IS NOT NULL");
            $stats['scheduled_deliveries'] = $query->row['total'];

            // Last sync date
            $query = $this->db->query("SELECT MAX(last_sync) as last_sync FROM `" . DB_PREFIX . "ciceksepeti_products`");
            $stats['last_sync'] = $query->row['last_sync'];

            // Flower type statistics
            $stats['flower_types'] = $this->getFlowerTypeStatistics();

            return $stats;
        } catch (Exception $e) {
            $this->log('error', 'getStatistics', 'Failed to get statistics: ' . $e->getMessage());
            return [
                'total_products' => 0,
                'active_products' => 0,
                'total_orders' => 0,
                'pending_orders' => 0,
                'scheduled_deliveries' => 0,
                'last_sync' => null,
                'flower_types' => [
                    'flower' => 0,
                    'plant' => 0,
                    'accessory' => 0,
                    'special_occasion' => 0
                ]
            ];
        }
    }

    /**
     * Clear old logs
     * 
     * @param int $days Days to keep (default: 30)
     * @return bool Success status
     */
    public function clearOldLogs($days = 30) {
        try {
            $this->db->query("
                DELETE FROM `" . DB_PREFIX . "ciceksepeti_logs` 
                WHERE `created_at` < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            ");

            $this->log('info', 'clearOldLogs', "Cleared logs older than {$days} days");
            return true;
        } catch (Exception $e) {
            $this->log('error', 'clearOldLogs', 'Failed to clear old logs: ' . $e->getMessage());
            return false;
        }
    }
}
?> 