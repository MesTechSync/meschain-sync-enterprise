<?php
/**
 * MesChain-Sync Reporting Model
 * 
 * @package    MesChain-Sync
 * @author     MezBjen Team
 * @copyright  2024 MesChain
 * @version    1.0.0
 */

class ModelExtensionModuleReporting extends Model {
    
    /**
     * Tüm marketplace'lerden satış verilerini getirir
     * 
     * @param array $data Filtre parametreleri
     * @return array
     */
    public function getSalesData($data = array()) {
        $sql = "SELECT 
                    o.order_id,
                    o.date_added,
                    o.date_modified,
                    o.total,
                    o.currency_code,
                    o.order_status_id,
                    os.name as order_status,
                    o.payment_method,
                    o.shipping_method,
                    o.comment,
                    COALESCE(mo.marketplace, 'OpenCart') as marketplace,
                    mo.marketplace_order_id,
                    mo.marketplace_status,
                    mo.commission_rate,
                    mo.commission_amount
                FROM `" . DB_PREFIX . "order` o
                LEFT JOIN `" . DB_PREFIX . "order_status` os ON (o.order_status_id = os.order_status_id)
                LEFT JOIN `" . DB_PREFIX . "marketplace_order` mo ON (o.order_id = mo.order_id)
                WHERE os.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        
        // Tarih filtreleri
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        // Marketplace filtresi
        if (!empty($data['filter_marketplace'])) {
            if ($data['filter_marketplace'] == 'opencart') {
                $sql .= " AND mo.marketplace IS NULL";
            } else {
                $sql .= " AND mo.marketplace = '" . $this->db->escape($data['filter_marketplace']) . "'";
            }
        }
        
        // Durum filtresi
        if (!empty($data['filter_order_status_id'])) {
            $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
        }
        
        $sql .= " ORDER BY o.date_added DESC";
        
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }
            
            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Toplam satış sayısını getirir
     * 
     * @param array $data Filtre parametreleri
     * @return int
     */
    public function getTotalSales($data = array()) {
        $sql = "SELECT COUNT(DISTINCT o.order_id) AS total
                FROM `" . DB_PREFIX . "order` o
                LEFT JOIN `" . DB_PREFIX . "order_status` os ON (o.order_status_id = os.order_status_id)
                LEFT JOIN `" . DB_PREFIX . "marketplace_order` mo ON (o.order_id = mo.order_id)
                WHERE os.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        
        // Tarih filtreleri
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        // Marketplace filtresi
        if (!empty($data['filter_marketplace'])) {
            if ($data['filter_marketplace'] == 'opencart') {
                $sql .= " AND mo.marketplace IS NULL";
            } else {
                $sql .= " AND mo.marketplace = '" . $this->db->escape($data['filter_marketplace']) . "'";
            }
        }
        
        // Durum filtresi
        if (!empty($data['filter_order_status_id'])) {
            $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
        }
        
        $query = $this->db->query($sql);
        
        return $query->row['total'];
    }
    
