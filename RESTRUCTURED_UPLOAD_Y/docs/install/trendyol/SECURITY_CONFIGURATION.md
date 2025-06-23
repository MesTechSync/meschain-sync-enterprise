# Trendyol Modülü - Güvenlik Yapılandırması

**Versiyon:** 4.5  
**Tarih:** 20 Haziran 2025  
**Güvenlik Seviyesi:** Enterprise Grade

---

## 🔐 Güvenlik Genel Bakış

Trendyol modülü, enterprise düzeyinde güvenlik önlemleri ile tasarlanmıştır:

- **Azure Active Directory** entegrasyonu
- **JWT Token** tabanlı kimlik doğrulama
- **End-to-End** şifreleme
- **API Rate Limiting** ve DDoS koruması
- **Webhook Signature** doğrulaması
- **Azure Key Vault** ile güvenli anahtar yönetimi

---

## 🛡️ Azure Active Directory Entegrasyonu

### 1. Azure AD Yapılandırması

```bash
# Azure CLI ile uygulama kaydı
az ad app create --display-name "Trendyol-OpenCart-Integration" \
  --sign-in-audience "AzureADMyOrg" \
  --web-redirect-uris "https://your-domain.com/auth/callback"

# Service Principal oluştur
az ad sp create --id [APPLICATION_ID]

# API izinleri ver
az ad app permission add --id [APPLICATION_ID] \
  --api 00000003-0000-0000-c000-000000000000 \
  --api-permissions e1fe6dd8-ba31-4d61-89e7-88639da4683d=Scope
```

### 2. OpenCart Azure AD Yapılandırması

```php
// config/azure_ad.php
return [
    'tenant_id' => env('AZURE_TENANT_ID', 'your-tenant-id'),
    'client_id' => env('AZURE_CLIENT_ID', 'your-client-id'),
    'client_secret' => env('AZURE_CLIENT_SECRET', 'your-client-secret'),
    'redirect_uri' => env('AZURE_REDIRECT_URI', 'https://your-domain.com/auth/callback'),
    
    'scopes' => [
        'openid',
        'profile',
        'email',
        'User.Read'
    ],
    
    'endpoints' => [
        'authorize' => "https://login.microsoftonline.com/{tenant_id}/oauth2/v2.0/authorize",
        'token' => "https://login.microsoftonline.com/{tenant_id}/oauth2/v2.0/token",
        'userinfo' => "https://graph.microsoft.com/v1.0/me"
    ]
];
```

### 3. Azure AD Authentication Middleware

```php
// system/library/meschain/security/AzureAdAuth.php
class AzureAdAuth {
    
    private $config;
    private $jwt_manager;
    
    public function __construct($config) {
        $this->config = $config;
        $this->jwt_manager = new JwtManager();
    }
    
    public function authenticate($authorization_code) {
        // Authorization code ile access token al
        $token_response = $this->getAccessToken($authorization_code);
        
        // Token doğrula
        $user_info = $this->getUserInfo($token_response['access_token']);
        
        // JWT token oluştur
        $jwt_token = $this->jwt_manager->createToken([
            'user_id' => $user_info['id'],
            'email' => $user_info['mail'],
            'name' => $user_info['displayName'],
            'groups' => $this->getUserGroups($token_response['access_token']),
            'exp' => time() + 3600 // 1 saat
        ]);
        
        return [
            'jwt_token' => $jwt_token,
            'user_info' => $user_info,
            'expires_at' => time() + 3600
        ];
    }
    
    public function validateToken($jwt_token) {
        try {
            $payload = $this->jwt_manager->validateToken($jwt_token);
            
            // Token süresi kontrolü
            if ($payload['exp'] < time()) {
                throw new SecurityException('Token expired');
            }
            
            return $payload;
        } catch (Exception $e) {
            throw new SecurityException('Invalid token: ' . $e->getMessage());
        }
    }
    
    private function getAccessToken($authorization_code) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->config['endpoints']['token'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'code' => $authorization_code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->config['redirect_uri'],
                'scope' => implode(' ', $this->config['scopes'])
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200) {
            throw new SecurityException('Failed to get access token');
        }
        
        return json_decode($response, true);
    }
}
```

---

## 🔑 JWT Token Yönetimi

### 1. JWT Configuration

```php
// config/jwt.php
return [
    'secret' => env('JWT_SECRET', 'your-super-secret-jwt-key-min-256-bits'),
    'algorithm' => 'HS256',
    'expiration' => 3600, // 1 saat
    'refresh_expiration' => 604800, // 7 gün
    
    'issuer' => 'meschain-trendyol-module',
    'audience' => 'trendyol-api-client',
    
    'blacklist' => [
        'enabled' => true,
        'grace_period' => 30, // 30 saniye
    ]
];
```

