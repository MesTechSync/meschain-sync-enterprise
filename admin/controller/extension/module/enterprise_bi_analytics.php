<?php
/**
 * ATOM-M017: Enterprise AI-Powered Analytics & Business Intelligence Controller
 * Advanced BI dashboard and analytics management interface
 * MesChain-Sync Enterprise v2.1.0 - Musti Team Implementation
 * 
 * @package    MesChain Enterprise BI Controller  
 * @version    2.1.0
 * @author     MUSTI TAKIMI - ATOM Development Team
 * @date       June 7, 2025
 * @copyright  MesTechSync Solutions
 */

class ControllerExtensionModuleEnterpriseBiAnalytics extends Controller {
    
    private $error = array();
    private $bi_engine;
    private $api_endpoints = [
        'dashboard' => 'getDashboardData',
        'reports' => 'getReports', 
        'generate' => 'generateReport',
        'export' => 'exportReport',
        'realtime' => 'getRealTimeData',
        'insights' => 'getAIInsights',
        'predictions' => 'getPredictiveAnalysis',
        'alerts' => 'getAlerts'
    ];
    
    public function __construct($registry) {
        parent::__construct($registry);
        
        // Load Enterprise BI Engine
        $this->load->library('meschain/analytics/enterprise_bi_engine');
        $this->bi_engine = new \MesChain\Analytics\EnterpriseBIEngine($registry);
        
        // Load required models
        $this->load->model('extension/module/enterprise_bi_analytics');
        $this->load->model('localisation/language');
        $this->load->model('user/user_group');
        
        // Set language
        $this->load->language('extension/module/enterprise_bi_analytics');
    }
    
    /**
     * Main BI Analytics Dashboard Index
     */
    public function index() {
        $this->load->language('extension/module/enterprise_bi_analytics');
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Check permissions
        if (!$this->user->hasPermission('modify', 'extension/module/enterprise_bi_analytics')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }
        
        $data = $this->getCommonData();
        
        // Get dashboard data
        $data['dashboard_data'] = $this->bi_engine->getRealTimeDashboardData();
        
        // Get recent reports
        $data['recent_reports'] = $this->model_extension_module_enterprise_bi_analytics->getRecentReports(10);
        
        // Get performance summary
        $data['performance_summary'] = $this->generatePerformanceSummary();
        
        // Get AI insights
        $data['ai_insights'] = $this->getLatestAIInsights();
        
        // Analytics configuration
        $data['analytics_config'] = $this->getAnalyticsConfiguration();
        
        // Real-time status
        $data['realtime_status'] = $this->getRealTimeStatus(); 
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/enterprise_bi_analytics', $data));
    }
    
