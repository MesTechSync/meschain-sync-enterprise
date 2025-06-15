<?php
/**
 * MesChain Cache Monitor Model
 * 
 * OpenCart Extension - Cache Monitoring System
 * 
 * @category   Model
 * @package    MesChain-Sync
 * @version    2.5.0
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

class ModelExtensionModuleCacheMonitor extends Model {
    
    /**
     * Model installation
     */
    public function install() {
        // Create cache monitor table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_cache_log` (
                `cache_id` int(11) NOT NULL AUTO_INCREMENT,
                `cache_key` varchar(255) NOT NULL,
                `cache_size` int(11) NOT NULL DEFAULT '0',
                `cache_type` varchar(50) NOT NULL,
                `hits` int(11) NOT NULL DEFAULT '0',
                `misses` int(11) NOT NULL DEFAULT '0',
                `created_at` datetime NOT NULL,
                `last_accessed` datetime NOT NULL,
                PRIMARY KEY (`cache_id`),
                KEY `cache_key` (`cache_key`),
                KEY `cache_type` (`cache_type`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Create cache statistics table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_cache_stats` (
                `stat_id` int(11) NOT NULL AUTO_INCREMENT,
                `total_size` bigint(20) NOT NULL DEFAULT '0',
                `total_files` int(11) NOT NULL DEFAULT '0',
                `hit_ratio` decimal(5,2) NOT NULL DEFAULT '0.00',
                `last_cleanup` datetime NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`stat_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
    }
    
    /**
     * Model uninstallation
     */
    public function uninstall() {
        // Note: We don't drop tables on uninstall to preserve data
        // Only remove settings
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE code LIKE 'module_cache_monitor%'");
    }
    
    /**
     * Get cache statistics
     */
    public function getCacheStats() {
        $data = array();
        
        // Get OpenCart cache directory
        $cache_dir = DIR_CACHE;
        
        if (is_dir($cache_dir)) {
            $data['cache_dir'] = $cache_dir;
            $data['total_size'] = $this->getDirectorySize($cache_dir);
            $data['total_files'] = $this->countFiles($cache_dir);
            $data['readable'] = is_readable($cache_dir);
            $data['writable'] = is_writable($cache_dir);
        } else {
            $data['cache_dir'] = $cache_dir;
            $data['total_size'] = 0;
            $data['total_files'] = 0;
            $data['readable'] = false;
            $data['writable'] = false;
        }
        
        return $data;
    }
    
    /**
     * Get cache files list
     */
    public function getCacheFiles($start = 0, $limit = 20) {
        $files = array();
        $cache_dir = DIR_CACHE;
        
        if (is_dir($cache_dir)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($cache_dir)
            );
            
            $count = 0;
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    if ($count >= $start && count($files) < $limit) {
                        $files[] = array(
                            'name' => $file->getFilename(),
                            'path' => $file->getPathname(),
                            'size' => $file->getSize(),
                            'modified' => date('Y-m-d H:i:s', $file->getMTime())
                        );
                    }
                    $count++;
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Clear all cache
     */
    public function clearAllCache() {
        $cache_dir = DIR_CACHE;
        
        if (is_dir($cache_dir)) {
            return $this->deleteDirectory($cache_dir, true);
        }
        
        return false;
    }
    
    /**
     * Clear specific cache file
     */
    public function clearCacheFile($filename) {
        $cache_dir = DIR_CACHE;
        $file_path = $cache_dir . $filename;
        
        if (file_exists($file_path) && is_writable($file_path)) {
            return unlink($file_path);
        }
        
        return false;
    }
    
    /**
     * Get directory size
     */
    private function getDirectorySize($directory) {
        $size = 0;
        
        if (is_dir($directory)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        
        return $size;
    }
    
    /**
     * Count files in directory
     */
    private function countFiles($directory) {
        $count = 0;
        
        if (is_dir($directory)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Delete directory contents
     */
    private function deleteDirectory($dir, $preserve_dir = true) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($path)) {
                $this->deleteDirectory($path, false);
            } else {
                unlink($path);
            }
        }
        
        if (!$preserve_dir) {
            return rmdir($dir);
        }
        
        return true;
    }
    
    /**
     * Format file size
     */
    public function formatFileSize($size) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }
    
    /**
     * Dashboard widget için istatistikleri getir
     * 
     * @return array Dashboard istatistikleri
     */
    public function getDashboardStats() {
        $stats = array();
        
        // Cache dizin bilgilerini al
        $cache_stats = $this->getCacheStats();
        
        // Toplam cache dosya sayısı
        $stats['total_products'] = $cache_stats['total_files'];
        
        // Cache boyutu (MB olarak)
        $stats['total_orders'] = round($cache_stats['total_size'] / 1024 / 1024, 2);
        
        // Cache temizleme sayısı (bu ay)
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_cache_log WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
        $stats['total_sync'] = $query->num_rows ? $query->row['total'] : 0;
        
        // Son cache temizleme tarihi
        $query = $this->db->query("SELECT MAX(last_cleanup) as last_cleanup FROM " . DB_PREFIX . "meschain_cache_stats");
        $stats['last_sync'] = ($query->num_rows && $query->row['last_cleanup']) ? 
            date('d.m.Y H:i', strtotime($query->row['last_cleanup'])) : 'Hiçbir zaman';
        
        // Cache durumu
        $stats['status'] = ($cache_stats['readable'] && $cache_stats['writable']) ? 'connected' : 'error';
        
        // Son aktivite
        if ($cache_stats['total_size'] > 0) {
            $stats['recent_activity'] = 'Cache boyutu: ' . $this->formatFileSize($cache_stats['total_size']);
        } else {
            $stats['recent_activity'] = 'Cache boş';
        }
        
        return $stats;
    }
} 