### 2. JWT Manager Sınıfı

```php
// system/library/meschain/security/JwtManager.php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtManager {
    
    private $config;
    private $redis;
    
    public function __construct() {
        $this->config = require 'config/jwt.php';
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }
    
    public function createToken($payload) {
        $now = time();
        
        $jwt_payload = [
            'iss' => $this->config['issuer'],      // Issuer
            'aud' => $this->config['audience'],    // Audience
            'iat' => $now,                         // Issued At
            'exp' => $now + $this->config['expiration'], // Expiration
            'nbf' => $now,                         // Not Before
            'jti' => $this->generateJti(),         // JWT ID (unique)
            'data' => $payload                     // Custom data
        ];
        
        return JWT::encode($jwt_payload, $this->config['secret'], $this->config['algorithm']);
    }
    
    public function validateToken($token) {
        // Blacklist kontrolü
        if ($this->isTokenBlacklisted($token)) {
            throw new SecurityException('Token is blacklisted');
        }
        
        try {
            $decoded = JWT::decode($token, new Key($this->config['secret'], $this->config['algorithm']));
            return (array) $decoded;
        } catch (Exception $e) {
            throw new SecurityException('Invalid JWT token: ' . $e->getMessage());
        }
    }
    
    public function blacklistToken($token) {
        $decoded = JWT::decode($token, new Key($this->config['secret'], $this->config['algorithm']));
        $jti = $decoded->jti;
        $exp = $decoded->exp;
        
        // Token'ı blacklist'e ekle (expiration süresi kadar sakla)
        $this->redis->setex("jwt_blacklist:{$jti}", $exp - time(), 1);
    }
    
    public function refreshToken($token) {
        $payload = $this->validateToken($token);
        
        // Eski token'ı blacklist'e ekle
        $this->blacklistToken($token);
        
        // Yeni token oluştur
        return $this->createToken($payload['data']);
    }
    
    private function generateJti() {
        return bin2hex(random_bytes(16));
    }
    
    private function isTokenBlacklisted($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->config['secret'], $this->config['algorithm']));
            $jti = $decoded->jti;
            
            return $this->redis->exists("jwt_blacklist:{$jti}");
        } catch (Exception $e) {
            return true; // Geçersiz token'ı blacklisted olarak kabul et
        }
    }
}
```

---

## 🔒 API Güvenliği

### 1. Rate Limiting

```php
// system/library/meschain/security/RateLimiter.php
class RateLimiter {
    
    private $redis;
    private $limits = [
        'api_general' => ['requests' => 1000, 'window' => 3600],    // 1000/saat
        'api_products' => ['requests' => 500, 'window' => 3600],    // 500/saat  
        'api_orders' => ['requests' => 2000, 'window' => 3600],     // 2000/saat
        'webhook' => ['requests' => 10000, 'window' => 3600],       // 10000/saat
        'login' => ['requests' => 10, 'window' => 300],             // 10/5dakika
    ];
    
    public function checkLimit($identifier, $endpoint_type = 'api_general') {
        $key = "rate_limit:{$endpoint_type}:{$identifier}";
        $limit_config = $this->limits[$endpoint_type];
        
        $current_count = $this->redis->get($key) ?: 0;
        
        if ($current_count >= $limit_config['requests']) {
            $this->logRateLimitHit($identifier, $endpoint_type);
            throw new RateLimitException("Rate limit exceeded for {$endpoint_type}");
        }
        
        // İstek sayısını artır
        $this->redis->incr($key);
        $this->redis->expire($key, $limit_config['window']);
        
        // Response header'lara rate limit bilgilerini ekle
        header("X-RateLimit-Limit: " . $limit_config['requests']);
        header("X-RateLimit-Remaining: " . ($limit_config['requests'] - $current_count - 1));
        header("X-RateLimit-Window: " . $limit_config['window']);
        
        return true;
    }
    
    public function getClientIdentifier() {
        // JWT token varsa user ID kullan
        if ($jwt_payload = $this->getJwtPayload()) {
            return 'user:' . $jwt_payload['data']['user_id'];
        }
        
        // IP adresini kullan (proxy arkasında ise gerçek IP'yi al)
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
              $_SERVER['HTTP_X_REAL_IP'] ?? 
              $_SERVER['REMOTE_ADDR'];
              
        return 'ip:' . $ip;
    }
    
    private function logRateLimitHit($identifier, $endpoint_type) {
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'identifier' => $identifier,
            'endpoint_type' => $endpoint_type,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
        
        file_put_contents(
            'storage/logs/rate_limit.log',
            json_encode($log_data) . "\n",
            FILE_APPEND | LOCK_EX
        );
        
        // Sürekli rate limit aşımı varsa IP'yi geçici olarak engelle
        $this->checkForAbuse($identifier);
    }
}
```

