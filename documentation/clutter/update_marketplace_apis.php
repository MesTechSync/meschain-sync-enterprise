<?php
/**
 * Update Marketplace API Controllers with New Infrastructure
 * Updates all marketplace API controllers to use the new infrastructure components
 * 
 * @version 1.0.0
 * @date June 2, 2025
 * @author MesChain Development Team
 */

class MarketplaceApiUpdater {
    
    private $controllers = [
        'ebay_api.php',
        'trendyol_api.php', 
        'n11_api.php',
        'hepsiburada_api.php',
        'ozon_api.php'
    ];
    
    private $controller_path = '/Users/mezbjen/Desktop/MesTech/MesChain-Sync/upload/admin/controller/extension/module/';
    
    public function updateAllControllers() {
        echo "=== MesChain Marketplace API Controllers Update ===\n\n";
        
        foreach ($this->controllers as $controller) {
            $this->updateController($controller);
        }
        
        echo "\n=== Update Complete ===\n";
    }
    
    private function updateController($controller_file) {
        $file_path = $this->controller_path . $controller_file;
        echo "Updating: {$controller_file}... ";
        
        if (!file_exists($file_path)) {
            echo "SKIP (file not found)\n";
            return;
        }
        
        $content = file_get_contents($file_path);
        $marketplace = str_replace('_api.php', '', $controller_file);
        $marketplace_title = ucfirst($marketplace);
        
        // Update constructor if not already updated
        if (strpos($content, 'loadInfrastructure()') === false) {
            $content = $this->updateConstructor($content, $marketplace_title);
        }
        
        // Add infrastructure loading method if not exists
        if (strpos($content, 'private function loadInfrastructure()') === false) {
            $content = $this->addInfrastructureMethod($content, $marketplace_title);
        }
        
        // Add sendResponse method if not exists
        if (strpos($content, 'private function sendResponse(') === false) {
            $content = $this->addSendResponseMethod($content, $marketplace);
        }
        
        // Update main API methods
        $content = $this->updateApiMethods($content, $marketplace);
        
        // Remove old setJsonHeaders calls
        $content = str_replace('$this->setJsonHeaders();', '', $content);
        $content = str_replace('$this->handleApiError(', '// $this->handleApiError(', $content);
        
        file_put_contents($file_path, $content);
        echo "UPDATED\n";
    }
    
    private function updateConstructor($content, $marketplace) {
        // Add integration_service property
        if (strpos($content, 'private $integration_service;') === false) {
            $content = str_replace(
                'private $metrics_collector;',
                "private \$metrics_collector;\n    private \$integration_service;",
                $content
            );
        }
        
        // Add loadInfrastructure call to constructor
        $pattern = '/public function __construct\(\$registry\) \{[\s\S]*?parent::__construct\(\$registry\);/';
        $replacement = "public function __construct(\$registry) {\n        parent::__construct(\$registry);\n        \n        // Load infrastructure components\n        \$this->loadInfrastructure();";
        
        $content = preg_replace($pattern, $replacement, $content);
        
        return $content;
    }
    
    private function addInfrastructureMethod($content, $marketplace) {
        $method = "
    /**
     * Load MesChain API infrastructure components
     */
    private function loadInfrastructure() {
        try {
            // Load integration service
            require_once(DIR_SYSTEM . 'library/meschain/api_integration_service.php');
            
            \$this->integration_service = new MeschainApiIntegrationService(\$this->db->link, [
                'enable_logging' => true,
                'enable_caching' => true,
                'enable_rate_limiting' => true,
                'enable_metrics' => true,
                'max_requests_per_minute' => 100,
                'debug_mode' => false
            ]);
            
        } catch (Exception \$e) {
            \$this->log->write('MesChain {$marketplace} API Infrastructure Load Error: ' . \$e->getMessage());
        }
    }";
        
        // Insert after constructor
        $content = preg_replace(
            '/(public function __construct\(\$registry\)[\s\S]*?\})/m',
            "$1\n$method",
            $content
        );
        
        return $content;
    }
    
