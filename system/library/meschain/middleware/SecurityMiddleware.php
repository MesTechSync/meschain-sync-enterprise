<?php
/**
 * Security Middleware
 * 
 * @package    MesChain-Sync
 * @author     MezBjen Team
 * @copyright  2024 MesChain
 * @version    1.0.0
 */

namespace Meschain\Middleware;

use Meschain\Security\SecurityConfig;
use Meschain\Logger\SecurityLogger;

class SecurityMiddleware {
    
    private $logger;
    private $config;
    
    public function __construct() {
        $this->logger = new SecurityLogger();
        $this->config = new SecurityConfig();
    }
    
    /**
     * Ana güvenlik middleware'i
     * 
     * @param array $request
     * @return bool
     */
    public function handle($request) {
        try {
            // Güvenlik başlıklarını ayarla
            SecurityConfig::setSecurityHeaders();
            
            // Rate limiting kontrolü
            if (!$this->checkRateLimit($request)) {
                $this->logger->warning('Rate limit exceeded', [
                    'ip' => $this->getClientIP(),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                    'endpoint' => $_SERVER['REQUEST_URI'] ?? ''
                ]);
                return false;
            }
            
            // CSRF koruması
            if (!$this->validateCSRF($request)) {
                $this->logger->warning('CSRF validation failed', [
                    'ip' => $this->getClientIP(),
                    'referer' => $_SERVER['HTTP_REFERER'] ?? ''
                ]);
                return false;
            }
            
            // Input sanitization
            $this->sanitizeRequest($request);
            
            // XSS koruması
            $this->preventXSS($request);
            
            // SQL injection koruması
            $this->preventSQLInjection($request);
            
            // Dosya yükleme güvenliği
            if (isset($_FILES) && !empty($_FILES)) {
                if (!$this->validateFileUploads($_FILES)) {
                    return false;
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->logger->error('Security middleware error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Rate limiting kontrolü
     * 
     * @param array $request
     * @return bool
     */
    private function checkRateLimit($request) {
        $ip = $this->getClientIP();
        $endpoint = $_SERVER['REQUEST_URI'] ?? '';
        
        // API endpoint'leri için farklı limitler
        if (strpos($endpoint, '/api/') !== false) {
            return SecurityConfig::checkRateLimit($ip, 'api');
        }
        
        // Webhook endpoint'leri için
        if (strpos($endpoint, '/webhook/') !== false) {
            return SecurityConfig::checkRateLimit($ip, 'webhook');
        }
        
        // Auth endpoint'leri için
        if (strpos($endpoint, '/auth/') !== false || strpos($endpoint, '/login') !== false) {
            return SecurityConfig::checkRateLimit($ip, 'auth');
        }
        
        return true;
    }
    
    /**
     * CSRF token doğrulama
     * 
     * @param array $request
     * @return bool
     */
    private function validateCSRF($request) {
        // GET istekleri için CSRF kontrolü yapma
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return true;
        }
        
        // AJAX istekleri için özel header kontrolü
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        } else {
            $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        }
        
        if (empty($token)) {
            return false;
        }
        
        return SecurityConfig::validateCSRFToken($token);
    }
    
    /**
     * Request sanitization
     * 
     * @param array &$request
     */
    private function sanitizeRequest(&$request) {
        // GET parametrelerini temizle
        if (!empty($_GET)) {
            foreach ($_GET as $key => $value) {
                $_GET[$key] = SecurityConfig::sanitizeInput($value);
            }
        }
        
        // POST parametrelerini temizle
        if (!empty($_POST)) {
            foreach ($_POST as $key => $value) {
                if (is_array($value)) {
                    $_POST[$key] = SecurityConfig::sanitizeInput($value, 'array');
                } else {
                    $_POST[$key] = SecurityConfig::sanitizeInput($value);
                }
            }
        }
        
        // Headers'ı temizle
        $dangerous_headers = ['X-Forwarded-For', 'X-Real-IP', 'X-Originating-IP'];
        foreach ($dangerous_headers as $header) {
            if (isset($_SERVER['HTTP_' . str_replace('-', '_', strtoupper($header))])) {
                $_SERVER['HTTP_' . str_replace('-', '_', strtoupper($header))] = 
                    SecurityConfig::sanitizeInput($_SERVER['HTTP_' . str_replace('-', '_', strtoupper($header))]);
            }
        }
    }
    
    /**
     * XSS koruması
     * 
     * @param array $request
     */
    private function preventXSS($request) {
        $suspicious_patterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/vbscript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>.*?<\/iframe>/is',
            '/<object[^>]*>.*?<\/object>/is',
            '/<embed[^>]*>/i'
        ];
        
        $input_data = array_merge($_GET, $_POST);
        
        foreach ($input_data as $key => $value) {
            if (is_string($value)) {
                foreach ($suspicious_patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $this->logger->warning('XSS attempt detected', [
                            'ip' => $this->getClientIP(),
                            'parameter' => $key,
                            'value' => substr($value, 0, 100),
                            'pattern' => $pattern
                        ]);
                        
                        // Tehlikeli içeriği temizle
                        if (isset($_GET[$key])) {
                            $_GET[$key] = SecurityConfig::preventXSS($_GET[$key]);
                        }
                        if (isset($_POST[$key])) {
                            $_POST[$key] = SecurityConfig::preventXSS($_POST[$key]);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * SQL injection koruması
     * 
     * @param array $request
     */
    private function preventSQLInjection($request) {
        $sql_patterns = [
            '/(\bUNION\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|\bDROP\b|\bCREATE\b|\bALTER\b)/i',
            '/(\bOR\b|\bAND\b)\s+\d+\s*=\s*\d+/i',
            '/\'\s*(OR|AND)\s*\'/i',
            '/;\s*(DROP|DELETE|UPDATE|INSERT)/i',
            '/\/\*.*?\*\//s',
            '/--.*$/m'
        ];
        
        $input_data = array_merge($_GET, $_POST);
        
        foreach ($input_data as $key => $value) {
            if (is_string($value)) {
                foreach ($sql_patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $this->logger->warning('SQL injection attempt detected', [
                            'ip' => $this->getClientIP(),
                            'parameter' => $key,
                            'value' => substr($value, 0, 100),
                            'pattern' => $pattern
                        ]);
                        
                        // Tehlikeli içeriği temizle
                        if (isset($_GET[$key])) {
                            $_GET[$key] = SecurityConfig::escapeSql($_GET[$key]);
                        }
                        if (isset($_POST[$key])) {
                            $_POST[$key] = SecurityConfig::escapeSql($_POST[$key]);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Dosya yükleme güvenliği
     * 
     * @param array $files
     * @return bool
     */
    private function validateFileUploads($files) {
        foreach ($files as $field => $file) {
            if (is_array($file['name'])) {
                // Multiple file upload
                for ($i = 0; $i < count($file['name']); $i++) {
                    if (!$this->validateSingleFile([
                        'name' => $file['name'][$i],
                        'type' => $file['type'][$i],
                        'size' => $file['size'][$i],
                        'tmp_name' => $file['tmp_name'][$i],
                        'error' => $file['error'][$i]
                    ])) {
                        return false;
                    }
                }
            } else {
                // Single file upload
                if (!$this->validateSingleFile($file)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Tek dosya doğrulama
     * 
     * @param array $file
     * @return bool
     */
    private function validateSingleFile($file) {
        // Dosya yükleme hatası kontrolü
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->logger->warning('File upload error', [
                'error_code' => $file['error'],
                'filename' => $file['name']
            ]);
            return false;
        }
        
        // Dosya türü kontrolü
        $category = $this->getFileCategory($file['name']);
        if (!SecurityConfig::isAllowedFileType($file['name'], $category)) {
            $this->logger->warning('Unauthorized file type upload attempt', [
                'ip' => $this->getClientIP(),
                'filename' => $file['name'],
                'type' => $file['type']
            ]);
            return false;
        }
        
        // Dosya boyutu kontrolü
        if (!SecurityConfig::isAllowedFileSize($file['size'], $category)) {
            $this->logger->warning('File size limit exceeded', [
                'ip' => $this->getClientIP(),
                'filename' => $file['name'],
                'size' => $file['size']
            ]);
            return false;
        }
        
        // MIME type kontrolü
        if (!$this->validateMimeType($file)) {
            $this->logger->warning('MIME type mismatch', [
                'ip' => $this->getClientIP(),
                'filename' => $file['name'],
                'declared_type' => $file['type'],
                'actual_type' => mime_content_type($file['tmp_name'])
            ]);
            return false;
        }
        
        return true;
    }
    
    /**
     * MIME type doğrulama
     * 
     * @param array $file
     * @return bool
     */
    private function validateMimeType($file) {
        if (!file_exists($file['tmp_name'])) {
            return false;
        }
        
        $actual_mime = mime_content_type($file['tmp_name']);
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        $allowed_mimes = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'csv' => ['text/csv', 'text/plain'],
            'zip' => ['application/zip']
        ];
        
        if (!isset($allowed_mimes[$extension])) {
            return false;
        }
        
        return in_array($actual_mime, $allowed_mimes[$extension]);
    }
    
    /**
     * Dosya kategorisi belirleme
     * 
     * @param string $filename
     * @return string
     */
    private function getFileCategory($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $document_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv'];
        $archive_extensions = ['zip', 'rar', '7z'];
        
        if (in_array($extension, $image_extensions)) {
            return 'image';
        } elseif (in_array($extension, $document_extensions)) {
            return 'document';
        } elseif (in_array($extension, $archive_extensions)) {
            return 'archive';
        }
        
        return 'unknown';
    }
    
    /**
     * Client IP adresini al
     * 
     * @return string
     */
    private function getClientIP() {
        $ip_headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Multiple IP'ler varsa ilkini al
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // IP doğrulama
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Güvenlik olayı logla
     * 
     * @param string $event
     * @param array $data
     */
    public function logSecurityEvent($event, $data = []) {
        $this->logger->info($event, array_merge($data, [
            'ip' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'timestamp' => date('Y-m-d H:i:s')
        ]));
    }
} 