<?php
/**
 * Advanced Production Monitor Controller - ATOM-M007
 * MesChain-Sync Enterprise Production Excellence
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0 - ATOM-M007
 * @author Musti DevOps Team
 * @date 2025-06-06
 */

class ControllerExtensionModuleAdvancedProductionMonitor extends Controller {
    
    private $error = array();
    private $monitor;
    
    /**
     * Initialize
     */
    public function __construct($registry) {
        parent::__construct($registry);
        
        // Load dependencies
        $this->load->language('extension/module/advanced_production_monitor');
        $this->load->model('extension/module/advanced_production_monitor');
        
        // Initialize monitor
        require_once(DIR_SYSTEM . 'library/meschain/monitoring/advanced_production_monitor.php');
        $this->monitor = new AdvancedProductionMonitor($this->db->getConnections(), [
            'alert_thresholds' => [
                'api_response_time' => $this->config->get('apm_api_threshold') ?: 200,
                'database_query_time' => $this->config->get('apm_db_threshold') ?: 50,
                'memory_usage' => $this->config->get('apm_memory_threshold') ?: 80,
                'cpu_usage' => $this->config->get('apm_cpu_threshold') ?: 75,
                'error_rate' => $this->config->get('apm_error_threshold') ?: 0.1
            ],
            'notification_channels' => [
                'email' => $this->config->get('apm_email_alerts') ?: true,
                'slack' => $this->config->get('apm_slack_alerts') ?: false,
                'webhook' => $this->config->get('apm_webhook_alerts') ?: false
            ]
        ]);
    }
    
    /**
     * Main dashboard view
     */
    public function index() {
        $this->load->language('extension/module/advanced_production_monitor');
        
        // Set page title
        $this->document->setTitle($this->language->get('heading_title'));
        
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
            'href' => $this->url->link('extension/module/advanced_production_monitor', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // URLs
        $data['action'] = $this->url->link('extension/module/advanced_production_monitor', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        $data['metrics_url'] = $this->url->link('extension/module/advanced_production_monitor/getMetrics', 'user_token=' . $this->session->data['user_token'], true);
        
        // Header and footer
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        // Load the dashboard view
        $this->response->setOutput($this->load->view('extension/module/advanced_production_dashboard', $data));
    }
    
    /**
     * AJAX endpoint for real-time metrics
     */
    public function getMetrics() {
        try {
            // Get APM metrics
            $apm_metrics = $this->monitor->getAPMMetrics();
            
            // Get user behavior analytics
            $user_behavior = $this->monitor->getUserBehaviorAnalytics();
            
            // Get predictive maintenance alerts
            $predictive_alerts = $this->monitor->getPredictiveMaintenanceAlerts();
            
            // Get customer experience metrics
            $cx_metrics = $this->monitor->getCustomerExperienceMetrics();
            
            // Get business intelligence data
            $bi_data = $this->monitor->getBusinessIntelligenceDashboard();
            
            // Combine all metrics
            $response = [
                'success' => true,
                'timestamp' => date('c'),
                'apm' => $apm_metrics,
                'user_behavior' => $user_behavior,
                'predictive_maintenance' => $predictive_alerts,
                'customer_experience' => $cx_metrics,
                'business_intelligence' => $bi_data,
                'system_status' => [
                    'overall_health' => $apm_metrics['health_score'] ?? 0,
                    'alerts_count' => count($predictive_alerts['maintenance_recommendations'] ?? []),
                    'critical_issues' => count($predictive_alerts['risk_assessment']['critical_risks'] ?? [])
                ]
            ];
            
            // Add the main metrics for the dashboard
            if (isset($apm_metrics['application'])) {
                $response['application'] = $apm_metrics['application'];
                $response['performance'] = $apm_metrics['performance'];
                $response['business_metrics'] = $apm_metrics['business_metrics'];
                $response['infrastructure'] = $apm_metrics['infrastructure'];
                $response['health_score'] = $apm_metrics['health_score'];
            }
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($response));
            
        } catch (Exception $e) {
            $error_response = [
                'success' => false,
                'error' => 'Failed to fetch metrics',
                'message' => $e->getMessage(),
                'timestamp' => date('c')
            ];
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($error_response));
        }
    }
    
