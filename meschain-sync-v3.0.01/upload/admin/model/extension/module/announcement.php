<?php
/**
 * Announcement Model
 * 
 * Duyuru ve bildirim yönetimi modeli
 * 
 * @category   Model
 * @package    MesChain-Sync
 * @version    2.5.0
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

class ModelExtensionModuleAnnouncement extends Model {
    
    /**
     * Install module tables
     */
    public function install() {
        // Create announcements table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_announcements` (
                `announcement_id` int(11) NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `message` text NOT NULL,
                `type` enum('info','success','warning','error') DEFAULT 'info',
                `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
                `target_users` text,
                `target_roles` text,
                `marketplace` varchar(50) DEFAULT NULL,
                `start_date` datetime DEFAULT NULL,
                `end_date` datetime DEFAULT NULL,
                `is_active` tinyint(1) DEFAULT '1',
                `is_sticky` tinyint(1) DEFAULT '0',
                `created_by` int(11) NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`announcement_id`),
                KEY `type` (`type`),
                KEY `priority` (`priority`),
                KEY `marketplace` (`marketplace`),
                KEY `is_active` (`is_active`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Create announcement views table (read tracking)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_announcement_views` (
                `view_id` int(11) NOT NULL AUTO_INCREMENT,
                `announcement_id` int(11) NOT NULL,
                `user_id` int(11) NOT NULL,
                `viewed_at` datetime NOT NULL,
                `ip_address` varchar(45) DEFAULT NULL,
                PRIMARY KEY (`view_id`),
                UNIQUE KEY `announcement_user` (`announcement_id`, `user_id`),
                KEY `user_id` (`user_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Create system notifications table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_notifications` (
                `notification_id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `title` varchar(255) NOT NULL,
                `message` text NOT NULL,
                `type` enum('info','success','warning','error') DEFAULT 'info',
                `action_url` varchar(500) DEFAULT NULL,
                `is_read` tinyint(1) DEFAULT '0',
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`notification_id`),
                KEY `user_id` (`user_id`),
                KEY `is_read` (`is_read`),
                KEY `type` (`type`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        $this->writeLog('INSTALL', 'Announcement tables created successfully');
    }
    
    /**
     * Uninstall module
     */
    public function uninstall() {
        // Don't drop tables to preserve data
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE code LIKE 'module_announcement%'");
        $this->writeLog('UNINSTALL', 'Announcement module settings removed');
    }
    
    /**
     * Get announcements
     */
    public function getAnnouncements($filter = array()) {
        $sql = "SELECT a.*, u.username as created_by_name
                FROM " . DB_PREFIX . "meschain_announcements a
                LEFT JOIN " . DB_PREFIX . "user u ON a.created_by = u.user_id
                WHERE 1=1";
        
        if (isset($filter['is_active'])) {
            $sql .= " AND a.is_active = '" . (int)$filter['is_active'] . "'";
        }
        
        if (isset($filter['type'])) {
            $sql .= " AND a.type = '" . $this->db->escape($filter['type']) . "'";
        }
        
        if (isset($filter['marketplace'])) {
            $sql .= " AND (a.marketplace = '" . $this->db->escape($filter['marketplace']) . "' OR a.marketplace IS NULL)";
        }
        
        if (isset($filter['priority'])) {
            $sql .= " AND a.priority = '" . $this->db->escape($filter['priority']) . "'";
        }
        
        // Current date filter for active announcements
        if (isset($filter['current_date']) && $filter['current_date']) {
            $sql .= " AND (a.start_date IS NULL OR a.start_date <= NOW())";
            $sql .= " AND (a.end_date IS NULL OR a.end_date >= NOW())";
        }
        
        $sql .= " ORDER BY a.is_sticky DESC, a.priority DESC, a.created_at DESC";
        
        if (isset($filter['start']) && isset($filter['limit'])) {
            $sql .= " LIMIT " . (int)$filter['start'] . ", " . (int)$filter['limit'];
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Get announcement by ID
     */
    public function getAnnouncement($announcement_id) {
        $query = $this->db->query("
            SELECT a.*, u.username as created_by_name
            FROM " . DB_PREFIX . "meschain_announcements a
            LEFT JOIN " . DB_PREFIX . "user u ON a.created_by = u.user_id
            WHERE a.announcement_id = '" . (int)$announcement_id . "'
        ");
        
        return $query->row;
    }
    
    /**
     * Add announcement
     */
    public function addAnnouncement($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "meschain_announcements SET
            title = '" . $this->db->escape($data['title']) . "',
            message = '" . $this->db->escape($data['message']) . "',
            type = '" . $this->db->escape($data['type'] ?? 'info') . "',
            priority = '" . $this->db->escape($data['priority'] ?? 'normal') . "',
            target_users = " . (isset($data['target_users']) ? "'" . $this->db->escape(json_encode($data['target_users'])) . "'" : "NULL") . ",
            target_roles = " . (isset($data['target_roles']) ? "'" . $this->db->escape(json_encode($data['target_roles'])) . "'" : "NULL") . ",
            marketplace = " . (isset($data['marketplace']) ? "'" . $this->db->escape($data['marketplace']) . "'" : "NULL") . ",
            start_date = " . (isset($data['start_date']) ? "'" . $this->db->escape($data['start_date']) . "'" : "NULL") . ",
            end_date = " . (isset($data['end_date']) ? "'" . $this->db->escape($data['end_date']) . "'" : "NULL") . ",
            is_active = '" . (int)($data['is_active'] ?? 1) . "',
            is_sticky = '" . (int)($data['is_sticky'] ?? 0) . "',
            created_by = '" . (int)($data['created_by'] ?? $this->user->getId()) . "',
            created_at = NOW(),
            updated_at = NOW()");
        
        $announcement_id = $this->db->getLastId();
        $this->writeLog('ADD_ANNOUNCEMENT', 'Announcement added: ' . $data['title']);
        
        // Create notifications for target users
        $this->createNotifications($announcement_id, $data);
        
        return $announcement_id;
    }
    
    /**
     * Update announcement
     */
    public function updateAnnouncement($announcement_id, $data) {
        $sql = "UPDATE " . DB_PREFIX . "meschain_announcements SET ";
        $updates = array();
        
        $allowed_fields = array('title', 'message', 'type', 'priority', 'marketplace', 'start_date', 'end_date');
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $updates[] = $field . " = '" . $this->db->escape($data[$field]) . "'";
            }
        }
        
        if (isset($data['target_users'])) {
            $updates[] = "target_users = '" . $this->db->escape(json_encode($data['target_users'])) . "'";
        }
        
        if (isset($data['target_roles'])) {
            $updates[] = "target_roles = '" . $this->db->escape(json_encode($data['target_roles'])) . "'";
        }
        
        if (isset($data['is_active'])) {
            $updates[] = "is_active = '" . (int)$data['is_active'] . "'";
        }
        
        if (isset($data['is_sticky'])) {
            $updates[] = "is_sticky = '" . (int)$data['is_sticky'] . "'";
        }
        
        if (!empty($updates)) {
            $updates[] = "updated_at = NOW()";
            $sql .= implode(', ', $updates) . " WHERE announcement_id = '" . (int)$announcement_id . "'";
            $this->db->query($sql);
            
            $this->writeLog('UPDATE_ANNOUNCEMENT', 'Announcement updated: ' . $announcement_id);
            return $this->db->countAffected() > 0;
        }
        
        return false;
    }
    
    /**
     * Delete announcement
     */
    public function deleteAnnouncement($announcement_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "meschain_announcements WHERE announcement_id = '" . (int)$announcement_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "meschain_announcement_views WHERE announcement_id = '" . (int)$announcement_id . "'");
        
        $this->writeLog('DELETE_ANNOUNCEMENT', 'Announcement deleted: ' . $announcement_id);
        return $this->db->countAffected() > 0;
    }
    
    /**
     * Mark announcement as viewed
     */
    public function markAsViewed($announcement_id, $user_id) {
        $ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        
        $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "meschain_announcement_views SET
            announcement_id = '" . (int)$announcement_id . "',
            user_id = '" . (int)$user_id . "',
            viewed_at = NOW(),
            ip_address = '" . $this->db->escape($ip_address) . "'");
    }
    
    /**
     * Get active announcements for user (used in header popup)
     */
    public function getActiveAnnouncementsForUser($user_id, $user_group_id) {
        try {
            // Get active announcements that the user hasn't viewed yet
            $query = $this->db->query("
                SELECT a.* 
                FROM " . DB_PREFIX . "meschain_announcements a
                LEFT JOIN " . DB_PREFIX . "meschain_announcement_views v 
                    ON a.announcement_id = v.announcement_id AND v.user_id = '" . (int)$user_id . "'
                WHERE a.is_active = 1
                AND v.view_id IS NULL
                AND (a.start_date IS NULL OR a.start_date <= NOW())
                AND (a.end_date IS NULL OR a.end_date >= NOW())
                AND (
                    a.target_users IS NULL 
                    OR a.target_users = ''
                    OR FIND_IN_SET('" . (int)$user_id . "', REPLACE(REPLACE(REPLACE(a.target_users, '[', ''), ']', ''), '\"', ''))
                )
                ORDER BY a.priority DESC, a.created_at DESC
                LIMIT 1
            ");
            
            if ($query->num_rows) {
                return $query->row;
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->writeLog('GET_ACTIVE_ANNOUNCEMENTS', 'Error getting announcements for user: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications($user_id, $limit = 20) {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_notifications
            WHERE user_id = '" . (int)$user_id . "'
            ORDER BY created_at DESC
            LIMIT " . (int)$limit
        );
        
        return $query->rows;
    }
    
    /**
     * Create notifications from announcement
     */
    private function createNotifications($announcement_id, $data) {
        $announcement = $this->getAnnouncement($announcement_id);
        
        // Get target users
        $target_users = array();
        
        if (isset($data['target_users']) && !empty($data['target_users'])) {
            $target_users = array_merge($target_users, $data['target_users']);
        }
        
        if (isset($data['target_roles']) && !empty($data['target_roles'])) {
            // Get users with specified roles
            foreach ($data['target_roles'] as $role) {
                $role_users = $this->getUsersByRole($role);
                $target_users = array_merge($target_users, $role_users);
            }
        }
        
        // If no specific targets, notify all active users
        if (empty($target_users)) {
            $query = $this->db->query("SELECT user_id FROM " . DB_PREFIX . "user WHERE status = 1");
            foreach ($query->rows as $row) {
                $target_users[] = $row['user_id'];
            }
        }
        
        // Create notifications
        $target_users = array_unique($target_users);
        foreach ($target_users as $user_id) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "meschain_notifications SET
                user_id = '" . (int)$user_id . "',
                title = '" . $this->db->escape($announcement['title']) . "',
                message = '" . $this->db->escape(substr($announcement['message'], 0, 500)) . "',
                type = '" . $this->db->escape($announcement['type']) . "',
                action_url = 'index.php?route=extension/module/announcement&announcement_id=" . $announcement_id . "',
                created_at = NOW()");
        }
    }
    
    /**
     * Get users by role
     */
    private function getUsersByRole($role) {
        $query = $this->db->query("
            SELECT DISTINCT ma.user_id 
            FROM " . DB_PREFIX . "meschain_user_assignments ma
            JOIN " . DB_PREFIX . "meschain_user_roles mr ON ma.role_id = mr.role_id
            WHERE mr.role_name = '" . $this->db->escape($role) . "'
            AND ma.is_active = 1
            AND mr.is_active = 1
        ");
        
        $users = array();
        foreach ($query->rows as $row) {
            $users[] = $row['user_id'];
        }
        
        return $users;
    }
    
    /**
     * Dashboard statistics
     */
    public function getDashboardStats() {
        $stats = array();
        
        // Total active announcements
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_announcements WHERE is_active = 1");
        $stats['total_products'] = $query->row['total'];
        
        // Total notifications sent
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_notifications");
        $stats['total_orders'] = $query->row['total'];
        
        // Urgent announcements
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_announcements WHERE priority = 'urgent' AND is_active = 1");
        $stats['total_sync'] = $query->row['total'];
        
        // Last announcement
        $query = $this->db->query("SELECT MAX(created_at) as last_announcement FROM " . DB_PREFIX . "meschain_announcements");
        $stats['last_sync'] = $query->row['last_announcement'] ? date('d.m.Y H:i', strtotime($query->row['last_announcement'])) : 'Hiçbir zaman';
        
        // System status
        $stats['status'] = 'connected';
        
        // Recent activity
        $query = $this->db->query("SELECT title FROM " . DB_PREFIX . "meschain_announcements ORDER BY created_at DESC LIMIT 1");
        if ($query->num_rows) {
            $stats['recent_activity'] = 'Son duyuru: ' . substr($query->row['title'], 0, 30) . '...';
        } else {
            $stats['recent_activity'] = 'Henüz duyuru yok';
        }
        
        return $stats;
    }
    
    /**
     * Write log
     */
    private function writeLog($action, $message) {
        $log_file = DIR_LOGS . 'announcement_model.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [MODEL] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 