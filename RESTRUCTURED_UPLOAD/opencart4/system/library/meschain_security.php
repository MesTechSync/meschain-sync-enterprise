<?php
/**
 * MesChain-Sync Security Library
 * Implements security measures for the system
 */

class MesChainSecurity {
    
    /**
     * Validate API token
     */
    public static function validateApiToken($token) {
        if (empty($token)) {
            return false;
        }
        
        // Token should be at least 32 characters
        if (strlen($token) < 32) {
            return false;
        }
        
        // Token should contain alphanumeric characters only
        if (!preg_match('/^[a-zA-Z0-9]+$/', $token)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Generate secure API token
     */
    public static function generateApiToken($length = 64) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $token = '';
        $max = strlen($characters) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[random_int(0, $max)];
        }
        
        return $token;
    }
    
    /**
     * Sanitize marketplace API credentials
     */
    public static function sanitizeApiCredentials($credentials) {
        $sanitized = [];
        
        foreach ($credentials as $key => $value) {
            // Remove any potential malicious characters
            $sanitized[$key] = preg_replace('/[^a-zA-Z0-9\-_]/', '', $value);
        }
        
        return $sanitized;
    }
    
    /**
     * Log security events
     */
    public static function logSecurityEvent($event_type, $details, $severity = 'warning') {
        $log_file = DIR_LOGS . 'meschain_security.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $log_entry = "[{$timestamp}] [{$severity}] {$event_type}: {$details} | IP: {$ip_address} | UA: {$user_agent}\n";
        
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Rate limiting check
     */
    public static function checkRateLimit($identifier, $max_requests = 100, $time_window = 3600) {
        $cache_key = "rate_limit_{$identifier}";
        
        // Simple file-based rate limiting (in production, use Redis or Memcached)
        $cache_file = DIR_CACHE . $cache_key;
        
        if (file_exists($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true);
            
            if (time() - $data['timestamp'] < $time_window) {
                if ($data['count'] >= $max_requests) {
                    return false; // Rate limit exceeded
                }
                $data['count']++;
            } else {
                // Reset counter
                $data = ['count' => 1, 'timestamp' => time()];
            }
        } else {
            $data = ['count' => 1, 'timestamp' => time()];
        }
        
        file_put_contents($cache_file, json_encode($data), LOCK_EX);
        return true;
    }
    
    /**
     * Encrypt sensitive data
     */
    public static function encryptData($data, $key) {
        $cipher = 'AES-256-CBC';
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, $cipher, $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     */
    public static function decryptData($encrypted_data, $key) {
        $cipher = 'AES-256-CBC';
        $data = base64_decode($encrypted_data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, $cipher, $key, 0, $iv);
    }
}
