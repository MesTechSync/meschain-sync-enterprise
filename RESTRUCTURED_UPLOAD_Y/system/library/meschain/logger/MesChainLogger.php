<?php
namespace MesChain\Logger;

/**
 * MesChain Logger
 * OpenCart 4.0.2.3 Compatible
 *
 * @author Cursor Development Team
 * @version 1.0.0
 */
class MesChainLogger {

    private $db;
    private $config;
    private $user_id;
    private $ip_address;

    public function __construct($registry = null) {
        if ($registry) {
            $this->db = $registry->get('db');
            $this->config = $registry->get('config');
            
            // Get user ID if available
            if ($registry->has('user') && $registry->get('user')->isLogged()) {
                $this->user_id = $registry->get('user')->getId();
            }
            
            // Get IP address
            $this->ip_address = $this->getClientIpAddress();
        }
    }

    /**
     * Log debug message
     */
    public function debug($message, $data = null, $marketplace = null): void {
        $this->log('debug', $message, $data, $marketplace);
    }

    /**
     * Log info message
     */
    public function info($message, $data = null, $marketplace = null): void {
        $this->log('info', $message, $data, $marketplace);
    }

    /**
     * Log warning message
     */
    public function warning($message, $data = null, $marketplace = null): void {
        $this->log('warning', $message, $data, $marketplace);
    }

    /**
     * Log error message
     */
    public function error($message, $data = null, $marketplace = null): void {
        $this->log('error', $message, $data, $marketplace);
    }

    /**
     * Log critical message
     */
    public function critical($message, $data = null, $marketplace = null): void {
        $this->log('critical', $message, $data, $marketplace);
    }

    /**
     * Main logging method
     */
    public function log($level, $message, $data = null, $marketplace = null, $log_type = 'meschain'): void {
        // Fallback to file logging if database is not available
        if (!$this->db) {
            $this->logToFile($level, $message, $data, $marketplace, $log_type);
            return;
        }

        try {
            $sql = "INSERT INTO `" . DB_PREFIX . "meschain_logs` 
                    (`log_level`, `log_type`, `log_message`, `log_data`, `marketplace`, `user_id`, `ip_address`, `date_added`) 
                    VALUES 
                    ('" . $this->db->escape($level) . "', 
                     '" . $this->db->escape($log_type) . "', 
                     '" . $this->db->escape($message) . "', 
                     '" . $this->db->escape($data ? json_encode($data) : null) . "', 
                     '" . $this->db->escape($marketplace) . "', 
                     " . (int)$this->user_id . ", 
                     '" . $this->db->escape($this->ip_address) . "', 
                     NOW())";

            $this->db->query($sql);
        } catch (Exception $e) {
            // Fallback to file logging if database logging fails
            $this->logToFile($level, $message, $data, $marketplace, $log_type);
        }
    }

    /**
     * Log to file as fallback
     */
    private function logToFile($level, $message, $data = null, $marketplace = null, $log_type = 'meschain'): void {
        $log_dir = defined('DIR_LOGS') ? DIR_LOGS : sys_get_temp_dir() . '/';
        $log_file = $log_dir . 'meschain_' . date('Y-m-d') . '.log';
        
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => strtoupper($level),
            'type' => $log_type,
            'message' => $message,
            'marketplace' => $marketplace,
            'user_id' => $this->user_id,
            'ip' => $this->ip_address
        ];
        
        if ($data) {
            $log_entry['data'] = is_string($data) ? $data : json_encode($data);
        }
        
        $formatted_log = json_encode($log_entry) . "\n";
        
        file_put_contents($log_file, $formatted_log, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get client IP address
     */
    private function getClientIpAddress(): string {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                   'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 
                   'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) && !empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, 
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Get recent logs for dashboard display
     */
    public function getRecentLogs($limit = 50, $level = null, $marketplace = null): array {
        if (!$this->db) {
            return [];
        }

        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_logs` WHERE 1=1";
        
        if ($level) {
            $sql .= " AND `log_level` = '" . $this->db->escape($level) . "'";
        }
        
        if ($marketplace) {
            $sql .= " AND `marketplace` = '" . $this->db->escape($marketplace) . "'";
        }
        
        $sql .= " ORDER BY `date_added` DESC LIMIT " . (int)$limit;
        
        $query = $this->db->query($sql);
        
        return $query->rows ?? [];
    }

    /**
     * Clear old logs (cleanup)
     */
    public function clearOldLogs($days = 30): bool {
        if (!$this->db) {
            return false;
        }

        try {
            $sql = "DELETE FROM `" . DB_PREFIX . "meschain_logs` 
                    WHERE `date_added` < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)";
            
            $this->db->query($sql);
            
            return true;
        } catch (Exception $e) {
            $this->error('Failed to clear old logs', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
