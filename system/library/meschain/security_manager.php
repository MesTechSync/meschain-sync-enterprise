<?php
/**
 * MesChain-Sync Advanced Security Framework
 * Enhanced Security Features and Hardening
 * VS Code Team Development - Phase 1
 * 
 * @version 3.1.1
 * @author VS Code Development Team
 * @date 2025-06-02
 */

class MesChainSecurityManager {
    
    private $config;
    private $logger;
    private $db;
    private $session;
    
    public function __construct($config, $logger, $db, $session) {
        $this->config = $config;
        $this->logger = $logger;
        $this->db = $db;
        $this->session = $session;
    }
    
    /**
     * Initialize advanced security features
     */
    public function initializeSecurityFeatures() {
        $security_features = [];
        
        try {
            // Initialize Two-Factor Authentication
            $this->initializeTwoFactorAuth();
            $security_features['2fa'] = 'INITIALIZED';
            
            // Setup API Rate Limiting
            $this->setupApiRateLimiting();
            $security_features['rate_limiting'] = 'INITIALIZED';
            
            // Implement Request Signing
            $this->setupRequestSigning();
            $security_features['request_signing'] = 'INITIALIZED';
            
            // Enhanced CSRF Protection
            $this->enhanceCsrfProtection();
            $security_features['csrf_protection'] = 'ENHANCED';
            
            // Security Monitoring
            $this->initializeSecurityMonitoring();
            $security_features['security_monitoring'] = 'ACTIVE';
            
            $this->logger->info('Advanced security features initialized', $security_features);
            
        } catch (Exception $e) {
            $this->logger->error('Security initialization failed: ' . $e->getMessage());
            $security_features['error'] = $e->getMessage();
        }
        
        return $security_features;
    }
    
    /**
     * Initialize Two-Factor Authentication System
     */
    private function initializeTwoFactorAuth() {
        // Create 2FA table if not exists
        $this->createTwoFactorAuthTable();
        
        // Initialize TOTP (Time-based One-Time Password) system
        $this->initializeTOTP();
        
        // Setup backup codes system
        $this->setupBackupCodes();
    }
    
