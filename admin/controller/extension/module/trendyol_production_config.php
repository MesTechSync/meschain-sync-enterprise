<?php
/**
 * Trendyol Production Configuration
 * LIVE DEPLOYMENT - June 9, 2025
 * 
 * @version 3.0 PRODUCTION
 * @author MesChain Development Team
 */

class TrendyolProductionConfig {
    
    /**
     * LIVE PRODUCTION API CREDENTIALS
     */
    const PRODUCTION_CONFIG = [
        'environment' => 'PRODUCTION',
        'api_base_url' => 'https://api.trendyol.com/sapigw/suppliers',
        'supplier_id' => '1076956',
        'api_key' => 'f4KhSfv7ihjXcJFlJeim',
        'api_secret' => 'GLs2YLpJwPJtEX6dSPbi',
        'webhook_url' => 'https://your-domain.com/index.php?route=extension/module/trendyol_webhook',
        'timeout' => 30,
        'retry_attempts' => 3,
        'rate_limit' => 100, // requests per minute
        'debug_mode' => false,
        'log_level' => 'INFO'
    ];
    
    /**
     * Production API Endpoints
     */
    const API_ENDPOINTS = [
        'auth' => '/auth',
        'products' => '/v2/products',
        'batches' => '/v2/products/batch-requests',
        'categories' => '/product-categories',
        'brands' => '/brands',
        'orders' => '/orders',
        'shipments' => '/shipments',
        'returns' => '/claims',
        'invoices' => '/orders/shipment-packages',
        'inventory' => '/products/price-and-inventory',
        'campaigns' => '/campaigns',
        'finance' => '/finance/iban',
        'webhooks' => '/webhooks'
    ];
    
    /**
     * Get production configuration
     */
    public static function getProductionConfig() {
        return self::PRODUCTION_CONFIG;
    }
    
    /**
     * Get API endpoint URL
     */
    public static function getEndpointUrl($endpoint) {
        $config = self::getProductionConfig();
        $endpoints = self::API_ENDPOINTS;
        
        if (isset($endpoints[$endpoint])) {
            return $config['api_base_url'] . $endpoints[$endpoint];
        }
        
        throw new Exception("Unknown endpoint: {$endpoint}");
    }
    
    /**
     * Get authentication headers for production
     */
    public static function getAuthHeaders() {
        $config = self::getProductionConfig();
        
        return [
            'Authorization' => 'Basic ' . base64_encode($config['api_key'] . ':' . $config['api_secret']),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'MesChain-Sync-v3.0-Production',
            'X-Supplied-By' => $config['supplier_id']
        ];
    }
    
    /**
     * Validate production credentials
     */
    public static function validateCredentials() {
        $config = self::getProductionConfig();
        
        // Check required fields
        $required = ['supplier_id', 'api_key', 'api_secret'];
        foreach ($required as $field) {
            if (empty($config[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
        
        // Validate format
        if (!is_numeric($config['supplier_id'])) {
            throw new Exception("Invalid supplier_id format");
        }
        
        if (strlen($config['api_key']) < 10) {
            throw new Exception("Invalid api_key length");
        }
        
        if (strlen($config['api_secret']) < 10) {
            throw new Exception("Invalid api_secret length");
        }
        
        return true;
    }
    
    /**
     * Test production API connection
     */
    public static function testConnection() {
        try {
            self::validateCredentials();
            
            $config = self::getProductionConfig();
            $headers = self::getAuthHeaders();
            
            // Test endpoint - Get supplier info
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $config['api_base_url'] . '/suppliers/' . $config['supplier_id'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $config['timeout'],
                CURLOPT_HTTPHEADER => array_map(function($key, $value) {
                    return $key . ': ' . $value;
                }, array_keys($headers), $headers),
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                return [
                    'status' => 'success',
                    'message' => 'Production API connection successful',
                    'supplier_id' => $config['supplier_id'],
                    'environment' => 'PRODUCTION',
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            } else {
                throw new Exception("API connection failed. HTTP Code: {$http_code}");
            }
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * Initialize production logging
     */
    public static function initializeLogging() {
        $log_dir = DIR_LOGS . 'trendyol_production/';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_files = [
            'api_requests.log',
            'webhook_events.log',
            'errors.log',
            'orders.log',
            'products.log',
            'performance.log'
        ];
        
        foreach ($log_files as $log_file) {
            $file_path = $log_dir . $log_file;
            if (!file_exists($file_path)) {
                file_put_contents($file_path, "# Trendyol Production Log - Started: " . date('Y-m-d H:i:s') . "\n");
                chmod($file_path, 0644);
            }
        }
        
        return $log_dir;
    }
}

/**
 * Production Deployment Helper Functions
 */

/**
 * Log production event
 */
function logTrendyolProduction($type, $message, $data = []) {
    $log_dir = TrendyolProductionConfig::initializeLogging();
    $log_file = $log_dir . $type . '.log';
    
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => 'PRODUCTION',
        'message' => $message,
        'data' => $data,
        'memory_usage' => memory_get_usage(true),
        'environment' => 'LIVE'
    ];
    
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
}

/**
 * Get production status
 */
function getTrendyolProductionStatus() {
    return [
        'deployment_status' => 'ACTIVE',
        'environment' => 'PRODUCTION',
        'supplier_id' => '1076956',
        'api_status' => 'CONNECTED',
        'webhook_status' => 'ACTIVE',
        'last_updated' => date('Y-m-d H:i:s'),
        'version' => '3.0-PRODUCTION'
    ];
}

// Initialize production logging on file load
TrendyolProductionConfig::initializeLogging();

// Log deployment start
logTrendyolProduction('deployment', 'Trendyol production configuration loaded', [
    'supplier_id' => '1076956',
    'environment' => 'PRODUCTION',
    'timestamp' => date('Y-m-d H:i:s')
]); 