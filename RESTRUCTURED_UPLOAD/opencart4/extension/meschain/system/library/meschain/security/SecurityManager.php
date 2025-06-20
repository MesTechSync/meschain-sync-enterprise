<?php
namespace MesChain\Security;

/**
 * MesChain Security Manager
 *
 * Enterprise-grade security implementation for marketplace integrations
 * Features: Encryption, Authentication, Rate Limiting, Audit Logging
 *
 * @author MesChain Development Team
 * @version 3.0.0
 */
class SecurityManager {
    private $encryption_key;
    private $db;
    private $config;
    private $session;
    private $audit_logger;

    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->session = $registry->get('session');
        $this->encryption_key = $this->getOrCreateEncryptionKey();
        $this->audit_logger = new AuditLogger($registry);
    }

    /**
     * Encrypt sensitive data
     *
     * @param string $data Data to encrypt
     * @return string Encrypted data
     */
    public function encrypt($data) {
        if (empty($data)) {
            return '';
        }

        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt(
            $data,
            'AES-256-CBC',
            $this->encryption_key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt sensitive data
     *
     * @param string $encrypted_data Encrypted data
     * @return string Decrypted data
     */
    public function decrypt($encrypted_data) {
        if (empty($encrypted_data)) {
            return '';
        }

        $data = base64_decode($encrypted_data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        return openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $this->encryption_key,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    /**
     * Validate API request signature
     *
     * @param string $marketplace Marketplace name
     * @param array $request Request data
     * @param string $signature Request signature
     * @return bool
     */
    public function validateApiSignature($marketplace, $request, $signature) {
        $secret = $this->getMarketplaceSecret($marketplace);
        $calculated_signature = $this->generateSignature($request, $secret);

        return hash_equals($calculated_signature, $signature);
    }

    /**
     * Generate API request signature
     *
     * @param array $data Request data
     * @param string $secret Secret key
     * @return string
     */
    public function generateSignature($data, $secret) {
        ksort($data);
        $string = http_build_query($data);
        return hash_hmac('sha256', $string, $secret);
    }

    /**
     * Check rate limit for API calls
     *
     * @param string $identifier Unique identifier (IP, user ID, etc.)
     * @param string $action Action being performed
     * @param int $max_attempts Maximum attempts allowed
     * @param int $window_seconds Time window in seconds
     * @return bool
     */
    public function checkRateLimit($identifier, $action, $max_attempts = 60, $window_seconds = 60) {
        $key = 'rate_limit_' . md5($identifier . '_' . $action);
        $current_time = time();

        // Get rate limit data from cache or database
        $query = $this->db->query("
            SELECT attempts, window_start
            FROM " . DB_PREFIX . "meschain_rate_limits
            WHERE `key` = '" . $this->db->escape($key) . "'
        ");

        if ($query->num_rows) {
            $attempts = (int)$query->row['attempts'];
            $window_start = (int)$query->row['window_start'];

            // Check if window has expired
            if ($current_time - $window_start > $window_seconds) {
                // Reset window
                $this->db->query("
                    UPDATE " . DB_PREFIX . "meschain_rate_limits
                    SET attempts = 1, window_start = " . (int)$current_time . "
                    WHERE `key` = '" . $this->db->escape($key) . "'
                ");
                return true;
            }

            // Check if limit exceeded
            if ($attempts >= $max_attempts) {
                $this->audit_logger->log('security', 'rate_limit_exceeded', [
                    'identifier' => $identifier,
                    'action' => $action,
                    'attempts' => $attempts
                ]);
                return false;
            }

            // Increment attempts
            $this->db->query("
                UPDATE " . DB_PREFIX . "meschain_rate_limits
                SET attempts = attempts + 1
                WHERE `key` = '" . $this->db->escape($key) . "'
            ");
        } else {
            // Create new rate limit record
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "meschain_rate_limits
                SET `key` = '" . $this->db->escape($key) . "',
                    attempts = 1,
                    window_start = " . (int)$current_time
            );
        }

        return true;
    }

    /**
     * Validate input data
     *
     * @param mixed $data Input data
     * @param string $type Expected data type
     * @param array $rules Validation rules
     * @return array Validation result
     */
    public function validateInput($data, $type, $rules = []) {
        $errors = [];

        // Type validation
        switch ($type) {
            case 'email':
                if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Invalid email format';
                }
                break;

            case 'url':
                if (!filter_var($data, FILTER_VALIDATE_URL)) {
                    $errors[] = 'Invalid URL format';
                }
                break;

            case 'integer':
                if (!is_numeric($data) || (int)$data != $data) {
                    $errors[] = 'Must be an integer';
                }
                break;

            case 'float':
                if (!is_numeric($data)) {
                    $errors[] = 'Must be a number';
                }
                break;

            case 'string':
                if (!is_string($data)) {
                    $errors[] = 'Must be a string';
                }
                break;
        }

        // Apply additional rules
        foreach ($rules as $rule => $value) {
            switch ($rule) {
                case 'min_length':
                    if (strlen($data) < $value) {
                        $errors[] = "Minimum length is $value characters";
                    }
                    break;

                case 'max_length':
                    if (strlen($data) > $value) {
                        $errors[] = "Maximum length is $value characters";
                    }
                    break;

                case 'min':
                    if ($data < $value) {
                        $errors[] = "Minimum value is $value";
                    }
                    break;

                case 'max':
                    if ($data > $value) {
                        $errors[] = "Maximum value is $value";
                    }
                    break;

                case 'pattern':
                    if (!preg_match($value, $data)) {
                        $errors[] = "Invalid format";
                    }
                    break;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'sanitized' => $this->sanitizeInput($data, $type)
        ];
    }

    /**
     * Sanitize input data
     *
     * @param mixed $data Input data
     * @param string $type Data type
     * @return mixed Sanitized data
     */
    public function sanitizeInput($data, $type) {
        switch ($type) {
            case 'email':
                return filter_var($data, FILTER_SANITIZE_EMAIL);

            case 'url':
                return filter_var($data, FILTER_SANITIZE_URL);

            case 'integer':
                return (int)filter_var($data, FILTER_SANITIZE_NUMBER_INT);

            case 'float':
                return (float)filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

            case 'string':
            default:
                return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * Generate secure token
     *
     * @param int $length Token length
     * @return string
     */
    public function generateSecureToken($length = 32) {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    /**
     * Hash password using bcrypt
     *
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify password against hash
     *
     * @param string $password Plain text password
     * @param string $hash Password hash
     * @return bool
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Get or create encryption key
     *
     * @return string
     */
    private function getOrCreateEncryptionKey() {
        $query = $this->db->query("
            SELECT value
            FROM " . DB_PREFIX . "meschain_settings
            WHERE setting_key = 'encryption_key'
        ");

        if ($query->num_rows) {
            return base64_decode($query->row['value']);
        }

        // Generate new encryption key
        $key = openssl_random_pseudo_bytes(32);

        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_settings
            SET setting_key = 'encryption_key',
                value = '" . $this->db->escape(base64_encode($key)) . "',
                date_added = NOW()
        ");

        return $key;
    }

    /**
     * Get marketplace secret key
     *
     * @param string $marketplace Marketplace name
     * @return string
     */
    private function getMarketplaceSecret($marketplace) {
        $query = $this->db->query("
            SELECT api_secret
            FROM " . DB_PREFIX . "meschain_marketplace_settings
            WHERE marketplace = '" . $this->db->escape($marketplace) . "'
        ");

        if ($query->num_rows) {
            return $this->decrypt($query->row['api_secret']);
        }

        return '';
    }
}

/**
 * Audit Logger Class
 */
class AuditLogger {
    private $db;
    private $user;

    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->user = $registry->get('user');
    }

    /**
     * Log security event
     *
     * @param string $category Event category
     * @param string $action Action performed
     * @param array $data Additional data
     */
    public function log($category, $action, $data = []) {
        $user_id = 0;
        $username = 'system';

        if ($this->user && $this->user->getId()) {
            $user_id = $this->user->getId();
            $username = $this->user->getUserName();
        }

        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_audit_log
            SET user_id = " . (int)$user_id . ",
                username = '" . $this->db->escape($username) . "',
                category = '" . $this->db->escape($category) . "',
                action = '" . $this->db->escape($action) . "',
                data = '" . $this->db->escape(json_encode($data)) . "',
                ip_address = '" . $this->db->escape($_SERVER['REMOTE_ADDR'] ?? '') . "',
                user_agent = '" . $this->db->escape($_SERVER['HTTP_USER_AGENT'] ?? '') . "',
                date_added = NOW()
        ");
    }
}
