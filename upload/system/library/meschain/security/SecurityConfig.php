<?php
/**
 * Security Configuration Class
 * 
 * @package    MesChain-Sync
 * @author     MezBjen Team
 * @copyright  2024 MesChain
 * @version    1.0.0
 */

namespace Meschain\Security;

class SecurityConfig {
    
    /**
     * Güvenli HTTP başlıkları
     */
    const SECURITY_HEADERS = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' https:; frame-ancestors 'none';",
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()'
    ];
    
    /**
     * İzin verilen dosya türleri
     */
    const ALLOWED_FILE_TYPES = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv'],
        'archive' => ['zip', 'rar', '7z']
    ];
    
    /**
     * Maksimum dosya boyutları (bytes)
     */
    const MAX_FILE_SIZES = [
        'image' => 5242880,      // 5MB
        'document' => 10485760,  // 10MB
        'archive' => 52428800    // 50MB
    ];
    
    /**
     * Rate limiting konfigürasyonu
     */
    const RATE_LIMITS = [
        'api' => [
            'requests_per_minute' => 60,
            'requests_per_hour' => 1000,
            'requests_per_day' => 10000
        ],
        'webhook' => [
            'requests_per_minute' => 100,
            'requests_per_hour' => 2000,
            'requests_per_day' => 20000
        ],
        'auth' => [
            'login_attempts' => 5,
            'lockout_duration' => 900, // 15 minutes
            'password_reset_attempts' => 3
        ]
    ];
    
    /**
     * Şifre gereksinimleri
     */
    const PASSWORD_REQUIREMENTS = [
        'min_length' => 8,
        'max_length' => 128,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special_chars' => true,
        'forbidden_patterns' => [
            'password', '123456', 'qwerty', 'admin', 'root'
        ]
    ];
    
    /**
     * JWT konfigürasyonu
     */
    const JWT_CONFIG = [
        'algorithm' => 'HS256',
        'access_token_ttl' => 3600,     // 1 hour
        'refresh_token_ttl' => 604800,  // 7 days
        'issuer' => 'meschain-sync',
        'audience' => 'meschain-api'
    ];
    
    /**
     * Şifreleme konfigürasyonu
     */
    const ENCRYPTION_CONFIG = [
        'cipher' => 'AES-256-GCM',
        'key_length' => 32,
        'iv_length' => 16,
        'tag_length' => 16
    ];
    
    /**
     * Güvenlik başlıklarını ayarla
     */
    public static function setSecurityHeaders() {
        foreach (self::SECURITY_HEADERS as $header => $value) {
            header($header . ': ' . $value);
        }
    }
    
    /**
     * Dosya türü kontrolü
     * 
     * @param string $filename
     * @param string $category
     * @return bool
     */
    public static function isAllowedFileType($filename, $category = 'image') {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!isset(self::ALLOWED_FILE_TYPES[$category])) {
            return false;
        }
        
        return in_array($extension, self::ALLOWED_FILE_TYPES[$category]);
    }
    
    /**
     * Dosya boyutu kontrolü
     * 
     * @param int $filesize
     * @param string $category
     * @return bool
     */
    public static function isAllowedFileSize($filesize, $category = 'image') {
        if (!isset(self::MAX_FILE_SIZES[$category])) {
            return false;
        }
        
        return $filesize <= self::MAX_FILE_SIZES[$category];
    }
    
    /**
     * Şifre güvenlik kontrolü
     * 
     * @param string $password
     * @return array
     */
    public static function validatePassword($password) {
        $errors = [];
        $config = self::PASSWORD_REQUIREMENTS;
        
        // Uzunluk kontrolü
        if (strlen($password) < $config['min_length']) {
            $errors[] = "Password must be at least {$config['min_length']} characters long";
        }
        
        if (strlen($password) > $config['max_length']) {
            $errors[] = "Password must not exceed {$config['max_length']} characters";
        }
        
        // Büyük harf kontrolü
        if ($config['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        // Küçük harf kontrolü
        if ($config['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        // Rakam kontrolü
        if ($config['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        // Özel karakter kontrolü
        if ($config['require_special_chars'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        // Yasaklı pattern kontrolü
        foreach ($config['forbidden_patterns'] as $pattern) {
            if (stripos($password, $pattern) !== false) {
                $errors[] = "Password contains forbidden pattern: {$pattern}";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Input sanitization
     * 
     * @param mixed $input
     * @param string $type
     * @return mixed
     */
    public static function sanitizeInput($input, $type = 'string') {
        switch ($type) {
            case 'string':
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
                
            case 'email':
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
                
            case 'url':
                return filter_var(trim($input), FILTER_SANITIZE_URL);
                
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                
            case 'array':
                if (is_array($input)) {
                    return array_map(function($item) {
                        return self::sanitizeInput($item, 'string');
                    }, $input);
                }
                return [];
                
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * SQL injection koruması
     * 
     * @param string $input
     * @return string
     */
    public static function escapeSql($input) {
        // OpenCart'ın DB sınıfını kullan
        global $db;
        if (isset($db) && method_exists($db, 'escape')) {
            return $db->escape($input);
        }
        
        // Fallback
        return addslashes($input);
    }
    
    /**
     * XSS koruması
     * 
     * @param string $input
     * @return string
     */
    public static function preventXSS($input) {
        // Tehlikeli HTML etiketlerini kaldır
        $dangerous_tags = [
            'script', 'iframe', 'object', 'embed', 'form', 
            'input', 'button', 'select', 'textarea', 'link', 
            'meta', 'style', 'base', 'applet'
        ];
        
        foreach ($dangerous_tags as $tag) {
            $input = preg_replace('/<' . $tag . '[^>]*>.*?<\/' . $tag . '>/is', '', $input);
            $input = preg_replace('/<' . $tag . '[^>]*\/?>/is', '', $input);
        }
        
        // JavaScript event handler'ları kaldır
        $input = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $input);
        $input = preg_replace('/javascript:/i', '', $input);
        $input = preg_replace('/vbscript:/i', '', $input);
        
        return $input;
    }
    
    /**
     * CSRF token oluştur
     * 
     * @return string
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }
    
    /**
     * CSRF token doğrula
     * 
     * @param string $token
     * @return bool
     */
    public static function validateCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // Token süresi (1 saat)
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Rate limiting kontrolü
     * 
     * @param string $identifier
     * @param string $type
     * @return bool
     */
    public static function checkRateLimit($identifier, $type = 'api') {
        if (!isset(self::RATE_LIMITS[$type])) {
            return true;
        }
        
        $limits = self::RATE_LIMITS[$type];
        $cache_key = "rate_limit_{$type}_{$identifier}";
        
        // Basit dosya tabanlı cache (production'da Redis/Memcached kullanılmalı)
        $cache_file = sys_get_temp_dir() . '/' . md5($cache_key) . '.cache';
        
        $current_time = time();
        $requests = [];
        
        if (file_exists($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true);
            if ($data && isset($data['requests'])) {
                $requests = $data['requests'];
            }
        }
        
        // Eski istekleri temizle (1 saat öncesi)
        $requests = array_filter($requests, function($timestamp) use ($current_time) {
            return $current_time - $timestamp < 3600;
        });
        
        // Dakika başına kontrol
        $minute_requests = array_filter($requests, function($timestamp) use ($current_time) {
            return $current_time - $timestamp < 60;
        });
        
        if (count($minute_requests) >= $limits['requests_per_minute']) {
            return false;
        }
        
        // Saat başına kontrol
        if (count($requests) >= $limits['requests_per_hour']) {
            return false;
        }
        
        // Yeni isteği kaydet
        $requests[] = $current_time;
        
        file_put_contents($cache_file, json_encode(['requests' => $requests]));
        
        return true;
    }
    
    /**
     * Güvenli rastgele string oluştur
     * 
     * @param int $length
     * @return string
     */
    public static function generateSecureRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * IP adresi doğrula
     * 
     * @param string $ip
     * @return bool
     */
    public static function validateIP($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
    
    /**
     * Güvenli dosya yükleme
     * 
     * @param array $file $_FILES array element
     * @param string $upload_dir
     * @param string $category
     * @return array
     */
    public static function secureFileUpload($file, $upload_dir, $category = 'image') {
        $result = [
            'success' => false,
            'message' => '',
            'filename' => ''
        ];
        
        // Dosya kontrolü
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $result['message'] = 'Invalid file upload';
            return $result;
        }
        
        // Dosya türü kontrolü
        if (!self::isAllowedFileType($file['name'], $category)) {
            $result['message'] = 'File type not allowed';
            return $result;
        }
        
        // Dosya boyutu kontrolü
        if (!self::isAllowedFileSize($file['size'], $category)) {
            $result['message'] = 'File size exceeds limit';
            return $result;
        }
        
        // Güvenli dosya adı oluştur
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = self::generateSecureRandomString(16) . '.' . $extension;
        $filepath = rtrim($upload_dir, '/') . '/' . $filename;
        
        // Dosyayı taşı
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $result['success'] = true;
            $result['filename'] = $filename;
            $result['message'] = 'File uploaded successfully';
        } else {
            $result['message'] = 'Failed to move uploaded file';
        }
        
        return $result;
    }
} 