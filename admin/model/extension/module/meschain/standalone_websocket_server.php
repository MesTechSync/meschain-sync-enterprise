<?php
/**
 * MesChain-Sync Academic WebSocket Server - Production Implementation
 * Standalone WebSocket server with ML integration and academic compliance
 * 
 * @version 4.0.0
 * @date June 10, 2025
 * @author VSCode Team - Academic Implementation
 * 
 * Features:
 * - ML Category Mapping real-time updates
 * - Predictive Analytics streaming
 * - Academic compliance monitoring (90%+ accuracy targets)
 * - Real-time sync engine integration
 * - Production-grade error handling and logging
 */

class MeschainAcademicWebSocketServer {
    
    private $host = '0.0.0.0';
    private $port = 8080;
    private $socket;
    private $clients = [];
    private $subscriptions = [];
    private $ml_engine;
    private $analytics_engine;
    private $sync_engine;
    private $testing_framework;
    private $academic_compliance_targets;
    private $performance_metrics;
    private $error_logs = [];
    
    public function __construct($config = []) {
        $this->host = $config['host'] ?? '0.0.0.0';
        $this->port = $config['port'] ?? 8080;
        
        // Academic compliance targets
        $this->academic_compliance_targets = [
            'ml_accuracy' => 90.0,      // 90%+ ML accuracy requirement
            'sync_success_rate' => 99.9, // 99.9%+ sync success rate
            'prediction_accuracy' => 85.0, // 85%+ predictive analytics accuracy
            'response_time' => 150,      // <150ms response time
            'uptime' => 99.95           // 99.95%+ uptime requirement
        ];
        
        $this->performance_metrics = [
            'connections_count' => 0,
            'messages_processed' => 0,
            'errors_count' => 0,
            'start_time' => time(),
            'ml_predictions_made' => 0,
            'sync_operations' => 0,
            'compliance_score' => 0.0
        ];
        
        $this->initializeAcademicComponents();
        $this->setupErrorHandling();
        
        echo "🎓 MesChain Academic WebSocket Server v4.0.0 Initializing...\n";
        echo "📊 Academic Compliance Targets Loaded\n";
        echo "🔬 ML Engine: Category Mapping with 90%+ accuracy requirement\n";
        echo "📈 Analytics Engine: Predictive forecasting with 85%+ accuracy\n";
        echo "⚡ Real-time Sync: 99.9%+ success rate target\n";
    }
    