    /**
     * Marketplace bazlı özet istatistikleri getirir
     * 
     * @param array $data Filtre parametreleri
     * @return array
     */
    public function getMarketplaceStats($data = array()) {
        $sql = "SELECT 
                    COALESCE(mo.marketplace, 'OpenCart') as marketplace,
                    COUNT(DISTINCT o.order_id) as order_count,
                    SUM(o.total) as total_sales,
                    AVG(o.total) as average_order_value,
                    SUM(mo.commission_amount) as total_commission
                FROM `" . DB_PREFIX . "order` o
                LEFT JOIN `" . DB_PREFIX . "order_status` os ON (o.order_status_id = os.order_status_id)
                LEFT JOIN `" . DB_PREFIX . "marketplace_order` mo ON (o.order_id = mo.order_id)
                WHERE os.language_id = '" . (int)$this->config->get('config_language_id') . "'
                AND o.order_status_id > '0'";
        
        // Tarih filtreleri
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        $sql .= " GROUP BY COALESCE(mo.marketplace, 'OpenCart')";
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Ürün bazlı satış raporunu getirir
     * 
     * @param array $data Filtre parametreleri
     * @return array
     */
    public function getProductSalesReport($data = array()) {
        $sql = "SELECT 
                    op.product_id,
                    op.name as product_name,
                    op.model,
                    SUM(op.quantity) as total_quantity,
                    SUM(op.total) as total_sales,
                    COUNT(DISTINCT op.order_id) as order_count,
                    COALESCE(mo.marketplace, 'OpenCart') as marketplace
                FROM `" . DB_PREFIX . "order_product` op
                LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)
                LEFT JOIN `" . DB_PREFIX . "order_status` os ON (o.order_status_id = os.order_status_id)
                LEFT JOIN `" . DB_PREFIX . "marketplace_order` mo ON (o.order_id = mo.order_id)
                WHERE os.language_id = '" . (int)$this->config->get('config_language_id') . "'
                AND o.order_status_id > '0'";
        
        // Tarih filtreleri
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        // Marketplace filtresi
        if (!empty($data['filter_marketplace'])) {
            if ($data['filter_marketplace'] == 'opencart') {
                $sql .= " AND mo.marketplace IS NULL";
            } else {
                $sql .= " AND mo.marketplace = '" . $this->db->escape($data['filter_marketplace']) . "'";
            }
        }
        
        $sql .= " GROUP BY op.product_id, op.name, op.model, COALESCE(mo.marketplace, 'OpenCart')
                  ORDER BY total_sales DESC";
        
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }
            
            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Günlük satış trendini getirir
     * 
     * @param array $data Filtre parametreleri
     * @return array
     */
    public function getDailySalesTrend($data = array()) {
        $sql = "SELECT 
                    DATE(o.date_added) as sale_date,
                    COUNT(DISTINCT o.order_id) as order_count,
                    SUM(o.total) as total_sales,
                    COALESCE(mo.marketplace, 'OpenCart') as marketplace
                FROM `" . DB_PREFIX . "order` o
                LEFT JOIN `" . DB_PREFIX . "order_status` os ON (o.order_status_id = os.order_status_id)
                LEFT JOIN `" . DB_PREFIX . "marketplace_order` mo ON (o.order_id = mo.order_id)
                WHERE os.language_id = '" . (int)$this->config->get('config_language_id') . "'
                AND o.order_status_id > '0'";
        
        // Tarih filtreleri
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        } else {
            // Varsayılan olarak son 30 gün
            $sql .= " AND DATE(o.date_added) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        // Marketplace filtresi
        if (!empty($data['filter_marketplace'])) {
            if ($data['filter_marketplace'] == 'opencart') {
                $sql .= " AND mo.marketplace IS NULL";
            } else {
                $sql .= " AND mo.marketplace = '" . $this->db->escape($data['filter_marketplace']) . "'";
            }
        }
        
        $sql .= " GROUP BY DATE(o.date_added), COALESCE(mo.marketplace, 'OpenCart')
                  ORDER BY sale_date ASC";
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Komisyon raporunu getirir
     * 
     * @param array $data Filtre parametreleri
     * @return array
     */
    public function getCommissionReport($data = array()) {
        $sql = "SELECT 
                    mo.marketplace,
                    COUNT(DISTINCT mo.order_id) as order_count,
                    SUM(o.total) as gross_sales,
                    AVG(mo.commission_rate) as avg_commission_rate,
                    SUM(mo.commission_amount) as total_commission,
                    SUM(o.total - mo.commission_amount) as net_sales
                FROM `" . DB_PREFIX . "marketplace_order` mo
                LEFT JOIN `" . DB_PREFIX . "order` o ON (mo.order_id = o.order_id)
                LEFT JOIN `" . DB_PREFIX . "order_status` os ON (o.order_status_id = os.order_status_id)
                WHERE os.language_id = '" . (int)$this->config->get('config_language_id') . "'
                AND o.order_status_id > '0'";
        
        // Tarih filtreleri
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        // Marketplace filtresi
        if (!empty($data['filter_marketplace'])) {
            $sql .= " AND mo.marketplace = '" . $this->db->escape($data['filter_marketplace']) . "'";
        }
        
        $sql .= " GROUP BY mo.marketplace
                  ORDER BY total_commission DESC";
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Stok durumu raporunu getirir
     * 
     * @param array $data Filtre parametreleri
     * @return array
     */
    public function getInventoryReport($data = array()) {
        $sql = "SELECT 
                    p.product_id,
                    pd.name as product_name,
                    p.model,
                    p.sku,
                    p.quantity as opencart_stock,
                    GROUP_CONCAT(
                        CONCAT(mps.marketplace, ':', mps.quantity) 
                        ORDER BY mps.marketplace 
                        SEPARATOR ', '
                    ) as marketplace_stocks,
                    p.price,
                    p.status
                FROM `" . DB_PREFIX . "product` p
                LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id)
                LEFT JOIN `" . DB_PREFIX . "marketplace_product_stock` mps ON (p.product_id = mps.product_id)
                WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        
        // Stok filtresi
        if (isset($data['filter_low_stock']) && $data['filter_low_stock']) {
            $sql .= " AND p.quantity <= '" . (int)$this->config->get('config_stock_warning') . "'";
        }
        
        // Durum filtresi
        if (isset($data['filter_status'])) {
            $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
        }
        
        $sql .= " GROUP BY p.product_id";
        
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }
            
            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Performans metriklerini getirir
     * 
     * @param string $marketplace
     * @param array $date_range
     * @return array
     */
    public function getPerformanceMetrics($marketplace = '', $date_range = array()) {
        $metrics = array();
        
        // Sipariş işleme süresi
        $sql = "SELECT 
                    AVG(TIMESTAMPDIFF(MINUTE, o.date_added, o.date_modified)) as avg_processing_time,
                    MIN(TIMESTAMPDIFF(MINUTE, o.date_added, o.date_modified)) as min_processing_time,
                    MAX(TIMESTAMPDIFF(MINUTE, o.date_added, o.date_modified)) as max_processing_time
                FROM `" . DB_PREFIX . "order` o
                LEFT JOIN `" . DB_PREFIX . "marketplace_order` mo ON (o.order_id = mo.order_id)
                WHERE o.order_status_id > '0'";
        
        if ($marketplace) {
            $sql .= " AND mo.marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        if (!empty($date_range['start'])) {
            $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($date_range['start']) . "'";
        }
        
        if (!empty($date_range['end'])) {
            $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($date_range['end']) . "'";
        }
        
        $query = $this->db->query($sql);
        $metrics['processing_time'] = $query->row;
        
        // İptal oranı
        $sql = "SELECT 
                    COUNT(CASE WHEN o.order_status_id = '7' THEN 1 END) as cancelled_orders,
                    COUNT(*) as total_orders,
                    (COUNT(CASE WHEN o.order_status_id = '7' THEN 1 END) * 100.0 / COUNT(*)) as cancellation_rate
                FROM `" . DB_PREFIX . "order` o
                LEFT JOIN `" . DB_PREFIX . "marketplace_order` mo ON (o.order_id = mo.order_id)
                WHERE 1=1";
        
        if ($marketplace) {
            $sql .= " AND mo.marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        if (!empty($date_range['start'])) {
            $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($date_range['start']) . "'";
        }
        
        if (!empty($date_range['end'])) {
            $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($date_range['end']) . "'";
        }
        
        $query = $this->db->query($sql);
        $metrics['cancellation'] = $query->row;
        
        return $metrics;
    }
    
    /**
     * Rapor verilerini dışa aktarır
     * 
     * @param string $type Rapor tipi
     * @param array $data Filtre parametreleri
     * @param string $format Dışa aktarım formatı (csv, excel, pdf)
     * @return string Dosya yolu
     */
    public function exportReport($type, $data, $format = 'csv') {
        $export_data = array();
        
        switch ($type) {
            case 'sales':
                $export_data = $this->getSalesData($data);
                break;
            case 'product':
                $export_data = $this->getProductSalesReport($data);
                break;
            case 'commission':
                $export_data = $this->getCommissionReport($data);
                break;
            case 'inventory':
                $export_data = $this->getInventoryReport($data);
                break;
        }
        
        // Dışa aktarım işlemi helper'da yapılacak
        $this->load->library('meschain/helper/export');
        $export_helper = new MesChainExportHelper();
        
        return $export_helper->export($export_data, $type, $format);
    }
} 