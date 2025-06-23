<?php
/**
 * MesChain API Gateway Management Controller
 * 
 * @category   MesChain
 * @package    API Gateway Management
 * @author     MesChain Development Team
 * @copyright  2025 MesChain
 * @license    https://meschain.com/license
 * @version    1.0.0
 */

class ControllerExtensionModuleMeschainApiGateway extends Controller {
    
    private $error = [];
    
    public function index() {
        $this->load->language('extension/module/meschain_api_gateway');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Load API Gateway library
        $this->load->library('meschain/api/api_gateway');
        $api_gateway = new MesChainApiGateway($this->registry);
        
        $data['breadcrumbs'] = [];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/meschain_api_gateway', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        // Get statistics
        $data['statistics'] = $api_gateway->getStatistics();
        
        // Get current configuration
        $data['rate_limits'] = $this->getRateLimits();
        $data['circuit_breaker_config'] = $this->getCircuitBreakerConfig();
        
        // Get recent API requests
        $data['recent_requests'] = $this->getRecentRequests();
        
        // Get API keys
        $data['api_keys'] = $this->getApiKeys();
        
        // Action URLs
        $data['action_test_api'] = $this->url->link('extension/module/meschain_api_gateway/testApiConnection', 'user_token=' . $this->session->data['user_token'], true);
        $data['action_reset_circuit_breaker'] = $this->url->link('extension/module/meschain_api_gateway/resetCircuitBreaker', 'user_token=' . $this->session->data['user_token'], true);
        $data['action_generate_api_key'] = $this->url->link('extension/module/meschain_api_gateway/generateApiKey', 'user_token=' . $this->session->data['user_token'], true);
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_api_gateway', $data));
    }
    
    /**
     * Test API connection
     */
    public function testApiConnection() {
        $this->load->language('extension/module/meschain_api_gateway');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $service = $this->request->post['service'] ?? '';
            
            if (empty($service)) {
                $json['error'] = 'Service parameter is required';
            } else {
                // Load API Gateway
                $this->load->library('meschain/api/api_gateway');
                $api_gateway = new MesChainApiGateway($this->registry);
                
                // Test connection based on service
                $test_result = $this->testServiceConnection($service);
                
                if ($test_result['success']) {
                    $json['success'] = 'Connection test successful';
                    $json['data'] = $test_result['data'];
                } else {
                    $json['error'] = $test_result['error'];
                }
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Reset circuit breaker
     */
    public function resetCircuitBreaker() {
        $this->load->language('extension/module/meschain_api_gateway');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $service = $this->request->post['service'] ?? '';
            
            if (empty($service)) {
                $json['error'] = 'Service parameter is required';
            } else {
                // Load API Gateway
                $this->load->library('meschain/api/api_gateway');
                $api_gateway = new MesChainApiGateway($this->registry);
                
                if ($api_gateway->resetCircuitBreaker($service)) {
                    $json['success'] = 'Circuit breaker reset successfully for ' . $service;
                } else {
                    $json['error'] = 'Failed to reset circuit breaker';
                }
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Generate new API key
     */
    public function generateApiKey() {
        $this->load->language('extension/module/meschain_api_gateway');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $client_name = $this->request->post['client_name'] ?? '';
            $plan_type = $this->request->post['plan_type'] ?? 'default';
            
            if (empty($client_name)) {
                $json['error'] = 'Client name is required';
            } else {
                $api_key = $this->createApiKey($client_name, $plan_type);
                
                if ($api_key) {
                    $json['success'] = 'API key generated successfully';
                    $json['api_key'] = $api_key;
                } else {
                    $json['error'] = 'Failed to generate API key';
                }
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get rate limiting configuration
     */
    private function getRateLimits() {
        return [
            'default' => [
                'requests_per_minute' => 100,
                'requests_per_hour' => 1000,
                'requests_per_day' => 10000
            ],
            'premium' => [
                'requests_per_minute' => 500,
                'requests_per_hour' => 5000,
                'requests_per_day' => 50000
            ],
            'enterprise' => [
                'requests_per_minute' => 1000,
                'requests_per_hour' => 10000,
                'requests_per_day' => 100000
            ]
        ];
    }
    
    /**
     * Get circuit breaker configuration
     */
    private function getCircuitBreakerConfig() {
        return [
            'trendyol' => ['failure_threshold' => 5, 'timeout' => 60],
            'n11' => ['failure_threshold' => 5, 'timeout' => 60],
            'amazon' => ['failure_threshold' => 3, 'timeout' => 120],
            'ebay' => ['failure_threshold' => 5, 'timeout' => 60],
            'hepsiburada' => ['failure_threshold' => 5, 'timeout' => 60],
            'ozon' => ['failure_threshold' => 5, 'timeout' => 60]
        ];
    }
    
    /**
     * Get recent API requests
     */
    private function getRecentRequests() {
        $query = $this->db->query("
            SELECT 
                request_id,
                service,
                processing_time,
                success,
                created_at
            FROM " . DB_PREFIX . "meschain_api_metrics 
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        
        return $query->rows;
    }
    
    /**
     * Get API keys
     */
    private function getApiKeys() {
        $query = $this->db->query("
            SELECT 
                ak.*,
                c.company_name,
                c.plan_type
            FROM " . DB_PREFIX . "meschain_api_keys ak
            LEFT JOIN " . DB_PREFIX . "meschain_clients c ON ak.client_id = c.client_id
            ORDER BY ak.created_at DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * Test service connection
     */
    private function testServiceConnection($service) {
        $test_endpoints = [
            'trendyol' => 'https://api.trendyol.com/health',
            'n11' => 'https://api.n11.com/health',
            'amazon' => 'https://mws.amazonservices.com/health',
            'ebay' => 'https://api.ebay.com/health',
            'hepsiburada' => 'https://listing-external.hepsiburada.com/health',
            'ozon' => 'https://api-seller.ozon.ru/health'
        ];
        
        if (!isset($test_endpoints[$service])) {
            return ['success' => false, 'error' => 'Unknown service'];
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $test_endpoints[$service],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'error' => $error];
        }
        
        return [
            'success' => true,
            'data' => [
                'http_code' => $http_code,
                'response_time' => curl_getinfo($ch, CURLINFO_TOTAL_TIME),
                'service' => $service
            ]
        ];
    }
    
    /**
     * Create new API key
     */
    private function createApiKey($client_name, $plan_type) {
        // Generate API key
        $api_key = 'mk_' . bin2hex(random_bytes(16));
        
        // Insert client
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_clients 
            (company_name, plan_type, status, created_at) 
            VALUES (
                '" . $this->db->escape($client_name) . "',
                '" . $this->db->escape($plan_type) . "',
                1,
                NOW()
            )
        ");
        
        $client_id = $this->db->getLastId();
        
        // Insert API key
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_api_keys 
            (client_id, api_key, expires_at, status, created_at) 
            VALUES (
                '" . (int)$client_id . "',
                '" . $this->db->escape($api_key) . "',
                DATE_ADD(NOW(), INTERVAL 1 YEAR),
                1,
                NOW()
            )
        ");
        
        return $api_key;
    }
    
    /**
     * Get dashboard data for AJAX calls
     */
    public function getDashboardData() {
        $this->load->library('meschain/api/api_gateway');
        $api_gateway = new MesChainApiGateway($this->registry);
        
        $json = [
            'statistics' => $api_gateway->getStatistics(),
            'recent_requests' => $this->getRecentRequests(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
} 