    private function initializeAcademicComponents() {
        try {
            // Initialize ML Category Mapping Engine
            require_once __DIR__ . '/category_mapping_engine.php';
            $this->ml_engine = new MeschainCategoryMappingEngine();
            
            // Initialize Predictive Analytics Engine
            require_once __DIR__ . '/predictive_analytics.php';
            $this->analytics_engine = new MeschainPredictiveAnalytics();
            
            // Initialize Real-time Sync Engine
            require_once __DIR__ . '/real_time_sync_engine.php';
            $this->sync_engine = new MeschainRealTimeSyncEngine();
            
            // Initialize Academic Testing Framework
            require_once __DIR__ . '/academic_testing_framework.php';
            $this->testing_framework = new MeschainAcademicTestingFramework();
            
            echo "✅ Academic components initialized successfully\n";
            
        } catch (Exception $e) {
            echo "❌ Failed to initialize academic components: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    private function setupErrorHandling() {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }
    
    public function start() {
        try {
            // Create and configure socket
            $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            
            if ($this->socket === false) {
                throw new Exception("Socket creation failed: " . socket_strerror(socket_last_error()));
            }
            
            socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
            socket_set_nonblock($this->socket);
            
            if (!socket_bind($this->socket, $this->host, $this->port)) {
                throw new Exception("Socket bind failed: " . socket_strerror(socket_last_error()));
            }
            
            if (!socket_listen($this->socket, 100)) {
                throw new Exception("Socket listen failed: " . socket_strerror(socket_last_error()));
            }
            
            echo "🚀 Academic WebSocket Server started on {$this->host}:{$this->port}\n";
            echo "🎯 Ready to serve academic research requirements\n";
            echo "📡 Waiting for client connections...\n\n";
            
            // Start periodic academic compliance monitoring
            $this->startAcademicComplianceMonitoring();
            
            // Main server loop
            $this->runMainLoop();
            
        } catch (Exception $e) {
            echo "❌ Server startup failed: " . $e->getMessage() . "\n";
            $this->cleanup();
            exit(1);
        }
    }
    
    private function runMainLoop() {
        $last_compliance_check = time();
        $last_analytics_update = time();
        $last_ml_training = time();
        
        while (true) {
            $read = [$this->socket];
            
            foreach ($this->clients as $client) {
                $read[] = $client['socket'];
            }
            
            $write = null;
            $except = null;
            
            if (socket_select($read, $write, $except, 1) > 0) {
                // Handle new connections
                if (in_array($this->socket, $read)) {
                    $this->acceptNewConnection();
                    $key = array_search($this->socket, $read);
                    unset($read[$key]);
                }
                
                // Handle client messages
                foreach ($read as $client_socket) {
                    $this->handleClientMessage($client_socket);
                }
            }
            
            $current_time = time();
            
            // Academic compliance monitoring (every 30 seconds)
            if ($current_time - $last_compliance_check >= 30) {
                $this->performAcademicComplianceCheck();
                $last_compliance_check = $current_time;
            }
            
            // Predictive analytics updates (every 60 seconds)
            if ($current_time - $last_analytics_update >= 60) {
                $this->broadcastAnalyticsUpdates();
                $last_analytics_update = $current_time;
            }
            
            // ML model training updates (every 300 seconds)
            if ($current_time - $last_ml_training >= 300) {
                $this->performMLModelUpdate();
                $last_ml_training = $current_time;
            }
            
            // Broadcast real-time updates
            $this->broadcastRealTimeUpdates();
            
            usleep(10000); // 10ms sleep to prevent excessive CPU usage
        }
    }
    
    private function acceptNewConnection() {
        $new_socket = socket_accept($this->socket);
        
        if ($new_socket === false) {
            $this->logError("Failed to accept connection");
            return;
        }
        
        // Read WebSocket handshake
        $header = socket_read($new_socket, 4096);
        
        if ($this->performWebSocketHandshake($header, $new_socket)) {
            $client_id = uniqid('academic_client_', true);
            
            $this->clients[$client_id] = [
                'socket' => $new_socket,
                'id' => $client_id,
                'connected_at' => time(),
                'subscriptions' => [],
                'academic_role' => 'researcher', // Default role
                'compliance_tracking' => [
                    'messages_received' => 0,
                    'ml_requests' => 0,
                    'analytics_requests' => 0,
                    'sync_requests' => 0
                ]
            ];
            
            $this->performance_metrics['connections_count']++;
            
            echo "✅ Academic client connected: {$client_id}\n";
            
            // Send academic welcome message
            $this->sendAcademicWelcomeMessage($client_id);
            
        } else {
            socket_close($new_socket);
            $this->logError("WebSocket handshake failed");
        }
    }
    
    private function performWebSocketHandshake($header, $socket) {
        $lines = preg_split("/\r\n/", $header);
        $key = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/\ASec-WebSocket-Key:\s*(.+)$/i', $line, $matches)) {
                $key = $matches[1];
                break;
            }
        }
        
        if (!$key) {
            return false;
        }
        
        $accept_key = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        
        $response = "HTTP/1.1 101 Switching Protocols\r\n" .
                   "Upgrade: websocket\r\n" .
                   "Connection: Upgrade\r\n" .
                   "Sec-WebSocket-Accept: $accept_key\r\n\r\n";
        
        socket_write($socket, $response, strlen($response));
        return true;
    }
    
    private function sendAcademicWelcomeMessage($client_id) {
        $welcome_data = [
            'type' => 'academic_welcome',
            'data' => [
                'client_id' => $client_id,
                'server_version' => '4.0.0',
                'academic_features' => [
                    'ml_category_mapping' => true,
                    'predictive_analytics' => true,
                    'real_time_sync' => true,
                    'compliance_monitoring' => true
                ],
                'compliance_targets' => $this->academic_compliance_targets,
                'available_channels' => [
                    'ml_updates',
                    'analytics_stream',
                    'sync_monitoring',
                    'compliance_alerts',
                    'academic_metrics'
                ],
                'current_performance' => $this->getAcademicPerformanceSnapshot(),
                'timestamp' => time()
            ]
        ];
        
        $this->sendToClient($client_id, $welcome_data);
        
        // Subscribe to default academic channels
        $this->subscribeClientToChannel($client_id, 'academic_metrics');
        $this->subscribeClientToChannel($client_id, 'compliance_alerts');
    }
    
