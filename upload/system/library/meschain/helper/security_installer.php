<?php
/**
 * MesChain Security System Database Installer
 * 
 * @category   MesChain
 * @package    Security Helper
 * @author     MesChain Development Team
 * @copyright  2025 MesChain
 * @license    https://meschain.com/license
 * @version    1.0.0
 */

class MesChainSecurityInstaller {
    
    private $db;
    private $log;
    
    public function __construct($db) {
        $this->db = $db;
        $this->log = new Log('meschain_security_installer.log');
    }
    
    /**
     * Install all security-related database tables
     */
    public function install() {
        try {
            $this->createOAuthClientTable();
            $this->createAuthCodesTable();
            $this->createRefreshTokensTable();
            $this->createAuthLogsTable();
            $this->createRolesTable();
            $this->createPermissionsTable();
            $this->createRolePermissionsTable();
            $this->createUserRolesTable();
            $this->createUserPermissionsTable();
            $this->createRbacLogsTable();
            
            $this->log->write('All security tables created successfully');
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Security table creation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create OAuth clients table
     */
    private function createOAuthClientTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_oauth_clients` (
                `client_id` varchar(128) NOT NULL,
                `client_secret` varchar(128) NOT NULL,
                `client_name` varchar(255) NOT NULL,
                `redirect_uri` text NOT NULL,
                `scopes` text,
                `status` tinyint(1) DEFAULT 1,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`client_id`),
                KEY `status` (`status`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create authorization codes table
     */
    private function createAuthCodesTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_auth_codes` (
                `code` varchar(128) NOT NULL,
                `client_id` varchar(128) NOT NULL,
                `user_id` int(11),
                `scope` varchar(500),
                `redirect_uri` text NOT NULL,
                `expires_at` timestamp NOT NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`code`),
                KEY `client_id` (`client_id`),
                KEY `user_id` (`user_id`),
                KEY `expires_at` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create refresh tokens table
     */
    private function createRefreshTokensTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_refresh_tokens` (
                `token` varchar(128) NOT NULL,
                `client_id` varchar(128) NOT NULL,
                `user_id` int(11),
                `scope` varchar(500),
                `expires_at` timestamp NOT NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`token`),
                KEY `client_id` (`client_id`),
                KEY `user_id` (`user_id`),
                KEY `expires_at` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create authentication logs table
     */
    private function createAuthLogsTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_auth_logs` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `event` varchar(100) NOT NULL,
                `client_id` varchar(128),
                `user_id` int(11),
                `data` text,
                `ip_address` varchar(45),
                `user_agent` text,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`log_id`),
                KEY `event` (`event`),
                KEY `client_id` (`client_id`),
                KEY `user_id` (`user_id`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create roles table
     */
    private function createRolesTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_roles` (
                `role_id` int(11) NOT NULL AUTO_INCREMENT,
                `role_name` varchar(100) NOT NULL,
                `description` text,
                `status` tinyint(1) DEFAULT 1,
                `created_by` int(11),
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`role_id`),
                UNIQUE KEY `role_name` (`role_name`),
                KEY `status` (`status`),
                KEY `created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create permissions table
     */
    private function createPermissionsTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_permissions` (
                `permission_id` int(11) NOT NULL AUTO_INCREMENT,
                `permission_name` varchar(255) NOT NULL,
                `description` text,
                `resource_type` varchar(100),
                `status` tinyint(1) DEFAULT 1,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`permission_id`),
                UNIQUE KEY `permission_name` (`permission_name`),
                KEY `resource_type` (`resource_type`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create role permissions table
     */
    private function createRolePermissionsTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_role_permissions` (
                `role_id` int(11) NOT NULL,
                `permission_id` int(11) NOT NULL,
                `assigned_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`role_id`, `permission_id`),
                KEY `role_id` (`role_id`),
                KEY `permission_id` (`permission_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create user roles table
     */
    private function createUserRolesTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_user_roles` (
                `user_id` int(11) NOT NULL,
                `role_id` int(11) NOT NULL,
                `assigned_by` int(11),
                `assigned_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`user_id`, `role_id`),
                KEY `user_id` (`user_id`),
                KEY `role_id` (`role_id`),
                KEY `assigned_by` (`assigned_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create user permissions table (for direct permissions)
     */
    private function createUserPermissionsTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_user_permissions` (
                `user_id` int(11) NOT NULL,
                `permission_id` int(11) NOT NULL,
                `assigned_by` int(11),
                `assigned_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`user_id`, `permission_id`),
                KEY `user_id` (`user_id`),
                KEY `permission_id` (`permission_id`),
                KEY `assigned_by` (`assigned_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Create RBAC logs table
     */
    private function createRbacLogsTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_rbac_logs` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `event` varchar(100) NOT NULL,
                `user_id` int(11),
                `role_id` int(11),
                `permission_id` int(11),
                `data` text,
                `ip_address` varchar(45),
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`log_id`),
                KEY `event` (`event`),
                KEY `user_id` (`user_id`),
                KEY `role_id` (`role_id`),
                KEY `permission_id` (`permission_id`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    /**
     * Uninstall all security tables
     */
    public function uninstall() {
        try {
            $tables = [
                'meschain_oauth_clients',
                'meschain_auth_codes', 
                'meschain_refresh_tokens',
                'meschain_auth_logs',
                'meschain_roles',
                'meschain_permissions',
                'meschain_role_permissions',
                'meschain_user_roles',
                'meschain_user_permissions',
                'meschain_rbac_logs'
            ];
            
            foreach ($tables as $table) {
                $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . $table . "`");
            }
            
            $this->log->write('All security tables removed successfully');
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Security table removal failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if security tables exist
     */
    public function checkInstallation() {
        $tables = [
            'meschain_oauth_clients',
            'meschain_roles',
            'meschain_permissions'
        ];
        
        $missing_tables = [];
        
        foreach ($tables as $table) {
            $result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
            if ($result->num_rows == 0) {
                $missing_tables[] = $table;
            }
        }
        
        return [
            'installed' => empty($missing_tables),
            'missing_tables' => $missing_tables
        ];
    }
} 