<?php
/**
 * MesChain WebSocket Server
 * Real-time Marketplace Data Streaming Server
 * 
 * @version 1.0.0
 * @date June 2, 2025
 * @author MesChain Development Team
 */

require_once 'vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class MeschainWebSocketServer implements MessageComponentInterface {
    
    protected $clients;
    protected $marketplace_connections;
    protected $data_collectors;
    protected $last_data_broadcast;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->marketplace_connections = [
            'amazon' => new \SplObjectStorage,
            'ebay' => new \SplObjectStorage,
            'trendyol' => new \SplObjectStorage,
            'n11' => new \SplObjectStorage,
            'hepsiburada' => new \SplObjectStorage,
            'ozon' => new \SplObjectStorage,
            'all' => new \SplObjectStorage
        ];
        $this->data_collectors = [];
        $this->last_data_broadcast = time();
        
        // Initialize data collectors for each marketplace
        $this->initializeDataCollectors();
        
        echo "MesChain WebSocket Server started on port 8080\n";
    }
    
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $this->marketplace_connections['all']->attach($conn);
        
        echo "New connection ({$conn->resourceId})\n";
        
        // Send welcome message with available channels
        $welcome_message = [
            'type' => 'welcome',
            'data' => [
                'connection_id' => $conn->resourceId,
                'available_channels' => array_keys($this->marketplace_connections),
                'timestamp' => time()
            ]
        ];
        
        $conn->send(json_encode($welcome_message));
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);
            
            if (!$data || !isset($data['action'])) {
                $this->sendError($from, 'Invalid message format');
                return;
            }
            
            switch ($data['action']) {
                case 'subscribe':
                    $this->handleSubscription($from, $data);
                    break;
                    
                case 'unsubscribe':
                    $this->handleUnsubscription($from, $data);
                    break;
                    
                case 'get_metrics':
                    $this->handleMetricsRequest($from, $data);
                    break;
                    
                case 'ping':
                    $this->handlePing($from);
                    break;
                    
                default:
                    $this->sendError($from, 'Unknown action: ' . $data['action']);
            }
            
        } catch (Exception $e) {
            $this->sendError($from, 'Message processing error: ' . $e->getMessage());
        }
    }
    
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        
        // Remove from all marketplace connections
        foreach ($this->marketplace_connections as $marketplace => $connections) {
            if ($connections->contains($conn)) {
                $connections->detach($conn);
            }
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
    
    /**
     * Handle subscription to marketplace channels
     */
    private function handleSubscription(ConnectionInterface $conn, $data) {
        $marketplace = $data['marketplace'] ?? 'all';
        $events = $data['events'] ?? ['all'];
        
        if (!isset($this->marketplace_connections[$marketplace])) {
            $this->sendError($conn, 'Invalid marketplace: ' . $marketplace);
            return;
        }
        
        // Add connection to marketplace-specific storage
        $this->marketplace_connections[$marketplace]->attach($conn, [
            'events' => $events,
            'subscribed_at' => time()
        ]);
        
        // Send confirmation
        $response = [
            'type' => 'subscription_confirmed',
            'data' => [
                'marketplace' => $marketplace,
                'events' => $events,
                'timestamp' => time()
            ]
        ];
        
        $conn->send(json_encode($response));
        
        // Send initial data
        $this->sendInitialData($conn, $marketplace);
        
        echo "Connection {$conn->resourceId} subscribed to {$marketplace}\n";
    }
    
    /**
     * Handle unsubscription from marketplace channels
     */
    private function handleUnsubscription(ConnectionInterface $conn, $data) {
        $marketplace = $data['marketplace'] ?? 'all';
        
        if (isset($this->marketplace_connections[$marketplace])) {
            $this->marketplace_connections[$marketplace]->detach($conn);
        }
        
        $response = [
            'type' => 'unsubscription_confirmed',
            'data' => [
                'marketplace' => $marketplace,
                'timestamp' => time()
            ]
        ];
        
        $conn->send(json_encode($response));
        
        echo "Connection {$conn->resourceId} unsubscribed from {$marketplace}\n";
    }
    
    /**
     * Handle real-time metrics request
     */
    private function handleMetricsRequest(ConnectionInterface $conn, $data) {
        $marketplace = $data['marketplace'] ?? 'all';
        $metrics_type = $data['metrics_type'] ?? 'summary';
        
        $metrics = $this->collectMetrics($marketplace, $metrics_type);
        
        $response = [
            'type' => 'metrics_response',
            'data' => [
                'marketplace' => $marketplace,
                'metrics_type' => $metrics_type,
                'metrics' => $metrics,
                'timestamp' => time()
            ]
        ];
        
        $conn->send(json_encode($response));
    }
    
    /**
     * Handle ping request
     */
    private function handlePing(ConnectionInterface $conn) {
        $response = [
            'type' => 'pong',
            'data' => [
                'timestamp' => time(),
                'server_time' => date('Y-m-d H:i:s')
            ]
        ];
        
        $conn->send(json_encode($response));
    }
    
    /**
     * Send error message to connection
     */
    private function sendError(ConnectionInterface $conn, $message) {
        $error = [
            'type' => 'error',
            'data' => [
                'message' => $message,
                'timestamp' => time()
            ]
        ];
        
        $conn->send(json_encode($error));
    }
    
    /**
     * Send initial data when client subscribes
     */
    private function sendInitialData(ConnectionInterface $conn, $marketplace) {
        $initial_data = $this->collectMetrics($marketplace, 'initial');
        
        $message = [
            'type' => 'initial_data',
            'data' => [
                'marketplace' => $marketplace,
                'metrics' => $initial_data,
                'timestamp' => time()
            ]
        ];
        
        $conn->send(json_encode($message));
    }
    
    /**
     * Broadcast real-time updates to subscribed clients
     */
    public function broadcastUpdates() {
        $current_time = time();
        
        // Broadcast every 5 seconds
        if ($current_time - $this->last_data_broadcast < 5) {
            return;
        }
        
        foreach ($this->marketplace_connections as $marketplace => $connections) {
            if ($connections->count() === 0) {
                continue;
            }
            
            $updates = $this->collectRealtimeUpdates($marketplace);
            
            if (empty($updates)) {
                continue;
            }
            
            $message = [
                'type' => 'realtime_update',
                'data' => [
                    'marketplace' => $marketplace,
                    'updates' => $updates,
                    'timestamp' => $current_time
                ]
            ];
            
            $json_message = json_encode($message);
            
            foreach ($connections as $conn) {
                try {
                    $conn->send($json_message);
                } catch (Exception $e) {
                    echo "Error sending to connection: " . $e->getMessage() . "\n";
                    $this->onClose($conn);
                }
            }
        }
        
        $this->last_data_broadcast = $current_time;
    }
    
    /**
     * Initialize data collectors for each marketplace
     */
    private function initializeDataCollectors() {
        $marketplaces = ['amazon', 'ebay', 'trendyol', 'n11', 'hepsiburada', 'ozon'];
        
        foreach ($marketplaces as $marketplace) {
            $this->data_collectors[$marketplace] = [
                'last_update' => 0,
                'cached_data' => [],
                'update_interval' => 10 // seconds
            ];
        }
    }
    
    /**
     * Collect metrics for specific marketplace
     */
    private function collectMetrics($marketplace, $type = 'summary') {
        if ($marketplace === 'all') {
            return $this->collectAllMarketplaceMetrics($type);
        }
        
        // Simulate real marketplace API calls
        // In production, these would call actual marketplace APIs
        return $this->getMarketplaceData($marketplace, $type);
    }
    
    /**
     * Collect real-time updates for marketplace
     */
    private function collectRealtimeUpdates($marketplace) {
        if ($marketplace === 'all') {
            return $this->collectAllMarketplaceUpdates();
        }
        
        // Check if we need to update cached data
        $collector = &$this->data_collectors[$marketplace];
        $current_time = time();
        
        if ($current_time - $collector['last_update'] >= $collector['update_interval']) {
            $collector['cached_data'] = $this->fetchRealtimeData($marketplace);
            $collector['last_update'] = $current_time;
        }
        
        return $collector['cached_data'];
    }
    
    /**
     * Get marketplace-specific data
     */
    private function getMarketplaceData($marketplace, $type) {
        // Simulated data - replace with actual API calls
        $base_data = [
            'orders_today' => rand(10, 100),
            'sales_today' => rand(1000, 10000),
            'active_products' => rand(100, 1000),
            'pending_orders' => rand(5, 50),
            'connection_status' => 'connected',
            'last_sync' => date('Y-m-d H:i:s')
        ];
        
        // Add marketplace-specific metrics
        switch ($marketplace) {
            case 'amazon':
                $base_data['buybox_wins'] = rand(10, 50);
                $base_data['fba_inventory'] = rand(500, 2000);
                break;
                
            case 'ebay':
                $base_data['watchers'] = rand(50, 200);
                $base_data['best_offers'] = rand(5, 25);
                break;
                
            case 'trendyol':
                $base_data['seller_score'] = rand(8.0, 10.0);
                $base_data['fast_delivery'] = rand(20, 80);
                break;
                
            case 'n11':
                $base_data['membership_level'] = 'Gold';
                $base_data['store_credit'] = rand(1000, 5000);
                break;
                
            case 'hepsiburada':
                $base_data['choice_products'] = rand(50, 200);
                $base_data['seller_rating'] = rand(8.5, 9.8);
                break;
                
            case 'ozon':
                $base_data['fbo_products'] = rand(200, 800);
                $base_data['premium_status'] = 'active';
                break;
        }
        
        return $base_data;
    }
    
    /**
     * Fetch real-time data from marketplace APIs
     */
    private function fetchRealtimeData($marketplace) {
        // Simulated real-time events
        $events = [];
        
        // Random events generation
        if (rand(1, 10) > 7) {
            $events[] = [
                'type' => 'new_order',
                'data' => [
                    'order_id' => 'ORD' . rand(10000, 99999),
                    'amount' => rand(50, 500),
                    'marketplace' => $marketplace
                ],
                'timestamp' => time()
            ];
        }
        
        if (rand(1, 10) > 8) {
            $events[] = [
                'type' => 'stock_alert',
                'data' => [
                    'product_id' => 'PRD' . rand(1000, 9999),
                    'current_stock' => rand(0, 5),
                    'threshold' => 10,
                    'marketplace' => $marketplace
                ],
                'timestamp' => time()
            ];
        }
        
        return $events;
    }
    
    /**
     * Collect metrics for all marketplaces
     */
    private function collectAllMarketplaceMetrics($type) {
        $all_metrics = [];
        $marketplaces = ['amazon', 'ebay', 'trendyol', 'n11', 'hepsiburada', 'ozon'];
        
        foreach ($marketplaces as $marketplace) {
            $all_metrics[$marketplace] = $this->getMarketplaceData($marketplace, $type);
        }
        
        // Add summary metrics
        $all_metrics['summary'] = [
            'total_orders_today' => array_sum(array_column($all_metrics, 'orders_today')),
            'total_sales_today' => array_sum(array_column($all_metrics, 'sales_today')),
            'total_active_products' => array_sum(array_column($all_metrics, 'active_products')),
            'connected_marketplaces' => count(array_filter($all_metrics, function($data) {
                return $data['connection_status'] === 'connected';
            }))
        ];
        
        return $all_metrics;
    }
    
    /**
     * Collect updates for all marketplaces
     */
    private function collectAllMarketplaceUpdates() {
        $all_updates = [];
        $marketplaces = ['amazon', 'ebay', 'trendyol', 'n11', 'hepsiburada', 'ozon'];
        
        foreach ($marketplaces as $marketplace) {
            $updates = $this->fetchRealtimeData($marketplace);
            if (!empty($updates)) {
                $all_updates[$marketplace] = $updates;
            }
        }
        
        return $all_updates;
    }
}

// Start the WebSocket server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new MeschainWebSocketServer()
        )
    ),
    8080
);

// Set up periodic broadcasting
$loop = $server->loop;
$loop->addPeriodicTimer(2, function() use ($server) {
    // Get the WebSocket component
    $wsComponent = $server->app->app->component;
    if (method_exists($wsComponent, 'broadcastUpdates')) {
        $wsComponent->broadcastUpdates();
    }
});

echo "MesChain WebSocket Server running on localhost:8080\n";
$server->run();