    private function addSendResponseMethod($content, $marketplace) {
        $method = "
    /**
     * Send standardized API response
     */
    private function sendResponse(\$data, \$status_code = 200, \$message = 'Success') {
        \$this->response->addHeader('Content-Type: application/json');
        \$this->response->addHeader('Cache-Control: no-cache, must-revalidate');
        \$this->response->addHeader('Access-Control-Allow-Origin: *');
        \$this->response->addHeader('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        \$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        if (\$this->integration_service) {
            \$response = \$this->integration_service->formatResponse(\$data, \$status_code, \$message);
        } else {
            \$response = [
                'success' => \$status_code >= 200 && \$status_code < 300,
                'status_code' => \$status_code,
                'message' => \$message,
                'data' => \$data,
                'timestamp' => date('Y-m-d H:i:s'),
                'marketplace' => '{$marketplace}'
            ];
        }
        
        \$this->response->setOutput(json_encode(\$response, JSON_PRETTY_PRINT));
    }";
        
        // Insert after loadInfrastructure method
        $content = preg_replace(
            '/(private function loadInfrastructure\(\)[\s\S]*?\})/m',
            "$1\n$method",
            $content
        );
        
        return $content;
    }
    
    private function updateApiMethods($content, $marketplace) {
        $marketplace_upper = strtoupper($marketplace);
        
        // Update metrics method
        $content = preg_replace(
            '/public function metrics\(\) \{[\s\S]*?try \{/',
            "public function metrics() {\n        try {\n            \$start_time = microtime(true);\n            \n            // Process request through integration service\n            if (\$this->integration_service) {\n                \$request_data = [\n                    'endpoint' => '{$marketplace}/metrics',\n                    'method' => 'GET',\n                    'params' => \$this->request->get\n                ];\n                \n                \$processed = \$this->integration_service->processRequest(\$request_data);\n                if (!\$processed['success']) {\n                    \$this->sendResponse(null, 400, \$processed['message']);\n                    return;\n                }\n            }\n            \n            try {",
            $content
        );
        
        // Update charts method 
        $content = preg_replace(
            '/public function charts\(\) \{[\s\S]*?try \{/',
            "public function charts() {\n        try {\n            \$period = \$this->request->get['period'] ?? '30';\n            \$chart_type = \$this->request->get['type'] ?? 'all';\n            \n            // Process request through integration service\n            if (\$this->integration_service) {\n                \$request_data = [\n                    'endpoint' => '{$marketplace}/charts',\n                    'method' => 'GET',\n                    'params' => ['period' => \$period, 'type' => \$chart_type]\n                ];\n                \n                \$processed = \$this->integration_service->processRequest(\$request_data);\n                if (!\$processed['success']) {\n                    \$this->sendResponse(null, 400, \$processed['message']);\n                    return;\n                }\n            }\n            \n            try {",
            $content
        );
        
        // Replace response outputs with sendResponse calls
        $content = preg_replace(
            '/\$this->response->setOutput\(json_encode\(\[\s*\'success\'\s*=>\s*true,[\s\S]*?\]\)\);/',
            "\$this->sendResponse(\$metrics, 200, '{$marketplace} metrics retrieved successfully');",
            $content
        );
        
        $content = preg_replace(
            '/\$this->response->setOutput\(json_encode\(\[\s*\'success\'\s*=>\s*true,[\s\S]*?\'data\'\s*=>\s*\$charts,[\s\S]*?\]\)\);/',
            "\$this->sendResponse(\$charts, 200, '{$marketplace} charts data retrieved successfully');",
            $content
        );
        
        // Update exception handling
        $content = preg_replace(
            '/\} catch \(Exception \$e\) \{\s*\$this->handleApiError\([^;]+\);\s*\}/',
            "} catch (Exception \$e) {\n            if (\$this->integration_service) {\n                \$this->integration_service->handleError(\$e, '{$marketplace_upper}_ERROR');\n            }\n            \$this->sendResponse(null, 500, 'Internal server error: ' . \$e->getMessage());\n        }",
            $content
        );
        
        return $content;
    }
}

// Run the updater
$updater = new MarketplaceApiUpdater();
$updater->updateAllControllers();
