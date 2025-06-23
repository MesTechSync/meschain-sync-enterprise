<?php
namespace MesChain\Security;

/**
 * MesChain Rate Limiter
 *
 * API rate limiting and throttling implementation
 *
 * @author MesChain Development Team
 * @version 3.0.0
 */
class RateLimiter {
    private $db;
    private $cache;
    private $config;

    // Default rate limits
    private $limits = [
        'api_global' => ['requests' => 1000, 'window' => 3600], // 1000 requests per hour
        'api_marketplace' => ['requests' => 100, 'window' => 60], // 100 requests per minute
        'login_attempts' => ['requests' => 5, 'window' => 300], // 5 attempts per 5 minutes
        'sync_operations' => ['requests' => 50, 'window' => 3600] // 50 syncs per hour
    ];

    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->cache = $registry->get('cache');
        $this->config = $registry->get('config');

        // Load custom limits from config
        $this->loadCustomLimits();
    }

    /**
     * Check if request is allowed
     *
     * @param string $key Rate limit key
     * @param string $identifier Unique identifier (IP, user ID, etc.)
     * @param string $type Limit type
     * @return bool True if allowed, false if rate limited
     */
    public function isAllowed($key, $identifier, $type = 'api_global') {
        $limit_key = $this->generateKey($key, $identifier, $type);
        $limit_config = $this->limits[$type] ?? $this->limits['api_global'];

        // Get current window start time
        $window_start = $this->getWindowStart($limit_config['window']);

        // Check cache first for performance
        $cached = $this->cache->get('rate_limit_' . $limit_key);
        if ($cached && $cached['window_start'] == $window_start) {
            if ($cached['attempts'] >= $limit_config['requests']) {
                $this->logRateLimitHit($key, $identifier, $type);
                return false;
            }

            // Increment and update cache
            $cached['attempts']++;
            $this->cache->set('rate_limit_' . $limit_key, $cached);
            return true;
        }

        // Check database
        $query = $this->db->query("
            SELECT attempts
            FROM " . DB_PREFIX . "meschain_rate_limits
            WHERE `key` = '" . $this->db->escape($limit_key) . "'
            AND window_start = '" . (int)$window_start . "'
        ");

        if ($query->num_rows) {
            $attempts = (int)$query->row['attempts'];

            if ($attempts >= $limit_config['requests']) {
                $this->logRateLimitHit($key, $identifier, $type);
                return false;
            }

            // Increment attempts
            $this->db->query("
                UPDATE " . DB_PREFIX . "meschain_rate_limits
                SET attempts = attempts + 1
                WHERE `key` = '" . $this->db->escape($limit_key) . "'
                AND window_start = '" . (int)$window_start . "'
            ");
        } else {
            // Create new rate limit record
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "meschain_rate_limits
                SET `key` = '" . $this->db->escape($limit_key) . "',
                    attempts = 1,
                    window_start = '" . (int)$window_start . "'
            ");
        }

        // Update cache
        $this->cache->set('rate_limit_' . $limit_key, [
            'attempts' => ($attempts ?? 0) + 1,
            'window_start' => $window_start
        ]);

        return true;
    }

    /**
     * Get remaining requests
     *
     * @param string $key Rate limit key
     * @param string $identifier Unique identifier
     * @param string $type Limit type
     * @return array Limit information
     */
    public function getRemainingRequests($key, $identifier, $type = 'api_global') {
        $limit_key = $this->generateKey($key, $identifier, $type);
        $limit_config = $this->limits[$type] ?? $this->limits['api_global'];
        $window_start = $this->getWindowStart($limit_config['window']);

        // Check cache first
        $cached = $this->cache->get('rate_limit_' . $limit_key);
        if ($cached && $cached['window_start'] == $window_start) {
            $attempts = $cached['attempts'];
        } else {
            // Check database
            $query = $this->db->query("
                SELECT attempts
                FROM " . DB_PREFIX . "meschain_rate_limits
                WHERE `key` = '" . $this->db->escape($limit_key) . "'
                AND window_start = '" . (int)$window_start . "'
            ");

            $attempts = $query->num_rows ? (int)$query->row['attempts'] : 0;
        }

        return [
            'limit' => $limit_config['requests'],
            'remaining' => max(0, $limit_config['requests'] - $attempts),
            'reset' => $window_start + $limit_config['window'],
            'window' => $limit_config['window']
        ];
    }

    /**
     * Reset rate limit
     *
     * @param string $key Rate limit key
     * @param string $identifier Unique identifier
     * @param string $type Limit type
     */
    public function reset($key, $identifier, $type = 'api_global') {
        $limit_key = $this->generateKey($key, $identifier, $type);

        // Delete from database
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "meschain_rate_limits
            WHERE `key` = '" . $this->db->escape($limit_key) . "'
        ");

        // Clear cache
        $this->cache->delete('rate_limit_' . $limit_key);
    }

    /**
     * Clean expired rate limits
     */
    public function cleanExpired() {
        // Delete records older than 24 hours
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "meschain_rate_limits
            WHERE window_start < '" . (int)(time() - 86400) . "'
        ");
    }

    /**
     * Set custom limit
     *
     * @param string $type Limit type
     * @param int $requests Number of requests
     * @param int $window Time window in seconds
     */
    public function setLimit($type, $requests, $window) {
        $this->limits[$type] = [
            'requests' => $requests,
            'window' => $window
        ];
    }

    /**
     * Generate rate limit key
     *
     * @param string $key Base key
     * @param string $identifier Unique identifier
     * @param string $type Limit type
     * @return string Generated key
     */
    private function generateKey($key, $identifier, $type) {
        return md5($type . ':' . $key . ':' . $identifier);
    }

    /**
     * Get window start time
     *
     * @param int $window Window size in seconds
     * @return int Window start timestamp
     */
    private function getWindowStart($window) {
        $now = time();
        return $now - ($now % $window);
    }

    /**
     * Load custom limits from configuration
     */
    private function loadCustomLimits() {
        // Load marketplace-specific limits
        $marketplaces = ['hepsiburada', 'trendyol', 'amazon', 'ebay', 'n11'];

        foreach ($marketplaces as $marketplace) {
            $requests = $this->config->get('meschain_' . $marketplace . '_rate_limit_requests');
            $window = $this->config->get('meschain_' . $marketplace . '_rate_limit_window');

            if ($requests && $window) {
                $this->limits['api_' . $marketplace] = [
                    'requests' => (int)$requests,
                    'window' => (int)$window
                ];
            }
        }
    }

    /**
     * Log rate limit hit
     *
     * @param string $key Rate limit key
     * @param string $identifier Unique identifier
     * @param string $type Limit type
     */
    private function logRateLimitHit($key, $identifier, $type) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_logs
            SET log_type = 'rate_limit',
                log_level = 'warning',
                log_action = 'rate_limit_exceeded',
                log_message = '" . $this->db->escape(json_encode([
                    'key' => $key,
                    'identifier' => $identifier,
                    'type' => $type,
                    'limit' => $this->limits[$type]
                ])) . "',
                marketplace = '',
                date_added = NOW()
        ");
    }

    /**
     * Get rate limit statistics
     *
     * @return array Statistics
     */
    public function getStatistics() {
        $stats = [];

        // Get current active rate limits
        $query = $this->db->query("
            SELECT
                COUNT(DISTINCT `key`) as active_limits,
                SUM(attempts) as total_attempts
            FROM " . DB_PREFIX . "meschain_rate_limits
            WHERE window_start >= '" . (int)(time() - 3600) . "'
        ");

        $stats['active_limits'] = (int)$query->row['active_limits'];
        $stats['total_attempts'] = (int)$query->row['total_attempts'];

        // Get rate limit hits in last hour
        $query = $this->db->query("
            SELECT COUNT(*) as hits
            FROM " . DB_PREFIX . "meschain_logs
            WHERE log_type = 'rate_limit'
            AND log_action = 'rate_limit_exceeded'
            AND date_added > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");

        $stats['hits_last_hour'] = (int)$query->row['hits'];

        // Get top limited keys
        $query = $this->db->query("
            SELECT
                `key`,
                MAX(attempts) as max_attempts
            FROM " . DB_PREFIX . "meschain_rate_limits
            WHERE window_start >= '" . (int)(time() - 3600) . "'
            GROUP BY `key`
            ORDER BY max_attempts DESC
            LIMIT 5
        ");

        $stats['top_limited'] = $query->rows;

        return $stats;
    }
}
