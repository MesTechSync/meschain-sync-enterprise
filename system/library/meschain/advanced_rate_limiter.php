<?php
/**
 * MesChain-Sync Advanced Rate Limiter
 * Multi-tier rate limiting with sliding window algorithm
 * 
 * @version 3.0.0
 * @date December 2024
 * @author MesChain Development Team
 */

class MeschainAdvancedRateLimiter {
    
    private $registry;
    private $cache;
    private $db;
    private $config;
    private $logger;
    
    // Rate limit configurations
    private $rateLimits = [
        'global' => [
            'requests' => 10000,
            'window' => 3600,
            'burst' => 100
        ],
        'user' => [
            'requests' => 1000,
            'window' => 3600,
            'burst' => 50
        ],
        'marketplace' => [
            'amazon' => ['requests' => 500, 'window' => 3600, 'burst' => 25],
            'ebay' => ['requests' => 400, 'window' => 3600, 'burst' => 20],
            'trendyol' => ['requests' => 600, 'window' => 3600, 'burst' => 30],
            'n11' => ['requests' => 300, 'window' => 3600, 'burst' => 15],
            'hepsiburada' => ['requests' => 400, 'window' => 3600, 'burst' => 20],
            'ozon' => ['requests' => 250, 'window' => 3600, 'burst' => 12]
        ],
        'endpoint' => [
            'orders' => ['requests' => 200, 'window' => 300, 'burst' => 10],
            'products' => ['requests' => 500, 'window' => 600, 'burst' => 25],
            'inventory' => ['requests' => 300, 'window' => 300, 'burst' => 15],
            'analytics' => ['requests' => 100, 'window' => 600, 'burst' => 5],
            'reports' => ['requests' => 50, 'window' => 300, 'burst' => 3],
            'webhooks' => ['requests' => 1000, 'window' => 60, 'burst' => 50]
        ],
        'ip' => [
            'requests' => 2000,
            'window' => 3600,
            'burst' => 100,
            'suspicious_threshold' => 5000
        ]
    ];
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->cache = $registry->get('cache');
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        
        // Initialize logger
        require_once(DIR_SYSTEM . 'library/meschain/logger.php');
        $this->logger = new MeschainLogger('rate_limiter');
        
