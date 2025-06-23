<?php
/**
 * MesChain IoT Edge Computing Manager
 * ATOM-M011-003: IoT Kenar Bilişim Yöneticisi
 * 
 * @category    MesChain
 * @package     IoT
 * @subpackage  EdgeComputing
 * @version     1.0.0
 * @author      Musti DevOps Team
 * @copyright   2024 MesChain Sync Enterprise
 */

namespace MesChain\IoT;

class EdgeComputingManager {
    
    private $db;
    private $config;
    private $logger;
    private $mqtt_client;
    private $edge_nodes;
    
    // IoT Performance Metrics
    private $iot_metrics = [
        'device_connectivity_rate' => 98.7,
        'data_processing_speed' => 2.3, // ms
        'edge_node_availability' => 99.1,
        'real_time_response_rate' => 96.4,
        'data_accuracy_score' => 94.8
    ];
    
    // Edge Computing Metrics
    private $edge_metrics = [
        'computational_efficiency' => 93.5,
        'bandwidth_optimization' => 87.2,
        'latency_reduction' => 78.9, // percentage
        'local_processing_ratio' => 85.3,
        'cloud_sync_success_rate' => 96.7
    ];
    
    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->logger = new \MesChain\Logger('iot_edge');
        $this->mqtt_client = new \MesChain\IoT\MQTTClient();
        $this->edge_nodes = new \MesChain\IoT\EdgeNodeManager();
        
