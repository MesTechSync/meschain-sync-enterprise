<?php
/**
 * MesChain OAuth 2.0 Server
 * Enterprise-level OAuth implementation with JWT support
 * 
 * @category   MesChain
 * @package    Security OAuth
 * @author     MesChain Development Team
 * @copyright  2025 MesChain
 * @license    https://meschain.com/license
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

class MesChainOAuthServer {
    
    private $registry;
    private $config;
    private $cache;
    private $log;
    private $jwt_secret;
    private $token_expiry = 3600; // 1 hour
    private $refresh_token_expiry = 2592000; // 30 days
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $registry->get('config');
        $this->cache = $registry->get('cache');
        $this->log = new Log('meschain_oauth.log');
        $this->jwt_secret = $this->config->get('meschain_jwt_secret') ?: 'meschain_default_secret_2025';
    }
    
    /**
     * Authorization endpoint
     * Handles OAuth 2.0 authorization code flow
     */
    public function authorize($client_id, $redirect_uri, $response_type = 'code', $scope = '', $state = '') {
        try {
            // Validate client
            $client = $this->validateClient($client_id, $redirect_uri);
            if (!$client) {
                throw new Exception('Invalid client or redirect URI', 400);
            }
            
            // Validate response type
            if (!in_array($response_type, ['code', 'token'])) {
                throw new Exception('Unsupported response type', 400);
            }
            
            // Generate authorization code
            $auth_code = $this->generateAuthorizationCode($client_id, $scope, $redirect_uri);
            
            // Log authorization attempt
            $this->logAuthEvent('authorization_requested', $client_id, [
                'scope' => $scope,
                'response_type' => $response_type
            ]);
            
            return [
                'success' => true,
                'authorization_code' => $auth_code,
                'redirect_uri' => $redirect_uri . '?code=' . $auth_code . ($state ? '&state=' . $state : ''),
                'expires_in' => 600 // 10 minutes
            ];
            
        } catch (Exception $e) {
            $this->log->write('Authorization failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }
    
    /**
     * Token endpoint
     * Exchanges authorization code for access token
     */
    public function token($grant_type, $client_id, $client_secret, $params = []) {
        try {
            // Validate client credentials
            if (!$this->validateClientCredentials($client_id, $client_secret)) {
                throw new Exception('Invalid client credentials', 401);
            }
            
            switch ($grant_type) {
                case 'authorization_code':
                    return $this->handleAuthorizationCodeGrant($params);
                
                case 'refresh_token':
                    return $this->handleRefreshTokenGrant($params);
                
                case 'client_credentials':
                    return $this->handleClientCredentialsGrant($client_id);
                
                default:
                    throw new Exception('Unsupported grant type', 400);
            }
            
        } catch (Exception $e) {
            $this->log->write('Token generation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }
    
    /**
     * Validate access token
     */
    public function validateToken($access_token) {
        try {
            // Decode JWT token
            $payload = $this->decodeJWT($access_token);
            
            if (!$payload) {
                throw new Exception('Invalid token', 401);
            }
            
            // Check expiration
            if ($payload['exp'] < time()) {
                throw new Exception('Token expired', 401);
            }
            
            // Check if token is revoked
            if ($this->isTokenRevoked($payload['jti'])) {
                throw new Exception('Token revoked', 401);
            }
            
            return [
                'success' => true,
                'client_id' => $payload['client_id'],
                'user_id' => $payload['user_id'] ?? null,
                'scope' => $payload['scope'] ?? '',
                'expires_at' => $payload['exp']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Revoke token
     */
    public function revokeToken($token, $token_type_hint = 'access_token') {
        try {
            if ($token_type_hint === 'access_token') {
                $payload = $this->decodeJWT($token);
                if ($payload && isset($payload['jti'])) {
                    $this->addRevokedToken($payload['jti'], $payload['exp']);
                }
            } else {
                // Handle refresh token revocation
                $this->revokeRefreshToken($token);
            }
            
            $this->log->write('Token revoked: ' . substr($token, 0, 10) . '...');
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle authorization code grant
     */
    private function handleAuthorizationCodeGrant($params) {
        $code = $params['code'] ?? '';
        $redirect_uri = $params['redirect_uri'] ?? '';
        
        if (empty($code)) {
            throw new Exception('Authorization code required', 400);
        }
        
        // Validate authorization code
        $auth_data = $this->validateAuthorizationCode($code);
        if (!$auth_data) {
            throw new Exception('Invalid authorization code', 400);
        }
        
        // Verify redirect URI matches
        if ($auth_data['redirect_uri'] !== $redirect_uri) {
            throw new Exception('Redirect URI mismatch', 400);
        }
        
        // Generate tokens
        $access_token = $this->generateAccessToken($auth_data['client_id'], $auth_data['scope'], $auth_data['user_id']);
        $refresh_token = $this->generateRefreshToken($auth_data['client_id'], $auth_data['user_id']);
        
        // Clean up authorization code
        $this->deleteAuthorizationCode($code);
        
        $this->logAuthEvent('token_issued', $auth_data['client_id'], [
            'grant_type' => 'authorization_code',
            'scope' => $auth_data['scope']
        ]);
        
        return [
            'success' => true,
            'access_token' => $access_token,
            'token_type' => 'Bearer',
            'expires_in' => $this->token_expiry,
            'refresh_token' => $refresh_token,
            'scope' => $auth_data['scope']
        ];
    }
    
    /**
     * Handle refresh token grant
     */
    private function handleRefreshTokenGrant($params) {
        $refresh_token = $params['refresh_token'] ?? '';
        
        if (empty($refresh_token)) {
            throw new Exception('Refresh token required', 400);
        }
        
        // Validate refresh token
        $token_data = $this->validateRefreshToken($refresh_token);
        if (!$token_data) {
            throw new Exception('Invalid refresh token', 400);
        }
        
        // Generate new access token
        $access_token = $this->generateAccessToken($token_data['client_id'], $token_data['scope'], $token_data['user_id']);
        
        $this->logAuthEvent('token_refreshed', $token_data['client_id']);
        
        return [
            'success' => true,
            'access_token' => $access_token,
            'token_type' => 'Bearer',
            'expires_in' => $this->token_expiry,
            'scope' => $token_data['scope']
        ];
    }
    
    /**
     * Handle client credentials grant
     */
    private function handleClientCredentialsGrant($client_id) {
        // Generate access token for client-only access
        $access_token = $this->generateAccessToken($client_id, 'client_access');
        
        $this->logAuthEvent('client_token_issued', $client_id, [
            'grant_type' => 'client_credentials'
        ]);
        
        return [
            'success' => true,
            'access_token' => $access_token,
            'token_type' => 'Bearer',
            'expires_in' => $this->token_expiry,
            'scope' => 'client_access'
        ];
    }
    
    /**
     * Generate JWT access token
     */
    private function generateAccessToken($client_id, $scope = '', $user_id = null) {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        
        $payload = [
            'iss' => 'meschain-oauth',
            'sub' => $user_id ?: $client_id,
            'aud' => $client_id,
            'iat' => time(),
            'exp' => time() + $this->token_expiry,
            'jti' => bin2hex(random_bytes(16)),
            'client_id' => $client_id,
            'scope' => $scope
        ];
        
        if ($user_id) {
            $payload['user_id'] = $user_id;
        }
        
        return $this->encodeJWT($header, $payload);
    }
    
    /**
     * Generate refresh token
     */
    private function generateRefreshToken($client_id, $user_id = null) {
        $token = bin2hex(random_bytes(32));
        
        $db = $this->registry->get('db');
        $db->query("
            INSERT INTO " . DB_PREFIX . "meschain_refresh_tokens 
            (token, client_id, user_id, expires_at, created_at) 
            VALUES (
                '" . $db->escape($token) . "',
                '" . $db->escape($client_id) . "',
                " . ($user_id ? "'" . (int)$user_id . "'" : "NULL") . ",
                DATE_ADD(NOW(), INTERVAL " . $this->refresh_token_expiry . " SECOND),
                NOW()
            )
        ");
        
        return $token;
    }
    
    /**
     * Generate authorization code
     */
    private function generateAuthorizationCode($client_id, $scope, $redirect_uri, $user_id = null) {
        $code = bin2hex(random_bytes(16));
        
        $db = $this->registry->get('db');
        $db->query("
            INSERT INTO " . DB_PREFIX . "meschain_auth_codes 
            (code, client_id, user_id, scope, redirect_uri, expires_at, created_at) 
            VALUES (
                '" . $db->escape($code) . "',
                '" . $db->escape($client_id) . "',
                " . ($user_id ? "'" . (int)$user_id . "'" : "NULL") . ",
                '" . $db->escape($scope) . "',
                '" . $db->escape($redirect_uri) . "',
                DATE_ADD(NOW(), INTERVAL 600 SECOND),
                NOW()
            )
        ");
        
        return $code;
    }
    
    /**
     * Validate client
     */
    private function validateClient($client_id, $redirect_uri) {
        $db = $this->registry->get('db');
        $query = $db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_oauth_clients 
            WHERE client_id = '" . $db->escape($client_id) . "' 
            AND redirect_uri = '" . $db->escape($redirect_uri) . "' 
            AND status = 1
        ");
        
        return $query->num_rows ? $query->row : false;
    }
    
    /**
     * Validate client credentials
     */
    private function validateClientCredentials($client_id, $client_secret) {
        $db = $this->registry->get('db');
        $query = $db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_oauth_clients 
            WHERE client_id = '" . $db->escape($client_id) . "' 
            AND client_secret = '" . $db->escape($client_secret) . "' 
            AND status = 1
        ");
        
        return $query->num_rows > 0;
    }
    
    /**
     * Validate authorization code
     */
    private function validateAuthorizationCode($code) {
        $db = $this->registry->get('db');
        $query = $db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_auth_codes 
            WHERE code = '" . $db->escape($code) . "' 
            AND expires_at > NOW()
        ");
        
        return $query->num_rows ? $query->row : false;
    }
    
    /**
     * Validate refresh token
     */
    private function validateRefreshToken($token) {
        $db = $this->registry->get('db');
        $query = $db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_refresh_tokens 
            WHERE token = '" . $db->escape($token) . "' 
            AND expires_at > NOW()
        ");
        
        return $query->num_rows ? $query->row : false;
    }
    
    /**
     * Delete authorization code
     */
    private function deleteAuthorizationCode($code) {
        $db = $this->registry->get('db');
        $db->query("
            DELETE FROM " . DB_PREFIX . "meschain_auth_codes 
            WHERE code = '" . $db->escape($code) . "'
        ");
    }
    
    /**
     * Revoke refresh token
     */
    private function revokeRefreshToken($token) {
        $db = $this->registry->get('db');
        $db->query("
            DELETE FROM " . DB_PREFIX . "meschain_refresh_tokens 
            WHERE token = '" . $db->escape($token) . "'
        ");
    }
    
    /**
     * Encode JWT token
     */
    private function encodeJWT($header, $payload) {
        $header_encoded = $this->base64UrlEncode(json_encode($header));
        $payload_encoded = $this->base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac('sha256', $header_encoded . '.' . $payload_encoded, $this->jwt_secret, true);
        $signature_encoded = $this->base64UrlEncode($signature);
        
        return $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;
    }
    
    /**
     * Decode JWT token
     */
    private function decodeJWT($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        $header = json_decode($this->base64UrlDecode($parts[0]), true);
        $payload = json_decode($this->base64UrlDecode($parts[1]), true);
        $signature = $this->base64UrlDecode($parts[2]);
        
        // Verify signature
        $expected_signature = hash_hmac('sha256', $parts[0] . '.' . $parts[1], $this->jwt_secret, true);
        
        if (!hash_equals($signature, $expected_signature)) {
            return false;
        }
        
        return $payload;
    }
    
    /**
     * Base64 URL encode
     */
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    private function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
    
    /**
     * Check if token is revoked
     */
    private function isTokenRevoked($jti) {
        return $this->cache->get('revoked_token_' . $jti) !== false;
    }
    
    /**
     * Add token to revoked list
     */
    private function addRevokedToken($jti, $expires_at) {
        $ttl = $expires_at - time();
        if ($ttl > 0) {
            $this->cache->set('revoked_token_' . $jti, true, $ttl);
        }
    }
    
    /**
     * Log authentication events
     */
    private function logAuthEvent($event, $client_id, $data = []) {
        $log_data = [
            'event' => $event,
            'client_id' => $client_id,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        if ($data) {
            $log_data['additional_data'] = $data;
        }
        
        $this->log->write(json_encode($log_data));
        
        // Store in database for audit trail
        $db = $this->registry->get('db');
        $db->query("
            INSERT INTO " . DB_PREFIX . "meschain_auth_logs 
            (event, client_id, data, ip_address, user_agent, created_at) 
            VALUES (
                '" . $db->escape($event) . "',
                '" . $db->escape($client_id) . "',
                '" . $db->escape(json_encode($data)) . "',
                '" . $db->escape($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "',
                '" . $db->escape($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . "',
                NOW()
            )
        ");
    }
    
    /**
     * Get OAuth statistics
     */
    public function getStatistics() {
        $db = $this->registry->get('db');
        
        // Active tokens count
        $active_tokens = $db->query("
            SELECT COUNT(*) as count 
            FROM " . DB_PREFIX . "meschain_refresh_tokens 
            WHERE expires_at > NOW()
        ")->row['count'];
        
        // Today's authentications
        $today_auths = $db->query("
            SELECT COUNT(*) as count 
            FROM " . DB_PREFIX . "meschain_auth_logs 
            WHERE DATE(created_at) = CURDATE() 
            AND event = 'token_issued'
        ")->row['count'];
        
        // Recent auth events
        $recent_events = $db->query("
            SELECT event, client_id, created_at 
            FROM " . DB_PREFIX . "meschain_auth_logs 
            ORDER BY created_at DESC 
            LIMIT 20
        ")->rows;
        
        return [
            'active_tokens' => $active_tokens,
            'today_authentications' => $today_auths,
            'recent_events' => $recent_events
        ];
    }
} 