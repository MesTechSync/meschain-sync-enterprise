<?php
/**
 * MesChain Encryption Class - Enhanced Security Version
 * Provides AES-256 encryption for API credentials and sensitive data
 * 
 * Security Improvements:
 * - Uses random_bytes() instead of deprecated openssl_random_pseudo_bytes()
 * - Enhanced key management with dynamic key generation
 * - Improved error handling and validation
 * - Added encryption verification methods
 * 
 * @version 3.1.0
 * @date May 31, 2025
 * @author VSCode Security Team
 */
class MeschainEncryptionEnhanced {
    private $method = 'AES-256-CBC';
    private $key;
    private $keyFile;
    
    /**
     * Constructor
     * Initializes encryption with enhanced security
     */
    public function __construct() {
        $this->keyFile = DIR_SYSTEM . 'config/encryption.key';
        $this->key = $this->initializeEncryptionKey();
    }
    
    /**
     * Initialize encryption key with enhanced security
     * @return string 32-byte encryption key
     */
    private function initializeEncryptionKey() {
        // First priority: Use OpenCart's encryption key if available
        if (defined('ENCRYPTION_KEY') && strlen(ENCRYPTION_KEY) >= 32) {
            return substr(hash('sha256', ENCRYPTION_KEY), 0, 32);
        }
        
        // Second priority: Use stored secure key
        if (file_exists($this->keyFile) && filesize($this->keyFile) > 0) {
            $storedKey = file_get_contents($this->keyFile);
            if ($storedKey !== false && strlen($storedKey) > 0) {
                return base64_decode($storedKey);
            }
        }
        
        // Generate new secure key as last resort
        return $this->generateAndStoreSecureKey();
    }
    
    /**
     * Generate and securely store a new encryption key
     * @return string 32-byte encryption key
     */
    private function generateAndStoreSecureKey() {
        try {
            // Generate cryptographically secure key
            $secureKey = random_bytes(32);
            
            // Ensure directory exists
            $keyDir = dirname($this->keyFile);
            if (!is_dir($keyDir)) {
                mkdir($keyDir, 0700, true);
            }
            
            // Store key securely
            $stored = file_put_contents($this->keyFile, base64_encode($secureKey));
            if ($stored !== false) {
                chmod($this->keyFile, 0600); // Restrict to owner read/write only
            }
            
            return $secureKey;
            
        } catch (Exception $e) {
            // Fallback to derived key if file operations fail
            error_log('MeschainEncryption: Failed to generate secure key, using fallback');
            return substr(hash('sha256', 'MesChainSyncSecureKey2023' . microtime()), 0, 32);
        }
    }
    
    /**
     * Encrypt API credentials with enhanced security
     * @param array $data The API credentials and settings to encrypt
     * @return array Encrypted data
     */
    public function encryptApiCredentials($data) {
        if (!is_array($data)) {
            return [];
        }
        
        $encrypted = [];
        $api_keys = [
            'api_key', 
            'api_secret', 
            'client_id', 
            'client_secret', 
            'access_token', 
            'refresh_token',
            'webhook_secret',
            'private_key'
        ];
        
        foreach ($data as $key => $value) {
            // Only encrypt sensitive API data
            if (in_array($key, $api_keys) && !empty($value)) {
                $encryptedValue = $this->encrypt($value);
                if ($encryptedValue !== false) {
                    $encrypted[$key] = $encryptedValue;
                } else {
                    // Log encryption failure but don't expose the value
                    error_log("MeschainEncryption: Failed to encrypt field: {$key}");
                    $encrypted[$key] = '';
                }
            } else {
                $encrypted[$key] = $value;
            }
        }
        
        return $encrypted;
    }
    
    /**
     * Decrypt API credentials with enhanced error handling
     * @param array $data The encrypted API credentials and settings
     * @return array Decrypted data
     */
    public function decryptApiCredentials($data) {
        if (!is_array($data)) {
            return [];
        }
        
        $decrypted = [];
        $api_keys = [
            'api_key', 
            'api_secret', 
            'client_id', 
            'client_secret', 
            'access_token', 
            'refresh_token',
            'webhook_secret',
            'private_key'
        ];
        
        foreach ($data as $key => $value) {
            // Only decrypt sensitive API data
            if (in_array($key, $api_keys) && !empty($value)) {
                $decryptedValue = $this->decrypt($value);
                if ($decryptedValue !== false) {
                    $decrypted[$key] = $decryptedValue;
                } else {
                    // Log decryption failure
                    error_log("MeschainEncryption: Failed to decrypt field: {$key}");
                    $decrypted[$key] = '';
                }
            } else {
                $decrypted[$key] = $value;
            }
        }
        
        return $decrypted;
    }
    