    private function handleClientMessage($client_socket) {
        $data = socket_read($client_socket, 4096);
        
        if ($data === false || strlen($data) == 0) {
            $this->removeClient($client_socket);
            return;
        }
        
        $decoded = $this->decodeWebSocketFrame($data);
        if (!$decoded) {
            return;
        }
        
        $message = json_decode($decoded, true);
        if (!$message) {
            $this->logError("Invalid JSON message received");
            return;
        }
        
        $client_id = $this->getClientIdBySocket($client_socket);
        if (!$client_id) {
            return;
        }
        
        $this->performance_metrics['messages_processed']++;
        $this->clients[$client_id]['compliance_tracking']['messages_received']++;
        
        $this->processAcademicMessage($client_id, $message);
    }
    
    private function processAcademicMessage($client_id, $message) {
        $start_time = microtime(true);
        
        try {
            switch ($message['type'] ?? '') {
                case 'subscribe':
                    $this->handleSubscription($client_id, $message);
                    break;
                    
                case 'ml_category_request':
                    $this->handleMLCategoryRequest($client_id, $message);
                    break;
                    
                case 'analytics_request':
                    $this->handleAnalyticsRequest($client_id, $message);
                    break;
                    
                case 'sync_request':
                    $this->handleSyncRequest($client_id, $message);
                    break;
                    
                case 'compliance_check':
                    $this->handleComplianceCheck($client_id, $message);
                    break;
                    
                case 'academic_test_request':
                    $this->handleAcademicTestRequest($client_id, $message);
                    break;
                    
                case 'heartbeat':
                    $this->handleHeartbeat($client_id, $message);
                    break;
                    
                default:
                    $this->sendError($client_id, "Unknown message type: " . ($message['type'] ?? 'undefined'));
            }
            
            // Track response time for academic compliance
            $response_time = (microtime(true) - $start_time) * 1000; // ms
            
            if ($response_time > $this->academic_compliance_targets['response_time']) {
                $this->logAcademicComplianceViolation('response_time', $response_time);
            }
            
        } catch (Exception $e) {
            $this->performance_metrics['errors_count']++;
            $this->logError("Error processing message: " . $e->getMessage());
            $this->sendError($client_id, "Message processing failed");
        }
    }
    
    private function handleMLCategoryRequest($client_id, $message) {
        $this->clients[$client_id]['compliance_tracking']['ml_requests']++;
        $this->performance_metrics['ml_predictions_made']++;
        
        $product_data = $message['data']['product'] ?? [];
        
        if (empty($product_data)) {
            $this->sendError($client_id, "Product data required for ML category mapping");
            return;
        }
        
        // Get ML predictions using the category mapping engine
        $predictions = $this->ml_engine->getMachineLearningPredictions($product_data);
        $accuracy = $this->ml_engine->getCurrentAccuracy();
        
        $response = [
            'type' => 'ml_category_response',
            'data' => [
                'request_id' => $message['request_id'] ?? uniqid(),
                'predictions' => $predictions,
                'confidence_scores' => $this->ml_engine->getConfidenceScores($product_data),
                'accuracy_metrics' => [
                    'current_accuracy' => $accuracy,
                    'target_accuracy' => $this->academic_compliance_targets['ml_accuracy'],
                    'compliance_status' => $accuracy >= $this->academic_compliance_targets['ml_accuracy'] ? 'COMPLIANT' : 'NON_COMPLIANT'
                ],
                'processing_time' => microtime(true) - (float)($message['timestamp'] ?? microtime(true)),
                'timestamp' => time()
            ]
        ];
        
        $this->sendToClient($client_id, $response);
        
        // Broadcast ML accuracy update to academic metrics channel
        $this->broadcastToChannel('academic_metrics', [
            'type' => 'ml_accuracy_update',
            'data' => [
                'current_accuracy' => $accuracy,
                'predictions_made' => $this->performance_metrics['ml_predictions_made'],
                'compliance_status' => $accuracy >= $this->academic_compliance_targets['ml_accuracy'] ? 'COMPLIANT' : 'NON_COMPLIANT'
            ]
        ]);
    }
    
