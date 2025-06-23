<?php
/**
 * MesChain API Management Controller
 * Central management interface for all API components
 * 
 * @version 1.0.0
 * @date June 2, 2025
 * @author MesChain Development Team
 */

class ControllerExtensionModuleMeschainApiManagement extends Controller {
    
    private $api_service;
    private $test_suite;
    private $error = array();
    
    public function __construct($registry) {
        parent::__construct($registry);
        
        // Load API components
        $this->loadApiComponents();
    }
    
    /**
     * Load API service components
     */
    private function loadApiComponents() {
        // Load API integration service
        if (file_exists(DIR_SYSTEM . 'library/meschain/api_integration_service.php')) {
            require_once(DIR_SYSTEM . 'library/meschain/api_integration_service.php');
            $this->api_service = new MeschainApiIntegrationService($this->db, [
                'enable_logging' => true,
                'enable_caching' => true,
                'enable_rate_limiting' => true,
                'enable_metrics' => true
            ]);
        }
        
        // Load test suite
        if (file_exists(DIR_SYSTEM . 'library/meschain/api_test_suite.php')) {
            require_once(DIR_SYSTEM . 'library/meschain/api_test_suite.php');
            $this->test_suite = new MeschainApiTestSuite($this->db);
        }
    }
    
    /**
     * Main API management dashboard
     */
    public function index() {
        $this->load->language('extension/module/meschain_sync');
        
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => 'API Management',
            'href' => $this->url->link('extension/module/meschain_api_management', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Get API health status
        $data['api_health'] = $this->api_service ? $this->api_service->getHealthStatus() : null;
        
        // Get API statistics
        $data['api_stats'] = $this->api_service ? $this->api_service->getApiStatistics(7) : null;
        
        // Get recent API logs
        $data['recent_logs'] = $this->getRecentApiLogs();
        
        // URLs for AJAX requests
        $data['health_check_url'] = $this->url->link('extension/module/meschain_api_management/health', 'user_token=' . $this->session->data['user_token'], true);
        $data['test_suite_url'] = $this->url->link('extension/module/meschain_api_management/test', 'user_token=' . $this->session->data['user_token'], true);
        $data['documentation_url'] = $this->url->link('extension/module/meschain_api_management/documentation', 'user_token=' . $this->session->data['user_token'], true);
        $data['logs_url'] = $this->url->link('extension/module/meschain_api_management/logs', 'user_token=' . $this->session->data['user_token'], true);
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_api_management', $data));
    }
    
    /**
     * API health check endpoint
     */
    public function health() {
        $this->response->addHeader('Content-Type: application/json');
        
        if (!$this->api_service) {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'message' => 'API service not available'
            ]));
            return;
        }
        
        $health_status = $this->api_service->getHealthStatus();
        $this->response->setOutput(json_encode($health_status));
    }
    
    /**
     * Run API test suite
     */
    public function test() {
        $this->response->addHeader('Content-Type: application/json');
        
        if (!$this->test_suite) {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'message' => 'Test suite not available'
            ]));
            return;
        }
        
        try {
            $test_results = $this->test_suite->runTestSuite();
            $this->response->setOutput(json_encode([
                'status' => 'success',
                'data' => $test_results
            ]));
        } catch (Exception $e) {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'message' => 'Test suite failed: ' . $e->getMessage()
            ]));
        }
    }
    
    /**
     * Generate and display API documentation
     */
    public function documentation() {
        if (!$this->test_suite) {
            $this->response->setOutput('API documentation not available');
            return;
        }
        
        $format = $this->request->get['format'] ?? 'html';
        
        if ($format === 'json') {
            $this->response->addHeader('Content-Type: application/json');
            $documentation = $this->test_suite->generateDocumentation();
            $this->response->setOutput(json_encode($documentation, JSON_PRETTY_PRINT));
        } else {
            $this->response->addHeader('Content-Type: text/html');
            $html_documentation = $this->test_suite->generateHtmlDocumentation();
            $this->response->setOutput($html_documentation);
        }
    }
    
    /**
     * Generate test report
     */
    public function testReport() {
        if (!$this->test_suite) {
            $this->response->setOutput('Test report not available');
            return;
        }
        
        $this->response->addHeader('Content-Type: text/html');
        $test_report = $this->test_suite->generateTestReport();
        $this->response->setOutput($test_report);
    }
    
    /**
     * Get API logs
     */
    public function logs() {
        $this->response->addHeader('Content-Type: application/json');
        
        $page = (int)($this->request->get['page'] ?? 1);
        $limit = (int)($this->request->get['limit'] ?? 50);
        $level = $this->request->get['level'] ?? '';
        
        $logs = $this->getApiLogs($page, $limit, $level);
        
        $this->response->setOutput(json_encode([
            'status' => 'success',
            'data' => $logs
        ]));
    }
    
    /**
     * Clear API logs
     */
    public function clearLogs() {
        $this->response->addHeader('Content-Type: application/json');
        
        try {
            $log_pattern = DIR_LOGS . 'meschain_api_*.log';
            $log_files = glob($log_pattern);
            
            $cleared_count = 0;
            foreach ($log_files as $log_file) {
                if (unlink($log_file)) {
                    $cleared_count++;
                }
            }
            
            $this->response->setOutput(json_encode([
                'status' => 'success',
                'message' => "Cleared {$cleared_count} log files"
            ]));
        } catch (Exception $e) {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'message' => 'Failed to clear logs: ' . $e->getMessage()
            ]));
        }
    }
    
    /**
     * API statistics endpoint
     */
    public function statistics() {
        $this->response->addHeader('Content-Type: application/json');
        
        if (!$this->api_service) {
            $this->response->setOutput(json_encode([
                'status' => 'error',
                'message' => 'API service not available'
            ]));
            return;
        }
        
        $period = (int)($this->request->get['period'] ?? 7);
        $stats = $this->api_service->getApiStatistics($period);
        
        $this->response->setOutput(json_encode([
            'status' => 'success',
            'data' => $stats
        ]));
    }
    
    /**
     * Real-time API monitoring endpoint (SSE)
     */
    public function monitor() {
        // Set SSE headers
        $this->response->addHeader('Content-Type: text/event-stream');
        $this->response->addHeader('Cache-Control: no-cache');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        
        // Send initial health status
        $health_status = $this->api_service ? $this->api_service->getHealthStatus() : null;
        echo "data: " . json_encode([
            'type' => 'health',
            'data' => $health_status,
            'timestamp' => time()
        ]) . "\n\n";
        
        // Send API statistics
        $stats = $this->api_service ? $this->api_service->getApiStatistics(1) : null;
        echo "data: " . json_encode([
            'type' => 'statistics',
            'data' => $stats,
            'timestamp' => time()
        ]) . "\n\n";
        
        // Flush output
        if (ob_get_level()) {
            ob_end_flush();
        }
        flush();
    }
    
    /**
     * Get recent API logs
     */
    private function getRecentApiLogs($limit = 10) {
        $logs = [];
        $log_file = DIR_LOGS . 'meschain_api_' . date('Y-m-d') . '.log';
        
        if (file_exists($log_file)) {
            $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $recent_lines = array_slice($lines, -$limit);
            
            foreach ($recent_lines as $line) {
                $log_entry = json_decode($line, true);
                if ($log_entry) {
                    $logs[] = $log_entry;
                }
            }
        }
        
        return array_reverse($logs);
    }
    
    /**
     * Get API logs with pagination
     */
    private function getApiLogs($page = 1, $limit = 50, $level = '') {
        $logs = [];
        $total_logs = 0;
        
        // Get log files for the last 7 days
        $log_files = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $log_file = DIR_LOGS . 'meschain_api_' . $date . '.log';
            if (file_exists($log_file)) {
                $log_files[] = $log_file;
            }
        }
        
        // Read and parse logs
        foreach ($log_files as $log_file) {
            $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $log_entry = json_decode($line, true);
                if ($log_entry) {
                    // Filter by level if specified
                    if ($level && $log_entry['level'] !== $level) {
                        continue;
                    }
                    $logs[] = $log_entry;
                }
            }
        }
        
        // Sort by timestamp (most recent first)
        usort($logs, function($a, $b) {
            return strcmp($b['timestamp'], $a['timestamp']);
        });
        
        $total_logs = count($logs);
        
        // Apply pagination
        $offset = ($page - 1) * $limit;
        $logs = array_slice($logs, $offset, $limit);
        
        return [
            'logs' => $logs,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total_logs,
                'pages' => ceil($total_logs / $limit)
            ]
        ];
    }
}
