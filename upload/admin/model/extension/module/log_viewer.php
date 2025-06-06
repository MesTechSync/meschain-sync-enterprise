<?php
/**
 * Log Viewer Model
 * MesChain-Sync OpenCart Extension
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0
 * @author MesChain Development Team
 */

class ModelExtensionModuleLogViewer extends Model {
    
    private $log_table = 'meschain_marketplace_logs';
    private $log_directory;
    
    public function __construct($registry) {
        parent::__construct($registry);
        $this->log_directory = DIR_LOGS;
    }
    
    /**
     * Get database logs with pagination and filtering
     *
     * @param array $filters Filter options
     * @return array Log entries
     */
    public function getDatabaseLogs($filters = []) {
        $sql = "SELECT * FROM " . DB_PREFIX . $this->log_table;
        $where = [];
        
        if (!empty($filters['marketplace'])) {
            $where[] = "marketplace = '" . $this->db->escape($filters['marketplace']) . "'";
        }
        
        if (!empty($filters['level'])) {
            $where[] = "level = '" . $this->db->escape($filters['level']) . "'";
        }
        
        if (!empty($filters['search'])) {
            $where[] = "message LIKE '%" . $this->db->escape($filters['search']) . "%'";
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "date_added >= '" . $this->db->escape($filters['date_from']) . "'";
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "date_added <= '" . $this->db->escape($filters['date_to']) . "'";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY date_added DESC";
        
        if (isset($filters['limit']) && $filters['limit'] > 0) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            
            if (isset($filters['offset']) && $filters['offset'] > 0) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get total count of database logs
     *
     * @param array $filters Filter options
     * @return int Total count
     */
    public function getTotalDatabaseLogs($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM " . DB_PREFIX . $this->log_table;
        $where = [];
        
        if (!empty($filters['marketplace'])) {
            $where[] = "marketplace = '" . $this->db->escape($filters['marketplace']) . "'";
        }
        
        if (!empty($filters['level'])) {
            $where[] = "level = '" . $this->db->escape($filters['level']) . "'";
        }
        
        if (!empty($filters['search'])) {
            $where[] = "message LIKE '%" . $this->db->escape($filters['search']) . "%'";
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "date_added >= '" . $this->db->escape($filters['date_from']) . "'";
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "date_added <= '" . $this->db->escape($filters['date_to']) . "'";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $query = $this->db->query($sql);
        
        return $query->num_rows ? (int)$query->row['total'] : 0;
    }
    
    /**
     * Get available log files from filesystem
     *
     * @return array Log file list
     */
    public function getLogFiles() {
        $log_files = [];
        
        if (is_dir($this->log_directory)) {
            $files = scandir($this->log_directory);
            
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'log') {
                    $filepath = $this->log_directory . $file;
                    
                    if (is_file($filepath)) {
                        $log_files[] = [
                            'name' => $file,
                            'size' => filesize($filepath),
                            'size_formatted' => $this->formatBytes(filesize($filepath)),
                            'modified' => filemtime($filepath),
                            'modified_formatted' => date('Y-m-d H:i:s', filemtime($filepath))
                        ];
                    }
                }
            }
            
            // Sort by modification time (newest first)
            usort($log_files, function($a, $b) {
                return $b['modified'] - $a['modified'];
            });
        }
        
        return $log_files;
    }
    
    /**
     * Read log file content
     *
     * @param string $filename Log file name
     * @param int $lines Number of lines to read from end
     * @return array Log content
     */
    public function readLogFile($filename, $lines = 100) {
        $filepath = $this->log_directory . basename($filename);
        $result = [
            'content' => '',
            'lines' => [],
            'error' => null
        ];
        
        if (!file_exists($filepath)) {
            $result['error'] = 'Log file not found';
            return $result;
        }
        
        if (!is_readable($filepath)) {
            $result['error'] = 'Log file is not readable';
            return $result;
        }
        
        try {
            // Read last N lines efficiently
            $file_lines = $this->tail($filepath, $lines);
            $result['lines'] = array_reverse($file_lines); // Show newest first
            $result['content'] = implode("\n", $result['lines']);
            
        } catch (Exception $e) {
            $result['error'] = 'Error reading log file: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Search in log file
     *
     * @param string $filename Log file name
     * @param string $search Search term
     * @param int $max_lines Maximum lines to return
     * @return array Search results
     */
    public function searchLogFile($filename, $search, $max_lines = 100) {
        $filepath = $this->log_directory . basename($filename);
        $result = [
            'matches' => [],
            'total_matches' => 0,
            'error' => null
        ];
        
        if (!file_exists($filepath)) {
            $result['error'] = 'Log file not found';
            return $result;
        }
        
        try {
            $handle = fopen($filepath, 'r');
            if (!$handle) {
                $result['error'] = 'Could not open log file';
                return $result;
            }
            
            $line_number = 0;
            $matches_found = 0;
            
            while (($line = fgets($handle)) !== false && $matches_found < $max_lines) {
                $line_number++;
                
                if (stripos($line, $search) !== false) {
                    $result['matches'][] = [
                        'line_number' => $line_number,
                        'content' => trim($line),
                        'highlighted' => str_ireplace($search, '<mark>' . $search . '</mark>', trim($line))
                    ];
                    $matches_found++;
                }
            }
            
            fclose($handle);
            $result['total_matches'] = $matches_found;
            
        } catch (Exception $e) {
            $result['error'] = 'Error searching log file: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Delete log file
     *
     * @param string $filename Log file name
     * @return bool Success status
     */
    public function deleteLogFile($filename) {
        $filepath = $this->log_directory . basename($filename);
        
        if (!file_exists($filepath)) {
            return false;
        }
        
        try {
            return unlink($filepath);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Clear database logs
     *
     * @param array $filters Optional filters to limit deletion
     * @return bool Success status
     */
    public function clearDatabaseLogs($filters = []) {
        try {
            $sql = "DELETE FROM " . DB_PREFIX . $this->log_table;
            $where = [];
            
            if (!empty($filters['marketplace'])) {
                $where[] = "marketplace = '" . $this->db->escape($filters['marketplace']) . "'";
            }
            
            if (!empty($filters['level'])) {
                $where[] = "level = '" . $this->db->escape($filters['level']) . "'";
            }
            
            if (!empty($filters['older_than_days'])) {
                $where[] = "date_added < DATE_SUB(NOW(), INTERVAL " . (int)$filters['older_than_days'] . " DAY)";
            }
            
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            
            $this->db->query($sql);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get log statistics
     *
     * @return array Statistics
     */
    public function getLogStatistics() {
        $stats = [
            'database_logs' => [
                'total' => 0,
                'by_level' => [],
                'by_marketplace' => [],
                'last_24h' => 0
            ],
            'file_logs' => [
                'total_files' => 0,
                'total_size' => 0,
                'total_size_formatted' => '0 B'
            ]
        ];
        
        // Database log statistics
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total,
                level,
                marketplace,
                SUM(CASE WHEN date_added >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) as last_24h
            FROM " . DB_PREFIX . $this->log_table . "
            GROUP BY level, marketplace
        ");
        
        foreach ($query->rows as $row) {
            $stats['database_logs']['total'] += (int)$row['total'];
            $stats['database_logs']['last_24h'] += (int)$row['last_24h'];
            
            if (!isset($stats['database_logs']['by_level'][$row['level']])) {
                $stats['database_logs']['by_level'][$row['level']] = 0;
            }
            $stats['database_logs']['by_level'][$row['level']] += (int)$row['total'];
            
            if (!isset($stats['database_logs']['by_marketplace'][$row['marketplace']])) {
                $stats['database_logs']['by_marketplace'][$row['marketplace']] = 0;
            }
            $stats['database_logs']['by_marketplace'][$row['marketplace']] += (int)$row['total'];
        }
        
        // File log statistics
        $log_files = $this->getLogFiles();
        $stats['file_logs']['total_files'] = count($log_files);
        
        foreach ($log_files as $file) {
            $stats['file_logs']['total_size'] += $file['size'];
        }
        
        $stats['file_logs']['total_size_formatted'] = $this->formatBytes($stats['file_logs']['total_size']);
        
        return $stats;
    }
    
    /**
     * Read last N lines of a file efficiently
     *
     * @param string $filepath File path
     * @param int $lines Number of lines
     * @return array Lines
     */
    private function tail($filepath, $lines) {
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            return [];
        }
        
        $buffer_size = 4096;
        $lines_found = [];
        
        // Start from end of file
        fseek($handle, 0, SEEK_END);
        $file_size = ftell($handle);
        $buffer = '';
        $pos = $file_size;
        
        while (count($lines_found) < $lines && $pos > 0) {
            $read_size = min($buffer_size, $pos);
            $pos -= $read_size;
            
            fseek($handle, $pos);
            $chunk = fread($handle, $read_size);
            $buffer = $chunk . $buffer;
            
            $new_lines = explode("\n", $buffer);
            $buffer = array_shift($new_lines);
            
            $lines_found = array_merge($new_lines, $lines_found);
        }
        
        fclose($handle);
        
        // Remove empty lines and limit to requested count
        $lines_found = array_filter($lines_found, 'strlen');
        return array_slice($lines_found, -$lines);
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