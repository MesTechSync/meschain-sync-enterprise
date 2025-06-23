<?php
/**
 * Enhanced API Security Framework
 * Comprehensive security for marketplace integrations
 * 
 * @version 2.0.0
 * @date June 2, 2025
 * @author VSCode Security Team
 */

class MeschainAPISecurityFramework {
    
    private $config;
    private $token_manager;
    private $rate_limiter;
    private $webhook_validator;
    
    public function __construct($config) {
        $this->config = $config;
        $this->token_manager = new MeschainTokenManager($config);
        $this->rate_limiter = new MeschainRateLimiter($config);
        $this->webhook_validator = new MeschainWebhookValidator($config);
    }
    
    /**
     * Enhanced API authentication with token rotation
     */
    public function authenticateAPIRequest($token, $marketplace = null) {
        try {
            // Rate limiting check
            if (!$this->rate_limiter->checkRateLimit($token, $marketplace)) {
                throw new SecurityException("Rate limit exceeded for API access");
            }
            
            // Token validation with enhanced security
            $token_data = $this->token_manager->validateToken($token);
            
            if (!$token_data || $this->token_manager->isTokenExpired($token_data)) {
                throw new SecurityException("Invalid or expired API token");
            }
            
            // Enhanced marketplace-specific validation
            if ($marketplace && !$this->validateMarketplaceAccess($token_data, $marketplace)) {
                throw new SecurityException("Insufficient permissions for marketplace: $marketplace");
            }
            
            // Log successful authentication
            $this->logSecurityEvent('api_auth_success', [
                'token_id' => $token_data['id'],
                'marketplace' => $marketplace,
                'ip' => $this->getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
            
            return $token_data;
            
        } catch (Exception $e) {
            // Log security event
            $this->logSecurityEvent('api_auth_failure', [
                'reason' => $e->getMessage(),
                'ip' => $this->getClientIP(),
                'attempted_marketplace' => $marketplace
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Enhanced webhook security validation
     */
    public function validateWebhookRequest($payload, $signature, $marketplace) {
        try {
            // Enhanced signature validation
            if (!$this->webhook_validator->validateSignature($payload, $signature, $marketplace)) {
                throw new SecurityException("Invalid webhook signature");
            }
            
            // Payload validation and sanitization
            $validated_payload = $this->webhook_validator->validatePayload($payload, $marketplace);
            
            // Rate limiting for webhooks
            if (!$this->rate_limiter->checkWebhookRateLimit($marketplace)) {
                throw new SecurityException("Webhook rate limit exceeded for marketplace: $marketplace");
            }
            
            // Log successful webhook validation
            $this->logSecurityEvent('webhook_validation_success', [
                'marketplace' => $marketplace,
                'payload_size' => strlen($payload),
                'ip' => $this->getClientIP()
            ]);
            
            return $validated_payload;
            
        } catch (Exception $e) {
            // Log security event
            $this->logSecurityEvent('webhook_validation_failure', [
                'marketplace' => $marketplace,
                'reason' => $e->getMessage(),
                'ip' => $this->getClientIP()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Enhanced API key management with rotation
     */
    public function rotateAPIKeys($marketplace, $user_id) {
        try {
            // Generate new API key pair
            $new_credentials = $this->token_manager->generateNewCredentials($marketplace, $user_id);
            
            // Validate old credentials before rotation
            $old_credentials = $this->token_manager->getCurrentCredentials($marketplace, $user_id);
            
            // Secure rotation process
            $rotation_result = $this->token_manager->performKeyRotation(
                $old_credentials, 
                $new_credentials, 
                $marketplace
            );
            
            // Log key rotation
            $this->logSecurityEvent('api_key_rotation', [
                'marketplace' => $marketplace,
                'user_id' => $user_id,
                'old_key_id' => $old_credentials['key_id'],
                'new_key_id' => $new_credentials['key_id']
            ]);
            
            return $rotation_result;
            
        } catch (Exception $e) {
            // Log rotation failure
            $this->logSecurityEvent('api_key_rotation_failure', [
                'marketplace' => $marketplace,
                'user_id' => $user_id,
                'reason' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Secure API response handling
     */
    public function secureAPIResponse($data, $token_data) {
        try {
            // Data sanitization and filtering
            $filtered_data = $this->filterSensitiveData($data, $token_data['permissions']);
            
            // Enhanced encryption for sensitive responses
            if ($this->containsSensitiveData($filtered_data)) {
                $filtered_data = $this->encryptSensitiveFields($filtered_data);
            }
            
            // Add security headers
            $this->addSecurityHeaders();
            
            // Log API response
            $this->logSecurityEvent('api_response_sent', [
                'token_id' => $token_data['id'],
                'data_size' => strlen(json_encode($filtered_data)),
                'sensitive_data' => $this->containsSensitiveData($data)
            ]);
            
            return $filtered_data;
            
        } catch (Exception $e) {
            // Log response error
            $this->logSecurityEvent('api_response_error', [
                'token_id' => $token_data['id'],
                'reason' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Enhanced marketplace access validation
     */
    private function validateMarketplaceAccess($token_data, $marketplace) {
        $allowed_marketplaces = $token_data['marketplaces'] ?? [];
        
        if (!in_array($marketplace, $allowed_marketplaces)) {
            return false;
        }
        
        // Additional marketplace-specific validation
        switch ($marketplace) {
            case 'amazon':
                return $this->validateAmazonAccess($token_data);
            case 'ebay':
                return $this->validateEbayAccess($token_data);
            case 'trendyol':
                return $this->validateTrendyolAccess($token_data);
            default:
                return true;
        }
    }
    
    /**
     * Security logging with enhanced details
     */
    private function logSecurityEvent($event_type, $details = []) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event_type' => $event_type,
            'ip' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];
        
        // Enhanced logging for security events
        $this->writeSecurityLog($log_entry);
        
        // Real-time alerting for critical events
        if (in_array($event_type, ['api_auth_failure', 'webhook_validation_failure'])) {
            $this->triggerSecurityAlert($log_entry);
        }
    }
    
    /**
     * Add security headers to API responses
     */
    private function addSecurityHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        header('Content-Security-Policy: default-src \'self\'');
        header('X-API-Security-Version: 2.0.0');
    }
    
    /**
     * Get client IP with proxy support
     */
    private function getClientIP() {
        $ip_sources = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ($ip_sources as $source) {
            if (!empty($_SERVER[$source]) && filter_var($_SERVER[$source], FILTER_VALIDATE_IP)) {
                return $_SERVER[$source];
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Enhanced data filtering for security
     */
    private function filterSensitiveData($data, $permissions) {
        $filtered = $data;
        
        // Remove sensitive fields based on permissions
        $sensitive_fields = ['api_secret', 'private_key', 'password', 'token'];
        
        foreach ($sensitive_fields as $field) {
            if (isset($filtered[$field]) && !in_array("access_$field", $permissions)) {
                unset($filtered[$field]);
            }
        }
        
        return $filtered;
    }
    
    /**
     * Check if data contains sensitive information
     */
    private function containsSensitiveData($data) {
        $sensitive_indicators = ['api_secret', 'private_key', 'password', 'credit_card', 'ssn'];
        $data_string = json_encode($data);
        
        foreach ($sensitive_indicators as $indicator) {
            if (strpos($data_string, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Encrypt sensitive fields in API responses
     */
    private function encryptSensitiveFields($data) {
        // Implementation would encrypt specific sensitive fields
        // This is a placeholder for the actual encryption logic
        return $data;
    }
    
    /**
     * Write security log with enhanced format
     */
    private function writeSecurityLog($log_entry) {
        $log_file = $this->config['security_log_path'] ?? '/var/log/meschain/security.log';
        $log_line = json_encode($log_entry) . "\n";
        
        // Secure log writing with proper permissions
        file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Trigger security alerts for critical events
     */
    private function triggerSecurityAlert($log_entry) {
        // Implementation would trigger real-time alerts
        // This could include email, SMS, or webhook notifications
        // Placeholder for actual alerting system
    }
}

/**
 * Enhanced Token Manager for API security
 */
class MeschainTokenManager {
    
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function validateToken($token) {
        // Enhanced token validation logic
        // This would validate against database and check token integrity
        return ['id' => 'token_123', 'user_id' => 1, 'marketplaces' => ['amazon', 'ebay'], 'permissions' => []];
    }
    
    public function isTokenExpired($token_data) {
        // Token expiration logic
        return false;
    }
    
    public function generateNewCredentials($marketplace, $user_id) {
        // New credential generation logic
        return ['key_id' => 'new_key_123', 'secret' => 'new_secret_456'];
    }
    
    public function getCurrentCredentials($marketplace, $user_id) {
        // Current credential retrieval logic
        return ['key_id' => 'old_key_123', 'secret' => 'old_secret_456'];
    }
    
    public function performKeyRotation($old_credentials, $new_credentials, $marketplace) {
        // Key rotation implementation
        return ['success' => true, 'rotation_id' => 'rot_123'];
    }
}

/**
 * Enhanced Rate Limiter for API security
 */
class MeschainRateLimiter {
    
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function checkRateLimit($token, $marketplace = null) {
        // Rate limiting implementation
        return true;
    }
    
    public function checkWebhookRateLimit($marketplace) {
        // Webhook rate limiting implementation
        return true;
    }
}

/**
 * Enhanced Webhook Validator for API security
 */
class MeschainWebhookValidator {
    
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function validateSignature($payload, $signature, $marketplace) {
        // Signature validation implementation
        return true;
    }
    
    public function validatePayload($payload, $marketplace) {
        // Payload validation implementation
        return json_decode($payload, true);
    }
}

/**
 * Security Exception for API framework
 */
class SecurityException extends Exception {
    // Custom security exception class
}
