<?php
/**
 * MesChain Sync Enterprise - System Logger
 *
 * @package MesChain-Sync Enterprise
 * @version 3.0.0
 * @author MesTech Development Team
 */

namespace MesChain\Logger;

class SystemLogger {

    private $log_directory;
    private $log_level;
    private $max_file_size;
    private $registry;

    const LEVEL_DEBUG = 1;
    const LEVEL_INFO = 2;
    const LEVEL_WARNING = 3;
    const LEVEL_ERROR = 4;
    const LEVEL_CRITICAL = 5;

    /**
     * Constructor
     *
     * @param object $registry OpenCart registry
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->log_directory = DIR_LOGS . 'meschain/';
        $this->log_level = self::LEVEL_INFO;
        $this->max_file_size = 10 * 1024 * 1024; // 10MB

        // Create log directory if it doesn't exist
        if (!is_dir($this->log_directory)) {
            mkdir($this->log_directory, 0755, true);
        }
    }

    /**
     * Log debug message
     *
     * @param string $message Log message
     * @param array $context Additional context data
     * @param string $module Module name
     */
    public function debug($message, $context = [], $module = 'system') {
        $this->log(self::LEVEL_DEBUG, $message, $context, $module);
    }

    /**
     * Log info message
     *
     * @param string $message Log message
     * @param array $context Additional context data
     * @param string $module Module name
     */
    public function info($message, $context = [], $module = 'system') {
        $this->log(self::LEVEL_INFO, $message, $context, $module);
    }

    /**
     * Log warning message
     *
     * @param string $message Log message
     * @param array $context Additional context data
     * @param string $module Module name
     */
    public function warning($message, $context = [], $module = 'system') {
        $this->log(self::LEVEL_WARNING, $message, $context, $module);
    }

    /**
     * Log error message
     *
     * @param string $message Log message
     * @param array $context Additional context data
     * @param string $module Module name
     */
    public function error($message, $context = [], $module = 'system') {
        $this->log(self::LEVEL_ERROR, $message, $context, $module);
    }

    /**
     * Log critical message
     *
     * @param string $message Log message
     * @param array $context Additional context data
     * @param string $module Module name
     */
    public function critical($message, $context = [], $module = 'system') {
        $this->log(self::LEVEL_CRITICAL, $message, $context, $module);
    }

    /**
     * Log marketplace activity
     *
     * @param string $marketplace Marketplace name
     * @param string $action Action performed
     * @param array $data Action data
     * @param string $status Action status (success/error)
     */
    public function logMarketplaceActivity($marketplace, $action, $data = [], $status = 'success') {
        $message = "Marketplace: {$marketplace} | Action: {$action} | Status: {$status}";
        $context = [
            'marketplace' => $marketplace,
            'action' => $action,
            'data' => $data,
            'status' => $status,
            'user_id' => $this->getUserId(),
            'ip_address' => $this->getIpAddress()
        ];

        $level = ($status === 'error') ? self::LEVEL_ERROR : self::LEVEL_INFO;
        $this->log($level, $message, $context, 'marketplace');
    }

