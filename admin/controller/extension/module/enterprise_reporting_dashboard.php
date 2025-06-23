<?php
/**
 * MesChain Enterprise Reporting Dashboard Controller
 * ATOM-M010: Advanced Enterprise Features - Reporting Dashboard
 * 
 * @category    MesChain
 * @package     Admin
 * @subpackage  Controller
 * @version     1.0.0
 * @author      Musti DevOps Team
 * @copyright   2024 MesChain Sync Enterprise
 */

class ControllerExtensionModuleEnterpriseReportingDashboard extends Controller {
    
    private $error = array();
    private $reporting_engine;
    
    public function __construct($registry) {
        parent::__construct($registry);
        
        // Load dependencies
        $this->load->model('extension/module/enterprise_reporting_dashboard');
        $this->load->language('extension/module/enterprise_reporting_dashboard');
        
        // Initialize reporting engine
        require_once(DIR_SYSTEM . 'library/meschain/enterprise/advanced_reporting_engine.php');
        $this->reporting_engine = new \MesChain\Enterprise\AdvancedReportingEngine($registry);
    }
    
    /**
     * Main Dashboard Index
     */
    public function index() {
        $this->load->language('extension/module/enterprise_reporting_dashboard');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Handle form submission
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_extension_module_enterprise_reporting_dashboard->editSettings($this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module/enterprise_reporting_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Prepare template data
        $data = $this->prepareTemplateData();
        
        // Get dashboard data
        $data['dashboard_data'] = $this->getDashboardData();
        $data['reports_overview'] = $this->getReportsOverview();
        $data['system_status'] = $this->reporting_engine->getEngineStatus();
        
        // Render template
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/enterprise_reporting_dashboard', $data));
    }
    
    /**
     * Generate Business Intelligence Report
     */
    public function generateBIReport() {
        $this->load->language('extension/module/enterprise_reporting_dashboard');
        
        $json = array();
        
        try {
            if (!isset($this->request->post['report_config'])) {
                throw new Exception('Report configuration required');
            }
            
            $report_config = json_decode($this->request->post['report_config'], true);
            $report = $this->reporting_engine->generateBIReport($report_config);
            
            $json['success'] = true;
            $json['report'] = $report;
            $json['message'] = $this->language->get('text_report_generated');
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Real-time Dashboard Data API
     */
    public function getRealTimeDashboardData() {
        $json = array();
        
        try {
            $dashboard_id = isset($this->request->get['dashboard_id']) ? 
                           (int)$this->request->get['dashboard_id'] : 1;
            
            $data = $this->reporting_engine->getRealTimeDashboardData($dashboard_id);
            
            $json['success'] = true;
            $json['data'] = $data;
            $json['timestamp'] = date('Y-m-d H:i:s');
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Export Report to Various Formats
     */
    public function exportReport() {
        try {
            $report_id = isset($this->request->post['report_id']) ? 
                        (int)$this->request->post['report_id'] : 0;
            $format = isset($this->request->post['format']) ? 
                     $this->request->post['format'] : 'pdf';
            $options = isset($this->request->post['options']) ? 
                      json_decode($this->request->post['options'], true) : [];
            
            if (!$report_id) {
                throw new Exception('Report ID required');
            }
            
            // Get report data
            $report_data = $this->model_extension_module_enterprise_reporting_dashboard->getReportData($report_id);
            
            // Export report
            $export_result = $this->reporting_engine->exportReport($report_data, $format, $options);
            
            // Set appropriate headers for download
            $this->setDownloadHeaders($format, $export_result['filename']);
            $this->response->setOutput($export_result['content']);
            
        } catch (Exception $e) {
            $this->session->data['error'] = $e->getMessage();
            $this->response->redirect($this->url->link('extension/module/enterprise_reporting_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
    }
    
    /**
     * Schedule Report
     */
    public function scheduleReport() {
        $this->load->language('extension/module/enterprise_reporting_dashboard');
        
        $json = array();
        
        try {
            if (!isset($this->request->post['report_id']) || !isset($this->request->post['schedule_config'])) {
                throw new Exception('Report ID and schedule configuration required');
            }
            
            $report_id = (int)$this->request->post['report_id'];
            $schedule_config = json_decode($this->request->post['schedule_config'], true);
            
            $schedule_id = $this->reporting_engine->scheduleReport($report_id, $schedule_config);
            
            $json['success'] = true;
            $json['schedule_id'] = $schedule_id;
            $json['message'] = $this->language->get('text_report_scheduled');
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Optimize Report Performance
     */
    public function optimizeReport() {
        $json = array();
        
        try {
            $report_id = isset($this->request->post['report_id']) ? 
                        (int)$this->request->post['report_id'] : 0;
            
            if (!$report_id) {
                throw new Exception('Report ID required');
            }
            
            $optimization_result = $this->reporting_engine->optimizeReportPerformance($report_id);
            
            $json['success'] = true;
            $json['optimization'] = $optimization_result;
            $json['message'] = 'Report optimized successfully';
            
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get Dashboard Data
     */
    private function getDashboardData() {
        return [
            'total_reports' => $this->model_extension_module_enterprise_reporting_dashboard->getTotalReports(),
            'active_reports' => $this->model_extension_module_enterprise_reporting_dashboard->getActiveReports(),
            'reports_today' => $this->model_extension_module_enterprise_reporting_dashboard->getReportsToday(),
            'performance_metrics' => $this->model_extension_module_enterprise_reporting_dashboard->getPerformanceMetrics(),
            'recent_reports' => $this->model_extension_module_enterprise_reporting_dashboard->getRecentReports(10),
            'top_reports' => $this->model_extension_module_enterprise_reporting_dashboard->getTopReports(5)
        ];
    }
    
    /**
     * Get Reports Overview
     */
    private function getReportsOverview() {
        return [
            'financial_reports' => $this->model_extension_module_enterprise_reporting_dashboard->getReportsByType('financial'),
            'operational_reports' => $this->model_extension_module_enterprise_reporting_dashboard->getReportsByType('operational'),
            'performance_reports' => $this->model_extension_module_enterprise_reporting_dashboard->getReportsByType('performance'),
            'compliance_reports' => $this->model_extension_module_enterprise_reporting_dashboard->getReportsByType('compliance'),
            'custom_reports' => $this->model_extension_module_enterprise_reporting_dashboard->getReportsByType('custom')
        ];
    }
    
    /**
     * Prepare Template Data
     */
    private function prepareTemplateData() {
        $data = array();
        
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
            'href' => $this->url->link('extension/module/enterprise_reporting_dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Basic data
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['user_token'] = $this->session->data['user_token'];
        
        // Action URLs
        $data['action'] = $this->url->link('extension/module/enterprise_reporting_dashboard', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        // API URLs
        $data['url_generate_report'] = $this->url->link('extension/module/enterprise_reporting_dashboard/generateBIReport', 'user_token=' . $this->session->data['user_token'], true);
        $data['url_realtime_data'] = $this->url->link('extension/module/enterprise_reporting_dashboard/getRealTimeDashboardData', 'user_token=' . $this->session->data['user_token'], true);
        $data['url_export_report'] = $this->url->link('extension/module/enterprise_reporting_dashboard/exportReport', 'user_token=' . $this->session->data['user_token'], true);
        $data['url_schedule_report'] = $this->url->link('extension/module/enterprise_reporting_dashboard/scheduleReport', 'user_token=' . $this->session->data['user_token'], true);
        $data['url_optimize_report'] = $this->url->link('extension/module/enterprise_reporting_dashboard/optimizeReport', 'user_token=' . $this->session->data['user_token'], true);
        
        // Error handling
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        // Success message
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        return $data;
    }
    
    /**
     * Set Download Headers
     */
    private function setDownloadHeaders($format, $filename) {
        $mime_types = [
            'pdf' => 'application/pdf',
            'excel' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            'json' => 'application/json'
        ];
        
        $mime_type = isset($mime_types[$format]) ? $mime_types[$format] : 'application/octet-stream';
        
        $this->response->addHeader('Content-Type: ' . $mime_type);
        $this->response->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
        $this->response->addHeader('Cache-Control: must-revalidate');
        $this->response->addHeader('Pragma: public');
    }
    
    /**
     * Validate Form Data
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/enterprise_reporting_dashboard')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        return !$this->error;
    }
} 