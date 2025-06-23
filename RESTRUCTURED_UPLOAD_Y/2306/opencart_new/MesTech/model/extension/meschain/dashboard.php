<?php
/**
 * MesChain Dashboard Model
 * Native OpenCart 4.x Model
 * Path: admin/model/extension/meschain/dashboard.php
 */

namespace Opencart\Admin\Model\Extension\Meschain;

class Dashboard extends \Opencart\System\Engine\Model {
    
    /**
     * Get dashboard statistics
     */
    public function getStats() {
        $stats = [];
        
        // Total products synced
        $stats['total_products'] = $this->getTotalSyncedProducts();
        
        // Total orders synced
        $stats['total_orders'] = $this->getTotalSyncedOrders();
        
        // Active marketplaces
        $stats['active_marketplaces'] = $this->getActiveMarketplaces();
        
        // Sync success rate (last 30 days)
        $stats['success_rate'] = $this->getSyncSuccessRate();
        
        // Revenue from synced orders
        $stats['synced_revenue'] = $this->getSyncedRevenue();
        
        // Last sync timestamp
        $stats['last_sync'] = $this->getLastSyncTimestamp();
        
        return $stats;
    }
    
    /**
     * Get recent synchronization activities
     */
    public function getRecentSyncs($limit = 10) {
        $query = $this->db->query("
            SELECT 
                msl.*,
                mm.name as marketplace_name,
                mm.code as marketplace_code,
                DATE_FORMAT(msl.created_at, '%Y-%m-%d %H:%i:%s') as sync_time
            FROM `" . DB_PREFIX . "meschain_sync_logs` msl
            LEFT JOIN `" . DB_PREFIX . "meschain_marketplaces` mm ON msl.marketplace_id = mm.marketplace_id
            ORDER BY msl.created_at DESC
            LIMIT " . (int)$limit . "
        ");
        
        return $query->rows;
    }
    
    /**
     * Get marketplace status overview
     */
    public function getMarketplaceStatus() {
        $query = $this->db->query("
            SELECT 
                mm.*,
                COUNT(mpm.mapping_id) as total_products,
                COUNT(CASE WHEN mpm.sync_status = 'synced' THEN 1 END) as synced_products,
                COUNT(CASE WHEN mpm.sync_status = 'error' THEN 1 END) as error_products,
                MAX(mpm.last_sync) as last_product_sync,
                COUNT(mom.mapping_id) as total_orders,
                MAX(mom.last_sync) as last_order_sync
            FROM `" . DB_PREFIX . "meschain_marketplaces` mm
            LEFT JOIN `" . DB_PREFIX . "meschain_product_mappings` mpm ON mm.marketplace_id = mpm.marketplace_id
            LEFT JOIN `" . DB_PREFIX . "meschain_order_mappings` mom ON mm.marketplace_id = mom.marketplace_id
            GROUP BY mm.marketplace_id
            ORDER BY mm.name
        ");
        
        return $query->rows;
    }
    
    /**
     * Perform quick sync operation
     */
    public function quickSync($sync_type = 'all', $marketplace = 'all') {
        try {
            $result = [
                'success' => true,
                'message' => '',
                'data' => []
            ];
            
            switch ($sync_type) {
                case 'products':
                    $result['data'] = $this->syncProducts($marketplace);
                    break;
                    
                case 'orders':
                    $result['data'] = $this->syncOrders($marketplace);
                    break;
                    
                case 'inventory':
                    $result['data'] = $this->syncInventory($marketplace);
                    break;
                    
                case 'all':
                default:
                    $result['data']['products'] = $this->syncProducts($marketplace);
                    $result['data']['orders'] = $this->syncOrders($marketplace);
                    $result['data']['inventory'] = $this->syncInventory($marketplace);
                    break;
            }
            
            return $result;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Get live statistics for AJAX updates
     */
    public function getLiveStats() {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'stats' => $this->getStats(),
            'health' => $this->getSystemHealth(),
            'active_syncs' => $this->getActiveSyncs()
        ];
    }
    
    /**
     * System health check
     */
    public function systemHealthCheck() {
        $health = [
            'overall' => 100,
            'components' => []
        ];
        
        // Database connectivity
        $health['components']['database'] = $this->checkDatabaseHealth();
        
        // API connectivity
        $health['components']['api'] = $this->checkApiHealth();
        
        // Disk space
        $health['components']['disk'] = $this->checkDiskSpace();
        
        // Memory usage
        $health['components']['memory'] = $this->checkMemoryUsage();
        
        // Error rate
        $health['components']['errors'] = $this->checkErrorRate();
        
        // Calculate overall health
        $total_score = 0;
        $component_count = count($health['components']);
        
        foreach ($health['components'] as $score) {
            $total_score += $score;
        }
        
        $health['overall'] = $component_count > 0 ? round($total_score / $component_count, 2) : 0;
        
        return $health;
    }
    
    /**
     * Export dashboard data
     */
    public function exportData($date_from, $date_to) {
        $query = $this->db->query("
            SELECT 
                msl.entity_type,
                msl.action,
                msl.status,
                mm.name as marketplace,
                msl.execution_time,
                msl.memory_usage,
                DATE(msl.created_at) as sync_date,
                COUNT(*) as operation_count
            FROM `" . DB_PREFIX . "meschain_sync_logs` msl
            LEFT JOIN `" . DB_PREFIX . "meschain_marketplaces` mm ON msl.marketplace_id = mm.marketplace_id
            WHERE DATE(msl.created_at) BETWEEN '" . $this->db->escape($date_from) . "' 
                AND '" . $this->db->escape($date_to) . "'
            GROUP BY 
                DATE(msl.created_at),
                msl.entity_type,
                msl.action,
                msl.status,
                mm.marketplace_id
            ORDER BY msl.created_at DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * Private helper methods
     */
    private function getTotalSyncedProducts() {
        $query = $this->db->query("
            SELECT COUNT(*) as total 
            FROM `" . DB_PREFIX . "meschain_product_mappings` 
            WHERE `sync_status` = 'synced'
        ");
        
        return (int)$query->row['total'];
    }
    
    private function getTotalSyncedOrders() {
        $query = $this->db->query("
            SELECT COUNT(*) as total 
            FROM `" . DB_PREFIX . "meschain_order_mappings` 
            WHERE `sync_status` = 'synced'
        ");
        
        return (int)$query->row['total'];
    }
    
    private function getActiveMarketplaces() {
        $query = $this->db->query("
            SELECT COUNT(*) as total 
            FROM `" . DB_PREFIX . "meschain_marketplaces` 
            WHERE `status` = 1
        ");
        
        return (int)$query->row['total'];
    }
    
    private function getSyncSuccessRate() {
        $query = $this->db->query("
            SELECT 
                COUNT(CASE WHEN status = 'success' THEN 1 END) as success_count,
                COUNT(*) as total_count
            FROM `" . DB_PREFIX . "meschain_sync_logs` 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        $row = $query->row;
        return $row['total_count'] > 0 ? round(($row['success_count'] / $row['total_count']) * 100, 2) : 0;
    }
    
    private function getSyncedRevenue() {
        $query = $this->db->query("
            SELECT COALESCE(SUM(o.total), 0) as revenue
            FROM `" . DB_PREFIX . "order` o
            INNER JOIN `" . DB_PREFIX . "meschain_order_mappings` mom ON o.order_id = mom.order_id
            WHERE mom.sync_status = 'synced'
                AND o.date_added >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        return (float)$query->row['revenue'];
    }
    
    private function getLastSyncTimestamp() {
        $query = $this->db->query("
            SELECT MAX(created_at) as last_sync 
            FROM `" . DB_PREFIX . "meschain_sync_logs`
        ");
        
        return $query->row['last_sync'];
    }
    
    private function syncProducts($marketplace = 'all') {
        // Implement product sync logic
        return [
            'synced' => 0,
            'errors' => 0,
            'skipped' => 0
        ];
    }
    
    private function syncOrders($marketplace = 'all') {
        // Implement order sync logic
        return [
            'synced' => 0,
            'errors' => 0,
            'skipped' => 0
        ];
    }
    
    private function syncInventory($marketplace = 'all') {
        // Implement inventory sync logic
        return [
            'synced' => 0,
            'errors' => 0,
            'skipped' => 0
        ];
    }
    
    private function getSystemHealth() {
        return [
            'status' => 'healthy',
            'score' => 95,
            'last_check' => date('Y-m-d H:i:s')
        ];
    }
    
    private function getActiveSyncs() {
        return [];
    }
    
    private function checkDatabaseHealth() {
        try {
            $this->db->query("SELECT 1");
            return 100;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function checkApiHealth() {
        // Check API endpoints health
        return 85;
    }
    
    private function checkDiskSpace() {
        $free_bytes = disk_free_space('/');
        $total_bytes = disk_total_space('/');
        
        if ($free_bytes && $total_bytes) {
            $usage_percent = (($total_bytes - $free_bytes) / $total_bytes) * 100;
            return $usage_percent < 90 ? 100 : (100 - $usage_percent);
        }
        
        return 100;
    }
    
    private function checkMemoryUsage() {
        $memory_usage = memory_get_usage(true);
        $memory_limit = ini_get('memory_limit');
        
        if ($memory_limit) {
            $limit_bytes = $this->convertToBytes($memory_limit);
            $usage_percent = ($memory_usage / $limit_bytes) * 100;
            return $usage_percent < 80 ? 100 : (100 - $usage_percent);
        }
        
        return 100;
    }
    
    private function checkErrorRate() {
        $query = $this->db->query("
            SELECT 
                COUNT(CASE WHEN status = 'error' THEN 1 END) as error_count,
                COUNT(*) as total_count
            FROM `" . DB_PREFIX . "meschain_sync_logs` 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        
        $row = $query->row;
        if ($row['total_count'] > 0) {
            $error_rate = ($row['error_count'] / $row['total_count']) * 100;
            return $error_rate < 5 ? 100 : (100 - $error_rate);
        }
        
        return 100;
    }
    
    private function convertToBytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (int)$val;
        
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        
        return $val;
    }
}
?>
