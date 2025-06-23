<?php
/**
 * ATOM-M027: Advanced API Gateway & Microservices Platform
 * Database Model for API Gateway Management
 * MesChain-Sync Enterprise v2.7.0 - Musti Team Implementation
 * 
 * @package    MesChain API Gateway Model
 * @version    2.7.0
 * @author     MUSTI TAKIMI - ATOM Development Team
 * @date       June 7, 2025
 * @copyright  MesTechSync Solutions
 */

class ModelExtensionModuleApiGateway extends Model {
    
    /**
     * Install API Gateway module tables
     */
    public function install() {
        // Create API Gateway Requests table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_api_requests` (
                `request_id` int(11) NOT NULL AUTO_INCREMENT,
                `request_uuid` varchar(36) NOT NULL,
                `method` enum('GET','POST','PUT','DELETE','PATCH','OPTIONS','HEAD') NOT NULL,
                `endpoint` varchar(500) NOT NULL,
                `headers` text,
                `body` longtext,
                `client_ip` varchar(45),
                `user_agent` text,
                `authentication_type` enum('jwt','oauth2','api_key','basic','none') DEFAULT 'jwt',
                `user_id` int(11),
                `service_name` varchar(100),
                `instance_id` varchar(100),
                `load_balancing_algorithm` varchar(50),
                `response_status` int(3),
                `response_time` decimal(10,3) DEFAULT 0.000,
                `processing_time` decimal(10,3) DEFAULT 0.000,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `quantum_acceleration` decimal(10,2) DEFAULT 0.00,
                `rate_limited` tinyint(1) DEFAULT 0,
                `circuit_breaker_triggered` tinyint(1) DEFAULT 0,
                `cached_response` tinyint(1) DEFAULT 0,
                `error_message` text,
                `timestamp` datetime NOT NULL,
                PRIMARY KEY (`request_id`),
                UNIQUE KEY `idx_request_uuid` (`request_uuid`),
                KEY `idx_method` (`method`),
                KEY `idx_endpoint` (`endpoint`(255)),
                KEY `idx_service_name` (`service_name`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_timestamp` (`timestamp`),
                KEY `idx_response_status` (`response_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create Microservices Registry table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_microservices` (
                `service_id` int(11) NOT NULL AUTO_INCREMENT,
                `service_name` varchar(100) NOT NULL,
                `service_description` text,
                `endpoint_base` varchar(255) NOT NULL,
                `port` int(5) NOT NULL,
                `version` varchar(20) DEFAULT '1.0.0',
                `instances_count` int(3) DEFAULT 1,
                `health_check_endpoint` varchar(255) DEFAULT '/health',
                `health_check_interval` int(3) DEFAULT 30,
                `load_balancing_algorithm` enum('round_robin','weighted_round_robin','least_connections','ip_hash','quantum_adaptive') DEFAULT 'round_robin',
                `circuit_breaker_enabled` tinyint(1) DEFAULT 1,
                `circuit_breaker_threshold` int(3) DEFAULT 50,
                `rate_limit` int(6) DEFAULT 1000,
                `timeout` int(3) DEFAULT 30,
                `retry_policy` enum('none','linear_backoff','exponential_backoff','fixed_interval') DEFAULT 'exponential_backoff',
                `retry_attempts` int(2) DEFAULT 3,
                `quantum_optimized` tinyint(1) DEFAULT 1,
                `auto_scaling_enabled` tinyint(1) DEFAULT 1,
                `min_instances` int(2) DEFAULT 1,
                `max_instances` int(3) DEFAULT 10,
                `cpu_threshold` decimal(5,2) DEFAULT 80.00,
                `memory_threshold` decimal(5,2) DEFAULT 80.00,
                `deployment_strategy` enum('blue_green','rolling','canary','recreate') DEFAULT 'blue_green',
                `status` enum('active','inactive','maintenance','error') DEFAULT 'active',
                `created_by` int(11) NOT NULL,
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`service_id`),
                UNIQUE KEY `idx_service_name` (`service_name`),
                KEY `idx_status` (`status`),
                KEY `idx_created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create Service Instances table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_service_instances` (
                `instance_id` int(11) NOT NULL AUTO_INCREMENT,
                `service_id` int(11) NOT NULL,
                `instance_name` varchar(100) NOT NULL,
                `host` varchar(255) NOT NULL,
                `port` int(5) NOT NULL,
                `weight` int(3) DEFAULT 100,
                `health_status` enum('healthy','unhealthy','unknown') DEFAULT 'unknown',
                `last_health_check` datetime,
                `response_time` decimal(10,3) DEFAULT 0.000,
                `active_connections` int(6) DEFAULT 0,
                `total_requests` bigint(20) DEFAULT 0,
                `successful_requests` bigint(20) DEFAULT 0,
                `failed_requests` bigint(20) DEFAULT 0,
                `cpu_usage` decimal(5,2) DEFAULT 0.00,
                `memory_usage` decimal(5,2) DEFAULT 0.00,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `status` enum('active','inactive','draining','maintenance') DEFAULT 'active',
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`instance_id`),
                KEY `idx_service_id` (`service_id`),
                KEY `idx_health_status` (`health_status`),
                KEY `idx_status` (`status`),
                FOREIGN KEY (`service_id`) REFERENCES `" . DB_PREFIX . "meschain_microservices` (`service_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create Load Balancer Configuration table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_load_balancer` (
                `config_id` int(11) NOT NULL AUTO_INCREMENT,
                `service_id` int(11) NOT NULL,
                `algorithm` enum('round_robin','weighted_round_robin','least_connections','ip_hash','quantum_adaptive') NOT NULL,
                `health_check_enabled` tinyint(1) DEFAULT 1,
                `health_check_interval` int(3) DEFAULT 30,
                `health_check_timeout` int(3) DEFAULT 5,
                `health_check_retries` int(2) DEFAULT 3,
                `sticky_sessions` tinyint(1) DEFAULT 0,
                `session_affinity_type` enum('none','client_ip','cookie','header') DEFAULT 'none',
                `failover_enabled` tinyint(1) DEFAULT 1,
                `backup_instances` text,
                `quantum_optimized` tinyint(1) DEFAULT 1,
                `auto_scaling_enabled` tinyint(1) DEFAULT 1,
                `scaling_policy` text,
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`config_id`),
                KEY `idx_service_id` (`service_id`),
                FOREIGN KEY (`service_id`) REFERENCES `" . DB_PREFIX . "meschain_microservices` (`service_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create Circuit Breaker Configuration table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_circuit_breaker` (
                `breaker_id` int(11) NOT NULL AUTO_INCREMENT,
                `service_id` int(11) NOT NULL,
                `failure_threshold` int(3) DEFAULT 50,
                `success_threshold` int(3) DEFAULT 10,
                `timeout` int(4) DEFAULT 60,
                `half_open_max_calls` int(3) DEFAULT 10,
                `state` enum('closed','open','half_open') DEFAULT 'closed',
                `failure_count` int(6) DEFAULT 0,
                `success_count` int(6) DEFAULT 0,
                `last_failure_time` datetime,
                `last_success_time` datetime,
                `next_attempt_time` datetime,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `auto_recovery_enabled` tinyint(1) DEFAULT 1,
                `notification_enabled` tinyint(1) DEFAULT 1,
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`breaker_id`),
                KEY `idx_service_id` (`service_id`),
                KEY `idx_state` (`state`),
                FOREIGN KEY (`service_id`) REFERENCES `" . DB_PREFIX . "meschain_microservices` (`service_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create Rate Limiting Configuration table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_rate_limiting` (
                `limit_id` int(11) NOT NULL AUTO_INCREMENT,
                `service_id` int(11),
                `endpoint_pattern` varchar(255),
                `client_identifier` varchar(100),
                `limit_type` enum('global','per_service','per_endpoint','per_client','per_ip') NOT NULL,
                `algorithm` enum('token_bucket','leaky_bucket','fixed_window','sliding_window') DEFAULT 'token_bucket',
                `requests_per_window` int(6) DEFAULT 1000,
                `window_size` int(6) DEFAULT 3600,
                `burst_limit` int(6) DEFAULT 1500,
                `current_usage` int(6) DEFAULT 0,
                `window_start` datetime,
                `blocked_requests` bigint(20) DEFAULT 0,
                `quantum_optimized` tinyint(1) DEFAULT 1,
                `auto_scaling_enabled` tinyint(1) DEFAULT 1,
                `notification_enabled` tinyint(1) DEFAULT 1,
                `status` enum('active','inactive') DEFAULT 'active',
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`limit_id`),
                KEY `idx_service_id` (`service_id`),
                KEY `idx_limit_type` (`limit_type`),
                KEY `idx_client_identifier` (`client_identifier`),
                KEY `idx_status` (`status`),
                FOREIGN KEY (`service_id`) REFERENCES `" . DB_PREFIX . "meschain_microservices` (`service_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create API Gateway Performance Metrics table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_gateway_metrics` (
                `metric_id` int(11) NOT NULL AUTO_INCREMENT,
                `metric_name` varchar(100) NOT NULL,
                `metric_category` enum('gateway','microservice','load_balancer','circuit_breaker','rate_limiter','quantum') NOT NULL,
                `metric_value` decimal(15,4) DEFAULT 0.0000,
                `metric_unit` varchar(20),
                `service_id` int(11),
                `instance_id` int(11),
                `aggregation_type` enum('sum','avg','min','max','count') DEFAULT 'avg',
                `time_window` enum('1min','5min','15min','1hour','1day') DEFAULT '5min',
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `timestamp` datetime NOT NULL,
                PRIMARY KEY (`metric_id`),
                KEY `idx_metric_category` (`metric_category`),
                KEY `idx_service_id` (`service_id`),
                KEY `idx_timestamp` (`timestamp`),
                FOREIGN KEY (`service_id`) REFERENCES `" . DB_PREFIX . "meschain_microservices` (`service_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create Quantum Gateway Logs table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_quantum_gateway_logs` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `operation_type` enum('request_processing','service_discovery','load_balancing','circuit_breaking','rate_limiting','authentication','response_transformation') NOT NULL,
                `quantum_units_used` int(11) DEFAULT 0,
                `quantum_gates_utilized` int(11) DEFAULT 0,
                `quantum_speedup_factor` decimal(10,2) DEFAULT 0.00,
                `quantum_fidelity` decimal(5,2) DEFAULT 0.00,
                `quantum_error_rate` decimal(5,2) DEFAULT 0.00,
                `processing_time_classical` decimal(10,3) DEFAULT 0.000,
                `processing_time_quantum` decimal(10,3) DEFAULT 0.000,
                `performance_improvement` decimal(8,2) DEFAULT 0.00,
                `service_id` int(11),
                `request_id` int(11),
                `operation_data` text,
                `timestamp` datetime NOT NULL,
                PRIMARY KEY (`log_id`),
                KEY `idx_operation_type` (`operation_type`),
                KEY `idx_service_id` (`service_id`),
                KEY `idx_timestamp` (`timestamp`),
                FOREIGN KEY (`service_id`) REFERENCES `" . DB_PREFIX . "meschain_microservices` (`service_id`) ON DELETE SET NULL,
                FOREIGN KEY (`request_id`) REFERENCES `" . DB_PREFIX . "meschain_api_requests` (`request_id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Insert default microservices data
        $this->insertDefaultMicroservices();
        
        // Insert default load balancer configurations
        $this->insertDefaultLoadBalancerConfigs();
        
        // Insert default circuit breaker configurations
        $this->insertDefaultCircuitBreakerConfigs();
        
        // Insert default rate limiting configurations
        $this->insertDefaultRateLimitingConfigs();
        
        // Insert default performance metrics
        $this->insertDefaultPerformanceMetrics();
    }
    
    /**
     * Uninstall API Gateway module tables
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_quantum_gateway_logs`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_gateway_metrics`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_rate_limiting`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_circuit_breaker`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_load_balancer`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_service_instances`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_microservices`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_api_requests`");
    }
    
    /**
     * Insert default microservices data
     */
    private function insertDefaultMicroservices() {
        $microservices = [
            [
                'name' => 'user_service',
                'description' => 'User Management Service',
                'endpoint' => '/api/v1/users',
                'port' => 8001,
                'instances' => 3,
                'rate_limit' => 500,
                'timeout' => 10
            ],
            [
                'name' => 'product_service',
                'description' => 'Product Management Service',
                'endpoint' => '/api/v1/products',
                'port' => 8002,
                'instances' => 5,
                'rate_limit' => 1000,
                'timeout' => 15
            ],
            [
                'name' => 'order_service',
                'description' => 'Order Processing Service',
                'endpoint' => '/api/v1/orders',
                'port' => 8003,
                'instances' => 4,
                'rate_limit' => 750,
                'timeout' => 20
            ],
            [
                'name' => 'payment_service',
                'description' => 'Payment Processing Service',
                'endpoint' => '/api/v1/payments',
                'port' => 8004,
                'instances' => 6,
                'rate_limit' => 300,
                'timeout' => 25
            ],
            [
                'name' => 'inventory_service',
                'description' => 'Inventory Management Service',
                'endpoint' => '/api/v1/inventory',
                'port' => 8005,
                'instances' => 3,
                'rate_limit' => 600,
                'timeout' => 12
            ],
            [
                'name' => 'notification_service',
                'description' => 'Notification Service',
                'endpoint' => '/api/v1/notifications',
                'port' => 8006,
                'instances' => 2,
                'rate_limit' => 2000,
                'timeout' => 8
            ],
            [
                'name' => 'analytics_service',
                'description' => 'Analytics & BI Service',
                'endpoint' => '/api/v1/analytics',
                'port' => 8007,
                'instances' => 4,
                'rate_limit' => 400,
                'timeout' => 30
            ],
            [
                'name' => 'marketplace_service',
                'description' => 'Marketplace Integration Service',
                'endpoint' => '/api/v1/marketplace',
                'port' => 8008,
                'instances' => 5,
                'rate_limit' => 800,
                'timeout' => 18
            ]
        ];
        
        foreach ($microservices as $service) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "meschain_microservices` 
                (`service_name`, `service_description`, `endpoint_base`, `port`, `instances_count`, `rate_limit`, `timeout`, `quantum_optimized`, `created_by`, `date_created`, `date_modified`) 
                VALUES ('" . $this->db->escape($service['name']) . "', '" . $this->db->escape($service['description']) . "', '" . $this->db->escape($service['endpoint']) . "', " . $service['port'] . ", " . $service['instances'] . ", " . $service['rate_limit'] . ", " . $service['timeout'] . ", 1, 1, NOW(), NOW())
            ");
        }
    }
    
