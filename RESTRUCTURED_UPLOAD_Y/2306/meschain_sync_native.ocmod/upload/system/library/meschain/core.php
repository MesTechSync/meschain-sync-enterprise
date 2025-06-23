<?php
/**
 * MesChain Core Library
 * Native OpenCart 4.x System Library
 * Path: system/library/meschain/core.php
 */

namespace Meschain;

class Core {
    
    private $registry;
    private $config;
    private $db;
    private $cache;
    private $log;
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $registry->get('config');
        $this->db = $registry->get('db');
        $this->cache = $registry->get('cache');
        $this->log = $registry->get('log');
    }
    
    /**
     * Get MesChain configuration
     */
    public function getConfig($key = null, $default = null) {
        if ($key === null) {
            return $this->getAllConfig();
        }
        
        $cache_key = 'meschain.config.' . $key;
        $value = $this->cache->get($cache_key);
        
        if ($value === false) {
            $query = $this->db->query("
                SELECT `value` 
                FROM `" . DB_PREFIX . "setting` 
                WHERE `code` = 'meschain' AND `key` = '" . $this->db->escape($key) . "'
            ");
            
            $value = $query->num_rows ? $query->row['value'] : $default;
            $this->cache->set($cache_key, $value, 3600);
        }
        
        return $value;
    }
    
    /**
     * Set MesChain configuration
     */
    public function setConfig($key, $value) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "setting` 
            SET `store_id` = 0,
                `code` = 'meschain',
                `key` = '" . $this->db->escape($key) . "',
                `value` = '" . $this->db->escape($value) . "',
                `serialized` = 0
            ON DUPLICATE KEY UPDATE 
                `value` = '" . $this->db->escape($value) . "'
        ");
        
        // Clear cache
        $cache_key = 'meschain.config.' . $key;
        $this->cache->delete($cache_key);
        
        return true;
    }
    
    /**
     * Get all MesChain configuration
     */
    private function getAllConfig() {
        $cache_key = 'meschain.config.all';
        $config = $this->cache->get($cache_key);
        
        if ($config === false) {
            $query = $this->db->query("
                SELECT `key`, `value` 
                FROM `" . DB_PREFIX . "setting` 
                WHERE `code` = 'meschain'
            ");
            
            $config = [];
            foreach ($query->rows as $row) {
                $config[$row['key']] = $row['value'];
            }
            
            $this->cache->set($cache_key, $config, 3600);
        }
        
        return $config;
    }
    
    /**
     * Get marketplace configuration
     */
    public function getMarketplace($code) {
        $cache_key = 'meschain.marketplace.' . $code;
        $marketplace = $this->cache->get($cache_key);
        
        if ($marketplace === false) {
            $query = $this->db->query("
                SELECT * 
                FROM `" . DB_PREFIX . "meschain_marketplaces` 
                WHERE `code` = '" . $this->db->escape($code) . "'
                    AND `status` = 1
                LIMIT 1
            ");
            
            $marketplace = $query->num_rows ? $query->row : null;
            
            if ($marketplace && isset($marketplace['settings'])) {
                $marketplace['settings'] = json_decode($marketplace['settings'], true) ?: [];
            }
            
            if ($marketplace && isset($marketplace['api_credentials'])) {
                $marketplace['api_credentials'] = json_decode($marketplace['api_credentials'], true) ?: [];
            }
            
            $this->cache->set($cache_key, $marketplace, 1800);
        }
        
        return $marketplace;
    }
    
    /**
     * Log sync activity
     */
    public function logSync($marketplace_id, $entity_type, $entity_id, $action, $status, $message = '', $request_data = null, $response_data = null, $execution_time = null, $memory_usage = null) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "meschain_sync_logs` 
            SET `marketplace_id` = " . (int)$marketplace_id . ",
                `entity_type` = '" . $this->db->escape($entity_type) . "',
                `entity_id` = " . (int)$entity_id . ",
                `action` = '" . $this->db->escape($action) . "',
                `status` = '" . $this->db->escape($status) . "',
                `message` = '" . $this->db->escape($message) . "',
                `request_data` = " . ($request_data ? "'" . $this->db->escape(json_encode($request_data)) . "'" : "NULL") . ",
                `response_data` = " . ($response_data ? "'" . $this->db->escape(json_encode($response_data)) . "'" : "NULL") . ",
                `execution_time` = " . ($execution_time ? (float)$execution_time : "NULL") . ",
                `memory_usage` = " . ($memory_usage ? (int)$memory_usage : "NULL") . ",
                `created_at` = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Get product mapping
     */
    public function getProductMapping($product_id, $marketplace_id) {
        $cache_key = 'meschain.product_mapping.' . $product_id . '.' . $marketplace_id;
        $mapping = $this->cache->get($cache_key);
        
        if ($mapping === false) {
            $query = $this->db->query("
                SELECT * 
                FROM `" . DB_PREFIX . "meschain_product_mappings` 
                WHERE `product_id` = " . (int)$product_id . "
                    AND `marketplace_id` = " . (int)$marketplace_id . "
                LIMIT 1
            ");
            
            $mapping = $query->num_rows ? $query->row : null;
            
            if ($mapping && isset($mapping['mapping_data'])) {
                $mapping['mapping_data'] = json_decode($mapping['mapping_data'], true) ?: [];
            }
            
            $this->cache->set($cache_key, $mapping, 1800);
        }
        
        return $mapping;
    }
    
    /**
     * Create or update product mapping
     */
    public function setProductMapping($product_id, $marketplace_id, $external_id, $external_sku = null, $mapping_data = null, $sync_status = 'pending') {
        $existing = $this->getProductMapping($product_id, $marketplace_id);
        
        if ($existing) {
            // Update existing mapping
            $this->db->query("
                UPDATE `" . DB_PREFIX . "meschain_product_mappings` 
                SET `external_id` = '" . $this->db->escape($external_id) . "',
                    `external_sku` = " . ($external_sku ? "'" . $this->db->escape($external_sku) . "'" : "NULL") . ",
                    `mapping_data` = " . ($mapping_data ? "'" . $this->db->escape(json_encode($mapping_data)) . "'" : "NULL") . ",
                    `sync_status` = '" . $this->db->escape($sync_status) . "',
                    `last_sync` = NOW(),
                    `updated_at` = NOW()
                WHERE `product_id` = " . (int)$product_id . "
                    AND `marketplace_id` = " . (int)$marketplace_id . "
            ");
        } else {
            // Create new mapping
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "meschain_product_mappings` 
                SET `product_id` = " . (int)$product_id . ",
                    `marketplace_id` = " . (int)$marketplace_id . ",
                    `external_id` = '" . $this->db->escape($external_id) . "',
                    `external_sku` = " . ($external_sku ? "'" . $this->db->escape($external_sku) . "'" : "NULL") . ",
                    `mapping_data` = " . ($mapping_data ? "'" . $this->db->escape(json_encode($mapping_data)) . "'" : "NULL") . ",
                    `sync_status` = '" . $this->db->escape($sync_status) . "',
                    `last_sync` = NOW(),
                    `created_at` = NOW(),
                    `updated_at` = NOW()
            ");
        }
        
        // Clear cache
        $cache_key = 'meschain.product_mapping.' . $product_id . '.' . $marketplace_id;
        $this->cache->delete($cache_key);
        
        return true;
    }
    
    /**
     * Get order mapping
     */
    public function getOrderMapping($order_id, $marketplace_id) {
        $cache_key = 'meschain.order_mapping.' . $order_id . '.' . $marketplace_id;
        $mapping = $this->cache->get($cache_key);
        
        if ($mapping === false) {
            $query = $this->db->query("
                SELECT * 
                FROM `" . DB_PREFIX . "meschain_order_mappings` 
                WHERE `order_id` = " . (int)$order_id . "
                    AND `marketplace_id` = " . (int)$marketplace_id . "
                LIMIT 1
            ");
            
            $mapping = $query->num_rows ? $query->row : null;
            
            if ($mapping && isset($mapping['order_data'])) {
                $mapping['order_data'] = json_decode($mapping['order_data'], true) ?: [];
            }
            
            $this->cache->set($cache_key, $mapping, 1800);
        }
        
        return $mapping;
    }
    
    /**
     * Rate limiting helper
     */
    public function checkRateLimit($marketplace, $endpoint) {
        $cache_key = 'meschain.rate_limit.' . $marketplace . '.' . md5($endpoint);
        $requests = $this->cache->get($cache_key) ?: [];
        
        $now = time();
        $window = 60; // 1 minute window
        $limit = $this->getConfig('meschain_' . $marketplace . '_rate_limit', 100);
        
        // Remove old requests
        $requests = array_filter($requests, function($timestamp) use ($now, $window) {
            return ($now - $timestamp) < $window;
        });
        
        if (count($requests) >= $limit) {
            return false; // Rate limit exceeded
        }
        
        // Add current request
        $requests[] = $now;
        $this->cache->set($cache_key, $requests, $window);
        
        return true;
    }
    
    /**
     * Validate API credentials
     */
    public function validateCredentials($marketplace_code, $credentials) {
        try {
            $marketplace = $this->getMarketplace($marketplace_code);
            
            if (!$marketplace) {
                return ['valid' => false, 'message' => 'Marketplace not found'];
            }
            
            // Load marketplace-specific API class
            $api_class = '\Meschain\\Api\\' . ucfirst($marketplace_code);
            
            if (!class_exists($api_class)) {
                return ['valid' => false, 'message' => 'API class not found'];
            }
            
            $api = new $api_class($credentials);
            return $api->testConnection();
            
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get system metrics
     */
    public function getMetrics() {
        return [
            'php_version' => PHP_VERSION,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'memory_limit' => ini_get('memory_limit'),
            'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            'database_queries' => $this->db->getQueryCount(),
            'cache_hits' => $this->cache->getHits(),
            'cache_misses' => $this->cache->getMisses()
        ];
    }
    
    /**
     * Clean expired cache
     */
    public function cleanCache($pattern = 'meschain.*') {
        $this->cache->delete($pattern);
    }
    
    /**
     * Get extension version
     */
    public function getVersion() {
        return $this->getConfig('meschain_version', '2.0.0');
    }
    
    /**
     * Check if extension is enabled
     */
    public function isEnabled() {
        return (bool)$this->getConfig('meschain_status', 0);
    }
    
    /**
     * Debug helper
     */
    public function debug($message, $data = null) {
        if ($this->getConfig('meschain_debug_mode', 0)) {
            $debug_message = '[MesChain Debug] ' . $message;
            
            if ($data !== null) {
                $debug_message .= ' | Data: ' . json_encode($data);
            }
            
            $this->log->write($debug_message);
        }
    }
}
?>
