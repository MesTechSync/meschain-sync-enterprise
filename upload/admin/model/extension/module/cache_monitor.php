<?php
/**
 * Cache Monitor Model
 * MesChain-Sync OpenCart Extension
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0
 * @author MesChain Development Team
 */

class ModelExtensionModuleCacheMonitor extends Model {
    
    private $cache_stats_table = 'meschain_cache_stats';
    private $cache_directory;
    
    public function __construct($registry) {
        parent::__construct($registry);
        $this->cache_directory = DIR_CACHE;
    }
    
    /**
     * Initialize cache monitoring tables
     *
     * @return void
     */
    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->cache_stats_table . "` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `cache_type` varchar(50) NOT NULL,
                `cache_key` varchar(255) NOT NULL,
                `size_bytes` bigint(20) DEFAULT 0,
                `hit_count` int(11) DEFAULT 0,
                `miss_count` int(11) DEFAULT 0,
                `last_access` datetime,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `expires_at` datetime,
                PRIMARY KEY (`id`),
                UNIQUE KEY `cache_key_unique` (`cache_type`, `cache_key`),
                KEY `cache_type` (`cache_type`),
                KEY `last_access` (`last_access`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }
    
    /**
     * Get cache statistics
     *
     * @return array Cache statistics
     */
    public function getCacheStatistics() {
        $stats = [
            'file_cache' => $this->getFileCacheStats(),
            'database_cache' => $this->getDatabaseCacheStats(),
            'memory_usage' => $this->getMemoryUsage(),
            'total_size' => 0,
            'total_files' => 0
        ];
        
        $stats['total_size'] = $stats['file_cache']['total_size'];
        $stats['total_files'] = $stats['file_cache']['total_files'];
        
        return $stats;
    }
    
    /**
     * Get file cache statistics
     *
     * @return array File cache stats
     */
    private function getFileCacheStats() {
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'total_size_formatted' => '0 B',
            'by_type' => []
        ];
        
        if (is_dir($this->cache_directory)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->cache_directory)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size = $file->getSize();
                    $ext = $file->getExtension();
                    
                    $stats['total_files']++;
                    $stats['total_size'] += $size;
                    
                    if (!isset($stats['by_type'][$ext])) {
                        $stats['by_type'][$ext] = [
                            'count' => 0,
                            'size' => 0
                        ];
                    }
                    