### 2. DDoS Koruması

```php
// system/library/meschain/security/DdosProtection.php
class DdosProtection {
    
    private $redis;
    private $thresholds = [
        'requests_per_second' => 50,
        'requests_per_minute' => 500,
        'concurrent_connections' => 100,
        'suspicious_patterns' => [
            'same_user_agent_count' => 1000,
            'empty_user_agent_count' => 100,
            'bot_like_patterns' => 50
        ]
    ];
    
    public function checkForDdos($ip_address) {
        // Saniye başına istek kontrolü
        if ($this->checkRequestsPerSecond($ip_address)) {
            $this->blockIp($ip_address, 'high_rps', 300); // 5 dakika blok
            throw new SecurityException('Too many requests per second');
        }
        
        // Dakika başına istek kontrolü  
        if ($this->checkRequestsPerMinute($ip_address)) {
            $this->blockIp($ip_address, 'high_rpm', 3600); // 1 saat blok
            throw new SecurityException('Too many requests per minute');
        }
        
        // Şüpheli pattern kontrolü
        if ($this->checkSuspiciousPatterns($ip_address)) {
            $this->blockIp($ip_address, 'suspicious_pattern', 1800); // 30 dakika blok
            throw new SecurityException('Suspicious request pattern detected');
        }
        
        return true;
    }
    
    private function checkRequestsPerSecond($ip_address) {
        $key = "ddos_rps:{$ip_address}:" . time();
        $count = $this->redis->incr($key);
        $this->redis->expire($key, 1);
        
        return $count > $this->thresholds['requests_per_second'];
    }
    
    private function checkRequestsPerMinute($ip_address) {
        $key = "ddos_rpm:{$ip_address}:" . floor(time() / 60);
        $count = $this->redis->incr($key);
        $this->redis->expire($key, 60);
        
        return $count > $this->thresholds['requests_per_minute'];
    }
    
    private function blockIp($ip_address, $reason, $duration) {
        $block_data = [
            'ip' => $ip_address,
            'reason' => $reason,
            'blocked_at' => time(),
            'expires_at' => time() + $duration,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
        
        $this->redis->setex("blocked_ip:{$ip_address}", $duration, json_encode($block_data));
        
        // Azure Security Center'a bildir
        $this->reportToAzureSecurity($block_data);
        
        // Log kaydet
        $this->logSecurityEvent('ip_blocked', $block_data);
    }
    
    public function isIpBlocked($ip_address) {
        return $this->redis->exists("blocked_ip:{$ip_address}");
    }
}
```

---

## 🔐 Webhook Güvenliği

### 1. Webhook Signature Doğrulaması

```php
// catalog/model/extension/module/trendyol_webhook.php
public function verifyWebhookSignature($payload, $signature) {
    $webhook_secret = $this->config->get('trendyol_webhook_secret');
    
    if (empty($webhook_secret)) {
        throw new SecurityException('Webhook secret not configured');
    }
    
    // Trendyol webhook signature formatı
    $expected_signature = hash_hmac('sha256', $payload, $webhook_secret);
    
    // Timing attack'lara karşı güvenli karşılaştırma
    if (!hash_equals($signature, $expected_signature)) {
        $this->logSecurityEvent('webhook_signature_mismatch', [
            'received_signature' => $signature,
            'expected_signature' => $expected_signature,
            'payload_hash' => hash('sha256', $payload)
        ]);
        
        throw new SecurityException('Invalid webhook signature');
    }
    
    return true;
}
```

### 2. Webhook Rate Limiting

```php
public function processWebhook($headers, $payload) {
    // IP tabanlı rate limiting
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    if (!$this->rateLimiter->checkLimit($ip_address, 'webhook')) {
        throw new RateLimitException('Webhook rate limit exceeded');
    }
    
    // Signature doğrulaması
    $signature = $headers['X-Trendyol-Signature'] ?? '';
    $this->verifyWebhookSignature($payload, $signature);
    
    // Timestamp kontrolü (replay attack koruması)
    $timestamp = $headers['X-Trendyol-Timestamp'] ?? 0;
    
    if (abs(time() - $timestamp) > 300) { // 5 dakikadan eski webhook'ları reddet
        throw new SecurityException('Webhook timestamp too old');
    }
    
    // Duplicate webhook kontrolü
    $webhook_id = $headers['X-Trendyol-Webhook-Id'] ?? '';
    
    if ($this->isDuplicateWebhook($webhook_id)) {
        $this->logSecurityEvent('duplicate_webhook', ['webhook_id' => $webhook_id]);
        return; // Sessizce ignore et
    }
    
    // Webhook'ı işle
    return $this->handleWebhookEvent(json_decode($payload, true));
}
```