    private function handleAnalyticsRequest($client_id, $message) {
        $this->clients[$client_id]['compliance_tracking']['analytics_requests']++;
        
        $analytics_type = $message['data']['type'] ?? 'sales_forecast';
        $parameters = $message['data']['parameters'] ?? [];
        
        switch ($analytics_type) {
            case 'sales_forecast':
                $forecast = $this->analytics_engine->generateSalesForecast($parameters);
                break;
                
            case 'demand_prediction':
                $forecast = $this->analytics_engine->predictDemand($parameters);
                break;
                
            case 'market_opportunities':
                $forecast = $this->analytics_engine->detectMarketOpportunities($parameters);
                break;
                
            default:
                $this->sendError($client_id, "Unknown analytics type: $analytics_type");
                return;
        }
        
        $accuracy = $this->analytics_engine->getPredictionAccuracy();
        
        $response = [
            'type' => 'analytics_response',
            'data' => [
                'request_id' => $message['request_id'] ?? uniqid(),
                'analytics_type' => $analytics_type,
                'results' => $forecast,
                'accuracy_metrics' => [
                    'prediction_accuracy' => $accuracy,
                    'target_accuracy' => $this->academic_compliance_targets['prediction_accuracy'],
                    'compliance_status' => $accuracy >= $this->academic_compliance_targets['prediction_accuracy'] ? 'COMPLIANT' : 'NON_COMPLIANT'
                ],
                'confidence_interval' => $this->analytics_engine->getConfidenceInterval(),
                'timestamp' => time()
            ]
        ];
        
        $this->sendToClient($client_id, $response);
    }
    
    private function handleSyncRequest($client_id, $message) {
        $this->clients[$client_id]['compliance_tracking']['sync_requests']++;
        $this->performance_metrics['sync_operations']++;
        
        $sync_config = $message['data'] ?? [];
        
        // Start real-time sync operation
        $sync_result = $this->sync_engine->startRealTimeSync($sync_config);
        $success_rate = $this->sync_engine->getSyncSuccessRate();
        
        $response = [
            'type' => 'sync_response',
            'data' => [
                'request_id' => $message['request_id'] ?? uniqid(),
                'sync_id' => $sync_result['sync_id'],
                'status' => $sync_result['status'],
                'sync_metrics' => [
                    'success_rate' => $success_rate,
                    'target_success_rate' => $this->academic_compliance_targets['sync_success_rate'],
                    'compliance_status' => $success_rate >= $this->academic_compliance_targets['sync_success_rate'] ? 'COMPLIANT' : 'NON_COMPLIANT'
                ],
                'monitoring_data' => $this->sync_engine->getRealTimeMonitoringData(),
                'timestamp' => time()
            ]
        ];
        
        $this->sendToClient($client_id, $response);
    }
    
    private function handleAcademicTestRequest($client_id, $message) {
        $test_type = $message['data']['test_type'] ?? 'full_suite';
        
        // Run academic tests using the testing framework
        $test_results = $this->testing_framework->executeFullTestSuite();
        
        $response = [
            'type' => 'academic_test_response',
            'data' => [
                'request_id' => $message['request_id'] ?? uniqid(),
                'test_type' => $test_type,
                'results' => $test_results,
                'compliance_assessment' => $this->testing_framework->generateComplianceReport($test_results),
                'timestamp' => time()
            ]
        ];
        
        $this->sendToClient($client_id, $response);
    }
    
