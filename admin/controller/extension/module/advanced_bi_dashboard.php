<?php
/**
 * MesChain-Sync Advanced BI Dashboard Controller
 * 
 * ATOM-MZ008: Advanced Business Intelligence Engine
 * Developed by: MezBjen Team - Business Intelligence Specialist
 * Date: June 8, 2025
 * 
 * @package    MesChain-Sync Enterprise
 * @subpackage Business Intelligence Dashboard
 * @version    3.0.0
 * @author     MezBjen Development Team
 * @copyright  2025 MesTechSync Solutions
 * @license    Enterprise License
 */

class ControllerExtensionModuleAdvancedBiDashboard extends Controller {
    
    private $error = array();
    private $bi_engine;
    private $logger;
    
    /**
     * Initialize BI Dashboard Controller
     */
    public function __construct($registry) {
        parent::__construct($registry);
        
        // Load required models and libraries
        $this->load->model('extension/module/advanced_bi_dashboard');
        $this->load->language('extension/module/advanced_bi_dashboard');
        
        // Initialize BI Engine
        require_once(DIR_SYSTEM . 'library/meschain/intelligence/advanced_bi_engine_v3.php');
        $this->bi_engine = new \MesChain\Intelligence\AdvancedBIEngine($this->db, $this->config);
        
        // Initialize logger
        require_once(DIR_SYSTEM . 'library/meschain/logger/bi_logger.php');
        $this->logger = new \MesChain\Logger\BILogger('bi_dashboard');
    }
    
