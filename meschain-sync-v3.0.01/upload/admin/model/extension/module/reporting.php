<?php
/**
 * Reporting Model
 * MesChain-Sync gelişmiş raporlama sistemi için model dosyası
 */
class ModelExtensionModuleReporting extends Model {
    
    /**
     * Satış raporunu getir
     * @param array $data Filtre parametreleri
     * @return array Satış raporu
     */
    public function getSalesReport($data = []) {
        $report = [];
        
        // Tarih aralığı kontrolü
        $date_start = $data['date_start'] ?? date('Y-m-01');
        $date_end = $data['date_end'] ?? date('Y-m-d');
        $marketplace = $data['marketplace'] ?? 'all';
        
        $report['summary'] = $this->getSalesSummary($date_start, $date_end, $marketplace);
        $report['daily_breakdown'] = $this->getDailySales($date_start, $date_end, $marketplace);
        $report['marketplace_breakdown'] = $this->getMarketplaceSales($date_start, $date_end);
        $report['top_products'] = $this->getTopSellingProducts($date_start, $date_end, $marketplace);
        $report['category_breakdown'] = $this->getCategorySales($date_start, $date_end, $marketplace);
        
        return $report;
    }
    
    /**
     * Satış özeti getir
     * @param string $date_start Başlangıç tarihi
     * @param string $date_end Bitiş tarihi
     * @param string $marketplace Pazaryeri
     * @return array Satış özeti
     */
    private function getSalesSummary($date_start, $date_end, $marketplace) {
        $where_marketplace = '';
        if ($marketplace !== 'all') {
            $where_marketplace = " AND marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        $query = $this->db->query("SELECT 
            COUNT(*) as total_orders,
            SUM(total_amount) as total_revenue,
            SUM(profit_amount) as total_profit,
            AVG(total_amount) as average_order_value,
            COUNT(DISTINCT customer_email) as unique_customers
            FROM `" . DB_PREFIX . "meschain_order` 
            WHERE DATE(date_added) >= '" . $this->db->escape($date_start) . "' 
            AND DATE(date_added) <= '" . $this->db->escape($date_end) . "'
            AND status IN ('completed', 'shipped', 'delivered')
            $where_marketplace");
        
        $summary = $query->row;
        
        // Önceki dönem karşılaştırması
        $previous_period = $this->getPreviousPeriodComparison($date_start, $date_end, $marketplace);
        $summary['comparison'] = $previous_period;
        
        return $summary;
    }
    
    /**
     * Günlük satış verilerini getir
     * @param string $date_start Başlangıç tarihi
     * @param string $date_end Bitiş tarihi
     * @param string $marketplace Pazaryeri
     * @return array Günlük satış verileri
     */
    private function getDailySales($date_start, $date_end, $marketplace) {
        $where_marketplace = '';
        if ($marketplace !== 'all') {
            $where_marketplace = " AND marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        $query = $this->db->query("SELECT 
            DATE(date_added) as sale_date,
            COUNT(*) as order_count,
            SUM(total_amount) as revenue,
            SUM(profit_amount) as profit
            FROM `" . DB_PREFIX . "meschain_order` 
            WHERE DATE(date_added) >= '" . $this->db->escape($date_start) . "' 
            AND DATE(date_added) <= '" . $this->db->escape($date_end) . "'
            AND status IN ('completed', 'shipped', 'delivered')
            $where_marketplace
            GROUP BY DATE(date_added)
            ORDER BY sale_date ASC");
        
        return $query->rows;
    }
    
    /**
     * Pazaryeri bazında satış verilerini getir
     * @param string $date_start Başlangıç tarihi
     * @param string $date_end Bitiş tarihi
     * @return array Pazaryeri satış verileri
     */
    private function getMarketplaceSales($date_start, $date_end) {
        $query = $this->db->query("SELECT 
            marketplace,
            COUNT(*) as order_count,
            SUM(total_amount) as revenue,
            SUM(profit_amount) as profit,
            AVG(total_amount) as avg_order_value
            FROM `" . DB_PREFIX . "meschain_order` 
            WHERE DATE(date_added) >= '" . $this->db->escape($date_start) . "' 
            AND DATE(date_added) <= '" . $this->db->escape($date_end) . "'
            AND status IN ('completed', 'shipped', 'delivered')
            GROUP BY marketplace
            ORDER BY revenue DESC");
        
        return $query->rows;
    }
    
    /**
     * En çok satan ürünleri getir
     * @param string $date_start Başlangıç tarihi
     * @param string $date_end Bitiş tarihi
     * @param string $marketplace Pazaryeri
     * @return array En çok satan ürünler
     */
    private function getTopSellingProducts($date_start, $date_end, $marketplace) {
        $where_marketplace = '';
        if ($marketplace !== 'all') {
            $where_marketplace = " AND o.marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        $query = $this->db->query("SELECT 
            oi.product_id,
            oi.product_name,
            oi.model,
            SUM(oi.quantity) as total_quantity,
            SUM(oi.total) as total_revenue,
            COUNT(DISTINCT oi.order_id) as order_count
            FROM `" . DB_PREFIX . "meschain_order_item` oi
            LEFT JOIN `" . DB_PREFIX . "meschain_order` o ON (oi.order_id = o.order_id)
            WHERE DATE(o.date_added) >= '" . $this->db->escape($date_start) . "' 
            AND DATE(o.date_added) <= '" . $this->db->escape($date_end) . "'
            AND o.status IN ('completed', 'shipped', 'delivered')
            $where_marketplace
            GROUP BY oi.product_id
            ORDER BY total_quantity DESC
            LIMIT 20");
        
        return $query->rows;
    }
    
    /**
     * Kategori bazında satış verilerini getir
     * @param string $date_start Başlangıç tarihi
     * @param string $date_end Bitiş tarihi
     * @param string $marketplace Pazaryeri
     * @return array Kategori satış verileri
     */
    private function getCategorySales($date_start, $date_end, $marketplace) {
        $where_marketplace = '';
        if ($marketplace !== 'all') {
            $where_marketplace = " AND o.marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        $query = $this->db->query("SELECT 
            cd.name as category_name,
            COUNT(*) as order_count,
            SUM(oi.quantity) as total_quantity,
            SUM(oi.total) as total_revenue
            FROM `" . DB_PREFIX . "meschain_order_item` oi
            LEFT JOIN `" . DB_PREFIX . "meschain_order` o ON (oi.order_id = o.order_id)
            LEFT JOIN `" . DB_PREFIX . "product_to_category` p2c ON (oi.product_id = p2c.product_id)
            LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (p2c.category_id = cd.category_id)
            WHERE DATE(o.date_added) >= '" . $this->db->escape($date_start) . "' 
            AND DATE(o.date_added) <= '" . $this->db->escape($date_end) . "'
            AND o.status IN ('completed', 'shipped', 'delivered')
            AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            $where_marketplace
            GROUP BY cd.category_id
            ORDER BY total_revenue DESC
            LIMIT 15");
        
        return $query->rows;
    }
    
    /**
     * Stok raporu getir
     * @param array $data Filtre parametreleri
     * @return array Stok raporu
     */
    public function getInventoryReport($data = []) {
        $report = [];
        
        $marketplace = $data['marketplace'] ?? 'all';
        $stock_threshold = $data['stock_threshold'] ?? 10;
        
        $report['summary'] = $this->getInventorySummary($marketplace);
        $report['low_stock'] = $this->getLowStockProducts($marketplace, $stock_threshold);
        $report['out_of_stock'] = $this->getOutOfStockProducts($marketplace);
        $report['overstocked'] = $this->getOverstockedProducts($marketplace);
        $report['stock_movements'] = $this->getStockMovements($marketplace);
        
        return $report;
    }
    
    /**
     * Stok özeti getir
     * @param string $marketplace Pazaryeri
     * @return array Stok özeti
     */
    private function getInventorySummary($marketplace) {
        $where_marketplace = '';
        if ($marketplace !== 'all') {
            $where_marketplace = " AND mi.marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        $query = $this->db->query("SELECT 
            COUNT(*) as total_products,
            SUM(quantity) as total_stock,
            SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as out_of_stock_count,
            SUM(CASE WHEN quantity <= 10 AND quantity > 0 THEN 1 ELSE 0 END) as low_stock_count,
            AVG(quantity) as avg_stock_per_product
            FROM `" . DB_PREFIX . "meschain_inventory` mi
            WHERE 1=1 $where_marketplace");
        
        return $query->row;
    }
    
    /**
     * Düşük stoklu ürünleri getir
     * @param string $marketplace Pazaryeri
     * @param int $threshold Stok eşiği
     * @return array Düşük stoklu ürünler
     */
    private function getLowStockProducts($marketplace, $threshold) {
        $where_marketplace = '';
        if ($marketplace !== 'all') {
            $where_marketplace = " AND mi.marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        $query = $this->db->query("SELECT 
            mi.product_id,
            mi.sku,
            pd.name as product_name,
            mi.quantity,
            mi.marketplace,
            mi.date_modified
            FROM `" . DB_PREFIX . "meschain_inventory` mi
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (mi.product_id = pd.product_id)
            WHERE mi.quantity <= '" . (int)$threshold . "' 
            AND mi.quantity > 0
            AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            $where_marketplace
            ORDER BY mi.quantity ASC");
        
        return $query->rows;
    }
    
    /**
     * Performans raporu getir
     * @param array $data Filtre parametreleri
     * @return array Performans raporu
     */
    public function getPerformanceReport($data = []) {
        $report = [];
        
        $date_start = $data['date_start'] ?? date('Y-m-01');
        $date_end = $data['date_end'] ?? date('Y-m-d');
        
        $report['api_performance'] = $this->getAPIPerformance($date_start, $date_end);
        $report['sync_performance'] = $this->getSyncPerformance($date_start, $date_end);
        $report['error_summary'] = $this->getErrorSummary($date_start, $date_end);
        $report['system_health'] = $this->getSystemHealth();
        
        return $report;
    }
    
    /**
     * API performans verilerini getir
     * @param string $date_start Başlangıç tarihi
     * @param string $date_end Bitiş tarihi
     * @return array API performans verileri
     */
    private function getAPIPerformance($date_start, $date_end) {
        $query = $this->db->query("SELECT 
            marketplace,
            endpoint,
            COUNT(*) as request_count,
            AVG(response_time) as avg_response_time,
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count,
            SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as error_count
            FROM `" . DB_PREFIX . "meschain_api_log` 
            WHERE DATE(date_created) >= '" . $this->db->escape($date_start) . "' 
            AND DATE(date_created) <= '" . $this->db->escape($date_end) . "'
            GROUP BY marketplace, endpoint
            ORDER BY request_count DESC");
        
        return $query->rows;
    }
    
    /**
     * Finansal rapor getir
     * @param array $data Filtre parametreleri
     * @return array Finansal rapor
     */
    public function getFinancialReport($data = []) {
        $report = [];
        
        $date_start = $data['date_start'] ?? date('Y-m-01');
        $date_end = $data['date_end'] ?? date('Y-m-d');
        $marketplace = $data['marketplace'] ?? 'all';
        
        $report['revenue_summary'] = $this->getRevenueSummary($date_start, $date_end, $marketplace);
        $report['commission_breakdown'] = $this->getCommissionBreakdown($date_start, $date_end, $marketplace);
        $report['profit_analysis'] = $this->getProfitAnalysis($date_start, $date_end, $marketplace);
        $report['expense_tracking'] = $this->getExpenseTracking($date_start, $date_end, $marketplace);
        
        return $report;
    }
    
    /**
     * Gelir özeti getir
     * @param string $date_start Başlangıç tarihi
     * @param string $date_end Bitiş tarihi
     * @param string $marketplace Pazaryeri
     * @return array Gelir özeti
     */
    private function getRevenueSummary($date_start, $date_end, $marketplace) {
        $where_marketplace = '';
        if ($marketplace !== 'all') {
            $where_marketplace = " AND marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        $query = $this->db->query("SELECT 
            SUM(total_amount) as gross_revenue,
            SUM(commission_amount) as total_commission,
            SUM(shipping_cost) as total_shipping,
            SUM(total_amount - commission_amount - shipping_cost) as net_revenue,
            COUNT(*) as total_transactions
            FROM `" . DB_PREFIX . "meschain_order` 
            WHERE DATE(date_added) >= '" . $this->db->escape($date_start) . "' 
            AND DATE(date_added) <= '" . $this->db->escape($date_end) . "'
            AND status IN ('completed', 'shipped', 'delivered')
            $where_marketplace");
        
        return $query->row;
    }
    
    /**
     * Özel rapor oluştur
     * @param array $config Rapor konfigürasyonu
     * @return array Özel rapor
     */
    public function generateCustomReport($config) {
        $report = [
            'title' => $config['title'] ?? 'Custom Report',
            'date_generated' => date('Y-m-d H:i:s'),
            'parameters' => $config,
            'data' => []
        ];
        
        try {
            switch ($config['type']) {
                case 'sales_by_category':
                    $report['data'] = $this->getSalesByCategory($config);
                    break;
                    
                case 'customer_analysis':
                    $report['data'] = $this->getCustomerAnalysis($config);
                    break;
                    
                case 'product_performance':
                    $report['data'] = $this->getProductPerformance($config);
                    break;
                    
                case 'marketplace_comparison':
                    $report['data'] = $this->getMarketplaceComparison($config);
                    break;
                    
                default:
                    $report['error'] = 'Unknown report type';
            }
            
        } catch (Exception $e) {
            $report['error'] = $e->getMessage();
            $this->log->write('Custom Report Error: ' . $e->getMessage());
        }
        
        return $report;
    }
    
    /**
     * Rapor verilerini CSV formatında export et
     * @param array $data Rapor verileri
     * @param string $filename Dosya adı
     * @return string CSV dosya yolu
     */
    public function exportToCSV($data, $filename) {
        $file_path = DIR_DOWNLOAD . 'meschain_reports/' . $filename . '_' . date('Y_m_d_H_i_s') . '.csv';
        
        // Dizin yoksa oluştur
        $dir = dirname($file_path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $handle = fopen($file_path, 'w');
        
        if ($handle === false) {
            return false;
        }
        
        // UTF-8 BOM ekle Excel uyumluluğu için
        fwrite($handle, "\xEF\xBB\xBF");
        
        if (!empty($data)) {
            // Header satırını yaz
            fputcsv($handle, array_keys($data[0]));
            
            // Veri satırlarını yaz
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
        }
        
        fclose($handle);
        
        return file_exists($file_path) ? $file_path : false;
    }
    
    /**
     * Raporlama tabloları oluştur
     */
    public function createReportingTables() {
        // Meschain orders tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_order` (
            `order_id` int(11) NOT NULL AUTO_INCREMENT,
            `marketplace_order_id` varchar(100) NOT NULL,
            `marketplace` varchar(50) NOT NULL,
            `customer_email` varchar(100),
            `total_amount` decimal(15,4) DEFAULT 0.0000,
            `commission_amount` decimal(15,4) DEFAULT 0.0000,
            `shipping_cost` decimal(15,4) DEFAULT 0.0000,
            `profit_amount` decimal(15,4) DEFAULT 0.0000,
            `status` varchar(50) NOT NULL,
            `date_added` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`order_id`),
            UNIQUE KEY `idx_marketplace_order` (`marketplace`, `marketplace_order_id`),
            KEY `idx_status` (`status`),
            KEY `idx_date_added` (`date_added`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // API log tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_api_log` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `marketplace` varchar(50) NOT NULL,
            `endpoint` varchar(200) NOT NULL,
            `method` varchar(10) NOT NULL,
            `status` varchar(20) NOT NULL,
            `response_time` decimal(10,4) DEFAULT 0.0000,
            `error_message` text,
            `date_created` datetime NOT NULL,
            PRIMARY KEY (`log_id`),
            KEY `idx_marketplace` (`marketplace`),
            KEY `idx_status` (`status`),
            KEY `idx_date_created` (`date_created`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }
} 