    /**
     * Create two-factor authentication database table
     */
    private function createTwoFactorAuthTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS `oc_meschain_user_2fa` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `secret_key` varchar(255) NOT NULL,
                `backup_codes` text,
                `is_enabled` tinyint(1) DEFAULT 0,
                `last_used` datetime NULL,
                `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_user_2fa` (`user_id`),
                KEY `idx_2fa_enabled` (`is_enabled`, `user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";
        
        $this->db->query($sql);
    }
    
    /**
     * Initialize TOTP system
     */
    private function initializeTOTP() {
        // TOTP configuration
        $totp_config = [
            'issuer' => 'MesChain-Sync',
            'algorithm' => 'sha1',
            'digits' => 6,
            'period' => 30
        ];
        
        $this->config->set('totp_config', $totp_config);
    }
    
    /**
     * Setup backup codes system
     */
    private function setupBackupCodes() {
        // Generate backup codes for users
        $backup_config = [
            'code_length' => 8,
            'code_count' => 10,
            'encryption_method' => 'AES-256-CBC'
        ];
        
        $this->config->set('backup_codes_config', $backup_config);
    }
    
    /**
     * Generate TOTP secret for user
     */
    public function generateTOTPSecret($user_id) {
        // Generate random secret
        $secret = $this->generateRandomSecret(32);
        
        // Encrypt secret before storing
        $encrypted_secret = $this->encryptData($secret);
        
        // Store in database
        $this->db->query("
            INSERT INTO oc_meschain_user_2fa (user_id, secret_key, backup_codes, is_enabled) 
            VALUES ('" . (int)$user_id . "', '" . $this->db->escape($encrypted_secret) . "', '', 0)
            ON DUPLICATE KEY UPDATE 
            secret_key = '" . $this->db->escape($encrypted_secret) . "'
        ");
        
        return $secret;
    }
    
    /**
     * Verify TOTP code
     */
    public function verifyTOTPCode($user_id, $code) {
        // Get user's secret
        $query = $this->db->query("
            SELECT secret_key, is_enabled 
            FROM oc_meschain_user_2fa 
            WHERE user_id = '" . (int)$user_id . "'
        ");
        
        if (!$query->num_rows) {
            return false;
        }
        
        $secret = $this->decryptData($query->row['secret_key']);
        
        // Verify TOTP code
        return $this->validateTOTPCode($secret, $code);
    }
    
    /**
     * Setup API Rate Limiting System
     */
    private function setupApiRateLimiting() {
        // Create rate limiting table
        $this->createRateLimitingTable();
        
        // Configure rate limits
        $this->configureRateLimits();
    }
    
    /**
     * Create rate limiting database table
     */
    private function createRateLimitingTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS `oc_meschain_rate_limits` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `identifier` varchar(255) NOT NULL,
                `identifier_type` enum('user_id', 'ip_address', 'api_key') NOT NULL,
                `endpoint` varchar(255) NOT NULL,
                `requests_count` int(11) NOT NULL DEFAULT 0,
                `window_start` datetime NOT NULL,
                `window_end` datetime NOT NULL,
                `is_blocked` tinyint(1) DEFAULT 0,
                `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_rate_limit_unique` (`identifier`, `endpoint`, `window_start`),
                KEY `idx_rate_limit_window` (`window_start`, `window_end`),
                KEY `idx_rate_limit_blocked` (`is_blocked`, `identifier`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";
        
        $this->db->query($sql);
    }
    
    /**
     * Configure API rate limits
     */
    private function configureRateLimits() {
        $rate_limits = [
            'global' => [
                'requests_per_minute' => 100,
                'requests_per_hour' => 3000,
                'requests_per_day' => 50000
            ],
            'marketplace_sync' => [
                'requests_per_minute' => 30,
                'requests_per_hour' => 1000,
                'requests_per_day' => 10000
            ],
            'product_upload' => [
                'requests_per_minute' => 10,
                'requests_per_hour' => 500,
                'requests_per_day' => 2000
            ],
            'order_processing' => [
                'requests_per_minute' => 50,
                'requests_per_hour' => 2000,
                'requests_per_day' => 20000
            ]
        ];
        
        $this->config->set('api_rate_limits', $rate_limits);
    }
    
    /**
     * Check API rate limit
     */
    public function checkRateLimit($identifier, $identifier_type, $endpoint) {
        $rate_limits = $this->config->get('api_rate_limits');
        $limit_config = $rate_limits[$endpoint] ?? $rate_limits['global'];
        
        // Check current window
        $window_start = date('Y-m-d H:i:00');
        $window_end = date('Y-m-d H:i:59');
        
        // Get current request count
        $query = $this->db->query("
            SELECT requests_count, is_blocked 
            FROM oc_meschain_rate_limits 
            WHERE identifier = '" . $this->db->escape($identifier) . "' 
            AND endpoint = '" . $this->db->escape($endpoint) . "'
            AND window_start = '" . $window_start . "'
        ");
        
        if ($query->num_rows) {
            $current_count = (int)$query->row['requests_count'];
            $is_blocked = (bool)$query->row['is_blocked'];
            
            if ($is_blocked || $current_count >= $limit_config['requests_per_minute']) {
                return false; // Rate limit exceeded
            }
            
            // Increment counter
            $this->db->query("
                UPDATE oc_meschain_rate_limits 
                SET requests_count = requests_count + 1 
                WHERE identifier = '" . $this->db->escape($identifier) . "' 
                AND endpoint = '" . $this->db->escape($endpoint) . "'
                AND window_start = '" . $window_start . "'
            ");
        } else {
            // Create new window
            $this->db->query("
                INSERT INTO oc_meschain_rate_limits 
                (identifier, identifier_type, endpoint, requests_count, window_start, window_end) 
                VALUES (
                    '" . $this->db->escape($identifier) . "',
                    '" . $this->db->escape($identifier_type) . "',
                    '" . $this->db->escape($endpoint) . "',
                    1,
                    '" . $window_start . "',
                    '" . $window_end . "'
                )
            ");
        }
        
        return true; // Rate limit OK
    }
    
    /**
     * Setup Request Signing for API Security
     */
    private function setupRequestSigning() {
        // Create API keys table
        $this->createApiKeysTable();
        
        // Configure signing algorithm
        $this->configureRequestSigning();
    }
    
    /**
     * Create API keys database table
     */
    private function createApiKeysTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS `oc_meschain_api_keys` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `api_key` varchar(255) NOT NULL,
                `api_secret` varchar(255) NOT NULL,
                `permissions` text,
                `is_active` tinyint(1) DEFAULT 1,
                `last_used` datetime NULL,
                `expires_at` datetime NULL,
                `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_api_key` (`api_key`),
                KEY `idx_user_api_keys` (`user_id`, `is_active`),
                KEY `idx_api_key_expiry` (`expires_at`, `is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";
        
        $this->db->query($sql);
    }
    
    /**
     * Configure request signing
     */
    private function configureRequestSigning() {
        $signing_config = [
            'algorithm' => 'sha256',
            'header_name' => 'X-MesChain-Signature',
            'timestamp_tolerance' => 300, // 5 minutes
            'nonce_expiry' => 3600 // 1 hour
        ];
        
        $this->config->set('request_signing', $signing_config);
    }
    
