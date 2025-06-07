<?php
/**
 * EntegratorN11 - N11 Marketplace API Integration Class
 * MesChain-Sync v3.0 - OpenCart 3.0.4.0 Integration
 * Turkish E-commerce Platform Integration with N11 Pro Features
 * 
 * @author MesChain Development Team - MezBjen Backend Enhancement Specialist
 * @version 3.0.0
 * @copyright 2024 MesChain Technologies
 * @license Commercial License
 */

class EntegratorN11 {
    
    private $registry;
    private $config;
    private $log;
    private $cache;
    private $db;
    
    // N11 API Configuration
    private $api_key;
    private $api_secret;
    private $api_base_url = 'https://api.n11.com';
    private $soap_client;
    
    // Rate Limiting
    private $rate_limit = 100; // requests per minute
    private $rate_window = 60; // seconds
    private $request_count = 0;
    private $window_start;
    
    // Batch Processing
    private $batch_size = 50;
    private $max_retries = 3;
    
    /**
     * N11 API Endpoints
     */
    const ENDPOINTS = [
        'auth' => '/ws/auth.wsdl',
        'product' => '/ws/ProductService.wsdl',
        'order' => '/ws/OrderService.wsdl',
        'category' => '/ws/CategoryService.wsdl',
        'shipment' => '/ws/ShipmentService.wsdl',
        'city' => '/ws/CityService.wsdl'
    ];
    
    /**
     * N11 Product Status Mapping
     */
    const PRODUCT_STATUS = [
        'ACTIVE' => 1,
        'PASSIVE' => 0,
        'WAITING_FOR_APPROVAL' => 2,
        'REJECTED' => 3,
        'SOLD_OUT' => 4
    ];
    
    /**
     * N11 Order Status Mapping
     */
    const ORDER_STATUS = [
        'NEW' => 'new',
        'CONFIRMED' => 'confirmed',
        'PREPARING' => 'preparing',
        'SHIPPED' => 'shipped',
        'DELIVERED' => 'delivered',
        'CANCELLED' => 'cancelled',
        'RETURNED' => 'returned'
    ];
    
