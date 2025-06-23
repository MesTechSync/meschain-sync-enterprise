<?php
/**
 * MesChain Analytics Engine
 * Business Intelligence ve Analytics sistemi
 * @author MesChain-Sync Team
 * @version 1.0.0
 */

class MesChainAnalyticsEngine {
    private $db;
    private $logger;
    private $config;
    private $cache;
    
    // Analiz türleri
    const ANALYSIS_SALES = 'sales';
    const ANALYSIS_PRODUCTS = 'products';
    const ANALYSIS_MARKETPLACE = 'marketplace';
    const ANALYSIS_PERFORMANCE = 'performance';
    const ANALYSIS_TRENDS = 'trends';
    
    // Zaman periyotları
    const PERIOD_DAILY = 'daily';
    const PERIOD_WEEKLY = 'weekly';
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_YEARLY = 'yearly';
    
    public function __construct($db, $logger, $config, $cache) {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = $config;
        $this->cache = $cache;
    }
    
    /**
     * Genel dashboard verilerini getir
     */
    public function getDashboardData($period = 'monthly') {
        $cache_key = "analytics_dashboard_{$period}";
        
        $data = $this->cache->get($cache_key);
        if ($data) {
            return $data;
        }
        
        try {
            $data = array(
                'summary' => $this->getSummaryStats($period),
                'sales_chart' => $this->getSalesChart($period),
                'marketplace_comparison' => $this->getMarketplaceComparison($period),
                'top_products' => $this->getTopProducts($period),
                'performance_metrics' => $this->getPerformanceMetrics($period),
                'generated_at' => date('Y-m-d H:i:s')
            );
            
            $this->cache->set($cache_key, $data, 1800);
            return $data;
            
        } catch (Exception $e) {
            $this->logger->error('Dashboard verisi hatası: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Özet istatistikleri
     */
    public function getSummaryStats($period) {
        $date_condition = $this->getDateCondition($period);
        
        $total_sales = $this->db->query("
            SELECT COUNT(*) as order_count, 
                   SUM(total_amount) as total_revenue
            FROM `" . DB_PREFIX . "meschain_orders` 
            WHERE {$date_condition}
        ");
        
        $total_products = $this->db->query("
            SELECT COUNT(DISTINCT opencart_product_id) as count
            FROM `" . DB_PREFIX . "meschain_products`
            WHERE status = 'active'
        ");
        
        return array(
            'total_orders' => (int)$total_sales->row['order_count'],
            'total_revenue' => (float)$total_sales->row['total_revenue'],
            'total_products' => (int)$total_products->row['count'],
            'period' => $period
        );
    }
    
    /**
     * Satış grafiği verisi
     */
    public function getSalesChart($period) {
        $group_by = $this->getGroupByFormat($period);
        $date_condition = $this->getDateCondition($period);
        
        $query = $this->db->query("
            SELECT DATE_FORMAT(date_added, '{$group_by}') as period_label,
                   COUNT(*) as order_count,
                   SUM(total_amount) as revenue,
                   marketplace
            FROM `" . DB_PREFIX . "meschain_orders`
            WHERE {$date_condition}
            GROUP BY DATE_FORMAT(date_added, '{$group_by}'), marketplace
            ORDER BY date_added ASC
        ");
        
        return $query->rows;
    }
    
    /**
     * Marketplace karşılaştırması
     */
    public function getMarketplaceComparison($period) {
        $date_condition = $this->getDateCondition($period);
        
        $query = $this->db->query("
            SELECT marketplace,
                   COUNT(*) as order_count,
                   SUM(total_amount) as revenue,
                   AVG(total_amount) as avg_order_value
            FROM `" . DB_PREFIX . "meschain_orders` o
            WHERE {$date_condition}
            GROUP BY marketplace
            ORDER BY revenue DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * En çok satan ürünler
     */
    public function getTopProducts($period, $limit = 10) {
        $date_condition = $this->getDateCondition($period);
        
        $query = $this->db->query("
            SELECT p.product_id,
                   p.name,
                   mp.marketplace,
                   COUNT(o.id) as order_count,
                   SUM(o.total_amount) as revenue
            FROM `" . DB_PREFIX . "meschain_orders` o
            LEFT JOIN `" . DB_PREFIX . "meschain_products` mp ON o.product_id = mp.marketplace_product_id
            LEFT JOIN `" . DB_PREFIX . "product_description` p ON mp.opencart_product_id = p.product_id
            WHERE {$date_condition} AND p.language_id = 2
            GROUP BY p.product_id, mp.marketplace
            ORDER BY revenue DESC
            LIMIT {$limit}
        ");
        
        return $query->rows;
    }
    
    /**
     * Performans metrikleri
     */
    public function getPerformanceMetrics($period) {
        $date_condition = $this->getDateCondition($period);
        
        $api_success_rate = $this->db->query("
            SELECT 
                COUNT(*) as total_calls,
                SUM(CASE WHEN response_code = 200 THEN 1 ELSE 0 END) as successful_calls
            FROM `" . DB_PREFIX . "meschain_api_health_logs`
            WHERE {$date_condition}
        ");
        
        $success_rate = 0;
        if ($api_success_rate->row['total_calls'] > 0) {
            $success_rate = ($api_success_rate->row['successful_calls'] / $api_success_rate->row['total_calls']) * 100;
        }
        
        return array(
            'api_success_rate' => round($success_rate, 2),
            'total_api_calls' => (int)$api_success_rate->row['total_calls']
        );
    }
    
    /**
     * Tarih koşulu oluştur
     */
    private function getDateCondition($period) {
        switch ($period) {
            case 'daily':
                return "date_added >= DATE(NOW())";
            case 'weekly':
                return "date_added >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'monthly':
                return "date_added >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case 'yearly':
                return "date_added >= DATE_SUB(NOW(), INTERVAL 365 DAY)";
            default:
                return "date_added >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        }
    }
    
    /**
     * Group by formatı
     */
    private function getGroupByFormat($period) {
        switch ($period) {
            case 'daily':
                return '%Y-%m-%d %H:00';
            case 'weekly':
                return '%Y-%m-%d';
            case 'monthly':
                return '%Y-%m-%d';
            case 'yearly':
                return '%Y-%m';
            default:
                return '%Y-%m-%d';
        }
    }
    
    /**
     * Cache temizle
     */
    public function clearCache() {
        $periods = array('daily', 'weekly', 'monthly', 'yearly');
        
        foreach ($periods as $period) {
            $this->cache->delete("analytics_dashboard_{$period}");
        }
        
        $this->logger->info('Analytics cache temizlendi');
    }
}

?> 