                    $stats['by_type'][$ext]['count']++;
                    $stats['by_type'][$ext]['size'] += $size;
                }
            }
        }
        
        $stats['total_size_formatted'] = $this->formatBytes($stats['total_size']);
        
        return $stats;
    }
    
    /**
     * Get database cache statistics
     *
     * @return array Database cache stats
     */
    private function getDatabaseCacheStats() {
        $stats = [
            'total_entries' => 0,
            'by_type' => [],
            'hit_miss_ratio' => 0
        ];
        
        $query = $this->db->query("
            SELECT 
                cache_type,
                COUNT(*) as count,
                SUM(size_bytes) as total_size,
                SUM(hit_count) as total_hits,
                SUM(miss_count) as total_misses
            FROM " . DB_PREFIX . $this->cache_stats_table . "
            GROUP BY cache_type
        ");
        
        $total_hits = 0;
        $total_misses = 0;
        
        foreach ($query->rows as $row) {
            $stats['total_entries'] += (int)$row['count'];
            $total_hits += (int)$row['total_hits'];
            $total_misses += (int)$row['total_misses'];
            
            $stats['by_type'][$row['cache_type']] = [
                'count' => (int)$row['count'],
                'size' => (int)$row['total_size'],
                'hits' => (int)$row['total_hits'],
                'misses' => (int)$row['total_misses']
            ];
        }
        
        if ($total_hits + $total_misses > 0) {
            $stats['hit_miss_ratio'] = round(($total_hits / ($total_hits + $total_misses)) * 100, 2);
        }
        
        return $stats;
    }
    
    /**
     * Get memory usage information
     *
     * @return array Memory usage stats
     */
    private function getMemoryUsage() {
        $stats = [
            'current_usage' => memory_get_usage(true),
            'current_usage_formatted' => $this->formatBytes(memory_get_usage(true)),
            'peak_usage' => memory_get_peak_usage(true),
            'peak_usage_formatted' => $this->formatBytes(memory_get_peak_usage(true)),
            'limit' => ini_get('memory_limit'),
            'percentage_used' => 0
        ];
        
        $limit_bytes = $this->parseMemoryLimit($stats['limit']);
        if ($limit_bytes > 0) {
            $stats['percentage_used'] = round(($stats['current_usage'] / $limit_bytes) * 100, 2);
        }
        
        return $stats;
    }
    
    /**
     * Clear cache by type
     *
     * @param string $type Cache type to clear
     * @return array Result with success status and message
     */
    public function clearCache($type = 'all') {
        $result = [
            'success' => false,
            'message' => '',
            'cleared_items' => 0
        ];
        
        try {
            switch ($type) {
                case 'file':
                    $result = $this->clearFileCache();
                    break;
                    
                case 'database':
                    $result = $this->clearDatabaseCache();
                    break;
                    
                case 'system':
                    $result = $this->clearSystemCache();
                    break;
                    
                case 'all':
                default:
                    $file_result = $this->clearFileCache();
                    $db_result = $this->clearDatabaseCache();
                    $system_result = $this->clearSystemCache();
                    
                    $result['success'] = $file_result['success'] && $db_result['success'] && $system_result['success'];
                    $result['cleared_items'] = $file_result['cleared_items'] + $db_result['cleared_items'] + $system_result['cleared_items'];
                    $result['message'] = 'All cache types cleared successfully';
                    break;
            }
            
            // Log cache clear operation
            $this->logCacheOperation('clear', $type, $result);
            
        } catch (Exception $e) {
            $result['success'] = false;
            $result['message'] = 'Error clearing cache: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Clear file cache
     *
     * @return array Result
     */
    private function clearFileCache() {
        $result = [
            'success' => false,
            'message' => '',
            'cleared_items' => 0
        ];
        
        try {
            if (is_dir($this->cache_directory)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($this->cache_directory),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getFilename() !== '.htaccess') {
                        if (unlink($file->getRealPath())) {
                            $result['cleared_items']++;
                        }
                    }
                }
                
                $result['success'] = true;
                $result['message'] = "Cleared {$result['cleared_items']} cache files";
            }
        } catch (Exception $e) {
            $result['message'] = 'Error clearing file cache: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Clear database cache
     *
     * @return array Result
     */
    private function clearDatabaseCache() {
        $result = [
            'success' => false,
            'message' => '',
            'cleared_items' => 0
        ];
        
        try {
            // Get count before deletion
            $count_query = $this->db->query("SELECT COUNT(*) as count FROM " . DB_PREFIX . $this->cache_stats_table);
            $result['cleared_items'] = $count_query->row['count'];
            
            // Clear database cache entries
            $this->db->query("DELETE FROM " . DB_PREFIX . $this->cache_stats_table);
            
            $result['success'] = true;
            $result['message'] = "Cleared {$result['cleared_items']} database cache entries";
            
        } catch (Exception $e) {
            $result['message'] = 'Error clearing database cache: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Clear system cache (OpCache, APC, etc.)
     *
     * @return array Result
     */
    private function clearSystemCache() {
        $result = [
            'success' => false,
            'message' => '',
            'cleared_items' => 0
        ];
        
        $cleared_types = [];
        
        // Clear OpCache if available
        if (function_exists('opcache_reset')) {
            if (opcache_reset()) {
                $cleared_types[] = 'OpCache';
                $result['cleared_items']++;
            }
        }
        
        // Clear APC cache if available
        if (function_exists('apc_clear_cache')) {
            if (apc_clear_cache()) {
                $cleared_types[] = 'APC';
                $result['cleared_items']++;
            }
        }
        
        // Clear APCu cache if available
        if (function_exists('apcu_clear_cache')) {
            if (apcu_clear_cache()) {
                $cleared_types[] = 'APCu';
                $result['cleared_items']++;
            }
        }
        
        if (!empty($cleared_types)) {
            $result['success'] = true;
            $result['message'] = 'Cleared system caches: ' . implode(', ', $cleared_types);
        } else {
            $result['success'] = true;
            $result['message'] = 'No system caches available to clear';
        }
        
        return $result;
    }
    
    /**
     * Get cache performance metrics
     *
     * @param int $days Number of days to analyze
     * @return array Performance metrics
     */
    public function getCachePerformanceMetrics($days = 7) {
        $metrics = [
            'hit_rate' => 0,
            'miss_rate' => 0,
            'total_requests' => 0,
            'daily_stats' => []
        ];
        
        $query = $this->db->query("
            SELECT 
                DATE(created_at) as date,
                SUM(hit_count) as daily_hits,
                SUM(miss_count) as daily_misses
            FROM " . DB_PREFIX . $this->cache_stats_table . "
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL " . (int)$days . " DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ");
        
        $total_hits = 0;
        $total_misses = 0;
        
        foreach ($query->rows as $row) {
            $daily_hits = (int)$row['daily_hits'];
            $daily_misses = (int)$row['daily_misses'];
            $daily_total = $daily_hits + $daily_misses;
            
            $total_hits += $daily_hits;
            $total_misses += $daily_misses;
            
            $metrics['daily_stats'][] = [
                'date' => $row['date'],
                'hits' => $daily_hits,
                'misses' => $daily_misses,
                'total' => $daily_total,
                'hit_rate' => $daily_total > 0 ? round(($daily_hits / $daily_total) * 100, 2) : 0
            ];
        }
        
        $metrics['total_requests'] = $total_hits + $total_misses;
        
        if ($metrics['total_requests'] > 0) {
            $metrics['hit_rate'] = round(($total_hits / $metrics['total_requests']) * 100, 2);
            $metrics['miss_rate'] = round(($total_misses / $metrics['total_requests']) * 100, 2);
        }
        
        return $metrics;
    }
    
    /**
     * Log cache operation
     *
     * @param string $operation Operation type
     * @param string $type Cache type
     * @param array $result Operation result
     * @return void
     */
    private function logCacheOperation($operation, $type, $result) {
        if (method_exists($this, 'log')) {
            $this->log('info', "Cache {$operation} operation", [
                'cache_type' => $type,
                'success' => $result['success'],
                'cleared_items' => $result['cleared_items'] ?? 0,
                'message' => $result['message']
            ], 'cache_monitor');
        }
    }
    
    /**
     * Parse memory limit string to bytes
     *
     * @param string $limit Memory limit string
     * @return int Bytes
     */
    private function parseMemoryLimit($limit) {
        if ($limit == -1) {
            return -1;
        }
        
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int)$limit;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * Format bytes to human readable format
     *
     * @param int $bytes Bytes
     * @param int $precision Decimal precision
     * @return string Formatted size
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}