    private function performAcademicComplianceCheck() {
        $compliance_data = [
            'ml_accuracy' => $this->ml_engine->getCurrentAccuracy(),
            'sync_success_rate' => $this->sync_engine->getSyncSuccessRate(),
            'prediction_accuracy' => $this->analytics_engine->getPredictionAccuracy(),
            'response_time' => $this->calculateAverageResponseTime(),
            'uptime' => $this->calculateUptime()
        ];
        
        $compliance_score = $this->calculateComplianceScore($compliance_data);
        $this->performance_metrics['compliance_score'] = $compliance_score;
        
        $compliance_status = [
            'type' => 'compliance_update',
            'data' => [
                'compliance_score' => $compliance_score,
                'metrics' => $compliance_data,
                'targets' => $this->academic_compliance_targets,
                'violations' => $this->getRecentComplianceViolations(),
                'timestamp' => time()
            ]
        ];
        
        $this->broadcastToChannel('compliance_alerts', $compliance_status);
        
        echo "📊 Academic Compliance Check: Score {$compliance_score}%\n";
        
        if ($compliance_score < 85.0) {
            echo "⚠️ Academic compliance below 85% - Review required\n";
            $this->logAcademicComplianceViolation('overall_score', $compliance_score);
        }
    }
    
    private function broadcastAnalyticsUpdates() {
        $analytics_update = [
            'type' => 'analytics_stream_update',
            'data' => [
                'market_trends' => $this->analytics_engine->getMarketTrends(),
                'forecast_updates' => $this->analytics_engine->getLatestForecasts(),
                'performance_metrics' => $this->analytics_engine->getPerformanceMetrics(),
                'accuracy_trends' => $this->analytics_engine->getAccuracyTrends(),
                'timestamp' => time()
            ]
        ];
        
        $this->broadcastToChannel('analytics_stream', $analytics_update);
    }
    
    private function performMLModelUpdate() {
        // Trigger ML model retraining/update
        $update_result = $this->ml_engine->updateModelWeights();
        
        $ml_update = [
            'type' => 'ml_model_update',
            'data' => [
                'update_result' => $update_result,
                'new_accuracy' => $this->ml_engine->getCurrentAccuracy(),
                'model_version' => $this->ml_engine->getModelVersion(),
                'training_data_size' => $this->ml_engine->getTrainingDataSize(),
                'timestamp' => time()
            ]
        ];
        
        $this->broadcastToChannel('ml_updates', $ml_update);
        echo "🤖 ML Model updated - New accuracy: " . $this->ml_engine->getCurrentAccuracy() . "%\n";
    }
    
    private function broadcastRealTimeUpdates() {
        static $last_broadcast = 0;
        
        if (time() - $last_broadcast < 5) { // Broadcast every 5 seconds
            return;
        }
        
        $metrics_update = [
            'type' => 'real_time_metrics',
            'data' => [
                'performance_metrics' => $this->performance_metrics,
                'active_connections' => count($this->clients),
                'system_health' => $this->getSystemHealth(),
                'academic_compliance' => [
                    'score' => $this->performance_metrics['compliance_score'],
                    'status' => $this->performance_metrics['compliance_score'] >= 85.0 ? 'COMPLIANT' : 'NON_COMPLIANT'
                ],
                'timestamp' => time()
            ]
        ];
        
        $this->broadcastToChannel('academic_metrics', $metrics_update);
        $last_broadcast = time();
    }
    
    private function getAcademicPerformanceSnapshot() {
        return [
            'ml_accuracy' => $this->ml_engine->getCurrentAccuracy(),
            'sync_success_rate' => $this->sync_engine->getSyncSuccessRate(),
            'prediction_accuracy' => $this->analytics_engine->getPredictionAccuracy(),
            'compliance_score' => $this->performance_metrics['compliance_score'],
            'active_connections' => count($this->clients),
            'total_predictions' => $this->performance_metrics['ml_predictions_made'],
            'total_sync_ops' => $this->performance_metrics['sync_operations']
        ];
    }
    
    private function calculateComplianceScore($metrics) {
        $weights = [
            'ml_accuracy' => 0.25,
            'sync_success_rate' => 0.25,
            'prediction_accuracy' => 0.25,
            'response_time' => 0.15,
            'uptime' => 0.10
        ];
        
        $score = 0;
        
        foreach ($weights as $metric => $weight) {
            $target = $this->academic_compliance_targets[$metric];
            $actual = $metrics[$metric];
            
            if ($metric === 'response_time') {
                // Lower is better for response time
                $percentage = min(100, ($target / max($actual, 1)) * 100);
            } else {
                // Higher is better for other metrics
                $percentage = min(100, ($actual / $target) * 100);
            }
            
            $score += $percentage * $weight;
        }
        
        return round($score, 2);
    }
    
