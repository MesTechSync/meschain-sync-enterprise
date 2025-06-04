<?php
/**
 * MesChain Reporting Helper
 * Pazaryeri raporlama ve analiz işlemleri
 */
class ReportingHelper {
    private $db;
    private $config;
    private $logFile;
    
    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->logFile = 'reporting.log';
    }
    
    /**
     * Log yazma metodu
     */
    private function writeLog($message) {
        $date = date('Y-m-d H:i:s');
        $logMessage = "[$date] $message\n";
        file_put_contents(DIR_LOGS . $this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Genel dashboard istatistikleri
     */
    public function getDashboardStats() {
        try {
            $stats = [];
            
            // Toplam ürün sayısı
            $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "product WHERE status = 1");
            $stats['total_products'] = $query->row['total'];
            
            // Toplam sipariş sayısı
            $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "order WHERE order_status_id > 0");
            $stats['total_orders'] = $query->row['total'];
            
            // Bu ayki sipariş sayısı
            $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "order WHERE order_status_id > 0 AND MONTH(date_added) = MONTH(NOW()) AND YEAR(date_added) = YEAR(NOW())");
            $stats['monthly_orders'] = $query->row['total'];
            
            // Toplam satış tutarı
            $query = $this->db->query("SELECT SUM(total) as total FROM " . DB_PREFIX . "order WHERE order_status_id > 0");
            $stats['total_sales'] = $query->row['total'] ?: 0;
            
            // Bu ayki satış tutarı
            $query = $this->db->query("SELECT SUM(total) as total FROM " . DB_PREFIX . "order WHERE order_status_id > 0 AND MONTH(date_added) = MONTH(NOW()) AND YEAR(date_added) = YEAR(NOW())");
            $stats['monthly_sales'] = $query->row['total'] ?: 0;
            
            // Aktif pazaryeri sayısı
            $marketplaces = ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon'];
            $active_marketplaces = 0;
            foreach ($marketplaces as $marketplace) {
                if ($this->config->get('module_' . $marketplace . '_status')) {
                    $active_marketplaces++;
                }
            }
            $stats['active_marketplaces'] = $active_marketplaces;
            
            return $stats;
            
        } catch (Exception $e) {
            $this->writeLog('Dashboard istatistikleri alınırken hata: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Pazaryeri bazlı satış raporu
     */
    public function getMarketplaceSalesReport($startDate = null, $endDate = null) {
        try {
            if (!$startDate) $startDate = date('Y-m-01'); // Bu ayın başı
            if (!$endDate) $endDate = date('Y-m-d'); // Bugün
            
            $report = [];
            $marketplaces = ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon'];
            
            foreach ($marketplaces as $marketplace) {
                // Pazaryeri siparişlerini al
                $query = $this->db->query("
                    SELECT 
                        COUNT(*) as order_count,
                        SUM(total) as total_sales,
                        AVG(total) as avg_order_value
                    FROM " . DB_PREFIX . "order 
                    WHERE order_status_id > 0 
                    AND comment LIKE '%" . $marketplace . "%'
                    AND DATE(date_added) BETWEEN '" . $this->db->escape($startDate) . "' AND '" . $this->db->escape($endDate) . "'
                ");
                
                $report[$marketplace] = [
                    'name' => ucfirst($marketplace),
                    'order_count' => $query->row['order_count'],
                    'total_sales' => $query->row['total_sales'] ?: 0,
                    'avg_order_value' => $query->row['avg_order_value'] ?: 0
                ];
            }
            
            return $report;
            
        } catch (Exception $e) {
            $this->writeLog('Pazaryeri satış raporu alınırken hata: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * En çok satan ürünler raporu
     */
    public function getTopSellingProducts($limit = 10, $startDate = null, $endDate = null) {
        try {
            if (!$startDate) $startDate = date('Y-m-01');
            if (!$endDate) $endDate = date('Y-m-d');
            
            $query = $this->db->query("
                SELECT 
                    p.product_id,
                    pd.name,
                    p.model,
                    p.price,
                    SUM(op.quantity) as total_sold,
                    SUM(op.total) as total_revenue
                FROM " . DB_PREFIX . "order_product op
                LEFT JOIN " . DB_PREFIX . "product p ON (op.product_id = p.product_id)
                LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
                LEFT JOIN " . DB_PREFIX . "order o ON (op.order_id = o.order_id)
                WHERE o.order_status_id > 0
                AND pd.language_id = 1
                AND DATE(o.date_added) BETWEEN '" . $this->db->escape($startDate) . "' AND '" . $this->db->escape($endDate) . "'
                GROUP BY p.product_id
                ORDER BY total_sold DESC
                LIMIT " . (int)$limit
            );
            
            return $query->rows;
            
        } catch (Exception $e) {
            $this->writeLog('En çok satan ürünler raporu alınırken hata: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Stok durumu raporu
     */
    public function getStockReport($lowStockThreshold = 10) {
        try {
            $report = [];
            
            // Düşük stok ürünleri
            $query = $this->db->query("
                SELECT 
                    p.product_id,
                    pd.name,
                    p.model,
                    p.quantity,
                    p.price
                FROM " . DB_PREFIX . "product p
                LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
                WHERE p.status = 1
                AND p.quantity <= " . (int)$lowStockThreshold . "
                AND pd.language_id = 1
                ORDER BY p.quantity ASC
            ");
            $report['low_stock'] = $query->rows;
            
            // Stokta olmayan ürünler
            $query = $this->db->query("
                SELECT 
                    p.product_id,
                    pd.name,
                    p.model,
                    p.quantity,
                    p.price
                FROM " . DB_PREFIX . "product p
                LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
                WHERE p.status = 1
                AND p.quantity = 0
                AND pd.language_id = 1
                ORDER BY pd.name ASC
            ");
            $report['out_of_stock'] = $query->rows;
            
            // Toplam stok değeri
            $query = $this->db->query("
                SELECT SUM(p.quantity * p.price) as total_stock_value
                FROM " . DB_PREFIX . "product p
                WHERE p.status = 1
            ");
            $report['total_stock_value'] = $query->row['total_stock_value'] ?: 0;
            
            return $report;
            
        } catch (Exception $e) {
            $this->writeLog('Stok raporu alınırken hata: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Aylık satış trendi
     */
    public function getMonthlySalesTrend($months = 12) {
        try {
            $query = $this->db->query("
                SELECT 
                    DATE_FORMAT(date_added, '%Y-%m') as month,
                    COUNT(*) as order_count,
                    SUM(total) as total_sales
                FROM " . DB_PREFIX . "order
                WHERE order_status_id > 0
                AND date_added >= DATE_SUB(NOW(), INTERVAL " . (int)$months . " MONTH)
                GROUP BY DATE_FORMAT(date_added, '%Y-%m')
                ORDER BY month ASC
            ");
            
            return $query->rows;
            
        } catch (Exception $e) {
            $this->writeLog('Aylık satış trendi alınırken hata: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Dropshipping performans raporu
     */
    public function getDropshippingReport() {
        try {
            $report = [];
            
            // Dropshipping siparişleri (eğer tablo varsa)
            $tableExists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "dropshipping_orders'");
            if ($tableExists->num_rows) {
                $query = $this->db->query("
                    SELECT 
                        status,
                        COUNT(*) as count,
                        SUM(total_amount) as total_amount
                    FROM " . DB_PREFIX . "dropshipping_orders
                    GROUP BY status
                ");
                $report['orders_by_status'] = $query->rows;
                
                // Tedarikçi bazlı performans
                $query = $this->db->query("
                    SELECT 
                        ds.name as supplier_name,
                        COUNT(do.id) as order_count,
                        SUM(do.total_amount) as total_amount,
                        AVG(do.commission) as avg_commission
                    FROM " . DB_PREFIX . "dropshipping_orders do
                    LEFT JOIN " . DB_PREFIX . "dropshipping_suppliers ds ON (do.supplier_id = ds.id)
                    WHERE ds.status = 1
                    GROUP BY ds.id
                    ORDER BY total_amount DESC
                ");
                $report['supplier_performance'] = $query->rows;
            }
            
            return $report;
            
        } catch (Exception $e) {
            $this->writeLog('Dropshipping raporu alınırken hata: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Rapor verilerini Excel formatında export et
     */
    public function exportToExcel($reportType, $data) {
        try {
            $filename = $reportType . '_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = DIR_DOWNLOAD . $filename;
            
            $file = fopen($filepath, 'w');
            
            // UTF-8 BOM ekle
            fwrite($file, "\xEF\xBB\xBF");
            
            switch ($reportType) {
                case 'marketplace_sales':
                    fputcsv($file, ['Pazaryeri', 'Sipariş Sayısı', 'Toplam Satış', 'Ortalama Sipariş Değeri']);
                    foreach ($data as $marketplace => $stats) {
                        fputcsv($file, [
                            $stats['name'],
                            $stats['order_count'],
                            number_format($stats['total_sales'], 2),
                            number_format($stats['avg_order_value'], 2)
                        ]);
                    }
                    break;
                    
                case 'top_products':
                    fputcsv($file, ['Ürün ID', 'Ürün Adı', 'Model', 'Fiyat', 'Satılan Adet', 'Toplam Gelir']);
                    foreach ($data as $product) {
                        fputcsv($file, [
                            $product['product_id'],
                            $product['name'],
                            $product['model'],
                            number_format($product['price'], 2),
                            $product['total_sold'],
                            number_format($product['total_revenue'], 2)
                        ]);
                    }
                    break;
            }
            
            fclose($file);
            
            return $filename;
            
        } catch (Exception $e) {
            $this->writeLog('Excel export hatası: ' . $e->getMessage());
            return false;
        }
    }
} 