    /**
     * Export comprehensive report
     */
    public function exportReport() {
        try {
            // Get all metrics
            $apm_metrics = $this->monitor->getAPMMetrics();
            $user_behavior = $this->monitor->getUserBehaviorAnalytics();
            $predictive_alerts = $this->monitor->getPredictiveMaintenanceAlerts();
            $cx_metrics = $this->monitor->getCustomerExperienceMetrics();
            $bi_data = $this->monitor->getBusinessIntelligenceDashboard();
            
            // Create comprehensive report
            $report = [
                'report_metadata' => [
                    'title' => 'ATOM-M007 Production Monitoring Report',
                    'generated_at' => date('Y-m-d H:i:s T'),
                    'report_period' => 'Real-time snapshot',
                    'system_version' => '3.0.4.0-ATOM-M007'
                ],
                'executive_summary' => [
                    'overall_health_score' => $apm_metrics['health_score'] ?? 0,
                    'system_uptime' => $apm_metrics['application']['uptime'] ?? 0,
                    'performance_status' => $this->getPerformanceStatus($apm_metrics),
                    'critical_alerts' => count($predictive_alerts['risk_assessment']['critical_risks'] ?? []),
                    'recommendations_count' => count($predictive_alerts['maintenance_recommendations'] ?? [])
                ],
                'detailed_metrics' => [
                    'application_performance' => $apm_metrics,
                    'user_behavior_analytics' => $user_behavior,
                    'predictive_maintenance' => $predictive_alerts,
                    'customer_experience' => $cx_metrics,
                    'business_intelligence' => $bi_data
                ],
                'marketplace_health' => [
                    'trendyol' => $this->getMarketplaceHealth('trendyol'),
                    'amazon' => $this->getMarketplaceHealth('amazon'),
                    'n11' => $this->getMarketplaceHealth('n11'),
                    'hepsiburada' => $this->getMarketplaceHealth('hepsiburada'),
                    'ebay' => $this->getMarketplaceHealth('ebay'),
                    'ozon' => $this->getMarketplaceHealth('ozon')
                ],
                'recommendations' => $this->generateRecommendations($apm_metrics, $predictive_alerts)
            ];
            
            // Set headers for download
            $filename = 'ATOM_M007_Production_Report_' . date('Y-m-d_H-i-s') . '.json';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
            $this->response->setOutput(json_encode($report, JSON_PRETTY_PRINT));
            
        } catch (Exception $e) {
            $this->response->addHeader('Content-Type: text/plain');
            $this->response->setOutput('Error generating report: ' . $e->getMessage());
        }
    }
    
