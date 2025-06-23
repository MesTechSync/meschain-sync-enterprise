<?php
/**
 * MesChain API Test Suite
 * Comprehensive testing and documentation for all API endpoints
 * 
 * @version 1.0.0
 * @date June 2, 2025
 * @author MesChain Development Team
 */

class MeschainApiTestSuite {
    
    private $api_service;
    private $test_results = [];
    private $documentation = [];
    
    public function __construct($db) {
        // Load API integration service
        if (file_exists(DIR_SYSTEM . 'library/meschain/api_integration_service.php')) {
            require_once(DIR_SYSTEM . 'library/meschain/api_integration_service.php');
            $this->api_service = new MeschainApiIntegrationService($db, [
                'enable_logging' => true,
                'debug_mode' => true
            ]);
        }
    }
    
    /**
     * Run comprehensive API test suite
     */
    public function runTestSuite() {
        $this->test_results = [];
        
        // Test dashboard endpoints
        $this->testDashboardEndpoints();
        
        // Test marketplace endpoints
        $this->testMarketplaceEndpoints();
        
        // Test data sync endpoints
        $this->testDataSyncEndpoints();
        
        // Test error handling
        $this->testErrorHandling();
        
        // Test rate limiting
        $this->testRateLimiting();
        
        // Test validation
        $this->testValidation();
        
        // Generate summary
        $this->generateTestSummary();
        
        return $this->test_results;
    }
    
    /**
     * Test dashboard endpoints
     */
    private function testDashboardEndpoints() {
        $this->addTestSection('Dashboard Endpoints');
        
        // Test dashboard metrics
        $this->runTest('Dashboard Metrics', function() {
            return $this->api_service->processRequest(
                'dashboard_metrics',
                'GET',
                ['period' => '30', 'format' => 'json'],
                [],
                '127.0.0.1'
            );
        });
        
        // Test dashboard metrics with different periods
        $this->runTest('Dashboard Metrics - 7 days', function() {
            return $this->api_service->processRequest(
                'dashboard_metrics',
                'GET',
                ['period' => '7'],
                [],
                '127.0.0.1'
            );
        });
        
        // Test dashboard metrics with marketplace filter
        $this->runTest('Dashboard Metrics - Amazon only', function() {
            return $this->api_service->processRequest(
                'dashboard_metrics',
                'GET',
                ['marketplace' => 'amazon'],
                [],
                '127.0.0.1'
            );
        });
    }
    
    /**
     * Test marketplace endpoints
     */
    private function testMarketplaceEndpoints() {
        $this->addTestSection('Marketplace Endpoints');
        
        $marketplaces = ['amazon', 'ebay', 'trendyol'];
        
        foreach ($marketplaces as $marketplace) {
            $this->runTest(ucfirst($marketplace) . ' Metrics', function() use ($marketplace) {
                return $this->api_service->processRequest(
                    $marketplace . '_metrics',
                    'GET',
                    ['period' => '30'],
                    [],
                    '127.0.0.1'
                );
            });
        }
    }
    
    /**
     * Test data sync endpoints
     */
    private function testDataSyncEndpoints() {
        $this->addTestSection('Data Sync Endpoints');
        
        // Test product sync
        $this->runTest('Product Sync - Amazon', function() {
            return $this->api_service->processRequest(
                'product_sync',
                'POST',
                ['marketplace' => 'amazon', 'force_update' => 'true'],
                [],
                '127.0.0.1'
            );
        });
        
        // Test order sync
        $this->runTest('Order Sync - All marketplaces', function() {
            return $this->api_service->processRequest(
                'order_sync',
                'POST',
                ['marketplace' => 'all'],
                [],
                '127.0.0.1'
            );
        });
    }
    
