<?php
/**
 * Reporting Controller
 * MesChain-Sync raporlama ve analiz sistemi
 */
class ControllerExtensionModuleReporting extends Controller {
    private $error = array();
    
    public function index() {
        $this->load->language('extension/module/meschain_sync');
        
        $this->document->setTitle('MesChain Raporlama Sistemi');
        
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => 'Ana Sayfa',
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'Raporlama',
            'href' => $this->url->link('extension/module/reporting', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Reporting Helper'ı yükle
        require_once(DIR_SYSTEM . 'library/meschain/helper/reporting.php');
        $reportingHelper = new ReportingHelper($this->registry);
        
        // Dashboard istatistikleri
        $data['dashboard_stats'] = $reportingHelper->getDashboardStats();
        
        // Pazaryeri satış raporu (bu ay)
        $data['marketplace_sales'] = $reportingHelper->getMarketplaceSalesReport();
        
        // En çok satan ürünler (top 10)
        $data['top_products'] = $reportingHelper->getTopSellingProducts(10);
        
        // Stok raporu
        $data['stock_report'] = $reportingHelper->getStockReport();
        
        // Aylık satış trendi (son 12 ay)
        $data['sales_trend'] = $reportingHelper->getMonthlySalesTrend(12);
        
        // Dropshipping raporu
        $data['dropshipping_report'] = $reportingHelper->getDropshippingReport();
        
        // URL'ler
        $data['export_marketplace_url'] = $this->url->link('extension/module/reporting/exportMarketplace', 'user_token=' . $this->session->data['user_token'], true);
        $data['export_products_url'] = $this->url->link('extension/module/reporting/exportProducts', 'user_token=' . $this->session->data['user_token'], true);
        $data['export_stock_url'] = $this->url->link('extension/module/reporting/exportStock', 'user_token=' . $this->session->data['user_token'], true);
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/reporting', $data));
    }
    
    /**
     * Pazaryeri satış raporunu export et
     */
    public function exportMarketplace() {
        require_once(DIR_SYSTEM . 'library/meschain/helper/reporting.php');
        $reportingHelper = new ReportingHelper($this->registry);
        
        $startDate = isset($this->request->get['start_date']) ? $this->request->get['start_date'] : date('Y-m-01');
        $endDate = isset($this->request->get['end_date']) ? $this->request->get['end_date'] : date('Y-m-d');
        
        $data = $reportingHelper->getMarketplaceSalesReport($startDate, $endDate);
        $filename = $reportingHelper->exportToExcel('marketplace_sales', $data);
        
        if ($filename) {
            $this->response->addHeader('Content-Type: application/octet-stream');
            $this->response->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
            $this->response->addHeader('Expires: 0');
            $this->response->addHeader('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            $this->response->addHeader('Pragma: public');
            $this->response->addHeader('Content-Length: ' . filesize(DIR_DOWNLOAD . $filename));
            
            $this->response->setOutput(file_get_contents(DIR_DOWNLOAD . $filename));
            
            // Dosyayı sil
            unlink(DIR_DOWNLOAD . $filename);
        } else {
            $this->response->setOutput('Export hatası oluştu.');
        }
    }
    
    /**
     * En çok satan ürünler raporunu export et
     */
    public function exportProducts() {
        require_once(DIR_SYSTEM . 'library/meschain/helper/reporting.php');
        $reportingHelper = new ReportingHelper($this->registry);
        
        $startDate = isset($this->request->get['start_date']) ? $this->request->get['start_date'] : date('Y-m-01');
        $endDate = isset($this->request->get['end_date']) ? $this->request->get['end_date'] : date('Y-m-d');
        $limit = isset($this->request->get['limit']) ? $this->request->get['limit'] : 50;
        
        $data = $reportingHelper->getTopSellingProducts($limit, $startDate, $endDate);
        $filename = $reportingHelper->exportToExcel('top_products', $data);
        
        if ($filename) {
            $this->response->addHeader('Content-Type: application/octet-stream');
            $this->response->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
            $this->response->addHeader('Expires: 0');
            $this->response->addHeader('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            $this->response->addHeader('Pragma: public');
            $this->response->addHeader('Content-Length: ' . filesize(DIR_DOWNLOAD . $filename));
            
            $this->response->setOutput(file_get_contents(DIR_DOWNLOAD . $filename));
            
            // Dosyayı sil
            unlink(DIR_DOWNLOAD . $filename);
        } else {
            $this->response->setOutput('Export hatası oluştu.');
        }
    }
    
    /**
     * Stok raporunu export et
     */
    public function exportStock() {
        require_once(DIR_SYSTEM . 'library/meschain/helper/reporting.php');
        $reportingHelper = new ReportingHelper($this->registry);
        
        $data = $reportingHelper->getStockReport();
        
        // Düşük stok ve stokta olmayan ürünleri birleştir
        $exportData = array_merge($data['low_stock'], $data['out_of_stock']);
        
        $filename = $reportingHelper->exportToExcel('stock_report', $exportData);
        
        if ($filename) {
            $this->response->addHeader('Content-Type: application/octet-stream');
            $this->response->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
            $this->response->addHeader('Expires: 0');
            $this->response->addHeader('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            $this->response->addHeader('Pragma: public');
            $this->response->addHeader('Content-Length: ' . filesize(DIR_DOWNLOAD . $filename));
            
            $this->response->setOutput(file_get_contents(DIR_DOWNLOAD . $filename));
            
            // Dosyayı sil
            unlink(DIR_DOWNLOAD . $filename);
        } else {
            $this->response->setOutput('Export hatası oluştu.');
        }
    }
    
    /**
     * AJAX ile dashboard verilerini güncelle
     */
    public function refreshDashboard() {
        require_once(DIR_SYSTEM . 'library/meschain/helper/reporting.php');
        $reportingHelper = new ReportingHelper($this->registry);
        
        $json = array();
        
        try {
            $json['dashboard_stats'] = $reportingHelper->getDashboardStats();
            $json['success'] = true;
        } catch (Exception $e) {
            $json['error'] = 'Dashboard verileri güncellenirken hata oluştu: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Özel tarih aralığı için rapor al
     */
    public function getCustomReport() {
        require_once(DIR_SYSTEM . 'library/meschain/helper/reporting.php');
        $reportingHelper = new ReportingHelper($this->registry);
        
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $startDate = isset($this->request->post['start_date']) ? $this->request->post['start_date'] : date('Y-m-01');
            $endDate = isset($this->request->post['end_date']) ? $this->request->post['end_date'] : date('Y-m-d');
            $reportType = isset($this->request->post['report_type']) ? $this->request->post['report_type'] : 'marketplace';
            
            try {
                switch ($reportType) {
                    case 'marketplace':
                        $json['data'] = $reportingHelper->getMarketplaceSalesReport($startDate, $endDate);
                        break;
                    case 'products':
                        $limit = isset($this->request->post['limit']) ? $this->request->post['limit'] : 10;
                        $json['data'] = $reportingHelper->getTopSellingProducts($limit, $startDate, $endDate);
                        break;
                    case 'sales_trend':
                        $months = isset($this->request->post['months']) ? $this->request->post['months'] : 12;
                        $json['data'] = $reportingHelper->getMonthlySalesTrend($months);
                        break;
                    default:
                        $json['error'] = 'Geçersiz rapor tipi';
                        break;
                }
                
                if (!isset($json['error'])) {
                    $json['success'] = true;
                }
                
            } catch (Exception $e) {
                $json['error'] = 'Rapor oluşturulurken hata oluştu: ' . $e->getMessage();
            }
        } else {
            $json['error'] = 'Geçersiz istek metodu';
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Dropshipping performans detayları
     */
    public function getDropshippingDetails() {
        require_once(DIR_SYSTEM . 'library/meschain/helper/reporting.php');
        $reportingHelper = new ReportingHelper($this->registry);
        
        $json = array();
        
        try {
            $json['data'] = $reportingHelper->getDropshippingReport();
            $json['success'] = true;
        } catch (Exception $e) {
            $json['error'] = 'Dropshipping raporu alınırken hata oluştu: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
} 