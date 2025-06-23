<?php
/**
 * User Management Model
 * 
 * Kullanıcı yönetimi ve rol bazlı erişim kontrol modeli
 * 
 * @category   Model
 * @package    MesChain-Sync
 * @version    2.5.0
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

class ModelExtensionModuleUserManagement extends Model {
    
    /**
     * Install module tables
     */
    public function install() {
        // Create user roles table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_user_roles` (
                `role_id` int(11) NOT NULL AUTO_INCREMENT,
                `role_name` varchar(100) NOT NULL,
                `role_description` text,
                `permissions` text,
                `marketplace_access` text,
                `is_active` tinyint(1) DEFAULT '1',
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`role_id`),
                UNIQUE KEY `role_name` (`role_name`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Create user assignments table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_user_assignments` (
                `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `role_id` int(11) NOT NULL,
                `marketplace` varchar(50) DEFAULT NULL,
                `assigned_by` int(11) NOT NULL,
                `assigned_at` datetime NOT NULL,
                `is_active` tinyint(1) DEFAULT '1',
                PRIMARY KEY (`assignment_id`),
                KEY `user_id` (`user_id`),
                KEY `role_id` (`role_id`),
                KEY `marketplace` (`marketplace`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Create user activity log table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_user_activity` (
                `activity_id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `marketplace` varchar(50) DEFAULT NULL,
                `action` varchar(100) NOT NULL,
                `description` text,
                `ip_address` varchar(45) DEFAULT NULL,
                `user_agent` text,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`activity_id`),
                KEY `user_id` (`user_id`),
                KEY `marketplace` (`marketplace`),
                KEY `action` (`action`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Insert default roles
        $this->insertDefaultRoles();
        
        $this->writeLog('INSTALL', 'User Management tables created successfully');
    }
    
    /**
     * Uninstall module
     */
    public function uninstall() {
        // Don't drop tables to preserve data
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE code LIKE 'module_user_management%'");
        $this->writeLog('UNINSTALL', 'User Management module settings removed');
    }
    
    /**
     * Insert default roles
     */
    private function insertDefaultRoles() {
        $roles = array(
            array(
                'role_name' => 'super_admin',
                'role_description' => 'Süper Yönetici - Tüm yetkilere sahip',
                'permissions' => json_encode(array('all')),
                'marketplace_access' => json_encode(array('all'))
            ),
            array(
                'role_name' => 'marketplace_manager',
                'role_description' => 'Marketplace Yöneticisi - Atanan pazaryerleri için tam yetki',
                'permissions' => json_encode(array('view', 'edit', 'sync', 'orders', 'products')),
                'marketplace_access' => json_encode(array())
            ),
            array(
                'role_name' => 'product_manager',
                'role_description' => 'Ürün Yöneticisi - Sadece ürün yönetimi',
                'permissions' => json_encode(array('view', 'edit', 'products')),
                'marketplace_access' => json_encode(array())
            ),
            array(
                'role_name' => 'order_manager',
                'role_description' => 'Sipariş Yöneticisi - Sadece sipariş yönetimi',
                'permissions' => json_encode(array('view', 'orders')),
                'marketplace_access' => json_encode(array())
            ),
            array(
                'role_name' => 'viewer',
                'role_description' => 'Görüntüleyici - Sadece görüntüleme yetkisi',
                'permissions' => json_encode(array('view')),
                'marketplace_access' => json_encode(array())
            )
        );
        
        foreach ($roles as $role) {
            $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "meschain_user_roles SET
                role_name = '" . $this->db->escape($role['role_name']) . "',
                role_description = '" . $this->db->escape($role['role_description']) . "',
                permissions = '" . $this->db->escape($role['permissions']) . "',
                marketplace_access = '" . $this->db->escape($role['marketplace_access']) . "',
                created_at = NOW(),
                updated_at = NOW()");
        }
    }
    
    /**
     * Get all users with their assignments
     */
    public function getUsers($filter = array()) {
        $sql = "SELECT u.user_id, u.username, u.firstname, u.lastname, u.email, u.status,
                       GROUP_CONCAT(DISTINCT mr.role_name) as roles,
                       GROUP_CONCAT(DISTINCT ma.marketplace) as marketplaces,
                       MAX(ma.assigned_at) as last_assignment
                FROM " . DB_PREFIX . "user u
                LEFT JOIN " . DB_PREFIX . "meschain_user_assignments ma ON u.user_id = ma.user_id AND ma.is_active = 1
                LEFT JOIN " . DB_PREFIX . "meschain_user_roles mr ON ma.role_id = mr.role_id
                WHERE 1=1";
        
        if (isset($filter['username'])) {
            $sql .= " AND u.username LIKE '%" . $this->db->escape($filter['username']) . "%'";
        }
        
        if (isset($filter['status'])) {
            $sql .= " AND u.status = '" . (int)$filter['status'] . "'";
        }
        
        $sql .= " GROUP BY u.user_id ORDER BY u.username";
        
        if (isset($filter['start']) && isset($filter['limit'])) {
            $sql .= " LIMIT " . (int)$filter['start'] . ", " . (int)$filter['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Get user roles
     */
    public function getRoles() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "meschain_user_roles WHERE is_active = 1 ORDER BY role_name");
        return $query->rows;
    }
    
    /**
     * Assign role to user
     */
    public function assignRole($user_id, $role_id, $marketplace = null, $assigned_by = null) {
        $assigned_by = $assigned_by ?: $this->user->getId();
        
        $this->db->query("INSERT INTO " . DB_PREFIX . "meschain_user_assignments SET
            user_id = '" . (int)$user_id . "',
            role_id = '" . (int)$role_id . "',
            marketplace = " . ($marketplace ? "'" . $this->db->escape($marketplace) . "'" : "NULL") . ",
            assigned_by = '" . (int)$assigned_by . "',
            assigned_at = NOW()");
        
        $this->logActivity($user_id, $marketplace, 'role_assigned', 'Role ID: ' . $role_id);
        $this->writeLog('ASSIGN_ROLE', "User $user_id assigned role $role_id for marketplace $marketplace");
        
        return $this->db->getLastId();
    }
    
    /**
     * Remove role from user
     */
    public function removeRole($assignment_id) {
        $this->db->query("UPDATE " . DB_PREFIX . "meschain_user_assignments SET 
            is_active = 0 
            WHERE assignment_id = '" . (int)$assignment_id . "'");
        
        $this->writeLog('REMOVE_ROLE', "Assignment $assignment_id deactivated");
        return $this->db->countAffected() > 0;
    }
    
    /**
     * Check user permission
     */
    public function checkPermission($user_id, $marketplace, $action) {
        $query = $this->db->query("
            SELECT mr.permissions 
            FROM " . DB_PREFIX . "meschain_user_assignments ma
            JOIN " . DB_PREFIX . "meschain_user_roles mr ON ma.role_id = mr.role_id
            WHERE ma.user_id = '" . (int)$user_id . "'
            AND ma.is_active = 1
            AND mr.is_active = 1
            AND (ma.marketplace = '" . $this->db->escape($marketplace) . "' OR ma.marketplace IS NULL)
        ");
        
        foreach ($query->rows as $row) {
            $permissions = json_decode($row['permissions'], true);
            if (in_array('all', $permissions) || in_array($action, $permissions)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get user activities
     */
    public function getUserActivities($user_id, $limit = 50) {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_user_activity 
            WHERE user_id = '" . (int)$user_id . "'
            ORDER BY created_at DESC 
            LIMIT " . (int)$limit
        );
        
        return $query->rows;
    }
    
    /**
     * Log user activity
     */
    public function logActivity($user_id, $marketplace, $action, $description = null) {
        $ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        
        $this->db->query("INSERT INTO " . DB_PREFIX . "meschain_user_activity SET
            user_id = '" . (int)$user_id . "',
            marketplace = " . ($marketplace ? "'" . $this->db->escape($marketplace) . "'" : "NULL") . ",
            action = '" . $this->db->escape($action) . "',
            description = " . ($description ? "'" . $this->db->escape($description) . "'" : "NULL") . ",
            ip_address = '" . $this->db->escape($ip_address) . "',
            user_agent = '" . $this->db->escape($user_agent) . "',
            created_at = NOW()");
    }
    
    /**
     * Dashboard statistics
     */
    public function getDashboardStats() {
        $stats = array();
        
        // Total users
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "user WHERE status = 1");
        $stats['total_products'] = $query->row['total'];
        
        // Total role assignments
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_user_assignments WHERE is_active = 1");
        $stats['total_orders'] = $query->row['total'];
        
        // Active roles
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_user_roles WHERE is_active = 1");
        $stats['total_sync'] = $query->row['total'];
        
        // Last activity
        $query = $this->db->query("SELECT MAX(created_at) as last_activity FROM " . DB_PREFIX . "meschain_user_activity");
        $stats['last_sync'] = $query->row['last_activity'] ? date('d.m.Y H:i', strtotime($query->row['last_activity'])) : 'Hiçbir zaman';
        
        // System status
        $stats['status'] = 'connected';
        
        // Recent activity
        $query = $this->db->query("SELECT action FROM " . DB_PREFIX . "meschain_user_activity ORDER BY created_at DESC LIMIT 1");
        if ($query->num_rows) {
            $stats['recent_activity'] = 'Son işlem: ' . $query->row['action'];
        } else {
            $stats['recent_activity'] = 'Henüz aktivite yok';
        }
        
        return $stats;
    }
    
    /**
     * Write log
     */
    private function writeLog($action, $message) {
        $log_file = DIR_LOGS . 'user_management_model.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [MODEL] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 