    /**
     * Test error handling
     */
    private function testErrorHandling() {
        $this->addTestSection('Error Handling');
        
        // Test unknown endpoint
        $this->runTest('Unknown Endpoint', function() {
            return $this->api_service->processRequest(
                'unknown_endpoint',
                'GET',
                [],
                [],
                '127.0.0.1'
            );
        }, false); // Expect failure
        
        // Test invalid method
        $this->runTest('Invalid Method', function() {
            return $this->api_service->processRequest(
                'dashboard_metrics',
                'DELETE',
                [],
                [],
                '127.0.0.1'
            );
        }, false); // Expect failure
    }
    
    /**
     * Test rate limiting
     */
    private function testRateLimiting() {
        $this->addTestSection('Rate Limiting');
        
        // Test multiple rapid requests
        $this->runTest('Rate Limiting - Multiple Requests', function() {
            $responses = [];
            for ($i = 0; $i < 5; $i++) {
                $response = $this->api_service->processRequest(
                    'dashboard_metrics',
                    'GET',
                    [],
                    [],
                    '127.0.0.1'
                );
                $responses[] = $response;
            }
            return end($responses); // Return last response
        });
    }
    
    /**
     * Test validation
     */
    private function testValidation() {
        $this->addTestSection('Validation');
        
        // Test missing required field
        $this->runTest('Missing Required Field', function() {
            return $this->api_service->processRequest(
                'product_sync',
                'POST',
                [], // Missing required 'marketplace' field
                [],
                '127.0.0.1'
            );
        }, false); // Expect failure
        
        // Test invalid field type
        $this->runTest('Invalid Field Type', function() {
            return $this->api_service->processRequest(
                'dashboard_metrics',
                'GET',
                ['period' => 123], // Should be string, not number
                [],
                '127.0.0.1'
            );
        });
    }
    
    /**
     * Run individual test
     */
    private function runTest($test_name, $test_function, $expect_success = true) {
        $start_time = microtime(true);
        
        try {
            $response = $test_function();
            $execution_time = (microtime(true) - $start_time) * 1000;
            
            $success = $expect_success ? 
                ($response['status'] === 'success') : 
                ($response['status'] === 'error');
            
            $this->test_results['tests'][] = [
                'name' => $test_name,
                'status' => $success ? 'PASS' : 'FAIL',
                'execution_time' => round($execution_time, 2),
                'response' => $response,
                'expected_success' => $expect_success
            ];
            
        } catch (Exception $e) {
            $execution_time = (microtime(true) - $start_time) * 1000;
            
            $this->test_results['tests'][] = [
                'name' => $test_name,
                'status' => $expect_success ? 'FAIL' : 'PASS',
                'execution_time' => round($execution_time, 2),
                'error' => $e->getMessage(),
                'expected_success' => $expect_success
            ];
        }
    }
    
    /**
     * Add test section
     */
    private function addTestSection($section_name) {
        $this->test_results['sections'][] = $section_name;
    }
    
