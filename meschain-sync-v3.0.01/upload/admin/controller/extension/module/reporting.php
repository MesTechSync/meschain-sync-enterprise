<?php
/**
 * MesChain-Sync Reporting Controller
 * 
 * Gelişmiş raporlama sistemi controller dosyası
 * 
 * @category   Controller
 * @package    MesChain-Sync
 * @version    3.0.1
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

class ControllerExtensionModuleReporting extends Controller {
    
    private $error = array();
    
    /**
     * Main index method
     */
    public function index() {
        $this->load->language('extension/module/reporting');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/module/reporting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_extension_module_reporting->editSettings($this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }
        
        // Get dashboard statistics
        $data['stats'] = $this->model_extension_module_reporting->getDashboardStats();
        
        // Get top products
        $data['top_products'] = $this->model_extension_module_reporting->getTopProducts(array(
            'start' => 0,
            'limit' => 10
        ));
        
        // Set dates
        $data['date_start'] = isset($this->request->get['date_start']) ? $this->request->get['date_start'] : date('Y-m-01');
        $data['date_end'] = isset($this->request->get['date_end']) ? $this->request->get['date_end'] : date('Y-m-d');
        
        // Get chart data
        $data['chart_data'] = $this->getChartData($data['date_start'], $data['date_end']);
        
        $this->getForm($data);
    }
    
    /**
     * Generate report via AJAX
     */
    public function generateReport() {
        $this->load->language('extension/module/reporting');
        $this->load->model('extension/module/reporting');
        
        $json = array();
        
        try {
            $report_type = isset($this->request->post['report_type']) ? $this->request->post['report_type'] : 'sales';
            $date_start = isset($this->request->post['date_start']) ? $this->request->post['date_start'] : date('Y-m-01');
            $date_end = isset($this->request->post['date_end']) ? $this->request->post['date_end'] : date('Y-m-d');
            $marketplace = isset($this->request->post['marketplace']) ? $this->request->post['marketplace'] : 'all';
            
            $filter = array(
                'report_type' => $report_type,
                'date_start' => $date_start,
                'date_end' => $date_end,
                'marketplace' => $marketplace
            );
            
            switch ($report_type) {
                case 'sales':
                    $data = $this->model_extension_module_reporting->getSalesReport($filter);
                    break;
                case 'inventory':
                    $data = $this->model_extension_module_reporting->getInventoryReport($filter);
                    break;
                case 'performance':
                    $data = $this->model_extension_module_reporting->getPerformanceReport($filter);
                    break;
                case 'financial':
                    $data = $this->model_extension_module_reporting->getFinancialReport($filter);
                    break;
                default:
                    $data = $this->model_extension_module_reporting->getSalesReport($filter);
            }
            
            // Get updated statistics
            $stats = $this->model_extension_module_reporting->getDashboardStats($filter);
            
            $json['success'] = true;
            $json['data'] = $data;
            $json['stats'] = $stats;
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
            $this->writeLog('REPORT_ERROR', 'Report generation failed: ' . $e->getMessage());
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Export report data
     */
    public function exportReport() {
        $this->load->language('extension/module/reporting');
        $this->load->model('extension/module/reporting');
        
        try {
            $report_type = isset($this->request->get['report_type']) ? $this->request->get['report_type'] : 'sales';
            $date_start = isset($this->request->get['date_start']) ? $this->request->get['date_start'] : date('Y-m-01');
            $date_end = isset($this->request->get['date_end']) ? $this->request->get['date_end'] : date('Y-m-d');
            $marketplace = isset($this->request->get['marketplace']) ? $this->request->get['marketplace'] : 'all';
            
            $filter = array(
                'report_type' => $report_type,
                'date_start' => $date_start,
                'date_end' => $date_end,
                'marketplace' => $marketplace,
                'export' => true
            );
            
            $data = $this->model_extension_module_reporting->getExportData($filter);
            
            // Generate CSV
            $filename = 'meschain_report_' . $report_type . '_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            
            $output = fopen('php://output', 'w');
            
            if (!empty($data)) {
                // Write headers
                fputcsv($output, array_keys($data[0]));
                
                // Write data
                foreach ($data as $row) {
                    fputcsv($output, $row);
                }
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            $this->session->data['error'] = 'Export failed: ' . $e->getMessage();
            $this->response->redirect($this->url->link('extension/module/reporting', 'user_token=' . $this->session->data['user_token'], true));
        }
    }
    
    /**
     * Generate custom report
     */
    public function generateCustomReport() {
        $this->load->language('extension/module/reporting');
        $this->load->model('extension/module/reporting');
        
        $json = array();
        
        try {
            $report_data = array(
                'report_name' => $this->request->post['report_name'],
                'custom_type' => $this->request->post['custom_type'],
                'date_range' => $this->request->post['date_range'],
                'grouping' => $this->request->post['grouping'],
                'format' => $this->request->post['format'],
                'filters' => $this->request->post['filters']
            );
            
            $data = $this->model_extension_module_reporting->generateCustomReport($report_data);
            
            $json['success'] = true;
            $json['data'] = $data;
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get chart data for dashboard
     */
    private function getChartData($date_start, $date_end) {
        $this->load->model('extension/module/reporting');
        
        $filter = array(
            'date_start' => $date_start,
            'date_end' => $date_end
        );
        
        return array(
            'sales_chart' => $this->model_extension_module_reporting->getSalesChartData($filter),
            'marketplace_chart' => $this->model_extension_module_reporting->getMarketplaceChartData($filter),
            'performance_chart' => $this->model_extension_module_reporting->getPerformanceChartData($filter)
        );
    }
    
    /**
     * Get form data and render view
     */
    private function getForm($data = array()) {
        // Error handling
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/reporting', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // URLs
        $data['action'] = $this->url->link('extension/module/reporting', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        $data['ajax_report_url'] = $this->url->link('extension/module/reporting/generateReport', 'user_token=' . $this->session->data['user_token'], true);
        $data['ajax_custom_report_url'] = $this->url->link('extension/module/reporting/generateCustomReport', 'user_token=' . $this->session->data['user_token'], true);
        $data['export_url'] = $this->url->link('extension/module/reporting/exportReport', 'user_token=' . $this->session->data['user_token'], true);
        
        // Language variables
        $language_vars = array(
            'heading_title', 'text_edit', 'text_enabled', 'text_disabled',
            'button_save', 'button_cancel', 'button_generate', 'button_export',
            'text_sales_report', 'text_inventory_report', 'text_performance_report',
            'text_financial_report', 'text_marketplace', 'text_all_marketplaces'
        );
        
        foreach ($language_vars as $var) {
            $data[$var] = $this->language->get($var);
        }
        
        // Template rendering
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/reporting', $data));
    }
    
    /**
     * Validate form data
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/reporting')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        return !$this->error;
    }
    
    /**
     * Write log entry
     */
    private function writeLog($action, $message) {
        $log = new Log('meschain_reporting.log');
        $log->write('[' . $action . '] ' . $message);
    }
    
    /**
     * Install method
     */
    public function install() {
        $this->load->model('extension/module/reporting');
        $this->model_extension_module_reporting->install();
    }
    
    /**
     * Uninstall method
     */
    public function uninstall() {
        $this->load->model('extension/module/reporting');
        $this->model_extension_module_reporting->uninstall();
    }
}
?> 