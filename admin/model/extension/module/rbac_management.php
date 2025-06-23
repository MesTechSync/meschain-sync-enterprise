<?php
/**
 * RBAC Management Model
 * 
 * Role-Based Access Control ve Multi-Tenant sistemi için model
 */
class ModelExtensionModuleRbacManagement extends Model {
    
    private $rbacHelper;
    
    public function __construct($registry) {
        parent::__construct($registry);
        
        // RBAC helper'ını yükle
        require_once(DIR_SYSTEM . 'library/meschain/helper/rbac.php');
        $this->rbacHelper = new MeschainRbacHelper($registry);
    }
    
    /**
     * Database tablolarını oluştur
     */
    public function install() {
        // Tenant tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_tenants` (
                `tenant_id` int(11) NOT NULL AUTO_INCREMENT,
                `tenant_name` varchar(100) NOT NULL,
                `tenant_type` enum('individual','business','enterprise') DEFAULT 'individual',
                `domain` varchar(255) DEFAULT NULL,
                `status` enum('active','inactive','suspended') DEFAULT 'active',
                `max_users` int(11) DEFAULT 5,
                `max_orders_daily` int(11) DEFAULT 100,
                `max_orders_monthly` int(11) DEFAULT 1000,
                `max_api_calls_daily` int(11) DEFAULT 1000,
                `features_enabled` text,
                `config_data` text,
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`tenant_id`),
                UNIQUE KEY `domain` (`domain`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        
        // Kullanıcı rol atamaları tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_user_roles` (
                `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `tenant_id` int(11) NOT NULL,
                `role_template` varchar(50) NOT NULL,
                `role_level` int(11) DEFAULT 1,
                `permissions` text,
                `marketplace_access` text,
                `feature_limits` text,
                `is_active` tinyint(1) DEFAULT 1,
                `assigned_by` int(11) DEFAULT NULL,
                `date_assigned` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`assignment_id`),
                UNIQUE KEY `user_tenant` (`user_id`, `tenant_id`),
                KEY `tenant_id` (`tenant_id`),
                KEY `role_template` (`role_template`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        
        // Kullanıcı oturum takibi tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_user_sessions` (
                `session_id` varchar(128) NOT NULL,
                `user_id` int(11) NOT NULL,
                `tenant_id` int(11) DEFAULT NULL,
                `ip_address` varchar(45) NOT NULL,
                `user_agent` text,
                `location` varchar(100) DEFAULT NULL,
                `login_time` datetime NOT NULL,
                `last_activity` datetime NOT NULL,
                `is_active` tinyint(1) DEFAULT 1,
                PRIMARY KEY (`session_id`),
                KEY `user_id` (`user_id`),
                KEY `tenant_id` (`tenant_id`),
                KEY `last_activity` (`last_activity`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        
        // Kullanıcı aktivite loglari tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_user_activities` (
                `activity_id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `tenant_id` int(11) DEFAULT NULL,
                `activity_type` varchar(50) NOT NULL,
                `activity_description` text,
                `ip_address` varchar(45) DEFAULT NULL,
                `user_agent` varchar(255) DEFAULT NULL,
                `request_data` text,
                `date_created` datetime NOT NULL,
                PRIMARY KEY (`activity_id`),
                KEY `user_id` (`user_id`),
                KEY `tenant_id` (`tenant_id`),
                KEY `activity_type` (`activity_type`),
                KEY `date_created` (`date_created`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        
        // Permission şablonları tablosu
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_permission_templates` (
                `template_id` int(11) NOT NULL AUTO_INCREMENT,
                `template_name` varchar(50) NOT NULL,
                `role_level` int(11) NOT NULL,
                `role_icon` varchar(10) DEFAULT NULL,
                `role_color` varchar(7) DEFAULT NULL,
                `description` text,
                `permissions` text NOT NULL,
                `marketplace_access` text,
                `feature_limits` text,
                `is_active` tinyint(1) DEFAULT 1,
                `date_created` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`template_id`),
                UNIQUE KEY `template_name` (`template_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        
        // Varsayılan permission şablonlarını ekle
        $this->insertDefaultPermissionTemplates();
        
        // Varsayılan tenant'ı oluştur
        $this->createDefaultTenant();
    }
    
    /**
     * Varsayılan permission şablonlarını ekle
     */
    private function insertDefaultPermissionTemplates() {
        $templates = [
            [
                'template_name' => 'super_admin',
                'role_level' => 100,
                'role_icon' => '👑',
                'role_color' => '#e74c3c',
                'description' => 'Süper Admin - Tüm sistem yetkilerine sahip',
                'permissions' => json_encode([
                    'system_admin' => true,
                    'tenant_management' => true,
                    'user_management' => true,
                    'marketplace_management' => true,
                    'webhook_management' => true,
                    'api_management' => true,
                    'report_access' => true,
                    'log_access' => true
                ]),
                'marketplace_access' => json_encode(['*']),
                'feature_limits' => json_encode([
                    'max_api_calls_daily' => -1,
                    'max_product_sync_daily' => -1,
                    'max_order_import_daily' => -1
                ])
            ],
            [
                'template_name' => 'admin',
                'role_level' => 80,
                'role_icon' => '👨‍💼',
                'role_color' => '#3498db',
                'description' => 'Admin - Tenant yönetimi ve marketplace işlemleri',
                'permissions' => json_encode([
                    'system_admin' => false,
                    'tenant_management' => false,
                    'user_management' => true,
                    'marketplace_management' => true,
                    'webhook_management' => true,
                    'api_management' => true,
                    'report_access' => true,
                    'log_access' => true
                ]),
                'marketplace_access' => json_encode(['*']),
                'feature_limits' => json_encode([
                    'max_api_calls_daily' => 5000,
                    'max_product_sync_daily' => 1000,
                    'max_order_import_daily' => 500
                ])
            ],
            [
                'template_name' => 'technical',
                'role_level' => 60,
                'role_icon' => '👨‍🔧',
                'role_color' => '#f39c12',
                'description' => 'Teknik Personel - API entegrasyonları ve teknik işlemler',
                'permissions' => json_encode([
                    'system_admin' => false,
                    'tenant_management' => false,
                    'user_management' => false,
                    'marketplace_management' => true,
                    'webhook_management' => true,
                    'api_management' => true,
                    'report_access' => true,
                    'log_access' => true
                ]),
                'marketplace_access' => json_encode(['trendyol', 'n11', 'amazon', 'hepsiburada']),
                'feature_limits' => json_encode([
                    'max_api_calls_daily' => 2000,
                    'max_product_sync_daily' => 500,
                    'max_order_import_daily' => 200
                ])
            ],
            [
                'template_name' => 'user',
                'role_level' => 40,
                'role_icon' => '👤',
                'role_color' => '#27ae60',
                'description' => 'Kullanıcı - Temel marketplace işlemleri',
                'permissions' => json_encode([
                    'system_admin' => false,
                    'tenant_management' => false,
                    'user_management' => false,
                    'marketplace_management' => true,
                    'webhook_management' => false,
                    'api_management' => false,
                    'report_access' => true,
                    'log_access' => false
                ]),
                'marketplace_access' => json_encode(['trendyol', 'n11']),
                'feature_limits' => json_encode([
                    'max_api_calls_daily' => 500,
                    'max_product_sync_daily' => 100,
                    'max_order_import_daily' => 50
                ])
            ],
            [
                'template_name' => 'viewer',
                'role_level' => 20,
                'role_icon' => '👁️',
                'role_color' => '#95a5a6',
                'description' => 'Görüntüleyici - Sadece okuma yetkisi',
                'permissions' => json_encode([
                    'system_admin' => false,
                    'tenant_management' => false,
                    'user_management' => false,
                    'marketplace_management' => false,
                    'webhook_management' => false,
                    'api_management' => false,
                    'report_access' => true,
                    'log_access' => false
                ]),
                'marketplace_access' => json_encode([]),
                'feature_limits' => json_encode([
                    'max_api_calls_daily' => 0,
                    'max_product_sync_daily' => 0,
                    'max_order_import_daily' => 0
                ])
            ]
        ];
        
        foreach ($templates as $template) {
            // Mevcut şablonu kontrol et
            $query = $this->db->query("SELECT template_id FROM `" . DB_PREFIX . "meschain_permission_templates` 
                                     WHERE template_name = '" . $this->db->escape($template['template_name']) . "'");
            
            if ($query->num_rows == 0) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_permission_templates` SET
                    template_name = '" . $this->db->escape($template['template_name']) . "',
                    role_level = '" . (int)$template['role_level'] . "',
                    role_icon = '" . $this->db->escape($template['role_icon']) . "',
                    role_color = '" . $this->db->escape($template['role_color']) . "',
                    description = '" . $this->db->escape($template['description']) . "',
                    permissions = '" . $this->db->escape($template['permissions']) . "',
                    marketplace_access = '" . $this->db->escape($template['marketplace_access']) . "',
                    feature_limits = '" . $this->db->escape($template['feature_limits']) . "',
                    date_created = NOW(),
                    date_modified = NOW()
                ");
            }
        }
    }
    
    /**
     * Varsayılan tenant oluştur
     */
    private function createDefaultTenant() {
        $query = $this->db->query("SELECT tenant_id FROM `" . DB_PREFIX . "meschain_tenants` WHERE tenant_name = 'Default'");
        
        if ($query->num_rows == 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_tenants` SET
                tenant_name = 'Default',
                tenant_type = 'enterprise',
                status = 'active',
                max_users = 100,
                max_orders_daily = 1000,
                max_orders_monthly = 10000,
                max_api_calls_daily = 10000,
                features_enabled = '" . json_encode(['*']) . "',
                date_created = NOW(),
                date_modified = NOW()
            ");
        }
    }
    
    /**
     * Tenant listesini getir
     */
    public function getTenants() {
        $query = $this->db->query("
            SELECT 
                t.*,
                (SELECT COUNT(*) FROM `" . DB_PREFIX . "meschain_user_roles` ur WHERE ur.tenant_id = t.tenant_id AND ur.is_active = 1) as user_count
            FROM `" . DB_PREFIX . "meschain_tenants` t
            ORDER BY t.date_created DESC
        ");
        
        $tenants = [];
        foreach ($query->rows as $row) {
            $row['features_enabled'] = json_decode($row['features_enabled'], true) ?? [];
            $tenants[] = $row;
        }
        
        return $tenants;
    }
    
    /**
     * Yeni tenant oluştur
     */
    public function createTenant($data) {
        try {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_tenants` SET
                tenant_name = '" . $this->db->escape($data['tenant_name']) . "',
                tenant_type = '" . $this->db->escape($data['tenant_type']) . "',
                domain = " . (!empty($data['domain']) ? "'" . $this->db->escape($data['domain']) . "'" : "NULL") . ",
                max_users = '" . (int)$data['max_users'] . "',
                max_orders_monthly = '" . (int)$data['max_orders_monthly'] . "',
                features_enabled = '" . $this->db->escape(json_encode($data['features_enabled'] ?? [])) . "',
                date_created = NOW(),
                date_modified = NOW()
            ");
            
            return $this->db->getLastId();
            
        } catch (Exception $e) {
            throw new Exception("Tenant oluşturulurken hata: " . $e->getMessage());
        }
    }
    
    /**
     * Kullanıcıya rol ata
     */
    public function assignUserRole($userId, $tenantId, $roleTemplate, $customPermissions = []) {
        try {
            // Mevcut atamayı kontrol et
            $query = $this->db->query("SELECT assignment_id FROM `" . DB_PREFIX . "meschain_user_roles` 
                                     WHERE user_id = '" . (int)$userId . "' AND tenant_id = '" . (int)$tenantId . "'");
            
            // Template bilgilerini al
            $templateQuery = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_permission_templates` 
                                             WHERE template_name = '" . $this->db->escape($roleTemplate) . "'");
            
            if ($templateQuery->num_rows == 0) {
                throw new Exception("Geçersiz rol şablonu");
            }
            
            $template = $templateQuery->row;
            
            // Permissions'ları birleştir
            $basePermissions = json_decode($template['permissions'], true);
            $finalPermissions = array_merge($basePermissions, $customPermissions);
            
            if ($query->num_rows > 0) {
                // Güncelle
                $this->db->query("UPDATE `" . DB_PREFIX . "meschain_user_roles` SET
                    role_template = '" . $this->db->escape($roleTemplate) . "',
                    role_level = '" . (int)$template['role_level'] . "',
                    permissions = '" . $this->db->escape(json_encode($finalPermissions)) . "',
                    marketplace_access = '" . $this->db->escape($template['marketplace_access']) . "',
                    feature_limits = '" . $this->db->escape($template['feature_limits']) . "',
                    assigned_by = '" . (int)$this->user->getId() . "',
                    date_modified = NOW()
                    WHERE assignment_id = '" . (int)$query->row['assignment_id'] . "'
                ");
            } else {
                // Yeni kayıt
                $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_user_roles` SET
                    user_id = '" . (int)$userId . "',
                    tenant_id = '" . (int)$tenantId . "',
                    role_template = '" . $this->db->escape($roleTemplate) . "',
                    role_level = '" . (int)$template['role_level'] . "',
                    permissions = '" . $this->db->escape(json_encode($finalPermissions)) . "',
                    marketplace_access = '" . $this->db->escape($template['marketplace_access']) . "',
                    feature_limits = '" . $this->db->escape($template['feature_limits']) . "',
                    assigned_by = '" . (int)$this->user->getId() . "',
                    date_assigned = NOW(),
                    date_modified = NOW()
                ");
            }
            
            return true;
            
        } catch (Exception $e) {
            throw new Exception("Rol atanırken hata: " . $e->getMessage());
        }
    }
    
    /**
     * Tenant kullanıcılarını getir
     */
    public function getTenantUsers($tenantId) {
        $assignedQuery = $this->db->query("
            SELECT 
                u.user_id, u.username, u.firstname, u.lastname, u.email,
                ur.role_template, ur.role_level, ur.permissions, ur.marketplace_access,
                ur.feature_limits, ur.date_assigned, ur.date_modified,
                pt.role_icon, pt.role_color,
                pt.template_name as role_name
            FROM `" . DB_PREFIX . "user` u
            INNER JOIN `" . DB_PREFIX . "meschain_user_roles` ur ON (u.user_id = ur.user_id)
            LEFT JOIN `" . DB_PREFIX . "meschain_permission_templates` pt ON (ur.role_template = pt.template_name)
            WHERE ur.tenant_id = '" . (int)$tenantId . "' AND ur.is_active = 1
            ORDER BY ur.role_level DESC, u.username
        ");
        
        $assigned = [];
        foreach ($assignedQuery->rows as $row) {
            $row['marketplace_access'] = json_decode($row['marketplace_access'], true) ?? [];
            $row['feature_limits'] = json_decode($row['feature_limits'], true) ?? [];
            $row['permissions'] = json_decode($row['permissions'], true) ?? [];
            $assigned[] = $row;
        }
        
        $unassignedQuery = $this->db->query("
            SELECT u.user_id, u.username, u.firstname, u.lastname, u.email
            FROM `" . DB_PREFIX . "user` u
            WHERE u.user_id NOT IN (
                SELECT ur.user_id FROM `" . DB_PREFIX . "meschain_user_roles` ur 
                WHERE ur.tenant_id = '" . (int)$tenantId . "' AND ur.is_active = 1
            )
            ORDER BY u.username
        ");
        
        return [
            'assigned_users' => $assigned,
            'unassigned_users' => $unassignedQuery->rows
        ];
    }
    
    /**
     * Permission şablonlarını getir
     */
    public function getPermissionTemplates() {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_permission_templates` 
            WHERE is_active = 1 
            ORDER BY role_level DESC
        ");
        
        $templates = [];
        foreach ($query->rows as $row) {
            $row['permissions'] = json_decode($row['permissions'], true) ?? [];
            $row['marketplace_access'] = json_decode($row['marketplace_access'], true) ?? [];
            $row['feature_limits'] = json_decode($row['feature_limits'], true) ?? [];
            $templates[] = $row;
        }
        
        return $templates;
    }
    
    /**
     * Aktif oturumları getir
     */
    public function getActiveSessions() {
        $query = $this->db->query("
            SELECT 
                s.*,
                u.username, u.firstname, u.lastname, u.email,
                t.tenant_name
            FROM `" . DB_PREFIX . "meschain_user_sessions` s
            LEFT JOIN `" . DB_PREFIX . "user` u ON (s.user_id = u.user_id)
            LEFT JOIN `" . DB_PREFIX . "meschain_tenants` t ON (s.tenant_id = t.tenant_id)
            WHERE s.is_active = 1 AND s.last_activity > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY s.last_activity DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * Kullanıcı aktivitesini logla
     */
    public function logUserActivity($userId, $tenantId, $activityType, $description, $requestData = []) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_user_activities` SET
            user_id = '" . (int)$userId . "',
            tenant_id = " . ($tenantId ? "'" . (int)$tenantId . "'" : "NULL") . ",
            activity_type = '" . $this->db->escape($activityType) . "',
            activity_description = '" . $this->db->escape($description) . "',
            ip_address = '" . $this->db->escape($this->request->server['REMOTE_ADDR'] ?? '') . "',
            user_agent = '" . $this->db->escape(substr($this->request->server['HTTP_USER_AGENT'] ?? '', 0, 255)) . "',
            request_data = '" . $this->db->escape(json_encode($requestData)) . "',
            date_created = NOW()
        ");
    }
    
    /**
     * RBAC tablolarını sil
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_user_activities`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_user_sessions`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_user_roles`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_permission_templates`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_tenants`");
    }
}
?> 