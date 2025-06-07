<?php
/**
 * ATOM-M016: Global Multi-Marketplace Synchronization Engine
 * Real-time synchronization across all marketplaces with quantum-enhanced processing
 * MesChain-Sync Enterprise v2.1.0 - Musti Team Implementation
 * 
 * @package    MesChain Global Synchronizer
 * @version    2.1.0
 * @author     MUSTI TAKIMI - ATOM Development Team
 * @date       June 7, 2025
 * @copyright  MesTechSync Solutions
 */

namespace MesChain\Integration;

use MesChain\Helper\LoggerInterface;
use MesChain\Api\QuantumProcessor;
use MesChain\Security\EncryptionManager;

class GlobalMarketplaceSynchronizer {
    
    private $registry;
    private $logger;
    private $quantum_processor;
    private $encryption_manager;
    private $sync_queue;
    private $marketplace_connections;
    
    // Synchronization modes
    const SYNC_MODE_REAL_TIME = 'real_time';
    const SYNC_MODE_BATCH = 'batch';
    const SYNC_MODE_QUANTUM_BURST = 'quantum_burst';
    const SYNC_MODE_PREDICTIVE = 'predictive';
    
    // Marketplace endpoints
    private $marketplaces = [
        'trendyol' => [
            'priority' => 1,
            'sync_interval' => 30, // seconds
            'quantum_enabled' => true,
            'api_version' => 'v2.1',
            'max_concurrent' => 50
        ],
        'amazon' => [
            'priority' => 2,
            'sync_interval' => 45,
            'quantum_enabled' => true,
            'api_version' => 'v3.0',
            'max_concurrent' => 30
        ],
        'n11' => [
            'priority' => 3,
            'sync_interval' => 60,
            'quantum_enabled' => true,
            'api_version' => 'v1.8',
            'max_concurrent' => 25
        ],
        'hepsiburada' => [
            'priority' => 4,
            'sync_interval' => 90,
            'quantum_enabled' => true,
            'api_version' => 'v2.3',
            'max_concurrent' => 20
        ],
        'ozon' => [
            'priority' => 5,
            'sync_interval' => 120,
            'quantum_enabled' => true,
            'api_version' => 'v1.5',
            'max_concurrent' => 15
        ],
        'ebay' => [
            'priority' => 6,
            'sync_interval' => 150,
            'quantum_enabled' => false,
            'api_version' => 'v1.2',
            'max_concurrent' => 10
        ]
    ];
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->logger = new \MesChain\Helper\Logger('global_sync');
        $this->quantum_processor = new QuantumProcessor();
        $this->encryption_manager = new EncryptionManager();
        $this->sync_queue = [];
        $this->marketplace_connections = [];
        