    /**
     * Main Dashboard Index
     */
    public function index() {
        try {
            $this->document->setTitle($this->language->get('heading_title'));
            
            // Check permissions
            if (!$this->user->hasPermission('access', 'extension/module/advanced_bi_dashboard')) {
                $this->session->data['error'] = $this->language->get('error_permission');
                $this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
            }
            
            $data = $this->getCommonData();
            
            // Get executive dashboard data
            $data['executive_dashboard'] = $this->bi_engine->generateExecutiveDashboard();
            $data['real_time_dashboard'] = $this->bi_engine->getRealTimeDashboard();
            $data['business_insights'] = $this->bi_engine->generateBusinessInsights();
            $data['engine_status'] = $this->bi_engine->getEngineStatus();
            
            // Performance metrics
            $data['performance_metrics'] = $this->bi_engine->getPerformanceMetrics();
            
            $this->logger->info('BI Dashboard accessed successfully', [
                'user_id' => $this->user->getId(),
                'dashboard_load_time' => $data['performance_metrics']['dashboard_load_time']
            ]);
            
            $this->response->setOutput($this->load->view('extension/module/advanced_bi_dashboard', $data));
            
        } catch (\Exception $e) {
            $this->logger->error('BI Dashboard access failed: ' . $e->getMessage());
            $this->session->data['error'] = 'BI Dashboard yüklenirken hata oluştu: ' . $e->getMessage();
            $this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
    }
    
    /**
     * Executive Dashboard API
     */
    public function getExecutiveDashboard() {
        try {
            $this->checkAjaxPermission();
            
            $dashboard_data = $this->bi_engine->generateExecutiveDashboard();
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'data' => $dashboard_data,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('Executive dashboard API failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Real-time Analytics API
     */
    public function getRealTimeAnalytics() {
        try {
            $this->checkAjaxPermission();
            
            $real_time_data = $this->bi_engine->getRealTimeDashboard();
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'data' => $real_time_data,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('Real-time analytics API failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Advanced Analytics Query API
     */
    public function performAdvancedAnalytics() {
        try {
            $this->checkAjaxPermission();
            
            $query_type = $this->request->post['query_type'] ?? '';
            $parameters = $this->request->post['parameters'] ?? [];
            
            if (empty($query_type)) {
                throw new \InvalidArgumentException('Query type is required');
            }
            
            $result = $this->bi_engine->performAdvancedAnalytics($query_type, $parameters);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'query_type' => $query_type,
                'data' => $result,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('Advanced analytics query failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Business Insights API
     */
    public function getBusinessInsights() {
        try {
            $this->checkAjaxPermission();
            
            $insights = $this->bi_engine->generateBusinessInsights();
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'data' => $insights,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('Business insights API failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Export Analytics Data
     */
    public function exportAnalytics() {
        try {
            $this->checkPermission();
            
            $format = $this->request->get['format'] ?? 'json';
            $data_type = $this->request->get['data_type'] ?? 'executive_dashboard';
            
            // Get data based on type
            switch ($data_type) {
                case 'executive_dashboard':
                    $data = $this->bi_engine->generateExecutiveDashboard();
                    break;
                case 'business_insights':
                    $data = $this->bi_engine->generateBusinessInsights();
                    break;
                case 'real_time_analytics':
                    $data = $this->bi_engine->getRealTimeDashboard();
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid data type: ' . $data_type);
            }
            
            $file_path = $this->bi_engine->exportAnalyticsData($format, $data);
            
            // Download file
            $this->downloadFile($file_path, basename($file_path));
            
        } catch (\Exception $e) {
            $this->logger->error('Analytics export failed: ' . $e->getMessage());
            $this->session->data['error'] = 'Export işlemi başarısız: ' . $e->getMessage();
            $this->response->redirect($this->url->link('extension/module/advanced_bi_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
    }
    
    /**
     * BI Engine Configuration
     */
    public function configure() {
        try {
            $this->document->setTitle($this->language->get('heading_title_config'));
            
            $this->checkPermission();
            
            if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateConfiguration()) {
                $this->model_extension_module_advanced_bi_dashboard->updateConfiguration($this->request->post);
                
                $this->session->data['success'] = $this->language->get('text_success_config');
                $this->response->redirect($this->url->link('extension/module/advanced_bi_dashboard', 'user_token=' . $this->session->data['user_token'], true));
            }
            
            $data = $this->getCommonData();
            $data['configuration'] = $this->model_extension_module_advanced_bi_dashboard->getConfiguration();
            $data['engine_status'] = $this->bi_engine->getEngineStatus();
            
            $this->response->setOutput($this->load->view('extension/module/advanced_bi_dashboard_config', $data));
            
        } catch (\Exception $e) {
            $this->logger->error('BI configuration failed: ' . $e->getMessage());
            $this->session->data['error'] = 'Konfigürasyon hatası: ' . $e->getMessage();
            $this->response->redirect($this->url->link('extension/module/advanced_bi_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
    }
    
    /**
     * Analytics Reports
     */
    public function reports() {
        try {
            $this->document->setTitle($this->language->get('heading_title_reports'));
            
            $this->checkPermission();
            
            $data = $this->getCommonData();
            
            // Get available reports
            $data['available_reports'] = $this->getAvailableReports();
            $data['recent_reports'] = $this->model_extension_module_advanced_bi_dashboard->getRecentReports();
            $data['scheduled_reports'] = $this->model_extension_module_advanced_bi_dashboard->getScheduledReports();
            
            $this->response->setOutput($this->load->view('extension/module/advanced_bi_dashboard_reports', $data));
            
        } catch (\Exception $e) {
            $this->logger->error('BI reports access failed: ' . $e->getMessage());
            $this->session->data['error'] = 'Raporlar yüklenirken hata oluştu: ' . $e->getMessage();
            $this->response->redirect($this->url->link('extension/module/advanced_bi_dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
    }
    
    /**
     * Generate Custom Report
     */
    public function generateReport() {
        try {
            $this->checkAjaxPermission();
            
            $report_config = $this->request->post;
            
            if (empty($report_config['report_type'])) {
                throw new \InvalidArgumentException('Report type is required');
            }
            
            $report_data = $this->generateCustomReport($report_config);
            
            // Save report
            $report_id = $this->model_extension_module_advanced_bi_dashboard->saveReport($report_data);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'report_id' => $report_id,
                'data' => $report_data,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('Report generation failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * AI Recommendations API
     */
    public function getAIRecommendations() {
        try {
            $this->checkAjaxPermission();
            
            $category = $this->request->get['category'] ?? 'all';
            $recommendations = $this->bi_engine->getAIRecommendations();
            
            if ($category !== 'all' && isset($recommendations[$category])) {
                $recommendations = [$category => $recommendations[$category]];
            }
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'category' => $category,
                'recommendations' => $recommendations,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('AI recommendations API failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Performance Monitoring API
     */
    public function getPerformanceMetrics() {
        try {
            $this->checkAjaxPermission();
            
            $metrics = $this->bi_engine->getPerformanceMetrics();
            $engine_status = $this->bi_engine->getEngineStatus();
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'performance_metrics' => $metrics,
                'engine_status' => $engine_status,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('Performance metrics API failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Validate Configuration
     */
    private function validateConfiguration() {
        $this->error = array();
        
        if (!$this->user->hasPermission('modify', 'extension/module/advanced_bi_dashboard')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        // Validate configuration parameters
        if (empty($this->request->post['bi_engine_status'])) {
            $this->error['bi_engine_status'] = $this->language->get('error_bi_engine_status');
        }
        
        return !$this->error;
    }
    
    /**
     * Get Available Reports
     */
    private function getAvailableReports() {
        return [
            'executive_summary' => [
                'name' => 'Executive Summary Report',
                'description' => 'Comprehensive executive dashboard summary',
                'category' => 'executive'
            ],
            'sales_performance' => [
                'name' => 'Sales Performance Report',
                'description' => 'Detailed sales analytics and trends',
                'category' => 'sales'
            ],
            'customer_insights' => [
                'name' => 'Customer Insights Report',
                'description' => 'Customer behavior and segmentation analysis',
                'category' => 'customer'
            ],
            'market_analysis' => [
                'name' => 'Market Analysis Report',
                'description' => 'Marketplace performance and competitive analysis',
                'category' => 'market'
            ],
            'predictive_forecast' => [
                'name' => 'Predictive Forecast Report',
                'description' => 'AI-powered predictions and forecasts',
                'category' => 'predictive'
            ],
            'operational_efficiency' => [
                'name' => 'Operational Efficiency Report',
                'description' => 'Operational metrics and efficiency analysis',
                'category' => 'operations'
            ]
        ];
    }
    
    /**
     * Generate Custom Report
     */
    private function generateCustomReport($config) {
        $report_type = $config['report_type'];
        $parameters = $config['parameters'] ?? [];
        
        switch ($report_type) {
            case 'executive_summary':
                return $this->bi_engine->generateExecutiveDashboard();
                
            case 'sales_performance':
                return $this->bi_engine->performAdvancedAnalytics('sales_analysis', $parameters);
                
            case 'customer_insights':
                return $this->bi_engine->performAdvancedAnalytics('customer_analysis', $parameters);
                
            case 'market_analysis':
                return $this->bi_engine->performAdvancedAnalytics('market_analysis', $parameters);
                
            case 'predictive_forecast':
                return $this->bi_engine->performAdvancedAnalytics('predictive_analysis', $parameters);
                
            case 'operational_efficiency':
                return $this->bi_engine->performAdvancedAnalytics('operational_analysis', $parameters);
                
            default:
                throw new \InvalidArgumentException('Unknown report type: ' . $report_type);
        }
    }
    
    /**
     * Check AJAX Permission
     */
    private function checkAjaxPermission() {
        if (!$this->user->hasPermission('access', 'extension/module/advanced_bi_dashboard')) {
            throw new \Exception('Access denied');
        }
    }
    
    /**
     * Check Permission
     */
    private function checkPermission() {
        if (!$this->user->hasPermission('access', 'extension/module/advanced_bi_dashboard')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
    }
    
    /**
     * Get Common Template Data
     */
    private function getCommonData() {
        $data = array();
        
        // Language variables
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        
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
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/advanced_bi_dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // URLs
        $data['action'] = $this->url->link('extension/module/advanced_bi_dashboard', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);
        
        // API URLs
        $data['api_executive_dashboard'] = $this->url->link('extension/module/advanced_bi_dashboard/getExecutiveDashboard', 'user_token=' . $this->session->data['user_token'], true);
        $data['api_real_time_analytics'] = $this->url->link('extension/module/advanced_bi_dashboard/getRealTimeAnalytics', 'user_token=' . $this->session->data['user_token'], true);
        $data['api_business_insights'] = $this->url->link('extension/module/advanced_bi_dashboard/getBusinessInsights', 'user_token=' . $this->session->data['user_token'], true);
        $data['api_ai_recommendations'] = $this->url->link('extension/module/advanced_bi_dashboard/getAIRecommendations', 'user_token=' . $this->session->data['user_token'], true);
        $data['api_performance_metrics'] = $this->url->link('extension/module/advanced_bi_dashboard/getPerformanceMetrics', 'user_token=' . $this->session->data['user_token'], true);
        
        // Template data
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        return $data;
    }
    
    /**
     * Download File
     */
    private function downloadFile($file_path, $filename) {
        if (file_exists($file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        } else {
            throw new \Exception('File not found: ' . $file_path);
        }
    }
}

?> 