    /**
     * Log API request/response
     *
     * @param string $endpoint API endpoint
     * @param array $request Request data
     * @param array $response Response data
     * @param int $response_time Response time in milliseconds
     */
    public function logApiCall($endpoint, $request, $response, $response_time) {
        $message = "API Call: {$endpoint} | Response Time: {$response_time}ms";
        $context = [
            'endpoint' => $endpoint,
            'request' => $request,
            'response' => $response,
            'response_time' => $response_time,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $this->log(self::LEVEL_INFO, $message, $context, 'api');
    }

    /**
     * Log security event
     *
     * @param string $event_type Security event type
     * @param string $description Event description
     * @param array $data Additional data
     */
    public function logSecurityEvent($event_type, $description, $data = []) {
        $message = "Security Event: {$event_type} | {$description}";
        $context = [
            'event_type' => $event_type,
            'description' => $description,
            'data' => $data,
            'user_id' => $this->getUserId(),
            'ip_address' => $this->getIpAddress(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];

        $this->log(self::LEVEL_WARNING, $message, $context, 'security');
    }

    /**
     * Core logging method
     *
     * @param int $level Log level
     * @param string $message Log message
     * @param array $context Additional context data
     * @param string $module Module name
     */
    private function log($level, $message, $context = [], $module = 'system') {
        if ($level < $this->log_level) {
            return;
        }

        $log_file = $this->log_directory . $module . '_' . date('Y-m-d') . '.log';

        // Check file size and rotate if necessary
        if (file_exists($log_file) && filesize($log_file) > $this->max_file_size) {
            $this->rotateLogFile($log_file);
        }

        $log_entry = $this->formatLogEntry($level, $message, $context);

        // Write to file
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

        // Also log critical errors to main OpenCart log
        if ($level >= self::LEVEL_ERROR) {
            $log_instance = $this->registry->get('log');
            if ($log_instance) {
                $log_instance->write('[MesChain] ' . $message);
            }
        }
    }

    /**
     * Format log entry
     *
     * @param int $level Log level
     * @param string $message Log message
     * @param array $context Additional context data
     * @return string Formatted log entry
     */
    private function formatLogEntry($level, $message, $context = []) {
        $level_names = [
            self::LEVEL_DEBUG => 'DEBUG',
            self::LEVEL_INFO => 'INFO',
            self::LEVEL_WARNING => 'WARNING',
            self::LEVEL_ERROR => 'ERROR',
            self::LEVEL_CRITICAL => 'CRITICAL'
        ];

        $timestamp = date('Y-m-d H:i:s');
        $level_name = $level_names[$level] ?? 'UNKNOWN';
        $context_string = !empty($context) ? ' | Context: ' . json_encode($context) : '';

        return "[{$timestamp}] {$level_name}: {$message}{$context_string}" . PHP_EOL;
    }

    /**
     * Rotate log file when it gets too large
     *
     * @param string $log_file Log file path
     */
    private function rotateLogFile($log_file) {
        $backup_file = $log_file . '.backup.' . time();
        rename($log_file, $backup_file);

        // Compress old log file
        if (function_exists('gzopen')) {
            $this->compressLogFile($backup_file);
        }
    }

    /**
     * Compress log file using gzip
     *
     * @param string $file_path File path to compress
     */
    private function compressLogFile($file_path) {
        $gz_file = $file_path . '.gz';
        $file = fopen($file_path, 'rb');
        $gz = gzopen($gz_file, 'wb9');

        while (!feof($file)) {
            gzwrite($gz, fread($file, 4096));
        }

        fclose($file);
        gzclose($gz);
        unlink($file_path);
    }

    /**
     * Get current user ID
     *
     * @return int|null User ID
     */
    private function getUserId() {
        $user = $this->registry->get('user');
        if ($user && method_exists($user, 'getId')) {
            return $user->getId();
        }
        return null;
    }

    /**
     * Get client IP address
     *
     * @return string IP address
     */
    private function getIpAddress() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    /**
     * Get log files for a module
     *
     * @param string $module Module name
     * @param int $days Number of days to include
     * @return array Log files
     */
    public function getLogFiles($module = '*', $days = 7) {
        $pattern = $this->log_directory . $module . '_*.log';
        $files = glob($pattern);

        // Filter by date
        $cutoff_date = date('Y-m-d', strtotime("-{$days} days"));
        $filtered_files = [];

        foreach ($files as $file) {
            if (preg_match('/(\d{4}-\d{2}-\d{2})\.log$/', $file, $matches)) {
                if ($matches[1] >= $cutoff_date) {
                    $filtered_files[] = $file;
                }
            }
        }

        return $filtered_files;
    }

    /**
     * Clear old log files
     *
     * @param int $days Keep files newer than this many days
     */
    public function clearOldLogs($days = 30) {
        $cutoff_date = strtotime("-{$days} days");
        $files = glob($this->log_directory . '*.log*');

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff_date) {
                unlink($file);
            }
        }
    }
}
