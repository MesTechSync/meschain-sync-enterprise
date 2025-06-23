<?php
/**
 * MesChain Encryption Class
 * Provides AES-256 encryption for API credentials and sensitive data
 */
class MeschainEncryption {
    private $method = 'AES-256-CBC';
    private $key;
    private $iv;
    
    /**
     * Constructor
     * Initializes encryption with a secure key
     */
    public function __construct() {
        // Use OpenCart's encryption key if available, or create a fallback
        if (defined('ENCRYPTION_KEY') && strlen(ENCRYPTION_KEY) >= 32) {
            $this->key = substr(hash('sha256', ENCRYPTION_KEY), 0, 32);
        } else {
            // Fallback to a hard-coded key (not ideal but better than nothing)
            $this->key = substr(hash('sha256', 'MesChainSyncSecureKey2023'), 0, 32);
        }
        
        // Create a unique IV for each instance
        $this->iv = substr(hash('sha256', microtime()), 0, 16);
    }
    
    /**
     * Encrypt API credentials
     * @param array $data The API credentials and settings to encrypt
     * @return array Encrypted data
     */
    public function encryptApiCredentials($data) {
        $encrypted = [];
        $api_keys = ['api_key', 'api_secret', 'client_id', 'client_secret', 'access_token', 'refresh_token'];
        
        foreach ($data as $key => $value) {
            // Only encrypt sensitive API data
            if (in_array($key, $api_keys) && !empty($value)) {
                $encrypted[$key] = $this->encrypt($value);
            } else {
                $encrypted[$key] = $value;
            }
        }
        
        return $encrypted;
    }
    
    /**
     * Decrypt API credentials
     * @param array $data The encrypted API credentials and settings
     * @return array Decrypted data
     */
    public function decryptApiCredentials($data) {
        $decrypted = [];
        $api_keys = ['api_key', 'api_secret', 'client_id', 'client_secret', 'access_token', 'refresh_token'];
        
        foreach ($data as $key => $value) {
            // Only decrypt sensitive API data
            if (in_array($key, $api_keys) && !empty($value)) {
                $decrypted[$key] = $this->decrypt($value);
            } else {
                $decrypted[$key] = $value;
            }
        }
        
        return $decrypted;
    }
    
    /**
     * Encrypt a string
     * @param string $value The string to encrypt
     * @return string The encrypted string
     */
    public function encrypt($value) {
        if (empty($value)) {
            return '';
        }
        
        // Generate a unique IV for each encryption
        $iv = openssl_random_pseudo_bytes(16);
        
        $encrypted = openssl_encrypt(
            $value,
            $this->method,
            $this->key,
            0,
            $iv
        );
        
        if ($encrypted === false) {
            return '';
        }
        
        // Combine IV and encrypted data for storage
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt a string
     * @param string $value The encrypted string
     * @return string The decrypted string
     */
    public function decrypt($value) {
        if (empty($value)) {
            return '';
        }
        
        $value = base64_decode($value);
        
        // Extract the IV (first 16 bytes)
        $iv = substr($value, 0, 16);
        $encrypted = substr($value, 16);
        
        $decrypted = openssl_decrypt(
            $encrypted,
            $this->method,
            $this->key,
            0,
            $iv
        );
        
        if ($decrypted === false) {
            return '';
        }
        
        return $decrypted;
    }
} 