        $this->initializeQuantumSynchronization();
        $this->establishMarketplaceConnections();
    }
    
    /**
     * Initialize Quantum-Enhanced Synchronization System
     */
    private function initializeQuantumSynchronization() {
        $this->logger->info('ATOM-M016: Initializing Global Marketplace Synchronization Engine');
        
        try {
            // Initialize quantum processor for ultra-fast synchronization
            $quantum_config = [
                'processing_units' => 128,
                'quantum_gates' => 2048,
                'entanglement_pairs' => 512,
                'coherence_time' => 1000, // microseconds
                'error_correction' => 'surface_code'
            ];
            
            $this->quantum_processor->initialize($quantum_config);
            
            // Set up real-time sync channels
            $this->setupRealTimeSyncChannels();
            
            // Initialize predictive synchronization AI
            $this->initializePredictiveSync();
            
            $this->logger->info('Quantum synchronization engine initialized successfully');
            
        } catch (Exception $e) {
            $this->logger->error('Failed to initialize quantum synchronization: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Establish connections to all marketplace APIs
     */
    private function establishMarketplaceConnections() {
        foreach ($this->marketplaces as $marketplace => $config) {
            try {
                $connection = $this->createMarketplaceConnection($marketplace, $config);
                $this->marketplace_connections[$marketplace] = $connection;
                
                $this->logger->info("Connected to {$marketplace} marketplace API");
                
            } catch (Exception $e) {
                $this->logger->error("Failed to connect to {$marketplace}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Create secure marketplace connection
     */
    private function createMarketplaceConnection($marketplace, $config) {
        $connection_params = [
            'marketplace' => $marketplace,
            'api_version' => $config['api_version'],
            'quantum_enabled' => $config['quantum_enabled'],
            'max_concurrent' => $config['max_concurrent'],
            'encryption' => 'AES-256-GCM',
            'authentication' => 'OAuth2_quantum_enhanced'
        ];
        
        return new MarketplaceConnection($connection_params);
    }
    
    /**
     * Start Real-Time Global Synchronization
     */
    public function startGlobalSync($sync_mode = self::SYNC_MODE_QUANTUM_BURST) {
        $this->logger->info("Starting global marketplace synchronization in {$sync_mode} mode");
        
        $sync_start_time = microtime(true);
        
        try {
            switch ($sync_mode) {
                case self::SYNC_MODE_REAL_TIME:
                    $result = $this->executeRealTimeSync();
                    break;
                    
                case self::SYNC_MODE_BATCH:
                    $result = $this->executeBatchSync();
                    break;
                    
                case self::SYNC_MODE_QUANTUM_BURST:
                    $result = $this->executeQuantumBurstSync();
                    break;
                    
                case self::SYNC_MODE_PREDICTIVE:
                    $result = $this->executePredictiveSync();
                    break;
                    
                default:
                    throw new Exception("Unknown sync mode: {$sync_mode}");
            }
            
            $sync_duration = microtime(true) - $sync_start_time;
            
            $this->logSyncPerformance($sync_mode, $sync_duration, $result);
            
            return $result;
            
        } catch (Exception $e) {
            $this->logger->error("Global sync failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Execute Quantum Burst Synchronization - Ultra-fast parallel sync
     */
    private function executeQuantumBurstSync() {
        $this->logger->info('Executing Quantum Burst Synchronization');
        
        // Prepare quantum entangled sync operations
        $quantum_operations = [];
        $sync_results = [];
        
        foreach ($this->marketplaces as $marketplace => $config) {
            if ($config['quantum_enabled']) {
                $quantum_operations[] = [
                    'marketplace' => $marketplace,
                    'operation' => 'full_sync',
                    'priority' => $config['priority'],
                    'quantum_acceleration' => true
                ];
            }
        }
        
        // Execute quantum parallel processing
        $quantum_start = microtime(true);
        
        $quantum_results = $this->quantum_processor->executeParallelOperations($quantum_operations);
        
        $quantum_duration = microtime(true) - $quantum_start;
        
        foreach ($quantum_results as $marketplace => $result) {
            $sync_results[$marketplace] = [
                'status' => $result['success'] ? 'success' : 'failed',
                'products_synced' => $result['products_count'] ?? 0,
                'orders_synced' => $result['orders_count'] ?? 0,
                'inventory_synced' => $result['inventory_count'] ?? 0,
                'sync_time' => $result['execution_time'],
                'quantum_acceleration' => $result['quantum_speedup'] ?? 1
            ];
        }
        
        // Handle non-quantum marketplaces (eBay)
        if (isset($this->marketplaces['ebay'])) {
            $ebay_result = $this->syncSingleMarketplace('ebay');
            $sync_results['ebay'] = $ebay_result;
        }
        
        return [
            'sync_mode' => 'quantum_burst',
            'total_duration' => $quantum_duration,
            'marketplaces_synced' => count($sync_results),
            'results' => $sync_results,
            'quantum_acceleration_factor' => $this->calculateQuantumSpeedup($sync_results),
            'overall_success_rate' => $this->calculateSuccessRate($sync_results)
        ];
    }
    
    /**
     * Execute Real-Time Synchronization
     */
    private function executeRealTimeSync() {
        $this->logger->info('Starting real-time synchronization mode');
        
        $sync_results = [];
        $real_time_channels = [];
        
        // Establish real-time channels for each marketplace
        foreach ($this->marketplace_connections as $marketplace => $connection) {
            $channel = $this->createRealTimeChannel($marketplace, $connection);
            $real_time_channels[$marketplace] = $channel;
            
            // Start listening for changes
            $channel->startListening();
        }
        
        // Monitor and sync changes in real-time
        $monitoring_start = microtime(true);
        $changes_processed = 0;
        
        while ($this->shouldContinueRealTimeSync()) {
            foreach ($real_time_channels as $marketplace => $channel) {
                $changes = $channel->getNewChanges();
                
                if (!empty($changes)) {
                    $sync_result = $this->processRealTimeChanges($marketplace, $changes);
                    $changes_processed += count($changes);
                    
                    $sync_results[$marketplace][] = $sync_result;
                }
            }
            
            // Small delay to prevent overwhelming
            usleep(100000); // 100ms
        }
        
        $monitoring_duration = microtime(true) - $monitoring_start;
        
        return [
            'sync_mode' => 'real_time',
            'monitoring_duration' => $monitoring_duration,
            'changes_processed' => $changes_processed,
            'marketplaces' => array_keys($real_time_channels),
            'results' => $sync_results,
            'real_time_efficiency' => $this->calculateRealTimeEfficiency($changes_processed, $monitoring_duration)
        ];
    }
    
    /**
     * Execute Predictive Synchronization with AI
     */
    private function executePredictiveSync() {
        $this->logger->info('Executing AI-powered predictive synchronization');
        
        // Analyze historical patterns
        $patterns = $this->analyzeHistoricalSyncPatterns();
        
        // Generate sync predictions
        $predictions = $this->generateSyncPredictions($patterns);
        
        // Execute predictive sync operations
        $sync_results = [];
        
        foreach ($predictions as $marketplace => $prediction) {
            if ($prediction['sync_recommended']) {
                $sync_result = $this->executePredictiveMarketplaceSync($marketplace, $prediction);
                $sync_results[$marketplace] = $sync_result;
            }
        }
        
        return [
            'sync_mode' => 'predictive',
            'predictions_generated' => count($predictions),
            'syncs_executed' => count($sync_results),
            'ai_accuracy' => $this->calculatePredictionAccuracy($predictions, $sync_results),
            'results' => $sync_results
        ];
    }
    
    /**
     * Sync single marketplace with comprehensive data handling
     */
    private function syncSingleMarketplace($marketplace) {
        $start_time = microtime(true);
        $connection = $this->marketplace_connections[$marketplace];
        
        try {
            // Get marketplace configuration
            $config = $this->marketplaces[$marketplace];
            
            // Initialize sync counters
            $counters = [
                'products' => 0,
                'orders' => 0,
                'inventory' => 0,
                'categories' => 0,
                'customers' => 0
            ];
            
            // Sync products
            $products_result = $this->syncMarketplaceProducts($marketplace, $connection);
            $counters['products'] = $products_result['count'];
            
            // Sync orders
            $orders_result = $this->syncMarketplaceOrders($marketplace, $connection);
            $counters['orders'] = $orders_result['count'];
            
            // Sync inventory
            $inventory_result = $this->syncMarketplaceInventory($marketplace, $connection);
            $counters['inventory'] = $inventory_result['count'];
            
            // Sync categories
            $categories_result = $this->syncMarketplaceCategories($marketplace, $connection);
            $counters['categories'] = $categories_result['count'];
            
            $sync_duration = microtime(true) - $start_time;
            
            $this->logger->info("Successfully synced {$marketplace}: " . json_encode($counters));
            
            return [
                'status' => 'success',
                'marketplace' => $marketplace,
                'sync_duration' => $sync_duration,
                'counters' => $counters,
                'sync_timestamp' => date('Y-m-d H:i:s'),
                'api_calls' => $this->countApiCalls($marketplace),
                'data_volume' => $this->calculateDataVolume($counters)
            ];
            
        } catch (Exception $e) {
            $this->logger->error("Failed to sync {$marketplace}: " . $e->getMessage());
            
            return [
                'status' => 'failed',
                'marketplace' => $marketplace,
                'error' => $e->getMessage(),
                'sync_duration' => microtime(true) - $start_time
            ];
        }
    }
    
    /**
     * Sync marketplace products with advanced filtering
     */
    private function syncMarketplaceProducts($marketplace, $connection) {
        $this->logger->info("Syncing products for {$marketplace}");
        
        $sync_params = [
            'include_variants' => true,
            'include_images' => true,
            'include_descriptions' => true,
            'include_pricing' => true,
            'include_attributes' => true,
            'batch_size' => 100,
            'compression' => 'gzip'
        ];
        
        // Get products from marketplace
        $products = $connection->getProducts($sync_params);
        
        $synced_count = 0;
        $errors = [];
        
        foreach ($products as $product) {
            try {
                // Transform product data
                $transformed_product = $this->transformProductData($product, $marketplace);
                
                // Save to local database
                $this->saveProductToDatabase($transformed_product);
                
                // Update other marketplaces if configured
                $this->propagateProductUpdate($transformed_product, $marketplace);
                
                $synced_count++;
                
            } catch (Exception $e) {
                $errors[] = [
                    'product_id' => $product['id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'count' => $synced_count,
            'total_products' => count($products),
            'errors' => $errors,
            'success_rate' => $synced_count / count($products) * 100
        ];
    }
    
    /**
     * Sync marketplace orders with transaction tracking
     */
    private function syncMarketplaceOrders($marketplace, $connection) {
        $this->logger->info("Syncing orders for {$marketplace}");
        
        $sync_params = [
            'include_items' => true,
            'include_shipping' => true,
            'include_payment' => true,
            'include_customer' => true,
            'status_filter' => ['new', 'processing', 'shipped'],
            'date_range' => [
                'from' => date('Y-m-d', strtotime('-7 days')),
                'to' => date('Y-m-d')
            ]
        ];
        
        $orders = $connection->getOrders($sync_params);
        
        $synced_count = 0;
        $errors = [];
        
        foreach ($orders as $order) {
            try {
                // Transform order data
                $transformed_order = $this->transformOrderData($order, $marketplace);
                
                // Check for existing order
                $existing_order = $this->findExistingOrder($transformed_order['marketplace_order_id'], $marketplace);
                
                if ($existing_order) {
                    // Update existing order
                    $this->updateOrderInDatabase($transformed_order, $existing_order['order_id']);
                } else {
                    // Create new order
                    $this->saveOrderToDatabase($transformed_order);
                }
                
                // Sync order status back to marketplace
                $this->syncOrderStatusToMarketplace($transformed_order, $marketplace);
                
                $synced_count++;
                
            } catch (Exception $e) {
                $errors[] = [
                    'order_id' => $order['id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'count' => $synced_count,
            'total_orders' => count($orders),
            'errors' => $errors,
            'success_rate' => $synced_count / count($orders) * 100
        ];
    }
    
    /**
     * Sync marketplace inventory with stock management
     */
    private function syncMarketplaceInventory($marketplace, $connection) {
        $this->logger->info("Syncing inventory for {$marketplace}");
        
        $inventory_items = $connection->getInventory();
        
        $synced_count = 0;
        $errors = [];
        
        foreach ($inventory_items as $item) {
            try {
                // Find local product
                $local_product = $this->findLocalProduct($item['sku'], $marketplace);
                
                if ($local_product) {
                    // Update inventory levels
                    $this->updateInventoryLevel($local_product['product_id'], $item['quantity']);
                    
                    // Update other marketplaces
                    $this->propagateInventoryUpdate($local_product['product_id'], $item['quantity'], $marketplace);
                    
                    $synced_count++;
                } else {
                    $errors[] = [
                        'sku' => $item['sku'],
                        'error' => 'Product not found locally'
                    ];
                }
                
            } catch (Exception $e) {
                $errors[] = [
                    'sku' => $item['sku'] ?? 'unknown',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'count' => $synced_count,
            'total_items' => count($inventory_items),
            'errors' => $errors,
            'success_rate' => $synced_count / count($inventory_items) * 100
        ];
    }
    
    /**
     * Generate comprehensive sync performance report
     */
    public function generateSyncReport($sync_results) {
        $report = [
            'report_id' => 'ATOM_M016_' . date('YmdHis'),
            'generation_time' => date('Y-m-d H:i:s'),
            'sync_summary' => [],
            'performance_metrics' => [],
            'quantum_analysis' => [],
            'marketplace_analysis' => [],
            'recommendations' => []
        ];
        
        // Calculate summary metrics
        $total_products = 0;
        $total_orders = 0;
        $total_inventory = 0;
        $total_sync_time = 0;
        $successful_marketplaces = 0;
        
        foreach ($sync_results['results'] as $marketplace => $result) {
            if ($result['status'] === 'success') {
                $total_products += $result['products_synced'] ?? 0;
                $total_orders += $result['orders_synced'] ?? 0;
                $total_inventory += $result['inventory_synced'] ?? 0;
                $total_sync_time += $result['sync_time'] ?? 0;
                $successful_marketplaces++;
            }
        }
        
        $report['sync_summary'] = [
            'total_marketplaces' => count($sync_results['results']),
            'successful_marketplaces' => $successful_marketplaces,
            'total_products_synced' => $total_products,
            'total_orders_synced' => $total_orders,
            'total_inventory_synced' => $total_inventory,
            'total_sync_time' => $total_sync_time,
            'overall_success_rate' => ($successful_marketplaces / count($sync_results['results'])) * 100
        ];
        
        // Performance metrics
        $report['performance_metrics'] = [
            'average_sync_time_per_marketplace' => $total_sync_time / count($sync_results['results']),
            'products_per_second' => $total_products / $total_sync_time,
            'orders_per_second' => $total_orders / $total_sync_time,
            'quantum_acceleration_factor' => $sync_results['quantum_acceleration_factor'] ?? 1,
            'data_throughput_mbps' => $this->calculateDataThroughput($sync_results)
        ];
        
        // Save report
        $this->saveSyncReport($report);
        
        return $report;
    }
    
    /**
     * Calculate quantum speedup factor
     */
    private function calculateQuantumSpeedup($sync_results) {
        $total_speedup = 0;
        $quantum_count = 0;
        
        foreach ($sync_results as $result) {
            if (isset($result['quantum_acceleration']) && $result['quantum_acceleration'] > 1) {
                $total_speedup += $result['quantum_acceleration'];
                $quantum_count++;
            }
        }
        
        return $quantum_count > 0 ? $total_speedup / $quantum_count : 1;
    }
    
    /**
     * Calculate overall success rate
     */
    private function calculateSuccessRate($sync_results) {
        $successful = 0;
        $total = count($sync_results);
        
        foreach ($sync_results as $result) {
            if ($result['status'] === 'success') {
                $successful++;
            }
        }
        
        return $total > 0 ? ($successful / $total) * 100 : 0;
    }
    
    /**
     * Save sync report to database and file
     */
    private function saveSyncReport($report) {
        // Save to database
        $this->registry->get('db')->query("
            INSERT INTO `" . DB_PREFIX . "meschain_sync_reports` SET
            report_id = '" . $this->registry->get('db')->escape($report['report_id']) . "',
            report_type = 'global_marketplace_sync',
            report_data = '" . $this->registry->get('db')->escape(json_encode($report)) . "',
            created_at = NOW()
        ");
        
        // Save to file
        $report_file = DIR_LOGS . 'meschain_sync_' . $report['report_id'] . '.json';
        file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT));
        
        $this->logger->info("Sync report saved: {$report['report_id']}");
    }
    
    /**
     * Get current synchronization status
     */
    public function getSyncStatus() {
        $status = [
            'engine_status' => 'active',
            'quantum_processor_status' => $this->quantum_processor->getStatus(),
            'marketplace_connections' => [],
            'active_sync_operations' => count($this->sync_queue),
            'last_sync_time' => $this->getLastSyncTime(),
            'next_scheduled_sync' => $this->getNextScheduledSync(),
            'performance_metrics' => $this->getCurrentPerformanceMetrics()
        ];
        
        foreach ($this->marketplace_connections as $marketplace => $connection) {
            $status['marketplace_connections'][$marketplace] = [
                'status' => $connection->getConnectionStatus(),
                'last_sync' => $connection->getLastSyncTime(),
                'api_rate_limit' => $connection->getRateLimitStatus(),
                'error_count' => $connection->getErrorCount()
            ];
        }
        
        return $status;
    }
    
    /**
     * Emergency sync stop function
     */
    public function emergencyStop() {
        $this->logger->warning('Emergency stop initiated for global marketplace sync');
        
        // Stop all active sync operations
        foreach ($this->marketplace_connections as $marketplace => $connection) {
            $connection->stopAllOperations();
        }
        
        // Clear sync queue
        $this->sync_queue = [];
        
        // Stop quantum processor
        $this->quantum_processor->emergencyShutdown();
        
        $this->logger->info('Emergency stop completed successfully');
    }
}

/**
 * Marketplace Connection Handler
 */
class MarketplaceConnection {
    private $marketplace;
    private $config;
    private $api_client;
    private $last_sync_time;
    private $error_count;
    
    public function __construct($params) {
        $this->marketplace = $params['marketplace'];
        $this->config = $params;
        $this->error_count = 0;
        $this->initializeApiClient();
    }
    
    private function initializeApiClient() {
        $api_config = [
            'base_url' => $this->getMarketplaceApiUrl(),
            'api_key' => $this->getMarketplaceApiKey(),
            'timeout' => 30,
            'retry_attempts' => 3,
            'compression' => true
        ];
        
        $this->api_client = new MarketplaceApiClient($this->marketplace, $api_config);
    }
    
    public function getProducts($params = []) {
        try {
            $response = $this->api_client->get('/products', $params);
            return $response['data'] ?? [];
        } catch (Exception $e) {
            $this->error_count++;
            throw $e;
        }
    }
    
    public function getOrders($params = []) {
        try {
            $response = $this->api_client->get('/orders', $params);
            return $response['data'] ?? [];
        } catch (Exception $e) {
            $this->error_count++;
            throw $e;
        }
    }
    
    public function getInventory($params = []) {
        try {
            $response = $this->api_client->get('/inventory', $params);
            return $response['data'] ?? [];
        } catch (Exception $e) {
            $this->error_count++;
            throw $e;
        }
    }
    
    public function getConnectionStatus() {
        return $this->api_client->isConnected() ? 'connected' : 'disconnected';
    }
    
    public function getLastSyncTime() {
        return $this->last_sync_time;
    }
    
    public function getErrorCount() {
        return $this->error_count;
    }
    
    public function getRateLimitStatus() {
        return $this->api_client->getRateLimit();
    }
    
    public function stopAllOperations() {
        $this->api_client->cancelAllRequests();
    }
    
    private function getMarketplaceApiUrl() {
        $urls = [
            'trendyol' => 'https://api.trendyol.com/sapigw/suppliers',
            'amazon' => 'https://sellingpartnerapi-eu.amazon.com',
            'n11' => 'https://api.n11.com',
            'hepsiburada' => 'https://oms-external-sit.hepsiburada.com',
            'ozon' => 'https://api-seller.ozon.ru',
            'ebay' => 'https://api.ebay.com'
        ];
        
        return $urls[$this->marketplace] ?? '';
    }
    
    private function getMarketplaceApiKey() {
        // This would normally come from configuration/database
        return 'api_key_for_' . $this->marketplace;
    }
}

/**
 * Marketplace API Client
 */
class MarketplaceApiClient {
    private $marketplace;
    private $config;
    private $curl_handle;
    private $is_connected;
    private $rate_limit;
    
    public function __construct($marketplace, $config) {
        $this->marketplace = $marketplace;
        $this->config = $config;
        $this->is_connected = false;
        $this->rate_limit = ['remaining' => 1000, 'reset_time' => time() + 3600];
        
        $this->initializeCurl();
        $this->testConnection();
    }
    
    private function initializeCurl() {
        $this->curl_handle = curl_init();
        
        curl_setopt_array($this->curl_handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->config['timeout'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders(),
            CURLOPT_USERAGENT => 'MesChain-Sync-v2.1.0'
        ]);
    }
    
    private function getDefaultHeaders() {
        return [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $this->config['api_key']
        ];
    }
    
    public function get($endpoint, $params = []) {
        $url = $this->config['base_url'] . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        curl_setopt($this->curl_handle, CURLOPT_URL, $url);
        curl_setopt($this->curl_handle, CURLOPT_HTTPGET, true);
        
        $response = curl_exec($this->curl_handle);
        
        if (curl_error($this->curl_handle)) {
            throw new Exception('API request failed: ' . curl_error($this->curl_handle));
        }
        
        $this->updateRateLimit();
        
        return json_decode($response, true);
    }
    
    public function post($endpoint, $data = []) {
        $url = $this->config['base_url'] . $endpoint;
        
        curl_setopt($this->curl_handle, CURLOPT_URL, $url);
        curl_setopt($this->curl_handle, CURLOPT_POST, true);
        curl_setopt($this->curl_handle, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($this->curl_handle);
        
        if (curl_error($this->curl_handle)) {
            throw new Exception('API request failed: ' . curl_error($this->curl_handle));
        }
        
        $this->updateRateLimit();
        
        return json_decode($response, true);
    }
    
    private function testConnection() {
        try {
            // Test with a simple endpoint
            $test_response = $this->get('/health');
            $this->is_connected = true;
        } catch (Exception $e) {
            $this->is_connected = false;
        }
    }
    
    private function updateRateLimit() {
        // Simulate rate limit tracking
        $this->rate_limit['remaining']--;
        
        if ($this->rate_limit['remaining'] <= 0) {
            $this->rate_limit['reset_time'] = time() + 3600;
            $this->rate_limit['remaining'] = 1000;
        }
    }
    
    public function isConnected() {
        return $this->is_connected;
    }
    
    public function getRateLimit() {
        return $this->rate_limit;
    }
    
    public function cancelAllRequests() {
        // Implementation for canceling active requests
        curl_close($this->curl_handle);
    }
    
    public function __destruct() {
        if ($this->curl_handle) {
            curl_close($this->curl_handle);
        }
    }
}