<?php
/**
 * MesChain Enterprise Reporting Dashboard Model
 * ATOM-M010: Advanced Enterprise Features - Model
 * 
 * @category    MesChain
 * @package     Admin
 * @subpackage  Model
 * @version     1.0.0
 * @author      Musti DevOps Team
 * @copyright   2024 MesChain Sync Enterprise
 */

class ModelExtensionModuleEnterpriseReportingDashboard extends Model {
    
    /**
     * Edit Settings
     */
    public function editSettings($data) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '0' AND `code` = 'module_enterprise_reporting_dashboard'");
        
        foreach ($data as $key => $value) {
            if (substr($key, 0, 32) == 'module_enterprise_reporting_dashboard') {
                $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `code` = 'module_enterprise_reporting_dashboard', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
            }
        }
    }
    
    /**
     * Get Total Reports Count
     */
    public function getTotalReports() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_advanced_reports WHERE status = 'active'");
        return $query->row['total'];
    }
    
    /**
     * Get Active Reports
     */
    public function getActiveReports() {
        $query = $this->db->query("
            SELECT r.*, COUNT(e.execution_id) as execution_count 
            FROM " . DB_PREFIX . "meschain_advanced_reports r
            LEFT JOIN " . DB_PREFIX . "meschain_report_executions e ON r.report_id = e.report_id
            WHERE r.status = 'active' 
            GROUP BY r.report_id 
            ORDER BY r.created_at DESC
        ");
        return $query->rows;
    }
    
    /**
     * Get Reports Generated Today
     */
    public function getReportsToday() {
        $query = $this->db->query("
            SELECT COUNT(*) as total 
            FROM " . DB_PREFIX . "meschain_report_executions 
            WHERE DATE(start_time) = CURDATE() AND status = 'completed'
        ");
        return $query->row['total'];
    }
    
    /**
     * Get Performance Metrics
     */
    public function getPerformanceMetrics() {
        $metrics = [];
        
        // Average generation time
        $query = $this->db->query("
            SELECT AVG(execution_duration) as avg_time 
            FROM " . DB_PREFIX . "meschain_report_executions 
            WHERE status = 'completed' AND execution_duration > 0
        ");
        $metrics['avg_generation_time'] = round($query->row['avg_time'], 2);
        
        // Success rate
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful
            FROM " . DB_PREFIX . "meschain_report_executions 
            WHERE start_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $metrics['success_rate'] = $query->row['total'] > 0 ? 
            round(($query->row['successful'] / $query->row['total']) * 100, 2) : 0;
        
        // Cache hit ratio
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN cache_usage = 'hit' THEN 1 ELSE 0 END) as cache_hits
            FROM " . DB_PREFIX . "meschain_report_executions 
            WHERE start_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $metrics['cache_hit_ratio'] = $query->row['total'] > 0 ? 
            round(($query->row['cache_hits'] / $query->row['total']) * 100, 2) : 0;
        
        return $metrics;
    }
    
    /**
     * Get Recent Reports
     */
    public function getRecentReports($limit = 10) {
        $query = $this->db->query("
            SELECT r.report_name, r.report_type, e.start_time, e.status, e.execution_duration
            FROM " . DB_PREFIX . "meschain_advanced_reports r
            JOIN " . DB_PREFIX . "meschain_report_executions e ON r.report_id = e.report_id
            WHERE e.status = 'completed'
            ORDER BY e.start_time DESC 
            LIMIT " . (int)$limit
        );
        return $query->rows;
    }
    
    /**
     * Get Top Reports by Usage
     */
    public function getTopReports($limit = 5) {
        $query = $this->db->query("
            SELECT r.report_name, r.report_type, COUNT(e.execution_id) as usage_count
            FROM " . DB_PREFIX . "meschain_advanced_reports r
            JOIN " . DB_PREFIX . "meschain_report_executions e ON r.report_id = e.report_id
            WHERE r.status = 'active'
            GROUP BY r.report_id
            ORDER BY usage_count DESC
            LIMIT " . (int)$limit
        );
        return $query->rows;
    }
    
    /**
     * Get Reports by Type
     */
    public function getReportsByType($type) {
        $query = $this->db->query("
            SELECT r.*, COUNT(e.execution_id) as execution_count,
                   MAX(e.start_time) as last_execution
            FROM " . DB_PREFIX . "meschain_advanced_reports r
            LEFT JOIN " . DB_PREFIX . "meschain_report_executions e ON r.report_id = e.report_id
            WHERE r.report_type = '" . $this->db->escape($type) . "' AND r.status = 'active'
            GROUP BY r.report_id
            ORDER BY r.created_at DESC
        ");
        return $query->rows;
    }
    
    /**
     * Get Report Data
     */
    public function getReportData($report_id) {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_advanced_reports 
            WHERE report_id = '" . (int)$report_id . "'
        ");
        
        if (!$query->num_rows) {
            return false;
        }
        
        $report = $query->row;
        
        // Get latest execution data
        $execution_query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_report_executions 
            WHERE report_id = '" . (int)$report_id . "' 
            ORDER BY start_time DESC 
            LIMIT 1
        ");
        
        if ($execution_query->num_rows) {
            $report['latest_execution'] = $execution_query->row;
        }
        
        return $report;
    }
    
    /**
     * Get BI Analytics Data
     */
    public function getBIAnalytics($days = 30) {
        $query = $this->db->query("
            SELECT analysis_type, metric_name, AVG(metric_value) as avg_value, 
                   AVG(confidence_score) as avg_confidence
            FROM " . DB_PREFIX . "meschain_bi_analytics 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
            GROUP BY analysis_type, metric_name
            ORDER BY avg_confidence DESC
        ");
        return $query->rows;
    }
    
    /**
     * Get Dashboard Insights
     */
    public function getDashboardInsights() {
        $insights = [];
        
        // Most used report types
        $query = $this->db->query("
            SELECT r.report_type, COUNT(e.execution_id) as usage_count
            FROM " . DB_PREFIX . "meschain_advanced_reports r
            JOIN " . DB_PREFIX . "meschain_report_executions e ON r.report_id = e.report_id
            WHERE e.start_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY r.report_type
            ORDER BY usage_count DESC
        ");
        $insights['most_used_types'] = $query->rows;
        
        // Peak usage hours
        $query = $this->db->query("
            SELECT HOUR(start_time) as hour, COUNT(*) as executions
            FROM " . DB_PREFIX . "meschain_report_executions
            WHERE start_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY HOUR(start_time)
            ORDER BY executions DESC
        ");
        $insights['peak_hours'] = $query->rows;
        
        // Performance trends
        $query = $this->db->query("
            SELECT DATE(start_time) as date, 
                   AVG(execution_duration) as avg_duration,
                   COUNT(*) as total_executions
            FROM " . DB_PREFIX . "meschain_report_executions
            WHERE start_time >= DATE_SUB(NOW(), INTERVAL 14 DAY)
            AND status = 'completed'
            GROUP BY DATE(start_time)
            ORDER BY date ASC
        ");
        $insights['performance_trends'] = $query->rows;
        
        return $insights;
    }
    
    /**
     * Get System Health Metrics
     */
    public function getSystemHealthMetrics() {
        $health = [];
        
        // Database performance
        $query = $this->db->query("SHOW STATUS LIKE 'Queries'");
        $health['db_queries'] = $query->row['Value'] ?? 0;
        
        // Failed executions in last 24 hours
        $query = $this->db->query("
            SELECT COUNT(*) as failed_count
            FROM " . DB_PREFIX . "meschain_report_executions 
            WHERE status = 'failed' 
            AND start_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $health['failed_executions_24h'] = $query->row['failed_count'];
        
        // Average response time
        $query = $this->db->query("
            SELECT AVG(execution_duration) as avg_response
            FROM " . DB_PREFIX . "meschain_report_executions 
            WHERE status = 'completed' 
            AND start_time >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $health['avg_response_time'] = round($query->row['avg_response'] ?? 0, 3);
        
        return $health;
    }
    
    /**
     * Create Sample Report
     */
    public function createSampleReport($type) {
        $sample_reports = [
            'financial' => [
                'name' => 'Monthly Financial Summary',
                'description' => 'Comprehensive financial performance report',
                'config' => json_encode([
                    'metrics' => ['revenue', 'profit', 'expenses'],
                    'period' => 'monthly',
                    'charts' => ['line', 'pie']
                ])
            ],
            'operational' => [
                'name' => 'Operational Efficiency Report',
                'description' => 'Key operational metrics and KPIs',
                'config' => json_encode([
                    'metrics' => ['orders', 'processing_time', 'efficiency'],
                    'period' => 'weekly',
                    'charts' => ['bar', 'gauge']
                ])
            ]
        ];
        
        if (!isset($sample_reports[$type])) {
            return false;
        }
        
        $report = $sample_reports[$type];
        
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_advanced_reports SET
            report_name = '" . $this->db->escape($report['name']) . "',
            report_type = '" . $this->db->escape($type) . "',
            description = '" . $this->db->escape($report['description']) . "',
            category = 'sample',
            data_sources = '[]',
            report_config = '" . $this->db->escape($report['config']) . "',
            visualization_config = '{}',
            access_permissions = '[]',
            created_by = '" . (int)$this->user->getId() . "',
            status = 'active'
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Install Module Tables
     */
    public function install() {
        // This would be called during module installation
        // Tables are created by the reporting engine itself
        return true;
    }
    
    /**
     * Uninstall Module
     */
    public function uninstall() {
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `code` = 'module_enterprise_reporting_dashboard'");
        
        // Note: We don't drop tables as they may contain important data
        // Tables should be manually removed if needed
        
        return true;
    }
} 