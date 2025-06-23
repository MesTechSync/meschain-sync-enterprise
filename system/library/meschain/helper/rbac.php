<?php
/**
 * MeschainRbacHelper - Role Based Access Control & Multi-Tenant Management
 * 
 * Bu sınıf MesChain-Sync sistemi için kapsamlı kullanıcı rol yönetimi ve 
 * multi-tenant (çoklu kiracı) desteği sağlar.
 * 
 * @author MesChain Development Team
 * @version 1.0.0
 * @since 2024-01-21
 */

class MeschainRbacHelper {
    
    private $registry;
    private $config;
    private $db;
    private $user;
    private $session;
    private $log;
    
    // Kullanıcı Rolleri - Hiyerarşik yapı (yüksek rakam = yüksek yetki)
    const ROLE_SUPER_ADMIN = 100;
    const ROLE_ADMIN = 80;
    const ROLE_TECHNICAL = 60;
    const ROLE_USER = 40;
    const ROLE_VIEWER = 20;
    
    // Tenant türleri
    const TENANT_ENTERPRISE = 'enterprise';
    const TENANT_BUSINESS = 'business';
    const TENANT_INDIVIDUAL = 'individual';
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $registry->get('config');
        $this->db = $registry->get('db');
        $this->user = $registry->get('user');
        $this->session = $registry->get('session');
        $this->log = new Log('meschain_rbac.log');
        