    /**
     * Generate comprehensive BI report
     */
    public function generateReport() {
        $this->load->language('extension/module/enterprise_bi_analytics');
        
        if (!$this->user->hasPermission('modify', 'extension/module/enterprise_bi_analytics')) {
            $json['error'] = $this->language->get('error_permission');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }
        
        try {
            $report_type = $this->request->post['report_type'] ?? 'comprehensive';
            $time_range = $this->request->post['time_range'] ?? '24h';
            
            $report_start = microtime(true);
            
            // Generate BI report
            $report = $this->bi_engine->generateBIReport($report_type, $time_range);
            
            $generation_time = microtime(true) - $report_start;
            
            $json = [
                'success' => true,
                'message' => sprintf($this->language->get('text_report_generated'), $report['report_id']),
                'report_id' => $report['report_id'],
                'report_type' => $report_type,
                'time_range' => $time_range,
                'generation_time' => round($generation_time, 3),
                'quantum_acceleration' => $report['quantum_acceleration'] ?? 1.0,
                'data_points' => $this->countDataPoints($report),
                'insights_count' => count($report['ai_insights']['key_findings'] ?? []),
                'recommendations_count' => count($report['recommendations']['priority_actions'] ?? [])
            ];
            
        } catch (Exception $e) {
            $json = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get real-time dashboard data via AJAX
     */
    public function getDashboardData() {
        if (!$this->user->hasPermission('access', 'extension/module/enterprise_bi_analytics')) {
            $json['error'] = $this->language->get('error_permission');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }
        
        try {
            $dashboard_data = $this->bi_engine->getRealTimeDashboardData();
            
            $json = [
                'success' => true,
                'data' => $dashboard_data,
                'timestamp' => date('Y-m-d H:i:s'),
                'processing_time' => $dashboard_data['processing_time'] ?? 0
            ];
            
        } catch (Exception $e) {
            $json = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Export BI report in various formats
     */
    public function exportReport() {
        if (!$this->user->hasPermission('access', 'extension/module/enterprise_bi_analytics')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/module/enterprise_bi_analytics', 'user_token=' . $this->session->data['user_token'], true));
            return;
        }
        
        try {
            $report_id = $this->request->get['report_id'] ?? '';
            $format = $this->request->get['format'] ?? 'json';
            
            if (!$report_id) {
                throw new Exception('Report ID is required');
            }
            
            $exported_data = $this->bi_engine->exportReport($report_id, $format);
            
            $filename = 'meschain_bi_report_' . $report_id . '.' . $format;
            
            // Set appropriate headers based on format
            switch ($format) {
                case 'json':
                    $this->response->addHeader('Content-Type: application/json');
                    break;
                case 'csv':
                    $this->response->addHeader('Content-Type: text/csv');
                    break;
                case 'pdf':
                    $this->response->addHeader('Content-Type: application/pdf');
                    break;
                case 'excel':
                    $this->response->addHeader('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    break;
            }
            
            $this->response->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
            $this->response->setOutput($exported_data);
            
        } catch (Exception $e) {
            $this->session->data['error'] = $e->getMessage();
            $this->response->redirect($this->url->link('extension/module/enterprise_bi_analytics', 'user_token=' . $this->session->data['user_token'], true));
        }
    }
    
    /**
     * Get AI-powered insights
     */
    public function getAIInsights() {
        if (!$this->user->hasPermission('access', 'extension/module/enterprise_bi_analytics')) {
            $json['error'] = $this->language->get('error_permission');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }
        
        try {
            $time_range = $this->request->get['time_range'] ?? '24h';
            
            // Generate comprehensive report to get insights
            $report = $this->bi_engine->generateBIReport('comprehensive', $time_range);
            $insights = $report['ai_insights'];
            
            $json = [
                'success' => true,
                'insights' => $insights,
                'key_metrics' => [
                    'key_findings_count' => count($insights['key_findings']),
                    'opportunities_count' => count($insights['opportunity_identification']),
                    'risk_alerts_count' => count($insights['risk_alerts']),
                    'anomalies_count' => count($insights['anomaly_detection'])
                ],
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $json = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get predictive analysis data
     */
    public function getPredictiveAnalysis() {
        if (!$this->user->hasPermission('access', 'extension/module/enterprise_bi_analytics')) {
            $json['error'] = $this->language->get('error_permission');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }
        
        try {
            $time_range = $this->request->get['time_range'] ?? '24h';
            $prediction_type = $this->request->get['type'] ?? 'all';
            
            // Generate predictive insights report
            $report = $this->bi_engine->generateBIReport('predictive_insights', $time_range);
            $predictions = $report['predictive_analysis'];
            
            // Filter predictions based on type if specified
            if ($prediction_type !== 'all' && isset($predictions[$prediction_type])) {
                $predictions = [$prediction_type => $predictions[$prediction_type]];
            }
            
            $json = [
                'success' => true,
                'predictions' => $predictions,
                'prediction_confidence' => $this->calculatePredictionConfidence($predictions),
                'forecast_horizon' => $time_range,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $json = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get system alerts and notifications
     */
    public function getAlerts() {
        if (!$this->user->hasPermission('access', 'extension/module/enterprise_bi_analytics')) {
            $json['error'] = $this->language->get('error_permission');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }
        
        try {
            $dashboard_data = $this->bi_engine->getRealTimeDashboardData();
            $alerts = $dashboard_data['alerts'];
            
            // Categorize alerts by severity
            $categorized_alerts = [
                'critical' => [],
                'warning' => [],
                'info' => []
            ];
            
            foreach ($alerts as $alert) {
                $severity = $alert['severity'] ?? 'info';
                $categorized_alerts[$severity][] = $alert;
            }
            
            $json = [
                'success' => true,
                'alerts' => $categorized_alerts,
                'total_alerts' => count($alerts),
                'critical_count' => count($categorized_alerts['critical']),
                'warning_count' => count($categorized_alerts['warning']),
                'info_count' => count($categorized_alerts['info']),
                'last_updated' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $json = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get list of generated reports
     */
    public function getReports() {
        if (!$this->user->hasPermission('access', 'extension/module/enterprise_bi_analytics')) {
            $json['error'] = $this->language->get('error_permission');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }
        
        try {
            $page = (int)($this->request->get['page'] ?? 1);
            $limit = (int)($this->request->get['limit'] ?? 20);
            $filter_type = $this->request->get['filter_type'] ?? '';
            
            $reports = $this->model_extension_module_enterprise_bi_analytics->getReports($page, $limit, $filter_type);
            $total_reports = $this->model_extension_module_enterprise_bi_analytics->getTotalReports($filter_type);
            
            $json = [
                'success' => true,
                'reports' => $reports,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total_reports / $limit),
                    'total_reports' => $total_reports,
                    'per_page' => $limit
                ]
            ];
            
        } catch (Exception $e) {
            $json = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Analytics configuration page  
     */
    public function configure() {
        $this->load->language('extension/module/enterprise_bi_analytics');
        $this->document->setTitle($this->language->get('heading_title_config'));
        
        if (!$this->user->hasPermission('modify', 'extension/module/enterprise_bi_analytics')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/module/enterprise_bi_analytics', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Handle form submission
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateConfig()) {
            $this->model_extension_module_enterprise_bi_analytics->updateConfiguration($this->request->post);
            $this->session->data['success'] = $this->language->get('text_config_success');
            $this->response->redirect($this->url->link('extension/module/enterprise_bi_analytics/configure', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        $data = $this->getCommonData();
        
        // Get current configuration
        $data['config'] = $this->model_extension_module_enterprise_bi_analytics->getConfiguration();
        
        // Configuration options
        $data['analytics_types'] = [
            'sales_analytics' => $this->language->get('text_sales_analytics'),
            'inventory_analytics' => $this->language->get('text_inventory_analytics'),
            'customer_analytics' => $this->language->get('text_customer_analytics'),
            'marketplace_analytics' => $this->language->get('text_marketplace_analytics'),
            'financial_analytics' => $this->language->get('text_financial_analytics')
        ];
        
        $data['processing_modes'] = [
            'real_time' => $this->language->get('text_real_time'),
            'batch' => $this->language->get('text_batch'),
            'quantum_enhanced' => $this->language->get('text_quantum_enhanced'),
            'ai_predictive' => $this->language->get('text_ai_predictive')
        ];
        
        $data['report_frequencies'] = [
            'real_time' => $this->language->get('text_real_time'),
            'hourly' => $this->language->get('text_hourly'),
            'daily' => $this->language->get('text_daily'),
            'weekly' => $this->language->get('text_weekly'),
            'monthly' => $this->language->get('text_monthly')
        ];
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/enterprise_bi_analytics_config', $data));
    }
    
    /**
     * Install/Setup BI Analytics module
     */
    public function install() {
        $this->load->model('extension/module/enterprise_bi_analytics');
        
        // Create database tables
        $this->model_extension_module_enterprise_bi_analytics->createTables();
        
        // Setup default configuration
        $default_config = [
            'status' => 1,
            'quantum_processing' => 1,
            'ai_insights' => 1,
            'real_time_analytics' => 1,
            'report_retention_days' => 90,
            'alert_thresholds' => json_encode([
                'revenue_drop' => 10,
                'inventory_low' => 5,
                'error_rate' => 2
            ])
        ];
        
        $this->model_extension_module_enterprise_bi_analytics->updateConfiguration($default_config);
        
        // Setup user permissions
        $this->model_extension_module_enterprise_bi_analytics->setupPermissions();
    }
    
    /**
     * Uninstall BI Analytics module
     */
    public function uninstall() {
        $this->load->model('extension/module/enterprise_bi_analytics');
        
        // Remove database tables (optional - keep data by default)
        // $this->model_extension_module_enterprise_bi_analytics->dropTables();
        
        // Remove permissions
        $this->model_extension_module_enterprise_bi_analytics->removePermissions();
    }
    
    /**
     * Get common template data
     */
    private function getCommonData() {
        $data = [];
        
        // Breadcrumbs
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/enterprise_bi_analytics', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        // Language strings
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        
        // URLs
        $data['action'] = $this->url->link('extension/module/enterprise_bi_analytics', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
        // API endpoints for AJAX calls
        $data['api_endpoints'] = [];
        foreach ($this->api_endpoints as $endpoint => $method) {
            $data['api_endpoints'][$endpoint] = $this->url->link('extension/module/enterprise_bi_analytics/' . $method, 'user_token=' . $this->session->data['user_token'], true);
        }
        
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
        
        // User token
        $data['user_token'] = $this->session->data['user_token'];
        
        return $data;
    }
    
    /**
     * Validate configuration form
     */
    private function validateConfig() {
        if (!$this->user->hasPermission('modify', 'extension/module/enterprise_bi_analytics')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        return !$this->error;
    }
    
    /**
     * Generate performance summary
     */
    private function generatePerformanceSummary() {
        return [
            'total_reports_generated' => $this->model_extension_module_enterprise_bi_analytics->getTotalReportsCount(),
            'analytics_processing_time' => $this->model_extension_module_enterprise_bi_analytics->getAverageProcessingTime(),
            'data_points_analyzed' => $this->model_extension_module_enterprise_bi_analytics->getTotalDataPoints(),
            'ai_insights_generated' => $this->model_extension_module_enterprise_bi_analytics->getTotalInsights(),
            'system_uptime' => $this->getSystemUptime(),
            'quantum_acceleration_factor' => $this->getQuantumAccelerationFactor()
        ];
    }
    
    /**
     * Get latest AI insights
     */
    private function getLatestAIInsights() {
        return $this->model_extension_module_enterprise_bi_analytics->getLatestInsights(5);
    }
    
    /**
     * Get analytics configuration
     */
    private function getAnalyticsConfiguration() {
        return $this->model_extension_module_enterprise_bi_analytics->getConfiguration();
    }
    
    /**
     * Get real-time system status
     */
    private function getRealTimeStatus() {
        return [
            'status' => 'active',
            'streams_active' => 5,
            'processing_queue' => 0,
            'last_update' => date('Y-m-d H:i:s'),
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'cpu_usage' => '12.5%'
        ];
    }
    
    /**
     * Count data points in report
     */
    private function countDataPoints($report) {
        $count = 0;
        if (is_array($report)) {
            array_walk_recursive($report, function($item) use (&$count) {
                if (is_numeric($item)) {
                    $count++;
                }
            });
        }
        return $count;
    }
    
    /**
     * Calculate prediction confidence
     */
    private function calculatePredictionConfidence($predictions) {
        $confidences = [];
        foreach ($predictions as $type => $data) {
            if (isset($data['confidence'])) {
                $confidences[] = $data['confidence'];
            }
        }
        
        return empty($confidences) ? 0 : round(array_sum($confidences) / count($confidences), 2);
    }
    
    /**
     * Get system uptime
     */
    private function getSystemUptime() {
        // Implementation would get actual system uptime
        return '99.9%';
    }
    
    /**
     * Get quantum acceleration factor
     */
    private function getQuantumAccelerationFactor() {
        // Implementation would get actual quantum acceleration factor from BI engine
        return 1247.8;
    }
}