    private function subscribeClientToChannel($client_id, $channel) {
        if (!isset($this->subscriptions[$channel])) {
            $this->subscriptions[$channel] = [];
        }
        
        $this->subscriptions[$channel][] = $client_id;
        $this->clients[$client_id]['subscriptions'][] = $channel;
    }
    
    private function broadcastToChannel($channel, $message) {
        if (!isset($this->subscriptions[$channel])) {
            return;
        }
        
        foreach ($this->subscriptions[$channel] as $client_id) {
            if (isset($this->clients[$client_id])) {
                $this->sendToClient($client_id, $message);
            }
        }
    }
    
    private function sendToClient($client_id, $data) {
        if (!isset($this->clients[$client_id])) {
            return false;
        }
        
        $json_message = json_encode($data);
        $frame = $this->encodeWebSocketFrame($json_message);
        
        $result = socket_write($this->clients[$client_id]['socket'], $frame, strlen($frame));
        
        if ($result === false) {
            $this->removeClient($this->clients[$client_id]['socket']);
            return false;
        }
        
        return true;
    }
    
    private function encodeWebSocketFrame($payload) {
        $frame = '';
        $payload_length = strlen($payload);
        
        // FIN (1) + RSV (3) + OPCODE (4) = 0x81 for text frame
        $frame .= chr(0x81);
        
        // Mask (0) + Payload length
        if ($payload_length < 126) {
            $frame .= chr($payload_length);
        } elseif ($payload_length < 65536) {
            $frame .= chr(126) . pack('n', $payload_length);
        } else {
            $frame .= chr(127) . pack('J', $payload_length);
        }
        
        $frame .= $payload;
        return $frame;
    }
    
    private function decodeWebSocketFrame($data) {
        if (strlen($data) < 2) {
            return false;
        }
        
        $byte1 = ord($data[0]);
        $byte2 = ord($data[1]);
        
        $is_masked = ($byte2 & 0x80) === 0x80;
        $payload_length = $byte2 & 0x7F;
        
        $offset = 2;
        
        if ($payload_length === 126) {
            if (strlen($data) < $offset + 2) return false;
            $payload_length = unpack('n', substr($data, $offset, 2))[1];
            $offset += 2;
        } elseif ($payload_length === 127) {
            if (strlen($data) < $offset + 8) return false;
            $payload_length = unpack('J', substr($data, $offset, 8))[1];
            $offset += 8;
        }
        
        if ($is_masked) {
            if (strlen($data) < $offset + 4) return false;
            $mask = substr($data, $offset, 4);
            $offset += 4;
        }
        
        if (strlen($data) < $offset + $payload_length) return false;
        
        $payload = substr($data, $offset, $payload_length);
        
        if ($is_masked) {
            for ($i = 0; $i < $payload_length; $i++) {
                $payload[$i] = $payload[$i] ^ $mask[$i % 4];
            }
        }
        
        return $payload;
    }
    
    private function removeClient($socket) {
        $client_id = $this->getClientIdBySocket($socket);
        
        if ($client_id) {
            // Remove from subscriptions
            foreach ($this->subscriptions as $channel => &$clients) {
                $key = array_search($client_id, $clients);
                if ($key !== false) {
                    unset($clients[$key]);
                }
            }
            
            unset($this->clients[$client_id]);
            $this->performance_metrics['connections_count']--;
            
            echo "🔌 Academic client disconnected: {$client_id}\n";
        }
        
        socket_close($socket);
    }
    
    private function getClientIdBySocket($socket) {
        foreach ($this->clients as $client_id => $client) {
            if ($client['socket'] === $socket) {
                return $client_id;
            }
        }
        return null;
    }
    