---

## 🛡️ Data Encryption

### 1. Database Encryption

```php
// system/library/meschain/security/DataEncryption.php
class DataEncryption {
    
    private $encryption_key;
    private $cipher = 'AES-256-GCM';
    
    public function __construct() {
        // Azure Key Vault'tan encryption key al
        $this->encryption_key = $this->getEncryptionKeyFromAzure();
    }
    
    public function encrypt($data) {
        $iv = random_bytes(16);
        $tag = '';
        
        $encrypted = openssl_encrypt(
            json_encode($data),
            $this->cipher,
            $this->encryption_key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        if ($encrypted === false) {
            throw new SecurityException('Encryption failed');
        }
        
        // IV, tag ve encrypted data'yı birleştir
        return base64_encode($iv . $tag . $encrypted);
    }
    
    public function decrypt($encrypted_data) {
        $data = base64_decode($encrypted_data);
        
        $iv = substr($data, 0, 16);
        $tag = substr($data, 16, 16);
        $encrypted = substr($data, 32);
        
        $decrypted = openssl_decrypt(
            $encrypted,
            $this->cipher,
            $this->encryption_key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        if ($decrypted === false) {
            throw new SecurityException('Decryption failed');
        }
        
        return json_decode($decrypted, true);
    }
    
    private function getEncryptionKeyFromAzure() {
        // Azure Key Vault'tan encryption key al
        $keyVaultClient = new AzureKeyVaultClient();
        return $keyVaultClient->getSecret('DatabaseEncryptionKey');
    }
}
```

### 2. Sensitive Data Handling

```php
// Hassas verileri encrypted olarak sakla
public function saveTrendyolCredentials($api_key, $api_secret, $supplier_id) {
    $sensitive_data = [
        'api_key' => $api_key,
        'api_secret' => $api_secret,
        'supplier_id' => $supplier_id,
        'created_at' => time()
    ];
    
    $encrypted_data = $this->dataEncryption->encrypt($sensitive_data);
    
    $this->db->query("UPDATE " . DB_PREFIX . "trendyol_settings 
                      SET value = '" . $this->db->escape($encrypted_data) . "' 
                      WHERE key = 'credentials'");
    
    // Audit log ekle
    $this->auditLogger->log('credentials_updated', [
        'user_id' => $this->user->getId(),
        'timestamp' => time()
    ]);
}
```

---

## 📊 Security Monitoring

### 1. Security Event Logging

```php
// system/library/meschain/security/SecurityLogger.php
class SecurityLogger {
    
    private $azure_monitor;
    
    public function logSecurityEvent($event_type, $details = []) {
        $event_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event_type' => $event_type,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'user_id' => $this->getCurrentUserId(),
            'session_id' => session_id(),
            'details' => $details
        ];
        
        // Local log dosyasına yaz
        file_put_contents(
            'storage/logs/security.log',
            json_encode($event_data) . "\n",
            FILE_APPEND | LOCK_EX
        );
        
        // Azure Monitor'a gönder
        $this->azure_monitor->trackSecurityEvent($event_data);
        
        // Kritik güvenlik olayları için alert gönder
        if ($this->isCriticalSecurityEvent($event_type)) {
            $this->sendSecurityAlert($event_data);
        }
    }
    
    private function isCriticalSecurityEvent($event_type) {
        $critical_events = [
            'unauthorized_access_attempt',
            'sql_injection_attempt',
            'xss_attempt',
            'csrf_token_mismatch',
            'suspicious_file_upload',
            'privilege_escalation_attempt'
        ];
        
        return in_array($event_type, $critical_events);
    }
}
```

### 2. Security Dashboard

```php
// admin/controller/extension/module/trendyol_security.php
public function securityDashboard() {
    $data['security_metrics'] = [
        'failed_login_attempts' => $this->getFailedLoginAttempts(),
        'blocked_ips' => $this->getBlockedIps(),
        'rate_limit_violations' => $this->getRateLimitViolations(),
        'webhook_signature_failures' => $this->getWebhookSignatureFailures(),
        'api_authentication_failures' => $this->getApiAuthFailures()
    ];
    
    $data['recent_security_events'] = $this->getRecentSecurityEvents();
    $data['security_recommendations'] = $this->getSecurityRecommendations();
    
    $this->response->setOutput($this->load->view('extension/module/trendyol_security_dashboard', $data));
}
```

Bu güvenlik yapılandırması, Trendyol modülünü enterprise düzeyinde güvenlik standartlarına uygun hale getirmektedir.