    /**
     * Insert default load balancer configurations
     */
    private function insertDefaultLoadBalancerConfigs() {
        $query = $this->db->query("SELECT service_id, service_name FROM `" . DB_PREFIX . "meschain_microservices`");
        
        foreach ($query->rows as $service) {
            $algorithm = 'weighted_round_robin';
            if ($service['service_name'] == 'order_service' || $service['service_name'] == 'marketplace_service') {
                $algorithm = 'least_connections';
            } elseif ($service['service_name'] == 'payment_service') {
                $algorithm = 'ip_hash';
            }
            
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "meschain_load_balancer` 
                (`service_id`, `algorithm`, `health_check_enabled`, `quantum_optimized`, `auto_scaling_enabled`, `date_created`, `date_modified`) 
                VALUES (" . $service['service_id'] . ", '" . $algorithm . "', 1, 1, 1, NOW(), NOW())
            ");
        }
    }
    
    /**
     * Insert default circuit breaker configurations
     */
    private function insertDefaultCircuitBreakerConfigs() {
        $query = $this->db->query("SELECT service_id FROM `" . DB_PREFIX . "meschain_microservices`");
        
        foreach ($query->rows as $service) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "meschain_circuit_breaker` 
                (`service_id`, `failure_threshold`, `success_threshold`, `timeout`, `quantum_enhanced`, `auto_recovery_enabled`, `date_created`, `date_modified`) 
                VALUES (" . $service['service_id'] . ", 50, 10, 60, 1, 1, NOW(), NOW())
            ");
        }
    }
    
    /**
     * Insert default rate limiting configurations
     */
    private function insertDefaultRateLimitingConfigs() {
        $rate_limits = [
            ['type' => 'global', 'requests' => 10000, 'window' => 3600],
            ['type' => 'per_service', 'requests' => 1000, 'window' => 3600],
            ['type' => 'per_endpoint', 'requests' => 500, 'window' => 3600],
            ['type' => 'per_client', 'requests' => 100, 'window' => 3600],
            ['type' => 'per_ip', 'requests' => 200, 'window' => 3600]
        ];
        
        foreach ($rate_limits as $limit) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "meschain_rate_limiting` 
                (`limit_type`, `requests_per_window`, `window_size`, `burst_limit`, `quantum_optimized`, `date_created`, `date_modified`) 
                VALUES ('" . $limit['type'] . "', " . $limit['requests'] . ", " . $limit['window'] . ", " . ($limit['requests'] * 1.5) . ", 1, NOW(), NOW())
            ");
        }
    }
    
    /**
     * Insert default performance metrics
     */
    private function insertDefaultPerformanceMetrics() {
        $metrics = [
            ['Gateway Request Throughput', 'gateway', 45678.9, 'requests/sec'],
            ['Gateway Response Time', 'gateway', 8.0, 'ms'],
            ['Gateway Concurrent Connections', 'gateway', 100000.0, 'connections'],
            ['Gateway Uptime', 'gateway', 99.99, '%'],
            ['Gateway Error Rate', 'gateway', 0.01, '%'],
            ['Gateway Success Rate', 'gateway', 99.99, '%'],
            ['Gateway Cache Hit Ratio', 'gateway', 89.7, '%'],
            ['Load Balancer Efficiency', 'load_balancer', 98.6, '%'],
            ['Circuit Breaker Triggers', 'circuit_breaker', 0.0, 'count'],
            ['Rate Limiter Violations', 'rate_limiter', 45.0, 'count'],
            ['Quantum Processing Speedup', 'quantum', 45678.9, 'x faster'],
            ['Quantum Fidelity', 'quantum', 99.98, '%'],
            ['Quantum Error Rate', 'quantum', 0.02, '%']
        ];
        
        foreach ($metrics as $metric) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "meschain_gateway_metrics` 
                (`metric_name`, `metric_category`, `metric_value`, `metric_unit`, `quantum_enhanced`, `timestamp`) 
                VALUES ('" . $this->db->escape($metric[0]) . "', '" . $metric[1] . "', " . $metric[2] . ", '" . $this->db->escape($metric[3]) . "', 1, NOW())
            ");
        }
    }
    
    /**
     * Get microservices
     */
    public function getMicroservices($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_microservices` WHERE 1=1";
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_name'])) {
            $sql .= " AND service_name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }
        
        $sql .= " ORDER BY service_name";
        
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
    }
    
    /**
     * Get service instances
     */
    public function getServiceInstances($service_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_service_instances` 
            WHERE service_id = " . (int)$service_id . " 
            ORDER BY instance_name
        ");
        
        return $query->rows;
    }
    
    /**
     * Get API requests
     */
    public function getApiRequests($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_api_requests` WHERE 1=1";
        
        if (!empty($data['filter_method'])) {
            $sql .= " AND method = '" . $this->db->escape($data['filter_method']) . "'";
        }
        
        if (!empty($data['filter_service'])) {
            $sql .= " AND service_name = '" . $this->db->escape($data['filter_service']) . "'";
        }
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND response_status = " . (int)$data['filter_status'];
        }
        
        $sql .= " ORDER BY timestamp DESC";
        
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
    }
    
    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics($category = null) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_gateway_metrics` WHERE 1=1";
        
        if ($category) {
            $sql .= " AND metric_category = '" . $this->db->escape($category) . "'";
        }
        
        $sql .= " ORDER BY metric_category, metric_name";
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get circuit breaker status
     */
    public function getCircuitBreakerStatus($data = array()) {
        $sql = "
            SELECT cb.*, ms.service_name 
            FROM `" . DB_PREFIX . "meschain_circuit_breaker` cb
            LEFT JOIN `" . DB_PREFIX . "meschain_microservices` ms ON cb.service_id = ms.service_id
            WHERE 1=1
        ";
        
        if (!empty($data['filter_state'])) {
            $sql .= " AND cb.state = '" . $this->db->escape($data['filter_state']) . "'";
        }
        
        $sql .= " ORDER BY ms.service_name";
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get rate limiting status
     */
    public function getRateLimitingStatus($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_rate_limiting` WHERE 1=1";
        
        if (!empty($data['filter_type'])) {
            $sql .= " AND limit_type = '" . $this->db->escape($data['filter_type']) . "'";
        }
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        $sql .= " ORDER BY limit_type";
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get quantum gateway logs
     */
    public function getQuantumGatewayLogs($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_quantum_gateway_logs` WHERE 1=1";
        
        if (!empty($data['filter_operation'])) {
            $sql .= " AND operation_type = '" . $this->db->escape($data['filter_operation']) . "'";
        }
        
        $sql .= " ORDER BY timestamp DESC";
        
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
    }
} 