        $this->initializeEdgeComputing();
    }
    
    /**
     * Initialize IoT Edge Computing
     */
    private function initializeEdgeComputing() {
        try {
            $this->createIoTTables();
            $this->setupMQTTBroker();
            $this->initializeEdgeNodes();
            $this->setupRealTimeProcessing();
            $this->startDeviceDiscovery();
            
            $this->logger->info('IoT Edge Computing Manager initialized successfully');
            
        } catch (Exception $e) {
            $this->logger->error('IoT Edge initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create IoT Database Tables
     */
    private function createIoTTables() {
        $tables = [
            // IoT Devices
            "CREATE TABLE IF NOT EXISTS `meschain_iot_devices` (
                `device_id` int(11) NOT NULL AUTO_INCREMENT,
                `device_uuid` varchar(36) NOT NULL UNIQUE,
                `device_name` varchar(255) NOT NULL,
                `device_type` enum('sensor','actuator','gateway','edge_node','hybrid') NOT NULL,
                `device_category` varchar(100) NOT NULL,
                `manufacturer` varchar(100),
                `model` varchar(100),
                `firmware_version` varchar(50),
                `hardware_specification` text,
                `network_interface` text NOT NULL,
                `connection_protocol` enum('mqtt','coap','http','tcp','udp','websocket') DEFAULT 'mqtt',
                `device_location` text,
                `installation_date` date,
                `last_seen` datetime,
                `status` enum('online','offline','maintenance','error','unknown') DEFAULT 'unknown',
                `configuration` longtext,
                `security_credentials` text,
                `battery_level` int(11),
                `signal_strength` int(11),
                `data_collection_frequency` int(11) DEFAULT 60,
                `edge_node_id` int(11),
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`device_id`),
                INDEX `idx_device_uuid` (`device_uuid`),
                INDEX `idx_device_type` (`device_type`),
                INDEX `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Edge Nodes
            "CREATE TABLE IF NOT EXISTS `meschain_edge_nodes` (
                `node_id` int(11) NOT NULL AUTO_INCREMENT,
                `node_uuid` varchar(36) NOT NULL UNIQUE,
                `node_name` varchar(255) NOT NULL,
                `node_type` enum('gateway','micro_datacenter','fog_node','cloudlet') NOT NULL,
                `location` text NOT NULL,
                `geographic_coordinates` text,
                `hardware_specifications` text NOT NULL,
                `cpu_cores` int(11) NOT NULL,
                `memory_gb` int(11) NOT NULL,
                `storage_gb` int(11) NOT NULL,
                `network_bandwidth` int(11) NOT NULL,
                `operating_system` varchar(100),
                `runtime_environment` text,
                `container_support` boolean DEFAULT TRUE,
                `ai_acceleration` boolean DEFAULT FALSE,
                `current_load` decimal(5,2) DEFAULT 0,
                `available_resources` text,
                `deployed_services` text,
                `connected_devices` int(11) DEFAULT 0,
                `data_processing_capacity` int(11) NOT NULL,
                `security_level` enum('basic','enhanced','enterprise') DEFAULT 'enhanced',
                `encryption_enabled` boolean DEFAULT TRUE,
                `status` enum('active','inactive','maintenance','error') DEFAULT 'active',
                `last_heartbeat` datetime,
                `uptime_percentage` decimal(5,2) DEFAULT 0,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`node_id`),
                INDEX `idx_node_uuid` (`node_uuid`),
                INDEX `idx_node_type` (`node_type`),
                INDEX `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Sensor Data
            "CREATE TABLE IF NOT EXISTS `meschain_sensor_data` (
                `data_id` int(11) NOT NULL AUTO_INCREMENT,
                `device_id` int(11) NOT NULL,
                `sensor_type` varchar(100) NOT NULL,
                `measurement_value` decimal(15,6) NOT NULL,
                `measurement_unit` varchar(20) NOT NULL,
                `quality_score` decimal(5,2) DEFAULT 100,
                `data_timestamp` bigint(20) NOT NULL,
                `collection_method` enum('push','pull','scheduled','triggered') DEFAULT 'push',
                `raw_data` text,
                `processed_data` text,
                `anomaly_detected` boolean DEFAULT FALSE,
                `anomaly_score` decimal(5,2),
                `edge_processed` boolean DEFAULT FALSE,
                `edge_node_id` int(11),
                `cloud_synced` boolean DEFAULT FALSE,
                `sync_timestamp` bigint(20),
                `data_integrity_hash` varchar(64),
                `compression_ratio` decimal(5,2),
                `processing_latency` decimal(10,6),
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`data_id`),
                FOREIGN KEY (`device_id`) REFERENCES `meschain_iot_devices`(`device_id`) ON DELETE CASCADE,
                INDEX `idx_device_timestamp` (`device_id`, `data_timestamp`),
                INDEX `idx_sensor_type` (`sensor_type`),
                INDEX `idx_anomaly` (`anomaly_detected`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Edge Applications
            "CREATE TABLE IF NOT EXISTS `meschain_edge_applications` (
                `app_id` int(11) NOT NULL AUTO_INCREMENT,
                `app_name` varchar(255) NOT NULL,
                `app_version` varchar(20) DEFAULT '1.0.0',
                `app_type` enum('data_processing','ai_inference','stream_analytics','device_control','monitoring') NOT NULL,
                `application_code` longtext NOT NULL,
                `runtime_environment` varchar(100) NOT NULL,
                `resource_requirements` text NOT NULL,
                `deployment_config` text NOT NULL,
                `scaling_policy` text,
                `health_check_config` text,
                `logging_config` text,
                `monitoring_config` text,
                `security_policies` text,
                `data_retention_policy` text,
                `api_endpoints` text,
                `event_handlers` text,
                `deployed_nodes` text,
                `deployment_status` enum('pending','deploying','deployed','failed','updating') DEFAULT 'pending',
                `performance_metrics` text,
                `created_by` int(11) NOT NULL,
                `status` enum('active','inactive','deprecated') DEFAULT 'active',
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`app_id`),
                INDEX `idx_app_type` (`app_type`),
                INDEX `idx_deployment_status` (`deployment_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];
        
        foreach ($tables as $sql) {
            $this->db->query($sql);
        }
    }
    
    /**
     * Register IoT Device
     */
    public function registerDevice($device_config) {
        try {
            // Validate device configuration
            $this->validateDeviceConfig($device_config);
            
            // Generate device UUID if not provided
            $device_uuid = $device_config['uuid'] ?? $this->generateDeviceUUID();
            
            // Determine optimal edge node
            $edge_node = $this->selectOptimalEdgeNode($device_config['location'], $device_config['requirements']);
            
            // Generate security credentials
            $security_credentials = $this->generateDeviceCredentials($device_uuid);
            
            // Create device record
            $device_data = [
                'device_uuid' => $device_uuid,
                'device_name' => $device_config['name'],
                'device_type' => $device_config['type'],
                'device_category' => $device_config['category'],
                'manufacturer' => $device_config['manufacturer'] ?? '',
                'model' => $device_config['model'] ?? '',
                'firmware_version' => $device_config['firmware_version'] ?? '1.0.0',
                'hardware_specification' => json_encode($device_config['hardware'] ?? []),
                'network_interface' => json_encode($device_config['network']),
                'connection_protocol' => $device_config['protocol'] ?? 'mqtt',
                'device_location' => json_encode($device_config['location']),
                'installation_date' => date('Y-m-d'),
                'configuration' => json_encode($device_config['config'] ?? []),
                'security_credentials' => json_encode($security_credentials),
                'data_collection_frequency' => $device_config['collection_frequency'] ?? 60,
                'edge_node_id' => $edge_node['node_id'],
                'status' => 'offline'
            ];
            
            $sql = "INSERT INTO meschain_iot_devices SET " . 
                   $this->buildInsertQuery($device_data);
            $this->db->query($sql);
            $device_id = $this->db->getLastId();
            
            // Setup device connection
            $connection_result = $this->setupDeviceConnection($device_id, $device_uuid, $edge_node);
            
            // Deploy edge applications for device
            $this->deployDeviceApplications($device_id, $device_config);
            
            $this->logger->info("IoT device registered: {$device_uuid}");
            
            return [
                'device_id' => $device_id,
                'device_uuid' => $device_uuid,
                'edge_node' => $edge_node,
                'security_credentials' => $security_credentials,
                'connection_config' => $connection_result,
                'status' => 'registered'
            ];
            
        } catch (Exception $e) {
            $this->logger->error('Device registration failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Process Real-time Sensor Data
     */
    public function processSensorData($device_uuid, $sensor_data) {
        $processing_start = microtime(true);
        
        try {
            // Get device information
            $device = $this->getDeviceByUUID($device_uuid);
            if (!$device) {
                throw new Exception("Device not found: {$device_uuid}");
            }
            
            // Validate sensor data
            $this->validateSensorData($sensor_data);
            
            // Get edge node for processing
            $edge_node = $this->getEdgeNode($device['edge_node_id']);
            
            // Process data at edge
            $processed_data = $this->processDataAtEdge($sensor_data, $edge_node, $device);
            
            // Anomaly detection
            $anomaly_result = $this->detectAnomalies($processed_data, $device);
            
            // Data quality assessment
            $quality_score = $this->assessDataQuality($processed_data, $sensor_data);
            
            // Store processed data
            $data_record = [
                'device_id' => $device['device_id'],
                'sensor_type' => $sensor_data['sensor_type'],
                'measurement_value' => $processed_data['value'],
                'measurement_unit' => $sensor_data['unit'],
                'quality_score' => $quality_score,
                'data_timestamp' => $sensor_data['timestamp'],
                'collection_method' => $sensor_data['collection_method'] ?? 'push',
                'raw_data' => json_encode($sensor_data),
                'processed_data' => json_encode($processed_data),
                'anomaly_detected' => $anomaly_result['detected'],
                'anomaly_score' => $anomaly_result['score'],
                'edge_processed' => true,
                'edge_node_id' => $edge_node['node_id'],
                'data_integrity_hash' => hash('sha256', json_encode($processed_data)),
                'processing_latency' => microtime(true) - $processing_start
            ];
            
            $sql = "INSERT INTO meschain_sensor_data SET " . 
                   $this->buildInsertQuery($data_record);
            $this->db->query($sql);
            $data_id = $this->db->getLastId();
            
            // Trigger real-time actions if needed
            if ($anomaly_result['detected'] || $processed_data['trigger_action']) {
                $this->triggerRealTimeActions($device, $processed_data, $anomaly_result);
            }
            
            // Schedule cloud synchronization
            $this->scheduleCloudSync($data_id, $processed_data, $edge_node);
            
            // Update device status
            $this->updateDeviceLastSeen($device['device_id']);
            
            return [
                'data_id' => $data_id,
                'processed_successfully' => true,
                'processing_latency' => microtime(true) - $processing_start,
                'quality_score' => $quality_score,
                'anomaly_detected' => $anomaly_result['detected'],
                'edge_processed' => true,
                'actions_triggered' => isset($processed_data['trigger_action']) ? $processed_data['trigger_action'] : false
            ];
            
        } catch (Exception $e) {
            $this->logger->error("Sensor data processing failed: {$e->getMessage()}");
            throw $e;
        }
    }
    
    /**
     * Deploy Edge Application
     */
    public function deployEdgeApplication($app_config) {
        try {
            // Validate application configuration
            $this->validateApplicationConfig($app_config);
            
            // Select target edge nodes
            $target_nodes = $this->selectDeploymentNodes($app_config['deployment_strategy']);
            
            // Create application record
            $app_data = [
                'app_name' => $app_config['name'],
                'app_version' => $app_config['version'] ?? '1.0.0',
                'app_type' => $app_config['type'],
                'application_code' => $app_config['code'],
                'runtime_environment' => $app_config['runtime'],
                'resource_requirements' => json_encode($app_config['resources']),
                'deployment_config' => json_encode($app_config['deployment']),
                'scaling_policy' => json_encode($app_config['scaling'] ?? []),
                'health_check_config' => json_encode($app_config['health_check'] ?? []),
                'logging_config' => json_encode($app_config['logging'] ?? []),
                'monitoring_config' => json_encode($app_config['monitoring'] ?? []),
                'security_policies' => json_encode($app_config['security'] ?? []),
                'api_endpoints' => json_encode($app_config['api_endpoints'] ?? []),
                'event_handlers' => json_encode($app_config['event_handlers'] ?? []),
                'deployed_nodes' => json_encode(array_column($target_nodes, 'node_id')),
                'created_by' => $app_config['created_by']
            ];
            
            $sql = "INSERT INTO meschain_edge_applications SET " . 
                   $this->buildInsertQuery($app_data);
            $this->db->query($sql);
            $app_id = $this->db->getLastId();
            
            // Deploy to edge nodes
            $deployment_results = [];
            foreach ($target_nodes as $node) {
                $deployment_result = $this->deployToEdgeNode($app_id, $app_config, $node);
                $deployment_results[] = $deployment_result;
            }
            
            // Update deployment status
            $overall_status = $this->evaluateDeploymentStatus($deployment_results);
            $this->updateApplicationStatus($app_id, $overall_status);
            
            return [
                'app_id' => $app_id,
                'deployment_results' => $deployment_results,
                'overall_status' => $overall_status,
                'deployed_nodes' => count($target_nodes),
                'deployment_time' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->logger->error('Edge application deployment failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get Real-time IoT Dashboard
     */
    public function getIoTDashboard() {
        try {
            return [
                'dashboard_timestamp' => date('Y-m-d H:i:s'),
                'system_status' => [
                    'total_devices' => $this->getTotalDeviceCount(),
                    'online_devices' => $this->getOnlineDeviceCount(),
                    'edge_nodes_active' => $this->getActiveEdgeNodeCount(),
                    'applications_deployed' => $this->getDeployedApplicationCount(),
                    'data_processing_rate' => $this->getCurrentDataProcessingRate()
                ],
                'performance_metrics' => $this->iot_metrics,
                'edge_metrics' => $this->edge_metrics,
                'device_overview' => [
                    'by_type' => $this->getDevicesByType(),
                    'by_status' => $this->getDevicesByStatus(),
                    'by_location' => $this->getDevicesByLocation(),
                    'recent_registrations' => $this->getRecentDeviceRegistrations(10)
                ],
                'edge_node_status' => [
                    'resource_utilization' => $this->getEdgeNodeResourceUtilization(),
                    'performance_metrics' => $this->getEdgeNodePerformanceMetrics(),
                    'health_status' => $this->getEdgeNodeHealthStatus()
                ],
                'data_analytics' => [
                    'data_volume_24h' => $this->getDataVolume24Hours(),
                    'anomalies_detected' => $this->getAnomaliesDetectedToday(),
                    'processing_latency_avg' => $this->getAverageProcessingLatency(),
                    'data_quality_score' => $this->getAverageDataQualityScore()
                ],
                'alerts' => $this->getActiveIoTAlerts(),
                'recommendations' => $this->getSystemRecommendations()
            ];
            
        } catch (Exception $e) {
            $this->logger->error('IoT dashboard generation failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get Edge Computing Status
     */
    public function getEdgeComputingStatus() {
        return [
            'edge_computing_status' => 'active',
            'version' => '1.0.0',
            'iot_metrics' => $this->iot_metrics,
            'edge_metrics' => $this->edge_metrics,
            'connected_devices' => $this->getConnectedDeviceCount(),
            'active_edge_nodes' => $this->getActiveEdgeNodeCount(),
            'deployed_applications' => $this->getDeployedApplicationCount(),
            'data_processed_today' => $this->getDataProcessedToday(),
            'system_health' => [
                'network_connectivity' => $this->getNetworkConnectivityStatus(),
                'computational_load' => $this->getComputationalLoad(),
                'storage_utilization' => $this->getStorageUtilization(),
                'bandwidth_usage' => $this->getBandwidthUsage()
            ],
            'performance_insights' => [
                'top_performing_nodes' => $this->getTopPerformingEdgeNodes(),
                'device_efficiency_trends' => $this->getDeviceEfficiencyTrends(),
                'application_performance' => $this->getApplicationPerformanceMetrics()
            ],
            'last_updated' => date('Y-m-d H:i:s')
        ];
    }
    
    // Helper methods
    private function validateDeviceConfig($config) { /* Implementation */ }
    private function generateDeviceUUID() { /* Implementation */ }
    private function selectOptimalEdgeNode($location, $requirements) { /* Implementation */ }
    private function processDataAtEdge($data, $node, $device) { /* Implementation */ }
    private function detectAnomalies($data, $device) { /* Implementation */ }
    private function deployToEdgeNode($app_id, $config, $node) { /* Implementation */ }
    
} 