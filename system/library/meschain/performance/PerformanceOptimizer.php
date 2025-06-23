<?php
namespace MesChain\Performance;

/**
 * MesChain Performance Optimizer
 *
 * Advanced performance optimization for marketplace operations
 * Features: Query optimization, Caching, Batch processing, Resource monitoring
 *
 * @author MesChain Development Team
 * @version 3.0.0
 */
class PerformanceOptimizer {
    private $db;
    private $cache;
    private $config;
    private $metrics = [];
    private $start_time;
    private $memory_start;

    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->cache = $registry->get('cache');
        $this->config = $registry->get('config');
        $this->start_time = microtime(true);
        $this->memory_start = memory_get_usage();
    }

    /**
     * Optimize database query
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @param int $cache_time Cache duration in seconds
     * @return mixed Query result
     */
    public function optimizedQuery($sql, $params = [], $cache_time = 3600) {
        // Generate cache key
        $cache_key = 'meschain_query_' . md5($sql . serialize($params));

        // Check cache first
        $cached_result = $this->cache->get($cache_key);
        if ($cached_result !== false) {
            $this->metrics['cache_hits'] = ($this->metrics['cache_hits'] ?? 0) + 1;
            return $cached_result;
        }

        // Execute query with timing
        $query_start = microtime(true);

        // Prepare statement for security and performance
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $sql = str_replace(':' . $key, "'" . $this->db->escape($value) . "'", $sql);
            }
        }

        $result = $this->db->query($sql);

        $query_time = microtime(true) - $query_start;
        $this->metrics['total_query_time'] = ($this->metrics['total_query_time'] ?? 0) + $query_time;
        $this->metrics['query_count'] = ($this->metrics['query_count'] ?? 0) + 1;

        // Log slow queries
        if ($query_time > 1.0) {
            $this->logSlowQuery($sql, $query_time);
        }

        // Cache result
        if ($cache_time > 0) {
            $this->cache->set($cache_key, $result, $cache_time);
        }

        return $result;
    }

    /**
     * Batch process items to reduce database calls
     *
     * @param array $items Items to process
     * @param callable $processor Processing function
     * @param int $batch_size Batch size
     * @return array Results
     */
    public function batchProcess($items, $processor, $batch_size = 100) {
        $results = [];
        $total_items = count($items);

        for ($i = 0; $i < $total_items; $i += $batch_size) {
            $batch = array_slice($items, $i, $batch_size);

            // Process batch
            $batch_start = microtime(true);
            $batch_results = call_user_func($processor, $batch);
            $batch_time = microtime(true) - $batch_start;

            // Merge results
            $results = array_merge($results, $batch_results);

            // Track metrics
            $this->metrics['batch_count'] = ($this->metrics['batch_count'] ?? 0) + 1;
            $this->metrics['batch_time'] = ($this->metrics['batch_time'] ?? 0) + $batch_time;

            // Prevent memory overflow
            if (memory_get_usage() > $this->getMemoryLimit() * 0.8) {
                $this->freeMemory();
            }
        }

        return $results;
    }

    /**
     * Cache marketplace data with smart invalidation
     *
     * @param string $marketplace Marketplace name
     * @param string $type Data type
     * @param mixed $data Data to cache
     * @param int $ttl Time to live in seconds
     */
    public function cacheMarketplaceData($marketplace, $type, $data, $ttl = 3600) {
        $cache_key = "meschain_{$marketplace}_{$type}";

        // Add metadata for smart invalidation
        $cache_data = [
            'data' => $data,
            'timestamp' => time(),
            'marketplace' => $marketplace,
            'type' => $type
        ];

        $this->cache->set($cache_key, $cache_data, $ttl);

        // Track cache usage
        $this->metrics['cache_writes'] = ($this->metrics['cache_writes'] ?? 0) + 1;
    }

    /**
     * Get cached marketplace data
     *
     * @param string $marketplace Marketplace name
     * @param string $type Data type
     * @return mixed|false Cached data or false
     */
    public function getCachedMarketplaceData($marketplace, $type) {
        $cache_key = "meschain_{$marketplace}_{$type}";
        $cached = $this->cache->get($cache_key);

        if ($cached !== false) {
            $this->metrics['cache_hits'] = ($this->metrics['cache_hits'] ?? 0) + 1;
            return $cached['data'];
        }

        $this->metrics['cache_misses'] = ($this->metrics['cache_misses'] ?? 0) + 1;
        return false;
    }

    /**
     * Invalidate cache by pattern
     *
     * @param string $pattern Cache key pattern
     */
    public function invalidateCache($pattern) {
        // This is a simplified version - actual implementation would depend on cache backend
        $this->cache->delete($pattern);
        $this->metrics['cache_invalidations'] = ($this->metrics['cache_invalidations'] ?? 0) + 1;
    }

    /**
     * Optimize memory usage
     */
    public function optimizeMemory() {
        // Force garbage collection
        if (function_exists('gc_collect_cycles')) {
            $collected = gc_collect_cycles();
            $this->metrics['gc_cycles'] = ($this->metrics['gc_cycles'] ?? 0) + $collected;
        }

        // Clear internal caches
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    /**
     * Get performance metrics
     *
     * @return array Performance metrics
     */
    public function getMetrics() {
        $current_time = microtime(true);
        $current_memory = memory_get_usage();

        return array_merge($this->metrics, [
            'execution_time' => $current_time - $this->start_time,
            'memory_usage' => $current_memory - $this->memory_start,
            'peak_memory' => memory_get_peak_usage(),
            'avg_query_time' => isset($this->metrics['query_count']) && $this->metrics['query_count'] > 0
                ? $this->metrics['total_query_time'] / $this->metrics['query_count']
                : 0,
            'cache_hit_rate' => $this->calculateCacheHitRate()
        ]);
    }

    /**
     * Enable query profiling
     */
    public function enableProfiling() {
        $this->db->query("SET profiling = 1");
    }

    /**
     * Get query profile
     *
     * @return array Query profile data
     */
    public function getQueryProfile() {
        $result = $this->db->query("SHOW PROFILES");
        $profiles = [];

        if ($result->num_rows) {
            foreach ($result->rows as $row) {
                $profiles[] = [
                    'query_id' => $row['Query_ID'],
                    'duration' => $row['Duration'],
                    'query' => $row['Query']
                ];
            }
        }

        return $profiles;
    }

    /**
     * Optimize table
     *
     * @param string $table Table name
     */
    public function optimizeTable($table) {
        $this->db->query("OPTIMIZE TABLE `" . DB_PREFIX . $table . "`");
    }

    /**
     * Analyze table for optimization
     *
     * @param string $table Table name
     * @return array Analysis results
     */
    public function analyzeTable($table) {
        $result = $this->db->query("ANALYZE TABLE `" . DB_PREFIX . $table . "`");
        return $result->rows;
    }

    /**
     * Create index if not exists
     *
     * @param string $table Table name
     * @param string $index_name Index name
     * @param array $columns Column names
     */
    public function createIndex($table, $index_name, $columns) {
        $table_name = DB_PREFIX . $table;

        // Check if index exists
        $result = $this->db->query("
            SHOW INDEX FROM `$table_name`
            WHERE Key_name = '" . $this->db->escape($index_name) . "'
        ");

        if (!$result->num_rows) {
            $columns_str = implode('`, `', $columns);
            $this->db->query("
                CREATE INDEX `$index_name`
                ON `$table_name` (`$columns_str`)
            ");
        }
    }

    /**
     * Log slow query
     *
     * @param string $sql SQL query
     * @param float $execution_time Execution time
     */
    private function logSlowQuery($sql, $execution_time) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_slow_queries
            SET query_sql = '" . $this->db->escape($sql) . "',
                execution_time = " . (float)$execution_time . ",
                date_added = NOW()
        ");
    }

    /**
     * Calculate cache hit rate
     *
     * @return float Cache hit rate percentage
     */
    private function calculateCacheHitRate() {
        $hits = $this->metrics['cache_hits'] ?? 0;
        $misses = $this->metrics['cache_misses'] ?? 0;
        $total = $hits + $misses;

        return $total > 0 ? ($hits / $total) * 100 : 0;
    }

    /**
     * Get memory limit in bytes
     *
     * @return int Memory limit
     */
    private function getMemoryLimit() {
        $limit = ini_get('memory_limit');

        if (preg_match('/^(\d+)(.)$/', $limit, $matches)) {
            $value = $matches[1];
            switch (strtoupper($matches[2])) {
                case 'G':
                    $value *= 1024;
                case 'M':
                    $value *= 1024;
                case 'K':
                    $value *= 1024;
            }
            return $value;
        }

        return 134217728; // Default 128MB
    }

    /**
     * Free memory
     */
    private function freeMemory() {
        // Clear any internal caches
        $this->metrics = [
            'cache_hits' => $this->metrics['cache_hits'] ?? 0,
            'cache_misses' => $this->metrics['cache_misses'] ?? 0,
            'cache_writes' => $this->metrics['cache_writes'] ?? 0
        ];

        // Force garbage collection
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    /**
     * Create performance report
     *
     * @return array Performance report
     */
    public function createPerformanceReport() {
        $metrics = $this->getMetrics();

        return [
            'summary' => [
                'total_execution_time' => round($metrics['execution_time'], 3) . 's',
                'memory_usage' => $this->formatBytes($metrics['memory_usage']),
                'peak_memory' => $this->formatBytes($metrics['peak_memory']),
                'total_queries' => $metrics['query_count'] ?? 0,
                'avg_query_time' => round($metrics['avg_query_time'] * 1000, 2) . 'ms',
                'cache_hit_rate' => round($metrics['cache_hit_rate'], 2) . '%'
            ],
            'recommendations' => $this->generateRecommendations($metrics),
            'detailed_metrics' => $metrics
        ];
    }

    /**
     * Generate performance recommendations
     *
     * @param array $metrics Performance metrics
     * @return array Recommendations
     */
    private function generateRecommendations($metrics) {
        $recommendations = [];

        // Check query performance
        if (isset($metrics['avg_query_time']) && $metrics['avg_query_time'] > 0.1) {
            $recommendations[] = 'Consider optimizing database queries - average query time is high';
        }

        // Check cache hit rate
        if ($metrics['cache_hit_rate'] < 80) {
            $recommendations[] = 'Cache hit rate is low - consider increasing cache TTL or preloading frequently accessed data';
        }

        // Check memory usage
        if ($metrics['memory_usage'] > $this->getMemoryLimit() * 0.7) {
            $recommendations[] = 'High memory usage detected - consider batch processing or increasing memory limit';
        }

        return $recommendations;
    }

    /**
     * Format bytes to human readable
     *
     * @param int $bytes Bytes
     * @return string Formatted string
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
