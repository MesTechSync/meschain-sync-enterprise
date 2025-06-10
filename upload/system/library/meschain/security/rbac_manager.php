<?php
/**
 * MesChain Role-Based Access Control (RBAC) Manager
 * Enterprise-level permission and role management
 * 
 * @category   MesChain
 * @package    Security RBAC
 * @author     MesChain Development Team
 * @copyright  2025 MesChain
 * @license    https://meschain.com/license
 * @version    1.0.0
 */

class MesChainRBACManager {
    
    private $registry;
    private $cache;
    private $log;
    private $user_permissions = [];
    
    /**
     * Constructor
     */
    public function __construct($registry) {
        $this->registry = $registry;
        $this->cache = $registry->get('cache');
        $this->log = new Log('meschain_rbac.log');
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission($user_id, $permission, $resource = null) {
        try {
            // Get user permissions (cached)
            $permissions = $this->getUserPermissions($user_id);
            
            // Check direct permission
            if (in_array($permission, $permissions)) {
                return true;
            }
            
            // Check wildcard permissions
            $permission_parts = explode('.', $permission);
            for ($i = count($permission_parts) - 1; $i > 0; $i--) {
                $wildcard = implode('.', array_slice($permission_parts, 0, $i)) . '.*';
                if (in_array($wildcard, $permissions)) {
                    return true;
                }
            }
            
            // Check resource-specific permissions
            if ($resource) {
                $resource_permission = $permission . ':' . $resource;
                if (in_array($resource_permission, $permissions)) {
                    return true;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->log->write('Permission check failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user has role
     */
    public function hasRole($user_id, $role_name) {
        $user_roles = $this->getUserRoles($user_id);
        return in_array($role_name, array_column($user_roles, 'role_name'));
    }
    
    /**
     * Assign role to user
     */
    public function assignRole($user_id, $role_id, $assigned_by = null) {
        try {
            $db = $this->registry->get('db');
            
            // Check if assignment already exists
            $existing = $db->query("
                SELECT * FROM " . DB_PREFIX . "meschain_user_roles 
                WHERE user_id = '" . (int)$user_id . "' 
                AND role_id = '" . (int)$role_id . "'
            ");
            
            if ($existing->num_rows > 0) {
                return true; // Already assigned
            }
            
            // Insert role assignment
            $db->query("
                INSERT INTO " . DB_PREFIX . "meschain_user_roles 
                (user_id, role_id, assigned_by, assigned_at) 
                VALUES (
                    '" . (int)$user_id . "',
                    '" . (int)$role_id . "',
                    " . ($assigned_by ? "'" . (int)$assigned_by . "'" : "NULL") . ",
                    NOW()
                )
            ");
            
            // Clear user permissions cache
            $this->clearUserPermissionsCache($user_id);
            
            // Log the assignment
            $this->logRBACEvent('role_assigned', $user_id, [
                'role_id' => $role_id,
                'assigned_by' => $assigned_by
            ]);
            
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Role assignment failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove role from user
     */
    public function removeRole($user_id, $role_id, $removed_by = null) {
        try {
            $db = $this->registry->get('db');
            
            $db->query("
                DELETE FROM " . DB_PREFIX . "meschain_user_roles 
                WHERE user_id = '" . (int)$user_id . "' 
                AND role_id = '" . (int)$role_id . "'
            ");
            
            // Clear user permissions cache
            $this->clearUserPermissionsCache($user_id);
            
            // Log the removal
            $this->logRBACEvent('role_removed', $user_id, [
                'role_id' => $role_id,
                'removed_by' => $removed_by
            ]);
            
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Role removal failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new role
     */
    public function createRole($role_name, $description = '', $permissions = [], $created_by = null) {
        try {
            $db = $this->registry->get('db');
            
            // Insert role
            $db->query("
                INSERT INTO " . DB_PREFIX . "meschain_roles 
                (role_name, description, created_by, created_at) 
                VALUES (
                    '" . $db->escape($role_name) . "',
                    '" . $db->escape($description) . "',
                    " . ($created_by ? "'" . (int)$created_by . "'" : "NULL") . ",
                    NOW()
                )
            ");
            
            $role_id = $db->getLastId();
            
            // Assign permissions to role
            if (!empty($permissions)) {
                foreach ($permissions as $permission_id) {
                    $this->assignPermissionToRole($role_id, $permission_id);
                }
            }
            
            // Log role creation
            $this->logRBACEvent('role_created', null, [
                'role_id' => $role_id,
                'role_name' => $role_name,
                'created_by' => $created_by
            ]);
            
            return $role_id;
            
        } catch (Exception $e) {
            $this->log->write('Role creation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new permission
     */
    public function createPermission($permission_name, $description = '', $resource_type = null) {
        try {
            $db = $this->registry->get('db');
            
            $db->query("
                INSERT INTO " . DB_PREFIX . "meschain_permissions 
                (permission_name, description, resource_type, created_at) 
                VALUES (
                    '" . $db->escape($permission_name) . "',
                    '" . $db->escape($description) . "',
                    " . ($resource_type ? "'" . $db->escape($resource_type) . "'" : "NULL") . ",
                    NOW()
                )
            ");
            
            $permission_id = $db->getLastId();
            
            // Log permission creation
            $this->logRBACEvent('permission_created', null, [
                'permission_id' => $permission_id,
                'permission_name' => $permission_name
            ]);
            
            return $permission_id;
            
        } catch (Exception $e) {
            $this->log->write('Permission creation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Assign permission to role
     */
    public function assignPermissionToRole($role_id, $permission_id) {
        try {
            $db = $this->registry->get('db');
            
            // Check if assignment already exists
            $existing = $db->query("
                SELECT * FROM " . DB_PREFIX . "meschain_role_permissions 
                WHERE role_id = '" . (int)$role_id . "' 
                AND permission_id = '" . (int)$permission_id . "'
            ");
            
            if ($existing->num_rows > 0) {
                return true; // Already assigned
            }
            
            $db->query("
                INSERT INTO " . DB_PREFIX . "meschain_role_permissions 
                (role_id, permission_id, assigned_at) 
                VALUES (
                    '" . (int)$role_id . "',
                    '" . (int)$permission_id . "',
                    NOW()
                )
            ");
            
            // Clear cache for users with this role
            $this->clearRolePermissionsCache($role_id);
            
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Permission assignment failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user permissions (cached)
     */
    private function getUserPermissions($user_id) {
        $cache_key = 'user_permissions_' . $user_id;
        $permissions = $this->cache->get($cache_key);
        
        if (!$permissions) {
            $permissions = $this->loadUserPermissions($user_id);
            $this->cache->set($cache_key, $permissions, 3600); // Cache for 1 hour
        }
        
        return $permissions;
    }
    
    /**
     * Load user permissions from database
     */
    private function loadUserPermissions($user_id) {
        $db = $this->registry->get('db');
        
        // Get permissions through roles
        $query = $db->query("
            SELECT DISTINCT p.permission_name 
            FROM " . DB_PREFIX . "meschain_permissions p
            INNER JOIN " . DB_PREFIX . "meschain_role_permissions rp ON p.permission_id = rp.permission_id
            INNER JOIN " . DB_PREFIX . "meschain_user_roles ur ON rp.role_id = ur.role_id
            WHERE ur.user_id = '" . (int)$user_id . "'
            AND p.status = 1
        ");
        
        $permissions = array_column($query->rows, 'permission_name');
        
        // Get direct user permissions
        $direct_query = $db->query("
            SELECT DISTINCT p.permission_name 
            FROM " . DB_PREFIX . "meschain_permissions p
            INNER JOIN " . DB_PREFIX . "meschain_user_permissions up ON p.permission_id = up.permission_id
            WHERE up.user_id = '" . (int)$user_id . "'
            AND p.status = 1
        ");
        
        $direct_permissions = array_column($direct_query->rows, 'permission_name');
        
        return array_unique(array_merge($permissions, $direct_permissions));
    }
    
    /**
     * Get user roles
     */
    public function getUserRoles($user_id) {
        $db = $this->registry->get('db');
        
        $query = $db->query("
            SELECT r.* 
            FROM " . DB_PREFIX . "meschain_roles r
            INNER JOIN " . DB_PREFIX . "meschain_user_roles ur ON r.role_id = ur.role_id
            WHERE ur.user_id = '" . (int)$user_id . "'
            AND r.status = 1
        ");
        
        return $query->rows;
    }
    
    /**
     * Get all roles
     */
    public function getAllRoles() {
        $db = $this->registry->get('db');
        
        $query = $db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_roles 
            WHERE status = 1 
            ORDER BY role_name
        ");
        
        return $query->rows;
    }
    
    /**
     * Get all permissions
     */
    public function getAllPermissions() {
        $db = $this->registry->get('db');
        
        $query = $db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_permissions 
            WHERE status = 1 
            ORDER BY permission_name
        ");
        
        return $query->rows;
    }
    
    /**
     * Get role permissions
     */
    public function getRolePermissions($role_id) {
        $db = $this->registry->get('db');
        
        $query = $db->query("
            SELECT p.* 
            FROM " . DB_PREFIX . "meschain_permissions p
            INNER JOIN " . DB_PREFIX . "meschain_role_permissions rp ON p.permission_id = rp.permission_id
            WHERE rp.role_id = '" . (int)$role_id . "'
            AND p.status = 1
        ");
        
        return $query->rows;
    }
    
    /**
     * Clear user permissions cache
     */
    private function clearUserPermissionsCache($user_id) {
        $this->cache->delete('user_permissions_' . $user_id);
    }
    
    /**
     * Clear role permissions cache
     */
    private function clearRolePermissionsCache($role_id) {
        // Get all users with this role and clear their cache
        $db = $this->registry->get('db');
        $query = $db->query("
            SELECT user_id 
            FROM " . DB_PREFIX . "meschain_user_roles 
            WHERE role_id = '" . (int)$role_id . "'
        ");
        
        foreach ($query->rows as $row) {
            $this->clearUserPermissionsCache($row['user_id']);
        }
    }
    
    /**
     * Initialize default roles and permissions
     */
    public function initializeDefaults() {
        try {
            // Create default permissions
            $default_permissions = [
                'marketplace.trendyol.read' => 'View Trendyol data',
                'marketplace.trendyol.write' => 'Manage Trendyol listings',
                'marketplace.n11.read' => 'View N11 data',
                'marketplace.n11.write' => 'Manage N11 listings',
                'marketplace.amazon.read' => 'View Amazon data',
                'marketplace.amazon.write' => 'Manage Amazon listings',
                'marketplace.ebay.read' => 'View eBay data',
                'marketplace.ebay.write' => 'Manage eBay listings',
                'marketplace.hepsiburada.read' => 'View Hepsiburada data',
                'marketplace.hepsiburada.write' => 'Manage Hepsiburada listings',
                'marketplace.ozon.read' => 'View Ozon data',
                'marketplace.ozon.write' => 'Manage Ozon listings',
                'analytics.view' => 'View analytics and reports',
                'analytics.export' => 'Export analytics data',
                'system.admin' => 'System administration',
                'api.gateway.manage' => 'Manage API Gateway',
                'users.manage' => 'Manage users and permissions'
            ];
            
            foreach ($default_permissions as $name => $description) {
                $this->createPermission($name, $description);
            }
            
            // Create default roles
            $admin_permissions = array_keys($default_permissions);
            $this->createRole('Super Admin', 'Full system access', $admin_permissions);
            
            $marketplace_manager_permissions = [
                'marketplace.trendyol.*',
                'marketplace.n11.*',
                'marketplace.amazon.*',
                'marketplace.ebay.*',
                'marketplace.hepsiburada.*',
                'marketplace.ozon.*',
                'analytics.view'
            ];
            $this->createRole('Marketplace Manager', 'Manage all marketplaces', $marketplace_manager_permissions);
            
            $analyst_permissions = [
                'marketplace.*.read',
                'analytics.*'
            ];
            $this->createRole('Analyst', 'View data and analytics', $analyst_permissions);
            
            $this->log->write('Default roles and permissions initialized');
            return true;
            
        } catch (Exception $e) {
            $this->log->write('Failed to initialize defaults: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log RBAC events
     */
    private function logRBACEvent($event, $user_id, $data = []) {
        $log_data = [
            'event' => $event,
            'user_id' => $user_id,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'data' => $data
        ];
        
        $this->log->write(json_encode($log_data));
        
        // Store in database
        $db = $this->registry->get('db');
        $db->query("
            INSERT INTO " . DB_PREFIX . "meschain_rbac_logs 
            (event, user_id, data, ip_address, created_at) 
            VALUES (
                '" . $db->escape($event) . "',
                " . ($user_id ? "'" . (int)$user_id . "'" : "NULL") . ",
                '" . $db->escape(json_encode($data)) . "',
                '" . $db->escape($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "',
                NOW()
            )
        ");
    }
    
    /**
     * Get RBAC statistics
     */
    public function getStatistics() {
        $db = $this->registry->get('db');
        
        $stats = [];
        
        // Count roles
        $stats['total_roles'] = $db->query("
            SELECT COUNT(*) as count 
            FROM " . DB_PREFIX . "meschain_roles 
            WHERE status = 1
        ")->row['count'];
        
        // Count permissions
        $stats['total_permissions'] = $db->query("
            SELECT COUNT(*) as count 
            FROM " . DB_PREFIX . "meschain_permissions 
            WHERE status = 1
        ")->row['count'];
        
        // Count users with roles
        $stats['users_with_roles'] = $db->query("
            SELECT COUNT(DISTINCT user_id) as count 
            FROM " . DB_PREFIX . "meschain_user_roles
        ")->row['count'];
        
        // Recent RBAC events
        $stats['recent_events'] = $db->query("
            SELECT event, user_id, created_at 
            FROM " . DB_PREFIX . "meschain_rbac_logs 
            ORDER BY created_at DESC 
            LIMIT 10
        ")->rows;
        
        return $stats;
    }
} 