    /**
     * Encrypt a string with enhanced security
     * @param string $value The string to encrypt
     * @return string|false The encrypted string or false on failure
     */
    public function encrypt($value) {
        if (empty($value) || !is_string($value)) {
            return '';
        }
        
        try {
            // Generate cryptographically secure IV for each encryption
            $iv = random_bytes(16);
            
            // Encrypt the data
            $encrypted = openssl_encrypt(
                $value,
                $this->method,
                $this->key,
                OPENSSL_RAW_DATA,
                $iv
            );
            
            if ($encrypted === false) {
                error_log('MeschainEncryption: OpenSSL encryption failed');
                return false;
            }
            
            // Combine IV and encrypted data for storage
            return base64_encode($iv . $encrypted);
            
        } catch (Exception $e) {
            error_log('MeschainEncryption: Encryption exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Decrypt a string with enhanced error handling
     * @param string $value The encrypted string
     * @return string|false The decrypted string or false on failure
     */
    public function decrypt($value) {
        if (empty($value) || !is_string($value)) {
            return '';
        }
        
        try {
            // Decode the base64 encoded data
            $data = base64_decode($value, true);
            if ($data === false) {
                error_log('MeschainEncryption: Invalid base64 data');
                return false;
            }
            
            // Ensure we have enough data for IV + encrypted content
            if (strlen($data) < 16) {
                error_log('MeschainEncryption: Insufficient data length');
                return false;
            }
            
            // Extract the IV (first 16 bytes) and encrypted data
            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);
            
            // Decrypt the data
            $decrypted = openssl_decrypt(
                $encrypted,
                $this->method,
                $this->key,
                OPENSSL_RAW_DATA,
                $iv
            );
            
            if ($decrypted === false) {
                error_log('MeschainEncryption: OpenSSL decryption failed');
                return false;
            }
            
            return $decrypted;
            
        } catch (Exception $e) {
            error_log('MeschainEncryption: Decryption exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify encryption integrity
     * @param array $originalData Original data before encryption
     * @param array $encryptedData Encrypted data
     * @return bool True if verification successful
     */
    public function verifyEncryption($originalData, $encryptedData) {
        try {
            $decrypted = $this->decryptApiCredentials($encryptedData);
            
            // Compare sensitive fields only
            $api_keys = [
                'api_key', 'api_secret', 'client_id', 
                'client_secret', 'access_token', 'refresh_token'
            ];
            
            foreach ($api_keys as $key) {
                if (isset($originalData[$key]) && isset($decrypted[$key])) {
                    if (!hash_equals($originalData[$key], $decrypted[$key])) {
                        return false;
                    }
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log('MeschainEncryption: Verification exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rotate encryption key (for future implementation)
     * @return bool Success status
     */
    public function rotateEncryptionKey() {
        // TODO: Implement key rotation with data re-encryption
        // This would require:
        // 1. Generate new key
        // 2. Re-encrypt all stored data
        // 3. Update key storage
        // 4. Verify re-encryption success
        
        error_log('MeschainEncryption: Key rotation not yet implemented');
        return false;
    }
    
    /**
     * Get encryption status information
     * @return array Status information
     */
    public function getEncryptionStatus() {
        return [
            'algorithm' => $this->method,
            'key_source' => $this->getKeySource(),
            'key_file_exists' => file_exists($this->keyFile),
            'key_file_readable' => is_readable($this->keyFile),
            'openssl_available' => extension_loaded('openssl'),
            'random_bytes_available' => function_exists('random_bytes')
        ];
    }
    
    /**
     * Determine the source of the encryption key
     * @return string Key source description
     */
    private function getKeySource() {
        if (defined('ENCRYPTION_KEY') && strlen(ENCRYPTION_KEY) >= 32) {
            return 'OpenCart ENCRYPTION_KEY';
        } elseif (file_exists($this->keyFile)) {
            return 'Stored secure key file';
        } else {
            return 'Generated fallback key';
        }
    }
}