    /**
     * Generate test summary
     */
    private function generateTestSummary() {
        $total_tests = count($this->test_results['tests']);
        $passed_tests = array_filter($this->test_results['tests'], function($test) {
            return $test['status'] === 'PASS';
        });
        $failed_tests = $total_tests - count($passed_tests);
        
        $total_time = array_sum(array_column($this->test_results['tests'], 'execution_time'));
        $avg_time = $total_tests > 0 ? $total_time / $total_tests : 0;
        
        $this->test_results['summary'] = [
            'total_tests' => $total_tests,
            'passed' => count($passed_tests),
            'failed' => $failed_tests,
            'success_rate' => $total_tests > 0 ? round((count($passed_tests) / $total_tests) * 100, 2) : 0,
            'total_execution_time' => round($total_time, 2),
            'average_execution_time' => round($avg_time, 2),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Generate API documentation
     */
    public function generateDocumentation() {
        $this->documentation = [
            'title' => 'MesChain API Documentation',
            'version' => '2.0.0',
            'generated_at' => date('Y-m-d H:i:s'),
            'base_url' => '/admin/extension/module/',
            'authentication' => [
                'type' => 'session',
                'description' => 'Uses OpenCart admin session for authentication'
            ],
            'endpoints' => $this->getEndpointsDocumentation()
        ];
        
        return $this->documentation;
    }
    
    /**
     * Get endpoints documentation
     */
    private function getEndpointsDocumentation() {
        return [
            'dashboard' => [
                'path' => 'meschain_dashboard_api',
                'description' => 'Main dashboard metrics and real-time data',
                'methods' => [
                    'metrics' => [
                        'method' => 'GET',
                        'path' => '/metrics',
                        'description' => 'Get comprehensive dashboard metrics',
                        'parameters' => [
                            'period' => [
                                'type' => 'string',
                                'required' => false,
                                'default' => '30',
                                'description' => 'Time period in days (7, 30, 90)'
                            ],
                            'format' => [
                                'type' => 'string',
                                'required' => false,
                                'default' => 'json',
                                'description' => 'Response format (json, chartjs)'
                            ],
                            'marketplace' => [
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Filter by specific marketplace'
                            ]
                        ],
                        'response_example' => [
                            'status' => 'success',
                            'data' => [
                                'metrics' => '...',
                                'performance' => '...',
                                'marketplace_status' => '...'
                            ],
                            'meta' => [
                                'timestamp' => '2025-06-02 10:30:00',
                                'processing_time' => '45.67'
                            ]
                        ]
                    ],
                    'chartjs' => [
                        'method' => 'GET',
                        'path' => '/chartjs',
                        'description' => 'Get Chart.js compatible data',
                        'parameters' => [
                            'type' => [
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Chart type (sales, orders, performance)'
                            ],
                            'period' => [
                                'type' => 'string',
                                'required' => false,
                                'default' => '30',
                                'description' => 'Time period in days'
                            ]
                        ]
                    ],
                    'realtime' => [
                        'method' => 'GET',
                        'path' => '/realtime',
                        'description' => 'Get real-time data stream',
                        'parameters' => [
                            'last_event_id' => [
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Last received event ID for incremental updates'
                            ]
                        ]
                    ]
                ]
            ],
            'amazon' => [
                'path' => 'amazon_api',
                'description' => 'Amazon marketplace specific endpoints',
                'methods' => [
                    'metrics' => [
                        'method' => 'GET',
                        'path' => '/metrics',
                        'description' => 'Get Amazon marketplace metrics',
                        'parameters' => [
                            'period' => [
                                'type' => 'string',
                                'required' => false,
                                'default' => '30',
                                'description' => 'Time period in days'
                            ],
                            'include_fees' => [
                                'type' => 'string',
                                'required' => false,
                                'default' => 'false',
                                'description' => 'Include fee breakdown in response'
                            ],
                            'region' => [
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Amazon region (US, EU, etc.)'
                            ]
                        ]
                    ],
                    'orders' => [
                        'method' => 'GET',
                        'path' => '/orders',
                        'description' => 'Get Amazon order data',
                        'parameters' => [
                            'status' => [
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Filter by order status'
                            ],
                            'date_from' => [
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Start date (YYYY-MM-DD)'
                            ],
                            'date_to' => [
                                'type' => 'string',
                                'required' => false,
                                'description' => 'End date (YYYY-MM-DD)'
                            ]
                        ]
                    ]
                ]
            ],
            'ebay' => [
                'path' => 'ebay_api',
                'description' => 'eBay marketplace specific endpoints',
                'methods' => [
                    'metrics' => [
                        'method' => 'GET',
                        'path' => '/metrics',
                        'description' => 'Get eBay marketplace metrics'
                    ],
                    'listings' => [
                        'method' => 'GET',
                        'path' => '/listings',
                        'description' => 'Get eBay listing data'
                    ]
                ]
            ],
            'trendyol' => [
                'path' => 'trendyol_api',
                'description' => 'Trendyol marketplace specific endpoints',
                'methods' => [
                    'metrics' => [
                        'method' => 'GET',
                        'path' => '/metrics',
                        'description' => 'Get Trendyol marketplace metrics'
                    ],
                    'campaigns' => [
                        'method' => 'GET',
                        'path' => '/campaigns',
                        'description' => 'Get Trendyol campaign data'
                    ]
                ]
            ],
            'sync' => [
                'path' => 'meschain_sync_api',
                'description' => 'Data synchronization endpoints',
                'methods' => [
                    'products' => [
                        'method' => 'POST',
                        'path' => '/sync/products',
                        'description' => 'Sync product data across marketplaces',
                        'parameters' => [
                            'marketplace' => [
                                'type' => 'string',
                                'required' => true,
                                'description' => 'Target marketplace or "all"'
                            ],
                            'product_ids' => [
                                'type' => 'array',
                                'required' => false,
                                'description' => 'Specific product IDs to sync'
                            ],
                            'force_update' => [
                                'type' => 'boolean',
                                'required' => false,
                                'default' => false,
                                'description' => 'Force update even if unchanged'
                            ]
                        ]
                    ],
                    'orders' => [
                        'method' => 'POST',
                        'path' => '/sync/orders',
                        'description' => 'Sync order data from marketplaces',
                        'parameters' => [
                            'marketplace' => [
                                'type' => 'string',
                                'required' => true,
                                'description' => 'Source marketplace or "all"'
                            ],
                            'date_from' => [
                                'type' => 'string',
                                'required' => false,
                                'description' => 'Start date for sync (YYYY-MM-DD)'
                            ],
                            'date_to' => [
                                'type' => 'string',
                                'required' => false,
                                'description' => 'End date for sync (YYYY-MM-DD)'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Generate HTML documentation
     */
    public function generateHtmlDocumentation() {
        $docs = $this->generateDocumentation();
        
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $docs['title'] . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1, h2, h3 { color: #333; }
        h1 { border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        .endpoint { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #007bff; }
        .method { display: inline-block; padding: 4px 8px; border-radius: 3px; color: white; font-weight: bold; margin-right: 10px; }
        .get { background: #28a745; }
        .post { background: #007bff; }
        .put { background: #ffc107; color: black; }
        .delete { background: #dc3545; }
        .param { background: #e9ecef; padding: 10px; margin: 5px 0; border-radius: 3px; }
        .param-name { font-weight: bold; color: #495057; }
        .param-type { color: #6c757d; font-style: italic; }
        .required { color: #dc3545; }
        .optional { color: #28a745; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .toc { background: #e7f3ff; padding: 20px; border-radius: 5px; margin-bottom: 30px; }
        .toc ul { list-style-type: none; padding-left: 0; }
        .toc li { margin: 5px 0; }
        .toc a { text-decoration: none; color: #007bff; }
        .toc a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>' . $docs['title'] . '</h1>
        <p><strong>Version:</strong> ' . $docs['version'] . '</p>
        <p><strong>Generated:</strong> ' . $docs['generated_at'] . '</p>
        <p><strong>Base URL:</strong> <code>' . $docs['base_url'] . '</code></p>
        
        <div class="toc">
            <h2>Table of Contents</h2>
            <ul>';
        
        foreach ($docs['endpoints'] as $endpoint_name => $endpoint_data) {
            $html .= '<li><a href="#' . $endpoint_name . '">' . ucfirst($endpoint_name) . ' API</a></li>';
        }
        
        $html .= '</ul>
        </div>';
        
        foreach ($docs['endpoints'] as $endpoint_name => $endpoint_data) {
            $html .= '<div class="endpoint" id="' . $endpoint_name . '">
                <h2>' . ucfirst($endpoint_name) . ' API</h2>
                <p>' . $endpoint_data['description'] . '</p>
                <p><strong>Base Path:</strong> <code>' . $endpoint_data['path'] . '</code></p>';
            
            foreach ($endpoint_data['methods'] as $method_name => $method_data) {
                $html .= '<h3>' . ucfirst($method_name) . '</h3>
                    <p><span class="method ' . strtolower($method_data['method']) . '">' . $method_data['method'] . '</span>
                    <code>' . $method_data['path'] . '</code></p>
                    <p>' . $method_data['description'] . '</p>';
                
                if (isset($method_data['parameters'])) {
                    $html .= '<h4>Parameters</h4>';
                    foreach ($method_data['parameters'] as $param_name => $param_data) {
                        $required_class = $param_data['required'] ? 'required' : 'optional';
                        $required_text = $param_data['required'] ? 'Required' : 'Optional';
                        
                        $html .= '<div class="param">
                            <span class="param-name">' . $param_name . '</span>
                            <span class="param-type">(' . $param_data['type'] . ')</span>
                            <span class="' . $required_class . '">[' . $required_text . ']</span><br>
                            ' . $param_data['description'];
                        
                        if (isset($param_data['default'])) {
                            $html .= '<br><strong>Default:</strong> ' . $param_data['default'];
                        }
                        
                        $html .= '</div>';
                    }
                }
                
                if (isset($method_data['response_example'])) {
                    $html .= '<h4>Example Response</h4>
                        <pre>' . json_encode($method_data['response_example'], JSON_PRETTY_PRINT) . '</pre>';
                }
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Generate test report
     */
    public function generateTestReport() {
        if (empty($this->test_results)) {
            $this->runTestSuite();
        }
        
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MesChain API Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .summary { background: #e7f3ff; padding: 20px; border-radius: 5px; margin-bottom: 30px; }
        .test { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #ccc; }
        .pass { border-left-color: #28a745; }
        .fail { border-left-color: #dc3545; }
        .status { font-weight: bold; padding: 4px 8px; border-radius: 3px; color: white; }
        .status.pass { background: #28a745; }
        .status.fail { background: #dc3545; }
        pre { background: #f1f1f1; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>MesChain API Test Report</h1>
        
        <div class="summary">
            <h2>Test Summary</h2>
            <p><strong>Total Tests:</strong> ' . $this->test_results['summary']['total_tests'] . '</p>
            <p><strong>Passed:</strong> ' . $this->test_results['summary']['passed'] . '</p>
            <p><strong>Failed:</strong> ' . $this->test_results['summary']['failed'] . '</p>
            <p><strong>Success Rate:</strong> ' . $this->test_results['summary']['success_rate'] . '%</p>
            <p><strong>Total Execution Time:</strong> ' . $this->test_results['summary']['total_execution_time'] . 'ms</p>
            <p><strong>Average Execution Time:</strong> ' . $this->test_results['summary']['average_execution_time'] . 'ms</p>
            <p><strong>Generated:</strong> ' . $this->test_results['summary']['timestamp'] . '</p>
        </div>
        
        <h2>Test Results</h2>';
        
        foreach ($this->test_results['tests'] as $test) {
            $status_class = strtolower($test['status']);
            $test_class = $status_class === 'pass' ? 'pass' : 'fail';
            
            $html .= '<div class="test ' . $test_class . '">
                <h3>' . $test['name'] . ' <span class="status ' . $status_class . '">' . $test['status'] . '</span></h3>
                <p><strong>Execution Time:</strong> ' . $test['execution_time'] . 'ms</p>';
            
            if (isset($test['error'])) {
                $html .= '<p><strong>Error:</strong> ' . htmlspecialchars($test['error']) . '</p>';
            }
            
            if (isset($test['response'])) {
                $html .= '<details>
                    <summary>Response Details</summary>
                    <pre>' . htmlspecialchars(json_encode($test['response'], JSON_PRETTY_PRINT)) . '</pre>
                </details>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>
</body>
</html>';
        
        return $html;
    }
}