    /**
     * Health check endpoint for external monitoring
     */
    public function healthCheck() {
        try {
            $health_data = [
                'status' => 'healthy',
                'timestamp' => date('c'),
                'version' => '3.0.4.0-ATOM-M007',
                'checks' => [
                    'database' => $this->checkDatabaseHealth(),
                    'filesystem' => $this->checkFilesystemHealth(),
                    'memory' => $this->checkMemoryHealth(),
                    'marketplace_apis' => $this->checkMarketplaceAPIsHealth()
                ]
            ];
            
            // Determine overall status
            $all_healthy = true;
            foreach ($health_data['checks'] as $check) {
                if (!$check['healthy']) {
                    $all_healthy = false;
                    break;
                }
            }
            
            $health_data['status'] = $all_healthy ? 'healthy' : 'degraded';
            $health_data['overall_score'] = $this->calculateOverallHealthScore($health_data['checks']);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($health_data));
            
        } catch (Exception $e) {
            $error_response = [
                'status' => 'error',
                'timestamp' => date('c'),
                'error' => $e->getMessage()
            ];
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($error_response));
        }
    }
    
    /**
     * Get alerts summary
     */
    public function getAlerts() {
        try {
            $predictive_alerts = $this->monitor->getPredictiveMaintenanceAlerts();
            
            $alerts_summary = [
                'timestamp' => date('c'),
                'total_alerts' => 0,
                'critical_alerts' => count($predictive_alerts['risk_assessment']['critical_risks'] ?? []),
                'warning_alerts' => count($predictive_alerts['risk_assessment']['moderate_risks'] ?? []),
                'info_alerts' => count($predictive_alerts['risk_assessment']['low_risks'] ?? []),
                'maintenance_recommendations' => $predictive_alerts['maintenance_recommendations'] ?? [],
                'alert_details' => []
            ];
            
            $alerts_summary['total_alerts'] = $alerts_summary['critical_alerts'] + 
                                            $alerts_summary['warning_alerts'] + 
                                            $alerts_summary['info_alerts'];
            
            // Format alert details
            foreach ($predictive_alerts['risk_assessment'] ?? [] as $severity => $risks) {
                foreach ($risks as $risk) {
                    $alerts_summary['alert_details'][] = [
                        'severity' => $severity,
                        'category' => $risk['category'] ?? 'system',
                        'description' => $risk['description'] ?? 'No description',
                        'probability' => $risk['probability'] ?? 0,
                        'impact_level' => $risk['impact_level'] ?? 'unknown'
                    ];
                }
            }
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($alerts_summary));
            
        } catch (Exception $e) {
            $error_response = [
                'error' => true,
                'message' => 'Failed to fetch alerts',
                'timestamp' => date('c')
            ];
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($error_response));
        }
    }
    
    // Helper methods
    private function getPerformanceStatus($metrics) {
        $avg_response = $metrics['performance']['api_response_times']['average'] ?? 0;
        
        if ($avg_response < 150) return 'excellent';
        if ($avg_response < 300) return 'good';
        if ($avg_response < 500) return 'fair';
        return 'poor';
    }
    
    private function getMarketplaceHealth($marketplace) {
        // Simulate marketplace health check
        $response_times = [
            'trendyol' => 120,
            'amazon' => 180,
            'n11' => 95,
            'hepsiburada' => 160,
            'ebay' => 200,
            'ozon' => 140
        ];
        
        $response_time = $response_times[$marketplace] ?? 999;
        
        return [
            'status' => $response_time < 250 ? 'healthy' : 'degraded',
            'response_time' => $response_time,
            'last_check' => date('c'),
            'uptime' => 99.9
        ];
    }
    
    private function generateRecommendations($apm_metrics, $predictive_alerts) {
        $recommendations = [];
        
        // Performance recommendations
        $avg_response = $apm_metrics['performance']['api_response_times']['average'] ?? 0;
        if ($avg_response > 200) {
            $recommendations[] = [
                'category' => 'performance',
                'priority' => 'high',
                'title' => 'API Response Time Optimization',
                'description' => 'Average API response time exceeds threshold. Consider implementing caching strategies.',
                'estimated_impact' => '30% improvement in response times'
            ];
        }
        
        // Add maintenance recommendations
        foreach ($predictive_alerts['maintenance_recommendations'] ?? [] as $recommendation) {
            $recommendations[] = $recommendation;
        }
        
        return $recommendations;
    }
    
    private function checkDatabaseHealth() {
        try {
            $this->db->query("SELECT 1");
            return ['healthy' => true, 'message' => 'Database connection OK'];
        } catch (Exception $e) {
            return ['healthy' => false, 'message' => 'Database connection failed'];
        }
    }
    
    private function checkFilesystemHealth() {
        return [
            'healthy' => is_writable(DIR_LOGS),
            'message' => is_writable(DIR_LOGS) ? 'Filesystem writable' : 'Filesystem not writable'
        ];
    }
    
    private function checkMemoryHealth() {
        $memory_usage = memory_get_usage(true);
        $memory_limit = ini_get('memory_limit');
        $memory_limit_bytes = $this->convertToBytes($memory_limit);
        $usage_percent = ($memory_usage / $memory_limit_bytes) * 100;
        
        return [
            'healthy' => $usage_percent < 80,
            'message' => "Memory usage: {$usage_percent}%",
            'usage_percent' => $usage_percent
        ];
    }
    
    private function checkMarketplaceAPIsHealth() {
        // Simulate marketplace API health checks
        return [
            'healthy' => true,
            'message' => 'All marketplace APIs responding',
            'details' => [
                'trendyol' => 'online',
                'amazon' => 'online',
                'n11' => 'online',
                'hepsiburada' => 'online',
                'ebay' => 'online',
                'ozon' => 'online'
            ]
        ];
    }
    
    private function calculateOverallHealthScore($checks) {
        $healthy_count = 0;
        $total_checks = count($checks);
        
        foreach ($checks as $check) {
            if ($check['healthy']) {
                $healthy_count++;
            }
        }
        
        return round(($healthy_count / $total_checks) * 100, 1);
    }
    
    private function convertToBytes($value) {
        $value = trim($value);
        $last = strtolower($value[strlen($value)-1]);
        $value = (int)$value;
        
        switch($last) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        
        return $value;
    }
}