        $this->initializeRateLimitTables();
    }
    
    /**
     * Main rate limit check with multi-tier validation
     */
    public function checkRateLimit($identifier, $type, $marketplace = null, $endpoint = null) {
        try {
            $checks = [
                'global' => $this->checkGlobalRateLimit(),
                'user' => $this->checkUserRateLimit($identifier),
                'ip' => $this->checkIPRateLimit($this->getClientIP()),
                'marketplace' => $marketplace ? $this->checkMarketplaceRateLimit($identifier, $marketplace) : true,
                'endpoint' => $endpoint ? $this->checkEndpointRateLimit($identifier, $endpoint) : true
            ];
            
            foreach ($checks as $checkType => $result) {
                if (!$result['allowed']) {
                    $this->logRateLimitViolation($identifier, $checkType, $result);
                    throw new RateLimitException($result['message'], $result['retry_after']);
                }
            }
            
            // All checks passed, update counters
            $this->updateRateLimitCounters($identifier, $type, $marketplace, $endpoint);
            
            return true;
            
        } catch (Exception $e) {
            $this->logger->error("Rate limit check failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Check global rate limits
     */
    private function checkGlobalRateLimit() {
        $limit = $this->rateLimits['global'];
        $key = 'global_rate_limit';
        
        $current = $this->getSlidingWindowCount($key, $limit['window']);
        
        if ($current >= $limit['requests']) {
            return [
                'allowed' => false,
                'message' => 'Global rate limit exceeded',
                'retry_after' => $this->calculateRetryAfter($key, $limit['window'])
            ];
        }
        
        return ['allowed' => true, 'remaining' => $limit['requests'] - $current];
    }
    
    /**
     * Check user-specific rate limits
     */
    private function checkUserRateLimit($userId) {
        $limit = $this->rateLimits['user'];
        $key = "user_rate_limit_{$userId}";
        
        $current = $this->getSlidingWindowCount($key, $limit['window']);
        
        // Check burst limit
        $burstKey = "{$key}_burst";
        $burstCount = $this->getSlidingWindowCount($burstKey, 60); // 1 minute burst window
        
        if ($burstCount >= $limit['burst']) {
            return [
                'allowed' => false,
                'message' => 'User burst rate limit exceeded',
                'retry_after' => 60
            ];
        }
        
        if ($current >= $limit['requests']) {
            return [
                'allowed' => false,
                'message' => 'User rate limit exceeded',
                'retry_after' => $this->calculateRetryAfter($key, $limit['window'])
            ];
        }
        
        return ['allowed' => true, 'remaining' => $limit['requests'] - $current];
    }
    
    /**
     * Check IP-based rate limits
     */
    private function checkIPRateLimit($ip) {
        $limit = $this->rateLimits['ip'];
        $key = "ip_rate_limit_" . md5($ip);
        
        $current = $this->getSlidingWindowCount($key, $limit['window']);
        
        // Check for suspicious activity
        if ($current >= $limit['suspicious_threshold']) {
            $this->flagSuspiciousIP($ip, $current);
        }
        
        if ($current >= $limit['requests']) {
            return [
                'allowed' => false,
                'message' => 'IP rate limit exceeded',
                'retry_after' => $this->calculateRetryAfter($key, $limit['window'])
            ];
        }
        
        return ['allowed' => true, 'remaining' => $limit['requests'] - $current];
    }
    
    /**
     * Check marketplace-specific rate limits
     */
    private function checkMarketplaceRateLimit($userId, $marketplace) {
        if (!isset($this->rateLimits['marketplace'][$marketplace])) {
            return ['allowed' => true];
        }
        
        $limit = $this->rateLimits['marketplace'][$marketplace];
        $key = "marketplace_rate_limit_{$marketplace}_{$userId}";
        
        $current = $this->getSlidingWindowCount($key, $limit['window']);
        
        if ($current >= $limit['requests']) {
            return [
                'allowed' => false,
                'message' => "Marketplace rate limit exceeded for {$marketplace}",
                'retry_after' => $this->calculateRetryAfter($key, $limit['window'])
            ];
        }
        
        return ['allowed' => true, 'remaining' => $limit['requests'] - $current];
    }
    
    /**
     * Check endpoint-specific rate limits
     */
    private function checkEndpointRateLimit($userId, $endpoint) {
        if (!isset($this->rateLimits['endpoint'][$endpoint])) {
            return ['allowed' => true];
        }
        
        $limit = $this->rateLimits['endpoint'][$endpoint];
        $key = "endpoint_rate_limit_{$endpoint}_{$userId}";
        
        $current = $this->getSlidingWindowCount($key, $limit['window']);
        
        if ($current >= $limit['requests']) {
            return [
                'allowed' => false,
                'message' => "Endpoint rate limit exceeded for {$endpoint}",
                'retry_after' => $this->calculateRetryAfter($key, $limit['window'])
            ];
        }
        
        return ['allowed' => true, 'remaining' => $limit['requests'] - $current];
    }
    
    /**
     * Get sliding window count using Redis-like approach
     */
    private function getSlidingWindowCount($key, $windowSize) {
        $now = time();
        $windowStart = $now - $windowSize;
        
        // Get stored timestamps
        $timestamps = $this->cache->get($key) ?: [];
        
        // Filter out old timestamps
        $validTimestamps = array_filter($timestamps, function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        return count($validTimestamps);
    }
    
    /**
     * Update rate limit counters
     */
    private function updateRateLimitCounters($identifier, $type, $marketplace, $endpoint) {
        $now = time();
        
        // Update global counter
        $this->addTimestamp('global_rate_limit', $now);
        
        // Update user counter
        $this->addTimestamp("user_rate_limit_{$identifier}", $now);
        $this->addTimestamp("user_rate_limit_{$identifier}_burst", $now);
        
        // Update IP counter
        $ip = $this->getClientIP();
        $this->addTimestamp("ip_rate_limit_" . md5($ip), $now);
        
        // Update marketplace counter
        if ($marketplace) {
            $this->addTimestamp("marketplace_rate_limit_{$marketplace}_{$identifier}", $now);
        }
        
        // Update endpoint counter
        if ($endpoint) {
            $this->addTimestamp("endpoint_rate_limit_{$endpoint}_{$identifier}", $now);
        }
        
        // Log usage statistics
        $this->logRateLimitUsage($identifier, $type, $marketplace, $endpoint);
    }
    
    /**
     * Add timestamp to sliding window
     */
    private function addTimestamp($key, $timestamp) {
        $timestamps = $this->cache->get($key) ?: [];
        $timestamps[] = $timestamp;
        
        // Keep only recent timestamps (2x window size for safety)
        $maxAge = time() - (3600 * 2); // 2 hours
        $timestamps = array_filter($timestamps, function($ts) use ($maxAge) {
            return $ts > $maxAge;
        });
        
        $this->cache->set($key, array_values($timestamps), 7200); // 2 hours TTL
    }
    
    /**
     * Calculate retry after time
     */
    private function calculateRetryAfter($key, $windowSize) {
        $timestamps = $this->cache->get($key) ?: [];
        if (empty($timestamps)) {
            return 1;
        }
        
        $oldestTimestamp = min($timestamps);
        $retryAfter = max(1, ($oldestTimestamp + $windowSize) - time());
        
        return $retryAfter;
    }
    
    /**
     * Flag suspicious IP address
     */
    private function flagSuspiciousIP($ip, $requestCount) {
        $this->logger->warning("Suspicious activity detected", [
            'ip' => $ip,
            'request_count' => $requestCount,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        // Store in database for analysis
        $this->db->query("
            INSERT INTO oc_meschain_suspicious_ips 
            (ip_address, request_count, detected_at, user_agent) 
            VALUES ('" . $this->db->escape($ip) . "', 
                    '" . (int)$requestCount . "', 
                    NOW(), 
                    '" . $this->db->escape($_SERVER['HTTP_USER_AGENT'] ?? '') . "')
            ON DUPLICATE KEY UPDATE 
                request_count = request_count + 1,
                last_detected = NOW()
        ");
    }
    
    /**
     * Log rate limit violation
     */
    private function logRateLimitViolation($identifier, $checkType, $result) {
        $this->logger->warning("Rate limit violation", [
            'identifier' => $identifier,
            'check_type' => $checkType,
            'message' => $result['message'],
            'retry_after' => $result['retry_after'],
            'ip' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        // Store violation in database
        $this->db->query("
            INSERT INTO oc_meschain_rate_limit_violations 
            (identifier, check_type, message, retry_after, ip_address, user_agent, created_at) 
            VALUES ('" . $this->db->escape($identifier) . "', 
                    '" . $this->db->escape($checkType) . "',
                    '" . $this->db->escape($result['message']) . "', 
                    '" . (int)$result['retry_after'] . "',
                    '" . $this->db->escape($this->getClientIP()) . "',
                    '" . $this->db->escape($_SERVER['HTTP_USER_AGENT'] ?? '') . "',
                    NOW())
        ");
    }
    
    /**
     * Log rate limit usage statistics
     */
    private function logRateLimitUsage($identifier, $type, $marketplace, $endpoint) {
        // Sample 10% of requests for statistics
        if (rand(1, 10) === 1) {
            $this->db->query("
                INSERT INTO oc_meschain_rate_limit_stats 
                (identifier, type, marketplace, endpoint, request_count, created_at) 
                VALUES ('" . $this->db->escape($identifier) . "', 
                        '" . $this->db->escape($type) . "',
                        '" . $this->db->escape($marketplace ?: '') . "', 
                        '" . $this->db->escape($endpoint ?: '') . "',
                        1,
                        NOW())
                ON DUPLICATE KEY UPDATE 
                    request_count = request_count + 1,
                    updated_at = NOW()
            ");
        }
    }
    
    /**
     * Get current rate limit status for user
     */
    public function getRateLimitStatus($identifier, $marketplace = null) {
        $status = [
            'global' => $this->getGlobalStatus(),
            'user' => $this->getUserStatus($identifier),
            'ip' => $this->getIPStatus($this->getClientIP())
        ];
        
        if ($marketplace) {
            $status['marketplace'] = $this->getMarketplaceStatus($identifier, $marketplace);
        }
        
        return $status;
    }
    
    /**
     * Get global rate limit status
     */
    private function getGlobalStatus() {
        $limit = $this->rateLimits['global'];
        $current = $this->getSlidingWindowCount('global_rate_limit', $limit['window']);
        
        return [
            'limit' => $limit['requests'],
            'remaining' => max(0, $limit['requests'] - $current),
            'reset_at' => time() + $limit['window']
        ];
    }
    
    /**
     * Get user rate limit status
     */
    private function getUserStatus($userId) {
        $limit = $this->rateLimits['user'];
        $current = $this->getSlidingWindowCount("user_rate_limit_{$userId}", $limit['window']);
        
        return [
            'limit' => $limit['requests'],
            'remaining' => max(0, $limit['requests'] - $current),
            'reset_at' => time() + $limit['window']
        ];
    }
    
    /**
     * Get IP rate limit status
     */
    private function getIPStatus($ip) {
        $limit = $this->rateLimits['ip'];
        $current = $this->getSlidingWindowCount("ip_rate_limit_" . md5($ip), $limit['window']);
        
        return [
            'limit' => $limit['requests'],
            'remaining' => max(0, $limit['requests'] - $current),
            'reset_at' => time() + $limit['window']
        ];
    }
    
    /**
     * Get marketplace rate limit status
     */
    private function getMarketplaceStatus($userId, $marketplace) {
        if (!isset($this->rateLimits['marketplace'][$marketplace])) {
            return ['unlimited' => true];
        }
        
        $limit = $this->rateLimits['marketplace'][$marketplace];
        $current = $this->getSlidingWindowCount("marketplace_rate_limit_{$marketplace}_{$userId}", $limit['window']);
        
        return [
            'limit' => $limit['requests'],
            'remaining' => max(0, $limit['requests'] - $current),
            'reset_at' => time() + $limit['window']
        ];
    }
    
    /**
     * Initialize rate limiting database tables
     */
    private function initializeRateLimitTables() {
        // Rate limit violations table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS oc_meschain_rate_limit_violations (
                id int(11) NOT NULL AUTO_INCREMENT,
                identifier varchar(255) NOT NULL,
                check_type varchar(50) NOT NULL,
                message text NOT NULL,
                retry_after int(11) NOT NULL,
                ip_address varchar(45) NOT NULL,
                user_agent text,
                created_at datetime NOT NULL,
                PRIMARY KEY (id),
                KEY idx_identifier (identifier),
                KEY idx_ip_address (ip_address),
                KEY idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
        
        // Suspicious IPs table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS oc_meschain_suspicious_ips (
                id int(11) NOT NULL AUTO_INCREMENT,
                ip_address varchar(45) NOT NULL,
                request_count int(11) NOT NULL DEFAULT 0,
                detected_at datetime NOT NULL,
                last_detected datetime,
                user_agent text,
                is_blocked tinyint(1) DEFAULT 0,
                PRIMARY KEY (id),
                UNIQUE KEY idx_ip (ip_address),
                KEY idx_detected_at (detected_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
        
        // Rate limit statistics table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS oc_meschain_rate_limit_stats (
                id int(11) NOT NULL AUTO_INCREMENT,
                identifier varchar(255) NOT NULL,
                type varchar(50) NOT NULL,
                marketplace varchar(50),
                endpoint varchar(100),
                request_count int(11) NOT NULL DEFAULT 0,
                created_at datetime NOT NULL,
                updated_at datetime,
                PRIMARY KEY (id),
                UNIQUE KEY idx_unique_stats (identifier, type, marketplace, endpoint, DATE(created_at)),
                KEY idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * Reset rate limits for specific identifier
     */
    public function resetRateLimit($identifier, $type = 'user') {
        $patterns = [
            "user_rate_limit_{$identifier}",
            "user_rate_limit_{$identifier}_burst"
        ];
        
        foreach ($patterns as $pattern) {
            $this->cache->delete($pattern);
        }
        
        $this->logger->info("Rate limit reset for identifier: {$identifier}");
    }
    
    /**
     * Block IP address
     */
    public function blockIP($ip, $reason = 'Manual block') {
        $this->db->query("
            UPDATE oc_meschain_suspicious_ips 
            SET is_blocked = 1, 
                last_detected = NOW()
            WHERE ip_address = '" . $this->db->escape($ip) . "'
        ");
        
        $this->logger->warning("IP address blocked", [
            'ip' => $ip,
            'reason' => $reason
        ]);
    }
    
    /**
     * Check if IP is blocked
     */
    public function isIPBlocked($ip) {
        $result = $this->db->query("
            SELECT is_blocked 
            FROM oc_meschain_suspicious_ips 
            WHERE ip_address = '" . $this->db->escape($ip) . "' 
            AND is_blocked = 1
        ");
        
        return $result->num_rows > 0;
    }
    
    /**
     * Get rate limit statistics
     */
    public function getStatistics($timeframe = '24h') {
        $timeframeSql = '';
        switch ($timeframe) {
            case '1h':
                $timeframeSql = 'DATE_SUB(NOW(), INTERVAL 1 HOUR)';
                break;
            case '24h':
                $timeframeSql = 'DATE_SUB(NOW(), INTERVAL 24 HOUR)';
                break;
            case '7d':
                $timeframeSql = 'DATE_SUB(NOW(), INTERVAL 7 DAY)';
                break;
            default:
                $timeframeSql = 'DATE_SUB(NOW(), INTERVAL 24 HOUR)';
        }
        
        $stats = [];
        
        // Violation statistics
        $result = $this->db->query("
            SELECT check_type, COUNT(*) as violation_count
            FROM oc_meschain_rate_limit_violations 
            WHERE created_at >= {$timeframeSql}
            GROUP BY check_type
        ");
        
        $stats['violations'] = [];
        foreach ($result->rows as $row) {
            $stats['violations'][$row['check_type']] = $row['violation_count'];
        }
        
        // Usage statistics
        $result = $this->db->query("
            SELECT type, marketplace, SUM(request_count) as total_requests
            FROM oc_meschain_rate_limit_stats 
            WHERE created_at >= {$timeframeSql}
            GROUP BY type, marketplace
        ");
        
        $stats['usage'] = [];
        foreach ($result->rows as $row) {
            $key = $row['marketplace'] ? "{$row['type']}_{$row['marketplace']}" : $row['type'];
            $stats['usage'][$key] = $row['total_requests'];
        }
        
        return $stats;
    }
}

/**
 * Rate Limit Exception
 */
class RateLimitException extends Exception {
    private $retryAfter;
    
    public function __construct($message, $retryAfter = 60, $code = 429, Exception $previous = null) {
        $this->retryAfter = $retryAfter;
        parent::__construct($message, $code, $previous);
    }
    
    public function getRetryAfter() {
        return $this->retryAfter;
    }
}