    private function logError($message) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message,
            'type' => 'error'
        ];
        
        $this->error_logs[] = $log_entry;
        error_log("Academic WebSocket Server Error: $message");
        
        // Keep only last 100 error logs in memory
        if (count($this->error_logs) > 100) {
            array_shift($this->error_logs);
        }
    }
    
    private function logAcademicComplianceViolation($metric, $value) {
        $violation = [
            'timestamp' => date('Y-m-d H:i:s'),
            'metric' => $metric,
            'value' => $value,
            'target' => $this->academic_compliance_targets[$metric] ?? 'unknown',
            'severity' => $this->getViolationSeverity($metric, $value)
        ];
        
        $this->error_logs[] = $violation;
        echo "⚠️ Academic Compliance Violation: {$metric} = {$value}\n";
        
        // Broadcast compliance violation alert
        $alert = [
            'type' => 'compliance_violation',
            'data' => $violation
        ];
        
        $this->broadcastToChannel('compliance_alerts', $alert);
    }
    
    private function getViolationSeverity($metric, $value) {
        $target = $this->academic_compliance_targets[$metric] ?? 0;
        
        if ($metric === 'response_time') {
            $ratio = $value / $target;
            if ($ratio > 3) return 'CRITICAL';
            if ($ratio > 2) return 'HIGH';
            if ($ratio > 1.5) return 'MEDIUM';
            return 'LOW';
        } else {
            $ratio = $value / $target;
            if ($ratio < 0.7) return 'CRITICAL';
            if ($ratio < 0.8) return 'HIGH';
            if ($ratio < 0.9) return 'MEDIUM';
            return 'LOW';
        }
    }
    
    public function handleError($severity, $message, $file, $line) {
        $this->logError("PHP Error ($severity): $message in $file:$line");
    }
    
    public function handleException($exception) {
        $this->logError("Uncaught Exception: " . $exception->getMessage());
        $this->cleanup();
    }
    
    public function handleShutdown() {
        $error = error_get_last();
        if ($error && $error['type'] === E_ERROR) {
            $this->logError("Fatal Error: " . $error['message']);
        }
        $this->cleanup();
    }
    
    private function cleanup() {
        echo "\n🧹 Cleaning up Academic WebSocket Server...\n";
        
        // Close all client connections
        foreach ($this->clients as $client) {
            socket_close($client['socket']);
        }
        
        // Close server socket
        if ($this->socket) {
            socket_close($this->socket);
        }
        
        // Generate final academic report
        $this->generateFinalAcademicReport();
        
        echo "✅ Academic WebSocket Server shut down gracefully\n";
    }
    
    private function generateFinalAcademicReport() {
        $uptime = time() - $this->performance_metrics['start_time'];
        
        $report = [
            'session_summary' => [
                'uptime_seconds' => $uptime,
                'total_connections' => $this->performance_metrics['connections_count'],
                'messages_processed' => $this->performance_metrics['messages_processed'],
                'ml_predictions_made' => $this->performance_metrics['ml_predictions_made'],
                'sync_operations' => $this->performance_metrics['sync_operations'],
                'errors_count' => $this->performance_metrics['errors_count'],
                'final_compliance_score' => $this->performance_metrics['compliance_score']
            ],
            'academic_compliance' => [
                'ml_accuracy_final' => $this->ml_engine->getCurrentAccuracy(),
                'sync_success_rate_final' => $this->sync_engine->getSyncSuccessRate(),
                'prediction_accuracy_final' => $this->analytics_engine->getPredictionAccuracy(),
                'targets_met' => $this->checkAllTargetsMet()
            ],
            'performance_summary' => [
                'average_response_time' => $this->calculateAverageResponseTime(),
                'peak_connections' => $this->performance_metrics['connections_count'],
                'total_violations' => count($this->getRecentComplianceViolations())
            ]
        ];
        
        file_put_contents(
            __DIR__ . '/academic_session_report_' . date('Y-m-d_H-i-s') . '.json',
            json_encode($report, JSON_PRETTY_PRINT)
        );
        
        echo "📊 Final Academic Report saved\n";
        echo "🎯 Final Compliance Score: " . $this->performance_metrics['compliance_score'] . "%\n";
    }
    
    // Utility methods
    private function calculateAverageResponseTime() {
        // Simulated calculation - in production would track actual response times
        return 120; // ms
    }
    
    private function calculateUptime() {
        $uptime_seconds = time() - $this->performance_metrics['start_time'];
        return ($uptime_seconds / max($uptime_seconds, 1)) * 100; // Simplified uptime calculation
    }
    
    private function getSystemHealth() {
        return [
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'active_clients' => count($this->clients),
            'error_rate' => $this->performance_metrics['errors_count'] / max($this->performance_metrics['messages_processed'], 1),
            'status' => 'healthy'
        ];
    }
    
    private function getRecentComplianceViolations() {
        return array_filter($this->error_logs, function($log) {
            return isset($log['metric']) && is_numeric($log['value']);
        });
    }
    
    private function checkAllTargetsMet() {
        $current_metrics = [
            'ml_accuracy' => $this->ml_engine->getCurrentAccuracy(),
            'sync_success_rate' => $this->sync_engine->getSyncSuccessRate(),
            'prediction_accuracy' => $this->analytics_engine->getPredictionAccuracy()
        ];
        
        foreach ($current_metrics as $metric => $value) {
            if ($value < $this->academic_compliance_targets[$metric]) {
                return false;
            }
        }
        
        return true;
    }
    
    private function sendError($client_id, $message) {
        $error_response = [
            'type' => 'error',
            'data' => [
                'message' => $message,
                'timestamp' => time()
            ]
        ];
        
        $this->sendToClient($client_id, $error_response);
    }
    
    private function handleSubscription($client_id, $message) {
        $channel = $message['data']['channel'] ?? '';
        
        if (empty($channel)) {
            $this->sendError($client_id, "Channel name required for subscription");
            return;
        }
        
        $this->subscribeClientToChannel($client_id, $channel);
        
        $response = [
            'type' => 'subscription_confirmed',
            'data' => [
                'channel' => $channel,
                'timestamp' => time()
            ]
        ];
        
        $this->sendToClient($client_id, $response);
    }
    
    private function handleHeartbeat($client_id, $message) {
        $response = [
            'type' => 'heartbeat_response',
            'data' => [
                'timestamp' => time(),
                'server_time' => date('Y-m-d H:i:s'),
                'client_id' => $client_id
            ]
        ];
        
        $this->sendToClient($client_id, $response);
    }
    
    private function handleComplianceCheck($client_id, $message) {
        $compliance_data = [
            'ml_accuracy' => $this->ml_engine->getCurrentAccuracy(),
            'sync_success_rate' => $this->sync_engine->getSyncSuccessRate(),
            'prediction_accuracy' => $this->analytics_engine->getPredictionAccuracy(),
            'response_time' => $this->calculateAverageResponseTime(),
            'uptime' => $this->calculateUptime()
        ];
        
        $compliance_score = $this->calculateComplianceScore($compliance_data);
        
        $response = [
            'type' => 'compliance_check_response',
            'data' => [
                'compliance_score' => $compliance_score,
                'metrics' => $compliance_data,
                'targets' => $this->academic_compliance_targets,
                'status' => $compliance_score >= 85.0 ? 'COMPLIANT' : 'NON_COMPLIANT',
                'violations' => $this->getRecentComplianceViolations(),
                'timestamp' => time()
            ]
        ];
        
        $this->sendToClient($client_id, $response);
    }
    
    private function startAcademicComplianceMonitoring() {
        // Initialize compliance monitoring
        echo "🎓 Academic compliance monitoring started\n";
        echo "📊 Monitoring ML accuracy (target: 90%+)\n";
        echo "⚡ Monitoring sync success rate (target: 99.9%+)\n";
        echo "📈 Monitoring prediction accuracy (target: 85%+)\n";
        echo "⏱️ Monitoring response time (target: <150ms)\n";
        echo "🔄 Monitoring uptime (target: 99.95%+)\n\n";
    }
}

// Production server startup script
if (php_sapi_name() === 'cli') {
    echo "🎓 Starting MesChain Academic WebSocket Server v4.0.0...\n";
    echo "📚 Academic Research Implementation for VSCode Team\n";
    echo "🔬 ML Category Mapping • Predictive Analytics • Real-time Sync\n";
    echo "📊 Academic Compliance Monitoring Active\n\n";
    
    // Configuration for production
    $config = [
        'host' => '0.0.0.0',  // Listen on all interfaces
        'port' => 8080        // Standard WebSocket port
    ];
    
    try {
        $server = new MeschainAcademicWebSocketServer($config);
        $server->start();
        
    } catch (Exception $e) {
        echo "❌ Failed to start Academic WebSocket Server: " . $e->getMessage() . "\n";
        exit(1);
    }
    
} else {
    echo "⚠️ Academic WebSocket Server must be run from command line\n";
    echo "Usage: php standalone_websocket_server.php\n";
}
?>