    /**
     * Constructor
     * 
     * @param object $registry OpenCart registry object
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $registry->get('config');
        $this->db = $registry->get('db');
        $this->cache = $registry->get('cache');
        $this->log = new Log('n11_integrator.log');
        
        // Initialize API credentials
        $this->api_key = $this->config->get('module_n11_api_key');
        $this->api_secret = $this->config->get('module_n11_api_secret');
        
        // Initialize rate limiting
        $this->window_start = time();
        
        $this->log->write('[INFO] EntegratorN11 initialized successfully');
    }
    
    /**
     * Test API Connection
     * 
     * @return array Connection test result
     */
    public function testConnection() {
        try {
            $this->log->write('[INFO] Testing N11 API connection...');
            
            if (empty($this->api_key) || empty($this->api_secret)) {
                throw new Exception('API credentials not configured');
            }
            
            // Test authentication
            $auth_result = $this->authenticate();
            
            if ($auth_result['success']) {
                $this->log->write('[SUCCESS] N11 API connection test successful');
                return [
                    'success' => true,
                    'message' => 'N11 API connection successful',
                    'data' => [
                        'api_version' => '1.0',
                        'connection_time' => date('Y-m-d H:i:s'),
                        'rate_limit' => $this->rate_limit,
                        'endpoints' => array_keys(self::ENDPOINTS)
                    ]
                ];
            } else {
                throw new Exception($auth_result['message']);
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] N11 API connection test failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }
    
    /**
     * Authenticate with N11 API
     * 
     * @return array Authentication result
     */
    private function authenticate() {
        try {
            $soap_client = new SoapClient($this->api_base_url . self::ENDPOINTS['auth']);
            
            $auth_request = [
                'appKey' => $this->api_key,
                'appSecret' => $this->api_secret
            ];
            
            $response = $soap_client->Authenticate($auth_request);
            
            if ($response->result->status == 'success') {
                return [
                    'success' => true,
                    'message' => 'Authentication successful',
                    'token' => $response->result->token ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response->result->errorMessage ?? 'Authentication failed'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Authentication error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get Product List from N11
     * 
     * @param array $params Query parameters
     * @return array Product list result
     */
    public function getProducts($params = []) {
        try {
            $this->checkRateLimit();
            $this->log->write('[INFO] Fetching products from N11...');
            
            $soap_client = new SoapClient($this->api_base_url . self::ENDPOINTS['product']);
            
            $request = [
                'auth' => $this->getAuthData(),
                'pagingData' => [
                    'currentPage' => $params['page'] ?? 0,
                    'pageSize' => $params['limit'] ?? 100
                ]
            ];
            
            $response = $soap_client->GetProductList($request);
            
            if ($response->result->status == 'success') {
                $products = [];
                
                if (isset($response->result->products->product)) {
                    $product_list = is_array($response->result->products->product) 
                        ? $response->result->products->product 
                        : [$response->result->products->product];
                    
                    foreach ($product_list as $product) {
                        $products[] = $this->convertN11ProductToOpenCart($product);
                    }
                }
                
                $this->log->write('[SUCCESS] Retrieved ' . count($products) . ' products from N11');
                
                return [
                    'success' => true,
                    'data' => $products,
                    'total' => $response->result->pagingData->totalCount ?? count($products),
                    'page' => $params['page'] ?? 0,
                    'limit' => $params['limit'] ?? 100
                ];
            } else {
                throw new Exception($response->result->errorMessage ?? 'Failed to fetch products');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to fetch products: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }
    
    /**
     * Create Product on N11
     * 
     * @param array $product_data OpenCart product data
     * @return array Creation result
     */
    public function createProduct($product_data) {
        try {
            $this->checkRateLimit();
            $this->log->write('[INFO] Creating product on N11: ' . $product_data['name']);
            
            $soap_client = new SoapClient($this->api_base_url . self::ENDPOINTS['product']);
            
            $n11_product = $this->convertOpenCartProductToN11($product_data);
            
            $request = [
                'auth' => $this->getAuthData(),
                'product' => $n11_product
            ];
            
            $response = $soap_client->SaveProduct($request);
            
            if ($response->result->status == 'success') {
                $this->log->write('[SUCCESS] Product created on N11: ' . $response->result->productId);
                
                return [
                    'success' => true,
                    'message' => 'Product created successfully',
                    'data' => [
                        'n11_product_id' => $response->result->productId,
                        'product_url' => $response->result->productUrl ?? null,
                        'status' => 'waiting_approval'
                    ]
                ];
            } else {
                throw new Exception($response->result->errorMessage ?? 'Failed to create product');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to create product: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }
    
    /**
     * Update Product on N11
     * 
     * @param string $n11_product_id N11 product ID
     * @param array $product_data Updated product data
     * @return array Update result
     */
    public function updateProduct($n11_product_id, $product_data) {
        try {
            $this->checkRateLimit();
            $this->log->write('[INFO] Updating product on N11: ' . $n11_product_id);
            
            $soap_client = new SoapClient($this->api_base_url . self::ENDPOINTS['product']);
            
            $n11_product = $this->convertOpenCartProductToN11($product_data);
            $n11_product['id'] = $n11_product_id;
            
            $request = [
                'auth' => $this->getAuthData(),
                'product' => $n11_product
            ];
            
            $response = $soap_client->UpdateProduct($request);
            
            if ($response->result->status == 'success') {
                $this->log->write('[SUCCESS] Product updated on N11: ' . $n11_product_id);
                
                return [
                    'success' => true,
                    'message' => 'Product updated successfully',
                    'data' => [
                        'n11_product_id' => $n11_product_id,
                        'last_update' => date('Y-m-d H:i:s')
                    ]
                ];
            } else {
                throw new Exception($response->result->errorMessage ?? 'Failed to update product');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to update product: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }
    
    /**
     * Delete Product from N11
     * 
     * @param string $n11_product_id N11 product ID
     * @return array Deletion result
     */
    public function deleteProduct($n11_product_id) {
        try {
            $this->checkRateLimit();
            $this->log->write('[INFO] Deleting product from N11: ' . $n11_product_id);
            
            $soap_client = new SoapClient($this->api_base_url . self::ENDPOINTS['product']);
            
            $request = [
                'auth' => $this->getAuthData(),
                'productId' => $n11_product_id
            ];
            
            $response = $soap_client->DeleteProduct($request);
            
            if ($response->result->status == 'success') {
                $this->log->write('[SUCCESS] Product deleted from N11: ' . $n11_product_id);
                
                return [
                    'success' => true,
                    'message' => 'Product deleted successfully'
                ];
            } else {
                throw new Exception($response->result->errorMessage ?? 'Failed to delete product');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to delete product: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }
    
    /**
     * Get Orders from N11
     * 
     * @param array $params Query parameters
     * @return array Orders result
     */
    public function getOrders($params = []) {
        try {
            $this->checkRateLimit();
            $this->log->write('[INFO] Fetching orders from N11...');
            
            $soap_client = new SoapClient($this->api_base_url . self::ENDPOINTS['order']);
            
            $request = [
                'auth' => $this->getAuthData(),
                'searchData' => [
                    'status' => $params['status'] ?? null,
                    'buyerName' => $params['buyer_name'] ?? null,
                    'orderNumber' => $params['order_number'] ?? null,
                    'productId' => $params['product_id'] ?? null,
                    'recipient' => $params['recipient'] ?? null,
                    'sameDayDelivery' => $params['same_day_delivery'] ?? null,
                    'period' => [
                        'startDate' => $params['start_date'] ?? date('Y-m-d', strtotime('-30 days')),
                        'endDate' => $params['end_date'] ?? date('Y-m-d')
                    ]
                ],
                'pagingData' => [
                    'currentPage' => $params['page'] ?? 0,
                    'pageSize' => $params['limit'] ?? 100
                ]
            ];
            
            $response = $soap_client->OrderList($request);
            
            if ($response->result->status == 'success') {
                $orders = [];
                
                if (isset($response->result->orderList->order)) {
                    $order_list = is_array($response->result->orderList->order) 
                        ? $response->result->orderList->order 
                        : [$response->result->orderList->order];
                    
                    foreach ($order_list as $order) {
                        $orders[] = $this->convertN11OrderToOpenCart($order);
                    }
                }
                
                $this->log->write('[SUCCESS] Retrieved ' . count($orders) . ' orders from N11');
                
                return [
                    'success' => true,
                    'data' => $orders,
                    'total' => $response->result->pagingData->totalCount ?? count($orders),
                    'page' => $params['page'] ?? 0,
                    'limit' => $params['limit'] ?? 100
                ];
            } else {
                throw new Exception($response->result->errorMessage ?? 'Failed to fetch orders');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to fetch orders: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }
    
    /**
     * Update Order Status on N11
     * 
     * @param string $order_number N11 order number
     * @param string $status New status
     * @param array $tracking_info Tracking information
     * @return array Update result
     */
    public function updateOrderStatus($order_number, $status, $tracking_info = []) {
        try {
            $this->checkRateLimit();
            $this->log->write('[INFO] Updating order status on N11: ' . $order_number . ' to ' . $status);
            
            $soap_client = new SoapClient($this->api_base_url . self::ENDPOINTS['order']);
            
            $request = [
                'auth' => $this->getAuthData(),
                'orderItemList' => [
                    'orderItem' => [
                        'id' => $order_number,
                        'status' => $status
                    ]
                ]
            ];
            
            // Add tracking info if provided
            if (!empty($tracking_info)) {
                $request['orderItemList']['orderItem']['shipmentInfo'] = [
                    'shipmentCompany' => $tracking_info['company'] ?? '',
                    'trackingNumber' => $tracking_info['tracking_number'] ?? '',
                    'trackingUrl' => $tracking_info['tracking_url'] ?? ''
                ];
            }
            
            $response = $soap_client->ChangeOrderStatus($request);
            
            if ($response->result->status == 'success') {
                $this->log->write('[SUCCESS] Order status updated on N11: ' . $order_number);
                
                return [
                    'success' => true,
                    'message' => 'Order status updated successfully',
                    'data' => [
                        'order_number' => $order_number,
                        'new_status' => $status,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]
                ];
            } else {
                throw new Exception($response->result->errorMessage ?? 'Failed to update order status');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to update order status: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }
    
    /**
     * Get Categories from N11
     * 
     * @return array Categories result
     */
    public function getCategories() {
        try {
            $this->checkRateLimit();
            $this->log->write('[INFO] Fetching categories from N11...');
            
            // Check cache first
            $cache_key = 'n11_categories';
            $cached_categories = $this->cache->get($cache_key);
            
            if ($cached_categories) {
                $this->log->write('[INFO] Retrieved categories from cache');
                return [
                    'success' => true,
                    'data' => $cached_categories,
                    'cached' => true
                ];
            }
            
            $soap_client = new SoapClient($this->api_base_url . self::ENDPOINTS['category']);
            
            $request = [
                'auth' => $this->getAuthData()
            ];
            
            $response = $soap_client->GetTopLevelCategories($request);
            
            if ($response->result->status == 'success') {
                $categories = [];
                
                if (isset($response->result->categoryList->category)) {
                    $category_list = is_array($response->result->categoryList->category) 
                        ? $response->result->categoryList->category 
                        : [$response->result->categoryList->category];
                    
                    foreach ($category_list as $category) {
                        $categories[] = [
                            'id' => $category->id,
                            'name' => $category->name,
                            'parent_id' => $category->parentId ?? 0,
                            'level' => $category->level ?? 0,
                            'is_leaf' => $category->isLeaf ?? false,
                            'commission_rate' => $category->commissionRate ?? 0
                        ];
                    }
                }
                
                // Cache categories for 1 hour
                $this->cache->set($cache_key, $categories, 3600);
                
                $this->log->write('[SUCCESS] Retrieved ' . count($categories) . ' categories from N11');
                
                return [
                    'success' => true,
                    'data' => $categories,
                    'cached' => false
                ];
            } else {
                throw new Exception($response->result->errorMessage ?? 'Failed to fetch categories');
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Failed to fetch categories: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }
    
    /**
     * Batch Process Products
     * 
     * @param array $products Array of products to process
     * @param string $operation Operation type (create, update, delete)
     * @return array Batch processing result
     */
    public function batchProcessProducts($products, $operation = 'create') {
        $this->log->write('[INFO] Starting batch processing: ' . $operation . ' for ' . count($products) . ' products');
        
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'processed' => []
        ];
        
        $batches = array_chunk($products, $this->batch_size);
        
        foreach ($batches as $batch_index => $batch) {
            $this->log->write('[INFO] Processing batch ' . ($batch_index + 1) . '/' . count($batches));
            
            foreach ($batch as $product) {
                $retry_count = 0;
                $success = false;
                
                while ($retry_count < $this->max_retries && !$success) {
                    try {
                        switch ($operation) {
                            case 'create':
                                $result = $this->createProduct($product);
                                break;
                            case 'update':
                                $result = $this->updateProduct($product['n11_product_id'], $product);
                                break;
                            case 'delete':
                                $result = $this->deleteProduct($product['n11_product_id']);
                                break;
                            default:
                                throw new Exception('Invalid operation: ' . $operation);
                        }
                        
                        if ($result['success']) {
                            $results['success']++;
                            $results['processed'][] = [
                                'product_id' => $product['product_id'] ?? $product['n11_product_id'],
                                'operation' => $operation,
                                'status' => 'success',
                                'result' => $result
                            ];
                            $success = true;
                        } else {
                            throw new Exception($result['message']);
                        }
                        
                    } catch (Exception $e) {
                        $retry_count++;
                        
                        if ($retry_count >= $this->max_retries) {
                            $results['failed']++;
                            $results['errors'][] = [
                                'product_id' => $product['product_id'] ?? $product['n11_product_id'],
                                'operation' => $operation,
                                'error' => $e->getMessage(),
                                'retries' => $retry_count
                            ];
                        } else {
                            // Wait before retry
                            sleep(1);
                        }
                    }
                }
            }
            
            // Rate limiting between batches
            if ($batch_index < count($batches) - 1) {
                sleep(2);
            }
        }
        
        $this->log->write('[INFO] Batch processing completed. Success: ' . $results['success'] . ', Failed: ' . $results['failed']);
        
        return $results;
    }
    
    /**
     * Handle Webhook from N11
     * 
     * @param array $webhook_data Webhook payload
     * @return array Processing result
     */
    public function handleWebhook($webhook_data) {
        try {
            $this->log->write('[INFO] Processing N11 webhook: ' . json_encode($webhook_data));
            
            $event_type = $webhook_data['eventType'] ?? '';
            $data = $webhook_data['data'] ?? [];
            
            switch ($event_type) {
                case 'ORDER_CREATED':
                    return $this->processOrderWebhook($data, 'created');
                    
                case 'ORDER_UPDATED':
                    return $this->processOrderWebhook($data, 'updated');
                    
                case 'PRODUCT_APPROVED':
                    return $this->processProductWebhook($data, 'approved');
                    
                case 'PRODUCT_REJECTED':
                    return $this->processProductWebhook($data, 'rejected');
                    
                case 'STOCK_UPDATED':
                    return $this->processStockWebhook($data);
                    
                default:
                    $this->log->write('[WARNING] Unknown webhook event type: ' . $event_type);
                    return [
                        'success' => false,
                        'message' => 'Unknown event type: ' . $event_type
                    ];
            }
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Webhook processing failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }
    
    /**
     * Process Order Webhook
     * 
     * @param array $order_data Order data from webhook
     * @param string $action Action type (created, updated)
     * @return array Processing result
     */
    private function processOrderWebhook($order_data, $action) {
        $this->log->write('[INFO] Processing order webhook: ' . $action);
        
        // Convert N11 order to OpenCart format
        $opencart_order = $this->convertN11OrderToOpenCart($order_data);
        
        // Save or update order in database
        $this->registry->get('load')->model('extension/module/n11');
        $model = $this->registry->get('model_extension_module_n11');
        
        if ($action === 'created') {
            $result = $model->saveOrderMapping(null, $order_data);
        } else {
            $result = $model->updateN11Order($order_data['orderNumber'], $order_data);
        }
        
        return [
            'success' => true,
            'message' => 'Order webhook processed successfully',
            'action' => $action,
            'order_number' => $order_data['orderNumber'] ?? 'unknown'
        ];
    }
    
    /**
     * Process Product Webhook
     * 
     * @param array $product_data Product data from webhook
     * @param string $status Product status (approved, rejected)
     * @return array Processing result
     */
    private function processProductWebhook($product_data, $status) {
        $this->log->write('[INFO] Processing product webhook: ' . $status);
        
        // Update product status in database
        $this->registry->get('load')->model('extension/module/n11');
        $model = $this->registry->get('model_extension_module_n11');
        
        $n11_product_id = $product_data['productId'] ?? '';
        
        if ($n11_product_id) {
            $model->updateProductListingStatus(null, $status, $n11_product_id);
        }
        
        return [
            'success' => true,
            'message' => 'Product webhook processed successfully',
            'status' => $status,
            'product_id' => $n11_product_id
        ];
    }
    
    /**
     * Process Stock Webhook
     * 
     * @param array $stock_data Stock data from webhook
     * @return array Processing result
     */
    private function processStockWebhook($stock_data) {
        $this->log->write('[INFO] Processing stock webhook');
        
        // Update stock information
        $product_id = $stock_data['productId'] ?? '';
        $new_stock = $stock_data['quantity'] ?? 0;
        
        if ($product_id) {
            // Update stock in OpenCart
            $this->registry->get('load')->model('catalog/product');
            $product_model = $this->registry->get('model_catalog_product');
            
            // Find OpenCart product by N11 product ID
            $this->registry->get('load')->model('extension/module/n11');
            $n11_model = $this->registry->get('model_extension_module_n11');
            
            $n11_product = $n11_model->getN11ProductByN11Id($product_id);
            
            if ($n11_product) {
                $product_model->editProduct($n11_product['product_id'], [
                    'quantity' => $new_stock
                ]);
            }
        }
        
        return [
            'success' => true,
            'message' => 'Stock webhook processed successfully',
            'product_id' => $product_id,
            'new_stock' => $new_stock
        ];
    }
    
    /**
     * Convert OpenCart Product to N11 Format
     * 
     * @param array $product OpenCart product data
     * @return array N11 formatted product
     */
    private function convertOpenCartProductToN11($product) {
        $commission_rate = $this->config->get('module_n11_commission_rate') ?: 8;
        
        // Calculate price including commission
        $base_price = (float)$product['price'];
        $final_price = $base_price * (1 + $commission_rate / 100);
        
        return [
            'productSellerCode' => $product['sku'] ?: 'OC_' . $product['product_id'],
            'title' => $product['name'],
            'subtitle' => substr($product['name'], 0, 100),
            'description' => strip_tags($product['description']),
            'category' => [
                'id' => $this->config->get('module_n11_default_category') ?: 1000
            ],
            'price' => number_format($final_price, 2, '.', ''),
            'currencyType' => 1, // TL
            'images' => $this->prepareProductImages($product),
            'approvalStatus' => 1,
            'preparingDay' => $this->config->get('module_n11_preparing_days') ?: 1,
            'attributes' => $this->prepareProductAttributes($product),
            'stockItems' => [
                'stockItem' => [
                    'bundle' => false,
                    'mpn' => $product['model'] ?: $product['sku'],
                    'gtin' => $product['ean'] ?? '',
                    'oem' => $product['manufacturer_model'] ?? '',
                    'quantity' => (int)$product['quantity'],
                    'sellerStockCode' => $product['sku'] ?: 'OC_' . $product['product_id'],
                    'attributes' => [
                        'attribute' => []
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Convert N11 Product to OpenCart Format
     * 
     * @param object $n11_product N11 product data
     * @return array OpenCart formatted product
     */
    private function convertN11ProductToOpenCart($n11_product) {
        return [
            'n11_product_id' => $n11_product->id,
            'name' => $n11_product->title,
            'description' => $n11_product->description ?? '',
            'sku' => $n11_product->productSellerCode ?? '',
            'model' => $n11_product->stockItems->stockItem->mpn ?? '',
            'price' => (float)$n11_product->price,
            'quantity' => (int)$n11_product->stockItems->stockItem->quantity,
            'status' => self::PRODUCT_STATUS[$n11_product->approvalStatus] ?? 0,
            'category_id' => $n11_product->category->id ?? null,
            'image_url' => $n11_product->images->image[0]->url ?? '',
            'product_url' => $n11_product->productUrl ?? '',
            'commission_rate' => $n11_product->commissionRate ?? 0,
            'last_update' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Convert N11 Order to OpenCart Format
     * 
     * @param object $n11_order N11 order data
     * @return array OpenCart formatted order
     */
    private function convertN11OrderToOpenCart($n11_order) {
        return [
            'n11_order_number' => $n11_order->orderNumber,
            'n11_order_id' => $n11_order->id,
            'customer_first_name' => $n11_order->buyerName ?? '',
            'customer_last_name' => '',
            'customer_email' => $n11_order->buyerEmail ?? 'noreply@n11.com',
            'customer_phone' => $n11_order->buyerPhone ?? '',
            'product_id' => $n11_order->orderItemList->orderItem->productId ?? '',
            'product_name' => $n11_order->orderItemList->orderItem->productName ?? '',
            'quantity' => (int)$n11_order->orderItemList->orderItem->quantity,
            'unit_price' => (float)$n11_order->orderItemList->orderItem->price,
            'total_amount' => (float)$n11_order->totalAmount,
            'commission_amount' => (float)$n11_order->commissionAmount,
            'currency' => 'TRY',
            'payment_type' => $n11_order->paymentType ?? 'credit_card',
            'order_status' => self::ORDER_STATUS[$n11_order->status] ?? 'new',
            'cargo_company' => $n11_order->shipmentInfo->shipmentCompany ?? '',
            'cargo_tracking_number' => $n11_order->shipmentInfo->trackingNumber ?? '',
            'billing_address' => $n11_order->billingAddress ?? '',
            'shipping_address' => $n11_order->shippingAddress ?? '',
            'city' => $n11_order->city ?? '',
            'district' => $n11_order->district ?? '',
            'order_date' => $n11_order->createDate ?? date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Prepare Product Images for N11
     * 
     * @param array $product Product data
     * @return array Images array
     */
    private function prepareProductImages($product) {
        $images = [];
        
        // Main image
        if (!empty($product['image'])) {
            $images[] = [
                'url' => HTTP_CATALOG . 'image/' . $product['image'],
                'order' => 1
            ];
        }
        
        // Additional images
        if (!empty($product['images'])) {
            $order = 2;
            foreach ($product['images'] as $image) {
                $images[] = [
                    'url' => HTTP_CATALOG . 'image/' . $image['image'],
                    'order' => $order++
                ];
            }
        }
        
        return ['image' => $images];
    }
    
    /**
     * Prepare Product Attributes for N11
     * 
     * @param array $product Product data
     * @return array Attributes array
     */
    private function prepareProductAttributes($product) {
        $attributes = [];
        
        if (!empty($product['attributes'])) {
            foreach ($product['attributes'] as $attribute) {
                $attributes[] = [
                    'name' => $attribute['name'],
                    'value' => $attribute['text']
                ];
            }
        }
        
        return ['attribute' => $attributes];
    }
    
    /**
     * Get Authentication Data
     * 
     * @return array Auth data
     */
    private function getAuthData() {
        return [
            'appKey' => $this->api_key,
            'appSecret' => $this->api_secret
        ];
    }
    
    /**
     * Check Rate Limiting
     * 
     * @throws Exception If rate limit exceeded
     */
    private function checkRateLimit() {
        $current_time = time();
        
        // Reset counter if window expired
        if ($current_time - $this->window_start >= $this->rate_window) {
            $this->request_count = 0;
            $this->window_start = $current_time;
        }
        
        // Check if rate limit exceeded
        if ($this->request_count >= $this->rate_limit) {
            $wait_time = $this->rate_window - ($current_time - $this->window_start);
            throw new Exception('Rate limit exceeded. Please wait ' . $wait_time . ' seconds.');
        }
        
        $this->request_count++;
    }
    
    /**
     * Get Performance Metrics
     * 
     * @return array Performance metrics
     */
    public function getPerformanceMetrics() {
        return [
            'rate_limit' => $this->rate_limit,
            'requests_made' => $this->request_count,
            'window_remaining' => $this->rate_window - (time() - $this->window_start),
            'batch_size' => $this->batch_size,
            'max_retries' => $this->max_retries,
            'api_endpoints' => count(self::ENDPOINTS),
            'last_request' => date('Y-m-d H:i:s')
        ];
    }
}