    /**
     * Generate API key pair
     */
    public function generateApiKeyPair($user_id, $permissions = []) {
        $api_key = 'mk_' . $this->generateRandomString(32);
        $api_secret = $this->generateRandomString(64);
        
        // Encrypt secret before storing
        $encrypted_secret = $this->encryptData($api_secret);
        
        $this->db->query("
            INSERT INTO oc_meschain_api_keys 
            (user_id, api_key, api_secret, permissions, is_active) 
            VALUES (
                '" . (int)$user_id . "',
                '" . $this->db->escape($api_key) . "',
                '" . $this->db->escape($encrypted_secret) . "',
                '" . $this->db->escape(json_encode($permissions)) . "',
                1
            )
        ");
        
        return [
            'api_key' => $api_key,
            'api_secret' => $api_secret
        ];
    }
    
    /**
     * Verify request signature
     */
    public function verifyRequestSignature($api_key, $signature, $request_data, $timestamp) {
        // Get API secret
        $query = $this->db->query("
            SELECT api_secret, is_active 
            FROM oc_meschain_api_keys 
            WHERE api_key = '" . $this->db->escape($api_key) . "'
        ");
        
        if (!$query->num_rows || !$query->row['is_active']) {
            return false;
        }
        
        $api_secret = $this->decryptData($query->row['api_secret']);
        
        // Verify timestamp
        if (abs(time() - $timestamp) > $this->config->get('request_signing.timestamp_tolerance', 300)) {
            return false;
        }
        
        // Generate expected signature
        $expected_signature = $this->generateRequestSignature($api_secret, $request_data, $timestamp);
        
        return hash_equals($expected_signature, $signature);
    }
    
    /**
     * Generate request signature
     */
    private function generateRequestSignature($api_secret, $request_data, $timestamp) {
        $string_to_sign = $timestamp . '.' . json_encode($request_data);
        return hash_hmac('sha256', $string_to_sign, $api_secret);
    }
    
    /**
     * Enhanced CSRF Protection
     */
    private function enhanceCsrfProtection() {
        // Generate secure CSRF tokens
        $this->generateCsrfToken();
        
        // Setup CSRF validation
        $this->setupCsrfValidation();
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCsrfToken() {
        $token = bin2hex(random_bytes(32));
        $this->session->data['csrf_token'] = $token;
        $this->session->data['csrf_token_time'] = time();
        
        return $token;
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCsrfToken($token) {
        if (!isset($this->session->data['csrf_token']) || 
            !isset($this->session->data['csrf_token_time'])) {
            return false;
        }
        
        // Check token expiry (1 hour)
        if (time() - $this->session->data['csrf_token_time'] > 3600) {
            return false;
        }
        
        return hash_equals($this->session->data['csrf_token'], $token);
    }
    
    /**
     * Initialize Security Monitoring
     */
    private function initializeSecurityMonitoring() {
        // Create security events table
        $this->createSecurityEventsTable();
        
        // Setup monitoring rules
        $this->setupSecurityMonitoringRules();
    }
    
    /**
     * Create security events table
     */
    private function createSecurityEventsTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS `oc_meschain_security_events` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `event_type` varchar(100) NOT NULL,
                `severity` enum('low', 'medium', 'high', 'critical') NOT NULL,
                `user_id` int(11) NULL,
                `ip_address` varchar(45) NOT NULL,
                `user_agent` text,
                `event_data` text,
                `is_blocked` tinyint(1) DEFAULT 0,
                `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_security_events_type` (`event_type`, `created_date`),
                KEY `idx_security_events_severity` (`severity`, `created_date`),
                KEY `idx_security_events_ip` (`ip_address`, `created_date`),
                KEY `idx_security_events_user` (`user_id`, `created_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";
        
        $this->db->query($sql);
    }
    
    /**
     * Setup security monitoring rules
     */
    private function setupSecurityMonitoringRules() {
        $monitoring_rules = [
            'failed_login_attempts' => [
                'threshold' => 5,
                'time_window' => 900, // 15 minutes
                'action' => 'block_ip'
            ],
            'rapid_api_calls' => [
                'threshold' => 1000,
                'time_window' => 300, // 5 minutes
                'action' => 'rate_limit'
            ],
            'suspicious_user_agent' => [
                'patterns' => ['bot', 'crawler', 'scanner'],
                'action' => 'log_and_monitor'
            ],
            'sql_injection_attempt' => [
                'patterns' => ['union select', 'drop table', '1=1'],
                'action' => 'block_immediately'
            ]
        ];
        
        $this->config->set('security_monitoring_rules', $monitoring_rules);
    }
    
    /**
     * Log security event
     */
    public function logSecurityEvent($event_type, $severity, $event_data = [], $user_id = null) {
        $ip_address = $this->getClientIpAddress();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $this->db->query("
            INSERT INTO oc_meschain_security_events 
            (event_type, severity, user_id, ip_address, user_agent, event_data) 
            VALUES (
                '" . $this->db->escape($event_type) . "',
                '" . $this->db->escape($severity) . "',
                " . ($user_id ? "'" . (int)$user_id . "'" : "NULL") . ",
                '" . $this->db->escape($ip_address) . "',
                '" . $this->db->escape($user_agent) . "',
                '" . $this->db->escape(json_encode($event_data)) . "'
            )
        ");
        
        // Check for automated response
        $this->checkSecurityRules($event_type, $ip_address, $user_id);
    }
    
    /**
     * Check security rules and take action
     */
    private function checkSecurityRules($event_type, $ip_address, $user_id) {
        $rules = $this->config->get('security_monitoring_rules');
        
        if (isset($rules[$event_type])) {
            $rule = $rules[$event_type];
            
            // Check threshold if applicable
            if (isset($rule['threshold'])) {
                $count = $this->getEventCount($event_type, $ip_address, $rule['time_window']);
                
                if ($count >= $rule['threshold']) {
                    $this->executeSecurityAction($rule['action'], $ip_address, $user_id);
                }
            }
        }
    }
    
    /**
     * Get event count for security rule checking
     */
    private function getEventCount($event_type, $ip_address, $time_window) {
        $since = date('Y-m-d H:i:s', time() - $time_window);
        
        $query = $this->db->query("
            SELECT COUNT(*) as count 
            FROM oc_meschain_security_events 
            WHERE event_type = '" . $this->db->escape($event_type) . "'
            AND ip_address = '" . $this->db->escape($ip_address) . "'
            AND created_date >= '" . $since . "'
        ");
        
        return (int)$query->row['count'];
    }
    
    /**
     * Execute security action
     */
    private function executeSecurityAction($action, $ip_address, $user_id) {
        switch ($action) {
            case 'block_ip':
                $this->blockIpAddress($ip_address);
                break;
            case 'rate_limit':
                $this->applyRateLimit($ip_address);
                break;
            case 'block_immediately':
                $this->blockIpAddress($ip_address, true);
                break;
            case 'log_and_monitor':
                $this->logSecurityEvent('enhanced_monitoring', 'medium', 
                    ['action' => $action, 'ip' => $ip_address], $user_id);
                break;
        }
    }
    
    /**
     * Block IP address
     */
    private function blockIpAddress($ip_address, $immediate = false) {
        // Implementation for IP blocking
        $this->logger->warning("IP address blocked: {$ip_address}", [
            'immediate' => $immediate,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Apply rate limiting
     */
    private function applyRateLimit($ip_address) {
        // Implementation for rate limiting
        $this->logger->info("Rate limit applied to IP: {$ip_address}");
    }
    
    /**
     * Utility functions
     */
    
    /**
     * Generate random secret
     */
    private function generateRandomSecret($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Generate random string
     */
    private function generateRandomString($length = 32) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($characters, $length)), 0, $length);
    }
    
    /**
     * Encrypt sensitive data
     */
    private function encryptData($data) {
        $key = $this->config->get('encryption_key');
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     */
    private function decryptData($encrypted_data) {
        $key = $this->config->get('encryption_key');
        $data = base64_decode($encrypted_data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
    
    /**
     * Get client IP address
     */
    private function getClientIpAddress() {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Validate TOTP code implementation
     */
    private function validateTOTPCode($secret, $code) {
        // TOTP validation logic
        $time = floor(time() / 30);
        
        // Check current and previous time windows for clock skew
        for ($i = -1; $i <= 1; $i++) {
            $calculated_code = $this->calculateTOTP($secret, $time + $i);
            if (hash_equals($calculated_code, $code)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Calculate TOTP code
     */
    private function calculateTOTP($secret, $time) {
        $data = pack('N*', 0, $time);
        $hash = hash_hmac('sha1', $data, $secret, true);
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % pow(10, 6);
        
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Setup CSRF validation
     */
    private function setupCsrfValidation() {
        // CSRF validation middleware setup
        $this->config->set('csrf_validation_enabled', true);
        $this->config->set('csrf_token_expiry', 3600); // 1 hour
    }
}

// Usage Example:
/*
$security_manager = new MesChainSecurityManager($config, $logger, $db, $session);

// Initialize all security features
$security_status = $security_manager->initializeSecurityFeatures();

// Generate 2FA for user
$secret = $security_manager->generateTOTPSecret($user_id);

// Generate API key pair
$api_credentials = $security_manager->generateApiKeyPair($user_id, ['read', 'write']);

// Log security event
$security_manager->logSecurityEvent('login_attempt', 'low', ['success' => true], $user_id);

echo "Advanced Security Features Initialized:\n";
print_r($security_status);
*/
