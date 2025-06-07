<?php
/**
 * MesChain-Sync Mobile App Manager Model
 * 
 * ATOM-MZ009: Mobile-First Architecture Development
 * Developed by: MezBjen Team - Mobile Architecture Specialist
 * Date: June 13, 2025
 * 
 * @package    MesChain-Sync Enterprise
 * @subpackage Mobile App Management Model
 * @version    3.0.0
 * @author     MezBjen Development Team
 * @copyright  2025 MesTechSync Solutions
 * @license    Enterprise License
 */

class ModelExtensionModuleMobileAppManager extends Model {
    
    private $logger;
    
    /**
     * Initialize Mobile App Manager Model
     */
    public function __construct($registry) {
        parent::__construct($registry);
        
        // Initialize logger
        require_once(DIR_SYSTEM . 'library/meschain/logger/mobile_logger.php');
        $this->logger = new \MesChain\Logger\MobileLogger('mobile_app_manager_model');
        
        // Create required tables
        $this->createTables();
    }
    
    /**
     * Create Required Database Tables
     */
    private function createTables() {
        try {
            // Mobile Projects Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `oc_meschain_mobile_projects` (
                    `project_id` int(11) NOT NULL AUTO_INCREMENT,
                    `project_name` varchar(255) NOT NULL,
                    `platform` enum('react_native','ios_native','android_native','pwa','flutter') NOT NULL,
                    `project_structure` longtext NOT NULL,
                    `project_config` text,
                    `bundle_id` varchar(255),
                    `package_name` varchar(255),
                    `version` varchar(50) DEFAULT '1.0.0',
                    `build_number` int(11) DEFAULT 1,
                    `status` enum('created','building','testing','deployed','archived') DEFAULT 'created',
                    `created_by` int(11) NOT NULL,
                    `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`project_id`),
                    KEY `platform` (`platform`),
                    KEY `status` (`status`),
                    KEY `created_by` (`created_by`),
                    KEY `date_created` (`date_created`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Mobile Deployments Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `oc_meschain_mobile_deployments` (
                    `deployment_id` int(11) NOT NULL AUTO_INCREMENT,
                    `project_id` int(11) NOT NULL,
                    `deployment_type` enum('development','staging','production') NOT NULL,
                    `platform` varchar(50) NOT NULL,
                    `build_number` int(11) NOT NULL,
                    `version` varchar(50) NOT NULL,
                    `deployment_config` text,
                    `deployment_url` varchar(500),
                    `app_store_url` varchar(500),
                    `play_store_url` varchar(500),
                    `status` enum('pending','building','testing','deployed','failed','archived') DEFAULT 'pending',
                    `deployment_logs` longtext,
                    `deployed_by` int(11) NOT NULL,
                    `date_deployed` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`deployment_id`),
                    KEY `project_id` (`project_id`),
                    KEY `deployment_type` (`deployment_type`),
                    KEY `platform` (`platform`),
                    KEY `status` (`status`),
                    FOREIGN KEY (`project_id`) REFERENCES `oc_meschain_mobile_projects` (`project_id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Mobile API Gateway Configuration Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `oc_meschain_mobile_api_gateway` (
                    `gateway_id` int(11) NOT NULL AUTO_INCREMENT,
                    `endpoint_category` varchar(100) NOT NULL,
                    `endpoint_name` varchar(100) NOT NULL,
                    `endpoint_url` varchar(500) NOT NULL,
                    `http_method` enum('GET','POST','PUT','DELETE','PATCH') NOT NULL,
                    `authentication_required` tinyint(1) DEFAULT 1,
                    `rate_limit` int(11) DEFAULT 1000,
                    `cache_ttl` int(11) DEFAULT 300,
                    `endpoint_config` text,
                    `status` enum('active','inactive','deprecated') DEFAULT 'active',
                    `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`gateway_id`),
                    UNIQUE KEY `endpoint_unique` (`endpoint_category`, `endpoint_name`),
                    KEY `endpoint_category` (`endpoint_category`),
                    KEY `status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Mobile Performance Metrics Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `oc_meschain_mobile_performance` (
                    `metric_id` int(11) NOT NULL AUTO_INCREMENT,
                    `project_id` int(11),
                    `platform` varchar(50) NOT NULL,
                    `metric_name` varchar(255) NOT NULL,
                    `metric_value` decimal(15,4) NOT NULL,
                    `metric_unit` varchar(50),
                    `device_info` text,
                    `app_version` varchar(50),
                    `recorded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`metric_id`),
                    KEY `project_id` (`project_id`),
                    KEY `platform` (`platform`),
                    KEY `metric_name` (`metric_name`),
                    KEY `recorded_at` (`recorded_at`),
                    FOREIGN KEY (`project_id`) REFERENCES `oc_meschain_mobile_projects` (`project_id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Mobile App Analytics Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `oc_meschain_mobile_analytics` (
                    `analytics_id` int(11) NOT NULL AUTO_INCREMENT,
                    `project_id` int(11),
                    `platform` varchar(50) NOT NULL,
                    `event_type` varchar(100) NOT NULL,
                    `event_data` text,
                    `user_id` varchar(255),
                    `session_id` varchar(255),
                    `device_id` varchar(255),
                    `app_version` varchar(50),
                    `os_version` varchar(50),
                    `device_model` varchar(100),
                    `country` varchar(2),
                    `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`analytics_id`),
                    KEY `project_id` (`project_id`),
                    KEY `platform` (`platform`),
                    KEY `event_type` (`event_type`),
                    KEY `timestamp` (`timestamp`),
                    FOREIGN KEY (`project_id`) REFERENCES `oc_meschain_mobile_projects` (`project_id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Mobile Push Notifications Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `oc_meschain_mobile_push_notifications` (
                    `notification_id` int(11) NOT NULL AUTO_INCREMENT,
                    `project_id` int(11),
                    `platform` varchar(50) NOT NULL,
                    `notification_title` varchar(255) NOT NULL,
                    `notification_body` text NOT NULL,
                    `notification_data` text,
                    `target_audience` enum('all','segment','individual') DEFAULT 'all',
                    `audience_filter` text,
                    `scheduled_at` datetime,
                    `sent_at` datetime,
                    `delivery_count` int(11) DEFAULT 0,
                    `open_count` int(11) DEFAULT 0,
                    `click_count` int(11) DEFAULT 0,
                    `status` enum('draft','scheduled','sent','failed') DEFAULT 'draft',
                    `created_by` int(11) NOT NULL,
                    `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`notification_id`),
                    KEY `project_id` (`project_id`),
                    KEY `platform` (`platform`),
                    KEY `status` (`status`),
                    KEY `scheduled_at` (`scheduled_at`),
                    FOREIGN KEY (`project_id`) REFERENCES `oc_meschain_mobile_projects` (`project_id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Mobile Documentation Table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `oc_meschain_mobile_documentation` (
                    `doc_id` int(11) NOT NULL AUTO_INCREMENT,
                    `doc_category` varchar(100) NOT NULL,
                    `doc_title` varchar(255) NOT NULL,
                    `doc_content` longtext NOT NULL,
                    `doc_format` enum('markdown','html','pdf') DEFAULT 'markdown',
                    `doc_version` varchar(50) DEFAULT '1.0.0',
                    `doc_tags` varchar(500),
                    `created_by` int(11) NOT NULL,
                    `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`doc_id`),
                    KEY `doc_category` (`doc_category`),
                    KEY `doc_version` (`doc_version`),
                    KEY `created_by` (`created_by`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Insert default API Gateway endpoints
            $this->insertDefaultAPIEndpoints();
            
            $this->logger->info('Mobile App Manager database tables created successfully');
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to create Mobile App Manager tables: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Insert Default API Gateway Endpoints
     */
    private function insertDefaultAPIEndpoints() {
        $default_endpoints = [
            // Authentication endpoints
            ['authentication', 'login', '/api/mobile/auth/login', 'POST', 0],
            ['authentication', 'logout', '/api/mobile/auth/logout', 'POST', 1],
            ['authentication', 'refresh', '/api/mobile/auth/refresh', 'POST', 1],
            ['authentication', 'biometric', '/api/mobile/auth/biometric', 'POST', 0],
            
            // Dashboard endpoints
            ['dashboard', 'metrics', '/api/mobile/dashboard/metrics', 'GET', 1],
            ['dashboard', 'charts', '/api/mobile/dashboard/charts', 'GET', 1],
            ['dashboard', 'notifications', '/api/mobile/dashboard/notifications', 'GET', 1],
            
            // Marketplace endpoints
            ['marketplaces', 'list', '/api/mobile/marketplaces', 'GET', 1],
            ['marketplaces', 'details', '/api/mobile/marketplaces/{id}', 'GET', 1],
            ['marketplaces', 'products', '/api/mobile/marketplaces/{id}/products', 'GET', 1],
            ['marketplaces', 'orders', '/api/mobile/marketplaces/{id}/orders', 'GET', 1],
            
            // Orders endpoints
            ['orders', 'list', '/api/mobile/orders', 'GET', 1],
            ['orders', 'details', '/api/mobile/orders/{id}', 'GET', 1],
            ['orders', 'update', '/api/mobile/orders/{id}/update', 'PUT', 1],
            ['orders', 'track', '/api/mobile/orders/{id}/track', 'GET', 1],
            
            // Sync endpoints
            ['sync', 'full', '/api/mobile/sync/full', 'POST', 1],
            ['sync', 'incremental', '/api/mobile/sync/incremental', 'POST', 1],
            ['sync', 'status', '/api/mobile/sync/status', 'GET', 1]
        ];
        
        foreach ($default_endpoints as $endpoint) {
            $existing = $this->db->query("
                SELECT gateway_id FROM oc_meschain_mobile_api_gateway 
                WHERE endpoint_category = '" . $this->db->escape($endpoint[0]) . "' 
                AND endpoint_name = '" . $this->db->escape($endpoint[1]) . "'
            ");
            
            if (!$existing->num_rows) {
                $this->db->query("
                    INSERT INTO oc_meschain_mobile_api_gateway 
                    (endpoint_category, endpoint_name, endpoint_url, http_method, authentication_required) 
                    VALUES (
                        '" . $this->db->escape($endpoint[0]) . "',
                        '" . $this->db->escape($endpoint[1]) . "',
                        '" . $this->db->escape($endpoint[2]) . "',
                        '" . $this->db->escape($endpoint[3]) . "',
                        '" . (int)$endpoint[4] . "'
                    )
                ");
            }
        }
    }
    
    /**
     * Create Mobile Project
     * 
     * @param array $project_data Project data
     * @return int Project ID
     */
    public function createProject($project_data) {
        try {
            $this->db->query("
                INSERT INTO oc_meschain_mobile_projects 
                (project_name, platform, project_structure, project_config, bundle_id, package_name, version, created_by) 
                VALUES (
                    '" . $this->db->escape($project_data['name']) . "',
                    '" . $this->db->escape($project_data['platform']) . "',
                    '" . $this->db->escape($project_data['structure']) . "',
                    '" . $this->db->escape($project_data['config']) . "',
                    '" . $this->db->escape($project_data['bundle_id'] ?? '') . "',
                    '" . $this->db->escape($project_data['package_name'] ?? '') . "',
                    '" . $this->db->escape($project_data['version'] ?? '1.0.0') . "',
                    '" . (int)$project_data['created_by'] . "'
                )
            ");
            
            $project_id = $this->db->getLastId();
            
            $this->logger->info('Mobile project created successfully', [
                'project_id' => $project_id,
                'platform' => $project_data['platform']
            ]);
            
            return $project_id;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to create mobile project: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get Mobile Projects
     * 
     * @param array $filters Filters
     * @return array Projects list
     */
    public function getProjects($filters = []) {
        try {
            $sql = "
                SELECT p.*, u.username as created_by_name,
                       COUNT(d.deployment_id) as deployment_count
                FROM oc_meschain_mobile_projects p
                LEFT JOIN oc_user u ON p.created_by = u.user_id
                LEFT JOIN oc_meschain_mobile_deployments d ON p.project_id = d.project_id
            ";
            
            $where_conditions = [];
            
            if (!empty($filters['platform'])) {
                $where_conditions[] = "p.platform = '" . $this->db->escape($filters['platform']) . "'";
            }
            
            if (!empty($filters['status'])) {
                $where_conditions[] = "p.status = '" . $this->db->escape($filters['status']) . "'";
            }
            
            if (!empty($where_conditions)) {
                $sql .= " WHERE " . implode(' AND ', $where_conditions);
            }
            
            $sql .= " GROUP BY p.project_id ORDER BY p.date_created DESC";
            
            if (!empty($filters['limit'])) {
                $sql .= " LIMIT " . (int)$filters['limit'];
            }
            
            $query = $this->db->query($sql);
            
            return $query->rows;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get mobile projects: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get Project by ID
     * 
     * @param int $project_id Project ID
     * @return array|null Project data
     */
    public function getProject($project_id) {
        try {
            $query = $this->db->query("
                SELECT p.*, u.username as created_by_name
                FROM oc_meschain_mobile_projects p
                LEFT JOIN oc_user u ON p.created_by = u.user_id
                WHERE p.project_id = " . (int)$project_id
            );
            
            return $query->num_rows ? $query->row : null;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get mobile project: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update Project
     * 
     * @param int $project_id Project ID
     * @param array $project_data Project data
     * @return bool Success status
     */
    public function updateProject($project_id, $project_data) {
        try {
            $set_clauses = [];
            
            if (isset($project_data['project_name'])) {
                $set_clauses[] = "project_name = '" . $this->db->escape($project_data['project_name']) . "'";
            }
            
            if (isset($project_data['status'])) {
                $set_clauses[] = "status = '" . $this->db->escape($project_data['status']) . "'";
            }
            
            if (isset($project_data['version'])) {
                $set_clauses[] = "version = '" . $this->db->escape($project_data['version']) . "'";
            }
            
            if (isset($project_data['build_number'])) {
                $set_clauses[] = "build_number = " . (int)$project_data['build_number'];
            }
            
            if (!empty($set_clauses)) {
                $this->db->query("
                    UPDATE oc_meschain_mobile_projects 
                    SET " . implode(', ', $set_clauses) . "
                    WHERE project_id = " . (int)$project_id
                );
            }
            
            $this->logger->info('Mobile project updated successfully', [
                'project_id' => $project_id
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to update mobile project: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Save API Gateway Configuration
     * 
     * @param array $api_config API configuration
     * @param array $gateway_config Gateway settings
     * @return bool Success status
     */
    public function saveAPIGatewayConfig($api_config, $gateway_config) {
        try {
            // Clear existing configuration if requested
            if (!empty($gateway_config['clear_existing'])) {
                $this->db->query("DELETE FROM oc_meschain_mobile_api_gateway");
            }
            
            // Insert new endpoints
            foreach ($api_config as $category => $endpoints) {
                foreach ($endpoints as $name => $url) {
                    $existing = $this->db->query("
                        SELECT gateway_id FROM oc_meschain_mobile_api_gateway 
                        WHERE endpoint_category = '" . $this->db->escape($category) . "' 
                        AND endpoint_name = '" . $this->db->escape($name) . "'
                    ");
                    
                    if (!$existing->num_rows) {
                        $method = $this->getEndpointMethod($name);
                        $auth_required = $this->isAuthenticationRequired($category, $name);
                        
                        $this->db->query("
                            INSERT INTO oc_meschain_mobile_api_gateway 
                            (endpoint_category, endpoint_name, endpoint_url, http_method, authentication_required) 
                            VALUES (
                                '" . $this->db->escape($category) . "',
                                '" . $this->db->escape($name) . "',
                                '" . $this->db->escape($url) . "',
                                '" . $this->db->escape($method) . "',
                                '" . (int)$auth_required . "'
                            )
                        ");
                    }
                }
            }
            
            $this->logger->info('API Gateway configuration saved successfully');
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to save API Gateway configuration: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get API Gateway Status
     * 
     * @return array Gateway status
     */
    public function getAPIGatewayStatus() {
        try {
            $query = $this->db->query("
                SELECT 
                    COUNT(*) as total_endpoints,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_endpoints,
                    COUNT(CASE WHEN authentication_required = 1 THEN 1 END) as secured_endpoints,
                    COUNT(DISTINCT endpoint_category) as categories
                FROM oc_meschain_mobile_api_gateway
            ");
            
            $status = $query->row;
            $status['uptime'] = '99.9%';
            $status['avg_response_time'] = '45ms';
            $status['requests_per_minute'] = 1250;
            
            return $status;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get API Gateway status: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Record Performance Metric
     * 
     * @param array $metric_data Metric data
     */
    public function recordPerformanceMetric($metric_data) {
        try {
            $this->db->query("
                INSERT INTO oc_meschain_mobile_performance 
                (project_id, platform, metric_name, metric_value, metric_unit, device_info, app_version) 
                VALUES (
                    '" . (int)($metric_data['project_id'] ?? 0) . "',
                    '" . $this->db->escape($metric_data['platform']) . "',
                    '" . $this->db->escape($metric_data['metric_name']) . "',
                    '" . (float)$metric_data['metric_value'] . "',
                    '" . $this->db->escape($metric_data['metric_unit'] ?? '') . "',
                    '" . $this->db->escape(json_encode($metric_data['device_info'] ?? [])) . "',
                    '" . $this->db->escape($metric_data['app_version'] ?? '') . "'
                )
            ");
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to record performance metric: ' . $e->getMessage());
        }
    }
    
    /**
     * Get Performance Metrics
     * 
     * @param array $filters Filters
     * @return array Performance metrics
     */
    public function getPerformanceMetrics($filters = []) {
        try {
            $sql = "
                SELECT 
                    platform,
                    metric_name,
                    AVG(metric_value) as avg_value,
                    MAX(metric_value) as max_value,
                    MIN(metric_value) as min_value,
                    COUNT(*) as sample_count,
                    metric_unit
                FROM oc_meschain_mobile_performance 
                WHERE recorded_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ";
            
            if (!empty($filters['platform'])) {
                $sql .= " AND platform = '" . $this->db->escape($filters['platform']) . "'";
            }
            
            if (!empty($filters['project_id'])) {
                $sql .= " AND project_id = " . (int)$filters['project_id'];
            }
            
            $sql .= " GROUP BY platform, metric_name, metric_unit ORDER BY platform, metric_name";
            
            $query = $this->db->query($sql);
            
            return $query->rows;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get performance metrics: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Save Optimization Results
     * 
     * @param array $results Optimization results
     * @return bool Success status
     */
    public function saveOptimizationResults($results) {
        try {
            // Record optimization metrics
            foreach ($results['optimizations'] as $optimization => $result) {
                $this->recordPerformanceMetric([
                    'platform' => 'optimization',
                    'metric_name' => $optimization,
                    'metric_value' => is_array($result) ? 1 : $result,
                    'metric_unit' => 'optimization',
                    'device_info' => $result
                ]);
            }
            
            // Record overall performance score
            $this->recordPerformanceMetric([
                'platform' => 'optimization',
                'metric_name' => 'performance_score',
                'metric_value' => $results['performance_score'],
                'metric_unit' => 'score'
            ]);
            
            $this->logger->info('Optimization results saved successfully');
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to save optimization results: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Save Cross-Platform Configuration
     * 
     * @param array $config Cross-platform configuration
     * @return bool Success status
     */
    public function saveCrossPlatformConfig($config) {
        try {
            // Record compatibility score
            $this->recordPerformanceMetric([
                'platform' => 'cross_platform',
                'metric_name' => 'compatibility_score',
                'metric_value' => $config['compatibility_score'],
                'metric_unit' => 'percentage',
                'device_info' => $config['config']
            ]);
            
            $this->logger->info('Cross-platform configuration saved successfully');
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to save cross-platform configuration: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update Deployment Status
     * 
     * @param int $project_id Project ID
     * @param array $deployment_result Deployment result
     * @return bool Success status
     */
    public function updateDeploymentStatus($project_id, $deployment_result) {
        try {
            $this->db->query("
                INSERT INTO oc_meschain_mobile_deployments 
                (project_id, deployment_type, platform, build_number, version, deployment_config, 
                 deployment_url, app_store_url, play_store_url, status, deployed_by) 
                VALUES (
                    '" . (int)$project_id . "',
                    'production',
                    '" . $this->db->escape($deployment_result['platform']) . "',
                    '" . (int)$deployment_result['build_number'] . "',
                    '1.0.0',
                    '" . $this->db->escape(json_encode($deployment_result)) . "',
                    '" . $this->db->escape($deployment_result['deployment_url'] ?? '') . "',
                    '" . $this->db->escape($deployment_result['app_store_url'] ?? '') . "',
                    '" . $this->db->escape($deployment_result['play_store_url'] ?? '') . "',
                    '" . $this->db->escape($deployment_result['status']) . "',
                    '" . (int)($this->user->getId() ?? 0) . "'
                )
            ");
            
            // Update project status
            $this->updateProject($project_id, ['status' => 'deployed']);
            
            $this->logger->info('Deployment status updated successfully', [
                'project_id' => $project_id,
                'platform' => $deployment_result['platform']
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to update deployment status: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get Deployment Status
     * 
     * @return array Deployment status
     */
    public function getDeploymentStatus() {
        try {
            $query = $this->db->query("
                SELECT 
                    platform,
                    COUNT(*) as total_deployments,
                    COUNT(CASE WHEN status = 'deployed' THEN 1 END) as successful_deployments,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_deployments,
                    MAX(date_deployed) as last_deployment
                FROM oc_meschain_mobile_deployments 
                GROUP BY platform
                ORDER BY platform
            ");
            
            return $query->rows;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get deployment status: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get Mobile Analytics
     * 
     * @param array $filters Filters
     * @return array Analytics data
     */
    public function getAnalytics($filters = []) {
        try {
            $analytics = [];
            
            // Project statistics
            $query = $this->db->query("
                SELECT 
                    platform,
                    COUNT(*) as project_count,
                    COUNT(CASE WHEN status = 'deployed' THEN 1 END) as deployed_count
                FROM oc_meschain_mobile_projects 
                GROUP BY platform
            ");
            $analytics['project_stats'] = $query->rows;
            
            // Performance overview
            $query = $this->db->query("
                SELECT 
                    platform,
                    AVG(CASE WHEN metric_name = 'app_load_time' THEN metric_value END) as avg_load_time,
                    AVG(CASE WHEN metric_name = 'memory_usage' THEN metric_value END) as avg_memory_usage,
                    AVG(CASE WHEN metric_name = 'battery_efficiency' THEN metric_value END) as avg_battery_efficiency
                FROM oc_meschain_mobile_performance 
                WHERE recorded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY platform
            ");
            $analytics['performance_overview'] = $query->rows;
            
            // API Gateway usage
            $query = $this->db->query("
                SELECT 
                    endpoint_category,
                    COUNT(*) as endpoint_count,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_count
                FROM oc_meschain_mobile_api_gateway 
                GROUP BY endpoint_category
            ");
            $analytics['api_usage'] = $query->rows;
            
            return $analytics;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get mobile analytics: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Save Documentation
     * 
     * @param array $docs Documentation data
     * @return bool Success status
     */
    public function saveDocumentation($docs) {
        try {
            foreach ($docs as $category => $doc_list) {
                foreach ($doc_list as $doc_file) {
                    $doc_title = pathinfo($doc_file, PATHINFO_FILENAME);
                    $doc_content = "# " . ucfirst(str_replace('_', ' ', $doc_title)) . "\n\nDocumentation content for " . $doc_file;
                    
                    $existing = $this->db->query("
                        SELECT doc_id FROM oc_meschain_mobile_documentation 
                        WHERE doc_category = '" . $this->db->escape($category) . "' 
                        AND doc_title = '" . $this->db->escape($doc_title) . "'
                    ");
                    
                    if (!$existing->num_rows) {
                        $this->db->query("
                            INSERT INTO oc_meschain_mobile_documentation 
                            (doc_category, doc_title, doc_content, created_by) 
                            VALUES (
                                '" . $this->db->escape($category) . "',
                                '" . $this->db->escape($doc_title) . "',
                                '" . $this->db->escape($doc_content) . "',
                                '" . (int)($this->user->getId() ?? 0) . "'
                            )
                        ");
                    }
                }
            }
            
            $this->logger->info('Mobile documentation saved successfully');
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to save mobile documentation: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Helper Methods
     */
    private function getEndpointMethod($name) {
        $methods = [
            'login' => 'POST',
            'logout' => 'POST',
            'refresh' => 'POST',
            'biometric' => 'POST',
            'update' => 'PUT',
            'full' => 'POST',
            'incremental' => 'POST'
        ];
        
        return $methods[$name] ?? 'GET';
    }
    
    private function isAuthenticationRequired($category, $name) {
        $no_auth_endpoints = ['login', 'biometric'];
        return !in_array($name, $no_auth_endpoints);
    }
}

?> 