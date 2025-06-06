<?php
/**
 * Advanced Reporting Model
 * MesChain-Sync OpenCart Extension
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0
 * @author MesChain Development Team
 */

class ModelExtensionModuleAdvancedReporting extends Model {
    
    /**
     * Get marketplace performance report
     *
     * @param array $filters Report filters
     * @return array Report data
     */
    public function getMarketplacePerformanceReport($filters = []) {
        $date_from = $filters['date_from'] ?? date('Y-m-01');
        $date_to = $filters['date_to'] ?? date('Y-m-d');
        $marketplace = $filters['marketplace'] ?? '';
        
        $where_conditions = [
            "DATE(mo.date_added) >= '" . $this->db->escape($date_from) . "'",
            "DATE(mo.date_added) <= '" . $this->db->escape($date_to) . "'"
        ];
        
        if (!empty($marketplace)) {
            $where_conditions[] = "mo.marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = $this->db->query("
            SELECT 
                mo.marketplace,
                COUNT(DISTINCT mo.id) as total_orders,
                COUNT(DISTINCT CASE WHEN mo.sync_status = 'synced' THEN mo.id END) as synced_orders,
                COUNT(DISTINCT CASE WHEN mo.sync_status = 'failed' THEN mo.id END) as failed_orders,
                SUM(mo.total) as total_revenue,
                AVG(mo.total) as avg_order_value,
                COUNT(DISTINCT mp.id) as total_products,
                COUNT(DISTINCT CASE WHEN mp.sync_status = 'synced' THEN mp.id END) as synced_products,
                COUNT(DISTINCT CASE WHEN mp.sync_status = 'failed' THEN mp.id END) as failed_products,
                (COUNT(DISTINCT CASE WHEN mo.sync_status = 'synced' THEN mo.id END) / COUNT(DISTINCT mo.id)) * 100 as sync_success_rate
            FROM " . DB_PREFIX . "meschain_marketplace_orders mo
            LEFT JOIN " . DB_PREFIX . "meschain_marketplace_products mp ON mo.marketplace = mp.marketplace
            WHERE " . $where_clause . "
            GROUP BY mo.marketplace
            ORDER BY total_revenue DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * Get sales trend analysis
     *
     * @param array $filters Report filters
     * @return array Trend data
     */
    public function getSalesTrendAnalysis($filters = []) {
        $period = $filters['period'] ?? '30d';
        $marketplace = $filters['marketplace'] ?? '';
        
        // Determine date range and grouping
        switch ($period) {
            case '7d':
                $date_condition = "DATE(date_added) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                $group_by = "DATE(date_added)";
                $date_format = "%Y-%m-%d";
                break;
            case '30d':
                $date_condition = "DATE(date_added) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                $group_by = "DATE(date_added)";
                $date_format = "%Y-%m-%d";
                break;
            case '12m':
                $date_condition = "DATE(date_added) >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
                $group_by = "DATE_FORMAT(date_added, '%Y-%m')";
                $date_format = "%Y-%m";
                break;
            default:
                $date_condition = "DATE(date_added) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                $group_by = "DATE(date_added)";
                $date_format = "%Y-%m-%d";
        }
        
        $where_conditions = [$date_condition];
        
        if (!empty($marketplace)) {
            $where_conditions[] = "marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = $this->db->query("
            SELECT 
                DATE_FORMAT(date_added, '" . $date_format . "') as period,
                marketplace,
                COUNT(*) as order_count,
                SUM(total) as revenue,
                AVG(total) as avg_order_value,
                COUNT(CASE WHEN sync_status = 'synced' THEN 1 END) as successful_orders,
                COUNT(CASE WHEN sync_status = 'failed' THEN 1 END) as failed_orders
            FROM " . DB_PREFIX . "meschain_marketplace_orders 
            WHERE " . $where_clause . "
            GROUP BY " . $group_by . ", marketplace
            ORDER BY period, marketplace
        ");
        
        return $query->rows;
    }
    
    /**
     * Get dashboard summary statistics
     *
     * @return array Dashboard stats
     */
    public function getDashboardSummary() {
        $stats = [];
        
        // Today's statistics
        $today = date('Y-m-d');
        
        $today_stats = $this->db->query("
            SELECT 
                COUNT(*) as today_orders,
                SUM(total) as today_revenue,
                COUNT(DISTINCT marketplace) as active_marketplaces
            FROM " . DB_PREFIX . "meschain_marketplace_orders 
            WHERE DATE(date_added) = '" . $today . "'
            AND sync_status = 'synced'
        ");
        
        $stats['today'] = $today_stats->row;
        
        // This month's statistics
        $month_start = date('Y-m-01');
        
        $month_stats = $this->db->query("
            SELECT 
                COUNT(*) as month_orders,
                SUM(total) as month_revenue,
                AVG(total) as avg_order_value
            FROM " . DB_PREFIX . "meschain_marketplace_orders 
            WHERE DATE(date_added) >= '" . $month_start . "'
            AND sync_status = 'synced'
        ");
        
        $stats['month'] = $month_stats->row;
        
        // Sync status overview
        $sync_stats = $this->db->query("
            SELECT 
                sync_status,
                COUNT(*) as count
            FROM " . DB_PREFIX . "meschain_marketplace_orders 
            WHERE DATE(date_added) >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY sync_status
        ");
        
        $stats['sync_status'] = [];
        foreach ($sync_stats->rows as $row) {
            $stats['sync_status'][$row['sync_status']] = $row['count'];
        }
        
        // Top performing marketplaces
        $top_marketplaces = $this->db->query("
            SELECT 
                marketplace,
                COUNT(*) as order_count,
                SUM(total) as revenue
            FROM " . DB_PREFIX . "meschain_marketplace_orders 
            WHERE DATE(date_added) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND sync_status = 'synced'
            GROUP BY marketplace
            ORDER BY revenue DESC
            LIMIT 5
        ");
        
        $stats['top_marketplaces'] = $top_marketplaces->rows;
        
        return $stats;
    }
    
    /**
     * Export report to CSV
     *
     * @param string $report_type Report type
     * @param array $data Report data
     * @param string $filename Filename
     * @return string File path
     */
    public function exportToCSV($report_type, $data, $filename = null) {
        if (!$filename) {
            $filename = $report_type . '_report_' . date('Y-m-d_H-i-s') . '.csv';
        }
        
        $filepath = DIR_DOWNLOAD . $filename;
        
        $file = fopen($filepath, 'w');
        
        if (!empty($data)) {
            // Write headers
            fputcsv($file, array_keys($data[0]));
            
            // Write data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
        }
        
        fclose($file);
        
        return $filepath;
    }
}