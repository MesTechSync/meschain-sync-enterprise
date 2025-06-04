<?php
/**
 * MeschainConfigHelper - Merkezi Configuration Yönetimi
 * 
 * Tüm MesChain modülleri için merkezi konfigürasyon yönetimi sağlar.
 * Environment-based config, encryption, caching ve validation desteği.
 * 
 * @author MesChain Development Team
 * @version 1.0.0
 * @since 2024-01-21
 */

class MeschainConfigHelper {
    
    private $registry;
    private $db;
    private $cache;
    private $log;
    private $configs = [];
    private $encryptionKey;
    
    // Config türleri
    const TYPE_SYSTEM = 'system';
    const TYPE_MARKETPLACE = 'marketplace';
    const TYPE_API = 'api';
    const TYPE_WEBHOOK = 'webhook';
    const TYPE_TENANT = 'tenant';
    
    // Ortam türleri
    const ENV_DEVELOPMENT = 'development';
    const ENV_STAGING = 'staging';
    const ENV_PRODUCTION = 'production';
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->cache = $registry->get('cache');
        $this->log = new Log('meschain_config.log');
        
        $this->encryptionKey = $this->getEncryptionKey();
        $this->createConfigTables();
        $this->loadDefaultConfigs();
    }
    
    /**
     * Config tablolarını oluştur
     */
    private function createConfigTables() {
        // Ana config tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_configs` (
            `config_id` int(11) NOT NULL AUTO_INCREMENT,
            `config_key` varchar(255) NOT NULL,
            `config_value` longtext,
            `config_type` enum('system','marketplace','api','webhook','tenant') DEFAULT 'system',
            `environment` enum('development','staging','production') DEFAULT 'production',
            `tenant_id` int(11) DEFAULT NULL,
            `is_encrypted` tinyint(1) DEFAULT 0,
            `is_active` tinyint(1) DEFAULT 1,
            `description` text,
            `validation_rules` json,
            `created_by` int(11) DEFAULT NULL,
            `date_created` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`config_id`),
            UNIQUE KEY `unique_config` (`config_key`, `environment`, `tenant_id`),
            KEY `config_type` (`config_type`),
            KEY `environment` (`environment`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Config değişiklik geçmişi
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_config_history` (
            `history_id` int(11) NOT NULL AUTO_INCREMENT,
            `config_id` int(11) NOT NULL,
            `old_value` longtext,
            `new_value` longtext,
            `changed_by` int(11) NOT NULL,
            `change_reason` varchar(255),
            `date_changed` datetime NOT NULL,
            PRIMARY KEY (`history_id`),
            KEY `config_id` (`config_id`),
            KEY `changed_by` (`changed_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        $this->log->write('Config tabloları oluşturuldu/kontrol edildi');
    }
    
    /**
     * Varsayılan konfigürasyonları yükle
     */
    private function loadDefaultConfigs() {
        $defaults = [
            // Sistem konfigürasyonları
            'system.app_name' => 'MesChain-Sync',
            'system.version' => '1.0.0',
            'system.timezone' => 'Europe/Istanbul',
            'system.locale' => 'tr_TR',
            'system.maintenance_mode' => false,
            'system.debug_mode' => false,
            'system.log_level' => 'info',
            'system.max_execution_time' => 300,
            'system.memory_limit' => '256M',
            
            // API konfigürasyonları
            'api.rate_limit_per_minute' => 100,
            'api.rate_limit_per_hour' => 1000,
            'api.request_timeout' => 30,
            'api.retry_attempts' => 3,
            'api.retry_delay' => 5,
            'api.user_agent' => 'MesChain-Sync/1.0',
            
            // Webhook konfigürasyonları
            'webhook.enabled' => true,
            'webhook.timeout' => 30,
            'webhook.retry_attempts' => 3,
            'webhook.secret_key' => $this->generateSecretKey(),
            
            // Cache konfigürasyonları
            'cache.enabled' => true,
            'cache.default_ttl' => 3600,
            'cache.api_responses_ttl' => 300,
            'cache.config_ttl' => 7200,
            
            // Güvenlik konfigürasyonları
            'security.session_timeout' => 3600,
            'security.max_login_attempts' => 5,
            'security.lockout_duration' => 900,
            'security.password_min_length' => 8,
            'security.require_2fa' => false,
            
            // Marketplace varsayılan ayarları
            'marketplace.sync_interval' => 300,
            'marketplace.batch_size' => 100,
            'marketplace.error_threshold' => 10,
            'marketplace.auto_retry' => true
        ];
        
        foreach ($defaults as $key => $value) {
            $this->setDefault($key, $value);
        }
    }
    
    /**
     * Config değeri al
     */
    public function get($key, $defaultValue = null, $tenantId = null) {
        $cacheKey = $this->getCacheKey($key, $tenantId);
        
        // Cache'den kontrol et
        if ($this->cache && $cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        $environment = $this->getCurrentEnvironment();
        
        $sql = "SELECT config_value, is_encrypted FROM `" . DB_PREFIX . "meschain_configs` 
                WHERE config_key = '" . $this->db->escape($key) . "' 
                AND environment = '" . $this->db->escape($environment) . "'
                AND is_active = 1";
        
        if ($tenantId) {
            $sql .= " AND tenant_id = " . (int)$tenantId;
        } else {
            $sql .= " AND tenant_id IS NULL";
        }
        
        $sql .= " ORDER BY tenant_id DESC LIMIT 1";
        
        $query = $this->db->query($sql);
        
        if ($query->num_rows) {
            $value = $query->row['config_value'];
            
            // Şifrelenmiş değeri çöz
            if ($query->row['is_encrypted']) {
                $value = $this->decrypt($value);
            }
            
            // JSON decode et
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
            
            // Cache'le
            if ($this->cache) {
                $this->cache->set($cacheKey, $value, $this->get('cache.config_ttl', 7200));
            }
            
            return $value;
        }
        
        return $defaultValue;
    }
    
    /**
     * Config değeri set et
     */
    public function set($key, $value, $options = []) {
        $tenantId = $options['tenant_id'] ?? null;
        $isEncrypted = $options['encrypted'] ?? $this->shouldEncrypt($key);
        $configType = $options['type'] ?? $this->getConfigType($key);
        $description = $options['description'] ?? '';
        $validationRules = $options['validation'] ?? [];
        
        // Validation
        if (!empty($validationRules) && !$this->validateValue($value, $validationRules)) {
            throw new Exception("Config değeri validation kurallarına uymuyor: {$key}");
        }
        
        $environment = $this->getCurrentEnvironment();
        
        // Mevcut değeri al (history için)
        $oldValue = $this->get($key, null, $tenantId);
        
        // Değeri hazırla
        $configValue = is_array($value) || is_object($value) ? json_encode($value) : $value;
        
        // Şifrele
        if ($isEncrypted) {
            $configValue = $this->encrypt($configValue);
        }
        
        // Mevcut kaydı kontrol et
        $sql = "SELECT config_id FROM `" . DB_PREFIX . "meschain_configs` 
                WHERE config_key = '" . $this->db->escape($key) . "' 
                AND environment = '" . $this->db->escape($environment) . "'";
        
        if ($tenantId) {
            $sql .= " AND tenant_id = " . (int)$tenantId;
        } else {
            $sql .= " AND tenant_id IS NULL";
        }
        
        $existing = $this->db->query($sql);
        
        if ($existing->num_rows) {
            // Güncelle
            $configId = $existing->row['config_id'];
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_configs` SET
                config_value = '" . $this->db->escape($configValue) . "',
                config_type = '" . $this->db->escape($configType) . "',
                is_encrypted = " . (int)$isEncrypted . ",
                description = '" . $this->db->escape($description) . "',
                validation_rules = '" . $this->db->escape(json_encode($validationRules)) . "',
                date_modified = NOW()
                WHERE config_id = " . (int)$configId);
        } else {
            // Yeni ekle
            $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_configs` SET
                config_key = '" . $this->db->escape($key) . "',
                config_value = '" . $this->db->escape($configValue) . "',
                config_type = '" . $this->db->escape($configType) . "',
                environment = '" . $this->db->escape($environment) . "',
                tenant_id = " . ($tenantId ? (int)$tenantId : "NULL") . ",
                is_encrypted = " . (int)$isEncrypted . ",
                description = '" . $this->db->escape($description) . "',
                validation_rules = '" . $this->db->escape(json_encode($validationRules)) . "',
                created_by = " . (int)$this->getCurrentUserId() . ",
                date_created = NOW(),
                date_modified = NOW()
            ");
            $configId = $this->db->getLastId();
        }
        
        // History kaydet
        $this->saveHistory($configId, $oldValue, $value);
        
        // Cache'i temizle
        $this->clearCache($key, $tenantId);
        
        $this->log->write("Config güncellendi: {$key} = " . (is_string($value) ? $value : json_encode($value)));
        
        return true;
    }
    
    /**
     * Marketplace config'i al
     */
    public function getMarketplaceConfig($marketplace, $tenantId = null) {
        $config = [];
        
        $patterns = [
            "{$marketplace}.*",
            "marketplace.{$marketplace}.*"
        ];
        
        foreach ($patterns as $pattern) {
            $configs = $this->getByPattern($pattern, $tenantId);
            $config = array_merge($config, $configs);
        }
        
        return $config;
    }
    
    /**
     * Pattern ile config'leri al
     */
    public function getByPattern($pattern, $tenantId = null) {
        $environment = $this->getCurrentEnvironment();
        $pattern = str_replace('*', '%', $pattern);
        
        $sql = "SELECT config_key, config_value, is_encrypted FROM `" . DB_PREFIX . "meschain_configs` 
                WHERE config_key LIKE '" . $this->db->escape($pattern) . "'
                AND environment = '" . $this->db->escape($environment) . "'
                AND is_active = 1";
        
        if ($tenantId) {
            $sql .= " AND tenant_id = " . (int)$tenantId;
        } else {
            $sql .= " AND tenant_id IS NULL";
        }
        
        $query = $this->db->query($sql);
        $configs = [];
        
        foreach ($query->rows as $row) {
            $value = $row['config_value'];
            
            if ($row['is_encrypted']) {
                $value = $this->decrypt($value);
            }
            
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
            
            $configs[$row['config_key']] = $value;
        }
        
        return $configs;
    }
    
    /**
     * Environment değeri al
     */
    private function getCurrentEnvironment() {
        // ENV dosyasından veya ayarlardan al
        if (defined('MESCHAIN_ENVIRONMENT')) {
            return MESCHAIN_ENVIRONMENT;
        }
        
        if (isset($_ENV['MESCHAIN_ENV'])) {
            return $_ENV['MESCHAIN_ENV'];
        }
        
        // Varsayılan production
        return self::ENV_PRODUCTION;
    }
    
    /**
     * Config tipini belirle
     */
    private function getConfigType($key) {
        $parts = explode('.', $key);
        $prefix = $parts[0];
        
        $typeMap = [
            'system' => self::TYPE_SYSTEM,
            'api' => self::TYPE_API,
            'webhook' => self::TYPE_WEBHOOK,
            'marketplace' => self::TYPE_MARKETPLACE,
            'tenant' => self::TYPE_TENANT,
            'trendyol' => self::TYPE_MARKETPLACE,
            'n11' => self::TYPE_MARKETPLACE,
            'amazon' => self::TYPE_MARKETPLACE,
            'hepsiburada' => self::TYPE_MARKETPLACE,
            'ozon' => self::TYPE_MARKETPLACE,
            'ebay' => self::TYPE_MARKETPLACE
        ];
        
        return $typeMap[$prefix] ?? self::TYPE_SYSTEM;
    }
    
    /**
     * Şifrelenmesi gereken alanları kontrol et
     */
    private function shouldEncrypt($key) {
        $encryptedKeys = [
            'api_key', 'secret_key', 'client_secret', 'password',
            'webhook.secret_key', 'database.password', 'encryption_key'
        ];
        
        foreach ($encryptedKeys as $pattern) {
            if (strpos($key, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Değeri şifrele
     */
    private function encrypt($value) {
        if (!$this->encryptionKey) {
            return $value;
        }
        
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($value, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Değeri çöz
     */
    private function decrypt($encryptedValue) {
        if (!$this->encryptionKey) {
            return $encryptedValue;
        }
        
        $data = base64_decode($encryptedValue);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt($encrypted, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
    }
    
    /**
     * Şifreleme anahtarını al/oluştur
     */
    private function getEncryptionKey() {
        $keyFile = DIR_STORAGE . 'meschain_encryption.key';
        
        if (file_exists($keyFile)) {
            return file_get_contents($keyFile);
        }
        
        // Yeni anahtar oluştur
        $key = base64_encode(openssl_random_pseudo_bytes(32));
        file_put_contents($keyFile, $key);
        chmod($keyFile, 0600);
        
        return $key;
    }
    
    /**
     * Secret key oluştur
     */
    private function generateSecretKey() {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }
    
    /**
     * Değeri validate et
     */
    private function validateValue($value, $rules) {
        foreach ($rules as $rule => $constraint) {
            switch ($rule) {
                case 'required':
                    if ($constraint && ($value === null || $value === '')) {
                        return false;
                    }
                    break;
                    
                case 'type':
                    if (!$this->validateType($value, $constraint)) {
                        return false;
                    }
                    break;
                    
                case 'min':
                    if (is_numeric($value) && $value < $constraint) {
                        return false;
                    }
                    break;
                    
                case 'max':
                    if (is_numeric($value) && $value > $constraint) {
                        return false;
                    }
                    break;
                    
                case 'in':
                    if (!in_array($value, $constraint)) {
                        return false;
                    }
                    break;
            }
        }
        
        return true;
    }
    
    /**
     * Tip kontrolü
     */
    private function validateType($value, $expectedType) {
        switch ($expectedType) {
            case 'string': return is_string($value);
            case 'integer': return is_int($value);
            case 'boolean': return is_bool($value);
            case 'array': return is_array($value);
            case 'url': return filter_var($value, FILTER_VALIDATE_URL) !== false;
            case 'email': return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            default: return true;
        }
    }
    
    /**
     * Cache anahtarı oluştur
     */
    private function getCacheKey($key, $tenantId = null) {
        $environment = $this->getCurrentEnvironment();
        return "meschain_config_{$environment}_{$key}" . ($tenantId ? "_{$tenantId}" : '');
    }
    
    /**
     * Cache temizle
     */
    private function clearCache($key, $tenantId = null) {
        if ($this->cache) {
            $cacheKey = $this->getCacheKey($key, $tenantId);
            $this->cache->delete($cacheKey);
        }
    }
    
    /**
     * Varsayılan değeri set et (sadece yoksa)
     */
    private function setDefault($key, $value) {
        $existing = $this->get($key);
        if ($existing === null) {
            $this->set($key, $value);
        }
    }
    
    /**
     * History kaydet
     */
    private function saveHistory($configId, $oldValue, $newValue) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_config_history` SET
            config_id = " . (int)$configId . ",
            old_value = '" . $this->db->escape(is_string($oldValue) ? $oldValue : json_encode($oldValue)) . "',
            new_value = '" . $this->db->escape(is_string($newValue) ? $newValue : json_encode($newValue)) . "',
            changed_by = " . (int)$this->getCurrentUserId() . ",
            date_changed = NOW()
        ");
    }
    
    /**
     * Mevcut kullanıcı ID'sini al
     */
    private function getCurrentUserId() {
        if ($this->registry->has('user')) {
            $user = $this->registry->get('user');
            return $user->getId();
        }
        return 0;
    }
    
    /**
     * Tüm config'leri export et
     */
    public function exportConfigs($tenantId = null) {
        $configs = $this->getByPattern('*', $tenantId);
        return [
            'exported_at' => date('Y-m-d H:i:s'),
            'environment' => $this->getCurrentEnvironment(),
            'tenant_id' => $tenantId,
            'configs' => $configs
        ];
    }
    
    /**
     * Config'leri import et
     */
    public function importConfigs($data, $tenantId = null) {
        if (!isset($data['configs']) || !is_array($data['configs'])) {
            throw new Exception('Geçersiz config data');
        }
        
        $imported = 0;
        foreach ($data['configs'] as $key => $value) {
            try {
                $this->set($key, $value, ['tenant_id' => $tenantId]);
                $imported++;
            } catch (Exception $e) {
                $this->log->write("Config import hatası: {$key} - " . $e->getMessage());
            }
        }
        
        return $imported;
    }
}
?> 