        $this->createRbacTables();
    }
    
    /**
     * RBAC ve Multi-Tenant veritabanı tablolarını oluştur
     */
    private function createRbacTables() {
        // Tenant tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_tenants` (
            `tenant_id` int(11) NOT NULL AUTO_INCREMENT,
            `tenant_name` varchar(255) NOT NULL,
            `tenant_type` enum('enterprise','business','individual') DEFAULT 'individual',
            `domain` varchar(255),
            `settings` json,
            `status` enum('active','inactive','suspended') DEFAULT 'active',
            `max_users` int(11) DEFAULT 5,
            `max_orders_monthly` int(11) DEFAULT 1000,
            `features_enabled` json,
            `date_created` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`tenant_id`),
            UNIQUE KEY `domain` (`domain`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
        
        // Kullanıcı rolleri tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_user_roles` (
            `role_id` int(11) NOT NULL AUTO_INCREMENT,
            `tenant_id` int(11) NOT NULL,
            `user_id` int(11) NOT NULL,
            `role_level` int(11) NOT NULL,
            `role_name` varchar(100) NOT NULL,
            `permissions` json,
            `marketplace_access` json,
            `feature_limits` json,
            `date_created` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`role_id`),
            UNIQUE KEY `tenant_user` (`tenant_id`, `user_id`),
            KEY `user_id` (`user_id`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
        
        // İzin şablonları tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_permission_templates` (
            `template_id` int(11) NOT NULL AUTO_INCREMENT,
            `template_name` varchar(100) NOT NULL,
            `role_level` int(11) NOT NULL,
            `permissions` json NOT NULL,
            `marketplace_access` json,
            `feature_limits` json,
            `description` text,
            `is_system` tinyint(1) DEFAULT 0,
            `date_created` datetime NOT NULL,
            PRIMARY KEY (`template_id`),
            UNIQUE KEY `template_name` (`template_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
        
        // Kullanıcı oturum izleme tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_user_sessions` (
            `session_id` varchar(128) NOT NULL,
            `user_id` int(11) NOT NULL,
            `tenant_id` int(11) NOT NULL,
            `ip_address` varchar(45) NOT NULL,
            `user_agent` text,
            `last_activity` datetime NOT NULL,
            `permissions_cache` json,
            PRIMARY KEY (`session_id`),
            KEY `user_id` (`user_id`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
        
        $this->log->write('RBAC tabloları oluşturuldu/kontrol edildi');
    }
    
    /**
     * Varsayılan rol şablonlarını yükle
     */
    public function installDefaultPermissionTemplates() {
        $templates = [
            [
                'template_name' => 'super_admin',
                'role_level' => self::ROLE_SUPER_ADMIN,
                'permissions' => json_encode([
                    'system_admin' => true,
                    'user_management' => true,
                    'tenant_management' => true,
                    'role_management' => true,
                    'system_settings' => true,
                    'view_all_logs' => true,
                    'marketplace_management' => true,
                    'api_key_management' => true,
                    'backup_restore' => true,
                    'module_install' => true,
                    'finance_reports' => true
                ]),
                'marketplace_access' => json_encode(['*']),
                'feature_limits' => json_encode([
                    'max_api_calls_daily' => -1,
                    'max_orders_monthly' => -1,
                    'max_products' => -1
                ]),
                'description' => 'Süper Admin - Tüm sistem yetkileri',
                'is_system' => 1
            ],
            [
                'template_name' => 'admin',
                'role_level' => self::ROLE_ADMIN,
                'permissions' => json_encode([
                    'user_management' => true,
                    'role_management' => false,
                    'system_settings' => true,
                    'view_logs' => true,
                    'marketplace_management' => true,
                    'api_key_management' => true,
                    'backup_restore' => false,
                    'module_install' => false,
                    'finance_reports' => true
                ]),
                'marketplace_access' => json_encode(['*']),
                'feature_limits' => json_encode([
                    'max_api_calls_daily' => 10000,
                    'max_orders_monthly' => 5000,
                    'max_products' => 1000
                ]),
                'description' => 'Admin - Yönetici yetkileri',
                'is_system' => 1
            ],
            [
                'template_name' => 'technical',
                'role_level' => self::ROLE_TECHNICAL,
                'permissions' => json_encode([
                    'user_management' => false,
                    'role_management' => false,
                    'system_settings' => false,
                    'view_logs' => true,
                    'marketplace_management' => true,
                    'api_key_management' => false,
                    'backup_restore' => false,
                    'module_install' => false,
                    'finance_reports' => false
                ]),
                'marketplace_access' => json_encode(['trendyol', 'n11', 'amazon', 'hepsiburada']),
                'feature_limits' => json_encode([
                    'max_api_calls_daily' => 5000,
                    'max_orders_monthly' => 2000,
                    'max_products' => 500
                ]),
                'description' => 'Teknik Personel - Marketplace yönetimi',
                'is_system' => 1
            ],
            [
                'template_name' => 'user',
                'role_level' => self::ROLE_USER,
                'permissions' => json_encode([
                    'user_management' => false,
                    'role_management' => false,
                    'system_settings' => false,
                    'view_logs' => false,
                    'marketplace_management' => true,
                    'api_key_management' => false,
                    'backup_restore' => false,
                    'module_install' => false,
                    'finance_reports' => false
                ]),
                'marketplace_access' => json_encode(['trendyol', 'n11']),
                'feature_limits' => json_encode([
                    'max_api_calls_daily' => 1000,
                    'max_orders_monthly' => 500,
                    'max_products' => 100
                ]),
                'description' => 'Kullanıcı - Temel marketplace işlemleri',
                'is_system' => 1
            ],
            [
                'template_name' => 'viewer',
                'role_level' => self::ROLE_VIEWER,
                'permissions' => json_encode([
                    'user_management' => false,
                    'role_management' => false,
                    'system_settings' => false,
                    'view_logs' => false,
                    'marketplace_management' => false,
                    'api_key_management' => false,
                    'backup_restore' => false,
                    'module_install' => false,
                    'finance_reports' => false
                ]),
                'marketplace_access' => json_encode(['trendyol']),
                'feature_limits' => json_encode([
                    'max_api_calls_daily' => 100,
                    'max_orders_monthly' => 50,
                    'max_products' => 10
                ]),
                'description' => 'Görüntüleyici - Sadece okuma yetkisi',
                'is_system' => 1
            ]
        ];
        
        foreach ($templates as $template) {
            $existing = $this->db->query("SELECT template_id FROM `" . DB_PREFIX . "meschain_permission_templates` WHERE `template_name` = '" . $this->db->escape($template['template_name']) . "'");
            
            if (!$existing->num_rows) {
                $sql = "INSERT INTO `" . DB_PREFIX . "meschain_permission_templates` SET ";
                $fields = [];
                foreach ($template as $key => $value) {
                    if (is_string($value)) {
                        $fields[] = "`{$key}` = '" . $this->db->escape($value) . "'";
                    } else {
                        $fields[] = "`{$key}` = " . (int)$value;
                    }
                }
                $fields[] = "`date_created` = NOW()";
                $sql .= implode(', ', $fields);
                
                $this->db->query($sql);
                $this->log->write("Rol şablonu oluşturuldu: " . $template['template_name']);
            }
        }
    }
    
    /**
     * Tenant oluştur
     */
    public function createTenant($data) {
        try {
            $sql = "INSERT INTO `" . DB_PREFIX . "meschain_tenants` SET
                `tenant_name` = '" . $this->db->escape($data['tenant_name']) . "',
                `tenant_type` = '" . $this->db->escape($data['tenant_type'] ?? self::TENANT_INDIVIDUAL) . "',
                `domain` = '" . $this->db->escape($data['domain'] ?? '') . "',
                `settings` = '" . $this->db->escape(json_encode($data['settings'] ?? [])) . "',
                `status` = 'active',
                `max_users` = " . (int)($data['max_users'] ?? 5) . ",
                `max_orders_monthly` = " . (int)($data['max_orders_monthly'] ?? 1000) . ",
                `features_enabled` = '" . $this->db->escape(json_encode($data['features_enabled'] ?? [])) . "',
                `date_created` = NOW(),
                `date_modified` = NOW()";
            
            $this->db->query($sql);
            $tenantId = $this->db->getLastId();
            
            $this->log->write("Tenant oluşturuldu: {$data['tenant_name']} (ID: {$tenantId})");
            
            return [
                'success' => true,
                'tenant_id' => $tenantId,
                'message' => 'Tenant başarıyla oluşturuldu'
            ];
            
        } catch (Exception $e) {
            $this->log->write("Tenant oluşturma hatası: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Kullanıcıya rol ata
     */
    public function assignUserRole($userId, $tenantId, $roleTemplate, $customPermissions = []) {
        try {
            // Rol şablonunu al
            $template = $this->getPermissionTemplate($roleTemplate);
            if (!$template) {
                throw new Exception("Rol şablonu bulunamadı: {$roleTemplate}");
            }
            
            // Özel izinleri birleştir
            $permissions = json_decode($template['permissions'], true);
            if (!empty($customPermissions)) {
                $permissions = array_merge($permissions, $customPermissions);
            }
            
            // Mevcut rol varsa güncelle, yoksa ekle
            $existing = $this->db->query("SELECT role_id FROM `" . DB_PREFIX . "meschain_user_roles` WHERE `user_id` = " . (int)$userId . " AND `tenant_id` = " . (int)$tenantId);
            
            if ($existing->num_rows) {
                $sql = "UPDATE `" . DB_PREFIX . "meschain_user_roles` SET
                    `role_level` = " . (int)$template['role_level'] . ",
                    `role_name` = '" . $this->db->escape($template['template_name']) . "',
                    `permissions` = '" . $this->db->escape(json_encode($permissions)) . "',
                    `marketplace_access` = '" . $this->db->escape($template['marketplace_access']) . "',
                    `feature_limits` = '" . $this->db->escape($template['feature_limits']) . "',
                    `date_modified` = NOW()
                    WHERE `user_id` = " . (int)$userId . " AND `tenant_id` = " . (int)$tenantId;
            } else {
                $sql = "INSERT INTO `" . DB_PREFIX . "meschain_user_roles` SET
                    `tenant_id` = " . (int)$tenantId . ",
                    `user_id` = " . (int)$userId . ",
                    `role_level` = " . (int)$template['role_level'] . ",
                    `role_name` = '" . $this->db->escape($template['template_name']) . "',
                    `permissions` = '" . $this->db->escape(json_encode($permissions)) . "',
                    `marketplace_access` = '" . $this->db->escape($template['marketplace_access']) . "',
                    `feature_limits` = '" . $this->db->escape($template['feature_limits']) . "',
                    `date_created` = NOW(),
                    `date_modified` = NOW()";
            }
            
            $this->db->query($sql);
            
            $this->log->write("Kullanıcı rolü atandı: User#{$userId}, Tenant#{$tenantId}, Rol: {$roleTemplate}");
            
            return [
                'success' => true,
                'message' => 'Rol başarıyla atandı'
            ];
            
        } catch (Exception $e) {
            $this->log->write("Rol atama hatası: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Kullanıcının belirli bir izni var mı kontrol et
     */
    public function hasPermission($userId, $permission, $tenantId = null) {
        if (!$tenantId) {
            $tenantId = $this->getCurrentTenantId();
        }
        
        $query = $this->db->query("SELECT ur.permissions, ur.role_level 
            FROM `" . DB_PREFIX . "meschain_user_roles` ur 
            WHERE ur.user_id = " . (int)$userId . " 
            AND ur.tenant_id = " . (int)$tenantId);
        
        if (!$query->num_rows) {
            return false;
        }
        
        $permissions = json_decode($query->row['permissions'], true);
        $roleLevel = $query->row['role_level'];
        
        // Süper admin her şeye erişebilir
        if ($roleLevel >= self::ROLE_SUPER_ADMIN) {
            return true;
        }
        
        return isset($permissions[$permission]) && $permissions[$permission];
    }
    
    /**
     * Kullanıcının marketplace erişimi var mı kontrol et
     */
    public function hasMarketplaceAccess($userId, $marketplace, $tenantId = null) {
        if (!$tenantId) {
            $tenantId = $this->getCurrentTenantId();
        }
        
        $query = $this->db->query("SELECT ur.marketplace_access, ur.role_level 
            FROM `" . DB_PREFIX . "meschain_user_roles` ur 
            WHERE ur.user_id = " . (int)$userId . " 
            AND ur.tenant_id = " . (int)$tenantId);
        
        if (!$query->num_rows) {
            return false;
        }
        
        $marketplaceAccess = json_decode($query->row['marketplace_access'], true);
        $roleLevel = $query->row['role_level'];
        
        // Süper admin tüm marketplace'lere erişebilir
        if ($roleLevel >= self::ROLE_SUPER_ADMIN) {
            return true;
        }
        
        // Tüm marketplace'lere erişim
        if (in_array('*', $marketplaceAccess)) {
            return true;
        }
        
        return in_array($marketplace, $marketplaceAccess);
    }
    
    /**
     * Kullanıcının feature limitini kontrol et
     */
    public function checkFeatureLimit($userId, $feature, $tenantId = null) {
        if (!$tenantId) {
            $tenantId = $this->getCurrentTenantId();
        }
        
        $query = $this->db->query("SELECT ur.feature_limits, ur.role_level 
            FROM `" . DB_PREFIX . "meschain_user_roles` ur 
            WHERE ur.user_id = " . (int)$userId . " 
            AND ur.tenant_id = " . (int)$tenantId);
        
        if (!$query->num_rows) {
            return ['allowed' => false, 'limit' => 0, 'current' => 0];
        }
        
        $featureLimits = json_decode($query->row['feature_limits'], true);
        $roleLevel = $query->row['role_level'];
        
        // Süper admin için limitsiz
        if ($roleLevel >= self::ROLE_SUPER_ADMIN) {
            return ['allowed' => true, 'limit' => -1, 'current' => 0];
        }
        
        $limit = $featureLimits[$feature] ?? 0;
        
        // Güncel kullanımı hesapla (bu marketplace'e özel olabilir)
        $current = $this->getCurrentUsage($userId, $feature, $tenantId);
        
        return [
            'allowed' => $limit == -1 || $current < $limit,
            'limit' => $limit,
            'current' => $current
        ];
    }
    
    /**
     * Mevcut kullanımı hesapla
     */
    private function getCurrentUsage($userId, $feature, $tenantId) {
        switch ($feature) {
            case 'max_api_calls_daily':
                // API çağrı sayısını loglardan hesapla (bugün)
                $query = $this->db->query("
                    SELECT COUNT(*) as count 
                    FROM " . DB_PREFIX . "meschain_api_logs 
                    WHERE user_id = " . (int)$userId . " 
                    AND tenant_id = " . (int)$tenantId . "
                    AND DATE(created_at) = CURDATE()
                ");
                return $query->row['count'] ?? 0;
                
            case 'max_orders_monthly':
                // Bu ay işlenen sipariş sayısı
                $query = $this->db->query("
                    SELECT COUNT(*) as count 
                    FROM " . DB_PREFIX . "meschain_marketplace_orders 
                    WHERE user_id = " . (int)$userId . " 
                    AND tenant_id = " . (int)$tenantId . "
                    AND MONTH(created_at) = MONTH(CURDATE()) 
                    AND YEAR(created_at) = YEAR(CURDATE())
                ");
                return $query->row['count'] ?? 0;
                
            case 'max_products':
                // Yönetilen ürün sayısı
                $query = $this->db->query("
                    SELECT COUNT(*) as count 
                    FROM " . DB_PREFIX . "meschain_marketplace_products 
                    WHERE user_id = " . (int)$userId . " 
                    AND tenant_id = " . (int)$tenantId . "
                    AND status = 1
                ");
                return $query->row['count'] ?? 0;
                
            case 'max_marketplaces':
                // Aktif marketplace sayısı
                $userRole = $this->getUserRole($userId, $tenantId);
                if ($userRole && !empty($userRole['marketplace_access'])) {
                    return count($userRole['marketplace_access']);
                }
                return 0;
                
            case 'max_webhooks':
                // Aktif webhook sayısı
                $query = $this->db->query("
                    SELECT COUNT(*) as count 
                    FROM " . DB_PREFIX . "meschain_webhook_settings 
                    WHERE user_id = " . (int)$userId . " 
                    AND tenant_id = " . (int)$tenantId . "
                    AND status = 1
                ");
                return $query->row['count'] ?? 0;
                
            case 'storage_used_mb':
                // Kullanılan storage (MB)
                $query = $this->db->query("
                    SELECT COALESCE(SUM(file_size), 0) as total_size 
                    FROM " . DB_PREFIX . "meschain_file_storage 
                    WHERE user_id = " . (int)$userId . " 
                    AND tenant_id = " . (int)$tenantId . "
                ");
                return round(($query->row['total_size'] ?? 0) / (1024 * 1024), 2); // Convert to MB
                
            default:
                return 0;
        }
    }
    
    /**
     * Kullanıcının rolünü al
     */
    public function getUserRole($userId, $tenantId = null) {
        if (!$tenantId) {
            $tenantId = $this->getCurrentTenantId();
        }
        
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_user_roles` 
            WHERE `user_id` = " . (int)$userId . " 
            AND `tenant_id` = " . (int)$tenantId);
        
        if ($query->num_rows) {
            $role = $query->row;
            $role['permissions'] = json_decode($role['permissions'], true);
            $role['marketplace_access'] = json_decode($role['marketplace_access'], true);
            $role['feature_limits'] = json_decode($role['feature_limits'], true);
            return $role;
        }
        
        return null;
    }
    
    /**
     * Tenant'ın mevcut kullanıcı sayısını al
     */
    public function getTenantUserCount($tenantId) {
        $query = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_user_roles` WHERE `tenant_id` = " . (int)$tenantId);
        return $query->row['count'];
    }
    
    /**
     * Tenant bilgilerini al
     */
    public function getTenant($tenantId) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_tenants` WHERE `tenant_id` = " . (int)$tenantId);
        
        if ($query->num_rows) {
            $tenant = $query->row;
            $tenant['settings'] = json_decode($tenant['settings'], true);
            $tenant['features_enabled'] = json_decode($tenant['features_enabled'], true);
            return $tenant;
        }
        
        return null;
    }
    
    /**
     * İzin şablonunu al
     */
    public function getPermissionTemplate($templateName) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_permission_templates` WHERE `template_name` = '" . $this->db->escape($templateName) . "'");
        return $query->num_rows ? $query->row : null;
    }
    
    /**
     * Mevcut tenant ID'sini al
     */
    public function getCurrentTenantId() {
        // Session'dan al veya domain'den tespit et
        if (isset($this->session->data['tenant_id'])) {
            return $this->session->data['tenant_id'];
        }
        
        // Domain bazlı tenant tespiti
        $domain = $_SERVER['HTTP_HOST'] ?? '';
        if ($domain) {
            $query = $this->db->query("SELECT tenant_id FROM `" . DB_PREFIX . "meschain_tenants` WHERE `domain` = '" . $this->db->escape($domain) . "'");
            if ($query->num_rows) {
                $this->session->data['tenant_id'] = $query->row['tenant_id'];
                return $query->row['tenant_id'];
            }
        }
        
        // Varsayılan tenant (1)
        return 1;
    }
    
    /**
     * Kullanıcı oturumunu kaydet
     */
    public function logUserSession($userId, $tenantId) {
        $sessionId = $this->session->getId();
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Kullanıcının izinlerini cache'le
        $userRole = $this->getUserRole($userId, $tenantId);
        $permissionsCache = $userRole ? json_encode($userRole) : '{}';
        
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_user_sessions` SET
            `session_id` = '" . $this->db->escape($sessionId) . "',
            `user_id` = " . (int)$userId . ",
            `tenant_id` = " . (int)$tenantId . ",
            `ip_address` = '" . $this->db->escape($ipAddress) . "',
            `user_agent` = '" . $this->db->escape($userAgent) . "',
            `last_activity` = NOW(),
            `permissions_cache` = '" . $this->db->escape($permissionsCache) . "'
            ON DUPLICATE KEY UPDATE
            `last_activity` = NOW(),
            `permissions_cache` = '" . $this->db->escape($permissionsCache) . "'");
    }
    
    /**
     * Rol seviyesine göre renk kodu al
     */
    public function getRoleColor($roleLevel) {
        $colors = [
            self::ROLE_SUPER_ADMIN => '#8e44ad', // Mor
            self::ROLE_ADMIN => '#e74c3c',       // Kırmızı
            self::ROLE_TECHNICAL => '#3498db',   // Mavi
            self::ROLE_USER => '#2ecc71',        // Yeşil
            self::ROLE_VIEWER => '#95a5a6'       // Gri
        ];
        
        return $colors[$roleLevel] ?? '#95a5a6';
    }
    
    /**
     * Rol seviyesine göre ikon al
     */
    public function getRoleIcon($roleLevel) {
        $icons = [
            self::ROLE_SUPER_ADMIN => '👑',
            self::ROLE_ADMIN => '🛡️',
            self::ROLE_TECHNICAL => '🔧',
            self::ROLE_USER => '👤',
            self::ROLE_VIEWER => '👁️'
        ];
        
        return $icons[$roleLevel] ?? '👤';
    }
    
    /**
     * Tüm permission template'larını al
     */
    public function getAllPermissionTemplates() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_permission_templates` ORDER BY `role_level` DESC");
        return $query->rows;
    }
    
    /**
     * Tenant'ın tüm kullanıcılarını al
     */
    public function getTenantUsers($tenantId) {
        $query = $this->db->query("SELECT ur.*, u.username, u.firstname, u.lastname, u.email 
            FROM `" . DB_PREFIX . "meschain_user_roles` ur
            LEFT JOIN `" . DB_PREFIX . "user` u ON (ur.user_id = u.user_id)
            WHERE ur.tenant_id = " . (int)$tenantId . "
            ORDER BY ur.role_level DESC, u.username ASC");
        
        $users = [];
        foreach ($query->rows as $row) {
            $row['permissions'] = json_decode($row['permissions'], true);
            $row['marketplace_access'] = json_decode($row['marketplace_access'], true);
            $row['feature_limits'] = json_decode($row['feature_limits'], true);
            $users[] = $row;
        }
        
        return $users;
    }
} 