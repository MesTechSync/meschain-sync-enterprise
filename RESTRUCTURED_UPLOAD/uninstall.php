<?php
/**
 * MesChain Sync Enterprise - Native OpenCart 4.x Uninstaller
 * Version: 2.0.0
 * 
 * Clean uninstallation without OCMOD dependency
 */

class MeschainUninstaller {
    private $registry;
    private $db;
    private $config;
    private $cache;
    private $log;
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->cache = $registry->get('cache');
        $this->log = $registry->get('log');
    }
    
    /**
     * Main uninstallation process
     */
    public function uninstall() {
        try {
            $this->log->write('MesChain Uninstallation Started');
            
            // Disable all sync processes
            $this->disableSyncProcesses();
            
            // Remove events
            $this->removeEvents();
            
            // Clean permissions
            $this->cleanPermissions();
            
            // Remove settings
            $this->removeSettings();
            
            // Remove extension entries
            $this->removeExtensionEntries();
            
            // Archive data (optional - don't delete immediately)
            $this->archiveData();
            
            // Clear caches
            $this->clearCaches();
            
            $this->log->write('MesChain Uninstallation Completed');
            
            return [
                'success' => true, 
                'message' => 'MesChain Sync Enterprise uninstalled successfully'
            ];
            
        } catch (Exception $e) {
            $this->log->write('MesChain Uninstallation Error: ' . $e->getMessage());
            
            return [
                'success' => false, 
                'message' => 'Uninstallation failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Disable all sync processes
     */
    private function disableSyncProcesses() {
        // Disable all marketplaces
        $this->db->query("
            UPDATE `" . DB_PREFIX . "meschain_marketplaces` 
            SET `status` = 0 
            WHERE `status` = 1
        ");
        
        // Set all sync statuses to disabled
        $this->db->query("
            UPDATE `" . DB_PREFIX . "meschain_product_mappings` 
            SET `sync_status` = 'disabled' 
            WHERE `sync_status` != 'disabled'
        ");
    }
    
    /**
     * Remove registered events
     */
    private function removeEvents() {
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "event` 
            WHERE `code` LIKE 'meschain_%'
        ");
    }
    
    /**
     * Clean permissions from user groups
     */
    private function cleanPermissions() {
        $meschain_permissions = [
            'extension/meschain/dashboard',
            'extension/meschain/trendyol',
            'extension/meschain/products',
            'extension/meschain/orders',
            'extension/meschain/settings',
            'extension/meschain/analytics',
            'extension/meschain/logs'
        ];
        
        $user_groups = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user_group`")->rows;
        
        foreach ($user_groups as $user_group) {
            $permissions = json_decode($user_group['permission'], true) ?: ['access' => [], 'modify' => []];
            
            // Remove MesChain permissions
            foreach ($meschain_permissions as $permission) {
                $permissions['access'] = array_diff($permissions['access'], [$permission]);
                $permissions['modify'] = array_diff($permissions['modify'], [$permission]);
            }
            
            $this->db->query("
                UPDATE `" . DB_PREFIX . "user_group` 
                SET `permission` = '" . $this->db->escape(json_encode($permissions)) . "'
                WHERE `user_group_id` = '" . (int)$user_group['user_group_id'] . "'
            ");
        }
    }
    
    /**
     * Remove settings
     */
    private function removeSettings() {
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "setting` 
            WHERE `code` IN ('meschain', 'module_meschain')
        ");
    }
    
    /**
     * Remove extension entries
     */
    private function removeExtensionEntries() {
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "extension` 
            WHERE `code` = 'meschain'
        ");
    }
    
    /**
     * Archive data instead of deleting (safer approach)
     */
    private function archiveData() {
        $timestamp = date('Y_m_d_H_i_s');
        
        // Create archive tables
        $archive_tables = [
            'meschain_registry' => 'meschain_registry_archive_' . $timestamp,
            'meschain_marketplaces' => 'meschain_marketplaces_archive_' . $timestamp,
            'meschain_product_mappings' => 'meschain_product_mappings_archive_' . $timestamp,
            'meschain_order_mappings' => 'meschain_order_mappings_archive_' . $timestamp,
            'meschain_sync_logs' => 'meschain_sync_logs_archive_' . $timestamp
        ];
        
        foreach ($archive_tables as $original => $archive) {
            $this->db->query("
                CREATE TABLE `" . DB_PREFIX . $archive . "` 
                AS SELECT * FROM `" . DB_PREFIX . $original . "`
            ");
        }
        
        // Add uninstall timestamp to registry archive
        $this->db->query("
            UPDATE `" . DB_PREFIX . "meschain_registry_archive_" . $timestamp . "` 
            SET `status` = 0
        ");
    }
    
    /**
     * Clear caches
     */
    private function clearCaches() {
        $cache_types = [
            'cache.currency.*',
            'cache.language.*',
            'cache.product.*',
            'cache.category.*',
            'cache.setting.*'
        ];
        
        foreach ($cache_types as $cache_type) {
            $this->cache->delete($cache_type);
        }
    }
    
    /**
     * Complete data removal (use with caution)
     */
    public function completeRemoval() {
        try {
            // Drop all MesChain tables
            $tables = [
                DB_PREFIX . 'meschain_sync_logs',
                DB_PREFIX . 'meschain_order_mappings',
                DB_PREFIX . 'meschain_product_mappings',
                DB_PREFIX . 'meschain_marketplaces',
                DB_PREFIX . 'meschain_registry'
            ];
            
            foreach ($tables as $table) {
                $this->db->query("DROP TABLE IF EXISTS `" . $table . "`");
            }
            
            // Remove archive tables
            $archive_tables = $this->db->query("
                SHOW TABLES LIKE '" . DB_PREFIX . "meschain_%_archive_%'
            ")->rows;
            
            foreach ($archive_tables as $table) {
                $table_name = array_values($table)[0];
                $this->db->query("DROP TABLE IF EXISTS `" . $table_name . "`");
            }
            
            return ['success' => true, 'message' => 'Complete removal successful'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Complete removal failed: ' . $e->getMessage()];
        }
    }
}

// Main uninstallation execution
try {
    $uninstaller = new MeschainUninstaller($registry);
    
    // Check for complete removal parameter
    $complete_removal = isset($_GET['complete']) && $_GET['complete'] === 'true';
    
    if ($complete_removal) {
        $result = $uninstaller->completeRemoval();
    } else {
        $result = $uninstaller->uninstall();
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Uninstallation failed: ' . $e->getMessage()
    ]);
}
?>
